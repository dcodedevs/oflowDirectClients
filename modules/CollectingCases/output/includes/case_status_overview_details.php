<?php 
$step_id = $_GET['step_id'] ?? 0;
$list_filter = $_GET['list_filter'] ?? 'open';
$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=case_status_overview&list_filter=".$list_filter;

$sql = "SELECT ccps.*, ccp.fee_level_name FROM collecting_cases_process_steps ccps
JOIN collecting_cases_process ccp ON ccp.id = ccps.collecting_cases_process_id WHERE ccps.id = ? ORDER BY ccp.sortnr, ccps.sortnr";
$o_query = $o_main->db->query($sql, array($step_id));
$step_info = $o_query ? $o_query->row_array() : array();


$s_sql_where = "ct.open = 1 AND IFNULL(cc.status, 0) = 0";
if($list_filter == "closed") {
	$s_sql_where = "(ct.open = 0 OR IFNULL(cc.status, 0) = 1)";
}

$sql = "SELECT cc.id, cccl.created as letter_created_date, cc.status, cc.sub_status, cccl.pdf, cccl.id as letterId,
c.companyname as creditorName, d.name as debitorName, ct.tab_status, ct.case_balance, ct.open  FROM collecting_cases cc
JOIN creditor_transactions ct ON ct.collectingcase_id = cc.id 
JOIN collecting_cases_claim_letter cccl ON cccl.case_id = cc.id
JOIN creditor c ON c.id = cc.creditor_id
JOIN customer d ON d.id = cc.debitor_id
WHERE ".$s_sql_where." AND cc.collecting_cases_process_step_id = ?
GROUP BY cc.id
ORDER BY cccl.created ASC";
$o_query = $o_main->db->query($sql, array($step_info['id']));
$connected_cases = $o_query ? $o_query->result_array() : array();

//need to fix last pdf
// var_dump($o_main->db->last_query());
$s_sql = "SELECT * FROM collecting_cases_main_status_basisconfig ORDER BY id ASC";
$o_query = $o_main->db->query($s_sql);
$v_pre_case_statuses = ($o_query ? $o_query->result_array() : array());

$s_sql = "SELECT * FROM collecting_cases_sub_status_basisconfig ORDER BY id ASC";
$o_query = $o_main->db->query($s_sql);
$v_pre_collecting_case_substatuses = ($o_query ? $o_query->result_array() : array());

$v_case_statuses = array();
foreach($v_pre_case_statuses as $v_pre_case_status) {
    $v_case_statuses[$v_pre_case_status['id']] = $v_pre_case_status;
}
$v_case_sub_statuses = array();
foreach($v_pre_collecting_case_substatuses as $v_pre_collecting_case_substatus) {
    $v_case_sub_statuses[$v_pre_collecting_case_substatus['id']] = $v_pre_collecting_case_substatus;
}
$v_tab_statuses = array(
    1=>$formText_Manual_output, 2=>$formText_Automatic_output, 3=>$formText_MissingAddress_output, 4=>$formText_DueDateNotExpired_output, 
    5=>$formText_MarkedToNotSend_output, 6=>$formText_StoppedWithObjection_output, 7=>$formText_MoveToCollectingManual_output,
    8=>$formText_MoveToCollectingAutomatic_output, 9=>$formText_MissingAddress_output, 10=>$formText_TooSmallAmountForProcessing_output, 
    11=>$formText_TooSmallAmountToMove_output, 12=>$formText_OnlyUnpaidFees_output, 13=>$formText_DueDateNotExpiredRemindersNotSentYet_output
);
?>
<div id="p_container" class="p_container">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
                <a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list" style="display: block; margin-bottom:10px;"><?php echo $formText_BackToList_outpup;?></a>
				<div class="p_pageDetails">
					<div class="p_pageDetailsTitle">
						<div class="" style="float: left">
							<?php echo $step_info['fee_level_name']." ".$step_info['name']." - ".count($connected_cases)." ".$formText_Cases_output;?>
						</div>
						<div class="clear"></div>
					</div>
                    
					<div class="p_contentBlock">
                        <div class="case_item">
                            <div class="case_item_column small_column"><?php echo $formText_CaseId_output;?></div>
                            <div class="case_item_column"><?php echo $formText_Creditor_output;?><br/><?php echo $formText_Debitor_output;?></div>
                            <div class="case_item_column"><?php echo $formText_CaseBalance_output;?></div>
                            <div class="case_item_column"><?php echo $formText_LastSent_output;?></div>
                            <div class="case_item_column"><?php echo $formText_Status_output;?></div>
                            <div class="clear"></div>
                        </div>
                        <?php 
                        foreach($connected_cases as $connected_case) {
                            $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$connected_case['id'];
                        ?>
                            <div class="case_item">
                                <div class="case_item_column small_column"><a href="<?php echo $s_edit_link;?>" target="_blank"><?php echo $connected_case['id'];?></a></div>
                                <div class="case_item_column"><?php echo $connected_case['creditorName'];?><br/><?php echo $connected_case['debitorName'];?></div>
                                <div class="case_item_column"><?php echo $connected_case['case_balance']?>&nbsp;</div>
                                <div class="case_item_column">                                    
                                    <?php 
                                    if($connected_case['pdf'] != "") {
                                        $fileParts = explode('/',$connected_case['pdf']);
                                        $fileName = array_pop($fileParts);
                                        $fileParts[] = rawurlencode($fileName);
                                        $filePath = implode('/',$fileParts);
                                        $fileUrl = $extradomaindirroot.$connected_case['pdf'];
                                        $fileName = basename($connected_case['pdf']);
                                        if(strpos($connected_case['pdf'],'uploads/protected/')!==false)
                                        {
                                            $fileUrl = $extradomaindirroot."/../".$filePath.'?caID='.$_GET['caID'].'&table=collecting_cases_claim_letter&field=pdf&ID='.$connected_case['letterId'];
                                        }
                                        ?>
                                        <div class="project-file">
                                            <div class="project-file-file">
                                                <a href="<?php echo $fileUrl;?>" download target="_blank"><?php echo $formText_Download_Output;?></a>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <?php echo date("d.m.Y H:i:s", strtotime($connected_case['letter_created_date']));?>
                                </div>     
                                <div class="case_item_column"><?php 
                                if($connected_case['open']){
                                    echo $v_tab_statuses[$connected_case['tab_status']];
                                } else { 
                                    echo $formText_Closed_output;
                                }
                                ?></div>                                 
                                <div class="clear"></div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    #p_container .case_item {
        padding: 5px 10px;
    }
    #p_container .case_item_column {
        float: left;
        width: 20%;
    }
    #p_container .case_item_column.small_column {
        width: 5%;
    }
</style>