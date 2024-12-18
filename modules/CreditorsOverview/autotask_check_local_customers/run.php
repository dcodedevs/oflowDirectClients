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

	$sql = "SELECT cr.*, cr.companyname as creditorName FROM creditor cr
	WHERE cr.integration_module <> '' AND cr.sync_from_accounting = 1 AND cr.loc_customer_difference is null 
	AND IFNULL(cr.onboarding_incomplete, 0) = 0 ORDER BY id ASC LIMIT 50";
	$o_query = $o_main->db->query($sql);
	$creditors = $o_query ? $o_query->result_array() : array();
	foreach($creditors as $creditorData){
		require_once __DIR__ . '/../../'.$creditorData['integration_module'].'/internal_api/load.php';
		if($creditorData['integration_module'] == "Integration24SevenOffice"){
			$sql = "SELECT * FROM customer WHERE creditor_id = ?";
			$o_query = $o_main->db->query($sql, array($creditorData['id']));
			$local_customer_count = $o_query ? $o_query->num_rows() : 0;
			
			$v_config = array(
				'ownercompany_id' => 1,
				'identityId' => $creditorData['entity_id'],
				'creditorId' => $creditorData['id'],
				'o_main' => $o_main
			);
			$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE username = '".$o_main->db->escape_str($username)."' AND creditor_id = '".$o_main->db->escape_str($creditorData['id'])."'";
			$o_query = $o_main->db->query($s_sql);
			if($o_query && 0 < $o_query->num_rows())
			{
				$v_int_session = $o_query->row_array();
				$v_config['session_id'] = $v_int_session['session_id'];
			}

			$api = new Integration24SevenOffice($v_config);
			$difference = 0;
			if($api->error == "") {
				$dataCustomer['changedAfter'] = date("Y-m-d", strtotime("01.01.2000"));
				$customer_updated = 0;
				$response_customer = $api->get_customer_list($dataCustomer);
				$customer_list = $response_customer['GetCompaniesResult']['Company'];

				$difference = count($customer_list) - $local_customer_count;			
			} else {
				echo $formText_ErrorConnectingToIntegration_output."<br/>";
			}
			$s_sql = "UPDATE creditor SET loc_customer_difference  = ?  WHERE id = '".$o_main->db->escape_str($creditorData['id'])."'";
			$o_query = $o_main->db->query($s_sql, array($difference));

		}
	}

	$sql = "SELECT cr.*, cr.companyname as creditorName FROM creditor cr
	WHERE cr.integration_module <> '' AND cr.sync_from_accounting = 1 AND cr.loc_customer_difference is null 
	AND IFNULL(cr.onboarding_incomplete, 0) = 0";
	$o_query = $o_main->db->query($sql);
	$leftCreditors = $o_query ? $o_query->num_rows() : 0;
	if($leftCreditors > 0){
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
