<?php
// no cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// content type
if (!$_GET['redirectTo']) {
    header("Content-Disposition: attachment; filename=\"fakturaeksport.txt\"");
    header("Content-type: text/plain; charset=UTF-8");
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

// Ownercompany, bookaccounts
$activate_global_export = $_GET['activate_global_export'];
$ownercompany_id = $_GET['ownercompany_id'];
if ($activate_global_export) {
    $o_query = $o_main->db->get('ownercompany_accountconfig');
    $settingsData = $o_query ? $o_query->row_array() : array();
} else {
    $o_query = $o_main->db->query("SELECT * FROM ownercompany WHERE id = ?", array($ownercompany_id));
    $settingsData = $o_query ? $o_query->row_array() : array();
}

// Rounding account
// $o_query = $o_main->db->query("SELECT * FROM bookaccount WHERE id = ?", array($settingsData['accountRoundingsOnInvoice']));
// $bookAccountData = ($o_query ? $o_query->row_array() : array());
// $roundingAccountNr = $bookAccountData['accountNr'];
$roundingAccountNr = $settingsData['accountCodeRoundingsOnInvoice'];

// $o_query = $o_main->db->query("SELECT * FROM bookaccount WHERE id = ?", array($settingsData['accountCustomerLedger']));
// $bookAccountData2 = ($o_query ? $o_query->row_array() : array());
// $roundingAccountNr2 = $bookAccountData2['accountNr'];
$roundingAccountNr2 = $settingsData['accountCodeCustomerLedger'];


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

if(isset($from_id) && is_numeric($from_id) && isset($to_id) && is_numeric($to_id)){
    $where = "WHERE i.id >= ".$o_main->db->escape($from_id)." AND i.id <= ".$o_main->db->escape($to_id)."";
}
else if(isset($from_id) && is_numeric($from_id)){
    $where = "WHERE i.id > ".$o_main->db->escape($from_id)."";
}

if (!$activate_global_export) {
    if (!$where) $where = "WHERE i.ownercompany_id = $ownercompany_id";
    else $where .= " AND i.ownercompany_id = $ownercompany_id";
}

// CSV
$teller = 0;

$utsql = "SELECT i.*, c.*,
i.id invoiceID,
i.external_invoice_nr fakturaID,
cei.external_id kundeID,
oc.external_ownercompany_code externalOwnerCompanyCode
FROM invoice i
LEFT JOIN customer c ON c.id = i.customerId
LEFT JOIN customer_externalsystem_id cei ON cei.customer_id = c.id
LEFT JOIN ownercompany oc ON oc.id = i.ownercompany_id
$where;";

$o_query = $o_main->db->query($utsql);
$skrivKunders = ($o_query ? $o_query->result_array() : array());

function sortBy($field, &$array, $direction = 'asc')
{
    usort($array, create_function('$a, $b', '
        $a = $a["' . $field . '"];
        $b = $b["' . $field . '"];

        if ($a == $b) return 0;

        $direction = strtolower(trim($direction));

        return ($a ' . ($direction == 'desc' ? '>' : '<') .' $b) ? -1 : 1;
    '));

    return true;
}
// Read data
ob_start();
foreach($o_query->result_array() as $skrivKunder) {
    $invoiceLines = array();
    $teller++;

    $fdatcreate = $skrivKunder['invoiceDate'];
    $skreate = explode("-",$fdatcreate);

    $fakturaDato = $skreate[0].$skreate[1].$skreate[2];
    $fakturaAar = $skreate[0];
    $tperiode = intval($skreate[1]);

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
    } else {
        $address = $skrivKunder['paStreet'] . (!empty($skrivKunder['paStreet2']) ? ', ' . $skrivKunder['paStreet2'] : '');
        $postalNumber = $skrivKunder['paPostalNumber'];
        $city = $skrivKunder['paCity'];
    }
    $address = sendIt(100, $address);
    $postalNumber = sendIt(10, $postalNumber);
    $city = sendIt(20, $city);

    $externalOwnerCompanyCode = $skrivKunder['externalOwnerCompanyCode'];

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
        // $vatCode = getOrderVAT($order['vatCode']);
        $vatCode = $order['vatCode'];
        $invoiceLine = array();
        $invoiceLine['output'] = ";;$externalOwnerCompanyCode;AR;TELLER_REPLACE;;;".sendIt(10,$fakturaDato).";".$order['bookaccountNr'].";;;".$order['projectCode'].";;;;;;;;;".$skrivKunder['kundeID'].";;;".$skrivKunder['fakturaID'].";;;;;;$vatCode;;;".($order['gross'] * -1).";;;;;;;;;;;;;;;;;;;;;;;;;;;;x\n";
        $invoiceLine['teller'] = $teller;
        $invoiceLine['date'] = sendIt(10,$fakturaDato);
        array_push($invoiceLines, $invoiceLine);

        $roundingCorrection -= floatval($order['gross']);
    }

    // Rounding correction line
    $roundingCorrection += floatval($skrivKunder['totalInclTax']);
    $roundingCorrection = $roundingCorrection * -1;
    $roundingCorrection = round($roundingCorrection,2);
    if ($roundingCorrection != 0) {
        $invoiceLine = array();
        $invoiceLine['output'] = ";;$externalOwnerCompanyCode;AR;TELLER_REPLACE;;;".sendIt(10,$fakturaDato).";".$roundingAccountNr.";;;;;;;;;;;;".$skrivKunder['kundeID'].";;;".$skrivKunder['fakturaID'].";;;;;;;;;".$roundingCorrection.";;;;;;;;;;;;;;;;;;;;;;;;;;;;x\n";
        $invoiceLine['teller'] = $teller;
        $invoiceLine['date'] = sendIt(10,$fakturaDato);
        array_push($invoiceLines, $invoiceLine);
    }

    // Total line for each order
    $invoiceLine = array();
    $invoiceLine['output'] = ";;$externalOwnerCompanyCode;AR;TELLER_REPLACE;;;".sendIt(10,$fakturaDato).";".$roundingAccountNr2.";;;;;;;;;;;;".$skrivKunder['kundeID'].";;;".$skrivKunder['fakturaID'].";".$skrivKunder['kidNumber'].";".str_replace('-','',$forfallsDato).";;;;;;;".$skrivKunder['totalInclTax'].";;;;;;;;;;;;;;;;;;;;;;;;;;;;x\n";
    $invoiceLine['teller'] = $teller;
    $invoiceLine['date'] = sendIt(10,$fakturaDato);
    array_push($invoiceLines, $invoiceLine);

    if($settingsData['activatePeriodization']){
        foreach($orderList as $order){
            if(intval($order['periodization']) > 0){
                $periodizationMonths = $order['periodizationMonths'];
                $periodizationMonthsArray = explode(",", $periodizationMonths);
                if(intval($order['periodization']) == 2){
                    $periodizationMonthsArray = array();
                    $start    = (new DateTime($order['dateFrom']))->modify('first day of this month');
                    $end      = (new DateTime($order['dateTo']))->modify('first day of next month');
                    $interval = DateInterval::createFromDateString('1 month');
                    $period   = new DatePeriod($start, $interval, $end);
                    foreach ($period as $dt) {
                        array_push($periodizationMonthsArray, $dt->format("mY"));
                    }
                }
                $monthCount = count($periodizationMonthsArray);

                $dontAddLines = false;
                if($monthCount == 1){
                    foreach($periodizationMonthsArray as $key => $periodizationMonthsSingle){
                        $date = array();
                        $date[0] = substr($periodizationMonthsSingle, 0, 2);
                        $date[1] = substr($periodizationMonthsSingle, 2);
                        if(intval($date[0]) == $tperiode && $date[1] == $fakturaAar) {
                            $dontAddLines = true;
                        }
                    }
                }
                if(!$dontAddLines){
                    $invoiceLine = array();
                    $invoiceLine['output'] =  ";;$externalOwnerCompanyCode;AR;TELLER_REPLACE;;;".sendIt(10,$fakturaDato).";".$order['bookaccountNr'].";;;".$order['projectCode'].";;;;;;;;;".$skrivKunder['kundeID'].";;;".$skrivKunder['fakturaID'].";;;;;;;;;".($order['gross'] / ((100 + $order['vatPercent'])/100)).";;;;;;;;;;;;;;;;;;;;;;;;;;;;x\n";
                    $invoiceLine['teller'] = $teller;
                    $invoiceLine['date'] = sendIt(10,$fakturaDato);
                    array_push($invoiceLines, $invoiceLine);

                    $invoiceLine = array();
                    $invoiceLine['output'] =  ";;$externalOwnerCompanyCode;AR;TELLER_REPLACE;;;".sendIt(10,$fakturaDato).";".$settingsData['balancePeriodizationAccountcode'].";;;".$order['projectCode'].";;;;;;;;;".$skrivKunder['kundeID'].";;;".$skrivKunder['fakturaID'].";;;;;;;;;".($order['gross'] / ((100 + $order['vatPercent'])/100) * -1).";;;;;;;;;;;;;;;;;;;;;;;;;;;;x\n";
                    $invoiceLine['teller'] = $teller;
                    $invoiceLine['date'] = sendIt(10,$fakturaDato);
                    array_push($invoiceLines, $invoiceLine);
                }
            }
        }
        foreach($orderList as $order){
            if(intval($order['periodization']) > 0){
                $periodizationMonths = $order['periodizationMonths'];
                $periodizationMonthsArray = explode(",", $periodizationMonths);

                if(intval($order['periodization']) == 2){
                    $periodizationMonthsArray = array();
                    $start    = (new DateTime($order['dateFrom']))->modify('first day of this month');
                    $end      = (new DateTime($order['dateTo']))->modify('first day of next month');
                    $interval = DateInterval::createFromDateString('1 month');
                    $period   = new DatePeriod($start, $interval, $end);
                    foreach ($period as $dt) {
                        array_push($periodizationMonthsArray, $dt->format("mY"));
                    }

                }
                $monthCount = count($periodizationMonthsArray);
                if($monthCount > 0){
                    $dontAddLines = false;
                    if($monthCount == 1){
                        foreach($periodizationMonthsArray as $key => $periodizationMonthsSingle){
                            $date = array();
                            $date[0] = substr($periodizationMonthsSingle, 0, 2);
                            $date[1] = substr($periodizationMonthsSingle, 2);
                            if(intval($date[0]) == $tperiode && $date[1] == $fakturaAar) {
                                $dontAddLines = true;
                            }
                        }
                    }
                    if(!$dontAddLines){
                        $incomeWithoutVat = $order['gross'] / ((100 + $order['vatPercent'])/100);
                        $singleMonthIncome =  $incomeWithoutVat / $monthCount;
                        $date1 = new DateTime($order['dateFrom']);
                        $date2 = new DateTime($order['dateTo']);
                        $totalDays = $date2->diff($date1)->format("%a")+1;

                        $singleDayIncome = round($incomeWithoutVat / intval($totalDays), 2);
                        $prevMonthDate = $order['dateFrom'];

                        $totalIncomeOutputted = 0;
                        foreach($periodizationMonthsArray as $key => $periodizationMonthsSingle){
                            $date = array();
                            $date[0] = substr($periodizationMonthsSingle, 0, 2);
                            $date[1] = substr($periodizationMonthsSingle, 2);
                            if($date[1] != "" && $date[0] != ""){
                                $baseMonth = date("mY", strtotime($baseFakturaDato));
                                $newMonth = date("mY", strtotime($date[1]."-".$date[0]."-01"));
                                if($baseMonth == $newMonth){
                                    $fakturaDato2 = date("Ymd", strtotime($baseFakturaDato));
                                } else {
                                    $fakturaDato2 = $date[1].$date[0]."01";
                                    $teller++;
                                }
                                $fakturaAar2 = $date[1];
                                $tperiode2 = intval($date[0]);
                                if(intval($order['periodization']) == 1){
                                    $currentIncome = $singleMonthIncome;
                                } else if(intval($order['periodization']) == 2){
                                    $currentMonthStart = $date[1]."-".$date[0]."-01";
                                    $currentMonthEnd = date("Y-m-t", strtotime($currentMonthStart));
                                    if($prevMonthDate < $currentMonthStart){
                                        $prevMonthDate = $currentMonthStart;
                                    }
                                    $date1 = new DateTime($prevMonthDate);
                                    $date2 = new DateTime($currentMonthEnd);
                                    $daysInCurrentMonth = $date2->diff($date1)->format("%a")+1;
                                    $currentIncome = $singleDayIncome * intval($daysInCurrentMonth);
                                }

                                if($key == ($monthCount - 1)){
                                    $currentIncome = $incomeWithoutVat - $totalIncomeOutputted;
                                }
                                $currentIncome = round($currentIncome, 2);

                                $totalIncomeOutputted += $currentIncome;

                                $invoiceLine = array();

                                $invoiceLine['output'] =  ";;$externalOwnerCompanyCode;AR;TELLER_REPLACE;;;".sendIt(10,$fakturaDato2).";".$order['bookaccountNr'].";;;".$order['projectCode'].";;;;;;;;;".$skrivKunder['kundeID'].";;;".$skrivKunder['fakturaID'].";;;;;;;;;".($currentIncome*-1).";;;;;;;;;;;;;;;;;;;;;;;;;;;;x\n";
                                $invoiceLine['teller'] = $teller;
                                $invoiceLine['date'] = sendIt(10,$fakturaDato2);
                                array_push($invoiceLines, $invoiceLine);

                                $invoiceLine = array();
                                $invoiceLine['output'] =  ";;$externalOwnerCompanyCode;AR;TELLER_REPLACE;;;".sendIt(10,$fakturaDato2).";".$settingsData['balancePeriodizationAccountcode'].";;;".$order['projectCode'].";;;;;;;;;".$skrivKunder['kundeID'].";;;".$skrivKunder['fakturaID'].";;;;;;;;;".($currentIncome).";;;;;;;;;;;;;;;;;;;;;;;;;;;;x\n";
                                $invoiceLine['teller'] = $teller;
                                $invoiceLine['date'] = sendIt(10,$fakturaDato2);
                                array_push($invoiceLines, $invoiceLine);
                            }
                        }
                    }
                }

            }
        }

        $maxTeller = 0;
        for($x = 0; $x<count($invoiceLines); $x++){
            $invoiceLine = $invoiceLines[$x];
            $tellerInside = $invoiceLine['teller'];
            $date = $invoiceLine['date'];

            $keys = array_keys(array_column($invoiceLines, 'date'), $invoiceLine['date']);
            foreach($keys as $key){
                $invoiceLines[$key]['teller'] = $tellerInside;
            }
            if($tellerInside > $maxTeller){
                $maxTeller = $tellerInside;
            }
        }
        $teller = $maxTeller;
        sortBy('teller',  $invoiceLines);
    }
    foreach($invoiceLines as $invoiceLine) {
        echo str_replace("TELLER_REPLACE", $invoiceLine['teller'], $invoiceLine['output']);
    }
}

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

// function getOrderVAT($vatcodeId) {
//     global $o_main;
//
//     $o_query = $o_main->db->query("SELECT * FROM vatcode WHERE id = ?", array($vatcodeId));
//     $row = ($o_query ? $o_query->row_array() : array());
//
//     $data = array(
//         'mamutCode' => $row['vatCodeMamutExport']
//     );
//
//     return $data;
// }
?>
