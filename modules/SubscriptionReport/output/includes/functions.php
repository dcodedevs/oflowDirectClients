<?php
function get_subscription_status($o_main, $subscription){
    $status = "";
    $startDateTime = strtotime($subscription['startDate']);
    $stoppedDateTime = strtotime($subscription['stoppedDate']);
    $currentTime = time();

    if($subscription['startDate'] == "" || $subscription['startDate'] == "0000-00-00") {
        $status = 1;
    } else if ($startDateTime > $currentTime) {
        $status = 1;
    } else if ($subscription['stoppedDate'] == "" || $subscription['stoppedDate'] == "0000-00-00") {
        $status = 2;
    } else if ($stoppedDateTime <= $currentTime){
        $status = 3;
    } else {
        $status = 4;
    }
    return $status;
}
function get_subscription_summary_per_month($o_main, $subscription){
    $sum = 0;
    $o_query = $o_main->db->query("SELECT * FROM subscriptionline WHERE subscribtionId = ?", array($subscription['id']));
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $subscriptionLine)
	{
        $amount = $subscriptionLine['amount'];
        $pricePerPiece = $subscriptionLine['pricePerPiece'];
        if($subscriptionLine['articleOrIndividualPrice']){
            $sql = "SELECT * FROM article WHERE id = ?";
            $o_query = $o_main->db->query($sql, array($subscriptionLine['articleNumber']));
            $article = $o_query ? $o_query->row_array() : array();
            $pricePerPiece = $article['price'];
        }
        $discount = $subscriptionLine['discountPercent'];
        $priceTotal = $pricePerPiece * $amount * (1 - $discount/100);
        $sum+=$priceTotal;
    }
    return $sum;
}
function get_total_subscription_summary_per_month($o_main, $filter, $search_filter, $subscription_type_filter, $status_filter,$customerselfdefinedlist_filter, $ownercompany_filter, $perYear = false, $date_filter = false, $workgroup_filter = ''){
    $status_filter_sql =  "";
    $search_filter_sql = "";
    $subscription_type_sql = "";
    $filter_sql = "";

    // if($filter == "all"){
    //     $filter_sql = " AND s.content_status = 0";
    // }
    // if($filter == "deleted"){
    //     $filter_sql = " AND s.content_status > 0";
    // }

    $currentDate = date("Y-m-d", time());
    if($date_filter != ""){
        $currentDate =date("Y-m-d", strtotime($date_filter));
    }
    if($filter == "active"){
        $filter_sql = " AND s.content_status = 0 AND (s.startDate <> '0000-00-00' AND s.startDate is NOT null AND s.startDate <= ".$o_main->db->escape($currentDate)." AND (s.stoppedDate = '0000-00-00' OR s.stoppedDate is null OR s.stoppedDate > ".$o_main->db->escape($currentDate)."))";
    }
    if($filter == "not_started"){
        $filter_sql = " AND s.content_status = 0 AND (s.startDate = '0000-00-00' OR s.startDate is null OR s.startDate > ".$o_main->db->escape($currentDate).") AND (s.stoppedDate > ".$o_main->db->escape($currentDate)." OR s.stoppedDate is null OR s.stoppedDate = '0000-00-00')";
    }
    if($filter == "stopped"){
        $filter_sql = " AND s.content_status = 0 AND (s.stoppedDate <> '0000-00-00' AND s.stoppedDate is NOT null and s.stoppedDate <= ".$o_main->db->escape($currentDate).")";
    }
    if($filter == "future_stop"){
        $filter_sql = " AND s.content_status = 0 AND (s.stoppedDate <> '0000-00-00' AND s.stoppedDate is NOT null AND s.stoppedDate > ".$o_main->db->escape($currentDate).")";
    }
    if($filter == "deleted"){
        $filter_sql = " AND s.content_status > 0";
    }
    if($subscription_type_filter > 0) {
        $subscription_type_sql = " AND s.subscriptiontype_id = ".$o_main->db->escape($subscription_type_filter);
    }
    // if($status_filter > 0) {
    //     $currentDate = date("Y-m-d", time());
    //     switch($status_filter){
    //         case 1:
    //             $status_filter_sql = " AND (s.startDate = '0000-00-00' OR s.startDate is null OR s.startDate > ".$o_main->db->escape($currentDate).")";
    //         break;
    //         case 2:
    //             $status_filter_sql = " AND (s.startDate <> '0000-00-00' AND s.startDate is NOT null AND s.startDate <= ".$o_main->db->escape($currentDate)." AND (s.stoppedDate = '0000-00-00' OR s.stoppedDate is null))";
    //         break;
    //         case 3:
    //             $status_filter_sql = " AND (s.startDate <> '0000-00-00' AND s.startDate is NOT null AND s.startDate <= ".$o_main->db->escape($currentDate)." AND s.stoppedDate <> '0000-00-00' AND s.stoppedDate <> '' AND s.stoppedDate is NOT null and s.stoppedDate <= ".$o_main->db->escape($currentDate).")";
    //         break;
    //         case 4:
    //             $status_filter_sql = " AND (s.startDate <> '0000-00-00' AND s.startDate is NOT null AND s.startDate <= ".$o_main->db->escape($currentDate)." AND s.stoppedDate <> '0000-00-00' AND s.stoppedDate <> '' AND s.stoppedDate is NOT null AND s.stoppedDate > ".$o_main->db->escape($currentDate).")";
    //         break;
    //     }
    // }
    if ($search_filter) {
        $search_filter_sql = " AND (s.subscriptionName LIKE '%".$o_main->db->escape_like_str($search_filter)."%' ESCAPE '!')";
    }
	if($workgroup_filter) {
		$filter_sql .= ('' != $filter_sql ? " AND" : '')." s.workgroupId = '".$o_main->db->escape_str($workgroup_filter)."'";
	}
    if($page == 0 && $perPage == 0){
        $pager = "";
    }
    if($perYear){
         $sql = "SELECT SUM(IF(sl.articleOrIndividualPrice = 1, a.price, sl.pricePerPiece)*sl.amount*(1-sl.discountPercent/100)*s.periodNumberOfMonths) as total
            FROM subscriptionmulti s
            LEFT OUTER JOIN subscriptionline sl ON sl.subscribtionId = s.id
            LEFT OUTER JOIN article a ON a.id = sl.articleNumber
            WHERE 1=1 AND (freeNoBilling = 0 OR freeNoBilling is null) ".$search_filter_sql.$filter_sql.$subscription_type_sql.$status_filter_sql."
            ORDER BY s.startDate ASC";
    } else {
        $sql = "SELECT SUM(IF(sl.articleOrIndividualPrice = 1, a.price, sl.pricePerPiece)*sl.amount*(1-sl.discountPercent/100)) as total
            FROM subscriptionmulti s
            LEFT OUTER JOIN subscriptionline sl ON sl.subscribtionId = s.id
            LEFT OUTER JOIN article a ON a.id = sl.articleNumber
            WHERE 1=1  AND (freeNoBilling = 0 OR freeNoBilling is null) ".$search_filter_sql.$filter_sql.$subscription_type_sql.$status_filter_sql."
            ORDER BY s.startDate ASC";
    }
	$summary = array();
	$o_query = $o_main->db->query($sql);
	if($o_query && $o_query->num_rows()>0) $summary = $o_query->row_array();

    return $summary['total'];
}
function get_support_list_count($o_main, $filter, $search_filter, $subscription_type_filter, $status_filter, $customerselfdefinedlist_filter, $ownercompany_filter, $date_filter, $workgroup_filter = ''){
    return get_support_list($o_main, $filter, $search_filter, $subscription_type_filter, $status_filter, $customerselfdefinedlist_filter, $ownercompany_filter, $date_filter, 0, 0, NULL, NULL, $workgroup_filter);
}
function get_support_list($o_main, $filter, $search_filter, $subscription_type_filter, $status_filter, $customerselfdefinedlist_filter, $ownercompany_filter, $date_filter = "", $page=1, $perPage=100, $order_field = null, $order = null, $workgroup_filter = '') {

    $s_sql = "SELECT * FROM subscriptionreport_accountconfig ORDER BY id ASC";
    $o_query = $o_main->db->query($s_sql);
    $subscriptionreport_accountconfig = ($o_query ? $o_query->row_array():array());

    $offset = ($page-1)*$perPage;
    if($offset < 0){
        $offset = 0;
    }
    $pager = " LIMIT ".$perPage ." OFFSET ".$offset;
    $list = array();
    $status_filter_sql =  "";
    $search_filter_sql = "";
    $subscription_type_sql = "";
    $filter_sql = "";
    $selfdefined_sql = "";
    $selfdefined_join = "";

    $currentDate = date("Y-m-d", time());
    if($date_filter != ""){
        $currentDate =date("Y-m-d", strtotime($date_filter));
    }
    if($filter == "active"){
        $filter_sql = " AND s.content_status = 0 AND (s.startDate <> '0000-00-00' AND s.startDate is NOT null AND s.startDate <= ".$o_main->db->escape($currentDate)." AND (s.stoppedDate = '0000-00-00' OR s.stoppedDate is null OR s.stoppedDate > ".$o_main->db->escape($currentDate)."))";
    }
    if($filter == "not_started"){
        $filter_sql = " AND s.content_status = 0 AND (s.startDate = '0000-00-00' OR s.startDate is null OR s.startDate > ".$o_main->db->escape($currentDate).") AND (s.stoppedDate > ".$o_main->db->escape($currentDate)." OR s.stoppedDate is null OR s.stoppedDate = '0000-00-00')";
    }
    if($filter == "stopped"){
        $filter_sql = " AND s.content_status = 0 AND (s.stoppedDate <> '0000-00-00' AND s.stoppedDate is NOT null and s.stoppedDate <= ".$o_main->db->escape($currentDate).")";
    }
    if($filter == "future_stop"){
        $filter_sql = " AND s.content_status = 0 AND (s.stoppedDate <> '0000-00-00' AND s.stoppedDate is NOT null AND s.stoppedDate > ".$o_main->db->escape($currentDate).")";
    }
    if($filter == "deleted"){
        $filter_sql = " AND s.content_status > 0";
    }
    if($customerselfdefinedlist_filter != ""){
        $selfdefined_join = "
        LEFT OUTER JOIN customer_selfdefined_values ON  customer_selfdefined_values.customer_id = c.id AND customer_selfdefined_values.selfdefined_fields_id = ".$o_main->db->escape($subscriptionreport_accountconfig['customerSelfdefinedField'])."
        LEFT OUTER JOIN customer_selfdefined_values_connection ON customer_selfdefined_values_connection.selfdefined_value_id = customer_selfdefined_values.id
        ";
        $selfdefined_sql = " AND (customer_selfdefined_values_connection.selfdefined_list_line_id = ".$o_main->db->escape($customerselfdefinedlist_filter)." OR customer_selfdefined_values.value= ".$o_main->db->escape($customerselfdefinedlist_filter).")";
    }
    if($ownercompany_filter > 0){
        $selfdefined_join = "";
        $selfdefined_sql = " AND s.ownercompany_id=".$o_main->db->escape($ownercompany_filter)."";
    }
    if($subscription_type_filter > 0) {
        $subscription_type_sql = " AND s.subscriptiontype_id = ".$o_main->db->escape($subscription_type_filter);
    }
	if($workgroup_filter) {
		$filter_sql .= ('' != $filter_sql ? " AND" : '')." s.workgroupId = '".$o_main->db->escape_str($workgroup_filter)."'";
	}
    // if($status_filter > 0) {
    //     switch($status_filter){
    //         case 1:
    //             $status_filter_sql = " ";
    //         break;
    //         case 2:
    //             $status_filter_sql = " ";
    //         break;
    //         case 3:
    //             $status_filter_sql = " ";
    //         break;
    //         case 4:
    //             $status_filter_sql = " ";
    //         break;
    //     }
    // }
    if ($search_filter) {
        if($search_filter == "freeNoBilling"){
            $search_filter_sql = " AND s.freeNoBilling = 1";
        } else {
            $search_filter_sql = " AND (s.subscriptionName LIKE '%".$o_main->db->escape_like_str($search_filter)."%' ESCAPE '!')";
        }
    }
    if(($page == 0 || $page == -1) && $perPage == 0){
        $pager = "";
    }

    $orderBy = "c.name ASC";
    if($order_field == "customerName") {
        if($order == "ASC") {
            $orderBy = "c.name ASC";
        } else if ($order == "DESC") {
            $orderBy = "c.name DESC";
        }
    } else if ($order_field == "subscriptionName") {
        if($order == "ASC") {
            $orderBy = "s.subscriptionName ASC";
        } else if ($order == "DESC") {
            $orderBy = "s.subscriptionName DESC";
        }
    } else if ($order_field == "status") {

    } else if ($order_field == "summaryPerMonth") {
        if($order == "ASC") {
            $orderBy = "summaryPerMonth ASC";
        } else if ($order == "DESC") {
            $orderBy = "summaryPerMonth DESC";
        }
    } else if ($order_field == "startDate") {
        if($order == "ASC") {
            $orderBy = "s.startDate ASC";
        } else if ($order == "DESC") {
            $orderBy = "s.startDate DESC";
        }
    } else if ($order_field == "nextRenewalDate") {
        if($order == "ASC") {
            $orderBy = "s.nextRenewalDate ASC";
        } else if ($order == "DESC") {
            $orderBy = "s.nextRenewalDate DESC";
        }
    } else if ($order_field == "stoppedDate") {
        if($order == "ASC") {
            $orderBy = "s.stoppedDate ASC";
        } else if ($order == "DESC") {
            $orderBy = "s.stoppedDate DESC";
        }
    } else if ($order_field == "useArticlePrice") {
        if($order == "ASC") {
            $orderBy = "useArticlePrice ASC";
        } else if ($order == "DESC") {
            $orderBy = "useArticlePrice DESC";
        }
    }

    $sql = "SELECT c.*, s.*, c.name as customerName, IFNULL(IF(s.freeNoBilling = 1, -1, SUM(IF(sl.articleOrIndividualPrice = 1, a.price, sl.pricePerPiece)*sl.amount*(1-sl.discountPercent/100))), 0) as summaryPerMonth,
        SUM(sl.articleOrIndividualPrice) as useArticlePrice
        FROM subscriptionmulti s
        LEFT OUTER JOIN subscriptionline sl ON sl.subscribtionId = s.id
        LEFT OUTER JOIN customer c ON c.id = s.customerId
        LEFT OUTER JOIN article a ON a.id = sl.articleNumber
        ".$selfdefined_join."
        WHERE 1=1 AND c.content_status < 2 AND (s.collecting_task = 0 OR s.collecting_task is null) ".$search_filter_sql.$filter_sql.$subscription_type_sql.$status_filter_sql.$selfdefined_sql."
        GROUP BY s.id
        ORDER BY ".$orderBy.$pager;

    //echo $sql;

    $o_query = $o_main->db->query($sql);

	if($o_query)
	{
		if($page == 0 && $perPage == 0){
			return $o_query->num_rows();
		} else {
			foreach($o_query->result_array() as $row) {
				array_push($list, $row);
			}
			return $list;
		}
	} else {
		return false;
	}
}
