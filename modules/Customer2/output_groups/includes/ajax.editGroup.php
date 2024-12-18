<?php
if(!function_exists("APIconnectorUser")) include_once(__DIR__."/../../../../fw/account_fw/includes/APIconnector.php");
$groupId = $_POST['groupId'] ? $o_main->db->escape_str($_POST['groupId']) : 0;
$action = $_POST['action'] ? $_POST['action'] : '';
$status = $_POST['status'] ? $_POST['status'] : 0;

if($groupId) {
	$sql = "SELECT p.* FROM contactperson_group p WHERE p.status = 1 AND p.id = ?";
	$o_query = $o_main->db->query($sql, array($groupId));
	$group_getynet_data = $o_query ? $o_query->row_array(): array();
}
$sql = "SELECT p.* FROM contactperson p WHERE p.email = ? AND p.content_status < 2";
$o_query = $o_main->db->query($sql, array($variables->loggID));
$currentContactPerson = $o_query ? $o_query->row_array(): array();

if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
		// $o_main->db->query("UPDATE accountinfo SET force_cache_refresh = NOW()");
		$account_id = $variables->account_id;
		if($account_id) {
	        if ($group_getynet_data) {

				$o_query = $o_main->db->query("UPDATE contactperson_group SET
					updated = NOW(),
					updatedBy = ?,
					name = ?,
					enable_page = ?,
					department = ?,
					show_group_to_all_in_group_page = ?,
					show_members_in_group_page = ?,
					show_group_to_all_in_group_list = ?,
					show_only_admins_in_group_list = ?,
					activate_memberlist_page = ?,
					activate_infopages_page = ?,
					activate_filearchive_page = ?,
					activate_picturegallery_page = ?,
					activate_activitycalendar_page = ?,
					activate_workboard_page = ?,
					activate_article_page = ?,
					display_posts_to_members = ?,
					editableForAllUserInCrm = ?,
					status = 1,
					group_type = 1
					WHERE id = ?", array($variables->loggID, $_POST['name'],$_POST['enable_page'], intval($_POST['department']), $_POST['show_group_to_all_in_group_page'],
					$_POST['show_members_in_group_page'], $_POST['show_group_to_all_in_group_list'], $_POST['show_only_admins_in_group_list'], $_POST['activate_memberlist_page'],
					$_POST['activate_infopages_page'], $_POST['activate_filearchive_page'], $_POST['activate_picturegallery_page'], $_POST['activate_activitycalendar_page'],
					$_POST['activate_workboard_page'],$_POST['activate_article_page'], $_POST['display_posts_to_members'], $_POST['editableForAllUserInCrm'], $group_getynet_data['id']));

				if($o_query){
		            $fw_redirect_url = $_POST['redirect_url'];
				}
	        } else {
				$o_query = $o_main->db->query("INSERT INTO contactperson_group SET
					created = NOW(),
					createdBy = ?,
					name = ?,
					enable_page = ?,
					department = ?,
					show_group_to_all_in_group_page = ?,
					show_members_in_group_page = ?,
					show_group_to_all_in_group_list = ?,
					show_only_admins_in_group_list = ?,
					activate_memberlist_page = ?,
					activate_infopages_page = ?,
					activate_filearchive_page = ?,
					activate_picturegallery_page = ?,
					activate_activitycalendar_page = ?,
					activate_workboard_page = ?,
					activate_article_page = ?,
					display_posts_to_members = ?,
					editableForAllUserInCrm = ?,
					status = 1,
					group_type = 1", array($variables->loggID, $_POST['name'],$_POST['enable_page'], intval($_POST['department']), $_POST['show_group_to_all_in_group_page'],
					$_POST['show_members_in_group_page'], $_POST['show_group_to_all_in_group_list'], $_POST['show_only_admins_in_group_list'], $_POST['activate_memberlist_page'],
					$_POST['activate_infopages_page'], $_POST['activate_filearchive_page'], $_POST['activate_picturegallery_page'], $_POST['activate_activitycalendar_page'],
					$_POST['activate_workboard_page'], $_POST['activate_article_page'], $_POST['display_posts_to_members'], $_POST['editableForAllUserInCrm']));
				if($o_query) {
					$group_id = $o_main->db->insert_id();
					if($currentContactPerson){
						$o_query = $o_main->db->query("INSERT INTO contactperson_group_user SET
							created = NOW(),
							createdBy = ?,
							contactperson_group_id = ?,
							contactperson_id = ?,
							type = 2,
							status = 0", array($variables->loggID, $group_id, $currentContactPerson['id']));
					}
					$fw_return_data = 1;
					$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_groups&inc_obj=list";
				} else {
					$fw_error_msg = array($formText_ErrorMissing_output);
					return;
				}
			}
		} else {
			$fw_error_msg = array($formText_AccountIdMissingPleaseContactSupport_output);
			return;
		}
	}
}

