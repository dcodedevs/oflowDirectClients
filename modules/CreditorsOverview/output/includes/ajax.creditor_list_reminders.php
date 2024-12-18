<?php
// var_dump(time());
$itemCount = get_transaction_count2($o_main, $cid, $mainlist_filter, $filters);
if(isset($_POST['page'])) {
	$page = $_POST['page'];
}
if(intval($page) == 0){
	$page = 1;
}
$perPage = 200;
$showing = $page * $perPage;
$showMore = false;
$currentCount = $itemCount;

if($showing < $currentCount){
	$showMore = true;
}
$totalPages = ceil($currentCount/$perPage);
// var_dump(time());
$customerListNonProcessed = get_transaction_list($o_main, $cid, $mainlist_filter, $filters, $page, $perPage);
// var_dump(time());

$collectingcase_ids = array();
foreach($customerListNonProcessed as $v_row) {
	if($v_row['id'] > 0){
		$collectingcase_ids[] = $v_row['id'];
	}
}
$s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND collectingcase_id IN (".implode(',', $collectingcase_ids).") ORDER BY created DESC";
$o_query = $o_main->db->query($s_sql);
$all_invoices = ($o_query ? $o_query->result_array() : array());

$s_sql = "SELECT cccl.* FROM collecting_cases_claim_letter cccl
WHERE cccl.content_status < 2 AND cccl.case_id IN (".implode(',', $collectingcase_ids).")  ORDER BY cccl.created DESC";
$o_query = $o_main->db->query($s_sql);
$all_claim_letters = ($o_query ? $o_query->result_array() : array());

$s_sql = "SELECT * FROM collecting_cases_objection WHERE collecting_case_id IN (".implode(',', $collectingcase_ids).") ORDER BY created DESC";
$o_query = $o_main->db->query($s_sql);
$all_objections = ($o_query ? $o_query->result_array() : array());

$s_sql = "SELECT * FROM collecting_cases_process ORDER BY sortnr ASC";
$o_query = $o_main->db->query($s_sql);
$all_processes_un = ($o_query ? $o_query->row_array() : array());
$all_processes = array();
foreach($all_processes_un as $all_process){
	$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_steps.collecting_cases_process_id = ? ORDER BY sortnr ASC";
	$o_query = $o_main->db->query($s_sql, array($all_process['id']));
	$old_steps = ($o_query ? $o_query->result_array() : array());
	$all_process['steps'] = $old_steps;
	$all_processes[] = $all_process;
}

$s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') AND creditor_id = ?";
$o_query = $o_main->db->query($s_sql, array($cid));
$all_transaction_payments = ($o_query ? $o_query->result_array() : array());

$s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND (comment LIKE '%reminderFee_%' OR comment LIKE '%interest_%') ORDER BY created DESC";
$o_query = $o_main->db->query($s_sql, array($cid));
$all_transaction_fees = ($o_query ? $o_query->result_array() : array());

$s_sql = "SELECT * FROM customer WHERE creditor_id = ?";
$o_query = $o_main->db->query($s_sql, array($cid));
$all_debitorCustomers = $o_query ? $o_query->result_array() : array();

$s_sql = "SELECT * FROM creditor_transactions WHERE creditor_id = ? AND collectingcase_id > 0";
$o_query = $o_main->db->query($s_sql, array($cid));
$all_transactions = $o_query ? $o_query->result_array() : array();


$s_sql = "SELECT crcp.*, ccp.fee_level_name, pst.name as stepTypeName, CONCAT_WS(' ', ccp.fee_level_name, pst.name) as name
FROM creditor_reminder_custom_profiles crcp
LEFT JOIN collecting_cases_process ccp ON ccp.id = crcp.reminder_process_id
LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id
WHERE crcp.creditor_id = ? AND crcp.content_status < 2";
$o_query = $o_main->db->query($s_sql, array($cid));
$creditor_reminder_custom_profiles = ($o_query ? $o_query->result_array() : array());

