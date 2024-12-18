<?php
$default_list = 1;

$mainlist_filter = "reminderLevel";
$default_sublist_filter = "not_approved";
// if(count($customer_listtabs_basisconfig) > 0) {
// 	$default_list = $customer_listtabs_basisconfig[0]['id'];
// }
$s_sql = "SELECT * FROM employee WHERE email = ?";
$o_query = $o_main->db->query($s_sql, array($variables->loggID));
if($o_query && $o_query->num_rows()>0){
    $currentEmployee = $o_query->row_array();
}

$filtersList = array("search_filter", "responsibleperson_filter", "projecttype_filter", "sub_status_filter", "date_from_filter", "date_to_filter", "case_filter");

if ($_POST['list_filter']) $_GET['list_filter'] = $_POST['list_filter'];
if ($_POST['sublist_filter']) $_GET['sublist_filter'] = $_POST['sublist_filter'];
foreach($filtersList as $filterName){
	if ($_POST[$filterName]) $_GET[$filterName] = $_POST[$filterName];
}

$list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : $default_list;
$sublist_filter = $_GET['sublist_filter'] ? ($_GET['sublist_filter']) : $default_sublist_filter;
foreach($filtersList as $filterName){
	${$filterName} = $_GET[$filterName] ? ($_GET[$filterName]) : '';
}
if($responsibleperson_filter == ''){
    $responsibleperson_filter = $currentEmployee['id'];
}
if($date_from_filter == ""){
	$date_from_filter = date("01.m.Y");
}
if($date_to_filter == ""){
	$date_to_filter = date("t.m.Y");
}

$_SESSION['list_filter'] = $list_filter;
$_SESSION['mainlist_filter'] = $mainlist_filter;
foreach($filtersList as $filterName){
	$_SESSION[$filterName] = ${$filterName};
}

$filters = array();
foreach($filtersList as $filterName){
	$filters[$filterName] = ${$filterName};
}
if(isset($_GET['order_direction'])){ $order_direction = $_GET['order_direction']; } else { $order_direction = '';}
if(isset($_GET['order_field'])){ $order_field = $_GET['order_field']; } else { $order_field = '';}
$filters['order_field'] = $order_field;
$filters['order_direction'] = $order_direction;

require_once __DIR__ . '/functions.php';
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

$s_sql = "SELECT * FROM moduledata WHERE name = 'Orders'";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $orders_module_id_find = $o_query->row_array();
    $orders_module_id = $orders_module_id_find["uniqueID"];
}

$s_sql = "SELECT * FROM collecting_cases_main_status_basisconfig ORDER BY sortnr ASC";
$o_query = $o_main->db->query($s_sql, array());
$main_statuses = ($o_query ? $o_query->result_array() : array());
// $case_count = array();
// foreach($main_statuses as $main_status) {
//     $case_count[$main_status['id']] = get_customer_list_count($o_main, $main_status['id'],$sublist_filter, $filters);
// }

//$current_count = get_customer_list_count($o_main, $list_filter,$sublist_filter, $filters);
$case_count[1] = $case_count[2] = $objection_count = $transferred_count = $all_cases_count = $canceled_count = $missing_transaction_count = '';//'<img src="'.$extradir.'/output/elementsOutput/ajax-loader.gif"/>';
//if($list_filter == 1){
//	$case_count[1] = $current_count;
//} else if($list_filter == 2) {
//	$case_count[2] = $current_count;
//	if($list_filter == 2){
//		// $approved_count = get_customer_list_count($o_main, $list_filter, "approved", $filters);
//		$not_approved_count = get_customer_list_count($o_main, $list_filter, "not_approved", $filters);
//	}
//} else if($list_filter == "cases_objection") {
//	$objection_count = $current_count;
//} else if($list_filter == "cases_transferred") {
//	$transferred_count = $current_count;
//} else if($list_filter == "all_cases") {
//	$all_cases_count = $current_count;
//} else if($list_filter == "cases_canceled") {
//	$canceled_count = $current_count;
//} else if($list_filter == "missing_transaction") {
//	$missing_transaction_count = $current_count;
//} 

// $case_count[1] = get_customer_list_count($o_main, 1,$sublist_filter, $filters);

// $case_count[2] = get_customer_list_count($o_main, 2,$sublist_filter, $filters);
// if($list_filter == 2){
//     // $approved_count = get_customer_list_count($o_main, $list_filter, "approved", $filters);
//     $not_approved_count = get_customer_list_count($o_main, $list_filter, "not_approved", $filters);
// }
// $objection_count = get_customer_list_count($o_main, "cases_objection", $sublist_filter, $filters);
// $transferred_count = get_customer_list_count($o_main, "cases_transferred", $sublist_filter, $filters);
// $all_cases_count = get_customer_list_count($o_main, "all_cases", $sublist_filter, $filters);
// $canceled_count = get_customer_list_count($o_main, "cases_canceled", $sublist_filter, $filters);
// $missing_transaction_count = get_customer_list_count($o_main, "missing_transaction", $sublist_filter, $filters);

