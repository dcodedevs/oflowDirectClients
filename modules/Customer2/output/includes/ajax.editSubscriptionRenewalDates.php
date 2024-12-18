<?php
$subscribtionId = $_POST['subscribtionId'] ? ($_POST['subscribtionId']) : 0;
$customerId = $_POST['customerId'] ? ($_POST['customerId']) : 0;
$action = $_POST['action'] ? ($_POST['action']) : '';

// Save
if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
        if ($subscribtionId) {
            $s_sql = "UPDATE subscriptionmulti SET
            updated = now(),
            updatedBy= ?,
            renewalappearance = ?,
            renewalappearance_daynumber= ?,
            invoicedate_suggestion= ?,
            invoicedate_daynumber= ?,
            duedate= ?,
            duedate_daynumber= ?
            WHERE id = ?";
            $o_main->db->query($s_sql, array($variables->loggID, $_POST['renewalappearance'], $_POST['renewalappearance_daynumber'], $_POST['invoicedate_suggestion'], $_POST['invoicedate_daynumber'],  $_POST['duedate'],  $_POST['duedate_daynumber'], $subscribtionId));
            $fw_return_data = $s_sql;
            $fw_redirect_url = $_POST['redirect_url'];
        }
	}
}

$s_sql = "SELECT * FROM customer_basisconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $customer_basisconfig = $o_query->row_array();
}

$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_customer_accountconfig = $o_query->row_array();
}

if($v_customer_accountconfig['hide_subscription_due_date_from_customer_setting'] > 0) {
    if($v_customer_accountconfig['hide_subscription_due_date_from_customer_setting'] == 1){
        $customer_basisconfig['hide_subscription_due_date_from_customer_setting'] = 1;
    }
    if($v_customer_accountconfig['hide_subscription_due_date_from_customer_setting'] == 2){
        $customer_basisconfig['hide_subscription_due_date_from_customer_setting'] = 0;
    }
}
if($v_customer_accountconfig['hide_subscription_due_date_month_after_invoicedate'] > 0) {
    if($v_customer_accountconfig['hide_subscription_due_date_month_after_invoicedate'] == 1){
        $customer_basisconfig['hide_subscription_due_date_month_after_invoicedate'] = 1;
    }
    if($v_customer_accountconfig['hide_subscription_due_date_month_after_invoicedate'] == 2){
        $customer_basisconfig['hide_subscription_due_date_month_after_invoicedate'] = 0;
    }
}
if($v_customer_accountconfig['hide_subscription_due_date_same_month_as_invoicedate'] > 0) {
    if($v_customer_accountconfig['hide_subscription_due_date_same_month_as_invoicedate'] == 1){
        $customer_basisconfig['hide_subscription_due_date_same_month_as_invoicedate'] = 1;
    }
    if($v_customer_accountconfig['hide_subscription_due_date_same_month_as_invoicedate'] == 2){
        $customer_basisconfig['hide_subscription_due_date_same_month_as_invoicedate'] = 0;
    }
}

if($v_customer_accountconfig['hide_subscription_invoice_date_suggestion_month_before_renewal'] > 0) {
    if($v_customer_accountconfig['hide_subscription_invoice_date_suggestion_month_before_renewal'] == 1){
        $customer_basisconfig['hide_subscription_invoice_date_suggestion_month_before_renewal'] = 1;
    }
    if($v_customer_accountconfig['hide_subscription_invoice_date_suggestion_month_before_renewal'] == 2){
        $customer_basisconfig['hide_subscription_invoice_date_suggestion_month_before_renewal'] = 0;
    }
}
if($v_customer_accountconfig['hide_subscription_invoice_date_suggestion_same_as_renewal'] > 0) {
    if($v_customer_accountconfig['hide_subscription_invoice_date_suggestion_same_as_renewal'] == 1){
        $customer_basisconfig['hide_subscription_invoice_date_suggestion_same_as_renewal'] = 1;
    }
    if($v_customer_accountconfig['hide_subscription_invoice_date_suggestion_same_as_renewal'] == 2){
        $customer_basisconfig['hide_subscription_invoice_date_suggestion_same_as_renewal'] = 0;
    }
}
if($v_customer_accountconfig['hide_subscription_invoice_date_suggestion_same_month_renewal'] > 0) {
    if($v_customer_accountconfig['hide_subscription_invoice_date_suggestion_same_month_renewal'] == 1){
        $customer_basisconfig['hide_subscription_invoice_date_suggestion_same_month_renewal'] = 1;
    }
    if($v_customer_accountconfig['hide_subscription_invoice_date_suggestion_same_month_renewal'] == 2){
        $customer_basisconfig['hide_subscription_invoice_date_suggestion_same_month_renewal'] = 0;
    }
}

