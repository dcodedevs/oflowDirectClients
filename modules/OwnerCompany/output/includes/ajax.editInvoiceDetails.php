<?php
$ownercompanyId = isset($_POST['ownercompanyId']) ? $_POST['ownercompanyId'] : 0;

if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
        if ($ownercompanyId) {
            $s_sql = "UPDATE ownercompany SET
            updated = now(),
            updatedBy= ?,
            invoice_template= ?,
            invoiceFromEmail= ?,
            invoiceSubjectEmail= ?,
            invoiceTextEmail= ?,
            invoicelogoWidth= ?,
            invoicelogoPositionX= ?,
            invoicelogoPositionY= ?,
            invoicebottomtext= ?,
            companyaccount= ?,
            companyiban= ?,
            companyswift= ?,
            companyBankAccount2= ?,
            companyiban2= ?,
            companyswift2= ?,
            companyBankAccount3= ?,
            companyiban3= ?,
            companyswift3= ?,
            emailFromExtra1= ?,
            EmailFromExtra2= ?,
            EmailFromExtra3= ?
            WHERE id = ?";

            $o_main->db->query($s_sql, array($variables->loggID, $_POST['invoice_template'], $_POST['invoiceFromEmail'], $_POST['invoiceSubjectEmail'], $_POST['invoiceTextEmail'], $_POST['invoicelogoWidth'], $_POST['invoicelogoPositionX'], $_POST['invoicelogoPositionY'], $_POST['invoicebottomtext'], $_POST['companyaccount'], $_POST['companyiban'], $_POST['companyswift'], $_POST['companyBankAccount2'], $_POST['companyiban2'], $_POST['companyswift2'], $_POST['companyBankAccount3'], $_POST['companyiban3'], $_POST['companyswift3'], $_POST['emailFromExtra1'], $_POST['EmailFromExtra2'], $_POST['EmailFromExtra3'], $ownercompanyId));
            $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$ownercompanyId;
        }
	}
}

if($ownercompanyId) {
    $sql = "SELECT * FROM ownercompany WHERE id = $ownercompanyId";
    $result = $o_main->db->query($sql);
    if($result && $result->num_rows() > 0) $officeSpaceData = $result->row();
}
?>

<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editInvoiceDetails";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="ownercompanyId" value="<?php echo $ownercompanyId;?>">
		<div class="inner">
			<div class="line">
				<div class="lineTitle"><?php echo $formText_InvoiceTemplate_output; ?></div>
				<div class="lineInput">
					<select name="invoice_template">
						<option value=""><?php echo $formText_Default_output;?></option>
						<option value="1" <?php if($officeSpaceData->invoice_template == 1) echo 'selected';?>><?php echo $formText_Alternative_output;?></option>
					</select>
				</div>
				<div class="clear"></div>
			</div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_InvoiceFromEmail_output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="invoiceFromEmail" value="<?php echo $officeSpaceData->invoiceFromEmail; ?>">
                </div>
                <div class="clear"></div>
            </div>
    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_InvoiceSubjectEmail_output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="invoiceSubjectEmail" value="<?php echo $officeSpaceData->invoiceSubjectEmail; ?>">
                </div>
        		<div class="clear"></div>
    		</div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_InvoiceTextEmail_output; ?></div>
                <div class="lineInput">
                    <textarea class="popupforminput botspace" name="invoiceTextEmail"><?php echo $officeSpaceData->invoiceTextEmail; ?></textarea>
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_InvoiceLogoWidth_output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="invoicelogoWidth" value="<?php echo $officeSpaceData->invoicelogoWidth; ?>">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_InvoiceLogoPositionX_output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="invoicelogoPositionX" value="<?php echo $officeSpaceData->invoicelogoPositionX; ?>">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_InvoiceLogoPositionY_output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="invoicelogoPositionY" value="<?php echo $officeSpaceData->invoicelogoPositionY; ?>">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_InvoiceBottomText_output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="invoicebottomtext" value="<?php echo $officeSpaceData->invoicebottomtext; ?>">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_CompanyAccount_output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="companyaccount" value="<?php echo $officeSpaceData->companyaccount; ?>">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_CompanyIban_output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="companyiban" value="<?php echo $officeSpaceData->companyiban; ?>">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_CompanySwift_output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="companyswift" value="<?php echo $officeSpaceData->companyswift; ?>">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_CompanyBankAccount2_output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="companyBankAccount2" value="<?php echo $officeSpaceData->companyBankAccount2; ?>">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_CompanyIban2_output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="companyiban2" value="<?php echo $officeSpaceData->companyiban2; ?>">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_CompanySwift2_output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="companyswift2" value="<?php echo $officeSpaceData->companyswift2; ?>">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_CompanyBankAccount3_output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="companyBankAccount3" value="<?php echo $officeSpaceData->companyBankAccount3; ?>">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_CompanyIban3_output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="companyiban3" value="<?php echo $officeSpaceData->companyiban3; ?>">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_CompanySwift3_output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="companyswift3" value="<?php echo $officeSpaceData->companyswift3; ?>">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_EmailFromExtra1_output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="emailFromExtra1" value="<?php echo $officeSpaceData->emailFromExtra1; ?>">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_EmailFromExtra2_output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="EmailFromExtra2" value="<?php echo $officeSpaceData->EmailFromExtra2; ?>">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_EmailFromExtra3_output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="EmailFromExtra3" value="<?php echo $officeSpaceData->EmailFromExtra3; ?>">
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
        submitHandler: function(form) {
            $.ajax({
                url: $(form).attr("action"),
                cache: false,
                type: "POST",
                dataType: "json",
                data: $(form).serialize(),
                success: function (data) {
                    if(data.redirect_url !== undefined)
                    {
                        out_popup.addClass("close-reload").data("redirect", data.redirect_url);
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
<style>

.selectDivModified {
    display:block;
}
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
	border:1px solid #e8e8e8;
	position:relative;
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
</style>
