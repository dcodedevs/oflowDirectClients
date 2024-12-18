<?php
$list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : 'active';
?>
<?php
if(isset($_GET['list_filter'])) { $filter = $_GET['list_filter']; } else { $filter = "active"; }
?>
<?php /* ?>
<div class="output-filter">
    <ul>
        <li class="item<?php echo ($list_filter == 'active' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="active" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=active"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $active_count; ?></span>
                    <?php echo $formText_ActiveCases_output;?>
                </span>
            </a>
        </li>
        <li class="item<?php echo ($list_filter == 'objection' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="objection" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=objection"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $objection_count; ?></span>
                    <?php echo $formText_ObjectionCases_output;?>
                </span>
            </a>
        </li>

        <li class="item<?php echo ($list_filter == 'finished' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="finished" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=finished"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $finished_count; ?></span>
                    <?php echo $formText_FinishedCases_output;?>
                </span>
            </a>
        </li>
        <li class="item<?php echo ($list_filter == 'canceled' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="canceled" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=canceled"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $canceled_count; ?></span>
                    <?php echo $formText_CanceledCases_output;?>
                </span>
            </a>
        </li>
    </ul>
</div> */?>

<div class="p_tableFilter">
    <div class="p_tableFilter_left">
        <div class="addBtn fw_text_link_color addNewCreditor"><?php echo $formText_AddNew_output;?></div>
        <div class="clear"></div>
		<span ><?php echo $formText_Filter_output;?></span>
		<select class="filterDown">
			<option value=""><?php echo $formText_All_output;?></option>
			<option value="1" <?php if($list_filter == 1) echo 'selected';?>><?php echo $formText_AutomaticReminders_output;?></option>
			<option value="2" <?php if($list_filter == 2) echo 'selected';?>><?php echo $formText_AutomaticMoveToCollecting_output;?></option>
			<option value="3" <?php if($list_filter == 3) echo 'selected';?>><?php echo $formText_OnboardingIncomplete_output;?></option>
			<option value="4" <?php if($list_filter == 4) echo 'selected';?>><?php echo $formText_ControlSumIncorrect_output;?></option>
			<option value="7" <?php if($list_filter == 7) echo 'selected';?>><?php echo $formText_AutosyncFailed_output;?></option>
			<option value="8" <?php if($list_filter == 8) echo 'selected';?>><?php echo $formText_AutosyncFailedWithAutomaticReminderProcess_output;?></option>
			<option value="5" <?php if($list_filter == 5) echo 'selected';?>><?php echo $formText_DemoAccount_output;?></option>
			<option value="6" <?php if($list_filter == 6) echo 'selected';?>><?php echo $formText_SignedCollectingCompanyAgreement_output;?></option>
			<option value="9" <?php if($list_filter == 9) echo 'selected';?>><?php echo $formText_DirectlyToCollectingCompanyHide_output;?></option>
			<option value="10" <?php if($list_filter == 10) echo 'selected';?>><?php echo $formText_NotSignedWithCollectingCompanyCases_output;?></option>
			<option value="11" <?php if($list_filter == 11) echo 'selected';?>><?php echo $formText_DirectlyToCollectingCompanyShow_output;?></option>
			<option value="12" <?php if($list_filter == 12) echo 'selected';?>><?php echo $formText_ShowTransferToCollectingCompanyInReadyToSend_output;?></option>
			<option value="13" <?php if($list_filter == 13) echo 'selected';?>><?php echo $formText_ShowCustomersWithActivatedEhf_output;?></option>
			<option value="14" <?php if($list_filter == 14) echo 'selected';?>><?php echo $formText_ShowCreditorsWithPerson_output;?></option>
			<option value="15" <?php if($list_filter == 15) echo 'selected';?>><?php echo $formText_ShowCreditorsWithNotDefaultProcessMoveTo_output;?></option>
			<option value="16" <?php if($list_filter == 16) echo 'selected';?>><?php echo $formText_ShowCreditorsWithSpecifiedBilling_output;?></option>
		
		</select>
    </div>
    <div class="p_tableFilter_right">
        <form class="searchFilterForm" id="searchFilterForm">
            <input type="text" class="searchFilter" autocomplete="off" placeholder="<?php echo $formText_SearchForCase_output;?>" value="<?php echo $search_filter;?>">
            <button id="p_tableFilterSearchBtn" class="fw_button_color "><?php echo $formText_Search_output; ?></button>
        </form>
        <div class="clear"></div>
        <div class="filteredCountRow">
            <span class="selectionCount">0</span> <?php echo $formText_InSelection_output;?>
            <div class="resetSelection fw_text_link_color"><?php echo $formText_Reset_output;?></div>
        </div>
		<div class="fw_text_link_color sync_all_creditors"><?php echo $formText_SyncAllCreditors_output;?></div>
		<div class="fw_text_link_color process_all_cases"><?php echo $formText_ProcessAllCasesReminder_output;?></div>
		<div class="fw_text_link_color process_all_cases_collecting"><?php echo $formText_ProcessAllCasesCollecting_output;?></div>
		<div class="fw_text_link_color process_all_cases_moving"><?php echo $formText_ProcessAllCasesToBeMovedAutomaticallyToCollecting_output;?></div>
		<div class="fw_text_link_color check_all_creditors_open_transaction_sum"><?php echo $formText_CheckAllCreditorOpenTransactionSum_output;?></div>

		<div class="fw_text_link_color process_all_cases_collecting_manual"><?php echo $formText_ProcessCasesCollectingManual_output;?></div>
		
		<div class="fw_text_link_color check_open_transactions_without_connection"><?php echo $formText_CheckOpenTransactionsWithoutConnection_output;?></div>

		
		<div class="fw_text_link_color process_all_automatic_cases"><?php echo $formText_ProcessAllAutomaticCases_output;?></div>
		<div class="fw_text_link_color process_all_move_to_collecting_cases"><?php echo $formText_ProcessAllMoveToCollectingCases_output;?></div>
		<div class="fw_text_link_color process_all_move_to_aptic"><?php echo $formText_ProcessAllMoveToApticCases_output;?></div>
			
		<?php
		if($variables->developeraccess > 10) {
			echo "<br/><br/><br/><br/>";
			$s_sql = "SELECT collecting_cases.*, CONCAT_WS(' ', c.name, c.middlename,c.lastname) as debitorName, cred.companyname as creditorName FROM collecting_cases
			LEFT OUTER JOIN customer c ON c.id = collecting_cases.debitor_id
			LEFT OUTER JOIN creditor cred ON cred.id = collecting_cases.creditor_id
			WHERE create_letter = 1";
			$o_query = $o_main->db->query($s_sql);
			$failed_letter_count = ($o_query ? $o_query->num_rows() : 0);
			?>
			<div class="fw_text_link_color check_duplicated_transactions"><?php echo $formText_CheckDuplicatedTransactions_output;?></div>


	        <div class="fw_text_link_color check_failed_letters"><?php echo $formText_CheckFailedToCreateLetters_output. " (".$failed_letter_count.")"?></div>
			<?php


		}

		?>
    </div>
	<div class="clear"></div>
	<div class="in_selection"><?php echo $formText_InSelection_output." ".$currentCount;?> </div>
