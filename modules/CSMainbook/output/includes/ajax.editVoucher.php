<?php
$sql = "SELECT * FROM collecting_system_settings ORDER BY id";
$result = $o_main->db->query($sql);
$v_collecting_system_settings = $result ? $result->row_array(): array();
if('0000-00-00' == $v_collecting_system_settings['accounting_close_last_date']) $v_collecting_system_settings['accounting_close_last_date'] = '';
$l_accounting_close_last_date = (''!=$v_collecting_system_settings['accounting_close_last_date']?strtotime($v_collecting_system_settings['accounting_close_last_date']):0);

function update_ledger_info($bookaccount_id, $amount, $caseData, $voucherId){
	global $o_main;
	global $moduleID;
	global $variables;
	$transaction_id = 0;
	if($amount != 0){
		$s_sql = "SELECT * FROM cs_bookaccount WHERE id = '".$o_main->db->escape_str($bookaccount_id)."'";
		$o_query = $o_main->db->query($s_sql);
		$cs_bookaccount = ($o_query ? $o_query->row_array() : array());
		$sql_update = "";
		if($cs_bookaccount['is_creditor_ledger']) {
			$sql_update.= ", creditor_id = '".$o_main->db->escape_str($caseData['creditor_id'])."'";
		}
		if($cs_bookaccount['is_debitor_ledger']) {
			$sql_update.= ", debitor_id = '".$o_main->db->escape_str($caseData['debitor_id'])."'";
		}
		$s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($voucherId)."' AND bookaccount_id = '".$o_main->db->escape_str($bookaccount_id)."'";
		$o_query = $o_main->db->query($s_sql);
		$cs_transaction = ($o_query ? $o_query->row_array() : array());
		if($cs_transaction){
			$s_sql = "UPDATE cs_mainbook_transaction SET moduleID = '".$o_main->db->escape_str($moduleID)."', updated = NOW(), updatedBy = '".$o_main->db->escape_str($variables->loggID)."',
			cs_mainbook_voucher_id = '".$o_main->db->escape_str($voucherId)."',
			amount = '".$o_main->db->escape_str(str_replace(",",".",$amount))."',
			bookaccount_id = '".$o_main->db->escape_str($bookaccount_id)."'".$sql_update."
			WHERE id = '".$o_main->db->escape_str($cs_transaction['id'])."'";
			$o_query = $o_main->db->query($s_sql);
			$transaction_id = $cs_transaction['id'];
		} else {
			$s_sql = "INSERT INTO cs_mainbook_transaction SET moduleID = '".$o_main->db->escape_str($moduleID)."', created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."',
			cs_mainbook_voucher_id = '".$o_main->db->escape_str($voucherId)."',
			amount = '".$o_main->db->escape_str(str_replace(",",".",$amount))."',
			bookaccount_id = '".$o_main->db->escape_str($bookaccount_id)."'".$sql_update;
			$o_query = $o_main->db->query($s_sql);
			$transaction_id = $o_main->db->insert_id();
		}
	}
	return $transaction_id;
}
$id = isset($_POST['id']) ? $_POST['id'] : '';
$s_sql = "SELECT * FROM cs_mainbook_voucher WHERE id = '".$o_main->db->escape_str($id)."'";
$o_query = $o_main->db->query($s_sql);
$voucher_data = ($o_query ? $o_query->row_array() : array());
$b_closed = FALSE;
if($voucher_data['date'] != "" && $voucher_data['date'] != "0000-00-00")
{
	$b_closed = $l_accounting_close_last_date >= strtotime($voucher_data['date']);
}
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		$s_sql = "SELECT * FROM collecting_company_cases WHERE content_status < 2 AND id = '".$o_main->db->escape_str($_POST['case_id'])."'";
		$o_query = $o_main->db->query($s_sql);
		$caseData = ($o_query ? $o_query->row_array() : array());
		if($caseData || $_POST['voucherOnly']){
			if($_POST['text'] != ""){
				if($_POST['date'] != ""){
					if(count($_POST['transactions']) > 0){
						$totalAmount = 0;
						$bookaccountError = 0;
						$missingDebitor = false;
						$missingCreditor = false;
						foreach($_POST['transactions'] as $key=>$transaction_id) {
							$type_id = $_POST['type'][$key];
							$bookaccount_id = $_POST['bookaccount'][$key];
							$amount = str_replace(",", ".", trim($_POST['amount'][$key]));
							$totalAmount += number_format($amount, 2, ".", "");

							$s_sql = "SELECT * FROM cs_bookaccount WHERE content_status < 2 AND id = '".$o_main->db->escape_str($bookaccount_id)."'";
							$o_query = $o_main->db->query($s_sql);
							$bookaccount = ($o_query ? $o_query->row_array() : array());
							if(!$bookaccount){
								$bookaccountError = 1;
							} else {
								if($_POST['voucherOnly']) {
									if($bookaccount['is_creditor_ledger'] && $_POST['creditor_id'][$key] == ""){
										$missingCreditor = true;
									}
									if($bookaccount['is_debitor_ledger'] && $_POST['debitor_id'][$key] == ""){
										$missingDebitor = true;
									}
								}
							}
						}
						if(!$missingDebitor){
							if(!$missingCreditor){
								if(!$bookaccountError) {
									if(abs(round($totalAmount, 2)) == 0) {
										$l_date = strtotime($_POST['date']);
										$date = date("Y-m-d", $l_date);
										if(!$b_closed)
										{
											if(isset($id) && 0 < $id)
											{
											   $s_sql = "UPDATE cs_mainbook_voucher SET updated = NOW(), updatedBy = '".$o_main->db->escape_str($variables->loggID)."',
											   date = '".$o_main->db->escape_str($date)."',
											   case_id = '".$o_main->db->escape_str($_POST['case_id'])."',
											   text = '".$o_main->db->escape_str($_POST['text'])."'
											   WHERE id = '".$o_main->db->escape_str($id)."'";
											} else {
											   $s_sql = "SELECT MAX(sortnr) sortnr FROM cs_mainbook_voucher";
											   $o_query = $o_main->db->query($s_sql);
											   $maxSort = ($o_query ? $o_query->row_array() : array());
											   $sortnr = intval($maxSort['sortnr']) + 1;

											   $s_sql = "INSERT INTO cs_mainbook_voucher SET moduleID = '".$o_main->db->escape_str($moduleID)."', created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."',
											   sortnr = '".$o_main->db->escape_str($sortnr)."',
											   date = '".$o_main->db->escape_str($date)."',
											   text = '".$o_main->db->escape_str($_POST['text'])."',
											   case_id = '".$o_main->db->escape_str($_POST['case_id'])."'";
											}
											$o_query = $o_main->db->query($s_sql);
											if(!$o_query)
											{
											   $fw_error_msg['error_'.count($fw_error_msg)] = $formText_ErrorOccurredHandlingRequest_Output;
											} else {
											   if($id == 0){
													$voucherId = $o_main->db->insert_id();
												} else {
													$voucherId = $id;
												}
												$summary_on_collecting_company_ledger = 0;
												$summary_on_creditor_ledger = 0;
												$summary_on_debitor_ledger = 0;
												$summary_on_protected_ledger = 0;
												
												$s_sql = "SELECT * FROM collecting_company_cases_claim_lines WHERE 
												collecting_company_case_id = '".$o_main->db->escape_str($caseData['id'])."'
												AND (claim_type = 9 OR claim_type = 10)
												AND IFNULL(court_fee_released_date, '0000-00-00') = '0000-00-00'";
												$o_query = $o_main->db->query($s_sql);
												$courtFeeProtectedLines = ($o_query ? $o_query->result_array() : array());

												$s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($voucherId)."' ORDER BY id";
												$o_query = $o_main->db->query($s_sql);
												$all_transactions = ($o_query ? $o_query->result_array() : array());
												$added_transaction_ids = array();
												foreach($_POST['transactions'] as $key=>$transaction_id){
													$bookaccount_id = $_POST['bookaccount'][$key];
													$amount = str_replace(",",".",$_POST['amount'][$key]);

													$s_sql = "SELECT * FROM cs_bookaccount WHERE id = '".$o_main->db->escape_str($bookaccount_id)."'";
													$o_query = $o_main->db->query($s_sql);
													$cs_bookaccount = ($o_query ? $o_query->row_array() : array());
													if($variables->loggID=="byamba@dcode.no"){
														if(($cs_bookaccount['id'] == 9 || $cs_bookaccount['id'] == 10) && count($courtFeeProtectedLines) > 0) {
															$summary_on_protected_ledger += $amount;
														} else {
															if($cs_bookaccount['summarize_on_ledger'] == 1){
																$summary_on_collecting_company_ledger += $amount;
															} else if($cs_bookaccount['summarize_on_ledger'] == 2) {
																$summary_on_creditor_ledger += $amount;
															} else if($cs_bookaccount['summarize_on_ledger'] == 3) {
																$summary_on_debitor_ledger += $amount;
															} 
														}
													} else {
														if($cs_bookaccount['summarize_on_ledger'] == 1){
															$summary_on_collecting_company_ledger += $amount;
														} else if($cs_bookaccount['summarize_on_ledger'] == 2) {
															$summary_on_creditor_ledger += $amount;
														} else if($cs_bookaccount['summarize_on_ledger'] == 3) {
															$summary_on_debitor_ledger += $amount;
														} 
													}

													$sql_update = "";
													if($cs_bookaccount['is_creditor_ledger']) {
														if($_POST['voucherOnly']) {
															$sql_update.= ", creditor_id = '".$o_main->db->escape_str($_POST['creditor_id'][$key])."'";
														} else {
															$sql_update.= ", creditor_id = '".$o_main->db->escape_str($caseData['creditor_id'])."'";
														}
													}
													if($cs_bookaccount['is_debitor_ledger']) {
														if($_POST['voucherOnly']) {
															$sql_update.= ", debitor_id = '".$o_main->db->escape_str($_POST['debitor_id'][$key])."'";
														} else {
															$sql_update.= ", debitor_id = '".$o_main->db->escape_str($caseData['debitor_id'])."'";
														}
													}

													$s_sql = "SELECT * FROM cs_mainbook_transaction WHERE content_status < 2 AND id = '".$o_main->db->escape_str($transaction_id)."'";
													$o_query = $o_main->db->query($s_sql);
													$cs_transaction = ($o_query ? $o_query->row_array() : array());
													if($cs_transaction) {
														$s_sql = "UPDATE cs_mainbook_transaction SET moduleID = '".$o_main->db->escape_str($moduleID)."', created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."',
														cs_mainbook_voucher_id = '".$o_main->db->escape_str($voucherId)."',
														amount = '".$o_main->db->escape_str($amount)."',
														bookaccount_id = '".$o_main->db->escape_str($bookaccount_id)."'".$sql_update."
														WHERE id = '".$o_main->db->escape_str($cs_transaction['id'])."'";
														$o_query = $o_main->db->query($s_sql);
													} else {
														$s_sql = "INSERT INTO cs_mainbook_transaction SET moduleID = '".$o_main->db->escape_str($moduleID)."', created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."',
														cs_mainbook_voucher_id = '".$o_main->db->escape_str($voucherId)."',
														amount = '".$o_main->db->escape_str($amount)."',
														bookaccount_id = '".$o_main->db->escape_str($bookaccount_id)."'".$sql_update;
														$o_query = $o_main->db->query($s_sql);
														$transaction_id = $o_main->db->insert_id();
													}
													$added_transaction_ids[] = $transaction_id;
												}

												$transaction_id = update_ledger_info(22, $summary_on_collecting_company_ledger, $caseData, $voucherId);
												$added_transaction_ids[] = $transaction_id;
												$transaction_id = update_ledger_info(16, $summary_on_creditor_ledger, $caseData, $voucherId);
												$added_transaction_ids[] = $transaction_id;
												$transaction_id = update_ledger_info(15, $summary_on_debitor_ledger, $caseData, $voucherId);
												$added_transaction_ids[] = $transaction_id;
												if($summary_on_protected_ledger > 0) {
													$transaction_id = update_ledger_info(33, $summary_on_protected_ledger, $caseData, $voucherId);
													$added_transaction_ids[] = $transaction_id;
												}

												if(count($added_transaction_ids) > 3){
													foreach($all_transactions as $all_transaction) {
														if(!in_array($all_transaction['id'], $added_transaction_ids)){
															$s_sql = "DELETE FROM cs_mainbook_transaction WHERE id = '".$o_main->db->escape_str($all_transaction['id'])."'";
															$o_query = $o_main->db->query($s_sql);
														}
													}
												}
												$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list";
												return;
											}
										} else {
											$fw_error_msg[] = $formText_AccountingPeriodIsClosedUntil_Output.' '.date('d.m.Y', $l_accounting_close_last_date);
										}
									} else {
										$fw_error_msg[] = $formText_TotalSumShouldBe0_output;
									}
								} else {
									$fw_error_msg[] = $formText_BookAccountIsMandatory_output;
								}
							} else {
								$fw_error_msg[] = $formText_MissingCreditor_output;
							}
						} else {
							$fw_error_msg[] = $formText_MissingDebitor_output;
						}
					} else {
						$fw_error_msg[] = $formText_MissingTransactions_output;
					}
				} else {
					$fw_error_msg[] = $formText_MissingDate_output;
				}
			} else {
				$fw_error_msg[] = $formText_MissingText_output;
			}
		} else {
			$fw_error_msg[] = $formText_MissingCase_output;
		}
	}
	if($_POST['action'] == "deleteVoucher") {
		if(!$b_closed)
		{
			$s_sql = "DELETE cs_mainbook_transaction, cs_mainbook_voucher FROM cs_mainbook_voucher
			LEFT JOIN cs_mainbook_transaction ON cs_mainbook_transaction.cs_mainbook_voucher_id = cs_mainbook_voucher.id
			WHERE cs_mainbook_voucher.id = '".$o_main->db->escape_str($id)."'";
			$o_query = $o_main->db->query($s_sql);
		} else {
			$fw_error_msg[] = $formText_AccountingPeriodIsClosedUntil_Output.' '.date('d.m.Y', $l_accounting_close_last_date);
		}
		return;
	}
}

