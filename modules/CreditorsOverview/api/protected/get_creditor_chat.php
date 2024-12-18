<?php
$creditor_id = $v_data['params']['creditor_id'];
$collecting_company_case_id = $v_data['params']['collecting_company_case_id'];
$username = $v_data['params']['username'];

$s_sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_id));
$creditor = ($o_query ? $o_query->row_array() : array());
if($creditor){
    $s_sql = "SELECT * FROM creditor_collecting_company_chat WHERE creditor_id = ?
    GROUP BY collecting_company_case_id
    ORDER BY created DESC";
    $o_query = $o_main->db->query($s_sql, array($creditor['id']));
    $active_chats = ($o_query ? $o_query->result_array() : array());
    

    $s_sql = "SELECT * FROM creditor_collecting_company_chat WHERE creditor_id = ? AND collecting_company_case_id=?
    ORDER BY created DESC";
    $o_query = $o_main->db->query($s_sql, array($creditor['id'], $collecting_company_case_id));
    $selected_chat_messages = ($o_query ? $o_query->result_array() : array());

    
    $s_sql = "SELECT * FROM creditor_collecting_company_chat WHERE creditor_id = ?
    AND IFNULL(read_check,0) = 0 AND IFNULL(message_from_oflow, 0) = 1
    GROUP BY collecting_company_case_id
    ORDER BY created DESC";
    $o_query = $o_main->db->query($s_sql, array($creditor['id']));
    $unread_chats = ($o_query ? $o_query->result_array() : array());
    

	$v_return['creditor'] = $creditor;
	$v_return['active_chats'] = $active_chats;
	$v_return['selected_chat_messages'] = $selected_chat_messages;
	$v_return['unread_chats'] = $unread_chats;
	$v_return['status'] = 1;
}
?>