if($action == "delete" && $group_getynet_data) {
	// $o_main->db->query("UPDATE accountinfo SET force_cache_refresh = NOW()");
	$o_query = $o_main->db->query("DELETE contactperson_group, contactperson_group_user FROM contactperson_group LEFT OUTER JOIN contactperson_group_user ON contactperson_group_user.contactperson_group_id = contactperson_group.id WHERE contactperson_group.id = ?", array($group_getynet_data['id']));
	if($o_query) {
		$fw_return_data = 1;
	} else {
		$fw_error_msg = array($data['error']);
	}
}
?>

<div class="popupform popupform-<?php echo $groupId;?>">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_groups&inc_obj=ajax&inc_act=editGroup";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="department" value="<?php echo $_POST['department']?>">
		<input type="hidden" name="groupId" value="<?php echo $group_getynet_data['id'];?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_groups&inc_obj=list"; ?>">
		<div class="popupformTitle"><?php if($group_getynet_data){ if($_POST['department']) {  echo $formText_UpdateDepartment_output; } else { echo $formText_UpdateGroup_output;} } else { if($_POST['department']) { echo $formText_CreateNewDepartment_output; } else { echo $formText_CreateNewGroup_output; }}?></div>
		<div class="inner">
    		<div class="line">
        		<div class="lineTitle"><?php if($_POST['department']) { echo $formText_DepartmentName_output; } else { echo $formText_GroupName_Output; } ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="name" value="<?php echo $group_getynet_data['name']; ?>" required autocomplete="off">
                </div>
        		<div class="clear"></div>
    		</div>
			<div class="line">
        		<div class="lineTitle"><?php  echo $formText_EditableForAllUserInCrm_Output;  ?></div>
        		<div class="lineInput">
                    <input type="checkbox" class="popupforminput botspace checkbox" name="editableForAllUserInCrm" value="1" <?php if($group_getynet_data['editableForAllUserInCrm']) echo 'checked';?> autocomplete="off">
                </div>
        		<div class="clear"></div>
    		</div>

			<div class="popupSubtitle">
				<?php if($_POST['department']) { echo $formText_DepartmentListInPeopleModule_Output; } else { echo $formText_GroupListInPeopleModule_output; } ?>
			</div>
			<div class="popupSettingsRow">
				<div class="line checkboxLine">
					<div class="lineTitle"><?php if($_POST['department']) { echo $formText_ShowDepartmentToAll_Output; } else { echo $formText_ShowGroupToAll_Output;} ?></div>
					<div class="lineInput">
						<input type="checkbox" class="popupforminput botspace checkbox" name="show_group_to_all_in_group_list" value="1" <?php if($group_getynet_data['show_group_to_all_in_group_list']) echo 'checked'; ?> autocomplete="off">
					</div>
					<div class="clear"></div>
				</div>

				<div class="line">
					<div class="lineTitle"><?php echo $formText_ShowMembersInList_Output; ?></div>
					<div class="lineInput">
						<select name="show_only_admins_in_group_list">
							<option value="1" <?php if($group_getynet_data['show_only_admins_in_group_list'] == 1) echo 'selected'; ?>><?php echo $formText_OnlyAdmins_output;?></option>
							<option value="0" <?php if($group_getynet_data['show_only_admins_in_group_list'] == 0 || !$group_getynet_data) echo 'selected'; ?>><?php echo $formText_AllMembers_output;?></option>
						</select>
					</div>
					<div class="clear"></div>
				</div>
			</div>

			<div class="popupSubtitle">
				<?php if($_POST['department']) { echo $formText_DepartmentPageSetting_Output; } else { echo $formText_GroupPageSetting_output; }?>
			</div>
			<div class="line checkboxLine">
        		<div class="lineTitle"><?php if($_POST['department']) { echo $formText_DepartmentPage_Output; } else { echo $formText_GroupPage_Output; } ?></div>
        		<div class="lineInput">
                    <input type="checkbox" class="popupforminput botspace checkbox group_page" name="enable_page" value="1" <?php if($group_getynet_data['enable_page']) echo 'checked'; ?> autocomplete="off">
                </div>
        		<div class="clear"></div>
    		</div>
			<div class="grouppage_settings <?php if($group_getynet_data['enable_page']) echo 'active'; ?>">
				<div class="popupSettingsRow">
					<div class="line">
						<div class="lineTitle"><?php echo $formText_ShowMembers_Output; ?></div>
						<div class="lineInput">
							<select name="show_members_in_group_page">
								<option value="1" <?php if($group_getynet_data['show_members_in_group_page'] == 1) echo 'selected'; ?>><?php echo $formText_OnlyAdmins_output;?></option>
								<option value="2" <?php if($group_getynet_data['show_members_in_group_page'] == 2 || !$group_getynet_data) echo 'selected'; ?>><?php echo $formText_AllMembers_output;?></option>
							</select>
						</div>
						<div class="clear"></div>
					</div>
		    		<div class="line checkboxLine">
		        		<div class="lineTitle"><?php if($_POST['department']) { echo $formText_ShowDepartmentPageToAll_Output; } else { echo $formText_ShowGroupPageToAll_Output;} ?></div>
		        		<div class="lineInput">
		                    <input type="checkbox" class="popupforminput botspace checkbox" name="show_group_to_all_in_group_page" value="1" <?php if($group_getynet_data['show_group_to_all_in_group_page']) echo 'checked'; ?> autocomplete="off">
		                </div>
		        		<div class="clear"></div>
		    		</div>
				</div>
				<div class="line">
					<div class="lineTitle"><?php echo $formText_DisplayPostsToMembers_Output; ?></div>
					<div class="lineInput">
						<select name="display_posts_to_members" autocomplete="off">
							<option value="0" <?php if($group_getynet_data['display_posts_to_members'] == 0) echo 'selected'; ?>><?php echo $formText_BothInGroupPageAndMainFeed_output;?></option>
							<option value="1" <?php if($group_getynet_data['display_posts_to_members'] == 1) echo 'selected'; ?>><?php echo $formText_OnlyInGrouppage_output;?></option>
						</select>
					</div>
					<div class="clear"></div>
				</div>

				<div class="line checkboxLine">
	        		<div class="lineTitle"><?php echo $formText_ActivateInfopagesPage_output;?></div>
	        		<div class="lineInput">
	                    <input type="checkbox" class="popupforminput botspace checkbox" name="activate_infopages_page" value="1" <?php if($group_getynet_data['activate_infopages_page']) echo 'checked'; ?> autocomplete="off">
	                </div>
	        		<div class="clear"></div>
	    		</div>
				<div class="line checkboxLine">
	        		<div class="lineTitle"><?php echo $formText_ActivateFilearchivePage_output;?></div>
	        		<div class="lineInput">
	                    <input type="checkbox" class="popupforminput botspace checkbox" name="activate_filearchive_page" value="1" <?php if($group_getynet_data['activate_filearchive_page']) echo 'checked'; ?> autocomplete="off">
	                </div>
	        		<div class="clear"></div>
	    		</div>
				<div class="line checkboxLine">
	        		<div class="lineTitle"><?php echo $formText_ActivatePictureGalleryPage_output;?></div>
	        		<div class="lineInput">
	                    <input type="checkbox" class="popupforminput botspace checkbox" name="activate_picturegallery_page" value="1" <?php if($group_getynet_data['activate_picturegallery_page']) echo 'checked'; ?> autocomplete="off">
	                </div>
	        		<div class="clear"></div>
	    		</div>
				<div class="line checkboxLine">
	        		<div class="lineTitle"><?php echo $formText_ActivateActivityCalendarPage_output;?></div>
	        		<div class="lineInput">
	                    <input type="checkbox" class="popupforminput botspace checkbox" name="activate_activitycalendar_page" value="1" <?php if($group_getynet_data['activate_activitycalendar_page']) echo 'checked'; ?> autocomplete="off">
	                </div>
	        		<div class="clear"></div>
	    		</div>
				<div class="line checkboxLine">
	        		<div class="lineTitle"><?php echo $formText_ActivateWorkboardPage_output;?></div>
	        		<div class="lineInput">
	                    <input type="checkbox" class="popupforminput botspace checkbox" name="activate_workboard_page" value="1" <?php if($group_getynet_data['activate_workboard_page']) echo 'checked'; ?> autocomplete="off">
	                </div>
	        		<div class="clear"></div>
	    		</div>

				<div class="line checkboxLine">
	        		<div class="lineTitle"><?php echo $formText_ActivateArticlePage_output;?></div>
	        		<div class="lineInput">
	                    <input type="checkbox" class="popupforminput botspace checkbox" name="activate_article_page" value="1" <?php if($group_getynet_data['activate_article_page']) echo 'checked'; ?> autocomplete="off">
	                </div>
	        		<div class="clear"></div>
	    		</div>
			</div>
		</div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close fw_button_not_filled_color"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" class="fw_button_color" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
		</div>

	</form>
