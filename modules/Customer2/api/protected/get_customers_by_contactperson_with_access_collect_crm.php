<?php
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");
$email = $v_data['params']['email'];
$companyID = $v_data['params']['companyID'];

if($companyID != "" && $email != ""){
	$s_sql = "select * from accountinfo";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0){
	    $v_accountinfo = $o_query->row_array();
	}
	$s_sql = "select * from customer_stdmembersystem_basisconfig";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0){
	    $v_membersystem_config = $o_query->row_array();
	}

	if($v_data['params']['useradmin']) {
		$s_sql = "SELECT creditor.*, customer.* FROM customer
		JOIN creditor ON creditor.customer_id = customer.id
		WHERE 1=1 AND customer.content_status < 2 GROUP BY customer.id ORDER BY customer.name";
	} else {
		$s_sql = "SELECT creditor.*, customer.* FROM customer
		JOIN contactperson ON contactperson.customerId = customer.id
		JOIN creditor ON creditor.customer_id = customer.id
		WHERE contactperson.email = ?  AND customer.content_status < 2 GROUP BY customer.id ORDER BY customer.name";
	}

	$o_query = $o_main->db->query($s_sql, array($email));
	$customers = $o_query ? $o_query->result_array() : array();

	$returnCustomers = array();
	foreach($customers as $customer) {
		$s_sql = "SELECT * FROM contactperson WHERE contactperson.email = ? AND contactperson.customerId = ?";
		$o_query = $o_main->db->query($s_sql, array($email, $customer['id']));
		$v_subrow = $o_query ? $o_query->row_array() : array();
		$isAdmin = true;
		$correctSubscriptionType = true;

		if($v_data['params']['adminCheck']) {
			$isAdmin = false;
			if($v_subrow['admin']) {
				$isAdmin = true;
			}
		}
		if($v_data['params']['subscriptionType']) {
			$correctSubscriptionType = false;

			$s_sql = "SELECT * FROM subscriptionmulti WHERE subscriptionmulti.customerId = ?";
			$o_query = $o_main->db->query($s_sql, array($customer['id']));
			$subscriptions = $o_query ? $o_query->result_array() : array();
			foreach($subscriptions as $subscription){
				if($subscription['subscriptiontype_id'] == $v_data['params']['subscriptionType']) {
					$correctSubscriptionType = true;
				}
			}
		}

		if($isAdmin || $v_data['params']['useradmin']){
			if($correctSubscriptionType){
				if($v_subrow['email']!="" && !$v_data['params']['useradmin'])
				{
					$o_membersystem = json_decode(APIconnectAccount("membersystemcompanyaccessusernameget", $v_accountinfo['accountname'], $v_accountinfo['password'], array("COMPANY_ID"=>$companyID, "USER"=>$v_subrow['email'], "MEMBERSYSTEMID"=>$customer['id'], "ACCESSLEVEL"=>$v_membersystem_config['access_level'], "MODULE"=>"Customer2")));
				}

				$l_membersystem_id = $v_subrow[$v_membersystem_config['content_id_field']];
				$imgToDisplay = "";
				$member = $o_membersystem->data;
				if($member || $v_data['params']['useradmin']){
					array_push($returnCustomers, $customer);
			    }
			}
		}
	}
	$v_return['data'] = $returnCustomers;
} else {

	$v_return['data'] = "Incorrect params";
}

?>
