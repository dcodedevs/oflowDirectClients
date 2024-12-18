<?php
$run_hook = function($data) {
    global $o_main;
    global $variables;

    // Params
    $invoice_id = $data['invoice_id'];

    $o_query = $o_main->db->query('SELECT * FROM invoice WHERE id = ?', array($invoice_id));
    $invoice_data = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();

    // Return object
    $return = array();

    if($invoice_data['sync_status'] == 2 && $invoice_data['external_invoice_nr'] != 0){
       $return['invoiceNumber'] = $invoice_data['external_invoice_nr'];
    } else {
        // Mark invoice sync_status as started
        $o_main->db->where('id', $invoice_id);
        $o_main->db->update('invoice', array('sync_status' => 1));

        // Get invoice data
        $o_query = $o_main->db->query('SELECT * FROM invoice WHERE id = ?', array($invoice_id));
        $invoice_data = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();
        // Load integration
        require_once __DIR__ . '/../internal_api/load.php';
        $api = new Integration24SevenOffice(array(
            'o_main' => $o_main,
            'ownercompany_id' => $invoice_data['ownercompany_id']
        ));


        // check if customer exists
        $sql = "SELECT c.*,
        cei.external_sys_id external_sys_id,
        cei.external_id external_id
        FROM customer c
        LEFT JOIN customer_externalsystem_id cei ON cei.customer_id = c.id AND cei.ownercompany_id = ?
        WHERE c.id = ?";
        $o_query = $o_main->db->query($sql, array($invoice_data['ownercompany_id'], $invoice_data['customerId']));
        $customer_data = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();
    	if($customer_data['external_id'] > 0){
    		$search_data = array();
    		$search_data['customerIds'] = array($customer_data['external_id']);
    		$customer_info = $api->get_customer_list($search_data);
    		if($customer_info){
    			//customer with that external id not found in system
    			if(isset($customer_info['GetCompaniesResult']) && count($customer_info['GetCompaniesResult']) == 0){
    				//empty external id
    				$sql = "UPDATE customer_externalsystem_id SET external_id = 0 WHERE customer_id = '".$o_main->db->escape_str($invoice_data['customerId'])."' AND ownercompany_id = '".$o_main->db->escape_str($invoice_data['ownercompany_id'])."'";
    				$o_query = $o_main->db->query($sql);
    			}
    		}
    	}

        // Get customer externalsystem id data
        $sql = "SELECT c.*,
        cei.external_sys_id external_sys_id,
        cei.external_id external_id
        FROM customer c
        LEFT JOIN customer_externalsystem_id cei ON cei.customer_id = c.id AND cei.ownercompany_id = ?
        WHERE c.id = ?";
        $o_query = $o_main->db->query($sql, array($invoice_data['ownercompany_id'], $invoice_data['customerId']));
        $customer_data = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();
        if($customer_data){
            // Process customer data
            $customer_data_processed = array();
            $customer_data_processed['name'] = trim($customer_data['name']. " ".$customer_data['middlename']. " ".$customer_data['lastname']);
            if($customer_data['external_id'] > 0) {
                $customer_data_processed['external_id'] = $customer_data['external_id'];
            }
            $customer_data_processed['id'] = $customer_data['id'];
            $customer_data_processed['ownercompany_id'] = $invoice_data['ownercompany_id'];

            if (strlen($customer_data['publicRegisterId']) === 9 && is_numeric($customer_data['publicRegisterId'])) {
                $customer_data_processed['vatNumber'] = $customer_data['publicRegisterId'];
            }
            //hardcoded to be overrite Confirmed by David
            $customer_data['paCountry'] = "NO";

            $customer_data_processed['invoiceEmail'] = $customer_data['invoiceEmail'];

            $customer_data_processed['mailAddress'] = array(
                'PostalArea' => $customer_data['paCity'],
                'PostalCode' => $customer_data['paPostalNumber'],
                'Street' => $customer_data['paStreet'],
                'Country' =>  $customer_data['paCountry']
            );
            $earlier = new DateTime(date("Y-m-d", strtotime($invoice_data['invoiceDate'])));
            $later = new DateTime(date("Y-m-d", strtotime($invoice_data['dueDate'])));
            $daysUntilDueDate = $later->diff($earlier)->format("%a");
            $customer_data_processed['daysUntilDueDate'] = $daysUntilDueDate;
            $customer_result = $api->add_customer($customer_data_processed);
            $return['customer_sync_result'] = $customer_result;
            if($customer_result['id'] > 0){
                // Get orderlines
                $sql = "SELECT o.*
                FROM orders o
                LEFT JOIN customer_collectingorder cco ON cco.id = o.collectingorderId
                WHERE cco.invoiceNumber = ?";
                $o_query = $o_main->db->query($sql, array($invoice_id));
                $order_lines = $o_query && $o_query->num_rows() ? $o_query->result_array() : array();

                $sql = "SELECT cco.*
                FROM customer_collectingorder cco
                WHERE cco.invoiceNumber = ?";
                $o_query = $o_main->db->query($sql, array($invoice_id));
                $collectingOrder = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();

                $order_lines_processed = array();

                foreach ($order_lines as $order) {

                    $sql = "SELECT * FROM article WHERE id = ?";
                    $o_query = $o_main->db->query($sql, array($order['articleNumber']));
                    $article = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();

                    array_push($order_lines_processed, array(
                        'description' => $order['articleName'],
                        'articleNumber' => $order['articleNumber'],
                        'external_product_id' => $article['external_sys_id'],
                        'accountNumber' => $order['bookaccountNr'],
                        'amount' => $order['pricePerPiece'],
                        'count' => $order['amount'],
                        'vatCode' => $order['vatCode'],
                        'external_sys_id' => $order['external_sys_id'],
                        'discount' => $order['discountPercent'] ? $order['discountPercent'] : 0
                    ));
                }
                $successfulOrTried = false;
                $triesLaunched = 0;
                do {
					$triesLaunched++;
                    $updatedCorrectly = true;
                    $external_order_id = $invoice_data['external_order_id'];
                    if(intval($external_order_id) == 0) {
                        $external_order_id = $collectingOrder['external_sys_id'];
                        if($external_order_id > 0) {
                            $updatedCorrectly = false;
                            $update_result = $api->update_order(array(
                                'date' => $invoice_data['invoiceDate'],
                                'dueDate' => $invoice_data['dueDate'],
                                'invoiceNo' => $invoice_data['id'],
                                'orderId' => $external_order_id,
                                'customerCode' => $customer_result['id'],
                                'lines' => $order_lines_processed,
                                'departmentCode' =>$collectingOrder['department_for_accounting_code'],
                                'projectCode' =>$collectingOrder['accountingProjectCode']
                            ));
                            if(!isset($update_result['SaveInvoicesResult']['InvoiceOrder']['APIException']) && isset($update_result['SaveInvoicesResult']['InvoiceOrder']['OrderId'])){
                                $updatedCorrectly = true;
                            }
                        }
                    }
                    if($updatedCorrectly){
	                    // Sync invoice
	                    $invoice_result = $api->add_invoice(array(
	                        'date' => $invoice_data['invoiceDate'],
	                        'dueDate' => $invoice_data['dueDate'],
	                        'invoiceNo' => $invoice_data['id'],
	                        'orderId' => $external_order_id,
	                        'customerCode' => $customer_result['id'],
	                        'lines' => $order_lines_processed,
	                        'departmentCode' =>$collectingOrder['department_for_accounting_code'],
	                        'projectCode' =>$collectingOrder['accountingProjectCode']
	                    ));

	                    if(!isset($invoice_result['SaveInvoicesResult']['InvoiceOrder']['APIException']) && isset($invoice_result['SaveInvoicesResult']['InvoiceOrder']['InvoiceId'])){
	                        $return['invoiceNumber'] = $invoice_result['SaveInvoicesResult']['InvoiceOrder']['InvoiceId'];

	                        $newOrderId = $invoice_result['SaveInvoicesResult']['InvoiceOrder']['OrderId'];
	                        $newInvoiceNrOnInvoice = $return['invoiceNumber'];

	                        $successfulOrTried = true;
	        				// Update kid number
	        				$o_main->db->query("UPDATE invoice SET external_invoice_nr = ?, external_order_id = ? WHERE id = ?", array($newInvoiceNrOnInvoice, $newOrderId, $invoice_id));

	                        $invoicesList = $api->get_invoice_list(array(
	                            'invoiceIds' => array($newInvoiceNrOnInvoice)
	                        ));

	                        if(isset($invoicesList['GetInvoicesResult'])){
	                            if(isset($invoicesList['GetInvoicesResult']['InvoiceOrder'])){
	                                if(isset($invoicesList['GetInvoicesResult']['InvoiceOrder']['InvoiceId'])){
	                                    $invoices = array($invoicesList['GetInvoicesResult']['InvoiceOrder']);
	                                } else {
	                                    $invoices = $invoicesList['GetInvoicesResult']['InvoiceOrder'];
	                                }
	                                foreach($invoices as $invoice) {
	                                    if(isset($invoice['OrderStatus'])){
	                                        if($invoice['OrderStatus'] == "Invoiced") {
	                                            $o_main->db->query("UPDATE invoice SET sync_confirmed_total_value_with_vat = ?, sync_confirmed_total_value_without_vat = ?, kidNumber = ? WHERE id = ?", array($invoice['OrderTotalIncVat'],$invoice['OrderTotalIncVat']-$invoice['OrderTotalVat'], $invoice['OCR'], $invoice_id));
	                                        }
	                                    }
	                                }
	                            }
	                        }
	                    }
					}
                    if($triesLaunched > 10){
                        $successfulOrTried = true;
                    }

                } while ($successfulOrTried == false);

                $return['sent_order_lines'] = $order_lines_processed;
                $return['invoice_sync_result'] = $invoice_result;


                if (!isset($invoice_result['SaveInvoicesResult']['InvoiceOrder']['APIException']) && isset($invoice_result['SaveInvoicesResult']['InvoiceOrder'])) {
                    // Mark invoice sync_status as success
                    // Save total values from tripletex
                    $o_main->db->where('id', $invoice_id);
                    $o_main->db->update('invoice', array(
                        'sync_status' => 2,
                        'sync_error_response' => json_encode($return),
                        // 'sync_confirmed_total_value_without_vat' => $invoice_result['invoice']['value']['amountExcludingVat'],
                        // 'sync_confirmed_total_value_with_vat' => $invoice_result['invoice']['value']['amount']
                    ));
                } else {
                    $o_main->db->where('id', $invoice_id);
                    $o_main->db->update('invoice', array(
                        'sync_error_response' => json_encode($return),
                    ));
                }
            }
        }
    }
    return $return;
}
?>
