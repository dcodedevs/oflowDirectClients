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

require_once(__DIR__."/../../CollectingCasePdfHandling/output/includes/tcpdf/config/lang/eng.php");
require_once(__DIR__."/../../CollectingCasePdfHandling/output/includes/tcpdf/tcpdf.php");
require_once(__DIR__."/../../CollectingCasePdfHandling/output/includes/fpdi/fpdi.php");

class concat_pdf extends FPDI
{
	var $files = array();
	function setFiles($files) {
		$this->files = $files;
	}
	function concat() {
		$this->setPrintHeader(false);
		$this->setPrintFooter(false);
		foreach($this->files AS $file) {
			$pagecount = $this->setSourceFile($file);
			for ($i = 1; $i <= $pagecount; $i++) {
				$tplidx = $this->ImportPage($i);
				$s = $this->getTemplatesize($tplidx);
				$this->AddPage($s['w'] > $s['h'] ? 'L' : 'P', array($s['w'], $s['h']));
				$this->useTemplate($tplidx);
			}
		}
	}
}
$v_input = $_SERVER['argv'];
list($s_script_path, $l_auto_task_id) = $v_input;
$s_sql = "SELECT at.*, atl.id AS auto_task_log_id FROM auto_task at JOIN auto_task_log atl ON atl.auto_task_id = at.id WHERE at.id = '".$o_main->db->escape_str($l_auto_task_id)."' AND atl.status = 1";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0)
{
	$v_auto_task = $o_query->row_array();
	$o_main->db->query("UPDATE auto_task_log SET status = 2, started = NOW() WHERE id = '".$o_main->db->escape_str($v_auto_task['auto_task_log_id'])."'");
	$v_auto_task_config = json_decode($v_auto_task['config'], TRUE);

	$s_sql = "SELECT collecting_system_settings.* FROM collecting_system_settings ORDER BY id";
	$o_query = $o_main->db->query($s_sql);
	$collecting_system_settings = ($o_query ? $o_query->row_array() : array());

	$emailForPrinting = $collecting_system_settings['email_for_printing'];
	$automatic_reminder_sending_time = $collecting_system_settings['automatic_reminder_sending_time'];

	$time_for_launch = "H:i";
	if($v_auto_task_config['runtime_h'] != null){
		$time_for_launch = $v_auto_task_config['runtime_h'];

		if($v_auto_task_config['runtime_i'] != null){
			$time_for_launch .= ":".$v_auto_task_config['runtime_i'];
		} else {
			$time_for_launch .= ":i";
		}
	}
	$time_for_launch = $automatic_reminder_sending_time;

	$s_sql = "SELECT creditor.* FROM creditor WHERE choose_progress_of_reminderprocess = 1 ORDER BY id";
	$o_query = $o_main->db->query($s_sql);
	$creditors = ($o_query ? $o_query->result_array() : array());

	require_once __DIR__ . '/../output/includes/creditor_functions.php';
	$filesAttached = array();
	foreach($creditors as $creditor) {
		$mainlist_filter = "reminderLevel";
		$filters['list_filter'] = "canSendReminderNow";
		$page = -1;
		$perPage = 0;
        $cases = get_transaction_list($o_main, $creditor['id'], $mainlist_filter, $filters, $page, $perPage);

	    $casesToGenerate = array();
		foreach($cases as $case){
			$reminderLevelOnly = 1;
			$manualProcessing = 1;
			$creditorId = $creditor['id'];
			$collecting_case_id = $case['id'];

			include(__DIR__."/../output/includes/process_scripts/handle_cases.php");
		}
		if(count($casesToGenerate) > 0) {
	        $_POST['casesToGenerate'] = $casesToGenerate;
	        include(__DIR__."/../output/includes/process_scripts/handle_actions.php");
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
