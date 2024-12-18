<?php
$list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : 'all_cases';
$mainlist_filter = 'reminderLevel';
if($mainlist_filter == "collectingLevel"){
    $list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : 3;
}
?>
<?php
if(isset($_GET['list_filter'])) { $filter = $_GET['list_filter']; } else { $filter = "active"; }
?>

<div class="output-filter">
    <ul>
		<li class="item<?php echo ($list_filter == 'all_cases' ? ' active':'');?>">
			<a class="topFilterlink" data-listfilter="all_cases" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=all_cases"; ?>">
				<span class="link_wrapper">
					<span class="count"><?php echo $all_cases_count; ?></span>
					<?php echo $formText_AllCases_output; ?>
				</span>
			</a>
		</li>
		<li class="item<?php echo ($list_filter == '1' ? ' active':'');?>">
			<a class="topFilterlink" data-listfilter="1" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=1"; ?>">
				<span class="link_wrapper">
					<span class="count"><?php echo $case_count[1]; ?></span>
					<?php echo $formText_OpenCases_output;?>
				</span>
			</a>
		</li>
		<li class="item<?php echo ($list_filter == '2' ? ' active':'');?>">
			<a class="topFilterlink" data-listfilter="2" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=2"; ?>">
				<span class="link_wrapper">
					<span class="count"><?php echo $case_count[2]; ?></span>
					<?php echo $formText_ClosedCases_output;?>
				</span>
			</a>
		</li>
        <?php
		/*
		foreach($main_statuses as $main_status) {
            if($mainlist_filter == "reminderLevel") {
                $statusArray = array(1,2,5);
            } else if($mainlist_filter == "collectingLevel") {
                $statusArray = array(3,4,6,7);
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
        }*/
        if($mainlist_filter == "reminderLevel") {
            ?>
            <li class="item<?php echo ($list_filter == 'cases_objection' ? ' active':'');?>">
                <a class="topFilterlink" data-listfilter="cases_objection" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=cases_objection"; ?>">
                    <span class="link_wrapper">
                        <span class="count"><?php echo $objection_count; ?></span>
                        <?php echo $formText_CasesWithObjections_output;?>
                    </span>
                </a>
            </li>
			<li class="item<?php echo ($list_filter == 'cases_transferred' ? ' active':'');?>">
                <a class="topFilterlink" data-listfilter="cases_transferred" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=cases_transferred"; ?>">
                    <span class="link_wrapper">
                        <span class="count"><?php echo $transferred_count; ?></span>
                        <?php echo $formText_CasesTransferredToCollectingCompany_output;?>
                    </span>
                </a>
            </li>
			<li class="item<?php echo ($list_filter == 'cases_canceled' ? ' active':'');?>">
                <a class="topFilterlink" data-listfilter="cases_canceled" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=cases_canceled"; ?>">
                    <span class="link_wrapper">
                        <span class="count"><?php echo $canceled_count; ?></span>
                        <?php echo $formText_CasesCanceledConnectedWithLinkId_output;?>
                    </span>
                </a>
            </li>
			<li class="item<?php echo ($list_filter == 'missing_transaction' ? ' active':'');?>">
                <a class="topFilterlink" data-listfilter="missing_transaction" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=missing_transaction"; ?>">
                    <span class="link_wrapper">
                        <span class="count"><?php echo $missing_transaction_count; ?></span>
                        <?php echo $formText_CasesMissingTransaction_output;?>
                    </span>
                </a>
            </li>
        <?php } ?>
    </ul>
</div>
<?php if($list_filter == 2) { ?>
<div class="output-filter">
	<ul>
		<li class="item<?php echo ($sublist_filter == 'not_approved' ? ' active':'');?>">
			<a class="topFilterlinkSub" data-listfilter="not_approved" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=2&sublist_filter=not_approved"; ?>">
				<span class="link_wrapper">
					<span class="count"><?php echo $not_approved_count; ?></span>
					<?php echo $formText_NotApproved_output; ?>
				</span>
			</a>
		</li>
		<li class="item<?php echo ($sublist_filter == 'approved' ? ' active':'');?>">
			<a class="topFilterlinkSub" data-listfilter="approved" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=2&sublist_filter=approved"; ?>">
				<span class="link_wrapper">
					<span class="count"><?php echo $approved_count; ?></span>
					<?php echo $formText_Approved_output; ?>
				</span>
			</a>
		</li>
	</ul>
</div>
<?php } ?>
<?php

// $s_sql = "SELECT * FROM collecting_cases_sub_status_basisconfig WHERE collecting_cases_main_status_id = ? ";
// $o_query = $o_main->db->query($s_sql, array($list_filter));
// $sub_statuses = ($o_query ? $o_query->result_array() : array());

