<?php
$run_hook = function($data) {
    global $o_main;
    global $variables;

    // Params
    $customer_id = $data['customer_id'];
    $ownercompany_id = $data['ownercompany_id'];
    // Load integration
    require_once __DIR__ . '/../internal_api/load.php';
    $api = new Integration24SevenOffice(array(
        'o_main' => $o_main,
        'ownercompany_id' => $ownercompany_id
    ));

    // Return object
    $return = array();
    $newArticles = 0;
    $updatedArticles = 0;
    $data['orderStates'] = array("ForInvoicing");
    $orderlines = $api->get_orders_list($data);
    $return['orderlines'] = $orderlines;
    return $return;
}
?>
