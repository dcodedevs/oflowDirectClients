<?php
$people_contactperson_type = 2;
$sql = "SELECT * FROM accountinfo_basisconfig ORDER BY id";
$o_query = $o_main->db->query($sql);
$accountinfo_basisconfig = $o_query ? $o_query->row_array() : array();
if(intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']) > 0){
	$people_contactperson_type = intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']);
}
$o_query = $o_main->db->get('accountinfo');
$accountinfo = $o_query ? $o_query->row_array() : array();
if(intval($accountinfo['contactperson_type_to_use_in_people']) > 0)
{
	$people_contactperson_type = $accountinfo['contactperson_type_to_use_in_people'];
}

set_time_limit(300);
ini_set('memory_limit', '256M');

// no cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// content type
header("Content-Disposition: attachment; filename=\"eksport.csv\"");
header("Content-type: text/csv; charset=UTF-8");

if(!is_array($_POST['selected'])) $_POST['selected'] = explode(",", $_POST['selected']);

$v_membersystem = array();
$v_membersystem_membership = array();
$v_registered_usernames = array();
$o_query = $o_main->db->query("SELECT * FROM cache_userlist_access");
$v_cache_userlist = $o_query ? $o_query->result_array() : array();
foreach($v_cache_userlist as $v_user_cached_info) {
	$v_membersystem[$v_user_cached_info['username']] = $v_user_cached_info;
    if($v_user_cached_info['user_id'] > 0) $v_registered_usernames[] = $v_user_cached_info['username'];
}

$o_query = $o_main->db->query("SELECT * FROM cache_userlist_membershipaccess");
$v_cache_userlist_membership = $o_query ? $o_query->result_array() : array();
foreach($v_cache_userlist_membership as $v_user_cached_info) {
	$v_membersystem_membership[$v_user_cached_info['username']] = $v_user_cached_info;
    if($v_user_cached_info['user_id'] > 0) $v_registered_usernames[] = $v_user_cached_info['username'];
}

$s_csv_data = '';
$l_cp_max_count = 0;
$o_query = $o_main->db->query("SELECT * FROM contactperson WHERE content_status < 2 AND type = '".$o_main->db->escape_str($people_contactperson_type)."' ORDER BY name");
if($o_query && $o_query->num_rows()>0)
foreach($o_query->result_array() as $v_customer)
{
	if(empty($v_customer['position']) && '' != trim($v_customer['title']))
	{
		$o_find = $o_main->db->query("SELECT * FROM people_position WHERE name = '".$o_main->db->escape_str($v_customer['title'])."'");
		$v_position = $o_find ? $o_find->row_array() : array();
		if(isset($v_position['id']) && 0 < $v_position['id'])
		{
			$v_customer['position'] = $v_position['id'];
			$o_main->db->query("UPDATE contactperson SET title = NULL, position = '".$o_main->db->escape_str($v_customer['position'])."' WHERE id = '".$o_main->db->escape_str($v_customer['id'])."'");
		} else {
			$o_main->db->query("INSERT INTO people_position SET created = NOW(), createdBy = '".$o_main->db->escape_str($_COOKIE['username'])."', name = '".$o_main->db->escape_str($v_customer['title'])."'");
			$v_customer['position'] = $o_main->db->insert_id();
			$o_main->db->query("UPDATE contactperson SET title = NULL, position = '".$o_main->db->escape_str($v_customer['position'])."' WHERE id = '".$o_main->db->escape_str($v_customer['id'])."'");
		}
	}
	$o_find = $o_main->db->query("SELECT * FROM people_position WHERE id = '".$o_main->db->escape_str($v_customer['position'])."'");
	$v_position = $o_find ? $o_find->row_array() : array();

	//re set information for getynet registered
	if(isset($v_membersystem[$v_customer['email']]) || isset($v_membersystem_membership[$v_customer['email']]))
	{
		unset($member);
		if(isset($v_membersystem[$v_customer['email']])){
			$member = $v_membersystem[$v_customer['email']];
		} else if(isset($v_membersystem_membership[$v_customer['email']])){
			$member = $v_membersystem_membership[$v_customer['email']];
		}
		if($member['user_id'] > 0)
		{
			if($member['first_name'] != "") {
				$v_customer['name'] = $member['first_name'];
				$v_customer['middle_name'] = $member['middle_name'];
				$v_customer['last_name'] = $member['last_name'];
			}
			if($member['mobile'] != "") {
				$v_customer['phone'] = $member['mobile'];
			}
		}
	}
	
	$s_line = '';
	$s_line = trim($v_customer['external_employee_id']).";".trim($v_customer['name']).";".trim($v_customer['middle_name']).";".trim($v_customer['last_name']).";".trim($v_customer['phone']).";".trim($v_customer['email']).";".trim($v_position['id']).";".trim($v_position['name']);

	$s_csv_data .= mb_convert_encoding($s_line."\r\n", 'UTF-8');
}

$s_csv_content = $formText_EmployeeId_Output.";".$formText_Name_Output.";".$formText_MiddleName_Output.";".$formText_LastName_Output.";".$formText_Phone_Output.";".$formText_Email_Output.";".$formText_JobId_Output.";".$formText_JobTitle_Output;

$s_csv_content = mb_convert_encoding($s_csv_content."\r\n", 'UTF-8');

$s_csv_content .= $s_csv_data;
echo $s_csv_content;
exit;
