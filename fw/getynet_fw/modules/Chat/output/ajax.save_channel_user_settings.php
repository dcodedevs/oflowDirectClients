<?php
$s_lang_file = __DIR__."/../../../languages/default.php";
if(is_file($s_lang_file)) include($s_lang_file);
$s_lang_file = __DIR__."/../../../languages/".$variables->languageID.".php";
if(is_file($s_lang_file)) include($s_lang_file);
if(!function_exists("APIconnectorUser")) include(__DIR__."/../../../includes/APIconnector.php");

$fw_return_data['status'] = 0;

$v_param = array(
	'CHANNEL_ID'=>$_POST['channel_id'],
	'STATUS'=>$_POST['status'],
	'SOUND_WHEN_NEW_MESSAGE'=>$_POST['sound_when_new_message'],
	'NOTIFICATION_COUNT_ON_APP_SYMBOL'=>$_POST['notification_count_on_app_symbol']
);

$s_response = APIconnectorUser('channel_user_settings_set', $_COOKIE['username'], $_COOKIE['sessionID'], $v_param);
$v_response = json_decode($s_response, true);
if($v_response['status'] == 1)
{
	$fw_return_data['status'] = 1;
} else {
	$fw_error_msg['error_1'] = $formText_ErrorOccurredSavingChannelUserSettings_Framework;
}
$return['data'] = $fw_return_data;
$return['error'] = $fw_error_msg;
echo json_encode($return);
?>