// $invoicesOnReminderLevelCount = get_customer_list_count($o_main, "cases_on_reminderlevel", $sublist_filter, $filters);

// $itemCount = $filteredCount = get_customer_list_count2($o_main, $list_filter, $sublist_filter, $filters);

if(isset($_POST['page'])) {
	$page = $_POST['page'];
}
if(intval($page) == 0){
	$page = 1;
}
$rowOnly = $_POST['rowOnly'];
$perPage = 100;
if($list_filter == 2){
	$perPage = 500;
}
$showing = $page * $perPage;
$showMore = false;
$currentCount = $itemCount;

if($showing < $currentCount){
	$showMore = true;
}
$totalPages = ceil($currentCount/$perPage);
$customerList = get_customer_list($o_main, $list_filter, $sublist_filter, $filters, $page, $perPage);

$_SESSION['listpagePerPage'] = $perPage;
$_SESSION['listpagePage'] = $page;

$s_sql = "SELECT * FROM accountinfo";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_accountinfo = $o_query->row_array();
}
$statusArray = array($formText_Active_output, $formText_Finished_output, $formText_Objection_output, $formText_Canceled_output, $formText_Inactive_output);
$closed_reasons = array($formText_FullyPaid_output, $formText_PayedWithLessAmountForgiven_output, $formText_ClosedWithoutAnyPayment_output,$formText_ClosedWithPartlyPayment_output,$formText_CreditedByCreditor_output,$formText_DrawnByCreditorToDeleteFees_output);

