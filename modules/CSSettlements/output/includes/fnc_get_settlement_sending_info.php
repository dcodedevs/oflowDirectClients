<?php 
if(!function_exists("get_settlement_sending_info")){

    function get_settlement_sending_info($o_main, $settlementId, $creditorId){
        $s_sql = "SELECT cmv.*, cmv.case_id, CONCAT_WS(' ',deb.name, deb.middlename, deb.lastname) as debitorName FROM cs_mainbook_voucher cmv
        JOIN collecting_company_cases cc ON cc.id = cmv.case_id
        JOIN customer deb ON deb.id = cc.debitor_id
        WHERE IFNULL(cmv.settlement_id, 0) = ? AND cc.creditor_id = ?";
        $o_query = $o_main->db->query($s_sql, array($settlementId, $creditorId));
        $payments = $o_query ? $o_query->result_array() : array();

        $total_bank_amount = 0;
        $total_vat_amount = 0;
        $invoices = array();
        foreach($payments as $payment) {
            $s_sql = "SELECT * FROM collecting_company_cases WHERE id = ?";
            $o_query = $o_main->db->query($s_sql, array($payment['case_id']));
            $collecting_case = $o_query ? $o_query->row_array() : array();

            $s_sql = "SELECT * FROM collecting_company_cases_claim_lines 
            WHERE collecting_company_case_id = ? AND (claim_type = 1 OR claim_type = 16 OR 
            (claim_type = 15 AND IFNULL(payment_after_closed, 0) = 0))";
            $o_query = $o_main->db->query($s_sql, array($payment['case_id']));
            $claimlines = $o_query ? $o_query->result_array() : array();
            $sumLeftToBePaid = 0;
            $main_claimlines = array();
            $payment_amount = 0;
            foreach($claimlines as $claimline) {
                $sumLeftToBePaid += $claimline['amount'];
                if($claimline['claim_type'] == 1){
                    $main_claimlines[] = $claimline;
                } else {
                    $payment_amount+=$claimline['amount'];
                }
            }

            //bank bookaccount
            $s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = ? AND bookaccount_id = 16";
            $o_query = $o_main->db->query($s_sql, array($payment['id']));
            $transactions_to_bank = $o_query ? $o_query->result_array() : array();
            foreach($transactions_to_bank as $transaction) {
                $total_bank_amount += $transaction['amount']*(-1);
            }
            
            //vat bookaccount
            $s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = ? AND bookaccount_id = 27";
            $o_query = $o_main->db->query($s_sql, array($payment['id']));
            $transactions_vat = $o_query ? $o_query->result_array() : array();
            foreach($transactions_vat as $transaction) {
                $total_vat_amount += $transaction['amount'];
            }

            //total payments
            $s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = ? AND bookaccount_id = 20";
            $o_query = $o_main->db->query($s_sql, array($payment['id']));
            $transactions_to_bank = $o_query ? $o_query->result_array() : array();
            $total_payment = 0;
            foreach($transactions_to_bank as $transaction) {
                $total_payment += $transaction['amount']*(-1); 
            }
            //invoices bookaccount
            foreach($main_claimlines as $main_claimline){
                if($total_payment >0){
                    $total_payment -= $main_claimline['amount'];
                    if($total_payment >= 0) {
                        $amountLeft = $main_claimline['amount'];
                        $s_sql = "SELECT * FROM creditor_transactions WHERE creditor_id = ? AND invoice_nr = ? AND system_type = 'InvoiceCustomer'";
                        $o_query = $o_main->db->query($s_sql, array($creditorId, $main_claimline['invoice_nr']));
                        $invoice_transaction = $o_query ? $o_query->row_array() : array();
                        if($invoice_transaction){
                            $invoice_data = array("claimline_id"=>$main_claimline['id'],"customerId"=>$invoice_transaction['external_customer_id'],"invoice_nr"=>$main_claimline['invoice_nr'], "amount"=>$amountLeft*-1, "transaction_guid"=>$invoice_transaction['transaction_id'], "open"=>$invoice_transaction['open']);
                            $invoices[] = $invoice_data;
                        }
                    } else {
                        $amountLeft = $main_claimline['amount']+$total_payment;
                        $s_sql = "SELECT * FROM creditor_transactions WHERE creditor_id = ? AND invoice_nr = ? AND system_type = 'InvoiceCustomer'";
                        $o_query = $o_main->db->query($s_sql, array($creditorId, $main_claimline['invoice_nr']));
                        $invoice_transaction = $o_query ? $o_query->row_array() : array();
                        if($invoice_transaction){
                            $invoice_data = array("claimline_id"=>$main_claimline['id'],"customerId"=>$invoice_transaction['external_customer_id'],"invoice_nr"=>$main_claimline['invoice_nr'], "amount"=>$amountLeft*-1, "transaction_guid"=>$invoice_transaction['transaction_id'], "open"=>$invoice_transaction['open']);
                            $invoices[] = $invoice_data;
                        }
                    }
                }
            }
        }
        $totalInvoices = 0;
        foreach($invoices as $invoice){
            $totalInvoices+=$invoice['amount'];
        }
        $correctNumbers = false;
        if(round($totalInvoices + $total_bank_amount + $total_vat_amount, 2) == 0){
            $correctNumbers = true;
        }
            
        if($_SERVER['REMOTE_ADDR'] == "83.99.234.99"){
            // var_dump($totalInvoices, $total_bank_amount, $total_vat_amount);
        }
        if($correctNumbers){
            $result['total_bank_amount'] = $total_bank_amount;
            $result['total_vat_amount'] = $total_vat_amount;
            $result['invoices'] = $invoices;
        } else {
            $result['error'] = "Error with data";
            $result['total_bank_amount'] = $total_bank_amount;
            $result['total_vat_amount'] = $total_vat_amount;
            $result['total_invoices_amount'] = $totalInvoices;
        }

        return $result;
    }
}
?>