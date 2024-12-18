<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);

	if(isset($_POST['checkCreditorNames'])) {
		$s_sql = "SELECT cred.*, c.name as customerName FROM creditor cred
		LEFT OUTER JOIN customer c ON c.id = cred.customer_id
		ORDER BY cred.created DESC";
		$o_query = $o_main->db->query($s_sql);
		$creditors = ($o_query ? $o_query->result_array() : array());

		foreach($creditors as $creditor) {
			if(trim(mb_strtolower($creditor['companyname'], 'UTF-8')) != trim(mb_strtolower($creditor['customerName'], 'UTF-8'))) {
				echo $formText_CreditorHasWrongCustomerConnected_output." ".$creditor['companyname']." - ".$creditor['customerName']."<br/>";
			}
		}
	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">
			<input type="submit" name="checkCreditorNames" value="Check creditor names">
		</div>
	</form>
</div>
