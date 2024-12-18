<?php
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');

if(is_file($includeFile)) include($includeFile);
$includeFile = __DIR__."/../../../languages/default.php";
if(is_file($includeFile)) include($includeFile);
$includeFile = __DIR__."/../../../languages/".$_GET['inputlang'].".php";
if(is_file($includeFile)) include($includeFile);
$return = $modulelist = $modulename = $modulemode = $moduleID = array();
include_once(__DIR__.'/getAccessElementList.php');

$o_query = $o_main->db->query('SELECT * FROM accountinfo');
$v_accountinfo = $o_query->row_array();

$b_simple_access = (0 < intval($v_accountinfo['getynet_app_id']));

if(!function_exists("APIconnectorAccount")) include(__DIR__."/../../../includes/APIconnector.php");
$s_response = APIconnectorAccount("account_module_list_get", $v_accountinfo['accountname'], $v_accountinfo['password'], array('ACC_NAME'=>$_GET['accountname']));
$v_response = json_decode($s_response, true);

$return['accountID'] = $_GET['accountID'];
if(count($v_response['modules']) == 0)
{
	$return['error'] = '<p class="bg-danger" style="line-height:30px; text-align:center;">'.$formText_ModulesCannotBeRetrieved_usersOutputLink.($v_response['error'] != "" ? ' ['.$v_response['error'].']' : '').'</p>';
} else {
	ob_start();
	$b_show_owner_access_restrict = $b_content_access = false;
	$v_show_owner_access_restrict = $v_content_access = $dashboard = array();
	foreach($v_response['modules'] as $v_module)
	{
		$moduleID[] = $v_module['id'];
		$modulename[] = $v_module['name'];
		$modulemode[] = $v_module['mode'];
		$modulelist[] = $v_module['module'];
		$dashboard[] = (isset($v_module['dashboard']) && $v_module['dashboard']);
		$v_show_owner_access_restrict[] = $v_module['show_owner_access_restrict'];
		$v_content_access[] = $v_module['content_access'];
		if($v_module['show_owner_access_restrict'] == 1) $b_show_owner_access_restrict = true;
		if(isset($v_module['content_access'])) $b_content_access = true;
	}
	$i = time();
	?>
	<table border="0" cellpadding="0" cellspacing="0" rules="none" frame="void" style="background-color:#EEEEEE; width:852px;" class="specified_access_table">
	<tr>
		<td style="border-bottom:1px solid #000000; width:210px; padding-left:40px;"><?php echo $_GET['listname']; ?></td>
		<td style="border-bottom:1px solid #000000;" colspan="3"><?php echo $_GET['listname2']; ?></td>
	</tr>
	<tr>
		<td style="padding-left:40px; width:100px;"><?php echo $formText_selectUnselectAll_usersOutputLink; ?></td>
		<td>
			<?php if(!$b_simple_access) { ?>
			<a href="#" onClick="javascript:readAll('frmwk_access_<?php print $i; ?>'); return false;" style="padding-right:30px;"><?php print $formText_modulenamelistreadaccess_usersOutputLink; ?></a>
			<a href="#" onClick="javascript:writeAll('frmwk_access_<?php print $i; ?>'); return false;" style="padding-right:30px;"><?php print $formText_modulenamelistwriteaccess_usersOutputLink; ?></a>
			<?php } ?>
			<a href="#" onClick="javascript:deleteAll('frmwk_access_<?php print $i; ?>'); return false;" style="padding-right:20px;"><?php print (!$b_simple_access ? $formText_modulenamelistdeleteaccess_usersOutputLink : $formText_AccessModule_usersOutputLink); ?></a>
		</td>
		<td style="background-color:rgba(144, 238, 144, 0.3); padding-left:10px;">
			<div class="access_element_column">
				<a href="#" onClick="javascript:accessElementAll('frmwk_access_accesselement_allow_<?php print $_GET['accountID']; ?>'); return false;" style="padding-right:0px;"><?php print $formText_ExpandAccess_usersOutputLink; ?></a>
			</div>
		</td>
		<td style="background-color:rgba(255, 87, 51, 0.3); padding-left:10px;">
			<div class="access_element_column">
				<a href="#" onClick="javascript:accessElementAll('frmwk_access_accesselement_restrict_<?php print $_GET['accountID']; ?>'); return false;" style="padding-right:0px;"><?php print $formText_RestrictAccess_usersOutputLink; ?></a>
			</div>
			<?php if(!isset($_GET['no_owner']) && $b_show_owner_access_restrict) { ?>
			<div class="access_element_column">
				<a href="#" onClick="javascript:ownerAll('frmwk_access_<?php print $i; ?>'); return false;"><?php print $formText_RestrictWriteAndDeleteToOwnerOnly_usersOutputLink; ?></a>
			</div>
			<?php } ?>
		</td>
	</tr>
	<?php
	foreach ($modulelist as $x => $name) 
	{
		?>
		<td style="padding-left:40px;"><?php echo $modulename[$x]; ?></td>
		<td>
			<?php if(!$b_simple_access) { ?>
			<input class="frmwk_access_<?php print $i; ?> 1" type="checkbox" name="account_<?php print $_GET['accountID']."_module_".$moduleID[$x]; ?>[]" value="1" style="width:auto;" checked="checked" id="account_<?php print $_GET['accountID']."_module_".$moduleID[$x]; ?>_ID1"/ onChange="changeModuleAccess(this);"> <label for="account_<?php print $_GET['accountID']."_module_".$moduleID[$x]; ?>_ID1" style="padding-right:10px;"><?php echo $formText_modulenamelistreadaccess_usersOutputLink; ?></label>
			<input class="frmwk_access_<?php print $i; ?> 2" type="checkbox" name="account_<?php print $_GET['accountID']."_module_".$moduleID[$x]; ?>[]" value="10" style="width:auto;" checked="checked" id="account_<?php print $_GET['accountID']."_module_".$moduleID[$x]; ?>_ID2"/ onChange="changeModuleAccess(this);"> <label for="account_<?php print $_GET['accountID']."_module_".$moduleID[$x]; ?>_ID2" style="padding-right:10px;"><?php echo $formText_modulenamelistwriteaccess_usersOutputLink; ?></label>
			<?php } ?>
			<input class="frmwk_access_<?php print $i; ?> 3" type="checkbox" name="account_<?php print $_GET['accountID']."_module_".$moduleID[$x]; ?>[]" value="100" style="width:auto;" checked="checked" id="account_<?php print $_GET['accountID']."_module_".$moduleID[$x]; ?>_ID3"/ onChange="changeModuleAccess(this);"> <label for="account_<?php print $_GET['accountID']."_module_".$moduleID[$x]; ?>_ID3" style="padding-right:10px;"><?php echo (!$b_simple_access ? $formText_modulenamelistdeleteaccess_usersOutputLink : $formText_AccessModule_usersOutputLink); ?></label>
			<?php if($dashboard[$x]) { ?>
			<div><input class="frmwk_dashboard" type="checkbox" name="accountdashboard_<?php print $apiAccount['id']."_".$moduleID[$x]; ?>[]" value="1" style="width:auto;" <?php if(1 == $apiModuleAccess['dashboard']){ ?> checked="checked"<?php } ?> id="accountdashboard_<?php print $apiAccount['id']."_".$moduleID[$x]; ?>"> <label for="accountdashboard_<?php print $apiAccount['id']."_".$moduleID[$x]; ?>" style="padding-right:10px;"><?php echo $formText_DashboardAccess_usersOutputLink; ?></label></div>
			<?php } ?>
		</td>
		<td style="background-color:rgba(144, 238, 144, 0.3); padding-left:10px;">
		<?php
			if($b_content_access && is_array($v_content_access[$x]))
			{
				/*
				?>
				<input type="checkbox" name="content_0_<?php echo $moduleID[$x]."_".$_GET['accountID'];?>[]" id="content_0_<?php echo $moduleID[$x]."_".$_GET['accountID'];?>" value="restrict" style="width:auto;" <?php if($apiModuleAccess['restricted_content'] == 1){ ?> checked="checked"<?php } ?> onChange="restricted_content_change(this);"> <label for="content_0_<?php echo $moduleID[$x]."_".$_GET['accountID'];?>"><?php echo $formText_RestrictContent_usersOutputLink;?></label>
				<div class="content_access<?php if($apiModuleAccess['restricted_content'] == 0) print ' hide';?>"><?php
				foreach($v_content_access[$x] as $v_item)
				{
					if(base64_decode($v_item[1], true) !== false)
					{
						$v_item[1] = base64_decode($v_item[1]);
					}
					if($v_item[2] == 1)
					{
						?><input type="hidden" name="content_<?php echo $v_item[0]."_".$moduleID[$x]."_".$_GET['accountID'];?>[]" value="<?php echo $v_item[0];?>"><input type="checkbox" style="width:auto;" checked disabled id="content_<?php echo $v_item[0]."_".$moduleID[$x]."_".$_GET['accountID'];?>"> <label for="content_<?php echo $v_item[0]."_".$moduleID[$x]."_".$_GET['accountID'];?>" style="margin-right:10px;"><?php echo $v_item[1];?></label><?php
					} else {
						?><input type="checkbox" name="content_<?php echo $v_item[0]."_".$moduleID[$x]."_".$_GET['accountID'];?>[]" value="<?php echo $v_item[0];?>" style="width:auto;" <?php if($apiModuleAccess['content_access'][$v_item[0]] == 1){ ?> checked="checked"<?php } ?> id="content_<?php echo $v_item[0]."_".$moduleID[$x]."_".$_GET['accountID'];?>"> <label for="content_<?php echo $v_item[0]."_".$moduleID[$x]."_".$_GET['accountID'];?>" style="margin-right:10px;"><?php echo $v_item[1];?></label><?php
					}
				}
				?></div><?php
				*/
			}
			$module_dir = __DIR__."/../../../../../modules/".$modulelist[$x];

			$dirs=array(realpath($module_dir));
			//define extension of the files
			$extensions=array('php');
			//directory exceptions
			$except_dirs=array(realpath($module_dir."/input"), realpath($module_dir."/properties"));
			foreach($addonFolders as $addonFolder) $except_dirs[] = realpath($module_dir."/".$addonFolder);
			//should check subdirs
			$check_subdirs=1;
			//gets files

			$output_folders=get_dirs($dirs, $except_dirs, 0);
			$getInputLang = $_GET['inputlang'];
			foreach($output_folders as $output_dir)
			{
				if(is_file($output_dir.'/'."languagesOutput/accesselements_empty.php")) include($output_dir.'/'."languagesOutput/accesselements_empty.php");
				if(is_file($output_dir.'/'."languagesOutput/accesselements_$getInputLang.php")) include($output_dir.'/'."languagesOutput/accesselements_$getInputLang.php");

			}
			$accessElements = getAccessElements($modulelist[$x], "allow");
			?>

			<div class="access_element_column">
			<?php
			if(count($accessElements) > 0){
				?>
					<?php
					foreach($accessElements as $accessElement) {
						?>
						<div>
							<input class="frmwk_access_accesselement_allow_<?php echo $_GET['accountID'];?>" type="checkbox" style="width:auto;" name="accesselement_<?php print $_GET['accountID']."_module_".$moduleID[$x]; ?>[]" value="<?php echo $accessElement['name'];?>"
							id="account_<?php print $_GET['accountID']."_module_".$moduleID[$x]; ?>_accesselement<?php echo $accessElement['name'];?>"
							<?php if(in_array($accessElement['name'], $apiModuleAccess['accesselements'])){ ?> checked="checked"<?php } ?>  />
							<label for="account_<?php print $_GET['accountID']."_module_".$moduleID[$x]; ?>_accesselement<?php echo $accessElement['name'];?>">
								<?php
								$accessElementName = ${"accessElementAllow_".$accessElement['name']."_name"};
								$accessElementDescription = ${"accessElementAllow_".$accessElement['name']."_description"};
								if($accessElementName != ""){
									echo $accessElementName;
								} else {
									echo $accessElement['name'];
								}
								if($accessElementDescription != ""){
									?>
									<span class="fas fa-info-circle accessElementInfoHoverWrapper"><div class="accessElementInfoHover"><?php echo $accessElementDescription;?></div></span>

									<?php
								}
								?>
							</label>
						</div>
						<?php
					}
					?>
				<?php
			}
			?>
			</div>
			<?php
			$restrictedAccessElements = getAccessElements($modulelist[$x], "restrict");
			?>
		</td>
		<td style="background-color:rgba(255, 87, 51, 0.3); padding-left:10px;">
			<div class="access_element_column">
			<?php
				if(count($restrictedAccessElements) > 0){
					foreach($restrictedAccessElements as $accessElement) {
						?>
						<div>
							<input class="frmwk_access_accesselement_restrict_<?php echo $_GET['accountID'];?>" type="checkbox" style="width:auto;" name="accesselementrestrict_<?php print $_GET['accountID']."_module_".$moduleID[$x]; ?>[]" value="<?php echo $accessElement['name'];?>"
							id="account_<?php print $_GET['accountID']."_module_".$moduleID[$x]; ?>_accesselementrestrict<?php echo $accessElement['name'];?>"
							<?php if(in_array($accessElement['name'], $apiModuleAccess['restrictaccesselements'])){ ?> checked="checked"<?php } ?>  />
							<label for="account_<?php print $_GET['accountID']."_module_".$moduleID[$x]; ?>_accesselementrestrict<?php echo $accessElement['name'];?>">
								<?php
								$accessElementName = ${"accessElementRestrict_".$accessElement['name']."_name"};
								$accessElementDescription = ${"accessElementRestrict_".$accessElement['name']."_description"};
								if($accessElementName != ""){
									echo $accessElementName;
								} else {
									echo $accessElement['name'];
								}
								if($accessElementDescription != ""){
									?>
									<span class="fas fa-info-circle accessElementInfoHoverWrapper"><div class="accessElementInfoHover"><?php echo $accessElementDescription;?></div></span>

									<?php
								}
								?>
							</label>
						</div>
						<?php
					}
				}
				?>
			</div>
			<div class="access_element_column">
				<?php if(!isset($_GET['no_owner']) && $b_show_owner_access_restrict && $v_show_owner_access_restrict[$x] == 1) { ?>
				<input class="frmwk_access_<?php print $i; ?> 4" type="checkbox" name="account_<?php print $_GET['accountID']."_module_".$moduleID[$x]; ?>[]" value="20" style="width:auto;" checked="checked" id="account_<?php print $_GET['accountID']."_module_".$moduleID[$x]; ?>_ID4"/ onChange="changeModuleAccess(this);"> <label class="l4" for="account_<?php print $_GET['accountID']."_module_".$moduleID[$x]; ?>_ID4"><?php echo $formText_RestrictWriteAndDeleteToOwnerOnly_usersOutputLink; ?></label>
				<?php } ?>
			</div>
		</td>
		</tr><?php
	}
	?></table>
	<style>

	.access_element_column {
	    display: inline-block;
	    vertical-align: top;
	    width:100%;

	}
	.access_element_column div {
	    -webkit-column-break-inside: avoid;
	    page-break-inside: avoid;
	    break-inside: avoid;
	    word-break: break-all;
	}
	.specified_access_table td {
	    vertical-align: top;
	    padding-top: 5px;
	    padding-bottom: 5px;
	    border-top: 1px solid #cecece;
	}
	.accessElementInfoHoverWrapper {
	    position: relative;
	}
	.accessElementInfoHoverWrapper:hover .accessElementInfoHover  {
	    display: block;
	}
	.accessElementInfoHover {
	    display: none;
	    position: absolute;
	    width: 300px;
	    left: 0;
	    background: #fff;
	    text-align: left;
	    padding: 10px 15px;
	    z-index: 1;
	    display: none;
	    -webkit-box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.1);
	    -moz-box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.1);
	    box-shadow: 0px 0px 5px 0px rgba(0,0,0,0.1);
	    font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
	    font-weight: normal;
	}
	</style><?php

	$return['html'] = ob_get_clean();
}

print json_encode($return);
?>
