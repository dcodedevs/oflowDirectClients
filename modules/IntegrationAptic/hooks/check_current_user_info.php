<?php
$run_hook = function($data) {
    global $o_main;
    // Load integration
    require_once __DIR__ . '/../internal_api/load.php';

    $api = new IntegrationAptic(array(
        'o_main' => $o_main
    ));


    $current_user = $api->get_current_user();
    $debtors = $api->get_debtors();
    return array(
        'data' => $current_user,
        'debtors' => $debtors
    );
}
?>
