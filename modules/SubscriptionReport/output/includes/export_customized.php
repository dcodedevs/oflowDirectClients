<?php
// error_reporting(E_ALL);
// ini_set("display_errors", 1);
session_start();
// Constants (taken from fw/index.php)
define('FRAMEWORK_DEBUG', FALSE);
define('ACCOUNT_PATH', realpath(__DIR__.'/../../../../')); // this is modified to fit this files location
define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
$v_tmp = explode("/",ACCOUNT_PATH);
$accountname = array_pop($v_tmp);

// Load database
require_once __DIR__ . '/../../../../elementsGlobal/cMain.php';

if(isset($_POST['ajaxSave'])){
	$_SESSION['list_filter'] = $_POST['list_filter'];
	$_SESSION['subscription_type'] = $_POST['subscription_type'];
	$_SESSION['status_filter'] = $_POST['status_filter'];
	$_SESSION['search_filter'] = $_POST['search_filter'];
	$_SESSION['customerselfdefinedlist_filter'] = $_POST['customerselfdefinedlist_filter'];
	$_SESSION['ownercompany_filter'] = $_POST['ownercompany_filter'];
	$_SESSION['date_filter'] = $_POST['date_filter'];
	if($_POST['contactperson']) {
		if(file_exists(__DIR__."/../languagesOutput/".$_POST['languageID'].".php")){
			include(__DIR__."/../languagesOutput/".$_POST['languageID'].".php");
		} else {
			include(__DIR__."/../languagesOutput/no.php");
		}
		$extradir = $_POST['extradir'];
		?>
		<div class="popupform">
			<div id="popup-validate-message" style="display:none;"></div>
			<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editOrder";?>" method="post">
				<input type="hidden" name="fwajax" value="1">
				<input type="hidden" name="fw_nocss" value="1">
				<input type="hidden" name="output_form_submit" value="1">
				<input type="hidden" name="orderId" value="<?php echo $orderId;?>">
				<input type="hidden" name="customerId" value="<?php echo $customerId;?>">
		        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$orderId; ?>">
				<div class="inner">
		            <div class="line">
		                <div class="lineTitle"><?php echo $formText_ContactPersons_Output; ?></div>
		                <div class="lineInput ">
							<select class="contactpersonType">
								<option value="0"><?php echo $formText_All_output;?></option>
								<option value="1"><?php echo $formText_AdminOnly_output;?></option>
							</select>
		                </div>
		                <div class="clear"></div>
		            </div>
				</div>
				<div class="popupformbtn">
					<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
					<input type="submit" name="sbmbtn" value="<?php echo $formText_Export_Output; ?>">
				</div>
			</form>
		</div>
		<script type="text/javascript" src="../modules/SubscriptionReport/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
		<script type="text/javascript">

		$(document).ready(function() {
			$("form.output-form").validate({
		        submitHandler: function(form) {
					fw_loading_start();
	    			$(".fw_info_message_wraper .fw_info_messages").html('');



	                var generateIframeDownload = function(){
						fetch("<?php echo $extradir;?>/output/includes/export.php?type=2&contactpersonType="+$(".contactpersonType").val()+"&time=<?php echo time();?>")
						  .then(resp => resp.blob())
						  .then(blob => {
						    const url = window.URL.createObjectURL(blob);
						    const a = document.createElement('a');
						    a.style.display = 'none';
						    a.href = url;
						    // the filename you want
						    a.download = 'export.xls';
						    document.body.appendChild(a);
						    a.click();
						    window.URL.revokeObjectURL(url);
							out_popup.close();
						  })
						  .catch(() => fw_loading_end());

	                // var iframe = document.createElement('iframe');
	                //     iframe.src = "<?php echo $extradir;?>/output/includes/export.php?type=2&contactpersonType="+$(".contactpersonType").val()+"&time=<?php echo time();?>";
	                //     iframe.style.display = "none";
	                //     iframe.onload = function(){
	                //         fw_loading_end();
					// 		out_popup.close();
	                //         setTimeout(function(){
	                //             iframe.remove();
	                //         }, 1000)
	                //     }
	                //     fw_loading_end();
	                //     document.body.appendChild(iframe);

	                }
	                generateIframeDownload();
		        },
		        invalidHandler: function(event, validator) {
		            var errors = validator.numberOfInvalids();
		            if (errors) {
		                var message = errors == 1
		                ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
		                : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

		                $("#popup-validate-message").html(message);
		                $("#popup-validate-message").show();
		                $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
		            } else {
		                $("#popup-validate-message").hide();
		            }
		            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
		        }
			})
		});

		</script>
		<style>

		.selectDivModified {
		    display:block;
		}
		.popupform, .popupeditform {
			width:100%;
			margin:0 auto;
			border:1px solid #e8e8e8;
			position:relative;
		}
		label.error { display: none !important; }
		.popupform .popupforminput.error { border-color:#c11 !important;}
		#popup-validate-message, .error-msg { font-weight:bold; color:#c11; padding-bottom:10px; }
		/* css for timepicker */
		.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
		.ui-timepicker-div dl { text-align: left; }
		.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
		.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
		.ui-timepicker-div td { font-size: 90%; }
		.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
		.clear {
			clear:both;
		}
		.inner {
			padding:10px;
		}
		.pplineV {
			position:absolute;
			top:0;bottom:0;left:70%;
			border-left:1px solid #e8e8e8;
		}
		.popupform input.popupforminput, .popupform textarea.popupforminput, .popupform select.popupforminput, .col-md-8z input {
			width:100%;
			border-radius: 4px;
			padding:5px 10px;
			font-size:12px;
			line-height:17px;
			color:#3c3c3f;
			background-color:transparent;
			-webkit-box-sizing: border-box;
			   -moz-box-sizing: border-box;
				 -o-box-sizing: border-box;
					box-sizing: border-box;
			font-weight:400;
			border: 1px solid #cccccc;
		}
		.popupformname {
			font-size:12px;
			font-weight:bold;
			padding:5px 0px;
		}
		.popupforminput.botspace {
			margin-bottom:10px;
		}
		textarea {
			min-height:50px;
			max-width:100%;
			min-width:100%;
			width:100%;
		}
		.popupformname {
			font-weight: 700;
			font-size: 13px;
		}
		.popupformbtn {
			text-align:right;
			margin:10px;
		}
		.popupformbtn input {
			border-radius:4px;
			border:1px solid #0393ff;
			background-color:#0393ff;
			font-size:13px;
			line-height:0px;
			padding: 20px 35px;
			font-weight:700;
			color:#FFF;
			margin-left:10px;
		}
		.error {
			border: 1px solid #c11;
		}
		.popupform .lineTitle {
			font-weight:700;
		}
		.popupform .line .lineTitle {
			width:30%;
			float:left;
			font-weight:700;
			padding:5px 0;
		}

		.popupform .line .lineTitleWithSeperator {
		    width:100%;
		    margin: 20px 0;
		    padding:0 0 10px;
		    border-bottom:1px solid #EEE;
		}

		.popupform .line .lineInput {
			width:70%;
			float:left;
		}
		.priceTotalLine .popupforminput {
		    border: none !important;
		}
		.popupform input.popupforminput.checkbox {
		    width: auto;
		}
		</style>
		<?php
	}
}else{
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

	$customerList = get_support_list($o_main, $list_filter, $search_filter, $subscription_type_filter, $status_filter, $customerselfdefinedlist_filter,$ownercompany_filter, $date_filter, -1, 0);

	// if(count($customerIdsArray) > 0){
		$type = $_GET['type'];
		$contactpersonType = $_GET['contactpersonType'];

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
			// $customerIds = "";
			// foreach($customerIdsArray as $checkbox){
			// 	$customerIds .= $checkbox.", ";
			// }
			// if($customerIds != ""){
			// 	$customerIds = substr($customerIds, 0, -2);
			// }
			// $selectCustomers = "SELECT * FROM customer WHERE id IN(".$customerIds.") ORDER BY name";
			// $selectCustomers = "SELECT customer.*,GROUP_CONCAT(customerindustriconnect.industryId) as industryId FROM customer
			// LEFT OUTER JOIN  customerindustriconnect ON  customerindustriconnect.customerId = customer.id
			// WHERE customer.id IN(".$customerIds.") GROUP BY customer.id ORDER BY name";
			//
			// $findCustomers = mysql_query($selectCustomers);
			// $maxIndustryNumber = 0;
			// $customers = array();
			// while($customer = mysql_fetch_array($findCustomers)){
			// 	$industryString = $customer['industryId'];
			// 	$industryArray = explode(',', $industryString);
			// 	$industryCount = count($industryArray);
			// 	if($industryCount > $maxIndustryNumber){
			// 		$maxIndustryNumber = $industryCount;
			// 	}
			// 	if($industryCount > 0){
			// 		$inCounter = 0;
			// 		foreach($industryArray as $industryId){
			// 			$customer['industryId'.$inCounter] = $industryId;
			// 			$inCounter++;
			// 		}
			// 	}
			// 	array_push($customers, $customer);
			// }


			$objPHPExcel->setActiveSheetIndex(0);
			$row = 1;

			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $formText_publicRegisterId_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $formText_exportName_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $formText_exportPaStreet_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $formText_exportPaPostalNumber_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $formText_exportPaCity_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $formText_exportPaCountry_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $formText_exportVaStreet_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $formText_exportVaPostalNumber_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, $formText_exportVaCity_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, $formText_exportVaCountry_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, $formText_exportPhone_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, $formText_exportMobile_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('M'.$row, $formText_exportFax_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('N'.$row, $formText_exportEmail_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('O'.$row, $formText_exportRevenue_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('P'.$row, $formText_exportFinancialYear_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('Q'.$row, $formText_exportRevenueManulallyadded_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('R'.$row, $formText_exportRevenueManulallyaddedYear_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('S'.$row, $formText_exportMembershipStatus_text);
			// $col = 20;
			// for($inC = 0; $inC < $maxIndustryNumber; $inC++){
			// 	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $formText_exportIndustry_text . " ".($inC + 1));
			// 	$col++;
			// 	if($inC >= 2){
			// 		break;
			// 	}
			// }

			foreach($customers as $customer){
				$row = $objPHPExcel->getActiveSheet()->getHighestRow()+1;
				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $customer['publicRegisterId']);
				$objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $customer['name']);
				$objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $customer['paStreet']);
				$objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $customer['paPostalNumber']);
				$objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $customer['paCity']);
				$objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $customer['paCountry']);
				$objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $customer['vaStreet']);
				$objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $customer['vaPostalNumber']);
				$objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, $customer['vaCity']);
				$objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, $customer['vaCountry']);
				$objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, $customer['phone']);
				$objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, $customer['mobile']);
				$objPHPExcel->getActiveSheet()->SetCellValue('M'.$row, $customer['fax']);
				$objPHPExcel->getActiveSheet()->SetCellValue('N'.$row, $customer['email']);
				$objPHPExcel->getActiveSheet()->SetCellValue('O'.$row, $customer['revenue']);
				$objPHPExcel->getActiveSheet()->SetCellValue('P'.$row, $customer['financialYear']);
				$objPHPExcel->getActiveSheet()->SetCellValue('Q'.$row, $customer['revenueManuallyAdded']);
				$objPHPExcel->getActiveSheet()->SetCellValue('R'.$row, $customer['revenueManuallyAddedYear']);
				$objPHPExcel->getActiveSheet()->SetCellValue('S'.$row, $memberStatus);

				$l_counter = -1;
				$v_fields = array();
				$v_columns = array('T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH');
				$o_selfdefined = $o_main->db->query("SELECT v.id AS value_id, v.value, f.* FROM customer_selfdefined_values v JOIN customer_selfdefined_fields f ON f.id = v.selfdefined_fields_id WHERE v.customer_id = '".$o_main->db->escape_str($customer['customerId'])."' ORDER BY f.list_id");
				if($o_selfdefined && $o_selfdefined->num_rows()>0)
				{
					foreach($o_selfdefined->result_array() as $v_selfdefined)
					{
						if(!array_key_exists($v_selfdefined['id'], $v_fields))
						{
							$l_counter++;
							$v_fields[$v_selfdefined['id']] = $l_counter;
						}
						$l_idx = $v_fields[$v_selfdefined['id']];
						$objPHPExcel->getActiveSheet()->SetCellValue($v_columns[$l_idx].'1', $v_selfdefined['name']);

						$s_value = '';
						if('1' == $v_selfdefined['type'])
						{
							$o_find = $o_main->db->query("SELECT * FROM customer_selfdefined_list_lines WHERE id = '".$o_main->db->escape_str($v_selfdefined['value'])."' AND list_id = '".$o_main->db->escape_str($v_selfdefined['list_id'])."'");
							$v_find = $o_find ? $o_find->row_array() : array();
							$s_value = $v_find['name'];
						} else if('2' == $v_selfdefined['type'])
						{
							$o_find = $o_main->db->query("SELECT * FROM customer_selfdefined_values_connection c JOIN customer_selfdefined_list_lines l ON l.id = c.selfdefined_list_line_id WHERE c.selfdefined_value_id = '".$o_main->db->escape_str($v_selfdefined['value_id'])."'");
							if($o_find && $o_find->num_rows()>0)
							{
								foreach($o_find->result_array() as $v_find)
								{
									$s_value .= (''!=$s_value?', ':'').$v_find['name'];
								}
							}
						}
						$objPHPExcel->getActiveSheet()->SetCellValue($v_columns[$l_idx].$row, $s_value);
					}
				}

				// $isMember = $formText_exportNotMember_text;
				// $customerID = $customer["customerId"];
				// $articleIDArray = array();
				// $o_query = $o_main->db->query("SELECT customer.*, subscriptionmulti.startDate, subscriptionmulti.stoppedDate FROM customer
				// LEFT JOIN
				// (SELECT subscriptionmulti.startDate, subscriptionmulti.id, subscriptionmulti.customerId, MIN(subscriptionmulti.stoppedDate) AS stoppedDateFROM subscriptionmulti WHERE subscriptionmulti.customerId <> 0 GROUP by subscriptionmulti.customerId) subscriptionmulti
				// ON subscriptionmulti.customerId = customer.id
				// WHERE customer.id = '".$customerID."' ORDER BY subscriptionmulti.id");
				// $members = $o_query ? $o_query->result_array() : array();
				// foreach($members as $c) {
				// 	if($c['startDate'] != '0000-00-00' && $c['startDate'] != null) {
				// 		if($c['stoppedDate'] == '0000-00-00' || $c['stoppedDate'] === null) {
				// 			$isMember = $formText_exportIsMember_text;
				// 			if(intval($c['articleNumber']) > 0){
				// 				array_push($articleIDArray, $c['articleNumber']);
				// 			}
				// 		} else {
				// 			$currentTime = time();
				// 			$startTime = strtotime($c['startDate']);
				// 			$stoppedDate = strtotime($c['stoppedDate']);
				// 			if($currentTime > $startDate && $currentTime <= $stoppedDate) {
				// 				$isMember = $formText_exportIsMember_text;
				// 				if(intval($c['articleNumber']) > 0){
				// 					array_push($articleIDArray, $c['articleNumber']);
				// 				}
				// 			}
				// 		}
				// 	}
				// }


				// $industryString = $customer['industryId'];
				// $industryArray = explode(',', $industryString);
				// $industryCount = count($industryArray);
				// $col = 20;
				// for($inC = 0; $inC < $industryCount; $inC++){
				// 	$selectIndustry = "SELECT * FROM industry WHERE id = ".$customer['industryId'.$inC]." ORDER BY sortnr";
				// 	$findIndustry = mysql_query($selectIndustry);
				// 	$industry = mysql_fetch_array($findIndustry);
				// 	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $industry['name']);
				// 	$col++;
				// 	if($inC >= 2){
				// 		break;
				// 	}
				// }
			}
		}else if ($type == 2){
			$objPHPExcel->setActiveSheetIndex(0);
			$row = 1;
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $formText_publicRegisterId_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $formText_exportName_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $formText_exportPaStreet_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $formText_exportPaPostalNumber_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $formText_exportPaCity_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $formText_exportPaCountry_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $formText_exportVaStreet_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $formText_exportVaPostalNumber_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, $formText_exportVaCity_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, $formText_exportVaCountry_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, $formText_exportPhone_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, $formText_exportMobile_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('M'.$row, $formText_exportFax_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('N'.$row, $formText_exportEmail_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('O'.$row, $formText_exportMembershipStatus_text);

			$objPHPExcel->getActiveSheet()->SetCellValue('P'.$row, $formText_exportContactName_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('Q'.$row, $formText_exportContactMiddleName_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('R'.$row, $formText_exportContactLastName_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('S'.$row, $formText_exportContactTitle_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('T'.$row, $formText_exportContactPhone_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('U'.$row, $formText_exportContactMobile_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('V'.$row, $formText_exportContactEmail_text);
			$objPHPExcel->getActiveSheet()->SetCellValue('W'.$row, $formText_exportContactWantToReceiveInfo_text);
            $objPHPExcel->getActiveSheet()->SetCellValue('X'.$row, $formText_exportContactMaincontact_text);


			$s_sql = "SELECT * FROM subscriptiontype WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($subscription_type_filter));
			$subscriptionType = ($o_query ? $o_query->row_array():array());
			if($subscriptionType['periodUnit'] == 0) {
			   $summaryLabel = $formText_PricePerMonth_output;
			} else {
			   $summaryLabel = $formText_exportPricePerYear_text;
		   	}

            $objPHPExcel->getActiveSheet()->SetCellValue('Y'.$row, $summaryLabel);

			// $col = 22;
			// for($inC = 0; $inC < $maxIndustryNumber; $inC++){
			// 	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $formText_exportIndustry_text . " ".($inC + 1));
			// 	$col++;
			// 	if($inC >= 2){
			// 		break;
			// 	}
			// }

			foreach($customers as $customer){

				$row = $objPHPExcel->getActiveSheet()->getHighestRow()+1;
				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $customer['publicRegisterId']);
				$objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $customer['name']);
				$objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $customer['paStreet']);
				$objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $customer['paPostalNumber']);
				$objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $customer['paCity']);
				$objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $customer['paCountry']);
				$objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $customer['vaStreet']);
				$objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $customer['vaPostalNumber']);
				$objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, $customer['vaCity']);
				$objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, $customer['vaCountry']);
				$objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, $customer['phone']);
				$objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, $customer['mobile']);
				$objPHPExcel->getActiveSheet()->SetCellValue('M'.$row, $customer['fax']);
				$objPHPExcel->getActiveSheet()->SetCellValue('N'.$row, $customer['email']);
				$objPHPExcel->getActiveSheet()->SetCellValue('O'.$row, $memberStatus);
				$contactperson_sql = "";
				if($contactpersonType == 1){
					$contactperson_sql = " AND contactperson.admin = 1";
				}
				$pricePerYear = $customer['summaryPerMonth'];;
				$o_query = $o_main->db->query("SELECT * FROM contactperson WHERE customerId = ".$customer['customerId']." ".$contactperson_sql." ORDER BY sortnr");
				$firstContact = true;
				$contactPersons = $o_query ? $o_query->result_array() : array();
				foreach($contactPersons as $contactPerson) {
					if(!$firstContact){
						$row = $objPHPExcel->getActiveSheet()->getHighestRow()+1;
						$objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $customer['publicRegisterId']);
						$objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $customer['name']);
						$objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $customer['paStreet']);
						$objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $customer['paPostalNumber']);
						$objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $customer['paCity']);
						$objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $customer['paCountry']);
						$objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $customer['vaStreet']);
						$objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $customer['vaPostalNumber']);
						$objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, $customer['vaCity']);
						$objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, $customer['vaCountry']);
						$objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, $customer['phone']);
						$objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, $customer['mobile']);
						$objPHPExcel->getActiveSheet()->SetCellValue('M'.$row, $customer['fax']);
						$objPHPExcel->getActiveSheet()->SetCellValue('N'.$row, $customer['email']);
						$objPHPExcel->getActiveSheet()->SetCellValue('O'.$row, $memberStatus);
					}
					$objPHPExcel->getActiveSheet()->SetCellValue('P'.$row, $contactPerson['name']);
					$objPHPExcel->getActiveSheet()->SetCellValue('Q'.$row, $contactPerson['middlename']);
					$objPHPExcel->getActiveSheet()->SetCellValue('R'.$row, $contactPerson['lastname']);
					$objPHPExcel->getActiveSheet()->SetCellValue('S'.$row, $contactPerson['title']);
					$objPHPExcel->getActiveSheet()->SetCellValue('T'.$row, $contactPerson['directPhone']);
					$objPHPExcel->getActiveSheet()->SetCellValue('U'.$row, $contactPerson['mobile']);
					$objPHPExcel->getActiveSheet()->SetCellValue('V'.$row, $contactPerson['email']);
					$objPHPExcel->getActiveSheet()->SetCellValue('W'.$row, $contactPerson['wantToReceiveInfo']);
                    $objPHPExcel->getActiveSheet()->SetCellValue('X'.$row, $contactPerson['mainContact']);
		            $objPHPExcel->getActiveSheet()->SetCellValue('Y'.$row, $pricePerYear);
					$firstContact = false;

					// $industryString = $customer['industryId'];
					// $industryArray = explode(',', $industryString);
					// $industryCount = count($industryArray);
					// $col = 22;
					// for($inC = 0; $inC < $industryCount; $inC++){
					// 	$selectIndustry = "SELECT * FROM industry WHERE id = ".$customer['industryId'.$inC]." ORDER BY sortnr";
					// 	$findIndustry = mysql_query($selectIndustry);
					// 	$industry = mysql_fetch_array($findIndustry);
					// 	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $industry['name']);
					// 	$col++;
					// 	if($inC >= 2){
					// 		break;
					// 	}
					// }
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
