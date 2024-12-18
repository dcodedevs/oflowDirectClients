<?php
session_start();
//error_reporting(E_ALL | E_STRICT); ini_set("display_errors", 1);
// Constants (taken from fw/index.php)
define('FRAMEWORK_DEBUG', FALSE);
define('ACCOUNT_PATH', realpath(__DIR__.'/../../../../')); // this is modified to fit this files location
define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
$v_tmp = explode("/",ACCOUNT_PATH);
$accountname = array_pop($v_tmp);

// Load database
require_once BASEPATH . 'elementsGlobal/cMain.php';
$_GET['folder'] = 'output';
include_once(__DIR__.'/tcpdf/tcpdf.php');
include_once(__DIR__.'/readOutputLanguage.php');

function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

$bookaccount_id = isset($_GET['bookaccount_id']) ? $_GET['bookaccount_id'] : 0;
$date_from = isset($_GET['date_from']) && $_GET['date_from'] != "" ? $_GET['date_from'] : date("01.m.Y", strtotime("-1 month"));
$date_to = isset($_GET['date_to']) && $_GET['date_to'] != "" ? $_GET['date_to'] : date("t.m.Y", strtotime("-1 month"));
$view_by = isset($_GET['view_by']) ? $_GET['view_by'] : 0;
$creditor_id = isset($_GET['creditor_id']) ? $_GET['creditor_id'] : 0;
$debitor_id = isset($_GET['debitor_id']) ? $_GET['debitor_id'] : 0;


$s_sql = "SELECT * FROM cs_bookaccount WHERE content_status < 2 ORDER BY sortnr";
$o_query = $o_main->db->query($s_sql);
$bookaccounts = $o_query ? $o_query->result_array() : array();
$transactions = array();
if($view_by == 0){
	if($bookaccount_id > 0){
		$s_sql = "SELECT cmt.*, cmv.date, cmv.case_id, cred.companyname creditorName, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as debitorName
		FROM cs_mainbook_transaction cmt
		LEFT OUTER JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
		LEFT OUTER JOIN collecting_company_cases ccc ON ccc.id = cmv.case_id
		LEFT OUTER JOIN creditor cred ON cred.id = ccc.creditor_id
		LEFT OUTER JOIN customer c ON c.id = ccc.debitor_id
		WHERE cmt.content_status < 2 AND cmt.bookaccount_id = '".$o_main->db->escape_str($bookaccount_id)."'
		AND cmv.date >= '".$o_main->db->escape_str(date("Y-m-d", strtotime($date_from)))."' AND cmv.date <= '".$o_main->db->escape_str(date("Y-m-d", strtotime($date_to)))."'
		ORDER BY cmv.date DESC";
		$o_query = $o_main->db->query($s_sql);
		$transactions = $o_query ? $o_query->result_array() : array();
	}
} else if($view_by == 1) {
	if($creditor_id > 0) {
		$s_sql = "SELECT * FROM creditor WHERE id = '".$o_main->db->escape_str($creditor_id)."'";
		$o_query = $o_main->db->query($s_sql);
		$selected_creditor = $o_query ? $o_query->row_array() : array();

		$s_sql = "SELECT cmt.*, cmv.date, cmv.case_id, cred.companyname creditorName, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as debitorName
		FROM cs_mainbook_transaction cmt
		LEFT OUTER JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
		LEFT OUTER JOIN collecting_company_cases ccc ON ccc.id = cmv.case_id
		LEFT OUTER JOIN creditor cred ON cred.id = ccc.creditor_id
		LEFT OUTER JOIN customer c ON c.id = ccc.debitor_id
		WHERE cmt.content_status < 2
		AND cmv.date >= '".$o_main->db->escape_str(date("Y-m-d", strtotime($date_from)))."' AND cmv.date <= '".$o_main->db->escape_str(date("Y-m-d", strtotime($date_to)))."'
		AND cmt.creditor_id = '".$o_main->db->escape_str($creditor_id)."'
		ORDER BY cmv.date DESC";
		$o_query = $o_main->db->query($s_sql);
		$transactions = $o_query ? $o_query->result_array() : array();
	}
} else if($view_by == 2) {
	if($debitor_id > 0) {
		$s_sql = "SELECT * FROM customer WHERE id = '".$o_main->db->escape_str($debitor_id)."'";
		$o_query = $o_main->db->query($s_sql);
		$selected_debitor = $o_query ? $o_query->row_array() : array();

		$s_sql = "SELECT cmt.*, cmv.date, cmv.case_id, cred.companyname creditorName, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as debitorName
		FROM cs_mainbook_transaction cmt
		LEFT OUTER JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
		LEFT OUTER JOIN collecting_company_cases ccc ON ccc.id = cmv.case_id
		LEFT OUTER JOIN creditor cred ON cred.id = ccc.creditor_id
		LEFT OUTER JOIN customer c ON c.id = ccc.debitor_id
		WHERE cmt.content_status < 2
		AND cmv.date >= '".$o_main->db->escape_str(date("Y-m-d", strtotime($date_from)))."' AND cmv.date <= '".$o_main->db->escape_str(date("Y-m-d", strtotime($date_to)))."'
		AND cmt.debitor_id = '".$o_main->db->escape_str($selected_debitor['id'])."'
		ORDER BY cmv.date DESC";
		$o_query = $o_main->db->query($s_sql);
		$transactions = $o_query ? $o_query->result_array() : array();
	}
}
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor("");
$pdf->SetTitle("");
$pdf->SetSubject("");
$pdf->SetKeywords("");
$pdf->SetCompression(true);

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}

