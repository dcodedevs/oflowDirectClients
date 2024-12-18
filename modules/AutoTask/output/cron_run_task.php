<?php
/*
 * Version 8.201
*/
ob_start();
$v_return = array(
	'items' => array(),
);
define('BASEPATH', realpath(__DIR__.'/../../../').'/');
require_once(BASEPATH.'elementsGlobal/cMain.php');
$s_sql = "SELECT * FROM auto_task WHERE next_run <= NOW() AND content_status = 0 ORDER BY next_run";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0)
foreach($o_query->result_array() as $v_row)
{
	$b_add = TRUE;
	// Check if task is handled 1 - Queued, 2 - Running, 3 - Stopped
	$o_find = $o_main->db->query("SELECT id FROM auto_task_log WHERE auto_task_id = '".$o_main->db->escape_str($v_row['id'])."' AND status <= 2 /*AND started > DATE_SUB(NOW(), INTERVAL -12 HOUR)*/");
	if($o_find && $o_find->num_rows()>0)
	{
		$b_add = FALSE;
	}
	if($b_add && is_file(BASEPATH.$v_row['script_path']))
	{
		$o_main->db->query("INSERT INTO auto_task_log SET auto_task_id = '".$o_main->db->escape_str($v_row['id'])."', status = 0, created = NOW()");
		$v_item = array();
		$v_item['auto_task_id'] = $v_row['id'];
		$v_item['script_path'] = BASEPATH.$v_row['script_path'];
		$v_item['runtime'] = $v_row['next_run'];
		$v_return['items'][] = $v_item;
	}
}
ob_end_clean();
echo json_encode($v_return);