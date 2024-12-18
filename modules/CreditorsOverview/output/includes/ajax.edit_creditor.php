<?php
include("fnc_password_encrypt.php");

$creditor_id = $_POST['creditor_id'] ? $o_main->db->escape_str($_POST['creditor_id']) : 0;
$action = $_POST['action'] ? $_POST['action'] : '';
$status = $_POST['status'] ? $_POST['status'] : 0;

$s_sql = "SELECT * FROM collecting_system_settings ORDER BY id ASC";
$o_query = $o_main->db->query($s_sql);
$system_settings = ($o_query ? $o_query->row_array() : array());

$s_sql = "SELECT * FROM moduledata WHERE name = '".$o_main->db->escape_str($module)."'";
$o_result = $o_main->db->query($s_sql);
$module_data = $o_result ? $o_result->row_array() : array();

include_once(__DIR__."/../../../CreditorsOverview/output/includes/fnc_process_open_cases_for_tabs.php");

$fwaFileuploadConfigs = array(
	array (
	  'module_folder' => 'CreditorsOverview', // module id in which this block is used
	  'id' => 'creditorlogofileupload',
	  'upload_type'=>'image',
	  'content_table' => 'creditor',
	  'content_field' => 'invoicelogo',
	  'content_id' => $creditor_id,
	  'content_module_id' => $module_data['uniqueID'], // id of module
	  'dropZone' => 'block',
	  'callbackAll' => 'callBackOnUploadAll',
	  'callbackStart' => 'callbackOnStart',
	  'callbackDelete' => 'callbackOnDelete',
	)
);
if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
		$lastImportedDate = "0000-00-00";
		if($_POST['lastImportedDate'] != "") {
			$lastImportedDate = date("Y-m-d", strtotime($_POST['lastImportedDate']));
		}
		$sql_where = "";
		if($_POST['24sevenoffice_password'] != ""){
			$sql_where .= ", 24sevenoffice_password = '".$o_main->db->escape_str(encrypt($_POST['24sevenoffice_password'], "uVh1eiS366"))."'";
		}
		$get_invoices_from_date = "0000-00-00";
		if($_POST['get_invoices_from_date'] != "") {
			$get_invoices_from_date = date("Y-m-d", strtotime($_POST['get_invoices_from_date']));
		}

		if($_POST['choose_progress_of_reminderprocess'] == 1){
			if($_POST['print_reminders'] == 0){
				$fw_error_msg = array($formText_CanNotChoosePrintRemindersYourselfOnAutomaticProcess_output);
				return;
			}
		}
		if(intval($_POST['use_customized_reminder_rest_note_min_amount']) == 0){
			$_POST['reminderRestNoteMinimumAmount'] = "";
		}
		if(!$_POST['use_local_email_phone_for_reminder']) {
			$_POST['local_email'] = '';
			$_POST['local_phone'] = '';
		}
        if ($creditor_id) {

            $sql = "UPDATE creditor SET
            updated = now(),
            updatedBy='".$variables->loggID."',
            moduleID = '".$o_main->db->escape_str($moduleID)."',
            process_for_handling_cases='".$o_main->db->escape_str($_POST['process_for_handling_cases'])."',
			integration_module='".$o_main->db->escape_str($_POST['integration_module'])."',
			sync_from_accounting = '".$o_main->db->escape_str($_POST['sync_from_accounting'])."',
			create_cases = '".$o_main->db->escape_str($_POST['create_cases'])."',
			vat_deduction = '".$o_main->db->escape_str($_POST['vat_deduction'])."',
			bank_account = '".$o_main->db->escape_str($_POST['bank_account'])."',
			covering_order_and_split_id = '".$o_main->db->escape_str($_POST['covering_order_and_split_id'])."',
			warning_covering_order_and_split_id = '".$o_main->db->escape_str($_POST['warning_covering_order_and_split_id'])."',
			emails_for_notification = '".$o_main->db->escape_str($_POST['emails_for_notification'])."'".$sql_where.",
			companyname = '".$o_main->db->escape_str($_POST['companyname'])."',
			companypostalbox = '".$o_main->db->escape_str($_POST['companypostalbox'])."',
			companyzipcode = '".$o_main->db->escape_str($_POST['companyzipcode'])."',
			companypostalplace = '".$o_main->db->escape_str($_POST['companypostalplace'])."',
			companyphone = '".$o_main->db->escape_str($_POST['companyphone'])."',
			companyorgnr = '".$o_main->db->escape_str($_POST['companyorgnr'])."',
			companyEmail = '".$o_main->db->escape_str($_POST['companyEmail'])."',
			send_reminder_from = '".$o_main->db->escape_str($_POST['send_reminder_from'])."',
			addCreditorPortalCodeOnLetter = '".$o_main->db->escape_str($_POST['addCreditorPortalCodeOnLetter'])."',
			choose_process_scope = '".$o_main->db->escape_str($_POST['choose_process_scope'])."',
			choose_progress_of_reminderprocess = '".$o_main->db->escape_str($_POST['choose_progress_of_reminderprocess'])."',
			choose_how_to_create_collectingcase = '".$o_main->db->escape_str($_POST['choose_how_to_create_collectingcase'])."',
			24sevenoffice_username = '".$o_main->db->escape_str($_POST['24sevenoffice_username'])."',
			tripletex_employeetoken = '".$o_main->db->escape_str($_POST['tripletex_employeetoken'])."',
			minimumAmountToPaybackToDebitor = '".$o_main->db->escape_str(str_replace(",",".",$_POST['minimumAmountToPaybackToDebitor']))."',
			maximumAmountForgiveTooLittlePayed = '".$o_main->db->escape_str(str_replace(",",".",$_POST['maximumAmountForgiveTooLittlePayed']))."',
			get_invoices_from_date = '".$o_main->db->escape_str($get_invoices_from_date)."',
			email_for_reminder_warning = '".$o_main->db->escape_str($_POST['email_for_reminder_warning'])."',
			reminder_bookaccount = '".$o_main->db->escape_str($_POST['reminder_bookaccount'])."',
			interest_bookaccount = '".$o_main->db->escape_str($_POST['interest_bookaccount'])."',
			reminder_bookaccount_project_id = '".$o_main->db->escape_str($_POST['reminder_bookaccount_project_id'])."',
			reminder_bookaccount_department_id = '".$o_main->db->escape_str($_POST['reminder_bookaccount_department_id'])."',
			interest_bookaccount_project_id = '".$o_main->db->escape_str($_POST['interest_bookaccount_project_id'])."',
			interest_bookaccount_department_id = '".$o_main->db->escape_str($_POST['interest_bookaccount_department_id'])."',
			invoice_bookaccount_project_id = '".$o_main->db->escape_str($_POST['invoice_bookaccount_project_id'])."',
			invoice_bookaccount_department_id = '".$o_main->db->escape_str($_POST['invoice_bookaccount_department_id'])."',
			loss_bookaccount = '".$o_main->db->escape_str($_POST['loss_bookaccount'])."',
			print_reminders = '".$o_main->db->escape_str($_POST['print_reminders'])."',
			reminder_process_for_person = '".$o_main->db->escape_str($_POST['reminder_process_for_person'])."',
			reminder_process_for_company = '".$o_main->db->escape_str($_POST['reminder_process_for_company'])."',
			collecting_process_for_company = '".$o_main->db->escape_str($_POST['collecting_process_for_company'])."',
			collecting_process_for_person = '".$o_main->db->escape_str($_POST['collecting_process_for_person'])."',
			use_customized_reminder_rest_note_min_amount = '".$o_main->db->escape_str($_POST['use_customized_reminder_rest_note_min_amount'])."',
			reminderRestNoteMinimumAmount = '".$o_main->db->escape_str(str_replace(",",".",$_POST['reminderRestNoteMinimumAmount']))."',
			reminder_only_from_invoice_nr = '".$o_main->db->escape_str($_POST['reminder_only_from_invoice_nr'])."',
			creditor_reminder_default_profile_for_company_id ='".$o_main->db->escape_str($_POST['creditor_reminder_default_profile_for_company_id'])."',
			creditor_reminder_default_profile_id ='".$o_main->db->escape_str($_POST['creditor_reminder_default_profile_id'])."',
			choose_move_to_collecting_process ='".$o_main->db->escape_str($_POST['choose_move_to_collecting_process'])."',
			reminder_system_edition ='".$o_main->db->escape_str($_POST['reminder_system_edition'])."',
			activateLinkToEsp = '".$o_main->db->escape_str($_POST['activateLinkToEsp'])."',
			invoice_email_priority = '".$o_main->db->escape_str($_POST['invoice_email_priority'])."',
			collecting_process_to_move_from_reminder = '".$o_main->db->escape_str($_POST['collecting_process_to_move_from_reminder'])."',
			collecting_case_reminder_fee_level = '".$o_main->db->escape_str($_POST['collecting_case_reminder_fee_level'])."',
			skip_reminder_go_directly_to_collecting = '".$o_main->db->escape_str($_POST['skip_reminder_go_directly_to_collecting'])."',
			is_demo = '".$o_main->db->escape_str($_POST['is_demo'])."',
			use_local_email_phone_for_reminder = '".$o_main->db->escape_str($_POST['use_local_email_phone_for_reminder'])."',
			local_email = '".$o_main->db->escape_str($_POST['local_email'])."',
			local_phone = '".$o_main->db->escape_str($_POST['local_phone'])."',
			onboarding_incomplete = '".$o_main->db->escape_str($_POST['onboarding_incomplete'])."',
			sms_sendername = '".$o_main->db->escape_str($_POST['sms_sendername'])."',
			checkbox_1 = '".$o_main->db->escape_str($_POST['checkbox_1'])."',
			default_collecting_late_fee = '".$o_main->db->escape_str($_POST['default_collecting_late_fee'])."',
			billing_type = '".$o_main->db->escape_str($_POST['billing_type'])."',
			billing_percent = '".$o_main->db->escape_str(str_replace(",",".",$_POST['billing_percent']))."',
			billing_percent_fees = '".$o_main->db->escape_str(str_replace(",",".",$_POST['billing_percent_fees']))."',
			billing_percent_interest = '".$o_main->db->escape_str(str_replace(",",".",$_POST['billing_percent_interest']))."',
			force_approve_terms = '".$o_main->db->escape_str($_POST['force_approve_terms'])."',
			bookaccount_upper_range = '".$o_main->db->escape_str($_POST['bookaccount_upper_range'])."',
			show_transfer_to_collecting_company_in_ready_to_send = '".$o_main->db->escape_str($_POST['show_transfer_to_collecting_company_in_ready_to_send'])."',
			activate_project_code_in_reminderletter = '".$o_main->db->escape_str($_POST['activate_project_code_in_reminderletter'])."',
			activate_reminder_overview_report = '".$o_main->db->escape_str($_POST['activate_reminder_overview_report'])."',
			warning_level_fee_for_person_reduced_by_one = '".$o_main->db->escape_str($_POST['warning_level_fee_for_person_reduced_by_one'])."',
			activate_aptic = '".$o_main->db->escape_str($_POST['activate_aptic'])."'
            WHERE id = $creditor_id";
			$o_query = $o_main->db->query($sql);
			$insert_id = $creditor_id;
			// $sql = "DELETE creditor_manualprocess_connection FROM creditor_manualprocess_connection WHERE creditor_id = '".$o_main->db->escape_str($creditor_id)."'";
			// $o_query = $o_main->db->query($sql);
			// foreach($_POST['process_manual'] as $process_manual) {
			// 	if($_POST['process_for_handling_cases'] != $process_manual){
			// 		$sql = "INSERT INTO creditor_manualprocess_connection SET
		    //         created = now(),
		    //         createdBy='".$variables->loggID."',
		    //         moduleID = '".$o_main->db->escape_str($moduleID)."',
		    //         creditor_id='".$o_main->db->escape_str($creditor_id)."',
		    //         process_id='".$o_main->db->escape_str($process_manual)."'";
			// 		$o_query = $o_main->db->query($sql);
			// 	}
			// }
            $fw_redirect_url = $_POST['redirect_url'];
        } else {
			$profileForPersonId = "";
			$profileForCompanyId = "";

			$o_query = $o_main->db->query("SELECT * FROM collecting_cases_process WHERE id = ?", array($_POST['person_process']));
			$process_for_person = $o_query ? $o_query->row_array() : array();

			$o_query = $o_main->db->query("SELECT * FROM collecting_cases_process WHERE id = ?", array($_POST['company_process']));
			$process_for_company = $o_query ? $o_query->row_array() : array();

			if($process_for_person && $process_for_company){
				$sql = "INSERT INTO creditor SET
				created = now(),
				createdBy='".$variables->loggID."',
				moduleID = '".$o_main->db->escape_str($moduleID)."',
				process_for_handling_cases='".$o_main->db->escape_str($_POST['process_for_handling_cases'])."',
				integration_module='".$o_main->db->escape_str($_POST['integration_module'])."',
				sync_from_accounting = '".$o_main->db->escape_str($_POST['sync_from_accounting'])."',
				create_cases = '".$o_main->db->escape_str($_POST['create_cases'])."',
				vat_deduction = '".$o_main->db->escape_str($_POST['vat_deduction'])."',
				bank_account = '".$o_main->db->escape_str($_POST['bank_account'])."',
				covering_order_and_split_id = '".$o_main->db->escape_str($_POST['covering_order_and_split_id'])."',
				warning_covering_order_and_split_id = '".$o_main->db->escape_str($_POST['warning_covering_order_and_split_id'])."',
				emails_for_notification = '".$o_main->db->escape_str($_POST['emails_for_notification'])."'".$sql_where.",
				companyname = '".$o_main->db->escape_str($_POST['companyname'])."',
				companypostalbox = '".$o_main->db->escape_str($_POST['companypostalbox'])."',
				companyzipcode = '".$o_main->db->escape_str($_POST['companyzipcode'])."',
				companypostalplace = '".$o_main->db->escape_str($_POST['companypostalplace'])."',
				companyphone = '".$o_main->db->escape_str($_POST['companyphone'])."',
				companyorgnr = '".$o_main->db->escape_str($_POST['companyorgnr'])."',
				companyEmail = '".$o_main->db->escape_str($_POST['companyEmail'])."',
				send_reminder_from = '".$o_main->db->escape_str($_POST['send_reminder_from'])."',
				addCreditorPortalCodeOnLetter = '".$o_main->db->escape_str($_POST['addCreditorPortalCodeOnLetter'])."',
				choose_process_scope = '".$o_main->db->escape_str($_POST['choose_process_scope'])."',
				choose_progress_of_reminderprocess = '".$o_main->db->escape_str($_POST['choose_progress_of_reminderprocess'])."',
				choose_how_to_create_collectingcase = '".$o_main->db->escape_str($_POST['choose_how_to_create_collectingcase'])."',
				24sevenoffice_username = '".$o_main->db->escape_str($_POST['24sevenoffice_username'])."',
				tripletex_employeetoken = '".$o_main->db->escape_str($_POST['tripletex_employeetoken'])."',
				minimumAmountToPaybackToDebitor = '".$o_main->db->escape_str(str_replace(",",".",$_POST['minimumAmountToPaybackToDebitor']))."',
				maximumAmountForgiveTooLittlePayed = '".$o_main->db->escape_str(str_replace(",",".",$_POST['maximumAmountForgiveTooLittlePayed']))."',
				get_invoices_from_date = '".$o_main->db->escape_str($get_invoices_from_date)."',
				email_for_reminder_warning = '".$o_main->db->escape_str($_POST['email_for_reminder_warning'])."',
				reminder_bookaccount = '".$o_main->db->escape_str($_POST['reminder_bookaccount'])."',
				interest_bookaccount = '".$o_main->db->escape_str($_POST['interest_bookaccount'])."',
				reminder_bookaccount_project_id = '".$o_main->db->escape_str($_POST['reminder_bookaccount_project_id'])."',
				reminder_bookaccount_department_id = '".$o_main->db->escape_str($_POST['reminder_bookaccount_department_id'])."',
				loss_bookaccount = '".$o_main->db->escape_str($_POST['loss_bookaccount'])."',
				interest_bookaccount_project_id = '".$o_main->db->escape_str($_POST['interest_bookaccount_project_id'])."',
				interest_bookaccount_department_id = '".$o_main->db->escape_str($_POST['interest_bookaccount_department_id'])."',
				invoice_bookaccount_project_id = '".$o_main->db->escape_str($_POST['invoice_bookaccount_project_id'])."',
				invoice_bookaccount_department_id = '".$o_main->db->escape_str($_POST['invoice_bookaccount_department_id'])."',
				print_reminders = '".$o_main->db->escape_str($_POST['print_reminders'])."',
				reminder_process_for_person = '".$o_main->db->escape_str($_POST['reminder_process_for_person'])."',
				reminder_process_for_company = '".$o_main->db->escape_str($_POST['reminder_process_for_company'])."',
				collecting_process_for_company = '".$o_main->db->escape_str($_POST['collecting_process_for_company'])."',
				collecting_process_for_person = '".$o_main->db->escape_str($_POST['collecting_process_for_person'])."',
				use_customized_reminder_rest_note_min_amount = '".$o_main->db->escape_str($_POST['use_customized_reminder_rest_note_min_amount'])."',
				reminderRestNoteMinimumAmount = '".$o_main->db->escape_str(str_replace(",",".",$_POST['reminderRestNoteMinimumAmount']))."',
				reminder_only_from_invoice_nr = '".$o_main->db->escape_str($_POST['reminder_only_from_invoice_nr'])."',
				choose_move_to_collecting_process ='".$o_main->db->escape_str($_POST['choose_move_to_collecting_process'])."',
				reminder_system_edition ='".$o_main->db->escape_str($_POST['reminder_system_edition'])."',
				activateLinkToEsp = '".$o_main->db->escape_str($_POST['activateLinkToEsp'])."',
				invoice_email_priority = '".$o_main->db->escape_str($_POST['invoice_email_priority'])."',
				collecting_process_to_move_from_reminder = '".$o_main->db->escape_str($_POST['collecting_process_to_move_from_reminder'])."',
				collecting_case_reminder_fee_level = '".$o_main->db->escape_str($_POST['collecting_case_reminder_fee_level'])."',
				skip_reminder_go_directly_to_collecting = '".$o_main->db->escape_str($_POST['skip_reminder_go_directly_to_collecting'])."',
				is_demo = '".$o_main->db->escape_str($_POST['is_demo'])."',
				use_local_email_phone_for_reminder = '".$o_main->db->escape_str($_POST['use_local_email_phone_for_reminder'])."',
				local_email = '".$o_main->db->escape_str($_POST['local_email'])."',
				local_phone = '".$o_main->db->escape_str($_POST['local_phone'])."',
				sms_sendername = '".$o_main->db->escape_str($_POST['sms_sendername'])."',
				checkbox_1 = '".$o_main->db->escape_str($_POST['checkbox_1'])."',
				default_collecting_late_fee = '".$o_main->db->escape_str($_POST['default_collecting_late_fee'])."',
				billing_type = '".$o_main->db->escape_str($_POST['billing_type'])."',
				billing_percent = '".$o_main->db->escape_str(str_replace(",",".",$_POST['billing_percent']))."',
				force_approve_terms = '".$o_main->db->escape_str($_POST['force_approve_terms'])."',
				bookaccount_upper_range = '".$o_main->db->escape_str($_POST['bookaccount_upper_range'])."',
				show_transfer_to_collecting_company_in_ready_to_send = '".$o_main->db->escape_str($_POST['show_transfer_to_collecting_company_in_ready_to_send'])."',
				activate_project_code_in_reminderletter = '".$o_main->db->escape_str($_POST['activate_project_code_in_reminderletter'])."',
				activate_reminder_overview_report = '".$o_main->db->escape_str($_POST['activate_reminder_overview_report'])."',
				warning_level_fee_for_person_reduced_by_one = '".$o_main->db->escape_str($_POST['warning_level_fee_for_person_reduced_by_one'])."',
				activate_aptic = '".$o_main->db->escape_str($_POST['activate_aptic'])."'";

				$o_query = $o_main->db->query($sql);
				$insert_id = $o_main->db->insert_id();

				// foreach($_POST['process_manual'] as $process_manual) {
				// 	if($_POST['process_for_handling_cases'] != $process_manual){
				// 		$sql = "INSERT INTO creditor_manualprocess_connection SET
				//         created = now(),
				//         createdBy='".$variables->loggID."',
				//         moduleID = '".$o_main->db->escape_str($moduleID)."',
				//         creditor_id='".$o_main->db->escape_str($insert_id)."',
				//         process_id='".$o_main->db->escape_str($process_manual)."'";
				// 		$o_query = $o_main->db->query($sql);
				// 	}
				// }
				$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$insert_id;
			} else {
				$fw_error_msg = array($formText_MissingProfile_output);
				return;
			}
		}

		if($insert_id > 0){			
			if($process_for_person){
				$s_sql = "INSERT INTO creditor_reminder_custom_profiles SET
				created = NOW(),
				createdBy = '".$o_main->db->escape_str("onboarding")."',
				name = '".$o_main->db->escape_str($process_for_person['name'])."',
				creditor_id = '".$o_main->db->escape_str($insert_id)."',
				reminder_process_id = '".$o_main->db->escape_str($process_for_person['id'])."'";
				$o_query = $o_main->db->query($s_sql);
				if($o_query){
					$profileForPersonId = $o_main->db->insert_id();
				}
			}

			if($process_for_company){
				$s_sql = "INSERT INTO creditor_reminder_custom_profiles SET
				created = NOW(),
				createdBy = '".$o_main->db->escape_str("onboarding")."',
				name = '".$o_main->db->escape_str($process_for_company['name'])."',
				creditor_id = '".$o_main->db->escape_str($insert_id)."',
				reminder_process_id = '".$o_main->db->escape_str($process_for_company['id'])."'";
				$o_query = $o_main->db->query($s_sql);
				if($o_query){
					$profileForCompanyId = $o_main->db->insert_id();
				}
			}
			$s_sql = "UPDATE creditor_customized_fees SET updated = NOW(), updatedBy='".$variables->loggID."',			
			creditor_reminder_default_profile_for_company_id ='".$o_main->db->escape_str($profileForCompanyId)."',
			creditor_reminder_default_profile_id ='".$o_main->db->escape_str($profileForPersonId)."'
			WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($insert_id));
						


			$s_sql = "SELECT * FROM creditor_customized_fees WHERE creditor_id = ? AND type=0 ORDER BY id";
			$o_query = $o_main->db->query($s_sql, array($insert_id));
			$customized_values = ($o_query ? $o_query->result_array() : array());

			$s_sql = "SELECT * FROM creditor_customized_fees WHERE creditor_id = ? AND type=1 ORDER BY id";
			$o_query = $o_main->db->query($s_sql, array($insert_id));
			$customized_values_collecting = ($o_query ? $o_query->result_array() : array());

		    $s_sql = "SELECT * FROM collecting_cases_process WHERE id = ? ORDER BY sortnr ASC";
		    $o_query = $o_main->db->query($s_sql, array($_POST['reminder_process_for_company']));
		    $reminder_level_case_company = ($o_query ? $o_query->row_array() : array());

		    $s_sql = "SELECT * FROM collecting_cases_process WHERE id = ? ORDER BY sortnr ASC";
		    $o_query = $o_main->db->query($s_sql, array($_POST['reminder_process_for_person']));
		    $reminder_level_case_person = ($o_query ? $o_query->row_array() : array());

		    $s_sql = "SELECT * FROM collecting_cases_process WHERE id = ? ORDER BY sortnr ASC";
		    $o_query = $o_main->db->query($s_sql, array($_POST['collecting_process_for_company']));
		    $collecting_level_case_company = ($o_query ? $o_query->row_array() : array());

		    $s_sql = "SELECT * FROM collecting_cases_process WHERE id = ? ORDER BY sortnr ASC";
		    $o_query = $o_main->db->query($s_sql, array($_POST['collecting_process_for_person']));
		    $collecting_level_case_person = ($o_query ? $o_query->row_array() : array());

			$reminder_company_price_array = array();
			$reminder_person_price_array = array();

			$steps_customized = array();
			if($reminder_level_case_company){
				foreach($_POST['customer_step_reminder_company'] as $index => $step_id){
					if($step_id > 0){
						if(!in_array($step_id, $steps_customized)){
							$steps_customized[] = $step_id;
						}
						$reminder_company_price_array[$step_id] = $_POST['amount_company_'.$step_id][0];
					}
				}
			}
			if($reminder_level_case_person){
				foreach($_POST['customer_step_reminder_person'] as $index => $step_id){
					if($step_id > 0){
						if(!in_array($step_id, $steps_customized)){
							$steps_customized[] = $step_id;
						}
						$reminder_person_price_array[$step_id] = $_POST['amount_person_'.$step_id][0];
					}
				}
			}
			foreach($customized_values as $customized_value) {
				if(!in_array($customized_value['collecting_cases_process_step_id'], $steps_customized)){
					$s_sql = "DELETE FROM creditor_customized_fees WHERE id = ?";
					$o_query = $o_main->db->query($s_sql, array($customized_value['id']));
				}
			}
			foreach($steps_customized as $step_id){
				if($step_id > 0){
					$s_sql = "SELECT * FROM creditor_customized_fees WHERE creditor_id = ? AND collecting_cases_process_step_id = ? AND type = 0";
					$o_query = $o_main->db->query($s_sql, array($insert_id, $step_id));
					$customized_value = ($o_query ? $o_query->row_array() : array());

					$reminder_company_price = $reminder_company_price_array[$step_id];
					$reminder_person_price = $reminder_person_price_array[$step_id];
					$collecting_fee_level = $collecting_fee_level_array[$step_id];

					if($reminder_company_price != null || $reminder_person_price != null) {
						if($customized_value){
							$s_sql = "UPDATE creditor_customized_fees SET updated = NOW(), updatedBy='".$variables->loggID."',
							creditor_id = ?,
							collecting_cases_process_step_id = ?,
							amount_person = ?,
							amount_company = ?
							WHERE id = ?";
							$o_query = $o_main->db->query($s_sql, array($insert_id, $step_id, $reminder_person_price, $reminder_company_price, $customized_value['id']));
						} else {
							$s_sql = "INSERT INTO creditor_customized_fees SET created = NOW(), createdBy='".$variables->loggID."',
							creditor_id = ?,
							collecting_cases_process_step_id = ?,
							amount_person = ?,
							amount_company = ?,
							type = 0";
							$o_query = $o_main->db->query($s_sql, array($insert_id, $step_id, $reminder_person_price, $reminder_company_price));
						}
					}
				}
			}


			$collecting_company_price_array = array();
			$collecting_person_price_array = array();
			$collecting_fee_level_array = array();
			$collecting_fee_level_person_array = array();
			$steps_customized = array();
			if($collecting_level_case_company){
				foreach($_POST['customer_step_collecting_company'] as $index => $step_id){
					if($step_id > 0){
						if(!in_array($step_id, $steps_customized)){
							$steps_customized[] = $step_id;
						}
						$collecting_company_price_array[$step_id] = $_POST['collecting_amount_company_'.$step_id][0];
						$collecting_fee_level_array[$step_id] = $_POST['fee_level_'.$step_id][0];
					}
				}
			}
			if($collecting_level_case_person){
				foreach($_POST['customer_step_collecting_person'] as $index => $step_id){
					if($step_id > 0){
						if(!in_array($step_id, $steps_customized)){
							$steps_customized[] = $step_id;
						}
						$collecting_person_price_array[$step_id] = $_POST['collecting_amount_person_'.$step_id][0];
						$collecting_fee_level_person_array[$step_id] = $_POST['fee_level_person_'.$step_id][0];
					}
				}
			}
			foreach($customized_values_collecting as $customized_value) {
				if(!in_array($customized_value['collecting_cases_process_step_id'], $steps_customized)){
					$s_sql = "DELETE FROM creditor_customized_fees WHERE id = ?";
					$o_query = $o_main->db->query($s_sql, array($customized_value['id']));
				}
			}
			foreach($steps_customized as $step_id){
				if($step_id > 0){
					$s_sql = "SELECT * FROM creditor_customized_fees WHERE creditor_id = ? AND collecting_cases_process_step_id = ? AND type = 1";
					$o_query = $o_main->db->query($s_sql, array($insert_id, $step_id));
					$customized_value = ($o_query ? $o_query->row_array() : array());

					$collecting_company_price = $collecting_company_price_array[$step_id];
					$collecting_person_price = $collecting_person_price_array[$step_id];
					$collecting_fee_level = $collecting_fee_level_array[$step_id];
					$collecting_fee_level_person = $collecting_fee_level_person_array[$step_id];
					if($collecting_company_price != null || $collecting_person_price != null){
						if($customized_value){
							$s_sql = "UPDATE creditor_customized_fees SET updated = NOW(), updatedBy='".$variables->loggID."',
							creditor_id = ?,
							collecting_cases_process_step_id = ?,
							amount_person = ?,
							amount_company = ?,
							collecting_fee_level = ?,
							collecting_fee_level_person = ?
							WHERE id = ?";
							$o_query = $o_main->db->query($s_sql, array($insert_id, $step_id, $collecting_person_price, $collecting_company_price, $collecting_fee_level, $collecting_fee_level_person, $customized_value['id']));
						} else {
							$s_sql = "INSERT INTO creditor_customized_fees SET created = NOW(), createdBy='".$variables->loggID."',
							creditor_id = ?,
							collecting_cases_process_step_id = ?,
							amount_person = ?,
							amount_company = ?,
							collecting_fee_level = ?,
							collecting_fee_level_person = ?,
							type = 1";
							$o_query = $o_main->db->query($s_sql, array($insert_id, $step_id, $collecting_person_price, $collecting_company_price, $collecting_fee_level, $collecting_fee_level_person));
						}
					}
				}
			}
			
			//trigger reordering 	

			$s_sql = "UPDATE creditor_transactions SET to_be_reordered = 1
			WHERE creditor_id = '".$o_main->db->escape_str($insert_id)."' AND open = 1 AND IFNULL(tab_status, 0) <> 0";
			$o_query = $o_main->db->query($s_sql);
			process_open_cases_for_tabs($insert_id, 1);
		}
		foreach($fwaFileuploadConfigs as $fwaFileuploadConfig) {
			$fieldName = $fwaFileuploadConfig['id'];
			$fwaFileuploadConfig['content_id'] = $insert_id;
			include( __DIR__ . "/fileupload10/contentreg.php");
		}
	}
}
if($action == "deleteCreditor" && $creditor_id) {
    $sql = "DELETE FROM creditor
    WHERE id = $creditor_id";
    $o_query = $o_main->db->query($sql);
}
if($creditor_id) {
    $sql = "SELECT * FROM creditor WHERE id = $creditor_id";
	$o_query = $o_main->db->query($sql);
    $projectData = $o_query ? $o_query->row_array() : array();


	$selectedProcesses = array();

	$defaultSelectedProcessId = $projectData['process_for_handling_cases'];

	$s_sql = "SELECT creditor_manualprocess_connection.* FROM creditor_manualprocess_connection WHERE creditor_manualprocess_connection.creditor_id = ?";
    $o_query = $o_main->db->query($s_sql, array($projectData['id']));
    $connections = ($o_query ? $o_query->result_array() : array());
	foreach($connections as $connection) {
		array_push($selectedProcesses, $connection['process_id']);
	}
}

