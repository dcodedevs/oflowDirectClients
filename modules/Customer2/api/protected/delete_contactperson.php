<?php
$s_username = $v_data['params']['username'];
$l_customer_id = $v_data['params']['customer_id'];
$l_contactperson_id = $v_data['params']['contactperson_id'];

$o_query = $o_main->db->query("SELECT cp.id FROM contactperson cp JOIN contactperson cp_check ON cp_check.customerId = cp.customerId WHERE cp_check.id = '".$o_main->db->escape_str($l_contactperson_id)."' AND cp.email = '".$o_main->db->escape_str($s_username)."' AND cp.customerId = '".$o_main->db->escape_str($l_customer_id)."' AND (cp.inactive IS NULL OR cp.inactive < 1) AND cp.admin = 1");
if($o_query && $o_query->num_rows()>0)
{
	if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");	

	$s_sql = "select * from customer_stdmembersystem_basisconfig";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0) $v_membersystem_config = $o_query->row_array(); 
	
	$s_sql = "SELECT * FROM customer_basisconfig ORDER BY id DESC";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0) $customer_basisconfig = $o_query->row_array();
	
	$s_sql = "select * from accountinfo";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0) $v_accountinfo = $o_query->row_array(); 
	
	$s_sql = "select * from contactperson where id = '".$o_main->db->escape_str($l_contactperson_id)."'";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0) $v_row = $o_query->row_array(); 
	
	$l_membersystem_id = $v_row[$v_membersystem_config['content_id_field']];
	
	$b_delete = TRUE;
	if($customer_basisconfig['activateContactPersonAccess'])
	{
		$v_data = json_decode(APIconnectAccount("accountcompanyinfoget", $v_accountinfo['accountname'], $v_accountinfo['password'], array()), TRUE);
		$companyinfo = $v_data['data'];
		
		if($v_row['email']!="")
		{
			$o_membersystem = json_decode(APIconnectAccount("membersystemcompanyaccessusernameget", $accountinfo['accountname'], $accountinfo['password'], array("COMPANY_ID"=>$companyinfo['id'], "USER"=>$v_row['email'], "MEMBERSYSTEMID"=>$l_membersystem_id, "ACCESSLEVEL"=>$v_membersystem_config['access_level'], "MODULE"=>'Customer2')));
			if(is_object($o_membersystem->data))
			{
				$s_response = APIconnectAccount("membersystemcompanyaccessdelete", $v_accountinfo['accountname'], $v_accountinfo['password'], array("COMPANY_ID"=>$companyinfo['id'], "USER"=>$v_row['email'], "MEMBERSYSTEMID"=>$l_membersystem_id, "ACCESSLEVEL"=>$v_membersystem_config['access_level']));
				$v_response = json_decode($s_response, TRUE);
				
				if($v_response['data'] == "OK")
				{
					$o_query = $o_main->db->query("DELETE FROM contactperson WHERE id = '".$o_main->db->escape_str($l_contactperson_id)."'");
					if(!$o_query)
					{
						$b_delete = FALSE;
						$v_return['message'] = 'Error occured processing request'.$s_response;
					}
				} else {
					$b_delete = FALSE;
					$v_return['message'] = 'Error occured processing request'.$s_response;
				}
			}
		}
	}
	$b_synced = TRUE;
	$b_delete_in_external_systems = TRUE;
	$contactpersonId = $l_contactperson_id;
	$s_include_file = __DIR__.'/../../../ContactpersonAccess/output/includes/perform_contactperson_sync.php';
	if(is_file($s_include_file)) include($s_include_file);
	if(!$b_synced) $b_delete = FALSE;
	
	if($b_delete)
	{
		$o_query = $o_main->db->query("DELETE FROM contactperson WHERE id = '".$o_main->db->escape_str($l_contactperson_id)."'");
		if($o_query)
		{
			$v_return['status'] = 1;
		} else {
			$v_return['message'] = 'Error occured processing request';
		}
	}
} else {
	$v_return['message'] = 'Admin access required';
}
