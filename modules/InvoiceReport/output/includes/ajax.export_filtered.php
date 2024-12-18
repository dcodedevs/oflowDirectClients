<?php
set_time_limit(300);
ini_set('memory_limit', '256M');

// no cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// content type
header("Content-Disposition: attachment; filename=\"eksport.csv\"");
header("Content-type: text/csv; charset=ISO-8859-1");

$s_sql = "SELECT * FROM customer_listtabs_basisconfig ORDER BY sortnr";
$o_query = $o_main->db->query($s_sql);
$customer_listtabs_basisconfig = ($o_query ? $o_query->result_array() : array());
$default_list = "all";

$ownercompany_id = (isset($_POST['ownercompany_id'])?$_POST['ownercompany_id']:'0');
$project_id = (isset($_POST['project_id'])?$_POST['project_id']:'0');
$department_id = (isset($_POST['department_id'])?$_POST['department_id']:'0');

if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

$projectSql = "";
$departmentSql = "";
if($_POST['show_type'] == 1){
	if($project_id > 0){
		$o_query = $o_main->db->query("SELECT * FROM projectforaccounting WHERE projectnumber = ?", array($project_id));
		$projectData = $o_query ? $o_query->row_array() : array();
		$projectSql = " AND (customer_collectingorder.accountingProjectCode = ".$o_main->db->escape($projectData['projectnumber']).")";
	}
	if($department_id > 0){
		$o_query = $o_main->db->query("SELECT * FROM departmentforaccounting WHERE departmentnumber = ?", array($department_id));
		$departmentData = $o_query ? $o_query->row_array() : array();
		$departmentSql = " AND (customer_collectingorder.department_for_accounting_code = ".$o_main->db->escape($departmentData['departmentnumber']).")";
	}
}
if(0 < $_POST['customer_id'])
{
	$projectSql .= " AND customer_collectingorder.customerId = '".$o_main->db->escape_str($_POST['customer_id'])."'";
}
$datefrom = (isset($_POST['datefrom'])?$_POST['datefrom']:'0000');
$dateto = (isset($_POST['dateto'])?date('Y-m-d',strtotime($_POST['dateto']. ' + 0 days')):'9999');

if($_POST['show_type'] == 0){

	//echo $LISTSQL.'<br>';
	$LISTSQL = "SELECT invoice.id, articleNumber, article.name, orders.priceTotal, orders.gross, sum(abs(amount)) as total_amount, sum(priceTotal ) as total,  count( articleNumber ) as c
	FROM invoice
	LEFT JOIN customer_collectingorder ON  customer_collectingorder.invoiceNumber = invoice.id
	LEFT JOIN orders ON customer_collectingorder.id = orders.collectingorderId
	LEFT JOIN article ON orders.articleNumber = article.id
	WHERE invoice.invoiceDate >= ? AND invoice.invoiceDate <= ? AND customer_collectingorder.ownercompanyId = ?
		".$projectSql.$departmentSql."
	GROUP BY orders.id
	ORDER BY c DESC, total DESC;";
	//echo $LISTSQL.'<br>';
	$findInvoices = $o_main->db->query($LISTSQL, array($datefrom, $dateto, $ownercompany_id));
	$invoices = array();
	// var_dump($LISTSQL, $findInvoices->num_rows());
	if($findInvoices && $findInvoices->num_rows() > 0) {
		foreach ($findInvoices->result() as $invoice) {
			$invoices[$invoice->articleNumber]['name'] = $invoice->name;
			$invoices[$invoice->articleNumber]['total'] += $invoice->priceTotal;
			$invoices[$invoice->articleNumber]['total_amount'] += $invoice->total_amount;
			$invoices[$invoice->articleNumber]['c'] += $invoice->c;
			$invoices[$invoice->articleNumber]['totalExTax'] += $invoice->priceTotal;
			$invoices[$invoice->articleNumber]['totalInclTax'] += $invoice->gross;
		}
	}
	$s_csv_data = '';
	$total = 0;
	$total_amount = 0;
	$totalInclTaxNumber = 0;

	foreach ($invoices as $articleNumber=>$invoice) {
		$total += $invoice['total'];
		$totalInclTaxNumber+= $invoice['totalInclTax'];
		$total_amount+= $invoice['total_amount'];
		$totalEx = number_format($invoice['totalExTax'], 2, ",", " ");
		$totalIncl = number_format($invoice['totalInclTax'], 2, ",", " ");
		$s_line = $articleNumber.";".$invoice['name'].";".$invoice['total_amount'].";".$totalEx.";".$totalIncl;

		$s_csv_data .= mb_convert_encoding($s_line."\r\n", 'ISO-8859-1', 'UTF-8');
	}

	$s_line = ";;".$total_amount.";".$total.";".$totalInclTaxNumber;

	$s_csv_data .= mb_convert_encoding($s_line."\r\n", 'ISO-8859-1', 'UTF-8');

	$s_csv_content = $formText_ArticleNumber_Output.";".$formText_ArticleName_Output.";".$formText_Amount_Output.";".$formText_WithoutTax_Output.";".$formText_TotalPrice_Output;
	$s_csv_content = mb_convert_encoding($s_csv_content."\r\n", 'ISO-8859-1', 'UTF-8');
} else {
	$LISTSQL = "SELECT invoice.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) AS customerName 
	FROM invoice LEFT JOIN customer_collectingorder ON  customer_collectingorder.invoiceNumber = invoice.id
	LEFT JOIN customer c ON c.id = customer_collectingorder.customerId
	WHERE invoice.invoiceDate >= ? AND invoice.invoiceDate <= ? AND customer_collectingorder.ownercompanyId = ?
		".$projectSql.$departmentSql."
	GROUP BY invoice.id
	ORDER BY invoice.external_invoice_nr ASC";
	//echo $LISTSQL.'<br>';
	$findInvoices = $o_main->db->query($LISTSQL, array($datefrom, $dateto, $ownercompany_id));
	$invoicesAll = $findInvoices ? $findInvoices->result_array() : array();
	$invoice_count = $findInvoices ? $findInvoices->num_rows() : 0;
	$total = 0;
	foreach ($invoicesAll as $invoice) {
		$total += $invoice['totalExTax'];
		$totalInclTax += $invoice['totalInclTax'];
	}

	foreach ($invoicesAll as $invoice) {
		$totalEx = number_format($invoice['totalExTax'], 2, ",", " ");
		$totalIncl = number_format($invoice['totalInclTax'], 2, ",", " ");

		$s_line = $invoice['external_invoice_nr'].";".date("d.m.Y", strtotime($invoice['invoiceDate'])).";".$invoice['customerName'].";".$totalEx.";".$totalIncl;

		$s_csv_data .= mb_convert_encoding($s_line."\r\n", 'ISO-8859-1', 'UTF-8');
	}
	$s_csv_content = $formText_InvoiceNumber_Output.";".$formText_InvoiceDate_Output.";".$formText_CustomerName_Output.";".$formText_WithoutTax_Output.";".$formText_Amount_Output;
	$s_csv_content = mb_convert_encoding($s_csv_content."\r\n", 'ISO-8859-1', 'UTF-8');
}


$s_csv_content .= $s_csv_data;
echo $s_csv_content;
exit;
