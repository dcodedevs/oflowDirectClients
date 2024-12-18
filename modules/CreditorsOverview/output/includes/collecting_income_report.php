
<?php 
$cid = isset($_GET['cid']) ? $_GET['cid'] : 0;

$sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($sql, array($cid));
$creditor = $o_query ? $o_query->row_array() : array();

if($creditor) {
    $s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid;
	
    
	$s_sql = "SELECT cs_bookaccount.* FROM cs_bookaccount
	WHERE cs_bookaccount.content_status < 2 AND cs_bookaccount.number IN (3000, 3001, 3003, 3004, 3100, 3200, 3300, 3500, 3600, 3601, 3602, 3900)
    ORDER BY cs_bookaccount.number ASC";
	$o_query = $o_main->db->query($s_sql);
	$bookaccounts = $o_query ? $o_query->result_array() : array();
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
                        <?php foreach($bookaccounts as $bookaccount) { ?>
                            <th><?php echo $bookaccount['name'];?></th>
                        <?php } ?>
                        <th><?php echo $formText_Sum_output;?></th>
                    </tr>

                    <?php 
                    $s_sql = "SELECT cc.* FROM collecting_company_cases cc
                    WHERE cc.creditor_id = ? AND IFNULL(cc.created, '0000-00-00 00:00:00') <> '0000-00-00 00:00:00' ORDER BY cc.created ASC";
                    $o_query = $o_main->db->query($s_sql, array($creditor['id']));
                    $first_collecting_case =$o_query ? $o_query->row_array() : array();
                    if($first_collecting_case) {                       
                        $first_case_date_time = new DateTime($first_collecting_case['created']);
                        $current_time = new DateTime(date("Y-m-d"));
                        $interval = $current_time->diff($first_case_date_time);
                        $month_back = (($interval->format('%y') * 12) + $interval->format('%m') + (($interval->format('%d') > 0) ? 1 : 0));
                        $month_start_time = strtotime("-".$month_back." months");
                        $total_bookaccount_results = array();
                        $total_row_sum = 0;
                       for($x=0; $x<$month_back; $x++){
                            $month_time = strtotime("+".$x." months", $month_start_time);
                            $month_start = date("Y-m-01", $month_time);
                            $month_end = date("Y-m-t", $month_time);           
                            
	                        $sql_where = " AND ccc.creditor_id = '".$o_main->db->escape_str($creditor['id'])."' 
                            AND cmv.date >= '".$o_main->db->escape_str($month_start)."'
                            AND cmv.date <= '".$o_main->db->escape_str($month_end)."'";                		

                            $s_sql = "SELECT cs_bookaccount.*, SUM(cmt.amount) as totalAmount FROM cs_bookaccount
                            JOIN cs_mainbook_transaction cmt ON cmt.bookaccount_id = cs_bookaccount.id
                            JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
                            JOIN collecting_company_cases ccc ON ccc.id = cmv.case_id
                            WHERE cs_bookaccount.content_status < 2 ".$sql_where."
                            GROUP BY cs_bookaccount.id ORDER BY cs_bookaccount.number ASC";
                            $o_query = $o_main->db->query($s_sql);
                            $bookaccountreports = $o_query ? $o_query->result_array() : array();
                            $result_bookaccounts = array();
                            foreach($bookaccountreports as $bookaccountreport) {
                                $result_bookaccounts[$bookaccountreport['id']] += $bookaccountreport['totalAmount'];                                
                            }
                            $row_sum = 0;
                            ?>
                            <tr>
                                <td><?php echo date("M Y", $month_time);?></td>                                
                                <?php foreach($bookaccounts as $bookaccount) { 
                                    if($bookaccount['id'] == 14) {
                                        $row_sum -= $result_bookaccounts[$bookaccount['id']];                                    
                                    } else {
                                        $row_sum += $result_bookaccounts[$bookaccount['id']];
                                    }
                                    $total_bookaccount_results[$bookaccount['id']] += $result_bookaccounts[$bookaccount['id']];
                                    ?>
                                    <td><?php echo number_format($result_bookaccounts[$bookaccount['id']]*-1, 2, ",", "")?></td>
                                <?php } ?>
                                <td><?php echo number_format($row_sum*-1, 2, ",", ""); ?></td>
                            </tr>
                            <?php
                            $total_row_sum+= $row_sum;

                       }
                    }
                    ?>
                    <tr>
                        <td><?php ?></td>
                        <?php foreach($bookaccounts as $bookaccount) { ?>
                            <td><?php echo number_format($total_bookaccount_results[$bookaccount['id']]*-1, 2, ",", "");?></td>
                        <?php } ?>
                        <td><?php echo number_format($total_row_sum*-1, 2, ",", "");?></td>
                    </tr>
                </table>
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
                        $month_back = (($interval->format('%y') * 12) + $interval->format('%m') + (($interval->format('%d') > 0) ? 1 : 0));
                        $month_start_time = strtotime("-".$month_back." months");

                        for($x=0; $x<$month_back; $x++) {
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
                            WHERE cccl.created >= ? AND cccl.created <= ? AND cc.creditor_id = ? AND IFNULL(cccl.fees_status, 0) = 1";
                            $o_query = $o_main->db->query($s_sql, array($month_start,$month_end, $creditor['id']));
                            $lettersSentWithoutFeeCount = $o_query ? $o_query->num_rows(): 0;

                            $s_sql = "SELECT cccl.* FROM collecting_cases_claim_letter cccl
                            LEFT OUTER JOIN collecting_cases cc ON cc.id = cccl.case_id
                            JOIN collecting_cases_report_24so report ON report.id = cc.billing_report_id
                            WHERE report.date >= ? AND report.date <= ? AND cc.creditor_id = ? AND IFNULL(cc.fees_forgiven, 0) = 1 AND IFNULL(cccl.fees_status, 0) = 0";
                            $o_query = $o_main->db->query($s_sql, array($month_start,$month_end, $creditor['id']));
                            $feesForgivenCount = $o_query ? $o_query->num_rows(): 0;


                            $s_sql = "SELECT cccl.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName, cc.payed_fee_amount, cc.payed_interest_amount, cc.billing_report_id
                            FROM collecting_cases_claim_letter cccl
                            JOIN collecting_cases cc ON cc.id = cccl.case_id
                            JOIN customer c ON c.id = cc.debitor_id
                            JOIN collecting_cases_report_24so report ON report.id = cc.billing_report_id
                            WHERE report.date >= ? AND report.date <= ?  AND cc.creditor_id = ? 
                            AND IFNULL(cc.fees_forgiven, 0) = 0 GROUP BY cc.id";
                            $o_query = $o_main->db->query($s_sql, array($month_start,$month_end, $creditor['id']));
                            $cases = $o_query ? $o_query->result_array() : array();
                            $total_fee_payed = 0;
                            $total_interest_payed = 0;
                            
                            $modifier = 0.5;
                            if($creditor['billing_type'] == 1){
                                $modifier = $creditor['billing_percent']/100;
                            }
                            $total_fee_and_interest_billed = 0;
                            $report_grouped = array();
                            foreach($cases as $case) {
                                $total_fee_payed+=$case['payed_fee_amount'];
                                $total_interest_payed+=$case['payed_interest_amount'];
                                $report_grouped[$case['billing_report_id']]['payed_interest_amount']+=$case['payed_interest_amount'];
                                $report_grouped[$case['billing_report_id']]['payed_fee_amount']+=$case['payed_fee_amount'];
                            }
                            foreach($report_grouped as $report){
                                $total_fee_and_interest_billed += round(($report['payed_fee_amount'] + $report['payed_interest_amount']) * $modifier);
                            }

                            $total_printed = 0;

                            $s_sql = "SELECT cccl.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName, cc.payed_fee_amount, cc.payed_interest_amount
                            FROM collecting_cases_claim_letter cccl
                            JOIN collecting_cases cc ON cc.id = cccl.case_id
                            JOIN collecting_cases_report_24so report ON report.id = cccl.billing_report_id
                            LEFT OUTER JOIN customer c ON c.id = cc.debitor_id
                            WHERE report.date >= ? AND report.date <= ?  AND cc.creditor_id = ? AND IFNULL(cccl.performed_action, 0) = 0 AND cccl.sent_to_external_company = 1 AND cccl.sending_status = 1";
                            $o_query = $o_main->db->query($s_sql, array($month_start,$month_end, $creditor['id']));
                            $total_printed = $o_query ? $o_query->num_rows() : 0;
                            
                            
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
            <div class="page_table_wrapper">
                <span style="font-size: 18px; font-weight: bold; margin-left: 10px;"><?php echo $formText_Total_output?>: <?php echo number_format($total_total_income_amount + $total_row_sum*-1, 2, ",", " ");?></span>
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
                        loadView("collecting_income_report", data);
                    }
                }
                $(this).removeClass('opened');
            }
        };
    </script>
    <?php
}
?>
<style>
    #fw_getynet {
        display: none;
    }
    #fw_account.alternative {
        max-width: 100% !important;
        min-height: auto !important;
        margin-top: 0 !important;
    }
    body.desktop #fw_account.alternative .fw_col.col0 {
        display: none !important;
    }
    #fw_account.alternative .fw_module_head_wrapper {
        display: none !important;
    }
    .p_headerLine {
        display: none;
    }
    .p_container {
        max-width: 100%;
    }
    body.desktop #fw_account.alternative .fw_col.col1 {
        width: 96% !important;
        margin: 0px 1% !important;
        left: 0 !important;
    }
    .p_container .p_containerInner {
        margin-top: 0px !important;
    }
    
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