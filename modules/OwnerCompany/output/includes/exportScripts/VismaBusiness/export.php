<?php
// no cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// content type
if (!$_GET['redirectTo']) {
    header("Content-Disposition: attachment; filename=\"fakturaeksport.txt\"");
    Header('Content-type: text/plain');
}

// Database
define('FRAMEWORK_DEBUG', FALSE);
define('ACCOUNT_PATH', realpath(__DIR__.'/../../../../../../')); // this is modified to fit this files location
define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
$v_tmp = explode("/",ACCOUNT_PATH);
$accountname = array_pop($v_tmp);

// Load database
require_once __DIR__ . '/../../../../../../elementsGlobal/cMain.php';
// PHPExcel lib

// Input language
// NOTE - chooseLanguage var?
$_GET['folder'] = "input";
require_once __DIR__ . '/../../../../input/includes/readInputLanguage.php';

$_GET['folder'] = "output";
require_once __DIR__ . '/../../../../output/includes/readOutputLanguage.php';


// Rounding account
$activate_global_export = $_GET['activate_global_export'];
$ownercompany_id = $_GET['ownercompany_id'];
if ($activate_global_export) {
    $o_query = $o_main->db->get('ownercompany_accountconfig');
    $settingsData = $o_query ? $o_query->row_array() : array();
} else {
    $o_query = $o_main->db->query("SELECT * FROM ownercompany WHERE id = ?", array($ownercompany_id));
    $settingsData = $o_query ? $o_query->row_array() : array();
}

$o_query = $o_main->db->query("SELECT * FROM bookaccount WHERE id = ?", array($settingsData['accountRoundingsOnInvoice']));
$bookAccountData = ($o_query ? $o_query->row_array() : array());
$roundingAccountNr = $bookAccountData['accountNr'];

$o_query = $o_main->db->query("SELECT * FROM bookaccount WHERE id = ?", array($settingsData['accountCustomerLedger']));
$bookAccountData2 = ($o_query ? $o_query->row_array() : array());
$roundingAccountNr2 = $bookAccountData2['accountNr'];

// From & to (SNAP them)
$from = $_GET['from'];
$to = $_GET['to'];

if ($activate_global_export) {
    // if global export activated from & to should be id's
    // otherwise from and to are external_invoice_nr
    $sql = "SELECT * FROM invoice WHERE id >= ? ORDER BY id ASC LIMIT 1";
    $o_query = $o_main->db->query($sql, array($from));
    $invoice_data = $o_query ? $o_query->row_array() : array();
    $from_id = $invoice_data['id'];
    $from_number = $invoice_data['external_invoice_nr'];

    $sql = "SELECT * FROM invoice WHERE id <= ? ORDER BY id DESC LIMIT 1";
    $o_query = $o_main->db->query($sql, array($to));
    $invoice_data = $o_query ? $o_query->row_array() : array();
    $to_id = $invoice_data['id'];
    $to_number = $invoice_data['external_invoice_nr'];
} else {
    $sql = "SELECT * FROM invoice WHERE external_invoice_nr >= ? AND ownercompany_id = $ownercompany_id ORDER BY external_invoice_nr ASC LIMIT 1";
    $o_query = $o_main->db->query($sql, array($from));
    $invoice_data = $o_query ? $o_query->row_array() : array();
    $from_id = $invoice_data['id'];
    $from_number = $invoice_data['external_invoice_nr'];

    $sql = "SELECT * FROM invoice WHERE external_invoice_nr <= ? AND ownercompany_id = $ownercompany_id ORDER BY external_invoice_nr DESC LIMIT 1";
    $o_query = $o_main->db->query($sql, array($to));
    $invoice_data = $o_query ? $o_query->row_array() : array();
    $to_id = $invoice_data['id'];
    $to_number = $invoice_data['external_invoice_nr'];
}

// Query
$where = '';
$ceiWhere = '';

if (!$activate_global_export) {
    $where = "AND invoice.ownercompany_id = ".$o_main->db->escape($ownercompany_id)." ";
    $ceiWhere = " AND cei.ownercompany_id = ".$o_main->db->escape($ownercompany_id)." ";
}

if(isset($from_id) && is_numeric($from_id) && isset($to_id) && is_numeric($to_id)){
    $where .= "AND invoice.id >= ".$o_main->db->escape($from_id)." AND invoice.id <= ".$o_main->db->escape($to_id)."";
}
else if(isset($from_id) && is_numeric($from_id)){
    $where .= "AND invoice.id > ".$o_main->db->escape($from_id)."";
}

