<?php
$list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : 1;
$mainlist_filter = 'collectingLevel';
$sublist_filter = "notStarted";
if($mainlist_filter == "collectingLevel"){
    $list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : 'all';
    $sublist_filter = $_GET['sublist_filter'] ? ($_GET['sublist_filter']) : $sublist_filter;
}
?>
<div class="output-filter">
    <ul>
        <?php /* foreach($main_statuses as $main_status) {
            if($mainlist_filter == "reminderLevel") {
                $statusArray = array(1,2,5);
            } else if($mainlist_filter == "collectingLevel") {
                $statusArray = array(3,4,6,7,8);
            }
            if(in_array($main_status['id'], $statusArray)) {
            ?>
            <li class="item<?php echo ($list_filter == $main_status['id'] ? ' active':'');?>">
                <a class="topFilterlink" data-listfilter="<?php echo $main_status['id'];?>" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=".$main_status['id']; ?>">
                    <span class="link_wrapper">
                        <span class="count"><?php echo $case_count[$main_status['id']]; ?></span>
                        <?php echo $main_status['name'];?>
                    </span>
                </a>
            </li>
        <?php
            }
        } */?>
		<li class="item<?php echo ($list_filter == "all" ? ' active':'');?>">
			<a class="topFilterlink" data-listfilter="all" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=all"; ?>">
				<span class="link_wrapper">
					<span class="count"><?php echo $all_case_count; ?></span>
					<?php echo $formText_AllCases_output;?>
				</span>
			</a>
		</li>
		<li class="item<?php echo ($list_filter == "warning" ? ' active':'');?>">
			<a class="topFilterlink" data-listfilter="warning" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=warning"; ?>">
				<span class="link_wrapper">
					<span class="count"><?php echo $warning_case_count; ?></span>
					<?php echo $formText_CasesInWarningLevel_output;?>
				</span>
			</a>
		</li>
		<li class="item<?php echo ($list_filter == "collecting" ? ' active':'');?>">
			<a class="topFilterlink" data-listfilter="collecting" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=collecting"; ?>">
				<span class="link_wrapper">
					<span class="count"><?php echo $collecting_case_count; ?></span>
					<?php echo $formText_CasesInCollectingLevel_output;?>
				</span>
			</a>
		</li>
		<li class="item<?php echo ($list_filter == "warning_closed" ? ' active':'');?>">
			<a class="topFilterlink" data-listfilter="warning_closed" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=warning_closed"; ?>">
				<span class="link_wrapper">
					<span class="count"><?php echo $warning_closed_case_count; ?></span>
					<?php echo $formText_CasesClosedInWarningLevel_output;?>
				</span>
			</a>
		</li>
		<li class="item<?php echo ($list_filter == "collecting_closed" ? ' active':'');?>">
			<a class="topFilterlink" data-listfilter="collecting_closed" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=collecting_closed"; ?>">
				<span class="link_wrapper">
					<span class="count"><?php echo $collecting_closed_case_count; ?></span>
					<?php echo $formText_CasesClosedInCollectingLevel_output;?>
				</span>
			</a>
		</li>
		<li class="item<?php echo ($list_filter == "company_fee_paid" ? ' active':'');?>">
			<a class="topFilterlink" data-listfilter="company_fee_paid" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=company_fee_paid"; ?>">
				<span class="link_wrapper">
					<span class="count"><?php echo $company_fee_paid_count; ?></span>
					<?php echo $formText_CompanyFeePaid_output;?>
				</span>
			</a>
		</li>
		<li class="item<?php echo ($list_filter == "company_fee_notpaid" ? ' active':'');?>">
			<a class="topFilterlink" data-listfilter="company_fee_notpaid" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=company_fee_notpaid"; ?>">
				<span class="link_wrapper">
					<span class="count"><?php echo $company_fee_notpaid_count; ?></span>
					<?php echo $formText_CompanyFeeNotPaid_output;?>
				</span>
			</a>
		</li>

		<li class="item<?php echo ($list_filter == "without_fee_paid" ? ' active':'');?>">
			<a class="topFilterlink" data-listfilter="without_fee_paid" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=without_fee_paid"; ?>">
				<span class="link_wrapper">
					<span class="count"><?php echo $without_fee_paid_count; ?></span>
					<?php echo $formText_WithoutFeePaid_output;?>
				</span>
			</a>
		</li>
		<li class="item<?php echo ($list_filter == "without_fee_notpaid" ? ' active':'');?>">
			<a class="topFilterlink" data-listfilter="without_fee_notpaid" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=without_fee_notpaid"; ?>">
				<span class="link_wrapper">
					<span class="count"><?php echo $without_fee_notpaid_count; ?></span>
					<?php echo $formText_WithoutFeeNotPaid_output;?>
				</span>
			</a>
		</li>

		<li class="item<?php echo ($list_filter == "consider" ? ' active':'');?>">
			<a class="topFilterlink" data-listfilter="consider" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=consider"; ?>">
				<span class="link_wrapper">
					<span class="count"><?php echo $consider_count; ?></span>
					<?php echo $formText_Consider_output;?>
				</span>
			</a>
		</li>
		<?php if($variables->developeraccess > 5) { ?>
			<li class="item<?php echo ($list_filter == "due_date_issue" ? ' active':'');?>">
				<a class="topFilterlink" data-listfilter="due_date_issue" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=due_date_issue"; ?>">
					<span class="link_wrapper">
						<span class="count"><?php echo $due_date_issue_count; ?></span>
						<?php echo $formText_DueDateIssue_output;?>
					</span>
				</a>
			</li>
			<li class="item<?php echo ($list_filter == "cases_to_check" ? ' active':'');?>">
				<a class="topFilterlink" data-listfilter="cases_to_check" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=cases_to_check"; ?>">
					<span class="link_wrapper">
						<span class="count"><?php echo $cases_to_check_count; ?></span>
						<?php echo $formText_CasesToCheck_output;?>
					</span>
				</a>
			</li>
			<li class="item<?php echo ($list_filter == "deleted" ? ' active':'');?>">
				<a class="topFilterlink" data-listfilter="deleted" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=deleted"; ?>">
					<span class="link_wrapper">
						<span class="count"><?php echo $deleted_count; ?></span>
						<?php echo $formText_Deleted_output;?>
					</span>
				</a>
			</li>
		<?php } ?>
		<li class="item<?php echo ($list_filter == "currency_new_case" ? ' active':'');?>">
			<a class="topFilterlink" data-listfilter="currency_new_case" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=currency_new_case"; ?>">
				<span class="link_wrapper">
					<span class="count"><?php echo $currencyNewCaseCount; ?></span>
					<?php echo $formText_CurrencyNewCase_output;?>
				</span>
			</a>
		</li>
		<li class="item<?php echo ($list_filter == "currency_recalculated" ? ' active':'');?>">
			<a class="topFilterlink" data-listfilter="currency_recalculated" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=currency_recalculated"; ?>">
				<span class="link_wrapper">
					<span class="count"><?php echo $currencyRecalculatedCount; ?></span>
					<?php echo $formText_CurrencyRecalculated_output;?>
				</span>
			</a>
		</li>
    </ul>
