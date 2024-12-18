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

	$o_query = $o_main->db->query("SELECT creditor_id, debitor_id 
	FROM collecting_company_cases 
	WHERE IFNULL(case_closed_date, '0000-00-00') = '0000-00-00'");
	$v_active_collecting_company_cases = $o_query ? $o_query->result_array() : array();
	$debitor_ids = array();
	$active_case_info = array();
	foreach($v_active_collecting_company_cases as $v_active_collecting_company_case){
		$debitor_ids[] = $v_active_collecting_company_case['debitor_id'];
		$active_case_info[$v_active_collecting_company_case['debitor_id']]++;
	}
	if(count($debitor_ids) > 0){
		// $o_query = $o_main->db->query("SELECT publicRegisterId, customer_marked_ceases_to_exist_date FROM customer WHERE IFNULL(publicRegisterId, '') <> '' AND IFNULL(updatedBy, '') <> 'cease check' GROUP BY publicRegisterId");
		// $v_customers = $o_query ? $o_query->result_array() : array();
		$o_query = $o_main->db->query("SELECT publicRegisterId, customer_marked_ceases_to_exist_date FROM customer WHERE (IFNULL(updatedBy, '') <> 'cease check' OR (updatedBy='cease check' AND updated < '".date("Y-m-d")." 20:00:00')) AND id IN (".implode(",", $debitor_ids).")");
		$v_customers = $o_query ? $o_query->result_array() : array();
		$org_nrs_primary = array();
		foreach($v_customers as $v_customer){
			if(intval(trim($v_customer['publicRegisterId'])) > 0){
				if($v_customer['customer_marked_ceases_to_exist_date'] == '0000-00-00' || $v_customer['customer_marked_ceases_to_exist_date'] == ""){
					$org_nrs_primary[] = trim($v_customer['publicRegisterId']);		
				}
			}
		}
		$org_nrs_primary = array_slice($org_nrs_primary, 0, 5000);
		// $markedAsBankrupt = 0;
		$org_nrs_chunk = array_chunk($org_nrs_primary, 500);
		// $customers_checked = 0;
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
			$org_nr_update_array = array();
			$org_nr_found_array = array();
			if(isset($v_response['status']) && $v_response['status'] == 1 && $v_response['items'])
			{
				$v_items = $v_response['items'];
				foreach($v_items as $v_item) {
					$org_nr_found_array[] = $v_item['orgnr'];
					if($v_item['konkurs'] == "J") {
						$org_nr_update_array[$v_item['orgnr']] = ",customer_marked_ceases_to_exist_date = NOW(), customer_marked_ceases_to_exist_reason='Konkurs'";
						$markedAsBankrupt++;
					} else if($v_item['tvangsavvikling'] == "J") {
						$org_nr_update_array[$v_item['orgnr']] = ",customer_marked_ceases_to_exist_date = NOW(), customer_marked_ceases_to_exist_reason='Tvangsavvikling'";
						$markedAsBankrupt++;
					} else if($v_item['avvikling'] == "J") {
						$org_nr_update_array[$v_item['orgnr']] = ",customer_marked_ceases_to_exist_date = NOW(), customer_marked_ceases_to_exist_reason='Avvikling'";
						$markedAsBankrupt++;
					}
				}			
			}
			if(count($org_nr_found_array) > 0) {
				foreach($org_nrs as $org_nr) {
					if(intval(trim($org_nr)) > 0) {
						$o_query = $o_main->db->query("SELECT customer.id, customer.creditor_id, customer.creditor_customer_id
						FROM customer 
						WHERE publicRegisterId = '".$o_main->db->escape_like_str($org_nr)."'");
						$v_customers = $o_query ? $o_query->result_array() : array();
						foreach($v_customers as $v_customer) {
							$sql_update = $org_nr_update_array[$org_nr];	
							
							if(!in_array($org_nr, $org_nr_found_array)) {
								$sql_update .= ", customer_marked_ceases_to_exist_date = NOW(), customer_marked_ceases_to_exist_reason='Not found in brreg'";
							}
							$active_case_count = $active_case_info[$v_customer['id']];
							if($active_case_count > 0) {
								$sql_update .= ", active_company_case_count = '".$o_main->db->escape_str($active_case_count)."'";
							} else {
								$sql_update .= ", customer_ceases_to_exist_handled = 1";
							}
							$o_query = $o_main->db->query("UPDATE customer SET updated = NOW(), updatedBy='cease check'".$sql_update." WHERE id = '".$o_main->db->escape_str($v_customer['id'])."'");
							if($o_query){
								$customers_checked++;
							}
						}
					}
				}
			}
		}
		$s_sql = "SELECT publicRegisterId, customer_marked_ceases_to_exist_date FROM customer WHERE (IFNULL(updatedBy, '') <> 'cease check' OR (updatedBy='cease check' AND updated < '".date("Y-m-d")."')) AND id IN (".implode(",", $debitor_ids)." LIMIT 1";
		$o_query = $o_main->db->query($s_sql);
		$leftCreditors = $o_query ? $o_query->num_rows() : 0;
		if($leftCreditors > 0) {
			$l_next_run = strtotime($v_auto_task['next_run']) + 60;
		} else {
			$l_next_run = strtotime($v_auto_task['next_run']) + 86400;
			$l_next_run = strtotime(date("d.m.Y ".$time_for_launch, $l_next_run));
		}
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
