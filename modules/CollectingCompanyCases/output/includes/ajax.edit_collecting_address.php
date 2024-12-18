<?php
$customerId = $_POST['customer_id'] ? $_POST['customer_id'] : 0;
$creditorId = $_POST['creditor_id'] ? $_POST['creditor_id'] : 0;

$sql = "SELECT * FROM customer WHERE id = '".$o_main->db->escape_str($customerId)."' AND '".$o_main->db->escape_str($creditorId)."'";
$o_query = $o_main->db->query($sql);
$customerData = $o_query ? $o_query->row_array() : array();

if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
        if ($customerData) {
            $sql = "UPDATE customer SET
            updated = now(),
            updatedBy='".$variables->loggID."',
			extraName='".$o_main->db->escape_str($_POST['extraName'])."',
			extraPublicRegisterId='".$o_main->db->escape_str($_POST['extraPublicRegisterId'])."',
			extra_social_security_number='".$o_main->db->escape_str($_POST['extra_social_security_number'])."',
			extra_phone='".$o_main->db->escape_str($_POST['extra_phone'])."',
			extra_invoice_email='".$o_main->db->escape_str($_POST['extra_invoice_email'])."',
			extraStreet='".$o_main->db->escape_str($_POST['extraStreet'])."',
			extraPostalNumber='".$o_main->db->escape_str($_POST['extraPostalNumber'])."',
			extraCity='".$o_main->db->escape_str($_POST['extraCity'])."',
			extraCountry='".$o_main->db->escape_str($_POST['extraCountry'])."',
			customer_type_for_collecting_cases ='".$o_main->db->escape_str($_POST['customer_type_for_collecting_cases'])."',
			extra_language ='".$o_main->db->escape_str($_POST['extra_language'])."',
			send_all_collecting_company_letters_by_email = '".$o_main->db->escape_str($_POST['send_all_collecting_company_letters_by_email'])."'
            WHERE id = '".$o_main->db->escape_str($customerData['id'])."'";

			$o_query = $o_main->db->query($sql);
            $fw_redirect_url = $_POST['redirect_url'];
        } else {

        }
	}
}
$customer_type_collect = intval($customerData['customer_type_collect']);
if($customerData['customer_type_collect_addition'] >  0){
	$customer_type_collect = $customerData['customer_type_collect_addition'] - 1;
}
?>

<div class="popupform popupform-<?php echo $caseId;?>">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_collecting_address";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="customer_id" value="<?php echo $customerId;?>">
		<input type="hidden" name="creditor_id" value="<?php echo $creditorId;?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$caseId; ?>">
		<div class="inner">			
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_ExternalCustomerNumber_Output; ?></div>
        		<div class="lineInput">
                    <?php echo $customerData['creditor_customer_id'];?>
                </div>
        		<div class="clear"></div>
    		</div>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_CrmName_Output; ?></div>
        		<div class="lineInput">
                    <?php echo $customerData['name'];?>
                </div>
        		<div class="clear"></div>
    		</div>
			<?php if($customer_type_collect == 0) { ?>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_CrmRegistered_Output; ?></div>
        		<div class="lineInput">
                    <?php echo $customerData['publicRegisterId'];?>
                </div>
        		<div class="clear"></div>
    		</div>
			<?php } else { ?>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_CrmSocialSecurityNumber_Output; ?></div>
        		<div class="lineInput">
                    <?php echo $customerData['social_security_number'];?>
                </div>
        		<div class="clear"></div>
    		</div>
			<?php } ?>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_CrmStreet_Output; ?></div>
        		<div class="lineInput">
                    <?php echo $customerData['paStreet'];?>
                </div>
        		<div class="clear"></div>
    		</div>

			<div class="line">
        		<div class="lineTitle"><?php echo $formText_CrmPostalNumber_Output; ?></div>
        		<div class="lineInput">
                    <?php echo $customerData['paPostalNumber'];?>
                </div>
        		<div class="clear"></div>
    		</div>

			<div class="line">
        		<div class="lineTitle"><?php echo $formText_CrmCity_Output; ?></div>
        		<div class="lineInput">
                    <?php echo $customerData['paCity'];?>
                </div>
        		<div class="clear"></div>
    		</div>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_CrmCountry_Output; ?></div>
        		<div class="lineInput">
                    <?php echo $customerData['paCountry'];?>
                </div>
        		<div class="clear"></div>
    		</div>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_CrmPhone_Output; ?></div>
        		<div class="lineInput">
                    <?php echo $customerData['phone'];?>
                </div>
        		<div class="clear"></div>
    		</div>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_CrmEmail_Output; ?></div>
        		<div class="lineInput">                    
					<?php echo $customerData['invoiceEmail'];?>
                </div>
        		<div class="clear"></div>
    		</div>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_CrmCustomerType_Output; ?></div>
        		<div class="lineInput">
                    <?php
					if($customer_type_collect == 0) {
						echo $formText_Company_output;
					} else {
						echo $formText_PrivatePerson_output;
					}
					?>
                </div>
        		<div class="clear"></div>
    		</div>
			
			<div class="crm_address_copy"><?php echo $formText_CopyAddressFromCrm_output;?></div>

			<div class="line">
        		<div class="lineTitle"><?php echo $formText_CollectingName_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace extraName" autocomplete="off" name="extraName" value="<?php echo $customerData['extraName'];?>">
                </div>
        		<div class="clear"></div>
    		</div>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_CollectingPublictRegisterId_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace extraPublicRegisterId" autocomplete="off" name="extraPublicRegisterId" value="<?php echo $customerData['extraPublicRegisterId'];?>">
                </div>
        		<div class="clear"></div>
    		</div>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_CollectingSocialSecurityNumber_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace extraSocialSecurityNumber" autocomplete="off" name="extra_social_security_number" value="<?php echo $customerData['extra_social_security_number'];?>">
                </div>
        		<div class="clear"></div>
    		</div>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_CollectingPhone_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace extraPhone" autocomplete="off" name="extra_phone" value="<?php echo $customerData['extra_phone'];?>">
                </div>
        		<div class="clear"></div>
    		</div>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_CollectingInvoiceEmail_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace extraInvoiceEmail" autocomplete="off" name="extra_invoice_email" value="<?php echo $customerData['extra_invoice_email'];?>">
                </div>
        		<div class="clear"></div>
    		</div>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_CollectingStreet_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace extraStreet" autocomplete="off" name="extraStreet" value="<?php echo $customerData['extraStreet'];?>">
                </div>
        		<div class="clear"></div>
    		</div>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_CollectingPostalNumber_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace extraPostalNumber" autocomplete="off" name="extraPostalNumber" value="<?php echo $customerData['extraPostalNumber'];?>">
                </div>
        		<div class="clear"></div>
    		</div>
    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_CollectingCity_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace extraCity" autocomplete="off" name="extraCity" value="<?php echo $customerData['extraCity'];?>" >
                </div>
        		<div class="clear"></div>
    		</div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_CollectingCountry_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace extraCountry" autocomplete="off" name="extraCountry" value="<?php echo $customerData['extraCountry'];?>">
                </div>
                <div class="clear"></div>
            </div>

			<div class="line">
        		<div class="lineTitle"><?php echo $formText_CollectingCustomerType_Output; ?></div>
        		<div class="lineInput">
					<select class="popupforminput botspace extraCustomerType" autocomplete="off" name="customer_type_for_collecting_cases">
						<option value="0"><?php echo $formText_UseCrmCustomerType_output;?></option>
						<option value="1" <?php if($customerData['customer_type_for_collecting_cases'] == 1)  echo 'selected';?>><?php echo $formText_Company_output;?></option>
						<option value="2" <?php if($customerData['customer_type_for_collecting_cases'] == 2)  echo 'selected';?>><?php echo $formText_PrivatePerson_output;?></option>
					</select>
                </div>
        		<div class="clear"></div>
    		</div>

			<div class="line">
        		<div class="lineTitle"><?php echo $formText_DebitorLanguage_Output; ?></div>
        		<div class="lineInput">
					<select class="popupforminput botspace" autocomplete="off" name="extra_language">
						<option value="0"><?php echo $formText_Default_output;?></option>
						<option value="1" <?php if($customerData['extra_language'] == 1)  echo 'selected';?>><?php echo $formText_English_Output;?></option>
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
<script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

