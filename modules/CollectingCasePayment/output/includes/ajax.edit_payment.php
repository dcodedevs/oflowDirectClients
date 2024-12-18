<?php
$b_edit = FALSE;
if(isset($_POST['id']) && 0 < $_POST['id'])
{
	$s_sql = "SELECT * FROM collecting_cases_payments WHERE id = '".$o_main->db->escape_str($_POST['id'])."'";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0)
	{
		$paymentTest = $o_query->row_array();
		$b_edit = TRUE;
	}
}

// Save
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		$o_query = $o_main->db->query("SELECT id FROM collecting_cases_payments WHERE id = '".$o_main->db->escape_str($_POST['id'])."' AND (settlement_id = 0 OR settlement_id is null)");
		if(($o_query && $o_query->num_rows()==1) || $_POST['id'] == 0)
		{
			if(isset($_POST['action']) && 'delete_payment' == $_POST['action'])
			{

				$o_main->db->query("DELETE ctq FROM collecting_cases_payment_coverlines ctq JOIN collecting_cases_payments ct ON ct.id = ctq.collecting_cases_payment_id WHERE ct.id = '".$o_main->db->escape_str($_POST['id'])."'");
				$o_main->db->query("DELETE ct FROM collecting_cases_payments ct WHERE ct.id = '".$o_main->db->escape_str($_POST['id'])."'");

				return;
			}

	        $amount = str_replace(",",".", $_POST['amount']);
			if('' == fix_string($_POST['date']))
			{
				$fw_error_msg['error_'.count($fw_error_msg)] = $formText_DateIsMissing_Output;
			}
			if('' == fix_string($_POST['collecting_case_id']))
			{
				$fw_error_msg['error_'.count($fw_error_msg)] = $formText_CollectingCaseIsMissing_Output;
			}
	        if('' == fix_string($amount) || intval($amount) == 0) {
				$fw_error_msg['error_'.count($fw_error_msg)] = $formText_AmountIsMissing_Output;
	        }

	        include_once("fnc_calculate_coverlines.php");

			$s_sql = "SELECT * FROM collecting_company_cases WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($_POST['collecting_case_id']));
			$collectingCase = $o_query ? $o_query->row_array() : array();

			$s_sql = "SELECT * FROM creditor WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($collectingCase['creditor_id']));
			$creditor = $o_query ? $o_query->row_array() : array();

			if($collectingCase['status'] == 7){
				$s_sql = "SELECT * FROM covering_order_and_split WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($creditor['warning_covering_order_and_split_id']));
				$coveringOrderAndSplit = $o_query ? $o_query->row_array() : array();
			} else {
				$s_sql = "SELECT * FROM covering_order_and_split WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($creditor['covering_order_and_split_id']));
				$coveringOrderAndSplit = $o_query ? $o_query->row_array() : array();
			}

	        $insertInfo = calculate_coverlines($coveringOrderAndSplit, $paymentTest['id'], $collectingCase, $amount);
			if(!$insertInfo) {
				$fw_error_msg['error_'.count($fw_error_msg)] = $formText_ConfigurationErrorNumbersNotMatching_output;
			}
			if(0 == count($fw_error_msg))
			{
	            $date = date("Y-m-d", strtotime($_POST['date']));
				if(isset($paymentTest['id']) && 0 < $paymentTest['id'])
				{
					$s_sql = "UPDATE collecting_cases_payments SET updated = NOW(), updatedBy = '".$o_main->db->escape_str($variables->loggID)."',
	                date = '".$o_main->db->escape_str($date)."',
					collecting_case_id = '".$o_main->db->escape_str($_POST['collecting_case_id'])."',
					amount = '".$o_main->db->escape_str($amount)."'
					WHERE id = '".$o_main->db->escape_str($paymentTest['id'])."'";
				} else {
					$s_sql = "SELECT MAX(sortnr) sortnr FROM course_test";
					$o_query = $o_main->db->query($s_sql);
					$maxSort = ($o_query ? $o_query->row_array() : array());
					$sortnr = intval($maxSort['sortnr']) + 1;

					$s_sql = "INSERT INTO collecting_cases_payments SET moduleID = '".$o_main->db->escape_str($moduleID)."', created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."',
					sortnr = '".$o_main->db->escape_str($sortnr)."',
					date = '".$o_main->db->escape_str($date)."',
					collecting_case_id = '".$o_main->db->escape_str($_POST['collecting_case_id'])."',
					amount = '".$o_main->db->escape_str($amount)."'";
				}
				$o_query = $o_main->db->query($s_sql);
				if(!$o_query)
				{
					$fw_error_msg['error_'.count($fw_error_msg)] = $formText_ErrorOccurredHandlingRequest_Output;
				} else {

					$collectingCaseId = $_POST['collecting_case_id'];
					$amount = str_replace(",", ".",$_POST['amount']);
					if($paymentTest['id'] == 0){
						$paymentId = $o_main->db->insert_id();
					} else {
						$paymentId = $paymentTest['id'];
					}

					if($collectingCaseId > 0) {
						$sql = "SELECT * FROM collecting_cases_payment_plan WHERE collecting_case_id = '".$o_main->db->escape_str($collectingCaseId)."' AND (status = 0 OR status is null)";
						$o_query = $o_main->db->query($sql);
						$collecting_cases_payment_plan = $o_query ? $o_query->row_array() : array();
						if($collecting_cases_payment_plan) {
							$s_sql = "SELECT * FROM collecting_cases_payment_plan_lines WHERE collecting_cases_payment_plan_id = '".$o_main->db->escape_str($collecting_cases_payment_plan['id'])."' AND (status = 0 OR status is null)
							ORDER BY due_date ASC";
							$o_query = $o_main->db->query($s_sql);
							$collecting_cases_payment_plan_line = $o_query ? $o_query->row_array() : array();
							if($collecting_cases_payment_plan_line) {
								$totalPayment = $collecting_cases_payment_plan_line['amount'] + $amount;
								$s_sql = "UPDATE collecting_cases_payment_plan_lines SET updated = NOW(), payed = '".$o_main->db->escape_str($totalPayment)."' WHERE id = '".$o_main->db->escape_str($collecting_cases_payment_plan_line['id'])."'";
								$o_query = $o_main->db->query($s_sql);
								if($o_query){
									$s_sql = "UPDATE collecting_cases_payments SET collecting_cases_payment_plan_line_id = '".$o_main->db->escape_str($collecting_cases_payment_plan_line['id'])."' WHERE id = '".$o_main->db->escape_str($paymentId)."'";
									$o_query = $o_main->db->query($s_sql);
								}
							}

						}
					}
					$sql = "SELECT * FROM collecting_company_cases WHERE id = '".$o_main->db->escape_str($collectingCaseId)."'";
					$o_query = $o_main->db->query($sql);
					$caseData = $o_query ? $o_query->row_array() : array();

					// $s_sql = "SELECT * FROM creditor_invoice WHERE content_status < 2 AND collecting_case_id = ? ORDER BY created DESC";
					// $o_query = $o_main->db->query($s_sql, array($caseData['id']));
					// $invoice = ($o_query ? $o_query->row_array() : array());
					//
					// $interestBearingAmount = $invoice['collecting_case_original_claim'];
					// $s_sql = "SELECT * FROM creditor_invoice_payment  WHERE invoice_number = ?";
					// $o_query = $o_main->db->query($s_sql, array($invoice['invoice_number']));
					// $invoice_payments = ($o_query ? $o_query->result_array() : array());
					//
					// foreach($invoice_payments as $invoice_payment) {
					// 	$interestBearingAmount += $invoice_payment['amount'];
					// }

					$s_sql = "SELECT cccl.* FROM collecting_company_cases_claim_lines cccl
					LEFT OUTER JOIN collecting_cases_claim_line_type_basisconfig bconfig ON bconfig.id = cccl.claim_type
					WHERE cccl.content_status < 2 AND cccl.collecting_company_case_id = ? AND IFNULL(bconfig.not_include_in_claim, 0) = 0
					ORDER BY cccl.claim_type ASC, cccl.created DESC";
					$o_query = $o_main->db->query($s_sql, array($caseData['id']));
					$claims = ($o_query ? $o_query->result_array() : array());

					$s_sql = "SELECT * FROM collecting_cases_payments WHERE collecting_case_id = ? ORDER BY created DESC";
					$o_query = $o_main->db->query($s_sql, array($caseData['id']));
					$payments = ($o_query ? $o_query->result_array() : array());

					$totalUnpaid = $interestBearingAmount;
					foreach($claims as $claim) {
						$totalUnpaid += $claim['amount'];
					}
					foreach($payments as $payment) {
						if($payment['id'] != $paymentId) {
							$totalUnpaid -= $payment['amount'];
						}
					}

					$insertInfo = calculate_coverlines($coveringOrderAndSplit, $paymentTest['id'], $collectingCase, $amount, $b_edit);
					foreach($insertInfo as $collecting_claim_line_type => $insertInfoSingle) {
	                    $collectioncompany_share = $insertInfoSingle[0];
	                    $creditor_share = $insertInfoSingle[1];
	                    $agent_share = $insertInfoSingle[2];
	                    $total_amount = $insertInfoSingle[3];
						$debitor_share = $insertInfoSingle[4];
	                    if($collectioncompany_share > 0 || $creditor_share > 0 || $agent_share > 0){
							$s_sql = "INSERT INTO collecting_cases_payment_coverlines SET moduleID = '".$o_main->db->escape_str($moduleID)."', created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."',
							collecting_cases_payment_id = '".$o_main->db->escape_str($paymentId)."',
							amount = '".$o_main->db->escape_str($total_amount)."',
							collectingcompany_amount = '".$o_main->db->escape_str($collectioncompany_share)."',
							creditor_amount = '".$o_main->db->escape_str($creditor_share)."',
							debitor_amount = '".$o_main->db->escape_str($debitor_share)."',
							agent_amount = '".$o_main->db->escape_str($agent_share)."',
							collecting_claim_line_type = '".$o_main->db->escape_str($collecting_claim_line_type)."'";
							$o_query = $o_main->db->query($s_sql);
						}
						$totalUnpaid -= $total_amount;
					}

					$collectedMainClaim = 0;
                    $collectedInterest = 0;
                    $collectedLegalCost = 0;
                    $collectedVat = 0;

					$sql_update = "";
                    $s_sql = "SELECT * FROM collecting_cases_payments WHERE collecting_case_id = ? ORDER BY created ASC";
                    $o_query = $o_main->db->query($s_sql, array($caseData['id']));
                    $payments = ($o_query ? $o_query->result_array() : array());

                    foreach($payments as $payment) {
                        $s_sql = "SELECT collecting_cases_payment_coverlines.*, clbc.claimline_type_category_id
                        FROM collecting_cases_payment_coverlines
                        LEFT OUTER JOIN collecting_cases_claim_line_type_basisconfig clbc ON clbc.id = collecting_cases_payment_coverlines.collecting_claim_line_type
                        WHERE collecting_cases_payment_coverlines.collecting_cases_payment_id = ?";
                        $o_query = $o_main->db->query($s_sql, array($payment['id']));
                        $paymentCoverlines = $o_query ? $o_query->result_array() : array();
                        foreach($paymentCoverlines as $paymentCoverline){
                            if($paymentCoverline['claimline_type_category_id'] == 1) {
                                $collectedMainClaim += $paymentCoverline['amount'];
                            } else if($paymentCoverline['claimline_type_category_id'] == 4){
                                $collectedInterest += $paymentCoverline['amount'];
                            } else if($paymentCoverline['claimline_type_category_id'] == 5){
                                $collectedLegalCost += $paymentCoverline['amount'];
                            }
                        }
                    }


					$sql_update .= ", current_total_claim = '".$o_main->db->escape_str($totalUnpaid)."'";
					$sql_update .= ", collected_main_claim = '".$o_main->db->escape_str($collectedMainClaim)."'";
					$sql_update .= ", collected_interest = '".$o_main->db->escape_str($collectedInterest)."'";
					$sql_update .= ", collected_legal_cost = '".$o_main->db->escape_str($collectedLegalCost)."'";
					$sql_update .= ", collected_vat = '".$o_main->db->escape_str($collectedVat)."'";

					$sql = "UPDATE collecting_company_cases SET updated = NOW()".$sql_update." WHERE id = ?";
					$o_query = $o_main->db->query($sql, array($caseData['id']));

					$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
					$o_query = $o_main->db->query($s_sql, array($caseData['creditor_id']));
					$creditor = ($o_query ? $o_query->row_array() : array());
					if(($totalUnpaid - $creditor['maximumAmountForgiveTooLittlePayed']) <= 0) {
						$too_little = 0;
						$not_payed_to_debitor = 0;
						if($totalUnpaid > 0){
							$too_little = $totalUnpaid;
						} else {
							$totalUnpaid = $totalUnpaid*(-1);
							if($totalUnpaid < $creditor['minimumAmountToPaybackToDebitor']) {
								$not_payed_to_debitor = $totalUnpaid;
							}
						}
						$stoppedStatus = 2;
						$stoppedSubStatus = 1;
						if($caseData['status'] = 3){
							$stoppedStatus = 4;
							$stoppedSubStatus = 14;
						}


				        $sql = "UPDATE collecting_company_cases SET status = '".$stoppedStatus."', sub_status = '".$stoppedSubStatus."', updated = NOW(), stopped_date = NOW(),
						forgiven_too_little_payed = '".$o_main->db->escape_str($too_little)."', not_payed_too_little_amount = '".$not_payed_to_debitor."'
						WHERE id = ?";
				        $o_query = $o_main->db->query($sql, array($caseData['id']));
					}
				}
			}
			return;
		} else {
			$fw_error_msg['error_'.count($fw_error_msg)] = $formText_PaymentAlreadySettled_Output;
			return;
		}
	}
}
function fix_string(&$value)
{
	if(is_array($value))
	{
	} else {
		$value = is_null($value) ? '' : trim(trim(trim($value), chr(0xC2).chr(0xA0)));
	}

	return $value;
}

