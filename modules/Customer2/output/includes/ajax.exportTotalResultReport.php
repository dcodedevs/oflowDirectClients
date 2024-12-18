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
header("Content-Disposition: attachment; filename=\"eksport.csv\"");
header("Content-type: text/csv; charset=UTF-8");

if(!is_array($_POST['selected'])) $_POST['selected'] = explode(",", $_POST['selected']);

$s_csv_data = '';
$l_cp_max_count = 0;

if(isset($_GET['filter_date_from'])){ $filter_date_from = $_GET['filter_date_from']; } else { $filter_date_from = date("01.m.Y", strtotime("-1 month", time())); }
if(isset($_GET['filter_date_to'])){ $filter_date_to = $_GET['filter_date_to']; } else { $filter_date_to = date("t.m.Y", strtotime("-1 month", time())); }
$viewType = 0;

$s_sql = "SELECT * FROM repeatingorder_accountconfig WHERE content_status < 2";
$o_query = $o_main->db->query($s_sql);
$repeatingorder_accountconfig = ($o_query ? $o_query->row_array() : array());

if(strtotime($completedRepeatingOrderDate) >= strtotime($filter_date_from)){
	if(strtotime($completedRepeatingOrderDate) >= strtotime($filter_date_to)){
		$sub_filter_date_to = $filter_date_to;
	} else {
		$sub_filter_date_to = $completedRepeatingOrderDate;
	}
} else {
	$sub_filter_date_to = "1990-00-00";
}

$sql_where = " AND (s.id is not null OR pp.id is not null)";
if($_GET['project_type'] > 0){
	if($_GET['project_type'] == 1) {
		$sql_where = " AND s.id is not null";
	} else if($_GET['project_type'] == 2) {
		$sql_where = " AND pp.id is not null AND (p.type = 0 OR p.type is null)";
	} else if($_GET['project_type'] == 3) {
		$sql_where = " AND pp.id is not null AND p.type = 1";
	}
}
if($_GET['project_leader'] > 0) {
	$sql_where .= " AND (p.employeeId = '".$o_main->db->escape_str($_GET['project_leader'])."' OR wgl.employeeId = '".$o_main->db->escape_str($_GET['project_leader'])."')";
}

include_once("total_result_report_functions.php");
$customers_ordered = get_processed_customers($variables);

foreach($customers_ordered as $customer)
{
	$repeatingOrdersUnified = $customer['repeatingOrdersUnified'];
	$customerName = $customer['customerName'];
	if($customer['subunitName'] != ""){
		$customerName .= " ".$customer['subunitName'];
	}
	foreach($repeatingOrdersUnified as $repeatingOrder) {
		$invoicedServices = $repeatingOrder['invoicedServices'];
		$invoicedItemSales = $repeatingOrder['invoicedItemSales'];
		$salaryCost = $repeatingOrder['salaryCost'];
		$itemCost = $repeatingOrder['itemCost'];
		$resultPercent = $repeatingOrder['resultPercent'];
		$resultAmount = $repeatingOrder['resultAmount'];
		$repeatingorderType = "";
		if($repeatingOrder['repeatingOrderId'] > 0) {
			$repeatingorderType = $formText_Repeatingorder_output;
		} else if($repeatingOrder['projectPeriodId'] > 0) {
			$repeatingorderType = $formText_Project_output;
		}

		$s_line = '';
		$s_line = $customerName.";".date("d.m.Y", strtotime($repeatingOrder['completed_date'])).";".$repeatingOrder['subscriptionName'].";".$repeatingorderType.";".number_format($invoicedServices, 2, ".", "").";".number_format($invoicedItemSales, 2, ".", "").";".number_format($salaryCost, 2, ".", "").";".number_format($itemCost, 2, ".", "").";".number_format($resultAmount, 2, ".", "").";".number_format($resultPercent, 2, ".", "");

		$s_csv_data .= mb_convert_encoding($s_line."\r\n", 'UTF-8');
	}
}

$s_csv_content = $formText_CustomerName_Output.";".$formText_Date_output.";".$formText_Name_output.";".$formText_Type_output.";".$formText_Invoiced_output.";".$formText_InvoicedItemSales_output.";".$formText_SalaryCost_output.";".$formText_ItemCost_output.";".$formText_Result_output.";".$formText_Margin_output;

$s_csv_content = mb_convert_encoding($s_csv_content."\r\n", 'UTF-8');

$s_csv_content .= $s_csv_data;
echo $s_csv_content;
exit;
