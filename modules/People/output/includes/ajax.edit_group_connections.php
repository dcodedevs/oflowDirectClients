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

$sql = "SELECT p.* FROM contactperson p WHERE p.email = ? AND p.content_status < 2 AND p.type = ?";
$o_query = $o_main->db->query($sql, array($variables->loggID, array($people_contactperson_type)));
$currentContactPerson = $o_query ? $o_query->row_array(): array();

$department_sql = " AND (p.department is null OR p.department = 0)";
if($_POST['department']){
    $department_sql = " AND p.department = 1";
}

$sql = "SELECT p.* FROM contactperson_group p WHERE p.content_status < 2 ".$department_sql;
$o_query = $o_main->db->query($sql);
$userGroupsInit = $o_query ? $o_query->result_array(): array();
foreach($userGroupsInit as $userGroup) {
	$isAdmin = false;
	$sql = "SELECT pu.type FROM contactperson_group_user pu WHERE pu.contactperson_id = ? AND pu.contactperson_group_id = ? AND pu.type = 2";
	$o_query = $o_main->db->query($sql, array($currentContactPerson['id'], $userGroup["id"]));
	$user_connections = $o_query ? $o_query->result_array(): array();
	if(count($user_connections) > 0){
		$isAdmin = true;
	}
	if($userGroup['all_admins_are_group_admins']){
		$isAdmin=true;
	}
	if($isAdmin){
		$userGroups[] = $userGroup;
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
        <input type="hidden" name="contactperson_id" value="<?php echo $_POST['contactperson_id']?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list"; ?>">
        <div class="popupformTitle"><?php  if($_POST['department']){ echo $formText_AddUserToDepartments_output;  } else { echo $formText_AddUserToGroups_output; } ?></div>

        <div class="inner">
            <div class="line">
				<div class="lineTitle"><?php if($_POST['department']){ echo $formText_Department_output; } else { echo $formText_Group_Output; }?></div>
				<div class="lineInput">
                    <?php
                    foreach($userGroups as $userGroup){
                        $sql = "SELECT p.* FROM contactperson_group_user p WHERE p.contactperson_id = ? AND p.contactperson_group_id = ?";
                    	$o_query = $o_main->db->query($sql, array($_POST['contactperson_id'], $userGroup['id']));
                    	$userGroupConnection = $o_query ? $o_query->row_array(): array();
                        ?>
						<div>
	                        <input type="checkbox" id="group<?php echo $userGroup['id']?>" class="popupforminput botspace checkbox memberEditCheckbox" <?php if($userGroupConnection) echo 'checked';?> value="<?php echo $userGroup['id']; ?>" autocomplete="off">
	                        <label for="group<?php echo $userGroup['id']?>"><?php echo $userGroup['name'];?></label>
						</div>
                        <?php
                    }
                    ?>
				</div>
				<div class="clear"></div>
			</div>

    		<div class="popupformbtn">
    			<button type="button" class="output-btn b-large b-close fw_button_not_filled_color"><?php echo $formText_Close_Output;?></button>
    		</div>
        </div>
    </form>
</div>
<style>
.popupform input.popupforminput.checkbox,
.popupform textarea.popupforminput.checkbox,
.popupform select.popupforminput.checkbox,
.col-md-8z input.checkbox {
    width: auto;
    display: inline-block;
}
</style>
<script type="text/text/javascript">
$(".memberEditCheckbox").change(function(){
    var checkbox = $(this);
    var contactperson_id = '<?php echo $_POST['contactperson_id']?>';
    if($(this).is(":checked")){
        var action = "add";
    } else {
        var action = "delete";
    }
    var data = {
        contactperson_id: contactperson_id,
        groupId: $(this).val(),
        action: action
    };
    $("#popup-validate-message").html("");
    ajaxCall({module_file:'editMembers', module_name: 'People', module_folder: 'output_groups'}, data, function(json) {
        if(json.error !== undefined)
        {
            $.each(json.error, function(index, value){
                $("#popup-validate-message").append("<div>"+value+"</div>").show();
            });
            fw_click_instance = fw_changes_made = false;
            if(action == "add") {
                checkbox.prop("checked", false);
            } else if(action == "delete") {
                checkbox.prop("checked", true);
            }
        } else {
            out_popup.addClass("close-reload");
        }
    });
});
</script>
