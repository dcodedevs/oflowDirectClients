<?php
//session_start();
$user = $variables->loggID?$variables->loggID:$_COOKIE['username'];
$developeraccess = $variables->developeraccess?$variables->developeraccess:$_POST['developeraccess'];

if(!isset($o_main))
{
	define('BASEPATH', realpath(__DIR__."/../../../../").DIRECTORY_SEPARATOR);
	include(BASEPATH."elementsGlobal/cMain.php");
}

$v_path = explode("/", realpath(__DIR__."/../"));
$s_module = array_pop($v_path);

$s_sql = "select * from session_framework where companyaccessID = ? and session = ? and username = ?";
$o_query = $o_main->db->query($s_sql, array($_GET['caID'], $_COOKIE['sessionID'], $_COOKIE['username']));
if($o_query && $o_query->num_rows()>0){
	$fw_session = $o_query->row_array();
}
$people_contactperson_type = 2;
$sql = "SELECT * FROM accountinfo_basisconfig ORDER BY id";
$o_query = $o_main->db->query($sql);
$accountinfo_basisconfig = $o_query ? $o_query->row_array() : array();
if(intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']) > 0){
	$people_contactperson_type = intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']);
}
$o_query = $o_main->db->get('accountinfo');
$accountinfo = $o_query ? $o_query->row_array() : array();
if(intval($accountinfo['contactperson_type_to_use_in_people']) > 0)
{
	$people_contactperson_type = $accountinfo['contactperson_type_to_use_in_people'];
}


$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id";
$o_query = $o_main->db->query($s_sql);
$v_customer_accountconfig = $o_query ? $o_query->row_array(): array();