?>
<div class="p_tableFilter">
    <div class="p_tableFilter_left">
		<?php
		if($sublist_filter == "approved" && $list_filter = 2) {
			$sql = "SELECT p.*, cred.companyname as creditorName, c2.name as debitorName, ct.link_id, ct.creditor_id, ct.invoice_nr, SUM(p.payed_fee_amount) as payed_fee_amount, SUM(p.payed_interest_amount) as payed_interest_amount
		             FROM collecting_cases p
					 JOIN creditor_transactions ct ON ct.collectingcase_id = p.id
		             LEFT JOIN creditor cred ON cred.id = p.creditor_id
		             LEFT JOIN customer c2 ON c2.id = p.debitor_id
		            WHERE p.content_status < 2 AND (ct.open = 0) AND p.approved_for_report = 1
					AND (DATE(p.stopped_date) >= '".$o_main->db->escape_str(date("Y-m-d", strtotime($date_from_filter)))."')
					AND (DATE(p.stopped_date) <= '".$o_main->db->escape_str(date("Y-m-d", strtotime($date_to_filter)))."')";
            $o_query = $o_main->db->query($sql);
			$list = $o_query ? $o_query->row_array() : array();

			echo $formText_FeePayed_output.": ".number_format($list['payed_fee_amount'], 2, ",", " ")."<br/>";
			echo $formText_InterestPayed_output.": ". number_format($list['payed_interest_amount'], 2, ",", " ");
		}
		?>
        <?php /*if(count($sub_statuses) > 0) { ?>
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
        <?php }
        <div class="addBtn add_case fw_text_link_color filterBtn"><?php echo $formText_AddCollectingCase_output;?></div>
		*/?>
		<?php if($list_filter=="cases_transferred") { ?>
			<?php echo $formText_Filter_output; ?>
			<select class="caseFilterChanger" data-case-id="<?php echo $caseData['id'];?>" autocomplete="off">
				<option value="0" ><?php echo $formText_All_output;?></option>
				<option value="1" <?php if($case_filter==1) echo 'selected';?>><?php echo $formText_CasesWithoutCollectingCompanyCase_output;?></option>
			</select>
		<?php } ?>
        <div class="clear"></div>
    </div>
    <div class="p_tableFilter_right">
        <form class="searchFilterForm" id="searchFilterForm">
			<?php
			if($list_filter == 2) {
				?>
                <div class="hide_with_issues_wrapper"><input type="checkbox" name="hide_with_issues" class="hide_with_issues" value="1" <?php if($_GET['hide_with_issues']) echo 'checked';?> id="hide_with_issues"/><label for="hide_with_issues"><?php echo $formText_HideWithIssues_output;?></label></div>
				<div class="date_wrapper"><input type="text" name="date_from" value="<?php echo $date_from_filter;?>" class="datepicker dateFrom" placeholder="<?php echo $formText_DateFrom_output;?>"/> - <input type="text" name="date_to" value="<?php echo $date_to_filter;?>" class="datepicker dateTo" placeholder="<?php echo $formText_DateTo_output;?>"/></div>
				<?php
			}
			?>
            <input type="text" class="searchFilter" autocomplete="off" placeholder="<?php echo $formText_SearchForCase_output;?>" value="<?php echo $search_filter;?>">
            <button id="p_tableFilterSearchBtn" class="fw_button_color "><?php echo $formText_Search_output; ?></button>
        </form>
        <div class="clear"></div>
        <div class="filteredCountRow">
			<span class="selectionCount"><?php echo $filteredCount;?></span> <?php echo $formText_InSelection_output;?>
            <div class="resetSelection fw_text_link_color"><?php echo $formText_Reset_output;?></div>
    	</div>
    </div>
</div>
<style>
.date_wrapper {
	float: left;
	margin-right: 10px;
}
.topFilterlink img {
    width: 20px;
}
.hide_with_issues_wrapper {
    float: left;
	margin-right: 10px;
}
.hide_with_issues_wrapper label {
    font-weight: normal !important;
    margin-left: 5px;
}
</style>
<script type="text/javascript">
$(function(){    
    // function loadTabNumber(tab_id){
    //     var data = {
    //         list_filter: '<?php echo $list_filter; ?>',
    //         sublist_filter: '<?php echo $sublist_filter; ?>',
    //         filters: '<?php echo json_encode($sublist_filter); ?>',
    //         tab_id: tab_id,
    //     };
    //     ajaxCall("getTabNumbers", data, function(json) {
    //         $('.topFilterlink[data-listfilter="'+tab_id+'"] .count').html(json.html);
    //     }, false);
    // }

    // $(".topFilterlink").each(function(index, el){
    //     var tab_id = $(el).data("listfilter");
    //     loadTabNumber(tab_id);
    // })    

	<?php if($search_filter != "" || $date_from_filter != "" || $date_to_filter != "") { ?>
		$(".filteredCountRow .selectionCount").html("<?php echo $filteredCount;?>");
		$(".filteredCountRow").show();
	<?php } ?>
	$(".datepicker").datepicker({
		dateFormat: "d.m.yy",
		firstDay: 1
	})
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

    // Filter by customer name
    $('.searchFilterForm').on('submit', function(e) {
        e.preventDefault();
        var data = {
            mainlist_filter: '<?php echo $mainlist_filter;?>',
            list_filter: '<?php echo $list_filter;?>',
            sublist_filter: '<?php echo $sublist_filter;?>',
            department_filter: $('.filterDepartment').val(),
            search_filter: $('.searchFilter').val(),
            date_from_filter: $('.dateFrom').val(),
            date_to_filter: $('.dateTo').val(),
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
            sublist_filter: '<?php echo $sublist_filter;?>'
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
            sub_status_filter: value
        };
        loadView("list", data);
    })
	$(".caseFilterChanger").change(function(){
        var data = {
            mainlist_filter: '<?php echo $mainlist_filter;?>',
            list_filter: '<?php echo $list_filter;?>',
			case_filter: $(this).val()
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
    $(".hide_with_issues").off("click").on("click", function(e){
        e.preventDefault();
        var checked = 0;
        if($(this).is(":checked")){
            checked = 1;
        }
        var data = {
            mainlist_filter: '<?php echo $mainlist_filter;?>',
            list_filter: '<?php echo $list_filter;?>',
            sublist_filter: '<?php echo $sublist_filter;?>',
            department_filter: $('.filterDepartment').val(),
            search_filter: $('.searchFilter').val(),
            date_from_filter: $('.dateFrom').val(),
            date_to_filter: $('.dateTo').val(),
            hide_with_issues: checked,
        };
        loadView("list", data);
    })
})
</script>
