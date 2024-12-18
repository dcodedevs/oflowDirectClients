<?php
$processId = $_POST['processId'] ? $o_main->db->escape_str($_POST['processId']) : 0;
$processStepId = $_POST['processStepId'] ? $o_main->db->escape_str($_POST['processStepId']) : 0;
$processStepActionId = $_POST['processStepActionId'] ? $o_main->db->escape_str($_POST['processStepActionId']) : 0;
$action = $_POST['action'] ? $_POST['action'] : '';
$status = $_POST['status'] ? $_POST['status'] : 0;

$sql = "SELECT * FROM collecting_cases_process WHERE id = $processId";
$o_query = $o_main->db->query($sql);
$processData = $o_query ? $o_query->row_array() : array();

$sql = "SELECT * FROM collecting_cases_process_steps WHERE id = $processStepId";
$o_query = $o_main->db->query($sql);
$processStepData = $o_query ? $o_query->row_array() : array();

if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
		$sortnr = 0;

		$sql = "SELECT * FROM collecting_cases_process_steps_action WHERE collecting_cases_process_id = ? ORDER BY sortnr DESC";
		$o_query = $o_main->db->query($sql, array($processData['id']));
		$maxData = $o_query ? $o_query->row_array() : array();
		$sortnr = intval($maxData['sortnr']) + 1;

        if ($processStepActionId) {
            $sql = "UPDATE collecting_cases_process_steps_action SET
            updated = now(),
            updatedBy='".$variables->loggID."',
            collecting_cases_process_steps_id='".$o_main->db->escape_str($processStepData['id'])."',
            number_of_days_to_due_date='".$o_main->db->escape_str($_POST['number_of_days_to_due_date'])."',
            action='".$o_main->db->escape_str($_POST['action'])."',
            collecting_cases_pdftext_id='".$o_main->db->escape_str($_POST['collecting_cases_pdftext_id'])."',
            collecting_cases_emailtext_id='".$o_main->db->escape_str($_POST['collecting_cases_emailtext_id'])."'
            WHERE id = $processStepActionId";

			$o_query = $o_main->db->query($sql);
			$insert_id = $processId;
            $fw_redirect_url = $_POST['redirect_url'];
        } else {
            $sql = "INSERT INTO collecting_cases_process_steps_action SET
            created = now(),
            createdBy='".$variables->loggID."',
            collecting_cases_process_steps_id='".$o_main->db->escape_str($processStepData['id'])."',
            number_of_days_to_due_date='".$o_main->db->escape_str($_POST['number_of_days_to_due_date'])."',
            action='".$o_main->db->escape_str($_POST['action'])."',
            collecting_cases_pdftext_id='".$o_main->db->escape_str($_POST['collecting_cases_pdftext_id'])."',
            collecting_cases_emailtext_id='".$o_main->db->escape_str($_POST['collecting_cases_emailtext_id'])."'";

			$o_query = $o_main->db->query($sql);
            $insert_id = $o_main->db->insert_id();
            $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$insert_id;
        }
	}
}
if($action == "deleteAction" && $processStepActionId) {
    $sql = "DELETE FROM collecting_cases_process_steps_action
    WHERE id = $processStepActionId";
    $o_query = $o_main->db->query($sql);
}

$sql = "SELECT * FROM collecting_cases_process_steps_action WHERE id = $processStepActionId";
$o_query = $o_main->db->query($sql);
$processStepActionData = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM collecting_cases_pdftext WHERE content_status < 2 ORDER BY sortnr ASC";
$o_query = $o_main->db->query($s_sql);
$letters = $o_query ? $o_query->result_array() : array();

$s_sql = "SELECT * FROM collecting_cases_emailtext WHERE content_status < 2 ORDER BY sortnr ASC";
$o_query = $o_main->db->query($s_sql);
$emails = $o_query ? $o_query->result_array() : array();

?>