$s_sql = "SELECT * FROM collecting_cases_process ORDER BY sortnr ASC";
$o_query = $o_main->db->query($s_sql);
$all_reminder_processes = ($o_query ? $o_query->result_array() : array());


$s_sql = "SELECT * FROM collecting_cases_collecting_process ORDER BY sortnr ASC";
$o_query = $o_main->db->query($s_sql);
$all_collecting_processes = ($o_query ? $o_query->result_array() : array());

?>

<div class="popupform popupform-<?php echo $creditor_id;?>">
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_creditor";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="creditor_id" value="<?php echo $creditor_id;?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$creditor_id; ?>">
		<div class="inner">

			<div class="line">
				<div class="lineTitle"><?php echo $formText_SyncronizeAllInvoicesFromAccounting_Output; ?></div>
				<div class="lineInput">
					<select name="sync_from_accounting" class="sync_from_accounting" required>
						<option value=""><?php echo $formText_Select_output;?></option>
						<option value="0"  <?php if($projectData['sync_from_accounting'] == 0) echo 'selected';?>><?php echo $formText_No_output;?></option>
						<option value="1"  <?php if($projectData['sync_from_accounting'] == 1) echo 'selected';?>><?php echo $formText_Yes_output;?></option>
					</select>
				</div>
				<div class="clear"></div>
			</div>
    		<div class="line sync_from_accounting_trigger">
        		<div class="lineTitle"><?php echo $formText_IntegrationModule_Output; ?></div>
        		<div class="lineInput">
					<select name="integration_module" class="integration_module_select">
						<option value=""><?php echo $formText_Select_output;?></option>
						<option value="Integration24SevenOffice" <?php if($projectData['integration_module'] == "Integration24SevenOffice") echo 'selected';?>><?php echo $formText_Integration24SevenOffice_output;?></option>
						<option value="IntegrationTripletex" <?php if($projectData['integration_module'] == "IntegrationTripletex") echo 'selected';?>><?php echo $formText_IntegrationTripletex_output;?></option>
					</select>
                </div>
        		<div class="clear"></div>
    		</div>
			<div class="sevenOfficeWrapper">
				<div class="line">
					<div class="lineTitle"><?php echo $formText_24sevenofficeUsername_Output; ?></div>
					<div class="lineInput">
						<input type="text" class="popupforminput botspace" autocomplete="off" name="24sevenoffice_username" value="<?php echo $projectData['24sevenoffice_username']; ?>" >
					</div>
					<div class="clear"></div>
				</div>
				<div class="line">
					<div class="lineTitle"><?php echo $formText_24sevenofficePassword_Output; ?></div>
					<div class="lineInput">
						<?php if($projectData['24sevenoffice_password'] != "") { ?>
							<div class="updatePassword"><?php echo $formText_UpdatePassword_output;?></div>
						<?php } ?>
						<input type="password" <?php if($projectData['24sevenoffice_password'] != "") { ?> style="display:none;" <?php } ?> class="popupforminput botspace passwordInput" autocomplete="off" name="24sevenoffice_password" value="" >
					</div>
					<div class="clear"></div>
				</div>
			</div>
			<div class="tripletexWrapper">
				<div class="line">
					<div class="lineTitle"><?php echo $formText_tripletetEmployeetoken_Output; ?></div>
					<div class="lineInput">
						<input type="text" class="popupforminput botspace" autocomplete="off" name="tripletex_employeetoken" value="<?php echo $projectData['tripletex_employeetoken']; ?>" >
					</div>
					<div class="clear"></div>
				</div>
			</div>


			<?php if($projectData['integration_module'] != "") { ?>
				<div class="line sevenOfficeWrapper">
					<div class="lineTitle"><?php echo $formText_ClientId_Output;?></div>
					<div class="lineInput">
						<span class="clientWrapper">
				            <?php echo $projectData['24sevenoffice_client_id'];?>
						</span>
					</div>
					<div class="clear"></div>
		        </div>
				<div class="line sevenOfficeWrapper">
					<div class="lineTitle"><?php echo $formText_EntityId_Output;?></div>
					<div class="lineInput">
						<span class="entityWrapper">
				            <?php echo $projectData['entity_id'];?>
						</span>
		            	<div class="updateEntityId output-btn" data-creditor-id="<?php echo $projectData['id']?>"><?php echo $formText_UpdateEntityId_output?></div>
					</div>
					<div class="clear"></div>
		        </div>
			<?php } ?>

			<div class="line">
				<div class="lineTitle"><?php echo $formText_CoveringOrderAndSplit_Output; ?></div>
				<div class="lineInput">
					<?php
				    $s_sql = "SELECT * FROM covering_order_and_split ORDER BY sortnr ASC";
				    $o_query = $o_main->db->query($s_sql);
				    $coveringOrders = ($o_query ? $o_query->result_array() : array());
					?>
					<select name="covering_order_and_split_id"  class="covering_order_and_split_id" required autocomplete="off">
						<option value=""><?php echo $formText_Select_output;?></option>
						<?php foreach($coveringOrders as $coveringOrder) { ?>
							<option value="<?php echo $coveringOrder['id'];?>" <?php if($coveringOrder['id'] == $projectData['covering_order_and_split_id']) echo 'selected';?>>
								<?php echo $coveringOrder['name'];?>
							</option>
						<?php } ?>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_WarningCoveringOrderAndSplit_Output; ?></div>
				<div class="lineInput">
					<?php
				    $s_sql = "SELECT * FROM covering_order_and_split ORDER BY sortnr ASC";
				    $o_query = $o_main->db->query($s_sql);
				    $coveringOrders = ($o_query ? $o_query->result_array() : array());
					?>
					<select name="warning_covering_order_and_split_id"  class="covering_order_and_split_id" required autocomplete="off">
						<option value=""><?php echo $formText_Select_output;?></option>
						<?php foreach($coveringOrders as $coveringOrder) { ?>
							<option value="<?php echo $coveringOrder['id'];?>" <?php if($coveringOrder['id'] == $projectData['warning_covering_order_and_split_id']) echo 'selected';?>>
								<?php echo $coveringOrder['name'];?>
							</option>
						<?php } ?>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			<?php /*
			<div class="line">
				<div class="lineTitle"><?php echo $formText_CreateCases_Output; ?></div>
				<div class="lineInput">
					<select name="create_cases" class="create_cases" required>
						<option value=""><?php echo $formText_Select_output;?></option>
						<option value="0" <?php if($projectData['create_cases'] == 0) echo 'selected';?>><?php echo $formText_Automatic_output;?></option>
						<option value="1" <?php if($projectData['create_cases'] == 1) echo 'selected';?>><?php echo $formText_Manual_output;?></option>
					</select>
				</div>
				<div class="clear"></div>
			</div>

			<div class="line">
				<div class="lineTitle"><span class="automatic_wrapper"><?php echo $formText_DaysOverdueStartCase_Output; ?></span></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="days_overdue_startcase" value="<?php echo $projectData['days_overdue_startcase']; ?>" >
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_DefaultProcessForHandlingCases_Output; ?></div>
				<div class="lineInput">
					<?php
				    $s_sql = "SELECT * FROM collecting_cases_process ORDER BY sortnr ASC";
				    $o_query = $o_main->db->query($s_sql);
				    $processes = ($o_query ? $o_query->result_array() : array());
					?>
					<select name="process_for_handling_cases"  class="default_process_manual" required>
						<option value=""><?php echo $formText_Select_output;?></option>
						<?php foreach($processes as $process) { ?>
							<option value="<?php echo $process['id'];?>" <?php if($process['id'] == $projectData['process_for_handling_cases']) echo 'selected';?>>
								<?php echo $process['name'];?>
							</option>
						<?php } ?>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_AdditionalCasesForSuggestedCases_Output; ?></div>
				<div class="lineInput">
					<?php
				    $s_sql = "SELECT * FROM collecting_cases_process ORDER BY sortnr ASC";
				    $o_query = $o_main->db->query($s_sql);
				    $processes = ($o_query ? $o_query->result_array() : array());
					?>
					<select name="process_manual[]" class="process_manual" multiple="multiple">
						<?php foreach($processes as $process) { ?>
							<option value="<?php echo $process['id'];?>" class="process_manual_<?php echo $process['id'];?>" <?php if(in_array($process['id'], $selectedProcesses)) echo 'selected';?>>
								<?php echo $process['name'];?>
							</option>
						<?php } ?>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			*/?>

			<div class="line">
				<div class="lineTitle"><?php echo $formText_VatDeduction_Output; ?></div>
				<div class="lineInput">
					<input type="checkbox" class="popupforminput botspace checkbox" autocomplete="off" name="vat_deduction" value="1" <?php if($projectData['vat_deduction']) echo 'checked'; ?>>
				</div>
				<div class="clear"></div>
			</div>

			<div class="line">
				<div class="lineTitle"><?php echo $formText_BankAccount_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="bank_account" value="<?php echo $projectData['bank_account']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_BookaccountUpperRange_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="bookaccount_upper_range" value="<?php echo $projectData['bookaccount_upper_range']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>
			
			<?php /*?>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_SenderName_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="sender_name" value="<?php echo $projectData['sender_name']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_SenderEmail_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="sender_email" value="<?php echo $projectData['sender_email']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>*/?>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_EmailsForNotification_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="emails_for_notification" value="<?php echo $projectData['emails_for_notification']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_SendReminderFrom_output; ?></div>
				<div class="lineInput">
					<select name="send_reminder_from" class="send_reminder_fromSelect popupforminput botspace" autocomplete="off">
						<option value="0">
							<?php echo $formText_CreditorCompany_output;?>
						</option>
						<option value="1" <?php if($projectData['send_reminder_from'] == 1) echo 'selected';?>>
							<?php echo $formText_CollectingCompany_output;?>
						</option>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line addCreditorPortalCodeOnLetterWrapper">
				<div class="lineTitle"><?php echo $formText_AddCreditorPortalCodeOnLetter_output; ?></div>
				<div class="lineInput">
					<select name="addCreditorPortalCodeOnLetter" class="popupforminput botspace" autocomplete="off">
						<option value="0">
							<?php echo $formText_Yes_output;?>
						</option>
						<option value="1" <?php if($projectData['addCreditorPortalCodeOnLetter'] == 1) echo 'selected';?>>
							<?php echo $formText_No_output;?>
						</option>
					</select>
				</div>
				<div class="clear"></div>
			</div>

			<div class="line">
				<div class="lineTitle"><?php echo $formText_BookaccountForReminder_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="reminder_bookaccount" value="<?php echo $projectData['reminder_bookaccount']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_BookaccountForInterest_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="interest_bookaccount" value="<?php echo $projectData['interest_bookaccount']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>

			<div class="line">
				<div class="lineTitle"><?php echo $formText_ReminderBookaccountProjectId_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="reminder_bookaccount_project_id" value="<?php echo $projectData['reminder_bookaccount_project_id']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>

			<div class="line">
				<div class="lineTitle"><?php echo $formText_ReminderBookaccountDepartmentId_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="reminder_bookaccount_department_id" value="<?php echo $projectData['reminder_bookaccount_department_id']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>

			<div class="line">
				<div class="lineTitle"><?php echo $formText_InterestBookaccountProjectId_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="interest_bookaccount_project_id" value="<?php echo $projectData['interest_bookaccount_project_id']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_InterestBookaccountDepartmentId_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="interest_bookaccount_department_id" value="<?php echo $projectData['interest_bookaccount_department_id']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_InvoiceBookaccountProjectId_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="invoice_bookaccount_project_id" value="<?php echo $projectData['invoice_bookaccount_project_id']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_InvoiceBookaccountDepartmentId_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="invoice_bookaccount_department_id" value="<?php echo $projectData['invoice_bookaccount_department_id']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_BookaccountForLoss_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="loss_bookaccount" value="<?php echo $projectData['loss_bookaccount']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>
			<?php /*?>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_PersonReminderFee_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off"  name="person_reminder_fee" value="<?php echo str_replace(".", ",", $projectData['person_reminder_fee']); ?>"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_CompanyReminderFee_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off"  name="company_reminder_fee" value="<?php echo str_replace(".", ",", $projectData['company_reminder_fee']); ?>"/>
				</div>
				<div class="clear"></div>
			</div>*/?>

			<div class="line">
				<div class="lineTitle"><?php echo $formText_InvoiceLogo_output; ?></div>
				<div class="lineInput">
					<?php
			        $fwaFileuploadConfig = $fwaFileuploadConfigs[0];
			        require __DIR__ . '/fileupload10/output.php';
			        ?>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_CompanyName_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="companyname" value="<?php echo $projectData['companyname']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_CompanyPostalBox_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="companypostalbox" value="<?php echo $projectData['companypostalbox']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_CompanyZipCode_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="companyzipcode" value="<?php echo $projectData['companyzipcode']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_CompanyPostalPlace_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="companypostalplace" value="<?php echo $projectData['companypostalplace']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_CompanyPhone_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="companyphone" value="<?php echo $projectData['companyphone']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_CompanyOrgNr_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="companyorgnr" value="<?php echo $projectData['companyorgnr']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_CompanyEmail_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="companyEmail" value="<?php echo $projectData['companyEmail']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>
			<?php /*?>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_ActivateReminderProcessForCompany_Output; ?></div>
				<div class="lineInput">
					<?php
				    $s_sql = "SELECT * FROM collecting_cases_process WHERE available_for = 2 OR available_for = 3 ORDER BY sortnr ASC";
				    $o_query = $o_main->db->query($s_sql);
				    $processes = ($o_query ? $o_query->result_array() : array());
					?>
					<select name="reminder_process_for_company"  class="process_selector is_company" >
						<option value=""><?php echo $formText_No_output;?></option>
						<?php foreach($processes as $process) { ?>
							<option value="<?php echo $process['id'];?>" <?php if($process['id'] == $projectData['reminder_process_for_company']) echo 'selected';?>>
								<?php echo $process['name'];?>
							</option>
						<?php } ?>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			<div class="steps_wrapper"></div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_ActivateReminderProcessForPerson_Output; ?></div>
				<div class="lineInput">
					<?php
				    $s_sql = "SELECT * FROM collecting_cases_process WHERE available_for = 1 OR available_for = 3 ORDER BY sortnr ASC";
				    $o_query = $o_main->db->query($s_sql);
				    $processes = ($o_query ? $o_query->result_array() : array());
					?>
					<select name="reminder_process_for_person"  class="process_selector" >
						<option value=""><?php echo $formText_No_output;?></option>
						<?php foreach($processes as $process) { ?>
							<option value="<?php echo $process['id'];?>" <?php if($process['id'] == $projectData['reminder_process_for_person']) echo 'selected';?>>
								<?php echo $process['name'];?>
							</option>
						<?php } ?>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			<div class="steps_wrapper"></div>
			*/
			/*
			?>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_ActivateCollectingProcessForCompany_Output; ?></div>
				<div class="lineInput">
					<?php
				    $s_sql = "SELECT * FROM collecting_cases_collecting_process WHERE (available_for = 2 OR available_for = 3) ORDER BY sortnr ASC";
				    $o_query = $o_main->db->query($s_sql);
				    $processes = ($o_query ? $o_query->result_array() : array());
					?>
					<select name="collecting_process_for_company"  class="process_selector is_collecting is_company" >
						<option value=""><?php echo $formText_No_output;?></option>
						<?php foreach($processes as $process) { ?>
							<option value="<?php echo $process['id'];?>" <?php if($process['id'] == $projectData['collecting_process_for_company']) echo 'selected';?>>
								<?php echo $process['name'];?>
							</option>
						<?php } ?>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			<div class="steps_wrapper"></div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_ActivateCollectingProcessForPerson_Output; ?></div>
				<div class="lineInput">
					<?php
				    $s_sql = "SELECT * FROM collecting_cases_collecting_process WHERE (available_for = 1 OR available_for = 3) ORDER BY sortnr ASC";
				    $o_query = $o_main->db->query($s_sql);
				    $processes = ($o_query ? $o_query->result_array() : array());
					?>
					<select name="collecting_process_for_person"  class="process_selector is_collecting" >
						<option value=""><?php echo $formText_No_output;?></option>
						<?php foreach($processes as $process) { ?>
							<option value="<?php echo $process['id'];?>" <?php if($process['id'] == $projectData['collecting_process_for_person']) echo 'selected';?>>
								<?php echo $process['name'];?>
							</option>
						<?php } ?>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			<div class="steps_wrapper"></div>
			<?php*/

			$s_sql = "SELECT crcp.*, ccp.fee_level_name, pst.name as stepTypeName, IF(crcp.name IS NULL or crcp.name = '', CONCAT_WS(' ', ccp.fee_level_name, pst.name), crcp.name) as name, ccp.available_for
			FROM creditor_reminder_custom_profiles crcp
			LEFT JOIN collecting_cases_process ccp ON ccp.id = crcp.reminder_process_id
			LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id
			WHERE crcp.creditor_id = ? ORDER BY ccp.sortnr ASC";
			$o_query = $o_main->db->query($s_sql, array($projectData['id']));
			$creditor_reminder_custom_profiles = ($o_query ? $o_query->result_array() : array());

			if($projectData) {
				?>
				<div class="line">
					<div class="lineTitle"><?php echo $formText_DefaultReminderProfileIdForPerson_Output; ?></div>
					<div class="lineInput">
						<select name="creditor_reminder_default_profile_id" class="popupforminput botspace">
							<option value=""><?php echo $formText_Select_output;?></option>
							<?php
							foreach($creditor_reminder_custom_profiles as $creditor_reminder_custom_profile) {
								if($creditor_reminder_custom_profile['available_for'] == 1){
									?>
									<option value="<?php echo $creditor_reminder_custom_profile['id'];?>" <?php if($creditor_reminder_custom_profile['id'] == $projectData['creditor_reminder_default_profile_id']) echo 'selected';?>><?php echo $creditor_reminder_custom_profile['name'];?></option>
									<?php
								}
							}
							?>
						</select>
					</div>
					<div class="clear"></div>
				</div>
				<div class="line">
					<div class="lineTitle"><?php echo $formText_DefaultReminderProfileIdForCompany_Output; ?></div>
					<div class="lineInput">
						<select name="creditor_reminder_default_profile_for_company_id" class="popupforminput botspace">
							<option value=""><?php echo $formText_Select_output;?></option>
							<?php
							foreach($creditor_reminder_custom_profiles as $creditor_reminder_custom_profile) {
								if($creditor_reminder_custom_profile['available_for'] == 2){
									?>
									<option value="<?php echo $creditor_reminder_custom_profile['id'];?>" <?php if($creditor_reminder_custom_profile['id'] == $projectData['creditor_reminder_default_profile_for_company_id']) echo 'selected';?>><?php echo $creditor_reminder_custom_profile['name'];?></option>
									<?php
								}
							}
							?>
						</select>
					</div>
					<div class="clear"></div>
				</div>
				<?php 
			} else {
				
				$s_sql = "SELECT ccp.*, pst.name as stepTypeName FROM collecting_cases_process ccp LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id WHERE  ccp.content_status < 2 AND ccp.published = 1 AND (ccp.available_for = 2 OR ccp.available_for = 3) ORDER BY ccp.sortnr ASC";
				$o_query = $o_main->db->query($s_sql);
				$company_processes = $o_query ? $o_query->result_array() : array();

				$s_sql = "SELECT ccp.*, pst.name as stepTypeName FROM collecting_cases_process ccp LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id  WHERE ccp.content_status < 2 AND ccp.published = 1 AND (ccp.available_for = 1 OR ccp.available_for = 3) ORDER BY ccp.sortnr ASC";
				$o_query = $o_main->db->query($s_sql);
				$person_processes = $o_query ? $o_query->result_array() : array();
				$company_processes_processed = array();
				foreach($company_processes as $company_process) {
					$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_id = ? ORDER BY sortnr ASC";
					$o_query = $o_main->db->query($s_sql, array($company_process['id']));
					$steps = ($o_query ? $o_query->result_array() : array());

					$company_process['steps'] = $steps;
					$company_processes_processed[] = $company_process;
				}
				$person_processes_processed = array();
				foreach($person_processes as $person_process) {
					$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_id = ? ORDER BY sortnr ASC";
					$o_query = $o_main->db->query($s_sql, array($person_process['id']));
					$steps = ($o_query ? $o_query->result_array() : array());

					$person_process['steps'] = $steps;
					$person_processes_processed[] = $person_process;
				}
				?>
				<div class="line">
					<div class="lineTitle"><?php echo $formText_DefaultReminderProcessForPerson_Output; ?></div>
					<div class="lineInput">
						<select name="person_process" class="popupforminput botspace">
							<option value=""><?php echo $formText_Select_output;?></option>
							<?php
							foreach($person_processes_processed as $collecting_case_process) {
								?>
								<option value="<?php echo $collecting_case_process['id'];?>"><?php echo $collecting_case_process['fee_level_name']." - ".$collecting_case_process['stepTypeName'];?></option>
								<?php
							}
							?>
						</select>
					</div>
					<div class="clear"></div>
				</div>
				<div class="line">
					<div class="lineTitle"><?php echo $formText_DefaultReminderProcessForCompany_Output; ?></div>
					<div class="lineInput">
						<select name="company_process" class="popupforminput botspace">
							<option value=""><?php echo $formText_Select_output;?></option>
							<?php							
							foreach($company_processes_processed as $collecting_case_process) {
								?>
								<option value="<?php echo $collecting_case_process['id'];?>"><?php echo $collecting_case_process['fee_level_name']." - ".$collecting_case_process['stepTypeName'];?></option>
								<?php
							}
							?>
						</select>
					</div>
					<div class="clear"></div>
				</div>
				<?php 
			}				
			?>

			<div class="line">
				<div class="lineTitle"><?php echo $formText_ChooseProgressOfReminderProcess_output; ?></div>
				<div class="lineInput">
					<select name="choose_progress_of_reminderprocess" class="popupforminput botspace choose_progress_of_reminderprocess" autocomplete="off">
						<option value="0">
							<?php echo $formText_Manual_output;?>
						</option>
						<option value="1" <?php if($projectData['choose_progress_of_reminderprocess'] == 1) echo 'selected';?>>
							<?php echo $formText_Automatic_output;?>
						</option>
					</select>
				</div>
				<div class="clear"></div>
			</div>

			<div class="line">
				<div class="lineTitle"><?php echo $formText_ChooseMoveToCollectingProcess_Output_output; ?></div>
				<div class="lineInput">
					<select name="choose_move_to_collecting_process" class="popupforminput botspace choose_move_to_collecting_process" autocomplete="off">
						<option value="0">
							<?php echo $formText_Manual_output;?>
						</option>
						<option value="1" <?php if($projectData['choose_move_to_collecting_process'] == 1) echo 'selected';?>>
							<?php echo $formText_Automatic_output;?>
						</option>
						<option value="2" <?php if($projectData['choose_move_to_collecting_process'] == 2) echo 'selected';?>>
							<?php echo $formText_DoNotSendToCollecting_output;?>
						</option>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_CollectingProcessToMoveTo_Output; ?></div>
				<div class="lineInput">
					<select name="collecting_process_to_move_from_reminder" class="popupforminput botspace" autocomplete="off">
						<option value=""><?php echo $formText_UseDefault_output;?></option>
						<?php
						foreach($all_collecting_processes as $all_process) {
							// $s_sql = "SELECT * FROM collecting_cases_collecting_process_steps WHERE collecting_cases_collecting_process_id = ? AND warning_level = 1 ORDER BY id";
							// $o_query = $o_main->db->query($s_sql, array($all_process['id']));
							// $warning_process_steps = ($o_query ? $o_query->result_array() : array());
							// if(count($warning_process_steps)){
								?>
								<option value="<?php echo $all_process['id']?>" <?php if($projectData['collecting_process_to_move_from_reminder'] == $all_process['id']) echo 'selected';?>>
									<?php echo $all_process['name'];?>
								</option>
								<?php
							// }
						}
						?>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_SkipReminderAndGoDirectlyToCollecting_Output; ?></div>
				<div class="lineInput">
					<select name="skip_reminder_go_directly_to_collecting" class="popupforminput botspace " autocomplete="off">
						<option value="0">
							<?php echo $formText_No_output;?>
						</option>
						<option value="1" <?php if($projectData['skip_reminder_go_directly_to_collecting'] == 1) echo 'selected';?>><?php echo $formText_YesHideProcessingPortal_output;?></option>
						<option value="2" <?php if($projectData['skip_reminder_go_directly_to_collecting'] == 2) echo 'selected';?>><?php echo $formText_YesShowProcessingPortal_output;?></option>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_CollectingCaseReminderFeeLevel_Output; ?></div>
				<div class="lineInput">
					<select name="collecting_case_reminder_fee_level" class="popupforminput botspace " autocomplete="off">
						<option value="0">
							<?php echo $formText_UseDefault_output;?> (2)
						</option>
						<option value="1" <?php if($projectData['collecting_case_reminder_fee_level'] == 1) echo 'selected';?>>1</option>
						<option value="2" <?php if($projectData['collecting_case_reminder_fee_level'] == 2) echo 'selected';?>>2</option>
						<option value="3" <?php if($projectData['collecting_case_reminder_fee_level'] == 3) echo 'selected';?>>3</option>
						<option value="4" <?php if($projectData['collecting_case_reminder_fee_level'] == 4) echo 'selected';?>>4</option>
					</select>
				</div>
				<div class="clear"></div>
			</div>

			<div class="line">
				<div class="lineTitle"><?php echo $formText_ReminderSystemEdition_Output_output; ?></div>
				<div class="lineInput">
					<select name="reminder_system_edition" class="popupforminput botspace reminder_system_edition" autocomplete="off">
						<option value="0">
							<?php echo $formText_Basic_output;?>
						</option>
						<option value="1" <?php if($projectData['reminder_system_edition'] == 1) echo 'selected';?>>
							<?php echo $formText_Light_output;?>
						</option>
					</select>
				</div>
				<div class="clear"></div>
			</div>

			<div class="line onlyCollectingWrapper">
				<div class="lineTitle"><?php echo $formText_ChooseHowToCreateCollectingCaseWhenAutomaticReminder_output; ?></div>
				<div class="lineInput">
					<select name="choose_how_to_create_collectingcase" class="popupforminput botspace" autocomplete="off">
						<option value="0">
							<?php echo $formText_Manual_output;?>
						</option>
						<option value="1" <?php if($projectData['choose_how_to_create_collectingcase'] == 1) echo 'selected';?>>
							<?php echo $formText_Automatic_output;?>
						</option>
					</select>
				</div>
				<div class="clear"></div>
			</div>

			<div class="line">
				<div class="lineTitle"><?php echo $formText_MinimumAmountToPaybackToDebitor_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="minimumAmountToPaybackToDebitor" value="<?php echo $projectData['minimumAmountToPaybackToDebitor']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_MaximumAmountForgiveTooLittlePayed_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="maximumAmountForgiveTooLittlePayed" value="<?php echo $projectData['maximumAmountForgiveTooLittlePayed']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>

			<div class="line">
				<div class="lineTitle"><?php echo $formText_ReminderOnlyFromInvoiceNr_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="reminder_only_from_invoice_nr" value="<?php echo $projectData['reminder_only_from_invoice_nr']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_EmailForReminderWarning_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="email_for_reminder_warning" value="<?php echo $projectData['email_for_reminder_warning']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_PrintReminders_output; ?></div>
				<div class="lineInput">
					<select name="print_reminders" class="print_reminders popupforminput botspace" autocomplete="off">
						<option value="0">
							<?php echo $formText_Yourself_output;?>
						</option>
						<option value="1" <?php if($projectData['print_reminders'] == 1) echo 'selected';?>>
							<?php echo $formText_SendToExternalCompany_output;?>
						</option>
					</select>
				</div>
				<div class="clear"></div>
			</div>

			<div class="line">
				<div class="lineTitle"><?php echo $formText_ReminderRestNoteMinimumAmount_output; ?></div>
				<div class="lineInput">
					<select name="use_customized_reminder_rest_note_min_amount" class="reminderRestNoteSelector popupforminput botspace" autocomplete="off">
						<option value="0">
							<?php echo $formText_UseDefault_output;?>
						</option>
						<option value="1" <?php if($projectData['use_customized_reminder_rest_note_min_amount'] == 1) echo 'selected';?>>
							<?php echo $formText_Customize_output;?>
						</option>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line reminderRestNoteWrapper" style="display: none;">
				<div class="lineTitle"><?php echo $formText_ReminderRestNoteMinimumAmount_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="reminderRestNoteMinimumAmount" value="<?php echo $projectData['reminderRestNoteMinimumAmount']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>

			<div class="line">
				<div class="lineTitle"><?php echo $formText_ActivateLinkToEsp_Output; ?></div>
				<div class="lineInput">
					<input type="checkbox" class="popupforminput botspace checkbox" autocomplete="off" name="activateLinkToEsp" value="1" <?php if($projectData['activateLinkToEsp']) echo 'checked'; ?>>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_ChooseInvoiceEmailPriority_output; ?></div>
				<div class="lineInput">
					<select name="invoice_email_priority" class="popupforminput botspace" autocomplete="off">
						<option value="0">
							<?php echo $formText_CustomerEmail_output;?>
						</option>
						<option value="1" <?php if($projectData['invoice_email_priority'] == 1) echo 'selected';?>>
							<?php echo $formText_InvoiceEmail_output;?>
						</option>
					</select>
				</div>
				<div class="clear"></div>
			</div>

			<div class="line">
				<div class="lineTitle"><?php echo $formText_isDemo_Output; ?></div>
				<div class="lineInput">
					<input type="checkbox" class="popupforminput botspace checkbox" autocomplete="off" name="is_demo" value="1" <?php if($projectData['is_demo']) echo 'checked'; ?>>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_UseLocalEmailPhoneForReminder_Output; ?></div>
				<div class="lineInput">
					<input type="checkbox" class="popupforminput botspace checkbox use_local_email_phone_for_reminder" autocomplete="off" name="use_local_email_phone_for_reminder" value="1" <?php if($projectData['use_local_email_phone_for_reminder']) echo 'checked'; ?>>
				</div>
				<div class="clear"></div>
			</div>
			<div class="localWrapper">
				<div class="line">
					<div class="lineTitle"><?php echo $formText_LocalEmailForReminder_output; ?></div>
					<div class="lineInput">
						<input type="text" class="popupforminput botspace" autocomplete="off" name="local_email" value="<?php echo $projectData['local_email']; ?>"/>
					</div>
					<div class="clear"></div>
				</div>

				<div class="line">
					<div class="lineTitle"><?php echo $formText_LocalPhoneForReminder_output; ?></div>
					<div class="lineInput">
						<input type="text" class="popupforminput botspace" autocomplete="off" name="local_phone" value="<?php echo $projectData['local_phone']; ?>"/>
					</div>
					<div class="clear"></div>
				</div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_SmsSendername_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="sms_sendername" value="<?php echo $projectData['sms_sendername']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>


			<div class="line">
				<div class="lineTitle"><?php echo $formText_OnboardingIncomplete_Output; ?></div>
				<div class="lineInput">
					<input type="checkbox" class="popupforminput botspace checkbox" autocomplete="off" name="onboarding_incomplete" value="1" <?php if($projectData['onboarding_incomplete']) echo 'checked'; ?>>
				</div>
				<div class="clear"></div>
			</div>


			<div class="line">
				<div class="lineTitle"><?php echo $formText_CollectingAgreementAcceptedBy_Output; ?></div>
				<div class="lineInput">
					<?php echo $projectData['collecting_agreement_accepted_by']; ?>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_CollectingAgreementAcceptedDate_Output; ?></div>
				<div class="lineInput">
					<?php if($projectData['collecting_agreement_accepted_date'] != "" && $projectData['collecting_agreement_accepted_date'] != "0000-00-00 00:00:00"){ echo date("d.m.Y H:i:s", strtotime($projectData['collecting_agreement_accepted_date'])); }?>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_CollectingAgreementFile_Output; ?></div>
				<div class="lineInput">
					<a href="../<?php echo $projectData['collecting_agreement_file'].'?caID='.$_GET['caID'].'&table=creditor&field=collecting_agreement_file&ID='.$projectData['id']; ?>" download target="_blank">
						<?php echo basename($projectData['collecting_agreement_file']); ?>
					</a>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Checkbox1_Output; ?></div>
				<div class="lineInput">
					<input type="checkbox" class="popupforminput botspace checkbox" autocomplete="off" name="checkbox_1" value="1" <?php if($projectData['checkbox_1']) echo 'checked'; ?>>
				</div>
				<div class="clear"></div>
			</div>
			<?php
			$s_sql = "SELECT * FROM debtcollectionlatefee_main WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($system_settings['default_collecting_late_fee']));
			$default_late_fee = $o_query ? $o_query->row_array() : array();
			?>

			<div class="line">
				<div class="lineTitle"><?php echo $formText_DefaultCollectingLateFee_Output; ?></div>
				<div class="lineInput">
					<select name="default_collecting_late_fee" class="reminderRestNoteSelector popupforminput botspace" autocomplete="off">
						<option value="0">
							<?php echo $formText_UseDefault_output;?> (<?php echo $default_late_fee['internal_name']." - ".$late_fee['article_name']?>)
						</option>
						<?php

						$s_sql = "SELECT * FROM debtcollectionlatefee_main ORDER BY id ASC";
					    $o_query = $o_main->db->query($s_sql);
				        $late_fees = $o_query ? $o_query->result_array() : array();
						foreach($late_fees as $late_fee) {?>
							<option value="<?php echo $late_fee['id'];?>" <?php if($projectData['default_collecting_late_fee'] == $late_fee['id']) echo 'selected';?>>
								<?php echo $late_fee['internal_name']." - ".$late_fee['article_name'];?>
							</option>
						<?php } ?>
					</select>
				</div>
				<div class="clear"></div>
			</div>

			
			<div class="line">
				<div class="lineTitle"><?php echo $formText_BillingType_output; ?></div>
				<div class="lineInput">
					<select name="billing_type" class="popupforminput botspace billingTypeChanger" autocomplete="off">
						<option value="0">
							<?php echo $formText_Default_output;?>
						</option>
						<option value="1" <?php if($projectData['billing_type'] == 1) echo 'selected';?>>
							<?php echo $formText_Specified_output;?>
						</option>
						<option value="2" <?php if($projectData['billing_type'] == 2) echo 'selected';?>>
							<?php echo $formText_SpecifiedSeperated_output;?>
						</option>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line billingPercentWrapper">
				<div class="lineTitle"><?php echo $formText_BillingPercent_output; ?></div>
				<div class="lineInput">					
					<input type="text" class="popupforminput botspace" autocomplete="off" name="billing_percent" value="<?php echo $projectData['billing_percent']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>
			
			<div class="line billingPercentWrapper2">
				<div class="lineTitle"><?php echo $formText_BillingPercentFees_output; ?></div>
				<div class="lineInput">					
					<input type="text" class="popupforminput botspace" autocomplete="off" name="billing_percent_fees" value="<?php echo $projectData['billing_percent_fees']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>
			
			<div class="line billingPercentWrapper2">
				<div class="lineTitle"><?php echo $formText_BillingPercentInterest_output; ?></div>
				<div class="lineInput">					
					<input type="text" class="popupforminput botspace" autocomplete="off" name="billing_percent_interest" value="<?php echo $projectData['billing_percent_interest']; ?>"/>
				</div>
				<div class="clear"></div>
			</div>
			
			<div class="line">
				<div class="lineTitle"><?php echo $formText_ForceApproveTerms_output; ?></div>
				<div class="lineInput">
					<input type="checkbox" class="popupforminput botspace checkbox" autocomplete="off" name="force_approve_terms" value="1" <?php if($projectData['force_approve_terms']) echo 'checked'; ?>>
				</div>
				<div class="clear"></div>
			</div>

			<div class="line">
				<div class="lineTitle"><?php echo $formText_ShowTransferToCollectingCompanyInReadyToSend_output; ?></div>
				<div class="lineInput">
					<input type="checkbox" class="popupforminput botspace checkbox" autocomplete="off" name="show_transfer_to_collecting_company_in_ready_to_send" value="1" <?php if($projectData['show_transfer_to_collecting_company_in_ready_to_send']) echo 'checked'; ?>>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_ActivateProjectCodeOnReminderLevel_output; ?></div>
				<div class="lineInput">
					<input type="checkbox" class="popupforminput botspace checkbox" autocomplete="off" name="activate_project_code_in_reminderletter" value="1" <?php if($projectData['activate_project_code_in_reminderletter']) echo 'checked'; ?>>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_activateReminderOverviewReport_output; ?></div>
				<div class="lineInput">
					<input type="checkbox" class="popupforminput botspace checkbox" autocomplete="off" name="activate_reminder_overview_report" value="1" <?php if($projectData['activate_reminder_overview_report']) echo 'checked'; ?>>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_WarningLevelFeeForPersonReducedByOne_output; ?></div>
				<div class="lineInput">
					<input type="checkbox" class="popupforminput botspace checkbox" autocomplete="off" name="warning_level_fee_for_person_reduced_by_one" value="1" <?php if($projectData['warning_level_fee_for_person_reduced_by_one']) echo 'checked'; ?>>
				</div>
				<div class="clear"></div>
			</div>

			<div class="line">
				<div class="lineTitle"><?php echo $formText_ActivateAptic_output; ?></div>
				<div class="lineInput">
					<input type="checkbox" class="popupforminput botspace checkbox" autocomplete="off" name="activate_aptic" value="1" <?php if($projectData['activate_aptic']) echo 'checked'; ?>>
				</div>
				<div class="clear"></div>
			</div>
			
			
			
		</div>
		<div id="popup-validate-message" style="display:none;"></div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
		</div>
	</form>

	<div class="initStepWrapper">
		<?php
		foreach($all_reminder_processes as $all_process) {
			$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_id = ? ORDER BY id";
			$o_query = $o_main->db->query($s_sql, array($all_process['id']));
			$process_steps = ($o_query ? $o_query->result_array() : array());
			if(count($process_steps) > 0){
				?>
				<div class="processStepsWrapper processStepsWrapper<?php echo $all_process['id'];?>">
					<div class="lineDivider"><?php echo $formText_Steps_output;?></div>
					<?php foreach($process_steps as $process_step) {
						$s_sql = "SELECT * FROM creditor_customized_fees WHERE creditor_id = ? AND collecting_cases_process_step_id = ? AND type=0 ORDER BY id";
						$o_query = $o_main->db->query($s_sql, array($projectData['id'], $process_step['id']));
						$customized_value = ($o_query ? $o_query->row_array() : array());
						?>
						<div class="line">
							<div class="lineTitle"><?php echo $process_step['name']; ?></div>
							<div class="lineInput">
								<select name="customer_step_reminder_person[]" class="step_selector customer_step_person">
									<option value=""><?php echo $formText_UseDefault_output?></option>
									<option value="<?php echo $process_step['id']; ?>" <?php if($customized_value['amount_person'] != null) echo 'selected'; ?>><?php echo $formText_Customize_output;?></option>
								</select>
								<select name="customer_step_reminder_company[]" class="step_selector customer_step_company">
									<option value=""><?php echo $formText_UseDefault_output?></option>
									<option value="<?php echo $process_step['id']; ?>" <?php if($customized_value['amount_company'] != null) echo 'selected'; ?>><?php echo $formText_Customize_output;?></option>
								</select>
								<div class="step_custom_values">
									<div class="line person_line reminder_line">
										<div class="lineTitle"><?php echo $formText_AmountPerson_output; ?></div>
										<div class="lineInput">
											<input type="text" class="popupforminput botspace datepicker" autocomplete="off" name="amount_person_<?php echo $process_step['id']?>[]" value="<?php echo $customized_value['amount_person']; ?>"/>
										</div>
										<div class="clear"></div>
									</div>
									<div class="line company_line reminder_line">
										<div class="lineTitle"><?php echo $formText_AmountCompany_output; ?></div>
										<div class="lineInput">
											<input type="text" class="popupforminput botspace" autocomplete="off" name="amount_company_<?php echo $process_step['id']?>[]" value="<?php echo $customized_value['amount_company']; ?>"/>
										</div>
										<div class="clear"></div>
									</div>

								</div>
							</div>
							<div class="clear"></div>
						</div>
					<?php } ?>
				</div>
				<?php
			}
		}
		/*
		foreach($all_collecting_processes as $all_process) {
			$s_sql = "SELECT * FROM collecting_cases_collecting_process_steps WHERE collecting_cases_collecting_process_id = ? ORDER BY id";
			$o_query = $o_main->db->query($s_sql, array($all_process['id']));
			$process_steps = ($o_query ? $o_query->result_array() : array());
			if(count($process_steps) > 0){
				?>
				<div class="processStepsWrapper collectingProcessStepsWrapper<?php echo $all_process['id'];?>">
					<div class="lineDivider"><?php echo $formText_Steps_output;?></div>
					<?php foreach($process_steps as $process_step) {
						$s_sql = "SELECT * FROM creditor_customized_fees WHERE creditor_id = ? AND collecting_cases_process_step_id = ? AND type=1 ORDER BY id";
						$o_query = $o_main->db->query($s_sql, array($projectData['id'], $process_step['id']));
						$customized_value_collecting = ($o_query ? $o_query->row_array() : array());
						?>
						<div class="line">
							<div class="lineTitle"><?php echo $process_step['name']; ?></div>
							<div class="lineInput">
								<select name="customer_step_collecting_person[]" class="step_selector customer_step_person">
									<option value=""><?php echo $formText_UseDefault_output?></option>
									<option value="<?php echo $process_step['id'];?>" <?php if($customized_value_collecting['collecting_fee_level_person'] != null) echo 'selected'; ?>><?php echo $formText_Customize_output;?></option>
								</select>
								<select name="customer_step_collecting_company[]" class="step_selector customer_step_company">
									<option value=""><?php echo $formText_UseDefault_output?></option>
									<option value="<?php echo $process_step['id'];?>" <?php if($customized_value_collecting['collecting_fee_level'] != null) echo 'selected'; ?>><?php echo $formText_Customize_output;?></option>
								</select>
								<div class="step_custom_values">
									<div class="line company_line collecting_line">
										<div class="lineTitle"><?php echo $formText_FeeLevel_output; ?></div>
										<div class="lineInput">
											<select name="fee_level_<?php echo $process_step['id']?>[]" class="popupforminput botspace" autocomplete="off" >
												<option value=""><?php echo $formText_UseDefault_output;?></option>
												<option value="1" <?php if($customized_value_collecting['collecting_fee_level'] == 1) echo 'selected';?>><?php echo '1';?></option>
												<option value="2" <?php if($customized_value_collecting['collecting_fee_level'] == 2) echo 'selected';?>><?php echo '2';?></option>
												<option value="3" <?php if($customized_value_collecting['collecting_fee_level'] == 3) echo 'selected';?>><?php echo '3';?></option>
												<option value="4" <?php if($customized_value_collecting['collecting_fee_level'] == 4) echo 'selected';?>><?php echo '4';?></option>
											</select>
										</div>
										<div class="clear"></div>
									</div>
									<div class="line person_line collecting_line">
										<div class="lineTitle"><?php echo $formText_FeeLevel_output; ?></div>
										<div class="lineInput">
											<select name="fee_level_person_<?php echo $process_step['id']?>[]" class="popupforminput botspace" autocomplete="off" >
												<option value=""><?php echo $formText_UseDefault_output;?></option>
												<option value="1" <?php if($customized_value_collecting['collecting_fee_level_person'] == 1) echo 'selected';?>><?php echo '1';?></option>
												<option value="2" <?php if($customized_value_collecting['collecting_fee_level_person'] == 2) echo 'selected';?>><?php echo '2';?></option>
												<option value="3" <?php if($customized_value_collecting['collecting_fee_level_person'] == 3) echo 'selected';?>><?php echo '3';?></option>
												<option value="4" <?php if($customized_value_collecting['collecting_fee_level_person'] == 4) echo 'selected';?>><?php echo '4';?></option>
											</select>
										</div>
										<div class="clear"></div>
									</div>
								</div>
							</div>
							<div class="clear"></div>
						</div>
					<?php } ?>
				</div>
				<?php
			}
		}*/?>
		?>
	</div>
