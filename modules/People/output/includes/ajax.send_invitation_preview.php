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
if($accessElementAllow_GiveRemoveAccessPeople || isset($_POST['from_owncompany'])) {
	$s_email_template = "sendemail_standard";

	if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

	if(!function_exists("APIconnectorUser")) include_once(__DIR__."/../../../../fw/account_fw/includes/APIconnector.php");

	$v_accountinfo_sql = $o_main->db->query("select * from accountinfo");
	if($v_accountinfo_sql && $v_accountinfo_sql->num_rows() > 0) $v_accountinfo = $v_accountinfo_sql->row();


	$v_data = json_decode(APIconnectAccount("accountcompanyinfoget", $v_accountinfo->accountname, $v_accountinfo->password, array()),true);
	$companyinfo = $v_data['data'];

	$s_sql = "select * from contactperson where id = ?";
	$o_result = $o_main->db->query($s_sql, array($_POST['cid']));
	if($o_result && $o_result->num_rows() > 0) $v_row = $o_result->row();

	$l_contactperson_id = $v_row->id;

	$s_receiver_name = $v_row->name;

	$s_receiver_email = $v_row->email;

	$data = json_decode(APIconnectorUser("companyaccessget",$variables->loggID, $variables->sessionID, array('COMPANY_ID'=>$_GET['companyID'], 'USERNAME'=>$s_receiver_email)),true);
	$userAccess = $data['data'];
	//print $s_sql;
	if($s_receiver_email == "") {
		$s_receiver_email = $variables->loggID;
	}
	include(__DIR__."/../../".$s_email_template."/template.php");

	$_POST["folder"] = "sendemail_standard";



	// include(__DIR__."/../includes/readOutputLanguage.php");
	if(isset($_POST['from_owncompany'])){
		$o_query = $o_main->db->query("SELECT * FROM accountinfo_invitation_basisconfig WHERE name = '".$o_main->db->escape_str($v_employee_accountconfig['personowncompany_invitation_config'])."'");
		$v_invitation_config = $o_query ? $o_query->row_array() : array();
		$o_query = $o_main->db->query("SELECT * FROM accountinfo_invitation_accountconfig WHERE name = '".$o_main->db->escape_str($v_employee_accountconfig['personowncompany_invitation_config'])."'");
		if($o_query && $o_query->num_rows()>0)
		{
			$v_invitation_config = $o_query->row_array();
		}
	} else {
		if(intval($v_employee_basisconfig['invitation_setting']) == 1){
			$o_query = $o_main->db->query("SELECT * FROM accountinfo_invitation_basisconfig WHERE name = '".$o_main->db->escape_str($v_employee_basisconfig['invitation_config'])."'");
			$v_invitation_config = $o_query ? $o_query->row_array() : array();

			if($v_employee_accountconfig['invitation_config'] != ""){
				$o_query = $o_main->db->query("SELECT * FROM accountinfo_invitation_basisconfig WHERE name = '".$o_main->db->escape_str($v_employee_accountconfig['invitation_config'])."'");
				$v_invitation_config = $o_query ? $o_query->row_array() : array();
				$o_query = $o_main->db->query("SELECT * FROM accountinfo_invitation_accountconfig WHERE name = '".$o_main->db->escape_str($v_employee_accountconfig['invitation_config'])."'");
				if($o_query && $o_query->num_rows()>0)
				{
					$v_invitation_config = $o_query->row_array();
				}
			}
		}
	}
	$linkedToCrmAccount = false;
	if($v_employee_accountconfig['linked_crm_account'] != "" && $v_employee_accountconfig['linked_crm_account_token'] != ""){
		$linkedToCrmAccount = true;
	}
	if($v_invitation_config['id'] || $linkedToCrmAccount || intval($v_employee_basisconfig['invitation_setting']) == 0){
		if($linkedToCrmAccount){
			$s_sql = "select * from contactperson where id = ?";
			$o_result = $o_main->db->query($s_sql, array($_POST['cid']));
			$peopleItem = $o_result ? $o_result->row_array() : array();

			$params = array(
				'api_url' => $v_employee_accountconfig['linked_crm_account'].'/api',
				'access_token'=> $v_employee_accountconfig['linked_crm_account_token'],
				'module' => 'Customer2',
				'action' => 'get_invitation_from_owncompany_preview',
				'params' => array(
					'customerId' => $_POST['crm_customer_id'],
					'peopleItem' => $peopleItem,
					'caID' => $_GET['caID'],
					'companyID' => $_GET['companyID'],
					'username' => $_COOKIE['username'],
					'sessionID' => $_COOKIE['sessionID'],
					'accountlanguageID' => $fw_session['accountlanguageID']
				)
			);
			$response = fw_api_call($params, false);
			if($response['status'] == 1) {
				$s_email_from = $response['email_from'];
				$s_email_body = $response['email_body'];
				$s_email_subject = $response['email_subject'];if($_SERVER['REMOTE_ADDR']=='95.68.108.13') {print_r($s_email_body);die('asdf');}
			} else {
				echo $formText_ErrorConnectingToCrmAccount_output;
			}
		}
		?>

		<?php

		$s_path = $variables->account_root_url;

		$v_css = array(
		  '/modules/Homes/output/output.css',
		);

		foreach($v_css as $s_item)
		{
		  $l_time = filemtime(BASEPATH.$s_item);
		  ?><link rel="stylesheet" href="<?php echo $s_path.$s_item.'?v='.$l_time;?>"><?php
		}

		?>

		<!-- <link href='../modules/Homes/output/output.css' rel='stylesheet' type='text/css'> -->

		<div class="popupform">

			<div class="popupformTitle"><?php echo $formText_ApproveInvitation_Output;?></div>
			<div class="popupError"></div>

			<?php if(!$_POST['from_owncompany'] && ($v_employee_basisconfig['invitation_select_access'] || $v_employee_accountconfig['invitation_select_access'] || intval($v_employee_basisconfig['invitation_setting']) == 0)) { ?>
				<?php if(!$_POST['resend']){?>
					<div class="selectAccess">
						<label><?php echo $formText_ChooseAccessLevel_output;?>&nbsp;&nbsp;&nbsp;</label>
						<select class="accessSelection">
							<option value="" selected><?php echo $formText_NoneSelected_output;?></option>
							<?php

								// $data = json_decode(APIconnectorUser("companyaccessget", $variables->loggID, $variables->sessionID array('COMPANY_ID'=>$companyID, 'USERNAME'=>$_GET['username'], 'ACCESSID'=>$_GET['accessID'])),true);

								// $userAccess = $data['data'];

								$data = json_decode(APIconnectUser("groupcompanyaccessbycompanyidget", $variables->loggID, $variables->sessionID, array('COMPANY_ID'=>$companyID)),true);

								$groupAccess = $data['data'];

								foreach($groupAccess as $groupItem)

								{

									?><option value="3_<?php echo $groupItem['id'];?>" <?php if($userAccess['accesslevel'] == "3" && $userAccess['groupID'] == $groupItem['id']) echo 'selected';?>><?php echo $formText_accessLevelGroups_usersOutputLink." - ".$groupItem['groupname'];?></option><?php

								}

							?>

							<option value="1" <?php if($userAccess['accesslevel'] == 1) echo 'selected';?>><?php echo $formText_accessLevelAll_usersOutputLink;?></option>

							<option value="2" <?php if($userAccess['accesslevel'] == 2) echo 'selected';?>><?php echo $formText_accessLevelSpecified_usersOutputLink;?></option>
						</select>
						<?php if(!$_POST['inBatch']) { ?>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							<input type="checkbox" name="admin" class="adminCheckbox" <?php if($userAccess['admin']) echo 'checked';?> id="adminCheckbox"/> &nbsp;<label for="adminCheckbox"><?php echo $formText_Admin_output;?></label>
						<?php } ?>
						<div class="specifiedWrapper">
						</div>
					</div>
				<?php } ?>
			<?php } ?>
			<?php if(!$_POST['noinvitiation']) { ?>
				<div style="border-bottom:1px solid #e9e9e9; padding-bottom:10px; margin-bottom:20px;"><b><?php echo $formText_invitationSender_Output;?>: <?php echo htmlspecialchars($s_email_from);?></b></div>

			    <div style="border-bottom:1px solid #e9e9e9; padding-bottom:10px; margin-bottom:20px;"><b><?php echo $formText_invitationSubject_Output;?>: <?Php print $s_email_subject; ?></b></div>

				<div style="background:#fff;"><?php echo $s_email_body;?></div>
			<?php } ?>
		</div>

		<?php
			if(isset($_POST['inBatch'])) {
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

				$sql = "SELECT p.*
			             FROM contactperson p
			            WHERE (p.id is not null) AND p.content_status < 2 AND p.type = ? ORDER BY p.name ASC";
				$o_query = $o_main->db->query($sql, array($people_contactperson_type));
				$customerList = $o_query ? $o_query->result_array() : array();
				?>
				<div class="batchUserTop"><input type="checkbox" class="selectAll" id="selectAll"/> <label for="selectAll"><?php echo $formText_SelectAll_output?></label></div>
				<div class="batchUserList">
				<?php
				$showingToGiveAccess = 0;
				foreach($customerList as $v_row)
				{
					$hasAccess = false;
					$hasRegistered = false;
					foreach($v_membersystem as $member){
						if(mb_strtolower($member['username']) == mb_strtolower($v_row['email'])){
							if($member['user_id'] > 0){
								$hasRegistered = true;
							}
							$hasAccess = true;
							if($member['first_name'] != ""){
								$v_row['name']=$member['first_name'];
								$v_row['middle_name']=$member['middle_name'];
								$v_row['last_name']=$member['last_name'];
							}
						}
					}
					$toCheckAccess = false;
					if($_POST['resend']){
						$toCheckAccess = true;
					}
					if($hasAccess == $toCheckAccess && (!$_POST['resend'] || $hasRegistered == false)) {
						$showingToGiveAccess++;
						?>
						<div class="peopleRow">
							<input type="checkbox" class="peopleItemCheckbox" id="peopleItem<?php echo $v_row['id']?>" value="<?php echo $v_row['id']?>" name="peopleIds[]"/><label for="peopleItem<?php echo $v_row['id']?>"><?php echo $v_row['name']." ".$v_row['middle_name']." ".$v_row['last_name'];?></label>
						</div>
						<?php
					}
				}
				?>
				</div>
				<div class="batchUserBottom"><?php echo $formText_Selected_output?> <span class="selected">0</span> <?php echo $formText_Of_output?> <span class="total"><?php echo $showingToGiveAccess;?></span></div>
				<?php
			}
		?>
		<div class="popupformbtn">

			<button type="button" class="output-btn b-large b-close fw_button_not_filled_color"><?php echo $formText_Cancel_Output;?></button>

			<button type="button" class="submitbtn fw_button_color" onClick="<?php if($_POST['inBatch']) { ?>output_send_invitation_in_batch();<?php } else {?>output_send_invitation(<?php echo $l_contactperson_id;?>);<?php } ?>"><?php if(!$_POST['noinvitiation']) { echo (isset($_POST['resend']) ? $formText_ResendInvitation_Output : $formText_SendInvitation_Output); } else { echo $formText_Save_output;} ?></button>

		</div>
	<?php } else {
		 echo "<br/>".$formText_NoInvitationConfigFound_output;
 	} ?>

	<script type="text/javascript">
	$(".accessSelection").change( function(){
		var access = $(this).val();
		if(access == 2){
			load_access_list();
			$(".specifiedWrapper").show();
		} else {
			$(".specifiedWrapper").html("");
			$(".specifiedWrapper").hide();
		}
	})
	$(".accessSelection").change();
	function load_access_list(){
		fw_loading_start();
		var data = {};
		data.fwajax = 1;
		data.fw_nocss = 1;
		data.accessID = '<?php echo $userAccess['id']?>';

		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=getaccesslist";?>',
			data: data,
			success: function(obj){
				fw_loading_end();
				$('#popupeditboxcontent .specifiedWrapper').html('');
				$('#popupeditboxcontent .specifiedWrapper').html(obj.html);
				$(window).resize();
			}

		});
	}
	function output_send_invitation(cid)

	{

		var accesslevel = $(".popupform .selectAccess select option:selected").val();

		if(accesslevel != "") {
			fw_loading_start();
			if(cid === undefined) cid = 0;
			var admin = 0;
			if($(".popupeditbox .adminCheckbox").is(":checked")){
				admin = 1;
			}
			$(".popupError").html("");

			var formdata = $(".specifiedForm").serialize();

			var data = {};
			data.fwajax = 1;
			data.fw_nocss = 1;
			data.cid = cid;
			data.accesslevel = accesslevel;
			data.admin = admin;
			data.crm_customer_id = '<?php echo $_POST['crm_customer_id']?>';
			<?php if(!$_POST['noinvitiation']) { ?>
				data.sendInvitation = 1;
			<?php } ?>
			<?php if($_POST['from_owncompany']) { ?>
				data.from_owncompany = 1;
			<?php } ?>
			var dataString = formdata + '&' + $.param(data);


			$.ajax({

				cache: false,

				type: 'POST',

				dataType: 'json',

				url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=send_invitation";?>',

				data: dataString,

				success: function(obj){
					fw_loading_end();

					$('#popupeditboxcontent').html('');

					$('#popupeditboxcontent').html(obj.html);

					out_popup = $('#popupeditbox').addClass('close-reload').bPopup(out_popup_options);

					$("#popupeditbox:not(.opened)").remove();

					$(window).resize();


				}

			});
		} else {
			$(".popupform .popupError").html("<?php echo $formText_ChooseAccessLevel_output;?>");
		}
	}
	function output_send_invitation_in_batch()
	{
		var accesslevel = $(".popupform .selectAccess select option:selected").val();
		if(accesslevel != "") {
			var peopleIds = $(".peopleItemCheckbox").serialize();
			if(peopleIds != ""){
				$(".popupError").html("");
				fw_loading_start();
				var sendInvitation = 0;
				var from_owncompany = 0;
				<?php if(!$_POST['noinvitiation']) { ?>
					sendInvitation = 1;
				<?php } ?>
				<?php if($_POST['from_owncompany']) { ?>
					from_owncompany = 1;
				<?php } ?>
				$.ajax({

					cache: false,

					type: 'POST',

					dataType: 'json',

					url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=send_invitation";?>',

					data: { fwajax: 1, fw_nocss: 1, peopleIds: peopleIds, inbatch: 1, resend: '<?php echo $_POST['resend']?>', accesslevel: accesslevel, sendInvitation: sendInvitation, from_owncompany: from_owncompany },

					success: function(obj){

						fw_loading_end();
						$('#popupeditboxcontent').html('');

						$('#popupeditboxcontent').html(obj.html);

						out_popup = $('#popupeditbox').addClass('close-reload').bPopup(out_popup_options);

						$("#popupeditbox:not(.opened)").remove();

						$(window).resize();
						if(typeof output_access_load == 'function')

						{

							output_access_load();

						}

					}

				});
			} else {
				$(".popupform .popupError").html("<?php echo $formText_ChoosePeopleToInvite_output;?>");
			}
		} else {
			$(".popupform .popupError").html("<?php echo $formText_ChooseAccessLevel_output;?>");
		}
	}
	$(".selectAll").click(function(){
		var checked = $(this).is(":checked");
		if(checked) {
			$(".peopleItemCheckbox").prop("checked", true);
		} else {
			$(".peopleItemCheckbox").prop("checked", false);
		}
		updateSelectedCount();
	})
	$(".peopleItemCheckbox").click(function(){

		updateSelectedCount();
	})
	function updateSelectedCount(){
		$(".batchUserBottom .selected").html($(".peopleItemCheckbox:checked").length);
	}
	</script>

	<style>
	.popupeditbox {
		width: 900px;
	}
	.popupError {
		margin-top: -10px;
		padding-bottom: 10px;
		color: red;
	}

	.popupformbtn .submitbtn {

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
	.batchUserList {
		max-height: 200px;
		overflow: auto;
	}
	.batchUserList input {
		vertical-align: middle;
		margin: 0;
		margin-right: 5px;
	}
	.batchUserList label {
		vertical-align: middle;
		margin-bottom: 0px;
	}
	.batchUserTop {
		padding: 5px 0px;
	}
	.batchUserTop input {
		vertical-align: middle;
		margin: 0;
	}
	.batchUserTop label {
		vertical-align: middle;
		margin-bottom: 0px;
	}
	.batchUserBottom {
		padding: 10px 0px;
	}

	</style>
<?php } else {
	echo $formText_YouHaveNoAccess_Output;
}?>
