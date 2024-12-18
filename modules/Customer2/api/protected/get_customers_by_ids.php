<?php
$customerIds = $v_data['params']['customerIds'];
if(count($customerIds) > 0) {
    $s_sql = "SELECT customer.*, subscriptiontype.name as subscriptionTypeName, subscriptiontype.id as subscriptionTypeId FROM customer
    LEFT OUTER JOIN subscriptionmulti ON subscriptionmulti.customerId = customer.id
    LEFT OUTER JOIN subscriptiontype ON subscriptiontype.id = subscriptionmulti.subscriptiontype_id
    WHERE customer.content_status < 2 AND customer.id IN (".implode(",", $customerIds).") AND (subscriptiontype.hide_on_insider is null OR subscriptiontype.hide_on_insider = 0)
    GROUP BY customer.id ORDER BY customer.name";

    $o_query = $o_main->db->query($s_sql);
    $customers = $o_query ? $o_query->result_array() : array();
    $returnCustomers = array();
    foreach($customers as $customer) {
    	$s_sql = "SELECT * FROM contactperson WHERE contactperson.customerId = ? ORDER BY contactperson.name";
    	$o_query = $o_main->db->query($s_sql, array($customer['id']));
    	$contactPersons = $o_query ? $o_query->result_array() : array();

        if($v_data['params']['getMembershipConnections']){
        	$s_sql = "SELECT imao.* FROM intranet_membership_customer_connection imcc
            LEFT OUTER JOIN intranet_membership_attached_object imao ON imao.membership_id = imcc.membership_id
            WHERE imcc.customer_id = ?";
        	$o_query = $o_main->db->query($s_sql, array($customer['id']));
        	$connections = $o_query ? $o_query->result_array() : array();

            $customer['membership_connections'] = $connections;
            $persons = array();
            foreach($contactPersons as $contactPerson) {
            	$s_sql = "SELECT imao.* FROM intranet_membership_contactperson_connection imcc
                LEFT OUTER JOIN intranet_membership_attached_object imao ON imao.membership_id = imcc.membership_id
                WHERE imcc.contactperson_id = ?";
            	$o_query = $o_main->db->query($s_sql, array($contactPerson['id']));
            	$connections = $o_query ? $o_query->result_array() : array();
                $contactPerson['membership_connections'] = $connections;
            	array_push($persons, $contactPerson);
            }
        } else {
            $persons = $contactPersons;
        }
    	$customer['contactPersons'] = $persons;
    	array_push($returnCustomers, $customer);
    }
    $v_return['data'] = $returnCustomers;
    $s_sql = "SELECT customer.*, subscriptiontype.name as subscriptionTypeName, subscriptiontype.id as subscriptionTypeId FROM customer
    LEFT OUTER JOIN subscriptionmulti ON subscriptionmulti.customerId = customer.id
    LEFT OUTER JOIN subscriptiontype ON subscriptiontype.id = subscriptionmulti.subscriptiontype_id
    WHERE customer.content_status < 2 AND customer.id IN (".implode(",", $customerIds).") AND (subscriptiontype.show_on_insider_persontab = 1)
    GROUP BY customer.id ORDER BY customer.name";

    $o_query = $o_main->db->query($s_sql);
    $customers = $o_query ? $o_query->result_array() : array();
    $returnCustomers = array();
    foreach($customers as $customer) {
        $s_sql = "SELECT * FROM contactperson WHERE contactperson.customerId = ? ORDER BY contactperson.name";
        $o_query = $o_main->db->query($s_sql, array($customer['id']));
        $contactPersons = $o_query ? $o_query->result_array() : array();

        if($v_data['params']['getMembershipConnections']){
            $s_sql = "SELECT imao.* FROM intranet_membership_customer_connection imcc
            LEFT OUTER JOIN intranet_membership_attached_object imao ON imao.membership_id = imcc.membership_id
            WHERE imcc.customer_id = ?";
            $o_query = $o_main->db->query($s_sql, array($customer['id']));
            $connections = $o_query ? $o_query->result_array() : array();

            $customer['membership_connections'] = $connections;
            $persons = array();
            foreach($contactPersons as $contactPerson) {
                $s_sql = "SELECT imao.* FROM intranet_membership_contactperson_connection imcc
                LEFT OUTER JOIN intranet_membership_attached_object imao ON imao.membership_id = imcc.membership_id
                WHERE imcc.contactperson_id = ?";
                $o_query = $o_main->db->query($s_sql, array($contactPerson['id']));
                $connections = $o_query ? $o_query->result_array() : array();
                $contactPerson['membership_connections'] = $connections;
                array_push($persons, $contactPerson);
            }
        } else {
            $persons = $contactPersons;
        }
        $customer['contactPersons'] = $persons;
        array_push($returnCustomers, $customer);
    }
    $v_return['dataOnPersonTab'] = $returnCustomers;

    $v_return['rowCount'] = $rowCount;
}
?>
