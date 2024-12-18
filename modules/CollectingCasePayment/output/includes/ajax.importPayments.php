<?php
$page = 1;

$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=importPayments";
if(isset($_POST['previewPayments'])){
    $fileContent = file_get_contents($_FILES['file']['tmp_name']);
    $matchingPayments = array();
    $notMatchingPayments = array();
    foreach(preg_split("/((\r?\n)|(\r\n?))/", $fileContent) as $line){
        $paymentDate = "";
        $kidNumber = "";
        $amount = "";
        if($line[0] == "N" && $line[1] == "Y" && $line[2] == "0" && $line[3] == "9" && $line[6] == "3" && $line[7] == "0" ){
            $paymentDate = trim(substr($line, 15, 21 - 15));
            $realPaymentDate = "20".$paymentDate[4].$paymentDate[5]."-".$paymentDate[2].$paymentDate[3]."-".$paymentDate[0].$paymentDate[1];
            $kidNumber = trim(substr($line, 49, 74 - 49));
            $amount = intval(substr($line, 32, 49 - 32))/100;

        }

        //
        if($kidNumber != "" && $amount != "" && $paymentDate != "") {
            $sql = "SELECT * FROM collecting_company_cases WHERE kid_number = ?";
            $o_query = $o_main->db->query($sql, array($kidNumber));
            $collectingCase = $o_query ? $o_query->row_array() : array();
            if($collectingCase) {
				$fullyPaid = false;

				$s_sql = "SELECT cccl.* FROM collecting_company_cases_claim_lines cccl
				LEFT OUTER JOIN collecting_cases_claim_line_type_basisconfig bconfig ON bconfig.id = cccl.claim_type
				WHERE cccl.content_status < 2 AND cccl.collecting_company_case_id = ? AND IFNULL(bconfig.not_include_in_claim, 0) = 0
				ORDER BY cccl.claim_type ASC, cccl.created DESC";
				$o_query = $o_main->db->query($s_sql, array($collectingCase['id']));
				$claims = ($o_query ? $o_query->result_array() : array());

				$s_sql = "SELECT * FROM collecting_cases_payments WHERE collecting_case_id = ? ORDER BY created DESC";
				$o_query = $o_main->db->query($s_sql, array($collectingCase['id']));
				$payments = ($o_query ? $o_query->result_array() : array());

				$totalUnpaid = $interestBearingAmount;
				foreach($claims as $claim) {
					$totalUnpaid += $claim['amount'];
				}
				foreach($payments as $payment) {
					$totalUnpaid -= $payment['amount'];
				}
				$totalUnpaid -= $amount;
				$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
				$o_query = $o_main->db->query($s_sql, array($collectingCase['creditor_id']));
				$creditor = ($o_query ? $o_query->row_array() : array());

				if(($totalUnpaid - $creditor['maximumAmountForgiveTooLittlePayed']) <= 0) {
					$fullyPaid = true;
				}
                $matchingPayments[] = array($kidNumber, $realPaymentDate,$amount, $collectingCase, $fullyPaid);
            } else {
                $notMatchingPayments[] = array($kidNumber, $realPaymentDate,$amount);
            }
        }
    }
    ?>
    <?php
}
if(isset($_POST['importPayments'])){

    include_once("fnc_calculate_coverlines.php");
    $paymentsImported = 0;
    $ignoringPayments = 0;
    $fileContent = $_POST['fileContent'];
    foreach(preg_split("/((\r?\n)|(\r\n?))/", $fileContent) as $line){
        $paymentDate = "";
        $kidNumber = "";
        $amount = "";
        if($line[0] == "N" && $line[1] == "Y" && $line[2] == "0" && $line[3] == "9" && $line[6] == "3" && $line[7] == "0" ){
            $paymentDate = trim(substr($line, 15, 21 - 15));
            $realPaymentDate = "20".$paymentDate[4].$paymentDate[5]."-".$paymentDate[2].$paymentDate[3]."-".$paymentDate[0].$paymentDate[1];
            $kidNumber = trim(substr($line, 49, 74 - 49));
            $amount = intval(substr($line, 32, 49 - 32))/100;

        }

        //
        if($kidNumber != "" && $amount != "" && $paymentDate != "") {
            $sql = "SELECT * FROM collecting_company_cases WHERE kid_number = ?";
            $o_query = $o_main->db->query($sql, array($kidNumber));
            $collectingCase = $o_query ? $o_query->row_array() : array();
            if($collectingCase) {
                $sql = "INSERT INTO collecting_cases_payments SET kid_number = ?, amount = ?, date = ?, created = NOW(), createdBy = ?, collecting_case_id = ?, payment_type = 1";
                $o_query = $o_main->db->query($sql, array($kidNumber, $amount, $realPaymentDate, $variables->loggID, $collectingCase['id']));
                if($o_query) {
                    $paymentsImported++;

					$paymentId = $o_main->db->insert_id();
					$collectingCaseId = $collectingCase['id'];
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

					$s_sql = "SELECT * FROM creditor WHERE id = ?";
					$o_query = $o_main->db->query($s_sql, array($caseData['creditor_id']));
					$creditor = $o_query ? $o_query->row_array() : array();

					if($caseData['status'] == 7){
						$s_sql = "SELECT * FROM covering_order_and_split WHERE id = ?";
						$o_query = $o_main->db->query($s_sql, array($creditor['warning_covering_order_and_split_id']));
						$coveringOrderAndSplit = $o_query ? $o_query->row_array() : array();
					} else {
						$s_sql = "SELECT * FROM covering_order_and_split WHERE id = ?";
						$o_query = $o_main->db->query($s_sql, array($creditor['covering_order_and_split_id']));
						$coveringOrderAndSplit = $o_query ? $o_query->row_array() : array();
					}

					$s_sql = "SELECT * FROM collecting_company_cases_claim_lines WHERE content_status < 2 AND collecting_company_case_id = ? ORDER BY claim_type ASC, created DESC";
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

					$insertInfo = calculate_coverlines($coveringOrderAndSplit, $paymentId, $collectingCase, $amount, true);

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
				        $sql = "UPDATE collecting_company_cases SET case_closed_date = NOW(), case_closed_reason = 0, updated = NOW() WHERE id = ?";
				        $o_query = $o_main->db->query($sql, array($caseData['id']));
					}
                }
            } else {
                $ignoringPayments++;
            }
        }
    }
    echo $paymentsImported." ".$formText_ImportedPayments_output."</br>";

    echo $ignoringPayments." ".$formText_IgnoredNotMatchingPayments_output;
}
if(isset($_POST['discardPayments'])){

}