</div>
<script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

function callBackOnUploadAll(data) {
    $('.popupformbtn .saveFiles').val('<?php echo $formText_Save; ?>').prop('disabled',false);

};
function callbackOnStart(data) {
    $('.popupformbtn .saveFiles').val('<?php echo $formText_UploadInProgress_output;?>...').prop('disabled',true);
};
function callbackOnDelete(data){
}

$(document).ready(function() {
	$(".billingTypeChanger").off("change").on("change", function(){
		if($(this).val() == 0) {
			$(".billingPercentWrapper").hide();
			$(".billingPercentWrapper2").hide();
		} else if($(this).val() == 1) {
			$(".billingPercentWrapper2").hide();
			$(".billingPercentWrapper").show();
		} else {
			$(".billingPercentWrapper").hide();
			$(".billingPercentWrapper2").show();
		}
	}).change();
	// $(".reminder_process_id").change(function(){
	// 	var val = $(this).val();
	// 	if(val > 0){
	// 		$(".onlyReminderWrapper").show()
	// 		$(".onlyReminderWrapper .choose_progress_of_reminderprocess").change();
	// 	} else {
	// 		$(".onlyReminderWrapper").hide();
	// 		$(".onlyCollectingWrapper").hide();
	// 	}
	// }).change();
	$(".use_local_email_phone_for_reminder").off("change").on("change", function(e){
		if($(this).is(":checked")){
			$(".localWrapper").show();
		} else {
			$(".localWrapper").hide();
		}
	}).change();
	$(".choose_progress_of_reminderprocess").change(function(){
		if($(this).val() == 1){
			$(".onlyCollectingWrapper").show();
			$(".print_reminders").val(1).change();
		} else {
			$(".onlyCollectingWrapper").hide();
		}
	}).change();
	$(".set_objection_days").change(function(){
		if($(this).val() == 0){
			$(".objectionDays").hide();
		} else {
			$(".objectionDays").show();
		}
	}).change();
	$(".default_process_manual").change(function(){
		var val = $(this).val();

		$(".process_manual option").show();
		$(".process_manual option.process_manual_"+val).hide();
	}).change();
	$(".sync_from_accounting").change(function(){
		if($(this).val() == 1) {
			$(".sync_from_accounting_trigger").show();
		} else if($(this).val() == 0) {
			$(".sync_from_accounting_trigger").hide();
		} else {
			$(".sync_from_accounting_trigger").hide();
		}
	}).change();
	$(".create_cases").change(function(){
		if($(this).val() == ""){
			$(".manual_wrapper").hide();
			$(".automatic_wrapper").hide();
			$(".manual_wrapper select").prop("required", false);
			$(".automatic_wrapper select").prop("required", false);
		} else if($(this).val() == 1) {
			$(".manual_wrapper").show();
			$(".automatic_wrapper").hide();
			$(".manual_wrapper select").prop("required", true);
			$(".automatic_wrapper select").prop("required", false);
		} else if($(this).val() == 0) {
			$(".manual_wrapper").hide();
			$(".automatic_wrapper").show();
			$(".manual_wrapper select").prop("required", false);
			$(".automatic_wrapper select").prop("required", true);
		}
	}).change();
	$(".datepicker").datepicker({
		"dateFormat": "d.m.yy"
	});
    $(".popupform-<?php echo $creditor_id;?> form.output-form").validate({
        ignore: [],
        submitHandler: function(form) {
            fw_loading_start();
            $.ajax({
                url: $(form).attr("action"),
                cache: false,
                type: "POST",
                dataType: "json",
                data: $(form).serialize(),
                success: function (data) {
                    fw_loading_end();
					if(data.error != undefined){
						$.each(data.error, function(index, value){
							var _type = Array("error");
							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
							$("#popup-validate-message").append("<div>"+value+"</div>");
						});
						$("#popup-validate-message").show();
					}  else {
	                    if(data.redirect_url !== undefined)
	                    {
	                        out_popup.addClass("close-reload").data("redirect", data.redirect_url);
	                        out_popup.close();
	                    }
					}
                }
            }).fail(function() {
                $(".popupform-<?php echo $creditor_id;?> #popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                $(".popupform-<?php echo $creditor_id;?> #popup-validate-message").show();
                $('.popupform-<?php echo $creditor_id;?> #popupeditbox').css('height', $('.popupform-<?php echo $creditor_id;?> #popupeditboxcontent').height());
                fw_loading_end();
            });
        },
        invalidHandler: function(event, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var message = errors == 1
                ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                $(".popupform-<?php echo $creditor_id;?> #popup-validate-message").html(message);
                $(".popupform-<?php echo $creditor_id;?> #popup-validate-message").show();
                $('.popupform-<?php echo $creditor_id;?> #popupeditbox').css('height', $('#popupeditboxcontent').height());
            } else {
                $(".popupform-<?php echo $creditor_id;?> #popup-validate-message").hide();
            }
            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
        },
        errorPlacement: function(error, element) {
            if(element.attr("name") == "creditor_id") {
                error.insertAfter(".popupform-<?php echo $creditor_id;?> .selectCreditor");
            }
            if(element.attr("name") == "customer_id") {
                error.insertAfter(".popupform-<?php echo $creditor_id;?> .selectCustomer");
            }
        },
        messages: {
            creditor_id: "<?php echo $formText_SelectTheCreditor_output;?>",
            customer_id: "<?php echo $formText_SelectTheCustomer_output;?>",
        }
    });
    $(".updateEntityId").on("click", function(e){
        e.preventDefault();
		var data = {
			creditor_id: $(this).data('creditor-id')
		};
		ajaxCall('update_entity_id', data, function(json) {
			$('#popupeditboxcontent2').html('');
			$('#popupeditboxcontent2').html(json.html);
			out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
			$("#popupeditbox2:not(.opened)").remove();
		});
    })
	$(".datefield").datepicker({
		dateFormat: "d.m.yy",
		firstDay: 1
	})
    $(".popupform-<?php echo $creditor_id;?> .selectCreditor").unbind("click").bind("click", function(){
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
                $('#popupeditboxcontent2').html('');
                $('#popupeditboxcontent2').html(obj.html);
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                $("#popupeditbox2:not(.opened)").remove();
            }
        });
    })
    $(".popupform-<?php echo $creditor_id;?> .selectCustomer").unbind("click").bind("click", function(){
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1};
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_customers";?>',
            data: _data,
            success: function(obj){
                fw_loading_end();
                $('#popupeditboxcontent2').html('');
                $('#popupeditboxcontent2').html(obj.html);
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                $("#popupeditbox2:not(.opened)").remove();
            }
        });
    })


    $(".popupform-<?php echo $creditor_id;?> .selectOwner").unbind("click").bind("click", function(){
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1, owner: 1};
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_employees";?>',
            data: _data,
            success: function(obj){
                fw_loading_end();
                $('#popupeditboxcontent2').html('');
                $('#popupeditboxcontent2').html(obj.html);
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                $("#popupeditbox2:not(.opened)").remove();
            }
        });
    })
	$(".reminderRestNoteSelector").off("change").on("change", function(){
		if($(this).val() == 0){
			$(".reminderRestNoteWrapper").hide();
		} else if($(this).val() == 1){
			$(".reminderRestNoteWrapper").show();
		}
	}).change();

	$(".resetInvoiceResponsible").on("click", function(){
		$("#invoiceResponsible").val("");
		$(".selectInvoiceResponsible").html("<?php echo $formText_SelectInvoiceResponsible_Output;?>");
	})
	$(".integration_module_select").change(function(){
		if($(this).val() == "Integration24SevenOffice"){
			$(".sevenOfficeWrapper").show();
			$(".tripletexWrapper").hide();
		} else if($(this).val() == "IntegrationTripletex") {
			$(".tripletexWrapper").show();
			$(".sevenOfficeWrapper").hide();
		}
	}).change();
	$(".updatePassword").off("click").on("click", function(){
		$(this).hide();
		$(".passwordInput").show();
	})
	$(".send_reminder_fromSelect").change(function(){
		if($(this).val() == 0){
			$(".addCreditorPortalCodeOnLetterWrapper").show();
		} else if($(this).val() == 1) {
			$(".addCreditorPortalCodeOnLetterWrapper").hide();
		}
	}).change();
	// $(".process_selector").change(function(){
	// 	var item = $(this);
	// 	var processId = $(this).val();
	// 	if($(this).hasClass("is_collecting")){
	// 		var steps = $(".initStepWrapper .collectingProcessStepsWrapper"+processId).clone();
	// 		steps.find(".step_custom_values .reminder_line").remove();
	// 	} else {
	// 		var steps = $(".initStepWrapper .processStepsWrapper"+processId).clone();
	// 		steps.find(".step_custom_values .collecting_line").remove();
	// 	}
	// 	if($(this).hasClass("is_company")){
	// 		steps.find(".step_custom_values .person_line").remove();
	// 		steps.find(".customer_step_person").remove();
	// 	} else {
	// 		steps.find(".step_custom_values .company_line").remove();
	// 		steps.find(".customer_step_company").remove();
	// 	}
	// 	if(processId > 0){
	// 		item.parents(".line").next(".steps_wrapper").html(steps);
	// 	} else {
	// 		item.parents(".line").next(".steps_wrapper").html("");
	// 	}
	// 	rebind_customize_steps();
	// }).change();
	rebind_customize_steps();
});
function rebind_customize_steps(){
	$(".step_selector").off("change").change(function(){
		var value = $(this).val();
		var parent = $(this).parent(".lineInput");
		if(value == 0){
			parent.find(".step_custom_values").hide();
		} else if(value > 0){
			parent.find(".step_custom_values").show();
		}
	}).change()
}

