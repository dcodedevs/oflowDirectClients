<?php 
if($_POST['output_form_submit']){	
	if(count($_POST['transaction_ids']) > 0 || count($_POST['case_ids']) > 0) {
		include(__DIR__."/process_scripts/handle_all_reminder_automatic_cases_new.php");
		return;
	} else {
		$fw_error_msg[] = $formText_NoTransactionSelectedToMoveToCollecting_output;
		return;
	}
} else {
	$set_preview = true;
	$creditorsToSync = array();
	$creditorsToSyncFull = array();
	include(__DIR__."/process_scripts/handle_all_reminder_automatic_cases_new.php");

    if(count($creditorsToSync) > 0) {		
		if($_POST['triggerSync']){
			foreach($creditorsToSync as $creditorToSync){
				$creditorId = $creditorToSync;
				if(is_file(__DIR__."/import_scripts/import_cases2.php")){
					ob_start();
					include(__DIR__."/import_scripts/import_cases2.php");
					$result_output = ob_get_contents();
					$result_output = trim(preg_replace('/\s\s+/', '', $result_output));
					ob_end_clean();
				}
			}
		} else {
			?>
			<div>
				<?php
				echo count($creditorsToSync)." ".$formText_CreditorsNeedsToBeSynced_output."<br/>";
				if(count($creditorsToSync) > 0) {
					foreach ($creditorsToSyncFull as $creditor_to_sync){
						echo $creditor_to_sync['companyname']."<br/>";
					}
				}
				echo "<span class='launch_syncing'>".$formText_LaunchSyncing_output."</span>";
				?>
			</div>
			<style>
				.launch_syncing {
					cursor: pointer;
					color: #46b2e2;
				}
			</style>
			<script type="text/javascript">
				$(function(){
					$(".launch_syncing").off("click").on("click", function(e){
						e.preventDefault();
						var data = {
							triggerSync: 1
						};

						ajaxCall('process_automatic_cases_new', data, function(json) {
							var data = {
							};

							ajaxCall('process_automatic_cases_new', data, function(json) {
								$('#popupeditboxcontent').html('');
								$('#popupeditboxcontent').html(json.html);
								out_popup = $('#popupeditbox').bPopup(out_popup_options);
								$("#popupeditbox:not(.opened)").remove();
								$(window).resize();
							});
						});
					})
				})
			</script>
			<?php
		}
	} else {
        $action_text = array(1=>$formText_SendLetter_output, 2=>$formText_SendEmail_output);
		?>
		<div class="popupform popupform-<?php echo $eventId;?>">
			<div id="popup-validate-message" style="display:none;"></div>
			<form class="output-form main" action="<?php if(isset($formActionUrl)) { echo $formActionUrl; } else { print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=process_automatic_cases_new"; }?>" method="post">
				<input type="hidden" name="fwajax" value="1">
				<input type="hidden" name="fw_nocss" value="1">
				<input type="hidden" name="output_form_submit" value="1">
				<input type="hidden" name="languageID" value="<?php echo $variables->languageID?>">
				<input type="hidden" name="redirect_url" value="<?php if(isset($formRedirectUrl)) { echo $formRedirectUrl; } else { echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$eventId; } ?>">
				<div class="inner">
					<div class="popupformTitle"><?php
						echo $formText_CasesToBeProcessed_output;
					?></div>
					<div class="caseList">
						<table class="table">
							<tr>
								<th><input type="checkbox" name="select_all" autocomplete="off" class="select_all_transactions"/></th>
								<th><?php echo $formText_CaseId_output;?></th>
								<th><?php echo $formText_Creditor_output;?></th>
								<th><?php echo $formText_Debitor_output;?></th>
								<th><?php echo $formText_DueDate_output;?></th>
								<th><?php echo $formText_Letter_output;?></th>
								<th><?php echo $formText_MainClaim_output;?></th>
								<th><?php echo $formText_InterestAndFees_output;?></th>
							</tr>
							<?php
							foreach($return_data as $creditorId => $cases){

								$s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND (comment LIKE '%reminderFee_%' OR comment LIKE '%interest_%') ORDER BY created DESC";
								$o_query = $o_main->db->query($s_sql, array($creditorId));
								$all_transaction_fees = ($o_query ? $o_query->result_array() : array());

								foreach($cases['casesToBeProcessed'] as $case){
									$transaction_fees = array();
									foreach($all_transaction_fees as $all_transaction_fee) {
										if($case['link_id'] > 0 && $all_transaction_fee['link_id'] == $case['link_id']){
											$transaction_fees[] = $all_transaction_fee;
										}
									}
									$openFeeAmount = 0;
									foreach($transaction_fees as $transaction_fee) {
										if($transaction_fee['open']) {
											$openFeeAmount += $transaction_fee['amount'];
										}
									}
									if($case['due_date'] == null) {
										$dueDate = $case['transactionDueDate'];
									} else {
										$dueDate = $case['due_date'];
									}
									$error = 0;
									if($dueDate == "" || $dueDate == "0000-00-00"){
										$error = 1;
									}
									$missingAddress = false;

									$actionType = 0;
									if($case['nextStepActionType'] == 2) {										
										if(filter_var(preg_replace('/\xc2\xa0/', '', trim($case['invoiceEmail'])), FILTER_VALIDATE_EMAIL)){
											$actionType = 1;
										}
									}
									if($actionType == 0){
										$s_sql = "SELECT * FROM customer WHERE creditor_customer_id = ? AND creditor_id = ?";
										$o_query = $o_main->db->query($s_sql, array($case['external_customer_id'], $case['creditorCreditorId']));
										$debitorCustomer = $o_query ? $o_query->row_array() : array();
										if($debitorCustomer['paStreet'] == "" && $debitorCustomer['paPostalNumber'] == "" && $debitorCustomer['paCity'] == "") {
											$missingAddress = true;
										}
									}
									if($missingAddress){
										$error = 1;
									}
									?>
									<tr>
										<td>
											<?php if(!$error){ ?>
												<input type="checkbox" name="case_ids[]" class="transaction_checkbox" autocomplete="off" value="<?php echo $case['collectingcase_id'];?>"/>
											<?php } ?>
										</td>
										<td><?php echo $case['collectingcase_id'];?></td>
										<td><?php echo $case['creditorName'];?></td>
										<td><?php echo $case['debitorName'];?></td>
										<td><?php
										echo date("d.m.Y", strtotime($dueDate));
										if($dueDate == "" || $dueDate == "0000-00-00"){
											echo '<div style="color: red;">'.$formText_DueDateMissing_output.'</div>';
										}
										?></td>
										<td><?php echo $case['nextStepName'];?><br/>
											<div class="action_icon_wrapper">
											<?php
											if($case['nextStepActionType'] == 2){
												if(trim($case['invoiceEmail']) != "") {
													if(filter_var(preg_replace('/\xc2\xa0/', '', trim($case['invoiceEmail'])), FILTER_VALIDATE_EMAIL)){
														echo $action_text[2];

														echo ": <span class='email_wrapper email_wrapper_text'>".$case['invoiceEmail'] ."</span>";
													} else {
														echo $action_text[1];
														echo ": <div class='error'>".$formText_InvalidEmail."".$case['invoiceEmail']."</div>";
											
													}
												} else {
													echo $action_text[1];
												}
											} else {
												echo $action_text[intval($case['nextStepActionType'])];
											}
											if($missingAddress) {
												echo '<div style="color: red;">'.$formText_MissingAddress_output.'</div>';
											}
											?>
										</div>
										</td>
										<td><?php
										$initialAmount = 0;
										if(intval($case['collectingcase_id']) == 0) {
											$initialAmount = $case['amount'];
										} else {
											$initialAmount = $case['totalSumOriginalClaim'];
										}
										echo number_format($initialAmount, 2, ',','');

										?></td>
										<td><?php echo number_format($openFeeAmount, 2, ',','')?></td>
									</tr>
									<?php
								}
								
								foreach($cases['casesToBeCreated'] as $case){
									$transaction_fees = array();
									foreach($all_transaction_fees as $all_transaction_fee) {
										if($case['link_id'] > 0 && $all_transaction_fee['link_id'] == $case['link_id']){
											$transaction_fees[] = $all_transaction_fee;
										}
									}
									$openFeeAmount = 0;
									foreach($transaction_fees as $transaction_fee) {
										if($transaction_fee['open']) {
											$openFeeAmount += $transaction_fee['amount'];
										}
									}
									?>
									<tr>
										<td><input type="checkbox" name="transaction_ids[]" class="transaction_checkbox" autocomplete="off" value="<?php echo $case['internalTransactionId'];?>"/></td>
										<td><?php echo $case['internalTransactionId'];?></td>
										<td><?php echo $case['creditorName'];?></td>
										<td><?php echo $case['debitorName'];?></td>
										<td><?php
										if($case['due_date'] == null) {
											echo "<br/>".date("d.m.Y", strtotime($case['transactionDueDate']));
										} else {
											echo "<br/>".date("d.m.Y", strtotime($case['due_date']));
										}
										?></td>

										<td><?php echo $case['nextStepName'];?><br/><div class="action_icon_wrapper">
										<?php
										if($case['nextStepActionType'] == 2){
											if($case['invoiceEmail'] != ""){
												if(filter_var(preg_replace('/\xc2\xa0/', '', trim($case['invoiceEmail'])), FILTER_VALIDATE_EMAIL)){
													
													echo $action_text[2];

													echo ": <span class='email_wrapper email_wrapper_text'>".$case['invoiceEmail'] ."</span>";
												} else {
													echo $action_text[1];
													echo ": <div class='error'>".$formText_InvalidEmail."".$case['invoiceEmail']."</div>";
											
												}
											} else {
												echo $action_text[1];
											}
										} else {
											echo $action_text[intval($case['nextStepActionType'])];
										}
										$actionType = 0;
										if($case['nextStepActionType'] == 2) {
											if($case['invoiceEmail'] != "") {
												$actionType = 1;
											}
										}
										$missingAddress = false;
										if($actionType == 0) {
											$s_sql = "SELECT * FROM customer WHERE creditor_customer_id = ? AND creditor_id = ?";
											$o_query = $o_main->db->query($s_sql, array($case['external_customer_id'], $case['creditorCreditorId']));
											$debitorCustomer = $o_query ? $o_query->row_array() : array();
											if($debitorCustomer['paStreet'] == "" && $debitorCustomer['paPostalNumber'] == "" && $debitorCustomer['paCity'] == "") {
												$missingAddress = true;
											}
										}
										if($missingAddress) {
											echo '<div style="color: red;">'.$formText_MissingAddress_output.'</div>';
										}
										?>
										</div></td>
										<td><?php
										$initialAmount = 0;
										if(intval($case['collectingcase_id']) == null) {
											$initialAmount = $case['amount'];
										} else {
											$initialAmount = $case['totalSumOriginalClaim'];
										}
										echo number_format($initialAmount, 2, ',','');
										?></td>
										<td><?php echo number_format($openFeeAmount, 2, ',','')?></td>
									</tr>
									<?php
								}
							}

							?>
						</table>
					</div>
				</div>

				<div class="popupformbtn">
					<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
					<input type="submit" name="sbmbtn" class="process_btn" value="<?php echo $formText_Process_Output; ?>">
				</div>
			</form>
		</div>
		<style>
		.popupformSubTitle {
			font-size: 14px;
			margin-bottom: 5px;
		}
		.action_icon_wrapper {
			display: inline-block;
			vertical-align: middle;
		}
		.action_icon_wrapper select {
			vertical-align: middle;
		}
		.email_wrapper_text {
			display: block;
			max-width: 200px;
			overflow-wrap: break-word;
		}
		#popupeditbox.popupeditbox.fixedWidth {
			width: 1100px;
		}
		</style>
		<script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
		<script type="text/javascript">

		$('#popupeditbox').addClass("fixedWidth");
		$(".select_all_transactions").off("click").on("click", function(){
			if($(this).is(":checked")) {
				$(".transaction_checkbox").prop("checked", true);
			} else {
				$(".transaction_checkbox").prop("checked", false);
			}
			calculate_total();
		})

		function calculate_total(){
			$(".process_btn").val($(".transaction_checkbox:checked").length + " "+'<?php echo $formText_Process_output;?>');
		}
		$(".transaction_checkbox").off("change").on("change", function(){
			calculate_total();
		})
		$("form.output-form").validate({
			submitHandler: function(form) {
				fw_loading_start();
				var formdata = $(form).serializeArray();
				var data = {};
				$(formdata ).each(function(index, obj){
					if(data[obj.name] != undefined) {
						if(Array.isArray(data[obj.name])){
							data[obj.name].push(obj.value);
						} else {
							data[obj.name] = [data[obj.name], obj.value];
						}
					} else {
						data[obj.name] = obj.value;
					}
				});
				$("#popup-validate-message").hide();

				$.ajax({
					url: $(form).attr("action"),
					cache: false,
					type: "POST",
					dataType: "json",
					data: data,
					success: function (data) {
						fw_loading_end();
						if(data.error !== undefined)
						{
							$.each(data.error, function(index, value){
								var _type = Array("error");
								if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
								$("#popup-validate-message").append(value);
							});
							$("#popup-validate-message").show();
							fw_loading_end();
							fw_click_instance = fw_changes_made = false;
						} else
						{
							$('#popupeditboxcontent').html('');
							$('#popupeditboxcontent').html(data.html);
							out_popup = $('#popupeditbox').bPopup(out_popup_options);
							$("#popupeditbox:not(.opened)").remove();
							out_popup.addClass("close-reload");
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
		<?php
	}
}
?>