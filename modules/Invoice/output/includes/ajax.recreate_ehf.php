<?php
$l_invoice_id = $_POST['invoiceId'] ? $_POST['invoiceId'] : 0;

if($l_invoice_id > 0)
{
	$o_query = $o_main->db->query("SELECT * FROM invoice WHERE id = '".$o_main->db->escape_str($l_invoice_id)."'");
	$v_invoice = $o_query ? $o_query->row_array() : array();
}

$s_sql = "SELECT * FROM batch_invoicing_accountconfig";
$o_query = $o_main->db->query($s_sql);
$batchinvoicing_accountconfig = $o_query ? $o_query->row_array() : array();
if($moduleAccesslevel > 10)
{
	if($v_invoice){
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

		if(!function_exists('APIconnectOpen')) include(__DIR__.'/../../input/includes/APIconnect.php');
		$v_countries = array();
		$v_response = json_decode(APIconnectOpen("countrylistget"), TRUE);
		if(isset($v_response['status']) && $v_response['status'] == 1)
		{
			foreach($v_response['data'] as $v_item)
			{
				$v_countries[$v_item['countryID']] = $v_item['name'];
			}
		}
		$_POST['folder'] = "procedure_create_invoices";
		$devaccess = $variables->developeraccess;
	    $variables->developeraccess = 0;
		include(__DIR__."/../../../BatchInvoicing/output/includes/readOutputLanguage.php");
	    $variables->developeraccess = $devaccess;

		include(__DIR__."/../../../BatchInvoicing/procedure_create_invoices/scripts/CREATE_INVOICE/functions.php");


		$s_sql = "SELECT c.* FROM customer c WHERE c.id = ? ";
		$o_query = $o_main->db->query($s_sql, array($v_invoice['customerId']));
		$v_customer = $o_query ? $o_query->row_array() : array();

		$ownercompany_id = $v_invoice['ownercompany_id'];
	   	$customerIdToDisplay = $v_customer['id'];

		$externalCustomerIdData = array();
		if ($ownercompanyAccountconfig['activate_global_external_company_id']) {
			$s_sql = "SELECT * FROM customer_externalsystem_id WHERE ownercompany_id = 0 AND customer_id = ?";
			$o_query = $o_main->db->query($s_sql, array($v_customer['id']));
			if($o_query && $o_query->num_rows()>0){
				$externalCustomerIdData = $o_query->row_array();
			}
		} else {
			// Get customer external id
			$s_sql = "SELECT * FROM customer_externalsystem_id WHERE ownercompany_id = $ownercompany_id AND customer_id = ?";
			$o_query = $o_main->db->query($s_sql, array($v_customer['id']));
			if($o_query && $o_query->num_rows()>0){
				$externalCustomerIdData = $o_query->row_array();
			}
		}
		if($externalCustomerIdData){
			$externalCustomerId = $externalCustomerIdData['external_id'];
			$customerIdToDisplay = $externalCustomerId;
		}

		$s_sql = "SELECT * FROM ownercompany WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($ownercompany_id));
		$v_settings = $o_query ? $o_query->row_array() : array();

		// Get bank account nr, iban, swift
		$bankAccountData = array();
		$bankAccountData['companyaccount'] = $v_settings['companyaccount'];
		$bankAccountData['companyiban'] = $v_settings['companyiban'];
		$bankAccountData['companyswift'] = $v_settings['companyswift'];


		$s_reference = $s_delivery_date = $s_delivery_address = $contantPersonLine ='';
		$s_sql = "SELECT * FROM customer_collectingorder WHERE invoiceNumber = ?";
		$o_query = $o_main->db->query($s_sql, array($v_invoice['id']));
		$collectingOrders = $o_query ? $o_query->result_array() : array();
		$hasAnyDiscount = false;

		$currentCurrencyDisplay = ($v_invoice['currencyName'] != 'EMPTY_CURRENCY' ? $v_invoice['currencyName'] : '');
		$decimalPlaces = $v_settings['numberDecimalsOnInvoice'] ? intval($v_settings['numberDecimalsOnInvoice']) : 2;

		$contactpID = array();
		$ordersArray = array();
		foreach($collectingOrders as $collectingOrder) {
			$contactpID[] = $collectingOrder['contactpersonId'];

			$s_reference = $collectingOrder['reference'];
			$s_delivery_date = ((!empty($collectingOrder['delivery_date']) && $collectingOrder['delivery_date'] != '0000-00-00') ? date('d.m.Y', strtotime($collectingOrder['delivery_date'])) : '');
			$s_delivery_address = trim(preg_replace('/\s+/', ' ', $collectingOrder['delivery_address_line_1'].' '.$collectingOrder['delivery_address_line_2'].' '.$collectingOrder['delivery_address_city'].' '.$collectingOrder['delivery_address_postal_code'].' '.$v_countries[$collectingOrder['delivery_address_country']]));

			$s_sql = "SELECT * FROM orders WHERE collectingorderId = ?";
			$o_query = $o_main->db->query($s_sql, array($collectingOrder['id']));
			$orders = $o_query ? $o_query->result_array() : array();
			foreach($orders as $order){
				if($order['discountPercent'] > 0) $hasAnyDiscount = true;

				$v_proc_variables["order_number"][] = $order['id'];

				$orderToPass['orderId'] = $order['id'];
				$orderToPass['articleName'] = $order['articleName'];
				$orderToPass['pricePerPiece'] = $order['pricePerPiece'];
				$orderToPass['amount'] = $order['amount'];
				$orderToPass['discountPercent'] = $order['discountPercent'];
				$orderToPass['vatPercentRate'] = $order['vatPercent'];
				$orderToPass['vatCode'] = $order['vatCode'];
				$orderToPass['vat'] = floatval($order['gross']) - floatval($order['priceTotal']);
				$orderToPass['priceTotal'] = $order['priceTotal'];

				$ordersArray['list'][]=$orderToPass;
			}
		}
		$ordersArray['totals']['totalSum'] = $v_invoice['totalExTax'];
		$ordersArray['totals']['totalVat'] = $v_invoice['tax'];
		$ordersArray['totals']['total'] = $v_invoice['totalInclTax'];

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
		$dateValShow = date('d.m.Y', strtotime($v_invoice['invoiceDate']));
		$dateExpireShow = date('d.m.Y', strtotime($v_invoice['dueDate']));
		list($html1, $html2, $html3, $html4, $html5) = generateHtml($ordersArray, $v_customer, $v_settings, $bankAccountData, $contantPersonLine,$s_reference,$s_delivery_date,$s_delivery_address,$customerIdToDisplay,$dateValShow,$dateExpireShow,$hasAnyDiscount,$currentCurrencyDisplay, $decimalPlaces, $v_proc_variables, $variables, $_GET['accountname']);


		$newInvoiceNrOnInvoice = $v_invoice['external_invoice_nr'];

		$kidnumber = $v_invoice['kidNumber'];

		$files_attached_pdf = array();
		if(count($files_attached) > 0) {
			$files_attached_without_pdf = array();
			foreach($files_attached as $file_to_attach) {
				$mime_type = mime_content_type(__DIR__."/../../../../".$file_to_attach[1][0]);
				if($mime_type != "application/pdf"){
					array_push($files_attached_without_pdf, $file_to_attach);
				} else {
					array_push($files_attached_pdf, __DIR__."/../../../../".$file_to_attach[1][0]);
				}
			}
			$files_attached = $files_attached_without_pdf;
		}
		//
		$html = $html1 . $newInvoiceNrOnInvoice . $html2;
		if($v_settings['kidOnInvoice'] > 0 || $batchinvoicing_accountconfig['activate_not_update_kid_number'])
			$html .=  $html3 .  $kidnumber .$html4;
		$html .=  $html5;
		$html = html_entity_decode($html);
		$html = $html;

		$file = "invoice_".$newInvoiceNrOnInvoice;
	    if ($activateMultiOwnerCompanies)
		{
			$file = "invoice_oc".$ownercompany_id."_".$newInvoiceNrOnInvoice;
	    }

		if($v_customer['useOwnInvoiceAdress']) {
			$s_cust_addr_prefix = 'ia';
		} else {
			$s_cust_addr_prefix = 'pa';
		}






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
		$v_ehf_data['supplier_country'] = 'NO';//(($v_settings['companyCountry'] == '' || strtolower($v_settings['companyCountry']) == 'norge') ? 'NO' : $v_settings['companyCountry']);
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
		$v_ehf_data['customer_country'] = 'NO';//(($v_customer[$s_cust_addr_prefix.'Country'] == '' || strtolower($v_customer[$s_cust_addr_prefix.'Country']) == 'norge') ? 'NO' : $v_customer[$s_cust_addr_prefix.'Country']);
		// Mandatory ONLY FOR COMPANIES (not for consumers) start
		$v_ehf_data['customer_org_nr_vat'] = $v_ehf_data['customer_country'].$v_ehf_data['customer_org_nr'].(('NO'==strtoupper($v_ehf_data['customer_country']) && stripos($v_ehf_data['customer_org_nr'], 'MVA') === FALSE) ? 'MVA' : '');
		$v_ehf_data['customer_legal_name'] = trim($v_customer['name']." ".$v_customer['middlename']." ".$v_customer['lastname']);
		$v_ehf_data['customer_legal_org_nr'] = preg_replace('#[^0-9]+#', '', $v_customer['publicRegisterId']);
		$v_ehf_data['customer_legal_city'] = $v_customer[$s_cust_addr_prefix.'City']; // optional
		$v_ehf_data['customer_legal_country'] = 'NO';//(($v_customer[$s_cust_addr_prefix.'Country'] == '' || strtolower($v_customer[$s_cust_addr_prefix.'Country']) == 'norge') ? 'NO' : $v_customer[$s_cust_addr_prefix.'Country']); // optional
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
			if(in_array($order['orderId'],$v_proc_variables["order_number"]))
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
		}

		$l_counter = 1;
		$v_ehf_data['additional_document_reference'] = array();
		foreach($files_attached as $file_to_attach)
		{
			if(!is_file(__DIR__."/../../../../".$file_to_attach[1][0])) continue;
			if(filesize(__DIR__."/../../../../".$file_to_attach[1][0]) > 5242880) continue; // Skip if larger than 5MB
			$s_mime_type = mime_content_type(__DIR__."/../../../../".$file_to_attach[1][0]);
			if('application/pdf' == $s_mime_type)
			{
				$v_info = pathinfo(__DIR__."/../../../../".$file_to_attach[1][0]);
				$v_item = array();
				$v_item['id'] = $v_info['basename'];
				$v_item['document_type'] = 'File'.$l_counter;
				$v_item['attachment_mime'] = $s_mime_type;
				$v_item['attachment_binary'] = base64_encode(file_get_contents(__DIR__."/../../../../".$file_to_attach[1][0]));
				//$v_item['attachment_uri'] = 'http://www.suppliersite.eu/sheet001.html';
				$v_ehf_data['additional_document_reference'][] = $v_item;
				$l_counter++;
			}
		}


		$filepath = __DIR__."/../../../../uploads/protected/invoices_ehf/";

		$s_ehf_file = $file;
		$s_ehf_file_path = $filepath.$s_ehf_file.'.xml';

		$file .= ".pdf";
		$filepath = __DIR__."/../../../../uploads/protected/invoices/";
		$s_pdf_file_path = $filepath.$file;
		if(!file_exists($filepath))
		{
			mkdir($filepath, 0777,true);
		}
		chmod($filepath, 0777);

		$invoicelogo = json_decode($v_settings['invoicelogo'],true);
		// create_pdf($filepath, $file, $files_attached_pdf, $newInvoiceNrOnInvoice, $invoicelogo, $v_settings, $_GET['accountname'], $html, trim($v_settings['choose_custom_invoice_template']));

		// $o_main->db->query("UPDATE invoice SET invoiceFile = ? WHERE id = ?", array("uploads/protected/invoices/".$file, $v_invoice['id']));


		$v_item = array();
		$v_item['id'] = $newInvoiceNrOnInvoice.'.pdf';
		$v_item['document_type'] = 'Commercial invoice'; //If, however, the "pdf-version" is supplied as an attachment, the element "DocumentType" must specify "Commercial invoice" for an invoice and "Credit note" for a creditnote.
		$v_item['attachment_mime'] = 'application/pdf';
		$v_item['attachment_binary'] = base64_encode(file_get_contents($s_pdf_file_path));
		//$v_item['attachment_uri'] = 'http://www.suppliersite.eu/sheet001.html';
		$v_ehf_data['additional_document_reference'][] = $v_item;
		$s_ehf_xml = create_ehf_invoice($v_ehf_data);
		file_put_contents($s_ehf_file_path, $s_ehf_xml);

		$o_main->db->query("UPDATE invoice SET ehf_invoice_file = '".$o_main->db->escape_str("uploads/protected/invoices_ehf/".$s_ehf_file.".xml")."' WHERE id = ?", array($v_invoice['id']));
	}
}

?>
