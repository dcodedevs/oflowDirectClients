<?php
if(!function_exists("generateRandomString")){
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

$s_sql = "SELECT c.*, a.id AS action_id FROM collecting_cases_handling_action a JOIN collecting_cases c ON c.id = a.collecting_case_id
LEFT OUTER JOIN customer cust ON cust.id = c.debitor_id
WHERE (a.performed_date IS NULL OR a.performed_date = '0000-00-00')
AND (a.action_type = 1 OR (a.action_type = 4 AND (cust.invoiceEmail = '' or cust.invoiceEmail is null))) AND a.collecting_cases_process_steps_action_id is not null
ORDER BY c.id";
$o_query = $o_main->db->query($s_sql);
$cases = $o_query ? $o_query->result_array() : array();
$casesToGenerate = $_POST['casesToGenerate'];

$created_letters = array();
if(count($cases) > 0){
	do{
		$code = generateRandomString(10);
		$batch_check = null;
		$s_sql = "SELECT * FROM collecting_cases_batch WHERE code = ?";
		$o_query = $o_main->db->query($s_sql, array($code));
		if($o_query){
			$batch_check = $o_query->row_array();
		}
	} while($batch_check != null);

	$s_sql = "INSERT INTO collecting_cases_batch SET id=NULL, createdBy='".$o_main->db->escape_str($variables->loggID)."', created=NOW(), code= ?";
	$o_query = $o_main->db->query($s_sql, array($code));
	$batch_id = $o_main->db->insert_id();
	foreach($cases as $case)
	{
		if(in_array($case['id'], $casesToGenerate)){
			$o_curl = curl_init();
			$s_url = $extradomaindirroot.'/modules/CollectingCases/output/includes/generatePdf.php?caseId='.$case['id'].'&batch_id='.$batch_id.'&action_id='.$case['action_id'];

            curl_setopt($o_curl, CURLOPT_URL, $s_url);
			curl_setopt($o_curl, CURLOPT_RETURNTRANSFER, true);
			$s_response = curl_exec($o_curl);
			curl_close($o_curl);
		    if($s_response !== FALSE)
			{
				$v_response = json_decode($s_response, TRUE);
				if(isset($v_response['status']) && 1 == $v_response['status'])
				{
					$s_sql = "UPDATE collecting_cases_handling_action SET performed_date = NOW(), performed_action = 0 WHERE id = '".$o_main->db->escape_str($case['action_id'])."'";
		    		$o_query = $o_main->db->query($s_sql);
					if($o_query){

						$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE case_id = ? AND action_id = ?";
						$o_query = $o_main->db->query($s_sql, array($case['id'], $case['action_id']));
						$letter = $o_query ? $o_query->row_array() : array();
						$filesAttached[] = $letter['pdf'];

						$created_letters[] = $letter;
					}
				}
			}
		}
	}
}

echo count($casesToGenerate)." ".$formText_CasesProcessed_output;echo "<br/>";

echo count($created_letters)." ".$formText_LetterCreated_output;
if(count($created_letters) > 0) {
	require_once(__DIR__."/../../../../CollectingCasePdfHandling/output/includes/tcpdf/config/lang/eng.php");
	require_once(__DIR__."/../../../../CollectingCasePdfHandling/output/includes/tcpdf/tcpdf.php");
	require_once(__DIR__."/../../../../CollectingCasePdfHandling/output/includes/fpdi/fpdi.php");
	if (!class_exists("concat_pdf")) {
		class concat_pdf extends FPDI
		{
			var $files = array();
			function setFiles($files) {
				$this->files = $files;
			}
			function concat() {
				$this->setPrintHeader(false);
				$this->setPrintFooter(false);
				foreach($this->files AS $file) {
					$pagecount = $this->setSourceFile($file);
					for ($i = 1; $i <= $pagecount; $i++) {
						$tplidx = $this->ImportPage($i);
						$s = $this->getTemplatesize($tplidx);
						$this->AddPage($s['w'] > $s['h'] ? 'L' : 'P', array($s['w'], $s['h']));
						$this->useTemplate($tplidx);
					}
				}
			}
		}
	}

	$s_sql = "SELECT collecting_system_settings.* FROM collecting_system_settings ORDER BY id";
	$o_query = $o_main->db->query($s_sql);
	$collecting_system_settings = ($o_query ? $o_query->row_array() : array());

	$emailForPrinting = $collecting_system_settings['email_for_printing'];
	if($creditor['print_reminders'] == 1 ){
		// $s_receiver_email = $emailForPrinting;
		// $s_receiver_name = $emailForPrinting;
		//
		// $o_query = $o_main->db->query("SELECT * FROM sys_emailserverconfig ORDER BY default_server DESC");
		// $v_email_server_config = $o_query ? $o_query->row_array() : array();
		//
		// $s_email_body = '<h3>'.$formText_RemindersForPrinting_AutoTask.'</h3><br/>'.$formText_Creditor_output.": ".$creditorCustomer['name']." ".$creditorCustomer['middlename']." ".$creditorCustomer['lastname'];
		//
		// $mail = new PHPMailer;
		// $mail->CharSet	= 'UTF-8';
		// if($v_email_server_config['host'] != "")
		// {
		// 	$mail->Host	= $v_email_server_config['host'];
		// 	if($v_email_server_config['port'] != "") $mail->Port = $v_email_server_config['port'];
		//
		// 	if($v_email_server_config['username'] != "" and $v_email_server_config['password'] != "")
		// 	{
		// 		$mail->SMTPAuth	= true;
		// 		$mail->Username	= $v_email_server_config['username'];
		// 		$mail->Password	= $v_email_server_config['password'];
		//
		// 	}
		// } else {
		// 	$mail->Host = "mail.dcode.no";
		// }
		//
		// $s_email_subject = $formText_RemindersForPrinting_AutoTask;
		// $s_sender_email = 'noreply@getynet.com';
		// $s_sender_name = 'AutoTask';
		//
		// $mail->IsSMTP(true);
		// $mail->From		= $s_sender_email;
		// $mail->FromName	= $s_sender_name;
		// $mail->Subject	= html_entity_decode($s_email_subject, ENT_QUOTES, 'UTF-8');
		// $mail->Body		= $s_email_body;
		// $mail->isHTML(true);
		// $mail->AddAddress($s_receiver_email);
		//
		// $v_files = array();
		// foreach($filesAttached as $attached_file)
		// {
		// 	if(is_file(__DIR__."/../../../../../".$attached_file))
		// 	{
		// 		$v_files[] = __DIR__."/../../../../../".$attached_file;
		// 	}
		// }
		// $s_file = __DIR__."/../../../../../uploads/reminders_to_be_sent_".$creditor['id']."_".date("Y_m_d_h_i_s").".pdf";
		// if(sizeof($v_files)==0)
		// {
		// } else {
		// 	$o_pdf_merge = new concat_pdf();
		// 	$o_pdf_merge->setFiles($v_files);
		// 	$o_pdf_merge->concat();
		// 	$o_pdf_merge->Output($s_file, "F");
		// }
		// if(is_file($s_file))
		// {
		// 	$attachmentFile = $s_file;
		// 	$mail->AddAttachment($attachmentFile);
		// }
		//
		//
		// $l_send_status = 2;
		// if($mail->Send())
		// {
		// 	$l_send_status = 1;
		// }
		//
		// $sql = "INSERT INTO sys_emailsend SET created = NOW(), createdBy = 'AutoTask', send_on = NOW(), sender = '".$o_main->db->escape_str($s_sender_name)."', sender_email = '".$o_main->db->escape_str($s_sender_email)."', subject = '".$o_main->db->escape_str($s_email_subject)."', text = '".$o_main->db->escape_str($s_email_body)."'";
		// $o_insert = $o_main->db->query($sql);
		// if($o_insert)
		// {
		// 	$l_email_send_id = $o_main->db->insert_id();
		//
		// 	$sql = "INSERT INTO sys_emailsendto SET emailsend_id = '".$o_main->db->escape_str($l_email_send_id)."', receiver = '".$o_main->db->escape_str($s_receiver_name)."', receiver_email = '".$o_main->db->escape_str($s_receiver_email)."', extra1 = '', extra2 = '', `status` = '".$o_main->db->escape_str($l_send_status)."', status_message = '', perform_time = NOW(), perform_count = 1";
		// 	$o_main->db->query($sql);
		// }
		$l_send_status = 1;
		foreach($created_letters as $created_letter) {
			$hook_file = __DIR__ . '/../../../../IntegrationJCloud/hooks/send_print_file.php';
	        if (file_exists($hook_file)) {
	            require_once $hook_file;
	            if (is_callable($run_hook)) {
					$hook_params = array(
						'letter_id' => $created_letter['id']
					);
					$hook_result = $run_hook($hook_params);
					if(isset($hook_result['response'])) {
						if($hook_result['response']['status'] == 0){
							$s_sql = "UPDATE collecting_cases_claim_letter SET sent_to_external_company = 1 WHERE id = ?";
							$o_query = $o_main->db->query($s_sql, array($created_letter['id']));
						} else {
							$l_send_status = 0;
						}
					} else {
						$l_send_status = 0;
					}
	            } else {
					$l_send_status = 0;
				}
				unset($run_hook);
	        } else {
				$l_send_status = 0;
			}
			// $s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
			// $o_query = $o_main->db->query($s_sql, array($created_letter['case_id']));
			// $case = $o_query ? $o_query->row_array() : array();
			//
			// $s_sql = "SELECT creditor.*, customer.* FROM customer LEFT JOIN creditor ON creditor.customer_id = customer.id  WHERE creditor.id = ?";
			// $o_query = $o_main->db->query($s_sql, array($case['creditor_id']));
			// $creditor = ($o_query ? $o_query->row_array() : array());
			//
			// $s_sql = "SELECT * FROM customer WHERE customer.id = ?";
			// $o_query = $o_main->db->query($s_sql, array($case['debitor_id']));
			// $debitor = ($o_query ? $o_query->row_array() : array());
			//
			//
			// $sender_id = $creditor['id'];
			// $sender_name = $creditor['name'];
			// if($creditor['middlename'] != ""){
			// 	$sender_name .= " ".$creditor['middlename'];
			// }
			// if($creditor['lastname'] != ""){
			// 	$sender_name .= " ".$creditor['lastname'];
			// }
			// $sender_address = $creditor['paStreet']." ";
			// $sender_address2 = $creditor['paStreet2']." ";
			// $sender_city = $creditor['paCity']." ";
			// $sender_postal = $creditor['paPostalNumber']." ";
			// $sender_country = "NO";
			//
			// $receiver_id = $debitor['id'];
			// $receiver_name = $debitor['name'];
			// if($debitor['middlename'] != ""){
			// 	$receiver_name .= " ".$debitor['middlename'];
			// }
			// if($debitor['lastname'] != ""){
			// 	$receiver_name .= " ".$debitor['lastname'];
			// }
			// $receiver_address = $debitor['paStreet']." ";
			// $receiver_address2 = $debitor['paStreet2']." ";
			// $receiver_city = $debitor['paCity']." ";
			// $receiver_postal = $debitor['paPostalNumber']." ";
			// $receiver_country = "NO";
			//
			// $xml = new DOMDocument("1.0", "UTF-8");
			// $xml_letter = $xml->createElement("Letter");
			// $xml_letter->setAttribute( "Version", "1.0" );
			// $xml_DocumentID = $xml->createElement("DocumentID", $case['id']);
			// $xml_SenderPartyDetails = $xml->createElement("SenderPartyDetails");
			// $xml_SenderPartyIdentifier = $xml->createElement("SenderPartyIdentifier", $sender_id);
			// $xml_SenderOrganisationName = $xml->createElement("SenderOrganisationName", $sender_name);
			// $xml_SenderOrganisationName2 = $xml->createElement("SenderOrganisationName", $sender_name2);
			// $xml_SenderPostalAddressDetails = $xml->createElement("SenderPostalAddressDetails");
			// $xml_SenderStreetName = $xml->createElement("SenderStreetName", $sender_address);
			// $xml_SenderStreetName2 = $xml->createElement("SenderStreetName", $sender_address2);
			// $xml_SenderTownName = $xml->createElement("SenderTownName", $sender_city);
			// $xml_SenderPostCodeIdentifier = $xml->createElement("SenderPostCodeIdentifier", $sender_postal);
			// $xml_CountryCode = $xml->createElement("CountryCode", $sender_country);
			//
			//
			// $xml_SenderPostalAddressDetails->appendChild( $xml_SenderStreetName );
			// $xml_SenderPostalAddressDetails->appendChild( $xml_SenderStreetName2 );
			// $xml_SenderPostalAddressDetails->appendChild( $xml_SenderTownName );
			// $xml_SenderPostalAddressDetails->appendChild( $xml_SenderPostCodeIdentifier );
			// $xml_SenderPostalAddressDetails->appendChild( $xml_CountryCode );
			//
			// $xml_SenderPartyDetails->appendChild( $xml_SenderPartyIdentifier );
			// $xml_SenderPartyDetails->appendChild( $xml_SenderOrganisationName );
			// $xml_SenderPartyDetails->appendChild( $xml_SenderOrganisationName2 );
			// $xml_SenderPartyDetails->appendChild( $xml_SenderPostalAddressDetails );
			//
			// $xml_DeliveryPartyDetails = $xml->createElement("DeliveryPartyDetails");
			//
			// $xml_DeliveryStreetName = $xml->createElement("DeliveryStreetName", $receiver_address);
			// $xml_DeliveryStreetName2 = $xml->createElement("DeliveryStreetName", $receiver_address2);
			// $xml_DeliveryTownName = $xml->createElement("DeliveryTownName", $receiver_city);
			// $xml_DeliveryPostCodeIdentifier = $xml->createElement("DeliveryPostCodeIdentifier", $receiver_postal);
			// $xml_CountryCode = $xml->createElement("CountryCode", $receiver_country);
			//
			// $xml_DeliveryPostalAddressDetails = $xml->createElement("DeliveryPostalAddressDetails");
			// $xml_DeliveryPostalAddressDetails->appendChild( $xml_DeliveryStreetName );
			// $xml_DeliveryPostalAddressDetails->appendChild( $xml_DeliveryStreetName2 );
			// $xml_DeliveryPostalAddressDetails->appendChild( $xml_DeliveryTownName );
			// $xml_DeliveryPostalAddressDetails->appendChild( $xml_DeliveryPostCodeIdentifier );
			// $xml_DeliveryPostalAddressDetails->appendChild( $xml_CountryCode );
			//
			// $xml_DeliveryPartyIdentifier = $xml->createElement("DeliveryPartyIdentifier", $receiver_id);
			// $xml_DeliveryOrganisationName = $xml->createElement("DeliveryOrganisationName", $receiver_name);
			// $xml_DeliveryOrganisationName2 = $xml->createElement("DeliveryOrganisationName", $receiver_name2);
			//
			// $xml_DeliveryPartyDetails->appendChild( $xml_DeliveryPartyIdentifier );
			// $xml_DeliveryPartyDetails->appendChild( $xml_DeliveryOrganisationName );
			// $xml_DeliveryPartyDetails->appendChild( $xml_DeliveryOrganisationName2 );
			// $xml_DeliveryPartyDetails->appendChild( $xml_DeliveryPostalAddressDetails );
			//
			// $pdf_url_text = basename($created_letter['pdf']);
			// $xml_InvoiceUrlNameText = $xml->createElement("InvoiceUrlNameText", "APIX_PDFFILE");
			// $xml_InvoiceUrlText = $xml->createElement("InvoiceUrlText", "file://".$pdf_url_text);
			//
			// $xml_letter->appendChild( $xml_DocumentID );
			// $xml_letter->appendChild( $xml_SenderPartyDetails );
			// $xml_letter->appendChild( $xml_DeliveryPartyDetails );
			// $xml_letter->appendChild( $xml_InvoiceUrlNameText );
			// $xml_letter->appendChild( $xml_InvoiceUrlText );
			// $xml->appendChild( $xml_letter );
			//
			// $s_filename_xml = 'uploads/'.$formText_Claimletter_output.'_'.$case['id'].'_'.$created_letter['action_id'].'.xsd';
			// $xml->save(ACCOUNT_PATH.'/'.$s_filename_xml);
			//
			// $zip = new ZipArchive();
			//
			// $DelFilePath=$formText_Claimletter_output.'_'.$case['id'].'_'.$created_letter['action_id'].'.zip';
			//
			// if(file_exists(ACCOUNT_PATH."/uploads/".$DelFilePath)) {
			// 	unlink (ACCOUNT_PATH."/uploads/".$DelFilePath);
			// }
			// if ($zip->open(ACCOUNT_PATH."/uploads/".$DelFilePath, ZIPARCHIVE::CREATE) != TRUE) {
			// 	die ("Could not open archive");
			// }
			// $zip->addFile(ACCOUNT_PATH."/uploads/".$formText_Claimletter_output.'_'.$case['id'].'_'.$created_letter['action_id'].'.xsd',$formText_Claimletter_output.'_'.$case['id'].'_'.$created_letter['action_id'].'xsd');
			// $zip->addFile(ACCOUNT_PATH."/uploads/".$pdf_url_text,$pdf_url_text);
			// // close and save archive
			// $zip->close();
		}
		if($l_send_status == 1){
			echo $formText_SuccessfullySentPdfsToExternalCompany_output;
		} else {
			echo $formText_ErrorSendingToExternalComapnyEmail_output." ".$emailForPrinting." ".$formText_YouCanDownloadPdfsHere_output;
			echo " <a href='".$extradomaindirroot."/modules/CollectingCasePdfHandling/output/includes/ajax.download.php?code=".$code."&id=".$batch_id."&username=".$accountname."&caID=".$_GET['caID']."'>".$formText_DownloadLetters_output."</a>";

		}

	} else {
		echo " <a href='".$extradomaindirroot."/modules/CollectingCasePdfHandling/output/includes/ajax.download.php?code=".$code."&id=".$batch_id."&username=".$accountname."&caID=".$_GET['caID']."'>".$formText_DownloadLetters_output."</a>";
	}
}
echo "<br/>";

$emails_sent = 0;

// $s_sql = "SELECT c.*, a.id AS action_id FROM collecting_cases_handling_action a JOIN collecting_cases c ON c.id = a.collecting_case_id
// LEFT OUTER JOIN customer cust ON cust.id = c.debitor_id
// WHERE (a.performed_date IS NULL OR a.performed_date = '0000-00-00')
// AND (a.action_type = 2 OR (a.action_type = 4 AND (cust.invoiceEmail <> '' AND cust.invoiceEmail is not null))) AND a.collecting_cases_process_steps_action_id is not null
// ORDER BY c.id";
// $o_query = $o_main->db->query($s_sql);
// $cases = $o_query ? $o_query->result_array() : array();
// $casesToGenerate = $_POST['casesToGenerate'];
// if(count($casesToGenerate) > 0 && count($cases) > 0){
// 	$s_sql = "select * from sys_emailserverconfig order by default_server desc";
// 	$o_query = $o_main->db->query($s_sql);
// 	$v_email_server_config = $o_query ? $o_query->row_array() : array();
//
// 	if($v_email_server_config) {
// 		do{
// 			$code = generateRandomString(10);
// 			$batch_check = null;
// 			$s_sql = "SELECT * FROM collecting_cases_batch WHERE code = ?";
// 			$o_query = $o_main->db->query($s_sql, array($code));
// 			if($o_query){
// 				$batch_check = $o_query->row_array();
// 			}
// 		} while($batch_check != null);
//
// 		$s_sql = "INSERT INTO collecting_cases_batch SET id=NULL, createdBy='".$o_main->db->escape_str($variables->loggID)."', created=NOW(), code= ?";
// 		$o_query = $o_main->db->query($s_sql, array($code));
// 		$batch_id = $o_main->db->insert_id();
// 		foreach($cases as $case)
// 		{
// 			if(in_array($case['id'], $casesToGenerate)){
// 			    $s_sql = "SELECT creditor.*, customer.* FROM customer LEFT JOIN creditor ON creditor.customer_id = customer.id  WHERE creditor.id = ?";
// 			    $o_query = $o_main->db->query($s_sql, array($case['creditor_id']));
// 			    $creditor = ($o_query ? $o_query->row_array() : array());
//
// 				$s_sql = "SELECT * FROM customer WHERE id = ?";
// 				$o_query = $o_main->db->query($s_sql, array($case['debitor_id']));
// 				$customer = $o_query ? $o_query->row_array() : array();
// 				if($customer && $customer['invoiceEmail'] != "" && $creditor) {
// 				    $s_sql = "SELECT * FROM collecting_cases_handling_action WHERE id = ?";
// 				    $o_query = $o_main->db->query($s_sql, array($case['action_id']));
// 				    $handling_action = ($o_query ? $o_query->row_array() : array());
// 					if($handling_action){
// 						$s_sql = "SELECT * FROM collecting_cases_process_steps_action WHERE id = ?";
// 				        $o_query = $o_main->db->query($s_sql, array($handling_action['collecting_cases_process_steps_action_id']));
// 				        $collecting_cases_process_steps_action = ($o_query ? $o_query->row_array() : array());
// 				        if($collecting_cases_process_steps_action) {
//
// 				            $s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_steps.id = ?";
// 				            $o_query = $o_main->db->query($s_sql, array($collecting_cases_process_steps_action['collecting_cases_process_steps_id']));
// 				            $process_step = ($o_query ? $o_query->row_array() : array());
//
// 							$s_email_subject = $process_step['name']." ".$formText_From_pdf." ".$creditor['name']." ".$creditor['middlename']." ".$creditor['lastname'];
// 							$s_email_body = $formText_Hi_pdf."<br/><br/>".$formText_SeeAttachedPdfFileWithSetupOfOurClaimAndDueDate_pdf."<br/><br/>".$formText_BestRegards_pdf."<br/>".$creditor['name']." ".$creditor['middlename']." ".$creditor['lastname']."<br/>";
// 							if($creditor['phone'] != "") {
// 								$s_email_body .= $formText_Phone_pdf." ".$creditor['phone'];
// 							}
// 							$o_curl = curl_init();
// 							$s_url = $extradomaindirroot.'/modules/CollectingCases/output/includes/generatePdf.php?caseId='.$case['id'].'&batch_id='.$batch_id.'&action_id='.$case['action_id'];
//
// 				            curl_setopt($o_curl, CURLOPT_URL, $s_url);
// 							curl_setopt($o_curl, CURLOPT_RETURNTRANSFER, true);
// 							$s_response = curl_exec($o_curl);
// 							curl_close($o_curl);
// 						    if($s_response !== FALSE)
// 							{
// 								$v_response = json_decode($s_response, TRUE);
// 								if(isset($v_response['status']) && 1 == $v_response['status'])
// 								{
// 									$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE case_id = ? AND action_id = ?";
// 			                        $o_query = $o_main->db->query($s_sql, array($case['id'], $case['action_id']));
// 			                        $letter = $o_query ? $o_query->row_array() : array();
//
// 									$s_sql = "INSERT INTO sys_emailsend (id, created, createdBy, `type`, send_on, sender, sender_email, subscriberlist_id, unsubscriberlist_id, content_id, content_table, content_module_id, sending_limit, subject, text, batch_id) VALUES (NULL, NOW(), ?, 2, NOW(), '', ?, 0, 0, ?, 'collecting_cases', '', 0, ?, ?, ?);";
// 									$o_main->db->query($s_sql, array($variables->loggID, $v_settings['invoiceFromEmail'], $case['id'], $s_email_subject, $s_email_body, $batch_id));
// 									$l_emailsend_id = $o_main->db->insert_id();
// 									$emailsArray = array($customer['invoiceEmail']);
// 									foreach($emailsArray as $invoiceEmail)
// 									{
// 										// Trim U+00A0 (0xc2 0xa0) NO-BREAK SPACE
// 										//$invoiceEmail = trim($invoiceEmail,chr(0xC2).chr(0xA0));
// 										// Trim rest spaces and new lines
// 										//$invoiceEmail = trim($invoiceEmail);
// 										if(filter_var($invoiceEmail, FILTER_VALIDATE_EMAIL))
// 										{
// 											$mail = new PHPMailer;
// 											$mail->CharSet	= 'UTF-8';
// 											$mail->IsSMTP(true);
// 											$mail->isHTML(true);
// 											if($v_email_server_config['host'] != "")
// 											{
// 												$mail->Host	= $v_email_server_config['host'];
// 												if($v_email_server_config['port'] != "") $mail->Port = $v_email_server_config['port'];
//
// 												if($v_email_server_config['username'] != "" and $v_email_server_config['password'] != "")
// 												{
// 													$mail->SMTPAuth	= true;
// 													$mail->Username	= $v_email_server_config['username'];
// 													$mail->Password	= $v_email_server_config['password'];
//
// 												}
// 											} else {
// 												$mail->Host = "mail.dcode.no";
// 											}
// 											$mail->From		= $creditor['sender_email'];
// 											$mail->FromName	= $creditor['sender_name'];
// 											$mail->Subject	= $s_email_subject;
// 											$mail->Body		= $s_email_body;
// 											$mail->AddAddress($invoiceEmail);
// 											$mail->AddAttachment(__DIR__."/../../../../../".$letter['pdf']);
// 											$s_sql = "INSERT INTO sys_emailsendto (id, emailsend_id, receiver, receiver_email, `status`, status_message, perform_time, perform_count) VALUES (NULL, ?, ?, ?, 1, '', NOW(), 1);";
// 											$o_main->db->query($s_sql, array($l_emailsend_id, $customer['name']." ".$customer['middlename']." ".$customer['lastname'], $invoiceEmail));
// 											$l_emailsendto_id = $o_main->db->insert_id();
//
// 											if($mail->Send())
// 											{
// 												$emails_sent++;
// 												$s_sql = "UPDATE collecting_cases_handling_action SET performed_date = NOW(), performed_action = 1 WHERE id = '".$o_main->db->escape_str($case['action_id'])."'";
// 									    		$o_query = $o_main->db->query($s_sql);
//
// 											} else {
// 												$s_sql = "UPDATE sys_emailsendto SET status = 2, status_message = ? WHERE id = ?";
// 												$o_main->db->query($s_sql, array(json_encode($mail), $l_emailsendto_id));
//
// 												$mail = new PHPMailer;
// 												$mail->CharSet	= 'UTF-8';
// 												$mail->IsSMTP(true);
// 												$mail->isHTML(true);
// 												if($v_email_server_config['host'] != "")
// 												{
// 													$mail->Host	= $v_email_server_config['host'];
// 													if($v_email_server_config['port'] != "") $mail->Port = $v_email_server_config['port'];
//
// 													if($v_email_server_config['username'] != "" and $v_email_server_config['password'] != "")
// 													{
// 														$mail->SMTPAuth	= true;
// 														$mail->Username	= $v_email_server_config['username'];
// 														$mail->Password	= $v_email_server_config['password'];
//
// 													}
// 												} else {
// 													$mail->Host = "mail.dcode.no";
// 												}
// 												$mail->From		= "noreply@getynet.com";
// 												$mail->FromName	= "Getynet.com";
// 												$mail->Subject	= $formText_NotDelivered_Output.": ".$s_email_subject;
// 												$mail->Body		= $s_email_body;
// 												$mail->AddAddress($v_email_server_config['technical_email']);
// 												// $mail->AddAttachment($filepath.$file);
// 												// foreach($files_attached as $file_to_attach) {
// 												// 	$mail->AddAttachment(__DIR__."/../../../../../".$file_to_attach[1][0]);
// 												// }
// 											}
// 										} else {
// 										}
//
// 									}
// 								}
// 							}
// 						}
// 					}
// 				}
// 			}
// 		}
// 	}
// }

echo $emails_sent ." ".$formText_EmailsSent_output."(".$formText_currentlyDisabled_output.")";
