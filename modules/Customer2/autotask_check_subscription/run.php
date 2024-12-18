<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
ob_start();
$v_return = array(
	'status' => 0,
	'messages' => array(),
);
require_once('Exception.php');
require_once('PHPMailer.php');
require_once('SMTP.php');

define('BASEPATH', realpath(__DIR__.'/../../../').'/');
require_once(BASEPATH.'elementsGlobal/cMain.php');
include_once(__DIR__."/includes/readOutputLanguage.php");

$v_input = $_SERVER['argv'];
list($s_script_path, $l_auto_task_id) = $v_input;
$s_sql = "SELECT at.*, atl.id AS auto_task_log_id FROM auto_task at JOIN auto_task_log atl ON atl.auto_task_id = at.id WHERE at.id = '".$o_main->db->escape_str($l_auto_task_id)."' AND atl.status = 1";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0)
{
	$v_auto_task = $o_query->row_array();
	$o_main->db->query("UPDATE auto_task_log SET status = 2, started = NOW() WHERE id = '".$o_main->db->escape_str($v_auto_task['auto_task_log_id'])."'");
	$v_auto_task_config = json_decode($v_auto_task['config'], TRUE);
	
	$l_days = intval($v_auto_task_config['parameters']['days']['value']);
	if(0 == $l_days) $l_days = 180;
	
	$b_send = FALSE;
	$s_email_body = '';
	$s_sql = "SELECT * FROM customer ORDER BY id";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $v_customer)
	{
		$s_email_content = '';
		$s_sql = "SELECT * FROM subscriptionmulti WHERE customerId = '".$o_main->db->escape_str($v_customer['id'])."' AND content_status = 0 AND stoppedDate >= CURDATE() AND stoppedDate < DATE_ADD(CURDATE(), INTERVAL ".$l_days." DAY)";
		$o_find = $o_main->db->query($s_sql);
		if($o_find && $o_find->num_rows()>0)
		foreach($o_find->result_array() as $v_subscription)
		{
			$b_send = TRUE;
			$s_email_content .= '<tr><td>'.$v_customer['name'].'</td><td>'.$v_subscription['subscriptionName'].'</td><td>'.date("d.m.Y", strtotime($v_subscription['startDate'])).'</td><td>'.date("d.m.Y", strtotime($v_subscription['stoppedDate'])).'</td></tr>';
		}
		
		if('' != $s_email_content)
		{
			$s_email_body .= $s_email_content;
		}
	}
	
	if($b_send)
	{
		$o_query = $o_main->db->query("SELECT * FROM sys_emailserverconfig ORDER BY default_server DESC");
		$v_email_server_config = $o_query ? $o_query->row_array() : array();
		
		$s_email_body = '<h3>'.$formText_FollowingSubscriptionsAreEndingSoon_AutoTask.'</h3><table width="100%" border="0"><tr><td><b>'.$formText_Customer_AutoTask.'</b></td><td><b>'.$formText_SubscriptionName_AutoTask.'</b></td><td><b>'.$formText_StartDate_AutoTask.'</b></td><td><b>'.$formText_StopDate_AutoTask.'</b></td></tr>'.$s_email_body.'</table>';
		
		$mail = new PHPMailer;
		$mail->CharSet	= 'UTF-8';
		if($v_email_server_config['host'] != "")
		{
			$mail->Host	= $v_email_server_config['host'];
			if($v_email_server_config['port'] != "") $mail->Port = $v_email_server_config['port'];
	
			if($v_email_server_config['username'] != "" and $v_email_server_config['password'] != "")
			{
				$mail->SMTPAuth	= true;
				$mail->Username	= $v_email_server_config['username'];
				$mail->Password	= $v_email_server_config['password'];
	
			}
		} else {
			$mail->Host = "mail.dcode.no";
		}
	
		$s_email_subject = $formText_FollowingSubscriptionsAreEndingSoon_AutoTask;
		$s_sender_email = 'noreply@getynet.com';
		$s_sender_name = 'AutoTask';
		$s_receiver_email = 'david@dcode.no';
		$s_receiver_name = 'David Gundersen';
	
		$mail->IsSMTP(true);
		$mail->From		= $s_sender_email;
		$mail->FromName	= $s_sender_name;
		$mail->Subject	= html_entity_decode($s_email_subject, ENT_QUOTES, 'UTF-8');
		$mail->Body		= $s_email_body;
		$mail->isHTML(true);
		$mail->AddAddress($s_receiver_email);
		
		$l_send_status = 2;
		if($mail->Send())
		{
			$l_send_status = 1;
		}
		
		$sql = "INSERT INTO sys_emailsend SET created = NOW(), createdBy = 'AutoTask', send_on = NOW(), sender = '".$o_main->db->escape_str($s_sender_name)."', sender_email = '".$o_main->db->escape_str($s_sender_email)."', subject = '".$o_main->db->escape_str($s_email_subject)."', text = '".$o_main->db->escape_str($s_email_body)."'";
		$o_insert = $o_main->db->query($sql);
		if($o_insert)
		{
			$l_email_send_id = $o_main->db->insert_id();
			
			$sql = "INSERT INTO sys_emailsendto SET emailsend_id = '".$o_main->db->escape_str($l_email_send_id)."', receiver = '".$o_main->db->escape_str($s_receiver_name)."', receiver_email = '".$o_main->db->escape_str($s_receiver_email)."', extra1 = '', extra2 = '', `status` = '".$o_main->db->escape_str($l_send_status)."', status_message = '', perform_time = NOW(), perform_count = 1";
			$o_main->db->query($sql);
		}
	}
	
	/*$v_auto_task_config['repeat_minutes'] = intval($v_auto_task_config['repeat_minutes']);
	if(0 == $v_auto_task_config['repeat_minutes']) $v_auto_task_config['repeat_minutes'] = 1;
	
	$s_format = (!empty($v_auto_task_config['runtime_y'])?$v_auto_task_config['runtime_y']:'Y').'-'.
				(!empty($v_auto_task_config['runtime_m'])?$v_auto_task_config['runtime_m']:'m').'-'.
				(!empty($v_auto_task_config['runtime_d'])?$v_auto_task_config['runtime_d']:'d').' '.
				(!empty($v_auto_task_config['runtime_h'])?$v_auto_task_config['runtime_h']:'H').':'.
				(!empty($v_auto_task_config['runtime_i'])?$v_auto_task_config['runtime_i']:'i').':00';
	$s_date = date($s_format);
	$l_runtime = strtotime($s_date);
	while($l_runtime < time())
	{
		$l_runtime = $l_runtime + ($v_auto_task_config['repeat_minutes']*60);
	}*/
	
	$l_next_run = strtotime($v_auto_task['next_run']) + 86400;
	
	$o_main->db->query("UPDATE auto_task SET last_run = next_run, next_run = '".$o_main->db->escape_str(date("Y-m-d H:i:s", $l_next_run))."' WHERE id = '".$o_main->db->escape_str($v_auto_task['id'])."'");
	//status (0 - Idle (only for showing in web), 1 - Queued, 2 - Running, 3 - Completed)
	$o_main->db->query("UPDATE auto_task_log SET status = 3, finished = NOW(), message = '".$o_main->db->escape_str(json_encode(var_export($o_main->db, TRUE)))."' WHERE id = '".$o_main->db->escape_str($v_auto_task['auto_task_log_id'])."'");
	$v_return['status'] = 1;
} else {
	$v_return['messages'][] = $formText_AutoTaskCannotBeFound_Output;
}

ob_end_clean();
echo json_encode($v_return);