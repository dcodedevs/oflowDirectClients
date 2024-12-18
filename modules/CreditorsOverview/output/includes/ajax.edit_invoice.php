<?php
$creditor_id = $_POST['creditor_id'] ? $o_main->db->escape_str($_POST['creditor_id']) : 0;
$invoice_id = $_POST['invoice_id'] ? $o_main->db->escape_str($_POST['invoice_id']) : 0;
$action = $_POST['action'] ? $_POST['action'] : '';
$status = $_POST['status'] ? $_POST['status'] : 0;

$sql = "SELECT * FROM creditor WHERE id = $creditor_id";
$o_query = $o_main->db->query($sql);
$creditorData = $o_query ? $o_query->row_array() : array();

if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
        $originalDueDate = "";
        $date = "";
        if($_POST['original_due_date'] != "" && $_POST['original_due_date'] != "0000-00-00"){
            $originalDueDate = date("Y-m-d",strtotime($_POST['original_due_date']));
        }
        if($_POST['date'] != "" && $_POST['date'] != "0000-00-00"){
            $date =  date("Y-m-d",strtotime($_POST['date']));
        }
        $amount = str_replace(",", ".", $_POST['amount']);
		$kid_number = $_POST['kid_number'];

		$crm_customer_info = array();
		if($_POST['customer_action'] == 1){
			$s_sql = "INSERT INTO customer SET created= NOW(), name = ?, middlename = ?, lastname = ?, paStreet = ?, paPostalNumber = ?, paCity = ?, paCountry = ?, creditor_id = ?, customer_type_collect = ?, publicRegisterId = ?";
			$o_query = $o_main->db->query($s_sql, array($_POST['customer_name'], $_POST['customer_middlename'], $_POST['customer_lastname'], $_POST['paStreet'], $_POST['paPostalNumber'], $_POST['paCity'], $_POST['paCountry'], $_POST['creditor_id'], $_POST['customer_type'], $_POST['publicRegisterId']));
			if($o_query) {
				$crm_customer_id = $o_main->db->insert_id();
			}
		} else if($_POST['customer_action'] == 2){
			$crm_customer_id = $_POST['customerId'];
		}

        if ($invoice_id) {
            $sql = "UPDATE creditor_invoice SET creditor_id = ?, debitor_id = ?, invoice_number = ?, amount = ?, date=?, due_date=?, kid_number = ?, updatedBy = 'import', updated=NOW() WHERE id = ?";
            $o_query = $o_main->db->query($sql, array($creditorData['id'], $crm_customer_id,  $_POST['invoice_number'], $amount,$date,$originalDueDate, $kid_number, $invoice_id));
            $fw_redirect_url = $_POST['redirect_url'];
        } else {
            $sql = "INSERT INTO creditor_invoice SET creditor_id = ?, debitor_id = ?, invoice_number = ?, amount = ?, date=?, due_date=?, kid_number = ?, createdBy = 'import', created=NOW()";
            $o_query = $o_main->db->query($sql, array($creditorData['id'], $crm_customer_id, $_POST['invoice_number'], $amount,$date,$originalDueDate, $kid_number));

			$o_query = $o_main->db->query($sql);
            $insert_id = $o_main->db->insert_id();

            $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$creditor_id;
        }

	}
}
if($action == "deleteInvoice" && $invoice_id) {

    $sql = "SELECT * FROM creditor_invoice WHERE id = $invoice_id";
	$o_query = $o_main->db->query($sql);
    $projectData = $o_query ? $o_query->row_array() : array();

	$s_sql = "SELECT collecting_cases.* FROM collecting_cases WHERE collecting_cases.id = ?";
	$o_query = $o_main->db->query($s_sql, array($projectData['collecting_case_id']));
	$collecting_cases = ($o_query ? $o_query->row_array() : array());
	if(!$collecting_cases){
	    $sql = "DELETE FROM creditor_invoice
	    WHERE id = $invoice_id";
	    $o_query = $o_main->db->query($sql);
		return;
	} else {
		echo $formText_CanNotDeleteInvoiceWithCollectingCase_output;
		return;
	}

}
if($invoice_id) {
    $sql = "SELECT * FROM creditor_invoice WHERE id = $invoice_id";
	$o_query = $o_main->db->query($sql);
    $projectData = $o_query ? $o_query->row_array() : array();

	$s_sql = "SELECT customer.* FROM customer WHERE customer.id = ?";
    $o_query = $o_main->db->query($s_sql, array($projectData['debitor_id']));
    $customer = ($o_query ? $o_query->row_array() : array());

}

