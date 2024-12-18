<?php
// Database
define('FRAMEWORK_DEBUG', FALSE);
define('ACCOUNT_PATH', realpath(__DIR__.'/../../../../../../')); // this is modified to fit this files location
define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
$v_tmp = explode("/",ACCOUNT_PATH);
$accountname = array_pop($v_tmp);

// Load database
require_once __DIR__ . '/../../../../../../elementsGlobal/cMain.php';
// PHPExcel lib
require_once __DIR__ . '/PHPExcel/Classes/PHPExcel.php';

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

// Create new PHPExcel object
$excel = new PHPExcel();
// Headings
$column_headings = array(
    'A' => $formText_OrderGroup_output,
    'B' => $formText_InvoiceDate_output,
    'C' => $formText_BookAccountNr_output,
    'D' => $formText_CustomerId_output,
    'E' => $formText_mamutCode_output,
    'F' => $formText_InvoiceNumber_output,
    'G' => $formText_KidNumber_output,
    'H' => $formText_Gross_output,
    'I' => $formText_DueDate_output,
    'J' => $formText_CustomerName_output,
    'K' => $formText_Address_output,
    'L' => $formText_PostalNumber_output,
    'M' => $formText_City_output,
);

// Setup headings
foreach ($column_headings as $col => $title) {
    $excel->setActiveSheetIndex(0)->setCellValue($col.'1', $title);
}

// Counters
$group = 0;
$line = 1;

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
    $where = " AND invoice.ownercompany_id = ".$o_main->db->escape($ownercompany_id)." ";
    $ceiWhere = " AND cei.ownercompany_id = ".$o_main->db->escape($ownercompany_id)." ";
}

