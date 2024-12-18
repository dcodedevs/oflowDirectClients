<?php
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

$s_sql = "select * from accountinfo";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_accountinfo = $o_query->row_array();
}
$s_sql = "select * from customer_stdmembersystem_basisconfig";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_membersystem_config = $o_query->row_array();
}
$s_sql = "SELECT * FROM article_accountconfig";
$o_query = $o_main->db->query($s_sql);
$article_accountconfig = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM customer_basisconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $customer_basisconfig = $o_query->row_array();
}

$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_customer_accountconfig = $o_query->row_array();
}
$s_sql = "SELECT * FROM ownercompany_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $ownercompany_accountconfig = $o_query->row_array();
}

if(!function_exists("rewriteCustomerBasisconfig")) include_once("fnc_rewritebasisconfig.php");
rewriteCustomerBasisconfig();


$v_return = array();

$sql = "SELECT * FROM contactperson WHERE id IN ('".implode("','", $_POST['ids'])."') AND content_status = 0";
$o_query = $o_main->db->query($sql);
if($o_query && $o_query->num_rows()>0)
foreach($o_query->result_array() as $v_row)
{
	$v_item = array();
	unset($o_membersystem);
	if($v_row['email']!="")
	{
		$o_membersystem = json_decode(APIconnectAccount("membersystemcompanyaccessusernameget", $v_accountinfo['accountname'], $v_accountinfo['password'], array("COMPANY_ID"=>$companyID, "USER"=>$v_row["email"], "MEMBERSYSTEMID"=>$_POST['customer_id'], "ACCESSLEVEL"=>$v_membersystem_config['access_level'], "MODULE"=>$module)));
	}
	
	$l_membersystem_id = $v_row[$v_membersystem_config['content_id_field']];
	$imgToDisplay = "";
	$member = $o_membersystem->data;
	
	if($member)
	{
		$response = json_decode(APIconnectorUser("companyaccessbycompanyidget", $variables->loggID, $variables->sessionID, array('COMPANY_ID'=>$companyID)));
		$v_membersystem = array();
		foreach($response->data as $writeContent)
		{
			if($v_row['email'] == $writeContent->username){
				$info = json_decode(APIconnectorUser("userdetailsget", $variables->loggID, $variables->sessionID, array('USER_ID'=>$writeContent->registeredID)),true);

				if($info['image'] != "" && $info['image'] != null){
					$imgToDisplay = json_decode($info['image'],true);
					$tdWidth = "75px";
				}
				break;
			}
		}
	}
	ob_start();
	if($imgToDisplay != "")
	{
		?><img src="https://pics.getynet.com/profileimages/<?php echo $imgToDisplay[0]; ?>" alt="<?php echo $v_row['name']." ".$v_row['middlename']." ".$v_row['lastname']; ?>" title="<?php echo $v_row['name']." ".$v_row['middlename']." ".$v_row['lastname']; ?>"/><?php
	}
	$v_item['image'] = ob_get_clean();
	
	ob_start();
	if($v_row['email']!="")
	{
		?><div class="output-access-changer"><?php
		if(isset($o_membersystem->data))
		{
			$v_access = $o_membersystem->data;
			$s_icon = "green";
			if($v_access->is_registered == 0) $s_icon = "green_grey";
			$s_last_activity = $v_access->last_activity;
			if($s_last_activity == "") $s_last_activity = $v_access->lastlogin;
	
			if($s_last_activity != '0000-00-00 00:00:00') {
				$s_logged = 1;
			} else {
				$s_logged = 0;
			}
			?><img src="<?php echo $extradir."/output/elementsOutput/access_key_".$s_icon;?>.png" /><?php
			?><div class="output-access-dropdown">
			<a class="script" href="#" onClick="javascript:output_access_remove(this,'<?php echo $v_row['id'];?>');" data-delete-msg="<?php echo $formText_RemoveAccess_Output.": ".$v_row["email"];?>?"><?php echo $formText_RemoveAccess_Output;?></a><br/>
	
			<a class="script" href="#" onClick="javascript:output_access_grant(this,'<?php echo $v_row['id'];?>');"><?php echo $formText_ResendAccess_Output;?></a><br/>
			<a class="script" href="#" onClick="javascript:output_access_grant(this,'<?php echo $v_row['id'];?>');" data-change="1"><?php echo $formText_ChangeAccess_Output;?></a>
	
			<div><?php if($s_logged == 1 && $v_access->is_registered == 1) echo $formText_LastActivity_Output.": ".date("d.m.Y H:i", strtotime($s_last_activity)); if($v_access->is_registered == 0) echo $formText_NeverLoggedIn_Output;?></div></div><?php
		} else {
			?><img src="<?php echo $extradir;?>/output/elementsOutput/access_key_grey.png" /><?php
			?><div class="output-access-dropdown"><a class="script" href="#" onClick="javascript:output_access_grant(this,'<?php echo $v_row['id'];?>');"><?php echo $formText_GiveAccess_Output;?></a></div><?php
		}
		?>
		</div><?php
	}
	$v_item['access'] = ob_get_clean();
	
	if($v_customer_accountconfig['activate_selfdefined_company'])
	{
		$v_item['company'] = (isset($o_membersystem->data->companyname_selfdefined_name) ? $o_membersystem->data->companyname_selfdefined_name : '');
	}
	
	$v_return[$v_row['id']] = $v_item;
}

$fw_return_data = $v_return;