<?php
$s_username = $v_data['params']['username'];
$customerData = $v_data['params']['customerData'];
$subscriptions = $customerData['subscriptions'];
$contactPerson = $customerData['contactPerson'];

$o_query = $o_main->db->query("SELET * FROM customer WHERE publicRegisterId = ?", array($customerData['publicRegisterId']));
$checkCustomer = $o_query ? $o_query->row_array() : array();
if(!$checkCustomer) {
    $o_query = $o_main->db->query("INSERT INTO customer SET created = NOW(), createdBy = '".$o_main->db->escape_str($s_username)."',
    name= '".$o_main->db->escape_str($customerData['name'])."',
    middlename= '".$o_main->db->escape_str($customerData['middlename'])."',
    lastname= '".$o_main->db->escape_str($customerData['lastname'])."',
    paStreet= '".$o_main->db->escape_str($customerData['paStreet'])."',
    paStreet2= '".$o_main->db->escape_str($customerData['paStreet2'])."',
    paPostalNumber= '".$o_main->db->escape_str($customerData['paPostalNumber'])."',
    paCity= '".$o_main->db->escape_str($customerData['paCity'])."',
    paCountry= '".$o_main->db->escape_str($customerData['paCountry'])."',
    invoiceBy= '".$o_main->db->escape_str($customerData['invoiceBy'])."',
    invoiceEmail= '".$o_main->db->escape_str($customerData['invoiceEmail'])."',
    phone= '".$o_main->db->escape_str($customerData['phone'])."',
    mobile= '".$o_main->db->escape_str($customerData['mobile'])."',
    homepage= '".$o_main->db->escape_str($customerData['homepage'])."',
    vaStreet= '".$o_main->db->escape_str($customerData['vaStreet'])."',
    vaStreet2= '".$o_main->db->escape_str($customerData['vaStreet2'])."',
    vaPostalNumber= '".$o_main->db->escape_str($customerData['vaPostalNumber'])."',
    vaCity= '".$o_main->db->escape_str($customerData['vaCity'])."',
    vaCountry= '".$o_main->db->escape_str($customerData['vaCountry'])."',
    email= '".$o_main->db->escape_str($customerData['email'])."',
    fax= '".$o_main->db->escape_str($customerData['fax'])."'"
	.(isset($customerData['selfdefined_company_id']) ? ", selfdefined_company_id= '".$o_main->db->escape_str($customerData['selfdefined_company_id'])."'" : ""));
    $customerId = $o_main->db->insert_id();
} else {
    $customerId = $checkCustomer['id'];
}
if($customerId > 0){
    $o_query = $o_main->db->query("SELET * FROM contactperson WHERE customerId = ? AND email = ?", array($customerId, $contactPerson['email']));
    $checkContactPerson = $o_query ? $o_query->row_array() : array();
    if(!$checkContactPerson) {
        $o_query = $o_main->db->query("INSERT INTO contactperson SET created = NOW(), createdBy = '".$o_main->db->escape_str($s_username)."',
        name= '".$o_main->db->escape_str($contactPerson['name'])."',
        email= '".$o_main->db->escape_str($contactPerson['email'])."',
    	door_access_code_type= 2");
        $contactPersonId = $o_main->db->insert_id();
    } else {
        $contactPersonId = $checkContactPerson['id'];
    }
    $noError = true;
    foreach($subscriptions as $subscriptionData){
        $subscriptionLines = $subscriptionData['subscriptionLines'];
        $o_query = $o_main->db->query("INSERT INTO subscriptionmulti SET created = NOW(), createdBy = '".$o_main->db->escape_str($s_username)."',
        subscriptionName= '".$o_main->db->escape_str($subscriptionData['subscriptionName'])."',
        periodNumberOfMonths = '".$o_main->db->escape_str($subscriptionData['periodNumberOfMonths'])."',
        contactPerson = '".$o_main->db->escape_str($contactPersonId)."',
        content_status = '".$o_main->db->escape_str($subscriptionData['content_status'])."',
        subscriptiontype_id = '".$o_main->db->escape_str($subscriptionData['subscriptiontype_id'])."',
        ownercompany_id = '".$o_main->db->escape_str($subscriptionData['ownercompany_id'])."',
        customerId= '".$o_main->db->escape_str($customerId)."'");
        if($o_query){
            $subscriptionId = $o_main->db->insert_id();
			$o_main->db->query("INSERT INTO contactperson_doorcode_connection SET contactperson_id = '".$o_main->db->escape_str($contactPersonId)."', subscriptionmulti_id = '".$o_main->db->escape_str($subscriptionId)."'");

            foreach($subscriptionLines as $subscriptionLine) {
                $o_query = $o_main->db->query("INSERT INTO subscriptionmulti SET created = NOW(), createdBy = '".$o_main->db->escape_str($s_username)."',
                articleNumber= '".$o_main->db->escape_str($subscriptionData['articleNumber'])."',
                articleName= '".$o_main->db->escape_str($subscriptionData['articleName'])."',
                pricePerPiece= '".$o_main->db->escape_str($subscriptionData['pricePerPiece'])."',
                bookAccountCode= '".$o_main->db->escape_str($subscriptionData['bookAccountCode'])."',
                vatCode= '".$o_main->db->escape_str($subscriptionData['vatCode'])."',
                cpiAdjustmentFactor= '".$o_main->db->escape_str($subscriptionData['cpiAdjustmentFactor'])."',
                subscribtionId= '".$o_main->db->escape_str($subscriptionId)."'");
                if(!$o_query){
                    $noError = false;
                }
            }
        } else {
            $noError = false;
        }
    }
    if($noError){
        $v_return['status'] = 1;
		$v_return['customer_id'] = $customerId;
		$v_return['contactperson_id'] = $contactPersonId;
    } else {
        $v_return['message'] = 'Error occurred creating subscription';
    }
} else {
	$v_return['message'] = 'Error occurred creating customer';
}
