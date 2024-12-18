<?php
if($variables->loggID == "byamba@dcode.no") {

	include_once(__DIR__."/../../../CollectingCompanyCases/output/includes/fnc_generate_pdf.php");
    // $result = generate_pdf("101386");
	// var_dump($result);
	// $sql = "SELECT cc.*, c.default_currency, ct.currency as transactionCurrency FROM collecting_company_cases cc
	// JOIN creditor_transactions ct ON ct.collecting_company_case_id = cc.id
	// JOIN creditor c ON c.id = ct.creditor_id
	// WHERE ct.id is not null AND (ct.currency <> 'LOCAL' OR c.default_currency <> 'NOK')";
	// $o_query = $o_main->db->query($sql);
	// $company_cases = $o_query ? $o_query->result_array() : array();
	// foreach($company_cases as $company_case) {
	// 	$currencyName = "";
	// 	if($company_case['transactionCurrency'] != ""){
	// 		if($company_case['transactionCurrency'] == 'LOCAL') {
	// 			$currencyName = trim($company_case['default_currency']);
	// 		} else {
	// 			$currencyName = trim($company_case['transactionCurrency']);
	// 		}
	// 	}
	// 	if($currencyName != "NOK") {
	// 		$s_sql = "UPDATE collecting_company_cases SET currency = 1, currency_name = ?
	// 		WHERE id = ?";
	// 	    $o_query = $o_main->db->query($s_sql, array($currencyName, $company_case['id']));
	// 	}
	// }
	// $s_sql = "UPDATE collecting_company_cases SET checkbox_1 = 0
	// WHERE collecting_company_cases.checkbox_1 = 1";
    // $o_query = $o_main->db->query($s_sql);
	//
	// $s_sql = "SELECT collecting_company_cases.* FROM collecting_company_cases
	// JOIN collecting_company_cases_claim_lines ON collecting_company_cases_claim_lines.collecting_company_case_id = collecting_company_cases.id
	// WHERE collecting_company_cases_claim_lines.claim_type = 15 AND collecting_company_cases.creditor_id NOT IN(1136,2000,1068)
	// GROUP BY collecting_company_cases.id";
    // $o_query = $o_main->db->query($s_sql);
    // $cases_with_payment = ($o_query ? $o_query->result_array() : array());
	// foreach($cases_with_payment as $case_with_payment) {
	// 	$s_sql = "UPDATE collecting_company_cases SET checkbox_1 = 1
	// 	WHERE collecting_company_cases.id = ?";
	//     $o_query = $o_main->db->query($s_sql, array($case_with_payment['id']));
	// }
	// $sql = "SELECT cc.*, crcp.reminder_process_id FROM collecting_cases cc
	// JOIN creditor_reminder_custom_profiles crcp ON crcp.id = cc.reminder_profile_id
	// WHERE cc.reminder_profile_id > 0 AND cc.collecting_cases_process_step_id > 0";
	// $o_query = $o_main->db->query($sql);
	// $cases_with_profile = $o_query ? $o_query->result_array() : array();
	// $wrongConnectedCount = 0;
	// foreach($cases_with_profile as $case_with_profile) {
	// 	$s_sql = "SELECT collecting_cases_process_steps.* FROM collecting_cases_process_steps WHERE collecting_cases_process_steps.collecting_cases_process_id = ? AND collecting_cases_process_steps.id = ?";
    //     $o_query = $o_main->db->query($s_sql, array($case_with_profile['reminder_process_id'], $case_with_profile['collecting_cases_process_step_id']));
    //     $current_step = ($o_query ? $o_query->row_array() : array());
	// 	if(!$current_step){
	// 		$wrongConnectedCount++;
	// 		$wrong_case_ids[] = $case_with_profile['id'];
	// 	}
	// }
	// var_dump(json_encode($wrong_case_ids));
	// echo $wrongConnectedCount." cases wrong";


	// $wrong_case_ids = json_decode('["104","344","350","351","367","369","371","411","1336","1370","1923","2447","2545","2548","2549","2554","2555","2558","2559","2577","2584","2617","2618","2619","2988","2992","3959","4551","4552","4554","4854"]', true);
	// foreach($wrong_case_ids as $wrong_case_id){
	// 	$sql = "SELECT cc.* FROM collecting_cases cc
	// 	WHERE cc.id = ?";
	// 	$o_query = $o_main->db->query($sql, array($wrong_case_id));
	//  	$case = $o_query ? $o_query->row_array() : array();
	// 	if($case){
	// 		$s_sql = "SELECT creditor_reminder_custom_profiles.* FROM creditor_reminder_custom_profiles WHERE creditor_reminder_custom_profiles.creditor_id = ? AND content_status < 2";
	//         $o_query = $o_main->db->query($s_sql, array($case['creditor_id']));
	//         $custom_profiles = ($o_query ? $o_query->result_array() : array());
	// 		$profile_id = 0;
	// 		foreach($custom_profiles as $custom_profile){
	// 			$s_sql = "SELECT collecting_cases_process_steps.* FROM collecting_cases_process_steps WHERE collecting_cases_process_steps.collecting_cases_process_id = ? AND collecting_cases_process_steps.id = ?";
	// 	        $o_query = $o_main->db->query($s_sql, array($custom_profile['reminder_process_id'], $case['collecting_cases_process_step_id']));
	// 	        $current_step = ($o_query ? $o_query->row_array() : array());
	// 			if($current_step){
	// 				$profile_id = $custom_profile['id'];
	// 			}
	// 		}
	// 		if($profile_id == 0){
	// 			$s_sql = "SELECT creditor_reminder_custom_profiles.* FROM creditor_reminder_custom_profiles WHERE creditor_reminder_custom_profiles.creditor_id = ? AND content_status >= 2";
	// 	        $o_query = $o_main->db->query($s_sql, array($case['creditor_id']));
	// 	        $custom_profiles = ($o_query ? $o_query->result_array() : array());
	// 			foreach($custom_profiles as $custom_profile){
	// 				$s_sql = "SELECT collecting_cases_process_steps.* FROM collecting_cases_process_steps WHERE collecting_cases_process_steps.collecting_cases_process_id = ? AND collecting_cases_process_steps.id = ?";
	// 		        $o_query = $o_main->db->query($s_sql, array($custom_profile['reminder_process_id'], $case['collecting_cases_process_step_id']));
	// 		        $current_step = ($o_query ? $o_query->row_array() : array());
	// 				if($current_step){
	// 					$profile_id = $custom_profile['id'];
	// 				}
	// 			}
	// 		}
	// 		if($profile_id > 0) {
	// 			$s_sql = "UPDATE collecting_cases SET reminder_profile_id = ? WHERE id = ?";
	// 	        $o_query = $o_main->db->query($s_sql, array($profile_id, $case['id']));
	// 		}
	// 	}
	// }
}
// List btn
// if($variables->loggID == "byamba@dcode.no"){
//
// 	$sql = "SELECT * FROM collecting_cases_objection WHERE collecting_company_case_id > 0";
// 	$o_query = $o_main->db->query($sql);
// 	$all_objections = $o_query ? $o_query->result_array() : array();
//
// 	foreach($all_objections as $all_objection) {
// 		$reason = 0;
// 		if($all_objection['objection_type_id'] == 1) {
// 			$reason = 4;
// 		} else if($all_objection['objection_type_id'] == 2) {
// 			$reason = 6;
// 		} else if($all_objection['objection_type_id'] == 3) {
// 			$reason = 5;
// 		} else if($all_objection['objection_type_id'] == 4) {
// 			$reason = 3;
// 		} else if($all_objection['objection_type_id'] == 5) {
// 			$reason = 3;
// 		}
// 		$s_sql = "INSERT INTO collecting_company_case_paused SET
// 		id=NULL,
// 		moduleID = ?,
// 		created = ?,
// 		createdBy= ?,
// 		collecting_company_case_id = ?,
// 		created_date = ?,
// 		pause_reason_comment = ?,
// 		pause_reason = ?,
// 		closed_date = ?,
// 		closed_comment = ?";
//
// 		$o_main->db->query($s_sql, array($all_objection['moduleID'], $all_objection['created'], $all_objection['createdBy'], $all_objection['collecting_company_case_id'], $all_objection['created'], $all_objection['message_from_debitor'], $reason, $all_objection['objection_closed_date'], $all_objection['objection_closed_handling_description']));
//
// 	}
// }
require_once __DIR__ . '/list_btn.php';
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

$sql = "select * from accountinfo";
$o_query = $o_main->db->query($sql);
$v_accountinfo = $o_query ? $o_query->row_array() : array();

$cid = $o_main->db->escape_str($_GET['cid']);

$sql = "SELECT * FROM collecting_company_cases WHERE id = $cid";
$o_query = $o_main->db->query($sql);
$caseData = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
$o_query = $o_main->db->query($s_sql, array($caseData['creditor_id']));
$creditor = ($o_query ? $o_query->row_array() : array());

$s_sql = "SELECT * FROM customer WHERE customer.id = ?";
$o_query = $o_main->db->query($s_sql, array($caseData['debitor_id']));
$debitor = ($o_query ? $o_query->row_array() : array());

$s_sql = "SELECT * FROM collecting_cases_collecting_process WHERE collecting_cases_collecting_process.id = ?";
$o_query = $o_main->db->query($s_sql, array($caseData['collecting_process_id']));
$collectingProcess = ($o_query ? $o_query->row_array() : array());

function formatHour($hour){
	return str_replace(".", ",", floatval(number_format($hour, 2, ".", "")));
}

$list_filter = isset($_SESSION['list_filter']) ? ($_SESSION['list_filter']) : 'warning';
$sublist_filter = $_SESSION['sublist_filter'] ? ($_SESSION['sublist_filter']) : '';
$mainlist_filter = $_SESSION['mainlist_filter'] ? ($_SESSION['mainlist_filter']) : 'collectingLevel';
$responsibleperson_filter = $_SESSION['responsibleperson_filter'] ? ($_SESSION['responsibleperson_filter']) : '';
$list_filter_main = $_SESSION['list_filter_main'] ? ($_SESSION['list_filter_main']) : '';
$search_filter = $_SESSION['search_filter'] ? ($_SESSION['search_filter']) : '';
$casetype_filter = $_SESSION['casetype_filter'] ? $_SESSION['casetype_filter'] : '';
$search_by = $_SESSION['search_by'] ? ($_SESSION['search_by']) : 1;
$show_not_zero_filter = $_SESSION['show_not_zero_filter'] ? ($_SESSION['show_not_zero_filter']) : 0;
$page = $_SESSION['listpagePage'] ? ($_SESSION['listpagePage']) : 0;
$closed_reason_filter = $_SESSION['closed_reason_filter'] ? $_SESSION['closed_reason_filter'] : 0;
$debitor_type_filter =  $_SESSION['debitor_type_filter'] ? $_SESSION['debitor_type_filter'] : 0;	
$amount_from_filter =  $_SESSION['amount_from_filter'] ? $_SESSION['amount_from_filter'] : '';	
$amount_to_filter =  $_SESSION['amount_to_filter'] ? $_SESSION['amount_to_filter'] : '';	


$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list&list_filter=".$list_filter."&search_filter=".$search_filter."&mainlist_filter=".$mainlist_filter."&sublist_filter=".$sublist_filter."&show_not_zero_filter=".$show_not_zero_filter."&closed_reason_filter=".$closed_reason_filter."&debitor_type_filter=".$debitor_type_filter."&amount_from_filter=".$amount_from_filter."&amount_to_filter=".$amount_to_filter."&page=".$page;

if($_GET['backToCreditor']){
	$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CreditorsOverview&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$_GET['backToCreditor']."&mainlist_filter=case&list_filter=".$list_filter."&search_filter=".$search_filter;
}
if($_GET['backToWorklist']){
	$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCaseHandling&folderfile=output&folder=output&inc_obj=list&&mainlist_filter=".$mainlist_filter."&list_filter=".$list_filter."&search_filter=".$search_filter;
	if(isset($_SESSION['objection_status'])) {
		$s_list_link .= "&objection_status=".$_SESSION['objection_status'];
	}
	if(isset($_SESSION['dateFrom'])) {
		$s_list_link .= "&dateFrom=".$_SESSION['dateFrom'];
	}
	if(isset($_SESSION['dateTo'])) {
		$s_list_link .= "&dateTo=".$_SESSION['dateTo'];
	}
	if(isset($_SESSION['creditor_id'])) {
		$s_list_link .= "&creditor_id_filter=".$_SESSION['creditor_id'];
	}
}
$s_page_reload_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$caseData['id']."&view=".$list_filter_main;

$registered_group_list = array();
$v_membersystem = array();
$v_registered_usernames = array();
$o_query = $o_main->db->query("SELECT * FROM cache_userlist_access");
$v_cache_userlist = $o_query ? $o_query->result_array() : array();
foreach($v_cache_userlist as $v_user_cached_info) {
	$v_membersystem[$v_user_cached_info['username']] = $v_user_cached_info;
    if($v_user_cached_info['user_id'] > 0) $v_registered_usernames[] = $v_user_cached_info['username'];
	$registered_group_list[$v_user_cached_info['username']] = json_decode($v_user_cached_info['groups'], true);
}

include("fnc_calculate_interest.php");
$collectingLevelArray = array(0=>$formText_NoUpdate_output, 1=>$formText_Reminder_output, 2=>$formText_DebtCollectionWarning_output, 3=>$formText_PaymentEncouragement_output,4=>$formText_HeavyFeeWarning_output, 5=>$formText_LastWarningBeforeLegalAction_output, 6=>$formText_LegalAction_output);

$s_sql = "SELECT * FROM collecting_system_settings ORDER BY id ASC";
$o_query = $o_main->db->query($s_sql);
$system_settings = ($o_query ? $o_query->row_array() : array());

$objection_after_days = intval($system_settings['default_days_after_closed_objection_to_process']);
if($creditor['days_after_closed_objection_to_process'] != NULL){
    $objection_after_days = $creditor['days_after_closed_objection_to_process'];
}

$s_sql = "SELECT * FROM collecting_company_case_paused WHERE collecting_company_case_id = ? ORDER BY created_date DESC";
$o_query = $o_main->db->query($s_sql, array($caseData['id']));
$objections = ($o_query ? $o_query->result_array() : array());

$s_sql = "SELECT * FROM collecting_company_case_paused WHERE collecting_company_case_id = ? AND IFNULL(pause_reason, 0) = 0 ORDER BY created_date DESC";
$o_query = $o_main->db->query($s_sql, array($caseData['id']));
$returned_letters = ($o_query ? $o_query->result_array() : array());

$s_sql = "SELECT * FROM collecting_cases_main_status_basisconfig ORDER BY id ASC";
$o_query = $o_main->db->query($s_sql, array());
$main_statuses = ($o_query ? $o_query->result_array() : array());


$s_sql = "SELECT * FROM collecting_cases_sub_status_basisconfig WHERE collecting_cases_main_status_id = ? ORDER BY id ASC";
$o_query = $o_main->db->query($s_sql, array($caseData['status']));
$sub_statuses = ($o_query ? $o_query->result_array() : array());
// calculate_interest(array(), $caseData);
// var_dump(calculate_interest(array(), $caseData));
// if($variables->loggID == "byamba@dcode.no"){
// 	$interestArray = calculate_interest(array(), $caseData);
// 	var_dump($interestArray);
// }
$differenceInAddress = false;
if($debitor['paStreet'] != $debitor['extraStreet']){
	$differenceInAddress = true;
}
if($debitor['paPostalNumber'] != $debitor['extraPostalNumber']){
	$differenceInAddress = true;
}
if($debitor['paCity'] != $debitor['extraCity']){
	$differenceInAddress = true;
}
if($debitor['paCountry'] != $debitor['extraCountry']){
	$differenceInAddress = true;
}

//Analyze history
$v_fields = array(
	'extraName' => $formText_CollectingName_Output,
	'extraPublicRegisterId' => $formText_CollectingPublictRegisterId_Output,
	'extra_social_security_number' => $formText_CollectingSocialSecurityNumber_Output,
	'extra_phone' => $formText_CollectingPhone_Output,
	'extra_invoice_email' => $formText_CollectingInvoiceEmail_Output,
	'extraStreet' => $formText_Street_Output,
	'extraPostalNumber' => $formText_PostalNumber_Output,
	'extraCity' => $formText_City_Output,
	'extraCountry' => $formText_Country_Output,
	'customer_type_for_collecting_cases' => $formText_CollectingCustomerType_Output,
);
$v_current = $debitor;
$v_debitor_history = array();
$s_sql = "SELECT created, content_value FROM sys_content_history WHERE content_id = '".$o_main->db->escape_str($debitor['id'])."' AND content_table = 'customer' ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0)
foreach($o_query->result_array() as $v_row)
{
	$v_json = json_decode($v_row['content_value'], TRUE);
	
	$b_found = FALSE;
	foreach($v_fields as $s_field => $s_label) if(array_key_exists($s_field, $v_json)) $b_found = TRUE;

	if($b_found)
	{
		if($v_json['extraName'] == $v_current['extraName']
		&& $v_json['extraPublicRegisterId'] == $v_current['extraPublicRegisterId']
		&& $v_json['extra_social_security_number'] == $v_current['extra_social_security_number']
		&& $v_json['extra_phone'] == $v_current['extra_phone']
		&& $v_json['extra_invoice_email'] == $v_current['extra_invoice_email']
		&& $v_json['extraStreet'] == $v_current['extraStreet']
		&& $v_json['extraPostalNumber'] == $v_current['extraPostalNumber']
		&& $v_json['extraCity'] == $v_current['extraCity']
		&& $v_json['extraCountry'] == $v_current['extraCountry']
		&& $v_json['customer_type_for_collecting_cases'] == $v_current['customer_type_for_collecting_cases'])
		{
		} else {
			$v_debitor_history[$v_row['created']] = $v_json;
			$v_current = $v_json;
		}
	}
}

include "fnc_check_loss.php";