</div>
<?php if($list_filter == "collecting" || $list_filter == 'warning') { ?>
	<div class="output-filter">
	    <ul>
			<li class="item<?php echo ($sublist_filter == "notStarted" ? ' active':'');?>">
                <a class="topFilterlink" data-sublistfilter="notStarted" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=".$list_filter."&sublist_filter=notStarted&cases_without_fee_filter=".$_GET['cases_without_fee_filter']; ?>">
                    <span class="link_wrapper">
                        <span class="count"><?php echo $notStartedCount; ?></span>
                        <?php echo $formText_NotStarted_output;?>
                    </span>
                </a>
            </li>
            <li class="item<?php echo ($sublist_filter == "canSendNow" ? ' active':'');?>">
                <a class="topFilterlink" data-sublistfilter="canSendNow" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=".$list_filter."&sublist_filter=canSendNow&cases_without_fee_filter=".$_GET['cases_without_fee_filter']; ?>">
                    <span class="link_wrapper">
                        <span class="count"><?php echo $canSendNowCount; ?></span>
                        <?php echo $formText_CanSendNow_output;?>
                    </span>
                </a>
            </li>
            <li class="item<?php echo ($sublist_filter == "dueDateNotExpired" ? ' active':'');?>">
                <a class="topFilterlink" data-sublistfilter="dueDateNotExpired" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=".$list_filter."&sublist_filter=dueDateNotExpired&cases_without_fee_filter=".$_GET['cases_without_fee_filter']; ?>">
                    <span class="link_wrapper">
                        <span class="count"><?php echo $dueDateNotExpiredCount; ?></span>
                        <?php echo $formText_DueDateNotExpired_output;?>
                    </span>
                </a>
            </li>
            <li class="item<?php echo ($sublist_filter == "paused" ? ' active':'');?>">
                <a class="topFilterlink" data-sublistfilter="paused" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=".$list_filter."&sublist_filter=paused&cases_without_fee_filter=".$_GET['cases_without_fee_filter']; ?>">
                    <span class="link_wrapper">
                        <span class="count"><?php echo $pausedCount; ?></span>
                        <?php echo $formText_Paused_output;?>
                    </span>
                </a>
            </li>
            <li class="item<?php echo ($sublist_filter == "manualProcess" ? ' active':'');?>">
                <a class="topFilterlink" data-sublistfilter="manualProcess" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=".$list_filter."&sublist_filter=manualProcess&cases_without_fee_filter=".$_GET['cases_without_fee_filter']; ?>">
                    <span class="link_wrapper">
                        <span class="count"><?php echo $manualProcessCount; ?></span>
                        <?php echo $formText_manualProcess_output;?>
                    </span>
                </a>
            </li>
            <li class="item<?php echo ($sublist_filter == "surveillance" ? ' active':'');?>">
                <a class="topFilterlink" data-sublistfilter="surveillance" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=".$list_filter."&sublist_filter=surveillance&cases_without_fee_filter=".$_GET['cases_without_fee_filter']; ?>">
                    <span class="link_wrapper">
                        <span class="count"><?php echo $surveillanceCount; ?></span>
                        <?php echo $formText_Surveillance_output;?>
                    </span>
                </a>
            </li>
			
            <li class="item<?php echo ($sublist_filter == "disputed" ? ' active':'');?>">
                <a class="topFilterlink" data-sublistfilter="disputed" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=".$list_filter."&sublist_filter=disputed&cases_without_fee_filter=".$_GET['cases_without_fee_filter']; ?>">
                    <span class="link_wrapper">
                        <span class="count"><?php echo $disputedCount; ?></span>
                        <?php echo $formText_Disputed_output;?>
                    </span>
                </a>
            </li>
			<?php 
			if($list_filter == "collecting") {
				?>
				<li class="item<?php echo ($sublist_filter == "completed" ? ' active':'');?>">
					<a class="topFilterlink" data-sublistfilter="completed" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=".$list_filter."&sublist_filter=completed&cases_without_fee_filter=".$_GET['cases_without_fee_filter']; ?>">
						<span class="link_wrapper">
							<span class="count"><?php echo $completedCount; ?></span>
							<?php echo $formText_CompletedProcess_output;?>
						</span>
					</a>
				</li>
				<?php
			}
			?>
	    </ul>
	</div>
	<?php
}

