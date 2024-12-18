<?php
$page = 1;
require_once __DIR__ . '/list_btn.php';


$s_sql = "SELECT * FROM case_worklist ORDER BY name ASC";
$o_query = $o_main->db->query($s_sql, array());
$worklists = ($o_query ? $o_query->result_array() : array());
$default_mainlist = "worklist";

$mainlist_filter = $_GET['mainlist_filter'] ? ($_GET['mainlist_filter']) : $default_mainlist;
if($mainlist_filter == "worklist"){
	$default_list = $worklists[0]['id'];
} else if($mainlist_filter == "paused"){
	$default_list = "0";
} else if($mainlist_filter == "incoming_messages"){
	$default_list = "not_handled";
}
$list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : $default_list;
$objection_status = $_GET['objection_status'] ?  ($_GET['objection_status']) : 0;
$dateFrom = $_GET['dateFrom'] ?  ($_GET['dateFrom']) : date("01.m.Y", strtotime("-1month"));
$dateTo = $_GET['dateTo'] ?  ($_GET['dateTo']) : date("t.m.Y", strtotime("-1month"));
$creditor_id = $_GET['creditor_id_filter']?($_GET['creditor_id_filter']) :0;
if($creditor_id > 0){
	$s_sql = "SELECT * FROM creditor WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($creditor_id));
	$creditor_filter = ($o_query ? $o_query->row_array() : array());
}
$_SESSION['list_filter'] = $list_filter;
$_SESSION['objection_status'] = $objection_status;
$_SESSION['dateFrom'] = $dateFrom;
$_SESSION['dateTo'] = $dateTo;
$_SESSION['creditor_id'] = $creditor_id;

$sql_join = " ";
$sql_init = "SELECT p.*, cred.companyname as creditorName, c2.name as debitorName,
	DATE_ADD(IFNULL(p.due_date, '2000-01-01'), INTERVAL IFNULL(nextStep.days_after_due_date, 0)+IF(step2.id > 0, 0,cred.days_overdue_startcase) DAY) as nextStepDate,
		IF(nextStep.id > 0, nextStep.name, '') as nextStepName, IF(nextStep.id > 0, nextStep.sending_action, '') as nextStepActionType, step2.name as processStepName, p.due_date as currentStepDate, nextStep.id as nextStepId,
		cwc.added_to_worklist_date, cwc.closed_date, cwc.reminder_date
		 FROM collecting_company_cases p
		 LEFT JOIN creditor cred ON cred.id = p.creditor_id
		 JOIN case_worklist_connection cwc ON cwc.collecting_company_case_id = p.id
		 LEFT JOIN customer c2 ON c2.id = p.debitor_id
		 LEFT JOIN collecting_cases_collecting_process_steps step2 ON step2.id = p.collecting_cases_process_step_id AND step2.collecting_cases_collecting_process_id = p.collecting_process_id
		 LEFT JOIN collecting_cases_collecting_process_steps nextStep ON nextStep.sortnr = (IFNULL(step2.sortnr, 0)+1) AND nextStep.collecting_cases_collecting_process_id = p.collecting_process_id
		".$sql_join."
		WHERE p.content_status < 2 AND DATE_ADD(IFNULL(p.due_date, '2000-01-01'), INTERVAL IFNULL(nextStep.days_after_due_date, 0) DAY) <= CURDATE() AND IFNULL(cwc.closed_date, '0000-00-00 00:00:00') = '0000-00-00 00:00:00'";


$sql_init_2 = "SELECT p.*, cred.companyname as creditorName, c2.name as debitorName,
	DATE_ADD(IFNULL(p.due_date, '2000-01-01'), INTERVAL IFNULL(nextStep.days_after_due_date, 0)+IF(step2.id > 0, 0,cred.days_overdue_startcase) DAY) as nextStepDate,
		IF(nextStep.id > 0, nextStep.name, '') as nextStepName, IF(nextStep.id > 0, nextStep.sending_action, '') as nextStepActionType, step2.name as processStepName, p.due_date as currentStepDate, nextStep.id as nextStepId,
		cccp.closed_date, cccp.id as objectionId,  cccp.message_handled_by, cccp.message_handled
		 FROM collecting_company_cases p
		 LEFT JOIN creditor cred ON cred.id = p.creditor_id
		 JOIN collecting_company_case_paused cccp ON cccp.collecting_company_case_id = p.id
		 LEFT JOIN customer c2 ON c2.id = p.debitor_id
		 LEFT JOIN collecting_cases_collecting_process_steps step2 ON step2.id = p.collecting_cases_process_step_id AND step2.collecting_cases_collecting_process_id = p.collecting_process_id
		 LEFT JOIN collecting_cases_collecting_process_steps nextStep ON nextStep.sortnr = (IFNULL(step2.sortnr, 0)+1) AND nextStep.collecting_cases_collecting_process_id = p.collecting_process_id
		WHERE p.content_status < 2";
