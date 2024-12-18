<?php

$s_sql = "SELECT * FROM batch_renewal_basisconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $batch_renewal_basisconfig = $o_query->row_array();
}
$s_sql = "SELECT * FROM batch_renewal_accountconfig";
$o_query = $o_main->db->query($s_sql);
$batch_renewal_accountconfig = $o_query ? $o_query->row_array() : array();

if($batch_renewal_accountconfig['activateCheckForProjectNr'] == 1){
	$batch_renewal_basisconfig['activateCheckForProjectNr'] = 1;
} else if($batch_renewal_accountconfig['activateCheckForProjectNr'] == 2) {
	$batch_renewal_basisconfig['activateCheckForProjectNr'] = 0;
}
if($batch_renewal_accountconfig['activateCheckForDepartmentCode'] == 1){
	$batch_renewal_basisconfig['activateCheckForDepartmentCode'] = 1;
} else if($batch_renewal_accountconfig['activateCheckForDepartmentCode'] == 2) {
	$batch_renewal_basisconfig['activateCheckForDepartmentCode'] = 0;
}

$subscriptionId = $_POST['subscriptionId'];
$customerId = $_POST['customerId'];

if($subscriptionId > 0){
	$s_sql = "SELECT subscriptiontype.*, subscriptionmulti.* FROM subscriptionmulti LEFT OUTER JOIN subscriptiontype ON subscriptiontype.id = subscriptionmulti.subscriptiontype_id WHERE subscriptionmulti.id = ?";
	$o_query = $o_main->db->query($s_sql, array($subscriptionId));
	$subscription = ($o_query ? $o_query->row_array() : array());
}

if($customerId > 0){
	$s_sql = "SELECT * FROM customer WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($customerId));
	$customer = ($o_query ? $o_query->row_array() : array());
}
$s_sql = "SELECT * FROM customer_basisconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $customer_basisconfig = $o_query->row_array();
}
$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_customer_accountconfig = $o_query->row_array();
}

$s_sql = "SELECT * FROM article_accountconfig";
$o_query = $o_main->db->query($s_sql);
$article_accountconfig = $o_query ? $o_query->row_array() : array();


$s_sql = "SELECT * FROM batch_renewal_basisconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $batch_renewal_basisconfig = $o_query->row_array();
}
$s_sql = "SELECT * FROM batch_renewal_accountconfig";
$o_query = $o_main->db->query($s_sql);
$batch_renewal_accountconfig = $o_query ? $o_query->row_array() : array();

if($batch_renewal_accountconfig['activateCheckForProjectNr'] == 1){
	$batch_renewal_basisconfig['activateCheckForProjectNr'] = 1;
} else if($batch_renewal_accountconfig['activateCheckForProjectNr'] == 2) {
	$batch_renewal_basisconfig['activateCheckForProjectNr'] = 0;
}

$s_sql = "select subscriptionmulti.*, subscriptiontype.periodUnit, subscriptiontype.activate_specified_invoicing, subscriptiontype.default_subscriptionname_in_invoiceline, subscriptiontype.subscription_category, subscriptiontype.script_for_generating_order,  subscriptiontype.useMainContactAsContactperson
from subscriptionmulti left outer join subscriptiontype ON subscriptiontype.id = subscriptionmulti.subscriptiontype_id where str_to_date(startDate,'%Y-%m-%d') > str_to_date('0000-00-00','%Y-%m-%d')
AND (stoppedDate = '0000-00-00' OR (nextRenewalDate <> '0000-00-00' AND stoppedDate > nextRenewalDate))

AND (freeNoBilling < 1 OR freeNoBilling IS NULL)  AND subscriptionmulti.content_status = 0 AND subscriptionmulti.id = ".$subscription['id']." order by nextRenewalDate";

$o_query = $o_main->db->query($s_sql);
$v_row = $o_query ? $o_query->row_array() : array();

