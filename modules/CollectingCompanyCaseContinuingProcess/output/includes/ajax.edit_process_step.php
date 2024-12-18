<?php
$processId = $_POST['processId'] ? $o_main->db->escape_str($_POST['processId']) : 0;
$processStepId = $_POST['processStepId'] ? $o_main->db->escape_str($_POST['processStepId']) : 0;
$action = $_POST['action'] ? $_POST['action'] : '';
$status = $_POST['status'] ? $_POST['status'] : 0;

$sql = "SELECT * FROM collecting_cases_collecting_process WHERE id = $processId";
$o_query = $o_main->db->query($sql);
$processData = $o_query ? $o_query->row_array() : array();


if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
		$sortnr = 0;
		$sql = "SELECT * FROM collecting_company_cases_continuing_process_steps WHERE collecting_cases_collecting_process_id = ? ORDER BY sortnr DESC";
		$o_query = $o_main->db->query($sql, array($processData['id']));
		$maxData = $o_query ? $o_query->row_array() : array();
		$sortnr = intval($maxData['sortnr']) + 1;
		if($processData['type'] == 1) {
			$_POST['bank_account_choice'] = 1;
		}
        if ($processStepId) {
            $sql = "UPDATE collecting_company_cases_continuing_process_steps SET
            updated = now(),
            updatedBy='".$variables->loggID."',
            name='".$o_main->db->escape_str($_POST['name'])."',
            collecting_company_cases_continuing_process_id='".$o_main->db->escape_str($_POST['processId'])."',
            days_after_due_date='".$o_main->db->escape_str($_POST['days_after_due_date'])."',
            add_number_of_days_to_due_date='".$o_main->db->escape_str($_POST['add_number_of_days_to_due_date'])."',
            create_letter = '".$o_main->db->escape_str($_POST['create_letter'])."',
            appear_in_legal_step_handling='".$o_main->db->escape_str($_POST['appear_in_legal_step_handling'])."',
            appear_in_call_debitor_step_handling='".$o_main->db->escape_str($_POST['appear_in_call_debitor_step_handling'])."',
            collecting_cases_pdftext_id='".$o_main->db->escape_str($_POST['collecting_cases_pdftext_id'])."'
            WHERE id = $processStepId";

			$o_query = $o_main->db->query($sql);
			$insert_id = $processId;
            $fw_redirect_url = $_POST['redirect_url'];
        } else {
            $sql = "INSERT INTO collecting_company_cases_continuing_process_steps SET
            created = now(),
            createdBy='".$variables->loggID."',
            name='".$o_main->db->escape_str($_POST['name'])."',
            collecting_company_cases_continuing_process_id='".$o_main->db->escape_str($_POST['processId'])."',
            days_after_due_date='".$o_main->db->escape_str($_POST['days_after_due_date'])."',
            add_number_of_days_to_due_date='".$o_main->db->escape_str($_POST['add_number_of_days_to_due_date'])."',
            create_letter = '".$o_main->db->escape_str($_POST['create_letter'])."',
            appear_in_legal_step_handling='".$o_main->db->escape_str($_POST['appear_in_legal_step_handling'])."',
            appear_in_call_debitor_step_handling='".$o_main->db->escape_str($_POST['appear_in_call_debitor_step_handling'])."',
            collecting_cases_pdftext_id='".$o_main->db->escape_str($_POST['collecting_cases_pdftext_id'])."'";

			$o_query = $o_main->db->query($sql);
            $insert_id = $o_main->db->insert_id();
            $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$insert_id;
        }
		return;
	}
}
if($action == "deleteProcess" && $processStepId) {
    $sql = "DELETE FROM collecting_company_cases_continuing_process_steps
    WHERE id = $processStepId";
    $o_query = $o_main->db->query($sql);
}

$sql = "SELECT * FROM collecting_company_cases_continuing_process_steps WHERE id = $processStepId";
$o_query = $o_main->db->query($sql);
$processStepData = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM collecting_cases_pdftext WHERE content_status < 2 ORDER BY sortnr ASC";
$o_query = $o_main->db->query($s_sql);
$letters = $o_query ? $o_query->result_array() : array();

$s_sql = "SELECT * FROM collecting_cases_emailtext WHERE content_status < 2 ORDER BY sortnr ASC";
$o_query = $o_main->db->query($s_sql);
$emails = $o_query ? $o_query->result_array() : array();

