<?php
function sync_local_customers($o_main, $customers, $creditorData){
	foreach($customers as $customer) {
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
			$sql = "INSERT INTO customer SET createdBy = 'import', created=NOW(), content_status=0, creditor_id = ?, creditor_customer_id = ?, name = ?, phone = ?, fax = ?, invoiceEmail = ?, publicRegisterId = ?, paStreet = ?,  paPostalNumber = ?, paCity = ?, paCountry = ?, vaStreet = ?,  vaPostalNumber = ?, vaCity = ?, vaCountry = ?";
			$o_query = $o_main->db->query($sql, array($creditorData['id'], $external_id, $name, $phone, $fax, $email, $regNr, $postAddresses['Street'], $postAddresses['PostalCode'],$postAddresses['PostalArea'],$postAddresses['Country'],
			$visitAddresses['Street'], $visitAddresses['PostalCode'],$visitAddresses['PostalArea'],$visitAddresses['Country']));
			if($o_query) {
				$customer_id = $o_main->db->insert_id();
			}
		} else {
			$sql = "UPDATE customer SET updatedBy = 'import', updated=NOW(), content_status=0, name = ?, phone = ?, fax = ?, invoiceEmail = ?, publicRegisterId = ?, paStreet = ?,  paPostalNumber = ?, paCity = ?, paCountry = ?, vaStreet = ?,  vaPostalNumber = ?, vaCity = ?, vaCountry = ? WHERE id = ?";
			$o_query = $o_main->db->query($sql, array($name, $phone, $fax, $email, $regNr, $postAddresses['Street'], $postAddresses['PostalCode'],$postAddresses['PostalArea'],$postAddresses['Country'],
			$visitAddresses['Street'], $visitAddresses['PostalCode'],$visitAddresses['PostalArea'],$visitAddresses['Country'], $customerExist['id']));
			if($o_query) {
				$customer_id = $customerExist['id'];
			}
		}
	}	
}
// error_reporting(E_ALL);
// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);

	if(isset($_POST['checkCreditorCustomers'])) {
		
		// $sql = "SELECT cr.*, cr.companyname as creditorName FROM creditor cr
		// WHERE cr.integration_module <> '' AND cr.sync_from_accounting = 1 AND cr.loc_customer_difference is null 
		// AND IFNULL(cr.onboarding_incomplete, 0) = 0 ORDER BY id ASC LIMIT 50";
		$sql = "SELECT cr.*, cr.companyname as creditorName FROM creditor cr
		WHERE cr.integration_module <> '' AND cr.sync_from_accounting = 1 AND 
		IFNULL(cr.customer_sync_start_time, '0000-00-00') = '0000-00-00' AND cr.id = 2921
		ORDER BY id ASC LIMIT 2";
		$o_query = $o_main->db->query($sql);
		$creditors = $o_query ? $o_query->result_array() : array();
		$updated_count =0;
		foreach($creditors as $creditorData){
			require_once __DIR__ . '/../../../../'.$creditorData['integration_module'].'/internal_api/load.php';
			if($creditorData['integration_module'] == "Integration24SevenOffice"){
				$sql = "SELECT * FROM customer WHERE creditor_id = ?";
				$o_query = $o_main->db->query($sql, array($creditorData['id']));
				$local_customer_count = $o_query ? $o_query->num_rows() : 0;
				
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
				$api = new Integration24SevenOffice($v_config);
				$difference = 0;
				if($api->error == "") {
					$dataCustomer = array();
					$initial_sync = false;
					if($creditorData['customer_sync_start_time'] != "" && $creditorData['customer_sync_start_time'] != "0000-00-00") {
						$dataCustomer['changedAfter'] = date("Y-m-d", strtotime($creditorData['customer_sync_start_time']));
					} else {
						$initial_sync = true;
					}
					$dataCustomer['page'] = 1;
					$response_customer_sync = $api->get_customer_list_for_sync($dataCustomer);
					$totalPages = $response_customer_sync['GetCompanySyncListResult']['TotalPages'];
					$totalItems = $response_customer_sync['GetCompanySyncListResult']['TotalItems'];
					$currentPage = $response_customer_sync['GetCompanySyncListResult']['CurrentPage'];
					$itemsPerPage = $response_customer_sync['GetCompanySyncListResult']['ItemsPerPage'];
					$items = $response_customer_sync['GetCompanySyncListResult']['Items']['SyncCompany'];
					if($totalPages > 0){							
						if($initial_sync) {
							$s_sql = "UPDATE customer SET content_status = 2 WHERE creditor_id = '".$o_main->db->escape_str($creditorData['id'])."'";
							$o_query = $o_main->db->query($s_sql);
						}
						for($x=1; $x<= $totalPages; $x++){
							if($x > 1){
								$dataCustomer['page'] = $x;
								$response_customer_sync = $api->get_customer_list_for_sync($dataCustomer);
								$totalPages = $response_customer_sync['GetCompanySyncListResult']['TotalPages'];
								$totalItems = $response_customer_sync['GetCompanySyncListResult']['TotalItems'];
								$currentPage = $response_customer_sync['GetCompanySyncListResult']['CurrentPage'];
								$itemsPerPage = $response_customer_sync['GetCompanySyncListResult']['ItemsPerPage'];
								$items = $response_customer_sync['GetCompanySyncListResult']['Items']['SyncCompany'];
							}

							$customerIds = array();
							foreach($items as $item) {
								if($item['Active']) {
									$customerIds[] = $item['CompanyId'];
								} else {
									$sql = "UPDATE customer SET content_status = 2 WHERE creditor_customer_id = ? AND creditor_id = ?";
									$o_query = $o_main->db->query($sql, array($item['CompanyId'], $creditorData['id']));
								}
							}
							if(count($customerIds) > 0) {
								$dataCustomer['customerIds'] = $customerIds;
								$response_customer = $api->get_customer_list($dataCustomer);
								$customer_list = $response_customer['GetCompaniesResult']['Company'];
								
								sync_local_customers($o_main, $customer_list, $creditorData);
							}
							$items = array();	
						}
					}
									
					$s_sql = "UPDATE creditor SET customer_sync_start_time = CURDATE()  WHERE id = '".$o_main->db->escape_str($creditorData['id'])."'";
					$o_query = $o_main->db->query($s_sql);
				} else {
					echo $formText_ErrorConnectingToIntegration_output."<br/>";
				}
			}
		}
		echo $updated_count;

	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">
			<input type="submit" name="checkCreditorCustomers" value="Check creditor customers">
		</div>
	</form>
</div>
