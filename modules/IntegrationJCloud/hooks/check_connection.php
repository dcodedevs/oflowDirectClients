<?php
$run_hook = function($data) {
    global $o_main;
    global $variables;

    // Params
    $letter_id = $data['letter_id'];
    $ownercompany_id = $data['ownercompany_id'];
    // Load integration
    require_once __DIR__ . '/../internal_api/load.php';
    $api = new IntegrationJCloud(array(
        'o_main' => $o_main,
        'ownercompany_id' => $ownercompany_id
    ));

    // Return object
    $return = array();
    // Process customer data
    $customer_data_processed = array();
    $customer_result = $api->test_connection();
    $return = $customer_result;
    return $return;
}
?>
