<?php
$s_sql = "SELECT invoice_accountconfig.* FROM invoice_accountconfig ORDER BY id";
$o_query = $o_main->db->query($s_sql);
$invoice_accountconfig = ($o_query ? $o_query->row_array() : array());

$repeatingorderId = $_POST['repeatingorderId'] ? $_POST['repeatingorderId'] : 0;
$fartileId = $_POST['fartileId'] ? $_POST['fartileId'] : 0;
$action = $_POST['action'] ? $_POST['action'] : '';
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		$s_fields = ", activateSendInvoiceExportMessage = '".$_POST['activateSendInvoiceExportMessage']."',
		activateSendExportFileWithMessage = '".$_POST['activateSendExportFileWithMessage']."',
		exportMessageSendToEmail = '".$_POST['exportMessageSendToEmail']."'";
        if($_POST['activateSendInvoiceExportMessage']) {
            if($_POST['exportMessageSendToEmail'] == ""){
                $fw_error_msg[] = $formText_MissingExportMessageSendToEmail_output;
                return;
            }
        }

		if($invoice_accountconfig)
		{
			$s_sql = "UPDATE invoice_accountconfig SET
			updated = now(),
			updatedBy='".$variables->loggID."'".$s_fields."
			WHERE id = '". $o_main->db->escape_str($invoice_accountconfig['id'])."'";
			var_dump($s_sql);
			$o_query = $o_main->db->query($s_sql);
		} else {
			$s_sql = "INSERT INTO invoice_accountconfig SET
			id=NULL,
			moduleID = '".$moduleID."',
			created = now(),
			createdBy='".$variables->loggID."'".$s_fields;
			$o_query = $o_main->db->query($s_sql);
		}
		if($o_query){
			if($_POST['activateSendInvoiceExportMessage']) {
				$s_sql = "SELECT * FROM auto_task WHERE script_path='modules/Invoice/autotask_report_of_invoice_ready_for_export/run.php' AND content_status < 2";
				$o_query = $o_main->db->query($s_sql);
				$autotask = ($o_query ? $o_query->row_array() : array());
				if(!$autotask) {
					include(__DIR__."/../../autotask_report_of_invoice_ready_for_export/config.php");
					$s_sql = "INSERT INTO auto_task SET content_status = 0, created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."',
					script_path='modules/Invoice/autotask_report_of_invoice_ready_for_export/run.php', config='".json_encode($v_auto_task_config)."',
					next_run = '".date("Y-m-d 07:00:00", strtotime("+1 day"))."'";
					$o_query = $o_main->db->query($s_sql);
				}
			} else {
				$s_sql = "UPDATE auto_task SET content_status = 2, updated = NOW(), updatedBy = '".$o_main->db->escape_str($variables->loggID)."' WHERE script_path='modules/Invoice/autotask_report_of_invoice_ready_for_export/run.php'";
				$o_query = $o_main->db->query($s_sql);
			}
		}
		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list";
		return;
	}
}
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_invoice_accountconfig";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<div class="inner">
			<div class="line">
				<div class="lineTitle"><?php echo $formText_ActivateSendInvoiceExportMessage_Output; ?></div>
				<div class="lineInput">
                    <input type="checkbox" class="popupforminput checkboxInput activateSendInvoiceExportMessage" name="activateSendInvoiceExportMessage" value="1" <?php if($invoice_accountconfig['activateSendInvoiceExportMessage']) echo 'checked';?>/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_ActivateSendExportFileWithMessage_Output; ?></div>
				<div class="lineInput">
                    <input type="checkbox" class="popupforminput checkboxInput activateSendExportFileWithMessage" name="activateSendExportFileWithMessage" value="1" <?php if($invoice_accountconfig['activateSendExportFileWithMessage']) echo 'checked';?>/>
				</div>
				<div class="clear"></div>
			</div>
            <div class="line exportMessageSendToEmailWrapper">
				<div class="lineTitle"><?php echo $formText_exportMessageSendToEmail_Output; ?></div>
				<div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="exportMessageSendToEmail" value="<?php echo $invoice_accountconfig['exportMessageSendToEmail'];?>"/>
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
$(function() {
	var datepickerChangeMade;
	$('.datepicker').datepicker({
		dateFormat: 'dd.mm.yy',
		firstDay: 1
	});

	$("form.output-form").validate({
		submitHandler: function(form) {
			fw_loading_start();
            $("#popup-validate-message").html("");
			$.ajax({
				url: $(form).attr("action"),
				cache: false,
				type: "POST",
				dataType: "json",
				data: $(form).serialize(),
				success: function (data) {
					fw_loading_end();
                    if(data.error !== undefined)
                    {
                        $.each(data.error, function(index, value){
                            var _type = Array("error");
                            if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
                            $("#popup-validate-message").append(value);
                        });
                        $("#popup-validate-message").show();
                        fw_loading_end();
                        fw_click_instance = fw_changes_made = false;
                    } else {
                        if(data.redirect_url !== undefined)
                        {
                            out_popup.addClass("close-reload");
                            out_popup.close();
                        }
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

    $(".selectArticle").unbind("click").bind("click", function(e){
		e.preventDefault();
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1};
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_articles";?>',
            data: _data,
            success: function(obj){
                $('#popupeditboxcontent2').html('');
                $('#popupeditboxcontent2').html(obj.html);
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                $("#popupeditbox2:not(.opened)").remove();
                fw_loading_end();
            }
        });
    })
    $(".activateSendInvoiceExportMessage").off("change").on("change", function(){
        if($(this).is(":checked")){
            $(".exportMessageSendToEmailWrapper").show();
        } else {
            $(".exportMessageSendToEmailWrapper").hide();
        }
    }).change();
});
</script>
<style>
.popupform input.popupforminput.activateSendInvoiceExportMessage,
.popupform textarea.popupforminput.activateSendInvoiceExportMessage,
.popupform select.popupforminput.activateSendInvoiceExportMessage,
.col-md-8z input.activateSendInvoiceExportMessage {
    width: auto;
}
.popupform input.popupforminput.activateSendExportFileWithMessage,
.popupform textarea.popupforminput.activateSendExportFileWithMessage,
.popupform select.popupforminput.activateSendExportFileWithMessage,
.col-md-8z input.activateSendExportFileWithMessage {
    width: auto;
}
.exportMessageSendToEmailWrapper {
    display: none;
}
.ui-datepicker-calendar {
	display: block;
}
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
	border:1px solid #e8e8e8;
	position:relative;
}
.popupeditbox label.error { display: none !important; }
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
.popupform .line .lineInput {
	width:70%;
	float:left;
}
</style>