</div>
<style>
	.in_selection {
		padding: 5px;
	}
    .p_tableFilter_left .addBtn {
        color: #46b2e2;
    }
	.sync_all_creditors {
        color: #46b2e2;
		cursor: pointer;
		margin-top: 20px;
	}
	.process_all_automatic_cases {
        color: #46b2e2;
		cursor: pointer;
		margin-top: 10px;
	}
	.process_all_move_to_collecting_cases {
        color: #46b2e2;
		cursor: pointer;
		margin-top: 10px;
	}
	.process_all_move_to_aptic {
        color: #46b2e2;
		cursor: pointer;
		margin-top: 10px;
	}

	.process_all_cases {
        color: #46b2e2;
		cursor: pointer;
		margin-top: 10px;
	}
	.process_all_cases_collecting {
        color: #46b2e2;
		cursor: pointer;
		margin-top: 10px;
	}
	.process_all_cases_collecting_manual {
        color: #46b2e2;
		cursor: pointer;
		margin-top: 10px;
	}
	.check_all_creditors_open_transaction_sum {
        color: #46b2e2;
		cursor: pointer;
		margin-top: 10px;
	}
	.process_all_cases_moving {
        color: #46b2e2;
		cursor: pointer;
		margin-top: 10px;
	}
	.check_open_transactions_without_connection {
        color: #46b2e2;
		cursor: pointer;
		margin-top: 10px;
	}
