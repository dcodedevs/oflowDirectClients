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
include_once(__DIR__."/../output/includes/readOutputLanguage.php");

$v_input = $_SERVER['argv'];
list($s_script_path, $l_auto_task_id) = $v_input;
$s_sql = "SELECT at.*, atl.id AS auto_task_log_id FROM auto_task at JOIN auto_task_log atl ON atl.auto_task_id = at.id WHERE at.id = '".$o_main->db->escape_str($l_auto_task_id)."' AND atl.status = 1";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0)
{
	$v_auto_task = $o_query->row_array();
	$o_main->db->query("UPDATE auto_task_log SET status = 2, started = NOW() WHERE id = '".$o_main->db->escape_str($v_auto_task['auto_task_log_id'])."'");
	$v_auto_task_config = json_decode($v_auto_task['config'], TRUE);

	$time_for_launch = "H:i";
	if($v_auto_task_config['runtime_h'] != null){
		$time_for_launch = $v_auto_task_config['runtime_h'];

		if($v_auto_task_config['runtime_i'] != null){
			$time_for_launch .= ":".$v_auto_task_config['runtime_i'];
		} else {
			$time_for_launch .= ":i";
		}
	}
	
	include_once(__DIR__."/../output/includes/fnc_process_open_cases_for_tabs.php");
	require(__DIR__."/../output/includes/creditor_functions_v2.php");
	require(__DIR__."/../output/includes/fnc_send_email_with_reminder_info.php");

	$sql = "SELECT cr.*, cr.companyname as creditorName FROM creditor cr
	WHERE cr.integration_module <> '' AND cr.sync_from_accounting = 1 
	AND DATE(IFNULL(cr.transaction_reorder_starttime, '0000-00-00')) < CURDATE()
	ORDER BY id ASC LIMIT 500";
	$o_query = $o_main->db->query($sql);
	$creditors = $o_query ? $o_query->result_array() : array();

	$s_sql = "SELECT * FROM collecting_system_settings";
	$o_query = $o_main->db->query($s_sql);
	$collecting_system_settings = $o_query ? $o_query->row_array() : array();
	include_once(__DIR__."/../output/languagesOutput/no.php");
	foreach($creditors as $creditor) {
		$s_sql = "UPDATE creditor_transactions SET to_be_reordered = 1 WHERE creditor_id = ? AND open = 1";
		$o_query = $o_main->db->query($s_sql, array($creditor['id']));
		$source_id = 1;
		process_open_cases_for_tabs($creditor['id'], $source_id, true);
		
        if($creditor['activate_email_with_todays_reminders']) {
			$invoiceEmails = array();
			
			$s_sql = "SELECT * FROM creditor_reminder_emails WHERE creditor_id = ? AND IFNULL(email, '') <> ''";
			$o_query = $o_main->db->query($s_sql, array($creditor['id']));
			$creditor_reminder_emails = ($o_query ? $o_query->result_array() : array());
			if(count($creditor_reminder_emails) > 0){
				foreach($creditor_reminder_emails as $creditor_reminder_email){
					$invoiceEmails[] = $creditor_reminder_email['email'];
				}
			} else {
				
			}
			if(count($invoiceEmails) > 0) {
				send_email_with_reminder_info($creditor, $invoiceEmails);
			}
		}
	}

	$sql = "SELECT cr.*, cr.companyname as creditorName FROM creditor cr
	WHERE cr.integration_module <> '' AND cr.sync_from_accounting = 1 
	AND DATE(IFNULL(cr.transaction_reorder_starttime, '0000-00-00')) < CURDATE()
	ORDER BY id ASC LIMIT 1";
	$o_query = $o_main->db->query($sql);
	$leftCreditors = $o_query ? $o_query->num_rows() : 0;
	if($leftCreditors > 0) {
		$l_next_run = strtotime($v_auto_task['next_run']) + 60;
	} else {
		$l_next_run = strtotime($v_auto_task['next_run']) + 86400;
		$l_next_run = strtotime(date("d.m.Y 5:00", $l_next_run));
	}

	$o_main->db->query("UPDATE auto_task SET last_run = next_run, next_run = '".$o_main->db->escape_str(date("Y.m.d H:i:s", $l_next_run))."'".$finishedStatusSql." WHERE id = '".$o_main->db->escape_str($v_auto_task['id'])."'");
	//status (0 - Idle (only for showing in web), 1 - Queued, 2 - Running, 3 - Completed)
	$o_main->db->query("UPDATE auto_task_log SET status = 3, finished = NOW(), message = ? WHERE id = '".$o_main->db->escape_str($v_auto_task['auto_task_log_id'])."'", array(''));

	$v_return['status'] = 1;
} else {
	$v_return['messages'][] = $formText_AutoTaskCannotBeFound_Output;
}

ob_end_clean();
echo json_encode($v_return);
