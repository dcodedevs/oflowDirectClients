<?php
$creditor_id = $_POST['creditor_id'];
$sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($sql, array($creditor_id));
$creditorData = $o_query ? $o_query->row_array() : array();
if($creditorData){
	if(!function_exists("customer_local_update")){
		function customer_local_update($customer_list, $creditorData, $updateOnly = false) {
			global $o_main;
			foreach($customer_list as $customer) {
				$regNr = $customer['OrganizationNumber'];
				$external_id = $customer['Id'];
				$name = $customer['Name'];
				$postAddresses = $customer['Addresses']['Post'];
				$visitAddresses = $customer['Addresses']['Visit'];
				$invoiceAddresses = $customer['Addresses']['Invoice'];
				$phone = $customer['PhoneNumbers']['Work']['Value'];
				$fax = $customer['PhoneNumbers']['Fax']['Value'];
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
						$sql = "INSERT INTO customer SET createdBy = 'import', created=NOW(), creditor_id = ?, creditor_customer_id = ?, name = ?, phone = ?, fax = ?, invoiceEmail = ?, publicRegisterId = ?, paStreet = ?,  paPostalNumber = ?, paCity = ?, paCountry = ?, vaStreet = ?,  vaPostalNumber = ?, vaCity = ?, vaCountry = ?, customer_type_collect = ?";
						$o_query = $o_main->db->query($sql, array($creditorData['id'], $external_id, $name, $phone, $fax, $email, $regNr, $invoiceAddresses['Street'], $invoiceAddresses['PostalCode'],$invoiceAddresses['PostalArea'],$invoiceAddresses['Country'],
						$visitAddresses['Street'], $visitAddresses['PostalCode'],$visitAddresses['PostalArea'],$visitAddresses['Country'], $type));
						if($o_query) {
							$customer_id = $o_main->db->insert_id();
						}
					}
				} else {
					$sql = "UPDATE customer SET updatedBy = 'import', updated=NOW(), name = ?, phone = ?, fax = ?, invoiceEmail = ?, publicRegisterId = ?, paStreet = ?,  paPostalNumber = ?, paCity = ?, paCountry = ?, vaStreet = ?,  vaPostalNumber = ?, vaCity = ?, vaCountry = ?, customer_type_collect = ? WHERE id = ?";
					$o_query = $o_main->db->query($sql, array($name, $phone, $fax, $email, $regNr, $invoiceAddresses['Street'], $invoiceAddresses['PostalCode'],$invoiceAddresses['PostalArea'],$invoiceAddresses['Country'],
					$visitAddresses['Street'], $visitAddresses['PostalCode'],$visitAddresses['PostalArea'],$visitAddresses['Country'], $type, $customerExist['id']));
					if($o_query) {
						$customer_id = $customerExist['id'];
					}
				}
			}
		}
	}
	require_once __DIR__ . '/../../../'.$creditorData['integration_module'].'/internal_api/load.php';
	$v_config = array(
		'ownercompany_id' => 1,
		'identityId' => $creditorData['entity_id'],
		'creditorId' => $creditorData['id'],
		'o_main' => $o_main
	);
	$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE creditor_id = '".$o_main->db->escape_str($creditorData['id'])."' ORDER BY DESC";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && 0 < $o_query->num_rows())
	{
		$v_int_session = $o_query->row_array();
		$v_config['session_id'] = $v_int_session['session_id'];
	}
	$api = new Integration24SevenOffice($v_config);

	if($api->error == "") {
		$data['changedAfter'] = date("Y-m-d", strtotime("01.01.2012"));
		$data['ShowOpenEntries'] = 1;
		
		$invoicesTransactions = array();
		$loopCounter = 0;
		do {
			$dateStart = $data['changedAfter'];
			$dateEnd = date('Y-m-t', strtotime("+1 year", strtotime($dateStart)));

			$transactionData = array();
			$transactionData['DateSearchParameters'] = 'DateChangedUTC';
			$transactionData['date_start'] = $dateStart;
			$transactionData['date_end'] = $dateEnd;
			$transactionData['bookaccountStart'] = 1500;
			$transactionData['bookaccountEnd'] = 1529;
			$transactionData['ShowOpenEntries'] = $data['ShowOpenEntries'];

			$connectedSuccessfully = false;
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


	    $s_sql = "SELECT * FROM creditor_transactions  WHERE creditor_id = ? AND open = 1";
	    $o_query = $o_main->db->query($s_sql, array($creditorData['id']));
	    $localTransactions = ($o_query ? $o_query->result_array() : array());
		if(count($invoicesTransactions) > count($localTransactions)) {
			$invoice_local_ids = array();
			foreach($localTransactions as $localTransaction) {
				$invoice_local_ids[] = $localTransaction['transaction_id'];
			}
			$customerIds = array();
			foreach($invoicesTransactions as $invoicesTransaction) {
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
				if(!in_array($invoicesTransaction['id'], $invoice_local_ids)) {

					$sql = "INSERT INTO creditor_transactions SET moduleID = ?, creditor_id = ?, external_customer_id = ?, amount = ?, date=?, due_date=?, createdBy = 'import', created=NOW(), link_id = ?, invoice_nr = ?, kid_number = ?, system_type = ?, open = ?, transaction_id = ?, account_nr = ?, transaction_nr = ?, vatcode = ?, hidden = ?, dimensions = ?, date_changed = ?, comment = ?, not_processed = 1";
					$o_query = $o_main->db->query($sql, array($moduleID, $creditorData['id'], $customerId, $invoicesTransaction['amount'],$invoicesTransaction['date'],$invoicesTransaction['dueDate'], $invoicesTransaction['linkId'], $invoicesTransaction['invoiceNr'], $invoicesTransaction['kidNumber'], $invoicesTransaction['systemType'], $invoicesTransaction['open'], $invoicesTransaction['id'], $invoicesTransaction['accountNr'], $invoicesTransaction['transactionNr'], $invoicesTransaction['vatCode'], $invoicesTransaction['hidden'], json_encode($invoicesTransaction['dimensions']), $invoicesTransaction['dateChanged'], $invoicesTransaction['comment']));
					if($o_query) {
						$transactionId = $o_main->db->insert_id();

						echo $transactionId . " successfully created<br/>";
					}
				}
			}
			// if(count($customerIds) > 0) {
			// 	$bigCustomerIdArray = array_chunk($customerIds,1000);
			// 	foreach($bigCustomerIdArray as $customerIds) {
			// 		$customer_id = "";
			// 		$dataCustomer = array();
			// 		$dataCustomer['customerIds'] = $customerIds;
			// 		$connect_tries = 0;
			// 		do {
			// 			$connect_tries++;
			// 			$response_customer = $api->get_customer_list($dataCustomer);
			// 			if($response_customer !== null){
			// 				break;
			// 			}
			// 		} while($connect_tries < 11);
			// 		$connect_tries--;

			// 		$customer_list = $response_customer['GetCompaniesResult']['Company'];
			// 		if(isset($customer_list['Id'])){
			// 			$customer_list = array($customer_list);
			// 		}
			// 		customer_local_update($customer_list, $creditorData);
			// 	}
			// }
		}

	} else {
		echo $api->error;
	}
}
?>
