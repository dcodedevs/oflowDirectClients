<?php 
$creditor_id = $_POST['creditor_id'];
$checked = $_POST['checked'];
if($creditor_id > 0){
    $s_sql = "UPDATE creditor SET activate_project_code_in_reminderletter = ? WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array((int)$checked, $creditor_id));
}
?>