<?php
// Include column config
include(__DIR__.'/ajax.export_config_columns.php');
$o_query = $o_main->db->query("SELECT id FROM contactperson WHERE mainContact = 1 LIMIT 1");
$b_main_contactperson = ($o_query && 0 < $o_query->num_rows());

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

require_once("fnc_rewritebasisconfig.php");
rewriteCustomerBasisconfig();

?>
<div class="popupform">
<div class="popupformTitle"><?php echo $formText_ChooseWhichColumnsToExport_Output;?></div>
<div id="popup-validate-message" style="display:none;"></div>
<form class="output-form-contactperson" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=".$_GET['folderfile']."&folder=".$_GET['folder']."&inc_obj=".$inc_obj."&inc_act=export_filtered";?>" method="post" target="_blank">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="building_filter" value="<?php echo $_POST['building_filter'];?>">
	<input type="hidden" name="customergroup_filter" value="<?php echo $_POST['customergroup_filter'];?>">
	<input type="hidden" name="list_filter" value="<?php echo $_POST['list_filter'];?>">
	<input type="hidden" name="search_filter" value="<?php echo $_POST['search_filter'];?>">
	<input type="hidden" name="search_by" value="<?php echo $_POST['search_by'];?>">

	<div class="inner">
		<?php
		foreach($v_export_columns as $l_key => $v_export_column)
		{
			if('export_config_shop_name' == $v_export_column['name'] && 1 != $customer_basisconfig['activate_shop_name']) continue;
			?>
			<div class="row">
				<div class="col-xs-12">
					<?php
					if(6 == $v_export_column['type'])
					{
						?><a href="javascript:void(0);" class="script" onClick="if($('.<?php echo $v_export_column['toggle_class'];?>:checked').length>0){$('.<?php echo $v_export_column['toggle_class'];?>').removeProp('checked');}else{$('.<?php echo $v_export_column['toggle_class'];?>').prop('checked', true);}"><?php
						echo $v_export_column['label'];
						?></a><?php
					} else if(4 == $v_export_column['type'])
					{
						?><h3><?php
						echo $v_export_column['label'];
						if($b_main_contactperson)
						{
							?><select name="export_main_contactperson">
							<option value="0"><?php echo $formText_ExportAll_Output;?></option>
							<option value="1"><?php echo $formText_MainContactpersonsOnly_Output;?></option>
							</select><?php
						}
						?></h3><?php
					} else {
						?><label><?php
						if(0 < $v_export_column['type'])
						{
							?><input class="<?php echo $v_export_column['class'];?>" name="<?php echo $v_export_column['name'];?>" type="checkbox" value="1" <?php if($v_export_column['checked']) { echo 'checked';}?>>&nbsp;<?php
						}
						echo (0==$v_export_column['type']?'<h3>':'').$v_export_column['label'].(0==$v_export_column['type']?'<h3>':'');
						?></label>
						<?php
					}
					?>
				</div>
			</div>
			<?php
		}
		?>
	</div>
	<div class="popupformbtn">
		<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
		<input type="submit" name="sbmbtn" value="<?php echo $formText_Export_Output; ?>">
	</div>
</form>
</div>
<style>
input[type="checkbox"][readonly] {
  pointer-events: none;
}
</style>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$(function() {
	$("form.output-form-contactperson").validate({
		submitHandler: function(form) {
			form.submit();
			out_popup.close();
		}
	});
});
</script>
