<?php
$s_username = $v_data['params']['username'];
$l_customer_id = $v_data['params']['customer_id'];
$l_contactperson_id = $v_data['params']['contactperson_id'];
$l_admin = intval($v_data['params']['contactperson_admin']);

$o_query = $o_main->db->query("SELECT cp.id FROM contactperson cp JOIN contactperson cp_check ON cp_check.customerId = cp.customerId WHERE cp_check.id = '".$o_main->db->escape_str($l_contactperson_id)."' AND cp.email = '".$o_main->db->escape_str($s_username)."' AND cp.customerId = '".$o_main->db->escape_str($l_customer_id)."' AND (cp.inactive IS NULL OR cp.inactive < 1) AND cp.admin = 1");
if($o_query && $o_query->num_rows()>0)
{
	$o_query = $o_main->db->query("UPDATE contactperson SET admin = '".$l_admin."', updatedBy = '".$o_main->db->escape_str($s_username)."', updated = NOW() WHERE id = '".$o_main->db->escape_str($l_contactperson_id)."'");
	if($o_query)
	{
		$v_return['status'] = 1;
	}
} else {
	$v_return['message'] = 'Admin access required';
}
