<?php
set_time_limit(1800);
include(__DIR__."/../../../../CollectingCases/output/includes/fnc_calculate_interest.php");
include(__DIR__."/../../../../CollectingCases/output/includes/fnc_generate_pdf.php");
include_once(__DIR__."/../fnc_process_open_cases_for_tabs.php");
$sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($sql, array($creditorId));
$creditorData = $o_query ? $o_query->row_array() : array();
if($creditorData) {
	if(!function_exists("generateRandomString")) {
		function generateRandomString($length = 8) {
			$characters = '0123456789abcdefghijklmnopqrs092u3tuvwxyzaskdhfhf9882323ABCDEFGHIJKLMNksadf9044OPQRSTUVWXYZ';
			$charactersLength = strlen($characters);
			$randomString = '';
			for ($i = 0; $i < $length; $i++) {
				$randomString .= $characters[rand(0, $charactersLength - 1)];
			}
			return $randomString;
		}
	}
	if(!function_exists("customer_local_update")){
		function customer_local_update($customer_list, $creditorData, $updateOnly = false) {
			global $o_main;
			$organization_numbers = array();
			foreach($customer_list as $customer) {
				$getOrganizationType = false;
				$regNr = $customer['OrganizationNumber'];
				$external_id = $customer['Id'];
				$name = $customer['Name'];
				$postAddresses = $customer['Addresses']['Post'];
				$visitAddresses = $customer['Addresses']['Visit'];
				$invoiceAddresses = $customer['Addresses']['Invoice'];
				$phone = $customer['PhoneNumbers']['Work']['Value'];
				$fax = $customer['PhoneNumbers']['Fax']['Value'];
				$invoice_language = $customer['InvoiceLanguage'];

				$type = 0;
				if($customer['Type'] == "Consumer"){
					$type = 1;
				}
				if(intval($creditorData['invoice_email_priority']) == 0) {
					$email = $customer['EmailAddresses']['Work']['Value'];
					$work_email = $customer['EmailAddresses']['Invoice']['Value'];
				} else if($creditorData['invoice_email_priority'] == 1) {
					$email = $customer['EmailAddresses']['Invoice']['Value'];
					$work_email = $customer['EmailAddresses']['Work']['Value'];
				}				
				$extra_language = 0;
				if(mb_strtolower($invoice_language) != 'no') {
					$extra_language = 1;
				}

				if($email == ""){
					$email = $work_email;
				}
				if($invoiceAddresses['Street'] == "" && $invoiceAddresses['PostalCode'] == "" && $invoiceAddresses['PostalArea'] == "") {
					$invoiceAddresses = $postAddresses;
				}
				if($invoiceAddresses['Street'] == "" && $invoiceAddresses['PostalCode'] == "" && $invoiceAddresses['PostalArea'] == "") {
					$invoiceAddresses = $visitAddresses;
				}
				
				$sql = "SELECT * FROM customer WHERE customer.creditor_customer_id = ? AND customer.creditor_id = ?";
				$o_query = $o_main->db->query($sql, array($external_id, $creditorData['id']));
				$customerExist = $o_query ? $o_query->row_array() : array();
				if(!$customerExist) {
					if(!$updateOnly) {
						$sql = "INSERT INTO customer SET createdBy = 'import', created=NOW(), creditor_id = ?, creditor_customer_id = ?, name = ?, phone = ?, fax = ?, invoiceEmail = ?, publicRegisterId = ?, paStreet = ?,  paPostalNumber = ?, paCity = ?, paCountry = ?, vaStreet = ?,  vaPostalNumber = ?, vaCity = ?, vaCountry = ?, customer_type_collect = ?, integration_invoice_language=?";
						$o_query = $o_main->db->query($sql, array($creditorData['id'], $external_id, $name, $phone, $fax, $email, $regNr, $invoiceAddresses['Street'], $invoiceAddresses['PostalCode'],$invoiceAddresses['PostalArea'],$invoiceAddresses['Country'],
						$visitAddresses['Street'], $visitAddresses['PostalCode'],$visitAddresses['PostalArea'],$visitAddresses['Country'], $type, $extra_language));
						if($o_query) {
							$customer_id = $o_main->db->insert_id();
							if($regNr != ""){
								$getOrganizationType = true;
							}
						}
					}
				} else {
					$sql = "UPDATE customer SET updatedBy = 'import', updated=NOW(), name = ?, phone = ?, fax = ?, invoiceEmail = ?, publicRegisterId = ?, paStreet = ?,  paPostalNumber = ?, paCity = ?, paCountry = ?, vaStreet = ?,  vaPostalNumber = ?, vaCity = ?, vaCountry = ?, customer_type_collect = ?, integration_invoice_language=? WHERE id = ?";
					$o_query = $o_main->db->query($sql, array($name, $phone, $fax, $email, $regNr, $invoiceAddresses['Street'], $invoiceAddresses['PostalCode'],$invoiceAddresses['PostalArea'],$invoiceAddresses['Country'],
					$visitAddresses['Street'], $visitAddresses['PostalCode'],$visitAddresses['PostalArea'],$visitAddresses['Country'], $type, $extra_language, $customerExist['id']));
					if($o_query) {
						$customer_id = $customerExist['id'];
						if($regNr != ""){
							if(!$customerExist['organization_type_check'] && $customerExist['organization_type'] == ""){
								$getOrganizationType = true;
							}
						}
					}
				}
				if($getOrganizationType){
					$organization_numbers[] = $regNr; 
				}
			}
			
			// if(count($organization_numbers) > 0) {				
			// 	$chunk_organization_numbers = array_chunk($organization_numbers,300);
			// 	foreach($chunk_organization_numbers as $organization_numbers) {
			// 		$ch = curl_init();
			// 		curl_setopt($ch, CURLOPT_HEADER, 0);
			// 		curl_setopt($ch, CURLOPT_VERBOSE, 0);
			// 		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			// 		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
			// 		curl_setopt($ch, CURLOPT_POST, TRUE);
			// 		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
			// 		curl_setopt($ch, CURLOPT_URL, 'http://ap_api.getynet.com/brreg.php');
			// 		$v_post = array(
			// 			'organisation_no' => $organization_numbers,
			// 			'token' => 'RySBvCv3i9u6tP4mEd177X2gspGP6Rw0E512874043zDfUyHDsNF70gZvJ2R9s6idmGxk29amaRRR1R4Hbvqy93LJzPCz9oy',
			// 			'password' => '_9^JAj|7_atz~-Y.BndXBguO9_jr0^z~~09m-*:4SXlj|!KZ6Xd.hnJe3WM75H9Vy=;ZIcrab-84WLKN+4Kdz~+xM5U%ePIY'
			// 		);

			// 		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($v_post));
			// 		$s_response = curl_exec($ch);
					
			// 		$v_items = array();
			// 		$v_response = json_decode($s_response, TRUE);
			// 		if(isset($v_response['status']) && $v_response['status'] == 1 && $v_response['items'])
			// 		{
			// 			foreach($v_response['items'] as $v_item) {
			// 				$s_person_sql = "";
			// 				if(mb_strtolower($v_item['organisasjonsform']) == mb_strtolower("ENK")){
			// 					// $s_person_sql = ", customer_type_for_collecting_cases = 2";
			// 				}
			// 				// $sql = "UPDATE customer SET updatedBy = 'import', updated=NOW(), organization_type = ?".$s_person_sql." WHERE publicRegisterId = ?";
			// 				// $o_query = $o_main->db->query($sql, array($v_item['organisasjonsform'], $v_item['orgnr']));
							
			// 			}
			// 		}
			// 	}
			// }

		}
	}
    if(!function_exists("sync_transactions")){
        function sync_transactions($invoicesTransactions, $creditorData, $moduleID, $api, $sync_customer=true) {
            global $o_main;
			global $creditor_syncing_id;

			$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
			$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step 2 started - group transactions by customer', $creditor_syncing_id));
            $totalSum = 0;
            $groupedTransactions = array();
			$invoice_ids = array();
			$customerIds = array();
            foreach($invoicesTransactions as $invoicesTransaction) {
                $sql = "SELECT * FROM creditor_transactions WHERE transaction_id = ? AND creditor_id = ?";
                $o_query = $o_main->db->query($sql, array($invoicesTransaction['id'], $creditorData['id']));
                $local_transaction = $o_query ? $o_query->row_array() : array();
                if($invoicesTransaction['open']) {
                    $totalSum+=$invoicesTransaction['amount'];
                }
                $customerId = 0;
                if(isset($invoicesTransaction['dimensions']['Dimension']['Type'])) {
                    if($invoicesTransaction['dimensions']['Dimension']['Type'] == "Customer"){
                        $customerId = $invoicesTransaction['dimensions']['Dimension']['Value'];
                    }
                } else {
                    foreach($invoicesTransaction['dimensions']['Dimension'] as $dimension){
                        if($dimension['Type'] == "Customer"){
                            $customerId = $dimension['Value'];
                        }
                    }
                }
                if(!in_array($customerId, $customerIds)){
                    $customerIds[] = $customerId;
                }
                $invoice_ids[] = $invoicesTransaction['invoiceNr'];
                // var_dump($invoicesTransaction['dimensions']);
                $groupedTransactions[$customerId][] = $invoicesTransaction;
            }
			$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
			$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step 2 ended - group transactions by customer', $creditor_syncing_id));
            if($sync_customer) {
				$dataCustomer = array();
				$changedAfterDate = isset($creditorData['lastImportedDateTimestamp']) ? $creditorData['lastImportedDateTimestamp'] : "";
				if($changedAfterDate != null && $changedAfterDate != "") {
					$now = DateTime::createFromFormat('U.u', $changedAfterDate);
					if($now){
						$dataCustomer['changedAfter'] = $now->format("Y-m-d\TH:i:s.u");
					}
				}
				$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
				$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step 3 started - customer sync', $creditor_syncing_id));
				if(isset($dataCustomer['changedAfter'])) {
					$connect_tries = 0;
					do {
						$connect_tries++;
						$response_customer = $api->get_customer_list($dataCustomer);
						if($response_customer !== null){
							break;
						}
					} while($connect_tries < 11);
					$connect_tries--;

					$customer_list = $response_customer['GetCompaniesResult']['Company'];
					if(isset($customer_list['Id'])){
						$customer_list = array($customer_list);
					}
					$updated_customer_count = count($customer_list);
					customer_local_update($customer_list, $creditorData, $updateOnly);
				}
                if(count($customerIds) > 0) {
                    $bigCustomerIdArray = array_chunk($customerIds,1000);
                    foreach($bigCustomerIdArray as $customerIds) {
                        $customer_id = "";
						$dataCustomer = array();
                        $dataCustomer['customerIds'] = $customerIds;
						$connect_tries = 0;
						do {
							$connect_tries++;
							$response_customer = $api->get_customer_list($dataCustomer);
							if($response_customer !== null){
								break;
							}
						} while($connect_tries < 11);
						$connect_tries--;

                        $customer_list = $response_customer['GetCompaniesResult']['Company'];
                        if(isset($customer_list['Id'])){
                            $customer_list = array($customer_list);
                        }
						customer_local_update($customer_list, $creditorData);
                    }
                }
				$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?, number_of_tries = ?";
				$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step 3 ended - customer sync'.count($customerIds)." updated ".$updated_customer_count, $creditor_syncing_id, $connect_tries));
            }



            ksort($groupedTransactions);
            $totalImportedSuccessfully = 0;
            $lastImportedDate = "";
            $cases_to_check = array();
			$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
			$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step 4 start - update local transactions '.count($groupedTransactions), $creditor_syncing_id));
            foreach($groupedTransactions as $customerId => $invoicesTransactions) {
                $totalCustomerAmount = 0;
                foreach($invoicesTransactions as $invoicesTransaction) {
                    $importedSuccessfully = false;
                    $sql = "SELECT * FROM creditor_transactions WHERE transaction_id = ? AND creditor_id = ?";
                    $o_query = $o_main->db->query($sql, array($invoicesTransaction['id'], $creditorData['id']));
                    $local_transaction = $o_query ? $o_query->row_array() : array();
                    if($local_transaction) {
                        if($invoicesTransaction['open']) {
                            $sql = "UPDATE creditor_transactions SET moduleID = ?, creditor_id = ?, external_customer_id = ?, amount = ?, date=?, due_date=?, updatedBy = 'import', updated=NOW(), link_id = ?, invoice_nr = ?, kid_number = ?, system_type = ?, open = ?, transaction_id = ?, account_nr = ?, transaction_nr = ?, vatcode = ?, hidden = ?, dimensions = ?, date_changed = ?, comment = ?, not_processed = 1, currency = ?,currency_rate = ?, currency_unit = ?, to_be_reordered = 1  WHERE id = ?";
                            $o_query = $o_main->db->query($sql, array($moduleID, $creditorData['id'], $customerId, $invoicesTransaction['amount'],$invoicesTransaction['date'],$invoicesTransaction['dueDate'], $invoicesTransaction['linkId'], $invoicesTransaction['invoiceNr'], $invoicesTransaction['kidNumber'], $invoicesTransaction['systemType'], $invoicesTransaction['open'], $invoicesTransaction['id'], $invoicesTransaction['accountNr'], $invoicesTransaction['transactionNr'], $invoicesTransaction['vatCode'], $invoicesTransaction['hidden'], json_encode($invoicesTransaction['dimensions']), $invoicesTransaction['dateChanged'], $invoicesTransaction['comment'], $invoicesTransaction['currency'], $invoicesTransaction['currencyRate'], $invoicesTransaction['currencyUnit'], $local_transaction['id']));
                            if($o_query) {
                                $importedSuccessfully = true;
                            }
                        } else {
                            $sql = "UPDATE creditor_transactions SET moduleID = ?, creditor_id = ?, external_customer_id = ?, amount = ?, date=?, due_date=?, updatedBy = 'import', updated=NOW(), link_id = ?, invoice_nr = ?, kid_number = ?, system_type = ?, open = ?, transaction_id = ?, account_nr = ?, transaction_nr = ?, vatcode = ?, hidden = ?, dimensions = ?, date_changed = ?, comment = ?, not_processed = 1, currency = ?,currency_rate = ?, currency_unit = ?, to_be_reordered = 1  WHERE id = ?";
                            $o_query = $o_main->db->query($sql, array($moduleID, $creditorData['id'], $customerId, $invoicesTransaction['amount'],$invoicesTransaction['date'],$invoicesTransaction['dueDate'], $invoicesTransaction['linkId'], $invoicesTransaction['invoiceNr'], $invoicesTransaction['kidNumber'], $invoicesTransaction['systemType'], $invoicesTransaction['open'], $invoicesTransaction['id'], $invoicesTransaction['accountNr'], $invoicesTransaction['transactionNr'], $invoicesTransaction['vatCode'], $invoicesTransaction['hidden'], json_encode($invoicesTransaction['dimensions']), $invoicesTransaction['dateChanged'], $invoicesTransaction['comment'],  $invoicesTransaction['currency'], $invoicesTransaction['currencyRate'], $invoicesTransaction['currencyUnit'], $local_transaction['id']));
                            if($o_query) {
                                $importedSuccessfully = true;
                            }
                        }
                    } else {
                        $sql = "INSERT INTO creditor_transactions SET moduleID = ?, creditor_id = ?, external_customer_id = ?, amount = ?, date=?, due_date=?, createdBy = 'import', created=NOW(), link_id = ?, invoice_nr = ?, kid_number = ?, system_type = ?, open = ?, transaction_id = ?, account_nr = ?, transaction_nr = ?, vatcode = ?, hidden = ?, dimensions = ?, date_changed = ?, comment = ?, not_processed = 1, currency = ?,currency_rate = ?, currency_unit = ?, to_be_reordered = 1";
                        $o_query = $o_main->db->query($sql, array($moduleID, $creditorData['id'], $customerId, $invoicesTransaction['amount'],$invoicesTransaction['date'],$invoicesTransaction['dueDate'], $invoicesTransaction['linkId'], $invoicesTransaction['invoiceNr'], $invoicesTransaction['kidNumber'], $invoicesTransaction['systemType'], $invoicesTransaction['open'], $invoicesTransaction['id'], $invoicesTransaction['accountNr'], $invoicesTransaction['transactionNr'], $invoicesTransaction['vatCode'], $invoicesTransaction['hidden'], json_encode($invoicesTransaction['dimensions']), $invoicesTransaction['dateChanged'], $invoicesTransaction['comment'], $invoicesTransaction['currency'], $invoicesTransaction['currencyRate'], $invoicesTransaction['currencyUnit']));
                        if($o_query) {
                            $transactionId = $o_main->db->insert_id();

                            $importedSuccessfully = true;
                        }
                    }
                    if($importedSuccessfully){
                        $totalImportedSuccessfully++;
                        if(strtotime($invoicesTransaction['dateChanged']) > strtotime($lastImportedDate)){
                            $lastImportedDate = $invoicesTransaction['dateChanged'];
                        }
                    }
                }
            }


			// $s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
			// $o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'invoice syncing started', $creditor_syncing_id));
			//
			// $dataInvoice = array();
			// if(count($invoice_ids) > 0){
			// 	$dataInvoice['invoiceIds'] = $invoice_ids;
			// 	$connect_tries = 0;
			// 	do {
			// 		$connect_tries++;
			// 		$response_invoice = $api->get_invoice_list($dataInvoice);
			// 		var_dump($response_invoice);
			// 		if($response_invoice !== null){
			// 			break;
			// 		}
			// 	} while($connect_tries < 11);
			// 	$connect_tries--;
			//
			// 	$invoice_list = $response_customer['GetInvoicesResult']['InvoiceOrder'];
			// 	if(isset($invoice_list['InvoiceId'])){
			// 		$invoice_list = array($invoice_list);
			// 	}
			// 	foreach($invoice_list as $single_invoice){
			// 		$sql = "UPDATE creditor_transactions SET integration_project_id = ?, integration_department_id = ?  WHERE invoice_nr = ? AND creditor_id = ?";
			// 		$o_query = $o_main->db->query($sql, array($single_invoice['ProjectId'], $single_invoice['DepartmentId'], $single_invoice['InvoiceId'], $creditorData['id']));
			// 	}
			// }
			//
			// $s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?, number_of_tries = ?";
			// $o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'invoice syncing ended', $creditor_syncing_id, $connect_tries));


			$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
			$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step 4 ended - update local transactions', $creditor_syncing_id));

            return array($totalImportedSuccessfully, $lastImportedDate, $cases_to_check, $totalSum);
        }
    }
	
    if(!function_exists("trigger_syncing")){
		function trigger_syncing($creditorData, $moduleID, $api, $currencyRates, $sync_creditor_info, $sync_deep_level){
			global $o_main;			
			global $creditor_syncing_id;
			
			//to get updated information on each sync trigger
			$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
			$o_query = $o_main->db->query($s_sql, array($creditorData['id']));
			$creditorData = ($o_query ? $o_query->row_array() : array());

			$sync_deep_level++;

			$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
			$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step 1 started - getting transactions from integration '.$api->clientId, $creditor_syncing_id));
			$customerIds = array();
			$data['changedAfter'] = date("Y-m-d", strtotime("01.01.2012"));
			$data['ShowOpenEntries'] = 1;
			$changedAfterDate = isset($creditorData['lastImportedDateTimestamp']) ? $creditorData['lastImportedDateTimestamp'] : "";
			if($changedAfterDate != null && $changedAfterDate != ""){
				$changedAfterDate = number_format($changedAfterDate+0.001, 3, ".", "");
				$now = DateTime::createFromFormat('U.u', $changedAfterDate);
				if($now){
					$data['changedAfter'] = $now->format("Y-m-d\TH:i:s.u");
					// var_dump($data);
					$data['ShowOpenEntries'] = null;
				}
			}
			if($data['ShowOpenEntries']) {
				$loopCounter = 0;
				do {
					$dateStart = $data['changedAfter'];
					if($creditorData['id'] == 3393) {
						$dateEnd = date('Y-m-d', strtotime("+1 month", strtotime($dateStart)));
					} else {
						$dateEnd = date('Y-m-t', strtotime("+1 year", strtotime($dateStart)));
					}

					$transactionData = array();
					$transactionData['DateSearchParameters'] = 'DateChangedUTC';
					$transactionData['date_start'] = $dateStart;
					$transactionData['date_end'] = $dateEnd;
					$transactionData['bookaccountStart'] = 1500;
					$transactionData['bookaccountEnd'] = 1529;
					$transactionData['ShowOpenEntries'] = $data['ShowOpenEntries'];
					$connect_tries = 0;
					do {
						$connect_tries++;
						$invoicesTransactionsApi = $api->get_transactions($transactionData);
						if($invoicesTransactionsApi !== null){
							$invoicesTransactions = array_merge($invoicesTransactionsApi, $invoicesTransactions);
							$connectedSuccessfully = true;
							break;
						}
					} while($connect_tries < 11);
					$connect_tries--;
					$data['changedAfter'] = $dateEnd;
					$loopCounter++;
				} while(strtotime($dateEnd) < time());
			} else {
				$transactionData = array();
				$transactionData['DateSearchParameters'] = 'DateChangedUTC';
				$transactionData['date_start'] = $data['changedAfter'];
				$transactionData['date_end'] = date('Y-m-t', time() + 60*60*24);
				$transactionData['bookaccountStart'] = 1500;
				$transactionData['bookaccountEnd'] = 1529;
				$transactionData['ShowOpenEntries'] = $data['ShowOpenEntries'];

				$connect_tries = 0;
				$connectedSuccessfully = false;
				do {
					$connect_tries++;
					$invoicesTransactions = $api->get_transactions($transactionData);
					if($invoicesTransactions !== null){
						$connectedSuccessfully = true;
						break;
					}
				} while($connect_tries < 11);
				$connect_tries--;
			}

			$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?, number_of_tries = ?, number_of_transactions = ?";
			$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step 1 ended - getting transactions from integration', $creditor_syncing_id, $connect_tries, count($invoicesTransactions)));
			if($connectedSuccessfully){
				if($sync_creditor_info){
					$connect_tries = 0;
					do {
						$connect_tries++;
						$company_info = $api->get_company_info();
						if($company_info['GetClientInformationResult']){
							$company_info_array = $company_info['GetClientInformationResult'];
							$companyname = $company_info_array['Name'];
							$bank_account = $company_info_array['BankAccount'];
							$companyorgnr = $company_info_array['OrganizationNumber'];
							$companypostalbox = $company_info_array['AddressList']['Post']['Street'];
							$companyzipcode = $company_info_array['AddressList']['Post']['PostalCode'];
							$companypostalplace = $company_info_array['AddressList']['Post']['PostalArea'];
							$companyphone = $company_info_array['PhoneNumberList']['Work']['Value'];
							$companyemail = $company_info_array['EmailAddressList']['Work']['Value'];

							$s_sql = "UPDATE creditor SET updated = NOW(), updatedBy = '24sevenintegration',
							bank_account = '".$o_main->db->escape_str($bank_account)."',
							companyname = '".$o_main->db->escape_str($companyname)."',
							companypostalbox = '".$o_main->db->escape_str($companypostalbox)."',
							companyzipcode = '".$o_main->db->escape_str($companyzipcode)."',
							companypostalplace = '".$o_main->db->escape_str($companypostalplace)."',
							companyorgnr = '".$o_main->db->escape_str($companyorgnr)."',
							companyEmail = '".$o_main->db->escape_str($companyemail)."',
							companyphone = '".$o_main->db->escape_str($companyphone)."'
							WHERE id = ?";
							$o_query = $o_main->db->query($s_sql, array($creditorData['id']));
							break;
						}
						sleep($connect_tries);
					} while($connect_tries < 11);
					$connect_tries--;

					$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?, number_of_tries = ?, type = 3";
					$o_query = $o_main->db->query($s_sql, array(0, 'company info selection syncing ', $creditor_syncing_id, $connect_tries));
				}
				// var_dump($invoicesTransactions);
				list($totalImportedSuccessfully, $lastImportedDate, $cases_to_check, $totalSum) = sync_transactions($invoicesTransactions, $creditorData, $moduleID, $api, true);
				if($totalImportedSuccessfully > 0) {
					if($lastImportedDate != ""){
						$dateTime = new DateTime($lastImportedDate);
						$timestamp = $dateTime->format("U");
						$microseconds = $dateTime->format("u");
						$sql = "UPDATE creditor SET lastImportedDateTimestamp = ? WHERE id = ?";
						$o_query = $o_main->db->query($sql, array($timestamp.".".$microseconds, $creditorData['id']));
					}
					$triggerSync = false;
					$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
					$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step 5 started - transaction linking', $creditor_syncing_id));

					$sql = "SELECT * FROM creditor_transactions
					WHERE creditor_id = ? AND open = 1 AND link_id is null AND comment LIKE '%-%-%-%' AND date_changed >= '".date("Y-m-d", strtotime("-10 days"))."'";
					$o_query = $o_main->db->query($sql, array($creditorData['id']));
					$local_transactions = $o_query ? $o_query->result_array() : array();
					$connectedSuccessfully = true;
					$total_transaction_to_link = 0;
					$current_transaction_success = 0;
					foreach($local_transactions as $local_transaction) {
						if($local_transaction['comment'] != ""){
							$sql = "SELECT * FROM creditor_transactions WHERE transaction_id = ? AND creditor_id = ? AND open = 1";
							$o_query = $o_main->db->query($sql, array($local_transaction['comment'], $creditorData['id']));
							$parent_transaction = $o_query ? $o_query->row_array() : array();
							if($parent_transaction){
								$connect_tries = 0;
								$total_transaction_to_link++;
								do {
									$connect_tries++;
									$linkArray = array();
									$linkArray['transaction1_id'] = $parent_transaction['transaction_id'];
									$linkArray['transaction2_id'] = $local_transaction['transaction_id'];
									$links_created_result = $api->create_link($linkArray);
									if($links_created_result){
										$current_transaction_success++;
										$triggerSync = true;
										break;
									}
								} while($connect_tries < 11);
								$connect_tries--;
								$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?, number_of_tries = ?";
								$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'Transaction links result '.json_encode($links_created_result).', data: '.json_encode($linkArray), $creditor_syncing_id, $connect_tries));
							}
						}
					}

					if($triggerSync && $sync_deep_level < 2) {
						$sync_deep_level = trigger_syncing($creditorData, $moduleID, $api, $currencyRates, false, $sync_deep_level);
					}
				}
			}
			return $sync_deep_level;
		}
	}

	if($doNotTriggerInitialSync){
		return;
	}
    $sql = "SELECT * FROM moduledata WHERE name = 'CreditorsOverview'";
    $o_query = $o_main->db->query($sql);
    $moduleInfo = $o_query ? $o_query->row_array() : array();
    $moduleID = $moduleInfo['uniqueID'];


    $sql = "SELECT * FROM collecting_system_settings ";
    $o_query = $o_main->db->query($sql);
    $collecting_system_settings = $o_query ? $o_query->row_array() : array();

    $invoices_created = array();
    $payments_created = array();
    if($creditorData['integration_module'] == "Integration24SevenOffice"){
		if(!class_exists("Integration24SevenOffice")){
		    require_once __DIR__ . '/../../../../'.$creditorData['integration_module'].'/internal_api/load.php';
		}
		$reminderRestNoteMinimumAmount = $collecting_system_settings['reminderRestNoteMinimumAmount'];
		if($creditorData['use_customized_reminder_rest_note_min_amount']){
			$reminderRestNoteMinimumAmount = $creditorData['reminderRestNoteMinimumAmount'];
		}
			$connection_error = false;
			$failedMsg = "";
        // if($creditorData['entity_id'] == "") {
        //     echo $formText_NoEntityId_output;
        // } else {
			if(!$fromProcessCases && !$fromResetFees){
				$s_sql = "INSERT INTO creditor_syncing SET created = NOW(), creditor_id = ?, started = NOW()";
				$o_query = $o_main->db->query($s_sql, array($creditorData['id']));
				if($o_query){
					$creditor_syncing_id = $o_main->db->insert_id();
				}
			}
			$v_config = array(
                'ownercompany_id' => 1,
                'identityId' => $creditorData['entity_id'],
                'creditorId' => $creditorData['id'],
                'o_main' => $o_main
            );
			$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE username = '".$o_main->db->escape_str($username)."' AND creditor_id = '".$o_main->db->escape_str($creditorData['id'])."'";
			$o_query = $o_main->db->query($s_sql);
			if($o_query && 0 < $o_query->num_rows())
			{
				$v_int_session = $o_query->row_array();
				$v_config['session_id'] = $v_int_session['session_id'];
			}
			// var_dump($v_config);
			$invoicesTransactions = array();
			$connectedSuccessfully = false;
			try {
				$api = new Integration24SevenOffice($v_config);
	            if($api->error == "") {
					$reminder_bookaccount = 8070;
					$interest_bookaccount = 8050;
					if($creditorData['reminder_bookaccount'] != ""){
						$reminder_bookaccount = $creditorData['reminder_bookaccount'];
					}
					if($creditorData['interest_bookaccount'] != ""){
						$interest_bookaccount = $creditorData['interest_bookaccount'];
					}
					
					$currencyRates = array();
									
					$hook_file = __DIR__ . '/../../../../Integration24SevenOffice/hooks/get_currency_rates.php';
					if (file_exists($hook_file)) {
						include $hook_file;
						if (is_callable($run_hook)) {
							$hook_result = $run_hook(array("creditor_id"=>$creditorData['id']));
							if(count($hook_result['currencyRates']) > 0){
								$currencyRates = $hook_result['currencyRates'];
							}
						}
					}
	                //
	                // $linkArray = array();
	                // $linkArray['transaction1_id'] = "9d0a8d72-13be-430c-9e5f-b971129b64b7";
	                // $linkArray['transaction2_id'] = "f12dc9b3-3152-43ec-9243-c9efc5b52742";
	                // $links_created_result = $api->create_link($linkArray);

					$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
					$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step 1 started - getting transactions from integration '.$api->clientId, $creditor_syncing_id));
	                $customerIds = array();

	                $data['changedAfter'] = date("Y-m-d", strtotime("01.01.2012"));
	                $data['ShowOpenEntries'] = 1;

					$changedAfterDate = isset($creditorData['lastImportedDateTimestamp']) ? $creditorData['lastImportedDateTimestamp'] : "";
					if($changedAfterDate != null && $changedAfterDate != ""){
						$changedAfterDate = number_format($changedAfterDate+0.001, 3, ".", "");
						$now = DateTime::createFromFormat('U.u', $changedAfterDate);
						if($now){
							$data['changedAfter'] = $now->format("Y-m-d\TH:i:s.u");
							// var_dump($data);
							$data['ShowOpenEntries'] = null;
						}
					}
					if($data['ShowOpenEntries']) {
						$loopCounter = 0;
						do {
							$dateStart = $data['changedAfter'];
							if($creditorData['id'] == 3393) {
								$dateEnd = date('Y-m-d', strtotime("+1 month", strtotime($dateStart)));
							} else {
								$dateEnd = date('Y-m-t', strtotime("+1 year", strtotime($dateStart)));
							}

							$transactionData = array();
							$transactionData['DateSearchParameters'] = 'DateChangedUTC';
							$transactionData['date_start'] = $dateStart;
							$transactionData['date_end'] = $dateEnd;
							$transactionData['bookaccountStart'] = 1500;
							$transactionData['bookaccountEnd'] = 1529;
							$transactionData['ShowOpenEntries'] = $data['ShowOpenEntries'];
							$connect_tries = 0;
							do {
								$connect_tries++;
								$invoicesTransactionsApi = $api->get_transactions($transactionData);
								if($invoicesTransactionsApi !== null){
									$invoicesTransactions = array_merge($invoicesTransactionsApi, $invoicesTransactions);
									$connectedSuccessfully = true;
									break;
								}
							} while($connect_tries < 11);
							$connect_tries--;
							$data['changedAfter'] = $dateEnd;
							$loopCounter++;
						} while(strtotime($dateEnd) < time());
					} else {
						$transactionData = array();
						$transactionData['DateSearchParameters'] = 'DateChangedUTC';
						$transactionData['date_start'] = $data['changedAfter'];
						$transactionData['date_end'] = date('Y-m-t', time() + 60*60*24);
						$transactionData['bookaccountStart'] = 1500;
						$transactionData['bookaccountEnd'] = 1529;
						$transactionData['ShowOpenEntries'] = $data['ShowOpenEntries'];

						$connect_tries = 0;
						$connectedSuccessfully = false;
						do {
							$connect_tries++;
							$invoicesTransactions = $api->get_transactions($transactionData);
							if($invoicesTransactions !== null){
								$connectedSuccessfully = true;
								break;
							}
						} while($connect_tries < 11);
						$connect_tries--;
					}

					$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?, number_of_tries = ?, number_of_transactions = ?";
					$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step 1 ended - getting transactions from integration', $creditor_syncing_id, $connect_tries, count($invoicesTransactions)));
					if($connectedSuccessfully){
						$connect_tries = 0;
						do {
							$connect_tries++;
							$company_info = $api->get_company_info();
							if($company_info['GetClientInformationResult']){
								$company_info_array = $company_info['GetClientInformationResult'];
								$companyname = $company_info_array['Name'];
								$bank_account = $company_info_array['BankAccount'];
								$companyorgnr = $company_info_array['OrganizationNumber'];
								$companypostalbox = $company_info_array['AddressList']['Post']['Street'];
								$companyzipcode = $company_info_array['AddressList']['Post']['PostalCode'];
								$companypostalplace = $company_info_array['AddressList']['Post']['PostalArea'];
								$companyphone = $company_info_array['PhoneNumberList']['Work']['Value'];
								$companyemail = $company_info_array['EmailAddressList']['Work']['Value'];

								$s_sql = "UPDATE creditor SET updated = NOW(), updatedBy = '24sevenintegration',
								bank_account = '".$o_main->db->escape_str($bank_account)."',
								companyname = '".$o_main->db->escape_str($companyname)."',
								companypostalbox = '".$o_main->db->escape_str($companypostalbox)."',
								companyzipcode = '".$o_main->db->escape_str($companyzipcode)."',
								companypostalplace = '".$o_main->db->escape_str($companypostalplace)."',
								companyorgnr = '".$o_main->db->escape_str($companyorgnr)."',
								companyEmail = '".$o_main->db->escape_str($companyemail)."',
								companyphone = '".$o_main->db->escape_str($companyphone)."'
								WHERE id = ?";
								$o_query = $o_main->db->query($s_sql, array($creditorData['id']));
								break;
							}
							sleep($connect_tries);
						} while($connect_tries < 11);
						$connect_tries--;

						$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?, number_of_tries = ?, type = 3";
						$o_query = $o_main->db->query($s_sql, array(0, 'company info selection syncing ', $creditor_syncing_id, $connect_tries));

		                // var_dump($invoicesTransactions);
		                list($totalImportedSuccessfully, $lastImportedDate, $cases_to_check, $totalSum) = sync_transactions($invoicesTransactions, $creditorData, $moduleID, $api, true);

		                if($totalImportedSuccessfully > 0) {
		                    if($lastImportedDate != ""){
		                        $dateTime = new DateTime($lastImportedDate);
		                        $timestamp = $dateTime->format("U");
		                        $microseconds = $dateTime->format("u");
		                        $sql = "UPDATE creditor SET lastImportedDateTimestamp = ? WHERE id = ?";
		                        $o_query = $o_main->db->query($sql, array($timestamp.".".$microseconds, $creditorData['id']));
		                    }
		                    $triggerSync = false;
							$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
						   	$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step 5 started - transaction linking', $creditor_syncing_id));

		                    $sql = "SELECT * FROM creditor_transactions
							WHERE creditor_id = ? AND open = 1 AND link_id is null AND comment is not null AND date_changed >= '".date("Y-m-d", strtotime("-10 days"))."'";
		                    $o_query = $o_main->db->query($sql, array($creditorData['id']));
		                    $local_transactions = $o_query ? $o_query->result_array() : array();
							$connectedSuccessfully = true;
							$total_transaction_to_link = 0;
							$current_transaction_success = 0;
		                    foreach($local_transactions as $local_transaction) {
								if($local_transaction['comment'] != ""){
									$sql = "SELECT * FROM creditor_transactions WHERE transaction_id = ? AND creditor_id = ? AND open = 1";
									$o_query = $o_main->db->query($sql, array($local_transaction['comment'], $creditorData['id']));
									$parent_transaction = $o_query ? $o_query->row_array() : array();
									if($parent_transaction){
										$connect_tries = 0;
										$total_transaction_to_link++;
										do {
											$connect_tries++;
											$linkArray = array();
											$linkArray['transaction1_id'] = $parent_transaction['transaction_id'];
											$linkArray['transaction2_id'] = $local_transaction['transaction_id'];
											$links_created_result = $api->create_link($linkArray);
											if($links_created_result){
												$current_transaction_success++;
												$triggerSync = true;
												break;
											}
										} while($connect_tries < 11);
										$connect_tries--;
										$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?, number_of_tries = ?";
										$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'Transaction links result '.json_encode($links_created_result).', data: '.json_encode($linkArray), $creditor_syncing_id, $connect_tries));
									}
								}
		                    }
							if($total_transaction_to_link != $current_transaction_success){
								$connectedSuccessfully = false;
							}
							if($connectedSuccessfully) {
								$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
							   	$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step 5 ended transaction linking', $creditor_syncing_id));

								if($triggerSync){
									$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
									$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step 7 started - syncing after links created', $creditor_syncing_id));
									$connect_tries = 0;
									$connectedSuccessfully = false;

									$transactionData = array();
									$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
									$o_query = $o_main->db->query($s_sql, array($creditorData['id']));
									$updatedCreditorData = ($o_query ? $o_query->row_array() : array());

									$changedAfterDate = isset($updatedCreditorData['lastImportedDateTimestamp']) ? $updatedCreditorData['lastImportedDateTimestamp'] : "";
									if($changedAfterDate != null && $changedAfterDate != ""){
										$changedAfterDate = number_format($changedAfterDate+0.001, 3, ".", "");
										$now = DateTime::createFromFormat('U.u', $changedAfterDate);
										if($now){
											$transactionData['date_start'] = $now->format("Y-m-d\TH:i:s.u");
										}
									}
									$transactionData['DateSearchParameters'] = 'DateChangedUTC';
									$transactionData['date_end'] = date('Y-m-t', time() + 60*60*24);
									$transactionData['bookaccountStart'] = 1500;
									$transactionData['bookaccountEnd'] = 1529;
									$transactionData['ShowOpenEntries'] = null;
									do {
										$connect_tries++;
										$invoicesTransactions = $api->get_transactions($transactionData);
										if($invoicesTransactions !== null){
											$connectedSuccessfully = true;
											break;
										}
									} while($connect_tries < 11);
									$connect_tries--;
									list($totalImportedSuccessfully_links, $lastImportedDate_links, $cases_to_check_links, $totalSum_links) = sync_transactions($invoicesTransactions, $creditorData, $moduleID, $api, false);
									if($totalImportedSuccessfully_links > 0) {
										if($lastImportedDate_links != ""){
											$dateTime = new DateTime($lastImportedDate_links);
											$timestamp = $dateTime->format("U");
											$microseconds = $dateTime->format("u");
											$sql = "UPDATE creditor SET lastImportedDateTimestamp = ? WHERE id = ?";
											$o_query = $o_main->db->query($sql, array($timestamp.".".$microseconds, $creditorData['id']));
										}
									}
									$triggerSync = false;

									$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?, number_of_tries = ?, number_of_transactions = ?";
									$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step 7 ended - syncing after links created', $creditor_syncing_id, $connect_tries, count($invoicesTransactions)));
								}
								$sql = "SELECT * FROM creditor_transactions WHERE creditor_id = ? AND not_processed = 1 AND collectingcase_id > 0";
			                    $o_query = $o_main->db->query($sql, array($creditorData['id']));
			                    $transactions_with_cases = $o_query ? $o_query->result_array() : array();

								$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
							   	$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step 6 started - checking credited amounts', $creditor_syncing_id));
								foreach($transactions_with_cases as $invoice) {
			                        $s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
			                        $o_query = $o_main->db->query($s_sql, array($invoice['collectingcase_id']));
			                        $case = $o_query ? $o_query->row_array() : array();
									if($case){
										$toBePaid = $invoice['collecting_case_original_claim'];

										$s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') AND link_id = ? AND creditor_id = ?";
										$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
										$invoice_payments = ($o_query ? $o_query->result_array() : array());

										if(count($invoice_payments) > 0) {
											$validForRest = false;
											$creditedAmount = 0;
											$credited = false;
											$lastCaseUpdateDate = $case['created'];

											$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE case_id = ? ORDER BY created DESC";
				                            $o_query = $o_main->db->query($s_sql, array($case['id']));
				                            $last_claim_letter = ($o_query ? $o_query->row_array() : array());
											if($last_claim_letter && strtotime($last_claim_letter['created']) > strtotime($lastCaseUpdateDate)) {
												$lastCaseUpdateDate = $last_claim_letter['created'];
											}
											$credited_transactions = array();
											foreach($invoice_payments as $invoice_payment){
												if($invoice_payment['system_type'] == 'CreditnoteCustomer') {
													$s_sql = "SELECT * FROM collecting_cases_reset_info WHERE creditor_transaction_id = ? AND collecting_case_id = ? ORDER BY created DESC";
						                            $o_query = $o_main->db->query($s_sql, array($invoice_payment['id'], $case['id']));
						                            $resetHappened = ($o_query ? $o_query->row_array() : array());
													if(!$resetHappened) {
														$creditedAmount+=$invoice_payment['amount'];
														$credited = true;
														$credited_transactions[] = $invoice_payment;
													}
												}
											}
											$creditedAmount = $creditedAmount*-1;
											if(count($credited_transactions) > 0) {

												$noFeeError3 = true;
												$s_sql = "SELECT * FROM creditor_transactions WHERE system_type = 'InvoiceCustomer'  AND link_id = ? AND creditor_id = ? AND open = 1 AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%\_%'";
												$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
												$fee_transactions = $o_query ? $o_query->result_array() : array();
												if(count($fee_transactions) > 0) {
													$noFeeError3 = false;
												}
												$noFeeError3count = 0;
												foreach($fee_transactions as $fee_transaction) {
													$commentArray = explode("_",$fee_transaction['comment']);
													if($commentArray[2] == "interest"){
													   	$transactionType = "interest";
													} else if($commentArray[2] == "reminderFee"){
													  	$transactionType = "reminderFee";
													} else if($commentArray[0] == "Rente"){
														$transactionType = "interest";
													} else {
														$transactionType = "reminderFee";
													}
													$hook_params = array (
														'transaction_id' => $fee_transaction['id'],
														'amount'=>$fee_transaction['amount']*(-1),
														'dueDate'=>$dueDate,
														'text'=>$commentArray[0],
														'type'=>$transactionType,
														'accountNo'=>$commentArray[1],
														'close'=> 1
													);

													$hook_file = __DIR__ . '/../../../../Integration24SevenOffice/hooks/insert_transaction.php';
													if (file_exists($hook_file)) {
														include $hook_file;
														if (is_callable($run_hook)) {
															$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?";
															$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'reset interest started', $creditor_syncing_id));

															$connect_tries = 0;
															do {
																$connect_tries++;
																$hook_result = $run_hook($hook_params);
																if($hook_result['result']){
																	break;
																}
															} while($connect_tries < 11);
															$connect_tries--;
															if($hook_result['result']){
																$noFeeError3count++;
																$triggerSync = true;
																$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?, number_of_tries = ?";
																$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'reset interest finished', $creditor_syncing_id, $connect_tries));
															} else {
																// var_dump("deleteError".$hook_result['error']);
																$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?, number_of_tries = ?";
																$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'reset interest failed: '.json_encode($hook_result['error']), $creditor_syncing_id, $connect_tries));
															}
														}
													}
												}

												if($noFeeError3count == count($fee_transactions)){
													$noFeeError3 = true;
												} else {
													$connectedSuccessfully = false;
												}
												if($connectedSuccessfully) {
													if($creditedAmount > 0) {
														$newDueDate = date("Y-m-d", strtotime("+".intval($collecting_system_settings['minimumDaysForDueDateAfterPartlyCredit'])." days", time()));

							                            $s_sql = "UPDATE collecting_cases SET due_date = '".$o_main->db->escape_str($newDueDate)."', collecting_cases_process_step_id = 0, updated=NOW(), updatedBy = 'import-CREDITED due date',  WHERE id = '".$o_main->db->escape_str($case['id'])."'";
							                            $o_query = $o_main->db->query($s_sql);
														foreach($credited_transactions as $credited_transaction) {
															$s_sql = "INSERT INTO collecting_cases_reset_info SET created = NOW(), createdBy='import', creditor_transaction_id = ?, collecting_case_id = ?, info='reset by credited'";
								                            $o_query = $o_main->db->query($s_sql, array($credited_transaction['id'], $case['id']));
														}
													}
												}
											}
										}
									}
								}
								if($connectedSuccessfully) {
									$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
								   	$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step 6 ended - checking credited amounts', $creditor_syncing_id));
				                    if($triggerSync){
										$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
									   	$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step 7 started - syncing after links created', $creditor_syncing_id));
										$connect_tries = 0;
										$connectedSuccessfully = false;
										$transactionData = array();
										$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
										$o_query = $o_main->db->query($s_sql, array($creditorData['id']));
										$updatedCreditorData = ($o_query ? $o_query->row_array() : array());

										$changedAfterDate = isset($updatedCreditorData['lastImportedDateTimestamp']) ? $updatedCreditorData['lastImportedDateTimestamp'] : "";
										if($changedAfterDate != null && $changedAfterDate != ""){
											$changedAfterDate = number_format($changedAfterDate+0.001, 3, ".", "");
											$now = DateTime::createFromFormat('U.u', $changedAfterDate);
											if($now){
												$transactionData['date_start'] = $now->format("Y-m-d\TH:i:s.u");
											}
										}
										$transactionData['DateSearchParameters'] = 'DateChangedUTC';
										$transactionData['date_end'] = date('Y-m-t', time() + 60*60*24);
										$transactionData['bookaccountStart'] = 1500;
										$transactionData['bookaccountEnd'] = 1529;
										$transactionData['ShowOpenEntries'] = null;
										do {
											$connect_tries++;
											$invoicesTransactions = $api->get_transactions($transactionData);
											if($invoicesTransactions !== null){
												$connectedSuccessfully = true;
												break;
											}
										} while($connect_tries < 11);
										$connect_tries--;
				                        list($totalImportedSuccessfully_links, $lastImportedDate_links, $cases_to_check_links, $totalSum_links) = sync_transactions($invoicesTransactions, $creditorData, $moduleID, $api, false);
										if($totalImportedSuccessfully_links > 0) {
											if($lastImportedDate_links != ""){
												$dateTime = new DateTime($lastImportedDate_links);
												$timestamp = $dateTime->format("U");
												$microseconds = $dateTime->format("u");
												$sql = "UPDATE creditor SET lastImportedDateTimestamp = ? WHERE id = ?";
												$o_query = $o_main->db->query($sql, array($timestamp.".".$microseconds, $creditorData['id']));
											}
										}
										$triggerSync = false;
										$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?, number_of_tries = ?, number_of_transactions = ?";
										$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step 7 ended - syncing after links created', $creditor_syncing_id, $connect_tries, count($invoicesTransactions)));
									}
									if($connectedSuccessfully) {
										$createRestForCases = array();

										$cases_checked = array();
					                    $sql = "SELECT * FROM creditor_transactions WHERE creditor_id = ? AND not_processed = 1";
					                    $o_query = $o_main->db->query($sql, array($creditorData['id']));
					                    $local_transactions = $o_query ? $o_query->result_array() : array();

										$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
									   	$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step 8 started - main process local transactions', $creditor_syncing_id));
										foreach($local_transactions as $local_transaction) {
											if($local_transaction['system_type'] == "Payment") {
												$sql = "SELECT * FROM creditor_transactions WHERE link_id = ? AND creditor_id = ? AND collecting_company_case_id > 0";
							                    $o_query = $o_main->db->query($sql, array($local_transaction['link_id'], $creditorData['id']));
							                    $parent_company_transaction = $o_query ? $o_query->row_array() : array();
												if($parent_company_transaction) {
													$s_sql = "SELECT * FROM collecting_company_cases WHERE id = ?";
													$o_query = $o_main->db->query($s_sql, array($parent_company_transaction['collecting_company_case_id']));
													$companyCase = ($o_query ? $o_query->row_array() : array());
													if(strtotime($local_transaction['created']) >= strtotime(date("Y-m-d", strtotime($companyCase['created'])))) {
														if(intval($local_transaction['company_claimline_id']) == 0){
															$payment_after_closed_sql = ", payment_after_closed = 0";
															if($companyCase['case_closed_date'] != "0000-00-00" && $companyCase['case_closed_date'] != ""){
																$payment_after_closed_sql = ", payment_after_closed = 1";
															}
															$s_sql = "SELECT cmt.* FROM cs_mainbook_transaction cmt
															JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
															WHERE cmv.case_id = '".$o_main->db->escape_str($companyCase['id'])."' AND cmt.bookaccount_id = 20 AND IFNULL(cmt.used_as_settlement_payment,0) = 0";
															$o_query = $o_main->db->query($s_sql);
															$mainclaim_to_creditor_transaction = ($o_query ? $o_query->row_array() : array());
															$claim_type_id = 18;
															$trigger_mainclaim_transaction_update = false;
															if($mainclaim_to_creditor_transaction){
																if(abs($mainclaim_to_creditor_transaction['amount']) == abs($local_transaction['amount'])) {
																	$trigger_mainclaim_transaction_update = true;
																	$claim_type_id = 15;
																	$payment_after_closed_sql = ", payment_after_closed = 1";
																}
															}

															$s_sql = "INSERT INTO collecting_company_cases_claim_lines SET
															id=NULL,
															moduleID = ?,
															created = now(),
															createdBy= ?,
															collecting_company_case_id = ?,
															name= ?,
															date = '".$o_main->db->escape_str(date("Y-m-d", strtotime($local_transaction['date'])))."',
															claim_type='".$o_main->db->escape_str($claim_type_id)."',
															amount= '".$o_main->db->escape_str($local_transaction['amount'])."'".$payment_after_closed_sql;
															$o_query = $o_main->db->query($s_sql, array($moduleID, 'import', $parent_company_transaction['collecting_company_case_id'], $formText_DirectPaymentToCreditor_output." ".date("d.m.Y", strtotime($local_transaction['date']))));
															if($o_query) {
																$claimline_id = $o_main->db->insert_id();
															}															
															
															if($claimline_id > 0) {
																if($trigger_mainclaim_transaction_update){																	
																	$s_sql = "UPDATE cs_mainbook_transaction SET used_as_settlement_payment = ? WHERE id = ?";
																	$o_query = $o_main->db->query($s_sql, array($claimline_id, $mainclaim_to_creditor_transaction['id']));
																}
																$s_sql = "UPDATE creditor_transactions SET company_claimline_id = ? WHERE id = '".$o_main->db->escape_str($local_transaction['id'])."'";
																$o_query = $o_main->db->query($s_sql, array($claimline_id));
															}
														}
													}
												}
											} else if($local_transaction['system_type'] == "CreditnoteCustomer") {
												$sql = "SELECT * FROM creditor_transactions WHERE link_id = ? AND creditor_id = ? AND collecting_company_case_id > 0";
							                    $o_query = $o_main->db->query($sql, array($local_transaction['link_id'], $creditorData['id']));
							                    $parent_company_transaction = $o_query ? $o_query->row_array() : array();
												if($parent_company_transaction) {
													$s_sql = "SELECT * FROM collecting_company_cases WHERE id = ?";
													$o_query = $o_main->db->query($s_sql, array($parent_company_transaction['collecting_company_case_id']));
													$companyCase = ($o_query ? $o_query->row_array() : array());
													if(strtotime($local_transaction['created']) >= strtotime(date("Y-m-d", strtotime($companyCase['created'])))) {
														if(intval($local_transaction['company_claimline_id']) == 0){
															$payment_after_closed_sql = ", payment_after_closed = 0";
															if($companyCase['case_closed_date'] != "0000-00-00" && $companyCase['case_closed_date'] != ""){
																$payment_after_closed_sql = ", payment_after_closed = 1";
															}
															$s_sql = "INSERT INTO collecting_company_cases_claim_lines SET
															id=NULL,
															moduleID = ?,
															created = now(),
															createdBy= ?,
															collecting_company_case_id = ?,
															name= ?,
															date = '".$o_main->db->escape_str(date("Y-m-d", strtotime($local_transaction['date'])))."',
												            claim_type='".$o_main->db->escape_str("16")."',
															amount= '".$o_main->db->escape_str($local_transaction['amount'])."'".$payment_after_closed_sql;
															$o_query = $o_main->db->query($s_sql, array($moduleID, 'import', $parent_company_transaction['collecting_company_case_id'], $formText_CreditInvoice_output." ".$local_transaction['invoice_nr']));
															if($o_query) {
																$claimline_id = $o_main->db->insert_id();
																if($claimline_id > 0) {
																	$s_sql = "UPDATE creditor_transactions SET company_claimline_id = ? WHERE id = '".$o_main->db->escape_str($local_transaction['id'])."'";
																	$o_query = $o_main->db->query($s_sql, array($claimline_id));
																}
															}
														}
													}
												}
											}

											$case_to_check = "";
						                    if($local_transaction['collectingcase_id'] > 0){
						                        $case_to_check= $local_transaction['collectingcase_id'];
						                    } else {
							                    $sql = "SELECT * FROM creditor_transactions WHERE invoice_nr = ? AND creditor_id = ? AND collectingcase_id > 0";
							                    $o_query = $o_main->db->query($sql, array($local_transaction['invoice_nr'], $creditorData['id']));
							                    $parent_transaction = $o_query ? $o_query->row_array() : array();
												if($parent_transaction) {
						                            $case_to_check = $parent_transaction['collectingcase_id'];
												}
											}

					                        $fullyPaid = false;

					                        $s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
					                        $o_query = $o_main->db->query($s_sql, array($case_to_check));
					                        $case = $o_query ? $o_query->row_array() : array();
											if($case) {
												if(!in_array($case['id'], $cases_checked)) {
							                        $s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND collectingcase_id = ? AND creditor_id = ? ORDER BY created DESC";
							                        $o_query = $o_main->db->query($s_sql, array($case['id'], $case['creditor_id']));
							                        $invoice = ($o_query ? $o_query->row_array() : array());

													$currencyName = "";
													if($invoice['currency'] == 'LOCAL') {
														$currencyName = trim($creditorData['default_currency']);
													} else {
														$currencyName = trim($invoice['currency']);
													}

							                        $toBePaid = $invoice['collecting_case_original_claim'];

													$s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') AND link_id = ? AND creditor_id = ?";
													$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
													$invoice_payments = ($o_query ? $o_query->result_array() : array());

													$total_transaction_payments = $invoice_payments;

													$s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND link_id = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%\_%' ORDER BY created DESC";
													$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
													$claim_transactions = ($o_query ? $o_query->result_array() : array());
							                        // foreach($claim_transactions as $transaction_fee) {
							                        //     if(!$transaction_fee['open']) {
							                        //         $s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') AND link_id = ? AND creditor_id = ?";
							                        //         $o_query = $o_main->db->query($s_sql, array($transaction_fee['link_id'], $transaction_fee['creditor_id']));
							                        //         $fee_payments = ($o_query ? $o_query->result_array() : array());
													//
													// 		foreach($fee_payments as $fee_payment) {
													// 			$inOtherTransaction = false;
													// 			foreach($invoice_payments as $transaction_payment) {
													// 				if($transaction_payment['id'] == $fee_payment['id']){
													// 					$inOtherTransaction = true;
													// 				}
													// 			}
													// 			if(!$inOtherTransaction){
													// 				$total_transaction_payments[]=$fee_payment;
													// 			}
													// 		}
							                        //     }
							                        // }

							                        $payments = 0;
							                        foreach($total_transaction_payments as $invoice_payment) {
							                            $payments += $invoice_payment['amount'];
							                        }
							                        foreach($claim_transactions as $claim_transaction) {
							                            $toBePaid += $claim_transaction['amount'];
							                        }
							                        if(($toBePaid + $payments) <= 0){
							                            $fullyPaid = true;
							                        }
													$creditedAmount = 0;
													if(count($invoice_payments) > 0){
														$validForRest = false;
														if($invoice['open']){
															foreach($invoice_payments as $invoice_payment){
																if($invoice_payment['system_type'] == "Payment"){
																	$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE case_id='".$o_main->db->escape_str($case['id'])."' AND rest_note = '1' ORDER BY created DESC";											
																	$o_query = $o_main->db->query($s_sql);											
																	$last_rest_letter = $o_query ? $o_query->row_array() : array();

																	$date_to_compare = $case['created'];
																	if($last_rest_letter) {
																		$date_to_compare = $last_rest_letter['created'];
																	}

																	if(date("d.m.Y", strtotime($invoice_payment['date'])) == date("d.m.Y", strtotime($date_to_compare))) {
																		if(strtotime($invoice_payment['created']) > strtotime($date_to_compare)) {
																			$validForRest = true;
																		}
																	} else {
																		if(strtotime($invoice_payment['date']) > strtotime($date_to_compare)) {
																			$validForRest = true;
																		}
																	}
																}
																if($invoice_payment['system_type'] == "CreditnoteCustomer") {
																	$creditedAmount += $invoice_payment['amount'];
																}
															}
															if($validForRest){
																$leftToBePaid = $toBePaid + $payments;
																if($leftToBePaid >= $reminderRestNoteMinimumAmount){
																	if($currencyName != ""){
																		//Send note
																		if(!in_array($case_to_check, $createRestForCases)){
																			$createRestForCases[] = $case_to_check;
																		}
																	} else {
																		echo $formText_CreditorMissingCurrency_output." ".$creditorData['companyname']." </br>";
																	}
																}
															}
														}
													}
													if($invoice['open'] == 0) {
														$totalPayments = 0;
														foreach($invoice_payments as $invoice_payment) {
															$totalPayments += $invoice_payment['amount'];
														}
														$totalFeeCharged = 0;
														foreach($claim_transactions as $claim_transaction){
															$totalFeeCharged += $claim_transaction['amount'];
														}
														$feeAmount = ($invoice['amount'] + $totalPayments)*(-1);

														if($feeAmount < 0) {
															$feeAmount = 0;
														} else if($feeAmount > $totalFeeCharged) {
															$feeAmount = $totalFeeCharged;
														}
														$s_sql = "UPDATE collecting_cases SET stopped_date = NOW(), fee_income='".$o_main->db->escape_str($feeAmount)."' WHERE id = '".$o_main->db->escape_str($case_to_check)."'";
							                            $o_query = $o_main->db->query($s_sql);
													} else {
														$s_sql = "UPDATE collecting_cases SET stopped_date = '0000-00-00' WHERE id = '".$o_main->db->escape_str($case_to_check)."'";
							                            $o_query = $o_main->db->query($s_sql);
													}
							                        // if($fullyPaid) {
													// 	$fullyCredited = false;
													// 	if($invoice['amount'] + $creditedAmount <= 0) {
													// 		$fullyCredited = true;
													// 	}
													// 	$subStatus = 1;
													// 	if($fullyCredited) {
													// 		$subStatus = 4;
													// 	}
							                        //     $s_sql = "UPDATE collecting_cases SET status = '2', sub_status = '".$subStatus."', stopped_date = NOW() WHERE id = '".$o_main->db->escape_str($case_to_check)."'";
							                        //     $o_query = $o_main->db->query($s_sql);
							                        // }
													$cases_checked[] = $case['id'];
												}
											}


											$s_sql = "UPDATE creditor_transactions SET not_processed = 0 WHERE id = '".$o_main->db->escape_str($local_transaction['id'])."'";
											$o_query = $o_main->db->query($s_sql);
					                    }
										$createRestForCasesUpdated = array();
									    foreach($createRestForCases as $caseToGenerate) {
											
									        $s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
									        $o_query = $o_main->db->query($s_sql, array($caseToGenerate));
									        $caseData = ($o_query ? $o_query->row_array() : array());

											$s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND collectingcase_id = ? AND creditor_id = ? ORDER BY created DESC";
											$o_query = $o_main->db->query($s_sql, array($caseData['id'], $caseData['creditor_id']));
											$invoice = ($o_query ? $o_query->row_array() : array());

											$noFeeError3 = true;
											$s_sql = "SELECT * FROM creditor_transactions WHERE system_type = 'InvoiceCustomer'  AND link_id = ? AND creditor_id = ? AND open = 1 AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%\_%'";
											$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
											$fee_transactions = $o_query ? $o_query->result_array() : array();
											if(count($fee_transactions) > 0) {
												$noFeeError3 = false;
											}
											$noFeeError3count = 0;
											$calleble_count = 0;
											foreach($fee_transactions as $fee_transaction) {

												$commentArray = explode("_",$fee_transaction['comment']);
												if($commentArray[2] == "interest"){
													$transactionType = "interest";
												} else if($commentArray[2] == "reminderFee"){
													$transactionType = "reminderFee";
												} else if($commentArray[0] == "Rente"){
													$transactionType = "interest";
												} else {
													$transactionType = "reminderFee";
												}
												if($transactionType == "interest"){														
													$currencyName = "";
													$invoiceDifferentCurrency = false;
													if($fee_transaction['currency'] == 'LOCAL') {
														$currencyName = trim($creditorData['default_currency']);
													} else {
														$currencyName = trim($fee_transaction['currency']);
														$invoiceDifferentCurrency = true;
													}		
													$currency_rate = 1;
													if($currencyName != "NOK") {
														$currency_rate = $fee_transaction['currency_rate'];
														if($currency_rate == 1){
															$error_with_currency = true;														
															foreach($currencyRates as $currencyRate) {
																if($currencyRate['symbol'] == $currencyName) {
																	$currency_rate = $currencyRate['rate'];
																	$error_with_currency = false;
																	break;
																}
															}
														}
													}
													$calleble_count++;
													$hook_params = array (
														'transaction_id' => $fee_transaction['id'],
														'amount'=>$fee_transaction['amount']*(-1),
														'dueDate'=>$dueDate,
														'text'=>$commentArray[0],
														'type'=>$transactionType,
														'accountNo'=>$commentArray[1],
														'close'=> 1
													);
													if($invoiceDifferentCurrency) {
														$hook_params['currency'] = $currencyName;
														$hook_params['currency_rate'] = $currency_rate;
														$hook_params['currency_unit'] = 1;
													}

													$hook_file = __DIR__ . '/../../../../Integration24SevenOffice/hooks/insert_transaction.php';
													if (file_exists($hook_file)) {
														include $hook_file;
														if (is_callable($run_hook)) {
															$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?";
															$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'reset interest started', $creditor_syncing_id));

															$connect_tries = 0;
															do {
																$connect_tries++;
																$hook_result = $run_hook($hook_params);
																if($hook_result['result']){
																	break;
																}
															} while($connect_tries < 11);
															$connect_tries--;
															if($hook_result['result']) {
																$noFeeError3count++;
																$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?, number_of_tries = ?";
																$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'reset interest finished', $creditor_syncing_id, $connect_tries));
															} else {
																// var_dump("deleteError".$hook_result['error']);
																$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?, number_of_tries = ?";
																$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'reset interest failed: '.json_encode($hook_result['error']), $creditor_syncing_id, $connect_tries));
															}
														}
													}
												}
											}
											if($calleble_count > 0){
												//new interest generation
												$without_fee = 0;
												$s_sql = "DELETE FROM collecting_cases_interest_calculation WHERE collecting_case_id = ? ";
												$o_query = $o_main->db->query($s_sql, array($caseData['id']));

												$currentClaimInterest = 0;
												$interestArray = calculate_interest($invoice, $caseData);
												$totalInterest = 0;
												foreach($interestArray as $interest) {
													$interestRate = $interest['rate'];
													$interestAmount = $interest['amount'];
													$interestFrom = date("Y-m-d", strtotime($interest['dateFrom']));
													$interestTo = date("Y-m-d", strtotime($interest['dateTo']));

													$s_sql = "INSERT INTO collecting_cases_interest_calculation SET created = NOW(), date_from = '".$o_main->db->escape_str($interestFrom)."',
													date_to = '".$o_main->db->escape_str($interestTo)."', amount = '".$o_main->db->escape_str($interestAmount)."', rate = '".$o_main->db->escape_str($interestRate)."', collecting_case_id = '".$o_main->db->escape_str($caseData['id'])."'";
													$o_query = $o_main->db->query($s_sql, array());
													$totalInterest += $interestAmount;
												}
												if($totalInterest > 0) {
													$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE id = ?";
													$o_query = $o_main->db->query($s_sql, array($caseData['collecting_cases_process_step_id']));
													$step = ($o_query ? $o_query->row_array() : array());
													
													$currencyName = "";
													$invoiceDifferentCurrency = false;
													if($invoice['currency'] == 'LOCAL') {
														$currencyName = trim($creditorData['default_currency']);
													} else {
														$currencyName = trim($invoice['currency']);
														$invoiceDifferentCurrency = true;
													}		
													$currency_rate = 1;
													if($currencyName != "NOK") {
														$error_with_currency = true;														
														foreach($currencyRates as $currencyRate) {
															if($currencyRate['symbol'] == $currencyName) {
																$currency_rate = $currencyRate['rate'];
																$error_with_currency = false;
																break;
															}
														}
													}
													$hook_params = array (
														'transaction_id' => $invoice['id'],
														'amount'=>$totalInterest,
														'dueDate'=>$caseData['due_date'],
														'text'=>$formText_Interest_output,
														'type'=>'interest',
														'type_no'=>$type_no,
														'accountNo'=>$interest_bookaccount,
														'username'=> $username,
														'caseId'=>$caseData['id'],
														'stepId'=>$step['id']
													);
													if($invoiceDifferentCurrency) {
														$hook_params['currency'] = $currencyName;
														$hook_params['currency_rate'] = $currency_rate;
														$hook_params['currency_unit'] = 1;
													}
													$newInterestAdded = false;
													$hook_file = __DIR__ . '/../../../../Integration24SevenOffice/hooks/insert_transaction.php';
													if (file_exists($hook_file)) {
														include $hook_file;
														if (is_callable($run_hook)) {
															$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?";
															$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'new interest started', $creditor_syncing_id));

															$connect_tries = 0;
															do {
																$connect_tries++;
																$hook_result = $run_hook($hook_params);
																if($hook_result['result']){
																	break;
																}
															} while($connect_tries < 11);
															$connect_tries--;
															if($hook_result['result']){
																$newInterestAdded = true;
																$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?, number_of_tries = ?";
																$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'new interest finished', $creditor_syncing_id, $connect_tries));
															} else {
																// var_dump("deleteError".$hook_result['error']);
																$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 1, creditor_syncing_id = ?, number_of_tries = ?";
																$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'new interest failed: '.json_encode($hook_result['error']), $creditor_syncing_id, $connect_tries));
															}
														}
													}
												}
											}

											if($noFeeError3count == $calleble_count){
												if($newInterestAdded){
													$triggerSync = true;
												}
												$createRestForCasesUpdated[] = $caseToGenerate;
											}
										}
										if($triggerSync){
											$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
											   $o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step rest interest update started - syncing after interests were recreated', $creditor_syncing_id));
											$connect_tries = 0;
											$connectedSuccessfully = false;
											
											$transactionData = array();
											$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
											$o_query = $o_main->db->query($s_sql, array($creditorData['id']));
											$updatedCreditorData = ($o_query ? $o_query->row_array() : array());
		
											$changedAfterDate = isset($updatedCreditorData['lastImportedDateTimestamp']) ? $updatedCreditorData['lastImportedDateTimestamp'] : "";
											if($changedAfterDate != null && $changedAfterDate != ""){
												$changedAfterDate = number_format($changedAfterDate+0.001, 3, ".", "");
												$now = DateTime::createFromFormat('U.u', $changedAfterDate);
												if($now){
													$transactionData['date_start'] = $now->format("Y-m-d\TH:i:s.u");
												}
											}
											$transactionData['DateSearchParameters'] = 'DateChangedUTC';
											$transactionData['date_end'] = date('Y-m-t', time() + 60*60*24);
											$transactionData['bookaccountStart'] = 1500;
											$transactionData['bookaccountEnd'] = 1529;
											$transactionData['ShowOpenEntries'] = null;
											do {
												$connect_tries++;
												$invoicesTransactions = $api->get_transactions($transactionData);
												if($invoicesTransactions !== null){
													$connectedSuccessfully = true;
													break;
												}
											} while($connect_tries < 11);
											$connect_tries--;
											list($totalImportedSuccessfully_links, $lastImportedDate_links, $cases_to_check_links, $totalSum_links) = sync_transactions($invoicesTransactions, $creditorData, $moduleID, $api, false);
											if($totalImportedSuccessfully_links > 0) {
												if($lastImportedDate_links != ""){
													$dateTime = new DateTime($lastImportedDate_links);
													$timestamp = $dateTime->format("U");
													$microseconds = $dateTime->format("u");
													$sql = "UPDATE creditor SET lastImportedDateTimestamp = ? WHERE id = ?";
													$o_query = $o_main->db->query($sql, array($timestamp.".".$microseconds, $creditorData['id']));
												}
											}
											$triggerSync = false;

											$sql = "SELECT * FROM creditor_transactions
											WHERE creditor_id = ? AND open = 1 AND link_id is null AND comment is not null AND date_changed >= '".date("Y-m-d", strtotime("-10 days"))."'";
											$o_query = $o_main->db->query($sql, array($creditorData['id']));
											$local_transactions = $o_query ? $o_query->result_array() : array();
											$connectedSuccessfully = true;
											$total_transaction_to_link = 0;
											$current_transaction_success = 0;
											foreach($local_transactions as $local_transaction) {
												if($local_transaction['comment'] != ""){
													$sql = "SELECT * FROM creditor_transactions WHERE transaction_id = ? AND creditor_id = ? AND open = 1";
													$o_query = $o_main->db->query($sql, array($local_transaction['comment'], $creditorData['id']));
													$parent_transaction = $o_query ? $o_query->row_array() : array();
													if($parent_transaction){
														$connect_tries = 0;
														$total_transaction_to_link++;
														do {
															$connect_tries++;
															$linkArray = array();
															$linkArray['transaction1_id'] = $parent_transaction['transaction_id'];
															$linkArray['transaction2_id'] = $local_transaction['transaction_id'];
															$links_created_result = $api->create_link($linkArray);
															if($links_created_result){
																$current_transaction_success++;
																break;
															}
														} while($connect_tries < 11);
														$connect_tries--;
														$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?, number_of_tries = ?";
														$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'Transaction links result '.json_encode($links_created_result).', data: '.json_encode($linkArray), $creditor_syncing_id, $connect_tries));
													}
												}
											}

											$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
											   $o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step rest interest update started - linking after interests were recreated', $creditor_syncing_id));
											$connect_tries = 0;
											$connectedSuccessfully = false;
											
											$transactionData = array();
											$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
											$o_query = $o_main->db->query($s_sql, array($creditorData['id']));
											$updatedCreditorData = ($o_query ? $o_query->row_array() : array());

											$changedAfterDate = isset($updatedCreditorData['lastImportedDateTimestamp']) ? $updatedCreditorData['lastImportedDateTimestamp'] : "";
											if($changedAfterDate != null && $changedAfterDate != ""){
												$changedAfterDate = number_format($changedAfterDate+0.001, 3, ".", "");
												$now = DateTime::createFromFormat('U.u', $changedAfterDate);
												if($now){
													$transactionData['date_start'] = $now->format("Y-m-d\TH:i:s.u");
												}
											}
											$transactionData['DateSearchParameters'] = 'DateChangedUTC';
											$transactionData['date_end'] = date('Y-m-t', time() + 60*60*24);
											$transactionData['bookaccountStart'] = 1500;
											$transactionData['bookaccountEnd'] = 1529;
											$transactionData['ShowOpenEntries'] = null;
											do {
												$connect_tries++;
												$invoicesTransactions = $api->get_transactions($transactionData);
												if($invoicesTransactions !== null){
													$connectedSuccessfully = true;
													break;
												}
											} while($connect_tries < 11);
											$connect_tries--;
											list($totalImportedSuccessfully_links, $lastImportedDate_links, $cases_to_check_links, $totalSum_links) = sync_transactions($invoicesTransactions, $creditorData, $moduleID, $api, false);
											if($totalImportedSuccessfully_links > 0) {
												if($lastImportedDate_links != ""){
													$dateTime = new DateTime($lastImportedDate_links);
													$timestamp = $dateTime->format("U");
													$microseconds = $dateTime->format("u");
													$sql = "UPDATE creditor SET lastImportedDateTimestamp = ? WHERE id = ?";
													$o_query = $o_main->db->query($sql, array($timestamp.".".$microseconds, $creditorData['id']));
												}
											}
											$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?, number_of_tries = ?, number_of_transactions = ?";
											$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step rest interest update endede - linking after interests were recreated', $creditor_syncing_id, $connect_tries, count($invoicesTransactions)));
										}
										$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
										$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step 8 ended - main process local transactions', $creditor_syncing_id));

									    do{
											$code = generateRandomString(10);
											$code_check = null;
											$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE print_batch_code = ?";
											$o_query = $o_main->db->query($s_sql, array($code));
											if($o_query){
												$code_check = $o_query->row_array();
											}
										} while($code_check != null);

										$restCount = count($createRestForCases);

								        $s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?, number_of_restclaims = ?";
								        $o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step 9 started - rest', $creditor_syncing_id, $restCount));
										$resclaim_log_id = $o_main->db->insert_id();
										$successfullyCreatedLetters = 0;
									    foreach($createRestForCasesUpdated as $caseToGenerate) {
									        $s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
									        $o_query = $o_main->db->query($s_sql, array($caseToGenerate));
									        $caseData = ($o_query ? $o_query->row_array() : array());

											$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE case_id='".$o_main->db->escape_str($caseData['id'])."'
											AND step_id = '".$o_main->db->escape_str($caseData['collecting_cases_process_step_id'])."' AND rest_note = '1'";											
											$o_query = $o_main->db->query($s_sql);											
											$rest_letter = $o_query ? $o_query->row_array() : array();
											if(!$rest_letter) {
										        $s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
										        $o_query = $o_main->db->query($s_sql, array($caseData['creditor_id']));
										        $creditor_in = ($o_query ? $o_query->row_array() : array());

										        $result = generate_pdf($caseToGenerate, 1);
										        if(count($result['errors']) > 0){
										            foreach($result['errors'] as $error){
										                echo $formText_LetterFailedToBeCreatedForCase_output." ".$caseToGenerate." ".$error."</br>";
										            }
										        } else {
										            $successfullyCreatedLetters++;
										            if($creditor_in['print_reminders'] == 0) {
										                if($result['item']['id'] > 0){
										                    $s_sql = "UPDATE collecting_cases_claim_letter SET updated = NOW(),  print_batch_code = ? WHERE id = ?";
										                    $o_query = $o_main->db->query($s_sql, array($code, $result['item']['id']));
										                    if($o_query) {
										                        $lettersForDownload[] = $result['item']['id'];
										                    }
										                }
										            }
										        }
											} else {
												$restCount--;
											}
									    }

										$s_sql = "UPDATE creditor_syncing_log SET number_of_restclaims = ? WHERE id = ?";
								        $o_query = $o_main->db->query($s_sql, array($restCount, $resclaim_log_id));

										$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?, number_of_restclaims = ?";
									   	$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step 9 ended - rest', $creditor_syncing_id, $successfullyCreatedLetters));
									} else {
										echo $formText_FailedToConnect_output."<br/>";
										$connection_error = true;
										$failedMsg = "Failed To Connect to sync transactions . Reached maximum number of tries";
									}
								} else {
									echo $formText_FailedToConnect_output."<br/>";
									$connection_error = true;
									$failedMsg = "Failed To Connect to reset fees . Reached maximum number of tries";
								}
							} else {
								echo $formText_FailedToConnect_output."<br/>";
								$connection_error = true;
								$failedMsg = "Failed To Connect Creating links . Reached maximum number of tries";
							}
		                }
		                if(!$fromProcessCases) {
		                    echo $formText_TotalTransactions_output.": ".$totalImportedSuccessfully."<br/>";
		                    echo $formText_TotalSum_output.": ".$totalSum."<br/><br/>";
		                }

		                $sql = "UPDATE creditor SET lastImportedDate = ?, sync_status = 0 WHERE id = ?";
		                $o_query = $o_main->db->query($sql, array(date("Y-m-d H:i:s"), $creditorData['id']));

						$s_sql = "UPDATE creditor SET autosyncing_not_working_date = '0000-00-00' WHERE id = ?";
						$o_query = $o_main->db->query($s_sql, array($creditorData['id']));
					} else {
						echo $formText_FailedToConnect_output."<br/>";
						$connection_error = true;
						$failedMsg = "Failed To Connect while trying to get transactions. Reached maximum number of tries";
					}
	            } else {
					$s_sql = "UPDATE creditor SET sync_status = 0 WHERE id = ?";
					$o_query = $o_main->db->query($s_sql, array($creditorData['id']));

					$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
					$o_query = $o_main->db->query($s_sql, array($creditorData['id'], $api->error, $creditor_syncing_id));
					echo $formText_FailedToConnect_output."<br/>";
					$connection_error = true;
					$failedMsg = "Failed To Connect to Integration. ".$api->error;
				}
			} catch(Exception $e) {
				$s_sql = "UPDATE creditor SET sync_status = 0 WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($creditorData['id']));

				$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
				$o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'Error '.$e->getMessage(), $creditor_syncing_id));
				echo $formText_FailedToConnect_output."<br/>";
				$connection_error = true;
				$failedMsg = "Critical error with exception. ".$e->getMessage();
			}
        // }

		if($failedMsg == "") {
			$s_sql = "UPDATE creditor_syncing SET finished = NOW() WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($creditor_syncing_id));
		} else {
			$s_sql = "UPDATE creditor_syncing SET failed = NOW(), failed_message = ? WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($failedMsg, $creditor_syncing_id));
		}

		//trigger reordering 		
		process_open_cases_for_tabs($creditorData['id']);
    }
}
?>
