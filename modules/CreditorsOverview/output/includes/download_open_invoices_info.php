<?php 
ini_set('max_execution_time', 600);
session_start();
define('FRAMEWORK_DEBUG', FALSE);
define('ACCOUNT_PATH', realpath(__DIR__.'/../../../../')); // this is modified to fit this files location
define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
$v_tmp = explode("/",ACCOUNT_PATH);
$accountname = array_pop($v_tmp);
if(!$from_api){
	// Load database
	require_once __DIR__ . '/../../../../elementsGlobal/cMain.php';
	$v_path = explode("/", realpath(__DIR__."/../../"));
	$s_module = array_pop($v_path);

	$s_sql = "select * from session_framework where companyaccessID = ? and session = ? and username = ?".($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"");
	$o_query = $o_main->db->query($s_sql, array($_GET['caID'], $_COOKIE['sessionID'], $_COOKIE['username']));
	if($o_query && $o_query->num_rows()>0){
		$fw_session = $o_query->row_array();
	}
	$v_module_access = json_decode($fw_session['cache_menu'],true);
	$l_access = $v_module_access[$s_module][2];

	$cid = isset($_GET['cid']) ? $_GET['cid'] : '';
} else {
	ob_start();
}

if($l_access || $from_api){
	require_once dirname(__FILE__) . '/PHPExcel/PHPExcel.php';
	require_once(__DIR__.'/../languagesOutput/no.php');

    define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
    

    $column_labels = array(
        $formText_InvoiceNumber_output, 
        $formText_CustomerName_output, 
        $formText_OriginalAmount_output, 
        $formText_Interest_output, 
        $formText_Fees_output, 
        $formText_PayedAmount_output, 
        $formText_Balance_output, 
        $formText_Status_output, 
        $formText_CollectingCompanyCaseId_output

    );

    // Set active sheet index to the first sheet, so Excel opens this as the first sheet
    $objPHPExcel->setActiveSheetIndex(0);
    $row = 1;
    $column = 0;
    foreach($column_labels as $column_label) {
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, $row, $column_label);
        $column++;
    }

    $list = array();
    $s_sql = "SELECT ct.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName FROM creditor_transactions ct 
    JOIN customer c ON c.creditor_customer_id = ct.external_customer_id AND c.creditor_id = ct.creditor_id
    WHERE ct.collectingcase_id > 0 AND ct.open = 1 AND ct.creditor_id = ?";
	$o_query = $o_main->db->query($s_sql, array($cid));
    $open_invoices = $o_query ? $o_query->result_array() : array();
    
    $collectingcase_ids = array();
    $transaction_ids = array();
    $all_link_ids = array();

    foreach($open_invoices as $v_row) {
        if($v_row['collectingcase_id'] > 0){
            $collectingcase_ids[] = $v_row['collectingcase_id'];
        }
        $transaction_ids[] = $v_row['internalTransactionId'];

        if($v_row['link_id'] > 0 && $v_row['system_type'] == 'InvoiceCustomer' && $v_row['open']){
            if(!in_array($v_row['link_id'], $all_link_ids)){
                $all_link_ids[$v_row['link_id']] = $v_row['link_id'];
            }
        }
    }

    
    $s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND creditor_id = ? 
    AND (collectingcase_id is null OR collectingcase_id = 0) 
    AND (comment LIKE '%reminderFee_%' OR comment LIKE '%interest_%') AND link_id IN (".implode(',', $all_link_ids).")";
    $o_query = $o_main->db->query($s_sql, array($cid));
    $all_transaction_fees = ($o_query ? $o_query->result_array() : array());
    $all_transaction_fees_grouped = array();
    foreach($all_transaction_fees as $all_transaction_fee) {
        $all_transaction_fees_grouped[$all_transaction_fee['link_id']][]=$all_transaction_fee;
    }
    $s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') 
    AND creditor_id = ? AND link_id IN (".implode(',', $all_link_ids).")";
    $o_query = $o_main->db->query($s_sql, array($cid));
    $all_transaction_payments = ($o_query ? $o_query->result_array() : array());
    $all_transaction_payments_grouped = array();
    foreach($all_transaction_payments as $all_transaction_payment) {
        $all_transaction_payments_grouped[$all_transaction_payment['link_id']][]=$all_transaction_payment;
    }

    foreach($open_invoices as $open_invoice) {

        $row++;
        $column = 0;
        $interest = 0;
        $fees = 0;
        $payed = 0;        
        $balance = floatval($open_invoice['case_balance']);

        if($open_invoice['link_id'] > 0){
            $transaction_fees = $all_transaction_fees_grouped[$open_invoice['link_id']];
            foreach($transaction_fees as $transaction_fee) {
                if(strpos($transaction_fee['comment'], "reminderFee_")){
                    $fees += $transaction_fee['amount'];
                } else if(strpos($transaction_fee['comment'], "interest_")){
                    $interest += $transaction_fee['amount'];
                }
            }
            $transaction_payments = $all_transaction_payments_grouped[$open_invoice['link_id']];
            foreach($transaction_payments as $transaction_payment) {
                $payed+=$transaction_payment['amount'];
            }
        }
        $status = "";
        if($open_invoice['collecting_company_case_id'] > 0) {
            $s_sql = "SELECT * FROM collecting_company_cases WHERE id = ? ORDER BY created DESC";
            $o_query = $o_main->db->query($s_sql, array($open_invoice['collecting_company_case_id']));
            $company_case = ($o_query ? $o_query->row_array() : array());
            
            $status = date("d.m.Y", strtotime($company_case['created']))." ".$formText_TransferredToCollectingCompany_output;
        } else {
            $s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE case_id = ? ORDER BY created DESC";
            $o_query = $o_main->db->query($s_sql, array($open_invoice['collectingcase_id']));
            $last_letter = ($o_query ? $o_query->row_array() : array());
            if($last_letter) {
                $status = date("d.m.Y", strtotime($last_letter['created']))." ".$last_letter['step_name'];
            }
        }
        foreach($column_labels as $column_label) {
            $value = "";
            switch($column){
                case 0:
                    $value = $open_invoice['invoice_nr'];
                break;
                case 1:
                    $value = $open_invoice['customerName'];
                break;
                case 2:
                    $value = $open_invoice['collecting_case_original_claim'];
                break;
                case 3:
                    $value = $interest;
                break;
                case 4:
                    $value = $fees;
                break;
                case 5:
                    $value = $payed;
                break;
                case 6:
                    $value = $balance;
                break;
                case 7:
                    $value = $status;
                break;
                case 8:
                    $value = $open_invoice['collecting_company_case_id'];
                break;
            }
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, $row, $value);
            $column++;
        }
    }
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_start();
    $objWriter->save('php://output');
    $excel_string = ob_get_clean();
}
?>