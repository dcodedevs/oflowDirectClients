<?php
$creditor_id = $v_data['params']['creditor_id'];
$date = $v_data['params']['date'];
$list_filter = $v_data['params']['list_filter'];


$s_sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_id));
$creditor = ($o_query ? $o_query->row_array() : array());
if($creditor){
	$exported_sql = " AND IFNULL(ccrs.printed_amount_reported, '0000-00-00 00:00:00') <> '0000-00-00 00:00:00'
 	AND IFNULL(ccrs.sent_without_fees_reported, '0000-00-00 00:00:00') <> '0000-00-00 00:00:00'
  	AND IFNULL(ccrs.total_fees_payed_reported, '0000-00-00 00:00:00') <> '0000-00-00 00:00:00'
   	AND IFNULL(ccrs.fees_forgiven_amount_reported, '0000-00-00 00:00:00') <> '0000-00-00 00:00:00'";


	if($v_data['params']['get_dates']) {
		$s_sql = "SELECT DATE_FORMAT(DATE(GREATEST(IFNULL(printed_amount_reported, '0000-00-00 00:00:00'),
		IFNULL(sent_without_fees_reported, '0000-00-00 00:00:00'),
		IFNULL(total_fees_payed_reported, '0000-00-00 00:00:00'),
		IFNULL(fees_forgiven_amount_reported, '0000-00-00 00:00:00'))), '%m.%Y') as exported_date
		FROM collecting_cases_report_24so ccrs
		WHERE ccrs.creditor_id = ?".$exported_sql."
		GROUP BY exported_date ORDER BY STR_TO_DATE(CONCAT('01', '.', exported_date), '%d.%m.%Y') DESC";
		$o_query = $o_main->db->query($s_sql, array($creditor['id']));
		$dates = ($o_query ? $o_query->result_array() : array());

		$v_return['creditor'] = $creditor;
		$v_return['dates'] = $dates;
	} else {
		$feesForgivenCount = 0;
		$feesPaidCount = 0;
		$printedCount = 0;


		$s_sql = "SELECT cccl.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName, ct.invoice_nr as invoice_nr
		FROM collecting_cases_claim_letter cccl
		JOIN collecting_cases_report_24so ccrs ON ccrs.id = cccl.billing_report_id
		LEFT OUTER JOIN collecting_cases cc ON cc.id = cccl.case_id
		LEFT OUTER JOIN creditor_transactions ct ON ct.collectingcase_id = cc.id
		LEFT OUTER JOIN customer c ON c.id = cc.debitor_id
		WHERE ccrs.creditor_id = ? AND cccl.fees_status = 1 AND DATE(GREATEST(IFNULL(printed_amount_reported, '0000-00-00 00:00:00'),
		IFNULL(sent_without_fees_reported, '0000-00-00 00:00:00'),
		IFNULL(total_fees_payed_reported, '0000-00-00 00:00:00'),
		IFNULL(fees_forgiven_amount_reported, '0000-00-00 00:00:00'))) >= ? AND DATE(GREATEST(IFNULL(printed_amount_reported, '0000-00-00 00:00:00'),
		IFNULL(sent_without_fees_reported, '0000-00-00 00:00:00'),
		IFNULL(total_fees_payed_reported, '0000-00-00 00:00:00'),
		IFNULL(fees_forgiven_amount_reported, '0000-00-00 00:00:00'))) <= ?";
		$o_query_without_fee = $o_main->db->query($s_sql, array($creditor_id, date("Y-m-01", strtotime("01.".$date)),date("Y-m-t", strtotime("01.".$date))));
		$sentWithoutFeesCount = $o_query_without_fee ? $o_query_without_fee->num_rows() : 0;

		$s_sql = "SELECT cccl.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName, ct.invoice_nr as invoice_nr
		FROM collecting_cases_claim_letter cccl
		JOIN collecting_cases_report_24so ccrs ON ccrs.id = cccl.billing_report_id
		LEFT OUTER JOIN collecting_cases cc ON cc.id = cccl.case_id
		LEFT OUTER JOIN creditor_transactions ct ON ct.collectingcase_id = cc.id
		LEFT OUTER JOIN customer c ON c.id = cc.debitor_id
		WHERE ccrs.creditor_id = ? AND IFNULL(cccl.performed_action, 0) = 0 AND cccl.sent_to_external_company = 1 AND cccl.sending_status = 1 AND DATE(GREATEST(IFNULL(printed_amount_reported, '0000-00-00 00:00:00'),
		IFNULL(sent_without_fees_reported, '0000-00-00 00:00:00'),
		IFNULL(total_fees_payed_reported, '0000-00-00 00:00:00'),
		IFNULL(fees_forgiven_amount_reported, '0000-00-00 00:00:00'))) >= ? AND DATE(GREATEST(IFNULL(printed_amount_reported, '0000-00-00 00:00:00'),
		IFNULL(sent_without_fees_reported, '0000-00-00 00:00:00'),
		IFNULL(total_fees_payed_reported, '0000-00-00 00:00:00'),
		IFNULL(fees_forgiven_amount_reported, '0000-00-00 00:00:00'))) <= ?";
		$o_query_printed = $o_main->db->query($s_sql, array($creditor_id, date("Y-m-01", strtotime("01.".$date)),date("Y-m-t", strtotime("01.".$date))));
		$printedCount = $o_query_printed ? $o_query_printed->num_rows() : 0;


		$s_sql = "SELECT cccl.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName, ct.invoice_nr as invoice_nr
		FROM collecting_cases_claim_letter cccl
		JOIN collecting_cases cc ON cc.id = cccl.case_id
		JOIN collecting_cases_report_24so ccrs ON ccrs.id = cc.billing_report_id
		LEFT OUTER JOIN creditor_transactions ct ON ct.collectingcase_id = cc.id
		LEFT OUTER JOIN customer c ON c.id = cc.debitor_id
		WHERE ccrs.creditor_id = ? AND IFNULL(cccl.fees_status, 0) = 0  AND IFNULL(cc.fees_forgiven, 0) = 1 AND DATE(GREATEST(IFNULL(printed_amount_reported, '0000-00-00 00:00:00'),
		IFNULL(sent_without_fees_reported, '0000-00-00 00:00:00'),
		IFNULL(total_fees_payed_reported, '0000-00-00 00:00:00'),
		IFNULL(fees_forgiven_amount_reported, '0000-00-00 00:00:00'))) >= ? AND DATE(GREATEST(IFNULL(printed_amount_reported, '0000-00-00 00:00:00'),
		IFNULL(sent_without_fees_reported, '0000-00-00 00:00:00'),
		IFNULL(total_fees_payed_reported, '0000-00-00 00:00:00'),
		IFNULL(fees_forgiven_amount_reported, '0000-00-00 00:00:00'))) <= ?";
		$o_query_forgiven = $o_main->db->query($s_sql, array($creditor_id, date("Y-m-01", strtotime("01.".$date)),date("Y-m-t", strtotime("01.".$date))));
		$feesForgivenCount = $o_query_forgiven ? $o_query_forgiven->num_rows() : 0;



		$s_sql = "SELECT cc.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName, ct.invoice_nr as invoice_nr FROM collecting_cases cc
		JOIN collecting_cases_report_24so ccrs ON ccrs.id = cc.billing_report_id
		LEFT OUTER JOIN creditor_transactions ct ON ct.collectingcase_id = cc.id
		LEFT OUTER JOIN customer c ON c.id = cc.debitor_id
		WHERE ccrs.creditor_id = ? AND IFNULL(cc.fees_forgiven, 0) = 0 AND DATE(GREATEST(IFNULL(printed_amount_reported, '0000-00-00 00:00:00'),
		IFNULL(sent_without_fees_reported, '0000-00-00 00:00:00'),
		IFNULL(total_fees_payed_reported, '0000-00-00 00:00:00'),
		IFNULL(fees_forgiven_amount_reported, '0000-00-00 00:00:00'))) >= ? AND DATE(GREATEST(IFNULL(printed_amount_reported, '0000-00-00 00:00:00'),
		IFNULL(sent_without_fees_reported, '0000-00-00 00:00:00'),
		IFNULL(total_fees_payed_reported, '0000-00-00 00:00:00'),
		IFNULL(fees_forgiven_amount_reported, '0000-00-00 00:00:00'))) <= ? GROUP BY cc.id";
		$o_query_paid = $o_main->db->query($s_sql, array($creditor_id, date("Y-m-01", strtotime("01.".$date)),date("Y-m-t", strtotime("01.".$date))));
		$feesPaidCount = $o_query_paid ? $o_query_paid->num_rows() : 0;

		if($list_filter == "sentWithoutFees") {
			$result = $o_query_without_fee ? $o_query_without_fee->result_array() : array();
		} else if($list_filter == "feesForgiven") {
			$result = $o_query_forgiven ? $o_query_forgiven->result_array() : array();
		} else if($list_filter == "feesPaid") {
			$result = $o_query_paid ? $o_query_paid->result_array() : array();
		} else if($list_filter == "printed") {
			$result = $o_query_printed ? $o_query_printed->result_array() : array();
		}


		$v_return['creditor'] = $creditor;
		$v_return['result'] = $result;

		$v_return['sentWithoutFeesCount'] = $sentWithoutFeesCount;
		$v_return['feesForgivenCount'] = $feesForgivenCount;
		$v_return['feesPaidCount'] = $feesPaidCount;
		$v_return['printedCount'] = $printedCount;
	}
	
	if($v_data['params']['get_summary']) {
		$s_sql = "SELECT price_per_print, price_per_fee, price_per_ehf FROM creditor_price_list WHERE date_from <= CURDATE()".($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"")." AND content_status < 2 ORDER BY date_from DESC LIMIT 1";
		$o_find = $o_main->db->query($s_sql);
		$v_return['price_list'] = $o_find ? $o_find->row_array() : array();
		
		$s_sql = "SELECT
		sum(fee_payed_amount) AS fee_payed_amount,
		SUM(sent_without_fees) AS sent_without_fees,
		SUM(interest_payed_amount) AS interest_payed_amount,
		SUM(printed_amount) AS printed_amount,
		SUM(sent_without_fees_amount) AS sent_without_fees_amount,
		SUM(fees_forgiven_amount) AS fees_forgiven_amount,
		SUM(total_fee_and_interest_billed) AS total_fee_and_interest_billed,
		SUM(ehf_amount) AS ehf_amount
		FROM collecting_cases_report_24so
		WHERE creditor_id = '".$o_main->db->escape_str($creditor_id)."' AND date >= '".$o_main->db->escape_str(date("Y-m-01", strtotime("01.".$date)))."' AND date <= '".$o_main->db->escape_str(date("Y-m-t", strtotime("01.".$date)))."'
		GROUP BY creditor_id";
		$o_summary = $o_main->db->query($s_sql);
		$v_return['summary'] = $o_summary ? $o_summary->row_array() : array();
		//$v_return['s_sql'] = $s_sql;
	}
	$v_return['status'] = 1;
}
?>
