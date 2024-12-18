<?php 
include(__DIR__."/income_report_functions.php");
$cid = isset($_GET['cid']) ? $_GET['cid'] : 0;

$sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($sql, array($cid));
$creditor = $o_query ? $o_query->row_array() : array();

if($creditor) {
    $s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid;

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
                <table class="table table_nopadding">
                    <tr>
                        <td><?php echo $formText_Month_output;?></td>
                        <td><?php echo $formText_SumOfOriginalMainClaim_output;?></td>
                        <td><?php echo $formText_NumberOfCases_output;?></td>
                        <td><?php echo $formText_NumberOfLettersOnStep1_output;?></td>
                        <td><?php echo $formText_NumberOfLettersOnStep2_output;?></td>
                        <td><?php echo $formText_NumberOfLettersOnStep3_output;?></td>                    
                        <td><?php echo $formText_NumberOfCasesTransferedToCollecting_output;?></td>
                        <td><?php echo $formText_MainclaimPayed_output;?></td>
                        <td><?php echo $formText_InterestPayed_output;?></td>
                        <td><?php echo $formText_FeesPayed_output;?></td>
                        <td><?php echo $formText_PercentOriginalMainclaimPayed_output;?></td>
                        <td><?php echo $formText_NotPayedMainClaim_output;?></td>
                        <td><?php echo $formText_OpenCasesBalance_output;?></td>
                        <td><?php echo $formText_NumberOfOpenCases_output;?></td>
                        <td><?php echo $formText_NumberOfCasesDrawn_output;?></td>
                    </tr>
                    <?php /*
                    <tr>
                        <td><?php echo $formText_Before_output." ".date("M Y", $month_start_time);?></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>*/?>
                    <?php 
                    $total_result = get_income_report($creditor['id']);
                    foreach($total_result as $date => $info_array) {
                        ?>
                        <tr>
                            <td><?php echo $date;?></td>
                            <td><?php echo number_format($info_array['original_main_claim_sum'], 2, ",", "");?></td>
                            <td><?php echo $info_array['cases_started_in_period_count'];?></td>
                            <td><?php echo $info_array['step1_count'];?></td>
                            <td><?php echo $info_array['step2_count'];?></td>
                            <td><?php echo $info_array['step3_count'];?></td>
                            <td><?php echo $info_array['moved_to_collecting_count'];?></td>
                            <td><?php echo number_format($info_array['mainclaim_payed'], 2, ",", "");?></td>
                            <td><?php echo number_format($info_array['interest_payed'], 2, ",", "");?></td>
                            <td><?php echo number_format($info_array['fees_payed'], 2, ",", "");?></td>
                            <td><?php echo number_format($info_array['mainclaim_payed_percentage'])."%";?></td>
                            <td><?php echo number_format($info_array['mainclaim_notpayed'], 2, ",", "");?></td>
                            <td><?php 
                            echo number_format($info_array['open_cases_balance'], 2, ",", "")."<br/>";
                            // echo number_format($balance, 2, ",", "")."<br/>";
                            
                            ?></td>
                            <td><?php echo $info_array['open_cases_count'];?></td>
                            <td></td>
                        </tr>
                        <?php
                    }                    
                    ?>
                </table>
                <div class="table_title"><?php echo $formText_CollectingCompanyCases_output;?></div>
                <table class="table table_nopadding">
                    <tr>
                        <td><?php echo $formText_Month_output;?></td>
                        <td><?php echo $formText_SumOfOriginalMainClaim_output;?></td>
                        <td><?php echo $formText_NumberOfCases_output;?></td>
                        <td><?php echo $formText_NumberOfOpenCases_output;?></td>
                        <td><?php echo $formText_payedAmountOnMainclaim_output;?></td>
                        <td><?php echo $formText_NotPayedMainClaim_output;?></td>
                        <td><?php echo $formText_PercentOriginalMainclaimPayed_output;?></td>
                    </tr>
                    <?php 
                    $total_result_collecting = get_collecting_income_report($creditor['id']);
                    foreach($total_result_collecting as $date => $info_array){
                        ?>
                        <tr>
                            <td><?php echo $date;?></td>
                            <td><?php echo number_format($info_array['original_main_claim_sum'], 2, ",", "");?></td>
                            <td><?php echo $info_array['collecting_company_cases_count'];?></td>
                            <td><?php echo $info_array['open_cases_count'];?></td>
                            <td><?php echo number_format($info_array['mainclaim_payed'], 2, ",", "");?></td>
                            <td><?php echo number_format($info_array['mainclaim_notpayed'], 2, ",", "");?></td>
                            <td><?php echo number_format($info_array['mainclaim_payed_percentage'], 2, ",", "")?>%</td>
                        </tr>
                        <?php
                    }                    
                    ?>
                </table>
            </div>
            <?php 
            
            ?>
        </div>
    </div>
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
    </style>
    <?php
}
?>