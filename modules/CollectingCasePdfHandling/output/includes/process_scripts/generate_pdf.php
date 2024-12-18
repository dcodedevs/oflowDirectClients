<?php
ob_start();
error_reporting(E_ALL | E_STRICT); ini_set("display_errors", 1);
require_once(__DIR__."/../../../CollectingCases/output/includes/tcpdf/config/lang/eng.php");
require_once(__DIR__."/../../../CollectingCases/output/includes/tcpdf/tcpdf.php");
require_once(__DIR__."/../../../CollectingCases/output/includes/fpdi/fpdi.php");

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

$s_sql = "INSERT INTO collecting_cases_batch SET id=NULL, createdBy='".$o_main->db->escape_str($variables->loggID)."', created=NOW()";
$o_query = $o_main->db->query($s_sql);
$batch_id = $o_main->db->insert_id();

$s_sql = "SELECT c.*, a.id AS action_id
FROM collecting_cases_handling_action a
JOIN collecting_cases c ON c.id = a.collecting_case_id
WHERE c.creditor_id = '".$o_main->db->escape_str($_POST['cid'])."' AND (a.performed_date IS NULL OR a.performed_date = '0000-00-00')
AND a.action_type = 1 AND a.collecting_cases_process_steps_action_id is not null
ORDER BY a.created ASC";
$o_query = $o_main->db->query($s_sql);
$cases = $o_query ? $o_query->result_array() : array();

foreach($cases as $case)
{
	$o_curl = curl_init();
	$s_url = 'https://s16.getynet.com/accounts/collectCrmDevNo/modules/CollectingCases/output/includes/generatePdf.php?caseId='.$case['id'].'&batch_id='.$batch_id.'&action_id='.$case['action_id'];
	curl_setopt($o_curl, CURLOPT_URL, $s_url);
	curl_setopt($o_curl, CURLOPT_RETURNTRANSFER, true);
	$s_response = curl_exec($o_curl);
	curl_close($o_curl);

    if($s_response !== FALSE)
	{
		$v_response = json_decode($s_response, TRUE);

		if(isset($v_response['status']) && 1 == $v_response['status'])
		{
			$s_sql = "UPDATE collecting_cases_handling_action SET performed_date = NOW() WHERE id = '".$o_main->db->escape_str($case['action_id'])."'";
    		//$o_query = $o_main->db->query($s_sql);
		}
	}
}


$v_files = array();
$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE batch_id='".$o_main->db->escape_str($batch_id)."' ORDER BY id";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0)
foreach($o_query->result_array() as $v_row)
{
	if(is_file(__DIR__."/../../../../".$v_row["pdf"]))
	{
		$v_files[] = __DIR__."/../../../../".$v_row["pdf"];
	}
}
ob_clean();
$s_file = "batch_report_".$batch_id.".pdf";
if(sizeof($v_files)==0)
{
	echo $formText_InvoicesNotFound_Output;
} else {
	$o_pdf_merge =& new concat_pdf();
	$o_pdf_merge->setFiles($v_files);
	$o_pdf_merge->concat();
	ob_end_clean();
	$o_pdf_merge->Output($s_file, "D");
}
