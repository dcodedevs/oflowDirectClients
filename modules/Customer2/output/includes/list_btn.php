<?php
$o_query = $o_main->db->get('ownercompany_accountconfig');
$ownercompany_accountconfig = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM customer_basisconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $customer_basisconfig = $o_query->row_array();
}
$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_customer_accountconfig = $o_query->row_array();
}

if($v_customer_accountconfig['activate_brreg_sync'] > 0)
{
	$b_activate_brreg_sync = ($v_customer_accountconfig['activate_brreg_sync'] == 1 ? TRUE : FALSE);
} else {
	$b_activate_brreg_sync = ($customer_basisconfig['activate_brreg_sync'] == 1 ? TRUE : FALSE);
}
?>
<div class="p_headerLine"><?php
if($moduleAccesslevel > 10)
{
	//if(intval($_GET['cid']) == 0) {
	?>
	<div class="addNewCustomerBtn btnStyle">
		<div class="plusTextBox active">
			<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
			<div class="text"><?php echo $formText_AddNew_Output; ?></div>
		</div>
		<div class="clear"></div>
	</div>
	<?php //} ?>
	<div style="display:none;" class="boxed">
		<div id="exportForm"><?php

		?><form method="post" action="/accounts/<?=$_GET['accountname']?>/modules/<?=$_GET['module']?>/input/buttontypes/ExportIfbHomes/button.php" accept-charset="UTF-8">
			<p align="center">
			<?php print 'Eksport fra tabellen "'.$_GET['module'].'"'; ?>
			</p>
			<p align="center">
				<input type="hidden" value="<?=$submodule ?>" name="table">
				<input type="hidden" value="<?=$choosenListInputLang ?>" name="languageID">
				<input type="submit" value="Export!">
			</p>
		</form>

		</div>
	</div>
	<div class="addEditSelfDefinedFields btnStyle">
		<div class="plusTextBox active">
			<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
			<div class="text"><?php echo $formText_AddEditSelfDefinedFields_Output; ?></div>
		</div>
		<div class="clear"></div>
	</div>
	<div class="addEditSubscriptionType btnStyle">
		<div class="plusTextBox active">
			<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
			<div class="text"><?php echo $formText_AddEditSubscriptionType_Output; ?></div>
		</div>
		<div class="clear"></div>
	</div>

	<?php if($v_customer_accountconfig['activate_selfdefined_company'] && $variables->developeraccess >= 5) { ?>
    	<div class="edit_selfdefined_company btnStyle">
    		<div class="plusTextBox active">
    			<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
				<div class="text"><?php echo $formText_AddEditSelfdefinedCompany_Output; ?></div>
    			<div class="clear"></div>
    		</div>
    	</div>
	<?php } ?>
    <?php if($v_customer_accountconfig['activate_intranet_membership']) { ?>
    	<div class="edit_intranet_membership btnStyle btnStyleAdd">
    		<div class="plusTextBox active">
    			<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
				<div class="text"><?php echo $formText_AddEditIntranetMembership_Output; ?></div>
    			<div class="clear"></div>
    		</div>
    	</div>
	<?php } ?>

	<?php if ($ownercompany_accountconfig['activate_global_external_company_id']): ?>
    	<div class="syncExternalCustomersBtn btnStyle">
    		<div class="plusTextBox active">
    			<div class="text"><?php echo $formText_SyncCustomers_Output; ?></div>
    		</div>
    		<div class="clear"></div>
    	</div>
    <?php endif; ?>

    <?php if($v_customer_accountconfig['activateKeycardsSystem']) { ?>
    	<div class="goToKeycards btnStyle">
    		<div class="plusTextBox active">
    			<div class="text"><?php echo $formText_Keycards_Output; ?></div>
    			<div class="clear"></div>
    		</div>
    	</div>
	<?php } ?>
	<?php if($v_customer_accountconfig['activateGateSystem']) { ?>
	<div class="goToGateAccess btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_GateAccess_Output; ?></div>
			<div class="clear"></div>
		</div>
	</div>
	<?php } ?>
	<?php // include(__DIR__.'/ajax.import_data.php'); ?>
	<?php // include(__DIR__.'/ajax.import_data_company.php'); ?>
	<?php // include(__DIR__.'/ajax.import_data_selfdefined.php'); ?>
    <?php
    if($variables->developeraccess >= 5) { ?>
        <div class="importCompany btnStyle">
            <div class="plusTextBox active">
                <div class="text"><?php echo $formText_ImportCompany_Output; ?></div>
                <div class="clear"></div>
            </div>
        </div>
    <?php } ?>
	<?php
	if($b_activate_brreg_sync)
	{
		$o_find = $o_main->db->query("SELECT id FROM customer_sync_data GROUP BY customer_id");
		$l_found = $o_find ? $o_find->num_rows() : 0;
		?>
		<div class="goToBrregSync btnStyle">
			<div class="plusTextBox active">
				<div class="text"><?php echo $formText_BrregSync_Output.($l_found>0 ? ' ('.$l_found.')' : ''); ?></div>
				<div class="clear"></div>
			</div>
		</div>
		<?php
	}
	?>
	<?php
	if(isset($v_customer_accountconfig['activate_contactperson_overview_page']) && 1 == $v_customer_accountconfig['activate_contactperson_overview_page']) {
		?>
		<div class="show-contactperson-overview btnStyle">
			<div class="plusTextBox active">
				<div class="text"><a href="<?php echo $variables->account_root_url."modules/".$modulename."/output_overview/?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&caID=".$_GET['caID']."&companyID=".$_GET['companyID'].'&_='.time();?>" target="_blank"><?php echo $formText_ShowContactpersonOverview_Output; ?></a></div>
				<div class="clear"></div>
			</div>
		</div>
		<?php
	}
	?>

    <div class="searchDuplicates btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_ShowDuplicates_Output; ?></div>
			<div class="clear"></div>
		</div>
	</div>
    <div class="priceAdjustment btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_PriceAdjustment_Output; ?></div>
			<div class="clear"></div>
		</div>
	</div>
    <div class="tableEdit btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_TableEdit_Output; ?></div>
			<div class="clear"></div>
		</div>
	</div>
    <?php if($v_customer_accountconfig['activate_member_profile_link']) { ?>
        <div class="memberProfileLink btnStyle">
    		<div class="plusTextBox active">
    			<div class="text"><?php echo $formText_MemberProfileLink_Output; ?></div>
    			<div class="clear"></div>
    		</div>
    	</div>
    <?php } ?>
	<div class="clear"></div>

    <div class="createOrdersFromSelfdefined btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_CreateOrdersFromSelfdefinedField_Output; ?></div>
			<div class="clear"></div>
		</div>
	</div>
    <?php
    if($variables->developeraccess >= 5) {
        ?>
        <div class="editHistoryCategory btnStyle">
    		<div class="plusTextBox active">
    			<div class="text"><?php echo $formText_EditHistoryCategory_Output; ?></div>
    			<div class="clear"></div>
    		</div>
    	</div>
        <div class="importHistory btnStyle">
            <div class="plusTextBox active">
                <div class="text"><?php echo $formText_ImportHistory_output; ?></div>
                <div class="clear"></div>
            </div>
        </div>
        <?php
    }

    ?>
    <?php
}
?></div>


