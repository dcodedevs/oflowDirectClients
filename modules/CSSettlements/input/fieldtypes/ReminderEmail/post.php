<?php
//$_POST[$fields[$nums][1].'_session'];
//$_POST[$fields[$nums][1].'_myselfname'];
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

if(is_file(__DIR__.'/../../../output_ReminderEmailFieldtype/'.trim($fields[$nums][11]).'.php'))
{
	include(__DIR__.'/../../../output_ReminderEmailFieldtype/'.trim($fields[$nums][11]).'.php');
	
	$sql = "select es.id from sys_emailsend es join sys_emailsendto est on est.emailsend_id = es.id where es.content_id = ".$o_main->db->escape($basetable->ID)." and es.content_table = ".$o_main->db->escape($basetable->name)." and es.content_module_id = ".$o_main->db->escape($moduleID)." and es.type = 1 and es.send_on IN (".$times.") and est.status > 0 LIMIT 1";
	$o_query = $o_main->db->query($sql);
	if(!$o_query || ($o_query && $o_query->num_rows()==0))
	{
		$o_main->db->query("delete est from sys_emailsendto est join sys_emailsend es on est.emailsend_id = es.id where es.content_id = ? and es.content_table = ? and es.content_module_id = ? and es.type = 1 and est.status = 0", array($basetable->ID, $basetable->name, $moduleID));
		$o_main->db->query("delete es from sys_emailsend es left outer join sys_emailsendto est on est.emailsend_id = es.id where es.content_id = ? and es.content_table = ? and es.content_module_id = ? and es.type = 1 and est.emailsend_id is null", array($basetable->ID, $basetable->name, $moduleID));
		
		if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../includes/APIconnect.php");
		foreach($_POST[$fields[$nums][1].'_time'] as $time)
		{
			$o_main->db->query("insert into sys_emailsend(id, created, createdBy, type, send_on, sender, sender_email, subscriberlist_id, unsubscriberlist_id, content_id, content_table, content_module_id, subject, text) VALUES (NULL, NOW(), ?, ?, STR_TO_DATE(?, '%d-%m-%Y %H:%i'), ?, ?, ?, ?, ?, ?, ?, ?, ?)", array($username, 1, $time, $_POST[$fields[$nums][1].'_myselfname'], $username, 0, 0, $basetable->ID, $basetable->name, $moduleID, $emailSubjectData, $emailBodyData));
			$l_emailsend_id = $o_main->db->insert_id();
			
			$o_query = $o_main->db->query("select * from sys_emailsend_userlist where session = ? and selected = 1 group by email", array($_POST[$fields[$nums][1].'_session']));
			if($o_query && $o_query->num_rows()>0)
			foreach($o_query->result_array() as $row)
			{
				$sql = "INSERT INTO sys_emailsendto (id, emailsend_id, receiver, receiver_email, `status`, status_message, perform_time, perform_count) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?)";
				$o_main->db->query($sql, array($l_emailsend_id, $row['name'], $row['email'], 0, '', '', 0));
			}
			$s_response = APIconnectAccount("cronjobtaskcreate", $accountinfo['accountname'], $accountinfo['password'], array('TYPE'=>'email', 'TIME'=>date('YmdHi',strtotime($time)), 'DATA'=>array('l_emailsend_id'=>$l_emailsend_id)));
		}
		
		$o_main->db->query("delete from sys_emailsend_userlist where session = ?", array($_POST[$fields[$nums][1].'_session']));
		$o_main->db->query("delete ur.* from sys_emailsend_userrelation ur join sys_emailsend_userfilter uf on uf.id = ur.userfilterID where uf.session = ?", array($_POST[$fields[$nums][1].'_session']));
		$o_main->db->query("delete from sys_emailsend_userfilter where session = ?", array($_POST[$fields[$nums][1].'_session']));
		$o_main->db->query("delete from sys_emailsend_userlistexpire where session = ?", array($_POST[$fields[$nums][1].'_session']));
		
		$sql = "select es.id from sys_emailsend es join sys_emailsendto est on est.emailsend_id = es.id where es.content_id = ? and es.content_table = ? and es.content_module_id = ? and es.type = 1 LIMIT 1";
		$o_query = $o_main->db->query($sql, array($basetable->ID, $basetable->name, $moduleID));
		$o_query2 = $o_main->db->query("select * from moduledata where name = 'Reminder'");
		if($o_query && $o_query->num_rows()>0 && $o_query2 && $o_query2->num_rows()>0)
		{
			$redirect_link = substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module.'&submodule='.$submodule.'&includefile=reminder_report&contentID='.$basetable->ID.'&content_table='.$basetable->name.'&content_moduleID='.$moduleID;
		}
		
	} else {
		//reminder perform in progress. do not change anything
		$error_msg[] = "Reminders sending in progress. Changes was not saved.";
	}
} else {
	$error_msg[] = "Template file - ".$fields[$nums][11]." -  not found for Reminders fieldtype. Changes was not be saved.";
}
?>