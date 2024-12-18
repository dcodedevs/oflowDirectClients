<?php
$run_hook = function($creditor_data) {
    global $o_main;
    // Load integration
    require_once __DIR__ . '/../internal_api/load.php';

    $api = new IntegrationAptic(array(
        'o_main' => $o_main
    ));
    $success = 0;
    if($creditor_data['aptic_customer_id'] != ""){
        $success = $api->update_customer($creditor_data);
    }
    return array(
        'success' => $success
    );
}
?>
