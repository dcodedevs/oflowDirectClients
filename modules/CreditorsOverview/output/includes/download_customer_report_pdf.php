<?php
// error_reporting(E_ALL);
// ini_set("display_errors", 1);

ini_set('max_execution_time', 600);
// Constants (taken from fw/index.php)


session_start();
define('FRAMEWORK_DEBUG', FALSE);
define('ACCOUNT_PATH', realpath(__DIR__.'/../../../../')); // this is modified to fit this files location
define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
$v_tmp = explode("/",ACCOUNT_PATH);
$accountname = array_pop($v_tmp);
if(!$from_api){
	// Load database
	require_once __DIR__ . '/../../../../elementsGlobal/cMain.php';
	$v_path = explode("/", realpath(__DIR__."/../../"));
	$s_module = array_pop($v_path);

	$s_sql = "select * from session_framework where companyaccessID = ? and session = ? and username = ?".($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"");
	$o_query = $o_main->db->query($s_sql, array($_GET['caID'], $_COOKIE['sessionID'], $_COOKIE['username']));
	if($o_query && $o_query->num_rows()>0){
		$fw_session = $o_query->row_array();
	}
	$v_module_access = json_decode($fw_session['cache_menu'],true);
	$l_access = $v_module_access[$s_module][2];

	$cid = isset($_GET['cid']) ? $_GET['cid'] : '';
} else {
	ob_start();
}
if($l_access || $from_api){
	require_once(__DIR__.'/tcpdf/tcpdf.php');
	require_once(__DIR__.'/tcpdf-charts.php');
	require_once(__DIR__.'/../languagesOutput/no.php');
	require_once(__DIR__."/income_report_functions.php");
	if($cid > 0) {		
		$total_result = get_income_report($cid);
		$total_result_collecting = get_collecting_income_report($cid);
		$o_query = $o_main->db->query("SELECT * FROM creditor WHERE id = '".$o_main->db->escape_str($cid)."'");
		$creditor_info = $o_query ? $o_query->row_array() : array();
		class MYPDF extends TcpdfCharts {

			//Page header
			public function Header() {
				// Logo
				global $formText_ReportFor_output;
				global $creditor_info;

				$this->SetY(7);
				$this->SetTextColor(127,127,127);
				$this->SetFont('helvetica', '', 8);
				$this->MultiCell(0, 0, date("d.m.Y"), 0, 'L', 0, 1, "", "", true, 0, true);
				$this->Ln(1);
				$this->SetTextColor(0,0,0);
				$this->SetFont('helvetica', '', 12);
				$this->MultiCell(0, 0, $formText_ReportFor_output." ".$creditor_info['companyname'], 0, 'L', 0, 1, "", "", true, 0, true);

				$this->SetY(10);
				$image_file = ACCOUNT_PATH.'/modules/CreditorsOverview/output/elementsOutput/24sevenoffice_Logo_Horizontal_Midnight_RGB.png';
				$this->Image($image_file, 205, 10, 45, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
				$image_file = ACCOUNT_PATH.'/modules/CreditorsOverview/output/elementsOutput/Oflow Full Logo Black.png';
				$this->Image($image_file, 253, 10, 30, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
				// // Set font
				// $this->SetFont('helvetica', 'B', 20);
				// // Title
				// $this->Cell(0, 15, '<< TCPDF Example 003 >>', 0, false, 'C', 0, '', 0, false, 'M', 'M');
			}
		
			// Page footer
			public function Footer() {
				// // Position at 15 mm from bottom
				// $this->SetY(-15);
				// // Set font
				// $this->SetFont('helvetica', 'I', 8);
				// // Page number
				// $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
			}
		}

		$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor("");
		$pdf->SetTitle("");
		$pdf->SetSubject("");
		$pdf->SetKeywords("");

		// remove default header/footer
		$pdf->setPrintHeader(true);
		$pdf->setPrintFooter(false);
		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		$pdf->SetMargins(15, 25, 15);

		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, 20);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		// set some language-dependent strings (optional)
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			require_once(dirname(__FILE__).'/lang/eng.php');
			$pdf->setLanguageArray($l);
		}

	    // add a page
	    $pdf->AddPage();

	    function output_income_line($line, $pdf){

			$pdf->setCellPaddings(1, 1, 1, 1);
			$pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
	        
			$height = $pdf->getStringHeight(13, $line['date']);
			$height2 = $pdf->getStringHeight(14, number_format($line['original_main_claim_sum'], 0, ",", " "));
			$height = get_max_height($height, $height2);
			$height2 = $pdf->getStringHeight(10, $line['cases_started_in_period_count']);
			$height = get_max_height($height, $height2);
			$height2 = $pdf->getStringHeight(10, $line['step1_count']);
			$height = get_max_height($height, $height2);
			$height2 = $pdf->getStringHeight(10, $line['step2_count']);
			$height = get_max_height($height, $height2);
			$height2 = $pdf->getStringHeight(10, $line['step3_count']);
			$height = get_max_height($height, $height2);
			$height2 = $pdf->getStringHeight(10, $line['moved_to_collecting_count']);
			$height = get_max_height($height, $height2);
			$height2 = $pdf->getStringHeight(14, number_format($line['mainclaim_payed'], 0, ",", " "));
			$height = get_max_height($height, $height2);
			$height2 = $pdf->getStringHeight(13, number_format($line['interest_payed'], 0, ",", " "));
			$height = get_max_height($height, $height2);
			$height2 = $pdf->getStringHeight(13, number_format($line['fees_payed'], 0, ",", " "));
			$height = get_max_height($height, $height2);
			$height2 = $pdf->getStringHeight(13, number_format($line['mainclaim_payed_percentage'], 1, ",", " "));
			$height = get_max_height($height, $height2);
			$height2 = $pdf->getStringHeight(14, number_format($line['mainclaim_notpayed'], 0, ",", " "));
			$height = get_max_height($height, $height2);
			$height2 = $pdf->getStringHeight(14, number_format($line['open_cases_balance'], 0, ",", " "));
			$height = get_max_height($height, $height2);
			$height2 = $pdf->getStringHeight(10, $line['open_cases_count']);
			$height = get_max_height($height, $height2);;

	        $pdf->MultiCell(15, $height, $line['date'], "TBLR", 'L', 1, 0, "", "", true, 0, true);
			$pdf->MultiCell(20, $height, number_format($line['original_main_claim_sum'], 0, ",", " "), "TBLR", 'R', 1, 0, "", "", true, 0, true);
			$pdf->MultiCell(16, $height, $line['cases_started_in_period_count'], "TBLR", 'R', 1, 0, "", "", true, 0, true);
			$pdf->MultiCell(16, $height, $line['step1_count'], "TBLR", 'R', 1, 0, "", "", true, 0, true);
			$pdf->MultiCell(16, $height, $line['step2_count'], "TBLR", 'R', 1, 0, "", "", true, 0, true);
			$pdf->MultiCell(16, $height, $line['step3_count'], "TBLR", 'R', 1, 0, "", "", true, 0, true);
			$pdf->MultiCell(16, $height, $line['moved_to_collecting_count'], "TBLR", 'R', 1, 0, "", "", true, 0, true);
			$pdf->MultiCell(20, $height, number_format($line['mainclaim_payed'], 0, ",", " "), "TBLR", 'R', 1, 0, "", "", true, 0, true);
			$pdf->MultiCell(19, $height, number_format($line['interest_payed'], 0, ",", " "), "TBLR", 'R', 1, 0, "", "", true, 0, true);
			$pdf->MultiCell(19, $height, number_format($line['fees_payed'], 0, ",", " "), "TBLR", 'R', 1, 0, "", "", true, 0, true);
			$pdf->MultiCell(19, $height, number_format($line['mainclaim_payed_percentage'], 1, ",", " "), "TBLR", 'R', 1, 0, "", "", true, 0, true);
			$pdf->MultiCell(20, $height, number_format($line['mainclaim_notpayed'], 0, ",", " "), "TBLR", 'R', 1, 0, "", "", true, 0, true);
			$pdf->MultiCell(20, $height, number_format($line['open_cases_balance'], 0, ",", " "), "TBLR", 'R', 1, 0, "", "", true, 0, true);
			$pdf->MultiCell(16, $height, $line['open_cases_count'], "TBLR", 'R', 1, 1, "", "", true, 0, true);
	        return $pdf;
	    }
		function output_income_collecting_line($line, $pdf){
			$pdf->setCellPaddings(1, 1, 1, 1);
			$pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
	        
			$height = $pdf->getStringHeight(13, $line['date']);
			$height2 = $pdf->getStringHeight(25, number_format($line['original_main_claim_sum'], 0, ",", " "));
			$height = get_max_height($height, $height2);
			$height2 = $pdf->getStringHeight(25, $line['collecting_company_cases_count']);
			$height = get_max_height($height, $height2);
			$height2 = $pdf->getStringHeight(25, $line['open_cases_count']);
			$height = get_max_height($height, $height2);
			$height2 = $pdf->getStringHeight(25, number_format($line['mainclaim_payed'], 0, ",", " "));
			$height = get_max_height($height, $height2);
			$height2 = $pdf->getStringHeight(25, number_format($line['mainclaim_notpayed'], 0, ",", " "));
			$height = get_max_height($height, $height2);
			$height2 = $pdf->getStringHeight(25, number_format($line['mainclaim_payed_percentage'], 1, ",", " "));
			$height = get_max_height($height, $height2);

	        $pdf->MultiCell(15, $height, $line['date'], "TBLR", 'L', 1, 0, "", "", true, 0, true);
			$pdf->MultiCell(25, $height, number_format($line['original_main_claim_sum'], 0, ",", " "), "TBLR", 'R', 1, 0, "", "", true, 0, true);
			$pdf->MultiCell(25, $height, $line['collecting_company_cases_count'], "TBLR", 'R', 1, 0, "", "", true, 0, true);
			$pdf->MultiCell(25, $height, $line['open_cases_count'], "TBLR", 'R', 1, 0, "", "", true, 0, true);
			$pdf->MultiCell(25, $height, number_format($line['mainclaim_payed'], 0, ",", " "), "TBLR", 'R', 1, 0, "", "", true, 0, true);
			$pdf->MultiCell(25, $height, number_format($line['mainclaim_notpayed'], 0, ",", " "), "TBLR", 'R', 1, 0, "", "", true, 0, true);
			$pdf->MultiCell(25, $height, number_format($line['mainclaim_payed_percentage'], 1, ",", " "), "TBLR", 'R', 1, 1, "", "", true, 0, true);
	        return $pdf;
		}
		function get_max_height($height, $height2){
			if($height2 > $height) {
				$height = $height2;
			}
			return $height;
		}
	    setlocale(LC_TIME, 'no_NO');
		$pdf->Ln(10);
		$pdf->setCellPaddings(0, 0, 0, 0);
		$pdf->SetFont('helvetica', 'b', 10);
		$pdf->MultiCell(190, 0, $formText_TitlePage1_output, "", 'L', 0, 1, "", "", true, 0, true);
	    $pdf->SetFont('helvetica', '', 10);
		$pdf->MultiCell(190, 0, $formText_TitleDescription1_output, "", 'L', 0, 1, "", "", true, 0, true);
		$pdf->Ln(5);

	    $pdf->SetFont('helvetica', 'b', 7);
		$pdf->setCellPaddings(1, 1, 1, 1);
		$pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));

		// $height = $pdf->getStringHeight(13, $formText_Month_output);
		// $height2 = $pdf->getStringHeight(14, $formText_SumOfOriginalMainClaim_output);
		// $height = get_max_height($height, $height2);
		// $height2 = $pdf->getStringHeight(10, $formText_NumberOfCases_output);
		// $height = get_max_height($height, $height2);
		// $height2 = $pdf->getStringHeight(10, $formText_NumberOfLettersOnStep1_output);
		// $height = get_max_height($height, $height2);
		// $height2 = $pdf->getStringHeight(10, $formText_NumberOfLettersOnStep2_output);
		// $height = get_max_height($height, $height2);
		// $height2 = $pdf->getStringHeight(10, $formText_NumberOfLettersOnStep3_output);
		// $height = get_max_height($height, $height2);
		// $height2 = $pdf->getStringHeight(10, $formText_NumberOfCasesTransferedToCollecting_output);
		// $height = get_max_height($height, $height2);
		// $height2 = $pdf->getStringHeight(14, $formText_MainclaimPayed_output);
		// $height = get_max_height($height, $height2);
		// $height2 = $pdf->getStringHeight(13, $formText_InterestPayed_output);
		// $height = get_max_height($height, $height2);
		// $height2 = $pdf->getStringHeight(13, $formText_FeesPayed_output);
		// $height = get_max_height($height, $height2);
		// $height2 = $pdf->getStringHeight(13, $formText_PercentOriginalMainclaimPayed_output);
		// $height = get_max_height($height, $height2);
		// $height2 = $pdf->getStringHeight(14, $formText_NotPayedMainClaim_output);
		// $height = get_max_height($height, $height2);
		// $height2 = $pdf->getStringHeight(14, $formText_OpenCasesBalance_output);
		// $height = get_max_height($height, $height2);
		// $height2 = $pdf->getStringHeight(10, $formText_NumberOfOpenCases_output);
		// $height = get_max_height($height, $height2);
		$height=14;
		$pdf->MultiCell(15, $height, $formText_Month_output, "TBLR", 'L', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(20, $height, $formText_SumOfOriginalMainClaim_output, "TBLR", 'L', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(16, $height, $formText_NumberOfCases_output, "TBLR", 'L', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(16, $height, $formText_NumberOfLettersOnStep1_output, "TBLR", 'L', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(16, $height, $formText_NumberOfLettersOnStep2_output, "TBLR", 'L', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(16, $height, $formText_NumberOfLettersOnStep3_output, "TBLR", 'L', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(16, $height, $formText_NumberOfCasesTransferedToCollecting_output, "TBLR", 'L', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(20, $height, $formText_MainclaimPayed_output, "TBLR", 'L', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(19, $height, $formText_InterestPayed_output, "TBLR", 'L', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(19, $height, $formText_FeesPayed_output, "TBLR", 'L', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(19, $height, $formText_PercentOriginalMainclaimPayed_output, "TBLR", 'L', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(20, $height, $formText_NotPayedMainClaim_output, "TBLR", 'L', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(20, $height, $formText_OpenCasesBalance_output, "TBLR", 'L', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(16, $height, $formText_NumberOfOpenCases_output, "TBLR", 'L', 0, 1, "", "", true, 0, true);
	    $pdf->SetFont('helvetica', '', 7);
		$rowCounter = 1;

		$total_sumOfOriginalMainClaim = 0;
		$total_numberOfCases = 0;
		$total_numberOfLettersOnStep1 = 0;
		$total_numberOfLettersOnStep2 = 0;
		$total_numberOfLettersOnStep3 = 0;
		$total_numberOfCasesTransferedToCollecting = 0;
		$total_mainclaimPayed = 0;
		$total_interestPayed = 0;
		$total_feesPayed = 0;
		$total_percentOriginalMainclaimPayed = 0;
		$total_notPayedMainClaim = 0;
		$total_openCasesBalance = 0;
		$total_numberOfOpenCases = 0;

		foreach($total_result as $date=> $single_result) {
			$single_result['date'] = $date;
			$pdf->startTransaction();
	        $start_page = $pdf->getPage();
			if($rowCounter % 2 == 0){
				$pdf->SetFillColor(255,255,255); 
			} else {
				$pdf->SetFillColor(246,246,246); 
			}
	        $pdf = output_income_line($single_result, $pdf);

			$end_page = $pdf->getPage();

	        if  ($end_page != $start_page) {
			    $pdf->rollbackTransaction(true); // don't forget the true
			    $pdf->AddPage();
	            // $pdf->SetFont('helvetica', 'B', 14);
	        	// $pdf->MultiCell(0, 0, $formText_OvertimeReport_LID25612." ".date("d.m.Y", strtotime($firstDateOfMonth))." - ".date("d.m.Y", strtotime($lastDateOfMonth)) ." (".$formText_Page_LID25613." ".$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages().")", 0, 'L', 0, 1, 10, 10, true, 0, true);
	        	$pdf->Ln(10);
	            $pdf->SetFont('helvetica', '', 7);
	            $pdf = output_income_line($single_result, $pdf);
	        } else {
				$pdf->commitTransaction();
			}
			$rowCounter++;
			$total_sumOfOriginalMainClaim+=$single_result['original_main_claim_sum'];
			$total_numberOfCases+=$single_result['cases_started_in_period_count'];
			$total_numberOfLettersOnStep1+=$single_result['step1_count'];
			$total_numberOfLettersOnStep2+=$single_result['step2_count'];
			$total_numberOfLettersOnStep3+=$single_result['step3_count'];
			$total_numberOfCasesTransferedToCollecting+=$single_result['moved_to_collecting_count'];
			$total_mainclaimPayed+=$single_result['mainclaim_payed'];
			$total_interestPayed+=$single_result['interest_payed'];
			$total_feesPayed+=$single_result['fees_payed'];
			$mainclaim_payed_percentage+=$single_result['mainclaim_payed_percentage'];
			$total_notPayedMainClaim+=$single_result['mainclaim_notpayed'];
			$total_openCasesBalance+=$single_result['open_cases_balance'];
			$total_numberOfOpenCases+=$single_result['open_cases_count'];
	    }

		if($total_sumOfOriginalMainClaim > 0){
			$total_percentOriginalMainclaimPayed = round($total_mainclaimPayed/$total_sumOfOriginalMainClaim*100, 2);
		}
		$height = $pdf->getStringHeight(13, "");
		$height2 = $pdf->getStringHeight(14, $total_sumOfOriginalMainClaim);
		$height = get_max_height($height, $height2);
		$height2 = $pdf->getStringHeight(10, $total_numberOfCases);
		$height = get_max_height($height, $height2);
		$height2 = $pdf->getStringHeight(10, $total_numberOfLettersOnStep1);
		$height = get_max_height($height, $height2);
		$height2 = $pdf->getStringHeight(10, $total_numberOfLettersOnStep2);
		$height = get_max_height($height, $height2);
		$height2 = $pdf->getStringHeight(10, $total_numberOfLettersOnStep3);
		$height = get_max_height($height, $height2);
		$height2 = $pdf->getStringHeight(10, $total_numberOfCasesTransferedToCollecting);
		$height = get_max_height($height, $height2);
		$height2 = $pdf->getStringHeight(14, $total_mainclaimPayed);
		$height = get_max_height($height, $height2);
		$height2 = $pdf->getStringHeight(13, $total_interestPayed);
		$height = get_max_height($height, $height2);
		$height2 = $pdf->getStringHeight(13, $total_feesPayed);
		$height = get_max_height($height, $height2);
		$height2 = $pdf->getStringHeight(13, $total_percentOriginalMainclaimPayed);
		$height = get_max_height($height, $height2);
		$height2 = $pdf->getStringHeight(14, $total_notPayedMainClaim);
		$height = get_max_height($height, $height2);
		$height2 = $pdf->getStringHeight(14, $total_openCasesBalance);
		$height = get_max_height($height, $height2);
		$height2 = $pdf->getStringHeight(10, $total_numberOfOpenCases);
		$height = get_max_height($height, $height2);
		
		$pdf->MultiCell(15, $height, "", "TBLR", 'R', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(20, $height, number_format($total_sumOfOriginalMainClaim, 0 , "", " "), "TBLR", 'R', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(16, $height, $total_numberOfCases, "TBLR", 'R', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(16, $height, $total_numberOfLettersOnStep1, "TBLR", 'R', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(16, $height, $total_numberOfLettersOnStep2, "TBLR", 'R', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(16, $height, $total_numberOfLettersOnStep3, "TBLR", 'R', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(16, $height, $total_numberOfCasesTransferedToCollecting, "TBLR", 'R', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(20, $height, number_format($total_mainclaimPayed, 0 , "", " "), "TBLR", 'R', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(19, $height, number_format($total_interestPayed, 0 , "", " "), "TBLR", 'R', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(19, $height, number_format($total_feesPayed, 0 , "", " "), "TBLR", 'R', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(19, $height, number_format($total_percentOriginalMainclaimPayed, 1, ",", " "), "TBLR", 'R', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(20, $height, number_format($total_notPayedMainClaim, 0 , "", " "), "TBLR", 'R', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(20, $height, number_format($total_openCasesBalance, 0 , "", " "), "TBLR", 'R', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(16, $height, $total_numberOfOpenCases, "TBLR", 'R', 0, 1, "", "", true, 0, true);


		//charts 	
		$pdf->AddPage();
		$pdf->graphHeading($formText_OverViewPerMonth_output);
		$data2 = array();
		$month_range_start = strtotime(date("01.m.Y", strtotime("-11 months", strtotime(date("t.m.Y")))));
		$iterator = 0;


		$highestTotalAmount = 0;
		for($x = 0; $x<= 11; $x++){				
			$totalNotPaid = 0;
			$totalPaid = 0;
			$totalAmount = 0;
			$date_time = strtotime("+".$x." month", $month_range_start);
			$date_start = date("01.m.Y", $date_time);
			$date_end = date("t.m.Y", $date_time);
			foreach($total_result as $date=> $single_result) {
				if(strtotime($date) >= strtotime($date_start) && strtotime($date) <= strtotime($date_end)){
					$totalAmount += $single_result['original_main_claim_sum'];
					$totalPaid += $single_result['mainclaim_payed'];
					$totalNotPaid += $single_result['mainclaim_notpayed'];
					if($totalAmount > $highestTotalAmount){
						$highestTotalAmount = $totalAmount;
					}
				}
			}

			$data2[] = array('text'=>date("M Y", $date_time),'rows'=>
			array(
				0=>array('value'=>$totalAmount, 'label'=>$formText_TotalAmountChart_output, 'color'=>array(21,96,130)), 
				1=>array('value'=>$totalPaid, 'label'=>$formText_MainclaimPayedChart_output, 'color'=>array(233,113,50)), 
				2=>array('value'=>$totalNotPaid, 'label'=>$formText_NotPayedMainClaimChart_output, 'color'=>array(25,107,36)))
			);
		}
		$pdf->builVerticalGraph($data2 , $highestTotalAmount, 170, 40, 8, true, true);
		
		
		$total_sum_pie = $total_mainclaimPayed + $total_notPayedMainClaim;
		$totalPaidPercent = $total_mainclaimPayed/$total_sum_pie;
		$totalNotPaidPercent = $total_notPayedMainClaim/$total_sum_pie;

		
		$pdf->SetY($pdf->GetY()-10);
		$pdf->SetX(210);
		$pdf->graphHeading($formText_OverViewTotal_output);
		$data = array();
		$data[] = array('text'=>$formText_MainclaimPayedPieChart_output, 'rows'=>array(0=>$totalPaidPercent));
		$data[] = array('text'=>$formText_MainclaimNotPayedPieChart_output, 'rows'=>array(0=>$totalNotPaidPercent));
		$pdf->buildPieGraph($data, 20, 250, $pdf->GetY() + 20);

		// $pdf->ln(5);
		$pdf->graphHeading($formText_StepOverViewPerMonth_output);
		$data3 = array();
		$month_range_start = strtotime(date("01.m.Y", strtotime("-11 months", strtotime(date("t.m.Y")))));
		$iterator = 0;


		$highestCount = 0;
		for($x = 0; $x<= 11; $x++){				
			$step1Count = 0;
			$step2Count = 0;
			$step3Count = 0;
			$movedToCollectingCount = 0;
			$date_time = strtotime("+".$x." month", $month_range_start);
			$date_start = date("01.m.Y", $date_time);
			$date_end = date("t.m.Y", $date_time);
			foreach($total_result as $date=> $single_result) {
				if(strtotime($date) >= strtotime($date_start) && strtotime($date) <= strtotime($date_end)){
					
					$step1Count+=$single_result['step1_count'];
					if($step1Count > $highestCount){
						$highestCount = $step1Count;
					}
					$step2Count+=$single_result['step2_count'];
					if($step2Count > $highestCount){
						$highestCount = $step2Count;
					}
					$step3Count+=$single_result['step3_count'];
					if($step3Count > $highestCount){
						$highestCount = $step3Count;
					}
					$movedToCollectingCount+=$single_result['moved_to_collecting_count'];
					if($movedToCollectingCount > $highestCount){
						$highestCount = $movedToCollectingCount;
					}
				}
			}

			$data3[] = array('text'=>date("M Y", $date_time),'rows'=>
			array(
				0=>array('value'=>$step1Count, 'label'=>$formText_LettersOnStep1Chart_output, 'color'=>array(21,96,130)), 
				1=>array('value'=>$step2Count, 'label'=>$formText_LettersOnStep2Chart_output, 'color'=>array(233,113,50)), 
				2=>array('value'=>$step3Count, 'label'=>$formText_LettersOnStep3Chart_output, 'color'=>array(25,107,36)), 
				3=>array('value'=>$movedToCollectingCount, 'label'=>$formText_MovedToCollectingChart_output)			
				)
			);
		}
		$pdf->builVerticalGraph($data3 , $highestCount, 170, 40, 8, true, true);
		$pdf->ln(5);
		$total_sum_pie = $total_numberOfLettersOnStep1 + $total_numberOfLettersOnStep2 + $total_numberOfLettersOnStep3 + $total_numberOfCasesTransferedToCollecting;
		$step1Percent = $total_numberOfLettersOnStep1/$total_sum_pie;
		$step2Percent = $total_numberOfLettersOnStep2/$total_sum_pie;
		$step3Percent = $total_numberOfLettersOnStep3/$total_sum_pie;
		$movedToCollectingPercent = $total_numberOfCasesTransferedToCollecting/$total_sum_pie;

		
		$pdf->SetY($pdf->GetY()-10);
		$pdf->SetX(210);
		$pdf->graphHeading($formText_StepOverViewTotal_output);
		$data = array();
		$data[] = array('text'=>$formText_LettersOnStep1PieChart_output, 'rows'=>array(0=>$step1Percent));
		$data[] = array('text'=>$formText_LettersOnStep2PieChart_output, 'rows'=>array(0=>$step2Percent));
		$data[] = array('text'=>$formText_LettersOnStep3PieChart_output, 'rows'=>array(0=>$step3Percent));
		$data[] = array('text'=>$formText_CasesMovedToCollectingPieChart_output, 'rows'=>array(0=>$movedToCollectingPercent));
		$pdf->buildPieGraph($data, 20, 250, $pdf->GetY() + 20);
		

		$pdf->SetTextColor(0,0,0);
		$pdf->AddPage();
		$pdf->SetFont('helvetica', 'b', 10);
		$pdf->setCellPaddings(0, 0, 0, 0);
		$pdf->MultiCell(190, 0, $formText_TitlePage2_output, "", 'L', 0, 1, "", "", true, 0, true);
		$pdf->SetFont('helvetica', '', 10);
		$pdf->MultiCell(190, 0, $formText_TitleDescription2_output, "", 'L', 0, 1, "", "", true, 0, true);
		$pdf->Ln(5);

		$pdf->SetFont('helvetica', 'b', 7);
		$pdf->setCellPaddings(1, 1, 1, 1);
		$pdf->SetLineStyle(array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
		
		$height = $pdf->getStringHeight(13, $formText_Month_output);
		$height2 = $pdf->getStringHeight(25, $formText_SumOfOriginalMainClaim_output);
		$height = get_max_height($height, $height2);
		$height2 = $pdf->getStringHeight(25, $formText_NumberOfCases_output);
		$height = get_max_height($height, $height2);
		$height2 = $pdf->getStringHeight(25, $formText_NumberOfOpenCases_output);
		$height = get_max_height($height, $height2);
		$height2 = $pdf->getStringHeight(25, $formText_payedAmountOnMainclaim_output);
		$height = get_max_height($height, $height2);
		$height2 = $pdf->getStringHeight(25, $formText_NotPayedMainClaim_output);
		$height = get_max_height($height, $height2);
		$height2 = $pdf->getStringHeight(25, $formText_PercentOriginalMainclaimPayed_output);
		$height = get_max_height($height, $height2);
		

		$pdf->MultiCell(15, $height, $formText_Month_output, "TBLR", 'L', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(25, $height, $formText_SumOfOriginalMainClaim_output, "TBLR", 'L', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(25, $height, $formText_NumberOfCases_output, "TBLR", 'L', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(25, $height, $formText_NumberOfOpenCases_output, "TBLR", 'L', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(25, $height, $formText_payedAmountOnMainclaim_output, "TBLR", 'L', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(25, $height, $formText_NotPayedMainClaim_output, "TBLR", 'L', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(25, $height, $formText_PercentOriginalMainclaimPayed_output, "TBLR", 'L', 0, 1, "", "", true, 0, true);

	    $pdf->SetFont('helvetica', '', 7);
		$rowCounter= 1;

		$total_SumOfOriginalMainClaim = 0;
		$total_NumberOfCases = 0;
		$total_NumberOfOpenCases = 0;
		$total_PayedAmountOnMainclaim = 0;
		$total_NotPayedMainClaim = 0;
		$total_PercentOriginalMainclaimPayed = 0;

		foreach($total_result_collecting as $date=> $single_result) {
			$single_result['date'] = $date;
			$pdf->startTransaction();
	        $start_page = $pdf->getPage();
			if($rowCounter % 2 == 0){
				$pdf->SetFillColor(255,255,255); 
			} else {
				$pdf->SetFillColor(246,246,246); 
			}
	        $pdf = output_income_collecting_line($single_result, $pdf);

			$end_page = $pdf->getPage();

	        if  ($end_page != $start_page) {
			    $pdf->rollbackTransaction(true); // don't forget the true
			    $pdf->AddPage();
	            // $pdf->SetFont('helvetica', 'B', 14);
	        	// $pdf->MultiCell(0, 0, $formText_OvertimeReport_LID25612." ".date("d.m.Y", strtotime($firstDateOfMonth))." - ".date("d.m.Y", strtotime($lastDateOfMonth)) ." (".$formText_Page_LID25613." ".$pdf->getAliasNumPage().'/'.$pdf->getAliasNbPages().")", 0, 'L', 0, 1, 10, 10, true, 0, true);
	        	$pdf->Ln(10);
	            $pdf->SetFont('helvetica', '', 7);
	            $pdf = output_income_collecting_line($single_result, $pdf);
	        } else {
				$pdf->commitTransaction();
			}
			$rowCounter++;
			$total_SumOfOriginalMainClaim += $single_result['original_main_claim_sum'];
			$total_NumberOfCases += $single_result['collecting_company_cases_count'];
			$total_NumberOfOpenCases += $single_result['open_cases_count'];
			$total_PayedAmountOnMainclaim += $single_result['mainclaim_payed'];
			$total_NotPayedMainClaim += $single_result['mainclaim_notpayed'];
	    }
		if($total_SumOfOriginalMainClaim > 0){
			$total_PercentOriginalMainclaimPayed = round($total_PayedAmountOnMainclaim/$total_SumOfOriginalMainClaim*100, 2);
		}		

		$height = $pdf->getStringHeight(13, "");
		$height2 = $pdf->getStringHeight(25, $total_SumOfOriginalMainClaim);
		$height = get_max_height($height, $height2);
		$height2 = $pdf->getStringHeight(25, $total_NumberOfCases);
		$height = get_max_height($height, $height2);
		$height2 = $pdf->getStringHeight(25, $total_NumberOfOpenCases);
		$height = get_max_height($height, $height2);
		$height2 = $pdf->getStringHeight(25, $total_PayedAmountOnMainclaim);
		$height = get_max_height($height, $height2);
		$height2 = $pdf->getStringHeight(25, $total_NotPayedMainClaim);
		$height = get_max_height($height, $height2);
		$height2 = $pdf->getStringHeight(25, $total_PercentOriginalMainclaimPayed);
		$height = get_max_height($height, $height2);
		

		$pdf->MultiCell(15, $height, "", "TBLR", 'R', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(25, $height, number_format($total_SumOfOriginalMainClaim, 0, ",", " "), "TBLR", 'R', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(25, $height, $total_NumberOfCases, "TBLR", 'R', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(25, $height, $total_NumberOfOpenCases, "TBLR", 'R', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(25, $height, number_format($total_PayedAmountOnMainclaim, 0, ",", " "), "TBLR", 'R', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(25, $height, number_format($total_NotPayedMainClaim, 0, ",", " "), "TBLR", 'R', 0, 0, "", "", true, 0, true);
		$pdf->MultiCell(25, $height, number_format($total_PercentOriginalMainclaimPayed, 1, ",", " "), "TBLR", 'R', 0, 1, "", "", true, 0, true);

	    ob_end_clean();
	    //Close and output PDF document
	    $pdfName = 'report_'.$cid;
	    $pdfName .= '.pdf';
		if($from_api){
	    	$pdf_string = $pdf->Output($pdfName, 'E');
		} else {
	    	$pdf->Output($pdfName, 'I');
		}
	} else {
		if(!$from_api){
			header('Location: ' . $_SERVER['HTTP_REFERER']);
		}
	}
} else {
	// header('Location: ' . $_SERVER['HTTP_REFERER']);
}
?>