if(intval($v_row['placeSubscriptionNameInInvoiceLine']) == 0){
	$v_row['placeSubscriptionNameInInvoiceLine'] = $v_row['default_subscriptionname_in_invoiceline'];
} else {
	$v_row['placeSubscriptionNameInInvoiceLine']--;
}
$errorTxt = "";
if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
		if($subscription){
			$dateMarker = date("Y-m-d", time());
			if($v_row){
				$invoiceDateSettingFromSubscription = 0;
				if($customer_basisconfig['activateSubscriptionRenewalDateSetting']){
					$invoiceDateSettingFromSubscription = $v_row['id'];
				}
				$seperateInvoiceFromSubscription = 0;
				if($v_row['seperateInvoiceFromSubscription']){
					$seperateInvoiceFromSubscription = $v_row['id'];
				}

				$subscribtionId = $v_row["id"];
				$projectId = 0;
				if($batch_renewal_basisconfig['activateGetProjectcodeFromSubscription']){
					$connectTablename = 'subscriptionofficespaceconnection';
					$fieldInConnecttableForSubscriptionId = 'subscriptionId';
					$fieldInConnecttableForConnectedRecordId = 'officeSpaceId';
					$connectedRecordTableName = 'property_unit';
					$connectedRecordConnectionFieldname =  'property_id';
					$tablenameToGetProjectcodeFrom = 'property';
					$fieldnameToGetProjectcodeFrom = 'accountingproject_code';

					// $connectTablename = $batch_renewal_basisconfig['tablenameOnConnecttable'];
					// $fieldInConnecttableForSubscriptionId = $batch_renewal_basisconfig['fieldInConnecttableForSubscriptionId'];
					// $fieldInConnecttableForConnectedRecordId = $batch_renewal_basisconfig['fieldInConnecttableForConnectedRecordId'];
					// $connectedRecordTableName = $batch_renewal_basisconfig['connectedRecordTableName'];
					// $connectedRecordConnectionFieldname = $batch_renewal_basisconfig['connectedRecordConnectionFieldname'];
					// $tablenameToGetProjectcodeFrom = $batch_renewal_basisconfig['tablenameToGetProjectcodeFrom'];
					// $fieldnameToGetProjectcodeFrom = $batch_renewal_basisconfig['fieldnameToGetProjectcodeFrom'];

					if($batch_renewal_basisconfig['makeProjectcodeFromSubscriptionMandatory']){
						$projectCodeError = true;
					}
					$s_sql = "SELECT {$connectedRecordTableName}.* FROM {$connectTablename} LEFT OUTER JOIN {$connectedRecordTableName} ON {$connectedRecordTableName}.id = {$connectTablename}.{$fieldInConnecttableForConnectedRecordId}
					WHERE {$connectTablename}.{$fieldInConnecttableForSubscriptionId} = ?";
					$o_query = $o_main->db->query($s_sql, array($v_row['id']));
					if($o_query && $o_query->num_rows()>0){
						$projectCodeFromId = "";
						$connectedRecords = $o_query->result_array();

						foreach($connectedRecords as $connectedRecord){
							if($connectedRecord[$connectedRecordConnectionFieldname] != "") {
								$projectCodeFromId = $connectedRecord[$connectedRecordConnectionFieldname];
								break;
							}
						}
						$s_sql = "SELECT * FROM {$tablenameToGetProjectcodeFrom} WHERE id = ?";
						$o_query = $o_main->db->query($s_sql, array($projectCodeFromId));
						$projectCodeItem = $o_query ? $o_query->row_array() : array();
						if($projectCodeItem[$fieldnameToGetProjectcodeFrom] != "" && $projectCodeItem[$fieldnameToGetProjectcodeFrom] > 0){
							$projectCodeError = false;
							$projectId = $projectCodeItem[$fieldnameToGetProjectcodeFrom];
						}
					}
				}
				if($v_row['connectToProjectBy'] == 1) {
					$projectId = 0;
					$projectCodeError = true;
					if($v_row['projectId'] != ""){
						$projectCodeError = false;
						$projectId = $v_row['projectId'];
					}
				}

				if($v_row['nextRenewalDate'] == '0000-00-00')
					$nextrenewaldatevalue = $v_row['startDate'];
				else
					$nextrenewaldatevalue = $v_row['nextRenewalDate'];

				$lastdate = $nextdate = $nextrenewaldatevalue;
				$nextdate2 = strtotime($nextdate);



				$realStoppedDate = $v_row['stoppedDate'];
				$stoppedDate = strtotime("+". $v_row['periodNumberOfMonths']." months", strtotime($lastdate));

				if(intval($v_row['periodUnit']) == 0){
					$nextrenewaldate = date('Y-m-d',mktime(0, 0, 0, date('m',$nextdate2)+$v_row['periodNumberOfMonths'], date('j',$nextdate2),  date('y',$nextdate2)));
					$nextrenewaldate2 = date('d.m.Y',mktime(0, 0, 0, date('m',$nextdate2)+$v_row['periodNumberOfMonths'], date('j',$nextdate2),  date('y',$nextdate2))-24*60*60);
				} else {
					$nextrenewaldate = date('Y-m-d',mktime(0, 0, 0, date('m',$nextdate2), date('j',$nextdate2),  date('Y',$nextdate2)+$v_row['periodNumberOfMonths']));
					$nextrenewaldate2 = date('d.m.Y',mktime(0, 0, 0, date('m',$nextdate2), date('j',$nextdate2),  date('Y',$nextdate2)+$v_row['periodNumberOfMonths'])-24*60*60);
				}

				$modifier = 1;
				$modifierActivated = false;
				if($realStoppedDate != "0000-00-00" && $realStoppedDate != null) {
					if(strtotime($realStoppedDate) < $stoppedDate-24*60*60) {
						$earlier = new DateTime(date("Y-m-d", strtotime($nextrenewaldatevalue)));
						$later = new DateTime(date("Y-m-d", strtotime($realStoppedDate)));
						$laterTimestamp = $later->getTimestamp();
						$lastDayOfStoppedDate = date("t.m.Y", $laterTimestamp);
						$dayOfStoppedDate = date("d.m.Y", $laterTimestamp);

						$wholeMonths = 0;
						do {
							$earlier->add(new DateInterval("P1M"));
							$timestamp = $earlier->getTimestamp();
							$wholeMonths++;
						} while($timestamp < $laterTimestamp);

						$modifier = $wholeMonths;
						if($lastDayOfStoppedDate != $dayOfStoppedDate) {
							$stoppedDateMonthTotalDays = date("t", strtotime($realStoppedDate));
							$stoppedDateMonthFirstDay = date("01.m.Y", strtotime($realStoppedDate));

							if(strtotime($nextrenewaldatevalue) > strtotime($stoppedDateMonthFirstDay)){
								$earlier = new DateTime(date("Y-m-d", strtotime($nextrenewaldatevalue)));
							} else {
								$earlier = new DateTime(date("Y-m-d", strtotime($stoppedDateMonthFirstDay)));
							}
							$later = new DateTime(date("Y-m-d", strtotime($realStoppedDate)+24*60*60));
							$diff = $later->diff($earlier);
							$stoppedDateMonthDays = $diff->format("%a");

							$modifier = $modifier + number_format($stoppedDateMonthDays/$stoppedDateMonthTotalDays, 4, ".", "");
						}
						$modifierActivated = true;

						$nextrenewaldate = date('Y-m-d', strtotime($realStoppedDate));
						$nextrenewaldate2 = date('d.m.Y', strtotime($realStoppedDate));
						$decimalNumber = 4;
					}
				}
                $s_sql = "SELECT * FROM subscriptionmulti_date_for_invoicing WHERE subscriptionmulti_id = ".$v_row['id']." ORDER BY id ASC";
                $o_query = $o_main->db->query($s_sql);
                $datesForInvoicing = ($o_query ? $o_query->result_array() : array());
                $dateArray = array();
                foreach($datesForInvoicing as $dateForInvoicing) {
                    $dateArray[] = date("d.m", strtotime($dateForInvoicing['date']));
                }
                $totalCollectNumber = count($dateArray);
                foreach($dateArray as $key => $dateToCompare) {
                    if(date("d.m", strtotime($nextrenewaldatevalue)) == $dateToCompare){
                        $collectNumber = $key+1;
                    }
                }
                if($v_row['subscription_category'] == 1){
                    $addYear = 0;
                    $collectIndex = $collectNumber;
                    if($collectIndex >= count($dateArray)){
                        $collectIndex = 0;
                    }
                    $nextrenewaldate = date('Y-m-d', strtotime($dateArray[$collectIndex].".".(date("Y", strtotime($nextrenewaldatevalue)))));

                    if(strtotime($nextrenewaldate) <= strtotime($nextrenewaldatevalue)) {
                        $nextrenewaldate = date('Y-m-d', strtotime("+1 year", strtotime($nextrenewaldate)));
                    }
                    $nextrenewaldate2 = date('d.m.Y', strtotime($nextrenewaldate)-24*60*60);
                }
                $nextrenewaldate2raw = date('Y-m-d', strtotime($nextrenewaldate2));

				$lastdate2 = date('d.m.Y',mktime(0, 0, 0, date('m',$nextdate2), date('j',$nextdate2),  date('y',$nextdate2)));

				if($nextrenewaldatevalue != '0000-00-00' &&  ($v_row['stoppedDate'] == '0000-00-00' || strtotime($v_row['stoppedDate']) > strtotime($nextrenewaldatevalue)))
				{
                    $nextrenewaldatevalueraw = $nextrenewaldatevalue;
					$nextrenewaldatevalue = date('d.m.Y', strtotime($nextrenewaldatevalue));
					// var_dump($v_row);
					if(!$v_row['activate_specified_invoicing']){
						// Create order for each subscriptionline
						$s_sql = "SELECT * FROM subscriptionline WHERE subscribtionId = ?";
						$lines = array();
						$o_query = $o_main->db->query($s_sql, array($v_row["id"]));
						if($o_query && $o_query->num_rows()>0){
							$lines = $o_query->result_array();
						}
					} else {
						// Create order for each type
						$s_sql = "SELECT rsi.*, ww.date, ww.estimatedTimeuse, rsi.id as specified_invoicing_id FROM workplanlineworker ww
						LEFT OUTER JOIN repeatingorder_specified_invoicing_group rsig ON rsig.id = ww.specified_invoicing_id
						LEFT OUTER JOIN repeatingorder_specified_invoicing rsi ON rsi.repeatingorder_specified_invoicing_group_id = rsig.id
						WHERE ww.repeatingOrderId = ? AND ww.specified_invoicing_id > 0 AND ww.date >= ? AND ww.date <= ? AND (ww.absenceDueToIllness is null OR ww.absenceDueToIllness = 0) ORDER BY ww.date ASC";
						$o_query = $o_main->db->query($s_sql, array($v_row['id'], date("Y-m-d", strtotime($nextrenewaldatevalue)), date("Y-m-d", strtotime($nextrenewaldate2))));
						$workplanlines = $o_query ? $o_query->result_array() : array();
						$lines = array();
						// $sl_rows_prep = array();
						$sl_rows_count = array();
						$datesAdded = array();
						foreach($workplanlines as $temp_line){
	                        $addLine = false;
							if($temp_line['invoicingType'] == 0){
								$temp_line['amount'] = $temp_line['estimatedTimeuse'];
							} else if($temp_line['invoicingType'] == 1){
								$temp_line['amount'] = 1;
							}
							$temp_line['specified_invoicing'] = true;
							$temp_line['workDate'] = date("d.m.Y", strtotime($temp_line['date']));
	                        if($temp_line['invoicingType'] == 1){
								if($batch_renewal_accountconfig['specifiedInvoicing_makeMultiWorkersIntoOneTime']){
									if(!isset($datesAdded[$temp_line['workDate']])) {
										$datesAdded[$temp_line['workDate']] = $temp_line;
										$addLine = true;
									}
								} else {
									$addLine = true;
								}
							} else {
								$addLine = true;
							}
							if($addLine) {
	    						// $sl_rows_prep[$temp_line['date']."-".$temp_line['id']] = $temp_line;
	    						$sl_rows_count[$temp_line['specified_invoicing_id']."_".$temp_line['workDate']][] = $temp_line;
	                        }
						}
						foreach($sl_rows_count as $specified_invoicing_id => $sl_rows_array) {
							foreach($sl_rows_array as $sl_rows_single){
	                            if($batch_renewal_accountconfig['specifiedInvoicing_linesToShow'] > 0 && count($sl_rows_count[$specified_invoicing_id]) > $batch_renewal_accountconfig['specifiedInvoicing_linesToShow']){
									if(!isset($lines[$specified_invoicing_id])){
										$sl_rows_single['combined_specified'] = true;
										$lines[$specified_invoicing_id] = $sl_rows_single;
										$lines[$specified_invoicing_id]['start_date'] = $sl_rows_single['workDate'];
										$lines[$specified_invoicing_id]['end_date'] = $sl_rows_single['workDate'];
									} else {
										$lines[$specified_invoicing_id]['amount'] += $sl_rows_single['amount'];
										if(strtotime($sl_rows_single['workDate']) < strtotime($lines[$specified_invoicing_id]['start_date'])) {
											$lines[$specified_invoicing_id]['start_date'] = $sl_rows_single['workDate'];
										}
										if(strtotime($sl_rows_single['workDate']) > strtotime($lines[$specified_invoicing_id]['end_date'])) {
											$lines[$specified_invoicing_id]['end_date'] = $sl_rows_single['workDate'];
										}
									}
								} else {
									if(isset($lines[$sl_rows_single['date']."-".$sl_rows_single['id']])){
										$sl_rows_single_new = $lines[$sl_rows_single['date']."-".$sl_rows_single['id']];
									} else {
										$sl_rows_single_new = $sl_rows_single;
										$sl_rows_single_new['amount'] = 0;
									}
									$sl_rows_single_new['amount']+=$sl_rows_single['amount'];
									$lines[$sl_rows_single['date']."-".$sl_rows_single['id']] = $sl_rows_single_new;
								}
							}
						}
					}

            		$noError = true;
                    if($v_row['useMainContactAsContactperson']){
        				$s_sql = "SELECT contactperson.* FROM contactperson
        				WHERE contactperson.customerId = ? AND contactperson.mainContact = 1";
        				$o_query = $o_main->db->query($s_sql, array($v_row['customerId']));
        				$contactPerson = $o_query ? $o_query->row_array() : array();
        			} else {
    					$s_sql = "SELECT contactperson.* FROM contactperson_role_conn
    					LEFT OUTER JOIN contactperson ON contactperson.id = contactperson_role_conn.contactperson_id
    					WHERE contactperson_role_conn.subscriptionmulti_id = ? AND (contactperson_role_conn.role = 0 OR contactperson_role_conn.role is null OR contactperson_role_conn.role = 1)
    					ORDER BY contactperson_role_conn.role DESC";
    					$o_query = $o_main->db->query($s_sql, array($v_row['id']));
    					$contactPerson = $o_query ? $o_query->row_array() : array();
                    }

					$contactPersonId = $contactPerson['id'];

					if(intval($v_row['ownercompany_id']) == 0){
						$noError = false;
						$errorTxt .= "<li>".$formText_OwnerCompanyIsNotAttachedToTheSubscription_output . " ".$customer['name']." - ".$v_row['subscriptionName']." (".$nextrenewaldatevalue." - ".$nextrenewaldate2.") "."</li>";
					}

                    if($v_row['subscription_category'] == 2){
                        require(__DIR__."/../../../CrmPriceOverview/output/renewal_include.php");
                    }
    				if($v_row['script_for_generating_order'] != ""){
    					require(__DIR__ . "/../../../SubscriptionReportAdvanced/output/includes/scripts/".$v_row['script_for_generating_order']."/script.php");
    				}
					foreach($lines as $line){
						//update bookingaccount and vatcode
			            $s_sql = "SELECT * FROM article WHERE id = ?";
			            $o_query = $o_main->db->query($s_sql, array($line['articleNumber']));
			            $article = $o_query ? $o_query->row_array() : array();

			            $s_sql = "SELECT * FROM customer WHERE id = ?";
			            $o_query = $o_main->db->query($s_sql, array($v_row['customerId']));
			            $customer = $o_query ? $o_query->row_array() : array();

						$vatCode = $article['VatCodeWithVat'];
			            $bookaccountNr = $article['SalesAccountWithVat'];

						if($v_customer_accountconfig['subscription_activate_edit_bookaccount_and_vatcode']){
							$vatCode = $line['vatCode'];
							$bookaccountNr = $line['bookaccountCode'];
						}

				        if($vatCode == ""){
			                $vatCode = $article_accountconfig['defaultVatCodeForArticle'];
				        }
			        	if($bookaccountNr == ""){
			                $bookaccountNr = $article_accountconfig['defaultSalesAccountWithVat'];
				        }

			            $vatPercent = '';


			            $vatCodeError = false;
			            $bookAccountError = false;
			            $articleError = false;
			            $projectError = false;

		                $s_sql = "SELECT * FROM vatcode WHERE vatCode = ?";
		                $o_query = $o_main->db->query($s_sql, array($vatCode));
		                $vatcodeItem = $o_query ? $o_query->row_array() : array();
		                if(!$vatcodeItem){
		                    $noError = false;
		                    $vatCodeError = true;
		                }

		                $s_sql = "SELECT * FROM bookaccount WHERE accountNr = ?";
		                $o_query = $o_main->db->query($s_sql, array($bookaccountNr));
		                $bookaccountItem = $o_query ? $o_query->row_array() : array();
		                if(!$bookaccountItem){
		                    $noError = false;
		                    $bookAccountError = true;
		                }

		                $s_sql = "SELECT * FROM article WHERE id = ?";
		                $o_query = $o_main->db->query($s_sql, array($line['articleNumber']));
		                $bookaccountItem = $o_query ? $o_query->row_array() : array();
		                if(!$bookaccountItem){
		                    $noError = false;
		                    $articleError = true;
		                }

			            if($batch_renewal_basisconfig['activateCheckForProjectNr']) {
			                $s_sql = "SELECT * FROM projectforaccounting WHERE projectnumber = ?";
			                $o_query = $o_main->db->query($s_sql, array($projectId));
			                $bookaccountItem = $o_query ? $o_query->row_array() : array();
			                if(!$bookaccountItem){
			                    $noError = false;
			                    $projectError = true;
			                }
			            }
	            		if($vatCodeError || $bookAccountError || $articleError || $projectError){
		                    if($vatCodeError){
		                        $errorTxt .= "<li>".$formText_VatCodeDoesntExist_output . " ".$formText_For_output." ".$line['articleName']." (".$nextrenewaldatevalue." - ".$nextrenewaldate2.") "."</li>";
		                    }
		                    if($bookAccountError){
		                        $errorTxt .= "<li>".$formText_BookAccountDoesntExist_output . " ".$formText_For_output." ".$line['articleName']." (".$nextrenewaldatevalue." - ".$nextrenewaldate2.") "."</li>";
		                    }
		                    if($articleError){
		                        $errorTxt .= "<li>".$formText_InvalidArticleNumber_output . " ".$formText_For_output." ".$line['articleName']." (".$nextrenewaldatevalue." - ".$nextrenewaldate2.") "."</li>";
		                    }
		                    if($projectError){
		                        $errorTxt .= "<li>".$formText_InvalidProjectFAccNumber_output . " ".$formText_For_output." ".$line['articleName']." (".$nextrenewaldatevalue." - ".$nextrenewaldate2.") "."</li>";
		                    }
						}
					}
					if(count($lines) == 0){
						$noError = false;
		                $errorTxt .= "<li>".$formText_CannotRenewSubscriptionWithoutSubscriptionlines_output . "</li>";
					} else {
						$totalSubscriptionPrice = 0;
                        foreach($lines as $line) {
                            $pricePerPiece = $line['pricePerPiece'];
                            if($line['articleOrIndividualPrice']) {
                                $s_sql = "SELECT * FROM article WHERE id = ?";
                                $o_query = $o_main->db->query($s_sql, array($line['articleNumber']));
                                $article = $o_query ? $o_query->row_array() : array();
                                $pricePerPiece = $article['price'];
                            }
                            if($line['fromPriceList']){
                                if(intval($v_row['periodUnit']) == 0){
                                    $pricePerPiece = round($pricePerPiece / 12 * $totalAmount, 2);
                                    $totalAmount = 1;
                                }
                            }
                            if(!$modifierActivated){
                                if($v_row['subscription_category'] == 1){
                                    $totalAmount = 1 * $line['amount'];
                                } else if($v_row['override_periods'] > 0) {
                                    $totalAmount = $v_row['override_periods']*$line['amount'];
                                } else {
                                    $totalAmount = $v_row['periodNumberOfMonths'] * $line['amount'];
                                }
                            } else {
                                $totalAmount = $line['amount']*$modifier;
                            }
        					$totalSubscriptionPrice += round($pricePerPiece * $totalAmount * (1 - $line['discountPercent']/100), 2);
        				}

						if($totalSubscriptionPrice == 0){
							$noError = false;
			                $errorTxt .= "<li>".$formText_CannotRenewSubscriptionWith0Price_output . "</li>";
			            }
					}
					if($noError){
						$seperatedInvoice = 0;
						// if($v_row['reference'] != "" || $v_row['delivery_address_line_1'] != "" || ($v_row['delivery_date'] != "0000-00-00" && $v_row['delivery_date'] != "")) {
						// 	$seperatedInvoice = 1;
						// }
                        $s_order_reference = $delivery_address_line_1 = $delivery_address_city =  $delivery_address_postal_code = $delivery_address_country = '';

        				$s_sql = "SELECT * FROM customer_subunit WHERE id = ?";
        				$o_query = $o_main->db->query($s_sql, array($v_row['customer_subunit_id']));
        				$subunit = $o_query ? $o_query->row_array() : array();
        				if($v_row['address_source'] == 0 && $subunit){
                            $delivery_address_line_1 = $subunit['delivery_address_line_1'];
                            $delivery_address_city = $subunit['delivery_address_city'];
                            $delivery_address_postal_code = $subunit['delivery_address_postal_code'];
                            $delivery_address_country = $subunit['delivery_address_country'];
                        } else {
                            $delivery_address_line_1 = $v_row['delivery_address_line_1'];
                            $delivery_address_city = $v_row['delivery_address_city'];
                            $delivery_address_postal_code = $v_row['delivery_address_postal_code'];
                            $delivery_address_country = $v_row['delivery_address_country'];
    					}
                        if($v_row['reference'] == "") {
                            if($subunit) {
                                $s_order_reference = $subunit['reference'];
                            } else {
                                $s_order_reference = $customer['defaultInvoiceReference'];
                            }
                        } else {
                            if($v_row['reference'] != 'empty') {
                                $s_order_reference = $v_row['reference'];
                            }
                        }
	    				$sql = "INSERT INTO customer_collectingorder SET
	                    created = now(),
	                    createdBy='".$variables->loggID."',
	                    date = NOW(),
	                    customerId = '".$v_row['customerId']."',
	                    accountingProjectCode = '".$projectId."',
	                    contactpersonId = '".$contactPersonId."',
	                    ownercompanyId = ?,
	                    seperateInvoiceFromSubscription = ?,
						reference = ?,
						delivery_address_line_1 = ?,
						delivery_address_city = ?,
						delivery_address_postal_code = ?,
						delivery_address_country = ?,
						seperatedInvoice = ?,
						department_for_accounting_code = ?,
	            		approvedForBatchinvoicing = 1";
	                    $o_query = $o_main->db->query($sql, array($v_row['ownercompany_id'], $seperateInvoiceFromSubscription, $s_order_reference, $delivery_address_line_1,
                        $delivery_address_city, $delivery_address_postal_code, $delivery_address_country, $seperatedInvoice, $v_row['departmentCode']));
	        			$collectingorderId = $o_main->db->insert_id();

	    				if(intval($collectingorderId) == 0){
							$noError = false;
							$errorTxt .= "<li>".$formText_CollectingOrderWasNotCreatedFor_output . " ".$customer['name']." - ".$v_row['subscriptionName']." (".$nextrenewaldatevalue." - ".$nextrenewaldate2.") "."</li>";
						}

						if(!$noError){
			                $errorTxt .= "<li>".$formText_SubscriptionWasNotRenewed_output . "</li>";
						} else {
							// Update renewal dates
							$s_sql = "UPDATE subscriptionmulti SET nextRenewalDate = ?, lastRenewalDate = ? WHERE id = ?";
							$o_main->db->query($s_sql, array($nextrenewaldate, $lastdate, $v_row["id"]));


							foreach($lines as $line){
								$not_full_order = 0;
								$pricePerPiece = $line['pricePerPiece'];
								if($line['articleOrIndividualPrice']) {
									  $s_sql = "SELECT * FROM article WHERE id = ?";
									  $o_query = $o_main->db->query($s_sql, array($line['articleNumber']));
									  $article = $o_query ? $o_query->row_array() : array();
									  $pricePerPiece = $article['price'];
									  if($v_customer_accountconfig['use_articlename_when_use_articleprice']) {
										  $line['articleName'] = $article['name'];
									  }
								}
								if($modifierActivated){
									$not_full_order = 1;
								}
								if(!$modifierActivated){
                                    if($v_row['subscription_category'] == 1){
                						$totalAmount = 1 * $line['amount'];
                					} else {
                						$totalAmount = $v_row['periodNumberOfMonths'] * $line['amount'];
                					}
								} else {
									$totalAmount = $line['amount']*$modifier;
								}
								//update bookingaccount and vatcode
					            $s_sql = "SELECT * FROM article WHERE id = ?";
					            $o_query = $o_main->db->query($s_sql, array($line['articleNumber']));
					            $article = $o_query ? $o_query->row_array() : array();

								$prepaidCommonCost = 0;
								if($article['system_article_type'] == 1){
									$prepaidCommonCost = 1;
								}

					            $s_sql = "SELECT * FROM customer WHERE id = ?";
					            $o_query = $o_main->db->query($s_sql, array($v_row['customerId']));
					            $customer = $o_query ? $o_query->row_array() : array();

								$vatCode = $article['VatCodeWithVat'];
					            $bookaccountNr = $article['SalesAccountWithVat'];

								if($v_customer_accountconfig['subscription_activate_edit_bookaccount_and_vatcode']){
									$vatCode = $line['vatCode'];
									$bookaccountNr = $line['bookaccountCode'];
								}
						        if($vatCode == ""){
					                $vatCode = $article_accountconfig['defaultVatCodeForArticle'];
						        }
					        	if($bookaccountNr == ""){
					                $bookaccountNr = $article_accountconfig['defaultSalesAccountWithVat'];
						        }
					            $vatPercent = '';

				                $vatPercent = 0;

		            			$periodization = 0;
		            			$periodizationMonths = "";
		            			if(intval($subscription['defaultPeriodising']) > 0){
		            				$periodization = intval($subscription['defaultPeriodising']);
			        				$start    = (new DateTime($nextrenewaldatevalueraw))->modify('first day of this month');
									$end      = (new DateTime($nextrenewaldate2raw))->modify('first day of next month');
									$interval = DateInterval::createFromDateString('1 month');
									$period   = new DatePeriod($start, $interval, $end);

									foreach ($period as $dt) {
										$periodizationMonths .= $dt->format("mY") . ",";
									}
									if(strlen($periodizationMonths) > 0){
										$periodizationMonths = substr($periodizationMonths, 0, -1);
									}
		            			}
								if($line['specified_invoicing']) {
		                            if($line['combined_specified']) {
		                                $date_string = " (".$line['start_date']." - ".$line['end_date'].")";
		                            } else {
		                                $date_string = " ".$line['workDate'];
		                            }
		                        } else {
                                    if($v_row['subscription_category'] == 1){
                                        $collectString = "";
                                        if($totalCollectNumber > 1){
                                            $collectString = "(".$formText_PartInvoice_output." ".$collectNumber. " ".$formText_Of_output ." ".$totalCollectNumber.")";
                                        }
                                        $date_string = " ".$collectString;
                                    } else {
                                        $date_string = " (".$nextrenewaldatevalue." - ".$nextrenewaldate2.")";
                                    }
		                        }
		                        $subscriptionNameString = "";
		                        if($v_row['placeSubscriptionNameInInvoiceLine']) {
		                            $subscriptionNameString = $v_row['subscriptionName'] . " - ";
		                        }
		                        $articleName = $subscriptionNameString.$line['articleName'].$date_string;

	            				$sql = $o_main->db->query("INSERT INTO orders SET moduleID = ?, createdBy = ?, created = NOW(), customerID = ?,  articleNumber = ?, articleName = ?, amount = ?, pricePerPiece = ?, discountPercent = ?, priceTotal = ?,  status = 4,  invoiceDateSettingFromSubscription = ?, subscribtionId = ?,  contactPerson = ?, projectCode = ?, bookaccountNr = ?, vatCode = ?, vatPercent = ?, prepaidCommonCost = ?, dateFrom = ?, dateTo = ?, periodization= ?, periodizationMonths = ?,  collectingorderId = ?, not_full_order = ?", array(0, $variables->loggID, $v_row['customerId'], $line['articleNumber'], $articleName, $totalAmount, $pricePerPiece, $line['discountPercent'], round($pricePerPiece * $totalAmount * (1 - $line['discountPercent']/100), 2), $invoiceDateSettingFromSubscription, $subscribtionId, $contactPersonId, $projectId, $bookaccountNr, $vatCode, $vatPercent, $prepaidCommonCost, $nextrenewaldatevalueraw, $nextrenewaldate2raw, $periodization, $periodizationMonths, $collectingorderId, $not_full_order));

							}
						}
					}
				}
			}
			if($errorTxt != ""){
				$fw_error_msg = '
			    <div class="item-error">
			        <div class="alert alert-danger">
			            <ul>
			            '.$errorTxt.'
			            </ul>
			        </div>
			    </div>';
			} else {
	            $fw_redirect_url = $_POST['redirect_url'];
	        }
		}
	}
}
$s_sql = "SELECT * FROM ownercompany WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($subscription['ownercompany_id']));
$ownerCompanyData = $o_query ? $o_query->row_array() : array();

