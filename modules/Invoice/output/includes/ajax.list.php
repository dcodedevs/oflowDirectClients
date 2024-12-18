<?php
require_once __DIR__ .'/functions.php';

$invoices = get_invoices($o_main, array(
    'company_filter' => $_POST['company_filter'],
    'search_filter' => $_POST['search_filter'],
    'page' => $_POST['page'],
    'per_page' => $invoice_accountconfig['invoices_per_page'] ? $invoice_accountconfig['invoices_per_page'] : 100
));

$return['invoices'] = $invoices;

$htmlReturn = $_POST['htmlReturn'] ? $_POST['htmlReturn'] : 'rows';

$v_country = array();
$v_response = json_decode(APIconnectorOpen("countrylistget"), TRUE);
if(isset($v_response['status']) && $v_response['status'] == 1)
{
	foreach($v_response['data'] as $v_item)
	{
		$v_country[$v_item['countryID']] = $v_item['name'];
	}
}

showListHtml($invoices, $htmlReturn, $invoice_accountconfig['activate_global_export']);
