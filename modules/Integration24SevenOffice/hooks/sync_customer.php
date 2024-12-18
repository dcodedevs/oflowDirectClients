<?php
$run_hook = function($data) {
    global $o_main;
    global $variables;

    // Params
    $customer_id = $data['customer_id'];
    $ownercompany_id = $data['ownercompany_id'];
    // Load integration
    require_once __DIR__ . '/../internal_api/load.php';
    $api = new Integration24SevenOffice(array(
        'o_main' => $o_main,
        'ownercompany_id' => $ownercompany_id
    ));

    // Return object
    $return = array();
    // check if customer exists
    $sql = "SELECT c.*,
    cei.external_sys_id external_sys_id,
    cei.external_id external_id
    FROM customer c
    LEFT JOIN customer_externalsystem_id cei ON cei.customer_id = c.id AND cei.ownercompany_id = ?
    WHERE c.id = ?";
    $o_query = $o_main->db->query($sql, array($ownercompany_id, $customer_id));
    $customer_data = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();
    if($customer_data['external_id'] > 0){
        $search_data = array();
        $search_data['customerIds'] = array($customer_data['external_id']);
        $customer_info = $api->get_customer_list($search_data);
        if($customer_info){
            //customer with that external id not found in system
            if(isset($customer_info['GetCompaniesResult']) && count($customer_info['GetCompaniesResult']) == 0){
                //empty external id
                $sql = "UPDATE customer_externalsystem_id SET external_id = 0 WHERE customer_id = '".$o_main->db->escape_str($customer_id)."' AND ownercompany_id = '".$o_main->db->escape_str($ownercompany_id)."'";
                $o_query = $o_main->db->query($sql);
            }
        }
    }

    // Get customer externalsystem id data
    $sql = "SELECT c.*,
    cei.external_sys_id external_sys_id,
    cei.external_id external_id
    FROM customer c
    LEFT JOIN customer_externalsystem_id cei ON cei.customer_id = c.id AND cei.ownercompany_id = ?
    WHERE c.id = ?";
    $o_query = $o_main->db->query($sql, array($ownercompany_id, $customer_id));
    $customer_data = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();
    if($customer_data){
        // Process customer data
        $customer_data_processed = array();
        $customer_data_processed['name'] = trim($customer_data['name']. " ".$customer_data['middlename']. " ".$customer_data['lastname']);
        if($customer_data['external_id'] > 0) {
            $customer_data_processed['external_id'] = $customer_data['external_id'];
        }
        $customer_data_processed['id'] = $customer_data['id'];
        $customer_data_processed['ownercompany_id'] = $ownercompany_id;

        if (strlen($customer_data['publicRegisterId']) === 9 && is_numeric($customer_data['publicRegisterId'])) {
            $customer_data_processed['vatNumber'] = $customer_data['publicRegisterId'];
        }
        //hardcoded to be overrite Confirmed by David
        $customer_data['paCountry'] = "NO";

        $customer_data_processed['invoiceEmail'] = $customer_data['invoiceEmail'];

        $customer_data_processed['mailAddress'] = array(
            'PostalArea' => $customer_data['paCity'],
            'PostalCode' => $customer_data['paPostalNumber'],
            'Street' => $customer_data['paStreet'],
            'Country' =>  $customer_data['paCountry']
        );

        $customer_result = $api->add_customer($customer_data_processed);
        $return['customer_sync_result'] = $customer_result;
    }
    return $return;
    }
?>