</style>
<script type="text/javascript">
$(document).ready(function(){
	$(".filterDown").change(function(){
		var data = {
            list_filter: $(this).val(),
            department_filter: '<?php echo $department_filter;?>',
            search_filter: $('.searchFilter').val(),
        };
        loadView("list", data);
	})
	$(".show_checksum_incorrect").off("click").on("click", function(){
		var data = {
            list_filter: '<?php echo $list_filter;?>',
            department_filter: $(this).val(),
            search_filter: $('.searchFilter').val(),
			show_checksum_incorrect: 1
        };
        loadView("list", data);
	})
    // Filter by building
    $('.filterDepartment').on('change', function(e) {
        var data = {
            list_filter: '<?php echo $list_filter;?>',
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
            list_filter: '<?php echo $list_filter;?>',
            department_filter: $('.filterDepartment').val(),
            search_filter: $('.searchFilter').val(),
        };
        loadView("list", data);
        // ajaxCall('list', data, function(json) {
        //     $('.p_pageContent').html(json.html);
        // });
    });
    $(".addNewCreditor").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			creditor_id: $(this).data('creditor-id')
		};
		ajaxCall('edit_creditor', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".sync_all_creditors").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
		};
		bootbox.confirm('<?php echo $formText_SyncAllCreditors_output; ?>?', function(result) {
			if (result) {
				ajaxCall('sync_all_creditors', data, function(json) {
					$('#popupeditboxcontent').html('');
					$('#popupeditboxcontent').html(json.html);
					out_popup = $('#popupeditbox').bPopup(out_popup_options);
					$("#popupeditbox:not(.opened)").remove();
				});
			}
		})
	});

	$(".process_all_cases").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
		};

		ajaxCall('process_all_cases', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".process_all_automatic_cases").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
		};
		
		ajaxCall('process_automatic_cases_new', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".process_all_move_to_collecting_cases").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
		};
		
		ajaxCall('process_automatic_move_to_collecting_cases_new', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".process_all_move_to_aptic").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
		};
		
		ajaxCall('process_automatic_move_to_aptic', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".process_all_cases_collecting").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
		};
		ajaxCall('process_all_cases_collecting', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});

	$(".process_all_cases_collecting_manual").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
		};
		ajaxCall('process_all_cases_collecting_manual', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});

	$(".check_open_transactions_without_connection").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
		};
		ajaxCall('check_open_transactions_without_connection', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".process_all_cases_moving").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
		};
		ajaxCall('process_automatic_move_to_collecting_cases', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".check_all_creditors_open_transaction_sum").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
		};
		ajaxCall('check_open_transaction_sum', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});

	$(".check_duplicated_transactions").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
		};
		ajaxCall('check_duplicated_transactions', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
    $(".resetSelection").on('click', function(e) {
        e.preventDefault();
        var data = {
            list_filter: '<?php echo $list_filter;?>'
        };
        loadView("list", data);
        // ajaxCall('list', data, function(json) {
        //     $('.p_pageContent').html(json.html);
        //     $('.searchFilterForm .searchFilter').val("");
        //     $(".filterDepartment").val("");
        // });
    });

	$(".check_failed_letters").on("click", function(e){
		e.preventDefault();
		var data = {
		};
		ajaxCall('check_failed_letters', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
})
</script>
