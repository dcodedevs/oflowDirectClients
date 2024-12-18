<?php
$mainlist_filter = $_GET['mainlist_filter'] ? ($_GET['mainlist_filter']) : '';
$list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : $default_list;
$sublist_filter = $_GET['sublist_filter'] ? ($_GET['sublist_filter']) : $default_sublist;

// $create_agreement_file = __DIR__ . '/../../api/protected/fnc_create_agreement_file.php';
// if (file_exists($create_agreement_file)) {
//   	// include $create_agreement_file;
// 	// $result = create_agreement_file($creditorId);
// 	// var_dump(sanitize_escape($result['file']));
// }

$type_no = "";
$hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/get_type_no.php';
if (file_exists($hook_file)) {
  include $hook_file;
  if (is_callable($run_hook)) {
    $hook_params = array('creditor_id'=>$creditor['id']);
    $hook_result = $run_hook($hook_params);
    // echo implode("<br/>", $hook_result['type_names']);
    if($hook_result['result']){
      $type_no = $hook_result['result'];
    }
    // var_dump($hook_result['types']);
  }
}
// var_dump($type_no);
if($variables->loggID == "byamba@dcode.no"){

    // $s_sql = "SELECT cr.* FROM creditor cr WHERE sync_from_accounting = 1 AND 24sevenoffice_client_id > 0";
    // $o_query = $o_main->db->query($s_sql);
    // $creditors = $o_query ? $o_query->result_array(): array();
    // foreach($creditors as $creditor) {
    //     $s_sql = "UPDATE creditor_report_collecting SET 24sevenoffice_client_id = ? WHERE creditor_id = ?";
    //     $o_query = $o_main->db->query($s_sql, array($creditor['24sevenoffice_client_id'], $creditor['id']));
    // }

    // $from_api = true;
    // $cid = $creditor['id'];

    // include(__DIR__."/../../output/includes/download_customer_report_pdf.php");

    // $hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/get_transactions.php';
    // if (file_exists($hook_file)) {
    //     include $hook_file;
    //     if (is_callable($run_hook)) {
            
    //         $bookaccountEnd = 1529;
    //         if($creditor['bookaccount_upper_range'] >= 1500){
    //             $bookaccountEnd = $creditor['bookaccount_upper_range'];
    //         }
    //         $hook_params = array();
    //         $hook_params['creditor_id'] = $creditor['id'];
    //         $hook_params['DateSearchParameters'] = 'DateChangedUTC';
    //         $hook_params['date_start'] = "2024-03-01";
    //         $hook_params['date_end'] = date('Y-m-t', time() + 60*60*24);
    //         $hook_params['bookaccountStart'] = 1500;
    //         $hook_params['bookaccountEnd'] = $bookaccountEnd;
    //         $hook_params['ShowOpenEntries'] = 1;

    //         $hook_result = $run_hook($hook_params);
    //         var_dump($hook_result);
    //     }
    // }
    // $hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/insert_transaction.php';
    // if (file_exists($hook_file)) {
    //     include $hook_file;
        
    //     if (is_callable($run_hook)) {
    //         var_dump(1);
    //     }
    // }
    // $changedAfterDate = isset($creditor['lastImportedDateTimestamp']) ? $creditor['lastImportedDateTimestamp'] : "";
    // if($changedAfterDate != null && $changedAfterDate != "") {
    //     $now = DateTime::createFromFormat('U.u', $changedAfterDate);
    //     if($now){
    //         $dataCustomer['changedAfter'] = $now->format("Y-m-d\TH:i:s.u");
    //     }
    // }
    // // $s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, creditor_syncing_id = ?";
    // // $o_query = $o_main->db->query($s_sql, array($creditorData['id'], 'step 3 started - customer sync', $creditor_syncing_id));
    //     // var_dump($dataCustomer['changedAfter']);
    // if(isset($dataCustomer['changedAfter'])) {
    //     $connect_tries = 0;
    //     do {
    //         $connect_tries++;
    //         $response_customer = $api->get_customer_list($dataCustomer);
    //         if($response_customer !== null){
    //             break;
    //         }
    //     } while($connect_tries < 11);
    //     $connect_tries--;

    //     $customer_list = $response_customer['GetCompaniesResult']['Company'];
    //     if(isset($customer_list['Id'])){
    //         $customer_list = array($customer_list);
    //     }
    //     var_dump($customer_list);
    //     $updated_customer_count = count($customer_list);
    //     // customer_local_update($customer_list, $creditorData, $updateOnly);
    // }
}
?>

