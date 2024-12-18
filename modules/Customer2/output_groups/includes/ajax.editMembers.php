<?php
$groupId = $_POST['groupId'] ? $o_main->db->escape_str($_POST['groupId']) : 0;
$action = $_POST['action'] ? $_POST['action'] : '';
$status = $_POST['status'] ? $_POST['status'] : 0;
if($groupId) {
	$sql = "SELECT p.* FROM contactperson_group p WHERE p.status = 1 AND p.id = ?";
	$o_query = $o_main->db->query($sql, array($groupId));
	$group_getynet_data = $o_query ? $o_query->row_array(): array();
}

if($group_getynet_data){
	$memberCount = 0;
	$memberIds = array();
	$sql = "SELECT p.* FROM contactperson_group_user p 	
	LEFT OUTER JOIN contactperson c ON c.id = p.contactperson_id
	JOIN customer cus ON cus.id = c.customerId AND cus.content_status < 2
	WHERE p.contactperson_group_id = ? AND (p.status = 0 OR p.status is null) AND (p.hidden = 0 OR p.hidden is null)";
	$o_query = $o_main->db->query($sql, array($group_getynet_data['id']));
	$members = $o_query ? $o_query->result_array(): array();
	$memberCount = count($members);
	foreach($members as $singlemember){
		array_push($memberIds, $singlemember['contactperson_id']);
	}
}
if($moduleAccesslevel > 10) {
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
			$fw_return_data = 1;
			return;
		} else {
			$fw_error_msg = array($formText_Error_output);
			return;
		}
	}
	if($action == "delete" && $group_getynet_data && $_POST['contactperson_id']) {
		// $o_main->db->query("UPDATE accountinfo SET force_cache_refresh = NOW()");
		$isAdmin = false;
		$adminCount = 0;
		foreach($members as $singlemember){
			if(mb_strtolower($_POST['contactperson_id']) == mb_strtolower($singlemember['contactperson_id'])){
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
			$o_query = $o_main->db->query("DELETE FROM contactperson_group_user WHERE contactperson_group_id = ? AND contactperson_id = ?", array($group_getynet_data['id'], $_POST['contactperson_id']));
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

	$response = json_decode(APIconnectorUser("companyaccessbycompanyidget", $variables->loggID, $variables->sessionID, array('COMPANY_ID'=>$companyID)));
	$v_membersystem = array();
	foreach($response->data as $writeContent)
	{
	    array_push($v_membersystem, $writeContent);
	}
	$memberSearch = $_POST['member_search'];
	$sql = "SELECT * FROM contactperson WHERE contactperson.type = 1 AND contactperson.name LIKE '%".$memberSearch."%' AND contactperson.content_status < 2 ORDER BY contactperson.name";
    $o_query = $o_main->db->query($sql);
    $peopleCountTotal = $o_query ? $o_query->num_rows() : 0;

    $sql = "SELECT * FROM contactperson WHERE contactperson.type = 1 AND contactperson.name LIKE '%".$memberSearch."%' AND contactperson.content_status < 2 ORDER BY contactperson.name LIMIT 50";
    $o_query = $o_main->db->query($sql);
    $people = $o_query ? $o_query->result_array() : array();


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

				<div class="memberExplanationRow"><?php echo $formText_CheckToAddAsMember_output;?> (<?php echo $formText_Showing_output." ".count($people)." ".$formText_of_output." ".$peopleCountTotal?>)</div>
                <div class="">
                    <table class="gtable">
                        <tr>
                            <th>&nbsp;</th>
                            <th><?php echo $formText_Name_output;?></th>
                            <th><?php echo $formText_Email_output;?></th>
                        </tr>
						<?php foreach($people as $v_row) {
							$nameToDisplay = $v_row['name']." ".$v_row['middlename']." ".$v_row['lastname'];
							$imgToDisplay = "";
							$currentMember = "";
							$people_getynet_id = "";
							foreach($v_membersystem as $member){
								if(mb_strtolower($member->username) == mb_strtolower($v_row['email'])){
									$info = json_decode(APIconnectorUser("userdetailsget", $variables->loggID, $variables->sessionID, array('USER_ID'=>$member->registeredID)),true);

									$currentMember = $member;
									if($info['image'] != "" && $info['image'] != null){
						                $imgToDisplay = json_decode($info['image'],true);
						            }
						            if($info['name'] != ""){
						                $nameToDisplay = $info['name'] . " ". $info['middle_name']." ".$info["last_name"];
						            }
									break;
								}
							}
							?>
							<tr>
	                            <td><input type="checkbox" value="<?php echo $v_row['id']?>" class="memberEditCheckbox" <?php if(in_array($v_row['id'], $memberIds)) echo 'checked'?>/></td>
	                            <td><?php echo $nameToDisplay;?></td>
	                            <td><?php echo $v_row['email'];?></td>
							</tr>
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
    <script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
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
			var contactpersonId = $(this).val();
			if($(this).is(":checked")){
				var action = "add";
			} else {
				var action = "delete";
			}
			var data = {
				action: action,
				contactperson_id: contactpersonId,
	            groupId: "<?php echo $group_getynet_data['id'];?>"
	        };
			$("#popup-validate-message").html("");
	        ajaxCall({module_file:'editMembers', module_name: 'Customer2', module_folder: 'output_groups'}, data, function(json) {
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
    textarea {
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
    </style>
<?php } ?>
