<?php
$sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($sql, array($creditorId));
$creditorData = $o_query ? $o_query->row_array() : array();
if($creditorData) {
    require_once __DIR__ . '/../../../../'.$creditorData['integration_module'].'/internal_api/load.php';
    if($creditorData['integration_module'] == "Integration24SevenOffice"){
        // if($creditorData['entity_id'] == ""){
        //     echo $formText_NoEntityId_output;
        // } else {
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
            if($api->error == "") {
                // $changedAfterDate = isset($creditorData['last_create_case_date']) ? $creditorData['last_create_case_date'] : "";
                $dataCustomer['changedAfter'] = date("Y-m-d", strtotime("01.01.2010"));

                // if($changedAfterDate != "0000-00-00" && $changedAfterDate != null && $changedAfterDate != ""){
                //     $dataCustomer['changedAfter'] = date("Y-m-d", strtotime("-100 days", strtotime($changedAfterDate)));
                //     $lastImportedDate = $dataCustomer['changedAfter'];
                // }
                $customer_updated = 0;
                $response_customer = $api->get_customer_list($dataCustomer);
                $customer_list = $response_customer['GetCompaniesResult']['Company'];
                // echo count($customer_list);
                $updateOnly = true;
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
                                $customer_updated++;
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
                            $customer_updated++;
                            if($regNr != ""){
                                if(!$customerExist['organization_type_check'] && $customerExist['organization_type'] == ""){
                                    $getOrganizationType = true;
                                }
                            }
                        }
                    }
                    if($external_id > 0) {
                        // $s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
                        // $o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step mark transactios start  '.$external_id, $creditor_syncing_id));
                
                        // $s_sql = "UPDATE creditor_transactions SET to_be_reordered = 1 WHERE creditor_id = ? AND external_customer_id = ? AND open = 1";
                        // $o_query = $o_main->db->query($s_sql, array($creditorData['id'], $external_id));
                        
                        // $s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
                        // $o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step mark transactios end  '.$o_main->db->last_query(), $creditor_syncing_id));
                    }
                    if($getOrganizationType){
                        $organization_numbers[] = $regNr; 
                    }
                }
			

                // foreach($customer_list as $customer) {
                //     $regNr = $customer['OrganizationNumber'];
                //     $external_id = $customer['Id'];
                //     $name = $customer['Name'];
                //     $postAddresses = $customer['Addresses']['Post'];
                //     $visitAddresses = $customer['Addresses']['Visit'];
                //     $invoiceAddresses = $customer['Addresses']['Invoice'];
                //     $phone = $customer['PhoneNumbers']['Work']['Value'];
                //     $fax = $customer['PhoneNumbers']['Fax']['Value'];
                //     $invoice_language = $customer['InvoiceLanguage'];
    
                //     $type = 0;
                //     if($customer['Type'] == "Consumer") {
                //         $type = 1;
                //     }
                //     if(intval($creditorData['invoice_email_priority']) == 0) {
                //         $email = $customer['EmailAddresses']['Work']['Value'];
                //         $work_email = $customer['EmailAddresses']['Invoice']['Value'];
                //     } else if($creditorData['invoice_email_priority'] == 1) {
                //         $email = $customer['EmailAddresses']['Invoice']['Value'];
                //         $work_email = $customer['EmailAddresses']['Work']['Value'];
                //     }				
                //     $extra_language = 0;
                //     if(mb_strtolower($invoice_language) != 'no') {
                //         $extra_language = 1;
                //     }
    
                //     if($email == ""){
                //         $email = $work_email;
                //     }
                //     if($invoiceAddresses['Street'] == "" && $invoiceAddresses['PostalCode'] == "" && $invoiceAddresses['PostalArea'] == "") {
                //         $invoiceAddresses = $postAddresses;
                //     }
                //     if($invoiceAddresses['Street'] == "" && $invoiceAddresses['PostalCode'] == "" && $invoiceAddresses['PostalArea'] == "") {
                //         $invoiceAddresses = $visitAddresses;
                //     }
                    
                //     $sql = "SELECT * FROM customer WHERE customer.creditor_customer_id = ? AND customer.creditor_id = ?";
                //     $o_query = $o_main->db->query($sql, array($external_id, $creditorData['id']));
                //     $customerExist = $o_query ? $o_query->row_array() : array();
                //     if(!$customerExist) {
                //         if(!$updateOnly) {
                //             $sql = "INSERT INTO customer SET createdBy = 'import', created=NOW(), creditor_id = ?, creditor_customer_id = ?, name = ?, phone = ?, fax = ?, invoiceEmail = ?, publicRegisterId = ?, paStreet = ?,  paPostalNumber = ?, paCity = ?, paCountry = ?, vaStreet = ?,  vaPostalNumber = ?, vaCity = ?, vaCountry = ?, customer_type_collect = ?, integration_invoice_language=?";
                //             $o_query = $o_main->db->query($sql, array($creditorData['id'], $external_id, $name, $phone, $fax, $email, $regNr, $invoiceAddresses['Street'], $invoiceAddresses['PostalCode'],$invoiceAddresses['PostalArea'],$invoiceAddresses['Country'],
                //             $visitAddresses['Street'], $visitAddresses['PostalCode'],$visitAddresses['PostalArea'],$visitAddresses['Country'], $type, $extra_language));
                //             if($o_query) {
                //                 $customer_id = $o_main->db->insert_id();
                //                 $customer_updated++;
                //             }
                //         }
                //     } else {
                //         $sql = "UPDATE customer SET updatedBy = 'import', updated=NOW(), name = ?, phone = ?, fax = ?, invoiceEmail = ?, publicRegisterId = ?, paStreet = ?,  paPostalNumber = ?, paCity = ?, paCountry = ?, vaStreet = ?,  vaPostalNumber = ?, vaCity = ?, vaCountry = ?, customer_type_collect = ?, integration_invoice_language=? WHERE id = ?";
                //         $o_query = $o_main->db->query($sql, array($name, $phone, $fax, $email, $regNr, $invoiceAddresses['Street'], $invoiceAddresses['PostalCode'],$invoiceAddresses['PostalArea'],$invoiceAddresses['Country'],
                //         $visitAddresses['Street'], $visitAddresses['PostalCode'],$visitAddresses['PostalArea'],$visitAddresses['Country'], $type, $extra_language, $customerExist['id']));
                //         if($o_query) {
                //             $customer_id = $customerExist['id'];
                //             $customer_updated++;
                //         }
                //     }
                // }
                echo $customer_updated." ".$formText_CustomersUpdated_output;
            } else {
                echo $formText_ErrorConnectingToIntegration_output."<br/>";
            }
        // }
    }
}
?>
