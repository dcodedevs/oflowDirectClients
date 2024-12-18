<?php
ob_start();
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');

$o_query = $o_main->db->query('SELECT * FROM accountinfo');
$v_accountinfo = $o_query ? $o_query->row_array() : array();

if($variables->loggID == null){
	$variables->loggID = $_COOKIE['username'];
}
if($variables->sessionID == null){
	$variables->sessionID = $_COOKIE['sessionID'];
}
if($variables->userID == null){
	$variables->userID = $_POST['current_user_id'];
}
if($variables->account_id == null) {
	$variables->account_id = $v_accountinfo['getynet_account_id'];
}
if($variables->account_framework_url == null) {
	$variables->account_framework_url = $_POST['fw_url'];
}
if($variables->defaultLanguageID == null) {
	$variables->defaultLanguageID = $_POST['defaultLanguageID'];
}
if($variables->languageDir == null) {
	$variables->languageDir = $_POST['languageDir'];
}
if($variables->languageID == null) {
	$variables->languageID = $_POST['languageID'];
}

$s_lang_file = __DIR__."/../../../languages/default.php";
if(is_file($s_lang_file)) include($s_lang_file);
$s_lang_file = __DIR__."/../../../languages/".$variables->languageID.".php";
if(is_file($s_lang_file)) include($s_lang_file);
if(!function_exists("APIconnectorAccount")) include(__DIR__."/../../../includes/APIconnector.php");
// include(__DIR__."/includes/readAccessElements.php");
$channelError = false;

$chat_usernames = array();
$chat_names = array();
$chat_images = array();
$memberIds = array();

$response = json_decode(APIconnectorUser("companyaccessbycompanyidget", $variables->loggID, $variables->sessionID, array('COMPANY_ID'=>$_GET['companyID'])), true);
$v_membersystem = array();
$v_registered_usernames = array();
foreach($response['data'] as $writeContent)
{
	$v_membersystem[$writeContent['username']] = $writeContent;
	if($writeContent['registeredID'] > 0) $v_registered_usernames[] = $writeContent['username'];
}
$registered_group_list = array();
if(count($v_registered_usernames)>0)
{
	$v_response = json_decode(APIconnectorUser("group_get_list_by_filter", $variables->loggID, $variables->sessionID, array('company_id'=>$_GET['companyID'], 'usernames'=>$v_registered_usernames, 'not_hidden'=>1)),true);
	if(isset($v_response['status']) && $v_response['status'] == 1)
	{
		$registered_group_list = $v_response['items'];
	}
}

