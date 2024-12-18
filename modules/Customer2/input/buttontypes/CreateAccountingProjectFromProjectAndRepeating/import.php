<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);

	$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
	$o_query = $o_main->db->query($s_sql);
	$v_customer_accountconfig = $o_query ? $o_query->row_array() : array();
	if(isset($_POST['create_accounting'])) {
		if($v_customer_accountconfig['next_available_projectcode'] > 0) {			//repeating orders

			$s_sql = "SELECT * FROM subscriptionmulti WHERE content_status < 2 AND (projectId = '' OR projectId is null)";
			$o_query = $o_main->db->query($s_sql);
			$repeatingOrders = $o_query ? $o_query->result_array() : array();
			$nextCode = $v_customer_accountconfig['next_available_projectcode'];
			foreach($repeatingOrders as $repeatingOrder) {
				$s_sql = "SELECT * FROM projectforaccounting WHERE projectnumber = '".$o_main->db->escape_str($nextCode)."'";

				$o_query = $o_main->db->query($s_sql);
				$projectExisting = $o_query ? $o_query->row_array() : array();
				if(!$projectExisting) {
					$s_sql = "INSERT INTO projectforaccounting SET
					created = now(),
					createdBy= '".$o_main->db->escape_str($variables->loggID)."',
					name= '".$o_main->db->escape_str($repeatingOrder['subscriptionName'])."',
					projectnumber = '".$o_main->db->escape_str($nextCode)."',
					ownercompany_id = '".$o_main->db->escape_str($repeatingOrder['ownercompany_id'])."',
					customer_id = '".$o_main->db->escape_str($repeatingOrder['customerId'])."'";
					$o_query = $o_main->db->query($s_sql);
					if($o_query) {
						$_POST['projectCode'] = $nextCode;

						$s_sql = "UPDATE subscriptionmulti SET
						updated = now(),
						updatedBy = '".$o_main->db->escape_str($variables->loggID)."',
						projectId = '".$o_main->db->escape_str($_POST['projectCode'])."'
						WHERE id = '".$o_main->db->escape_str($repeatingOrder['id'])."'";
						$o_query = $o_main->db->query($s_sql);

						$nextCode = $nextCode + 1;

						$s_sql = "UPDATE customer_accountconfig SET
						updated = now(),
						updatedBy = '".$o_main->db->escape_str($variables->loggID)."',
						next_available_projectcode = '".$o_main->db->escape_str($nextCode)."'
						WHERE id = '".$o_main->db->escape_str($v_customer_accountconfig['id'])."'";
						$o_query = $o_main->db->query($s_sql);
					} else {
						$fw_error_msg[] = $formText_ErrorAddingProjectCodeContactSystemDeveloper_output;
					}
				} else {
					$fw_error_msg[] = $formText_ProjectWithProjectCodeExistsTryAgain_output;
				}
			}
			$s_sql = "SELECT * FROM project2 WHERE content_status < 2 AND (type = 0 OR type is null OR type = 1) AND (projectCode = '' OR projectCode is null)";
			$o_query = $o_main->db->query($s_sql);
			$repeatingOrders = $o_query ? $o_query->result_array() : array();
			foreach($repeatingOrders as $repeatingOrder){
				//projects
				$s_sql = "SELECT * FROM projectforaccounting WHERE projectnumber = '".$o_main->db->escape_str($nextCode)."'";
				$o_query = $o_main->db->query($s_sql);
				$projectExisting = $o_query ? $o_query->row_array() : array();
				if(!$projectExisting) {
					$s_sql = "INSERT INTO projectforaccounting SET
					created = now(),
					createdBy= '".$o_main->db->escape_str($variables->loggID)."',
					name= '".$o_main->db->escape_str($repeatingOrder['name'])."',
					projectnumber = '".$o_main->db->escape_str($nextCode)."',
					customer_id = '".$o_main->db->escape_str($repeatingOrder['customerId'])."'";
					$o_query = $o_main->db->query($s_sql);
					if($o_query) {
						$_POST['projectCode'] = $nextCode;

						$s_sql = "UPDATE project2 SET
						updated = now(),
						updatedBy = '".$o_main->db->escape_str($variables->loggID)."',
						projectCode = '".$o_main->db->escape_str($_POST['projectCode'])."'
						WHERE id = '".$o_main->db->escape_str($repeatingOrder['id'])."'";
						$o_query = $o_main->db->query($s_sql);

						$nextCode = $nextCode + 1;

						$s_sql = "UPDATE customer_accountconfig SET
						updated = now(),
						updatedBy = '".$o_main->db->escape_str($variables->loggID)."',
						next_available_projectcode = '".$o_main->db->escape_str($nextCode)."'
						WHERE id = '".$o_main->db->escape_str($v_customer_accountconfig['id'])."'";
						$o_query = $o_main->db->query($s_sql);
					} else {
						$fw_error_msg[] = $formText_ErrorAddingProjectCodeContactSystemDeveloper_output;
					}
				} else {
					$fw_error_msg[] = $formText_ProjectWithProjectCodeExistsTryAgain_output;
				}
			}
		} else {
			echo $formText_MissingProjectCode_output;
		}
		if(count($fw_error_msg) > 0) {
			echo $formText_SomeErrorAppeared_output;
		}
	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">
			<input type="submit" name="create_accounting" value="Create Accountingproject from project and repeating orders">

		</div>
	</form>
</div>