$o_query = $o_main->db->query($sql_init);
$total_worklist_count = $o_query ? $o_query->num_rows() : 0;

$o_query = $o_main->db->query($sql_init_2);
$total_paused_count = $o_query ? $o_query->num_rows() : 0;


$o_query = $o_main->db->query($sql_init_2." AND cccp.incoming_from_portal = 1 AND IFNULL(cccp.message_handled, '0000-00-00 00:00:00') = '0000-00-00 00:00:00'");
$total_incoming_messages_count = $o_query ? $o_query->num_rows() : 0;


if($mainlist_filter == "worklist"){
	$sql_where = " AND cwc.case_worklist_id = ".$o_main->db->escape($list_filter);

	$sql = $sql_init.$sql_where;
    $o_query = $o_main->db->query($sql);
	$customerList = $o_query ? $o_query->result_array() :array();

	$worklist_count = array();
	foreach($worklists as $worklist) {
		$sql_where = " AND cwc.case_worklist_id = ".$o_main->db->escape($worklist['id']);
		$sql = $sql_init.$sql_where;
		$o_query = $o_main->db->query($sql);
		$worklist_count[$worklist['id']] =  $o_query ? $o_query->num_rows(): 0;
	}
} else if($mainlist_filter == "paused") {	
	$group_by = "";
	$sql_where = " AND cccp.pause_reason = ".$o_main->db->escape($list_filter);
	if($objection_status == 0){
		$sql_where .= " AND IFNULL(cccp.closed_date, '0000-00-00 00:00:00') = '0000-00-00 00:00:00'";
	} else if($objection_status == 1){
		$sql_where .= " AND IFNULL(cccp.closed_date, '0000-00-00 00:00:00') <> '0000-00-00 00:00:00'";
	} else {
		$sql_where .= " AND cccp.created_date >= '".date("Y-m-d", strtotime($dateFrom))."' AND cccp.created_date <= '".date("Y-m-d", strtotime($dateTo))."'";
		if($creditor_filter){
			$sql_where .= " AND cred.id = '".$o_main->db->escape_str($creditor_filter['id'])."'";
		}
	}
	$sql = $sql_init_2.$sql_where.$group_by;
    $o_query = $o_main->db->query($sql);
	$customerList = $o_query ? $o_query->result_array() :array();
	$in_selection_count = count($customerList);

	$paused_count = array();
	$pause_reasons = array(0,1,2,3,4,5,6,7,8);
	foreach($pause_reasons as $pause_reason) {
		$sql_where = " AND IFNULL(cccp.closed_date, '0000-00-00 00:00:00') = '0000-00-00 00:00:00' AND cccp.pause_reason = ".$o_main->db->escape($pause_reason);
		$sql = $sql_init_2.$sql_where.$group_by;
		$o_query = $o_main->db->query($sql);
		$paused_count[$pause_reason] =  $o_query ? $o_query->num_rows(): 0;
	}
} else if($mainlist_filter == "incoming_messages") {
	$sql_where = " AND cccp.incoming_from_portal = 1";
	$sql_order = " ORDER BY cccp.created DESC";
	if($list_filter == "handled"){
		$sql_where .= " AND IFNULL(cccp.message_handled, '0000-00-00 00:00:00') <> '0000-00-00 00:00:00'";
		$sql_order = " ORDER BY cccp.message_handled DESC";
	} else {
		$sql_where .= " AND IFNULL(cccp.message_handled, '0000-00-00 00:00:00') = '0000-00-00 00:00:00'";
	}
	// if($objection_status == 0){
	// 	$sql_where .= " AND IFNULL(cccp.closed_date, '0000-00-00 00:00:00') = '0000-00-00 00:00:00'";
	// } else if($objection_status == 1){
	// 	$sql_where .= " AND IFNULL(cccp.closed_date, '0000-00-00 00:00:00') <> '0000-00-00 00:00:00'";
	// } else {
	// 	$sql_where .= " AND cccp.created_date >= '".date("Y-m-d", strtotime($dateFrom))."' AND cccp.created_date <= '".date("Y-m-d", strtotime($dateTo))."'";
	// 	if($creditor_filter){
	// 		$sql_where .= " AND cred.id = '".$o_main->db->escape_str($creditor_filter['id'])."'";
	// 	}
	// }
	$o_query = $o_main->db->query($sql_init_2.$sql_where.$sql_order);
	$customerList = $o_query ? $o_query->result_array() :array();
	$in_selection_count = count($customerList);

	$incoming_messages_count = array();
	$sql = $sql_init_2." AND cccp.incoming_from_portal = 1 AND IFNULL(cccp.message_handled, '0000-00-00 00:00:00') = '0000-00-00 00:00:00'";

	$o_query = $o_main->db->query($sql);
	$incoming_messages_count["not_handled"] =  $o_query ? $o_query->num_rows(): 0;
	
	
	$sql = $sql_init_2." AND cccp.incoming_from_portal = 1 AND IFNULL(cccp.message_handled, '0000-00-00 00:00:00') <> '0000-00-00 00:00:00'";
	$o_query = $o_main->db->query($sql);
	$incoming_messages_count["handled"] =  $o_query ? $o_query->num_rows(): 0;

}
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
$_SESSION['mainlist_filter'] = $mainlist_filter;
$_SESSION['list_filter'] = $list_filter;
?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
				<div class="output-filter">
				    <ul>
						<li class="item<?php echo ($mainlist_filter == "worklist" ? ' active':'');?>">
							<a class="topFilterlink" data-mainlistfilter="all" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=worklist"; ?>">
								<span class="link_wrapper">
									<span class="count"><?php echo $total_worklist_count; ?></span>
									<?php echo $formText_Worklist_output;?>
								</span>
							</a>
						</li>
						<li class="item<?php echo ($mainlist_filter == "paused" ? ' active':'');?>">
							<a class="topFilterlink" data-mainlistfilter="paused" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=paused"; ?>">
								<span class="link_wrapper">
									<span class="count"><?php echo $total_paused_count; ?></span>
									<?php echo $formText_Paused_output;?>
								</span>
							</a>
						</li>
						<li class="item<?php echo ($mainlist_filter == "incoming_messages" ? ' active':'');?>">
							<a class="topFilterlink" data-mainlistfilter="incoming_messages" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=incoming_messages"; ?>">
								<span class="link_wrapper">
									<span class="count"><?php echo $total_incoming_messages_count; ?></span>
									<?php echo $formText_IncomingMessages_output;?>
								</span>
							</a>
						</li>
					</ul>
				</div>
				<?php if($mainlist_filter == "worklist") { ?>
					<div class="output-filter">
					    <ul>
							<?php foreach($worklists as $worklist) { ?>
								<li class="item<?php echo ($list_filter == $worklist['id'] ? ' active':'');?>">
									<a class="topFilterlink" data-listfilter="all" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list&mainlist_filter=worklist&list_filter=".$worklist['id']; ?>">
										<span class="link_wrapper">
											<span class="count"><?php echo $worklist_count[$worklist['id']]; ?></span>
											<?php echo $worklist['name'];?>
										</span>
									</a>
								</li>
							<?php } ?>
						</ul>
					</div>
				<?php } else if($mainlist_filter == "paused") { ?>
					<div class="output-filter">
					    <ul>
							<?php foreach($pause_reasons as $pause_reason) { ?>
								<li class="item<?php echo ($list_filter != "dispute" && $list_filter == $pause_reason ? ' active':'');?>">
									<a class="topFilterlink" data-listfilter="all" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list&mainlist_filter=paused&list_filter=".$pause_reason; ?>">
										<span class="link_wrapper">
											<span class="count"><?php echo $paused_count[$pause_reason]; ?></span>
											<?php echo $type_messages[$pause_reason];?>
										</span>
									</a>
								</li>
							<?php } ?>
						</ul>
					</div>
				<?php } else if($mainlist_filter == "incoming_messages") { ?>
					<div class="output-filter">
					    <ul>
							<li class="item<?php echo ($list_filter == "not_handled" ? ' active':'');?>">
								<a class="topFilterlink" data-listfilter="not_handled" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list&mainlist_filter=incoming_messages&list_filter=not_handled"; ?>">
									<span class="link_wrapper">
										<span class="count"><?php echo $incoming_messages_count["not_handled"]; ?></span>
										<?php echo $formText_NotHandled_output;?>
									</span>
								</a>
							</li>
							<li class="item<?php echo ($list_filter == "handled" ? ' active':'');?>">
								<a class="topFilterlink" data-listfilter="handled" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list&mainlist_filter=incoming_messages&list_filter=handled"; ?>">
									<span class="link_wrapper">
										<span class="count"><?php echo $incoming_messages_count["handled"]; ?></span>
										<?php echo $formText_Handled_output;?>
									</span>
								</a>
							</li>
						</ul>
					</div>
				<?php } ?>
				<?php 
				if($mainlist_filter != "incoming_messages") {
					?>
					<select class="objection_status">
						<option value="0"><?php echo $formText_Open_output;?></option>
						<option value="1"<?php if($objection_status == 1) echo 'selected';?>><?php echo $formText_Closed_output;?></option>
						<option value="2"<?php if($objection_status == 2) echo 'selected';?>><?php echo $formText_All_output;?></option>
					</select>
					<?php 
					if($objection_status == 2) {
						?>
						<input type="text" class="datepicker dateFrom" placeholder="<?php echo $formText_DateFrom_output;?>" value="<?php echo $dateFrom;?>"/> - <input type="text" class="datepicker dateTo" placeholder="<?php echo $formText_DateTo_output;?>" value="<?php echo $dateTo;?>"/> 
					
						<b><?php echo $formText_Creditor_output;?></b> <span class="select_creditor"><?php if($creditor_filter){ echo $creditor_filter['companyname']; } else { echo $formText_All_output; }?> </span> <?php if($creditor_filter) echo " <span class='reset_creditor'>".$formText_ResetCreditor_output."</span>";?><input type="hidden" id="creditorIdFilter" value="<?php echo $creditor_id;?>"/>
						<?php
					}
					?>
					<div class=""><span class=""><?php echo $in_selection_count?></span> <?php echo $formText_InSelection_output;?></div>
					
					<div class="addBtn export_list fw_text_link_color filterBtn"><?php echo $formText_ExportCurrentSelection_Output;?></div>
					<?php
				}
				?>
				<div class="gtable" id="gtable_search">
				    <div class="gtable_row">
						<?php if($mainlist_filter == "worklist") { ?>
							<div class="gtable_cell gtable_cell_head"><?php echo $formText_CreatedDate_output."<br/>".$formText_ClosedDate_output."<br/>".$formText_ReminderDate_output; ?></div>
						<?php } ?>
				        <div class="gtable_cell gtable_cell_head c1"><?php echo $formText_CaseNumber_output;?></div>
				        <div class="gtable_cell gtable_cell_head"><?php echo $formText_Debitor_output;?></div>
				        <div class="gtable_cell gtable_cell_head"><?php echo $formText_Creditor_output;?></div>
				        <div class="gtable_cell gtable_cell_head"><?php echo $formText_DueDate_output;?></div>
				        <div class="gtable_cell gtable_cell_head"><?php echo $formText_MainClaim_output;?></div>
				        <div class="gtable_cell gtable_cell_head"><?php echo $formText_Balance_output;?></div>
				        <div class="gtable_cell gtable_cell_head"><?php echo $formText_WillBeSentNow_output;?></div>
						<?php if($mainlist_filter =="incoming_messages") { ?>
				        <div class="gtable_cell gtable_cell_head"><?php echo $formText_Status_output;?></div>
				        <div class="gtable_cell gtable_cell_head"><?php echo $formText_Handle_output;?></div>
						<?php } else { ?>
				        <div class="gtable_cell gtable_cell_head"><?php echo $formText_Status_output;?></div>
						<?php } ?>
				    </div>
					<?php
					foreach($customerList as $v_row){
						$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCases&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['id']."&backToWorklist=1";

						$mainClaim = $v_row['original_main_claim'];

						$s_sql = "SELECT cccl.* FROM collecting_company_cases_claim_lines cccl
						LEFT OUTER JOIN collecting_cases_claim_line_type_basisconfig bconfig ON bconfig.id = cccl.claim_type
						WHERE cccl.content_status < 2 AND cccl.collecting_company_case_id = ? AND IFNULL(bconfig.not_include_in_claim, 0) = 0
						ORDER BY cccl.claim_type ASC, cccl.created DESC";
						$o_query = $o_main->db->query($s_sql, array($v_row['id']));
						$claims = ($o_query ? $o_query->result_array() : array());

						$s_sql = "SELECT * FROM collecting_cases_payments WHERE collecting_case_id = ? ORDER BY created DESC";
						$o_query = $o_main->db->query($s_sql, array($v_row['id']));
						$payments = ($o_query ? $o_query->result_array() : array());
						
						$balance = 0;

						foreach($claims as $claim) {
							if(!$claim['payment_after_closed']) {
								$balance += $claim['amount'];
							}
						}
						foreach($payments as $payment) {
							$balance -= $payment['amount'];
						}
						?>
						<div class="gtable_row output-click-helper" data-href="<?php echo $s_edit_link;?>">
						<?php
						// Show default columns
						 ?>
							 <?php if($mainlist_filter == "worklist") { ?>
	 							<div class="gtable_cell"><?php echo date("d.m.Y", strtotime($v_row['added_to_worklist_date']));
								if($v_row['closed_date'] != "" && $v_row['closed_date'] != "0000-00-00") {
									echo "<br/>".date("d.m.Y", strtotime($v_row['closed_date']));
								}
								if($v_row['reminder_date'] != "" && $v_row['reminder_date'] != "0000-00-00") {
									echo "<br/>".date("d.m.Y", strtotime($v_row['reminder_date']));
								}

								?></div>
	 						<?php } ?>
							<div class="gtable_cell c1"><?php echo $v_row['id'];?></div>
							<div class="gtable_cell"><?php echo $v_row['debitorName'];?></div>
							<div class="gtable_cell"><?php echo $v_row['creditorName'];?></div>
							<div class="gtable_cell"><?php if($v_row['due_date'] != "0000-00-00" && $v_row['due_date'] != ""){ echo date("d.m.Y", strtotime($v_row['due_date'])); }?></div>
							<div class="gtable_cell rightAlign"><?php echo number_format($mainClaim, 2, ",", " ");?></div>
							<div class="gtable_cell rightAlign">
								<?php echo number_format($balance, 2, ",", " ");?>

								<span class="glyphicon glyphicon-info-sign hoverEye">
									<div class="hoverInfo hoverInfo2 hoverInfoFull">
										<table class="table smallTable">
											<?php
											if(count($claims) > 0){ ?>
												<?php
												foreach($claims as $claim) {
													?>
													<tr>
														<td><b><?php echo $claim['name'];?></b></td>
														<td><?php if($claim['date'] != "0000-00-00" && $claim['date'] != "") echo date("d.m.Y", strtotime($claim['date']));?></td>
														<td><?php echo number_format($claim['amount'], 2, ",", " ");?></td>
													</tr>
													<?php
												}
											}
											if(count($payments) > 0){
											?>
												<?php
												foreach($payments as $payment) {
													?>
													<tr>
														<td><b><?php echo $formText_Payment_output;?></b></td>
														<td><?php if($payment['date'] != "0000-00-00" && $payment['date'] != "") echo date("d.m.Y", strtotime($payment['date']));?></td>
														<td><?php echo number_format($payment['amount'], 2, ",", " ");?></td>
													</tr>
													<?php
												}
											}
											?>
											<tr class="balance_row">
												<td><b><?php echo $formText_Balance_output;?></b></td>
												<td></td>
												<td><?php echo number_format($balance, 2, ",", " ");?></td>
											</tr>
										</table>
										<?php if(count($transaction_fees) > 0){ ?>
											<div class="resetTheCase" data-caseid="<?php echo $v_row['id'];?>"><?php echo $formText_ResetFees_output;?></div>
										<?php } ?>
										<?php
										if(count($transaction_payments) > 0){
											?>
											<div class="createRestNote" data-caseid="<?php echo $v_row['id'];?>"><?php echo $formText_CreateRestNote_output;?></div>
											<?php
										}
										?>
									</div>
								</span>
							</div>
							<div class="gtable_cell">
								<?php
								if($v_row['nextStepDate'] != "") echo date("d.m.Y", strtotime($v_row['nextStepDate']))."<br/>";

								echo $v_row['nextStepName'];
								?>
							</div>
							<?php 
							if($mainlist_filter =="incoming_messages") {
							?>
								<div class="gtable_cell">
									<?php
									if($v_row['closed_date'] != '0000-00-00' && $v_row['closed_date'] != '') {
										echo $formText_Closed_output;
									} else {
										echo $formText_Open_output;
									}
									?>
								</div>
								<div class="gtable_cell">
									<input type="checkbox" autocomplete="off" <?php if($v_row['message_handled_by'] != "") echo 'checked'; ?> class="handleObjection" value="<?php echo $v_row['objectionId']?>"/>
								</div>
							<?php } else { ?> 
								<div class="gtable_cell">
									<?php
									if($v_row['collecting_case_surveillance_date'] != '0000-00-00' && $v_row['collecting_case_surveillance_date'] != ''){
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
							<?php } ?>
						</div>
					<?php } ?>
				</div>

			</div>
		</div>
	</div>
</div>

<?php $list_filter = $_GET['list_filter'] ? $_GET['list_filter'] : 0; ?>
<script type="text/javascript">
var out_popup;
var out_popup_options={
	follow: [true, true],
	followSpeed: 0,
	fadeSpeed: 0,
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function(){
		$(this).addClass('opened');
		//$(this).find('.b-close').on('click', function(){out_popup.close();});
	},
	onClose: function(){
		if($(this).is('.close-reload')) {
			reloadPage();
        }
		$(this).removeClass('opened');
	}
};


$(document).ready(function() {
    var page = '<?php echo $page?>';
    // On customer row click
	$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
		if(e.target.nodeName == 'DIV') {
		 	fw_load_ajax($(this).data('href'),'',true);
		}
	});
	$(".reset_creditor").off("click").on("click", function(){
		$("#creditorIdFilter").val(0);
		reloadPage();
	})
	$(".objection_status").off("change").on("change", function(){  		
		reloadPage();	
	})
	$(".datepicker").datepicker({
		dateFormat: "d.m.yy",
		firstDay: 1,
		onSelect: function() {
			reloadPage(); 
		}
	});
	$(".select_creditor").off("click").on("click", function(e){
		e.preventDefault();
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1, creditor: 1};
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_creditors";?>',
            data: _data,
            success: function(obj){
                fw_loading_end();
                $('#popupeditboxcontent').html('');
                $('#popupeditboxcontent').html(obj.html);
                out_popup = $('#popupeditbox').bPopup(out_popup_options);
                $("#popupeditbox:not(.opened)").remove();
            }
        });
	})
	$(".handleObjection").off("click").on("click", function(){
		var checked = 0;
		if($(this).is(":checked")){
			checked = 1;
		}
		var data = {
			paused_id: $(this).val(),
			checked: checked
		};
		ajaxCall('update_pause_handle', data, function(json) {
			reloadPage();
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
	
	$('.export_list').off("click").on("click", function(e){
		fw_loading_start();
		var generateIframeDownload = function(){
			fetch("<?php echo $extradir;?>/output/includes/export_list.php?mainlist_filter=<?php echo $mainlist_filter;?>&list_filter=<?php echo $list_filter;?>&sublist_filter=<?php echo $sublist_filter;?>&time=<?php echo time();?>")
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
		  }

		  generateIframeDownload();
	})
});
</script>

<style>
.gtable_cell {
	position: relative;
}
.hoverEye {
	position: relative;
	color: #0284C9;
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
.hoverEye.hover .hoverInfo {
	display: block;
}
.select_creditor {
	cursor: pointer;
	color: #0284C9;
}
.reset_creditor {
	cursor: pointer;
	color: #0284C9;
	margin-left: 10px;
}
.export_list {
	float: right;
	cursor: pointer;
	margin-bottom: 10px;
}
</style>
