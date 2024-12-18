<?php 
if(!function_exists("check_loss")){
    function check_loss($creditor, $caseData){
        global $o_main;
        $has_loss = false;
        if($creditor) {
            if($caseData) {
                require_once __DIR__ . '/../../../'.$creditor['integration_module'].'/internal_api/load.php';
                $api = new Integration24SevenOffice(array(
                    'ownercompany_id' => 1,
                    'identityId' => $creditor['entity_id'],
                    'creditorId' => $creditor['id'],
                    'o_main' => $o_main
                ));
                $s_sql = "SELECT * FROM creditor_transactions WHERE collecting_company_case_id = '".$o_main->db->escape_str($caseData['id'])."' AND creditor_id = '".$o_main->db->escape_str($caseData['creditor_id'])."' ORDER BY created DESC";
                $o_query = $o_main->db->query($s_sql);
                $connected_transactions = ($o_query ? $o_query->result_array() : array());

                foreach($connected_transactions as $connected_transaction) {
                    if($connected_transaction['link_id'] > 0) {
                        $s_sql = "SELECT * FROM creditor_transactions WHERE link_id = '".$o_main->db->escape_str($connected_transaction['link_id'])."' AND id <> '".$o_main->db->escape_str($connected_transaction['link_id'])."' AND creditor_id = '".$o_main->db->escape_str($caseData['creditor_id'])."' ORDER BY created DESC";
                        $o_query = $o_main->db->query($s_sql);
                        $linked_transactions = ($o_query ? $o_query->result_array() : array());
                        foreach($linked_transactions as $linked_transaction){
                            $transactionNr = $linked_transaction['transaction_nr'];
                            if($transactionNr > 0) {
                                // $data['changedAfter'] = date("Y-m-d", strtotime("01.02.2023"));
                                //
                                $transactionData = array();
                                $transactionData['DateSearchParameters'] = 'DateChangedUTC';
                                $transactionData['date_start'] = $data['changedAfter'];
                                $transactionData['date_end'] =date('Y-m-t', time() + 60*60*24);
                                // $transactionData['LinkId'] = 10272870;

                                // $transactionData['bookaccountStart'] = 7830;
                                // $transactionData['bookaccountEnd'] = 7830;
                                $transactionData['TransactionNoStart'] = $transactionNr;
                                $transactionData['TransactionNoEnd'] = $transactionNr;

                                $invoicesTransactions = $api->get_transactions($transactionData, true);
                                $realTransactions = $invoicesTransactions['Transaction'];
                                if($realTransactions[0]['Id'] == "") {
                                    $realTransactions = array($realTransactions);
                                }
                                if(count($realTransactions) > 0) {
                                    foreach($realTransactions as $transaction) {
                                        if($transaction['AccountNo'] == $creditor['loss_bookaccount']){
                                            $has_loss = true;
                                        }                                        
                                    }
                                }
                            }
                        }
                    } else {
                        $transactionNr = $connected_transaction['transaction_nr'];
                        if($transactionNr > 0) {
                            // $data['changedAfter'] = date("Y-m-d", strtotime("01.02.2023"));
                            //
                            $transactionData = array();
                            $transactionData['DateSearchParameters'] = 'DateChangedUTC';
                            $transactionData['date_start'] = $data['changedAfter'];
                            $transactionData['date_end'] =date('Y-m-t', time() + 60*60*24);
                            // $transactionData['LinkId'] = 10272870;

                            // $transactionData['bookaccountStart'] = 7830;
                            // $transactionData['bookaccountEnd'] = 7830;
                            $transactionData['TransactionNoStart'] = $transactionNr;
                            $transactionData['TransactionNoEnd'] = $transactionNr;

                            $invoicesTransactions = $api->get_transactions($transactionData, true);
                            $realTransactions = $invoicesTransactions['Transaction'];
                            if($realTransactions[0]['Id'] == "") {
                                $realTransactions = array($realTransactions);
                            }
                            if(count($realTransactions) > 0) {
                                foreach($realTransactions as $transaction) {
                                    if($transaction['AccountNo'] == $creditor['loss_bookaccount']){
                                        $has_loss = true;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $has_loss;
    }
}
?>