$s_sql = "SELECT * FROM collecting_cases_sub_status_basisconfig WHERE collecting_cases_main_status_id = ? ";
$o_query = $o_main->db->query($s_sql, array($list_filter));
$sub_statuses = ($o_query ? $o_query->result_array() : array());

?>
<div class="p_tableFilter">
    <div class="p_tableFilter_left">
        <?php if(count($sub_statuses) > 0) { ?>
            <?php echo $formText_SubStatus_output; ?>
            <select class="subStatusFilter" data-case-id="<?php echo $caseData['id'];?>" autocomplete="off">
                <option value="0" <?php if(intval($caseData['status']) == 0) echo 'selected';?>><?php echo $formText_All_output;?></option>
                <?php
                foreach($sub_statuses as $sub_status) {
                    ?>
                    <option value="<?php echo $sub_status['id'];?>" <?php if(intval($sub_status_filter) == $sub_status['id']) echo 'selected';?>><?php echo $sub_status['name'];?></option>
                    <?php
                }
                ?>
            </select>
        <?php } ?>
        <div class="addBtn add_case fw_text_link_color filterBtn"><?php echo $formText_AddCollectingCase_output;?></div>
		<div class="addBtn export_list fw_text_link_color filterBtn"><?php echo $formText_ExportCurrentSelection_Output;?></div>

		<div class="clear"></div>
    </div>
    <div class="p_tableFilter_right">
		<?php if($list_filter == "warning_closed" || $list_filter == "collecting_closed") { ?>
			<span class="show_not_zero"><?php echo $formText_ShowNotZeroChecksum_output;?></span>
		<?php } ?>
        <form class="searchFilterForm" id="searchFilterForm">
			<select class="debitorTypeFilter" autocomplete="off">
				<option value=""><?php echo $formText_All_output;?></option>
				<option value="1" <?php if($debitor_type_filter == 1) echo 'selected';?>><?php echo $formText_Company_output;?></option>
				<option value="2" <?php if($debitor_type_filter == 2) echo 'selected';?>><?php echo $formText_Person_output;?></option>
			</select>
			<input type="text" class="amountFrom" autocomplete="off" placeholder="<?php echo $formText_AmountFrom_output;?>" value="<?php echo $amount_from_filter;?>"/> -
			<input type="text" class="amountTo" autocomplete="off" placeholder="<?php echo $formText_AmountTo_output;?>" value="<?php echo $amount_to_filter;?>"/>
            <input type="text" class="searchFilter" autocomplete="off" placeholder="<?php echo $formText_SearchForCase_output;?>" value="<?php echo $search_filter;?>">
            <button id="p_tableFilterSearchBtn" class="fw_button_color "><?php echo $formText_Search_output; ?></button>
        </form>
        <div class="clear"></div>
        <div class="filteredCountRow">
            <span class="selectionCount">0</span> <?php echo $formText_InSelection_output;?>
            <div class="resetSelection fw_text_link_color"><?php echo $formText_Reset_output;?></div>
        </div>

		<?php
		if($list_filter == "collecting" || $list_filter == "collecting_closed") {
			if(!$_GET['cases_without_fee_filter']){ ?>
			<div class="showCasesWithoutFees"><?php echo $formText_ShowCasesWithoutFees_output;?></div>
		<?php } else {
			?>
			<div class="showAllCases"><?php echo $formText_ShowAllCases_output;?></div>
			<?php
			}
		}?>
		<div class="clear"></div>
    </div>
	<div class="clear"></div>
	<div class="" style="margin-bottom: 10px; margin-left: 10px;">
		<?php if($list_filter == "collecting_closed" || $list_filter == "warning_closed") { ?>
			<select name="case_closed_reason" class="changeClosedReason">
				<option value=""><?php echo $formText_Select_output;?></option>
				<option value="0" <?php if($_GET['closed_reason_filter'] != "" && $closed_reason_filter == 0) echo 'selected';?>><?php echo $formText_FullyPaid_output;?></option>
				<option value="1" <?php if($closed_reason_filter == 1) echo 'selected';?>><?php echo $formText_PayedWithLessAmountForgiven_output;?></option>
				<option value="2" <?php if($closed_reason_filter == 2) echo 'selected';?>><?php echo $formText_ClosedWithoutAnyPayment_output;?></option>
				<option value="3" <?php if($closed_reason_filter == 3) echo 'selected';?>><?php echo $formText_ClosedWithPartlyPayment_output;?></option>
				<option value="4" <?php if($closed_reason_filter == 4) echo 'selected';?>><?php echo $formText_CreditedByCreditor_output;?></option>
				<option value="5" <?php if($closed_reason_filter == 5) echo 'selected';?>><?php echo $formText_DrawnByCreditorToDeleteFees_output;?></option>
			</select>
		<?php } ?>
	</div>
