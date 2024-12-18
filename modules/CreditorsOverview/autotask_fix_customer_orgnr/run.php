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

	$s_sql = "SELECT c.publicRegisterId, c.id FROM customer c WHERE IF(c.publicRegisterId = 0 AND LENGTH(c.publicRegisterId) = 1, '', c.publicRegisterId) <> '' AND IFNULL(c.updatedBy, '') <> 'fix script 2' LIMIT 10000";
	$o_query = $o_main->db->query($s_sql);
	$customers = ($o_query ? $o_query->result_array() : array());
	$updated_count = 0;
	foreach($customers as $customer){
		$regNr = preg_replace('/[^0-9]+/', '', $customer['publicRegisterId']);
		$s_sql_update = ",extra1='".$o_main->db->escape_str($customer['publicRegisterId'])."', publicRegisterId = '".$o_main->db->escape_str($regNr)."'";

		if($s_sql_update != "") {
			$s_sql = "UPDATE customer SET
			updated = now(),
			updatedBy='fix script 2'".$s_sql_update."
			WHERE id = '".$o_main->db->escape_str($customer['id'])."'";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				$updated_count++;
			}
		}
	}

	$s_sql = "SELECT c.publicRegisterId, c.id FROM customer c WHERE IF(c.publicRegisterId = 0 AND LENGTH(c.publicRegisterId) = 1, '', c.publicRegisterId) <> '' AND IFNULL(c.updatedBy, '') <> 'fix script 2' LIMIT 1";
	$o_query = $o_main->db->query($s_sql);
	$leftCreditors = $o_query ? $o_query->num_rows() : 0;
	if($leftCreditors > 0) {
		$l_next_run = strtotime($v_auto_task['next_run']) + 60;
	} else {
		$l_next_run = strtotime($v_auto_task['next_run']) + 86400;
		$l_next_run = strtotime(date("d.m.Y ".$time_for_launch, $l_next_run));
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
