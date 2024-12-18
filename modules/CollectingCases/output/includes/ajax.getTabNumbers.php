<?php
$tab_id = $_POST['tab_id'];
$sublist_filter = $_POST['sublist_filter'];
$filters = $_POST['filters'];

require_once __DIR__ . '/functions.php';

$itemCount = get_customer_list_count($o_main, $tab_id, $sublist_filter, $filters);

echo $itemCount;
