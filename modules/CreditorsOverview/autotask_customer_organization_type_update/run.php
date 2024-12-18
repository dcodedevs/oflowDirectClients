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
	$failedMsg = "";
	$sql = "SELECT * FROM customer WHERE IFNULL(organization_type_checked, 0) = 0 AND IFNULL(publicRegisterId, '') <> '' LIMIT 300";
	$o_query = $o_main->db->query($sql);
	$customers = $o_query ? $o_query->result_array() : array();
	$organization_numbers = array();
	foreach($customers as $customer) {
		$organization_numbers[$customer['publicRegisterId']] = $customer['publicRegisterId'];
	}
	if(count($organization_numbers) > 0) {
		$only_organization_numbers = array();
		foreach($organization_numbers as $organization_number){
			$only_organization_numbers[] = $organization_number;
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_URL, 'http://ap_api.getynet.com/brreg.php');
		$v_post = array(
			'organisation_no' => $only_organization_numbers,
			'token' => 'RySBvCv3i9u6tP4mEd177X2gspGP6Rw0E512874043zDfUyHDsNF70gZvJ2R9s6idmGxk29amaRRR1R4Hbvqy93LJzPCz9oy',
			'password' => '_9^JAj|7_atz~-Y.BndXBguO9_jr0^z~~09m-*:4SXlj|!KZ6Xd.hnJe3WM75H9Vy=;ZIcrab-84WLKN+4Kdz~+xM5U%ePIY'
		);

		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($v_post));
		$s_response = curl_exec($ch);
		
		$v_items = array();
		$v_response = json_decode($s_response, TRUE);
		if(isset($v_response['status']) && $v_response['status'] == 1 && $v_response['items'])
		{
			foreach($v_response['items'] as $v_item) {
				$s_person_sql = "";
				if(mb_strtolower($v_item['organisasjonsform']) == mb_strtolower("ENK")){
					// $s_person_sql = ", customer_type_for_collecting_cases = 2";
				}
				$sql = "UPDATE customer SET updatedBy = 'import', updated=NOW(), organization_type = ?".$s_person_sql." WHERE publicRegisterId = ?";
				$o_query = $o_main->db->query($sql, array($v_item['organisasjonsform'], $v_item['orgnr']));					
			}
			foreach($organization_numbers as $organization_number) {
				$sql = "UPDATE customer SET updatedBy = 'import', updated=NOW(), organization_type_checked = 1 WHERE publicRegisterId = ?";
				$o_query = $o_main->db->query($sql, array($organization_number));		
			}
		}
	}

	$sql = "SELECT * FROM customer WHERE IFNULL(organization_type_checked, 0) = 0 AND IFNULL(publicRegisterId, '') <> '' LIMIT 300";
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
	$o_main->db->query("UPDATE auto_task_log SET status = 3, finished = NOW(), message = ? WHERE id = '".$o_main->db->escape_str($v_auto_task['auto_task_log_id'])."'", array($failedMsg));

	$v_return['status'] = 1;
} else {
	$v_return['messages'][] = $formText_AutoTaskCannotBeFound_Output;
}

ob_end_clean();
echo json_encode($v_return);