$s_sql = "SELECT * FROM creditor_transactions WHERE collectingcase_id > 0 AND creditor_id = ? AND open = 1 ORDER BY created DESC";
$o_query = $o_main->db->query($s_sql, array($cid));
$all_casesOnReminder = ($o_query ? $o_query->result_array() : array());

$s_sql = "SELECT collecting_company_cases.* FROM collecting_company_cases
LEFT OUTER JOIN customer c ON c.id = collecting_company_cases.debitor_id
WHERE collecting_company_cases.creditor_id = ? AND collecting_company_cases.case_closed_date = '0000-00-00 00:00:00'";
$o_query = $o_main->db->query($s_sql, array($v_row['creditorCreditorId']));
$all_casesOnCollecting = ($o_query ? $o_query->result_array() : array());

$customerList = array();
foreach($customerListNonProcessed as $v_row) {
	$totalSumOriginalClaim = 0;
	$invoices = array();
	foreach($all_invoices as $all_invoice){
		if($all_invoice['collectingcase_id'] == $v_row['id']){
			$invoices[] = $all_invoice;
		}
	}
	foreach($invoices as $invoice) {
		$totalSumOriginalClaim += $invoice['collecting_case_original_claim'];
	}
	$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($v_row['collecting_cases_process_step_id']));
	$process_step = ($o_query ? $o_query->row_array() : array());
	$collectingLevelName = $process_step['name'];

	$v_row['collectingLevelName'] = $collectingLevelName;
	$v_row['totalSumOriginalClaim'] = $totalSumOriginalClaim;
	$v_row['invoices'] = $invoices;

	$v_claim_letters = array();
	foreach($all_claim_letters as $all_claim_letter) {
		if($all_claim_letter['case_id'] == $v_row['id']){
			$v_claim_letters[] = $all_claim_letter;
		}
	}
	$v_row['letters'] = $v_claim_letters;
	$process = array();
	foreach($all_processes as $all_process){
		if($all_process['id'] == $v_row['reminder_process_id']){
			$process = $all_process;

		}
	}
	$steps = $process['steps'];

	$next_step = array();
	$stepTrigger = false;
	$currentStep = array();
	foreach($steps as $step) {
		if(!$next_step){
			$next_step = $step;
		}
		if($stepTrigger){
			$next_step = $step;
			$stepTrigger = false;
		}
		if($step['id'] == $v_row['collecting_cases_process_step_id']) {
			$currentStep = $step;
			$stepTrigger = true;
		}
	}

	$v_row['steps'] = $steps;
	$v_row['next_step'] = $next_step;

	$objections = array();
	foreach($all_objections as $all_objection) {
		if($all_objection['collecting_case_id'] == $v_row['id']) {
			$objections[] = $all_objection;
		}
	}
	$v_row['objections'] = $objections;

	$connected_transactions = array();
	$all_connected_transaction_ids = array($v_row['internalTransactionId']);
	if($v_row['link_id'] > 0 && ($creditor['checkbox_1'])) {
		$s_sql = "SELECT * FROM creditor_transactions ct WHERE ct.link_id = ? AND (ct.open = 1) AND (ct.system_type='InvoiceCustomer' OR ct.system_type = 'CreditnoteCustomer') AND ct.id <> ?";
		$o_query = $o_main->db->query($s_sql, array($v_row['link_id'], $v_row['internalTransactionId']));
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
	$v_row['connected_transactions'] = $connected_transactions;

	$transaction_payments = array();
	foreach($all_transaction_payments as $all_transaction_payment) {
		if($v_row['link_id'] > 0 && $all_transaction_payment['link_id'] == $v_row['link_id']){
			if(!in_array($all_transaction_payment['id'], $all_connected_transaction_ids)){
				$transaction_payments[] = $all_transaction_payment;
			}
		}
	}
	$total_transaction_payments = $transaction_payments;

	$transaction_fees = array();
	foreach($all_transaction_fees as $all_transaction_fee) {
		if($v_row['link_id'] > 0 && $all_transaction_fee['link_id'] == $v_row['link_id']){
			$transaction_fees[] = $all_transaction_fee;
		}
	}

	$v_row['transaction_fees'] = $transaction_fees;
	$v_row['transaction_payments'] = $total_transaction_payments;
	$casesOnReminderCount = 0;
	foreach($all_casesOnReminder as $caseOnreminder){
		if($caseOnreminder['external_customer_id'] == $v_row['external_customer_id']){
			$casesOnReminderCount++;
		}
	}
	$casesOnCollectingCount = 0;
	foreach($all_casesOnCollecting as $caseOnCollecting){
		if($caseOnCollecting['creditor_customer_id'] == $v_row['external_customer_id']){
			$casesOnCollectingCount++;
		}
	}

	$debitorCustomer = array();
	foreach($all_debitorCustomers as $all_debitorCustomer){
		if($all_debitorCustomer['creditor_customer_id'] == $v_row['external_customer_id']){
			$debitorCustomer = $all_debitorCustomer;
		}
	}
	$v_row['debitorCustomer'] = $debitorCustomer;

	$transaction = array();
	foreach($all_transactions as $all_transaction){
		if($all_transaction['id'] == $v_row['internalTransactionId']){
			$transaction = $all_transaction;
		}
	}
	$v_row['transaction'] = $transaction;

	$v_row['creditor_profiles'] = $creditor_reminder_custom_profiles;

	$v_row['casesOnReminderCount'] = $casesOnReminderCount;
	$v_row['casesOnCollectingCount'] = $casesOnCollectingCount;
	array_push($customerList, $v_row);
}
// var_dump(time());
?>

