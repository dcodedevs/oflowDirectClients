<?php
function get_grouped_transaction_list_count2($o_main, $creditor_id, $filter, $filters){
    return get_customer_list($o_main, $creditor_id, $filter, $filters, 0, 0);
}
function get_grouped_transaction_list_count($o_main, $creditor_id, $filter, $filters){
    $filters = array();
    return get_customer_list($o_main, $creditor_id, $filter, $filters, 0, 0);
}
function get_grouped_transaction_list($o_main, $creditor_id, $filter, $filters, $page=1, $perPage=300) {

    $s_sql = "SELECT * FROM creditor WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($creditor_id));
    $creditor = ($o_query ? $o_query->row_array() : array());

    $offset = ($page-1)*$perPage;
    if($offset < 0){
        $offset = 0;
    }
    $pager = " LIMIT ".$perPage ." OFFSET ".$offset;
    $list = array();

    $sql_join = "";
    $sql_where = "";

    if($page == 0 && $perPage == 0){
        $pager = "";
    }
	$sql_order_direction = " DESC";
    foreach($filters as $filterName=>$filterValue) {
        switch($filterName){
            case "order_direction":
                switch($filterValue) {
                    case "0";
                        $sql_order_direction = " DESC";
                    break;
                    case "1";
                        $sql_order_direction = " ASC";
                    break;
                }
            break;
        }
    }

    // foreach($filters as $filterName=>$filterValue){
    //     switch($filterName){
    //         case "search_filter":
    //             if($filterValue != "") {
    //                 $sql_join .= "";
    //                 $sql_where .= " AND (p.companyname LIKE '%".$o_main->db->escape_like_str($filterValue)."%' OR p.id LIKE '%".$o_main->db->escape_like_str($filterValue)."%')";
    //             }
    //         break;
	// 		case "order_field":
    //             switch($filterValue) {
    //                 default:
    //                     $sql_order = " ORDER BY customerName ".$sql_order_direction;
    //                 break;
    //             }
    //         break;
    //     }
    // }
	if($filter == 1){
		// $sql_where .= " AND choose_progress_of_reminderprocess = 1";
	}

	$sql_order = " ORDER BY customerName ASC";

    $sql = "SELECT ct.*, IFNULL(ct.link_id,UUID()) as unq_link_id, COUNT(ct.id) AS linked_transaction_count, trim(c.name) as customerName
        FROM creditor_transactions ct
        LEFT JOIN customer c ON c.creditor_id = ct.creditor_id AND c.creditor_customer_id = ct.external_customer_id 
        WHERE ct.creditor_id = '".$o_main->db->escape_str($creditor_id)."' AND ct.content_status < 2 AND ct.open = 1".$sql_where;

    $sql .= " GROUP BY unq_link_id";
    if($page == 0 && $perPage == 0) {
        $rowCount = 0;
        $o_query = $o_main->db->query($sql);
        if($o_query){
            $rowCount = $o_query->num_rows();
        }
        return $rowCount;
    } else {
        $sql .= $sql_order." ".$pager;
        $f_check_sql = $sql;

        $o_query = $o_main->db->query($sql);
        if($o_query && $o_query->num_rows()>0){
            $list = $o_query->result_array();
        }
        $list_processed = array();
        foreach($list as $singleItem){
            if($singleItem['linked_transaction_count'] > 1) {
                if($singleItem['link_id'] != "") {
                    $s_sql = "SELECT ct.* FROM creditor_transactions ct WHERE ct.creditor_id = '".$o_main->db->escape_str($creditor_id)."' AND link_id = ?";
                    $o_query = $o_main->db->query($s_sql, array($singleItem['link_id']));
                    $all_linked_transactions = ($o_query ? $o_query->result_array() : array());
                    $singleItem['transactions'] = $all_linked_transactions;
                }
            } else {
                $singleItem['transactions'][] = $singleItem;
            }
            $list_processed[] = $singleItem;
        }
        return $list_processed;
    }
}
