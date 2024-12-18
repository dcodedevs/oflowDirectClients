<div class="p_headerLine"><?php

$ownerCompanyAccountConfig_sql = $o_main->db->query("SELECT * FROM ownercompany_accountconfig");
if($ownerCompanyAccountConfig_sql && $ownerCompanyAccountConfig_sql->num_rows() > 0) $ownerCompanyAccountConfig = $ownerCompanyAccountConfig_sql->row();

$ownerCompanyAccountConfig_sql = $o_main->db->query("SELECT * FROM ownercompany_basisconfig");
if($ownerCompanyAccountConfig_sql && $ownerCompanyAccountConfig_sql->num_rows() > 0) $ownerCompanyBasisConfig = $ownerCompanyAccountConfig_sql->row();

if($ownerCompanyAccountConfig->activateEditAllProjects > 0) {
	$ownerCompanyBasisConfig->activateEditAllProjects = $ownerCompanyAccountConfig->activateEditAllProjects - 1;
}
if($ownerCompanyAccountConfig->activateEditAllDepartment > 0) {
	$ownerCompanyBasisConfig->activateEditAllDepartment = $ownerCompanyAccountConfig->activateEditAllDepartment - 1;
}

$maximumOwnerCompanies = intval($ownerCompanyAccountConfig->max_number_ownercompanies);
if($maximumOwnerCompanies == 0) {
	$maximumOwnerCompanies = 1;
}
$currentOwnerCompanyCount_sql = $o_main->db->query("SELECT * FROM ownercompany WHERE content_status < 2");
$currentOwnerCompanyCount = $currentOwnerCompanyCount_sql->num_rows();
if($moduleAccesslevel > 10)
{
	if($currentOwnerCompanyCount < $maximumOwnerCompanies) {
	?>
	<div class="addNewCustomerBtn btnStyle">
		<div class="plusTextBox active">
			<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
			<div class="text"><?php echo $formText_AddNew_Output; ?></div>
		</div>
		<div class="clear"></div>
	</div>
	<?php } ?>
	<?php if($ownerCompanyBasisConfig->activateEditAllProjects) {?>
	    <div class="editAllProjectsBtn btnStyle">
	        <div class="plusTextBox active">
	            <div class="text"><?php echo $formText_EditAllProjects_Output; ?></div>
	        </div>
	        <div class="clear"></div>
	    </div>
	<?php } ?>
	<?php if($ownerCompanyBasisConfig->activateEditAllDepartment) {?>
	    <div class="editAllDepartmentsBtn btnStyle">
	        <div class="plusTextBox active">
	            <div class="text"><?php echo $formText_EditAllDepartments_Output; ?></div>
	        </div>
	        <div class="clear"></div>
	    </div>
	<?php } ?>

	<?php if($ownerCompanyAccountConfig->activate_company_product_sets) {?>
	    <div class="editProductSetsBtn btnStyle">
	        <div class="plusTextBox active">
	            <div class="text"><?php echo $formText_EditCompanyProductSets_Output; ?></div>
	        </div>
	        <div class="clear"></div>
	    </div>
	<?php } ?>
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
	<?php
	/*if(intval($_GET['cid']) > 0) {
		?>
		<div class=" btnStyle">
			<div class="plusTextBox active">
				<div class="text"><a target="_blank" href="/accounts/<?=$_GET['accountname']?>/modules/<?=$_GET['module']?>/output/includes/generatePdf.php?cid=<?php echo intval($_GET['cid']);?>"><?php echo $formText_DownloadPdf_Output; ?></a></div>
			</div>
			<div class="clear"></div>
		</div>
		<?php
	}*/
	?>
	<?php
}
?>
	<div class="p_headerLine_description"><?php echo $formText_MaximumNumberOfOwnerCompanies_output;?>: <?php echo $maximumOwnerCompanies;?></div>
	<div class="clear"></div>
</div>


<script type="text/javascript">
$(".editAllProjectsBtn").on('click', function(e){
    e.preventDefault();
    var data = {
        ownercompanyId: 0
    };
    ajaxCall('editAllProjects', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$(".editAllDepartmentsBtn").on('click', function(e){
    e.preventDefault();
    var data = {
        ownercompanyId: 0
    };
    ajaxCall('editAllDepartments', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});

$(".addNewCustomerBtn").on('click', function(e){
    e.preventDefault();
    var data = {
        ownercompanyId: 0
    };
    ajaxCall('editOwnerDetails', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$(".editProductSetsBtn").on('click', function(e){
    e.preventDefault();
    var data = {
        ownercompanyId: 0
    };
    ajaxCall('editProductSets', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
</script>
<style>
	.p_headerLine_description {
		float: right;
	}
	.p_headerLine .btnStyle.addEditGroup {
		margin-left: 40px;
	}
</style>
