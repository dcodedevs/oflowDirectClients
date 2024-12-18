<?php
$groupId = $_POST['groupId'] ? $o_main->db->escape_str($_POST['groupId']) : 0;
$username = $_POST['username'] ? $o_main->db->escape_str($_POST['username']) : 0;

$sql = "SELECT p.* FROM contactperson p WHERE p.email = ? AND p.content_status < 2";
$o_query = $o_main->db->query($sql, array($variables->loggID));
$currentContactPerson = $o_query ? $o_query->row_array(): array();

$action = $_POST['action'] ? $_POST['action'] : '';
$status = $_POST['status'] ? $_POST['status'] : 0;
if($action == 'addYourselfAsAdmin'){
	$o_main->db->query("UPDATE accountinfo SET force_cache_refresh = NOW()");
	if($accessElementAllow_AddOneselfAsGroupadmin) {
		if($currentContactPerson){
			$o_query = $o_main->db->query("INSERT INTO contactperson_group_user SET
				created = NOW(),
				createdBy = ?,
				contactperson_group_id = ?,
				contactperson_id = ?,
				type = 2,
				status = 0", array($variables->loggID, $groupId, $currentContactPerson['id']));

			if($o_query){
				$fw_return_data = 1;
				return;
			} else {
				$fw_error_msg = array($data['error']);
				return;
			}
		} else {
			$fw_error_msg = array($formText_MissingContactPerson_output);
		}
	} else {
		$fw_error_msg = array($formText_NoAccessForThisAction_output);
		return;
	}
}
if($groupId) {
	$data = json_decode(APIconnectorUser("group_get", $variables->loggID, $variables->sessionID, array('id'=>$groupId)),true);
	if($data['status'] == 1){
		$group_getynet_data = $data['item'];
	}

	$data = json_decode(APIconnectorUser("group_user_get_list", $variables->loggID, $variables->sessionID, array('group_id'=>$group_getynet_data['id'])),true);
	if($data['status'] == 1){
		$adminCount = 0;
		$members = $data['items'];
		$isAdmin = false;
		foreach($members as $member){
			if($member['type'] == 2){
				$adminCount++;
			}
			if(mb_strtolower($username) == mb_strtolower($member['username'])){
				$isMember = true;
				if($member['type'] == 2){
					$isAdmin = true;
				}
			}
		}
		$memberCount = count($members);
	}
}
if($moduleAccesslevel > 10) {
	if($action == "update" && $group_getynet_data && $username){
		$o_main->db->query("UPDATE accountinfo SET force_cache_refresh = NOW()");
		if($adminCount > 1 || $_POST['type'] == 2){
		    $data = json_decode(APIconnectorUser("group_user_set", $variables->loggID, $variables->sessionID, array('group_id'=>$group_getynet_data['id'],'username'=>$username, 'type'=>$_POST['type'])),true);
			if($data['status']){
				$fw_return_data = 1;
				return;
			} else {
				$fw_error_msg = array($data['error']);
				return;
			}
		} else {
			$fw_error_msg = array($formText_CanNotRemoveLastAdministrator_output);
			return;
		}
	}
}
if($group_getynet_data){

?><div class="popupform popupform-<?php echo $groupId;?>">
	<div id="popup-validate-message" style="display:none;"></div>
		<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&folderfile="
		.$_GET['folderfile']."&folder=".$_GET['folder']."&inc_obj=ajax&inc_act=".$_GET['inc_act'];?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="groupId" value="<?php echo $group_getynet_data['id'];?>">
		<input type="hidden" name="username" value="<?php echo $username;?>">
		<input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_POST['module']."&folderfile=output&folder=output_groups&inc_obj=list"; ?>">
		<div class="popupformTitle"><?php  echo $formText_EditMember_output; ?></div>
		<div class="inner">
			<div class="line checkboxLine">
        		<div class="lineTitle"><?php echo $formText_Administrator_Output; ?></div>
        		<div class="lineInput">
					<input type="checkbox" value="<?php echo $username;?>" class="memberEditCheckbox" <?php if($isAdmin) echo 'checked'?>/></td>
                </div>
        		<div class="clear"></div>
    		</div>
			<div class="">

			</div>
		</div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close fw_button_not_filled_color"><?php echo $formText_Close_Output;?></button>
		</div>
	</form>
</div>
<script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

$(document).ready(function() {
	$(".memberEditCheckbox").change(function(){
		var checkbox = $(this);
		var username = $(this).val();
		if($(this).is(":checked")){
			var type = 2;
		} else {
			var type = 1;
		}
		var data = {
			action: "update",
			username: username,
			groupId: "<?php echo $group_getynet_data['id'];?>",
			type: type
		};
		$("#popup-validate-message").html("");
		ajaxCall({module_file:'editMemberInfo', module_name: 'Customer2', module_folder: 'output_groups'}, data, function(json) {
			if(json.error !== undefined)
			{
				$.each(json.error, function(index, value){
					$("#popup-validate-message").append("<div>"+value+"</div>").show();
				});
				fw_click_instance = fw_changes_made = false;
				if(type == 2){
					checkbox.prop("checked", false);
				} else if(type == 1){
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
<?php } ?>
