<?php
// error_reporting(E_ALL);
// ini_set("display_errors", 1);
session_start();
if(isset($_POST['ajaxSave'])){
	$_SESSION['list_filter'] = $_POST['list_filter'];
	$_SESSION['subscription_type'] = $_POST['subscription_type'];
	$_SESSION['status_filter'] = $_POST['status_filter'];
	$_SESSION['search_filter'] = $_POST['search_filter'];
	$_SESSION['customerselfdefinedlist_filter'] = $_POST['customerselfdefinedlist_filter'];
	$_SESSION['ownercompany_filter'] = $_POST['ownercompany_filter'];
}else{
	// Constants (taken from fw/index.php)
	define('FRAMEWORK_DEBUG', FALSE);
	define('ACCOUNT_PATH', realpath(__DIR__.'/../../../../')); // this is modified to fit this files location
	define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
	$v_tmp = explode("/",ACCOUNT_PATH);
	$accountname = array_pop($v_tmp);

	// Load database
	require_once __DIR__ . '/../../../../elementsGlobal/cMain.php';
	require_once __DIR__ . '/functions.php';

	ini_set('memory_limit', '2048M');
	ini_set('max_execution_time', 300);
	include("readOutputLanguage.php");

	// $checkboxes = $_SESSION['checkboxes'];
	// $customerIdsArrayString = explode("&", $checkboxes);
	// $customerIdsArray = array();
	// foreach($customerIdsArrayString as $customerIdArray){
	// 	$customerIdsItem = explode("=", $customerIdArray);
	// 	if(count($customerIdsItem) == 2){
	// 		array_push($customerIdsArray, $customerIdsItem[1]);
	// 	}
	// }

	$list_filter = $_SESSION['list_filter'];
	$subscription_type_filter = $_SESSION['subscription_type'];
	$status_filter = $_SESSION['status_filter'];
	$search_filter = $_SESSION['search_filter'];
	$customerselfdefinedlist_filter = $_SESSION['customerselfdefinedlist_filter'];
	$ownercompany_filter = $_SESSION['ownercompany_filter'];
	$date_filter = $_SESSION['date_filter'];

	$customerList = get_support_list($o_main, $list_filter, $search_filter, $subscription_type_filter, $status_filter, $customerselfdefinedlist_filter,$ownercompany_filter, $date_filter, -1, 0, null, null);

	// if(count($customerIdsArray) > 0){
		$type = 1;

		/** Include PHPExcel */
		require_once dirname(__FILE__) . '/phpExcel/PHPExcel.php';

		define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
		$customers = array();
		foreach($customerList as $singlecustomer){
			array_push($customers, $singlecustomer);
		}
		$memberStatus = "";
		if($list_filter == "active"){
			$memberStatus = $formText_Active_output;
		} else if($list_filter == "not_started"){
			$memberStatus = $formText_NotStarted_output;
		} else if($list_filter == "stopped"){
			$memberStatus = $formText_Stopped_output;
		} else if($list_filter == "future_stop"){
			$memberStatus = $formText_FutureStop_output;
		} else if($list_filter == "deleted"){
			$memberStatus = $formText_Deleted_output;
		}
		if($type == 1){


			$objPHPExcel->setActiveSheetIndex(0);
			$row = 1;

			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $formText_CustomerName_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $formText_SubscriptionName_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $formText_StartDate_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $formText_NextRenewalDate_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $formText_SummaryPerMonth_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $formText_OrderlineName_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $formText_OrderlineAmountPerMonth_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $formText_OrderlinePricePerMonth_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, $formText_OrderlineDiscount_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, $formText_OrderlinePricePerPeriod_text);

			foreach($customers as $customer){
                if($customer['freeNoBilling']) {
                    $summaryPerMonth = $formText_FreeNoBilling_Output;
                } else {
                    $summaryPerMonth = number_format($customer['summaryPerMonth'], 2, ',', '');
                }

                $o_query = $o_main->db->query("SELECT * FROM subscriptionline WHERE subscribtionId = ?", array($customer['id']));
    			if($o_query && $o_query->num_rows()>0)
    			foreach($o_query->result_array() as $subscriptionline)
    			{
    				$row = $objPHPExcel->getActiveSheet()->getHighestRow()+1;

    				$pricePerPiece = $subscriptionline['pricePerPiece'];
    				if($subscriptionline['articleOrIndividualPrice']) {
    		            $sql = "SELECT * FROM article WHERE id = ?";
    		            $o_query = $o_main->db->query($sql, array($subscriptionline['articleNumber']));
    		            $article = $o_query ? $o_query->row_array() : array();
    					$pricePerPiece = $article['price'];
    				}
                    $pricePerPeriod = $pricePerPiece * $subscriptionline['amount'] * (1 - $subscriptionline['discount']/100);

    				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $customer['customerName']);
    				$objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $customer['subscriptionName']);
    				$objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $customer['startDate']);
    				$objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $customer['nextRenewalDate']);
    				$objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $summaryPerMonth);
    				$objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $subscriptionline['articleName']);
    				$objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $subscriptionline['amount']);
    				$objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $pricePerPiece);
    				$objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, $subscriptionline['discountPercent']);
    				$objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, $pricePerPeriod);
                }


			}
		}
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);

		// header('Content-Encoding: UTF-8');
		// header('Content-type: text/csv; charset=UTF-8');
		// header('Content-Disposition: attachment;filename="export.csv"');
		// header('Cache-Control: max-age=0');
		// header("Pragma: no-cache");
		// header("Expires: 0");
		// header('Content-Transfer-Encoding: binary');
		// echo "\xEF\xBB\xBF";

		// $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="export.xls"');
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

		$objWriter->save('php://output');
		// unset($_SESSION['checkboxes']);
	// }
}
?>
