<?php
$o_query = $o_main->db->query("SELECT numberOfUnits, municipality_id FROM companyinfo");

if($o_query && $o_query->num_rows()>0)
{
	$v_row = $o_query->row_array();
	$v_return['data'] = $v_row;
	$v_return['status'] = 1;
}