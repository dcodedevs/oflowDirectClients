<?php
/*
 * Version 8.100
 * Modified 2017-08-14
*/
ob_start();
// Database
define('FRAMEWORK_DEBUG', FALSE);
define('ACCOUNT_PATH', realpath(__DIR__.'/../../../../../../')); // this is modified to fit this files location
define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
$v_tmp = explode("/",ACCOUNT_PATH);
$accountname = array_pop($v_tmp);

// Load database
require_once __DIR__ . '/../../../../../../elementsGlobal/cMain.php';

// Input language
// NOTE - chooseLanguage var?
$_GET['folder'] = "input";
require_once __DIR__ . '/../../../../input/includes/readInputLanguage.php';

$_GET['folder'] = "output";
require_once __DIR__ . '/../../../../output/includes/readOutputLanguage.php';


// Rounding account
$ownerCompanyId = $_GET['ownerCompany'];
$o_query = $o_main->db->query("SELECT * FROM ownercompany WHERE id = ?", array($ownerCompanyId));
$settingsData = ($o_query ? $o_query->row_array() : array());

$o_query = $o_main->db->query("SELECT * FROM bookaccount WHERE id = ?", array($settingsData['accountRoundingsOnInvoice']));
$bookAccountData = ($o_query ? $o_query->row_array() : array());
$roundingAccountNr = $bookAccountData['accountNr'];

// Basis config
$o_query = $o_main->db->query("SELECT * FROM ownercompany WHERE id = ?", array($ownerCompanyId));
$basisConfigData = ($o_query ? $o_query->row_array() : array());

$o_main->db->query("INSERT INTO avtalegiro_export_files(created) VALUES(NOW())");
$l_avtalegiro_export_file_id = $o_main->db->insert_id();

$l_avtalegiro_num = sprintf("%06d", $l_avtalegiro_export_file_id % 1000000);


$s_buffer = '';
//Data sender (customer_id, lenght: 8)
$s_data_sender = sprintf("%08d",$settingsData['avtalegiro_data_sender']);
//Shipping number (day + month + counter, unique, length - 7)
$s_shipping_number = '0'.$l_avtalegiro_num; // - 0 + avtalegiro_num
//Data receiver (customer_id, Nets'ID always = 00008080, lenght: 8)
$s_data_receiver = '00008080';
//Assignment number (day + month + counter, unique, length - 7)
//Must be unique numbering of assignments per. Payee recipient agreement 12 months + one day in advance. (Eg DD MM (day, month) + serial number and the like)
$s_assignment_number = '1'.$l_avtalegiro_num; // - 1 + avtalegiro_num
$s_assignment_number_delete = '2'.$l_avtalegiro_num; // 2 + avtalegiro_num for delete messages)
//Assignment account (numeric, unique, lenght - 11)
$s_assignment_account = sprintf("%011d", $settingsData['companyaccount']); // Ownercompany bank account

//Start shipping
$s_buffer .= 'NY000010'.$s_data_sender.$s_shipping_number.$s_data_receiver.sprintf("%049d", 0).PHP_EOL;


//Start payment records
$s_buffer .= 'NY210020'.sprintf("%09d", 0).$s_assignment_number.$s_assignment_account.sprintf("%045d", 0).PHP_EOL;

$l_count = 0;
$l_payment_record_count = 2;
$l_payment_trans_count = 0;
$l_payment_total = 0;
//First due date (DDMMYY, length - 6)
$l_first_due_date = 0;
$l_last_due_date = 0;


// Query
$s_sql_where = "";
if(isset($_GET['fra']) && is_numeric($_GET['fra']) && isset($_GET['til']) && is_numeric($_GET['til'])){
    $where = "AND invoice.external_invoice_nr >= ".$o_main->db->escape($_GET['fra'])." AND invoice.external_invoice_nr <= ".$o_main->db->escape($_GET['til'])."";
}
else if(isset($_GET['fra']) && is_numeric($_GET['fra'])){
    $where = "AND invoice.external_invoice_nr > ".$o_main->db->escape($_GET['fra'])."";
}

// Read billing invoices
$s_sql = "SELECT invoice.*, customer.*, invoice.id invoice_id, invoice.external_invoice_nr AS fakturaID, customer.id AS kundeID FROM invoice, customer WHERE customer.id = invoice.customerId AND invoice.ownercompany_id = ".$o_main->db->escape($ownerCompanyId)." AND invoice.drawing_agreement = 1 AND (invoice.avtalegiro_delete_request IS NULL OR invoice.avtalegiro_delete_request = 0)".$s_sql_where;

