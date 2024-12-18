<?php
$s_sql = "SELECT * FROM accountinfo";
$o_query = $o_main->db->query($s_sql);
$v_accountinfo = $o_query ? $o_query->row_array() : array();

if(isset($_POST['output_form_submit']))
{
	$case_id = $_POST['case_id'];
	$transaction_id = $_POST['transaction_id'];
	$customer_id = $_POST['customer_id'];
	$username = $variables->loggID;
	$choose_reminder_profile = $_POST['choose_reminder_profile'];
	$choose_move_to_collecting = $_POST['choose_move_to_collecting'];
	$choose_progress_of_reminderprocess = $_POST['choose_progress_of_reminderprocess'];

	if(isset($case_id) && $case_id > 0)
	{
	    $s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
	    $o_query = $o_main->db->query($s_sql, array($case_id));
	    if($o_query && $o_query->num_rows() == 1) {
			$collecting_cases = $o_query ? $o_query->row_array() : array();
			$s_sql = "SELECT * FROM creditor WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($collecting_cases['creditor_id']));
			$creditor = $o_query ? $o_query->row_array() : array();
			if($creditor['reminder_system_edition'] == 1){
		        $s_sql = "UPDATE collecting_cases SET
		        updated = now(),
		        updatedBy= ?,
		        choose_move_to_collecting_process = ?,
		        choose_progress_of_reminderprocess = ?
		        WHERE id = ?";
		        $o_main->db->query($s_sql, array($username, $choose_move_to_collecting, $choose_progress_of_reminderprocess, $case_id));
			} else {
				$s_sql = "UPDATE collecting_cases SET
			    updated = now(),
			    updatedBy= ?,
			    reminder_profile_id = ?,
			    choose_move_to_collecting_process = ?,
			    choose_progress_of_reminderprocess = ?
			    WHERE id = ?";
			    $o_main->db->query($s_sql, array($username, $choose_reminder_profile,$choose_move_to_collecting, $choose_progress_of_reminderprocess,  $case_id));
			}
	    }
	} else if(isset($transaction_id) && $transaction_id > 0)
	{
	    $s_sql = "SELECT * FROM creditor_transactions WHERE id = ?";
	    $o_query = $o_main->db->query($s_sql, array($transaction_id));
	    if($o_query && $o_query->num_rows() == 1) {
			$transaction = $o_query ? $o_query->row_array() : array();
			$s_sql = "SELECT * FROM creditor WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($transaction['creditor_id']));
			$creditor = $o_query ? $o_query->row_array() : array();
			if($creditor['reminder_system_edition'] == 1){
				$s_sql = "UPDATE creditor_transactions SET
			   updated = now(),
			   updatedBy= ?,
			   choose_move_to_collecting_process = ?,
			   choose_progress_of_reminderprocess = ?
			   WHERE id = ?";
			   $o_main->db->query($s_sql, array($username, $choose_move_to_collecting, $choose_progress_of_reminderprocess, $transaction_id));
			} else {
		        $s_sql = "UPDATE creditor_transactions SET
		        updated = now(),
		        updatedBy= ?,
		        reminder_profile_id = ?,
		        choose_move_to_collecting_process = ?,
		        choose_progress_of_reminderprocess = ?
		        WHERE id = ?";
		        $o_main->db->query($s_sql, array($username, $choose_reminder_profile, $choose_move_to_collecting, $choose_progress_of_reminderprocess, $transaction_id));
			}
	    }
	} else if(isset($customer_id) && $customer_id > 0)
	{
	    $s_sql = "SELECT * FROM customer WHERE id = ?";
	    $o_query = $o_main->db->query($s_sql, array($customer_id));
	    if($o_query && $o_query->num_rows() == 1) {
			$customer = $o_query ? $o_query->row_array() : array();
			$s_sql = "SELECT * FROM creditor WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($customer['creditor_id']));
			$creditor = $o_query ? $o_query->row_array() : array();
			if($creditor['reminder_system_edition'] == 1){
				$s_sql = "UPDATE customer SET
			   updated = now(),
			   updatedBy= ?,
			   choose_move_to_collecting_process = ?,
			   choose_progress_of_reminderprocess = ?
			   WHERE id = ?";
			   $o_main->db->query($s_sql, array($username, $choose_move_to_collecting, $choose_progress_of_reminderprocess, $customer_id));
			} else {
		        $s_sql = "UPDATE customer SET
		        updated = now(),
		        updatedBy= ?,
		        creditor_reminder_profile_id = ?,
		        choose_move_to_collecting_process = ?,
		        choose_progress_of_reminderprocess = ?
		        WHERE id = ?";
		        $o_main->db->query($s_sql, array($username, $choose_reminder_profile, $choose_move_to_collecting, $choose_progress_of_reminderprocess, $customer_id));
			}
	    }
	} else {
	    $fw_error_msg = $formText_MissingCase_output;
	}
	return;
}

