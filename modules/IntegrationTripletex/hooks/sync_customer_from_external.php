<?php
$run_hook = function($data) {
    global $o_main;
    global $variables;

    // Params
    $update = $data['update'];
    $ownercompany_id = $data['ownercompany_id'];
    // Load integration
    require_once __DIR__ . '/../internal_api/load.php';
    $api = new IntegrationTripletex(array(
        'o_main' => $o_main,
        'ownercompany_id' => $ownercompany_id
    ));

    // Return object
    $return = array();
    $customers = $api->get_customer_list(array("count" => 10000, 'isInactive' => false, 'fields'=> '*,postalAddress(*)'));

    foreach($customers as $customer) {
        if($customer['isCustomer']){
            $addresses = $customer['postalAddress'];
            $email_address = $customer['invoiceEmail'];

            // check if customer exists
            $sql = "SELECT c.*,
            cei.external_sys_id external_sys_id,
            cei.external_id external_id
            FROM customer c
            JOIN customer_externalsystem_id cei ON cei.customer_id = c.id AND cei.ownercompany_id = ?
            WHERE cei.external_id = ?";
            $o_query = $o_main->db->query($sql, array($ownercompany_id, $customer['customerNumber']));
            $local_customer = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();
            if($local_customer) {
                if($update) {
                    $sql = "UPDATE customer SET
                    updated = now(),
        			publicRegisterId = ?,
                    name = ?,
                    paStreet = ?,
                    paPostalNumber = ?,
                    paCity = ?,
                    email = ?
                    WHERE id = ?";
                    $o_query = $o_main->db->query($sql, array($customer['organizationNumber'], $customer['name'],
                    $addresses['addressLine1'],
                    $addresses['postalCode'],
                    $addresses['city'],
                    $email_address,
                    $local_customer['id']));
                    if($o_query){
                        $return['updated']++;
                    }
                }
            } else {
                $sql = "INSERT INTO customer SET
                created = now(),
    			publicRegisterId = ?,
                name = ?,
                paStreet = ?,
                paPostalNumber = ?,
                paCity = ?,
                email = ?";
                $o_query = $o_main->db->query($sql, array($customer['organizationNumber'], $customer['name'],
                $addresses['addressLine1'],
                $addresses['postalCode'],
                $addresses['city'],
                $email_address));
                if($o_query){
                    $customer_id = $o_main->db->insert_id();
                    if($customer_id > 0) {
                        $sql = "INSERT INTO customer_externalsystem_id SET
                        created = now(),
                        customer_id = ?,
                        external_id = ?,
                        ownercompany_id = ?";
                        $o_query = $o_main->db->query($sql, array($customer_id, $customer['customerNumber'], $ownercompany_id));
                    }
                    $return['created']++;
                }
            }
        }
    }
    return $return;
}
?>
