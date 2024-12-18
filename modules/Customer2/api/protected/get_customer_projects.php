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
    $searchSql = " AND (p.name LIKE '%".$o_main->db->escape_like_str($search_filter)."%')";
}

$sqlNoLimit = "SELECT pp.*, pp.id as projectPeriodId, p.*, pp.created FROM project2 p
LEFT OUTER JOIN project2_periods pp ON pp.projectId = p.id
WHERE p.customerId = ".$o_main->db->escape($customer_filter)."".$searchSql ."
GROUP BY pp.id
ORDER BY pp.created DESC";

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
        $row['workplanline_files'] = array();
        $row['workplanline_images'] = array();
        $row['workplanline_portal_files'] = array();
        $row['workplanline_portal_images'] = array();

        $sql = "SELECT wf.* FROM workplanlineworker_files wf
        LEFT OUTER JOIN workplanlineworker w ON w.id = wf.workplanlineworkerId
        WHERE w.projectId = ".$o_main->db->escape($row['id'])."
        GROUP BY wf.id
        ORDER BY w.date DESC";
        $o_query =  $o_main->db->query($sql);
        $workplanline_files = $o_query ? $o_query->result_array() : array();
        foreach($workplanline_files as $file_item) {
            $images = json_decode($file_item['image'], true);
            $files = json_decode($file_item['file'], true);
            if(count($images) > 0) {
                $row['workplanline_images'][] = $file_item;
            }
            if(count($files) > 0) {
                $row['workplanline_files'][] = $file_item;
            }

        }


        $s_sql = "SELECT * FROM project2_customerportal_files WHERE content_status < 2 AND project2_id = ? AND project2_period_id = ? ORDER BY created DESC";
        $o_query = $o_main->db->query($s_sql, array($row['id'], $row['projectPeriodId']));
        $fileItems = ($o_query ? $o_query->result_array() : array());
        foreach($fileItems as $file_item) {
            $files = json_decode($file_item['file'], true);
            if(count($files) > 0) {
                $row['workplanline_portal_files'][] = $file_item;
            }
        }
        $s_sql = "SELECT * FROM project2_customerportal_images WHERE content_status < 2 AND project2_id = ? AND project2_period_id = ? ORDER BY created DESC";
        $o_query = $o_main->db->query($s_sql, array($row['id'], $row['projectPeriodId']));
        $fileItems = ($o_query ? $o_query->result_array() : array());
        foreach($fileItems as $file_item) {
            $image = $file_item['file_url'];
            if($image != "") {
                $fileUrlThumb = $image;
                $fileUrlThumb2 = str_replace("/0/", "/1/", $image);
                $fileUrlThumb2_array = explode("/uploads/", $fileUrlThumb2);
                if(count($fileUrlThumb2_array) == 2){
                    $fileUrlThumb2_array2 = explode("?", $fileUrlThumb2_array[1]);
                    if(file_exists(__DIR__."/../../../../uploads/".$fileUrlThumb2_array2[0])){
                        $fileUrlThumb = $fileUrlThumb2;
                    }
                }

                $row['workplanline_portal_images'][] = $image;
                $row['workplanline_portal_images_thumbs'][] = $fileUrlThumb;
            }
        }

        $row['workplanline_portal_comments'] = array();
        $s_sql = "SELECT * FROM project2_customerportal_comment WHERE content_status < 2 AND project2_id = ? AND project2_period_id = ? ORDER BY created DESC";
        $o_query = $o_main->db->query($s_sql, array($row['id'], $row['projectPeriodId']));
        $comments = ($o_query ? $o_query->result_array() : array());
        $row['workplanline_portal_comments'] = $comments;


        $row['invoices'] = array();
        $s_sql = "SELECT invoice.* FROM customer_collectingorder
        LEFT OUTER JOIN invoice ON invoice.id = customer_collectingorder.invoiceNumber
        WHERE customer_collectingorder.project2Id = ?
        AND (customer_collectingorder.invoiceNumber > 0 ) AND customer_collectingorder.project2PeriodId = ?
        ORDER BY customer_collectingorder.date ASC";
        $o_query = $o_main->db->query($s_sql, array($row['id'], $row['projectPeriodId']));
        $invoices = ($o_query ? $o_query->result_array() : array());
        $row['invoices'] = $invoices;

        array_push($v_return['list'], $row);
    }
}

$v_return['status'] = 1;
$v_return['totalInvoiceCount'] = $invoice_count;
?>
