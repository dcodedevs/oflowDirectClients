<?php
	$fromApiMarker = true;
	$_POST['from_owncompany'] = true;
	$o_query = $o_main->db->query("SELECT * FROM customer WHERE id = '".$o_main->db->escape_str($v_data['params']['customerId'])."'");
	$customer = $o_query ? $o_query->row_array() : array();
	// if(intval($customer['selfdefined_company_id']) > 0){

	$_POST['selfdefined_company_id'] = intval($customer['selfdefined_company_id']);
	$_GET['companyID'] = $v_data['params']['companyID'];
	$_GET['caID'] = $v_data['params']['caID'];
	$contactpersonIds = $v_data['params']['contactpersonIds'];
	$_COOKIE['username'] =$v_data['params']['username'];
	$_COOKIE['sessionID'] = $v_data['params']['sessionID'];
	foreach($contactpersonIds as $cid){
		$_POST['cid'] = $cid;
		ob_start();
		include(__DIR__."/../../output/includes/ajax.send_invitation.php");
		$output = ob_get_clean();
		if($isregistered){
			$v_return['status'] = 1;
		}
	}
	// } else {
	// 	$v_return['message'] = $formText_CustomerMissingSelfdefinedCompany_output;
	// }
?>