<div class="popupform popupform-<?php echo $processId;?>">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_process_step_action";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="processId" value="<?php echo $processId;?>">
		<input type="hidden" name="processStepId" value="<?php echo $processStepId;?>">
		<input type="hidden" name="processStepActionId" value="<?php echo $processStepActionId;?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$processId; ?>">
		<div class="inner">
    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_Action_Output; ?></div>
        		<div class="lineInput">
					<select name="action" autocomplete="off" class="createAction">
						<option value="0"><?php echo $formText_None_output;?></option>
						<option value="1" <?php if($processStepActionData['action'] == 1) echo 'selected'; ?> ><?php echo $formText_SendLetter_output;?></option>
						<option value="4" <?php if($processStepActionData['action'] == 4) echo 'selected'; ?> ><?php echo $formText_SendEmailIfEmailExistsOrElseLetter_output;?></option>
				   </select>
                </div>
        		<div class="clear"></div>
    		</div>

    		<div class="line letterText">
        		<div class="lineTitle"><?php echo $formText_LetterText_Output; ?></div>
        		<div class="lineInput">
					<select name="collecting_cases_pdftext_id" autocomplete="off">
						<option value="0"><?php echo $formText_None_output;?></option>
						<?php
						foreach($letters as $letter) {
							?>
							<option value="<?php echo $letter['id'];?>" <?php if($letter['id'] == $processStepActionData['collecting_cases_pdftext_id']) echo 'selected';?>><?php echo $letter['title'];?></option>
							<?php
						}
						?>
					</select>
                </div>
        		<div class="clear"></div>
    		</div>
			<div class="line emailText">
        		<div class="lineTitle"><?php echo $formText_EmailText_Output; ?></div>
        		<div class="lineInput">
					<select name="collecting_cases_emailtext_id" autocomplete="off">
						<option value="0"><?php echo $formText_None_output;?></option>
						<?php
						foreach($emails as $letter) {
							?>
							<option value="<?php echo $letter['id'];?>" <?php if($letter['id'] == $processStepActionData['collecting_cases_emailtext_id']) echo 'selected';?>><?php echo $letter['subject'];?></option>
							<?php
						}
						?>
					</select>
                </div>
        		<div class="clear"></div>
    		</div>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_NumberOfDaysToDueDate_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" autocomplete="off" name="number_of_days_to_due_date" value="<?php echo $processStepActionData['number_of_days_to_due_date']; ?>">
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
<script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

$(document).ready(function() {
	$(".createAction").change(function(){
		if($(this).val() == 1){
			$(".letterText").show();
			$(".emailText").hide();
		} else if($(this).val() == 4) {
			$(".emailText").show();
			$(".letterText").show();
		} else{
			$(".emailText").hide();
			$(".letterText").hide();
		}

	}).change();
    $(".popupform-<?php echo $processId;?> form.output-form").validate({
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
                    if(data.redirect_url !== undefined)
                    {
                        out_popup.addClass("close-reload").data("redirect", data.redirect_url);
                        out_popup.close();
                    }
                }
            }).fail(function() {
                $(".popupform-<?php echo $processId;?> #popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                $(".popupform-<?php echo $processId;?> #popup-validate-message").show();
                $('.popupform-<?php echo $processId;?> #popupeditbox').css('height', $('.popupform-<?php echo $processId;?> #popupeditboxcontent').height());
                fw_loading_end();
            });
        },
        invalidHandler: function(event, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var message = errors == 1
                ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                $(".popupform-<?php echo $processId;?> #popup-validate-message").html(message);
                $(".popupform-<?php echo $processId;?> #popup-validate-message").show();
                $('.popupform-<?php echo $processId;?> #popupeditbox').css('height', $('#popupeditboxcontent').height());
            } else {
                $(".popupform-<?php echo $processId;?> #popup-validate-message").hide();
            }
            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
        },
        errorPlacement: function(error, element) {
            if(element.attr("name") == "creditor_id") {
                error.insertAfter(".popupform-<?php echo $processId;?> .selectCreditor");
            }
            if(element.attr("name") == "debitor_id") {
                error.insertAfter(".popupform-<?php echo $processId;?> .selectDebitor");
            }
        },
        messages: {
            creditor_id: "<?php echo $formText_SelectTheCreditor_output;?>",
            debitor_id: "<?php echo $formText_SelectTheDebitor_output;?>",
        }
    });
});

</script>
<style>
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
</style>
