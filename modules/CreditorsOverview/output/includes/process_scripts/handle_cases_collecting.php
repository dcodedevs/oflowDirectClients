<?php
include_once(__DIR__."/../../../../CollectingCompanyCases/output/includes/fnc_calculate_interest.php");
include_once(__DIR__."/../../../../CollectingCompanyCases/output/includes/fnc_generate_pdf.php");
include_once(__DIR__."/../../../../CollectingCompanyCases/output/includes/fnc_process_continuing_step.php");
//$creditorId
$successfullyCreatedLetters = 0;
$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
$o_query = $o_main->db->query($s_sql, array($creditorId));
$creditor = ($o_query ? $o_query->row_array() : array());
if($creditor){
	
	$s_sql = "SELECT * FROM customer WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($creditor['customer_id']));
	$creditorCustomer = $o_query ? $o_query->row_array() : array();

	$s_sql = "SELECT * FROM collecting_system_settings ORDER BY id ASC";
	$o_query = $o_main->db->query($s_sql);
	$system_settings = ($o_query ? $o_query->row_array() : array());
	$collecting_late_fee_item = array();
	if($creditor['default_collecting_late_fee'] > 0){
		$s_sql = "SELECT * FROM debtcollectionlatefee_main WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($creditor['default_collecting_late_fee']));
		$collecting_late_fee_item = ($o_query ? $o_query->row_array() : array());
	} else {
		$s_sql = "SELECT * FROM debtcollectionlatefee_main WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($system_settings['default_collecting_late_fee']));
		$collecting_late_fee_item = ($o_query ? $o_query->row_array() : array());
	}
	if($collecting_late_fee_item) {
		// $objection_after_days = intval($system_settings['default_days_after_closed_objection_to_process']);
		// if($creditor['days_after_closed_objection_to_process'] != NULL){
		//     $objection_after_days = $creditor['days_after_closed_objection_to_process'];
		// }
		$onhold_sql = " AND (onhold_by_creditor is null OR onhold_by_creditor = 0) ";
		if($skip_to_step > 0) {
		    $onhold_sql = "";
		}
		$collectingcase_status_sql = " AND IFNULL(collecting_company_cases.case_closed_date, '0000-00-00') = '0000-00-00' ";
		$cases = array();
		if(isset($collecting_case_id)) {
			if(!is_array($collecting_case_id)) {
				$collecting_case_id = array($collecting_case_id);
			}
		    $s_sql = "SELECT * FROM collecting_company_cases WHERE creditor_id = ? ".$collectingcase_status_sql.$onhold_sql." AND collecting_company_cases.id IN (".implode(",", $collecting_case_id).")";
			$o_query = $o_main->db->query($s_sql, array($creditor['id']));
		    $cases = $o_query ? $o_query->result_array() : array();
		}
		if($skip_to_step > 0){
		    $s_sql = "SELECT * FROM collecting_cases_collecting_process_steps WHERE id = ?";
		    $o_query = $o_main->db->query($s_sql, array($skip_to_step));
		    $skip_to_step_item = ($o_query ? $o_query->row_array() : array());
		}
		foreach($cases as $case)
		{
			if(intval($case['content_status']) == 0) {
				if(intval($case['collectingcase_progress_type']) == 0) {
				    if($case['create_letter'] == 0) {
				        $s_sql = "SELECT * FROM customer WHERE id = ?";
				        $o_query = $o_main->db->query($s_sql, array($case['debitor_id']));
				        $debitorCustomer = $o_query ? $o_query->row_array() : array();

						$is_company = false;
						 if($debitorCustomer['customer_type_for_collecting_cases'] == 0) {
							$customer_type_collect_debitor = $debitorCustomer['customer_type_collect'];
							if($debitorCustomer['customer_type_collect_addition'] > 0) {
								$customer_type_collect_debitor = $debitorCustomer['customer_type_collect_addition'] - 1;
							}
							if($customer_type_collect_debitor == 0) {
								$is_company = true;
							} else if($customer_type_collect_debitor == 1) {
							}

						 } else if($debitorCustomer['customer_type_for_collecting_cases'] == 1) {
							 $is_company = true;
						 } else if($debitorCustomer['customer_type_for_collecting_cases'] == 2) {
						}
						if(!$is_company || ($is_company && $debitorCustomer['confirmed_as_company'] != "0000-00-00" && $debitorCustomer['confirmed_as_company'] != "")){

							if($debitorCustomer['extraName'] != "" && $debitorCustomer['extraPostalNumber'] != "" && $debitorCustomer['extraCity'] != ""){
						        $s_sql = "SELECT * FROM collecting_cases_payment_plan WHERE collecting_case_id = ? AND (status = 0 OR status is null) ORDER BY sortnr ASC";
						        $o_query = $o_main->db->query($s_sql, array($case['id']));
						        $active_payment_plan = ($o_query ? $o_query->row_array() : array());
						        if(!$active_payment_plan) {
						            if($case['onhold_by_creditor'] != 1 || $skip_to_step > 0){
					                    $s_sql = "SELECT * FROM collecting_company_case_paused WHERE collecting_company_case_id = ? AND (closed_date = '0000-00-00 00:00:00' OR closed_date is null) ORDER BY created DESC";
					                    $o_query = $o_main->db->query($s_sql, array($case['id']));
					                    $activeObjections = ($o_query ? $o_query->result_array() : array());
					                    if(count($activeObjections) == 0) {
											if($case['continuing_process_step_id'] > 0){	
												$result = process_continuing_step($case['id']);
												if($result['success']){
													$successfullyCreatedLetters++;
												} else {
													echo $formText_ErrorProcessingContinuing_output;
												}
											} else {
												$s_sql = "SELECT * FROM collecting_cases_collecting_process WHERE id = ? ORDER BY sortnr ASC";
												$o_query = $o_main->db->query($s_sql, array($case['collecting_process_id']));
												$process = ($o_query ? $o_query->row_array() : array());

												$customer_type_collect_debitor = $debitorCustomer['customer_type_collect'];
												if($debitorCustomer['customer_type_collect_addition'] > 0){
													$customer_type_collect_debitor = $debitorCustomer['customer_type_collect_addition'] - 1;
												}
												if($debitorCustomer['customer_type_for_collecting_cases'] > 0){
													$customer_type_collect_debitor = $debitorCustomer['customer_type_for_collecting_cases'] - 1;
												}							
												// if($customer_type_collect_debitor == 0){
												// 	if($debitorCustomer['organization_type'] == "ENK") {
												// 		$customer_type_collect_debitor = 1;
												// 	}
												// }
												if(!$process) {
													if($customer_type_collect_debitor == 0){
														$s_sql = "SELECT * FROM collecting_cases_process WHERE id = ? ORDER BY sortnr ASC";
														$o_query = $o_main->db->query($s_sql, array($creditor['reminder_process_for_company']));
														$process = ($o_query ? $o_query->row_array() : array());
													} else if($customer_type_collect_debitor == 1){
														$s_sql = "SELECT * FROM collecting_cases_process WHERE id = ? ORDER BY sortnr ASC";
														$o_query = $o_main->db->query($s_sql, array($creditor['reminder_process_for_person']));
														$process = ($o_query ? $o_query->row_array() : array());
													}
												}
												$s_sql = "SELECT * FROM collecting_cases_collecting_process_steps WHERE collecting_cases_collecting_process_steps.collecting_cases_collecting_process_id = ? ORDER BY sortnr ASC";
												$o_query = $o_main->db->query($s_sql, array($process['id']));
												$steps = ($o_query ? $o_query->result_array() : array());
												$caseOnLastStep = false;
												if($case['collecting_cases_process_step_id'] == intval($steps[count($steps)-1]['id']) && intval($steps[count($steps)-1]['id']) > 0){
													$caseOnLastStep = true;
												}
												foreach($steps as $step) {
													if($skip_to_step_item){
														if($step['sortnr'] < $skip_to_step_item['sortnr']){
															continue;
														}
													}
													//renew case if step updated
													$s_sql = "SELECT * FROM collecting_company_cases WHERE id = ?";
													$o_query = $o_main->db->query($s_sql, array($case['id']));
													$case = $o_query ? $o_query->row_array() : array();

													$s_sql = "SELECT * FROM collecting_cases_collecting_process_steps WHERE collecting_cases_collecting_process_id = '".$o_main->db->escape_str($step['collecting_cases_collecting_process_id'])."' AND sortnr < '".$o_main->db->escape_str($step['sortnr'])."' ORDER BY sortnr DESC";
													$o_query = $o_main->db->query($s_sql);
													$previous_step = $o_query ? $o_query->row_array() : array();

													$s_sql = "SELECT * FROM collecting_cases_collecting_process_steps WHERE collecting_cases_collecting_process_id = '".$o_main->db->escape_str($step['collecting_cases_collecting_process_id'])."' AND sortnr > '".$o_main->db->escape_str($step['sortnr'])."' ORDER BY sortnr ASC";
													$o_query = $o_main->db->query($s_sql);
													$next_step = $o_query ? $o_query->row_array() : array();
													$forced = false;
													// if(isset($collecting_level_to_move_from)) {
													//     if(intval($previous_step['caselevel']) == 0){
													//         if($previous_step['collectinglevel'] == $collecting_level_to_move_from) {
													//             $forced = true;
													//         }
													//     }
													// }

													//collecting caselevels can't be forced to be moved to next level
													// if(intval($previous_step['caselevel']) == 1) {
													//     $forced = false;
													// }
													$log.= $forced." ";

													if(isset($case_step_to_move_to)){
														if($case_step_to_move_to == $step['id']) {
															$forced = true;
														}
													}
													$log.=$case_step_to_move_to." ";


													if($case['collecting_cases_process_step_id'] == $previous_step['id'] || intval($case['collecting_cases_process_step_id']) == 0 || $forced || $caseOnLastStep) {

														$s_sql = "SELECT * FROM collecting_company_case_paused WHERE collecting_company_case_id = ? AND (closed_date <> '0000-00-00 00:00:00' AND closed_date is not null) ORDER BY closed_date DESC";
														$o_query = $o_main->db->query($s_sql, array($case['id']));
														$closedObjection = ($o_query ? $o_query->row_array() : array());
														$objectionTime = 0;
														if($closedObjection && $closedObjection['pause_reason'] > 0) {
															// $objectionTime = strtotime("+".($objection_after_days)." days", strtotime($closedObjection['closed_date']));
														}

														$dueDateTime = strtotime($case['due_date']);
														$correctDueDateTime = strtotime("+".($step['days_after_due_date'])." days", $dueDateTime);

														// if($objectionTime > $correctDueDateTime){
														//     $correctDueDateTime = $objectionTime;
														// }
														$dateFrom = "0000-00-00";
														$s_sql = "SELECT collecting_company_cases_claim_lines.*, collecting_cases_claim_line_type_basisconfig.make_to_appear_in_consider_tab FROM collecting_company_cases_claim_lines
														JOIN collecting_cases_claim_line_type_basisconfig ON collecting_cases_claim_line_type_basisconfig.id = collecting_company_cases_claim_lines.claim_type
														WHERE collecting_company_cases_claim_lines.content_status < 2
														AND collecting_company_cases_claim_lines.collecting_company_case_id = ? AND IFNULL(collecting_cases_claim_line_type_basisconfig.not_include_in_claim, 0) = 0
														ORDER BY collecting_company_cases_claim_lines.claim_type ASC, collecting_company_cases_claim_lines.created DESC";
														$o_query = $o_main->db->query($s_sql, array($case['id']));
														$claims = ($o_query ? $o_query->result_array() : array());
														$hasConsideration = false;
														foreach($claims as $claim) {
															if($claim['claim_type'] == 1){
																$interestBearingAmount += $claim['amount'];
																$dateFrom = $claim['original_due_date'];
															}
															if($claim['make_to_appear_in_consider_tab']) {
																$hasConsideration = true;
															}
														}
														if(!$hasConsideration){
															if($dateFrom != "0000-00-00" && $dateFrom != ""){
																if($correctDueDateTime < time() || $forced || intval($case['collecting_cases_process_step_id']) == 0)
																{
																	if($caseOnLastStep){
																		if($case['collecting_case_manual_process_date'] == '0000-00-00' || $case['collecting_case_manual_process_date'] == ""){
																			$s_sql = "UPDATE collecting_company_cases SET collecting_case_manual_process_date = NOW() WHERE id = ?";
																			$o_query = $o_main->db->query($s_sql, array($case['id']));
																			echo $formText_CaseMovedToManualProcess_output. " ".$case["id"]."<br/>";
																		}
																	} else {
																		if(intval($case['collecting_cases_process_step_id']) == 0){
																			if($step['warning_level']){
																				$s_sql = "UPDATE collecting_company_cases SET warning_case_started_date = NOW() WHERE id = ?";
																				$o_query = $o_main->db->query($s_sql, array($case['id']));
																			} else {
																				if($case['collecting_case_created_date'] == '0000-00-00' || $case['collecting_case_created_date'] == ""){
																					$s_sql = "UPDATE collecting_company_cases SET collecting_case_created_date = NOW() WHERE id = ?";
																					$o_query = $o_main->db->query($s_sql, array($case['id']));
																				}
																				$s_sql = "UPDATE collecting_company_cases SET collecting_case_autoprocess_date = NOW() WHERE id = ?";
																				$o_query = $o_main->db->query($s_sql, array($case['id']));
																			}
																		} else {
																			if(!$step['warning_level']){
																				if($case['collecting_case_created_date'] == '0000-00-00' || $case['collecting_case_created_date'] == ""){
																					$s_sql = "UPDATE collecting_company_cases SET collecting_case_created_date = NOW() WHERE id = ?";
																					$o_query = $o_main->db->query($s_sql, array($case['id']));
																				}
																				if($case['collecting_case_autoprocess_date'] == '0000-00-00' || $case['collecting_case_autoprocess_date'] == ""){
																					$s_sql = "UPDATE collecting_company_cases SET collecting_case_autoprocess_date = NOW() WHERE id = ?";
																					$o_query = $o_main->db->query($s_sql, array($case['id']));
																				}
																			}
																		}

																		$s_sql = "UPDATE collecting_company_cases SET updated = NOW(), create_letter = 1 WHERE id = ?";
																		$o_query = $o_main->db->query($s_sql, array($case['id']));


																		// $s_sql = "SELECT * FROM debtcollectionlatefee WHERE id = ?";
																		// $o_query = $o_main->db->query($s_sql, array($step['claim_type_2_article']));
																		// $articleForType2 = $o_query ? $o_query->row_array() : array();

																		$debtCollectionTableName = "";
																		$claimTypeForType3 = "";

																		if($debitorCustomer) {
																			$customer_type_collect_creditor = $debitorCustomer['customer_type_collect'];
																			if($debitorCustomer['customer_type_collect_addition'] > 0){
																				$customer_type_collect_creditor = $debitorCustomer['customer_type_collect_addition'] - 1;
																			}
																			if($debitorCustomer['customer_type_for_collecting_cases'] > 0){
																				$customer_type_collect_creditor = $debitorCustomer['customer_type_for_collecting_cases'] - 1;
																			}
																			if(intval($customer_type_collect_creditor) == 0) {
																				if($creditor['vat_deduction']){
																					$debtCollectionTableName = "debtcollectionfeecompanycreditorwithvatdeduct";
																					$claimTypeForType3 = '6';
																				} else {
																					$debtCollectionTableName = "debtcollectionfeecompanycreditorwithoutvatdeduct";
																					$claimTypeForType3 = '7';
																				}
																			} else if($customer_type_collect_creditor == 1) {
																				if($creditor['vat_deduction']){
																					$debtCollectionTableName = "debtcollectionfeepersoncreditorwithvatdeduct";
																					$claimTypeForType3 = '4';
																				} else {
																					$debtCollectionTableName = "debtcollectionfeepersoncreditorwithoutvatdeduct";
																					$claimTypeForType3 = '5';
																				}
																			}
																		}

																		$s_sql = "SELECT * FROM collecting_company_cases_claim_lines WHERE collecting_company_case_id = '".$o_main->db->escape_str($case['id'])."' AND claim_type = 1 ORDER BY created DESC";
																		$o_query = $o_main->db->query($s_sql, array($case['id']));
																		$main_claimlines = ($o_query ? $o_query->result_array() : array());
																		$baseAmount = 0;
																		foreach($main_claimlines as $main_claimline) {
																			$baseAmount += $main_claimline['amount'];
																		}

																		$articleForType3 = array();
																		if($debtCollectionTableName != "") {
																			if($step['claim_type_3_article'] > 0) {
																				$s_sql = "SELECT * FROM ".$debtCollectionTableName." WHERE amountFrom < ? ORDER BY amountFrom DESC";
																				$o_query = $o_main->db->query($s_sql, array($baseAmount));
																				$articleForType3 = $o_query ? $o_query->row_array() : array();
																			}
																		}
																		$s_sql = "DELETE FROM collecting_company_cases_claim_lines WHERE claim_type = 2 AND collecting_company_case_id = ?";
																		$o_query = $o_main->db->query($s_sql, array($case['id']));
																		if(!$case['doNotAddLateFee']) {
																			if(intval($step['claim_type_2_article']) > 0) {
																				if($customer_type_collect_debitor == 0) {
																					$s_sql = "SELECT * FROM debtcollectionlatefee_amount WHERE mainclaim_amount < ? AND debtcollectionlatefee_main_id = ? AND IFNULL(type, 0) = 0 ORDER BY mainclaim_amount DESC";
																					$o_query = $o_main->db->query($s_sql, array($baseAmount, $collecting_late_fee_item['id']));
																					$articleForType2 = $o_query ? $o_query->row_array() : array();
																				} else {
																					$s_sql = "SELECT * FROM debtcollectionlatefee_amount WHERE mainclaim_amount < ? AND debtcollectionlatefee_main_id = ? AND IFNULL(type, 0) = 1 ORDER BY mainclaim_amount DESC";
																					$o_query = $o_main->db->query($s_sql, array($baseAmount, $collecting_late_fee_item['id']));
																					$articleForType2 = $o_query ? $o_query->row_array() : array();
																				}								
																				$reduced_by = 0;
																				if($creditor['warning_level_fee_for_person_reduced_by_one']){
																					if($customer_type_collect_debitor == 1) {
																						$reduced_by = 1;																					
																						if(intval($step['claim_type_2_article']) == 1) {
																							$articleForType2 = array();
																						}
																					}
																				}
																				// $s_sql = "SELECT * FROM debtcollectionlatefee WHERE id = ?";
																				// $o_query = $o_main->db->query($s_sql, array($step['claim_type_2_article']));
																				// $articleForType2 = $o_query ? $o_query->row_array() : array();

																				if($articleForType2) {
																					$fee_amount = $articleForType2['amount'];
																					$s_sql = "SELECT * FROM collecting_cases_pdftext WHERE collecting_cases_pdftext.id = ?";
																					$o_query = $o_main->db->query($s_sql, array($step['collecting_cases_pdftext_id']));
																					$pdfText = ($o_query ? $o_query->row_array() : array());

																					$claimline_text = $pdfText['claimline_text'];
																					if($debitorCustomer['extra_language'] == 1){
																						$claimline_text = $pdfText['claimline_text_english'];
																					}
																					if(intval($step['claim_type_2_article']) == 2) {
																						if($reduced_by == 0){
																							$fee_amount = $fee_amount*(2);
																							$claimline_text .= " x2";
																						}
																					}
																					// $fee_level = 2;
																					// if($creditor['collecting_case_reminder_fee_level'] > 0){
																					// 	$fee_level = $creditor['collecting_case_reminder_fee_level'];
																					// }
																					//
																					// if($customer_type_collect_debitor == 0) {
																					// 	switch($fee_level) {
																					// 		case 1:
																					// 			$fee_amount = $articleForType2['company_fee_amount_level_1'];
																					// 		break;
																					// 		case 2:
																					// 			$fee_amount = $articleForType2['company_fee_amount_level_2'];
																					// 		break;
																					// 		case 3:
																					// 			$fee_amount = $articleForType2['company_fee_amount_level_3'];
																					// 		break;
																					// 		case 4:
																					// 			$fee_amount = $articleForType2['company_fee_amount_level_4'];
																					// 		break;
																					// 	}
																					// } else {
																					// 	switch($fee_level) {
																					// 		case 1:
																					// 			$fee_amount = $articleForType2['person_fee_amount_level_1'];
																					// 		break;
																					// 		case 2:
																					// 			$fee_amount = $articleForType2['person_fee_amount_level_2'];
																					// 		break;
																					// 		case 3:
																					// 			$fee_amount = $articleForType2['person_fee_amount_level_3'];
																					// 		break;
																					// 		case 4:
																					// 			$fee_amount = $articleForType2['person_fee_amount_level_4'];
																					// 		break;
																					// 	}
																					// }

																					$s_sql = "INSERT INTO collecting_company_cases_claim_lines SET created=NOW(), createdBy='claim line process', name=?, amount = ?, collecting_company_case_id= ?, claim_type = 2";
																					$o_query = $o_main->db->query($s_sql, array($claimline_text, $fee_amount, $case['id']));

																				}
																			}
																		}
																		$s_sql = "DELETE FROM collecting_company_cases_claim_lines WHERE (claim_type = 4 || claim_type = 5 || claim_type = 6 || claim_type = 7) AND collecting_company_case_id = ?";
																		$o_query = $o_main->db->query($s_sql, array($case['id']));
																		if(!$case['doNotAddDebtCollectionFee']) {
																			if(intval($step['claim_type_3_article']) > 0) {
																				if($articleForType3) {
																					if($step['claim_type_3_article'] == 1) {
																						$amount = $articleForType3['lightFee'];
																					} else {
																						$amount = $articleForType3['heavyFee'];
																					}
																					$s_sql = "INSERT INTO collecting_company_cases_claim_lines SET  created=NOW(), createdBy='claim line process', name=?, amount = ?,collecting_company_case_id =?, claim_type = ?";
																					$o_query = $o_main->db->query($s_sql, array($articleForType3['articleText'], $amount, $case['id'], $claimTypeForType3));
																				}
																			}
																		}
																		$noInterestError = false;
																		if($reduced_by != 1) {
																			$s_sql = "DELETE FROM collecting_cases_interest_calculation WHERE collecting_company_case_id = ? ";
																			$o_query = $o_main->db->query($s_sql, array($case['id']));

																			$currentClaimInterest = 0;
																			$interestArray = calculate_interest(array(), $case);
																			$totalInterest = 0;
																			foreach($interestArray as $interest_index => $interest) {
																				$interest_index_array = explode("_", $interest_index);
																				$claimline_id = intval($interest_index_array[2]);

																				$interestRate = $interest['rate'];
																				$interestAmount = $interest['amount'];
																				$interestFrom = date("Y-m-d", strtotime($interest['dateFrom']));
																				$interestTo = date("Y-m-d", strtotime($interest['dateTo']));

																				$s_sql = "INSERT INTO collecting_cases_interest_calculation SET created = NOW(), date_from = '".$o_main->db->escape_str($interestFrom)."',
																				date_to = '".$o_main->db->escape_str($interestTo)."', amount = '".$o_main->db->escape_str($interestAmount)."', rate = '".$o_main->db->escape_str($interestRate)."', collecting_company_case_id = '".$o_main->db->escape_str($case['id'])."',
																				collecting_company_cases_claim_line_id = '".$o_main->db->escape_str($claimline_id)."'";
																				$o_query = $o_main->db->query($s_sql, array());
																				$totalInterest += $interestAmount;
																			}

																			$s_sql = "SELECT * FROM collecting_company_cases_claim_lines WHERE collecting_company_case_id = '".$o_main->db->escape_str($case['id'])."' AND claim_type = 8 ORDER BY created DESC";
																			$o_query = $o_main->db->query($s_sql, array($case['id']));
																			$interest_claim_line = ($o_query ? $o_query->row_array() : array());
																			if($interest_claim_line) {
																				$s_sql = "UPDATE collecting_company_cases_claim_lines SET updated = NOW(), amount = '".$o_main->db->escape_str($totalInterest)."',
																				collecting_company_case_id = '".$o_main->db->escape_str($case['id'])."'
																				WHERE id = '".$o_main->db->escape_str($interest_claim_line['id'])."'";
																				$o_query = $o_main->db->query($s_sql);
																			} else {
																				$s_sql = "INSERT INTO collecting_company_cases_claim_lines SET updated = NOW(), amount = '".$o_main->db->escape_str($totalInterest)."',
																				collecting_company_case_id = '".$o_main->db->escape_str($case['id'])."', claim_type = 8, name= '".$o_main->db->escape_str($formText_Interest_output)."'";
																				$o_query = $o_main->db->query($s_sql);
																			}
																		}

																		$dueDate = date("Y-m-d", strtotime("+".$step['add_number_of_days_to_due_date']." days", time()));

																		$s_sql = "UPDATE collecting_company_cases SET last_change_date_for_process = NOW(), due_date = '".$o_main->db->escape_str($dueDate)."', collecting_cases_process_step_id = '".$o_main->db->escape_str($step['id'])."', status = '".$o_main->db->escape_str($step['status_id'])."', sub_status = '".$o_main->db->escape_str($step['sub_status_id'])."' WHERE id = '".$o_main->db->escape_str($case['id'])."'";
																		$o_query = $o_main->db->query($s_sql);
																		$casesToGenerate[] = $case['id'];

																	}
																} else {
																	if($manualProcessing && !in_array($case['id'], $casesToGenerate)) {
																		echo $formText_DueDateForProcessingIsNotReachedForCaseNr_output." ".$case['id']."<br/>";
																		break;
																	}
																}
															} else {
																echo $formText_OriginalDueDateIsMissingForCaseNr_output." ".$case['id']."<br/>";
																break;
															}
														} else {
															echo $formText_CaseHasClaimlinesThatNeedsConsideration_output." ".$case['id']."<br/>";
															break;
														}
													}
												}
											}
						                } else {
									        echo $formText_CaseIsPaused_output."<br/>";
										}
						            } else {
										echo $formText_CaseIsOnholdByCreditor_output."<br/>";
									}
						        } else {
									echo $formText_CaseHasActivePaymentPlan_output."<br/>";
						            include("handle_cases_payment_plan.php");
						        }
							} else {
						        echo $formText_CustomerMissingFields_output."<br/>";
						    }
						} else {
							echo $formText_CustomerNotConfirmedAsCompany_output."<br/>";
						}
				    } else {
				        echo $formText_CaseAlreadyInProcessOfCreatingLetter_output."<br/>";
				    }
				} else {
					echo $formText_CaseIsNotInAutomaticProcessType_output."<br/>";
				}
			} else {
				echo $formText_CaseIsDeleted_output."<br/>";
			}
		}
	} else {
		echo $formText_LateFeeDoesNotExist_output."<br/>";
	}
} else {
	echo $formText_CreditorNotFound_output."<br/>";
}
if(count($casesToGenerate) > 0){
    foreach($casesToGenerate as $caseToGenerate){
        $result = generate_pdf($caseToGenerate);
        if(count($result['errors']) > 0) {
            foreach($result['errors'] as $error) {
                echo $formText_LetterFailedToBeCreatedForCase_output." ".$caseToGenerate." ".$error."</br>";
            }
        } else {
            $successfullyCreatedLetters++;
        }
    }
}
echo $successfullyCreatedLetters." ".$formText_LettersWereCreated_output."<br/>";

$s_sql = "UPDATE creditor SET last_process_date = NOW() WHERE creditor.id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor['id']));
