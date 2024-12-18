<?php

// http://developer.24sevenoffice.com/

class Integration24SevenOffice {
    function __construct($config) {
        unset($_SESSION['ASP.NET_SessionId']);
        $this->ownercompany_id = isset($config['ownercompany_id']) ? $config['ownercompany_id'] : 0;
        $this->o_main = $config['o_main'];
        $this->identityId = isset($config['identityId']) ? $config['identityId'] : 0;
        $this->creditorId = isset($config['creditorId']) ? $config['creditorId'] : 0;
        $this->clientId = isset($config['clientId']) ? $config['clientId'] : 0;
        $this->supplierId = isset($config['supplierId']) ? $config['supplierId'] : 0;
        $this->previous = isset($config['previous']) ? $config['previous'] : '';
        $this->options = array ('trace' => true , 'encoding'=>' UTF-8');
		if($config['session_id'] == null) {
			$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE creditor_id = '".$this->o_main->db->escape_str($this->creditorId)."' AND IFNULL(DATE_ADD(IFNULL(failed,'0000-00-00'), INTERVAL 30 MINUTE), CURDATE()) < NOW() ORDER BY created DESC";
			$o_query = $this->o_main->db->query($s_sql);
			if($o_query && 0 < $o_query->num_rows())
			{
				$v_int_session = $o_query->row_array();
				$config['session_id'] = $v_int_session['session_id'];
			}
		}

		if(isset($config['session_id']) && '' != $config['session_id'])
		{
			$this->sessionId = $config['session_id'];
		}
		$s_sql = "SELECT * FROM creditor WHERE id = '".$this->o_main->db->escape_str($this->creditorId)."'";
		$o_query = $this->o_main->db->query($s_sql);
		if($o_query && 0 < $o_query->num_rows())
		{
			$creditor = $o_query->row_array();
			$this->creditor = $creditor;
			if(intval($this->clientId) == 0) {
				$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE session_id = '".$this->o_main->db->escape_str($this->sessionId)."'";
				$o_query = $this->o_main->db->query($s_sql);
				if($o_query && 0 < $o_query->num_rows())
				{
					$v_int_session = $o_query->row_array();
					$this->clientId = $v_int_session['client_id'];
				}
			}
		}
        if(isset($config['token']))
		{
			$this->authToken($config['token']);
		} else {
			$this->auth(isset($config['getIdentityIdByName']) ? $config['getIdentityIdByName'] : '');
		}
    }

    /**
     * API session handing
     * this auth() is taken from sample
     */


    function get_local_config() {
        if($this->creditorId > 0){
            require_once(__DIR__."/../../CreditorsOverview/output/includes/fnc_password_encrypt.php");
            $s_sql = "SELECT * FROM integration24sevenoffice";
            $o_query = $this->o_main->db->query($s_sql);
            $config = $o_query ? $o_query->row_array() : array();

            $sql = "SELECT * FROM creditor WHERE id = ?";
            $o_query = $this->o_main->db->query($sql, array($this->creditorId));
            $creditorData = $o_query ? $o_query->row_array() : array();
            $config['username'] = $creditorData['24sevenoffice_username'];
            $config['password'] = decrypt($creditorData['24sevenoffice_password'], "uVh1eiS366");
            $config['identity_name'] = $creditorData['24sevenoffice_identityname'];
            $config['client_id'] = $creditorData['24sevenoffice_client_id'];
        } else {
            if ($this->ownercompany_id) {
                $s_sql = "SELECT * FROM integration24sevenoffice WHERE ownerCompanyId = ?";
                $o_query = $this->o_main->db->query($s_sql, array($this->ownercompany_id));
                if($o_query && $o_query->num_rows()>0) $config = $o_query->row_array();
            } else {
                $s_sql = "SELECT * FROM integration24sevenoffice";
                $o_query = $this->o_main->db->query($s_sql);
                if($o_query && $o_query->num_rows()>0) $config = $o_query->row_array();
            }
        }

        // Return
        return $config;
    }

