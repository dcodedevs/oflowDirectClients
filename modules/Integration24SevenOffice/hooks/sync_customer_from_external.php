<?php
$run_hook = function($data) {
    global $o_main;
    global $variables;

    // Params
    $update = $data['update'];
    $ownercompany_id = $data['ownercompany_id'];
    // Load integration
    require_once __DIR__ . '/../internal_api/load.php';
    $api = new Integration24SevenOffice(array(
        'o_main' => $o_main,
        'ownercompany_id' => $ownercompany_id
    ));

    // Return object
    $return = array();
    $customer_result = $api->get_customer_list();
    $customers = $customer_result['GetCompaniesResult']['Company'];
    foreach($customers as $customer) {
        $addresses = $customer['Addresses'];
        $post_address = $addresses['Post'];
        $visit_address = $addresses['Visit'];
        $phone_numbers = $customer['PhoneNumbers'];
        $email_address = $customer['EmailAddresses'];

        // check if customer exists
        $sql = "SELECT c.*,
        cei.external_sys_id external_sys_id,
        cei.external_id external_id
        FROM customer c
        JOIN customer_externalsystem_id cei ON cei.customer_id = c.id AND cei.ownercompany_id = ?
        WHERE cei.external_id = ?";
        $o_query = $o_main->db->query($sql, array($ownercompany_id, $customer['Id']));
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
                paCountry = ?,
                phone = ?,
                email = ?
                WHERE id = ?";
                $o_query = $o_main->db->query($sql, array($customer['OrganizationNumber'], $customer['Name'],
                $post_address['Street'],
                $post_address['PostalCode'],
                $post_address['PostalArea'],
                $post_address['Country'],
                $phone_numbers['Work']['Value'],
                $email_address['Work']['Value'],
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
            paCountry = ?,
            phone = ?,
            email = ?";
            $o_query = $o_main->db->query($sql, array($customer['OrganizationNumber'], $customer['Name'],
            $post_address['Street'],
            $post_address['PostalCode'],
            $post_address['PostalArea'],
            $post_address['Country'],
            $phone_numbers['Work']['Value'],
            $email_address['Work']['Value']));
            if($o_query){
                $customer_id = $o_main->db->insert_id();
                if($customer_id > 0) {
                    $sql = "INSERT INTO customer_externalsystem_id SET
                    created = now(),
                    customer_id = ?,
                    external_id = ?,
                    ownercompany_id = ?";
                    $o_query = $o_main->db->query($sql, array($customer_id, $customer['Id'], $ownercompany_id));
                }
                $return['created']++;
            }
        }
    }
    return $return;
}
?>
