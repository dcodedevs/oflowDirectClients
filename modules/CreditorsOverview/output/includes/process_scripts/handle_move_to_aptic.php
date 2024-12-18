<?php 

require(__DIR__."/../creditor_functions_v2.php");
require(__DIR__."/../fnc_move_transaction_to_aptic.php");
$nopreview = true;
if($set_preview){
	$nopreview = false;
}
$sql = "SELECT c.* FROM creditor_transactions ct
JOIN creditor c ON c.id = ct.creditor_id
WHERE (ct.tab_status = 8 OR (ct.tab_status = 2 AND ct.next_step_is_oflow = 1)) AND c.activate_aptic = 1  GROUP BY ct.creditor_id";
$o_query = $o_main->db->query($sql);
$creditors = $o_query ? $o_query->result_array() : array();
$page = isset($_POST['page'])?$_POST['page']:1;
$perPage = 50;
$offset = ($page - 1)*$perPage;
$limit = $offset+$perPage;

$transactionCount = 0;
$totalTransactionCount = 0;
foreach($creditors as $creditor) {
	if(intval($creditor['onboarding_incomplete']) == 0){
		$reminder_bookaccount = 8070;
		$interest_bookaccount = 8050;
		if($creditor['reminder_bookaccount'] != ""){
			$reminder_bookaccount = $creditor['reminder_bookaccount'];
		}
		if($creditor['interest_bookaccount'] != ""){
			$interest_bookaccount = $creditor['interest_bookaccount'];
		}
		$creditor_move_to_collecting = $creditor['choose_move_to_collecting_process'];
		$sendToday = false;
		if($creditor['email_sending_day_choice_move'] == 0) {
			$sendToday = true;
		} else {
			$s_sql = "SELECT * FROM creditor_email_sending_days WHERE creditor_id = ? AND IFNULL(type, 0) = 1";
			$o_query = $o_main->db->query($s_sql, array($creditor['id']));
			$creditor_email_sending_days = ($o_query ? $o_query->result_array() : array());
			foreach($creditor_email_sending_days as $creditor_email_sending_day) {
				if($creditor_email_sending_day['day_number'] == date('N')) {
					if($creditor_email_sending_day['checked']){
						$sendToday = true;
					}
				}
			}
		}
		if($sendToday){	
            if($creditor['collecting_agreement_accepted_date'] != "" && $creditor['collecting_agreement_accepted_date'] != "0000-00-00 00:00:00") {			
                    
                                    
                $transactionsToBeMoved = array();
                
                $filters = array();
                $filters['order_field'] = '';
                $filters['order_direction'] = 0;
                $filters['list_filter'] = "next_step_collecting_company_case";
                $filters['sublist_filter'] = "automatic_move";
                $customerListNonProcessed = get_transaction_list($o_main, $creditor['id'], "reminderLevel", $filters, 0, 0, true);
                
                foreach($customerListNonProcessed as $case) {
                    if(!$v_row['next_step_is_oflow']){
                        $mainClaim = 0;
                        $interestAndFees = 0;
                        $s_sql = "SELECT * FROM creditor_transactions WHERE id = ?";
                        $o_query = $o_main->db->query($s_sql, array($case['internalTransactionId']));
                        $invoice = ($o_query ? $o_query->row_array() : array());

                        $s_sql = "SELECT * FROM customer WHERE creditor_customer_id = ? AND creditor_id = ?";
                        $o_query = $o_main->db->query($s_sql, array($invoice['external_customer_id'], $invoice['creditor_id']));
                        $debitorCustomer = $o_query ? $o_query->row_array() : array();
                        
                        $claim_transactions = array();
                        if($invoice['link_id'] > 0){
                            $s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND link_id = ? AND invoice_nr = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%\_%' ORDER BY created DESC";
                            $o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['invoice_nr'], $invoice['creditor_id']));
                            $claim_transactions = ($o_query ? $o_query->result_array() : array());
                        }
                        foreach($claim_transactions as $claim_transaction) {
                            $interestAndFees+=$claim_transaction['amount'];
                        }
                        $restAmount = round($invoice['amount'], 2);
                        $all_transaction_payments = array();
                        if($invoice['link_id'] > 0){
                            $s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') AND link_id = ? AND creditor_id = ?";
                            $o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
                            $all_transaction_payments = ($o_query ? $o_query->result_array() : array());
                        }

                        $transaction_payments = array();
                        foreach($all_transaction_payments as $all_transaction_payment) {
                            if(!in_array($all_transaction_payment['id'], $all_connected_transaction_ids)){
                                $transaction_payments[] = $all_transaction_payment;
                            }
                        }

                        $connected_transactions = array();
                        $all_connected_transaction_ids = array($invoice['id']);
                        if($invoice['link_id'] > 0 && ($creditor['checkbox_1'])) {
                            $s_sql = "SELECT * FROM creditor_transactions ct WHERE ct.link_id = ? AND (ct.open = 1) AND (ct.system_type='InvoiceCustomer' OR ct.system_type = 'CreditnoteCustomer') AND ct.id <> ?";
                            $o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['id']));
                            $connected_transactions_raw = ($o_query ? $o_query->result_array() : array());
                            foreach($connected_transactions_raw as $connected_transaction_raw) {
                                if(strpos($connected_transaction_raw['comment'], '_') === false) {
                                    $connected_transactions[] = $connected_transaction_raw;
                                }
                            }
                            foreach($connected_transactions as $connected_transaction){
                                $all_connected_transaction_ids[] = $connected_transaction['id'];
                            }
                        }

                        if(count($connected_transactions) == 0) {
                            foreach($transaction_payments as $transaction_payment){
                                $restAmount += round($transaction_payment['amount'], 2);
                            }
                        }
                        $restAmount = round($restAmount, 2);
                        $customer_move_to_collecting = $debitorCustomer['choose_move_to_collecting_process'];				
                        $case_move_to_collecting = $case['choose_move_to_collecting_process'];
                        $disabled = false;
                        if($case_move_to_collecting == 0){
                            if($customer_move_to_collecting == 0){
                                $case_move_to_collecting = $creditor_move_to_collecting;
                            } else {
                                $case_move_to_collecting = $customer_move_to_collecting - 1;
                            }
                        } else {
                            $case_move_to_collecting--;
                        }
                        if($case_move_to_collecting == 2) {
                            $notSendInfos[] = array('info'=>$formText_CustomerMarkedNotMoveToCollecting_output);
                            
                            $disabled = true;
                        }
                        $case['debitor'] = $debitorCustomer;
                        if(!$disabled){
                            if($restAmount > $system_settings['minimum_amount_move_to_collecting_company_case'] || $showAll) {
                                if($transactionCount >= $offset && $transactionCount < $limit) {
                                    $transactionsToBeMoved[] = $case;
                                }
                                $transactionCount++;
                            }
                        }
                    }
                }

                if(count($transactionsToBeMoved) > 0) {
                    if(strtotime($creditor['lastImportedDate'])+30*60 < time()) {
                        $s_sql = "SELECT * FROM integration24sevenoffice_session WHERE creditor_id = '".$o_main->db->escape_str($creditor['id'])."' 
                        AND IFNULL(DATE_ADD(IFNULL(failed,'0000-00-00'), INTERVAL 30 MINUTE), CURDATE()) < NOW() ORDER BY created DESC";
                        $o_query = $o_main->db->query($s_sql);
                        $integration24sevenoffice_session = ($o_query ? $o_query->row_array() : array());
                        if($integration24sevenoffice_session){
                            $creditorsToSync[] = $creditor['id'];
                            $creditorsToSyncFull[] = $creditor;
                        }
                    }
                }
                if($nopreview) {
                    $transactionsToBeMoved = array();
                    foreach($customerListNonProcessed as $v_row) {
                        if(in_array($v_row['internalTransactionId'], $_POST['transaction_ids'])) {
                            $transactionsToBeMoved[] = $v_row;
                        }
                    }
                    if(count($transactionsToBeMoved) > 0) {
                        $transactionsMoved = 0;
                        if(!$transaction_errors) {
                            // $fromProcessCases = true;
                            // include(__DIR__."/../import_scripts/import_cases2.php");
                            foreach($transactionsToBeMoved as $transaction) {
                                $sql = "SELECT customer.* FROM customer
                                WHERE customer.creditor_customer_id = ? AND customer.creditor_id = ?";
                                $o_query = $o_main->db->query($sql, array($transaction['external_customer_id'], $creditor['id']));
                                $debitorData = $o_query ? $o_query->row_array() : array();

                                $customer_type_collect_debitor = $debitorData['customer_type_collect'];
                                if($debitorData['customer_type_collect_addition'] > 0){
                                    $customer_type_collect_debitor = $debitorData['customer_type_collect_addition'] - 1;
                                }

                                $processId = $collecting_process['id'];
                                if($transaction['collectingProcessToMoveTo'] > 0){
                                    $processId = $transaction['collectingProcessToMoveTo'];
                                }
                                $v_return = move_transaction_to_aptic($transaction["internalTransactionId"], $username);
                                var_dump($v_return);
                                if($v_return['status']) {
                                    $transactionsMoved++;
                                } else {
                                    foreach($v_return['error'] as $error){
                                        echo $error."<br/>";
                                    }
                                }
                            }
                            echo $transactionsMoved . " ".$formText_TransactionsMoved_output."</br>";
                        } else {
                            echo $formText_ErrorResettingFees;
                        }
                    }
                } else {
                    $return_data[$creditor['id']] = $transactionsToBeMoved;
                }			
            }else {				
				echo $creditor['companyname']." ".$formText_CollectingAgreementNotSigned_output."<br/>";
			}
        }
	}
}
$totalPages = ceil($transactionCount / $perPage);

?>