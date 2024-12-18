<?php
$payment_id = isset($_POST['payment_id']) ? $_POST['payment_id'] : 0;

$s_sql = "SELECT * FROM collecting_cases_payments WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($payment_id));
$payment = ($o_query ? $o_query->row_array() : array());
if($payment){
	if(intval($payment['settlement_id']) == 0){
		$s_sql = "SELECT * FROM collecting_cases_payment_coverlines WHERE collecting_cases_payment_id = ?";
		$o_query = $o_main->db->query($s_sql, array($payment['id']));
		$paymentCoverlines = $o_query ? $o_query->result_array() : array();
		if($_POST['output_form_submit']){
			$totalMainAmount = 0;
			$totalOldAmount = 0;
			// foreach($paymentCoverlines as $paymentCoverline) {
			// 	$company_amount = str_replace(" ", "",str_replace(",", ".",$_POST['company_amount'][$paymentCoverline['id']]));
			// 	$creditor_amount = str_replace(" ", "",str_replace(",", ".",$_POST['creditor_amount'][$paymentCoverline['id']]));
			// 	$debitor_amount = str_replace(" ", "",str_replace(",", ".",$_POST['debitor_amount'][$paymentCoverline['id']]));
			// 	$total_amount = $company_amount+$creditor_amount+$debitor_amount;
			// 	$totalMainAmount += $total_amount;
			// }
			foreach($_POST['company_amount'] as $coverlineId=>$company_amount) {
				$company_amount = str_replace(" ", "",str_replace(",", ".",$company_amount));
				$creditor_amount = str_replace(" ", "",str_replace(",", ".",$_POST['creditor_amount'][$coverlineId]));
				$debitor_amount = str_replace(" ", "",str_replace(",", ".",$_POST['debitor_amount'][$coverlineId]));
				$total_amount = $company_amount+$creditor_amount+$debitor_amount;
				$totalMainAmount += $total_amount;
			}
			if($totalMainAmount == $payment['amount']) {
				$coverlineIds = array();
				foreach($_POST['company_amount'] as $coverlineId=>$company_amount) {
					$company_amount = str_replace(" ", "",str_replace(",", ".",$company_amount));
					$creditor_amount = str_replace(" ", "",str_replace(",", ".",$_POST['creditor_amount'][$coverlineId]));
					$debitor_amount = str_replace(" ", "",str_replace(",", ".",$_POST['debitor_amount'][$coverlineId]));
					$total_amount = $company_amount+$creditor_amount+$debitor_amount;
					$collecting_claim_line_type = $_POST['collecting_claim_line_type'][$coverlineId];
					if($coverlineId > 0) {
						$s_sql = "UPDATE collecting_cases_payment_coverlines SET updated = NOW(), updatedBy = ?, collectingcompany_amount = ?, creditor_amount = ?, debitor_amount = ?, amount = ?, collecting_claim_line_type = ? WHERE id = ?";
						$o_query = $o_main->db->query($s_sql, array($variables->loggID, $company_amount, $creditor_amount, $debitor_amount, $total_amount, $collecting_claim_line_type, $coverlineId));
					} else {
						$s_sql = "INSERT INTO collecting_cases_payment_coverlines SET created = NOW(), createdBy = ?, collecting_cases_payment_id = ?, collectingcompany_amount = ?, creditor_amount = ?, debitor_amount = ?, amount = ?, collecting_claim_line_type = ?";
						$o_query = $o_main->db->query($s_sql, array($variables->loggID, $payment['id'], $company_amount, $creditor_amount, $debitor_amount, $total_amount, $collecting_claim_line_type));
						if($o_query) {
							$coverlineId = $o_main->db->insert_id();
						}
					}
					$coverlineIds[] = $coverlineId;
				}
				if(count($coverlineIds) > 0) {
					foreach($paymentCoverlines as $paymentCoverline) {
						if(!in_array($paymentCoverline['id'], $coverlineIds)) {
							$s_sql = "DELETE FROM collecting_cases_payment_coverlines WHERE id = ? AND collecting_claim_line_type <> 17";
							$o_query = $o_main->db->query($s_sql, array($paymentCoverline['id']));
						}
					}
				}
				$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$payment['collecting_case_id'];

			} else {
				$fw_error_msg[] = $formText_TotalSumShouldMatchPayment_output;
			}
			return;
		}
		?>
		<div class="popupform popupform-<?php echo $creditor_id;?>">
			<div id="popup-validate-message" style="display:none;"></div>
			<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_payment_coverlines";?>" method="post">
				<input type="hidden" name="fwajax" value="1">
				<input type="hidden" name="fw_nocss" value="1">
				<input type="hidden" name="output_form_submit" value="1">
				<input type="hidden" name="payment_id" value="<?php echo $payment_id;?>">
		        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$case_id; ?>">
				<div class="inner">
					<table class="table fixedTable coverlineTable">
						<tr>
							<th><?php echo $formText_Type_output;?></th>
							<th><?php echo $formText_CollectingCompanyShare_output;?></th>
							<th><?php echo $formText_CreditorShare_output;?></th>
							<th><?php echo $formText_DebitorShare_output;?></th>
							<th class="rightAligned"><?php echo $formText_Total_output;?></th>
							<th></th>
						</tr>
						<?php

						$s_sql = "SELECT * FROM collecting_cases_claim_line_type_basisconfig ORDER BY sortnr";
						$o_query = $o_main->db->query($s_sql);
						$claim_line_types = $o_query ? $o_query->result_array() : array();

						$debitor_share = 0;
						foreach( $paymentCoverlines as $paymentCoverline) {
							$collectioncompany_share = $paymentCoverline['collectingcompany_amount'];
							$creditor_share = $paymentCoverline['creditor_amount'];
							$total_amount = $paymentCoverline['amount'];
							$debitor_share += $paymentCoverline['debitor_amount'];
							 ?>

							 <?php if($paymentCoverline['collecting_claim_line_type'] != "17"){?>
								 <tr>
									 <td>
										 <select name="collecting_claim_line_type[<?php echo $paymentCoverline['id'];?>]" class="claimtype_select" autocomplete="off" required>
											 <?php
											 foreach($claim_line_types as $claim_line_type) {
												 ?>
												 <option value="<?php echo $claim_line_type['id'];?>" <?php if($claim_line_type['id'] == $paymentCoverline['collecting_claim_line_type']) echo 'selected';?>> <?php echo $claim_line_type['type_name'];?></option>
												 <?php
											 }
											 ?>
										 </select>
									 </td>
									 <td><input class="company_amount" type="text" autocomplete="off" name="company_amount[<?php echo $paymentCoverline['id'];?>]" value="<?php echo number_format($collectioncompany_share, 2, ",", ""); ?>" /></td>
									 <td><input class="creditor_amount" type="text" autocomplete="off" name="creditor_amount[<?php echo $paymentCoverline['id'];?>]" value="<?php echo number_format($creditor_share, 2, ",", ""); ?>" /></td>
									 <td><input class="debitor_amount" type="text" autocomplete="off" name="debitor_amount[<?php echo $paymentCoverline['id'];?>]" value="<?php echo number_format(0, 2, ",", ""); ?>" /></td>
									 <td class="rightAligned"><span class="total_amount"><?php echo number_format($total_amount, 2, ",", ""); ?></span></td>
									 <td>
										 <span class="glyphicon glyphicon-trash deleteCoverLine"></span>

									 </td>
								 </tr>
							 <?php } else { ?>
								 <tr>
									<td>
										<?php
										foreach($claim_line_types as $claim_line_type) {
											if($claim_line_type['id'] == $paymentCoverline['collecting_claim_line_type']){
												 echo $claim_line_type['type_name'];
											}
										}
										?>
									</td>
									<td><?php echo number_format($collectioncompany_share, 2, ",", ""); ?></td>
									<td><?php echo number_format($creditor_share, 2, ",", ""); ?></td>
									<td><?php echo number_format(0, 2, ",", ""); ?></td>
									<td class="rightAligned"><span class="total_amount"><?php echo number_format($total_amount, 2, ",", ""); ?></span></td>
									<td>


									</td>
								</tr>
							 <?php } ?>
						<?php }
						if($debitor_share > 0) {
							?>
							<tr>
								<td><?php echo $formText_CreditorPayedTooMuch;?></td>
								<td><?php echo number_format(0, 2, ",", ""); ?></td>
								<td><?php echo number_format(0, 2, ",", ""); ?></td>
								<td><?php echo number_format($debitor_share, 2, ",", ""); ?></td>
								<td><?php echo number_format($debitor_share, 2, ",", ""); ?></td>
							</tr>
							<?php
						}
						?>
					</table>
					<div class="add_coverline"><?php echo $formText_AddCoverline_output;?></div>
					<div class="total_payed_amount"><?php echo $formText_TotalPayedAmount_output." <span>". number_format($payment['amount'], 2, ",", "");?></span></div>
					<div class="clear"></div>

					<div class="total_main_amount"><?php echo $formText_TotalAmount_output." <span>".number_format($payment['amount'], 2, ",", "");?></span></div>
					<div class="clear"></div>
				</div>
				<div class="popupformbtn">
					<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
					<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
				</div>
			</form>
		</div>
		<style>
		.fixedTable {
			table-layout: fixed;
		}
		.fixedTable input {
			width: 100%;
			border-radius: 3px;
			padding: 5px 10px;
			border: 1px solid #cecece;
		}
		.fixedTable td.rightAligned,
		.fixedTable th.rightAligned {
			text-align: right;
		}
		.total_main_amount {
			float: right;
		}
		.total_payed_amount {
			float: right;
		}
		.claimtype_select {
			width: 100%;
		}
		.deleteCoverLine {
			cursor: pointer;
			color: #46b2e2;
			float: right;
		}
		.add_coverline {
			cursor: pointer;
			color: #46b2e2;
		}

		</style>
		<script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
		<script type="text/javascript">
		$(function(){

			$(".add_coverline").off("click").on("click", function(){
				var index = "new-"+$(".coverlineTable tr").length;
				var row = '<tr><td>'+
					'<select name="collecting_claim_line_type['+index+']" class="claimtype_select" autocomplete="off" required>';
				<?php
					foreach($claim_line_types as $claim_line_type) {
						?>
						row += '<option value="<?php echo $claim_line_type['id'];?>"> <?php echo $claim_line_type['type_name'];?></option>';
						<?php
					}
					?>
				row += '</select>';
				row += '</td>';
				row += '<td><input class="company_amount" type="text" autocomplete="off" name="company_amount['+index+']" value="0" /></td>';
				row += '<td><input class="creditor_amount" type="text" autocomplete="off" name="creditor_amount['+index+']" value="0" /></td>';
				row += '<td><input class="debitor_amount" type="text" autocomplete="off" name="debitor_amount['+index+']" value="0" /></td>';
				row += '<td class="rightAligned"><span class="total_amount">0</span></td>';
				row += '<td><span class="glyphicon glyphicon-trash deleteCoverLine"></span></td></tr>';
				$(".coverlineTable").append(row);

				bindChangeTriggers();
				calculateTotal();
			})
			function calculateTotal() {
				var totalMainAmount = 0;
				$(".company_amount").each(function(){
					var parentTr = $(this).parents("tr");
					var companyAmountEl = parentTr.find(".company_amount");
					var creditorAmountEl = parentTr.find(".creditor_amount");
					var debitorAmountEl = parentTr.find(".debitor_amount");
					var totalAmountEl = parentTr.find(".total_amount");
					var creditorAmount = creditorAmountEl.val();
					var companyAmount = companyAmountEl.val();
					var debitorAmount = debitorAmountEl.val();
					creditorAmount = creditorAmount.replace(",", ".");
					companyAmount = companyAmount.replace(",", ".");
					debitorAmount = debitorAmount.replace(",", ".");
					totalAmount = parseFloat(companyAmount)+parseFloat(debitorAmount)+parseFloat(creditorAmount);
					totalMainAmount += totalAmount;
					totalAmount = totalAmount.toFixed(2);
					totalAmountEl.html(totalAmount.toString().replace(".", ","));
				})
				$(".total_main_amount span").html(totalMainAmount.toFixed(2).toString().replace(".", ","));
			}
			function bindChangeTriggers(){
				$(".company_amount").off("keyup").on("keyup", function() {
					calculateTotal();
				})
				$(".creditor_amount").off("keyup").on("keyup", function() {
					calculateTotal();
				})
				$(".debitor_amount").off("keyup").on("keyup", function() {
					calculateTotal();
				})
				$(".deleteCoverLine").off("click").on("click", function(){
					$(this).parents("tr").remove();
					calculateTotal();
				})
			}
			bindChangeTriggers();

		    $(".popupform-<?php echo $creditor_id;?> form.output-form").validate({
		        ignore: [],
		        submitHandler: function(form) {
		            fw_loading_start();
					if($(".total_main_amount span").html() == $(".total_payed_amount span").html()){
						$(".popupform-<?php echo $creditor_id;?> #popup-validate-message").hide();
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
			                $(".popupform-<?php echo $creditor_id;?> #popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
			                $(".popupform-<?php echo $creditor_id;?> #popup-validate-message").show();
			                $('.popupform-<?php echo $creditor_id;?> #popupeditbox').css('height', $('.popupform-<?php echo $creditor_id;?> #popupeditboxcontent').height());
			                fw_loading_end();
			            });
					} else {
		                $(".popupform-<?php echo $creditor_id;?> #popup-validate-message").html("<?php echo $formText_TotalSumShouldMatchPayment_output;?>").show();
						fw_loading_end();
					}
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
		})
		</script>
	<?php
	}
} else {
	echo $formText_NoPaymentFound_output;
}
?>
