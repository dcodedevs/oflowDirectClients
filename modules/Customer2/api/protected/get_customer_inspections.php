<?php
$filters = $v_data['params']['filters'];
// Process filter data
$list_filter = $filters['list_filter'] ? $filters['list_filter'] : 'active';
$customer_filter = $filters['customer_filter'] ? $filters['customer_filter'] : 0;
$search_filter = $filters['search_filter'] ? $filters['search_filter'] : '';
$page = $filters['page'] ? $filters['page'] : 1;
$per_page = $filters['perPage'] ? $filters['perPage'] : 100;

// Offset
$offset = ($page-1)*$per_page;
if($page > 1){
    $offset -= ($per_page-10);
}

// Return data array
$return_data = array(
    'list' => array()
);

$searchSql = "";
if ($search_filter) {
    $searchSql = " AND (s.subscriptionName LIKE '%".$o_main->db->escape_like_str($search_filter)."%')";
}

$sqlNoLimit = "SELECT ir.*, s.subscriptionName FROM inspectionreport ir
LEFT OUTER JOIN repeatingorderinspection roi ON roi.id = ir.repeatingOrderInspectionId
LEFT OUTER JOIN subscriptionmulti s ON s.id = roi.repeatingOrderId
LEFT OUTER JOIN repeatingorder_underlayingorderconnection rouc ON rouc.overlayingorderId = s.id
LEFT OUTER JOIN subscriptionmulti ss ON rouc.underlayingorderId = ss.id
WHERE (s.customerId = ".$o_main->db->escape($customer_filter)." OR ss.customerId = ".$o_main->db->escape($customer_filter).")".$searchSql ."
GROUP BY ir.id
ORDER BY ir.date DESC";

$o_query =  $o_main->db->query($sqlNoLimit);
$invoice_count = 0;
if($o_query && $o_query->num_rows()>0)
$invoice_count = $o_query->num_rows();
$totalPages = ceil($invoice_count/$per_page);

$sql = $sqlNoLimit." LIMIT ".$per_page." OFFSET ".$offset;
$v_return['list'] = array();

$o_inspection = $o_main->db->query($sql);
if($o_inspection && $o_inspection->num_rows()>0) {
    foreach($o_inspection->result_array() AS $row) {
        array_push($v_return['list'], $row);
    }
}

$v_return['status'] = 1;
$v_return['totalInvoiceCount'] = $invoice_count;
?>