</div>
<div id="popupeditbox2" class="popupeditbox">
	<span class="button b-close fw_popup_x_color"><span>X</span></span>
	<div id="popupeditboxcontent2"></div>
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
<script type="text/javascript">

var hoveredOverInfo = false;
$(".popupChannelInfoTrigger").hover(function(){
	$(".popup_channelinfo_hover").addClass("active");
}, function(){
	setTimeout(function(){
		if(!hoveredOverInfo) {
			$(".popup_channelinfo_hover").removeClass("active");
		}
	}, 300)
});
$(".popup_channelinfo_hover").hover(function(){ hoveredOverInfo = true; }, function(){
	$(".popup_channelinfo_hover").removeClass("active");
	hoveredOverInfo = false;
})

var out_popup2;
var out_popup_options2={
	follow: [true, true],
	fadeSpeed: 0,
	followSpeed: 0,
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function(){
		$(this).addClass('opened');
		//$(this).find('.b-close').on('click', function(){out_popup.close();});
	},
	onClose: function(){
		if($(this).hasClass("close-reload")){
			var data = {
	            groupId: '<?php echo $group_getynet_data['id']; ?>',
				department: '<?php echo $_POST['department'];?>'
	        };
	        ajaxCall({module_file:'editGroup', module_name: 'Customer2', module_folder: 'output_groups'}, data, function(json) {
	            $('#popupeditboxcontent').html('');
	            $('#popupeditboxcontent').html(json.html);
	            out_popup = $('#popupeditbox').bPopup(out_popup_options);
	            $("#popupeditbox:not(.opened)").remove();
	        });
        }
		if($(this).hasClass("close-page-reload")){

        }
		$(this).removeClass('opened');
	}
};
$(document).off('click', '.output-edit-channel').on('click', '.output-edit-channel', function(e){
	e.preventDefault();
	var data = {
		cid: '<?php echo $group_getynet_data['id']; ?>',
		channel_id:$(this).data('id')
	};
	ajaxCall({module_file:'edit_channels', module_name: 'Customer2', module_folder: 'output_groups'}, data, function(json){
		$('#popupeditboxcontent2').html('');
		$('#popupeditboxcontent2').html(json.html);
		out_popup2 = $('#popupeditbox2').bPopup(out_popup_options2);
		$("#popupeditbox2:not(.opened)").remove();
	});
});

