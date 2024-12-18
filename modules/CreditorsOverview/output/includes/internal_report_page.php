<?php 
$cid = isset($_GET['cid']) ? $_GET['cid'] : 0;

$sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($sql, array($cid));
$creditor = $o_query ? $o_query->row_array() : array();

if($creditor) {
    $s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid;
	$month_back = 6;
    $month_start_time = strtotime("-".$month_back." months");
    ?>
    <div id="p_container" class="p_container <?php echo $folderName; ?>">
		<div class="p_containerInner">
			<div class="p_content">
				<div class="p_pageContent">
					<a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list" style="display: block; margin-bottom:10px; float: left;"><?php echo $formText_BackToCreditor_outpup;?></a>
					<div class="clear"></div>
				</div>
			</div>
			<div class="creditor_info_row title_row">
				<?php echo $formText_CreditorName_output;?>:
				<b><?php echo $creditor['companyname'];?></b>
			</div>
            <div class="page_table_wrapper">
                <table class="table">
                    <tr>
                        <th><?php echo $formText_Date_output;?></th>
                        <th><?php echo $formText_SentWithoutFees_output;?></th>
                        <th><?php echo $formText_FeesForgiven_output;?></th>
                        <th><?php echo $formText_FeePayed_output;?></th>
                        <th><?php echo $formText_InterestPayed_output;?></th>
                        <th><?php echo $formText_TotalPrinted_output;?></th>
                        <th><?php echo $formText_TotalInterestAndFeeBilled_output;?></th>
                        <th><?php echo $formText_TotalPrint_output;?></th>
                        <th><?php echo $formText_TotalFee_output;?></th>
                        <th><?php echo $formText_TotalIncome_output;?></th>
                    </tr>
                    <?php 
                    $s_sql = "SELECT cc.* FROM collecting_cases cc
                     WHERE cc.creditor_id = ? AND IFNULL(cc.created, '0000-00-00 00:00:00') <> '0000-00-00 00:00:00' ORDER BY cc.created ASC";
                    $o_query = $o_main->db->query($s_sql, array($creditor['id']));
                    $first_collecting_case =$o_query ? $o_query->row_array() : array();
                    if($first_collecting_case) {                        
                        $first_case_date_time = new DateTime($first_collecting_case['created']);
                        $current_time = new DateTime(date("Y-m-d"));
                        $interval = $current_time->diff($first_case_date_time);
                        $month_back = ($interval->format('%y') * 12) + $interval->format('%m');
                        $month_start_time = strtotime("-".$month_back." months");

                        for($x=0; $x<$month_back; $x++){
                            $total_income_amount = 0;
                            $month_time = strtotime("+".$x." months", $month_start_time);
                            $month_start = date("Y-m-01", $month_time);
                            $month_end = date("Y-m-t", $month_time);
                            $price_per_print = 0;
                            $price_per_fees = 0;

                            $s_sql = "SELECT cpl.* FROM creditor_price_list cpl WHERE cpl.date_from <= ? ORDER BY cpl.date_from DESC";
                            $o_query = $o_main->db->query($s_sql, array($month_start));
                            $price_per_print_item = $o_query ? $o_query->row_array(): array();
                            if($price_per_print_item){
                                $price_per_print = $price_per_print_item['price_per_print'];
                                $price_per_fees = $price_per_print_item['price_per_fee'];
                            }

                            $s_sql = "SELECT cccl.id FROM collecting_cases_claim_letter cccl
                            JOIN collecting_cases cc ON cc.id = cccl.case_id
                            WHERE cc.reminder_process_started >= ? AND cc.reminder_process_started <= ? AND cc.creditor_id = ? AND IFNULL(cccl.fees_status, 0) = 1";
                            $o_query = $o_main->db->query($s_sql, array($month_start,$month_end, $creditor['id']));
                            $lettersSentWithoutFeeCount = $o_query ? $o_query->num_rows(): 0;

                            $s_sql = "SELECT cccl.* FROM collecting_cases_claim_letter cccl
                            JOIN collecting_cases cc ON cc.id = cccl.case_id
                            WHERE cc.reminder_process_started >= ? AND cc.reminder_process_started <= ? AND cc.creditor_id = ? AND IFNULL(cc.fees_forgiven, 0) = 1 AND IFNULL(cccl.fees_status, 0) = 0";
                            $o_query = $o_main->db->query($s_sql, array($month_start,$month_end, $creditor['id']));
                            $feesForgivenCount = $o_query ? $o_query->num_rows(): 0;


                            $s_sql = "SELECT cccl.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName, cc.payed_fee_amount, cc.payed_interest_amount
                            FROM collecting_cases_claim_letter cccl
                            JOIN collecting_cases cc ON cc.id = cccl.case_id
                            JOIN customer c ON c.id = cc.debitor_id
                            WHERE cc.reminder_process_started >= ? AND cc.reminder_process_started <= ? AND cc.creditor_id = ? 
                            AND IFNULL(cc.fees_forgiven, 0) = 0 GROUP BY cc.id";
                            $o_query = $o_main->db->query($s_sql, array($month_start,$month_end, $creditor['id']));
                            $cases = $o_query ? $o_query->result_array() : array();
                            $total_fee_payed = 0;
                            $total_interest_payed = 0;
                            foreach($cases as $case) {
                                $total_fee_payed+=$case['payed_fee_amount'];
                                $total_interest_payed+=$case['payed_interest_amount'];
                            }

                            $total_printed = 0;

                            $s_sql = "SELECT cccl.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName, cc.payed_fee_amount, cc.payed_interest_amount
                            FROM collecting_cases_claim_letter cccl
                            JOIN collecting_cases cc ON cc.id = cccl.case_id
                            LEFT OUTER JOIN customer c ON c.id = cc.debitor_id
                            WHERE cc.reminder_process_started >= ? AND cc.reminder_process_started <= ? AND cc.creditor_id = ? AND IFNULL(cccl.performed_action, 0) = 0 AND cccl.sent_to_external_company = 1 AND cccl.sending_status = 1";
                            $o_query = $o_main->db->query($s_sql, array($month_start,$month_end, $creditor['id']));
                            $total_printed = $o_query ? $o_query->num_rows() : 0;
                            
                            $modifier = 0.5;
                            if($creditor['billing_type'] == 1){
                                $modifier = $creditor['billing_percent']/100;
                            }
                            $total_fee_and_interest_billed = round(($total_fee_payed + $total_interest_payed) * $modifier);
                            
                            $total_print_amount = $total_printed * $price_per_print;
                            $total_fees_amount = ($lettersSentWithoutFeeCount + $feesForgivenCount)* $price_per_fees;
                            $total_income_amount = $total_fee_and_interest_billed+$total_print_amount+$total_fees_amount;
                            ?>
                            <tr>
                                <td><?php echo date("M Y", $month_time);?></td>
                                <td><div class="show_reminders_without_fees show_button" data-creditor_id="<?php echo $creditor['id']; ?>" data-start_time="<?php echo $month_start;?>" data-end_time="<?php echo $month_end;?>"><?php echo $lettersSentWithoutFeeCount;?></div></td>
                                <td><div class="show_reminders_fees_forgiven show_button" data-creditor_id="<?php echo $creditor['id']; ?>" data-start_time="<?php echo $month_start;?>" data-end_time="<?php echo $month_end;?>"><?php echo $feesForgivenCount;?></div></td>
                                <td><div class="show_case_fees show_button" data-creditor_id="<?php echo $creditor['id']; ?>" data-start_time="<?php echo $month_start;?>" data-end_time="<?php echo $month_end;?>"><?php echo $total_fee_payed;?></div></td>
                                <td><div class="show_case_fees show_button" data-creditor_id="<?php echo $creditor['id']; ?>" data-start_time="<?php echo $month_start;?>" data-end_time="<?php echo $month_end;?>"><?php echo $total_interest_payed;?></div></td>
                                <td><div class="show_printed show_button" data-creditor_id="<?php echo $creditor['id']; ?>" data-start_time="<?php echo $month_start;?>" data-end_time="<?php echo $month_end;?>"><?php echo $total_printed;?></div></td>
                                <td><?php echo $total_fee_and_interest_billed;?></td>
                                <td><?php echo $total_print_amount;?></td>
                                <td><?php echo $total_fees_amount;?></td>
                                <td><?php echo $total_income_amount;?></td>
                            </tr>
                            <?php
                            $total_lettersSentWithoutFeeCount+=$lettersSentWithoutFeeCount;
                            $total_feesForgivenCount+=$feesForgivenCount;
                            $total_total_fee_payed+=$total_fee_payed;
                            $total_total_interest_payed+=$total_interest_payed;
                            $total_total_printed+=$total_printed;
                            $total_total_fee_and_interest_billed+=$total_fee_and_interest_billed;
                            $total_total_print_amount+=$total_print_amount;
                            $total_total_fees_amount+=$total_fees_amount;
                            $total_total_income_amount+=$total_income_amount;
                        }
                    }
                    ?>
                    <tr>
                        <td><?php echo $formText_Total_output;?></td>
                        <td><?php echo $total_lettersSentWithoutFeeCount;?></td>
                        <td><?php echo $total_feesForgivenCount;?></td>
                        <td><?php echo $total_total_fee_payed;?></td>
                        <td><?php echo $total_total_interest_payed;?></td>
                        <td><?php echo $total_total_printed;?></td>
                        <td><?php echo $total_total_fee_and_interest_billed;?></td>
                        <td><?php echo $total_total_print_amount;?></td>
                        <td><?php echo $total_total_fees_amount;?></td>
                        <td><?php echo $total_total_income_amount;?></td>

                    </tr>
                </table> 
            </div>
        </div>
    </div>
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
                        var data = {
                            cid: '<?php echo $cid;?>'
                        }
                        loadView("internal_report_page", data);
                    }
                // window.location.reload();
                }
                $(this).removeClass('opened');
            }
        };
        $(function(){
            $(".show_printed").off("click").on("click", function(e){
                e.preventDefault();
                var report_id = $(this).data("report-id");

                var data = {
                    creditor_id: $(this).data("creditor_id"),
                    start_time: $(this).data("start_time"),
                    end_time: $(this).data("end_time"),
                    printed:1
                }
                ajaxCall("show_claimletters", data, function(json) {
                    $('#popupeditboxcontent').html('');
                    $('#popupeditboxcontent').html(json.html);
                    out_popup = $('#popupeditbox').bPopup(out_popup_options);
                    $("#popupeditbox:not(.opened)").remove();
                });
            })
            $(".show_case_fees").off("click").on("click", function(e){
                e.preventDefault();
                var data = {
                    creditor_id: $(this).data("creditor_id"),
                    start_time: $(this).data("start_time"),
                    end_time: $(this).data("end_time"),
                    with_fees:1
                }
                ajaxCall("show_claimletters", data, function(json) {
                    $('#popupeditboxcontent').html('');
                    $('#popupeditboxcontent').html(json.html);
                    out_popup = $('#popupeditbox').bPopup(out_popup_options);
                    $("#popupeditbox:not(.opened)").remove();
                });
            })
            $(".show_reminders_with_fees").off("click").on("click", function(e){
                e.preventDefault();
                var data = {
                    creditor_id: $(this).data("creditor_id"),
                    start_time: $(this).data("start_time"),
                    end_time: $(this).data("end_time"),
                    with_fees:1
                }
                ajaxCall("show_claimletters", data, function(json) {
                    $('#popupeditboxcontent').html('');
                    $('#popupeditboxcontent').html(json.html);
                    out_popup = $('#popupeditbox').bPopup(out_popup_options);
                    $("#popupeditbox:not(.opened)").remove();
                });
            })
            $(".show_reminders_without_fees").off("click").on("click", function(e){
                e.preventDefault();
                var data = {
                    creditor_id: $(this).data("creditor_id"),
                    start_time: $(this).data("start_time"),
                    end_time: $(this).data("end_time"),
                    without_fees:1
                }
                ajaxCall("show_claimletters", data, function(json) {
                    $('#popupeditboxcontent').html('');
                    $('#popupeditboxcontent').html(json.html);
                    out_popup = $('#popupeditbox').bPopup(out_popup_options);
                    $("#popupeditbox:not(.opened)").remove();
                });
            })
            $(".show_reminders_fees_forgiven").off("click").on("click", function(e){
                e.preventDefault();
                var data = {
                    creditor_id: $(this).data("creditor_id"),
                    start_time: $(this).data("start_time"),
                    end_time: $(this).data("end_time"),
                    fees_forgiven: 1
                }
                ajaxCall("show_claimletters", data, function(json) {
                    $('#popupeditboxcontent').html('');
                    $('#popupeditboxcontent').html(json.html);
                    out_popup = $('#popupeditbox').bPopup(out_popup_options);
                    $("#popupeditbox:not(.opened)").remove();
                });
            })
        })
    </script>
    <style>
        .table_nopadding > tbody > tr > td,
        .table_nopadding > tbody > tr > th {
            padding: 2px;
        }
        .page_table_wrapper {
            background: #fff;
        }
        .table_title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .show_button {
            cursor: pointer;
            color: #46b2e2;
        }
    </style>
    <?php
}
?>