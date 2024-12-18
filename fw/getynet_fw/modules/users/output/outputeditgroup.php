<div class="module_customized"><?php
$includeFile = __DIR__."/../../../languages/default.php";
if(is_file($includeFile)) include($includeFile);
$includeFile = __DIR__."/../../../languages/".$variables->languageID.".php";
if(is_file($includeFile)) include($includeFile);
//$include_file = __DIR__."/../../../includes/include.developeraccess.php";
//if(is_file($include_file)) include($include_file);
$includeFile = __DIR__."/output_javascript.php";
if(is_file($includeFile)) include($includeFile);

include_once(__DIR__.'/getAccessElementList.php');

if(!function_exists("APIconnectorAccount")) include(__DIR__."/../../../includes/APIconnector.php");
$o_query = $o_main->db->query('SELECT * FROM accountinfo');
$v_accountinfo = $o_query->row_array();

if(isset($_GET['error']))
{
	$class = "error";
	$print = addslashes('<div class="item ui-corner-all '.$class.'">'.urldecode($_GET['error']).'</div>');
	if(isset($ob_javascript))
	{
		$ob_javascript .= ' $(function(){$(".fw_info_messages").html("'.$print.'").slideDown();});';
	} else {
		?><script type="text/javascript" language="javascript"><?php echo '$(function(){$(".fw_info_messages").html("'.$print.'").slideDown();});';?></script><?php
	}
	unset($_GET['error']);
}

$data = json_decode(APIconnectorUser("groupcompanyaccessget", $_COOKIE['username'], $_COOKIE['sessionID'], array('COMPANY_ID'=>$companyID, 'GROUP_ID'=>$_GET['groupID'])),true);
$groupAccess = $data['data'];
$noedit = false;
if(isset($groupAccess['id']))
{
	$edit = 2;
	if($groupAccess['type']==2) $noedit = true;
} else {
	$edit = 1;
}

$o_query = $o_main->db->get('accountinfo');
$v_accountinfo = $o_query ? $o_query->row_array() : array();

$o_query = $o_main->db->query("SELECT * FROM account_accountconfig WHERE 1");
$v_accountconfig = $o_query ? $o_query->row_array() : array();

$group_rates = array();
$v_param = array(
	'ACCOUNT_ID' => $v_accountinfo['getynet_account_id'],
	'TYPE' => 'basic'
);
$v_response = json_decode(APIconnectorUser("license_rate_group_get", $variables->loggID, $variables->sessionID, $v_param), true);

$group_rates = $v_response['items'];