//get current single chat user info
if(isset($_POST['user_id'])){
	$data = json_decode(APIconnectorUser("userdetailsget", $variables->loggID, $variables->sessionID, array('USER_ID'=>$_POST['user_id'])),true);

	if($data['username']){
		array_push($memberIds, $data['id']);
		$chat_usernames[$_POST['user_id']]=$data['username'];
		$chat_names[$_POST['user_id']]=$data['name']. " ".$data['middle_name']. " ".$data['last_name'];
		$chat_images[$_POST['user_id']] = $data['image'];
	}
}
if(isset($_POST['channel_id']))
{
	$s_response = APIconnectorUser("channel_get", $_COOKIE['username'], $_COOKIE['sessionID'], array('CHANNEL_ID'=>$_POST['channel_id']));
	$v_response = json_decode($s_response,true);
	if($v_response['status'] == 1)
	{
		$v_channel = $v_response['channel'];
		$v_access = $v_response['access'];
		$l_edit = 1;
        $channelError = true;
    }
}
if($v_channel['type'] == 2 && count($v_access) > 0){
    $channelError = false;
    $v_channel['group_id'] = $v_access[0]['group_id'];

    $data = json_decode(APIconnectorUser("group_user_get_list", $variables->loggID, $variables->sessionID, array('group_id'=>$v_access[0]['group_id'])),true);
	if($data['status'] == 1){
        $isAdmin = false;
        $isMember = false;
		$tempMembers = $data['items'];
		foreach($tempMembers as $member){
			if($variables->loggID == $member['username']){
				$isMember = true;
				if($member['type'] == 2){
					$isAdmin = true;
				}
			}
            $data = json_decode(APIconnectorUser("userdetailsget", $variables->loggID, $variables->sessionID, array('USERNAME'=>$member['username'])),true);
            if($data['username']){
				if(!in_array($data['id'], $memberIds)){
		            array_push($memberIds, $data['id']);
	                //to not call twice already known info
	                $chat_usernames[$data['id']]=$member['username'];
	                $chat_names[$data['id']]=$member['name']. " ".$member['middle_name']. " ".$member['last_name'];
					$chat_images[$data['id']] = $member['image'];
				}
            }
		}

		$memberCount = count($memberIds);
        if(!$isAdmin) {
            $fw_error_msg = array($formText_NoAccessToEditGroupChat_chat2);
            return;
        }
	} else {
        $fw_error_msg = array($formText_ErrorRetrievingData_chat2);
        return;
    }
}
if($channelError){
    $fw_error_msg = array($formText_NotGroupChat_chat2);
    return;
}
if(isset($_POST['output_form_submit'])){

    $user_ids = $_POST['user_ids'];
    $user_ids_array = array_filter(explode(",", $user_ids));
    if(count($user_ids_array) > 1){
        $new_added_chat_usernames = array();
        $deleted_chat_usernames = array();
        //addng users to group
        foreach($user_ids_array as $single_user_id){
            if(!isset($chat_usernames[$single_user_id])){
                $data = json_decode(APIconnectorUser("userdetailsget", $variables->loggID, $variables->sessionID, array('USER_ID'=>$single_user_id)),true);
                if($data['username']){
                    array_push($new_added_chat_usernames, $data['username']);
                    $chat_usernames[$single_user_id]=$data['username'];
                    $chat_names[$single_user_id]=$data['name']. " ".$data['middle_name']. " ".$data['last_name'];
                }
            }
        }
        //remove deleted users
        foreach($chat_usernames as $s_userid=>$s_username){
            if(!in_array($s_userid, $user_ids_array)){
                if($s_userid != $variables->userID){
                    array_push($deleted_chat_usernames, $s_username);
                }
                unset($chat_usernames[$s_userid]);
                unset($chat_names[$s_userid]);
            }
        }
        if(count($chat_usernames) == (count($user_ids_array))){
			$data = json_decode(APIconnectorUser("userdetailsget", $variables->loggID, $variables->sessionID, array('USER_ID'=>$variables->userID)),true);
			if($data['username']){
	            $channel_name = $data['name']. " ".$data['middle_name']. " ".$data['last_name'];
			}
            $chatnameCount = 0;
            foreach($chat_names as $chat_name) {
                if($chatnameCount < 4){
                    $channel_name .= ", ".$chat_name;
                    $chatnameCount++;
                }
            }
            if(count($chat_names) > 4) {
                $channel_name .= " +";
            }

        	$v_param = array('NAME'=>$channel_name, 'TYPE'=>2, 'STATUS'=>1);
            if($v_channel){
                //editing existing group chat
                if($isAdmin) {
                    $groupId = $v_channel['group_id'];
                    $v_param['ID'] = $v_channel['id'];
                    if($groupId > 0){
                        $s_response = APIconnectorUser('channel_set', $_COOKIE['username'], $_COOKIE['sessionID'], $v_param);
                        $v_response = json_decode($s_response, true);
                        if($v_response['status'] == 1)
                        {
                            $fw_return_data = $v_channel['id'];

                            foreach($new_added_chat_usernames as $chat_username) {
                                $data = json_decode(APIconnectorUser("group_user_set", $variables->loggID, $variables->sessionID, array('group_id'=>$groupId,'username'=>$chat_username, 'type'=>2)),true);

                                if($data['status']){

                                } else {
                                    $fw_error_msg = array($data['error']);
                                }
                            }
                            foreach($deleted_chat_usernames as $chat_username) {
                                $data = json_decode(APIconnectorUser("group_user_delete", $variables->loggID, $variables->sessionID, array('group_id'=>$groupId,'username'=>$chat_username)),true);
                                if($data['status']){
                                } else {
                                    $fw_error_msg = array($data['error']);
                                }
                            }
                        } else {
                            $fw_error_msg = array($formText_ErrorSavingInDatabase_chat2);
                        }
                    } else {
                        $fw_error_msg = array($formText_ErrorRetrievingData_chat2);
                    }
                } else {
                    $fw_error_msg = array($formText_NoAccessToEditGroupChat_chat2);
                }
            } else {
                //creating new group chat
                $s_response = APIconnectorUser('channel_set', $_COOKIE['username'], $_COOKIE['sessionID'], $v_param);
                $v_response = json_decode($s_response, true);
                if($v_response['status'] == 1)
                {
                    $channel_id = $v_response['channel_id'];
                    //creating group
                    $data = json_decode(APIconnectorUser("group_set", $variables->loggID, $variables->sessionID,
                    array(
                        'name'=>$channel_name,
                        'status'=>'1',
                        'company_id'=>$_GET['companyID'],
                        'account_id'=> $variables->account_id,
                        'group_chat'=>1
                    )),true);
                    if($data['status'] == 1){
                        $groupId = $data['id'];
                        //adding administrator to group
                        $data = json_decode(APIconnectorUser("group_user_set", $variables->loggID, $variables->sessionID, array('group_id'=>$groupId,'username'=>$variables->loggID, 'type'=>2)),true);
                        if($data['status']){
                            foreach($chat_usernames as $chat_username) {

                                $data = json_decode(APIconnectorUser("group_user_set", $variables->loggID, $variables->sessionID, array('group_id'=>$groupId,'username'=>$chat_username, 'type'=>2)),true);

                                if($data['status']){

                                } else {
                                    $fw_error_msg = array($data['error']);
                                }
                            }
                            //adding channel access
                    		$v_param = array('CHANNEL_ID'=>$channel_id, 'ACCESS'=>array());
                			$v_access = array
                			(
                				'ID'=>0,
                				'TYPE'=>3,
                				'GROUP_ID'=>$groupId,
                				'ACCESS_LEVEL'=>1
                			);
                			$v_param['ACCESS'][] = $v_access;
							// var_dump($v_param, $v_accountinfo);
                    		$s_response = APIconnectorAccount('channel_access_set', $v_accountinfo['accountname'], $v_accountinfo['password'], $v_param);
                    		$v_response = json_decode($s_response, TRUE);
							// var_dump($v_response);
                    		if(isset($v_response['status']) && $v_response['status'] == 1) {
                                $fw_return_data = $channel_id;
                            } else {
                                $fw_error_msg = array($v_response['error']);
                            }
                        } else {
                            $fw_error_msg = array($data['error']);
                        };

                    } else {
                        $fw_error_msg = array($data['error']);
                    }
                } else {
                    $fw_error_msg = array($formText_ErrorSavingInDatabase_chat2);
                }
            }
        } else {
            $fw_error_msg = array($formText_ErrorRetrievingData_chat2);
        }

    } else {
        $fw_error_msg = array($formText_PleaseChooseMoreThanOneParticipantForGroupChat_chat2);
    }
	if($fw_return_data != null) {
		$returnItem['data'] = $fw_return_data;
	}
	if($fw_error_msg != null) {
		$returnItem['error'] = $fw_error_msg;
	}
	echo json_encode($returnItem);
    return;
}

