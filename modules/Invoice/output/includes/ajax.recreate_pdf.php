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

		$s_sql = "SELECT id, log, attached_files FROM batch_invoicing WHERE id = ? ORDER BY id DESC";
		$o_query = $o_main->db->query($s_sql, array($v_invoice["batch_id"]));
		$v_active_batch = $o_query ? $o_query->row_array() : array();

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
				$orderToPass['priceTotal'] = $order['priceTotal'];
				$orderToPass['adminFee'] = $order['adminFee'];

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
				$mime_type = mime_content_type(__DIR__."/../../../../../".$file_to_attach[1][0]);
				if($mime_type != "application/pdf"){
					array_push($files_attached_without_pdf, $file_to_attach);
				} else {
					array_push($files_attached_pdf, __DIR__."/../../../../../".$file_to_attach[1][0]);
				}
			}
			$files_attached = $files_attached_without_pdf;
		}
		$files_from_batch = json_decode($v_active_batch['attached_files'], true);

		foreach($files_from_batch as $file_to_attach) {
			if(file_exists(__DIR__."/../../../../".$file_to_attach[1][0])){
				$mime_type = mime_content_type(__DIR__."/../../../../".$file_to_attach[1][0]);
				if($mime_type != "application/pdf"){
				} else {
					array_push($files_attached_pdf, __DIR__."/../../../../".$file_to_attach[1][0]);
				}
			}
		}
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
		$file = "invoice_".$newInvoiceNrOnInvoice;
	    if ($activateMultiOwnerCompanies)
		{
			$file = "invoice_oc".$ownercompany_id."_".$newInvoiceNrOnInvoice;
	    }

		$filepath = __DIR__."/../../../../uploads/protected/invoices_ehf/";

		$s_ehf_file = $file;
		$s_ehf_file_path = $filepath.$s_ehf_file.'.xml';

		$file .= ".pdf";
		$filepath = __DIR__."/../../../../uploads/protected/invoices/";
		if(!file_exists($filepath))
		{
			mkdir($filepath, 0777,true);
		}
		chmod($filepath, 0777);

		$invoicelogo = json_decode($v_settings['invoicelogo'],true);
		create_pdf($filepath, $file, $files_attached_pdf, $newInvoiceNrOnInvoice, $invoicelogo, $v_settings, $_GET['accountname'], $html, trim($v_settings['choose_custom_invoice_template']), $html_footer);

		$o_main->db->query("UPDATE invoice SET invoiceFile = ? WHERE id = ?", array("uploads/protected/invoices/".$file, $v_invoice['id']));

	}
}

?>
