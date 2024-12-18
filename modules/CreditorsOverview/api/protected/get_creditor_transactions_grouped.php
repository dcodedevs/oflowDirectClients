<?php 

$filters_new = $v_data['params']['filters'];
$creditor_id = $v_data['params']['creditor_filter'] ? $v_data['params']['creditor_filter'] : 0;

$list_filter = $filters_new['list_filter'] ? $filters_new['list_filter'] : 'active';
$page = $filters_new['page'] ? $filters_new['page'] : 1;
$perPage = $filters_new['perPage'] ? $filters_new['perPage'] : 150;


require_once __DIR__ . '/../../output/includes/transaction_functions.php';

$transactions_grouped = get_grouped_transaction_list($o_main, $creditor_id, $mainlist_filter, $filters, $page, $perPage);
$v_return['transactions_grouped'] = $transactions_grouped;
$v_return['status'] = 1;
?>