<div class="resultTableWrapper">
	<?php if($groupedbyDebitor['debitor_id'] > 0) { ?>
		<div class="debitorNameLabel"><?php echo $groupedbyDebitor['debitorName'];?></div>
	<?php } ?>

<form class="checkCaseToProcessForm">
	<input type="hidden" value="<?php echo $cid?>" name="creditor_id" autocomplete="off"/>
<table class="gtable" id="gtable_search">
	<tr class="gtable_row">
		<?php
		if($mainlist_filter == "reminderLevel" || $mainlist_filter == "collectingLevel"){ ?>
			<?php if(($selectedCustomer['choose_progress_of_reminderprocess'] == 0 && ($list_filter == "canSendReminderNow" || $list_filter == "notPayedConsiderCollectingProcess")) || ($list_filter == "activeOnCollectingLevel" && $selectedCustomer['choose_how_to_create_collectingcase'] == 0)){ ?>
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
				<?php echo $formText_InvoiceNo_output;?><br/><?php echo $formText_InvoiceDate_output;?><br/><?php echo $formText_DueDate_output;?>
				<div class="ordering">
					<div class="fas fa-caret-up" <?php if($order_field == "invoice_no" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
					<div class="fas fa-caret-down" <?php if($order_field == "invoice_no" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
				</div>
			</th>
			<?php if($mainlist_filter == "collectingLevel") { ?>
				<th class="gtable_cell gtable_cell_head"><?php echo $formText_CollectingLevel_output;?></th>
			<?php } ?>
			<th class="gtable_cell gtable_cell_head rightAlign" style="width: 100px;"><?php echo $formText_MainClaim_output;?></th>
			<th class="gtable_cell gtable_cell_head rightAlign" style="width: 100px;"><?php echo $formText_Balance_output;?></th>

			<?php if($mainlist_filter == "reminderLevel") { ?>
				<th class="gtable_cell gtable_cell_head" style="width: 70px;"><?php echo $formText_CollectingCaseDueDate_output;?></th>
			<?php } ?>
			<th width="30px" class="gtable_cell gtable_cell_head"></th>
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
		<?php
		}
		?>
	</tr>
<?php
$collectingLevelArray = array(0=>$formText_NoUpdate_output, 1=>$formText_Reminder_output, 2=>$formText_DebtCollectionWarning_output, 3=>$formText_PaymentEncouragement_output,4=>$formText_HeavyFeeWarning_output, 5=>$formText_LastWarningBeforeLegalAction_output, 6=>$formText_LegalAction_output);

$action_text = array(1=>$formText_SendLetter_output, 2=>$formText_SendEmail_output);
$action_text_icons = array(1=>'<i class="fas fa-file"></i>', 2=>'<i class="fas fa-at"></i>');

foreach($customerList as $v_row) {
	$notSendInfo = "";
	if($mainlist_filter == "collectingLevel" || $mainlist_filter == "reminderLevel"){
		$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['id'];
		$steps = $v_row['steps'];
		$next_step = $v_row['next_step'];
		$invoices = $v_row['invoices'];
		$letters = $v_row['letters'];
		$objections = $v_row['objections'];
		$transaction_payments = $v_row['transaction_payments'];
		$transaction_fees = $v_row['transaction_fees'];
		$connected_transactions = $v_row['connected_transactions'];
		$casesOnReminderCount = $v_row['casesOnReminderCount'];
		$casesOnCollectingCount = $v_row['casesOnCollectingCount'];
		$debitorCustomer = $v_row['debitorCustomer'];
		$transaction = $v_row['transaction'];
		$creditor_profiles = $v_row['creditor_profiles'];

		$customer_reminder_profile = $debitorCustomer['creditor_reminder_profile_id'];
		$customer_move_to_collecting = $debitorCustomer['choose_move_to_collecting_process'];
		$customer_progress_of_reminder_process = $debitorCustomer['choose_progress_of_reminderprocess'];

		$case_progress_of_reminder_process = $v_row['choose_progress_of_reminderprocess'];
		if($v_row['id'] == null){
			$case_progress_of_reminder_process = $transaction['choose_progress_of_reminderprocess'];
		}
		if($v_row['invoice_nr'] > intval($selectedCustomer['reminder_only_from_invoice_nr'])) {
		} else {
			$notSendInfo = $formText_InvoicesBeforeNumber_output." ".intval($selectedCustomer['reminder_only_from_invoice_nr'])." ".$formText_ShouldNotSendReminders_output;
		}

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

		$initialAmount = 0;
		if($v_row['id'] == null) {
			$initialAmount = $v_row['amount'];
		} else {
			$initialAmount = $v_row['totalSumOriginalClaim'];
		}
		$amount = $initialAmount;
		foreach($connected_transactions as $connected_transaction){
			$amount+=$connected_transaction['amount'];
		}
		$openFeeAmount = 0;
		foreach($transaction_fees as $transaction_fee) {
			$amount += $transaction_fee['amount'];
			if($transaction_fee['open']) {
				$openFeeAmount += $transaction_fee['amount'];
			}
		}
		foreach($transaction_payments as $transaction_payment) {
			$amount += $transaction_payment['amount'];
		}
		?>
		<tr class="gtable_row <?php if($list_filter == "stoppedWithObjection") echo 'objectionRow';?>" data-href="<?php echo $s_edit_link;?>">
		<?php
		// Show default columns
		 ?>

			<?php  if(($selectedCustomer['choose_progress_of_reminderprocess'] == 0 && ($list_filter == "canSendReminderNow" || $list_filter == "notPayedConsiderCollectingProcess")) || ($list_filter == "activeOnCollectingLevel" && $selectedCustomer['choose_how_to_create_collectingcase'] == 0)){  ?>
				<td class="gtable_cell checkboxColumn">
					<?php
					if($v_row['id'] == null){
						if($amount > 0) {
							if($v_row['invoice_nr'] > intval($selectedCustomer['reminder_only_from_invoice_nr'])) {
								$actionType = 0;
								if($v_row['nextStepActionType'] == 2) {
									if($v_row['invoiceEmail'] != "") {
										$actionType = 1;
									}
								}
								?>
								<input type="checkbox" value="<?php echo $v_row['internalTransactionId']?>" data-action_type="<?php echo $actionType;?>"  name="suggestedToProcess[]" id='suggestedToProcess<?php echo $v_row['internalTransactionId']?>' autocomplete="off" class="checkCaseToProcess suggestedToProcess"/>
								<?php
							} else {
								$notSendInfo = $formText_InvoicesBeforeNumber_output." ".intval($selectedCustomer['reminder_only_from_invoice_nr'])." ".$formText_ShouldNotSendReminders_output;
							}
						}
					} else {
						if(strtotime($v_row['nextStepDate']) <= time() && ($amount) > 0){
							if($v_row['invoice_nr'] > intval($selectedCustomer['reminder_only_from_invoice_nr'])) {
								$actionType = 0;
								if($v_row['nextStepActionType'] == 2){
									if($v_row['invoiceEmail'] != ""){
										$actionType = 1;
									}
								}
							?>
								<input type="checkbox" value="<?php echo $v_row['id']?>" name="checkCaseToProcess[]" data-action_type="<?php echo $actionType;?>" id='checkCaseToProcess<?php echo $v_row['id']?>' autocomplete="off" class="checkCaseToProcess"/>
						<?php } else{
								$notSendInfo = $formText_InvoicesBeforeNumber_output." ".intval($selectedCustomer['reminder_only_from_invoice_nr'])." ".$formText_ShouldNotSendReminders_output;
							}
						}
					}?>
				</td>
			<?php } ?>
			<td class="gtable_cell">
				<?php echo $v_row['debitorName']; ?>
				<?php if($notSendInfo != "") { ?>
					<div class="notSendInfo"><?php echo $notSendInfo;?></div>
				<?php } ?>
			</td>

			<td class="gtable_cell">
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
					switch($customer_move_to_collecting){
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
				echo '<span class="change_customer_type personal_customer" data-customer-type="'.$customer_type_collect.'" data-customer-id="'.$debitorCustomer['id'].'" data-creditor-id="'.$v_row['creditorCreditorId'].'"><span class="hoverEye">P<span class="hoverInfo hoverInfoAuto hoverInfoLeft">'.$formText_PrivatePerson_output.'</span></span></span>';
			} else {
				echo '<span class="change_customer_type business_customer" data-customer-type="'.$customer_type_collect.'" data-customer-id="'.$debitorCustomer['id'].'" data-creditor-id="'.$v_row['creditorCreditorId'].'"><span class="hoverEye">B<span class="hoverInfo hoverInfoAuto hoverInfoLeft">'.$formText_Business_output.'</span></span></span>';
			}
			 ?></td>
			 <td class="gtable_cell">
				 <?php
				 $invoiceFile = $v_row['invoiceFile'];
				 if($invoiceFile != ""){
				 ?>
				 <a href="../<?php echo $invoiceFile; ?>?caID=<?php echo $_GET['caID']?>&table=creditor_invoice&field=invoiceFile&ID=<?php echo $invoices[0]['id']; ?>&time=<?php echo time();?>" target="_blank">
				<?php } ?>
					<?php echo $v_row['invoice_nr'];?>
				<?php 	if($invoiceFile != ""){ ?>
					</a>
				 <?php } ?>
				 <?php echo "<br/>".date("d.m.Y", strtotime($v_row['date']));?>
				 <?php
				 if($v_row['due_date'] == null) {
					  echo "<br/>".date("d.m.Y", strtotime($v_row['transactionDueDate']));
				 } else {
					 echo "<br/>".date("d.m.Y", strtotime($v_row['due_date']));
				 }?>
			 </td>
			<td class="gtable_cell rightAlign"><?php
			echo number_format($initialAmount, 2, ",", " ");
			?>
			</td>
			<td class="gtable_cell rightAlign"><?php
			echo number_format($amount, 2, ",", " ");

			?>
			<span class="glyphicon glyphicon-info-sign hoverEye">
				<div class="hoverInfo hoverInfoFull hoverInfoBig">
					<table class="table smallTable">
						<tr>
							<td><b><?php echo $formText_MainClaimLine_output;?></b></td>
							<td>
								<?php echo $formText_InvoiceNumber_output." ".$v_row['invoice_nr'];?>.
							</td>
							<td width="130px"><?php echo number_format($initialAmount, 2, ",", " ");?></td>
						</tr>
						<?php
						if(count($connected_transactions) > 0) { ?>
							<?php foreach($connected_transactions as $connected_transaction) { ?>
								<tr>
									<td><b><?php echo $formText_ExtraInvoiceConnected_output;?></b></td>
									<td>
										<?php echo $formText_InvoiceNumber_output." ".$connected_transaction['invoice_nr'];?>.
									</td>
									<td width="130px"><?php echo number_format($connected_transaction['amount'], 2, ",", " ");?></td>
								</tr>
							<?php } ?>
						<?php }
						if(count($transaction_fees) > 0){ ?>
							<?php
							foreach($transaction_fees as $transaction_fee) {
								$claim_text_array = explode("_", $transaction_fee['comment']);
								?>
								<tr>
									<td><b><?php echo $formText_Fee_output;?></b></td>
									<td><?php echo $claim_text_array[0];?></td>
									<td><?php echo number_format($transaction_fee['amount'], 2, ",", " ");?></td>
								</tr>
								<?php
							}
						}
						if(count($transaction_payments) > 0){
						?>
							<?php
							foreach($transaction_payments as $transaction_payment) {
								?>
								<tr>
									<td><b><?php echo $formText_Payment_output;?></b></td>
									<td><?php echo date("d.m.Y", strtotime($transaction_payment['date']));?></td>
									<td><?php echo number_format($transaction_payment['amount'], 2, ",", " ");?></td>
								</tr>
								<?php
							}
						}
						?>
						<tr class="balance_row">
							<td><b><?php echo $formText_Balance_output;?></b></td>
							<td></td>
							<td><?php echo number_format($amount, 2, ",", " ");?></td>
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
			<?php if($mainlist_filter == "reminderLevel") { ?>
				<td class="gtable_cell" style="width: 100px;"><?php
					if($v_row['id'] != null) {
						echo date("d.m.Y", strtotime($v_row['due_date']));?> <span class="edit_case_duedate glyphicon glyphicon-pencil" data-case-id="<?php echo $v_row['id'];?>" data-duedate="<?php echo $v_row['due_date'];?>"></span>
					<?php } else {
						echo date("d.m.Y", strtotime($v_row['transactionDueDate']));
					} ?>
				</td>
			<?php } ?>

			<?php /*if($mainlist_filter == "cases_reminding") { ?>
				<div class="gtable_cell">
					<div class="move_to_next_step" data-case-id="<?php echo $v_row['id'];?>" data-process-id="<?php echo $process['process_id'];?>"><?php echo $formText_MoveToNextStep;?></div>
				</div>
			<?php } */?>
			<td class="gtable_cell">
				<?php
				if($mainlist_filter == "reminderLevel"){
					if($v_row['id'] > 0) {
						$transaction_changed = false;
						if($v_row['choose_progress_of_reminderprocess'] > 0) {
							$transaction_changed = true;
						}
						if($v_row['choose_move_to_collecting_process'] > 0) {
							$transaction_changed = true;
						}
						?>
						<div class="hoverEye arrowHoverEye">
							<span class="arrow-wrapper <?php if($transaction_changed) echo "changed";?>">
								<i class="glyphicon glyphicon-menu-right"></i><i class="glyphicon glyphicon-menu-right second-arrow"></i>
							</span>
							<div class="hoverInfo hoverInfoFull hoverInfoRight">
								<?php
								echo "<b>".$formText_SettingsForThisInvoice_output."</b><br/>";
								?>
								<b><?php if($transaction_changed) { echo $formText_SpecifiedProcess_output; } else { echo $formText_DefaultProcess_output;}?></b></br></br>
								<?php
								echo $formText_ChooseProgressOfReminderProcess_output.": ";

								if($customer_progress_of_reminder_process == 0){
									$default_progress_of_reminderprocess = $creditor_progress_of_reminder_process;
								} else {
									$default_progress_of_reminderprocess = $customer_progress_of_reminder_process - 1;
								}
								switch($v_row['choose_progress_of_reminderprocess']){
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

								if($customer_move_to_collecting == 0){
									$default_move_to_collecting = $creditor_move_to_collecting;
								} else {
									$default_move_to_collecting = $customer_move_to_collecting - 1;
								}
								switch($v_row['choose_move_to_collecting_process']){
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
								?><div class="glyphicon glyphicon-pencil edit_case_settings" data-case-id="<?php echo $v_row['id'];?>"></div>
							</div>
						</div>
						<?php
					} else {
						$transaction_changed = false;
						if($transaction['reminder_profile_id'] > 0) {
							$transaction_changed = true;
						}
						if($transaction['choose_progress_of_reminderprocess'] > 0) {
							$transaction_changed = true;
						}
						if($transaction['choose_move_to_collecting_process'] > 0) {
							$transaction_changed = true;
						}
						?>
						<div class="hoverEye arrowHoverEye">
							<span class="arrow-wrapper <?php if($transaction_changed) echo "changed";?>">
								<i class="glyphicon glyphicon-menu-right"></i><i class="glyphicon glyphicon-menu-right second-arrow"></i>
							</span>
							<div class="hoverInfo hoverInfoFull hoverInfoRight">
								<?php
								echo "<b>".$formText_SettingsForThisInvoice_output."</b><br/>";
								?>
								<b><?php if($transaction_changed) { echo $formText_SpecifiedProcess_output; } else { echo $formText_DefaultProcess_output;}?></b></br>

								<?php
								echo $formText_ChooseReminderProfile_output.": ";

								if($customer_reminder_profile == 0){
									$customer_type_collect_debitor = $debitorCustomer['customer_type_collect'];
								   if($debitorCustomer['customer_type_collect_addition'] > 0){
									   $customer_type_collect_debitor = $debitorCustomer['customer_type_collect_addition'] - 1;
								   }
									if( $customer_type_collect_debitor == 1){
										$default_reminder_profile = $creditor_profile_for_person;
									} else {
										$default_reminder_profile = $creditor_profile_for_company;
									}
								} else {
									$default_reminder_profile = $customer_reminder_profile;
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

								if($customer_progress_of_reminder_process == 0){
									$default_progress_of_reminderprocess = $creditor_progress_of_reminder_process;
								} else {
									$default_progress_of_reminderprocess = $customer_progress_of_reminder_process - 1;
								}
								switch($transaction['choose_progress_of_reminderprocess']){
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

								if($customer_move_to_collecting == 0){
									$default_move_to_collecting = $creditor_move_to_collecting;
								} else {
									$default_move_to_collecting = $customer_move_to_collecting - 1;
								}
								switch($transaction['choose_move_to_collecting_process']){
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
								} ?>
								<div class="glyphicon glyphicon-pencil edit_transaction_settings" data-transaction-id="<?php echo $v_row['internalTransactionId'];?>"></div>
							</div>
						</div>

						<?php
					}
				}
				?>
			</td>
			<?php if($mainlist_filter == "reminderLevel" || $mainlist_filter == "collectingLevel") { ?>
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
				<?php /*
				<div class="gtable_cell"><input type="checkbox" autocomplete="off" class="case_to_process" data-case-id="<?php echo $v_row['id']?>"/></div>
				*/?>
			<?php } ?>
			<td class="gtable_cell">
				<?php
				if($mainlist_filter == "reminderLevel") { ?>
					<div class="hoverEye menuHoverEye">
						<span class="glyphicon glyphicon-menu-hamburger"></span>
						<div class="hoverInfo hoverInfo3"><?php
						$collectingWarningProcess = array();
						foreach($collectingProcesses as $key => $collectingProcess){
							if($collectingProcess['with_warning']){
								$collectingWarningProcess = $collectingProcess;
							}
						}
						?>
						<?php
						if($list_filter == "notPayedConsiderCollectingProcess"){
							foreach($collectingProcesses as $key => $collectingProcess){
								if(!$collectingProcess['with_warning']){
									?>
									<div class="move_case_to_collecting" data-transactionid="<?php echo $v_row['internalTransactionId']?>" data-processid="<?php echo $collectingProcess['id']?>"><?php echo $formText_TransferToOflowCollect_output; ?></div>
									<?php
									break;
								}
							}
						} else {
							?>
							<div class="move_case_to_collecting" data-processid="<?php echo $collectingWarningProcess['id']?>" data-transactionid="<?php echo $v_row['internalTransactionId']?>"><?php echo $formText_TransferToOflowCollect_output; ?></div>
							<?php
						}
						?>

						<?php
						if($v_row['id'] > 0) {
							if($list_filter == "canSendReminderNow" || $list_filter == "dueDateNotExpired" || $list_filter == "notPayedConsiderCollectingProcess") {
								if($list_filter != "notPayedConsiderCollectingProcess") {
									 ?>
								<div class="putonhold" data-case-id="<?php echo $v_row['id'];?>"><?php echo $formText_Stop_output;?></div>
							<?php } ?>
							<div class="full_reset_case" data-case-id="<?php echo $v_row['id'];?>"><?php echo $formText_ResetCase_output;?></div>

						<?php }

						} ?>

						</div>
					</div>
					<?php
					if($casesOnReminderCount > 0) {
						echo $casesOnReminderCount." ".$formText_CasesOnReminderLevel_output."<br/>";
					}
					if($casesOnCollectingCount > 0) {
						echo $casesOnCollectingCount." ".$formText_CasesOnCollectingLevel_output."<br/>";
					}
				}
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

		<?php if($list_filter == "stoppedWithObjection") { ?>
			<tr class="gtable_row objectionRow">
				<td class="gtable_cell" colspan="9"><?php
			$objections = $v_row['objections'];
			?>
			<table class="fixedTable">
				<?php
				$type_messages = array("", $formText_WantsInvoiceCopy_output,$formText_WantsDefermentOfPayment_output,$formText_WantsInstallmentPayment_output,$formText_HasAnObjectionToTheAmount_output,$formText_HasAnObjectionToTheProductService_output);

				foreach($objections as $objection) {
					?>
					<tr>
						<td><?php if($objection['stopped_by_creditor']) { echo $formText_StoppedByCreditor_output; } else{ echo $type_messages[$objection['objection_type_id']]; }?></td>
						<td><?php echo $objection['message_from_debitor'];?></td>
						<td><?php
						if($objection['objection_closed_date'] != "0000-00-00" && $objection['objection_closed_date'] != ""){
							echo date("d.m.Y", strtotime($objection['objection_closed_date']))." ".$objection['objection_closed_by']."<br/>".nl2br($objection['objection_closed_handling_description']);
						}
						?></td>
						<td>
							<?php if($objection['objection_closed_date'] != "0000-00-00" && $objection['objection_closed_date'] != ""){ ?>
							<?php } else { ?>
								<button class="output-btn small output-close-objection editBtnIcon" data-objection-id="<?php echo $objection['id'];?>" data-case-id="<?php echo $v_row['id']; ?>"><?php echo $formText_Close_output;?></button>
							<?php } ?>
						</td>
					</tr>
					<?php
				}
				?>
			</table>
		</td></tr>
		<?php } ?>
	<?php } ?>
<?php } ?>

</table>
</form>
<?php
// var_dump(time());
 if(($selectedCustomer['choose_progress_of_reminderprocess'] == 0 && ($mainlist_filter == "suggestedCases" || $list_filter == "canSendReminderNow" || $list_filter == "notPayedConsiderCollectingProcess")) || ($list_filter == "activeOnCollectingLevel" && $selectedCustomer['choose_how_to_create_collectingcase'] == 0)){  ?>
	<?php if($list_filter == "canSendReminderNow" || $mainlist_filter == "suggestedCases") { ?>
		<div class="sendReminders manualActionButton"><?php echo $formText_SendReminders_output;?>
			<?php echo ' - <span class="emails_counter">0</span> '.$formText_Emails_output.", <span class='prints_counter'>0</span> ". $formText_Prints_output; ?>
		</div>
	<?php } ?>
	<?php if($list_filter == "notPayedConsiderCollectingProcess") { ?>
		<div class="massMoveToCollecting manualActionButton"><?php echo $formText_StartCollectingProcess_output;?></div>
	<?php } ?>
	<div class="clear"></div>
<?php }
?>
</div>
