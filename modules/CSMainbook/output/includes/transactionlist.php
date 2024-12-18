<?php

$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list";
$bookaccount_id = isset($_GET['bookaccount_id']) ? $_GET['bookaccount_id'] : 0;
$date_from = isset($_GET['date_from']) && $_GET['date_from'] != "" ? $_GET['date_from'] : date("01.m.Y", strtotime("-1 month"));
$date_to = isset($_GET['date_to']) && $_GET['date_to'] != "" ? $_GET['date_to'] : date("t.m.Y", strtotime("-1 month"));
$view_by = isset($_GET['view_by']) ? $_GET['view_by'] : 0;
$creditor_id = isset($_GET['creditor_id']) ? $_GET['creditor_id'] : 0;
$debitor_id = isset($_GET['debitor_id']) ? $_GET['debitor_id'] : 0;
$view_open = isset($_GET['view_open']) ? $_GET['view_open'] : 0;

$s_sql = "SELECT * FROM cs_bookaccount WHERE content_status < 2 ORDER BY sortnr";
$o_query = $o_main->db->query($s_sql);
$bookaccounts = $o_query ? $o_query->result_array() : array();
$transactions = array();
$sql_where = "";
$sql_date = " AND cmv.date >= '".$o_main->db->escape_str(date("Y-m-d", strtotime($date_from)))."' AND cmv.date <= '".$o_main->db->escape_str(date("Y-m-d", strtotime($date_to)))."'";
if($view_open) {
	$sql_where = " AND IFNULL(cmt.closed, 0) = 0";
	$sql_date = "";
}
if($view_by == 0) {
	if($bookaccount_id > 0) {
		$s_sql = "SELECT cmt.*, cmv.date, cmv.case_id, cred.companyname creditorName, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as debitorName
		FROM cs_mainbook_transaction cmt
		LEFT OUTER JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
		LEFT OUTER JOIN collecting_company_cases ccc ON ccc.id = cmv.case_id
		LEFT OUTER JOIN creditor cred ON cred.id = ccc.creditor_id
		LEFT OUTER JOIN customer c ON c.id = ccc.debitor_id
		WHERE cmt.content_status < 2 AND cmt.bookaccount_id = '".$o_main->db->escape_str($bookaccount_id)."'".$sql_date.$sql_where." ORDER BY cmv.date DESC";
		$o_query = $o_main->db->query($s_sql);
		$transactions = $o_query ? $o_query->result_array() : array();
	}
} else if($view_by == 1) {
	if($creditor_id > 0) {
		$s_sql = "SELECT * FROM creditor WHERE id = '".$o_main->db->escape_str($creditor_id)."'";
		$o_query = $o_main->db->query($s_sql);
		$selected_creditor = $o_query ? $o_query->row_array() : array();

		$s_sql = "SELECT cmt.*, cmv.date, cmv.case_id, cred.companyname creditorName, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as debitorName
		FROM cs_mainbook_transaction cmt
		LEFT OUTER JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
		LEFT OUTER JOIN collecting_company_cases ccc ON ccc.id = cmv.case_id
		LEFT OUTER JOIN creditor cred ON cred.id = ccc.creditor_id
		LEFT OUTER JOIN customer c ON c.id = ccc.debitor_id
		WHERE cmt.content_status < 2
		".$sql_date.$sql_where." AND cmt.creditor_id = '".$o_main->db->escape_str($creditor_id)."'
		ORDER BY cmv.date ASC";
		$o_query = $o_main->db->query($s_sql);
		$transactions = $o_query ? $o_query->result_array() : array();
	} 
} else if($view_by == 2) {
	if($debitor_id > 0) {
		$s_sql = "SELECT * FROM customer WHERE id = '".$o_main->db->escape_str($debitor_id)."'";
		$o_query = $o_main->db->query($s_sql);
		$selected_debitor = $o_query ? $o_query->row_array() : array();

		$s_sql = "SELECT cmt.*, cmv.date, cmv.case_id, cred.companyname creditorName, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as debitorName
		FROM cs_mainbook_transaction cmt
		LEFT OUTER JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
		LEFT OUTER JOIN collecting_company_cases ccc ON ccc.id = cmv.case_id
		LEFT OUTER JOIN creditor cred ON cred.id = ccc.creditor_id
		LEFT OUTER JOIN customer c ON c.id = ccc.debitor_id
		WHERE cmt.content_status < 2
		".$sql_date.$sql_where." AND cmt.debitor_id = '".$o_main->db->escape_str($selected_debitor['id'])."'
		ORDER BY cmv.date ASC";
		$o_query = $o_main->db->query($s_sql);
		$transactions = $o_query ? $o_query->result_array() : array();
	}
}
if($_POST['output_form_submit']) {
	$totalAmount = 0;
	$amountCorrect = false;
	if(count($_POST['connect_ids']) > 0){
		$amountCountedFor = 0;
		foreach($transactions as $transaction){
			foreach($_POST['connect_ids'] AS $transaction_id) {
				if($transaction['id'] == $transaction_id){
					$totalAmount += round(floatval($transaction['amount']), 2);
					$totalAmount = round(floatval($totalAmount), 2);
					$amountCountedFor++;
				}
			}
		}
		if($amountCountedFor == count($_POST['connect_ids'])){
			if($totalAmount == 0 ) {
				$s_sql = "SELECT * FROM collecting_system_settings";
				$o_query = $o_main->db->query($s_sql);
				$collecting_system_settings = $o_query ? $o_query->row_array() : array();
				if($collecting_system_settings){
					$link_id = $collecting_system_settings['next_available_link_id'];
					$s_sql = "SELECT * FROM cs_mainbook_transaction WHERE link_id = '".$o_main->db->escape_str($link_id)."'";
					$o_query = $o_main->db->query($s_sql);
					$cs_mainbook_transaction = $o_query ? $o_query->row_array() : array();
					if(!$cs_mainbook_transaction) {
						$s_sql = "UPDATE cs_mainbook_transaction SET updated = NOW(), updatedBy = ?, link_id = ?, closed = 1 WHERE id IN (".implode(",", $_POST['connect_ids']).")";
						$o_query = $o_main->db->query($s_sql, array($variables->loggID, $link_id));
						if($o_query) {
							$link_id++;
							$s_sql = "UPDATE collecting_system_settings SET next_available_link_id = ? WHERE id = ?";
							$o_query = $o_main->db->query($s_sql, array($link_id, $collecting_system_settings['id']));
						} else {
							$fw_error_msg[] = $formText_ErrorUpdatingDatabase_output;
						}
					} else {
						$fw_error_msg[] = $formText_ErrorTryAgain_output;
					}
				} else {
					$fw_error_msg[] = $formText_MissingSettings_output;
				}
			} else {
				$fw_error_msg[] = $formText_SumNotZero_output." " .$totalAmount;
			}
		} else {
			$fw_error_msg[] = $formText_TransactionsNotFound_output;
		}
	} else {
		$fw_error_msg[] = $formText_MissingTransactions_output;
	}

	return;
}
if($view_by == 1 && $creditor_id > 0 && $view_open == 1) {
	$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=transactionlist&view_by=1&creditor_id=&view_open=1";
}
if($view_by == 2 && $debitor_id > 0 && $view_open == 1) {
	$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=transactionlist&view_by=2&debitor_id=&view_open=1";
}
?>
<div id="p_container" class="p_container">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
				<a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list" style="display: block; margin-bottom:10px;"><?php echo $formText_BackToList_outpup;?></a>

				<div class="">
					<?php echo $formText_ViewTransactions_Output;?>
					<select name="" class="viewTransactionsChanger" autocomplete="off">
						<option value="0"><?php echo $formText_Bookaccount_output;?></option>
						<option value="1" <?php if($view_by == 1) echo 'selected';?>><?php echo $formText_Creditor_output;?></option>
						<option value="2" <?php if($view_by == 2) echo 'selected';?>><?php echo $formText_Debitor_output;?></option>
					</select>
					<?php if($view_by == 0) {?>
						<?php echo $formText_Bookaccount_output;?>
						<select name="" class="bookaccountChanger" autocomplete="off">
							<option value=""><?php echo $formText_Select_output;?></option>
							<?php foreach($bookaccounts as $bookaccount) { ?>
								<option value="<?php echo $bookaccount['id'];?>" <?php if($bookaccount_id == $bookaccount['id']) echo 'selected';?>><?php echo $bookaccount['name'];?></option>
							<?php } ?>
						</select>
					<?php } else if($view_by == 1) { ?>
						<?php echo $formText_Creditor_output;?>
						<span class="creditor_selector"><span class="labelText"><?php if($selected_creditor) { echo $selected_creditor['companyname'];} else { echo $formText_SelectCreditor_output; }?></span><input type="hidden" value="<?php echo $selected_creditor['id'];?>" class="creditorChanger"/></span>
					<?php } else if($view_by == 2) { ?>
						<?php echo $formText_Debitor_output;?>
						<span class="debitor_selector"><span class="labelText"><?php if($selected_debitor) { echo $selected_debitor['name']." ".$selected_debitor['middlename']." ".$selected_debitor['lastname']; } else { echo $formText_SelectDebitor_output; }?></span><input type="hidden" value="<?php echo $selected_debitor['id'];?>" class="debitorChanger"/></span>
					<?php } ?>
					<a href="<?php echo $variables->account_root_url.'/modules/'.$module.'/output/includes/generatePdf.php?bookaccount_id='.$bookaccount_id.'&view_by='.$view_by.'&creditor_id='.$creditor_id.'&debitor_id='.$debitor_id.'&date_from='.$date_from.'&date_to='.$date_to.'&_='.time();?>" class="generatePdf" target="_blank">
						<?php echo $formText_DownloadPdf_output; ?>
					</a>
					<div style="margin-top: 5px;">
						<select name="" class="open_transaction_changer" autocomplete="off">
							<option value="0"><?php echo $formText_ShowAllTransactions_output;?></option>
							<?php if($view_by == 1) { ?>
								<option value="1" <?php if($view_open == 1) echo 'selected';?>><?php echo $formText_ShowOpenTransactions_output;?></option>
							<?php } ?>
						</select>
						<?php if($view_open == 0) { ?>
							<?php echo $formText_DateFrom_output;?>
							<input type="text" class="datepicker dateFrom" autocomplete="off" value="<?php if($date_from != "") { echo date("d.m.Y", strtotime($date_from));}?>"/>
							<?php echo $formText_DateTo_output;?>
							<input type="text" class="datepicker dateTo" autocomplete="off" value="<?php if($date_to != "") { echo date("d.m.Y", strtotime($date_to));}?>"/>
						<?php } ?>
					</div>

				</div>
				<div class="p_pageDetails">
					<?php 
						if($view_by == 1 && $creditor_id == 0 && $view_open == 1) {
							$s_sql = "SELECT cred.id, cred.companyname creditorName, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as debitorName, SUM(cmt.amount) as totalGross, COUNT(cmt.id) as transactionCount
							FROM cs_mainbook_transaction cmt
							LEFT OUTER JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
							LEFT OUTER JOIN collecting_company_cases ccc ON ccc.id = cmv.case_id
							LEFT OUTER JOIN creditor cred ON cred.id = cmt.creditor_id
							LEFT OUTER JOIN customer c ON c.id = ccc.debitor_id
							WHERE cmt.content_status < 2
							AND cmt.creditor_id > 0
							".$sql_date.$sql_where."
							GROUP BY cmt.creditor_id
							ORDER BY cmv.date DESC";
							$o_query = $o_main->db->query($s_sql);
							// var_dump($o_main->db->last_query());
							$creditors = $o_query ? $o_query->result_array() : array();
							$total_sum = 0;
							?>
							<div class="p_contentBlock no-vertical-padding">
								<table class="table" width="100%" border="0" cellpadding="0" cellspacing="0">
									<tr>
										<th><?php echo $formText_Creditor_output;?></th>
										<th><?php echo $formText_Transactions_output;?></th>
										<th><?php echo $formText_Amount_output;?></th>
									</tr>
									<?php
									foreach($creditors as $creditor) {
										$total_sum+=$creditor['totalGross'];
										$s_detail_page = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=transactionlist&view_by=1&creditor_id=".$creditor["id"]."&view_open=1"; ?>
										<tr>
											<td><a href="<?php echo $s_detail_page;?>" class="optimize"><?php echo $creditor['creditorName'];?></a></td>
											<td><?php echo $creditor['transactionCount'];?></td>
											<td><?php echo number_format($creditor['totalGross'], 2, ",", " ");?></td>
										</tr>
										<?php
									}
									?>
									<tr>
										<td><b><?php echo $formText_Summary_output;?></b></td>
										<td></td>
										<td><?php echo number_format($total_sum, 2, ",", " ");?></td>
									</tr>
								</table>
							</div>
							<?php
						} else if($view_by == 2 && $debitor_id == 0 && $view_open == 1){
							$s_sql = "SELECT cmt.debitor_id as id, cred.companyname creditorName, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as debitorName, SUM(cmt.amount) as totalGross, COUNT(cmt.id) as transactionCount
							FROM cs_mainbook_transaction cmt
							LEFT OUTER JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
							LEFT OUTER JOIN collecting_company_cases ccc ON ccc.id = cmv.case_id
							LEFT OUTER JOIN creditor cred ON cred.id = ccc.creditor_id
							LEFT OUTER JOIN customer c ON c.id = cmt.debitor_id
							WHERE cmt.content_status < 2
							AND cmt.debitor_id > 0
							".$sql_date.$sql_where."
							GROUP BY cmt.debitor_id
							ORDER BY cmv.date DESC";
							$o_query = $o_main->db->query($s_sql);
							$creditors = $o_query ? $o_query->result_array() : array();
							?>
							<div class="p_contentBlock no-vertical-padding">							
								<table class="table" width="100%" border="0" cellpadding="0" cellspacing="0">
									<tr>
										<th><?php echo $formText_Creditor_output;?></th>
										<th><?php echo $formText_Transactions_output;?></th>
										<th><?php echo $formText_Amount_output;?></th>
									</tr>
									<?php
									foreach($creditors as $creditor) {
										$s_detail_page = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=transactionlist&view_by=2&debitor_id=".$creditor["id"]."&view_open=1"; ?>
										<tr>
											<td><a href="<?php echo $s_detail_page;?>" class="optimize"><?php echo $creditor['debitorName'];?></a></td>
											<td><?php echo $creditor['transactionCount'];?></td>
											<td><?php echo $creditor['totalGross'];?></td>
										</tr>
										<?php
									}
									?>
								</table>
							</div>
							<?php
						} else {
							if(count($transactions) > 0) { ?>
							<div class="p_pageDetailsTitle"><?php echo $formText_Transactions_Output;?></div>
							<div class="p_contentBlock no-vertical-padding">
								<form class="output-form-list main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=transactionlist&bookaccount_id=".$bookaccount_id."&date_from=".$date_from."&date_to=".$date_to."&view_by=".$view_by."&creditor_id=".$creditor_id."&debitor_id=".$debitor_id."&view_open=".$view_open;?>" method="POST">
									<input type="hidden" name="fwajax" value="1">
									<input type="hidden" name="fw_nocss" value="1">
									<input type="hidden" name="output_form_submit" value="1">
									<table class="table" width="100%" border="0" cellpadding="0" cellspacing="0">
										<tr>
											<?php
											if($view_by > 0) {
												?>
												<th></th>
												<?php
											}
											?>
											<th><?php echo $formText_Date_output;?></th>
											<?php if($view_by == 1) { ?>
												<th><?php echo $formText_Status_output;?></th>
											<?php } ?>
											<th><?php echo $formText_Type_output;?></th>
											<th><?php echo $formText_Bookaccount_output;?></th>
											<th><?php echo $formText_Case_output;?></th>
											<th><?php echo $formText_Creditor_output;?></th>
											<th><?php echo $formText_Debitor_output;?></th>
											<th class="rightAligned"><?php echo $formText_Amount_output;?></th>
										</tr>
										<?php
										$summary = 0;
										foreach( $transactions as $paymentCoverline) {
											$total_amount = $paymentCoverline['amount'];

											$s_sql = "SELECT * FROM collecting_cases_claim_line_type_basisconfig WHERE id = ?";
											$o_query = $o_main->db->query($s_sql, array($paymentCoverline['collecting_claim_line_type']));
											$claim_line_type = $o_query ? $o_query->row_array() : array();

											$s_sql = "SELECT * FROM cs_bookaccount WHERE id = ?";
											$o_query = $o_main->db->query($s_sql, array($paymentCoverline['bookaccount_id']));
											$cs_bookaccount = $o_query ? $o_query->row_array() : array();
											$summary+= $total_amount;

											$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCases&folderfile=output&folder=output&inc_obj=details&cid=".$paymentCoverline['case_id'];

											?>
											<tr>
												<?php
												if($view_by > 0) {
													?>
													<td>
														<?php if($paymentCoverline['link_id'] == "") {?>
															<input type="checkbox" data-amount="<?php echo $total_amount;?>" value="<?php echo $paymentCoverline['id'];?>" name="connect_ids[]" class="connectLines" id="connect_<?php echo $paymentCoverline['id']?>"/><label for="connect_<?php echo $paymentCoverline['id']?>"></label>
														<?php } ?>
													</td>
													<?php
												}
												?>
												<td><?php echo date("d.m.Y", strtotime($paymentCoverline['date']));?></td>
												
												<?php if($view_by == 1) { ?>
													<td><?php
													if($paymentCoverline['closed']) {
														echo $formText_Closed_output;
													} else {
														echo $formText_Open_output;
													}?></td>
												<?php } ?>
												<td><?php echo $claim_line_type['type_name'];?></td>
												<td><?php echo $cs_bookaccount['name']; ?></td>
												<td><a href="<?php echo $s_edit_link?>" target="_blank"><?php echo $paymentCoverline['case_id']; ?></a></td>
												<td><?php echo $paymentCoverline['creditorName']?></td>
												<td><?php echo $paymentCoverline['debitorName']?></td>
												<td class="rightAligned"><?php echo number_format($total_amount, 2, ",", " "); ?></td>
											</tr>
										<?php } ?>
										<tr>
											<td><b><?php echo $formText_Total_output;?></b></td>
												<?php
												if($view_by > 0) {
													?>
													<td></td>
													<?php
												}
												?>
											<?php if($view_by == 1) { ?>
												<td></td>
											<?php } ?>
											<td></td>
											<td></td>
											<td></td>
											<td></td>
											<td></td>
											<td class="rightAligned"><b><?php echo number_format($summary, 2, ",", " "); ?></b></td>
										</tr>
									</table>
									<div id="validate-message"></div>
									<div>
										<span class="totalSum"><?php echo $formText_TotalSum_output;?> <span></span></span>
										<input type="submit" class="connectButtons" value="<?php echo $formText_Connect_output;?>"/>
									</div>
								</form>
							</div>
					<?php } else {?>
						<div class="p_contentBlock no-vertical-padding">
							<?php
							echo $formText_NoTransactionsFound_output;
							?>
						</div>
						<?php
					}
				} ?>
				</div>
			</div>
		</div>
	</div>
