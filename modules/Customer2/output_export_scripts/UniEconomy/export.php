<?php
// no cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// content type
header("Content-Disposition: attachment; filename=\"customers.csv\"");
Header('Content-type: application/csv');

// Database
define('FRAMEWORK_DEBUG', FALSE);
define('ACCOUNT_PATH', realpath(__DIR__.'/../../../../')); // this is modified to fit this files location
define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
$v_tmp = explode("/",ACCOUNT_PATH);
$accountname = array_pop($v_tmp);

// Load database
require_once __DIR__ . '/../../../../elementsGlobal/cMain.php';
// PHPExcel lib

// Input language
// NOTE - chooseLanguage var?
$_GET['folder'] = "input";
require_once __DIR__ . '/../../input/includes/readInputLanguage.php';

$_GET['folder'] = "output";
require_once __DIR__ . '/../../output/includes/readOutputLanguage.php';

// Query
$where = ' AND (customer.content_status IS null OR customer.content_status = 0)';
$ceiWhere = '';
// CSV
$utsql = "SELECT customer.*, cei.external_id AS kundeID
FROM customer
LEFT JOIN customer_externalsystem_id cei ON cei.customer_id = customer.id $ceiWhere
WHERE 1=1 $where;";
$o_query = $o_main->db->query($utsql);
$skrivKunders = ($o_query ? $o_query->result_array() : array());
// Read data
ob_start();
foreach($skrivKunders as $skrivKunder) {
    $customerName = $skrivKunder['name'];
    if(trim($skrivKunder['middlename']) != ""){
        $customerName .= trim($skrivKunder['middlename']);
    }
    if(trim($skrivKunder['lastname']) != ""){
        $customerName .= trim($skrivKunder['lastname']);
    }
    echo mb_convert_encoding('30,'.$skrivKunder['kundeID'].','.$customerName.','.$skrivKunder['paStreet'].','.$skrivKunder['paStreet2'].','.$skrivKunder['paPostalNumber'].','
    .$skrivKunder['paCity'].',,,'.$skrivKunder['credittimeDays'].',,,,'.$skrivKunder['phone'].',,,,,,'.$skrivKunder['publicRegisterId'].',,'.$skrivKunder['paCountry'].','.PHP_EOL, 'ISO-8859-1', 'UTF-8');
}

$csv = ob_get_clean();

// Output file
echo $csv;

?>