?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">

			</div>
		</div>
	</div>
</div>
<div class="popupform">
<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=importPayments";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<input type="hidden" name="cid" value="<?php print $_POST['cid'];?>">
	<input type="hidden" name="caseId" value="<?php print $_POST['caseId'];?>">

	<div class="inner">
        <?php if(!isset($_POST['previewPayments'])){ ?>
            <input type="file" name="file" id="file"/>
        	<div class="popupformbtn"><input type="submit" name="previewPayments" value="<?php echo $formText_PreviewPayments_output; ?>"></div>

        <?php } else { ?>
            <?php echo $formText_MatchingPayments_output;?>
            <ul>
                <?php
                foreach($matchingPayments as $matchingPaymentArray) {
                    $paymentDate = $matchingPaymentArray[1];
                    $kidNumber =$matchingPaymentArray[0];
                    $amount = $matchingPaymentArray[2];
                    $invoice = $matchingPaymentArray[3];
                    $fullyPaid = $matchingPaymentArray[4];
					$fullyPaidText = '';
					if($fullyPaid) {
						$fullyPaidText = $formText_FullyPaid_output;
					}
                    echo '<li>'.$kidNumber.' - '.$paymentDate.' '.number_format($amount, 2, ",", "").' '.$invoice['external_invoice_nr'].''.$fullyPaidText.'</li>';
                }
                ?>
            </ul>
            <?php echo $formText_NotMatchingPayments_output;?>
            <ul>
                <?php
                foreach($notMatchingPayments as $matchingPaymentArray) {
                    $paymentDate = $matchingPaymentArray[1];
                    $kidNumber =$matchingPaymentArray[0];
                    $amount = $matchingPaymentArray[2];
                    echo '<li>'.$kidNumber.' - '.$paymentDate.' '.number_format($amount, 2, ",", "").'</li>';
                }
                ?>
            </ul>
        	<div class="popupformbtn">
                <input type="hidden" name="fileContent" value="<?php echo $fileContent;?>" />
    			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
                <input type="submit" name="importPayments" value="<?php echo $formText_ImportMatchedPayments_output; ?>">
            </div>
        <?php } ?>

	</div>
</form>
</div>
<style>

.popupform .lineTitle {
	font-weight:700;
	margin-bottom: 10px;
}
.popupform textarea.popupforminput {
	border-radius: 4px;
	padding:5px 10px;
	font-size:12px;
	line-height:17px;
}
.project-file {
	margin-bottom: 4px;
}
.project-file .deleteImage {
	float: right;
}
</style>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$(function() {
	$("form.output-form").validate({
		submitHandler: function(form) {
			fw_loading_start();
			var formdata = $(form).serializeArray();
			var data = {};
            var form_data = new FormData();

            <?php if(!isset($_POST['previewPayments'])){ ?>
                var file_data = $('#file').prop('files')[0];
                form_data.append('file', file_data);


            <?php } ?>

            $(formdata).each(function(index, obj){
                form_data.append(obj.name, obj.value);
            });

			$.ajax({
				url: $(form).attr("action"),
				cache: false,
				type: "POST",
				dataType: "json",
                contentType: false,
                processData: false,
				data: form_data,
				success: function (data) {
					fw_loading_end();
                    $("#popup-validate-message").html("");

                    $('#popupeditboxcontent').html('');
                    $('#popupeditboxcontent').html(data.html);
    				$('#popupeditbox').css('height', "auto");
                    out_popup = $('#popupeditbox').bPopup(out_popup_options);
                    $("#popupeditbox:not(.opened)").remove();
					out_popup.addClass("close-reload");
                    $(window).resize();
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
