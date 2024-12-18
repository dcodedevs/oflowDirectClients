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
	if(!function_exists("isBetween")){
		function isBetween($from, $till, $input) {
		    $fromTime = strtotime($from);
		    $toTime = strtotime($till);
		    $inputTime = strtotime($input);

		    return($inputTime >= $fromTime and $inputTime <= $toTime);
		}
	}

	$v_auto_task = $o_query->row_array();
	$o_main->db->query("UPDATE auto_task_log SET status = 2, started = NOW() WHERE id = '".$o_main->db->escape_str($v_auto_task['auto_task_log_id'])."'");
	$v_auto_task_config = json_decode($v_auto_task['config'], TRUE);
	if(isBetween("8:00", "23:00", date("H:i"))) {
		$s_sql = "SELECT collecting_cases_claim_letter.* FROM collecting_cases_claim_letter WHERE content_status < 2 AND (sending_status is null OR sending_status = 0)";
		$o_query = $o_main->db->query($s_sql);
		$collecting_cases_claim_letter = ($o_query ? $o_query->result_array() : array());
		if(count($collecting_cases_claim_letter) > 0){

			$o_query = $o_main->db->query("SELECT * FROM sys_emailserverconfig ORDER BY default_server DESC");
			$v_email_server_config = $o_query ? $o_query->row_array() : array();

			$s_email_body = count($collecting_cases_claim_letter).' '.$formText_ClaimlettersNotProcessed_AutoTask;

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
					if($v_email_server_config['host'] == "mail3.getynetmail.com"){
						$mail->SMTPSecure = 'ssl';
					}

				}
			} else {
				$mail->Host = "mail.dcode.no";
			}

			$s_email_subject = $formText_ClaimlettersNotProcessed_AutoTask;
			$s_sender_email = 'noreply@getynet.com';
			$s_sender_name = 'AutoTask';

			$mail->IsSMTP(true);
			$mail->From		= $s_sender_email;
			$mail->FromName	= $s_sender_name;
			$mail->Subject	= html_entity_decode($s_email_subject, ENT_QUOTES, 'UTF-8');
			$mail->Body		= $s_email_body;
			$mail->isHTML(true);
			$mail->AddAddress("david@dcode.no");
			$mail->AddAddress("byamba@dcode.no");

			$l_send_status = 2;
			if($mail->Send())
			{
				$l_send_status = 1;
			}
		}

	}
	$x = 0;
	do {
		$l_next_run = strtotime($v_auto_task['next_run']) + 3600;
		$v_auto_task['next_run'] = date("Y-m-d H:i:s", $l_next_run);
		$x++;
	} while($l_next_run<time() && $x<100);

	$o_main->db->query("UPDATE auto_task SET last_run = next_run, next_run = '".$o_main->db->escape_str(date("Y.m.d H:i:s", $l_next_run))."' WHERE id = '".$o_main->db->escape_str($v_auto_task['id'])."'");
	//status (0 - Idle (only for showing in web), 1 - Queued, 2 - Running, 3 - Completed)
	$o_main->db->query("UPDATE auto_task_log SET status = 3, finished = NOW(), message = ? WHERE id = '".$o_main->db->escape_str($v_auto_task['auto_task_log_id'])."'", array(''));

	$v_return['status'] = 1;
} else {
	$v_return['messages'][] = $formText_AutoTaskCannotBeFound_Output;
}

ob_end_clean();
echo json_encode($v_return);
