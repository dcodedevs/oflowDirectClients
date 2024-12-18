
<?php 
$cid = isset($_GET['cid']) ? $_GET['cid'] : 0;
$dateFrom = isset($_GET['dateFrom']) ? $_GET['dateFrom']: '';
$dateTo= isset($_GET['dateTo']) ? $_GET['dateTo']: '';

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
                    $total_bookaccount_results = array();
                    $s_sql = "SELECT * FROM creditor_report_collecting
                    WHERE creditor_id = ? ORDER BY date ASC";
                    $o_query = $o_main->db->query($s_sql, array($creditor['id']));
                    $creditor_reports_collecting = $o_query ? $o_query->result_array() : array();
                    foreach($creditor_reports_collecting as $creditor_report_collecting){                        
                        $month_start = date("Y-m-01", strtotime($creditor_report_collecting['date']));
                        $month_end = date("Y-m-t", strtotime($creditor_report_collecting['date']));           
                        
                        $sql_where = " AND ccc.creditor_id = '".$o_main->db->escape_str($creditor['id'])."' 
                        AND cmv.date > '".$o_main->db->escape_str($month_start)."'
                        AND cmv.date <= '".$o_main->db->escape_str($month_end)."'";                		
                        
                        $row_sum = 0;
                        ?>
                        <tr>
                            <td><?php echo date("M Y", strtotime($creditor_report_collecting['date']));?></td>                                
                            <?php foreach($bookaccounts as $bookaccount) {   
                                $value = 0;
                                if($bookaccount['id'] == 6) {
                                    $value = $creditor_report_collecting['fee_cwo'];
                                } else if($bookaccount['id'] == 5) {
                                    $value = $creditor_report_collecting['fee_cw'];
                                } else if($bookaccount['id'] == 4) {
                                    $value = $creditor_report_collecting['fee_pwo'];
                                } else if($bookaccount['id'] == 3) {
                                    $value = $creditor_report_collecting['fee_pw'];
                                } else if($bookaccount['id'] == 7) {
                                    $value = $creditor_report_collecting['forsinkelsesrente'];
                                } else if($bookaccount['id'] == 2) {
                                    $value = $creditor_report_collecting['purregebyr'];
                                } else if($bookaccount['id'] == 29) {
                                    $value = $creditor_report_collecting['overbetalt'];
                                } else if($bookaccount['id'] == 19) {
                                    $value = $creditor_report_collecting['hovedstol'];
                                } else if($bookaccount['id'] == 13) {
                                    $value = $creditor_report_collecting['saerskilt'];
                                } else if($bookaccount['id'] == 12) {
                                    $value = $creditor_report_collecting['avdragsgebyr'];
                                } else if($bookaccount['id'] == 11) {
                                    $value = $creditor_report_collecting['omkostningesrente'];
                                } else if($bookaccount['id'] == 14) {
                                    $value = $creditor_report_collecting['mva'];
                                }
                                if($bookaccount['id'] == 14) {
                                    $row_sum -= $value;                                    
                                } else {                            
                                    $row_sum += $value;
                                }
                                $total_bookaccount_results[$bookaccount['id']] += $value;
                                ?>
                                <td><?php echo number_format($value, 2, ",", "")?></td>
                            <?php } ?>
                            <td><?php echo number_format($row_sum, 2, ",", ""); ?></td>
                        </tr>
                        <?php
                        $total_row_sum+= $row_sum;

                    }
                    ?>
                    <tr>
                        <td><?php ?></td>
                        <?php foreach($bookaccounts as $bookaccount) { ?>
                            <td><?php echo number_format($total_bookaccount_results[$bookaccount['id']], 2, ",", "");?></td>
                        <?php } ?>
                        <td><?php echo number_format($total_row_sum, 2, ",", "");?></td>
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
                    
                    
                    $s_sql = "SELECT * FROM creditor_report_reminder
                    WHERE creditor_id = ? ORDER BY date ASC";
                    $o_query = $o_main->db->query($s_sql, array($creditor['id']));
                    $creditor_reports_reminder = $o_query ? $o_query->result_array() : array();

                    foreach($creditor_reports_reminder as $creditor_report_reminder){
                        $total_income_amount = 0;
                        $month_start = date("Y-m-01", strtotime($creditor_report_reminder['date']));
                        $month_end = date("Y-m-t", strtotime($creditor_report_reminder['date']));

                        $lettersSentWithoutFeeCount = $creditor_report_reminder['sent_without_fees'];
                        $feesForgivenCount = $creditor_report_reminder['fees_forgiven'];
                        $total_fee_payed = $creditor_report_reminder['fees_payed'];
                        $total_interest_payed = $creditor_report_reminder['interest_payed'];
                        $total_printed = $creditor_report_reminder['total_printed'];
                        $total_fee_and_interest_billed = $creditor_report_reminder['total_interest_and_fee_billed'];

                        
                        $total_print_amount = $creditor_report_reminder['total_print'];                        
                        $total_fees_amount = $creditor_report_reminder['total_fee'];
                        $total_income_amount = $creditor_report_reminder['total_income'];

                       
                        ?>
                        <tr>
                            <td><?php echo date("M Y", strtotime($creditor_report_reminder['date']));?></td>
                            <td><?php echo $lettersSentWithoutFeeCount;?></td>
                            <td><?php echo $feesForgivenCount;?></td>
                            <td><?php echo $total_fee_payed;?></td>
                            <td><?php echo $total_interest_payed;?></td>
                            <td><?php echo $total_printed;?></td>
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
                <span style="font-size: 18px; font-weight: bold; margin-left: 10px;"><?php echo $formText_Total_output?>: <?php echo number_format($total_total_income_amount + $total_row_sum, 2, ",", " ");?></span>
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
                        loadView("income_report", data);
                    }
                }
                $(this).removeClass('opened');
            }
        };
        $(function(){
            $(".datepicker").datepicker({
                "dateFormat": "dd.mm.yy"
            });
            $(".dateFrom").change(function(){
                var dateFrom = $(".dateFrom").val();
                var dateTo = $(".dateTo").val();
                if(dateFrom != "" && dateTo != ""){
                    var data = {
                        cid: '<?php echo $cid;?>',
                        dateFrom: dateFrom,
                        dateTo: dateTo,
                    }
                    loadView("income_report", data);
                }
            })
            $(".dateTo").change(function(){
                var dateFrom = $(".dateFrom").val();
                var dateTo = $(".dateTo").val();
                if(dateFrom != "" && dateTo != ""){
                    var data = {
                        cid: '<?php echo $cid;?>',
                        dateFrom: dateFrom,
                        dateTo: dateTo,
                    }
                    loadView("income_report", data);
                }
            })
        })
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
    .date_wrapper {
        padding: 10px 0px;
    }
</style>