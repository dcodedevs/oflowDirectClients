<?php
$s_username = $v_data['params']['username'];
$l_customer_id = $v_data['params']['customer_id'];

$o_query = $o_main->db->query("SELECT id FROM contactperson WHERE email = '".$o_main->db->escape_str($s_username)."' AND customerId = '".$o_main->db->escape_str($l_customer_id)."' AND (inactive IS NULL OR inactive < 1) AND admin = 1");
if($o_query && $o_query->num_rows()>0)
{
	$o_query = $o_main->db->query("SELECT id, user_registration, user_registration_link, user_registration_token, user_registration_domain FROM customer WHERE id = '".$o_main->db->escape_str($l_customer_id)."'");
	if($o_query && $o_query->num_rows()>0)
	{
		$v_return['status'] = 1;
		$v_return['data'] = $o_query->row_array();
	} else {
		$v_return['message'] = 'Error occurred getting data';
	}
} else {
	$v_return['message'] = 'Admin access required';
}
