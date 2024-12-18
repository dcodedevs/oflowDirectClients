<?php
// no cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// content type
if (!$_GET['redirectTo']) {
    header("Content-Disposition: attachment; filename=\"fakturaeksport.csv\"");
    Header('Content-type: application/csv');
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
// Read data
ob_start();
$counter = 1;
if(intval($settingsData['nextInvoiceExportVoucherNumber']) > 0){
    $counter = intval($settingsData['nextInvoiceExportVoucherNumber']);
}
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

    echo mb_convert_encoding('30,'.$skrivKunder['kundeID'].','.$customerName.','.$paStreet.','.$paStreet2.','.$paPostalNumber.','
    .$paCity.',,,'.$skrivKunder['credittimeDays'].',,,,'.$skrivKunder['phone'].',,,,,,'.$skrivKunder['publicRegisterId'].',,'.$skrivKunder['paCountry'].','."\r\n", 'ISO-8859-1', 'UTF-8');

    $o_query = $o_main->db->query("SELECT contactperson.* FROM customer_collectingorder JOIN contactperson ON contactperson.id = customer_collectingorder.contactpersonId
        WHERE customer_collectingorder.invoiceNumber = ? AND contactperson.id is not null", array($skrivKunder['invoiceID']));
    $contactPersons = ($o_query ? $o_query->result_array() : array());
    $contactpersonName = "";
    foreach($contactPersons as $contactPerson){
        if($contactpersonName != '')
        $contactpersonName .= " / ";

        $contactpersonName .= $contactPerson['name']." ".$contactPerson['middlename']." ".$contactPerson['lastname'];
    }

    // Order lines
    $roundingCorrection = 0;
    $o_query = $o_main->db->query("SELECT orders.*, customer_collectingorder.department_for_accounting_code, customer_collectingorder.accountingProjectCode FROM orders JOIN customer_collectingorder ON customer_collectingorder.id = orders.collectingorderId WHERE customer_collectingorder.invoiceNumber = ?", array($skrivKunder['invoiceID']));
    $orderList = ($o_query ? $o_query->result_array() : array());
    foreach($orderList as $order){
        $departmentCode = "";
        $projectCode = "";
        if($order['department_for_accounting_code'] > 0){
            $departmentCode = $order['department_for_accounting_code'];
        }
        if($order['accountingProjectCode'] > 0){
            $projectCode = $order['accountingProjectCode'];
        }
        $articleName = str_replace(",", " ", $order['articleName']);
        echo mb_convert_encoding('60,'.$counter.','.date('dmy', strtotime($skrivKunder['invoiceDate'])).','.$order['bookaccountNr'].','.$departmentCode.','.$projectCode.','.$order['vatCode'].','.$articleName.','.$order['gross']*(-1).',,'.$skrivKunder['division'].',,,,,,,,,,'."\r\n", 'ISO-8859-1', 'UTF-8');
    }

    $skrivKunderDepartment = "";
    $skrivKunderProject = "";

    if($skrivKunder['department_for_accounting_code'] > 0){
        $skrivKunderDepartment = $skrivKunder['department_for_accounting_code'];
    }
    if($skrivKunder['accountingProjectCode'] > 0){
        $skrivKunderProject = $skrivKunder['accountingProjectCode'];
    }
    echo mb_convert_encoding('60,'.$counter.','.date('dmy', strtotime($skrivKunder['invoiceDate'])).','.$skrivKunder['kundeID'].','.$skrivKunderDepartment.','.$skrivKunderProject.',,'.$formText_InvoiceNumber_output.": ".$skrivKunder['fakturaID'].','.$skrivKunder['totalInclTax'].',,'.$skrivKunder['division'].',,,,,'.$skrivKunder['fakturaID'].','.date('dmy', strtotime($skrivKunder['dueDate'])).',,,,'."\r\n", 'ISO-8859-1', 'UTF-8');


    $name = str_replace(",", " ", $skrivKunder['name'].' '.$skrivKunder['middlename'].' '.$skrivKunder['lastname']);
    $contactpersonName = str_replace(",", " ", $contactpersonName);
    echo mb_convert_encoding('80,'.$skrivKunder['fakturaID'].','.$skrivKunder['kundeID'].',1,'.$skrivKunder['totalInclTax'].',,,'.$skrivKunder['tax'].',,,,'
    .$name.','.$paStreet.','.$paStreet2.','.$paPostalNumber.','
    .$paCity.','.$contactpersonName.',,'.date('d.m.Y', strtotime($skrivKunder['invoiceDate'])).','.date('d.m.Y', strtotime($skrivKunder['dueDate'])).',,,,,,,,,,,,'.$counter.''."\r\n", 'ISO-8859-1', 'UTF-8');

    // Order lines
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
        echo mb_convert_encoding('81,'.$skrivKunder['kundeID'].','.$articleCode.','.$order['amount'].','.$order['pricePerPiece'].','.$order['discountPercent'].','.$order['bookaccountNr']
        .','.$order['vatCode'].','.$articleName.',,,,,,,,,,,,,,'."\r\n", 'ISO-8859-1', 'UTF-8');
    }
    $counter++;
}

$sql = "UPDATE ownercompany SET nextInvoiceExportVoucherNumber = ? WHERE id = ?";
$o_query = $o_main->db->query($sql, array($counter, $settingsData['id']));

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
        $fileName = 'export2_' . $o_main->db->insert_id() . '.csv';
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
        $fileName = 'export_' . $o_main->db->insert_id() . '.csv';
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
