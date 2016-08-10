<?php

function iugu_activate(){
	defineGatewayField("iugu","text","api_token","","Token", "40", "Você pode gerenciar seus tokens nas configurações de sua <a href='https://iugu.com/settings/account' target='blank'>conta</a>.");
	defineGatewayField("iugu","text","dias","2","Dias Adicionais", "2", "Quantos dias serão acrescidos após o boleto estar vencido?");
}

function iugu_link($params){
	$db_invoice = mysql_query("SELECT * FROM tblinvoices WHERE id='{$params['invoiceid']}'") or $db_invoice = 0;
	$dados_invoice = mysql_fetch_array($db_invoice);
	if($dados_invoice['duedate'] < date('d/m/Y')){
		$vencimento = date('d/m/Y', strtotime('+ '.$params['dias'].' days'));
	}else{
		$vencimento = date('d/m/Y', strtotime($dados_invoice['duedate']));
	}

	$valor = number_format($dados_invoice['total'], 2, '', '');
	
	$code = '<form action="modules/gateways/iugu/gerar.php" method="POST">
		<input type="hidden" name="api_token" value="'.$params['api_token'].'">
		<input type="hidden" name="api_dias" value="'.$params['dias'].'">
		<input type="hidden" name="return_url" value="'.$params['systemurl'].'/viewinvoice.php?id='.$params['invoiceid'].'&paymentsuccess=true">
		<input type="hidden" name="expired_url" value="'.$params['systemurl'].'/viewinvoice.php?id='.$params['invoiceid'].'&paymentfailed=true">
		<input type="hidden" name="notification_url" value="'.$params['systemurl'].'/modules/gateways/callback/iugu.php">
		<input type="hidden" name="email" value="'.$params['clientdetails']['email'].'">
		<input type="hidden" name="nome" value="'.$params['clientdetails']['fullname'].'">
		<input type="hidden" name="rua" value="'.$params['clientdetails']['address1'].'">
		<input type="hidden" name="bairro" value="'.$params['clientdetails']['address2'].'">
		<input type="hidden" name="cidade" value="'.$params['clientdetails']['city'].'">
		<input type="hidden" name="uf" value="'.$params['clientdetails']['state'].'">
		<input type="hidden" name="cep" value="'.$params['clientdetails']['postcode'].'">
		<input type="hidden" name="pais" value="'.$params['clientdetails']['country'].'">
		<input type="hidden" name="invoice_id" value="'.$params['invoiceid'].'">
		<input type="hidden" name="valor" value="'.$valor.'">
		<input type="hidden" name="due_date" value="'.$vencimento.'">
		<input type="hidden" name="ignore_due_email" value="true">
		<input type="submit" id="btnPagar" value="Pagar">
	</form>';
	
	return $code;
}

$GATEWAYMODULE["iuguname"]="iugu";
$GATEWAYMODULE["iuguvisiblename"]="Iugu v1.4b";
$GATEWAYMODULE["iugutype"]="Invoices";
?>
