<?php

// no cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// content type
header("Content-Disposition: attachment; filename=\"fakturaeksport.txt\"");
header("Content-type: text/plain ");

// db
include("../../../dbConnect.php");
$where = "";

// rounding account
$settingsFind = mysql_query("SELECT * FROM settings;");
$settingsData = mysql_fetch_array($settingsFind);
$bookAccountFind = mysql_query("SELECT * FROM bookaccount WHERE id = '".$settingsData['accountRoundingsOnInvoice']."'");
$bookAccountData = mysql_fetch_array($bookAccountFind);
$roundingAccountNr = $bookAccountData['accountNr'];

// basis config
$basisConfigData = mysql_fetch_assoc(mysql_query("SELECT * FROM invoice_basisconfig"));

if(isset($_GET['fra']) && is_numeric($_GET['fra']) && isset($_GET['til']) && is_numeric($_GET['til'])){
    $where = "AND invoice.id >= '".$_GET['fra']."' AND invoice.id <= '".$_GET['til']."'";
}
else if(isset($_GET['fra']) && is_numeric($_GET['fra'])){
    $where = "AND invoice.id > '".$_GET['fra']."'";
}

$teller = 0;
$utsql = "SELECT invoice.*, customer.*, invoice.id AS fakturaID, customer.id AS kundeID FROM invoice, customer WHERE customer.id = invoice.customerId $where;";

$finnKunder = mysql_query($utsql);
//echo $utsql."\n";
while($skrivKunder = mysql_fetch_array($finnKunder)) {

    $teller++;

    $fdatcreate = $skrivKunder['invoiceDate'];
    $skreate = split("-",$fdatcreate);

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
    $skreate = split("\.",$fdatcreate);
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


    // Order lines
    $roundingCorrection = 0;
    $orderList = mysql_query("SELECT * FROM orders WHERE invoiceNumber = '".$skrivKunder['fakturaID']."'");
    while ($order = mysql_fetch_assoc($orderList)) {
        $projectData = array();
        if ($basisConfigData['activateExportProjectNumber']) {
            $projectData = mysql_fetch_assoc(mysql_query("SELECT * FROM projectforaccounting WHERE id = '".$order['projectFAccNumber']."'"));
        }
        $vatCode = getOrderVAT($order['vatCode']);
        echo "GBAT10;".$teller.";".sendIt(10,$fakturaDato).";1;".$tperiode.";".$fakturaAar.";".$order['bookaccountNr'].";".$vatCode['mamutCode'].";".($order['gross'] * -1).";0;0;;".$address.";".$postalNumber.";".$city.";".$skrivKunder['fakturaID'].";;;;;".$skrivKunder['kundeID']." ".$skrivKunder['name']." ".$skrivKunder['fakturaID'].";".$skrivKunder['kundeID']." ".$skrivKunder['name']." ".$skrivKunder['fakturaID'].";;".$projectData['projectnumber'].";;;T;".($order['gross'] * -1)."\n";
        $roundingCorrection -= floatval($order['gross']);
    }

    // Rounding correction line
    $roundingCorrection += floatval($skrivKunder['totalInclTax']);
    $roundingCorrection = $roundingCorrection * -1;
    $roundingCorrection = round($roundingCorrection,2);
    if ($roundingCorrection != 0) {
        echo "GBAT10;".$teller.";".sendIt(10,$fakturaDato).";1;".$tperiode.";".$fakturaAar.";".$roundingAccountNr.";0;".$roundingCorrection.";0;0;;".$address.";".$postalNumber.";".$city.";".$skrivKunder['fakturaID'].";;;;;".$skrivKunder['kundeID']." ".$skrivKunder['name']." ".$skrivKunder['fakturaID'].";".$skrivKunder['kundeID']." ".$skrivKunder['name']." ".$skrivKunder['fakturaID'].";;;;;T;".$roundingCorrection."\n";
    }

    // Total line for each order
    echo "GBAT10;".$teller.";".sendIt(10,$fakturaDato).";1;".$tperiode.";".$fakturaAar.";1510;0;".$skrivKunder['totalInclTax'].";".$skrivKunder['kundeID'].";0;".$skrivKunder['name'].";".$address.";".$postalNumber.";".$city.";".$skrivKunder['fakturaID'].";".$skrivKunder['kidNumber'].";".str_replace("-","",$forfallsDato).";;;".$skrivKunder['kundeID']." ".$skrivKunder['name']." ".$skrivKunder['fakturaID'].";;;;;;T;".$skrivKunder['totalInclTax']."\n";

}

function sendIt($lengde, $tekst){

    $tekst = ereg_replace("\n","",$tekst);
    $tekst = ereg_replace("\r","",$tekst);

    if(strlen($tekst) <= $lengde){
        return $tekst;
    }
    else{
        return substr($tekst,0,$lengde);
    }
}

function getOrderVAT($vatcodeId) {
    $q = "SELECT * FROM vatcode WHERE id = '".$vatcodeId."'";
    $res = mysql_query($q);
    $row = mysql_fetch_array($res);

    $data = array(
        'mamutCode' => $row['vatCodeMamutExport']
    );

    return $data;
}

?>
