<?php
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");
$email = $v_data['params']['email'];
$customer_id = $v_data['params']['customer_id'];

if($customer_id != "" && $email != "")
{
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

	$s_sql = "SELECT creditor.*, customer.* FROM customer
	JOIN creditor ON creditor.customer_id = customer.id
	JOIN integration24sevenoffice_session AS s ON s.creditor_id = creditor.id
	WHERE s.username = ? AND customer.id = ? AND customer.content_status < 2 GROUP BY customer.id ORDER BY customer.name";

	$o_query = $o_main->db->query($s_sql, array($email, $customer_id));
	$customers = $o_query ? $o_query->result_array() : array();
	$v_return['sql'] = $o_main->db->last_query();
	$returnCustomers = array();
	foreach($customers as $customer)
	{
		$correctSubscriptionType = true;
		
		if($v_data['params']['subscriptionType'])
		{
			$correctSubscriptionType = false;

			$s_sql = "SELECT * FROM subscriptionmulti WHERE subscriptionmulti.customerId = ?";
			$o_query = $o_main->db->query($s_sql, array($customer['id']));
			$subscriptions = $o_query ? $o_query->result_array() : array();
			foreach($subscriptions as $subscription)
			{
				if($subscription['subscriptiontype_id'] == $v_data['params']['subscriptionType'])
				{
					$correctSubscriptionType = true;
				}
			}
		}

		if($correctSubscriptionType)
		{
			array_push($returnCustomers, $customer);
		}
	}
	$v_return['data'] = $returnCustomers;
} else {

	$v_return['data'] = "Incorrect params";
}