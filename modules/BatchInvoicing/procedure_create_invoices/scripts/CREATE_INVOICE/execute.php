<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
require_once('Exception.php');
require_once('PHPMailer.php');
require_once('SMTP.php');
require_once(__DIR__.'/../../../output/fnc_getMaxDecimalAmount.php');

if(!function_exists('APIconnectOpen')) include(__DIR__.'/../../../input/includes/APIconnect.php');


if(!function_exists("proc_rem_style")) include(__DIR__."/functions.php");
// Hack - manually update proc line
$procrunlineID = $v_proc_variables['procrunlineID'];

$o_main->db->query("update sys_procrunline set starttime = now(), status = 2 where id = ?", array($procrunlineID));

$o_query = $o_main->db->query("SELECT * FROM accountinfo");
$v_accountinfo = $o_query ? $o_query->row_array() : array();

$o_query = $o_main->db->query("SELECT * FROM mainbook_voucher_basisconfig");
$v_mainbook_voucher_basisconfig = $o_query ? $o_query->row_array() : array();


if ($procrunlineID) {
	//get active running batch
	$s_sql = "SELECT id, log, attached_files FROM batch_invoicing WHERE id = ? ORDER BY id DESC";
	$o_query = $o_main->db->query($s_sql, array($v_proc_variables["batch_invoice_id"]));
	$v_active_batch = $o_query ? $o_query->row_array() : array();
	if($v_active_batch['id'] == $v_proc_variables["batch_invoice_id"]){
		$activateMultiOwnerCompanies = false;
		$s_sql = "SELECT * FROM ownercompany_accountconfig";
		$o_query = $o_main->db->query($s_sql);
		if($o_query && $o_query->num_rows()>0){
			$ownercompanyAccountconfig = $o_query->row_array();
			$activateMultiOwnerCompaniesItem = intval($ownercompanyAccountconfig['max_number_ownercompanies']);
			if ($activateMultiOwnerCompaniesItem > 1) {
				$activateMultiOwnerCompanies = true;
			}
		}

		$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
		$o_query = $o_main->db->query($s_sql);
		$customer_accountconfig = array();
		if($o_query && $o_query->num_rows()>0) {
		    $customer_accountconfig = $o_query->row_array();
		}

	    if (!$activateMultiOwnerCompanies) {
			$s_sql = "SELECT * FROM ownercompany";
			$o_query = $o_main->db->query($s_sql);
			if($o_query && $o_query->num_rows()>0){
				$defaultOwnerCompany = $o_query->row_array();
			}
	        $defaultOwnerCompanyId = $defaultOwnerCompany['id'];
	    }
	    $s_sql = "SELECT * FROM batch_invoicing_basisconfig";
		$o_query = $o_main->db->query($s_sql);
		if($o_query && $o_query->num_rows()>0){
			$basisConfigData = $o_query->row_array();
		}
		$s_sql = "SELECT * FROM batch_invoicing_accountconfig";
		$o_query = $o_main->db->query($s_sql);
		$batchinvoicing_accountconfig = $o_query ? $o_query->row_array() : array();

		if($batchinvoicing_accountconfig['activateCheckForVatCode'] == 1){
			$basisConfigData['activateCheckForVatCode'] = 1;
		} else if($batchinvoicing_accountconfig['activateCheckForVatCode'] == 2) {
			$basisConfigData['activateCheckForVatCode'] = 0;
		}
		if($batchinvoicing_accountconfig['activateCheckForArticleNumber'] == 1){
			$basisConfigData['activateCheckForArticleNumber'] = 1;
		} else if($batchinvoicing_accountconfig['activateCheckForArticleNumber'] == 2) {
			$basisConfigData['activateCheckForArticleNumber'] = 0;
		}
		if($batchinvoicing_accountconfig['activateCheckForProjectNr'] == 1){
			$basisConfigData['activateCheckForProjectNr'] = 1;
		} else if($batchinvoicing_accountconfig['activateCheckForProjectNr'] == 2) {
			$basisConfigData['activateCheckForProjectNr'] = 0;
		}
		if($batchinvoicing_accountconfig['activateCheckForDepartmentCode'] == 1){
			$basisConfigData['activateCheckForDepartmentCode'] = 1;
		} else if($batchinvoicing_accountconfig['activateCheckForDepartmentCode'] == 2) {
			$basisConfigData['activateCheckForDepartmentCode'] = 0;
		}
		if($batchinvoicing_accountconfig['activateCheckForBookaccountNr'] == 1){
			$basisConfigData['activateCheckForBookaccountNr'] = 1;
		} else if($batchinvoicing_accountconfig['activateCheckForBookaccountNr'] == 2) {
			$basisConfigData['activateCheckForBookaccountNr'] = 0;
		}

		$procrunresult = 0;
		$_POST['folder'] = "procedure_create_invoices";

	    // This is lang hack, to force output in norwegien
	    // otherwise, if there is devaccess, readOutputLanguage will set it to eng
	    $devaccess = $variables->developeraccess;
	    $variables->developeraccess = 0;
		include(__DIR__."/../../../output/includes/readOutputLanguage.php");
	    $variables->developeraccess = $devaccess;


		// $s_sql = "SELECT * FROM moduledata WHERE name = 'Orders'";
		// $o_query = $o_main->db->query($s_sql);
		// if($o_query && $o_query->num_rows()>0){
		// 	$orders_module_id_find = $o_query->row_array();
		// 	$orders_module_id = $orders_module_id_find["uniqueID"];
		// }
		$orders_module_id = 0;
		$s_sql = "SELECT * FROM moduledata WHERE name = 'Invoice'";
		$o_query = $o_main->db->query($s_sql);
		if($o_query && $o_query->num_rows()>0){
			$v_row = $o_query->row_array();
		}
		$l_invoice_module_id = $v_row['uniqueID'];
		$v_customers = array();
		$s_sql = "SELECT c.*, pl.extra extra FROM customer c JOIN sys_procrunline pl ON pl.recordID = c.id WHERE pl.id = ?;";
		$o_query = $o_main->db->query($s_sql, array($procrunlineID));
		if($o_query && $o_query->num_rows()>0){
			$v_customers = $o_query->result_array();
		}
		$files_attached = array();
		$files_attached_collectingorder = array();

		$errors = array();
		$totalErrors = isset($_SESSION['invoicing_errors']) ? $_SESSION['invoicing_errors'] : array();
		foreach($v_customers as $v_customer)
		{

			$v_ehf_data = array();
			// Orders array for current customer
			$extraInfo = array();
			if($v_customer['extra'] != "" && $v_customer['extra'] != null){
				$extraInfo = explode("-", $v_customer['extra']);
			}
			$block_group_id = $v_customer['id'];
	        if(count($extraInfo) > 0){
				$block_group_id .= "-".$v_customer['extra'];
	        	$ordersArrayWithCurrencyGroups = $_POST['orders'][$v_customer['id'].'-'.$v_customer['extra']];
	        } else {
	            $ordersArrayWithCurrencyGroups = $_POST['orders'][$v_customer['id']];
	        }

	        $ownercompany_id = $defaultOwnerCompanyId;

	        if($activateMultiOwnerCompanies) {
				$block_group_id = $v_customer['id'];
	            $extraInfo = explode("-", $v_customer['extra']);
	            $ownercompany_id = $extraInfo[0];
	            if(count($extraInfo) > 1){
					$block_group_id .= "-".$v_customer['extra'];
	            	$ordersArrayWithCurrencyGroups = $_POST['orders'][$v_customer['id'].'-'.$v_customer['extra']];
	            } else {
					$block_group_id .= "-".$ownercompany_id;
		            $ordersArrayWithCurrencyGroups = $_POST['orders'][$v_customer['id'].'-'.$ownercompany_id];
		        }
	        }
			$correctOrders = false;

			foreach ($ordersArrayWithCurrencyGroups as $currentCurrency => $ordersArray) {
				$currentEntryOrderIds = array();
				foreach($ordersArray['list']  as $key => $order){
					if(in_array($order['orderId'],$v_proc_variables["order_number"])) {
						$orderId = $order['orderId'];
						if(strpos($orderId, 'adminFee') === false){
							array_push($currentEntryOrderIds, $orderId);
						}
					}
				}
				if($ordersArray['subscriptionId']) {
					$correctOrders = true;
				} else {
					$s_sql = "SELECT orders.*
					FROM orders
					LEFT OUTER JOIN customer_collectingorder ON customer_collectingorder.id = orders.collectingorderId
					WHERE customer_collectingorder.customerId = ? AND orders.id IN (".implode(",",$currentEntryOrderIds).")
					AND (customer_collectingorder.invoiceNumber = 0 || customer_collectingorder.invoiceNumber is null)
					AND orders.content_status = 0";

					$uninvoiced_orders = array();
					$o_query = $o_main->db->query($s_sql, array($v_customer['id']));
					if($o_query && $o_query->num_rows()>0){
						$uninvoiced_orders = $o_query->result_array();
					}
					if(count($uninvoiced_orders) == count($currentEntryOrderIds)){
						$correctOrders = true;
					}
				}
			}
            $_SESSION['correctOrders'] = $correctOrders;
			if($correctOrders) {
		        // Owner company settings
				$s_sql = "SELECT * FROM ownercompany WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($ownercompany_id));
				$v_settings = $o_query ? $o_query->row_array() : array();
				if($v_settings['customerid_autoormanually'] == 2 && intval($v_settings['nextCustomerId']) == 0) {
					$errors[0][] = array(
						'orderId' => 0,
						'errorMsg' => $formText_OwnercompanyMissingCustomerNumber_Output.": ".$v_settings['name']
					);
				}
				$companyaccount = "";
				if ($_POST['bankAccountId'] == 2) {
					$companyaccount = $v_settings['companyBankAccount2'];
				}
				elseif ($_POST['bankAccountId'] == 3) {
					$companyaccount = $v_settings['companyBankAccount3'];
				}
				else {
					$companyaccount = $v_settings['companyaccount'];
				}
				if($companyaccount == ""){
					$errors[0][] = array(
						'orderId' => 0,
						'errorMsg' => $formText_OwnercompanyMissingCompanyBankAccount_Output.": ".$v_settings['name']
					);
				}

				if($v_customer['invoiceBy'] == 2) {
					if($v_customer['useOwnInvoiceAdress'])
					{
						$s_cust_addr_prefix = 'ia';
					} else {
						$s_cust_addr_prefix = 'pa';
					}
					if($v_customer[$s_cust_addr_prefix.'PostalNumber'] == "" || $v_customer[$s_cust_addr_prefix.'City'] == "") {
						$errors[0][] = array(
							'orderId' => 0,
							'errorMsg' => $formText_CustomerMissingPostalAddressPostalNumberAndCityAreMandatory_Output.": ".$v_customer['name']
						);
					}
					if($v_settings['companyzipcode'] == "" || $v_settings['companypostalplace'] == "") {
						$errors[0][] = array(
							'orderId' => 0,
							'errorMsg' => $formText_OwnercompanyMissingPostalAddressPostalNumberAndCityAreMandatory_Output.": ".$v_settings['name']
						);
					}
				}

				foreach ($ordersArrayWithCurrencyGroups as $currentCurrency => $ordersArray) {
					$currentCurrencyDisplay = ($currentCurrency != 'EMPTY_CURRENCY' ? $currentCurrency : '');
					$currentCurrencyCode = $ordersArray['currencyCode'];


					$s_sql = "select * from countryregister where countryID = ?";
					$o_query = $o_main->db->query($s_sql, array($v_customer['paCountry']));
					if($o_query && $o_query->num_rows()>0){
						$v_country = $o_query->row_array();
					}

					$heightAdd = 0;
					if(count($ordersArray['list']) > 25){
						$heightAdd = ($numberOrder - 25) * 17;
					}

					$sum = 0;
					$tax = 0;
					$totalSum = 0;
					$invoiceVatResult = array();
					foreach($ordersArray['list'] as $key => $order){

						if($ordersArray['subscriptionId']) {
							$orderId = $order['orderId'];
							$s_sql = "SELECT * FROM subscriptionline  WHERE subscriptionline.id = ".$order['orderId']."";
							$o_query = $o_main->db->query($s_sql);
							if($o_query && $o_query->num_rows()>0){
								$orderSingle = $o_query->row_array();
                                $orderSingle['priceTotal'] = $orderSingle['pricePerPiece'] * $orderSingle['amount'] * (100-$orderSingle['discountPercent'])/100;
							}

							$s_sql = "SELECT * FROM subscriptionmulti WHERE id = ?";
			                $o_query = $o_main->db->query($s_sql, array($orderSingle['subscribtionId']));
			                $collectingorderItem = $o_query ? $o_query->row_array() : array();
						} else {
							$orderId = $order['orderId'];
							$s_sql = "SELECT * FROM orders LEFT OUTER JOIN subscriptionmulti ON subscriptionmulti.id = orders.invoiceDateSettingFromSubscription WHERE orders.id = ".$order['orderId']." ORDER BY orders.invoiceDateSettingFromSubscription ASC";
							$o_query = $o_main->db->query($s_sql);
							if($o_query && $o_query->num_rows()>0){
								$orderSingle = $o_query->row_array();
							}

							$s_sql = "SELECT * FROM customer_collectingorder WHERE id = ?";
			                $o_query = $o_main->db->query($s_sql, array($orderSingle['collectingorderId']));
			                $collectingorderItem = $o_query ? $o_query->row_array() : array();
						}

						$articleNumber = $orderSingle['articleNumber'];

						if(strpos($order['orderId'], 'adminFee') !== false){
							$articleNumber = $order['articleNumber'];
						}
						// Check for articleNumber
						if ($basisConfigData['activateCheckForArticleNumber']) {
							$articleCount = 0;
							$s_sql = "SELECT * FROM article WHERE id = ?";
							$o_query = $o_main->db->query($s_sql, array($articleNumber));
							if($o_query && $o_query->num_rows()>0){
								$articleCount = $o_query->num_rows();
							}
							if (!$articleCount) {
								$errors[$orderId][] = array(
									'orderId' => $orderId,
									'errorMsg' => $formText_InvalidArticleNumber_output
								);
							}
						}

						// Check VAT code
						if ($basisConfigData['activateCheckForVatCode']) {
							$vatCount = 0;
							$s_sql = "SELECT * FROM vatcode WHERE vatCode = ?";
							$o_query = $o_main->db->query($s_sql, array($order['vatCode']));
							if($o_query && $o_query->num_rows()>0){
								$vatCount = $o_query->num_rows();
							}

							if(!$vatCount) {
								$errors[$orderId][] = array(
									'orderId' => $orderId,
									'errorMsg' => $formText_InvalidVatCode_output
								);
							}
						}

						// EHF check for E+Z combo
						$s_sql = "SELECT * FROM vatcode WHERE vatCode = ?";
						$o_query = $o_main->db->query($s_sql, array($order['vatCode']));
						$vatInfo = $o_query ? $o_query->row_array() : array();
						$invoiceVatResult[$collectingorderItem['id']][] = $vatInfo['ehf'];
						if($vatInfo['ehf'] == 'Z') {
							if(in_array('E', $invoiceVatResult[$collectingorderItem['id']])){
								$errors[$orderId][] = array(
									'orderId' => $orderId,
									'errorMsg' => $formText_InvoiceCanNotContainEAndZTypeCodesAtTheSameTime_output
								);
							}
						} else if($vatInfo['ehf'] == 'E'){
							if(in_array('Z', $invoiceVatResult[$collectingorderItem['id']])){
								$errors[$orderId][] = array(
									'orderId' => $orderId,
									'errorMsg' => $formText_InvoiceCanNotContainEAndZTypeCodesAtTheSameTime_output
								);
							}
						}

						// Check for customer invoiceEmail
						if($v_customer["invoiceBy"] == 1)
						{
							$l_valid_emails = 0;
							$s_emails = str_replace(array(";", chr(0xC2).chr(0xA0)), array(",", ''), $v_customer["invoiceEmail"]);
							$v_emails = explode(",", $s_emails);
							$v_emails = array_map('trim', $v_emails);
							foreach($v_emails as $s_email)
							{
								if(filter_var($s_email, FILTER_VALIDATE_EMAIL))
								{
									$l_valid_emails++;
								} else {
									$errors[$orderId][] = array(
										'orderId' => $orderId,
										'errorMsg' => $formText_FollowingEmailIsInvalid_Output.": ".$s_email.". ".$formText_InvoiceWasNotCreated_Output,
									);
								}
							}
							if(0 == $l_valid_emails)
							{
								$errors[$orderId][] = array(
									'orderId' => $orderId,
									'errorMsg' => $formText_CustomerSupposeToReceiveInvoiceByEmailButNoneSpecified_Output.". ".$formText_InvoiceWasNotCreated_Output,
								);
							}
						}

						// Check bookaccount nr
						if ($basisConfigData['activateCheckForBookaccountNr']) {
							$bookCount = 0;
							$s_sql = "SELECT * FROM bookaccount WHERE accountNr = ?";
							$o_query = $o_main->db->query($s_sql, array($order['bookAccountNr']));
							if($o_query && $o_query->num_rows()>0){
								$bookCount = $o_query->num_rows();
							}

							if(!$bookCount) {
								$errors[$orderId][] = array(
									'orderId' => $orderId,
									'errorMsg' => $formText_InvalidArticleBookAccount_output
								);
							}
						}

						if($order['pricePerPiece'] < 0){
							$errors[$orderId][] = array(
								'orderId' => $orderId,
								'errorMsg' => $formText_PricePerPieceCanNotBeNegativeUseNegativeAmount_output
							);
						}

						if(strpos($order['orderId'], 'adminFee') === false){
							if($collectingorderItem['project2Id']) {
								$s_sql = "SELECT * FROM project2 WHERE id = ?";
				                $o_query = $o_main->db->query($s_sql, array($collectingorderItem['project2Id']));
				                $project2 = $o_query ? $o_query->row_array() : array();

								$orderSingle['projectCode'] = $project2['projectCode'];
								$s_sql = "UPDATE orders SET projectCode = ? WHERE id = ?";
				                $o_query = $o_main->db->query($s_sql, array($orderSingle['projectCode'], $orderSingle['id']));
							}
							// Check for projectFAccNumber
							if ($basisConfigData['activateCheckForProjectNr']) {
								$fAccCount = 0;
								$s_sql = "SELECT * FROM projectforaccounting WHERE projectnumber = ?";
								$o_query = $o_main->db->query($s_sql, array($orderSingle['projectCode']));
								if($o_query && $o_query->num_rows()>0){
									$fAccCount = $o_query->num_rows();
								}
								if (!$fAccCount) {
									$errors[$orderId][] = array(
										'orderId' => $orderId,
										'errorMsg' => $formText_InvalidProjectFAccNumber_output
									);
								}
							}
							if(round($order['priceTotal'], 2) != round($orderSingle['priceTotal'], 2)){
								$errors[$orderId][] = array(
									'orderId' => $orderId,
									'errorMsg' => $formText_TotalPricesDoesNotMatch_output
								);
							}

							$s_sql = "SELECT * FROM vatcode WHERE vatCode = ?";
			                $o_query = $o_main->db->query($s_sql, array($orderSingle['vatCode']));
			                $vatItem = $o_query ? $o_query->row_array() : array();
			                $vatPercent = $vatItem['percentRate'];

							if($order['vatPercentRate'] != $vatPercent){
								$errors[$orderId][] = array(
									'orderId' => $orderId,
									'errorMsg' => $formText_VatPercentDoesNotMatch_output
								);
							}

							$sum += round($orderSingle['priceTotal'], 2);
							$tax += round($orderSingle['priceTotal'] * ($vatPercent/100), 2);
						}
						if($batchinvoicing_accountconfig['forceConnectCreditInvoice']  && $ordersArray['totals']['total'] < 0) {
							$creditRefNo = intval($_POST['creditRefNo']);

							$invoiceCount = 0;
							$s_sql = "SELECT * FROM invoice WHERE external_invoice_nr = ?";
							$o_query = $o_main->db->query($s_sql, array($creditRefNo));
							if($o_query && $o_query->num_rows()>0){
								$invoiceCount = $o_query->num_rows();
							}

							if(!$invoiceCount) {
								$errors[$orderId][] = array(
									'orderId' => $orderId,
									'errorMsg' => $formText_InvoiceIsNotChoosenForCreditInvoice_output
								);
							}
						}

						if(intval($collectingorderItem['id']) == 0){
							$errors[$orderId][] = array(
								'orderId' => $orderId,
								'errorMsg' => $formText_OrderIsMissingCollectingOrder_output
							);
						}

						if($collectingorderItem['project2Id']) {
							$s_sql = "SELECT * FROM project2 WHERE id = ?";
			                $o_query = $o_main->db->query($s_sql, array($collectingorderItem['project2Id']));
			                $project2 = $o_query ? $o_query->row_array() : array();


							$collectingorderItem['department_for_accounting_code'] = $project2['departmentCode'];
							$s_sql = "UPDATE customer_collectingorder SET department_for_accounting_code = ? WHERE id = ?";
			                $o_query = $o_main->db->query($s_sql, array($collectingorderItem['department_for_accounting_code'], $collectingorderItem['id']));
						}

						if ($basisConfigData['activateCheckForDepartmentCode']) {
							if(isset($collectingorderItem['departmentCode'])){
								$collectingorderItem['department_for_accounting_code'] = $collectingorderItem['departmentCode'];
							}
							$fAccCount = 0;
							$s_sql = "SELECT * FROM departmentforaccounting WHERE departmentnumber = ?";
							$o_query = $o_main->db->query($s_sql, array($collectingorderItem['department_for_accounting_code']));
							if($o_query && $o_query->num_rows()>0){
								$fAccCount = $o_query->num_rows();
							}
							if (!$fAccCount) {
								$errors[$orderId][] = array(
									'orderId' => $orderId,
									'errorMsg' => $formText_InvalidDepartmentFAccNumber_output
								);
							}
						}

						$files_to_attach = json_decode($collectingorderItem['files_attached_to_invoice'], true);
						$files_attached_collectingorder[$collectingorderItem['id']] = $files_to_attach;

						$ordersArray['list'][$key]['reference'] = $collectingorderItem['reference'];
						$ordersArray['list'][$key]['delivery_date'] = $collectingorderItem['delivery_date'];
						$ordersArray['list'][$key]['delivery_address_line_1'] = $collectingorderItem['delivery_address_line_1'];
						$ordersArray['list'][$key]['delivery_address_line_2'] = $collectingorderItem['delivery_address_line_2'];
						$ordersArray['list'][$key]['delivery_address_city'] = $collectingorderItem['delivery_address_city'];
						$ordersArray['list'][$key]['delivery_address_postal_code'] = $collectingorderItem['delivery_address_postal_code'];
						$ordersArray['list'][$key]['delivery_address_country'] = $collectingorderItem['delivery_address_country'];

					}
					foreach($files_attached_collectingorder as $files_single){
						$files_attached= array_merge($files_attached, $files_single);
					}
					if(count($errors) > 0)	continue;

					if($ordersArray['subscriptionId']) {
						// Get contact persons
						$s_sql = "SELECT subscriptionline.*, subscriptionmulti.contactPerson as contactPerson FROM subscriptionline
						LEFT OUTER JOIN subscriptionmulti ON subscriptionmulti.id = subscriptionline.subscribtionId
						WHERE subscriptionmulti.customerId = ? AND subscriptionline.id IN (".implode(",",$currentEntryOrderIds).") and subscriptionline.content_status = 0";

						$orderWrites = array();
						$o_query = $o_main->db->query($s_sql, array($v_customer['id']));
						if($o_query && $o_query->num_rows()>0){
							$numberOrder = $o_query->num_rows();
							$orderWrites = $o_query->result_array();
						}
					} else {
						// Get contact persons
						$s_sql = "SELECT orders.*, customer_collectingorder.contactpersonId as contactPerson FROM orders LEFT OUTER JOIN customer_collectingorder ON customer_collectingorder.id = orders.collectingorderId WHERE customer_collectingorder.customerId = ? AND orders.id IN (".implode(",",$currentEntryOrderIds).")  AND (customer_collectingorder.invoiceNumber = 0 || customer_collectingorder.invoiceNumber is null) and orders.content_status = 0;";

						$orderWrites = array();
						$o_query = $o_main->db->query($s_sql, array($v_customer['id']));
						if($o_query && $o_query->num_rows()>0){
							$numberOrder = $o_query->num_rows();
							$orderWrites = $o_query->result_array();
						}
					}


					$contantPersonLine = "";
					$contantPersonLine = "";
					$contactpID = array();
					foreach($orderWrites as $orderWrite){
						if(count($contactpID) == 0 && $orderWrite['contactPerson'] > 0)
							$contactpID[] = $orderWrite['contactPerson'];
						else if(count($contactpID) > 0 && $orderWrite['contactPerson'] > 0 && !array_search($orderWrite['contactPerson'],$contactpID))
						{
							$contactpID[] = $orderWrite['contactPerson'];
						}

					}
					$s_sql = "select * from contactperson where id IN (".implode(", ",$contactpID).")";
					$listcpersons = array();
					$o_query = $o_main->db->query($s_sql);
					if($o_query && $o_query->num_rows()>0){
						$listcpersons = $o_query->result_array();
					}

					foreach($listcpersons as $listcperson){
					   if($contantPersonLine != '')
							$contantPersonLine .= " / ";

						$contantPersonLine .= $listcperson['name']." ".$listcperson['middlename']." ".$listcperson['lastname'];
					}

					foreach($ordersArray['list'] as $key => $order){
						if(in_array($order['orderId'],$v_proc_variables["order_number"])) {
							if(strpos($order['orderId'], 'adminFee') !== false){
								$adminFeeArray = explode("-", $order['orderId']);
								$adminFeeCustomerId = $adminFeeArray[1];
								if($adminFeeCustomerId > 0){
									$s_sql = "SELECT * FROM orders WHERE articleNumber = ? AND articleName = ? AND amount = ? AND pricePerPiece = ? AND discountPercent = ? AND priceTotal = ? AND  bookaccountNr = ? AND vatCode = ? AND vatPercent = ? AND collectingorderId = ? AND adminFee = 1";
									$o_query = $o_main->db->query($s_sql, array($order['articleNumber'], $order['articleName'], $order['amount'], $order['pricePerPiece'], $order['discountPercent'], $order['priceTotal'], $order['bookAccountNr'], $order['vatCode'], $order['vatPercentRate'], $ordersArray['collectingorderId']));
									$adminFeeOrder = $o_query ? $o_query->result_array() : array();
									if(!$adminFeeOrder){
										$o_main->db->query("INSERT INTO orders SET moduleID = ?, createdBy = ?, created = NOW(), articleNumber = ?, articleName = ?, amount = ?, pricePerPiece = ?, discountPercent = ?, priceTotal = ?, status = 4,  bookaccountNr = ?, vatCode = ?, vatPercent = ?, collectingorderId = ?, adminFee = 1", array($orders_module_id, $variables->loggID,  $order['articleNumber'], $order['articleName'], $order['amount'], $order['pricePerPiece'], $order['discountPercent'], $order['priceTotal'], $order['bookAccountNr'], $order['vatCode'], $order['vatPercentRate'], $ordersArray['collectingorderId']));
										$order['realOrderId'] = $o_main->db->insert_id();

										$sum += round($order['priceTotal'], 2);
										$tax += round($order['priceTotal'] * ($order['vatPercentRate']/100), 2);

									} else {
										$order['realOrderId'] = $adminFeeOrder['id'];
									}
									$ordersArray['list'][$key] = $order;
								}
							}
						}
					}
					$totalSum += $tax + $sum;

					$ownercompanyCheck = true;
					$hasAnyDiscount = false;
		            $notOverrideDate = false;

					$defaultCreditDays = 14;
					if($customer_accountconfig['activateDefaultCreditDays'] && $customer_accountconfig['defaultCreditDays'] > 0) {
					    $defaultCreditDays = intval($customer_accountconfig['defaultCreditDays']);
					}

		            $credittimeDays = $v_customer['credittimeDays'];
		            $customerSetting = true;
		            if($credittimeDays == ""){
		            	$credittimeDays = $defaultCreditDays;
		            }
					foreach($ordersArray['list'] as $order){
						if(in_array($order['orderId'],$v_proc_variables["order_number"])) {

							if($order['discountPercent'] > 0) $hasAnyDiscount = true;

							$s_sql = "SELECT * FROM orders LEFT OUTER JOIN subscriptionmulti ON subscriptionmulti.id = orders.invoiceDateSettingFromSubscription WHERE orders.id = ".$order['orderId']." ORDER BY orders.invoiceDateSettingFromSubscription ASC";
							$o_query = $o_main->db->query($s_sql);
							if($o_query && $o_query->num_rows()>0){
								$orderSingle = $o_query->row_array();
								if($orderSingle){
		                        	if($orderSingle['invoiceDateSettingFromSubscription'] > 0){
		                    			$notOverrideDate = true;
		                            	$customerSetting = false;
										$invoicedate_suggestion = $orderSingle['invoicedate_suggestion'];
										$invoicedate_daynumber = $orderSingle['invoicedate_daynumber'];

										$duedate = $orderSingle['duedate'];
										$duedate_daynumber = $orderSingle['duedate_daynumber'];

										switch($invoicedate_suggestion) {
											case 0:
												$dateValShow = date("d.m.Y", time());
											break;
											case 1:
												$dateValShow = date("d.m.Y", strtotime($orderSingle['dateFrom']));
											break;
											case 2:
												$lastDate = date("t", strtotime("-1 month", strtotime($orderSingle['dateFrom'])));
												if($lastDate < $invoicedate_daynumber) {
													$invoicedate_daynumber = $lastDate;
												}
												$dateValShow = date($invoicedate_daynumber.".m.Y", strtotime("-1 month", strtotime($orderSingle['dateFrom'])));
											break;
											case 3:
												$lastDate = date("t", strtotime($orderSingle['dateFrom']));
												if($lastDate < $invoicedate_daynumber) {
													$invoicedate_daynumber = $lastDate;
												}
												$dateValShow = date($invoicedate_daynumber.".m.Y", strtotime($orderSingle['dateFrom']));
											break;
										}
										switch($duedate) {
											case 0:
			                                    $dateExpireShow = date("d.m.Y", strtotime("+".$credittimeDays." days", strtotime($dateValShow)));
											break;
											case 1:
												$dateExpireShow = date("d.m.Y", strtotime("+".$duedate_daynumber." days", strtotime($dateValShow)));
											break;
											case 2:
												$lastDate = date("t", strtotime($dateValShow));
												if($lastDate < $duedate_daynumber) {
													$duedate_daynumber = $lastDate;
												}
												$dateExpireShow = date($duedate_daynumber.".m.Y", strtotime($dateValShow));
											break;
											case 3:
												$lastDate = date("t", strtotime("+1 month", strtotime($dateValShow)));
												if($lastDate < $duedate_daynumber) {
													$duedate_daynumber = $lastDate;
												}
												$dateExpireShow = date($duedate_daynumber.".m.Y", strtotime("+1 month", strtotime($dateValShow)));
				                            break;
										}
									} else {
		                            	if(!$notOverrideDate || ($notOverrideDate && $dateExpireShow == '' && $dateValShow == '')){
		                            		$customerSetting = true;
											$dateValShow = date('d').".".date('m').".".date('Y');
											$dateExpireShow = date("d.m.Y", strtotime("+".$credittimeDays." days", strtotime($dateValShow)));
										}
									}
								}
							}
						}
					}

		            if($ownercompanyCheck){
		                if($customerSetting){
		                    $dateInit = "1_";
		                } else {
		                    $dateInit = "2_";
		                }
		            }
		            if(isset($_SESSION[$dateInit.$dateValShow."/".$dateExpireShow])){
		            	$dateKeyArray = explode("_", $_SESSION[$dateInit.$dateValShow."/".$dateExpireShow]);
		            	$datePairArray = explode("/", $dateKeyArray[1]);
		            	if(count($datePairArray)== 2){
		            		$dateInvoice = $datePairArray[0];
		            		$dateDue = $datePairArray[1];
		            		if($dateInvoice != "" && $dateDue != ""){
		            			$dateValShow = $dateInvoice;
		            			$dateExpireShow = $dateDue;
		            		}
		            	}
		            }
					if(isset($_SESSION[$block_group_id."_".$dateValShow."/".$dateExpireShow])){
		            	$dateKeyArray = explode("_", $_SESSION[$block_group_id."_".$dateValShow."/".$dateExpireShow]);
		            	$datePairArray = explode("/", $dateKeyArray[1]);
		            	if(count($datePairArray)== 2){
		            		$dateInvoice = $datePairArray[0];
		            		$dateDue = $datePairArray[1];
		            		if($dateInvoice != "" && $dateDue != ""){
		            			$dateValShow = $dateInvoice;
		            			$dateExpireShow = $dateDue;
		            		}
		            	}
		            }

					if(isset($_POST["invoice_date"]) && $_POST["invoice_date"] != ""){
						$dateValShow = $_POST["invoice_date"];
					}
					if(isset($_POST["due_date"]) && $_POST["due_date"] != ""){
						$dateExpireShow = $_POST["due_date"];
					}
					$batch_log =" \nChecking dates - ".$invoice_id." ".$dateValShow."/".$dateExpireShow;
					$o_main->db->query("INSERT INTO batch_invoicing_log SET created = NOW(), log = ?, batch_invoicing_id = ?", array($batch_log, $v_proc_variables["batch_invoice_id"]));

					if(strtotime($dateExpireShow) < strtotime($dateValShow)) {
						continue;
					}

		            // HACK! dateVal, dateExpire
		            $dateVal = date('Y-m-d', strtotime($dateValShow));
		            $dateExpire = date('Y-m-d', strtotime($dateExpireShow));

					$s_sql = "SELECT * FROM ownercompany WHERE id = ?";
					$o_query = $o_main->db->query($s_sql, array($ownercompany_id));
					if($o_query && $o_query->num_rows()>0){
						$ownerCompanyData = $o_query->row_array();
					}
					$creditRefNo = intval($_POST['creditRefNo']);

					$kidnumber = 0;
					$external_invoice_nr = 0;

					$files_attached_pdf = array();
					if(count($files_attached) > 0) {
						$files_attached_without_pdf = array();
						foreach($files_attached as $file_to_attach) {
							if(file_exists(__DIR__."/../../../../../".rawurldecode($file_to_attach[1][0]))){
								$mime_type = mime_content_type(__DIR__."/../../../../../".rawurldecode($file_to_attach[1][0]));
								if($mime_type != "application/pdf"){
									array_push($files_attached_without_pdf, $file_to_attach);
								} else {
									array_push($files_attached_pdf, __DIR__."/../../../../../".rawurldecode($file_to_attach[1][0]));
								}
							}
						}
						$files_attached = $files_attached_without_pdf;
					}
					$files_from_batch = json_decode($v_active_batch['attached_files'], true);
					foreach($files_from_batch as $file_to_attach) {
						if(file_exists(__DIR__."/../../../../../".rawurldecode($file_to_attach[1][0]))){
							$mime_type = mime_content_type(__DIR__."/../../../../../".rawurldecode($file_to_attach[1][0]));
							if($mime_type != "application/pdf"){
							} else {
								array_push($files_attached_pdf, __DIR__."/../../../../../".rawurldecode($file_to_attach[1][0]));
							}
						}
					}

					$for_print = 0;
					$for_sending = 0;
					// mark invoice to be send for printing
					if ($batchinvoicing_accountconfig['activate_overwrite_invoice_number'] == 2) {
						$for_print = 1;
					}
					if(intval($v_customer["invoiceBy"]) != 0) {
						$for_print = 0;
						$for_sending = 1;
					}
					$batch_log =" \nHtml for pdf generated - ".$invoice_id." ".date("d.m.Y H:i:s")." ".json_encode($files_attached);
					$o_main->db->query("INSERT INTO batch_invoicing_log SET created = NOW(), log = ?, batch_invoicing_id = ?", array($batch_log, $v_proc_variables["batch_invoice_id"]));

					$files_attached_json = json_encode($files_attached);
		            $invoiceSQL = "INSERT INTO invoice(created, createdBy, moduleID,customerId,invoiceDate,dueDate,totalExTax,tax,totalInclTax, currencyCode, currencyName, ownercompany_id, external_invoice_nr, creditRefNo, files_attached, kidNumber, batch_id, not_processed, for_print, for_sending, do_not_send_invoice, invoiceBy, invoiceEmail)
					VALUES(NOW(), '".$_COOKIE['username']."', '".$l_invoice_module_id."','".$v_customer["id"]."','$dateVal','$dateExpire','$sum','$tax','$totalSum','$currentCurrencyCode','$currentCurrency', '$ownercompany_id', '$external_invoice_nr', $creditRefNo, '$files_attached_json', '$kidnumber',
					'".$v_proc_variables["batch_invoice_id"]."', 1, $for_print, $for_sending, '".$_POST["do_not_send_invoice"]."', '".$v_customer["invoiceBy"]."', '".$v_customer["invoiceEmail"]."');";
					$o_query = $o_main->db->query($invoiceSQL);
					if($o_query){
			            $invoice_id = $o_main->db->insert_id();

						$voucherSql = "INSERT INTO mainbook_voucher SET created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."', voucher_type = '1', date = '".$o_main->db->escape_str($dateVal)."'";
						$o_query = $o_main->db->query($voucherSql);
					 	$voucher_id = $o_main->db->insert_id();
						if($voucher_id > 0) {
							$voucherSql = "INSERT INTO mainbook_voucherlines SET created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."',
							mainbook_voucher_id = '".$o_main->db->escape_str($voucher_id)."', bookaccountNr = '".$o_main->db->escape_str($v_mainbook_voucher_basisconfig['customerBookaccountNr'])."',
						 	description = '".$o_main->db->escape_str($formText_Invoice_output." ".$invoice_id)."', amount = '".$o_main->db->escape_str($totalSum)."', customer_id = '".$o_main->db->escape_str($v_customer['id'])."'";
							$o_query = $o_main->db->query($voucherSql);
						}

						$invoicedOrders_module_id = 0;
						foreach($ordersArray['list'] as $order)
						{
							if(in_array($order['orderId'],$v_proc_variables["order_number"])) {

								if(strpos($order['orderId'], 'adminFee') !== false){
									$order['orderId'] = $order['realOrderId'];
								}

								if($ordersArray['subscriptionId']) {
									$o_main->db->query("UPDATE invoice SET subscriptionmulti_id = ?, draw_case_id = ? WHERE id = ?", array($ordersArray['subscriptionId'], $ordersArray['draw_case_id'], $invoice_id));
								} else {
									$s_sql = "SELECT * FROM orders WHERE id = ?";
									$o_query = $o_main->db->query($s_sql, array($order['orderId']));
									$orderItem = $o_query ? $o_query->row_array() : array();

									$s_sql = "SELECT * FROM vatcode WHERE vatCode = ?";
									$o_query = $o_main->db->query($s_sql, array($orderItem['vatCode']));
									$vatItem = $o_query ? $o_query->row_array() : array();
									$vatPercent = $vatItem['percentRate'];

									$o_main->db->query("UPDATE orders SET gross = ?,  vatPercent = ? WHERE id = ?", array($order['gross'],  $vatPercent, $order['orderId']));

									$o_main->db->query("UPDATE customer_collectingorder SET invoiceNumber = ? WHERE id = ?", array($invoice_id, $orderItem['collectingorderId']));
								}

								if($voucher_id > 0) {
									$amount = $order['gross'];
									$vatAmount = 0;
									if($orderItem['vatCode'] > 0){
										$s_sql = "select * from vatcode WHERE vatCode = '".$o_main->db->escape_str($orderItem['vatCode'])."'";
										$o_query = $o_main->db->query($s_sql);
										$vatcodeItem = ($o_query ? $o_query->row_array() : array());
										if($vatcodeItem) {
											$vatPercent = $vatcodeItem['percentRate'];
											$originalAmount = round($amount/((100+$vatPercent)/100), 2);
											$vatAmount = $amount - $originalAmount;
											$amount = $originalAmount;
										}
									}
									$voucherSql = "INSERT INTO mainbook_voucherlines SET created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."',
									mainbook_voucher_id = '".$o_main->db->escape_str($voucher_id)."', bookaccountNr = '".$o_main->db->escape_str($orderItem['bookaccountNr'])."',
									 description = '".$o_main->db->escape_str($orderItem['articleName'])."', amount = '".$o_main->db->escape_str($amount*(-1))."',
									 vatCode = '".$o_main->db->escape_str($orderItem['vatCode'])."'";
									$o_query = $o_main->db->query($voucherSql);
									if($o_query){
										$voucherlineId = $o_main->db->insert_id();
										if($vatcodeItem) {
											$sql = "INSERT INTO mainbook_voucherlines SET
								            updated = now(),
								            updatedBy = '".$variables->loggID."',
								            mainbook_voucher_id = '".$o_main->db->escape_str($voucher_id)."',
											description = '".$o_main->db->escape_str($formText_Vat_output." ".$orderItem['vatCode'])."',
								            bookaccountNr = '".$o_main->db->escape_str($vatcodeItem['bookaccountNr'])."',
								            amount = '".$o_main->db->escape_str($vatAmount*(-1))."',
								            vatCode = '".$o_main->db->escape_str($orderItem['vatCode'])."',
											vat_line = '".$o_main->db->escape_str($voucherlineId)."'";
											$o_query = $o_main->db->query($sql);
										}
									}
								}
							}
						}

						$s_sql = "SELECT * FROM invoice WHERE id = ?";
						$o_query = $o_main->db->query($s_sql, array($invoice_id));
						$notProcessedInvoice = $o_query ? $o_query->row_array() : array();

						$customerIdToDisplay = $v_customer['id'];
						if ($batchinvoicing_accountconfig['activate_customer_sync_at_start']) {
							$hook_params = array(
								'customer_id' => $v_customer['id'],
								'ownercompany_id' => $ownercompany_id,
							);
							$hook_file = __DIR__ . '/../../../../../' . $batchinvoicing_accountconfig['path_for_customer_sync_at_start'];
							if (file_exists($hook_file)) {
								include $hook_file;
								if (is_callable($run_hook)) {
									$batch_log =" \nHook Started for customer ".$invoice_id." - ".date("d.m.Y H:i:s");
									$o_main->db->query("INSERT INTO batch_invoicing_log SET created = NOW(), log = ?, batch_invoicing_id = ?", array($batch_log, $notProcessedInvoice["batch_id"]));

									$hook_result = $run_hook($hook_params);

									$batch_log =" \nHook Result ".$invoice_id." ".json_encode($hook_result)." - ".date("d.m.Y H:i:s");
									$o_main->db->query("INSERT INTO batch_invoicing_log SET created = NOW(), log = ?, batch_invoicing_id = ?", array($batch_log, $notProcessedInvoice["batch_id"]));

									unset($run_hook);
									// Get customer external id
									$s_sql = "SELECT * FROM customer_externalsystem_id WHERE ownercompany_id = ? AND customer_id = ?";
									$o_query = $o_main->db->query($s_sql, array($ownercompany_id, $v_customer['id']));

									$externalCustomerIdData = $o_query ? $o_query->row_array() : array();
									$externalCustomerId = $externalCustomerIdData['external_id'];

									if ($externalCustomerId) {
										$customerIdToDisplay = $externalCustomerId;
										$external_sys_id = $externalCustomerIdData['external_sys_id'];
									}
								}
							}
						} else {

							if ($ownercompanyAccountconfig['activate_global_external_company_id']) {
								// Get customer external id
								$s_sql = "SELECT * FROM customer_externalsystem_id WHERE ownercompany_id = 0 AND customer_id = ?";
								$o_query = $o_main->db->query($s_sql, array($v_customer['id']));
								$externalCustomerIdData = $o_query ? $o_query->row_array() : array();

								$externalCustomerId = $externalCustomerIdData['external_id'];
								$customerIdToDisplay = $externalCustomerId;

								if (!$externalCustomerId) {
									$nextCustomerId = $ownercompanyAccountconfig['next_global_external_company_id'] ? $ownercompanyAccountconfig['next_global_external_company_id'] : 1;
									$o_main->db->query("INSERT INTO customer_externalsystem_id
									SET created = NOW(),
									ownercompany_id = ?,
									customer_id = ?,
									external_id = ?,
									external_sys_id = ?", array(0, $v_customer['id'], $nextCustomerId, 0));

									$customerIdToDisplay = $nextCustomerId;

									$nextCustomerId++;

									$o_main->db->query("UPDATE ownercompany_accountconfig SET next_global_external_company_id = $nextCustomerId");
								}
							}
							else {
								if ($v_settings['customerid_autoormanually']) {

									// Get customer external id
									$s_sql = "SELECT * FROM customer_externalsystem_id WHERE ownercompany_id = $ownercompany_id AND customer_id = ?";
									$o_query = $o_main->db->query($s_sql, array($v_customer['id']));
									$externalCustomerIdData = $o_query ? $o_query->row_array() : array();
									$externalCustomerId = $externalCustomerIdData['external_id'];

									if ($externalCustomerId) {
										$customerIdToDisplay = $externalCustomerId;
										$external_sys_id = $externalCustomerIdData['external_sys_id'];

										if ($use_integration) {
											$api->update_customer(array(
												'id' => $external_sys_id,
												'name' => trim($v_customer['name']),
												'customerNumber' => $externalCustomerId
											));
										}

									} else {
										// Create automatically
										if ($v_settings['customerid_autoormanually'] == '1') {

											// next customer id
											$nextCustomerId = $v_settings['nextCustomerId'];

											if ($use_integration) {
												$new_customer_on_api = $api->add_customer(array(
													'name' => trim($v_customer['name']),
													'customerNumber' => $nextCustomerId
												));
												$external_sys_id = $new_customer_on_api['id'] ? $new_customer_on_api['id'] : 0;
												$nextCustomerId = $new_customer_on_api['customerNumber'];
											} else {
												$external_sys_id = 0;
											}

											$o_main->db->query("INSERT INTO customer_externalsystem_id
											SET created = NOW(),
											ownercompany_id = ?,
											customer_id = ?,
											external_id = ?,
											external_sys_id = ?", array($ownercompany_id, $v_customer['id'], $nextCustomerId, $external_sys_id));

											$customerIdToDisplay = $nextCustomerId;

											$nextCustomerId++;

											$o_main->db->query("UPDATE ownercompany SET nextCustomerId = $nextCustomerId WHERE id = ?", array($ownercompany_id));
										}
									}
								}
							}
						}

						$external_invoice_nr = 0;
						$kidnumber = 0;
						if (intval($batchinvoicing_accountconfig['activate_overwrite_invoice_number']) == 0)  {
							$external_invoice_nr = intval($v_settings['nextInvoiceNr']);
							$newInvoiceNrOnInvoice = $external_invoice_nr;
							$kidnumber = generate_kidnumber($v_settings, $customerIdToDisplay, $newInvoiceNrOnInvoice, 0);

						} else if ($batchinvoicing_accountconfig['activate_overwrite_invoice_number'] == 1) {
							$hook_params = array(
							);

							$hook_file = __DIR__ . '/../../../../../' . $batchinvoicing_accountconfig['path_overwrite_invoice_number'];
							if (file_exists($hook_file)) {
								include $hook_file;
								if (is_callable($run_hook)) {
									$hook_result = $run_hook($hook_params);
									unset($run_hook);

									if ($hook_result['next_invoice_number']) {
										$external_invoice_nr = $hook_result['next_invoice_number'];
										$newInvoiceNrOnInvoice = $hook_result['next_invoice_number'];

										$kidnumber = generate_kidnumber($v_settings, $customerIdToDisplay, $newInvoiceNrOnInvoice, 0);
									}
								}
							}
						} else if($batchinvoicing_accountconfig['activate_overwrite_invoice_number'] == 2) {
							$external_invoice_nr = 0;
							$newInvoiceNrOnInvoice = 0;
							if($notProcessedInvoice['external_invoice_nr'] > 0){
								$external_invoice_nr = $notProcessedInvoice['external_invoice_nr'];
								$newInvoiceNrOnInvoice = $notProcessedInvoice['external_invoice_nr'];
							}
						}

						if(intval($batchinvoicing_accountconfig['activate_not_update_kid_number']) == 1)  {
							$kidnumber = 0;
							if($notProcessedInvoice['kidNumber'] > 0){
								$kidnumber = $notProcessedInvoice['kidNumber'];
							}
						}

						if(intval($batchinvoicing_accountconfig['activate_overwrite_invoice_number']) == 0){
							// update next invoice nr
							$update_next_invoice_nr = $external_invoice_nr + 1;
							$o_main->db->query("UPDATE ownercompany SET nextInvoiceNr = $update_next_invoice_nr WHERE id = ?", array($ownercompany_id));
						}

						$o_query = $o_main->db->query("UPDATE invoice SET kidNumber = ?, external_invoice_nr = ? WHERE id = ?", array($kidnumber, $external_invoice_nr, $invoice_id));

						// Get bank account nr, iban, swift
						$bankAccountData = array();
						if ($_POST['bankAccountId'] == 2) {
							$bankAccountData['companyaccount'] = $v_settings['companyBankAccount2'];
							$bankAccountData['companyiban'] = $v_settings['companyiban2'];
							$bankAccountData['companyswift'] = $v_settings['companyswift2'];
						}
						elseif ($_POST['bankAccountId'] == 3) {
							$bankAccountData['companyaccount'] = $v_settings['companyBankAccount3'];
							$bankAccountData['companyiban'] = $v_settings['companyiban3'];
							$bankAccountData['companyswift'] = $v_settings['companyswift3'];
						}
						else {
							$bankAccountData['companyaccount'] = $v_settings['companyaccount'];
							$bankAccountData['companyiban'] = $v_settings['companyiban'];
							$bankAccountData['companyswift'] = $v_settings['companyswift'];
						}
						// Customer address
						if($v_customer['useOwnInvoiceAdress']) {
							$s_cust_addr_prefix = 'ia';
							$customerAddress = 'own address';
							$customerAddress = $v_customer['iaStreet1']."<br />".(!empty($v_customer['iaStreet2']) ? $v_customer['iaStreet2'] . '<br />' : '').$v_customer['iaPostalNumber']." ".$v_customer['iaCity'] . "<br>" . $v_customer['iaCountry'];
						} else {
							$s_cust_addr_prefix = 'pa';
							$customerAddress = $v_customer['paStreet']."<br />".(!empty($v_customer['paStreet2']) ? $v_customer['paStreet2'] . '<br />' : '').$v_customer['paPostalNumber']." ".$v_customer['paCity'] . "<br>" . $v_customer['paCountry'];
						}
						$s_customer = trim($v_customer['name']." ".$v_customer['middlename']." ".$v_customer['lastname'])."<br />".$customerAddress;

						$decimalPlaces = $v_settings['numberDecimalsOnInvoice'] ? intval($v_settings['numberDecimalsOnInvoice']) : 2;
						$invoicelogo = json_decode($v_settings['invoicelogo'],true);

			            if($notProcessedInvoice['subscriptionmulti_id']){
			                $v_settings['choose_custom_invoice_template'] = 'Creditor';
			            }
						$function = 'generateHtml';
						if('' != trim($v_settings['choose_custom_invoice_template']))
						{
							$function = 'generateHtml'.$v_settings['choose_custom_invoice_template'];
							if(!function_exists($function)) $function = 'generateHtml';
						}

						$s_reference = $s_delivery_date = $s_delivery_address = '';
						$hasAnyDiscount = false;

						$dateValShow = date("d.m.Y", strtotime($notProcessedInvoice['invoiceDate']));
						$dateExpireShow = date("d.m.Y", strtotime($notProcessedInvoice['dueDate']));

						$contantPersonLine = "";
						$contactpID = array();
						$ordersArray = array();
						$ordersArray['subscriptionId'] = $notProcessedInvoice['subscriptionmulti_id'];

						if($ordersArray['subscriptionId']) {
							// Get contact persons
							$s_sql = "SELECT subscriptionline.*, subscriptionmulti.contactPerson as contactPerson FROM subscriptionline
							LEFT OUTER JOIN subscriptionmulti ON subscriptionmulti.id = subscriptionline.subscribtionId
							WHERE subscriptionmulti.customerId = ? AND subscriptionmulti.id = ? and subscriptionline.content_status = 0";

							$orderWrites = array();
							$o_query = $o_main->db->query($s_sql, array($v_customer['id'], $notProcessedInvoice['subscriptionmulti_id']));
							if($o_query && $o_query->num_rows()>0){
								$numberOrder = $o_query->num_rows();
								$orderWrites = $o_query->result_array();
							}
						} else {
							// Get contact persons
							$s_sql = "SELECT orders.*, customer_collectingorder.contactpersonId as contactPerson, customer_collectingorder.reference,customer_collectingorder.delivery_date,
							customer_collectingorder.delivery_address_line_1,customer_collectingorder.delivery_address_line_2,customer_collectingorder.delivery_address_city,customer_collectingorder.delivery_address_postal_code, customer_collectingorder.delivery_address_country
							FROM orders
							LEFT OUTER JOIN customer_collectingorder ON customer_collectingorder.id = orders.collectingorderId
							WHERE customer_collectingorder.customerId = ? AND customer_collectingorder.invoiceNumber = ?
							AND orders.content_status = 0;";

							$orderWrites = array();
							$o_query = $o_main->db->query($s_sql, array($v_customer['id'], $notProcessedInvoice['id']));
							if($o_query && $o_query->num_rows()>0){
								$numberOrder = $o_query->num_rows();
								$orderWrites = $o_query->result_array();
							}
						}

						foreach($orderWrites as $orderWrite) {

							if($orderWrite['discountPercent'] > 0) $hasAnyDiscount = true;
							$s_reference = $orderWrite['reference'];
							$s_delivery_date = ((!empty($orderWrite['delivery_date']) && $orderWrite['delivery_date'] != '0000-00-00') ? date('d.m.Y', strtotime($orderWrite['delivery_date'])) : '');
							$s_delivery_address = trim(preg_replace('/\s+/', ' ', $orderWrite['delivery_address_line_1'].' '.$orderWrite['delivery_address_line_2'].' '.$orderWrite['delivery_address_city'].' '.$orderWrite['delivery_address_postal_code'].' '.$v_countries[$orderWrite['delivery_address_country']]));

							if(count($contactpID) == 0 && $orderWrite['contactPerson'] > 0)
								$contactpID[] = $orderWrite['contactPerson'];
							else if(count($contactpID) > 0 && $orderWrite['contactPerson'] > 0 && !array_search($orderWrite['contactPerson'],$contactpID))
							{
								$contactpID[] = $orderWrite['contactPerson'];
							}
							$orderWrite['orderId'] = $orderWrite['id'];
							$orderWrite['vatPercentRate'] = $orderWrite['vatPercent'];

							$vat = round($orderWrite['priceTotal'] * ($orderWrite['vatPercent']/100), $decimalPlaces);
							$orderWrite['vat'] = $vat;
							$ordersArray['list'][] = $orderWrite;
						}

						$s_sql = "select * from contactperson where id IN (".implode(", ",$contactpID).")";
						$listcpersons = array();
						$o_query = $o_main->db->query($s_sql);
						if($o_query && $o_query->num_rows()>0){
							$listcpersons = $o_query->result_array();
						}
						foreach($listcpersons as $listcperson){
						   if($contantPersonLine != '')
								$contantPersonLine .= " / ";

							$contantPersonLine .= $listcperson['name']." ".$listcperson['middlename']." ".$listcperson['lastname'];
						}
						$ordersArray['totals']['totalSum'] = $notProcessedInvoice['totalExTax'];
						$ordersArray['totals']['totalVat'] = $notProcessedInvoice['tax'];
						$ordersArray['totals']['total'] = $notProcessedInvoice['totalInclTax'];

						if ($batchinvoicing_accountconfig['activate_overwrite_invoice_number'] != 2) {
							list($html1, $html2, $html3, $html4, $html5) = $function($ordersArray, $v_customer, $v_settings, $bankAccountData, $contantPersonLine,$s_reference,$s_delivery_date,$s_delivery_address,$customerIdToDisplay,$dateValShow,$dateExpireShow,$hasAnyDiscount,$currentCurrencyDisplay, $decimalPlaces, array(), array(), $v_accountinfo['accountname']);
							// END OF BUILDING PDF
							$batch_log =" \nHtml for pdf generated - ".$invoice_id." ".date("d.m.Y H:i:s");
							$o_main->db->query("INSERT INTO batch_invoicing_log SET created = NOW(), log = ?, batch_invoicing_id = ?", array($batch_log, $notProcessedInvoice["batch_id"]));

							/// ehf
							file_put_contents(__DIR__ . '/../../../../../uploads/test-'.$currentCurrencyDisplay.'.txt', $html);

							$file = "invoice_".$newInvoiceNrOnInvoice;
							if ($activateMultiOwnerCompanies)
							{
								$file = "invoice_oc".$ownercompany_id."_".$newInvoiceNrOnInvoice;
							}

							$filepath = __DIR__."/../../../../../uploads/protected/invoices_ehf/";

							$s_ehf_file = $file;
							$s_ehf_file_path = $filepath.$s_ehf_file.'.xml';

							//default temp
							if($v_settings['invoice_template'] != 1){
								$html = $html1 . $newInvoiceNrOnInvoice . $html2;
								if($v_settings['kidOnInvoice'] > 0 || $batchinvoicing_accountconfig['activate_not_update_kid_number'])
									$html .=  $html3 .  $kidnumber .$html4;
								$html .=  $html5;
								$html = html_entity_decode($html);
								$html = $html;
							} else {
								//alternative
								$html = $html1 . $newInvoiceNrOnInvoice . $html2;
								$html .=  $html5;


								$html = html_entity_decode($html);
								$html = $html;
								if($v_settings['kidOnInvoice'] > 0 || $batchinvoicing_accountconfig['activate_not_update_kid_number']){
									$html_footer .=  $html3 . "<b>".$kidnumber."</b>" .$html4;
								} else {
									$html_footer .=  $html3 .$html4;
								}
							}

							$file .= ".pdf";
							$filepath = __DIR__."/../../../../../uploads/protected/invoices/";
							$s_pdf_file_path = $filepath.$file;
							if(!file_exists($filepath))
							{
								mkdir($filepath, 0777,true);
							}
							chmod($filepath, 0777);
							$batch_log =" \nPDF starting ".$invoice_id." - ".date("d.m.Y H:i:s").$v_accountinfo['accountname']." ".json_encode($html);
							$o_main->db->query("INSERT INTO batch_invoicing_log SET created = NOW(), log = ?, batch_invoicing_id = ?", array($batch_log, $notProcessedInvoice["batch_id"]));

							create_pdf($filepath, $file, $files_attached_pdf, $newInvoiceNrOnInvoice, $invoicelogo, $v_settings, $v_accountinfo['accountname'], $html, trim($v_settings['choose_custom_invoice_template']), $html_footer);

							$batch_log =" \nPDF created ".$invoice_id." - ".date("d.m.Y H:i:s");
							$o_main->db->query("INSERT INTO batch_invoicing_log SET created = NOW(), log = ?, batch_invoicing_id = ?", array($batch_log, $notProcessedInvoice["batch_id"]));

							if(!file_exists(__DIR__."/../../../../../uploads/protected/invoices/".$file)) {
								$errors[0][] = array(
									'orderId' => 0,
									'invoice' => $updated_invoice,
									'errorMsg' => $invoice_id['id']." ".$formText_ErrorCreatingPdf
								);
								continue;
							}
							$o_query = $o_main->db->query("UPDATE invoice SET invoiceFile = ? WHERE id = ?", array("uploads/protected/invoices/".$file, $invoice_id));
						}
					} else {
						$errors[0][] = array(
							'orderId' => 0,
							'errorMsg' => $formText_ErrorCreatingInvoice_Output
						);
					}
				}
				$v_proc_variables["lines_total"]++;
				//}
				$procrunresult = 1;
			} else {
				$errors[0][] = array(
					'orderId' => 0,
					'errorMsg' => $formText_OrdersWereAlreadyInvoicedPleaseRefreshPage_Output
				);
			}
		}
		//merging all errors from other invoices in same batch into 1
		$totalErrors = array_merge($errors, $totalErrors);
		$_SESSION['invoicing_errors'] = $totalErrors;

		if ($hook_result['emergencyLock']) {
			$sql = "UPDATE batch_invoicing SET emergency_lock = 1 ORDER BY id DESC LIMIT 1";
			$o_query = $o_main->db->query($sql);
		}
        if(!$v_proc_variables['subscriptionInvoicing']) {
			$hook_result_return = array();
			if($hook_result['emergencyLock']){
				$hook_result_return['emergencyLock'] = $hook_result['emergencyLock'];
			}
            echo json_encode(array(
               'hook_result' => $hook_result_return
            ));
        } else {
            $_SESSION['hook_result'] = $hook_result;
        }

		$s_sql = "SELECT * FROM invoice WHERE not_processed = 1";
		$o_query = $o_main->db->query($s_sql);
		$notProcessedInvoices = $o_query ? $o_query->result_array() : array();
		if(count($notProcessedInvoices) > 0) {
			$s_sql = "SELECT * FROM auto_task WHERE script_path = ? AND content_status <> 2";
			$o_query = $o_main->db->query($s_sql, array('modules/BatchInvoicing/autotask_process_invoices/run.php'));
			$autoTask = $o_query ? $o_query->row_array() : array();
			if($autoTask){
				if(intval($autoTask['content_status']) != 0){
					$s_sql = "UPDATE auto_task SET
					updated = now(),
					updatedBy= ?,
					content_status = 0,
					next_run = ?
					WHERE id = ?";
					$o_main->db->query($s_sql, array($_COOKIE['username'], date("Y-m-d H:i:s", time()+60), $autoTask['id']));
				}
			} else {
				include(__DIR__.'/../../../../../modules/BatchInvoicing/autotask_process_invoices/config.php');
				$s_sql = "INSERT INTO auto_task SET
				id=NULL,
				created = now(),
				createdBy= ?,
				script_path= ?,
				config = ?,
				next_run = ?";
				$o_main->db->query($s_sql, array($_COOKIE['username'], 'modules/BatchInvoicing/autotask_process_invoices/run.php', json_encode($v_auto_task_config), date("Y-m-d H:i:s", time()+60)));
			}
		}
	}
}

$o_main->db->query("update sys_procrunline set stoptime = now(), status = 1, statustext = ? where id = ?", array($procrunresulttext, $procrunlineID));
