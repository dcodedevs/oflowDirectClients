<?php
$run_hook = function($data) {
    global $o_main;
    global $variables;

    $sql = "SELECT * FROM creditor WHERE id = ?";
    $o_query = $o_main->db->query($sql, array($data['creditor_id']));
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
		$bookaccounts = $api->get_account_list();
		$return['bookaccounts'] = $bookaccounts;

    }
    return $return;
}

?>
