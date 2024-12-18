<?php
$run_hook = function($data) {
    global $o_main;
    global $variables;

    // Params
    $zip_file_path = $data['zip_file_path'];
    $zip_file_name = $data['zip_file_name'];
    $ownercompany_id = $data['ownercompany_id'];
    // Load integration
    require_once __DIR__ . '/../internal_api/load.php';
    $api = new IntegrationApix(array(
        'o_main' => $o_main,
        'ownercompany_id' => $ownercompany_id
    ));

    // Return object
    $return = array();

    // Process customer data
    $customer_data_processed = array();
    $customer_data_processed['zip_file_path'] = $zip_file_path;
    $customer_data_processed['zip_file_name'] = $zip_file_name;
    if(file_exists($zip_file_path)) {
        $customer_result = $api->send_print_zip($customer_data_processed);
        $return = $customer_result;
    }
    return $return;
}
?>
