<?php

$tab_id = $_POST['tab_id'];
$city_filter = $_POST['city_filter'];
$search_filter = $_POST['search_filter'];
$selfdefinedfield_filter = $_POST['selfdefinedfield_filter'];
$activecontract_filter = $_POST['activecontract_filter'];

require_once __DIR__ . '/functions.php';
$itemCount = get_customer_list_count($o_main, $tab_id, $city_filter, $search_filter, $activecontract_filter, $selfdefinedfield_filter);

echo $itemCount;
?>
