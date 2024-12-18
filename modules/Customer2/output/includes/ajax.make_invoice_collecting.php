<?php
require_once("fnc_getMaxDecimalAmount.php");
$collectingOrderId = $_POST['collectingorderId'];
$action = $_POST['action'];
// if($action == "setCollectingOrderInvoiced" && $collectingOrderId) {
//     $s_sql = "SELECT * FROM orders WHERE collectingorderId = ? AND invoiceNumber > 0";
//     $o_query = $o_main->db->query($s_sql, array($collectingOrderId));
//     $invoicedOrder = ($o_query ? $o_query->row_array() : array());
//     if($invoicedOrder){
// 		$s_sql = "UPDATE customer_collectingorder SET
// 	    updated = now(),
// 	    updatedBy= ?,
// 	    invoiceNumber= ?
// 	    WHERE id = ?";
// 	    $o_main->db->query($s_sql, array($variables->loggID, $invoicedOrder['invoiceNumber'], $collectingOrderId));
// 	}
// }
$s_sql = "SELECT * FROM article_accountconfig";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
	$article_accountconfig = $o_query->row_array();
}
// read output settings
// require_once __DIR__ . '/settingsOutput/settings.php';
$s_sql = "SELECT * FROM batch_invoicing_basisconfig";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
	$basisConfigData = $o_query->row_array();
}

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
$totalOwnerCompanies = 0;
$s_sql = "SELECT * FROM ownercompany WHERE content_status < 2";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
	$totalOwnerCompanies = $o_query->num_rows();
}
if (!$activateMultiOwnerCompanies) {
	$s_sql = "SELECT * FROM ownercompany";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0){
		$defaultOwnerCompany = $o_query->row_array();
    	$defaultOwnerCompanyId = $defaultOwnerCompany['id'];
	}
}
$s_sql = "SELECT * FROM batch_invoicing_basisconfig";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
	$basisConfigData = $o_query->row_array();
}
$s_sql = "SELECT * FROM batch_invoicing_accountconfig";
$o_query = $o_main->db->query($s_sql);
$batchinvoicing_accountconfig = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM customer_accountconfig";
$o_query = $o_main->db->query($s_sql);
$v_customer_accountconfig = $o_query ? $o_query->row_array() : array();

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

// List of available bank accounts - THIS IS BROKEN WITH MULTI OWNERCOMPAMY IMPLEMENTATION - MUST FIX
$bankAccountArray = array();
$bankAccountArray[1] = $v_settings['companyaccount'];
if (!empty($v_settings['companyBankAccount2'])) $bankAccountArray[2] = $v_settings['companyBankAccount2'];
if (!empty($v_settings['companyBankAccount3'])) $bankAccountArray[3] = $v_settings['companyBankAccount3'];

$customerId = $o_main->db->escape_str($_POST['customerId']);

// !! Reading all customers and orders in ob_start() buffer
//
$s_sql = "SELECT c.*, customer_collectingorder.id as collectingorderId, customer_collectingorder.ownercompanyId ownercompany_id, customer_collectingorder.department_for_accounting_code, customer_collectingorder.accountingProjectCode FROM customer_collectingorder LEFT JOIN customer c ON c.id = customer_collectingorder.customerId  WHERE customer_collectingorder.id = '".$collectingOrderId."' AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null) AND customer_collectingorder.content_status = 0  AND (customer_collectingorder.seperateInvoiceFromSubscription = 0 || customer_collectingorder.seperateInvoiceFromSubscription IS NULL) AND customer_collectingorder.content_status = 0 GROUP BY c.id ORDER BY c.name";
if ($activateMultiOwnerCompanies) {
	$s_sql = "SELECT c.*, customer_collectingorder.id as collectingorderId, customer_collectingorder.ownercompanyId ownercompany_id, customer_collectingorder.department_for_accounting_code, customer_collectingorder.accountingProjectCode FROM customer_collectingorder LEFT JOIN customer c ON c.id = customer_collectingorder.customerId  WHERE customer_collectingorder.id = '".$collectingOrderId."' AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null) AND customer_collectingorder.content_status = 0 AND (customer_collectingorder.seperateInvoiceFromSubscription = 0 || customer_collectingorder.seperateInvoiceFromSubscription IS NULL) AND customer_collectingorder.content_status = 0 GROUP BY c.id, customer_collectingorder.ownercompanyId ORDER BY c.name";
}
$customersCount = 0;
$customersCountWithErrors = 0;
$customersSelected = 0;
$v_customers = array();
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
	$v_customers = $o_query->result_array();
}


