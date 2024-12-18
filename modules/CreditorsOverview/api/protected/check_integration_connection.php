<?php
$customer_id = $v_data['params']['customer_id'];
$username= $v_data['params']['username'];

$s_sql = "SELECT creditor.* FROM customer
JOIN creditor ON creditor.customer_id = customer.id
WHERE customer.id = ".$o_main->db->escape($customer_id)."
AND customer.content_status < 2 GROUP BY customer.id ORDER BY customer.name";
$o_query = $o_main->db->query($s_sql);
$creditor = ($o_query ? $o_query->row_array() : array());

if($creditor){
    if($creditor['integration_module'] == "Integration24SevenOffice"){
        require_once __DIR__ . '/../../../Integration24SevenOffice/internal_api/load.php';
        ob_start();
        $api = new Integration24SevenOffice(array(
            'identityId' => $creditor['entity_id'],
            'creditorId' => $creditor['id'],
            'o_main' => $o_main
        ));
        if($api->error == "") {
            $v_return['data'] = 1;
        } else {
            $v_return['error'] = $api->error;
            $v_return['identities'] = $api->identities;
        }
    } else if($creditor['integration_module'] == "IntegrationTripletex"){
        require_once __DIR__ . '/../../../IntegrationTripletex/internal_api/load.php';
        $api = new IntegrationTripletex(array(
            'creditorId' => $creditor['id'],
            'o_main' => $o_main
        ));
        if($api->error == "") {
            $v_return['data'] = 1;
        } else {
            $v_return['error'] = $api->error;
        }
    }
} else {
    $v_return['error'] = 'Customer Not found';
}
?>