<?php
if(isset($_GET['list_filter'])) { $filter = $_GET['list_filter']; } else { $filter = "active"; }
?>
<div class="creditor_info">
    <div class="creditor_info_row title_row">
        <?php echo $formText_CreditorName_output;?>:
        <b><?php echo $creditor['companyname'];?></b>
        <a class="open_24seven" href="https://s30.getynet.com/accounts/oflowDirectClients24so/fw/index.php?pageID=35&accountname=oflowDirectClients24so&companyID=<?php echo $_GET['companyID']?>&caID=acc_local&module=CustomerPortalCollectCase&folder=output&folderfile=output&inc_obj=list&list_filter=canSendReminderNow&mainlist_filter=reminderLevel&creditor_filter=<?php echo $creditor['id']?>&customer_filter=&search_filter=&search_by=&page=1" target="_blank"><?php echo $formText_Open24SevenOfficePortal_output?></a>
    </div>
	<div class="creditor_info_row">
        <?php echo $formText_Contactpersons_output;?> <span class="add_contactperson"><?php echo $formText_AddContactperson_output;?></span>
		<div class="contatperson_list">
			<table class="table">
				<tr>
					<th><?php echo $formText_Name_output;?></th>
					<th><?php echo $formText_Email_output;?></th>
					<th><?php echo $formText_Phone_output;?></th>
					<th><?php echo $formText_Position_output;?></th>
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
						<td><?php echo $contactperson['name'];?></td>
						<td><?php echo $contactperson['email'];?></td>
						<td><?php echo $contactperson['phone'];?></td>
						<td><?php echo $contactperson['position'];?></td>
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
		</div>
    </div>
    <?php /*
    <div class="creditor_info_row">
        <?php echo $formText_AdditionalBookAccounts_output;?> <span class="edit_additional_bookaccount"><?php echo $formText_AddBookaccount_output;?></span>
		<div class="contatperson_list">
			<table class="table">
				<tr>
					<th><?php echo $formText_Bookaccount_output;?></th>
					<th></th>
				</tr>
				<?php
				$s_sql = "SELECT * FROM creditor_additional_bookaccounts WHERE creditor_id = ? ORDER BY bookaccount ASC";
				$o_query = $o_main->db->query($s_sql, array($creditor['id']));
				$additional_bookaccounts = ($o_query ? $o_query->result_array() : array());
				foreach($additional_bookaccounts as $additional_bookaccount) {
					?>
					<tr>
						<td><?php echo $additional_bookaccount['bookaccount'];?></td>
                        <td>
							<span class="glyphicon glyphicon-pencil edit_additional_bookaccount" data-bookaccountid="<?php echo $additional_bookaccount['id'];?>"></span>
							<span class="glyphicon glyphicon-trash delete_additional_bookaccount" data-bookaccountid="<?php echo $additional_bookaccount['id'];?>"></span>
						</td>
					</tr>
					<?php
				}
				?>
			</table>
		</div>
    </div>*/?>

    <div class="creditor_info_row">
        <?php if($creditor['create_cases']){  ?>
            <label><?php echo $formText_DaysOverdueSuggestCase_Output;?>:</label>
        <?php } else { ?>
            <label><?php echo $formText_DaysOverdueStartCase_Output;?>:</label>
        <?php } ?>
        <?php echo $creditor['days_overdue_startcase'];?>
    </div>

    <?php if($creditor['create_cases']){ ?>
        <div class="creditor_info_row">
            <label><?php echo $formText_ProcessForManual_Output;?>:</label>
            <div style="display: inline-block; vertical-align:top;">
                <?php
            	$s_sql = "SELECT collecting_cases_process.* FROM creditor_manualprocess_connection LEFT OUTER JOIN collecting_cases_process ON collecting_cases_process.id = creditor_manualprocess_connection.process_id
                WHERE creditor_manualprocess_connection.creditor_id = ?";
                $o_query = $o_main->db->query($s_sql, array($creditor['id']));
                $connections = ($o_query ? $o_query->result_array() : array());
            	foreach($connections as $connection) {
                    echo $connection['name']."</br>";
            	}
                ?>
            </div>
        </div>
    <?php } else { ?>
        <div class="creditor_info_row">
            <label><?php echo $formText_ReminderLevelCaseCompany_Output;?>:</label>
            <?php echo $reminder_level_case_company['name'];?>
        </div>
        <div class="creditor_info_row">
            <label><?php echo $formText_ReminderLevelCasePerson_Output;?>:</label>
            <?php echo $reminder_level_case_person['name'];?>
        </div>
        <div class="creditor_info_row">
            <label><?php echo $formText_CollectingLevelCaseCompany_Output;?>:</label>
            <?php echo $collecting_level_case_company['name'];?>
        </div>
        <div class="creditor_info_row">
            <label><?php echo $formText_CollectingLevelCasePerson_Output;?>:</label>
            <?php echo $collecting_level_case_person['name'];?>
        </div>
    <?php } ?>
        
    <div class="incomeReport output-btn"><?php echo $formText_ShowIncomeReport_output?></div>

    <div class="row_info">
        <div class="launchImport output-btn"><?php echo $formText_LaunchImportScript_output?></div>

        <span class="row_info_description">
            <label><?php echo $formText_LastImportedTimestamp_Output;?>:</label>
            <?php
			// if($creditor['lastImportedDateTimestamp'] != ""){
            //     $now = DateTime::createFromFormat('U.u', $creditor['lastImportedDateTimestamp']);
            //     if($now){
            //         echo $now->format("Y-m-d H:i:s.u");
            //     }
            // }
			if($creditor['lastImportedDate'] != "" && $creditor['lastImportedDate'] != "0000-00-00"){
				echo date("d.m.Y H:i:s", strtotime($creditor['lastImportedDate']));
			}
			?>
        </span>
    </div>
    <?php /*
    <div class="row_info">
        <div class="createCases output-btn"><?php echo $formText_CreateCases_output?></div>

        <span class="row_info_description">
            <label><?php echo $formText_LastCreateCaseDate_Output;?>:</label>
            <?php if($creditor['last_create_case_date'] != "" && $creditor['last_create_case_date'] != "0000-00-00") echo date("d.m.Y", strtotime($creditor['last_create_case_date']));?>
        </span>
    </div>
    <div class="row_info">
        <div class="launchProcessingSteps output-btn"><?php echo $formText_LaunchProcessingStepsScript_output?></div>
        <span class="row_info_description">
            <label><?php echo $formText_LastProcessDate_Output;?>:</label>
            <?php if($creditor['last_process_date'] != "" && $creditor['last_process_date'] != "0000-00-00") echo date("d.m.Y", strtotime($creditor['last_process_date']));?>
        </span>
    </div>*/?>

    <div class="row_info">
        <div class="syncCustomer output-btn"><?php echo $formText_SyncCustomers_output?></div>

    </div>

    <?php /*
    <div class="creditor_info_row">
        <label><?php echo $formText_ProcessForGeneratePdf_Output;?>:</label>
        <div class="launchPdfGenerate output-btn"><?php echo $formText_LaunchPdfGenerateScript_output?></div>
    </div> */?>
	<div class="row_info">
		<?php

		$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE creditor_id = ? ORDER BY created DESC";
		$o_query = $o_main->db->query($s_sql, array($creditor['id']));
		$connections = ($o_query ? $o_query->result_array() : array());
		foreach($connections as $connection) {
			?>
			<div>
                <?php echo $connection['username']." - ".date("d.m.Y H:i:s", strtotime($connection['created']));?>
                <span class="delete_session" data-session_id="<?php echo $connection['id']; ?>"><?php echo $formText_Delete_output;?></span>
            </div>
			<?php
		}
		?>

	</div>



    <div class="row_info">
        <div class="show_billing output-btn"><?php echo $formText_ShowBilling_output?></div>

    </div>
	<div class="row_info">
        <div class="show_closed_invoices_info output-btn"><?php echo $formText_ShowAllTransactionsForClosedInvoices_output?></div>
    </div>
	<div class="row_info">
        <div class="show_fees_without_connection output-btn"><?php echo $formText_ShowOpenFeesWithoutConnection_output?></div>
    </div>
	<div class="row_info">
        <div class="check_transaction output-btn"><?php echo $formText_CheckTransaction_output?></div>
    </div>
	<div class="row_info">
        <div class="sort_by_tabs output-btn"><?php echo $formText_SortByTabs_output?></div>
    </div>
	<div class="row_info">
        <div class="sendEmailWithReminderInfo output-btn"><?php echo $formText_SendEmailWithReminderInfo_output?></div>
    </div>
	<div class="row_info">
        <div class="reportPage output-btn"><?php echo $formText_CustomerReportPage_output?></div>
    </div>
	<div class="row_info">
        <a class="reportPdf output-btn" href="<?php echo $_SERVER['PHP_SELF'].'/../../modules/'.$module.'/output/includes/download_customer_report_pdf.php?caID='.$_GET['caID']."&cid=".$cid;?>" target="_blank">
        <?php echo $formText_DownloadCustomerReportPdf_output;?></a>
    </div>
	<div class="row_info">
        <div class="internalReportPage output-btn"><?php echo $formText_InternalReportPage_output?></div>
    </div>
	<div class="row_info">
        <div class="collectingIncomeReport output-btn"><?php echo $formText_collectingIncomeReport_output?></div>
    </div>
	<div class="row_info">
        <div class="update_all_transactions output-btn"><?php echo $formText_UpdateAllTransactions_output?></div>
    </div>

    <?php if($variables->loggID =="byamba@dcode.no") { ?>
        <div class="row_info">
            <div class="view_syncing_logs output-btn"><?php echo $formText_ViewSyncingLogs_output?></div>
        </div>
    <?php } ?>
    
    <div class="row_info">
        <div class="sync_to_aptic output-btn"><?php echo $formText_SyncToAptic_output?> <?php if($creditor['aptic_customer_id'] != "") { ?>(<?php echo $creditor['aptic_customer_id'];?>)<?php } ?></div>
    </div>
    
    <div class="actionBtnRow">
        <button class="output-btn small output-edit-creditor-detail editBtnIcon" data-creditor-id="<?php echo $creditor['id']?>"><span class="glyphicon glyphicon-pencil"></span></button>
    </div>
    <?php /*
    <div class="checkbox_update_wrapper"><input type="checkbox" class="update_include_project_code" id="update_include_project_code" <?php if($creditor['activate_project_code_in_reminderletter']) echo 'checked'?>/> <label for="update_include_project_code"><?php echo $formText_ActivateProjectCodeOnReminderLevel_output; ?></label></div>
    */?>
    <div class="clear"></div>
