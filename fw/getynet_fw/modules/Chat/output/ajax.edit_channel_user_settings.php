<?php
ob_start();?>
<div class="module_customized"><?php
$s_lang_file = __DIR__."/../../../languages/default.php";
if(is_file($s_lang_file)) include($s_lang_file);
$s_lang_file = __DIR__."/../../../languages/".$variables->languageID.".php";
if(is_file($s_lang_file)) include($s_lang_file);
if(!function_exists("APIconnectorUser")) include(__DIR__."/../../../includes/APIconnector.php");

if(isset($_POST['channel_id']))
{
	$s_response = APIconnectorUser("channel_get", $_COOKIE['username'], $_COOKIE['sessionID'], array('CHANNEL_ID'=>$_POST['channel_id']));
	$v_response = json_decode($s_response,true);
	if($v_response['status'] == 1)
	{
		$v_channel = $v_response['channel'];
		$s_response = APIconnectorUser("channel_user_settings_get", $_COOKIE['username'], $_COOKIE['sessionID'], array('CHANNEL_ID'=>$_POST['channel_id']));
		$v_response = json_decode($s_response,true);
		if($v_response['status'] == 1)
		{
			$v_settings = $v_response['settings'];
		} else {
			$v_settings = array();
		}
	} else {
		echo $formText_ErrorOccurredRetrievingData_Framework;
		return;
	}
}
if($v_channel){
	$fw_return_data = $v_channel['name'];
}
?>
<div class="channel_edit_wrapper">
	<h3 class="panel-title"><?php echo $formText_MyUserSettingsForThisChannel_Framework;?></h3>

	<form id="channel-update-form" name="upadate" action="<?php echo $_POST['fw_url'];?>getynet_fw/modules/Chat/output/ajax.save_channel_user_settings.php" method="POST">
	<input type="hidden" name="channel_id" value="<?php echo $_POST['channel_id']; ?>" />
	<div class="profile">
		<table border="0" cellpadding="0" cellspacing="0" rules="none" frame="void" width="100%">
			<tr>
				<td><?php echo $formText_Status_Framework;?></td>
				<td>
					<select class="form-control input-sm size-10" name="status"><?php
					$v_options = array(1=>$formText_Active_Framework, 2=>$formText_Hidden_Framework);
					foreach($v_options as $key => $item)
					{
						?><option value="<?php echo $key;?>"<?php echo ($v_settings['status']==$key?' selected':'');?>><?php echo $item;?></option><?php
					}
					?></select>
				</td>
			</tr>
			<?php /*
			<tr>
				<td><?php echo $formText_SoundWhenNewMessage_Framework;?></td>
				<td>
					<select class="form-control input-sm size-10" name="sound_when_new_message"><?php
					$v_options = array(0=>$formText_On_Framework, 1=>$formText_Off_Framework);
					foreach($v_options as $key => $item)
					{
						?><option value="<?php echo $key;?>"<?php echo ($v_settings['sound_when_new_message']==$key?' selected':'');?>><?php echo $item;?></option><?php
					}
					?></select>
				</td>
			</tr>*/?>
			<input type="hidden" name="notification_count_on_app_symbol" value="0">
			<?php /*?><tr>
				<td><?php echo $formText_NotificationCountOnAppSymbol_Framework;?></td>
				<td>
					<select class="form-control input-sm size-10" name="notification_count_on_app_symbol"><?php
					$v_options = array(0=>$formText_On_Framework, 1=>$formText_Off_Framework);
					foreach($v_options as $key => $item)
					{
						?><option value="<?php echo $key;?>"<?php echo ($v_settings['notification_count_on_app_symbol']==$key?' selected':'');?>><?php echo $item;?></option><?php
					}
					?></select>
				</td>
			</tr><?php */?>

			<tr><td colspan="2">
				<a class="fw-btn fw-btn-small fw_button_color script"><?php echo $formText_Save_Framework;?></a>
			</td>
			</tr>
		</table>
	</div>
	</form>
</div>
<?php
$data['html'] = ob_get_clean();
echo json_encode($data);
?>
