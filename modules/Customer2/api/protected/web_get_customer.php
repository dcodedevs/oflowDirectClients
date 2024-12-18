<?php
$customerId = $v_data['params']['customer_id'];
$customer_search_text = $v_data['params']['search'];
$l_per_page = $v_data['params']['per_page'];
$l_page = $v_data['params']['page'];
if($customerId > 0){
    $o_query = $o_main->db->query("SELECT id, name, paStreet AS street, paCity AS city, email FROM customer
    WHERE content_status < 2 AND id = ?
    ORDER BY sortnr", array($customerId));
    $customer = $o_query ? $o_query->row_array() : array();
    $v_return['data'] = $customer;
    $v_return['status'] = 1;
} else {
    $sql_where = "";
    if($customer_search_text != ""){
        $sql_where = " AND name LIKE '%".$customer_search_text."%'";
    }
    $sql = "SELECT id, name, paStreet AS street, paCity AS city FROM customer
    WHERE content_status < 2 ".$sql_where." ORDER BY name ASC";
    $o_query = $o_main->db->query($sql);
    $total_count = $o_query ? $o_query->num_rows() : 0;

    $limit = " LIMIT ".($l_page*$l_per_page).", $l_per_page";
    $o_query = $o_main->db->query($sql.$limit);
    $customers = $o_query ? $o_query->result_array() : array();

    $v_return['total_count'] = $total_count;
    $v_return['data'] = $customers;
    $v_return['status'] = 1;
}
