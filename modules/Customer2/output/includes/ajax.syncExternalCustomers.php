<?php
// Ownercompany accountconfig
$o_query = $o_main->db->get('ownercompany_accountconfig');
$ownercompany_accountconfig = $o_query ? $o_query->row_array() : array();

// Load integration
$integration = $ownercompany_accountconfig['global_integration'];
$integration_file = __DIR__ . '/../../../'. $integration .'/internal_api/load.php';
if (file_exists($integration_file)) {
    require_once $integration_file;
    if (class_exists($integration)) {
        if ($api) unset($api);
        $api = new $integration(array(
            'o_main' => $o_main
        ));
    }
}
else {
    echo 'No integration found';
    return;
}

// Get customer list for sync
$sql = "SELECT c.*, 
cei.id external_local_entry_id, 
cei.external_id external_id, 
cei.external_sys_id external_sys_id,
cei.synced_with_external_system synced_with_external_system
FROM customer c
LEFT JOIN customer_externalsystem_id cei ON cei.customer_id = c.id
WHERE cei.ownercompany_id = 0";
$o_query = $o_main->db->query($sql);

$all_customer_list = $o_query && $o_query->num_rows() ? $o_query->result_array() : array();
$unsynced_customer_list = array();

foreach ($all_customer_list as $customer) {
    if (!$customer['synced_with_external_system']) {
        $unsynced_customer_list[] = $customer;
    }
}

$all_customer_count = count($all_customer_list);
$unsynced_customer_count = count($unsynced_customer_list);

// On form submit
if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {

        $customer_csv = "_ID;Code;Description;Subledger Id;Group;Company No;Company;Company Category;Phone;E-Mail;HTML E-mail;Phone Type;Invoice Delivery Method;Invoice Layout;Report Setup;Language;Culture Info;AP Tax Reporting;Price Group;Pricelist;Collection Code;Resale Number;Credit Limit;Pay Terms;Payment Delays;Pay Method;Pay Doc Code;Pay Doc Text;Payment Charge;Bank;Bank Account;Pay Currency;Currency;Currency fixed;Separate Payment;Split Order;Auto Pay;Ext. Account No;Invoice No;Invoice No Fixed;Ext. Identifier;Ext. Identifier Fixed;Account Name;Account Name Fixed;SL Payment Receiver;Ext. Order Ref;Ext. Order Ref Fixed;Tax No;Ext. GL;Ext. GL Fixed;Sales Contact;Our Ref;Your Reference;Contract;Invoice Header;Invoice Footer;Shipping terms;Shipping Method;Service Type;Account;Account Fixed;SL Account;Sl Account Fixed;GL Object Value 1;GL Object Value 2;XGL;Tax Rule;Tax Rule Fixed;Text;Text Fixed;Notes;Date From;Date To;Street Address;Zip Code;City;State;Country;Street Address 2;Zip Code 2;City 2;State 2;Country 2;Payment Notification;Dummy 1;Dummy 2;Dummy 3;Dummy 4;Dummy 5;End of Line\n";

        $customers_to_sync = $_POST['syncMethod'] === 'sync_all' ? $all_customer_list : $unsynced_customer_list;

        foreach ($customers_to_sync as $customer) {
            if (!$customer['synced_with_external_system']) {
                $o_main->db->where('id', $customer['external_local_entry_id']);
                $o_main->db->update('customer_externalsystem_id', array(
                    'external_sys_id' => $customer['external_id'],
                    'synced_with_external_system' => 1,
                    'sync_time' => date('Y-m-d H:i:s')
                ));
                $customer['external_sys_id'] = $customer['external_id'];
            } else {
                $o_main->db->where('id', $customer['external_local_entry_id']);
                $o_main->db->update('customer_externalsystem_id', array(
                    'sync_time' => date('Y-m-d H:i:s')
                ));
            }

            // Prepare csv
            $customer_external_id = $customer['external_sys_id'];
            $customer_name = $customer['name'];
            $customer_phone = $customer['phone'];
            $customer_reg_nr = $customer['publicRegisterId'];
            $customer_street_address = $customer['paStreet'];
            $customer_zip_code = $customer['paPostalNumber'];
            $customer_city = $customer['paCity'];
            $customer_country = $customer['paCountry'];

            $customer_csv_line = ";$customer_external_id;$customer_name;;;$customer_reg_nr;$customer_name;;$customer_phone;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;$customer_street_address;$customer_zip_code;$customer_city;$customer_country;$customer_country;;;;;;;;;;;;x\n";
            $customer_csv .= $customer_csv_line;
        }

        $file_name = 'getynet-customer-sync.csv';
        $api->upload_file_str('AR02', $_POST['ownerCode'], $file_name, $customer_csv);

        $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list";
	}
}
?>

<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=syncExternalCustomers";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<div class="inner">
            <div class="line">
                <div class="lineTitle"><?php echo $formText_NewUnsyncedCustomerCount_output; ?></div>
                <div class="lineInput">
                    <?php echo $unsynced_customer_count; ?>
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_AllCustomerCount_output; ?></div>
                <div class="lineInput">
                    <?php echo $all_customer_count; ?>
                </div>
                <div class="clear"></div>
            </div>

            <div class="line">
                <div class="lineTitle"><?php echo $formText_Sync_output; ?></div>
                <div class="lineInput">
                    <select name="syncMethod">
                        <option value="sync_new">
                            <?php echo $formText_OnlyNewUnsyncedCustomers_output; ?> (<?php echo $unsynced_customer_count; ?>)
                        </option>
                        <option value="sync_all">
                            <?php echo $formText_AllCustomers_output; ?> (<?php echo $all_customer_count; ?>) (<?php echo $formText_OverwritesPreviouslySynced_output; ?>)
                        </option>
                    </select>
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_ChooseGlobalEntity_output; ?></div>
                <div class="lineInput">
                    <select name="ownerCode">
                        <?php foreach ($api->get_entities() as $entity): ?>
                            <option value="<?php echo $entity['ownerCode']; ?>">
                                (<?php echo $entity['ownerCode']; ?>) <?php echo $entity['companyName']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="clear"></div>
            </div>
		</div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_SyncNow_Output; ?>">
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
.popupform input.popupforminput.checkbox {
    width: auto;
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
