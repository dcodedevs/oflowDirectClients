<?php
if(!function_exists("APIconnectorAccount")) include_once(__DIR__."/../../../../fw/account_fw/includes/APIconnector.php");
if($_POST['output_form_submit']){
	foreach($_POST['serialized_array'] as $v_item)
	{
		if(substr($v_item['name'], -2, 2) == '[]')
		{
			$s_key = substr($v_item['name'], 0, -2);
			if(!isset($_POST[$s_key])) $_POST[$s_key] = array();
			$_POST[$s_key][] = $v_item['value'];
		} else {
			$_POST[$v_item['name']] = $v_item['value'];
		}
	}
}

if(isset($_POST['cid'])){ $cid = $_POST['cid']; }
if($cid) {
	$data = json_decode(APIconnectorUser("group_get", $variables->loggID, $variables->sessionID, array('id'=>$cid)),true);
	if($data['status'] == 1){
		$group_getynet_data = $data['item'];
	}
}
if(!$group_getynet_data) {
	$fw_error_msg = array($formText_GroupNotFound_output);
	return;
}
$o_query = $o_main->db->query("SELECT * FROM accountinfo");
$v_accountinfo = $o_query ? $o_query->row_array() : array();

$v_channel = array();
if(isset($_POST['channel_id']))
{
	//$v_param = array('module_id'=>'', 'content_id'=>'');
	//$s_response = APIconnectorAccount('group_get_list', $v_accountinfo['accountname'], $v_accountinfo['password'], $v_param);

	// $s_response = APIconnectorUser("group_get_list", $variables->loggID, $variables->sessionID, array('company_id'=>$_GET['companyID']));
	// $v_response = json_decode($s_response, TRUE);
	// if(isset($v_response['status']) && $v_response['status'] == 1)
	// {
	// 	if(count($v_response['items']) > 0)
	// 	{
	// 		$v_groups = $v_response['items'];
	// 	}
	// }

	if(isset($_POST['channel_id']) && $_POST['channel_id'] > 0)
	{
		$v_param = array('CHANNEL_ID'=>$_POST['channel_id']);
		$s_response = APIconnectorAccount('channel_get', $v_accountinfo['accountname'], $v_accountinfo['password'], $v_param);
		$v_response = json_decode($s_response, TRUE);
		if(isset($v_response['status']) && $v_response['status'] == 1)
		{
			$v_access = $v_response['access'];
			$v_channel = $v_response['channel'];
		}
	} else {
		$v_access = array();
		$v_channel = array('type' => 0);
	}
}

if(isset($_POST['output_form_submit']))
{
	$v_return = array();
	$b_failure = FALSE;
	$b_add = FALSE;


	$_POST['channel_status'] = intval($_POST['channel_status']);
	if(isset($_POST['channel_id']) && intval($_POST['channel_id'])>0)
	{
		$v_param = array('ID'=>$_POST['channel_id'], 'NAME'=>$_POST['name'], 'TYPE'=>$_POST['channel_type'], 'STATUS'=>$_POST['channel_status']);
	} else {
		$b_add = TRUE;
		$v_param = array('NAME'=>$_POST['name'], 'TYPE'=>$_POST['channel_type'], 'STATUS'=>1);
	}
	$s_response = APIconnectorAccount('channel_set', $v_accountinfo['accountname'], $v_accountinfo['password'], $v_param);
	$v_response = json_decode($s_response, TRUE);
	if(isset($v_response['status']) && $v_response['status'] == 1)
	{
		if($b_add)
		{
			$_POST['channel_id'] = $v_response['channel_id'];
		}

		$v_param = array('CHANNEL_ID'=>$_POST['channel_id'], 'ACCESS'=>array());
		//changed to single group access to 1 channel
		// foreach($_POST['access_id'] as $l_key => $l_access_id)
		// {
		// 	$v_access = array
		// 	(
		// 		'ID'=>$l_access_id,
		// 		'TYPE'=>3,
		// 		'GROUP_ID'=>$_POST['access_group_id'][$l_key],
		// 		'ACCESS_LEVEL'=>$_POST['access_level'][$l_key]
		// 	);
		// 	$v_param['ACCESS'][] = $v_access;
		// }
		if(count($v_access) > 0){
			$l_access_id = $v_access[0]['id'];
		}
		$v_access = array
		(
			'ID'=>$l_access_id,
			'TYPE'=>3,
			'GROUP_ID'=>$group_getynet_data['id'],
			'ACCESS_LEVEL'=>1
		);
		$v_param['ACCESS'][] = $v_access;
		$s_response = APIconnectorAccount('channel_access_set', $v_accountinfo['accountname'], $v_accountinfo['password'], $v_param);
		$v_response = json_decode($s_response, TRUE);

		if(isset($v_response['status']) && $v_response['status'] == 1)
		{} else {
			$b_failure = TRUE;
		}
	} else {
		$b_failure = TRUE;
	}

	if($b_failure)
	{
		$v_return['error'] = array($formText_ErrorOccurredSavingChannel_Output);
	}

	echo json_encode($v_return);
	return;
}

