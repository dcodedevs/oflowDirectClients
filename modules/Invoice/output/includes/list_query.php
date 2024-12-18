<?php
$list_filter = $_GET['list_filter'] ? $_GET['list_filter'] : 'active';
$company_filter = $_GET['company_filter'] ? $_GET['company_filter'] : 0;
$search_filter = $_GET['search_filter'] ? $_GET['search_filter'] : '';
// $customerList = get_invoice_list($list_filter, $company_filter, $search_filter);
$sql = "SELECT * FROM ownercompany";
$result = $o_main->db->query($sql);
if($result && $result->num_rows()>0)
if($result->num_rows() == 1){
    $ownerCompany = $result->result();
    $company_filter = $ownerCompany[0]->id;
}

$perPage = 50;
$offset = ($page-1)*$perPage;

$customerList = array();
$searchSql = "";
if ($search_filter) {
    $searchSql = " AND (i.external_invoice_nr LIKE '%".$o_main->db->escape_like_str($search_filter)."%' OR i.id LIKE  '%".$o_main->db->escape_like_str($search_filter)."%' )";
}
$sqlNoLimit = "SELECT i.*, oc.name companyName, c.name customerName
FROM invoice i
LEFT JOIN ownercompany oc ON oc.id = i.ownercompany_id
LEFT JOIN customer c ON c.id = i.customerId
WHERE oc.id = $company_filter AND i.content_status < 2 ".$searchSql ." ORDER BY i.external_invoice_nr DESC";
$o_query =  $o_main->db->query($sqlNoLimit, array($company_filter));
if($o_query && $o_query->num_rows()>0)

$invoice_count = $o_query->num_rows();
$totalPages = ceil($invoice_count/$perPage);

$sql = $sqlNoLimit." LIMIT ".$perPage." OFFSET ".$offset;
$result = $o_main->db->query($sql);
if($result && $result->num_rows()>0)

foreach($result->result() AS $row) {

    if ($list_filter == 'active') {
        if (!$row->content_status) {
            array_push($customerList, $row);
        }
    }

    if ($list_filter == 'inactive') {
        if ($row->content_status == 2) {
            array_push($customerList, $row);
        }
    }
}


?>
