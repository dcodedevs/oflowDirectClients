<?php
$page = 1;
require_once __DIR__ . '/list_btn.php';
$date_from = isset($_GET['date_from']) && $_GET['date_from'] != "" ? date("d.m.Y", strtotime($_GET['date_from'])) : date("01.m.Y");
$date_to = isset($_GET['date_to']) && $_GET['date_to'] != "" ? date("d.m.Y", strtotime($_GET['date_to'])) : date("t.m.Y");

$_SESSION['date_from'] = $date_from;
$_SESSION['date_to'] = $date_to;

?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
				<div class="filterRow">
					<div class="">
						<?php echo $formText_DateFrom_output;?>
						<input type="text" class="dateFrom datepicker" name="date_from" value="<?php echo $date_from?>"/>
						<?php echo $formText_DateTo_output;?>
						<input type="text" class="dateTo datepicker" name="date_to" value="<?php echo $date_to?>"/>
					</div>
					<div class="exportReport"><?php echo $formText_ExportReported_output;?></div>
					<div class="createReport"><?php echo $formText_CreateReport_output;?></div>
					<div class="clear"></div>
				</div>
				<?php if($variables->loggID == "byamba@dcode.no") { ?>
					<div class="check_data">Check data</div>
				<?php } ?>

				<table class="table">
					<tr>
						<th><?php echo $formText_Date_output;?></th>
						<th><?php echo $formText_SentWithoutFees_output;?></th>
						<th><?php echo $formText_FeesForgiven_output;?></th>
						<th><?php echo $formText_FeePayed_output;?></th>
						<th><?php echo $formText_InterestPayed_output;?></th>
						<th><?php echo $formText_TotalPrinted_output;?></th>
						<th><?php echo $formText_TotalEhf_output;?></th>
						<th><?php echo $formText_TotalFeeAndInterestBilled_output;?></th>
						<th></th>
					</tr>
					<?php

					$s_sql = "SELECT * FROM collecting_cases_report_24so WHERE date >= ? AND date <= ? GROUP BY date ORDER BY date DESC";
					$o_query = $o_main->db->query($s_sql, array(date("Y-m-d", strtotime($date_from)), date("Y-m-d", strtotime($date_to))));
					$report_dates = $o_query ? $o_query->result_array() : array();

					$summary_interest_payed = 0;
					$summary_fee_payed = 0;
					$summary_feesForgivenCount = 0;
					$summary_lettersSentWithoutFeeCount = 0;
					$summary_total_printed = 0;
					$summary_total_fees_and_interest = 0;

					foreach($report_dates as $report_date) {
						$s_sql = "SELECT collecting_cases_report_24so.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as creditorName,
						IFNULL(printed_amount_reported, '0000-00-00') AS printed_amount_reported, IFNULL(total_fees_payed_reported, '0000-00-00') AS total_fees_payed_reported,
						IFNULL(sent_without_fees_reported, '0000-00-00') AS sent_without_fees_reported, IFNULL(fees_forgiven_amount_reported, '0000-00-00') AS fees_forgiven_amount_reported
						FROM collecting_cases_report_24so
						LEFT OUTER JOIN creditor ON creditor.id = collecting_cases_report_24so.creditor_id
						LEFT OUTER JOIN customer c ON c.id = creditor.customer_id
						WHERE DATE(collecting_cases_report_24so.date) = '".date("Y-m-d", strtotime($report_date['date']))."'
						ORDER BY creditor.id";
						$o_query = $o_main->db->query($s_sql);
						$reportlines = $o_query ? $o_query->result_array() : array();

						$global_interest_payed = 0;
						$global_fee_payed = 0;
						$global_feesForgivenCount = 0;
						$global_lettersSentWithoutFeeCount = 0;
						$global_total_printed = 0;
						$global_fee_and_interest_billed = 0;
						$exportedCount = 0;
						$global_total_ehf = 0;

						foreach($reportlines as $reportline) {
							$global_interest_payed+= $reportline['interest_payed_amount'];
							$global_fee_payed+= $reportline['fee_payed_amount'];
							$global_feesForgivenCount += $reportline['fees_forgiven_amount'];
							$global_total_ehf += $reportline['ehf_amount'];
							$global_lettersSentWithoutFeeCount += $reportline['sent_without_fees_amount'];
							$global_total_printed += $reportline['printed_amount'];
							$global_fee_and_interest_billed += $reportline['total_fee_and_interest_billed'];

							if($reportline['printed_amount_reported'] != "0000-00-00" && $reportline['total_fees_payed_reported'] != "0000-00-00" &&
							$reportline['sent_without_fees_reported'] != "0000-00-00" && $reportline['fees_forgiven_amount_reported'] != "0000-00-00") {
								$exportedCount++;
							}
						}


						$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list&month=".$report_date['date'];

						?>
						<tr class="output-click-helper" data-href="<?php echo $s_edit_link;?>" >
							<td><?php echo date("d.m.Y", strtotime($report_date['date']));?></td>
							<td><?php echo $global_lettersSentWithoutFeeCount;?></td>
							<td><?php echo $global_feesForgivenCount;?></td>
							<td><?php echo number_format($global_fee_payed, 2, ",", " ");?></td>
							<td><?php echo number_format($global_interest_payed, 2, ",", " ");?></td>
							<td><?php echo $global_total_printed;?></td>
							<td><?php echo $global_total_ehf;?></td>
							<td><?php echo number_format($global_fee_and_interest_billed, 2, ",", " ");?></td>
							<td>
								<span class="delete_global_report glyphicon glyphicon-trash" data-date="<?php echo $report_date['date'];?>"></span>
								<?php
								echo $exportedCount."/".count($reportlines)." ".$formText_Exported_output;
								if($exportedCount != count($reportlines)){
								?>
									<span class="exportTo24Seven" data-date="<?php echo $report_date['date'];?>"><?php echo $formText_ExportTo24So_output;?></span>
								<?php } ?>
							</td>
						</tr>
						<?php
						$summary_interest_payed += $global_interest_payed;
						$summary_fee_payed += $global_fee_payed;
						$summary_feesForgivenCount += $global_feesForgivenCount;
						$summary_lettersSentWithoutFeeCount += $global_lettersSentWithoutFeeCount;
						$summary_total_printed += $global_total_printed;
						$summary_total_ehf += $global_total_ehf;
						$summary_total_fees_and_interest += $global_fee_and_interest_billed;
					}
					?>
					<tr>
						<td><b><?php echo $formText_Summary;?></b></td>
						<td><b><?php echo $summary_lettersSentWithoutFeeCount;?></b></td>
						<td><b><?php echo $summary_feesForgivenCount;?></b></td>
						<td><b><?php echo number_format($summary_fee_payed, 2, ",", " ")?></b></td>
						<td><b><?php echo number_format($summary_interest_payed, 2, ",", " ");?></b></td>
						<td><b><?php echo $summary_total_printed;?></b></td>
						<td><b><?php echo $summary_total_ehf;?></b></td>
						<td><b><?php echo number_format($summary_total_fees_and_interest, 2, ",", " ");?></b></td>
						<td></td>
					</tr>
				</table>
				<style>
				.show_reminders_with_fees,
				.show_reminders_without_fees,
				.show_reminders_fees_forgiven,
				.show_case_fees,
				.show_printed,
				.exportTo24Seven,
				.delete_global_report {
					cursor: pointer;
					color: #46b2e2;
				}
				.delete_report {
					cursor: pointer;
					color: #46b2e2;
					float: right;
				}
				.exportTo24Seven {
					margin-left: 10px;
				}
				.exportReport {
				    color: #46b2e2;
					cursor: pointer;
					margin-left: 20px;
					float: right;
				}
				</style>
				<script type="text/javascript">


				function submit_post_via_hidden_form(url, params) {
					var f = $("<form method='POST' target='_blank' style='display:none;'></form>").attr({
						action: url
					}).appendTo(document.body);
					for (var i in params) {
						if (params.hasOwnProperty(i)) {
							$('<input type="hidden" />').attr({
								name: i,
								value: params[i]
							}).appendTo(f);
						}
					}
					f.submit();
					f.remove();
				}
				$(function() {
					$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
						if(e.target.nodeName == 'TD') fw_load_ajax($(this).data('href'),'',true);
					});
					$('.exportReport').on('click', function(e) {
				        e.preventDefault();
						var data = {
							fwajax: 1,
							fw_nocss: 1
						};
						submit_post_via_hidden_form('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=exportToCsv&date_from=".$date_from."&date_to=".$date_to; ?>', data);
				    });
				});
				$(".exportTo24Seven").off("click").on("click", function(){
					var data = {
						date: $(this).data("date"),
						action: "export"
					}
					ajaxCall("exportTo24Seven", data, function(json) {
						if(json.html != ""){
							$('#popupeditboxcontent').html('');
							$('#popupeditboxcontent').html(json.html);
							out_popup = $('#popupeditbox').bPopup(out_popup_options);
							$("#popupeditbox:not(.opened)").remove();
						} else {
							var data2 = {
								date_from: $(".dateFrom").val(),
								date_to: $(".dateTo").val()
							}
							loadView("list_dates", data2);
						}
					});
				})
				$(".check_data").off("click").on("click", function(){
					var data = {
						action: "check_data"
					}
					ajaxCall("exportTo24Seven", data, function(json) {
						$('#popupeditboxcontent').html('');
						$('#popupeditboxcontent').html(json.html);
						out_popup = $('#popupeditbox').bPopup(out_popup_options);
						$("#popupeditbox:not(.opened)").remove();
					});
				})

				$(".delete_global_report").off("click").on("click", function(){

					var data = {
						date: $(this).data("date")
					}
					bootbox.confirm({
						message:"<?php echo $formText_DeleteAllReportsFromGivenDate_output?>",
						buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
						callback: function(result){
							if(result)
							{
								ajaxCall("delete_report", data, function(json) {
									var data2 = {
										date_from: $(".dateFrom").val(),
										date_to: $(".dateTo").val()
									}
									loadView("list_dates", data2);
								});
							}
						}
					})
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
				function refreshPage(){
					var data = {
						date_from: $(".dateFrom").val(),
						date_to: $(".dateTo").val()
					}
					loadView("list_dates", data);
				}
				function updateListByMonth(){
					var data = {
						date_from: $(".dateFrom").val(),
						date_to: $(".dateTo").val()
					}
					loadView("list_dates", data);
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
				.check_data {
					cursor: pointer;
				}
				</style>

			</div>
		</div>
	</div>
</div>

<?php $list_filter = $_GET['list_filter'] ? $_GET['list_filter'] : 'all'; ?>
<script type="text/javascript">
var out_popup;
var out_popup_options={
	follow: [true, true],
	followSpeed: 0,
	fadeSpeed: 0,
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function(){
		$(this).addClass('opened');
		//$(this).find('.b-close').on('click', function(){out_popup.close();});
	},
	onClose: function(){
		if($(this).is('.close-reload')) {
			var redirectUrl = $(this).data("redirect");
			if(redirectUrl !== undefined && redirectUrl != ""){
				document.location.href = redirectUrl;
			} else {
				var data2 = {
					date_from: $(".dateFrom").val(),
					date_to: $(".dateTo").val()
				}
            	loadView("list_dates", data2);
            }
          // window.location.reload();
        }
		if($(this).is('.close-reload-date')) {
			var data2 = {
				date_from: $(".dateFrom").val(),
				date_to: $(".dateTo").val()
			}
	    	loadView("list_dates", data2);
          // window.location.reload();
        }
		$(this).removeClass('opened');
	}
};
</script>