?>
<div class="profileEditForm popupform">
    <div id="popup-validate-message2"></div>
	<div class="loading"></div>
    <form class="output-form2" action="<?php echo /*parseLink*/('modules/CompanyProfile/output/ajax.edit_channels.php');?>" method="post">
	<input type="hidden" name="output_form_submit" value="1">
	<input type="hidden" name="cid" value="<?php echo $cid?>">
	<input type="hidden" name="channel_id" value="<?php echo $v_channel['id']?>">
	<?php if(isset($_POST['channel_id'])) { ?>
		<div class="popupformTitle"><?php echo ($_POST['channel_id']>0?$formText_EditChannel_Output:$formText_AddChannel_Output);?></div>

		<div class="line">
			<div class="lineTitle"><?php echo $formText_Name_Output; ?></div>
			<div class="lineInput">
				<input type="hidden" name="channel_type" value="<?php echo $v_channel['type'];?>">
				<input class="popupforminput botspace" name="name" type="text" value="<?php if($v_channel['name'] != ""){ echo $v_channel['name']; } else { echo $group_getynet_data['name']; }?>" required>
			</div>
			<div class="clear"></div>
		</div>
		<?php if($_POST['channel_id']>0) {?>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Status_Output; ?></div>
				<div class="lineInput">
					<select class="form-control input-sm size-30 popupforminput botspace" name="channel_status"><?php
					$v_channel_statuses = array(1=>$formText_Active_Output, 3=>$formText_InactiveStillVisibleInInactiveListWithoutWritingOption_Output, 0=>$formText_DeletedNotVisibleToAnyUser_Output);
					foreach($v_channel_statuses as $key => $item)
					{
						?><option value="<?php echo $key;?>"<?php echo ($v_channel['status']==$key?' selected':'');?>><?php echo $item;?></option><?php
					}
					?></select>
				</div>
				<div class="clear"></div>
			</div>
		<?php } ?>
		<?php /*
		<div class="line">
			<div class="lineTitle">
				<?php echo $formText_ChannelAccess_Output; ?> &nbsp;<a class="output-channel-access-add"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></a>
				<div class="info">
					<?php echo $formText_ChannelAccessInfo_output;?>
				</div>
			</div>
			<div class="lineInput">
				<table class="table" width="100%" style="text-align: left;">
				<thead>
					<tr>
						<th width="40%"><?php echo $formText_Group_Output;?></th>
						<th width="40%"><?php echo $formText_AccessLevel_Output;?></th>
						<th width="20%" style="text-align:center;"><?php echo $formText_Delete_Output;?></th>
					</tr>
				</thead>
				<tbody class="output-channel-access-container"><?php
				$v_access_level = array(1=>$formText_ReadAndWrite_Output, 2=>$formText_ReadOnly_Output);
				if(count($v_access)>0)
				foreach($v_access as $v_item)
				{
					output_print_channel_access($v_item, $v_groups, $v_access_level);
				}
				?>
				</tbody>
				</table>
				<input type="hidden" class="output-channel-access-origin" value="<?php
				ob_start();
				output_print_channel_access(array(), $v_groups, $v_access_level);
				echo htmlentities(ob_get_clean());
				?>">
			</div>
			<div class="clear"></div>
		</div> */
		?>

		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output;?>">
		</div>
	<?php } else { ?>

	<?php } ?>
	</form>
</div>
<style>
/* .table th,
.table td {
	padding: 5px 0px;
}
.table .glyphicon-trash {
	color: #0284C9;
	cursor: pointer;
}
.glyphicon.glyphicon-plus {
	color: #0284C9;
	cursor: pointer;
} */
</style>
<script type="text/javascript">
function objectifyForm(formArray){
	//serialize data function
	var returnArray = {};
	for(var i = 0; i < formArray.length; i++){
		returnArray[formArray[i]['name']] = formArray[i]['value'];
	}
	return returnArray;
}
$(document).off('submit', 'form.output-form2').on('submit', 'form.output-form2', function(e){
	e.preventDefault();
	var data = { output_form_submit: 1, serialized_array: $(this).serializeArray() };

	ajaxCall({module_file:'edit_channels', module_name: 'People', module_folder: 'output_groups'}, data, function(json){
		if(json.error !== undefined)
		{
			var errorMessage = "";
			$.each(json.error, function(index, value){
				errorMessage += value+"<br/>";
			});
			$("#popup-validate-message2").html(errorMessage, true).show();
		} else {
			out_popup2.addClass("close-reload");
			out_popup2.close();
		}
	});
});
</script>
<?php


function output_print_channel_access($v_item, $v_groups, $v_access_level)
{
	?><tr class="channel-access">
		<td>
			<input type="hidden" name="access_id[]" value="<?php echo $v_item['id'];?>">
			<select name="access_group_id[]"><?php
			foreach($v_groups as $v_group)
			{
				?><option value="<?php echo $v_group['id'];?>"<?php echo ($v_item['group_id']==$v_group['id']?' selected':'');?>><?php echo $v_group['name'];?></option><?php
			}
			?></select>
		</td>
		<td>
			<select class="form-control input-sm" name="access_level[]"><?php
			foreach($v_access_level as $l_key=>$s_access_level)
			{
				?><option value="<?php echo $l_key;?>"<?php echo ($v_item['access_level']==$l_key?' selected':'');?>><?php echo $s_access_level;?></option><?php
			}
			?></select>
		</td>
		<td style="text-align:center;">
			<a onClick="$(this).closest('.channel-access').remove();"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></a>
		</td>
	</tr><?php
}
