<?php 
$creditor_id = $_POST['creditor_id'];
$session_id = $_POST['session_id'];
$s_sql = "DELETE FROM  integration24sevenoffice_session
WHERE integration24sevenoffice_session.creditor_id = ? AND integration24sevenoffice_session.id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_id, $session_id));
?>