    function auth($getIdentityIdByName = "") {
        session_start();
        $config = $this->get_local_config();
        if($getIdentityIdByName == ""){
            $getIdentityIdByName = $config['identity_name'];
        }
        $username = $config['username'];  //Change this to your client user or community login
        $password = $config['password'];  //Change this to your password
        $applicationid = $config['applicationId'];  //Change this to your applicationId

        $params ["credential"]["Username"] = $username;
        // $encodedPassword = md5(mb_convert_encoding($password, 'utf-16le', 'utf-8')); //not supported anymore
        $params ["credential"]["Password"] = $password;
        $params ["credential"]["ApplicationId"] = $applicationid;

        // $params ["credential"]["IdentityId"] = "00000000-0000-0000-0000-000000000000";

        try {
            $authentication = new SoapClient ( "https://api.24sevenoffice.com/authenticate/v001/authenticate.asmx?wsdl", $this->options );
            // log into 24SevenOffice if we don't have any active session. No point doing this more than once.
            $login = true;
            if (!empty($_SESSION['ASP.NET_SessionId']))
            {
                $authentication->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);
                try
                {
                     $login = !($authentication->HasSession()->HasSessionResult);
					 if($login) {
					 	$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE session_id = '".$this->o_main->db->escape_str($this->sessionId)."' AND creditor_id = '".$this->o_main->db->escape_str($this->creditorId)."'";
	 					$o_query = $this->o_main->db->query($s_sql);
	 					$integration_session_item = $o_query ? $o_query->row_array() : array();
						if($integration_session_item['token'] != ""){
							if($integration_session_item['client_id'] > 0){
								$this->clientId = $integration_session_item['client_id'];
							}
							$this->authToken($integration_session_item['token']);
							return;
						}
					 }
                }
                catch ( SoapFault $fault )
                {
					$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE session_id = '".$this->o_main->db->escape_str($this->sessionId)."' AND creditor_id = '".$this->o_main->db->escape_str($this->creditorId)."'";
					$o_query = $this->o_main->db->query($s_sql);
					$integration_session_item = $o_query ? $o_query->row_array() : array();
					if($integration_session_item['token'] != ""){
						if($integration_session_item['client_id'] > 0){
							$this->clientId = $integration_session_item['client_id'];
						}
						$this->authToken($integration_session_item['token']);
						return;
					} else {
	                    $login = true;
					}
                }
            } else if(isset($this->sessionId) && !empty($this->sessionId))
			{
				$_SESSION['ASP.NET_SessionId'] = $this->sessionId;
				$authentication->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);
				try
                {
                     $login = !($authentication->HasSession()->HasSessionResult);
					 if($login) {
					 	$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE session_id = '".$this->o_main->db->escape_str($this->sessionId)."' AND creditor_id = '".$this->o_main->db->escape_str($this->creditorId)."'";
	 					$o_query = $this->o_main->db->query($s_sql);
	 					$integration_session_item = $o_query ? $o_query->row_array() : array();
						if($integration_session_item['token'] != ""){
							if($integration_session_item['client_id'] > 0){
								$this->clientId = $integration_session_item['client_id'];
							}
							$this->authToken($integration_session_item['token']);
							return;
						}
					 }
                }
                catch ( SoapFault $fault )
                {
					$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE session_id = '".$this->o_main->db->escape_str($this->sessionId)."' AND creditor_id = '".$this->o_main->db->escape_str($this->creditorId)."'";
					$o_query = $this->o_main->db->query($s_sql);
					$integration_session_item = $o_query ? $o_query->row_array() : array();
					if($integration_session_item['token'] != ""){
						if($integration_session_item['client_id'] > 0){
							$this->clientId = $integration_session_item['client_id'];
						}
						$this->authToken($integration_session_item['token']);
						return;
					} else {
	                    $login = true;
					}
                }
			}
            if( $login )
            {
				$this->error = "Failed to login";
                // if($this->identityId == "") {
                //     $identities = $authentication->GetIdentitiesWithCredential($params);
                //     $identiryResult = $identities->GetIdentitiesWithCredentialResult->Identity;
                //     $this->identities = $identiryResult;
                //     $identityId = "";
				//
                //     if(is_array($identiryResult)){
                //         if($getIdentityIdByName != ""){
                //             foreach($identiryResult as $identityResultSingle){
                //                 $client = $identityResultSingle->User;
                //                 if(mb_strtolower($client->Name,'UTF-8') == mb_strtolower($getIdentityIdByName, 'UTF-8')) {
                //                     $identityId  = $identityResultSingle->Id;
                //                 } else {
                //                     $client = $identityResultSingle->Client;
                //                     if(mb_strtolower($client->Name,'UTF-8') == mb_strtolower($getIdentityIdByName, 'UTF-8')) {
                //                         $identityId  = $identityResultSingle->Id;
                //                     }
                //                 }
                //             }
                //         }
                //     } else {
                //         $client = $identiryResult->Client;
                //         $identityId  = $identiryResult->Id;
                //     }
				//
                //     $this->identityId = $identityId;
                //     if($identityId != ""){
                //         if($this->creditorId > 0){
                //             $s_sql = "UPDATE creditor SET entity_id = ? WHERE id = ?";
                //             $o_query = $this->o_main->db->query($s_sql, array($identityId, $this->creditorId));
                //         } else {
                //             $s_sql = "UPDATE ownercompany SET identity_id = ? WHERE id = ?";
                //             $o_query = $this->o_main->db->query($s_sql, array($identityId, $this->ownercompany_id));
                //         }
                //     }
                // }
				// if($this->creditorId > 0 && "" == $config['client_id'])
				// {
				// 	$identities = $authentication->GetIdentitiesWithCredential($params);
                //     $identiryResult = $identities->GetIdentitiesWithCredentialResult->Identity;
                //     $client_id = "";
                //     if(is_array($identiryResult))
				// 	{
                //         if($getIdentityIdByName != "")
				// 		{
                //             foreach($identiryResult as $identityResultSingle)
				// 			{
                //                 if(isset($identityResultSingle->Client))
				// 				{
                //                     $client = $identityResultSingle->Client;
                //                     if(mb_strtolower($client->Name,'UTF-8') == mb_strtolower($getIdentityIdByName, 'UTF-8'))
				// 					{
                //                         $client_id = $client->Id;
                //                     }
                //                 }
                //             }
                //         } else {
				// 			foreach($identiryResult as $identityResultSingle)
				// 			{
				// 				if($identityResultSingle->Id == $this->identityId){
	            //                     if(isset($identityResultSingle->Client))
				// 					{
	            //                         $client = $identityResultSingle->Client;
	            //                         $client_id = $client->Id;
	            //                     }
				// 				}
                //             }
				// 		}
                //     } else {
                //         $client_id  = $identiryResult->Client->Id;
                //     }
                //     if($client_id != "")
				// 	{
				// 		$s_sql = "UPDATE creditor SET 24sevenoffice_client_id = '".$this->o_main->db->escape_str($client_id)."' WHERE id = '".$this->o_main->db->escape_str($this->creditorId)."'";
				// 		$o_query = $this->o_main->db->query($s_sql);
                //     }
				// }
				//
                // $params["credential"]["IdentityId"] = $this->identityId;
                // if($params["credential"]["IdentityId"] != ""){
                //     $result = ($temp = $authentication->Login($params));
                //     // set the session id for next time we call this page
                //     $_SESSION['ASP.NET_SessionId'] = $result->LoginResult;
				//
                //     // each seperate webservice need the cookie set
                //     $authentication->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);
                //     // throw an error if the login is unsuccessful
                //     if($authentication->HasSession()->HasSessionResult == false)
                //         throw new SoapFault("0", "Invalid credential information.");
                // } else {
                //     $this->error = "No Identity found";
                // }
            }

        }
        catch ( SoapFault $fault )
        {
            $this->error = 'Exception: ' . $fault->getMessage();
        }
    }

	function authToken($token)
	{
        session_start();
		$o_main = $this->o_main;
        $config = $this->get_local_config();

        $applicationid = $config['applicationId'];  //Change this to your applicationId

        $params ["token"]["Id"] = $token;
        $params ["token"]["ApplicationId"] = $applicationid;
		// $params ["credential"]["IdentityId"] = "00000000-0000-0000-0000-000000000000";
        try {
            $authentication = new SoapClient ( "https://api.24sevenoffice.com/authenticate/v001/authenticate.asmx?wsdl", $this->options );

            $login = true;
			if($this->clientId > 0){
				$passport = $authentication->AuthenticateByToken($params);
                if(isset($passport->AuthenticateByTokenResult->Identities->Identity))
				{
					$s_session_id = $passport->AuthenticateByTokenResult->SessionId;
					$s_email = $passport->AuthenticateByTokenResult->Email;
                    $this->identities = $passport->AuthenticateByTokenResult->Identities->Identity;
                    $identityId = "";
					$client_id = "";
					if(isset($this->identities->Id)){
						$this->identities = array($this->identities);
					}
					foreach($this->identities as $identity)
					{
						$o_client = $identity->Client;
						$o_user = $identity->User;
						$client_id = $o_client->Id;
						if($client_id == $this->clientId) {
							$identityId = $identity->Id;
							break;
						}
					}

					$identityParams = array();
					$identityParams["identityId"] = $identityId;
					$identityResult = $authentication->SetIdentityById($identityParams);
					if(!$identityResult->SetIdentityByIdResult) {
						$identityId = "";
					}

                    $this->identityId = $identityId;
                    if($identityId != "" && "" != $client_id)
					{
						$s_sql = "INSERT INTO creditor_syncing SET created = NOW(), creditor_id = ?, started=NOW(), client_id = ?";
						$o_query = $o_main->db->query($s_sql, array(0, $client_id));
						$creditor_syncing_id = $o_main->db->insert_id();

						$v_name = explode(" ", $o_user->Name, 2);
						$s_sql = "SELECT * FROM creditor WHERE 24sevenoffice_client_id = '".$o_main->db->escape_str($client_id)."'";
						$o_query = $o_main->db->query($s_sql);
						if($o_query && $o_query->num_rows()==0)
						{

							$connect_tries = 1;
							$company_info = $this->get_company_info($s_session_id);
							$bank_account = "";
							$iban = "";
							$swift = "";
							$emails_for_notification = "";
							$interest_bookaccount = "";
							$reminder_bookaccount = "";
							$companyname = "";
							$companypostalbox = "";
							$companyzipcode = "";
							$companypostalplace = "";
							$companyphone = "";
							$companyorgnr = "";
							$companyEmail = "";
							$defaultCurrency = "";
							$successfullyConnected = false;
							if($company_info['GetClientInformationResult']){
								$successfullyConnected = true;
								$company_info_array = $company_info['GetClientInformationResult'];
								$companyname = $company_info_array['Name'];
								$bank_account = $company_info_array['BankAccount'];
								$iban = $company_info_array['IBAN'];
								$swift = $company_info_array['Swift'];
								$companyorgnr = $company_info_array['OrganizationNumber'];
								$companypostalbox = $company_info_array['AddressList']['Post']['Street'];
								$companyzipcode = $company_info_array['AddressList']['Post']['PostalCode'];
								$companypostalplace = $company_info_array['AddressList']['Post']['PostalArea'];
								$companyphone = $company_info_array['PhoneNumberList']['Work']['Value'];
								$companyEmail = $company_info_array['EmailAddressList']['Work']['Value'];
								$defaultCurrency = $company_info_array['DefaultCurrency'];
							} else {
								do {
									$connect_tries++;
									$company_info = $this->get_company_info($s_session_id);
									if($company_info['GetClientInformationResult']){
										$successfullyConnected = true;
										$company_info_array = $company_info['GetClientInformationResult'];
										$companyname = $company_info_array['Name'];
										$bank_account = $company_info_array['BankAccount'];
										$iban = $company_info_array['IBAN'];
										$swift = $company_info_array['Swift'];
										$companyorgnr = $company_info_array['OrganizationNumber'];
										$companypostalbox = $company_info_array['AddressList']['Post']['Street'];
										$companyzipcode = $company_info_array['AddressList']['Post']['PostalCode'];
										$companypostalplace = $company_info_array['AddressList']['Post']['PostalArea'];
										$companyphone = $company_info_array['PhoneNumberList']['Work']['Value'];
										$companyEmail = $company_info_array['EmailAddressList']['Work']['Value'];
										$defaultCurrency = $company_info_array['DefaultCurrency'];
										break;
									}
								} while($connect_tries < 10);
							}

							$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?, number_of_tries = ?, type = 3";
							$o_query = $o_main->db->query($s_sql, array(0, 'company info selection syncing '.$client_id, $creditor_syncing_id, $connect_tries));

							if($successfullyConnected){

				                $s_sql = "SELECT * FROM collecting_system_settings";
				                $o_query = $o_main->db->query($s_sql);
				                $collecting_system_settings = $o_query ? $o_query->row_array() : array();

								// $l_customer_id = 0;	
								$s_sql = "INSERT INTO creditor SET created = NOW(), moduleID = '110', createdBy = '24sevenintegration', integration_module='Integration24SevenOffice', onboarding_incomplete = 1, 24sevenoffice_client_id = '".$o_main->db->escape_str($client_id)."', bank_account = '".$o_main->db->escape_str($bank_account)."',
								sync_from_accounting = 1,
								reminder_bookaccount = '".$o_main->db->escape_str($collecting_system_settings['default_reminder_bookaccount'])."',
								interest_bookaccount = '".$o_main->db->escape_str($collecting_system_settings['default_interest_bookaccount'])."',
								companyname = '".$o_main->db->escape_str($companyname)."',
								companypostalbox = '".$o_main->db->escape_str($companypostalbox)."',
								companyzipcode = '".$o_main->db->escape_str($companyzipcode)."',
								companypostalplace = '".$o_main->db->escape_str($companypostalplace)."',
								companyphone = '".$o_main->db->escape_str($companyphone)."',
								companyorgnr = '".$o_main->db->escape_str($companyorgnr)."',
								companyEmail = '".$o_main->db->escape_str($companyEmail)."',
								companyiban = '".$o_main->db->escape_str($iban)."',
								companyswift = '".$o_main->db->escape_str($swift)."',
								default_currency = '".$o_main->db->escape_str($defaultCurrency)."',
								collecting_process_for_company = '".$o_main->db->escape_str($collecting_system_settings['default_collecting_process_for_company'])."',
								collecting_process_for_person = '".$o_main->db->escape_str($collecting_system_settings['default_collecting_process_for_person'])."',
								covering_order_and_split_id = '".$o_main->db->escape_str($collecting_system_settings['default_covering_order_and_split_id'])."',
								warning_covering_order_and_split_id = '".$o_main->db->escape_str($collecting_system_settings['default_warning_covering_order_and_split_id'])."',
								minimumAmountToPaybackToDebitor = '".$o_main->db->escape_str($collecting_system_settings['default_minimumAmountToPaybackToDebitor'])."',
								maximumAmountForgiveTooLittlePayed = '".$o_main->db->escape_str($collecting_system_settings['default_maximumAmountForgiveTooLittlePayed'])."',
								print_reminders = 1";
								$o_query = $o_main->db->query($s_sql);
								if(!$o_query) throw new SoapFault("0", "Creditor not created.");
								$l_creditor_id = $o_main->db->insert_id();

								if($this->supplierId == "508851970445592"){
									$s_sql = "INSERT INTO creditor_reminder_custom_profiles SET
									created = NOW(),
									createdBy = '".$o_main->db->escape_str("onboarding")."',
									name = '',
									creditor_id = '".$o_main->db->escape_str($l_creditor_id)."',
									reminder_process_id = 1";
									$o_query = $o_main->db->query($s_sql);
									if($o_query){
										$profileForPersonId = $o_main->db->insert_id();
									}

									$s_sql = "INSERT INTO creditor_reminder_custom_profiles SET
									created = NOW(),
									createdBy = '".$o_main->db->escape_str("onboarding")."',
									name = '',
									creditor_id = '".$o_main->db->escape_str($l_creditor_id)."',
									reminder_process_id = 8";
									$o_query = $o_main->db->query($s_sql);
									if($o_query){
										$profileForCompanyId = $o_main->db->insert_id();
									}
									if($profileForPersonId > 0 && $profileForCompanyId){
										$s_sql = "UPDATE creditor 
										SET 
										updated = now(),
										updatedBy= ?,				
										reminder_system_edition = 0,
										onboarding_incomplete = 0,
										choose_progress_of_reminderprocess = 0,
										choose_move_to_collecting_process = 0,
										creditor_reminder_default_profile_for_company_id = ?,
										creditor_reminder_default_profile_id = ?
										WHERE id = ?";
										$o_query = $o_main->db->query($s_sql, array($username, $profileForCompanyId, $profileForPersonId, $l_creditor_id));

									}
									
									include_once(__DIR__."/../../CreditorsOverview/output/includes/fnc_process_open_cases_for_tabs.php");
									$s_sql = "UPDATE creditor_transactions SET to_be_reordered = 1 WHERE creditor_id = ? AND open = 1";
									$o_query = $o_main->db->query($s_sql, array($l_creditor_id));
									$source_id = 1;
									process_open_cases_for_tabs($l_creditor_id, $source_id);
								}
							} else {
								 throw new SoapFault("0", "Creditor not created.");
							}

						} else {
							$v_creditor = $o_query->row_array();
							$l_creditor_id = $v_creditor['id'];
							// $l_customer_id = $v_creditor['customer_id'];
						}


						// if(0 == intval($l_customer_id))
						// {
						// 	$s_sql = "SELECT * FROM customer WHERE name = '".$o_main->db->escape_str($companyname)."' AND IFNULL(creditor_id, 0) = 0";
						// 	$o_query = $o_main->db->query($s_sql);
						// 	if($o_query && $o_query->num_rows()==0)
						// 	{
						// 		$s_sql = "INSERT INTO customer SET created = NOW(), createdBy = '24sevenintegration', name = '".$o_main->db->escape_str($companyname)."'";
						// 		$o_query = $o_main->db->query($s_sql);
						// 		if(!$o_query) throw new SoapFault("0", "Customer not created.");
						// 		$l_customer_id = $o_main->db->insert_id();
						// 	} else {
						// 		$v_customer = $o_query->row_array();
						// 		$l_customer_id = $v_customer['id'];
						// 	}
						// 	$s_sql = "UPDATE creditor SET customer_id = '".$o_main->db->escape_str($l_customer_id)."' WHERE id = '".$o_main->db->escape_str($l_creditor_id)."'";
						// 	$o_query = $o_main->db->query($s_sql);
						// 	if(!$o_query) throw new SoapFault("0", "Creditor not updated.");
						// }

						// $s_sql = "UPDATE creditor SET log = '".$o_main->db->escape_str(json_encode($company_info))."' WHERE id = '".$o_main->db->escape_str($l_creditor_id)."'";
						// $o_query = $o_main->db->query($s_sql);
						// if(!$o_query) throw new SoapFault("0", "Creditor not updated.");

						// Create session
						$o_query = $o_main->db->query("SELECT * FROM accountinfo");
						$v_accountinfo = $o_query ? $o_query->row_array() : array();

						$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?,  type = 3";
						$o_query = $o_main->db->query($s_sql, array($l_creditor_id, 'Getynet session start'.$client_id, $creditor_syncing_id));

						if($this->previous != ''){
							$s_sql = "UPDATE creditor SET previous_reminder_system = '".$o_main->db->escape_str($this->previous)."' WHERE id = '".$o_main->db->escape_str($l_creditor_id)."'";
							$o_query = $o_main->db->query($s_sql);
						}

						if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../input/includes/APIconnect.php");
						$s_response = APIconnectAccount("accountcompanyinfoget", $v_accountinfo['accountname'], $v_accountinfo['password']);
						$v_response = json_decode($s_response, TRUE);
						$v_companyinfo = $v_response['data'];
						$s_response = APIconnectAccount("account_authenticate", $v_accountinfo['accountname'], $v_accountinfo['password']);
						$v_auth = json_decode($s_response, TRUE);

						$hostsplit =  explode(".",$_SERVER['HTTP_HOST']);
						$host = (count($hostsplit) == 3 ? substr($_SERVER['HTTP_HOST'],strpos($_SERVER['HTTP_HOST'],".")+1) : $_SERVER['HTTP_HOST']);

						setcookie("acc_username", $s_email, time()+60*60*24*365, '/', ".$host", true, true);
						setcookie("acc_session_id", uniqid('24so', TRUE), time()+60*60*24*365, '/', ".$host", true, true);
						setcookie("companyID", $v_companyinfo['id'], time()+60*60*24*365, '/', ".$host", true, true);

						$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?,  type = 3";
						$o_query = $o_main->db->query($s_sql, array($l_creditor_id, 'Getynet session end'.$client_id, $creditor_syncing_id));

						$s_cus_portal_account = '';
						$v_tmp = explode('/', $config['cus_portal_account_url']);
						while('' == $s_cus_portal_account)
						{
							$s_cus_portal_account = array_pop($v_tmp);
						}
						$s_sql = "UPDATE creditor_syncing SET finished=NOW() WHERE id = ?";
						$o_query = $o_main->db->query($s_sql, array($creditor_syncing_id));

						$_SESSION['redirect_url'] = $config['cus_portal_account_url'].'/fw/index.php?companyID='.$v_companyinfo['id'].'&accountname='.$s_cus_portal_account.'&24so_conn='.$v_accountinfo['accountname'].'&token='.urlencode($v_auth['token']).'&user_name='.urlencode($o_user->Name).'&url_param='.urlencode('module=CustomerPortalCollectCase&folder=output&folderfile=output&inc_obj=list&list_filter=canSendReminderNow&mainlist_filter=reminderLevel&&creditor_filter='.$l_creditor_id);
                    }
                }

                $params["credential"]["IdentityId"] = $this->identityId;
                if('' != $s_session_id && '' != $params["credential"]["IdentityId"])
				{
                    // set the session id for next time we call this page
                    $_SESSION['ASP.NET_SessionId'] = $s_session_id;

					$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE username = '".$o_main->db->escape_str($s_email)."' AND creditor_id = '".$o_main->db->escape_str($l_creditor_id)."'";
					$o_query = $o_main->db->query($s_sql);
					if($o_query && 0 < $o_query->num_rows())
					{
						$s_sql = "UPDATE integration24sevenoffice_session SET created = NOW(), updated=NOW(), session_id = '".$o_main->db->escape_str($s_session_id)."', identity_id = '".$o_main->db->escape_str($identityId)."', token = '".$o_main->db->escape_str($token)."', client_id = '".$o_main->db->escape_str($client_id)."' WHERE username = '".$o_main->db->escape_str($s_email)."' AND creditor_id = '".$o_main->db->escape_str($l_creditor_id)."'";
						$o_query = $o_main->db->query($s_sql);
					} else {
						$s_sql = "INSERT INTO integration24sevenoffice_session SET created = NOW(), username = '".$o_main->db->escape_str($s_email)."', creditor_id = '".$o_main->db->escape_str($l_creditor_id)."', session_id = '".$o_main->db->escape_str($s_session_id)."', identity_id = '".$o_main->db->escape_str($identityId)."', token = '".$o_main->db->escape_str($token)."', client_id = '".$o_main->db->escape_str($client_id)."'";
						$o_query = $o_main->db->query($s_sql);
					}

                    // each seperate webservice need the cookie set
                    $authentication->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);
                    // throw an error if the login is unsuccessful
                    if($authentication->HasSession()->HasSessionResult == false)
                        throw new SoapFault("0", "Invalid credential information.");
                } else {
                    $this->error = "No Identity found";
                }
            } else {
				$this->error = "No client id specified";
			}
        }
        catch ( SoapFault $fault )
        {
            $this->error = 'Exception: ' . $fault->getMessage();
        }
    }

    function get_customer_list($data = array()) {
		try {
	        $service = new SoapClient ("https://api.24sevenoffice.com/CRM/Company/V001/CompanyService.asmx?WSDL", $this->options);
	        $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);

	        if(count($data['customerIds']) > 0){
	            $searchParams = array(
	                'CompanyName' => '%',
	                'CompanyIds' => $data['customerIds']
	            );
	        } else {
	            $searchParams = array(
	                'CompanyName' => '%',
	                'ChangedAfter' => isset($data['changedAfter']) ? date("Y-m-d\TH:i:s.u", strtotime($data['changedAfter'])) : date("Y-m-d", strtotime("01.01.2000"))
	            );
	        }

	        $returnProperties = array('Addresses', 'PhoneNumbers', 'EmailAddresses', 'OrganizationNumber','Type', 'InvoiceLanguage', 'Status');

	        $params = array(
	            'searchParams' => $searchParams,
	            'returnProperties' => $returnProperties
	        );

	        $result = $service->GetCompanies($params);

	        // Convert to array
	        $result = json_decode(json_encode($result), true);
		}
		catch ( SoapFault $fault )
		{
			$this->error = 'Exception: ' . $fault->getMessage();
            $result['error'] = $fault->getMessage();
		}
        return $result;
    }

	function get_customer_list_for_sync($data = array()) {
		try {
	        $service = new SoapClient ("https://api.24sevenoffice.com/CRM/Company/V001/CompanyService.asmx?WSDL", $this->options);
	        $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);

			$searchParams = array(
				'Page' => isset($data['page']) ? $data['page'] : 1
			);
			if(isset($data['changedAfter'])){
				$searchParams['ChangedAfter'] = date("Y-m-d", strtotime($data['changedAfter']));
			}
	        $params = array(
	            'syncSearchParameters' => $searchParams
	        );

	        $result = $service->GetCompanySyncList($params);

	        // Convert to array
	        $result = json_decode(json_encode($result), true);
		}
		catch ( SoapFault $fault )
		{
			$this->error = 'Exception: ' . $fault->getMessage();
            $result['error'] = $fault->getMessage();
		}
        return $result;
    }

    function add_customer($data) {
        $return = array();
		try {
	        $service = new SoapClient ("https://api.24sevenoffice.com/CRM/Company/V001/CompanyService.asmx?WSDL", $this->options);
	        $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);

	        if ($data['customerName']) $data['name'] = $data['customerName'];

	        //required field
	        if($data['mailAddress']['Country'] == "" || $data['mailAddress']['Country'] == null){
	            $data['mailAddress']['Country'] = "NO";
	        }
	        $company = array(
	            'Name' => $data['name'],
	            'Type' => 'Business', // hardcoded,
	            'Addresses'=> array(
	                'Post'=> $data['mailAddress']
	            ),
	            'EmailAddresses'=> array(
	                'Invoice'=>array(
	                    'Name' => $data['invoiceEmail'],
	                    'Value' => $data['invoiceEmail']
	                )
	            ),
	            'OrganizationNumber'=>$data['vatNumber'],
	            'PaymentTime'=>$data['daysUntilDueDate']
	        );
	        if($data['external_id'] > 0){
	            $company['Id'] = $data['external_id'];
	        }
	        $companies = array($company);
	        $params = array(
	            'companies' => $companies
	        );
	        $result = $service->SaveCompanies($params);
	        $result = json_decode(json_encode($result), true);
	        if(!isset($result['SaveCompaniesResult']['Company']['APIException']) && isset($result['SaveCompaniesResult']['Company'])) {
	            $result = $result['SaveCompaniesResult']['Company'];

	            if(intval($data['external_id']) == 0){
	                $this->o_main->db->query("INSERT INTO customer_externalsystem_id
	                SET created = NOW(),
	                ownercompany_id = ?,
	                customer_id = ?,
	                external_id = ?", array($data['ownercompany_id'], $data['id'], $result['Id']));
	            }
	            $return = array(
	                'id' => $result['Id'],
	                'customerNumber' => $result['Id'],
	                'name' => $result['Name']
	            );

	        } else {
	            if(isset($result['SaveCompaniesResult']['Company']['APIException'])){
	                $return = array(
	                    "error" => $result['SaveCompaniesResult']['Company']['APIException'],
	                    "params" => $params
	                );
	            } else {
	                $return = array(
	                    "error" => $result['error'],
	                    "params" => $params
	                );
	            }
	        }
		} catch ( SoapFault $fault )
		{
			$this->error = 'Exception: ' . $fault->getMessage();
            $return['error'] = $fault->getMessage();
		}

        return $return;
    }
	function update_customer_type($data) {
        $return = array();
		try {
	        $service = new SoapClient ("https://api.24sevenoffice.com/CRM/Company/V001/CompanyService.asmx?WSDL", $this->options);
	        $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);

	        $company = array(
	            'Type' => $data['type'],
				'Id' => $data['external_customer_id'],
				'Name' => $data['name']
	        );
			if($data['address'] != ""){
				$data_new = array();
				$data_new['mailAddress']['Street'] = $data['address'];
				$data_new['mailAddress']['PostalCode'] = $data['postalNumber'];
				$data_new['mailAddress']['PostalArea'] = $data['city'];

				if($data['country'] == "" || $data['country'] == null){
		            $data_new['mailAddress']['Country'] = "NO";
		        }
				$company['Addresses'] = array(
				   'Visit'=> $data_new['mailAddress'],
   				   'Post'=> $data_new['mailAddress']
			   );
			}
	        $companies = array($company);
	        $params = array(
	            'companies' => $companies
	        );
	        $result = $service->SaveCompanies($params);
	        $result = json_decode(json_encode($result), true);
	        if(!isset($result['SaveCompaniesResult']['Company']['APIException']) && isset($result['SaveCompaniesResult']['Company'])) {
	            $result = $result['SaveCompaniesResult']['Company'];

	            $return = array(
	                'id' => $result['Id'],
	                'customerNumber' => $result['Id'],
	                'name' => $result['Name'],
	                'type' => $result['Type'],
					'result' => 1
	            );

	        } else {
	            if(isset($result['SaveCompaniesResult']['Company']['APIException'])){
	                $return = array(
	                    "error" => $result['SaveCompaniesResult']['Company']['APIException'],
	                    "params" => $params
	                );
	            } else {
	                $return = array(
	                    "error" => $result['error'],
	                    "params" => $params
	                );
	            }
	        }
		} catch ( SoapFault $fault )
		{
			$this->error = 'Exception: ' . $fault->getMessage();
            $return['error'] = $fault->getMessage();
		}

        return $return;
    }
	function get_company_info($s_session_id = NULL){
		try {
            if(NULL == $s_session_id)
			{
				$s_session_id = $_SESSION['ASP.NET_SessionId'];
			}
            $service = new SoapClient ("https://api.24sevenoffice.com/Client/V001/ClientService.asmx?WSDL", $this->options);
            $service->__setCookie("ASP.NET_SessionId", $s_session_id);

            $params = array(
            );

            $result = $service->GetClientInformation($params);

			// Convert to array
            $result = json_decode(json_encode($result), true);
        }
        catch ( SoapFault $fault )
        {
            $this->error = 'Exception: ' . $fault->getMessage();
        }
        return $result;
	}

    function update_customer($data) {
        return $this->add_customer($data);
    }

    function get_invoice_list($data) {
        try {
            // error_reporting(E_ALL);
            // ini_set("display_errors", 1);
            $service = new SoapClient ("https://api.24sevenoffice.com/Economy/InvoiceOrder/V001/InvoiceService.asmx?WSDL", $this->options);
            $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);

            if(count($data['invoiceIds']) > 0) {
                $searchParams = array(
                    'InvoiceIds' => $data['invoiceIds'],
                    'OrderStatus' => 'Invoiced'
                );
            } else {
                $searchParams = array(
                    'CustomerIds' => $data['customerIds'],
                    'ChangedAfter' => isset($data['changedAfter']) ? date("Y-m-d", strtotime($data['changedAfter'])) : date("Y-m-d", strtotime("01.01.2000")),
                    'InvoiceIds' => $data['invoiceIds'],
                    'OrderStatus' => 'Invoiced'
                );
            }

            $returnProperties = array('InvoiceId', 'OrderId', 'CustomerId', 'CustomerName', 'Addresses', 'OrderStatus', 'DateOrdered', 'DateInvoiced', 'PaymentTime', 'DateChanged', 'PaymentAmount','PaymentMethodId',
            'OurReference', 'YourReference', 'ReferenceNumber', 'InvoiceRows', 'OrderTotalVat', 'OrderTotalIncVat', 'Paid','Closed', 'ExternalStatus', 'OCR', 'DepartmentId', 'ProjectId');
            $rowReturnProperties = array('ProductId', 'RowId', 'Name', 'Quantity', 'Type', 'Price', 'DepartmentId', 'ProjectId');

            $params = array(
                'searchParams' => $searchParams,
                'invoiceReturnProperties' => $returnProperties,
                'rowReturnProperties' => $rowReturnProperties
            );
            $result = $service->GetInvoices($params);
			// var_dump($searchParams, $result);
            // Convert to array
            $result = json_decode(json_encode($result), true);
        }
        catch ( SoapFault $fault )
        {
            $this->error = 'Exception: ' . $fault->getMessage();
			// var_dump($this->error);
        }
        return $result;
    }
	function update_order($data){
        $service = new SoapClient ("https://api.24sevenoffice.com/Economy/InvoiceOrder/V001/InvoiceService.asmx?WSDL", $this->options);
        $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);

        $result = array();
        if($data['orderId'] > 0){
            $lines = $data['lines'];
            if(intval($data['departmentCode']) == 0){
                $data['departmentCode'] = null;
            }
            if(intval($data['projectCode']) == 0){
                $data['projectCode'] = null;
            }
            $preparedRows = array();
            foreach($lines as $line) {
                $row_order = array(
                    'Name' => htmlspecialchars($line['description']),
                    'InPrice' => 0,
                    'Price' => $line['amount'],
                    'Quantity' => $line['count'],
                    'DiscountRate' => $line['discount'],
                    'ProductId' => $line['external_product_id'],
                );
                if($line['external_sys_id'] > 0) {
                    $row_order['RowId'] = $line['external_sys_id'];
                    if($data['orderId'] != null) {
                        if($line['deleted']){
                            $row_order['ChangeState'] = "Delete";
                        } else {
                            $row_order['ChangeState'] = "Edit";
                        }
                    }
                } else {
                    if($data['orderId'] != null) {
                        $row_order['ChangeState'] = "Add";
                    }
                }
                $preparedRows[] = $row_order;
            }
            $params = array(
                'invoices' => array(
                     array(
                        'OrderId' => $data['orderId'],
                        'CustomerId' => $data['customerCode'],
                        'OrderStatus' => 'ForInvoicing',
                        'InvoiceRows' => $preparedRows,
                        'DepartmentId' => $data['departmentCode'],
                        'ProjectId' => $data['projectCode'],
                        'Distributor' => "Manual"
                    )
                )
            );
            try{
                $result = $service->SaveInvoices($params);
                $result = json_decode(json_encode($result), true);
            } catch ( SoapFault $fault )
            {
                $this->error = 'Exception: ' . $fault->getMessage();
                $result['error'] = $fault;
            }
        }
        return $result;
    }
    function add_invoice($data) {
        $service = new SoapClient ("https://api.24sevenoffice.com/Economy/InvoiceOrder/V001/InvoiceService.asmx?WSDL", $this->options);
        $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);

        $lines = $data['lines'];

		if(intval($data['departmentCode']) == 0){
			$data['departmentCode'] = null;
		}
		if(intval($data['projectCode']) == 0){
			$data['projectCode'] = null;
		}
		if(intval($data['orderId']) == 0){
			$data['orderId'] = null;
		}
        $preparedRows = array();
        foreach($lines as $line) {
            $row_order = array(
                'Name' => htmlspecialchars($line['description']),
                'InPrice' => 0,
                'Price' => $line['amount'],
                'Quantity' => $line['count'],
                'DiscountRate' => $line['discount'],
                'ProductId' => $line['external_product_id'],
            );
            if($line['external_sys_id'] > 0) {
                $row_order['RowId'] = $line['external_sys_id'];
                if($data['orderId'] != null) {
                    if($line['deleted']){
                        $row_order['ChangeState'] = "Delete";
                    } else {
                        $row_order['ChangeState'] = "Edit";
                    }
                }
            } else {
                if($data['orderId'] != null) {
                    $row_order['ChangeState'] = "Add";
                }
            }
            $preparedRows[] = $row_order;
        }
        $params = array(
            'invoices' => array(
                 array(
                    'OrderId' => $data['orderId'],
                    'OrderStatus' => 'Invoiced',
                    'CustomerId' => $data['customerCode'],
                    'DateInvoiced' => $data['date'],
                    'InvoiceRows' => $preparedRows,
                    'DepartmentId' => $data['departmentCode'],
                    'ProjectId' => $data['projectCode'],
                    'Distributor' => "Manual"
                )
            )
        );
        try{
            $result = $service->SaveInvoices($params);
            $result = json_decode(json_encode($result), true);
        } catch ( SoapFault $fault )
        {
            $this->error = 'Exception: ' . $fault->getMessage();
            $result['error'] = $fault;
        }
        return $result;
    }

    function save_product($data) {
		try {
	        $service = new SoapClient ("https://api.24sevenoffice.com/Logistics/Product/V001/ProductService.asmx?WSDL", $this->options);
	        $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);

	        $product_category_id = $this->save_product_category();

	        $params = array(
	            'products' => array(
	                0 => array(
	                    'ProductId' => $data['id'],
	                    'CategoryId' => $product_category_id,
	                    'Name' => $data['name'],
	                    'Price' => $data['priceWithoutVat'],
	                    'Cost' => $data['costWithoutVat'],
	                    'No' => $data['article_code'],
	                    'TaxRate' => $data['vat'],
	                )
	            )
	        );
	        $result = $service->SaveProducts($params);
	        $result = json_decode(json_encode($result), true);
	        $product = $result['SaveProductsResult']['Product'];
	        $processed_product = array();
	        if($product){
	            $processed_product['id'] = $product['Id'];
	        } else {
	            $processed_product['error'] = 'Error adding product';
	        }
		} catch ( SoapFault $fault )
        {
            $this->error = 'Exception: ' . $fault->getMessage();
        }
        return $processed_product;
    }

    function get_transactions($data, $full = false, $showLog = false) {
		$transactions_processed = null;
		try {
	        $service = new SoapClient ("https://api.24sevenoffice.com/Economy/Accounting/V001/TransactionService.asmx?WSDL", $this->options);
	        $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);
	        $dateSearchParameters = "EntryDate";
	        if($data['DateSearchParameters'] != "") {
	            $dateSearchParameters = $data['DateSearchParameters'];
	        }
	        $params = array(
	            'searchParams' => array(
	                'DateSearchParameters' => $dateSearchParameters,
	                'DateStart' => ($data['date_start'] != "") ? $data['date_start'] : date('Y-m-d', time() - 60*60*24*365),
	                'DateEnd' =>  ($data['date_end'] != "") ? $data['date_end'] : date('Y-m-d', time() + 60*60*24),
	                'SystemType'=> $data['SystemType'],
	                'InvoiceNo' => $data['InvoiceNo'],
	                'TransactionNoStart' => $data['TransactionNoStart'],
	                'TransactionNoEnd' => $data['TransactionNoEnd'],
	                'ShowOpenEntries' => $data['ShowOpenEntries'],
	                'HasInvoiceId' => $data['HasInvoiceId'],
	                'LinkId' => $data['LinkId'],
	                'AccountNoStart'=>$data['bookaccountStart'],
	                'AccountNoEnd'=>$data['bookaccountEnd'],
	                'CustomerId'=>$data['CustomerId']
	            )
	        );
	        $result = $service->GetTransactions($params);
			
	        $result = json_decode(json_encode($result), true);
	        $transactions = $result['GetTransactionsResult']['Transaction'];
			if(isset($result['GetTransactionsResult'])){
				if($full){
					return $result['GetTransactionsResult'];
				} else {
			        $transactions_processed = array();

			        if (count($transactions) > 0) {
			            if(!isset($transactions[0]['Date'])){
			                $transactions = array($transactions);
			            }
			            foreach ($transactions as $transaction) {
			                array_push($transactions_processed, array(
			                    'date' => $transaction['Date'],
			                    'accountNr' => $transaction['AccountNo'],
			                    'vatCode' => $transaction['VatCode'],
			                    'transactionNr' => $transaction['TransactionNo'],
			                    'invoiceNr' => $transaction['InvoiceNo'],
			                    'amount' => $transaction['Amount'],
			                    'dueDate' => $transaction['DueDate'],
			                    'kidNumber' => $transaction['OCR'],
			                    'systemType' => $transaction['SystemType'],
			                    'linkId' => $transaction['LinkId'],
			                    'open' => $transaction['Open'],
			                    'hidden'=>$transaction['Hidden'],
			                    'dateChanged'=>$transaction['DateChanged'],
			                    'dimensions'=>$transaction['Dimensions'],
			                    'comment'=>$transaction['Comment'],
			                    'currency'=>$transaction['Currency'],
			                    'currencyRate'=>$transaction['CurrencyRate'],
			                    'currencyUnit'=>$transaction['CurrencyUnit'],
			                    'transactionTypeId'=>$transaction['TransactionTypeId'],
			                    'id' => $transaction['Id']

			                ));
			            }
			        }
				}
			}
		} catch ( SoapFault $fault )
		{
			$this->error = 'Exception: ' . $fault->getMessage();
			if($showLog){
	        	var_dump($fault->getMessage(), $data);
			}
		}
        return $transactions_processed;
    }
	function get_currency_list(){
		try {
	        $service = new SoapClient ("https://api.24sevenoffice.com/Client/V001/ClientService.asmx?WSDL", $this->options);
	        $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);
	        $params = array();
	        $result = $service->GetCurrencyList($params);
	        $result = json_decode(json_encode($result), true);
		}
		catch ( SoapFault $fault )
		{
			$this->error = 'Exception: ' . $fault->getMessage();
			$result['error'] = $fault->getMessage();
		}
        return $result;

	}
    function get_products_list() {
        $service = new SoapClient ("https://api.24sevenoffice.com/Logistics/Product/V001/ProductService.asmx?WSDL", $this->options);
        $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);

		/*Id Int32 Default value: Int32.MinValue
		Name String Max length 125 characters
		Stock Decimal Default value: Decimal.MinValue
		StatusId Int32 Default value: Int32.MinValue
		CategoryId Int32 Default value: Int32.MinValue
		PriceGroupID Int32 Default value: Int32.MinValue
		InPrice Decimal Default value: Decimal.MinValue
		Description String Max length 500 characters
		DescriptionLong String characters
		Cost Decimal Default value: typeof(Decimal
		EAN1 String Max length 50 characters
		Price Decimal Default value: Decimal.MinValue
		No String Max length 100 characters
		DateChanged DateTime Default value: DateTime.MinValue
		APIException Office24Seven.WebService.Library.Common.APIException Default value: null
		Weight Decimal Default value: Decimal.MinValue
		MinimumStock Decimal Default value: Decimal.MinValue
		OrderProposal Decimal Default value: Decimal.MinValue
		StockLocation String Max length 100 characters
		SupplierProductCode String Max length 50 characters
		SupplierProductName String Max length 250 characters
		Web Boolean*/

        $params = array(
            'searchParams' => array('Name' => '%'),
            'returnProperties' => array('Id', 'Name', 'Stock', 'CategoryId', 'Price', 'No', 'Description', 'DescriptionLong', 'Web','TaxRate', 'Cost')
        );

        $result = $service->GetProducts($params);
        $result = json_decode(json_encode($result), true);
        $products = $result['GetProductsResult']['Product'];

        $products_processed = array();

        if ($products) {
            if(!isset($products[0]['Id'])){
                $products = array($products);
            }
            foreach ($products as $product) {
                array_push($products_processed, array(
                    'id' => $product['Id'],
                    'name' => isset($product['Name']) ? $product['Name'] : '',
                    'stock' => isset($product['Stock']) ? $product['Stock'] : '',
                    'price' => isset($product['Price']) ? $product['Price'] : '',
                    'categoryId' => isset($product['CategoryId']) ? $product['CategoryId'] : '',
                    'cost' => isset($product['Cost']) ? $product['Cost'] : '',
					'no' => isset($product['No']) ? $product['No'] : '',
					'description' => isset($product['Description']) ? $product['Description'] : '',
					'description_long' => isset($product['DescriptionLong']) ? $product['DescriptionLong'] : '',
					'status' => isset($product['Web']) ? $product['Web'] : '',
					'taxRate' => isset($product['TaxRate']) ? $product['TaxRate'] : ''
                ));
            }
        }

        return $products_processed;
    }

    function save_product_category(){
		try {
	        $service = new SoapClient ("https://api.24sevenoffice.com/Logistics/Product/V001/ProductService.asmx?WSDL", $this->options);
	        $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);
	        $categories = $this->get_product_categories();
	        $categoryId = null;
	        foreach($categories as $category){
	            if($category['name'] == "Getynet article"){
	                $categoryId = $category['id'];
	            }
	        }
	        $params = array(
	            'categories' => array(
	                0 => array(
	                    'Id' => $categoryId,
	                    'Name' => "Getynet article"
	                )
	            )
	        );
	        $result = $service->SaveCategories($params);
	        $result = json_decode(json_encode($result), true);
		} catch ( SoapFault $fault )
        {
            $this->error = 'Exception: ' . $fault->getMessage();
        }

        return $result;
    }

    function get_product_categories() {
		try {
	        $service = new SoapClient ("https://api.24sevenoffice.com/Logistics/Product/V001/ProductService.asmx?WSDL", $this->options);
	        $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);

	        $params = array(
	            'returnProperties' => array('Id', 'Name', 'ParentId')
	        );

	        $result = $service->GetCategories($params);
	        $result = json_decode(json_encode($result), true);

	        $categories = $result['GetCategoriesResult']['Category'];

	        $categories_processed = array();

	        if ($categories) {
	            if(!isset($categories[0]['Id'])){
	                $categories = array($categories);
	            }
	            // var_dump($categories);
	            foreach ($categories as $category) {
	                array_push($categories_processed, array(
	                    'id' => $category['Id'],
	                    'name' => $category['Name'],
	                    'parentId' => $category['ParentId']
	                ));
	            }
        	}
		} catch ( SoapFault $fault )
        {
            $this->error = 'Exception: ' . $fault->getMessage();
        }

        return $categories_processed;
    }

    function get_orders_list($data = array()) {
        $service = new SoapClient ("https://api.24sevenoffice.com/Economy/InvoiceOrder/V001/InvoiceService.asmx?WSDL", $this->options);
        $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);

        $params = array(
            'invoiceReturnProperties' => array(
                'OrderId', 'CustomerId', 'CustomerName', 'DateOrdered', 'OrderTotalIncVat', 'OrderTotalVat', 'ReferenceNumber', 'OrderStatus'
            ),
            'rowReturnProperties' => array(
                'ProductId', 'RowId', 'VatRate', 'Price', 'Name', 'DiscountRate', 'Quantity', 'Cost', 'InPrice'
            ),
            'searchParams' => array(
                'ChangedAfter' => '1970-01-01'// it demands at least one filter param
            )
        );
		if(count($data['customerIds'])>0)
		{
			$params['searchParams']['CustomerIds'] = $data['customerIds'];
		}
        if(count($data['orderStates'])>0)
		{
			$params['searchParams']['OrderStates'] = $data['orderStates'];
		}

        $result = $service->GetInvoices($params);
        $result_converted = json_decode(json_encode($result), true); // convert to array
        $return = array();

        if ($result_converted['GetInvoicesResult']['InvoiceOrder']) {
            if(isset($result_converted['GetInvoicesResult']['InvoiceOrder']['OrderId'])) {
                $order_rows = array();
                $row = $result_converted['GetInvoicesResult']['InvoiceOrder'];
                if ($row['InvoiceRows']['InvoiceRow']) {
                    foreach ($row['InvoiceRows']['InvoiceRow'] as $order_row) {
                        array_push($order_rows, array(
                            'id' => $order_row['RowId'],
                            'name' => $order_row['Name'],
                            'productId' => $order_row['ProductId'],
                            'price' => $order_row['Price'],
                            'cost' => $order_row['Cost'],
                            'quantity' => $order_row['Quantity'],
                            'discountRate' => $order_row['DiscountRate'],
                            'vatRate' => $order_row['VatRate']
                        ));
                    }
                }

                array_push($return, array(
                    'orderId' => $row['OrderId'],
                    'customerName' => $row['CustomerName'],
                    'customerId' => $row['CustomerId'],
                    'date' => date('Y-m-d H:i:s', strtotime($row['DateOrdered'])),
                    'totalIncVat' => $row['OrderTotalIncVat'],
                    'totalVat' => $row['OrderTotalVat'],
                    'total' => $row['OrderTotalIncVat'] - $row['OrderTotalVat'],
                    'referenceNumber' => $row['ReferenceNumber'],
                    'orderStatus' => $order_row['OrderStatus'],
                    'orderLines' => $order_rows
                ));
            } else {
                foreach ($result_converted['GetInvoicesResult']['InvoiceOrder'] as $row) {

                    $order_rows = array();

                    if ($row['InvoiceRows']['InvoiceRow']) {
                        foreach ($row['InvoiceRows']['InvoiceRow'] as $order_row) {
                            array_push($order_rows, array(
                                'id' => $order_row['RowId'],
                                'name' => $order_row['Name'],
                                'productId' => $order_row['ProductId'],
                                'price' => $order_row['Price'],
                                'cost' => $order_row['Cost'],
                                'quantity' => $order_row['Quantity'],
                                'discountRate' => $order_row['DiscountRate'],
                                'vatRate' => $order_row['VatRate']
                            ));
                        }
                    }

                    array_push($return, array(
                        'orderId' => $row['OrderId'],
                        'customerName' => $row['CustomerName'],
                        'customerId' => $row['CustomerId'],
                        'date' => date('Y-m-d H:i:s', strtotime($row['DateOrdered'])),
                        'totalIncVat' => $row['OrderTotalIncVat'],
                        'totalVat' => $row['OrderTotalVat'],
                        'total' => $row['OrderTotalIncVat'] - $row['OrderTotalVat'],
    					'referenceNumber' => $row['ReferenceNumber'],
                        'orderStatus' => $order_row['OrderStatus'],
                        'orderLines' => $order_rows
                    ));
                }
            }
        }
        else {
            $return = array(
                'error' => 1
            );
        }

        return $return;
    }

	function get_orders_lines($data = array()) {
        $service = new SoapClient ("https://api.24sevenoffice.com/Economy/InvoiceOrder/V001/InvoiceService.asmx?WSDL", $this->options);
        $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);

        $params = array(
            'invoiceReturnProperties' => array(
                'OrderId', 'CustomerId', 'CustomerName', 'DateOrdered', 'OrderTotalIncVat', 'OrderTotalVat', 'ReferenceNumber'
            ),
            'rowReturnProperties' => array(
                'ProductId', 'RowId', 'VatRate', 'Price', 'Name', 'DiscountRate', 'Quantity', 'Cost', 'InPrice'
            ),
            'searchParams' => array(
                'ChangedAfter' => '1970-01-01'// it demands at least one filter param
            )
        );
		if(count($data['orderIds'])>0)
		{
			$params['searchParams']['OrderIds'] = $data['orderIds'];
		}

        $result = $service->GetInvoices($params);
        $result_converted = json_decode(json_encode($result), true); // convert to array
        $return = array();

        if ($result_converted['GetInvoicesResult']['InvoiceOrder']) {
            $row = $result_converted['GetInvoicesResult']['InvoiceOrder'];

			$order_rows = array();

			if ($row['InvoiceRows']['InvoiceRow']) {
				foreach ($row['InvoiceRows']['InvoiceRow'] as $order_row) {
					array_push($order_rows, array(
						'id' => $order_row['RowId'],
						'name' => $order_row['Name'],
						'productId' => $order_row['ProductId'],
						'price' => $order_row['Price'],
						'cost' => $order_row['Cost'],
						'quantity' => $order_row['Quantity'],
						'discountRate' => $order_row['DiscountRate'],
						'vatRate' => $order_row['VatRate']
					));
				}
			}

			array_push($return, array(
				'orderId' => $row['OrderId'],
				'customerName' => $row['CustomerName'],
				'customerId' => $row['CustomerId'],
				'date' => date('Y-m-d H:i:s', strtotime($row['DateOrdered'])),
				'totalIncVat' => $row['OrderTotalIncVat'],
				'totalVat' => $row['OrderTotalVat'],
				'total' => $row['OrderTotalIncVat'] - $row['OrderTotalVat'],
				'referenceNumber' => $row['ReferenceNumber'],
				'orderLines' => $order_rows
			));
        }
        else {
            $return = array(
                'error' => 1
            );
        }

        return $return;
    }

    function get_projects_list() {
        $service = new SoapClient ("https://webservices.24sevenoffice.com/Project/V001/ProjectService.asmx?WSDL", $this->options);
        $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);
        $params = array(
            'Ps' => array(),
        );
        $result = $service->GetProjectList($params);
        $result = json_decode(json_encode($result), true);
        $list = $result['GetProjectListResult']['Project'];

        $return_list = array();

        // return $list;

        foreach ($list as $item) {
            array_push($return_list, array(
                'code' => $item['Id'],
                'description' => $item['Name'],
                'ownerCode' => '',
                'parentCode' => ''
            ));
        }

        return $return_list;
    }

    function get_account_list() {
        $service = new SoapClient ("https://webservices.24sevenoffice.com/economy/accountV002/Accountservice.asmx?wsdl", $this->options);
        $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);
        $params = array(
            'Ps' => array(),
        );
        $result = $service->GetAccountList($params);
        $result = json_decode(json_encode($result), true);
        $list = $result['GetAccountListResult']['AccountData'];

        $return_list = array();

        foreach ($list as $item) {
            array_push($return_list, array(
                'id' => $item['AccountId'],
                'code' => $item['AccountNo'],
                'name' => $item['AccountName'],
                'full_name' => $item['AccountName'],
                'account_tax' => isset($item['AccountTax']) ? $item['AccountTax'] : '',
                'tax_no' => isset($item['TaxNo']) ? $item['TaxNo'] : ''
            ));
        }

        return $return_list;
    }
    function get_tax_code_list() {
        $service = new SoapClient ("https://webservices.24sevenoffice.com/economy/accountV002/Accountservice.asmx?wsdl", $this->options);
        $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);
        $params = array(
            'Ps' => array(),
        );
        $result = $service->GetTaxCodeList($params);
        $result = json_decode(json_encode($result), true);
        $list = $result['GetTaxCodeListResult']['TaxCodeElement'];

        $return_list = array();

        foreach ($list as $item) {
            array_push($return_list, array(
                'id' => $item['TaxId'],
                'tax_no' => $item['TaxNo'],
                'tax_name' => $item['TaxName'],
                'tax_rate' => $item['TaxRate'],
                'account_no' => $item['AccountNo']
            ));
        }

        return $return_list;
    }

    function get_invoice_pdf($data = array()) {
        try {
            // error_reporting(E_ALL);
            // ini_set("display_errors", 1);
            $service = new SoapClient ("https://api.24sevenoffice.com/Economy/InvoiceOrder/V001/InvoiceService.asmx?WSDL", $this->options);
            $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);

            $searchParams = array(
                'InvoiceId' => $data['invoice_id']
            );

            $params = array(
                'Parameters' => $searchParams
            );

            $result = $service->GetInvoiceDocument($params);
            $file = $result->GetInvoiceDocumentResult;
        }
        catch ( SoapFault $fault )
        {
            $this->error = 'Exception: ' . $fault->getMessage();
        }
        return $file;
    }
    function getTypeList() {
		$result = array();
        try {
            $service = new SoapClient ("https://api.24sevenoffice.com/Economy/Account/V004/Accountservice.asmx?wsdl", $this->options);
            $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);
            $params = array(

            );
            $entryIdObject = $service->GetTypeList($params);
            $entryIdItem = json_decode(json_encode($entryIdObject), true);
			$types = array();
			if(isset($entryIdItem['GetTypeListResult'])){
				$datas = $entryIdItem['GetTypeListResult']['TypeData'];
				foreach($datas as $data_single) {
					if($data_single['TypeNo'] > 0){
						$types[] = $data_single;
					}
				}
			}
			$result['types'] = $types;
        }catch ( SoapFault $fault )
        {
            $this->error = 'Exception: ' . $fault->getMessage();
            $result['error'] = $fault->getMessage();
        }
        return $result;
    }
	function get_transaction_types(){
		$result = array();
        try {
            $service = new SoapClient ("https://api.24sevenoffice.com/Economy/Accounting/V001/TransactionService.asmx?WSDL", $this->options);
            $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);
            $params = array(

            );
            $entryIdObject = $service->GetTransactionTypes($params);
            $result = json_decode(json_encode($entryIdObject), true);

        }catch ( SoapFault $fault )
        {
            $this->error = 'Exception: ' . $fault->getMessage();
            $result['error'] = $fault->getMessage();
        }
        return $result;
	}
    function create_link($data) {
        $result['result'] = 0;
        if($data['transaction1_id'] != "" && $data['transaction2_id'] != "") {
            try {
                $service = new SoapClient ("https://api.24sevenoffice.com/Economy/Account/V004/Accountservice.asmx?wsdl", $this->options);
                $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);
                $linkId = 0;
                $linkObject = $service->CreateLink();
                $linkItem = json_decode(json_encode($linkObject), true);
                if($linkItem['CreateLinkResult'] > 0){
                    $linkId = $linkItem['CreateLinkResult'];
                }
                if($linkId > 0){
                    $params = array(
                        'linkEntryItem'=> array(
                            'LineIds'=>array($data['transaction1_id'], $data['transaction2_id']),
                            'LinkId'=>$linkId
                        )
                    );
                    $replacedLinks = $service->ReplaceLinkEntries($params);
                    $replacedLinksItem = json_decode(json_encode($replacedLinks), true);
                    if($replacedLinksItem['ReplaceLinkEntriesResult']) {
                        $result['result'] = 1;
                    }
                }
            }
            catch ( SoapFault $fault )
            {
                $this->error = 'Exception: ' . $fault->getMessage();
                $result['error'] = $fault->getMessage();
            }
        }

        return $result;
    }
	function create_link_multiple($data) {
        $result['result'] = 0;
        if(count($data['transaction_ids']) > 1) {
            try {
                $service = new SoapClient ("https://api.24sevenoffice.com/Economy/Account/V004/Accountservice.asmx?wsdl", $this->options);
                $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);
                $linkId = 0;
                $linkObject = $service->CreateLink();
                $linkItem = json_decode(json_encode($linkObject), true);
                if($linkItem['CreateLinkResult'] > 0){
                    $linkId = $linkItem['CreateLinkResult'];
                }
                if($linkId > 0){
                    $params = array(
                        'linkEntryItem'=> array(
                            'LineIds'=>$data['transaction_ids'],
                            'LinkId'=>$linkId
                        )
                    );
                    $replacedLinks = $service->ReplaceLinkEntries($params);
                    $replacedLinksItem = json_decode(json_encode($replacedLinks), true);
                    if($replacedLinksItem['ReplaceLinkEntriesResult']) {
                        $result['result'] = 1;
                    }
                }
            }
            catch ( SoapFault $fault )
            {
                $this->error = 'Exception: ' . $fault->getMessage();
                $result['error'] = $fault->getMessage();
            }
        }

        return $result;
    }
    function insert_transactions($datas, $isMultiple = false) {
        $result['result'] = 0;
        try {
            $service = new SoapClient ("https://api.24sevenoffice.com/Economy/Account/V004/Accountservice.asmx?wsdl", $this->options);
            $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);
			if(!$isMultiple){
				$datas = array($datas);
			}
			$vouchers = array();
			$creditor = $this->creditor;
			$reminder_bookaccount_project_id = $creditor['reminder_bookaccount_project_id'];
			$reminder_bookaccount_department_id = $creditor['reminder_bookaccount_department_id'];
			$interest_bookaccount_project_id = $creditor['interest_bookaccount_project_id'];
			$interest_bookaccount_department_id = $creditor['interest_bookaccount_department_id'];
			$invoice_bookaccount_project_id = $creditor['invoice_bookaccount_project_id'];
			$invoice_bookaccount_department_id = $creditor['invoice_bookaccount_department_id'];

			foreach($datas as $data){	
				$typeNo = "";	
				$entrySeriesId = 1;		
				$type_result = $this->getTypeList();

				if(isset($data['type_no'])){
					$typeNo = intval($data['type_no']);
				} else {
					$types = $type_result['types'];
					foreach($types as $type) {
						if(mb_strpos($type['Title'], "Utgående faktura") !== false) {
							$typeNo = $type['TypeNo'];
						}
					}
				}
				// $types = $type_result['types'];
				// foreach($types as $type) {
				// 	if(mb_strpos($type['Title'], "Utgående faktura") !== false) {
				// 		$entrySeriesId = $type['EntrySeriesId'];
				// 	}
				// }

	            $paramsEntryIdObject = array(
	                'argEntryId'=> array(
	                    'Date' => date("c", strtotime($data['date'])),
	                    'SortNo' => $entrySeriesId,
	                    'EntryNo' => ''
	                )
	            );
	            $entryIdObject = $service->GetEntryId($paramsEntryIdObject);
	            $entryIdItem = json_decode(json_encode($entryIdObject), true);
	            $transactionId = $entryIdItem['GetEntryIdResult']['EntryNo'];
	            if($transactionId > 0) {
	                $linkId = strtolower($this->guid());
	                $linkId2 = strtolower($this->guid());
	                if($data['type'] == 'reminderFee'){
	                    $vatNumber = 0;
	                } else if($data['type'] == 'interest') {
	                    $vatNumber = 0;
	                }

	                if($typeNo != "") {
						$case_id = $data['case_id'];
						$step_id = $data['step_id'];
						if($data['project_id'] != ""){
							$invoice_bookaccount_project_id = $data['project_id'];
						}
						if($data['department_id'] != ""){
							$invoice_bookaccount_department_id = $data['department_id'];
						}
	                    if($data['close']){
	                        $entryArray = array(
	                            'AccountNo'=> 1500,
	                            'CustomerId' => $data['customerId'],
	                            'Date'=> date("c", strtotime($data['date'])),
	                            'DueDate' => date("c", strtotime($data['dueDate'])),
	                            'Amount'=> $data['amount'],
	                            'Comment'=>$data['transaction_guid'],
	                            'InvoiceReferenceNo'=>$data['invoice_nr'],
	                            'InvoiceOcr'=>$data['kid_number'],
	                            'DepartmentId'=>$invoice_bookaccount_department_id,
	                            'ProjectId'=>$invoice_bookaccount_project_id
	                        );
	                    } else {
	                        $entryArray = array(
	                            'AccountNo'=> 1500,
	                            'CustomerId' =>$data['customerId'],
	                            'Date'=> date("c", strtotime($data['date'])),
	                            'DueDate' => date("c", strtotime($data['dueDate'])),
	                            'Amount'=> $data['amount'],
	                            'Comment'=>$data['text']."_".$data['accountNo']."_".$data['type']."_".$step_id,
	                            'InvoiceReferenceNo'=>$data['invoice_nr'],
	                            'InvoiceOcr'=>$data['kid_number'],
	                            'DepartmentId'=>$invoice_bookaccount_department_id,
	                            'ProjectId'=>$invoice_bookaccount_project_id,
	                            'LinkId' => $linkId,
	                            'Links'=>array(
	                                array(
	                                    'LineId' => $data['transaction_guid'],
	                                    'LinkId' => $linkId,
	                                )
	                            )
	                        );
	                    }
						if(isset($data['currency'])){
							$entryArray['CurrencyId'] = $data['currency'];
						}
						if(isset($data['currency_rate'])){
							$entryArray['CurrencyRate'] = $data['currency_rate'];
						}
						if(isset($data['currency_unit'])){
							$entryArray['CurrencyUnit'] = $data['currency_unit'];
						}

						if($data['project_id'] != ""){
							$reminder_bookaccount_project_id = $data['project_id'];
							$interest_bookaccount_project_id = $data['project_id'];
						}
						if($data['department_id'] != ""){
							$reminder_bookaccount_department_id = $data['department_id'];
							$interest_bookaccount_department_id = $data['department_id'];
						}
						$project_id_to_pass = "";
						$department_id_to_pass = "";
						if($data['type'] == "reminderFee"){
							$project_id_to_pass = $reminder_bookaccount_project_id;
							$department_id_to_pass = $reminder_bookaccount_department_id;
						} else if($data['type'] == "interest") {
							$project_id_to_pass = $interest_bookaccount_project_id;
							$department_id_to_pass = $interest_bookaccount_department_id;
						}
						$entryArrayDup = array(
							'AccountNo'=> $data['accountNo'],
							'CustomerId' =>$data['customerId'],
							'Date'=> date("c", strtotime($data['date'])),
							'Amount'=> $data['amount']*(-1),
							'Comment'=>$data['text'],
							'TaxNo' => $vatNumber,
							'DepartmentId'=>$department_id_to_pass,
							'ProjectId'=>$project_id_to_pass
						);
						if(isset($data['currency'])){
							$entryArrayDup['CurrencyId'] = $data['currency'];
						}
						if(isset($data['currency_rate'])){
							$entryArrayDup['CurrencyRate'] = $data['currency_rate'];
						}
						if(isset($data['currency_unit'])){
							$entryArrayDup['CurrencyUnit'] = $data['currency_unit'];
						}

						$voucher = array(
							'TransactionNo'=>$transactionId,
							'Sort' => $typeNo,
							'Entries' => array(
								$entryArray,
								$entryArrayDup
							)
						);
						$vouchers[date("Y", strtotime($data['date']))][] = $voucher;
	                }
	            } else if($transactionId == -1) {
					$result['error'] = $entryIdItem;
				}
			}
			if(count($vouchers) > 0){
				$bundles = array();
				foreach($vouchers as $year => $year_vouchers){
					$bundle = array(
						'YearId' => $year,
						'Name' => "Bundle",
						'BundleDirectAccounting' => false,
						'Sort' => $typeNo,
						'Vouchers' => $year_vouchers
					);
					$bundles[] = $bundle;
				}

				$params = array(
					'BundleList'=> array(
						'AllowDifference'=>false,
						'DirectLedger'=>false,
						'SaveOption'=>0,
						'DefaultCustomerId'=>0,
						'IgnoreWarnings'=>array('invoicealreadyexists'),
						'Bundles' => $bundles
					)
				);
				// var_dump($vouchers);
				$resultItem = $service->SaveBundleList($params);
				$resultItem = json_decode(json_encode($resultItem), true);
				$result['resultItem'] = $resultItem;
				$result['params'] = $params;
				if($resultItem['SaveBundleListResult']['Type'] == "Ok"){
					$result['result'] = 1;
				} else {
					$result['result'] = 0;
				}
			} else {
				$result['result'] = 0;
			}

        }
        catch ( SoapFault $fault )
        {
            $this->error = 'Exception: ' . $fault->getMessage();
            $result['error'] = $fault->getMessage()." - ".json_encode($entryIdItem)." - ".json_encode($params);
        }

        return $result;
    }
	function send_settlement($data = array()){
        $result['result'] = 0;
		try {
            $service = new SoapClient ("https://api.24sevenoffice.com/Economy/Account/V004/Accountservice.asmx?wsdl", $this->options);
	        $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);
			$bundles = array();
			
			$type_result = $this->getTypeList();
			$types = $type_result['types'];
			$type_info = array();
			foreach($types as $type) {
				if(mb_strpos($type['Title'], "Bank") !== false) {
					$type_info = $type;
				}
			}
			if($type_info){
				$result['type_info'] = $type_info;
				$type_no = $type_info['TypeNo'];
				$entry_series_id = $type_info['EntrySeriesId'];
				if($type_no != ""){
					$params = array(
						'argEntryId'=> array(
							'Date' => date("c", strtotime($data['date'])),
							'SortNo' => $entry_series_id,//Bank
							'EntryNo' => ''
						)
					);
					$entryIdObject = $service->GetEntryId($params);
					$entryIdItem = json_decode(json_encode($entryIdObject), true);
					$transactionId = $entryIdItem['GetEntryIdResult']['EntryNo'];
					if($transactionId > 0) {
						$vatNumber = 0;
						$department_id_to_pass = "";
						$project_id_to_pass = "";
						$entryArrayBank = array(
							'AccountNo'=> 1920,
							'Date'=> date("c", strtotime($data['date'])),
							'Amount'=> $data['total_bank_amount'],
							'Comment'=>"Oppgjør fra Oflow ".date("d.m.Y", strtotime($data['date'])),
							'TaxNo' => $vatNumber,
							'DepartmentId'=>$department_id_to_pass,
							'ProjectId'=>$project_id_to_pass
						);
						$entries[] = $entryArrayBank;
						$entryArrayVat = array(
							'AccountNo'=> 2910,
							'Date'=> date("c", strtotime($data['date'])),
							'Amount'=> $data['total_vat_amount'],
							'Comment'=>"Oppgjør fra Oflow ".date("d.m.Y", strtotime($data['date'])),
							'TaxNo' => $vatNumber,
							'DepartmentId'=>$department_id_to_pass,
							'ProjectId'=>$project_id_to_pass
						);
						$entries[] = $entryArrayVat;

						foreach($data['invoices'] as $invoice){
							$linkId = strtolower($this->guid());
							$entryArray = array(
								'AccountNo'=> 1500,
								'Date'=> date("c", strtotime($data['date'])),
								'Amount'=> $invoice['amount'],
								'InvoiceReferenceNo'=> $invoice['invoice_nr'],
	                            'CustomerId' =>$invoice['customerId'],
								'Comment'=>"Oppgjør fra Oflow ".date("d.m.Y", strtotime($data['date'])),
								'TaxNo' => $vatNumber,
								'DepartmentId'=>$department_id_to_pass,
								'ProjectId'=>$project_id_to_pass
							);
							if($invoice['open']){
								$entryArray['LinkId'] = $linkId;
								$entryArray['Links'] = array(
	                                array(
	                                    'LineId' => $invoice['transaction_guid'],
	                                    'LinkId' => $linkId,
	                                )
	                            );
							}
							$entries[] = $entryArray;
						}
						$voucher = array(
							'TransactionNo'=>$transactionId,
							'Sort' => $type_no,
							'Entries' => $entries
						);
						$vouchers[date("Y", strtotime($data['date']))][] = $voucher;
						if(count($vouchers) > 0){
							foreach($vouchers as $year => $year_vouchers){
								$bundle = array(
									'YearId' => $year,
									'Name' => "Oppgjør fra Oflow ".date("d.m.Y", strtotime($data['date'])),
									'BundleDirectAccounting' => false,
									'Sort' => $type_no,
									'Vouchers' => $year_vouchers
								);
								$bundles[] = $bundle;
							}

							$params = array(
								'BundleList'=> array(
									'AllowDifference'=>false,
									'DirectLedger'=>false,
									'SaveOption'=>1,
									'DefaultCustomerId'=>0,
									'IgnoreWarnings'=>'invoicealreadyexists',
									'Bundles' => $bundles
								)
							);
							$resultItem = $service->SaveBundleList($params);
							$resultItem = json_decode(json_encode($resultItem), true);
							$result['resultItem'] = $resultItem;
							$result['params'] = $params;
							if($resultItem['SaveBundleListResult']['Type'] == "Ok"){
								$result['result'] = 1;
							} else {
								$result['error'] = $resultItem['SaveBundleListResult'];
							}
						} else {
							$result['error'] = 'No vouchers to send';
						}
					} else {
						$result['error'] = 'Failed retreiving series id';
					}
				} else {
					$result['error'] = 'Type no not found';
				}				
			} else {				
				$result['error'] = 'Type Not Found';
			}
		} catch ( SoapFault $fault ) {
			$this->error = 'Exception: ' . $fault->getMessage();
			$result['error'] =  $fault->getMessage();
		}
		return $result;
	}
    function guid()
    {
        if (function_exists('com_create_guid') === true)
        {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }
	function get_projects() {
        try {
	        $service = new SoapClient ("https://webservices.24sevenoffice.com/Project/V001/ProjectService.asmx?WSDL", $this->options);
	        $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);
	        $params = array(
	            'Ps' => array(),
	        );
	        $result = $service->GetProjectList($params);
	        $result = json_decode(json_encode($result), true);

	        return $result;
		}
		catch ( SoapFault $fault )
		{
			$this->error = 'Exception: ' . $fault->getMessage();
			$result['error'] = $fault->getMessage();
		}
		return $result;
    }
	function get_departments() {
        try {
	        $service = new SoapClient ("https://api.24sevenoffice.com/Client/V001/ClientService.asmx?WSDL", $this->options);
	        $service->__setCookie("ASP.NET_SessionId", $_SESSION['ASP.NET_SessionId']);
	        $params = array();
	        $result = $service->GetDepartmentList($params);
	        $result = json_decode(json_encode($result), true);
		}
		catch ( SoapFault $fault )
		{
			$this->error = 'Exception: ' . $fault->getMessage();
			$result['error'] = $fault->getMessage();
		}
        return $result;
    }

	function get_currency_rates() {
        try {
			$ch = curl_init();
	        // set url
	        curl_setopt($ch, CURLOPT_URL, "https://currency-2.api.24sevenoffice.com/history/latest/");
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($ch, CURLOPT_COOKIE, 'ASP.NET_SessionId='.$_SESSION['ASP.NET_SessionId']);
	        $output = curl_exec($ch);
	        curl_close($ch);
			$result = json_decode($output, true);
		}
		catch ( SoapFault $fault )
		{
			$this->error = 'Exception: ' . $fault->getMessage();
			$result['error'] = $fault->getMessage();
		}
        return $result;
    }
}

?>