if($creditor['loss_bookaccount'] != "") {
	$case_has_loss = check_loss($creditor, $caseData);
}
?>
<div id="p_container" class="p_container">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
                <a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list" style="display: block; margin-bottom:10px;"><?php echo $formText_BackToList_outpup;?></a>

				<?php
				if($differenceInAddress){
					?>
					<div class="customer_difference_info"><?php echo $formText_ThereIsDifferenceBetweenCrmAndCollectingAddress_output;?></div>
					<?php
				}
				?>
				<div class="p_pageDetails">
					<div class="p_pageDetailsTitle">
						<div class="" style="float: left">
							<?php echo $formText_CaseNumber_output;?>
							<div class="caseId"><span class="caseIdText"><?php echo $caseData['id'];?></span></div>
							<?php if($caseData['content_status'] == 2) echo '('.$formText_Deleted_output.')'?>
							<!-- <div class="caseCreated">
								<?php echo $formText_Created_output.": ". $caseData['created'];?> <?php echo $formText_CreatedBy_output.": ".$caseData['createdBy'];?>
							</div> -->
						</div>
						<div class="" style="float: right;">
							<?php 
								if($caseData['dispute_case']) {
									echo "<span style='color: red;'>".$formText_DisputedCase_Output."</span>";
								}

							
							?>
							<span class="">
								<?php echo $formText_CaseLimitationDate_output;?> 
								<?php if($caseData['case_limitation_date'] != "0000-00-00" && $caseData['case_limitation_date'] != "") echo date("d.m.Y", strtotime($caseData['case_limitation_date'])); ?> 
								<span class="glyphicon glyphicon-pencil edit_case_limitation_date"></span>
								<br/>
								<?php 
								if($caseData['approved_to_expire']) { 
									echo $formText_ApprovedForExpireDate_output.": ".date("d.m.Y", strtotime($caseData['approved_to_expire_date']))."<br/>";
									echo $formText_ApprovedBy_output.": ".$caseData['approved_to_expire_by']."<br/>";
									echo $formText_Note_output.": ".substr($caseData['approved_to_expire_note'], 0, 30)."".(mb_strlen($caseData['approved_to_expire_note']) > 30 ? "...":"");
								} ?>
							</span>
						</div>

						<div class="clear"></div>
					</div>

					<div class="p_contentBlock">
					    <div class="caseDetails">
					        <table class="mainTable" width="100%" border="0" cellpadding="0" cellspacing="0">
					        	<tr>
					                <td class="txt-label"><?php echo $formText_Creditor_output;?></td>
					                <td class="txt-value">
					                	<?php echo $creditor['companyname'];?>

										<?php 
										if($creditor['creditor_marked_ceases_to_exist_date'] != "0000-00-00" && $creditor['creditor_marked_ceases_to_exist_date'] != ""){
											echo "<span class='marked_to_cease_to_exist'>".$creditor['creditor_marked_ceases_to_exist_reason']." ".date("d.m.Y", strtotime($creditor['creditor_marked_ceases_to_exist_date']))."</span>";
										}
										?>
					                </td>
					            </tr>
								<tr>
					                <td class="txt-label"><?php echo $formText_CreditorEmail_output;?></td>
					                <td class="txt-value">
					                	<?php echo $creditor['companyEmail'];?>
					                </td>
					            </tr>
					            <tr>
					                <td class="txt-label"><?php echo $formText_CreditorAddress_output;?></td>
					                <td class="txt-value"><?php echo $creditor['companypostalbox']." ".$creditor['companyzipcode']." ".$creditor['companypostalplace'];?></td>
					            </tr>
								<tr>
					                <td class="txt-label"><?php echo $formText_CreditorPhone_output;?></td>
					                <td class="txt-value"><?php echo $creditor['companyphone'];?></td>
					            </tr>
								<?php /*?><tr>
					                <td class="txt-label"><?php echo $formText_CreditorUsers_output;?></td>
					                <td class="txt-value">
					                	<?php
										$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE creditor_id = ? ORDER BY created DESC";
										$o_query = $o_main->db->query($s_sql, array($creditor['id']));
										$connections = ($o_query ? $o_query->result_array() : array());
										foreach($connections as $connection) {
											?>
											<div><?php echo $connection['username']." - ".date("d.m.Y H:i:s", strtotime($connection['created']));?></div>
											<?php
										}
										?>
					                </td>
					            </tr><?php */?>
								<tr><td colspan="2">
									<b><?php echo $formText_Contactpersons_output;?></b> <span class="add_contactperson edit_contactperson"><?php echo $formText_AddContactperson_output;?></span>
									<table class="table">
										<tr>
											<th><?php echo $formText_Name_output;?></th>
											<th><?php echo $formText_MessagesRegardingCases_output;?></th>
											<th><?php echo $formText_ContactpersonForAgreement_output;?></th>
											<th><?php echo $formText_ReceiveSettlementReports_output;?></th>
											<th></th>
										</tr>
										<?php
										$s_sql = "SELECT * FROM creditor_contact_person WHERE creditor_id = ? ORDER BY name ASC";
										$o_query = $o_main->db->query($s_sql, array($creditor['id']));
										$contactpersons = ($o_query ? $o_query->result_array() : array());
										foreach($contactpersons as $contactperson) {
											?>
											<tr>
												<td><?php echo $contactperson['name'];?><br/><?php echo $contactperson['email'];?><br/><?php echo $contactperson['phone'];?><br/><?php echo $contactperson['position'];?></td>

												<td><input type="checkbox" disabled readonly <?php if($contactperson['messages_regarding_cases']) echo 'checked';?>></td>
												<td><input type="checkbox" disabled readonly <?php if($contactperson['contactperson_for_agreement']) echo 'checked';?>></td>
												<td><input type="checkbox" disabled readonly <?php if($contactperson['receive_settlement_reports']) echo 'checked';?>></td>
												<td>
													<span class="glyphicon glyphicon-pencil edit_contactperson" data-contactpersonid="<?php echo $contactperson['id'];?>"></span>
													<span class="glyphicon glyphicon-trash delete_contactperson" data-contactpersonid="<?php echo $contactperson['id'];?>"></span>
												</td>
											</tr>
											<?php
										}
										?>
									</table>
								</td></tr>

								<tr><td>&nbsp;</td><td></td></tr>
								<tr><td style="border-bottom: 1px solid #cecece;">&nbsp;</td><td style="border-bottom: 1px solid #cecece;"></td></tr>
								<tr><td>&nbsp;</td><td></td></tr>
								<tr>
					                <td class="txt-label"><?php echo $formText_Debitor_output;?></td>
					                <td class="txt-value">
					                	<?php echo $debitor['extraName'];?>
										<?php 
										if($debitor['customer_marked_ceases_to_exist_date'] != "0000-00-00" && $debitor['customer_marked_ceases_to_exist_date'] != ""){
											echo "<span class='marked_to_cease_to_exist'>".$debitor['customer_marked_ceases_to_exist_reason']." ".date("d.m.Y", strtotime($debitor['customer_marked_ceases_to_exist_date']))."</span>";
										}
										?>
					                </td>
					            </tr>
								<tr>
					                <td class="txt-label"><?php echo $formText_InvoiceEmail_output;?></td>
					                <td class="txt-value">
					                	<?php echo $debitor['extra_invoice_email'];?>
					                </td>
					            </tr>
								<tr>
					                <td class="txt-label"><?php echo $formText_Phone_output;?></td>
					                <td class="txt-value">
					                	<?php echo $debitor['extra_phone'];?>
					                </td>
					            </tr>
								<?php if(2 == $debitor['customer_type_collect_addition']) { ?>
								<tr>
					                <td class="txt-label"><?php echo $formText_SocialSecurityNumber_output;?></td>
					                <td class="txt-value">
					                	<?php echo $debitor['extra_social_security_number'];?>
					                </td>
					            </tr>
								<?php } ?>
					            <tr>
					                <td class="txt-label"><?php echo $formText_DebitorAddress_output;?></td>
					                <td class="txt-value"><?php echo $debitor['extraStreet']." ".$debitor['extraPostalNumber']." ".$debitor['extraCity'];?> <span class="glyphicon glyphicon-pencil edit_collecting_address" data-customer-id="<?php echo $debitor['id'];?>"></span>
									<?php if(0 < count($v_debitor_history)) { ?>
									<a href="#" class="show_debitor_history_btn" data-customer-id="<?php echo $debitor['id'];?>" title="<?php echo $formText_History_Output;?>"><span class="badge"><?php echo count($v_debitor_history);?></span></a>
									<?php } ?>
									</td>
					            </tr>

					            <tr>
					                <td class="txt-label"><?php echo $formText_CustomerType_output;?></td>
					                <td class="txt-value"><?php
									$is_company = false;
									 if($debitor['customer_type_for_collecting_cases'] == 0) {
									 	echo $formText_UseCrmCustomerType_output;
										$customer_type_collect_debitor = $debitor['customer_type_collect'];
										if($debitor['customer_type_collect_addition'] > 0) {
											$customer_type_collect_debitor = $debitor['customer_type_collect_addition'] - 1;
										}
										if($customer_type_collect_debitor == 0) {
											echo " (".$formText_Company_output.")";
											$is_company = true;
										} else if($customer_type_collect_debitor == 1) {
											echo " (".$formText_Person_output.")";
										}
									 } else if($debitor['customer_type_for_collecting_cases'] == 1) {
										 echo $formText_Company_output;
										 $is_company = true;
									 } else if($debitor['customer_type_for_collecting_cases'] == 2) {
										 echo $formText_Person_output;
									}
									if($is_company) {
										if($debitor['confirmed_as_company'] != '0000-00-00' && $debitor['confirmed_as_company'] != ""){
											echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$formText_Confirmed_output;
											?>
											<span class="glyphicon glyphicon-info-sign hoverEye">
												<div class="hoverInfo hoverInfo2 hoverInfoFull">
													<?php echo $formText_ConfirmedBy_output." ".$debitor['confirmed_by']." ".date("d.m.Y", strtotime($debitor['confirmed_as_company']));?>
												</div>
											</span>
											<?php
										} else {
											echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$formText_NotConfirmed_output;
											echo "<span class='confirm_company' data-id='".$debitor['id']."'>".$formText_Confirm_output."</span>";
										}
										?>
										<?php
									}
									?>

									</td>
					            </tr>
								<?php if($is_company) { ?>
									<tr>
										<td class="txt-label"><?php echo $formText_OrgNr_output;?></td>
										<td class="txt-value"><?php if($debitor['extraPublicRegisterId'] != "") { echo $debitor['extraPublicRegisterId']; } else {echo $debitor['publicRegisterId'];  }?></td>
									</tr>
								<?php } else { ?>
									<tr>
										<td class="txt-label"><?php echo $formText_PersonNumber_output;?></td>
										<td class="txt-value"><?php echo $debitor['personnumber']?></td>
									</tr>
								<?php } ?>
					            <tr>
					                <td class="txt-label"><?php echo $formText_ConnectOtherParts_output;?></td>
					                <td class="txt-value">
										<?php
										$s_sql = "SELECT * FROM collecting_cases_other_parts WHERE collecting_cases_other_parts.collecting_company_case_id = ?";
										$o_query = $o_main->db->query($s_sql, array($caseData['id']));
										$otherParts = ($o_query ? $o_query->result_array() : array());
										foreach($otherParts as $otherPart) {
											$s_sql = "SELECT * FROM customer WHERE customer.id = ?";
											$o_query = $o_main->db->query($s_sql, array($otherPart['customer_id']));
											$otherPartCustomer = ($o_query ? $o_query->row_array() : array());
											?>
											<div class="">
												<?php echo $otherPartCustomer['name']." ".$otherPartCustomer['middlename']." ".$otherPartCustomer['lastname']?>
												<?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-otherpart editBtnIcon" data-case-id="<?php echo $cid; ?>" data-otherpart-id="<?php echo $otherPart['id'];?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?>
												<?php if($moduleAccesslevel > 110) { ?>
												<button class="output-btn small output-delete-otherpart editBtnIcon" data-case-id="<?php echo $cid; ?>" data-otherpart-id="<?php echo $otherPart['id'];?>">
													<span class="glyphicon glyphicon-trash"></span>
												<?php } ?>
											</div>
											<?php
										}
										?>
										<a href="#" class="output-edit-otherpart" data-case-id="<?php echo $cid?>">+ <?php echo $formText_AddAnother_output;?></a>
									</td>
					            </tr>
									<tr>
										<td class="txt-label"><?php echo $formText_kidNumber_output;?></td>
										<td class="txt-value"><?php echo $caseData['kid_number']?></td>
									</tr>
								<tr>
					                <td class="btn-edit" colspan="2"><?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-case-detail editBtnIcon" data-case-id="<?php echo $cid; ?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?></td>
					            </tr>

					        </table>
							<?php

							$s_sql = "SELECT * FROM collecting_cases_collecting_process_steps WHERE collecting_cases_collecting_process_id = '".$o_main->db->escape_str($caseData['collecting_process_id'])."'  ORDER BY sortnr ASC";
							$o_query = $o_main->db->query($s_sql);
							$all_steps_collecting = $o_query ? $o_query->result_array() : array();


							// if(intval($caseData['status']) == 0){
							// 	$caseData['status'] = 1;
							// }
							// $s_sql = "SELECT * FROM collecting_cases_main_status_basisconfig WHERE id = ? ORDER BY id ASC";
							// $o_query = $o_main->db->query($s_sql, array(intval($caseData['status'])));
							// $collecting_case_status = ($o_query ? $o_query->row_array() : array());

							// $s_sql = "SELECT * FROM collecting_cases_sub_status_basisconfig WHERE id = ? ORDER BY id ASC";
							// $o_query = $o_main->db->query($s_sql, array(intval($caseData['sub_status'])));
							// $collecting_case_substatus = ($o_query ? $o_query->row_array() : array());

						    $s_sql = "SELECT * FROM collecting_cases_payment_plan WHERE collecting_company_case_id = '".$o_main->db->escape_str($caseData['id'])."' AND (status = 0 OR status is null) ORDER BY sortnr ASC";
						    $o_query = $o_main->db->query($s_sql);
						    $active_payment_plan = ($o_query ? $o_query->row_array() : array());
							?>
							<div class="collectinglevelDisplay">
								<?php if($caseData['dispute_case']) { ?>
									<div class="disputeCaseText"><?php echo $formText_DisputeCase_Output;?></div>
								<?php } ?>
								<div>
									<?php echo $formText_Status_output;?>
									<span class="levelText">
										<?php
										if($caseData['collecting_case_surveillance_date'] != '0000-00-00' && $caseData['collecting_case_surveillance_date'] != ''){
											if(($caseData['case_closed_date'] == "0000-00-00" OR $caseData['case_closed_date'] == "")){
												echo $formText_Surveillance_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($caseData['collecting_case_surveillance_date'])).")";
											} else {
												echo $formText_ClosedInSurveillance_output;
											}
										} else if($caseData['collecting_case_manual_process_date'] != '0000-00-00' && $caseData['collecting_case_manual_process_date'] != ''){
											if(($caseData['case_closed_date'] == "0000-00-00" OR $caseData['case_closed_date'] == "")){
												echo $formText_ManualProcess_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($caseData['collecting_case_manual_process_date'])).")";
											} else {
												echo $formText_ClosedInManualProcess_output;
											}
										} else if($caseData['collecting_case_created_date'] != '0000-00-00' && $caseData['collecting_case_created_date'] != ''){
											if(($caseData['case_closed_date'] == "0000-00-00" OR $caseData['case_closed_date'] == "")){
												echo $formText_CollectingLevel_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($caseData['collecting_case_created_date'])).")";
											} else {
												echo $formText_ClosedInCollectingLevel_output;
											}
										} else if($caseData['warning_case_created_date'] != '0000-00-00' && $caseData['warning_case_created_date'] != '') {
											if(($caseData['case_closed_date'] == "0000-00-00" OR $caseData['case_closed_date'] == "")){
												echo $formText_WarningLevel_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($caseData['warning_case_created_date'])).")";
											} else {
												echo $formText_ClosedInWarningLevel_output;
											}
										}
										// echo $collecting_case_status['name'];
										?>
										<span class="edit_case_step glyphicon glyphicon-pencil"></span>
									</span>
								</div>
								<?php /* if(count($sub_statuses) > 0) {?>
									<div>
										<?php echo $formText_SubStatus_output;?>
										<span class="levelText">
											<?php
											echo $collecting_case_substatus['name'];
											?>
										</span>
									</div>
								<?php } */?>
								<div>
									<?php echo $formText_DueDate_output;?>
									<?php if($active_payment_plan){
										?>
										<span class="levelText"><?php echo $formText_ActivePaymentAgreement_output;?></span>
										<?php
									} else { ?>
										<span class="processText">
											<?php
											if($caseData['due_date'] != "0000-00-00" && $caseData['due_date'] != "") echo date("d.m.Y", strtotime($caseData['due_date']));
											?>
											<span class="edit_due_date glyphicon glyphicon-pencil"></span>
										</span>
									<?php } ?>
								</div>
								<?php
								if($caseData['case_closed_date'] != "0000-00-00" && $caseData['case_closed_date'] != ""){
									?>
									<div>
										<?php echo $formText_ClosedDate_output;?>
										<span class="processText">
											<?php
											echo date("d.m.Y", strtotime($caseData['case_closed_date']));
											?>
										</span>
									</div>
									<div>
										<span class="processText">
											<?php
											$closed_reasons = array($formText_FullyPaid_output, $formText_PayedWithLessAmountForgiven_output, $formText_ClosedWithoutAnyPayment_output,$formText_ClosedWithPartlyPayment_output,$formText_CreditedByCreditor_output,$formText_DrawnByCreditorToDeleteFees_output);

											if($caseData['case_closed_reason'] >= 0){
												echo $closed_reasons[$caseData['case_closed_reason']];
											}
											?>
										</span>
										<div class="clear"></div>
									</div>

									<div>
										<?php
										echo $formText_ForgivenAmountOnMainClaim_output." ".number_format($caseData['forgivenAmountOnMainClaim'], 2, ",", "")."<br/>";
										echo $formText_ForgivenAmountExceptMainClaim_output." ".number_format($caseData['forgivenAmountExceptMainClaim'], 2, ",", "")."<br/>";
										echo $formText_OverpaidAmount_output." ".number_format($caseData['overpaidAmount'], 2, ",", "");
										?>
										<span class="edit_forgiven glyphicon glyphicon-pencil"></span>
									</div>
									<?php
								}
								?>
								<br/>
								<br/>
								<?php

								$s_sql = "SELECT * FROM collecting_company_case_paused WHERE collecting_company_case_id = ? AND IFNULL(closed_date, '0000-00-00 00:00:00') = '0000-00-00 00:00:00'  ORDER BY created_date DESC";
								$o_query = $o_main->db->query($s_sql, array($caseData['id']));
								$activeObjections = ($o_query ? $o_query->result_array() : array());

								if(count($activeObjections) > 0){
									echo $formText_CaseHasActiveObjections_output;
								}
								$current_step = array();
								$current_collecting_step = array();

								$next_step = $all_steps[0];
								if( $caseData['collecting_cases_process_step_id'] == 0){
									$next_step = $all_steps_collecting[0];
								}

								foreach($all_steps_collecting as $index=>$all_step) {
									if($all_step['id'] == $caseData['collecting_cases_process_step_id'] ){
										$current_collecting_step = $all_step;
										$next_step = $all_steps_collecting[$index+1];
									}
								}
								?>
								<div>
									<?php echo $formText_CollectingProcess_output;?>
									<span class="processText">
										<?php
										echo $collectingProcess['name'];
										?>
									</span>
								</div>
								<div class="processStepWrapper  <?php if(intval($caseData['collecting_cases_process_step_id']) == 0) echo 'active_step';?>" >
									<?php echo $formText_NotStarted_output;?>
								</div>

								<?php
								
								$onLastStep = false;
								foreach($all_steps_collecting as $step_index => $all_step) {
									$isCurrentStep = false;
									$isNextStep = false;
									$isManual = false;
									if($current_collecting_step['id']  == $all_step['id']){
										if(intval($caseData['continuing_process_step_id']) == 0){												
											$isCurrentStep = true;
										}
										if($step_index == count($all_steps_collecting) - 1){
											$onLastStep = true;
											if($caseData['collecting_case_manual_process_date'] != "0000-00-00" && $caseData['collecting_case_manual_process_date'] != "") {
												$isManual = true;
											}
										}
									}
									if($next_step['id']  == $all_step['id']){
										$isNextStep = true;
									}
									?>
									<div class="processStepWrapper <?php if($isCurrentStep && !$isManual){ echo 'active_step'; }?>">
										<?php
										echo $all_step['name'];
										if($isCurrentStep){
											echo " (".$formText_CurrentStep_output.")";
										} else if($isNextStep) {
											if(count($activeObjections) == 0) {
												?>
												(<?php echo $formText_NextStep_output;?>
													<?php
													if($caseData['due_date'] != "0000-00-00" && $caseData['due_date'] != ""){
														$dueDateTime = strtotime($caseData['due_date']);
														$correctDueDateTime = strtotime("+".($next_step['days_after_due_date'])." days", $dueDateTime);

														$s_sql = "SELECT * FROM collecting_company_case_paused WHERE collecting_company_case_id = ? AND IFNULL(closed_date, '0000-00-00 00:00:00') <> '0000-00-00 00:00:00' AND (pause_reason = 3 OR pause_reason = 4 OR pause_reason = 5 OR pause_reason = 6)  ORDER BY closed_date DESC";
									                    $o_query = $o_main->db->query($s_sql, array($caseData['id']));
									                    $closedObjection = ($o_query ? $o_query->row_array() : array());
									                    $objectionTime = 0;
									                    if($closedObjection) {
									                        $objectionTime = strtotime("+".($objection_after_days)." days", strtotime($closedObjection['objection_closed_date']));
									                    }
														if($objectionTime > $correctDueDateTime){
									                        $correctDueDateTime = $objectionTime;
									                    }

														?>
														<?php echo date("d.m.Y", $correctDueDateTime);?>
														<?php
													}
													?>
												)
												<?php
											}
										}
										?>
										<div class="clear"></div>
									</div>
									<?php
								}
								if($onLastStep && strtotime($caseData['due_date']) < strtotime(date("Y-m-d"))) {
									if(count($activeObjections) == 0) {
										?>
										<div class="start_continuing_process"><?php echo $formText_StartContinuingProcess_output;?></div>
										<?php
									}
								}
								/*
								?>
								<div class="manual_process_info <?php if($isManual) echo 'active';?>"><?php
								echo $formText_ManualProcess_output;
								if($isManual) echo "&nbsp;". date("d.m.Y", strtotime($caseData['collecting_case_manual_process_date']));
								?>
								</div>
								<?php */
								
								$startedContiuningProcess = false;
								if($caseData['continuing_process_step_id'] > 0) {		
									$startedContiuningProcess = true;							
									$sql = "SELECT * FROM collecting_company_cases_continuing_process_steps WHERE id = ?";
									$o_query = $o_main->db->query($sql, array($caseData['continuing_process_step_id']));
									$current_continuing_step = $o_query ? $o_query->row_array() : array();

									$sql = "SELECT * FROM collecting_company_cases_continuing_process WHERE id = ?";
									$o_query = $o_main->db->query($sql, array($current_continuing_step['collecting_company_cases_continuing_process_id']));
									$continuing_process = $o_query ? $o_query->row_array() : array();
													
									$sql = "SELECT * FROM collecting_company_cases_continuing_process_steps WHERE collecting_company_cases_continuing_process_id = ? ORDER BY sortnr ASC";
									$o_query = $o_main->db->query($sql, array($continuing_process['id']));
									$continuing_steps = $o_query ? $o_query->result_array() : array();
									?>
									<div>
										<br/>
										<?php
										echo $formText_ContinuingProcess_output.": ". $continuing_process['name'];
										?>
									</div>
									<?php
									$step_counter=0;
									foreach($continuing_steps as $continuing_step) {
										$step_counter++;
										?>										
										<div class="processStepWrapper  <?php if(intval($caseData['continuing_process_step_id']) == $continuing_step['id']) echo 'active_step';?>" >
											<?php echo $continuing_step['name'];?>
											
											<?php 
											if(intval($caseData['continuing_process_step_id']) == $continuing_step['id']) {		
												if($step_counter != count($continuing_steps)){										
													?>
													<div class="handleContinuingStep" data-step-id="<?php echo $continuing_step['id']?>"><?php echo $formText_ProcessNextStep_output;?></div>
													<?php
												}
											}
											?>
											<div class="clear"></div>
										</div>
										<?php
									}
									// echo $continuing_step['name'];
								}
								?>
								<?php								
								if($caseData['content_status'] == 0) {
								?>
									<?php
									if(!$startedContiuningProcess) {
										?>
										<div class="processCase" data-case-id="<?php echo $caseData['id'];?>"><?php echo $formText_ProcessCase_output;?></div>
										<?php
									}
									?>
									<?php if($caseData['case_closed_date'] != "0000-00-00" && $caseData['case_closed_date'] != "") { ?>
										<div class="reactivateCase" data-case-id="<?php echo $caseData['id'];?>">
											<?php echo $formText_ReactivateCase_output;?>
										</div>
									<?php } else { ?>
										<div class="stopCase" data-case-id="<?php echo $caseData['id'];?>">
											<?php echo $formText_StopCase_output;?>
										</div>
									<?php } ?>
									<div class="clear"></div>
									<div class="create_restnote" data-case-id="<?php echo $caseData['id'];?>"><?php echo $formText_CreateRestNote_output;?></div>

									<div class="clear"></div>
								<?php } ?>
								<?php if($variables->developeraccess > 5) {
									?>
									<div class="deleteCase" data-case-id="<?php echo $caseData['id'];?>">
										<?php echo $formText_DeleteCase_output;?>
									</div>
									<?php
								}?>
								<?php								
									if(count($activeObjections) == 0) {
									?>
									<div class="createLetter" data-case-id="<?php echo $caseData['id'];?>"><?php echo $formText_CreateLetter_output;?></div>
								<?php 
									} ?>
								<div class="clear"></div>
							</div>
							<div class="clear"></div>
							<div class="checkboxes" style="float: right; margin-top: 5px; margin-right: 5px; text-align: right;">
								<?php
								$feepaid = "";
								if($caseData['without_fee_paid']) {
									$feepaid = "checked";
								}
								$feenotpaid = "";
								if($caseData['without_fee_notpaid']) {
									$feenotpaid = "checked";
								}
								$companyfeepaid = "";
								if($caseData['company_fee_paid']) {
									$companyfeepaid = "checked";
								}
								$companyfeenotpaid = "";
								if($caseData['company_fee_notpaid']) {
									$companyfeenotpaid = "checked";
								}
								echo $formText_WithoutFeePaid_output." <input type='checkbox' class='without_fee_paid' autocomplete='off' value='1' ".$feepaid." style='margin-left: 5px;' /><br/>";
								echo $formText_WithoutFeeNotPaid_output." <input type='checkbox' class='without_fee_notpaid' autocomplete='off' value='1' ".$feenotpaid." style='margin-left: 5px;' /><br/>";
								echo $formText_CompanyFeePaid_output." <input type='checkbox' class='company_fee_paid' autocomplete='off' value='1' ".$companyfeepaid." style='margin-left: 5px;' /><br/>";
								echo $formText_CompanyFeeNotPaid_output." <input type='checkbox' class='company_fee_notpaid' autocomplete='off' value='1' ".$companyfeenotpaid." style='margin-left: 5px;' /><br/>";

								$checkbox1 = "";
								if($caseData['checkbox_1']) {
									$checkbox1 = "checked";
								}
								echo $formText_Checkbox1_output." <input type='checkbox' class='checkbox1' autocomplete='off' value='1' ".$checkbox1." style='margin-left: 5px;' /><br/>";

								?>
								<?php echo $formText_Currency_output;?>
								<select class="currencySelect">
									<option value="0"><?php echo $formText_No_output;?></option>
									<option value="1" <?php if($caseData['currency'] == 1) echo 'selected';?>><?php echo $formText_CurrencyNewCase_output;?></option>
									<option value="2" <?php if($caseData['currency'] == 2) echo 'selected';?>><?php echo $formText_CurrencyRecalculatedCase_output;?></option>
								</select>&nbsp;
								<?php echo $caseData['currency_name'];?>
							</div>
							<div class="clear"></div>


							<div class="createCaseSummaryWrapper">
								<?php
								$s_sql = "SELECT * FROM collecting_company_cases_summary WHERE case_id = ? ORDER BY created DESC";
								$o_query = $o_main->db->query($s_sql, array($caseData['id']));
								$collecting_cases_summaries = ($o_query ? $o_query->result_array() : array());
								foreach($collecting_cases_summaries as $collecting_cases_summary) {
									?>
									<div class=""><?php if($collecting_cases_summary['file'] != "") {

										$fileParts = explode('/',$collecting_cases_summary['file']);
										$fileName = array_pop($fileParts);
										$fileParts[] = rawurlencode($fileName);
										$filePath = implode('/',$fileParts);
										$fileUrl = $extradomaindirroot.$collecting_cases_summary['file'];
										$fileName = basename($collecting_cases_summary['file']);
										if(strpos($collecting_cases_summary['file'],'uploads/protected/')!==false)
										{
											$fileUrl = $extradomaindirroot."/../".$filePath.'?caID='.$_GET['caID'].'&table=collecting_company_cases_summary&field=file&ID='.$collecting_cases_summary['id'];
										}
										?>
										<div class="project-file">
											<div class="project-file-file">
												<a href="<?php echo $fileUrl;?>" download target="_blank"><?php echo $fileName;?></a>
											</div>
										</div>
									<?php }?></div>
									<?php
								}
								?>
								<div class="createCaseSummary"><?php echo $formText_CreateCaseSummary_output;?></div>
							</div>
							<div class="clear"></div>
					    </div>
					</div>
					<?php if($_SERVER['REMOTE_ADDR']=='87.110.235.137' || isset($caseData['currency']) && 0 < $caseData['currency']) { ?>
					<div class="p_contentBlockWrapper">
						<div class="p_pageDetailsSubTitle dropdown_content_show white">
							<?php echo $formText_CurrencyExplanationText_Output;?> <span class="edit_currency_explanation_text glyphicon glyphicon-pencil"></span>
							<div class="showArrow"><span class="glyphicon glyphicon-triangle-bottom"></span></div>
						</div>
						<div class="p_contentBlock noTopPadding dropdown_content">
							<?php echo $caseData['currency_explanation_text'];?>
						</div>
					</div>
					<?php } ?>
					<div class="p_contentBlockWrapper">
						<div class="p_pageDetailsSubTitle dropdown_content_show white">
							<?php echo $formText_Claims_Output;?><?php if($moduleAccesslevel > 10) { ?><button class="addEntryBtn small output-edit-claims" data-case-id="<?php echo $cid; ?>"><?php echo $formText_Add_output;?></button><?php } ?>
							<div class="showArrow"><span class="glyphicon glyphicon-triangle-bottom"></span></div>
						</div>
						<div class="p_contentBlock noTopPadding dropdown_content">
							<?php

							$s_sql = "SELECT * FROM creditor_transactions WHERE collecting_company_case_id = ? AND creditor_id = ?";
							$o_query = $o_main->db->query($s_sql, array($caseData['id'], $caseData['creditor_id']));
							$main_transactions = ($o_query ? $o_query->result_array() : array());
							$link_ids = array();
							foreach($main_transactions as $main_transaction){
								if($main_transaction['link_id'] != ""){
									if(!in_array($main_transaction['link_id'], $link_ids)){
										$link_ids[] = $main_transaction['link_id'];
									}
								}
							}
							$s_sql = "SELECT cccl.*, bconfig.cs_bookaccount_id, bconfig.cs_bookaccount_creditor FROM collecting_company_cases_claim_lines cccl
							LEFT OUTER JOIN collecting_cases_claim_line_type_basisconfig bconfig ON bconfig.id = cccl.claim_type
							WHERE cccl.content_status < 2 AND cccl.collecting_company_case_id = ? AND IFNULL(bconfig.not_include_in_claim, 0) = 0
							ORDER BY cccl.claim_type ASC, cccl.created DESC";
							$o_query = $o_main->db->query($s_sql, array($caseData['id']));
							$claims = ($o_query ? $o_query->result_array() : array());

							

		                    $s_sql = "SELECT * FROM cs_mainbook_voucher WHERE case_id = ? ORDER BY created ASC";
		                    $o_query = $o_main->db->query($s_sql, array($caseData['id']));
		                    $payments = ($o_query ? $o_query->result_array() : array());

							$totalSumPaid = 0;
							$totalSumDue = 0;

							foreach($payments as $payment) {
								$s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."' AND bookaccount_id = 1 ORDER BY id";
								$o_query = $o_main->db->query($s_sql);
								$transactions = ($o_query ? $o_query->result_array() : array());
								foreach($transactions as $transaction) {
									$totalSumPaid += $transaction['amount'];
								}
							}

							?>
							<table class="claimsTable table table-borderless">
								<tr>
									<th width="60%"><?php echo $formText_Name_Output; ?></th>
									<th width="7%" style="text-align: right;">
										<?php echo $formText_Covered_output;?>
									</th>
									<th width="7%" style="text-align: right;">
										<?php echo $formText_Remaining_output;?>
									</th>
									<th width="10%" style="text-align: right;">
										<?php echo $formText_Amount_Output;?>
									</th>
									<th width="6%" style="text-align: right;"></th>
								</tr>
								<?php
								$coveredTypeDisplayed = array();
								foreach($claims as $claim) {									
									?>
									<tr>
										<td width="60%">
											<?php echo $claim['name'];?>
											<?php if($claim['original_due_date'] != "0000-00-00" && $claim['original_due_date'] != ""){ echo date("d.m.Y", strtotime($claim['original_due_date'])); }?>
											<?php if($claim['claim_type'] == 1 && $isRestAmount) echo "(".$formText_Rest_output.")";?>
											<?php

											if($claim['payment_after_closed']) {
												echo '<span style="float: right; color: red;">'.$formText_PaymentAfterClosed_Output.'</span>';
											}
											if($claim['claim_type'] == 18 || $claim['claim_type'] == 15) {
												if($case_has_loss) {
													echo '<span style="float: right; color: red;">'.$formText_CheckIfLoss_Output.'</span>';
												}
											}
											if($claim['claim_type'] == 1) {
												$s_sql = "SELECT * FROM creditor_transactions WHERE creditor_id = ? AND collecting_company_case_id = ?";
												$o_query = $o_main->db->query($s_sql, array($caseData['creditor_id'], $caseData['id']));
												$invoice = ($o_query ? $o_query->row_array() : array());
												// var_dump($invoice, $caseData['creditor_id'], $caseData['id']);
												if($invoice){
													if(!$invoice['open']) {
														echo  '<div style="float: right;"><span style="color: red;">'.$formText_InvoiceIsClosedInAccountingSystem_output.'</span>';
														echo '<div><input type="checkbox" class="allowProcessing" data-claimline_id="'.$claim['id'].'" autocomplete="off"'.($claim['invoice_closed_allow_processing_anyway']?' checked':'').' id="claim_'.$claim['id'].'"/><label for="claim_'.$claim['id'].'">'.$formText_AllowProcessingAnyway_output.'</label></div></div>';
													}
												} else {
													echo  '<div style="float: right;"><span style="color: red;">'.$formText_InvoiceIsNotConnected_output.'</span>';
												}
											}
											if($claim['claim_type'] == 15 || $claim['claim_type'] == 18) {
												$s_sql = "SELECT * FROM creditor_transactions WHERE creditor_id = '".$o_main->db->escape_str($creditor['id'])."' AND company_claimline_id = '".$o_main->db->escape_str($claim['id'])."'";
                								$o_query = $o_main->db->query($s_sql, array($claimline_id));
												$connected_trans = $o_query ? $o_query->row_array() : array();
												if(count($connected_trans) == 0){
													echo '<div style="float: right;"><span style="color: red;">'.$formText_PaymentIsMissing_output.'</span>';
												} else {
													if(!in_array($connected_trans['link_id'], $link_ids)){
														echo '<div style="float: right;"><span style="color: red;">'.$formText_PaymentIsMissing_output.'</span>';
													}
												}
												
											}
											// if($variables->loggID=="byamba@dcode.no"){
											// 	var_dump($invoice);
											// }
											if($claim['note'] != ""){
												?>
												<span class="glyphicon glyphicon-info-sign hoverEye">
													<div class="hoverInfo hoverInfo2 hoverInfoNotes"><?php echo $claim['note'];?></div>
												</span>
												<?php
											}
											?>
										</td>
										<td width="7%" style="text-align: right;">
											<?php
											$coveredSum =  0;
											$remaining_sum = "";
											if($claim['cs_bookaccount_creditor'] > 0){
												$s_sql = "SELECT cmt.* FROM cs_mainbook_transaction cmt 
												JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id 
												WHERE cmv.case_id = ? AND cmt.bookaccount_id = ?";
												$o_query = $o_main->db->query($s_sql, array($caseData['id'],$claim['cs_bookaccount_creditor']));
												
												$transactions = ($o_query ? $o_query->result_array() : array());
												foreach($transactions as $transaction){
													$coveredSum += $transaction['amount'];
												}
												$coveredSum *= (-1);
												if(!$coveredTypeDisplayed[$claim['cs_bookaccount_creditor']] && $coveredSum > 0) { 
													echo "<span style='float: right;'>".$coveredSum."</span>";
													$coveredTypeDisplayed[$claim['cs_bookaccount_creditor']] = 1;
													$remaining_sum = $claim['amount'] - $coveredSum;
													?>
												<?php }
											} 
											if($claim['cs_bookaccount_id'] > 0) {
												$s_sql = "SELECT cmt.* FROM cs_mainbook_transaction cmt 
												JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id 
												WHERE cmv.case_id = ? AND cmt.bookaccount_id = ?";
												$o_query = $o_main->db->query($s_sql, array($caseData['id'],$claim['cs_bookaccount_id']));
												$transactions = ($o_query ? $o_query->result_array() : array());
												foreach($transactions as $transaction){
													$coveredSum += $transaction['amount'];
												}
												$coveredSum *= (-1);
												if(!$coveredTypeDisplayed[$claim['cs_bookaccount_id']] && $coveredSum > 0) { 
													echo "<span style='float: right;'>".$coveredSum."</span>";
													$coveredTypeDisplayed[$claim['cs_bookaccount_id']] = 1;
													$remaining_sum = $claim['amount'] - $coveredSum;
													?>
												<?php }
											}
											//echo number_format($claim['interest_percent'], 2, ",", " ");
											?>
										</td>
										<td width="7%" style="text-align: right;">
											<?php
											echo $remaining_sum;
											//echo number_format($claim['interest_percent'], 2, ",", " ");
											?>
										</td>
										<td width="10%" style="text-align: right;">
											<?php
											echo number_format($claim['amount'], 2, ",", " ");

											if(!$claim['payment_after_closed']) {
												$totalSumDue += $claim['amount'];
											}
											?>
										</td>
										<td width="6%" style="text-align: right;">
											<?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-claims editBtnIcon" data-case-id="<?php echo $cid; ?>" data-claim-id="<?php echo $claim['id'];?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?>
											<?php if($moduleAccesslevel > 110) {

										 	?>
											<button class="output-btn small output-delete-claims editBtnIcon" data-case-id="<?php echo $cid; ?>" data-claim-id="<?php echo $claim['id'];?>">
												<span class="glyphicon glyphicon-trash"></span>
											</button>
											<?php
											} ?>
										</td>
									</tr>
								<?php }
								?>

								<?php
								$totalSumDueAfterPayment = number_format($totalSumDue - $totalSumPaid, 2, ".", "");
								?>
								<tr class="spaceWrapper"><td colspan="5"></td></tr>
								<tr class="totalSum">
									<td width="60%" class="first">
										<?php echo $formText_TotalSumPaid_Output; ?><br/>
										<?php echo $formText_TotalSumDue_Output; ?><br/>
									</td>
									<td width="7%" style="text-align: right;">
									</td>
									<td width="7%" style="text-align: right;">
									</td>
									<td width="10%" style="text-align: right;">
										<?php echo number_format($totalSumPaid, 2, ",", " "); ?><br/>
										<?php echo number_format($totalSumDueAfterPayment, 2, ",", " "); ?><br/>
									</td>
									<td width="6%" style="text-align: right;"></td>
								</tr>

								<tr>
									<td colspan="5" style="text-align: right;">
										<?php /*?>
										<a href="<?php echo $_SERVER['PHP_SELF'].'/../../modules/'.$module.'/output/includes/generatePdf.php?caseId='.$cid;?>" class="generatePdf" target="_blank">
											<?php echo $formText_DownloadPdf_output; ?>
										</a> */ ?>
									</td>
								</tr>
							</table>

							<?php
							$s_sql = "SELECT * FROM collecting_cases_interest_calculation
							WHERE collecting_company_case_id = '".$o_main->db->escape_str($caseData['id'])."'
							ORDER BY created ASC";
			                $o_query = $o_main->db->query($s_sql);
			                $collecting_cases_interest_calculations = ($o_query ? $o_query->result_array() : array());
							if(count($collecting_cases_interest_calculations) > 0) {
								?>
								<div class="regenerate_interest" data-><?php echo $formText_GenerateInterest_output;?></div>
								<table class="claimsTable table table-borderless">
									<tr>
										<th width="70%"><?php echo $formText_Name_Output; ?></th>
										<th width="10%" style="text-align: right;">
										</th>
										<th width="10%" style="text-align: right;">
											<?php echo $formText_Amount_Output;?>
										</th>
									</tr>
									<?php foreach($collecting_cases_interest_calculations as $collecting_cases_interest_calculation) {
										$s_sql = "SELECT * FROM collecting_company_cases_claim_lines
										WHERE id = '".$o_main->db->escape_str($collecting_cases_interest_calculation['collecting_company_cases_claim_line_id'])."'";
						                $o_query = $o_main->db->query($s_sql);
						                $claimline = ($o_query ? $o_query->row_array() : array());
										 ?>
										<tr>
											<td width="70%"><?php echo $formText_Interest_Output; ?> <?php if($claimline) echo $claimline['name'];?> (<?php echo date("d.m.Y", strtotime($collecting_cases_interest_calculation['date_from']))." - ".date("d.m.Y", strtotime($collecting_cases_interest_calculation['date_to']))?>)</td>
											<td width="10%" style="text-align: right;">
												<?php echo number_format($collecting_cases_interest_calculation['rate'], 2, ",", " ");?>
											</td>
											<td width="10%" style="text-align: right;">
												<?php
												echo number_format($collecting_cases_interest_calculation['amount'], 2, ",", " ");
												?>
											</td>
										</tr>
									<?php } ?>
								</table>
								<?php
							}
							?>
							<span class="edit_claim_fees_setting fas fa-cog" ></span>
						</div>
					</div>
					<div class="p_contentBlockWrapper">
						<?php
						$connected_payment_transactions = array();
						foreach($link_ids as $link_id) {
							$s_sql = "SELECT * FROM creditor_transactions WHERE link_id=? AND creditor_id = ? AND system_type='Payment'";
							$o_query = $o_main->db->query($s_sql, array($link_id, $caseData['creditor_id']));
							$single_payment_transactions = ($o_query ? $o_query->result_array() : array());
							$connected_payment_transactions = array_merge($connected_payment_transactions,  $single_payment_transactions);
						}

						?>
						<div class="p_pageDetailsSubTitle dropdown_content_show white">
							<?php echo $formText_ConnectedPayments_Output;?>
							<div class="showArrow"><span class="glyphicon glyphicon-triangle-bottom"></span></div>
						</div>						
						<div class="p_contentBlock dropdown_content noTopPadding">
						<table class="claimsTable table table-borderless">
								<tr>
									<th width="50%"><?php echo $formText_Name_Output; ?></th>
									<th width="10%" style="text-align: right;">
										<?php echo $formText_LinkId_Output;?>
									</th>
									<th width="10%" style="text-align: right;">
										<?php echo $formText_Amount_Output;?>
									</th>
									<th width="10%" style="text-align: right;">
										<?php echo $formText_Date_output;?>
									</th>
									<th width="10%" style="text-align: right;">
										<?php echo $formText_ClaimlineId_output;?>
									</th>
								</tr>
								<?php
								foreach($connected_payment_transactions as $connected_transaction) {
									?>
									<tr>
										<td width="50%"><?php echo $formText_Payment_output." ".$connected_transaction['invoice_nr']; ?></td>
										<td width="10%" style="text-align: right;">
											<a href="#" class="show_connected_transactions_by_link_id" data-link-id="<?php echo $connected_transaction['link_id']?>"><?php
											echo $connected_transaction['link_id'];
											?></a>
										</td>
										<td width="10%" style="text-align: right;">
											<?php
											echo number_format($connected_transaction['amount'], 2, ",", " ");
											?>
										</td>
										<th width="10%" style="text-align: right;">
										<?php
											echo date("d.m.Y", strtotime($connected_transaction['date']));
											?> (<?php echo $formText_LastChangedDate_output." ".date("d.m.Y H:i:s", strtotime($connected_transaction['date_changed']));?>)
										
										</th>
										<td width="10%" style="text-align: right;">
											<?php 
											if($connected_transaction['company_claimline_id'] == 0){
												echo $formText_ClaimlineNotCreate_output." <span class='create_claimline' data-payment_transaction_id='".$connected_transaction['id']."'>".$formText_CreateClaimline_output."</span>";
											} else {
												echo $connected_transaction['company_claimline_id'];
												$s_sql = "SELECT * FROM collecting_company_cases_claim_lines WHERE id = ?";
												$o_query = $o_main->db->query($s_sql, array($connected_transaction['company_claimline_id']));
												$company_claimline = ($o_query ? $o_query->row_array() : array());
												if($company_claimline) {
													echo " ".$formText_Case_output." <a href='".$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCases&folderfile=output&folder=output&inc_obj=details&cid=".$company_claimline['collecting_company_case_id']."' target='_blank'>".$company_claimline['collecting_company_case_id'].'</a><br/>';
												}
											}
											/*if($moduleAccesslevel > 110) { ?>
											<button class="output-btn small output-delete-transaction-connection editBtnIcon" data-transaction-id="<?php echo $connected_transaction['id'];?>">
												<span class="glyphicon glyphicon-trash"></span>
											</button>
											<?php
										} */?>
										</td>
									</tr>
								<?php } ?>
							</table>
						</div>
					</div>
					<div class="p_contentBlockWrapper">
						<div class="p_pageDetailsSubTitle dropdown_content_show white">
							<?php echo $formText_ClaimlinesNotIncludedInClaim_Output;?>
							<div class="showArrow"><span class="glyphicon glyphicon-triangle-bottom"></span></div>
						</div>
						<div class="p_contentBlock dropdown_content noTopPadding">
							<?php
							$s_sql = "SELECT cccl.*, bconfig.type_name as claim_line_type_name FROM collecting_company_cases_claim_lines cccl
							LEFT OUTER JOIN collecting_cases_claim_line_type_basisconfig bconfig ON bconfig.id = cccl.claim_type
							WHERE cccl.content_status < 2 AND cccl.collecting_company_case_id = ? AND IFNULL(bconfig.not_include_in_claim, 0) = 1
							ORDER BY cccl.claim_type ASC, cccl.created DESC";
							$o_query = $o_main->db->query($s_sql, array($caseData['id']));
							$claims = ($o_query ? $o_query->result_array() : array());
							?>
							<table class="claimsTable table table-borderless">
								<tr>
									<th width="70%"><?php echo $formText_Name_Output; ?></th>
									<th width="10%" style="text-align: right;">
									</th>
									<th width="10%" style="text-align: right;">
										<?php echo $formText_Amount_Output;?>
									</th>
									<th width="10%" style="text-align: right;"></th>
								</tr>
								<?php

								foreach($claims as $claim) {
									?>
									<tr>
										<td width="70%">
											<?php echo $claim['claim_line_type_name'];?>
											<?php if($claim['original_due_date'] != "0000-00-00" && $claim['original_due_date'] != ""){ echo date("d.m.Y", strtotime($claim['original_due_date'])); }?>

										</td>
										<td width="10%" style="text-align: right;">
											<?php
											echo number_format($claim['interest_percent'], 2, ",", " ");
											?>
										</td>
										<td width="10%" style="text-align: right;">
											<?php
											echo number_format($claim['amount'], 2, ",", " ");

											if(!$claim['payment_after_closed']) {
												$totalSumDue += $claim['amount'];
											}
											?>
										</td>
										<td width="10%" style="text-align: right;">
											<?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-claims editBtnIcon" data-case-id="<?php echo $cid; ?>" data-claim-id="<?php echo $claim['id'];?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?>
											<?php if($moduleAccesslevel > 110) {

										 	?>
											<button class="output-btn small output-delete-claims editBtnIcon" data-case-id="<?php echo $cid; ?>" data-claim-id="<?php echo $claim['id'];?>">
												<span class="glyphicon glyphicon-trash"></span>
											</button>
											<?php
											} ?>
										</td>
									</tr>
								<?php }
								?>

							</table>
						</div>
					</div>
					<div class="p_contentBlockWrapper">
						<?php
						$s_sql = "SELECT * FROM creditor_transactions WHERE collecting_company_case_id = '".$o_main->db->escape_str($caseData['id'])."' AND creditor_id = '".$o_main->db->escape_str($caseData['creditor_id'])."' ORDER BY created DESC";
						$o_query = $o_main->db->query($s_sql);
						$connected_transactions = ($o_query ? $o_query->result_array() : array());
						?>
						<div class="p_pageDetailsSubTitle dropdown_content_show white">
							<?php echo $formText_ConnectedTransactions_Output;?>
							<?php if(count($connected_transactions) > 0) { ?>
								<span class="send_invoices"><?php echo $formText_SendInvoices_output;?></span>
							<?php } ?>
							<div class="showArrow"><span class="glyphicon glyphicon-triangle-bottom"></span></div>
						</div>
						<div class="p_contentBlock dropdown_content noTopPadding">
							<table class="claimsTable table table-borderless">
								<tr>
									<th width="50%"><?php echo $formText_Name_Output; ?></th>
									<th width="10%" style="text-align: right;">
										<?php echo $formText_LinkId_Output;?>
									</th>
									<th width="10%" style="text-align: right;">
										<?php echo $formText_Amount_Output;?>
									</th>
									<th width="10%" style="text-align: right;">
										<?php echo $formText_Date_Output;?>
									</th>
									<th width="10%" style="text-align: right;">
									</th>
								</tr>
								<?php
								foreach($connected_transactions as $connected_transaction) {
									?>
									<tr>
										<td width="50%"><?php echo $formText_Invoice_output." <span class='download_invoice' data-id='".$connected_transaction['id']."'>".$connected_transaction['invoice_nr']."</span>"; ?></td>
										<td width="10%" style="text-align: right;">
											<a href="#" class="show_connected_transactions_by_link_id" data-link-id="<?php echo $connected_transaction['link_id']?>"><?php
											echo $connected_transaction['link_id'];
											?></a>
										</td>
										<td width="10%" style="text-align: right;">
											<?php
											echo number_format($connected_transaction['amount'], 2, ",", " ");
											?>
										</td>
										<td width="10%" style="text-align: right;">
											<?php
											echo date("d.m.Y", strtotime($connected_transaction['date']));
											?> (<?php echo $formText_LastChangedDate_output." ".date("d.m.Y H:i:s", strtotime($connected_transaction['date_changed']));?>)
										</td>
										<td width="10%" style="text-align: right;">
											<?php /*if($moduleAccesslevel > 110) { ?>
											<button class="output-btn small output-delete-transaction-connection editBtnIcon" data-transaction-id="<?php echo $connected_transaction['id'];?>">
												<span class="glyphicon glyphicon-trash"></span>
											</button>
											<?php
										} */?>
										</td>
									</tr>
								<?php } ?>
							</table>
							<div class="show_all_transactions"><?php echo $formText_ShowTransactionsFrom24SevenOffice_output;?></div>
							<div class="transactions_holder">

							</div>
						</div>
					</div>
					<?php
					$connected_case_ids = array();
					foreach($connected_transactions as $connected_transaction){
						if($connected_transaction['collectingcase_id'] > 0) {
							if(!in_array($connected_transaction['collectingcase_id'], $connected_case_ids)) {
								$connected_case_ids[] = $connected_transaction['collectingcase_id'];
							}
						}
					}
					if(count($connected_case_ids) > 0) {
					?>
						<div class="p_contentBlockWrapper">

							<div class="p_pageDetailsSubTitle white dropdown_content_show ">
								<?php echo $formText_ConnectedReminderCases_Output;?>
								<div class="showArrow"><span class="glyphicon glyphicon-triangle-bottom"></span></div>
							</div>
							<?php
							$s_sql = "SELECT * FROM collecting_cases WHERE id IN (".implode(',', $connected_case_ids).") ORDER BY created DESC";
							$o_query = $o_main->db->query($s_sql);
							$connected_cases = ($o_query ? $o_query->result_array() : array());
							?>
							<div class="p_contentBlock dropdown_content noTopPadding">
								<?php
								foreach($connected_cases as $connected_case) {
									$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCases&folderfile=output&folder=output&inc_obj=details&cid=".$connected_case['id'];

									$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE case_id = ? ORDER BY created DESC";
									$o_query = $o_main->db->query($s_sql, array($connected_case['id']));
									$last_letter = ($o_query ? $o_query->row_array() : array());
									?>
									<div>
										<a href="<?php echo $s_list_link?>" target="_blank"><?php echo $connected_case['id']; ?></a>
										<?php echo $formText_LastLetter_output;?>: <?php echo date("d.m.Y", strtotime($last_letter['created']));?> <?php echo $formText_DueDate_output." ". date("d.m.Y", strtotime($last_letter['due_date']));?>
										<?php
										if($last_letter['pdf'] != "") {

											$fileParts = explode('/',$last_letter['pdf']);
											$fileName = array_pop($fileParts);
											$fileParts[] = rawurlencode($fileName);
											$filePath = implode('/',$fileParts);
											$fileUrl = $extradomaindirroot.$last_letter['pdf'];
											$fileName = basename($last_letter['pdf']);
											if(strpos($last_letter['pdf'],'uploads/protected/')!==false)
											{
												$fileUrl = $extradomaindirroot."/../".$filePath.'?caID='.$_GET['caID'].'&table=collecting_cases_claim_letter&field=pdf&ID='.$last_letter['id'];
											}
											?>
												<a href="<?php echo $fileUrl;?>" download target="_blank"><?php echo $formText_Download_Output;?></a>
										<?php } ?>
									</div>
									<?php
								}
								?>
							</div>
						</div>
					<?php } ?>
					<div class="p_contentBlockWrapper">

						<div class="p_pageDetailsSubTitle white dropdown_content_show ">
							<?php echo $formText_NotesAndFiles_Output;?><?php if($moduleAccesslevel > 10) { ?><button class="addEntryBtn small output-edit-comment" data-case-id="<?php echo $cid; ?>"><?php echo $formText_Add_output;?></button><?php } ?>
							<div class="showArrow"><span class="glyphicon glyphicon-triangle-bottom"></span></div>
						</div>
						<div class="p_contentBlock dropdown_content noTopPadding">
							<?php

							$s_sql = "SELECT * FROM collecting_cases_notesandfiles WHERE content_status < 2 AND collecting_company_case_id = ? ORDER BY created DESC";
							$o_query = $o_main->db->query($s_sql, array($caseData['id']));
							$comments = ($o_query ? $o_query->result_array() : array());
							foreach($comments as $comment) {
								?>
								<div class="commentBlock handle_ui">
									<table class="table table-borderless">
										<tr>
											<td width="90%" class="createdLabel"><?php echo date("d.m.Y H:i", strtotime($comment['created']));?> | <?php echo $comment['createdBy'];?></td>
											<td width="10%" style="text-align: right;">
												<?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-comment editBtnIcon" data-case-id="<?php echo $cid; ?>" data-comment-id="<?php echo $comment['id'];?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?>
												<?php if($moduleAccesslevel > 110) { ?>
						    					<button class="output-btn small output-delete-comment editBtnIcon" data-case-id="<?php echo $cid; ?>" data-comment-id="<?php echo $comment['id'];?>">
							    					<span class="glyphicon glyphicon-trash"></span>
						    					<?php } ?>
											</td>
										</tr>
										<tr>
											<td colspan="2">
												<?php echo nl2br($comment['text']);?>
												<?php
												$files = json_decode($comment['files']);
												foreach($files as $file) {
													$fileParts = explode('/',$file[1][0]);
													$fileName = array_pop($fileParts);
													$fileParts[] = rawurlencode($fileName);
													$filePath = implode('/',$fileParts);
													$fileUrl = $extradomaindirroot."/../".$file[1][0];
													$fileName = $file[0];
													if(strpos($file[1][0],'uploads/protected/')!==false)
													{
														$fileUrl = $extradomaindirroot."/../".$filePath.'?caID='.$_GET['caID'].'&table=collecting_cases_notesandfiles&field=files&ID='.$comment['id'];
													}
												?>
													<div class="project-file">
														<div class="project-file-file">
															<a href="<?php echo $fileUrl;?>" download><?php echo $fileName;?></a>
														</div>
													</div>
													<?php
												}
												?>
											</td>
										</tr>
									</table>
									<div class="expand_collapse"><span class="glyphicon glyphicon-chevron-down"></span><span class="glyphicon glyphicon-chevron-up"></span></div>
								</div>
								<?php
							}
							?>
						</div>
					</div>


					<div class="p_contentBlockWrapper">

						<div class="p_pageDetailsSubTitle white dropdown_content_show ">
							<?php echo $formText_ClaimAction_Output;?>
							<div class="showArrow"><span class="glyphicon glyphicon-triangle-bottom"></span></div>
						</div>
						<div class="p_contentBlock dropdown_content noTopPadding">

							<table class="table table-borderless">
								<tr>
									<th><?php echo $formText_Date_Output; ?></th>
									<th><?php echo $formText_ActionType_Output; ?></th>
									<th><?php echo $formText_StepName_Output; ?></th>
									<th><?php echo $formText_TotalAmount_Output; ?></th>
									<th><?php echo $formText_DueDate_Output; ?></th>
									<th><?php echo $formText_Pdf_Output; ?></th>
									<th></th>
								</tr>
								<?php


								$s_sql = "SELECT collecting_cases_claim_letter.* FROM collecting_cases_claim_letter
								WHERE collecting_cases_claim_letter.content_status < 2 AND collecting_cases_claim_letter.collecting_company_case_id = ?
								ORDER BY collecting_cases_claim_letter.created DESC";
								$o_query = $o_main->db->query($s_sql, array($caseData['id']));
								$v_claim_letters = ($o_query ? $o_query->result_array() : array());
								foreach($v_claim_letters as $v_claim_letter) {
									?>
									<tr>
										<td><?php echo date("d.m.Y", strtotime($v_claim_letter['created'])); ?></td>
										<td><?php
										if($v_claim_letter['sending_status'] > 0){
											if($v_claim_letter['performed_action'] == 0){
												echo $formText_SendLetter_output;
											} else if($v_claim_letter['performed_action'] == 1){
												echo $formText_SendEmail_output;
											} else if($v_claim_letter['performed_action'] == 5){
												echo $formText_SendEhf_output;
											}
										} else {
											switch(intval($v_claim_letter['sending_action'])) {
												case 0:
													echo $formText_None_output;
												break;
												case 1:
													echo $formText_SendLetter_output;
												break;
												case 2:
													echo $formText_SendEmailIfEmailExistsOrElseLetter_output;
												break;
											}
										}
										?></td>
										<td><?php echo $v_claim_letter['step_name'];?> <?php if($v_claim_letter['rest_note']) echo $formText_RestClaim_output;?></td>
										<td><?php if($v_claim_letter['total_amount'] != "") echo number_format($v_claim_letter['total_amount'], 2, ",", " ")?></td>
										<td><?php if($v_claim_letter['due_date'] != "0000-00-00" && $v_claim_letter['due_date'] != "") echo date("d.m.Y", strtotime($v_claim_letter['due_date'])); ?></td>
										<td>
											<?php if($v_claim_letter['pdf'] != "") {

												$fileParts = explode('/',$v_claim_letter['pdf']);
												$fileName = array_pop($fileParts);
												$fileParts[] = rawurlencode($fileName);
												$filePath = implode('/',$fileParts);
												$fileUrl = $extradomaindirroot.$v_claim_letter['pdf'];
												$fileName = basename($v_claim_letter['pdf']);
												if(strpos($v_claim_letter['pdf'],'uploads/protected/')!==false)
												{
													$fileUrl = $extradomaindirroot."/../".$filePath.'?caID='.$_GET['caID'].'&table=collecting_cases_claim_letter&field=pdf&ID='.$v_claim_letter['id'];
												}
												?>
												<div class="project-file">
													<div class="project-file-file">
														<a href="<?php echo $fileUrl;?>" download target="_blank"><?php echo $formText_Download_Output;?></a>
													</div>
												</div>
											<?php } ?>

										</td>
										<td><span class="delete_letter glyphicon glyphicon-trash" data-letter-id="<?php echo $v_claim_letter['id']?>"></span></td>
									</tr>
									<?php
								}
								?>
							</table>
							<?php
							$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE content_status = 2 AND collecting_company_case_id = ? ORDER BY created DESC";
							$o_query = $o_main->db->query($s_sql, array($caseData['id']));
							$deleted_claim_letters = ($o_query ? $o_query->result_array() : array());
							if(count($deleted_claim_letters) > 0) {
								?>
								<div class="showDeletedClaimLetters"><?php echo $formText_ShowDeletedClaimLetters_output; echo " ".count($deleted_claim_letters)?> <span class="glyphicon glyphicon-menu-down"></span><span class="glyphicon glyphicon-menu-up"></span></div>
								<div class="deletedClaimLetters">
									<table class="table table-borderless">
										<tr>
											<th><?php echo $formText_Date_Output; ?></th>
											<th><?php echo $formText_ActionType_Output; ?></th>
											<th><?php echo $formText_PerformedDate_Output; ?></th>
											<th><?php echo $formText_TotalAmount_Output; ?></th>
											<th><?php echo $formText_DueDate_Output; ?></th>
											<th><?php echo $formText_Pdf_Output; ?></th>
											<th><?php echo $formText_DeletionComment_Output; ?></th>
										</tr>
										<?php
										foreach($deleted_claim_letters as $v_claim_letter) {
											?>
											<tr>
												<td><?php echo date("d.m.Y", strtotime($v_claim_letter['created'])); ?></td>
												<td><?php
												if($v_claim_letter['sending_status'] > 0){
													if($v_claim_letter['performed_action'] == 0){
														echo $formText_SendLetter_output;
													} if($v_claim_letter['performed_action'] == 1){
														echo $formText_SendEmail_output;
													}
												} else {
													switch(intval($v_claim_letter['sending_action'])) {
														case 0:
															echo $formText_None_output;
														break;
														case 1:
															echo $formText_SendLetter_output;
														break;
														case 2:
															echo $formText_SendEmailIfEmailExistsOrElseLetter_output;
														break;
													}
												}
												?></td>
												<td><?php
												if($v_claim_letter['sending_status'] == 1){
													if($v_claim_letter['performed_date'] != "0000-00-00" && $v_claim_letter['performed_date'] != "") echo date("d.m.Y", strtotime($v_claim_letter['performed_date']));

												} else if($v_claim_letter['sending_status'] == -1) {
													echo $formText_CurrentlySending_output;
												} else if($v_claim_letter['sending_status'] == 2){
													echo $formText_SendingFailed_output;
												}
												?></td>
												<td><?php if($v_claim_letter['total_amount'] != "") echo number_format($v_claim_letter['total_amount'], 2, ",", " ")?></td>
												<td><?php if($v_claim_letter['due_date'] != "0000-00-00" && $v_claim_letter['due_date'] != "") echo date("d.m.Y", strtotime($v_claim_letter['due_date'])); ?></td>
												<td>
													<?php if($v_claim_letter['pdf'] != "") {

														$fileParts = explode('/',$v_claim_letter['pdf']);
														$fileName = array_pop($fileParts);
														$fileParts[] = rawurlencode($fileName);
														$filePath = implode('/',$fileParts);
														$fileUrl = $extradomaindirroot.$v_claim_letter['pdf'];
														$fileName = basename($v_claim_letter['pdf']);
														if(strpos($v_claim_letter['pdf'],'uploads/protected/')!==false)
														{
															$fileUrl = $extradomaindirroot."/../".$filePath.'?caID='.$_GET['caID'].'&table=collecting_cases_claim_letter&field=pdf&ID='.$v_claim_letter['id'];
														}
														?>
														<div class="project-file">
															<div class="project-file-file">
																<a href="<?php echo $fileUrl;?>" download target="_blank"><?php echo $formText_Download_Output;?></a>
															</div>
														</div>
													<?php } ?>

												</td>
												<td><?php echo nl2br($v_claim_letter['delete_comment']);?></td>
											</tr>
											<?php
										}
										?>
									</table>
								</div>
								<?php
							}
							?>
						</div>
					</div>
					<div class="p_contentBlockWrapper">
						<div class="p_pageDetailsSubTitle white dropdown_content_show ">
							<?php echo $formText_Worklists_Output;?><?php if($moduleAccesslevel > 10) { ?><button class="addEntryBtn small output-edit-worklist" data-case-id="<?php echo $cid; ?>"><?php echo $formText_Add_output;?></button><?php } ?>
							<div class="showArrow"><span class="glyphicon glyphicon-triangle-bottom"></span></div>
						</div>
						<div class="p_contentBlock dropdown_content noTopPadding">
							<table class="table">
								<tr>
									<th><?php echo $formText_Worklist_output;?></th>
									<th><?php echo $formText_Comment_output;?></th>
									<th><?php echo $formText_AddedToWorklistDate_Output;?></th>
									<th><?php echo $formText_ReminderDate_Output;?></th>
									<th><?php echo $formText_ClosedDate_Output;?></th>
									<th></th>
								</tr>
								<?php
								$worklist_status_array = array($formText_Active_output, $formText_Finished_output);
								$s_sql = "SELECT cwc.*, cw.name as worklistName FROM case_worklist_connection cwc LEFT JOIN case_worklist cw ON cw.id = cwc.case_worklist_id WHERE cwc.content_status < 2 AND cwc.collecting_company_case_id = ?";
								$o_query = $o_main->db->query($s_sql, array($cid));
								$worklist_connections = ($o_query ? $o_query->result_array() : array());
								foreach($worklist_connections as $worklist_connection) {
									?>
									<tr>
										<td><?php echo $worklist_connection['worklistName'];?></td>
										<td><?php echo nl2br($worklist_connection['comment']);?></td>
										<td><?php if($worklist_connection['added_to_worklist_date'] != "" && $worklist_connection['added_to_worklist_date'] != "0000-00-00 00:00:00") echo date("d.m.Y", strtotime($worklist_connection['added_to_worklist_date'])); ?></td>
										<td><?php if($worklist_connection['reminder_date'] != "" && $worklist_connection['reminder_date'] != "0000-00-00 00:00:00") echo date("d.m.Y", strtotime($worklist_connection['reminder_date']));?></td>
										<td><?php if($worklist_connection['closed_date'] != "" && $worklist_connection['closed_date'] != "0000-00-00 00:00:00") echo date("d.m.Y", strtotime($worklist_connection['closed_date']));?></td>
										<td>
											<button class="output-btn small output-edit-worklist editBtnIcon" data-worklist-id="<?php echo $worklist_connection['id'];?>" data-case-id="<?php echo $cid; ?>"><span class="glyphicon glyphicon-pencil"></span></button>
											<button class="output-btn small output-delete-worklist editBtnIcon" data-worklist-id="<?php echo $worklist_connection['id'];?>" data-case-id="<?php echo $cid; ?>"><span class="glyphicon glyphicon-trash"></span></button>
										</td>
									</tr>
									<?php
								}
								?>
							</table>
						</div>
					</div>

					<div class="p_contentBlockWrapper">
						<div class="p_pageDetailsSubTitle white dropdown_content_show ">
							<?php echo $formText_Paused_Output;?><?php if($moduleAccesslevel > 10) { ?><button class="addEntryBtn small output-edit-objection" data-case-id="<?php echo $cid; ?>"><?php echo $formText_Add_output;?></button><?php } ?>
							<div class="showArrow"><span class="glyphicon glyphicon-triangle-bottom"></span></div>
						</div>
						<div class="p_contentBlock dropdown_content noTopPadding">
							<table class="table">
								<tr>
									<th><?php echo $formText_Type_output;?></th>
									<th><?php echo $formText_Message_output;?></th>
									<th><?php echo $formText_ClosedDate_output;?></th>
									<th><?php echo $formText_ClosedMessage_output;?></th>
									<th></th>
								</tr>
								<?php
								
								$type_messages = array(
									$formText_ReturnedLetters_Output, 
									$formText_PausedByCollectingCompany_output, 
									$formText_PausedByCreditor_output, 
									$formText_StoppedWithMessageFromDebitor_output,
									$formText_WantsInvoiceCopy_output,
									$formText_WantsInstallmentPayment_output,
									$formText_WantsDefermentOfPayment_output, 
									$formText_HasAnObjection_output,
									$formText_StoppedWithOtherReason_output);
								
								foreach($objections as $objection) {
									?>
									<tr>
										<td><?php echo $type_messages[$objection['pause_reason']];?></td>
										<td><?php echo nl2br($objection['pause_reason_comment']);?></td>
										<td><?php
										if($objection['closed_date'] != "0000-00-00 00:00:00" && $objection['closed_date'] != ""){
											echo date("d.m.Y", strtotime($objection['closed_date']));
										}
										?></td>
										<td><?php
										if($objection['closed_date'] != "0000-00-00 00:00:00" && $objection['closed_date'] != ""){
											echo nl2br($objection['closed_comment']);
										}
										?></td>
										<td>
											<button class="output-btn small output-edit-objection editBtnIcon" data-objection-id="<?php echo $objection['id'];?>" data-case-id="<?php echo $cid; ?>"><span class="glyphicon glyphicon-pencil"></span></button>
											<button class="output-btn small output-delete-objection editBtnIcon" data-objection-id="<?php echo $objection['id'];?>" data-case-id="<?php echo $cid; ?>"><span class="glyphicon glyphicon-trash"></span></button>
											<button class="output-btn small output-close-objection editBtnIcon" data-objection-id="<?php echo $objection['id'];?>" data-case-id="<?php echo $cid; ?>"><?php echo $formText_Close_output;?></button>

										</td>
									</tr>
									<?php
								}
								?>
							</table>
						</div>
					</div>
					<?php /*?>
					<div class="p_contentBlockWrapper">
						<div class="p_pageDetailsSubTitle white dropdown_content_show ">
							<?php echo $formText_ReturnedLetters_Output;?><?php if($moduleAccesslevel > 10) { ?><button class="addEntryBtn small output-edit-returned" data-case-id="<?php echo $cid; ?>"><?php echo $formText_Add_output;?></button><?php } ?>
							<div class="showArrow"><span class="glyphicon glyphicon-triangle-bottom"></span></div>
						</div>
						<div class="p_contentBlock dropdown_content noTopPadding">
							<table class="table">
								<tr>
									<th><?php echo $formText_Date_output;?></th>
									<th><?php echo $formText_Comment_output;?></th>
									<th><?php echo $formText_Closed_output;?></th>
									<th></th>
								</tr>
								<?php
								foreach($returned_letters as $returned_letter) {
									?>
									<tr>
										<td><?php echo date("d.m.Y", strtotime($returned_letter['created_date']));?></td>
										<td><?php echo $returned_letter['pause_reason_comment'];?></td>
										<td><?php
											if($returned_letter['closed_date'] != "" && $returned_letter['closed_date'] != "0000-00-00 00:00:00"){
											 echo date("d.m.Y", strtotime($returned_letter['closed_date']));
											 echo "<br/>".nl2br($returned_letter['closed_comment']);
										 	}
											 ?></td>
										<td>
											<button class="output-btn small output-edit-returned editBtnIcon" data-returned-id="<?php echo $returned_letter['id'];?>" data-case-id="<?php echo $cid; ?>"><span class="glyphicon glyphicon-pencil"></span></button>
											<button class="output-btn small output-delete-returned editBtnIcon" data-returned-id="<?php echo $returned_letter['id'];?>" data-case-id="<?php echo $cid; ?>"><span class="glyphicon glyphicon-trash"></span></button>
										</td>
									</tr>
									<?php
								}
								?>
							</table>
						</div>
					</div>
					*/?>
					<div class="p_contentBlockWrapper">
						<div class="p_pageDetailsSubTitle white dropdown_content_show ">
							<?php echo $formText_PaymentPlan_Output;?><?php if($moduleAccesslevel > 10) { ?><button class="addEntryBtn small output-edit-payment-plan" data-case-id="<?php echo $cid; ?>"><?php echo $formText_Add_output;?></button><?php } ?>
							<div class="showArrow"><span class="glyphicon glyphicon-triangle-bottom"></span></div>
						</div>
						<div class="p_contentBlock dropdown_content noTopPadding">
							<?php

							$s_sql = "SELECT * FROM collecting_cases_payment_plan WHERE  collecting_case_id = ? ORDER BY created DESC";
							$o_query = $o_main->db->query($s_sql, array($caseData['id']));
							$paymentPlans = ($o_query ? $o_query->result_array() : array());


							foreach($paymentPlans as $paymentPlan) {

								?>
								<table class="paymentPlanTable table table-borderless">
									<tr>
										<td><?php echo $formText_Status_Output;?></td>
										<td><?php
										 if(intval($paymentPlan['status']) == 0) {
											echo $formText_Active_output;
										} else if($paymentPlan['status'] == 1) {
											echo $formText_Completed_output;
										} else if($paymentPlan['status'] == 2) {
											echo $formText_Interrupted_output;
										}?></td>
									</tr>
									<tr>
										<td><?php echo $formText_TotalSumDue_Output;?></td>
										<td><?php echo number_format($totalSumDueAfterPayment, 2, ",", " ");?></td>
									</tr>
									<tr>
										<td><?php echo $formText_FirstPaymentDate_Output;?></td>
										<td><?php echo date("d.m.Y", strtotime($paymentPlan['first_payment_date']));?></td>
									</tr>
									<tr>
										<td><?php echo $formText_NextPaymentDate_Output;?></td>
										<td><?php if($paymentPlan['next_payment_date'] != "0000-00-00" && $paymentPlan['next_payment_date'] != "") echo date("d.m.Y", strtotime($paymentPlan['next_payment_date']));?></td>
									</tr>
									<tr>
										<td><?php echo $formText_MonthlyPayment_Output;?></td>
										<td><?php echo $paymentPlan['monthly_payment'];?></td>
									</tr>
								</table>
								<table class="paymentPlanTable btn-edit-table" width="100%" border="0" cellpadding="0" cellspacing="0">
						            <tr>
						                <td class="txt-label"></td>
						                <td class="txt-value"></td>
						                <td class="btn-edit" colspan="2"><?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-payment-plan editBtnIcon" data-case-id="<?php echo $cid; ?>" data-paymentplan-id="<?php echo $paymentPlan['id'];?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?></td>
						            </tr>
						        </table>
								<table class="paymentPlanTableBig table table-borderless">
									<tr>
										<th><?php echo $formText_StartDate_Output; ?></th>
										<th><?php echo $formText_DueDate_Output; ?></th>
										<th><?php echo $formText_AmountToPay_Output; ?></th>
										<th><?php echo $formText_Payed_Output; ?></th>
										<th><?php echo $formText_Pdf_Output; ?></th>
									</tr>
									<?php
									$monthlyPayment = $paymentPlan['monthly_payment'];

									$nextPaymentDate = date("d.m.Y", strtotime($paymentPlan['first_payment_date']));

									$s_sql = "SELECT * FROM collecting_cases_payment_plan_lines WHERE collecting_cases_payment_plan_id = '".$o_main->db->escape_str($paymentPlan['id'])."'";
								    $o_query = $o_main->db->query($s_sql);
									$collecting_cases_payment_plan_lines = $o_query ? $o_query->result_array() : array();

									foreach($collecting_cases_payment_plan_lines as $collecting_cases_payment_plan_line) {
										?>
										<tr>
											<td><?php echo date("d.m.Y", strtotime($collecting_cases_payment_plan_line['created']));?></td>
											<td><?php echo date("d.m.Y", strtotime($collecting_cases_payment_plan_line['due_date']));?></td>
											<td><?php echo number_format($collecting_cases_payment_plan_line['amount_to_pay'], 2, ",", " ");?></td>
											<td><?php echo number_format($collecting_cases_payment_plan_line['payed'], 2, ",", " ") ?></td>
											<td>
												<?php if($collecting_cases_payment_plan_line['pdf'] != "") { ?>
													<div class="project-file">
														<div class="project-file-file">
															<a href="<?php echo $extradomaindirroot.$collecting_cases_payment_plan_line['pdf'];?>" download><?php echo $formText_Download_Output;?></a>
														</div>
													</div>
												<?php } ?>
											</td>

										</tr>
										<?php
									}
									?>
								</table>
								<?php
							}
							?>

							<div class="clear"></div>
						</div>
					</div>
					<?php /*?><div class="p_contentBlockWrapper">
						<div class="p_pageDetailsSubTitle white dropdown_content_show ">
							<?php echo $formText_PaymentsReceived_Output;?>
							<div class="showArrow"><span class="glyphicon glyphicon-triangle-bottom"></span></div>
						</div>
						<div class="p_contentBlock dropdown_content noTopPadding">
							<?php
							$s_sql = "SELECT * FROM collecting_cases_payments WHERE collecting_case_id = ? ORDER BY created ASC";
							$o_query = $o_main->db->query($s_sql, array($caseData['id']));
							$payments = ($o_query ? $o_query->result_array() : array());

							?>
							<table class="table table-borderless">
								<tr>
									<th><?php echo $formText_Date_Output; ?></th>
									<th><?php echo $formText_Amount_Output; ?></th>
									<th class="rightAlign"><?php echo $formText_InterestBearingAmountLeft_Output; ?></th>
									<th class="rightAlign"><?php echo $formText_TotalAmountLeft_Output; ?></th>
								</tr>
								<?php
								$interestBearingAmount = $invoice['collecting_case_original_claim'];

								$s_sql = "SELECT * FROM creditor_transactions  WHERE  open = 1 AND system_type='Payment' AND link_id = ? AND creditor_id = ?";
								$o_query = $o_main->db->query($s_sql, array($invoice['link_id'], $invoice['creditor_id']));
								$invoice_payments = ($o_query ? $o_query->result_array() : array());
								foreach($invoice_payments as $invoice_payment) {
									$interestBearingAmount += $invoice_payment['amount'];
								}
								$totalUnpaid = $interestBearingAmount;
								foreach($claims as $claim) {
									$totalUnpaid += $claim['amount'];
								}
								$dateFrom = date("Y-m-d", strtotime($invoice['due_date']));
								$lastPayment = array();
								foreach($payments as $payment) {
							        $s_sql = "SELECT * FROM collecting_cases_payment_coverlines WHERE collecting_cases_payment_id = ?";
							        $o_query = $o_main->db->query($s_sql, array($payment['id']));
							        $paymentCoverlines = $o_query ? $o_query->result_array() : array();
									foreach($paymentCoverlines as $paymentCoverline){
					        			if($paymentCoverline['collecting_claim_line_type'] == 1) {
					        				$interestBearingAmount -= $paymentCoverline['amount'];
					        			}
										$totalUnpaid -= $paymentCoverline['amount'];
					        		}
									if($lastPayment) {
										$dateFrom = date("Y-m-d", strtotime($lastPayment['date']));
									}
									?>
									<tr>
										<td><?php echo date("d.m.Y", strtotime($payment['date'])); ?></td>
										<td>
											<?php echo number_format($payment['amount'], 2,",", " "); ?>
											<table class="table">
							                    <tr>
							                        <th><?php echo $formText_Type_output;?></th>
							                        <th><?php echo $formText_CollectingCompanyShare_output;?></th>
							                        <th><?php echo $formText_CreditorShare_output;?></th>
							                        <th><?php echo $formText_DebitorShare_output;?></th>
							                        <th><?php echo $formText_Total_output;?></th>
							                    </tr>
							                    <?php
							                    $debitor_share = 0;
							                    foreach( $paymentCoverlines as $paymentCoverline) {
							                        $collectioncompany_share = $paymentCoverline['collectingcompany_amount'];
							                        $creditor_share = $paymentCoverline['creditor_amount'];
							                        $total_amount = $paymentCoverline['amount'];
							                        $debitor_share += $paymentCoverline['debitor_amount'];

							                        $s_sql = "SELECT * FROM collecting_cases_claim_line_type_basisconfig WHERE id = ?";
							                        $o_query = $o_main->db->query($s_sql, array($paymentCoverline['collecting_claim_line_type']));
							                        $claim_line_type = $o_query ? $o_query->row_array() : array();

							                         ?>
							                         <tr>
							                             <td><?php echo $claim_line_type['type_name'];?></td>
							                             <td><?php echo number_format($collectioncompany_share, 2, ",", " "); ?></td>
							                             <td><?php echo number_format($creditor_share, 2, ",", " "); ?></td>
							                             <td><?php echo number_format(0, 2, ",", " "); ?></td>
							                             <td><?php echo number_format($total_amount, 2, ",", " "); ?></td>
							                         </tr>
							                    <?php }
							                    if($debitor_share > 0){
							                        ?>
							                        <tr>
							                            <td><?php echo $formText_CreditorPayedTooMuch;?></td>
							                            <td><?php echo number_format(0, 2, ",", " "); ?></td>
							                            <td><?php echo number_format(0, 2, ",", " "); ?></td>
							                            <td><?php echo number_format($debitor_share, 2, ",", " "); ?></td>
							                            <td><?php echo number_format($debitor_share, 2, ",", " "); ?></td>
							                        </tr>
							                        <?php
							                    }
							                    ?>
							                </table>
											<?php if(intval($payment['settlement_id']) == 0) { ?>
												<span class="glyphicon glyphicon-pencil output-edit-coverlines" data-paymentid="<?php echo $payment['id'];?>"></span>
											<?php } ?>
										</td>
										<td class="rightAlign">
											<?php echo number_format($interestBearingAmount, 2,",", " "); ?>
										</td>
										<td class="rightAlign">
											<?php echo number_format($totalUnpaid, 2,",", " "); ?>
										</td>
									</tr>
									<?php
								}
								?>
								<tr>
									<td></td>
									<td></td>
									<td class="rightAlign"><?php echo $formText_Unpaid_output;?>: <?php echo number_format($interestBearingAmount, 2, ",", ""); ?></td>
									<td class="rightAlign"><?php echo $formText_Unpaid_output;?>: <?php echo number_format($totalUnpaid, 2, ",", ""); ?></td>
								</tr>
							</table>
							<div class="clear"></div>
						</div>
					</div><?php */?>
					<div class="p_contentBlockWrapper">
						<div class="p_pageDetailsSubTitle white dropdown_content_show ">
							<?php echo $formText_Vouchers_Output;?>
							<div class="showArrow"><span class="glyphicon glyphicon-triangle-bottom"></span></div>
						</div>
						<div class="p_contentBlock dropdown_content noTopPadding">
							<?php
							$s_sql = "SELECT * FROM cs_mainbook_voucher WHERE case_id = ? ORDER BY created ASC";
							$o_query = $o_main->db->query($s_sql, array($caseData['id']));
							$vouchers = ($o_query ? $o_query->result_array() : array());

							?>
							<table class="table table-borderless">
								<?php foreach($vouchers as $voucher) {
									$s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = '".$o_main->db->escape_str($voucher['id'])."' ORDER BY id";
									$o_query = $o_main->db->query($s_sql);
									$transactions = ($o_query ? $o_query->result_array() : array());

									?>
									<tr>
										<td><?php echo $voucher['text'];?></td>
										<td><?php echo date("d.m.Y", strtotime($voucher['date']));?></td>
										<td>
											<b><?php echo $formText_Transactions_output;?></b><br/><br/>
											<table class="table">
							                    <tr>
							                        <th><?php echo $formText_Type_output;?></th>
							                        <th><?php echo $formText_Bookaccount_output;?></th>
							                        <th class="rightAligned"><?php echo $formText_Amount_Output;?></th>
							                    </tr>
							                    <?php
							                    $debitor_share = 0;
							                    foreach( $transactions as $transaction) {
													if($transaction['bookaccount_id'] != 22 && $transaction['bookaccount_id'] != 16 && $transaction['bookaccount_id'] != 15 ) {
								                        $s_sql = "SELECT * FROM collecting_cases_claim_line_type_basisconfig WHERE id = ?";
								                        $o_query = $o_main->db->query($s_sql, array($transaction['collecting_claim_line_type']));
								                        $claim_line_type = $o_query ? $o_query->row_array() : array();

								                        $s_sql = "SELECT * FROM cs_bookaccount WHERE id = ?";
								                        $o_query = $o_main->db->query($s_sql, array($transaction['bookaccount_id']));
								                        $bookaccount = $o_query ? $o_query->row_array() : array();

								                         ?>
								                         <tr>
								                             <td><?php echo $claim_line_type['type_name'];?></td>
								                             <td><?php echo $bookaccount['name']; ?></td>
								                             <td class="rightAligned"><?php echo number_format($transaction['amount'], 2, ",", " "); ?></td>
								                         </tr>
							                    <?php }
												}
							                    ?>
							                </table>
											<b><?php echo $formText_Ledger_output;?></b><br/><br/>
											<table class="table">
							                    <tr>
							                        <th><?php echo $formText_Bookaccount_output;?></th>
							                        <th class="rightAligned"><?php echo $formText_Amount_Output;?></th>
							                    </tr>
							                    <?php
							                    $debitor_share = 0;
							                    foreach( $transactions as $transaction) {
													if($transaction['bookaccount_id'] != 22 && $transaction['bookaccount_id'] != 16 && $transaction['bookaccount_id'] != 15 ) {
													} else {

								                        $s_sql = "SELECT * FROM cs_bookaccount WHERE id = ?";
								                        $o_query = $o_main->db->query($s_sql, array($transaction['bookaccount_id']));
								                        $bookaccount = $o_query ? $o_query->row_array() : array();

								                         ?>
								                         <tr>
								                             <td><?php echo $bookaccount['name']; ?></td>
								                             <td class="rightAligned"><?php echo number_format($transaction['amount'], 2, ",", " "); ?></td>
								                         </tr>
							                    <?php }
												}
							                    ?>
							                </table>

										</td>
										<td><span class="glyphicon glyphicon-pencil edit_voucher" data-id="<?php echo $voucher['id'];?>"></span></td>
									</tr>
									<?php
								}?>
							</table>
						</div>
					</div>
					<?php 					
					$s_sql = "SELECT cccc.id,
					cccc.created,
					cccc.message,
					cccc.message_from_oflow,
					cccc.creditor_id,
					cccc.collecting_company_case_id,
					cccc.screenshot,
					cccc.files,
					cccc.createdBy,
					cccc.read_check
					FROM creditor_collecting_company_chat cccc
					WHERE cccc.creditor_id = ? AND cccc.collecting_company_case_id = ?
					ORDER BY cccc.created DESC";
					$o_query = $o_main->db->query($s_sql, array($caseData['creditor_id'], $caseData['id']));
					$creditor_messages = ($o_query ? $o_query->result_array() : array());
					$unread_message_ids = array();
					foreach($creditor_messages as $selected_chat_message){
                        if(!$selected_chat_message['read_check'] && $selected_chat_message['message_from_oflow'] == 0){
                            $unread_message_ids[] = $selected_chat_message['id'];
                        }
                    }
					?>
					<div class="p_contentBlockWrapper">
						<div class="p_pageDetailsSubTitle white dropdown_content_show messages_section">
							<?php echo $formText_MessagesFromCustomer_Output;?> 							
							<span class="message_count"><?php echo count($creditor_messages)." ".$formText_Messages_output;
							if(count($unread_message_ids) > 0) echo ' <span class="unread_wrapper">('.count($unread_message_ids)." ".$formText_Unread_output.")</span>";
							?></span>
							<div class="showArrow"><span class="glyphicon <?php if($_GET['message_section_loaded']) { echo 'glyphicon-triangle-bottom';} else { echo 'glyphicon-triangle-right';} ?> "></span></div>
						</div>
						<div class="p_contentBlock dropdown_content noTopPadding" style="<?php if($_GET['message_section_loaded']) { } else { echo 'display: none;';}?>">
							<div class="creditor_message_wrapper">
								<div class="creditor_chat_wrapper">
									<form class="messageForm" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CreditorChat&folderfile=output&folder=output&inc_obj=ajax&inc_act=add_message";?>" method="post">
										<input type="hidden" name="fwajax" value="1">
										<input type="hidden" name="fw_nocss" value="1">
										<input type="hidden" name="creditor_id" value="<?php echo $creditor['id'];?>">
										<input type="hidden" name="collecting_company_case_id" value="<?php echo $caseData['id'];?>">
											<textarea class="creditor_chat" name="message"></textarea>
											<?php 
											
											$s_sql = "SELECT * FROM moduledata WHERE name = 'CreditorChat'";
											$o_query = $o_main->db->query($s_sql);
											$creditor_chat_module = ($o_query ? $o_query->row_array() : array());
											$creditorChatModuleID = $creditor_chat_module['id'];
											$fwaFileuploadConfigs = array(
												array (
												'module_folder' => 'CreditorChat', // module id in which this block is used
												'id' => 'articleimageeuploadpopup',
												'upload_type'=>'file',
												'content_table' => 'creditor_collecting_company_chat',
												'content_field' => 'files',
												'content_id' => $cid,
												'content_module_id' => $creditorChatModuleID, // id of module
												'dropZone' => 'block',
												'callbackAll' => 'callBackOnUploadAll',
												'callbackStart' => 'callbackOnStart',
												'callbackDelete' => 'callbackOnDelete'
												),
												array (
												'module_folder' => 'CreditorChat', // module id in which this block is used
												'id' => 'articleinsfileupload',
												'upload_type' => 'image',
												'content_table' => 'creditor_collecting_company_chat',
												'content_field' => 'screenshot',
												'content_id' => $cid,
												'content_module_id' => $creditorChatModuleID, // id of module
												'dropZone' => 'block',
												'callbackAll' => 'callBackOnUploadAll',
												'callbackStart' => 'callbackOnStart',
												'callbackDelete' => 'callbackOnDelete'
												)
											);
											?>
											<div class="line">
												<div class="lineTitle"><?php echo $formText_Files_Output; ?></div>
												<div class="lineInput" style="margin-bottom: 10px">
													<?php
													$fwaFileuploadConfig = $fwaFileuploadConfigs[0];
													include __DIR__ . '/fileupload_popup/output.php';
													?>
												</div>
												<div class="clear"></div>
											</div>
											<div class="line">
												<div class="lineTitle"><?php echo $formText_Images_Output; ?></div>
												<div class="lineInput">
													<?php
													$fwaFileuploadConfig = $fwaFileuploadConfigs[1];
													include __DIR__ . '/fileupload_popup/output.php';
													?>
												</div>
												<div class="clear"></div>
											</div>
									</form>
									<div class="send_message"><?php echo $formText_Send_output;?></div>
								</div>
								<div class="creditor_chat_messages">
									<?php 
									foreach($creditor_messages as $creditor_message) {
										?>
										<div class="chat_message<?php if($creditor_message['message_from_oflow']) echo ' from_oflow'?>">
											<div class="message_info"><?php echo $formText_Created_output;?> <?php echo date("d.m.Y H:i", strtotime($creditor_message['created']))?> <?php if($creditor_message['message_from_oflow']) { echo $formText_Oflow_output." ".$creditor_message['createdBy'];} else { echo $creditor_message['createdBy'];}?></div>
											<div class="chat_message_info">
												<div><?php echo nl2br($creditor_message['message']);?></div>
												<?php
												$ordersScreenshots = json_decode($creditor_message['screenshot'], true);
												foreach($ordersScreenshots as $file) {
													$fileParts = explode('/',$file[1][0]);
													$fileName = array_pop($fileParts);
													$fileParts[] = rawurlencode($fileName);
													$filePath = implode('/',$fileParts);
													$fileUrl = $extradomaindirroot."/../".$file[1][0];
													$fileName = $file[0];
													if(strpos($file[1][0],'uploads/protected/')!==false)
													{
														$fileUrl = $extradomaindirroot."/../".$filePath.'?caID='.$_GET['caID'].'&table=creditor_collecting_company_chat&field=screenshot&ID='.$creditor_message['id'];
													}
												?>
												
												<span class="screenshot-view" >
													<a href="<?php echo $fileUrl;?>" class="fancybox" rel="message<?php echo $creditor_message['id'];?>">
														<img src="<?php echo $fileUrl;?>" class="screenshotImage"/></a>
													</span>
												<?php } ?>
												<?php
												$files = json_decode($creditor_message['files'], true);
												foreach($files as $file) {
													$fileParts = explode('/',$file[1][0]);
													$fileName = array_pop($fileParts);
													$fileParts[] = rawurlencode($fileName);
													$filePath = implode('/',$fileParts);
													$fileUrl = $extradomaindirroot."/../".$file[1][0];
													$fileName = $file[0];
													if(strpos($file[1][0],'uploads/protected/')!==false)
													{
														$fileUrl = $extradomaindirroot."/../".$filePath.'?caID='.$_GET['caID'].'&table=creditor_collecting_company_chat&field=files&ID='.$creditor_message['id'];
													}
												?>
													<div class="project-file">
														<div class="project-file-file">
															<a href="<?php echo $fileUrl;?>" download><?php echo $fileName;?></a>
														</div>
													</div>
													<?php
												}
												?>
											</div>
										</div>
										<?php
									}
									?>
									<div class="clear"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
