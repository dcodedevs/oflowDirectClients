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
	<div class="cases_view btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_CasesView_Output; ?></div>
		</div>
		<div class="clear"></div>
	</div>
	<div class="worklist_view btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_WorklistView_Output; ?></div>
		</div>
		<div class="clear"></div>
	</div>
	<?php if($_GET['inc_obj'] == "worklist") { ?>
		<div class="add_worklist btnStyle">
			<div class="plusTextBox active">
				<div class="text"><?php echo $formText_AddWorklist_Output; ?></div>
			</div>
			<div class="clear"></div>
		</div>
	<?php } ?>
	<div class="get_collecting_report btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_CreateCollectingCasesStatisticsReport_Output; ?></div>
		</div>
		<div class="clear"></div>
	</div>
	<div class="view_collecting_report btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_ViewCollectingCasesStatisticsReport_Output; ?></div>
		</div>
		<div class="clear"></div>
	</div>
	<div class="show_credit_report_for_db btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_ShowCreditReportForDb_Output; ?></div>
		</div>
		<div class="clear"></div>
	</div>
	<?php if(20 == $variables->developeraccess) { ?>
		<div class="export_cases">
			<div class="plusTextBox active">
				<div class="text"><?php echo $formText_Export_Output; ?></div>
			</div>
			<div class="clear"></div>
		</div>
	<?php } ?>
	
	<div class="export_cases2">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_ExportCasesWithMultipleInvoices_Output; ?></div>
		</div>
		<div class="clear"></div>
	</div>
	<div class="export_cases3">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_ExportCasesWithObjection_Output; ?></div>
		</div>
		<div class="clear"></div>
	</div>
	<div class="clear"></div>
	<?php
}
?></div>


<script type="text/javascript">
$(".cases_view").off("click").on("click", function(e){
	e.preventDefault();
	loadView("list");
})
$(".worklist_view").off("click").on("click", function(e){
	e.preventDefault();
	loadView("worklist");
})
$(".add_worklist").off("click").on("click", function(e){
	e.preventDefault();
	var data = {
	};
	ajaxCall('edit_worklist', data, function(json) {
		$('#popupeditboxcontent').html('');
		$('#popupeditboxcontent').html(json.html);
		out_popup = $('#popupeditbox').bPopup(out_popup_options);
		$("#popupeditbox:not(.opened)").remove();
	});
})
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
$(".view_collecting_report").off("click").on("click", function(e) {
	e.preventDefault();
	loadView("view_collecting_report");
});
$('.show_credit_report_for_db').off("click").on("click", function(e) {
	e.preventDefault();
	loadView({ module_file: 'output', module_folder: 'output_export_report_for_db' });
});
$(".export_cases").off("click").on("click", function(){
	var generateIframeDownload = function(){
		fetch("<?php echo $extradir;?>/output/includes/export_list_info.php?mainlist_filter=<?php echo $mainlist_filter;?>&list_filter=<?php echo $list_filter;?>&sublist_filter=<?php echo $sublist_filter;?>&time=<?php echo time();?>")
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
$(".export_cases2").on('click', function(e){
    e.preventDefault();
    var data = {
    };
    ajaxCall('export_cases_with_multiple_invoices', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$(".export_cases3").on('click', function(e){
    e.preventDefault();
    var data = {
    };
    ajaxCall('export_cases_with_objections', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
</script>
<style>
	.p_headerLine .btnStyle.addEditCustomerGroup {
		margin-left: 40px;
	}
	.p_headerLine .btnStyle.importPayments {
		margin-left: 40px;
	}
	.export_cases {
		cursor: pointer;
	}
	.export_cases2  {
		cursor: pointer;
	}
	.export_cases3  {
		cursor: pointer;
	}
</style>
