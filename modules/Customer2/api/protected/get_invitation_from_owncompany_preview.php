<?php
	$fromApiMarker = true;
	$_POST['from_owncompany'] = true;
	$o_query = $o_main->db->query("SELECT * FROM customer WHERE id = '".$o_main->db->escape_str($v_data['params']['customerId'])."'");
	$customer = $o_query ? $o_query->row_array() : array();
	if(!function_exists("APIconnectorUser")) include_once(__DIR__."/../../../../fw/account_fw/includes/APIconnector.php");

	$companyID  = $_GET['companyID'] = $v_data['params']['companyID'];
	$_GET['caID'] = $v_data['params']['caID'];
	$contactpersonIds = $v_data['params']['contactpersonIds'];
	$_COOKIE['username'] =$v_data['params']['username'];
	$_COOKIE['sessionID'] = $v_data['params']['sessionID'];
	$peopleItem = $v_data['params']['peopleItem'];
	$v_contactperson['name'] =$peopleItem['name'];
	$v_contactperson['middlename'] =$peopleItem['middle_name'];
	$v_contactperson['lastname'] =$peopleItem['last_name'];
	$s_receiver_email = $peopleItem['email'];
	$fw_session['accountlanguageID'] = $v_data['params']['accountlanguageID'];


	ob_start();
	$s_email_template = "sendemail_standard";
	include(__DIR__."/../../".$s_email_template."/template.php");
	$output = ob_get_clean();
	$v_return['email_from'] = $s_email_from;
	$v_return['email_body'] = $s_email_body;
	$v_return['email_subject'] = $s_email_subject;
	$v_return['log'] = $v_data['params']['customerId'];
	$v_return['status'] = 1;
?>
