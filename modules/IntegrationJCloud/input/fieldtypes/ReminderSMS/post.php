<?php
//$_POST[$fields[$nums][1].'_session'];
//$_POST[$fields[$nums][1].'_time'];
//$_POST[$fields[$nums][1].'_time_old'];

$row = array();
$o_query = $o_main->db->query('SELECT * FROM moduledata where name = ?', array($module));
if($o_query && $o_query->num_rows()>0) $row = $o_query->row_array();
if(isset($row['uniqueID'])) $moduleID = $row['uniqueID']; else $moduleID = $row['id'];

$times = "";
foreach($_POST[$fields[$nums][1].'_time_old'] as $item)
{
	if($item!="")
	{
		if($times!="") $times .= ",";
		$times .= "STR_TO_DATE(".$o_main->db->escape($item).",'%d-%m-%Y %H:%i')";
	}
}

$accountinfo = array();
$o_query = $o_main->db->query('SELECT * FROM accountinfo');
if($o_query && $o_query->num_rows()>0) $accountinfo = $o_query->row_array();

if($fields[$nums][11] == '') $fields[$nums][11] = 'standard';
$templateTable = $basetable->name;
$templateID = $basetable->ID;
$templateLanguageID = $s_default_output_language;

if(is_file(__DIR__.'/../../../output_ReminderSMSFieldtype/'.trim($fields[$nums][11]).'.php'))
{
	include(__DIR__.'/../../../output_ReminderSMSFieldtype/'.trim($fields[$nums][11]).'.php');
	//truncate message
	$smsMessage = substr($smsMessage, 0, 640);
	
	$sql = "select ss.id from sys_smssend ss join sys_smssendto sst on sst.smssend_id = ss.id where ss.content_id = ".$o_main->db->escape($basetable->ID)." and ss.content_table = ".$o_main->db->escape($basetable->name)." and ss.content_module_id = ".$o_main->db->escape($moduleID)." and ss.send_on IN (".$times.") and ss.type = 1 and sst.status > 0 LIMIT 1";
	$o_query = $o_main->db->query($sql);
	if(!$o_query || ($o_query && $o_query->num_rows()==0))
	{
		if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../includes/APIconnect.php");
		$o_main->db->query("delete sst from sys_smssendto sst join sys_smssend ss on sst.smssend_id = ss.id where ss.content_id = ? and ss.content_table = ? and ss.content_module_id = ? and ss.type = 1 and sst.status = 0", array($basetable->ID, $basetable->name, $moduleID));
		$o_main->db->query("delete ss from sys_smssend ss left outer join sys_smssendto sst on sst.smssend_id = ss.id where ss.content_id = ? and ss.content_table = ? and ss.content_module_id = ? and ss.type = 1 and sst.smssend_id is null", array($basetable->ID, $basetable->name, $moduleID));
		
		foreach($_POST[$fields[$nums][1].'_time'] as $time)
		{
			$o_main->db->query("insert into sys_smssend(id, created, createdBy, type, send_on, sender, sender_email, content_id, content_table, content_module_id, message) VALUES (NULL, NOW(), ?, ?, STR_TO_DATE(?, '%d-%m-%Y %H:%i'), ?, ?, ?, ?, ?, ?)", array($username, 1, $time, $_POST[$fields[$nums][1].'_myselfname'], $username, $basetable->ID, $basetable->name, $moduleID, $smsMessage));
			$l_smssend_id = $o_main->db->insert_id();
			
			$o_query = $o_main->db->query("select * from sys_smssend_userlist where session = ? and selected = 1 group by mobile", array($_POST[$fields[$nums][1].'_session']));
			if($o_query && $o_query->num_rows()>0)
			foreach($o_query->result_array() as $row)
			{
				$sql = "INSERT INTO sys_smssendto (id, smssend_id, receiver, receiver_mobile, `status`, status_message, perform_time, perform_count) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?)";
				$o_main->db->query($sql, array($l_smssend_id, $row['name'], $row['mobile'], 0, '', '', 0));
				
				$s_response = APIconnectAccount("cronjobtaskcreate", $accountinfo['accountname'], $accountinfo['password'], array('TYPE'=>'sms', 'TIME'=>date('YmdHi',strtotime($time)), 'DATA'=>array('l_smssend_id'=>$l_smssend_id, 's_sms_ack_url'=>$s_account_url."elementsGlobal/smsack.php")));
			}
		}
		
		$o_main->db->query("delete from sys_smssend_userlist where session = ?" , array($_POST[$fields[$nums][1].'_session']));
		$o_main->db->query("delete ur.* from sys_smssend_userrelation ur join sys_smssend_userfilter uf on uf.id = ur.userfilterID where uf.session = ?" , array($_POST[$fields[$nums][1].'_session']));
		$o_main->db->query("delete from sys_smssend_userfilter where session = ?" , array($_POST[$fields[$nums][1].'_session']));
		$o_main->db->query("delete from sys_smssend_userlistexpire where session = ?" , array($_POST[$fields[$nums][1].'_session']));
		
		$sql = "select ss.id from sys_smssend ss join sys_smssendto sst on sst.smssend_id = ss.id where ss.content_id = ? and ss.content_table = ? and ss.content_module_id = ? and ss.type = 1 LIMIT 1";
		$o_query = $o_main->db->query($sql, array($basetable->ID, $basetable->name, $moduleID));
		if($o_query && $o_query->num_rows()>0 && is_file(__DIR__.'/../../../addOn_include/reminder_report.php'))
		{
			$redirect_link = substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module.'&submodule='.$submodule.'&includefile=reminder_report&contentID='.$basetable->ID.'&content_table='.$basetable->name.'&content_moduleID='.$moduleID.'&smstype=1';
		}
		
	} else {
		//reminder perform in progress. do not change anything
		$error_msg[] = "Reminders sending in progress. Changes was not saved.";
	}
} else {
	$error_msg[] = "Template file - ".$fields[$nums][11]." -  not found for RemindersSMS fieldtype. Changes was not be saved.";
}
?>