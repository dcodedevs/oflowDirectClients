<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);
	if(isset($_POST['fix'])) {
		$s_sql = "SELECT c.invoiceEmail, c.phone, c.extra_invoice_email, c.extra_phone, c.extraName, c.id FROM customer c WHERE c.extraName <> ''";
		$o_query = $o_main->db->query($s_sql);
		$customers = ($o_query ? $o_query->result_array() : array());
		foreach($customers as $customer){
			$s_sql_update = "";
			if($customer['extra_invoice_email'] == ""){
				$s_sql_update = ", extra_invoice_email = '".$o_main->db->escape_str($customer['invoiceEmail'])."'";
			}		
			if($customer['extra_phone'] == ""){
				$s_sql_update .= ", extra_phone = '".$o_main->db->escape_str($customer['phone'])."'";
			}	
			if($s_sql_update != ""){
				$s_sql = "UPDATE customer SET
				updated = now(),
				updatedBy='fix script'".$s_sql_update."
				WHERE id = '".$o_main->db->escape_str($customer['id'])."'";
				$o_query = $o_main->db->query($s_sql);
				// var_dump($o_query);
			}
		}
	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">
			<input type="submit" name="fix" value="Fix extra invoice email and extra phone">
		</div>
	</form>
</div>
