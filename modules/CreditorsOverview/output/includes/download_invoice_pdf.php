<?php
session_start();
// error_reporting(E_ALL);
// ini_set("display_errors", 1);
ini_set('max_execution_time', 600);
// Constants (taken from fw/index.php)
define('FRAMEWORK_DEBUG', FALSE);
define('ACCOUNT_PATH', realpath(__DIR__.'/../../../../')); // this is modified to fit this files location
define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
$v_tmp = explode("/",ACCOUNT_PATH);
$accountname = array_pop($v_tmp);

// Load database
require_once __DIR__ . '/../../../../elementsGlobal/cMain.php';
include_once(dirname(__FILE__).'/readOutputLanguage.php');


$creditorId = intval($_GET['creditor_id']);
$invoiceId = intval($_GET['invoice_id']);
$sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($sql, array($creditorId));
$creditorData = $o_query ? $o_query->row_array() : array();
if($creditorData) {
    require_once __DIR__ . '/../../../'.$creditorData['integration_module'].'/internal_api/load.php';
    if($creditorData['entity_id'] == ""){
        echo $formText_NoEntityId_output;
        // $api2 = new Integration24SevenOffice(array(
        //     'ownercompany_id' => 1,
        //     'identityId' => $creditorData['entity_id'],
        //     'o_main' => $o_main,
        //     'creditorId'=> $creditorData['id']
        //     'getIdentityIdByName' => "Value Accounting Kristiansand AS"
        // ));
    } else {

        $api = new Integration24SevenOffice(array(
            'ownercompany_id' => 1,
            'identityId' => $creditorData['entity_id'],
            'o_main' => $o_main
        ));
        ?>

        <?php
        if($api->error == "") {
            $data = array("invoice_id"=>$invoiceId);
            $fileText = $api->get_invoice_pdf($data);

            $filename = "invoice_".$creditorId."_".$invoiceId.".pdf";
            // var_dump($fileText);
            file_put_contents(__DIR__."/../../../../uploads/".$filename, $fileText);
            // $file = fopen($filename, 'w');
            // fputs($file, $fileText);
            // fclose($file);


            // ensure we don't have any previous output
            if(headers_sent()){
                exit("PDF stream will be corrupted - there is already output from previous code.");
            }

            header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
            header('Pragma: public');
            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');

            // force download dialog
            header('Content-Type: application/force-download');
            header('Content-Type: application/octet-stream', false);
            header('Content-Type: application/download', false);

            // use the Content-Disposition header to supply a recommended filename
            header('Content-Disposition: attachment; filename="'.basename($filename).'";');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: '.filesize(__DIR__."/../../../../uploads/".$filename));
            header('Content-Type: application/octet-stream', false);
            readfile(__DIR__."/../../../../uploads/".$filename);
            exit;
        }
        ?>
        <?php
    }
}

?>
