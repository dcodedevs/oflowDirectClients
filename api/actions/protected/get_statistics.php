<?php
$s_date_from = date('Y-m-d 00:00:00', strtotime($v_data['date_from']));
$s_date_to = date('Y-m-d 23:59:59', strtotime($v_data['date_to']));

$o_query = $o_main->db->query("SELECT COUNT(created) AS cnt FROM sys_log WHERE created >= '".$o_main->db->escape_str($s_date_from)."' AND created <= '".$o_main->db->escape_str($s_date_to)."'");

if($o_query && $o_query->num_rows()>0)
{
	$v_row = $o_query->row_array();
	$v_return['total'] = $v_row['cnt'];
	$v_return['status'] = 1;
}

$o_query = $o_main->db->query("SELECT module, COUNT(created) AS cnt FROM sys_log WHERE created >= '".$o_main->db->escape_str($s_date_from)."' AND created <= '".$o_main->db->escape_str($s_date_to)."' GROUP BY module");

if($o_query && $o_query->num_rows()>0)
{
	$v_return['module_total_clicks'] = array();
	foreach($o_query->result_array() as $v_row)
	{
		$v_return['module_total_clicks'][$v_row['module']] = $v_row['cnt'];
	}
	$v_return['status'] = 1;
}

$o_query = $o_main->db->query("SELECT module, COUNT(created) AS cnt FROM sys_log WHERE created >= '".$o_main->db->escape_str($s_date_from)."' AND created <= '".$o_main->db->escape_str($s_date_to)."' AND url LIKE '%&updatepath=1' GROUP BY module");

if($o_query && $o_query->num_rows()>0)
{
	$v_return['module_total_opens'] = array();
	foreach($o_query->result_array() as $v_row)
	{
		$v_return['module_total_opens'][$v_row['module']] = $v_row['cnt'];
	}
	$v_return['status'] = 1;
}