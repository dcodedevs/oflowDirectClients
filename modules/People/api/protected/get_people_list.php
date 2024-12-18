<?php
require_once __DIR__ . '/../../../../fw/account_fw/includes/APIconnector.php';
if(!function_exists("filter_email_by_domain")) include_once(__DIR__."/../../output_groups/includes/fnc_filter_email_by_domain.php");

// Get account info
$o_query = $o_main->db->get('accountinfo');
$accountinfo = $o_query ? $o_query->row_array() : array();

$people_contactperson_type = 2;
$sql = "SELECT * FROM accountinfo_basisconfig ORDER BY id";
$o_query = $o_main->db->query($sql);
$accountinfo_basisconfig = $o_query ? $o_query->row_array() : array();
if(intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']) > 0){
	$people_contactperson_type = intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']);
}
if(intval($accountinfo['contactperson_type_to_use_in_people']) > 0)
{
	$people_contactperson_type = $accountinfo['contactperson_type_to_use_in_people'];
}

// TODO security check
$s_sql = "select * from people_basisconfig";
$o_query = $o_main->db->query($s_sql);
$v_employee_basisconfig = $o_query ? $o_query->row_array() : array();

$s_sql = "select * from people_accountconfig";
$o_query = $o_main->db->query($s_sql);
$v_employee_accountconfig = ($o_query ? $o_query->row_array() : array());
foreach($v_employee_accountconfig as $key=>$value){
    if(!isset($v_employee_basisconfig[$key])){
        if($value > 0){
            $v_employee_basisconfig[$key] = ($value - 1);
        } else {
            $v_employee_basisconfig[$key] = 0;
        }
    } else if (isset($v_employee_basisconfig[$key]) && $value > 0){
        $v_employee_basisconfig[$key] = ($value - 1);
    }
}

$sql_select = ", p.middlename as middle_name, p.lastname as last_name";
$sql_join = "";
$sql_where = "";
$sql_group = "";

$sql_where .= " AND p.content_status < 1";
if($v_employee_basisconfig['filter_by_subscription'] == 1) {
	$sql_join .= " LEFT OUTER JOIN customer c ON c.id = p.customerId
	LEFT OUTER JOIN subscriptionmulti s ON s.customerId = c.id";

	$sql_where .= " AND s.startDate <= NOW() AND (s.stoppedDate = '0000-00-00' OR s.stoppedDate is null or s.stoppedDate > NOW())";
}
if($v_employee_basisconfig['show_only_persons_marked_to_show_in_intranet'] == 1){
	$sql_where .= " AND p.show_in_intranet = 1";
}
$sql_where .= " AND (p.notVisibleInMemberOverview = 0 OR p.notVisibleInMemberOverview is NULL)";

$variables->loggID = $v_data['username'];
// Access elements
$o_query = $o_main->db->query("SELECT *, TIME_TO_SEC(TIMEDIFF(NOW(), cache_timestamp)) refreshtime FROM session_framework WHERE companyaccessID = '".$o_main->db->escape_str($v_data['caID'])."' AND session = '".$o_main->db->escape_str($v_data['sessionID'])."' AND username = '".$o_main->db->escape_str($v_data['username'])."' LIMIT 1");
$fw_session = $o_query ? $o_query->row_array() : array();
$variables->useradmin = $fw_session['useradmin'];

if((!isset($variables->useradmin) || 0 == $variables->useradmin) && $v_employee_accountconfig['activateFilterByTags'])
{
	$v_property_ids = $v_property_group_ids = array();
	$s_sql = "SELECT cp.* FROM contactperson AS cp
	JOIN customer AS cus ON cus.id = cp.customerId AND cus.content_status < 2
	LEFT OUTER JOIN subscriptionmulti AS sm ON sm.customerId = cp.customerId AND sm.startDate <= CURDATE() AND ( sm.stoppedDate = '0000-00-00' OR sm.stoppedDate is null OR sm.stoppedDate >= CURDATE())
	
	LEFT OUTER JOIN contactperson_subscription_connection AS csc ON csc.contactperson_id = cp.id
	LEFT OUTER JOIN subscriptionmulti AS sm2 ON csc.subscriptionmulti_id = sm2.id AND sm2.startDate <= CURDATE()
	AND (sm2.stoppedDate = '0000-00-00' OR sm2.stoppedDate is null OR sm2.stoppedDate >= CURDATE())
	
	WHERE cp.email = '".$o_main->db->escape_str($variables->loggID)."' AND (
	(ifnull(cp.intranet_membership_subscription_type, 0) = 0 AND sm.id IS NOT NULL) OR 
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
	)";
	$sql_group = " GROUP BY cp.id";
}

