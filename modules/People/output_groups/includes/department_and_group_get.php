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

$s_sql = "select * from people_accountconfig";
$o_query = $o_main->db->query($s_sql);
$v_employee_accountconfig = ($o_query ? $o_query->row_array() : array());

$sql = "SELECT * FROM people_basisconfig ORDER BY id";
$o_query = $o_main->db->query($sql);
$v_employee_basisconfig = $o_query ? $o_query->row_array() : array();

foreach($v_employee_accountconfig as $key=>$value){
    if($value > 0){
        $v_employee_basisconfig[$key] = ($value - 1);
    }
}
$sql_where = "";
$sql_join = "";
if($v_employee_basisconfig['filter_by_subscription'] == 1) {
	$sql_join .= " LEFT OUTER JOIN customer c ON c.id = p.customerId
	LEFT OUTER JOIN subscriptionmulti s ON s.customerId = c.id";
	$sql_where .= " AND s.startDate <= NOW() AND (s.stoppedDate = '0000-00-00' OR s.stoppedDate is null or s.stoppedDate > NOW())";
} else if($v_employee_basisconfig['filter_by_subscription'] == 2){
	$type_ids = array();
	if(intval($v_employee_accountconfig['filter_by_subscription']) == 0){
		$type_ids = explode(",", $v_employee_basisconfig['specified_subscription_type_ids']);
	} else {
		$type_ids = explode(",", $v_employee_accountconfig['specified_subscription_type_ids']);
	}
	if(count($type_ids) > 0){
		$sql_join .= " LEFT OUTER JOIN customer c ON c.id = p.customerId
		LEFT OUTER JOIN subscriptionmulti s ON s.customerId = c.id
		LEFT OUTER JOIN subscriptiontype st ON st.id = s.subscriptiontype_id";
		$sql_where .= " AND s.startDate <= NOW() AND (s.stoppedDate = '0000-00-00' OR s.stoppedDate is null or s.stoppedDate > NOW()) AND st.id IN(".implode(',', $type_ids).")";
	}
}
if($v_employee_basisconfig['show_only_persons_marked_to_show_in_intranet'] == 1){
	$sql_where .= " AND p.show_in_intranet = 1";
}
$sql_where .= " AND (p.notVisibleInMemberOverview = 0 OR p.notVisibleInMemberOverview is null)";