</div>
<style>
.creditor_selector {
	cursor: pointer;
	color: #46b2e2
}
.debitor_selector {
	cursor: pointer;
	color: #46b2e2
}
.rightAligned {
	text-align: right;
}
#validate-message {
	color: red;
}
</style>
<?php
$s_path = 'modules/'.$module.'/output/elementsOutput/jquery.validate/jquery.validate.min.js';
$l_time = filemtime(BASEPATH.$s_item);
?>
<script type="text/javascript" src="<?php echo $variables->account_root_url.$s_path.'?v='.$l_time;?>"></script>
<script type="text/javascript">

var out_popup;
var out_popup_options = {
	follow: [true, true],
	followSpeed: 300,
	fadeSpeed: 0,
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function(){
		$(this).addClass('opened');
	},
	onClose: function(){
		$(this).removeClass('opened');
		$(this).removeClass('fullWidth');
		if($(this).is('.close-reload')) {
            var redirectUrl = $(this).data("redirect");
			if(redirectUrl !== undefined && redirectUrl != ""){
				document.location.href = redirectUrl;
			} else {
				var data = {
					bookaccount_id: $(".bookaccountChanger").val(),
					date_from: $(".dateFrom").val(),
					date_to: $(".dateTo").val(),
					view_by: $(".viewTransactionsChanger").val(),
					creditor_id: $(".creditorChanger").val(),
					debitor_id: $(".debitorChanger").val(),
					view_open: $(".open_transaction_changer").val()
				}
                loadView("transactionlist", data);
	            $('#popupeditboxcontent').html('');
            }
		}
	}
};
$(function(){
	$("form.output-form-list").validate({
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
						$("#validate-message").html(_msg, true);
						$("#validate-message").show();
					} else {
						var data = {
							bookaccount_id: $(".bookaccountChanger").val(),
							date_from: $(".dateFrom").val(),
							date_to: $(".dateTo").val(),
							view_by: $(".viewTransactionsChanger").val(),
							creditor_id: $(".creditorChanger").val(),
							debitor_id: $(".debitorChanger").val(),
							view_open: $(".open_transaction_changer").val()
						}
						loadView("transactionlist", data);
					}
				}
			}).fail(function() {
				$("#validate-message").html("<?php echo $formText_ErrorOccurredHandlingRequest_Output;?>", true);
				$("#validate-message").show();
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

				$("#validate-message").html(message);
				$("#validate-message").show();
				$('#popupeditbox').css('height', "auto");
			} else {
				$("#validate-message").hide();
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
	function updateConnectButton(){
		var total_amount = 0;
		$(".connectLines:checked").each(function(){
			total_amount += parseFloat($(this).data("amount"));
		})
		total_amount = parseFloat(total_amount).toFixed(2);
		$(".totalSum span").html(total_amount);
		if(total_amount == 0 && $(".connectLines:checked").length > 0) {
			$(".connectButtons").prop("disabled", false);
		} else {
			$(".connectButtons").prop("disabled", true);
		}
	}
	$(".connectLines").off("change").on("change", function(){
		updateConnectButton();
	})
	updateConnectButton();
	$(".creditor_selector").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
		};
		ajaxCall('get_creditors', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})

	$(".debitor_selector").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
		};
		ajaxCall('get_debitors', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".datepicker").datepicker({
		firstDay: 1,
		dateFormat: "dd.mm.yy",
		onSelect: function(){
			var data = {
				bookaccount_id: $(".bookaccountChanger").val(),
				date_from: $(".dateFrom").val(),
				date_to: $(".dateTo").val(),
				view_by: $(".viewTransactionsChanger").val(),
				creditor_id: $(".creditorChanger").val(),
				debitor_id: $(".debitorChanger").val(),
				view_open: $(".open_transaction_changer").val()
			}
			loadView("transactionlist", data);
		}
	})
	$(".bookaccountChanger").change(function(){
		var data = {
			bookaccount_id: $(".bookaccountChanger").val(),
			date_from: $(".dateFrom").val(),
			date_to: $(".dateTo").val(),
			view_by: $(".viewTransactionsChanger").val(),
			creditor_id: $(".creditorChanger").val(),
			debitor_id: $(".debitorChanger").val(),
			view_open: $(".open_transaction_changer").val()
		}
		loadView("transactionlist", data);
	})

	$(".viewTransactionsChanger").change(function(){
		var data = {
			bookaccount_id: $(".bookaccountChanger").val(),
			date_from: $(".dateFrom").val(),
			date_to: $(".dateTo").val(),
			view_by: $(".viewTransactionsChanger").val(),
			creditor_id: $(".creditorChanger").val(),
			debitor_id: $(".debitorChanger").val(),
			view_open: $(".open_transaction_changer").val()
		}
		loadView("transactionlist", data);
	})
	$(".open_transaction_changer").change(function(){
		var data = {
			bookaccount_id: $(".bookaccountChanger").val(),
			date_from: $(".dateFrom").val(),
			date_to: $(".dateTo").val(),
			view_by: $(".viewTransactionsChanger").val(),
			creditor_id: $(".creditorChanger").val(),
			debitor_id: $(".debitorChanger").val(),
			view_open: $(".open_transaction_changer").val()
		}
		loadView("transactionlist", data);
	})
	$(".creditorChanger").change(function(){
		var data = {
			bookaccount_id: $(".bookaccountChanger").val(),
			date_from: $(".dateFrom").val(),
			date_to: $(".dateTo").val(),
			view_by: $(".viewTransactionsChanger").val(),
			creditor_id: $(".creditorChanger").val(),
			debitor_id: $(".debitorChanger").val(),
			view_open: $(".open_transaction_changer").val()
		}
		loadView("transactionlist", data);
	})
	$(".debitorChanger").change(function(){
		var data = {
			bookaccount_id: $(".bookaccountChanger").val(),
			date_from: $(".dateFrom").val(),
			date_to: $(".dateTo").val(),
			view_by: $(".viewTransactionsChanger").val(),
			creditor_id: $(".creditorChanger").val(),
			debitor_id: $(".debitorChanger").val(),
			view_open: $(".open_transaction_changer").val()
		}
		loadView("transactionlist", data);
	})
})
</script>
