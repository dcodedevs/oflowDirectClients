<?php
if(isset($_POST['cid'])){ $cid = $_POST['cid']; } else if(isset($_GET['cid'])){ $cid = $_GET['cid']; } else { $cid = NULL;}
if(!function_exists("filter_email_by_domain")) include_once(__DIR__."/fnc_filter_email_by_domain.php");
require("functions.php");
$sql = "SELECT p.*
         FROM contactperson p
        WHERE p.id = ?";
$o_query = $o_main->db->query($sql, array($cid));
$peopleData = $o_query ? $o_query->row_array() : array();
$bannerImage = json_decode($peopleData['profileBannerImage']);


$o_query = $o_main->db->query("SELECT * FROM accountinfo");
$v_accountinfo = $o_query ? $o_query->row_array() : array();

$o_query = $o_main->db->query("SELECT * FROM cache_userlist_access LIMIT 1");
$v_cache_userlist_access = $o_query ? $o_query->row_array() : array();

$v_param = array(
	'COMPANY_ID'=>$companyID,
	'CACHE_TIMESTAMP'=>$v_cache_userlist_access['cache_timestamp'],
	'CACHE_RECREATE'=>strtotime($variables->accountinfo['force_cache_refresh']) > strtotime($v_cache_userlist_access['cache_timestamp']),
	'GET_MEMBERSHIPS' => 1
);
$s_response = APIconnectorUser("companyaccessbycompanyidget_v2", $variables->loggID, $variables->sessionID, $v_param);
$v_response = json_decode($s_response, TRUE);
if(isset($v_response['status']) && $v_response['status'] == 1 && $v_response['cache_status'] != 2)
{
	$o_main->db->query("TRUNCATE cache_userlist_access");
	foreach($v_response['data'] as $v_item)
	{
		$o_main->db->query("INSERT INTO cache_userlist_access SET cache_timestamp = '".$o_main->db->escape_str($v_response['cache_timestamp'])."', companyaccess_id = '".$o_main->db->escape_str($v_item['companyaccess_id'])."', user_id = '".$o_main->db->escape_str($v_item['user_id'])."', username = '".$o_main->db->escape_str($v_item['username'])."', first_name = '".$o_main->db->escape_str($v_item['first_name'])."', middle_name = '".$o_main->db->escape_str($v_item['middle_name'])."', last_name = '".$o_main->db->escape_str($v_item['last_name'])."', deactivated = '".$o_main->db->escape_str($v_item['deactivated'])."', admin = '".$o_main->db->escape_str($v_item['admin'])."', system_admin = '".$o_main->db->escape_str($v_item['system_admin'])."', groupID = '".$o_main->db->escape_str($v_item['groupID'])."', groupname = '".$o_main->db->escape_str($v_item['groupname'])."', accesslevel = '".$o_main->db->escape_str($v_item['accesslevel'])."', image = '".$o_main->db->escape_str($v_item['image'])."', mobile = '".$o_main->db->escape_str($v_item['mobile'])."', mobile_prefix = '".$o_main->db->escape_str($v_item['mobile_prefix'])."', mobile_verified = '".$o_main->db->escape_str($v_item['mobile_verified'])."', firstlogin = '".$o_main->db->escape_str($v_item['firstlogin'])."', lastlogin = '".$o_main->db->escape_str($v_item['lastlogin'])."', last_activity = '".$o_main->db->escape_str($v_item['last_activity'])."', invitationsent = '".$o_main->db->escape_str($v_item['invitationsent'])."', invitationsentnr = '".$o_main->db->escape_str($v_item['invitationsentnr'])."', `groups` = '".$o_main->db->escape_str(json_encode($v_item['groups']))."'");
	}
	$o_main->db->query("TRUNCATE cache_userlist_membershipaccess");
	foreach($v_response['data_memberships'] as $v_item)
	{
		$o_main->db->query("INSERT INTO cache_userlist_membershipaccess SET cache_timestamp = '".$o_main->db->escape_str($v_response['cache_timestamp'])."', companyaccess_id = '".$o_main->db->escape_str($v_item['companyaccess_id'])."', user_id = '".$o_main->db->escape_str($v_item['user_id'])."', username = '".$o_main->db->escape_str($v_item['username'])."', first_name = '".$o_main->db->escape_str($v_item['first_name'])."', middle_name = '".$o_main->db->escape_str($v_item['middle_name'])."', last_name = '".$o_main->db->escape_str($v_item['last_name'])."', deactivated = '".$o_main->db->escape_str($v_item['deactivated'])."', admin = '".$o_main->db->escape_str($v_item['admin'])."', system_admin = '".$o_main->db->escape_str($v_item['system_admin'])."', groupID = '".$o_main->db->escape_str($v_item['groupID'])."', groupname = '".$o_main->db->escape_str($v_item['groupname'])."', accesslevel = '".$o_main->db->escape_str($v_item['accesslevel'])."', image = '".$o_main->db->escape_str($v_item['image'])."', mobile = '".$o_main->db->escape_str($v_item['mobile'])."', mobile_prefix = '".$o_main->db->escape_str($v_item['mobile_prefix'])."', mobile_verified = '".$o_main->db->escape_str($v_item['mobile_verified'])."', firstlogin = '".$o_main->db->escape_str($v_item['firstlogin'])."', lastlogin = '".$o_main->db->escape_str($v_item['lastlogin'])."', last_activity = '".$o_main->db->escape_str($v_item['last_activity'])."', invitationsent = '".$o_main->db->escape_str($v_item['invitationsent'])."', invitationsentnr = '".$o_main->db->escape_str($v_item['invitationsentnr'])."', `groups` = '".$o_main->db->escape_str(json_encode($v_item['groups']))."'");
	}
}


$groups = array();
$departments = array();

// $v_response = json_decode(APIconnectorAccount("userinfoget", $v_accountinfo['accountname'], $v_accountinfo['password'], array('SEARCH_USERNAME'=>$peopleData['email'])), TRUE);

$registered_group_list = array();
$v_membersystem = array();
$v_registered_usernames = array();

$o_query = $o_main->db->query("SELECT * FROM cache_userlist_membershipaccess");
$v_cache_userlist_membership = $o_query ? $o_query->result_array() : array();
foreach($v_cache_userlist_membership as $v_user_cached_info) {
	$v_membersystem[$v_user_cached_info['username']] = $v_user_cached_info;
    if($v_user_cached_info['user_id'] > 0) $v_registered_usernames[] = $v_user_cached_info['username'];
	$registered_group_list[$v_user_cached_info['username']] = json_decode($v_user_cached_info['groups'], true);
}

$o_query = $o_main->db->query("SELECT * FROM cache_userlist_access");
$v_cache_userlist = $o_query ? $o_query->result_array() : array();
foreach($v_cache_userlist as $v_user_cached_info) {
	$v_membersystem[$v_user_cached_info['username']] = $v_user_cached_info;
	$v_registered_usernames[] = $v_user_cached_info['username'];
	$registered_group_list[$v_user_cached_info['username']] = json_decode($v_user_cached_info['groups'], true);
}

// $response = json_decode(APIconnectorUser("companyaccessbycompanyidget", $variables->loggID, $variables->sessionID, array('COMPANY_ID'=>$companyID, 'GET_GROUPS'=>1)), true);
// $v_membersystem = array();
// foreach($response['data'] as $writeContent)
// {
//     array_push($v_membersystem, $writeContent);
// }
$b_registered_user = false;
$nameToDisplay = $peopleData['name']." ". $peopleData['middlename']." ". $peopleData['lastname'];
$imgToDisplay = "";
$v_access = null;
foreach($v_membersystem as $member){
    if(mb_strtolower($member['username'])== mb_strtolower($peopleData['email'])){
        if($member['user_id'] > 0){
            $b_registered_user = true;
            if($member['image'] != "" && $member['image'] != null){
				$imgToDisplay = json_decode($member['image'],true);
			}
            if($member['first_name'] != ""){
				$nameToDisplay = $member['first_name'] . " ". $member['middle_name']." ".$member["last_name"];
			}
        }

		$currentMember = $member;
        $v_access = $currentMember;
        break;
    }
}

$canEdit = false;
$canEditAdmin = false;
if($variables->loggID == $peopleData['email']) {
	$canEdit = true;
}
$canEditAdmin = $accessElementAllow_AddEditDeletePeople;
if(!$b_registered_user)
{
    $v_response = json_decode(APIconnectorUser("userdetailsget", $variables->loggID, $variables->sessionID, array('USERNAME'=>$peopleData['email'])), TRUE);

    if(!array_key_exists("error", $v_response))
    {
    	$hasGetynetAccount = TRUE;
    } else {
    	$hasGetynetAccount = FALSE;
    }
    if($peopleData['email'] != ""){
    	$o_query = $o_main->db->query("SELECT * FROM accountinfo");
    	$v_accountinfo = $o_query ? $o_query->row_array() : array();
    	$v_response = json_decode(APIconnectorAccount("user_image_upload_get", $v_accountinfo['accountname'], $v_accountinfo['password'], array('username'=>$peopleData['email'])), TRUE);
    	if(isset($v_response['status']) && $v_response['status'] == 1)
    	{
    		$imgToDisplay = json_decode($v_response['image'],TRUE);
    	}
    }

    $not_registered_group_list = array();
	$v_response = json_decode(APIconnectorUser("group_get_list_by_filter", $variables->loggID, $variables->sessionID, array('company_id'=>$_GET['companyID'], 'usernames'=>array($peopleData['email']), 'not_hidden'=> 1)),true);
	if(isset($v_response['status']) && $v_response['status'] == 1)
	{
		$not_registered_group_list = $v_response['items'];
	}
}

$sql = "SELECT p.* FROM contactperson_group p
JOIN contactperson_group_user pu ON pu.contactperson_group_id = p.id
JOIN contactperson ON contactperson.id = pu.contactperson_id
WHERE p.status = 1 AND p.department = 1 AND contactperson.id = ? ORDER BY p.name";
$o_query = $o_main->db->query($sql, array($peopleData['id']));
$departments = $o_query ? $o_query->result_array(): array();

$sql = "SELECT p.* FROM contactperson_group p
JOIN contactperson_group_user pu ON pu.contactperson_group_id = p.id
JOIN contactperson ON contactperson.id = pu.contactperson_id
WHERE p.status = 1 AND (p.department = 0 OR p.department is null) AND contactperson.id = ? ORDER BY p.name";
$o_query = $o_main->db->query($sql, array($peopleData['id']));
$groups = $o_query ? $o_query->result_array(): array();

$peopleData['groups'] = $groups;
$peopleData['departments'] = $departments;

$s_sql = "select * from people_basisconfig";
$o_query = $o_main->db->query($s_sql);
$v_employee_basisconfig = $o_query ? $o_query->row_array() : array();

$s_sql = "select * from people_accountconfig";
$o_query = $o_main->db->query($s_sql);
$v_employee_accountconfig = ($o_query ? $o_query->row_array() : array());

foreach($v_employee_accountconfig as $key=>$value){
    if(!isset($v_employee_basisconfig[$key])){
        if($value > 0){
            $v_employee_basisconfig[$key] = ($value - 1);
        } else {
            $v_employee_basisconfig[$key] = 0;
        }
    } else if (isset($v_employee_basisconfig[$key]) && $value > 0){
        $v_employee_basisconfig[$key] = ($value - 1);
    }
}

$companyCanEditProfile = false;
if($currentMember['username'] != ""){
	//check if company has access to edit
	$v_param = array(
		'COMPANY_ID'=>$_GET['companyID'],
		'USERNAME'=>$currentMember['username']
	);
	$s_response = APIconnectorUser("companyallowededituserdata_check", $variables->loggID, $variables->sessionID, $v_param);
	$v_response = json_decode($s_response, TRUE);
	if($v_response['data'] == 'OK'){
		$companyCanEditProfile = true;
	}
}

require_once __DIR__ . '/functions.php';
$perPage = $_SESSION['listpagePerPage'];
$page = $_SESSION['listpagePage'];
if($perPage > 0 && $page > 0) {
    $listPage = 1;
    $listPagePer = $page*$perPage;
    $customerList = get_customer_list($o_main, $list_filter, $filters, $listPage, $listPagePer, $cid);
    $prevCustomer = $customerList[0];
    $nextCustomer = $customerList[2];
    $nextId = $nextCustomer['id'];
    $prevId = $prevCustomer['id'];
}

$s_prev_link =  $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$prevId;
$s_next_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$nextId;

$s_sql = "SELECT * FROM people_view_history WHERE people_view_history.username = ?";
$o_query = $o_main->db->query($s_sql, array($variables->loggID));
$customer_view_history = $o_query ? $o_query->row_array() : array();
if($customer_view_history) {
	$customerHistory = json_decode($customer_view_history['history_log'], true);
	$newHistoryList = array();
	$newCount = 0;
	$customerHistoryOrdered = array_reverse($customerHistory);
	foreach($customerHistoryOrdered as $customerHistoryItem) {
		if($customerHistoryItem['id'] != $cid){
			if($newCount < 19){
				$newCount++;
				$newHistoryList[] = $customerHistoryItem;
			}
		}
	}
	$newHistoryList = array_reverse($newHistoryList);
	$newHistoryList[] = array("id"=>$cid, "time"=>date("d.m.Y H:i:s", time()));

	$s_sql = "UPDATE people_view_history SET updated = NOW(), history_log = ? WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array(json_encode($newHistoryList), $customer_view_history['id']));
} else {
	$customerHistory = array();
	$customerHistory[] = array("id"=>$cid, "time"=>date("Y-m-d H:i:s", time()));
	$s_sql = "INSERT INTO people_view_history SET username = ?, created = NOW(), history_log = ?";
	$o_query = $o_main->db->query($s_sql, array($variables->loggID, json_encode($customerHistory)));
}


