<?php
function get_support_list_count($filter, $set_id, $search_filter = null ){
    return get_support_list($filter, $set_id, $search_filter, 0, 0);
}
function get_support_list($filter, $set_id, $search_filter, $page=1, $perPage=100, $order_field = 'name', $order_direction = 1) {
    global $o_main;
    $pager = "";
    if($page > 0){
        $offset = ($page-1)*$perPage;
        if($offset < 0){
            $offset = 0;
        }
        $pager = " LIMIT ".$perPage ." OFFSET ".$offset;
    }
    $list = array();
    $customer_group_filter_sql =  "";
    $search_filter_sql = "";
    $building_sql = "";
    $filter_sql = "";

    $filter_sql = " AND a.content_status < 2";

    if ($search_filter) {
        $search_filter_sql = " AND (a.name LIKE '%$search_filter%' OR a.articleCode LIKE '%$search_filter%')";
    }
    if($set_id > 0) {
        $search_filter_sql .= " AND a.company_product_set_id = '".$o_main->db->escape_str($set_id)."'";
    } else if($set_id == 0) {
        $search_filter_sql .= " AND (a.company_product_set_id = 0 OR a.company_product_set_id is null OR a.company_product_set_id = -1)";
    } else {
        $s_sql = "SELECT * FROM company_product_set ORDER BY name ASC";
    	$o_query = $o_main->db->query($s_sql);
    	$company_product_sets = $o_query ? $o_query->result_array() : array();
    	if(count($company_product_sets) > 0) {
            $search_filter_sql .= " AND a.company_product_set_id = '".$o_main->db->escape_str($set_id)."'";
        }
    }
    if($page == 0 && $perPage == 0){
        $pager = "";
    }
    $sql_order_direction = " ASC";
    switch($order_direction) {
        case "0";
            $sql_order_direction = " DESC";
        break;
        case "1";
            $sql_order_direction = " ASC";
        break;
    }
    switch($order_field) {
        case "article_code";
            $sql_order = " ORDER BY a.articleCode ".$sql_order_direction;
        break;
        case "comment";
            $sql_order = " ORDER BY a.comment ".$sql_order_direction;
        break;
        case "cost_price";
            $sql_order = " ORDER BY a.costPrice ".$sql_order_direction;
        break;
        case "price";
            $sql_order = " ORDER BY a.price ".$sql_order_direction;
        break;
        case "sales_account";
            $sql_order = " ORDER BY a.SalesAccountWithVat ".$sql_order_direction;
        break;
        case "vat_code";
            $sql_order = " ORDER BY a.VatCodeWithVat ".$sql_order_direction;
        break;
        case "name";
            $sql_order = " ORDER BY a.name ".$sql_order_direction;
            break;
        case "group";
            $sql_order = " ORDER BY groupName ".$sql_order_direction;
            break;
        case "external_id";
            $sql_order = " ORDER BY a.external_sys_id ".$sql_order_direction;
            break;
        default:
            $sql_order = " ORDER BY a.name ".$sql_order_direction;
        break;
    }
    if($filter > 0){
        $filter_sql .= " AND (a.article_supplier_id = ".$o_main->db->escape($filter).")";
    } else {
        $filter_sql .= " AND (a.article_supplier_id is null OR a.article_supplier_id = 0)";
    }

    $sql = "SELECT
        a.*, ag.name as groupName
        FROM article a
        LEFT OUTER JOIN article_group ag ON ag.id = a.articlegroup_id
        WHERE 1=1 ".$search_filter_sql.$filter_sql."
        GROUP BY a.id
        ".$sql_order;
    if($page == 0 && $perPage == 0){
        $rowCount = 0;
        $o_query = $o_main->db->query($sql);
        if($o_query){
            $rowCount = $o_query->num_rows();
        }
        return $rowCount;
    } else {

        $sql .= " ".$pager;
        $f_check_sql = $sql;

        $o_query = $o_main->db->query($sql);
        if($o_query && $o_query->num_rows()>0){
            $list = $o_query->result_array();
        }
        return $list;
    }
}
