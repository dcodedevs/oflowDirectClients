<?php
$transaction_id = $_POST['transaction_id'] ? $_POST['transaction_id'] : 0;
$action = $_POST['action'] ? $_POST['action'] : '';
$status = $_POST['status'] ? $_POST['status'] : 0;

$sql = "SELECT * FROM creditor_transactions WHERE id = '".$o_main->db->escape_str($transaction_id)."'";
$o_query = $o_main->db->query($sql);
$transaction = $o_query ? $o_query->row_array() : array();
if($transaction){
    $sql = "SELECT * FROM creditor WHERE id= '".$o_main->db->escape_str($transaction['creditor_id'])."'";
    $o_query = $o_main->db->query($sql);
    $creditorData = $o_query ? $o_query->row_array() : array();

    require_once __DIR__ . '/../../../'.$creditorData['integration_module'].'/internal_api/load.php';
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
        $data['changedAfter'] = date("Y-m-d", strtotime("01.01.2000"));
        $data['ShowOpenEntries'] = 0;

        $transactionData = array();
        $transactionData['DateSearchParameters'] = 'DateChangedUTC';
        $transactionData['date_start'] = $data['changedAfter'];
        $transactionData['date_end'] =date('Y-m-t', time() + 60*60*24);
        $transactionData['TransactionNoStart'] = $transaction['transaction_nr'];
        $transactionData['TransactionNoEnd'] = $transaction['transaction_nr'];
        $invoicesTransactions = $api->get_transactions($transactionData);
        $external_transaction = array();
        foreach($invoicesTransactions as $invoicesTransaction){
            if($invoicesTransaction['id'] == $transaction['transaction_id']){
                $external_transaction = $invoicesTransaction;
            }
        }
        ?>
        <?php

    }
}
?>

<div class="popupform popupform-<?php echo $creditor_id;?>">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=show_transaction_info";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
        <div class="inner">
            <b><?php echo $formText_InternalTransaction_output;?></b>
            <table class="table">
                <tr>
                    <td>
                        <?php echo $formText_Id_output;?>
                    </td>
                    <td>
                    <?php echo $transaction['id'];?>
                    </td>
                </tr><tr>
                    <td>
                        <?php echo $formText_Date_output;?>
                    </td>
                    <td>
                    <?php echo date("d.m.Y H:i:s", strtotime($transaction['date']));?>
                    </td>
                </tr><tr><td>
                        <?php echo $formText_DueDate_output;?>
                    </td>
                    <td>
                    <?php echo date("d.m.Y H:i:s", strtotime($transaction['due_date']));?>
                    </td>
                </tr><tr><td>
                        <?php echo $formText_LinkId_output;?>
                    </td>
                    <td>
                    <?php echo $transaction['link_id'];?>
                    </td>
                </tr><tr><td>
                        <?php echo $formText_InvoiceNr_output;?>
                    </td>
                    <td>
                    <?php echo $transaction['invoice_nr'];?>
                    </td>
                </tr><tr><td>
                        <?php echo $formText_Amount_output;?>
                    </td>
                    <td>
                    <?php echo $transaction['amount'];?>
                    </td>
                </tr><tr><td>
                        <?php echo $formText_KidNumber_output;?>
                    </td>
                    <td>
                    <?php echo $transaction['kid_number'];?>
                    </td>
                </tr><tr><td>
                        <?php echo $formText_Type_output;?>
                    </td>
                    <td>
                    <?php echo $transaction['system_type'];?>
                    </td>
                </tr><tr><td>
                        <?php echo $formText_Open_output;?>
                    </td>
                    <td>
                    <?php echo $transaction['open'];?>
                    </td>
                </tr>
            </table>    
            <br/>  
            <br/>
            <b><?php echo $formText_ExternalTransaction_output;?></b>
            <?php if($external_transaction ){?>
            <table class="table">                
                <tr>
                    <td>
                        <?php echo $formText_ExternalId_output;?>
                    </td>
                    <td>
                    <?php echo $external_transaction['id'];?>
                    </td>
                </tr><tr>
                    <td>
                        <?php echo $formText_Date_output;?>
                    </td>
                    <td>
                    <?php echo date("d.m.Y H:i:s", strtotime($external_transaction['date']));?>
                    </td>
                </tr><tr><td>
                        <?php echo $formText_DueDate_output;?>
                    </td>
                    <td>
                    <?php echo date("d.m.Y H:i:s", strtotime($external_transaction['dueDate']));?>
                    </td>
                </tr><tr><td>
                        <?php echo $formText_DateChanged_output;?>
                    </td>
                    <td>
                    <?php echo date("d.m.Y H:i:s", strtotime($external_transaction['dateChanged']));?>
                    </td>
                </tr><tr><td>
                        <?php echo $formText_LinkId_output;?>
                    </td>
                    <td>
                    <?php echo $external_transaction['linkId'];?>
                    </td>
                </tr><tr><td>
                        <?php echo $formText_InvoiceNr_output;?>
                    </td>
                    <td>
                    <?php echo $external_transaction['invoiceNr'];?>
                    </td>
                </tr><tr><td>
                        <?php echo $formText_Amount_output;?>
                    </td>
                    <td>
                    <?php echo $external_transaction['amount'];?>
                    </td>
                </tr><tr><td>
                        <?php echo $formText_KidNumber_output;?>
                    </td>
                    <td>
                    <?php echo $external_transaction['kidNumber'];?>
                    </td>
                </tr><tr><td>
                        <?php echo $formText_Type_output;?>
                    </td>
                    <td>
                    <?php echo $external_transaction['systemType'];?>
                    </td>
                </tr><tr><td>
                        <?php echo $formText_Open_output;?>
                    </td>
                    <td>
                    <?php echo $external_transaction['open'];?>
                    </td>
                </tr>
            </table>  
            <?php } else {
                echo $formText_NotFound_output;
                }?>  
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
