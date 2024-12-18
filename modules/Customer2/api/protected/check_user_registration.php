<?php
$s_token = $v_data['params']['token'];

$o_query = $o_main->db->query("SELECT id, user_registration, user_registration_link, user_registration_domain FROM customer WHERE user_registration_token = '".$o_main->db->escape_str($s_token)."'");
if($o_query && $o_query->num_rows()>0)
{
	$v_return['status'] = 1;
	$v_return['data'] = $o_query->row_array();
} else {
	$v_return['message'] = 'Token is not valid';
}
