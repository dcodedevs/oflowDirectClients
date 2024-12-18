<?php

$sql = "SELECT * FROM accountinfo";
$o_query = $o_main->db->query($sql);
$accountinfo_m = $o_query ? $o_query->row_array() : array();

$sql = "SELECT * FROM accountinfo_basisconfig";
$o_query = $o_main->db->query($sql);
$accountinfo_m_basisconfig = $o_query ? $o_query->row_array() : array();
if($accountinfo_m['deactivate_useradministration'] > 0) {
	$accountinfo_m_basisconfig['deactivate_useradministration'] = $accountinfo_m['deactivate_useradministration'] - 1;
}
if(!$accountinfo_m_basisconfig['deactivate_useradministration'] || $variables->developeraccess >= 10) {
	?>
	<div class="module_customized"><?php
	$o_query = $o_main->db->query('SELECT * FROM accountinfo');
	$v_accountinfo = $o_query->row_array();

	$includeFile = __DIR__."/../../../languages/default.php";
	if(is_file($includeFile)) include($includeFile);
	$includeFile = __DIR__."/../../../languages/".$variables->languageID.".php";
	if(is_file($includeFile)) include($includeFile);
	$includeFile = __DIR__."/output_javascript.php";
	if(is_file($includeFile)) include($includeFile);

	if(isset($_GET['error']) )
	{
		$class = "error";
		$print = addslashes('<div class="item ui-corner-all '.$class.'">'.$_GET['error'].'</div>');
		if(isset($ob_javascript))
		{
			$ob_javascript .= ' $(function(){$(".fw_info_messages").html("'.$print.'").slideDown();});';
		} else {
			?><script type="text/javascript" language="javascript"><?php echo '$(function(){$(".fw_info_messages").html("'.$print.'").slideDown();});';?></script><?php
		}
		unset($_GET['error']);
	}
	?>
	<div class="panel panel-default">
		<div class="panel-heading">
	  		<h3 class="panel-title">
				<?php echo $formText_usersBigTitle_usersOutput;?>
				&nbsp;<a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?'&caID='.$_GET['caID']:'')."&module=".$_GET['module']."&folder=output&folderfile=outputedit&modulename=users&getynetaccount=1";?>"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span><?php /*=$formText_addUser_usersOutputLink;*/?></a>
			</h3>
		</div>
		<div class="panel-body">
			<table class="table table-striped table-hover table-condensed">
				<thead>
					<tr>
						<th width="160"><?php echo $formText_listheaderEmail_usersOutputList; ?></th>
						<th width="120"><?php echo $formText_listheaderName_usersOutputList; ?></th>
						<th width="65"><?php echo $formText_listheaderAccesslevel_usersOutputList; ?></th>
						<th width="45"><?php echo $formText_listheaderAdmin_usersOutputList; ?></th>
						<th width="60"><?php echo $formText_SystemAdmin_usersOutputLink; ?></th>
						<th width="70"><?php echo $formText_listheaderInvitation_usersOutputList; ?></th>
						<th width="35"><?php echo $formText_listheaderReg_usersOutputList; ?></th>
						<th width="45"><?php echo $formText_listheaderInactive_usersOutputList; ?></th>
						<th width="30"><?php echo $formText_listheaderDelete_usersOutputList; ?></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$response = json_decode(APIconnectorUser("companyaccessbycompanyidget", $variables->loggID, $variables->sessionID, array('COMPANY_ID'=>$companyID)));
				foreach($response->data as $writeContent)
				{
					?><tr>
						<td>
							<a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?'&caID='.$_GET['caID']:'')."&module=".$_GET['module']."&modulename=".$modulename."&folder=output&folderfile=outputedit&username=".$writeContent->username."&accessID=".$writeContent->id."&getynetaccount=1";?>"<?php echo ($writeContent->deactivated==1?' style="color:#BBBBBB;"':'');?>><?php echo $writeContent->username;?></a>
						</td>
						<td>
							<a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?'&caID='.$_GET['caID']:'')."&module=".$_GET['module']."&modulename=".$modulename."&folder=output&folderfile=outputedit&username=".$writeContent->username."&accessID=".$writeContent->id."&getynetaccount=1";?>"<?php echo ($writeContent->deactivated == 1?' style="color:#BBBBBB;':'');?>><?php echo ($writeContent->users_name != '' ? $writeContent->users_name : $writeContent->fullname);?></a>
						</td>
						<td<?php echo ($writeContent->deactivated==1?' style="color:#BBBBBB;"':'');?>><?php
							if($writeContent->accesslevel == 1)
							{
								print $formText_listvalueAll_usersOutputList;
							}
							elseif($writeContent->accesslevel == 2) {
								print $formText_listvalueRestricted_usersOutputList;
							}
							elseif($writeContent->accesslevel == 0) {
								print $formText_listvalueNoAccess_usersOutputList;
							} else {
								print $formText_listvalueGroup_usersOutputList. " - ".$writeContent->groupname;
							}
						?></td>
						<td<?php echo ($writeContent->deactivated==1?' style="color:#BBBBBB;"':'');?>><?php echo ($writeContent->admin=='1'?" X ":"");?></td>
						<td<?php echo ($writeContent->deactivated==1?' style="color:#BBBBBB;"':'');?>><?php echo ($writeContent->system_admin=='1'?" X ":"");?></td>
						<td<?php echo ($writeContent->deactivated==1?' style="color:#BBBBBB;"':'');?>><?php
							if($writeContent->invitationsent != '')
							{
								if(stristr($writeContent->invitationsent,","))
								{
									print substr($writeContent->invitationsent,strrpos($writeContent->invitationsent,",")+1). " (".$writeContent->invitationsentnr.")";
								} else {
									print $writeContent->invitationsent;
								}
							}
						?></td>
						<td<?php echo ($writeContent->deactivated==1?' style="color:#BBBBBB;"':'');?>><?php echo ($writeContent->registeredID != ''?" X ":"");?></td>
						<td<?php echo ($writeContent->deactivated==1?' style="color:#BBBBBB;"':'');?>><?php echo ($writeContent->deactivated == 1?" X ":"");?></td>
						<td>
							<form id="fw_useradmin_deleteuser_<?php echo $writeContent->id;?>" name="update" action="<?php echo $extradir;?>/output/outputreg.php" method="POST">
							<input type="hidden" name="pageID" value="<?php echo $_GET['pageID'];?>">
							<input type="hidden" name="accountname" value="<?php echo $_GET['accountname'];?>">
							<input type="hidden" name="companyID" value="<?php echo $_GET['companyID'];?>">
							<input type="hidden" name="caID" value="<?php echo (!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']);?>">
							<input type="hidden" name="userID" value="<?php echo $writeContent->id; ?>">
							<input type="hidden" name="deletetest" value="" id="fw_useradmin_deleteusermark_<?php echo $writeContent->id; ?>">
							<input type="hidden" name="username" value="<?php echo $writeContent->username; ?>">
							<input type="hidden" name="extradir" value="<?php echo $extradir; ?>">
							<input type="hidden" name="module" value="<?php echo $module; ?>">
							<input type="hidden" name="updateuser" value="1">
							<input type="hidden" name="returnurl" value="<?php echo (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');?>">
							<input type="image" onClick="fw_useradmin_deleteuserconfirmlink('<?php echo $writeContent->id;?>','<?php echo $writeContent->username;?>'); return false;" src="<?php echo $extradir;?>/output/elementsOutput/delete_icon.gif" width="20" alt="" border="0">
							</form>
						</td>
					</tr>
					<?php
				}
				?>
				</tbody>
			</table>
		</div>
	</div>

	<div class="panel panel-default">
		<div class="panel-heading">
	  		<h3 class="panel-title">
				<?php echo $formText_groupsBigTitle_usersOutput;?>
				&nbsp;<a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?'&caID='.$_GET['caID']:'')."&module=".$_GET['module']."&getynetaccount=1&folder=output&folderfile=outputeditgroup&modulename=users&usermodule=1";?>"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span><?php /*=$formText_addGroup_usersOutputLink;*/?></a>
			</h3>
		</div>
		<div class="panel-body">
			<table class="table table-striped table-hover table-condensed">
				<thead>
					<tr>
						<th width="400"><?php echo $formText_listheaderName_usersOutputList;?></th>
						<th width="235"><?php echo $formText_listheaderAccesslevel_usersOutputList;?></th>
						<th width="30"><?php echo $formText_listheaderDelete_usersOutputList;?></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$response = json_decode(APIconnectorUser("groupcompanyaccessbymoduleidget", $variables->loggID, $variables->sessionID, array('COMPANY_ID'=>$companyID, 'MODULE_ID'=>'0')));
				foreach($response->data as $writeContent)
				{
					?><tr>
						<td width="400">
							<a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?'&caID='.$_GET['caID']:'')."&module=".$_GET['module']."&modulename=".$modulename."&folder=output&folderfile=outputeditgroup&groupID=".$writeContent->id."&getynetaccount=1";?>"<?php echo ((isset($writeContent->deactivated) && $writeContent->deactivated==1)?' style="color:#BBBBBB;"':'');?>><?php echo $writeContent->groupname;?></a>
						</td>
						<td width="235"<?php echo ((isset($writeContent->deactivated) && $writeContent->deactivated==1)?' style="color:#BBBBBB;"':'');?>><?php
						if($writeContent->accesslevel == 1)
						{
							print $formText_listvalueAll_usersOutputList;
						}
						elseif($writeContent->accesslevel == 2)
						{
							print $formText_listvalueRestricted_usersOutputList;
						}
						elseif($writeContent->accesslevel == 0)
						{
							print $formText_listvalueNoAccess_usersOutputList;
						}
						?></td>
						<td width="30"><?php
						if($writeContent->type < 1)
						{
							?><form name="update" action="<?php echo $extradir;?>/output/outputreg.php" method="post" id="fw_useradmin_deletegroup_<?php echo $writeContent->id; ?>">
							<input type="hidden" name="pageID" value="<?php echo $_GET['pageID'];?>">
							<input type="hidden" name="accountname" value="<?php echo $_GET['accountname'];?>">
							<input type="hidden" name="companyID" value="<?php echo $_GET['companyID'];?>">
							<input type="hidden" name="caID" value="<?php echo (!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']);?>">
							<input type="hidden" name="groupID" value="<?php echo $writeContent->id; ?>" />
							<input type="hidden" name="deletetestgroup" value="" id="fw_useradmin_deletegroupmark_<?php echo $writeContent->id;?>"/>
							<input type="hidden" name="groupname" value="<?php echo $writeContent->groupname; ?>" />
							<input type="hidden" name="extradir" value="<?php echo $extradir; ?>" />
							<input type="hidden" name="module" value="<?php echo $module; ?>" />
							<input type="hidden" name="updateuser" value="1" />
							<input type="hidden" name="returnurl" value="<?php echo (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''); ?>" />
							<input type="image" onClick="fw_useradmin_deletegroupconfirm('<?php echo $writeContent->id; ?>','<?php echo $writeContent->groupname; ?>'); return false;" src="<?php echo $extradir;?>/output/elementsOutput/delete_icon.gif" width="20" alt="" border="0" />
							</form><?php
						}
						?></td>
					</tr><?php
				}
				?>
				</tbody>
			</table>
		</div>
	</div>
	<?php
	$response = json_decode(APIconnectorAccount("membersystemcompanyaccesslistallget", $accountname, $v_accountinfo['password'],array("COMPANY_ID"=>$companyID,"MEMBERSYSTEMID"=>"")),true);
	if(isset($response['data']) && count($response['data']) >0)
	{
		?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><?php echo $formText_custAccessListBigTitle_usersOutput;?></h3>
			</div>
			<div class="panel-body">
				<table class="table table-striped table-hover table-condensed">
					<thead>
						<tr>
							<th width="190"><?php echo $formText_listheaderEmail_usersOutputList;?></th>
							<th width="225"><?php echo $formText_listheaderName_usersOutputList;?></th>
							<th width="100"><?php echo $formText_listheaderModule_usersOutputList;?></th>
							<th width="150"><?php echo $formText_listheaderLastLogin_usersOutputList;?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach($response['data'] as $writeContent)
						{
							?><tr>
								<td><?php echo $writeContent['username'];?></td>
								<td><?php echo $writeContent['fullname'];?></td>
								<td><?php echo ucfirst($writeContent['membersystemmodule']); ?></td>
								<td><?php echo $writeContent['lastlogin']; ?></td>
							</tr><?php
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	}
	?>
	</div>
<?php } ?>
