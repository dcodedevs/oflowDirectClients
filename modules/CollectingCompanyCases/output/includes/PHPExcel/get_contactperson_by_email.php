<?php
$s_email = $v_data['params']['email'];
$o_query = $o_main->db->query("SELECT cp.id, cp.name, cp.email, cp.mobile, cp.admin, cp.customerId, c.name customer_name FROM contactperson cp JOIN customer c ON c.id = cp.customerId WHERE cp.email = '".$o_main->db->escape_str($s_email)."' AND (cp.inactive IS NULL OR cp.inactive < 1)");
if($o_query && $o_query->num_rows()>0)
{
	$v_return['contactperson'] = array();
	$v_return['status'] = 1;
	foreach($o_query->result_array() as $v_row)
	{
		$v_return['contactperson'][] = $v_row;
	}
} else {
	$v_return['message'] = 'Contactperson by specified email not exists';
}