</div>

<div class="output-filter">
    <ul>
        <li class="item<?php echo ($mainlist_filter == 'transactions' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="transactions" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid."&mainlist_filter=transactions"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $transactionsCount; ?></span>
                    <?php echo $formText_transactions_output;?>
                </span>
            </a>
        </li>

        <li class="item<?php echo ($mainlist_filter == 'reminderLevel' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="reminderLevel" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid."&mainlist_filter=reminderLevel"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $invoicesOnReminderLevelCount; ?></span>
                    <?php echo $formText_InvoicesOnReminderLevel_output;?>
                </span>
            </a>
        </li>
        <li class="item<?php echo ($mainlist_filter == 'collectingLevel' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="collectingLevel" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid."&mainlist_filter=collectingLevel"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $casesOnCollectingLevelCount; ?></span>
                    <?php echo $formText_CasesOnCollectingLevel_output;?>
                </span>
            </a>
        </li>
        <?php /*

<li class="item<?php echo ($mainlist_filter == 'all_transactions' ? ' active':'');?>">
    <a class="topFilterlink" data-listfilter="all_transactions" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid."&mainlist_filter=all_transactions"; ?>">
        <span class="link_wrapper">
            <span class="count"><?php echo $allTransactionsCount; ?></span>
            <?php echo $formText_AllTransactions_output;?>
        </span>
    </a>
</li>
        <li class="item<?php echo ($mainlist_filter == 'invoice' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="invoice" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid."&mainlist_filter=invoice"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $invoices_count; ?></span>
                    <?php echo $formText_Invoices_output;?>
                </span>
            </a>
        </li>
        <li class="item<?php echo ($mainlist_filter == 'case' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="case" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid."&mainlist_filter=case"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $cases_count; ?></span>
                    <?php echo $formText_Cases_output;?>
                </span>
            </a>
        </li>*/?>
    </ul>