if((!isset($variables->useradmin) || 0 == $variables->useradmin) && $v_employee_accountconfig['activateFilterByTags'])
{
	$v_property_ids = $v_property_group_ids = array();
	$s_sql = "SELECT cp.* FROM contactperson AS cp
	JOIN customer AS cus ON cus.id = cp.customerId AND cus.content_status < 2
	LEFT OUTER JOIN subscriptionmulti AS sm ON sm.customerId = cp.customerId AND sm.startDate <= CURDATE() AND (sm.stoppedDate = '0000-00-00' OR sm.stoppedDate is null OR sm.stoppedDate >= CURDATE())

	LEFT OUTER JOIN contactperson_subscription_connection AS csc ON csc.contactperson_id = cp.id
	LEFT OUTER JOIN subscriptionmulti AS sm2 ON csc.subscriptionmulti_id = sm2.id AND sm2.startDate <= CURDATE()
	AND (sm2.stoppedDate = '0000-00-00' OR sm2.stoppedDate is null OR sm2.stoppedDate >= CURDATE())

	WHERE cp.email = '".$o_main->db->escape_str($variables->loggID)."' AND (
	(IFNULL(cp.intranet_membership_subscription_type, 0) = 0 AND sm.id IS NOT NULL) OR
	(cp.intranet_membership_subscription_type = 1 AND sm2.id IS NOT NULL AND csc.id IS NOT NULL) OR
	cp.intranet_membership_subscription_type = 2
	)";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $v_contactperson)
	{
		$v_properties = array();
		if(intval($v_contactperson['intranet_membership_type']) == 0)
		{
			$s_sql = "SELECT imao.object_id, pgc.property_id FROM intranet_membership AS im
			JOIN intranet_membership_customer_connection AS im_cus ON im_cus.membership_id = im.id
			LEFT OUTER JOIN intranet_membership_attached_object AS imao ON imao.membership_id = im_cus.membership_id
			LEFT OUTER JOIN property_group_connection AS pgc ON pgc.property_group_id = imao.objectgroup_id AND imao.object_id = 0
			WHERE im_cus.customer_id = '".$o_main->db->escape_str($v_contactperson['customerId'])."'";
			$o_find = $o_main->db->query($s_sql);
			$v_properties = $o_find ? $o_find->result_array() : array();

		} else if($v_contactperson['intranet_membership_type'] == 1)
		{
			$s_sql = "SELECT imao.object_id, pgc.property_id FROM intranet_membership AS im
			JOIN intranet_membership_contactperson_connection AS im_cp ON im_cp.membership_id = im.id
			LEFT OUTER JOIN intranet_membership_attached_object AS imao ON imao.membership_id = im_cp.membership_id
			LEFT OUTER JOIN property_group_connection AS pgc ON pgc.property_group_id = imao.objectgroup_id AND imao.object_id = 0
			WHERE im_cp.contactperson_id = '".$o_main->db->escape_str($v_contactperson['id'])."'";
			$o_find = $o_main->db->query($s_sql);
			$v_properties = $o_find ? $o_find->result_array() : array();

		}
		foreach($v_properties as $v_item)
		{
			if(0 < $v_item['object_id'] && !in_array($v_item['object_id'], $v_property_ids))
			{
				array_push($v_property_ids, $v_item['object_id']);
			}
			if(0 < $v_item['property_id'] && !in_array($v_item['property_id'], $v_property_group_ids))
			{
				array_push($v_property_group_ids, $v_item['property_id']);
			}
		}
	}
	//echo 'PROP: '.implode(', ', $v_property_ids).'<br>';
	//echo 'GROUP_PROP: '.implode(', ', $v_property_group_ids).'<br>';
	$s_sql_a = '';
	$s_sql_b = '';
	if(0<count($v_property_ids))
	{
		$s_sql_a = "imao.object_id IN (".implode(', ', $v_property_ids).")";
		$s_sql_b = "imao2.object_id IN (".implode(', ', $v_property_ids).")";
	}
	if(0<count($v_property_group_ids))
	{
		$s_sql_a .= (''!=$s_sql_a?" OR ":'')."pgc.property_id IN (".implode(', ', $v_property_group_ids).")";
		$s_sql_b .= (''!=$s_sql_b?" OR ":'')."pgc2.property_id IN (".implode(', ', $v_property_group_ids).")";
	}

	$sql_join .= " JOIN contactperson AS cp ON cp.id = p.id
	JOIN customer AS cus ON cus.id = cp.customerId AND cus.content_status < 2
	LEFT OUTER JOIN subscriptionmulti AS sm ON sm.customerId = cp.customerId AND sm.startDate <= CURDATE() AND (sm.stoppedDate = '0000-00-00' OR sm.stoppedDate is null OR sm.stoppedDate >= CURDATE())

	LEFT OUTER JOIN contactperson_subscription_connection AS csc ON csc.contactperson_id = cp.id
	LEFT OUTER JOIN subscriptionmulti AS sm2 ON csc.subscriptionmulti_id = sm2.id AND sm2.startDate <= CURDATE()
	AND (sm2.stoppedDate = '0000-00-00' OR sm2.stoppedDate is null OR sm2.stoppedDate >= CURDATE())

	LEFT OUTER JOIN intranet_membership_customer_connection AS im_cus ON im_cus.customer_id = cp.customerId
	LEFT OUTER JOIN intranet_membership_attached_object AS imao ON imao.membership_id = im_cus.membership_id
	LEFT OUTER JOIN property_group_connection AS pgc ON pgc.property_group_id = imao.objectgroup_id AND imao.object_id = 0

	LEFT OUTER JOIN intranet_membership_contactperson_connection AS im_cp ON im_cp.contactperson_id = cp.id
	LEFT OUTER JOIN intranet_membership_attached_object AS imao2 ON imao2.membership_id = im_cp.membership_id
	LEFT OUTER JOIN property_group_connection AS pgc2 ON pgc2.property_group_id = imao2.objectgroup_id AND imao2.object_id = 0";

	$sql_where .= " AND (
	(IFNULL(cp.intranet_membership_type, 0) = 0 AND (".$s_sql_a.") AND im_cus.id IS NOT NULL) OR
	(cp.intranet_membership_type = 1 AND (".$s_sql_b.") AND im_cp.id IS NOT NULL)
	)
	AND (
	(IFNULL(cp.intranet_membership_subscription_type, 0) = 0 AND sm.id IS NOT NULL) OR
	(cp.intranet_membership_subscription_type = 1 AND sm2.id IS NOT NULL AND csc.id IS NOT NULL) OR
	cp.intranet_membership_subscription_type = 2
	)
	GROUP BY cp.id";
}

