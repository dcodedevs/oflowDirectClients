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
	
	$l_updated = $l_created = $l_failed = 0;
	$o_query = $o_main->db->query("SELECT * FROM customer WHERE content_status < 2");
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $v_customer)
	{
		if(isset($v_customer_accountconfig['activate_create_folders_in_files_for_new_customer']) && 1 == $v_customer_accountconfig['activate_create_folders_in_files_for_new_customer'])
		{
			$v_folders = explode(";", $v_customer_accountconfig['specify_folders_in_files_for_new_customer']);
			foreach($v_folders as $s_folder)
			{
				$l_parent_id = 0;
				$v_items = explode("/", $s_folder);
				foreach($v_items as $s_item)
				{
					if('' == trim($s_item)) continue;
					
					$s_sql = "SELECT id FROM customer_folders WHERE customer_id = '".$o_main->db->escape_str($v_customer['id'])."' AND name = '".$o_main->db->escape_str($s_item)."'";
					$o_find = $o_main->db->query($s_sql);
					if($o_find && $o_find->num_rows() > 0)
					{
						$v_row = $o_find->row_array();
						$l_parent_id = $v_row['id'];
					} else {
						$s_sql = "INSERT INTO customer_folders SET id=NULL, moduleID = '".$o_main->db->escape_str($moduleID)."', created = NOW(), createdBy= 'FIX_SCRIPT', name = '".$o_main->db->escape_str($s_item)."', parent_id = '".$o_main->db->escape_str($l_parent_id)."', customer_id = '".$o_main->db->escape_str($v_customer['id'])."'";
						$o_update = $o_main->db->query($s_sql);
						if(!$o_update) $l_failed++;
						$l_created++;
						$l_parent_id = $o_main->db->insert_id();
					}
				}
			}
			$l_updated++;
		}
	}
	
	echo 'Fixed folders for '.$l_updated.' customers<br><br>Created folders: '.$l_created.'<br><br>Failed folders: '.$l_failed;
}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >
		<div class="formRow submitRow">
			<input type="submit" name="submitImportData" value="Create folders as set in accountconfig">
		</div>
	</form>
</div>
