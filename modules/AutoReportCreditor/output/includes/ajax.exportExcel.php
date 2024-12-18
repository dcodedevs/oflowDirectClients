<?php
$people_contactperson_type = 2;
$sql = "SELECT * FROM accountinfo_basisconfig ORDER BY id";
$o_query = $o_main->db->query($sql);
$accountinfo_basisconfig = $o_query ? $o_query->row_array() : array();
if(intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']) > 0){
	$people_contactperson_type = intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']);
}
$o_query = $o_main->db->get('accountinfo');
$accountinfo = $o_query ? $o_query->row_array() : array();
if(intval($accountinfo['contactperson_type_to_use_in_people']) > 0)
{
	$people_contactperson_type = $accountinfo['contactperson_type_to_use_in_people'];
}

set_time_limit(300);
ini_set('memory_limit', '256M');

// no cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// content type
header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=export.xls");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

require_once dirname(__FILE__) . '/../elementsOutput/PHPExcel/PHPExcel.php';

$s_sql = "SELECT * FROM autoreportcreditor_report WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($_POST['reportId']));
$report = $o_query ? $o_query->row_array() : array();

if($report['closed_report']){
	$sql = "SELECT autoreportcreditor_lines.*, autoreportcreditor_report.created as reported_to_creditor_date, CONCAT_WS(',', deb.name, deb.middlename, deb.lastname) as debitorName
	FROM autoreportcreditor_report
	LEFT OUTER JOIN autoreportcreditor_lines ON autoreportcreditor_lines.case_closed_autoreport_id = autoreportcreditor_report.id
	LEFT OUTER JOIN collecting_company_cases ccc ON ccc.id = autoreportcreditor_lines.case_id
	LEFT OUTER JOIN customer deb ON deb.id = ccc.debitor_id
	WHERE autoreportcreditor_report.id = ? ORDER BY debitorName ASC";
	$result = $o_main->db->query($sql, array($_POST['reportId']));
	$autoreportcreditor_lines = $result ? $result->result_array(): array();

} else {
	$sql = "SELECT autoreportcreditor_lines.*, autoreportcreditor_report.created as reported_to_creditor_date, CONCAT_WS(',', deb.name, deb.middlename, deb.lastname) as debitorName
	FROM autoreportcreditor_report
	LEFT OUTER JOIN autoreportcreditor_lines ON autoreportcreditor_lines.autoreportcreditor_report_id = autoreportcreditor_report.id
	LEFT OUTER JOIN collecting_company_cases ccc ON ccc.id = autoreportcreditor_lines.case_id
	LEFT OUTER JOIN customer deb ON deb.id = ccc.debitor_id
	WHERE autoreportcreditor_report.id = ? ORDER BY debitorName ASC";
	$result = $o_main->db->query($sql, array($_POST['reportId']));
	$autoreportcreditor_lines = $result ? $result->result_array(): array();
}

if(!is_array($_POST['selected'])) $_POST['selected'] = explode(",", $_POST['selected']);

$s_csv_data = '';
$l_cp_max_count = 0;

define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
$objPHPExcel->setActiveSheetIndex(0);

$fieldsArray = array(
    array("id"=>"created", "name"=>$formText_Created_output, "fieldsize" => 100, "type"=>"date"),
    array("id"=>"case_id", "name"=>$formText_CaseId_output, "fieldsize" => 100),
    array("id"=>"bankaccount", "name"=>$formText_Bankaccount_output, "fieldsize" => 100),
    array("id"=>"kidnumber", "name"=>$formText_Kidnumber_output, "fieldsize" => 100),
    array("id"=>"debitor_customer_nr", "name"=>$formText_DebitorCustomerNr_output, "fieldsize" => 100),
    array("id"=>"debitorName", "name"=>$formText_DebitorCustomerName_output, "fieldsize" => 100),
    array("id"=>"invoice_numbers", "name"=>$formText_InvoiceNumbers_output, "fieldsize" => 100),
    array("id"=>"total_outstanding_oflow", "name"=>$formText_TotalOutstandingOflow_output, "fieldsize" => 100, "type" => "number"),
    array("id"=>"reported_to_creditor_date", "name"=>$formText_ReportedToCreditorDate_output, "fieldsize" => 100, "type" => "date")
);
$row = 1; // 1-based index
$col = 0;
foreach($fieldsArray as $key=>$fieldItem) {
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $fieldItem['name']);
	$col++;
}
$row++;
foreach($autoreportcreditor_lines as $autoreportcreditor_line)
{
	$col = 0;
	foreach($fieldsArray as $fieldItem) {
		switch($fieldItem['id']) {
			case 'customerName':
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, trim($customerName));
				$objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col).($row))->getAlignment()->setWrapText(true);
				$col++;
			default:
				$outputText = $autoreportcreditor_line[$fieldItem['id']];
				if($fieldItem['type'] == "date"){
					if($autoreportcreditor_line[$fieldItem['id']] != "0000-00-00" && $autoreportcreditor_line[$fieldItem['id']] != "" && $autoreportcreditor_line[$fieldItem['id']] != null) {
						 $outputText = date("d.m.Y", strtotime($autoreportcreditor_line[$fieldItem['id']]));
					} else {
						$outputText = "";
					}
				} else if($fieldItem['type'] == "number"){
					$number = $autoreportcreditor_line[$fieldItem['id']];
					$decimal = 2;
					if($fieldItem['roundup']){
						$number = round($number);
						$decimal = 0;
					}
					$outputText = number_format($number, $decimal, ".", "");
				}
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $outputText);
				if($fieldItem['type'] == "number"){
					if($decimal == 0){
						$objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col).($row))->getNumberFormat()->setFormatCode('# ### ### ##0');
					} else {
						$objPHPExcel->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col).($row))->getNumberFormat()->setFormatCode('# ### ### ##0.00');
					}
				}
				$col++;
			break;
		}
	}
	$row++;
}

$objPHPExcel->setActiveSheetIndex(0);
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;
