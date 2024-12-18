<?php
if(!function_exists("calculate_interest")) {
    function calculate_interest($invoice, $caseData){
    	global $o_main;
    	global $variables;
    	$interestArray = array();
        $interestBearingAmount = 0;
		$dateFrom = "0000-00-00";
        $s_sql = "SELECT collecting_cases_payments.id, collecting_cases_payments.date, 1 as collectingcase_payment, collecting_cases_payments.amount
            FROM collecting_cases_payments WHERE collecting_case_id = ?
        ORDER BY date ASC";
    	$o_query = $o_main->db->query($s_sql, array($caseData['id']));
    	$payments = ($o_query ? $o_query->result_array() : array());

        $s_sql = "SELECT collecting_company_cases_claim_lines.id, collecting_company_cases_claim_lines.date, 0 as collectingcase_payment, collecting_company_cases_claim_lines.amount
            FROM collecting_company_cases_claim_lines WHERE collecting_company_case_id = ? AND claim_type = 15 ORDER BY date ASC";
    	$o_query = $o_main->db->query($s_sql, array($caseData['id']));
    	$claimlines = ($o_query ? $o_query->result_array() : array());
		foreach($claimlines as $claimline){
			$payments[] = array('date'=>$claimline['date'], 'id' => "direct|".$claimline['id'], 'amount'=> $claimline['amount']);
		}
		$payments_grouped_by_date = array();
		foreach($payments as $payment) {
			$amount = $payments_grouped_by_date[$payment['date']]['amount'];
			$amount += $payment['amount'];
			$payments_grouped_by_date[$payment['date']] =  array('date'=>$payment['date'], 'id' => $payment['id'], 'amount'=> $amount, 'collectingcase_payment'=> $payment['collectingcase_payment']);
		}
		$payments_grouped_by_date[date("Y-m-d")] = array('date'=>date("Y-m-d"), 'id' => 0);

		

        $s_sql = "SELECT * FROM collecting_company_cases_claim_lines WHERE content_status < 2 AND collecting_company_case_id = ? AND claim_type = 1 ORDER BY original_due_date ASC";
        $o_query = $o_main->db->query($s_sql, array($caseData['id']));
        $claims = ($o_query ? $o_query->result_array() : array());

		$s_sql = "SELECT * FROM collecting_company_cases_claim_lines WHERE content_status < 2 AND collecting_company_case_id = ? AND claim_type = 16";
        $o_query = $o_main->db->query($s_sql, array($caseData['id']));
        $credited_claims = ($o_query ? $o_query->result_array() : array());
		$total_credited_amount = 0;
		foreach($credited_claims as $credited_claim) {
			$total_credited_amount+= $credited_claim['amount'];
		}
		$updatedPayments = array();
        foreach($claims as $claim) {
            $interestBearingAmount = $claim['amount'];
			if(abs($total_credited_amount) > $interestBearingAmount) {
				$total_credited_amount += $interestBearingAmount;
				$interestBearingAmount = 0;
			} else {
				$interestBearingAmount += $total_credited_amount;
			}
            $dateFrom = $claim['original_due_date'];

			if($dateFrom != "0000-00-00" && $dateFrom != "") {
		    	$lastPayment = array();
		    	foreach($payments_grouped_by_date as $payment) {
					if($payment['amount'] < 0 || $payment['collectingcase_payment'] || $payment['id'] == 0) {
						if($interestBearingAmount > 0) {
				            if(strtotime($payment['date']) < strtotime($claim['original_due_date'])){
				                $payment['date'] = $claim['original_due_date'];
				            }
				    		if($lastPayment){
				                if(strtotime($lastPayment['date']) < strtotime($claim['original_due_date'])){
				                    $lastPayment['date'] = $claim['original_due_date'];
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

				    					if(!isset($interestArray[$payment['id']."_".$interest['id']."_".$claim['id']]['dateFrom'])) {
				    						$interestArray[$payment['id']."_".$interest['id']."_".$claim['id']]['dateFrom'] = $dateFrom;
				    					}
				    					$interestArray[$payment['id']."_".$interest['id']."_".$claim['id']]['dateTo'] = $dateTo;
				    					$interestArray[$payment['id']."_".$interest['id']."_".$claim['id']]['amount'] += $interestAmount;
				    					$interestArray[$payment['id']."_".$interest['id']."_".$claim['id']]['rate'] = $interest['rate'];
				    					$interestArray[$payment['id']."_".$interest['id']."_".$claim['id']]['amount_from'] = $interestBearingAmount;


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
								$payment['amount'] = $coveredInteresBearingAmount * (-1);
								$payment['collectingcase_payment'] = 0;
				            }
							if($payment['id'] !== 0){
								$interestBearingAmount += $payment['amount'];
								if($interestBearingAmount < 0) {
									$payment['amount'] = $interestBearingAmount;
								} else {
									$payment['amount'] = 0;
								}
							}
				    		$lastPayment = $payment;
						}
						$updatedPayments[$payment['id']] = $payment;
					}
		    	}
				$payments_grouped_by_date = $updatedPayments;
			}
        }

    	return $interestArray;
    }
}
?>
