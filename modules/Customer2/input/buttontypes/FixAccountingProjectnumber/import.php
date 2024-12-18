<?php
ini_set('max_execution_time', 120);

if(isset($_POST['submitImportData']))
{
	$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
	$o_query = $o_main->db->query($s_sql);
	$v_customer_accountconfig = array();
	if($o_query && $o_query->num_rows()>0) {
		$v_customer_accountconfig = $o_query->row_array();
	}

	if(1 == $_POST['action'])
	{
		$updated = 0;
		$s_sql = "SELECT * FROM customer WHERE accounting_project_number IS NULL OR accounting_project_number = '' OR accounting_project_number = 0";
		$o_query = $o_main->db->query($s_sql);
		if($o_query && $o_query->num_rows()>0)
		foreach($o_query->result_array() as $v_customer)
		{
			$o_main->db->query("INSERT INTO projectforaccounting SET id = NULL, moduleID = '".$o_main->db->escape_str($moduleID)."', createdBy = '".$o_main->db->escape_str($variables->loggID)."', created = NOW(), projectnumber = id, name = '".$o_main->db->escape_str($v_customer['name'])."', ownercompany_id = '".$o_main->db->escape_str($v_customer_accountconfig['accounting_project_number_ownercompany_id'])."', parentId = '', parentNumber = ''");
			$l_projectforaccounting_id = $o_main->db->insert_id();
			if(0 < $l_projectforaccounting_id)
			{
				$o_main->db->query("UPDATE projectforaccounting SET projectnumber = '".$o_main->db->escape_str($l_projectforaccounting_id)."' WHERE id = '".$o_main->db->escape_str($l_projectforaccounting_id)."'");
				if($o_main->db->query("UPDATE customer SET accounting_project_number = '".$o_main->db->escape_str($l_projectforaccounting_id)."' WHERE id = '".$o_main->db->escape_str($v_customer['id'])."'"))
				{
					$updated++;
				}
			}
		}
		echo $updated." customers updated.<br><br>";
	}
	elseif(2 == $_POST['action'])
	{
		$updated = 0;
		$s_sql = "UPDATE customer_collectingorder AS co INNER JOIN customer AS c ON c.id = co.customerId AND c.accounting_project_number <> '' AND c.accounting_project_number IS NOT NULL SET co.accountingProjectCode = c.accounting_project_number WHERE co.accountingProjectCode IS NULL OR co.accountingProjectCode = '' OR co.accountingProjectCode = 0";
		$o_query = $o_main->db->query($s_sql);
		if($o_query) $updated = $o_main->db->affected_rows();
		echo $updated." collecting orders updated.<br><br>";
		
		$updated = 0;
		$s_sql = "UPDATE subscriptionmulti AS sm INNER JOIN customer AS c ON c.id = sm.customerId AND c.accounting_project_number <> '' AND c.accounting_project_number IS NOT NULL SET sm.projectId = c.accounting_project_number WHERE sm.projectId IS NULL OR sm.projectId = '' OR sm.projectId = 0";
		$o_query = $o_main->db->query($s_sql);
		if($o_query) $updated = $o_main->db->affected_rows();
		echo $updated." repeating orders updated.<br><br>";
		
		$updated = 0;
		$s_sql = "UPDATE project2 AS p INNER JOIN customer AS c ON c.id = p.customerId SET p.projectCode = c.accounting_project_number WHERE p.projectCode IS NULL OR p.projectCode = '' OR p.projectCode = 0";
		$o_query = $o_main->db->query($s_sql);
		if($o_query) $updated = $o_main->db->affected_rows();
		echo $updated." projects updated.<br><br>";
	} else {
		echo "Incorrect action requested.<br><br>";
	}
}
?>
<div>
	<form id="output-button-form" name="importData" method="post" enctype="multipart/form-data"  action="" >
		<input type="hidden" name="submitImportData" value="1">
		<input type="hidden" id="output-button-form-action" name="action" value="">
		<div class="formRow submitRow">
			<input type="button" name="submitImportData" value="Fix accounting project code" onClick="$('#output-button-form-action').val(1); $('#output-button-form').submit();">
			<input type="button" name="submitImportData" value="Apply project code from customer to repeating order, collecting order and project2" onClick="$('#output-button-form-action').val(2); $('#output-button-form').submit();">
		</div>
	</form>
</div>
