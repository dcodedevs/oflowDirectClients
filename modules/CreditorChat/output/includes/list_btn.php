<?php
$o_query = $o_main->db->get('ownercompany_accountconfig');
$ownercompany_accountconfig = $o_query ? $o_query->row_array() : array();
?>
<div class="p_headerLine"><?php
if($moduleAccesslevel > 10)
{
	?>
	<div class="addNewCreditorBtn btnStyle">
		<div class="plusTextBox active">
			<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
			<div class="text"><?php echo $formText_AddNew_Output; ?></div>
		</div>
		<div class="clear"></div>
	</div>
    <div class="showCreditorConfigListBtn btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_ShowCreditorConfigList_Output; ?></div>
			<div class="clear"></div>
		</div>
	</div>
    <div class="showDuplicatedFeeResetBtn btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_ShowDuplicatedFeeResets_Output; ?></div>
			<div class="clear"></div>
		</div>
	</div>
	<div class="viewTabSortingLogPage btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_ShowTabSortingLogPage_Output; ?></div>
		</div>
		<div class="clear"></div>
	</div>

	<?php if($variables->developeraccess > 5) {?>
		<div class="exportIncome btnStyle">
			<div class="plusTextBox active">
				<div class="text"><?php echo $formText_ExportIncome_Output; ?></div>
			</div>
			<div class="clear"></div>
		</div>
	<?php } ?>
    <?php
}
?></div>


<script type="text/javascript">
$(".showDuplicatedFeeResetBtn").off('click').on('click', function(e){
	e.preventDefault();
	var data = {
		creditor_id: 0
	};
	loadView("duplicated_fee_reset_list");
});
$(".viewTabSortingLogPage").off('click').on('click', function(e){
	e.preventDefault();
	var data = {
		creditor_id: 0
	};
	loadView("tab_sorting_log_page");
});
$(".addNewCreditorBtn").off('click').on('click', function(e){
	e.preventDefault();
	var data = {
		creditor_id: 0
	};
	ajaxCall({module_file: 'edit_creditor', module_folder: 'output'}, data, function(json) {
		$('#popupeditboxcontent').html('');
		$('#popupeditboxcontent').html(json.html);
		out_popup = $('#popupeditbox').bPopup(out_popup_options);
		$("#popupeditbox:not(.opened)").remove();
	});
});
$(".exportIncome").off('click').on('click', function(e){
	e.preventDefault();
	var data = {
		creditor_id: 0
	};
	ajaxCall({module_file: 'export_income', module_folder: 'output_creditor'}, data, function(json) {
		$('#popupeditboxcontent').html('');
		$('#popupeditboxcontent').html(json.html);
		out_popup = $('#popupeditbox').bPopup(out_popup_options);
		$("#popupeditbox:not(.opened)").remove();
	});
});
$(".showCreditorConfigListBtn").off("click").on("click", function(e){
    e.preventDefault();
    fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list"; ?>', false, true);
});
</script>
<style>
	.p_headerLine .btnStyle.addEditCustomerGroup {
		margin-left: 40px;
	}
	.p_headerLine .btnStyle.addEditSelfDefinedFields {
		margin-left: 40px;
	}
	.p_headerLine .btnStyle.addEditSubscriptionType {
		margin-left: 40px;
	}
	.p_headerLine .btnStyle.edit_selfdefined_company {
		margin-left: 40px;
	}
	.p_headerLine .btnStyle.btnStyleAdd {
		margin-left: 40px;
	}
</style>
