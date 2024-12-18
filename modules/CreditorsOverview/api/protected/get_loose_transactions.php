<?php
$creditor_id = $v_data['params']['creditor_id'];
$search_filter = $v_data['params']['search_filter'];
$page = $v_data['params']['page'];

$s_sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_id));
$creditor = ($o_query ? $o_query->row_array() : array());
if($creditor){
	$per_page = 50;
	$offset = 0;
	if($page > 1){
		$offset = ($page - 1) * $per_page;
	}
	$pager = " LIMIT ".$per_page." OFFSET ".$offset;
	if($search_filter != ""){
		$s_search_sql = " AND (c.name LIKE '%".$o_main->db->escape_like_str($search_filter)."%' 
		OR c.middlename LIKE '%".$o_main->db->escape_like_str($search_filter)."%' 
		OR c.lastname LIKE '%".$o_main->db->escape_like_str($search_filter)."%' 
		OR ct.invoice_nr = '".$o_main->db->escape_str($search_filter)."')";
	}
	$s_sql = "SELECT ct.*, concat_ws(' ', c.name, c.middlename, c.lastname) as debitorName FROM creditor_transactions ct 
	LEFT JOIN creditor_transactions ct2 ON ct2.link_id = ct.link_id AND ct2.collectingcase_id > 0
	LEFT JOIN creditor_transactions ct3 ON ct3.transaction_id = ct.comment
	JOIN customer c ON c.creditor_customer_id = ct.external_customer_id AND c.creditor_id = ct.creditor_id
	WHERE ct.open = 1 AND ct.creditor_id = ? AND (ct.system_type='InvoiceCustomer' OR ct.system_type='CreditnoteCustomer') AND ct2.id IS NULL 
	AND (ct.collectingcase_id is null OR ct.collectingcase_id = 0) AND (ct.comment LIKE '%reminderFee_%' OR ct.comment LIKE '%interest_%' OR (ct.comment LIKE '%-%-%-%-%' AND ct3.`open` = 0))
	".$s_search_sql."
	ORDER BY c.name ASC";
	$o_query = $o_main->db->query($s_sql.$pager, array($creditor['id']));
	$openFeesWithoutConnection = $o_query ? $o_query->result_array() : array();

	$o_query = $o_main->db->query($s_sql, array($creditor['id']));
	$openFeesWithoutConnectionTotalCount = $o_query ? $o_query->num_rows() : array();
	if($openFeesWithoutConnectionTotalCount == 0){
		$s_sql = "UPDATE creditor SET has_loose_transactions = 0 WHERE id = ?";
		$o_query = $o_main->db->query($s_sql.$pager, array($creditor['id']));
	}
	$v_return['creditor'] = $creditor;
	$v_return['loose_transactions'] = $openFeesWithoutConnection;
	$v_return['loose_transactions_all_count'] = $openFeesWithoutConnectionTotalCount;
	$v_return['total_pages'] = ceil($openFeesWithoutConnectionTotalCount/$per_page);
	
	$v_return['status'] = 1;
}
?>
