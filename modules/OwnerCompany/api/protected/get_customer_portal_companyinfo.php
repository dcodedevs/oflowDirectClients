<?php
$s_sql = "SELECT * FROM ownercompany ORDER BY id LIMIT 1";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0)
{
	$v_ownercompany = $o_query->row_array();
	$v_return['companyinfo'] = $v_ownercompany;
	
	$s_sql = "SELECT * FROM ownercompany_contacts WHERE ownercompany_id = '".$o_main->db->escape_str($v_ownercompany['id'])."' ORDER BY sortnr ASC";
	$o_query = $o_main->db->query($s_sql);
	$v_return['contacts'] = $o_query ? $o_query->result_array() : array();
	
	$s_sql = "SELECT * FROM ownercompany_qualityicons WHERE ownercompany_id = '".$o_main->db->escape_str($v_ownercompany['id'])."' ORDER BY id ASC";
	$o_query = $o_main->db->query($s_sql);
	$v_return['qualityicons'] = $o_query ? $o_query->result_array() : array();
    
	$v_return['status'] = 1;
	
} else {
	$v_return['message'] = 'Companyinfo does not exists';
}