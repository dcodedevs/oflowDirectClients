<?php
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
$filter_date_from = "";
$filter_date_to = "";
if(isset($_GET['date_from'])){ $filter_date_from = $_GET['date_from']; }
if(isset($_GET['date_to'])){ $filter_date_to = $_GET['date_to']; }
if($filter_date_from != "" && $filter_date_to != "") {

	$s_sql = "SELECT ccrs.*, cred.companyname as creditorName FROM collecting_cases_report_24so ccrs
	LEFT OUTER JOIN creditor cred ON cred.id = ccrs.creditor_id
	WHERE ccrs.date >= ? AND ccrs.date <= ?
	AND IFNULL(ccrs.printed_amount_reported, '0000-00-00 00:00:00') <> '0000-00-00 00:00:00'
	AND IFNULL(ccrs.total_fees_payed_reported, '0000-00-00 00:00:00') <> '0000-00-00 00:00:00'
	AND IFNULL(ccrs.sent_without_fees_reported, '0000-00-00 00:00:00') <> '0000-00-00 00:00:00'
	AND IFNULL(ccrs.fees_forgiven_amount_reported, '0000-00-00 00:00:00') <> '0000-00-00 00:00:00'
	ORDER BY ccrs.date DESC";
	$o_query = $o_main->db->query($s_sql, array(date("Y-m-d", strtotime($filter_date_from)), date("Y-m-d", strtotime($filter_date_to))));
	$reportlines = $o_query ? $o_query->result_array() : array();

	$global_interest_payed = 0;
	$global_fee_payed = 0;
	$global_feesForgivenCount = 0;
	$global_lettersSentWithoutFeeCount = 0;
	$global_total_printed = 0;
	$exportedCount = 0;

	foreach($reportlines as $reportline) {
		$global_interest_payed+= $reportline['interest_payed_amount'];
		$global_fee_payed+= $reportline['fee_payed_amount'];
		$global_feesForgivenCount += $reportline['fees_forgiven_amount'];
		$global_lettersSentWithoutFeeCount += $reportline['sent_without_fees_amount'];
		$global_total_printed += $reportline['printed_amount'];


		$s_line = '';
		$s_line = date("d.m.Y", strtotime($reportline['date'])).";".$reportline['creditorName'].";".$reportline['sent_without_fees_amount'].";".$reportline['fees_forgiven_amount'].";".$reportline['fee_payed_amount'].";".$reportline['interest_payed_amount'].";".$reportline['printed_amount'];

		$s_csv_data .= mb_convert_encoding($s_line."\r\n", 'UTF-8');
	}


}

$s_csv_content = $formText_Date_Output.";".$formText_CreditorName_Output.";".$formText_SentWithoutFees_output.";".$formText_FeesForgiven_output.";".$formText_FeePayed_output.";".$formText_InterestPayed_output.";".$formText_TotalPrinted_output;

$s_csv_content = mb_convert_encoding($s_csv_content."\r\n", 'UTF-8');

$s_csv_content .= $s_csv_data;
echo $s_csv_content;
exit;