?>
<?php if (!$rowOnly) { ?>
	<?php if(!$_POST['updateOnlyList']){?>
		<?php include(__DIR__."/list_filter.php"); ?>
	<?php } ?>
	<?php if(!$_POST['updateOnlyList']){?>
	<div class="resultTableWrapper">
	<?php } ?>
	<form class="approve_form" action="">
	<div class="gtable" id="gtable_search" style="table-layout: fixed;">
	    <div class="gtable_row">
            <?php if($list_filter == 2) { ?>
				<div class="gtable_cell gtable_cell_head">
					<input type="checkbox" value="1" class="select_all" id="select_all_for_report" /><br/><label for="select_all_for_report"><?php echo $formText_SelectAll_output;?></label>
				</div>
                <div class="gtable_cell gtable_cell_head orderBy" data-orderfield="stopped_date" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
                    <?php echo $formText_StoppedDate_output;?>
                    <div class="ordering">
                        <div class="fas fa-caret-up" <?php if($order_field == "stopped_date" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
                        <div class="fas fa-caret-down" <?php if($order_field == "stopped_date" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
                    </div>
					<br/>
					<?php echo $formText_Debitor_output;?><br/><?php echo $formText_Creditor_output;?>
                </div>
            <?php } ?>
	        <div class="gtable_cell gtable_cell_head c1"><?php echo $formText_CaseNumber_output;?></div>
			<?php if($list_filter == 2) { ?>
		        <div class="gtable_cell gtable_cell_head"><?php echo $formText_FeesCalculated_output;?></div>
		        <div class="gtable_cell gtable_cell_head"><?php echo $formText_FeesPaid_output;?></div>
		        <div class="gtable_cell gtable_cell_head"><?php echo $formText_InterestCalculated_output;?></div>
		        <div class="gtable_cell gtable_cell_head"><?php echo $formText_InterestPaid_output;?></div>
		        <div class="gtable_cell gtable_cell_head"><?php echo $formText_FeesForgiven_output;?></div>
		        <div class="gtable_cell gtable_cell_head"><?php echo $formText_LinkedTransactions_output;?></div>
		        <div class="gtable_cell gtable_cell_head"><?php echo $formText_ConnectedTransactions_output;?></div>
		        <div class="gtable_cell gtable_cell_head"><?php echo $formText_RemindersCreated_output;?></div>
		        <div class="gtable_cell gtable_cell_head"><?php echo $formText_TotalSum_output;?></div>
		        <div class="gtable_cell gtable_cell_head"><?php echo $formText_TotalPayment_output;?></div>
			<?php } else { ?>
		        <div class="gtable_cell gtable_cell_head"><?php echo $formText_Debitor_output;?><br/><?php echo $formText_Creditor_output;?></div>
				<?php if($list_filter == "cases_transferred") { ?>
			        <div class="gtable_cell gtable_cell_head"><?php echo $formText_CollectingCompanyCase_output;?></div>
			        <div class="gtable_cell gtable_cell_head"><?php echo $formText_MainClaim_output;?></div>
			        <div class="gtable_cell gtable_cell_head"><?php echo $formText_Balance_output;?></div>
			        <div class="gtable_cell gtable_cell_head"><?php echo $formText_Status_output;?></div>
				<?php } else { ?>
			        <div class="gtable_cell gtable_cell_head"><?php echo $formText_CreditorRef_output;?></div>
			        <div class="gtable_cell gtable_cell_head"><?php echo $formText_OriginalDueDate_output;?></div>
				<?php } ?>
			<?php } ?>
	    </div>
<?php } ?>
    <?php

	$all_creditor_transactions = array();

    foreach($customerList as $v_row){
		if($list_filter == 2) {
			$totalPayments = "0";
			$totalOnlyPayments = 0;
			$all_linked_transactions = array();
			$all_linked_not_to_invoice_transactions = array();
			$all_not_linked_transactions = array();
			$all_payments = array();
			$claim_transactions = array();
			$linked_claim_transactions = array();

			// if(!isset($all_creditor_transactions[$v_row['creditor_id']])){
			// 	$s_sql = "SELECT * FROM creditor_transactions WHERE creditor_id = ? ORDER BY created DESC";
			// 	$o_query = $o_main->db->query($s_sql, array($v_row['creditor_id']));
			// 	$all_creditor_transactions[$v_row['creditor_id']] = ($o_query ? $o_query->result_array() : array());
			// }
			// foreach($all_creditor_transactions[$v_row['creditor_id']] as $all_creditor_transaction) {
			// 	if($all_creditor_transaction['link_id'] == $v_row['link_id'] && $all_creditor_transaction['invoice_nr'] == $v_row['invoice_nr']) {
			// 		$all_linked_transactions[] = $all_creditor_transaction;
			// 	}
			// 	if($all_creditor_transaction['link_id'] == $v_row['link_id'] && $all_creditor_transaction['invoice_nr'] != $v_row['invoice_nr']) {
			// 		$all_linked_not_to_invoice_transactions[] = $all_creditor_transaction;
			// 	}
			// 	if($all_creditor_transaction['link_id'] != $v_row['link_id'] && $all_creditor_transaction['invoice_nr'] == $v_row['invoice_nr']) {
			// 		$all_not_linked_transactions[] = $all_creditor_transaction;
			// 	}


			// 	if(($all_creditor_transaction['system_type'] == 'Payment' || $all_creditor_transaction['system_type'] == 'CreditnoteCustomer')
			// 	&& $all_creditor_transaction['link_id'] == $v_row['link_id'] && intval($all_creditor_transaction['collectingcase_id']) == 0) {
			// 		$all_payments[] = $all_creditor_transaction;
			// 	}

			// 	if(($all_creditor_transaction['system_type'] == 'InvoiceCustomer')
			// 	&& $all_creditor_transaction['link_id'] == $v_row['link_id'] && $all_creditor_transaction['invoice_nr'] == $v_row['invoice_nr']
			// 	 && intval($all_creditor_transaction['collectingcase_id']) == 0 && strpos($all_creditor_transaction['comment'], "_") != false) {
			// 		$claim_transactions[] = $all_creditor_transaction;
			// 	}

			// 	if(($all_creditor_transaction['system_type'] == 'InvoiceCustomer')
			// 	&& $all_creditor_transaction['link_id'] == $v_row['link_id'] && $all_creditor_transaction['invoice_nr'] != $v_row['invoice_nr']) {
			// 		$linked_claim_transactions[] = $all_creditor_transaction;
			// 	}

			// }

			if($v_row['link_id'] > 0){
				$s_sql = "SELECT * FROM creditor_transactions WHERE link_id = ? AND invoice_nr = ?  AND creditor_id = ?  ORDER BY created DESC";
				$o_query = $o_main->db->query($s_sql, array($v_row['link_id'], $v_row['invoice_nr'],  $v_row['creditor_id']));
				$all_linked_transactions = ($o_query ? $o_query->result_array() : array());

				$s_sql = "SELECT * FROM creditor_transactions WHERE link_id = ? AND invoice_nr <> ?  AND creditor_id = ?  ORDER BY created DESC";
				$o_query = $o_main->db->query($s_sql, array($v_row['link_id'], $v_row['invoice_nr'],  $v_row['creditor_id']));
				$all_linked_not_to_invoice_transactions = ($o_query ? $o_query->result_array() : array());
				
				$s_sql = "SELECT * FROM creditor_transactions WHERE IFNULL(link_id, 0) <> ? AND invoice_nr = ?  AND creditor_id = ?  ORDER BY created DESC";
				$o_query = $o_main->db->query($s_sql, array($v_row['link_id'], $v_row['invoice_nr'],  $v_row['creditor_id']));
				$all_not_linked_transactions = ($o_query ? $o_query->result_array() : array());
				
				$s_sql = "SELECT * FROM creditor_transactions WHERE (system_type='Payment' OR system_type='CreditnoteCustomer') AND link_id = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0)  ORDER BY created DESC";
				$o_query = $o_main->db->query($s_sql, array($v_row['link_id'], $v_row['creditor_id']));
				$all_payments = ($o_query ? $o_query->result_array() : array());
				
				$s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND link_id = ? AND invoice_nr = ?  AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%\_%' ORDER BY created DESC";
				$o_query = $o_main->db->query($s_sql, array($v_row['link_id'], $v_row['invoice_nr'],  $v_row['creditor_id']));
				$claim_transactions = ($o_query ? $o_query->result_array() : array());
				
				$s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND link_id = ? AND invoice_nr <> ?  AND creditor_id = ? ORDER BY created DESC";
				$o_query = $o_main->db->query($s_sql, array($v_row['link_id'], $v_row['invoice_nr'],  $v_row['creditor_id']));
				$linked_claim_transactions = ($o_query ? $o_query->result_array() : array());
			}
			$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE case_id = ?";
			$o_query = $o_main->db->query($s_sql, array($v_row['id']));
			$numberOfReminders = ($o_query ? $o_query->num_rows() : 0);
			foreach($all_payments as $invoice_payment) {
				$totalPayments+=$invoice_payment['amount'];
				if($invoice_payment['system_type'] == "Payment"){
					$totalOnlyPayments+=$invoice_payment['amount'];
				}
			}
			$interest_fee = 0;
			$reminder_fee = 0;

			$chargedInterest = 0;
			$chargedFee = 0;
			$fees_forgiven = 0;

			foreach($claim_transactions as $claim_transaction) {
				$commentArray = explode("_", $claim_transaction['comment']);
				if($commentArray[2] == "interest"){
				   $transactionType = "interest";
				} else if($commentArray[2] == "reminderFee"){
				  $transactionType = "reminderFee";
				} else if($commentArray[0] == "Rente"){
					$transactionType = "interest";
				} else {
					$transactionType = "reminderFee";
				}
				if($transactionType == "interest") {
					$chargedInterest += $claim_transaction['amount'];
				} else if($transactionType == "reminderFee"){
					$chargedFee += $claim_transaction['amount'];
				}
			}

			$original_amount = $v_row['amount'];
			foreach($linked_claim_transactions as $linked_claim_transaction) {
				$original_amount += $linked_claim_transaction['amount'];
			}

			foreach($all_payments as $all_payment) {
				$original_amount += $all_payment['amount'];
			}
			$overpaidOriginalAmount = 0;
			if($original_amount < 0){
				$overpaidOriginalAmount = $original_amount*-1;
			}
			if($overpaidOriginalAmount > 0){
				if($overpaidOriginalAmount >= $chargedFee) {
					$reminder_fee = $chargedFee;
					$overpaidOriginalAmount -= $chargedFee;
				} else {
					$reminder_fee = $overpaidOriginalAmount;
					$overpaidOriginalAmount = 0;
				}

				if($overpaidOriginalAmount >= $chargedInterest) {
					$interest_fee = $chargedInterest;
					$overpaidOriginalAmount -= $chargedInterest;
				} else {
					$interest_fee = $overpaidOriginalAmount;
					$overpaidOriginalAmount = 0;
				}
			}
		}

        $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['id'];


		?>
		<?php
		$background_linked = "";
		$showRow = true;
		if($list_filter == 2) {
			$makeFeesZero = false;
			foreach($all_linked_transactions as $all_linked_transaction) {
				if($all_linked_transaction['system_type'] =="CreditnoteCustomer" || $all_linked_transaction['system_type'] == "Disbursment") {
					$background_linked = "differentBackgroundColor";
				}
			}
			foreach($all_linked_not_to_invoice_transactions as $all_linked_transaction) {
				if($all_linked_transaction['system_type'] =="CreditnoteCustomer" || $all_linked_transaction['system_type'] == "Disbursment") {
					$background_linked = "differentBackgroundColor";
				}
			}
			if($background_linked == "differentBackgroundColor"){
				if(abs($totalOnlyPayments) == $v_row['amount'] && $v_row['amount'] > 0){
					$background_linked = "differentBackgroundColor3";
					$makeFeesZero = true;
				}
			}

			if($_GET['hide_with_issues'] && $background_linked == "differentBackgroundColor"){
				$showRow = false;
			}
			if($reminder_fee <= 0 && $interest_fee <= 0){
				$background_linked .= " differentBackgroundColor2";
			}
		}
		if($showRow){
		?>
        <div class="gtable_row output-click-helper <?php echo $background_linked;?>" data-href="<?php echo $s_edit_link;?>">
        <?php
      	// Show default columns
      	 ?>
             <?php if($list_filter == 2) { ?>
				 <div class="gtable_cell">
					 <input type="checkbox" value="<?php echo $v_row['id']?>" name="approve_for_report[]" class="approve_for_report" autocomplete="off"/>
				 </div>
                 <div class="gtable_cell">
                     <?php
                     echo date("d.m.Y H:i:s", strtotime($v_row['stopped_date']));
                     ?>
					 <br/>
					 <?php echo $v_row['debitorName'];?><br/><?php echo $v_row['debitorCountry'];?><br/><?php echo $v_row['creditorName'];?>
                 </div>
             <?php } ?>
	        <div class="gtable_cell c1"><?php echo $v_row['id'];?></div>
			<?php if($list_filter == 2) { ?>
				<div class="gtable_cell"><?php echo number_format($reminder_fee, 2, ",", " ");?></div>
				<div class="gtable_cell"><?php if($sublist_filter == "not_approved") {?><input type="text" class="fees_input" value="<?php if($makeFeesZero){ echo 0; } else { echo number_format($reminder_fee, 2, ",", "");} ?>" autocomplete="off" name="payed_fee_amount[<?php echo $v_row['id'];?>]"/><?php  } else { echo number_format($v_row['payed_fee_amount'], 2, ",", " "); } ?></div>
				<div class="gtable_cell"><?php echo number_format($interest_fee, 2, ",", " ");?></div>
				<div class="gtable_cell"><?php if($sublist_filter == "not_approved") { ?><input type="text" class="fees_input" value="<?php if($makeFeesZero){ echo 0; } else { echo number_format($interest_fee, 2, ",", "");}?>" autocomplete="off" name="payed_interest_amount[<?php echo $v_row['id'];?>]"/> <?php } else { echo number_format($v_row['payed_interest_amount'], 2, ",", " "); }?></div>
				<div class="gtable_cell">
					<?php
					$s_sql = "SELECT cccl.* FROM collecting_cases_claim_letter cccl
					WHERE (IFNULL(cccl.fees_status, 0) = 0 OR IFNULL(cccl.fees_status, 0) = 2)
					AND cccl.case_id = '".$o_main->db->escape_str($v_row['id'])."'";
					$o_query = $o_main->db->query($s_sql);
					$created_claimletters = $o_query ? $o_query->result_array() : array();

					$fees_forgiven = 0;
					if(count($created_claimletters) > 0) {
						if(($reminder_fee+$interest_fee) == 0) {
							$fees_forgiven = 1;
						}
					}
					if($fees_forgiven){
						echo $formText_Yes_output;
					} else {
						echo $formText_No_output;
					}
					?>
				</div>
				<div class="gtable_cell">
					<?php echo count($all_linked_transactions);?>

					<?php if(count($all_linked_transactions) > 0){ ?>
					<span class="glyphicon glyphicon-info-sign hoverEye">
						<div class="hoverInfo hoverInfoBig hoverInfoFull">
							<table class="table smallTable">
								<?php
								foreach($all_linked_transactions as $transaction_fee) {
									$claim_text_array = explode("_", $transaction_fee['comment']);
									?>
									<tr>
										<td><b><?php echo $transaction_fee['system_type'];?></b></td>
										<td><?php if(count($claim_text_array) > 1) { echo $claim_text_array[0]." "; } echo $formText_InvoiceNr_output.": ".$transaction_fee['invoice_nr'];?></td>
										<td><?php echo $currencyName.number_format($transaction_fee['amount'], 2, ",", " ");?></td>
										<td><?php if($transaction_fee['open']) { echo $formText_Open_output;} else { echo $formText_Closed_output;}?></td>
									</tr>
									<?php
								}
								?>
							</table>
						</div>
					</span>
					<?php } ?>
					<?php if(count($all_linked_not_to_invoice_transactions) > 0){ ?>
						<br/>
					<?php
					echo count($all_linked_not_to_invoice_transactions); ?>
					<span class="glyphicon glyphicon-info-sign hoverEye">
						<div class="hoverInfo hoverInfoBig hoverInfoFull">
							<table class="table smallTable">
								<?php
								foreach($all_linked_not_to_invoice_transactions as $transaction_fee) {
									$claim_text_array = explode("_", $transaction_fee['comment']);
									?>
									<tr>
										<td><b><?php echo $transaction_fee['system_type'];?></b></td>
										<td><?php if(count($claim_text_array) > 1) { echo $claim_text_array[0]." "; } echo $formText_InvoiceNr_output.": ".$transaction_fee['invoice_nr'];?></td>
										<td><?php echo $currencyName.number_format($transaction_fee['amount'], 2, ",", " ");?></td>
										<td><?php if($transaction_fee['open']) { echo $formText_Open_output;} else { echo $formText_Closed_output;}?></td>
									</tr>
									<?php
								}
								?>
							</table>
						</div>
					</span>
					<?php } ?>
				</div>
				<div class="gtable_cell">

					<?php echo count($all_not_linked_transactions);?>
					<?php if(count($all_not_linked_transactions) > 0){?>
					<span class="glyphicon glyphicon-info-sign hoverEye">
						<div class="hoverInfo hoverInfoBig hoverInfoFull">
							<table class="table smallTable">
								<?php
								if(count($all_not_linked_transactions) > 0){ ?>
									<?php
									foreach($all_not_linked_transactions as $transaction_fee) {
										$claim_text_array = explode("_", $transaction_fee['comment']);
										?>
										<tr>
											<td><b><?php echo $transaction_fee['system_type'];?></b></td>
											<td><?php if(count($claim_text_array) > 1) { echo $claim_text_array[0]." "; } echo $formText_InvoiceNr_output.": ".$transaction_fee['invoice_nr'];?></td>
											<td><?php echo $currencyName.number_format($transaction_fee['amount'], 2, ",", " ");?></td>
											<td><?php if($transaction_fee['open']) { echo $formText_Open_output;} else { echo $formText_Closed_output;}?></td>
										</tr>
										<?php
									}
								}
								?>
							</table>
						</div>
					</span>
					<?php } ?>
				</div>
				<div class="gtable_cell"><?php echo $numberOfReminders;?></div>
				<div class="gtable_cell"><?php echo number_format($v_row['amount'], 2, ",", " ");?></div>
				<div class="gtable_cell"><?php echo number_format($totalPayments, 2, ",", " ");?></div>
			<?php } else { ?>
				<div class="gtable_cell"><?php echo $v_row['debitorName'];?><br/><?php echo $v_row['debitorCountry'];?><br/><?php echo $v_row['creditorName'];?></div>
				<?php if($list_filter == "cases_transferred") {

					$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCases&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['collecting_company_case_id'];
					$s_sql = "SELECT collecting_company_cases_claim_lines.*, collecting_cases_claim_line_type_basisconfig.make_to_appear_in_consider_tab FROM collecting_company_cases_claim_lines
					JOIN collecting_cases_claim_line_type_basisconfig ON collecting_cases_claim_line_type_basisconfig.id = collecting_company_cases_claim_lines.claim_type
					WHERE collecting_company_cases_claim_lines.content_status < 2
					AND collecting_company_cases_claim_lines.collecting_company_case_id = ?  AND IFNULL(collecting_cases_claim_line_type_basisconfig.not_include_in_claim, 0) = 0
					ORDER BY collecting_company_cases_claim_lines.claim_type ASC, collecting_company_cases_claim_lines.created DESC";
					$o_query = $o_main->db->query($s_sql, array($v_row['collecting_company_case_id']));
					$claims = ($o_query ? $o_query->result_array() : array());

					$s_sql = "SELECT * FROM cs_mainbook_voucher WHERE case_id = ? ORDER BY created DESC";
					$o_query = $o_main->db->query($s_sql, array($v_row['collecting_company_case_id']));
					$payments = ($o_query ? $o_query->result_array() : array());

					$balance = 0;
					$checksum = 0;
					$ledgerChecksum = 0;
					$forgivenChecksum = 0;

					$hasConsideration = false;
					foreach($claims as $claim) {
						if(!$claim['payment_after_closed']) {
							$balance += $claim['amount'];
							$checksum += $claim['amount'];
						}
						if($claim['claim_type'] == 1 || $claim['claim_type'] == 16 || ($claim['claim_type'] == 15 && !$claim['payment_after_closed'])){
							$forgivenChecksum += $claim['amount'];
						}
						if($claim['make_to_appear_in_consider_tab']) {
							$hasConsideration = true;
						}
					}

					foreach($payments as $payment) {
						$s_sql = "SELECT cmt.* FROM cs_mainbook_transaction cmt WHERE cmt.bookaccount_id = '1' AND cmt.cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."'";
						$o_query = $o_main->db->query($s_sql);
						$transactions = ($o_query ? $o_query->result_array() : array());
						foreach($transactions as $transaction) {
							$balance -= $transaction['amount'];
							$checksum -= $transaction['amount'];
							$ledgerChecksum+= $transaction['amount'];
						}
						$s_sql = "SELECT cmt.* FROM cs_mainbook_transaction cmt WHERE (cmt.bookaccount_id = '15' OR cmt.bookaccount_id = '16' OR cmt.bookaccount_id = '22') AND cmt.cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."'";
						$o_query = $o_main->db->query($s_sql);
						$ledger_transactions = ($o_query ? $o_query->result_array() : array());
						foreach($ledger_transactions as $transaction) {
							$ledgerChecksum+= $transaction['amount'];
						}
						$s_sql = "SELECT cmt.* FROM cs_mainbook_transaction cmt WHERE (cmt.bookaccount_id = '20' OR cmt.bookaccount_id = 19) AND cmt.cs_mainbook_voucher_id = '".$o_main->db->escape_str($payment['id'])."'";
						$o_query = $o_main->db->query($s_sql);
						$ledger_transactions = ($o_query ? $o_query->result_array() : array());
						foreach($ledger_transactions as $transaction) {
							$forgivenChecksum+= $transaction['amount'];
						}
					}
					$forgivenChecksum -= $v_row['forgivenAmountOnMainClaim'];
					$checksum -= $v_row['forgivenAmountOnMainClaim'];
					$checksum -= $v_row['forgivenAmountExceptMainClaim'];
					$checksum += $v_row['overpaidAmount'];

					?>
					<div class="gtable_cell"><a href="<?php echo $s_list_link?>" target="_blank"><?php echo $v_row['collecting_company_case_id']?></a></div>
					<div class="gtable_cell"><?php echo number_format($v_row['original_main_claim'], 2, ",", " ");?></div>
					<div class="gtable_cell"><?php echo number_format($balance, 2, ",", " ");?></div>
					<div class="gtable_cell">
						<?php
						if($v_row['nextStepDate'] != "") echo date("d.m.Y", strtotime($v_row['nextStepDate']))."<br/>";
						echo $v_row['nextStepName']."<br/>";

						if($v_row['collecting_case_surveillance_date'] != '0000-00-00' && $v_row['collecting_case_surveillance_date'] != '') {
							if(($v_row['case_closed_date'] == "0000-00-00" OR $v_row['case_closed_date'] == "")){
								echo $formText_Surveillance_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($v_row['collecting_case_surveillance_date'])).")";
							} else {
								echo $formText_ClosedInSurveillance_output;
							}
						} else if($v_row['collecting_case_manual_process_date'] != '0000-00-00' && $v_row['collecting_case_manual_process_date'] != ''){
							if(($v_row['case_closed_date'] == "0000-00-00" OR $v_row['case_closed_date'] == "")){
								echo $formText_ManualProcess_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($v_row['collecting_case_manual_process_date'])).")";
							} else {
								echo $formText_ClosedInManualProcess_output;
							}
						} else if($v_row['collecting_case_created_date'] != '0000-00-00' && $v_row['collecting_case_created_date'] != ''){
							if(($v_row['case_closed_date'] == "0000-00-00" OR $v_row['case_closed_date'] == "")){
								echo $formText_CollectingLevel_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($v_row['collecting_case_created_date'])).")";
							} else {
								echo $formText_ClosedInCollectingLevel_output;
							}
						} else if($v_row['warning_case_created_date'] != '0000-00-00' && $v_row['warning_case_created_date'] != '') {
							if(($v_row['case_closed_date'] == "0000-00-00" OR $v_row['case_closed_date'] == "")){
								echo $formText_WarningLevel_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($v_row['warning_case_created_date'])).")";
							} else {
								echo $formText_ClosedInWarningLevel_output;
							}
						}
						if(($v_row['case_closed_date'] != "0000-00-00" AND $v_row['case_closed_date'] != "")){
							if($v_row['case_closed_reason'] >= 0){
								echo "<br/>".$closed_reasons[$v_row['case_closed_reason']];
							}
						}
						if($v_row['forgivenAmountOnMainClaim'] != 0) {
							echo "<br/>".$formText_ForgivenAmountOnMainClaim_output." ".number_format($v_row['forgivenAmountOnMainClaim'], 2, ",", "");
						}
						if($v_row['forgivenAmountExceptMainClaim'] != 0) {
							echo "<br/>".$formText_ForgivenAmountExceptMainClaim_output." ".number_format($v_row['forgivenAmountExceptMainClaim'], 2, ",", "");
						}
						if($v_row['overpaidAmount'] != 0) {
							echo "<br/>".$formText_OverpaidAmount_output." ".number_format($v_row['overpaidAmount'], 2, ",", "");
						}
					?>
					</div>
				<?php } else { ?>
				    <div class="gtable_cell"><?php echo $v_row['creditor_ref'];?></div>
			        <div class="gtable_cell"><?php if($v_row['original_due_date'] != "0000-00-00" && $v_row['original_due_date'] != ""){ echo date("d.m.Y", strtotime($v_row['original_due_date'])); }?></div>
				<?php } ?>
			<?php } ?>
        </div>
    <?php } 
	}
	?>
	<?php if (!$rowOnly) { ?>
		</div>
	</form>
		<?php if($totalPages > 1) {
			$currentPage = $page;
			$pages = array();
			array_push($pages, 1);
			if(!in_array($currentPage, $pages)){
				array_push($pages, $currentPage);
			}
			if(!in_array($totalPages, $pages)){
				array_push($pages, $totalPages);
			}
			for ($y = 10; $y <= $totalPages; $y+=10){
				if(!in_array($y, $pages)){
					array_push($pages, $y);
				}
			}
			for($x = 1; $x <= 3;$x++){
				$prevPage = $page - $x;
				$nextPage = $page + $x;
				if($prevPage > 0){
					if(!in_array($prevPage, $pages)){
						array_push($pages, $prevPage);
					}
				}
				if($nextPage <= $totalPages){
					if(!in_array($nextPage, $pages)){
						array_push($pages, $nextPage);
					}
				}
			}echo '<!--section-->';
			asort($pages);
			?>
			<?php foreach($pages as $page) {?>
				<a href="#" data-page="<?php echo $page?>" class="page-link"><?php echo $page;?></a>
			<?php } ?>
			<?php /*
		    <div class="showMoreCustomers"><?php echo $formText_Showing_Output;?> <span class="showing"><?php echo $showing;?></span> <?php echo $formText_Of_output." ".$currentCount;?> <a href="#" class="showMoreCustomersBtn"><?php echo $formText_ShowMore_output;?></a> </div>*/?>
	<?php } ?>
	<?php if(!$_POST['updateOnlyList']){ ?>

		<?php if($sublist_filter == "not_approved") {?>
			<div class="approve_cases"><?php echo $formText_Approve_output;?></div>
		<?php } else if($sublist_filter == "approved") { ?>
			<div class="unapprove_cases"><?php echo $formText_Unapprove_output;?></div>
		<?php } ?>
	</div>
	<?php } ?>
	<style>
	.fees_input {
		width: 50px;
		border-radius: 3px;
		border: 1px solid #e5e5e5;
	}
	.approve_cases {
		position: fixed;
		bottom: 20px;
		right: 20%;
		z-index: 10;
		float: right;
		color: #fff;
		padding: 10px 35px;
		font-size: 14px;
		background: #0b9b32;
		border-radius: 3px;
		text-align: center;
		cursor: pointer;
		font-weight: bold;
		margin-top: 10px;
	}
	.unapprove_cases {
		position: fixed;
		bottom: 20px;
		right: 20%;
		z-index: 10;
		float: right;
		color: #fff;
		padding: 10px 35px;
		font-size: 14px;
		background: #0b9b32;
		border-radius: 3px;
		text-align: center;
		cursor: pointer;
		font-weight: bold;
		margin-top: 10px;
	}
	.differentBackgroundColor .gtable_cell {
		background: #FFBD2B;
	}
	.differentBackgroundColor2 .gtable_cell {
		background: #e2e2e2;
	}
	.differentBackgroundColor3 .gtable_cell {
		background: #6CB4EE;
	}
	</style>
<script type="text/javascript">
	var out_popup;
	var out_popup_options={
		follow: [true, false],
		modalClose: false,
		escClose: false,
		closeClass:'b-close',
		onOpen: function(){
			$(this).addClass('opened');
			//$(this).find('.b-close').on('click', function(){out_popup.close();});
		},
		onClose: function(){
			$(this).removeClass('opened');
		}
	};
	$(function() {
		$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
			if(e.target.nodeName == 'DIV') fw_load_ajax($(this).data('href'),'',true);
		});
		$("#select_all_for_report").off("click").on("click", function(){
			if($(this).is(":checked")) {
				$(".approve_for_report").prop("checked", true);
			} else {
				$(".approve_for_report").prop("checked", false);
			}
		})

		$(".approve_cases").off("click").on("click", function(e){
			e.preventDefault();
			var formdata = $(".approve_form").serializeArray();
			var data = {};
			$(formdata).each(function(index, obj){
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
			ajaxCall('approve_cases', data, function(json) {
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(json.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				out_popup.addClass("close-reload");
				$("#popupeditbox:not(.opened)").remove();
			});
		})
		$(".unapprove_cases").off("click").on("click", function(e){
			e.preventDefault();
			var formdata = $(".approve_form").serializeArray();
			var data = {};
			$(formdata).each(function(index, obj){
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
			ajaxCall('unapprove_cases', data, function(json) {
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(json.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				out_popup.addClass("close-reload");
				$("#popupeditbox:not(.opened)").remove();
			});
		})
	});

    $(".page-link").on('click', function(e) {
	    page = $(this).data("page");
	    e.preventDefault();
	    var data = {
	        building_filter:$(".buildingFilter").val(),
	        customergroup_filter: $(".customerGroupFilter").val(),
	        list_filter: '<?php echo $list_filter; ?>',
	        search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
            date_from_filter: $(".dateFrom").val(),
            date_to_filter: $(".dateTo").val(),
			hide_with_issues: "<?php echo $_GET['hide_with_issues'];?>",
	        page: page
	    };
	    ajaxCall('list', data, function(json) {
	        $('.p_pageContent').html(json.html);
	        if(json.html.replace(" ", "") == ""){
	            $(".showMoreCustomersBtn").hide();
	        }

	    });
    });
    $('.showMoreCustomersBtn').on('click', function(e) {
        page = parseInt(page)+1;
        e.preventDefault();
        var data = {
            building_filter: $(".buildingFilter").val(),
            customergroup_filter: $(".customerGroupFilter").val(),
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
            page: page,
            rowOnly: 1
        };
        ajaxCall('list', data, function(json) {
            $('.p_pageContent .gtable').append(json.html);
            $(".showMoreCustomers .showing").html($(".gtable .gtable_row.output-click-helper").length);
            if(json.html.replace(" ", "") == ""){
                $(".showMoreCustomersBtn").hide();
            }
        });
    });
    $(".orderBy").off("click").on("click", function(){
        var order_field = $(this).data("orderfield");
        var order_direction = $(this).data("orderdirection");

        var data = {
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
            order_field: order_field,
            order_direction: order_direction
        }
        loadView("list", data);
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
	)
</script>
<?php } ?>
