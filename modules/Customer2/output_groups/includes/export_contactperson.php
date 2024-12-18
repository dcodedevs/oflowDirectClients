<?php
// error_reporting(E_ALL);
// ini_set("display_errors", 1);
session_start();
// Constants (taken from fw/index.php)
define('FRAMEWORK_DEBUG', FALSE);
define('ACCOUNT_PATH', realpath(__DIR__.'/../../../../')); // this is modified to fit this files location
define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
$v_tmp = explode("/",ACCOUNT_PATH);
$accountname = array_pop($v_tmp);

// Load database
require_once __DIR__ . '/../../../../elementsGlobal/cMain.php';


require_once __DIR__ . '/functions.php';

ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 300);
include("readOutputLanguage.php");


$group_id = $_GET['group_id'];

/** Include PHPExcel */
require_once dirname(__FILE__) . '/phpExcel/PHPExcel.php';

define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);

$v_membersystem_un = array();

$o_query = $o_main->db->query("SELECT * FROM cache_userlist_membershipaccess");
$v_cache_userlist_membership = $o_query ? $o_query->result_array() : array();
foreach($v_cache_userlist_membership as $v_user_cached_info) {
	$v_membersystem_un[$v_user_cached_info['username']] = $v_user_cached_info;
	$registered_group_list[$v_user_cached_info['username']] = json_decode($v_user_cached_info['groups']);
}
$o_query = $o_main->db->query("SELECT * FROM cache_userlist_access");
$v_cache_userlist = $o_query ? $o_query->result_array() : array();
foreach($v_cache_userlist as $v_user_cached_info) {
	$v_membersystem_un[$v_user_cached_info['username']] = $v_user_cached_info;
	$registered_group_list[$v_user_cached_info['username']] = json_decode($v_user_cached_info['groups']);
}

$memberStatus = "";

$s_sql = "select * from people_accountconfig";
$o_query = $o_main->db->query($s_sql);
$v_employee_accountconfig = ($o_query ? $o_query->row_array() : array());

$sql = "SELECT * FROM people_basisconfig ORDER BY id";
$o_query = $o_main->db->query($sql);
$v_employee_basisconfig = $o_query ? $o_query->row_array() : array();

foreach($v_employee_accountconfig as $key=>$value){
	if($value > 0){
		$v_employee_basisconfig[$key] = ($value - 1);
	}
}

$sql = "SELECT p.* FROM contactperson_group p WHERE p.id = ?";
$o_query = $o_main->db->query($sql, array($group_id));
$v_row = $o_query ? $o_query->row_array(): array();

$cp_sql_where = "";
if($v_employee_basisconfig['show_only_persons_marked_to_show_in_intranet'] == 1){
	$cp_sql_where .= " AND c.show_in_intranet = 1";
}

$cp_sql_where .= " AND (p.hidden = 0 OR p.hidden is null)";

if($v_row['show_only_admins_in_group_list']){
	$cp_sql_where .= " AND p.type = 2";
}

$sql = "SELECT c.*, cei.external_id FROM contactperson_group_user p LEFT OUTER JOIN contactperson c ON c.id = p.contactperson_id
LEFT OUTER JOIN customer_externalsystem_id cei ON cei.customer_id = c.customerId
WHERE p.contactperson_group_id = ? AND (p.status = 0 OR p.status is null)
AND (c.notVisibleInMemberOverview = 0 OR c.notVisibleInMemberOverview is null)
AND c.content_status < 2 ".$cp_sql_where."
GROUP BY c.id
ORDER BY c.name ASC";
// var_dump($sql);
$o_query = $o_main->db->query($sql, array($group_id));
$contactPersons = $o_query ? $o_query->result_array(): array();