?>
<div class="panel panel-default">
	<div class="panel-heading">
  		<h3 class="panel-title"><?php echo (isset($_GET['groupID']) ? $formText_editGroup_usersOutputLink : $formText_addGroup_usersOutputLink);?></h3>
	</div>
	<div class="panel-body">
		<form name="upadate" action="<?php echo $extradir;?>/output/outputreg.php" method="post" id="userupdateformid">
		<input type="hidden" name="pageID" value="<?php echo $_GET['pageID'];?>">
		<input type="hidden" name="accountname" value="<?php echo $_GET['accountname'];?>">
		<input type="hidden" name="companyID" value="<?php echo $_GET['companyID'];?>">
		<input type="hidden" name="caID" value="<?php echo $_GET['caID'];?>">
		<input type="hidden" name="groupID" value="<?php echo $groupAccess['id']; ?>" />
		<input type="hidden" name="username" value="<?php echo $groupAccess['username']; ?>" />
		<input type="hidden" name="editedBy" value="<?php echo $variables->loggID; ?>" />
		<input type="hidden" name="languageID" value="<?php echo $variables->languageID; ?>" />
		<input type="hidden" name="defaultLanguageID" value="<?php echo $variables->defaultLanguageID; ?>" />
		<input type="hidden" name="extradir" value="<?php echo $extradir;?>" />
		<input type="hidden" name="module" value="<?php echo $module;?>" />
		<input type="hidden" name="editgroup" value="<?php echo $edit;?>" />
		<input type="hidden" name="formsendtype" value="0" id="formsendtypeid" />
		<?php if(!$hideExtended) {?>
			<input type="hidden" name="fw_domain_url" value="<?php echo $fw_domain_url."&module=".$_GET['module']."&folder=output&modulename=users&getynetaccount=1&folderfile=outputeditgroup";?>">
		<?php } else {?>
			<input type="hidden" name="fw_domain_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?'&caID='.$_GET['caID']:'')."&module=".$_GET['module']."&modulename=".$modulename."&folder=output&folderfile=output&inc_obj=access_group_list&groupID=".$groupAccess['id'];?>">
		<?php } ?>
		<table border="0" cellpadding="0" cellspacing="0" rules="none" frame="void" width="100%">
			<tr>
				<td width="120"><?php echo $formText_groupName_usersOutputLink;?></td>
				<td><?php
				if($edit == 2 && $groupAccess['type'] > 0)
				{
					?><input type="hidden" name="groupname" value="<?php echo $groupAccess['groupname'];?>" /><?php
					echo $groupAccess['groupname'];
				} else {
					?><input type="text" name="groupname" value="<?php echo $groupAccess['groupname'];?>"<?php echo ($noedit?' disabled':'');?>><?php
				}
				?></td>
			</tr>
			<tr>
				<td width="120"><?php echo $formText_LicenseRateGroup_usersOutputLink;?></td>
				<td>
					<select name="license_rate_group_id" <?php echo ($noedit?' disabled':'');?>>
						<option value=""><?php echo $formText_Select_usersOutputLink;?></option>
						<?php
						foreach($group_rates as $group_rate) {
							?>
							<option value="<?php echo $group_rate['id'];?>" <?php if($group_rate['id'] == $groupAccess['license_rate_group_id']) echo 'selected';?>><?php echo $group_rate['name'];?></option>
							<?php
						}
						?>
					</select>
				</td>
			</tr>

			<!-- REGULAR ACCESS -->
			<tr>
				<td><?php echo $formText_userlistAccesslevel_usersOutputLink;?></td>
				<td align="left"><select name="companyaccess" id="companyaccessID" onChange="javascript:fw_useradmin_updateaccesslevel('<?php echo $companyID;?>','<?php echo $extradir;?>','<?php echo $module;?>','<?php echo $variables->languageID;?>','<?php echo $module;?>','<?php echo $formText_modulenamelistaccess_usersOutputLink;?>','<?php echo $formText_moduleaccessheader_usersOutputLink;?>','<?php echo $formText_moduleLevelAll_usersOutputLink;?>','<?php echo $formText_moduleLevelRestricted_usersOutputLink;?>','<?php echo $formText_moduleLevelDenied_usersOutputLink;?>');">
				<option value="1"<?php echo ($groupAccess['accesslevel']==1?' selected="selected"':'');?>><?php echo $formText_accessLevelAll_usersOutputLink;?></option>
				<option value="2"<?php echo ($groupAccess['accesslevel']==2?' selected="selected"':'');?>><?php echo $formText_accessLevelRestricted_usersOutputLink;?></option>
				<option value="0"<?php echo ($groupAccess['accesslevel']==0?' selected="selected"':'');?>><?php echo $formText_accessLevelNoAccess_usersOutputLink;?></option>
				</select></td>
			</tr>
			<tr>
				<td colspan="2" style="padding-bottom:5px;">
				<div id="accountaccesslistid" style="width:100%;">
				<?php


				if($groupAccess['id'] != '' && $groupAccess['accesslevel'] == 2)
				{
					$groupEdit = true;
					include("accountaccesslist.php");
				}
				?></div>
				</td>
			</tr>


			<!-- EXTENED ACCESS -->
			<?php
			if(!$hideExtended){
				/*
				$b_extended = FALSE;
				$v_extended_access = array();
				$s_response = APIconnectorUser("contentaccess_extended_get", $_COOKIE['username'], $_COOKIE['sessionID'], array('company_id'=>$_GET['companyID'], 'group_id'=>$_GET['groupID']));
				$v_response = json_decode($s_response, TRUE);
				if($v_response['status'] == 1)
				{
					$b_extended = TRUE;
					$v_extended_access = $v_response['access'];
				}
				?>
				<tr><td><?php echo $formText_ExtendedAccess;?></td><td align="left"><select onChange="javascript:fw_useradmin_update_extended(this, '<?php echo $companyID;?>', '<?php echo $extradir;?>');">
				<option value="1"<?php echo ($b_extended?' selected="selected"':'');?>><?php echo $formText_accessLevelRestricted_usersOutputLink;?></option>
				<option value="0"<?php echo (!$b_extended?' selected="selected"':'');?>><?php echo $formText_accessLevelNoAccess_usersOutputLink;?></option>
				</select></td></tr>
				<tr><td colspan="2" style="padding-bottom:5px;">
				<div id="contentaccess_extended" style="width:100%;">
					<?php if($b_extended) include(__DIR__."/get_extended_access.php"); ?>
				</div>
				</td>
				</tr>
			<?php */ } ?>
			<tr><td colspan="2"><?php
			if(!$noedit)
			{
				?>
				<input type="submit" class="btn btn-sm btn-success script" value="<?php echo $formText_saveButton_usersOutputEdit;?>">
				<?php
				if(!$hideExtended){
					?>
					<a class="btn btn-sm btn-default optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?'&caID='.$_GET['caID']:'')."&module=".$_GET['module']."&getynetaccount=1&folder=output&folderfile=output&modulename=users";?>"><?php echo $formText_Cancel_Framework;?></a>
					<?php
				} else {
					?>
					<a class="btn btn-sm btn-default optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?'&caID='.$_GET['caID']:'')."&module=".$_GET['module']."&folder=output&folderfile=output&inc_obj=access_group_list";?>"><?php echo $formText_Cancel_Framework;?></a>
					<?php
				}
			} else {
				if(!$hideExtended){
					?><a class="btn btn-sm btn-default optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?'&caID='.$_GET['caID']:'')."&module=37&folderfile=output&folder=output&modulename=users&getynetaccount=1&updatepath=1";?>"><?php echo $formText_backButton_usersOutputEdit;?></a><?php
				}
			}
			?>
			</td></tr>
		</table>
		</form>
	</div>
</div>
<script type="text/javascript">
function fw_toggle_extended(_this){
	if($(_this).is(':checked')){
		$(_this).closest('tr').find('.access_element_column').show();
	} else {
		$(_this).closest('tr').find('.access_element_column').hide();
	}
}
</script>
