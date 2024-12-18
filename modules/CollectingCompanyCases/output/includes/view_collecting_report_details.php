<?php 

$report_year = $_GET['report_year'] ? $_GET['report_year'] : '';
$report_period = $_GET['report_period'] ? $_GET['report_period'] : '';
$info = $_GET['info'] ? $_GET['info'] : '';

$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=view_collecting_report&report_year=".$report_year."&report_period=".$report_period;

include(__DIR__."/fnc_get_collecting_case_report_data.php");
$v_count = get_collecting_case_report_data($o_main, $report_year, $report_period, $info);

include("collecting_report_entries.php");

?>
<div id="p_container" class="p_container">
    <div class="p_containerInner">
        <div class="p_content">
            <div class="p_pageContent">
                <a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list" style="display: block; margin-bottom:10px;"><?php echo $formText_BackToList_outpup;?></a>
                <div class="p_pageDetails">
					<div class="p_pageDetailsTitle"><?php echo $formText_CollectingStatisticReport_Output;?></div>
                    <div class="p_contentBlock">
                        <?php 
                        echo $v_info_array[$info]['name'].": ".$v_count[$info];

                        if($info == "value_25" || $info == "value_26" || $info == "value_27" || $info == "value_28" || $info == "value_6" || $info == "value_29" || $info == "value_32" ) {

                        ?>
                        <table class="table">
                            <tr>
                                <td><?php echo $formText_CaseId_output?></td>
                                <td><?php echo $formText_ReminderCreated_output?></td>
                                <td><?php echo $formText_CollectingCaseCreated_output?></td>
                            </tr>
                            <?php
                            foreach($v_count[$info."_info"] as $v_result){
                                
                                $edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCases&folderfile=output&folder=output&inc_obj=details&cid=".$v_result['id'];
                                ?>                                
                                <tr>
                                    <td><a href="<?php echo $edit_link;?>" target="_blank"><?php echo $v_result['id'];?></a></td>
                                    <td><?php echo $v_result['warning_case_created_date'];?></td>
                                    <td><?php echo $v_result['collecting_case_created_date'];?></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </table>
                        <?php }  else if($info=="value_7" || $info=="value_8" || $info=="value_30" || $info=="value_33" || $info=="value_31" || $info=="value_34" ) { ?>
                        <table class="table">
                            <tr>
                                <td><?php echo $formText_CaseId_output?></td>
                                <td><?php echo $formText_MainClaimAmount_output?></td>
                                <td><?php echo $formText_TotalClaim_output?></td>
                            </tr>
                            <?php
                            foreach($v_count[$info."_info"] as $v_result) {
                                
                                $edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCases&folderfile=output&folder=output&inc_obj=details&cid=".$v_result['id'];

                                ?>                                
                                <tr>
                                    <td><a href="<?php echo $edit_link;?>" target="_blank"><?php echo $v_result['id'];?></a></td>
                                    <td><?php echo number_format($v_result['main_claim'], 2, ",", " ");?></td>
                                    <td><?php echo number_format($v_result['total_claim'], 2, ",", " ");?></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </table>
                        <?php }  else if($info=="value_31_1" || $info=="value_34_1" || $info=="value_31_2" || $info=="value_34_2" ) { ?>
                        <table class="table">
                            <tr>
                                <td><?php echo $formText_CaseId_output?></td>
                                <td><?php echo $formText_Interest_output?></td>
                                <td><?php echo $formText_NonLegalCost_output?></td>
                            </tr>
                            <?php
                            foreach($v_count[$info."_info"] as $v_result) {
                                
                                $edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCases&folderfile=output&folder=output&inc_obj=details&cid=".$v_result['id'];

                                ?>                                
                                <tr>
                                    <td><a href="<?php echo $edit_link;?>" target="_blank"><?php echo $v_result['id'];?></a></td>
                                    <td><?php echo number_format($v_result['current_case_interest'], 2, ",", " ");?></td>
                                    <td><?php echo number_format($v_result['current_case_non_legal_cost'], 2, ",", " ");?></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </table>
                        <?php }  else if($info=="value_20" || $info=="value_21" || $info=="value_22") { ?>
                        <table class="table">
                            <tr>
                                <td><?php echo $formText_VoucherId_output?></td>
                                <td><?php echo $formText_CaseId_output?></td>
                                <td><?php echo $formText_CollectedMainClaim_output?></td>
                                <td><?php echo $formText_CollectedInterest_output?></td>
                                <td><?php echo $formText_CollectedNonLegalCost_output?></td>
                            </tr>
                            <?php
                            foreach($v_count[$info."_info"] as $v_result) {                                
                                $edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCases&folderfile=output&folder=output&inc_obj=details&cid=".$v_result['case_id'];

                                ?>                                
                                <tr>
                                    <td><?php echo $v_result['id'];?></td>
                                    <td><a href="<?php echo $edit_link;?>" target="_blank"><?php echo $v_result['case_id'];?></a></td>
                                    <td class="rightAligned"><?php echo number_format($v_result['current_case_main_claim'], 2, ",", " ");?></td>
                                    <td class="rightAligned"><?php echo number_format($v_result['current_case_interest'], 2, ",", " ");?></td>
                                    <td class="rightAligned"><?php echo number_format($v_result['current_case_non_legal_cost'], 2, ",", " ");?></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </table>
                        <?php }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>