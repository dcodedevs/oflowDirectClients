<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);

$sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($sql, array($creditorId));
$creditorData = $o_query ? $o_query->row_array() : array();
if($creditorData) {
    require_once __DIR__ . '/../../../../'.$creditorData['integration_module'].'/internal_api/load.php';

    $sql = "SELECT * FROM moduledata WHERE name = 'CreditorsOverview'";
    $o_query = $o_main->db->query($sql);
    $moduleInfo = $o_query ? $o_query->row_array() : array();
    $moduleID = $moduleInfo['uniqueID'];

    $invoices_created = array();
    $payments_created = array();
    if($creditorData['integration_module'] == "Integration24SevenOffice"){
        if($creditorData['entity_id'] == ""){
            echo $formText_NoEntityId_output;
        } else {

            $api = new Integration24SevenOffice(array(
                'ownercompany_id' => 1,
                'identityId' => $creditorData['entity_id'],
                'creditorId' => $creditorData['id'],
                'o_main' => $o_main
            ));
            if($api->error == "") {
                $customerIds = array();

                $changedAfterDate = isset($creditorData['last_create_case_date']) ? $creditorData['last_create_case_date'] : "";
                $data['changedAfter'] = date("Y-m-d", strtotime("01.01.2000"));
                if($creditorData['get_invoices_from_date'] != "0000-00-00" && $creditorData['get_invoices_from_date'] != "") {
                    $data['changedAfter'] = date("Y-m-d", strtotime($creditorData['get_invoices_from_date']));
                }
                if($changedAfterDate != "0000-00-00" && $changedAfterDate != null && $changedAfterDate != ""){
                    $data['changedAfter'] = date("Y-m-d", strtotime("-1 day", strtotime($changedAfterDate)));
                    $lastImportedDate = $data['changedAfter'];
                }

               $transactionData = array();
               $transactionData['SystemType'] = 'InvoiceCustomer';
               $transactionData['ShowOpenEntries'] = 1;
               $transactionData['HasInvoiceId'] = 1;
               $transactionData['DateSearchParameters'] = 'DateChangedUTC';
               $transactionData['date_start'] = $data['changedAfter'];
               $transactionData['date_end'] =date('Y-m-d', time() + 60*60*24);
               $invoicesTransactions = $api->get_transactions($transactionData);
               $invoiceIds = array();
               foreach($invoicesTransactions as $invoicesTransaction) {
                   if(!$invoicesTransaction['hidden']){
                       $invoiceIds[] = $invoicesTransaction['invoiceNr'];
                   }
               }
               $invoices = array();
               $bigInvoiceIdArray = array_chunk($invoiceIds,1000);
               foreach($bigInvoiceIdArray as $similarInvoiceIds) {
                   $dataInvoice['invoiceIds'] = $similarInvoiceIds;
                   $invoicesList = $api->get_invoice_list($dataInvoice);
                   if(isset($invoicesList['GetInvoicesResult'])){
                       if(isset($invoicesList['GetInvoicesResult']['InvoiceOrder'])){
                           $invoicesFromApi = $invoicesList['GetInvoicesResult']['InvoiceOrder'];
                           if(isset($invoices['InvoiceId'])){
                               $invoicesFromApi = array($invoices);
                           }
                           $invoices = array_merge($invoices, $invoicesFromApi);
                       }
                   }
               }
               foreach($invoices as $invoice) {
                   if(isset($invoice['OrderStatus'])){
                       if($invoice['OrderStatus'] == "Invoiced") {
                           if(isset($invoice['CustomerId']) && isset($invoice['InvoiceId'])) {
                               $customerId = $invoice['CustomerId'];
                               if(!in_array($customerId, $customerIds)) {
                                   array_push($customerIds, $customerId);
                               }
                           }
                       }
                   }
               }

               $casesAdded = 0;
               // $lastImportedInvoiceNumber = $creditorData['lastImportedInvoiceNumber'];
               $openInvoices = array();
               foreach($invoices as $invoice) {
                   $external_invoice_id = $invoice['InvoiceId'];
                   $external_customer_id = $invoice['CustomerId'];
                   $date = $invoice['DateInvoiced'];
                   $amount = floatval($invoice['OrderTotalIncVat']);
                   if($invoice['PaymentTime'] > 0){
                       $originalDueDate = date("Y-m-d H:i:s", strtotime("+".$invoice['PaymentTime']." days", strtotime($date)));
                   }

                   if($external_invoice_id > 0) {
                       $sql = "SELECT creditor_invoice.* FROM creditor_invoice
                       WHERE creditor_invoice.invoice_number = ? AND creditor_invoice.creditor_id = ?";
                       $o_query = $o_main->db->query($sql, array($external_invoice_id, $creditorData['id']));
                       $caseExist = $o_query ? $o_query->row_array() : array();
                       if(!$caseExist) {
                           $sql = "SELECT customer.* FROM customer
                           WHERE customer.creditor_customer_id = ? AND customer.creditor_id = ?";
                           $o_query = $o_main->db->query($sql, array($external_customer_id, $creditorData['id']));
                           $debitorData = $o_query ? $o_query->row_array() : array();
                           if($debitorData) {
                               $filename = "uploads/protected/invoice_".$creditorData['id']."_".$external_invoice_id.".pdf";
                               $data = array("invoice_id"=>$external_invoice_id);
                               $fileText = $api->get_invoice_pdf($data);
                               // var_dump($fileText);
                               file_put_contents(__DIR__."/../../../../../".$filename, $fileText);

                               $sql = "INSERT INTO creditor_invoice SET moduleID = ?, creditor_id = ?, debitor_id = ?, invoice_number = ?, amount = ?, date=?, due_date=?, createdBy = 'import', created=NOW(), invoiceFile = ?, closed = NOW()";
                               $o_query = $o_main->db->query($sql, array($moduleID, $creditorData['id'], $debitorData['id'],  $external_invoice_id, $amount,$date,$originalDueDate, $filename));
                               if($o_query) {
                                   $invoices_created[] = $o_main->db->insert_id();
                                   $casesAdded++;
                                   // if($external_invoice_id > $lastImportedInvoiceNumber) {
                                   //     $lastImportedInvoiceNumber = $external_invoice_id;
                                   // }
                               }
                           }
                       }
                   }
               }
               $bigCustomerIdArray = array_chunk($customerIds,1000);
               foreach($bigCustomerIdArray as $customerIds) {
                   $customer_id = "";
                   $dataCustomer['customerIds'] = $customerIds;
                   $response_customer = $api->get_customer_list($dataCustomer);
                   $customer_list = $response_customer['GetCompaniesResult']['Company'];
                   foreach($customer_list as $customer) {
                       $data['customerIds'][] = $customer['Id'];
                       $regNr = $customer['OrganizationNumber'];
                       $external_id = $customer['Id'];
                       $name = $customer['Name'];
                       $postAddresses = $customer['Addresses']['Post'];
                       $visitAddresses = $customer['Addresses']['Visit'];
                       $phone = $customer['PhoneNumbers']['Work']['Value'];
                       $fax = $customer['PhoneNumbers']['Fax']['Value'];
                       $email = $customer['EmailAddresses']['Invoice']['Value'];

                       $sql = "SELECT * FROM customer WHERE customer.creditor_customer_id = ? AND customer.creditor_id = ?";
                       $o_query = $o_main->db->query($sql, array($external_id, $creditorData['id']));
                       $customerExist = $o_query ? $o_query->row_array() : array();
                       if(!$customerExist){
                           $sql = "INSERT INTO customer SET createdBy = 'import', created=NOW(), creditor_id = ?, creditor_customer_id = ?, name = ?, phone = ?, fax = ?, invoiceEmail = ?, publicRegisterId = ?, paStreet = ?,  paPostalNumber = ?, paCity = ?, paCountry = ?, vaStreet = ?,  vaPostalNumber = ?, vaCity = ?, vaCountry = ?";
                           $o_query = $o_main->db->query($sql, array($creditorData['id'], $external_id, $name, $phone, $fax, $email, $regNr, $postAddresses['Street'], $postAddresses['PostalCode'],$postAddresses['PostalArea'],$postAddresses['Country'],
                           $visitAddresses['Street'], $visitAddresses['PostalCode'],$visitAddresses['PostalArea'],$visitAddresses['Country']));
                           if($o_query) {
                               $customer_id = $o_main->db->insert_id();
                           }
                       } else {
                           $sql = "UPDATE customer SET updatedBy = 'import', updated=NOW(), name = ?, phone = ?, fax = ?, invoiceEmail = ?, publicRegisterId = ?, paStreet = ?,  paPostalNumber = ?, paCity = ?, paCountry = ?, vaStreet = ?,  vaPostalNumber = ?, vaCity = ?, vaCountry = ? WHERE id = ?";
                           $o_query = $o_main->db->query($sql, array($name, $phone, $fax, $email, $regNr, $postAddresses['Street'], $postAddresses['PostalCode'],$postAddresses['PostalArea'],$postAddresses['Country'],
                           $visitAddresses['Street'], $visitAddresses['PostalCode'],$visitAddresses['PostalArea'],$visitAddresses['Country'], $customerExist['id']));
                           if($o_query) {
                               $customer_id = $customerExist['id'];
                           }
                       }
                   }
               }
               foreach($invoicesTransactions as $invoicesTransaction) {
                   if(!$invoicesTransaction['hidden']){
                       $invoice_nr = $invoicesTransaction['invoiceNr'];
                       $linkId = $invoicesTransaction['linkId'];
                       $transactionId = $invoicesTransaction['transactionNr'];
                       $sql = "SELECT creditor_invoice.* FROM creditor_invoice
                        WHERE creditor_invoice.invoice_number = ? AND creditor_id = ?";
                        $o_query = $o_main->db->query($sql, array($invoice_nr, $creditorData['id']));
                        $case = $o_query ? $o_query->row_array() : array();

                       if($case) {
                           $sql = "UPDATE creditor_invoice SET closed = '', link_id = ?, kid_number = ?  WHERE id = ?";
                           $o_query = $o_main->db->query($sql, array($linkId, $invoicesTransaction['kidNumber'], $case['id']));
                       }
                   }
               }


                $paymentsAdded = 0;

               // $transactionData = array();
               // $transactionData['date_start'] = date('Y-m-d', time() - 60*60*24*365*2);
               // $transactionData['date_end'] =date('Y-m-d', time() + 60*60*24);
               // $transactionData['LinkId'] = '5595551';
               // $transactions = $api->get_transactions($transactionData);
               // var_dump($transactions);
                $o_main->db->query("UPDATE creditor_invoice_payment SET open = 0, updated=NOW() WHERE creditor_id = ?", $creditorData['id']);

                $transactionData = array();
                $transactionData['SystemType'] = 'Payment';
                $transactionData['date_start'] = date('Y-m-d', time() - 60*60*24*365*3);
                $transactionData['date_end'] =date('Y-m-d', time() + 60*60*24);
                $transactionData['ShowOpenEntries'] = 1;
                $transactions1 = $api->get_transactions($transactionData);

                $transactionData = array();
                $transactionData['SystemType'] = 'Disbursment';
                $transactionData['date_start'] = date('Y-m-d', time() - 60*60*24*365*3);
                $transactionData['date_end'] =date('Y-m-d', time() + 60*60*24);
                $transactionData['ShowOpenEntries'] = 1;
                $transactions2 = $api->get_transactions($transactionData);

                $transactions = array_merge($transactions1, $transactions2);
                // var_dump($transactions);
                foreach($transactions as $transaction) {
                    $external_transaction_id = $transaction['transactionNr'];

                    $sql = "SELECT creditor_invoice.* FROM creditor_invoice
                        WHERE creditor_invoice.link_id = ? AND creditor_id = ?";
                    $o_query = $o_main->db->query($sql, array($transaction['linkId'], $creditorData['id']));
                    $case = $o_query ? $o_query->row_array() : array();
                    if(!$case){
                        $sql = "SELECT creditor_invoice.* FROM creditor_invoice
                            WHERE creditor_invoice.invoice_number = ? AND creditor_id = ?";
                        $o_query = $o_main->db->query($sql, array($transaction['invoiceNr'], $creditorData['id']));
                        $case = $o_query ? $o_query->row_array() : array();
                    }
                    $customerItem = array();
                    foreach($transaction['dimensions'] as $dimension) {
                        if($dimension['Type'] == 'Customer'){
                            $customerItem = $dimension;
                            break;
                        }
                    }
                   if($case) {
                       $external_invoice_id = $case['invoice_number'];
                       $external_transaction_id = $transaction['transactionNr'];

                       $sql = "SELECT creditor_invoice_payment.* FROM creditor_invoice_payment
                       WHERE creditor_invoice_payment.external_transaction_id = ? AND creditor_invoice_payment.invoice_number = ? AND creditor_invoice_payment.creditor_id = ?";
                       $o_query = $o_main->db->query($sql, array($external_transaction_id, $external_invoice_id, $case['creditor_id']));
                       $transactionExists = $o_query ? $o_query->row_array() : array();

                       if(!$transactionExists){
                           $before_or_after = 0;
                           if(intval($case['collecting_case_id']) > 0) {
                               $before_or_after = 1;
                           }
                           $sql = "INSERT INTO creditor_invoice_payment SET createdBy = 'import', created=NOW(), invoice_number = ?, date = ?, amount = ?, external_transaction_id = ?, creditor_id = ?, before_or_after_case =?, creditnote = 0, external_customer_id = ?";
                           $o_query = $o_main->db->query($sql, array($external_invoice_id, date("Y-m-d H:i:s", strtotime($transaction['date'])), floatval($transaction['amount']), $external_transaction_id, $case['creditor_id'], $before_or_after, $customerItem['Value']));

                           if($o_query) {
                               $payments_created[]=$o_main->db->insert_id();
                               $paymentsAdded++;
                           }
                       } else {
                           $o_main->db->query("UPDATE creditor_invoice_payment SET open = 1, updated=NOW() WHERE id = ?", array($transactionExists['id']));
                       }
                   } else {
                       if($external_transaction_id != ""){
                           // var_dump($transaction);
                           $sql = "SELECT creditor_invoice_payment.* FROM creditor_invoice_payment
                           WHERE creditor_invoice_payment.external_transaction_id = ? AND creditor_invoice_payment.creditor_id = ?";
                           $o_query = $o_main->db->query($sql, array($external_transaction_id, $creditorData['id']));
                           $transactionExists = $o_query ? $o_query->row_array() : array();
                           if(!$transactionExists){
                               $before_or_after = 0;
                               $sql = "INSERT INTO creditor_invoice_payment SET createdBy = 'import', created=NOW(),date = ?, amount = ?, external_transaction_id = ?, creditor_id = ?, before_or_after_case =?, creditnote = 0, external_customer_id = ?";
                               $o_query = $o_main->db->query($sql, array(date("Y-m-d H:i:s", strtotime($transaction['date'])), floatval($transaction['amount']), $external_transaction_id, $creditorData['id'], $before_or_after, $customerItem['Value']));

                               if($o_query) {
                                   $payments_created[]=$o_main->db->insert_id();
                                   $paymentsAdded++;
                               }
                           } else {
                               $o_main->db->query("UPDATE creditor_invoice_payment SET open = 1, updated=NOW() WHERE id = ?", array($transactionExists['id']));
                           }
                       }
                   }
                }

                 $transactionData = array();
                 $transactionData['SystemType'] = 'CreditnoteCustomer';
                 $transactionData['date_start'] = date('Y-m-d', time() - 60*60*24*365*2);
                 $transactionData['date_end'] =date('Y-m-d', time() + 60*60*24);
                 $transactionData['ShowOpenEntries'] = 1;
                 $transactions = $api->get_transactions($transactionData);
                 // var_dump($transactions);
                 foreach($transactions as $transaction) {
                     $external_transaction_id = $transaction['transactionNr'];

                     $sql = "SELECT creditor_invoice.* FROM creditor_invoice
                         WHERE creditor_invoice.link_id = ? AND creditor_id = ?";
                     $o_query = $o_main->db->query($sql, array($transaction['linkId'], $creditorData['id']));
                     $case = $o_query ? $o_query->row_array() : array();
                     if(!$case){
                         $sql = "SELECT creditor_invoice.* FROM creditor_invoice
                             WHERE creditor_invoice.invoice_number = ? AND creditor_id = ?";
                         $o_query = $o_main->db->query($sql, array($transaction['invoiceNr'], $creditorData['id']));
                         $case = $o_query ? $o_query->row_array() : array();
                     }

                     $customerItem = array();
                     foreach($transaction['dimensions'] as $dimension) {
                         if($dimension['Type'] == 'Customer'){
                             $customerItem = $dimension;
                             break;
                         }
                     }
                    if($case) {
                        $external_invoice_id = $case['invoice_number'];
                        $external_transaction_id = $transaction['transactionNr'];

                        $sql = "SELECT creditor_invoice_payment.* FROM creditor_invoice_payment
                        WHERE creditor_invoice_payment.external_transaction_id = ? AND creditor_invoice_payment.invoice_number = ? AND creditor_invoice_payment.creditor_id = ?";
                        $o_query = $o_main->db->query($sql, array($external_transaction_id, $external_invoice_id, $case['creditor_id']));
                        $transactionExists = $o_query ? $o_query->row_array() : array();

                        if(!$transactionExists){
                            $before_or_after = 0;
                            if(intval($case['collecting_case_id']) > 0){
                                $before_or_after = 1;
                            }

                            $sql = "INSERT INTO creditor_invoice_payment SET createdBy = 'import', created=NOW(), invoice_number = ?, date = ?, amount = ?, external_transaction_id = ?, creditor_id = ?, before_or_after_case =?, creditnote = 1, external_customer_id = ?";
                            $o_query = $o_main->db->query($sql, array($external_invoice_id, date("Y-m-d H:i:s", strtotime($transaction['date'])), floatval($transaction['amount']), $external_transaction_id, $case['creditor_id'], $before_or_after, $customerItem['Value']));

                            if($o_query) {
                                $payments_created[]=$o_main->db->insert_id();
                                $paymentsAdded++;
                            }
                        } else {
                            $o_main->db->query("UPDATE creditor_invoice_payment SET open = 1, updated=NOW() WHERE id = ?", array($transactionExists['id']));
                        }
                    } else {
                        if($external_transaction_id != ""){
                            $sql = "SELECT creditor_invoice_payment.* FROM creditor_invoice_payment
                            WHERE creditor_invoice_payment.external_transaction_id = ? AND creditor_invoice_payment.creditor_id = ?";
                            $o_query = $o_main->db->query($sql, array($external_transaction_id, $creditorData['id']));
                            $transactionExists = $o_query ? $o_query->row_array() : array();
                            if(!$transactionExists){
                                $before_or_after = 0;
                                $sql = "INSERT INTO creditor_invoice_payment SET createdBy = 'import', created=NOW(),date = ?, amount = ?, external_transaction_id = ?, creditor_id = ?, before_or_after_case =?, creditnote = 1, external_customer_id = ?";
                                $o_query = $o_main->db->query($sql, array(date("Y-m-d H:i:s", strtotime($transaction['date'])), floatval($transaction['amount']), $external_transaction_id, $creditorData['id'], $before_or_after, $customerItem['Value']));

                                if($o_query) {
                                    $payments_created[]=$o_main->db->insert_id();
                                    $paymentsAdded++;
                                }
                            } else {
                                $o_main->db->query("UPDATE creditor_invoice_payment SET open = 1, updated=NOW() WHERE id = ?", array($transactionExists['id']));
                            }
                        }
                    }
                 }


                $lastImportedDate = date("Y-m-d H:i:s");
                $sql = "UPDATE creditor SET lastImportedDate = ? WHERE id = ?";
                $o_query = $o_main->db->query($sql, array($lastImportedDate, $creditorData['id']));
            } else {
                echo $formText_ErrorConnectingToIntegration_output."<br/>";
            }
        }
    } else if($creditorData['integration_module'] == "IntegrationTripletex") {
        $api = new IntegrationTripletex(array(
            'ownercompany_id' => 1,
            'creditorId' => $creditorData['id'],
            'o_main' => $o_main
        ));

        $changedAfterDate = isset($creditorData['last_create_case_date']) ?$creditorData['last_create_case_date'] : "";
        $data['invoiceDateFrom'] = date("Y-m-d", strtotime("-5 years"));

        if($changedAfterDate != "0000-00-00" && $changedAfterDate != null && $changedAfterDate != ""){
            $data['invoiceDateFrom'] = date("Y-m-d", strtotime("-700 days", strtotime($changedAfterDate)));
            $lastImportedDate = $data['invoiceDateFrom'];
        }
        $data['invoiceDateTo'] = date("Y-m-d", strtotime("+1 day"));

        $data['from'] = 0;
        $data['count'] = 3000;

        $invoices = array();
        $invoicesList = $api->get_invoice_list($data);
        $customerIds = array();
        if(isset($invoicesList['values'])){
            $invoices = $invoicesList['values'];
            $all_invoices = array();
            foreach($invoices as $invoice) {
                $customer = $invoice['customer'];
                $invoiceId = $invoice['invoiceNumber'];
                $customerId = $customer['id'];
                if(!in_array($customerId, $customerIds)) {
                    array_push($customerIds, $customerId);
                }
                $all_invoices[$invoiceId] = $invoice;
            }
            do {
                $data['from'] += $data['count'];
                $invoices = array();
                $invoicesList = $api->get_invoice_list($data);
                if(isset($invoicesList['values'])){
                    $invoices = $invoicesList['values'];
                    foreach($invoices as $invoice) {
                        $customer = $invoice['customer'];
                        $invoiceId = $invoice['invoiceNumber'];
                        $customerId = $customer['id'];
                        if(!in_array($customerId, $customerIds)) {
                            array_push($customerIds, $customerId);
                        }
                        $all_invoices[$invoiceId] = $invoice;
                    }
                }
            } while(count($invoices) == $data['count']);


            $customer_data = array();
            $customer_data['id'] = implode(",",$customerIds);
            $customer_list = $api->get_customer_list($customer_data);
            $addresses = $api->get_address_list();
            $countries = $api->get_country_list();
            foreach($customer_list as $customer) {
                $data['customerIds'][] = $customer['Id'];
                $regNr = $customer['organizationNumber'];
                $external_id = $customer['id'];
                $name = $customer['name'];
                // $postAddresses = $customer['Addresses']['Post'];
                // $visitAddresses = $customer['Addresses']['Visit'];
                $postAddress = $addresses[$customer['postalAddress']['id']];
                $visitAddress = $addresses[$customer['physicalAddress']['id']];
                $postCountryCode = $countries[$postAddress['country']['id']]['isoAlpha2Code'];
                $visitCountryCode = $countries[$visitAddress['country']['id']]['isoAlpha2Code'];
                $phone = $customer['phoneNumberMobile'];
                $fax = '';
                $email = $customer['overdueNoticeEmail'];
                if($email == ""){
                    $email = $customer['invoiceEmail'];
                }

                $sql = "SELECT * FROM customer WHERE customer.creditor_customer_id = ? AND customer.creditor_id = ?";
                $o_query = $o_main->db->query($sql, array($external_id, $creditorData['id']));
                $customerExist = $o_query ? $o_query->row_array() : array();
                if(!$customerExist){
                    $sql = "INSERT INTO customer SET createdBy = 'import', created=NOW(), creditor_id = ?, creditor_customer_id = ?, name = ?, phone = ?, fax = ?, invoiceEmail = ?, publicRegisterId = ?, paStreet = ?,  paPostalNumber = ?, paCity = ?, paCountry = ?, vaStreet = ?,  vaPostalNumber = ?, vaCity = ?, vaCountry = ?";
                    $o_query = $o_main->db->query($sql, array($creditorData['id'], $external_id, $name, $phone, $fax, $email, $regNr, $postAddress['addressLine1'], $postAddress['postalCode'],$postAddress['city'],$postCountryCode,
                    $visitAddress['addressLine1'], $visitAddress['postalCode'],$visitAddress['city'],$visitCountryCode));
                    if($o_query) {
                        $customer_id = $o_main->db->insert_id();
                    }
                } else {
                    $sql = "UPDATE customer SET createdBy = 'import', created=NOW(), creditor_id = ?, creditor_customer_id = ?, name = ?, phone = ?, fax = ?, invoiceEmail = ?, publicRegisterId = ?, paStreet = ?,  paPostalNumber = ?, paCity = ?, paCountry = ?, vaStreet = ?,  vaPostalNumber = ?, vaCity = ?, vaCountry = ? WHERE id = ?";
                    $o_query = $o_main->db->query($sql, array($creditorData['id'], $external_id, $name, $phone, $fax, $email, $regNr, $postAddress['addressLine1'], $postAddress['postalCode'],$postAddress['city'],$postCountryCode,
                    $visitAddress['addressLine1'], $visitAddress['postalCode'],$visitAddress['city'],$visitCountryCode, $customerExist['id']));
                    if($o_query) {
                        $customer_id = $customerExist['id'];
                    }
                }
            }

            $casesAdded = 0;
            $casesUpdated = 0;

            // $lastImportedInvoiceNumber = $creditorData['lastImportedInvoiceNumber'];
            $openInvoices = array();
            // $invoiceId = '505683964';
            $open_postings = $api->get_open_posting_list();
            if(count($open_postings) > 0 && count($all_invoices) > 0){
                $sql = "UPDATE creditor_invoice SET closed = NOW(), updated=NOW(), updatedBy='import' WHERE closed is null";
                $o_query = $o_main->db->query($sql);
            }
            $counter = 0;
            foreach($open_postings as $open_posting) {
                $invoice_number = $open_posting['invoiceNumber'];
                $invoice = $all_invoices[$invoice_number];
                if($invoice){
                    $external_invoice_id = $invoice['invoiceNumber'];
                    $external_customer_id = $invoice['customer']['id'];
                    $date = date("Y-m-d H:i:s", strtotime($invoice['invoiceDate']));
                    $amount = floatval($invoice['amount']);
                    $originalDueDate = date("Y-m-d H:i:s", strtotime($invoice['invoiceDueDate']));
                    $kidNumber = $invoice['kid'];
                    if($external_invoice_id > 0) {
                        $sql = "SELECT creditor_invoice.* FROM creditor_invoice
                        WHERE creditor_invoice.invoice_number = ? AND creditor_invoice.creditor_id = ?";
                        $o_query = $o_main->db->query($sql, array($external_invoice_id, $creditorData['id']));
                        $caseExist = $o_query ? $o_query->row_array() : array();
                        if(!$caseExist) {
                            $sql = "SELECT customer.* FROM customer
                            WHERE customer.creditor_customer_id = ? AND customer.creditor_id = ?";
                            $o_query = $o_main->db->query($sql, array($external_customer_id, $creditorData['id']));
                            $debitorData = $o_query ? $o_query->row_array() : array();
                            if($debitorData) {
                                $sql = "INSERT INTO creditor_invoice SET creditor_id = ?, debitor_id = ?, invoice_number = ?, amount = ?, date=?, due_date=?, createdBy = 'import', created=NOW(), kid_number = ?";
                                $o_query = $o_main->db->query($sql, array($creditorData['id'], $debitorData['id'],  $external_invoice_id, $amount,$date,$originalDueDate, $kidNumber));
                                if($o_query) {
                                    $invoices_created[] = $o_main->db->insert_id();
                                    $casesAdded++;
                                }
                            } else {
                                echo 'Missing customer'."<br/>";
                            }
                        } else {
                            $sql = "UPDATE creditor_invoice SET closed = null, updated=NOW(),updatedBy='import' WHERE id = ?";
                            $o_query = $o_main->db->query($sql, array($caseExist['id']));
                            if($o_query) {
                                $casesUpdated++;
                            }
                        }
                    }
                }
            }


            $data = array();
            $data['from'] = 0;
            $data['count'] = 3000;
            $transactions = $api->get_transactions($data);
            $customerIds = array();
            $all_transactions = array();
            foreach($transactions as $transaction) {
                $all_transactions[] = $transaction;
            }
            do {
                $data['from'] += $data['count'];
                $transactions = $api->get_transactions($data);
                foreach($transactions as $transaction) {
                    $all_transactions[] = $transaction;
                }
            } while(count($transactions) == $data['count']);

            foreach($all_transactions as $transaction) {
                $external_invoice_id = $transaction['invoiceNr'];
                $external_transaction_id = $transaction['transactionNr'];
                $external_customer_id = $transaction['customerId'];

                if($external_invoice_id > 0) {
                    if($transaction['amount'] < 0){
                        $sql = "SELECT creditor_invoice_payment.* FROM creditor_invoice_payment
                        WHERE creditor_invoice_payment.external_transaction_id = ? AND creditor_invoice_payment.creditor_id = ?";
                        $o_query = $o_main->db->query($sql, array($external_transaction_id, $creditorData['id']));
                        $transactionExists = $o_query ? $o_query->row_array() : array();
                        if(!$transactionExists){
                            $before_or_after = 0;
                            $sql = "INSERT INTO creditor_invoice_payment SET createdBy = 'import', created=NOW(),date = ?, amount = ?, external_transaction_id = ?, creditor_id = ?, before_or_after_case =?, creditnote = 1, external_customer_id = ?, invoice_number = ?";
                            $o_query = $o_main->db->query($sql, array(date("Y-m-d H:i:s", strtotime($transaction['date'])), floatval($transaction['amount']), $external_transaction_id, $creditorData['id'], $before_or_after, $external_customer_id, $external_invoice_id));

                            if($o_query) {
                                $payments_created[]=$o_main->db->insert_id();
                                $paymentsAdded++;
                            }
                        } else {
                            $o_main->db->query("UPDATE creditor_invoice_payment SET open = 1, updated=NOW() WHERE id = ?", array($transactionExists['id']));
                        }
                    }
                }
            }
            $lastImportedDate = date("Y-m-d H:i:s");
            $sql = "UPDATE creditor SET lastImportedDate = ? WHERE id = ?";
            $o_query = $o_main->db->query($sql, array($lastImportedDate, $creditorData['id']));

            // echo $casesAdded . " cases were created.</br>";
            // echo $casesUpdated . " cases were updated.</br>";
            ?>
            <?php
        } else {
            echo $formText_ErrorConnectingToIntegration_output."<br/>";
        }

    }

    //get all closed invoices
    $sql = "SELECT p.* FROM creditor_invoice p
        JOIN collecting_cases c ON c.id = p.collecting_case_id
        WHERE (c.status = 0 OR c.status = 1) AND (p.closed > '0000-00-00')";
    $o_query = $o_main->db->query($sql);
    $closed_cases = $o_query ? $o_query->result_array() : array();
    foreach($closed_cases as $case) {
        $sql = "UPDATE collecting_cases SET status = 2, sub_status = 1, updated = NOW(), stopped_date = NOW() WHERE id = ?";
        $o_query = $o_main->db->query($sql, array($case['collecting_case_id']));
    }

    echo count($invoices_created)." ".$formText_InvoicesWereCreated_output."<br/>";
    if(count($closed_cases) > 0){
        echo "<div class='closed_cases'>".count($closed_cases)." ".$formText_CasesWereClosed."</div>";
        ?>
        <table class="table">
            <tr>
                <th><?php echo $formText_CaseId_output;?></th>
                <th><?php echo $formText_InvoiceNumber_output;?></th>
                <th><?php echo $formText_Date_output;?></th>
                <th><?php echo $formText_DueDate_output;?></th>
                <th><?php echo $formText_Amount_output;?></th>
            </tr>
        <?php
        foreach($closed_cases as $closed_case) {
            ?>
            <tr>
                <td><?php echo $closed_case['collecting_case_id'];?></td>
                <td><?php echo $closed_case['invoice_number'];?></td>
                <td><?php echo date("d.m.Y", strtotime($closed_case['date']));?></td>
                <td><?php echo date("d.m.Y", strtotime($closed_case['due_date']));?></td>
                <td><?php echo $closed_case['amount'];?></td>
            </tr>
            <?php
        }
        ?>
        </table>
        <?php
    }
    if(count($payments_created) > 0){
        echo "<div class='payments_Created'>".count($payments_created)." ".$formText_PaymentsWereCreated."</div>";
        ?>
        <table class="table">
            <tr>
                <th><?php echo $formText_PaymentId_output;?></th>
                <th><?php echo $formText_InvoiceNumber_output;?></th>
                <th><?php echo $formText_PaymentDate_output;?></th>
                <th><?php echo $formText_PaymentAmount_output;?></th>
            </tr>
        <?php

        foreach($payments_created as $payment_created) {
            $sql = "SELECT creditor_invoice_payment.* FROM creditor_invoice_payment
            WHERE creditor_invoice_payment.id = ?";
            $o_query = $o_main->db->query($sql, array($payment_created));
            $creditor_invoice_payment = $o_query ? $o_query->row_array() : array();
            ?>
            <tr>
                <td><?php echo $creditor_invoice_payment['id'];?></td>
                <td><?php echo $creditor_invoice_payment['invoice_number'];?></td>
                <td><?php echo date("d.m.Y", strtotime($creditor_invoice_payment['date']));?></td>
                <td><?php echo $creditor_invoice_payment['amount'];?></td>
            </tr>
            <?php
        }
        ?>
        </table>
        <?php
    }
} else {
    echo $formText_NoCreditor_output;
}
?>
