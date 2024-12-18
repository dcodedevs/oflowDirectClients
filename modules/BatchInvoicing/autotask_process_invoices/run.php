<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
ob_start();
register_shutdown_function('catch_fatal_error');
$v_return = array(
	'status' => 0,
	'messages' => array(),
);
require_once('Exception.php');
require_once('PHPMailer.php');
require_once('SMTP.php');


define('BASEPATH', realpath(__DIR__.'/../../../').'/');
require_once(BASEPATH.'elementsGlobal/cMain.php');
include_once(__DIR__."/includes/readOutputLanguage.php");


if(!function_exists("proc_rem_style")) include(__DIR__."/../procedure_create_invoices/scripts/CREATE_INVOICE/functions.php");
function generateRandomString($length = 10) {
	return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}
$v_input = $_SERVER['argv'];
list($s_script_path, $l_auto_task_id) = $v_input;
$s_sql = "SELECT at.*, atl.id AS auto_task_log_id FROM auto_task at JOIN auto_task_log atl ON atl.auto_task_id = at.id WHERE at.id = '".$o_main->db->escape_str($l_auto_task_id)."' AND atl.status = 1";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0)
{
	$v_auto_task = $o_query->row_array();
	$o_main->db->query("UPDATE auto_task_log SET status = 2, started = NOW() WHERE id = '".$o_main->db->escape_str($v_auto_task['auto_task_log_id'])."'");
	$v_auto_task_config = json_decode($v_auto_task['config'], TRUE);

	$l_days = intval($v_auto_task_config['parameters']['days']['value']);
	if(0 == $l_days) $l_days = 180;

	if(!function_exists('APIconnectorAccount')) include(__DIR__.'/../../../fw/account_fw/includes/APIconnector.php');
	if(!function_exists('APIconnectOpen')) include(__DIR__.'/../input/includes/APIconnect.php');
	$v_countries = array();
	$v_response = json_decode(APIconnectOpen("countrylistget"), TRUE);
	if(isset($v_response['status']) && $v_response['status'] == 1)
	{
		foreach($v_response['data'] as $v_item)
		{
			$v_countries[$v_item['countryID']] = $v_item['name'];
		}
	}
	$errors = array();

	require_once(__DIR__."/../output/includes/tcpdf/config/lang/eng.php");
	require_once(__DIR__."/../output/includes/tcpdf/tcpdf.php");
	require_once(__DIR__."/../output/includes/fpdi/fpdi.php");

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

	try {
		$sql_next = "SELECT * FROM invoice WHERE not_processed = 1 ORDER BY id ASC LIMIT 1";
		$o_next = $o_main->db->query($sql_next);
		$invoiceTries = 0;
		while($o_next && $o_next->num_rows() > 0) {
			$invoiceTries++;
			if($invoiceTries > 10){
				break;
			}
			$notProcessedInvoice = $o_next ? $o_next->row_array() : array();
			$o_query = $o_main->db->query("UPDATE invoice SET not_processed = -1 WHERE id = ?", array($notProcessedInvoice['id']));

			$sql_next = "SELECT * FROM invoice WHERE not_processed = 1 ORDER BY id ASC LIMIT 1";
			$o_next = $o_main->db->query($sql_next);

			$activateMultiOwnerCompanies = false;
			$s_sql = "SELECT * FROM ownercompany_accountconfig";
			$o_query = $o_main->db->query($s_sql);
			$ownercompanyAccountconfig = $o_query ? $o_query->row_array() : array();
			$activateMultiOwnerCompaniesItem = intval($ownercompanyAccountconfig['max_number_ownercompanies']);
			if ($activateMultiOwnerCompaniesItem > 1) {
				$activateMultiOwnerCompanies = true;
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

			$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
			$o_query = $o_main->db->query($s_sql);
			$customer_accountconfig = $o_query ? $o_query->row_array() : array();

			$o_query = $o_main->db->query("SELECT * FROM accountinfo");
			$v_accountinfo = $o_query ? $o_query->row_array() : array();

			$files_attached = array();
			$files_attached_collectingorder = array();
			$ownercompany_id = $notProcessedInvoice['ownercompany_id'];
			$customer_id = $notProcessedInvoice['customerId'];
			$invoice_id = $notProcessedInvoice['id'];

			$s_sql = "SELECT id, log, createdBy, server_url FROM batch_invoicing WHERE id = ? ORDER BY id DESC";
			$o_query = $o_main->db->query($s_sql, array($notProcessedInvoice['batch_id']));
			$v_active_batch = $o_query ? $o_query->row_array() : array();

			$batch_log = $v_active_batch['log'];

			$s_sql = "SELECT * FROM customer WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($customer_id));
			$v_customer = $o_query ? $o_query->row_array() : array();
			// Owner company settings
			$s_sql = "SELECT * FROM ownercompany WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($ownercompany_id));
			$v_settings = $o_query ? $o_query->row_array() : array();

			if($v_customer && $v_settings) {

				$s_sql = "SELECT * FROM customer_collectingorder WHERE invoiceNumber = ?";
				$o_query = $o_main->db->query($s_sql, array($notProcessedInvoice['id']));
				$collectingOrders = $o_query ? $o_query->result_array() : array();

				foreach($collectingOrders as $collectingOrder){
					$files_to_attach = json_decode($collectingOrder['files_attached_to_invoice'], true);
					$files_attached_collectingorder[$collectingOrder['id']] = $files_to_attach;

					$s_sql = "SELECT * FROM orders WHERE collectingorderId = ?";
					$o_query = $o_main->db->query($s_sql, array($collectingOrder['id']));
					$orderlines = $o_query ? $o_query->result_array() : array();
					foreach($orderlines as $orderline){
						$s_sql = "UPDATE orders SET projectCode = ?, departmentCode = ? WHERE id = ?";
						$o_query = $o_main->db->query($s_sql, array($collectingOrder['accountingProjectCode'],$collectingOrder['department_for_accounting_code'], $orderline['id']));
					}
				}
				foreach($files_attached_collectingorder as $files_single){
					$files_attached = array_merge($files_attached, $files_single);
				}

				$attachedFilesCorrect = true;
				$files_attached_pdf = array();
				$missing_files = array();

				if(count($files_attached) > 0) {
					$files_attached_without_pdf = array();
					foreach($files_attached as $file_to_attach) {
						if(file_exists(__DIR__."/../../../".rawurldecode($file_to_attach[1][0]))){
							$mime_type = mime_content_type(__DIR__."/../../../".rawurldecode($file_to_attach[1][0]));
							if($mime_type != "application/pdf"){
								array_push($files_attached_without_pdf, $file_to_attach);
							} else {
								array_push($files_attached_pdf, __DIR__."/../../../".rawurldecode($file_to_attach[1][0]));
							}
						} else {
							$missing_files[] = $file_to_attach[1][0];
							$attachedFilesCorrect = false;
						}
					}
					$files_attached = $files_attached_without_pdf;
				}
				if(!$attachedFilesCorrect){
					$errors[0][] = array(
						'orderId' => 0,
						'invoice' => $notProcessedInvoice,
						'errorMsg' => $notProcessedInvoice['id']." ".$formText_MissingFilesToAttachToInvoice." ".json_encode($missing_files)
					);
					continue;
				}

				/**
				 * Integration
				 */

				// If global customer numbers activated, we use global numbers & global integration
				if ($ownercompanyAccountconfig['activate_global_external_company_id']) {

				}
				else {
					// Integration based on ownercompany
					$use_integration = false;
					if ($v_settings['use_integration'] && $v_settings['use_integration'] != '0') {
						$integration = $v_settings['use_integration'];
						$integration_file = __DIR__ . '/../../'. $integration .'/api/load.php';
						if (file_exists($integration_file)) {
							include $integration_file;
							if (class_exists($integration)) {
								if ($api) unset($api);

								$api = new $integration(array(
									'ownercompany_id' => $ownercompany_id,
									'o_main' => $o_main
								));
								$use_integration = true;
							}
						}
					}
				}

				/**
				 * Customer ID
				 */
				$customerIdToDisplay = $v_customer['id'];
				if ($batchinvoicing_accountconfig['activate_customer_sync_at_start']) {
					$s_sql = "SELECT * FROM customer_externalsystem_id WHERE ownercompany_id = ? AND customer_id = ?";
					$o_query = $o_main->db->query($s_sql, array($ownercompany_id, $v_customer['id']));

					$externalCustomerIdData = $o_query ? $o_query->row_array() : array();
					$externalCustomerId = $externalCustomerIdData['external_id'];

					if ($externalCustomerId) {
						$customerIdToDisplay = $externalCustomerId;
						$external_sys_id = $externalCustomerIdData['external_sys_id'];
					}
				}else {
					if ($ownercompanyAccountconfig['activate_global_external_company_id']) {
						$s_sql = "SELECT * FROM customer_externalsystem_id WHERE ownercompany_id = 0 AND customer_id = ?";
						$o_query = $o_main->db->query($s_sql, array($v_customer['id']));
						$externalCustomerIdData = $o_query ? $o_query->row_array() : array();

						$externalCustomerId = $externalCustomerIdData['external_id'];
						$customerIdToDisplay = $externalCustomerId;
					}else {
						if ($v_settings['customerid_autoormanually']) {
							$s_sql = "SELECT * FROM customer_externalsystem_id WHERE ownercompany_id = $ownercompany_id AND customer_id = ?";
							$o_query = $o_main->db->query($s_sql, array($v_customer['id']));
							$externalCustomerIdData = $o_query ? $o_query->row_array() : array();
							$externalCustomerId = $externalCustomerIdData['external_id'];
							if ($externalCustomerId) {
								$customerIdToDisplay = $externalCustomerId;
								$external_sys_id = $externalCustomerIdData['external_sys_id'];
							}
						}
					}
				}

				$external_invoice_nr = $notProcessedInvoice['external_invoice_nr'];
				$kidnumber = $notProcessedInvoice['kidNumber'];
				$newInvoiceNrOnInvoice = $external_invoice_nr;

				$currentCurrency = $notProcessedInvoice['currencyName'];
				$currentCurrencyDisplay = ($currentCurrency != 'EMPTY_CURRENCY' ? $currentCurrency : '');
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

				if ($batchinvoicing_accountconfig['activate_overwrite_invoice_number'] == 2) {
					list($html1, $html2, $html3, $html4, $html5) = $function($ordersArray, $v_customer, $v_settings, $bankAccountData, $contantPersonLine,$s_reference,$s_delivery_date,$s_delivery_address,$customerIdToDisplay,$dateValShow,$dateExpireShow,$hasAnyDiscount,$currentCurrencyDisplay, $decimalPlaces, array(), array(), $v_accountinfo['accountname']);
					// END OF BUILDING PDF
					$batch_log=" \nHtml for pdf generated - ".$notProcessedInvoice['id']." ".date("d.m.Y H:i:s");
					$o_main->db->query("INSERT INTO batch_invoicing_log SET created = NOW(), log = ?, batch_invoicing_id = ?", array($batch_log, $notProcessedInvoice["batch_id"]));

				}
				$sync_invoice_attachment = false;
				// HOOK: Syncing of customer and invoice after invoice is created
				if ($batchinvoicing_accountconfig['activate_syncing_of_customer_and_invoice']) {
					if ($batchinvoicing_accountconfig['activate_overwrite_invoice_number'] != 2) {
						if(intval($notProcessedInvoice['external_invoice_nr']) == 0) {
							$errors[0][] = array(
								'orderId' => 0,
								'invoice' => $notProcessedInvoice,
								'errorMsg' => $notProcessedInvoice['id']." ".$formText_InvoiceMissingExternalInvoiceNr
							);
							continue;
						}
					}
					$hook_params = array(
						'ownercompany_id'=> $ownercompany_id,
						'invoice_id' => $invoice_id
					);

					$hook_file = __DIR__ . '/../../../' . $batchinvoicing_accountconfig['path_syncing_of_customer_and_invoice'];
					if (file_exists($hook_file)) {
						include $hook_file;
						if (is_callable($run_hook)) {
							$batch_log=" \nHook Started ".$invoice_id." - ".date("d.m.Y H:i:s");
							$o_main->db->query("INSERT INTO batch_invoicing_log SET created = NOW(), log = ?, batch_invoicing_id = ?", array($batch_log, $notProcessedInvoice["batch_id"]));

							$hook_result = $run_hook($hook_params);
							$sync_invoice_attachment = $hook_result['sync_invoice_attachment'];
							$batch_log=" \nHook Result ".$notProcessedInvoice['id']." ".json_encode($hook_result)." - ".date("d.m.Y H:i:s");
							$o_main->db->query("INSERT INTO batch_invoicing_log SET created = NOW(), log = ?, batch_invoicing_id = ?", array($batch_log, $notProcessedInvoice["batch_id"]));


							$s_sql = "SELECT * FROM invoice WHERE id = ?";
							$o_query = $o_main->db->query($s_sql, array($notProcessedInvoice['id']));
							$checkSync = $o_query ? $o_query->row_array() : array();

							if($checkSync['sync_status'] != 2) {
								$errors[0][] = array(
									'orderId' => 0,
									'invoice' => $checkSync,
									'errorMsg' => $notProcessedInvoice['id']." ".$formText_IntegrationSyncingFailed
								);
							}
							unset($run_hook);
							if ($batchinvoicing_accountconfig['activate_overwrite_invoice_number'] == 2) {
								if(isset($hook_result['invoiceNumber']) && $hook_result['invoiceNumber'] > 0) {
									$newInvoiceNrOnInvoice = $hook_result['invoiceNumber'];
								}
								if($newInvoiceNrOnInvoice > 0){
									if(isset($hook_result['kidNumber']) && $hook_result['kidNumber'] > 0) {
										$kidnumber = $hook_result['kidNumber'];
									} else {
										if(intval($batchinvoicing_accountconfig['activate_not_update_kid_number']) == 0)  {
											$kidnumber = generate_kidnumber($v_settings, $customerIdToDisplay, $newInvoiceNrOnInvoice, 0);
										} else {
											$s_sql = "SELECT * FROM invoice WHERE id = ?";
											$o_query = $o_main->db->query($s_sql, array($invoice_id));
											$updated_invoice = $o_query ? $o_query->row_array() : array();
											$kidnumber = $updated_invoice['kidNumber'];
										}
									}
									// Update kid number
									$o_main->db->query("UPDATE invoice SET kidNumber = ?, external_invoice_nr = ? WHERE id = ?", array($kidnumber, $newInvoiceNrOnInvoice, $invoice_id));

								}
							}
							$batch_log=" \nHook Finished ".$invoice_id." - ".date("d.m.Y H:i:s");
							$o_main->db->query("INSERT INTO batch_invoicing_log SET created = NOW(), log = ?, batch_invoicing_id = ?", array($batch_log, $notProcessedInvoice["batch_id"]));

						}
					}
				}

				// get updated invoice entry to check external id
				$s_sql = "SELECT * FROM invoice WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($invoice_id));
				$updated_invoice = $o_query ? $o_query->row_array() : array();

				if(intval($updated_invoice['external_invoice_nr']) == 0) {
					$errors[0][] = array(
						'orderId' => 0,
						'invoice' => $updated_invoice,
						'errorMsg' => $updated_invoice['id']." ".$formText_InvoiceMissingExternalInvoiceNr
					);
					continue;
				}

				if(intval($batchinvoicing_accountconfig['activate_not_update_kid_number']) == 1){
					$kidnumber = $updated_invoice['kidNumber'];
					if(intval($kidnumber) == 0) {
						$errors[0][] = array(
							'orderId' => 0,
							'invoice' => $updated_invoice,
							'errorMsg' => $updated_invoice['id']." ".$formText_InvoiceMissingKidNumber
						);
						continue;
					}
				}
				/// ehf
				file_put_contents(__DIR__ . '/../../../uploads/test-'.$currentCurrencyDisplay.'.txt', $html);

				$file = "invoice_".$newInvoiceNrOnInvoice;
				if ($activateMultiOwnerCompanies)
				{
					$file = "invoice_oc".$ownercompany_id."_".$newInvoiceNrOnInvoice;
				}

				$filepath = __DIR__."/../../../uploads/protected/invoices_ehf/";

				$s_ehf_file = $file;
				$s_ehf_file_path = $filepath.$s_ehf_file.'.xml';

				if ($batchinvoicing_accountconfig['activate_overwrite_invoice_number'] == 2) {
					$html = $html1 . $newInvoiceNrOnInvoice . $html2;
					if($v_settings['kidOnInvoice'] > 0 || $batchinvoicing_accountconfig['activate_not_update_kid_number'])
						$html .=  $html3 .  $kidnumber .$html4;
					$html .=  $html5;
					$html = html_entity_decode($html);
					$html = $html;

					$file .= ".pdf";
					$filepath = __DIR__."/../../../uploads/protected/invoices/";
					$s_pdf_file_path = $filepath.$file;
					if(!file_exists($filepath))
					{
						mkdir($filepath, 0777,true);
					}
					chmod($filepath, 0777);
					$batch_log=" \nPDF starting ".$invoice_id." - ".date("d.m.Y H:i:s").$v_accountinfo['accountname']." ".json_encode($html);
					$o_main->db->query("INSERT INTO batch_invoicing_log SET created = NOW(), log = ?, batch_invoicing_id = ?", array($batch_log, $notProcessedInvoice["batch_id"]));

					create_pdf($filepath, $file, $files_attached_pdf, $newInvoiceNrOnInvoice, $invoicelogo, $v_settings, $v_accountinfo['accountname'], $html, trim($v_settings['choose_custom_invoice_template']));

					$batch_log=" \nPDF created ".$invoice_id." - ".date("d.m.Y H:i:s");
					$o_main->db->query("INSERT INTO batch_invoicing_log SET created = NOW(), log = ?, batch_invoicing_id = ?", array($batch_log, $notProcessedInvoice["batch_id"]));

					if(!file_exists(__DIR__."/../../../uploads/protected/invoices/".$file)) {
						$errors[0][] = array(
							'orderId' => 0,
							'invoice' => $updated_invoice,
							'errorMsg' => $invoice_id['id']." ".$formText_ErrorCreatingPdf
						);
						continue;
					}
					$o_query = $o_main->db->query("UPDATE invoice SET invoiceFile = ? WHERE id = ?", array("uploads/protected/invoices/".$file, $invoice_id));
				} else {
					$s_pdf_file_path = __DIR__."/../../../".$notProcessedInvoice['invoiceFile'];
				}

				$o_query = $o_main->db->query("UPDATE invoice SET not_processed = 0, kidNumber = ? WHERE id = ?", array($kidnumber, $invoice_id));


				if($sync_invoice_attachment){
					$hook_params = array(
						'ownercompany_id'=> $ownercompany_id,
						'invoice_id' => $invoice_id,
						'sync_invoice_attachment' => 1
					);

					$hook_file = __DIR__ . '/../../../' . $batchinvoicing_accountconfig['path_syncing_of_customer_and_invoice'];
					if (file_exists($hook_file)) {
						include $hook_file;
						if (is_callable($run_hook)) {
							$batch_log=" \nHook Started ".$invoice_id." - ".date("d.m.Y H:i:s");
							$o_main->db->query("INSERT INTO batch_invoicing_log SET created = NOW(), log = ?, batch_invoicing_id = ?", array($batch_log, $notProcessedInvoice["batch_id"]));

							$hook_result = $run_hook($hook_params);
							$hook_result['base64EncodedData'] = '';
							$batch_log=" \nHook Result ".$notProcessedInvoice['id']." ".json_encode($hook_result)." - ".date("d.m.Y H:i:s");
							$o_main->db->query("INSERT INTO batch_invoicing_log SET created = NOW(), log = ?, batch_invoicing_id = ?", array($batch_log, $notProcessedInvoice["batch_id"]));

							unset($run_hook);
							$batch_log=" \nHook Finished ".$invoice_id." - ".date("d.m.Y H:i:s");
							$o_main->db->query("INSERT INTO batch_invoicing_log SET created = NOW(), log = ?, batch_invoicing_id = ?", array($batch_log, $notProcessedInvoice["batch_id"]));

						}
					}
				}

				if(!$updated_invoice['do_not_send_invoice']){
					$customerInvoiceBy = $v_customer["invoiceBy"];
					$customerInvoiceEmail = $v_customer["invoiceEmail"];

					if($updated_invoice['invoiceBy'] != ""){
						$customerInvoiceBy = $updated_invoice['invoiceBy'];
					}
					if($updated_invoice['invoiceEmail'] != ""){
						$customerInvoiceEmail = $updated_invoice['invoiceEmail'];
					}

					// *************** EHF CREATION START ******
					if($customerInvoiceBy == 2)
					{

						$batch_log=" \nEHF start - ".$notProcessedInvoice['id']." ".date("d.m.Y H:i:s");
						$o_main->db->query("INSERT INTO batch_invoicing_log SET created = NOW(), log = ?, batch_invoicing_id = ?", array($batch_log, $notProcessedInvoice["batch_id"]));

						$s_custom_error = '';
						$v_ehf_data['invoice_nr'] = $newInvoiceNrOnInvoice;
						$v_ehf_data['invoice_issue_date'] = date("Y-m-d", strtotime($dateValShow));
						// 380 - Commercial invoice
						// 393 - Factored invoice
						// 384 - Corrected invoice
						// ftp://ftp.cen.eu/public/CWAs/BII2/CWA16558/CWA16558-Annex-G-BII-CodeLists-V2_0_4.pdf - page 15
						$v_ehf_data['invoice_type_code'] = 380;
						$v_ehf_data['invoice_note'] = ''; // optional
						$v_ehf_data['tax_point_date'] = ''; // optional - Y-m-d
						$v_ehf_data['currency_code'] = 'NOK';
						$v_ehf_data['accounting_cost'] = ''; // optional
						$v_ehf_data['period_start'] = ''; // optional - Y-m-d
						$v_ehf_data['period_end'] = ''; // optional - Y-m-d
						$v_ehf_data['order_reference'] = ''; // recommended - filled below

						$v_ehf_data['contract_document_reference'] = ''; // recommended - Contract321
						//$v_data['contract_document_type_code'] = '2'; // optional
						//$v_data['contract_document_type'] = 'Framework agreement'; // optional

						$v_ehf_data['supplier_org_nr'] = preg_replace('#[^0-9]+#', '', $v_settings['companyorgnr']);
						$v_ehf_data['supplier_identification'] = ''; // optional
						$v_ehf_data['supplier_name'] = $v_settings['companyname'];
						$v_ehf_data['supplier_street'] = $v_settings['companypostalbox']; // optional
						$v_ehf_data['supplier_city'] = $v_settings['companypostalplace'];
						$v_ehf_data['supplier_postal_code'] = $v_settings['companyzipcode'];
						$v_ehf_data['supplier_country'] = strtoupper(''!=$v_settings['country_code']?$v_settings['country_code']:'NO');
						$v_ehf_data['supplier_org_nr_vat'] = $v_ehf_data['supplier_country'].$v_ehf_data['supplier_org_nr'].(('NO'==strtoupper($v_ehf_data['supplier_country']) && stripos($v_ehf_data['supplier_org_nr'], 'MVA') === FALSE) ? 'MVA' : ''); // optional (mandatory when zerro VAT) //TODO: NO + MVA
						$v_ehf_data['supplier_contact_id'] = (''!=trim($contantPersonLine)?$contantPersonLine:$v_settings['id']); // recommended
						//$v_ehf_data['supplier_contact_name'] = ''; // recommended
						$v_ehf_data['supplier_contact_phone'] = $v_settings['companyphone']; // recommended
						//$v_ehf_data['supplier_contact_fax'] = ''; // recommended
						$v_ehf_data['supplier_contact_email'] = $v_settings['companyEmail']; // recommended

						$o_contactperson = $o_main->db->query("SELECT * FROM contactperson WHERE customerId = ? ORDER BY mainContact DESC, id ASC", array($v_customer['id']));
						$v_contactperson = $o_contactperson ? $o_contactperson->row_array() : array();
						$v_ehf_data['customer_org_nr'] = preg_replace('#[^0-9]+#', '', $v_customer['publicRegisterId']);
						$v_ehf_data['customer_identification'] = $customerIdToDisplay; // optional
						$v_ehf_data['customer_name'] = trim($v_customer['name']." ".$v_customer['middlename']." ".$v_customer['lastname']);
						$v_ehf_data['customer_street'] = $v_customer[$s_cust_addr_prefix.'Street']; // optional
						$v_ehf_data['customer_street_additional'] = $v_customer[$s_cust_addr_prefix.'Street2']; // optional
						$v_ehf_data['customer_city'] = $v_customer[$s_cust_addr_prefix.'City'];
						$v_ehf_data['customer_postal_code'] = $v_customer[$s_cust_addr_prefix.'PostalNumber'];
						//$v_ehf_data['customer_country_subentity'] = ''; // optional
						$v_ehf_data['customer_country'] = strtoupper(''!=$v_customer['country_code']?$v_customer['country_code']:'NO');
						// Mandatory ONLY FOR COMPANIES (not for consumers) start
						$v_ehf_data['customer_org_nr_vat'] = $v_ehf_data['customer_country'].$v_ehf_data['customer_org_nr'].(('NO'==strtoupper($v_ehf_data['customer_country']) && stripos($v_ehf_data['customer_org_nr'], 'MVA') === FALSE) ? 'MVA' : '');
						$v_ehf_data['customer_legal_name'] = trim($v_customer['name']." ".$v_customer['middlename']." ".$v_customer['lastname']);
						$v_ehf_data['customer_legal_org_nr'] = preg_replace('#[^0-9]+#', '', $v_customer['publicRegisterId']);
						$v_ehf_data['customer_legal_city'] = $v_customer[$s_cust_addr_prefix.'City']; // optional
						$v_ehf_data['customer_legal_country'] = strtoupper(''!=$v_customer['country_code']?$v_customer['country_code']:'NO'); // optional
						// Mandatory ONLY FOR COMPANIES (not for consumers) end
						$v_ehf_data['customer_contact_id'] = ($v_contactperson['id'] != '' ? $v_contactperson['id'] : $contantPersonLine); // Name or identifier specifying the customers reference (Eg employee number)
						$v_ehf_data['customer_contact_name'] = $contantPersonLine;//trim($v_contactperson['name']." ".$v_contactperson['middlename']." ".$v_contactperson['lastname']); // optional
						$v_ehf_data['customer_contact_phone'] = '';//($v_contactperson['directPhone']!=''?$v_contactperson['directPhone']:$v_contactperson['mobile']); // optional
						$v_ehf_data['customer_contact_fax'] = ''; // optional
						$v_ehf_data['customer_contact_email'] = '';//$v_contactperson['email']; // optional

						//Optional >>
						//$v_ehf_data['payee_identification'] = ''; // 2298740918237
						//$v_ehf_data['payee_name'] = ''; // Ebeneser Scrooge AS
						//$v_ehf_data['payee_company_id'] = ''; //999999999
						// << Optional
						//Optional >>
						//$v_ehf_data['tax_representative_name'] = ''; // Tax handling company AS
						//$v_ehf_data['tax_representative_street'] = '';
						//$v_ehf_data['tax_representative_street_additional'] = ''; // Front door
						//$v_ehf_data['tax_representative_city'] = '';
						//$v_ehf_data['tax_representative_postal_code'] = '';
						//$v_ehf_data['tax_representative_country_subentity'] = '';
						//$v_ehf_data['tax_representative_country'] = 'NO';
						//$v_ehf_data['tax_representative_tax_scheme_company_nr'] = ''; // 999999999MVA
						// << Optional

						$v_ehf_data['payment_means_code'] = '31'; //Code according to UN/CEFACT codelist 4461
						$v_ehf_data['payment_due_date'] = date("Y-m-d", strtotime($dateExpireShow));
						$v_ehf_data['payment_id'] = (0 != $kidnumber ? $kidnumber : ''); //In Norway this element is used for KID number.
						$v_ehf_data['payment_bank_account_type'] = 'BBAN'; //BBAN, IBAN
						$v_ehf_data['payment_bank_account'] = preg_replace('#[^A-Za-z0-9]+#', '', $bankAccountData['companyaccount']); // $bankAccountData['companyiban']
						$v_ehf_data['payment_financial_institution_branch_id'] = 'BIC'; // Dependent
						$v_ehf_data['payment_financial_institution_bic'] = $bankAccountData['companyswift'];  // Dependent
						//Optional >>
						$v_ehf_data['payment_terms'] = ''; //"2 % discount if paid within 2 days", "Penalty percentage 10% from due date";
						//Optional <<
						//Optional >>
						$v_ehf_data['allowance_charge'] = array();
						$v_item = array();
						$v_item['type'] = 'true'; //true = Charge, false = Allowance
						$v_item['reason_code'] = ''; // 94, Use codelist AllowanceChargeReasonCode, UN/ECE 4465, Version D08B
						$v_item['reason'] = ''; //packing charges
						$v_item['amount'] = ''; //100
						$v_item['tax_category'] = ''; //S
						$v_item['tax_percent'] = ''; //25
						//$v_ehf_data['allowance_charge'][] = $v_item;
						//Optional <<

						$v_ehf_data['tax_amount'] = round(floatval($ordersArray['totals']['totalVat']), $decimalPlaces);
						$v_ehf_data['tax_subtotal'] = array();
						$v_taxes = array();
						foreach($ordersArray['list'] as $order)
						{
							$o_query = $o_main->db->query("SELECT * FROM vatcode WHERE vatCode = '".$o_main->db->escape_str($order['vatCode'])."'");
							$v_vat = $o_query ? $o_query->row_array() : array();
							if(trim($v_vat['ehf']) == '') $s_custom_error = $formText_MissingEhfConfigurationForVat_Output.': '.$v_vat['name'];
							if(array_key_exists($order['vatPercentRate'], $v_taxes))
							{
								$v_taxes[$order['vatPercentRate']]['taxable_amount'] += round(floatval($order['priceTotal']), $decimalPlaces);
								$v_taxes[$order['vatPercentRate']]['tax_amount'] += round(floatval($order['vat']), $decimalPlaces);
							} else {
								$v_taxes[$order['vatPercentRate']]['taxable_amount'] = round(floatval($order['priceTotal']), $decimalPlaces);
								$v_taxes[$order['vatPercentRate']]['tax_amount'] = round(floatval($order['vat']), $decimalPlaces);
								$v_taxes[$order['vatPercentRate']]['tax_percent'] = floatval($order['vatPercentRate']);
								$v_taxes[$order['vatPercentRate']]['tax_category'] = $v_vat['ehf'];
								if(in_array($v_vat['ehf'], array('AE', 'E', 'G', 'K'))) $v_taxes[$order['vatPercentRate']]['reason'] = $v_vat['name'];
							}
							$v_ehf_data['order_reference'] = (!empty($order['reference']) ? $order['reference'] : ''); // recommended
							// Delivery - Recommended >>
							$v_ehf_data['delivery_date'] = date("Y-m-d", strtotime((!empty($order['delivery_date']) && $order['delivery_date'] != '0000-00-00') ? $order['delivery_date'] : $dateValShow)); //2013-06-15
							//$v_ehf_data['delivery_location'] = ''; //6754238987643
							$v_ehf_data['delivery_street'] = (!empty($order['delivery_address_line_1']) ? $order['delivery_address_line_1'] : '');
							$v_ehf_data['delivery_street_additional'] = (!empty($order['delivery_address_line_2']) ? $order['delivery_address_line_2'] : '');
							$v_ehf_data['delivery_city'] = (!empty($order['delivery_address_city']) ? $order['delivery_address_city'] : '');
							$v_ehf_data['delivery_postal_code'] = (!empty($order['delivery_address_postal_code']) ? $order['delivery_address_postal_code'] : '');
							//$v_ehf_data['delivery_country_subentity'] = 'RegionD';
							$v_ehf_data['delivery_country'] = (!empty($order['delivery_address_country']) ? strtoupper($order['delivery_address_country']) : '');
							// << Recommended
						}
						foreach($v_taxes as $v_tax)
						{
							$v_item = array();
							$v_item['taxable_amount'] = round($v_tax['taxable_amount'], $decimalPlaces);
							$v_item['tax_amount'] = round($v_tax['tax_amount'], $decimalPlaces);
							$v_item['tax_percent'] = $v_tax['tax_percent'];
							$v_item['tax_category'] = $v_tax['tax_category'];
							// Depend
							if(isset($v_tax['reason']))
							$v_item['tax_exemption_reason'] = $v_tax['reason']; //Exempt New Means of Transport = Mandatory if VAT category = E
							$v_ehf_data['tax_subtotal'][] = $v_item;
						}

						$v_ehf_data['legal_monetary_line_extension'] = round(floatval($ordersArray['totals']['totalSum']), $decimalPlaces);
						$v_ehf_data['legal_monetary_tax_exclusive'] = round(floatval($ordersArray['totals']['totalSum']), $decimalPlaces);
						$v_ehf_data['legal_monetary_tax_inclusive'] = round(floatval($ordersArray['totals']['total']), $decimalPlaces);
						//$v_ehf_data['legal_monetary_allowance_total'] = ''; // optional
						//$v_ehf_data['legal_monetary_charge_total'] = ''; // optional
						//$v_ehf_data['legal_monetary_prepaid'] = ''; // optional
						//$v_ehf_data['legal_monetary_payable_rounding'] = ''; // optional
						$v_ehf_data['legal_monetary_payable_amount'] = round(floatval($ordersArray['totals']['total']), $decimalPlaces);

						$l_orderline_counter = 1;
						$v_ehf_data['invoice_line'] = array();
						foreach($ordersArray['list'] as $order)
						{
							if($ordersArray['subscriptionId']) {
								$s_sql = "SELECT * FROM subscriptionline WHERE subscriptionline.id = ".$order['orderId']."";
								$o_query = $o_main->db->query($s_sql);
								$v_order = $o_query ? $o_query->row_array() : array();
							} else {
								$s_sql = "SELECT * FROM orders LEFT OUTER JOIN subscriptionmulti ON subscriptionmulti.id = orders.invoiceDateSettingFromSubscription WHERE orders.id = ".$order['orderId']." ORDER BY orders.invoiceDateSettingFromSubscription ASC";
								$o_query = $o_main->db->query($s_sql);
								$v_order = $o_query ? $o_query->row_array() : array();
							}

							$o_query = $o_main->db->query("SELECT * FROM vatcode WHERE vatCode = '".$o_main->db->escape_str($order['vatCode'])."'");
							$v_vat = $o_query ? $o_query->row_array() : array();

							$o_query = $o_main->db->query("SELECT * FROM article WHERE id = '".$o_main->db->escape_str($v_order['articleNumber'])."'");
							$v_article = $o_query ? $o_query->row_array() : array();

							$v_item = array();
							$v_item['id'] = $order['orderId'];
							//$v_item['note'] = ''; // optional
							$v_item['quantity'] = floatval($order['amount']);
							$v_item['amount'] = floatval($order['priceTotal']);
							$v_item['accounting_cost'] = ($v_article['accounting_reference_for_invoice_receiver'] != '' ? $v_article['accounting_reference_for_invoice_receiver'] : 'NO_REFERENCE'); // recommended
							if($v_order['dateFrom'] != '' && $v_order['dateFrom'] != '0000-00-00' && $v_order['dateTo'] != '' && $v_order['dateTo'] != '0000-00-00')
							{
							$v_item['period_from'] = date("Y-m-d", strtotime($v_order['dateFrom']));
							$v_item['period_to'] = date("Y-m-d", strtotime($v_order['dateTo']));
							}
							$v_item['reference'] = $order['orderId'].'#'.$l_orderline_counter; // recommended
							//Optional >>
							$v_item['delivery_date'] = date("Y-m-d", strtotime($dateValShow));
							//$v_item['delivery_location_gln'] = '6754238987643';
							//$v_item['delivery_street'] = (!empty($order['delivery_address_line_1']) ? $order['delivery_address_line_1'] : '');
							//$v_item['delivery_street_additional'] = (!empty($order['delivery_address_line_2']) ? $order['delivery_address_line_2'] : '');
							$v_item['delivery_city'] = $v_settings['companypostalplace'];
							$v_item['delivery_postal_code'] = $v_settings['companyzipcode'];
							//$v_item['delivery_country_subentity'] = 'RegionD';
							$v_item['delivery_country'] = 'NO';//(($v_settings['companyCountry'] == '' || strtolower($v_settings['companyCountry']) == 'norge') ? 'NO' : $v_settings['companyCountry']);
							//Optional <<
							//Optional >>
							if(floatval($v_order['discountPercent'])>0)
							{
								$v_item['allowance_charge'] = array();
								$v_sub_item = array();
								$v_sub_item['type'] = 'false';
								$v_sub_item['reason'] = round(floatval($v_order['discountPercent']), $decimalPlaces).'% Rabatt';
								$v_sub_item['amount'] = round(floatval($v_order['pricePerPiece']) * floatval($v_order['amount']) * floatval($v_order['discountPercent']/100), $decimalPlaces);
								$v_item['allowance_charge'][] = $v_sub_item;
							}
							//$v_item['allowance_charge'] = array();
							//$v_sub_item = array();
							//$v_sub_item['type'] = 'false';
							//$v_sub_item['reason'] = 'Damage';
							//$v_sub_item['amount'] = '12';
							//$v_item['allowance_charge'][] = $v_sub_item;
							//$v_sub_item = array();
							//$v_sub_item['type'] = 'true';
							//$v_sub_item['reason'] = 'Testing';
							//$v_sub_item['amount'] = '12';
							//$v_item['allowance_charge'][] = $v_sub_item;
							//Optional <<
							//$v_item['description'] = array(array('text'=>proc_rem_style($order['articleName']))); // optional
							$v_item['name'] = proc_rem_style($order['articleName']);
							$v_item['sellers_item_identification'] = $v_order['articleNumber']; // optional
							//$v_item['standard_item_identification'] = '1234567890124'; // optional
							//$v_item['origin_country'] = 'DE'; // optional
							//$v_item['commodity_classification'] = array(array('id'=>'MP', 'code'=>'12344321'), array('id'=>'STI', 'code'=>'65434568')); // optional
							$v_item['classified_tax_category'] = $v_vat['ehf'];
							$v_item['classified_tax_percent'] = floatval($order['vatPercentRate']); // optional
							$v_item['addition_item_property'] = array(); // optional
							//$v_sub_item = array();
							//$v_sub_item['name'] = 'Color';
							//$v_sub_item['value'] = 'Black';
							//$v_item['addition_item_property'][] = $v_item['id'];
							//Optional >>
							//$v_item['manufacturer_party_name'] = 'Company name ASA';
							//$v_item['manufacturer_party_org_nr'] = '904312347';
							//Optional <<
							$v_item['price'] = round(floatval($order['pricePerPiece']), $decimalPlaces);
							//$v_item['base_quantity'] = 1; // optional
							if(floatval($v_order['discountPercent'])>0)
							{
								$v_item['price_allowance_charge'] = array(); // optional
								$v_sub_item = array();
								$v_sub_item['type'] = 'false';
								$v_sub_item['multiplier_factor'] = round(floatval($v_order['discountPercent']) / 100, $decimalPlaces*2);
								$v_sub_item['reason'] = round(floatval($v_order['discountPercent']), $decimalPlaces).'% Rabatt';
								$v_sub_item['amount'] = round(floatval($v_order['pricePerPiece']) * floatval($v_order['amount']) * floatval($v_order['discountPercent']/100), $decimalPlaces);
								$v_item['price_allowance_charge'][] = $v_sub_item;
							}
							//$v_item['price_allowance_charge'] = array(); // optional
							//$v_sub_item = array();
							//$v_sub_item['type'] = 'false';
							//$v_sub_item['reason'] = 'Contract';
							//$v_sub_item['multiplier_factor'] = '0.15';
							//$v_sub_item['amount'] = '225';
							//$v_sub_item['base_amount'] = '1500';
							//$v_item['price_allowance_charge'][] = $v_sub_item;
							$v_ehf_data['invoice_line'][] = $v_item;

							$l_orderline_counter++;
						}

						$l_counter = 1;
						$v_ehf_data['additional_document_reference'] = array();
						foreach($files_attached as $file_to_attach)
						{
							if(!is_file(__DIR__."/../../../".rawurldecode($file_to_attach[1][0]))) continue;
							if(filesize(__DIR__."/../../../".rawurldecode($file_to_attach[1][0])) > 5242880) continue; // Skip if larger than 5MB
							$s_mime_type = mime_content_type(__DIR__."/../../../".rawurldecode($file_to_attach[1][0]));
							if('application/pdf' == $s_mime_type)
							{
								$v_info = pathinfo(__DIR__."/../../../".rawurldecode($file_to_attach[1][0]));
								$v_item = array();
								$v_item['id'] = $v_info['basename'];
								$v_item['document_type'] = 'File'.$l_counter;
								$v_item['attachment_mime'] = $s_mime_type;
								$v_item['attachment_binary'] = base64_encode(file_get_contents(__DIR__."/../../../".rawurldecode($file_to_attach[1][0])));
								//$v_item['attachment_uri'] = 'http://www.suppliersite.eu/sheet001.html';
								$v_ehf_data['additional_document_reference'][] = $v_item;
								$l_counter++;
							}
						}

						if($s_custom_error == '')
						{
							$filepath = __DIR__."/../../../uploads/protected/invoices_ehf/";
							if(!file_exists($filepath))
							{
								mkdir($filepath, 0777,true);
							}
							chmod($filepath, 0777);

							$s_ehf_xml = create_ehf_invoice($v_ehf_data);
							$s_ehf_file_path_check = $filepath.'check-'.date("YmdHis").'-'.$newInvoiceNrOnInvoice.'.xml';
							file_put_contents($s_ehf_file_path_check, $s_ehf_xml);

							$ch = curl_init();
							curl_setopt($ch, CURLOPT_URL, 'https://ap.getynet.com/validator/');
							curl_setopt($ch, CURLOPT_HEADER, 0);
							curl_setopt($ch, CURLOPT_VERBOSE, 0);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
							curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
							curl_setopt($ch, CURLOPT_COOKIEJAR, '/var/www/tmp/cookie-'.date("YmdHis").'-'.$newInvoiceNrOnInvoice);
							curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
                            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 600); //timeout in seconds
                            curl_setopt($ch, CURLOPT_TIMEOUT, 600); //timeout in seconds
							$s_response = curl_exec($ch);
							$dom = new DomDocument();
							$dom->loadHTML($s_response);
							$tokens = $dom->getElementsByTagName("input");
							for ($i = 0; $i < $tokens->length; $i++)
							{
								$meta = $tokens->item($i);
								if($meta->getAttribute('name') == '_csrf')
								$s_token = $meta->getAttribute('value');
							}
							curl_setopt($ch, CURLOPT_HEADER, 0);
							curl_setopt($ch, CURLOPT_VERBOSE, 0);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
							curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
							curl_setopt($ch, CURLOPT_COOKIEFILE, '/var/www/tmp/cookie-'.date("YmdHis").'-'.$newInvoiceNrOnInvoice);
							curl_setopt($ch, CURLOPT_POST, TRUE);
							curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
                            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 600); //timeout in seconds
                            curl_setopt($ch, CURLOPT_TIMEOUT, 600); //timeout in seconds
							curl_setopt($ch, CURLOPT_URL, 'https://ap.getynet.com/validator/?_csrf='.$s_token);
							$v_post = array(
								'file' => new CurlFile($s_ehf_file_path_check, mime_content_type($s_ehf_file_path_check), basename($s_ehf_file_path_check)),
								'_csrf' => $s_token,
							);
							curl_setopt($ch, CURLOPT_POSTFIELDS, $v_post);
							$s_response = curl_exec($ch);

							$s_status = '';
							require_once __DIR__ . '/../procedure_create_invoices/scripts/CREATE_INVOICE/html5/Parser.php';
							$dom = HTML5_Parser::parse($s_response);
							$divs = $dom->getElementsByTagName('div');
							$b_check = FALSE;
							foreach($divs as $div)
							{
								if($b_check && strpos($div->getAttribute('class'), 'status status-') !== FALSE)
								{
									$s_status = strtolower($div->getAttribute('class'));
									break;
								}
								if($div->getAttribute('class') == 'report')
								{
									$b_check = TRUE;
								}
							}
							$b_valid_file = (strpos($s_status, 'status-warning') !== FALSE || strpos($s_status, 'status-ok') !== FALSE);

							$v_check = validate_ehf_invoice($v_ehf_data);

							if(sizeof($v_check)>0 || !$b_valid_file)
							{
								rename($s_ehf_file_path_check, substr($s_ehf_file_path_check, 0, -4).'.error.xml');
								foreach($ordersArray['list'] as $order)
								{
									if($b_valid_file)
									{
										foreach($v_check as $s_error)
										{
											$errors[$order['orderId']][] = array(
												'orderId' => $order['orderId'],
												'invoice' => $updated_invoice,
												'errorMsg' => $s_error//$formText_EhfInvoiceFileValidationFailed_Output
											);
										}
									} else {
										$errors[$order['orderId']][] = array(
											'orderId' => $order['orderId'],
											'invoice' => $updated_invoice,
											'errorMsg' => $formText_EhfInvoiceFileValidationFailed_Output.' (<a href="'.curl_getinfo($ch, CURLINFO_EFFECTIVE_URL).'" target="_blank">'.$formText_MoreInfo_Output.'</a>)'
										);
									}

								}
								$o_main->db->query("INSERT INTO invoice_send_log SET created = NOW(), invoice_id = '".$o_main->db->escape_str($invoice_id)."', send_type = 3, send_status = 2");

								continue;
							} else {
								unlink($s_ehf_file_path_check);
							}
						} else {
							foreach($ordersArray['list'] as $order)
							{
								$errors[$order['orderId']][] = array(
									'orderId' => $order['orderId'],
									'invoice' => $updated_invoice,
									'errorMsg' => $s_custom_error
								);
							}
							$o_main->db->query("INSERT INTO invoice_send_log SET created = NOW(), invoice_id = '".$o_main->db->escape_str($invoice_id)."', send_type = 3, send_status = 2");

							continue;
						}

						$batch_log=" \nEHF end - ".$notProcessedInvoice['id']." ".date("d.m.Y H:i:s");
						$o_main->db->query("INSERT INTO batch_invoicing_log SET created = NOW(), log = ?, batch_invoicing_id = ?", array($batch_log, $notProcessedInvoice["batch_id"]));
					}
					// ************   EHF CREATION END ***********

					if($customerInvoiceBy == 2)
					{
						$v_item = array();
						$v_item['id'] = $newInvoiceNrOnInvoice.'.pdf';
						$v_item['document_type'] = 'Commercial invoice'; //If, however, the "pdf-version" is supplied as an attachment, the element "DocumentType" must specify "Commercial invoice" for an invoice and "Credit note" for a creditnote.
						$v_item['attachment_mime'] = 'application/pdf';
						$v_item['attachment_binary'] = base64_encode(file_get_contents($s_pdf_file_path));
						//$v_item['attachment_uri'] = 'http://www.suppliersite.eu/sheet001.html';
						$v_ehf_data['additional_document_reference'][] = $v_item;
						$s_ehf_xml = create_ehf_invoice($v_ehf_data);
						file_put_contents($s_ehf_file_path, $s_ehf_xml);
					}

					$b_send_by_email = false;
					if($customerInvoiceBy == 1)
					{
						$b_send_by_email = true;
						$s_email_subject = $v_settings['invoiceSubjectEmail']. " ".$newInvoiceNrOnInvoice;
						$s_email_body = nl2br($v_settings['invoiceTextEmail']);

						$s_sql = "select * from sys_emailserverconfig order by default_server desc";
						$o_query = $o_main->db->query($s_sql);
						if($o_query && $o_query->num_rows()>0){
							$v_email_server_config = $o_query->row_array();
						}


						$customerInvoiceEmail = str_replace(array(";", chr(0xC2).chr(0xA0)), array(",", ''), $customerInvoiceEmail);
						$emailsArray = explode(",", $customerInvoiceEmail);
						$emailsArray = array_map('trim', $emailsArray);

						$s_sql = "INSERT INTO sys_emailsend (id, created, createdBy, `type`, send_on, sender, sender_email, subscriberlist_id, unsubscriberlist_id, content_id, content_table, content_module_id, sending_limit, subject, text) VALUES (NULL, NOW(), ?, 2, NOW(), '', ?, 0, 0, ?, 'invoice', '', 0, ?, ?);";
						$o_main->db->query($s_sql, array($_COOKIE['username'], $v_settings['invoiceFromEmail'], $invoice_id, $s_email_subject, $s_email_body));
						$l_emailsend_id = $o_main->db->insert_id();

						$l_invoice_sent = 0;
						foreach($emailsArray as $invoiceEmail)
						{
							// Trim U+00A0 (0xc2 0xa0) NO-BREAK SPACE
							//$invoiceEmail = trim($invoiceEmail,chr(0xC2).chr(0xA0));
							// Trim rest spaces and new lines
							//$invoiceEmail = trim($invoiceEmail);
							if(filter_var($invoiceEmail, FILTER_VALIDATE_EMAIL))
							{
								$mail = new PHPMailer;
								$mail->CharSet	= 'UTF-8';
								$mail->IsSMTP(true);
								$mail->isHTML(true);
								if($v_email_server_config['host'] != "")
								{
									$mail->Host	= $v_email_server_config['host'];
									if($v_email_server_config['port'] != "") $mail->Port = $v_email_server_config['port'];

									if($v_email_server_config['username'] != "" and $v_email_server_config['password'] != "")
									{
										$mail->SMTPAuth	= true;
										$mail->Username	= $v_email_server_config['username'];
										$mail->Password	= $v_email_server_config['password'];

									}
								} else {
									$mail->Host = "mail.dcode.no";
								}
								$mail->From		= $v_settings['invoiceFromEmail'];
								$mail->FromName	= $v_settings['name'];
								$mail->Subject	= $s_email_subject;
								$mail->Body		= $s_email_body;
								$mail->AltBody	= strip_tags($s_email_body);
								$mail->AddAddress($invoiceEmail, $v_customer['name']." ".$v_customer['middlename']." ".$v_customer['lastname']);
								$mail->AddAttachment($s_pdf_file_path);
								$s_sql = "INSERT INTO sys_emailsendto (id, emailsend_id, receiver, receiver_email, `status`, status_message, perform_time, perform_count) VALUES (NULL, ?, '', ?, 1, '', NOW(), 1);";
								$o_main->db->query($s_sql, array($l_emailsend_id, $invoiceEmail));
								$l_emailsendto_id =$o_main->db->insert_id();
								foreach($files_attached as $file_to_attach) {
									if(file_exists(__DIR__."/../../../".rawurldecode($file_to_attach[1][0]))){
										$mail->AddAttachment(__DIR__."/../../../".rawurldecode($file_to_attach[1][0]));
									}
								}
								if($mail->Send())
								{
									$b_successfully_sent_by_email = true;
									$v_proc_variables["lines_sent"]++;
									$l_invoice_sent++;
								} else {
									$s_sql = "UPDATE sys_emailsendto SET status = 2, status_message = ? WHERE id = ?";
									$o_main->db->query($s_sql, array(json_encode($mail), $l_emailsendto_id));

									$mail = new PHPMailer;
									$mail->CharSet	= 'UTF-8';
									$mail->IsSMTP(true);
									$mail->isHTML(true);
									if($v_email_server_config['host'] != "")
									{
										$mail->Host	= $v_email_server_config['host'];
										if($v_email_server_config['port'] != "") $mail->Port = $v_email_server_config['port'];

										if($v_email_server_config['username'] != "" and $v_email_server_config['password'] != "")
										{
											$mail->SMTPAuth	= true;
											$mail->Username	= $v_email_server_config['username'];
											$mail->Password	= $v_email_server_config['password'];

										}
									} else {
										$mail->Host = "mail.dcode.no";
									}
									$mail->From		= "noreply@getynet.com";
									$mail->FromName	= "Getynet.com";
									$mail->Subject	= $formText_NotDelivered_Output.": ".$s_email_subject;
									$mail->Body		= $s_email_body;
									$mail->AddAddress($v_email_server_config['technical_email']);
									$mail->AddAttachment($s_pdf_file_path);
									foreach($files_attached as $file_to_attach) {
										if(file_exists(__DIR__."/../../../".rawurldecode($file_to_attach[1][0]))){
											$mail->AddAttachment(__DIR__."/../../../".rawurldecode($file_to_attach[1][0]));
										}
									}
									$mail->Send();
								}
							} else {
								$errors[0][] = array(
									'orderId' => 0,
									'invoice' => $updated_invoice,
									'errorMsg' => $formText_InvoiceNumber_Output.": ".$newInvoiceNrOnInvoice.". ".$formText_InvalidEmailSpecified_Output." ".$formText_InvoiceHasNotBeSentByEmailToCustomer_Output." ".$v_customer['name']. " ".$v_customer['middlename']." ".$v_customer['lastname']
								);
							}

						}
						if(count($emailsArray) != $l_invoice_sent)
						{
							if(0 == $l_invoice_sent)
							{
								$errors[0][] = array(
									'orderId' => 0,
									'invoice' => $updated_invoice,
									'errorMsg' => $formText_InvoiceNumber_Output.": ".$newInvoiceNrOnInvoice.". ".$formText_InvalidEmailSpecified_Output." ".$formText_InvoiceHasNotBeSentByEmailToCustomer_Output." ".$v_customer['name']. " ".$v_customer['middlename']." ".$v_customer['lastname']
								);
							} else {
								$errors[0][] = array(
									'orderId' => 0,
									'invoice' => $updated_invoice,
									'errorMsg' => $formText_InvoiceNumber_Output.": ".$newInvoiceNrOnInvoice.". ".(count($emailsArray) - $l_invoice_sent) ." ". $formText_of_Output." ".count($emailsArray)." ".$formText_EmailAddressesIsInvalidForCustomer_Output." ".$v_customer['name']. " ".$v_customer['middlename']." ".$v_customer['lastname']
								);
							}
						}

						$o_main->db->query("INSERT INTO invoice_send_log SET created = NOW(), invoice_id = '".$o_main->db->escape_str($invoice_id)."', send_type = 2, send_status = '".$o_main->db->escape_str(count($emailsArray) != $l_invoice_sent ? 2 : 1)."'");
					}
					// There was batch_id previously
					$sentByEmailText = '';
					if($b_send_by_email) {
						$sentByEmailText = $customerInvoiceEmail;
					}

					$s_ehf_reference = '';
					if($customerInvoiceBy == 2)
					{
						$b_successfully_sent_by_ehf = FALSE;

						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, 'https://ap.getynet.com/validator/');
						curl_setopt($ch, CURLOPT_HEADER, 0);
						curl_setopt($ch, CURLOPT_VERBOSE, 0);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
						curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
						curl_setopt($ch, CURLOPT_COOKIEJAR, '/var/www/tmp/cookie-'.basename($s_ehf_file_path));
						curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 600); //timeout in seconds
                        curl_setopt($ch, CURLOPT_TIMEOUT, 600); //timeout in seconds
						$s_response = curl_exec($ch);
						$dom = new DomDocument();
						$dom->loadHTML($s_response);
						$tokens = $dom->getElementsByTagName("input");
						for ($i = 0; $i < $tokens->length; $i++)
						{
							$meta = $tokens->item($i);
							if($meta->getAttribute('name') == '_csrf')
							$s_token = $meta->getAttribute('value');
						}
						curl_setopt($ch, CURLOPT_HEADER, 0);
						curl_setopt($ch, CURLOPT_VERBOSE, 0);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
						curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
						curl_setopt($ch, CURLOPT_COOKIEFILE, '/var/www/tmp/cookie-'.basename($s_ehf_file_path));
						curl_setopt($ch, CURLOPT_POST, TRUE);
						curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 600); //timeout in seconds
                        curl_setopt($ch, CURLOPT_TIMEOUT, 600); //timeout in seconds
						curl_setopt($ch, CURLOPT_URL, 'https://ap.getynet.com/validator/?_csrf='.$s_token);
						$v_post = array(
							'file' => new CurlFile($s_ehf_file_path, mime_content_type($s_ehf_file_path), basename($s_ehf_file_path)),
							'_csrf' => $s_token,
						);
						curl_setopt($ch, CURLOPT_POSTFIELDS, $v_post);
						$s_response = curl_exec($ch);

						$s_status = '';
						require_once __DIR__ . '/../procedure_create_invoices/scripts/CREATE_INVOICE/html5/Parser.php';
						$dom = HTML5_Parser::parse($s_response);
						$divs = $dom->getElementsByTagName('div');
						$b_check = FALSE;
						foreach($divs as $div)
						{
							if($b_check && strpos($div->getAttribute('class'), 'status status-') !== FALSE)
							{
								$s_status = strtolower($div->getAttribute('class'));
								break;
							}
							if($div->getAttribute('class') == 'report')
							{
								$b_check = TRUE;
							}
						}
						$b_valid_file = (strpos($s_status, 'status-warning') !== FALSE || strpos($s_status, 'status-ok') !== FALSE);

						if($b_valid_file)
						{
							$s_ehf_reference .= '[FILE_VALID]:OK';
							$ch = curl_init();
							curl_setopt($ch, CURLOPT_HEADER, 0);
							curl_setopt($ch, CURLOPT_VERBOSE, 0);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
							curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
							curl_setopt($ch, CURLOPT_POST, TRUE);
							curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
                            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 600); //timeout in seconds
                            curl_setopt($ch, CURLOPT_TIMEOUT, 600); //timeout in seconds
							curl_setopt($ch, CURLOPT_URL, 'https://ap_api.getynet.com/index.php');
							$v_post = array(
								'file' => new CurlFile($s_ehf_file_path, mime_content_type($s_ehf_file_path), basename($s_ehf_file_path)),
								'receiver' => '0192:'.$v_ehf_data['customer_org_nr'],
								'sender' => '0192:'.$v_ehf_data['supplier_org_nr'],
								'token' => 'RySBvCv3i9u6tP4mEd177X2gspGP6Rw0E512874043zDfUyHDsNF70gZvJ2R9s6idmGxk29amaRRR1R4Hbvqy93LJzPCz9oy',
								'password' => '_9^JAj|7_atz~-Y.BndXBguO9_jr0^z~~09m-*:4SXlj|!KZ6Xd.hnJe3WM75H9Vy=;ZIcrab-84WLKN+4Kdz~+xM5U%ePIY'
							);

							curl_setopt($ch, CURLOPT_POSTFIELDS, $v_post);
							$s_response = curl_exec($ch);

							$v_response = json_decode($s_response, TRUE);
							if(isset($v_response['status']) && $v_response['status'] == 1)
							{
								$s_ehf_reference = '[REFERENCE]:'.$v_response['reference'];
								$b_successfully_sent_by_ehf = TRUE;
							} else {
								$s_ehf_reference .= '[AP_ERROR]:'.$s_response;
							}
						} else {
							$s_ehf_reference .= '[FILE_VALID]:FAIL';
						}

						$o_main->db->query("INSERT INTO invoice_send_log SET created = NOW(), invoice_id = '".$o_main->db->escape_str($invoice_id)."', send_type = 3, send_status = '".$o_main->db->escape_str($b_successfully_sent_by_ehf ? 1 : 2)."'");
					}

					$o_query = $o_main->db->query("UPDATE invoice SET sentByEmail = ?, ehf_reference = '".$o_main->db->escape_str($s_ehf_reference)."', ehf_invoice_file = '".$o_main->db->escape_str(($customerInvoiceBy == 2 ? "uploads/protected/invoices_ehf/".$s_ehf_file.".xml" : ''))."' WHERE id = ?", array($sentByEmailText, $invoice_id));
					// mysql_query("UPDATE invoice SET invoiceFile = 'uploads/protected/invoices/".$file."', kidNumber = '".$kidnumber."', sentByEmail = '".$v_customer["invoiceEmail"]."' WHERE id = '$invoice_id';");

				}
			} else {
				$errors[0][] = array(
					'orderId' => 0,
					'invoice' => $updated_invoice,
					'errorMsg' => 'Missing customer or ownercompany entries'
				);
			}



			$finishedBatchHasPrint = false;

			$s_sql = "SELECT * FROM invoice WHERE batch_id = ? AND not_processed = 1";
			$o_query = $o_main->db->query($s_sql, array($v_active_batch['id']));
			$not_processed_count = $o_query ? $o_query->num_rows() : 1;
			if($not_processed_count == 0){
				$s_sql = "SELECT * FROM invoice WHERE batch_id = ? AND for_print = 1";
				$o_query = $o_main->db->query($s_sql, array($v_active_batch['id']));
				$v_rows = $o_query ? $o_query->result_array() : array();
				if(count($v_rows) > 0){
					$finishedBatchHasPrint = true;
				}
				if($finishedBatchHasPrint){
					$sendToEmail = $v_active_batch['createdBy'];
					if($batchinvoicing_accountconfig['printing_invoice_notification_email'] != ""){
						$sendToEmail = $batchinvoicing_accountconfig['printing_invoice_notification_email'];
					}
					if(filter_var($sendToEmail, FILTER_VALIDATE_EMAIL)) {
						$v_membersystem = array();
						$o_query = $o_main->db->query("SELECT * FROM cache_userlist_membershipaccess");
						$v_cache_userlist_membership = $o_query ? $o_query->result_array() : array();
						foreach($v_cache_userlist_membership as $v_user_cached_info) {
							$v_membersystem[$v_user_cached_info['username']] = $v_user_cached_info;
						}

						$o_query = $o_main->db->query("SELECT * FROM cache_userlist_access");
						$v_cache_userlist = $o_query ? $o_query->result_array() : array();
						foreach($v_cache_userlist as $v_user_cached_info) {
							$v_membersystem[$v_user_cached_info['username']] = $v_user_cached_info;
						}
						$currentPersonInfo = array();
						foreach($v_membersystem as $member){
						    if(mb_strtolower($member['username'])== mb_strtolower($sendToEmail)){
								$currentPersonInfo = $member;
							}
						}

						$v_files = array();
						foreach($v_rows as $v_row)
						{
							if(is_file(__DIR__."/../../../".$v_row["invoiceFile"]))
							{
								$v_files[] = __DIR__."/../../../".$v_row["invoiceFile"];
							}
						}
						$s_file = "batch_invoice_".$v_active_batch["id"].".pdf";
						if(sizeof($v_files)==0)
						{

						} else {
							$o_pdf_merge = new concat_pdf();
							$o_pdf_merge->setFiles($v_files);
							$o_pdf_merge->concat();
							ob_end_clean();
							$o_pdf_merge->Output(__DIR__."/../../../uploads/protected/invoices/".$s_file, "F");

						}

						// $s_sql = "select * from file_links where content_table = 'batch_invoicing' AND content_id = ?";
						// $o_query = $o_main->db->query($s_sql, array($v_active_batch['id']));
						// $file_link = $o_query ? $o_query->row_array() : array();
						//
						// $key = "";
						// if($file_link){
						// 	$key = $file_link['link_key'];
						// } else {
						// 	do {
						// 		$key = generateRandomString("40");
						// 		$s_sql = "select * from file_links where content_table = 'batch_invoicing' AND link_key = ?";
						// 		$o_query = $o_main->db->query($s_sql, array($key));
						// 		$key_item = $o_query ? $o_query->row_array() : array();
						// 	} while(count($key_item) > 0);
						//
						//
						// 	$s_sql = "INSERT INTO file_links SET content_table = 'batch_invoicing', content_id = ?, link_key = ?, filepath = ?";
						// 	$o_query = $o_main->db->query($s_sql, array($v_active_batch['id'], $key, "uploads/protected/invoices/".$s_file));
						//
						// }
						if($_POST['subject'] == ""){
							$_POST['subject'] = $formText_InvoicesForPrint_output;
						}
						$s_email_subject = $_POST['subject'];

						$s_email_body = $formText_Hi_output."<br/><br/>";
						$s_email_body .= nl2br($formText_YouCanDownloadInvoicesForPrintFromBatchinvoicingModule_output)."<br/>";
						// $s_email_body .= "<a href='".$v_active_batch['server_url']."/modules/Accountinfo/view_file.php?key=".$key."'>".$formText_ClickHereToOpen_output."</a>";
						$s_email_body .= "<br/><br/>";

						$mail = new PHPMailer;
						$mail->CharSet  = 'UTF-8';
						$mail->IsSMTP(true);
						$mail->isHTML(true);
						if($v_email_server_config['host'] != "")
						{
							$mail->Host = $v_email_server_config['host'];
							if($v_email_server_config['port'] != "") $mail->Port = $v_email_server_config['port'];

							if($v_email_server_config['username'] != "" and $v_email_server_config['password'] != "")
							{
								$mail->SMTPAuth = true;
								$mail->Username = $v_email_server_config['username'];
								$mail->Password = $v_email_server_config['password'];
							}
						} else {
							$mail->Host = "mail.dcode.no";
						}
						$mail->From     = 'noreply@getynet.com';
						$mail->FromName = "Getynet";
						$mail->Subject  = $s_email_subject;
						$mail->Body     = $s_email_body;
						$mail->AltBody    = strip_tags($s_email_body);
						$mail->AddAddress($sendToEmail, $currentPersonInfo['first_name']." ".$currentPersonInfo['middle_name']." ".$currentPersonInfo['last_name']);

						$s_sql = "INSERT INTO sys_emailsend (id, created, createdBy, `type`, send_on, sender, sender_email, subscriberlist_id, unsubscriberlist_id, content_id, content_table, content_module_id, sending_limit, subject, text) VALUES (NULL, NOW(), 'noreply@getynet.com', 2, NOW(), '', '".addslashes('noreply@getynet.com')."', 0, 0, '".$v_active_batch['id']."', 'batch_invoicing', '', 0, '".addslashes($s_email_subject)."', '".addslashes($s_email_body)."');";
						$o_main->db->query($s_sql);
						$l_emailsend_id = $o_main->db->insert_id();

						$s_sql = "INSERT INTO sys_emailsendto (id, emailsend_id, receiver, receiver_email, `status`, status_message, perform_time, perform_count) VALUES (NULL, '".$l_emailsend_id."', '', '".addslashes($sendToEmail)."', 1, '', NOW(), 1);";
						$o_main->db->query($s_sql);
						$l_emailsendto_id = $o_main->db->insert_id();

						if($mail->Send())
						{

						} else {
							$errors[-1] = $formText_ErrorSendingEmail_output;

							$s_sql = "UPDATE sys_emailsendto SET status = 2, status_message = '".json_encode($mail)."' WHERE id = ?";
							$o_main->db->query($s_sql, array($l_emailsendto_id));

							$mail = new PHPMailer;
							$mail->CharSet  = 'UTF-8';
							$mail->IsSMTP(true);
							$mail->isHTML(true);
							if($v_email_server_config['host'] != "")
							{
								$mail->Host = $v_email_server_config['host'];
								if($v_email_server_config['port'] != "") $mail->Port = $v_email_server_config['port'];

								if($v_email_server_config['username'] != "" and $v_email_server_config['password'] != "")
								{
									$mail->SMTPAuth = true;
									$mail->Username = $v_email_server_config['username'];
									$mail->Password = $v_email_server_config['password'];
								}
							} else {
								$mail->Host = "mail.dcode.no";
							}
							$mail->From     = "noreply@getynet.com";
							$mail->FromName = "Getynet.com";
							$mail->Subject  = $formText_NotDelivered_Output.": ".$s_email_subject;
							$mail->Body     = $s_email_body;
							$mail->AddAddress(trim($v_email_server_config['technical_email']));
							$mail->AddAttachment($invoiceFile);
							foreach($files_attached as $file_to_attach) {
								$mail->AddAttachment(__DIR__."/../../../../".rawurldecode($file_to_attach[1][0]));
							}

						}
					} else {
						$errors[-1] = $formText_InvalidEmail_output;
					}
				}
			}
		}
	} catch(Exception $e){
		$errors[-1] = 'Fatal error '.json_encode($e);
	}
	if(count($errors) > 0){
		foreach($errors as $key => $error_array){
			if($key == -1){
				$s_sql = "SELECT * FROM moduledata WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($updated_invoice['moduleID']));
				$moduleInfo = $o_query ? $o_query->row_array() : array();
				$invoice_id_to_pass = $updated_invoice['id'];
				if($updated_invoice['external_invoice_nr'] > 0){
					$invoice_id_to_pass = $updated_invoice['external_invoice_nr'];
				}
				//add info to message
				$v_response = json_decode(APIconnectorAccount("account_message_add", $v_accountinfo['accountname'], $v_accountinfo['password'],
				array('message'=>$error_array, 'username'=>$updated_invoice['createdBy'], 'module_name' => $moduleInfo['name'], 'module_id' => $updated_invoice['moduleID'], 'content_id' => $invoice_id_to_pass, 'content_table'=> "invoice", 'alert_level'=> 5, 'message_type'=>4)
				), TRUE);
			} else {
				foreach($error_array as $error){
					$s_sql = "SELECT * FROM moduledata WHERE id = ?";
					$o_query = $o_main->db->query($s_sql, array($error['invoice']['moduleID']));
					$moduleInfo = $o_query ? $o_query->row_array() : array();

					$invoice_id_to_pass = $error['invoice']['id'];
					if($error['invoice']['external_invoice_nr'] > 0){
						$invoice_id_to_pass = $error['invoice']['external_invoice_nr'];
					}

					//add info to message
					$v_response = json_decode(APIconnectorAccount("account_message_add", $v_accountinfo['accountname'], $v_accountinfo['password'],
					array('message'=>$error['errorMsg'], 'username'=>$error['invoice']['createdBy'], 'module_name' => $moduleInfo['name'], 'module_id' => $error['invoice']['moduleID'], 'content_id' => $invoice_id_to_pass, 'content_table'=> "invoice", 'alert_level'=> 5, 'message_type'=>4)
					), TRUE);
				}
			}
		}
		$finishedStatusSql = ", content_status = 2";
	} else {
		$s_sql = "SELECT * FROM invoice WHERE not_processed = 1";
		$o_query = $o_main->db->query($s_sql);
		$notProcessedInvoices = $o_query ? $o_query->result_array() : array();
		$finishedStatusSql = "";
		if(count($notProcessedInvoices) == 0) {
			$finishedStatusSql = ", content_status = 2";
		}
	}
	$l_next_run = strtotime($v_auto_task['next_run']) + 10;

	$o_main->db->query("UPDATE auto_task SET last_run = next_run, next_run = '".$o_main->db->escape_str(date("Y.m.d H:i:s", $l_next_run))."'".$finishedStatusSql." WHERE id = '".$o_main->db->escape_str($v_auto_task['id'])."'");
	//status (0 - Idle (only for showing in web), 1 - Queued, 2 - Running, 3 - Completed)
	$o_main->db->query("UPDATE auto_task_log SET status = 3, finished = NOW(), message = '' WHERE id = '".$o_main->db->escape_str($v_auto_task['auto_task_log_id'])."'");

	$v_return['status'] = 1;
} else {
	$v_return['messages'][] = 'Auto task cannot be found';
}