$projectId = 0;
if($batch_renewal_basisconfig['activateGetProjectcodeFromSubscription']){
	$connectTablename = $batch_renewal_basisconfig['tablenameOnConnecttable'];
	$fieldInConnecttableForSubscriptionId = $batch_renewal_basisconfig['fieldInConnecttableForSubscriptionId'];
	$fieldInConnecttableForConnectedRecordId = $batch_renewal_basisconfig['fieldInConnecttableForConnectedRecordId'];
	$connectedRecordTableName = $batch_renewal_basisconfig['connectedRecordTableName'];
	$connectedRecordConnectionFieldname = $batch_renewal_basisconfig['connectedRecordConnectionFieldname'];
	$tablenameToGetProjectcodeFrom = $batch_renewal_basisconfig['tablenameToGetProjectcodeFrom'];
	$fieldnameToGetProjectcodeFrom = $batch_renewal_basisconfig['fieldnameToGetProjectcodeFrom'];

	if($batch_renewal_basisconfig['makeProjectcodeFromSubscriptionMandatory']){
		$projectCodeError = true;
	}
	$s_sql = "SELECT {$connectedRecordTableName}.* FROM {$connectTablename} LEFT OUTER JOIN {$connectedRecordTableName} ON {$connectedRecordTableName}.id = {$connectTablename}.{$fieldInConnecttableForConnectedRecordId}
	WHERE {$connectTablename}.{$fieldInConnecttableForSubscriptionId} = ?";
	$o_query = $o_main->db->query($s_sql, array($v_row['id']));
	if($o_query && $o_query->num_rows()>0){
		$projectCodeFromId = "";
		$connectedRecords = $o_query->result_array();

		foreach($connectedRecords as $connectedRecord){
			if($connectedRecord[$connectedRecordConnectionFieldname] != "") {
				$projectCodeFromId = $connectedRecord[$connectedRecordConnectionFieldname];
				break;
			}
		}
		$s_sql = "SELECT * FROM {$tablenameToGetProjectcodeFrom} WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($projectCodeFromId));
		$projectCodeItem = $o_query ? $o_query->row_array() : array();
		if($projectCodeItem[$fieldnameToGetProjectcodeFrom] != "" && $projectCodeItem[$fieldnameToGetProjectcodeFrom] > 0){
			$projectCodeError = false;
			$projectId = $projectCodeItem[$fieldnameToGetProjectcodeFrom];
		}
	}
}
if($v_row['connectToProjectBy'] == 1) {
	$projectId = 0;
	$projectCodeError = true;
	if($v_row['projectId'] != ""){
		$projectCodeError = false;
		$projectId = $v_row['projectId'];
	}
}
// Create order for each subscriptionline
$s_sql = "SELECT * FROM subscriptionline WHERE subscribtionId = ?";
$lines = array();
$o_query = $o_main->db->query($s_sql, array($v_row["id"]));
if($o_query && $o_query->num_rows()>0){
	$lines = $o_query->result_array();
}