$active_sets = $_POST['active_sets'];

$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
$o_query = $o_main->db->get_where('session_framework', $v_param);
if($o_query) $fw_session = $o_query->row_array();
$contactLists = json_decode(APIconnectorUser('contactsetget', $variables->loggID, $variables->sessionID, array('SHOW_SET'=>array(), 'SHOW_COMPANY'=>array())), TRUE);
$sets = $contactLists['sets'];

$choosenSets = array();
$choosenCompanies = array();
//allSets and companies
foreach($sets as $singleSet){
	// if(in_array($singleSet['company_id'], $active_sets['company'])){
	    array_push($choosenSets, $singleSet['set_id']);
	    array_push($choosenCompanies, $singleSet['company_id']);
	// }
}
$UserContactSetSelectedCount = count($choosenSets);
$UserContactSetCount = count($sets);
?>
<div class="popupform popupform-<?php echo $v_channel['id'];?>">
	<div id="popup-validate-message" style="display:none;"></div>
    <div class="channel_edit_wrapper">
    	<form  class="output-form main"  name="upadate" action="<?php echo $variables->account_framework_url;?>getynet_fw/modules/Chat/output/ajax.add_group_chat.php<?php echo "?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']?>"
		method="POST">
			<input type="hidden" name="current_user_id" value="<?php echo $_POST['current_user_id'];?>"/>
        	<input type="hidden" name="user_id" value="<?php echo $_POST['user_id']; ?>" />
            <?php if($v_channel) {?>
                <input type="hidden" name="channel_id" value="<?php echo $_POST['channel_id']; ?>" />
            <?php } ?>
            <input type="hidden" name="fwajax" value="1">
    		<input type="hidden" name="fw_nocss" value="1">
    		<input type="hidden" name="output_form_submit" value="1">
			<input type="hidden" name="fw_url" value="<?php echo $variables->account_framework_url; ?>">
        	<input type="hidden" name="languageDir" value="<?php echo $_POST['languageDir']; ?>" />
        	<input type="hidden" name="languageID" value="<?php echo $_POST['languageID']; ?>" />
        	<input type="hidden" name="defaultLanguageID" value="<?php echo $_POST['defaultLanguageID']; ?>" />
        	<div class="profile">
                <div class="popupformTitle" style="margin-bottom: 0;"><?php if($v_channel) { echo $formText_AddToGroupChat_Chat2; } else { echo $formText_CreateNewGroupChat_Chat2;}?></div>
        		<table border="0" cellpadding="0" cellspacing="0" rules="none" frame="void" width="100%" class="channel_info_table">
                    <tr>
                        <td>
							<?php /*?>
                            <div class="fw_chat_list_tab_contacts" data-chat-list-tab="contacts">
        						<div class="fw_chat_contact_list_filter filter_groups">
        							<a href="#" class="button2" id="fwcl_chat_groups_button"><?php echo $formText_ChooseContactGroups_Chat2; ?> (<span class="selected"><?php echo $UserContactSetSelectedCount ?></span> / <span class="all"><?php echo $UserContactSetCount; ?></span>) <span class="icon icon-arrow-right"></a>

        							<div class="filter_groups_checkboxes">
        								<ul id="fwcl_chat_groups">
        									<li><input type="checkbox" class="showall" <?php if ($UserContactSetCount == $UserContactSetSelectedCount ) echo 'checked'; ?>> <?php echo $formText_ShowAll_Chat2; ?></li>

											<?php foreach($sets as $item): ?>
        									<li>
        										<input type="checkbox" autocomplete="off" <?php if (in_array($item['company_id'], $choosenCompanies)) { echo 'checked'; }; ?> data-setid="<?php echo $item['set_id']; ?>" data-companyid="<?php echo $item['company_id']; ?>"> <?php echo $item['name'];?>
        									</li>
        									<?php endforeach; ?>
        								</ul>
        							</div>
        						</div>
        						<div class="fw_chat_list_tab_contacts_list"></div>
        					</div>*/?>
							<div class="peopleSearchWrapper">
								<input type="text" class="" value="" placeholder="<?php echo $formText_Search_chat2;?>"/>
							</div>
							<div class="filteredInfo" style="display:none;"><span class="filteredPeople"></span> <?php echo $formText_InSelection_chat2;?> <span class="resetFilter fw_text_link_color"><?php echo $formText_ResetSearch_chat2;?></span></div>
							<div class="peopleSearchInfo">
								<span class="totalPeople">0</span> <?php echo $formText_InTotal_chat2?>
							</div>
                            <div class="seletectedContactCount">
								<?php echo $formText_SelectedCount_chat2?> <span class="selectedPeople">0</span>
							</div>
							<div class="clear"></div>

                            <div class="contact_list_popup" style="height: 300px; overflow: auto; float:left; width: 47%;">
								<!-- <?php echo $formText_AllContacts_chat2;?> -->
                                <div class="fw_chat_recents_list"></div>
                            </div>

                            <div class="contact_list_popup_selected" style="height: 300px; overflow: auto; float:right; width: 47%;">
								<!-- <?php echo $formText_SelectedContacts_chat2;?> -->
                                <div class="fw_chat_recents_list">
									<ul>
										<?php foreach($memberIds as $memberId) {
											if($chat_usernames[$memberId] != $variables->loggID){
												$profileimage = json_decode($chat_images[$memberId],true); ?>
												<li data-fullname="<?php echo mb_strtolower($chat_names[$memberId]); ?>">
													<a href="#" class="selected" data-user-id="<?php echo $memberId; ?>" rel="<?php echo $variables->languageDir."modules/OnlineList/output/userdetails_ajax.php?userID=".$memberId."&amp;profileimage=".urlencode($item['image'])."&amp;dlang=".$variables->defaultLanguageID."&amp;lang=".$variables->languageID;?>">
														<span class="image">
															<span class="user_image">
																<?php if(is_array($profileimage)){?><img src="https://pics.getynet.com/profileimages/<?php print $profileimage[0]; ?>" alt="" border="0" /><?php }
																else {
																	echo '&nbsp;';
																}
																?>
															</span>
														</span>

														<span class="name"><?php echo $chat_names[$memberId]; ?><span class="glyphicon glyphicon-ok selected-icon fw_icon_color"></span></span>
														<?php
														$groups = array();
														$departments = array();
														// if(isset($v_membersystem[$chat_usernames[$memberId]])) {
														// 	$single_item = $v_membersystem[$chat_usernames[$memberId]];
														// 	foreach($single_item['groups'] as $groupSingle){
														// 		if(intval($groupSingle['department']) == 0) {
														// 			array_push($groups, $groupSingle);
														// 		} else {
														// 			array_push($departments, $groupSingle);
														// 		}
														// 	}
														// }
														if(isset($registered_group_list[$chat_usernames[$memberId]]))
														{
															$allGroupsForNotRegistered = $registered_group_list[$chat_usernames[$memberId]];
															foreach($allGroupsForNotRegistered as $groupSingleItem){
																if($groupSingleItem['department']){
																	array_push($departments, $groupSingleItem);
																} else {
																	array_push($groups, $groupSingleItem);
																}
															}
														}
														if(count($departments) > 0) {
															?>
															<span class="departmentInfo fas fa-info-circle"></span>
															<div class="departments">
																<?php
																foreach($departments as $department){
																	?>
																	<div class="department"><?php echo $department['name'];?></div>
																	<?php
																}
																?>
															</div>
														<?php } ?>
													</a>
												</li>
										<?php }
											} ?>
									</ul>
								</div>
                            </div>
                        </td>
                    </tr>
        			<tr><td>
                        <div class="popupformbtn">
                			<input type="submit" class="fw-btn fw-btn-small fw_button_color" name="sbmbtn" value="<?php echo $formText_Save_Framework; ?>">
                		</div>
        			</td>
        			</tr>
        		</table>

        	</div>
    	</form>
    </div>
