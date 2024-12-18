<?php
if(!function_exists("calculate_paid_fees")) {
	function calculate_paid_fees($invoice) {
		global $o_main;

		$s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND link_id = ? AND creditor_id = ? AND invoice_nr <> ?";
		$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id'], $invoice['invoice_nr']));
		$linked_transactions_not_connected = ($o_query ? $o_query->result_array() : array());

		$s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND link_id = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%\_%' ORDER BY created DESC";
		$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
		$claim_transactions = ($o_query ? $o_query->result_array() : array());

		$chargedInterest = 0;
		$chargedFee = 0;
		$fees_forgiven = 0;

		foreach($claim_transactions as $claim_transaction) {
			$commentArray = explode("_", $claim_transaction['comment']);
			if($commentArray[2] == "interest"){
			   $transactionType = "interest";
			} else if($commentArray[2] == "reminderFee"){
			  $transactionType = "reminderFee";
			} else if($commentArray[0] == "Rente"){
				$transactionType = "interest";
			} else {
				$transactionType = "reminderFee";
			}
			if($transactionType == "interest") {
				$chargedInterest += $claim_transaction['amount'];
			} else if($transactionType == "reminderFee"){
				$chargedFee += $claim_transaction['amount'];
			}
		}
		$s_sql = "SELECT * FROM creditor_transactions WHERE (system_type='Payment' OR system_type='CreditnoteCustomer') AND link_id = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0)  ORDER BY created DESC";
		$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
		$all_payments = ($o_query ? $o_query->result_array() : array());

		$original_amount = $invoice['amount'];

		foreach($all_payments as $all_payment) {
			$original_amount += $all_payment['amount'];
		}
		$overpaidOriginalAmount = 0;
		if($original_amount < 0){
			$overpaidOriginalAmount = $original_amount*-1;
		}

		if($overpaidOriginalAmount > 0){
			if($overpaidOriginalAmount >= $chargedFee) {
				$reminder_fee = $chargedFee;
				$overpaidOriginalAmount -= $chargedFee;
			} else {
				$reminder_fee = $overpaidOriginalAmount;
				$overpaidOriginalAmount = 0;
			}

			if($overpaidOriginalAmount >= $chargedInterest) {
				$interest_fee = $chargedInterest;
				$overpaidOriginalAmount -= $chargedInterest;
			} else {
				$interest_fee = $overpaidOriginalAmount;
				$overpaidOriginalAmount = 0;
			}
		}
		if(($interest_fee + $reminder_fee) == 0) {
			$fees_forgiven_sql = ", fees_status = 2";
			$fees_forgiven = 1;
		}
	}
}
?>
