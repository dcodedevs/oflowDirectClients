<?php
$date = isset($_GET['month']) && $_GET['month'] != "" ? date("d.m.Y", strtotime($_GET['month'])) : "";

$firstDay = date("Y-m-01", strtotime($month));
$lastDay = date("Y-m-t", strtotime($month));

$start = new DateTime($firstDay);
$interval = DateInterval::createFromDateString('1 day');
$end = new DateTime($lastDay);
$end->setTime(0,0,1);
$period = new DatePeriod($start, $interval, $end);
// if($variables->loggID == "byamba@dcode.no") {
// 	$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE case_id > 0";
// 	$o_query = $o_main->db->query($s_sql);
// 	$claimletters = $o_query ? $o_query->result_array() : array();
// 	foreach($claimletters as $claimletter){
// 		$s_sql = "SELECT * FROM collecting_cases WHERE id = ? ORDER BY sortnr ASC";
// 		$o_query = $o_main->db->query($s_sql, array($claimletter['case_id']));
// 		$case = ($o_query ? $o_query->row_array() : array());
//
// 		$s_sql = "SELECT * FROM customer WHERE id = ?";
// 		$o_query = $o_main->db->query($s_sql, array($case['debitor_id']));
// 		$debitorCustomer = $o_query ? $o_query->row_array() : array();
//
// 		$customer_move_to_collecting = $debitorCustomer['choose_move_to_collecting_process'];
// 		$customer_progress_of_reminder_process = $debitorCustomer['choose_progress_of_reminderprocess'];
//
// 		$profile = array();
//
// 		$case_progress_of_reminder_process = $case['choose_progress_of_reminderprocess'];
// 		if($case_progress_of_reminder_process == 0){
// 			if($customer_progress_of_reminder_process == 0){
// 				$case_progress_of_reminder_process = $creditor_progress_of_reminder_process;
// 			} else {
// 				$case_progress_of_reminder_process = $customer_progress_of_reminder_process - 1;
// 			}
// 		} else {
// 			$case_progress_of_reminder_process--;
// 		}
//
// 		if($case['reminder_profile_id'] > 0){
// 			$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ?";
// 			$o_query = $o_main->db->query($s_sql, array($case['reminder_profile_id']));
// 			$profile = $o_query ? $o_query->row_array() : array();
// 		}
// 		if($profile){
// 			$s_sql = "SELECT * FROM creditor_reminder_custom_profile_values WHERE creditor_reminder_custom_profile_id = ?";
// 			$o_query = $o_main->db->query($s_sql, array($profile['id']));
// 			$unprocessed_profile_values = $o_query ? $o_query->result_array() : array();
//
// 			$profile_values = array();
// 			foreach($unprocessed_profile_values as $unprocessed_profile_value) {
// 				$profile_values[$profile['reminder_process_id']][$unprocessed_profile_value['collecting_cases_process_step_id']] = $unprocessed_profile_value;
// 			}
//
// 			$without_fee = 1;
//
// 			$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE id = ? ORDER BY sortnr ASC";
// 			$o_query = $o_main->db->query($s_sql, array($claimletter['step_id']));
// 			$step = ($o_query ? $o_query->row_array() : array());
//
// 			$profile_value = $profile_values[$process['id']][$step['id']];
// 			$fee_added = false;
// 			if(!$case['doNotAddLateFee']) {
// 				$doNotAddFee = $step['doNotAddFee'];
// 				if($profile_value['doNotAddFee'] > 0){
// 					$doNotAddFee = $profile_value['doNotAddFee'] - 1;
// 				}
// 				if(!$doNotAddFee) {
// 					if($step['reminder_transaction_text'] != "") {
// 						$without_fee = 0;
// 					}
// 				}
// 			}
// 			$doNotAddInterest = $step['doNotAddInterest'];
// 			if($profile_value['doNotAddInterest'] > 0){
// 				$doNotAddInterest = $profile_value['doNotAddInterest'] - 1;
// 			}
// 			if(!$doNotAddInterest) {
// 				$without_fee = 0;
// 			}
// 			var_dump($without_fee);
// 			if($without_fee) {
// 				$s_sql = "UPDATE collecting_cases_claim_letter SET without_fee = ? WHERE id = ?";
// 				$o_query = $o_main->db->query($s_sql, array($without_fee, $claimletter['id']));
// 			}
// 		}
// 	}
// }
// if($variables->loggID == "byamba@dcode.no") {
// 	$s_sql = "SELECT * FROM collecting_cases WHERE content_status < 2";
// 	$o_query = $o_main->db->query($s_sql);
// 	$collecting_cases = $o_query ? $o_query->result_array() : array();
// 	foreach($collecting_cases as $case){
//
// 		$s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND collectingcase_id = ? AND creditor_id = ? ORDER BY created DESC";
// 		$o_query = $o_main->db->query($s_sql, array($case['id'], $case['creditor_id']));
// 		$invoice = ($o_query ? $o_query->row_array() : array());
//
// 		$s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment') AND link_id = ? AND creditor_id = ?";
// 		$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
// 		$invoice_payments = ($o_query ? $o_query->result_array() : array());
//
// 		$s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND link_id = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%\_%' ORDER BY created DESC";
// 		$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
// 		$claim_transactions = ($o_query ? $o_query->result_array() : array());
//
// 		if($invoice['open'] == 0) {
// 			$totalPayments = 0;
// 			foreach($invoice_payments as $invoice_payment) {
// 				$totalPayments += $invoice_payment['amount'];
// 			}
// 			$totalFeeCharged = 0;
// 			foreach($claim_transactions as $claim_transaction){
// 				$totalFeeCharged += $claim_transaction['amount'];
// 			}
// 			$feeAmount = ($invoice['amount'] + $totalPayments)*(-1);
//
// 			if($feeAmount < 0) {
// 				$feeAmount = 0;
// 			} else if($feeAmount > $totalFeeCharged) {
// 				$feeAmount = $totalFeeCharged;
// 			}
// 			$s_sql = "UPDATE collecting_cases SET fee_income='".$o_main->db->escape_str($feeAmount)."' WHERE id = '".$o_main->db->escape_str($case['id'])."'";
// 			$o_query = $o_main->db->query($s_sql);
// 		}
//
// 		$s_sql = "SELECT * FROM customer WHERE id = ?";
// 		$o_query = $o_main->db->query($s_sql, array($case['debitor_id']));
// 		$debitorCustomer = $o_query ? $o_query->row_array() : array();
//
// 		$customer_move_to_collecting = $debitorCustomer['choose_move_to_collecting_process'];
// 		$customer_progress_of_reminder_process = $debitorCustomer['choose_progress_of_reminderprocess'];
//
// 		$profile = array();
//
// 		$case_progress_of_reminder_process = $case['choose_progress_of_reminderprocess'];
// 		if($case_progress_of_reminder_process == 0){
// 			if($customer_progress_of_reminder_process == 0){
// 				$case_progress_of_reminder_process = $creditor_progress_of_reminder_process;
// 			} else {
// 				$case_progress_of_reminder_process = $customer_progress_of_reminder_process - 1;
// 			}
// 		} else {
// 			$case_progress_of_reminder_process--;
// 		}
//
// 		if($case['reminder_profile_id'] > 0){
// 			$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE id = ?";
// 			$o_query = $o_main->db->query($s_sql, array($case['reminder_profile_id']));
// 			$profile = $o_query ? $o_query->row_array() : array();
// 		}
//
// 		if($profile) {
// 			$s_sql = "SELECT * FROM creditor_reminder_custom_profile_values WHERE creditor_reminder_custom_profile_id = ?";
// 			$o_query = $o_main->db->query($s_sql, array($profile['id']));
// 			$unprocessed_profile_values = $o_query ? $o_query->result_array() : array();
//
// 			$profile_values = array();
// 			foreach($unprocessed_profile_values as $unprocessed_profile_value) {
// 				$profile_values[$profile['reminder_process_id']][$unprocessed_profile_value['collecting_cases_process_step_id']] = $unprocessed_profile_value;
// 			}
//
// 			$s_sql = "SELECT * FROM collecting_cases_process WHERE id = ? ORDER BY sortnr ASC";
// 			$o_query = $o_main->db->query($s_sql, array($profile['reminder_process_id']));
// 			$process = ($o_query ? $o_query->row_array() : array());
// 			if($process){
// 				$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_steps.collecting_cases_process_id = ? ORDER BY sortnr ASC";
// 				$o_query = $o_main->db->query($s_sql, array($process['id']));
// 				$steps = ($o_query ? $o_query->result_array() : array());
//
// 				$original_without_fee_reminders_to_be_billed_when_closed = 0;
// 				foreach($steps as $step) {
// 					$profile_value = $profile_values[$process['id']][$step['id']];
// 					$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_id = '".$o_main->db->escape_str($step['collecting_cases_process_id'])."' AND sortnr < '".$o_main->db->escape_str($step['sortnr'])."' ORDER BY sortnr DESC";
// 					$o_query = $o_main->db->query($s_sql);
// 					$previous_step = $o_query ? $o_query->row_array() : array();
//
// 					$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_id = '".$o_main->db->escape_str($step['collecting_cases_process_id'])."' AND sortnr > '".$o_main->db->escape_str($step['sortnr'])."' ORDER BY sortnr ASC";
// 					$o_query = $o_main->db->query($s_sql);
// 					$next_step = $o_query ? $o_query->row_array() : array();
//
// 					$fee_added = false;
// 					if(!$case['doNotAddLateFee']) {
// 						$doNotAddFee = $step['doNotAddFee'];
// 						if($profile_value['doNotAddFee'] > 0){
// 							$doNotAddFee = $profile_value['doNotAddFee'] - 1;
// 						}
// 						if(!$doNotAddFee) {
// 							if($step['reminder_transaction_text'] != "") {
// 								$without_fee = false;
// 							}
// 						}
// 					}
// 					$doNotAddInterest = $step['doNotAddInterest'];
// 					if($profile_value['doNotAddInterest'] > 0){
// 						$doNotAddInterest = $profile_value['doNotAddInterest'] - 1;
// 					}
// 					if(!$doNotAddInterest) {
// 						$without_fee = false;
// 					}
// 					if($without_fee) {
// 						$original_without_fee_reminders_to_be_billed_when_closed++;
// 					}
// 					if($step['id'] == $case['collecting_cases_process_step_id']) {
// 						break;
// 					}
// 				}
//
// 				$s_sql = "UPDATE collecting_cases SET  without_fee_reminders_to_be_billed_when_closed = '".$o_main->db->escape_str($original_without_fee_reminders_to_be_billed_when_closed)."' WHERE id = '".$o_main->db->escape_str($case['id'])."'";
// 				$o_query = $o_main->db->query($s_sql);
// 			}
// 		}
// 	}
// }