</div>
<?php
if($mainlist_filter == "reminderLevel") {
    ?>
    <div class="output-filter">
        <ul>
            <li class="item<?php echo ($list_filter == 'canSendReminderNow' ? ' active':'');?>">
                <a class="topFilterlink" data-listfilter="canSendReminderNow" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid."&mainlist_filter=".$mainlist_filter."&list_filter=canSendReminderNow"; ?>">
                    <span class="link_wrapper">
                        <span class="count"><?php echo $canSendReminderNowCount; ?></span>
                        <?php echo $formText_CanSendReminderNow_output;?>
                    </span>
                </a>
            </li>
            <li class="item<?php echo ($list_filter == 'dueDateNotExpired' ? ' active':'');?>">
                <a class="topFilterlink" data-listfilter="dueDateNotExpired" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid."&mainlist_filter=".$mainlist_filter."&list_filter=dueDateNotExpired"; ?>">
                    <span class="link_wrapper">
                        <span class="count"><?php echo $dueDateNotExpiredCount; ?></span>
                        <?php echo $formText_dueDateNotExpired_output;?>
                    </span>
                </a>
            </li>
            <li class="item<?php echo ($list_filter == 'doNotSend' ? ' active':'');?>">
                <a class="topFilterlink" data-listfilter="doNotSend" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid."&mainlist_filter=".$mainlist_filter."&list_filter=doNotSend"; ?>">
                    <span class="link_wrapper">
                        <span class="count"><?php echo $doNotSendCount; ?></span>
                        <?php echo $formText_DoNotSend_output;?>
                    </span>
                </a>
            </li>
            <li class="item<?php echo ($list_filter == 'stoppedWithObjection' ? ' active':'');?>">
                <a class="topFilterlink" data-listfilter="stoppedWithObjection" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid."&mainlist_filter=".$mainlist_filter."&list_filter=stoppedWithObjection"; ?>">
                    <span class="link_wrapper">
                        <span class="count"><?php echo $stoppedWithObjectionCount; ?></span>
                        <?php echo $formText_stoppedWithObjection_output;?>
                    </span>
                </a>
            </li>
            <li class="item<?php echo ($list_filter == 'notPayedConsiderCollectingProcess' ? ' active':'');?>">
                <a class="topFilterlink" data-listfilter="notPayedConsiderCollectingProcess" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid."&mainlist_filter=".$mainlist_filter."&list_filter=notPayedConsiderCollectingProcess"; ?>">
                    <span class="link_wrapper">
                        <span class="count"><?php echo $notPayedConsiderCollectingProcessCount; ?></span>
                        <?php echo $formText_notPayedConsiderCollectingProcess_output;?>
                    </span>
                </a>
            </li>

            <li class="item<?php echo ($list_filter == 'allTransactionsWithoutCases' ? ' active':'');?>">
                <a class="topFilterlink" data-listfilter="allTransactionsWithoutCases" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid."&mainlist_filter=".$mainlist_filter."&list_filter=allTransactionsWithoutCases"; ?>">
                    <span class="link_wrapper">
                        <span class="count"><?php echo $allTransactionsWithoutCasesCount; ?></span>
                        <?php echo $formText_allTransactionsWithoutCasess_output;?>
                    </span>
                </a>
            </li>
        </ul>
    </div>
    <?php
} else if($mainlist_filter == "collectingLevel"){
    ?>
    <div class="output-filter">
        <ul>

			<li class="item<?php echo ($list_filter == "warning" ? ' active':'');?>">
				<a class="topFilterlink" data-listfilter="warning" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid."&mainlist_filter=".$mainlist_filter."&list_filter=warning"; ?>">
					<span class="link_wrapper">
						<span class="count"><?php echo $warning_case_count; ?></span>
						<?php echo $formText_CasesInWarningLevel_output;?>
					</span>
				</a>
			</li>
			<li class="item<?php echo ($list_filter == "collecting" ? ' active':'');?>">
				<a class="topFilterlink" data-listfilter="collecting" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid."&mainlist_filter=".$mainlist_filter."&list_filter=collecting"; ?>">
					<span class="link_wrapper">
						<span class="count"><?php echo $collecting_case_count; ?></span>
						<?php echo $formText_CasesInCollectingLevel_output;?>
					</span>
				</a>
			</li>
			<li class="item<?php echo ($list_filter == "warning_closed" ? ' active':'');?>">
				<a class="topFilterlink" data-listfilter="warning_closed" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid."&mainlist_filter=".$mainlist_filter."&list_filter=warning_closed"; ?>">
					<span class="link_wrapper">
						<span class="count"><?php echo $warning_closed_case_count; ?></span>
						<?php echo $formText_CasesClosedInWarningLevel_output;?>
					</span>
				</a>
			</li>
			<li class="item<?php echo ($list_filter == "collecting_closed" ? ' active':'');?>">
				<a class="topFilterlink" data-listfilter="collecting_closed" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid."&mainlist_filter=".$mainlist_filter."&list_filter=collecting_closed"; ?>">
					<span class="link_wrapper">
						<span class="count"><?php echo $collecting_closed_case_count; ?></span>
						<?php echo $formText_CasesClosedInCollectingLevel_output;?>
					</span>
				</a>
			</li>
        </ul>
    </div>
	<?php if($list_filter == "collecting" || $list_filter == 'warning') { ?>
		<div class="output-filter">
		    <ul>
				<li class="item<?php echo ($sublist_filter == "notStarted" ? ' active':'');?>">
	                <a class="topFilterlink" data-sublistfilter="notStarted" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid."&mainlist_filter=".$mainlist_filter."&list_filter=".$list_filter."&sublist_filter=notStarted"; ?>">
	                    <span class="link_wrapper">
	                        <span class="count"><?php echo $notStartedCount; ?></span>
	                        <?php echo $formText_NotStarted_output;?>
	                    </span>
	                </a>
	            </li>
	            <li class="item<?php echo ($sublist_filter == "canSendNow" ? ' active':'');?>">
	                <a class="topFilterlink" data-sublistfilter="canSendNow" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid."&mainlist_filter=".$mainlist_filter."&list_filter=".$list_filter."&sublist_filter=canSendNow"; ?>">
	                    <span class="link_wrapper">
	                        <span class="count"><?php echo $canSendNowCount; ?></span>
	                        <?php echo $formText_CanSendNow_output;?>
	                    </span>
	                </a>
	            </li>
	            <li class="item<?php echo ($sublist_filter == "dueDateNotExpired" ? ' active':'');?>">
	                <a class="topFilterlink" data-sublistfilter="dueDateNotExpired" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid."&mainlist_filter=".$mainlist_filter."&list_filter=".$list_filter."&sublist_filter=dueDateNotExpired"; ?>">
	                    <span class="link_wrapper">
	                        <span class="count"><?php echo $dueDateNotExpiredCount; ?></span>
	                        <?php echo $formText_DueDateNotExpired_output;?>
	                    </span>
	                </a>
	            </li>
	            <li class="item<?php echo ($sublist_filter == "stoppedWithObjection" ? ' active':'');?>">
	                <a class="topFilterlink" data-sublistfilter="stoppedWithObjection" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid."&mainlist_filter=".$mainlist_filter."&list_filter=".$list_filter."&sublist_filter=stoppedWithObjection"; ?>">
	                    <span class="link_wrapper">
	                        <span class="count"><?php echo $stoppedWithObjectionCount; ?></span>
	                        <?php echo $formText_StoppedWithObjection_output;?>
	                    </span>
	                </a>
	            </li>
		    </ul>
		</div>
		<?php
	}
}
?>