$s_sql = "SELECT * FROM cs_bookaccount WHERE content_status < 2 ORDER BY sortnr DESC";
$o_query = $o_main->db->query($s_sql);
$cs_bookaccounts = ($o_query ? $o_query->result_array() : array());

$s_sql = "SELECT * FROM collecting_company_cases WHERE id = '".$o_main->db->escape_str($voucher_data['case_id'])."'";
$o_query = $o_main->db->query($s_sql);
$collectingCase = ($o_query ? $o_query->row_array() : array());

$s_sql = "SELECT * FROM collecting_company_cases_claim_lines WHERE 
collecting_company_case_id = '".$o_main->db->escape_str($collectingCase['id'])."'
AND (claim_type = 9 OR claim_type = 10)
AND IFNULL(court_fee_released_date, '0000-00-00') = '0000-00-00'";
$o_query = $o_main->db->query($s_sql);
$courtFeeProtectedLines = ($o_query ? $o_query->result_array() : array());

$s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($voucher_data['id'])."' ORDER BY id";
$o_query = $o_main->db->query($s_sql);
$transactions = ($o_query ? $o_query->result_array() : array());

$ledgerChecksum = 0;
?>
<div class="popupform">
	<div class="popupformTitle"><?php echo ($b_edit ? $formText_EditVoucher_Output : $formText_AddNewVoucher_Output);?></div>
	<form class="output-form2 main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editVoucher";?>" method="POST">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="id" value="<?php if(isset($_POST['id'])) echo $_POST['id'];?>">
		<input type="hidden" name="voucherOnly" value="<?php if($voucher_data){ if($voucher_data['case_id'] == 0) echo 1; } else{ if(isset($_POST['voucherOnly'])) echo $_POST['voucherOnly']; }?>">

		<div class="inner">
			<?php if(!$_POST['voucherOnly'] && (($voucher_data && $voucher_data['case_id'] > 0) || !$voucher_data)) { ?>
				<div class="line collectingCaseWrapper">
	                <div class="lineTitle"><?php echo $formText_CollectingCase_Output; ?></div>
	                <div class="lineInput">
	                    <?php if($collectingCase) { ?>
							<?php if(!$b_closed){?>
								<a href="#" class="selectCollectingCase"><?php echo $collectingCase['id']." ".$collectingCase['debitorName']?></a>
							<?php } else { ?>
								<?php echo $collectingCase['id']." ".$collectingCase['debitorName']?>
							<?php } ?>
							<a href="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCases&folderfile=output&folder=output&inc_obj=details&cid=".$collectingCase['id'];?>" target="_blank"><?php echo $formText_openCase_output;?></a>
						<?php } elseif(!$b_closed) { ?>
							<a href="#" class="selectCollectingCase"><?php echo $formText_SelectCollectingCase_Output;?></a>
						<?php } ?>
						<input type="hidden" name="case_id" id="collectingCaseId" data-has-court-protected-lines="<?php if(count($courtFeeProtectedLines) > 0) echo '1';?>" value="<?php print $collectingCase['id'];?>" required>

	                </div>
	                <div class="clear"></div>
	            </div>
			<?php } ?>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Text_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="text" required value="<?php echo $voucher_data['text']; ?>" placeholder="" autocomplete="off"<?php echo($b_closed?' disabled':'');?>>
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Date_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace datepicker" required name="date" value="<?php if($voucher_data['date'] != "0000-00-00" && $voucher_data['date'] != "") echo date("d.m.Y", strtotime($voucher_data['date'])); ?>" placeholder="" autocomplete="off"<?php echo($b_closed?' disabled':'');?>>
                </div>
                <div class="clear"></div>
            </div>
			<div class="lineSeperatorTitle"><?php echo $formText_Transactions_output;?><?php if(!$b_closed){?><span class="add_transaction_row">+ <?php echo $formText_AddTransaction_output;?></span><?php } ?></div>
			<div class="transaction_list">
				<div class="transaction_row">
					<div class="transaction_column"><b><?php echo $formText_Bookaccount_output;?></b></div>
					<div class="transaction_column"><b><?php echo $formText_Amount_output;?></b></div>
					<div class="clear"></div>
				</div>
				<?php
				$total_sum = 0;
				foreach($transactions as $transaction) {
					if($transaction['bookaccount_id'] != 22 && $transaction['bookaccount_id'] != 16 && $transaction['bookaccount_id'] != 15 OR !$collectingCase) {
						$total_sum+=$transaction['amount'];
						if(!$collectingCase){
							$s_sql = "SELECT * FROM customer WHERE id = '".$o_main->db->escape_str($transaction['debitor_id'])."'";
							$o_query = $o_main->db->query($s_sql);
							$transaction_debitor = ($o_query ? $o_query->row_array() : array());

							$s_sql = "SELECT * FROM creditor WHERE id = '".$o_main->db->escape_str($transaction['creditor_id'])."'";
							$o_query = $o_main->db->query($s_sql);
							$transaction_creditor = ($o_query ? $o_query->row_array() : array());
						}
						?>
						<div class="transaction_row">
							<input type="hidden" name="transactions[]" value="<?php echo $transaction['id']?>"/>
							<div class="transaction_column">
								<select name="bookaccount[]" class="popupforminput botspace bookaccountSelector" autocomplete="off"<?php echo($b_closed?' disabled':'');?>>
									<option value=""><?php echo $formText_Select_output;?></option>
									<?php foreach($cs_bookaccounts as $cs_bookaccount) {?>
										<option value="<?php echo $cs_bookaccount['id'];?>" data-creditor-ledger="<?php echo $cs_bookaccount['is_creditor_ledger']; ?>"  data-debitor-ledger="<?php echo $cs_bookaccount['is_debitor_ledger']; ?>" data-summarize-on-ledger="<?php echo $cs_bookaccount['summarize_on_ledger'];?>" <?php if($transaction['bookaccount_id'] == $cs_bookaccount['id']) echo 'selected';?>><?php echo $cs_bookaccount['name'];?></option>
									<?php } ?>
								</select>
							</div>
							<div class="transaction_column">
								<input type="text" class="popupforminput botspace transaction_amount_input rightAligned" name="amount[]" autocomplete="off" value="<?php echo number_format($transaction['amount'], 2, ",", "");?>"<?php echo($b_closed?' disabled':'');?>/>
							</div>
							<div class="transaction_column">
								<div class="debitor_select_wrapper">
									<a href="#" class="selectDebitor"><?php echo $transaction_debitor['name'];?></a>
									<input type="hidden" name="debitor_id[]" class="debitorId" value="<?php echo $transaction_debitor['id']?>">
								</div>
								<div class="creditor_select_wrapper">
									<a href="#" class="selectCreditor"><?php echo $transaction_creditor['companyname']?></a>
									<input type="hidden" name="creditor_id[]" class="creditorId" value="<?php print $transaction_creditor['id'];?>">
								</div>
							</div>
							<?php if(!$b_closed){?><div class="transaction_column edit_column"><span class="glyphicon glyphicon-trash delete_transaction"></span></div><?php } ?>
							<div class="clear"></div>
						</div>
						<?php
					}
				}?>
			</div>
			<div class="transaction_sum">
				<div class="transaction_row">
					<div class="transaction_column"><b><?php echo $formText_Total_output;?></b></div>
					<div class="transaction_column">&nbsp;</div>
					<div class="transaction_column transaction_total_sum rightAligned"><?php echo number_format($total_sum, 2, ",", "")?></div>
					<div class="clear"></div>
				</div>
			</div>
			<div class="lineSeperatorTitle"><?php echo $formText_Ledger_output;?></div>
			<div class="ledger_list">
				<?php
				$ledger_total_sum = 0;

				$current_transaction = array();
				foreach($transactions as $transaction) {
					if($transaction['bookaccount_id'] == 15 ) {
						$current_transaction = $transaction;
						break;
					}
				}
				?>
				<div class="transaction_row">
					<div class="transaction_column">
						<?php foreach($cs_bookaccounts as $cs_bookaccount) {
						if($cs_bookaccount['id'] == 15) {
							echo $cs_bookaccount['name'];
						}
					} ?></div>
					<div class="transaction_column">
						&nbsp;
					</div>
					<div class="transaction_column ledger_amount_15 rightAligned">
						<?php
						echo number_format($current_transaction['amount'], 2, ",", "");
						$ledger_total_sum += $current_transaction['amount'];
						?>
					</div>
					<div class="transaction_column"></div>
					<div class="clear"></div>
				</div>
				<?php
				$current_transaction = array();
				foreach($transactions as $transaction) {
					if($transaction['bookaccount_id'] == 16 ) {
						$current_transaction = $transaction;
						break;
					}
				}
				?>
				<div class="transaction_row">
					<div class="transaction_column">
						<?php foreach($cs_bookaccounts as $cs_bookaccount) {
						if($cs_bookaccount['id'] == 16) {
							echo $cs_bookaccount['name'];
						}
					} ?></div>
					<div class="transaction_column">
						&nbsp;
					</div>
					<div class="transaction_column ledger_amount_16 rightAligned">
						<?php
						echo number_format($current_transaction['amount'], 2, ",", "");
						$ledger_total_sum += $current_transaction['amount'];
						?>
					</div>
					<div class="transaction_column"></div>
					<div class="clear"></div>
				</div>
				<?php
				$current_transaction = array();
				foreach($transactions as $transaction) {
					if($transaction['bookaccount_id'] == 22 ) {
						$current_transaction = $transaction;
						break;
					}
				}
				?>
				<div class="transaction_row">
					<div class="transaction_column">
						<?php foreach($cs_bookaccounts as $cs_bookaccount) {
						if($cs_bookaccount['id'] == 22) {
							echo $cs_bookaccount['name'];
						}
					} ?></div>
					<div class="transaction_column">
						&nbsp;
					</div>
					<div class="transaction_column ledger_amount_22 rightAligned">
						<?php
						echo number_format($current_transaction['amount'], 2, ",", "");
						$ledger_total_sum += $current_transaction['amount'];
						?>
					</div>
					<div class="transaction_column"></div>
					<div class="clear"></div>
				</div>
				<?php
				if($variables->loggID=="byamba@dcode.no"){
					$current_transaction = array();
					foreach($transactions as $transaction) {
						if($transaction['bookaccount_id'] == 33 ) {
							$current_transaction = $transaction;
							break;
						}
					}
					?>
					<div class="transaction_row">
						<div class="transaction_column">
							<?php foreach($cs_bookaccounts as $cs_bookaccount) {
							if($cs_bookaccount['id'] == 33) {
								echo $cs_bookaccount['name'];
							}
						} ?></div>
						<div class="transaction_column">
							&nbsp;
						</div>
						<div class="transaction_column ledger_amount_33 rightAligned">
							<?php
							echo number_format($current_transaction['amount'], 2, ",", "");
							$ledger_total_sum += $current_transaction['amount'];
							?>
						</div>
						<div class="transaction_column"></div>
						<div class="clear"></div>
					</div>
					<?php 
				}
				?>
				<div class="transaction_row">
					<div class="transaction_column"><b><?php echo $formText_Total_output;?></b></div>
					<div class="transaction_column">&nbsp;</div>
					<div class="transaction_column ledger_total_sum rightAligned"><?php echo number_format($ledger_total_sum, 2, ",", "")?></div>
					<div class="clear"></div>
				</div>
				<div class="transaction_row">
					<div class="transaction_column"><b><?php echo $formText_LedgerChecksum_output;?></b></div>
					<div class="transaction_column">&nbsp;</div>
					<div class="transaction_column ledger_checksum rightAligned"><?php echo number_format($ledgerChecksum, 2, ",", "")?></div>
					<div class="clear"></div>
				</div>
			</div>

		</div>

		<div id="popup-validate-message"></div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<?php if(!$b_closed||$b_closed){?><input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>"><?php } ?>
		</div>
	</form>
	<div class="empty_transaction">
		<div class="transaction_row">
			<input type="hidden" name="transactions[]" value=""/>
			<div class="transaction_column">
				<select name="bookaccount[]" class="popupforminput botspace bookaccountSelector" autocomplete="off">
					<option value=""><?php echo $formText_Select_output;?></option>
					<?php foreach($cs_bookaccounts as $cs_bookaccount) {?>
						<option value="<?php echo $cs_bookaccount['id'];?>" data-creditor-ledger="<?php echo $cs_bookaccount['is_creditor_ledger']; ?>"  data-debitor-ledger="<?php echo $cs_bookaccount['is_debitor_ledger']; ?>"  data-summarize-on-ledger="<?php echo $cs_bookaccount['summarize_on_ledger'];?>"><?php echo $cs_bookaccount['name'];?></option>
					<?php } ?>
				</select>
			</div>
			<div class="transaction_column">
				<input type="text" class="popupforminput botspace transaction_amount_input rightAligned" name="amount[]" autocomplete="off" value=""/>
			</div>
			<div class="transaction_column">
				<div class="debitor_select_wrapper">
					<a href="#" class="selectDebitor"><?php echo $formText_SelectDebitor_Output;?></a>
					<input type="hidden" name="debitor_id[]" class="debitorId" value="">
				</div>
				<div class="creditor_select_wrapper">
					<a href="#" class="selectCreditor"><?php echo $formText_SelectCreditor_Output;?></a>
					<input type="hidden" name="creditor_id[]" class="creditorId" value="">
				</div>
			</div>
			<div class="transaction_column edit_column"><span class="glyphicon glyphicon-trash delete_transaction"></span></div>
			<div class="clear"></div>
		</div>
	</div>
</div>
<style>
.debitor_select_wrapper {
	display: none;
}
.creditor_select_wrapper {
	display: none;
}
.empty_transaction {
	display: none;
}
.lineSeperatorTitle {
	font-size: 15px;
	font-weight: bold;
	margin-top: 15px;
	margin-bottom: 10px;
}
.lineSeperatorTitle .add_transaction_row {
	font-weight: normal;
	font-size: 12px;
	margin-left: 20px;
	cursor: pointer;
	color: #46b2e2;
}

.transaction_row {
	margin-bottom: 5px;
}
.transaction_row .transaction_column {
	float: left;
	width: 29%;
	vertical-align: middle;
	margin-right: 1%;
}
.transaction_row .transaction_column.edit_column {
	width: 10%;
	margin-right: 0;
}
.transaction_row .transaction_column .delete_transaction {
	cursor: pointer;
	color: #46b2e2;
	margin-top: 7px;
}
.transaction_total_sum {
	font-weight: bold;
}
.rightAligned {
	text-align: right;
}
</style>
<?php
$s_path = 'modules/'.$module.'/output/elementsOutput/jquery.validate/jquery.validate.min.js';
$l_time = filemtime(BASEPATH.$s_item);
?>
<script type="text/javascript" src="<?php echo $variables->account_root_url.$s_path.'?v='.$l_time;?>"></script>
<script type="text/javascript">

$(function(){
	$("form.output-form2").validate({
		submitHandler: function(form) {
			fw_loading_start();
			$.ajax({
				url: $(form).attr("action"),
				cache: false,
				type: "POST",
				dataType: "json",
				data: $(form).serialize(),
				success: function (json) {
					fw_loading_end();
					if(json.error !== undefined) {
						var _msg = '';
						$.each(json.error, function(index, value){
							var _type = Array("error");
							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
							_msg = _msg + '<div class="msg-' + _type[0] + '">' + value + '</div>';
						});
						$("#popup-validate-message").html(_msg, true);
						$("#popup-validate-message").show();
					} else {
						out_popup.addClass("close-reload");
						out_popup.close();
					}
				}
			}).fail(function() {
				$("#popup-validate-message").html("<?php echo $formText_ErrorOccurredHandlingRequest_Output;?>", true);
				$("#popup-validate-message").show();
				$('#popupeditbox').css('height', "auto");
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
				$('#popupeditbox').css('height', "auto");
			} else {
				$("#popup-validate-message").hide();
			}
			setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
		},
		errorPlacement: function(error, element) {
			if(element.attr("name") == "case_id") {
				error.insertAfter(".popupform .selectCollectingCase");
			}
		},
		messages: {
			case_id: "<?php echo $formText_SelectCollectingCase_output;?>",
		}
	});

	$(".datepicker").datepicker({
		dateFormat: "dd.mm.yy",
		firstDay: 1
	})

	$(".selectCollectingCase").unbind("click").bind("click", function(){
		var data = {};
		ajaxCall('get_collecting_cases', data, function(obj) {
			$('#popupeditboxcontent2').html('');
			$('#popupeditboxcontent2').html(obj.html);
			out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
			$("#popupeditbox2:not(.opened)").remove();
		});
	})
	bind_transactions();
	$(".add_transaction_row").off("click").on("click", function(){
		var transaction_row = $(".empty_transaction .transaction_row").clone();
		$(".transaction_list").append(transaction_row);
		bind_transactions();
	})
	function bind_transactions() {
		$(".transaction_row .delete_transaction").off("click").on("click", function(){
			$(this).parents(".transaction_row").remove();
		})

		$(".transaction_list .transaction_amount_input").off("keyup").on("keyup", function(){
			calculate_totals();
		})
		$(".bookaccountSelector").change(function(){
			if($(this).find("option:selected").data("creditor-ledger")){
				$(this).parents(".transaction_row").find(".debitor_select_wrapper").hide();
				$(this).parents(".transaction_row").find(".creditor_select_wrapper").show();
			} else if($(this).find("option:selected").data("debitor-ledger")){
				$(this).parents(".transaction_row").find(".creditor_select_wrapper").hide();
				$(this).parents(".transaction_row").find(".debitor_select_wrapper").show();
			}
			calculate_totals();
		}).change();
		$(".selectCreditor").off("click").on("click", function(e){
			e.preventDefault();
			var data = {
				indexNumber:$(this).parents(".transaction_row").index()
			};
			ajaxCall('get_creditors', data, function(obj) {
				$('#popupeditboxcontent2').html('');
				$('#popupeditboxcontent2').html(obj.html);
				out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
				$("#popupeditbox2:not(.opened)").remove();
			});
		})
		$(".selectDebitor").off("click").on("click", function(e){
			e.preventDefault();
			var data = {
				indexNumber:$(this).parents(".transaction_row").index()
			};
			ajaxCall('get_debitors', data, function(obj) {
				$('#popupeditboxcontent2').html('');
				$('#popupeditboxcontent2').html(obj.html);
				out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
				$("#popupeditbox2:not(.opened)").remove();
			});
		})
	}
	function calculate_totals(){
		var summary_on_collecting_company_ledger = 0;
		var summary_on_debitor_ledger = 0;
		var summary_on_creditor_ledger = 0;
		var summary_on_protected_ledger = 0;
		var ledger_total_sum =0;
		var totalSum = 0;
		var ledger_checksum = 0;
		<?php if($variables->loggID =="byamba@dcode.no") { ?>
			$(".transaction_list .transaction_amount_input").each(function(){
				var val = parseFloat($(this).val().replace(",", "."));
				if(!isNaN(val)){
					totalSum += val;
					var summarize_on_ledger = $(this).parents(".transaction_row").find(".bookaccountSelector option:selected").data("summarize-on-ledger");
					var had_court_protected_lines = $("#collectingCaseId").data("had_court_protected_lines");
					var bookaccount_id = $(this).parents(".transaction_row").find(".bookaccountSelector").val();
					if((bookaccount_id == 9 || bookaccount_id == 10) && had_court_protected_lines){
						summary_on_protected_ledger += val;
					} else {
						if(summarize_on_ledger == 1) {
							summary_on_collecting_company_ledger += val;
						} else if(summarize_on_ledger == 2) {
							summary_on_creditor_ledger += val;
						} else if(summarize_on_ledger == 3) {
							summary_on_debitor_ledger += val;
						}
					}
					if($(this).parents(".transaction_row").find(".bookaccountSelector").val() == 1){
						ledger_checksum+=val;
					}
				}
			})
		<?php } else { ?>
		$(".transaction_list .transaction_amount_input").each(function(){
			var val = parseFloat($(this).val().replace(",", "."));
			if(!isNaN(val)){
				totalSum += val;
				var summarize_on_ledger = $(this).parents(".transaction_row").find(".bookaccountSelector option:selected").data("summarize-on-ledger");
				var had_court_protected_lines = $("#collectingCaseId").data("had_court_protected_lines");
				var bookaccount_id = $(this).parents(".transaction_row").find(".bookaccountSelector").val();
				if(summarize_on_ledger == 1) {
					summary_on_collecting_company_ledger += val;
				} else if(summarize_on_ledger == 2) {
					summary_on_creditor_ledger += val;
				} else if(summarize_on_ledger == 3) {
					summary_on_debitor_ledger += val;
				}
				if($(this).parents(".transaction_row").find(".bookaccountSelector").val() == 1){
					ledger_checksum+=val;
				}
			}
		})
		<?php } ?>
		$(".transaction_total_sum").html(totalSum.toFixed(2).replace(".", ","));

		ledger_total_sum = summary_on_collecting_company_ledger+summary_on_creditor_ledger+summary_on_debitor_ledger+summary_on_protected_ledger;
		$(".ledger_amount_22").html(summary_on_collecting_company_ledger.toFixed(2).replace(".", ","))
		$(".ledger_amount_16").html(summary_on_creditor_ledger.toFixed(2).replace(".", ","))
		$(".ledger_amount_15").html(summary_on_debitor_ledger.toFixed(2).replace(".", ","))
		$(".ledger_amount_33").html(summary_on_protected_ledger.toFixed(2).replace(".", ","))
		$(".ledger_total_sum").html(ledger_total_sum.toFixed(2).replace(".", ","));
		$(".ledger_checksum").html((ledger_checksum+ledger_total_sum).toFixed(2).replace(".", ","));

	}
	calculate_totals();
})
</script>
