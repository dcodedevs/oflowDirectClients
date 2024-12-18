<?php
$b_generate = (isset($_GET['generate']) && 1 == $_GET['generate']);
$sql_join = " LEFT OUTER JOIN collecting_company_case_paused paused ON paused.collecting_company_case_id = p.id AND IFNULL(paused.closed_date, '0000-00-00 00:00:00') = '0000-00-00 00:00:00'";
$s_sql_select = '';
/*$s_sql_select .= ",
DATE_ADD(IFNULL(p.due_date, '2000-01-01'), INTERVAL IFNULL(nextStep.days_after_due_date, 0)+IF(step2.id > 0, 0,cred.days_overdue_startcase) DAY) as nextStepDate,
IF(nextStep.id > 0, nextStep.name, '') as nextStepName, IF(nextStep.id > 0, nextStep.sending_action, '') as nextStepActionType, step2.name as processStepName, p.due_date as currentStepDate, nextStep.id as nextStepId,
nextContinuingStep.appear_in_legal_step_handling, nextContinuingStep.appear_in_call_debitor_step_handling,
DATE_ADD(IFNULL(p.due_date, '2000-01-01'), INTERVAL IFNULL(nextContinuingStep.days_after_due_date, 0) DAY) as nextContinuingStepDate,
IF(nextContinuingStep.id > 0, nextContinuingStep.name, '') as nextContinuingStepName";*/
$content_stats_sql = " p.content_status < 2";
$sql_where = " AND (IFNULL(p.collecting_case_created_date, '0000-00-00') <> '0000-00-00' OR IFNULL(p.collecting_case_autoprocess_date, '0000-00-00') <> '0000-00-00') AND IFNULL(p.case_closed_date, '0000-00-00') = '0000-00-00'";

$s_sql = "SELECT p.*,
	cred.companyname as creditor_name,
	cred.companyorgnr as creditor_org_no,
	c2.name as debitor_name,
	c2.paStreet as debitor_street,
	c2.paPostalNumber AS debitor_postal_no,
	c2.publicRegisterId AS debitor_org_no,
	IF(0 < c2.customer_type_collect_addition, c2.customer_type_collect_addition - 1, c2.customer_type_collect) AS debitor_customer_type_collect,
	rdb.id AS rdb_id,
	IFNULL(SUM(cccl.amount), 0) as totalAmountValue".$s_sql_select."
FROM collecting_company_cases p
JOIN creditor cred ON cred.id = p.creditor_id
LEFT JOIN customer c2 ON c2.id = p.debitor_id
LEFT JOIN collecting_company_cases_claim_lines cccl ON cccl.collecting_company_case_id = p.id
LEFT JOIN collecting_company_case_report_db rdb on rdb.collecting_company_case_id = p.id AND rdb.id = (
      SELECT MAX(t2.id)
      FROM collecting_company_case_report_db t2
      WHERE t2.collecting_company_case_id = p.id
   )
