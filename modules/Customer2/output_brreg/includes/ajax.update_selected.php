<?php
$o_query = $o_main->db->query("SELECT * FROM sys_cronjob WHERE content_id = 1 AND script_path = '".$o_main->db->escape_str('modules/Customer2/output_brreg/cron_sync_brreg.php')."'");
if($o_query && $o_query->num_rows()>0)
{
	$v_sys_cronjob = $o_query->row_array();
	if($v_sys_cronjob['status'] == 1)
	{
		$fw_error_msg = array($formText_UpdateCannotBeDoneWhileSyncIsRunning_Output);
		return;
	}
}

if(!is_array($_POST['skip'])) $_POST['skip'] = explode(",", $_POST['skip']);
if(!is_array($_POST['sync'])) $_POST['sync'] = explode(",", $_POST['sync']);
if(!is_array($_POST['nosync'])) $_POST['nosync'] = explode(",", $_POST['nosync']);

$o_query = $o_main->db->query("SELECT * FROM customer WHERE id IN ?", array($_POST['sync']));
if($o_query && $o_query->num_rows()>0)
foreach($o_query->result_array() as $v_customer)
{
	$s_sql_update = '';
	$o_find = $o_main->db->query("SELECT * FROM customer_sync_data WHERE customer_id = '".$o_main->db->escape_str($v_customer['id'])."'");
	if($o_find && $o_find->num_rows()>0)
	foreach($o_find->result_array() as $v_sync)
	{
		$s_sql_update .= ', '.$v_sync['field']." = '".$o_main->db->escape_str($v_sync['brreg_value'])."'";
	}
	$o_main->db->query("UPDATE customer SET updated = NOW(), updatedBy = '".$o_main->db->escape_str($_COOKIE['username'])."', brreg_compare_status = 2".$s_sql_update." WHERE id = '".$o_main->db->escape_str($v_customer['id'])."'");
}

$o_main->db->query("TRUNCATE customer_sync_data");

$o_main->db->query("UPDATE customer SET updated = NOW(), updatedBy = '".$o_main->db->escape_str($_COOKIE['username'])."', brreg_compare_status = 3 WHERE id IN ?", array($_POST['skip']));
$o_main->db->query("UPDATE customer SET updated = NOW(), updatedBy = '".$o_main->db->escape_str($_COOKIE['username'])."', brreg_compare_status = 4, notOverwriteByImport = 1 WHERE id IN ?", array($_POST['nosync']));