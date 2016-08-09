<?php
if(!extension_loaded('mbstring')){
	echo "Necessário instalar a extensão mbstring no servidor. Contate o suporte.";
}

require_once("../../../init.php");
require_once("Iugu.php");

$sql = mysql_query("SELECT * FROM mod_iugu WHERE fatura_id='".$_POST['invoice_id']."' AND valor='".$_POST['valor']."' AND vencimento='".$_POST['due_date']."'");

if(mysql_num_rows($sql)){
	$row = mysql_fetch_array($sql);
	header("Location: http://iugu.com/invoices/".$row['secure_id']);
}else{
	Iugu::setApiKey($_POST['api_token']);
	$criar = Iugu_Invoice::create(Array(
		"email" => $_POST['email'],
		"due_date" => $_POST['due_date'],
		"return_url" => $_POST['return_url'],
		"expired_url" => $_POST['expired_url'],
		"notification_url" => $_POST['notification_url'],
		"custom_variables" => Array(
			Array(
				"name" => "invoice_id",
				"value" => $_POST['invoice_id']
			)
		),
		"items" => $_POST['items'],
		"ignore_due_email" => true
	));
	
	// print_r($criar);

	if($criar->secure_url){
		mysql_query("INSERT INTO mod_iugu (fatura_id, iugu_id, secure_id, valor, vencimento) VALUES ('".$_POST['invoice_id']."', '".$criar->id."', '".$criar->secure_id."', '".$_POST['valor']."', '".$_POST['due_date']."')");
		header("Location: ".$criar->secure_url);
	}else{
		echo "Erro ao gerar cobrança. Contate o suporte.";
	}
}
?>
