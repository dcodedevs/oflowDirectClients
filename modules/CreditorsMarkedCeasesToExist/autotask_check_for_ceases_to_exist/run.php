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


	$o_query = $o_main->db->query("SELECT * FROM creditor WHERE companyorgnr <> ''");
	if($o_query && $o_query->num_rows()>0)
	{
		$v_creditors = $o_query->result_array();
		$org_nrs = array();
		$markedAsBankrupt = 0;
		foreach($v_creditors as $v_creditor) {	   
			if($v_creditor['companyorgnr'] != "" && $v_creditor['companyorgnr'] != 0){
				if($v_creditor['creditor_marked_ceases_to_exist_date'] == "0000-00-00" || $v_creditor['creditor_marked_ceases_to_exist_date'] == ""){
					$org_nrs[] = $v_creditor['companyorgnr'];
				}
			}
		}
		$org_nrs_chunk = array_chunk($org_nrs, 500);
		foreach($org_nrs_chunk as $org_nrs) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_VERBOSE, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
			curl_setopt($ch, CURLOPT_URL, 'https://brreg.getynet.com/brreg.php');
			$v_post = array(
				'organisation_no' => $org_nrs,
				'token' => 'RySBvCv3i9u6tP4mEd177X2gspGP6Rw0E512874043zDfUyHDsNF70gZvJ2R9s6idmGxk29amaRRR1R4Hbvqy93LJzPCz9oy',
				'password' => '_9^JAj|7_atz~-Y.BndXBguO9_jr0^z~~09m-*:4SXlj|!KZ6Xd.hnJe3WM75H9Vy=;ZIcrab-84WLKN+4Kdz~+xM5U%ePIY'
			);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($v_post));
			$s_response = curl_exec($ch);

			$v_items = array();
			$v_response = json_decode($s_response, TRUE);
			if(isset($v_response['status']) && $v_response['status'] == 1 && $v_response['items'])
			{
				$v_items = $v_response['items'];
				foreach($v_items as $v_item) {						
					$s_sql = "SELECT collecting_company_cases.id,collecting_company_cases.creditor_id
					FROM collecting_company_cases
					WHERE collecting_company_cases.companyorgnr LIKE '%".$o_main->db->escape_like_str($v_item['orgnr'])."%' AND IFNULL(case_closed_date, '0000-00-00') = '0000-00-00'";
					$o_query = $o_main->db->query($s_sql);
					$v_count_active_cases = $o_query ? $o_query->num_rows() : 0;

					if($v_item['konkurs'] == "J"){
						$o_main->db->query("UPDATE creditor SET creditor_marked_ceases_to_exist_date = NOW(), creditor_marked_ceases_to_exist_reason='Konkurs', active_company_case_count = '".$o_main->db->escape_str($v_count_active_cases)."' WHERE companyorgnr LIKE '%".$o_main->db->escape_like_str($v_item['orgnr'])."%'");
						$markedAsBankrupt++;
					} else if($v_item['tvangsavvikling'] == "J"){
						$o_main->db->query("UPDATE creditor SET creditor_marked_ceases_to_exist_date = NOW(), creditor_marked_ceases_to_exist_reason='Tvangsavvikling', active_company_case_count = '".$o_main->db->escape_str($v_count_active_cases)."' WHERE companyorgnr LIKE '%".$o_main->db->escape_like_str($v_item['orgnr'])."%'");
						$markedAsBankrupt++;
					} else if($v_item['avvikling'] == "J") {
						$o_main->db->query("UPDATE creditor SET creditor_marked_ceases_to_exist_date = NOW(), creditor_marked_ceases_to_exist_reason='Avvikling', active_company_case_count = '".$o_main->db->escape_str($v_count_active_cases)."' WHERE companyorgnr LIKE '%".$o_main->db->escape_like_str($v_item['orgnr'])."%'");
						$markedAsBankrupt++;
					}
				}
				
			}
		}
	}
	$l_next_run = strtotime($v_auto_task['next_run']) + 86400;
	$l_next_run = strtotime(date("d.m.Y ".$time_for_launch, $l_next_run));


	$o_main->db->query("UPDATE auto_task SET last_run = next_run, next_run = '".$o_main->db->escape_str(date("Y.m.d H:i:s", $l_next_run))."'".$finishedStatusSql." WHERE id = '".$o_main->db->escape_str($v_auto_task['id'])."'");
	//status (0 - Idle (only for showing in web), 1 - Queued, 2 - Running, 3 - Completed)
	$o_main->db->query("UPDATE auto_task_log SET status = 3, finished = NOW(), message = ? WHERE id = '".$o_main->db->escape_str($v_auto_task['auto_task_log_id'])."'", array(''));

	$v_return['status'] = 1;
} else {
	$v_return['messages'][] = $formText_AutoTaskCannotBeFound_Output;
}

ob_end_clean();
echo json_encode($v_return);
