<?php
$s_sql = "SELECT * FROM article_accountconfig";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
	$article_accountconfig = $o_query->row_array();
}
$selectedInvoicesArray = array();
if(isset($_GET['selectedInvoices'])){
	$selectedInvoicesArray = explode(",", $_GET['selectedInvoices']);
}
$s_sql = "select * from sys_emailserverconfig order by default_server desc";
$o_query = $o_main->db->query($s_sql);
$v_email_server_config = $o_query ? $o_query->row_array() : array();

$search = $_GET['search'] > 0 ? $_GET['search'] : "";
// read output settings
// require_once __DIR__ . '/settingsOutput/settings.php';
$s_sql = "SELECT * FROM batch_invoicing_basisconfig";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
	$basisConfigData = $o_query->row_array();
}
$s_sql = "SELECT * FROM batch_invoicing_accountconfig";
$o_query = $o_main->db->query($s_sql);
$batchinvoicing_accountconfig = $o_query ? $o_query->row_array() : array();

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

if((int)$batchinvoicing_accountconfig['activate_ehf_check'] > 0){
	$basisConfigData['activate_ehf_check'] = (int)$batchinvoicing_accountconfig['activate_ehf_check'];
}

include(__DIR__.'/ehf_check.php');

if(!function_exists("validate_ehf_invoice_country")) include(__DIR__."/../procedure_create_invoices/scripts/CREATE_INVOICE/functions.php");

//if(!function_exists('APIconnectOpen')) include(__DIR__.'/../../../input/includes/APIconnect.php');
$v_countries = array();
$v_response = json_decode(APIconnectorOpen("countrylistget"), TRUE);
if(isset($v_response['status']) && $v_response['status'] == 1)
{
	foreach($v_response['data'] as $v_item)
	{
		$v_countries[$v_item['countryID']] = $v_item['name'];
	}
}

$project_id = isset($_GET['project_filter']) ? $_GET['project_filter'] : 0;
$department_id = isset($_GET['department_filter']) ? $_GET['department_filter'] : 0;

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
$sql_filter = "";
$ownercompany_filter = $_GET['ownercompany'] ? explode(",", $_GET['ownercompany']) : array();
$ownercompany_filter_sql = "";
$real_ownercompany_filter = array();
if(count($ownercompany_filter) > 0){
	foreach($ownercompany_filter as $singleItem){
		if($singleItem > 0){
			array_push($real_ownercompany_filter, $singleItem);
		}
	}
	if(count($real_ownercompany_filter) > 0){
		$ownercompany_filter_sql = " AND o.ownercompany_id IN (".implode(',', $real_ownercompany_filter).")";
	}
}
if($search != ""){
	$o_query = $o_main->db->query("SELECT *, CONCAT_WS(' ', customer.name, customer.middlename, customer.lastname) as customerName FROM customer WHERE id = ?", array($search));
	$searchedCustomer = $o_query ? $o_query->row_array() : array();
}



if($project_id > 0){
	$o_query = $o_main->db->query("SELECT * FROM projectforaccounting WHERE projectnumber = ?", array($project_id));
	$projectData = $o_query ? $o_query->row_array() : array();
	$sql_filter .= " AND (co.accountingProjectCode = ".$o_main->db->escape($projectData['projectnumber']).")";
}
if($department_id > 0){
	$o_query = $o_main->db->query("SELECT * FROM departmentforaccounting WHERE departmentnumber = ?", array($department_id));
	$departmentData = $o_query ? $o_query->row_array() : array();
	$sql_filter .= " AND (co.department_for_accounting_code = ".$o_main->db->escape($departmentData['departmentnumber']).")";
}

$totalOwnerCompanies = 0;
$ownercompanies = array();
$s_sql = "SELECT * FROM ownercompany WHERE content_status < 2";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
	$totalOwnerCompanies = $o_query->num_rows();
	$ownercompanies = $o_query->result_array();
}
if (!$activateMultiOwnerCompanies) {
	$s_sql = "SELECT * FROM ownercompany";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0){
		$defaultOwnerCompany = $o_query->row_array();
    	$defaultOwnerCompanyId = $defaultOwnerCompany['id'];
	}
}
//include(__DIR__."/list_btn.php");
$v_address_format = array('paStreet', 'paCity', 'paCountry', 'paPostalNumber');

function getOrderVAT($o_main, $orderId) {
	global $article_accountconfig;
	// Get VAT
	// Building query
    $q = "SELECT o.vatCode vatcodeId, o.bookaccountNr bookAccountNr FROM orders o WHERE o.id = ?";
	$o_query = $o_main->db->query($q, array($orderId));
    if($o_query && $o_query->num_rows()>0){
        $rowInfo = $o_query->row_array();
        $bookaccountNr = $rowInfo['bookAccountNr'];
        $vatCode = $rowInfo['vatcodeId'];
    }

    $s_sql = "SELECT * FROM vatcode WHERE vatCode = ?";
    $o_query = $o_main->db->query($s_sql, array($vatCode));
    $vatItem = $o_query ? $o_query->row_array() : array();
    $vatPercent = $vatItem['percentRate'];

	// Preping return data
	$data = array(
		'percentRate' => $vatPercent,
		'code' => $vatCode,
		'bookAccountNr' => $bookaccountNr
	);
	return $data;
}


$s_sql = "SELECT * FROM moduledata WHERE name = '".$o_main->db->escape_str($module)."'";
$o_result = $o_main->db->query($s_sql);
$module_data = $o_result ? $o_result->row_array() : array();

$fwaFileuploadConfigs = array(
	array (
	  'module_folder' => 'BatchInvoicing', // module id in which this block is used
	  'id' => 'articleinsfileupload',
	  'upload_type' => 'file',
	  'content_table' => 'batch_invoicing',
	  'content_field' => 'attached_files',
	  'content_module_id' => $module_data['uniqueID'], // id of module
	  'dropZone' => 'block',
      'file_type'=>'pdf',
	  'callbackAll' => 'callBackOnUploadAll',
	  'callbackStart' => 'callbackOnStart',
	  'callbackDelete' => 'callbackOnDelete'
	)
);

// List of available bank accounts - THIS IS BROKEN WITH MULTI OWNERCOMPAMY IMPLEMENTATION - MUST FIX
$bankAccountArray = array();
$bankAccountArray[1] = $v_settings['companyaccount'];
if (!empty($v_settings['companyBankAccount2'])) $bankAccountArray[2] = $v_settings['companyBankAccount2'];
if (!empty($v_settings['companyBankAccount3'])) $bankAccountArray[3] = $v_settings['companyBankAccount3'];

// !! Reading all customers and orders in ob_start() buffer
//
$customersCount = 0;
$customersCountWithErrors = 0;
$customersSelected = 0;
$v_customers = array();
$s_group_sql = ", IFNULL(co.contactpersonId, 0)";
if($batchinvoicing_accountconfig['activate_merge_multiple_contactpersons']){
	$s_group_sql = "";
}
$s_group_sql_project = ", IF(co.department_for_accounting_code IS NULL or co.department_for_accounting_code = '', 0, co.department_for_accounting_code),
IF(co.accountingProjectCode IS NULL or co.accountingProjectCode = '', 0, co.accountingProjectCode)";
if($batchinvoicing_accountconfig['activate_merge_multiple_project_and_department_codes']){
	$s_group_sql_project = "";
}
if($search != ""){
	$sql_filter.= " AND c.id = '".$search."'";
}
$s_sql = "SELECT c.*, co.ownercompanyId ownercompany_id, co.seperateInvoiceFromSubscription as seperatedInvoiceSubscriptionId, co.id collectingorderId, co.seperatedInvoice, co.contactpersonId,
		CONCAT_WS(' ', TRIM(co.reference), TRIM(co.delivery_date), TRIM(co.delivery_address_line_1), TRIM(co.delivery_address_line_2), TRIM(co.delivery_address_city), TRIM(co.delivery_address_postal_code), TRIM(co.delivery_address_country)) as combinedReference, co.department_for_accounting_code, co.accountingProjectCode,
		IF(cs.customer_subunit_invoicing_group_id IS NULL or cs.customer_subunit_invoicing_group_id = '', 0, cs.customer_subunit_invoicing_group_id) as customer_subunit_invoicing_group_id
	 FROM customer_collectingorder co
	 JOIN customer c ON c.id = co.customerId
	 LEFT OUTER JOIN customer_subunit cs ON cs.id = co.customer_subunit_id
	 WHERE co.approvedForBatchinvoicing = 1 AND (co.invoiceNumber = 0 OR co.invoiceNumber is null) AND co.content_status = 0
	 ".$sql_filter."
	 GROUP BY c.id,
	 IF(co.seperatedInvoice IS NULL or co.seperatedInvoice = '', 0, co.id),
	 IF(co.seperateInvoiceFromSubscription IS NULL or co.seperateInvoiceFromSubscription = '', 0, co.seperateInvoiceFromSubscription),
	 IF(co.ownercompanyId IS NULL or co.ownercompanyId = '', 0, co.ownercompanyId),
	 combinedReference,
	 IF(cs.customer_subunit_invoicing_group_id IS NULL or cs.customer_subunit_invoicing_group_id = '', 0, cs.customer_subunit_invoicing_group_id)".$s_group_sql_project.$s_group_sql."
	 ORDER BY c.name";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
	$v_customers = $o_query->result_array();
}

if($basisConfigData['activate_ehf_check'] > 1 && !isset($_POST['step'])) return;


