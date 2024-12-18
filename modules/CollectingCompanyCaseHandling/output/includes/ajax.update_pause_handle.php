<?php 
$checked = $_POST['checked'];
$paused_id = $_POST['paused_id'];

if($checked) {
    $sql_init = "UPDATE collecting_company_case_paused SET message_handled = NOW(), message_handled_by = ? WHERE id = ?";
    $o_query = $o_main->db->query($sql_init, array($variables->loggID, $paused_id));
} else {
    $sql_init = "UPDATE collecting_company_case_paused SET message_handled = '0000-00-00 00:00:00', message_handled_by = '' WHERE id = ?";
    $o_query = $o_main->db->query($sql_init, array($paused_id));
}


?>