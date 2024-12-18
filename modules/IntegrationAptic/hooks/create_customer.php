<?php
$run_hook = function($creditor_data) {
    global $o_main;
    // Load integration
    require_once __DIR__ . '/../internal_api/load.php';

    $api = new IntegrationAptic(array(
        'o_main' => $o_main
    ));


    $new_customer_info = $api->update_customer($creditor_data);
    if($new_customer_info){
        $s_sql = "UPDATE creditor SET aptic_customer_id = ? WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($new_customer_info['customerGuid'],$creditor_data['id']));
    }
    return array(
        'data' => $new_customer_info
    );
}
?>
