<?php
$people_contactperson_type = 2;
$sql = "SELECT * FROM accountinfo_basisconfig ORDER BY id";
$o_query = $o_main->db->query($sql);
$accountinfo_basisconfig = $o_query ? $o_query->row_array() : array();
if(intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']) > 0){
	$people_contactperson_type = intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']);
}
if(intval($variables->accountinfo['contactperson_type_to_use_in_people']) > 0)
{
	$people_contactperson_type = $variables->accountinfo['contactperson_type_to_use_in_people'];
}

$v_current_module=explode('/modules/', __DIR__);
$s_current_module=$v_current_module[1];

$v_current_module=explode('/', $s_current_module);
$s_current_module=$v_current_module[0];
$s_current_module_folder=$v_current_module[1];
if(!isset($accessElementAllow_SeeAllGroupsAndDepartmentsInList)){
    include_once(__DIR__."/readAccessElements.php");
}
$sql = "SELECT p.* FROM contactperson p WHERE (p.id is not null) AND p.content_status < 2 AND type = ?";
$o_query = $o_main->db->query($sql, array($people_contactperson_type));
$peopleCount = $o_query ? $o_query->num_rows(): array();

$sql = "SELECT p.* FROM contactperson p WHERE (p.id is not null) AND p.content_status = 2 AND type = ?";
$o_query = $o_main->db->query($sql, array($people_contactperson_type));
$deletedPeopleCount = $o_query ? $o_query->num_rows(): array();

$allGroups = array();
$departmentCount = 0;
$groupCount = 0;
$departments = array();

$sql = "SELECT p.* FROM contactperson_group p WHERE p.status = 1 ORDER BY p.name";
$o_query = $o_main->db->query($sql);
$allGroups = $o_query ? $o_query->result_array(): array();

foreach($allGroups as $allGroup) {
	//current user information
	$isMember = false;

	$sql = "SELECT p.* FROM contactperson p WHERE p.email = ? AND p.content_status < 2";
	$o_query = $o_main->db->query($sql, array($variables->loggID));
	$currentContactPerson = $o_query ? $o_query->row_array(): array();

	$sql = "SELECT p.* FROM contactperson_group_user p WHERE p.contactperson_id = ? AND p.contactperson_group_id = ?";
	$o_query = $o_main->db->query($sql, array($currentContactPerson['id'], $allGroup['id']));
	$userGroupConnection = $o_query ? $o_query->row_array(): array();

	if($userGroupConnection) {
		$isMember = true;
	}

	if($isMember || $allGroup['show_group_to_all_in_group_list'] || $accessElementAllow_SeeAllGroupsAndDepartmentsInList){
		if(intval($allGroup['department']) == 1){
			$departmentCount++;
			array_push($departments, $allGroup);
		} else {
			$groupCount++;
		}
	}
}

?>
