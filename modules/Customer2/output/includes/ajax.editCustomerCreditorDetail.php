<?php
if(isset($_POST['customerId'])){ $customerId = $_POST['customerId']; } else { $customerId = 0; }
//$customerId = $_POST['customerId'] ? ($_POST['customerId']) : 0;


$s_sql = "SELECT * FROM customer_basisconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) {
    $customer_basisconfig = $o_query->row_array();
}

$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
$v_customer_accountconfig = array();
if($o_query && $o_query->num_rows()>0) {
    $v_customer_accountconfig = $o_query->row_array();
}

require_once("fnc_rewritebasisconfig.php");
rewriteCustomerBasisconfig();


if($customerId) {
    $s_sql = "SELECT * FROM customer WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($customerId));
    if($o_query && $o_query->num_rows()>0) {
        $customerData = $o_query->row_array();
    }
}
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{

		$sql_update = ", customer_type_collect = '".$o_main->db->escape_str($_POST['customer_type_collect'])."'";

		$choose_reminder_profile = $_POST['choose_reminder_profile'];
		$choose_move_to_collecting = $_POST['choose_move_to_collecting'];
		$choose_progress_of_reminderprocess = $_POST['choose_progress_of_reminderprocess'];
        if ($customerId) {

            $s_sql = "UPDATE customer SET
            updated = now(),
            updatedBy=?,
			creditor_reminder_profile_id= ?,
			choose_move_to_collecting_process = ?,
			choose_progress_of_reminderprocess = ?,
			send_all_collecting_company_letters_by_email = '".$o_main->db->escape_str($_POST['send_all_collecting_company_letters_by_email'])."'".$sql_update."
            WHERE id = ?";

            $o_query = $o_main->db->query($s_sql, array($variables->loggID, $choose_reminder_profile, $choose_move_to_collecting, $choose_progress_of_reminderprocess, $customerId));

            if($o_query)
			{
                $fw_return_data = $_POST['name'];
				$fw_redirect_url = $_POST['redirect_url'];
			} else {
				die('sql_error');
			}
        }
		return;
	}
}

$defaultCreditDays = 14;
if($v_customer_accountconfig['activateDefaultCreditDays'] && $v_customer_accountconfig['defaultCreditDays'] > 0) {
    $defaultCreditDays = intval($v_customer_accountconfig['defaultCreditDays']);
}

$s_sql = "SELECT * FROM ownercompany WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($customerData['owner']));

if($o_query && $o_query->num_rows()>0) {
    $ownerCompany = $o_query->row_array();
}
$s_sql = "SELECT * FROM ownercompany_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $ownercompany_accountconfig = $o_query->row_array();
}
if(!isset($customerData)) {
    $customerData['customerType'] = intval($customer_basisconfig['defaultWhenAddingNewCustomer']);
}

$s_sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($customerData['creditor_id']));
$creditor = $o_query ? $o_query->row_array() : array();

$creditor_profile_for_person = $creditor['creditor_reminder_default_profile_id'];
$creditor_profile_for_company = $creditor['creditor_reminder_default_profile_for_company_id'];

