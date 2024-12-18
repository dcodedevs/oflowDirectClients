<?php
$s_lang_file = __DIR__."/../../../languages/default.php";
if(is_file($s_lang_file)) include($s_lang_file);
$s_lang_file = __DIR__."/../../../languages/".$variables->languageID.".php";
if(is_file($s_lang_file)) include($s_lang_file);
if(!function_exists("APIconnectorAccount")) include(__DIR__."/../../../includes/APIconnector.php");
include(__DIR__."/includes/readAccessElements.php");
$l_edit = 0;
if(isset($_POST['channel_id']))
{
	$s_response = APIconnectorUser("channel_get", $_COOKIE['username'], $_COOKIE['sessionID'], array('CHANNEL_ID'=>$_POST['channel_id']));
	$v_response = json_decode($s_response,true);
	if($v_response['status'] == 1)
	{
		$v_channel = $v_response['channel'];
		$v_access = $v_response['access'];
		$l_edit = 1;
	} else {
		echo $formText_ErrorOccurredRetrievingData_Framework;
		return;
	}
}
$v_companies = $v_accounts = $v_apps = $v_groups = array();
$s_response = APIconnectorUser("company_get_list", $_COOKIE['username'], $_COOKIE['sessionID']);
$v_response = json_decode($s_response,true);
if(isset($v_response['data']))
{
	$v_companies = $v_response['data'];
}
$s_response = APIconnectorUser("group_get_list", $_COOKIE['username'], $_COOKIE['sessionID'], array('company_id'=>$_GET['companyID']));
$v_response = json_decode($s_response,true);
if(isset($v_response['status']) && $v_response['status'] == 1)
{
	$v_groups = $v_response['items'];
}
if($v_channel){
	$fw_return_data = $v_channel['name'];
} else {
	$fw_return_data = "<b>".$formText_AddNewGroupChat_Chat2."</b>";
}
?>
<div class="channel_edit_wrapper">
	<?php if($variables->useradmin == 1 || $variables->system_admin == 1) { ?>
	<div class="profile">
		<table border="0" cellpadding="0" cellspacing="0" rules="none" frame="void" width="100%" class="channel_info_table">
			<tr>
				<td width="120">
					<span class="formLabel"><?php echo $formText_ChannelName_Framework;?><span>
				</td>
				<td>
					<?php echo $v_channel['name']; ?>
					<?php /* <input type="hidden" name="channel_type" value="0">
					<input class="form-control input-sm size-50" type="text" name="channel_name" value="<?php echo $v_channel['name']; ?>"/> */?>
				</td>
			</tr>
			<tr>
				<td width="120">
					<span class="formLabel"><?php echo $formText_Status_Framework;?><span>
				</td>
				<td>
					<?php
					$v_channel_statuses = array(1=>$formText_PlacedInActiveListForAllUsers_Framework, 2=>$formText_PlacedInHiddenListForAllUsers_Framework, 3=>$formText_InactiveNotVisibleForAllUsers_Framework);

					echo $v_channel_statuses[$v_channel['status']];?>
					<?php /* <select class="form-control input-sm size-30" name="channel_status"><?php
					foreach($v_channel_statuses as $key => $item)
					{
						?><option value="<?php echo $key;?>"<?php echo ($v_channel['status']==$key?' selected':'');?>><?php echo $item;?></option><?php
					}
					?></select> */?>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<span class="formLabel">
						<?php echo $formText_DeactivateCommentAsNewMessage_Framework;?>
					</span>
					<input type="checkbox" class="checkboxInput input-sm size-30" name="deactivate_comment_as_new_message" disabled value="1"<?php echo ($v_channel['deactivate_comment_as_new_message']==1?' checked':'');?>>
					<span class="glyphicon glyphicon-pencil fw_delete_edit_icon_color edit_channel_settings_btn"></span>
				</td>
			</tr>
			<?php /*?><tr>
				<td><?php echo $formText_ChannelType_Framework;?></td>
				<td align="left">
				<select class="form-control input-sm" name="channel_type"><?php
				$v_channel_types = array(0=>$formText_StandardChannel_Framework, 1=>$formText_CaseChannel_Framework);
				foreach($v_channel_types as $key => $item)
				{
					?><option value="<?php echo $key;?>"<?php echo ($v_channel['type']==$key?' selected':'');?>><?php echo $item;?></option><?php
				}
				?></select>
				</td>
			</tr><?php */?>
			<?php /*?>
			<tr>
				<td valign="top"><?php echo $formText_ChannelAccess_Framework;?>&nbsp;<button class="btn btn-sm btn-default channel-access-add"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></button></td>
				<td align="left">
					<table class="table table-bordered table-condensed">
					<thead>
						<th width="30%"><?php echo $formText_Type_Framework;?></th>
						<th width="30%"><?php echo $formText_AccessLevel_Framework;?></th>
						<th width="30%"><?php echo $formText_Account_Framework." / ".$formText_Group_Framework;?></th>
						<th width="10%" style="text-align:center;"><?php echo $formText_Delete_Framework;?></th>
					</thead>
					<tbody class="channel-access-container"><?php
					$v_access_level = array(1=>$formText_ReadAndWrite_Framework, 2=>$formText_ReadOnly_Framework);
					$v_access_types = array(1=>$formText_AllCompanyUsers_Framework, 3=>$formText_UserGroup_Framework);
					foreach($v_access as $v_item)
					{
						fw_print_channel_access($v_item, $v_access_types, $v_access_level, $v_companies, $v_groups);
					}
					?>
					</tbody>
					</table>
				</td>
			</tr>*/
			?>
		</table>

		<script type="text/javascript">
		$(".edit_channel_settings_btn").on("click", function(){
			if(!fw_click_instance)
			{
				fw_click_instance = true;
				fw_loading_start();
				var data = { fwajax: 1, fw_nocss: 1}
				data.channel_id = '<?php echo $v_channel['id']?>';
				$.ajax({
					type: 'POST',
					cache: false,
					dataType: 'json',
					url: "<?php echo $variables->account_framework_url."index.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&getynetaccount=1&module=37&folder=output&modulename=Chat&folderfile=ajax.edit_channel_settings_popup";?>",
					data: data,
					success: function(json) {
						fw_click_instance = false;
						if(json.error !== undefined)
						{
							$.each(json.error, function(index, value){
								var _type = Array("error");
								if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
								self.add_alert(_type[0], value);
							});
							self.show_alert();
						} else {
							$('#popupeditboxcontent').html('');
							$('#popupeditboxcontent').html(json.html);
							out_popup_chat = $('#popupeditbox').bPopup(out_popup_options_chat);
							$("#popupeditbox:not(.opened)").remove();
						}
						fw_loading_end();
					}
				}).fail(function() {
					fw_loading_end();
					fw_click_instance = false;
				});
			}
		})
		</script>
	</div>
	<?php } else { ?>
	<div class="info-message"><?php echo $formText_OnlyCompanyAdministratorsCanAddNewChannel_Framework.". ".$formText_PleaseContactAdministratorsToCompleteThisAction_Framework;?></div>
	<?php } ?>