$s_sql = "SELECT collecting_company_cases.*, CONCAT_WS(' ', customer.name, customer.middlename, customer.lastname) as debitorName FROM collecting_company_cases
LEFT OUTER JOIN customer ON customer.id = collecting_company_cases.debitor_id
WHERE collecting_company_cases.content_status < 2 AND collecting_company_cases.id = ? ORDER BY collecting_company_cases.id ASC";
$o_query = $o_main->db->query($s_sql, array($paymentTest['collecting_case_id']));
$collectingCase = $o_query ? $o_query->row_array() : array();

?>
<div class="popupform">
	<div class="popupformTitle"><?php echo ($b_edit ? $formText_EditPayment_Output : $formText_AddNewPayment_Output);?></div>
	<form class="output-form2 main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_payment";?>" method="POST">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="id" value="<?php if(isset($_POST['id'])) echo $_POST['id'];?>">
		<input type="hidden" name="chapter_id" value="<?php if(isset($_POST['chapter_id'])) echo $_POST['chapter_id'];?>">
		<input type="hidden" name="course_id" value="<?php if(isset($_POST['course_id'])) echo $_POST['course_id'];?>">
		<div class="inner">

            <div class="line collectingCaseWrapper">
                <div class="lineTitle"><?php echo $formText_CollectingCase_Output; ?></div>
                <div class="lineInput">
                    <?php if($collectingCase) { ?>
					<a href="#" class="selectCollectingCase"><?php echo $collectingCase['id']." ".$collectingCase['debitorName']?></a>
					<?php } else { ?>
					<a href="#" class="selectCollectingCase"><?php echo $formText_SelectCollectingCase_Output;?></a>
					<?php } ?>
					<input type="hidden" name="collecting_case_id" id="collectingCaseId" value="<?php print $collectingCase['id'];?>" required>

                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Date_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace datepicker" name="date" value="<?php if($paymentTest['date'] != "0000-00-00" && $paymentTest['date'] != "") echo date("d.m.Y", strtotime($paymentTest['date'])); ?>" placeholder="" autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Amount_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace amountInput" name="amount" value="<?php echo number_format($paymentTest['amount'], 2, ",", ""); ?>" placeholder="" autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>
            <div class="previewCoverLines">

            </div>
		</div>
		<div id="popup-validate-message"></div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
		</div>
	</form>
