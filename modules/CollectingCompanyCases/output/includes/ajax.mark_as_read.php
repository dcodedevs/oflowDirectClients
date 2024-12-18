<?php
$collecting_case_id = $_POST['case_id'];
$creditor_id = $_POST['creditor_id'];
$message_ids= $_POST['message_ids'];


$s_sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_id));
$creditor = ($o_query ? $o_query->row_array() : array());
if($moduleAccesslevel > 10)
{
	$s_sql = "SELECT * FROM collecting_company_cases WHERE id = ? AND creditor_id = ?";
    $o_query = $o_main->db->query($s_sql, array($collecting_case_id, $creditor['id']));
    $case_data = ($o_query ? $o_query->row_array() : array());
    if($case_data) {        
        $s_sql = "SELECT * FROM creditor_collecting_company_chat WHERE creditor_id = ? AND collecting_company_case_id=? AND IFNULL(read_check, 0) = 0
        ORDER BY created DESC";
        $o_query = $o_main->db->query($s_sql, array($case_data['creditor_id'], $case_data['id']));
        $selected_chat_messages = ($o_query ? $o_query->result_array() : array());
        var_dump($o_main->db->last_query());
        $message_ids_array = json_decode($message_ids, true);
        foreach($selected_chat_messages as $selected_chat_message) {
            if(in_array($selected_chat_message['id'], $message_ids_array)) {
                $s_sql = "UPDATE creditor_collecting_company_chat SET read_check = 1 WHERE id = ?";
                $o_query = $o_main->db->query($s_sql, array($selected_chat_message['id']));
                if($o_query){                    
                    $fw_return_data = 1;
                }
            }
        }
    }
}
