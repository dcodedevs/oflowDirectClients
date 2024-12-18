<?php
$run_hook = function($guid) {
    global $o_main;
    // Load integration
    require_once __DIR__ . '/../internal_api/load.php';

    $api = new IntegrationAptic(array(
        'o_main' => $o_main
    ));

    $customer_info = $api->get_customer($guid);
    return array(
        'data' => $customer_info
    );
}
?>