// add a page
$pdf->AddPage();

setlocale(LC_TIME, 'no_NO');
$pdf->SetFont('calibri', '', 9);

ob_start();
if(count($transactions) > 0)
{
	?>
	<h3><?php echo $formText_Transactions_Output.' ('.date("d.m.Y", strtotime($date_from)).' - '.date("d.m.Y", strtotime($date_to)).')';?></h3>
	<table width="100%" border="0" cellpadding="2" cellspacing="0">
		<thead>
		<tr>
			<th style="border:.5px thin #666666;" width="10%"><b><?php echo $formText_Date_output;?></b></th>
			<th style="border:.5px thin #666666;" width="10%"><b><?php echo $formText_Type_output;?></b></th>
			<th style="border:.5px thin #666666;" width="16%"><b><?php echo $formText_Bookaccount_output;?></b></th>
			<th style="border:.5px thin #666666;" width="10%"><b><?php echo $formText_Case_output;?></b></th>
			<th style="border:.5px thin #666666;" width="22%"><b><?php echo $formText_Creditor_output;?></b></th>
			<th style="border:.5px thin #666666;" width="22%"><b><?php echo $formText_Debitor_output;?></b></th>
			<th style="border:.5px thin #666666;" width="10%" align="right"><b><?php echo $formText_Amount_output;?></b></th>
		</tr>
		</thead>
		<?php
		$summary = 0;
		foreach($transactions as $paymentCoverline)
		{
			$total_amount = $paymentCoverline['amount'];

			$s_sql = "SELECT * FROM collecting_cases_claim_line_type_basisconfig WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($paymentCoverline['collecting_claim_line_type']));
			$claim_line_type = $o_query ? $o_query->row_array() : array();

			$s_sql = "SELECT * FROM cs_bookaccount WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($paymentCoverline['bookaccount_id']));
			$cs_bookaccount = $o_query ? $o_query->row_array() : array();
			$summary+= $total_amount;
			 ?>
			 <tr>
				 <td style="border:.5px thin #666666;" width="10%"><?php echo date("d.m.Y", strtotime($paymentCoverline['date']));?></td>
				 <td style="border:.5px thin #666666;" width="10%"><?php echo $claim_line_type['type_name'];?></td>
				 <td style="border:.5px thin #666666;" width="16%"><?php echo $cs_bookaccount['name']; ?></td>
				 <td style="border:.5px thin #666666;" width="10%"><?php echo $paymentCoverline['case_id']; ?></td>
				 <td style="border:.5px thin #666666;" width="22%"><?php echo $paymentCoverline['creditorName']?></td>
				 <td style="border:.5px thin #666666;" width="22%"><?php echo $paymentCoverline['debitorName']?></td>
				 <td style="border:.5px thin #666666;" width="10%" align="right"><?php echo number_format($total_amount, 2, ",", " "); ?></td>
			 </tr>
		<?php } ?>
		<tr>
			<td style="border:.5px thin #666666;" width="90%" colspan="6"><b><?php echo $formText_Total_output;?></b></td>
			<td style="border:.5px thin #666666;" width="10%" align="right"><b><?php echo number_format($summary, 2, ",", " "); ?></b></td>
		</tr>
	</table>
	<?php
} else {
	?><h3><?php echo $formText_NoTransactionsFound_output;?></h3><?php
}

$s_buffer = ob_get_clean();

$pdf->writeHTML($s_buffer, true, false, false, false, '');
//Close and save PDF document
$pdf->Output('transactions.pdf', 'I');