$errorTxt = "";
$noError = true;
$warningMessage = "";
if(intval($v_row['ownercompany_id']) == 0){
	$noError = false;
	$errorTxt .= "<li>".$formText_OwnerCompanyIsNotAttachedToTheSubscription_output . " ".$customer['name']." - ".$v_row['subscriptionName']." (".$nextrenewaldatevalue." - ".$nextrenewaldate2.") "."</li>";
}
if($v_row['subscription_category'] == 2){
    require(__DIR__."/../../../CrmPriceOverview/output/renewal_include.php");
}
foreach($lines as $line){

    $s_sql = "SELECT * FROM article WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($line['articleNumber']));
    $article = $o_query ? $o_query->row_array() : array();

    $s_sql = "SELECT * FROM customer WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($v_row['customerId']));
    $customer = $o_query ? $o_query->row_array() : array();

	$pricePerPiece = $line['pricePerPiece'];
	if($line['articleOrIndividualPrice']){
		$pricePerPiece = $article['price'];
		if($v_customer_accountconfig['use_articlename_when_use_articleprice']) {
			$line['articleName'] = $article['name'];
		}
	}
    if($v_row['subscription_category'] == 1){
        $totalAmount = 1 * $line['amount'];
    } else {
        $totalAmount = $v_row['periodNumberOfMonths'] * $line['amount'];
    }
	$totalRowPrice = $totalAmount * $pricePerPiece * ((100-$line['discountPercent'])/100);

	$vatCode = $article['VatCodeWithVat'];
	$bookaccountNr = $article['SalesAccountWithVat'];

	if($vatCode == ""){
		$vatCode = $article_accountconfig['defaultVatCodeForArticle'];
	}
	if($bookaccountNr == ""){
		$bookaccountNr = $article_accountconfig['defaultSalesAccountWithVat'];
	}

    $vatPercent = '';

    $vatCodeError = false;
    $bookAccountError = false;
    $articleError = false;
    $projectError = false;

    $s_sql = "SELECT * FROM vatcode WHERE vatCode = ?";
    $o_query = $o_main->db->query($s_sql, array($vatCode));
    $vatcodeItem = $o_query ? $o_query->row_array() : array();
    if(!$vatcodeItem){
        $noError = false;
        $vatCodeError = true;
    }

    $s_sql = "SELECT * FROM bookaccount WHERE accountNr = ?";
    $o_query = $o_main->db->query($s_sql, array($bookaccountNr));
    $bookaccountItem = $o_query ? $o_query->row_array() : array();
    if(!$bookaccountItem){
        $noError = false;
        $bookAccountError = true;
    }

    $s_sql = "SELECT * FROM article WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($line['articleNumber']));
    $bookaccountItem = $o_query ? $o_query->row_array() : array();
    if(!$bookaccountItem){
        $noError = false;
        $articleError = true;
    }

    if($batch_renewal_basisconfig['activateCheckForProjectNr']) {
        $s_sql = "SELECT * FROM projectforaccounting WHERE projectnumber = ?";
        $o_query = $o_main->db->query($s_sql, array($projectId));
        $bookaccountItem = $o_query ? $o_query->row_array() : array();
        if(!$bookaccountItem){
            $noError = false;
            $projectError = true;
        }
    }
    if($vatCodeError || $bookAccountError || $articleError || $projectError){
        if($vatCodeError){
            $errorTxt .= "<li>".$formText_VatCodeDoesntExist_output . " ".$formText_For_output." ".$v_row['subscriptionName']."  - ".$line['articleName']." "."</li>";
        }
        if($bookAccountError){
            $errorTxt .= "<li>".$formText_BookAccountDoesntExist_output . " ".$formText_For_output." ".$v_row['subscriptionName']."  - ".$line['articleName']." "."</li>";
        }
        if($articleError){
            $errorTxt .= "<li>".$formText_InvalidArticleNumber_output . " ".$formText_For_output." ".$v_row['subscriptionName']."  - ".$line['articleName']." "."</li>";
        }
        if($projectError){
            $errorTxt .= "<li>".$formText_InvalidProjectFAccNumber_output . " ".$formText_For_output." ".$v_row['subscriptionName']."  - ".$line['articleName']." "."</li>";
        }
	}
	if($pricePerPiece <= 0 || $totalRowPrice <= 0) {
		$warningMessage .= "<li>".$formText_SubscriptionLinePriceIsZero_output . " ".$formText_For_output." ".$v_row['subscriptionName']."  - ".$line['articleName']." "."</li>";
	}
}
$contactPersonName = "";
if($subscription['useMainContactAsContactperson']){
    $s_sql = "SELECT contactperson.* FROM contactperson
    WHERE contactperson.customerId = ? AND contactperson.mainContact = 1";
    $o_query = $o_main->db->query($s_sql, array($subscription['customerId']));
    $contactPersonData = $o_query ? $o_query->row_array() : array();
} else {
    $s_sql = "SELECT contactperson.* FROM contactperson_role_conn
    LEFT OUTER JOIN contactperson ON contactperson.id = contactperson_role_conn.contactperson_id
    WHERE contactperson_role_conn.subscriptionmulti_id = ? AND (contactperson_role_conn.role = 0 OR contactperson_role_conn.role is null OR contactperson_role_conn.role = 1)
    ORDER BY contactperson_role_conn.role DESC";
    $o_query = $o_main->db->query($s_sql, array($subscription['id']));
    $contactPersonData = $o_query ? $o_query->row_array() : array();
}

