<?php
$o_query = $o_main->db->get('ownercompany_accountconfig');
$ownercompany_accountconfig = $o_query ? $o_query->row_array() : array();
?>
<div class="p_headerLine"><?php
if($moduleAccesslevel > 10)
{
	$o_query = $o_main->db->get('auto_task');
	
	$s_sql = "SELECT * FROM auto_task WHERE script_path = ? AND content_status = 0";
	$o_query = $o_main->db->query($s_sql, array("modules/CreditorsMarkedCeasesToExist/autotask_check_for_ceases_to_exist/run.php"));
	$auto_task = $o_query ? $o_query->row_array() : array();
	echo $formText_UpdatingAutomaticallyEachNight_output;
	echo " ".$formText_LastUpdate_output." ". date("d.m.Y H:i:s", strtotime($auto_task['last_run']));
	/*
	?>
	<div class="check_for_bankruptcy btnStyle">
		<div class="plusTextBox active">
			<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
			<div class="text"><?php echo $formText_CheckForBankruptCreditors_Output; ?></div>
		</div>
		<div class="clear"></div>
	</div>
    <?php*/
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
$(".check_for_bankruptcy").off('click').on('click', function(e){
	e.preventDefault();
	var data = {
		creditor_id: 0
	};
	ajaxCall({module_file: 'check_for_bankruptcy', module_folder: 'output'}, data, function(json) {
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