// Read data
ob_start();
$counter = 1;
if(intval($settingsData['nextInvoiceExportVoucherNumber']) > 0){
    $counter = intval($settingsData['nextInvoiceExportVoucherNumber']);
}
echo mb_convert_encoding("@IMPORT_METHOD(1)\r\n", 'ISO-8859-1', 'UTF-8');
echo mb_convert_encoding("@Actor (=CustNo, Nm, Ad1, Ad2, PNo, PArea, Phone, BsNo)\r\n", 'ISO-8859-1', 'UTF-8');
$utsql = "SELECT co.*, invoice.*, customer.*,
invoice.id AS invoiceID,
invoice.external_invoice_nr AS fakturaID,
cei.external_id AS kundeID,
oc.division,
CONCAT_WS(' ', customer.name, customer.middlename, customer.lastname) as customerName
FROM invoice
LEFT JOIN customer ON customer.id = invoice.customerId
LEFT JOIN ownercompany oc ON oc.id = invoice.ownercompany_id
LEFT JOIN customer_externalsystem_id cei ON cei.customer_id = customer.id
LEFT JOIN customer_collectingorder co ON co.invoiceNumber = invoice.id $ceiWhere
WHERE 1=1 $where GROUP BY customer.id";
$o_query = $o_main->db->query($utsql);
$skrivKunders = ($o_query ? $o_query->result_array() : array());
foreach($skrivKunders as $skrivKunder) {
    echo mb_convert_encoding('"'.$skrivKunder['kundeID'].'" "'.$skrivKunder['customerName'].'" "'.$skrivKunder['paStreet'].'" "'.$skrivKunder['paStreet2'].'" "'.$skrivKunder['paPostalNumber'].'" "'.$skrivKunder['paCity'].'" "'.$skrivKunder['phone'].'" "'.$skrivKunder['publicRegisterId'].'"'."\r\n", 'ISO-8859-1', 'UTF-8');
}
echo mb_convert_encoding("@IMPORT_METHOD(3)\r\n", 'ISO-8859-1', 'UTF-8');
echo mb_convert_encoding("@WaBnd (ValDt)\r\n", 'ISO-8859-1', 'UTF-8');
echo mb_convert_encoding('"'.date("Ymd").'"'."\r\n", 'ISO-8859-1', 'UTF-8');
echo mb_convert_encoding('@WaVo (VoNo, VoDt, ValDt, VoTp, Txt, DbAcCl, DbAcNo, DbTrnCl, DbTxCd, CrAcCl, CrAcNo, CrTrnCl, CrTxCd, Am, InvoNo, AgRef, PmtTrm, DueDt, CurAm, ExRt, R1, R2, R6, Cur, VatAm, TransSt, CID)'."\r\n", 'ISO-8859-1', 'UTF-8');