$o_query = $o_main->db->query($s_sql);
$v_contents = ($o_query ? $o_query->result_array() : array());
// Read data
foreach($v_contents as $v_content) {
	//Transaction amount (numeric, lenght - 17)
	$l_amount = intval($v_content['totalInclTax']*100);
	$l_payment_total += $v_content['totalInclTax'];
	//Customer identification KID (numeric, lenght - 25)
	//The field must be filled in if the payment requirement is valid against the payer's fixed assignment. Fill in with valid KID following specifications specified in the Registration Form for the AvtaleGiro and OCR giro. KID is corrected, without special characters, and any available positions are blanked. Letters can not be used.
	$s_customer_kid = sprintf("%25s", $v_content['kidNumber']);
	//Payer name (alphanumeric, lenght - 10)
	$s_payer_name = str_pad(substr($v_content['name'], 0, 10), 10);
	//Transaction type (numeric, lenght - 2)
	//- No notification from bank - Transtype = 02 (zero-two)
	//- Notification from bank (AgreementGiro info) - Transtype = 21 (two-one)
	$s_transaction_type = '21';
	//Transaction number (numeric, lenght - 7)
	//Unique numbering of transaction per. Assignments in ascending sequence.
	//NB: The same transaction number must be used for the entire transaction. That is, amounts 1 and 2 and the specification records. The transaction number must be greater than zero. If the same transaction number is not used throughout the transaction, the transaction will be rejected.
	$s_transaction_number = sprintf("%07d", $l_count);
	//Maturity date (DDMMYY, length - 6)
	//Must not be more than 12 months in advance. If the date specified is not a business day in Nets, ie Saturday, Sunday or moving holiday, Nets will use the following business day as due date. Nets will not change the date of the transaction itself.
	$l_due_date = strtotime($v_content['dueDate']);
	if($l_first_due_date == 0 || $l_first_due_date > $l_due_date) $l_first_due_date = $l_due_date;
	if($l_last_due_date < $l_due_date) $l_last_due_date = $l_due_date;
	$s_transaction_maturity_date = date('dmy', $l_due_date);

	//Reference (alphanumeric, lenght - 25)
	//The field can be used as an information box for payment of payment. For example, to inform pay if the transaction is notified to the collective alerts. The foreign reference is transferred to the payer's bank statement and the AgreementGiro info. If the foreign reference is not used, the field must be blanked. Foreign reference overrides fixed text on payer's fixed payment assignments.
	$s_reference = '. Invoice: '.$v_content['fakturaID'];
	$s_reference = str_pad(trim(substr($settingsData['avtalegiro_drawing_reference_text'], 0, (25 - strlen($s_reference)))).$s_reference, 25);

	//Amount item 1
	$s_buffer .= 'NY21'.$s_transaction_type.'30'.$s_transaction_number.$s_transaction_maturity_date.str_pad('', 11).sprintf("%017d", $l_amount).$s_customer_kid.sprintf("%06d", 0).PHP_EOL;

	//Amount item 2
	$s_buffer .= 'NY21'.$s_transaction_type.'31'.$s_transaction_number.$s_payer_name.str_pad('', 25).$s_reference.sprintf("%05d", 0).PHP_EOL;
	$l_payment_record_count+=2;
	$l_payment_trans_count+=2;

	//Sepecification//
	//--------------------
	//$l_payment_record_count+=2;
	//$l_payment_trans_count++;

	$o_main->db->query("UPDATE invoice SET avtalegiro_export_file_id = ".$o_main->db->escape($l_avtalegiro_export_file_id)." WHERE id = ".$o_main->db->escape($v_content['invoice_id'])."");

	$l_count++;
}

$s_first_male_date = date('dmy', $l_first_due_date);
$s_last_duration_date = date('dmy', $l_last_due_date);
//End payment records
$s_buffer .= 'NY210088'.sprintf("%08d", $l_payment_trans_count).sprintf("%08d", $l_payment_record_count).sprintf("%017d", $l_payment_total).$s_first_male_date.$s_last_duration_date.sprintf("%027d", 0).PHP_EOL;



//Start delete request records
$s_buffer .= 'NY213620'.sprintf("%09d", 0).$s_assignment_number_delete.$s_assignment_account.sprintf("%045d", 0).PHP_EOL;


$l_delete_record_count = 0;
$l_delete_trans_count = 0;
$l_delete_total = 0;
$l_first_due_date_delete = 0;
$l_last_due_date_delete = 0;


// Read deleting requests
$s_sql = "SELECT invoice.*, customer.*, invoice.id invoice_id, invoice.external_invoice_nr AS fakturaID, customer.id AS kundeID FROM invoice, customer WHERE customer.id = invoice.customerId AND invoice.ownercompany_id = ".$o_main->db->escape($ownerCompanyId)." AND invoice.drawing_agreement = 1 AND invoice.avtalegiro_delete_request = 1".$s_sql_where;

