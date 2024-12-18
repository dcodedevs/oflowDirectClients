<?php
$customerId = $_POST['customerId'] ? ($_POST['customerId']) : 0;

if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {

        if ($customerId) {
            $s_sql = "UPDATE customer SET
            updated = now(),
            updatedBy= ?,
            publicRegisterId= ?,
            name= ?,
            phone= ?,
            paStreet= ?,
            paStreet2= ?,
            paPostalNumber= ?,
            paCity= ?,
            paCountry= ?,
            vaStreet= ?,
            vaStreet2= ?,
            vaPostalNumber= ?,
            vaCity= ?,
            vaCountry= ?,
            invoiceBy= ?,
            invoiceEmail= ?,
            credittimeDays= ?,
            textVisibleInMyProfile= ?
            WHERE id =?";

            $o_main->db->query($s_sql, array($variables->loggID, $_POST['publicRegisterId'], $_POST['name'], $_POST['phone'], $_POST['paStreet'], $_POST['paStreet2'], $_POST['paPostalNumber'], $_POST['paCity'], $_POST['paCountry'], $_POST['vaStreet'], $_POST['vaStreet2'], $_POST['vaPostalNumber'], $_POST['vaCity'], $_POST['vaCountry'], $_POST['invoiceBy'], $_POST['invoiceEmail'], $_POST['credittimeDays'], $_POST['textVisibleInMyProfile'], $customerId));

            $fw_redirect_url = $_POST['redirect_url'];
        }
        else {
            $s_sql = "INSERT INTO customer SET
            created = now(),
            createdBy= ?,
			publicRegisterId= ?,
            name= ?,
            phone= ?,
            paStreet= ?,
            paStreet2= ?,
            paPostalNumber= ?,
            paCity= ?,
            paCountry= ?,
            vaStreet= ?,
            vaStreet2= ?,
            vaPostalNumber= ?,
            vaCity= ?,
            vaCountry= ?,
            invoiceBy= ?,
            invoiceEmail= ?,
            credittimeDays= ?,
            textVisibleInMyProfile= ?";

            $o_main->db->query($s_sql, array($variables->loggID, $_POST['publicRegisterId'], $_POST['name'], $_POST['phone'], $_POST['paStreet'], $_POST['paStreet2'], $_POST['paPostalNumber'], $_POST['paCity'], $_POST['paCountry'], $_POST['vaStreet'], $_POST['vaStreet2'], $_POST['vaPostalNumber'], $_POST['vaCity'], $_POST['vaCountry'], $_POST['invoiceBy'], $_POST['invoiceEmail'], $_POST['credittimeDays'], $_POST['textVisibleInMyProfile']));

            $insert_id = $o_main->db->insert_id();
			$return['create_sql'] = $s_sql;
            $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$insert_id;
        }

	}
}

if($customerId) {
    $s_sql = "SELECT * FROM customer WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($customerId));
    if($o_query && $o_query->num_rows()>0) {
        $customerData = $o_query->row_array();
    }
}
$s_sql = "SELECT * FROM customer_basisconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) {
    $customer_basisconfig = $o_query->row_array();
}
?>

<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editCustomerDetail";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="customerId" value="<?php echo $customerId;?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customerId; ?>">
		<div class="inner">
            <div class="line">
                <div class="lineTitle"><?php echo $formText_PublicRegisterNumber_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="publicRegisterId" value="<?php echo $customerData['publicRegisterId']; ?>" autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>
    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_Name_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="name" value="<?php echo $customerData['name']; ?>" required autocomplete="off">
                </div>
        		<div class="clear"></div>
    		</div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Phone_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="phone" value="<?php echo $customerData['phone']; ?>" autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_InvoiceBy_Output; ?></div>
                <div class="lineInput inlineInput">
                    <label for="invoiceByPaper"><?php echo $formText_Paper_output;?></label>
                    <input id="invoiceByPaper" type="radio" class="popupforminput botspace" name="invoiceBy" value="0" <?php if($customerData['invoiceBy'] == 0) echo 'checked';?>>
                    <label for="invoiceByEmail"><?php echo $formText_Email_output;?></label>
                    <input id="invoiceByEmail" type="radio" class="popupforminput botspace" name="invoiceBy" value="1" <?php if($customerData['invoiceBy'] == 1) echo 'checked';?>>
                </div>
                <div class="clear"></div>
            </div>
            <div class="line invoiceEmail">
                <div class="lineTitle"><?php echo $formText_InvoiceEmail_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="invoiceEmail" value="<?php echo $customerData['invoiceEmail']; ?>" required autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_CreditTimeDays_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="credittimeDays" value="<?php echo $customerData['credittimeDays']; ?>" autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>

            <?php if($customer_basisconfig['display_field_text_on_mypage']) { ?>
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_TextVisibleInMyProfile_Output; ?></div>
                    <div class="lineInput">
                        <input type="text" class="popupforminput botspace" name="textVisibleInMyProfile" value="<?php echo $customerData['textVisibleInMyProfile']; ?>" autocomplete="off">
                    </div>
                    <div class="clear"></div>
                </div>
            <?php } ?>


            <div class="line">
                <div class="lineTitle lineTitleWithSeperator"><?php echo $formText_PostalAddress_output; ?></div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Street_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="paStreet" value="<?php echo $customerData['paStreet']; ?>" autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Street2_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="paStreet2" value="<?php echo $customerData['paStreet2']; ?>" autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_PostalNumber_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="paPostalNumber" value="<?php echo $customerData['paPostalNumber']; ?>" autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_City_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="paCity" value="<?php echo $customerData['paCity']; ?>" autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Country_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="paCountry" value="<?php echo $customerData['paCountry']; ?>" autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>

            <div class="line">
                <div class="lineTitle lineTitleWithSeperator"><?php echo $formText_VisitingAddress_output; ?></div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Street_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="vaStreet" value="<?php echo $customerData['vaStreet']; ?>" autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Street2_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="vaStreet2" value="<?php echo $customerData['vaStreet2']; ?>" autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_PostalNumber_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="vaPostalNumber" value="<?php echo $customerData['vaPostalNumber']; ?>" autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_City_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="vaCity" value="<?php echo $customerData['vaCity']; ?>" autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Country_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="vaCountry" value="<?php echo $customerData['vaCountry']; ?>" autocomplete="off">
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
    // $('.output-form').on('submit', function(e) {
    //     e.preventDefault();
    //     var data = {};
    //     $(this).serializeArray().forEach(function(item, index) {
    //         data[item.name] = item.value;
    //     });
    //     ajaxCall('editCustomerDetail', data, function (json) {
    //         if (json.redirect_url) document.location.href = json.redirect_url;
    //         else out_popup.close();
    //         // console.log(json);
    //     });
    // });
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

    $("#invoiceByPaper").unbind("click").bind("click", function(){
        $(".invoiceEmail").hide();
    })
    $("#invoiceByEmail").unbind("click").bind("click", function(){
        $(".invoiceEmail").show();
    })
    $("input[name='invoiceBy']:checked").click();
});

</script>
<style>
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
