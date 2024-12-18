<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);
	if(isset($_POST['fix'])) {
		$s_sql = "SELECT c.publicRegisterId, c.id FROM customer c WHERE c.publicRegisterId <> '' AND c.updatedBy <> 'fix script 2' AND c.publicRegisterId <> 0 LIMIT 5000";
		$o_query = $o_main->db->query($s_sql);
		$customers = ($o_query ? $o_query->result_array() : array());
		$updated_count = 0;
		foreach($customers as $customer){
			$regNr = preg_replace('/[^0-9]+/', '', $customer['publicRegisterId']);
			$s_sql_update = ",extra1='".$o_main->db->escape_str($customer['publicRegisterId'])."', publicRegisterId = '".$o_main->db->escape_str($regNr)."'";

			if($s_sql_update != "") {
				$s_sql = "UPDATE customer SET
				updated = now(),
				updatedBy='fix script 2'".$s_sql_update."
				WHERE id = '".$o_main->db->escape_str($customer['id'])."'";
				$o_query = $o_main->db->query($s_sql);
				if($o_query){
					$updated_count++;
				}
			}
		}
		echo $updated_count." customers updated";
	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">
			<input type="submit" name="fix" value="Fix customer organization number">
		</div>
	</form>
</div>
