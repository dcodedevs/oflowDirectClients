<?php 

$debitor_id= $v_data['params']['debitor_id'];
$creditor_filter= $v_data['params']['creditor_filter'];
include(dirname(__FILE__).'/../languagesOutput/no.php');

$s_sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_filter));
$creditor = ($o_query ? $o_query->row_array() : array());

$s_sql = "SELECT *, concat_ws(' ',customer.name, customer.middlename, customer.lastname) as fullName FROM customer WHERE creditor_id = ? AND id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_filter, $debitor_id));
$debitor = ($o_query ? $o_query->row_array() : array());

$s_sql = "SELECT * FROM creditor_transactions ct WHERE creditor_id = ? AND external_customer_id = ? AND IFNULL(open, 0) = 1 
AND system_type='InvoiceCustomer' AND (ct.comment is null OR (ct.comment NOT LIKE '%reminderFee_%' AND ct.comment NOT LIKE '%interest_%')) ";
$o_query = $o_main->db->query($s_sql, array($creditor['id'], $debitor['creditor_customer_id']));
$creditor_transactions = ($o_query ? $o_query->result_array() : array());
if(count($creditor_transactions) == 0) {
    $v_return['error'] = $formText_NoUnpaidInvoices_api;
}
?>