<div class="p_tableFilter">
    <div class="p_tableFilter_left">
        <!-- <div class="addBtn fw_text_link_color filterBtn"><?php echo $formText_Filter_output;?></div> -->
        <!-- <div class="add_new_invoice addBtn fw_text_link_color"><?php echo $formText_AddNew_Invoice_output;?></div> -->
        <?php if($mainlist_filter != "case") {
            /*
             ?>
            <div class="group_by_debitor_wrapper">
                <input type="checkbox" value="1" id="group_by_debitor" autocomplete="off" <?php if($_GET['group_by_debitor']) echo 'checked';?>/>
                <label for="group_by_debitor" id="group_by_debitor_label">
                    <?php echo $formText_GroupByDebitor_output;?>
                </label>
            </div>
        <?php */} ?>
		<?php
		if($mainlist_filter == "transactions") {
            if($_GET['customer_filter'] > 0){
                $s_sql = "SELECT * FROM customer WHERE creditor_customer_id = ? AND creditor_id = ?";
                $o_query = $o_main->db->query($s_sql, array($_GET['customer_filter'], $cid));
                $selected_customer = ($o_query ? $o_query->row_array() : array());
            }
		?>
			<?php /*<select name="order_by" class="orderByField" autocomplete="off">
				<option value="0"><?php echo $formText_CustomerName_output;?></option>
				<option value="1" <?php if($_GET['orderBy'] == 1) echo 'selected';?>><?php echo $formText_CustomerId_output;?></option>
			</select>*/?>
            <span class="select_transaction_customer"><?php if($selected_customer){ echo $selected_customer['name']." ".$selected_customer['middlename']." ".$selected_customer['lastname'];} else { echo $formText_SelectCustomer_output; }?></span>

            <select name="order_by" class="transactionsStatus" autocomplete="off">
				<option value="0"><?php echo $formText_open_output;?></option>
				<option value="1" <?php if($_GET['transaction_status'] == 1) echo 'selected';?>><?php echo $formText_closed_output;?></option>
				<option value="2" <?php if($_GET['transaction_status'] == 2) echo 'selected';?>><?php echo $formText_All_output;?></option>
            </select>
	        <div class="clear"></div>
			<div class="compareWithOpenTransactions"><?php echo $formText_CompareWithOpenTransactions_output;?></div>
		<?php } ?>
    </div>
    <div class="p_tableFilter_right">
        <form class="searchFilterForm" id="searchFilterForm">
			<div class="employeeSearch">
				<span class="glyphicon glyphicon-search"></span>
				<input type="text" placeholder="<?php if($mainlist_filter == "invoice") { echo $formText_SearchForInvoice_output; } else { echo $formText_SearchForCase_output; }?>" class="employeeSearchInput searchFilter" autocomplete="off" value="<?php echo $search_filter;?>"/>
				<div class="employeeSearchSuggestions allowScroll"></div>
				<button id="p_tableFilterSearchBtn" class="fw_button_color "><?php echo $formText_Search_output; ?></button>
			</div>
        </form>
        <div class="clear"></div>
        <div class="filteredCountRow">
            <span class="selectionCount">0</span> <?php echo $formText_InSelection_output;?>
            <div class="resetSelection fw_text_link_color"><?php echo $formText_Reset_output;?></div>
        </div>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function(){

	$(".topFilterlink").unbind("click").bind("click", function(e){
		e.preventDefault();
		var href = $(this).attr("href");
		fw_load_ajax(href, false, true);
	})
	var loadingCustomer = false;
    var $input = $('.employeeSearchInput');
    var customer_search_value;
    $input.on('focusin', function () {
        searchCustomerSuggestions();
        $("#p_container").unbind("click").bind("click", function (ev) {
            if($(ev.target).parents(".employeeSearch").length == 0){
                $(".employeeSearchSuggestions").hide();
            }
        });
    })
    //on keyup, start the countdown
    $input.on('keyup', function () {
        searchCustomerSuggestions();
    });
    //on keydown, clear the countdown
    $input.on('keydown', function () {
        searchCustomerSuggestions();
    });
    function searchCustomerSuggestions (){
        if(!loadingCustomer) {
            if(customer_search_value != $(".employeeSearchInput").val()) {
                loadingCustomer = true;
                customer_search_value = $(".employeeSearchInput").val();
                $('.employeeSearch .employeeSearchSuggestions').html('<div class="article-loading lds-ring"><div></div><div></div><div></div><div></div></div>').show();
                var _data = { fwajax: 1, fw_nocss: 1, search: customer_search_value, creditor_id: '<?php echo $_GET['cid'];?>', mainlist_filter: '<?php echo $mainlist_filter;?>', list_filter: '<?php echo $list_filter;?>'};
                $.ajax({
                    cache: false,
                    type: 'POST',
                    dataType: 'json',
                    url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_customers_suggestions";?>',
                    data: _data,
                    success: function(obj){
                        loadingCustomer = false;
                        $('.employeeSearch .employeeSearchSuggestions').html('');
                        $('.employeeSearch .employeeSearchSuggestions').html(obj.html).show();
                        searchCustomerSuggestions();
                    }
                }).fail(function(){
                    loadingCustomer = false;
                })
            }
        }
    }
	$(".orderByField").change(function(){
		var data = {
            mainlist_filter: '<?php echo $mainlist_filter;?>',
            list_filter: '<?php echo $list_filter;?>',
            department_filter: $(this).val(),
            search_filter: $('.searchFilter').val(),
            cid: '<?php echo $cid;?>',
			orderBy: $(".orderByField").val()
        };
        loadView("creditor_list", data);
	})
    $(".transactionsStatus").change(function(){
		var data = {
            mainlist_filter: '<?php echo $mainlist_filter;?>',
            list_filter: '<?php echo $list_filter;?>',
            department_filter: $(this).val(),
            search_filter: $('.searchFilter').val(),
            cid: '<?php echo $cid;?>',
			transaction_status: $(".transactionsStatus").val(),
			customer_filter: '<?php echo $customer_filter;?>'
        };
        loadView("creditor_list", data);
	})
    $(".select_transaction_customer").off("click").on("click", function(){
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1, creditor_id: '<?php echo $cid;?>', filter: 1};
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_customers";?>',
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
    // Filter by building
    $('.filterDepartment').on('change', function(e) {
        var data = {
            mainlist_filter: '<?php echo $mainlist_filter;?>',
            list_filter: '<?php echo $list_filter;?>',
            department_filter: $(this).val(),
            search_filter: $('.searchFilter').val(),
            cid: '<?php echo $cid;?>'
        };
        loadView("creditor_list", data);
        // ajaxCall('list', data, function(json) {
        //     $('.p_pageContent').html(json.html);
        // });
    });
	$(".compareWithOpenTransactions").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $cid;?>'
		};
        
		bootbox.confirm('<?php echo $formText_StartCompareScript_output; ?>?', function(result) {
			if (result) {
                ajaxCall('compareWithOpenTransactions', data, function(json) {
                    $('#popupeditboxcontent').html('');
                    $('#popupeditboxcontent').html(json.html);
                    out_popup = $('#popupeditbox').bPopup(out_popup_options);
                    $("#popupeditbox:not(.opened)").remove();
                });
            }
        })
	});
    // Filter by customer name
    $('.searchFilterForm').on('submit', function(e) {
        e.preventDefault();
        var data = {
            mainlist_filter: '<?php echo $mainlist_filter;?>',
            list_filter: '<?php echo $list_filter;?>',
            department_filter: $('.filterDepartment').val(),
            search_filter: $('.searchFilter').val(),
            cid: '<?php echo $cid;?>'
        };
        loadView("creditor_list", data);
        // ajaxCall('list', data, function(json) {
        //     $('.p_pageContent').html(json.html);
        // });
    });
    $(".resetSelection").on('click', function(e) {
        e.preventDefault();
        var data = {
            mainlist_filter: '<?php echo $mainlist_filter;?>',
            list_filter: '<?php echo $list_filter;?>',
            cid: '<?php echo $cid;?>'
        };
        loadView("creditor_list", data);
        // ajaxCall('list', data, function(json) {
        //     $('.p_pageContent').html(json.html);
        //     $('.searchFilterForm .searchFilter').val("");
        //     $(".filterDepartment").val("");
        // });
    });
    $(".launchImport").on("click", function(e){
        e.preventDefault();

        var data = {
            mainlist_filter: '<?php echo $mainlist_filter;?>',
            list_filter: '<?php echo $list_filter;?>',
            department_filter: $('.filterDepartment').val(),
            search_filter: $('.searchFilter').val(),
            cid: '<?php echo $cid;?>',
            action: 'launchImport'
        };
        loadView("creditor_list", data);
    })
    $(".launchProcessingSteps").on("click", function(e){
        e.preventDefault();

        var data = {
            mainlist_filter: '<?php echo $mainlist_filter;?>',
            list_filter: '<?php echo $list_filter;?>',
            department_filter: $('.filterDepartment').val(),
            search_filter: $('.searchFilter').val(),
            cid: '<?php echo $cid;?>',
            action: 'launchProcessingSteps'
        };
        loadView("creditor_list", data);
    })
    $(".createCases").on("click", function(e){
        e.preventDefault();

        var data = {
            mainlist_filter: '<?php echo $mainlist_filter;?>',
            list_filter: '<?php echo $list_filter;?>',
            department_filter: $('.filterDepartment').val(),
            search_filter: $('.searchFilter').val(),
            cid: '<?php echo $cid;?>',
            action: 'createCases'
        };
        loadView("creditor_list", data);
    })
    $(".syncCustomer").on("click", function(e){
        e.preventDefault();

        var data = {
            mainlist_filter: '<?php echo $mainlist_filter;?>',
            list_filter: '<?php echo $list_filter;?>',
            department_filter: $('.filterDepartment').val(),
            search_filter: $('.searchFilter').val(),
            cid: '<?php echo $cid;?>',
            action: 'syncCustomer'
        };
        loadView("creditor_list", data);
    })
	$(".show_billing").on("click", function(e){
		e.preventDefault();
		var data = {
            cid: '<?php echo $cid;?>',
		}
        loadView("billing_info", data);
	})
	$(".show_closed_invoices_info").on("click", function(e){
		e.preventDefault();
		var data = {
            cid: '<?php echo $cid;?>',
		}
        loadView("show_closed_invoices_info", data);
	})
    $(".show_fees_without_connection").on("click", function(e){
        e.preventDefault();
		var data = {
            cid: '<?php echo $cid;?>',
		}
        loadView("show_fees_without_connection", data);
    })
    $(".launchFile").on("click", function(){
        var data = {
            mainlist_filter: '<?php echo $mainlist_filter;?>',
            list_filter: '<?php echo $list_filter;?>',
            department_filter: $('.filterDepartment').val(),
            search_filter: $('.searchFilter').val(),
            cid: '<?php echo $cid;?>',
            action: 'launchFile'
        };
        loadView("creditor_list", data);
    })
    $(".reportPage").on("click", function(){
        var data = {
            cid: '<?php echo $cid;?>',
        };
        loadView("report_page", data);
    })
    $(".internalReportPage").on("click", function(){
        var data = {
            cid: '<?php echo $cid;?>',
        };
        loadView("internal_report_page", data);
    })
    $(".collectingIncomeReport").on("click", function(){
        var data = {
            cid: '<?php echo $cid;?>',
        };
        loadView("collecting_income_report", data);
    })

    // $(".createCasesForAllMarked").on("click", function(e){
    //     e.preventDefault();
	// 	var data = {
	// 		creditor_id: '<?php echo $cid;?>'
	// 	};
	// 	ajaxCall('create_case_manual', data, function(json) {
	// 		$('#popupeditboxcontent').html('');
	// 		$('#popupeditboxcontent').html(json.html);
	// 		out_popup = $('#popupeditbox').bPopup(out_popup_options);
	// 		$("#popupeditbox:not(.opened)").remove();
	// 	});
    // })
    $(".launchPdfGenerate").on("click", function(e){
        e.preventDefault();
		ajaxCall('generate_pdf', { cid: '<?php echo $cid;?>' }, function(json) {
			var win = window.open('<?php echo $extradomaindirroot.'/modules/CollectingCases/output/ajax.download.php?ID=';?>' + json.data.batch_id, '_blank');
  			win.focus();
			/*$.ajax({
				url: '<?php echo $extradomaindirroot.'/modules/CollectingCases/output/ajax.download.php?ID=';?>' + json.data.batch_id,
				type: "POST",
				dataType: "text",
				data: {
					fwajax: 1,
					fw_nocss: 1,
					cid: '<?php echo $cid;?>'
				},
				success: function(response) {
					//window.open("data:application/pdf," + escape(response));
					var w = window.open('about:blank');
					w.document.write(response);
					w.document.close();
				},
				error: function () {
					alert('Problem getting data');
				},
			});*/
		});
    })

	$(".output-edit-creditor-detail").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			creditor_id: $(this).data('creditor-id')
		};
		ajaxCall('edit_creditor', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
    $(".add_new_invoice").unbind("click").on('click', function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $cid;?>'
		};
		ajaxCall('edit_invoice', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
    $("#group_by_debitor").unbind("click").on('click', function(){
        var group_by_debitor = 0;
        if($(this).is(":checked")) {
            group_by_debitor = 1;
        }
        var data = {
            mainlist_filter: '<?php echo $mainlist_filter;?>',
            list_filter: '<?php echo $list_filter;?>',
            department_filter: $('.filterDepartment').val(),
            search_filter: $('.searchFilter').val(),
            cid: '<?php echo $cid;?>',
            group_by_debitor: group_by_debitor
        };
        loadView("creditor_list", data);
    })
	$(".add_contactperson").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $cid;?>'
		};
		ajaxCall('edit_creditor_contactperson', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".edit_contactperson").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $cid;?>',
			contactperson_id: $(this).data("contactpersonid")
		};
		ajaxCall('edit_creditor_contactperson', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".delete_contactperson").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $cid;?>',
			contactperson_id: $(this).data("contactpersonid"),
			action: "delete"
		}
		bootbox.confirm('<?php echo $formText_DeleteContactperson_output; ?>?', function(result) {
			if (result) {
				ajaxCall('edit_creditor_contactperson', data, function(json) {
				    var data = {
			            mainlist_filter: '<?php echo $mainlist_filter;?>',
			            list_filter: '<?php echo $list_filter;?>',
			            department_filter: $('.filterDepartment').val(),
			            search_filter: $('.searchFilter').val(),
			            cid: '<?php echo $cid;?>',
			        };
			        loadView("creditor_list", data);
				});
			}
		});
	})
    $(".edit_additional_bookaccount").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $cid;?>',
			bookaccount_id: $(this).data("bookaccountid")
		};
		ajaxCall('edit_additional_bookaccount', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
    $(".delete_additional_bookaccount").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $cid;?>',
			bookaccount_id: $(this).data("bookaccountid"),
			action: "delete"
		}
		bootbox.confirm('<?php echo $formText_DeleteBookaccount_output; ?>?', function(result) {
			if (result) {
				ajaxCall('edit_additional_bookaccount', data, function(json) {
				    var data = {
			            mainlist_filter: '<?php echo $mainlist_filter;?>',
			            list_filter: '<?php echo $list_filter;?>',
			            department_filter: $('.filterDepartment').val(),
			            search_filter: $('.searchFilter').val(),
			            cid: '<?php echo $cid;?>',
			        };
			        loadView("creditor_list", data);
				});
			}
		});
	})
    $(".check_transaction").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $cid;?>'
		};
		ajaxCall('check_transaction', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
    $(".sort_by_tabs").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $cid;?>'
		};
		ajaxCall('sort_by_tabs', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
    $(".sendEmailWithReminderInfo").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $cid;?>'
		};
		ajaxCall('send_email_with_reminder_info', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
    $(".update_all_transactions").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $cid;?>'
		};
		ajaxCall('update_all_transactions', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
    $(".update_include_project_code").off("click").on("click", function(e){
        var checked = 0;
        if($(this).is(":checked")){
            checked = 1;
        }
		var data = {
			creditor_id: '<?php echo $cid;?>',
            checked: checked
		};
		ajaxCall('update_include_project_code', data, function(json) {
            var data = {
                mainlist_filter: '<?php echo $mainlist_filter;?>',
                list_filter: '<?php echo $list_filter;?>',
                department_filter: $('.filterDepartment').val(),
                search_filter: $('.searchFilter').val(),
                cid: '<?php echo $cid;?>',
            };
            loadView("creditor_list", data);
		});
	})
    $(".delete_session").off("click").on("click", function(e){
        e.preventDefault();
        var data = {
			creditor_id: '<?php echo $cid;?>',
            session_id: $(this).data("session_id")
		};

        bootbox.confirm('<?php echo $formText_DeleteSession_output; ?>?', function(result) {
			if (result) {
                ajaxCall('delete_session', data, function(json) {
                    var data = {
                        mainlist_filter: '<?php echo $mainlist_filter;?>',
                        list_filter: '<?php echo $list_filter;?>',
                        department_filter: $('.filterDepartment').val(),
                        search_filter: $('.searchFilter').val(),
                        cid: '<?php echo $cid;?>',
                    };
                    loadView("creditor_list", data);
                });
            }
        })

    })
    $(".incomeReport").off("click").on("click", function(){
        var data = {
            cid: '<?php echo $cid;?>',
        };
        loadView("income_report", data);
    })
    $(".view_syncing_logs").off("click").on("click", function(){
        var data = {
            cid: '<?php echo $cid;?>',
        };
        loadView("creditor_syncing_log", data);
    })
    $(".sync_to_aptic").off("click").on("click", function(){
        var data = {
            cid: '<?php echo $cid;?>',
            sync_to_aptic: 1,
        };
        loadView("creditor_list", data);
    })
})
</script>
<style>
    .delete_session {
        cursor: pointer;
        color: #46b2e2;
        margin-left: 20px;
    }
