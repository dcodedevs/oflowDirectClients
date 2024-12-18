<?php
if($accessElementAllow_AddEditDeletePeople){

	if(isset($_POST['cid'])){ $cid = $_POST['cid']; } else { $cid = NULL; }

	$s_sql = "SELECT contactperson.* FROM contactperson
	WHERE contactperson.id = ?";
	$o_query = $o_main->db->query($s_sql, array($cid));
	$v_data = $o_query ? $o_query->row_array() : array();

	if(isset($_POST['output_form_submit']))
	{
	    if(!function_exists("APIconnectorUser")) include_once(__DIR__."/../../../../fw/account_fw/includes/APIconnector.php");
	    $v_accountinfo_sql = $o_main->db->query("select * from accountinfo");
	    if($v_accountinfo_sql && $v_accountinfo_sql->num_rows() > 0) $v_accountinfo = $v_accountinfo_sql->row();

	    $v_membersystem_config_sql = $o_main->db->query("select * from people_stdmembersystem_basisconfig");
	    if($v_membersystem_config_sql && $v_membersystem_config_sql->num_rows() > 0) $v_membersystem_config = $v_membersystem_config_sql->row();

	    $l_homes_id = $v_data['id'];
	    $s_receiver_name = $v_data['name'];
	    $s_receiver_email = $v_data['email'];
	    $data = json_decode(APIconnectorUser("companyaccessget",$variables->loggID, $variables->sessionID, array('COMPANY_ID'=>$_GET['companyID'], 'USERNAME'=>$s_receiver_email, 'ACCESSID'=>$_GET['accessID'])),true);
	    $userAccess = $data['data'];
		$noError = true;
		if($userAccess){
		    $resultcompanyaccess = json_decode(APIconnectorUser("companyaccessdelete", $variables->loggID, $variables->sessionID, array('COMPANY_ID'=>$_GET['companyID'], 'USER_ID'=>$userAccess['id'])),true);
		    if(!array_key_exists('error',$resultcompanyaccess))
		    {
		        $data = json_decode(APIconnectorUser("departmentaccessdelete", $variables->loggID, $variables->sessionID, array('COMPANYACCESS_ID'=>$userAccess['id'],'COMPANYDEPARTMENT_ID'=>'')),true);

		    } else {
				$noError = false;
		    }
		}
		if($noError){
			$allGroups = array();
			$v_response = json_decode(APIconnectorUser("group_get_list_by_filter", $variables->loggID, $variables->sessionID, array('company_id'=>$_GET['companyID'], 'usernames'=>array($v_data['email']))),true);
			if(isset($v_response['status']) && $v_response['status'] == 1)
			{
				$deleted_user_group_list = $v_response['items'];
				$allGroups = $deleted_user_group_list[$v_data['email']];
			}

			foreach($allGroups as $single_group){
				$data = json_decode(APIconnectorUser("group_user_delete", $variables->loggID, $variables->sessionID, array('group_id'=>$single_group['id'],'username'=>$v_data['email'])),true);
				if($data['status']){
					$fw_return_data = 1;
				} else {
					$fw_error_msg = array($data['error']);
				}
			}
			if(count($fw_error_msg) == 0) {
				$sql = "UPDATE contactperson SET
				updated = now(),
				updatedBy='".$variables->loggID."',
				content_status = 2
				WHERE id = $cid";
				$o_query = $o_main->db->query($sql);

				// $peopleId - needed for sync script
				$peopleId = $cid;
				include("sync_people.php");
			}
		} else {
			$fw_error_msg = array($formText_ErrorRemovingAccess_output);
		}

	} else {
		?>
		<div class="profileEditForm popupform">
			<div id="popup-validate-message"></div>
			<form class="output-form" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=deletePeople";?>" method="post">
			<input type="hidden" name="fwajax" value="1">
			<input type="hidden" name="fw_nocss" value="1">
			<input type="hidden" name="output_form_submit" value="1">
			<input type="hidden" name="languageID" value="<?php echo $languageID?>">
			<input type="hidden" name="cid" value="<?php echo $cid?>">
			<div class="confirm-text">
				<?php echo $formText_AreYouSureYouWantToDeleteThisPerson_Output;?> (<?php echo $formText_AccessWillBeDeleted_Output;?>)
				<br/>
				<b><?php echo $v_data['email']; ?></b>
			</div>
			<div class="popupformbtn">
				<button type="button" class="output-btn b-large b-close fw_button_not_filled_color"><?php echo $formText_Cancel_Output;?></button>
				<input type="submit" class="fw_button_color" name="sbmbtn" value="<?php echo $formText_Delete_Output; ?>">
			</div>
		  </form>
		</div>
		<style>
		</style>

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
		$("form.output-form").validate({
			submitHandler: function(form){
				fw_loading_start();
				$.ajax({
					url: $(form).attr("action"),
					cache: false,
					type: "POST",
					dataType: "json",
					data: $(form).serialize(),
					success: function (data){
						fw_loading_end();
						if(data.error !== undefined)
						{
							var errorMessage = "";
							$.each(data.error, function(index, value){
								errorMessage += value+"<br/>";
							});
							$("#popup-validate-message").html(errorMessage, true);
							$("#popup-validate-message").show();
							$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
						} else {
							out_popup.close();
							var currentCount = 0;
							var data = {
								department_filter: $('.filterDepartment').val(),
								search_filter: $('.searchFilter').val(),
								list_filter: 'deleted',
								page: 1,
							};
							loadView('list', data);
						}
					}
				}).fail(function(){
					fw_loading_end();
					$("#popup-validate-message").html("<?php echo $formText_ErrorOccurredSavingContent_Output;?>", true);
					$("#popup-validate-message").show();
					$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
				});
			},
			invalidHandler: function(event, validator){}
		});
		</script>
		<?php
	}
} else {
	echo $formText_YouHaveNoAccess_Output;
} ?>
