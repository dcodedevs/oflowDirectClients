<?php
$o_query = $o_main->db->get('ownercompany_accountconfig');
$ownercompany_accountconfig = $o_query ? $o_query->row_array() : array();
?>
<div class="p_headerLine"><?php
if($moduleAccesslevel > 10)
{
	// $o_query = $o_main->db->get('auto_task');
	
	// $s_sql = "SELECT * FROM auto_task WHERE script_path = ? AND content_status = 0";
	// $o_query = $o_main->db->query($s_sql, array("modules/CreditorsMarkedCeasesToExist/autotask_check_for_ceases_to_exist/run.php"));
	// $auto_task = $o_query ? $o_query->row_array() : array();
	// echo $formText_UpdatingAutomaticallyEachNight_output;
	// echo " ".$formText_LastUpdate_output." ". date("d.m.Y H:i:s", strtotime($auto_task['last_run']));
	$s_sql = "SELECT 
	ccc.id
	FROM collecting_company_cases ccc
	WHERE (ccc.case_closed_date = '0000-00-00' OR ccc.case_closed_date IS NULL)
	AND (ccc.case_limitation_date = '0000-00-00' OR ccc.case_limitation_date IS NULL)
	ORDER BY ccc.case_limitation_date ASC";
	$o_query = $o_main->db->query($s_sql);
	$emptyCount = ($o_query ? $o_query->num_rows() : 0);

	?>
	<div class="fillEmpty btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_FillEmptyCaseLimitationDates_Output; ?> (<?php echo $emptyCount?>)</div>
		</div>
		<div class="clear"></div>
	</div>
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
$(".fillEmpty").off('click').on('click', function(e){
	e.preventDefault();
	var data = {
		creditor_id: 0
	};
	ajaxCall({module_file: 'fill_empty', module_folder: 'output'}, data, function(json) {
		$('#popupeditboxcontent').html('');
		$('#popupeditboxcontent').html(json.html);
		out_popup = $('#popupeditbox').bPopup(out_popup_options).addClass("close-reload");
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