$sql = "SELECT p.* FROM contactperson p ".$sql_join." WHERE (p.id is not null) AND p.content_status < 1 AND p.type = ?".$sql_where;
$o_query = $o_main->db->query($sql, array($people_contactperson_type));
$peopleCount = $o_query ? $o_query->num_rows(): 0;

$sql = "SELECT p.* FROM contactperson p WHERE (p.id is not null) AND p.content_status = 2 AND p.deactivated = 1 AND p.type = ?";
$o_query = $o_main->db->query($sql, array($people_contactperson_type));
$l_inactive_people_count = $o_query ? $o_query->num_rows(): 0;

$sql = "SELECT p.* FROM contactperson p WHERE (p.id is not null) AND p.content_status = 2 AND (p.deactivated IS NULL OR p.deactivated = 0) AND p.type = ?";
$o_query = $o_main->db->query($sql, array($people_contactperson_type));
$deletedPeopleCount = $o_query ? $o_query->num_rows(): 0;

if($v_employee_basisconfig['activate_persons_tab']) {
    if(!$v_employee_accountconfig['activateFilterByTags']){
        $sql = "SELECT p.* FROM contactperson p WHERE (p.id is not null) AND p.content_status < 1 AND p.type = ?";
        $o_query = $o_main->db->query($sql, array($people_contactperson_type));
        $personListCount = $o_query ? $o_query->num_rows(): 0;
    }
}
if($v_employee_basisconfig['activate_companies_tab']) {
    $sql = "";
    $o_query = $o_main->db->query($sql);
    $companiesCount = $o_query ? $o_query->num_rows(): 0;
}
if($v_employee_basisconfig['activate_people_owncompany_tab']) {
    $sql = "";
    $o_query = $o_main->db->query($sql);
    $peopleOwnCompanyCount = $o_query ? $o_query->num_rows(): 0;
}
$v_haveAccessToGroups = array();

$v_membersystem_un = array();
$o_query = $o_main->db->query("SELECT * FROM cache_userlist_access");
$v_cache_userlist = $o_query ? $o_query->result_array() : array();
foreach($v_cache_userlist as $v_user_cached_info) {
    $v_membersystem_un[$v_user_cached_info['username']] = $v_user_cached_info;
}
// if(count($v_membersystem_un) == 0){
//     $v_membersystem_un = array();
//     $v_response = json_decode(APIconnectorUser("companyaccessbycompanyidget", $variables->loggID, $variables->sessionID, array('COMPANY_ID'=>$companyID, 'GET_GROUPS'=>1)), true);
//     foreach($v_response['data'] as $v_writeContent)
//     {
//         array_push($v_membersystem_un, $v_writeContent);
//     }
// }
foreach($v_membersystem_un as $v_member){
    if(mb_strtolower($v_member['username']) == mb_strtolower($variables->loggID)){
        $v_haveAccessToGroups = json_decode($v_member['groups'], true);
    }
}
$v_groupIdsWithAccess = array();
foreach($v_haveAccessToGroups as $v_singleGroup){
    array_push($v_groupIdsWithAccess, $v_singleGroup['id']);
}
$allGroups = array();
$departmentCount = 0;
$groupCount = 0;
$departments = array();

$sql = "SELECT p.* FROM contactperson_group p WHERE p.status = 1 AND p.show_in_people = 1 ORDER BY p.name";
$o_query = $o_main->db->query($sql, array($people_contactperson_type));
$allGroups = $o_query ? $o_query->result_array(): array();

foreach($allGroups as $allGroup) {
	//current user information
	$isMember = false;

	$sql = "SELECT p.* FROM contactperson p WHERE p.email = ? AND p.type = ? AND p.content_status < 2";
	$o_query = $o_main->db->query($sql, array($variables->loggID, $people_contactperson_type));
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
