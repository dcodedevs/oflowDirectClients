<?php
	$people_contactperson_type = 2;
	$sql = "SELECT * FROM accountinfo_basisconfig ORDER BY id";
	$o_query = $o_main->db->query($sql);
	$accountinfo_basisconfig = $o_query ? $o_query->row_array() : array();
	if(intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']) > 0){
		$people_contactperson_type = intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']);
	}
	$o_query = $o_main->db->get('accountinfo');
	$accountinfo = $o_query ? $o_query->row_array() : array();
	if(intval($accountinfo['contactperson_type_to_use_in_people']) > 0)
	{
		$people_contactperson_type = $accountinfo['contactperson_type_to_use_in_people'];
	}
	// error_reporting(E_ALL);
	// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);
	if(isset($_POST['Migrate'])) {

		$s_sql = "SELECT * FROM subscriptionmulti";
		$o_query = $o_main->db->query($s_sql);
		$subscriptions = $o_query ? $o_query->result_array() : array();
		$updatedSuccessfully = array();
		foreach($subscriptions as $subscription) {
			if($subscription['previous_customer_id'] != "" && $subscription['customerId'] > 0){
				$s_sql = "UPDATE customer SET previous_sys_id = ? WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($subscription['previous_customer_id'], $subscription['customerId']));
				if($o_query){
					$updatedSuccessfully[$subscription['customerId']] = 1;
				}
			}
		}
		echo count($updatedSuccessfully)." ".$formText_CustomersUpdated_output;
	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">

			<input type="submit" name="Migrate" value="Update previous customer id from subscriptions">

		</div>
	</form>
</div>