if(isset($from_id) && is_numeric($from_id) && isset($to_id) && is_numeric($to_id)){
    $where .= "AND invoice.id >= ".$from_id." AND invoice.id <= ".$to_id."";
}
else if(isset($from_id) && is_numeric($from_id)){
    $where .= "AND invoice.id > ".$from_id."";
}
$utsql = "SELECT invoice.*, customer.*,
invoice.id AS invoiceID,
invoice.external_invoice_nr AS fakturaID,
cei.external_id AS kundeID
FROM invoice
LEFT JOIN customer ON customer.id = invoice.customerId
LEFT JOIN ownercompany oc ON oc.id = invoice.ownercompany_id
LEFT JOIN customer_externalsystem_id cei ON cei.customer_id = customer.id $ceiWhere
WHERE 1=1 $where;";
$o_query = $o_main->db->query($utsql);
$skrivKunders = ($o_query ? $o_query->result_array() : array());
// Read data
foreach($skrivKunders as $skrivKunder) {

    $group++;

    $fdatcreate = $skrivKunder['invoiceDate'];
    $skreate = explode("-",$fdatcreate);

    $fakturaDato = $skreate[0].$skreate[1].$skreate[2];
    $fakturaAar = $skreate[0];
    $tperiode = $skreate[1];

    if($tperiode[0] == "0"){
        $tperiode = $tperiode[0];
    }

    $konto = 3010;
    $mvakode = 10;

    if($skrivKunder['taxPercent'] == 0 || $skrivKunder['taxPercent'] == ""){
        $konto = 3110;
        $mvakode = 1;
    }

    $fdatcreate = $skrivKunder['dueDate'];
    $skreate = explode("\.",$fdatcreate);
    $forfallsDato = $skreate[2].$skreate[1].$skreate[0];
    //$momsuten = number_format();

    // Addresss
    if ($skrivKunder['useOwnInvoiceAdress']) {
        $address = $skrivKunder['iaStreet1'] . (!empty($skrivKunder['iaStreet2']) ? ', ' . $skrivKunder['iaStreet2'] : '');
        $postalNumber = $skrivKunder['iaPostalNumber'];
        $city = $skrivKunder['iaCity'];
    }
    else {
        $address = $skrivKunder['paStreet'] . (!empty($skrivKunder['paStreet2']) ? ', ' . $skrivKunder['paStreet2'] : '');
        $postalNumber = $skrivKunder['paPostalNumber'];
        $city = $skrivKunder['paCity'];
    }

    $address = sendIt(100, $address);
    $postalNumber = sendIt(10, $postalNumber);
    $city = sendIt(20, $city);
    $excel->setActiveSheetIndex(0)->getStyle("H")->getNumberFormat()->setFormatCode('0.00');
    // Order lines
    $roundingCorrection = 0;

    $o_query = $o_main->db->query("SELECT orders.* FROM orders JOIN customer_collectingorder ON customer_collectingorder.id = orders.collectingorderId WHERE customer_collectingorder.invoiceNumber = ?", array($skrivKunder['invoiceID']));
    $orderList = ($o_query ? $o_query->result_array() : array());
    foreach($orderList as $order){
        $line++;

        $projectData = array();
        if ($order['projectFAccNumber']) {
            $o_query = $o_main->db->query("SELECT * FROM projectforaccounting WHERE id = ?", array($order['projectFAccNumber']));
            $projectData = ($o_query ? $o_query->row_array() : array());
        }
        // $vatCode = getOrderVAT($order['vatCode']);

        $excel->setActiveSheetIndex(0)
            ->setCellValue('A'.$line, $group)
            ->setCellValueExplicit('B'.$line, ''.sendIt(10,date('dmY',strtotime($fakturaDato))).'', PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValue('C'.$line, $order['bookaccountNr'])
            ->setCellValue('D'.$line, 0)
            ->setCellValue('E'.$line, $order['vatCode'])
            ->setCellValue('F'.$line, $skrivKunder['fakturaID'])
            ->setCellValue('G'.$line, '')
            ->setCellValue('H'.$line, number_format((float)($order['gross'] * -1), 2, ',', ' '))
            ->setCellValue('I'.$line, '')
            ->setCellValue('J'.$line, '')
            ->setCellValue('K'.$line, $address)
            ->setCellValue('L'.$line, $postalNumber)
            ->setCellValue('M'.$line, $city);
        $roundingCorrection -= floatval($order['gross']);
    }

    // Rounding correction line
    $roundingCorrection += floatval($skrivKunder['totalInclTax']);
    $roundingCorrection = $roundingCorrection * -1;
    $roundingCorrection = round($roundingCorrection,2);
    if ($roundingCorrection != 0) {
        $line++;
        $excel->setActiveSheetIndex(0)
            ->setCellValue('A'.$line, $group)
            ->setCellValueExplicit('B'.$line, ''.sendIt(10,date('dmY',strtotime($fakturaDato))).'', PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValue('C'.$line, $roundingAccountNr)
            ->setCellValue('D'.$line, 0)
            ->setCellValue('E'.$line, 0)
            ->setCellValue('F'.$line, $skrivKunder['fakturaID'])
            ->setCellValue('G'.$line, '')
            ->setCellValue('H'.$line, number_format((float)$roundingCorrection, 2, ',', ' '))
            ->setCellValue('I'.$line, '')
            ->setCellValue('J'.$line, '')
            ->setCellValue('K'.$line, $address)
            ->setCellValue('L'.$line, $postalNumber)
            ->setCellValue('M'.$line, $city);
    }


    // Total line for each order
    $line++;
    $excel->setActiveSheetIndex(0)
        ->setCellValue('A'.$line, $group)
        ->setCellValueExplicit('B'.$line, ''.sendIt(10,date('dmY',strtotime($fakturaDato))).'', PHPExcel_Cell_DataType::TYPE_STRING)
        ->setCellValue('C'.$line, $skrivKunder['kundeID'])
        ->setCellValue('D'.$line, $skrivKunder['kundeID'])
        ->setCellValue('E'.$line, 0)
        ->setCellValue('F'.$line, $skrivKunder['fakturaID'])
        ->setCellValueExplicit('G'.$line, ''.$skrivKunder['kidNumber'].'', PHPExcel_Cell_DataType::TYPE_STRING)
        ->setCellValue('H'.$line, number_format((float)$skrivKunder['totalInclTax'], 2, ',', ' '))
        ->setCellValueExplicit('I'.$line, ''.date('dmY', strtotime(str_replace("-","",$forfallsDato))).'', PHPExcel_Cell_DataType::TYPE_STRING)
        ->setCellValue('J'.$line, $skrivKunder['name'])
        ->setCellValue('K'.$line, $address)
        ->setCellValue('L'.$line, $postalNumber)
        ->setCellValue('M'.$line, $city);
}

// Rename worksheet
$excel->getActiveSheet()->setTitle('Export');

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$excel->setActiveSheetIndex(0);

// Headers
if (!$_GET['redirectTo']) {
    header('Content-Type: application/vnd.ms-excel' );
    header('Content-Disposition: attachment;filename="export.csv"');
}
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

// Output
$objWriter = PHPExcel_IOFactory::createWriter($excel, 'CSV');
$objWriter->setDelimiter(";")->setEnclosure('')->setLineEnding("\r\n");

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

        $objWriter->save($filePath);


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

        $objWriter->save($filePath);


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
    $objWriter->save('php://output');
}
else {
    header('Location: ' . $_GET['redirect_to']);
}

/*****************************************************************************
 * Functions
 *****************************************************************************
 */

// Formating
function sendIt($lengde, $tekst){
	$tekst = str_replace(array("\n", "\r"), " ", $tekst);
    if(strlen($tekst) <= $lengde){
        return $tekst;
    }
    else{
        return substr($tekst,0,$lengde);
    }
}

// Ger order VAT
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
