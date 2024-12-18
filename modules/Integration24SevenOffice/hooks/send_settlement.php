<?php
$run_hook = function($data) {
    global $o_main;
    global $variables;

    // Params
    $creditor_id = $data['creditor_id'];
    $settlement_id = $data['settlement_id'];


    $sql = "SELECT * FROM creditor WHERE id = ?";
    $o_query = $o_main->db->query($sql, array($creditor_id));
    $creditorData = $o_query ? $o_query->row_array() : array();
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
        
        $data = array();
        
        $sql = "SELECT * FROM cs_settlement WHERE id = '".$o_main->db->escape_str($settlement_id)."'";
        $o_query = $o_main->db->query($sql);
        $settlement = $o_query ? $o_query->row_array() : array();
            
        require(__DIR__."/../../CSSettlements/output/includes/fnc_get_settlement_sending_info.php");
        $result = get_settlement_sending_info($o_main,$settlement['id'], $creditorData['id']);
        if($result['error'] == ""){
            $data['total_bank_amount'] = $result['total_bank_amount'];
            $data['total_vat_amount'] = $result['total_vat_amount'];
            $data['invoices'] = $result['invoices'];
            $data['date'] = date("c", strtotime($settlement['date']));
            $sent_result = $api->send_settlement($data);
            if($sent_result['result']){
                $return['result']=$sent_result['result'];
            } else {
                $return['error'] = $sent_result['error'];
            }
        } else {
            $return['error'] = 'Error with sum';
        }
    }
    return $return;
}

?>
