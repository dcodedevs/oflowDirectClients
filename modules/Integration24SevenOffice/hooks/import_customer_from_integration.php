<?php
$run_hook_import = function($data) {
    global $o_main;
    global $variables;

    // Params
    $external_id = $data['customer_external_id'];
    $ownercompany_id = 1;
    // Load integration
    require_once __DIR__ . '/../internal_api/load.php';
    $api = new Integration24SevenOffice(array(
        'o_main' => $o_main,
        'ownercompany_id' => $ownercompany_id
    ));

    $return = array();
    // check if customer exists
    $sql = "SELECT c.*,
    cei.external_sys_id external_sys_id,
    cei.external_id external_id
    FROM customer c
    LEFT JOIN customer_externalsystem_id cei ON cei.customer_id = c.id AND cei.ownercompany_id = '".$o_main->db->escape_str($ownercompany_id)."'
    WHERE cei.external_id = ?";
    $o_query = $o_main->db->query($sql, array($external_id));
    $customer_data = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();

    if(!$customer_data){
        $search_data = array();
        $search_data['customerIds'] = array($external_id);
        $customer_info = $api->get_customer_list($search_data);

        if($customer_info){
            //customer with that external id not found in system
            if(isset($customer_info['GetCompaniesResult']) && count($customer_info['GetCompaniesResult']) > 0){
                $customer_result = $customer_info['GetCompaniesResult']['Company'];

                $invoice_by = 0;
                $customer_name = $customer_result['Name'];
                $customer_invoice_email = $customer_result['EmailAddresses']['Invoice']['Value'];
                $customer_email = $customer_result['EmailAddresses']['Work']['Value'];
                $customer_phone = $customer_result['PhoneNumbers']['Work']['Value'];
                if($customer_invoice_email != ""){
                    $invoice_by = 1;
                }
                $customer_pa_street = $customer_result['Addresses']['Post']['Street'];
                $customer_pa_postal = $customer_result['Addresses']['Post']['PostalCode'];
                $customer_pa_city = $customer_result['Addresses']['Post']['PostalArea'];
                $customer_pa_country = $customer_result['Addresses']['Post']['Country'];


                $customer_va_street = $customer_result['Addresses']['Visit']['Street'];
                $customer_va_postal = $customer_result['Addresses']['Visit']['PostalCode'];
                $customer_va_city = $customer_result['Addresses']['Visit']['PostalArea'];
                $customer_va_country = $customer_result['Addresses']['Visit']['Country'];
                $org_nr = $customer_result['OrganizationNumber'];

                $s_sql = "INSERT INTO customer SET
                created = now(),
                createdBy= ?,
                name= ?,
                phone= ?,
                paStreet= ?,
                paPostalNumber=?,
                paCity=?,
                paCountry=?,
                vaStreet=?,
                vaPostalNumber=?,
                vaCity=?,
                vaCountry=?,
                invoiceBy=?,
                invoiceEmail=?,
                email = ?,
                publicRegisterId = ?";

                $o_query = $o_main->db->query($s_sql, array($variables->loggID, $customer_name, $customer_phone, $customer_pa_street, $customer_pa_postal, $customer_pa_city, $customer_pa_country, $customer_va_street, $customer_va_postal, $customer_va_city, $customer_va_country, $invoice_by, $customer_invoice_email, $customer_email, $org_nr));

    			if($o_query)
    			{
                    $customer_id = $o_main->db->insert_id();
                    $sql = "INSERT INTO customer_externalsystem_id SET external_id = '".$o_main->db->escape_str($external_id)."', customer_id = '".$o_main->db->escape_str($customer_id)."', ownercompany_id = '".$o_main->db->escape_str($ownercompany_id)."'";
                    $o_query = $o_main->db->query($sql);
                }
                //empty external id
            }
        }
    }
}
?>
