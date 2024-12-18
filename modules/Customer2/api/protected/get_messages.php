<?php
$filters = $v_data['params']['filters'];
$customer_id = $filters['customer_filter'];
$perPage = isset($filters['perPage']) ? $filters['perPage'] : 100;
$page = isset($filters['page']) ? $filters['page'] : 1;
$cid = isset($filters['cid']) ? $filters['cid'] : 0;

$resultPosts = array();
$v_return['status'] = 1;

$offset = ($page-1) * $perPage;
$pager = " LIMIT ".$perPage." OFFSET ".$offset;

if($cid > 0) {
    $s_sql = "SELECT messages_to_customer.* FROM messages_to_customer
    WHERE messages_to_customer.customer_id = ? AND messages_to_customer.id = ? ORDER BY messages_to_customer.date DESC";
    $o_query = $o_main->db->query($s_sql, array($customer_id, $cid));
    $totalPosts = $o_query ? $o_query->num_rows() : 0;

    $s_sql = $s_sql.$pager;
    $o_query = $o_main->db->query($s_sql, array($customer_id, $cid));
    $posts = $o_query ? $o_query->result_array() : array();
    $posts = array_merge($resultPosts, $posts);
} else {
    $s_sql = "SELECT messages_to_customer.* FROM messages_to_customer
    WHERE messages_to_customer.customer_id  = ? ORDER BY messages_to_customer.date DESC";
    $o_query = $o_main->db->query($s_sql, array($customer_id));
    $totalPosts = $o_query ? $o_query->num_rows() : 0;

    $s_sql = $s_sql.$pager;
    $o_query = $o_main->db->query($s_sql, array($customer_id));
    $posts = $o_query ? $o_query->result_array() : array();
    $posts = array_merge($resultPosts, $posts);
}

foreach($posts as $post) {
    $s_sql = "SELECT ".$post['content_table'].".* FROM ".$post['content_table']."
    WHERE ".$post['content_table'].".id  = ? ORDER BY ".$post['content_table'].".id DESC";
    $o_query = $o_main->db->query($s_sql, array($post['content_id']));
    $source = $o_query ? $o_query->row_array() : array();
    if($source['subscriptionName'] != ""){
        $post['repeatingOrderName'] = $source['subscriptionName'];
    } else if($source['name'] != ""){
        $post['repeatingOrderName'] = $source['name'];
    }
    $post['source'] = $source;
    $resultPosts[] = $post;
}

$v_return['data'] = $resultPosts;
$v_return['total'] = $totalPosts;
