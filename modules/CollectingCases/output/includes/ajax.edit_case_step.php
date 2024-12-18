<?php
$caseId = $_POST['caseId'] ? $o_main->db->escape_str($_POST['caseId']) : 0;
$action = $_POST['action'] ? $_POST['action'] : '';
$status = $_POST['status'] ? $_POST['status'] : 0;

$sql = "SELECT * FROM collecting_cases WHERE id = $caseId";
$o_query = $o_main->db->query($sql);
$projectData = $o_query ? $o_query->row_array() : array();

if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
        if ($caseId) {
	        $s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_steps.id = ? ORDER BY sortnr ASC";
	        $o_query = $o_main->db->query($s_sql, array($_POST['collecting_cases_process_step_id']));
	        $step = ($o_query ? $o_query->row_array() : array());
			$sub_status = $step['sub_status'];
            $sql = "UPDATE collecting_cases SET
            updated = now(),
            updatedBy='".$variables->loggID."',
			status = '".$o_main->db->escape_str($_POST['status'])."',
			sub_status = '".$o_main->db->escape_str($sub_status)."',
			collecting_cases_process_step_id = '".$o_main->db->escape_str($_POST['collecting_cases_process_step_id'])."',
			reminder_process_id = '".$o_main->db->escape_str($_POST['reminder_process_id'])."'
            WHERE id = $caseId";
			$o_query = $o_main->db->query($sql);
			$fw_redirect_url = $_POST['redirect_url'];


			$s_sql = "SELECT * FROM debtcollectionlatefee WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($step['claim_type_2_article']));
			$articleForType2 = $o_query ? $o_query->row_array() : array();

			$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
			$o_query = $o_main->db->query($s_sql, array($projectData['creditor_id']));
			$creditor = ($o_query ? $o_query->row_array() : array());

			$s_sql = "SELECT * FROM customer WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($creditor['customer_id']));
			$creditorCustomer = $o_query ? $o_query->row_array() : array();

			$debtCollectionTableName = "";
			$claimTypeForType3 = "";
			$claimTypeForType2 = "";
			if($creditorCustomer) {
				if(intval($creditorCustomer['customer_type_collect']) == 0) {
					if($creditor['vat_deduction']){
						$debtCollectionTableName = "debtcollectionfeecompanycreditorwithvatdeduct";
						$claimTypeForType3 = '6';
					} else {
						$debtCollectionTableName = "debtcollectionfeecompanycreditorwithoutvatdeduct";
						$claimTypeForType3 = '7';
					}
				} else if($creditorCustomer['customer_type_collect'] == 1) {
					if($creditor['vat_deduction']){
						$debtCollectionTableName = "debtcollectionfeepersoncreditorwithvatdeduct";
						$claimTypeForType3 = '4';
					} else {
						$debtCollectionTableName = "debtcollectionfeepersoncreditorwithoutvatdeduct";
						$claimTypeForType3 = '5';
					}
				}
			}
			$articleForType3 = array();
			if($debtCollectionTableName != ""){
				if($step['claim_type_3_article'] > 0) {
					$s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND collectingcase_id = ? ORDER BY created DESC";
					$o_query = $o_main->db->query($s_sql, array($projectData['id']));
					$invoice = ($o_query ? $o_query->row_array() : array());

					$baseAmount = $invoice['collecting_case_original_claim'];

					$sql = "SELECT creditor_invoice_payment.* FROM creditor_invoice_payment
					WHERE creditor_invoice_payment.invoice_number = ? AND creditor_invoice_payment.creditor_id = ? AND (before_or_after_case = 1)";
					$o_query = $o_main->db->query($sql, array($invoice['invoice_nr'], $invoice['creditor_id']));
					$payments = $o_query ? $o_query->result_array() : array();
					foreach($payments as $payment) {
						$baseAmount += $payment['amount'];
					}

					$s_sql = "SELECT * FROM ".$debtCollectionTableName." WHERE amountFrom < ? ORDER BY amountFrom DESC";
					$o_query = $o_main->db->query($s_sql, array($baseAmount));
					$articleForType3 = $o_query ? $o_query->row_array() : array();
				}
			}

			$s_sql = "DELETE FROM collecting_cases_claim_lines WHERE claim_type = 2 AND collecting_case_id = ?";
			$o_query = $o_main->db->query($s_sql, array($projectData['id']));
			if(!$projectData['doNotAddLateFee']) {
				if(intval($step['claim_type_2_article']) > 0) {
					if($articleForType2) {
						$s_sql = "INSERT INTO collecting_cases_claim_lines SET created=NOW(), createdBy='claim line process', name=?, amount = ?, collecting_case_id= ?, claim_type = 2";
						$o_query = $o_main->db->query($s_sql, array($articleForType2['article_name'], $articleForType2['amount'], $projectData['id']));
					}
				}
			}
			$s_sql = "DELETE FROM collecting_cases_claim_lines WHERE (claim_type = 4 || claim_type = 5 || claim_type = 6 || claim_type = 7) AND collecting_case_id = ?";
			$o_query = $o_main->db->query($s_sql, array($projectData['id']));
			if(!$projectData['doNotAddDebtCollectionFee']) {

				if(intval($step['claim_type_3_article']) > 0) {
					if($articleForType3) {
						if($step['claim_type_3_article'] == 1) {
							$amount = $articleForType3['lightFee'];
						} else {
							$amount = $articleForType3['heavyFee'];
						}
						$s_sql = "INSERT INTO collecting_cases_claim_lines SET  created=NOW(), createdBy='claim line process', name=?, amount = ?,collecting_case_id =?, claim_type = ?";
						$o_query = $o_main->db->query($s_sql, array($articleForType3['articleText'], $amount, $projectData['id'], $claimTypeForType3));
					}
				}
			}


        } else {

        }
	}
}

