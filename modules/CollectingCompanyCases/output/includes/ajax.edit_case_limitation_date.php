<?php
$caseId = $_POST['case_id'] ? $o_main->db->escape_str($_POST['case_id']) : 0;
$action = $_POST['action'] ? $_POST['action'] : '';
$status = $_POST['status'] ? $_POST['status'] : 0;

$sql = "SELECT * FROM collecting_company_cases WHERE id = $caseId";
$o_query = $o_main->db->query($sql);
$projectData = $o_query ? $o_query->row_array() : array();

if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
        if ($caseId) {
            $s_sql_update = "";
            if($_POST['approvedToExpire']){
                $s_sql_update .= ", approved_to_expire = 1";
                $s_sql_update .= ", approved_to_expire_date = NOW()";
                $s_sql_update .= ", approved_to_expire_by = '".$o_main->db->escape_str($variables->loggID)."'";
                $s_sql_update .= ", approved_to_expire_note = '".$o_main->db->escape_str($_POST['approved_to_expire_note'])."'";
            } else {
                $s_sql_update .= ", approved_to_expire = 0";
                $s_sql_update .= ", approved_to_expire_date = '0000-00-00'";
                $s_sql_update .= ", approved_to_expire_by = '".$o_main->db->escape_str($variables->loggID)."'";
                $s_sql_update .= ", approved_to_expire_note = '".$o_main->db->escape_str($_POST['approved_to_expire_note'])."'";
            }


            $sql = "UPDATE collecting_company_cases SET
            updated = now(),
            updatedBy='".$variables->loggID."',
			case_limitation_date='".($_POST['case_limitation_date'] == "" ? "0000-00-00":date("Y-m-d", strtotime($o_main->db->escape_str($_POST['case_limitation_date']))))."'".$s_sql_update." WHERE id = $caseId";

			$o_query = $o_main->db->query($sql);
            $fw_redirect_url = $_POST['redirect_url'];
        }   
	}
}

?>

<div class="popupform popupform-<?php echo $caseId;?>">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_case_limitation_date";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="case_id" value="<?php echo $caseId;?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$caseId; ?>">
		<div class="inner">

    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_DueDate_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace datefield" autocomplete="off" name="case_limitation_date" value="<?php if($projectData['case_limitation_date'] != "0000-00-00" && $projectData['case_limitation_date'] != "") echo date("d.m.Y", strtotime($projectData['case_limitation_date'])); ?>" >
                </div>
        		<div class="clear"></div>
    		</div>
            <div class="line">
        		<div class="lineTitle"><?php echo $formText_ApprovedToExpire_Output; ?></div>
        		<div class="lineInput">
                    <input type="checkbox" class="popupforminput botspace checkbox" autocomplete="off" name="approvedToExpire" <?php if($projectData['approved_to_expire']) echo 'checked'; ?> value="1" >
                </div>
        		<div class="clear"></div>
    		</div>            
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Note_LID6009; ?></div>
				<div class="lineInput">
					<textarea name="approved_to_expire_note" class="popupforminput botspace"><?php echo $projectData['approved_to_expire_note'];?></textarea>
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
    $(".popupform-<?php echo $caseId;?> form.output-form").validate({
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
                $(".popupform-<?php echo $caseId;?> #popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                $(".popupform-<?php echo $caseId;?> #popup-validate-message").show();
                $('.popupform-<?php echo $caseId;?> #popupeditbox').css('height', $('.popupform-<?php echo $caseId;?> #popupeditboxcontent').height());
                fw_loading_end();
            });
        },
        invalidHandler: function(event, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var message = errors == 1
                ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                $(".popupform-<?php echo $caseId;?> #popup-validate-message").html(message);
                $(".popupform-<?php echo $caseId;?> #popup-validate-message").show();
                $('.popupform-<?php echo $caseId;?> #popupeditbox').css('height', $('#popupeditboxcontent').height());
            } else {
                $(".popupform-<?php echo $caseId;?> #popup-validate-message").hide();
            }
            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
        },
        errorPlacement: function(error, element) {
            if(element.attr("name") == "creditor_id") {
                error.insertAfter(".popupform-<?php echo $caseId;?> .selectCreditor");
            }
            if(element.attr("name") == "debitor_id") {
                error.insertAfter(".popupform-<?php echo $caseId;?> .selectDebitor");
            }
        },
        messages: {
            creditor_id: "<?php echo $formText_SelectTheCreditor_output;?>",
            debitor_id: "<?php echo $formText_SelectTheDebitor_output;?>",
        }
    });
	$(".datefield").datepicker({
		dateFormat: "d.m.yy",
		firstDay: 1
	})

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
