<?php
$run_hook = function($data) {
    global $o_main;
    global $variables;

    $ownercompany_id = $data['ownercompany_id'];
    if(intval($ownercompany_id) == 0){
        $ownercompany_id = 1;
    }
    // Load integration
    require_once __DIR__ . '/../internal_api/load.php';
    $api = new IntegrationTripletex(array(
        'ownercompany_id' => $ownercompany_id,
        'o_main' => $o_main
    ));

    // Return object
    $return = array();

    // Params
    $customer_id = $data['customer_id'];

    // Check if external id does exist
    $sql = "SELECT * FROM customer_externalsystem_id WHERE customer_id = ?  AND ownercompany_id = ?";
    $o_query = $o_main->db->query($sql, array($customer_id, $ownercompany_id));
    $has_external_id = $o_query && $o_query->num_rows();

    // Generate external id if needed
    if (!$has_external_id) {
        $sql = "SELECT * FROM ownercompany WHERE id = ?";
        $o_query = $o_main->db->query($sql, array($ownercompany_id));
        $ownercompany_settings = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();

        if ($ownercompany_settings['customerid_autoormanually'] == '1') {
            $nextCustomerId = $ownercompany_settings['nextCustomerId'];
            $o_main->db->query("INSERT INTO customer_externalsystem_id
            SET created = NOW(),
            ownercompany_id = ?,
            customer_id = ?,
            external_id = ?,
            external_sys_id = ?", array($ownercompany_id, $customer_id, $nextCustomerId, 0));
            $nextCustomerId++;
            $o_main->db->query("UPDATE ownercompany SET nextCustomerId = $nextCustomerId WHERE id = ?", array($ownercompany_id));
        }
    }

    // Get customer data + externalsystem id data
    $sql = "SELECT c.*,
    cei.external_sys_id external_sys_id,
    cei.external_id external_id
    FROM customer c
    LEFT JOIN customer_externalsystem_id cei ON cei.customer_id = c.id AND cei.ownercompany_id = ?
    WHERE c.id = ?";
    $o_query = $o_main->db->query($sql, array($ownercompany_id, $customer_id));
    $customer_data = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();

    // External sys id
    $customer_data_processed = array();
    $customer_data_processed['name'] = trim($customer_data['name']." ". $customer_data['middlename']." ". $customer_data['lastname']);
    if (!$customer_data['external_sys_id']) {
        $customer_data_processed['customerNumber'] = $customer_data['external_id'];
    }
    if (strlen($customer_data['publicRegisterId']) === 9 && is_numeric($customer_data['publicRegisterId'])) {
        $customer_data_processed['organizationNumber'] = $customer_data['publicRegisterId'];
    }
    $customer_data_processed['postalAddress'] = array(
        'addressLine1' => $customer_data['paStreet'] . ' ' . $customer_data['paStreet2'],
        // 'addressLine2' => $customer_data['paCity'] . ' ' . $customer_data['paCountry'],
        'postalCode' => $customer_data['paPostalNumber'],
        'city' => $customer_data['paCity']
    );
    $customer_data_processed['invoiceEmail'] = $customer_data['invoiceEmail'];
    $customer_data_processed['invoiceSendMethod'] = "EMAIL";

    // Sync customer
    if ($customer_data['external_sys_id']) {
        $customer_data_processed['id'] = $customer_data['external_sys_id'];
        $customer_update = $api->update_customer($customer_data_processed);
        $return['customer_sync_result'] = $customer_update;
    }
    else {
        // Add on API
        $new_customer_data = $api->add_customer($customer_data_processed);

        // Save externalsystem id and number
        $o_query = $o_main->db->query('UPDATE customer_externalsystem_id SET updated = ?, updatedBy = ?, external_sys_id = ? WHERE customer_id = ? AND ownercompany_id = ?',
         array(date('Y-m-d H:i:s'), $variables->loggID, $new_customer_data['value']['id'], $customer_data['id'], $ownercompany_id));

        $customer_data['external_sys_id'] = $new_customer_data['value']['id'];
        $return['customer_sync_result'] = $new_customer_data;
    }

    return $return;
    }
?>
