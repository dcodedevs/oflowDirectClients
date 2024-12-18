<?php
$s_lang_file = __DIR__."/../../../languages/default.php";
if(is_file($s_lang_file)) include($s_lang_file);
$s_lang_file = __DIR__."/../../../languages/".$variables->languageID.".php";
if(is_file($s_lang_file)) include($s_lang_file);
if(!function_exists("APIconnectorAccount")) include(__DIR__."/../../../includes/APIconnector.php");

$fw_return_data['status'] = 0;

$b_failure = FALSE;
$b_add = FALSE;
$_POST['channel_status'] = intval($_POST['channel_status']);
if(intval($_POST['channel_id'])>0)
{
	$v_param = array('ID'=>$_POST['channel_id'], 'NAME'=>$_POST['channel_name'], 'TYPE'=>$_POST['channel_type'], 'STATUS'=>$_POST['channel_status']);
} else {
	$b_add = TURE;
	$v_param = array('NAME'=>$_POST['channel_name'], 'TYPE'=>$_POST['channel_type'], 'STATUS'=>$_POST['channel_status']);
}
$v_param['deactivate_comment_as_new_message'] = ((isset($_POST['deactivate_comment_as_new_message']) && $_POST['deactivate_comment_as_new_message'] == 1) ? 1 : 0);
$s_response = APIconnectorUser('channel_set', $_COOKIE['username'], $_COOKIE['sessionID'], $v_param);
$v_response = json_decode($s_response, true);
if($v_response['status'] == 1)
{
	if($b_add)
	{
		$_POST['channel_id'] = $v_response['channel_id'];
	}

	$v_param = array('CHANNEL_ID'=>$_POST['channel_id'], 'ACCESS'=>array());
	foreach($_POST['access_id'] as $l_key => $l_access_id)
	{
		$v_access = array
		(
			'ID'=>$l_access_id,
			'TYPE'=>$_POST['channel_access_type'][$l_key],
			'COMPANY_ID'=>$_POST['access_company_id'][$l_key],
			'GROUP_ID'=>$_POST['access_group_id'][$l_key],
			'ACCESS_LEVEL'=>$_POST['access_level'][$l_key]
		);
		$v_param['ACCESS'][] = $v_access;
	}
	$s_response = APIconnectorUser('channel_access_set', $_COOKIE['username'], $_COOKIE['sessionID'], $v_param);
	$v_response = json_decode($s_response, true);

	if($v_response['status'] != 1)
	{
		$b_failure = TRUE;
	}
} else {
	$b_failure = TRUE;
}

if($b_failure)
{
	$fw_error_msg['error_1'] = $formText_ErrorOccurredSavingChannel_Framework;
} else {
	$fw_return_data['status'] = 1;
	$fw_return_data['channel_id'] = $_POST['channel_id'];
}

/*
if((isset($_POST['delete_channel']) && $_POST['delete_channel'] == 1))
{
	$v_param = array('ID'=>$_POST['channel_id']);
	$s_response = APIconnectorUser('channel_delete', $_COOKIE['username'], $_COOKIE['sessionID'], $v_param);
	$v_response = json_decode($s_response, true);
	if($v_response['status'] == 1)
	{
	} else {
		header("Location: ".$_POST['fw_domain_url']."&folderfile=edit_channel&channel_id=".$_POST['channel_id']."&error=".urlencode("Error occured deleting channel"));
		exit;
	}
}
*/