if ($contactPersonData) {
	$contactPersonName = $contactPersonData['name']." ".$contactPersonData['middlename']." ".$contactPersonData['lastname'];
}

?>

<div class="popupform">
	<div class="popupTitle"><?php echo $formText_RenewSubscription_output;?>: <?php echo $subscription['subscriptionName']?></div>
	<?php if($contactPersonName != "") { ?>
		<div class=""><?php echo $formText_YourContact_output?>: <?php echo $contactPersonName; ?></div>
	<?php } ?>
	<?php if($subscription['freeNoBilling']){
		echo $formText_ContractIsMarkedAsFreeNoBilling_output;
		?>
	<?php } else { ?>
		<div id="popup-validate-message" style="display:none;"></div>
		<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=subscriptionRenewal";?>" method="post">
			<input type="hidden" name="fwajax" value="1">
			<input type="hidden" name="fw_nocss" value="1">
			<input type="hidden" name="output_form_submit" value="1">
			<input type="hidden" name="customerId" value="<?php echo $customerId;?>">
			<input type="hidden" name="subscriptionId" value="<?php echo $subscriptionId;?>">
	        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customerId; ?>">
			<div class="inner">

				<div class="popupRow initialLine">
					<?php
	                if($subscription['periodUnit'] == 0){
	                    echo $formText_PeriodNumberOfMonths_output.":";
	                } else {
	                    echo $formText_PeriodNumberOfYears_output.":";
	                }
	                ?>
	                <b><?php echo $subscription['periodNumberOfMonths'];?></b>
				</div>

				<?php if(!$noError) { ?>
					<div class="alert alert-danger">
						<ul class="errorList">
							<?php echo $errorTxt;?>
						</ul>
					</div>
				<?php } ?>
				<?php if($warningMessage != "") { ?>
					<div class="alert alert-warning">
						<ul class="errorList">
							<?php echo $warningMessage;?>
						</ul>
					</div>
				<?php } ?>
				<?php if(!$ownerCompanyData) { ?>
				<div class="titleError"><?php echo $formText_NoOwnerCompany_Output;?></div>
				<?php } ?>
				<?php if($projectCodeError) { ?>
				<div class="titleError"><?php echo $formText_NoProjectCodeFound_Output;?></div>
				<?php } ?>
				<div class="popupRow">
					<div class="item-order">
						<table class="table table-condensed">
						<thead>
							<tr>
								<th><?php echo $formText_OrderlineText_Output;?></th>
								<th><?php echo $formText_Amount_Output;?></th>
								<th><span class="articleInfo">&nbsp;</span><?php echo $formText_PricePerPiece_Output;?></th>
								<th><?php echo $formText_Discount_Output;?></th>
								<th class="text-right"><?php echo $formText_TotalPrice_Output;?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							if($subscription['nextRenewalDate'] == '0000-00-00')
								$nextrenewaldatevalue = $subscription['startDate'];
							else
								$nextrenewaldatevalue = $subscription['nextRenewalDate'];

							$lastdate = $nextdate = $nextrenewaldatevalue;
							$nextdate2 = strtotime($nextdate);
							//
							$nextrenewaldatevalue = date('d.m.Y', strtotime($nextrenewaldatevalue));
							if(intval($subscription['periodUnit']) == 0){
								$nextrenewaldate = date('Y-m-d',mktime(0, 0, 0, date('m',$nextdate2)+$subscription['periodNumberOfMonths'], date('j',$nextdate2),  date('y',$nextdate2)));
								$nextrenewaldate2 = date('d.m.Y',mktime(0, 0, 0, date('m',$nextdate2)+$subscription['periodNumberOfMonths'], date('j',$nextdate2),  date('y',$nextdate2))-24*60*60);
							} else {
								$nextrenewaldate = date('Y-m-d',mktime(0, 0, 0, date('m',$nextdate2), date('j',$nextdate2),  date('Y',$nextdate2)+$subscription['periodNumberOfMonths']));
								$nextrenewaldate2 = date('d.m.Y',mktime(0, 0, 0, date('m',$nextdate2), date('j',$nextdate2),  date('Y',$nextdate2)+$subscription['periodNumberOfMonths'])-24*60*60);
							}
							$modifier = 1;
							$modifierActivated = false;
							$decimalNumber = 2;
							$realStoppedDate = $subscription['stoppedDate'];
							$stoppedDate = strtotime("+". $subscription['periodNumberOfMonths']." months", strtotime($nextrenewaldatevalue));

							if($realStoppedDate != "0000-00-00" && $realStoppedDate != null) {
								if(strtotime($realStoppedDate) < $stoppedDate-24*60*60) {
									$earlier = new DateTime(date("Y-m-d", strtotime($nextrenewaldatevalue)));
									$later = new DateTime(date("Y-m-d", strtotime($realStoppedDate)));
									$laterTimestamp = $later->getTimestamp();
									$lastDayOfStoppedDate = date("t.m.Y", $laterTimestamp);
									$dayOfStoppedDate = date("d.m.Y", $laterTimestamp);

									$wholeMonths = 0;
									do {
										$earlier->add(new DateInterval("P1M"));
										$timestamp = $earlier->getTimestamp();
										$wholeMonths++;
									} while($timestamp < $laterTimestamp);

									$modifier = $wholeMonths;
									if($lastDayOfStoppedDate != $dayOfStoppedDate) {
										$stoppedDateMonthTotalDays = date("t", strtotime($realStoppedDate));
										$stoppedDateMonthFirstDay = date("01.m.Y", strtotime($realStoppedDate));

										if(strtotime($nextrenewaldatevalue) > strtotime($stoppedDateMonthFirstDay)){
											$earlier = new DateTime(date("Y-m-d", strtotime($nextrenewaldatevalue)));
										} else {
											$earlier = new DateTime(date("Y-m-d", strtotime($stoppedDateMonthFirstDay)));
										}
										$later = new DateTime(date("Y-m-d", strtotime($realStoppedDate)+24*60*60));
										$diff = $later->diff($earlier);
										$stoppedDateMonthDays = $diff->format("%a");

										$modifier = $modifier + number_format($stoppedDateMonthDays/$stoppedDateMonthTotalDays, 4, ".", "");
									}
									$modifierActivated = true;

									$nextrenewaldate = date('Y-m-d', strtotime($realStoppedDate));
									$nextrenewaldate2 = date('d.m.Y', strtotime($realStoppedDate));
									$decimalNumber = 4;
								}
							}
                            $s_sql = "SELECT * FROM subscriptionmulti_date_for_invoicing WHERE subscriptionmulti_id = ".$v_row['id']." ORDER BY id ASC";
                            $o_query = $o_main->db->query($s_sql);
                            $datesForInvoicing = ($o_query ? $o_query->result_array() : array());
                            $dateArray = array();
                            foreach($datesForInvoicing as $dateForInvoicing) {
                                $dateArray[] = date("d.m", strtotime($dateForInvoicing['date']));
                            }
                            $totalCollectNumber = count($dateArray);
                            foreach($dateArray as $key => $dateToCompare) {
                                if(date("d.m", strtotime($nextrenewaldatevalue)) == $dateToCompare){
                                    $collectNumber = $key+1;
                                }
                            }
                            if($v_row['subscription_category'] == 1){
                                $addYear = 0;
                                $collectIndex = $collectNumber;
                                if($collectIndex > count($dateArray)){
                                    $collectIndex = 0;
                                }
                                $nextrenewaldate2 = date('d.m.Y', strtotime($dateArray[$collectIndex].".".(date("Y", strtotime($nextrenewaldatevalue)))));
                                if(strtotime($nextrenewaldate2) < strtotime($nextrenewaldate)) {
                                    $nextrenewaldate2 = date('d.m.Y', strtotime("+1 year", strtotime($nextrenewaldate2)));
                                }
                            }
							if(!$subscription['activate_specified_invoicing']){
								$s_sql = "SELECT * FROM subscriptionline WHERE subscribtionId = ?";
								$totalTotal = 0;
								$sl_rows = array();
								$o_query = $o_main->db->query($s_sql, array($subscription['id']));
								if($o_query && $o_query->num_rows()>0){
									$sl_rows = $o_query->result_array();
								}
							} else {
								// Create order for each workline
								$s_sql = "SELECT rsi.*, ww.date, ww.estimatedTimeuse, rsi.id as specified_invoicing_id, ww.id as workplanlineworkerId FROM workplanlineworker ww
								LEFT OUTER JOIN repeatingorder_specified_invoicing_group rsig ON rsig.id = ww.specified_invoicing_id
								LEFT OUTER JOIN repeatingorder_specified_invoicing rsi ON rsi.repeatingorder_specified_invoicing_group_id = rsig.id
								WHERE ww.repeatingOrderId = ? AND ww.specified_invoicing_id > 0 AND ww.date >= ? AND ww.date <= ? AND (ww.absenceDueToIllness is null OR ww.absenceDueToIllness = 0) ORDER BY ww.date ASC";
								$o_query = $o_main->db->query($s_sql, array($v_row['id'], date("Y-m-d", strtotime($nextrenewaldatevalue)), date("Y-m-d", strtotime($nextrenewaldate2))));
								$workplanlines = $o_query ? $o_query->result_array() : array();
								// $sl_rows = array();
								// foreach($workplanlines as $temp_line){
								// 	if($temp_line['invoicingType'] == 0){
								// 		$temp_line['amount'] = $sl_rows[$temp_line['date']."-".$temp_line['id']]['amount'] + $temp_line['estimatedTimeuse'];
								// 	} else if($temp_line['invoicingType'] == 1){
								// 		$temp_line['amount'] = 1;
								// 	}
								//
								// 	$temp_line['specified_invoicing'] = true;
								// 	$temp_line['workDate'] = date("d.m.Y", strtotime($temp_line['date']));
								//
								// 	$sl_rows[$temp_line['date']."-".$temp_line['id']] = $temp_line;
								// }
								$sl_rows = array();
								$sl_rows_count = array();
								$datesAdded = array();
								foreach($workplanlines as $temp_line){
									$addLine = false;
									if($temp_line['invoicingType'] == 0){
										$temp_line['amount'] = $temp_line['estimatedTimeuse'];
									} else if($temp_line['invoicingType'] == 1){
										$temp_line['amount'] = 1;
									}
									$temp_line['specified_invoicing'] = true;
									$temp_line['workDate'] = date("d.m.Y", strtotime($temp_line['date']));
									if($temp_line['invoicingType'] == 1){
										if($batch_renewal_accountconfig['specifiedInvoicing_makeMultiWorkersIntoOneTime']){
											if(!isset($datesAdded[$temp_line['workDate']])) {
												$datesAdded[$temp_line['workDate']] = $temp_line;
												$addLine = true;
											}
										} else {
											$addLine = true;
										}
									} else {
										$addLine = true;
									}
									if($addLine) {
										// $sl_rows_prep[$temp_line['date']."-".$temp_line['id']] = $temp_line;
										$sl_rows_count[$temp_line['specified_invoicing_id']."_".$temp_line['workDate']][] = $temp_line;
									}
								}
								foreach($sl_rows_count as $specified_invoicing_id => $sl_rows_array) {
									foreach($sl_rows_array as $sl_rows_single){
										if($batch_renewal_accountconfig['specifiedInvoicing_linesToShow'] > 0 && count($sl_rows_count[$specified_invoicing_id]) > $batch_renewal_accountconfig['specifiedInvoicing_linesToShow']){
											if(!isset($sl_rows[$specified_invoicing_id])){
												$sl_rows_single['combined_specified'] = true;
												$sl_rows[$specified_invoicing_id] = $sl_rows_single;
												$sl_rows[$specified_invoicing_id]['start_date'] = $sl_rows_single['workDate'];
												$sl_rows[$specified_invoicing_id]['end_date'] = $sl_rows_single['workDate'];
											} else {
												$sl_rows[$specified_invoicing_id]['amount'] += $sl_rows_single['amount'];
												if(strtotime($sl_rows_single['workDate']) < strtotime($sl_rows[$specified_invoicing_id]['start_date'])) {
													$sl_rows[$specified_invoicing_id]['start_date'] = $sl_rows_single['workDate'];
												}
												if(strtotime($sl_rows_single['workDate']) > strtotime($sl_rows[$specified_invoicing_id]['end_date'])) {
													$sl_rows[$specified_invoicing_id]['end_date'] = $sl_rows_single['workDate'];
												}
											}
										} else {
											if(isset($sl_rows[$sl_rows_single['date']."-".$sl_rows_single['id']])){
												$sl_rows_single_new = $sl_rows[$sl_rows_single['date']."-".$sl_rows_single['id']];
											} else {
												$sl_rows_single_new = $sl_rows_single;
												$sl_rows_single_new['amount'] = 0;
											}
											$sl_rows_single_new['amount']+=$sl_rows_single['amount'];
											$sl_rows[$sl_rows_single['date']."-".$sl_rows_single['id']] = $sl_rows_single_new;
										}
									}
								}

								$s_sql = "SELECT ww.date, ww.estimatedTimeuse, p.name, p.middlename, p.lastname FROM workplanlineworker ww
								LEFT OUTER JOIN contactperson p ON p.id = ww.employeeId
								WHERE ww.repeatingOrderId = ? AND (ww.specified_invoicing_id = 0 OR ww.specified_invoicing_id is null) AND ww.date >= ? AND ww.date <= ? AND (ww.absenceDueToIllness is null OR ww.absenceDueToIllness = 0) ORDER BY ww.date ASC";
								$o_query = $o_main->db->query($s_sql, array($v_row['id'], date("Y-m-d", strtotime($nextrenewaldatevalue)), date("Y-m-d", strtotime($nextrenewaldate2))));
								$workplanlines = $o_query ? $o_query->result_array() : array();
								foreach($workplanlines as $temp_line){
									array_push($free_sl_rows, $temp_line);
								}

						   }

                            if($v_row['subscription_category'] == 2){
               					require(__DIR__."/../../../CrmPriceOverview/output/renewal_include.php");
               				}
                            if($v_row['script_for_generating_order'] != ""){
            					if(is_file(__DIR__ . "/../../../SubscriptionReportAdvanced/output/includes/scripts/".$v_row['script_for_generating_order']."/script.php")) {
            						require(__DIR__ . "/../../../SubscriptionReportAdvanced/output/includes/scripts/".$v_row['script_for_generating_order']."/script.php");
            					}
            				}
							foreach($sl_rows as $sl_row){

		                        $pricePerPiece = $sl_row['pricePerPiece'];
		                        if($sl_row['articleOrIndividualPrice']) {
		                            $s_sql = "SELECT * FROM article WHERE id = ?";
		                            $o_query = $o_main->db->query($s_sql, array($sl_row['articleNumber']));
		                            $article = $o_query ? $o_query->row_array() : array();
		                            $pricePerPiece = $article['price'];
									if($v_customer_accountconfig['use_articlename_when_use_articleprice']) {
										$sl_row['articleName'] = $article['name'];
									}
		                        }
								if(!$modifierActivated){
                                    if($subscription['subscription_category'] == 1){
                						$totalAmount = 1 * $line['amount'];
                					} else {
                						$totalAmount = $subscription['periodNumberOfMonths'] * $sl_row['amount'];
                					}
								} else {
									$totalAmount = $sl_row['amount']*$modifier;
								}
            					if($line['fromPriceList']){
            						if(intval($v_row['periodUnit']) == 0){
            							$pricePerPiece = round($pricePerPiece / 12 * $totalAmount, 2);
            							$totalAmount = 1;
            						}
            					}

								$totalRowPrice = number_format($totalAmount * $pricePerPiece * ((100-$sl_row['discountPercent'])/100), 2,".","");
								$totalTotal += $totalRowPrice;

								if($sl_row['specified_invoicing']) {
									if($sl_row['combined_specified']) {
										$date_string = " (".$sl_row['start_date']." - ".$sl_row['end_date'].")";
									} else {
										$date_string = " ".$sl_row['workDate'];
									}
								} else {
                                    if($v_row['subscription_category'] == 1){
                                        $collectString = "";
                                        if($totalCollectNumber > 1){
                                            $collectString = "(".$formText_PartInvoice_output." ".$collectNumber. " ".$formText_Of_output ." ".$totalCollectNumber.")";
                                        }
                                        $date_string = " ".$collectString;
                                    } else {
                                        $date_string = " (".$nextrenewaldatevalue." - ".$nextrenewaldate2.")";
                                    }
								}
								$subscriptionNameString = "";
								if($v_row['placeSubscriptionNameInInvoiceLine']) {
									$subscriptionNameString = $v_row['subscriptionName'] . " - ";
								}
								?>
								<tr>
									<td><?php echo $subscriptionNameString . $sl_row['articleName'].$date_string; ?></td>
									<td><?php echo number_format($totalAmount, $decimalNumber, ",", " "); ?></td>
									<td><span class="articleInfo"><?php if($sl_row['articleOrIndividualPrice']) { ?><i class="fas fa-info-circle" title="<?php echo $formText_UsingArticlePrice_output;?>"></i><?php } ?></span><?php echo $pricePerPiece; ?></td>
									<td><?php echo $sl_row['discountPercent']; ?>%</td>
									<td class="item-total text-right"><?php echo number_format($totalRowPrice, 2, ",", " "); ?></td>
								</tr>
								<?php } ?>
							<tr>
								<td width="40%"></td>
								<td width="8%" class="item-price"><?php //echo $l_price; ?></td>
								<td width="12%"><?php //echo $v_row['amount']; ?></td>
								<td width="8%"><?php //echo $v_row['discountPercent']; ?></td>
								<td width="8%" class="item-total text-right"><?php echo number_format($totalTotal, 2, ",", " "); ?></td>
							</tr>
							<?php
							if(count($free_sl_rows) > 0) {
								?>
								<tr>
									<td><b><?php echo $formText_WorkplanlinesMarkedAsFreeNoInvoicing_output;?></b></td>
									<td></td>
									<td></td>
									<td></td>
									<td class="item-total text-right"></td>
								</tr>
								<?php
								foreach($free_sl_rows as $sl_row) {
									$date_string = " ".date("d.m.Y", strtotime($sl_row['date']));
									$subscriptionNameString = $sl_row['name'] . " ".$sl_row['middlename']." ".$sl_row['lastname']." - ";
									?>
									<tr>
										<td><?php echo $subscriptionNameString.$date_string ; ?></td>
										<td><?php echo number_format($sl_row['estimatedTimeuse'], 2, ",", " "); ?></td>
										<td></td>
										<td></td>
										<td class="item-total text-right"></td>
									</tr>
								<?php }
							}?>
						</tbody>
						</table>
					</div>
				</div>
				<div class="popupRow popupformbtn">
					<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
					<?php if($ownerCompanyData && !$projectCodeError && $noError){ ?>
					<input type="submit" name="sbmbtn" value="<?php echo $formText_Renew_Output; ?>">
					<?php } ?>
				</div>
			</div>
		</form>
	<?php } ?>
