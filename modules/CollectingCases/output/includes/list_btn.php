<?php
$o_query = $o_main->db->get('ownercompany_accountconfig');
$ownercompany_accountconfig = $o_query ? $o_query->row_array() : array();
?>
<div class="p_headerLine"><?php
if($moduleAccesslevel > 10)
{
	if(intval($_GET['cid']) == 0) {
		/*
	?>
	<div class="addNewArticleBtn btnStyle">
		<div class="plusTextBox active">
			<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
			<div class="text"><?php echo $formText_AddNew_Output; ?></div>
		</div>
		<div class="clear"></div>
	</div>
	<!-- <div class="addEditProcessSteps btnStyle">
		<div class="plusTextBox active">
			<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
			<div class="text"><?php echo $formText_AddEditProcessSteps_Output; ?></div>
		</div>
		<div class="clear"></div>
	</div> -->
<?php */ } ?>
	<div class="process_cases_view btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_ProcessCasesView_Output; ?></div>
		</div>
		<div class="clear"></div>
	</div>
	<div class="get_collecting_report btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_CreateCollectingCasesStatisticsReport_Output; ?></div>
		</div>
		<div class="clear"></div>
	</div>
	<?php

	$s_sql = "SELECT collecting_cases.* FROM collecting_cases
	JOIN creditor ON creditor.id = collecting_cases.creditor_id
	JOIN creditor_transactions ct ON ct.collectingcase_id = collecting_cases.id
	WHERE (collecting_cases.due_date = '0000-00-00' OR collecting_cases.due_date is null OR collecting_cases.due_date = '1970-01-01')
	AND ct.open = 1
	AND IFNULL(creditor.is_demo, 0) = 0";
	$o_query = $o_main->db->query($s_sql);
	$missingWithoutDueDateCount = ($o_query ? $o_query->num_rows() : 0);
	?>
	<div class="cases_without_due_date btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $missingWithoutDueDateCount." "; echo $formText_CasesWithoutDueDate_Output; ?></div>
		</div>
		<div class="clear"></div>
	</div>
	
	<div class="marked_to_be_reset btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_MarkedToBeReset_Output; ?></div>
		</div>
		<div class="clear"></div>
	</div>
	<?php if($variables->developeraccess > 5) { ?>
		<div class="check_closed_cases btnStyle">
			<div class="plusTextBox active">
				<div class="text"><?php echo $formText_CheckClosedCases_Output; ?></div>
			</div>
			<div class="clear"></div>
		</div>
	<?php } ?>
	<div class="case_status_overview btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_CaseStatusOverview_Output; ?></div>
		</div>
		<div class="clear"></div>
	</div>
	<div class="clear"></div>
	<?php
}
?></div>


<script type="text/javascript">

$(".marked_to_be_reset").off("click").on("click", function(e){
	e.preventDefault();
	loadView("marked_to_be_reset_view");
})
$(".process_cases_view").off("click").on("click", function(e){
	e.preventDefault();
	loadView("process_cases_view");
})
$(".case_status_overview").off("click").on("click", function(e){
	e.preventDefault();
	loadView("case_status_overview");
})
$(".addEditProcessSteps").on('click', function(e){
	e.preventDefault();
	var data = { };
    ajaxCall('edit_process_steps', data, function(obj) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(obj.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$(".importPayments").on('click', function(e){
	e.preventDefault();
	var data = { };
    ajaxCall('importPayments', data, function(obj) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(obj.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$(".addNewArticleBtn").on('click', function(e){
    e.preventDefault();
    var data = {
    };
    ajaxCall('edit_case', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$(".get_collecting_report").on('click', function(e){
    e.preventDefault();
    var data = {
    };
    ajaxCall('get_collecting_report', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$(".cases_without_due_date").off("click").on("click", function(e){
	e.preventDefault();
	var order_field = $(this).data("orderfield");
	var order_direction = $(this).data("orderdirection");

	var data = {
		list_filter: 'without_due_date',
		search_filter: $('.searchFilter').val(),
		search_by: $(".searchBy").val(),
		order_field: order_field,
		order_direction: order_direction
	}
	loadView("list", data);
})
$(".check_closed_cases").off("click").on("click", function(e){
	e.preventDefault();
    var data = {
    };
    ajaxCall('check_closed_cases', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
})
</script>
<style>
	.p_headerLine .btnStyle.addEditCustomerGroup {
		margin-left: 40px;
	}
	.p_headerLine .btnStyle.importPayments {
		margin-left: 40px;
	}
	.cases_without_due_date {
		cursor: pointer;
	}
</style>
