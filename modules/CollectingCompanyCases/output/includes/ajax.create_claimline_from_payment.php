<?php 
$case_id = $_POST['case_id'];
$payment_transaction_id = $_POST['payment_transaction_id'];

if($case_id > 0 && $payment_transaction_id){
    $s_sql = "SELECT * FROM collecting_company_cases WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($case_id));
    $companyCase = ($o_query ? $o_query->row_array() : array());

    $sql = "SELECT * FROM creditor_transactions WHERE id = ?";
    $o_query = $o_main->db->query($sql, array($payment_transaction_id));
    $local_transaction = $o_query ? $o_query->row_array() : array();
    var_dump(date("Y-m-d", strtotime($companyCase['created'])));
    var_dump(date("Y-m-d", strtotime($local_transaction['created'])));
    // if(strtotime($local_transaction['created']) >= strtotime(date("Y-m-d", strtotime($companyCase['created'])))) {
        if(intval($local_transaction['company_claimline_id']) == 0){
            $payment_after_closed_sql = ", payment_after_closed = 0";
            if($companyCase['case_closed_date'] != "0000-00-00" && $companyCase['case_closed_date'] != ""){
                $payment_after_closed_sql = ", payment_after_closed = 1";
            }
            $s_sql = "SELECT cmt.* FROM cs_mainbook_transaction cmt
            JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
            WHERE cmv.case_id = '".$o_main->db->escape_str($companyCase['id'])."' AND cmt.bookaccount_id = 20 AND IFNULL(cmt.used_as_settlement_payment,0) = 0";
            $o_query = $o_main->db->query($s_sql);
            $mainclaim_to_creditor_transaction = ($o_query ? $o_query->row_array() : array());
            $claim_type_id = 18;
            $trigger_mainclaim_transaction_update = false;
            if($mainclaim_to_creditor_transaction){
                if(abs($mainclaim_to_creditor_transaction['amount']) == abs($local_transaction['amount'])) {
                    $trigger_mainclaim_transaction_update = true;
                    $claim_type_id = 15;
                    $payment_after_closed_sql = ", payment_after_closed = 1";
                }
            }

            $s_sql = "INSERT INTO collecting_company_cases_claim_lines SET
            id=NULL,
            moduleID = ?,
            created = now(),
            createdBy= ?,
            collecting_company_case_id = ?,
            name= ?,
            date = '".$o_main->db->escape_str(date("Y-m-d", strtotime($local_transaction['date'])))."',
            claim_type='".$o_main->db->escape_str($claim_type_id)."',
            amount= '".$o_main->db->escape_str($local_transaction['amount'])."'".$payment_after_closed_sql;
            $o_query = $o_main->db->query($s_sql, array($moduleID, 'import', $companyCase['id'], $formText_DirectPaymentToCreditor_output." ".date("d.m.Y", strtotime($local_transaction['date']))));
            if($o_query) {
                $claimline_id = $o_main->db->insert_id();
            }															
            
            if($claimline_id > 0) {
                if($trigger_mainclaim_transaction_update){																	
                    $s_sql = "UPDATE cs_mainbook_transaction SET used_as_settlement_payment = ? WHERE id = ?";
                    $o_query = $o_main->db->query($s_sql, array($claimline_id, $mainclaim_to_creditor_transaction['id']));
                }
                $s_sql = "UPDATE creditor_transactions SET company_claimline_id = ? WHERE id = '".$o_main->db->escape_str($local_transaction['id'])."'";
                $o_query = $o_main->db->query($s_sql, array($claimline_id));
            }
        }
    // }
}
?>