</div>
<input type="hidden" class="channel-access-origin" value="<?php
ob_start();
fw_print_channel_access(array(), $v_access_types, $v_access_level, $v_companies, $v_groups, true);
echo htmlentities(ob_get_clean());
?>">
<?php
function fw_print_channel_access($v_item, $v_access_types, $v_access_level, $v_companies, $v_groups, $b_new_item = false)
{
	if($b_new_item)
	{
		$v_item['company_id'] = $_GET['companyID'];
		$v_item['group_id'] = 0;
	}
	$b_editable = true;
	if(!$b_new_item && ($v_item['company_id'] != $_GET['companyID']))
	{
		$b_editable = false;
		?>
		<input type="hidden" name="channel_access_type[]" value="<?php echo $v_item['type'];?>">
		<input type="hidden" name="access_company_id[]" value="<?php echo $v_item['company_id'];?>">
		<input type="hidden" name="access_group_id[]" value="<?php echo $v_item['group_id'];?>">
		<input type="hidden" name="access_level[]" value="<?php echo $v_item['access_level'];?>">
		<?php
	}
	?><tr class="channel-access">
		<td>
			<input type="hidden" name="access_id[]" value="<?php echo $v_item['id'];?>">
			<select class="form-control input-sm channel-type" <?php echo ($b_editable ? 'name="channel_access_type[]"' : 'disabled');?>><?php
			foreach($v_access_types as $l_id => $s_type)
			{
				if(!isset($v_item['type'])) $v_item['type'] = $l_id;
				?><option value="<?php echo $l_id;?>"<?php echo ($v_item['type']==$l_id?' selected':'');?>><?php echo $s_type;?></option><?php
			}
			?></select>
		</td>
		<td>
			<div class="choice 1<?php echo ($v_item['type']!=1?' hide':'');?>">
				<select class="form-control input-sm" <?php echo ($b_editable ? 'name="access_company_id[]"' : 'disabled');?>><?php
				foreach($v_companies as $v_company)
				{
					if($v_item['company_id'] != $v_company['companyID']) continue;
					?><option value="<?php echo $v_company['companyID'];?>"<?php echo ($v_item['company_id']==$v_company['companyID']?' selected':'');?>><?php echo $v_company['companyname'];?></option><?php
				}
				?></select>
			</div>
			<div class="choice 3<?php echo ($v_item['type']!=3?' hide':'');?>">
				<select class="form-control input-sm" <?php echo ($b_editable ? 'name="access_group_id[]"' : 'disabled');?>><?php
				foreach($v_groups as $v_group)
				{
					?><option value="<?php echo $v_group['id'];?>"<?php echo ($v_item['group_id']==$v_group['id']?' selected':'');?>><?php echo $v_group['name'];?></option><?php
				}
				?></select>
			</div>
		</td>
		<td>
			<select class="form-control input-sm" <?php echo ($b_editable ? 'name="access_level[]"' : 'disabled');?>><?php
			foreach($v_access_level as $l_key=>$s_access_level)
			{
				?><option value="<?php echo $l_key;?>"<?php echo ($v_item['access_level']==$l_key?' selected':'');?>><?php echo $s_access_level;?></option><?php
			}
			?></select>
		</td>
		<td style="text-align:center;">
			<?php if($b_editable) { ?><button type="button" class="btn btn-default btn-sm" onClick="$(this).closest('.channel-access').remove();"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button><?php } ?>
		</td>
	</tr><?php
}
