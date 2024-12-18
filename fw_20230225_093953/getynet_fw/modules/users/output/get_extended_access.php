<?php
ob_start();
if(!function_exists('APIconnectorUser')) require_once(__DIR__."/../../../includes/APIconnector.php");
if(!isset($o_main))
{
	define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
	require_once(BASEPATH.'elementsGlobal/cMain.php');
	$o_query = $o_main->db->query("SELECT * FROM accountinfo");
	$v_accountinfo = $o_query ? $o_query->row_array() : array();
}
$s_buffer = '';

$i = 1;
if(!isset($apiAccounts))
{
	$data = json_decode(APIconnectorUser("accountbycompanyidgetlist", $_COOKIE['username'], $_COOKIE['sessionID'], array('COMPANY_ID'=>$_GET['companyID'])), TRUE);
	$apiAccounts = $data['data'];
}
foreach($apiAccounts as $apiAccount)
{
	$b_show_extended = FALSE;
	ob_start();
	?>
	<tr>
	<td style="padding-left:30px; width:120px;"><?php echo ($apiAccount['friendlyaccountname'] != '' ? $apiAccount['friendlyaccountname'] : $apiAccount['accountname']);?></td>
	<td width="400" align="left"></td>
	</tr>
	<tr>
		<td colspan="2" style="border-bottom:5px solid #FFFFFF;">
			<div>
				<?php
				$b_show_owner_access_restrict = $b_content_access = false;
				$modulelist = $modulename = $modulemode = $moduleID = $v_show_owner_access_restrict = $v_content_access = array();
				$s_response = APIconnectorAccount("account_module_list_get", $v_accountinfo['accountname'], $v_accountinfo['password'], array('ACC_NAME'=>$apiAccount['accountname']));
				$v_response = json_decode($s_response, true);
				if(count($v_response['modules'])>0)
				{
					?>
					<table border="0" cellpadding="0" cellspacing="0" rules="none" frame="void" style="background-color:#EEEEEE; width:100%">
					<tr>
						<td style="border-bottom:1px solid #000000; width:200px; padding-left:50px;"><?php echo $formText_modulenamelistaccess_usersOutputLink;?></td>
						<td style="border-bottom:1px solid #000000;"><?php echo $formText_moduleaccessheader_usersOutputLink;?></td>
					</tr>
					<?php
					foreach($v_response['modules'] as $v_module)
					{
						if(!isset($v_module['extended_content_access'])) continue;
						$b_show_extended = TRUE;
						?>
						<tr>
						<td style="padding-left:50px; width:200px;"><?php echo $v_module['name']; ?></td>
						<td>
							<div class="content_access"><?php
							foreach($v_module['extended_content_access'] as $v_item)
							{
								if(base64_decode($v_item[1], TRUE) !== FALSE)
								{
									$v_item[1] = base64_decode($v_item[1]);
								}
								if($v_item[2] == 1)
								{
									?><input type="hidden" name="extended_<?php echo $v_item[0]."_".$v_module['id']."_".$apiAccount['id'];?>[]" value="<?php echo $v_item[0];?>"><input type="checkbox" style="width:auto;" checked disabled id="extended_<?php echo $v_item[0]."_".$v_module['id']."_".$apiAccount['id'];?>"> <label for="extended_<?php echo $v_item[0]."_".$v_module['id']."_".$apiAccount['id'];?>" style="margin-right:10px;"><?php echo $v_item[1];?></label><?php
								} else {
									?><input type="checkbox" name="extended_<?php echo $v_item[0]."_".$v_module['id']."_".$apiAccount['id'];?>[]" value="<?php echo $v_item[0];?>" style="width:auto;" <?php if($v_extended_access[$apiAccount['id']][$v_module['id']][$v_item[0]]['accesslevel'] == 1){ ?> checked="checked"<?php } ?> id="extended_<?php echo $v_item[0]."_".$v_module['id']."_".$apiAccount['id'];?>"> <label for="extended_<?php echo $v_item[0]."_".$v_module['id']."_".$apiAccount['id'];?>" style="margin-right:10px;"><?php echo $v_item[1];?></label><?php
								}
							}
							?></div>
						</td>
						</tr><?php
					}
					?></table><?php
				} else {
					print '<p class="bg-danger" style="line-height:30px; text-align:center;">'.$formText_ErrorOccuredRetrievingModuleList_usersOutputLink.'</p>';
				}
				?>
			</div>
		</td>
	</tr>
	<?php
	$i++;
	if(!$b_show_extended) ob_end_clean(); else $s_buffer .= ob_get_clean();
}
?>
<table border="0" cellpadding="0" cellspacing="0" rules="none" frame="void" style="background-color:#EEEEEE; width:100%"><?php echo $s_buffer;?></table>
<?php
if(!isset($v_extended_access))
{
	$v_return = array();
	$v_return['html'] = ob_get_clean();
	echo json_encode($v_return);
} else {
	echo ob_get_clean();
}