</div>
<style>
.show_not_zero {
	color: #46b2e2;
	cursor: pointer;
	margin-top: 5px;
	display: inline-block;
}
.showCasesWithoutFees {
	float: right;
	color: #46b2e2;
	cursor: pointer;
	margin-right: 5px;
	margin-bottom: 5px;
}
</style>
<script type="text/javascript">
$(document).ready(function(){
    // Filter by building
    $('.filterDepartment').on('change', function(e) {
        var data = {
            mainlist_filter: '<?php echo $mainlist_filter;?>',
            list_filter: '<?php echo $list_filter;?>',
            sublist_filter: '<?php echo $sublist_filter;?>',
            department_filter: $(this).val(),
            search_filter: $('.searchFilter').val()
        };
        loadView("list", data);
        // ajaxCall('list', data, function(json) {
        //     $('.p_pageContent').html(json.html);
        // });
    });
	$(".show_not_zero").on('click', function(e) {
        e.preventDefault();
        var data = {
            mainlist_filter: '<?php echo $mainlist_filter;?>',
            list_filter: '<?php echo $list_filter;?>',
            sublist_filter: '<?php echo $sublist_filter;?>',
            department_filter: $('.filterDepartment').val(),
            search_filter: $('.searchFilter').val(),
            amount_from_filter: $('.amountFrom').val(),
            amount_to_filter: $('.amountTo').val(),
			debitor_type_filter: $(".debitorTypeFilter").val(),
			show_not_zero_filter: 1
        };
        loadView("list", data);
        // ajaxCall('list', data, function(json) {
        //     $('.p_pageContent').html(json.html);
        // });
    });
    // Filter by customer name
    $('.searchFilterForm').on('submit', function(e) {
        e.preventDefault();
        var data = {
            mainlist_filter: '<?php echo $mainlist_filter;?>',
            list_filter: '<?php echo $list_filter;?>',
            sublist_filter: '<?php echo $sublist_filter;?>',
            department_filter: $('.filterDepartment').val(),
            search_filter: $('.searchFilter').val(),
            amount_from_filter: $('.amountFrom').val(),
            amount_to_filter: $('.amountTo').val(),
			debitor_type_filter: $(".debitorTypeFilter").val(),
			cases_without_fee_filter: '<?php echo $_GET['cases_without_fee_filter']?>',
        };
        loadView("list", data);
        // ajaxCall('list', data, function(json) {
        //     $('.p_pageContent').html(json.html);
        // });
    });
    $(".resetSelection").on('click', function(e) {
        e.preventDefault();
        var data = {
            mainlist_filter: '<?php echo $mainlist_filter;?>',
            list_filter: '<?php echo $list_filter;?>',
            sublist_filter: '<?php echo $sublist_filter;?>',
        };
        loadView("list", data);
        // ajaxCall('list', data, function(json) {
        //     $('.p_pageContent').html(json.html);
        //     $('.searchFilterForm .searchFilter').val("");
        //     $(".filterDepartment").val("");
        // });
    });
    $(".subStatusFilter").change(function(){
        var value = $(this).val();
        var data = {
            mainlist_filter: '<?php echo $mainlist_filter;?>',
            list_filter: '<?php echo $list_filter;?>',
            sublist_filter: '<?php echo $sublist_filter;?>',
            sub_status_filter: value
        };
        loadView("list", data);
    })
	$(".changeClosedReason").change(function(){
		var value = $(this).val();
        var data = {
            mainlist_filter: '<?php echo $mainlist_filter;?>',
            list_filter: '<?php echo $list_filter;?>',
            sublist_filter: '<?php echo $sublist_filter;?>',
			closed_reason_filter: value
        };
        loadView("list", data);
	})
    $(".add_case").off("click").on("click", function(e){
        e.preventDefault();
		var data = {
		};
		ajaxCall('edit_case', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
    })
	$(".showCasesWithoutFees").off("click").on("click", function(){
        var data = {
            mainlist_filter: '<?php echo $mainlist_filter;?>',
            list_filter: '<?php echo $list_filter;?>',
            sublist_filter: '<?php echo $sublist_filter;?>',
            department_filter: $('.filterDepartment').val(),
            search_filter: $('.searchFilter').val(),
            amount_from_filter: $('.amountFrom').val(),
            amount_to_filter: $('.amountTo').val(),
			debitor_type_filter: $(".debitorTypeFilter").val(),
			cases_without_fee_filter: 1
        };
        loadView("list", data);
	})
	$(".showAllCases").off("click").on("click", function(){
        var data = {
            mainlist_filter: '<?php echo $mainlist_filter;?>',
            list_filter: '<?php echo $list_filter;?>',
            sublist_filter: '<?php echo $sublist_filter;?>',
            department_filter: $('.filterDepartment').val(),
            search_filter: $('.searchFilter').val(),
            amount_from_filter: $('.amountFrom').val(),
            amount_to_filter: $('.amountTo').val(),
			debitor_type_filter: $(".debitorTypeFilter").val(),
			cases_without_fee_filter: 0
        };
        loadView("list", data);
	});
	$('.export_list').off("click").on("click", function(e){
		fw_loading_start();
		var generateIframeDownload = function(){
			fetch("<?php echo $extradir;?>/output/includes/export_list.php?mainlist_filter=<?php echo $mainlist_filter;?>&list_filter=<?php echo $list_filter;?>&sublist_filter=<?php echo $sublist_filter;?>&search_filter=<?php echo $search_filter?>&amount_from_filter=<?php echo $amount_from_filter?>&amount_to_filter=<?php echo $amount_to_filter?>&debitor_type_filter=<?php echo $debitor_type_filter?>&cases_without_fee_filter=<?php echo $cases_without_fee_filter?>&time=<?php echo time();?>")
			  .then(resp => resp.blob())
			  .then(blob => {
				const url = window.URL.createObjectURL(blob);
				const a = document.createElement('a');
				a.style.display = 'none';
				a.href = url;
				// the filename you want
				a.download = 'export.xls';
				document.body.appendChild(a);
				a.click();
				window.URL.revokeObjectURL(url);
				out_popup.close();
			  })
			  .catch(() => fw_loading_end());
		  }

		  generateIframeDownload();
	})
})
</script>