$module="Customer2";
$v_module_access = json_decode($fw_session['cache_menu'],true);
$l_access = $v_module_access[$module][2];
$dbfields = array();
include(__DIR__."/../../input/settings/fields/customerfields.php");
foreach($prefields as $fieldinfo)
{
	$fieldinfo = explode('¤', $fieldinfo);
	$dbfields[] = $fieldinfo[0];
}
$dbfields2 = array();
include(__DIR__."/../../input/settings/fields/contactpersonfields.php");
foreach($prefields as $fieldinfo)
{
	$fieldinfo = explode('¤', $fieldinfo);
	$dbfields2[] = $fieldinfo[0];
}
$o_main->db->query("insert into callbacklog set log = ?, sessionID = ?", array("POST data: ".json_encode($_POST), "1111"));
if($_POST['output_form_submit'] == 1)
{
	if($l_access < 10){
		$fw_error_msg = array($formText_NoAccess_output);
		return;
	}
	$approved = $_POST['approved'];

	$insertTable = $_POST['table']; //'overview';
	// echo $_SERVER['HTTP_REFERER'];
	// print_r($_POST);	die();
	$spliter = $_POST['spliter'];
	if($spliter == "t"){
		$spliter = "\t";
	}
	$rows = explode("\r\n", str_replace("\"", "", $_POST['csv']));
	$header = $rows[0];
	$headers = explode($spliter, $header);
	$headersToShow = array();
	for ( $j = 0; $j < sizeof($headers); $j++ ) {
		$headersToShow[trim($headers[$j])] = trim($headers[$j]);
	}

	unset($rows[0]);
	for ( $i = 1; $i <= sizeof($rows); $i++ ) {
		$rowValues = explode($spliter,$rows[$i]);
		for ( $j = 0; $j < sizeof($headers); $j++ ) {
			$csv[$i][trim($headers[$j])] = trim($rowValues[$j]);
		}
		//break;
	}

	$relation = (array_filter($_POST['field']));
	if( is_array($_POST['customlabel']) && sizeof($_POST['customlabel']) ) {
		foreach($_POST['customlabel'] as $dbField=>$label) {
			$customLabels[] = $o_main->db_escape_name($dbField)." = ".$o_main->db->escape($label);
		}
	}
	$newsletter_full_found = array();
	$newsletter_partial_found = array();
	$newsletter_not_found = array();
	$newsletter_email_found = array();
	$matches_found = array();
	$nomatches_found = array();
	$other_nomatches_found = array();
	$other_matches_found = array();
	$subscription_not_matches_found = array();
	$subscription_matches_found = array();
	$successfullyUpdatedCount = 0;
	$rowNumber = 0;
	$empty_customernumber = 0;
	$currentProcessTime = time();
	foreach($csv as $row) {
		if(sizeof($customLabels)) {
			$set = $customLabels;
		}

		if($_POST['createProspect'] == 2){
			$set[] = "updated = NOW()";
			$set[] = "updatedby = ".$o_main->db->escape("imported - ".$user);
		} else {
			$set[] = "created = NOW()";
			$set[] = "createdby = ".$o_main->db->escape("imported - ".$user);
			$set[] = "customerType = 0";
		}

		$set2[] = "created = NOW()";
		$set2[] = "createdby = ".$o_main->db->escape("imported - ".$user);

		$foundItem = array();
		$foundItemBoth = array();
		$foundItemCustomer = array();
		$foundItemOrganization = array();

		$setSelfdefined = array();
		$setExtrenal = array();
		$setProspect = array();
		$prospectValue = "";
		$prospectInfo = "";
		$contactpersonEmail = "";
		$name = "";
		$fullname_for_import_comparing = "";
		$newsletter_email = "";
		$subscription_start_date = "0000-00-00";
		$subscription_nextrenewal_date = "0000-00-00";
		$subscription_name = "";
		$subscription_articlenumber1 = "";
		$subscription_articlenumber2 = "";
		$subscription_articlenumber3 = "";
		$subscription_subtype_id = "";
		$subscription_invoice_to_other_customer_id = "";
		$subscription_reference = "";
		$subscription_previous_customer_id = "";
		$subscription_previous_customer_name = "";
		$customerOrgNr = "";
		$customerName = "";
		$customerPreviousId = "";
		$subunitId = "";
		$subunitName = "";
		$subunit_compare_id = "0";
		$previous_sys_id = "";
		$previous_customer_sys_id = "";
		if(intval($_POST['createProspect']) != 0){
			$_POST['checkForDuplicates'] = 0;
		}
		foreach($relation as $dbField=>$csvField) {
			$csvField = trim($csvField);
			if(trim($row[$csvField]) != ""){
				if(strpos($dbField, "selfdefined") === false) {
					if(strpos($dbField, "contactperson") === false) {
						if(strpos($dbField, "prospect") === false) {
							if(strpos($dbField, "groupconnection") === false) {
								if(strpos($dbField, "subscription") === false) {
									if(strpos($dbField, "newsletter") === false) {
										if(strpos($dbField, "subunit") === false) {
											$rowData = $row[$csvField];
											$rowData = str_replace(array("\n", "\t", "\r"), '', $rowData);
											if($dbField == 'rentalUnit' || $dbField == 'wantInfoElectronic' || $dbField == 'mustReceiveInfoOnPaper') {
												if(strtolower($row[$csvField]) == "x"){
													$rowData = 1;
												} else {
													$rowData = 0;
												}
											}
											if($_POST['checkForDuplicates']){
												if($dbField == "publicRegisterId"){
													$customerOrgNr = $rowData;
												}
												if($dbField == "name"){
													$customerName = $rowData;
												}
											}

											if($dbField == "customerNumber"){
												if(intval($_POST['compareBy']) == 0){
													if(intval($rowData) > 0){
														$setExtrenal[] = $rowData;
													} else {
														$empty_customernumber++;
													}
												}
											} else if($dbField == "customerName") {
												if(intval($_POST['compareBy']) == 1){
													$customerName = $rowData;
												}
											} else if($dbField == "customerOrgNr"){
												if(intval($_POST['compareBy']) == 1){
													$customerOrgNr = $rowData;
												}
											} else {
												if($dbField == "comparing_previous_customer_id"){
													$customerPreviousId = $rowData;
												}
												$set[] = $o_main->db_escape_name($dbField)." = ".$o_main->db->escape($rowData);
												if($dbField == 'previous_sys_id'){
													$previous_customer_sys_id = $rowData;
												}
											}

										} else {
											$rowData = $row[$csvField];
											$realDbFieldArray = explode("_", $dbField, 2);
											$dbField = $realDbFieldArray[1];

											if($dbField == "id"){
												$subunitId = $rowData;
											}
											if($dbField == "name"){
												$subunitName = $rowData;
											}
											if($dbField == "compare_id"){
												$subunit_compare_id = $rowData;
											}
										}
									} else {
										$rowData = $row[$csvField];
										$realDbFieldArray = explode("_", $dbField, 2);
										$dbField = $realDbFieldArray[1];

										if($dbField == "fullname"){
											$fullname_for_import_comparing = $rowData;
										}
										if($dbField == "email"){
											$newsletter_email = $rowData;
										}
									}
								} else {
									$rowData = $row[$csvField];
									$realDbFieldArray = explode("_", $dbField, 2);
									$dbField = $realDbFieldArray[1];
									if($dbField == "start_date"){
										$subscription_start_date = $rowData;
									} else if($dbField == "nextrenewal_date"){
										$subscription_nextrenewal_date = $rowData;
									} else if($dbField == "articlenumber1"){
										$subscription_articlenumber1 = $rowData;
									} else if($dbField == "articlenumber2"){
										$subscription_articlenumber2 = $rowData;
									} else if($dbField == "articlenumber3"){
										$subscription_articlenumber3 = $rowData;
									} else if($dbField == "subtype_id"){
										$subscription_subtype_id = $rowData;
									} else if($dbField == "invoice_to_other_customer_id"){
										$subscription_invoice_to_other_customer_id = $rowData;
									} else if($dbField == "reference"){
										$subscription_reference = $rowData;
									} else if($dbField == "previous_customer_id"){
										$subscription_previous_customer_id = $rowData;
									} else if($dbField == "previous_customer_name"){
										$subscription_previous_customer_name = $rowData;
									} else if($dbField == "name"){
										$subscription_name = $rowData;
									}
								}
							} else {
								$rowData = $row[$csvField];
								$realDbFieldArray = explode("_", $dbField, 2);
								$dbField = $realDbFieldArray[1];
								if($dbField == "fullname_for_comparing"){
									$fullname_for_import_comparing = $rowData;
								}
								if($dbField == "previous_sys_id"){
									$previous_sys_id = $rowData;
								}
							}
						} else {
							$rowData = $row[$csvField];
							$realDbFieldArray = explode("_", $dbField, 2);
							$dbField = $realDbFieldArray[1];
							if($dbField == "value") {
								$prospectValue = $rowData;
							} else if($dbField == "info"){
								$prospectInfo = $rowData;
							}
						}
					} else {
						$rowData = $row[$csvField];
						$realDbFieldArray = explode("_", $dbField, 2);
						$dbField = $realDbFieldArray[1];
						if($dbField == "email") {
							$contactpersonEmail = $rowData;
						}
						if($dbField == "fullname_for_import_comparing"){
							$fullname_for_import_comparing = $rowData;
						}
						if($dbField == "birthdate"){
							$rowDataInit = '0000-00-00';
							if($rowData != ""){
								$rowDataInit = date("Y-m-d", strtotime($rowData));
							}
							$rowData = $rowDataInit;
						}
						if($dbField == "previous_customer_id"){
							$customerPreviousId = $rowData;
						}
						$set2[] = $o_main->db_escape_name($dbField)." = ".$o_main->db->escape($rowData);
					}
				} else {
					$realDbFieldArray = explode("_", $dbField, 2);
					$rowData = $row[$csvField];
					$rowData = str_replace(array("\n", "\t", "\r"), '', $rowData);

					$setSelfdefined[] = array($realDbFieldArray[1]=>$rowData);
				}
				if($dbField == "publicRegisterId") {
					$idField = $dbField;
					$idValue = preg_replace("/[^0-9]/", "", $row[$csvField]);
				}
				if($dbField == "invoiceEmail" && $rowData != "") {
					$set[] = "invoiceBy = 1";
				}
			} else {
				if($dbField == "customerNumber"){
				 	if(count(array_filter($row)) != 0){
						$empty_customernumber++;
					}
				}
			}
		}
		if($_POST['createProspect'] == 2){
            $o_main->db->query("insert into callbacklog set log = ?, sessionID = ?", array("SelfDefined: ".json_encode($setSelfdefined), "1111"));
			if(count($setExtrenal) > 0 || $customerName != "" || $customerOrgNr != ""){
				$ownercompanyIdPost = $_POST['ownercompany'];

				$match_found = false;
				if($_POST['compareBy'] == 0){
					foreach($setExtrenal as $external_sys_id){
						$o_query = $o_main->db->query("SELECT * FROM customer_externalsystem_id WHERE external_id = ? AND ownercompany_id = ?", array($external_sys_id, $ownercompanyIdPost));
						if($o_query && $o_query->num_rows()>0)
						{
							$external_item = $o_query->row_array();
							$o_query = $o_main->db->query("SELECT * FROM customer WHERE id = ?", array($external_item['customer_id']));
							$foundItem = $o_query ? $o_query->row_array() : array();
						}
					}
				} else if($_POST['compareBy'] == 1){
					$foundItemOrganization = array();
					$foundItemCustomer = array();
					$foundItemBoth = array();

					if($customerName != "" && $customerOrgNr != ""){
						$o_query = $o_main->db->query("SELECT * FROM customer WHERE name = ? AND  publicRegisterId = ? AND content_status < 2", array($customerName, $customerOrgNr));
						$foundItemBoth = $o_query ? $o_query->row_array() : array();
					}
					if(!$foundItemBoth){
						if($customerOrgNr != ""){
							$o_query = $o_main->db->query("SELECT * FROM customer WHERE publicRegisterId = ? AND content_status < 2", array($customerOrgNr));
							$foundItemOrganization = $o_query ? $o_query->row_array() : array();
						}
						if(!$foundItemOrganization){
							if($customerName != ""){
						        // $search_filter_reg = str_replace(" ", "|", $customerName);
								$o_query = $o_main->db->query("SELECT * FROM customer WHERE name = ? AND content_status < 2", array($customerName));
								$foundItemCustomer = $o_query ? $o_query->row_array() : array();
							}
						}
					}
					if($foundItemBoth) {
						$foundItem = $foundItemBoth;
					} else {
						if($foundItemOrganization) {
							$foundItem = $foundItemOrganization;
						} else {
							$foundItem = $foundItemCustomer;
						}
					}

				}
				if($foundItem){
					$match_found = true;
					if($approved){
                        $selfdefinedtest = -1;
                        foreach($setSelfdefined as $selfdefinedRow){
							foreach($selfdefinedRow as $selfdefinedId => $sefdefinedValue){
                                $selfdefinedtest = 0;
								$o_query = $o_main->db->query("SELECT * FROM customer_selfdefined_fields WHERE id = ?", array($selfdefinedId));
								$selfdefinedField = $o_query ? $o_query->row_array() : array();
								$list_id = 0;
								$valueCheck = 0;
								$valueString = "";

								if($selfdefinedField['type'] == 2){
									$valueCheck = 0;
									$valueString = "";
								} else {
									if(strtolower(trim($sefdefinedValue)) == "x"){
										$valueCheck = 1;
									} else {
										$valueCheck = 1;
										$valueString = $sefdefinedValue;
									}
								}


                                $o_main->db->query("insert into callbacklog set log = ?, sessionID = ?", array("Steg 1, customerID = ".$foundItem['id']." selfdefined field id =".$selfdefinedId, "1111"));
								$o_query = $o_main->db->query("SELECT * FROM customer_selfdefined_values WHERE customer_id = ? AND selfdefined_fields_id = ?", array($foundItem['id'], $selfdefinedId));
								if($o_query && $o_query->num_rows()>0)
								{
									$selfdefinedFieldValue = $o_query->row_array();
									$o_query = $o_main->db->query("UPDATE customer_selfdefined_values SET value = ?, active = ? WHERE id = ?", array($valueString, $valueCheck, $selfdefinedFieldValue['id']));
									$selfdefinedFieldValueId = $selfdefinedFieldValue['id'];

								} else {
									$o_query = $o_main->db->query("INSERT INTO customer_selfdefined_values SET customer_id = ?, selfdefined_fields_id = ?, value = ?, active = ?", array($foundItem['id'], $selfdefinedId, $valueString, $valueCheck));
									if($o_query){
										$selfdefinedFieldValueId = $o_main->db->insert_id();
									}
								}

								if(!$o_query){
									$fw_error_msg[] = json_encode($o_main->db->error());
                                    $o_main->db->query("insert into callbacklog set log = ?, sessionID = ?", array("Steg 1 error, ".json_encode($o_main->db->last_query()), "1111"));
								} else {
									if($selfdefinedField['type'] == 2){
										if($selfdefinedFieldValueId > 0){
											$o_query = $o_main->db->query("SELECT * FROM customer_selfdefined_values_connection WHERE selfdefined_value_id = ? AND selfdefined_list_line_id = ?", array($selfdefinedFieldValueId, $sefdefinedValue));
											if($o_query && $o_query->num_rows()>0)
											{
												$selfdefinedFieldConValue = $o_query->row_array();
												$o_query = $o_main->db->query("UPDATE customer_selfdefined_values_connection SET selfdefined_value_id = ?, selfdefined_list_line_id = ? WHERE id = ?", array($selfdefinedFieldValueId, $sefdefinedValue, $selfdefinedFieldConValue['id']));
												$selfdefinedFieldValueId = $selfdefinedFieldValue['id'];
											} else {
												$o_query = $o_main->db->query("INSERT INTO customer_selfdefined_values_connection SET selfdefined_value_id = ?, selfdefined_list_line_id = ?", array($selfdefinedFieldValueId, $sefdefinedValue));

											}
										}
									}
                                    $selfdefinedtest = 1;
									//$successfullyUpdatedCount++;
								}
							}
						}
						$s_sql  = "UPDATE ".$insertTable." SET ".implode(", ", $set)." WHERE id = ".$foundItem['id'].";";
						if(!$o_main->db->query($s_sql)){
							$fw_error_msg[] = $o_main->db->error();
						} else {
                            if($selfdefinedtest == -1 || $selfdefinedtest == 1)
							$successfullyUpdatedCount++;
						}
					}
				}
				if($_POST['compareBy'] == 0) {
					if($match_found){
						$matches_found[] = $external_sys_id;
					} else {
						$nomatches_found[] = $external_sys_id;
					}
				} else if($_POST['compareBy'] == 1) {
					if($foundItemCustomer || $foundItemOrganization || $foundItemBoth) {
						if($foundItemCustomer){  $foundItemCustomer['search_org'] = $customerOrgNr; $foundItemCustomer['search_name'] = $customerName; $matches_found['customer_only'][] = $foundItemCustomer;}
						if($foundItemOrganization){ $foundItemOrganization['search_org'] = $customerOrgNr; $foundItemOrganization['search_name'] = $customerName; $matches_found['organization_only'][] = $foundItemOrganization;}
						if($foundItemBoth){ $matches_found['both'][] = $foundItemBoth;}
					} else {
						$nomatches_found[] = array($customerName, $customerOrgNr);
					}
				}
			}
		} else if($_POST['createProspect'] == 3) {
			$ownercompanyIdPost = $_POST['ownercompany'];
			if($_POST['compareBy'] == 0){
				foreach($setExtrenal as $external_sys_id){
					$o_query = $o_main->db->query("SELECT * FROM customer_externalsystem_id WHERE external_id = ? AND ownercompany_id = ?", array($external_sys_id, $ownercompanyIdPost));
					if($o_query && $o_query->num_rows()>0)
					{
						$external_item = $o_query->row_array();
						$o_query = $o_main->db->query("SELECT * FROM customer WHERE id = ?", array($external_item['customer_id']));
						$foundItem = $o_query ? $o_query->row_array() : array();
					}
				}
			} else {
				$foundItemOrganization = array();
				$foundItemCustomer = array();
				$foundItemBoth = array();
				$foundItemSys = array();
				if($_POST['compareBy'] == 2) {
					if($customerPreviousId != "") {
						$o_query = $o_main->db->query("SELECT * FROM customer WHERE previous_sys_id = ? AND content_status < 2", array($customerPreviousId));
						$foundItemSys = $o_query ? $o_query->row_array() : array();
					}
				}
				if(!$foundItemSys) {
					if($customerName != "" && $customerOrgNr != ""){
						$o_query = $o_main->db->query("SELECT * FROM customer WHERE name = ? AND  publicRegisterId = ? AND content_status < 2", array($customerName, $customerOrgNr));
						$foundItemBoth = $o_query ? $o_query->row_array() : array();
					}
				}
				if(!$foundItemBoth && !$foundItemSys){
					if($customerOrgNr != ""){
						$o_query = $o_main->db->query("SELECT * FROM customer WHERE publicRegisterId = ? AND content_status < 2", array($customerOrgNr));
						$foundItemOrganization = $o_query ? $o_query->row_array() : array();
					}
					if(!$foundItemOrganization){
						if($customerName != ""){
							// $search_filter_reg = str_replace(" ", "|", $customerName);
							$o_query = $o_main->db->query("SELECT * FROM customer WHERE name = ? AND content_status < 2", array($customerName));
							$foundItemCustomer = $o_query ? $o_query->row_array() : array();
						}
					}
				}
				if($foundItemSys){
					$foundItem = $foundItemSys;
				} else if($foundItemBoth) {
					$foundItem = $foundItemBoth;
				} else {
					if($foundItemOrganization) {
						$foundItem = $foundItemOrganization;
					} else {
						$foundItem = $foundItemCustomer;
					}
				}

			}

			if(count($set2) > 2) {
				if($_POST['approvedChoice'] || $_POST['approvedChoiceAll']) {
					if($foundItem) {
						$set2[] = "type = 1";
						$set2[] = "customerId = ".$foundItem['id'];
						$s_sql = "SELECT * FROM contactperson WHERE customerId = ? AND fullname_for_import_comparing = ?";
						$o_query = $o_main->db->query($s_sql, array($foundItem['id'], $fullname_for_import_comparing));
						$contactpersonPrivate = $o_query ? $o_query->row_array() : array();
						if(!$contactpersonPrivate) {
							$s_sql  = "INSERT INTO contactperson SET ".implode(", ", $set2).";";
							if(!$o_main->db->query($s_sql)){
								$fw_error_msg[] = $o_main->db->error();
							} else {
								$successfullyUpdatedCount++;
							}
						} else {
							$s_sql  = "UPDATE contactperson SET updated = NOW(), ".implode(", ", $set2)." WHERE id = '".$o_main->db->escape_str($contactpersonPrivate['id'])."';";
							if(!$o_main->db->query($s_sql)){
								$fw_error_msg[] = $o_main->db->error();
							} else {
								$successfullyUpdatedCount++;
							}
						}
					} else {
						if($_POST['approvedChoiceAll']){
							$set2[] = "type = 1";
							$set2[] = "customerId = 0";
							$s_sql  = "INSERT INTO contactperson SET ".implode(", ", $set2).";";
							if(!$o_main->db->query($s_sql)){
								$fw_error_msg[] = $o_main->db->error();
							} else {
								$successfullyUpdatedCount++;
							}
						}
					}

				}
				if($foundItem){
					$matches_found[] = $fullname_for_import_comparing;
				} else {
					$nomatches_found[] = $fullname_for_import_comparing;
				}
			}
		} else if($_POST['createProspect'] == 4 || $_POST['createProspect'] == 10) {
	        $s_sql = "SELECT * FROM article_accountconfig";
	        $o_query = $o_main->db->query($s_sql);
	        $article_accountconfig = $o_query ? $o_query->row_array() : array();
			$foundItem = array();
			if(count($setExtrenal) > 0 || $customerName != "" || $customerOrgNr != "" || $_POST['createProspect'] == 10){
				if($_POST['createProspect'] == 4){
					$ownercompanyIdPost = $_POST['ownercompany'];
					if($_POST['compareBy'] == 0){
						foreach($setExtrenal as $external_sys_id){
							$o_query = $o_main->db->query("SELECT * FROM customer_externalsystem_id WHERE external_id = ? AND ownercompany_id = ?", array($external_sys_id, $ownercompanyIdPost));
							if($o_query && $o_query->num_rows()>0)
							{
								$external_item = $o_query->row_array();
								$o_query = $o_main->db->query("SELECT * FROM customer WHERE id = ?", array($external_item['customer_id']));
								$foundItem = $o_query ? $o_query->row_array() : array();
							}
						}
					} else {
						$foundItemOrganization = array();
						$foundItemCustomer = array();
						$foundItemBoth = array();
						$foundItemSys = array();
						if($_POST['compareBy'] == 2) {
							if($customerPreviousId != "") {
								$o_query = $o_main->db->query("SELECT * FROM customer WHERE previous_sys_id = ? AND content_status < 2", array($customerPreviousId));
								$foundItemSys = $o_query ? $o_query->row_array() : array();
							}
						}
						if(!$foundItemSys) {
							if($customerName != "" && $customerOrgNr != ""){
								$o_query = $o_main->db->query("SELECT * FROM customer WHERE name = ? AND  publicRegisterId = ? AND content_status < 2", array($customerName, $customerOrgNr));
								$foundItemBoth = $o_query ? $o_query->row_array() : array();
							}
						}
						if(!$foundItemBoth && !$foundItemSys){
							if($customerOrgNr != ""){
								$o_query = $o_main->db->query("SELECT * FROM customer WHERE publicRegisterId = ? AND content_status < 2", array($customerOrgNr));
								$foundItemOrganization = $o_query ? $o_query->row_array() : array();
							}
							if(!$foundItemOrganization){
								if($customerName != ""){
									// $search_filter_reg = str_replace(" ", "|", $customerName);
									$o_query = $o_main->db->query("SELECT * FROM customer WHERE name = ? AND content_status < 2", array($customerName));
									$foundItemCustomer = $o_query ? $o_query->row_array() : array();
								}
							}
						}
						if($foundItemSys){
							$foundItem = $foundItemSys;
						} else if($foundItemBoth) {
							$foundItem = $foundItemBoth;
						} else {
							if($foundItemOrganization) {
								$foundItem = $foundItemOrganization;
							} else {
								$foundItem = $foundItemCustomer;
							}
						}
					}
				} else if($_POST['createProspect'] == 10){
					$o_query = $o_main->db->query("SELECT * FROM customer_subunit WHERE id = ?", array($subunit_compare_id));
					$subunit = $o_query ? $o_query->row_array() : array();

					$o_query = $o_main->db->query("SELECT * FROM customer WHERE id = ?", array($subunit['customer_id']));
					$foundItem = $o_query ? $o_query->row_array() : array();
				}
				if($_POST['approvedChoice'] || $_POST['approvedChoiceAll'] || $approved) {

					if($_POST['createProspect'] == 4){
						if($foundItem) {

						} else {
							if($_POST['approvedChoiceAll']) {
								$foundItem['id'] = 0;
								$foundItem['name'] = '';
							}
						}
					}

					if($subscription_name == ""){
						$subscription_name = $foundItem['name'];
					}
					if(isset($foundItem['id'])) {
						$match_found = true;
						$internal_customer_id = 0;
						$internal_error = false;
						if($subscription_invoice_to_other_customer_id > 0) {
							$internal_error = true;
							$o_query = $o_main->db->query("SELECT * FROM customer_externalsystem_id WHERE external_id = ? AND ownercompany_id = ?", array($subscription_invoice_to_other_customer_id, $ownercompanyIdPost));
							if($o_query && $o_query->num_rows()>0)
							{
								$internal_error = false;
								$external_item = $o_query->row_array();
								$internal_customer_id = $external_item['customer_id'];
							}
						}
						if(!$internal_error){
							$s_sql  = "INSERT INTO subscriptionmulti SET created = NOW(), createdby = ".$o_main->db->escape("imported - ".$user).", customerId = ?, startDate = ?, nextRenewalDate = ?, subscriptionName = ?, subscriptiontype_id = ?, periodNumberOfMonths = ?, stoppedDate = '0000-00-00', subscriptionsubtypeId = ?, invoice_to_other_customer_id = ?, reference = ?, customer_subunit_id = ?, previous_customer_id = ?, previous_customer_name = ?;";
							if(!$o_main->db->query($s_sql, array($foundItem['id'], date("Y-m-d", strtotime($subscription_start_date)), date("Y-m-d", strtotime($subscription_nextrenewal_date)), $subscription_name, $_POST['subscription_type'], $_POST['subscription_period_length'], $subscription_subtype_id, $internal_customer_id, $subscription_reference, $subunit_compare_id, $subscription_previous_customer_id, $subscription_previous_customer_name))){
								$fw_error_msg[] = $o_main->db->error();
							} else {
								$subscriptionmultiId = $o_main->db->insert_id();
								if($subscriptionmultiId) {
									if($subscription_articlenumber1 > 0) {
						                $s_sql = "SELECT * FROM article WHERE article.id = ?";
						                $o_query = $o_main->db->query($s_sql, array($subscription_articlenumber1));
						                $article = ($o_query ? $o_query->row_array() : array());
										if($article){
											$vatCode = $article['VatCodeWithVat'];
						                    $bookaccountNr = $article['SalesAccountWithVat'];

											if($vatCode == ""){
						                        $vatCode = $article_accountconfig['defaultVatCodeForArticle'];
						                    }
						                    if($bookaccountNr == ""){
						                        $bookaccountNr = $article_accountconfig['defaultSalesAccountWithVat'];
						                    }

											$s_sql = "INSERT INTO subscriptionline SET
								            created = now(),
								            createdBy= ?,
								            articleOrIndividualPrice= ?,
								            articleNumber= ?,
								            articleName= ?,
								            amount = ?,
								            pricePerPiece = ?,
								            discountPercent= ?,
								            subscribtionId= ?,
				                            cpiAdjustmentFactor = ?,
				                            bookaccountCode = ?,
				                            vatCode = ?";
				                            $o_main->db->query($s_sql, array($user, 1,
											$article['id'], $article['name'], 1, 0, 0,
											$subscriptionmultiId, 100 ,$bookaccountNr, $vatCode));
										}
									}
									if($subscription_articlenumber2 > 0) {
						                $s_sql = "SELECT * FROM article WHERE article.id = ?";
						                $o_query = $o_main->db->query($s_sql, array($subscription_articlenumber2));
						                $article = ($o_query ? $o_query->row_array() : array());
										if($article){
											$vatCode = $article['VatCodeWithVat'];
						                    $bookaccountNr = $article['SalesAccountWithVat'];

											if($vatCode == ""){
						                        $vatCode = $article_accountconfig['defaultVatCodeForArticle'];
						                    }
						                    if($bookaccountNr == ""){
						                        $bookaccountNr = $article_accountconfig['defaultSalesAccountWithVat'];
						                    }

											$s_sql = "INSERT INTO subscriptionline SET
								            created = now(),
								            createdBy= ?,
								            articleOrIndividualPrice= ?,
								            articleNumber= ?,
								            articleName= ?,
								            amount = ?,
								            pricePerPiece = ?,
								            discountPercent= ?,
								            subscribtionId= ?,
				                            cpiAdjustmentFactor = ?,
				                            bookaccountCode = ?,
				                            vatCode = ?";
				                            $o_main->db->query($s_sql, array($user, 1,
											$article['id'], $article['name'], 1, 0, 0,
											$subscriptionmultiId, 100 ,$bookaccountNr, $vatCode));
										}
									}
									if($subscription_articlenumber3 > 0) {
						                $s_sql = "SELECT * FROM article WHERE article.id = ?";
						                $o_query = $o_main->db->query($s_sql, array($subscription_articlenumber3));
						                $article = ($o_query ? $o_query->row_array() : array());
										if($article){
											$vatCode = $article['VatCodeWithVat'];
						                    $bookaccountNr = $article['SalesAccountWithVat'];

											if($vatCode == ""){
						                        $vatCode = $article_accountconfig['defaultVatCodeForArticle'];
						                    }
						                    if($bookaccountNr == ""){
						                        $bookaccountNr = $article_accountconfig['defaultSalesAccountWithVat'];
						                    }

											$s_sql = "INSERT INTO subscriptionline SET
								            created = now(),
								            createdBy= ?,
								            articleOrIndividualPrice= ?,
								            articleNumber= ?,
								            articleName= ?,
								            amount = ?,
								            pricePerPiece = ?,
								            discountPercent= ?,
								            subscribtionId= ?,
				                            cpiAdjustmentFactor = ?,
				                            bookaccountCode = ?,
				                            vatCode = ?";
				                            $o_main->db->query($s_sql, array($user, 1,
											$article['id'], $article['name'], 1, 0, 0,
											$subscriptionmultiId, 100 ,$bookaccountNr, $vatCode));
										}
									}
								}
								$successfullyUpdatedCount++;
							}
						} else {
							$fw_error_msg[] = $formText_ExternalCustomerIdNotFound_output;
						}
					}
				}

				if($foundItem){
					$matches_found[] = $subscription_name;
				} else {
					$nomatches_found[] = $subscription_name;
				}
				if($subscription_invoice_to_other_customer_id > 0) {
					if($internal_error) {
						$other_nomatches_found[] = $subscription_invoice_to_other_customer_id;
					} else {
						$other_matches_found[] = $subscription_invoice_to_other_customer_id;
					}
				}
			}
		} else if($_POST['createProspect'] == 8) {
			$s_sql = "SELECT * FROM article_accountconfig";
			$o_query = $o_main->db->query($s_sql);
			$article_accountconfig = $o_query ? $o_query->row_array() : array();
			if(count($setExtrenal) > 0 || $customerName != "" || $customerOrgNr != ""){
				$ownercompanyIdPost = $_POST['ownercompany'];
				$match_found = false;
				if($_POST['compareBy'] == 0){
					foreach($setExtrenal as $external_sys_id){
						$o_query = $o_main->db->query("SELECT * FROM customer_externalsystem_id WHERE external_id = ? AND ownercompany_id = ?", array($external_sys_id, $ownercompanyIdPost));
						if($o_query && $o_query->num_rows()>0)
						{
							$external_item = $o_query->row_array();
							$o_query = $o_main->db->query("SELECT * FROM customer WHERE id = ?", array($external_item['customer_id']));
							$foundItem = $o_query ? $o_query->row_array() : array();
						}
					}
				} else if($_POST['compareBy'] == 1){
					$foundItemOrganization = array();
					$foundItemCustomer = array();
					$foundItemBoth = array();

					if($customerName != "" && $customerOrgNr != ""){
						$o_query = $o_main->db->query("SELECT * FROM customer WHERE name = ? AND  publicRegisterId = ? AND content_status < 2", array($customerName, $customerOrgNr));
						$foundItemBoth = $o_query ? $o_query->row_array() : array();
					}
					if(!$foundItemBoth){
						if($customerOrgNr != ""){
							$o_query = $o_main->db->query("SELECT * FROM customer WHERE publicRegisterId = ? AND content_status < 2", array($customerOrgNr));
							$foundItemOrganization = $o_query ? $o_query->row_array() : array();
						}
						if(!$foundItemOrganization){
							if($customerName != ""){
								// $search_filter_reg = str_replace(" ", "|", $customerName);
								$o_query = $o_main->db->query("SELECT * FROM customer WHERE name = ? AND content_status < 2", array($customerName));
								$foundItemCustomer = $o_query ? $o_query->row_array() : array();
							}
						}
					}
					if($foundItemBoth) {
						$foundItem = $foundItemBoth;
					} else {
						if($foundItemOrganization) {
							$foundItem = $foundItemOrganization;
						} else {
							$foundItem = $foundItemCustomer;
						}
					}

				}
				if($_POST['subscriptionTypeCompareBy'] > 0){
					$missingSubscription = true;
					if($foundItem){
						$match_found = true;
						$internal_customer_id = 0;
						$internal_error = false;
						if($subscription_invoice_to_other_customer_id > 0) {
							$internal_error = true;
							$o_query = $o_main->db->query("SELECT * FROM customer_externalsystem_id WHERE external_id = ? AND ownercompany_id = ?", array($subscription_invoice_to_other_customer_id, $ownercompanyIdPost));
							if($o_query && $o_query->num_rows()>0)
							{
								$internal_error = false;
								$external_item = $o_query->row_array();
								$internal_customer_id = $external_item['customer_id'];
							}
						}
						$o_query = $o_main->db->query("SELECT * FROM subscriptionmulti WHERE subscriptiontype_id = ? AND customerId = ?",
						array($_POST['subscriptionTypeCompareBy'], $foundItem['id']));
						$subscription = $o_query ? $o_query->row_array() : array();
						if($subscription){
							$missingSubscription = false;
						}
						if($approved) {
							if(!$missingSubscription) {
								if(!$internal_error){
									$sql_update = "";
									if($subscription_start_date != "0000-00-00"){
										$sql_update .= ", startDate = '".date("Y-m-d", strtotime($subscription_start_date))."'";
									}
									if($subscription_nextrenewal_date != "0000-00-00"){
										$sql_update .= ", nextRenewalDate = '".date("Y-m-d", strtotime($subscription_nextrenewal_date))."'";
									}
									if($subscription_name != ""){
										$sql_update .= ", subscriptionName = '".date("Y-m-d", strtotime($subscription_name))."'";
									}
									if($_POST['subscription_period_length']!=""){
										$sql_update .= ", periodNumberOfMonths = '".$_POST['subscription_period_length']."'";
									}
									if($subscription_subtype_id!=""){
										$sql_update .= ", subscriptionsubtypeId = '".$subscription_subtype_id."'";
									}
									if($internal_customer_id!=""){
										$sql_update .= ", invoice_to_other_customer_id = '".$internal_customer_id."'";
									}
									if($subscription_reference!=""){
										$sql_update .= ", reference = '".$subscription_reference."'";
									}
									if($subscription_previous_customer_id!=""){
										$sql_update .= ", previous_customer_id = '".$subscription_previous_customer_id."'";
									}
									if($subscription_previous_customer_name!=""){
										$sql_update .= ", previous_customer_name = '".$subscription_previous_customer_name."'";
									}

									$s_sql  = "UPDATE subscriptionmulti SET updated = NOW(), updatedby = ".$o_main->db->escape("imported - ".$user).$sql_update." WHERE id = ?;";
									if(!$o_main->db->query($s_sql, array($subscription['id']))){
										$fw_error_msg[] = $o_main->db->error();
									} else {
										$subscriptionmultiId = $subscription['id'];
										$successfullyUpdatedCount++;
									}
								} else {
									$fw_error_msg[] = $formText_ExternalCustomerIdNotFound_output;
								}
							} else {
								$fw_error_msg[] = $formText_SubscriptionNotFound_output;
							}
						}
					}
					if($_POST['compareBy'] == 0) {
						if($match_found){
							$matches_found[] = $external_sys_id;
						} else {
							$nomatches_found[] = $external_sys_id;
						}
					} else if($_POST['compareBy'] == 1) {
						if($foundItemCustomer || $foundItemOrganization || $foundItemBoth) {
							if($foundItemCustomer){  $foundItemCustomer['search_org'] = $customerOrgNr; $foundItemCustomer['search_name'] = $customerName; $matches_found['customer_only'][] = $foundItemCustomer;}
							if($foundItemOrganization){ $foundItemOrganization['search_org'] = $customerOrgNr; $foundItemOrganization['search_name'] = $customerName; $matches_found['organization_only'][] = $foundItemOrganization;}
							if($foundItemBoth){ $matches_found['both'][] = $foundItemBoth;}
						} else {
							$nomatches_found[] = array($customerName, $customerOrgNr);
						}
					}
					if($subscription_invoice_to_other_customer_id > 0) {
						if($internal_error) {
							$other_nomatches_found[] = $subscription_invoice_to_other_customer_id;
						} else {
							$other_matches_found[] = $subscription_invoice_to_other_customer_id;
						}
					}
					if($missingSubscription) {
						$subscription_not_matches_found[] = $_POST['subscriptionTypeCompareBy']." - ". $foundItem['id'];
					} else {
						$subscription_matches_found[] = $_POST['subscriptionTypeCompareBy']." - ". $foundItem['id'] - $subscription['id'];
					}
				}
			}
		} else if($_POST['createProspect'] == 5) {
			$match_found = false;
			$contactpersonPrivate = array();
			if($_POST['personGroupCompareBy'] == 0){
				if(count($setExtrenal) > 0){
					$ownercompanyIdPost = $_POST['ownercompany'];

					foreach($setExtrenal as $external_sys_id){
						$o_query = $o_main->db->query("SELECT * FROM customer_externalsystem_id WHERE external_id = ? AND ownercompany_id = ?", array($external_sys_id, $ownercompanyIdPost));
						if($o_query && $o_query->num_rows()>0)
						{
							$external_item = $o_query->row_array();
							$o_query = $o_main->db->query("SELECT * FROM customer WHERE id = ?", array($external_item['customer_id']));
							$foundItem = $o_query ? $o_query->row_array() : array();
							if($foundItem){
								$s_sql = "SELECT * FROM contactperson WHERE customerId = ? AND fullname_for_import_comparing = ?";
								$o_query = $o_main->db->query($s_sql, array($foundItem['id'], $fullname_for_import_comparing));
								$contactpersonPrivate = $o_query ? $o_query->row_array() : array();

							}
						}
					}
				}
			} else if($_POST['personGroupCompareBy'] == 1){
				if($previous_sys_id != ""){
					$s_sql = "SELECT * FROM contactperson WHERE previous_sys_id = ?";
					$o_query = $o_main->db->query($s_sql, array($previous_sys_id));
					$contactpersonPrivate = $o_query ? $o_query->row_array() : array();
					if($contactpersonPrivate){
						$fullname_for_import_comparing = $contactpersonPrivate['name']." ".$contactpersonPrivate['middlename']." ".$contactpersonPrivate['lastname'];
					} else {
						$fullname_for_import_comparing = $previous_sys_id;
					}
				}
			}

			if($contactpersonPrivate){
				$match_found = true;
				if($approved){
					$o_query = $o_main->db->query("INSERT INTO contactperson_group_user SET
						created = NOW(),
						createdBy = ?,
						contactperson_group_id = ?,
						contactperson_id = ?,
						type = 1,
						status = 0", array("imported - ".$user, $_POST['group_id'], $contactpersonPrivate['id']));
					if(!$o_query){
						$fw_error_msg[] = $o_main->db->error();
					} else {
						$successfullyUpdatedCount++;
					}
				}
			}
			if($match_found){
				$matches_found[] = $fullname_for_import_comparing;
			} else {
				$nomatches_found[] = $fullname_for_import_comparing;
			}
		} else if($_POST['createProspect'] == 6) {
			$ownercompanyIdPost = $_POST['ownercompany'];

			if($_POST['compareBy'] == 0){
				foreach($setExtrenal as $external_sys_id){
					$o_query = $o_main->db->query("SELECT * FROM customer_externalsystem_id WHERE external_id = ? AND ownercompany_id = ?", array($external_sys_id, $ownercompanyIdPost));
					if($o_query && $o_query->num_rows()>0)
					{
						$external_item = $o_query->row_array();
						$o_query = $o_main->db->query("SELECT * FROM customer WHERE id = ?", array($external_item['customer_id']));
						$foundItem = $o_query ? $o_query->row_array() : array();
					}
				}
			} else {
				$foundItemOrganization = array();
				$foundItemCustomer = array();
				$foundItemBoth = array();
				$foundItemSys = array();
				if($_POST['compareBy'] == 2) {
					if($customerPreviousId != "") {
						$o_query = $o_main->db->query("SELECT * FROM customer WHERE previous_sys_id = ? AND content_status < 2", array($customerPreviousId));
						$foundItemSys = $o_query ? $o_query->row_array() : array();
					}
				}
				if(!$foundItemSys) {
					if($customerName != "" && $customerOrgNr != ""){
						$o_query = $o_main->db->query("SELECT * FROM customer WHERE name = ? AND  publicRegisterId = ? AND content_status < 2", array($customerName, $customerOrgNr));
						$foundItemBoth = $o_query ? $o_query->row_array() : array();
					}
				}
				if(!$foundItemBoth && !$foundItemSys){
					if($customerOrgNr != ""){
						$o_query = $o_main->db->query("SELECT * FROM customer WHERE publicRegisterId = ? AND content_status < 2", array($customerOrgNr));
						$foundItemOrganization = $o_query ? $o_query->row_array() : array();
					}
					if(!$foundItemOrganization){
						if($customerName != ""){
							// $search_filter_reg = str_replace(" ", "|", $customerName);
							$o_query = $o_main->db->query("SELECT * FROM customer WHERE name = ? AND content_status < 2", array($customerName));
							$foundItemCustomer = $o_query ? $o_query->row_array() : array();
						}
					}
				}
				if($foundItemSys){
					$foundItem = $foundItemSys;
				} else if($foundItemBoth) {
					$foundItem = $foundItemBoth;
				} else {
					if($foundItemOrganization) {
						$foundItem = $foundItemOrganization;
					} else {
						$foundItem = $foundItemCustomer;
					}
				}
			}
			if($foundItem){
				if($approved){
					foreach($setSelfdefined as $selfdefinedRow){
						foreach($selfdefinedRow as $selfdefinedId => $sefdefinedValue){
							$o_query = $o_main->db->query("SELECT * FROM customer_selfdefined_fields WHERE id = ?", array($selfdefinedId));
							$selfdefinedField = $o_query ? $o_query->row_array() : array();
							$list_id = 0;
							$valueCheck = 0;
							$valueString = "";

							if($selfdefinedField['type'] == 2){
								$valueCheck = 0;
								$valueString = "";
							} else {
								if(strtolower(trim($sefdefinedValue)) == "x"){
									$valueCheck = 1;
								} else {
									$valueCheck = 1;
									$valueString = $sefdefinedValue;
								}
							}


							$o_main->db->query("insert into callbacklog set log = ?, sessionID = ?", array("Steg 1, customerID = ".$foundItem['id']." selfdefined field id =".$selfdefinedId, "1111"));
							$o_query = $o_main->db->query("SELECT * FROM customer_selfdefined_values WHERE customer_id = ? AND selfdefined_fields_id = ?", array($foundItem['id'], $selfdefinedId));
							if($o_query && $o_query->num_rows()>0)
							{
								$selfdefinedFieldValue = $o_query->row_array();
								$o_query = $o_main->db->query("UPDATE customer_selfdefined_values SET value = ?, active = ? WHERE id = ?", array($valueString, $valueCheck, $selfdefinedFieldValue['id']));
								$selfdefinedFieldValueId = $selfdefinedFieldValue['id'];
							} else {
								$o_query = $o_main->db->query("INSERT INTO customer_selfdefined_values SET customer_id = ?, selfdefined_fields_id = ?, value = ?, active = ?", array($foundItem['id'], $selfdefinedId, $valueString, $valueCheck));
								if($o_query){
									$selfdefinedFieldValueId = $o_main->db->insert_id();
								}
							}
							if(!$o_query){
								$fw_error_msg[] = $o_main->db->error();
							} else {
								if($selfdefinedField['type'] == 2){
									if($selfdefinedFieldValueId > 0){
										$o_query = $o_main->db->query("SELECT * FROM customer_selfdefined_values_connection WHERE selfdefined_value_id = ? AND selfdefined_list_line_id = ?", array($selfdefinedFieldValueId, $sefdefinedValue));
										if($o_query && $o_query->num_rows()>0)
										{
											$selfdefinedFieldConValue = $o_query->row_array();
											$o_query = $o_main->db->query("UPDATE customer_selfdefined_values_connection SET selfdefined_value_id = ?, selfdefined_list_line_id = ? WHERE id = ?", array($selfdefinedFieldValueId, $sefdefinedValue, $selfdefinedFieldConValue['id']));
											$selfdefinedFieldValueId = $selfdefinedFieldValue['id'];
										} else {
											$o_query = $o_main->db->query("INSERT INTO customer_selfdefined_values_connection SET selfdefined_value_id = ?, selfdefined_list_line_id = ?", array($selfdefinedFieldValueId, $sefdefinedValue));

										}
									}
								}
								$successfullyUpdatedCount++;
							}
						}
					}
				}
			}

		} else if($_POST['createProspect'] == 7) {
			$sql_join = " LEFT OUTER JOIN customer c ON c.id = cp.customerId";
			$sql_where = "";
			if($_POST['newsletter_check_member']) {
				$sql_join .= " LEFT OUTER JOIN subscriptionmulti s ON s.customerId = cp.customerId ";
				$sql_where .= " AND s.id IS NOT NULL AND s.startDate <= NOW() AND ((s.stoppedDate is not null AND s.stoppedDate <> '0000-00-00' AND s.stoppedDate > NOW()) OR (s.stoppedDate = '0000-00-00' OR s.stoppedDate is null))";
			}
			$s_sql = "SELECT cp.*, CONCAT_WS(' ',c.name, c.middlename, c.lastname) as customerName FROM contactperson cp
			".$sql_join."
			WHERE cp.fullname_for_import_comparing = ? AND cp.email = ?".$sql_where;
			$o_query = $o_main->db->query($s_sql, array($fullname_for_import_comparing, $newsletter_email));
			$contactpersonPrivate = $o_query ? $o_query->row_array() : array();

			if($contactpersonPrivate){
				$newsletter_full_found[] = array($newsletter_email, $fullname_for_import_comparing, $contactpersonPrivate['customerName']);
			} else {
				$s_sql = "SELECT cp.*, CONCAT_WS(' ',c.name, c.middlename, c.lastname) as customerName FROM contactperson cp
				".$sql_join."
				WHERE cp.email = ?".$sql_where;
				$o_query = $o_main->db->query($s_sql, array($newsletter_email));
				$contactpersonPrivate = $o_query ? $o_query->row_array() : array();
				if($contactpersonPrivate){
					$newsletter_email_found[] = array($newsletter_email, $fullname_for_import_comparing, $contactpersonPrivate['customerName']);

				} else {
					$s_sql = "SELECT cp.*, CONCAT_WS(' ',c.name, c.middlename, c.lastname) as customerName FROM contactperson cp
					".$sql_join."
					WHERE cp.fullname_for_import_comparing = ? AND (cp.email = '' OR cp.email is null)".$sql_where;
					$o_query = $o_main->db->query($s_sql, array($fullname_for_import_comparing));
					$contactpersonPrivate = $o_query ? $o_query->row_array() : array();
					if($contactpersonPrivate){
						$newsletter_partial_found[] = array($newsletter_email, $fullname_for_import_comparing, $contactpersonPrivate['customerName'], $rowNumber);
						if($approved) {
							$createSubscriber = $_POST['newsletter_choice'][$rowNumber];
							if($createSubscriber == 1) {
								$o_query = $o_main->db->query("INSERT INTO sys_email_subscriber SET
									created = NOW(),
									createdBy = ?,
									subscriberlist_id = ?,
									name = ?,
									email = ?,
									content_status = 0", array("imported - ".$user, $_POST['newsletter_list'], $fullname_for_import_comparing, $newsletter_email));
								if(!$o_query){
									$fw_error_msg[] = $o_main->db->error();
								} else {
									$successfullyUpdatedCount++;
								}
							} else {
								$o_query = $o_main->db->query("UPDATE contactperson SET
									created = NOW(),
									createdBy = ?,
									email = ?
									WHERE id = ?", array("imported - ".$user, $newsletter_email, $contactpersonPrivate['id']));
								if(!$o_query){
									$fw_error_msg[] = $o_main->db->error();
								} else {
									$successfullyUpdatedCount++;
								}
							}
						}
					} else {
						$newsletter_not_found[] = array($newsletter_email, $fullname_for_import_comparing);
						if($approved) {
							$o_query = $o_main->db->query("INSERT INTO sys_email_subscriber SET
								created = NOW(),
								createdBy = ?,
								subscriberlist_id = ?,
								name = ?,
								email = ?,
								content_status = 0", array("imported - ".$user, $_POST['newsletter_list'], $fullname_for_import_comparing, $newsletter_email));
							if(!$o_query){
								$fw_error_msg[] = $o_main->db->error();
							} else {
								$successfullyUpdatedCount++;
							}
						}
					}
				}
			}

		} else if($_POST['createProspect'] == 9) {
			if(count($setExtrenal) > 0){
				$ownercompanyIdPost = $_POST['ownercompany'];
				$match_found = false;
				if(intval($_POST['compareBy']) == 0){
					foreach($setExtrenal as $external_sys_id){
						$o_query = $o_main->db->query("SELECT * FROM customer_externalsystem_id WHERE external_id = ? AND ownercompany_id = ?", array($external_sys_id, $ownercompanyIdPost));
						if($o_query && $o_query->num_rows()>0)
						{
							$external_item = $o_query->row_array();
							$o_query = $o_main->db->query("SELECT * FROM customer WHERE id = ?", array($external_item['customer_id']));
							$foundItem = $o_query ? $o_query->row_array() : array();
						}
					}
				}
			}
			if($foundItem){
				$matches_found[] = $external_sys_id;

				if($approved) {
					$o_query = $o_main->db->query("INSERT INTO customer_subunit SET
						created = NOW(),
						createdBy = ?,
						id = ?,
						name = ?,
						customer_id = ?", array("imported - ".$user, $subunitId, $subunitName, $foundItem['id']));
					if(!$o_query){
						$fw_error_msg[] = $o_main->db->error();
					} else {
						$successfullyUpdatedCount++;
					}
				}
			} else {
				$nomatches_found[] = $external_sys_id;
			}
		} else {
            $o_main->db->query("insert into callbacklog set log = ?, sessionID = ?", array("IFELSE test 1 count set = ".count($set). " set = ".json_encode($set), "1111"));
			if(count($set) > 3){
				$set[] = "moduleID = 41";
				$insertCustomer = false;
				$insertOwnerCompanyNr = false;
				$customerFoundId = 0;
				if($_POST['checkForDuplicates']) {
					$foundItemCustomerNr = array();
					$o_query = $o_main->db->query("SELECT * FROM customer WHERE name = ? AND  IFNULL(publicRegisterId, '') = ? AND content_status < 2 AND IFNULL(origId, '') <> '".$o_main->db->escape_str($currentProcessTime)."'", array($customerName, $customerOrgNr));
					$foundItem = $o_query ? $o_query->row_array() : array();
					if($foundItem) {
						$ownercompanyIdPost = $_POST['ownercompany'];
						foreach($setExtrenal as $external_sys_id){
							$o_query = $o_main->db->query("SELECT * FROM customer_externalsystem_id WHERE external_id = ? AND ownercompany_id = ?", array($external_sys_id, $ownercompanyIdPost));
							if($o_query && $o_query->num_rows()>0)
							{
								$external_item = $o_query->row_array();
								$o_query = $o_main->db->query("SELECT * FROM customer WHERE id = ?", array($external_item['customer_id']));
								$foundItemCustomerNr = $o_query ? $o_query->row_array() : array();
							}
						}
						if($foundItemCustomerNr){
							$matches_found['both'][] = $foundItemCustomerNr;
						} else {
							$matches_found['only_customer'][] = $foundItem;
						}
					} else {
						$nomatches_found[] = array($customerName, $customerOrgNr);
					}

					if($approved){
						if($foundItem && !$foundItemCustomerNr){
							$insertOwnerCompanyNr = true;
							$customerId = $foundItem['id']; // needed for ownercompany insert
						} else if(!$foundItem) {
							$insertCustomer = true;
							$insertOwnerCompanyNr = true;
						}
						if($foundItem){
							$customerFoundId = $foundItem['id'];
						}
						if($foundItemCustomerNr){
							$customerFoundId = $foundItemCustomerNr['id'];
						}
					}
				} else {
					$insertCustomer = true;
					$insertOwnerCompanyNr = true;
				}
				if($insertCustomer) {
					$insertTable = $o_main->db_escape_name($insertTable);
					$idField = $o_main->db_escape_name($idField);
					$o_query = $o_main->db->query("SELECT * FROM ".$insertTable." WHERE ".$idField." = ?", array($idValue));
					$foundItem = $o_query ? $o_query->row_array() : array();
					$customerId = 0;
					$createNewCustomer = true;
					// if($_POST['notAllowDuplicatePublicRegisterId']){
					// 	if($foundItem){
					// 		$createNewCustomer = false;
					// 	} else {
					// 		$createNewCustomer = true;
					// 	}
					// } else {
					// 	$createNewCustomer = true;
					// }

					if($approved){
						if($_POST['marked_for_manual_check']){
							$set[] = 'marked_for_manual_check = 1';
						}
						if($createNewCustomer){
							$set[] = 'origId = "'.$o_main->db->escape_str($currentProcessTime).'"';
							$s_sql  = "INSERT INTO ".$insertTable." SET ".implode(", ", $set).";";
							if(!$o_main->db->query($s_sql)) die($o_main->db->error());
							$customerId = $o_main->db->insert_id();
							$successfullyUpdatedCount++;
						} else {
							$foundItem = $o_query->row_array();
							$customerId = $foundItem['id'];
						}
						if($customerId > 0) {
							if(count($set2) > 2){
								$set2[] = "type = 1";
								$set2[] = "customerId = ".$customerId;
								$s_sql = "SELECT * FROM contactperson WHERE customerId = ? AND email = ?";
								$o_query = $o_main->db->query($s_sql, array($customerId, $contactpersonEmail));
								$contactpersonPrivate = $o_query ? $o_query->row_array() : array();
								if(!$contactpersonPrivate) {
									$s_sql  = "INSERT INTO contactperson SET ".implode(", ", $set2).";";
									$o_query = $o_main->db->query($s_sql);
									if(!$o_query){
										$fw_error_msg[] = $o_main->db->error();
									} else {
									}
								}
							}

							foreach($setSelfdefined as $selfdefinedRow){
								foreach($selfdefinedRow as $selfdefinedId => $sefdefinedValue){

									$o_query = $o_main->db->query("SELECT * FROM customer_selfdefined_fields WHERE id = ?", array($selfdefinedId));
									$selfdefinedField = $o_query ? $o_query->row_array() : array();

									$list_id = 0;
									$valueCheck = 0;
									$valueString = "";

									if($selfdefinedField['type'] == 2){
										$valueCheck = 0;
										$valueString = "";
									} else {
										if(strtolower(trim($sefdefinedValue)) == "x"){
											$valueCheck = 1;
										} else {
											$valueCheck = 1;
											$valueString = $sefdefinedValue;
										}
									}
									$o_main->db->query("insert into callbacklog set log = ?, sessionID = ?", array("Steg 2, customerID = ".$customerId." selfdefined field id =".$selfdefinedId, "1111"));
									$o_query = $o_main->db->query("SELECT * FROM customer_selfdefined_values WHERE customer_id = ? AND selfdefined_fields_id = ?", array($customerId, $selfdefinedId));
									if($o_query && $o_query->num_rows()>0)
									{
										$selfdefinedFieldValue = $o_query->row_array();
										$o_query = $o_main->db->query("UPDATE customer_selfdefined_values SET value = ?, active = ? WHERE id = ?", array($valueString, $valueCheck, $selfdefinedFieldValue['id']));
										$selfdefinedFieldValueId = $selfdefinedFieldValue['id'];
									} else {
										$o_query = $o_main->db->query("INSERT INTO customer_selfdefined_values SET customer_id = ?, selfdefined_fields_id = ?, value = ?, active = ?", array($customerId, $selfdefinedId, $valueString, $valueCheck));
										if($o_query){
											$selfdefinedFieldValueId = $o_main->db->insert_id();
										}
									}
									if(!$o_query){
										$fw_error_msg[] = $o_main->db->error();
									} else {
										if($selfdefinedField['type'] == 2){
											if($selfdefinedFieldValueId > 0){
												$o_query = $o_main->db->query("SELECT * FROM customer_selfdefined_values_connection WHERE selfdefined_value_id = ? AND selfdefined_list_line_id = ?", array($selfdefinedFieldValueId, $sefdefinedValue));
												if($o_query && $o_query->num_rows()>0)
												{
													$selfdefinedFieldConValue = $o_query->row_array();
													$o_query = $o_main->db->query("UPDATE customer_selfdefined_values_connection SET selfdefined_value_id = ?, selfdefined_list_line_id = ? WHERE id = ?", array($selfdefinedFieldValueId, $sefdefinedValue, $selfdefinedFieldConValue['id']));
													$selfdefinedFieldValueId = $selfdefinedFieldValue['id'];
												} else {
													$o_query = $o_main->db->query("INSERT INTO customer_selfdefined_values_connection SET selfdefined_value_id = ?, selfdefined_list_line_id = ?", array($selfdefinedFieldValueId, $sefdefinedValue));

												}
											}
										}
									}

								}
							}
						}
					}
					if($_POST['createProspect'] == 1) {
						$prospectType = $_POST['prospectType'];
						$employeeId = $_POST['employeeId'];
						if($prospectType != "" && $employeeId != "") {
							if($approved){
								$s_sql = "INSERT INTO prospect SET
								id=NULL,
								moduleID = ?,
								created = now(),
								createdBy= ?,
								customerId = ?,
								prospecttypeId = ?,
								employeeId = ?,
								value=?,
								info = ?";
								$o_query = $o_main->db->query($s_sql, array($moduleID, $variables->loggID, $customerId, $prospectType, $employeeId, $prospectValue, $prospectInfo));

								if(!$o_query){
									$fw_error_msg[] = $o_main->db->error();
								} else {
								}
							}
						}
					}
				}

				if($insertOwnerCompanyNr) {
					if($_POST['ownercompany'] > 0) {
						$ownercompanyIdPost = $_POST['ownercompany'];
						foreach($setExtrenal as $external_sys_id){
							$o_query = $o_main->db->query("SELECT * FROM customer_externalsystem_id WHERE external_id = ? AND ownercompany_id = ?", array($external_sys_id, $ownercompanyIdPost));
							if($o_query && $o_query->num_rows()>0)
							{
								$selfdefinedFieldValue = $o_query->row_array();
								$o_query = $o_main->db->query("UPDATE customer_externalsystem_id SET updated = NOW(),  external_id = ?, ownercompany_id = ?, customer_id = ? WHERE id = ?", array($external_sys_id, $ownercompanyIdPost, $customerId, $selfdefinedFieldValue['id']));
							} else {
								$o_query = $o_main->db->query("INSERT INTO customer_externalsystem_id SET created = NOW(),  external_id = ?, ownercompany_id = ?, customer_id = ?", array($external_sys_id, $ownercompanyIdPost, $customerId));
							}
							if(!$o_query){
								$fw_error_msg[] = $o_main->db->error();
							} else {

								if(!$insertCustomer){
									$successfullyUpdatedCount++;
								}
							}
						}
					}
				}

				if($_POST['checkForDuplicates']) {
					if($_POST['applyPreviousSysId']) {
						$o_query = $o_main->db->query("UPDATE customer SET updated = NOW(), previous_sys_id = ? WHERE id = ?", array($previous_customer_sys_id, $customerFoundId));
					}
				}
			}
		}
		unset($set);
		unset($set2);
		unset($setSelfdefined);
		unset($setExtrenal);
		unset($setProspect);
		$rowNumber++;
	}

	?>
	<div class="tableOverview" style="overflow: auto; height: 300px; margin-bottom: 10px;">
		<table class="table">
			<tr>
				<?php
				foreach($headersToShow as $header){
					$activeRelation = "";
					foreach($relation as $dbField=>$csvField) {
						$csvField = trim($csvField);
						if($csvField == $header){
							$activeRelation = $dbField;
						}
					}
					?>
					<th <?php if($activeRelation != "") echo 'style="background: #fcf8e3;"';?>><?php echo $header?> <?php if($activeRelation != "") echo '('.$activeRelation.')'?></th>
				<?php } ?>
			</tr>
			<?php
			foreach($csv as $row){
				?>
				<tr>
					<?php
					foreach($row as $keyItem => $rowValue){
						$activeRelation = "";
						foreach($relation as $dbField=>$csvField) {
							$csvField = trim($csvField);
							if($csvField == $keyItem){
								$activeRelation = $dbField;
							}
						}
						?>
						<td <?php if($activeRelation != "") echo 'style="background: #fcf8e3;"';?>><?php
							 echo $rowValue?></td>
						<?php
					}
					?>
				</tr>
				<?php
				//break;
			}
			?>
		</table>
	</div>
	<?php
}

if($developeraccess >= 5) {
	?>
	<style>
	.labelSpan {
		display: inline-block;
		width: 140px;
	}
	#importForm2 {
		width: 800px;
		min-height: 300px;
	}
	.half {
		width: 50%;
		float: left;
	}
	.half1 {
		width: 45%;
		float: left;
	}
	.half2 {
		width: 55%;
		float: left;
	}
	#csvimportfields2 {
		*display: none;
	}

	#dbFields2 {
		min-height: 100px;
		width: 200px;
	}
	#dbFields2:after{
		display:block;
		content:"";
		clear:both;
	}
	#dbFields2 div {
		float: none;
		width: inherit;
		text-indent: 5px;
	}
	.draggable { cursor: move; }
	.droppable div { border: 1px dotted black; margin: 2px; background: #EEE; }
	.draggable { height: 20px; line-height: 20px; text-indent: 5px; }
	.label {
		display: none;
	}
	div.label {
		position: absolute;
		top: 0px;
		right: 0px;
		font-size: 9px;
		width: 180px;
		display: none;
	}
	#csvimportfields2 .droppable {
		width: 90%;
		clear: both;
		border: 1px dotted black; margin: 2px 0px;
		min-height: 26px;
		position: relative;

	}
	#csvimportfields2 .draggable {
		background: #DDD;
		float: right;
		width: 200px;
	}
	.prospectTypesSelect {
		display: none;
	}
	.employeeIdSelect {
		display: none;
	}
	.prospectValue {
		display: none;
	}
	.subscriptionOnlyFields {
		display: none;
	}
	.subscriptionUpdateOnlyFields {
		display: none;
	}
	.compareOrganizationAndName {
		display: none;
	}
	.comparingPreviousCustomerId {
		display: none;
	}
	.popupElement {
		display: none;
	}
	.showElementsInPopup {
		cursor: pointer;
	}
	</style>


	<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
	<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/encoding.min.js"></script>
	<script>
	$(".fancybox-wrap").unbind('mousewheel.fb');
	function generateDragable2(f,s) {
		$('#importForm2 #csvimportfields2 .draggable').remove();
		if(s == "t"){
			fields = f.split("\t");
		} else{
			fields = f.split(s);
		}
		$("#importForm2 #dbFields2").html('');
		$.each(fields , function(i, val) {
			if(val.trim() != ""){
				val = val.replaceAll('"', '');
				$("#importForm2 #dbFields2").append(' <div class="draggable" id="field_'+val+'" data-scope="'+val+'">'+val+'</div>');
			}
		});

		$('.draggable').each(function(index, div) {
			var scope = $(this).attr('data-scope');
			$(div).draggable({

				stop: function() {
					$('.droppable').droppable('option', 'disabled', false);
				},

				helper: 'clone'
			});
		});

		$('.draggable').on('mousedown', function(e) {

			$('.droppable').droppable({

				drop: function(event, ui) {

					var x = $(this).find('.draggable');

					if(!$(this).attr('id')==='dbFields2'){
						if(!x.length){
							$(this).append(ui.draggable);
						}else{

						}
					}else{
						$('#importForm2 #dbFields2').append($('.ui-draggable', this));

						$(this).append(ui.draggable);
						$(this).find('div.label').hide();

					}
				}
			});
		});
		<?php foreach($_POST['field'] as $key=>$value) {
			if(trim($value) != ""){
			?>
			$('#dbFields2 .draggable[data-scope="<?php echo $value?>"]').eq(0).appendTo($("#csv_<?php echo $key;?>"));
		<?php }
		}
	 	?>
		$(window).resize();
	}

	$(document).ready(function () {
		// $(".subscription_start_date").datepicker({
		// 	firstDay: 1,
        //     dateFormat: 'dd.mm.yy',
		// })
		var spliter = ",";

		$("#cancelImportButton2").off("click").on("click", function(e){
			e.preventDefault();
			$(".output_form_submit").val("0");
			$(".approved").val("0");
			$("form.output-form").submit();
		})
		$("#importForm2 #separator").change(function(e) {
			$("#importForm2 #spliter").val( $(this).val() );
			spliter = $(this).val();
			generateDragable2($('#importForm2 input[name="csvFields"]').val(), spliter);
		}).change();

		$("#importForm2 #filenameImport2").change(function(e) {
			var ext = $("#importForm2 input#filenameImport2").val().split(".").pop().toLowerCase();
			if($.inArray(ext, ["csv"]) == -1) {
				alert('Please upload CSV file!');
				return false;
			}

			if (e.target.files != undefined) {
				var file = e.target.files.item(0)
				var reader = new FileReader();
				reader.onload = function(e) {
					var codes = new Uint8Array(e.target.result);
					var encoding = Encoding.detect(codes);
					if(encoding == "UTF8" || encoding == "UNICODE" || encoding == "UTF32"){
						var reader2 = new FileReader();
						reader2.onload = function(e) {
							$('#importForm2 input[name="csv"]').val(e.target.result);
							var csvval=e.target.result.split("\n");
							var csvvalue=csvval[0];
							$('#importForm2 input[name="csvFields"]').val(csvvalue);
							generateDragable2($('#importForm2 input[name="csvFields"]').val(), spliter);
						};
						reader2.readAsText(file);
					} else {
						alert("<?php echo $formText_PossibleToUploadOnlyFilesInUTF8Encoding_output;?>");
					}
				};
				reader.readAsArrayBuffer(file);

			}
			$('#importForm2 #importButton2').prop("disabled", false);
			$('#importForm2 #csvimportfields2 a.label').show();

			return false;
		});

        $("form.output-form").validate({
    		submitHandler: function(form) {
    			fw_loading_start();

                $('#importForm2 #csvimportfields2 .boxes input.fieldsToCheck').each(function(index, div) {
    				if(!$(this).val() || $(this).parent().parent().find('.draggable').length) {
    					$(this).remove();
    				}
    			});

    			$('#importForm2 #csvimportfields2 .droppable').each(function(index, div) {
    				var csvField = $(this).attr('id').replace('csv_','');
    				var dbFiled = 0;
    				if($(this).find('.draggable').length) {
    					dbFiled = $(this).find('.draggable').attr('id').replace('field_','');
    					if($(this).hasClass("contactPerson1")){
    						$('#importForm2 input[name="field[contactperson1_'+csvField+']"]').val(dbFiled);
    					} else if ($(this).hasClass("contactPerson2")) {
    						$('#importForm2 input[name="field[contactperson2_'+csvField+']"]').val(dbFiled);
    					} else if ($(this).hasClass("ownerFromDate")) {
    						$('#importForm2 input[name="field['+csvField+']"]').val(dbFiled);
    					} else if ($(this).hasClass("selfdefined")) {
    						$('#importForm2 input[name="field['+csvField+']"]').val(dbFiled);
    					} else if ($(this).hasClass("customerNumber")) {
    						$('#importForm2 input[name="field[customerNumber]"]').val(dbFiled);
    					} else {
    						$('#importForm2 input[name="field['+csvField+']"]').val(dbFiled);
    					}

    				}
    			});
				$("#popup-validate-message").hide();
    			$.ajax({
    				url: $(form).attr("action"),
    				cache: false,
    				type: "POST",
    				dataType: "json",
    				data: $(form).serialize(),
    				success: function (data) {
    					fw_loading_end();
    					if(data.error !== undefined)
    					{
    						$.each(data.error, function(index, value){
    							var _type = Array("error");
    							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
								$("#popup-validate-message").html(value, true);
    						});
		    				$("#popup-validate-message").show();
    						fw_click_instance = fw_changes_made = false;
    					} else {
							$('#popupeditboxcontent').html('');
							$('#popupeditboxcontent').html(data.html);
							out_popup = $('#popupeditbox').bPopup(out_popup_options);
							$("#popupeditbox:not(.opened)").remove();
    					}
    				}
    			}).fail(function() {
    				$("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
    				$("#popup-validate-message").show();
    				$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
    				fw_loading_end();
    			});
    		},
    		invalidHandler: function(event, validator) {
    			var errors = validator.numberOfInvalids();
    			if (errors) {
    				var message = errors == 1
    				? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
    				: '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

    				$("#popup-validate-message").html(message);
    				$("#popup-validate-message").show();
    				$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
    			} else {
    				$("#popup-validate-message").hide();
    			}
    			setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
    		}
    	});

		$(".createProspectSelect").change(function(){
			var mainFields = $("#csvimportfields2");

			$(".ownercompanySelectWrapper").show();

			mainFields.find(".fieldWrapper").hide();
			mainFields.find(".fieldWrapper select").prop("required", false);
			mainFields.find(".fieldWrapper input:not(.fieldsToCheck):not([type='checkbox'])").prop("required", false);

			// $("#csvimportfields2 select").prop("required", false);
			// $("#csvimportfields2 input:not(.fieldsToCheck):not([type='checkbox'])").prop("required", false);
			mainFields.find(".ownercompanyWrapper").show();

			var value = $(this).val();
			if(value == 1) {
				mainFields.find(".subscriptionWrapper").hide();
				mainFields.find(".subscriptionWrapper select").prop("required", false);
				mainFields.find(".subscriptionWrapper input:not(.fieldsToCheck):not([type='checkbox'])").prop("required", false);

				mainFields.find(".groupConnectionWrapper").hide();
				mainFields.find(".groupConnectionWrapper select").prop("required", false);

				mainFields.find(".prospectTypesSelect").show();
				mainFields.find(".employeeIdSelect").show();
				mainFields.find(".prospectValue").show();
				mainFields.find(".prospectTypesSelect .prospectType").prop("required", true);
				mainFields.find(".employeeIdSelect .employeeId").prop("required", true);
				mainFields.find(".insertWrapper").show();
				mainFields.find(".notCpImportWrapper").show();
			} else if(value == 2) {
				mainFields.find(".subscriptionWrapper").hide();
				mainFields.find(".subscriptionWrapper select").prop("required", false);
				mainFields.find(".subscriptionWrapper input:not(.fieldsToCheck):not([type='checkbox'])").prop("required", false);

				mainFields.find(".groupConnectionWrapper").hide();
				mainFields.find(".groupConnectionWrapper select").prop("required", false);

				mainFields.find(".prospectTypesSelect").hide();
				mainFields.find(".employeeIdSelect").hide();
				mainFields.find(".prospectValue").hide();
				mainFields.find(".prospectTypesSelect .prospectType").prop("required", false);
				mainFields.find(".employeeIdSelect .employeeId").prop("required", false);
				mainFields.find(".insertWrapper").hide();
				mainFields.find(".notCpImportWrapper").show();
			} else if(value == 3){
				mainFields.find(".subscriptionWrapper").hide();
				mainFields.find(".subscriptionWrapper select").prop("required", false);
				mainFields.find(".subscriptionWrapper input:not(.fieldsToCheck):not([type='checkbox'])").prop("required", false);

				mainFields.find(".groupConnectionWrapper").hide();
				mainFields.find(".groupConnectionWrapper select").prop("required", false);

				mainFields.find(".insertWrapper").hide();
				mainFields.find(".prospectTypesSelect").hide();
				mainFields.find(".employeeIdSelect").hide();
				mainFields.find(".prospectValue").hide();
				mainFields.find(".prospectTypesSelect .prospectType").prop("required", false);
				mainFields.find(".employeeIdSelect .employeeId").prop("required", false);

				mainFields.find(".notCpImportWrapper").hide();
				mainFields.find(".cpWrapper").show();
			} else if(value == 4) {
				mainFields.find(".groupConnectionWrapper").hide();
				mainFields.find(".groupConnectionWrapper select").prop("required", false);

				mainFields.find(".insertWrapper").hide();
				mainFields.find(".prospectTypesSelect").hide();
				mainFields.find(".employeeIdSelect").hide();
				mainFields.find(".prospectValue").hide();
				mainFields.find(".prospectTypesSelect .prospectType").prop("required", false);
				mainFields.find(".employeeIdSelect .employeeId").prop("required", false);
				mainFields.find(".notCpImportWrapper").hide();

				mainFields.find(".subscriptionWrapper").show();
				mainFields.find(".subscriptionWrapperInsertOnly").show();
				mainFields.find(".subscriptionWrapper select").prop("required", true);
				mainFields.find(".subscriptionWrapper input:not(.fieldsToCheck):not([type='checkbox'])").prop("required", true);
			} else if(value == 8) {
				mainFields.find(".groupConnectionWrapper").hide();
				mainFields.find(".groupConnectionWrapper select").prop("required", false);

				mainFields.find(".insertWrapper").hide();
				mainFields.find(".prospectTypesSelect").hide();
				mainFields.find(".employeeIdSelect").hide();
				mainFields.find(".prospectValue").hide();
				mainFields.find(".prospectTypesSelect .prospectType").prop("required", false);
				mainFields.find(".employeeIdSelect .employeeId").prop("required", false);
				mainFields.find(".notCpImportWrapper").hide();

				mainFields.find(".subscriptionWrapper").show();
				mainFields.find(".subscriptionWrapperInsertOnly").hide();
				mainFields.find(".subscriptionWrapper select").prop("required", true);
				mainFields.find(".subscriptionWrapper input:not(.fieldsToCheck):not([type='checkbox'])").prop("required", true);

				mainFields.find(".subscriptionWrapper input.subscription_period_length ").prop("required", false);

			} else if(value == 5){
				mainFields.find(".subscriptionWrapper").hide();
				mainFields.find(".subscriptionWrapper select").prop("required", false);
				mainFields.find(".subscriptionWrapper input:not(.fieldsToCheck):not([type='checkbox'])").prop("required", false);

				mainFields.find(".insertWrapper").hide();
				mainFields.find(".prospectTypesSelect").hide();
				mainFields.find(".employeeIdSelect").hide();
				mainFields.find(".prospectValue").hide();
				mainFields.find(".prospectTypesSelect .prospectType").prop("required", false);
				mainFields.find(".employeeIdSelect .employeeId").prop("required", false);
				mainFields.find(".notCpImportWrapper").hide();

				mainFields.find(".groupConnectionWrapper").show();
				mainFields.find(".groupConnectionWrapper select").prop("required", true);
			} else if(value == 6) {
				mainFields.find(".groupConnectionWrapper").hide();
				mainFields.find(".groupConnectionWrapper select").prop("required", false);

				mainFields.find(".fieldWrapper").hide();
				mainFields.find(".fieldWrapper select").prop("required", false);
				mainFields.find(".fieldWrapper input:not(.fieldsToCheck):not([type='checkbox'])").prop("required", false);

				mainFields.find(".ownercompanyWrapper").show();
				mainFields.find(".selfdefinedWrapper").show();
				mainFields.find(".selfdefinedWrapper select").prop("required", true);
				mainFields.find(".selfdefinedWrapper input:not(.fieldsToCheck):not([type='checkbox'])").prop("required", true);
			} else if(value == 7) {
				mainFields.find(".fieldWrapper").hide();
				mainFields.find(".fieldWrapper select").prop("required", false);
				mainFields.find(".fieldWrapper input:not(.fieldsToCheck):not([type='checkbox'])").prop("required", false);

				mainFields.find(".newsletterWrapper").show();
				mainFields.find(".newsletterWrapper select").prop("required", true);
				mainFields.find(".newsletterWrapper input:not(.fieldsToCheck):not([type='checkbox'])").prop("required", true);
			} else if(value == 9) {

				mainFields.find(".subunitWrapper").show();
				mainFields.find(".subunitWrapper select").prop("required", true);
			} else if(value == 10) {
				mainFields.find(".subscriptionWrapper").show();
				mainFields.find(".subscriptionWrapperInsertOnly").show();
				mainFields.find(".subscriptionWrapper select").prop("required", true);
				mainFields.find(".subscriptionWrapper input:not(.fieldsToCheck):not([type='checkbox'])").prop("required", true);
			} else if(value == ""){
				mainFields.find(".fieldWrapper").hide();
			} else {
				mainFields.find(".subscriptionWrapper").hide();
				mainFields.find(".subscriptionWrapper select").prop("required", false);
				mainFields.find(".subscriptionWrapper input:not(.fieldsToCheck):not([type='checkbox'])").prop("required", false);

				mainFields.find(".groupConnectionWrapper").hide();
				mainFields.find(".groupConnectionWrapper select").prop("required", false);

				mainFields.find(".prospectTypesSelect").hide();
				mainFields.find(".employeeIdSelect").hide();
				mainFields.find(".prospectValue").hide();
				mainFields.find(".prospectTypesSelect .prospectType").prop("required", false);
				mainFields.find(".employeeIdSelect .employeeId").prop("required", false);
				mainFields.find(".insertWrapper").show();
				mainFields.find(".notCpImportWrapper").show();
			}

			$(".compareSubunitId").hide();
			if(value == 4 || value == 8 || value == 2){
				if(value == 8){
					$(".subscriptionUpdateOnlyFields").show();
				} else {
					$(".subscriptionUpdateOnlyFields").hide();
				}
				$(".subscriptionOnlyFields").show();
				if(value == 2){
					$(".subscriptionOnlyFields").hide();
					$(".subscriptionUpdateOnlyFields").hide();
					$(".compareByDifferentFields").show();
				}
				$(".compareOrganizationAndName").hide();
				$(".compareCustomerNumber").show();
			} else if(value == 10) {
				$(".compareCustomerNumber").hide();
				$(".compareSubunitId").show();
			} else if(value == 3 || value == 6) {
				$(".subscriptionOnlyFields").hide();
				$(".compareOrganizationAndName").hide();
				$(".compareByDifferentFields").show();
			} else {
				$(".compareByDifferentFields").hide();
				$(".subscriptionOnlyFields").hide();
				$(".compareOrganizationAndName").hide();
				$(".compareCustomerNumber").show();
			}
			if(value == "") {
				$(".checkDuplicatesWrapper").hide();
			} else {
				if(value == 0){
					$(".checkDuplicatesWrapper").show();
				} else {
					$(".checkDuplicatesWrapper").hide();
				}
			}
			if(value == 3 || value == 6){
				$(".importContactPersonCompare").show();
			} else {
				$(".importContactPersonCompare").hide();
			}
			$(".personGroupCompareBy").change();
			$(window).resize();
		}).change();
		$(".customerCompareBy").change(function(){
			if($(this).val() == 0){
				$(".compareOrganizationAndName").hide();
				$(".compareCustomerNumber").show();
				$(".ownercompanySelectWrapper").show();
			} else {
				$(".compareOrganizationAndName").show();
				$(".compareCustomerNumber").hide();
				$(".ownercompanySelectWrapper").hide();
				if($(this).val() == 2){
					$(".comparingPreviousCustomerId").show();
				} else {
					$(".comparingPreviousCustomerId").hide();
				}
			}
		}).change()
		$(".showElementsInPopup").off("click").on("click", function(){
			var element = $(this).next(".popupElement").html();
			$('#popupeditboxcontent2').html('');
			$('#popupeditboxcontent2').html(element);
			out_popup = $('#popupeditbox2').bPopup(out_popup_options);
			$("#popupeditbox2:not(.opened)").remove();
		})
		$(".personGroupCompareBy").change(function(){
			var value = $(this).val();
			if($(".createProspectSelect").val() == 5){
				if(value == 0){
					$(".ownercompanySelectWrapper").show();
					$(".compareCustomerNumber").show();
					$("#csv_groupconnection_fullname_for_comparing").show();
					$("#csv_groupconnection_previous_sys_id").hide();
				} else if(value == 1){
					$(".ownercompanySelectWrapper").hide();
					$(".compareCustomerNumber").hide();
					$("#csv_groupconnection_fullname_for_comparing").hide();
					$("#csv_groupconnection_previous_sys_id").show();

				}
			}
		}).change();
		$("#checkForDuplicates").off("click").on("click", function(){
			if($(this).is(":checked")){
				$(".applyPreviousSysIdWrapper").show();
			} else {
				$(".applyPreviousSysIdWrapper").hide();
			}
		})
	});
	</script>
	<?php
	ob_start();
	?>
		<div class="popupform" id="importForm2">
			<form class="output-form"  method="post" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=import_company";?>" accept-charset="UTF-8">
            <input type="hidden" name="fwajax" value="1">
        	<input type="hidden" name="fw_nocss" value="1">
        	<input type="hidden" name="output_form_submit" class="output_form_submit" value="1">
            <p align="right">
				<?php echo $formText_Import_output;?>:
				<select name="createProspect" class="createProspectSelect" autocomplete="off" required>
					<option value=""><?php echo $formText_Choose_output;?></option>
					<option value="0" <?php if(isset($_POST['createProspect']) && $_POST['createProspect'] == 0) echo 'selected';?>><?php echo $formText_ImportOnlyCompanies_output?></option>
					<option value="1" <?php if(isset($_POST['createProspect']) && $_POST['createProspect'] == 1) echo 'selected';?>><?php echo $formText_ImportProspects_output?></option>
					<option value="2" <?php if(isset($_POST['createProspect']) && $_POST['createProspect'] == 2) echo 'selected';?>><?php echo $formText_UpdatingImport_output?></option>
					<option value="3" <?php if(isset($_POST['createProspect']) && $_POST['createProspect'] == 3) echo 'selected';?>><?php echo $formText_ImportContactPersons_output?></option>
					<option value="4" <?php if(isset($_POST['createProspect']) && $_POST['createProspect'] == 4) echo 'selected';?>><?php echo $formText_ImportSubscriptions_output?></option>
					<option value="8" <?php if(isset($_POST['createProspect']) && $_POST['createProspect'] == 8) echo 'selected';?>><?php echo $formText_UpdateSubscriptions_output?></option>
					<option value="5" <?php if(isset($_POST['createProspect']) && $_POST['createProspect'] == 5) echo 'selected';?>><?php echo $formText_ImportPersonGroupConnection_output?></option>
					<option value="6" <?php if(isset($_POST['createProspect']) && $_POST['createProspect'] == 6) echo 'selected';?>><?php echo $formText_ImportSelfdefined_output?></option>
					<option value="7" <?php if(isset($_POST['createProspect']) && $_POST['createProspect'] == 7) echo 'selected';?>><?php echo $formText_ImportToNewsletterList_output?></option>
					<option value="9" <?php if(isset($_POST['createProspect']) && $_POST['createProspect'] == 9) echo 'selected';?>><?php echo $formText_ImportCustomerSubunit_output?></option>
					<option value="10" <?php if(isset($_POST['createProspect']) && $_POST['createProspect'] == 10) echo 'selected';?>><?php echo $formText_ImportSubscriptionToSubunit_output?></option>

				</select>
			</p>
			<?php
			if($_POST['output_form_submit'] == 1){
				if($_POST['approved']){
					echo $successfullyUpdatedCount. " ".$formText_EntriesSuccessfullyUpdated_output;
				} else {
					?>
					<div id="popup-validate-message"></div>
		        	<input type="hidden" name="approved" class="approved" value="1">

					<div style="max-height: 600px; overflow: auto;">
						<?php
						if($_POST['createProspect'] == 8 || $_POST['createProspect'] == 2 || $_POST['createProspect'] == 9 || $_POST['createProspect'] == 10){
							if($_POST['compareBy'] == 0){
								?>
								<b><?php echo $formText_MatchesFound_output." ".count($matches_found);?></b><br/>
								<?php if($empty_customernumber > 0){ ?>
									<b><?php echo $formText_EntriesWithEmptyCustomerNumber_output." ".$empty_customernumber;?></b><br/>
								<?php } ?>
								<b><?php echo $formText_NoMatchesFound_output." ".count($nomatches_found);?></b><br/>
								<?php echo implode("<br/> ",$nomatches_found);?><br/>
								<?php
							} else if($_POST['compareBy'] == 1){
								?>
								<b class="showElementsInPopup"><?php echo $formText_MatchesFoundByOrganizationNumberAndCustomerName_output." ".count($matches_found['both']);?></b>
								<div class="popupElement">
									<b><?php echo $formText_MatchesFoundByOrganizationNumberAndCustomerName_output?></b><br/><br/>
									<?php foreach($matches_found['both'] as $customer) { ?>
										<div class=""><?php echo $customer['name']." ".$customer['middlename']." ".$customer['lastname'];?></div>
									<?php } ?>
								</div><br/>
								<b class="showElementsInPopup"><?php echo $formText_MatchesFoundByOrganizationNumberOnly_output." ".count($matches_found['organization_only']);?></b>
								<div class="popupElement">
									<b><?php echo $formText_MatchesFoundByOrganizationNumberOnly_output?></b><br/><br/>
									<table class="table">
										<tr>
											<th><?php echo $formText_OrganizationNumber_output?></th>
											<th><?php echo $formText_CustomerName_output?></th>
											<th><?php echo $formText_SearchCustomerName_output?></th>
										</tr>
										<?php foreach($matches_found['organization_only'] as $customer) { ?>
											<tr>
												<td><?php echo $customer['publicRegisterId']?></td>
												<td><?php echo $customer['name']." ".$customer['middlename']." ".$customer['lastname']?></td>
												<td><?php echo $customer['search_name']?></td>
											</tr>
										<?php } ?>
									</table>
								</div><br/>
								<b class="showElementsInPopup"><?php echo $formText_MatchesFoundByCustomerNameOnly_output." ".count($matches_found['customer_only']);?></b>
								<div class="popupElement">
									<b><?php echo $formText_MatchesFoundByCustomerNameOnly_output?></b><br/><br/>
									<table class="table">
										<tr>
											<th><?php echo $formText_InDatabase_output?></th>
											<th><?php echo $formText_InFile_output?></th>
										</tr>
										<?php foreach($matches_found['customer_only'] as $customer) { ?>
											<tr>
												<td>
													<?php echo $customer['publicRegisterId']?>&nbsp;</br>
													<?php echo $customer['name']." ".$customer['middlename']." ".$customer['lastname']?>
												</td>
												<td><?php echo $customer['search_org']?>&nbsp;</br><?php echo $customer['search_name']?></td>
											</tr>
										<?php } ?>
									</table>
								</div><br/>
								<b class="showElementsInPopup"><?php echo $formText_NoMatchesFound_output." ".count($nomatches_found);?></b>
								<div class="popupElement">
									<b><?php echo $formText_NoMatchesFound_output?></b><br/><br/>
									<?php foreach($nomatches_found as $customer) { ?>
										<div class=""><?php echo $customer[0]." ".$customer[1];?></div>
									<?php } ?>
								</div><br/>
								<?php
							}
							?>
							<?php if(count($other_matches_found) > 0) { ?>
								<b><?php echo $formText_OtherCustomersMatchesFound_output." ".count($other_matches_found);?></b><br/>
							<?php } ?>
							<?php if(count($other_nomatches_found) > 0) { ?>
								<b><?php echo $formText_OtherCustomersNoMatchesFound_output." ".count($other_nomatches_found);?></b><br/>
								<?php echo implode("<br/> ",$other_nomatches_found);?><br/>
								<?php
							}
							if(count($subscription_not_matches_found) > 0) { ?>
								<b><?php echo $formText_SubsriptionsNoMatchesFound_output." ".count($subscription_not_matches_found);?></b><br/>
								<?php echo implode("<br/> ",$subscription_not_matches_found);?><br/>
							<?php }
							if(count($subscription_matches_found) > 0) { ?>
								<b><?php echo $formText_SubsriptionsMatchesFound_output." ".count($subscription_matches_found);?></b><br/>
							<?php }

						}
						if($_POST['createProspect'] == 5) {
							?>
							<b><?php echo $formText_MatchesFound_output." ".count($matches_found);?></b><br/>
							<b><?php echo $formText_NoMatchesFound_output." ".count($nomatches_found);?></b> <?php echo implode(", ",$nomatches_found);?><br/>
							<?php
						}
						if($_POST['createProspect'] == 7) {
							?>
							<b><?php echo $formText_FullMatchesFound_output." ".count($newsletter_full_found);?></b><br/>
							<table>
								<tr><td><?php echo $formText_Email_output;?></td><td><?php echo $formText_Name_output;?></td><td><?php echo $formText_Customer_output;?></td></tr>
								<?php
								foreach($newsletter_full_found as $newsletter_email_single){
									?>
									<tr>
										<td>
											<?php echo $newsletter_email_single[0];?>
										</td>
										<td>
											<?php echo $newsletter_email_single[1];?>
										</td>
										<td>
											<?php echo $newsletter_email_single[2];?>
										</td>
									</tr>
									<?php
								}
								?>
							</table>
							<b><?php echo $formText_MatchesOnEmail_output." ".count($newsletter_email_found);?></b><br/>
							<table>
								<tr><td><?php echo $formText_Email_output;?></td><td><?php echo $formText_Name_output;?></td><td><?php echo $formText_Customer_output;?></td></tr>
								<?php
								foreach($newsletter_email_found as $newsletter_email_single){
									?>
									<tr>
										<td>
											<?php echo $newsletter_email_single[0];?>
										</td>
										<td>
											<?php echo $newsletter_email_single[1];?>
										</td>
										<td>
											<?php echo $newsletter_email_single[2];?>
										</td>
									</tr>
									<?php
								}
								?>
							</table>
							<b><?php echo $formText_MatchesOnName_output." ".count($newsletter_partial_found);?></b><br/>
							<table>
								<tr><td><?php echo $formText_Email_output;?></td><td><?php echo $formText_Name_output;?></td><td><?php echo $formText_Customer_output;?></td><td><?php echo $formText_Action_output;?></td></tr>
								<?php
								foreach($newsletter_partial_found as $newsletter_email_single){
									?>
									<tr>
										<td>
											<?php echo $newsletter_email_single[0];?>
										</td>
										<td>
											<?php echo $newsletter_email_single[1];?>
										</td>
										<td>
											<?php echo $newsletter_email_single[2];?>
										</td>
										<td>
											<select name="newsletter_choice[<?php echo $newsletter_email_single[3]?>]">
												<option value="0"><?php echo $formText_CopyEmailToContactPerson_output;?></option>
												<option value="1"><?php echo $formText_DoNotCopyAndStoreInSubscriberList_output;?></option>
											</select>
										</td>
									</tr>
									<?php
								}
								?>
							</table>

							<b><?php echo $formText_NoMatch_output." ".count($newsletter_not_found);?></b><br/>
							<table>
								<tr><td><?php echo $formText_Email_output;?></td><td><?php echo $formText_Name_output;?></td><td><?php echo $formText_Customer_output;?></td></tr>
								<?php
								foreach($newsletter_not_found as $newsletter_email_single){
									?>
									<tr>
										<td>
											<?php echo $newsletter_email_single[0];?>
										</td>
										<td>
											<?php echo $newsletter_email_single[1];?>
										</td>
										<td>
											<?php echo $newsletter_email_single[2];?>
										</td>
									</tr>
									<?php
								}
								?>
							</table>
						<?php } ?>
						<?php
						if($_POST['createProspect'] == 0 && $_POST['checkForDuplicates']) {
							?>
							<b class="showElementsInPopup"><?php echo $formText_MatchesWithSameCustomerNr_output." ".count($matches_found['both']);?></b> - <?php echo $formText_WillNotBeImported_output;?>
							<div class="popupElement">
								<b><?php echo $formText_MatchesWithSameCustomerNr_output?></b><br/><br/>
								<?php foreach($matches_found['both'] as $customer) { ?>
									<div class=""><?php echo $customer['name']." ".$customer['middlename']." ".$customer['lastname'];?></div>
								<?php } ?>
							</div><br/>
							<b class="showElementsInPopup"><?php echo $formText_MatchesButNotExistingSameCustomerNr_output." ".count($matches_found['only_customer']);?></b> - <?php echo $formText_WillBeUpdatedWithExtraCustomerNrRecord_output;?>
							<div class="popupElement">
								<b><?php echo $formText_MatchesButNotExistingSameCustomerNr_output?></b><br/><br/>
								<?php foreach($matches_found['only_customer'] as $customer) { ?>
									<div class=""><?php echo $customer['name']." ".$customer['middlename']." ".$customer['lastname'];?></div>
								<?php } ?>
							</div><br/>
							<b class="showElementsInPopup"><?php echo $formText_NoMatchesFound_output." ".count($nomatches_found);?></b> - <?php echo $formText_WillBeImported_output;?>
							<div class="popupElement">
								<b><?php echo $formText_NoMatchesFound_output?></b><br/><br/>
								<?php foreach($nomatches_found as $customer) { ?>
									<div class=""><?php echo $customer[0]." ".$customer[1];?></div>
								<?php } ?>
							</div><br/>
							<input type="checkbox" autocomplete="off" name="marked_for_manual_check" id="marked_for_manual_check"/><label for="marked_for_manual_check"><?php echo $formText_MarkForManualCheck_output;?></label>
							<?php
						}
						if($_POST['createProspect'] == 3){
							?>
							<b class="showElementsInPopup"><?php echo $formText_ContactPersonsMatchedToCustomer_output." ".count($matches_found);?></b>
							<div class="popupElement">
								<b><?php echo $formText_ContactPersonsMatchedToCustomer_output?></b><br/><br/>
								<?php foreach($matches_found as $contactpersonname) { ?>
									<div class=""><?php echo $contactpersonname;?></div>
								<?php } ?>
							</div><br/>
							<b class="showElementsInPopup"><?php echo $formText_ContactPersonsWithoutMatch_output." ".count($nomatches_found);?></b>
							<div class="popupElement">
								<b><?php echo $formText_ContactPersonsWithoutMatch_output?></b><br/><br/>
								<?php foreach($nomatches_found as $contactpersonname) { ?>
									<div class=""><?php echo $contactpersonname;?></div>
								<?php } ?>
							</div><br/>
							<?php
						}
						if($_POST['createProspect'] == 4){
							?>
							<b class="showElementsInPopup"><?php echo $formText_SubscriptionsMatchedToCustomer_output." ".count($matches_found);?></b>
							<div class="popupElement">
								<b><?php echo $formText_SubscriptionsMatchedToCustomer_output?></b><br/><br/>
								<?php foreach($matches_found as $contactpersonname) { ?>
									<div class=""><?php echo $contactpersonname;?></div>
								<?php } ?>
							</div><br/>
							<b class="showElementsInPopup"><?php echo $formText_SubscriptionsWithoutMatch_output." ".count($nomatches_found);?></b>
							<div class="popupElement">
								<b><?php echo $formText_SubscriptionsWithoutMatch_output?></b><br/><br/>
								<?php foreach($nomatches_found as $contactpersonname) { ?>
									<div class=""><?php echo $contactpersonname;?></div>
								<?php } ?>
							</div><br/>
							<?php
						}
						?>
					</div>
					<br/><br/><br/>
					<input type="button" name="" id="cancelImportButton2" value="<?php echo $formText_Cancel_output;?>">
					<?php
					if($_POST['createProspect'] == 3) {						?>

						<input type="submit" name="approvedChoice" id="importButton3" value="<?php echo $formText_ImportOnlyMatchedContactpersons_output ;?>">

						<input type="submit" name="approvedChoiceAll" id="importButton4" value="<?php echo $formText_ImportAllIncludingNotMatchedContactpersons_output ;?>">
						<?php

					} else if($_POST['createProspect'] == 4) {?>

						<input type="submit" name="approvedChoice" id="importButton3" value="<?php echo $formText_ImportOnlyMatchedSubscriptions_output ;?>">

						<input type="submit" name="approvedChoiceAll" id="importButton4" value="<?php echo $formText_ImportAllIncludingNotMatchedSubscriptions_output ;?>">
						<?php

					} else {
						?>
						<input type="submit" name="" id="importButton2" value="<?php echo $formText_Approve_output ;?>">
						<?php
					}
				}
			}
			?>
			<div style="<?php if($_POST['output_form_submit'] == 1){ echo 'display: none;'; } ?>">
				<p align="center">
					<b><?php echo $formText_PleaseSelectCsvFileForImport_output;?>:</b> <input type="file" value="" name="filenameImport" id="filenameImport2">
				</p>

				<p align="center">
					<b><?php echo $formText_PleaseSelectCsvFileSeperatorValue_output;?>:</b>
					<select name="separator" id="separator" autocomplete="off">
						<option value="," <?php if($_POST['separator'] == ",") echo 'selected';?>>, (commas)</option>
						<option value=";" <?php if($_POST['separator'] == ";") echo 'selected';?>>; (semi-colons)</option>
						<option value=":" <?php if($_POST['separator'] == ":") echo 'selected';?>>: (colons)</option>
						<option value="|" <?php if($_POST['separator'] == "|") echo 'selected';?>>| (pipes)</option>
						<option value="t" <?php if($_POST['separator'] == "t") echo 'selected';?>>&nbsp;&nbsp;(tab)</option>
					</select>
					<br/>
					<br/>
					<?php echo $formText_FirstLineOfFileWillNotBeImported_output;?>
				</p>
				<p align="center">
					<input type="hidden" name="csvFields" value="<?php echo htmlspecialchars($_POST['csvFields'])?>">
				</p>

				<div class="half1">
					<div id="dbFields2" class="droppable bank">
					</div>
				</div>


				<div class="half2" id="csvimportfields2">
					<div id="popup-validate-message"></div>
					<?php

					$s_sql = "SELECT * FROM contactperson WHERE content_status < 2 AND type = ? ORDER BY sortnr";
					$o_query = $o_main->db->query($s_sql, array($people_contactperson_type));
					$employees = $o_query ? $o_query->result_array() : array();

					$s_sql = "SELECT * FROM prospecttype WHERE content_status < 2 ORDER BY sortnr";
					$o_query = $o_main->db->query($s_sql);
					$prospecttypes = $o_query ? $o_query->result_array() : array();


					?>

					<input type="hidden" name="developeraccess" value="<?php echo $variables->developeraccess;?>" autocomplete="off">
					<div class="prospectTypesSelect fieldWrapper">
						<?php echo $formText_ProspectType_output;?>:
						<select name="prospectType" class="prospectType" autocomplete="off">
							<option value=""><?php echo $formText_Choose_output;?></option>
							<?php
							foreach($prospecttypes as $prospecttype) {
								?>
								<option value="<?php echo $prospecttype['id']?>" <?php if($_POST['prospectType'] == $prospecttype['id']) echo 'selected';?>><?php echo $prospecttype['name'];?></option>
								<?php
							}
							?>
						</select>
					</div>
					<div class="employeeIdSelect fieldWrapper" >
						<?php echo $formText_Employee_output;?>:
						<select name="employeeId" class="employeeId" autocomplete="off">
							<option value=""><?php echo $formText_Choose_output;?></option>
							<?php
							foreach($employees as $employee) {
								?>
								<option value="<?php echo $employee['id']?>" <?php if($_POST['employeeId'] == $employee['id']) echo 'selected';?>><?php echo $employee['name']." ".$employee['middlename']." ".$employee['lastname'];?></option>
								<?php
							}
							?>
						</select>
					</div>
					<div class="prospectValue fieldWrapper">
						<?php
						$field = "prospect_value";
						?>
						<div id="csv_<?=$field?>" class="droppable prospect_value home" data-scope="<?=$field?>">
							<?=$formText_ProspectValue_output;?> <sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
							<div class="label">
								<input type="text" name="<?=$field?>" id="label_<?=$field?>" value="<?php echo $_POST[$field];?>" placeholder="<?=$field?>" autocomplete="off">
								<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
							</div>
						</div>
						<?php
						$field = "prospect_info";
						?>
						<div id="csv_<?=$field?>" class="droppable prospect_value home" data-scope="<?=$field?>">
							<?=$formText_ProspectInfo_output;?> <sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
							<div class="label">
								<input type="text" name="<?=$field?>" id="label_<?=$field?>" value="<?php echo $_POST[$field];?>" placeholder="<?=$field?>" autocomplete="off">
								<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
							</div>
						</div>
					</div>
					<?php /*?>
					<div class="insertWrapper fieldWrapper">
						<label><?php echo $formText_NotAllowDuplicatePublicRegisterId_output;?></label> <input type="checkbox" name="notAllowDuplicatePublicRegisterId" id="notAllowDuplicatePublicRegisterId"/>
					</div>*/?>
					<div class="insertWrapper checkDuplicatesWrapper fieldWrapper">
						<label><?php echo $formText_CheckIfExistsInExistingCustomers_output;?></label> <input <?php if(isset($_POST['checkForDuplicates'])){if($_POST['checkForDuplicates']) echo 'checked'; }  ?>  type="checkbox" name="checkForDuplicates" id="checkForDuplicates"/>
						<div class="applyPreviousSysIdWrapper" style="display: none;">
							<label><?php echo $formText_ApplyPreviousSysIdWhenMatched_output;?></label> <input <?php if(isset($_POST['applyPreviousSysId'])){if($_POST['applyPreviousSysId']) echo 'checked'; }  ?>  type="checkbox" name="applyPreviousSysId" id="applyPreviousSysId"/>
						</div>
					</div>

					<br/>

					<div class="fieldWrapper ownercompanyWrapper">
						<div class="groupConnectionWrapper ">
							<label><?php echo $formText_CompareBy_output;?></label>
							<select name="personGroupCompareBy" class="personGroupCompareBy" autocomplete="off">
								<option value="0"><?php echo $formText_CustomerNumberAndFullname_Output;?></option>
								<option value="1" <?php if($_POST['personGroupCompareBy'] == 1) echo 'selected';?>><?php echo $formText_PreviousPersonSysId_Output;?></option>
							</select>
						</div>
						<div class="subscriptionOnlyFields compareByDifferentFields">
							<label><?php echo $formText_CompareBy_output;?></label>
							<select name="compareBy" class="customerCompareBy" autocomplete="off">
								<option value="0"><?php echo $formText_CustomerNumber_Output;?></option>
								<option value="1" <?php if($_POST['compareBy'] == 1) echo 'selected';?>><?php echo $formText_OrganizationNumberAndCustomerName_Output;?></option>
								<option class="importContactPersonCompare" style="display: none;" value="2" <?php if($_POST['compareBy'] == 2) echo 'selected';?>><?php echo $formText_PreviousCustomerIdOrOrganizationNumberAndCustomerName_output;?></option>
							</select>
							<div class="compareOrganizationAndName">
								<?php
								$field = "customerName";
								?>
								<div id="csv_<?=$field?>" class="droppable customerName home" data-scope="<?=$field?>">
									<?=$formText_customerName_output;?> <sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
									<div class="label">
										<input type="text" name="<?=$field?>" id="label_<?=$field?>" value="<?php echo $_POST[$field];?>" placeholder="<?=$field?>" autocomplete="off">
										<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
									</div>
								</div>
								<?php
								$field = "customerOrgNr";
								?>
								<div id="csv_<?=$field?>" class="droppable customerOrgNr home" data-scope="<?=$field?>">
									<?=$formText_CustomerOrgNr_output;?> <sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
									<div class="label">
										<input type="text" name="<?=$field?>" id="label_<?=$field?>" value="<?php echo $_POST[$field];?>" placeholder="<?=$field?>" autocomplete="off">
										<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
									</div>
								</div>
								<?php
								$field = "comparing_previous_customer_id";
								?>
								<div id="csv_<?=$field?>" class="droppable comparingPreviousCustomerId home" data-scope="<?=$field?>">
									<?=$formText_PreviousCustomerId_output;?> <sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
									<div class="label">
										<input type="text" name="<?=$field?>" id="label_<?=$field?>" value="<?php echo $_POST[$field];?>" placeholder="<?=$field?>" autocomplete="off">
										<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
									</div>
								</div>
							</div>
						</div>

							<div class="ownercompanySelectWrapper">
								<label><?php echo $formText_OwnerCompany_output;?></label>
								<?php
								$ownercompanies = array();
								$s_sql = "SELECT * FROM ownercompany";
								$o_query = $o_main->db->query($s_sql);
								if($o_query && $o_query->num_rows()>0) {
									$ownercompanies = $o_query->result_array();
								}
								$default_own = $ownercompanies[0];
								?>
								<select name="ownercompany" class="ownercompanySelect" required autocomplete="off">
									<option value=""><?php echo $formText_SelectOwnerCompany_Output;?></option>
									<?php foreach($ownercompanies as $ownercompany) { ?>
										<option value="<?php echo $ownercompany['id'];?>" <?php if(count($ownercompanies) == 1 && $default_own['id'] == $ownercompany['id']) { echo 'selected';}?> <?php if(isset($_POST['ownercompany']) && $_POST['ownercompany'] == $ownercompany['id']) echo 'selected'; ?>><?php echo $ownercompany['name'];?></option>
									<?php } ?>
								</select>
							</div>
						<div class="compareSubunitId" style="display: none;">
							<?php
							$field = "subunit_compare_id";
							?>
							<div id="csv_<?=$field?>" class="droppable subunit_compare_id home" data-scope="<?=$field?>">
								<?=$formText_SubunitId_output;?> <sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
								<div class="label">
									<input type="text" name="<?=$field?>" id="label_<?=$field?>" value="<?php echo $_POST[$field];?>" placeholder="<?=$field?>" autocomplete="off">
									<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
								</div>
							</div>
						</div>
						<div class="compareCustomerNumber">
							<?php
							$field = "customerNumber";
							?>
							<div id="csv_<?=$field?>" class="droppable customerNumber home" data-scope="<?=$field?>">
								<?=$formText_CustomerNumber_output;?> <sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
								<div class="label">
									<input type="text" name="<?=$field?>" id="label_<?=$field?>" value="<?php echo $_POST[$field];?>" placeholder="<?=$field?>" autocomplete="off">
									<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
								</div>
							</div>
						</div>
						<div class="subscriptionUpdateOnlyFields">
							<label><?php echo $formText_CompareBy_output;?></label>
							<select name="subscriptionTypeCompareBy" class="subscriptionTypeCompareBy" autocomplete="off">
								<option value="0"><?php echo $formText_SubscriptionType_Output;?></option>
								<?php
			                    $s_sql = "SELECT * FROM subscriptiontype WHERE content_status < 2 ORDER BY name";
			                    $o_query = $o_main->db->query($s_sql);
			                    $subscriptionTypes = ($o_query ? $o_query->result_array():array());

								foreach($subscriptionTypes as $subscriptionType) {
									?>
									<option value="<?php echo $subscriptionType['id']?>" <?php if($_POST['subscriptionTypeCompareBy'] == $subscriptionType['id']) echo 'selected';?>><?php echo $subscriptionType['name'];?></option>
									<?php
								}
								?>
							</select>
						</div>
					</div>

					<br/>
					<div class="boxes">

					<div class="notCpImportWrapper fieldWrapper">
						<?php
						// $defaultFields = array('id','moduleID','createdBy','created','updatedBy','updated','origId','sortnr','seotitle','seodescription','seourl', 'content_status',
						// 'companyType', 'notOverwriteByImport', 'consideredIrrelevant', 'creditApproved', 'creditLimit', 'textVisibleInMyProfie',
						// 'numberOfUnits', 'associationId', 'housingcooperativeType', 'getynet_customer_id', 'create_filearchive_folder', 'articlePriceMatrixId', 'articleDiscountMatrixId',
						// 'user_registration', 'user_registration_link', 'user_registration_token', 'user_registration_domain', 'ownerFromDate', 'industries','financialYear', 'revenue',
						// 'municipalityName', 'publicRegisterContactperson', 'publicRegisterContactpFunction', 'revenueManuallyAdded', 'revenueManuallyAddedYear', 'numberOfEmplyees', 'comments',
						// 'textVisibleInMyProfile', 'industryCode', 'industryText', 'customerType', 'iaStreet1', 'iaStreet2', 'iaPostalNumber', 'iaCity', 'iaCountry', 'useOwnInvoiceAdress',
						// 'mobile', 'fax', 'middlename', 'lastname', 'personnumber', 'birthdate'
						// );
						$defaultFields = array('publicRegisterId', "name", "paStreet", "paPostalNumber", "paCity", "paCountry", "vaStreet", "vaPostalNumber", "vaCity", "vaCountry",
						"phone", "mobile", "email", "homepage", "invoiceBy", "invoiceEmail", "vaStreet2", "paStreet2", "credittimeDays", "overrideAdminFeeDefault", "extra1", "extra2", "extra3", "extra4", "previous_sys_id");
						foreach($dbfields as $field) {
								if(in_array($field, $defaultFields) ) {
						?>
							<div id="csv_<?=$field?>" class="droppable home" data-scope="<?=$field?>">
								<?=$field?> <sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
								<div class="label">
									<input type="text" class="fieldsToCheck" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="<?php echo $_POST['customlabel'][$field];?>" placeholder="<?=$field?>" autocomplete="off">
									<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
								</div>
							</div>
						<?php }
						}
						?>
					</div>
					<div class="notCpImportWrapper selfdefinedWrapper fieldWrapper insertWrapper">
						<div class="contactPersonTitle"><?php echo $formText_SelfDefinedFields_Output;?></div>
						<?php
						$selfdefinedFields = array();
						$o_query = $o_main->db->query("SELECT * FROM customer_selfdefined_fields ORDER BY name");
						if($o_query && $o_query->num_rows()>0)
						foreach($o_query->result_array() as $v_row)
						{
							array_push($selfdefinedFields, $v_row);
						}
						foreach($selfdefinedFields as $selfdefinedField)
						{
							$field = "selfdefined_".$selfdefinedField['id'];
							?>
							<div id="csv_<?=$field?>" class="droppable selfdefined home" data-scope="<?=$field?>">
								<?=$selfdefinedField['name']?> <sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
								<div class="label">
									<input type="text" class="fieldsToCheck" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="<?php echo $_POST['customlabel'][$field];?>" placeholder="<?=$field?>" autocomplete="off">
									<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
								</div>
							</div>
							<?php
						}
						?>
					</div>
					<?php
					?>
					<div class="cpWrapper fieldWrapper insertWrapper">
						<div class="contactPersonTitle"><?php echo $formText_ContactPerson_output;?></div>
						<?php
						$defaultFields2 = array('name', 'middlename', 'lastname', 'mobile', 'email',  'title', 'fullname_for_import_comparing', 'birthdate', 'previous_sys_id', 'previous_customer_id', 'previous_customer_name');
						foreach($dbfields2 as $field) {
							if(in_array($field, $defaultFields2) ) {
								$field = "contactperson_".$field;
						?>
							<div id="csv_<?=$field?>" class="droppable home" data-scope="<?=$field?>">
								<?php
								switch($field) {
									case "contactperson_name":
										echo $formText_FirstName_output;
									break;
									case "contactperson_middlename":
										echo $formText_MiddleName_output;
									break;
									case "contactperson_lastname":
										echo $formText_Lastname_output;
									break;
									case "contactperson_email":
										echo $formText_Email_output;
									break;
									case "contactperson_mobile":
										echo $formText_Mobile_output;
									break;
									case "contactperson_title":
										echo $formText_Title_output;
									break;
									case "contactperson_fullname_for_import_comparing":
										echo $formText_FullNameForImportComparing_output;
									break;
									case "contactperson_birthdate":
										echo $formText_BirthDate_output;
									break;
									case "contactperson_previous_sys_id":
										echo $formText_PreviousSysId_output;
									break;
									case "contactperson_previous_customer_id":
										echo $formText_PreviousCustomerId_output;
									break;
									case "contactperson_previous_customer_name":
										echo $formText_PreviousCustomerName_output;
									break;
									default:
										echo $field;
									break;
								}
								?>
								<sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
								<div class="label">
									<input type="text" class="fieldsToCheck" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="<?php echo $_POST['customlabel'][$field];?>" placeholder="<?=$field?>" autocomplete="off">
									<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
								</div>
							</div>
						<?php }
						}
						?>
					</div>
					<div class="subscriptionWrapper fieldWrapper" style="display: none;">
						<?php
						$field = "subscription_start_date";
						?>
						<div id="csv_<?=$field?>" class="droppable home" data-scope="<?=$field?>">
							<?php
							switch($field) {
								case "subscription_start_date":
									echo $formText_StartDate_output;
								break;
								default:
									echo $field;
								break;
							}
							?>
							<sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
							<div class="label">
								<input type="text" class="fieldsToCheck" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="<?php echo $_POST['customlabel'][$field];?>" placeholder="<?=$field?>" autocomplete="off">
								<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
							</div>
						</div>
						<?php
						$field = "subscription_nextrenewal_date";
						?>
						<div id="csv_<?=$field?>" class="droppable home" data-scope="<?=$field?>">
							<?php
							switch($field) {
								case "subscription_nextrenewal_date":
									echo $formText_NextRenewalDate_output;
								break;
								default:
									echo $field;
								break;
							}
							?>
							<sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
							<div class="label">
								<input type="text" class="fieldsToCheck" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="<?php echo $_POST['customlabel'][$field];?>" placeholder="<?=$field?>" autocomplete="off">
								<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
							</div>
						</div>
						<?php
						$field = "subscription_name";
						?>
						<div id="csv_<?=$field?>" class="droppable home" data-scope="<?=$field?>">
							<?php
							switch($field) {
								case "subscription_name":
									echo $formText_SubscriptionName_output;
								break;
								default:
									echo $field;
								break;
							}
							?>
							<sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
							<div class="label">
								<input type="text" class="fieldsToCheck" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="<?php echo $_POST['customlabel'][$field];?>" placeholder="<?=$field?>" autocomplete="off">
								<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
							</div>
						</div>
						<?php
						$field = "subscription_articlenumber1";
						?>
						<div id="csv_<?=$field?>" class="droppable home subscriptionWrapperInsertOnly" data-scope="<?=$field?>">
							<?php
							switch($field) {
								case "subscription_articlenumber1":
									echo $formText_SubscriptionLineArticleNumber1_output;
								break;
								default:
									echo $field;
								break;
							}
							?>
							<sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
							<div class="label">
								<input type="text" class="fieldsToCheck" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="<?php echo $_POST['customlabel'][$field];?>" placeholder="<?=$field?>" autocomplete="off">
								<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
							</div>
						</div>
						<?php
						$field = "subscription_articlenumber2";
						?>
						<div id="csv_<?=$field?>" class="droppable home subscriptionWrapperInsertOnly" data-scope="<?=$field?>">
							<?php
							switch($field) {
								case "subscription_articlenumber2":
									echo $formText_SubscriptionLineArticleNumber2_output;
								break;
								default:
									echo $field;
								break;
							}
							?>
							<sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
							<div class="label">
								<input type="text" class="fieldsToCheck" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="<?php echo $_POST['customlabel'][$field];?>" placeholder="<?=$field?>" autocomplete="off">
								<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
							</div>
						</div>
						<?php
						$field = "subscription_articlenumber3";
						?>
						<div id="csv_<?=$field?>" class="droppable home subscriptionWrapperInsertOnly" data-scope="<?=$field?>">
							<?php
							switch($field) {
								case "subscription_articlenumber3":
									echo $formText_SubscriptionLineArticleNumber3_output;
								break;
								default:
									echo $field;
								break;
							}
							?>
							<sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
							<div class="label">
								<input type="text" class="fieldsToCheck" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="<?php echo $_POST['customlabel'][$field];?>" placeholder="<?=$field?>" autocomplete="off">
								<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
							</div>
						</div>
						<?php
						$field = "subscription_subtype_id";
						?>
						<div id="csv_<?=$field?>" class="droppable home" data-scope="<?=$field?>">
							<?php
							switch($field) {
								case "subscription_subtype_id":
									echo $formText_SubTypeId_output;
								break;
								default:
									echo $field;
								break;
							}
							?>
							<sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
							<div class="label">
								<input type="text" class="fieldsToCheck" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="<?php echo $_POST['customlabel'][$field];?>" placeholder="<?=$field?>" autocomplete="off">
								<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
							</div>
						</div>
						<?php
						if($v_customer_accountconfig['activateSubscriptionInvoiceToOtherCustomer']) {
							$field = "subscription_invoice_to_other_customer_id";
							?>
							<div id="csv_<?=$field?>" class="droppable home" data-scope="<?=$field?>">
								<?php
								switch($field) {
									case "subscription_invoice_to_other_customer_id":
										echo $formText_InvoiceToOtherCustomer_output;
									break;
									default:
										echo $field;
									break;
								}
								?>
								<sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
								<div class="label">
									<input type="text" class="fieldsToCheck" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="<?php echo $_POST['customlabel'][$field];?>" placeholder="<?=$field?>" autocomplete="off">
									<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
								</div>
							</div>
						<?php } ?>

						<?php
						$field = "subscription_reference";
						?>
						<div id="csv_<?=$field?>" class="droppable home" data-scope="<?=$field?>">
							<?php
							switch($field) {
								case "subscription_reference":
									echo $formText_SubscriptionReference_output;
								break;
								default:
									echo $field;
								break;
							}
							?>
							<sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
							<div class="label">
								<input type="text" class="fieldsToCheck" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="<?php echo $_POST['customlabel'][$field];?>" placeholder="<?=$field?>" autocomplete="off">
								<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
							</div>
						</div>
						<?php
						$field = "subscription_previous_customer_id";
						?>
						<div id="csv_<?=$field?>" class="droppable home" data-scope="<?=$field?>">
							<?php
							switch($field) {
								case "subscription_previous_customer_id":
									echo $formText_PreviousCustomerId_output;
								break;
								default:
									echo $field;
								break;
							}
							?>
							<sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
							<div class="label">
								<input type="text" class="fieldsToCheck" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="<?php echo $_POST['customlabel'][$field];?>" placeholder="<?=$field?>" autocomplete="off">
								<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
							</div>
						</div>
						<?php
						$field = "subscription_previous_customer_name";
						?>
						<div id="csv_<?=$field?>" class="droppable home" data-scope="<?=$field?>">
							<?php
							switch($field) {
								case "subscription_previous_customer_name":
									echo $formText_PreviousCustomerName_output;
								break;
								default:
									echo $field;
								break;
							}
							?>
							<sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
							<div class="label">
								<input type="text" class="fieldsToCheck" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="<?php echo $_POST['customlabel'][$field];?>" placeholder="<?=$field?>" autocomplete="off">
								<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
							</div>
						</div>
						<div style="margin-bottom: 5px;">
							<span class="labelSpan">
								<?php echo $formText_PeriodLength_output;?>:
							</span>
							<input type="text" name="subscription_period_length" value="<?php echo $_POST['subscription_period_length'];?>" class="subscription_period_length" autocomplete="off"/>
						</div>
						<div class="subscriptionWrapperInsertOnly" style="margin-bottom: 5px;">
							<span class="labelSpan">
								<?php echo $formText_SubscriptionType_output;?>:
							</span>
							<select name="subscription_type" class="subscription_type" autocomplete="off">
								<option value=""><?php echo $formText_Choose_output;?></option>
								<?php
			                    $s_sql = "SELECT * FROM subscriptiontype WHERE content_status < 2 ORDER BY name";
			                    $o_query = $o_main->db->query($s_sql);
			                    $subscriptionTypes = ($o_query ? $o_query->result_array():array());

								foreach($subscriptionTypes as $subscriptionType) {
									?>
									<option value="<?php echo $subscriptionType['id']?>" <?php if($_POST['subscription_type'] == $subscriptionType['id']) echo 'selected';?>><?php echo $subscriptionType['name'];?></option>
									<?php
								}
								?>
							</select>
						</div>
					</div>
					<div class="groupConnectionWrapper fieldWrapper" style="display: none;">
						<?php
						$field = "groupconnection_fullname_for_comparing";
						?>
						<div id="csv_<?=$field?>" class="droppable home" data-scope="<?=$field?>">
							<?php
							echo $formText_FullNameForImportComparing_output;
							?>
							<sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
							<div class="label">
								<input type="text" class="fieldsToCheck" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="<?php echo $_POST['customlabel'][$field];?>" placeholder="<?=$field?>" autocomplete="off">
								<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
							</div>
						</div>
						<?php
						$field = "groupconnection_previous_sys_id";
						?>
						<div id="csv_<?=$field?>" class="droppable home" data-scope="<?=$field?>">
							<?php
							echo $formText_PreviousPersonSysId_Output;
							?>
							<sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
							<div class="label">
								<input type="text" class="fieldsToCheck" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="<?php echo $_POST['customlabel'][$field];?>" placeholder="<?=$field?>" autocomplete="off">
								<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
							</div>
						</div>

						<div style="margin-bottom: 5px;">
							<span class="labelSpan">
								<?php echo $formText_Group_output;?>:
							</span>
							<select name="group_id" class="group_id" autocomplete="off">
								<option value=""><?php echo $formText_Choose_output;?></option>
								<?php
								$s_sql = "SELECT * FROM contactperson_group WHERE status = 1 ORDER BY name";
								$o_query = $o_main->db->query($s_sql);
								$groups = ($o_query ? $o_query->result_array():array());

								foreach($groups as $group) {
									?>
									<option value="<?php echo $group['id']?>" <?php if($_POST['group_id'] == $group['id']) echo 'selected';?>><?php echo $group['name'];?></option>
									<?php
								}
								?>
							</select>
						</div>
					</div>
					<div class="newsletterWrapper fieldWrapper" style="display: none;">
						<div style="margin-bottom: 5px;">
							<span class="labelSpan">
								<?php echo $formText_SubscriberList_output;?>:
							</span>
							<select name="newsletter_list" class="newsletter_list" autocomplete="off">
								<option value=""><?php echo $formText_Choose_output;?></option>
								<?php
								$s_sql = "SELECT * FROM email_subscriber_list WHERE content_status < 2 ORDER BY name";
								$o_query = $o_main->db->query($s_sql);
								$groups = ($o_query ? $o_query->result_array():array());

								foreach($groups as $group) {
									?>
									<option value="<?php echo $group['id']?>" <?php if($_POST['newsletter_list'] == $group['id']) echo 'selected';?>><?php echo $group['name'];?></option>
									<?php
								}
								?>
							</select>
						</div>
						<div style="margin-bottom: 5px;">
							<span class="labelSpan">
								<?php echo $formText_CheckIfMember_output;?>:
							</span>
							<input type="checkbox" name="newsletter_check_member" value="1" <?php if($_POST['newsletter_check_member']) echo 'checked';?> class="newsletter_check_member" autocomplete="off"/>
						</div>
						<?php
						$field = "newsletter_fullname";
						?>
						<div id="csv_<?=$field?>" class="droppable home" data-scope="<?=$field?>">
							<?php
							echo $formText_FullNameForImportComparing_output;
							?>
							<sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
							<div class="label">
								<input type="text" class="fieldsToCheck" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="<?php echo $_POST['customlabel'][$field];?>" placeholder="<?=$field?>" autocomplete="off">
								<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
							</div>
						</div>
						<?php
						$field = "newsletter_email";
						?>
						<div id="csv_<?=$field?>" class="droppable home" data-scope="<?=$field?>">
							<?php
							echo $formText_Email_output;
							?>
							<sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
							<div class="label">
								<input type="text" class="fieldsToCheck" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="<?php echo $_POST['customlabel'][$field];?>" placeholder="<?=$field?>" autocomplete="off">
								<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
							</div>
						</div>
					</div>
					<div class="subunitWrapper fieldWrapper" style="display: none;">
						<?php
						$field = "subunit_id";
						?>
						<div id="csv_<?=$field?>" class="droppable home" data-scope="<?=$field?>">
							<?php
							echo $formText_SubunitId_output;
							?>
							<sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
							<div class="label">
								<input type="text" class="fieldsToCheck" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="<?php echo $_POST['customlabel'][$field];?>" placeholder="<?=$field?>" autocomplete="off">
								<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
							</div>
						</div>
						<?php
						$field = "subunit_name";
						?>
						<div id="csv_<?=$field?>" class="droppable home" data-scope="<?=$field?>">
							<?php
							echo $formText_SubunitName_output;
							?>
							<sup><a class="label" href="#label_<?=$field?>" onclick="$($(this).attr('href')).parent().show(); return false;">label</a></sup>
							<div class="label">
								<input type="text" class="fieldsToCheck" name="customlabel[<?=$field?>]" id="label_<?=$field?>" value="<?php echo $_POST['customlabel'][$field];?>" placeholder="<?=$field?>" autocomplete="off">
								<a href="#label_<?=$field?>" onclick="$($(this).attr('href')).val('').parent().hide(); return false;">[x]</a>
							</div>
						</div>
					</div>
					<input type="submit" name="" id="importButton2" value="IMPORT" <?php if(!isset($_POST['output_form_submit'])) { ?>disabled="disabled"<?php } ?>>
					<input type="hidden" name="csv" value="<?php echo htmlspecialchars($_POST['csv']);?>">
					<input type="hidden" name="spliter" id="spliter" value=",">
					<input type="hidden" name="table" value="customer">

					<?php foreach($dbfields as $field) { ?>
						<input type="hidden" name="field[<?=$field?>]" value="<?php echo $_POST['field'][$field];?>">
					<?php } ?>
					<?php foreach($selfdefinedFields as $selfdefinedField) {
						$field = "selfdefined_".$selfdefinedField['id']; ?>
						<input type="hidden" name="field[<?=$field?>]" value="<?php echo $_POST['field'][$field];?>">
					<?php } ?>
					<?php foreach($dbfields2 as $field) {
						$field = "contactperson_".$field; ?>
						<input type="hidden" name="field[<?=$field?>]" value="<?php echo $_POST['field'][$field];?>">
					<?php } ?>
					<input type="hidden" name="field[groupconnection_fullname_for_comparing]" value="<?php echo $_POST['field']['groupconnection_fullname_for_comparing'];?>">
					<input type="hidden" name="field[groupconnection_previous_sys_id]" value="<?php echo $_POST['field']['groupconnection_previous_sys_id'];?>">
					<input type="hidden" name="field[customerNumber]" value="<?php echo $_POST['field']['customerNumber'];?>">
					<input type="hidden" name="field[customerName]" value="<?php echo $_POST['field']['customerName'];?>">
					<input type="hidden" name="field[customerOrgNr]" value="<?php echo $_POST['field']['customerOrgNr'];?>">
					<input type="hidden" name="field[prospect_value]" value="<?php echo $_POST['field']['prospect_value'];?>">
					<input type="hidden" name="field[subscription_start_date]" value="<?php echo $_POST['field']['subscription_start_date'];?>">
					<input type="hidden" name="field[subscription_nextrenewal_date]" value="<?php echo $_POST['field']['subscription_nextrenewal_date'];?>">
					<input type="hidden" name="field[subscription_name]" value="<?php echo $_POST['field']['subscription_name'];?>">
					<input type="hidden" name="field[subscription_articlenumber1]" value="<?php echo $_POST['field']['subscription_articlenumber1'];?>">
					<input type="hidden" name="field[subscription_articlenumber2]" value="<?php echo $_POST['field']['subscription_articlenumber2'];?>">
					<input type="hidden" name="field[subscription_articlenumber3]" value="<?php echo $_POST['field']['subscription_articlenumber3'];?>">
					<input type="hidden" name="field[subscription_reference]" value="<?php echo $_POST['field']['subscription_reference'];?>">
					<input type="hidden" name="field[subscription_previous_customer_id]" value="<?php echo $_POST['field']['subscription_previous_customer_id'];?>">
					<input type="hidden" name="field[subscription_previous_customer_name]" value="<?php echo $_POST['field']['subscription_previous_customer_name'];?>">
					<input type="hidden" name="field[subscription_subtype_id]" value="<?php echo $_POST['field']['subscription_subtype_id'];?>">
					<input type="hidden" name="field[subscription_invoice_to_other_customer_id]" value="<?php echo $_POST['field']['subscription_invoice_to_other_customer_id'];?>">
					<input type="hidden" name="field[newsletter_fullname]" value="<?php echo $_POST['field']['newsletter_fullname'];?>">
					<input type="hidden" name="field[newsletter_email]" value="<?php echo $_POST['field']['newsletter_email'];?>">
					<input type="hidden" name="field[subunit_id]" value="<?php echo $_POST['field']['subunit_id'];?>">
					<input type="hidden" name="field[subunit_name]" value="<?php echo $_POST['field']['subunit_name'];?>">
					<input type="hidden" name="field[subunit_compare_id]" value="<?php echo $_POST['field']['subunit_compare_id'];?>">
					<input type="hidden" name="field[comparing_previous_customer_id]" value="<?php echo $_POST['field']['comparing_previous_customer_id'];?>">

					</div>
				</div>
			</div>
			<div style="clear: both;"></div>
			</form>
		</div>
	<?php
	$s_popup = ob_get_clean();
	?>

    <?php echo $s_popup;?>
<?php
}
?>
