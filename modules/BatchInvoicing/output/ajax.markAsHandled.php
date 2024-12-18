<?php
if(isset($_POST["batch_id"]))
{
    $s_sql = "UPDATE batch_invoicing SET printing_handled = 1, updated = NOW(), updatedBy = ? WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($variables->loggID, $_POST["batch_id"]));
}
?>
