<?php

require_once __DIR__ . '/../../output/includes/creditor_functions.php';
$filters_new = $v_data['params']['filters'];
// Process filter data
$list_filter = $filters_new['list_filter'] ? $filters_new['list_filter'] : 'active';
$customer_filter = $filters_new['customer_filter'] ? $filters_new['customer_filter'] : 0;
$search_filter = $filters_new['search_filter'] ? $filters_new['search_filter'] : '';
$page = $filters_new['page'] ? $filters_new['page'] : 1;
$per_page = $filters_new['perPage'] ? $filters_new['perPage'] : 100;
$mainlist_filter = $filters_new['mainlist_filter'] ? $filters_new['mainlist_filter'] : 'active';

// Offset
$offset = ($page-1)*$per_page;
if($page > 1){
    $offset -= ($per_page-10);
}

// Return data array
$return_data = array(
    'list' => array()
);
$countArray = array();

$filters = array();
$filters['search_filter'] = $search_filter;

$s_sql = "SELECT * FROM creditor WHERE customer_id = ?";
$o_query = $o_main->db->query($s_sql, array($customer_filter));
$creditor = ($o_query ? $o_query->row_array() : array());

$processes = array();
$cid = $creditor['id'];
if($creditor) {

    $list_filter_fil = "suggested";
    $suggested_count = get_invoice_list_count($o_main, $cid, $list_filter_fil, $filters);
    $countArray['suggested_count'] = $suggested_count;

    $list_filter_fil = "suggested_onhold";
    $suggested_onhold_count = get_invoice_list_count($o_main, $cid, $list_filter_fil, $filters);
    $countArray['suggested_onhold_count'] = $suggested_onhold_count;

    $list_filter_fil = "suggested_new_steps";
    $suggested_new_steps_count = get_case_list_count($o_main, $cid, $list_filter_fil, $filters);
    $countArray['suggested_new_steps_count'] = $suggested_new_steps_count;

    $list_filter_fil = "suggested_new_steps_onhold";
    $suggested_new_steps_onhold_count = get_case_list_count($o_main, $cid, $list_filter_fil, $filters);
    $countArray['suggested_new_steps_onhold_count'] = $suggested_new_steps_onhold_count;

    if($mainlist_filter == "invoice") {

        $open_not_started_count = get_invoice_list_count($o_main, $cid, "open_not_started", $filters);
        $closed_not_started_count = get_invoice_list_count($o_main, $cid, "closed_not_started", $filters);
        $marked_to_not_count = get_invoice_list_count($o_main, $cid, "marked_to_not_create_case", $filters);
        $active_count = get_invoice_list_count($o_main, $cid, "active_case", $filters);
        $objection_count = get_invoice_list_count($o_main, $cid, "objection_case", $filters);
        $finished_count = get_invoice_list_count($o_main, $cid, "finished_case", $filters);
        $canceled_count = get_invoice_list_count($o_main, $cid, "canceled_case", $filters);

        $countArray['open_not_started_count'] = $open_not_started_count;
        $countArray['closed_not_started_count'] = $closed_not_started_count;
        $countArray['marked_to_not_count'] = $marked_to_not_count;
        $countArray['active_count'] = $active_count;
        $countArray['objection_count'] = $objection_count;
        $countArray['finished_count'] = $finished_count;
        $countArray['canceled_count'] = $canceled_count;

        $itemCount = get_invoice_list_count($o_main, $cid, $list_filter, $filters);

        $v_totals = get_invoice_list_totals($o_main, $cid, $list_filter, $filters);

        $l_total_amount = $v_totals['amount'];
        $l_total_credited = $v_totals['credited_amount'];
        $l_total_paid = $v_totals['paid_amount'];
        $l_total_saldo = $v_totals['amount'] + $v_totals['paid_amount']+$v_totals['credited_amount'];

        $countArray['l_total_amount'] = $l_total_amount;
        $countArray['l_total_credited'] = $l_total_credited;
        $countArray['l_total_paid'] = $l_total_paid;
        $countArray['l_total_saldo'] = $l_total_saldo;


        if(isset($_POST['page'])) {
            $page = $_POST['page'];
        }
        if(intval($page) == 0){
            $page = 1;
        }
        $rowOnly = $_POST['rowOnly'];
        $perPage = 100;
        $showing = $page * $perPage;
        $showMore = false;
        $currentCount = $itemCount;

        if($showing < $currentCount){
            $showMore = true;
        }
        $totalPages = ceil($currentCount/$perPage);

        $customerList = get_invoice_list($o_main, $cid, $list_filter, $filters, $page, $perPage);

    } else if($mainlist_filter == "case") {
        $active_count = get_case_list_count($o_main, $cid, "active", $filters);
        $objection_count = get_case_list_count($o_main, $cid, "objection", $filters);
        $finished_count = get_case_list_count($o_main, $cid, "finished", $filters);
        $canceled_count = get_case_list_count($o_main, $cid, "canceled", $filters);
        $inactive_count = get_case_list_count($o_main, $cid, "inactive", $filters);

        $countArray['active_count'] = $active_count;
        $countArray['objection_count'] = $objection_count;
        $countArray['finished_count'] = $finished_count;
        $countArray['canceled_count'] = $canceled_count;
        $countArray['inactive_count'] = $inactive_count;

        $itemCount = get_case_list_count($o_main, $cid, $list_filter, $filters);

        if(isset($_POST['page'])) {
            $page = $_POST['page'];
        }
        if(intval($page) == 0){
            $page = 1;
        }
        $rowOnly = $_POST['rowOnly'];
        $perPage = 100;
        $showing = $page * $perPage;
        $showMore = false;
        $currentCount = $itemCount;

        if($showing < $currentCount){
            $showMore = true;
        }
        $totalPages = ceil($currentCount/$perPage);

        $customerListNonProcessed = get_case_list($o_main, $cid, $list_filter, $filters, $page, $perPage);
        $customerList = array();
        foreach($customerListNonProcessed as $v_row) {
            $totalSumOriginalClaim = 0;
			$s_sql = "SELECT * FROM creditor_invoice WHERE content_status < 2 AND collecting_case_id = ? ORDER BY created DESC";
			$o_query = $o_main->db->query($s_sql, array($v_row['id']));
			$invoices = ($o_query ? $o_query->result_array() : array());
            foreach($invoices as $invoice) {
                $totalSumOriginalClaim += $invoice['collecting_case_original_claim'];
            }
            $s_sql = "SELECT * FROM collecting_cases_process_steps WHERE id = ?";
            $o_query = $o_main->db->query($s_sql, array($v_row['collectinglevel']));
            $process_step = ($o_query ? $o_query->row_array() : array());
            $collectingLevelName = $process_step['name'];

            $v_row['collectingLevelName'] = $collectingLevelName;
            $v_row['totalSumOriginalClaim'] = $totalSumOriginalClaim;
            array_push($customerList, $v_row);
        }
    } else if($mainlist_filter == "suggested_case"){
        $list_filter = "suggested";

        if(isset($_POST['page'])) {
            $page = $_POST['page'];
        }
        if(intval($page) == 0){
            $page = 1;
        }
        $rowOnly = $_POST['rowOnly'];
        $perPage = 100;
        $showing = $page * $perPage;
        $showMore = false;
        $currentCount = $suggested_count;

        if($showing < $currentCount){
            $showMore = true;
        }
        $totalPages = ceil($currentCount/$perPage);

        $customerList = get_invoice_list($o_main, $cid, $list_filter, $filters, $page, $perPage);
        $selectedProcesses = array();

        $s_sql = "SELECT creditor_manualprocess_connection.*, collecting_cases_process.name FROM creditor_manualprocess_connection
        LEFT OUTER JOIN collecting_cases_process ON collecting_cases_process.id = creditor_manualprocess_connection.process_id
        WHERE creditor_manualprocess_connection.creditor_id = ?";
        $o_query = $o_main->db->query($s_sql, array($creditor['id']));
        $connections = ($o_query ? $o_query->result_array() : array());
        foreach($connections as $connection) {
            array_push($processes, $connection);
        }
    } else if($mainlist_filter == "suggested_case_onhold"){
        $list_filter = "suggested_onhold";

        if(isset($_POST['page'])) {
            $page = $_POST['page'];
        }
        if(intval($page) == 0){
            $page = 1;
        }
        $rowOnly = $_POST['rowOnly'];
        $perPage = 100;
        $showing = $page * $perPage;
        $showMore = false;
        $currentCount = $suggested_onhold_count;

        if($showing < $currentCount){
            $showMore = true;
        }
        $totalPages = ceil($currentCount/$perPage);

        $customerList = get_invoice_list($o_main, $cid, $list_filter, $filters, $page, $perPage);

    } else if($mainlist_filter == "suggested_new_steps"){
        $list_filter = "suggested_new_steps";

        if(isset($_POST['page'])) {
            $page = $_POST['page'];
        }
        if(intval($page) == 0){
            $page = 1;
        }
        $rowOnly = $_POST['rowOnly'];
        $perPage = 100;
        $showing = $page * $perPage;
        $showMore = false;
        $currentCount = $suggested_new_steps_count;

        if($showing < $currentCount){
            $showMore = true;
        }
        $totalPages = ceil($currentCount/$perPage);

        $customerList = get_case_list($o_main, $cid, $list_filter, $filters, $page, $perPage);

    } else if($mainlist_filter == "suggested_new_steps_onhold"){
        $list_filter = "suggested_new_steps_onhold";

        if(isset($_POST['page'])) {
            $page = $_POST['page'];
        }
        if(intval($page) == 0){
            $page = 1;
        }
        $rowOnly = $_POST['rowOnly'];
        $perPage = 100;
        $showing = $page * $perPage;
        $showMore = false;
        $currentCount = $suggested_new_steps_onhold_count;

        if($showing < $currentCount){
            $showMore = true;
        }
        $totalPages = ceil($currentCount/$perPage);

        $customerList = get_case_list($o_main, $cid, $list_filter, $filters, $page, $perPage);

    }
}
$v_return['customerList'] = $customerList;
$v_return['processes'] = $processes;
$v_return['itemCount'] = $itemCount;
$v_return['totalPages'] = $totalPages;
$v_return['showMore'] = $showMore;
$v_return['page'] = $page;
$v_return['showing'] = $showing;
$v_return['countArray'] = $countArray;


$v_return['status'] = 1;
?>
