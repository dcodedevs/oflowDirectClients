<?php 
$filter_date_from = '2024-01-01';
$filter_date_to = '2024-01-31';
$s_sql = "SELECT cs_bookaccount.*, SUM(cmt.amount) as totalAmount, ccc.creditor_id FROM cs_bookaccount
JOIN cs_mainbook_transaction cmt ON cmt.bookaccount_id = cs_bookaccount.id
JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
JOIN collecting_company_cases ccc ON ccc.id = cmv.case_id
WHERE cs_bookaccount.content_status < 2 
AND cmv.date >= ?
AND cmv.date <= ?
AND ABS(cmt.amount) > 0 AND cs_bookaccount.id = 7
GROUP BY ccc.creditor_id ORDER BY cs_bookaccount.number ASC";

$o_query = $o_main->db->query($s_sql, array($filter_date_from, $filter_date_to));
$creditors_report_data = $o_query ? $o_query->result_array() : array();
foreach($creditors_report_data as $creditor_report_data){
    $sql = "SELECT SUM(crc.summary) as collecting_sum, SUM(crc.saerskilt) as sum_saerskilt, 
    SUM(crc.fee_cwo) as sum_fee_cwo, SUM(crc.fee_cw) as sum_fee_cw, SUM(crc.fee_pwo) as sum_fee_pwo, 
    SUM(crc.fee_pw) as sum_fee_pw, SUM(crc.forsinkelsesrente) as sum_forsinkelsesrente, 
    SUM(crc.purregebyr) as sum_purregebyr, SUM(crc.overbetalt) as sum_overbetalt, 
    SUM(crc.hovedstol) as sum_hovedstol, SUM(crc.avdragsgebyr) as sum_avdragsgebyr, 
    SUM(crc.omkostningesrente) as sum_omkostningesrente, 
    SUM(crc.mva) as sum_mva
    FROM creditor_report_collecting crc WHERE crc.creditor_id = ? AND crc.date >= ? AND crc.date <= ? GROUP BY crc.creditor_id";
    $o_query = $o_main->db->query($sql, array($creditor_report_data['creditor_id'], $filter_date_from, $filter_date_to));
    $creditor_report_collecting = $o_query ? $o_query->row_array() : array();

    $total_amount = $creditor_report_collecting['sum_forsinkelsesrente'];
    if(abs($creditor_report_data['totalAmount']) != $total_amount){
        echo abs($creditor_report_data['totalAmount'])." - ".$total_amount." ".  $creditor_report_data['creditor_id']." with difference<br/>";
    }
}
?>