ob_end_clean();
echo json_encode($v_return);


function catch_fatal_error()
{
	// Getting Last Error
	$last_error =  error_get_last();
	
	// Check if Last error is of type FATAL
	if(isset($last_error['type']) && in_array($last_error['type'], array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR)))
	{
		// Fatal Error Occurs
		$s_message = "Fatal error occurred executing script:\n".__FILE__."\n\nError details:\n".var_export($last_error, TRUE);
		$s_mailserver = 'mail.dcode.no';
		$s_mailserver_port = 25;
		$s_email_sender = 'noreply@dcode.no';
		$v_alert_email = array('agris@dcode.no', 'byamba@dcode.no', 'david@dcode.no');
		
		$err_mail = new PHPMailer;
		$err_mail->CharSet	= 'UTF-8';
		$err_mail->Host		= $s_mailserver;
		$err_mail->Port		= $s_mailserver_port;
		$err_mail->IsSMTP(true);
		$err_mail->SetFrom($s_email_sender);
		foreach($v_alert_email as $s_email) $err_mail->AddAddress($s_email);
		$err_mail->Subject  = "ALERT - BatchInvoicing autotask failure";
		$err_mail->Body		= $s_message;
		$err_mail->WordWrap = 150;
		$err_mail->Send();
		
		global $v_auto_task;
		$o_main = get_instance();
		
		$l_next_run = strtotime($v_auto_task['next_run']) + 10;
	
		$o_main->db->query("UPDATE auto_task SET last_run = next_run, next_run = '".$o_main->db->escape_str(date("Y.m.d H:i:s", $l_next_run))."', content_status = 2 WHERE id = '".$o_main->db->escape_str($v_auto_task['id'])."'");
		//status (0 - Idle (only for showing in web), 1 - Queued, 2 - Running, 3 - Completed)
		$o_main->db->query("UPDATE auto_task_log SET status = 3, finished = NOW(), message = '".$o_main->db->escape_str($s_message)."' WHERE id = '".$o_main->db->escape_str($v_auto_task['auto_task_log_id'])."'");
		
		ob_end_clean();
		echo json_encode(array('messages_fatal'=>array($s_message)));
	}
}