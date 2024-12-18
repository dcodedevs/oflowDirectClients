<?php
$ip = $v_data['params']['ip'];
$code = $v_data['params']['code'];

$s_sql = "select * from collecting_cases_debitor_codes_log where ip = ? AND successful = 0 AND created BETWEEN  DATE_FORMAT(NOW(), '%Y-%m-%d %H:00:00')  AND  DATE_FORMAT(NOW(), '%Y-%m-%d %H:59:59')";
$o_query = $o_main->db->query($s_sql, array($ip));
$attempts = $o_query ? $o_query->result_array() : array();

include(__DIR__."/../../output/includes/fnc_calculate_interest.php");
require_once __DIR__ . '/../../../Integration24SevenOffice/internal_api/load.php';
if(count($attempts) < 3){
    $s_sql = "INSERT INTO collecting_cases_debitor_codes_log SET code_used = ?, successful = 0, ip = ?, created = NOW()";
    $o_query = $o_main->db->query($s_sql, array($code, $ip));
    if($o_query){
        $log_id = $o_main->db->insert_id();
        $s_sql = "select * from collecting_cases_debitor_codes where code = ? AND expiration_time > NOW()";
        $o_query = $o_main->db->query($s_sql, array($code));
        $key_item = $o_query ? $o_query->row_array() : array();
        if($key_item) {
            $s_sql = "UPDATE collecting_cases_debitor_codes_log SET successful = 1 WHERE id = ?";
            $o_query = $o_main->db->query($s_sql, array($log_id));
            $v_return['status'] = 1;

            $s_sql = "select * from customer where id = ?";
            $o_query = $o_main->db->query($s_sql, array($key_item['customer_id']));
            $customer = $o_query ? $o_query->row_array() : array();


            $s_sql = "SELECT cc.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as creditorName FROM collecting_cases cc
            LEFT OUTER JOIN creditor_transactions ci ON ci.collectingcase_id = cc.id
            LEFT JOIN creditor cred ON cred.id = cc.creditor_id
            LEFT JOIN customer c ON cred.customer_id = c.id
            WHERE cc.id = ? AND cc.debitor_id = ? AND (cc.status = 0 OR cc.status is null OR cc.status = 1)
            ORDER BY cc.id";
            $o_query = $o_main->db->query($s_sql, array($key_item['collecting_cases_id'], $customer['id']));
            $collecting_cases = $o_query ? $o_query->result_array() : array();
            $collecting_cases_final = array();
            foreach($collecting_cases as $collecting_case) {
                $s_sql = "select * from creditor where id = ?";
                $o_query = $o_main->db->query($s_sql, array($collecting_case['creditor_id']));
                $creditorData = $o_query ? $o_query->row_array() : array();
				$invoices_for_pdf = array();
                if($creditorData){
                    $api = new Integration24SevenOffice(array(
                        'ownercompany_id' => 1,
                        'identityId' => $creditorData['entity_id'],
                        'creditorId' => $creditorData['id'],
                        'o_main' => $o_main
                    ));
                    if($api->error == "") {
						if($key_item['collecting_company_case_id'] > 0) {
	                        $s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND collecting_company_case_id = ? ORDER BY created DESC";
	                        $o_query = $o_main->db->query($s_sql, array($collecting_case['id']));
	                        $invoices_to_check = ($o_query ? $o_query->result_array() : array());
						} else {
	                        $s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND collectingcase_id = ? ORDER BY created DESC";
	                        $o_query = $o_main->db->query($s_sql, array($collecting_case['id']));
	                        $invoices_to_check = ($o_query ? $o_query->result_array() : array());
						}
                        foreach($invoices_to_check as $invoice) {
							$data = array("invoice_id"=>$invoice['invoice_nr']);
							$fileText = $api->get_invoice_pdf($data);
							if($fileText != ""){
								$invoices_for_pdf[] = $invoice;
							}
                        }
                    }
                }
				$s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND collectingcase_id = ? ORDER BY created DESC";
                $o_query = $o_main->db->query($s_sql, array($collecting_case['id']));
                $invoice = ($o_query ? $o_query->row_array() : array());

                $s_sql = "SELECT * FROM collecting_cases_claim_lines WHERE content_status < 2 AND collecting_case_id = ? ORDER BY claim_type ASC, created DESC";
                $o_query = $o_main->db->query($s_sql, array($collecting_case['id']));
                $claims = ($o_query ? $o_query->result_array() : array());

				$s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') AND link_id = ? AND creditor_id = ?";
	            $o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
	            $transaction_payments = ($o_query ? $o_query->result_array() : array());

				$total_transaction_payments = $transaction_payments;

				$s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND link_id = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%\_%' ORDER BY created DESC";
				$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
				$transaction_fees = ($o_query ? $o_query->result_array() : array());
	            foreach($transaction_fees as $transaction_fee){
	                if(!$transaction_fee['open']){
	                    $s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') AND link_id = ? AND creditor_id = ?";
	                    $o_query = $o_main->db->query($s_sql, array($transaction_fee['link_id'], $transaction_fee['creditor_id']));
	                    $fee_payments = ($o_query ? $o_query->result_array() : array());

						foreach($fee_payments as $fee_payment) {
							$inOtherTransaction = false;
							foreach($transaction_payments as $transaction_payment) {
								if($transaction_payment['id'] == $fee_payment['id']){
									$inOtherTransaction = true;
								}
							}
							if(!$inOtherTransaction){
								$total_transaction_payments[]=$fee_payment;
							}
						}
	                }
	            }

                $s_sql = "SELECT * FROM collecting_cases_objection WHERE collecting_case_id = ? ORDER BY created DESC";
                $o_query = $o_main->db->query($s_sql, array($collecting_case['id']));
                $objections = ($o_query ? $o_query->result_array() : array());

                $s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE case_id = ? ORDER BY created DESC";
                $o_query = $o_main->db->query($s_sql, array($collecting_case['id']));
                $lastletter = ($o_query ? $o_query->row_array() : array());

                $s_sql = "SELECT * FROM collecting_cases_main_status_basisconfig WHERE id = ? ORDER BY id ASC";
                $o_query = $o_main->db->query($s_sql, array(intval($collecting_case['status'])));
                $collecting_case_status = ($o_query ? $o_query->row_array() : array());

                $s_sql = "SELECT * FROM collecting_cases_sub_status_basisconfig WHERE id = ? ORDER BY id ASC";
                $o_query = $o_main->db->query($s_sql, array(intval($collecting_case['sub_status'])));
                $collecting_case_substatus = ($o_query ? $o_query->row_array() : array());

				$collecting_case['case_type'] = 'reminder';
                $collecting_case['invoice'] = $invoice;
				$collecting_case['invoices_for_pdf'] = $invoices_for_pdf;
                $collecting_case['claims'] = $claims;
	            $collecting_case['transaction_fees'] = $transaction_fees;
	            $collecting_case['transaction_payments'] = $total_transaction_payments;
                $collecting_case['lastletter'] = $lastletter;
                $interestArray = calculate_interest($invoice, $collecting_case);
                $collecting_case['interestArray'] = $interestArray;
                $collecting_case['objections'] = $objections;
                $collecting_case['collecting_case_status'] = $collecting_case_status;
                $collecting_case['collecting_case_sub_status'] = $collecting_case_substatus;
                $collecting_cases_final[] = $collecting_case;
            }

			$s_sql = "SELECT cc.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as creditorName FROM collecting_company_cases cc
			LEFT OUTER JOIN creditor_transactions ci ON ci.collecting_company_case_id = cc.id
			LEFT JOIN creditor cred ON cred.id = cc.creditor_id
			LEFT JOIN customer c ON cred.customer_id = c.id
			WHERE cc.id = ? AND cc.debitor_id = ?
			GROUP BY cc.id
			ORDER BY cc.id";
			$o_query = $o_main->db->query($s_sql, array($key_item['collecting_company_case_id'], $customer['id']));
			$collecting_cases = $o_query ? $o_query->result_array() : array();
			//
			foreach($collecting_cases as $collecting_case) {
                $s_sql = "select * from creditor where id = ?";
                $o_query = $o_main->db->query($s_sql, array($collecting_case['creditor_id']));
                $creditorData = $o_query ? $o_query->row_array() : array();
				$invoices_for_pdf = array();
                if($creditorData){
                    // include_once __DIR__ . '/../../../'.$creditorData['integration_module'].'/internal_api/load.php';
                    $api = new Integration24SevenOffice(array(
                        'ownercompany_id' => 1,
                        'identityId' => $creditorData['entity_id'],
                        'creditorId' => $creditorData['id'],
                        'o_main' => $o_main
                    ));
                    if($api->error == "") {
                        $s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND collecting_company_case_id = ? ORDER BY created DESC";
                        $o_query = $o_main->db->query($s_sql, array($collecting_case['id']));
                        $invoices_to_check = ($o_query ? $o_query->result_array() : array());
                        foreach($invoices_to_check as $invoice) {
                            $data = array("invoice_id"=>$invoice['invoice_nr']);
                            $fileText = $api->get_invoice_pdf($data);
							if($fileText != ""){
								$invoices_for_pdf[] = $invoice;
							}
                        }
                    }
                }

                $s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND collecting_company_case_id = ? ORDER BY created DESC";
                $o_query = $o_main->db->query($s_sql, array($collecting_case['id']));
                $invoice = ($o_query ? $o_query->row_array() : array());

                $s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND collecting_company_case_id = ? ORDER BY created DESC";
                $o_query = $o_main->db->query($s_sql, array($collecting_case['id']));
                $invoices = ($o_query ? $o_query->result_array() : array());

				$s_sql = "SELECT cccl.* FROM collecting_company_cases_claim_lines cccl
				LEFT OUTER JOIN collecting_cases_claim_line_type_basisconfig bconfig ON bconfig.id = cccl.claim_type
				WHERE cccl.content_status < 2 AND cccl.collecting_company_case_id = ? AND IFNULL(bconfig.not_include_in_claim, 0) = 0
				ORDER BY cccl.claim_type ASC, cccl.created DESC";
                $o_query = $o_main->db->query($s_sql, array($collecting_case['id']));
                $claims = ($o_query ? $o_query->result_array() : array());

				$s_sql = "SELECT * FROM cs_mainbook_voucher WHERE case_id = ? ORDER BY created ASC";
				$o_query = $o_main->db->query($s_sql, array($collecting_case['id']));
				$payments = ($o_query ? $o_query->result_array() : array());

				$totalSumPaid = 0;
				$totalSumDue = 0;

				foreach($payments as $payment) {
					$s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND bookaccount_id = 1 ORDER BY id";
					$o_query = $o_main->db->query($s_sql);
					$transactions = ($o_query ? $o_query->result_array() : array());
					foreach($transactions as $transaction) {
						$totalSumPaid += $transaction['amount'];
					}
				}

                // $s_sql = "SELECT * FROM collecting_cases_objection WHERE collecting_company_case_id = ? ORDER BY created DESC";
                // $o_query = $o_main->db->query($s_sql, array($collecting_case['id']));
                // $objections = ($o_query ? $o_query->result_array() : array());

				$s_sql = "SELECT * FROM collecting_company_case_paused WHERE collecting_company_case_id = ? AND (pause_reason = 3 OR pause_reason = 4 OR pause_reason = 5 OR pause_reason = 6) ORDER BY created_date DESC";
				$o_query = $o_main->db->query($s_sql, array($collecting_case['id']));
				$objections = ($o_query ? $o_query->result_array() : array());

                $s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE collecting_company_case_id = ? ORDER BY created DESC";
                $o_query = $o_main->db->query($s_sql, array($collecting_case['id']));
                $lastletter = ($o_query ? $o_query->row_array() : array());

                $s_sql = "SELECT * FROM collecting_cases_main_status_basisconfig WHERE id = ? ORDER BY id ASC";
                $o_query = $o_main->db->query($s_sql, array(intval($collecting_case['status'])));
                $collecting_case_status = ($o_query ? $o_query->row_array() : array());

                $s_sql = "SELECT * FROM collecting_cases_sub_status_basisconfig WHERE id = ? ORDER BY id ASC";
                $o_query = $o_main->db->query($s_sql, array(intval($collecting_case['sub_status'])));
                $collecting_case_substatus = ($o_query ? $o_query->row_array() : array());

				$collecting_case['case_type'] = 'collecting';
                $collecting_case['invoice'] = $invoice;
				$collecting_case['invoices_for_pdf'] = $invoices_for_pdf;
                $collecting_case['claims'] = $claims;
                $collecting_case['lastletter'] = $lastletter;
				$collecting_case['totalSumPaid'] = $totalSumPaid;
                $interestArray = calculate_interest($invoice, $collecting_case);
                $collecting_case['interestArray'] = $interestArray;
                $collecting_case['objections'] = $objections;
                $collecting_case['collecting_case_status'] = $collecting_case_status;
                $collecting_case['collecting_case_sub_status'] = $collecting_case_substatus;

                $collecting_case['invoices'] = $invoices;
                $collecting_cases_final[] = $collecting_case;
            }

            $v_return['customer'] = $customer;
            $v_return['collecting_cases'] = $collecting_cases_final;
			$v_return['collecting_company'] = $key_item['collecting_company_case_id'] > 0 ? 1 : 0;
        } else {
            $v_return['error'] = 'Wrong/expired code';
        }
    }
} else {
    $v_return['error'] = "Too many wrong requests. You have been suspended for 1 hour.";
}
?>
