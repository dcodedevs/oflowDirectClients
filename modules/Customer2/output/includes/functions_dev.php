<?php
function get_subscriptions_with_offices($o_main, $customerId) {
    $list = array(
      'all' => array(),
      'with_subscriptions' => array(),
      'with_expired_subscriptions' => array()
    );
    $s_sql = "SELECT s.subscriptionName subscriptionName,
    s.startDate startDate,
    s.stoppedDate stoppedDate,
    s.nextRenewalDate nextRenewalDate,
    b.name buildingName,
    b.id buildingId,
    o.officeNumber officeNumber
    FROM subscriptionmulti s
    LEFT JOIN subscriptionofficespaceconnection c ON c.subscriptionId = s.id
    LEFT JOIN officespace o ON o.id = c.officeSpaceId
    LEFT JOIN building b ON b.id = o.buildingId
    WHERE s.customerId = ?";

    $o_query = $o_main->db->query($s_sql, array($customerId));
    if($o_query && $o_query->num_rows()>0) {
        $rows = $o_query->result_array();
        foreach($rows as $row) {
            array_push($list['all'], $row);
            $startTime = $row['startDate'] ? strtotime($row['startDate']) : 0;
            $stoppedTime = $row['stoppedDate'] && $row['stoppedDate'] != '0000-00-00' ? strtotime($row['stoppedDate']) : 0;
            if (($stoppedTime >= strtotime(date("Y-m-d")) || !$stoppedTime)) {
              array_push($list['with_subscriptions'], $row);
            } else {
              array_push($list['with_expired_subscriptions'], $row);
            }
        }
    }

    return $list;
}
function get_customer_list_count2($o_main, $filter, $city_filter, $search_filter,  $activecontract_filter, $selfdefinedfield_filter, $search_by){
    return get_customer_list($o_main, $filter, $city_filter, $search_filter,  $activecontract_filter, $selfdefinedfield_filter, $search_by, 0, 0);
}
function get_customer_list_count($o_main, $filter, $city_filter, $search_filter,  $activecontract_filter, $selfdefinedfield_filter, $search_by = ""){
    $city_filter = "";
    $search_filter = "";
    $activecontract_filter = "";
    $selfdefinedfield_filter = "";
    $search_by = "";
    return get_customer_list($o_main, $filter, $city_filter, $search_filter,  $activecontract_filter, $selfdefinedfield_filter, $search_by, 0, 0);
}
function get_customer_list($o_main, $filter, $city_filter, $search_filter,  $activecontract_filter, $selfdefinedfield_filter = null, $search_by, $page=1, $perPage=100, $customer_id = null, $s_custom_order_by = '') {
    global $v_customer_accountconfig;
    global $customer_basisconfig;
	$b_get_count = (0 == $page && 0 == $perPage);
	$filterArray = explode("sublistSeperator", $filter);
    $filter = $filterArray[0];
    $subfilter = "";
    if(isset($filterArray[1])) {
        $subfilter = $filterArray[1];
    }
    if(!isset($selfdefinedfield_filter) || $selfdefinedfield_filter == ''){ $selfdefinedfield_filter = array(); }

    if($search_filter != "" && $page > 0 && $perPage > 0)
    {
        $perPage = 50;
        $perPage = $perPage * ($page - 1);
        $offset = 0;
        if($page == 1)
        {
            $offset = 0;
            $perPage = 5;
        }
        // $l_brreg_page = ceil(($perPage * $page) / 100);
        //echo $l_brreg_page;
    } else {
        $offset = ($page-1)*$perPage;
        if($offset < 0){
            $offset = 0;
        }
    }

    $pager = " LIMIT ".$perPage ." OFFSET ".$offset;
    $list = array();
    $search_filter_sql_select = "";
    $search_filter_sql_join = "";
    $search_filter_sql_where = "";
    $city_sql_where = "";
    $with_orders_sql_where = "";
    $selfdefinedfield_sql_where = "";
    $selfdefinedfield_sql_join = "";
    $with_orders_sql_join="";
    $activecontract_sql_where = "";
    $activecontract_sql_join="";

    $subunit_sql_join = '';
    $subunit_sql_where = '';
    if($v_customer_accountconfig['activate_subunits']) {
        $subunit_sql_join = ' LEFT OUTER JOIN customer_subunit csu ON csu.customer_id = c.id';
    }
    if($city_filter){
        $city_sql_where = " AND c.paCity = ".$o_main->db->escape($city_filter);
    }
    if($activecontract_filter != "") {
        $activecontract_sql_where = " AND pu.property_id = ".$o_main->db->escape($activecontract_filter)." AND (subM.stoppedDate = '0000-00-00' OR subM.stoppedDate is null OR subM.stoppedDate > DATE(NOW())) AND (subM.startDate <= DATE(NOW()))";
        $activecontract_sql_join = " JOIN subscriptionmulti subM ON subM.customerId = c.id JOIN subscriptionofficespaceconnection subOc ON subOc.subscriptionId = subM.id
        JOIN property_unit pu ON pu.id = subOc.officeSpaceId";
    }
    if($search_filter != "")
	{
        $search_filter_reg = str_replace(" ", "|",$search_filter);
        switch($search_by){
            case 1:
                if($v_customer_accountconfig['activate_subunits']) {
                    $subunit_sql_where = " OR csu.name REGEXP '".$search_filter."'";
                }
                $search_filter_sql_select = "";
                $search_filter_sql_join = "";
                //$search_filter_sql_where = " AND ((c.name REGEXP '".$search_filter."' AND (c.customerType is null OR c.customerType = 0)) OR ((c.name REGEXP '".$search_filter_reg."' OR c.middlename REGEXP '".$search_filter_reg."' OR c.lastname REGEXP '".$search_filter_reg."') AND c.customerType = 1) OR c.publicRegisterId LIKE '%".$o_main->db->escape_like_str($search_filter)."%' OR c.publicRegisterId LIKE '%".$o_main->db->escape_like_str(str_replace(' ', '',$search_filter))."%' OR cexternl.external_sys_id LIKE '%".$o_main->db->escape_like_str($search_filter)."%' OR cexternl.external_id LIKE '%".$o_main->db->escape_like_str($search_filter)."%'".($customer_basisconfig['activate_shop_name'] ? " OR c.shop_name LIKE '%".$search_filter."%'" : "")."".$subunit_sql_where.")";
				$search_filter_sql_where = " AND c.name REGEXP '".$search_filter."'";

            break;
            case 2:
                if($v_customer_accountconfig['activate_subunits']) {
                    $subunit_sql_where = " OR contactperson.name REGEXP '".$search_filter_reg."'";
                }
                $search_filter_sql_select = ",  CONCAT_WS(' ', COALESCE(contactperson.name, ''), COALESCE(contactperson.middlename, ''), COALESCE(contactperson.lastname, '')) as contactpersonName, contactperson.mobile as contactpersonMobile, contactperson.email as contactpersonEmail";
                $search_filter_sql_join = " LEFT OUTER JOIN contactperson ON contactperson.customerId = c.id ";
                $search_filter_sql_where = " AND (contactperson.name REGEXP '".$search_filter_reg."' OR contactperson.middlename REGEXP '".$search_filter_reg."' OR contactperson.lastname REGEXP '".$search_filter_reg."' OR contactperson.mobile LIKE '%".$o_main->db->escape_like_str($search_filter)."%' OR contactperson.email LIKE '%".$o_main->db->escape_like_str($search_filter)."%' ".$subunit_sql_where.")";

            break;
            default:
                if($v_customer_accountconfig['activate_subunits']) {
                    $subunit_sql_where = " OR csu.name LIKE '%".$o_main->db->escape_like_str($search_filter)."%'";
                }
                $search_filter_sql_select = "";
                $search_filter_sql_join = "";
                //$search_filter_sql_where = " AND (CONCAT(COALESCE(c.name, ''), ' ', COALESCE(c.middlename, ''), ' ', COALESCE(c.lastname, '')) LIKE '%".$o_main->db->escape_like_str($search_filter)."%' OR c.publicRegisterId LIKE '%".$o_main->db->escape_like_str($search_filter)."%' OR c.publicRegisterId LIKE '%".$o_main->db->escape_like_str(str_replace(' ', '',$search_filter))."%' OR cexternl.external_sys_id LIKE '%".$o_main->db->escape_like_str($search_filter)."%' OR cexternl.external_id LIKE '%".$o_main->db->escape_like_str($search_filter)."%'".($customer_basisconfig['activate_shop_name'] ? " OR c.shop_name LIKE '%".$search_filter."%'" : "")."".$subunit_sql_where.")";
				$search_filter_sql_where = " AND c.name LIKE '%".$o_main->db->escape_like_str($search_filter)."%'";

        }
    }
    if(count($selfdefinedfield_filter) > 0){
        foreach($selfdefinedfield_filter as $key=>$value) {
            $s_sql = "SELECT * FROM customer_selfdefined_fields WHERE id = ?";
            $o_query = $o_main->db->query($s_sql, array($key));
            if($o_query && $o_query->num_rows()>0){
                $selfdefinedfield = $o_query->row_array();
                $valueArray = explode(",", $value);
                $firstItem = true;
                $selfdefinedfield_sql_single = "";
                foreach($valueArray as $singleValue){
                    if($singleValue != ""){
                        if($firstItem) {
                            $firstItem = false;
                        } else {
                            $selfdefinedfield_sql_single .= " OR ";
                        }

                        if($selfdefinedfield['type'] == 0){
                            if($selfdefinedfield['hide_textfield']){
                                $s_sql = "SELECT customer_selfdefined_lists.* FROM customer_selfdefined_lists_connection
                                LEFT OUTER JOIN customer_selfdefined_lists ON customer_selfdefined_lists.id = customer_selfdefined_lists_connection.customer_selfdefined_list_id
                                WHERE customer_selfdefined_field_id = ?";
                                $o_query = $o_main->db->query($s_sql, array($selfdefinedfield['id']));
                                $selfdefinedLists = $o_query ? $o_query->result_array() : array();
                            } else {
                                $selfdefinedLists = array();
                            }

                            if(count($selfdefinedLists) > 0) {
                                $selfdefinedfield_sql_single .= " csvc{$key}.selfdefined_list_line_id = ".$o_main->db->escape($singleValue)."";
                            } else {
                                $selfdefinedfield_sql_single .= " csv{$key}.active = 1";
                            }
                        } else if($selfdefinedfield['type'] == 1) {
                            $selfdefinedfield_sql_single .= " csv{$key}.value = ".$o_main->db->escape($singleValue)."";
                        } else if($selfdefinedfield['type'] == 2) {
                            $selfdefinedfield_sql_single .= " csvc{$key}.selfdefined_list_line_id = ".$o_main->db->escape($singleValue)."";
                        }
                    }
                }
                if($selfdefinedfield_sql_single != ""){
                    $selfdefinedfield_sql_where .= " AND (".$selfdefinedfield_sql_single.") ";
                }
            }
        }
    }
    if($page == 0 && $perPage == 0){
        $pager = "";
    }
    if($filter == "with_orders"){
        /*$with_orders_sql_join = "LEFT JOIN customer_collectingorder ON customer_collectingorder.customerId = c.id AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null)
        LEFT JOIN orders ON orders.collectingorderId = customer_collectingorder.id
        LEFT OUTER JOIN project ON project.id = customer_collectingorder.projectId";
        $with_orders_sql_where = " AND customer_collectingorder.id is not NULL AND customer_collectingorder.content_status = 0 AND (project.id is null OR (project.id is not null AND (project.status = 0 OR project.status is null)))";*/
		
		$with_orders_sql_where = " AND EXISTS (
			SELECT cco.id FROM customer_collectingorder AS cco
			LEFT OUTER JOIN project ON project.id = cco.projectId
			WHERE cco.customerId = c.id AND IFNULL(cco.invoiceNumber, 0) = 0 AND cco.content_status = 0 AND (project.id is null OR (project.id is not null AND IFNULL(project.status, 0) = 0))
		)";
    }
    $filter_content_where = " c.content_status < 2";

    $showDefaultList = false;
    if($filter == "all") {
        $showDefaultList = true;
        $filter_content_where = " c.content_status < 2";
    } else if ($filter == "deleted") {
        $filter_content_where = " c.content_status = 2";
    } else if ($filter == "marked_for_manual_check") {
        $filter_content_where = " c.content_status < 2 AND c.marked_for_manual_check = 1";
    } else if ($filter == "selfregistered") {
        $showDefaultList = true;
        $filter_content_where = " c.content_status < 2 AND c.selfregistered > 0";
        if($subfilter == "selfregistered_handled"){
            $filter_content_where .= " AND c.selfregistered = 2";
        } else if($subfilter == "selfregistered_unhandled"){
            $filter_content_where .= " AND c.selfregistered = 1";
        }
    } else if(strpos($filter, "subscriptiontype_") !== false) {
        if(strpos($filter, "group_by_") !== false) {
            $subscriptionttypeId = str_replace("group_by_subscriptiontype_", "", $filter);
            $groupBySubscription = 1;
        } else {

            $subscriptionttypeId = str_replace("subscriptiontype_", "", $filter);
        }
        $with_subs_sql_select = ", s.subscriptionName subscriptionName,
        s.startDate startDate,
        s.stoppedDate stoppedDate,
        s.nextRenewalDate nextRenewalDate";
        $with_subs_sql_join = "LEFT JOIN subscriptionmulti s ON s.customerId = c.id";
        $with_subs_sql_where = " AND s.subscriptiontype_id = '".$subscriptionttypeId."' AND ((s.stoppedDate >= CURDATE() OR s.stoppedDate = '0000-00-00' OR s.stoppedDate is null) AND (s.startDate <> '0000-00-00' AND s.startDate is not null AND s.startDate <= CURDATE())) AND s.content_status < 2";
    }

    $s_sql = "SELECT * FROM customer_listtabs_basisconfig WHERE id = '".$filter."' ORDER BY sortnr";
    $o_query = $o_main->db->query($s_sql);
    $o_nums = $o_query->num_rows();
    $customer_listtabs_basisconfig = ($o_query ? $o_query->row_array() : array());
    if($o_nums > 0) {
        $filterNumber = $customer_listtabs_basisconfig['filter'];
        switch($filterNumber) {
            case 1:
            break;
            case 2:
            $with_orders_sql_join = "LEFT JOIN customer_collectingorder ON customer_collectingorder.customerId = c.id AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null) LEFT JOIN orders ON orders.collectingorderId = customer_collectingorder.id ";
            $with_orders_sql_where = " AND customer_collectingorder.id is not NULL AND customer_collectingorder.content_status = 0 AND orders.id is not NULL";
            break;
            case 3:
            $with_subs_sql_select = ", s.subscriptionName subscriptionName,
            s.startDate startDate,
            s.stoppedDate stoppedDate,
            s.nextRenewalDate nextRenewalDate";
            $with_subs_sql_join = "LEFT JOIN subscriptionmulti s ON s.customerId = c.id";
            $with_subs_sql_where = " AND ((s.stoppedDate >= CURDATE() OR s.stoppedDate = '0000-00-00' OR s.stoppedDate is null) AND (s.startDate <> '0000-00-00' AND s.startDate is not null AND s.startDate <= CURDATE())) AND s.content_status < 2";
            break;
            case 4:
            $with_subs_sql_select = ", s.subscriptionName subscriptionName,
            s.startDate startDate,
            s.stoppedDate stoppedDate,
            s.nextRenewalDate nextRenewalDate
            ";
            $with_subs_sql_join = "LEFT JOIN subscriptionmulti s ON s.customerId = c.id
            LEFT JOIN (SELECT * FROM subscriptionmulti s2 WHERE (s2.stoppedDate >= CURDATE() OR s2.stoppedDate = '0000-00-00') AND (s2.startDate <> '0000-00-00' AND s2.startDate <= NOW())) s3 ON s3.customerId = c.id";
            $with_subs_sql_where = " AND ((s.stoppedDate < CURDATE() AND s.stoppedDate <> '0000-00-00') AND (s.startDate <> '0000-00-00' AND s.startDate <= CURDATE()) AND s3.id is null) AND s.content_status < 2";

            break;
        }
        $s_sql = "SELECT * FROM customer_listfields_basisconfig WHERE customer_list_basisconfig_id = ".$customer_listtabs_basisconfig['choose_list'];
        $o_query = $o_main->db->query($s_sql);
        $customer_listfields_basisconfig = ($o_query ? $o_query->result_array() : array());
        foreach($customer_listfields_basisconfig as $listField){
            if($listField['customertable_fieldname'] != "customerName"){
                if($listField['fieldtype'] == 1 && $listField['customertable_fieldname'] != ""){
                    $customer_field_sql_select .= ", c.".$listField['customertable_fieldname'];
                }
            }
        }

    } else {
        $s_sql = "SELECT * FROM customer_listfields_basisconfig LEFT OUTER JOIN customer_list_basisconfig ON customer_list_basisconfig.id = customer_listfields_basisconfig.customer_list_basisconfig_id  WHERE customer_list_basisconfig.default_list = 1";
        $o_query = $o_main->db->query($s_sql);
        $customer_listfields_basisconfig = ($o_query ? $o_query->result_array() : array());
        // var_dump($customer_listfields_basisconfig);
        foreach($customer_listfields_basisconfig as $listField){
            if($listField['customertable_fieldname'] != "customerName"){
                if($listField['fieldtype'] == 1 && $listField['customertable_fieldname'] != ""){
                    $customer_field_sql_select .= ", c.".$listField['customertable_fieldname'];
                }
            }
        }
    }

    if($selfdefinedfield_sql_where != "") {
        foreach($selfdefinedfield_filter as $key=>$value) {
            $s_sql = "SELECT * FROM customer_selfdefined_fields WHERE id = ?";
            $o_query = $o_main->db->query($s_sql, array($key));
            if($o_query && $o_query->num_rows()>0){
                $selfdefinedfield = $o_query->row_array();
                if($selfdefinedfield['type'] == 0){
                    if($selfdefinedfield['hide_textfield']){
                        $selfdefinedfield_sql_join .= " LEFT OUTER JOIN customer_selfdefined_values as csv{$key} ON  csv{$key}.customer_id = c.id  AND csv{$key}.selfdefined_fields_id = ".$o_main->db->escape($key)."
                        LEFT OUTER JOIN customer_selfdefined_values_connection as csvc{$key} ON csvc{$key}.selfdefined_value_id = csv{$key}.id ";
                    } else {
                        $selfdefinedfield_sql_join .= " LEFT OUTER JOIN customer_selfdefined_values as csv{$key} ON  csv{$key}.customer_id = c.id  AND csv{$key}.selfdefined_fields_id = ".$o_main->db->escape($key)." ";
                    }
                } else if( $selfdefinedfield['type'] == 1){
                    $selfdefinedfield_sql_join .= " LEFT OUTER JOIN customer_selfdefined_values as csv{$key} ON  csv{$key}.customer_id = c.id  AND csv{$key}.selfdefined_fields_id = ".$o_main->db->escape($key)." ";
                } else if($selfdefinedfield['type'] == 2){
                    $selfdefinedfield_sql_join .= " LEFT OUTER JOIN customer_selfdefined_values as csv{$key} ON  csv{$key}.customer_id = c.id  AND csv{$key}.selfdefined_fields_id = ".$o_main->db->escape($key)."
                    LEFT OUTER JOIN customer_selfdefined_values_connection as csvc{$key} ON csvc{$key}.selfdefined_value_id = csv{$key}.id ";
                }
            }
        }
    }

    if(!isset($customer_field_sql_select)){ $customer_field_sql_select = ''; }
    if(!isset($selfdefinedfield_sql_select)){ $selfdefinedfield_sql_select = ''; }
    if(!isset($with_subs_sql_select)){ $with_subs_sql_select = ''; }
    if(!isset($with_subs_sql_join)){ $with_subs_sql_join = ''; }
    if(!isset($with_subs_sql_where)){ $with_subs_sql_where = ''; }


    $sql = "SELECT ".($b_get_count?($groupBySubscription?"COUNT(s.id)":"COUNT(c.id)")." AS cnt":"c.created, cexternl.external_id as customerExternalNr, c.id customerId, c.name,c.middlename,c.lastname, c.publicRegisterId, c.shop_name,
            CONCAT(COALESCE(c.name, ''), ' ', COALESCE(c.middlename, ''), ' ', COALESCE(c.lastname, '')) as customerName, cred.companyname as creditorName, c.creditor_customer_id, c.paCity, c.paStreet ".$customer_field_sql_select.$selfdefinedfield_sql_select.$with_subs_sql_select.$search_filter_sql_select)."
            FROM customer c
			JOIN creditor cred ON c.creditor_id = cred.id
            LEFT OUTER JOIN customer_externalsystem_id cexternl ON cexternl.customer_id = c.id

            ".$subunit_sql_join.$with_orders_sql_join.$selfdefinedfield_sql_join.$search_filter_sql_join.$with_subs_sql_join.$activecontract_sql_join."
            WHERE ".$filter_content_where.$search_filter_sql_where.$with_orders_sql_where.$city_sql_where.$selfdefinedfield_sql_where.$with_subs_sql_where.$activecontract_sql_where;

    if($customer_id != null) {
        $list = array();
        if($groupBySubscription) {
            $sql .= " GROUP BY s.id ".(''!=$s_custom_order_by ? $s_custom_order_by : "ORDER BY c.id ASC").$pager;
        } else {
            $sql .= //" GROUP BY c.id".
			" ".(''!=$s_custom_order_by ? $s_custom_order_by : "ORDER BY c.name_sort ASC").$pager;
        }
        $o_query = $o_main->db->query($sql);
        if($o_query && $o_query->num_rows()>0){
            $customerList = $o_query->result_array();
            foreach($customerList as $index=>$customer) {
                if($customer['customerId'] == $customer_id) {
                    $currentCustomerIndex = $index;
                    break;
                }
            }
            array_push($list, $customerList[$currentCustomerIndex-1]);
            array_push($list, $customerList[$currentCustomerIndex]);
            array_push($list, $customerList[$currentCustomerIndex+1]);
        }

        return $list;
    } else {
        
        if($b_get_count){
            $rowCount = 0;
            $o_query = $o_main->db->query($sql);
            if($o_query && $o_query->num_rows()>0){
				$v_row = $o_query->row_array();
                $rowCount = $v_row['cnt'];
            }
            return $rowCount;
        } else {
            if($groupBySubscription) {
				$sql .= " GROUP BY s.id";
			} else {
				//$sql .= " GROUP BY c.id";
			}
			$sql .= (''!=$s_custom_order_by ? $s_custom_order_by : " ORDER BY c.name_sort ASC").$pager;
            $f_check_sql = $sql;

            $o_query = $o_main->db->query($sql);
            if($o_query && $o_query->num_rows()>0){
                $list = $o_query->result_array();
            }
            return $list;
        }
    }
}
