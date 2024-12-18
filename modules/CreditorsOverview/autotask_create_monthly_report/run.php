<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
ob_start();
$v_return = array(
	'status' => 0,
	'messages' => array(),
);
require_once('Exception.php');
require_once('PHPMailer.php');
require_once('SMTP.php');

define('BASEPATH', realpath(__DIR__.'/../../../').'/');
require_once(BASEPATH.'elementsGlobal/cMain.php');
include_once(__DIR__."/includes/readOutputLanguage.php");
include_once(__DIR__."/../output/includes/readOutputLanguage.php");

$v_input = $_SERVER['argv'];
list($s_script_path, $l_auto_task_id) = $v_input;
$s_sql = "SELECT at.*, atl.id AS auto_task_log_id FROM auto_task at JOIN auto_task_log atl ON atl.auto_task_id = at.id WHERE at.id = '".$o_main->db->escape_str($l_auto_task_id)."' AND atl.status = 1";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0)
{
	$v_auto_task = $o_query->row_array();
	$o_main->db->query("UPDATE auto_task_log SET status = 2, started = NOW() WHERE id = '".$o_main->db->escape_str($v_auto_task['auto_task_log_id'])."'");
	$v_auto_task_config = json_decode($v_auto_task['config'], TRUE);

	$time_for_launch = "H:i";
	if($v_auto_task_config['runtime_h'] != null){
		$time_for_launch = $v_auto_task_config['runtime_h'];

		if($v_auto_task_config['runtime_i'] != null){
			$time_for_launch .= ":".$v_auto_task_config['runtime_i'];
		} else {
			$time_for_launch .= ":i";
		}
	}
	

	$sql = "SELECT cr.*, cr.companyname as creditorName, IFNULL(cr.last_report_date, '0000-00-00') as last_report_date FROM creditor cr
	WHERE DATE(IFNULL(cr.last_report_date, '0000-00-00')) < LAST_DAY(DATE_ADD(CURDATE(), INTERVAL -1 MONTH))
	ORDER BY id ASC LIMIT 500";
	$o_query = $o_main->db->query($sql);
	$creditors = $o_query ? $o_query->result_array() : array();

	$s_sql = "SELECT * FROM collecting_system_settings";
	$o_query = $o_main->db->query($s_sql);
	$collecting_system_settings = $o_query ? $o_query->row_array() : array();
	include_once(__DIR__."/../output/languagesOutput/no.php");
	
	$s_sql = "SELECT cs_bookaccount.* FROM cs_bookaccount
	WHERE cs_bookaccount.content_status < 2 AND cs_bookaccount.number IN (3000, 3001, 3003, 3004, 3100, 3200, 3300, 3500, 3600, 3601, 3602, 3900)
	ORDER BY cs_bookaccount.number ASC";
	$o_query = $o_main->db->query($s_sql);
	$bookaccounts = $o_query ? $o_query->result_array() : array();

	foreach($creditors as $creditor) {
		$month_back = 0;
		$month_back2 = 0;
		if($creditor['last_report_date'] == "0000-00-00"){
			$s_sql = "SELECT cc.* FROM collecting_cases cc
			WHERE cc.creditor_id = ? AND IFNULL(cc.created, '0000-00-00 00:00:00') <> '0000-00-00 00:00:00' ORDER BY cc.created ASC";
			$o_query = $o_main->db->query($s_sql, array($creditor['id']));
			$first_collecting_case = $o_query ? $o_query->row_array() : array();
			if($first_collecting_case) {
				$first_case_date_time = new DateTime($first_collecting_case['created']);
				$current_time = new DateTime(date("Y-m-d"));
				$interval = $current_time->diff($first_case_date_time);
				$month_back = (($interval->format('%y') * 12) + $interval->format('%m') + (($interval->format('%d') > 0) ? 1 : 0));
                        
			}
			$s_sql = "SELECT cc.* FROM collecting_company_cases cc
			WHERE cc.creditor_id = ? AND IFNULL(cc.created, '0000-00-00 00:00:00') <> '0000-00-00 00:00:00' ORDER BY cc.created ASC";
			$o_query = $o_main->db->query($s_sql, array($creditor['id']));
			$first_collecting_company_case = $o_query ? $o_query->row_array() : array();
			if($first_collecting_company_case) {
				$first_case_date_time = new DateTime($first_collecting_company_case['created']);
				$current_time = new DateTime(date("Y-m-d"));
				$interval = $current_time->diff($first_case_date_time);
				$month_back2 = (($interval->format('%y') * 12) + $interval->format('%m') + (($interval->format('%d') > 0) ? 1 : 0));
                        
			}
			if($month_back2 > $month_back){
				$month_back = $month_back2;
			}

			if($month_back <= 0){
				$month_back = 1;
			}
		} else {
			$month_back = 1;
		}
		$month_start_time = strtotime("-".$month_back." months");
				
		for($x=0; $x<$month_back; $x++){
			$month_time = strtotime("+".$x." months", $month_start_time);
			$month_start = date("Y-m-01", $month_time);
			$month_end = date("Y-m-t", $month_time); 

			$sql_where = " AND ccc.creditor_id = '".$o_main->db->escape_str($creditor['id'])."' 
			AND cmv.date >= '".$o_main->db->escape_str($month_start)."'
			AND cmv.date <= '".$o_main->db->escape_str($month_end)."'";
			$s_sql = "SELECT cs_bookaccount.*, SUM(cmt.amount) as totalAmount FROM cs_bookaccount
			JOIN cs_mainbook_transaction cmt ON cmt.bookaccount_id = cs_bookaccount.id
			JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
			JOIN collecting_company_cases ccc ON ccc.id = cmv.case_id
			WHERE cs_bookaccount.content_status < 2 ".$sql_where."
			GROUP BY cs_bookaccount.id ORDER BY cs_bookaccount.number ASC";
			$o_query = $o_main->db->query($s_sql);
			$bookaccountreports = $o_query ? $o_query->result_array() : array();
			$result_bookaccounts = array();
			foreach($bookaccountreports as $bookaccountreport) {
				$result_bookaccounts[$bookaccountreport['id']] += $bookaccountreport['totalAmount'];                                
			} 

			$row_sum = 0;
			$fee_cwo = 0;
			$fee_cw = 0;
			$fee_pwo = 0;
			$fee_pw = 0;
			$forsinkelsesrente = 0;
			$purregebyr = 0;
			$overbetalt = 0;
			$hovedstol = 0;
			$avdragsgebyr = 0;
			$saerskilt = 0;
			$omkostningesrente = 0;
			$mva = 0;
			$mva_sum = 0;
			foreach($bookaccounts as $bookaccount) {     
				if($bookaccount['id'] == 6) {
					$fee_cwo = $result_bookaccounts[$bookaccount['id']]*-1;
				} else if($bookaccount['id'] == 5) {
					$fee_cw = $result_bookaccounts[$bookaccount['id']]*-1;
				} else if($bookaccount['id'] == 4) {
					$fee_pwo = $result_bookaccounts[$bookaccount['id']]*-1;
				} else if($bookaccount['id'] == 3) {
					$fee_pw = $result_bookaccounts[$bookaccount['id']]*-1;
				} else if($bookaccount['id'] == 7) {
					$forsinkelsesrente = $result_bookaccounts[$bookaccount['id']]*-1;
				} else if($bookaccount['id'] == 2) {
					$purregebyr = $result_bookaccounts[$bookaccount['id']]*-1;
				} else if($bookaccount['id'] == 29) {
					$overbetalt = $result_bookaccounts[$bookaccount['id']]*-1;
				} else if($bookaccount['id'] == 19) {
					$hovedstol = $result_bookaccounts[$bookaccount['id']]*-1;
				} else if($bookaccount['id'] == 13) {
					$saerskilt = $result_bookaccounts[$bookaccount['id']]*-1;
				} else if($bookaccount['id'] == 12) {
					$avdragsgebyr = $result_bookaccounts[$bookaccount['id']]*-1;
				} else if($bookaccount['id'] == 11) {
					$omkostningesrente = $result_bookaccounts[$bookaccount['id']]*-1;
				} else if($bookaccount['id'] == 14) {
					$mva = $result_bookaccounts[$bookaccount['id']]*-1;
				}
				// if($bookaccount['id'] == 14) {
				// 	//ignore mva for sum
				// } else if($bookaccount['id'] == 6) {    
				// 	//extract mva from CWO 
				// 	$row_sum += $result_bookaccounts[$bookaccount['id']]/5*4;
				// } else {
				// 	$row_sum += $result_bookaccounts[$bookaccount['id']];
				// }
				if($bookaccount['id'] != 14) {
					$row_sum += $result_bookaccounts[$bookaccount['id']];
				}
				if($bookaccount['id'] == 14) {
					$mva_sum += $result_bookaccounts[$bookaccount['id']];
				}
			}
			//extract mva;
			$row_sum -= $mva_sum;

			$s_sql = "SELECT id FROM creditor_report_collecting WHERE creditor_id = ? AND date = ?";
			$o_query = $o_main->db->query($s_sql, array($creditor['id'], $month_end));
			$creditor_report_collecting = $o_query ? $o_query->row_array() : array();
			$sevenoffice_client_id = $creditor['24sevenoffice_client_id'];
			if(!$creditor_report_collecting) {
				$s_sql = "INSERT INTO creditor_report_collecting SET
					created = NOW(),
					creditor_id = ?,
					date = ?,
					fee_cwo = ?,
					fee_cw=?,
					fee_pwo=?,
					fee_pw=?,
					forsinkelsesrente = ?,
					purregebyr = ?,
					overbetalt = ?,
					hovedstol = ?,
					avdragsgebyr = ?,
					saerskilt = ?,
					omkostningesrente = ?,
					mva = ?,
					summary = ?,
					24sevenoffice_client_id = ?
				";
				$o_query = $o_main->db->query($s_sql, array($creditor['id'], $month_end, $fee_cwo, $fee_cw, $fee_pwo, 
				$fee_pw, $forsinkelsesrente, $purregebyr, $overbetalt,$hovedstol,$avdragsgebyr, $saerskilt, $omkostningesrente, $mva, $row_sum*-1, $sevenoffice_client_id));
			} else {
				$s_sql = "UPDATE creditor_report_collecting SET
					creditor_id = ?,
					date = ?,
					fee_cwo = ?,
					fee_cw=?,
					fee_pwo=?,
					fee_pw=?,
					forsinkelsesrente = ?,
					purregebyr = ?,
					overbetalt = ?,
					hovedstol = ?,
					avdragsgebyr = ?,
					saerskilt = ?,
					omkostningesrente = ?,
					mva = ?,
					summary = ?,
					24sevenoffice_client_id = ?
					WHERE id = ?
				";
				$o_query = $o_main->db->query($s_sql, array($creditor['id'], $month_end, $fee_cwo, $fee_cw, $fee_pwo, 
				$fee_pw, $forsinkelsesrente, $purregebyr, $overbetalt,$hovedstol,$avdragsgebyr, $saerskilt, $omkostningesrente, $mva, $row_sum*-1, $sevenoffice_client_id, $creditor_report_collecting['id']));
			}


			$price_per_print = 0;
			$price_per_fees = 0;

			$s_sql = "SELECT cpl.* FROM creditor_price_list cpl WHERE cpl.date_from <= ? ORDER BY cpl.date_from DESC";
			$o_query = $o_main->db->query($s_sql, array($month_start));
			$price_per_print_item = $o_query ? $o_query->row_array(): array();
			if($price_per_print_item){
				$price_per_print = $price_per_print_item['price_per_print'];
				$price_per_fees = $price_per_print_item['price_per_fee'];
			}

			$s_sql = "SELECT SUM(ccrs.sent_without_fees_amount) as sum_sent_without_fees_amount, SUM(ccrs.fees_forgiven_amount) as sum_fees_forgiven_amount,
			SUM(ccrs.total_fee_and_interest_billed) as sum_total_fee_and_interest_billed,
			SUM(ccrs.fee_payed_amount) as sum_fee_payed_amount,
			SUM(ccrs.interest_payed_amount) as sum_interest_payed_amount,
			SUM(ccrs.printed_amount) as sum_printed_amount
			FROM collecting_cases_report_24so ccrs 
			WHERE ccrs.date >= ? AND ccrs.date <= ? AND ccrs.creditor_id = ?";
			$o_query = $o_main->db->query($s_sql, array($month_start,$month_end, $creditor['id']));
			$report_sum_data = $o_query ? $o_query->row_array(): array();
			$lettersSentWithoutFeeCount = $report_sum_data['sum_sent_without_fees_amount'];
			$feesForgivenCount = $report_sum_data['sum_fees_forgiven_amount'];
			$total_fee_and_interest_billed=$report_sum_data['sum_total_fee_and_interest_billed'];
			$total_fee_payed =$report_sum_data['sum_fee_payed_amount'];
			$total_interest_payed =$report_sum_data['sum_interest_payed_amount'];
			$total_printed =$report_sum_data['sum_printed_amount'];
			                            

			$total_print_amount = $total_printed * $price_per_print;
			$total_fees_amount = ($lettersSentWithoutFeeCount + $feesForgivenCount)* $price_per_fees;
			$total_income_amount = $total_fee_and_interest_billed+$total_print_amount+$total_fees_amount;

			$s_sql = "SELECT id FROM creditor_report_reminder WHERE creditor_id = ? AND date = ?";
			$o_query = $o_main->db->query($s_sql, array($creditor['id'], $month_end));
			$creditor_report_collecting = $o_query ? $o_query->row_array() : array();
			if(!$creditor_report_collecting){
				$s_sql = "INSERT INTO creditor_report_reminder SET
					created = NOW(),
					creditor_id = ?,
					date = ?,
					sent_without_fees=?,
					fees_forgiven=?,
					fees_payed=?,
					interest_payed = ?,
					total_printed = ?,
					total_interest_and_fee_billed = ?,
					total_print = ?,
					total_fee = ?,
					total_income = ?";
				$o_query = $o_main->db->query($s_sql, array($creditor['id'], $month_end, $lettersSentWithoutFeeCount, $feesForgivenCount,
				$total_fee_payed, $total_interest_payed, $total_printed, $total_fee_and_interest_billed, $total_print_amount, 
				$total_fees_amount,$total_income_amount));
			} else {
				$s_sql = "UPDATE creditor_report_reminder SET
					creditor_id = ?,
					date = ?,
					sent_without_fees=?,
					fees_forgiven=?,
					fees_payed=?,
					interest_payed = ?,
					total_printed = ?,
					total_interest_and_fee_billed = ?,
					total_print = ?,
					total_fee = ?,
					total_income = ?
					WHERE id = ?
				";
				$o_query = $o_main->db->query($s_sql, array($creditor['id'],$month_end, $lettersSentWithoutFeeCount, $feesForgivenCount,
				$total_fee_payed, $total_interest_payed, $total_printed, $total_fee_and_interest_billed, $total_print_amount, 
				$total_fees_amount,$total_income_amount, $creditor_report_collecting['id']));
			}
			$s_sql = "UPDATE creditor SET last_report_date = '".$o_main->db->escape_str($month_end)."' WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($creditor['id']));	

		}     
	}

	$sql = "SELECT cr.*, cr.companyname as creditorName FROM creditor cr
	WHERE DATE(IFNULL(cr.last_report_date, '0000-00-00')) <  LAST_DAY(DATE_ADD(CURDATE(), INTERVAL -1 MONTH))
	ORDER BY id ASC LIMIT 1";
	$o_query = $o_main->db->query($sql);
	$leftCreditors = $o_query ? $o_query->num_rows() : 0;
	if($leftCreditors > 0) {
		$l_next_run = strtotime($v_auto_task['next_run']) + 60;
	} else {
		$l_next_run = strtotime("+1 month", strtotime($v_auto_task['next_run']));
		$l_next_run = strtotime(date("01.m.Y 3:00", $l_next_run));
	}

	$o_main->db->query("UPDATE auto_task SET last_run = next_run, next_run = '".$o_main->db->escape_str(date("Y.m.d H:i:s", $l_next_run))."'".$finishedStatusSql." WHERE id = '".$o_main->db->escape_str($v_auto_task['id'])."'");
	//status (0 - Idle (only for showing in web), 1 - Queued, 2 - Running, 3 - Completed)
	$o_main->db->query("UPDATE auto_task_log SET status = 3, finished = NOW(), message = ? WHERE id = '".$o_main->db->escape_str($v_auto_task['auto_task_log_id'])."'", array(''));

	$v_return['status'] = 1;
} else {
	$v_return['messages'][] = $formText_AutoTaskCannotBeFound_Output;
}

ob_end_clean();
echo json_encode($v_return);
