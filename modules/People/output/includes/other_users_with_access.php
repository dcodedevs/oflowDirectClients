<?php
require_once __DIR__ . '/list_btn.php';
$people_contactperson_type = 2;
$sql = "SELECT * FROM accountinfo_basisconfig ORDER BY id";
$o_query = $o_main->db->query($sql);
$accountinfo_basisconfig = $o_query ? $o_query->row_array() : array();
if(intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']) > 0){
	$people_contactperson_type = intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']);
}
if(intval($variables->accountinfo['contactperson_type_to_use_in_people']) > 0)
{
	$people_contactperson_type = $variables->accountinfo['contactperson_type_to_use_in_people'];
}
$extradir = "../fw/getynet_fw/modules/users";
$includeFile = __DIR__."/../../../../fw/getynet_fw/modules/users/output/output_javascript.php";
if(is_file($includeFile)) include($includeFile);
?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
            <div class="p_tableFilter">
                <div class="p_tableFilter_left">
                    <span class="fas fa-users fw_icon_title_color"></span>
                    <?php echo $formText_OtherUsersWithAccess_Output;?>
                </div>
            </div>
			<div class="p_pageContent">
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
                    foreach($v_membersystem_un as $writeContent) {
                        $s_sql = "SELECT * FROM contactperson WHERE email = ? AND type = ?";
                        $o_result = $o_main->db->query($s_sql, array($writeContent['username'], $people_contactperson_type));
                        $people = $o_result ? $o_result->row_array() : array();

                        if(!$people){
                            ?>
                            <tr>
        						<td>
                                    <?php /*
        							<a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&modulename=".$modulename."&folder=output&folderfile=outputedit&username=".$writeContent['username']."&accessID=".$writeContent['id']."&getynetaccount=1";?>"<?php echo ($writeContent['deactivated']==1?' style="color:#BBBBBB;"':'');?>>
                                    </a>*/?>
                                    <?php echo $writeContent['username'];?>
        						</td>
        						<td>
                                    <?php /*
        							<a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&modulename=".$modulename."&folder=output&folderfile=outputedit&username=".$writeContent['username']."&accessID=".$writeContent['id']."&getynetaccount=1";?>"<?php echo ($writeContent['deactivated'] == 1?' style="color:#BBBBBB;':'');?>>
                                    </a>*/?>
                                    <?php echo ($writeContent['users_name'] != '' ? $writeContent['users_name'] : $writeContent['fullname']);?>
        						</td>
        						<td<?php echo ($writeContent['deactivated']==1?' style="color:#BBBBBB;"':'');?>><?php
        							if($writeContent['accesslevel'] == 1)
        							{
        								print $formText_listvalueAll_usersOutputList;
        							}
        							elseif($writeContent['accesslevel'] == 2) {
        								print $formText_listvalueRestricted_usersOutputList;
        							}
        							elseif($writeContent['accesslevel'] == 0) {
        								print $formText_listvalueNoAccess_usersOutputList;
        							} else {
        								print $formText_listvalueGroup_usersOutputList. " - ".$writeContent['groupname'];
        							}
        						?></td>
        						<td<?php echo ($writeContent['deactivated']==1?' style="color:#BBBBBB;"':'');?>><?php echo ($writeContent['admin']=='1'?" X ":"");?></td>
        						<td<?php echo ($writeContent['deactivated']==1?' style="color:#BBBBBB;"':'');?>><?php echo ($writeContent['system_admin']=='1'?" X ":"");?></td>
        						<td<?php echo ($writeContent['deactivated']==1?' style="color:#BBBBBB;"':'');?>><?php
        							if($writeContent['invitationsent'] != '')
        							{
        								if(stristr($writeContent['invitationsent'],","))
        								{
        									print substr($writeContent['invitationsent'],strrpos($writeContent['invitationsent'],",")+1). " (".$writeContent['invitationsentnr'].")";
        								} else {
        									print $writeContent['invitationsent'];
        								}
        							}
        						?></td>
        						<td<?php echo ($writeContent['deactivated']==1?' style="color:#BBBBBB;"':'');?>><?php echo ($writeContent['registeredID'] != ''?" X ":"");?></td>
        						<td<?php echo ($writeContent['deactivated']==1?' style="color:#BBBBBB;"':'');?>><?php echo ($writeContent['deactivated'] == 1?" X ":"");?></td>
        						<td>
        							<form id="fw_useradmin_deleteuser_<?php echo $writeContent['id'];?>" name="update" action="<?php echo $extradir;?>/output/outputreg.php" method="POST">
        							<input type="hidden" name="pageID" value="<?php echo $_GET['pageID'];?>">
        							<input type="hidden" name="accountname" value="<?php echo $_GET['accountname'];?>">
        							<input type="hidden" name="companyID" value="<?php echo $_GET['companyID'];?>">
        							<input type="hidden" name="caID" value="<?php echo $_GET['caID'];?>">
        							<input type="hidden" name="userID" value="<?php echo $writeContent['id']; ?>">
        							<input type="hidden" name="deletetest" value="" id="fw_useradmin_deleteusermark_<?php echo $writeContent['id']; ?>">
        							<input type="hidden" name="username" value="<?php echo $writeContent['username']; ?>">
        							<input type="hidden" name="extradir" value="<?php echo $extradir; ?>">
        							<input type="hidden" name="module" value="<?php echo $module; ?>">
        							<input type="hidden" name="updateuser" value="1">
        							<input type="hidden" name="returnurl" value="<?php echo (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');?>">
        							<input type="image" onClick="fw_useradmin_deleteuserconfirmlink('<?php echo $writeContent['id'];?>','<?php echo $writeContent['username'];?>'); return false;" src="<?php echo $extradir;?>/output/elementsOutput/delete_icon.gif" width="20" alt="" border="0">
        							</form>
        						</td>
        					</tr>
                            <?php
                        }
                    }
                    ?>
                </table>
			</div>
		</div>
	</div>
</div>
