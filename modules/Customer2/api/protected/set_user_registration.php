<?php
$s_username = $v_data['params']['username'];
$l_customer_id = $v_data['params']['customer_id'];
$l_user_registration = intval($v_data['params']['user_registration']);
$s_user_registration_link = $v_data['params']['user_registration_link'];
$s_user_registration_domain = $v_data['params']['user_registration_domain'];

$o_query = $o_main->db->query("SELECT id FROM contactperson WHERE email = '".$o_main->db->escape_str($s_username)."' AND customerId = '".$o_main->db->escape_str($l_customer_id)."' AND (inactive IS NULL OR inactive < 1) AND admin = 1");
if($o_query && $o_query->num_rows()>0)
{
	if($l_user_registration < 1) $s_user_registration_link = '';
	if($l_user_registration < 2) $s_user_registration_domain = '';
	$s_user_registration_token = '';
	$v_link = explode('register.php?t=', $s_user_registration_link);
	if($v_link[1] != '') $s_user_registration_token = $v_link[1];
	
	$v_update = array(
		'updatedBy' => $s_username,
		'updated' => date('Y-m-d H:i:s'),
		'user_registration' => $l_user_registration,
		'user_registration_link' => $s_user_registration_link,
		'user_registration_token' => $s_user_registration_token,
		'user_registration_domain' => $s_user_registration_domain
	);
	$o_query = $o_main->db->update('customer', $v_update, array('id' => $l_customer_id));
	if($o_query)
	{
		$v_return['status'] = 1;
	} else {
		$v_return['message'] = 'Error occurred updating data';
	}
} else {
	$v_return['message'] = 'Admin access required';
}
