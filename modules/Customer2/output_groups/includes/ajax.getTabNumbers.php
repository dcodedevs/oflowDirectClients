<?php

if(!function_exists("APIconnectorUser")) include_once(__DIR__."/../../../../fw/account_fw/includes/APIconnector.php");

$tab_id = $_POST['tab_id'];
$city_filter = $_POST['city_filter'];
$search_filter = $_POST['search_filter'];
$selfdefinedfield_filter = $_POST['selfdefinedfield_filter'];
$activecontract_filter = $_POST['activecontract_filter'];


$customerList = array();
$departments = array();
$groups = array();

$sql = "SELECT p.* FROM contactperson_group p WHERE p.status = 1 AND p.group_type = 1 ORDER BY p.name";
$o_query = $o_main->db->query($sql);
$allGroups = $o_query ? $o_query->result_array(): array();

foreach($allGroups as $allGroup){
	if(intval($allGroup['department']) == 1){
		array_push($departments, $allGroup);
	} else {
		array_push($groups, $allGroup);
	}
}

$customerList = $groups;
$itemCount = 0;
foreach($customerList as $v_row)
{
	$isMember = false;

	$sql = "SELECT p.* FROM contactperson p WHERE p.email = ? AND p.content_status < 2";
	$o_query = $o_main->db->query($sql, array($variables->loggID));
	$currentContactPerson = $o_query ? $o_query->row_array(): array();

	$sql = "SELECT p.* FROM contactperson_group_user p WHERE p.contactperson_id = ? AND p.contactperson_group_id = ?";
	$o_query = $o_main->db->query($sql, array($currentContactPerson['id'], $v_row['id']));
	$userGroupConnection = $o_query ? $o_query->row_array(): array();
	if($userGroupConnection){
		$isMember = true;
	}

    if(($isMember) || $v_row['show_group_to_all_in_group_list'] || $v_row['editableForAllUserInCrm'] || $accessElementAllow_SeeAllGroupsAndDepartmentsInList){
        $itemCount++;
    }

}
echo $itemCount;
?>