$transaction_id = $_POST['transaction_id'];
$case_id = $_POST['case_id'];
$customer_id = $_POST['customer_id'];
if($transaction_id > 0){
	$s_sql = "SELECT * FROM creditor_transactions WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($transaction_id));
	$transaction = $o_query ? $o_query->row_array() : array();

	$s_sql = "SELECT * FROM creditor WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($transaction['creditor_id']));
	$creditor = $o_query ? $o_query->row_array() : array();

	$s_sql = "SELECT * FROM customer WHERE creditor_id = ? AND creditor_customer_id = ?";
	$o_query = $o_main->db->query($s_sql, array($creditor['id'], $transaction['external_customer_id']));
	$debitorCustomer = $o_query ? $o_query->row_array() : array();

} else if($case_id > 0) {
	$s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($case_id));
	$collecting_case = $o_query ? $o_query->row_array() : array();

	$s_sql = "SELECT * FROM creditor WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($collecting_case['creditor_id']));
	$creditor = $o_query ? $o_query->row_array() : array();

	$s_sql = "SELECT * FROM customer WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($collecting_case['debitor_id']));
	$debitorCustomer = $o_query ? $o_query->row_array() : array();

} else if($customer_id > 0) {
	$s_sql = "SELECT * FROM customer WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($customer_id));
	$debitorCustomer = $o_query ? $o_query->row_array() : array();

	$s_sql = "SELECT * FROM creditor WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($debitorCustomer['creditor_id']));
	$creditor = $o_query ? $o_query->row_array() : array();
}

$s_sql = "SELECT collecting_system_settings.* FROM collecting_system_settings WHERE content_status < 2";
$o_query = $o_main->db->query($s_sql);
$collecting_system_settings = ($o_query ? $o_query->row_array() : array());

$s_sql = "SELECT collecting_cases_process.* FROM collecting_cases_process WHERE collecting_cases_process.id = ? ";
$o_query = $o_main->db->query($s_sql, array($collecting_system_settings['light_edition_reminder_process_person']));
$light_edition_reminder_process_person = ($o_query ? $o_query->row_array() : array());

$s_sql = "SELECT collecting_cases_process.* FROM collecting_cases_process WHERE collecting_cases_process.id = ? ";
$o_query = $o_main->db->query($s_sql, array($collecting_system_settings['light_edition_reminder_process_company']));
$light_edition_reminder_process_company = ($o_query ? $o_query->row_array() : array());

$creditor_profile_for_person = $creditor['creditor_reminder_default_profile_id'];
$creditor_profile_for_company = $creditor['creditor_reminder_default_profile_for_company_id'];

$creditor_move_to_collecting = $creditor['choose_move_to_collecting_process'];
$creditor_progress_of_reminder_process = $creditor['choose_progress_of_reminderprocess'];

$customer_reminder_profile = $debitorCustomer['creditor_reminder_profile_id'];
$customer_move_to_collecting = $debitorCustomer['choose_move_to_collecting_process'];
$customer_progress_of_reminder_process = $debitorCustomer['choose_progress_of_reminderprocess'];