if($v_customer_accountconfig['hide_subscription_renewal_appeareance_month_before_renewaldate'] > 0) {
    if($v_customer_accountconfig['hide_subscription_renewal_appeareance_month_before_renewaldate'] == 1){
        $customer_basisconfig['hide_subscription_renewal_appeareance_month_before_renewaldate'] = 1;
    }
    if($v_customer_accountconfig['hide_subscription_renewal_appeareance_month_before_renewaldate'] == 2){
        $customer_basisconfig['hide_subscription_renewal_appeareance_month_before_renewaldate'] = 0;
    }
}
if($v_customer_accountconfig['hide_subscription_renewal_appeareance_number_of_days_after'] > 0) {
    if($v_customer_accountconfig['hide_subscription_renewal_appeareance_number_of_days_after'] == 1){
        $customer_basisconfig['hide_subscription_renewal_appeareance_number_of_days_after'] = 1;
    }
    if($v_customer_accountconfig['hide_subscription_renewal_appeareance_number_of_days_after'] == 2){
        $customer_basisconfig['hide_subscription_renewal_appeareance_number_of_days_after'] = 0;
    }
}
if($v_customer_accountconfig['hide_subscription_renewal_appeareance_number_of_days_before'] > 0) {
    if($v_customer_accountconfig['hide_subscription_renewal_appeareance_number_of_days_before'] == 1){
        $customer_basisconfig['hide_subscription_renewal_appeareance_number_of_days_before'] = 1;
    }
    if($v_customer_accountconfig['hide_subscription_renewal_appeareance_number_of_days_before'] == 2){
        $customer_basisconfig['hide_subscription_renewal_appeareance_number_of_days_before'] = 0;
    }
}
if($v_customer_accountconfig['hide_subscription_renewal_appeareance_same_month_as_renewaldate'] > 0) {
    if($v_customer_accountconfig['hide_subscription_renewal_appeareance_same_month_as_renewaldate'] == 1){
        $customer_basisconfig['hide_subscription_renewal_appeareance_same_month_as_renewaldate'] = 1;
    }
    if($v_customer_accountconfig['hide_subscription_renewal_appeareance_same_month_as_renewaldate'] == 2){
        $customer_basisconfig['hide_subscription_renewal_appeareance_same_month_as_renewaldate'] = 0;
    }
}


if($subscribtionId) {
    $s_sql = "SELECT * FROM subscriptionmulti WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($subscribtionId));
    if($o_query && $o_query->num_rows()>0) {
        $subscribtionData = $o_query->row_array();
    }
}

$s_sql = "SELECT * FROM customer WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($customerId));
if($o_query && $o_query->num_rows()>0) {
    $customer = $o_query->row_array();
}

function formatDate($date) {
    global $formText_NotSet_output;
    if ($date == '0000-00-00' || !$date || empty($date)) return '';
    return date('d.m.Y', strtotime($date));
}

function unformatDate($date) {
    $d = explode('.', $date);
    return $d[2].'-'.$d[1].'-'.$d[0];
}
$ownercompanies = array();

$s_sql = "SELECT * FROM ownercompany";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) {
    $ownercompanies = $o_query->result_array();
}

$subscriptiontypes = array();

$s_sql = "SELECT * FROM subscriptiontype";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) {
    $subscriptiontypes = $o_query->result_array();
}

$s_sql = "SELECT * FROM default_repeatingorder_invoicedate_settings ORDER BY sortnr ASC";
$o_query = $o_main->db->query($s_sql);
$default_repeatingorder_invoicedate_settings = $o_query ? $o_query->result_array() : array();