</div>
<style>
.popupform #fwcl_chat_groups {
	padding: 0;
}
.popupform .showMoreInChat {
	cursor: pointer;
}
.popupform .fw_chat_contact_list_filter  {
    max-height: 300px;
    overflow: auto;
    margin-bottom: 15px;
}
.popupform .fw_chat_contact_list_filter a {
    display: block;
}
.popupform .fw_chat_list_tab_contacts {
    display: block;
    position: relative;
}
.popupform #fwcl_list {
    padding-left: 0;
}
.popupform .contact_list_popup_selected ul {
	padding-left: 0;
	list-style: none;
}
.popupform .seletectedContactCount {
	width: 47%;
	float: right;
    margin-bottom: 10px;
    font-size: 14px;
}
.popupform .peopleSearchInfo {
	width: 47%;
	float: left;
    margin-bottom: 10px;
    font-size: 14px;
}
.popupform .filteredInfo {
	font-size: 14px;
	margin-bottom: 10px;
}
.popupform .filteredInfo .resetFilter {
	cursor: pointer;
	margin-left: 20px;
}
.popupform .peopleSearchWrapper {
	margin-bottom: 10px;
}
.popupform .peopleSearchWrapper input {
	font-size: 13px;
	line-height: 15px;
	padding: 6px 10px;
	border-radius: 3px;
	border: 1px solid #e5e5e5;
	width: 100%;
}
.popupform .fw_chat_recents_list li {
	position: relative;
}
.popupform .fw_chat_recents_list li a {
    height: auto;
}
.popupform .fw_chat_recents_list li a.selected {
    /* background: #eee; */
}
.popupform .fw_chat_recents_list li .departmentInfo {
	position: absolute;
	right: 40px;
	top: 15px;
	color: #cecece;
}
.popupform .fw_chat_recents_list li .departments {
	position: absolute;
	right: 0;
	z-index: 3;
	background: #fff;
	padding: 5px 10px;
	text-align: right;
	border: 1px solid #cecece;
	display: none;
}
.popupform .fw_chat_recents_list li .departments.active {
	display: block;
}
.popupform .fw_chat_recents_list li .departments .department {
	padding: 3px 0px;
}
.popupform .contact_list_popup_selected .fw_chat_recents_list li a.selected {
    /* background: #eee; */
	cursor: pointer;
}
.popupform .fw_chat_recents_list li a:focus
{
	display:block;
	text-decoration:none;
	background:#fff;
}
.popupform .fw_chat_recents_list li a.selected .name {
    position: relative;
}
.popupform .fw_chat_recents_list li a.selected .name .selected-icon {
    position: absolute;
    width: 20px;
    right: 0;
    top: 9px;
    display: block !important;
}
.popupform .departments .department {
	margin-bottom: 4px;
}
</style>
<script type="text/javascript" src="<?php echo $variables->account_framework_url;?>getynet_fw/modules/Chat/output/includes/jquery.validate.min.js"></script>
<script type="text/javascript">
	//fix preselected checkboxes cache issue
	$(".popupform #fwcl_chat_groups input:checkbox").attr("autocomplete", "off");

	//reset channel data, that triggers reload
    $(".popupeditbox").data("channel-id", 0);

    var set = [];
    var company = [];
    <?php foreach($choosenSets as $singleSet) { ?>
        set.push('<?php echo $singleSet?>');
    <?php } ?>
    <?php foreach($choosenCompanies as $singleSet) { ?>
        company.push('<?php echo $singleSet?>');
    <?php } ?>
    $('.popupform #fwcl_chat_groups input').on('change', function() {
        // if($(this).hasClass('showall')) {
        //     if($(this).prop('checked')) $('.popupform #fwcl_chat_groups input').prop('checked', true);
        //     else $('.popupform #fwcl_chat_groups input').prop('checked', false);
		//
		// 	if($(this).prop('checked')) $('.fw_chat_left #fwcl_chat_groups input').prop('checked', true);
        //     else $('.fw_chat_left #fwcl_chat_groups input').prop('checked', false);
		//
		// 	if($(this).prop('checked')) {
		// 		 $('.fw_chat_left #fwcl_chat_groups input.showall').prop('checked', true).change();
		// 	} else {
		// 		 $('.fw_chat_left #fwcl_chat_groups input.showall').prop('checked', false).change();
		// 	}
		//
        // } else {
		// 	var setid = $(this).data("setid");
		// 	var companyid = $(this).data("companyid");
		// 	if($(this).prop('checked')) {
		// 		 $('.fw_chat_left #fwcl_chat_groups input[data-companyid="'+companyid+'"]').prop('checked', true).change();
		// 	} else {
		// 		 $('.fw_chat_left #fwcl_chat_groups input[data-companyid="'+companyid+'"]').prop('checked', false).change();
		// 	}
		// }
		//
        // var checkboxes = $(".popupform #fwcl_chat_groups input");
        // var set = [];
        // var company = [];
        // var selected = 0;
        // checkboxes.each(function(){
        //     if(!$(this).hasClass('showall')) {
        //         var checked = $(this).prop('checked');
        //         var setid = $(this).data("setid");
        //         var companyid = $(this).data("companyid");
        //         if(checked) {
        //             selected++;
        //             set.push(setid);
        //             company.push(companyid);
        //         }
        //     }
        // });
        // $(".popupform #fwcl_chat_groups_button .selected").html(selected);
        // fw_loading_start();
        // $.ajax({
        //     url: "<?php echo $variables->account_framework_url; ?>getynet_fw/modules/Chat/output/ajax.get_userlist.php",
        //     data: {
        //         set: set,
        //         company: company,
        //         preseletedIds: '<?php echo json_encode($memberIds)?>',
        //         accountname: '<?php echo $_GET['accountname'];?>',
        //         caID: '<?php echo $_GET['caID'];?>',
        //         dlang: '<?php echo $variables->defaultLanguageID; ?>',
        //         lang: '<?php echo $variables->languageID;?>',
        //         refresh: 1,
		// 		hideStatus: 1
        //     },
        //     success: function (data) {
        //         $('.contact_list_popup .fw_chat_recents_list').html(data);
        //         fw_loading_end();
        //         rebindContactFunction();
        //         //select user from where the add group was clicked
        //         $('.contact_list_popup #fwcl_list a[data-user-id="<?php echo $_POST['user_id']?>"]').addClass("selected");
		// 		refreshSelected();
        //     },
        //     cache: false
        // }).fail(function() {
        //     fw_loading_end();
        // });
    })
	var page = 1;
	var per_page = 100;
    $.ajax({
        url: "<?php echo $variables->account_framework_url; ?>getynet_fw/modules/Chat/output/ajax.get_userlist.php",
		method: "POST",
        data: {
            set: set,
            company: company,
            preseletedIds: '<?php echo json_encode($memberIds)?>',
            accountname: '<?php echo $_GET['accountname'];?>',
            caID: '<?php echo $_GET['caID'];?>',
            dlang: '<?php echo $variables->defaultLanguageID; ?>',
            lang: '<?php echo $variables->languageID;?>',
            refresh: 1,
			hideStatus: 1,
			page: page,
			per_page: per_page,
			companyID: '<?php echo $_GET['companyID']?>',
			show_department: 1
        },
        success: function (data) {
            $('.contact_list_popup .fw_chat_recents_list').html(data);
            fw_loading_end();
            rebindContactFunction();
            //select user from where the add group was clicked
            $('.contact_list_popup #fwcl_list a[data-user-id="<?php echo $_POST['user_id']?>"]').addClass("selected");
			refreshSelected();
        },
        cache: false
    }).fail(function() {
        fw_loading_end();
    });

	var typingTimer;                //timer identifier
	var doneTypingInterval = 500;  //time in ms, 5 second for example
	var $input = $('.popupform .peopleSearchWrapper input');

	//on keyup, start the countdown
	$input.on('keyup', function () {
	  clearTimeout(typingTimer);
	  typingTimer = setTimeout(doneTyping, doneTypingInterval);
	});

	//on keydown, clear the countdown
	$input.on('keydown', function () {
	  clearTimeout(typingTimer);
	});

	//user is "finished typing," do something
	function doneTyping () {
	  //do something
	  fw_loading_start();
	  var searchValue = $input.val();
	  page = 1;
	  $.ajax({
		  url: "<?php echo $variables->account_framework_url; ?>getynet_fw/modules/Chat/output/ajax.get_userlist.php",
		  method: "POST",
		  data: {
			  set: set,
			  company: company,
			  preseletedIds: '<?php echo json_encode($memberIds)?>',
			  accountname: '<?php echo $_GET['accountname'];?>',
			  caID: '<?php echo $_GET['caID'];?>',
			  dlang: '<?php echo $variables->defaultLanguageID; ?>',
			  lang: '<?php echo $variables->languageID;?>',
			  refresh: 1,
			  hideStatus: 1,
			  page: page,
			  per_page: per_page,
			  search: searchValue,
  			  companyID: '<?php echo $_GET['companyID']?>',
  			  show_department: 1
		  },
		  success: function (data) {
			  $('.contact_list_popup .fw_chat_recents_list').html(data);
			  fw_loading_end();
			  rebindContactFunction();
			  refreshSelected();
			  if(searchValue != ""){
				  $(".popupform .filteredInfo .filteredPeople").html($(".contact_list_popup #fwcl_list").data("search-count"));
				  $(".popupform .filteredInfo").show();
			  } else {
				  $(".popupform .filteredInfo").hide();
			  }
		  },
		  cache: false
	  }).fail(function() {
		  fw_loading_end();
	  });
	}

	// $(".popupform .peopleSearchWrapper input").on("keyup", function(){
	//
	// 	// var searchValue = $(this).val().toLowerCase();
	// 	// if(searchValue != ""){
	// 	// 	$(".popupform .contact_list_popup .fw_chat_recents_list li").hide();
	//     // 	dataResult = $('.popupform .contact_list_popup .fw_chat_recents_list li[data-fullname*="'+searchValue+'"]');
	// 	// 	dataResult.show();
	// 	// 	$(".peopleSearchInfo .filteredInfo .filteredPeople").html($(".popupform .contact_list_popup .fw_chat_recents_list li:visible").length);
	// 	//
	// 	// } else {
	// 	// 	$(".popupform .contact_list_popup .fw_chat_recents_list li").show();
	// 	//
	// 	// }
	// })
	$(".popupform .filteredInfo .resetFilter").on("click", function(){
		fw_loading_start();
		$(".popupform .peopleSearchWrapper input").val("").keyup();
	})
    function rebindContactFunction(){
        $(".contact_list_popup #fwcl_list a").off("click").on("click", function(){
			if($(this).hasClass("selected")){
				$(this).removeClass("selected");
				$('.contact_list_popup_selected ul a[data-user-id="'+$(this).data("user-id")+'"]').remove();
			} else {
            	$(this).addClass("selected");
			}
			refreshSelected();
        })
		$(".contact_list_popup_selected ul a").off("click").on("click", function(){
            $('.contact_list_popup #fwcl_list a[data-user-id="'+$(this).data("user-id")+'"]').removeClass("selected");
			$(this).parents("li").remove();
			refreshSelected();
        })
		$(".contact_list_popup .showMoreInChat").off("click").on("click", function(){
			var showMoreButton = $(this);
				fw_loading_start();
				page += 1;
				var searchValue = $input.val();
		  	  $.ajax({
		  		  url: "<?php echo $variables->account_framework_url; ?>getynet_fw/modules/Chat/output/ajax.get_userlist.php",
				  method: "POST",
		  		  data: {
		  			  set: set,
		  			  company: company,
		  			  preseletedIds: '<?php echo json_encode($memberIds)?>',
		  			  accountname: '<?php echo $_GET['accountname'];?>',
		  			  caID: '<?php echo $_GET['caID'];?>',
		  			  dlang: '<?php echo $variables->defaultLanguageID; ?>',
		  			  lang: '<?php echo $variables->languageID;?>',
		  			  refresh: 1,
		  			  hideStatus: 1,
		  			  page: page,
		  			  per_page: per_page,
		  			  search: searchValue
		  		  },
		  		  success: function (data) {
					  showMoreButton.parents(".showMoreInChatWrapper").remove();
		  			  $('.contact_list_popup .fw_chat_recents_list').append(data);
		  			  fw_loading_end();
		  			  rebindContactFunction();
		  			  // refreshSelected();
		  			  // if(searchValue != ""){
		  				//   $(".peopleSearchInfo .filteredInfo .filteredPeople").html($(".contact_list_popup #fwcl_list").data("search-count"));
		  				//   $(".peopleSearchInfo .filteredInfo").show();
		  			  // } else {
		  				//   $(".peopleSearchInfo .filteredInfo").hide();
		  			  // }
		  		  },
		  		  cache: false
		  	  }).fail(function() {
		  		  fw_loading_end();
		  	  });
		})
		var timeoutNew = null;
		$(".fw_chat_recents_list .departmentInfo").off("mouseenter").on("mouseenter", function(e){
			e.stopPropagation();
			$(this).parents("li").find(".departments").addClass("active");
			clearTimeout(timeoutNew);
		})
		$(".fw_chat_recents_list .departmentInfo").off("mouseleave").on("mouseleave", function(e){
			e.stopPropagation();
			var el = $(this);
			timeoutNew = setTimeout(function(){
				el.parents("li").find(".departments").removeClass("active");
			}, 200)
		})
		$(".fw_chat_recents_list .departments").off("mouseover").on("mouseover", function(e){
			e.stopPropagation();
			$(this).addClass("active");
			clearTimeout(timeoutNew);
		})
		$(".fw_chat_recents_list .departments").off("mouseleave").on("mouseleave", function(e){
			e.stopPropagation();
			var el = $(this);
			timeoutNew = setTimeout(function(){
				el.removeClass("active");
			}, 200)
		})
    }
	function refreshSelected(){
		var selectedUl = $(".contact_list_popup_selected .fw_chat_recents_list ul");
		// selectedUl.html("");
		$(".contact_list_popup #fwcl_list a.selected").each(function(){
			var parent = $(this).parents("li");
			var contactUserId = parent.data("user-id");
			var selectedUser = selectedUl.find('a[data-user-id="'+$(this).data("user-id")+'"]');
			if(selectedUser.length == 0){
				selectedUl.append(parent.clone().show());
			}
		})
		rebindContactFunction();
		refreshSelectedCount();

	}
    function refreshSelectedCount(){
        $(".peopleSearchInfo .totalPeople").html($(".contact_list_popup #fwcl_list").data("total-count"));
        $(".seletectedContactCount .selectedPeople").html($(".contact_list_popup_selected ul a.selected").length);
    }
    $(".popupform-<?php echo $v_channel['id'];?> form.output-form").validate({
        ignore: [],
        submitHandler: function(form) {
            var user_ids = "";
            var data = [];
            $(".contact_list_popup_selected ul a.selected").each(function(){
                var selected = $(this);
                var selectedUserId = selected.data("user-id");
                user_ids += selectedUserId+",";
            })

            data[0] = {
                name: "user_ids",
                value: user_ids
            };

            var data_serialized = $(form).serializeArray();

            var dataToPass = $.merge(data, data_serialized);
            $("#popup-validate-message").html("");
            fw_loading_start();
            $.ajax({
                url: $(form).attr("action"),
                cache: false,
                type: "POST",
                dataType: "json",
                data: dataToPass,
                success: function (data) {
                    fw_loading_end();
					if(data.error !== undefined)
					{
						$.each(data.error, function(index, value){
							$("#popup-validate-message").html("<div>"+value+"</div>").show();
						});
						fw_click_instance = fw_changes_made = false;
					} else {
                        out_popup_chat.addClass("close-reload");
                        out_popup_chat.data("channel-id", data.data);
                        out_popup_chat.data("refreshRecentList", 1);
                        out_popup_chat.close();
					}
                }
            }).fail(function() {
                $(".popupform-<?php echo $v_channel['id'];?> #popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                $(".popupform-<?php echo $v_channel['id'];?> #popup-validate-message").show();
                $('.popupform-<?php echo $v_channel['id'];?> #popupeditbox').css('height', $('.popupform-<?php echo $v_channel['id'];?> #popupeditboxcontent').height());
                fw_loading_end();
            });
        },
        invalidHandler: function(event, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var message = errors == 1
                ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                $(".popupform-<?php echo $v_channel['id'];?> #popup-validate-message").html(message);
                $(".popupform-<?php echo $v_channel['id'];?> #popup-validate-message").show();
                $('.popupform-<?php echo $v_channel['id'];?> #popupeditbox').css('height', $('#popupeditboxcontent').height());
            } else {
                $(".popupform-<?php echo $v_channel['id'];?> #popup-validate-message").hide();
            }
            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
        },
        errorPlacement: function(error, element) {
            if(element.attr("name") == "customerId") {
                error.insertAfter(".popupform-<?php echo $v_channel['id'];?> .selectCustomer");
            }
        },
        messages: {
            customerId: "<?php echo $formText_SelectTheCustomer_output;?>",
        }
    });

</script>
<?php
$returnItem['html'] = ob_get_clean();
echo json_encode($returnItem);
?>
