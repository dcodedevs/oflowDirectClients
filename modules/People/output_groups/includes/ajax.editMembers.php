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

if(!function_exists("filter_email_by_domain")) include_once(__DIR__."/fnc_filter_email_by_domain.php");
$groupId = isset($_POST['groupId']) ? $o_main->db->escape_str($_POST['groupId']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : 0;
if($groupId) {
	$sql = "SELECT p.* FROM contactperson_group p WHERE p.status = 1 AND p.id = ?";
	$o_query = $o_main->db->query($sql, array($groupId));
	$group_getynet_data = $o_query ? $o_query->row_array(): array();
}

if($group_getynet_data){
	$memberCount = 0;
	$memberIds = array();
	$sql = "SELECT p.* FROM contactperson_group_user p WHERE p.contactperson_group_id = ? AND (p.status = 0 OR p.status is null) AND (p.hidden = 0 OR p.hidden is null)";
	$o_query = $o_main->db->query($sql, array($group_getynet_data['id']));
	$members = $o_query ? $o_query->result_array(): array();
	$memberCount = count($members);
	foreach($members as $singlemember){
		array_push($memberIds, $singlemember['contactperson_id']);
	}

	$adminIds = array();
	$sql = "SELECT p.* FROM contactperson_group_user p WHERE p.contactperson_group_id = ? AND p.`type` = 2";
	$o_query = $o_main->db->query($sql, array($group_getynet_data['id']));
	$v_rows = $o_query ? $o_query->result_array(): array();
	foreach($v_rows as $v_row){
		array_push($adminIds, $v_row['contactperson_id']);
	}
}
if($moduleAccesslevel > 10) {
	if('set_admin' == $action && $group_getynet_data && $_POST['contactperson_id'])
	{
		$sql = "SELECT p.* FROM contactperson_group_user p WHERE p.contactperson_id = ? AND p.contactperson_group_id = ?";
		$o_query = $o_main->db->query($sql, array($_POST['contactperson_id'], $group_getynet_data['id']));
		if($o_query && $o_query->num_rows()>0)
		{
			$o_query = $o_main->db->query("UPDATE contactperson_group_user SET
				updated = NOW(),
				updatedBy = ?,
				type = 2,
				status = 0
				WHERE contactperson_group_id = ? AND contactperson_id = ?", array($variables->loggID, $group_getynet_data['id'], $_POST['contactperson_id']));
		} else {
			$o_query = $o_main->db->query("INSERT INTO contactperson_group_user SET
				created = NOW(),
				createdBy = ?,
				contactperson_group_id = ?,
				contactperson_id = ?,
				type = 2,
				status = 0", array($variables->loggID, $group_getynet_data['id'], $_POST['contactperson_id']));
		}
	}
	if('unset_admin' == $action && $group_getynet_data && $_POST['contactperson_id'])
	{
		$o_query = $o_main->db->query("UPDATE contactperson_group_user SET type = 1 WHERE contactperson_group_id = ? AND contactperson_id = ?", array($group_getynet_data['id'], $_POST['contactperson_id']));
	}
	if($action == "add" && $group_getynet_data) {
		// $o_main->db->query("UPDATE accountinfo SET force_cache_refresh = NOW()");
		$o_query = $o_main->db->query("INSERT INTO contactperson_group_user SET
			created = NOW(),
			createdBy = ?,
			contactperson_group_id = ?,
			contactperson_id = ?,
			type = 1,
			status = 0", array($variables->loggID, $group_getynet_data['id'], $_POST['contactperson_id']));
		if($o_query){
			$sql = "SELECT p.* FROM contactperson p WHERE p.id = ?";
			$o_query = $o_main->db->query($sql, array($_POST['contactperson_id']));
			$peopleData = $o_query ? $o_query->row_array() : array();
			if($peopleData['email'] != ""){
				$s_push_notification_title = $variables->accountinfo['push_notification_title'];

				if($s_push_notification_title == '')
				{
					$s_response = APIconnectorAccount('accountcompanyinfoget', $variables->accountinfo['accountname'], $variables->accountinfo['password']);
					$v_response = json_decode($s_response, TRUE);
					if(isset($v_response['status']) && $v_response['status'] == 1)
					{
						$v_company = $v_response['data'];
						$s_push_notification_title = $v_company['companyname'];
					}
				}
				if($s_push_notification_title == '') $s_push_notification_title = $variables->accountinfo['accountname'];

				$s_response = APIconnectorUser('userinfoget', $variables->loggID, $variables->sessionID, array('SEARCH_USERNAME'=>$peopleData['email'], 'COMPANY_ID'=>$_GET['companyID'], 'get_push_notification_tokens'=>1));
				$v_response = json_decode($s_response, TRUE);
				if(isset($v_response['status']) && $v_response['status'] == 1)
				{
					$v_user = $v_response['data'];
				}

				$s_response = APIconnectorUser('userprofileget', $variables->loggID, $variables->sessionID);
				$v_response = json_decode($s_response, TRUE);
				if(isset($v_response['status']) && $v_response['status'] == 1)
				{
					$v_post_user = $v_response['data'];
				}

				$s_message = html_entity_decode($v_post_user['name'].' '.$formText_haveAddedYouToGroup_Output.' '.$group_getynet_data['name']);
				$s_sql_field = '';
				$v_parameters = array();
				$o_query = $o_main->db->query("SELECT id FROM notificationcenter WHERE receiver_user_id = '".$o_main->db->escape_str($v_user['userID'])."' AND (is_seen IS NULL OR is_seen = 0)");
				$l_notification_count = ($o_query ? $o_query->num_rows() : 0) + 1;
				foreach($v_user['push_notification_tokens'] as $v_token)
				{
					if(substr($v_token['app_name'], 0, 8) != 'insider_') continue;
					$v_param_item = array(
						'to' => $v_token['token'],
						'title' => html_entity_decode($s_push_notification_title),
						'body' => $s_message,
						'badge' => $l_notification_count
					);
					// Send in one batch one app tokens only
					if(!isset($v_parameters[$v_token['app_name']])) $v_parameters[$v_token['app_name']] = array();
					$v_parameters[] = $v_param_item;
				}
				$s_mobile_response = '';
				foreach($v_parameters as $v_param)
				{
					if(count($v_param)>0)
					{
						$s_url = 'https://exp.host/--/api/v2/push/send';
						$ch = curl_init($s_url);
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($v_param));
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
						curl_setopt($ch, CURLOPT_HTTPHEADER, array(
							/*'host: exp.host',
							'accept: application/json',
							'accept-encoding: gzip, deflate',
							'Content-Type: application/json'*/
							'accept: application/json',
							'content-type: application/json'
						));

						$s_response = curl_exec($ch);
						$s_mobile_response .= $s_response;
						$v_response = json_decode($s_response, TRUE);
						if(isset($v_response['data']))
						{
							//$s_sql_field = ", mobile_response = 'ok'";
							foreach($v_response['data'] as $v_resp)
							{
								//if($v_resp['status'] != 'ok')
							}
						}
					}
				}
				$s_sql_field = ", mobile_response = '".$o_main->db->escape_str($s_mobile_response)."'";

				$o_main->db->query("INSERT INTO notificationcenter SET created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."', text = '".$o_main->db->escape_str($s_message)."', created_by_user_id = '".$o_main->db->escape_str($variables->userID)."', receiver_user_id = '".$o_main->db->escape_str($v_user['userID'])."', content_table = 'group_page', content_id = '".$o_main->db->escape_str($group_getynet_data['id'])."'".$s_sql_field);
			}
			$fw_return_data = 1;
			return;
		} else {
			$fw_error_msg = array($data['error']);
			return;
		}
	}
	if($action == "delete" && $group_getynet_data && $_POST['contactperson_id']) {

		$sql = "SELECT p.* FROM contactperson_group_user p WHERE p.contactperson_id = ? AND p.contactperson_group_id = ?";
		$o_query = $o_main->db->query($sql, array($_POST['contactperson_id'], $group_getynet_data['id']));
		$connection_data = $o_query ? $o_query->row_array(): array();

		$isAdmin = false;
		$adminCount = 0;
		foreach($members as $singlemember){
			if(mb_strtolower($connection_data['contactperson_id']) == mb_strtolower($singlemember['contactperson_id'])){
				$isMember = true;
				if($singlemember['type'] == 2){
					$isAdmin = true;
				}
			}
			if($singlemember['type'] == 2){
				$adminCount++;
			}
		}

		if(($adminCount > 1 && $isAdmin) || !$isAdmin){
			$o_query = $o_main->db->query("DELETE FROM contactperson_group_user WHERE id = ?", array($connection_data['id']));
			if($o_query){
				$fw_return_data = 1;
				return;
			} else {
				$fw_error_msg = array($formText_Error_output);
				return;
			}
		} else {
			$fw_error_msg = array($formText_CanNotDeleteLastAdministrator_output);
			return;
		}
	}
}
if($group_getynet_data){

	$s_sql = "select * from people_accountconfig";
	$o_query = $o_main->db->query($s_sql);
	$v_employee_accountconfig = ($o_query ? $o_query->row_array() : array());

	$sql = "SELECT * FROM people_basisconfig ORDER BY id";
	$o_query = $o_main->db->query($sql);
	$v_employee_basisconfig = $o_query ? $o_query->row_array() : array();

	foreach($v_employee_accountconfig as $key=>$value){
	    if($value > 0){
	        $v_employee_basisconfig[$key] = ($value - 1);
	    }
	}
	$cp_sql_where = "";
	if($v_employee_basisconfig['show_only_persons_marked_to_show_in_intranet'] == 1){
		$cp_sql_where .= " AND contactperson.show_in_intranet = 1";
	}

	$memberSearch = $_POST['member_search'];
	$sql = "SELECT * FROM contactperson WHERE contactperson.content_status < 2 AND contactperson.type = ".$people_contactperson_type."
	AND (contactperson.notVisibleInMemberOverview = 0 OR contactperson.notVisibleInMemberOverview is null) ".$cp_sql_where." ORDER BY contactperson.name";
	$o_query = $o_main->db->query($sql);
	$people = $o_query ? $o_query->result_array() : array();

	$response = json_decode(APIconnectorUser("companyaccessbycompanyidget", $variables->loggID, $variables->sessionID, array('COMPANY_ID'=>$companyID, 'GET_GROUPS'=>1)));
	$v_membersystem = array();
	$v_registered_usernames = array();
	foreach($response->data as $writeContent)
	{
		$v_membersystem[$writeContent->username] = $writeContent;
		if($writeContent->registeredID > 0) $v_registered_usernames[] = $writeContent->username;
	}
	$v_not_registered_usernames = array();
	foreach($people as $v_row)
	{
		if(!in_array($v_row['email'], $v_registered_usernames) && $v_row['email'] != "") $v_not_registered_usernames[] = $v_row['email'];
	}
	$v_not_registered_images = array();

    ?>

    <div class="popupform popupform-<?php echo $groupId;?>">
    	<div id="popup-validate-message" style="display:none;"></div>
    	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&folderfile="
		.$_GET['folderfile']."&folder=".$_GET['folder']."&inc_obj=ajax&inc_act=".$_GET['inc_act'];?>" method="post">
    		<input type="hidden" name="fwajax" value="1">
    		<input type="hidden" name="fw_nocss" value="1">
    		<input type="hidden" name="output_form_submit" value="1">
    		<input type="hidden" name="groupId" value="<?php echo $group_getynet_data['id'];?>">
            <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_groups&inc_obj=list"; ?>">
			<div class="popupformTitle"><?php  echo $formText_AddMembers_output; ?></div>

			<div class="inner">
                <div class="memberSearchRow">
                    <input type="text" autocomplete="off" name="member_search" placeholder="<?php echo $formText_SearchAfterEmployee_output;?>" value="<?php echo $memberSearch?>"/>
                </div>
                <div class="memberCountRow"><?php echo $formText_Members_output?> <span class="memberCount"><?php echo $memberCount;?></span></div>
				<div class="memberExplanationRow"><?php echo $formText_CheckToAddAsMember_output;?></div>
                <div class="">
                    <table class="gtable">
                        <tr class="gtable_row">
                            <th class="gtable_cell gtable_cell_head">&nbsp;</th>
                            <th class="gtable_cell gtable_cell_head"><?php echo $formText_Name_output;?></th>
							<?php if($people_contactperson_type) { ?>
                            	<th class="gtable_cell gtable_cell_head"><?php echo $formText_Company_output;?></th>
							<?php } ?>
                            <th class="gtable_cell gtable_cell_head"><?php echo $formText_Email_output;?></th>
                            <th class="gtable_cell gtable_cell_head"><?php echo $formText_Department_output;?></th>
                            <th class="gtable_cell gtable_cell_head"><?php echo $formText_Admin_output;?></th>
                        </tr>
						<?php foreach($people as $v_row) {

							$b_is_member = in_array($v_row['id'], $memberIds);
							$b_is_admin = $b_is_member && in_array($v_row['id'], $adminIds);
							$nameToDisplay = $v_row['name']." ".$v_row['middlename']." ".$v_row['lastname'];
							$imgToDisplay = "";
							$currentMember = "";
							$people_getynet_id = "";
							$b_registered_user = FALSE;

							if(isset($v_membersystem[$v_row['email']]))
							{
								$currentMember = $member = $v_membersystem[$v_row['email']];
								// $info = json_decode(APIconnectorUser("userdetailsget", $variables->loggID, $variables->sessionID, array('USER_ID'=>$member->registeredID)),true);
								if($member->registeredID > 0)
								{
									$b_registered_user = TRUE;
									if($member->image != "" && $member->image != null){
										$imgToDisplay = json_decode($member->image, TRUE);
									}
									if($member->first_name != ""){
										$nameToDisplay = $member->first_name . " ". $member->middle_name." ".$member->last_name;
									}
									if($member->mobile != "") {
										$phoneToDisplay = $member->mobile;
									}
								}
							}
							$nameToDisplay = trim(preg_replace('!\s+!', ' ', $nameToDisplay));

							if($memberSearch == "" || strpos(mb_strtolower($nameToDisplay), mb_strtolower($memberSearch)) !== false || strpos(filter_email_by_domain($v_row['email']), $memberSearch) !== false) {
								$sql = "SELECT p.* FROM contactperson_group p
								JOIN contactperson_group_user pu ON pu.contactperson_group_id = p.id
								JOIN contactperson ON contactperson.id = pu.contactperson_id
								WHERE p.status = 1 AND p.department = 1 AND contactperson.id = ? ORDER BY p.name";
								$o_query = $o_main->db->query($sql, array($v_row['id']));
								$departments = $o_query ? $o_query->result_array(): array();

								$sql = "SELECT p.* FROM contactperson_group p
								JOIN contactperson_group_user pu ON pu.contactperson_group_id = p.id
								JOIN contactperson ON contactperson.id = pu.contactperson_id
								WHERE p.status = 1 AND (p.department = 0 OR p.department is null) AND contactperson.id = ? ORDER BY p.name";
								$o_query = $o_main->db->query($sql, array($v_row['id']));
								$groups = $o_query ? $o_query->result_array(): array();

								$v_row['groups'] = $groups;
								$v_row['departments'] = $departments;

								$sql = "SELECT c.* FROM customer c
								JOIN contactperson ON contactperson.customerId = c.id
								WHERE contactperson.id = ? ORDER BY c.name";
								$o_query = $o_main->db->query($sql, array($v_row['id']));
								$customer = $o_query ? $o_query->row_array(): array();
							?>
							<tr class="gtable_row<?php echo ($b_is_member?' active_member':'');?>">
	                            <td class="gtable_cell"><input type="checkbox" value="<?php echo $v_row['id']?>" class="memberEditCheckbox" <?php if($b_is_member) echo 'checked'?>/></td>
	                            <td class="gtable_cell"><?php echo $nameToDisplay;?></td>
								<?php if($people_contactperson_type) { ?>
	                            	<td class="gtable_cell"><?php echo $customer['name']." ".$customer['middlename']." ".$customer['lastname'];?></td>
								<?php } ?>
	                            <td class="gtable_cell"><?php echo filter_email_by_domain($v_row['email']);?></td>
								<td class="gtable_cell">
									<?php
								$departmentShown = 1;
								foreach($v_row['departments'] as $group) {
									?>
									<div class="<?php if($departmentShown > 3) echo 'extraRow';?>"><?php echo is_object($group) ? $group->name : $group['name'];?></div>
									<?php
									$departmentShown++;
								}?>
								<?php if(count($v_row['departments']) > 3) { ?>
									<div class="seeAllDepartments seeElements fw_text_link_color view-changer"><?php echo $formText_SeeAll_output;?>(<?php echo count($v_row['departments']);?>)</div>
									<div class="hideAllDepartments hideElements fw_text_link_color view-changer"><?php echo $formText_Hide_output;?></div>
								<?php } ?>
								</td>
								<td class="gtable_cell"><input type="checkbox" value="<?php echo $v_row['id']?>" class="memberAdminCheckbox"<?php if(!$b_is_member) echo ' style="display:none;"'; if($b_is_admin) echo ' checked'?>/></td>
							</tr>
							<?php } ?>
						<?php
						} ?>
                    </table>
                </div>
    		</div>
    		<div class="popupformbtn">
    			<button type="button" class="output-btn b-large b-close fw_button_not_filled_color"><?php echo $formText_Close_Output;?></button>
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
        $(".popupform-<?php echo $groupId;?> form.output-form").validate({
            ignore: [],
            submitHandler: function(form) {
				$("#popup-validate-message").html("");
                fw_loading_start();
                $.ajax({
                    url: $(form).attr("action"),
                    cache: false,
                    type: "POST",
                    dataType: "json",
                    data: $(form).serialize(),
                    success: function (data) {
                        fw_loading_end();
                        if(data.redirect_url !== undefined)
                        {
                            out_popup.addClass("close-reload").data("redirect", data.redirect_url);
                            out_popup.close();
                        } else {
							$('#popupeditboxcontent').html('');
					        $('#popupeditboxcontent').html(data.html);
					        out_popup = $('#popupeditbox').bPopup(out_popup_options);
					        $("#popupeditbox:not(.opened)").remove();
						}
                    }
                }).fail(function() {
                    $(".popupform-<?php echo $groupId;?> #popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                    $(".popupform-<?php echo $groupId;?> #popup-validate-message").show();
                    $('.popupform-<?php echo $groupId;?> #popupeditbox').css('height', $('.popupform-<?php echo $groupId;?> #popupeditboxcontent').height());
                    fw_loading_end();
                });
            },
            invalidHandler: function(event, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    var message = errors == 1
                    ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                    : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                    $(".popupform-<?php echo $groupId;?> #popup-validate-message").html(message);
                    $(".popupform-<?php echo $groupId;?> #popup-validate-message").show();
                    $('.popupform-<?php echo $groupId;?> #popupeditbox').css('height', $('#popupeditboxcontent').height());
                } else {
                    $(".popupform-<?php echo $groupId;?> #popup-validate-message").hide();
                }
                setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
            },
            errorPlacement: function(error, element) {
                if(element.attr("name") == "customerId") {
                    error.insertAfter(".popupform-<?php echo $groupId;?> .selectCustomer");
                }
                if(element.attr("name") == "projectLeader") {
                    error.insertAfter(".popupform-<?php echo $groupId;?> .selectEmployee");
                }
                if(element.attr("name") == "projectOwner") {
                    error.insertAfter(".popupform-<?php echo $groupId;?> .selectOwner");
                }
            },
            messages: {
                customerId: "<?php echo $formText_SelectTheCustomer_output;?>",
                projectLeader: "<?php echo $formText_SelectProjectLeader_output;?>",
                projectOwner: "<?php echo $formText_SelectProjectOwner_output;?>"
            }
        });

        $(".memberEditCheckbox").change(function(){
			var checkbox = $(this);
			var contactperson_id = $(this).val();
			if($(this).is(":checked")){
				var action = "add";
			} else {
				var action = "delete";
			}
			var data = {
				action: action,
				contactperson_id: contactperson_id,
	            groupId: "<?php echo $group_getynet_data['id'];?>"
	        };
			$("#popup-validate-message").html("");
	        ajaxCall({module_file:'editMembers', module_name: 'People', module_folder: 'output_groups'}, data, function(json) {
				if(json.error !== undefined)
				{
					$.each(json.error, function(index, value){
						$("#popup-validate-message").append("<div>"+value+"</div>").show();
					});
					fw_click_instance = fw_changes_made = false;
					if(action == "add"){
						checkbox.prop("checked", false);
					} else if(action == "delete"){
						checkbox.prop("checked", true);
					}
				} else {
					updateMemberCount();
					if(action == "add"){
						checkbox.closest('tr').addClass('active_member').find('input.memberAdminCheckbox').prop("checked", false).show();
					} else if(action == "delete"){
						checkbox.closest('tr').removeClass('active_member').find('input.memberAdminCheckbox').prop("checked", false).hide();
					}
				}
	        });
        });
		$(".memberAdminCheckbox").change(function(){
			var checkbox = $(this);
			var contactperson_id = $(this).val();
			if($(this).is(":checked")){
				var action = "set_admin";
			} else {
				var action = "unset_admin";
			}
			var data = {
				action: action,
				contactperson_id: contactperson_id,
	            groupId: "<?php echo $group_getynet_data['id'];?>"
	        };
			$("#popup-validate-message").html("");
	        ajaxCall({module_file:'editMembers', module_name: 'People', module_folder: 'output_groups'}, data, function(json) {
				if(json.error !== undefined)
				{
					$.each(json.error, function(index, value){
						$("#popup-validate-message").append("<div>"+value+"</div>").show();
					});
					fw_click_instance = fw_changes_made = false;
					if(action == "set_admin"){
						checkbox.prop("checked", false);
					} else if(action == "unset_admin"){
						checkbox.prop("checked", true);
					}
				} else {
					updateMemberCount();
				}
	        });
        });

		function updateMemberCount(){
			$(".memberCount").html($(".memberEditCheckbox:checked").length);
		}
		$('#popupeditbox').addClass("close-reload");

		$(".seeAllDepartments").on("click", function(){
			$(this).parents("td").toggleClass("active");
		})
		$(".hideAllDepartments").on("click", function(){
			$(this).parents("td").toggleClass("active");
		})
    });

    </script>
    <style>
	tr.active_member td {
		background-color:#e3f4fa;
	}
	#popupeditbox, #popupeditbox2 {
		min-width: 1024px;
	}
	.memberSearchRow input {
		width: 100%;
		padding: 8px 15px;
		border: 1px solid #cecece;
	}
	.memberSearchRow {
		margin-bottom: 15px;
	}
	.memberCountRow {
		margin-bottom: 10px;
	}
	.memberExplanationRow {
		margin-bottom: 10px;
		font-weight: bold;
	}
    .lineInput .otherInput {
        margin-top: 10px;
    }
    .lineInput input[type="radio"]{
        margin-right: 10px;
        vertical-align: middle;
    }
    .lineInput input[type="radio"] + label {
        margin-right: 10px;
        vertical-align: middle;
    }
    .popupform .inlineInput input.popupforminput {
        display: inline-block;
        width: auto;
        vertical-align: middle;
        margin-right: 20px;
    }
    .popupform .inlineInput label {
        display: inline-block !important;
        vertical-align: middle;
    }
    .popupform .lineInput.lineWhole {
    	font-size: 14px;
    }
    .popupform .lineInput.lineWhole label {
    	font-weight: normal !important;
    }
    .selectDivModified {
        display:block;
    }
    .invoiceEmail {
        display: none;
    }
    label.error {
        color: #c11;
        margin-left: 10px;
        border: 0;
        display: inline !important;
    }
    .popupform .popupforminput.error { border-color:#c11 !important;}
    #popup-validate-message, .error-msg { font-weight:bold; color:#c11; padding-bottom:10px; }
    /* css for timepicker */
    .ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
    .ui-timepicker-div dl { text-align: left; }
    .ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
    .ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
    .ui-timepicker-div td { font-size: 90%; }
    .ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
    .clear {
    	clear:both;
    }
    .inner {
    	padding:10px;
    }
    .pplineV {
    	position:absolute;
    	top:0;bottom:0;left:70%;
    	border-left:1px solid #e8e8e8;
    }
    .popupform input.popupforminput, .popupform textarea.popupforminput, .popupform select.popupforminput, .col-md-8z input {
    	width:100%;
    	border-radius: 4px;
    	padding:5px 10px;
    	font-size:12px;
    	line-height:17px;
    	color:#3c3c3f;
    	background-color:transparent;
    	-webkit-box-sizing: border-box;
    	   -moz-box-sizing: border-box;
    		 -o-box-sizing: border-box;
    			box-sizing: border-box;
    	font-weight:400;
    	border: 1px solid #cccccc;
    }
    .popupformname {
    	font-size:12px;
    	font-weight:bold;
    	padding:5px 0px;
    }
    .popupforminput.botspace {
    	margin-bottom:10px;
    }
    .popupform textarea {
    	min-height:50px;
    	max-width:100%;
    	min-width:100%;
    	width:100%;
    }
    .popupformname {
    	font-weight: 700;
    	font-size: 13px;
    }
    .popupformbtn {
    	text-align:right;
    	margin:10px;
    }
    .popupformbtn input {
    	border-radius:4px;
    	border:1px solid #0393ff;
    	background-color:#0393ff;
    	font-size:13px;
    	line-height:0px;
    	padding: 20px 35px;
    	font-weight:700;
    	color:#FFF;
    	margin-left:10px;
    }
    .error {
    	border: 1px solid #c11;
    }
    .popupform .lineTitle {
    	font-weight:700;
    }
    .popupform .line .lineTitle {
    	width:30%;
    	float:left;
    	font-weight:700;
    	padding:5px 0;
    }

    .popupform .line .lineTitleWithSeperator {
        width:100%;
        margin: 20px 0;
        padding:0 0 10px;
        border-bottom:1px solid #EEE;
    }

    .popupform .line .lineInput {
    	width:70%;
    	float:left;
    }
    .addSubProject {
        margin-bottom: 10px;
    }
	.popupform .gtable .gtable_cell {
		vertical-align: top;
		padding: 5px 0px;
	}
	.popupform .gtable tr:hover .td {
		background: #fcfbfb;
	}
	.popupform .gtable td .extraRow {
		display: none;
	}
	.popupform .gtable td .seeElements {
		display: block;
		cursor: pointer;
	}
	.popupform .gtable td .hideElements {
		display: none;
		cursor: pointer;
	}
	.popupform .gtable td.active .extraRow {
		display: block;
	}
	.popupform .gtable td.active .seeElements {
		display: none;
	}
	.popupform .gtable td.active .hideElements {
		display: block;
	}
    </style>
<?php } ?>
