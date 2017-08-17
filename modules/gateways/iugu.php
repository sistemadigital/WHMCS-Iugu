<?php
if(!defined("WHMCS")){
    die("This file cannot be accessed directly");
}

function iugu_config(){
	$fields = array();
	
	$sql_fields = mysql_query("SELECT id, fieldname FROM tblcustomfields");
	while($row_fields = mysql_fetch_array($sql_fields)){
		$fields[$row_fields['id']] = $row_fields['fieldname'];
	};
			
    $inputs = array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Iugu v1.5',
        ),
		'token' => array(
            'FriendlyName' => 'Token',
            'Type' => 'text',
            'Size' => '40',
            'Default' => '',
            'Description' => 'Você pode gerenciar seus tokens nas configurações de sua <a href="https://iugu.com/settings/account" target="blank">conta</a>.',
        ),
		'late_payment_fine' => array(
            'FriendlyName' => 'Multa (%)',
            'Type' => 'text',
            'Size' => '5',
            'Default' => '2',
            'Description' => 'Informe a multa (em porcentagem) a ser cobrada para pagamentos efetuados após a data de vencimento. (0 para desativar)',
        ),
		'per_day_interest' => array(
            'FriendlyName' => 'Juros',
            'Type' => 'yesno',
            'Description' => 'Cobrar juros por dia de atraso. (1% ao mês pro rata)',
        ),
		'doc' => array(
            'FriendlyName' => 'Campo CPF/CNPJ',
            'Type' => 'dropdown',
            'Options' => $fields,
            'Description' => 'Selecione o campo que contenha o CPF/CNPJ do cliente.',
        ),
		'num' => array(
            'FriendlyName' => 'Campo N° (Endereço)',
            'Type' => 'dropdown',
            'Options' => $fields,
            'Description' => 'Selecione o campo que contenha o número do endereço do cliente.',
        ),
    );
	
	return $inputs;
}

function ValorCampo($campo){
	$sql = mysql_query("SELECT value FROM tblcustomfieldsvalues WHERE fieldid = '$campo' AND relid='".$_SESSION['uid']."'");
	$row = mysql_fetch_array($sql);
	
	return $row['value'];
}

function iugu_link($params){
	
	if($params['clientdetails']['companyname']){
		$nome = $params['clientdetails']['companyname'];
	}else{
		$nome = $params['clientdetails']['fullname'];
	}
	
	$code = '<form action="modules/gateways/iugu/gerar.php" method="POST">
		<input type="hidden" name="token" value="'.$params['token'].'">
		<input type="hidden" name="late_payment_fine" value="'.$params['late_payment_fine'].'">
		<input type="hidden" name="per_day_interest" value="'.$params['per_day_interest'].'">
		<input type="hidden" name="return_url" value="'.$params['systemurl'].'/viewinvoice.php?id='.$params['invoiceid'].'&paymentsuccess=true">
		<input type="hidden" name="expired_url" value="'.$params['systemurl'].'/viewinvoice.php?id='.$params['invoiceid'].'&paymentfailed=true">
		<input type="hidden" name="notification_url" value="'.$params['systemurl'].'/modules/gateways/callback/iugu.php">
		<input type="hidden" name="invoice_id" value="'.$params['invoiceid'].'">
		<input type="hidden" name="valor" value="'.$params['amount'].'">
		<input type="hidden" name="due_date" value="'.$params['dueDate'].'">
		<input type="hidden" name="email" value="'.$params['clientdetails']['email'].'">
		<input type="hidden" name="nome" value="'.$nome.'">
		<input type="hidden" name="doc" value="'.ValorCampo($params['doc']).'">
		<input type="hidden" name="rua" value="'.$params['clientdetails']['address1'].'">
		<input type="hidden" name="num" value="'.ValorCampo($params['num']).'">
		<input type="hidden" name="bairro" value="'.$params['clientdetails']['address2'].'">
		<input type="hidden" name="cidade" value="'.$params['clientdetails']['city'].'">
		<input type="hidden" name="uf" value="'.$params['clientdetails']['state'].'">
		<input type="hidden" name="cep" value="'.$params['clientdetails']['postcode'].'">
		<input type="hidden" name="pais" value="'.$params['clientdetails']['country'].'">
		<input type="submit" id="btnPagar" value="Pagar">
	</form>';

	return $code;
}
?>
