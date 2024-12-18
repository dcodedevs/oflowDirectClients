<?php
if(!function_exists("calculate_interest")) {
    function calculate_interest($invoice, $caseData){
    	global $o_main;
    	$interestArray = array();
        $interestBearingAmount = 0;
		$dateFrom = "0000-00-00";
        if(isset($invoice['invoice_nr'])){

			$connected_transactions = array();
			$all_connected_transaction_ids = array($invoice['id']);

			$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
			$o_query = $o_main->db->query($s_sql, array($invoice['creditor_id']));
			$creditor = ($o_query ? $o_query->row_array() : array());

			if($invoice['link_id'] > 0 && ($creditor['checkbox_1'])) {
				$s_sql = "SELECT * FROM creditor_transactions ct WHERE ct.link_id = ? AND (ct.open = 1) AND (ct.system_type='InvoiceCustomer' OR ct.system_type = 'CreditnoteCustomer') AND ct.id <> ?";
				$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['id']));
				$connected_transactions_raw = ($o_query ? $o_query->result_array() : array());
				foreach($connected_transactions_raw as $connected_transaction_raw){
					if(strpos($connected_transaction_raw['comment'], '_') === false){
						$connected_transactions[] = $connected_transaction_raw;
					}
				}
				foreach($connected_transactions as $connected_transaction){
					$all_connected_transaction_ids[] = $connected_transaction['id'];
				}
			}

        	$s_sql = "SELECT * FROM (SELECT collecting_cases_payments.id, collecting_cases_payments.date, 1 as collectingcase_payment, 0 as amount
                FROM collecting_cases_payments WHERE collecting_case_id = ?
                UNION
                SELECT creditor_transactions.id, creditor_transactions.date, 0 as collectingcase_payment, creditor_transactions.amount FROM creditor_transactions
                WHERE creditor_transactions.system_type = 'Payment' AND creditor_transactions.link_id = ? AND creditor_transactions.creditor_id = ?)
            as unionTable
            ORDER BY unionTable.date ASC";
        	$o_query = $o_main->db->query($s_sql, array($caseData['id'], $invoice['link_id'], $invoice['creditor_id']));
        	$payments = ($o_query ? $o_query->result_array() : array());

            $s_sql = "SELECT creditor_transactions.*
                FROM creditor_transactions WHERE  open = 1 AND system_type = 'CreditnoteCustomer' AND creditor_transactions.link_id = ? AND creditor_transactions.creditor_id = ?
            ORDER BY date ASC";
        	$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
        	$creditnotes_all = ($o_query ? $o_query->result_array() : array());
			$creditnotes = array();

			foreach($creditnotes_all as $creditnote_all) {
				if(!in_array($creditnote_all['id'], $all_connected_transaction_ids)){
					$creditnotes[] = $creditnote_all;
				}
			}

        	$interestBearingAmount = $invoice['collecting_case_original_claim'];
            foreach($creditnotes as $creditnote) {
                $interestBearingAmount += $creditnote['amount'];
            }

	       $dateFrom = date("Y-m-d", strtotime($invoice['due_date']));
        } else {
            $s_sql = "SELECT collecting_cases_payments.id, collecting_cases_payments.date, 1 as collectingcase_payment, 0 as amount
                FROM collecting_cases_payments WHERE collecting_case_id = ?
            ORDER BY date ASC";
        	$o_query = $o_main->db->query($s_sql, array($caseData['id']));
        	$payments = ($o_query ? $o_query->result_array() : array());
        }

        $s_sql = "SELECT * FROM collecting_cases_claim_lines WHERE content_status < 2 AND collecting_case_id = ? ORDER BY claim_type ASC, created DESC";
        $o_query = $o_main->db->query($s_sql, array($caseData['id']));
        $claims = ($o_query ? $o_query->result_array() : array());
        foreach($claims as $claim) {
            if($claim['claim_type'] == 1){
                $interestBearingAmount += $claim['amount'];
                $dateFrom = $claim['original_due_date'];
            }
        }
		if($dateFrom != "0000-00-00" && $dateFrom != ""){
	    	$lastPayment = array();
	        $payments[] = array('date'=>date("Y-m-d"), 'id' => 0);

			//main invoice
	    	foreach($payments as $payment) {
	            if(strtotime($payment['date']) < strtotime($invoice['due_date'])){
	                $payment['date'] = $invoice['due_date'];
	            }
	    		if($lastPayment){
	                if(strtotime($lastPayment['date']) < strtotime($invoice['due_date'])){
	                    $lastPayment['date'] = $invoice['due_date'];
	                }
	    			$dateFrom = date("Y-m-d", strtotime($lastPayment['date']));
	    		}
	    		$dateTo = date("Y-m-d", strtotime($payment['date']));

	    		$s_sql = "SELECT * FROM collecting_interest WHERE content_status < 2 AND date >= ? AND date < ? ORDER BY date ASC";
	    		$o_query = $o_main->db->query($s_sql, array($dateFrom, $dateTo));
	    		$futureInterests = ($o_query ? $o_query->result_array() : array());

	    		$s_sql = "SELECT * FROM collecting_interest WHERE content_status < 2 AND date < ? ORDER BY date DESC";
	    		$o_query = $o_main->db->query($s_sql, array($dateFrom));
	    		$interest = ($o_query ? $o_query->row_array() : array());
	            if(!$interest) {
	                $s_sql = "SELECT * FROM collecting_interest WHERE content_status < 2 ORDER BY date ASC";
	        		$o_query = $o_main->db->query($s_sql);
	        		$interest = ($o_query ? $o_query->row_array() : array());
	            }
	    		$interests = array($interest);
	    		foreach($futureInterests as $singleInterest) {
	    			$interests[] = $singleInterest;
	    		}
	    		if(count($interests) > 0){
	    			foreach($interests as $index => $interest) {
	    				if(strtotime($dateFrom) < strtotime($dateTo)) {
	    					$nextInterest = $interests[$index+1];
	    					$swappedDates = false;
	    					if($nextInterest){
	    						if(strtotime($dateTo) > strtotime($nextInterest['date'])) {
	    							$initialDateTo = $dateTo;
	    							$dateTo = $nextInterest['date'];
	    							$swappedDates = true;
	    						}
	    					}
	    					$earlier = new DateTime($dateFrom);
	    					$later = new DateTime($dateTo);
	    					$diff = $later->diff($earlier)->format("%a");
	    					$interestAmount = number_format($interestBearingAmount*$interest['rate']/100/365*$diff, 2, ".", "");

	    					$interestInPeriod = number_format($interestAmount, 2, ",", "");

	    					if(!isset($interestArray[$payment['id']."_".$interest['id']]['dateFrom'])) {
	    						$interestArray[$payment['id']."_".$interest['id']]['dateFrom'] = $dateFrom;
	    					}
	    					$interestArray[$payment['id']."_".$interest['id']]['dateTo'] = $dateTo;
	    					$interestArray[$payment['id']."_".$interest['id']]['amount'] += $interestAmount;
	    					$interestArray[$payment['id']."_".$interest['id']]['rate'] = $interest['rate'];
	    					$interestArray[$payment['id']."_".$interest['id']]['amount_from'] = $interestBearingAmount;


	    					if($swappedDates) {
	    						$dateFrom = $dateTo;
	    						$dateTo = $initialDateTo;
	    					}
	    				}
	    			}
	    		}

	            if($payment['collectingcase_payment']){
	                $coveredInteresBearingAmount = 0;
	        		$s_sql = "SELECT * FROM collecting_cases_payment_coverlines WHERE collecting_cases_payment_id = ?";
	        		$o_query = $o_main->db->query($s_sql, array($payment['id']));
	        		$paymentCoverlines = $o_query ? $o_query->result_array() : array();
	        		foreach($paymentCoverlines as $paymentCoverline){
	        			if($paymentCoverline['collecting_claim_line_type'] == 1) {
	        				$coveredInteresBearingAmount += $paymentCoverline['amount'];
	        			}
	        		}
	        		$interestBearingAmount -= $coveredInteresBearingAmount;
	            } else {
	                $interestBearingAmount += $payment['amount'];
	            }

	    		$lastPayment = $payment;
	    	}

			foreach($connected_transactions as $connected_transaction) {
				$interestBearingAmount = $connected_transaction['amount'];
				$dateFrom = date("Y-m-d", strtotime($connected_transaction['due_date']));
				$lastPayment = array();
		        $payments[] = array('date'=>date("Y-m-d"), 'id' => 0);

				//main invoice
		    	foreach($payments as $payment) {
		            if(strtotime($payment['date']) < strtotime($connected_transaction['due_date'])){
		                $payment['date'] = $connected_transaction['due_date'];
		            }
		    		if($lastPayment){
		                if(strtotime($lastPayment['date']) < strtotime($connected_transaction['due_date'])){
		                    $lastPayment['date'] = $connected_transaction['due_date'];
		                }
		    			$dateFrom = date("Y-m-d", strtotime($lastPayment['date']));
		    		}
		    		$dateTo = date("Y-m-d", strtotime($payment['date']));

		    		$s_sql = "SELECT * FROM collecting_interest WHERE content_status < 2 AND date >= ? AND date < ? ORDER BY date ASC";
		    		$o_query = $o_main->db->query($s_sql, array($dateFrom, $dateTo));
		    		$futureInterests = ($o_query ? $o_query->result_array() : array());

		    		$s_sql = "SELECT * FROM collecting_interest WHERE content_status < 2 AND date < ? ORDER BY date DESC";
		    		$o_query = $o_main->db->query($s_sql, array($dateFrom));
		    		$interest = ($o_query ? $o_query->row_array() : array());
		            if(!$interest) {
		                $s_sql = "SELECT * FROM collecting_interest WHERE content_status < 2 ORDER BY date ASC";
		        		$o_query = $o_main->db->query($s_sql);
		        		$interest = ($o_query ? $o_query->row_array() : array());
		            }
		    		$interests = array($interest);
		    		foreach($futureInterests as $singleInterest) {
		    			$interests[] = $singleInterest;
		    		}
		    		if(count($interests) > 0){
		    			foreach($interests as $index => $interest) {
		    				if(strtotime($dateFrom) < strtotime($dateTo)) {
		    					$nextInterest = $interests[$index+1];
		    					$swappedDates = false;
		    					if($nextInterest){
		    						if(strtotime($dateTo) > strtotime($nextInterest['date'])) {
		    							$initialDateTo = $dateTo;
		    							$dateTo = $nextInterest['date'];
		    							$swappedDates = true;
		    						}
		    					}
		    					$earlier = new DateTime($dateFrom);
		    					$later = new DateTime($dateTo);
		    					$diff = $later->diff($earlier)->format("%a");
		    					$interestAmount = number_format($interestBearingAmount*$interest['rate']/100/365*$diff, 2, ".", "");

		    					$interestInPeriod = number_format($interestAmount, 2, ",", "");

		    					if(!isset($interestArray[$payment['id']."_".$interest['id']."_".$connected_transaction['id']]['dateFrom'])) {
		    						$interestArray[$payment['id']."_".$interest['id']."_".$connected_transaction['id']]['dateFrom'] = $dateFrom;
		    					}
		    					$interestArray[$payment['id']."_".$interest['id']."_".$connected_transaction['id']]['dateTo'] = $dateTo;
		    					$interestArray[$payment['id']."_".$interest['id']."_".$connected_transaction['id']]['amount'] += $interestAmount;
		    					$interestArray[$payment['id']."_".$interest['id']."_".$connected_transaction['id']]['rate'] = $interest['rate'];
		    					$interestArray[$payment['id']."_".$interest['id']."_".$connected_transaction['id']]['amount_from'] = $interestBearingAmount;


		    					if($swappedDates) {
		    						$dateFrom = $dateTo;
		    						$dateTo = $initialDateTo;
		    					}
		    				}
		    			}
		    		}

		            if($payment['collectingcase_payment']){
		                $coveredInteresBearingAmount = 0;
		        		$s_sql = "SELECT * FROM collecting_cases_payment_coverlines WHERE collecting_cases_payment_id = ?";
		        		$o_query = $o_main->db->query($s_sql, array($payment['id']));
		        		$paymentCoverlines = $o_query ? $o_query->result_array() : array();
		        		foreach($paymentCoverlines as $paymentCoverline){
		        			if($paymentCoverline['collecting_claim_line_type'] == 1) {
		        				$coveredInteresBearingAmount += $paymentCoverline['amount'];
		        			}
		        		}
		        		$interestBearingAmount -= $coveredInteresBearingAmount;
		            } else {
		                $interestBearingAmount += $payment['amount'];
		            }

		    		$lastPayment = $payment;
		    	}
			}
		}
    	return $interestArray;
    }
}
?>
