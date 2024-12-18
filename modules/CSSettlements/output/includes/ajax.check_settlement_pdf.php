<?php
$creditorId = $_POST['creditorId'];
$settlementId = $_POST['settlementId'];

$s_sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($creditorId));
$creditor = $o_query ? $o_query->row_array() : array();

$sql = "SELECT * FROM cs_settlement WHERE id = $settlementId";
$o_query = $o_main->db->query($sql);
$settlement = $o_query ? $o_query->row_array() : array();
if($creditor && $settlement){
	$s_sql = "SELECT cmv.*, cmv.case_id, CONCAT_WS(' ',deb.name, deb.middlename, deb.lastname) as debitorName FROM cs_mainbook_voucher cmv
	JOIN collecting_company_cases cc ON cc.id = cmv.case_id
	JOIN customer deb ON deb.id = cc.debitor_id
	WHERE IFNULL(cmv.settlement_id, 0) = ? AND cc.creditor_id = ?";
	$o_query = $o_main->db->query($s_sql, array($settlement['id'], $creditor['id']));
	$payments = $o_query ? $o_query->result_array() : array();
	$errorWithChecksum = false;
	$totalMain = 0;
	$totalLedger = 0;
	foreach($payments as $payment){
		$s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = ?";
		$o_query = $o_main->db->query($s_sql, array($payment['id']));
		$transactions = $o_query ? $o_query->result_array() : array();

		foreach($transactions as $transaction) {
			$s_sql = "SELECT * FROM cs_bookaccount WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($transaction['bookaccount_id']));
			$bookaccount = $o_query ? $o_query->row_array() : array();
			if($transaction['bookaccount_id'] == 20){
				$totalMain += $transaction['amount'];
			} else if($bookaccount['summarize_on_ledger'] == 2) {
				$transactionsToShow[] = $transaction;
			}
		}
		foreach($transactionsToShow as $transaction) {
			$s_sql = "SELECT * FROM cs_bookaccount WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($transaction['bookaccount_id']));
			$bookaccount = $o_query ? $o_query->row_array() : array();
			$totalMain += $transaction['amount'];
		}

		foreach($transactions as $transaction) {
			if($transaction['bookaccount_id'] == 16){
				$totalLedger+=$transaction['amount'];
				break;
			}
		}
		if($totalLedger != $totalMain) {
			$errorWithChecksum = true;
		}
	}
	if(!$errorWithChecksum){

	} else {
		$fw_error_msg[] = $formText_ErrorWithChecksum_output;
	}
} else {
	$fw_error_msg[] = $formText_MissingInfo_output;
}
?>
