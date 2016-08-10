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
	$db_invoice = mysql_query("SELECT * FROM tblinvoices WHERE id='".$_POST['invoice_id']."'") or $db_invoice = 0;
	$dados_invoice = mysql_fetch_array($db_invoice);
	if($dados_invoice['duedate'] < date('d/m/Y')){
		$vencimento = date('d/m/Y', strtotime('+ '.$_POST['api_dias'].' days'));
	}else{
		$vencimento = date('d/m/Y', strtotime($dados_invoice['duedate']));
	}
	
	$valor_total = number_format($dados_invoice['total'], 2, '', '');
	
	$db_invoice_items = mysql_query("SELECT amount, description FROM tblinvoiceitems WHERE invoiceid='".$_POST['invoice_id']."'") or $db_invoice_items = 0;
	$itens = Array();
	if($db_invoice_items){
		while($dados_items = mysql_fetch_array($db_invoice_items)){
			$valor = number_format($dados_items['amount'], 2, '', '');
			
			$item = Array();
			$item['description'] = $dados_items['description'];
			$item['quantity'] = "1";
			$item['price_cents'] = $valor;
			$itens[] = $item;
		}
	}
	
	Iugu::setApiKey($_POST['api_token']);
	$criar = Iugu_Invoice::create(Array(
		"email" => $_POST['email'],
		"due_date" => $vencimento,
		"return_url" => $_POST['return_url'],
		"expired_url" => $_POST['expired_url'],
		"notification_url" => $_POST['notification_url'],
		"items" => $itens,
		"ignore_due_email" => true,
		"custom_variables" => Array(
			Array(
				"name" => "invoice_id",
				"value" => $_POST['invoice_id']
			)
		),
		"payer" => Array(
			"name" => $_POST['nome'],
			"email" => $_POST['email'],
			"address" => Array(
				"street" => $_POST['rua'],
				"number" => $_POST['bairro'],
				"city" => $_POST['cidade'],
				"state" => $_POST['uf'],
				"country" => $_POST['pais'],
				"zip_code" => $_POST['cep']
			)
		)
	));
	
	// print_r($criar);

	if($criar->secure_url){
		mysql_query("INSERT INTO mod_iugu (fatura_id, iugu_id, secure_id, valor, vencimento) VALUES ('".$_POST['invoice_id']."', '".$criar->id."', '".$criar->secure_id."', '".$valor_total."', '".$vencimento."')");
		header("Location: ".$criar->secure_url);
	}else{
		echo "Erro ao gerar cobrança. Contate o suporte.";
	}
}
?>