$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list_dates&date_from=".$_SESSION['date_from']."&date_to=".$_SESSION['date_to'];

?>
<div class="filterRow">
	<a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list" style="display: block; margin-bottom:10px; float: left;"><?php echo $formText_BackToDates_outpup;?></a>

	<div class="createReport"><?php echo $formText_CreateReport_output;?></div>
	<div class="clear"></div>
</div>
<?php
if($date == ""){
	echo $formText_MissingDate_output;
	return;
}
?>
<table class="table">
	<tr>
		<th><?php echo $formText_ClientId_output;?></th>
		<th><?php echo $formText_Creditor_output;?></th>
		<th></th>
		<th><?php echo $formText_SentWithoutFees_output;?></th>
		<th><?php echo $formText_FeesForgiven_output;?></th>
		<th><?php echo $formText_FeePayed_output;?></th>
		<th><?php echo $formText_InterestPayed_output;?></th>
		<th><?php echo $formText_TotalPrinted_output;?></th>
		<th><?php echo $formText_TotalEhf_output;?></th>
		<th><?php echo $formText_TotalInterestAndFeeBilled_output;?></th>
		<th></th>
	</tr>
	<?php
	$s_sql = "SELECT collecting_cases_report_24so.*, CONCAT_WS(' ', creditor.companyname) as creditorName, creditor.is_demo creditorDemo, creditor.24sevenoffice_client_id,
	IFNULL(printed_amount_reported, '0000-00-00') AS printed_amount_reported, IFNULL(total_fees_payed_reported, '0000-00-00') AS total_fees_payed_reported,
	IFNULL(sent_without_fees_reported, '0000-00-00') AS sent_without_fees_reported, IFNULL(fees_forgiven_amount_reported, '0000-00-00') AS fees_forgiven_amount_reported
	FROM collecting_cases_report_24so
	LEFT OUTER JOIN creditor ON creditor.id = collecting_cases_report_24so.creditor_id
	WHERE DATE(collecting_cases_report_24so.date) = '".date("Y-m-d", strtotime($date))."' ORDER BY creditor.id";
	$o_query = $o_main->db->query($s_sql);
	$reportlines = $o_query ? $o_query->result_array() : array();
	foreach($reportlines as $reportline) {
		$lettersSentWithoutFeeCount = $reportline['sent_without_fees_amount'];
		$feesForgivenCount = $reportline['fees_forgiven_amount'];

		$total_fee_payed = $reportline['fee_payed_amount'];
		$total_interest_payed = $reportline['interest_payed_amount'];
		$total_printed = $reportline['printed_amount'];
		$total_ehf = $reportline['ehf_amount'];
		?>
		<tr >
			<td><?php echo $reportline['24sevenoffice_client_id'];?></td>
			<td><?php echo $reportline['creditorName'];?></td>
			<td><?php if($reportline['creditorDemo']) echo $formText_Demo_output; ?></td>
			<td><span class="show_reminders_without_fees" data-report-id="<?php echo $reportline['id'];?>"><?php echo $lettersSentWithoutFeeCount;?></span>
			</td>
			<td><span class="show_reminders_fees_forgiven" data-report-id="<?php echo $reportline['id'];?>"><?php echo $feesForgivenCount;?></span>
			</td>
			<td><span class="show_case_fees" data-report-id="<?php echo $reportline['id'];?>"><?php echo number_format($total_fee_payed, 2, ",", " ");?></span>
			</td>
			<td><span class="show_case_fees" data-report-id="<?php echo $reportline['id'];?>"><?php echo number_format($total_interest_payed, 2, ",", " ");?></span>
			</td>
			<td><span class="show_printed" data-report-id="<?php echo $reportline['id'];?>"><?php echo $total_printed;?></span></td>
			<td><span class="show_ehf" data-report-id="<?php echo $reportline['id'];?>"><?php echo $total_ehf;?></span></td>
		
			<td><?php echo number_format($reportline['total_fee_and_interest_billed'], 2, ",", " ");?></td>
			<td>
				<?php
				if($reportline['printed_amount_reported'] != "0000-00-00" && $reportline['total_fees_payed_reported'] != "0000-00-00" &&
				$reportline['sent_without_fees_reported'] != "0000-00-00" && $reportline['fees_forgiven_amount_reported'] != "0000-00-00") {
					if($reportline['is_demo']){
						echo $formText_Demo_output;
					} else{
						echo $formText_Exported_output;
					}
				}
				?>
				<span class="delete_report glyphicon glyphicon-trash" data-id="<?php echo $reportline['id'];?>"></span>
				<span class="edit_report glyphicon glyphicon-pencil" data-id="<?php echo $reportline['id'];?>"></span>

			</td>
		</tr>
		<?php
	}
	?>
