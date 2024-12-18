<?php
function process_plan($formText_ErrorUpdatingDatabaseForPlan_output, $formText_ErrorUpdatingDatabaseForPlanline_output){
    global $o_main;

	$completedPlans = array();
	$interruptedPlans = array();
	$errors = array();
    $s_sql = "SELECT * FROM collecting_cases_payment_plan WHERE (status = 0 OR status is null) ORDER BY created DESC";
	$o_query = $o_main->db->query($s_sql);
	$paymentPlans = ($o_query ? $o_query->result_array() : array());
	foreach($paymentPlans as $paymentPlan){
		$collectingCaseId = $paymentPlan['collecting_case_id'];
		$sql = "SELECT * FROM collecting_cases WHERE id = '".$o_main->db->escape_str($collectingCaseId)."'";
		$o_query = $o_main->db->query($sql);
		$caseData = $o_query ? $o_query->row_array() : array();

		$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
		$o_query = $o_main->db->query($s_sql, array($caseData['creditor_id']));
		$creditor = ($o_query ? $o_query->row_array() : array());

		$s_sql = "SELECT customer.* FROM customer LEFT JOIN creditor ON creditor.customer_id = customer.id  WHERE creditor.id = ?";
		$o_query = $o_main->db->query($s_sql, array($caseData['creditor_id']));
		$creditorCustomer = ($o_query ? $o_query->row_array() : array());

		$s_sql = "SELECT * FROM customer WHERE customer.id = ?";
		$o_query = $o_main->db->query($s_sql, array($caseData['debitor_id']));
		$debitor = ($o_query ? $o_query->row_array() : array());

		$s_sql = "SELECT * FROM collecting_cases_payment_plan_lines WHERE (status = 0 OR status is null) AND collecting_cases_payment_plan_id = '".$o_main->db->escape_str($paymentPlan['id'])."'";
		$o_query = $o_main->db->query($s_sql);
		$collecting_cases_payment_plan_line = $o_query ? $o_query->row_array() : array();
		if($collecting_cases_payment_plan_line){
			$processing_due_date_time = time();

			$totalSumPaid = 0;
			$totalSumPaidInvoice = 0;
			$totalSumDue = 0;

			$s_sql = "SELECT * FROM creditor_invoice WHERE content_status < 2 AND collecting_case_id = ? ORDER BY created DESC";
			$o_query = $o_main->db->query($s_sql, array($collectingCaseId));
			$invoice = ($o_query ? $o_query->row_array() : array());

			$s_sql = "SELECT * FROM creditor_invoice_payment  WHERE invoice_number = ?  AND (before_or_after_case = 0 OR before_or_after_case is null)";
			$o_query = $o_main->db->query($s_sql, array($invoice['invoice_number']));
			$invoice_payments = ($o_query ? $o_query->result_array() : array());

			foreach($invoice_payments as $invoice_payment) {
				$totalSumPaidInvoice += $invoice_payment['amount'];
			}
			$sql = "SELECT creditor_invoice_payment.* FROM creditor_invoice_payment
			WHERE creditor_invoice_payment.invoice_number = ? AND creditor_invoice_payment.creditor_id = ? AND (before_or_after_case = 1)";
			$o_query = $o_main->db->query($sql, array($invoice['invoice_number'], $invoice['creditor_id']));
			$payments = $o_query ? $o_query->result_array() : array();
			foreach($payments as $payment) {
				$totalSumPaidInvoice += $payment['amount'];
			}
			$s_sql = "SELECT * FROM collecting_cases_claim_lines WHERE content_status < 2 AND collecting_case_id = ? ORDER BY claim_type ASC, created DESC";
			$o_query = $o_main->db->query($s_sql, array($collectingCaseId));
			$claims = ($o_query ? $o_query->result_array() : array());

			$s_sql = "SELECT * FROM collecting_cases_payments WHERE collecting_case_id = ? ORDER BY created DESC";
			$o_query = $o_main->db->query($s_sql, array($collectingCaseId));
			$payments = ($o_query ? $o_query->result_array() : array());
			foreach($payments as $payment) {
				$totalSumPaid += $payment['amount'];
			}
			$totalSumDue += $invoice['collecting_case_original_claim'];

			foreach($claims as $claim) {
				$totalSumDue += $claim['amount'];
			}

			$totalSumDue += $totalSumPaidInvoice;
			$totalSumDueAfterPayment = number_format($totalSumDue - $totalSumPaid, 2, ".", "");

			if(strtotime($collecting_cases_payment_plan_line['due_date']) < $processing_due_date_time || $totalSumDueAfterPayment <= 0){
				$s_sql = "UPDATE collecting_cases_payment_plan_lines SET
				status = 1 WHERE id = '".$o_main->db->escape_str($collecting_cases_payment_plan_line['id'])."'";
				$o_query = $o_main->db->query($s_sql);
				if($o_query){
					if($totalSumDueAfterPayment > 0) {
						$amountToPay = $paymentPlan['monthly_payment'];
						if($totalSumDueAfterPayment < $amountToPay) {
							$amountToPay = $totalSumDueAfterPayment;
						}

						$needToBePayed = $collecting_cases_payment_plan_line['amount_to_pay'];
						$payed = $collecting_cases_payment_plan_line['payed'];

						if($payed >= $needToBePayed) {
							$paymentDate = $collecting_cases_payment_plan_line['due_date'];
							$dayNumber = date("d", strtotime($paymentDate));
							$dueDate = date("Y-m-".$dayNumber, strtotime($paymentDate . " +1 month"));

							$s_sql = "INSERT INTO collecting_cases_payment_plan_lines SET
							id=NULL,
							created = now(),
							createdBy= 'autotask',
							status = 0,
							due_date = '".$o_main->db->escape_str($dueDate)."',
							amount_to_pay = '".$o_main->db->escape_str($amountToPay)."',
							collecting_cases_payment_plan_id = '".$o_main->db->escape_str($paymentPlan['id'])."',
							payed = 0";
							$o_query = $o_main->db->query($s_sql);
						} else {
							$s_sql = "UPDATE collecting_cases_payment_plan SET
							status = 2 WHERE id = '".$o_main->db->escape_str($paymentPlan['id'])."'";
							$o_query = $o_main->db->query($s_sql);
							if($o_query){
								$interruptedPlans[] = $paymentPlan;
							} else {
								$errors[] = $formText_ErrorUpdatingDatabaseForPlan_output." ".$paymentPlan['id'];
							}
						}
					} else {
						$s_sql = "UPDATE collecting_cases_payment_plan SET
						status = 1 WHERE id = '".$o_main->db->escape_str($paymentPlan['id'])."'";
						$o_query = $o_main->db->query($s_sql);
						if($o_query){
							$completedPlans[] = $paymentPlan;
						} else {
							$errors[] = $formText_ErrorUpdatingDatabaseForPlan_output." ".$paymentPlan['id'];
						}
					}
				} else {
					$errors[] = $formText_ErrorUpdatingDatabaseForPlanline_output." ".$collecting_cases_payment_plan_line['id'];
				}
			}
		}
	}
    return array($completedPlans, $interruptedPlans, $errors);
}
?>
