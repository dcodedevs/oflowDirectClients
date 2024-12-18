<?php
if($accessElementAllow_GiveRemoveAccessPeople || isset($_POST['from_owncompany'])){
	if($moduleAccesslevel > 10)
	{
		// if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");
		if(!function_exists("APIconnectorUser")) include_once(__DIR__."/../../../../fw/account_fw/includes/APIconnector.php");
		$v_accountinfo_sql = $o_main->db->query("select * from accountinfo");
		if($v_accountinfo_sql && $v_accountinfo_sql->num_rows() > 0) $v_accountinfo = $v_accountinfo_sql->row();

		$v_membersystem_config_sql = $o_main->db->query("select * from people_stdmembersystem_basisconfig");
		if($v_membersystem_config_sql && $v_membersystem_config_sql->num_rows() > 0) $v_membersystem_config = $v_membersystem_config_sql->row();

		$s_sql = "select * from contactperson where id = ?";
		$o_result = $o_main->db->query($s_sql, array($_POST['cid']));
		if($o_result && $o_result->num_rows() > 0) $v_row = $o_result->row();
		$l_homes_id = $v_row->id;
		$s_receiver_name = $v_row->name;
		$s_receiver_email = $v_row->email;
		$data = json_decode(APIconnectorUser("companyaccessget",$variables->loggID, $variables->sessionID, array('COMPANY_ID'=>$_GET['companyID'], 'USERNAME'=>$s_receiver_email, 'ACCESSID'=>$_GET['accessID'])),true);
		$userAccess = $data['data'];
		$resultcompanyaccess = json_decode(APIconnectorUser("companyaccessdelete", $variables->loggID, $variables->sessionID, array('COMPANY_ID'=>$_GET['companyID'], 'USER_ID'=>$userAccess['id'])),true);
		if(!array_key_exists('error',$resultcompanyaccess))
		{
			$data = json_decode(APIconnectorUser("departmentaccessdelete", $variables->loggID, $variables->sessionID, array('COMPANYACCESS_ID'=>$userAccess['id'],'COMPANYDEPARTMENT_ID'=>'')),true);
		}
		
		// if($_POST['return_data'] == 1)
		// {
		// 	$fw_return_data['result'] = (!array_key_exists('error',$resultcompanyaccess) ? 1 : 0);
		// 	return;
		// }
	}
	if(!isset($_POST['hide_output'])){
		?>
		<div class="popupform">
			<div class="popupfromTitle"><?php echo $formText_RemoveAccess_Output;?></div>
			<div><?php
			if(!array_key_exists('error',$resultcompanyaccess))
			{
				echo $formText_AccessRemoved_Output;
			} else {
				echo $formText_ErrorOccured_Output.": ".$resultcompanyaccess['error'];
			}
			?></div>
		</div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close fw_button_not_filled_color"><?php echo $formText_Close_Output;?></button>
		</div>
		<style>
		.popupfromTitle {
			font-size: 24px;
			padding: 0px 0px 10px;
			border-bottom: 1px solid #ededed;
			color: #5d5d5d;
			margin-bottom: 15px;
		}
		.popupformbtn {
			text-align:right;
			margin-top:20px;
			position:relative;
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
		</style>
	<?php } ?>
<?php } else {
	echo $formText_YouHaveNoAccess_Output;
} ?>