</table>
<style>
.show_reminders_with_fees,
.show_reminders_without_fees,
.show_reminders_fees_forgiven,
.show_case_fees,
.show_printed,
.show_ehf {
	cursor: pointer;
	color: #46b2e2;
}
.delete_report {
	cursor: pointer;
	color: #46b2e2;
	float: right;
}
.edit_report {
	cursor: pointer;
	color: #46b2e2;
	float: right;
	margin-right: 10px;
}
</style>
<script type="text/javascript">
$(function() {
	$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
		if(e.target.nodeName == 'TR') fw_load_ajax($(this).data('href'),'',true);
	});
});
$(".delete_report").off("click").on("click", function(){

	var data = {
		report_id: $(this).data("id")
	}
	bootbox.confirm({
		message:"<?php echo $formText_DeleteReport_output?>",
		buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
		callback: function(result){
			if(result)
			{
				ajaxCall("delete_report", data, function(json) {
					var data2 = {
						month: '<?php echo $_GET['month'];?>'
					}
					loadView("list", data2);
				});
			}
		}
	})
})
$(".edit_report").off("click").on("click", function(e){
	e.preventDefault();
	var report_id = $(this).data("id");
	if(report_id > 0) {
		var data = {
			report_id: report_id,
		}
		ajaxCall("edit_report", data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	}
})

$(".show_reminders_with_fees").off("click").on("click", function(e){
	e.preventDefault();
	var report_id = $(this).data("report-id");
	if(report_id > 0) {
		var data = {
			report_id: report_id,
			with_fees:1
		}
		ajaxCall("show_claimletters", data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	}
})
$(".show_reminders_without_fees").off("click").on("click", function(e){
	e.preventDefault();
	var report_id = $(this).data("report-id");
	if(report_id > 0) {
		var data = {
			report_id: report_id,
			without_fees:1
		}
		ajaxCall("show_claimletters", data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	}
})
$(".show_reminders_fees_forgiven").off("click").on("click", function(e){
	e.preventDefault();
	var report_id = $(this).data("report-id");
	if(report_id > 0) {
		var data = {
			report_id: report_id,
			fees_forgiven:1
		}
		ajaxCall("show_claimletters", data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	}
})
$(".show_case_fees").off("click").on("click", function(e){
	e.preventDefault();
	var report_id = $(this).data("report-id");
	if(report_id > 0) {
		var data = {
			report_id: report_id,
			with_fees:1
		}
		ajaxCall("show_claimletters", data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	}
})
$(".show_printed").off("click").on("click", function(e){
	e.preventDefault();
	var report_id = $(this).data("report-id");
	if(report_id > 0) {
		var data = {
			report_id: report_id,
			printed:1
		}
		ajaxCall("show_claimletters", data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	}
})
$(".show_ehf").off("click").on("click", function(e){
	e.preventDefault();
	var report_id = $(this).data("report-id");
	if(report_id > 0) {
		var data = {
			report_id: report_id,
			ehf:1
		}
		ajaxCall("show_claimletters", data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	}
})
function refreshPage(){
	var data = {
		month: $(".date_filter").val()
	}
	loadView("list", data);
}
function updateListByMonth(){
	var data = {
		month: $(".datepicker").val()
	}
	loadView("list", data);
}
$(".datepicker").datepicker({
 	dateFormat: "dd.mm.yy",
	firstDay: 1,
	onClose: function(dateText, inst) {
        updateListByMonth();
    },
})
$(".startMonthPicker").datepicker({
 	dateFormat: "mm-yy",
    changeMonth: true,
    changeYear: true,
    showButtonPanel: true,
    onClose: function(dateText, inst) {
        var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
        var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
        $(this).datepicker('setDate', new Date(year, month, 1));
        updateListByMonth();
    },
    // onClose: function(dateText, inst) {


    //     function isDonePressed(){
    //         return ($('#ui-datepicker-div').html().indexOf('ui-datepicker-close ui-state-default ui-priority-primary ui-corner-all ui-state-hover') > -1);
    //     }

    //     if (isDonePressed()){
    //         var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
    //         var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
    //         $(this).datepicker('setDate', new Date(year, month, 1)).trigger('change');

    //          $('.startMonthPicker').focusout()//Added to remove focus from datepicker input box on selecting date
    //          updateListByMonth();
    //     }
    // },
    beforeShow : function(input, inst) {
        inst.dpDiv.addClass('month_year_datepicker');

        if ((datestr = $(this).val()).length > 0) {
            year = datestr.substring(datestr.length-4, datestr.length);
            month = datestr.substring(0, 2);
            $(this).datepicker('option', 'defaultDate', new Date(year, month-1, 1));
    	}
    }
})
$(".date_filter").off("change").on("change", function(e){
	refreshPage();
})
$(".createReport").off("click").on("click", function(e){
	e.preventDefault();
	var data = {}
	ajaxCall("create_report", data, function(json) {
		$('#popupeditboxcontent').html('');
		$('#popupeditboxcontent').html(json.html);
		out_popup = $('#popupeditbox').bPopup(out_popup_options);
		$("#popupeditbox:not(.opened)").remove();
	});
})
$(".reports").off("click").on("click", function(e){
	loadView("report_history", {});
})
</script>
<style>
.filterRow {
	padding: 10px 5px;
	margin-bottom: 10px;
}
.p_pageContent {
	background: #ffffff;
}
.month_year_datepicker .ui-datepicker-calendar {
    display: none;
}
.startMonth {
	float: left;
}
.createReport {
	color: #46b2e2;
	cursor: pointer;
	float: right;
	margin-left: 10px;
}
.reports {
	float: right;
	color: #46b2e2;
	cursor: pointer;
}
</style>