<script type="text/javascript">
$(".memberProfileLink").off("click").on("click", function(e){
    e.preventDefault();
    var data = {
        customerId: 0
    };
    loadView("member_list", data);
})
$(".addNewCustomerBtn").on('click', function(e){
    e.preventDefault();
    var data = {
        customerId: 0,
        newCustomer: 1
    };
    ajaxCall('editCustomerDetail', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$(".totalResultReportBtn").off("click").on("click", function(e){
    e.preventDefault();
    loadView("total_result_report");
})
$(".projectCodeOverviewBtn").off("click").on("click", function(e){
    e.preventDefault();
    loadView("project_code_overview");
})

$(".addEditSelfDefinedFields").on('click', function(e){
	e.preventDefault();
	var data = { };
    ajaxCall('add_selfdefined', data, function(obj) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(obj.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});

$(".addEditSubscriptionType").on('click', function(e){
	e.preventDefault();
	var data = { };
    ajaxCall('add_subscription_type', data, function(obj) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(obj.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$(".editHistoryCategory").off("click").on("click", function(e){
    e.preventDefault();
	var data = { };
    ajaxCall('edit_history_category', data, function(obj) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(obj.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
})
$(".importCompany").on('click', function(e){
	e.preventDefault();
	var data = { };
    ajaxCall('import_company', data, function(obj) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(obj.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$(".importHistory").on('click', function(e){
    e.preventDefault();
    var data = { };
    ajaxCall('import_historical', data, function(obj) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(obj.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});


$(".syncExternalCustomersBtn").on('click', function(e){
	e.preventDefault();
	var data = { };
    ajaxCall('syncExternalCustomers', data, function(obj) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(obj.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$(".goToKeycards").on('click', function(e){
    e.preventDefault();
    fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_keycard&inc_obj=list"; ?>', false, true);
});

$(".goToGateAccess").on('click', function(e){
    e.preventDefault();
    fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_gate&inc_obj=list"; ?>', false, true);
});

$(".goToBrregSync").on('click', function(e){
    e.preventDefault();
    fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folder=output_brreg&folderfile=output"; ?>', false, true);
});
$(".searchDuplicates").on('click', function(e){
    e.preventDefault();
    fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folder=output&folderfile=output&inc_obj=duplicate_search"; ?>', false, true);
});
$(".priceAdjustment").on('click', function(e){
    e.preventDefault();
    fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folder=output_adjustprices&folderfile=output"; ?>', false, true);
});
$(".createOrdersFromSelfdefined").on('click', function(e){
    e.preventDefault();
    fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folder=output_create_orders_selfdefined&folderfile=output"; ?>', false, true);
});
$(".tableEdit").on("click", function(e){
    e.preventDefault();
    loadView("table_list");
})
$('.edit_selfdefined_company').on('click', function(e){
	e.preventDefault();
	var data = { };
    ajaxCall('add_selfdefined_company', data, function(obj) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(obj.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$('.edit_intranet_membership').on('click', function(e){
	e.preventDefault();
	var data = { };
    ajaxCall('add_intranet_membership', data, function(obj) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(obj.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
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
