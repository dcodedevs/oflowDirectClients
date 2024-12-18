<?php
$collecting_case_id = isset($_POST['collecting_case_id']) ? $_POST['collecting_case_id'] : 0;
$cid = isset($_POST['cid']) ? $_POST['cid'] : 0;

if($collecting_case_id > 0 || $cid > 0) {

	$sql = "SELECT * FROM collecting_cases WHERE id = ?";
	$o_query = $o_main->db->query($sql, array($collecting_case_id));
	$caseData = $o_query ? $o_query->row_array() : array();

	if($cid > 0) {
		$s_sql = "SELECT creditor_reminder_custom_profiles.* FROM creditor_reminder_custom_profiles WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($cid));
		$creditor_reminder_custom_profile = ($o_query ? $o_query->row_array() : array());
	} else {
		$s_sql = "SELECT creditor_reminder_custom_profiles.* FROM creditor_reminder_custom_profiles WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($caseData['reminder_profile_id']));
		$creditor_reminder_custom_profile = ($o_query ? $o_query->row_array() : array());
	}
	if($creditor_reminder_custom_profile) {

		$s_sql = "SELECT * FROM creditor WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($caseData['creditor_id']));
		$creditor = ($o_query ? $o_query->row_array() : array());

		$s_sql = "SELECT creditor_reminder_custom_profiles.* FROM creditor_reminder_custom_profiles WHERE creditor_id = ? ORDER BY sortnr ASC";
		$o_query = $o_main->db->query($s_sql, $creditor['id']);
		$creditor_reminder_custom_profiles = ($o_query ? $o_query->result_array() : array());

		$s_sql = "SELECT ccp.*, pst.name as stepTypeName FROM collecting_cases_process ccp LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id WHERE  ccp.content_status < 2 AND ccp.id = ?";
		$o_query = $o_main->db->query($s_sql, array($creditor_reminder_custom_profile['reminder_process_id']));
		$currentProcess = $o_query ? $o_query->row_array() : array();

		if($creditor_reminder_custom_profile['name'] == ""){
			$creditor_reminder_custom_profile['name'] = $currentProcess['fee_level_name']." ".$currentProcess['stepTypeName'];
		}

		$s_sql = "SELECT * FROM creditor_reminder_custom_profile_values WHERE creditor_reminder_custom_profile_id = ?";
		$o_query = $o_main->db->query($s_sql, array($creditor_reminder_custom_profile['id']));
		$profile_values = ($o_query ? $o_query->result_array() : array());
		$processed_profile_values = array();
		foreach($profile_values as $profile_value){
			$processed_profile_values[$profile_value['collecting_cases_process_step_id']] = $profile_value;
		}
		$s_sql = "SELECT * FROM collecting_cases_process WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($creditor_reminder_custom_profile['reminder_process_id']));
		$default_process = ($o_query ? $o_query->row_array() : array());

		$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_id = ? ORDER BY sortnr ASC";
		$o_query = $o_main->db->query($s_sql, array($default_process['id']));
		$steps = ($o_query ? $o_query->result_array() : array());
		?>
		<div class="profile_block">
			<select class="profile_selector" autocomplete="off">
				<option value=""><?php echo $formText_Select_output;?></option>
				<?php
				foreach($creditor_reminder_custom_profiles as $creditor_reminder_custom_profile_dropdown) {

					$s_sql = "SELECT ccp.*, pst.name as stepTypeName FROM collecting_cases_process ccp LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id WHERE  ccp.content_status < 2 AND ccp.id = ?";
					$o_query = $o_main->db->query($s_sql, array($creditor_reminder_custom_profile_dropdown['reminder_process_id']));
					$currentProcess = $o_query ? $o_query->row_array() : array();

					if($creditor_reminder_custom_profile_dropdown['name'] == ""){
						$creditor_reminder_custom_profile_dropdown['name'] = $currentProcess['fee_level_name']." ".$currentProcess['stepTypeName'];
					}
					?>
					<option value="<?php echo $creditor_reminder_custom_profile_dropdown['id'];?>" <?php if($creditor_reminder_custom_profile_dropdown['id'] == $creditor_reminder_custom_profile['id']) echo 'selected';?>><?php echo $creditor_reminder_custom_profile_dropdown['name']; if($creditor_reminder_custom_profile_dropdown['content_status'] >= 2) echo " (deleted)";?></option>
					<?php
				} ?>
			</select>

			<table class="profile_table">
				<tr>
					<th class="firstTd"></th>
					<?php
					foreach($steps as $step) {
						echo "<th>".$step['name']."</th><th class='spanTd'></th>";
					}
					?>
				</tr>
				<tr>
					<td class="firstTd"><b><?php echo $formText_PdfText_Output; ?></b></td>

					<?php
					foreach($steps as $step) {
						$stepPdfText = array();
						foreach($pdfTexts as $letter) {
							if($letter['id'] == $step['collecting_cases_pdftext_id']) {
								$stepPdfText = $letter;
							}
						}
						$profile_value = $processed_profile_values[$step['id']];
						?>
						<td>
							<?php
							if($profile_value['pdftext_title'] != "" || $profile_value['pdftext_text'] != "") {
								echo $profile_value['pdftext_title'];
								$default_span = " <span class='customized_span'>".$formText_Customized_output."</span>";
							} else {
								if($stepPdfText){
									echo $stepPdfText['name'];
								} else {
									echo $formText_None_output;
								}
								$default_span = " <span class='default_span'>".$formText_Default_output." </span>";
							}?>

						</td>
						<td class="spanTd"><?php
						echo $default_span;
						?></td>
						<?php
					}
					?>
				</tr>
				<tr>
					<td class="firstTd"><b><?php echo $formText_ReminderAmount_Output; ?></b></td>
					<?php
					foreach($steps as $step) {
						$profile_value = $processed_profile_values[$step['id']];
						?>
						<td>
							<?php
							if($profile_value['reminder_amount'] > 0){
								echo number_format($profile_value['reminder_amount'], 2, ","," ");
								$default_span = " <span class='customized_span'>".$formText_Customized_output."</span>";
							} else {
								echo number_format($step['reminder_amount'], 2, ","," ");
								$default_span = " <span class='default_span'>".$formText_Default_output." </span>";
							}
							?>
						</td>
						<td class="spanTd"><?php
						echo $default_span;
						?></td>
						<?php
					}
					?>
				</tr>
				<tr>
					<td class="firstTd"><b><?php echo $formText_AddFee_Output; ?></b></td>
					<?php
					foreach($steps as $step) {
						$profile_value = $processed_profile_values[$step['id']];
						?>
						<td>
							<?php
							if($profile_value['doNotAddFee'] > 0){
								if($profile_value['doNotAddFee'] != 2){
									echo $formText_Yes_output;
								} else {
									echo $formText_No_output;
								}
								$default_span = " <span class='customized_span'>".$formText_Customized_output."</span>";
							} else {
								if(!$step['doNotAddFee']){
									echo $formText_Yes_output;
								} else {
									echo $formText_No_output;
								}
								$default_span =  " <span class='default_span'>".$formText_Default_output." </span>";
							}
							?>
						</td>
						<td class="spanTd"><?php
						echo $default_span;
						?></td>
						<?php
					}
					?>
				</tr>
				<tr>
					<td class="firstTd"><b><?php echo $formText_AddInterest_Output; ?></b></td>
					<?php
					foreach($steps as $step) {
						$profile_value = $processed_profile_values[$step['id']];
						?>
						<td>
							<?php
							if($profile_value['doNotAddInterest'] > 0){
								if($profile_value['doNotAddInterest'] != 2){
									echo $formText_Yes_output;
								} else {
									echo $formText_No_output;
								}
								$default_span = " <span class='customized_span'>".$formText_Customized_output."</span>";
							} else {
								if(!$step['doNotAddInterest']){
									echo $formText_Yes_output;
								} else {
									echo $formText_No_output;
								}
								$default_span = " <span class='default_span'>".$formText_Default_output." </span>";
							}
							?>
						</td>
						<td class="spanTd"><?php
						echo $default_span;
						?></td>
						<?php
					}
					?>
				</tr>
				<tr>
					<td class="firstTd"><b><?php echo $formText_DaysAfterDueDate_Output; ?></b></td>
					<?php
					foreach($steps as $step) {
						?>
						<td>
							<?php
							echo $step['days_after_due_date'];
							$default_span = " <span class='default_span'>".$formText_Default_output." </span>";
							?>
						</td>
						<td class="spanTd"><?php
						echo $default_span;
						?></td>
						<?php
					}
					?>
				</tr>
				<tr>
					<td class="firstTd"><b><?php echo $formText_AddNumberOfDaysToDueDate_Output; ?></b></td>
					<?php
					foreach($steps as $step) {
						?>
						<td>
							<?php
							echo $step['add_number_of_days_to_due_date'];
							$default_span = " <span class='default_span'>".$formText_Default_output." </span>";
							?>
						</td>
						<td class="spanTd"><?php
						echo $default_span;
						?></td>
						<?php
					}
					?>
				</tr>
				<tr>
					<td class="firstTd"><b><?php echo $formText_SendingAction_Output; ?></b></td>
					<?php
					foreach($steps as $step) {
						$profile_value = $processed_profile_values[$step['id']];
						?>
						<td>
							<?php
							$sending_action = $step['sending_action'];
							$default_span = " <span class='default_span'>".$formText_Default_output." </span>";
							if($profile_value['sending_action'] > 0){
								$sending_action = $profile_value['sending_action'];
								$default_span = " <span class='customized_span'>".$formText_Customized_output."</span>";
							}
							switch($sending_action){
								case 1:
									echo $formText_SendLetter_output;
								break;
								case 2:
									echo $formText_SendEmailIfEmailExistsOrElseLetter_output;
								break;
							}
							?>

						</td>
						<td class="spanTd"><?php
						echo $default_span;
						?></td>
						<?php
					}
					?>
				</tr>
			</table>
		</div>
		<style>
		.profile_table {
			table-layout: fixed;
			width: auto;
			border: 1px solid #cecece;
			margin-top: 10px;
		}
		.profile_table td,
		.profile_table th {
			padding: 5px 15px 5px 25px;
			border-left: 1px solid #cecece;
		}
		.profile_table .firstTd {
			width: 200px;
			border-left: 0;
			padding-left: 10px;
		}
		.profile_table .default_span {
			color: #cecece;
			margin-left: 15px;
		}
		.profile_table .spanTd {
			border-left: 0;
		}
		.editProfileSending {
			margin-left: 10px;
			color: #cccccc;
			cursor: pointer;
		}
		.profile_table .customized_span {
			color: orange;
			font-weight: bold;
			margin-left: 15px;
		}
		.popupeditbox.widePopup {
			width: 1024px;
		}
		</style>
		<script type="text/javascript">
		$("#popupeditbox").addClass("widePopup");
		$(function(){
			$(".profile_selector").change(function(e){
				e.preventDefault();
				var data = {
					collecting_case_id: '<?php echo $_POST['collecting_case_id'];?>',
					cid: $(this).val()
				};
				ajaxCall('show_profile_settings', data, function(json) {
					$('#popupeditboxcontent').html('');
					$('#popupeditboxcontent').html(json.html);
					out_popup = $('#popupeditbox').bPopup(out_popup_options);
					$("#popupeditbox:not(.opened)").remove();
				});
			})
		})
		</script>
		<?php
	}
}
?>
