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

	$s_sql = "SELECT c.* FROM creditor AS cr
	JOIN customer AS c ON c.creditor_id = cr.id AND (0 < LENGTH(c.publicRegisterId) OR c.invoiceBy = 2)
	WHERE cr.activate_send_reminders_by_ehf = 1 AND c.updatedBy <> 'ehf check script'
	GROUP BY c.id LIMIT 500";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $v_customer)
	{
		$b_update = $b_found_receiver = FALSE;
		$s_customer_org_nr = preg_replace('#[^0-9]+#', '', $v_customer['publicRegisterId']);

		//$v_customer['country_code'] = 'no';
		$s_catalog_code = '0192';
		//if('se' == strtolower($v_customer['country_code'])) $s_catalog_code = '0007';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_URL, 'https://ap_api.getynet.com/find.php');
		$v_post = array(
			'organisation_no' => $s_customer_org_nr,
			'catalog_code' => $s_catalog_code,
			'token' => 'RySBvCv3i9u6tP4mEd177X2gspGP6Rw0E512874043zDfUyHDsNF70gZvJ2R9s6idmGxk29amaRRR1R4Hbvqy93LJzPCz9oy',
			'password' => '_9^JAj|7_atz~-Y.BndXBguO9_jr0^z~~09m-*:4SXlj|!KZ6Xd.hnJe3WM75H9Vy=;ZIcrab-84WLKN+4Kdz~+xM5U%ePIY'
		);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $v_post);
		$s_response = curl_exec($ch);

		$v_response = json_decode($s_response, TRUE);
		$l_status = $v_customer['invoiceBy'];
		if(isset($v_response['status']) && $v_response['status'] == 1)
		{
			if(isset($v_response['can_receive_ehf_invoice']) && 1 == $v_response['can_receive_ehf_invoice'])
			{
				$b_found_receiver = TRUE;
			}
			if($b_found_receiver)
			{
				if($v_customer['invoiceBy'] != 2)
				{
					$b_update = TRUE;
					$l_status = 2;
				}
			} else {
				if($v_customer['invoiceBy'] == 2)
				{
					$l_status = filter_var($v_customer['email'], FILTER_VALIDATE_EMAIL) ? 1 : 0;
				}
			}			
			$s_sql = "UPDATE customer SET invoiceBy = '".$o_main->db->escape_str($l_status)."', updated = NOW(), updatedBy = 'ehf check script' WHERE id = '".$o_main->db->escape_str($v_customer['id'])."'";
			$o_query = $o_main->db->query($s_sql);
		}
		
	}
	$s_sql = "SELECT c.* FROM creditor AS cr
	JOIN customer AS c ON c.creditor_id = cr.id AND (0 < LENGTH(c.publicRegisterId) OR c.invoiceBy = 2)
	WHERE cr.activate_send_reminders_by_ehf = 1 AND c.updatedBy <> 'ehf check script'
	GROUP BY c.id LIMIT 1";
	$o_query = $o_main->db->query($s_sql);
	$leftCreditors = $o_query ? $o_query->num_rows() : 0;
	if($leftCreditors > 0){
		$l_next_run = strtotime($v_auto_task['next_run']) + 60;
	} else {
		$l_next_run = strtotime($v_auto_task['next_run']) + 86400;
		$l_next_run = strtotime(date("d.m.Y 04:00", $l_next_run));
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
