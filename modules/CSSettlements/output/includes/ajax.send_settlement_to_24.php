<?php 

$creditorId = $_POST['creditorId'];
$settlementId = $_POST['settlementId'];


$s_sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($creditorId));
$creditor = $o_query ? $o_query->row_array() : array();

$sql = "SELECT * FROM cs_settlement_line WHERE cs_settlement_id = ? AND creditor_id = ?";
$o_query = $o_main->db->query($sql, array($settlementId, $creditorId));
$settlementLine = $o_query ? $o_query->row_array() : array();
if($creditor && $settlementLine) {
    // if(!$settlementLine['sent_to24']){
        $totalCreditor = 0;
        $totalCollecting = 0;
        
        $hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/send_settlement.php';
        if (file_exists($hook_file)) {
            include $hook_file;
            if (is_callable($run_hook)) {
                $hook_params['creditor_id'] =$creditor['id'];
                $hook_params['settlement_id'] =$settlementId;
                $connect_tries++;
                $hook_result = $run_hook($hook_params);
                // var_dump($hook_result);
                if($hook_result['result']){
                    $sql = "UPDATE cs_settlement_line SET sent_to24 = 1 WHERE id = ?";
                    $o_query = $o_main->db->query($sql, array($settlementLine['id']));
                } else {
                    $fw_error_msg[] = $hook_result['error'];
                }
            }
        }
    // }
}

?>