$(document).ready(function() {
	$(".crm_address_copy").off("click").on("click", function(){
		$(".extraCustomerType").val("<?php echo htmlspecialchars(str_replace(array("\r", "\n"), ' ', $customer_type_collect+1)); ?>").change();
		$(".extraName").val("<?php echo htmlspecialchars(str_replace(array("\r", "\n"), ' ',$customerData['name'])); if($customerData['middlename'] != "") echo ' '.htmlspecialchars(str_replace(array("\r", "\n"), ' ',$customerData['middlename'])); if($customerData['lastname'] != "") echo ' '.htmlspecialchars(str_replace(array("\r", "\n"), ' ',$customerData['lastname'])); ?>");
		$(".extraPublicRegisterId").val("<?php echo htmlspecialchars(str_replace(array("\r", "\n"), ' ', $customerData['publicRegisterId'])); ?>");
		$(".extraSocialSecurityNumber").val("<?php echo htmlspecialchars(str_replace(array("\r", "\n"), ' ', $customerData['social_security_number'])); ?>");
		$(".extraPhone").val("<?php echo htmlspecialchars(str_replace(array("\r", "\n"), ' ', $customerData['phone'])); ?>");
		$(".extraInvoiceEmail").val("<?php echo htmlspecialchars(str_replace(array("\r", "\n"), ' ', $customerData['invoiceEmail'])); ?>");
		$(".extraStreet").val("<?php echo htmlspecialchars(str_replace(array("\r", "\n"), ' ', $customerData['paStreet'])); ?>");
		$(".extraPostalNumber").val("<?php echo htmlspecialchars(str_replace(array("\r", "\n"), ' ', $customerData['paPostalNumber'])); ?>");
		$(".extraCity").val("<?php echo htmlspecialchars(str_replace(array("\r", "\n"), ' ', $customerData['paCity'])); ?>");
		$(".extraCountry").val("<?php echo htmlspecialchars(str_replace(array("\r", "\n"), ' ', $customerData['paCountry'])); ?>");
	})
    $(".extraCustomerType").off('change').on('change', function(){
		if(2 == $(this).val())
		{
			$(".extraPublicRegisterId").val('').closest('.line').hide();
			$(".extraSocialSecurityNumber").closest('.line').show();
		} else {
			$(".extraPublicRegisterId").closest('.line').show();
			$(".extraSocialSecurityNumber").val('').closest('.line').hide();
		}
	}).change();
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
                    out_popup.addClass("close-reload");
                    out_popup.close();
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
.crm_address_copy {
	cursor: pointer;
	color: #46b2e2;
	margin: 10px 0px;
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
</style>