</script>
<style>
.billingPercentWrapper {
	display: none;	
}
.sevenOfficeWrapper {
	display: none;
}
.tripletexWrapper {
	display: none;
}

.onlyCollectingWrapper {
	display: none;
}
.onlyReminderWrapper {
	display: none;
}
.objectionDays {
	display: none;
}
.categoryWrapper {
	display: none;
}
.resetInvoiceResponsible {
	margin-left: 20px;
}
.lineInput .otherInput {
    margin-top: 10px;
}
.lineInput input[type="radio"]{
    margin-right: 10px;
    vertical-align: middle;
}
.lineInput input[type="radio"] + label {
    margin-right: 10px;
    vertical-align: middle;
}
.popupform .inlineInput input.popupforminput {
    display: inline-block;
    width: auto;
    vertical-align: middle;
    margin-right: 20px;
}
.popupform .inlineInput label {
    display: inline-block !important;
    vertical-align: middle;
}
.popupform .lineInput.lineWhole {
	font-size: 14px;
}
.popupform .lineInput.lineWhole label {
	font-weight: normal !important;
}
.selectDivModified {
    display:block;
}
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
	border:1px solid #e8e8e8;
	position:relative;
}
.invoiceEmail {
    display: none;
}
label.error {
    color: #c11;
    margin-left: 10px;
    border: 0;
    display: inline !important;
}
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
.addSubProject {
    margin-bottom: 10px;
}
.updatePassword {
	cursor: pointer;
	color: #46b2e2;
}
.lineDivider {
	padding: 10px 0px;
	font-weight: bold;
}
.initStepWrapper {
	display: none;
}
.steps_wrapper {
	padding: 0px 0px 15px 30px;
}
.localWrapper {
	display: none;
}
</style>
