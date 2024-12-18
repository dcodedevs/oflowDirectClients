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

	$s_sql = "SELECT ccl.*, IF(ccs.id is null, cred.companyname, cred_cc.companyname) as creditorName
	FROM collecting_cases_claim_letter ccl
	LEFT OUTER JOIN collecting_cases cs ON cs.id = ccl.case_id
	LEFT OUTER JOIN collecting_company_cases ccs ON ccs.id = ccl.collecting_company_case_id
	LEFT OUTER JOIN creditor cred ON cred.id = cs.creditor_id
	LEFT OUTER JOIN creditor cred_cc ON cred_cc.id = ccs.creditor_id
	WHERE ccl.content_status < 2
	AND (ccl.sending_status is null OR ccl.sending_status = 0)
	AND ccl.created < DATE_SUB(NOW(), INTERVAL 30 MINUTE)";
	$o_query = $o_main->db->query($s_sql);
	$cases = $o_query ? $o_query->result_array() : array();

	foreach($cases as $created_letter) {
		$sending_error = "";
		if(strpos(mb_strtolower($created_letter['creditorName']), "(demo)") === false){
			if($created_letter['sending_action'] > 0) {
				if($created_letter['pdf'] != "" && file_exists(__DIR__."/../../../".$created_letter['pdf'])) {
					if($created_letter['total_amount'] > 0) {
						if($created_letter['due_date'] == "0000-00-00" || $created_letter['due_date'] == "" || $created_letter['due_date'] == "1970-01-01") {
							$sending_error = "Due date is missing";
						} else {
							$s_sql = "UPDATE collecting_cases_claim_letter SET updated = NOW(), sending_status = -2 WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
							$o_query = $o_main->db->query($s_sql);
						}
					} else {
						$sending_error = "negative amount";
					}
				} else {
					$sending_error = "missing pdf for letter";
				}
			} else {
				$sending_error = "Missing sending action";
			}
		} else {
			$sending_error = "Demo account";
		}
		if($sending_error != "") {
			$s_sql = "UPDATE collecting_cases_claim_letter SET sending_status = 2, sending_error_log = '".$o_main->db->escape_str($sending_error)."' WHERE id = '".$o_main->db->escape_str($created_letter['id'])."'";
			$o_query = $o_main->db->query($s_sql);
		}
	}

	$x = 0;
	do {
		$l_next_run = strtotime($v_auto_task['next_run']) + 60;
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
