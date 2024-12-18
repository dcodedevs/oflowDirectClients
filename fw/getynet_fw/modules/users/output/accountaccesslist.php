<?php
if(!function_exists("APIconnectorAccount") || !function_exists("APIconnectorUser")) include(__DIR__."/../../../includes/APIconnector.php");

include_once(__DIR__.'/getAccessElementList.php');
if($groupEdit){
    $data = json_decode(APIconnectorUser("groupcompanyaccessget", $_COOKIE['username'], $_COOKIE['sessionID'], array('COMPANY_ID'=>$companyID, 'GROUP_ID'=>$_GET['groupID'])),true);
    $groupAccess = $data['data'];
} else {
	$data = json_decode(APIconnectorUser("companyaccessget", $_COOKIE['username'], $_COOKIE['sessionID'], array('ACCESSID'=>$_GET['accessID'])),true);
	$userAccess = $data['data'];
}
$data = json_decode(APIconnectorUser("accountbycompanyidgetlist", $_COOKIE['username'], $_COOKIE['sessionID'], array('COMPANY_ID'=>$companyID)),true);
$apiAccounts = $data['data'];
?>
<table border="0" cellpadding="0" cellspacing="0" rules="none" frame="void" style="background-color:#EEEEEE; width:100%">
<?php

$b_simple_access = (0 < intval($variables->accountinfo['getynet_app_id']));

