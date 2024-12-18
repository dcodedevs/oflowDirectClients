<?php
$s_username = $v_data['params']['username'];
$customerData = $v_data['params']['customerData'];
$subscriptions = $customerData['subscriptions'];
$contactPerson = $customerData['contactPerson'];

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
fax= '".$o_main->db->escape_str($customerData['fax'])."',
publicRegisterId= '".$o_main->db->escape_str($customerData['publicRegisterId'])."',
selfregistered = 1,
selfdefined_company_id= '".$o_main->db->escape_str($customerData['selfdefined_company_id'])."'");
$customerId = $o_main->db->insert_id();
if($customerId > 0){
    $o_query = $o_main->db->query("INSERT INTO contactperson SET created = NOW(), createdBy = '".$o_main->db->escape_str($s_username)."',
	customerId= '".$o_main->db->escape_str($customerId)."',
	name= '".$o_main->db->escape_str($contactPerson['name'])."',
	middlename= '".$o_main->db->escape_str($contactPerson['middlename'])."',
	lastname= '".$o_main->db->escape_str($contactPerson['lastname'])."',
	email= '".$o_main->db->escape_str($contactPerson['email'])."',
	intranet_membership_type = 1,
	intranet_membership_subscription_type = 1,
	door_access_code_type= 2");
	$contactPersonId = $o_main->db->insert_id();

    $noError = true;
    foreach($subscriptions as $subscriptionData){
        if($subscriptionData['subscriptiontype_id'] == "getynetPay"){
            $sql = "SELECT * FROM subscriptiontype WHERE autorenewal = 2 ORDER BY id ASC";
        	$o_query = $o_main->db->query($sql);
            $subscription_type = $o_query ? $o_query->row_array() : array();

            $subscriptionData['subscriptiontype_id'] = intval($subscription_type['id']);
        }
        $subscriptionLines = $subscriptionData['subscriptionLines'];
        $o_query = $o_main->db->query("INSERT INTO subscriptionmulti SET created = NOW(), createdBy = '".$o_main->db->escape_str($s_username)."',
        startDate = NOW(),
		subscriptionName= '".$o_main->db->escape_str($subscriptionData['subscriptionName'])."',
        periodNumberOfMonths = '".$o_main->db->escape_str($subscriptionData['periodNumberOfMonths'])."',
        contactPerson = '".$o_main->db->escape_str($contactPersonId)."',
        content_status = '".$o_main->db->escape_str($subscriptionData['content_status'])."',
        subscriptiontype_id = '".$o_main->db->escape_str($subscriptionData['subscriptiontype_id'])."',
        ownercompany_id = '".$o_main->db->escape_str($subscriptionData['ownercompany_id'])."',
        nextRenewalDate =  now() + interval 1 month,
        freeNoBilling = 0,
        customerId= '".$o_main->db->escape_str($customerId)."'");
        if($o_query){
            $subscriptionId = $o_main->db->insert_id();
            $v_return['subscription_ids'][] = $subscriptionId;
			$o_main->db->query("INSERT INTO intranet_membership_contactperson_connection SET contactperson_id = '".$o_main->db->escape_str($contactPersonId)."', membership_id = 4");
			$o_main->db->query("INSERT INTO contactperson_subscription_connection SET contactperson_id = '".$o_main->db->escape_str($contactPersonId)."', subscriptionmulti_id = '".$o_main->db->escape_str($subscriptionId)."'");
			$o_main->db->query("INSERT INTO contactperson_doorcode_connection SET contactperson_id = '".$o_main->db->escape_str($contactPersonId)."', subscriptionmulti_id = '".$o_main->db->escape_str($subscriptionId)."'");

            foreach($subscriptionLines as $subscriptionLine) {
                $o_query = $o_main->db->query("INSERT INTO subscriptionline SET created = NOW(), createdBy = '".$o_main->db->escape_str($s_username)."',
                articleNumber= '".$o_main->db->escape_str($subscriptionLine['articleNumber'])."',
                articleName= '".$o_main->db->escape_str($subscriptionLine['articleName'])."',
                pricePerPiece= '".$o_main->db->escape_str($subscriptionLine['pricePerPiece'])."',
                amount= '".$o_main->db->escape_str($subscriptionLine['amount'])."',
                bookAccountCode= '".$o_main->db->escape_str($subscriptionLine['bookAccountCode'])."',
                vatCode= '".$o_main->db->escape_str($subscriptionLine['vatCode'])."',
                cpiAdjustmentFactor= '".$o_main->db->escape_str($subscriptionLine['cpiAdjustmentFactor'])."',
                articleOrIndividualPrice= 0,
                subscribtionId= '".$o_main->db->escape_str($subscriptionId)."'");
                if(!$o_query){
                    $noError = false;
					$v_return['sql'] = $o_main->db->last_query();
                }
            }
        } else {
            $noError = false;
			$v_return['sql'] = $o_main->db->last_query();
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
