<?php 
$creditor_id = $_POST['creditor_id'];
$s_sql = "DELETE FROM creditor_transactions_status_log  WHERE source = 1";
$o_query = $o_main->db->query($s_sql, array($creditor_id));
?>