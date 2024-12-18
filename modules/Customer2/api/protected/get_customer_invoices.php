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
    $searchSql = " AND (i.external_invoice_nr LIKE '%".$o_main->db->escape_like_str($search_filter)."%' OR i.id LIKE  '%".$o_main->db->escape_like_str($search_filter)."%'
    OR co.reference LIKE  '%".$o_main->db->escape_like_str($search_filter)."%' OR co.delivery_address_city LIKE  '%".$o_main->db->escape_like_str($search_filter)."%'
    OR co.delivery_address_line_1 LIKE  '%".$o_main->db->escape_like_str($search_filter)."%' OR co.delivery_address_line_2 LIKE  '%".$o_main->db->escape_like_str($search_filter)."%'
    OR co.delivery_address_postal_code LIKE  '%".$o_main->db->escape_like_str($search_filter)."%' OR co.delivery_address_country LIKE  '%".$o_main->db->escape_like_str($search_filter)."%'
    OR cp.name LIKE '%".$o_main->db->escape_like_str($search_filter)."%' OR cp.middlename LIKE '%".$o_main->db->escape_like_str($search_filter)."%' OR cp.lastname LIKE '%".$o_main->db->escape_like_str($search_filter)."%'
    )";
}
$sqlNoLimit = "SELECT i.*, oc.name companyName, CONCAT(c.name, ' ', c.middlename, ' ', c.lastname) AS customerName
FROM invoice i
LEFT JOIN ownercompany oc ON oc.id = i.ownercompany_id
LEFT JOIN customer c ON c.id = i.customerId
LEFT OUTER JOIN customer_collectingorder co ON co.invoiceNumber = i.id
LEFT OUTER JOIN contactperson cp ON cp.id = co.contactpersonId
WHERE c.id = $customer_filter AND i.content_status < 2 ".$searchSql ." GROUP BY i.id ORDER BY i.external_invoice_nr DESC";

$o_query =  $o_main->db->query($sqlNoLimit);
$invoice_count = 0;
if($o_query && $o_query->num_rows()>0)
$invoice_count = $o_query->num_rows();
$totalPages = ceil($invoice_count/$per_page);

$sql = $sqlNoLimit." LIMIT ".$per_page." OFFSET ".$offset;
$v_return['list'] = array();
$result = $o_main->db->query($sql);
if($result && $result->num_rows()>0)

foreach($result->result_array() AS $row) {

    $s_sql = "SELECT customer_collectingorder.*, CONCAT(cp.name, ' ', cp.middlename, ' ', cp.lastname) as contactPersonName FROM customer_collectingorder
    LEFT OUTER JOIN contactperson cp ON cp.id = customer_collectingorder.contactpersonId
    WHERE customer_collectingorder.invoiceNumber = ?  GROUP BY customer_collectingorder.id ORDER BY customer_collectingorder.id DESC";
    $o_query = $o_main->db->query($s_sql, array($row['id']));
    $collecting_orders = ($o_query ? $o_query->result_array() : array());
    $row['collecting_orders'] = $collecting_orders;

    if ($list_filter == 'active') {
        if (!$row['content_status']) {
            array_push($v_return['list'], $row);
        }
    }

    if ($list_filter == 'inactive') {
        if ($row['content_status'] == 2) {
            array_push($v_return['list'], $row);
        }
    }
}
$v_return['status'] = 1;
$v_return['totalInvoiceCount'] = $invoice_count;
?>