$objPHPExcel->setActiveSheetIndex(0);
$row = 1;
$objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $formText_ExternalCustomerId_text);
$objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $formText_publicRegisterId_text);
$objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $formText_exportName_text);
$objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $formText_exportPaStreet_text);
$objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $formText_exportPaPostalNumber_text);
$objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $formText_exportPaCity_text);
$objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $formText_exportPaCountry_text);
$objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $formText_exportVaStreet_text);
$objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, $formText_exportVaPostalNumber_text);
$objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, $formText_exportVaCity_text);
$objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, $formText_exportVaCountry_text);
$objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, $formText_exportPhone_text);
$objPHPExcel->getActiveSheet()->SetCellValue('M'.$row, $formText_exportMobile_text);
$objPHPExcel->getActiveSheet()->SetCellValue('N'.$row, $formText_exportFax_text);
$objPHPExcel->getActiveSheet()->SetCellValue('O'.$row, $formText_exportEmail_text);
$objPHPExcel->getActiveSheet()->SetCellValue('P'.$row, $formText_exportMembershipStatus_text);

$objPHPExcel->getActiveSheet()->SetCellValue('Q'.$row, $formText_exportContactName_text);
$objPHPExcel->getActiveSheet()->SetCellValue('R'.$row, $formText_exportContactMiddleName_text);
$objPHPExcel->getActiveSheet()->SetCellValue('S'.$row, $formText_exportContactLastName_text);
$objPHPExcel->getActiveSheet()->SetCellValue('T'.$row, $formText_exportContactTitle_text);
$objPHPExcel->getActiveSheet()->SetCellValue('U'.$row, $formText_exportContactPhone_text);
$objPHPExcel->getActiveSheet()->SetCellValue('V'.$row, $formText_exportContactMobile_text);
$objPHPExcel->getActiveSheet()->SetCellValue('W'.$row, $formText_exportContactEmail_text);
$objPHPExcel->getActiveSheet()->SetCellValue('X'.$row, $formText_exportContactWantToReceiveInfo_text);
$objPHPExcel->getActiveSheet()->SetCellValue('Y'.$row, $formText_exportContactMaincontact_text);

foreach($contactPersons as $contactPerson) {
	$sql = "SELECT * FROM customer WHERE id = ?";
	$o_query = $o_main->db->query($sql, array($contactPerson['customerId']));
	$customer = $o_query ? $o_query->row_array(): array();

	$row = $objPHPExcel->getActiveSheet()->getHighestRow()+1;
	$objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $contactPerson['external_id']);
	$objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $customer['publicRegisterId']);
	$objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $customer['name']);
	$objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $customer['paStreet']);
	$objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $customer['paPostalNumber']);
	$objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $customer['paCity']);
	$objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $customer['paCountry']);
	$objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $customer['vaStreet']);
	$objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, $customer['vaPostalNumber']);
	$objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, $customer['vaCity']);
	$objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, $customer['vaCountry']);
	$objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, $customer['phone']);
	$objPHPExcel->getActiveSheet()->SetCellValue('M'.$row, $customer['mobile']);
	$objPHPExcel->getActiveSheet()->SetCellValue('N'.$row, $customer['fax']);
	$objPHPExcel->getActiveSheet()->SetCellValue('O'.$row, $customer['email']);
	$objPHPExcel->getActiveSheet()->SetCellValue('P'.$row, $memberStatus);
	$objPHPExcel->getActiveSheet()->SetCellValue('Q'.$row, $contactPerson['name']);
	$objPHPExcel->getActiveSheet()->SetCellValue('R'.$row, $contactPerson['middlename']);
	$objPHPExcel->getActiveSheet()->SetCellValue('S'.$row, $contactPerson['lastname']);
	$objPHPExcel->getActiveSheet()->SetCellValue('T'.$row, $contactPerson['title']);
	$objPHPExcel->getActiveSheet()->SetCellValue('U'.$row, $contactPerson['directPhone']);
	$objPHPExcel->getActiveSheet()->SetCellValue('V'.$row, $contactPerson['mobile']);
	$objPHPExcel->getActiveSheet()->SetCellValue('W'.$row, $contactPerson['email']);
	$objPHPExcel->getActiveSheet()->SetCellValue('X'.$row, $contactPerson['wantToReceiveInfo']);
    $objPHPExcel->getActiveSheet()->SetCellValue('Y'.$row, $contactPerson['mainContact']);

}

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="export.xls"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

$objWriter->save('php://output');
?>