</div>
<style>
.type_wrapper {
	margin-bottom: 10px;
}
</style>
<?php
$s_path = 'modules/'.$module.'/output/elementsOutput/jquery.validate/jquery.validate.min.js';
$l_time = filemtime(BASEPATH.$s_item);
?>
<script type="text/javascript" src="<?php echo $variables->account_root_url.$s_path.'?v='.$l_time;?>"></script>
<script type="text/javascript">
function previewCoverLines(){
    var collectingCaseId = $("#collectingCaseId").val();
    var amount = $(".amountInput").val().replace(",", ".");
    var data = {collectingCaseId: collectingCaseId, amount: amount, paymentId: '<?php echo $paymentTest['id']?>'};
	if(amount > 0){
	    ajaxCall('get_preview_coverlines', data, function(obj) {
	        $(".previewCoverLines").html(obj.html);
	    });
	}
}
$(function(){
	previewCoverLines();
    $("form.output-form2").validate({
        submitHandler: function(form) {
            $.ajax({
                url: $(form).attr("action"),
                cache: false,
                type: "POST",
                dataType: "json",
                data: $(form).serialize(),
                success: function (json) {
                    if(json.error !== undefined){
                        var _msg = '';
						$.each(json.error, function(index, value){
							var _type = Array("error");
							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
							_msg = _msg + '<div class="msg-' + _type[0] + '">' + value + '</div>';
						});
						$("#popup-validate-message").html(_msg, true);
						$("#popup-validate-message").show();
                    } else {
                        out_popup.addClass("close-reload");
                        out_popup.close();
                    }
                }
            }).fail(function() {
                $("#popup-validate-message").html("<?php echo $formText_ErrorOccurredHandlingRequest_Output;?>", true);
                $("#popup-validate-message").show();
                $('#popupeditbox').css('height', "auto");
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
                $('#popupeditbox').css('height', "auto");
            } else {
                $("#popup-validate-message").hide();
            }
            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
        },
        errorPlacement: function(error, element) {
            if(element.attr("name") == "collecting_case_id") {
                error.insertAfter(".popupform .selectCollectingCase");
            }
        },
        messages: {
            collecting_case_id: "<?php echo $formText_SelectCollectingCase_output;?>",
        }
    });

    $(".datepicker").datepicker({
        dateFormat: "dd.mm.yy",
		firstDay: 1
    })

    $(".selectCollectingCase").unbind("click").bind("click", function(){
        var data = {};
        ajaxCall('get_collecting_cases', data, function(obj) {
            $('#popupeditboxcontent2').html('');
            $('#popupeditboxcontent2').html(obj.html);
            out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
            $("#popupeditbox2:not(.opened)").remove();
        });
    })
    $("#collectingCaseId").change(function(){
        previewCoverLines();
    })
    $(".amountInput").keyup(function(){
        previewCoverLines();
    })
});

</script>
<style>
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
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
.popupform input.popupforminput.checkbox {
    width: auto;
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