$i = 1;
foreach($apiAccounts as $apiAccount)
{
    if($groupEdit){
        $data = json_decode(APIconnectorUser("groupaccountaccessget", $_COOKIE['username'], $_COOKIE['sessionID'], array('ACCOUNT_ID'=>$apiAccount['id'], 'GROUPCOMPANYACCOUNT_ID'=>$groupAccess['id'])),true);
        $apiAccountAccess = $data['data'];
    } else {
        $data = json_decode(APIconnectorUser("accountaccessget", $_COOKIE['username'], $_COOKIE['sessionID'], array('ACCOUNT_ID'=>$apiAccount['id'], 'COMPANYACCESS_ID'=>$userAccess['id'])),true);
        $apiAccountAccess = $data['data'];
    }
    // print_r($data);
    ?>
    <tr>
    <td style="padding-left:30px; width:120px;"><?php echo ($apiAccount['friendlyaccountname'] != '' ? $apiAccount['friendlyaccountname'] : $apiAccount['accountname']);?></td>
    <td width="400" align="left"><select name="accountaccess_<?php echo $apiAccount['id']; ?>" id="accountaccess_<?php echo $apiAccount['id']; ?>_id" style="width:auto;" onChange="javascript:fw_useradmin_updateaccountlevel('<?php echo $apiAccount['id'];?>','getynet_fw/modules/users','users','<?php echo $variables->languageID;?>','users','<?php echo $formText_modulenamelistaccess_usersOutputLink; ?>','<?php echo $formText_moduleaccessheader_usersOutputLink; ?>','<?php echo $apiAccount['accountname'];?>','<?php echo $apiAccount['accounttype']; ?>');"><option value="1" <?php if($apiAccountAccess['accesslevel'] == 1){ ?> selected="selected"<?php } ?>><?php echo $formText_moduleLevelAll_usersOutputLink;?></option><option value="2" <?php if($apiAccountAccess['accesslevel'] == 2){ ?> selected="selected"<?php } ?>><?php echo $formText_moduleLevelRestricted_usersOutputLink;?></option><option value="0" <?php if($apiAccountAccess['accesslevel'] == 0){ ?> selected="selected"<?php } ?>><?php echo $formText_moduleLevelDenied_usersOutputLink;?></option></select></td>
    </tr>
    <tr>
        <td colspan="2" style="border-bottom:5px solid #FFFFFF;">
            <div id="accountmodulesaccesslistid_<?php echo $apiAccount['id']; ?>">
                <?php
                if($apiAccountAccess['accesslevel'] == 2)
                {
                    $b_show_owner_access_restrict = $b_content_access = false;
                    $modulelist = $modulename = $modulemode = $moduleID = $v_show_owner_access_restrict = $v_content_access = $dashboard = array();
                    $s_response = APIconnectorAccount("account_module_list_get", $v_accountinfo['accountname'], $v_accountinfo['password'], array('ACC_NAME'=>$apiAccount['accountname']));
                    $v_response = json_decode($s_response, true);
//print_r($s_response);
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

                    if(count($modulelist)>0)
                    {
                        ?>
                        <table border="0" cellpadding="0" cellspacing="0" rules="none" frame="void" style="background-color:#EEEEEE;  width: 100%; min-width:852px;" class="specified_access_table">
                        <tr>
							<td style="border-bottom:1px solid #000000; width:210px; padding-left:40px;"><?php echo $formText_modulenamelistaccess_usersOutputLink;?></td>
							<td style="border-bottom:1px solid #000000;" colspan="3"><?php echo $formText_moduleaccessheader_usersOutputLink;?></td>
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
                                    <a href="#" onClick="javascript:accessElementAll('frmwk_access_accesselement_allow_<?php print $apiAccount['id']; ?>'); return false;" style="padding-right:0px;"><?php print $formText_ExpandAccess_usersOutputLink; ?></a>
                                </div>
							</td>
							<td style="background-color:rgba(255, 87, 51, 0.3); padding-left:10px;">
                                <div class="access_element_column">
                                    <a href="#" onClick="javascript:accessElementAll('frmwk_access_accesselement_restrict_<?php print $apiAccount['id']; ?>'); return false;" style="padding-right:0px;"><?php print $formText_RestrictAccess_usersOutputLink; ?></a>
                                </div>
                                <?php if($b_show_owner_access_restrict) { ?>
                                <div class="access_element_column">
                                <a href="#" onClick="javascript:ownerAll('frmwk_access_<?php print $i; ?>'); return false;"><?php print $formText_RestrictWriteAndDeleteToOwnerOnly_usersOutputLink; ?></a>
                                </div>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php
                        
						foreach ($modulelist as $x => $name) 
						{	
                            if($groupEdit){
                                $data = json_decode(APIconnectorUser("groupmoduleaccessget", $_COOKIE['username'], $_COOKIE['sessionID'], array('ACCOUNTACCESS_ID'=>$apiAccountAccess['id'], 'ACCOUNTMODULE_ID'=>$moduleID[$x])),true);
								$apiModuleAccess = $data['data'];
                            } else {
                                $data = json_decode(APIconnectorUser("moduleaccessget", $_COOKIE['username'], $_COOKIE['sessionID'], array('ACCOUNTACCESS_ID'=>$apiAccountAccess['id'], 'ACCOUNTMODULE_ID'=>$moduleID[$x])),true);
                                $apiModuleAccess = $data['data'];
                            }
                            ?>
                            <tr>
                            <td style="padding-left:40px;"><?php echo $modulename[$x]; ?></td>
                            <td>
                            <?php if(!$b_simple_access) { ?>
							<input class="frmwk_access_<?php print $i; ?> 1" type="checkbox" name="account_<?php print $apiAccount['id']."_module_".$moduleID[$x]; ?>[]" value="1" style="width:auto;" <?php if(($apiModuleAccess['accesslevel'] % 10) >= 1){ ?> checked="checked"<?php } ?> id="account_<?php print $apiAccount['id']."_module_".$moduleID[$x]; ?>_ID1" onChange="changeModuleAccess(this);fw_toggle_extended(this);"> <label for="account_<?php print $apiAccount['id']."_module_".$moduleID[$x]; ?>_ID1" style="padding-right:10px;"><?php echo $formText_modulenamelistreadaccess_usersOutputLink; ?></label>
                            <input class="frmwk_access_<?php print $i; ?> 2" type="checkbox" name="account_<?php print $apiAccount['id']."_module_".$moduleID[$x]; ?>[]" value="10" style="width:auto;" <?php if(($apiModuleAccess['accesslevel'] % 100) >= 10 ){ ?> checked="checked"<?php } ?> id="account_<?php print $apiAccount['id']."_module_".$moduleID[$x]; ?>_ID2" onChange="changeModuleAccess(this);"> <label for="account_<?php print $apiAccount['id']."_module_".$moduleID[$x]; ?>_ID2" style="padding-right:10px;"><?php echo $formText_modulenamelistwriteaccess_usersOutputLink; ?></label>
							<?php } ?>
                            <input class="frmwk_access_<?php print $i; ?> 3" type="checkbox" name="account_<?php print $apiAccount['id']."_module_".$moduleID[$x]; ?>[]" value="<?php echo ($b_simple_access ? 111 : 100);?>" style="width:auto;" <?php if(($apiModuleAccess['accesslevel'] % 1000) >= 100){ ?> checked="checked"<?php } ?> id="account_<?php print $apiAccount['id']."_module_".$moduleID[$x]; ?>_ID3" onChange="changeModuleAccess(this);<?php echo (!$b_simple_access?'':'fw_toggle_extended(this);');?>"> <label for="account_<?php print $apiAccount['id']."_module_".$moduleID[$x]; ?>_ID3" style="padding-right:10px;"><?php echo (!$b_simple_access ? $formText_modulenamelistdeleteaccess_usersOutputLink : $formText_AccessModule_usersOutputLink); ?></label>
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
                                <input type="checkbox" name="content_0_<?php echo $moduleID[$x]."_".$apiAccount['id'];?>[]" id="content_0_<?php echo $moduleID[$x]."_".$apiAccount['id'];?>" value="restrict" style="width:auto;" <?php if($apiModuleAccess['restricted_content'] == 1){ ?> checked="checked"<?php } ?> onChange="restricted_content_change(this);"> <label for="content_0_<?php echo $moduleID[$x]."_".$apiAccount['id'];?>"><?php echo $formText_RestrictContent_usersOutputLink;?></label>
                                <div class="content_access<?php if($apiModuleAccess['restricted_content'] == 0) print ' hide';?>"><?php
                                foreach($v_content_access[$x] as $v_item)
                                {
                                    if(base64_decode($v_item[1], true) !== false)
                                    {
                                        $v_item[1] = base64_decode($v_item[1]);
                                    }
                                    if($v_item[2] == 1)
                                    {
                                        ?><input type="hidden" name="content_<?php echo $v_item[0]."_".$moduleID[$x]."_".$apiAccount['id'];?>[]" value="<?php echo $v_item[0];?>"><input type="checkbox" style="width:auto;" checked disabled id="content_<?php echo $v_item[0]."_".$moduleID[$x]."_".$apiAccount['id'];?>"> <label for="content_<?php echo $v_item[0]."_".$moduleID[$x]."_".$apiAccount['id'];?>" style="margin-right:10px;"><?php echo $v_item[1];?></label><?php
                                    } else {
                                        ?><input type="checkbox" name="content_<?php echo $v_item[0]."_".$moduleID[$x]."_".$apiAccount['id'];?>[]" value="<?php echo $v_item[0];?>" style="width:auto;" <?php if($apiModuleAccess['content_access'][$v_item[0]] == 1){ ?> checked="checked"<?php } ?> id="content_<?php echo $v_item[0]."_".$moduleID[$x]."_".$apiAccount['id'];?>"> <label for="content_<?php echo $v_item[0]."_".$moduleID[$x]."_".$apiAccount['id'];?>" style="margin-right:10px;"><?php echo $v_item[1];?></label><?php
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

                            foreach($output_folders as $output_dir)
                            {
                    			if(is_file($output_dir.'/'."languagesOutput/accesselements_empty.php")) include($output_dir.'/'."languagesOutput/accesselements_empty.php");
                    			if(is_file($output_dir.'/'."languagesOutput/accesselements_$variables->languageID.php")) include($output_dir.'/'."languagesOutput/accesselements_$variables->languageID.php");

                            }
                            $accessElements = getAccessElements($modulelist[$x], "allow");
                            ?>
                            <div class="access_element_column"<?php echo ($apiModuleAccess['accesslevel']>0?'':' style="display:none;"');?>>
                            <?php
                            if(count($accessElements) > 0){

                                foreach($accessElements as $accessElement) {
                                    ?>
                                    <div>
                                        <input class="frmwk_access_accesselement_allow_<?php echo $apiAccount['id'];?>" type="checkbox" style="width:auto;" name="accesselement_<?php print $apiAccount['id']."_module_".$moduleID[$x]; ?>[]" value="<?php echo $accessElement['name'];?>"
                                        id="account_<?php print $apiAccount['id']."_module_".$moduleID[$x]; ?>_accesselement<?php echo $accessElement['name'];?>"
                                        <?php if(in_array($accessElement['name'], $apiModuleAccess['accesselements'])){ ?> checked="checked"<?php } ?>  />
                                        <label for="account_<?php print $apiAccount['id']."_module_".$moduleID[$x]; ?>_accesselement<?php echo $accessElement['name'];?>">
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
                            }
                            ?>
                            </div>
							</td>
							<td style="background-color:rgba(255, 87, 51, 0.3); padding-left:10px;">
                            <?php
                            $restrictedAccessElements = getAccessElements($modulelist[$x], "restrict");
                            ?>
                            <div class="access_element_column"<?php echo ($apiModuleAccess['accesslevel']>0?'':' style="display:none;"');?>>
                            <?php
                            if(count($restrictedAccessElements) > 0){

                                foreach($restrictedAccessElements as $accessElement) {
                                    ?>
                                    <div>
                                        <input class="frmwk_access_accesselement_restrict_<?php echo $apiAccount['id'];?>" type="checkbox" style="width:auto;" name="accesselementrestrict_<?php print $apiAccount['id']."_module_".$moduleID[$x]; ?>[]" value="<?php echo $accessElement['name'];?>"
                                        id="account_<?php print $apiAccount['id']."_module_".$moduleID[$x]; ?>_accesselementrestrict<?php echo $accessElement['name'];?>"
                                        <?php if(in_array($accessElement['name'], $apiModuleAccess['restrictaccesselements'])){ ?> checked="checked"<?php } ?>  />
                                        <label for="account_<?php print $apiAccount['id']."_module_".$moduleID[$x]; ?>_accesselementrestrict<?php echo $accessElement['name'];?>">
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
                            <?php
                            ?>
                            <div class="access_element_column"<?php echo ($apiModuleAccess['accesslevel']>0?'':' style="display:none;"');?>>
                                <?php if($b_show_owner_access_restrict && $v_show_owner_access_restrict[$x] == 1) { ?>
                                <input class="frmwk_access_<?php print $i; ?> 4<?php if(($apiModuleAccess['accesslevel'] % 100) < 10) print ' hide';?>" type="checkbox" name="account_<?php print $apiAccount['id']."_module_".$moduleID[$x]; ?>[]" value="20" style="width:auto; margin-left:0px;" <?php if($apiModuleAccess['owneraccess'] == 1){ ?> checked="checked"<?php } ?> id="account_<?php print $apiAccount['id']."_module_".$moduleID[$x]; ?>_ID4" onChange="changeModuleAccess(this);"> <label class="l4<?php if(($apiModuleAccess['accesslevel'] % 100) < 10) print ' hide';?>" for="account_<?php print $apiAccount['id']."_module_".$moduleID[$x]; ?>_ID4"><?php echo $formText_RestrictWriteAndDeleteToOwnerOnly_usersOutputLink; ?></label>
                                <?php } ?>
                            </div>
                            </td>
                            </tr><?php
                        }
                        ?></table><?php
                    } else {
                        print '<p class="bg-danger" style="line-height:30px; text-align:center;">'.$formText_ErrorOccuredRetrievingModuleList_usersOutputLink.'</p>';
                    }
                }
                ?>
            </div>
        </td>
    </tr>
    <?php
    $i++;
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
</style>
<?php