$s_sql = "SELECT crcp.*, ccp.fee_level_name, pst.name as stepTypeName, CONCAT_WS(' ', ccp.fee_level_name, pst.name) as name
FROM creditor_reminder_custom_profiles crcp
LEFT JOIN collecting_cases_process ccp ON ccp.id = crcp.reminder_process_id
LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id
WHERE crcp.creditor_id = ? AND crcp.content_status < 2";
$o_query = $o_main->db->query($s_sql, array($creditor['id']));
$creditor_profiles = ($o_query ? $o_query->result_array() : array());
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=choose_settings";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="case_id" value="<?php print $_POST['case_id'];?>">
		<?php if($_POST['customer_id'] > 0){ ?>
			<input type="hidden" name="customer_id" value="<?php print $_POST['customer_id'];?>">
		<?php } else { ?>
			<input type="hidden" name="transaction_id" value="<?php print $_POST['transaction_id'];?>">
		<?php } ?>


		<div class="inner">
			<?php
			if($_POST['customer_id'] > 0) {
				if($customer_reminder_profile == 0){
					$customer_type_collect_debitor = $debitorCustomer['customer_type_collect'];
					if($debitorCustomer['customer_type_collect_addition'] > 0){
						$customer_type_collect_debitor = $debitorCustomer['customer_type_collect_addition'] - 1;
					}
					if($customer_type_collect_debitor== 1){
						$default_reminder_profile = $creditor_profile_for_person;
					} else {
						$default_reminder_profile = $creditor_profile_for_company;
					}
				} else {
					$default_reminder_profile = $customer_reminder_profile;
				}

				?>
				<div class="line">
					<div class="lineTitle"><?php echo $formText_ChooseReminderProfile_output; ?></div>
					<div class="lineInput">
						<?php if($creditor['reminder_system_edition'] == 1) {
							$customer_type_collect_debitor = $debitorCustomer['customer_type_collect'];
							if($debitorCustomer['customer_type_collect_addition'] > 0){
								$customer_type_collect_debitor = $debitorCustomer['customer_type_collect_addition'] - 1;
							}
							if($customer_type_collect_debitor== 1){
								echo $light_edition_reminder_process_person['name'];
							} else {
								echo $light_edition_reminder_process_company['name'];
							}
							echo " (".$formText_LightEdition_output.")";
						} else { ?>
							<select class="popupforminput botspace" name="choose_reminder_profile" autocomplete="off">
								<option value="0" <?php if($transaction['reminder_profile_id'] == 0) echo 'selected';?>><?php echo $formText_Default_output." ";
								foreach($creditor_profiles as $creditor_profile) {
									if($default_reminder_profile == $creditor_profile['id']){
										echo "(".$creditor_profile['name'].")";
									}
								} ?></option>
								<?php
								foreach($creditor_profiles as $creditor_profile) {
									?>
									<option value="<?php echo $creditor_profile['id'];?>" <?php if($creditor_profile['id'] == $debitorCustomer['reminder_profile_id']) echo 'selected';?>><?php echo $creditor_profile['name'];?></option>
									<?php
								}
								?>
							</select>
						<?php } ?>
					</div>
					<div class="clear"></div>
				</div>
				<?php
				$default_progress_of_reminderprocess = $creditor_progress_of_reminder_process;
				?>
				<div class="line">
					<div class="lineTitle"><?php echo $formText_ChooseProgressOfReminderProcess_output; ?></div>
					<div class="lineInput">
						<select class="popupforminput botspace" name="choose_progress_of_reminderprocess" autocomplete="off">
							<option value="0" <?php if($debitorCustomer['choose_progress_of_reminderprocess'] == 0) echo 'selected';?>><?php echo $formText_Default_output." ";

							switch($default_progress_of_reminderprocess) {
								case 0:
									echo "(".$formText_Manual_output.")";
								break;
								case 1:
									echo "(".$formText_Automatic_output.")";
								break;
								case 2:
									echo "(".$formText_DoNotSent_output.")";
								break;
							} ?></option>
							<option value="1" <?php if($debitorCustomer['choose_progress_of_reminderprocess'] == 1) echo 'selected';?>><?php echo $formText_Manual_output;?></option>
							<option value="2" <?php if($debitorCustomer['choose_progress_of_reminderprocess'] == 2) echo 'selected';?>><?php echo $formText_Automatic_output;?></option>
							<option value="3" <?php if($debitorCustomer['choose_progress_of_reminderprocess'] == 3) echo 'selected';?>><?php echo $formText_DoNotSend_output;?></option>
						</select>
					</div>
					<div class="clear"></div>
				</div>
				<?php
				$default_move_to_collecting = $creditor_move_to_collecting;
				?>
				<div class="line">
					<div class="lineTitle"><?php echo $formText_ChooseMoveToCollectingProcess_output; ?></div>
					<div class="lineInput">
						<select class="popupforminput botspace" name="choose_move_to_collecting" autocomplete="off">
							<option value="0" <?php if($debitorCustomer['choose_move_to_collecting_process'] == 0) echo 'selected';?>><?php echo $formText_Default_output." ";

							switch($default_move_to_collecting) {
								case 0:
									echo "(".$formText_Manual_output.")";
								break;
								case 1:
									echo "(".$formText_Automatic_output.")";
								break;
								case 2:
									echo "(".$formText_DoNotSent_output.")";
								break;
							} ?></option>
							<option value="1" <?php if($debitorCustomer['choose_move_to_collecting_process'] == 1) echo 'selected';?>><?php echo $formText_Manual_output;?></option>
							<option value="2" <?php if($debitorCustomer['choose_move_to_collecting_process'] == 2) echo 'selected';?>><?php echo $formText_Automatic_output;?></option>
							<option value="3" <?php if($debitorCustomer['choose_move_to_collecting_process'] == 3) echo 'selected';?>><?php echo $formText_DoNotSend_output;?></option>
						</select>
					</div>
					<div class="clear"></div>
				</div>
				<?php
			} else if($_POST['transaction_id'] > 0) {

				if($customer_reminder_profile == 0){
					$customer_type_collect_debitor = $debitorCustomer['customer_type_collect'];
					if($debitorCustomer['customer_type_collect_addition'] > 0){
						$customer_type_collect_debitor = $debitorCustomer['customer_type_collect_addition'] - 1;
					}
					if($customer_type_collect_debitor== 1){
						$default_reminder_profile = $creditor_profile_for_person;
					} else {
						$default_reminder_profile = $creditor_profile_for_company;
					}
				} else {
					$default_reminder_profile = $customer_reminder_profile;
				}
				?>
				<div class="line">
					<div class="lineTitle"><?php echo $formText_ChooseReminderProfile_output; ?></div>
					<div class="lineInput">
						<?php if($creditor['reminder_system_edition'] == 1) {
							$customer_type_collect_debitor = $debitorCustomer['customer_type_collect'];
							if($debitorCustomer['customer_type_collect_addition'] > 0){
								$customer_type_collect_debitor = $debitorCustomer['customer_type_collect_addition'] - 1;
							}
							if($customer_type_collect_debitor== 1){
								echo $light_edition_reminder_process_person['name'];
							} else {
								echo $light_edition_reminder_process_company['name'];
							}
							echo " (".$formText_LightEdition_output.")";
						} else { ?>
							<select class="popupforminput botspace" name="choose_reminder_profile" autocomplete="off">
								<option value="0" <?php if($transaction['reminder_profile_id'] == 0) echo 'selected';?>><?php echo $formText_Default_output." ";
								foreach($creditor_profiles as $creditor_profile) {
									if($default_reminder_profile == $creditor_profile['id']){
										echo "(".$creditor_profile['name'].")";
									}
								} ?></option>
								<?php
								foreach($creditor_profiles as $creditor_profile) {
									?>
									<option value="<?php echo $creditor_profile['id'];?>" <?php if($creditor_profile['id'] == $transaction['reminder_profile_id']) echo 'selected';?>><?php echo $creditor_profile['name'];?></option>
									<?php
								}
								?>
							</select>
						<?php } ?>
					</div>
					<div class="clear"></div>
				</div>
				<?php
				if($customer_progress_of_reminder_process == 0){
					$default_progress_of_reminderprocess = $creditor_progress_of_reminder_process;
				} else {
					$default_progress_of_reminderprocess = $customer_progress_of_reminder_process - 1;
				}
				?>
				<div class="line">
					<div class="lineTitle"><?php echo $formText_ChooseProgressOfReminderProcess_output; ?></div>
					<div class="lineInput">
						<select class="popupforminput botspace" name="choose_progress_of_reminderprocess" autocomplete="off">
							<option value="0" <?php if($transaction['choose_progress_of_reminderprocess'] == 0) echo 'selected';?>><?php echo $formText_Default_output." ";

							switch($default_progress_of_reminderprocess) {
								case 0:
									echo "(".$formText_Manual_output.")";
								break;
								case 1:
									echo "(".$formText_Automatic_output.")";
								break;
								case 2:
									echo "(".$formText_DoNotSent_output.")";
								break;
							} ?></option>
							<option value="1" <?php if($transaction['choose_progress_of_reminderprocess'] == 1) echo 'selected';?>><?php echo $formText_Manual_output;?></option>
							<option value="2" <?php if($transaction['choose_progress_of_reminderprocess'] == 2) echo 'selected';?>><?php echo $formText_Automatic_output;?></option>
							<option value="3" <?php if($transaction['choose_progress_of_reminderprocess'] == 3) echo 'selected';?>><?php echo $formText_DoNotSend_output;?></option>
						</select>
					</div>
					<div class="clear"></div>
				</div>
				<?php
				if($customer_move_to_collecting == 0){
					$default_move_to_collecting = $creditor_move_to_collecting;
				} else {
					$default_move_to_collecting = $customer_move_to_collecting - 1;
				}
				?>
				<div class="line">
					<div class="lineTitle"><?php echo $formText_ChooseMoveToCollectingProcess_output; ?></div>
					<div class="lineInput">
						<select class="popupforminput botspace" name="choose_move_to_collecting" autocomplete="off">
							<option value="0" <?php if($transaction['choose_move_to_collecting_process'] == 0) echo 'selected';?>><?php echo $formText_Default_output." ";

							switch($default_move_to_collecting) {
								case 0:
									echo "(".$formText_Manual_output.")";
								break;
								case 1:
									echo "(".$formText_Automatic_output.")";
								break;
								case 2:
									echo "(".$formText_DoNotSent_output.")";
								break;
							} ?></option>
							<option value="1" <?php if($transaction['choose_move_to_collecting_process'] == 1) echo 'selected';?>><?php echo $formText_Manual_output;?></option>
							<option value="2" <?php if($transaction['choose_move_to_collecting_process'] == 2) echo 'selected';?>><?php echo $formText_Automatic_output;?></option>
							<option value="3" <?php if($transaction['choose_move_to_collecting_process'] == 3) echo 'selected';?>><?php echo $formText_DoNotSend_output;?></option>
						</select>
					</div>
					<div class="clear"></div>
				</div>
			<?php } else {  ?>
				<?php
				if($customer_progress_of_reminder_process == 0){
					$default_progress_of_reminderprocess = $creditor_progress_of_reminder_process;
				} else {
					$default_progress_of_reminderprocess = $customer_progress_of_reminder_process - 1;
				}
				?>
				<div class="line">
					<div class="lineTitle"><?php echo $formText_ChooseProgressOfReminderProcess_output; ?></div>
					<div class="lineInput">
						<select class="popupforminput botspace" name="choose_progress_of_reminderprocess" autocomplete="off">
							<option value="0" <?php if($collecting_case['choose_progress_of_reminderprocess'] == 0) echo 'selected';?>><?php echo $formText_Default_output." ";

							switch($default_progress_of_reminderprocess) {
								case 0:
									echo "(".$formText_Manual_output.")";
								break;
								case 1:
									echo "(".$formText_Automatic_output.")";
								break;
								case 2:
									echo "(".$formText_DoNotSent_output.")";
								break;
							} ?></option>
							<option value="1" <?php if($collecting_case['choose_progress_of_reminderprocess'] == 1) echo 'selected';?>><?php echo $formText_Manual_output;?></option>
							<option value="2" <?php if($collecting_case['choose_progress_of_reminderprocess'] == 2) echo 'selected';?>><?php echo $formText_Automatic_output;?></option>
							<option value="3" <?php if($collecting_case['choose_progress_of_reminderprocess'] == 3) echo 'selected';?>><?php echo $formText_DoNotSend_output;?></option>
						</select>
					</div>
					<div class="clear"></div>
				</div>
				<?php
				if($customer_move_to_collecting == 0){
					$default_move_to_collecting = $creditor_move_to_collecting;
				} else {
					$default_move_to_collecting = $customer_move_to_collecting - 1;
				}
				?>
				<div class="line">
					<div class="lineTitle"><?php echo $formText_ChooseMoveToCollectingProcess_output; ?></div>
					<div class="lineInput">
						<select class="popupforminput botspace" name="choose_move_to_collecting" autocomplete="off">
							<option value="0" <?php if($collecting_case['choose_move_to_collecting_process'] == 0) echo 'selected';?>><?php echo $formText_Default_output." ";

							switch($default_move_to_collecting) {
								case 0:
									echo "(".$formText_Manual_output.")";
								break;
								case 1:
									echo "(".$formText_Automatic_output.")";
								break;
								case 2:
									echo "(".$formText_DoNotSent_output.")";
								break;
							} ?></option>
							<option value="1" <?php if($collecting_case['choose_move_to_collecting_process'] == 1) echo 'selected';?>><?php echo $formText_Manual_output;?></option>
							<option value="2" <?php if($collecting_case['choose_move_to_collecting_process'] == 2) echo 'selected';?>><?php echo $formText_Automatic_output;?></option>
							<option value="3" <?php if($collecting_case['choose_move_to_collecting_process'] == 3) echo 'selected';?>><?php echo $formText_DoNotSend_output;?></option>
						</select>
					</div>
					<div class="clear"></div>
				</div>
			<?php } ?>
		</div>
		<div class="popupformbtn"><input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>"></div>
	</form>
