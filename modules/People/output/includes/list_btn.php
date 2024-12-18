<div class="p_headerLine"><?php
if($moduleAccesslevel > 10)
{
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

	include_once(__DIR__."/person_init.php");
	include(__DIR__."/../../output_groups/includes/department_and_group_get.php");


	?>
	<?php
	if(!$v_employee_basisconfig['hide_people_tab']){
		?>
		<div class="btnStyle peopleBtn fw_tab_color <?php if($_GET['folder'] == "output" && $_GET['list_filter'] != "deleted" && $s_inc_obj == "") echo 'active';?>">
			<div class="plusTextBox">
				<div class="text"><b><?php echo $peopleCount; ?></b> <?php if($s_module_local_name && $variables->developeraccess <= 5){ echo $s_module_local_name;} else { echo $formText_People_Output; } ?></div>
			</div>
			<div class="clear"></div>
		</div>
		<?php
	}
	?>
	<?php if($v_employee_basisconfig['activate_persons_tab']){ ?>
		<div class="btnStyle personListBtn person_list fw_tab_color <?php if($_GET['folder'] == "output" && $s_inc_obj == "person_list") echo 'active';?>">
			<div class="plusTextBox">
				<div class="text"><b><?php echo $peopleCount; ?></b> <?php echo $formText_Persons_Output; ?></div>
			</div>
			<div class="clear"></div>
		</div>
	<?php } ?>
	<?php if($v_employee_basisconfig['activate_companies_tab']){ ?>
		<div class="btnStyle companiesBtn companies fw_tab_color <?php if($_GET['folder'] == "output" && $s_inc_obj == "companies") echo 'active';?>">
			<div class="plusTextBox">
				<div class="text"><b><?php echo $crmCustomersCount; ?></b> <?php echo $formText_Companies_Output; ?></div>
			</div>
			<div class="clear"></div>
		</div>
	<?php } ?>


	<?php
	if(!$v_employee_basisconfig['hide_other_users_tab']){
		if($variables->useradmin) {
			// $response = json_decode(APIconnectorUser("groupcompanyaccessbymoduleidget", $variables->loggID, $variables->sessionID, array('COMPANY_ID'=>$companyID, 'MODULE_ID'=>'0')), true);
			// $otherUsersCount = count($response['data']);
			$otherUsersCount = 0;
			foreach($v_membersystem_un as $v_membersystem_single) {
				$s_sql = "SELECT * FROM contactperson WHERE email = ? AND type = ?";
				$o_result = $o_main->db->query($s_sql, array($v_membersystem_single['username'], $people_contactperson_type));
				$people = $o_result ? $o_result->row_array() : array();

				if(!$people){
					$otherUsersCount++;
				}
			}
			?>
			<div class="btnStyle otherUsersBtn other_users_with_access fw_tab_color <?php if($_GET['folder'] == "output" && $s_inc_obj == "other_users_with_access") echo 'active';?>">
				<div class="plusTextBox">
					<div class="text"><b><?php echo $otherUsersCount; ?></b> <?php echo $formText_OtherUsersWithAccess_Output; ?></div>
				</div>
				<div class="clear"></div>
			</div>
		<?php } ?>
	<?php } ?>

	<?php
	if($l_inactive_people_count > 0)
	{
		?>
		<div class="btnStyle inactivePeopleBtn fw_tab_color <?php if($_GET['folder'] == "output" && $_GET['list_filter'] == 'inactive') echo 'active';?>">
			<div class="plusTextBox">
				<div class="text"><b><?php echo $l_inactive_people_count; ?></b> <?php echo $formText_InactivePeople_Output;?></div>
			</div>
			<div class="clear"></div>
		</div>
	<?php } ?>
	<?php
	if(!$v_employee_basisconfig['hide_deleted_people_tab']){
		if($accessElementAllow_AddEditDeletePeople){?>
			<div class="btnStyle deletedPeopleBtn fw_tab_color <?php if($_GET['folder'] == "output" && $_GET['list_filter'] == 'deleted') echo 'active';?>">
				<div class="plusTextBox">
					<div class="text"><b><?php echo $deletedPeopleCount; ?></b> <?php echo $formText_Deleted_Output." "; if($s_module_local_name && $variables->developeraccess <= 5){ echo $s_module_local_name;} else { echo $formText_People_Output; } ?></div>
				</div>
				<div class="clear"></div>
			</div>
		<?php } ?>
	<?php } ?>
	<?php
	if($accessElementAllow_ViewDepartments) {
		if(!$v_employee_basisconfig['hide_department_tab']){ ?>
			<div class="btnStyle departmentBtn fw_tab_color <?php if($groupFolder && $_GET['department']) echo 'active';?>">
				<div class="plusTextBox">
					<div class="text"><b><?php echo $departmentCount; ?></b> <?php echo $formText_Department_Output; ?></div>
				</div>
				<div class="clear"></div>
			</div>
		<?php
		}
	}
	?>

	<?php if($v_employee_basisconfig['activate_group_tab']){ ?>
		<div class="btnStyle groupsBtn fw_tab_color <?php if($groupFolder && !$_GET['department']) echo 'active';?>">
			<div class="plusTextBox">
				<div class="text"><b><?php echo $groupCount; ?></b> <?php echo $formText_Groups_Output; ?></div>
			</div>
			<div class="clear"></div>
		</div>
	<?php } ?>

	<?php if($v_employee_basisconfig['activate_people_owncompany_tab']){ ?>
		<div class="btnStyle peopleOwnCompanyBtn people_owncompany fw_tab_color <?php if($_GET['folder'] == "output" && $s_inc_obj == "people_owncompany") echo 'active';?>">
			<div class="plusTextBox">
				<div class="text"><?php echo $formText_PersonOwncompany_Output; ?></div>
			</div>
			<div class="clear"></div>
		</div>
	<?php } ?>
	<?php if(!$v_employee_basisconfig['hide_access_groups_tab']){ ?>
		<?php
		if($variables->useradmin) {
			$response = json_decode(APIconnectorUser("groupcompanyaccessbymoduleidget", $variables->loggID, $variables->sessionID, array('COMPANY_ID'=>$companyID, 'MODULE_ID'=>'0')), true);
			$accessGroupCount = count($response['data']);
			?>
			<div class="btnStyle accessGroupsBtn access_group_list fw_tab_color <?php if($_GET['folder'] == "output" && $s_inc_obj == "access_group_list") echo 'active';?>">
				<div class="plusTextBox">
					<div class="text"><b><?php echo $accessGroupCount; ?></b> <?php echo $formText_AccessGroups_Output; ?></div>
				</div>
				<div class="clear"></div>
			</div>
		<?php } ?>
	<?php } ?>

	<?php

	$o_query = $o_main->db->get('accountinfo');
	$v_accountinfo = $o_query ? $o_query->row_array() : array();
	if($v_accountinfo['activate_crm_user_content_filtering_tags']){
		if($v_accountinfo['crm_account_url'] != "" && $v_accountinfo['crm_access_token'] != ""  && $v_accountinfo['crm_account_module'] != "" ){
			if($variables->useradmin) { ?>
				<div class="" style="float: right; margin-right: 20px;">
					<label><?php echo $formText_Tags_output;?></label>
					<select class="tagViewFilter" autocomplete="off">
						<option value=""><?php echo $formText_All_output;?></option>
						<?php
						$s_sql = "SELECT * FROM crm_user_content_filtering_tags_for_admin";
						$o_query = $o_main->db->query($s_sql);
						$admin_tag_info = $o_query ? $o_query->row_array() : array();
						if($admin_tag_info){
							$tags = json_decode($admin_tag_info['tags'], true);
							$tagsToRead = $tags['tags'];
							$groupsToRead = $tags['groups'];
						}
						foreach($tagsToRead as $tagToRead) {
							?>
							<option value="<?php echo $tagToRead['id']?>" <?php if($tag_view_filter == $tagToRead['id']) echo 'selected'; ?>><?php echo $tagToRead['name'];?></option>
							<?php
						}
						?>
					</select>
				</div>
			<?php } ?>
		<?php }
	} ?>
	<div class="clear"></div>
	<?php

	if($variables->developeraccess >= 5) {
    ?>

        <a style="margin-left: 10px; float: right;" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folder=output_view_table&folderfile=output&fullsize=1"; ?>" target="_blank"><?php echo $formText_ViewTable_output?></a>

        <!-- <div class="view_table btnStyle">
            <div class="plusTextBox active">
                <div class="text"><?php echo $formText_ViewTable_output; ?></div>
                <div class="clear"></div>
            </div>
        </div> -->
        <?php
    }
}
?></div>


