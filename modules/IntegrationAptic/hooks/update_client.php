<?php
$run_hook = function($creditor_data) {
    global $o_main;
    // Load integration
    require_once __DIR__ . '/../internal_api/load.php';

    $api = new IntegrationAptic(array(
        'o_main' => $o_main
    ));
    $success = 0;
    if($creditor_data['aptic_client_id'] != ""){
        $success = $api->update_client($creditor_data);
    } else {
        $new_customer_info = $api->update_client($creditor_data);
        if($new_customer_info){
            $s_sql = "UPDATE creditor SET aptic_client_id = ? WHERE id = ?";
            $o_query = $o_main->db->query($s_sql, array($new_customer_info['customerGuid'],$creditor_data['id']));
            $success = 1;
        }
    }
    return array(
        'success' => $success,
        'data' => $new_customer_info
    );
}
?>