$customer_reminder_profile = $customerData['creditor_reminder_profile_id'];
$customer_move_to_collecting = $customerData['choose_move_to_collecting_process'];
$customer_progress_of_reminder_process = $customerData['choose_progress_of_reminderprocess'];


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
	<form class="output-form output-worker-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editCustomerCreditorDetail";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="customerId" value="<?php echo $customerId;?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customerId; ?>">
		<div class="inner">

			<div class="line">
                <div class="lineTitle"><?php echo $formText_CrmCustomerType_output; ?></div>
                <div class="lineInput">
					<select name="customer_type_collect" required>
						<option value="0" <?php if(intval($customerData['customer_type_collect']) == 0) { echo 'selected'; }?>><?php echo $formText_Company_output;?></option>
						<option value="1" <?php if(intval($customerData['customer_type_collect']) == 1) { echo 'selected'; }?>><?php echo $formText_PrivatePerson_output;?></option>
					</select>
                </div>
                <div class="clear"></div>
            </div>
			<?php
				if($customer_reminder_profile == 0) {
					$customer_type_collect_debitor = $customerData['customer_type_collect'];
					if($customerData['customer_type_collect_addition'] > 0) {
						$customer_type_collect_debitor = $customerData['customer_type_collect_addition'] - 1;
					}
					if($customer_type_collect_debitor== 1) {
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
						<select class="popupforminput botspace" name="choose_reminder_profile" autocomplete="off">
							<option value="0" <?php if($customerData['creditor_reminder_profile_id'] == 0) echo 'selected';?>><?php echo $formText_Default_output." ";
							foreach($creditor_profiles as $creditor_profile) {
								if($default_reminder_profile == $creditor_profile['id']){
									echo "(".$creditor_profile['name'].")";
								}
							} ?></option>
							<?php
							foreach($creditor_profiles as $creditor_profile) {
								?>
								<option value="<?php echo $creditor_profile['id'];?>" <?php if($creditor_profile['id'] == $customerData['creditor_reminder_profile_id']) echo 'selected';?>><?php echo $creditor_profile['name'];?></option>
								<?php
							}
							?>
						</select>
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
							<option value="0" <?php if($customerData['choose_progress_of_reminderprocess'] == 0) echo 'selected';?>><?php echo $formText_Default_output." ";

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
							<option value="1" <?php if($customerData['choose_progress_of_reminderprocess'] == 1) echo 'selected';?>><?php echo $formText_Manual_output;?></option>
							<option value="2" <?php if($customerData['choose_progress_of_reminderprocess'] == 2) echo 'selected';?>><?php echo $formText_Automatic_output;?></option>
							<option value="3" <?php if($customerData['choose_progress_of_reminderprocess'] == 3) echo 'selected';?>><?php echo $formText_DoNotSend_output;?></option>
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
							<option value="0" <?php if($customerData['choose_move_to_collecting_process'] == 0) echo 'selected';?>><?php echo $formText_Default_output." ";

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
							<option value="1" <?php if($customerData['choose_move_to_collecting_process'] == 1) echo 'selected';?>><?php echo $formText_Manual_output;?></option>
							<option value="2" <?php if($customerData['choose_move_to_collecting_process'] == 2) echo 'selected';?>><?php echo $formText_Automatic_output;?></option>
							<option value="3" <?php if($customerData['choose_move_to_collecting_process'] == 3) echo 'selected';?>><?php echo $formText_DoNotSend_output;?></option>
						</select>
					</div>
					<div class="clear"></div>
				</div>
				<div class="line">
					<div class="lineTitle"><?php echo $formText_SendAllCollectingCompanyLettersByEmail_Output; ?></div>
					<div class="lineInput">
						<select name="send_all_collecting_company_letters_by_email">
							<option value="0" <?php if($customerData['send_all_collecting_company_letters_by_email'] == 0) echo 'selected';?>><?php echo $formText_No_output;?></option>
							<option value="1" <?php if($customerData['send_all_collecting_company_letters_by_email'] == 1) echo 'selected';?>><?php echo $formText_Yes_output;?></option>
						</select>
					</div>
					<div class="clear"></div>
				</div>
		</div>

		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
		</div>
	</form>
</div>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

$(document).ready(function() {
    $("form.output-form").validate({
        ignore: [],
        submitHandler: function(form) {
            fw_loading_start();
            $.ajax({
                url: $(form).attr("action"),
                cache: false,
                type: "POST",
                dataType: "json",
                data: $(form).serialize(),
                success: function (json) {
					if(json.error !== undefined)
					{
						var _msg = '';
						$.each(json.error, function(index, value){
							var _type = Array("error");
							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
							_msg = _msg + '<div class="msg-' + _type[0] + '">' + value + '</div>';
						});
						$("#popup-validate-message").html(_msg, true);
						$("#popup-validate-message").show();
					} else {
						if(json.redirect_url !== undefined)
						{
                            <?php if($_POST['creditorId'] > 0) {
                                ?>
                    			var data = {
                    				creditor_id: '<?php echo $_POST['creditorId'];?>',
                                    search: json.data,
                                    debitor: 1,
                    			};
                    			ajaxCall({module_file:'get_customers', module_name: 'CollectingCases', module_folder: 'output'}, data, function(json) {
                                    $('#popupeditboxcontent2').html('');
                                    $('#popupeditboxcontent2').html(json.html);
                                    out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                                    $("#popupeditbox2:not(.opened)").remove();
                    			});
                                <?php
                            } else { ?>
    							out_popup.addClass("close-reload").data("redirect", json.redirect_url);
    							out_popup.close();
                            <?php } ?>
						}
					}
					fw_loading_end();
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
        },
        errorPlacement: function(error, element) {
            if(element.attr("name") == "creditor_id") {
                error.insertAfter(".popupform .selectCreditor");
            }
            if(element.attr("name") == "customer_id") {
                error.insertAfter(".popupform .selectCustomer");
            }
			if(element.attr("name") == "projectCode") {
				error.insertAfter(".selectProject");
			}
        },
        messages: {
            creditor_id: "<?php echo $formText_SelectTheCreditor_output;?>",
            customer_id: "<?php echo $formText_SelectTheCustomer_output;?>",
        	projectCode: "<?php echo $formText_SelectProjectCode_output;?>",
        }
    });

});

</script>
<style>
.popupform input.popupforminput.checkbox {
    width: auto;
}
.popupform input.popupforminput.radioInput {
    width: auto;
    margin-bottom: 5px;
}
.popupform .inlineInput input.popupforminput {
    display: inline-block;
    width: auto;
    vertical-align: middle;
    margin-right: 20px;
    margin-bottom: 0px;
    margin-top: 5px;
}
.popupform .inlineInput {
    margin-bottom: 10px;
}
.popupform .inlineInput label {
    margin-bottom: 0px;
}
.popupform .inlineInput label {
    display: inline-block !important;
    vertical-align: middle;
}
.popupform .invoiceByWrapper input {
    margin-left: 10px;
}
.popupeditbox .lineInput.invoiceByWrapper input[type="radio"] + label {
    margin-right: 0px;
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
/* .invoiceEmail {
    display: none;
} */
.selectDivModified {
    display:block;
}
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
	border:1px solid #e8e8e8;
	position:relative;
}
.privatePersonField {
	display: none;
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
.addCustomerManually {
    cursor: pointer;
    color: #46b2e2;
}
.line.description {
    font-size: 15px;
}
.ehf_result {
    margin-right: 15px;
    vertical-align: middle;
}
.doNotCheckForEhfWrapper {
    display: block;
}
.popupeditbox .popupform .inlineInput input.popupforminput {
    margin-right: 5px;
    margin-left: 0px;
}
.doNotCheckForEhfWrapper label {
    font-weight: normal;
}
</style>
