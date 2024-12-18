<?php
	if(isset($_POST['submitImportData'])) {

		$sql = "SELECT c.* FROM invoice e
		LEFT OUTER JOIN customer c ON c.id = e.customerId
		LEFT OUTER JOIN customer_externalsystem_id cei ON cei.customer_id = c.id
		WHERE cei.external_id is null OR cei.external_id = 0
		GROUP BY c.id";
		$o_query = $o_main->db->query($sql);
		$customersWithoutExternalId = $o_query ? $o_query->result_array() : array();
		foreach($customersWithoutExternalId as $customerWithoutExternalId) {
			echo $formText_CustomerId_output." <a href='index.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Customer2&folderfile=output&folder=output&inc_obj=details&cid=".$customerWithoutExternalId['id']."' target='_blank'>".$customerWithoutExternalId['id']."</a></br>";
		}
	}
?>
<div>
	<form name="importData" method="post"  action="" >
		<div class="formRow submitRow">
			<input type="submit" name="submitImportData" value="Check invoices">
		</div>
	</form>
</div>
