<?php
$creditor_id = $_POST['creditor_id'] ? $o_main->db->escape_str($_POST['creditor_id']) : 0;
$action = $_POST['action'] ? $_POST['action'] : '';
$status = $_POST['status'] ? $_POST['status'] : 0;

$sql = "SELECT * FROM creditor WHERE id = $creditor_id";
$o_query = $o_main->db->query($sql);
$creditorData = $o_query ? $o_query->row_array() : array();

$sql = "SELECT * FROM creditor_contact_person WHERE id = $contactperson_id";
$o_query = $o_main->db->query($sql);
$contactperson = $o_query ? $o_query->row_array() : array();

$creditorId = $creditorData['id'];
$doNotTriggerInitialSync = 1;
require_once __DIR__ . '/import_scripts/import_cases2.php';

if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
        require_once __DIR__ . '/../../../'.$creditorData['integration_module'].'/internal_api/load.php';

        $sql = "SELECT * FROM moduledata WHERE name = 'CreditorsOverview'";
        $o_query = $o_main->db->query($sql);
        $moduleInfo = $o_query ? $o_query->row_array() : array();
        $moduleID = $moduleInfo['uniqueID'];

        $v_config = array(
            'ownercompany_id' => 1,
            'identityId' => $creditorData['entity_id'],
            'creditorId' => $creditorData['id'],
            'o_main' => $o_main
        );
        $s_sql = "SELECT * FROM integration24sevenoffice_session WHERE creditor_id = '".$o_main->db->escape_str($creditorData['id'])."' ORDER BY DESC";
        $o_query = $o_main->db->query($s_sql);
        if($o_query && 0 < $o_query->num_rows())
        {
            $v_int_session = $o_query->row_array();
            $v_config['session_id'] = $v_int_session['session_id'];
        }
        $api = new Integration24SevenOffice($v_config);
        if($api->error == "") {         

            $bookaccountEnd = 1529;
            if($creditorData['bookaccount_upper_range'] >= 1500){
                $bookaccountEnd = $creditorData['bookaccount_upper_range'];
            }
            $sql = "SELECT * FROM creditor_transactions WHERE creditor_id = ? AND open = 1 GROUP BY external_customer_id LIMIT 5000";
            $o_query = $o_main->db->query($sql, array($creditorData['id']));
            $creditor_transactions = $o_query ? $o_query->result_array() : array();
            $total_updated_count = 0;
            foreach($creditor_transactions as $creditor_transaction){
                $data['changedAfter'] = date("Y-m-d", strtotime("01.01.2000"));
                $transactionData = array();
                $transactionData['DateSearchParameters'] = 'EntryDate';
                $transactionData['date_start'] = $data['changedAfter'];
                $transactionData['date_end'] =date('Y-m-t', time() + 60*60*24);
                $transactionData['CustomerId'] = $creditor_transaction['external_customer_id'];
                $transactionData['bookaccountStart'] = 1500;
                $transactionData['bookaccountEnd'] = $bookaccountEnd;
                $transactionData['ShowOpenEntries'] = 1;
                $invoicesTransactions = $api->get_transactions($transactionData); 
                list($totalImportedSuccessfully, $lastImportedDate, $cases_to_check, $totalSum) = sync_transactions($invoicesTransactions, $creditorData, $moduleID, $api, true);
                $total_updated_count+= $totalImportedSuccessfully;
            }
        }
	}
}
?>

<div class="popupform popupform-<?php echo $creditor_id;?>">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=update_all_transactions";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="creditor_id" value="<?php echo $creditor_id;?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$creditor_id; ?>">
		<div class="inner">
            <?php if(!isset($_POST['output_form_submit'])) { ?>
                <div class="line">
                    <?php echo $formText_ThisWillSyncAllOpenTransasctionsInSystem_output; ?>
                    <div class="clear"></div>
                </div>
            <?php } else {                
                echo $total_updated_count." ".$formText_TransactionsUpdated_output;
            } ?>
		</div>

		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
            <?php if(!isset($_POST['output_form_submit'])) { ?>
			    <input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
            <?php } ?>
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
                    $('#popupeditboxcontent').html('');
                    $('#popupeditboxcontent').html(data.html);
                    $(window).resize();
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
