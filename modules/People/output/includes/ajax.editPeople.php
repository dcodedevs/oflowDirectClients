<?php
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

$peopleId = isset($_POST['cid']) ? $o_main->db->escape_str($_POST['cid']) : 0;
$parentId = isset($_POST['parentId']) ? $_POST['parentId'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : 0;
$customer_id = isset($_POST['customer_id']) ? $o_main->db->escape_str($_POST['customer_id']) : 0;

$o_query = $o_main->db->query("SELECT * FROM people_basisconfig");
$v_people_config = $o_query ? $o_query->row_array() : array();
$o_query = $o_main->db->query("SELECT * FROM people_accountconfig");
if($o_query && $o_query->num_rows()>0)
{
	$v_people_config = $o_query->row_array();
}

$v_membersystem = array();

$o_query = $o_main->db->query("SELECT * FROM cache_userlist_membershipaccess");
$v_cache_userlist_membership = $o_query ? $o_query->result_array() : array();
foreach($v_cache_userlist_membership as $v_user_cached_info) {
	$v_membersystem[$v_user_cached_info['username']] = $v_user_cached_info;
}
$o_query = $o_main->db->query("SELECT * FROM cache_userlist_access");
$v_cache_userlist = $o_query ? $o_query->result_array() : array();
foreach($v_cache_userlist as $v_user_cached_info) {
	$v_membersystem[$v_user_cached_info['username']] = $v_user_cached_info;
}
// $response = json_decode(APIconnectorUser("companyaccessbycompanyidget", $variables->loggID, $variables->sessionID, array('COMPANY_ID'=>$companyID, 'GET_GROUPS'=>1)));
// $v_membersystem = array();
// foreach($response->data as $writeContent)
// {
//     array_push($v_membersystem, $writeContent);
// }

$canEditAdmin = $accessElementAllow_AddEditDeletePeople;
if($peopleId) {
    $sql = "SELECT * FROM contactperson WHERE id = $peopleId";
	$o_query = $o_main->db->query($sql);
    $peopleData = $o_query ? $o_query->row_array() : array();
} else {
    // $sql = "SELECT * FROM people WHERE email = ?";
	// $o_query = $o_main->db->query($sql, array($_POST['email']));
    // $peopleData = $o_query ? $o_query->row_array() : array();
	// $peopleId = $peopleData['id'];
}
$v_user_external = array();
$v_user_external['name'] = $peopleData['name'];
$v_user_external['middle_name'] = $peopleData['middlename'];
$v_user_external['last_name'] = $peopleData['lastname'];
$v_user_external['mobile'] = $peopleData['mobile'];
$v_user_external['mobile_prefix'] = $peopleData['mobile_prefix'];
$v_user_external['username'] = trim($peopleData['email']);
$notEditable = false;
$notEditableOther = true;
$companyCanEditProfile = false;
if($peopleData) {
    if(!function_exists("APIconnectorUser")) include(__DIR__."/includes/APIconnector.php");
	if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

    if($canEditAdmin) {
        $editable = true;
        $partlyEdit = true;
    }
    foreach($v_membersystem as $member){
        if(mb_strtolower(trim($peopleData['email'])) == mb_strtolower($member['username'])) {
            if($member['user_id']){
                $notEditable = true;
                $notEditableOther = false;
                // $v_user_external = json_decode(APIconnectorUser("userdetailsget", $variables->loggID, $variables->sessionID, array('USER_ID'=>$member['user_id'])),true);

            	$v_user_external['fullname'] = preg_replace('/\s+/', ' ', $member['first_name'].' '.$member['middle_name'].' '.$member['last_name']);
            	$v_user_external['name'] = preg_replace('/\s+/', ' ', $member['first_name']);
            	$v_user_external['middle_name'] = preg_replace('/\s+/', ' ', $member['middle_name']);
            	$v_user_external['last_name'] = preg_replace('/\s+/', ' ', $member['last_name']);

                $v_user_external['username'] = mb_strtolower($member['username']);
                if($member['mobile'] == ""){
                    $v_user_external['mobile'] = $peopleData['mobile'];
                    $v_user_external['mobile_prefix'] = $peopleData['mobile_prefix'];
                } else {
                    $v_user_external['mobile'] = $member['mobile'];
                    $v_user_external['mobile_prefix'] = $member['mobile_prefix'];
                }
                if(mb_strtolower($member['username']) != mb_strtolower($variables->loggID)){
                    $notEditableOther = true;
                }
            }
            break;
        }
    }
	if($v_people_config['invitation_contentIdField'] != "" || $v_people_config['personowncompany_fixed_contentIdField'] != ""){
		$accessLevelConfig = $v_people_config['invitation_accesslevel'];
		if($v_people_config['personowncompany_fixed_contentIdField'] != ""){
			$accessLevelConfig = $v_people_config['personowncompany_fixed_accesslevel'];
		}
		if($peopleData['email']!="")
		{
			$o_membersystem = json_decode(APIconnectAccount("membersystemcompanyaccessusernameget", $v_accountinfo['accountname'], $v_accountinfo['password'], array("COMPANY_ID"=>$_GET['companyID'], "USER"=>$peopleData["email"], "MEMBERSYSTEMID"=>$peopleData['id'],
			"ACCESSLEVEL"=>$accessLevelConfig, "MODULE"=>$module)), true);
			if($o_membersystem['data']){
				$member = $o_membersystem['data'];
				$notEditable = true;
                $notEditableOther = false;

				$v_user_external['fullname'] = preg_replace('/\s+/', ' ', $member['first_name'].' '.$member['middle_name'].' '.$member['last_name']);
                $v_user_external['username'] = mb_strtolower($member['username']);
                if($member['mobile'] == ""){
                    $v_user_external['mobile'] = $peopleData['mobile'];
                    $v_user_external['mobile_prefix'] = $peopleData['mobile_prefix'];
                } else {
                    $v_user_external['mobile'] = $member['mobile'];
                    $v_user_external['mobile_prefix'] = $member['mobile_prefix'];
                }
                if(mb_strtolower($member['username']) != mb_strtolower($variables->loggID)){
                    $notEditableOther = true;
                }
			}
		}
	}
}
if($member['username'] != ""){
	//check if company has access to edit
	$v_param = array(
		'COMPANY_ID'=>$_GET['companyID'],
		'USERNAME'=>$member['username']
	);
	$s_response = APIconnectorUser("companyallowededituserdata_check", $variables->loggID, $variables->sessionID, $v_param);
	$v_response = json_decode($s_response, TRUE);
	if($v_response['data'] == 'OK'){
		$notEditableOther = false;
		$companyCanEditProfile = true;
	}
}

if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
		$viewExist = false;
		$s_sql = "select * from contactperson_crm limit 1";
		$o_query = $o_main->db->query($s_sql);
		$contactpersonView = $o_query ? $o_query->row_array() : array();
		if($contactpersonView){
			$viewExist = true;
		}

		$updated = false;
		if($_POST['first_name'] != ""){
			if(trim($_POST['email']) != "" && filter_var(trim($_POST['email']),FILTER_VALIDATE_EMAIL) || trim($_POST['email']) == ""){
				if($notEditable){
	                //rewrite post data to prevent html manipulation
					$_POST['email'] = trim($v_user_external['username']);
	                if($notEditableOther){
	                    $_POST['first_name'] = $v_user_external['name'];
	                    $_POST['middle_name'] = $v_user_external['middle_name'];
	                    $_POST['last_name'] = $v_user_external['last_name'];
	    				$_POST['mobile'] = $v_user_external['mobile'];
	    				$_POST['mobile_prefix'] = $v_user_external['mobile_prefix'];
	                }
				}
				$triggerGetynetUpdate = false;
		        if ($peopleId) {
		        	$s_sql = "select * from contactperson where id = ?";
		            $o_query = $o_main->db->query($s_sql, array($peopleId));
		            $oldPeople = $o_query ? $o_query->row_array() : array();

					$s_sql = "select * from contactperson where email = ? AND id <> ? AND type = ?";
		            $o_query = $o_main->db->query($s_sql, array(trim($_POST['email']), $oldPeople['id'], $people_contactperson_type));
		            $sameEmail = $o_query ? $o_query->row_array() : array();
					if(!$sameEmail){
			            $sql = "UPDATE contactperson SET
			            updated = now(),
			            updatedBy='".$variables->loggID."',
			            name='".$o_main->db->escape_str($_POST['first_name'])."',
			            middlename='".$o_main->db->escape_str($_POST['middle_name'])."',
			            lastname='".$o_main->db->escape_str($_POST['last_name'])."',
			            email='".$o_main->db->escape_str(trim($_POST['email']))."',
			            mobile='".$o_main->db->escape_str($_POST['mobile'])."',
			            mobile_prefix='".$o_main->db->escape_str($_POST['mobile_prefix'])."',
		                position = '".$o_main->db->escape_str($_POST['position_id'])."',
		                title = '".$o_main->db->escape_str($_POST['job_title'])."'
			            WHERE id = $peopleId";

						$o_query = $o_main->db->query($sql);
						if($o_query)
						{
							$triggerGetynetUpdate = true;
						}
					} else {
	                    $fw_error_msg = array($formText_EmailExistAlready_output);
					}
		        } else {
					$validPerson = true;
					if(trim($_POST['email']) != ""){
						$validPerson = false;
		                $sql = "SELECT * FROM contactperson WHERE email = ? AND type = ?";
		            	$o_query = $o_main->db->query($sql, array(trim($_POST['email']), $people_contactperson_type));
		                $checkPeople = $o_query ? $o_query->row_array() : array();
		                if($checkPeople){
		                   if($checkPeople['content_status'] == 2) {
							   if($checkPeople['deactivated']){
							   		$fw_error_msg = array($formText_UserWithThisEmailIsInactive_output);
							   } else {
		                       	$fw_error_msg = array($formText_UserWithThisEmailIsDeleted_output);
								}
		                   } else {
		                      	$fw_error_msg = array($formText_EmailAlreadyHasBeenRegistered_output);
		                   }
					   } else {
						   $validPerson = true;
					   }
					}
					if($validPerson){
						if($customer_id > 0){
							$people_contactperson_type = 1;
						}
	    	            if('' === $_POST['mobile_prefix']) $_POST['mobile_prefix'] = '+47';
						$sql = "INSERT INTO contactperson SET
	    	            created = now(),
	    	            createdBy='".$variables->loggID."',
	    	            name='".$o_main->db->escape_str($_POST['first_name'])."',
	    	            middlename='".$o_main->db->escape_str($_POST['middle_name'])."',
	    	            lastname='".$o_main->db->escape_str($_POST['last_name'])."',
	    	            email='".$o_main->db->escape_str(trim($_POST['email']))."',
	    	            mobile='".$o_main->db->escape_str($_POST['mobile'])."',
	    	            mobile_prefix='".$o_main->db->escape_str($_POST['mobile_prefix'])."',
	                    position = '".$o_main->db->escape_str($_POST['position_id'])."',
						type = '".$o_main->db->escape_str($people_contactperson_type)."',
						customerId = '".$o_main->db->escape_str($customer_id)."',
		                title = '".$o_main->db->escape_str($_POST['job_title'])."'";

	    				$o_query = $o_main->db->query($sql);
						if($o_query){
							$updated = true;
						}
	    	            $insert_id = $o_main->db->insert_id();
	                    $peopleId = $insert_id;
	    	            $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list";
	                }
	            }
				$synced = true;

				if($synced){
					if($triggerGetynetUpdate){
						if(!$notEditable && (trim($peopleData['email']) != trim($_POST['email'])))
						{
							$o_query = $o_main->db->query("SELECT * FROM accountinfo");
							$v_accountinfo = $o_query ? $o_query->row_array() : array();
							$v_param = array(
								'username' => trim($peopleData['email']),
								'new_username' => trim($_POST['email'])
							);
							APIconnectorAccount("user_image_upload_change_username", $v_accountinfo['accountname'], $v_accountinfo['password'], $v_param);
							APIconnectorAccount("group_user_change_username", $v_accountinfo['accountname'], $v_accountinfo['password'], $v_param);
						}
						if($notEditable && !$notEditableOther)
						{
							if($companyCanEditProfile) {
								if('' === $_POST['mobile_prefix']) $_POST['mobile_prefix'] = '+47';
								$v_param = array(
									'NAME' => $_POST['first_name'],
									'MIDDLE_NAME' => $_POST['middle_name'],
									'LAST_NAME' => $_POST['last_name'],
									'MOBILE' => $_POST['mobile'],
									'MOBILE_PREFIX' => $_POST['mobile_prefix'],
									'ID'=> $member['user_id']
								);
								$v_response = json_decode(APIconnectorAccount("userinfoset", $v_accountinfo['accountname'], $v_accountinfo['password'], $v_param), TRUE);

								if(isset($v_response['status']) && $v_response['status'] == 1) {
									$fw_redirect_url = $_POST['redirect_url'];
									$updated = true;
								}
							} else {
								if('' === $_POST['mobile_prefix']) $_POST['mobile_prefix'] = '+47';
								$v_param = array(
									'NAME' => $_POST['first_name'],
									'MIDDLE_NAME' => $_POST['middle_name'],
									'LAST_NAME' => $_POST['last_name'],
									'MOBILE' => $_POST['mobile'],
									'MOBILE_PREFIX' => $_POST['mobile_prefix'],
									'chat_notifications' => $_POST['chat_notifications']
								);
								$v_response = json_decode(APIconnectorUser("userprofileset", $_COOKIE['username'], $_COOKIE['sessionID'], $v_param), TRUE);
								if(isset($v_response['status']) && $v_response['status'] == 1)
								{
									$fw_redirect_url = $_POST['redirect_url'];
									$updated = true;
								}
							}
						} else {
							$updated = true;
							$fw_redirect_url = $_POST['redirect_url'];
						}
					}
				} else {
					if(count($oldPeople) == 0){
						$s_sql = "DELETE FROM contactperson where id = ?";
						$o_query = $o_main->db->query($s_sql, array($peopleId));
					} else {
						$o_main->db->set($oldPeople);
						$o_main->db->where('id', $oldPeople['id']);
						$o_query = $o_main->db->update("contactperson");
					}
					$fw_error_msg[] = $formText_ErrorUpdatingPersonOnAccount_output . " ".$v_employee_accountconfig['linked_crm_account']." ".$response['message'];
					return;
				}
				if($updated){
					$o_main->db->query("UPDATE accountinfo SET force_cache_refresh = NOW()");

					$fw_return_data['content_id'] = $peopleId;
					$fw_return_data['crm_customer_id'] = $customer_id;

				} else {
					$fw_error_msg[] = $formText_ErrorUpdatingEntry_output;
					return;
				}
			} else {
				$fw_error_msg[] = $formText_EnterValidEmail_output;
				return;
			}
		} else {
			$fw_error_msg[] = $formText_MissingName_output;
			return;
		}
	}
}

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
?>