".$sql_join."
WHERE ".$content_stats_sql." ".$sql_where." GROUP BY p.id";
//echo $s_sql;exit;
$s_content = '';
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0)
foreach($o_query->result_array() as $v_case)
{
	$s_rec_type = 'N';
	if(0 < (int)$v_case['rdb_id'])
	{
		$s_rec_type = 'E';
		continue;
	}
	
	/*$totalMainClaim = 0;
    $totalClaim = 0;
    foreach($claims as $claim) {
        if(!$claim['payment_after_closed'] || $claim['claim_type'] != 15) {
            if($claim['claim_type'] == 1 || $claim['claim_type'] == 15 || $claim['claim_type'] == 16){
                $totalMainClaim += $claim['amount'];
            }
            $totalClaim += $claim['amount'];
        }
    }
	
	$totalPaymentForMain = 0;
    $totalPayment = 0;
    $s_sql = "SELECT cmt.* FROM cs_mainbook_transaction cmt
    LEFT OUTER JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
    WHERE cmv.case_id = ? AND (cmt.bookaccount_id = 1 OR cmt.bookaccount_id = 20) ORDER BY cmv.created DESC";
    $o_query = $o_main->db->query($s_sql, array($caseData['id']));
    $transactions = ($o_query ? $o_query->result_array() : array());
    foreach($transactions as $transaction) {
        if($transaction['bookaccount_id'] == 1) {
            $totalPayment += $transaction['amount'];
        } else if($transaction['bookaccount_id'] == 20) {
            $totalPaymentForMain += $transaction['amount'];
        }
    }*/
	if('N' == $s_rec_type)
	{
		$s_deb_type = 1 == $v_case['debitor_customer_type_collect'] ? 'P' : 'N';
		$s_deb_reg_no = substr(preg_replace('#[^0-9]+#', '', $v_case['debitor_org_no']), 0, 11);
		$s_deb_name = substr(iconv("UTF-8", "ASCII", preg_replace('/\s+/', '', $v_case['debitor_name'])), 0, 36);
		$s_deb_street = substr(iconv("UTF-8", "ASCII", preg_replace('/\s+/', '', $v_case['debitor_street'])), 0, 30);
		$s_deb_postal_no = substr(preg_replace('#[^0-9]+#', '', $v_case['debitor_postal_no']), 0, 4);
		$s_action_type = 'IN';
		$s_amount = (int)$v_case['totalAmountValue'];
		$s_reg_date = date('ymd', strtotime($v_case['collecting_case_created_date']));
		$s_source = 'FLOW';
		$s_ref_no = $v_case['id'];
		$s_settlement_date = '';
		$s_settlement_form = '';
		$s_creditor_name = substr(iconv("UTF-8", "ASCII", preg_replace('/\s+/', '', $v_case['creditor_name'])), 0, 36);
		$s_creditor_reg_no = substr(preg_replace('#[^0-9]+#', '', $v_case['debitor_org_no']), 0, 11);
		$s_line = sprintf("SP%-1s%-1s%'.011d%-36s%-30s%-4s%-2s%'.014d%-6s%-4s%'.013d%-6s%-1s%-36s%-11s\n",
			$s_rec_type, $s_deb_type, $s_deb_reg_no, $s_deb_name, $s_deb_street, $s_deb_postal_no, $s_action_type, $s_amount,
			$s_reg_date, $s_source, $s_ref_no, $s_settlement_date, $s_settlement_form, iconv("UTF-8", "ASCII", $s_creditor_name), $s_creditor_reg_no
		);
	}
	
	if($b_generate)
	{
		if(!isset($l_collecting_company_case_report_id))
		{
			$s_sql = "INSERT INTO collecting_company_case_report SET created = NOW(), createdBy = '".$o_main->db->escape_str($_COOKIE['username'])."', content_status = 1";
			$o_update = $o_main->db->query($s_sql);
			if(!$o_update) die("Error occurred");
			$l_collecting_company_case_report_id = $o_main->db->insert_id();
		}

		$s_sql = "INSERT INTO collecting_company_case_report_db SET
		collecting_company_case_report_id='".$o_main->db->escape_str($l_collecting_company_case_report_id)."',
		collecting_company_case_id='".$o_main->db->escape_str($v_case['id'])."',
		record_type='".$o_main->db->escape_str($s_rec_type)."',
		indentity_type='".$o_main->db->escape_str($s_deb_type)."',
		indentity_number='".$o_main->db->escape_str($s_deb_reg_no)."',
		`name`='".$o_main->db->escape_str($s_deb_name)."',
		address='".$o_main->db->escape_str($s_deb_street)."',
		postal_code='".$o_main->db->escape_str($s_deb_postal_no)."',
		action_type='".$o_main->db->escape_str($s_action_type)."',
		amount='".$o_main->db->escape_str($s_amount)."',
		registration_date='".$o_main->db->escape_str(date('Y-m-d', strtotime($s_reg_date)))."',
		`source`='".$o_main->db->escape_str($s_source)."',
		reference_number='".$o_main->db->escape_str($s_ref_no)."',
		settlement_date='".$o_main->db->escape_str($s_settlement_date)."',
		settlement_form='".$o_main->db->escape_str($s_settlement_form)."',
		creditor_name='".$o_main->db->escape_str($s_creditor_name)."',
		creditor_org_no='".$o_main->db->escape_str($s_creditor_reg_no)."'";
		$o_update = $o_main->db->query($s_sql);
		if(!$o_update) die("Error occurred");
	}
	
	$s_content .= $s_line;
	break;
}
ob_end_clean();
echo $s_content;

if($b_generate)
{
	$s_sql = "INSERT INTO uploads SET created = NOW(), createdBy = '".$o_main->db->escape_str($_COOKIE['username'])."', filename = 'export.txt', size = '".$o_main->db->escape_str(strlen($s_content))."'";
    $o_update = $o_main->db->query($s_sql);
    if(!$o_update) die("Error occurred");
    $l_upload_id = $o_main->db->insert_id();
	$s_path = 'uploads/protected/'.$l_upload_id.'/0/export.txt';
	$v_files = array(
		array('export.txt', array($s_path), array(), "", $l_upload_id),
	);
	$s_sql = "UPDATE collecting_company_case_report content_status = 0, file = '".$o_main->db->escape_str(json_encode($v_files))."' WHERE id = '".$o_main->db->escape_str($l_collecting_company_case_report_id)."'";
    $o_update = $o_main->db->query($s_sql);
}
exit;

