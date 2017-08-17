<?php
if(!extension_loaded('mbstring')){
	echo "Necessário instalar a extensão mbstring no servidor. Contate o suporte.";
}

require_once(dirname(__FILE__)."/../../../init.php");
require_once(dirname(__FILE__)."/Iugu.php");

$sql_fatura_items = mysql_query("SELECT amount, description FROM tblinvoiceitems WHERE invoiceid='".$_POST['invoice_id']."'");
$itens = Array();
while($row_fatura_items = mysql_fetch_array($sql_fatura_items)){
	$item = Array();
	$item['description'] = $row_fatura_items['description'];
	$item['quantity'] = "1";
	$item['price_cents'] = str_replace(".", "", $row_fatura_items['amount']);
	$itens[] = $item;
}

if($_POST['due_date'] < date("Y-m-d")){
	$date = new DateTime('+1 day');
	$vencimento = $date->format('Y-m-d');
}else{
	$vencimento = $_POST['due_date'];
}

if($_POST['late_payment_fine'] > 0){
	$late_payment_fine = $_POST['late_payment_fine'];
	$fines = "true";
	
	if($_POST['due_date'] < date("Y-m-d")){
		$calcMulta_dec = $_POST['valor'] * ($_POST['late_payment_fine'] / 100);
		$calcMulta_explode = explode('.', $calcMulta_dec);
		$calcMulta_cent = substr($calcMulta_explode[1], 0, 2);
		$calcMulta = number_format($calcMulta_explode[0].".".$calcMulta_cent, 2);
		
		$item = Array();
		$item['description'] = "Multa por atraso (".$_POST['late_payment_fine']."%)";
		$item['quantity'] = "1";
		$item['price_cents'] = str_replace(".", "", number_format($calcMulta, 2));
		$itens[] = $item;
	}
}else{
	$late_payment_fine = "";
	$fines = "false";
}

if($_POST['per_day_interest'] == "on"){
	$per_day_interest = "true";
	
	if($_POST['due_date'] < date("Y-m-d")){
		$calcMora_dec = $_POST['valor'] * ((1/30) / 100);
		$calcMora_explode = explode('.', $calcMora_dec);
		$calcMora_cent = substr($calcMora_explode[1], 0, 2);
		$calcMora = number_format($calcMora_explode[0].".".$calcMora_cent, 2);
		
		$dias_vencido = (strtotime(date("Y-m-d")) - strtotime($_POST['due_date']))  / (60 * 60 * 24);
		
		$item = Array();
		$item['description'] = "Juros diário (1% ao mês pro rata) - ".$dias_vencido." dias";
		$item['quantity'] = "1";
		$item['price_cents'] = str_replace(".", "", number_format(($calcMora * $dias_vencido), 2));
		$itens[] = $item;
	}
}else{
	$per_day_interest = "false";
}

Iugu::setApiKey($_POST['token']);
$criar = Iugu_Invoice::create(Array(
	"email" => $_POST['email'],
	"due_date" => date('d/m/Y', strtotime($vencimento)),
	"return_url" => $_POST['return_url'],
	"expired_url" => $_POST['expired_url'],
	"notification_url" => $_POST['notification_url'],
	"items" => $itens,
	"ignore_due_email" => true,
	"fines" => $fines,
	"late_payment_fine" => $late_payment_fine,
	"per_day_interest" => $per_day_interest,
	"payable_with" => "bank_slip",
	"custom_variables" => Array(
		Array(
			"name" => "invoice_id",
			"value" => $_POST['invoice_id']
		)
	),
	"payer" => Array(
		"cpf_cnpj" => preg_replace("/[^0-9]/", "", $_POST['doc']),
		"name" => $_POST['nome'],
		"email" => $_POST['email'],
		"address" => Array(
			"street" => $_POST['rua'],
			"number" => $_POST['num'],
			"city" => $_POST['cidade'],
			"state" => $_POST['uf'],
			"country" => $_POST['pais'],
			"zip_code" => preg_replace("/[^0-9]/", "", $_POST['cep'])
		)
	)
));

if($criar->secure_url){
	header("Location: ".$criar->secure_url);
}else{
	print_r($criar->errors);
}
?>
