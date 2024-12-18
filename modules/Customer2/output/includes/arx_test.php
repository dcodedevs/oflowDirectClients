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

header('Content-Disposition: attachment; filename="arx_test.xml"');

$contactpersonId = $_GET['contactpersonId'] ? $_GET['contactpersonId'] : 0;
$o_query = $o_main->db->get_where('contactperson', array('id' => $contactpersonId));
$cp = $o_query ? $o_query->row_array() : array();

// Keycards
$integration = 'IntegrationArx';
$integration_file = __DIR__ . '/../../../'. $integration .'/internal_api/load.php';
if (file_exists($integration_file)) {
    require_once $integration_file;
    if (class_exists($integration)) {
        if ($api) unset($api);
        $api = new $integration(array(
            'o_main' => $o_main
        ));
    }
}

$response = $api->export_all_data();
$response_xml = new SimpleXMLElement($response);

echo $response_xml->asXml();
?>
