<?php
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");
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
if($v_employee_basisconfig['activate_persons_tab'] || $v_employee_basisconfig['activate_companies_tab'] || $v_employee_basisconfig['activate_people_owncompany_tab']){

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

	$crmCustomers = array();

    $sql_where = "";
    $sql_join = "";
    if($v_employee_basisconfig['filter_by_subscription'] == 1) {
    	$sql_join .= " LEFT OUTER JOIN subscriptionmulti s ON s.customerId = c.id";
    	$sql_where .= " AND s.startDate <= NOW() AND (s.stoppedDate = '0000-00-00' OR s.stoppedDate is null or s.stoppedDate > NOW())";
    } else if($v_employee_basisconfig['filter_by_subscription'] == 2){
    	$type_ids = array();
    	if(intval($v_employee_accountconfig['filter_by_subscription']) == 0){
    		$type_ids = explode(",", $v_employee_basisconfig['specified_subscription_type_ids']);
    	} else {
    		$type_ids = explode(",", $v_employee_accountconfig['specified_subscription_type_ids']);
    	}
    	if(count($type_ids) > 0){
    		$sql_join .= " LEFT OUTER JOIN subscriptionmulti s ON s.customerId = c.id
    		LEFT OUTER JOIN subscriptiontype st ON st.id = s.subscriptiontype_id";
    		$sql_where .= " AND s.startDate <= NOW() AND (s.stoppedDate = '0000-00-00' OR s.stoppedDate is null or s.stoppedDate > NOW()) AND st.id IN(".implode(',', $type_ids).")";
    	}
    }

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

		$sql_join .= " JOIN contactperson AS cp ON cp.customerId = c.id
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
	}

    $s_sql = "select * from customer c ".$sql_join." WHERE c.content_status < 2".$sql_where." GROUP BY c.id ORDER BY c.name ASC";

	$o_query = $o_main->db->query($s_sql);
    $crmCustomersCount = ($o_query ? $o_query->num_rows() : 0);



    $search_company = $_GET['search'];
    if($search_company != ""){
        $sql_where .= " AND c.name LIKE '%".$search_company."%'";
    }

    $companyPage = 1;
    if(isset($_GET['page'])) {
        $companyPage = $_GET['page'];
    }
    if($companyPage < 0){
        $companyPage = 1;
    }
    $companyPerPage = 100;

    $offset = ($companyPage-1)*$companyPerPage;
    if($offset < 0){
        $offset = 0;
    }
    $pager = " LIMIT ".$companyPerPage ." OFFSET ".$offset;
    $showing = $companyPerPage * $companyPage;

    $currentCount = $crmCustomersCount;

    $totalPages = ceil($currentCount/$companyPerPage);

    $s_sql = "select c.* from customer c ".$sql_join." WHERE c.content_status < 2".$sql_where." GROUP BY c.id ORDER BY c.name ASC".$pager;
    $o_query = $o_main->db->query($s_sql);
    $crmCustomers = ($o_query ? $o_query->result_array() : array());

    // $crmCustomersShowOnPersonTab = array();
	// $s_sql = "SELECT * FROM contactperson WHERE customerId > 0 GROUP BY customerId";
	// $o_result = $o_main->db->query($s_sql);
	// $crm_connections = $o_result ? $o_result->result_array() : array();
	// $customerIds = array();
	// foreach($crm_connections as $crm_connection) {
	// 	array_push($customerIds, $crm_connection['customerId']);
	// }
    // $getMembershipConnections = false;
    // if($v_employee_accountconfig['activateFilterByTags']){
    //     $getMembershipConnections = true;
    // }
	// $params = array(
	// 	'api_url' => $v_employee_accountconfig['linked_crm_account'].'/api',
	// 	'access_token'=> $v_employee_accountconfig['linked_crm_account_token'], //hardcoded
	// 	'module' => 'Customer2',
	// 	'action' => 'get_customers_by_ids',
	// 	'params' => array(
	// 		'customerIds' => $customerIds,
    //         'getMembershipConnections' => $getMembershipConnections
	// 	)
	// );
	// $response = fw_api_call($params, false);
	// if(count($response['data']) > 0) {
    //     if($v_employee_accountconfig['activateFilterByTags']){
    //         $filtered_contactperson_ids = array();
    //         //post tag section
    //         $o_query = $o_main->db->get('accountinfo');
    //         $v_accountinfo = $o_query ? $o_query->row_array() : array();
    //
    //         if($v_accountinfo['activate_crm_user_content_filtering_tags']){
    //             if($v_accountinfo['crm_account_url'] != "" && $v_accountinfo['crm_access_token'] != ""  && $v_accountinfo['crm_account_module'] != "" ){
    //                 $s_sql = "SELECT * FROM crm_user_content_filtering_tags WHERE	username = ?";
    //                 $o_query = $o_main->db->query($s_sql, array($variables->loggID));
    //                 $tags_info = $o_query ? $o_query->row_array() : array();
    //
    //                 $tagsToRead = array();
    //                 $groupsToRead = array();
    //                 if($tags_info) {
    //                     $tags = json_decode($tags_info['tags'], true);
    //                     $tagsToRead = $tags['tags_read'];
    //                     $groupsToRead = $tags['groups_read'];
    //                 }
    //                 if($accessElementAllow_ReadAllTagsAndGroups){
    //                     $s_sql = "SELECT * FROM crm_user_content_filtering_tags_for_admin";
    //                     $o_query = $o_main->db->query($s_sql);
    //                     $admin_tag_info = $o_query ? $o_query->row_array() : array();
    //                     if($admin_tag_info){
    //                         $tags = json_decode($admin_tag_info['tags'], true);
    //                         $tagsToRead = $tags['tags'];
    //                         $groupsToRead = $tags['groups'];
    //                     }
    //                 }
    //                 if($tag_view_filter > 0){
    //                     $tagsToRead = array(array("id" => $tag_view_filter));
    //                     $groupsToRead = array();
    //                 }
    //                 $finalTags = array();
    //
    //                 foreach($tagsToRead as $tagToRead) {
    //                     array_push($finalTags, $tagToRead['id']);
    //                 }
    //
    //                 foreach($groupsToRead as $groupToRead) {
    //                     $s_sql = "SELECT * FROM crm_tag_and_group_connection WHERE group_id = ?";
    //                     $o_query = $o_main->db->query($s_sql, array($groupToRead['id']));
    //                     $groupTags = $o_query ? $o_query->result_array() : array();
    //                     foreach($groupTags as $groupTag){
    //                         if(!in_array($groupTag['tag_id'], $finalTags)){
    //                             array_push($finalTags, $groupTag['tag_id']);
    //                         }
    //                     }
    //                 }
    //
    //         		foreach($response['data'] as $customerSingle) {
    //                     //filter by tags
    //                     $customerAddToList = false;
    //                     $membership_connections = $customerSingle['membership_connections'];
    //                     foreach($membership_connections as $membership_connection) {
    //                         $tagId = $membership_connection['object_id'];
    //                         if(in_array($tagId, $finalTags)){
    //                              $customerAddToList = true;
    //                         }
    //                     }
    //
    //                     $contactPersons = $customerSingle['contactPersons'];
    //                     foreach($contactPersons as $contactPerson) {
    //                         $contactPersonAddToList = false;
    //                         if($contactPerson['intranet_membership_type'] == 0){
    //
    //                         } else if($contactPerson['intranet_membership_type'] == 0){
    //                             $membership_connections = $contactPerson['membership_connections'];
    //                             foreach($membership_connections as $membership_connection) {
    //                                 $tagId = $membership_connection['object_id'];
    //                                 if(in_array($tagId, $finalTags)){
    //                                     $contactPersonAddToList = true;
    //                                 }
    //                             }
    //                         }
    //                         if($customerAddToList || $contactPersonAddToList ) {
    //                             $crmCustomers[$customerSingle['id']] = $customerSingle;
    //                             array_push($filtered_contactperson_ids, $contactPerson['id']);
    //
    //                         }
    //                     }
    //
    //         		}
    //                 foreach($response['dataOnPersonTab'] as $customerSingle) {
    //                     //filter by tags
    //                     $customerAddToList = false;
    //                     $membership_connections = $customerSingle['membership_connections'];
    //                     foreach($membership_connections as $membership_connection) {
    //                         $tagId = $membership_connection['object_id'];
    //                         if(in_array($tagId, $finalTags)){
    //                              $customerAddToList = true;
    //                         }
    //                     }
    //
    //                     $contactPersons = $customerSingle['contactPersons'];
    //                     foreach($contactPersons as $contactPerson) {
    //                         $contactPersonAddToList = false;
    //                         if($contactPerson['intranet_membership_type'] == 0){
    //
    //                         } else if($contactPerson['intranet_membership_type'] == 0){
    //                             $membership_connections = $contactPerson['membership_connections'];
    //                             foreach($membership_connections as $membership_connection) {
    //                                 $tagId = $membership_connection['object_id'];
    //                                 if(in_array($tagId, $finalTags)){
    //                                     $contactPersonAddToList = true;
    //                                 }
    //                             }
    //                         }
    //                         if($customerAddToList || $contactPersonAddToList ) {
    //                             $crmCustomersShowOnPersonTab[$customerSingle['id']] = $customerSingle;
    //                             array_push($filtered_contactperson_ids, $contactPerson['id']);
    //
    //                         }
    //                     }
    //         		}
    //             }
    //         }
    //
    //     } else {
    // 		foreach($response['data'] as $customerSingle) {
    // 			$crmCustomers[$customerSingle['id']] = $customerSingle;
    // 		}
    //         foreach($response['dataOnPersonTab'] as $customerSingle) {
    // 			$crmCustomersShowOnPersonTab[$customerSingle['id']] = $customerSingle;
    // 		}
    //     }
	// }
    //

	$s_sql = "select * from accountinfo";
	$o_query = $o_main->db->query($s_sql);
    $v_accountinfo = $o_query ? $o_query->row_array() : array();

    $s_sql = "select * from customer_stdmembersystem_basisconfig";
    $o_query = $o_main->db->query($s_sql);
    $v_membersystem_config = $o_query ? $o_query->row_array() : array();

	$personCompanies = array();

	$s_sql = "SELECT c.* FROM contactperson cp LEFT OUTER JOIN customer c ON c.id = cp.customerId WHERE cp.email = ? AND cp.customerId > 0";
	$o_result = $o_main->db->query($s_sql, array($variables->loggID));
	$contactpersonWithAccesses = $o_result ? $o_result->result_array() : array();


    foreach($contactpersonWithAccesses as $crmCustomer){
        $member = array();
        if(!$variables->useradmin){
            $o_membersystem = json_decode(APIconnectAccount("membersystemcompanyaccessusernameget", $v_accountinfo['accountname'], $v_accountinfo['password'], array("COMPANY_ID"=>$companyID, "USER"=>$variables->loggID, "MEMBERSYSTEMID"=>$crmCustomer['id'], "ACCESSLEVEL"=>$v_membersystem_config['access_level'], "MODULE"=>"Customer2")));
            $member = $o_membersystem->data;
        }
        if($member || $variables->useradmin) {
            $personCompanies[$crmCustomer['id']] = $crmCustomer;
        }
    }
}
?>