$(document).ready(function() {
    $(".popupform-<?php echo $groupId;?> form.output-form").validate({
        ignore: [],
        submitHandler: function(form) {
            fw_loading_start();
            $.ajax({
                url: $(form).attr("action"),
                cache: false,
                type: "POST",
                dataType: "json",
                data: $(form).serialize(),
                success: function (data) {
                    fw_loading_end();
					if(data.error !== undefined)
					{
						$.each(data.error, function(index, value){
							$("#popup-validate-message").append("<div>"+value+"</div>").show();
						});
						fw_click_instance = fw_changes_made = false;
					} else {
	                    if(data.redirect_url !== undefined)
	                    {
	                        out_popup.addClass("close-page-reload").data("redirect", data.redirect_url);
	                        out_popup.close();
	                    }
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

    $(".popupform-<?php echo $groupId;?> .selectCustomer").unbind("click").bind("click", function(){
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
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options2);
                $("#popupeditbox2:not(.opened)").remove();
            }
        });
    })
    $(".popupform-<?php echo $groupId;?> .selectEmployee").unbind("click").bind("click", function(){
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
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options2);
                $("#popupeditbox2:not(.opened)").remove();
            }
        });
    })
    $(".popupform-<?php echo $groupId;?> .selectOwner").unbind("click").bind("click", function(){
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
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options2);
                $("#popupeditbox2:not(.opened)").remove();
            }
        });
    })
	$(".popupform-<?php echo $groupId;?> .group_page").on("click", function(){
		var checked = $(this).is(":checked");
		if(checked){
			$(".popupform-<?php echo $groupId;?> .grouppage_settings").addClass("active");
		} else {
			$(".popupform-<?php echo $groupId;?> .grouppage_settings").removeClass("active");
		}
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

</script>
<style>
.popupChannelInfoTrigger {
	font-size: 13px;
	margin-left: 5px;
}
.popup_channelinfo_hover {
	font-size: 12px;
	position: absolute;
	width: 300px;
	background: #fff;
	border: 1px solid #eee;
	padding: 5px 10px;
	position: absolute;
	top: 20px;
	line-height: 18px;
	z-index: 10;
	font-weight: normal;
	left: 50px;
	display: none;
}
.popup_channelinfo_hover.active {
	display: block;
}
.add-channel {
	font-size: 12px;
	margin-left: 10px;
}
.popupform .popupSubtitle {
	position: relative;
	font-size: 16px;
	margin-top: 10px;
	margin-bottom: 10px;
}
.popupform .popupSettingsRow {
	margin-bottom: 15px;
}
.popupform .grouppage_settings {
	display: none;
	margin-bottom: 15px;
}
.popupform .grouppage_settings.active {
	display: block;
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