$currentDefaultSettingId = 0;
if($subscribtionData) {
    $s_sql = "SELECT * FROM default_repeatingorder_invoicedate_settings WHERE renewalappearance = ? AND renewalappearance_daynumber = ? AND invoicedate_suggestion = ? AND invoicedate_daynumber = ? AND duedate = ? AND duedate_daynumber = ?";

    $o_query = $o_main->db->query($s_sql, array(intval($subscribtionData['renewalappearance']),intval($subscribtionData['renewalappearance_daynumber']),intval($subscribtionData['invoicedate_suggestion']),intval($subscribtionData['invoicedate_daynumber']),intval($subscribtionData['duedate']),intval($subscribtionData['duedate_daynumber'])));
    $currentDefaultSetting = $o_query ? $o_query->row_array() : array();
    if($currentDefaultSetting) {
        $currentDefaultSettingId = $currentDefaultSetting['id'];
    }
}
?>

<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editSubscriptionRenewalDates";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="subscribtionId" value="<?php echo $subscribtionId;?>">
		<input type="hidden" name="customerId" value="<?php echo $customerId;?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customerId; ?>">
		<div class="inner">
            <?php if(count($default_repeatingorder_invoicedate_settings) > 0) { ?>
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_Settings_Output; ?></div>
                    <div class="lineInput">
                        <select class="defaultSettings">
                            <?php
                            foreach($default_repeatingorder_invoicedate_settings as $default_repeatingorder_invoicedate_setting){
                                ?>
                                    <option value="<?php echo $default_repeatingorder_invoicedate_setting['id']?>" <?php if($currentDefaultSettingId == $default_repeatingorder_invoicedate_setting['id']) echo 'selected';?> data-field1="<?php echo $default_repeatingorder_invoicedate_setting['renewalappearance'];?>" data-field2="<?php echo $default_repeatingorder_invoicedate_setting['renewalappearance_daynumber'];?>" data-field3="<?php echo $default_repeatingorder_invoicedate_setting['invoicedate_suggestion'];?>" data-field4="<?php echo $default_repeatingorder_invoicedate_setting['invoicedate_daynumber'];?>" data-field5="<?php echo $default_repeatingorder_invoicedate_setting['duedate'];?>" data-field6="<?php echo $default_repeatingorder_invoicedate_setting['duedate_daynumber'];?>"><?php echo $default_repeatingorder_invoicedate_setting['name'];?></option>
                                <?php
                            }
                            ?>
                            <option value="custom" <?php if($currentDefaultSettingId == 0) echo 'selected';?>><?php echo $formText_Customized_output;?></option>
                        </select>
                    </div>
                    <div class="clear"></div>
                </div>
                <script type="text/javascript">
                    $(".defaultSettings").change(function(){
                        var field1 = $(this).find(':selected').data("field1");
                        var field2 = $(this).find(':selected').data("field2");
                        var field3 = $(this).find(':selected').data("field3");
                        var field4 = $(this).find(':selected').data("field4");
                        var field5 = $(this).find(':selected').data("field5");
                        var field6 = $(this).find(':selected').data("field6");
                        if(field1 != undefined && field2 != undefined && field3 != undefined && field4 != undefined && field5 != undefined && field6 != undefined){
                            $(".renewalappearance").val(field1);
                            $(".renewalappearance_daynumber").val(field2);
                            $(".invoicedate_suggestion").val(field3);
                            $(".invoicedate_daynumber").val(field4);
                            $(".duedate").val(field5);
                            $(".duedate_daynumber").val(field6);

                            //
                            if($(".renewalappearance").val() != 0) {
                                $(".renewalAppearanceDays").show();
                            } else {
                                $(".renewalAppearanceDays").hide();
                            }
                            if($(".invoicedate_suggestion").val() != 0 && $(".invoicedate_suggestion").val() != 1) {
                                $(".invoiceDateDays").show();
                            } else {
                                $(".invoiceDateDays").hide();
                            }
                            if($(".duedate").val() != 0) {
                                $(".dueDateDays").show();
                            } else {
                                $(".dueDateDays").hide();
                            }
                        }
                    })
                    $(".defaultSettings").change();
                </script>
            <?php } ?>

            <div class="line">
                <div class="lineTitle"><?php echo $formText_RenewalAppearence_Output; ?></div>
                <div class="lineInput">
                    <select name="renewalappearance" class="renewalappearance" required>
                        <option value=""><?php echo $formText_Select_output;?></option>
                        <option value="0" <?php echo $subscribtionData['renewalappearance'] == 0 ? 'selected="selected"' : ''; ?>><?php echo $formText_SameDayAsRenewaldate_output; ?></option>
                        <?php if(!$customer_basisconfig['hide_subscription_renewal_appeareance_number_of_days_before']) { ?>
                            <option value="1" <?php echo $subscribtionData['renewalappearance'] == 1 ? 'selected="selected"' : ''; ?>><?php echo $formText_NumberOfDaysBeforeRenewaldate_output; ?></option>
                        <?php } ?>
                        <?php if(!$customer_basisconfig['hide_subscription_renewal_appeareance_number_of_days_after']) { ?>
                        <option value="2" <?php echo $subscribtionData['renewalappearance'] == 2 ? 'selected="selected"' : ''; ?>><?php echo $formText_NumberOfDaysAfterRenewaldate_output; ?></option>
                        <?php } ?>
                        <?php if(!$customer_basisconfig['hide_subscription_renewal_appeareance_month_before_renewaldate']) { ?>
                        <option value="3" <?php echo $subscribtionData['renewalappearance'] == 3 ? 'selected="selected"' : ''; ?>><?php echo $formText_MonthBefoterRenewalDateDaynumber_output; ?></option>
                        <?php } ?>
                        <?php if(!$customer_basisconfig['hide_subscription_renewal_appeareance_month_after_renewaldate']) { ?>
                        <option value="5" <?php echo $subscribtionData['renewalappearance'] == 5 ? 'selected="selected"' : ''; ?>><?php echo $formText_MonthAfterRenewalDateDaynumber_output; ?></option>
                        <?php } ?>
                        <?php if(!$customer_basisconfig['hide_subscription_renewal_appeareance_same_month_as_renewaldate']) { ?>
                        <option value="4" <?php echo $subscribtionData['renewalappearance'] == 4 ? 'selected="selected"' : ''; ?>><?php echo $formText_SameMonthAsRenewaldateDaynumber_output; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="clear"></div>
            </div>
            <div class="line renewalAppearanceDays">
                <div class="lineTitle"><?php echo $formText_RenewalAppearenceDays_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace renewalappearance_daynumber" name="renewalappearance_daynumber" value="<?php echo $subscribtionData['renewalappearance_daynumber']; ?>" autocomplete="off">
                </div>
            </div>

            <div class="line">
                <div class="lineTitle"><?php echo $formText_InvoiceDateSuggestion_Output; ?></div>
                <div class="lineInput">
                    <select name="invoicedate_suggestion" class="invoicedate_suggestion" required>
                        <option value=""><?php echo $formText_Select_output;?></option>
                        <option value="0" <?php echo $subscribtionData['invoicedate_suggestion'] == 0 ? 'selected="selected"' : ''; ?>><?php echo $formText_CurrentDate_output; ?></option>
                        <?php if(!$customer_basisconfig['hide_subscription_invoice_date_suggestion_same_as_renewal']) { ?>
                            <option value="1" <?php echo $subscribtionData['invoicedate_suggestion'] == 1 ? 'selected="selected"' : ''; ?>><?php echo $formText_SameAsRenewaldate_output; ?></option>
                        <?php } ?>
                        <?php if(!$customer_basisconfig['hide_subscription_invoice_date_suggestion_month_before_renewal']) { ?>
                            <option value="2" <?php echo $subscribtionData['invoicedate_suggestion'] == 2 ? 'selected="selected"' : ''; ?>><?php echo $formText_MonthBeforeRenewaldateDaynumber_output; ?></option>
                        <?php } ?>
                        <?php if(!$customer_basisconfig['hide_subscription_invoice_date_suggestion_same_month_renewal']) { ?>
                            <option value="3" <?php echo $subscribtionData['invoicedate_suggestion'] == 3 ? 'selected="selected"' : ''; ?>><?php echo $formText_SameMonthDaynumber_output; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="clear"></div>
            </div>
            <div class="line invoiceDateDays">
                <div class="lineTitle"><?php echo $formText_InvoiceDateDays_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace invoicedate_daynumber" name="invoicedate_daynumber" value="<?php echo $subscribtionData['invoicedate_daynumber']; ?>" autocomplete="off">
                </div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_DueDate_Output; ?></div>
                <div class="lineInput">
                    <select name="duedate" class="duedate" required>
                        <option value=""><?php echo $formText_Select_output;?></option>
                        <?php if(!$customer_basisconfig['hide_subscription_due_date_from_customer_setting']) { ?>
                            <option value="0" <?php echo $subscribtionData['duedate'] == 0 ? 'selected="selected"' : ''; ?>><?php echo $formText_FromCustomerSetting_output; ?></option>
                        <?php } ?>
                        <option value="1" <?php echo $subscribtionData['duedate'] == 1 ? 'selected="selected"' : ''; ?>><?php echo $formText_NumberOfDaysAfterInvoiceDate_output; ?></option>
                        <?php if(!$customer_basisconfig['hide_subscription_due_date_same_month_as_invoicedate']) { ?>
                            <option value="2" <?php echo $subscribtionData['duedate'] == 2 ? 'selected="selected"' : ''; ?>><?php echo $formText_SameMonthAsInvoicedateDaynumber_output; ?></option>
                        <?php } ?>
                        <?php if(!$customer_basisconfig['hide_subscription_due_date_month_after_invoicedate']) { ?>
                            <option value="3" <?php echo $subscribtionData['duedate'] == 3 ? 'selected="selected"' : ''; ?>><?php echo $formText_MonthAfterInvoicedateDaynumber_output; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="clear"></div>
            </div>
            <div class="line dueDateDays">
                <div class="lineTitle"><?php echo $formText_DueDateDays_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace duedate_daynumber" name="duedate_daynumber" value="<?php echo $subscribtionData['duedate_daynumber']; ?>" autocomplete="off">
                </div>
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
                        out_popup.addClass("close-reload");
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
    $(".renewalappearance").change(function(){
        if($(this).val() != 0) {
            $(".renewalAppearanceDays").show();
        } else {
            $(".renewalAppearanceDays").hide();
        }
        checkIfDefaultSettings();
    })
    $(".invoicedate_suggestion").change(function(){
        if($(this).val() != 0 && $(this).val() != 1) {
            $(".invoiceDateDays").show();
        } else {
            $(".invoiceDateDays").hide();
        }
        checkIfDefaultSettings();
    })
    $(".duedate").change(function(){
        if($(this).val() != 0) {
            $(".dueDateDays").show();
        } else {
            $(".dueDateDays").hide();
        }
        checkIfDefaultSettings();
    })
    $(".renewalappearance_daynumber").keyup(function(){
        checkIfDefaultSettings();
    })
    $(".invoicedate_daynumber").keyup(function(){
        checkIfDefaultSettings();
    })
    $(".duedate_daynumber").keyup(function(){
        checkIfDefaultSettings();
    })
    function checkIfDefaultSettings() {
        var field1 = $(".renewalappearance").val();
        var field2 = $(".renewalappearance_daynumber").val();
        var field3 = $(".invoicedate_suggestion").val();
        var field4 = $(".invoicedate_daynumber").val();
        var field5 = $(".duedate").val();
        var field6 = $(".duedate_daynumber").val();

        var default_option = $('.defaultSettings option[data-field1="'+field1+'"][data-field2="'+field2+'"][data-field3="'+field3+'"][data-field4="'+field4+'"][data-field5="'+field5+'"][data-field6="'+field6+'"]');
        if(default_option.length > 0){
            $(".defaultSettings").val(default_option.attr("value"));
        } else {
            $(".defaultSettings").val("custom");
        }
    }
});
    $(".renewalappearance").change();
    $(".invoicedate_suggestion").change();
    $(".duedate").change();

</script>
<style>
.popupform input.popupforminput.checkbox {
    width: auto;
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
