<?php


$s_sql = "SELECT * FROM collecting_system_settings";
$o_query = $o_main->db->query($s_sql, array());
$system_settings = ($o_query ? $o_query->row_array() : array());

$showAll = $_POST['show_all'];
if($_POST['output_form_submit']) {
	$forceMove = 1;
	if(count($_POST['transaction_ids']) > 0) {
		include(__DIR__."/process_scripts/handle_move_to_aptic.php");
		return;
	} else {
		$fw_error_msg[] = $formText_NoTransactionSelectedToMoveToCollecting_output;
		return;
	}
} else {
	$set_preview = true;
	$creditorsToSync = array();
	$creditorsToSyncFull = array();
	include(__DIR__."/process_scripts/handle_move_to_aptic.php");

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
							triggerSync: 1,
							page: '<?php echo $page;?>'
						};

						ajaxCall('process_automatic_move_to_aptic', data, function(json) {
							var data = {
								page: '<?php echo $page;?>'
							};

							ajaxCall('process_automatic_move_to_aptic', data, function(json) {
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
		?>
		<div class="popupform popupform-<?php echo $eventId;?>">
			<form class="output-form main" action="<?php if(isset($formActionUrl)) { echo $formActionUrl; } else { print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=process_automatic_move_to_aptic"; }?>" method="post">
				<input type="hidden" name="fwajax" value="1">
				<input type="hidden" name="fw_nocss" value="1">
				<input type="hidden" name="output_form_submit" value="1">
				<input type="hidden" name="languageID" value="<?php echo $variables->languageID?>">
				<input type="hidden" name="redirect_url" value="<?php if(isset($formRedirectUrl)) { echo $formRedirectUrl; } else { echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$eventId; } ?>">
				<div class="inner">
					<div class="popupformTitle"><?php
						echo $formText_CasesToBeMoved_output;
					?></div>
					<input type="checkbox" class="showAllTransactions" value="1" <?php if($_POST['show_all']) echo 'checked';?> id="show_all"/><label for="show_all"><?php echo $formText_ShowAll_output;?></label>
					<div class="caseList">
						<table class="table">
							<tr>
								<th><input type="checkbox" name="select_all" autocomplete="off" class="select_all_transactions"/></th>
								<th><?php echo $formText_TransactionId_output;?></th>
								<th><?php echo $formText_Creditor_output;?></th>
								<th><?php echo $formText_Debitor_output;?></th>
								<th><?php echo $formText_DueDate_output;?></th>
								<th><?php echo $formText_MainClaim_output;?></th>
								<th><?php echo $formText_InterestAndFees_output;?></th>
								<th><?php echo $formText_Case_output;?></th>
							</tr>
							<?php

							foreach($return_data as $creditorId => $cases){
								foreach($cases as $case){
									$mainClaim = 0;
									$interestAndFees = 0;
									$s_sql = "SELECT * FROM creditor_transactions WHERE id = ?";
									$o_query = $o_main->db->query($s_sql, array($case['internalTransactionId']));
									$invoice = ($o_query ? $o_query->row_array() : array());

									$s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND link_id = ? AND invoice_nr = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND (comment LIKE '%reminderFee_%' OR comment LIKE '%interest_%') ORDER BY created DESC";
									$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['invoice_nr'], $invoice['creditor_id']));
									$claim_transactions = ($o_query ? $o_query->result_array() : array());
									foreach($claim_transactions as $claim_transaction) {
										$interestAndFees+=$claim_transaction['amount'];
									}
									$restAmount = $invoice['amount'];
									$s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') AND link_id = ? AND creditor_id = ?";
									$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
									$all_transaction_payments = ($o_query ? $o_query->result_array() : array());

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
											$restAmount += $transaction_payment['amount'];
										}
									}
									$correctAddress = false;
									$debitor = $case['debitor'];
									if($debitor['paCity'] != "" && $debitor['paStreet'] != ""  && $debitor['paPostalNumber'] != "" ){
										$correctAddress = true;
									}
									?>
									<tr>
										<td>
											<?php if($restAmount > $system_settings['minimum_amount_move_to_collecting_company_case'] && $correctAddress) { ?>
												<input type="checkbox" name="transaction_ids[]" class="transaction_checkbox" autocomplete="off" value="<?php echo $case['internalTransactionId'];?>"/>
											<?php } ?>
										</td>
										<td><?php echo $case['internalTransactionId'];?></td>
										<td><?php echo $case['creditorName'];?></td>
										<td>
											<?php echo $case['debitorName'];?>
											<?php if(!$correctAddress) echo '<div class="missing_address">'.$formText_MissingAddress_output.'</div>'?>
										</td>
										<td><?php if($case['id'] > 0) { echo date("d.m.Y", strtotime($case['due_date'])); } else { echo date("d.m.Y", strtotime($invoice['due_date']));} ?></td>
										<td><?php echo number_format($restAmount, 2, ",", "");?></td>
										<td><?php echo number_format($interestAndFees, 2, ",", "");?></td>
										<td><?php echo $case['id'];?></td>
									</tr>
									<?php
								}
							}

							?>
						</table>
						<?php if($totalPages > 1) { ?>
							<div class="pages">
								<?php for($x =1; $x<= $totalPages; $x++) { ?>
									<a class="page_selector <?php if($page == $x) echo 'active';?>" data-page="<?php echo $x ?>"><?php echo $x;?></a>
								<?php } ?>
							</div>
						<?php } ?>
					</div>
				</div>

				<div id="popup-validate-message" style="display:none;"></div>
				<div class="popupformbtn">
					<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
					<input type="submit" class="process_btn" name="sbmbtn" value="<?php echo $formText_MoveToAptic_Output; ?>">
				</div>
			</form>
		</div>
		<style>
			.missing_address {
				color: red;
			}
		.popupformSubTitle {
			font-size: 14px;
			margin-bottom: 5px;
		}
		.page_selector {
			margin: 4px;
			cursor: pointer;
		}
		.page_selector.active {
			text-decoration: underline;
		}
		.showAllTransactions {
			margin-right: 10px !important;
		}
		.caseList {
			margin-top: 10px;
		}
		</style>
		<script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
		<script type="text/javascript">
		$(function(){
			$(".select_all_transactions").off("click").on("click", function(){
				if($(this).is(":checked")) {
					$(".transaction_checkbox").prop("checked", true);
				} else {
					$(".transaction_checkbox").prop("checked", false);
				}
				calculate_total();
			})
			function calculate_total(){
				$(".process_btn").val($(".transaction_checkbox:checked").length + " "+'<?php echo $formText_MoveToAptic_output;?>');
			}
			$(".transaction_checkbox").off("change").on("change", function(){
				calculate_total();
			})
			$(".page_selector").off("click").on("click", function(e){
				e.preventDefault();
				var data = {
					page: $(this).data("page"),
					show_all: $(".showAllTransactions:checked").length
				};
				ajaxCall('process_automatic_move_to_aptic', data, function(json) {
					$('#popupeditboxcontent').html('');
					$('#popupeditboxcontent').html(json.html);
					out_popup = $('#popupeditbox').bPopup(out_popup_options);
					$("#popupeditbox:not(.opened)").remove();
				});
			})
			$(".showAllTransactions").off("click").on("click", function(e){
				e.preventDefault();
				var data = {
					page: 1,
					show_all: $(".showAllTransactions:checked").length
				};
				ajaxCall('process_automatic_move_to_aptic', data, function(json) {
					$('#popupeditboxcontent').html('');
					$('#popupeditboxcontent').html(json.html);
					out_popup = $('#popupeditbox').bPopup(out_popup_options);
					$("#popupeditbox:not(.opened)").remove();
				});

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
		})
		</script>
		<?php

	}
}
?>