$s_sql = "SELECT c.*, customer_collectingorder.id as collectingorderId, customer_collectingorder.ownercompanyId ownercompany_id, customer_collectingorder.seperateInvoiceFromSubscription as seperatedInvoiceSubscriptionId, customer_collectingorder.department_for_accounting_code, customer_collectingorder.accountingProjectCode FROM customer_collectingorder LEFT JOIN customer c ON c.id = customer_collectingorder.customerId WHERE customer_collectingorder.id = '".$collectingOrderId."' AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null) AND customer_collectingorder.content_status = 0 AND customer_collectingorder.seperateInvoiceFromSubscription > 0 AND customer_collectingorder.content_status = 0  GROUP BY customer_collectingorder.seperateInvoiceFromSubscription ORDER BY c.name";
if ($activateMultiOwnerCompanies) {
	$s_sql = "SELECT c.*, customer_collectingorder.id as collectingorderId, customer_collectingorder.ownercompanyId ownercompany_id, customer_collectingorder.seperateInvoiceFromSubscription as seperatedInvoiceSubscriptionId, customer_collectingorder.department_for_accounting_code, customer_collectingorder.accountingProjectCode FROM customer_collectingorder LEFT JOIN customer c ON c.id = customer_collectingorder.customerId WHERE customer_collectingorder.id = '".$collectingOrderId."'  AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null) AND customer_collectingorder.content_status = 0 AND customer_collectingorder.seperateInvoiceFromSubscription > 0 AND customer_collectingorder.content_status = 0  GROUP BY customer_collectingorder.seperateInvoiceFromSubscription, customer_collectingorder.ownercompanyId ORDER BY c.name";
}
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
	$v_customers2 = $o_query->result_array();
	foreach($v_customers2 as $v_customer) {
		array_push($v_customers, $v_customer);
	}
}
ob_start();
$creditTimeDays = 14;
$prepaidCommonCostCounter = 0;
foreach($v_customers as $v_customer)
{
	$creditTimeDays = $v_customer['credittimeDays'] > 0 ? $v_customer['credittimeDays'] : 14;
    // Get block group id, will be for orders grouping
    $block_group_id = $v_customer['id'];
    if ($activateMultiOwnerCompanies) {
        $block_group_id = $v_customer['id'] . '-' . $v_customer['ownercompany_id'];
    }
    $block_group_id .="-".$v_customer['collectingorderId'];

    $s_sql = "SELECT * FROM ownercompany WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($v_customer['ownercompany_id']));
	if($o_query && $o_query->num_rows()>0){
		$v_settings = $o_query->row_array();
	}

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
	$l_vat = $v_settings['vat'];
	// if($v_customer['taxFreeSale'] == 1) $l_vat = 0;

	$s_sql = "SELECT orders.*, CONCAT_WS(' ', TRIM(co.reference), TRIM(co.delivery_date), TRIM(co.delivery_address_line_1), TRIM(co.delivery_address_line_2), TRIM(co.delivery_address_city), TRIM(co.delivery_address_postal_code), TRIM(co.delivery_address_country)) as combinedReference, co.department_for_accounting_code, co.accountingProjectCode
	FROM orders
	JOIN customer_collectingorder co ON co.id = orders.collectingorderId
	WHERE orders.collectingorderId = '".$v_customer['collectingorderId']."' AND orders.content_status = 0 ORDER BY orders.id ";

	$v_orders = array();
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0){
		$v_orders = $o_query->result_array();
	}

    /**
     * Customer errors
     */
    $customer_errors = array();

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

	$errors = array();
	$contactPersons = array();
	$currencyIdsDetected = array();
	foreach($v_orders as $v_order){
		if(intval($v_order['seperateInvoiceFromSubscription']) > 0){
			$block_group_id.="-seperate-".$v_order['seperateInvoiceFromSubscription'];
		}
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

		// Currency check
		// If multi currencies assigned, check if order has valid currencyId assigned to it
		// if ($v_settings['allowMultiCurrencies']) {

		// 	$currencyCount = 0;
		// 	$s_sql = "SELECT * FROM currency WHERE id = ?";
		// 	$o_query = $o_main->db->query($s_sql, array($v_order['currencyId']));
		// 	if($o_query && $o_query->num_rows()>0){
		// 		$currencyCount = $o_query->num_rows();
		// 	}
		// 	if (!$currencyCount) {
		// 		$errors[$orderId][] = array(
		// 			'orderId' => $orderId,
		// 			'errorMsg' => $formText_InvalidCurrencyId_output
		// 		);
		// 	}
		// }


		// Get contactpersons
		$s_sql = "SELECT * FROM customer_collectingorder WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($v_order['collectingorderId']));
		if($o_query && $o_query->num_rows()>0){
			$collectingOrder = $o_query->row_array();
		}

		if ($collectingOrder['contactpersonId'] > 0) {
			$s_sql = "SELECT * FROM contactperson WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($collectingOrder['contactpersonId']));
			if($o_query && $o_query->num_rows()>0){
				$contactPersonData = $o_query->row_array();
			}
			$contactPersons[$collectingOrder['contactpersonId']] = $contactPersonData['name']." ".$contactPersonData['middlename']." ".$contactPersonData['lastname'];
		}

	}

	// Customer count & selected count
	$customersCount++;
	if (!count($errors) && !count($customer_errors)) {
		$customersSelected++;
	}
	else {
		$customersCountWithErrors++;
	}
	$allowInvoice_error = 0;
	if($batchinvoicing_accountconfig['notAllowInvoiceIfNoExtcustomerId']){
		$s_sql = "SELECT * FROM customer_externalsystem_id WHERE ownercompany_id = ? AND customer_id = ?";

		$o_query = $o_main->db->query($s_sql, array($v_customer['ownercompany_id'], $v_customer['id']));
		$externalCustomerIdData = array();
		if($o_query && $o_query->num_rows()>0){
			$externalCustomerIdData = $o_query->row_array();
		}
		$externalCustomerId = intval($externalCustomerIdData['external_id']);
		if($externalCustomerId == 0){
			$allowInvoice_error = 1;
		}
	}
	?><div class="item-customer">
		<div class="item-title">
			<div>
				<?php if(!count($errors) && !count($customer_errors) && !$allowInvoice_error): ?>
				<input type="checkbox" value="<?php echo $block_group_id;?>" name="customer[]" checked class="hidden" />
				<?php endif; ?>
				<?php echo $v_customer['name']." ".$v_customer['middlename']." ".$v_customer['lastname'];?>
                <?php if ($activateMultiOwnerCompanies):
                	$s_sql = "SELECT * FROM ownercompany WHERE id = ?";
					$o_query = $o_main->db->query($s_sql, array($v_customer['ownercompany_id']));
					if($o_query && $o_query->num_rows()>0){
						$ownerCompanyData = $o_query->row_array();
					}
                ?>
                    <div>
                        <small>
                            (<?php echo $formText_OwnerCompany_output; ?>: <?php echo $ownerCompanyData['name']; ?>)
                        </small>
                    </div>
                <?php endif; ?>
			</div>
			<?php if($v_customer['accountingProjectCode'] > 0 || $v_customer['department_for_accounting_code'] > 0) { ?>
				<div class="out-projectcode">
					<?php echo $formText_ProjectCode_output;?>: <?php echo $v_customer['accountingProjectCode']; ?>
					<?php if($v_customer['department_for_accounting_code'] > 0 && $v_customer['accountingProjectCode'] > 0) { ?>
						<br/>
					<?php } ?>
					<?php if($v_customer['department_for_accounting_code'] > 0) {
						echo $formText_DepartmentCode_output;?>: <?php echo $v_customer['department_for_accounting_code'];
					} ?>
				</div>
			<?php } ?>
			<div class="out-ref"><?php echo $formText_YourContact_output;?>: <?php echo join(',', $contactPersons); ?> </div>
			<div class="out-address"><?php echo $s_address;?></div>
			<br clear="all">
		</div>

        <?php if(count($customer_errors)): ?>
        <div class="item-error">
            <div class="alert alert-danger"><?php echo $formText_CustomerHasErrors_output; ?>
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
            <div class="alert alert-danger"><?php echo $formText_CustomerHasError_output; ?>
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
			<?php
			$addAdminFee = false;
			if (!$isMultiCurrencyAccount) $currencyIdsDetected[] = 1;

			foreach ($currencyIdsDetected as $currentCurrencyId => $currentCurrencyVal):

				$currentCurrency = ($isMultiCurrencyAccount ? $currentCurrencyVal : $defaultAccountCurrency);
				$currentCurrencyIndexForArray = $currentCurrency ? $currentCurrency : 'EMPTY_CURRENCY';
				$currentCurrencyCode = ($isMultiCurrencyAccount ? $allCurrenciesCodeList[$currentCurrencyId] : $defaultAccountCurrencyCode);

				?>
				<table class="table table-condensed">
				<thead>
					<tr>
						<th>&nbsp;</th>
						<th><?php echo $formText_Text_Output;?></th>
						<th><?php echo $formText_Amount_Output;?></th>
						<th><?php echo $formText_Price_Output;?></th>
						<th><?php echo $formText_Discount_Output;?></th>
						<th><?php echo $formText_Vat_Output;?></th>
						<th></th>
						<th class="text-right"><?php echo $formText_TotalPrice_Output;?></th>
					</tr>
				</thead>
				<tbody>
					<?php

					$l_sum = 0;
					$vatTotal = 0;

         //            $dateValShow = '';
         //            $dateExpireShow = '';
         //            $notOverrideDate = false;
         //            foreach($v_orders as $order){
         //                $s_sql = "SELECT * FROM orders LEFT OUTER JOIN subscriptionmulti ON subscriptionmulti.id = orders.invoiceDateSettingFromSubscription WHERE orders.id = ".$order['id']." ORDER BY orders.invoiceDateSettingFromSubscription ASC";
         //                $o_query = $o_main->db->query($s_sql);
         //                if($o_query && $o_query->num_rows()>0){
         //                    $orderSingle = $o_query->row_array();
         //                    if($orderSingle){
         //                    	if($orderSingle['invoiceDateSettingFromSubscription'] > 0){
         //                    		$notOverrideDate = true;
	        //                         $invoicedate_suggestion = $orderSingle['invoicedate_suggestion'];
	        //                         $invoicedate_daynumber = $orderSingle['invoicedate_daynumber'];

	        //                         $duedate = $orderSingle['duedate'];
	        //                         $duedate_daynumber = $orderSingle['duedate_daynumber'];

	        //                         switch($invoicedate_suggestion) {
	        //                             case 0:
	        //                                 $dateValShow = date("d.m.Y", time());
	        //                             break;
	        //                             case 1:
	        //                                 $dateValShow = date("d.m.Y", strtotime($orderSingle['dateFrom']));
	        //                             break;
	        //                             case 2:
	        //                                 $dateValShow = date($invoicedate_daynumber.".m.Y", strtotime("-1 month", strtotime($orderSingle['dateFrom'])));
	        //                             break;
	        //                             case 3:
	        //                                 $dateValShow = date($invoicedate_daynumber.".m.Y", strtotime($orderSingle['dateFrom']));
	        //                             break;
	        //                         }
	        //                         switch($duedate) {
	        //                             case 0:
		       //                              $dateExpireShow = date("d.m.Y", strtotime("+".$v_customer['credittimeDays']." days", strtotime($dateValShow)));
	        //                             break;
	        //                             case 1:
	        //                                 $dateExpireShow = date("d.m.Y", strtotime("+".$duedate_daynumber." days", strtotime($dateValShow)));
	        //                             break;
	        //                             case 2:
	        //                                 $dateExpireShow = date($duedate_daynumber.".m.Y", strtotime($dateValShow));
	        //                             break;
	        //                             case 3:
	        //                                 $dateExpireShow = date($duedate_daynumber.".m.Y", strtotime("+1 month", strtotime($dateValShow)));
	        //                             break;
	        //                         }
	        //                     } else {
	        //                     	if(!$notOverrideDate || ($notOverrideDate && $dateExpireShow == '' && $dateValShow == '')){
									// 	$dateValShow = date('d').".".date('m').".".date('Y');
		       //                      	if(isset($_GET["invoice_date"]) && $_GET["invoice_date"] != ""){
									// 		$dateValShow = $_GET["invoice_date"];
									// 	}
									// 	$dateExpireShow = date("d.m.Y", strtotime("+14 days", strtotime($dateValShow)));
									// 	if(isset($_GET["due_date"]) && $_GET["due_date"] != "" && strtotime($_GET['due_date']) > strtotime($_GET['invoice_date'])){
									// 		$dateExpireShow = $_GET["due_date"];
									// 	}
									// }
	        //                     }
         //                    }
         //                }
         //            }


					$dateValShow = date('d').".".date('m').".".date('Y');
                	if(isset($_GET["invoice_date"]) && $_GET["invoice_date"] != ""){
						$dateValShow = $_GET["invoice_date"];
					}
					$dateExpireShow = date("d.m.Y", strtotime("+14 days", strtotime($dateValShow)));
					if(isset($_GET["due_date"]) && $_GET["due_date"] != "" && strtotime($_GET['due_date']) > strtotime($_GET['invoice_date'])){
						$dateExpireShow = $_GET["due_date"];
					}

					foreach($v_orders as $v_order)
					{
						$orderId = $v_order['id'];
						$vatData = getOrderVAT($o_main, $orderId);
						$orderHasError = (count($errors[$orderId]) ? true : false);

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
								<?php if(!count($errors)): ?>
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
								<input type="checkbox" value="<?php echo $v_order['id'];?>" name="order_number[]" <?php echo (!$orderHasError ? 'checked' : ''); ?> data-total="<?php echo $v_order['priceTotal']; ?>" data-vat="<?php echo $vat; ?>" class="hidden" />
								<?php endif; ?>
							</td>
							<td><?php echo $v_order['articleName']; ?></td>
							<td><?php echo number_format(floatval($v_order['amount']),$decimalNumber,',',''); ?></td>
							<td><?php echo number_format(floatval($v_order['pricePerPiece']),2,',',''); ?></td>
							<td><?php echo round($v_order['discountPercent'], 2); ?>%</td>
							<td><?php echo $vatData['percentRate']; ?>%</td>
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
							<td class="text-right"><?php echo number_format(floatval($v_order['priceTotal']),2,',',''); ?></td>
						</tr><?php

						$totalSumInclVat = $vatTotal + $l_sum;
						$l_sum = round($l_sum, $decimalPlaces);
						$totalSumInclVat = round($totalSumInclVat, $decimalPlaces);
						$vatTotal = $totalSumInclVat - $l_sum;
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
				            // $taxFreeSale = $v_customer['taxFreeSale'];

							$vatCode = $adminFeeArticle['VatCodeWithVat'];
							$bookaccountNr = $adminFeeArticle['SalesAccountWithVat'];

                            // if($taxFreeSale && !$adminFeeArticle['forceVat']) {
                            //     $vatCode = $article_accountconfig['vatcode_default_when_without_vat'];
                            //     $bookaccountNr = $adminFeeArticle['SalesAccountWithoutVat'];
                            // } else {
                            // }

                            if($vatCode == ""){
								$vatCode = $article_accountconfig['defaultVatCodeForArticle'];
                                // if($taxFreeSale && !$adminFeeArticle['forceVat']) {
                                //     $vatCode = $article_accountconfig['vatcode_default_when_without_vat'];
                                // } else {
                                // }
                            }
                            if($bookaccountNr == ""){
								$bookaccountNr = $article_accountconfig['defaultSalesAccountWithVat'];
                                // if($taxFreeSale && !$adminFeeArticle['forceVat']) {
                                //     $bookaccountNr = $article_accountconfig['defaultSalesAccountWithoutVat'];
                                // } else {
                                // }
                            }

                            $s_sql = "SELECT * FROM vatcode WHERE vatCode = ?";
                            $o_query = $o_main->db->query($s_sql, array($vatCode));
                            $vatItem = $o_query ? $o_query->row_array() : array();
                            $vatPercent = $vatItem['percentRate'];

				            $adminFeeTotal = 0;
				            $adminFeeTotal = $adminFeeArticle['price'];
				            $vat = round($adminFeeTotal * ($vatPercent/100), $decimalPlaces);

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
										<input type="checkbox" value="<?php echo $orderId;?>" name="order_number[]" checked data-total="<?php echo $adminFeeTotal; ?>" data-vat="<?php echo $vat; ?>"/>
									<?php endif;?>
								</td>
								<td><?php echo $adminFeeArticle['name']; ?></td>
								<td><?php echo number_format(floatval("1.00"),$decimalNumber,',',''); ?></td>
								<td><?php echo number_format(floatval($adminFeeArticle['price']),2,',',''); ?></td>
								<td><?php echo round(0); ?>%</td>
								<td><?php echo $vatPercent; ?>%</td>
								<td></td>
								<td class="text-right"><?php echo number_format(floatval($adminFeeTotal),2,',',''); ?></td>
							</tr>
							<?php

							$totalSumInclVat = $vatTotal + $l_sum;
							$l_sum = round($l_sum, $decimalPlaces);
							$totalSumInclVat = round($totalSumInclVat, $decimalPlaces);
							$vatTotal = $totalSumInclVat - $l_sum;
						}
					}
					?>
					<tr>
						<td colspan="8" class="item-totals text-right">
							<?php if(!count($errors)): ?>
							<span class="spacer"><?php echo $formText_SumWithoutVat_Output.' '. $currentCurrency.': ';?><span class="total-sum"><?php echo number_format(floatval($l_sum),$decimalPlaces,',','');?></span></span>
							<span class="spacer"><?php echo $formText_Vat_Output.' '. $currentCurrency.': ';?><span class="total-vat"><?php echo number_format(floatval($vatTotal),$decimalPlaces,',','');?></span></span>
							<span class="spacer"><?php echo $formText_Total_Output.' '. $currentCurrency.': ';?><span class="total-total"><?php echo number_format(floatval($totalSumInclVat),$decimalPlaces,',','');?></span></span>
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
if($listBuffer != ""){
?>
<?php /*?>
<div id="out-info-box">
	<ul>
		<li><span class="number customersSelectedCountSpan"><?php echo $customersSelected; ?></span> <?php echo $formText_CustomersSelected_output; ?></li>
		<li><span class="number customersListedCountSpan"><?php echo $customersCount; ?></span> <?php echo $formText_CustomersListed_output; ?></li>
	</ul>

</div>*/?>

<div id="out-bank-account-select" <?php echo count($bankAccountArray) <= 1 ? 'style="display:none;"' : ''; ?>>
	<?php echo $formText_ChooseBankAccount_output; ?>
	<select name="bankAccountId" id="bankAccountId">
		<?php foreach ($bankAccountArray as $key => $bankAccount): ?>
		<option value="<?php echo $key; ?>"><?php echo $bankAccount; ?></option>
		<?php endforeach; ?>
	</select>
</div>

<div id="out-invoice-date-config">
	<?php
	if(!$v_customer_accountconfig['deactivate_buttons_for_invoice_date']){
		?>
		<b><?php echo $formText_FillInInvoiceDateColumn_output;?>:</b> &nbsp;&nbsp;&nbsp;
		<div class="button buttonsmall fillinlastdate"><?php echo $formText_LastDateInPreviousMonth_output;?></div>
		<div class="button buttonsmall fillincurentdate"><?php echo $formText_CurrentDate_output;?></div>
		&nbsp;&nbsp; ... <?php echo $formText_OrEditDirectlyTheInvoiceDateField_output;?>
		<div class="clear"></div>
		<br/>
		<?php
	}
	?>
	<?php echo $formText_InvoiceDate_output; ?>
	<input type="text" class="invoice_date_input" name="invoice_date" value="<?php
	if($v_customer_accountconfig['deactivate_buttons_for_invoice_date']){
		echo date('d.m.Y', time());
	}
	?>" autocomplete="off" required>
	<?php echo $formText_DueDate_output; ?>
	<input type="text" class="due_date_input" name="due_date" value="<?php
		echo date('d.m.Y', time() + 60*60*24*$creditTimeDays);
	?>" autocomplete="off" required>

	<?php
	if($batchinvoicing_accountconfig['forceConnectCreditInvoice']) {
		?>
		<div class="selectInvoice" data-customer-id="<?php echo $customerId?>" <?php if($totalSumInclVat >= 0) echo 'style="display: none;"';?>>
			<label>
				<?php echo $formText_SelectInvoice_output;?>
			</label>
			<span class='resetInvoiceConnection'><?php echo $formText_Reset_output;?></span>
			<input type="hidden" id="creditRefNo" value="" name="creditRefNo"/>
			<span class="errorLabel hidden"><?php echo $formText_PleaseSelectTheInvoice_output;?></span>
		</div>
		<?php
	}
	?>
</div>

<div id="out-error-box">
	<?php if ($customersCountWithErrors > 0): ?>
	<div class="alert alert-danger"><?php echo $customersCountWithErrors. ' ' . $formText_CustomerInvoicesHasErrors_output; ?></div>
	<?php endif; ?>
</div>
<div id="out-customer-list">
	<?php /*
	<div class="out-select-all">
		<?php if ($customersCount > 0): ?>
			<input type="checkbox" id="selectDeselectAll" <?php if (($customersCount - $customersCountWithErrors) == $customersSelected) echo 'checked="checked"'; ?>> <?php echo $formText_SelectAll_output; ?>
		<?php endif; ?>
	</div>*/?>
	<div class="out-dynamic">
		<?php echo $listBuffer; ?>
	</div>
	<div class="out-buttons">
		<?php
		if(!count($errors) && !count($customer_errors) && !$allowInvoice_error):
		?>
		<button id="out-invoice-create" class="btn btn-default"><?php echo $formText_CreateInvoices_Output;?></button>
		<?php endif;?>
		<!-- <a class="btn btn-default pull-right history-btn" href="#" data-customer-id="<?php echo $customerId?>"><?php echo $formText_History_Output;?></a> -->
	</div>
</div>

<div id="out-process-progress">
	<div class="progress-title"><span class="processed">0</span> / <span class="total">0</span></div>
	<div class="progress-bar2">
		<div class="progress-bar2-fill"></div>
	</div>
	<div class="progress-info"><?php echo $formText_DoNotCloseThePopupWhileYouCanSeeThisMessage_output;?></div>
</div>
<?php } else { ?>
<div id="out-error-box">
	<div class="alert alert-danger"><?php echo $formText_ThereIsNoOrdersToInvoice_output;; ?></div>
</div>
<?php } ?>
<style>
#popupeditbox .buttonsmall,
#popupeditbox2 .buttonsmall {
	padding: 5px 10px;
	border-radius: 5px;
}
.selectInvoice {
	color: #0284C9;
	cursor: pointer;
	margin-top: 10px;
}
.resetInvoiceConnection {
	margin-left: 30px;
	cursor: pointer;
	color: #0284C9;
	display: none;
}
.selectInvoice label {
	cursor: pointer;
}
.selectInvoice .errorLabel {
	color: red;
}
.periodizationLabel {
	position: relative;
	cursor: pointer;
}
.periodizationHover {
	display: none;
	position: absolute;
	top: -5px;
	right: -110px;
	width: 100px;
	background: #fff;
	border: 1px solid #cecece;
	padding: 3px 5px;
}
.periodizationLabel:hover .periodizationHover {
	display: block
}
	#output-content-container {
		max-width:900px;
		margin:15px auto;
		border-radius:3px;
		border:1px solid #D9D6D6;
	}
	#out-customer-list {
		margin:15px 0;
	}
	#out-customer-list .item-customer {
		margin:10px;
		padding:10px;
		border-radius:3px;
		border:1px solid #D9D6D6;
	}
	#out-customer-list .item-title > div {
		float:left;
		font-size:14px;
		font-weight:bold;
		width:33%;
	}
	#out-customer-list .item-title div.out-address {
		text-align:right;
	}

	#out-customer-list .item-title div.out-ref {
		text-align:center;
	}
	#out-customer-list .item-order {
		padding-left:30px;
	}
	#out-customer-list .item-order table.table {
		margin-bottom:0px;
	}
	#out-customer-list .item-totals span.spacer {
		font-weight:bold;
		padding-left:30px;
	}
	#out-customer-list .out-buttons {
		padding:10px;
	}
	#out-error-box {
		padding:10px 10px 0 10px;
	}

	#out-error-box .alert.alert-danger,
	.item-error .alert.alert-danger{
		border-radius:3px;
		background:#f2dede;
		border-color:#ebccd1;
		color: #a94442;
		box-shadow:none;
		text-shadow: none;
	}

	.item-error .alert.alert-danger {
		margin:10px 0;
	}

	.alert-danger a {
		color: #a94442;
		font-weight:bold;
	}

	.orderHasError {
		color:#a94442;
			background:#f2dede;
	}

	#out-info-box {
		padding:15px 10px 5px 10px;
	}

	#out-info-box ul {
		margin:0;
		padding:0;
	}
	#out-info-box ul li {
		display:inline-block;
		margin-right:20px;
		font-size:15px;
	}
	#out-info-box ul li .number {
		font-weight:bold;
	}


	#out-bank-account-select {
		padding:15px 10px 5px 10px;
		font-size:15px;
	}

	#out-invoice-date-config {
		padding:15px 10px 5px 10px;
	}

	#out-process-progress {
		padding:15px;
		display:none;
		font-size:15px;
	}

	#out-process-progress .progress-title {
		font-size:18px;
	}

	#out-process-progress .progress-bar2 {
		border-radius:4px;
		overflow: hidden;
		width:100%;
		min-height:25px;
		background:#F8F8F8;
		border:1px solid #EEE;
		margin-bottom:5px;
	}
	#out-process-progress .progress-bar2-fill {
		width:0;
		min-height:25px;
		background:#27ae60;
	}

	.out-select-all {
		font-size:15px;
		padding:5px 15px;
	}