ob_start();
require_once("fnc_getMaxDecimalAmount.php");
$totalDateArrayCustomer = array();
$totalDateArraySubscription = array();
$numberOfInvoicesFromSubscription = array();
$numberOfInvoicesFromCustomer = array();
$prepaidCommonCostCounter = 0;
foreach($v_customers as $v_customer)
{
	$totalSumInclVat = 0;
    // Get block group id, will be for orders grouping
    $block_group_id = $v_customer['id'];
    if ($activateMultiOwnerCompanies) {
        $block_group_id = $v_customer['id'] . '-' . $v_customer['ownercompany_id'];
    }
    $block_group_id .="-".$v_customer['collectingorderId'];

    $block_group_id .="-".$v_customer['accountingProjectCode'];
    $block_group_id .="-".$v_customer['department_for_accounting_code'];
    $block_group_id .="-".$v_customer['customer_subunit_invoicing_group_id'];

    $original_block_group_id = $block_group_id;

	if(intval($v_customer['seperatedInvoiceSubscriptionId']) > 0){
		$block_group_id.="-seperate-".$v_customer['seperatedInvoiceSubscriptionId'];
	}

    $s_sql = "SELECT * FROM ownercompany WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($v_customer['ownercompany_id']));
	$v_settings = $o_query ? $o_query->row_array() : array();

    $decimalPlaces = $v_settings['numberDecimalsOnInvoice'] ? intval($v_settings['numberDecimalsOnInvoice']) : 2;

    // Currency setup
    $isMultiCurrencyAccount = $v_settings['allowMultiCurrencies'];
    $defaultAccountCurrency = $v_settings['currencyNameWhenOnlyOne']; // when multi currency is switched of
    $defaultAccountCurrencyCode = $v_settings['currencyCodeWhenOnlyOne']; // when multi currency is switched of

    if ($isMultiCurrencyAccount) {
    	$allCurrenciesList = array();
    	$allCurrenciesCodeList = array();
    	$currencies = array();
	    $s_sql = "SELECT * FROM currency";
		$o_query = $o_main->db->query($s_sql);
		if($o_query && $o_query->num_rows()>0){
			$currencies = $o_query->result_array();
		}
		foreach($currencies as $currency){
    		$allCurrenciesList[$currency['id']] = $currency['shortname'];
    		$allCurrenciesCodeList[$currency['id']] = $currency['code'];
    	}
    }

	$s_address = "";
	foreach($v_address_format as $s_key)
	{
		if($v_customer[$s_key] != "")
		{
			if($s_address != "") $s_address .= ", ";
			$s_address .= $v_customer[$s_key];
		}
	}
	// $l_vat = $v_settings['vat'];
	// if($v_customer['taxFreeSale'] == 1) $l_vat = 0;

	$v_orders = array();
	$s_sql_select = "SELECT orders.*, CONCAT_WS(' ', TRIM(co.reference), TRIM(co.delivery_date), TRIM(co.delivery_address_line_1), TRIM(co.delivery_address_line_2), TRIM(co.delivery_address_city), TRIM(co.delivery_address_postal_code), TRIM(co.delivery_address_country)) as combinedReference, co.department_for_accounting_code, co.accountingProjectCode
  	FROM orders ";
	$s_sql_join = " JOIN customer_collectingorder co ON co.id = orders.collectingorderId";
	$s_sql_where = " WHERE co.approvedForBatchinvoicing = 1 AND (co.invoiceNumber = 0 OR co.invoiceNumber is null) AND co.content_status = 0 AND orders.content_status = 0 AND co.customerId = ".$v_customer['id'];
	$s_sql_group = "";
	if(intval($v_customer['seperatedInvoice']) > 0){
		$s_sql_where .= " AND (co.seperatedInvoice = ".$v_customer['seperatedInvoice'].") AND co.id = ".$v_customer['collectingorderId'];
	} else {
		$s_sql_where .= " AND CONCAT_WS(' ', TRIM(co.reference), TRIM(co.delivery_date), TRIM(co.delivery_address_line_1), TRIM(co.delivery_address_line_2), TRIM(co.delivery_address_city), TRIM(co.delivery_address_postal_code), TRIM(co.delivery_address_country)) = '".$o_main->db->escape_str($v_customer['combinedReference']) ."' AND (co.seperatedInvoice is null OR co.seperatedInvoice = 0) ";
	}
	if($v_customer['customer_subunit_invoicing_group_id'] > 0){
		$s_sql_join .= " LEFT OUTER JOIN customer_subunit cs ON cs.id = co.customer_subunit_id";
		$s_sql_where .= " AND cs.customer_subunit_invoicing_group_id = '".$o_main->db->escape_str($v_customer['customer_subunit_invoicing_group_id'])."'";
	}

	if(intval($v_customer['seperatedInvoiceSubscriptionId']) > 0){
		$s_sql_where .= " AND (co.seperateInvoiceFromSubscription = ".$o_main->db->escape($v_customer['seperatedInvoiceSubscriptionId']).") AND co.id = ".$o_main->db->escape($v_customer['collectingorderId']);
	} else {
		$s_sql_where .= " AND (co.seperateInvoiceFromSubscription is null OR co.seperateInvoiceFromSubscription = 0) ";
	}

	if(!$batchinvoicing_accountconfig['activate_merge_multiple_project_and_department_codes']){
		if(intval($v_customer['accountingProjectCode']) > 0){
			$s_sql_where .= " AND co.accountingProjectCode = ".$o_main->db->escape($v_customer['accountingProjectCode']);
		}
		if(intval($v_customer['department_for_accounting_code']) > 0){
			$s_sql_where .= " AND co.department_for_accounting_code = ".$o_main->db->escape($v_customer['department_for_accounting_code']);
		}
	}

	if ($activateMultiOwnerCompanies) {
		$s_sql_where .= " AND co.ownercompanyId = " . intval($v_customer['ownercompany_id']);
	}

	if(!$batchinvoicing_accountconfig['activate_merge_multiple_contactpersons']){
		if($v_customer['contactpersonId'] > 0){
			$s_sql_where .= " AND co.contactpersonId = ".$v_customer['contactpersonId'];
		} else {
			$s_sql_where .= " AND (co.contactpersonId is null OR co.contactpersonId = 0)";
		}
	}

	$s_sql = $s_sql_select.$s_sql_join.$s_sql_where.$s_sql_group;
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0){
		$v_orders = $o_query->result_array();
	}



    /**
     * Customer errors
     */
    $customer_errors = array();



    if(count($v_orders) == 0) {
		$customer_errors[] = array(
			'errorMsg' => $formText_NoOrderlines_output
		);
	}
    if(intval($v_customer['ownercompany_id']) == 0) {
		$customer_errors[] = array(
			'errorMsg' => $formText_OrderDoesNotHaveOwnerCompany_output
		);
	}
	if($v_customer["invoiceBy"] == 1)
	{
		if(trim($v_settings['invoiceSubjectEmail']) == "" || trim($v_settings['invoiceTextEmail']) == "" ) {
			$customer_errors[] = array(
				'errorMsg' => $formText_OwnerCompanyMissingInvoiceTextOrSubject_output
			);
		}
	}

	if($v_settings['customerid_autoormanually'] == 2 && intval($v_settings['nextCustomerId']) == 0) {
		$customer_errors[] = array(
			'errorMsg' => $formText_OwnercompanyMissingCustomerNumber_Output." ".$v_settings['name']
		);
	}
	$companyaccount = $v_settings['companyaccount'];
	if ($v_settings['companyBankAccount2'] != "") {
		$companyaccount = $v_settings['companyBankAccount2'];
	} elseif ($v_settings['companyBankAccount3'] != "") {
		$companyaccount = $v_settings['companyBankAccount3'];
	}
	if($companyaccount == ""){
		$customer_errors[] = array(
			'errorMsg' => $formText_OwnercompanyMissingCompanyBankAccount_Output.": ".$v_settings['name']
		);
	}
    // Check external customer id
    if ($v_settings['useExternalCustomerId']) {

        // Get customer external id
        $s_sql = "SELECT * FROM customer_externalsystem_id WHERE ownercompany_id = ? AND customer_id = ?";

		$o_query = $o_main->db->query($s_sql, array($v_customer['ownercompany_id'], $v_customer['id']));
		if($o_query && $o_query->num_rows()>0){
			$externalCustomerIdData = $o_query->row_array();
		}
        $externalCustomerId = $externalCustomerIdData['external_id'];

        if (!$externalCustomerId && $v_settings['useExternalCustomerId'] > 1) {
            $customer_errors[] = array(
                'errorMsg' => $formText_ExternalCustomerIdMustBeCreatedManuallyOrSynced_output
            );
        }

        if ($v_settings['useExternalCustomerId'] == 1 && !$v_settings['nextCustomerId']) {
            $customer_errors[] = array(
                'errorMsg' => $formText_NextCustomerIdMissingInSettings_output
            );
        }
    }

	// Check EHF related
	if($v_customer["invoiceBy"] == 2)
	{
		// Customer address
		if($v_customer['useOwnInvoiceAdress'])
		{
			$s_cust_addr_prefix = 'ia';
		} else {
			$s_cust_addr_prefix = 'pa';
		}

		if('' == trim(preg_replace('#[^0-9]+#', '', $v_settings['companyorgnr'])))
		{
			$customer_errors[] = array(
                'errorMsg' => $formText_SupplierOrgNumberIsMissingOrInvalid_Ehf
            );
		}
		if('' == trim($v_settings['companyname']))
		{
			$customer_errors[] = array(
                'errorMsg' => $formText_SupplierNameIsMissingOrInvalid_Ehf
            );
		}
		if('' == trim($v_settings['companypostalplace']))
		{
			$customer_errors[] = array(
                'errorMsg' => $formText_CityOfSupplierIsMissingOrInvalid_Ehf
            );
		}
		if('' == trim($v_settings['companyzipcode']))
		{
			$customer_errors[] = array(
                'errorMsg' => $formText_PostalCodeOfSupplierIsMissingOrInvalid_Ehf
            );
		}
		// Hardcoded currenlty as NO
		/*if('' == $v_data['supplier_country'] || !validate_ehf_invoice_country($v_data['supplier_country']))
		{
			$customer_errors[] = array(
                'errorMsg' => $formText_CountryOfSupplierIsMissingOrInvalid_Ehf
            );
		}*/
		if('' == trim(preg_replace('#[^0-9]+#', '', $v_customer['publicRegisterId'])))
		{
			$customer_errors[] = array(
                'errorMsg' => $formText_CustomerOrgNumIsMissingOrInvalid_Ehf
            );
		}
		if('' == trim($v_customer['name']." ".$v_customer['middlename']." ".$v_customer['lastname']))
		{
			$customer_errors[] = array(
                'errorMsg' => $formText_CustomerNameIsMissingOrInvalid_Ehf
            );
		}
		if('' == trim($v_customer[$s_cust_addr_prefix.'City']))
		{
			$customer_errors[] = array(
                'errorMsg' => $formText_CityOfCustomerIsMissingOrInvalid_Ehf
            );
		}
		if('' == trim($v_customer[$s_cust_addr_prefix.'PostalNumber']))
		{
			$customer_errors[] = array(
                'errorMsg' => $formText_PostalCodeOfCustomerIsMissingOrInvalid_Ehf
            );
		}
		// Hardcoded currenlty as NO
		/*if('' == $v_data['customer_country'] || !validate_ehf_invoice_country($v_data['customer_country']))
		{
			$customer_errors[] = array(
                'errorMsg' => $formText_CountryOfCustomerIsMissingOrInvalid_Ehf
            );
		}*/
		if(!empty(trim($v_customer['name']." ".$v_customer['middlename']." ".$v_customer['lastname'])) && '' == trim($v_customer['publicRegisterId']))
		{
			$customer_errors[] = array(
                'errorMsg' => $formText_CustomerLegalOrgNumberIsMissingOrInvalid_Ehf
            );
		}
		// Hardcoded currenlty to fill with 1 if empty
		/*if('' == $v_data['customer_contact_id'])
		{
			$customer_errors[] = array(
                'errorMsg' => $formText_CustomerContactpersonIsMissingOrInvalid_Ehf
            );
		}*/
		// Not in use currently
		/*if(!empty($v_data['tax_representative_name']))
		{
			if('' == $v_data['tax_representative_country'] || !validate_ehf_invoice_country($v_data['tax_representative_country']))
			{
				$v_return[] = $formText_TaxRepresentativePartyCountryIsMissingOrInvalid_Ehf;
			}
		}*/
	}

	/**
	 * Check if there are any order related issues
	 * + build contactperson array
	 */
	$ownercompanyCheck = false;
	if(count($real_ownercompany_filter) == 0 || (count($real_ownercompany_filter) > 0 && in_array($v_customer['ownercompany_id'], $real_ownercompany_filter))) {
		$ownercompanyCheck = true;
	}

	$errors = array();
	$contactPersons = array();
	$currencyIdsDetected = array();
	$projectCodes = array();
	$departmentCodes = array();
	$v_order_priceTotal = 0;
	$s_order_reference = $s_order_delivery_date = $s_order_delivery_address = '';
	$invoiceVatResult = array();

	foreach($v_orders as $v_order){
		$orderId = $v_order['id'];

		$articleCount = 0;
		$s_sql = "SELECT * FROM article WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($v_order['articleNumber']));
		if($o_query && $o_query->num_rows()>0){
			$articleCount = $o_query->num_rows();
		}
		$vatData = getOrderVAT($o_main, $orderId);

		if ($isMultiCurrencyAccount) {
			$currencyIdsDetected[$v_order['currencyId']] = $allCurrenciesList[$v_order['currencyId']];
		}


		// Check for articleNumber
		if (!$articleCount) {
			$errors[$orderId][] = array(
				'orderId' => $orderId,
				'errorMsg' => $formText_InvalidArticleNumber_output
			);
		}

		// Check VAT code
		$vatCount = 0;
		$s_sql = "SELECT * FROM vatcode WHERE vatCode = ?";
		$o_query = $o_main->db->query($s_sql, array($vatData['code']));
		if($o_query && $o_query->num_rows()>0){
			$vatCount = $o_query->num_rows();
		}

		if(!$vatCount) {
			$errors[$orderId][] = array(
				'orderId' => $orderId,
				'errorMsg' => $formText_InvalidVatCode_output
			);
		}

		// Check bookaccount nr
		$bookCount = 0;
		$s_sql = "SELECT * FROM bookaccount WHERE accountNr = ?";
		$o_query = $o_main->db->query($s_sql, array($vatData['bookAccountNr']));
		if($o_query && $o_query->num_rows()>0){
			$bookCount = $o_query->num_rows();
		}

		if(!$bookCount) {
			$errors[$orderId][] = array(
				'orderId' => $orderId,
				'errorMsg' => $formText_InvalidArticleBookAccount_output
			);
		}

		// EHF check for E+Z combo
		$s_sql = "SELECT * FROM vatcode WHERE vatCode = ?";
		$o_query = $o_main->db->query($s_sql, array($v_order['vatCode']));
		$vatInfo = $o_query ? $o_query->row_array() : array();
		$invoiceVatResult[$v_order['collectingorderIdid']][] = $vatInfo['ehf'];
		if($vatInfo['ehf'] == 'Z') {
			if(in_array('E', $invoiceVatResult[$v_order['collectingorderIdid']])){
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
		// Check for projectFAccNumber
		if ($basisConfigData['activateCheckForProjectNr']) {
			$fAccCount = 0;
			$s_sql = "SELECT * FROM projectforaccounting WHERE projectnumber = ?";
			$o_query = $o_main->db->query($s_sql, array($v_order['accountingProjectCode']));
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

		if($v_order['pricePerPiece'] < 0){
			$errors[$orderId][] = array(
				'orderId' => $orderId,
				'errorMsg' => $formText_PricePerPieceCanNotBeNegativeUseNegativeAmount_output
			);
		}

		// Get contactpersons
		$s_sql = "SELECT * FROM customer_collectingorder WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($v_order['collectingorderId']));
		if($o_query && $o_query->num_rows()>0){
			$collectingOrder = $o_query->row_array();
		}
				// Check for projectFAccNumber
		if ($basisConfigData['activateCheckForDepartmentCode']) {
			$fAccCount = 0;
			$s_sql = "SELECT * FROM departmentforaccounting WHERE departmentnumber = ?";
			$o_query = $o_main->db->query($s_sql, array($collectingOrder['department_for_accounting_code']));
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

		// Check EHF related
		if($v_customer["invoiceBy"] == 2)
		{
			$dateValShow = '';
			$s_sql = "SELECT * FROM orders LEFT OUTER JOIN subscriptionmulti ON subscriptionmulti.id = orders.invoiceDateSettingFromSubscription WHERE orders.id = ".$order['id']." ORDER BY orders.invoiceDateSettingFromSubscription ASC";
			$o_query = $o_main->db->query($s_sql);
			if($o_query && $o_query->num_rows()>0){
				$orderSingle = $o_query->row_array();
				if($orderSingle){
					if($orderSingle['invoiceDateSettingFromSubscription'] > 0){
						$notOverrideDate = true;
						if(!in_array($block_group_id,$numberOfInvoicesFromSubscription) && $ownercompanyCheck) {
							array_push($numberOfInvoicesFromSubscription, $block_group_id);
							if (($key = array_search($block_group_id, $numberOfInvoicesFromCustomer)) !== false) {
								unset($numberOfInvoicesFromCustomer[$key]);
							}
						}
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
							if(!in_array($block_group_id,$numberOfInvoicesFromCustomer) && $ownercompanyCheck) {
								array_push($numberOfInvoicesFromCustomer, $block_group_id);
							}
							$customerSetting = true;
							$dateValShow = date('d').".".date('m').".".date('Y');
							if(isset($_GET["invoice_date"]) && $_GET["invoice_date"] != ""){
								$dateValShow = $_GET["invoice_date"];
							}
							$dateExpireShow = date("d.m.Y", strtotime("+".$credittimeDays." days", strtotime($dateValShow)));
							if(isset($_GET["due_date"]) && $_GET["due_date"] != "" && strtotime($_GET['due_date']) > strtotime($_GET['invoice_date'])){
								$dateExpireShow = $_GET["due_date"];
							}
						}
					}
				}
			}
			$s_delivery_date = date("Y-m-d", strtotime((!empty($order['delivery_date']) && $order['delivery_date'] != '0000-00-00') ? $order['delivery_date'] : $dateValShow));
			if(!empty($s_delivery_date) && !validate_ehf_invoice_date_format($s_delivery_date))
			{
				$errors[$orderId][] = array(
					'orderId' => $orderId,
					'errorMsg' => $formText_DeliveryDateIsInvalid_Ehf
				);
			}
			// Hardcoded currenlty as NO
			/*if(!empty($v_data['delivery_country']) && !validate_ehf_invoice_country($v_data['delivery_country']))
			{
				$errors[$orderId][] = array(
					'orderId' => $orderId,
					'errorMsg' => $formText_CountryOfDeliveryIsMissingOrInvalid_Ehf
				);
			}*/
			// Hardcoded currenlty as 31
			/*if('' == $v_data['payment_means_code'])
			{
				$errors[$orderId][] = array(
					'orderId' => $orderId,
					'errorMsg' => $formText_PaymentMensCodeIsMissingOrInvalid_Ehf
				);
			}*/
			/*if(empty($v_data['payment_due_date']) || !validate_ehf_invoice_date_format($v_data['payment_due_date']))
			{
				$errors[$orderId][] = array(
					'orderId' => $orderId,
					'errorMsg' => $formText_PaymentDueDateIsMissingOrInvalid_Ehf
				);
			}*/
			// Hardcoded currenlty as BBAN
			/*if('' == $v_data['payment_bank_account_type'])
			{
				$errors[$orderId][] = array(
					'orderId' => $orderId,
					'errorMsg' => $formText_PaymentBankAccountTypeIsMissingOrInvalid_Ehf
				);
			}*/
			if('' == trim(preg_replace('#[^A-Za-z0-9]+#', '', $v_settings['companyaccount'])))
			{
				$errors[$orderId][] = array(
					'orderId' => $orderId,
					'errorMsg' => $formText_PaymentBankAccountIsMissingOrInvalid_Ehf
				);
			}
		}

		if ($collectingOrder['contactpersonId'] > 0) {
			$s_sql = "SELECT * FROM contactperson WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($collectingOrder['contactpersonId']));
			$contactPersonData = $o_query ? $o_query->row_array() : array();
			if($contactPersonData){
				$contactPersons[$collectingOrder['contactpersonId']] = $contactPersonData['name']." ".$contactPersonData['middlename']." ".$contactPersonData['lastname'];
			}
		}

		$s_order_reference = $collectingOrder['reference'];
		$s_order_delivery_date = ((!empty($collectingOrder['delivery_date']) && $collectingOrder['delivery_date'] != '0000-00-00') ? date('d.m.Y', strtotime($collectingOrder['delivery_date'])) : '');
		$s_order_delivery_address = trim(preg_replace('/\s+/', ' ', $collectingOrder['delivery_address_line_1'].' '.$collectingOrder['delivery_address_line_2'].' '.$collectingOrder['delivery_address_city'].' '.$collectingOrder['delivery_address_postal_code'].' '.$v_countries[$collectingOrder['delivery_address_country']]));

		if($collectingOrder['accountingProjectCode'] > 0){
			if(!in_array($collectingOrder['accountingProjectCode'], $projectCodes)){
				array_push($projectCodes, $collectingOrder['accountingProjectCode']);
			}
		}
		if($collectingOrder['department_for_accounting_code'] > 0){
			if(!in_array($collectingOrder['department_for_accounting_code'], $departmentCodes)){
				array_push($departmentCodes, $collectingOrder['department_for_accounting_code']);
			}
		}
		$v_order_priceTotal += $v_order['priceTotal'];
	}

	if($batchinvoicing_accountconfig['forceConnectCreditInvoice'] && $v_order_priceTotal < 0) {
		$invoiceCount = 0;
		if(!$invoiceCount) {
			$customer_errors[] = array(
				'errorMsg' => $formText_InvoiceIsNotChoosenForCreditInvoice_output
			);
		}
	}
	if($ownercompanyCheck){
		// Customer count & selected count
		$customersCount++;
		if (!count($errors) && !count($customer_errors)) {
			$customersSelected++;
		}
		else {
			$customersCountWithErrors++;
		}
	}
	$allowInvoice_error = 0;
	if($batchinvoicing_accountconfig['notAllowInvoiceIfNoExtcustomerId']){
		$s_sql = "SELECT * FROM customer_externalsystem_id WHERE ownercompany_id = ? AND customer_id = ?";
		$externalCustomerIdData = array();
		$o_query = $o_main->db->query($s_sql, array($v_customer['ownercompany_id'], $v_customer['id']));
		if($o_query && $o_query->num_rows()>0){
			$externalCustomerIdData = $o_query->row_array();
		}
        $externalCustomerId = intval($externalCustomerIdData['external_id']);
		if($externalCustomerId == 0){
			$allowInvoice_error = 1;
		}
	}
	?><div class="item-customer<?php if(count($real_ownercompany_filter) > 0 && !in_array($v_customer['ownercompany_id'], $real_ownercompany_filter)) echo ' hidden';?>" data-ownercompany="<?php echo $v_customer['ownercompany_id']?>">
		<div class="item-title">
			<div>
				<?php if(!count($errors) && !count($customer_errors) && !$allowInvoice_error): ?>
				<input type="checkbox" value="<?php echo $block_group_id;?>" name="customer[]" autocomplete="off" <?php if(isset($_GET['selectedAll']) && $_GET['selectedAll']){ echo 'checked';} else if(isset($_GET['selectedInvoices'])) { if(in_array($block_group_id,$selectedInvoicesArray)) echo 'checked'; } else { echo 'checked';}?>/>
				<?php endif; ?>
				<?php echo $v_customer['name']." ".$v_customer['middlename']." ".$v_customer['lastname'];?>
                <?php if ($activateMultiOwnerCompanies):
                	$s_sql = "SELECT * FROM ownercompany WHERE id = ?";
					$o_query = $o_main->db->query($s_sql, array($v_customer['ownercompany_id']));
					$ownerCompanyData = $o_query ? $o_query->row_array(): array();
                ?>
                    <div>
                        <small>
                            (<?php echo $formText_OwnerCompany_output; ?>: <?php echo $ownerCompanyData['name']; ?>)
                        </small>
                    </div>
                <?php endif; ?>
			</div>
			<?php if(count($projectCodes) > 0 || count($departmentCodes) > 0) { ?>
				<div class="out-projectcode">
					<?php echo $formText_ProjectCode_output;?>: <?php echo implode(',', $projectCodes); ?>
					<?php if(count($departmentCodes) > 0 && count($projectCodes) > 0) { ?>
						<br/>
					<?php } ?>
					<?php if(count($departmentCodes) > 0) {
						echo $formText_DepartmentCode_output;?>: <?php echo implode(',', $departmentCodes);
					} ?>
				</div>
			<?php } ?>
			<div class="out-ref"><?php echo $formText_YourContact_output?>: <?php echo join(', ', $contactPersons); ?> </div>
			<div class="out-address"><?php echo $s_address;?></div>
			<br clear="all">
		</div>
        <?php if(count($customer_errors)): ?>
        <div class="item-error">
            <div class="alert alert-danger"><?php echo $formText_CustomerHasError_output; ?>
                <ul style="margin:0; padding:0 15px;">
                    <?php foreach($customer_errors as $customer_error): ?>
                        <li><?php echo $customer_error['errorMsg']; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
        <?php if($allowInvoice_error): ?>
        <div class="item-error">
            <div class="alert alert-danger"><?php echo $formText_CustomerHasErrors_output; ?>
                <ul style="margin:0; padding:0 15px;">
                    <li><?php echo $formText_MissingExternalCustomerId_output; ?></li>
                </ul>
            </div>
        </div>
        <?php endif; ?>

		<?php if(count($errors)): ?>
		<div class="item-error">
			<div class="alert alert-danger"><?php echo $formText_CustomerHasOrderErrors_output; ?>
				<ul style="margin:0; padding:0 15px;">
					<?php foreach($errors as $orderErrorList): ?>
						<?php foreach($orderErrorList as $error): ?>
							<li><a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Orders&ID=".$error['orderId']."&includefile=edit&submodule=orders"; ?>"><?php echo $formText_Order_output; ?> #<?php echo $error['orderId']; ?></a> - <?php echo $error['errorMsg']; ?></li>
						<?php endforeach; ?>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<?php endif; ?>
		<div class="item-order">
			<?php if(!empty($s_order_reference)) { ?>
			<div><b><?php echo $formText_Reference;?></b>: <?php echo $s_order_reference;?></div>
			<?php } ?>
			<?php if(!empty($s_order_delivery_date)) { ?>
			<div><b><?php echo $formText_DeliveryDate;?></b>: <?php echo $s_order_delivery_date;?></div>
			<?php } ?>
			<?php if(!empty($s_order_delivery_address)) { ?>
			<div><b><?php echo $formText_DeliveryAddress;?></b>: <?php echo $s_order_delivery_address;?></div>
			<?php } ?>
			<?php
			$attachedFiles = json_decode($collectingOrder['files_attached_to_invoice'], true);
			if(count($attachedFiles) > 0){				?>

				<div><b><?php echo $formText_AttachedFiles;?></b>:</div>
				<?php
				foreach($attachedFiles as $file){
					$fileUrl = $extradomaindirroot.'/../'.$file[1][0].'?caID='.$_GET['caID'].'&table=customer_collectingorder&field=files_attached_to_invoice&ID='.$collectingOrder['id'];

					?>
						<div><a href="<?php echo $fileUrl?>" download><?php echo $file[0];?></a></div>
					<?php
				}
			}
			?>
			<?php
			$addAdminFee = false;
			if (!$isMultiCurrencyAccount) $currencyIdsDetected[] = 1;
			foreach ($currencyIdsDetected as $currentCurrencyId => $currentCurrencyVal): ?>

				<?php
				$currentCurrency = ($isMultiCurrencyAccount ? $currentCurrencyVal : $defaultAccountCurrency);
				$currentCurrencyIndexForArray = $currentCurrency ? $currentCurrency : 'EMPTY_CURRENCY';
				$currentCurrencyCode = ($isMultiCurrencyAccount ? $allCurrenciesCodeList[$currentCurrencyId] : $defaultAccountCurrencyCode);

				?>
				<table class="table table-condensed">
				<thead>
					<tr>
						<th>&nbsp;</th>
						<th><?php echo $formText_Text_Output;?></th>
						<th><?php echo $formText_Price_Output;?></th>
						<th><?php echo $formText_Amount_Output;?></th>
						<th><?php echo $formText_Discount_Output;?></th>
						<th><?php echo $formText_Vat_Output;?></th>
						<th>&nbsp;</th>
						<th class="text-right"><?php echo $formText_TotalPrice_Output;?></th>
					</tr>
				</thead>
				<tbody>
					<?php

					$l_sum = 0;
					$vatTotal = 0;
                    $dateValShow = '';
                    $dateExpireShow = '';
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

                    foreach($v_orders as $order){
                        $s_sql = "SELECT * FROM orders LEFT OUTER JOIN subscriptionmulti ON subscriptionmulti.id = orders.invoiceDateSettingFromSubscription WHERE orders.id = ".$order['id']." ORDER BY orders.invoiceDateSettingFromSubscription ASC";
                        $o_query = $o_main->db->query($s_sql);
                        if($o_query && $o_query->num_rows()>0){
                            $orderSingle = $o_query->row_array();
                            if($orderSingle){
                            	if($orderSingle['invoiceDateSettingFromSubscription'] > 0){
                            		$notOverrideDate = true;
                            		if(!in_array($block_group_id,$numberOfInvoicesFromSubscription) && $ownercompanyCheck) {
	                            		array_push($numberOfInvoicesFromSubscription, $block_group_id);
	                            		if (($key = array_search($block_group_id, $numberOfInvoicesFromCustomer)) !== false) {
										    unset($numberOfInvoicesFromCustomer[$key]);
										}
	                            	}
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
	                            		if(!in_array($block_group_id,$numberOfInvoicesFromCustomer) && $ownercompanyCheck) {
		                            		array_push($numberOfInvoicesFromCustomer, $block_group_id);
		                            	}
                            			$customerSetting = true;
										$dateValShow = date('d').".".date('m').".".date('Y');
		                            	if(isset($_GET["invoice_date"]) && $_GET["invoice_date"] != ""){
											$dateValShow = $_GET["invoice_date"];
										}
										$dateExpireShow = date("d.m.Y", strtotime("+".$credittimeDays." days", strtotime($dateValShow)));
										if(isset($_GET["due_date"]) && $_GET["due_date"] != "" && strtotime($_GET['due_date']) > strtotime($_GET['invoice_date'])){
											$dateExpireShow = $_GET["due_date"];
										}
									}
	                            }
                            }
                        }
                    }
                    if(count($v_orders) > 0){
	                    if($ownercompanyCheck){
		                    if($customerSetting){
			                    $dateInit = "1_";
			                    $totalDateArrayCustomer[$dateInit.$dateValShow."/".$dateExpireShow]++;
			                } else {
			                    $dateInit = "2_";
			                	$totalDateArraySubscription[$dateInit.$dateValShow."/".$dateExpireShow]++;
			                }
			            }
			        }
		            $dateValShowInit = $dateValShow;
		            $dateExpireShowInit = $dateExpireShow;

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
					$dateValShowNew = "";
					$dateExpireShowNew = "";
					if(isset($_SESSION[$block_group_id."_".$dateValShow."/".$dateExpireShow])){
                    	$dateKeyArray = explode("_", $_SESSION[$block_group_id."_".$dateValShow."/".$dateExpireShow]);
                    	$datePairArray = explode("/", $dateKeyArray[1]);
                    	if(count($datePairArray)== 2){
                    		$dateInvoiceNew = $datePairArray[0];
                    		$dateDueNew = $datePairArray[1];
                    		if($dateInvoiceNew != "" && $dateDueNew != ""){
                    			$dateValShowNew = $dateInvoiceNew;
                    			$dateExpireShowNew = $dateDueNew;
                    		}
                    	}
                    }

                    if ($dateValShow) {
                        echo '<span style="margin: 5px 5px 5px 0; display:inline-block;"><b>' . $formText_InvoiceDate . '</b>: ';
						if($dateValShowNew){
							echo  "<span class='old'>".$dateValShow . '</span>' .$dateValShowNew . '</span>';
						} else {
							echo  $dateValShow . '</span>';
						}
                    }
                    if ($dateExpireShow) {
                        echo '<span style="margin: 5px 5px 5px 0; display:inline-block;"><b>' . $formText_DueDate . '</b>: ';
						if($dateExpireShowNew){
							echo  "<span class='old'>".$dateExpireShow . '</span>' .$dateExpireShowNew . '</span>';
						} else {
							echo  $dateExpireShow . '</span>';
						}
                    }
					$key = $block_group_id."_".$dateValShow."/".$dateExpireShow;
					echo '<span class="glyphicon glyphicon-pencil fw_text_link_color editInvoiceDates" data-datepair="'.$key.'"></span>';
					if($dateValShowNew != "" && $dateExpireShowNew != ""){
					?>
					<a href="#" class="resetOverrideDatesPopup" data-datepair="<?php echo $key;?>"><?php echo $formText_ResetDates_output;?></a>
					<?php
					}
                    $prepaidSubscriptionCommonCostAdded = array();

					foreach($v_orders as $v_order)
					{
						if(!$v_order['adminFee']){
							$orderId = $v_order['id'];
							$vatData = getOrderVAT($o_main, $orderId);
							$orderHasError = (count($errors[$orderId]) ? true : false);
							// if(!$orderHasError){
							//  	$orderHasError = (count($customer_errors) ? true : false);
							// }
							if ($currentCurrencyId != $v_order['currencyId'] && $isMultiCurrencyAccount) continue;

							// Calc totals
							$vat = round($v_order['priceTotal'] * ($vatData['percentRate']/100), $decimalPlaces);
							if (!$orderHasError){
								$l_sum += $v_order['priceTotal'];
								$vatTotal += $vat;
							}
							$decimalNumber = getMaxDecimalAmount($v_order['amount']);
							?><tr <?php echo ($orderHasError ? 'class="orderHasError"' : ''); ?>>
								<td>
									<?php if(!count($errors) && !count($customer_errors)): ?>
									<input type="hidden" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][list][<?php echo $orderId; ?>][orderId]" value="<?php echo $orderId; ?>">
									<input type="hidden" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][list][<?php echo $orderId; ?>][articleName]" value="<?php echo $v_order['articleName']; ?>">
									<input type="hidden" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][list][<?php echo $orderId; ?>][vatPercentRate]" value="<?php echo $vatData['percentRate']; ?>">
									<input type="hidden" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][list][<?php echo $orderId; ?>][vatCode]" value="<?php echo $vatData['code']; ?>">
									<input type="hidden" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][list][<?php echo $orderId; ?>][bookAccountNr]" value="<?php echo $vatData['bookAccountNr']; ?>">
									<input type="hidden" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][list][<?php echo $orderId; ?>][pricePerPiece]" value="<?php echo $v_order['pricePerPiece']; ?>">
									<input type="hidden" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][list][<?php echo $orderId; ?>][amount]" value="<?php echo $v_order['amount']; ?>">
									<input type="hidden" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][list][<?php echo $orderId; ?>][discountPercent]" value="<?php echo $v_order['discountPercent']; ?>">
									<input type="hidden" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][list][<?php echo $orderId; ?>][priceTotal]" value="<?php echo $v_order['priceTotal']; ?>">
									<input type="hidden" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][list][<?php echo $orderId; ?>][vat]" value="<?php echo $vat; ?>">
									<input type="hidden" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][list][<?php echo $orderId; ?>][gross]" value="<?php echo $v_order['priceTotal'] + $vat; ?>">
									<input type="checkbox" value="<?php echo $v_order['id'];?>" name="order_number[]" <?php echo (!$orderHasError ? 'checked' : ''); ?> data-total="<?php echo $v_order['priceTotal']; ?>" data-vat="<?php echo $vat; ?>" style="display: none;" />
									<?php endif; ?>
								</td>
								<td><?php echo $v_order['articleName']; ?></td>
								<td><?php echo number_format(floatval($v_order['pricePerPiece']),2,',',' '); ?></td>
								<td><?php echo number_format(floatval($v_order['amount']),$decimalNumber,',',' '); ?></td>
								<td><?php echo number_format(floatval($v_order['discountPercent']),2,',',' '); ?>%</td>
								<td><?php echo number_format($vatData['percentRate'],2,',',' '); ?>%</td>
								<td>
									<?php
									if(intval($v_order['periodization']) > 0) {
										echo '<span class="periodizationLabel">P';
										if(intval($v_order['periodization']) == 2){
											echo '<div class="periodizationHover">'.$formText_DivideOnDays_outputper.'<br/>'.$formText_DateFrom_output.': '.date("d.m.Y", strtotime($v_order['dateFrom'])).'<br/>'.$formText_DateTo_output.': '.date("d.m.Y", strtotime($v_order['dateTo'])).'</div>';
										}
										if(intval($v_order['periodization']) == 1){
											$monthFrom = date("m.Y", strtotime($v_order['dateFrom']));
											$monthTo = date("m.Y", strtotime($v_order['dateTo']));
											if($monthFrom == $monthTo) {
												echo '<div class="periodizationHover">'.$formText_DivideOnMonths_outputper.'<br/>'.$formText_Month_output.': '.$monthFrom.'</div>';
											} else {
												echo '<div class="periodizationHover">'.$formText_DivideOnMonths_outputper.'<br/>'.$formText_MonthFrom_output.': '.$monthFrom.'<br/>'.$formText_MonthTo_output.': '.$monthTo.'</div>';
											}
										}
										echo '</span>';
									}
									?>
								</td>
								<td class="text-right"><?php echo number_format(floatval($v_order['priceTotal']),2,',',' '); ?></td>
							</tr><?php

							$totalSumInclVat = $vatTotal + $l_sum;
							$l_sum = round($l_sum, $decimalPlaces);
							$totalSumInclVat = round($totalSumInclVat, $decimalPlaces);
							$vatTotal = $totalSumInclVat - $l_sum;
						}
					}
					if($addAdminFee){
						$addAdminFee = false;
					} else {
						$addAdminFeeAutomatically = intval($ownercompanyAccountconfig['addAdminFeeAutomatically']);
						$overrideAdminFeeDefault = intval($v_customer['overrideAdminFeeDefault']);

						if($addAdminFeeAutomatically > 0){
							$doOverride = true;
							if($addAdminFeeAutomatically == 1){
								$addAdminFee = true;
							} else if($addAdminFeeAutomatically == 2 && intval($v_customer['invoiceBy']) == 0){
								$addAdminFee = true;
							} else if($addAdminFeeAutomatically == 3){
								if($overrideAdminFeeDefault == 2 || ($overrideAdminFeeDefault == 3 && intval($v_customer['invoiceBy']) == 0)) {
									$addAdminFee = true;
								} else {
									$addAdminFee = false;
								}
								$doOverride = false;
							}
							if($doOverride){
								if($overrideAdminFeeDefault > 0) {
									if($overrideAdminFeeDefault == 1){
										$addAdminFee = false;
									} else if($overrideAdminFeeDefault == 2){
										$addAdminFee = true;
									} else if($overrideAdminFeeDefault == 3 && intval($v_customer['invoiceBy']) == 0){
										$addAdminFee = true;
									} else {
										$addAdminFee = false;
									}
								}
							}
						}
					}
					if($addAdminFee){
						$s_sql = "SELECT * FROM article WHERE id = ?";
						$o_query = $o_main->db->query($s_sql, array($ownercompanyAccountconfig['chooseArticleForAdminFee']));
						$adminFeeArticle = $o_query ? $o_query->row_array() : array();
						if($adminFeeArticle){
							$orderId = "adminFee-".$v_customer['id'];
				            $taxFreeSale = $v_customer['taxFreeSale'];

				            if($taxFreeSale && !$adminFeeArticle['forceVat']) {
                                $vatCode = $article_accountconfig['vatcode_default_when_without_vat'];
                                $bookaccountNr = $adminFeeArticle['SalesAccountWithoutVat'];
                            } else {
                                $vatCode = $adminFeeArticle['VatCodeWithVat'];
                                $bookaccountNr = $adminFeeArticle['SalesAccountWithVat'];
                            }

                            if($vatCode == ""){
                                if($taxFreeSale && !$adminFeeArticle['forceVat']) {
                                    $vatCode = $article_accountconfig['vatcode_default_when_without_vat'];
                                } else {
                                    $vatCode = $article_accountconfig['defaultVatCodeForArticle'];
                                }
                            }
                            if($bookaccountNr == ""){
                                if($taxFreeSale && !$adminFeeArticle['forceVat']) {
                                    $bookaccountNr = $article_accountconfig['defaultSalesAccountWithoutVat'];
                                } else {
                                    $bookaccountNr = $article_accountconfig['defaultSalesAccountWithVat'];
                                }
                            }

                            $s_sql = "SELECT * FROM vatcode WHERE vatCode = ?";
                            $o_query = $o_main->db->query($s_sql, array($vatCode));
                            $vatItem = $o_query ? $o_query->row_array() : array();
                            $vatPercent = $vatItem['percentRate'];

				            $adminFeeTotal = 0;
				            $adminFeeTotal = $adminFeeArticle['price'];
				            $vat = round($adminFeeTotal * ($vatPercent/100), $decimalPlaces);

							$s_sql = "SELECT * FROM orders WHERE articleNumber = ? AND articleName = ? AND pricePerPiece = ? AND bookaccountNr = ? AND vatCode = ? AND collectingorderId = ? AND adminFee = 1";
							$o_query = $o_main->db->query($s_sql, array($adminFeeArticle['id'], $adminFeeArticle['name'], $adminFeeArticle['price'], $bookaccountNr, $vatCode, $v_customer['collectingorderId']));
							$adminFeeOrder = $o_query ? $o_query->result_array() : array();
							if(!$adminFeeOrder){
					           	$l_sum += $adminFeeTotal;
								$vatTotal += $vat;
								$decimalNumber = getMaxDecimalAmount("1.00");
								?>
								<tr <?php echo ($orderHasError ? 'class="orderHasError"' : ''); ?>>
									<td>
										<?php if(!count($errors)): ?>
											<input type="hidden" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][list][<?php echo $orderId; ?>][orderId]" value="<?php echo $orderId; ?>">
											<input type="hidden" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][list][<?php echo $orderId; ?>][articleName]" value="<?php echo $adminFeeArticle['name']; ?>">
											<input type="hidden" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][list][<?php echo $orderId; ?>][articleNumber]" value="<?php echo $adminFeeArticle['id']; ?>">
											<input type="hidden" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][list][<?php echo $orderId; ?>][vatPercentRate]" value="<?php echo $vatPercent; ?>">
											<input type="hidden" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][list][<?php echo $orderId; ?>][vatCode]" value="<?php echo $vatCode; ?>">
											<input type="hidden" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][list][<?php echo $orderId; ?>][bookAccountNr]" value="<?php echo $bookaccountNr; ?>">
											<input type="hidden" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][list][<?php echo $orderId; ?>][pricePerPiece]" value="<?php echo $adminFeeArticle['price']; ?>">
											<input type="hidden" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][list][<?php echo $orderId; ?>][amount]" value="<?php echo 1; ?>">
											<input type="hidden" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][list][<?php echo $orderId; ?>][discountPercent]" value="<?php echo 0; ?>">
											<input type="hidden" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][list][<?php echo $orderId; ?>][priceTotal]" value="<?php echo $adminFeeTotal; ?>">
											<input type="hidden" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][list][<?php echo $orderId; ?>][vat]" value="<?php echo $vat; ?>">
											<input type="hidden" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][list][<?php echo $orderId; ?>][gross]" value="<?php echo $adminFeeTotal + $vat; ?>">
											<input type="checkbox" value="<?php echo $orderId;?>" name="order_number[]" style="display: none;" checked data-total="<?php echo $adminFeeTotal; ?>" data-vat="<?php echo $vat; ?>" />
										<?php endif; ?>
									</td>
									<td><?php echo $adminFeeArticle['name']; ?></td>
									<td><?php echo number_format(floatval($adminFeeArticle['price']),2,',',' '); ?></td>
									<td><?php echo number_format(floatval("1.00"),$decimalNumber,',',' '); ?></td>
									<td><?php echo round(0); ?>%</td>
									<td><?php echo number_format($vatPercent,2,',',' '); ?>%</td>
									<td><?php ?></td>
									<td class="text-right"><?php echo number_format(floatval($adminFeeTotal),2,',',' '); ?></td>
								</tr>
								<?php

								$totalSumInclVat = $vatTotal + $l_sum;
								$l_sum = round($l_sum, $decimalPlaces);
								$totalSumInclVat = round($totalSumInclVat, $decimalPlaces);
								$vatTotal = $totalSumInclVat - $l_sum;
							}
						}
					}
					?>
					<tr class="date_different date_<?php echo md5($dateInit.$dateValShow."/".$dateExpireShow);?> date_<?php echo md5($original_block_group_id);?>" data-key="<?php echo md5($dateInit.$dateValShowInit."/".$dateExpireShowInit);?>"  data-key2="<?php echo md5($original_block_group_id);?>">
						<td colspan="8" class="item-totals text-right">
							<?php if(!count($errors)): ?>
							<span class="spacer"><?php echo $formText_SumWithoutVat_Output.' '. $currentCurrency.': ';?><span class="total-sum"><?php echo number_format(floatval($l_sum),$decimalPlaces,',',' ');?></span></span>
							<span class="spacer"><?php echo $formText_Vat_Output.' '. $currentCurrency.': ';?><span class="total-vat"><?php echo number_format(floatval($vatTotal),$decimalPlaces,',',' ');?></span></span>
							<span class="spacer"><?php echo $formText_Total_Output.' '. $currentCurrency.': ';?><span class="total-total"><?php echo number_format(floatval($totalSumInclVat),$decimalPlaces,',',' ');?></span></span>
							<input type="hidden" class="total-collectingorder" value="<?php echo $v_customer['collectingorderId'];?>" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][collectingorderId]" />

							<input type="hidden" class="total-sum-hidden" value="<?php echo $l_sum; ?>" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][totals][totalSum]" />
							<input type="hidden" class="total-vat-hidden" value="<?php echo $vatTotal; ?>" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][totals][totalVat]" />
							<input type="hidden" class="total-total-hidden" value="<?php echo $vatTotal + $l_sum; ?>" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][totals][total]" />
							<input type="hidden" value="<?php echo $currentCurrencyCode; ?>" name="orders[<?php echo $block_group_id; ?>][<?php echo $currentCurrencyIndexForArray; ?>][currencyCode]" />
							<?php endif; ?>
							<!-- <input type="hidden" value="<?php echo $_POST['invoiceDate'];  ?>" name="invoiceDate" /> -->
						</td>
					</tr>
				</tbody>
				</table>
			<?php endforeach; ?>
			<div><i><?php
			if($v_customer["invoiceBy"] == 2)
			{
				echo $formText_InvoiceWillBeHandledByEhfService_Output;
			} else if($v_customer["invoiceBy"] == 1)
			{
				echo $formText_InvoiceWillBeSentToEmail_Output.": ".$v_customer["invoiceEmail"];
			} else {
				echo $formText_InvoiceWillBePrinted_Output;
			}
		   ?></i></div>
		</div>
	</div><?php
}
$listBuffer = ob_get_clean();
?>
<div class="p_pageDetails">
	<div class="spf_check_warning"></div>
	<?php
	$s_sql = "SELECT * FROM batch_invoicing WHERE printing_handled = 0 OR printing_handled is null ORDER BY created DESC";
	$o_query = $o_main->db->query($s_sql);
	$batches = $o_query ? $o_query->result_array() : array();
	$finishedBatchHasPrint = false;
	foreach($batches as $batch){
		$s_sql = "SELECT * FROM invoice WHERE batch_id = ? AND for_print = 1";
		$o_query = $o_main->db->query($s_sql, array($batch['id']));
		$v_rows = $o_query ? $o_query->result_array() : array();
		if(count($v_rows) > 0){
			$finishedBatchHasPrint = true;
		} else {
			$s_sql = "UPDATE batch_invoicing SET printing_handled = 1, updated = NOW(), updatedBy = ? WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($variables->loggID, $batch['id']));
		}
	}
	if($finishedBatchHasPrint){
 	?>
		<div class="p_pageDetailsTitle"><?php echo $formText_InvoiceToBePrinted_output;?></div>
		<div class="p_contentBlock">
			<?php
			foreach($batches as $batch){
				$s_sql = "SELECT * FROM invoice WHERE batch_id = ? AND for_print = 1";
				$o_query = $o_main->db->query($s_sql, array($batch['id']));
				$v_rows = $o_query ? $o_query->result_array() : array();
				if(count($v_rows) > 0){
					$createdBy = $batch['createdBy'];
					if($batchinvoicing_accountconfig['printing_invoice_notification_email'] != ""){
						$createdBy = $batchinvoicing_accountconfig['printing_invoice_notification_email'];
					}
					?>
					<div>
						<a href="<?php echo $extradir."/output/ajax.download.php?accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&ID=".$batch["id"];?>" class="downlodInvoice">
							<?php
							echo count($v_rows)." ".$formText_InvoicesNeedsToBePrinted_output;
							?>
						</a>
						<?php echo date("d.m.Y", strtotime($batch['created']))." ".$createdBy;?>
						<a href="#" class="markAsHandled" data-batch-id="<?php echo $batch['id'];?>"><?php echo $formText_MarkAsHandled_output;?></a>
					</div>
					<?php
				}
			}
			?>
		</div>
	<?php } ?>
	<div class="p_pageDetailsTitle"><?php echo $formText_SummaryOfInvoicesChoosenForInvoicing_output;?></div>
	<div class="p_contentBlock">
		<div class="invoiceSendersTitle"><span class="invoiceSendersCount"><?php echo count($ownercompanies);?></span> <?php echo $formText_InvoiceSenders_output;?></div>
		<div class="invoiceSendersBody">
			<table class="table table-bordered" style="margin-bottom: 0px;">
				<tr>
					<th width="45%"><?php echo $formText_Company_output;?></th>
					<th width="20%"><?php echo $formText_InvoicesChoosen_output;?></th>
					<th width="20%"><?php echo $formText_TotalAmount_output;?></th>
					<?php if(count($ownercompanies) > 1){ ?>
					<th width="15%"></th>
					<?php } ?>
				</tr>
				<?php
				foreach($ownercompanies as $ownercompany) {
					$real_ownercompany_filter_without_current = array();
					foreach($real_ownercompany_filter as $single_item) {
						if($single_item != $ownercompany['id']){
							array_push($real_ownercompany_filter_without_current, $single_item);
						}
					}
					?>
					<tr class="<?php  if(in_array($ownercompany['id'], $real_ownercompany_filter)) { echo 'active'; }?>">
						<td width="45%"><?php echo $ownercompany['name'];?></td>
						<td width="20%"><span class="selectedAmount" data-ownercompanyid="<?php echo $ownercompany['id'];?>"></span> <?php echo $formText_Of_output;?> <span class="totalAmount" data-ownercompanyid="<?php echo $ownercompany['id'];?>"></span></td>
						<td width="20%"><div class="totalMoneyAmount" data-ownercompanyid="<?php echo $ownercompany['id'];?>"></div></td>
						<?php if(count($ownercompanies) > 1){ ?>
						<td width="15%">
							<?php if(in_array($ownercompany['id'], $real_ownercompany_filter)) {?>
								<a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&search=".$_GET['search']."&department_filter=".$_GET['department_filter']."&project_filter=".$_GET['project_filter']."&ownercompany=".implode(",", $real_ownercompany_filter_without_current);?>"><?php echo $formText_RemoveFromFilter_output;?></a>
							<?php
							} else { ?>
								<a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&search=".$_GET['search']."&department_filter=".$_GET['department_filter']."&project_filter=".$_GET['project_filter']."&ownercompany=".implode(",", $real_ownercompany_filter).",".$ownercompany['id'];?>"><?php echo $formText_AddToFilter_output;?></a>
							<?php } ?>
						</td>
						<?php } ?>
					</tr>
					<?php
				}
				?>
			</table>
		</div>
	</div>
	<div class="p_contentBlock">
		<div class="invoiceSendersTitle"><span class="invoicesFromCustomer"><?php echo count($numberOfInvoicesFromCustomer);?></span> <?php echo $formText_InvoicesAreUsingDateAndDueDateSettingsFromCustomer_output;?></div>
		<div class="invoiceSendersBody">
			<table class="table table-bordered table_1" style="margin-top: 15px;">
				<tr>
					<th width="25%"><?php echo $formText_InvoiceDate_output;?></th>
					<th width="25%"><?php echo $formText_DueDate_output;?></th>
					<th width="20%"><?php echo $formText_InvoiceCount_output;?></th>
					<th width="17%"></th>
					<th width="13%"></th>
				</tr>
			<?php

			foreach($totalDateArrayCustomer as $key=>$value){
				$keyArray = explode("_", $key);
				$dateArray = explode("/", $keyArray[1], 2);
				$dateInvoice = $dateArray[0];
				$dateDue = $dateArray[1];
				$totalInvoices = $value;

				$dateInvoiceNew = "";
				$dateDueNew = "";
				if(isset($_SESSION[$key])){
					$datePair = $_SESSION[$key];
					$keyArray = explode("_", $datePair);
					$datePairArray = explode("/", $keyArray[1], 2);
					$dateInvoiceNew = $datePairArray[0];
					$dateDueNew = $datePairArray[1];
				}
				?>
				<tr class="summaryRowInfo <?php  if($_GET['filter'] == $key) { echo 'active'; }?>" data-key="<?php echo md5($key);?>">
					<td width="25%"><span <?php if($dateInvoiceNew != "") echo 'class="old"'; ?>><?php echo $dateInvoice;?></span><?php if($dateInvoiceNew != "") echo $dateInvoiceNew;?></td>
					<td width="25%"><span <?php if($dateDueNew != "") echo 'class="old"'; ?>><?php echo $dateDue;?></span><?php if($dateDueNew != "") echo $dateDueNew;?></td>
					<td width="20%"><span class="summaryRowInfoSelected"></span> <?php echo $formText_Of_output; ?> <?php echo $totalInvoices;?></td>
					<td width="17%">
						<?php
						if($dateInvoiceNew != "" && $dateDueNew != ""){
						?>
						<a href="#" class="resetOverrideDatesPopup" data-datepair="<?php echo $key;?>"><?php echo $formText_ResetDates_output;?></a>
						<?php
						} else {
						?>
						<a href="#" class="overrideDatesPopup" data-datepair="<?php echo $key;?>"><?php echo $formText_OverrideDates_output;?></a>
						<?php } ?>
					</td>
					<td width="13%">
						<?php  if($_GET['filter'] == $key) { ?>
						<a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&search=".$_GET['search']."&department_filter=".$_GET['department_filter']."&project_filter=".$_GET['project_filter']."&ownercompany=".$_GET['ownercompany']?>"><?php echo $formText_RemoveFilter_output;?></a>
						<?php } else { ?>
						<a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&search=".$_GET['search']."&department_filter=".$_GET['department_filter']."&project_filter=".$_GET['project_filter']."&ownercompany=".$_GET['ownercompany']."&filter=".$key;?>"><?php echo $formText_Filter_output;?></a>
						<?php } ?>
					</td>
				</tr>
				<?php
			}
			?>
			</table>
		</div>
		<div class="invoiceSendersTitle"><span class="invoicesFromCustomer"><?php echo count($numberOfInvoicesFromSubscription);?></span> <?php echo $formText_InvoicesAreUsingDateAndDueDateSettingsFromSubscriptions_output;?></div>
		<div class="invoiceSendersBody">
			<table class="table table-bordered table_2" style="margin-top: 15px; margin-bottom: 0px;">
				<tr>
					<th width="25%"><?php echo $formText_InvoiceDate_output;?></th>
					<th width="25%"><?php echo $formText_DueDate_output;?></th>
					<th width="20%"><?php echo $formText_InvoiceCount_output;?></th>
					<th width="17%"></th>
					<th width="13%"></th>
				</tr>
			<?php

			foreach($totalDateArraySubscription as $key=>$value){
				$keyArray = explode("_", $key);
				$dateArray = explode("/", $keyArray[1], 2);
				$dateInvoice = $dateArray[0];
				$dateDue = $dateArray[1];
				$totalInvoices = $value;

				$dateInvoiceNew = "";
				$dateDueNew = "";
				if(isset($_SESSION[$key])){
					$datePair = $_SESSION[$key];
					$keyArray = explode("_", $datePair);
					$datePairArray = explode("/", $keyArray[1], 2);
					$dateInvoiceNew = $datePairArray[0];
					$dateDueNew = $datePairArray[1];
				}
				?>
				<tr class="summaryRowInfo <?php  if($_GET['filter'] == $key) { echo 'active'; }?>" data-key="<?php echo md5($key);?>">
					<td width="25%"><span <?php if($dateInvoiceNew != "") echo 'class="old"'; ?>><?php echo $dateInvoice;?></span><?php if($dateInvoiceNew != "") echo $dateInvoiceNew;?></td>
					<td width="25%"><span <?php if($dateDueNew != "") echo 'class="old"'; ?>><?php echo $dateDue;?></span><?php if($dateDueNew != "") echo $dateDueNew;?></td>
					<td width="20%"><span class="summaryRowInfoSelected"></span> <?php echo $formText_Of_output; ?> <?php echo $totalInvoices;?></td>
					<td width="17%">
						<?php
						if($dateInvoiceNew != "" && $dateDueNew != ""){
						?>
						<a href="#" class="resetOverrideDatesPopup" data-datepair="<?php echo $key;?>"><?php echo $formText_ResetDates_output;?></a>
						<?php
						} else {
						?>
						<a href="#" class="overrideDatesPopup" data-datepair="<?php echo $key;?>"><?php echo $formText_OverrideDates_output;?></a>
						<?php } ?></td>
					<td width="13%">
						<?php  if($_GET['filter'] == $key) { ?>
						<a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&search=".$_GET['search']."&department_filter=".$_GET['department_filter']."&project_filter=".$_GET['project_filter']."&ownercompany=".$_GET['ownercompany'];?>"><?php echo $formText_RemoveFilter_output;?></a>
						<?php } else { ?>
						<a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&search=".$_GET['search']."&department_filter=".$_GET['department_filter']."&project_filter=".$_GET['project_filter']."&ownercompany=".$_GET['ownercompany']."&filter=".$key;?>"><?php echo $formText_Filter_output;?></a>
						<?php } ?>
					</td>
				</tr>
				<?php
			}
			?>
			</table>
		</div>
	</div>
	<?php if($batchinvoicing_accountconfig['activate_filter_by_project'] || $batchinvoicing_accountconfig['activate_filter_by_department']) { ?>
		<div class="p_contentBlock">
			<?php if($batchinvoicing_accountconfig['activate_filter_by_department']) {?>
				<div class="departmentFilterWrapper" style="text-align: right;">
					<?php echo $formText_DepartmentFilter_output; ?>
					<span class="selectDiv selected">
						<span class="selectDivWrapper">
							<select name="defaultSelect" class="departmentFilter" autocomplete="off">
								<option value=""><?php echo $formText_All_output;?></option>
								<?php
									$s_sql = "SELECT * FROM departmentforaccounting
									WHERE departmentforaccounting.content_status < 2 ORDER BY departmentforaccounting.departmentnumber ASC";

									$o_query = $o_main->db->query($s_sql);
									$departments = ($o_query ? $o_query->result_array() : array());
									foreach($departments as $department) {
										?>
										<option value="<?php echo $department['departmentnumber'];?>" <?php if($department_id == $department['departmentnumber']) echo 'selected';?>><?php echo $department['name']?></option>
										<?php
									}
								?>
							</select>
						</span>
						<span class="arrowDown"></span>
					</span>
				</div>
				<script type="text/javascript">
					$('.departmentFilter').on('change', function(e) {
						fw_load_ajax("<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folder=output&search=".$_GET['search']."&folderfile=output&ownercompany=".$_GET['ownercompany']."&filter=".$_GET['filter']."&project_filter=".$_GET['project_filter'];?>&department_filter="+$(this).val(), '', true);
					});
				</script>
			<?php } ?>
			<?php if($batchinvoicing_accountconfig['activate_filter_by_project']) {?>
				<div class="projectFilterWrapper" style="text-align: right;">
					<?php echo $formText_ProjectFilter_output; ?>
					<span class="selectDiv selected">
						<span class="selectDivWrapper">
							<select name="defaultSelect" class="projectFilter" autocomplete="off">
								<option value=""><?php echo $formText_All_output;?></option>
								<?php
								function getProjects($o_main, $parentNumber = 0) {
									$projects = array();

									if ($parentNumber) {
										$o_main->db->order_by('projectnumber', 'ASC');
										$o_query = $o_main->db->get_where('projectforaccounting', array('parentNumber' => $parentNumber));
									} else {
										$o_query = $o_main->db->query("SELECT * FROM projectforaccounting WHERE parentNumber IS NULL OR parentNumber = 0 ORDER BY projectnumber");
									}

									if ($o_query && $o_query->num_rows()) {
										foreach ($o_query->result_array() as $row) {
											array_push($projects, array(
												'id' => $row['id'],
												'name' => $row['name'],
												'number' => $row['projectnumber'],
												'parentNumber' => $row['parentNumber'] ? $row['parentNumber'] : 0,
												'children' => getProjects($o_main, $row['projectnumber'])
											));
										}
									}

									return $projects;
								}

								function getProjectsOptionsListHtml($projects, $level, $accountingproject_code) {
									ob_start(); ?>

									<?php foreach ($projects as $project): ?>
										<option value="<?php echo $project['number']; ?>" <?php echo $project['number'] == $accountingproject_code ? 'selected="selected"' : ''; ?>>
											<?php
											$identer = '';
											for($i = 0; $i < $level; $i++) { $identer .= '-'; }
											echo $identer;
											?>
											<?php echo $project['number']; ?> <?php echo $project['name']; ?>
										</option>

										<?php if (count($project['children'])): ?>
											<?php echo getProjectsOptionsListHtml($project['children'], $level+1, $accountingproject_code); ?>
										<?php endif; ?>
									<?php endforeach; ?>

									<?php return ob_get_clean();
								}

								$projects = getProjects($o_main);
								echo getProjectsOptionsListHtml($projects, 0, $project_id);
								?>
							</select>
						</span>
						<span class="arrowDown"></span>
					</span>
				</div>
				<script type="text/javascript">
					$('.projectFilter').on('change', function(e) {
						fw_load_ajax("<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folder=output&folderfile=output&search=".$_GET['search']."&ownercompany=".$_GET['ownercompany']."&filter=".$_GET['filter']."&department_filter=".$_GET['department_filter'];?>&project_filter="+$(this).val(), '', true);
					});
				</script>
			<?php } ?>
			<div class="clear"></div>
		</div>
	<?php } ?>
	<div class="p_contentBlock">
		<div class="employeeSearch">
			<span class="glyphicon glyphicon-search"></span>
			<input type="text" placeholder="<?php echo $formText_Customer_output;?>" value="<?php echo $searchedCustomer['customerName']?>" class="employeeSearchInput" autocomplete="off"/>
			<span class="glyphicon glyphicon-triangle-right"></span>
			<div class="employeeSearchSuggestions allowScroll"></div>

			<?php if($search != "") { ?>
		        <div class="filteredCountRow">
		            <span class="selectionCount"><?php echo count($v_orders);?></span> <?php echo $formText_InSelection_output;?>
		            <div class="resetSelection fw_text_link_color"><?php echo $formText_Reset_output;?></div>
		        </div>
			<?php } ?>
		</div>
		<div class="clear"></div>
	</div>

	<div class="p_pageDetailsTitle"><?php echo $formText_Invoices_output;?></div>
	<div class="p_contentBlock">
		<div id="out-customer-list">

			<?php if ($customersCountWithErrors > 0): ?>
				<div id="out-error-box">
					<div class="alert alert-danger"><?php echo $customersCountWithErrors. ' ' . $formText_CustomerInvoicesHasErrors_output; ?></div>
				</div>
			<?php endif; ?>
			<div class="out-select-all">
				<?php if ($customersCount > 0): ?>
					<input type="checkbox" id="selectDeselectAll"  autocomplete="off" <?php if(isset($_GET['selectedAll'])) { if($_GET['selectedAll']) echo 'checked';} else { if (($customersCount - $customersCountWithErrors) == $customersSelected) echo 'checked="checked"'; } ?>> <?php echo $formText_SelectAll_output; ?>
				<?php endif; ?>
			</div>
			<div class="out-dynamic">
				<?php echo $listBuffer; ?>
			</div>
			<div id="out-hook-error"></div>

			<div class="attached_file_line">
				<div class="lineTitle">
					<?php echo $formText_AttachedFiles_output; ?>
				</div>
				<div class="lineInput">
					<?php
					$fwaFileuploadConfig = $fwaFileuploadConfigs[0];
					require __DIR__ . '/includes/fileupload_popup/output.php';
					?>
				</div>
				<div class="clear"></div>
			</div>
			<div class="out-buttons">
				<button id="out-invoice-create" class="btn btn-default"><?php echo $formText_CreateInvoices_Output;?></button>
		        <span class="totalCost"><?php echo $formText_TotalCost_output;?>: <span class="totalCostNumber"></span></span>
		        <span class="totalSelectedInvoices"><?php echo $formText_InvoicesSelected_output;?>: <span class="totalInvoicesSelected"></span></span>

				<a class="btn btn-default pull-right optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=history";?>"><?php echo $formText_History_Output;?></a>
			</div>
		</div>

		<div id="out-process-progress">
			<div class="progress-title"><span class="processed">0</span> / <span class="total">0</span></div>
			<div class="progress-bar2">
				<div class="progress-bar2-fill"></div>
			</div>
		</div>
	</div>
</div>
<div id="popupeditbox" class="popupeditbox">
	<span class="button b-close"><span>X</span></span>
	<div id="popupeditboxcontent"></div>
</div>
<style>
.attached_file_line {
	position: relative;
}
.attached_file_line .lineTitle {
	font-weight: 700;
	padding: 5px 0;
}
.attached_file_line .lineInput {

}
.attached_file_line .fwaFileuploadAddFiles  {
	position: absolute;
	top: 0px;
	left: 110px;
}
.projectFilterWrapper,
.departmentFilterWrapper {
	float: right;
	margin: 5px 0px 5px 10px;
}
.p_contentBlock .employeeSearch {
    float: right;
    position: relative;
    margin-bottom: 0;
}
.p_contentBlock .employeeSearch .employeeSearchSuggestions {
    display: none;
    background: #fff;
    position: absolute;
    width: 100%;
    max-height: 200px;
    overflow: auto;
    z-index: 2;
    border: 1px solid #dedede;
    border-top: 0;
}
.p_contentBlock .employeeSearch .employeeSearchSuggestions table {
    margin-bottom: 0;
}
#p_container .p_contentBlock .employeeSearch .employeeSearchSuggestions td {
    padding: 5px 10px;
}

.p_contentBlock .employeeSearch .glyphicon-triangle-right {
    position: absolute;
    top: 7px;
    right: 4px;
    color: #048fcf;
}
.p_contentBlock .employeeSearch .glyphicon-search {
    position: absolute;
    top: 7px;
    left: 6px;
    color: #048fcf;
}
.p_contentBlock .employeeSearchInput {
    width: 250px;
    border: 1px solid #dedede;
    padding: 3px 15px 3px 25px;
}
.p_contentBlock .employeeSearchInputBefore {
    width: 150px;
    border: 1px solid #dedede;
    padding: 3px 10px 3px 10px;
}
.p_contentBlock .employeeSearchBtn {
    background: #0093e7;
    border-radius: 5px;
    margin-left: 3px;
    color: #fff;
    padding: 5px 15px;
    cursor: pointer;
    border: 0;
}

.filteredCountRow .resetSelection {
	float: right;
	cursor: pointer;
}
.spf_check_warning {
	display: none;
}
</style>
<script type="text/javascript">

$(".resetSelection").on('click', function(e) {
	e.preventDefault();
	var data = {
		filter: '<?php echo $_GET['filter'];?>',
		ownercompany: '<?php echo $_GET['ownercompany'];?>',
		department_filter: '<?php echo $_GET['department_filter'];?>',
		project_filter: '<?php echo $_GET['project_filter'];?>'
	}
	loadView("list", data);
});

var loadingCustomer = false;
var $input = $('.employeeSearchInput');
var customer_search_value;
$input.on('focusin', function () {
	searchCustomerSuggestions();
	$("#p_container").unbind("click").bind("click", function (ev) {
		if($(ev.target).parents(".employeeSearch").length == 0){
			$(".employeeSearchSuggestions").hide();
		}
	});
})
//on keyup, start the countdown
$input.on('keyup', function () {
	searchCustomerSuggestions();
});
//on keydown, clear the countdown
$input.on('keydown', function () {
	searchCustomerSuggestions();
});
function searchCustomerSuggestions (){
	if(!loadingCustomer) {
		if(customer_search_value != $(".employeeSearchInput").val()) {
			loadingCustomer = true;
			customer_search_value = $(".employeeSearchInput").val();
			$('.employeeSearch .employeeSearchSuggestions').html('<div class="article-loading lds-ring"><div></div><div></div><div></div><div></div></div>').show();
			var _data = { fwajax: 1, fw_nocss: 1, search: customer_search_value};
			$.ajax({
				cache: false,
				type: 'POST',
				dataType: 'json',
				url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_customers_suggestions";?>',
				data: _data,
				success: function(obj){
					loadingCustomer = false;
					$('.employeeSearch .employeeSearchSuggestions').html('');
					$('.employeeSearch .employeeSearchSuggestions').html(obj.html).show();
					searchCustomerSuggestions();
				}
			}).fail(function(){
				loadingCustomer = false;
			})
		}
	}
}
window.onbeforeunload = function (e) {
	if(fw_click_instance){
	    e = e || window.event;
	    // For IE and Firefox prior to version 4
	    if (e) {
	        e.returnValue = 'Sure?';
	    }
	    // For Safari
	    return 'Sure?';
	}
};

function loadView(includeFile, data) {

    // includeFile check
    if (typeof(includeFile) !== 'string') return;
    // data object check
    if (typeof(data) !== 'object') var data = {};
    // Url params
    var urlParams = $.param(data);

    // Load view
    fw_load_ajax("<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folder=output&folderfile=output";?>&inc_obj=" + includeFile + '&' + urlParams, '', true);

}
var out_popup;
var out_popup_options={
	follow: [true, true],
	followSpeed: 0,
	fadeSpeed: 0,
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function(){
		$(this).addClass('opened');
		//$(this).find('.b-close').on('click', function(){out_popup.close();});
	},
	onClose: function(){
		if($(this).is('.close-reload')) {
			// var redirectUrl = $(this).data("redirect");
			// if(redirectUrl !== undefined && redirectUrl != ""){
			// 	document.location.href = redirectUrl;
			// } else {
   //          	loadView();
   //          }
   			// fw_loading_start();
      		// window.location.reload();
			reloadPage();
        }
		$(this).removeClass('opened');
	}
};
function callBackOnUploadAll(data) {
    $('#out-invoice-create').html('<?php echo $formText_CreateInvoices_Output; ?>').prop('disabled',false);
};
function callbackOnStart(data) {
    $('#out-invoice-create').html('<?php echo $formText_UploadInProgress_output;?>...').prop('disabled',true);
};
function callbackOnDelete(data){

}
function reloadPage(){
	var selectedAll = $("#selectDeselectAll").is(":checked");
	var selectedInvoices = $(".item-title input:checked");
	var selectedInvoicesString = "";
	if(selectedAll){
		selectedAll = 1;
	} else {
		selectedAll = 0;
	}
	if(!selectedAll){
		var selectedInvoicesArray = selectedInvoices.serializeArray();
		var selectedInvoicesValueArray = new Array();
		$(selectedInvoicesArray).each(function(index, value){
			selectedInvoicesValueArray.push(value.value);
		})
		selectedInvoicesString = selectedInvoicesValueArray.join(",");
	}
	var data = {
		selectedInvoices: selectedInvoicesString,
		selectedAll: selectedAll,
		filter: '<?php echo $_GET['filter'];?>',
		ownercompany: '<?php echo $_GET['ownercompany'];?>',
		department_filter: '<?php echo $_GET['department_filter'];?>',
		project_filter: '<?php echo $_GET['project_filter'];?>',
		search: '<?php echo $_GET['search'];?>'
	}
	loadView("list", data);
}
$(function() {
	<?php foreach($ownercompanies as $ownercompany){ ?>
		<?php if($ownercompany['invoiceFromEmail'] != "") { ?>
		    var data = {
		        sender: '<?php echo $ownercompany['invoiceFromEmail']?>',
		        host: '<?php echo $v_email_server_config["host"];?>'
		    }
		    ajaxCall("check_spf", data, function(obj){
		        if(obj.data !== undefined)
		        {
		            if(obj.data.status == 'FAIL')
		            {
		                sendEmail_alert(obj.data.message, "warning");
		            }
		        }
		    });
		<?php } else { ?>
			sendEmail_alert("<?php echo $formText_MissingInvoiceFromEmail_output?>", "warning");
		<?php } ?>
	<?php } ?>
    function sendEmail_alert(msg, type)
    {
        $('.spf_check_warning').append('<div class="alert alert-' + type + ' alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' + msg + '</div>').show();

        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(msg);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
	}

	$(".markAsHandled").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
            batch_id: $(this).data("batch-id"),
        };
		bootbox.confirm('<?php echo $formText_MarkInvoicesAsHandled_output; ?>?', function(result) {
			if (result) {
				ajaxCall('markAsHandled', data, function(json) {
					reloadPage();
				});
			}
		});
	})
	$(".ownercompaniesSelect").on("change", function(){
		window.location = '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&ownercompany=";?>'+$(this).val();
	});

	$(".item-customer .item-title input[type=checkbox]").on("change", function(){
		$(this).closest(".item-customer").find(".item-order input[type=checkbox]").prop("checked", $(this).is(":checked"));
		out_calculate(this);
	});
	$(".item-customer .item-order input[type=checkbox]").on("change", function(){
		out_calculate(this);
	});

	$('#selectDeselectAll').on('change', function(event) {
		fw_loading_start();
		var totalToProcess = $('[name="customer[]"]:visible').length;
		var processed = 0;
		var _this = $(this);
		setTimeout(function() {
			if (_this.prop('checked')) {
				$('[name="customer[]"]:visible').each(function (index,item) {
					if(!$(this).prop('checked')) $(this).trigger('click');
					processed++;
				});
			}
			else {
				$('[name="customer[]"]:visible').each(function (index,item) {
					if($(this).prop('checked')) $(this).trigger('click');
					processed++;
				});
			}
			fw_loading_end();
		}, 100);
	});

	$('#out-invoice-date-config input').datepicker({
		dateFormat: 'dd.mm.yy',
		onSelect: function(d, i){
			if(d !== i.lastVal){
				window.location = '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&filter=".$_GET['filter']."&ownercompany=";?>'+$(".ownercompaniesSelect").val();
			}
		}
	});
	$(".editInvoiceDates").unbind("click").bind("click", function(e){
		var datepair = $(this).data("datepair");
 		e.preventDefault();
        var data = {
            datepair: datepair,
        };
        ajaxCall('overrideDates', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
	})
    function calculateTotal(){
        var totalCost = 0;
        var totalCostArray = [];
        var totalSelectedArray = [];
        var totalOwnerCompanies = [];
        var totalSelected2 = {};
		var selectedCustomerCount = 0;
        $(".item-customer:not(.hidden) .item-title input:checked").each(function(){
        	var ownercompanyid = $(this).parents(".item-customer").data("ownercompany");
        	var ownerCompanyTotal = 0;
            if(totalSelectedArray[ownercompanyid] != undefined){
            	ownerCompanyTotal = totalSelectedArray[ownercompanyid];
            }
            ownerCompanyTotal++;
            totalSelectedArray[ownercompanyid] = ownerCompanyTotal;
        })
        $(".item-customer:not(.hidden) .total-total-hidden").each(function(){
			if($(this).parents(".item-customer").find(".item-title input:checked").length > 0){
	        	var value = parseFloat($(this).val());
	            if(value > 0) {
	                totalCost += value;
		            var ownercompanyid = $(this).parents(".item-customer").data("ownercompany");
		            var ownerCompanyTotal = 0;
		            if(totalCostArray[ownercompanyid] != undefined){
		            	ownerCompanyTotal = totalCostArray[ownercompanyid];
		            }
		            ownerCompanyTotal += value;
		            totalCostArray[ownercompanyid] = ownerCompanyTotal;
	            }
				selectedCustomerCount++;
			}
        })
        $(".item-customer").each(function(){
        	var ownercompanyid = $(this).data("ownercompany");
        	var ownerCompanyTotal = 0;
            if(totalOwnerCompanies[ownercompanyid] != undefined){
            	ownerCompanyTotal = totalOwnerCompanies[ownercompanyid];
            }
            ownerCompanyTotal++;
            totalOwnerCompanies[ownercompanyid] = ownerCompanyTotal;
        })
        //init counters
        $(".item-customer:not(.hidden) tr.date_different").each(function(){
        	var invoice = $(this).parents(".item-customer");
        	var selectedTotal = 0;
        	var key = $(this).data("key");
        	totalSelected2[key] = 0;
        })
        //count
        $(".item-customer:not(.hidden) tr.date_different").each(function(){
        	var invoice = $(this).parents(".item-customer");
        	var selectedTotal = 0;
        	var key = $(this).data("key");
        	if(invoice.find(".item-title input").is(":checked")){
	            if(totalSelected2[key] != undefined){
	            	selectedTotal = totalSelected2[key];
	            }
	            selectedTotal++;
            	totalSelected2[key] = selectedTotal;
	        }
		})

		$(".selectedAmount").html(0);
		$(".totalAmount").html(0);
		$(".totalMoneyAmount").html(0);

        $.each(totalCostArray, function( index, value ){
        	if(value != undefined){
	        	$(".totalMoneyAmount[data-ownercompanyid="+index+"]").html(value.toFixed(2).replace(".", ","));
	        }
		});
        $.each(totalSelectedArray, function( index, value ){
        	if(value == undefined){
        		value = 0;
        	}
        	$(".selectedAmount[data-ownercompanyid="+index+"]").html(value);
		});
        $.each(totalOwnerCompanies, function( index, value ){
    		$(".totalAmount[data-ownercompanyid="+index+"]").html($(".item-customer[data-ownercompany="+index+"]").length);
		});
        $.each(totalSelected2, function( index, value ){
        	if(value == undefined){
        		value = 0;
        	}
        	$(".summaryRowInfo[data-key="+index+"] .summaryRowInfoSelected").html(value);
		});
		$(".totalInvoicesSelected").html(selectedCustomerCount);
        $(".totalCostNumber").html(totalCost.toFixed(2));
    }
    calculateTotal();
	function out_calculate(_this)
	{
		var _total = 0;
		var _vat = 0;
		var _checked = false;
		var _customer = $(_this).closest(".item-customer");
		_customer.find(".item-order input[type=checkbox]").each(function(){
			if($(this).is(":checked"))
			{
				_checked = true;
				_total = _total + parseFloat($(this).data("total"));
				_vat = _vat + parseFloat($(this).data('vat'));
			}
		});

		var _grand_total = _total + _vat;
		var total_formated = _total.toFixed(2).replace('.',',');
		var vat_formated = _vat.toFixed(2).replace('.',',');
		var grand_total_formated = _grand_total.toFixed(2).replace('.',',');

		_customer.find(".item-title input[type=checkbox]").prop("checked", _checked);
		_customer.find(".item-totals .total-sum").text(total_formated);
		_customer.find(".item-totals .total-vat").text(vat_formated);
		_customer.find(".item-totals .total-total").text(grand_total_formated);
		_customer.find(".item-totals .total-sum-hidden").val(_total.toFixed(2));
		_customer.find(".item-totals .total-vat-hidden").val(_vat.toFixed(2));
		_customer.find(".item-totals .total-total-hidden").val(_grand_total.toFixed(2));

		$('.customersSelectedCountSpan').html($('.item-title input[type=checkbox]:checked').length);
		calculateTotal();
	}

	var procLinesProcessed;
	var totalProcLines;
	var lineProcessing = false;

	$("#out-invoice-create").on("click", function(){
		$('#out-invoice-create').text('<?php echo $formText_InvoicingStartedPleaseWait_Output; ?>...');
		if(!fw_click_instance)
		{
			fw_click_instance = true;
			var customerList = $("#out-customer-list").clone();
			customerList.find(".item-customer.hidden").remove();
			var serializedInput = customerList.find("input").serialize();

			$.ajax({
				cache: false,
				type: 'POST',
				dataType: 'json',
				url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=create_invoices";?>',
				data: "fwajax=1&fw_nocss=1&" + serializedInput  + '&' + $('#out-invoice-date-config input').eq(0).serialize() + '&' + $('#out-invoice-date-config input').eq(1).serialize(),
				success: function(obj){
					$('#out-hook-error').html('');

					var procData = $.parseJSON(obj.html);
					if (procData.error) {
						if(procData.error.type == 1){
							$('#out-hook-error').html('<?php echo $formText_AnotherBatchInvoicingAlreadyRunningPleaseWaitUntilItIsDone_output;?>');
						} else if(procData.error.type == 2){
							$('#out-hook-error').html('<?php echo $formText_InvoicingFunctionalityLockedPleaseContactSupport_output;?>');
						} else {
							$('#out-hook-error').html(procData.error.message);
						}
						$('#out-hook-error').css({ color: 'red' });
						fw_click_instance = false;
					} else {
						totalProcLines = procData.lines.length;
						procLinesProcessed = 0;
						$('#out-customer-list').hide();
						$('#out-process-progress').show();
						$('#out-process-progress .total').html(totalProcLines);
						// console.log(procData);
						process_lines(procData);
					}
				}
			}).fail(function() {
				fw_info_message_add("error", "<?php echo $formText_ErrorOccuredPleaseContactSupport_framework;?>", true, true);
				fw_click_instance = false;
			});
		}
	});

	function process_lines(procData) {
		if(!lineProcessing){
			lineProcessing = true;
			if (procData.lines[0]) {
				var customerList = $("#out-customer-list").clone();
				customerList.find(".item-customer.hidden").remove();
				var serializedInput = customerList.find("input").serialize();

				$.ajax({
					cache: false,
					type: 'POST',
					dataType: 'json',
					url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=create_invoices";?>',
					data: "fwajax=1&fw_nocss=1&procrunlineID="+procData.lines[0].procrunlineID+"&procrunID="+procData.procrunID+"&"+ serializedInput + "&bankAccountId=" + $('#bankAccountId').val() + '&' + $('#out-invoice-date-config input').eq(0).serialize() + '&' + $('#out-invoice-date-config input').eq(1).serialize(),
					success: function(obj){
						var resData = JSON.parse(obj.html);
						if (resData && resData.hook_result && resData.hook_result.emergencyLock) {
							procData.lines = [];
							fw_info_message_add("error", "<?php echo $formText_SyncErrorEncounteredEmergencyLockdownPleaseContactSupport_framework;?>", true, true);
							lineProcessing = false;
							show_finish();
						} else {
							// calc
							procData.lines.splice(0,1);
							procLinesProcessed++;
							var progressBarWidth = procLinesProcessed / totalProcLines * 100;
							// html
							$('#out-process-progress .processed').html(procLinesProcessed);
							$('#out-process-progress .progress-bar2-fill').css('width', progressBarWidth + '%');
							// process next
							lineProcessing = false;
							process_lines(procData);
						}

					}
				}).fail(function() {
					fw_info_message_add("error", "<?php echo $formText_ErrorOccuredPleaseContactSupport_framework;?>", true, true);
					lineProcessing = false;
					fw_click_instance = false;
				});
			}
			else {
				lineProcessing = false;
				show_finish();
			}
		}
	}

	function show_finish() {
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=create_invoices";?>',
			data: "fwajax=1&fw_nocss=1&showFinish=1",
			success: function(obj){
				fw_click_instance = false;
				$('#out-process-progress').append(obj.html);
				// // calc
				// procData.lines.splice(0,1);
				// procLinesProcessed++;
				// var progressBarWidth = procLinesProcessed / totalProcLines * 100;
				// // html
				// $('#out-process-progress .processed').html(procLinesProcessed);
				// $('#out-process-progress .progress-bar2-fill').css('width', progressBarWidth + '%');
				// // process next
				// process_lines(procData);
			}
		}).fail(function() {
			fw_info_message_add("error", "<?php echo $formText_ErrorOccuredPleaseContactSupport_framework;?>", true, true);
			fw_click_instance = false;
		});
	}
	function ajaxCall(includeFile, data, callback, showLoader) {

        // includeFile check
        if (typeof(includeFile) !== 'string') return;
        // data object check
        if (typeof(data) !== 'object') var data = {};
        // callback check
        if (typeof(callback) !== 'function') var callback = function() { };
        // showLoader check
        if (typeof(showLoader) !== 'boolean') var showLoader = true;

        // Default data
        var __data = {
            fwajax: 1,
            fw_nocss: 1
        }

        // Concat default and user data
        var ajaxData = $.extend({}, __data, data);
        // Show loader
        if (showLoader) $('#fw_loading').show();

        // Run AJAX
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act="; ?>' + includeFile,
            data: ajaxData,
            success: function(json){
                if (showLoader) $('#fw_loading').hide();
                callback(json);
            }
        });
    }
	$(".resetOverrideDatesPopup").unbind("click").bind("click", function(e){
		var datepair = $(this).data("datepair");
 		e.preventDefault();
        var data = {
            datepair: datepair,
            action: "reset"
        };
        ajaxCall('overrideDates', data, function(json) {
			reloadPage();
        });
	})
	$(".overrideDatesPopup").unbind("click").bind("click", function(e){
		var datepair = $(this).data("datepair");
 		e.preventDefault();
        var data = {
            datepair: datepair,
        };
        ajaxCall('overrideDates', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
	})
	<?php
	if($_GET['filter'] != ""){
		if(isset($_SESSION[$_GET['filter']])){
			$_GET['filter'] = $_SESSION[$_GET['filter']];
		}
		?>
		$(".summaryRowInfo").addClass("hidden");
		$(".summaryRowInfo.active").removeClass("hidden");
		$(".item-customer").addClass("hidden");
		$(".item-customer .item-title input[type=checkbox]").prop("checked", false);

		$("tr.date_<?php echo md5($_GET['filter']);?>").each(function(){
			var parent = $(this).parents(".item-customer");
			parent.removeClass("hidden");
			parent.find(".item-title input[type=checkbox]").prop("checked", true);
			out_calculate(parent.find(".item-title input[type=checkbox]"));
		})
		$(".customersSelectedCountSpan").html($(".item-customer .item-title input[type=checkbox]:checked").length);
		$(".customersListedCountSpan").html($(".item-customer:not(.hidden)").length);
		<?php
	}
	/*
	$files_not_downloaded = 0;
	if($files_not_downloaded > 0){
		?>
		fw_info_message_add("error", "<?php echo $formText_Batch_output." ".$batch['id']." ".$formText_StillHasNotPrintedInvoices_output." ".$files_not_downloaded;?>");
		fw_info_message_show();
		<?php
	}*/
	?>

});
</script>