?>

<div class="popupform popupform-<?php echo $creditor_id;?>">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_invoice";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="creditor_id" value="<?php echo $creditor_id;?>">
		<input type="hidden" name="invoice_id" value="<?php echo $invoice_id;?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$creditor_id; ?>">
		<div class="inner">

			<div class="line">
				<div class="lineTitle"><?php echo $formText_Customer_Output; ?></div>
				<div class="lineInput">
					<input type="radio" class="customer_action" name="customer_action" value="2" checked autocomplete="off"/>
					<label><?php echo $formText_SelectCustomer_output;?></label>
					<input type="radio" class="customer_action" name="customer_action" value="1" autocomplete="off"/>
					<label><?php echo $formText_NewCustomer_output;?></label>
				</div>
				<div class="clear"></div>
			</div>
			<div class="newCustomerWrapper">
				<div class="line  companyField privatePersonField">
					<div class="lineTitle"><?php echo $formText_CustomerType_Output; ?></div>
					<div class="lineInput">
						<select name="customer_type" class="customerType requiredClass" autocomplete="off" required>
							<option value=""><?php echo $formText_Select_output?></option>
							<option value="0"><?php echo $formText_Company_output?></option>
							<option value="1"><?php echo $formText_PrivatePerson_output?></option>
						</select>
					</div>
					<div class="clear"></div>
				</div>
				<div class="line companyField privatePersonField">
					<div class="lineTitle"><?php echo $formText_CreditorCustomerId_Output; ?></div>
					<div class="lineInput">
						<input type="text" class="popupforminput botspace requiredClass" name="creditor_customer_id" autocomplete="off" value="" >
					</div>
					<div class="clear"></div>
				</div>
				<div class="line companyField">
	                <div class="lineTitle"><?php echo $formText_PublicRegisterNumber_Output; ?></div>
	                <div class="lineInput">
	                    <input type="text" class="popupforminput botspace requiredClass" name="publicRegisterId" value="" autocomplete="off">
	                </div>
	                <div class="clear"></div>
	            </div>
				<div class="line companyField privatePersonField">
					<div class="lineTitle"><?php echo $formText_CustomerName_Output; ?></div>
					<div class="lineInput">
						<input type="text" class="popupforminput botspace requiredClass" name="customer_name" autocomplete="off" value="">
					</div>
					<div class="clear"></div>
				</div>
	            <div class="line privatePersonField">
	                <div class="lineTitle"><?php echo $formText_MiddleName_Output; ?></div>
	                <div class="lineInput">
	                    <input type="text" class="popupforminput botspace" name="customer_middlename" value="" autocomplete="off">
	                </div>
	                <div class="clear"></div>
	            </div>
				<div class="line privatePersonField">
					<div class="lineTitle"><?php echo $formText_CustomerLastName_Output; ?></div>
					<div class="lineInput">
						<input type="text" class="popupforminput botspace requiredClass" name="customer_lastname" autocomplete="off" value="">
					</div>
					<div class="clear"></div>
				</div>
				<div class="line companyField privatePersonField">
					<div class="lineTitle"><?php echo $formText_PaStreet_Output; ?></div>
					<div class="lineInput">
						<input type="text" class="popupforminput botspace requiredClass" name="paStreet" autocomplete="off" value="">
					</div>
					<div class="clear"></div>
				</div>
				<div class="line companyField privatePersonField">
					<div class="lineTitle"><?php echo $formText_PaPostalNumber_Output; ?></div>
					<div class="lineInput">
						<input type="text" class="popupforminput botspace" name="paPostalNumber" autocomplete="off" value="">
					</div>
					<div class="clear"></div>
				</div>
				<div class="line companyField privatePersonField">
					<div class="lineTitle"><?php echo $formText_paCity_Output; ?></div>
					<div class="lineInput">
						<input type="text" class="popupforminput botspace requiredClass" name="paCity" autocomplete="off" value="">
					</div>
					<div class="clear"></div>
				</div>
				<div class="line companyField privatePersonField">
					<div class="lineTitle"><?php echo $formText_paCountry_Output; ?></div>
					<div class="lineInput">
						<input type="text" class="popupforminput botspace" name="paCountry" autocomplete="off" value="">
					</div>
					<div class="clear"></div>
				</div>
			</div>
			<div class="oldCustomerWrapper">
				<div class="line">
					<div class="lineTitle"><?php echo $formText_Customer_Output; ?></div>
					<div class="lineInput">
						<?php if($customer) { ?>
						<a href="#" class="selectCustomer"><?php echo $customer['name']?></a>
						<?php } else { ?>
						<a href="#" class="selectCustomer"><?php echo $formText_SelectCustomer_Output;?></a>
						<?php } ?>
						<input type="hidden" name="customerId" id="customerId" class="requiredClass" value="<?php print $customer['id'];?>" required>
					</div>
					<div class="clear"></div>
				</div>
			</div>

            <div class="line">
				<div class="lineTitle"><?php echo $formText_InvoiceNumber_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="invoice_number" value="<?php echo $projectData['invoice_number']; ?>" required/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Amount_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="amount" value="<?php echo $projectData['amount']; ?>" required/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Date_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace datepicker" required autocomplete="off" name="date" value="<?php if($projectData['date'] != "" && $projectData['date'] != "0000-00-00") echo date("d.m.Y", strtotime($projectData['date'])); ?>"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_OriginalDueDate_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace datepicker" required autocomplete="off" name="original_due_date" value="<?php if($projectData['due_date'] != "" && $projectData['due_date'] != "0000-00-00") echo date("d.m.Y", strtotime($projectData['due_date'])); ?>"/>
				</div>
				<div class="clear"></div>
			</div>

			<div class="line">
				<div class="lineTitle"><?php echo $formText_KidNumber_Output_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace datepicker"  autocomplete="off" name="kid_number" value="<?php echo $projectData['kid_number']; ?>"/>
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
<style>
.newCustomerWrapper {
	display: none;
}
.oldCustomerWrapper {
	display: block;
}
</style>
<script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

