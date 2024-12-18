<?php
$paymentsToSettle = $_POST['paymentsToSettle'];
$total_shares = array();
$collectingcompany_shares = array();
$creditor_shares= array();
$debitor_shares = array();
$agent_shares = array();
$hasErrors = false;
$creditorIds = array();
foreach($paymentsToSettle as $paymentId){
    $s_sql = "SELECT cmt.*, cmv.case_id FROM cs_mainbook_transaction cmt
	JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
	WHERE cmt.id = ? AND IFNULL(cmv.settlement_id, 0) = 0";
    $o_query = $o_main->db->query($s_sql, array($paymentId));
    $payment = $o_query ? $o_query->row_array() : array();

    $s_sql = "SELECT * FROM collecting_company_cases WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($payment['case_id']));
    $collecting_case = $o_query ? $o_query->row_array() : array();

    $s_sql = "SELECT * FROM creditor WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($collecting_case['creditor_id']));
    $creditor = $o_query ? $o_query->row_array() : array();
    if($payment && $collecting_case && $creditor) {
		if(!in_array($creditor['id'], $creditorIds)){
			$creditorIds[] = $creditor['id'];
		}
    } else {
        $hasErrors = true;
    }
}

if(!$hasErrors) {
    $collectingcompany_share_total = 0;
    $creditor_share_total = 0;
    $debitor_share_total = 0;
    $agent_share_total = 0;
    $total_share_total = 0;

    foreach($collectingcompany_shares as $collectingcompany_share) {
        $collectingcompany_share_total+=$collectingcompany_share;
    }
    foreach($creditor_shares as $creditor_share) {
        $creditor_share_total+=$creditor_share;
    }
    foreach($debitor_shares as $debitor_share) {
        $debitor_share_total+=$debitor_share;
    }
    foreach($agent_shares as $agent_share) {
        $agent_share_total+=$agent_share;
    }

    $s_sql = "INSERT INTO cs_settlement SET moduleID = '".$o_main->db->escape_str($moduleID)."', created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."',
    date = NOW()";
    $o_query = $o_main->db->query($s_sql);
    $collectingcompany_settlement_id = $o_main->db->insert_id();

    include_once(__DIR__.'/../../../CollectingCases/output/includes/tcpdf/tcpdf.php');

    $s_sql = "SELECT * FROM ownercompany";
    $o_query = $o_main->db->query($s_sql);
    $ownercompany = ($o_query ? $o_query->row_array() : array());

    if($collectingcompany_settlement_id > 0){
        $collecting_case_forgiven_shown = array();
		foreach($creditorIds as $creditorId) {

            $s_sql = "INSERT INTO cs_settlement_line SET moduleID = '".$o_main->db->escape_str($moduleID)."', created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."',
            cs_settlement_id = '".$o_main->db->escape_str($collectingcompany_settlement_id)."',
            creditor_id = '".$o_main->db->escape_str($creditorId)."'";
            $o_query = $o_main->db->query($s_sql);
            if(!$o_query) {
                $lineErrors = true;
            }
        }
        if(!$lineErrors) {
            foreach($paymentsToSettle as $paymentId){
			    $s_sql = "SELECT cmt.*, cmv.case_id FROM cs_mainbook_transaction cmt
				JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
				WHERE cmt.id = ? AND IFNULL(cmv.settlement_id, 0) = 0";
			    $o_query = $o_main->db->query($s_sql, array($paymentId));
			    $payment = $o_query ? $o_query->row_array() : array();
				if($payment) {
	                $s_sql = "UPDATE cs_mainbook_voucher SET settlement_id = ? WHERE id = ?";
	                $o_query = $o_main->db->query($s_sql, array($collectingcompany_settlement_id, $payment['cs_mainbook_voucher_id']));
				}
            }
        } else {
            $fw_error_msg[] = $formText_ErrorCreatingSettlementLines_output;
            $s_sql = "DELETE FROM cs_settlement WHERE id = ?";
            $o_query = $o_main->db->query($s_sql, array($collectingcompany_settlement_id));
        }
    } else {
        $fw_error_msg[] = $formText_ErrorCreatingSettlement_output;
    }
} else {
    $fw_error_msg[] = $formText_ErrorWithPaymentLines_output;
}
?>
