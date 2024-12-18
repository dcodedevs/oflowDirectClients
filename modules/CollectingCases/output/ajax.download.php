<?php
// error_reporting(E_ALL);
// ini_set("display_errors", 1);
define('BASEPATH', __DIR__."/../../..".DIRECTORY_SEPARATOR);
require_once(__DIR__."/../../../elementsGlobal/cMain.php");
require_once(__DIR__."/includes/tcpdf/config/lang/eng.php");
require_once(__DIR__."/includes/tcpdf/tcpdf.php");
require_once(__DIR__."/includes/fpdi/fpdi.php");

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
include(__DIR__."/../output/includes/readOutputLanguage.php");
/*if($l_access < 1)
{
	echo $formText_YouHaveNoAccess_Output;
	return;
}*/

if(isset($_GET["ID"]) && $_GET["ID"] > 0)
{
	$v_files = array();
	$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE batch_id='".$o_main->db->escape_str($_GET["ID"])."' ORDER BY id";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $v_row)
	{
		if(is_file(__DIR__."/../../../".$v_row["pdf"]))
		{
			$v_files[] = __DIR__."/../../../".$v_row["pdf"];
		}
	}
	$s_file = "batch_report_".$_GET["ID"].".pdf";
	if(sizeof($v_files)==0)
	{
		echo $formText_CasesNotFound_Output;
	} else {
		$o_pdf_merge =new concat_pdf();
		$o_pdf_merge->setFiles($v_files);
		$o_pdf_merge->concat();
		ob_end_clean();
		$o_pdf_merge->Output($s_file, "D");
	}
}
