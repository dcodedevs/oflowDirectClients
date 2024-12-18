<?php
// *********************************
// *** Ali - not in use anumore!?!
// *********************************

set_time_limit(300);
ini_set('memory_limit', '256M');
// Database
define('FRAMEWORK_DEBUG', FALSE);
define('ACCOUNT_PATH', realpath(__DIR__.'/../../../../')); // this is modified to fit this files location
define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
$v_tmp = explode("/",ACCOUNT_PATH);
$accountname = array_pop($v_tmp);

// Load database
require_once __DIR__ . '/../../../../elementsGlobal/cMain.php';

// no cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// content type
header("Content-Disposition: attachment; filename=\"eksport.csv\"");
header("Content-type: text/csv; charset=ISO-8859-1");

$_GET['folder'] = "output";
require_once __DIR__ . '/readOutputLanguage.php';

$list_filter = $_POST['list_filter'];

if(strpos($list_filter, "group_by_") !== false) {
	$subscriptiontypeId = str_replace("group_by_subscriptiontype_", "", $list_filter);
	$groupBySubscription = 1;
} else {
	$subscriptiontypeId = str_replace("subscriptiontype_", "", $list_filter);
}


if($subscriptiontypeId > 0){
	$s_csv_data = '';
	$o_query = $o_main->db->query("SELECT s.*, cei.external_id AS external_id, CONCAT_WS(' ', c.name, c.middlename, c.lastname) AS customerName, st.name as subscriptionTypeName,
	sst.name as subscriptionSubTypeName, cei_other.external_id as invoiceToOtherCustomerNr,
	CONCAT_WS(' ', cp.name, cp.middlename, cp.lastname) as contactpersonName, cp.email as contactpersonEmail, cp.mobile as contactpersonMobile, c.email as customerEmail
	FROM customer c
	LEFT OUTER JOIN customer_externalsystem_id cei ON cei.customer_id = c.id
	JOIN subscriptionmulti s ON s.customerId = c.id
	JOIN subscriptiontype st ON st.id = s.subscriptiontype_id
	LEFT OUTER JOIN customer_externalsystem_id cei_other ON cei_other.customer_id = s.invoice_to_other_customer_id
	LEFT OUTER JOIN subscriptiontype_subtype sst ON sst.id = s.subscriptionsubtypeId
	LEFT OUTER JOIN contactperson_role_conn cprc ON cprc.subscriptionmulti_id = s.id AND (cprc.role = 0 OR cprc.role is null)
	LEFT OUTER JOIN contactperson cp ON cp.id = cprc.contactperson_id
	WHERE c.content_status < 2 AND s.subscriptiontype_id = '".$subscriptiontypeId."'
	AND ((s.stoppedDate >= CURDATE() OR s.stoppedDate = '0000-00-00' OR s.stoppedDate is null)
	AND (s.startDate <> '0000-00-00' AND s.startDate is not null AND s.startDate <= CURDATE()))
	AND s.content_status < 2
	GROUP BY s.id
	ORDER BY customerName");
	if($o_query && $o_query->num_rows()>0) {
		foreach($o_query->result_array() as $v_customer)
		{
			$s_line = '';
			$s_line = $v_customer['external_id'].";".$v_customer['customerName'].";".$v_customer['subscriptionTypeName'].";".$v_customer['subscriptionSubTypeName'].";".$v_customer['subscriptionName'].";".$v_customer['contactpersonName'].";".
			$v_customer['contactpersonMobile'].";".$v_customer['contactpersonEmail'].";".$v_customer['startDate'].";".$v_customer['nextRenewalDate'].";".$v_customer['invoiceToOtherCustomerNr'].";".$v_customer['customerEmail'];


			$s_csv_data .= mb_convert_encoding($s_line."\r\n", 'ISO-8859-1', 'UTF-8');
		}
	}
}

$s_csv_content = $formText_CustomerNumber_Output.";".$formText_CustomerName_Output.";".$formText_SubscriptionType_Output.";".$formText_SubscriptionSubType_Output.";".$formText_SubscriptionName_Output.";".
$formText_ContactpersonName_Output.";".$formText_ContactpersonMobile_Output.";".$formText_ContactpersonEmail_Output.";".$formText_StartDate_Output.";".$formText_NextRenewalDate_Output.";".$formText_InvoiceToOtherCustomer_Output.";".$formText_CompanyEmail_output;

$s_csv_content = mb_convert_encoding($s_csv_content."\r\n", 'ISO-8859-1', 'UTF-8');

$s_csv_content .= $s_csv_data;
echo $s_csv_content;
exit;