$sql = "SELECT p.*".$sql_select." FROM contactperson p ".$sql_join." WHERE (p.id is not null) AND p.type = '".$people_contactperson_type."' AND p.content_status < 2 ".$sql_where.$sql_group."  ORDER by p.name";
$o_query = $o_main->db->query($sql);
$people = array();
$user_emails = array();

// Get local user list
if ($o_query && $o_query->num_rows()) {
    foreach($o_query->result_array() as $row) {
        $row['userData'] = array('image' => null);
        if ($row['email']) {
            array_push($user_emails, $row['email']);
        }
        array_push($people, $row);
    }
}

// Get user info from API
$user_data_list = json_decode(APIconnectorUser('userdetailsget', $v_data['username'], $v_data['sessionID'], array('USERNAME'=> $user_emails)), true);
$users_grouped_by_email = array();
foreach($user_data_list['items'] as $user) {
    $username = $user['username'];
    if (!$users_grouped_by_email[$username]) {
        $users_grouped_by_email[$username] = $user;
    }
}

// Merge data
$unregistered_users = array();
$people_processed = array();
foreach ($people as $person) {
    $email = $person['email'];
    if ($email) {
        if ($users_grouped_by_email[$email]) {
            $person['userData'] = $users_grouped_by_email[$email];
        } else {
            array_push($unregistered_users, $email);
        }
    }
    array_push($people_processed, $person);
}

// Get unregistered user images
if(count($unregistered_users)) {
    // Get account data
    $sql = "SELECT * FROM accountinfo";
    $o_query = $o_main->db->query($sql);
    $accountinfo = $row_data = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();

    $response = json_decode(APIconnectAccount("user_image_upload_get", $accountinfo['accountname'], $accountinfo['password'], array('username' => $unregistered_users)), TRUE);
    if(isset($response['status']) && $response['status'] == 1) {
        $unreigstered_user_images = $response['items'];
    }

    // Add unregisterd images for users
    $people_processed_new = array();
    foreach ($people_processed as $person) {
        if ($unreigstered_user_images[$person['email']]) {
            $person['userData'] = array(
                'image' => $unreigstered_user_images[$person['email']]['image']
            );
        }
        $people_processed_new[] = $person;
    }
    $people_processed = $people_processed_new;
}
// the &$person was causing last record getting overwritten with previous one for some reason
$people_processed_new = array();
foreach ($people_processed as $person) {
    $person['email'] = filter_email_by_domain($person['email']);
    $people_processed_new[] = $person;
}
$people_processed = $people_processed_new;

//TODO change to the tag real tag id if needs to filter by single
$tag_view_filter = 0;

$filtered_contactperson_ids = array();
include(__DIR__ . '/../../output/includes/readAccessElements.php');
//

include(__DIR__."/../../output/includes/person_init.php");
if(count($filtered_contactperson_ids) > 0){
    $newFilteredPeople = array();
	$addedToListIds = array();
    foreach ($people_processed as $person) {

        if(in_array($person['id'], $filtered_contactperson_ids)) {
			if(!in_array($person['id'], $addedToListIds)){
				array_push($newFilteredPeople, $person);
				array_push($addedToListIds, $person['id']);
			}
		}
    }
    $people_processed = $newFilteredPeople;
}

$v_return['status'] = 1;
$v_return['data'] = $people_processed;