$s_sql = "SELECT * FROM people_view_history WHERE people_view_history.username = ?";
$o_query = $o_main->db->query($s_sql, array($variables->loggID));
$customer_view_history = $o_query ? $o_query->row_array() : array();
$customerHistory = json_decode($customer_view_history['history_log'], true);
$customerHistoryOrdered = array_reverse($customerHistory);
?>
<div class="moduleSinglePage">
    <div class="pageTopImageWrapper">
        <div class="pageTopImage fw_module_head_color">
            <?php if(count($bannerImage)>0){ ?>
                <img src="../<?php echo ($bannerImage[0][1][0])?>" class="profileBanner"/>
            <?php } ?>
        </div>
        <?php if(!$v_employee_accountconfig['duplicate_module']) { ?>
            <?php if($peopleData['content_status'] < 2){ ?>
                <?php if(($canEdit || $canEditAdmin) && !$v_employee_basisconfig['deactivate_update_top_image']) {?>
                    <div class="editPageCoverBtn<?php if(count($bannerImage)==0) echo ' visible';?>"><?php echo $formText_changeBannerImage_output;?></div>
                <?php } ?>
            <?php } ?>
        <?php } ?>
    </div>
    <div class="pageBottomContentWrapper">
        <div class="pageBottomContentLeft">
            <div class="pageBottomContentLeftWrapper">
                <div class="profileImageWrapper">
                    <div class="profileImage fw_module_head_color">
                        <?php if($imgToDisplay != "") { ?>
                            <img src="https://pics.getynet.com/profileimages/<?php echo $imgToDisplay[0]; ?>" alt="<?php echo $nameToDisplay;?>" title="<?php echo $nameToDisplay;?>"/>
                        <?php } ?>
                    </div>

                    <?php if(!$v_employee_accountconfig['duplicate_module']) { ?>
                        <?php if($peopleData['content_status'] < 2){
                             ?>
                            <?php
                            if(($canEdit || $companyCanEditProfile  || $canEditAdmin) && $peopleData['email'] != "") { ?>
                                <div class="editProfileImageBtn<?php if($imgToDisplay == "") echo ' visible';?>"><?php echo $formText_UpdateProfilePicture_output;?></div>
        						<?php if($imgToDisplay != '') { ?><div class="deleteProfileImageBtn"><?php echo $formText_DeleteProfilePicture_output;?></div><?php } ?>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                </div>

                <?php if($peopleData['content_status'] < 2){ ?>
                    <?php if($canEdit){ ?>
                        <?php if(!$v_employee_accountconfig['duplicate_module']) { ?>
                            <div class="profileButton changePassword fw_button_color"><?php echo $formText_ChangePassword_output;?></div>
                        <?php } ?>
                    <?php } else {
                        if($b_registered_user){
                        ?>
                            <div class="profileButton sendMessage fw_button_color"><?php echo $formText_SendMessage_output;?></div>
                    <?php }
                        }?>
                <?php } ?>
            </div>
        </div>
        <div class="pageBottomContentRight">
            <div class="searchBlock">
                <?php if(count($customerHistoryOrdered) > 0) { ?>
                    <div class="fas fa-history historyList hoverEye"><div class="hoverInfo">
                        <div class="historyListTitle"><?php echo $formText_LastViewedCustomerCards_output;?></div>
                        <table clasas="gtable" style="width: 100%;">
                            <?php foreach($customerHistoryOrdered as $customerHistoryItem) {
                                $s_sql = "SELECT * FROM contactperson WHERE contactperson.id = ?";
                                $o_query = $o_main->db->query($s_sql, array($customerHistoryItem['id']));
                                $historyCustomer = $o_query ? $o_query->row_array() : array();
                                ?>
                                <tr class="gtable_row output-click-helper" data-href="<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folder=output&folderfile=output";?>&inc_obj=details&cid=<?php echo $customerHistoryItem['id'];?>">
                                    <td class="gtable_cell"><?php echo $historyCustomer['name']." ".$historyCustomer['lastname'];?></td>
                                    <td class="gtable_cell timeColor"><?php echo $customerHistoryItem['time'];?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div></div>
                <?php } ?>
                <div class="employeeSearch">
                    <span class="glyphicon glyphicon-search"></span>
                    <input type="text" placeholder="<?php echo $formText_Person_output;?>" class="employeeSearchInput" autocomplete="off"/>
                    <div class="employeeSearchSuggestions allowScroll"></div>
                </div>
                <?php if(intval($nextId) > 0){ ?>
                    <a href="<?php echo $s_next_link;?>" class="output-click-helper optimize next-link"><?php echo $formText_Next_outpup;?></a>
                <?php } ?>
                <?php if(intval($prevId) > 0){ ?>
                    <a href="<?php echo $s_prev_link;?>" class="output-click-helper optimize prev-link"><?php echo $formText_Prev_outpup;?></a>
                <?php } ?>
                <div class="clear"></div>
            </div>

            <div class="pageTitle">
                <?php echo $nameToDisplay;?>
                <?php if(!$v_employee_accountconfig['duplicate_module']) { ?>
                    <?php if($peopleData['content_status'] < 2){ ?>
                        <?php if($canEdit || $canEditAdmin) { ?>
                           <div class="overlayEditProfileBtn editBtn"><?php echo $formText_EditProfileBtn_output;?></div>
                       <?php } ?>
                   <?php } ?>
                   <?php if($canEdit || $canEditAdmin) {
                       if($peopleData['content_status'] == 2 || 1 == $peopleData['content_status']){ ?>
                           <div class="overlayActivateProfileBtn editBtn"><?php echo $formText_ActivateProfileBtn_output;?></div>
                       <?php
                       } else {
                        ?>
                        <div class="overlayDeleteProfileBtn editBtn"><?php echo $formText_DeleteProfileBtn_output;?></div>
                        <?php if(isset($people_accountconfig['activate_deactivated_status']) && 1 == $people_accountconfig['activate_deactivated_status']) { ?>
						<div class="overlayDeactivateProfileBtn editBtn"><?php echo $formText_DeactivateProfileBtn_output;?></div>
						<?php } ?>
                    <?php } ?>
                  <?php } ?>
              <?php } ?>
            </div>
            <div class="pageContent">
                <div class="pageContentRow">
                    <div class="pageContentRowLabel"><?php echo $formText_Email_output;?></div>
                    <div class="pageContentRowText">
                        <?php
                        $emailToDisplay = ($currentMember['username'] ? $currentMember['username']: $peopleData['email']);
                        if(!$canEditAdmin) {
                            $emailToDisplay = filter_email_by_domain($emailToDisplay);
                        }
                        ?>
                        <a class="link fw_text_link_color" href="mailto:<?php echo $emailToDisplay;?>"><?php echo $emailToDisplay;?></a>
                    </div>
                </div>
                <div class="pageContentRow">
                    <div class="pageContentRowLabel"><?php echo $formText_Phone_output;?></div>
                    <div class="pageContentRowText">
						<?php
                        echo ($currentMember['mobile'] ? $currentMember['mobile_prefix'].$currentMember['mobile']: $peopleData['mobile_prefix']." ".$peopleData['mobile']);?>

                        <?php if(!$v_employee_accountconfig['duplicate_module']) { ?>
                            <?php if($v_employee_basisconfig['activateMobileVerification']) {?>
    	                      <?php if($canEdit && '' != $currentMember['mobile']) { ?>
        						<?php if(0 == $currentMember['mobile_verified']) { ?>
                                    <a class="overlay-verify-mobile" data-mobile="<?php echo $currentMember['mobile'];?>" data-mobile-prefix="<?php echo $currentMember['mobile_prefix'];?>"><?php echo $formText_MobileNotVerifiedClickHereToVerify_Output;?></a><?php } else { echo $formText_Verified_Output; } ?>
        						<?php } ?>
                            <?php } ?>
                        <?php } ?>
					</div>
                </div>
        		<?php if($people_accountconfig['activateJobTitle'] > 0) { ?>
                    <div class="pageContentRow">
                        <div class="pageContentRowLabel"><?php echo $formText_JobTitle_Output;?></div>
                        <div class="pageContentRowText"><?php echo $peopleData['title'];?></div>
                    </div>
                <?php }
				if($people_accountconfig['activatePosition'] > 0) { ?>
				   <?php
				   $o_find = $o_main->db->query("SELECT * FROM people_position WHERE id = '".$o_main->db->escape_str($peopleData['position'])."'");
				   $v_position = $o_find ? $o_find->row_array() : array();
				   ?><div class="pageContentRow">
					   <div class="pageContentRowLabel"><?php echo $formText_Position_Output;?></div>
					   <div class="pageContentRowText"><?php echo $v_position['name'];?></div>
				   </div>
			   <?php } ?>
                <?php if($v_employee_basisconfig['activateDependantSection'] && $canEdit) {?>
                    <div class="pageContentRow">
                    </div>
                    <div class="pageContentRow">
                        <div class="pageContentRowLabel"><?php echo $formText_DependantName_Output;?></div>
                        <div class="pageContentRowText">
                            <?php echo $peopleData['dependants_name'];?>

                            <a style="margin-left: 15px;" class="link fw_text_link_color employeelist__editemployeeDependant"  data-cid="<?php echo $peopleData['id']; ?>" href="#"><span class="glyphicon glyphicon-pencil"></span></a>
                    </div>
                    </div>
                    <div class="pageContentRow">
                        <div class="pageContentRowLabel"><?php echo $formText_DependantPhone_Output;?></div>
                        <div class="pageContentRowText">
                            <?php echo $peopleData['dependants_phone'];?>
                        </div>
                    </div>
                <?php } ?>
                <?php
                if($peopleData['content_status'] == 0) {
	                if($accessElementAllow_GiveRemoveAccessPeople) {
                    ?>
                    <div class="output-access-loader" data-id="<?php echo $peopleData['id']?>" data-email="<?php echo $peopleData['email'];?>" data-membersystem-id="<?php echo $peopleData['id'];?>">
                        <div class="output-access-changer"><?php
                        if($b_registered_user)
                        {
                            $v_invitations = explode(",", $v_access['invitationsent']);
                            $v_access['invitationsent'] = '';
                            foreach($v_invitations as $s_invitation)
                            {
                                $v_access['invitationsent'] .= ($v_access['invitationsent']!=''?', ':'').date("d.m.Y", strtotime($s_invitation));
                            }
                            $s_icon = "green";
                            if($v_access['user_id'] == 0) $s_icon = "green_grey";
                            ?><img src="<?php echo $extradir."/output/elementsOutput/access_key_".$s_icon;?>.png" /><?php
                            ?>
                            <?php if(!$v_employee_accountconfig['duplicate_module']) {
                                if(!$personList || ($personList && !$v_employee_accountconfig['personTabHideGivingAccess'])) {
                                    ?>
                                    <div class="output-access-dropdown">
                                        <div class="script fw_text_link_color" onClick="javascript:output_access_remove(this,'<?php echo $peopleData['id'];?>');" data-delete-msg="<?php echo $formText_RemoveAccess_Output.": ".$peopleData['email'];?>?">
                                            <?php echo $formText_RemoveAccess_Output;?>
                                        </div>
                                        <?php /*?><div>
                                            <?php
                                            if($v_access['last_activity'] != "0000-00-00 00:00:00" && $v_access['last_activity'] != null)
                                                echo $formText_LastActivity_Output.": ".date("d.m.Y H:i", strtotime($v_access['last_activity']));
                                            if($v_access['firstlogin'] == "0000-00-00 00:00:00")
                                                echo $formText_NeverLoggedIn_Output;
                                            ?>
                                        </div><?php */?>
                                        <!-- <div><?php echo $formText_InvitationSent_Output.': '.$v_access['invitationsent'];?></div> -->
                                        <div class="script fw_text_link_color" onClick="javascript:output_access_grant(this,'<?php echo $peopleData['id'];?>');"><?php echo $formText_ResendInvitation_Output;?></div>

                                        <div class="script fw_text_link_color" onClick="javascript:output_access_grant_no_sending(this,'<?php echo $peopleData['id'];?>');"><?php echo $formText_EditAccess_Output;?></div>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                            <?php
                            if($v_access['accesslevel'] == 1){
                                ?>
                                <div class="accesslevel"><?php echo $formText_AccessAll_output;?></div>
                                <?php
                            } else if($v_access['accesslevel'] == 2) {
                                ?>
                                <div class="accesslevel"><?php echo $formText_SpecificAccess_output;?></div>
                                <?php
                            } else if($v_access['accesslevel'] == 0) {
                                ?>
                                <div class="accesslevel"><?php echo $formText_NoAccess_output;?></div>
                                <?php
                            } else if($v_access['accesslevel'] == 3){
                                ?>
                                <div class="accesslevel"><?php echo $formText_GroupAccess_output;?> - <?php echo $v_access['groupname'];?></div>
                                <?php
                            } else if($v_access['accesslevel'] == 4){
                                ?>
                                <div class="accesslevel"><?php echo $formText_MembershipAccess_output;?></div>
                                <?php
                            }
                        } else {
                            ?>
                            <?php if(!$v_employee_accountconfig['duplicate_module']) {
                                ?>
                                <img src="<?php echo $extradir;?>/output/elementsOutput/access_key_grey.png" />
                                <?php
                               if(!$personList || ($personList && !$v_employee_accountconfig['personTabHideGivingAccess'])) {?>
                                   <div class="output-access-dropdown"><div class="script fw_text_link_color" onClick="javascript:output_access_grant(this,'<?php echo $peopleData['id'];?>');"><?php echo $formText_GiveAccess_Output;?></div></div>
                            <?php }
                            }
                        }
                        ?>

                        </div>
                    </div>
                <?php }
                }?>
            </div>
			<?php if(!$people_accountconfig['hide_departments_in_people']) { ?>
                <div class="pageGroups">
                    <div class="pageGroupsTitle"><?php echo $formText_Departments_output;?><span class="edit_department_connection" data-employeeid="<?php echo $peopleData['id']?>"><?php echo $formText_EditDepartmentConnections_output;?></span></div>
    				<?php foreach($peopleData['departments'] as $group) {
                        $groupPageLink = "";
                        if($group['enable_page']){
                            $groupPageLink = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=GroupPage&folderfile=output&folder=output&inc_obj=details&cid=".$group['id'];
                        }
    					?>
    					<div class="pageGroupRow">
                            <span class="fas fa-users fw_icon_color"></span>&nbsp;
                            <?php if ($groupPageLink != "") { ?>
                                <a href="<?php echo $groupPageLink;?>" class="fw_text_link_color">
                            <?php } ?>
                                <?php echo $group['name'];?>
                            <?php if ($groupPageLink != "") { ?>
                                </a>
                            <?php } ?>
                        </div>
    					<?php
    				}?>
                </div>
            <?php } ?>
			<?php if(!$people_accountconfig['hide_groups_in_people']) { ?>
                <div class="pageGroups">
                    <div class="pageGroupsTitle"><?php echo $formText_Groups_output;?><span class="edit_group_connection" data-employeeid="<?php echo $peopleData['id']?>"><?php echo $formText_EditGroupConnections_output;?></span></div>
    				<?php foreach($peopleData['groups'] as $group) {
                        $groupPageLink = "";
                        if($group['enable_page']){
                            $groupPageLink = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=GroupPage&folderfile=output&folder=output&inc_obj=details&cid=".$group['id'];
                        }
    					?>
    					<div class="pageGroupRow">
                            <span class="fas fa-users fw_icon_color"></span>&nbsp;
                            <?php if ($groupPageLink != "") { ?>
                                <a href="<?php echo $groupPageLink;?>" class="fw_text_link_color">
                            <?php } ?>
                            <?php echo $group['name'];?>
                            <?php if ($groupPageLink != "") { ?>
                                </a>
                            <?php } ?>
                        </div>
    					<?php
    				}?>
                </div>
            <?php } ?>
            <?php if($variables->loggID ==$peopleData['email']) {?>
                <div class="pageContentRow">
                    <div class="pageContentRowLabel"><?php echo $formText_CompaniesThatCanEditYourProfile_output;?></div>
                    <div class="pageContentRowText">
                        <?php
                        echo $formText_CheckToGiveAccessForCompanyToEditYourProfile_output."<br/>";
                        $v_param = array(
                        );
                        $s_response = APIconnectorUser("companyaccessgetlist", $variables->loggID, $variables->sessionID, $v_param);
                        $v_response = json_decode($s_response, TRUE);
                        if(isset($v_response['status']) && $v_response['status'] == 1)
                        {
                            $companiesWithAccess = $v_response['data'];
                            $companiesShowed = array();
                            foreach($companiesWithAccess as $companyWithAccess){
                                if(!in_array($companyWithAccess['companyID'], $companiesShowed)){
                                    ?>
                                    <div class=""><input <?php if($companyWithAccess['hasAccessToEditProfile']) echo 'checked';?> type="checkbox" class="giveAccessToEdit" autocomplete="off" value="<?php echo $companyWithAccess['companyID'];?>" id="company_can_edit_<?php echo $companyWithAccess['companyID'];?>" /> <label for="company_can_edit_<?php echo $companyWithAccess['companyID'];?>"><?php echo $companyWithAccess['companyname']?></label></div>
                                    <?php
                                    $companiesShowed[] = $companyWithAccess['companyID'];
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
            <?php } ?>
            <?php
            $resources = array();
        	$s_sql = "SELECT * FROM people_selfdefined_fields WHERE content_status < 2 ORDER BY sortnr";
        	$o_query = $o_main->db->query($s_sql);
        	if($o_query && $o_query->num_rows()>0) {
        	    $resources = $o_query->result_array();
        	}
            ?>
            <br/>
            <div class="selfdefinedFields">
                <?php
                foreach($resources as $resource) {
                    $s_sql = "SELECT * FROM people_selfdefined_values WHERE people_id = ? AND selfdefined_fields_id = ?";

                    $o_query = $o_main->db->query($s_sql, array($peopleData['id'], $resource['id']));

                    if($o_query && $o_query->num_rows()>0){

                        $selfdefinedFieldValue = $o_query->row_array();

                    }
                    if($selfdefinedFieldValue != "" || $canEdit){
                    ?>
                        <div class="pageGroupRow"><b><?php echo $resource['name'];?>:</b>&nbsp; <?php echo $selfdefinedFieldValue['value'];?>
                            <?php if(!$v_employee_accountconfig['duplicate_module']) { ?> <?php if($canEdit) {?> <span class="glyphicon glyphicon-pencil fw_delete_edit_icon_color editSelfdefinedFieldValue" data-people-id="<?php echo $peopleData['id'];?>" data-selfdefinedfield-id="<?php echo $resource['id']?>"></span><?php } ?><?php } ?>
                        </div>
                        <?php
                    }
                }?>
            </div>
        </div>
        <div class="clear"></div>
    </div>


    <?php
    if($v_employee_basisconfig['activateFilesSection']) {
        function getFullFolderPathForFile($id, $o_main) {
            // File info
            $s_sql = "SELECT * FROM sys_filearchive_file WHERE id = ?";
            $o_query = $o_main->db->query($s_sql, array($id));
            if($o_query && $o_query->num_rows()>0){
                $fileData = $o_query->row_array();
            }
            // Path
            return getFullFolderPathForFolder($fileData['folder_id'], $o_main);
        }

        /**
         * Get full folder path for folder function
         */
        function getFullFolderPathForFolder($id, $o_main) {
            // Full path
            $fullPath = '';
            // Folder data
            $s_sql = "SELECT * FROM sys_filearchive_folder WHERE id = ?";
            $o_query = $o_main->db->query($s_sql, array($id));
            if($o_query && $o_query->num_rows()>0){
                $folderData = $o_query->row_array();
            }
            // Add folder name to path
            $fullPath = $folderData['name'];
            // If folder has parents
            if ($folderData['parent_id']) $fullPath = getFullFolderPathForFolder($folderData['parent_id'], $o_main) . ' / ' . $fullPath;
            // Return path
            return $fullPath;
        }
        $canViewFiles = false;
        if($canEditAdmin) {
            $canViewFiles = true;
        }
        $isEmployee = true;
        // $sql = "SELECT p.*
        //          FROM people p
        //         WHERE p.email = ?";
        // $o_query = $o_main->db->query($sql, array($variables->loggID));
        // $currentPeople = $o_query ? $o_query->row_array() : array();
        // if($currentPeople) {
        //     $isEmployee = true;
        // }

        $isCustomer = false;

        $s_sql = "SELECT * FROM people_files WHERE peopleId = ? AND content_status < 2 ORDER BY people_files.filename ASC";
        $o_result = $o_main->db->query($s_sql, array($cid));
        $files = $o_result ? $o_result->result() : array();
        $filesViewCount = 0;
        foreach($files AS $v_comment)
        {
            $canViewFile = $canViewFiles;
            if($v_comment->visible_for_customers && $isCustomer) {
                $canViewFile = true;
            }
            if($v_comment->visible_for_all_employee && $isEmployee) {
                $canViewFile = true;
            }
            if($v_comment->visible_for_current_employee && $isEmployee && $variables->loggID == $peopleData['email']) {
                $canViewFile = true;
            }
            if($canViewFile){
                $filesViewCount++;
            }
        }

		$o_query = $o_main->db->query("SELECT * FROM integration_signant_basisconfig ORDER BY id DESC");
		$v_signant_basisconfig = $o_query ? $o_query->row_array() : array();

		$o_query = $o_main->db->query("SELECT * FROM integration_signant_accountconfig ORDER BY id DESC");
		if($o_query && $o_query->num_rows() > 0)
		{
			$v_signant_accountconfig = $o_query->row_array();
			if(2 != $v_signant_accountconfig['set_open'])
			{
				$v_signant_basisconfig['set_open'] = $v_signant_accountconfig['set_open'];
			}
		}

		$b_activate_signant = (1 == $v_signant_basisconfig['set_open']);
		$s_signant_file = BASEPATH.'modules/IntegrationSignant/output/output_functions.php';
		if(is_file($s_signant_file)) include($s_signant_file);
		$v_sign_status = array(
			0 => $formText_NotSigned_Output,
			1 => $formText_PartlySigned_Output,
			2 => $formText_Signed_Output,
			3 => $formText_Canceled_Output,
			4 => $formText_Failure_Output,
			5 => $formText_Rejected_Output,
		);
        ?>

        <div class="p_contentBlock ">
            <div class="p_contentBlockTitle"><?php echo $formText_Files_Output;?> <?php if(!$v_employee_accountconfig['duplicate_module']) { ?> <?php if($canEditAdmin) { ?><button class="output-edit-files addEntryBtn" data-employeeid="<?php echo $peopleData['id'];?>"><?php echo $formText_Add_output;?></button><?php } ?><?php } ?></div>
            <div class="p_contentInner">
                <?php if($filesViewCount) { ?>
                    <div class="output-filelist">
                        <table class="table table-bordered">
                            <tr>
                                <td><?php echo $formText_Filename_output;?></td>
                                <?php if(!$v_employee_accountconfig['duplicate_module']) { ?>
                                    <?php if($canEditAdmin){ ?>
                                        <td><?php echo $formText_VisibleForCurrentEmployee_output?></td>
                                        <td><?php echo $formText_VisibleForAllEmployee_output?></td>
                                        <td><?php echo $formText_VisibleForCustomers_output?></td>
										<td><?php echo $formText_Signing_Output?></td>
                                        <td></td>
                                    <?php } ?>
                                <?php } ?>
                            </tr>
                            <?php

                            foreach($files AS $v_comment)
                            {
                                $canViewFile = $canViewFiles;
                                if($v_comment->visible_for_customers && $isCustomer) {
                                    $canViewFile = true;
                                }
                                if($v_comment->visible_for_all_employee && $isEmployee) {
                                    $canViewFile = true;
                                }
                                if($v_comment->visible_for_current_employee && $isEmployee && $variables->loggID == $peopleData['email']) {
                                    $canViewFile = true;
                                }
                                if($canViewFile){
                                    $fileInfo = json_decode($v_comment->file);
                                    $fileParts = explode('/',$fileInfo[0][1][0]);
                                    $fileName = array_pop($fileParts);
                                    $fileParts[] = rawurlencode($fileName);
                                    $filePath = implode('/',$fileParts);

                                    $fileUrl = $fileInfo[0][1][0];
                                    $fileName = $fileInfo[0][0];
                                    $fileUrl = "";
                                    if($v_employee_accountconfig['duplicate_module']) {
                                        $externalApiAccount = $v_employee_accountconfig['masteraccount_url'];
                                        if($externalApiAccount != ""){
                                            $hash = md5($externalApiAccount . '-' . $v_comment->id);
                                            $fileNameApi = "";
                                            foreach($fileParts as $filePart) {
                                                if($filePart != "uploads" && $filePart != "protected"){
                                                    $fileNameApi .= $filePart."/";
                                                }
                                            }
                                            $fileNameApi = trim($fileNameApi, "/");
                                            $fileAddition = "externalApiAccount=".$externalApiAccount."&externalApiHash=".$hash."&file=".$fileNameApi;
                                        }
                                    }
                                    if(strpos($fileInfo[0][1][0],'uploads/protected/')!==false)
                                    {
                                        $fileUrl = $extradomaindirroot.'/../'.$filePath.'?caID='.$_GET['caID'].'&table=people_files&field=file&ID='.$v_comment->id."&".$fileAddition;
                                    } else {
                                        $fileUrl = $extradomaindirroot.'/../'.$filePath."?".$fileAddition;
                                    }

                                    if(isset($v_comment->signant_id) && 0 < $v_comment->signant_id)
									{
										$s_sql = "SELECT * FROM integration_signant WHERE id = '".$o_main->db->escape_str($v_comment->signant_id)."'";
										$o_query = $o_main->db->query($s_sql);
										$v_signant = $o_query ? $o_query->row_array() : array();

										$s_file_field = 'file_original';
										$s_sql = "SELECT * FROM integration_signant_attachment WHERE signant_id = '".$o_main->db->escape_str($v_comment->signant_id)."'";
										$o_attachment = $o_main->db->query($s_sql);
										$v_attachment = $o_attachment ? $o_attachment->row_array() : array();
										if(1 == $v_signant['sign_status'] || 2 == $v_signant['sign_status'])
										{
											$s_file_field = 'file_signed';
										}
										$v_files = json_decode($v_attachment[$s_file_field], TRUE);
										$fileUrl = $variables->account_root_url.$v_files[0][1][0].'?caID='.$_GET['caID'].'&table=integration_signant_attachment&field='.$s_file_field.'&ID='.$v_attachment['id'];
									}
                                    ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo $fileUrl; ?>" class="fw_text_link_color" style="color: #333">
                                                <?php echo $fileName; ?>
                                            </a>
                                        </td>
                                        <?php if(!$v_employee_accountconfig['duplicate_module']) { ?>
                                            <?php if($canEditAdmin){ ?>
                                                <td><input type="checkbox" class="visibleForCurrentEmployee" value="<?php echo $v_comment->id?>" <?php if($v_comment->visible_for_current_employee) echo 'checked';?>/></td>
                                                <td><input type="checkbox" class="visibleForAllEmployee" value="<?php echo $v_comment->id?>" <?php if($v_comment->visible_for_all_employee) echo 'checked';?>/></td>
                                                <td><input type="checkbox" class="visibleForCustomers" value="<?php echo $v_comment->id?>" <?php if($v_comment->visible_for_customers) echo 'checked';?>/></td>
                                                <td>
                                                    <?php
													if(isset($v_comment->signant_id) && 0 < $v_comment->signant_id)
													{
														if(1 < $v_signant['sign_status'])
														{
															echo '<span class="hoverEye">'.$v_sign_status[$v_signant['sign_status']].integration_signant_get_status_details($v_signant['id']).'</span>';
														} else {
															?><div class="signant-status load" data-id="<?php echo $v_signant["id"];?>"><?php echo $formText_Checking_Output;?> <loading-dots>.</loading-dots></div><?php
														}
														if(1 == $accessElementAllow_SendFilesToSignant && 1 >= $v_signant['sign_status'])
														{
															?><a class="output-cancel-signant-document script" href="#" data-id="<?php echo $v_signant['id'];?>" data-cancel-msg="<?php echo $formText_CancelDocumentSigning_Output.': '.$v_signant['name'];?>?" title="<?php echo $formText_Cancel_Output;?>"><span class="glyphicon glyphicon-remove-circle"></span></a><?php
														}
													} else {
														if(1 == $accessElementAllow_SendFilesToSignant)
														{
															?>
															<a href="#" class="signCustomFile" data-id="<?php echo $v_comment->id; ?>">
																<?php echo $formText_SendForSigning_Output;?>
															</a>
															<?php
														}
													}
													?>
                                                </td>
												<td>
                                                    <a href="#" class="deleteCustomFile" data-deletefileid="<?php echo $v_comment->id; ?>" data-delete-msg="<?php echo ((isset($v_comment->signant_id) && 0 < $v_comment->signant_id) ? (1 >= $v_signant['sign_status'] ? $formText_SigningShouldBeCanceledBeforeDeletingFile_Output : $formText_ConfirmDeleteFile_Output.' ('.$formText_FileWillBeDeletedFromHereButSignedDocumentWillBeAvailableInSignantModule_Output) : $formText_ConfirmDeleteFile_Output);?>" data-disabled="<?php echo (isset($v_comment->signant_id) && 0 < $v_comment->signant_id && 1 >= $v_signant['sign_status'] ? 1 : 0);?>">
                                                        <span class="glyphicon glyphicon-trash"></span>
                                                    </a>
                                                </td>
                                            <?php } ?>
                                        <?php } ?>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                        </table>
                    </div/>
                <?php } ?>
            </div>
        </div>
    <?php
    }
    ?>
    <?php if($canEditAdmin && ($v_employee_basisconfig['activateAddressSection']
        || $v_employee_basisconfig['activateEmailSignature'] || $v_employee_basisconfig['activateHourlyBudgetSection']
        || $v_employee_basisconfig['activateSalarySection'] || $v_employee_basisconfig['activateEmployers']
        || $v_employee_basisconfig['activateCommentsSection'] || $v_employee_basisconfig['activateWorkIdCardSection'] || $v_employee_basisconfig['activateBankaccountSection']
        || ($v_employee_basisconfig['activateDependantSection'] && !$canEdit)
    )) { ?>
        <div class="p_contentBlock ">
            <div class="p_contentBlockTitle" style="margin-bottom: 0px;"><?php echo $formText_InformationBelowOnlyVisibleForAdministration_Output;?></div>
        </div>
    <?php } ?>
	<?php
    if($v_employee_basisconfig['activateEmployers'] && $canEditAdmin) {?>
        <div class="p_contentBlock different">
            <div class="p_contentBlockTitle">
                <span>
                    <?php echo $formText_EmployersAccountingId_Output;?>
                </span>
                <?php if(!$v_employee_accountconfig['duplicate_module']) { ?>
                    <?php if($canEdit || $canEditAdmin) {?>
                        <button class="output-edit-employers addEntryBtn" data-employeeid="<?php echo $cid; ?>"><?php echo $formText_Add_Output;?></button>
                    <?php } ?>
                <?php } ?>
            </div>
            <div class="p_contentInner">
                <?php
                $s_sql = "SELECT * FROM people_employerconnection WHERE peopleId = ?";
                $o_result = $o_main->db->query($s_sql, array($cid));
                $employersConnections = $o_result ? $o_result->result_array() : array();
                foreach($employersConnections as $employersConnection) {
                    $s_sql = "SELECT * FROM repeatingorder_employers WHERE content_status < 2 AND id = ? ORDER BY name ASC";
                    $o_query = $o_main->db->query($s_sql, array($employersConnection['employerId']));
                    $employer = ($o_query ? $o_query->row_array() : array());

                    ?>
                    <div class="employer-row">
                        <div class="employer-name"><?php echo $employer['name'];?></div>
                        <div class="employer-name"><?php echo $employersConnection['accountingEmployeeId'];?></div>
                        <div class="employer-action">
                            <?php if(!$v_employee_accountconfig['duplicate_module']) { ?>
                                <?php if($canEdit || $canEditAdmin) { ?>
                                    <span class="output-btn small glyphicon glyphicon-pencil fw_delete_edit_icon_color output-edit-employers" data-employerid="<?php echo $employer['id']; ?>" data-employeeid="<?php echo $cid;?>"></span>

                                    <span class="output-btn small glyphicon glyphicon-trash fw_delete_edit_icon_color output-delete-item"  data-url="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_employee_employers&cid=".$employersConnection['id'];?>" data-delete-msg="<?php echo $formText_DeleteEmployerConnection_Output;?>?"></span>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    <?php } ?>
    <?php if(!$v_employee_basisconfig['activateEmployers']) { ?>
        <?php if($people_accountconfig['activateEmployeeCode'] > 0  && $canEditAdmin) { ?>
            <div class="p_contentBlock different">
                <div class="p_contentBlockTitle">
                    <span>
                        <?php echo $formText_EmployeeAccountingId_Output;?>
                    </span>
                </div>
                <div class="p_contentInner">
                    <div class="employer-row">
                        <div class="employer-name"><?php echo $formText_EmployeeCode_Output;?></div>
                        <div class="employer-name"><?php echo $peopleData['external_employee_id'];?></div>
                        <div class="employer-action">
                            <?php if(!$v_employee_accountconfig['duplicate_module']) { ?>
                                <?php if($canEdit || $canEditAdmin) { ?>
                                    <span class="output-btn small glyphicon glyphicon-pencil fw_delete_edit_icon_color output-edit-employeecode" data-employeeid="<?php echo $cid;?>"></span>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    <?php } ?>
    <?php
    if($v_employee_basisconfig['activateSalarySection'] && $canEditAdmin) {
        ?>
        <div class="p_contentBlock">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="txt-label" valign="top"><?php echo $formText_Seniority_Output;?></td>
                    <td class="txt-value">
                        <?php
                        if($peopleData['seniority_salary'] == 0) {
                            echo $formText_NotActivated_output;
                        } else if($peopleData['seniority_salary'] == 1) {
                            echo $formText_AdjustAutomaticallyFromSeniorityDate_output." ";
                            if($peopleData['seniorityStartDate'] != "0000-00-00" && $peopleData['seniorityStartDate'] != null) echo date("d.m.Y", strtotime($peopleData['seniorityStartDate']));
                        } else if($peopleData['seniority_salary'] == 2) {
                            echo $formText_AdjustManually_output." ";
                            echo $peopleData['seniority_years']." ".$formText_Years_Output." ";
                            if($peopleData['seniority_reminder_consider_new_adjustment_from_date'] != "0000-00-00" && $peopleData['seniority_reminder_consider_new_adjustment_from_date'] != null)
                            echo " - ".$formText_NextAdjustmentMonth_output." ".date("m.Y", strtotime($peopleData['seniority_reminder_consider_new_adjustment_from_date']));
                            echo "<br/>".nl2br($peopleData['seniority_note']);
                        }
                        ?>
                        <?php if(!$v_employee_accountconfig['duplicate_module']) { ?>
                            <?php if($canEdit || $canEditAdmin) { ?>
                                <span class="btn-edit"><span class="output-btn small glyphicon glyphicon-pencil fw_delete_edit_icon_color employeelist__editemployeeSeniority " data-cid="<?php echo $peopleData['id']; ?>"></span></span>
                            <?php } ?>
                        <?php } ?>
                    </td>
                </tr>
            </table>
            <?php
            $workLeaderSql = "SELECT *, peoplesalary.* FROM peoplesalary JOIN contactperson ON contactperson.id = peoplesalary.peopleId
                WHERE peoplesalary.peopleId = ?";
            $o_result = $o_main->db->query($workLeaderSql, array($peopleData['id']));
            $people_salary = $o_result ? $o_result->row_array() : array();
            ?>
            <div class="p_contentBlockTitle">
                <span>
                    <?php echo $formText_Salary_Output;?>
                </span>
                <?php if(!$v_employee_accountconfig['duplicate_module']) { ?>
                    <?php if ($canEdit || $canEditAdmin) {?>
                        <select class="salarySourcePeople" autocomplete="off">
                            <option value="" <?php if(intval($people_salary['stdOrIndividualRate']) == 0 && intval($people_salary['standardwagerate_group_id']) == 0) echo 'selected';?>><?php echo $formText_Default_output;?></option>
                            <option value="0" <?php if($people_salary['stdOrIndividualRate'] == 0 && $people_salary['standardwagerate_group_id'] > 0) echo 'selected';?>><?php echo $formText_Specified_output;?></option>
                            <option value="1" <?php if($people_salary['stdOrIndividualRate'] == 1) echo 'selected';?>><?php echo $formText_Individual_output;?></option>
                            <option value="2" <?php if($people_salary['stdOrIndividualRate'] == 2) echo 'selected';?>><?php echo $formText_SendingInvoice_output;?></option>
                            <option value="3" <?php if($people_salary['stdOrIndividualRate'] == 3) echo 'selected';?>><?php echo $formText_FixedSalary_output;?></option>
                        </select>
                        <?php /*
                        <button class="output-edit-salary addEntryBtn" data-employeeid="<?php echo $cid; ?>"><?php echo $formText_Add_Output;?></button>
                        */?>
                    <?php } ?>
                <?php } ?>
            </div>
            <div class="p_contentInner">
                <?php

                $salary_list = get_salary_list($peopleData, $v_employee_accountconfig, $canEdit, $canEditAdmin);
                echo $salary_list['output'];
                ?>
            </div>

        </div>
        <?php
    }
    ?>
    <?php  if($v_employee_basisconfig['activateEmploymentSection'] && $canEditAdmin) {?>
            <div class="p_contentBlock ">
                <div class="p_contentBlockTitle"><?php echo $formText_Employment_Output;?> <?php if(!$v_employee_accountconfig['duplicate_module']) { ?> <?php if($canEditAdmin) { ?><button class="output-edit-employment addEntryBtn" data-employeeid="<?php echo $peopleData['id'];?>"><?php echo $formText_Add_output;?></button><?php } ?><?php } ?></div>
                <div class="p_contentInner">
                <table class="table">
                    <tr>
                        <td><?php echo $formText_StartDate_output;?></td>
                        <td><?php echo $formText_StoppedDate_output;?></td>
                        <td><?php echo $formText_JobTimePercent_output;?></td>
                        <td><?php echo $formText_ContractType_output;?></td>
                        <td><?php echo $formText_ContractFiles_output;?></td>
                        <td></td>
                    </tr>
                    <?php

                    $s_sql = "SELECT * FROM people_contract_types ORDER BY name ASC";
                    $o_query = $o_main->db->query($s_sql);
                    $contract_types = ($o_query ? $o_query->result_array() : array());
                    $s_sql = "SELECT people_employment.*, people_contract_types.name as contractTypeName FROM people_employment
                    LEFT OUTER JOIN people_contract_types ON people_contract_types.id = people_employment.contract_type_id WHERE people_employment.peopleId = ? ORDER BY people_employment.start_date DESC";
                    $o_result = $o_main->db->query($s_sql, array($cid));
                    $employments = $o_result ? $o_result->result_array() : array();
                    foreach($employments as $employment) {
                        ?>
                        <tr>
                            <td><?php if($employment['start_date'] != "" && $employment['start_date'] != "0000-00-00") echo date("d.m.Y", strtotime($employment['start_date']));?></td>
                            <td><?php if($employment['stopped_date'] != "" && $employment['stopped_date'] != "0000-00-00") echo date("d.m.Y", strtotime($employment['stopped_date']));?></td>
                            <td><?php echo $employment['job_time_percent'];?></td>
                            <td><?php echo $employment['contractTypeName'];?></td>
                            <td>
                                <?php
                                $contract_files = json_decode($employment['contract_file']);
                                foreach($contract_files as $contract_file){
                                    $fileParts = explode('/',$contract_file[1][0]);
                                    $fileName = array_pop($fileParts);
                                    $fileParts[] = rawurlencode($fileName);
                                    $filePath = implode('/',$fileParts);

                                    $fileUrl = $contract_file[1][0];
                                    $fileName = $contract_file[0];
                                    $fileUrl = "";
                                    $fileAddition = "";
                                    if($v_employee_accountconfig['duplicate_module']) {
                                        $externalApiAccount = $v_employee_accountconfig['masteraccount_url'];
                                        if($externalApiAccount != ""){
                                            $hash = md5($externalApiAccount . '-' . $employment['id']);
                                            $fileNameApi = "";
                                            foreach($fileParts as $filePart) {
                                                if($filePart != "uploads" && $filePart != "protected"){
                                                    $fileNameApi .= $filePart."/";
                                                }
                                            }
                                            $fileNameApi = trim($fileNameApi, "/");
                                            $fileAddition = "externalApiAccount=".$externalApiAccount."&externalApiHash=".$hash."&file=".$fileNameApi;
                                        }
                                    }



                                    if(strpos($contract_file[1][0],'uploads/protected/')!==false)
                                    {
                                        $fileUrl = $extradomaindirroot.'/../'.$filePath.'?caID='.$_GET['caID'].'&table=people_employment&field=contract_file&ID='.$employment['id']."&".$fileAddition;
                                    } else {
                                        $fileUrl = $extradomaindirroot.'/../'.$filePath."?".$fileAddition;
                                    }
                                    ?>
                                    <div>
                                        <a href="<?php echo $fileUrl; ?>" class="fw_text_link_color">
                                            <?php echo $fileName; ?>
                                        </a>
                                    </div>
                                    <?php
                                }
                                ?>
                            </td>
                            <td>
                                <?php if(!$v_employee_accountconfig['duplicate_module']) { ?>
                                    <?php if($canEdit || $canEditAdmin) { ?>
                                        <span class="output-btn small glyphicon glyphicon-pencil fw_delete_edit_icon_color output-edit-employment" data-id="<?php echo $employment['id']; ?>" ></span>

                                        <span class="output-btn small glyphicon glyphicon-trash fw_delete_edit_icon_color output-delete-item"  data-url="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_employment&cid=".$employment['id'];?>" data-delete-msg="<?php echo $formText_DeleteEmployment_Output;?>?"></span>
                                    <?php } ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
            </div>
        </div>
    <?php } ?>

    <?php  if($v_employee_basisconfig['activateCompetenceSection'] && $canEditAdmin) {?>
        <div class="p_contentBlock ">
            <div class="p_contentBlockTitle"><?php echo $formText_Competence_Output;?> <?php if(!$v_employee_accountconfig['duplicate_module']) { ?> <?php if($canEditAdmin) { ?><button class="output-edit-competence addEntryBtn" data-employeeid="<?php echo $peopleData['id'];?>"><?php echo $formText_Add_output;?></button><?php } ?><?php } ?></div>
            <div class="p_contentInner">
                <table class="table">
                    <tr>
                        <td><?php echo $formText_Name_output;?></td>
                        <td><?php echo $formText_Date_output;?></td>
                        <td><?php echo $formText_CompetenceFiles_output;?></td>
                        <td></td>
                    </tr>
                    <?php
                    $s_sql = "SELECT people_competence.*, pr.name as competenceName FROM people_competence JOIN people_competence_register pr ON pr.id = people_competence.competence_id WHERE people_competence.peopleId = ? ORDER BY people_competence.id DESC";
                    $o_result = $o_main->db->query($s_sql, array($cid));
                    $employments = $o_result ? $o_result->result_array() : array();
                    foreach($employments as $employment) {
                        ?>
                        <tr>
                            <td><?php echo $employment['competenceName'];?></td>
                            <td><?php if($employment['date'] != "" && $employment['date'] != "0000-00-00") echo date("d.m.Y", strtotime($employment['date']));?></td>
                            <td>
                                <?php
                                $contract_files = json_decode($employment['files']);
                                foreach($contract_files as $contract_file){
                                    $fileParts = explode('/',$contract_file[1][0]);
                                    $fileName = array_pop($fileParts);
                                    $fileParts[] = rawurlencode($fileName);
                                    $filePath = implode('/',$fileParts);

                                    $fileUrl = $contract_file[1][0];
                                    $fileName = $contract_file[0];
                                    $fileUrl = "";
                                    $fileAddition = "";
                                    if($v_employee_accountconfig['duplicate_module']) {
                                        $externalApiAccount = $v_employee_accountconfig['masteraccount_url'];
                                        if($externalApiAccount != ""){
                                            $hash = md5($externalApiAccount . '-' . $employment['id']);
                                            $fileNameApi = "";
                                            foreach($fileParts as $filePart) {
                                                if($filePart != "uploads" && $filePart != "protected"){
                                                    $fileNameApi .= $filePart."/";
                                                }
                                            }
                                            $fileNameApi = trim($fileNameApi, "/");
                                            $fileAddition = "externalApiAccount=".$externalApiAccount."&externalApiHash=".$hash."&file=".$fileNameApi;
                                        }
                                    }



                                    if(strpos($contract_file[1][0],'uploads/protected/')!==false)
                                    {
                                        $fileUrl = $extradomaindirroot.'/../'.$filePath.'?caID='.$_GET['caID'].'&table=people_competence&field=files&ID='.$employment['id']."&".$fileAddition;
                                    } else {
                                        $fileUrl = $extradomaindirroot.'/../'.$filePath."?".$fileAddition;
                                    }
                                    ?>
                                    <div>
                                        <a href="<?php echo $fileUrl; ?>" class="fw_text_link_color">
                                            <?php echo $fileName; ?>
                                        </a>
                                    </div>
                                    <?php
                                }
                                ?>
                            </td>
                            <td>
                                <?php if(!$v_employee_accountconfig['duplicate_module']) { ?>
                                    <?php if($canEdit || $canEditAdmin) { ?>
                                        <span class="output-btn small glyphicon glyphicon-pencil fw_delete_edit_icon_color output-edit-competence" data-id="<?php echo $employment['id']; ?>" ></span>

                                        <span class="output-btn small glyphicon glyphicon-trash fw_delete_edit_icon_color output-delete-item"  data-url="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_competence&cid=".$employment['id'];?>" data-delete-msg="<?php echo $formText_DeleteEmployment_Output;?>?"></span>
                                    <?php } ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
            </div>
        </div>
    <?php } ?>
    <?php  if($v_employee_basisconfig['activateFollowupSection'] && $canEditAdmin) {?>
        <div class="p_contentBlock ">
            <div class="p_contentBlockTitle"><?php echo $formText_Followup_Output;?> <?php if(!$v_employee_accountconfig['duplicate_module']) { ?> <?php if($canEditAdmin) { ?><button class="output-edit-followup addEntryBtn" data-employeeid="<?php echo $peopleData['id'];?>"><?php echo $formText_Add_output;?></button><?php } ?><?php } ?></div>
            <div class="p_contentInner">
                <table class="table">
                    <tr>
                        <td><?php echo $formText_Date_output;?></td>
                        <td><?php echo $formText_Subject_output;?></td>
                        <td><?php echo $formText_Text_output;?></td>
                        <td><?php echo $formText_FollowupFiles_output;?></td>
                        <td></td>
                    </tr>
                    <?php
                    $s_sql = "SELECT * FROM people_followup WHERE peopleId = ? ORDER BY date DESC";
                    $o_result = $o_main->db->query($s_sql, array($cid));
                    $employments = $o_result ? $o_result->result_array() : array();
                    foreach($employments as $employment) {
                        ?>
                        <tr>
                            <td><?php if($employment['date'] != "" && $employment['date'] != "0000-00-00") echo date("d.m.Y", strtotime($employment['date']));?></td>
                            <td><?php echo $employment['subject'];?></td>
                            <td><?php echo nl2br($employment['text']);?></td>
                            <td>
                                <?php
                                $contract_files = json_decode($employment['files']);
                                foreach($contract_files as $contract_file){
                                    $fileParts = explode('/',$contract_file[1][0]);
                                    $fileName = array_pop($fileParts);
                                    $fileParts[] = rawurlencode($fileName);
                                    $filePath = implode('/',$fileParts);

                                    $fileUrl = $contract_file[1][0];
                                    $fileName = $contract_file[0];
                                    $fileUrl = "";
                                    $fileAddition = "";
                                    if($v_employee_accountconfig['duplicate_module']) {
                                        $externalApiAccount = $v_employee_accountconfig['masteraccount_url'];
                                        if($externalApiAccount != ""){
                                            $hash = md5($externalApiAccount . '-' . $employment['id']);
                                            $fileNameApi = "";
                                            foreach($fileParts as $filePart) {
                                                if($filePart != "uploads" && $filePart != "protected"){
                                                    $fileNameApi .= $filePart."/";
                                                }
                                            }
                                            $fileNameApi = trim($fileNameApi, "/");
                                            $fileAddition = "externalApiAccount=".$externalApiAccount."&externalApiHash=".$hash."&file=".$fileNameApi;
                                        }
                                    }



                                    if(strpos($contract_file[1][0],'uploads/protected/')!==false)
                                    {
                                        $fileUrl = $extradomaindirroot.'/../'.$filePath.'?caID='.$_GET['caID'].'&table=people_followup&field=files&ID='.$employment['id']."&".$fileAddition;
                                    } else {
                                        $fileUrl = $extradomaindirroot.'/../'.$filePath."?".$fileAddition;
                                    }
                                    ?>
                                    <div>
                                        <a href="<?php echo $fileUrl; ?>" class="fw_text_link_color">
                                            <?php echo $fileName; ?>
                                        </a>
                                    </div>
                                    <?php
                                }
                                ?>
                            </td>
                            <td>
                                <?php if(!$v_employee_accountconfig['duplicate_module']) { ?>
                                    <?php if($canEdit || $canEditAdmin) { ?>
                                        <span class="output-btn small glyphicon glyphicon-pencil fw_delete_edit_icon_color output-edit-followup" data-id="<?php echo $employment['id']; ?>" ></span>

                                        <span class="output-btn small glyphicon glyphicon-trash fw_delete_edit_icon_color output-delete-item"  data-url="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_followup&cid=".$employment['id'];?>" data-delete-msg="<?php echo $formText_DeleteEmployment_Output;?>?"></span>
                                    <?php } ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
            </div>
        </div>
    <?php } ?>
    <?php  if($v_employee_basisconfig['activateDependantSection'] && $canEditAdmin && !$canEdit) {?>
        <div class="p_contentBlock">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="txt-label"><?php echo $formText_DependantName_Output;?></td>
                    <td class="txt-value"><?php echo $peopleData['dependants_name'];?></td>
                </tr>
                <tr>
                    <td class="txt-label"><?php echo $formText_DependantPhone_Output;?></td>
                    <td class="txt-value"><?php echo $peopleData['dependants_phone'];?></td>
                </tr>
                <tr>
                    <td class="btn-edit" colspan="2">
                        <?php if(!$v_employee_accountconfig['duplicate_module']) { ?>
                            <?php if($canEdit || $canEditAdmin) { ?>
                                <span class="output-btn small glyphicon glyphicon-pencil fw_delete_edit_icon_color employeelist__editemployeeDependant " data-cid="<?php echo $peopleData['id']; ?>"></span>
                            <?php } ?>
                        <?php } ?>
                    </td>
                </tr>
            </table>
        </div>
    <?php } ?>
    <?php  if($v_employee_basisconfig['activateWorkIdCardSection'] && $canEditAdmin) {?>
        <div class="p_contentBlock">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="txt-label"><?php echo $formText_HmsCardNumber_Output;?></td>
                    <td class="txt-value">
                        <?php
                        echo $peopleData['hms_card_number'];
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class="txt-label"><?php echo $formText_ExpireDate_Output;?></td>
                    <td class="txt-value">
                        <?php if($peopleData['workIdCardExpireDate'] != "" && $peopleData['workIdCardExpireDate'] != "0000-00-00"){
                        if(time() > strtotime($peopleData['workIdCardExpireDate'])) { echo '<span class="red">';}
                        echo date("d.m.Y", strtotime($peopleData['workIdCardExpireDate']));
                        if(time() > strtotime($peopleData['workIdCardExpireDate'])) { echo '</span>';}
                        }?>
                    </td>
                </tr>
                <?php if($accessElementAllow_EditPersonNumber){?>
                    <tr>
                        <td class="txt-label"><?php echo $formText_PersonNumber_Output;?></td>
                        <td class="txt-value"><?php echo $peopleData['personNumber'];?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td class="txt-label"><?php echo $formText_Birthdate_Output;?></td>
                    <td class="txt-value">
                        <?php if($peopleData['birthdate'] != "" && $peopleData['birthdate'] != "0000-00-00"){
                            echo date("d.m.Y", strtotime($peopleData['birthdate']));
                        }?>
                    </td>
                </tr>
                <tr>
                    <td class="txt-label"><?php echo $formText_Nationality_Output;?></td>
                    <td class="txt-value">
                        <?php
                        echo $peopleData['nationality'];
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class="txt-label"><?php echo $formText_Gender_Output;?></td>
                    <td class="txt-value">
                        <?php
                        if($peopleData['gender'] == 1){
                            echo $formText_Male_output;
                        } else if($peopleData['gender'] == 2){
                            echo $formText_Female_output;
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class="btn-edit" colspan="2">
                        <?php if(!$v_employee_accountconfig['duplicate_module']) { ?>
                            <?php if($canEditAdmin) { ?><span class="output-btn small glyphicon glyphicon-pencil fw_delete_edit_icon_color employeelist__editemployeeWorkIdCard " data-cid="<?php echo $peopleData['id']; ?>"></span><?php } ?>
                        <?php } ?>
                    </td>
                </tr>
            </table>
        </div>
    <?php } ?>
    <?php  if($v_employee_basisconfig['activateAddressSection'] && $canEditAdmin) {?>
        <div class="p_contentBlock">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="txt-label"><?php echo $formText_StreetAddress_Output;?></td>
                    <td class="txt-value"><?php echo $peopleData['streetadress'];?></td>
                </tr>
                <tr>
                    <td class="txt-label"><?php echo $formText_PostalNumber_Output;?></td>
                    <td class="txt-value"><?php echo $peopleData['postalnumber'];?></td>
                </tr>
                <tr>
                    <td class="txt-label"><?php echo $formText_City_Output;?></td>
                    <td class="txt-value"><?php echo $peopleData['city'];?></td>
                </tr>
                <tr>
                    <td class="btn-edit" colspan="2">
                        <?php if(!$v_employee_accountconfig['duplicate_module']) { ?>
                            <?php if($canEditAdmin) { ?>
                                <span class="output-btn small glyphicon glyphicon-pencil fw_delete_edit_icon_color employeelist__editemployeeAddress " data-cid="<?php echo $peopleData['id']; ?>"></span>
                            <?php } ?>
                        <?php } ?>
                    </td>
                </tr>
            </table>
        </div>
    <?php } ?>
    <?php
    if($v_employee_basisconfig['activateEmailSignature'] && $canEditAdmin) {
        if($canEdit || $canEditAdmin) {
            ?>
            <div class="p_contentBlock">
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="txt-label"><?php echo $formText_EmailSignature_Output;?></td>
                        <td class="txt-value"><?php echo nl2br($peopleData['emailSignature']);?></td>
                    </tr>
                    <tr>
                        <td class="txt-label"><?php echo $formText_EmailAddress_Output;?></td>
                        <td class="txt-value"><?php echo $peopleData['emailAddress'];?></td>
                    </tr>
                    <tr>
                        <td class="txt-label"><?php echo $formText_EmailPassword_Output;?></td>
                        <td class="txt-value"><?php echo $peopleData['emailPassword'];?></td>
                    </tr>
                    <tr>
                        <td class="txt-label"><?php echo $formText_EmailCalendarUrl_Output;?></td>
                        <td class="txt-value"><?php echo $peopleData['emailCalDavUrl'];?></td>
                    </tr>
                    <tr>
                        <td class="txt-label"><?php echo $formText_ActivateEmailCalendarSharing_Output;?></td>
                        <td class="txt-value"><input type="checkbox" disabled readonly <?php if($peopleData['emailCalendarActivateSharing']) echo 'checked';?>/></td>
                    </tr>
                    <tr>
                        <td class="btn-edit" colspan="2">
                            <?php if(!$v_employee_accountconfig['duplicate_module']) { ?>
                                <?php if($canEdit || $canEditAdmin) {?>
                                    <span class="output-btn small glyphicon glyphicon-pencil fw_delete_edit_icon_color employeelist__editemployeeEmailSettings " data-cid="<?php echo $peopleData['id']; ?>"></span>
                                <?php } ?>
                            <?php } ?>
                        </td>
                    </tr>
                </table>
            </div>
        <?php } ?>
    <?php } ?>
    <?php  if($v_employee_basisconfig['activateHourlyBudgetSection'] && $canEditAdmin) {?>
        <div class="p_contentBlock">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="txt-label"><?php echo $formText_HourlyBudgetCost_Output;?></td>
                    <td class="txt-value"><?php echo $peopleData['hourlyBudgetCost'];?></td>
                </tr>
                <tr>
                    <td class="btn-edit" colspan="2">
                        <?php if(!$v_employee_accountconfig['duplicate_module']) { ?>
                            <?php if($canEdit || $canEditAdmin) { ?>
                                <span class="output-btn small glyphicon glyphicon-pencil fw_delete_edit_icon_color employeelist__editemployeeBudgetSettings " data-cid="<?php echo $peopleData['id']; ?>"></span>
                            <?php } ?>
                        <?php } ?>
                    </td>
                </tr>
            </table>
        </div>
    <?php } ?>

    <?php  if($v_employee_basisconfig['activateBankaccountSection'] && $canEditAdmin) {?>
        <div class="p_contentBlock">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="txt-label"><?php echo $formText_BankAccountNumber_Output;?></td>
                    <td class="txt-value"><?php echo $peopleData['bankAccountNr'];?></td>
                </tr>
                <tr>
                    <td class="btn-edit" colspan="2">
                        <?php if(!$v_employee_accountconfig['duplicate_module']) { ?>
                            <?php if($canEdit || $canEditAdmin) { ?>
                                <span class="output-btn small glyphicon glyphicon-pencil fw_delete_edit_icon_color employeelist__editemployeeOtherInfo " data-cid="<?php echo $peopleData['id']; ?>"></span>
                            <?php } ?>
                        <?php } ?>
                    </td>
                </tr>
            </table>
        </div>
    <?php } ?>
    <?php
    if($v_employee_basisconfig['activateCommentsSection'] && $canEditAdmin) {
        ?>
        <div class="p_contentBlock different">
            <div class="p_contentBlockTitle"><?php echo $formText_Comments_Output;?>
                <?php if(!$v_employee_accountconfig['duplicate_module']) { ?>
                    <?php if($canEdit || $canEditAdmin) { ?><button class="output-edit-comment addEntryBtn"><?php echo $formText_Add_output;?></button><?php } ?>
                <?php } ?>
            </div>
            <div class="p_contentInner">
                <?php
                $s_sql = "SELECT * FROM people_comments WHERE peopleId = ? AND content_status = 0";
                $o_result = $o_main->db->query($s_sql, array($cid));
                if($o_result && $o_result->num_rows() > 0)
                foreach($o_result->result() AS $v_comment)
                {
                    ?><div class="output-comment">
                        <div>
                            <span class="createdBy">
                                <?php echo (!empty($v_comment->name) ? $v_comment->name : $v_comment->createdBy);  ?>
                            </span>
                            <span class="createdTime">
                                <?php echo date('d.m.Y H:i', strtotime($v_comment->created));?>
                            </span>
                            <?php if(!$v_employee_accountconfig['duplicate_module']) { ?>
                                <?php if($canEdit || $canEditAdmin) { ?>
                                    <span class="output-btn small glyphicon glyphicon-pencil fw_delete_edit_icon_color output-edit-comment" data-cid="<?php echo $v_comment->id;?>"></span>

                                    <span class="output-btn small glyphicon glyphicon-trash fw_delete_edit_icon_color output-delete-item" data-url="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_comment&cid=".$v_comment->id;?>" data-delete-msg="<?php echo $formText_DeleteComment_Output;?>?"></span>
                                <?php } ?>
                            <?php } ?>
                        </div>
                        <div class="commentText"><?php echo nl2br($v_comment->comment);?></div>
                    </div><?php
                }
                ?>
            </div>
        </div>
        <?php
    }
    ?>
</div>
<style>
.p_contentBlock  .hoverInit {
    display: inline-block;
    vertical-align: top;
    margin-left: 5px;
}
.p_contentBlock .hoverInit i {
    color: #bbb;
}
.p_contentBlock .hoverInit .hoverSpan {
    display: none;
    position: absolute;
    padding: 5px 10px;
    background: #fff;
    border: 1px solid #cecece;
    max-width: 300px;
    z-index: 10;
    color: #3B3C4E;
    font-weight: normal;
}
.p_contentBlock .hoverInit:hover .hoverSpan {
    display: block;
}

.pageTopImageWrapper {
    position: relative;
}
.moduleSinglePage .pageTopImage {
    background: #667573;
    width: 100%;
    position: relative;
	height: 200px;
    overflow: hidden;
}
body.mobile .moduleSinglePage .pageTopImage {
    height: auto;
	max-height: 200px;
    min-height: 90px;
}
.pageTopImageWrapper .pageTopImage img {
  position: relative;
  width: 100%;
}
.pageTopImageWrapper .editPageCoverBtn {
    display: none;
    position: absolute;
    bottom: 10px;
    right: 10px;
    background: rgba(0,0,0, 0.3);
    color: #fff;
    font-weight: bold;
    padding: 7px 10px;
    cursor: pointer;
    z-index: 1;
    border-radius: 4px;
}
.pageTopImageWrapper:hover .editPageCoverBtn {
    display: block;
}
.pageTopImageWrapper .editPageCoverBtn.visible {
    display: block;
}
.pageTopImageWrapper .editPageCoverBtn img {
    width: 20px !important;
    height: auto !important;
    top: 0  !important;
    left: 0 !important;
    margin-right: 5px;
    vertical-align: middle;
}

.moduleSinglePage .pageBottomContentLeft {
    float: left;
    width: 290px;
}
.moduleSinglePage .pageBottomContentLeft .pageBottomContentLeftWrapper {
    margin: -100px 35px 0px 35px;
    width: 220px;
}
.moduleSinglePage .pageBottomContentLeft .pageBottomContentLeftWrapper .profileImageWrapper {
    position: relative;
    border: 1px solid #fff;
    z-index: 10;
    margin-bottom: 20px;
    width: 220px;
    height: 220px;
}
.moduleSinglePage .pageBottomContentLeft .pageBottomContentLeftWrapper .profileImage {
    width: 218px;
    height: 218px;
    background: #667573;
    overflow: hidden;
    position: relative;
}
.moduleSinglePage .pageBottomContentLeft .pageBottomContentLeftWrapper .profileImage img {
    width: 100%;
    height: auto;
	position: absolute;
  	left: 50%;
  	top: 50%;
  	transform: translate(-50%, -50%);
}
.moduleSinglePage .pageBottomContentLeft .pageBottomContentLeftWrapper .profileImageWrapper .editProfileImageBtn,
.moduleSinglePage .pageBottomContentLeft .pageBottomContentLeftWrapper .profileImageWrapper .deleteProfileImageBtn {
    display: none;
    position: absolute;
    bottom: 10px;
    right: 10px;
    background: rgba(0,0,0, 0.3);
    color: #fff;
    font-weight: bold;
    padding: 7px 10px;
    cursor: pointer;
    z-index: 1;
    border-radius: 4px;
}
.moduleSinglePage .pageBottomContentLeft .pageBottomContentLeftWrapper .profileImageWrapper .deleteProfileImageBtn {
	bottom: 50px;
}
.moduleSinglePage .pageBottomContentLeft .pageBottomContentLeftWrapper .profileImageWrapper:hover .editProfileImageBtn,
.moduleSinglePage .pageBottomContentLeft .pageBottomContentLeftWrapper .profileImageWrapper:hover .deleteProfileImageBtn,
.moduleSinglePage .pageBottomContentLeft .pageBottomContentLeftWrapper .profileImageWrapper .editProfileImageBtn.visible {
    display: block;
}
.moduleSinglePage .pageBottomContentLeft .pageBottomContentLeftWrapper .profileImageWrapper .editProfileImageBtn img {
    width: 20px !important;
    height: auto !important;
    top: 0  !important;
    left: 0 !important;
    margin-right: 5px;
    vertical-align: middle;
}

.moduleSinglePage .pageBottomContentRight {
    float: right;
    width: calc(100% - 290px);
}
.moduleSinglePage .pageBottomContentWrapper {
    background: #fff;
    padding-bottom: 40px;
}
.moduleSinglePage .pageBottomContentRight .pageTitle {
    font-size: 22px;
    font-weight: bold;
    margin-top: 40px;
    margin-bottom: 30px;
}
.moduleSinglePage .pageBottomContentRight .pageTitle  .editBtn {
    display: inline-block;
    vertical-align: middle;
    font-size: 12px;
    font-weight: normal;
    margin-left: 15px;
    cursor: pointer;
    color: #0284C9;
    text-decoration: underline;
}
.moduleSinglePage .pageBottomContentRight .pageContent {
    font-size: 14px;
    line-height: 18px;
    margin-bottom: 30px;
}
.moduleSinglePage .pageBottomContentRight .pageContent .pageContentRowLabel {
    display: inline-block;
    vertical-align: middle;
    width: 130px;
}
.moduleSinglePage .pageBottomContentRight .pageContent .pageContentRowText {
    display: inline-block;
    vertical-align: middle;
}
.moduleSinglePage .profileButton {
    background: #667573;
    border-radius: 3px;
    color: #fff;
    padding: 7px 10px;
    text-align: center;
    cursor: pointer;
    font-weight: bold;
    font-size: 14px;
}
.moduleSinglePage .pageGroupsTitle {
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 10px;
}
.moduleSinglePage .pageGroupRow {
    font-size: 14px;
    margin-bottom: 5px;
}
.moduleSinglePage .pageGroupRow img {
    width: 20px;
    margin-right: 5px;
}
.clear {
    clear: both;
}
.editSelfdefinedFieldValue {
    cursor: pointer;
    margin-left: 20px;
}
body.mobile .moduleSinglePage .pageBottomContentLeft .pageBottomContentLeftWrapper {
    margin-top: -40px;
}
.salarySourcePeople {
    font-size: 13px;
    font-weight: normal;
    margin-left: 20px;
}
.output-access-loader {
    margin-top: 20px;
}
.edit_group_connection {
    margin-left: 10px;
    cursor: pointer;
    color: #46b2e2;
    font-weight: normal;
    font-size: 12px;
}
.edit_department_connection {
    margin-left: 10px;
    cursor: pointer;
    color: #46b2e2;
    font-weight: normal;
    font-size: 12px;
}
.article-loading.lds-ring {
  display: inline-block;
  position: relative;
  width: 24px;
  height: 24px;
  margin: 10px 20px;
}
.article-loading.lds-ring div {
  box-sizing: border-box;
  display: block;
  position: absolute;
  width: 22px;
  height: 22px;
  margin: 3px;
  border: 3px solid #46b2e2;
  border-radius: 50%;
  animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
  border-color: #46b2e2 transparent transparent transparent;
}
.article-loading.lds-ring div:nth-child(1) {
  animation-delay: -0.45s;
}
.article-loading.lds-ring div:nth-child(2) {
  animation-delay: -0.3s;
}
.article-loading.lds-ring div:nth-child(3) {
  animation-delay: -0.15s;
}
@keyframes lds-ring {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
.moduleSinglePage .searchBlock {
    margin-top: 20px;
    margin-right: 20px;
}
.moduleSinglePage .employeeSearch {
    float: right;
    position: relative;
    margin-bottom: 0;
}
.moduleSinglePage .employeeSearch .employeeSearchSuggestions {
    display: none;
    background: #fff;
    position: absolute;
    width: 100%;
    max-height: 200px;
    overflow: auto;
    z-index: 2;
    border: 1px solid #dedede;
    border-top: 0;
}
.moduleSinglePage .employeeSearch .employeeSearchSuggestions table {
    margin-bottom: 0;
}
#p_container .moduleSinglePage .employeeSearch .employeeSearchSuggestions td {
    padding: 5px 10px;
}

.moduleSinglePage .employeeSearch .glyphicon-triangle-right {
    position: absolute;
    top: 7px;
    right: 4px;
    color: #048fcf;
}
.moduleSinglePage .employeeSearch .glyphicon-search {
    position: absolute;
    top: 7px;
    left: 6px;
    color: #048fcf;
}
.moduleSinglePage .employeeSearchInput {
    width: 250px;
    border: 1px solid #dedede;
    padding: 3px 15px 3px 25px;
}
.moduleSinglePage .employeeSearchInputBefore {
    width: 150px;
    border: 1px solid #dedede;
    padding: 3px 10px 3px 10px;
}
.moduleSinglePage .employeeSearchBtn {
    background: #0093e7;
    border-radius: 5px;
    margin-left: 3px;
    color: #fff;
    padding: 5px 15px;
    cursor: pointer;
    border: 0;
}
.moduleSinglePage .prev-link {
    float: right;
    padding: 3px 10px;
}
.moduleSinglePage .next-link {
    float: right;
    padding: 3px 10px;
}
.historyList {
	margin-top: 7px; margin-left: 5px;margin-right: 5px;
    text-align: left;
    padding-left: 30px;
}
.historyList .hoverInfo {
	padding: 0px 0px;
}
.historyListItem {
	font-weight: normal;
	padding: 3px 0px;
    text-align: left;
}
.historyList .historyListTitle {
    font-weight: bold;
    text-align: left;
	padding: 10px 10px 10px 10px;
}
.historyList .gtable_cell {
	border: 0;
	border-top: 1px solid #efecec;
    font-weight: normal;
}
.historyList .timeColor {
	color: #999999 !important;
}
.hoverEye {
	position: relative;
	color: #0284C9;
	float: right;
	margin-top: 5px;
}
.hoverEye .hoverInfo {
	font-family: 'PT Sans', sans-serif;
	width:450px;
	display: none;
	color: #000;
	position: absolute;
	right: 0%;
	top: 100%;
	padding: 5px 10px;
	background: #fff;
	border: 1px solid #ccc;
	z-index: 1;
}
.hoverEye:hover .hoverInfo {
	display: block;
}
.hoverLabel {
	margin-bottom: 10px;
	font-weight: bold;
}
.hoverEyeCreated {
	position: relative;
	color: #cecece;
	float: left;
	margin-top: 2px;
}
.hoverEyeCreated.customerAddress {
	position: relative;
	color: #cecece;
	float: right;
	margin-top: 2px;
}
.hoverEyeCreated .hoverInfo {
	font-family: 'PT Sans', sans-serif;
	width:250px;
	display: none;
	color: #000;
	position: absolute;
	left: 0%;
	top: 100%;
	padding: 5px 10px;
	background: #fff;
	border: 1px solid #ccc;
	z-index: 1;
}
.hoverEyeCreated.show-right-over .hoverInfo {
	width:350px;
	left:auto;
	right:0;
	top:0;
	padding:20px 20px;
}
.hoverEyeCreated.show-right-over .hoverInfo div {
	padding:5px 0;
}
.hoverEyeCreated:hover .hoverInfo {
	display: block;
}
</style>
<script type="text/javascript">
var out_popup;
var out_popup_options={
	follow: [true, true],
	followSpeed: 0,
	fadeSpeed: 0,
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function(){
		$(this).addClass('opened');
		//$(this).find('.b-close').on('click', function(){out_popup.close();});
	},
	onClose: function(){
        if($(this).hasClass("close-reload")){
            loadView("details", {cid: "<?php echo $cid;?>"});
        }
		$(this).removeClass('opened');
	}
};
var loadingCustomer = false;
var $input = $('.employeeSearchInput');
var customer_search_value;
$input.on('focusin', function () {
    searchCustomerSuggestions();
    $("#p_container").unbind("click").bind("click", function (ev) {
        if($(ev.target).parents(".employeeSearch").length == 0){
            $(".employeeSearchSuggestions").hide();
        }
    });
})
//on keyup, start the countdown
$input.on('keyup', function () {
    searchCustomerSuggestions();
});
//on keydown, clear the countdown
$input.on('keydown', function () {
    searchCustomerSuggestions();
});
function searchCustomerSuggestions (){
    if(!loadingCustomer) {
        if(customer_search_value != $(".employeeSearchInput").val()) {
            loadingCustomer = true;
            customer_search_value = $(".employeeSearchInput").val();
            $('.employeeSearch .employeeSearchSuggestions').html('<div class="article-loading lds-ring"><div></div><div></div><div></div><div></div></div>').show();
            var _data = { fwajax: 1, fw_nocss: 1, search: customer_search_value, detailpage: 1};
            $.ajax({
                cache: false,
                type: 'POST',
                dataType: 'json',
                url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_people_suggestions";?>',
                data: _data,
                success: function(obj){
                    loadingCustomer = false;
                    $('.employeeSearch .employeeSearchSuggestions').html('');
                    $('.employeeSearch .employeeSearchSuggestions').html(obj.html).show();
                    searchCustomerSuggestions();
                }
            }).fail(function(){
                loadingCustomer = false;
            })
        }
    }
}

$(".moduleSinglePage").off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
    if(e.target.nodeName == 'DIV' || e.target.nodeName == 'TD'){
        fw_load_ajax($(this).data('href'),'',true);
        if($("body.alternative").length == 0) {
            var $scrollbar6 = $('.tinyScrollbar.col1');
            $scrollbar6.tinyscrollbar();

            var scrollbar6 = $scrollbar6.data("plugin_tinyscrollbar");
            scrollbar6.update(0);
        }
    }
});
$(".sendMessage").on('click', function(){
    if(fwchat != undefined){
        fwchat.showChat('<?php echo $currentMember['user_id'];?>');
    }
})
$(".changePassword").on('click', function(e){
    e.preventDefault();
    var data = {
        cid: '<?php echo $cid;?>'
    };
	ajaxCall('change_password', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$(".editProfileImageBtn").on('click', function(e){
    e.preventDefault();
    var data = {
        cid: '<?php echo $cid;?>'
    };
    ajaxCall('editProfileImage', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$('.deleteProfileImageBtn').on('click', function(e){
    e.preventDefault();
    var data = {
        cid: '<?php echo $cid;?>'
    };
    ajaxCall('deleteProfileImage', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$(".editPageCoverBtn").on('click', function(e){
    e.preventDefault();
    var data = {
        cid: '<?php echo $cid;?>'
    };
    ajaxCall('editPageCover', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$(".overlayEditProfileBtn").unbind("click").bind("click", function(e){
    e.preventDefault();
    var data = {
        cid: '<?php echo $cid;?>'
    };
    ajaxCall('editPeople', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
})
$(".overlayDeleteProfileBtn").unbind("click").bind("click", function(e){
    e.preventDefault();
    var data = {
        cid: '<?php echo $cid;?>'
    };
    ajaxCall('deletePeople', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
})
$(".overlayDeactivateProfileBtn").unbind("click").bind("click", function(e){
    e.preventDefault();
    var data = {
        cid: '<?php echo $cid;?>'
    };
    ajaxCall('deactivatePeople', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
})
$('.overlayActivateProfileBtn').on('click', function(e){
    e.preventDefault();
    var data = {
        cid: '<?php echo $cid;?>'
    };
    ajaxCall('activatePeople', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$('.overlay-verify-mobile').off('click').on('click', function(e){
    e.preventDefault();
    var data = {
		cid: '<?php echo $cid;?>',
		mobile: $(this).data('mobile'),
		mobile_prefix: $(this).data('mobile-prefix')
    };
    ajaxCall('verify_mobile', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$(".editSelfdefinedFieldValue").unbind("click").bind("click", function(e){
    e.preventDefault();
    var data = {
        cid: '<?php echo $cid;?>',
        selfdefinedfield_id:  $(this).data("selfdefinedfield-id")
    };
    ajaxCall('editSelfdefinedFieldValue', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
})

$('.employeelist__editemployeeAddress').on('click', function(event) {
    event.preventDefault();
    /* Act on the event */
    if ($(this).data('cid')) {
        var data = {
            cid: $(this).data('cid')
        };
    }
    else {
        var data = {};
    }
    ajaxCall('edit_employee_address', data, function(obj) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(obj.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$('.employeelist__editemployeeSeniority').on('click', function(event) {
    event.preventDefault();
    /* Act on the event */
    if ($(this).data('cid')) {
        var data = {
            cid: $(this).data('cid')
        };
    }
    else {
        var data = {};
    }
    ajaxCall('edit_employee_seniority', data, function(obj) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(obj.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});

$('.employeelist__editemployeeWorkIdCard').on('click', function(event) {
    event.preventDefault();
    /* Act on the event */
    if ($(this).data('cid')) {
        var data = {
            cid: $(this).data('cid')
        };
    }
    else {
        var data = {};
    }
    ajaxCall('edit_employee_workidcard', data, function(obj) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(obj.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$('.employeelist__editemployeeDependant').on('click', function(event) {
    event.preventDefault();
    /* Act on the event */
    if ($(this).data('cid')) {
        var data = {
            cid: $(this).data('cid')
        };
    }
    else {
        var data = {};
    }
    ajaxCall('edit_employee_dependant', data, function(obj) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(obj.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});

$('.employeelist__editemployeeEmailSettings').on('click', function(event) {
    event.preventDefault();
    /* Act on the event */
    if ($(this).data('cid')) {
        var data = {
            cid: $(this).data('cid')
        };
    }
    else {
        var data = {};
    }
    ajaxCall('edit_employee_emailinfo', data, function(obj) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(obj.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$('.employeelist__editemployeeBudgetSettings').on('click', function(event) {
    event.preventDefault();
    /* Act on the event */
    if ($(this).data('cid')) {
        var data = {
            cid: $(this).data('cid')
        };
    }
    else {
        var data = {};
    }
    ajaxCall('edit_employee_budgetinfo', data, function(obj) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(obj.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$('.employeelist__editemployeeOtherInfo').on('click', function(event) {
    event.preventDefault();
    /* Act on the event */
    if ($(this).data('cid')) {
        var data = {
            cid: $(this).data('cid')
        };
    }
    else {
        var data = {};
    }
    ajaxCall('edit_employee_info', data, function(obj) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(obj.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$(".output-edit-employment").off("click").on("click", function(){
    var employmentid = $(this).data("id");
    var data = { fwajax: 1, fw_nocss: 1, peopleId:'<?php echo $cid;?>', cid: employmentid};
    ajaxCall('edit_employment', data, function(obj) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(obj.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
})
$(".output-edit-competence").off("click").on("click", function(){
    var employmentid = $(this).data("id");
    var data = { fwajax: 1, fw_nocss: 1, peopleId:'<?php echo $cid;?>', cid: employmentid};
    ajaxCall('edit_competence', data, function(obj) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(obj.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
})
$(".output-edit-followup").off("click").on("click", function(){
    var employmentid = $(this).data("id");
    var data = { fwajax: 1, fw_nocss: 1, peopleId:'<?php echo $cid;?>', cid: employmentid};
    ajaxCall('edit_followup', data, function(obj) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(obj.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
})
$(".output-edit-salary").on('click', function(e){
    var salaryid = $(this).data("id");
    var employeeid = $(this).data("employeeid");
    if(employeeid > 0){
        var data = { fwajax: 1, fw_nocss: 1, employeeid:'<?php echo $cid;?>', salaryid: salaryid};
        ajaxCall('edit_salary', data, function(obj) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(obj.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    }
});
$(".output-edit-salary-group").on('click', function(e){
    var salarygroupid = $(this).data("id");
    var employeeid = $(this).data("employeeid");
    if(employeeid > 0){
        var data = { fwajax: 1, fw_nocss: 1, salarygroupid: salarygroupid, employeeid: employeeid};
        ajaxCall('edit_salary_group', data, function(obj) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(obj.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    }
});
$(".output-edit-comment").on('click', function(e){
    var cid = $(this).data('cid');

    if(cid === undefined) cid = 0;
    $.ajax({
        cache: false,
        type: 'POST',
        dataType: 'json',
        url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_comment";?>',
        data: { fwajax: 1, fw_nocss: 1, employeeId: "<?php echo $cid;?>", cid: cid},
        success: function(obj){
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(obj.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        }
    });
});
$('.output-delete-item').on('click',function(e){
    e.preventDefault();
    if(!fw_click_instance)
    {
        fw_click_instance = true;
        var $_this = $(this);
        bootbox.confirm({
            message:$_this.attr("data-delete-msg"),
            buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
            callback: function(result){
                if(result)
                {
                    $.ajax({
                        cache: false,
                        type: 'POST',
                        dataType: 'json',
                        data: {fwajax: 1, fw_nocss: 1, employeeId: '<?php echo $cid;?>', output_delete: 1},
                        url: $_this.data('url'),
                        success: function(data){
                            if(data.error !== undefined)
                            {
                                fw_info_message_empty();
                                $.each(data.error, function(index, value){
                                    var _type = Array("error");
                                    if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
                                    fw_info_message_add(_type[0], value);
                                });
                                fw_info_message_show();
                                fw_loading_end();
                            } else {
                                fw_load_ajax(data.redirect_url,'',true);
                            }
                        }
                    });
                }
                fw_click_instance = false;
            }
        });
    }
});
$(".edit_department_connection").off("click").on("click", function(e){
    e.preventDefault();
    var data = {
        contactperson_id: $(this).data('employeeid'),
        department: 1
    };
    ajaxCall('edit_group_connections', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
})
$(".edit_group_connection").off("click").on("click", function(e){
    e.preventDefault();
    var data = {
        contactperson_id: $(this).data('employeeid')
    };
    ajaxCall('edit_group_connections', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
})
$(".visibleForCurrentEmployee").unbind("click").bind("click", function(e){
    var value = $(this).val();
    var checked = 0;
    if($(this).is(":checked")){
        checked = 1
    }
    var data = {
        fileid: value,
        checked: checked,
        action: "visibleForCurrentEmployeeCheck"
    };
    ajaxCall('add_people_files', data, function(json) {

    });
})
$(".visibleForAllEmployee").unbind("click").bind("click", function(e){
    var value = $(this).val();
    var checked = 0;
    if($(this).is(":checked")){
        checked = 1
    }
    var data = {
        fileid: value,
        checked: checked,
        action: "visibleForAllEmployeeCheck"
    };
    ajaxCall('add_people_files', data, function(json) {

    });
})
$(".visibleForCustomers").unbind("click").bind("click", function(e){
    var value = $(this).val();
    var checked = 0;
    if($(this).is(":checked")){
        checked = 1
    }
    var data = {
        fileid: value,
        checked: checked,
        action: "visibleForCustomersCheck"
    };
    ajaxCall('add_people_files', data, function(json) {

    });
})
$(".default_salary_repeatingorder").unbind("click").bind("click", function(e){
    var value = $(this).val();
    var checked = 0;
    if($(this).is(":checked")){
        checked = 1
    }
    var data = {
        value: value,
        checked: checked,
        employeeid: '<?php echo $cid?>',
        action: "defaultSalaryRepeatingorder"
    };
    ajaxCall('edit_salary', data, function(json) {
        loadView("details", {cid: "<?php echo $cid;?>"});
    });
})
$(".default_salary_project").unbind("click").bind("click", function(e){
    var value = $(this).val();
    var checked = 0;
    if($(this).is(":checked")){
        checked = 1
    }
    var data = {
        value: value,
        checked: checked,
        employeeid: '<?php echo $cid?>',
        action: "defaultSalaryProject"
    };
    ajaxCall('edit_salary', data, function(json) {
        loadView("details", {cid: "<?php echo $cid;?>"});
    });
})
function output_delete_file(cid, deletefileid)
{
    fw_loading_start();
    if(cid === undefined) cid = 0;
    $.ajax({
        cache: false,
        type: 'POST',
        dataType: 'json',
        url: '<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=save_files";?>',
        data: { fwajax: 1, fw_nocss: 1, cid: cid, fileuploadaction: 'delete', deletefileid: deletefileid },
        success: function(obj){
            fw_loading_end();
            loadView("details", {cid: "<?php echo $cid;?>"});
        }
    });
}
$('.output-filelist li .deleteFile').on('click', function(e) {
    e.preventDefault();
    var self = $(this);
    bootbox.confirm("<?php echo $formText_ConfirmDeleteFile; ?>: " + self.closest('li').find('a').first().text(), function(result) {
      if(result) {
            output_delete_file('<?php echo ($cid); ?>', self.data('deletefileid'));
        }
    });
});
$('.deleteCustomFile').on('click', function(e) {
    e.preventDefault();
    var self = $(this);
    if(1 == self.data('disabled'))
	{
		bootbox.alert(self.data('delete-msg'), function(result) {});
	} else {
		bootbox.confirm(self.data('delete-msg'), function(result) {
		  if(result) {
			  fw_loading_start();
			  $.ajax({
				  cache: false,
				  type: 'POST',
				  dataType: 'json',
				  url: '<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=add_people_files";?>',
				  data: { fwajax: 1, fw_nocss: 1, cid: <?php echo ($cid); ?>, fileuploadaction: 'delete', deletefileid: self.data('deletefileid') },
				  success: function(obj){
					  fw_loading_end();
                      loadView("details", {cid: "<?php echo $cid;?>"});
				  }
			  });
			}
		});
	}
});
$('.signCustomFile').off('click').on('click', function(e) {
	e.preventDefault();
	var data = {
        cid: $(this).data('id')
    };
	ajaxCall('send_to_signant', data, function(json) {
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});

$('.output-add-file').on('click', function(e) {
    e.preventDefault();
    var data = {
        cid: $(this).data('employeeid')
    };
    ajaxCall('addCustomerFiles', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
$('.output-edit-files').on('click', function(e) {
    e.preventDefault();
    var data = {
        cid: $(this).data('employeeid')
    };
    ajaxCall('add_people_files', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});

$(".output-edit-employers").on('click', function(e){
    var employerId = $(this).data("employerid");
    var employeeId = $(this).data("employeeid");
    if(employeeId > 0){
        var data = { fwajax: 1, fw_nocss: 1, employerId: employerId, employeeId: employeeId};
        ajaxCall('edit_employee_employers', data, function(obj) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(obj.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    }
});
$(".output-edit-employeecode").on('click', function(e){
    var employeeId = $(this).data("employeeid");
    if(employeeId > 0){
        var data = { fwajax: 1, fw_nocss: 1, cid: employeeId};
        ajaxCall('edit_employee_code', data, function(obj) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(obj.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    }
});
$(".giveAccessToEdit").off("click").on("click", function(){
    var checked = 0;
    if($(this).is(":checked")){
        checked = 1
    }
    var data = { fwajax: 1, fw_nocss: 1, companyID: $(this).val(), checked: checked};
    ajaxCall('giveAccessToEdit', data, function(obj) {
        if(obj.html != ""){
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(obj.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            out_popup.addClass("close-reload");
            $("#popupeditbox:not(.opened)").remove();
        } else {
            loadView("details", {cid: "<?php echo $cid;?>"});
        }
    });
})
function output_access_grant(_this, id)
{
    fw_loading_start();
    $(_this).closest(".output-access-loader").addClass("load");
    $.ajax({
        cache: false,
        type: 'POST',
        dataType: 'json',
        url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=send_invitation_preview";?>',
        data: { fwajax: 1, fw_nocss: 1, cid: id },
        success: function(obj){
            fw_loading_end();
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(obj.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
            $(window).resize();
        }
    });
}
function output_access_grant_no_sending(_this, id)
{
    fw_loading_start();
    $(_this).closest(".output-access-loader").addClass("load");
    $.ajax({
        cache: false,
        type: 'POST',
        dataType: 'json',
        url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=send_invitation_preview";?>',
        data: { fwajax: 1, fw_nocss: 1, cid: id, noinvitiation:1 },
        success: function(obj){
            fw_loading_end();
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(obj.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
            $(window).resize();
        }
    });
}
function output_access_remove(_this, id)
{
    if(!fw_click_instance)
    {
        fw_click_instance = true;
        bootbox.confirm({
            message:$(_this).attr("data-delete-msg"),
            buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
            callback: function(result){
                if(result)
                {
                    fw_loading_start();
                    $(_this).closest(".output-access-loader").addClass("load");
                    $.ajax({
                        cache: false,
                        type: 'POST',
                        dataType: 'json',
                        url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=remove_access";?>',
                        data: { fwajax: 1, fw_nocss: 1, cid: id },
                        success: function(obj){
                            fw_loading_end();
                            $('#popupeditboxcontent').html('');
                            $('#popupeditboxcontent').html(obj.html);
                            out_popup = $('#popupeditbox').bPopup(out_popup_options);
                            $("#popupeditbox:not(.opened)").remove();
                            output_access_load();
                            $(window).resize();
                        }
                    });
                }
                fw_click_instance = false;
            }
        });
    }
}
$(function(){

	$(document).off('mouseenter mouseleave', '.output-access-changer')
	.on('mouseenter', '.output-access-changer', function(){
		$(this).find(".output-access-dropdown").show();
	}).on('mouseleave', '.output-access-changer', function(){
		$(this).find(".output-access-dropdown").hide();
	});


	<?php if(1 == $accessElementAllow_SendFilesToSignant) { ?>
	/**
    *** Cancel document
    **/
    $('.output-cancel-signant-document').off('click').on('click', function(event) {
        event.preventDefault();
		if(!fw_click_instance)
		{
			fw_click_instance = true;
			var _this = this;
			bootbox.confirm({
				message: $(_this).data('cancel-msg'),
				buttons: {confirm:{label:'<?php echo $formText_Yes_Output;?>'},cancel:{label:'<?php echo $formText_No_Output;?>'}},
				callback: function(result){
					fw_click_instance = false;
					if(result)
					{
						ajaxCall('cancel_document', { id: $(_this).data('id') }, function(json) {
							if(json.error !== undefined)
							{
								$.each(json.error, function(index, value){
									var _type = Array("error");
									if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
									fw_info_message_add(_type[0], value);
									$('#popupeditbox span.button.b-close').trigger('click');
								});
								fw_info_message_show();
							} else {
								output_reload_page();
							}
						});
					}
				}
			});
		}
    });
	<?php } ?>
	setTimeout(integration_signant_status_check, 800);

    $(".salarySourcePeople").change(function(){
        var data = { fwajax: 1, fw_nocss: 1, employeeid: '<?php echo $peopleData['id'];?>', action:"updateSalarySource", salary_source: $(this).val(), salaryid: '<?php echo $people_salary['id'];?>'};
        ajaxCall('edit_salary', data, function(obj) {
            if(obj.html != ""){
                $('#popupeditboxcontent').html('');
                $('#popupeditboxcontent').html(obj.html);
                out_popup = $('#popupeditbox').bPopup(out_popup_options);
                out_popup.addClass("close-reload");
                $("#popupeditbox:not(.opened)").remove();
            } else {
                loadView("details", {cid: "<?php echo $cid;?>"});
            }
        });
    })
})
function integration_signant_status_check()
{
	var handle = $('.signant-status.load');
	if(handle.length > 0)
	{
		var obj = $(handle).get(0);
		var data = {
			output_form_submit: 1,
			id: $(obj).data('id'),
		};
		ajaxCall('sync_document', data, function(json) {
            if(json.data !== undefined)
			{
				if(json.data.download_url)
				{
					$(obj).closest('tr').find('a.download-url').attr('href', json.data.download_url);
				}
				if(json.data.s > 1)
				{
					$(obj).closest('tr').find('a.output-cancel-document').remove();
				}
				$(obj).replaceWith(json.data.sign_status);
			}
			integration_signant_status_check();
        }, false);
	}
}

</script>
