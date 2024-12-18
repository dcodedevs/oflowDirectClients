<?php
$run_hook = function($data) {
    global $o_main;
    global $variables;

    // Params
    $external_customer_id = $data['external_customer_id'];
    $creditor_id = $data['creditor_id'];
    $type = $data['type'];

    $sql = "SELECT * FROM creditor WHERE id = ?";
    $o_query = $o_main->db->query($sql, array($creditor_id));
    $creditorData = $o_query ? $o_query->row_array() : array();

    $sql = "SELECT * FROM customer WHERE creditor_id = ? AND creditor_customer_id = ?";
    $o_query = $o_main->db->query($sql, array($creditor_id, $external_customer_id));
    $customer = $o_query ? $o_query->row_array() : array();
    // Return object
    $return = array();
    $return['result'] = 0;
    if($creditorData){
        require_once __DIR__ . '/../internal_api/load.php';
		$v_config = array(
            'ownercompany_id' => 1,
            'identityId' => $creditorData['entity_id'],
            'creditorId' => $creditorData['id'],
            'o_main' => $o_main
        );
		$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE username = '".$o_main->db->escape_str($data['username'])."' AND creditor_id = '".$o_main->db->escape_str($creditorData['id'])."'";
		$o_query = $o_main->db->query($s_sql);
		if($o_query && 0 < $o_query->num_rows())
		{
			$v_int_session = $o_query->row_array();
			$v_config['session_id'] = $v_int_session['session_id'];
		}
        $api = new Integration24SevenOffice($v_config);
		$type_string = "Business";
		if($type == 1){
			$type_string = "Consumer";
		}
		$customerName = $customer['name'];
		if($customer['middlename'] != ""){
			$customerName .= " ".$customer['middlename'];
		} else if($customer['lastname'] != ""){
			$customerName .= " ".$customer['lastname'];
		}
        $search_data = array(
            'external_customer_id'=>$external_customer_id,
            'type' => $type_string,
			'name' => $customerName
        );
        $customer_info = $api->update_customer_type($search_data);
        if($customer_info['result']){
            $return['search_data'] = $search_data;
            $return['customer_info'] = $customer_info;
            $return['result'] = 1;
        } else {
            $return['error'] = $customer_info['error'];
        }
    }
    return $return;
}

?>