</div>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
	<?php
	if($modifierActivated){
		?>
		$(".initialLine").after('<div class="alert alert-warning"> <ul class="errorList"> <?php echo "<li>".$formText_NotFullOrder_output."</li>"; ?> </ul> </div>');
		<?php
	}
	?>
	$("form.output-form").validate({
        submitHandler: function(form) {
            fw_loading_start();
            $.ajax({
                url: $(form).attr("action"),
                cache: false,
                type: "POST",
                dataType: "json",
                data: $(form).serialize(),
                success: function (data) {
                    fw_loading_end();
                    if(data.redirect_url !== undefined)
                    {
                        out_popup.addClass("close-reload").data("redirect", data.redirect_url);
                        out_popup.close();
                    } else {
		                $("#popup-validate-message").html(data.error, true);
		                $("#popup-validate-message").show();
		                $('#popupeditbox').css('height', "auto");

                    }
                }
            }).fail(function() {
                $("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                $("#popup-validate-message").show();
                $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
                fw_loading_end();
            });
        },
        invalidHandler: function(event, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var message = errors == 1
                ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                $("#popup-validate-message").html(message);
                $("#popup-validate-message").show();
                $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
            } else {
                $("#popup-validate-message").hide();
            }
            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
        }
    });
</script>
<style>
	.popupform .articleInfo {
		width: 12px;
		display: inline-block;
		margin-right: 5px;
	}
	.popupform .articleInfo i {
		color: #8b8b8b;
		cursor: help;
	}
	.popupform, .popupeditform {
		border: 0;
	}
	.titleError {
		color: red;
	}
	.popupform {
		font-size: 13px;
	}
	.popupform .popupTitle {
		font-size: 16px;
	}
	.popupform .popupRow {
		padding: 10px 0px;
	}
	.popupformbtn {
		text-align:right;
		margin:10px;
	}
	.popupformbtn input {
		border-radius:4px;
		border:1px solid #0393ff;
		background-color:#0393ff;
		font-size:13px;
		line-height:0px;
		padding: 20px 35px;
		font-weight:700;
		color:#FFF;
		margin-left:10px;
	}
</style>