$(document).ready(function() {
	$(".sync_from_accounting").change(function(){
		if($(this).val() == 1) {
			$(".sync_from_accounting_trigger").show();
		} else if($(this).val() == 0) {
			$(".sync_from_accounting_trigger").hide();
		} else {
			$(".sync_from_accounting_trigger").hide();
		}
	}).change();
	$(".create_cases").change(function(){
		if($(this).val() == ""){
			$(".manual_wrapper").hide();
			$(".automatic_wrapper").hide();
		} else if($(this).val() == 1) {
			$(".manual_wrapper").show();
			$(".automatic_wrapper").hide();
		} else if($(this).val() == 0) {
			$(".manual_wrapper").hide();
			$(".automatic_wrapper").show();
		}
	}).change();
	$(".datepicker").datepicker({
		"dateFormat": "d.m.yy"
	});
    $(".popupform-<?php echo $creditor_id;?> form.output-form").validate({
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
                $(".popupform-<?php echo $creditor_id;?> #popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                $(".popupform-<?php echo $creditor_id;?> #popup-validate-message").show();
                $('.popupform-<?php echo $creditor_id;?> #popupeditbox').css('height', $('.popupform-<?php echo $creditor_id;?> #popupeditboxcontent').height());
                fw_loading_end();
            });
        },
        invalidHandler: function(event, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var message = errors == 1
                ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                $(".popupform-<?php echo $creditor_id;?> #popup-validate-message").html(message);
                $(".popupform-<?php echo $creditor_id;?> #popup-validate-message").show();
                $('.popupform-<?php echo $creditor_id;?> #popupeditbox').css('height', $('#popupeditboxcontent').height());
            } else {
                $(".popupform-<?php echo $creditor_id;?> #popup-validate-message").hide();
            }
            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
        },
        errorPlacement: function(error, element) {
            if(element.attr("name") == "creditor_id") {
                error.insertAfter(".popupform-<?php echo $creditor_id;?> .selectCreditor");
            }
            if(element.attr("name") == "customer_id") {
                error.insertAfter(".popupform-<?php echo $creditor_id;?> .selectCustomer");
            }
        },
        messages: {
            creditor_id: "<?php echo $formText_SelectTheCreditor_output;?>",
            customer_id: "<?php echo $formText_SelectTheCustomer_output;?>",
        }
    });
    $(".updateEntityId").on("click", function(e){
        e.preventDefault();
		var data = {
			creditor_id: $(this).data('creditor-id')
		};
		ajaxCall('update_entity_id', data, function(json) {
			$('#popupeditboxcontent2').html('');
			$('#popupeditboxcontent2').html(json.html);
			out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
			$("#popupeditbox2:not(.opened)").remove();
		});
    })
	$(".datefield").datepicker({
		dateFormat: "d.m.yy",
		firstDay: 1
	})
    $(".popupform-<?php echo $creditor_id;?> .selectCreditor").unbind("click").bind("click", function(){
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
    $(".popupform-<?php echo $creditor_id;?> .selectCustomer").unbind("click").bind("click", function(){
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1, creditor_id: '<?php echo $creditor_id;?>'};
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


    $(".popupform-<?php echo $creditor_id;?> .selectOwner").unbind("click").bind("click", function(){
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

	$(".customerType").change(function(){
        var type = $(this).val();
		$(".newCustomerWrapper input.requiredClass ").prop("required", false);
		$(".newCustomerWrapper select.requiredClass ").prop("required", false);
		if($(".customer_action:checked").val() == 1){
	        if(type == 0) {
	            $(".privatePersonField").hide();
	            $(".companyField").show();
				$(".newCustomerWrapper .companyField input.requiredClass ").prop("required", true);
	            $(".newCustomerWrapper .companyField select.requiredClass ").prop("required", true);
	        } else if(type == 1) {
				$(".companyField").hide();
	            $(".privatePersonField").show();
				$(".newCustomerWrapper .privatePersonField input.requiredClass").prop("required", true);
	            $(".newCustomerWrapper .privatePersonField select.requiredClass").prop("required", true);
	        }else if(type == ""){
				$(".newCustomerWrapper .privatePersonField.companyField input.requiredClass").prop("required", true);
	            $(".newCustomerWrapper .privatePersonField.companyField select.requiredClass").prop("required", true);
			}
		}
		$(window).resize();
    }).change();
    $(".customer_action").click(function(){
        if($(this).val() == 1){
            $(".oldCustomerWrapper input.requiredClass ").prop("required", false);
            $(".oldCustomerWrapper select.requiredClass ").prop("required", false);
            $(".newCustomerWrapper input.requiredClass ").prop("required", true);
            $(".newCustomerWrapper select.requiredClass ").prop("required", true);
            $(".oldCustomerWrapper").hide();
            $(".newCustomerWrapper").show();
        } else if($(this).val() == 2){
            $(".oldCustomerWrapper input.requiredClass ").prop("required", true);
            $(".oldCustomerWrapper select.requiredClass ").prop("required", true);
            $(".newCustomerWrapper input.requiredClass ").prop("required", false);
            $(".newCustomerWrapper select.requiredClass ").prop("required", false);
            $(".newCustomerWrapper").hide();
            $(".oldCustomerWrapper").show();
        }
		$(".customerType").change();
		$(window).resize();
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