var out_popup;
var out_popup_options={
	follow: [true, true],
	followSpeed:0,
	fadeSpeed: 0,
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function(){
		$(this).addClass('opened');
	},
	onClose: function(){
		$(this).removeClass('opened');
		if($(this).is('.close-reload')) {
			loadView("details", {cid:"<?php echo $cid;?>"});
		}
		$(this).find("#popupeditboxcontent").html("");
	}
};


function callBackOnUploadAll(data) {
	// updatePreview();
    $('.creditor_message_wrapper .send_message').val('<?php echo $formText_Send; ?>').prop('disabled',false);

};
function callbackOnStart(data) {
    $('.creditor_message_wrapper .send_message').val('<?php echo $formText_UploadInProgress_output;?>...').prop('disabled',true);
};
function callbackOnDelete(data){
	// updatePreview();
}
$(function(){
	$(".fancybox").fancybox();
	// $(".generatePdf").off("click").on("click", function(e) {
	// 	e.preventDefault();
	// 	var data = {
	// 		caseId: '<?php echo $cid;?>',
	// 	};
	// 	ajaxCall('generatePdf', data, function(json) {
	// 		if(json.data != undefined) {
	// 			var a = document.createElement('a');
	// 			a.href =  '<?php echo $extradomaindirroot."/uploads/";?>'+json.data;
	// 			a.setAttribute('target', '_blank');
	// 			a.click();
	// 		}
	// 	});
	// })
	$(".edit_forgiven").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			caseId: '<?php echo $cid;?>'
		};
		ajaxCall('edit_forgiven', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".edit_voucher").off("click").on("click", function(e){
		e.preventDefault();

		var data = {
			id: $(this).data("id")
		};
		ajaxCall({module_name:"CSMainbook", module_file:'editVoucher', module_folder: "output"}, data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".addAdditionalDays").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			collecting_case_id: '<?php echo $cid;?>',
			process_step_id: $(this).data("process_step_id"),
			cid: $(this).data("id")
		};
		ajaxCall('add_additional_days', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".caseStatusChange").on('change', function(e){
		e.preventDefault();
		var caseId  = $(this).data('case-id');
		var data = {
			caseId: caseId,
			action:"statusChange",
			status: $(this).val()
		};
		ajaxCall('edit_case', data, function(json) {
			loadView("details", {cid:"<?php echo $cid;?>"});
		});
	});
	$(".caseSubStatusChange").on('change', function(e){
		e.preventDefault();
		var caseId  = $(this).data('case-id');
		var data = {
			caseId: caseId,
			action:"subStatusChange",
			status: $(this).val()
		};
		ajaxCall('edit_case', data, function(json) {
			loadView("details", {cid:"<?php echo $cid;?>"});
		});
	});
	$(".output-edit-case-detail").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			caseId: $(this).data('case-id')
		};
		ajaxCall('edit_case', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".output-edit-comment").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			caseId: $(this).data('case-id'),
			cid: $(this).data('comment-id')
		};
		ajaxCall('edit_comment', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});

	$(".output-delete-comment").unbind("click").on('click', function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			cid: self.data('comment-id'),
			caseId: self.data('case-id'),
			output_delete: 1
		};
		bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
			if (result) {
				ajaxCall('edit_comment', data, function(json) {
					loadView("details", {cid:"<?php echo $cid;?>"});
				});
			}
		});
	});
	$(".output-edit-payment").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			caseId: $(this).data('case-id'),
			cid: $(this).data('comment-id')
		};
		ajaxCall('edit_payment', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".output-edit-payment-plan").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			caseId: $(this).data('case-id'),
			cid: $(this).data('paymentplan-id')
		};
		ajaxCall('edit_payment_plan', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".output-edit-claims").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			caseId: $(this).data('case-id'),
			cid: $(this).data('claim-id')
		};
		ajaxCall('edit_claims', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".output-delete-claims").unbind("click").on('click', function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			cid: self.data('claim-id'),
			caseId: self.data('case-id'),
			output_delete: 1
		};
		bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
			if (result) {
				ajaxCall('edit_claims', data, function(json) {
					loadView("details", {cid:"<?php echo $cid;?>"});
				});
			}
		});
	});


    $(".dropdown_content_show").unbind("click").bind("click", function(e){
        var parent = $(this);
        if($(e.target).hasClass("dropdown_content_show") || $(e.target).hasClass("showArrow") || $(e.target).parent().hasClass("showArrow")){
            var dropdown = parent.next(".p_contentBlock.dropdown_content");
            if(dropdown.is(":visible")) {
                dropdown.slideUp();
                parent.find(".showArrow .glyphicon").addClass("glyphicon-triangle-right");
                parent.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-bottom");
            } else {
                if(parent.hasClass("autoload")) {
                    dropdown.slideDown(0);
                    parent.removeClass("autoload");
                } else {
                    dropdown.slideDown();
                }
                parent.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-right");
                parent.find(".showArrow .glyphicon").addClass("glyphicon-triangle-bottom");
            }
        }
    })

	$(".output-edit-messages-creditor").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			caseId: $(this).data('case-id'),
			cid: $(this).data('message-id')
		};
		ajaxCall('edit_message_creditor', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});

	$(".output-delete-messages-creditor").unbind("click").on('click', function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			cid: self.data('message-id'),
			caseId: self.data('case-id'),
			output_delete: 1
		};
		bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
			if (result) {
				ajaxCall('edit_message_creditor', data, function(json) {
					loadView("details", {cid:"<?php echo $cid;?>"});
				});
			}
		});
	});

	$(".output-edit-messages-debitor").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			caseId: $(this).data('case-id'),
			cid: $(this).data('message-id')
		};
		ajaxCall('edit_message_debitor', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});

	$(".output-delete-messages-debitor").unbind("click").on('click', function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			cid: self.data('message-id'),
			caseId: self.data('case-id'),
			output_delete: 1
		};
		bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
			if (result) {
				ajaxCall('edit_message_debitor', data, function(json) {
					loadView("details", {cid:"<?php echo $cid;?>"});
				});
			}
		});
	});
	$(".output-edit-objection").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			collecting_case_id: $(this).data('case-id'),
			cid: $(this).data('objection-id')
		};
		ajaxCall('edit_paused', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".output-close-objection").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			collecting_case_id: $(this).data('case-id'),
			cid: $(this).data('objection-id'),
			close: 1
		};
		ajaxCall('edit_paused', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".output-delete-objection").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			collecting_case_id: $(this).data('case-id'),
			cid: $(this).data('objection-id'),
			delete: 1
		};
		bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
			if (result) {
				ajaxCall('edit_paused', data, function(json) {
					$('#popupeditboxcontent').html('');
					$('#popupeditboxcontent').html(json.html);
					out_popup = $('#popupeditbox').bPopup(out_popup_options);
					$("#popupeditbox:not(.opened)").remove();
				});
			}
		})
	});
	$(".output-edit-returned").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			collecting_case_id: $(this).data('case-id'),
			cid: $(this).data('returned-id'),
			reason: 'returned_letter'
		};
		ajaxCall('edit_paused', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".output-delete-returned").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			collecting_case_id: $(this).data('case-id'),
			cid: $(this).data('returned-id'),
			output_delete: 1
		};
		bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
			if (result) {
				ajaxCall('edit_paused', data, function(json) {
					loadView("details", {cid:"<?php echo $cid;?>"});
				});
			}
		})
	});



	$(".delete_letter").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			letter_id: $(this).data('letter-id')
		};
		ajaxCall('delete_claimletter', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".output-edit-otherpart").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			caseId: $(this).data('case-id'),
			cid: $(this).data('otherpart-id')
		};
		ajaxCall('edit_other_part', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".output-delete-otherpart").unbind("click").on('click', function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			caseId: $(this).data('case-id'),
			cid: $(this).data('otherpart-id'),
			action: "delete"
		};
		bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
			if (result) {
				ajaxCall('edit_other_part', data, function(json) {
					loadView("details", {cid:"<?php echo $cid;?>"});
				});
			}
		});
	});
	$(".stopCase").off("click").on("click", function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			caseId: $(this).data('case-id'),
			action: "stopCase"
		};
		ajaxCall('edit_case', data, function(json) {
			if(json.html != ""){
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(json.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
				out_popup.addClass("close-reload");
			} else {
				loadView("details", {cid:"<?php echo $cid;?>"});
			}
		});
	})
	$(".deleteCase").off("click").on("click", function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			caseId: $(this).data('case-id'),
			action: "deleteCase"
		};
		bootbox.confirm('<?php echo $formText_ConfirmDeletingTheCase_output; ?>', function(result) {
			if (result) {
				ajaxCall('edit_case', data, function(json) {
					if(json.html != ""){
						$('#popupeditboxcontent').html('');
						$('#popupeditboxcontent').html(json.html);
						out_popup = $('#popupeditbox').bPopup(out_popup_options);
						$("#popupeditbox:not(.opened)").remove();
						out_popup.addClass("close-reload");
					} else {
						loadView("details", {cid:"<?php echo $cid;?>"});
					}
				});
			}
		});
	})
	$(".reactivateCase").off("click").on("click", function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			caseId: $(this).data('case-id'),
			action: "reactivateCase"
		};
		bootbox.confirm('<?php echo $formText_ConfirmStartCase_output; ?>', function(result) {
			if (result) {
				ajaxCall('edit_case', data, function(json) {
					loadView("details", {cid:"<?php echo $cid;?>"});
				});
			}
		});
	})
	$(".show_connected_transactions_by_link_id").off("click").on("click", function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			link_id: $(this).data('link-id'),
			creditor_id: '<?php echo $creditor['id']?>'
		};
		ajaxCall('show_linked_transactions', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".processCase").off("click").on("click", function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			caseId: $(this).data('case-id'),
			action: "processCase"
		};
		bootbox.confirm('<?php echo $formText_LaunchCaseProcessAndLetterCreation_output; ?>', function(result) {
			if (result) {
				ajaxCall('edit_case', data, function(json) {
					if(json.html != ""){
						$('#popupeditboxcontent').html('');
						$('#popupeditboxcontent').html(json.html);
						out_popup = $('#popupeditbox').bPopup(out_popup_options);
						$("#popupeditbox:not(.opened)").remove();
						out_popup.addClass("close-reload");
					} else {
						loadView("details", {cid:"<?php echo $cid;?>"});
					}
				});
			}
		});
	})
	$(".createLetter").off("click").on("click", function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			caseId: $(this).data('case-id'),
			action: "create_letter"
		};
		ajaxCall('create_letter', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})

	$(".edit_due_date").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			caseId: '<?php echo $cid;?>'
		};
		ajaxCall('edit_due_date', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".edit_case_step").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			caseId: '<?php echo $cid;?>'
		};
		ajaxCall('edit_case_step', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".edit_claim_fees_setting").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			caseId: '<?php echo $cid;?>'
		};
		ajaxCall('edit_claim_fees_setting', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".edit_currency_explanation_text").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			caseId: '<?php echo $cid;?>'
		};
		ajaxCall('edit_currency_explanation_text', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".showDeletedClaimLetters").off("click").on("click", function(){
		$(".deletedClaimLetters").slideToggle();
		$(this).toggleClass("active");
	})
	$(".create_restnote").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			caseId: '<?php echo $cid;?>'
		};
		bootbox.confirm('<?php echo $formText_CreateRestnote_output; ?>?', function(result) {
			if (result) {
				ajaxCall('create_restnote', data, function(json) {
					$('#popupeditboxcontent').html('');
					$('#popupeditboxcontent').html(json.html);
					out_popup = $('#popupeditbox').bPopup(out_popup_options);
					$("#popupeditbox:not(.opened)").remove();
					out_popup.addClass("close-reload");
				});
			}
		});
	})
	$(".edit_collecting_address").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			customer_id: $(this).data("customer-id"),
			creditor_id: '<?php echo $creditor['id'];?>'
		};
		ajaxCall('edit_collecting_address', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".show_debitor_history_btn").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			customer_id: $(this).data("customer-id")
		};
		ajaxCall('show_debitor_history', data, function(json) {
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".output-edit-worklist").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			worklistConnectionId: $(this).data("worklist-id"),
			caseId: '<?php echo $cid;?>'
		};
		ajaxCall('edit_worklist_connection', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})

	$(".output-delete-worklist").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			worklistConnectionId: $(this).data("worklist-id"),
			caseId: '<?php echo $cid;?>',
			action: "deleteConnection"
		};
		bootbox.confirm('<?php echo $formText_DeleteConnection_output; ?>?', function(result) {
			if (result) {
				ajaxCall('edit_worklist_connection', data, function(json) {
					loadView("details", {cid:"<?php echo $cid;?>"});
				});
			}
		})
	})
	$(".output-edit-coverlines").off("click").on("click", function(e) {
		e.preventDefault();
		var data = {
			payment_id: $(this).data("paymentid"),
		};
		ajaxCall('edit_payment_coverlines', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".output-delete-transaction-connection").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			transation_id: $(this).data('transaction-id'),
			cid: '<?php echo $cid;?>'
		};
		bootbox.confirm('<?php echo $formText_ConfirmDeleteConnection_output; ?>', function(result) {
			if (result) {
				ajaxCall('remove_connection', data, function(json) {
					loadView("details", {cid:"<?php echo $cid;?>"});
				});
			}
		})
	})


	$(".edit_contactperson").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $creditor['id'];?>',
			contactperson_id: $(this).data("contactpersonid")
		};
		ajaxCall({module_name:"CreditorsOverview", module_file:'edit_creditor_contactperson', module_folder: "output"}, data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".delete_contactperson").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $creditor['id'];?>',
			contactperson_id: $(this).data("contactpersonid"),
			action: "delete"
		}
		bootbox.confirm('<?php echo $formText_DeleteContactperson_output; ?>?', function(result) {
			if (result) {
				ajaxCall({module_name:"CreditorsOverview", module_file:'edit_creditor_contactperson', module_folder: "output"}, data, function(json) {
				    loadView("details", {cid:"<?php echo $cid;?>"});
				});
			}
		});
	})
	$(".without_fee_paid").off("click").on("click", function(){
		var checkbox = 0;
		if($(this).is(":checked")){
			checkbox = 1;
		}
		var data = {
			case_id: "<?php echo $cid;?>",
			without_fee_paid: checkbox
		};
		ajaxCall('edit_without_fee_checkbox', data, function(json) {
			loadView("details", {cid:"<?php echo $cid;?>"});
		});
	})
	$(".without_fee_notpaid").off("click").on("click", function(){
		var checkbox = 0;
		if($(this).is(":checked")){
			checkbox = 1;
		}
		var data = {
			case_id: "<?php echo $cid;?>",
			without_fee_notpaid: checkbox
		};
		ajaxCall('edit_without_fee_checkbox', data, function(json) {
			loadView("details", {cid:"<?php echo $cid;?>"});
		});
	})
	$(".company_fee_paid").off("click").on("click", function(){
		var checkbox = 0;
		if($(this).is(":checked")){
			checkbox = 1;
		}
		var data = {
			case_id: "<?php echo $cid;?>",
			company_fee_paid: checkbox
		};
		ajaxCall('edit_without_fee_checkbox', data, function(json) {
			loadView("details", {cid:"<?php echo $cid;?>"});
		});
	})
	$(".company_fee_notpaid").off("click").on("click", function(){
		var checkbox = 0;
		if($(this).is(":checked")){
			checkbox = 1;
		}
		var data = {
			case_id: "<?php echo $cid;?>",
			company_fee_notpaid: checkbox
		};
		ajaxCall('edit_without_fee_checkbox', data, function(json) {
			loadView("details", {cid:"<?php echo $cid;?>"});
		});
	});
	$(".checkbox1").off("click").on("click", function(){
		var checkbox = 0;
		if($(this).is(":checked")){
			checkbox = 1;
		}
		var data = {
			case_id: "<?php echo $cid;?>",
			checkbox1: checkbox
		};
		ajaxCall('edit_without_fee_checkbox', data, function(json) {
			loadView("details", {cid:"<?php echo $cid;?>"});
		});
	});
	$('.commentBlock .expand_collapse').off('click').on('click', function(){
		$parent = $(this).parent();
		if($parent.is('.opened'))
		{
			$parent.css({'max-height':'250px', 'overflow':'hidden'}).removeClass('opened');
		} else {
			$parent.removeAttr('style').addClass('opened');
		}
	});
	$(".show_all_transactions").off("click").on("click", function(){
		var data = {
			case_id: "<?php echo $cid;?>"
		};
		ajaxCall('show_all_transactions', data, function(json) {
			$(".transactions_holder").html(json.html);
		});
	})
	$(".currencySelect").change(function(){
		var data = {
			case_id: "<?php echo $cid;?>",
			currency: $(this).val()
		};
		ajaxCall('edit_currency', data, function(json) {
			loadView("details", {cid:"<?php echo $cid;?>"});
		});
	});
	setTimeout(output_handle_comment_ui,500);

	$(".confirm_company").off("click").on("click", function(){
		var data = {
			customerId: $(this).data('id'),
		};
		bootbox.confirm('<?php echo $formText_DoYouWantToConfirmDebitorAsCompany_output; ?>', function(result) {
			if (result) {
				ajaxCall('confirm_company', data, function(json) {
					loadView("details", {cid:"<?php echo $cid;?>"});
				});
			}
		});
	})

	$(".hoverEye").hover(
		 function(){$(this).addClass("hover");},
		 function(){
			 var item = $(this);
			 setTimeout(function(){
				 if(item.is(":hover")){

				 } else {
					 item.removeClass("hover");
				 }
			 }, 300)
		 }
	 );

 	$(".regenerate_interest").off("click").on("click", function(e){
 		e.preventDefault();
 		var data = {
 			case_id: '<?php echo $cid;?>'
 		};
 		ajaxCall('regenerate_interest', data, function(json) {
 			loadView("details", {cid:"<?php echo $cid;?>"});
 		});
 	})
 	$(".createCaseSummary").off("click").on("click", function(e){
 		e.preventDefault();
 		var data = {
 			case_id: '<?php echo $cid;?>'
 		};
 		ajaxCall('create_case_summary', data, function(json) {
 			loadView("details", {cid:"<?php echo $cid;?>"});
 		});
 	})
	$(".download_invoice").off("click").on("click", function(e){
		e.preventDefault();
		var invoice_nr = $(this).data("id");
		var data = {
			transaction_id: $(this).data("id"),
			creditor_id: '<?php echo $creditor['id']?>'
		};
		ajaxCall('download_invoice', data, function(json) {
			if(json.error == undefined){
				var link = document.createElement("a");
				  document.body.appendChild(link);
				  link.setAttribute("type", "hidden");
				  link.href = "data:text/plain;base64," + json.data;
				  link.download = "invoice_"+invoice_nr+".pdf";
				  link.click();
				  document.body.removeChild(link);
			} else {
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(json.error);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
			}
		});
	})
	$(".send_invoices").off("click").on("click", function(){
		var data = {
			case_id: '<?php echo $caseData['id']?>'
		};
		ajaxCall('send_invoices', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".create_claimline").off("click").on("click", function(){
		var data = {
			case_id: '<?php echo $caseData['id']?>',
			payment_transaction_id: $(this).data("payment_transaction_id")
		};
		ajaxCall('create_claimline_from_payment', data, function(json) {
 			loadView("details", {cid:"<?php echo $cid;?>"});
		});
	})
	$(".allowProcessing").off("click").on("click", function(){
		var checked = 0;
		if($(this).is(":checked")){
			checked = 1;
		}
		var data = {
			case_id: '<?php echo $caseData['id']?>',
			claimline_id: $(this).data("claimline_id"),
			checked: checked
		};
		ajaxCall('allow_processing_anyway', data, function(json) {
 			loadView("details", {cid:"<?php echo $cid;?>"});
		});
	})
	$(".edit_case_limitation_date").off("click").on("click", function(){
		var data = {
			case_id: '<?php echo $caseData['id']?>'
		};
		ajaxCall('edit_case_limitation_date', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".send_message").off("click").on("click", function(){
        var data = {
			creditor_id: '<?php echo $creditor['id']?>',
			collecting_company_case_id: '<?php echo $cid?>',
			message: $(".creditor_chat").val()
        };
		var formdata = $(".messageForm").serializeArray();
		var data = {};
		$(formdata ).each(function(index, obj){
			if(data[obj.name] != undefined) {
				if(Array.isArray(data[obj.name])){
					data[obj.name].push(obj.value);
				} else {
					data[obj.name] = [data[obj.name], obj.value];
				}
			} else {
				data[obj.name] = obj.value;
			}
		});

		$.ajax({
			url: $(".messageForm").attr("action"),
			cache: false,
			type: "POST",
			dataType: "json",
			data: data,
			success: function (data) {
				fw_loading_end();					
				var data = {
					cid: '<?php echo $caseData['id'];?>',
                	message_section_loaded: 1
				};
				loadView("details", data);
			}
		}).fail(function() {
			$("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
			$("#popup-validate-message").show();
			$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
			fw_loading_end();
		});

	})
	$(".messages_section").on("click", function(){
		<?php if($unread_message_ids) { ?>
			var data = {
				creditor_id: '<?php echo $creditor['id'];?>',
				case_id: '<?php echo $caseData['id']?>',
				message_ids: '<?php echo json_encode($unread_message_ids)?>'
			};
			ajaxCall('mark_as_read', data, function(json) {
				if(json.data == 1){
					$(".unread_wrapper").hide();
				}
			}, false);
		<?php } ?>
	})	
	$(".handleContinuingStep").off("click").on("click", function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			caseId: '<?php echo $caseData['id']?>',
			stepId: self.data('step-id'),
		};
		bootbox.confirm('<?php echo $formText_ConfirmStepHandling_output; ?>', function(result) {
			if (result) {
				ajaxCall('continuing_step_handled', data, function(json) {
					loadView("details", {cid:"<?php echo $caseData['id'];?>"});
				});
			}
		});
	})
	$(".start_continuing_process").off("click").on("click", function(e){
		var data = {
			case_id: '<?php echo $caseData['id']?>'
		};
		ajaxCall('start_continuing_process', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	
});
$(window).load(function() {
	output_handle_comment_ui();
});
function output_handle_comment_ui()
{
	$('.commentBlock.handle_ui').each(function(){
		$(this).removeClass('handle_ui').css('max-height','auto');
		if($(this).height() > 250)
		{
			$(this).css({'max-height':'250px', 'overflow':'hidden'});
			$(this).find('.expand_collapse').show();
		}
	});
}
</script>
<style>
	.allowProcessing {
		display: inline;
		margin-right: 5px !important;
	}
	.disputeCaseText {
		font-weight: bold;
		font-size: 18px;
	}
	.edit_forgiven {
		color: #46b2e2;
		cursor: pointer;
		margin-left: 10px;
	}
	.edit_collecting_address, .show_debitor_history_btn {
		color: #46b2e2;
		cursor: pointer;
	}
	.addAdditionalDays {
		color: #46b2e2;
		cursor: pointer;
	}
	.processStepWrapper {
		padding: 5px 0px;
		border-bottom: 1px solid #cecece;
	}
	.addAdditionalDaysRight {
		float: right;
	}
	.generatePdf {
		color: #46b2e2;
		cursor: pointer;
	}
	.totalSum {
		background: #f0f0f0;
	}
	.spaceWrapper td,
	.totalSum td {
		border: 0 !important;
	}
	.totalSum td.first {
		padding: 10px 10px !important;
	}
	.totalSum td.second {
		padding: 10px 0px !important;
	}
	.caseDetails .txt-label {
		width:30%;
	}
	.p_pageContent .btn-edit {
		text-align: right;
		margin-top: -15px;
	}
	.p_pageContent .btn-edit-table {
		margin-top: -25px;
	}
	.p_pageDetailsTitle .caseId {
		display: inline-block;
	}
	.caseStatus {
		float: right;
	}
	.p_contentBlockWrapper {
		position: relative;
		border-bottom: 2px solid #316896;
	}
	.p_contentBlockWrapper .p_contentBlock {
		border-bottom:0;
	}
	.p_contentBlockWrapper .p_pageDetailsSubTitle .showArrow {
	    float: right;
	    cursor: pointer;
	    color: #2996E7;
	    margin-left: 10px;
	    position: absolute;
	    right: 10px;
	    top: 12px;
	}
	.p_contentBlock.noTopPadding {
		padding-top: 0;
	}

	.table-borderless > tbody > tr > td,
	.table-borderless > tbody > tr > th,
	.table-borderless > tfoot > tr > td,
	.table-borderless > tfoot > tr > th,
	.table-borderless > thead > tr > td,
	.table-borderless > thead > tr > th {
		border: 0;
	}
	.commentBlock {
		border: 1px solid #bebebe;
		border-radius: 5px;
		padding: 10px;
		margin-bottom:15px;
		position:relative;
	}
	.commentBlock .createdLabel {
		color: #000000 !important;
		font-weight:bold !important;
	}
	.commentBlock .expand_collapse {
		display:none;
		text-align:center;
		color: #2996E7;
		position: absolute;
		width: 94%;
		margin:auto;
		bottom: 0;
		background: #fff;
		cursor:pointer;
	}
	.commentBlock .expand_collapse .glyphicon-chevron-up {
		display:none;
	}
	.commentBlock.opened { padding-bottom:30px; }
	.commentBlock.opened .expand_collapse { padding-bottom:10px; }
	.commentBlock.opened .expand_collapse .glyphicon-chevron-up {
		display:inherit;
	}
	.commentBlock.opened .expand_collapse .glyphicon-chevron-down {
		display:none;
	}
	.commentBlock.opened {
		max-height:auto !important;
	}
	.commentBlock .table {
		margin-bottom: 0;
	}
	.feedbackBlock {
		background: #f0f0f0;
	}
	#p_container .commentBlock td {
		padding: 0px 0px;
	}

	.ticketCommentBlock {
	    text-align: left;
	    width: 70%;
		float: right;
	}
	.ticketCommentBlock .inline_info {
	    float: right;
	    margin-left: 10px;
	}
	.ticketCommentBlock .table {
		display: block;
	    margin-bottom: 0;
		border: 1px solid #ddd;
	    border-radius: 5px;
	    margin-bottom: 10px;
	    padding: 7px 15px;
		margin-top: 5px;
	    background: #f0f0f0;
	}
	.ticketCommentBlock.from_customer {
	    text-align: left;
	    float: left;
	}
	.ticketCommentBlock.from_customer .table {
	    background: #bcdef7;
	}
	.ticketCommentBlock.from_customer .inline_info {
	    float: left;
	    margin-right: 10px;
	    margin-left: 0;
	}

	.employeeImage {
		width: 40px;
		height: 40px;
		overflow: hidden;
		position: relative;
		border-radius: 20px;
		overflow: hidden;
	    float: right;
	    margin-left: 10px;
	}
	.employeeImage img {
		width: calc(100% + 4px);
		height: auto;
		position: absolute;
	  	left: 50%;
	  	top: 50%;
	  	transform: translate(-50%, -50%);
	}
	.employeeInfo {
	    float: right;
	    width: calc(100% - 50px);
	}
	.ticketCommentBlock.from_customer .employeeImage {
	    float: left;
	    margin-left: 0;
	    margin-right: 10px;
	}
	.ticketCommentBlock.from_customer .employeeInfo {
	    float: left;
	}
	.detailContainer {
		margin-bottom: 10px;
	}
	.claimsTable > tbody > tr > td,
	.claimsTable > tbody > tr > th,
	.claimsTable > tfoot > tr > td,
	.claimsTable > tfoot > tr > th,
	.claimsTable > thead > tr > td,
	.claimsTable > thead > tr > th {
		border-bottom: 1px solid #ddd;
		padding: 5px 0px;
	}
	.caseDetails {
		position: relative;
	}
	.caseDetails .mainTable {
		width: 50%;
		float: left;
	}
	.caseDetails .collectinglevelDisplay {
		float: right;
		width: 45%;
		padding: 10px 15px;
		border: 2px solid #80d88a;
		border-radius: 5px;
	}
	.caseDetails .collectinglevelDisplay .active_step {
		font-weight: bold;
	}
	.create_restnote {
		float: right;
		margin-top: 10px;
		cursor: pointer;
		color: #46b2e2;
	}
	.stopCase {
		font-size: 13px;
		line-height: 15px;
		background: #194273;
		color: #FFF;
		padding: 6px 15px;
		border-radius: 3px;
		border: 1px solid transparent;
		margin: 15px 0 0 3px;
		float: right;
		cursor: pointer;
	}
	.deleteCase {
		float: right;
		margin-top: 10px;
		cursor: pointer;
		color: #46b2e2;
	}
	.reactivateCase {
		font-size: 13px;
		line-height: 15px;
		background: #194273;
		color: #FFF;
		padding: 6px 15px;
		border-radius: 3px;
		border: 1px solid transparent;
		margin: 15px 0 0 3px;
		float: right;
		cursor: pointer;
	}
	.levelText {
		font-weight: bold;
		float: right;
		margin-left: 30px;
		color: #80d88a;
	}
	.processText {
		float: right;
		margin-left: 30px;
	}
	.paymentPlanTable {
		width: 60%;
	}
	.timeExplanation i {
        color: #bbb;
    }
    .timeExplanation .hoverSpan {
        display: none;
		position: absolute;
		padding: 5px 10px;
		background: #fff;
		border: 1px solid #cecece;
		max-width: 300px;
    }
    .timeExplanation:hover .hoverSpan {
        display: block;
        z-index: 100;
    }
	.processCase {
		float: left;
		margin-top: 20px;
		cursor: pointer;
		color: #46b2e2;
	}
	.createLetter {
		float: left;
		margin-top: 20px;
		cursor: pointer;
		color: #46b2e2;
	}
	.edit_due_date {
		cursor: pointer;
		color: #46b2e2;
	}
	.edit_case_step {
		cursor: pointer;
		color: #46b2e2;
	}
	.edit_currency_explanation_text {
		padding-left:10px;
		cursor: pointer;
		color: #46b2e2;
	}
	.edit_claim_fees_setting {
		cursor: pointer;
		color: #46b2e2;
	}
	.delete_letter {
		cursor: pointer;
		color: #46b2e2;
	}
	.showDeletedClaimLetters {
		margin-top: 10px;
		cursor: pointer;
		color: #46b2e2;
	}
	.showDeletedClaimLetters .glyphicon-menu-up {
		display: none;
	}
	.showDeletedClaimLetters.active .glyphicon-menu-down {
		display: none;
	}
	.showDeletedClaimLetters.active .glyphicon-menu-up {
		display: inline;
	}
	.deletedClaimLetters {
		margin-top: 10px;
		display: none;
	}

	.customer_difference_info {
		padding: 5px 10px;
		background: #fff3cd;
	}
	.output-edit-coverlines {
		color: #46b2e2;
		cursor: pointer;
		float: right;
	}
	.rightAligned {
		text-align: right;
	}
	.add_contactperson {
		cursor: pointer;
		color: #46b2e2;
		margin-left: 10px;
	}
	.edit_contactperson {
		cursor: pointer;
		color: #46b2e2;
	}
	.delete_contactperson {
		cursor: pointer;
		color: #46b2e2;
	}
	.confirm_company {
		cursor: pointer;
		color: #46b2e2;
		margin-left: 20px;
	}

	.hoverEye {
		position: relative;
		color: #777;
		margin-top: 2px;
	}
	.hoverEye .hoverInfo {
		font-family: 'PT Sans', sans-serif;
		width: 450px;
		display: none;
		color: #000;
		position: absolute;
		right: 0%;
		top: 100%;
		padding: 5px 10px;
		background: #fff;
		border: 1px solid #ccc;
		z-index: 1;
		max-height: 300px;
		overflow: auto;
	}
	.hoverEye .hoverInfo2 {
		width: 400px;
	}
	.hoverEye .hoverInfo3 {
		width: 300px;
	}
	.hoverEye .hoverInfoSmall {
		width: 200px;
	}
	.hoverEye .hoverInfoNotes {
		left: -40px;
	}
	.hoverEye.hover .hoverInfo {
		display: block;
	}
	.manual_process_info {
		margin-top: 10px;
	}
	.manual_process_info.active {
		font-weight: bold;
	}

	.createCaseSummaryWrapper {
		float: right;
		margin-top: 10px;
	}
	.createCaseSummary {
		cursor: pointer;
		color: #46b2e2;
		margin-top: 10px;
	}
	.regenerate_interest {
		cursor: pointer;
		color: #46b2e2;
	}
	.download_invoice {
		cursor: pointer;
		color: #46b2e2;
	}
	.send_invoices {
		cursor: pointer;
		color: #46b2e2;
		margin-left: 10px;
		font-weight: normal;
	}
	.create_claimline {
		cursor: pointer;
		color: #46b2e2;
		display: block;
	}

	.creditor_message_wrapper {
		background: #fff;
	}
	.creditor_message_wrapper .send_message {
		display: inline-block;
		border: none;
		border-radius: 4px;
		padding: 5px 10px;
		color: #FFF;
		background: #124171;
		outline: none;
		margin-top: 10px;
		cursor:pointer;
	}
	.creditor_chat_wrapper {
		padding-top: 10px;
	}
	.creditor_chat_wrapper .creditor_chat {
		width: 100%;
	}
	.creditor_chat_messages {
		margin-top: 10px;
		padding: 5px 0px;
	}
	.creditor_chat_messages .chat_message {
		display: block;
	    margin-bottom: 10px;	
		margin-top: 5px;
		float: left;
		width: 65%;
		text-align: left;		
	}
	.creditor_chat_messages .chat_message_info {
		border: 1px solid #ddd;
	    border-radius: 5px;
		word-break: break-all;
	    padding: 5px 7px;
		background: #6edaed;
	}
	.creditor_chat_messages .chat_message.from_oflow {
	    float: right;
		text-align: right;
	}
	.creditor_chat_messages .chat_message.from_oflow .chat_message_info{
	    background: #f0f0f0;
	}
	.creditor_chat_messages .message_info {
		color: #bbbbbb;
	}
	.show_case {
		margin-left: 10px;
	}
	.screenshot-view {
	    display: inline-block;
	    vertical-align: top;
	    width: 50px;
	    margin-right: 10px;
	    border: 1px solid #cecece;
	    cursor: pointer;
	}
	.screenshot-view img {
	    width: 100%;
	}
	
	.message_count {
		margin-left: 10px;
		font-weight: normal;
		font-size: 12px;
	}
	.unread_wrapper {
		font-size: 12px;
		color: red;
	}
	.marked_to_cease_to_exist {
		color: red;
		margin-left: 5px;
	}
	.edit_case_limitation_date {
		margin-left: 10px;
		cursor: pointer;
		color: #46b2e2;
	}
	.handleAppearInLegalStep,
	.handleContinuingStep,
	.handleAppearInCallStep {
		float: right;
		color: #fff;
		padding: 5px;
		background: #194273;
		cursor: pointer;
		border-radius: 5px;;
	}
	.start_continuing_process {
		color: #fff;
		padding: 5px 10px;
		background: #194273;
		cursor: pointer;
		border-radius: 5px;
		margin-top: 10px;
	}
</style>