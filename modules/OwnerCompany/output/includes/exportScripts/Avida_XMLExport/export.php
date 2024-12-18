<?php
// no cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// content type
if (!$_GET['redirectTo']) {
    header("Content-Disposition: attachment; filename=\"fakturaeksport.xml\"");
    Header('Content-type: text/xml');
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

if(!function_exists("proc_rem_style")) include(__DIR__."/functions.php");

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

// basis config
$o_query = $o_main->db->query("SELECT * FROM invoice_basisconfig");
$basisConfigData = ($o_query ? $o_query->row_array() : array());


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
if (!$activate_global_export) {
    $where = "AND invoice.ownercompany_id = ".$o_main->db->escape($ownercompany_id)." ";
}

if(isset($from_id) && is_numeric($from_id) && isset($to_id) && is_numeric($to_id)){
    $where .= "AND invoice.id >= ".$o_main->db->escape($from_id)." AND invoice.id <= ".$o_main->db->escape($to_id)."";
}
else if(isset($from_id) && is_numeric($from_id)){
    $where .= "AND invoice.id > ".$o_main->db->escape($from_id)."";
}

$teller = 0;
$utsql = "SELECT invoice.*, customer.*,invoice.id as invoiceID, invoice.external_invoice_nr AS fakturaID, customer.id AS kundeID, 0 as creditInvoice FROM invoice, customer WHERE customer.id = invoice.customerId AND invoice.totalInclTax >= 0 $where; ";
$o_query = $o_main->db->query($utsql);
$skrivKunders = ($o_query ? $o_query->result_array() : array());

//credit invoices
$utsql = "SELECT invoice.*, customer.*,invoice.id as invoiceID, invoice.external_invoice_nr AS fakturaID, customer.id AS kundeID, 1 as creditInvoice FROM invoice, customer WHERE customer.id = invoice.customerId AND invoice.totalInclTax < 0 $where; ";
$o_query = $o_main->db->query($utsql);
$skrivKunders2 = ($o_query ? $o_query->result_array() : array());

$skrivKunders = array_merge($skrivKunders, $skrivKunders2);
// Read data
$xml = new SimpleXMLElement('<Ledger/>');
$xml->addAttribute("productionDate", date("Y-m-d"));

$decimalPlaces = $settingsData['numberDecimalsOnInvoice'] ? intval($settingsData['numberDecimalsOnInvoice']) : 0;

ob_clean();
foreach($skrivKunders as $skrivKunder) {

    $teller++;

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
        $address = $skrivKunder['iaStreet1'];
        $address2 = $skrivKunder['iaStreet2'];
        $postalNumber = $skrivKunder['iaPostalNumber'];
        $city = $skrivKunder['iaCity'];
    } else {
        $address = $skrivKunder['paStreet'];
        $address2 = $skrivKunder['paStreet2'];
        $postalNumber = $skrivKunder['paPostalNumber'];
        $city = $skrivKunder['paCity'];
    }
    $address = sendIt(100, $address);
    $postalNumber = sendIt(10, $postalNumber);
    $city = sendIt(20, $city);


    $utsql = "SELECT oc.* FROM ownercompany oc WHERE oc.id = ?";
    $o_query = $o_main->db->query($utsql, array($skrivKunder['ownercompany_id']));
    $ownercompany = ($o_query ? $o_query->row_array() : array());

    $utsql = "SELECT * FROM customer_externalsystem_id  WHERE customer_id = ?";
    $o_query = $o_main->db->query($utsql, array($skrivKunder['kundeID']));
    $customer_externalsystem_id = ($o_query ? $o_query->row_array() : array());

    $clientNo = $ownercompany['clientNumberFactoring'];
    $custLegalNo = $skrivKunder['publicRegisterId'];
    $custNo = $customer_externalsystem_id['external_id'];
    $name = $skrivKunder['name'];
    $amount = $skrivKunder['totalInclTax'];
    $vatAmount = $skrivKunder['tax'];
    $currency = $skrivKunder['currencyName'];
    if($currency == 'EMPTY_CURRENCY') {
        $currency = "NOK";
    }
    $invoiceNo = $skrivKunder['external_invoice_nr'];
    $paymentRefNo = $skrivKunder['kidNumber'];
    $email = $skrivKunder['invoiceEmail'];
    $creditInvoice = false;
    if($amount > 0){
        $invoice = $xml->addChild('Invoice');
    } else {
        $invoice = $xml->addChild('Credit');
        $creditInvoice = true;
    }

    $invoice->addChild("ClientNo", $clientNo);
    $invoice->addChild("CustLegalNo", $custLegalNo);
    $invoice->addChild("CustNo", $custNo);
    $invoice->addChild("Name", $name);
    $invoice->addChild("Adress", $address);
    $invoice->addChild("Adress2", $address2);
    $invoice->addChild("PostCode", $postalNumber);
    $invoice->addChild("City", $city);
    if($creditInvoice){
        $invoice->addChild("CreditNo", $invoiceNo);
        $invoice->addChild("CreditDate", date("Y-m-d", strtotime($fakturaDato)));
        $invoice->addChild("CreditDueDate", date("Y-m-d", strtotime($forfallsDato)));
        $invoice->addChild("CreditRefType", 0);
        $invoice->addChild("CreditRefNo", $skrivKunder['creditRefNo']);
        $invoice->addChild("Amount", number_format($amount, 2, '.', '')*-1);
        $invoice->addChild("VATAmount", number_format($vatAmount, 2, '.', '')*-1);
    } else {
        $invoice->addChild("InvoiceNo", $invoiceNo);
        $invoice->addChild("InvoiceDate", date("Y-m-d", strtotime($fakturaDato)));
        $invoice->addChild("InvoiceDueDate", date("Y-m-d", strtotime($forfallsDato)));
        $invoice->addChild("Amount", number_format($amount, 2, '.', ''));
        $invoice->addChild("VATAmount", number_format($vatAmount, 2, '.', ''));
    }
    $invoice->addChild("Currency", $currency);
    if(!$creditInvoice){
        $invoice->addChild("PaymentRefNo", $paymentRefNo);
    }
    $invoice->addChild("Email", $email);

    // Order lines
    $roundingCorrection = 0;
    $o_query = $o_main->db->query("SELECT orders.* FROM orders JOIN customer_collectingorder ON customer_collectingorder.id = orders.collectingorderId WHERE customer_collectingorder.invoiceNumber = ?", array($skrivKunder['invoiceID']));
    $orderList = ($o_query ? $o_query->result_array() : array());
    foreach($orderList as $order){
        $projectData = array();

        if ($order['projectFAccNumber']) {
            $o_query = $o_main->db->query("SELECT * FROM projectforaccounting WHERE id = ?", array($order['projectFAccNumber']));
            $projectData = ($o_query ? $o_query->row_array() : array());
        }


        $description = $order['articleName'];
        $quantity = $order['amount'];
        $unitPrice = $order['pricePerPiece'];
        $vatPct = $order['vatPercent'];
        $lineAmountExclVat = round($order['priceTotal'], $decimalPlaces);
        $lineAmountInclVat = round($order['gross'], $decimalPlaces);
        $vatAmount = round($lineAmountInclVat - $lineAmountExclVat, $decimalPlaces);
        if($creditInvoice){
            $unitPrice = $unitPrice*-1;
            $vatAmount = $vatAmount*-1;
            $lineAmountExclVat = $lineAmountExclVat*-1;
            $lineAmountInclVat = $lineAmountInclVat*-1;
        }
        $line = $invoice->addChild("Line");
        $line->addChild("ItemNo", $order['id']);
        $line->addChild("Description", $description);
        $line->addChild("Quantity", $quantity);
        $line->addChild("UnitPrice", number_format($unitPrice, 2, '.', ''));
        $line->addChild("VATPct", number_format($vatPct, 2, '.', ''));
        $line->addChild("VATAmount", number_format($vatAmount, 2, '.', ''));
        $line->addChild("DiscountPct", number_format($order['discountPercent'], 2, '.', ''));
        $line->addChild("LineAmountExclVAT", number_format($lineAmountExclVat, 2, '.', ''));
        $line->addChild("LineAmountInclVAT", number_format($lineAmountInclVat, 2, '.', ''));

    }

}


if($_GET['export2']){
    // Save in export2 history
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
        $fileName = 'export2_' . $o_main->db->insert_id() . '.xml';
        $filePath = $fileDir . $fileName;

        $fp = fopen($filePath, 'w');
        fwrite($fp, $xml->asXML());
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
        $fileName = 'export_' . $o_main->db->insert_id() . '.xml';
        $filePath = $fileDir . $fileName;

        $fp = fopen($filePath, 'w');
        fwrite($fp, $xml->asXML());
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
    print($xml->asXML());
}
else {
    header('Location: ' . $_GET['redirect_to']);
}



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

    $o_query = $o_main->db->query("SELECT * FROM vatcode WHERE id = ?", array($vatcodeId));
    $row = ($o_query ? $o_query->row_array() : array());

    $data = array(
        'mamutCode' => $row['vatCodeMamutExport']
    );

    return $data;
}
function getOrderVAT2($vatcodeId) {
    global $o_main;

    $o_query = $o_main->db->query("SELECT * FROM vatcode WHERE id = ?", array($vatcodeId));
    $row = ($o_query ? $o_query->row_array() : array());

    return $row;
}
?>