// CSV
$utsql = "SELECT co.*, invoice.*, customer.*,
invoice.id AS invoiceID,
invoice.external_invoice_nr AS fakturaID,
cei.external_id AS kundeID,
oc.division
FROM invoice
LEFT JOIN customer ON customer.id = invoice.customerId
LEFT JOIN ownercompany oc ON oc.id = invoice.ownercompany_id
LEFT JOIN customer_externalsystem_id cei ON cei.customer_id = customer.id
LEFT JOIN customer_collectingorder co ON co.invoiceNumber = invoice.id $ceiWhere
WHERE 1=1 $where GROUP BY invoice.id";
$o_query = $o_main->db->query($utsql);
$skrivKunders = ($o_query ? $o_query->result_array() : array());
foreach($skrivKunders as $skrivKunder) {
    $customerName = $skrivKunder['name'];
    if(trim($skrivKunder['middlename']) != ""){
        $customerName .= trim($skrivKunder['middlename']);
    }
    if(trim($skrivKunder['lastname']) != ""){
        $customerName .= trim($skrivKunder['lastname']);
    }
    $customerName = str_replace(",", " ", $customerName);
    $paStreet = str_replace(",", " ", $skrivKunder['paStreet']);
    $paStreet2 = str_replace(",", " ", $skrivKunder['paStreet2']);
    $paPostalNumber = str_replace(",", " ", $skrivKunder['paPostalNumber']);
    $paCity = str_replace(",", " ", $skrivKunder['paCity']);

    $skrivKunderDepartment = "0";
    $skrivKunderProject = "0";

    if($skrivKunder['department_for_accounting_code'] > 0){
        $skrivKunderDepartment = $skrivKunder['department_for_accounting_code'];
    }
    if($skrivKunder['accountingProjectCode'] > 0){
        $skrivKunderProject = $skrivKunder['accountingProjectCode'];
    }

    $debetOrCredit = '"1" "'.$skrivKunder['kundeID'].'" "0" "0" "" "" "" ""';
    $type = 11;
    $text = "Utgående faktura";

    if($skrivKunder['totalInclTax'] < 0){
        $skrivKunder['totalInclTax'] = $skrivKunder['totalInclTax'] * (-1);
        $debetOrCredit = '"" "" "" "" "1" "'.$skrivKunder['kundeID'].'" "0" "0"';
        $type = 12;
        $text = "Utgående kreditfaktura";
    }
    echo mb_convert_encoding('"'.$skrivKunder['fakturaID'].'" "'.date("Ymd", strtotime($skrivKunder['invoiceDate'])).'" "'.date("Ymd", strtotime($skrivKunder['invoiceDate'])).'" "'.$type.'" "'.$text.'" '.$debetOrCredit.' "'.$skrivKunder['totalInclTax'].'" "'.$skrivKunder['fakturaID'].'" "" "" "'.date("Ymd", strtotime($skrivKunder['dueDate'])).'" "" "" "'.$skrivKunderDepartment.'" "'.$skrivKunderProject.'" "0" "" "0.00" "1" "'.$skrivKunder['kidNumber'].'"'."\r\n", 'ISO-8859-1', 'UTF-8');

    $o_query = $o_main->db->query("SELECT contactperson.* FROM customer_collectingorder JOIN contactperson ON contactperson.id = customer_collectingorder.contactpersonId
        WHERE customer_collectingorder.invoiceNumber = ? AND contactperson.id is not null", array($skrivKunder['invoiceID']));
    $contactPersons = ($o_query ? $o_query->result_array() : array());
    $contactpersonName = "";
    foreach($contactPersons as $contactPerson){
        if($contactpersonName != '')
        $contactpersonName .= " / ";

        $contactpersonName .= $contactPerson['name']." ".$contactPerson['middlename']." ".$contactPerson['lastname'];
    }

    $roundingCorrection = 0;
    $o_query = $o_main->db->query("SELECT orders.* FROM orders JOIN customer_collectingorder ON customer_collectingorder.id = orders.collectingorderId WHERE customer_collectingorder.invoiceNumber = ?", array($skrivKunder['invoiceID']));
    $orderList = ($o_query ? $o_query->result_array() : array());
    foreach($orderList as $order){
        $articleName = str_replace(",", " ", $order['articleName']);
        $articleCode = $order['articleNumber'];

        $o_query = $o_main->db->query("SELECT article.* FROM article WHERE article.id = ?", array($articleCode));
        $article = ($o_query ? $o_query->row_array() : array());
        if($article['articleCode'] != ""){
            $articleCode = $article['articleCode'];
        }
        $o_query = $o_main->db->query("SELECT vatcode.* FROM vatcode WHERE vatcode.vatCode = ?", array($order['vatCode']));
        $vatCode = ($o_query ? $o_query->row_array() : array());

        $debetOrCredit = '"" "" "" "" "3" "'.$order['bookaccountNr'].'" "'.$vatCode['revenue_class'].'" "'.$order['vatCode'].'"';
        if($order['gross'] < 0){
            $order['gross'] = $order['gross'] * (-1);
            $order['priceTotal'] = $order['priceTotal'] * (-1);
            $debetOrCredit = '"3" "'.$order['bookaccountNr'].'" "'.$vatCode['revenue_class'].'" "'.$order['vatCode'].'" "" "" "" ""';
        }
        echo mb_convert_encoding('"'.$skrivKunder['fakturaID'].'" "'.date("Ymd", strtotime($skrivKunder['invoiceDate'])).'" "'.date("Ymd", strtotime($skrivKunder['invoiceDate'])).'" "'.$type.'" "'.$text.'" '.$debetOrCredit.' "'.$order['gross'].'" "'.$skrivKunder['fakturaID'].'" "" "" "" "" "" "'.$skrivKunderDepartment.'" "'.$skrivKunderProject.'" "0" "" "'.($order['gross']-$order['priceTotal']).'" "1" ""'."\r\n", 'ISO-8859-1', 'UTF-8');
    }
}

// $sql = "UPDATE ownercompany SET nextInvoiceExportVoucherNumber = ? WHERE id = ?";
// $o_query = $o_main->db->query($sql, array($counter, $settingsData['id']));

$csv = ob_get_clean();

if($_GET['export2']){
    // Save in export history
    if ($o_main->db->table_exists('invoice_export2_history')) {

        // Get invoice module id
        $o_query = $o_main->db->get_where('moduledata', array('name' => 'Invoice'));
        $invoice_module = $o_query ? $o_query->row_array() : array();
        $moduleID = $invoice_module['id'];

        $o_main->db->insert('invoice_export2_history', array(
            'moduleID' => $moduleID,
            'created' => date('Y-m-d H:i:s'),
            'createdBy' => $variables->loggID,
            'invoiceIdFrom' => $from_id,
            'invoiceIdTo' => $to_id,
            'invoiceNrFrom' => $from_number,
            'invoiceNrTo' => $to_number,
            'ownerCompanyId' => $ownercompany_id
        ));


        // Write file to disc
        $fileDir = __DIR__ . '/../../../../../../uploads/protected/invoice_exports/';
        if (!is_dir($fileDir)) {
            mkdir($fileDir);
        }
        $fileName = 'export2_' . $o_main->db->insert_id() . '.txt';
        $filePath = $fileDir . $fileName;

        $fp = fopen($filePath, 'w');
        fwrite($fp, $csv);
        fclose($fp);


        $file_json = array(
            0 => array(
                0 => $fileName,
                1 => array('uploads/protected/invoice_exports/'. $fileName),
                2 => array(),
                3 => '',
                4 => $o_main->db->insert_id()
            )
        );

        // Update name
        $o_main->db->where('id', $o_main->db->insert_id());
        $o_main->db->update('invoice_export2_history', array('file' => json_encode($file_json)));
    }
} else {
    // Save in export history
    if ($o_main->db->table_exists('invoice_export_history')) {

        // Get invoice module id
        $o_query = $o_main->db->get_where('moduledata', array('name' => 'Invoice'));
        $invoice_module = $o_query ? $o_query->row_array() : array();
        $moduleID = $invoice_module['id'];

        $o_main->db->insert('invoice_export_history', array(
            'moduleID' => $moduleID,
            'created' => date('Y-m-d H:i:s'),
            'createdBy' => $variables->loggID,
            'invoiceIdFrom' => $from_id,
            'invoiceIdTo' => $to_id,
            'invoiceNrFrom' => $from_number,
            'invoiceNrTo' => $to_number,
            'ownerCompanyId' => $ownercompany_id
        ));


        // Write file to disc
        $fileDir = __DIR__ . '/../../../../../../uploads/protected/invoice_exports/';
        if (!is_dir($fileDir)) {
            mkdir($fileDir);
        }
        $fileName = 'export_' . $o_main->db->insert_id() . '.txt';
        $filePath = $fileDir . $fileName;

        $fp = fopen($filePath, 'w');
        fwrite($fp, $csv);
        fclose($fp);


        $file_json = array(
            0 => array(
                0 => $fileName,
                1 => array('uploads/protected/invoice_exports/'. $fileName),
                2 => array(),
                3 => '',
                4 => $o_main->db->insert_id()
            )
        );

        // Update name
        $o_main->db->where('id', $o_main->db->insert_id());
        $o_main->db->update('invoice_export_history', array('file' => json_encode($file_json)));
    }
}


if (!$_GET['redirect_to']) {
    // Output file
    echo $csv;
}
else {
    header('Location: ' . $_GET['redirect_to']);
}

/**
 * Helper functions
 */

function sendIt($lengde, $tekst){
	$tekst = str_replace(array("\n", "\r"), " ", $tekst);

    if(strlen($tekst) <= $lengde){
        return $tekst;
    }
    else{
        return substr($tekst,0,$lengde);
    }
}

function getOrderVAT($vatcodeId) {
    global $o_main;

    $o_query = $o_main->db->query("SELECT * FROM vatcode WHERE vatCode = ?", array($vatcodeId));
    $row = ($o_query ? $o_query->row_array() : array());

    $data = array(
        'mamutCode' => $row['vatCodeMamutExport']
    );

    return $data;
}
?>