</div>
<style>

.popupform .lineTitle {
	font-weight:700;
	margin-bottom: 10px;
}
.popupform textarea.popupforminput {
	border-radius: 4px;
	padding:5px 10px;
	font-size:12px;
	line-height:17px;
}
.project-file {
	margin-bottom: 4px;
}
.project-file .deleteImage {
	float: right;
}
</style>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

$(function() {
	$("form.output-form").validate({
		submitHandler: function(form) {
			fw_loading_start();
			var formdata = $(form).serializeArray();
			var data = {};
			$(formdata).each(function(index, obj){
				data[obj.name] = obj.value;
			});
			// data.imagesToProcess = imagesToProcess;
			// data.imagesHandle = imagesHandle;
			// data.images = images;

			$.ajax({
				url: $(form).attr("action"),
				cache: false,
				type: "POST",
				dataType: "json",
				data: data,
				success: function (data) {
					fw_loading_end();
					if(data.error !== undefined)
					{
						$("#popup-validate-message").html("");
						$.each(data.error, function(index, value){
							var _type = Array("error");
							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
							$("#popup-validate-message").append(value);
						});
						$("#popup-validate-message").show()
						fw_loading_end();
						fw_click_instance = fw_changes_made = false;
					} else {
						out_popup.addClass("close-reload-creditor");
						out_popup.close();
					}
				}
			}).fail(function() {
				$("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
				$("#popup-validate-message").show();
				$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
				fw_loading_end();
			});
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
	});
});
</script>
