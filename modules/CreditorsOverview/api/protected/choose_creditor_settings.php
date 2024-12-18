<?php
include(__DIR__."/../languagesOutput/default.php");

$creditor_id = $v_data['params']['creditor_id'];
$username = $v_data['params']['username'];

if(isset($creditor_id) && $creditor_id > 0)
{
    $s_sql = "SELECT * FROM creditor WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($creditor_id));
    if($o_query && $o_query->num_rows() == 1) {
		$creditor = $o_query ? $o_query->row_array() : array();
		$update_sql = "";
		$trigger_full_reorder = false;
		if(isset($v_data['params']['choose_move_to_collecting'])){
			$update_sql .= ", choose_move_to_collecting_process = '".$o_main->db->escape_str($v_data['params']['choose_move_to_collecting'])."'";
			$trigger_full_reorder = true;
		}
		if(isset($v_data['params']['choose_progress_of_reminderprocess'])){
			$update_sql .= ", choose_progress_of_reminderprocess = '".$o_main->db->escape_str($v_data['params']['choose_progress_of_reminderprocess'])."'";
			$trigger_full_reorder = true;
		}
		if(isset($v_data['params']['choose_edition'])){
			$update_sql .= ", reminder_system_edition = '".$o_main->db->escape_str($v_data['params']['choose_edition'])."'";
			$trigger_full_reorder = true;
		}
		if(isset($v_data['params']['reminder_only_from_invoice_nr'])){
			$update_sql .= ", reminder_only_from_invoice_nr = '".$o_main->db->escape_str(trim($v_data['params']['reminder_only_from_invoice_nr']))."'";
			$trigger_full_reorder = true;
		}
		if(isset($v_data['params']['vat_deduction'])){
			$update_sql .= ", vat_deduction = '".$o_main->db->escape_str($v_data['params']['vat_deduction'])."'";
		}
		if(isset($v_data['params']['interest_bookaccount_project_id'])){
			$update_sql .= ", interest_bookaccount_project_id = '".$o_main->db->escape_str($v_data['params']['interest_bookaccount_project_id'])."'";
		}
		if(isset($v_data['params']['reminder_bookaccount_project_id'])){
			$update_sql .= ", reminder_bookaccount_project_id = '".$o_main->db->escape_str($v_data['params']['reminder_bookaccount_project_id'])."'";
		}
		if(isset($v_data['params']['invoice_bookaccount_project_id'])){
			$update_sql .= ", invoice_bookaccount_project_id = '".$o_main->db->escape_str($v_data['params']['invoice_bookaccount_project_id'])."'";
		}
		if(isset($v_data['params']['reminder_bookaccount_department_id'])){
			$update_sql .= ", reminder_bookaccount_department_id = '".$o_main->db->escape_str($v_data['params']['reminder_bookaccount_department_id'])."'";
		}
		if(isset($v_data['params']['interest_bookaccount_department_id'])){
			$update_sql .= ", interest_bookaccount_department_id = '".$o_main->db->escape_str($v_data['params']['interest_bookaccount_department_id'])."'";
		}
		if(isset($v_data['params']['invoice_bookaccount_department_id'])){
			$update_sql .= ", invoice_bookaccount_department_id = '".$o_main->db->escape_str($v_data['params']['invoice_bookaccount_department_id'])."'";
		}
		if(isset($v_data['params']['choose_next_automatic_reminder_process_time'])){
			
			$next_process_date = date("Y-m-d 11:00:00");
			if($v_data['params']['choose_next_automatic_reminder_process_time'] == 1) {
				$next_process_date = date("Y-m-d 11:00:00", time()+86400);
			}

			$update_sql .= ", next_automatic_reminder_process_time = '".$o_main->db->escape_str($next_process_date)."'";
		}
		if(isset($v_data['params']['use_local_email_phone_for_reminder'])){
			$update_sql .= ", use_local_email_phone_for_reminder = '".$o_main->db->escape_str($v_data['params']['use_local_email_phone_for_reminder'])."'";
		}
		if(isset($v_data['params']['local_email'])){
			$update_sql .= ", local_email = '".$o_main->db->escape_str(trim($v_data['params']['local_email']))."'";
		}
		if(isset($v_data['params']['local_phone'])){
			$update_sql .= ", local_phone = '".$o_main->db->escape_str(trim($v_data['params']['local_phone']))."'";
		}
		if(isset($v_data['params']['invoice_email_priority'])){
			$update_sql .= ", invoice_email_priority = '".$o_main->db->escape_str($v_data['params']['invoice_email_priority'])."'";
		}
		if(isset($v_data['params']['activate_email_with_todays_reminders'])){
			$update_sql .= ", activate_email_with_todays_reminders = '".$o_main->db->escape_str($v_data['params']['activate_email_with_todays_reminders'])."'";
		}
		if(isset($v_data['params']['email_sending_day_choice_reminder'])){
			$update_sql .= ", email_sending_day_choice_reminder = '".$o_main->db->escape_str($v_data['params']['email_sending_day_choice_reminder'])."'";
		}
		if(isset($v_data['params']['email_sending_day_choice_move'])){
			$update_sql .= ", email_sending_day_choice_move = '".$o_main->db->escape_str($v_data['params']['email_sending_day_choice_move'])."'";
		}
		if(isset($v_data['params']['activate_send_reminders_by_ehf'])){
			$update_sql .= ", activate_send_reminders_by_ehf = '".$o_main->db->escape_str($v_data['params']['activate_send_reminders_by_ehf'])."'";
		}
		if(isset($v_data['params']['activate_email_with_todays_reminders'])){
			$update_sql .= ", activate_email_with_todays_reminders = '".$o_main->db->escape_str($v_data['params']['activate_email_with_todays_reminders'])."'";

			if($v_data['params']['local_reminder_emails']) {
				$local_reminder_emails = $v_data['params']['local_reminder_emails'];

				$s_sql = "SELECT * FROM creditor_reminder_emails WHERE creditor_id = '".$o_main->db->escape_str($creditor['id'])."'";
				$o_query = $o_main->db->query($s_sql);
				$creditor_reminder_emails = $o_query ? $o_query->result_array() : array();
				$save_ids = array();
				foreach($local_reminder_emails as $local_reminder_email) {
					if(trim($local_reminder_email) != "") {						
						$s_sql = "SELECT * FROM creditor_reminder_emails WHERE creditor_id = '".$o_main->db->escape_str($creditor['id'])."' AND email = '".$o_main->db->escape_str(trim($local_reminder_email))."'";
						$o_query = $o_main->db->query($s_sql);
						$creditor_reminder_email = $o_query ? $o_query->row_array() : array();
						if(!$creditor_reminder_email) {
							$s_sql = "INSERT INTO creditor_reminder_emails SET created = now(), createdBy= ?, email = ?, creditor_id = ?";
        					$o_main->db->query($s_sql, array($username, $local_reminder_email, $creditor['id']));
						} else {
							$save_ids[] = $creditor_reminder_email['id'];
						}
					}
				}
				foreach($creditor_reminder_emails as $creditor_reminder_email){
					if(!in_array($creditor_reminder_email['id'], $save_ids)){						
						$s_sql = "DELETE FROM creditor_reminder_emails WHERE id = ?";
						$o_main->db->query($s_sql, array($creditor_reminder_email['id']));
					}
				}
			} else {
				$s_sql = "DELETE FROM creditor_reminder_emails WHERE creditor_id = ?";
				$o_main->db->query($s_sql, array($creditor['id']));
			}
		}
		
		
        $s_sql = "UPDATE creditor SET
        updated = now(),
        updatedBy= ?".$update_sql."
        WHERE id = ?";
        $o_main->db->query($s_sql, array($username, $creditor_id));
		
		if($trigger_full_reorder){
			$s_sql = "UPDATE creditor SET trigger_full_reorder = 1
			WHERE id = '".$o_main->db->escape_str($creditor['id'])."'";
			$o_query = $o_main->db->query($s_sql);
		}
		
		if(isset($v_data['params']['days_selector_reminder'])){
			$day_values = array();
			$day_values[1] = $v_data['params']['monday'];
			$day_values[2] = $v_data['params']['tuesday'];
			$day_values[3] = $v_data['params']['wednesday'];
			$day_values[4] = $v_data['params']['thursday'];
			$day_values[5] = $v_data['params']['friday'];

			foreach($day_values as $day_number => $day_value){
				$s_sql = "SELECT * FROM creditor_email_sending_days WHERE creditor_id = ? AND IFNULL(type, 0) = 0 AND day_number = ?";
				$o_query = $o_main->db->query($s_sql, array($creditor_id, $day_number));
				$creditor_email_sending_day = $o_query ? $o_query->row_array() : array();
				if($creditor_email_sending_day) {
					$s_sql = "UPDATE creditor_email_sending_days SET checked = ? WHERE id = '".$o_main->db->escape_str($creditor_email_sending_day['id'])."'";
					$o_query = $o_main->db->query($s_sql, array($day_value));
				} else {
					$s_sql = "INSERT INTO creditor_email_sending_days SET checked = ?, day_number = ?, creditor_id = ?, type = 0";
					$o_query = $o_main->db->query($s_sql, array($day_value, $day_number, $creditor_id));
				}
			}
		}
		if(isset($v_data['params']['days_selector_move'])){
			$day_values = array();
			$day_values[1] = $v_data['params']['monday'];
			$day_values[2] = $v_data['params']['tuesday'];
			$day_values[3] = $v_data['params']['wednesday'];
			$day_values[4] = $v_data['params']['thursday'];
			$day_values[5] = $v_data['params']['friday'];

			foreach($day_values as $day_number => $day_value) {
				$s_sql = "SELECT * FROM creditor_email_sending_days WHERE creditor_id = ? AND IFNULL(type, 0) = 1 AND day_number = ?";
				$o_query = $o_main->db->query($s_sql, array($creditor_id, $day_number));
				$creditor_email_sending_day = $o_query ? $o_query->row_array() : array();
				if($creditor_email_sending_day) {
					$s_sql = "UPDATE creditor_email_sending_days SET checked = ?
					WHERE id = '".$o_main->db->escape_str($creditor_email_sending_day['id'])."'";
					$o_query = $o_main->db->query($s_sql, array($day_value));
				} else {
					$s_sql = "INSERT INTO creditor_email_sending_days SET checked = ?, day_number = ?, creditor_id = ?, type = 1";
					$o_query = $o_main->db->query($s_sql, array($day_value, $day_number, $creditor_id));
				}
			}
		}
		if(isset($v_data['params']['activate_send_reminders_by_ehf']) && 1 == $v_data['params']['activate_send_reminders_by_ehf'] && 1 != $creditor['activate_send_reminders_by_ehf'])
		{
			$s_sql = "SELECT c.* FROM creditor AS cr
			JOIN customer AS c ON c.creditor_id = cr.id AND (0 < LENGTH(c.publicRegisterId) OR c.invoiceBy = 2)
			WHERE cr.id = '".$o_main->db->escape_str($creditor['id'])."' AND cr.activate_send_reminders_by_ehf = 1
			GROUP BY c.id";
			$o_query = $o_main->db->query($s_sql);
			if($o_query && $o_query->num_rows()>0)
			foreach($o_query->result_array() as $v_customer)
			{
				$b_update = $b_found_receiver = FALSE;
				$s_customer_org_nr = preg_replace('#[^0-9]+#', '', $v_customer['publicRegisterId']);
		
				//$v_customer['country_code'] = 'no';
				$s_catalog_code = '0192';
				//if('se' == strtolower($v_customer['country_code'])) $s_catalog_code = '0007';
		
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_VERBOSE, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
				curl_setopt($ch, CURLOPT_POST, TRUE);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
				curl_setopt($ch, CURLOPT_URL, 'https://ap_api.getynet.com/find.php');
				$v_post = array(
					'organisation_no' => $s_customer_org_nr,
					'catalog_code' => $s_catalog_code,
					'token' => 'RySBvCv3i9u6tP4mEd177X2gspGP6Rw0E512874043zDfUyHDsNF70gZvJ2R9s6idmGxk29amaRRR1R4Hbvqy93LJzPCz9oy',
					'password' => '_9^JAj|7_atz~-Y.BndXBguO9_jr0^z~~09m-*:4SXlj|!KZ6Xd.hnJe3WM75H9Vy=;ZIcrab-84WLKN+4Kdz~+xM5U%ePIY'
				);
		
				curl_setopt($ch, CURLOPT_POSTFIELDS, $v_post);
				$s_response = curl_exec($ch);
		
				$v_response = json_decode($s_response, TRUE);
				if(isset($v_response['status']) && $v_response['status'] == 1)
				{
					if(isset($v_response['can_receive_ehf_invoice']) && 1 == $v_response['can_receive_ehf_invoice'])
					{
						$b_found_receiver = TRUE;
					}
				}
		
				$l_status = 0;
				if($b_found_receiver)
				{
					if($v_customer['invoiceBy'] != 2)
					{
						$b_update = TRUE;
						$l_status = 2;
					}
				} else {
					if($v_customer['invoiceBy'] == 2)
					{
						$l_status = filter_var($v_customer['email'], FILTER_VALIDATE_EMAIL) ? 1 : 0;
					}
				}
				
				if($b_update)
				{
					$s_sql = "UPDATE customer SET invoiceBy = '".$o_main->db->escape_str($l_status)."' WHERE id = '".$o_main->db->escape_str($v_customer['id'])."'";
					$o_query = $o_main->db->query($s_sql);
				}
			}
		}
    }
    $v_return['status'] = 1;
} else {
    $v_return['error'] = $formText_MissingCase_output;
}


?>
