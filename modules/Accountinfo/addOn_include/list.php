<?php
$v_row = array();
$o_query = $o_main->db->query('SELECT * FROM accountinfo');
if($o_query && $o_query->num_rows()>0) $v_row = $o_query->row_array();
if($v_row['id'] < 1)
{
	$o_main->db->query("update accountinfo set id = 1 where 1");
}
include(__DIR__."/../input/includes/list.php");
?>