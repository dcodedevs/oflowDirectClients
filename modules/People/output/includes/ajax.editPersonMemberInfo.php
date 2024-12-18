<?php
$people_contactperson_type = 2;
$sql = "SELECT * FROM accountinfo_basisconfig ORDER BY id";
$o_query = $o_main->db->query($sql);
$accountinfo_basisconfig = $o_query ? $o_query->row_array() : array();
if(intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']) > 0){
	$people_contactperson_type = intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']);
}
if(intval($variables->accountinfo['contactperson_type_to_use_in_people']) > 0)
{
	$people_contactperson_type = $variables->accountinfo['contactperson_type_to_use_in_people'];
}

$contactpersonId = isset($_POST['contactpersonId']) ? $o_main->db->escape_str($_POST['contactpersonId']) : 0;

$o_query = $o_main->db->query("SELECT * FROM contactperson WHERE id = ?", array($contactpersonId));
$crm_connection = $o_query ? $o_query->row_array() : array();

$action = isset($_POST['action']) ? $_POST['action'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : 0;
if($crm_connection){
	$currentMember = null;
	if($moduleAccesslevel > 10) {
		$s_sql = "SELECT * FROM contactperson WHERE customerId = ? AND admin = 1";
		$o_result = $o_main->db->query($s_sql, array($crm_connection['crm_customer_id']));
		$contactPersonAdmins = $o_result ? $o_result->result_array() : array();
		$adminCount = count($contactPersonAdmins);

		if($action == "update" && $crm_connection){
			if($adminCount > 1 || $_POST['type'] == 1){
				$s_sql = "UPDATE contactperson SET admin = ? WHERE id = ?";
				$o_result = $o_main->db->query($s_sql, array($_POST['type'], $crm_connection['id']));
				if($o_result){
					$fw_return_data = 1;
					return;
				} else {
					$fw_error_msg = array($formText_ErrorUpdatingEntry_output);
					return;
				}
			} else {
				$fw_error_msg = array($formText_CanNotRemoveLastAdministrator_output);
				return;
			}
		}

		if($action == "updateVisible" && $crm_connection){
			$s_sql = "UPDATE contactperson SET notVisibleInMemberOverview = ? WHERE id = ?";
			$o_result = $o_main->db->query($s_sql, array($_POST['type'], $crm_connection['id']));
			if($o_result){
				$fw_return_data = 1;
				return;
			} else {
				$fw_error_msg = array($formText_ErrorUpdatingEntry_output);
				return;
			}
		}
	}

	?><div class="popupform popupform-<?php echo $groupId;?>">
		<div id="popup-validate-message" style="display:none;"></div>
			<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&folderfile="
			.$_GET['folderfile']."&folder=".$_GET['folder']."&inc_obj=ajax&inc_act=".$_GET['inc_act'];?>" method="post">
			<input type="hidden" name="fwajax" value="1">
			<input type="hidden" name="fw_nocss" value="1">
			<input type="hidden" name="output_form_submit" value="1">
			<input type="hidden" name="contactpersonId" value="<?php echo $contactpersonId;?>">
			<input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_POST['module']."&folderfile=output&folder=output_groups&inc_obj=list"; ?>">
			<div class="popupformTitle"><?php  echo $formText_EditMember_output; ?></div>
			<div class="inner">
				<div class="line checkboxLine">
	        		<div class="lineTitle"><?php echo $formText_Administrator_Output; ?></div>
	        		<div class="lineInput">
						<input type="checkbox" value="<?php echo $contactpersonId;?>" autocomplete="off" class="memberEditCheckbox" <?php if($crm_connection['admin']) echo 'checked'?>/></td>
	                </div>
	        		<div class="clear"></div>
	    		</div>
				<div class="line checkboxLine">
	        		<div class="lineTitle"><?php echo $formText_NotVisibleForOtherMembers_Output; ?></div>
	        		<div class="lineInput">
						<input type="checkbox" value="<?php echo $contactpersonId;?>" autocomplete="off" class="notVisible" <?php if($crm_connection['notVisibleInMemberOverview']) echo 'checked'?>/></td>
	                </div>
	        		<div class="clear"></div>
	    		</div>
			</div>
			<div class="popupformbtn">
				<button type="button" class="output-btn b-large b-close fw_button_not_filled_color"><?php echo $formText_Close_Output;?></button>
			</div>
		</form>
	</div>

	<?php

$s_path = $variables->account_root_url;

$v_script = array(
  'modules/'.$module.'/output/elementsOutput/jquery.validate/jquery.validate.min.js',
);

foreach($v_script as $s_item)
{
  $l_time = filemtime(BASEPATH.$s_item);
  ?><script type="text/javascript" src="<?php echo $s_path.$s_item.'?v='.$l_time;?>"></script><?php
}

?>

	<!-- <script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script> -->
	<script type="text/javascript">

	$(document).ready(function() {
		$(".memberEditCheckbox").change(function(){
			var checkbox = $(this);
			var contactpersonId = $(this).val();
			if($(this).is(":checked")){
				var type = 1;
			} else {
				var type = 0;
			}
			var data = {
				action: "update",
				contactpersonId: contactpersonId,
				type: type
			};
			$("#popup-validate-message").html("");
			ajaxCall({module_file:'editPersonMemberInfo', module_name: 'People', module_folder: 'output'}, data, function(json) {
				if(json.error !== undefined)
				{
					$.each(json.error, function(index, value){
						$("#popup-validate-message").append("<div>"+value+"</div>").show();
					});
					fw_click_instance = fw_changes_made = false;
					if(type == 1){
						checkbox.prop("checked", false);
					} else if(type == 0){
						checkbox.prop("checked", true);
					}
				} else {
					$('#popupeditbox').addClass("close-reload");
				}
			});
		})


		$(".notVisible").change(function(){
			var checkbox = $(this);
			var contactpersonId = $(this).val();
			if($(this).is(":checked")){
				var type = 1;
			} else {
				var type = 0;
			}
			var data = {
				action: "updateVisible",
				contactpersonId: contactpersonId,
				type: type
			};
			$("#popup-validate-message").html("");
			ajaxCall({module_file:'editPersonMemberInfo', module_name: 'People', module_folder: 'output'}, data, function(json) {
				if(json.error !== undefined)
				{
					$.each(json.error, function(index, value){
						$("#popup-validate-message").append("<div>"+value+"</div>").show();
					});
					fw_click_instance = fw_changes_made = false;
					if(type == 1){
						checkbox.prop("checked", false);
					} else if(type == 0){
						checkbox.prop("checked", true);
					}
				} else {
					$('#popupeditbox').addClass("close-reload");
				}
			});
		})

	});

	</script>
	<style>
	.memberSearchRow input {
		width: 100%;
		padding: 8px 15px;
		border: 1px solid #cecece;
	}
	.memberSearchRow {
		margin-bottom: 15px;
	}
	.memberCountRow {
		margin-bottom: 10px;
	}
	.memberExplanationRow {
		margin-bottom: 10px;
		font-weight: bold;
	}
	.lineInput .otherInput {
		margin-top: 10px;
	}
	.lineInput input[type="radio"]{
		margin-right: 10px;
		vertical-align: middle;
	}
	.lineInput input[type="radio"] + label {
		margin-right: 10px;
		vertical-align: middle;
	}
	.popupform .inlineInput input.popupforminput {
		display: inline-block;
		width: auto;
		vertical-align: middle;
		margin-right: 20px;
	}
	.popupform .inlineInput label {
		display: inline-block !important;
		vertical-align: middle;
	}
	.popupform .lineInput.lineWhole {
		font-size: 14px;
	}
	.popupform .lineInput.lineWhole label {
		font-weight: normal !important;
	}
	.selectDivModified {
		display:block;
	}
	.invoiceEmail {
		display: none;
	}
	label.error {
		color: #c11;
		margin-left: 10px;
		border: 0;
		display: inline !important;
	}
	.popupform .popupforminput.error { border-color:#c11 !important;}
	#popup-validate-message, .error-msg { font-weight:bold; color:#c11; padding-bottom:10px; }
	/* css for timepicker */
	.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
	.ui-timepicker-div dl { text-align: left; }
	.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
	.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
	.ui-timepicker-div td { font-size: 90%; }
	.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
	.clear {
		clear:both;
	}
	.inner {
		padding:10px;
	}
	.pplineV {
		position:absolute;
		top:0;bottom:0;left:70%;
		border-left:1px solid #e8e8e8;
	}
	.popupform input.popupforminput, .popupform textarea.popupforminput, .popupform select.popupforminput, .col-md-8z input {
		width:100%;
		border-radius: 4px;
		padding:5px 10px;
		font-size:12px;
		line-height:17px;
		color:#3c3c3f;
		background-color:transparent;
		-webkit-box-sizing: border-box;
		   -moz-box-sizing: border-box;
			 -o-box-sizing: border-box;
				box-sizing: border-box;
		font-weight:400;
		border: 1px solid #cccccc;
	}
	.popupformname {
		font-size:12px;
		font-weight:bold;
		padding:5px 0px;
	}
	.popupforminput.botspace {
		margin-bottom:10px;
	}
	textarea {
		min-height:50px;
		max-width:100%;
		min-width:100%;
		width:100%;
	}
	.popupformname {
		font-weight: 700;
		font-size: 13px;
	}
	.popupformbtn {
		text-align:right;
		margin:10px;
	}
	.popupformbtn input {
		border-radius:4px;
		border:1px solid #0393ff;
		background-color:#0393ff;
		font-size:13px;
		line-height:0px;
		padding: 20px 35px;
		font-weight:700;
		color:#FFF;
		margin-left:10px;
	}
	.error {
		border: 1px solid #c11;
	}
	.popupform .lineTitle {
		font-weight:700;
	}
	.popupform .line .lineTitle {
		width:30%;
		float:left;
		font-weight:700;
		padding:5px 0;
	}

	.popupform .line .lineTitleWithSeperator {
		width:100%;
		margin: 20px 0;
		padding:0 0 10px;
		border-bottom:1px solid #EEE;
	}

	.popupform .line .lineInput {
		width:70%;
		float:left;
	}
	.addSubProject {
		margin-bottom: 10px;
	}
	</style>
	<?php
}?>
