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
	
	$s_sql = "SELECT ct.* FROM creditor_transactions ct 
	LEFT JOIN creditor_transactions ct2 ON ct2.link_id = ct.link_id AND ct2.collectingcase_id > 0
	WHERE ct.open = 1 AND (ct.system_type='InvoiceCustomer') AND ct2.id IS NULL 
	AND ct.link_id > 0 
	AND (ct.collectingcase_id is null OR ct.collectingcase_id = 0) AND (ct.comment LIKE '%reminderFee_%' OR ct.comment LIKE '%interest_%')
	GROUP BY ct.creditor_id";
	$o_query = $o_main->db->query($s_sql);
	$openFeesWithoutConnection = $o_query ? $o_query->result_array() : array();
	
	foreach($openFeesWithoutConnection as $openFeeWithoutConnection){
		$s_sql = "UPDATE creditor SET has_loose_transactions = 1 WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($openFeeWithoutConnection['creditor_id']));
	}

	$l_next_run = strtotime($v_auto_task['next_run']) + 86400;
	$l_next_run = strtotime(date("d.m.Y 3:00", $l_next_run));
	$o_main->db->query("UPDATE auto_task SET last_run = next_run, next_run = '".$o_main->db->escape_str(date("Y.m.d H:i:s", $l_next_run))."'".$finishedStatusSql." WHERE id = '".$o_main->db->escape_str($v_auto_task['id'])."'");
	//status (0 - Idle (only for showing in web), 1 - Queued, 2 - Running, 3 - Completed)
	$o_main->db->query("UPDATE auto_task_log SET status = 3, finished = NOW(), message = ? WHERE id = '".$o_main->db->escape_str($v_auto_task['auto_task_log_id'])."'", array(''));

	$v_return['status'] = 1;
} else {
	$v_return['messages'][] = $formText_AutoTaskCannotBeFound_Output;
}

ob_end_clean();
echo json_encode($v_return);