$s_sql = "SELECT * FROM collecting_cases_main_status_basisconfig ORDER BY sortnr ASC";
$o_query = $o_main->db->query($s_sql);
$main_statuses = ($o_query ? $o_query->result_array() : array());

$filterDownStatusArray = array(3,4,6,7,8);

?>

<div class="popupform popupform-<?php echo $processId;?>">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_process_step";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="processId" value="<?php echo $processId;?>">
		<input type="hidden" name="processStepId" value="<?php echo $processStepId;?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$processId; ?>">
		<div class="inner">
    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_Name_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" autocomplete="off" name="name" value="<?php echo $processStepData['name']; ?>">
                </div>
        		<div class="clear"></div>
    		</div>
    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_DaysAfterDueDate_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" autocomplete="off" name="days_after_due_date" value="<?php echo $processStepData['days_after_due_date']; ?>">
                </div>
        		<div class="clear"></div>
    		</div>		
            <div class="line">
                <div class="lineTitle"><?php echo $formText_AddNumberOfDaysToDueDate_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" autocomplete="off" name="add_number_of_days_to_due_date" value="<?php echo $processStepData['add_number_of_days_to_due_date']; ?>">
                </div>
                <div class="clear"></div>
            </div>	
            
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_CreateLetter_Output; ?></div>
        		<div class="lineInput">
                    <input type="checkbox" class="popupforminput botspace checkbox createLetter" autocomplete="off" name="create_letter" 					
					value="1" <?php if($processStepData['create_letter']) echo 'checked'; ?>>
                </div>
        		<div class="clear"></div>
    		</div>
            <div class="createLetterWrapper">
                <div class="line ">
                    <div class="lineTitle"><?php echo $formText_LetterText_Output; ?></div>
                    <div class="lineInput">
                        <select name="collecting_cases_pdftext_id" autocomplete="off">
                            <option value="0"><?php echo $formText_None_output;?></option>
                            <?php
                            foreach($letters as $letter) {
                                ?>
                                <option value="<?php echo $letter['id'];?>" <?php if($letter['id'] == $processStepData['collecting_cases_pdftext_id']) echo 'selected';?>><?php echo $letter['name'];?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <div class="createLetterNotCreated">
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_AppearInLegalStepHandling_Output; ?></div>
                    <div class="lineInput">
                        <input type="checkbox" class="popupforminput botspace checkbox" autocomplete="off" name="appear_in_legal_step_handling" 					
                        value="1" <?php if($processStepData['appear_in_legal_step_handling']) echo 'checked'; ?>>
                    </div>
                    <div class="clear"></div>
                </div>
                
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_AppearInCallDebitorStepHandling_Output; ?></div>
                    <div class="lineInput">
                        <input type="checkbox" class="popupforminput botspace checkbox" autocomplete="off" name="appear_in_call_debitor_step_handling" 
                        value="1" <?php if($processStepData['appear_in_call_debitor_step_handling']) echo 'checked'; ?>>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
		</div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
		</div>
	</form>
</div>
<style>
.subtituesWrapper {
	display: none;
}
.createLetterWrapper {
    display: none;
}
.createLetterNotCreated {
    display: none;
}
</style>
<script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

$(document).ready(function() {
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
    $(".createLetter").change(function(){
        if($(this).is(":checked")){
            $(".createLetterWrapper").show();
            $(".createLetterNotCreated").hide();
        } else {
            $(".createLetterWrapper").hide();
            $(".createLetterNotCreated").show();
        }        
    }).change();
	$(".datefield").datepicker({
		dateFormat: "d.m.yy",
		firstDay: 1
	})
    $(".popupform-<?php echo $processId;?> .selectCreditor").unbind("click").bind("click", function(){
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
    $(".popupform-<?php echo $processId;?> .selectDebitor").unbind("click").bind("click", function(){
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1, debitor: 1};
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


    $(".popupform-<?php echo $processId;?> .selectOwner").unbind("click").bind("click", function(){
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

	$(".resetInvoiceResponsible").on("click", function(){
		$("#invoiceResponsible").val("");
		$(".selectInvoiceResponsible").html("<?php echo $formText_SelectInvoiceResponsible_Output;?>");
	})
	$(".popupform .statusChange").change(function(){
		if($(this).val() > 0) {
			$(".popupform .subtituesWrapperBig").html($(".popupform .subtituesWrapper"+$(this).val()).clone().removeClass("subtituesWrapper"+$(this).val()).show());
		} else {
			$(".popupform .subtituesWrapperBig").html("");
		}
	}).change();
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