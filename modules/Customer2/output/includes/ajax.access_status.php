<?php

$s_sql = "select * from customer_stdmembersystem_basisconfig";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_membersystem_config = $o_query->row_array();
}

if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

$s_sql = "select * from accountinfo";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $accountinfo = $o_query->row_array();
}
$o_membersystem = json_decode(APIconnectAccount("membersystemcompanyaccessusernameget", $accountinfo['accountname'], $accountinfo['password'], array("COMPANY_ID"=>$companyID, "USER"=>$_POST["email"], "MEMBERSYSTEMID"=>$_POST['membersystem_id'], "ACCESSLEVEL"=>$v_membersystem_config['access_level'], "MODULE"=>$module)));
?><div class="output-access-changer">
	<?php
	if(is_object($o_membersystem->data))
	{
		if($o_membersystem->data->last_activity != "")
		{
			?><img src="<?php echo $extradir;?>/output/elementsOutput/access_key_green.png" /><?php
		} else {
			?><img src="<?php echo $extradir;?>/output/elementsOutput/access_key_green_grey.png" /><?php /*access_key_red*/
		}
		?><div class="output-access-dropdown"><a class="script" href="#" onClick="javascript:output_access_remove(this,'<?php echo $_POST['contactperson_id'];?>');" data-delete-msg="<?php echo $formText_RemoveAccess_Output.": ".$_POST["email"];?>?"><?php echo $formText_RemoveAccess_Output;?></a></div><?php
	} else {
		?><img src="<?php echo $extradir;?>/output/elementsOutput/access_key_grey.png" /><?php
		?><div class="output-access-dropdown"><a class="script" href="#" onClick="javascript:output_access_grant(this,'<?php echo $_POST['contactperson_id'];?>');"><?php echo $formText_GiveAccess_Output;?></a></div><?php
	}
	?>
</div>