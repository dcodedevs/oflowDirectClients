<?php
$itemCount = get_collecting_company_case_count2($o_main, $cid, $mainlist_filter, $filters);
if(isset($_POST['page'])) {
	$page = $_POST['page'];
}
if(intval($page) == 0){
	$page = 1;
}
$perPage = 1000;
$showing = $page * $perPage;
$showMore = false;
$currentCount = $itemCount;

if($showing < $currentCount){
	$showMore = true;
}
$totalPages = ceil($currentCount/$perPage);
$customerListNonProcessed = get_collecting_company_case_list($o_main, $cid, $list_filter, $filters, $page, $perPage);

$customerList = array();
foreach($customerListNonProcessed as $v_row) {
	$mainClaim = 0;


	$s_sql = "SELECT cccl.* FROM collecting_company_cases_claim_lines cccl
	LEFT OUTER JOIN collecting_cases_claim_line_type_basisconfig bconfig ON bconfig.id = cccl.claim_type
	WHERE cccl.content_status < 2 AND cccl.collecting_company_case_id = ? AND IFNULL(bconfig.not_include_in_claim, 0) = 0
	ORDER BY cccl.claim_type ASC, cccl.created DESC";
	$o_query = $o_main->db->query($s_sql, array($v_row['id']));
	$claims = ($o_query ? $o_query->result_array() : array());

	foreach($claims as $claim) {
		if($claim['claim_type'] == 1){
			$mainClaim += $claim['amount'];
		}
	}
	$s_sql = "SELECT * FROM collecting_cases_payments WHERE collecting_case_id = ? ORDER BY created DESC";
	$o_query = $o_main->db->query($s_sql, array($v_row['id']));
	$payments = ($o_query ? $o_query->result_array() : array());

	$s_sql = "SELECT * FROM customer WHERE id = ? AND creditor_id = ?";
	$o_query = $o_main->db->query($s_sql, array($v_row['debitor_id'], $v_row['creditor_id']));
	$debitorCustomer = $o_query ? $o_query->row_array() : array();
	$v_row['debitorCustomer'] = $debitorCustomer;
	$v_row['claims'] = $claims;
	$v_row['payments'] = $payments;
	$customerList[] = $v_row;
}
?>
<div class="resultTableWrapper">
	<?php if($groupedbyDebitor['debitor_id'] > 0) { ?>
		<div class="debitorNameLabel"><?php echo $groupedbyDebitor['debitorName'];?></div>
	<?php } ?>

	<form class="checkCaseToProcessForm">
		<input type="hidden" value="<?php echo $cid?>" name="creditor_id" autocomplete="off"/>
		<table class="gtable" id="gtable_search">
			<tr class="gtable_row">
				<?php if($list_filter == "canSendNow"){ ?>
					<th class="gtable_cell gtable_cell_head checkboxColumn" width="30px"><label for="checkAll"><?php echo $formText_CheckAll_output;?></label><br/><input type="checkbox" value="1" class="checkAll" id="checkAll" autocomplete="off"/></th>
				<?php } ?>
				<th width="150" class="gtable_cell gtable_cell_head orderBy <?php if($order_field == "debitor") echo 'orderActive';?>" data-orderfield="debitor" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
					<?php echo $formText_Debitor_output;?>
					<div class="ordering">
						<div class="fas fa-caret-up" <?php if($order_field == "debitor" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
						<div class="fas fa-caret-down" <?php if($order_field == "debitor" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
					</div>
				</th>
				<th width="40" class="gtable_cell gtable_cell_head"></th>
				<th width="70" class="gtable_cell gtable_cell_head orderBy <?php if($order_field == "invoice_no") echo 'orderActive';?>" data-orderfield="invoice_no" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
					<?php echo $formText_CaseCreatedDate_output;?><br/><?php echo $formText_CaseStartedDate_output;?><br/><?php echo $formText_DueDate_output;?>
					<div class="ordering">
						<div class="fas fa-caret-up" <?php if($order_field == "invoice_no" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
						<div class="fas fa-caret-down" <?php if($order_field == "invoice_no" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
					</div>
				</th>
				<th class="gtable_cell gtable_cell_head"><?php echo $formText_CollectingLevel_output;?></th>
				<th class="gtable_cell gtable_cell_head rightAlign" style="width: 100px;"><?php echo $formText_MainClaim_output;?></th>
				<th class="gtable_cell gtable_cell_head rightAlign" style="width: 100px;"><?php echo $formText_Balance_output;?></th>

				<th class="gtable_cell gtable_cell_head" style="width: 150px;">
					<?php
					if($list_filter == "canSendReminderNow") {
						echo $formText_WillBeSentNow_output;
					} else {
						echo $formText_NextStep_output;
					}
					?>

				</th>
				<th class="gtable_cell gtable_cell_head" width="200px"><?php echo $formText_History_output;?></th>
			</tr>

			<?php
			$collectingLevelArray = array(0=>$formText_NoUpdate_output, 1=>$formText_Reminder_output, 2=>$formText_DebtCollectionWarning_output, 3=>$formText_PaymentEncouragement_output,4=>$formText_HeavyFeeWarning_output, 5=>$formText_LastWarningBeforeLegalAction_output, 6=>$formText_LegalAction_output);

			$action_text = array(1=>$formText_SendLetter_output, 2=>$formText_SendEmail_output);
			$action_text_icons = array(1=>'<i class="fas fa-file"></i>', 2=>'<i class="fas fa-at"></i>');

			foreach($customerList as $v_row) {
				$debitorCustomer = $v_row['debitorCustomer'];
				$customer_reminder_profile = $debitorCustomer['creditor_reminder_profile_id'];
				$customer_move_to_collecting = $debitorCustomer['choose_move_to_collecting_process'];
				$customer_progress_of_reminder_process = $debitorCustomer['choose_progress_of_reminderprocess'];
				$mainClaim = $v_row['original_main_claim'];
				$claims = $v_row['claims'];
				$payments = $v_row['payments'];


				$case_progress_of_reminder_process = $v_row['choose_progress_of_reminderprocess'];
				if($case_progress_of_reminder_process == 0){
					if($customer_progress_of_reminder_process == 0){
						$case_progress_of_reminder_process = $creditor_progress_of_reminder_process;
					} else {
						$case_progress_of_reminder_process = $customer_progress_of_reminder_process - 1;
						if($case_progress_of_reminder_process == 2) {
							$notSendInfo = $formText_CustomerMarkedNotSendReminders_output;
						}
					}
				} else {
					$case_progress_of_reminder_process--;
					if($case_progress_of_reminder_process == 2) {
						$notSendInfo = $formText_CaseMarkedNotSendReminders_output;
					}
				}
				$balance = 0;

				foreach($claims as $claim) {
					$balance += $claim['amount'];
				}
				foreach($payments as $payment) {
					$balance -= $payment['amount'];
				}
				?>
				<tr class="gtable_row">

					<?php if($list_filter == "canSendNow"){ ?>
						<td class="gtable_cell">
							<input type="checkbox" value="<?php echo $v_row['id']?>" name="checkCaseToProcess[]" data-action_type="<?php echo $actionType;?>" id='checkCaseToProcess<?php echo $v_row['id']?>' autocomplete="off" class="checkCaseToProcess"/>
						</td>
					<?php }?>
					<td class="gtable_cell">
						<?php echo $v_row['debitorName']; ?>
						<?php if($notSendInfo != "") { ?>
							<div class="notSendInfo"><?php echo $notSendInfo;?></div>
						<?php } ?>
					</td><td class="gtable_cell">
					<div class="hoverEye customerHoverEye">
						<span class="glyphicon glyphicon-menu-hamburger"></span>
						<div class="hoverInfo hoverInfoFull hoverInfoLeft">
							<?php
							echo "<b>".$formText_SettingsForThisCustomer_output."</b><br/>";
							echo $formText_ChooseReminderProfile_output.": ";
							$customer_type_collect_debitor = $debitorCustomer['customer_type_collect'];
							if($debitorCustomer['customer_type_collect_addition'] > 0){
								$customer_type_collect_debitor = $debitorCustomer['customer_type_collect_addition'] - 1;
							}
							if($customer_type_collect_debitor== 1){
								$default_reminder_profile = $creditor_profile_for_person;
							} else {
								$default_reminder_profile = $creditor_profile_for_company;
							}

							if($transaction['reminder_profile_id'] == 0) {
								echo $formText_Default_output." ";
								foreach($creditor_profiles as $creditor_profile) {
									if($default_reminder_profile == $creditor_profile['id']){
										echo "(".$creditor_profile['name'].")";
									}
								}
							} else {
								foreach($creditor_profiles as $creditor_profile) {
									if($creditor_profile['id'] == $transaction['reminder_profile_id']) {
										echo $creditor_profile['name'];
									}
								}
							}

							echo "</br>".$formText_ChooseProgressOfReminderProcess_output.": ";

							$default_progress_of_reminderprocess = $creditor_progress_of_reminder_process;
							switch($customer_progress_of_reminder_process) {
								case 0:
									echo $formText_Default_output." ";
									switch($default_progress_of_reminderprocess) {
										case 0:
											echo "(".$formText_Manual_output.")";
										break;
										case 1:
											echo "(".$formText_Automatic_output.")";
										break;
										case 2:
											echo "(".$formText_DoNotSent_output.")";
										break;
									}
								break;
								case 1:
									echo $formText_Manual_output;
								break;
								case 2:
									echo $formText_Automatic_output;
								break;
								case 3:
									echo $formText_DoNotSend_output;
								break;
							}
							echo "</br>".$formText_ChooseMoveToCollectingProcess_output.": ";

							$default_move_to_collecting = $creditor_move_to_collecting;
							switch($customer_move_to_collecting) {
								case 0:
									echo $formText_Default_output." ";
									switch($default_move_to_collecting) {
										case 0:
											echo "(".$formText_Manual_output.")";
										break;
										case 1:
											echo "(".$formText_Automatic_output.")";
										break;
										case 2:
											echo "(".$formText_DoNotSent_output.")";
										break;
									}
								break;
								case 1:
									echo $formText_Manual_output;
								break;
								case 2:
									echo $formText_Automatic_output;
								break;
								case 3:
									echo $formText_DoNotSend_output;
								break;
							}
							?><div class="glyphicon glyphicon-pencil edit_customer_settings" data-customer-id="<?php echo $debitorCustomer['id'];?>" data-transaction-id="<?php echo $v_row['internalTransactionId'];?>"></div>
						</div>
					</div>
					 <?php
					$customer_type_collect = $v_row['customer_type_collect'];
					if($v_row['customer_type_collect_addition'] > 0) {
					   $customer_type_collect = $v_row['customer_type_collect_addition'] - 1;
					}
					if($customer_type_collect == 1) {
						echo '<span class="change_customer_type personal_customer" data-customer-type="'.$customer_type_collect.'" data-customer-id="'.$debitorCustomer['id'].'" data-creditor-id="'.$v_row['creditor_Id'].'"><span class="hoverEye">P<span class="hoverInfo hoverInfoAuto hoverInfoLeft">'.$formText_PrivatePerson_output.'</span></span></span>';
					} else {
						echo '<span class="change_customer_type business_customer" data-customer-type="'.$customer_type_collect.'" data-customer-id="'.$debitorCustomer['id'].'" data-creditor-id="'.$v_row['creditor_Id'].'"><span class="hoverEye">B<span class="hoverInfo hoverInfoAuto hoverInfoLeft">'.$formText_Business_output.'</span></span></span>';
					}
					 ?></td>

					 <td class="gtable_cell">
						 <?php
						 if($list_filter == "warning"){
							 echo date("d.m.Y", strtotime($v_row['warning_case_created_date']));?>
							 <?php
							 if($v_row['warning_case_started_date'] != null) {
								 echo "<br/>".date("d.m.Y", strtotime($v_row['warning_case_started_date']));
							 }
						 } else if($list_filter == "collecting"){
							echo date("d.m.Y", strtotime($v_row['collecting_case_created_date']));?>
							<?php
							if($v_row['collecting_case_autoprocess_date'] != null) {
								echo "<br/>".date("d.m.Y", strtotime($v_row['collecting_case_autoprocess_date']));
							}
						 }
						 if($v_row['due_date'] != null) {
							 echo "<br/>".date("d.m.Y", strtotime($v_row['due_date']));
						 }
						 ?>
					 </td>
					<td class="gtable_cell">
					   <?php if($v_row['currentStepDate'] != "0000-00-00" && $v_row['currentStepDate'] != "") echo date("d.m.Y", strtotime($v_row['currentStepDate']));?></br>
					   <?php echo $v_row['processStepName'];?>
					</td>
					<td class="gtable_cell rightAlign"><?php
					echo number_format($mainClaim, 2, ",", " ");
					?>
					</td>
					<td class="gtable_cell rightAlign"><?php
					echo number_format($balance, 2, ",", " ");

					?>
					<span class="glyphicon glyphicon-info-sign hoverEye">
						<div class="hoverInfo hoverInfoFull hoverInfoBig">
							<table class="table smallTable">
								<?php
								if(count($claims) > 0){ ?>
									<?php
									foreach($claims as $claim) {
										?>
										<tr>
											<td><b><?php echo $claim['name'];?></b></td>
											<td><?php if($claim['date'] != "0000-00-00" && $claim['date'] != "") echo date("d.m.Y", strtotime($claim['date']));?></td>
											<td><?php echo number_format($claim['amount'], 2, ",", " ");?></td>
										</tr>
										<?php
									}
								}
								if(count($payments) > 0){
								?>
									<?php
									foreach($payments as $payment) {
										?>
										<tr>
											<td><b><?php echo $formText_Payment_output;?></b></td>
											<td><?php if($payment['date'] != "0000-00-00" && $payment['date'] != "") echo date("d.m.Y", strtotime($payment['date']));?></td>
											<td><?php echo number_format($payment['amount'], 2, ",", " ");?></td>
										</tr>
										<?php
									}
								}
								?>
								<tr class="balance_row">
									<td><b><?php echo $formText_Balance_output;?></b></td>
									<td></td>
									<td><?php echo number_format($balance, 2, ",", " ");?></td>
								</tr>
							</table>

							<?php if(count($transaction_fees) > 0) { ?>
								<div class="resetTheCase" data-caseid="<?php echo $v_row['id'];?>"><?php echo $formText_ResetFees_output;?></div>
							<?php } ?>
							<?php
							if($v_row['id'] != null){
								if(count($transaction_payments) > 0) {
									?>
									<div class="createRestNote" data-caseid="<?php echo $v_row['id'];?>"><?php echo $formText_CreateRestNote_output;?></div>
									<?php
								}
							}
							?>
						</div>
					</span>
					</td>
					<td class="gtable_cell break">
						<?php
						if($v_row['id'] == null) {
							if($list_filter != "canSendReminderNow") {
								if($v_row['nextStepDate'] != "") echo date("d.m.Y", strtotime($v_row['nextStepDate']))."<br/>";
							}
							?>
							<?php echo $v_row['nextStepName'];
							?>
							<div class="action_icon_wrapper">
								<?php
								if($v_row['nextStepActionType'] == 2){
									if($v_row['invoiceEmail'] != ""){
										echo $action_text[2];

										echo ": <span class='email_wrapper email_wrapper_text'>".$v_row['invoiceEmail'] ."</span>";
									} else {
										echo $action_text[1];
									}
								} else {
									echo $action_text[intval($v_row['nextStepActionType'])];
								} ?>
							</div>
						<?php } else {
							if(intval($v_row['nextStepId']) > 0) {

							if($list_filter != "canSendReminderNow") {
								if($v_row['nextStepDate'] != "") echo date("d.m.Y", strtotime($v_row['nextStepDate']))."<br/>";
							}
							?>
								<?php echo $v_row['nextStepName'];
							   ?>
							   <div class="action_icon_wrapper">
								   <?php
								   if($v_row['nextStepActionType'] == 2){
									   if($v_row['invoiceEmail'] != ""){
										   echo $action_text[2];

										   echo ": <span class='email_wrapper email_wrapper_text'>".$v_row['invoiceEmail'] ."</span>";
									   } else {
										   echo $action_text[1];
									   }
								   } else {
									   echo $action_text[intval($v_row['nextStepActionType'])];
								   } ?>
							   </div>
						<?php } else {
								echo $formText_FinalStep_output.": ";
								foreach($steps as $step) {
									if($v_row['collecting_cases_process_step_id'] == $step['id']) echo '<span class="editCaseStep" data-case-id="'.$v_row['id'].'">'.$step['name']."</span>";
								}
							}
						} ?>
						<?php /* if($list_filter != "due_date_not_expired") { ?>
							<?php if($list_filter == "due_date_expired_manual") { ?>
								<div class="processToNext" data-case-id="<?php echo $v_row['id'];?>" data-process-id="<?php echo $process['process_id'];?>"><?php echo $formText_ProcessToNextStep_output;?></div>
							<?php } ?>
						<?php } */ ?>
						<div class="clear"></div>
					</td>
					<td class="gtable_cell">
						<?php
						$history_array = array();

						if(count($letters) > 0) {
							foreach($letters as $v_claim_letter){
								$v_claim_letter_array = array('created'=> date("Y-m-d", strtotime($v_claim_letter['created'])), 'id'=>$v_claim_letter['id'], 'pdf'=>$v_claim_letter['pdf'], 'action_type'=>$v_claim_letter['performed_action'], 'step_name'=>$v_claim_letter['step_name'], 'claim_letter'=>1, 'rest_note'=>$v_claim_letter['rest_note'], 'performed_date'=>$v_claim_letter['performed_date']);
								$history_array[] = $v_claim_letter_array;
							}
						}

						if(count($objections) > 0) {
							foreach($objections as $objection) {
								$v_claim_letter_array = array('created'=> date("Y-m-d", strtotime($objection['created'])), 'claim_letter'=>0, 'objection_opened'=>1, 'message_from_debitor'=> $objection['message_from_debitor']);
								$history_array[] = $v_claim_letter_array;
								if($objection['objection_closed_date'] != "0000-00-00" && $objection['objection_closed_date'] != null){
									$v_claim_letter_array = array('created'=> date("Y-m-d", strtotime($objection['objection_closed_date'])), 'claim_letter'=>0, 'objection_closed'=>1, 'objection_closed_handling_description'=> $objection['objection_closed_handling_description']);
									$history_array[] = $v_claim_letter_array;
								}
							}
						}

						usort($history_array, 'sortByCreated');
						foreach($history_array as $v_claim_letter_item){
							if($v_claim_letter_item['claim_letter']){
								?>
								<div>
									<?php
									echo date("d.m.Y", strtotime($v_claim_letter_item['created']));
									?>
									<?php if($v_claim_letter_item['pdf'] != "") {
										if(strpos($v_claim_letter_item['pdf'],'uploads/protected/')!==false)
										{
											$fileAddition = "";
											$fileParts = explode('/',$v_claim_letter_item['pdf']);
											$fileName = array_pop($fileParts);
											$fileParts[] = rawurlencode($fileName);
											$filePath = implode('/',$fileParts);

											if($v_accountinfo['cus_portal_crm_account_url'] != ""){
												$hash = md5($v_accountinfo['cus_portal_crm_account_url'] . '-' . $v_claim_letter_item['id']);
												$fileNameApi = "";
												foreach($fileParts as $filePart) {
													if($filePart != "uploads" && $filePart != "protected"){
														$fileNameApi .= $filePart."/";
													}
												}
												$fileNameApi = trim($fileNameApi, "/");
												$fileAddition = "&externalApiAccount=".$v_accountinfo['cus_portal_crm_account_url']."&externalApiHash=".$hash."&file=".$fileNameApi;
										   }
										 ?>
										<a href="<?php echo $extradomaindirroot."/".$v_claim_letter_item['pdf']."?caID=".$_GET['caID']."&table=collecting_cases_claim_letter&field=pdf&ID=".$v_claim_letter_item['id'].$fileAddition;?>" download><?php if($v_claim_letter_item['rest_note']){ echo $formText_RestNote_output; }else { echo $v_claim_letter_item['step_name']; }?></a>

									<?php
										} else { ?>
										<a href="<?php echo $v_accountinfo['cus_portal_crm_account_url']."/".$v_claim_letter_item['pdf'];?>" download><?php echo $v_claim_letter_item['step_name'];?></a>
									<?php }
									} ?>
									<span class="action_icon_wrapper">
										<?php
										if($v_claim_letter_item['action_type'] == 2){
											if($v_row['invoiceEmail'] != ""){
												echo $action_text_icons[2];
											} else {
												echo $action_text_icons[1];
											}
										} else {
											echo $action_text_icons[intval($v_claim_letter_item['action_type'])];
										}
										?>
									</span>
								</div>
								<?php
							} else if($v_claim_letter_item['objection_opened']){
								?>
								<div class="">
									<?php echo date("d.m.Y", strtotime($v_claim_letter_item['created']))?> - <?php echo $formText_ObjectionStarted_output;?>
									<?php if($v_claim_letter_item['message_from_debitor'] != "") { ?>
										<span class="glyphicon glyphicon-info-sign hoverEye">
											<div class="hoverInfo hoverInfo2">
												<?php echo $v_claim_letter_item['message_from_debitor']?>
											</div>
										</span>
									<?php } ?>
								</div>
								<?php
							} else if($v_claim_letter_item['objection_closed']){
								?>
								<div class="">
									<?php echo date("d.m.Y", strtotime($v_claim_letter_item['created']))?> - <?php echo $formText_ObjectionClosed_output;?>
									<?php if($v_claim_letter_item['objection_closed_handling_description'] != "") { ?>
										<span class="glyphicon glyphicon-info-sign hoverEye">
											<div class="hoverInfo hoverInfo2">
												<?php echo $v_claim_letter_item['objection_closed_handling_description']?>
											</div>
										</span>
									<?php } ?>
								</div>
								<?php
							}
						}
						?>
					</td>
				</tr>
				<?php
			}
			?>
		</table>
	</form>
</div>
