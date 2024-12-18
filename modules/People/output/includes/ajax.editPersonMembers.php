<?php
if(!function_exists("filter_email_by_domain")) include_once(__DIR__."/fnc_filter_email_by_domain.php");
$customer_id = isset($_POST['customer_id']) ? $o_main->db->escape_str($_POST['customer_id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : 0;

if($moduleAccesslevel > 10) {
	// $viewExist = false;
	// $s_sql = "select * from contactperson_crm limit 1";
	// $o_query = $o_main->db->query($s_sql);
	// $contactpersonView = $o_query ? $o_query->row_array() : array();
	// if($contactpersonView){
	// 	$viewExist = true;
	// }

	if($action == "add" && $customer_id && isset($_POST['contactpersonId']) && $_POST['contactpersonId']) {

		$s_sql = "SELECT * FROM people_crm_contactperson_connection WHERE people_id = ? AND crm_customer_id = ?";
		$o_result = $o_main->db->query($s_sql, array($_POST['people_id'], $customer_id));
		$people_crm_contactperson_connection = $o_result ? $o_result->row_array() : array();
		if(!$people_crm_contactperson_connection) {
			$s_sql = "INSERT INTO people_crm_contactperson_connection SET created = now(), createdBy = ?, people_id = ?, crm_customer_id = ?";
			$o_result = $o_main->db->query($s_sql, array($variables->loggID, $_POST['people_id'], $customer_id));
			if($o_result){
			} else {
				$fw_error_msg = array($formText_ErrorUpdatingEntry_output);
				return;
			}
		}

		$s_sql = "SELECT * FROM contactperson WHERE id = ?";
		$o_result = $o_main->db->query($s_sql, array($_POST['people_id']));
		$peopleData = $o_result ? $o_result->row_array() : array();

		$contactpersonData = array();
		$contactpersonData['email'] = $peopleData['email'];
		$contactpersonData['name'] = $peopleData['name'];
		$contactpersonData['middlename'] = $peopleData['middlename'];
		$contactpersonData['lastname'] = $peopleData['lastname'];
		$contactpersonData['mobile'] = $peopleData['phone'];
		$contactpersonData['customerId'] = $customer_id;

		if($oldPeople){
			$contactpersonData['updated'] = date("Y-m-d H:i:s");
		} else {
			$contactpersonData['created'] = date("Y-m-d H:i:s");
		}

		if($viewExist){
			include("update_contactperson.php");
			if($v_return['status'] == 1){
				$fw_return_data = 1;
			} else {
				$fw_error_msg[] = $formText_ErrorUpdatingPersonOnAccount_output . " ".$v_employee_accountconfig['linked_crm_account']." ".$v_return['message'];
				return;
			}
		} else {
			$params = array(
				'api_url' => $v_employee_accountconfig['linked_crm_account'].'/api',
				'access_token'=> $v_employee_accountconfig['linked_crm_account_token'], //hardcoded
				'module' => 'Customer2',
				'action' => 'update_contactperson_from_insider',
				'params' => array(
					'contactperson' => $contactpersonData,
					'customerId' => $customer_id,
					'contactpersonId' => 0
				)
			);
			$response = fw_api_call($params, false);
			if($response['status'] == 1) {
				$fw_return_data = 1;
			} else {
				$fw_error_msg[] = $formText_ErrorUpdatingPersonOnAccount_output . " ".$v_employee_accountconfig['linked_crm_account']." ".$response['message'];
				return;
			}
		}
	}
	if($action == "delete" && $customer_id && isset($_POST['contactpersonId'])) {
		$o_main->db->query("UPDATE accountinfo SET force_cache_refresh = NOW()");
		$isAdmin = false;

		$s_sql = "SELECT * FROM contactperson WHERE customerId = ? AND admin = 1";
		$o_result = $o_main->db->query($s_sql, array($customer_id));
		$contactPersonAdmins = $o_result ? $o_result->result_array() : array();
		$adminCount = count($contactPersonAdmins);
		foreach($contactPersonAdmins as $contactPersonAdmin) {
			if($contactPersonAdmin['id'] == $_POST['contactpersonId']) {
				$isAdmin = true;
			}
		}
		if(($adminCount > 1 && $isAdmin) || !$isAdmin){
			$_POST['from_owncompany'] = 1;
			$_POST['cid'] = $_POST['contactpersonId'];
			$_POST['hide_output'] = 1;
			include("ajax.remove_access.php");
			
			$s_sql = "UPDATE contactperson SET inactive = 1 WHERE id = ?";
			if(isset($people_accountconfig['activate_contactperson_delete']) && 1 == $people_accountconfig['activate_contactperson_delete'])
			{
				$s_sql = "DELETE FROM contactperson WHERE id = ?";
			}
			$o_result = $o_main->db->query($s_sql, array($_POST['contactpersonId']));
			if($o_result){
				
			} else {
				$fw_error_msg = array($formText_ErrorDeletingEntry_output);
				return;
			}
		} else {
			$fw_error_msg = array($formText_CanNotDeleteLastAdministrator_output);
			return;
		}
	}
}
if($customer_id){
	$memberIds = array();
	
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

	$s_sql = "SELECT * FROM people_crm_contactperson_connection WHERE crm_customer_id = ?";
	$o_result = $o_main->db->query($s_sql, array($customer_id));
	$people_crm_contactperson_connections = $o_result ? $o_result->result_array() : array();
	foreach($people_crm_contactperson_connections as $people_crm_contactperson_connection_single) {
		array_push($memberIds, $people_crm_contactperson_connection_single['people_id']);
	}
	$memberSearch = $_POST['member_search'];
	$sql = "SELECT * FROM contactperson WHERE type = '".$o_main->db->escape_str($people_contactperson_type)."' AND content_status < 2 ORDER BY name";
	$o_query = $o_main->db->query($sql);
	$people = $o_query ? $o_query->result_array() : array();

	$v_membersystem = array();
	$v_registered_usernames = array();
	$o_query = $o_main->db->query("SELECT * FROM cache_userlist_membershipaccess");
	$v_cache_userlist_membership = $o_query ? $o_query->result_array() : array();
	foreach($v_cache_userlist_membership as $v_user_cached_info) {
		$v_membersystem[$v_user_cached_info['username']] = $v_user_cached_info;
	    if($v_user_cached_info['user_id'] > 0) $v_registered_usernames[] = $v_user_cached_info['username'];
	}
	$o_query = $o_main->db->query("SELECT * FROM cache_userlist_access");
	$v_cache_userlist = $o_query ? $o_query->result_array() : array();
	foreach($v_cache_userlist as $v_user_cached_info) {
		$v_membersystem[$v_user_cached_info['username']] = $v_user_cached_info;
	    if($v_user_cached_info['user_id'] > 0) $v_registered_usernames[] = $v_user_cached_info['username'];
	}

	$v_not_registered_usernames = array();
	foreach($people as $v_row)
	{
		if(!in_array($v_row['email'], $v_registered_usernames)) $v_not_registered_usernames[] = $v_row['email'];
	}
	$v_not_registered_images = array();
	$not_registered_group_list = array();
	$registered_group_list = array();
	if(count($v_not_registered_usernames)>0)
	{
		$v_response = json_decode(APIconnectorUser("group_get_list_by_filter", $variables->loggID, $variables->sessionID, array('company_id'=>$companyID, 'usernames'=>$v_not_registered_usernames, 'not_hidden'=> 1)),true);
		if(isset($v_response['status']) && $v_response['status'] == 1)
		{
			$not_registered_group_list = $v_response['items'];
		}
	}

	if(count($v_registered_usernames)>0)
	{
		$v_response = json_decode(APIconnectorUser("group_get_list_by_filter", $variables->loggID, $variables->sessionID, array('company_id'=>$companyID, 'usernames'=>$v_registered_usernames, 'not_hidden'=>1)),true);
		if(isset($v_response['status']) && $v_response['status'] == 1)
		{
			$registered_group_list = $v_response['items'];
		}
	}
    ?>

    <div class="popupform popupform-<?php echo $groupId;?>">
    	<div id="popup-validate-message" style="display:none;"></div>
    	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&folderfile="
		.$_GET['folderfile']."&folder=".$_GET['folder']."&inc_obj=ajax&inc_act=".$_GET['inc_act'];?>" method="post">
    		<input type="hidden" name="fwajax" value="1">
    		<input type="hidden" name="fw_nocss" value="1">
    		<input type="hidden" name="output_form_submit" value="1">
    		<input type="hidden" name="customer_id" value="<?php echo $customer_id;?>">
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
                            <th class="gtable_cell gtable_cell_head"><?php echo $formText_Email_output;?></th>
                        </tr>
						<?php foreach($people as $v_row) {
							$nameToDisplay = $v_row['name']." ".$v_row['middlename']." ".$v_row['lastname'];
							$imgToDisplay = "";
							$currentMember = "";
							$people_getynet_id = "";
							$b_registered_user = FALSE;

							if(isset($v_membersystem[$v_row['email']]))
							{
								$currentMember = $member = $v_membersystem[$v_row['email']];
								if($member['user_id'] > 0)
								{
									$b_registered_user = TRUE;
									if($member['image'] != "" && $member['image'] != null){
										$imgToDisplay = json_decode($member['image'], TRUE);
									}
									if($member['first_name'] != ""){
										$nameToDisplay = $member['first_name'] . " ". $member['middle_name']." ".$member['last_name'];
									}
									if($member['mobile'] != "") {
										$phoneToDisplay = $member['mobile'];
									}
								}
							}
							$nameToDisplay = trim(preg_replace('!\s+!', ' ', $nameToDisplay));

							if($memberSearch == "" || strpos(mb_strtolower($nameToDisplay), mb_strtolower($memberSearch)) !== false) {

							?>
							<tr class="gtable_row">
	                            <td class="gtable_cell"><input type="checkbox" value="<?php echo $v_row['id']?>" class="memberEditCheckbox" <?php if(in_array($v_row['id'], $memberIds)) echo 'checked'?>/></td>
	                            <td class="gtable_cell"><?php echo $nameToDisplay;?></td>
	                            <td class="gtable_cell"><?php echo ($v_row['email']);?></td>
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
			var people_id = $(this).val();
			if($(this).is(":checked")){
				var action = "add";
			} else {
				var action = "delete";
			}
			var data = {
				action: action,
				customer_id: <?php echo $customer_id;?>,
				people_id: people_id,
	        };
			$("#popup-validate-message").html("");
	        ajaxCall({module_file:'editPersonMembers', module_name: 'People', module_folder: 'output'}, data, function(json) {
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
				}
	        });
        })

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