<script type="text/javascript">
<?php
if($v_employee_accountconfig['default_tab'] != "") {
	?>
	$(".p_headerLine .btnStyle.<?php echo $v_employee_accountconfig['default_tab'];?>").prependTo(".p_headerLine");
	<?php
}
?>
$(".peopleBtn").on('click', function(e){
	e.preventDefault();
    fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list&tag_view_filter="; ?>'+$(".tagViewFilter").val(), false, true);
});
$(".departmentBtn").on('click', function(e){
	e.preventDefault();
    fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_groups&inc_obj=list&department=1&tag_view_filter="; ?>'+$(".tagViewFilter").val(), false, true);
});
$(".groupsBtn").on('click', function(e){
	e.preventDefault();
    fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_groups&inc_obj=list&tag_view_filter="; ?>'+$(".tagViewFilter").val(), false, true);
});
$(".inactivePeopleBtn").on('click', function(e){
	e.preventDefault();
    fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list&list_filter=inactive&tag_view_filter="; ?>'+$(".tagViewFilter").val(), false, true);
});
$(".deletedPeopleBtn").on('click', function(e){
	e.preventDefault();
    fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list&list_filter=deleted&tag_view_filter="; ?>'+$(".tagViewFilter").val(), false, true);
});
$(".accessGroupsBtn").on('click', function(e){
	e.preventDefault();
    fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=access_group_list&tag_view_filter="; ?>'+$(".tagViewFilter").val(), false, true);
});
$(".otherUsersBtn").on('click', function(e){
	e.preventDefault();
    fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=other_users_with_access&tag_view_filter="; ?>'+$(".tagViewFilter").val(), false, true);
});
$(".personListBtn").on('click', function(e){
	e.preventDefault();
    fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=person_list&tag_view_filter="; ?>'+$(".tagViewFilter").val(), false, true);
});
$(".companiesBtn").on('click', function(e){
	e.preventDefault();
    fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=companies&tag_view_filter="; ?>'+$(".tagViewFilter").val(), false, true);
});
$(".peopleOwnCompanyBtn").on('click', function(e){
	e.preventDefault();
    fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=people_owncompany&tag_view_filter="; ?>'+$(".tagViewFilter").val(), false, true);
});

$(".tagViewFilter").change(function(e){
	e.preventDefault();
	var data = {
		inc_obj: '<?php echo $inc_obj;?>',
		tag_view_filter: $('.tagViewFilter').val()
	};
	loadView("list",data);
})

</script>
<style>
	.p_headerLine .btnStyle.addEditCustomerGroup {
		margin-left: 40px;
	}
	.p_headerLine .btnStyle.addEditSelfDefinedFields {
		margin-left: 40px;
	}
	.tagViewFilter {
		max-width: 200px;
		margin-top: 10px;
	}
</style>
