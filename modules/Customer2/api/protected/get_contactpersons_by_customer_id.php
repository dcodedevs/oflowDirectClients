<?php
$s_username = $v_data['params']['username'];
$l_customer_id = $v_data['params']['customer_id'];
$o_query = $o_main->db->query("SELECT id FROM contactperson WHERE email = '".$o_main->db->escape_str($s_username)."' AND customerId = '".$o_main->db->escape_str($l_customer_id)."' AND (inactive IS NULL OR inactive < 1)");
if($o_query && $o_query->num_rows()>0)
{
	$v_return['contactpersons'] = array();
	$o_query = $o_main->db->query("SELECT id, name, email, mobile, admin FROM contactperson WHERE customerId = '".$o_main->db->escape_str($l_customer_id)."' AND (inactive IS NULL OR inactive < 1)");
	if($o_query && $o_query->num_rows()>0)
	{
		$v_return['status'] = 1;
		foreach($o_query->result_array() as $v_row)
		{
			$v_return['contactpersons'][] = $v_row;
		}
	}
} else {
	$v_return['message'] = 'No access to this customer';
}