</style>
<script type="text/javascript">
$(function() {
	var _vat = parseInt("<?php echo $l_vat;?>");

	$(".item-customer .item-title input[type=checkbox]").on("change", function(){
		$(this).closest(".item-customer").find(".item-order input[type=checkbox]").prop("checked", $(this).is(":checked"));
		out_calculate(this);
	});
	$(".item-customer .item-order input[type=checkbox]").on("change", function(){
		out_calculate(this);
	});

	$('#selectDeselectAll').on('change', function(event) {
		if ($(this).prop('checked')) {
			$('[name="customer[]"]').each(function (index,item) {
				if(!$(this).prop('checked')) $(this).trigger('click');
			});
		}
		else {
			$('[name="customer[]"]').each(function (index,item) {
				if($(this).prop('checked')) $(this).trigger('click');
			});
		}
	});

	$('#out-invoice-date-config input').datepicker({
		dateFormat: 'dd.mm.yy'
	});
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
		_customer.find(".item-totals .total-sum-hidden").val(_total);
		_customer.find(".item-totals .total-vat-hidden").val(_vat);
		_customer.find(".item-totals .total-total-hidden").val(_total + _vat);
		<?php
		if($batchinvoicing_accountconfig['forceConnectCreditInvoice']) { ?>
			if(_grand_total < 0) {
				$(".selectInvoice").show();
			} else {
				$(".selectInvoice").hide();
			}
		<?php } ?>

		$('.customersSelectedCountSpan').html($('.item-title input[type=checkbox]:checked').length);
	}

	var procLinesProcessed;
	var totalProcLines;
	$(".history-btn").unbind("click").bind("click", function(e){
        e.preventDefault();
        var data = {
            customerId: $(this).data('customer-id')
        };
        ajaxCall('invoice_history', data, function(json) {
            $('#popupeditboxcontent2').html('');
            $('#popupeditboxcontent2').html(json.html);
            out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
            $("#popupeditbox2:not(.opened)").remove();
        });
	})
	$(".selectInvoice label").unbind("click").bind("click", function(e){
        e.preventDefault();
        var data = {
            customerId: $(this).parent().data('customer-id')
        };
        ajaxCall('get_invoices', data, function(json) {
            $('#popupeditboxcontent2').html('');
            $('#popupeditboxcontent2').html(json.html);
            out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
            $("#popupeditbox2:not(.opened)").remove();
        });
	})
	$(".selectInvoice .resetInvoiceConnection").unbind("click").bind("click", function(e){
        e.preventDefault();
        var parent = $(this).parent();
        parent.find("label").html("<?php echo $formText_SelectInvoice_output;?>");
        parent.find(".resetInvoiceConnection").hide();
        parent.find("#creditRefNo").val("");
	})
	$("#out-invoice-create").on("click", function(){
		var fromString = $('#out-invoice-date-config input').eq(0).val();
		var toString = $('#out-invoice-date-config input').eq(1).val();
		if(fromString != "" && toString != ""){
			var fromArray = fromString.split(".");
			var toArray = toString.split(".");
			var from = new Date(parseInt(fromArray[1])+"/"+parseInt(fromArray[0])+"/"+parseInt(fromArray[2]));
			var to = new Date(parseInt(toArray[1])+"/"+parseInt(toArray[0])+"/"+parseInt(toArray[2]));
			if(from <= to){
				$('#out-invoice-create').text('<?php echo $formText_InvoicingStartedPleaseWait_Output; ?>...');
				$("#out-error-box").html("");
				if(!fw_click_instance)
				{
					$("#popupeditbox .button.b-close").hide();
					$(".progress-info").show();
					fw_click_instance = true;
					var valid = false;
					if($("#creditRefNo").length > 0 && $(".selectInvoice").is(":visible")){
						if($("#creditRefNo").val() > 0){
							$("#creditRefNo").parent().find(".error").addClass("hidden");
							valid = true;
						}
					} else {
						valid = true;
					}
					if(valid){
						$.ajax({
							cache: false,
							type: 'POST',
							dataType: 'json',
							url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=BatchInvoicing&folderfile=output&folder=output&inc_obj=ajax&inc_act=create_invoices";?>',
							data: "fwajax=1&fw_nocss=1&" + $("#out-customer-list input").serialize() + '&' + $('#out-invoice-date-config input').eq(0).serialize() + '&' + $('#out-invoice-date-config input').eq(1).serialize()+ '&' + $('.selectInvoice input').eq(0).serialize(),
							success: function(obj){
								if(obj.html != ""){
									$('#out-error-box').html('');

									var procData = $.parseJSON(obj.html);
									if (procData.error) {
										if(procData.error.type == 1){
											$('#out-error-box').html('<?php echo $formText_AnotherBatchInvoicingAlreadyRunningPleaseWaitUntilItIsDone_output;?>');
										} else if(procData.error.type == 2){
											$('#out-error-box').html('<?php echo $formText_InvoicingFunctionalityLockedPleaseContactSupport_output;?>');
										} else {
											$('#out-error-box').html(procData.error.message);
										}
										$('#out-error-box').css({ color: 'red' });
										fw_click_instance = false;
									} else {
										totalProcLines = procData.lines.length;
										procLinesProcessed = 0;
										$('#out-customer-list').hide();
										$('#out-process-progress').show();
										$('#out-process-progress .total').html(totalProcLines);
										process_lines(procData);
									}
								}
							}
						}).fail(function() {
							fw_info_message_add("error", "<?php echo $formText_ErrorOccuredPleaseContactSupport_framework;?>", true, true);
							fw_click_instance = false;
							$("#popupeditbox .button.b-close").show();
							$(".progress-info").hide();
						});
					} else {
						$("#creditRefNo").parent().find(".errorLabel").removeClass("hidden");
					}
				}
			} else {
				$("#out-error-box").html("<div class='alert alert-danger'><?php echo $formText_DueDateCanNotBeLessThanInvoiceDate_output;?></div>");
			}
		} else {
			$("#out-error-box").html("<div class='alert alert-danger'><?php echo $formText_MissingInvoiceDates_output;?></div>");
		}
	});

	function process_lines(procData) {
		if (procData.lines[0]) {
			$.ajax({
				cache: false,
				type: 'POST',
				dataType: 'json',
				url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=BatchInvoicing&folderfile=output&folder=output&inc_obj=ajax&inc_act=create_invoices";?>',
				data: "fwajax=1&fw_nocss=1&procrunlineID="+procData.lines[0].procrunlineID+"&procrunID="+procData.procrunID+"&"+ $("#out-customer-list input").serialize() + "&bankAccountId=" + $('#bankAccountId').val() + '&' + $('#out-invoice-date-config input').eq(0).serialize() + '&' + $('#out-invoice-date-config input').eq(1).serialize()+ '&' + $('.selectInvoice input').eq(0).serialize(),
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
						process_lines(procData);
					}
				}
			}).fail(function() {
				fw_info_message_add("error", "<?php echo $formText_ErrorOccuredPleaseContactSupport_framework;?>", true, true);
				fw_click_instance = false;
				$("#popupeditbox .button.b-close").show();
				$(".progress-info").hide();
			});
		}
		else {
			show_finish();
		}
	}

	function show_finish() {
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=BatchInvoicing&folderfile=output&folder=output&inc_obj=ajax&inc_act=create_invoices";?>',
			data: "fwajax=1&fw_nocss=1&showFinish=1",
			success: function(obj){
				fw_click_instance = false;
				$('#out-process-progress').append(obj.html);
				$("#popupeditbox").addClass("close-reload");
				$("#popupeditbox .button.b-close").show();
				$(".progress-info").hide();
			}
		}).fail(function() {
			fw_info_message_add("error", "<?php echo $formText_ErrorOccuredPleaseContactSupport_framework;?>", true, true);
			fw_click_instance = false;
			$("#popupeditbox .button.b-close").show();
			$(".progress-info").hide();
		});
	}
	<?php
	$lastMonthDate = date('t.m.Y', strtotime("-1 month",time()));
	?>
	$(".fillinlastdate").off("click").on("click", function(){
		$(".invoice_date_input").val("<?php echo $lastMonthDate;?>");
		// $(".due_date_input").val("<?php echo date('d.m.Y', time() + 60*60*24*$creditTimeDays);?>");
	})
	$(".fillincurentdate").off("click").on("click", function(){
		$(".invoice_date_input").val("<?php echo date('d.m.Y', time());?>");
		// $(".due_date_input").val("<?php echo date('d.m.Y', time() + 60*60*24*$creditTimeDays);?>");
	})
	// $(".invoice_date_input").change(function(){
	// 	var date = $(this).val();
	// 	var dateArray = date.split(".");
	// 	var invoiceDate = new Date(parseInt(dateArray[1])+"/"+parseInt(dateArray[0])+"/"+parseInt(dateArray[2]));
	// 	invoiceDate.setDate(invoiceDate.getDate()+<?php echo $creditTimeDays?>);
	// 	console.log(invoiceDate);
	// 	$(".due_date_input").val("<?php echo date('d.m.Y', time() + 60*60*24*$creditTimeDays);?>");
	// })

});
</script>
