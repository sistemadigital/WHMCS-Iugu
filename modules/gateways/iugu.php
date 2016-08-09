<?php

function iugu_activate(){
	defineGatewayField("iugu","text","api_token","","Token", "40", "Você pode gerenciar seus tokens nas configurações de sua <a href='https://iugu.com/settings/account' target='blank'>conta</a>.");
	defineGatewayField("iugu","text","dias","2","Dias Adicionais", "2", "Quantos dias serão acrescidos após o boleto estar vencido?");
}

function iugu_link($params){
	$db_invoice = mysql_query("SELECT * FROM tblinvoices WHERE id='{$params['invoiceid']}' AND userid='{$params['clientdetails']['userid']}'") or $db_invoice = 0;
	$dados_invoice = mysql_fetch_array($db_invoice);
	
	$db_invoice_items = mysql_query("SELECT amount, description, SUM(amount) AS total FROM tblinvoiceitems WHERE invoiceid='{$params['invoiceid']}' AND userid='{$params['clientdetails']['userid']}'") or $db_invoice_items = 0;
	
	if($dados_invoice['duedate'] < date('d/m/Y')){
		$vencimento = date('d/m/Y', strtotime('+ '.$params['dias'].' days'));
	}else{
		$vencimento = date('d/m/Y', strtotime($dados_invoice['duedate']));
	}
	
	$itens = "";
	if($db_invoice_items){
		while($dados_items = mysql_fetch_array($db_invoice_items)){
			$valor = number_format($dados_items['amount'], 2, '', '');
			$valor_total = number_format($dados_items['total'], 2, '', '');
			
			$itens .= '<input type="hidden" name="items[][description]" value="'.$dados_items['description'].'">
		<input type="hidden" name="items[][quantity]" value="1">
		<input type="hidden" name="items[][price_cents]" value="'.$valor.'">
		';
		}
	}
	
	$code = '<form action="modules/gateways/iugu/gerar.php" method="POST">
		<input type="hidden" name="api_token" value="'.$params['api_token'].'">
		<input type="hidden" name="return_url" value="'.$params['systemurl'].'/viewinvoice.php?id='.$params['invoiceid'].'&paymentsuccess=true">
		<input type="hidden" name="expired_url" value="'.$params['systemurl'].'/viewinvoice.php?id='.$params['invoiceid'].'&paymentfailed=true">
		<input type="hidden" name="notification_url" value="'.$params['systemurl'].'/modules/gateways/callback/iugu.php">
		<input type="hidden" name="email" value="'.$params['clientdetails']['email'].'">
		<input type="hidden" name="due_date" value="'.$vencimento.'">
		'.$itens.'<input type="hidden" name="valor" value="'.$valor_total.'">
		<input type="hidden" name="invoice_id" value="'.$params['invoiceid'].'">
		<input type="hidden" name="ignore_due_email" value="true">
		<input type="submit" id="btnPagar" value="Pagar">
	</form>';
	
	return $code;
}

$GATEWAYMODULE["iuguname"]="iugu";
$GATEWAYMODULE["iuguvisiblename"]="Iugu v1.3b";
$GATEWAYMODULE["iugutype"]="Invoices";
?>