$s_sql = "SELECT * FROM collecting_cases_main_status_basisconfig ORDER BY id ASC";
$o_query = $o_main->db->query($s_sql);
$main_statuses = ($o_query ? $o_query->result_array() : array());
$filterDownStatusArray = array(1,3,4);
?>

<div class="popupform popupform-<?php echo $caseId;?>">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_case_step";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="caseId" value="<?php echo $caseId;?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$caseId; ?>">
		<div class="inner">
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Status_Output; ?></div>
				<div class="lineInput">
					<select name="status" autocomplete="off" required class="statusSelect">
						<option value=""><?php echo $formText_Select_output;?></option>
						<?php
						foreach($main_statuses as $main_status) {
							if(in_array($main_status['id'], $filterDownStatusArray)){
							?>
								<option value="<?php echo $main_status['id'];?>" <?php if($main_status['id'] == $projectData['status']) echo 'selected';?>><?php echo $main_status['name'];?></option>
								<?php
							}
						}
						?>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_ReminderProcess_Output; ?></div>
				<div class="lineInput">
					<?php
				    $s_sql = "SELECT * FROM collecting_cases_process ORDER BY sortnr ASC";
				    $o_query = $o_main->db->query($s_sql);
				    $processes = ($o_query ? $o_query->result_array() : array());
					?>
					<select name="reminder_process_id"  class="reminderSelect">
						<option value=""><?php echo $formText_Select_output;?></option>
						<?php foreach($processes as $process) { ?>
							<option value="<?php echo $process['id'];?>" <?php if($process['id'] == $projectData['reminder_process_id']) echo 'selected';?>>
								<?php echo $process['name'];?>
							</option>
						<?php } ?>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_ProcessStep_Output; ?></div>
				<div class="lineInput step_wrapper">

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
	$(".statusSelect").change(function(){
		refresh_steps();
	})
	$(".collectingSelect").change(function(){
		refresh_steps();
	})
	$(".reminderSelect").change(function(){
		refresh_steps();
	})
	refresh_steps();
});


function refresh_steps(){
	var data = {
		status: $(".statusSelect").val(),
		collecting_process: $(".collectingSelect").val(),
		reminder_process: $(".reminderSelect").val(),
		current_step: '<?php echo $projectData['collecting_cases_process_step_id'];?>'
	};
	ajaxCall('get_steps', data, function(json) {
		$(".step_wrapper").html(json.html);
	});
};

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
