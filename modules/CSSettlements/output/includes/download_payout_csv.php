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

header('Content-Disposition: attachment; filename="settlement.csv"');

$settlementId= $_GET['settlementId'] ? $_GET['settlementId'] : 0;
$o_query = $o_main->db->get_where('collectingcompany_settlement', array('id' => $settlementId));
$settlement = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM ownercompany";
$o_query = $o_main->db->query($s_sql);
$ownercompany = ($o_query ? $o_query->row_array() : array());


$columnNames = "Date,Company,Amount,Bank account";
$lineData = array(
    $settlement['date'],
    trim($ownercompany['companyname']),
    $settlement['collectingcompany_total_amount'],
    $ownercompany['companyaccount']
);
echo $columnNames . "\n" . implode(',', $lineData) . "\n";

$s_sql = "SELECT * FROM creditor_settlement WHERE content_status < 2 AND collectingcompany_settlement_id = ? ORDER BY created DESC";
$o_query = $o_main->db->query($s_sql, array($settlement['id']));
$creditor_settlements = ($o_query ? $o_query->result_array() : array());
foreach($creditor_settlements as $creditor_settlement)
{
    $s_sql = "SELECT creditor.*, customer.* FROM customer LEFT JOIN creditor ON creditor.customer_id = customer.id  WHERE creditor.id = ?";
    $o_query = $o_main->db->query($s_sql, array($creditor_settlement['creditor_id']));
    $creditor = ($o_query ? $o_query->row_array() : array());

    $lineData = array(
        $settlement['date'],
        trim($creditor['name']." ".$creditor['middlename']." ".$creditor['lastname']),
        $creditor_settlement['creditor_amount'],
        $creditor['bank_account'],
    );
}
echo implode(',', $lineData) . "\n";

?>
