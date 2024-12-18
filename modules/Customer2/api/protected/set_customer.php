<?php
$s_username = $v_data['params']['username'];
$s_sql_extra = '';
if(isset($v_data['params']['paStreet'])) $s_sql_extra .= ", paStreet = '".$o_main->db->escape_str($v_data['params']['paStreet'])."'";
if(isset($v_data['params']['paPostalNumber'])) $s_sql_extra .= ", paPostalNumber = '".$o_main->db->escape_str($v_data['params']['paPostalNumber'])."'";
if(isset($v_data['params']['paCity'])) $s_sql_extra .= ", paCity = '".$o_main->db->escape_str($v_data['params']['paCity'])."'";
if(isset($v_data['params']['paCountry'])) $s_sql_extra .= ", paCountry = '".$o_main->db->escape_str($v_data['params']['paCountry'])."'";
if(isset($v_data['params']['vaStreet'])) $s_sql_extra .= ", vaStreet = '".$o_main->db->escape_str($v_data['params']['vaStreet'])."'";
if(isset($v_data['params']['vaPostalNumber'])) $s_sql_extra .= ", vaPostalNumber = '".$o_main->db->escape_str($v_data['params']['vaPostalNumber'])."'";
if(isset($v_data['params']['vaCity'])) $s_sql_extra .= ", vaCity = '".$o_main->db->escape_str($v_data['params']['vaCity'])."'";
if(isset($v_data['params']['vaCountry'])) $s_sql_extra .= ", vaCountry = '".$o_main->db->escape_str($v_data['params']['vaCountry'])."'";
if(isset($v_data['params']['phone'])) $s_sql_extra .= ", phone = '".$o_main->db->escape_str($v_data['params']['phone'])."'";
if(isset($v_data['params']['mobile'])) $s_sql_extra .= ", mobile = '".$o_main->db->escape_str($v_data['params']['mobile'])."'";
if(isset($v_data['params']['email'])) $s_sql_extra .= ", email = '".$o_main->db->escape_str($v_data['params']['email'])."'";
if(isset($v_data['params']['homepage'])) $s_sql_extra .= ", homepage = '".$o_main->db->escape_str($v_data['params']['homepage'])."'";
if(isset($v_data['params']['middlename'])) $s_sql_extra .= ", middlename = '".$o_main->db->escape_str($v_data['params']['middlename'])."'";
if(isset($v_data['params']['lastname'])) $s_sql_extra .= ", lastname = '".$o_main->db->escape_str($v_data['params']['lastname'])."'";
if(isset($v_data['params']['birthdate'])) $s_sql_extra .= ", birthdate = '".$o_main->db->escape_str($v_data['params']['birthdate'])."'";
if(isset($v_data['params']['personnumber'])) $s_sql_extra .= ", personnumber = '".$o_main->db->escape_str($v_data['params']['personnumber'])."'";
if(isset($v_data['params']['publicRegisterId'])) $s_sql_extra .= ", publicRegisterId = '".$o_main->db->escape_str($v_data['params']['publicRegisterId'])."'";
if(isset($v_data['params']['customerType'])) $s_sql_extra .= ", customerType = '".$o_main->db->escape_str($v_data['params']['customerType'])."'";

if(!$v_data['params']['contactpersonOnly']) {
	$o_query = $o_main->db->query("INSERT INTO customer SET created = NOW(), createdBy = '".$o_main->db->escape_str($s_username)."', name= '".$o_main->db->escape_str($v_data['params']['name'])."', getynet_customer_id= '".$o_main->db->escape_str($v_data['params']['getynet_customer_id'])."', selfregistered = 1".$s_sql_extra);
	if($o_query)
	{
		$customerId = $o_main->db->insert_id();

	    if(isset($v_data['params']['add_no_bill_subscription']))
		{
			$o_query = $o_main->db->query("INSERT INTO contactperson SET created = NOW(), createdBy = '".$o_main->db->escape_str($s_username)."',
			name= '".$o_main->db->escape_str($v_data['params']['first_name'])."',
			middlename= '".$o_main->db->escape_str($v_data['params']['middle_name'])."',
			lastname= '".$o_main->db->escape_str($v_data['params']['last_name'])."',
			email= '".$o_main->db->escape_str($v_data['params']['email'])."'");
			$contactPersonId = $o_main->db->insert_id();

			$o_query = $o_main->db->query("INSERT INTO subscriptionmulti SET created = NOW(), createdBy = '".$o_main->db->escape_str($s_username)."',
			subscriptionName= '".$o_main->db->escape_str($v_data['params']['name'])."',
			freeNoBilling = 1,
			customerId= '".$o_main->db->escape_str($customerId)."'");
			if($o_query)
			{
				$subscriptionId = $o_main->db->insert_id();
				$o_query = $o_main->db->query("INSERT INTO subscriptionmulti SET created = NOW(), createdBy = '".$o_main->db->escape_str($s_username)."',
				articleName= '".$o_main->db->escape_str($$v_data['params']['name'])."',
				subscribtionId= '".$o_main->db->escape_str($subscriptionId)."'");
			}
		}
		$v_return['status'] = 1;
		$v_return['customer_id'] = $customerId;
	} else {
		$v_return['message'] = 'Error occurred updating data';
	}
} else {
	$customerId = $o_main->db->escape_str($v_data['params']['customerId']);
	$sql = "SELECT * FROM contactperson WHERE email = ? AND customerId = ?";
	$o_query = $o_main->db->query($sql, array($v_data['params']['contactperson_email'], $customerId));
    $contactperson = $o_query ? $o_query->row_array() : array();
	if(!$contactperson){
		$o_query = $o_main->db->query("INSERT INTO contactperson SET created = NOW(), createdBy = '".$o_main->db->escape_str($s_username)."',
		name= '".$o_main->db->escape_str($v_data['params']['first_name'])."',
		middlename= '".$o_main->db->escape_str($v_data['params']['middle_name'])."',
		lastname= '".$o_main->db->escape_str($v_data['params']['last_name'])."',
		email= '".$o_main->db->escape_str($v_data['params']['contactperson_email'])."',
		mobile= '".$o_main->db->escape_str($v_data['params']['contactperson_mobile'])."',
		customerId= '".$o_main->db->escape_str($customerId)."'");
	}
}
