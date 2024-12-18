<?php
$customer_id = $v_data['params']['customer_id'];
$o_main->db->order_by('id', 'DESC');
$o_query = $o_main->db->get_where('invoice', array('customerId' => $customer_id));
$invoices = array();
if ($o_query && $o_query->num_rows()) {
    foreach ($o_query->result_array() as $row) {
        if($v_data['params']['getOrderName']){
            $orderName = "";

            $s_sql = "SELECT s.subscriptionName as orderName FROM customer_collectingorder co JOIN orders o ON o.collectingorderId = co.id JOIN subscriptionmulti s ON s.id = o.subscribtionId WHERE co.invoiceNumber = ? AND s.id is not null";
            $o_query = $o_main->db->query($s_sql, array($row['id']));
            $subscription = ($o_query ? $o_query->row_array() : array());
            $orderName = $subscription['orderName'];

            $row['order_name'] = $orderName;
        }
        array_push($invoices, $row);
    }
}

$v_return['data'] = $invoices;
