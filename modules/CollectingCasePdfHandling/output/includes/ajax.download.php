<?php
ini_set('memory_limit','1024M');

define('BASEPATH', __DIR__."/../../../..".DIRECTORY_SEPARATOR);
require_once(__DIR__."/../../../../elementsGlobal/cMain.php");
require_once(__DIR__."/tcpdf/config/lang/eng.php");
require_once(__DIR__."/tcpdf/tcpdf.php");
require_once(__DIR__."/fpdi/fpdi.php");
include_once(__DIR__."/../output_check_access.php");
include_once(__DIR__."/../output_functions.php");

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

$_POST['folder'] = "output";
include(__DIR__."/readOutputLanguage.php");
if($l_access < 1)
{
	$callFromCustomerPortal = false;
	if($_GET['username'] != ""){
		$s_sql = "SELECT * FROM sys_api_access WHERE username = ?";
		$o_query = $o_main->db->query($s_sql, array($_GET["username"]));
		$apiaccess = $o_query ? $o_query->row_array() : array();
		if($apiaccess){
			$callFromCustomerPortal = true;
		}
	}
	if(!$callFromCustomerPortal){
		echo $formText_YouHaveNoAccess_Output;
		return;
	}
}
if(isset($_GET["code"]) && $_GET['id'] > 0)
{
	$s_sql = "SELECT * FROM collecting_cases_batch WHERE code = ? AND id = ?";
	$o_query = $o_main->db->query($s_sql, array($_GET["code"], $_GET["id"]));
	$batch = $o_query ? $o_query->row_array() : array();
	$v_files = array();
	$s_sql = "SELECT pdf FROM collecting_cases_claim_letter WHERE batch_id = ?";
	$v_rows = array();
	$o_query = $o_main->db->query($s_sql, array($batch["id"]));
	if($o_query && $o_query->num_rows()>0){
		$v_rows = $o_query->result_array();
	}
	foreach($v_rows as $v_row)
	{
		if(is_file(__DIR__."/../../../../".$v_row["pdf"]))
		{
			$v_files[] = __DIR__."/../../../../".$v_row["pdf"];
		}
	}
	$s_file = "batch_report_".$_GET["id"].".pdf";
	if(sizeof($v_files)==0)
	{
		echo $formText_InvoicesNotFound_Output;
	} else {
		$o_pdf_merge = new concat_pdf();
		$o_pdf_merge->setFiles($v_files);
		$o_pdf_merge->concat();
		ob_end_clean();
		$o_pdf_merge->Output($s_file, "D");
	}
}