$o_query = $o_main->db->query($s_sql);
$v_contents = ($o_query ? $o_query->result_array() : array());
// Read data
foreach($v_contents as $v_content) {
	//Transaction amount (numeric, lenght - 17)
	$l_amount = intval($v_content['totalInclTax']*100);
	$l_delete_total += $v_content['totalInclTax'];
	//Customer identification KID (numeric, lenght - 25)
	//The field must be filled in if the payment requirement is valid against the payer's fixed assignment. Fill in with valid KID following specifications specified in the Registration Form for the AvtaleGiro and OCR giro. KID is corrected, without special characters, and any available positions are blanked. Letters can not be used.
	$s_customer_kid = sprintf("%25s", $v_content['kidNumber']);
	//Payer name (alphanumeric, lenght - 10)
	$s_payer_name = str_pad(substr($v_content['name'], 0, 10), 10);
	//Transaction type (numeric, lenght - 2)
	//- Always 93
	$s_transaction_type = '93';
	//Transaction number (numeric, lenght - 7)
	//Unique numbering of transaction per. Assignments in ascending sequence.
	//NB: The same transaction number must be used for the entire transaction. That is, amounts 1 and 2 and the specification records. The transaction number must be greater than zero. If the same transaction number is not used throughout the transaction, the transaction will be rejected.
	$s_transaction_number = sprintf("%07d", $l_count);
	//Maturity date (DDMMYY, length - 6)
	//Must not be more than 12 months in advance. If the date specified is not a business day in Nets, ie Saturday, Sunday or moving holiday, Nets will use the following business day as due date. Nets will not change the date of the transaction itself.
	$l_due_date = strtotime($v_content['dueDate']);
	if($l_first_due_date_delete == 0 || $l_first_due_date_delete > $l_due_date) $l_first_due_date_delete = $l_due_date;
	if($l_last_due_date_delete < $l_due_date) $l_last_due_date_delete = $l_due_date;
	$s_transaction_maturity_date = date('dmy', $l_due_date);

	//Reference (alphanumeric, lenght - 25)
	//The field can be used as an information box for payment of payment. For example, to inform pay if the transaction is notified to the collective alerts. The foreign reference is transferred to the payer's bank statement and the AgreementGiro info. If the foreign reference is not used, the field must be blanked. Foreign reference overrides fixed text on payer's fixed payment assignments.
	$s_reference = '. Invoice: '.$v_content['fakturaID'];
	$s_reference = str_pad(trim(substr($settingsData['avtalegiro_drawing_reference_text'], 0, (25 - strlen($s_reference)))).$s_reference, 25);

	//Delete request 1
	$s_buffer .= 'NY21'.$s_transaction_type.'30'.$s_transaction_number.$s_transaction_maturity_date.str_pad('', 11).sprintf("%017d", $l_amount).$s_customer_kid.sprintf("%06d", 0).PHP_EOL;

	//Delete request 2
	$s_buffer .= 'NY21'.$s_transaction_type.'31'.$s_transaction_number.$s_payer_name.str_pad('', 25).$s_reference.sprintf("%05d", 0).PHP_EOL;
	$l_delete_record_count+=2;
	$l_delete_trans_count+=2;

	//Sepecification//
	//--------------------
	//$l_delete_record_count+=2;
	//$l_delete_trans_count++;

	$o_main->db->query("UPDATE invoice SET avtalegiro_delete_request_file_id = ".$o_main->db->escape($l_avtalegiro_export_file_id)." WHERE id = ".$o_main->db->escape($v_content['invoice_id'])."");

	$l_count++;
}

$s_first_male_date = date('dmy', $l_first_due_date_delete);
$s_last_duration_date = date('dmy', $l_last_due_date_delete);
//End delete request records
$s_buffer .= 'NY213688'.sprintf("%08d", $l_delete_trans_count).sprintf("%08d", $l_delete_record_count).sprintf("%017d", $l_delete_total).$s_first_male_date.$s_last_duration_date.sprintf("%027d", 0).PHP_EOL;


//Compare due date
if($l_first_due_date > $l_first_due_date_delete) $l_first_due_date = $l_first_due_date_delete;



//End shipping
$s_buffer .= 'NY000089'.sprintf("%08d", $l_payment_trans_count+$l_delete_trans_count).sprintf("%08d", $l_payment_record_count+$l_delete_record_count+2).sprintf("%17d", $l_payment_total+$l_delete_total).date('dmy', $l_first_due_date).sprintf("%033d", 0);


// Headers
header('Content-type: text/plain' );
header('Content-Disposition: attachment;filename="export_'.$l_avtalegiro_export_file_id.'.txt"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

// Output
if(!is_dir(__DIR__."/../../../../../../uploads/protected/avtalegiro_files"))
{
	mkdir(__DIR__."/../../../../../../uploads/protected/avtalegiro_files");
}
$s_file_name = "uploads/protected/avtalegiro_files/export_".$l_avtalegiro_export_file_id.".txt";
file_put_contents(__DIR__."/../../../../../../".$s_file_name, $s_buffer);

$o_main->db->query("UPDATE avtalegiro_export_files SET file_path = ".$o_main->db->escape($s_file_name)." WHERE id = ".$o_main->db->escape($l_avtalegiro_export_file_id)."");

ob_end_clean();
echo $s_buffer;
?>