.p_pageContent .employeeSearch {
    float: right;
    position: relative;
    margin-bottom: 0;
}
.p_pageContent .employeeSearch .employeeSearchSuggestions {
    display: none;
    background: #fff;
    position: absolute;
    width: 100%;
    max-height: 200px;
    overflow: auto;
    z-index: 2;
    border: 1px solid #dedede;
    border-top: 0;
	text-align: left;
}
.p_pageContent .employeeSearch .employeeSearchSuggestions table {
    margin-bottom: 0;
}
#p_container .p_pageContent .employeeSearch .employeeSearchSuggestions td {
    padding: 5px 10px;
}

.p_pageContent .employeeSearch .glyphicon-triangle-right {
    position: absolute;
    top: 7px;
    right: 4px;
    color: #048fcf;
}
.p_pageContent .employeeSearch .glyphicon-search {
    position: absolute;
    top: 7px;
    left: 6px;
    color: #048fcf;
}
.p_pageContent .employeeSearchInput {
    width: 250px;
    border: 1px solid #dedede;
    padding: 3px 15px 3px 25px;
}
.p_pageContent .employeeSearchInputBefore {
    width: 150px;
    border: 1px solid #dedede;
    padding: 3px 10px 3px 10px;
}
.p_pageContent .employeeSearchBtn {
    background: #0093e7;
    border-radius: 5px;
    margin-left: 3px;
    color: #fff;
    padding: 5px 15px;
    cursor: pointer;
    border: 0;
}
.p_tableFilter_left {
    width: 300px;
}
.group_by_debitor_wrapper {
    margin-top: 5px;
    margin-left: 10px;
}
#group_by_debitor {
    display: inline-block !important;
    vertical-align: middle;
    margin-top: 0;
}
#group_by_debitor_label {
    display: inline-block;
    vertical-align: middle;
    margin-bottom: 0;
}
.creditor_info {
    margin-bottom: 10px;
    background: #fff;
    padding: 10px 10px;
}
.launchImport {
    cursor: pointer;
    display: inline-block;
    vertical-align: middle;
    margin-left: 0;
}
.launchProcessingSteps {
    cursor: pointer;
}
.createCases {
    cursor: pointer;
}
.actionBtnRow {
    margin-top: 20px;
    text-align: right;
}
.creditor_info_row {
    margin-bottom: 5px;
}
.creditor_info_row label {
    display: inline-block !important;
    width: 250px;
    margin-right: 5px;
}
.title_row {
    font-size: 16px;
    margin-bottom: 15px;
}
.creditor_info_row label,
.row_info label {
    font-weight: normal !important;
}
.row_info {
    margin-bottom: 10px;
}
.row_info .output-btn {
    margin-left: 0;
    margin-right: 10px;
    width: 250px;
    text-align: center;
}
.row_info .row_info_description {
    display: inline-block;
    vertical-align: middle;
    color: #888;
}
.article-loading.lds-ring {
  display: inline-block;
  position: relative;
  width: 24px;
  height: 24px;
  margin: 10px 20px;
}
.article-loading.lds-ring div {
  box-sizing: border-box;
  display: block;
  position: absolute;
  width: 22px;
  height: 22px;
  margin: 3px;
  border: 3px solid #46b2e2;
  border-radius: 50%;
  animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
  border-color: #46b2e2 transparent transparent transparent;
}
.article-loading.lds-ring div:nth-child(1) {
  animation-delay: -0.45s;
}
.article-loading.lds-ring div:nth-child(2) {
  animation-delay: -0.3s;
}
.article-loading.lds-ring div:nth-child(3) {
  animation-delay: -0.15s;
}
@keyframes lds-ring {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
.compareWithOpenTransactions {
	margin-top: 20px;
	cursor: pointer;
	color: #46b2e2;
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
.edit_additional_bookaccount {    
	cursor: pointer;
	color: #46b2e2;
	margin-left: 10px;
}
.delete_additional_bookaccount {
	cursor: pointer;
	color: #46b2e2;
}

.select_transaction_customer {
	cursor: pointer;
	color: #46b2e2;
    margin-right: 20px;
}
.sendEmailWithReminderInfo {
    cursor: pointer;
}
.reportPage {
    cursor: pointer;
}
.reportPdf {
    cursor: pointer;
}
.open_24seven {
    cursor: pointer;
	color: #46b2e2;
    display: inline-block;
    vertical-align: middle;
    margin-left: 10px;
    font-size: 14px;
}
.checkbox_update_wrapper {
    float: right;
}
.incomeReport {
    float: right;
}
.view_syncing_logs {
    cursor: pointer;
}
.sync_to_aptic {
    cursor: pointer;
}
</style>
