<?php

$sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($sql, array($creditorId));
$creditorData = $o_query ? $o_query->row_array() : array();
if($creditorData) {
    require_once __DIR__ . '/../../../../'.$creditorData['integration_module'].'/internal_api/load.php';
    if($creditorData['entity_id'] == ""){
        echo $formText_NoEntityId_output;
        // $api2 = new Integration24SevenOffice(array(
        //     'ownercompany_id' => 1,
        //     'identityId' => $creditorData['entity_id'],
        //     'o_main' => $o_main,
        //     'creditorId'=> $creditorData['id']
        //     'getIdentityIdByName' => "Value Accounting Kristiansand AS"
        // ));
    } else {

        $api = new Integration24SevenOffice(array(
            'ownercompany_id' => 1,
            'identityId' => $creditorData['entity_id'],
            'o_main' => $o_main
        ));
        ?>

        <pre>
        <?php
        if($api->error == "") {
            $data = array();
            $invoicesList = $api->get_invoice_pdf($data);
        }
        ?>
        </pre>
        <?php
    }
}
?>
