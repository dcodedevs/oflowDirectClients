<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Constants (taken from fw/index.php)
define('FRAMEWORK_DEBUG', FALSE);
define('ACCOUNT_PATH', realpath(__DIR__.'/../../../../')); // this is modified to fit init.php location
define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
$v_tmp = explode("/",ACCOUNT_PATH);
$accountname = array_pop($v_tmp);

// Load database
require_once __DIR__ . '/../../../../elementsGlobal/cMain.php';

header('Content-Disposition: attachment; filename="activate_wifi.csv"');

$contactpersonId = $_GET['contactpersonId'] ? $_GET['contactpersonId'] : 0;
$o_query = $o_main->db->get_where('contactperson', array('id' => $contactpersonId));
$cp = $o_query ? $o_query->row_array() : array();

$o_query = $o_main->db->get_where('customer', array('id' => $cp['customerId']));
$customer_data = $o_query ? $o_query->row_array() : array();


$columnNames = "ivenName, Surname,Name,DisplayName,SamAccountName,UserPrincipalName,Office,Company,Description,StreetAddress,City,MobilePhone,HomePage,State,PostalCode,OfficePhone,EmailAddress";

$lineData = array(
    $cp['name'],
    $cp['name'],
    $cp['name'],
    $cp['name'],
    $cp['email'],
    $cp['email'],
    '', // office
    $customer_data['name'], //
    '', // description
    $customer_data['paStreet'], // streetaddress
    $customer_data['paCity'], // city
    $cp['mobile'], // mobilephone
    '', // homepage
    $customer_data['paCountry'], // state
    $customer_data['paPostalNumber'], // postalcode
    $customer_data['phone'], // officephone
    $cp['email'] // emailaddress
);

echo $columnNames . "\n" . implode(',', $lineData);
?>