<div class="popupform popupform-<?php echo $peopleId;?>">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editPeople";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="cid" value="<?php echo $peopleId;?>">
		<input type="hidden" name="customer_id" value="<?php echo $customer_id;?>">
		<?php if(isset($_POST['from_owncompany'])) { ?>
		<input type="hidden" name="from_owncompany" value="1">
		<?php } ?>
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$peopleId; ?>">
        <div class="popupformTitle"><?php if($peopleData){ echo $formText_UpdatePerson_output; } else{ echo $formText_AddNewPerson_output;}?></div>
        <div class="inner">
			<?php
			if($notEditable && $notEditableOther){
				if(!$companyCanEditProfile) {
					echo $formText_UserHaveNotGivenAccessForThisCompanyToEditHisProfileDataYouNeedToContactTheUserAndTellToGiveYouTheAccessOrToUpdateTheProfileSelf_output."<br/><br/>";
				}
			}
			?>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_FirstName_Output; ?></div>
				<div class="lineInput">
                    <?php if($notEditable && $notEditableOther){
						echo ($v_user_external['name']);
					} else { ?>
	                   <input class="popupforminput botspace" name="first_name" type="text" value="<?php echo $v_user_external['name'];?>" required autocomplete="off">
					<?php } ?>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_MiddleName_Output; ?></div>
				<div class="lineInput">
                    <?php if($notEditable && $notEditableOther){
						echo ($v_user_external['middle_name']);
					} else { ?>
					   <input class="popupforminput botspace" name="middle_name" type="text" value="<?php echo $v_user_external['middle_name'];?>" autocomplete="off">
					<?php } ?>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_LastName_Output; ?></div>
				<div class="lineInput">
                    <?php if($notEditable && $notEditableOther){
						echo ($v_user_external['last_name']);
					} else { ?>
					    <input class="popupforminput botspace" name="last_name" type="text" value="<?php echo $v_user_external['last_name'];?>" autocomplete="off">
					<?php } ?>
				</div>
				<div class="clear"></div>
			</div>
    		<?php if($people_accountconfig['activateJobTitle'] > 0) { ?>
    			<div class="line">
            		<div class="lineTitle"><?php echo $formText_JobTitle_Output; ?></div>
            		<div class="lineInput">
                        <input type="text" class="popupforminput botspace" name="job_title" value="<?php echo $peopleData['title']; ?>" autocomplete="off"/>
                    </div>
            		<div class="clear"></div>
        		</div>
            <?php } ?>

    		<?php if($people_accountconfig['activatePosition'] > 0) { ?>
    			<div class="line">
            		<div class="lineTitle"><?php echo $formText_Position_Output; ?></div>
            		<div class="lineInput">
                        <?php /*?><input type="text" class="popupforminput botspace" name="job_title" value="<?php echo $peopleData['title']; ?>" autocomplete="off"><?php */?>
						<select class="popupforminput botspace" name="position_id">
						<option value=""><?php echo $formText_ChoosePosition_Output;?></option>
						<?php
						$o_positon = $o_main->db->query("SELECT * FROM people_position WHERE content_status < 2 ORDER BY name ASC");
						if($o_positon && $o_positon->num_rows()>0)
						foreach($o_positon->result_array() as $v_position)
    					{
    						?><option value="<?php echo $v_position['id'];?>"<?php echo ($v_position['id'] == $peopleData['position']?' selected':'');?>><?php echo $v_position['name'];?></option><?php
    					}
    					?></select>
                    </div>
            		<div class="clear"></div>
        		</div>
            <?php } ?>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_Email_Output; ?></div>
        		<div class="lineInput">
					<?php if($notEditable){
						echo ($v_user_external['username']);
					} else { ?>
	                    <input type="text" class="popupforminput botspace" name="email" value="<?php echo $peopleData['email']; ?>" autocomplete="off"<?php echo (isset($_POST['from_owncompany'])?' required':'');?>>
					<?php } ?>
                </div>
        		<div class="clear"></div>
    		</div>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_Phone_Output; ?></div>
				<div class="lineInput output-ajax-dropdown">
                    <?php if($notEditable && $notEditableOther){ ?>
                        <?php echo $v_user_external['mobile_prefix'].$v_user_external['mobile']; ?>
                    <?php } else { ?>
                        <input type="text" name="mobile_prefix" class="popupforminput botspace ajax-search" value="<?php echo $v_user_external['mobile_prefix'];?>" placeholder="+47" autocomplete="new-mobile-prefix" data-script="get_mobile_prefix" style="width:30% !important;">
						<ul class="output-dropdown-list" style="margin-top:-10px;"></ul>
						<input type="text" class="popupforminput botspace" name="mobile" value="<?php echo $v_user_external['mobile']; ?>" autocomplete="off" style="width:69% !important;">
                    <?php } ?>
                </div>
        		<div class="clear"></div>
    		</div>
            <?php if($peopleData) {?>
    			<div class="line">
            		<div class="lineTitle"><?php echo $formText_ChatNotifications_Output; ?></div>
            		<div class="lineInput">
    					<select class="popupforminput botspace" name="chat_notifications"><?php
    					$v_values = array(
    						'0' => $formText_SendDailyOverview_Output,
    						'15'=> $formText_SendAfter15Minutes_Output,
    						'-1'=> $formText_NoSending_Output
    					);
    					foreach($v_values as $s_key => $s_value)
    					{
    						?><option value="<?php echo $s_key;?>"<?php echo ($s_key == $v_user_external['chat_notifications']?' selected':'');?>><?php echo $s_value;?></option><?php
    					}
    					?></select>
                    </div>
            		<div class="clear"></div>
        		</div>
            <?php } ?>
			<?php
			if($v_employee_accountconfig['linked_crm_account'] != "" && $v_employee_accountconfig['linked_crm_account_token'] != ""){
				if($customer_id > 0) {
					echo $formText_AccessWillBeSentOnceSaveButtonClicked_output;
				} ?>

			<?php } ?>
		</div>

		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close fw_button_not_filled_color"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" class="fw_button_color" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
		</div>
	</form>
</div>

<?php

$s_path = $variables->account_root_url;

$v_script = array(
  'modules/'.$module.'/output/elementsOutput/jquery.validate/jquery.validate.min.js',
);

foreach($v_script as $s_item)
{
  $l_time = filemtime(BASEPATH.$s_item);
  ?><script type="text/javascript" src="<?php echo $s_path.$s_item.'?v='.$l_time;?>"></script><?php
}

?>

<!-- <script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script> -->
<script type="text/javascript">

$(document).ready(function() {
    $(".popupform-<?php echo $peopleId;?> form.output-form").validate({
        ignore: [],
        submitHandler: function(form) {
            fw_loading_start();
            $("#popup-validate-message").html("");
            $.ajax({
                url: $(form).attr("action"),
                cache: false,
                type: "POST",
                dataType: "json",
                data: $(form).serialize(),
                success: function (json) {
					if(json.error !== undefined)
					{
						$.each(json.error, function(index, value){
							$("#popup-validate-message").append("<div>"+value+"</div>").show();
						});
						fw_click_instance = fw_changes_made = false;
						fw_loading_end();
					} else {
						<?php if(isset($_POST['from_owncompany'])) { ?>
						if(json.data !== undefined)
						{
							param = {};
							param.fwajax = 1;
							param.fw_nocss = 1;
							param.cid = json.data.content_id;
							param.accesslevel = 0;
							param.admin = 0;
							param.crm_customer_id = json.data.crm_customer_id;
							param.sendInvitation = 1;
							param.from_owncompany = 1;

							$.ajax({
								cache: false,
								type: 'POST',
								dataType: 'json',
								url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=send_invitation";?>',
								data: $.param(param),
								success: function(obj){
									fw_loading_end();
									if(json.redirect_url !== undefined)
									{
										out_popup.addClass("close-reload").data("redirect", json.redirect_url);
										out_popup.close();
									}
								}

							});
						}
						<?php } else { ?>
	                    if(json.redirect_url !== undefined)
	                    {
	                        out_popup.addClass("close-reload").data("redirect", json.redirect_url);
	                        out_popup.close();
	                    }
						fw_loading_end();
						<?php } ?>
					}
                }
            }).fail(function() {
                $(".popupform-<?php echo $peopleId;?> #popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                $(".popupform-<?php echo $peopleId;?> #popup-validate-message").show();
                $('.popupform-<?php echo $peopleId;?> #popupeditbox').css('height', $('.popupform-<?php echo $peopleId;?> #popupeditboxcontent').height());
                fw_loading_end();
            });
        },
        invalidHandler: function(event, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var message = errors == 1
                ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                $(".popupform-<?php echo $peopleId;?> #popup-validate-message").html(message);
                $(".popupform-<?php echo $peopleId;?> #popup-validate-message").show();
                $('.popupform-<?php echo $peopleId;?> #popupeditbox').css('height', $('#popupeditboxcontent').height());
            } else {
                $(".popupform-<?php echo $peopleId;?> #popup-validate-message").hide();
            }
            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
        },
        errorPlacement: function(error, element) {
            if(element.attr("name") == "customerId") {
                error.insertAfter(".popupform-<?php echo $peopleId;?> .selectCustomer");
            }
            if(element.attr("name") == "projectLeader") {
                error.insertAfter(".popupform-<?php echo $peopleId;?> .selectEmployee");
            }
            if(element.attr("name") == "projectOwner") {
                error.insertAfter(".popupform-<?php echo $peopleId;?> .selectOwner");
            }
        },
        messages: {
            customerId: "<?php echo $formText_SelectTheCustomer_output;?>",
            projectLeader: "<?php echo $formText_SelectProjectLeader_output;?>",
            projectOwner: "<?php echo $formText_SelectProjectOwner_output;?>"
        }
    });

    $(".popupform-<?php echo $peopleId;?> .selectCustomer").unbind("click").bind("click", function(){
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1};
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_customers";?>',
            data: _data,
            success: function(obj){
                fw_loading_end();
                $('#popupeditboxcontent2').html('');
                $('#popupeditboxcontent2').html(obj.html);
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                $("#popupeditbox2:not(.opened)").remove();
            }
        });
    })
    $(".popupform-<?php echo $peopleId;?> .selectEmployee").unbind("click").bind("click", function(){
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1, owner: 0};
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_employees";?>',
            data: _data,
            success: function(obj){
                fw_loading_end();
                $('#popupeditboxcontent2').html('');
                $('#popupeditboxcontent2').html(obj.html);
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                $("#popupeditbox2:not(.opened)").remove();
            }
        });
    })
    $(".popupform-<?php echo $peopleId;?> .selectOwner").unbind("click").bind("click", function(){
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1, owner: 1};
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_employees";?>',
            data: _data,
            success: function(obj){
                fw_loading_end();
                $('#popupeditboxcontent2').html('');
                $('#popupeditboxcontent2').html(obj.html);
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                $("#popupeditbox2:not(.opened)").remove();
            }
        });
    })
    $(".projectTypeSelect").change(function(){
    	if($(this).val() == 2 || $(this).val() == 3) {
    		$(".customerInputWrapper").show();
    	} else {
    		$(".customerInputWrapper").hide();
    		$(".selectCustomer").html('<?php echo $formText_SelectCustomer_Output;?>');
    		$("#customerId").val(0);
    	}
    })
    $(".projectTypeSelect").change();
});
var output_ajax_dropdown;
var output_ajax_dropdown_list;
var output_ajax_dropdown_timer;
$('.output-ajax-dropdown input.ajax-search').on('keyup',function(e){
	if($(this).val() != '')
	{
		window.clearTimeout(output_ajax_dropdown_timer);
		output_ajax_dropdown = this;
		output_ajax_dropdown_timer = window.setTimeout(output_init_ajax_dropdown, 200);
	}
});
$('.output-ajax-dropdown input.ajax-search').on('click',function(e){
	if($(this).is('.open')) return;
	if($(this).val() != '')
	{
		window.clearTimeout(output_ajax_dropdown_timer);
		output_ajax_dropdown = this;
		output_ajax_dropdown_timer = window.setTimeout(output_init_ajax_dropdown, 200);
	}
});
function output_init_ajax_dropdown()
{
	$(output_ajax_dropdown).addClass('open');
	var post = {
		text: $(output_ajax_dropdown).val()
	};
	output_ajax_dropdown_list = $(output_ajax_dropdown).closest('.output-ajax-dropdown').find('.output-dropdown-list');
	$(output_ajax_dropdown_list).html('<li class="spin"><span class="fa fa-spinner fa-spin"></span></li>').show();
	output_build_ajax_dropdown(post);
}
function output_build_ajax_dropdown(post)
{
	var _data = { fwajax: 1, fw_nocss: 1, owner: 1, text: post.text };
	$.ajax({
		method: "POST",
		dataType: 'json',
		url: "<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=";?>" + $(output_ajax_dropdown).data('script'),
		data: _data
	})
	.done(function(json){
		if(json.data != undefined && json.data.status != undefined && json.data.status == 1 && json.data.results != undefined)
		{
			$(output_ajax_dropdown_list).html('<div class="list-title"><?php echo $formText_ChooseFromList_Output;?><i class="fa fa-times" onClick="javascript:$(output_ajax_dropdown).removeClass(\'open\');$(this).parent().parent().hide()"></i></div>').show();
			$(json.data.results).each(function(idx, obj){
				var $liNode = $('<li>').text(obj.name).data('code', obj.code);
				$(output_ajax_dropdown_list).append($liNode);
			});
		} else {
			$(output_ajax_dropdown_list).html('<li class="nothing"><?php echo $formText_NothingHasBeenFound_Output;?></li>');
		}
		output_on_ajax_dropdown_result();
	});
}
function output_on_ajax_dropdown_result()
{
	$(output_ajax_dropdown_list).find("li").on('click', function(){
		var _this = this;
		if(!$(_this).is('.nothing'))
		{
			$(output_ajax_dropdown).val($(_this).data('code'));
		}
		$(output_ajax_dropdown).removeClass('open');
		$(_this).parent().hide();
	});
}
</script>
<style>

.invoiceEmail {
    display: none;
}

.addSubProject {
    margin-bottom: 10px;
}
</style>
