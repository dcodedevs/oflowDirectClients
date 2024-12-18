<?php
$cid = isset($_GET['cid']) ? $_GET['cid'] : 0;

$sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($sql, array($cid));
$creditor = $o_query ? $o_query->row_array() : array();
if($creditor) {	
	$currencyName = "";
	$invoiceDifferentCurrency = false;
	if($fee_transaction['currency'] == 'LOCAL') {
		$currencyName = trim($creditor['default_currency']);
	} else {
		$currencyName = trim($fee_transaction['currency']);
		$invoiceDifferentCurrency = true;
	}
	if($_POST['output_form_submit']){
		$fw_error_msg='error';
		return;
	}
	$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid;
	?>
	<div id="p_container" class="p_container <?php echo $folderName; ?>">
		<div class="p_containerInner">
			<div class="p_content">
				<div class="p_pageContent">
					<a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list" style="display: block; margin-bottom:10px; float: left;"><?php echo $formText_BackToCreditor_outpup;?></a>
					<div class="clear"></div>
				</div>
			</div>
			<div class="creditor_info_row title_row">
				<?php echo $formText_CreditorName_output;?>:
				<b><?php echo $creditor['companyname'];?></b>
			</div>
			<form class="output-form-case main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=show_fees_without_connection";?>" method="post">
				<input type="hidden" name="fwajax" value="1">
				<input type="hidden" name="fw_nocss" value="1">
				<input type="hidden" name="output_form_submit" value="1">				
				<input type="hidden" name="cid" value="<?php echo $cid;?>">

				<?php

				// $s_sql = "SELECT ct.* FROM creditor_transactions ct 
				// JOIN creditor_transactions ct2 ON ct2.transaction_id = ct.comment
				// WHERE ct.open = 1 AND (ct.system_type='CreditnoteCustomer') AND ct2.open = 0 AND ct.creditor_id = '".$o_main->db->escape_str($creditor['id'])."' AND (ct.collectingcase_id is null OR ct.collectingcase_id = 0)
				// LIMIT 50";
				// $o_query = $o_main->db->query($s_sql);
				// $openCreditnotesWithClosedConnection = $o_query ? $o_query->result_array() : array();


				$s_sql = "SELECT ct.* FROM creditor_transactions ct 
				LEFT JOIN creditor_transactions ct2 ON ct2.link_id = ct.link_id AND ct2.collectingcase_id > 0
				LEFT JOIN creditor_transactions ct3 ON ct3.transaction_id = ct.comment
				WHERE ct.open = 1 AND (ct.system_type='InvoiceCustomer' OR ct.system_type='CreditnoteCustomer') AND ct2.id IS NULL AND ct.creditor_id = '".$o_main->db->escape_str($creditor['id'])."' 
				AND (ct.collectingcase_id is null OR ct.collectingcase_id = 0) AND (ct.comment LIKE '%reminderFee_%' OR ct.comment LIKE '%interest_%' OR (ct.comment LIKE '%-%-%-%-%' AND ct3.`open` = 0))
				LIMIT 50";
				$o_query = $o_main->db->query($s_sql);
				$openFeesWithoutConnection = $o_query ? $o_query->result_array() : array();
				?>
				<table class="table">
					<tr>
						<th class="gtable_cell"><input type="checkbox" class="select_all"/></th>
						<th class="gtable_cell"><?php echo $formText_Type_output;?></th>
						<th class="gtable_cell"><?php echo $formText_Date_output;?><br/><?php echo $formText_DueDate_output;?></th>
						<th class="gtable_cell"><?php echo $formText_InvoiceNr_output;?></th>
						<th class="gtable_cell"><?php echo $formText_Comment_output;?></th>
						<th class="gtable_cell"><?php echo $formText_LinkId_output;?></th>
						<th class="gtable_cell"><?php echo $formText_Amount_output;?></th>
						<th class="gtable_cell"><?php echo $formText_Status_output;?></th>
					</tr>
					<?php
					foreach($openFeesWithoutConnection as $openFeeWithoutConnection){
						?>
						<tr>
							<td class="gtable_cell"><input type="checkbox" class="reset_fee_select" name="transaction_ids[]" value="<?php echo $openFeeWithoutConnection['id'];?>"/></td>
							<td class="gtable_cell"><?php echo $openFeeWithoutConnection['system_type'];?></td>
							<td class="gtable_cell"><?php echo date("d.m.Y", strtotime($openFeeWithoutConnection['date']));?><br/><?php if($openFeeWithoutConnection['dueDate']!="" && $invoicesTransaction['dueDate'] != "0000-00-00") echo date("d.m.Y", strtotime($invoicesTransaction['dueDate']));?></td>
							<td class="gtable_cell"><?php echo $openFeeWithoutConnection['invoice_nr'];?></td>
							<td class="gtable_cell"><?php echo $openFeeWithoutConnection['comment'];?></td>
							<td class="gtable_cell"><?php echo $openFeeWithoutConnection['link_id'];?></td>
							<td class="gtable_cell"><?php echo $openFeeWithoutConnection['amount'];?></td>
							<td class="gtable_cell"><?php
							if($openFeeWithoutConnection['open']) {
								echo $formText_Open_output;
							} else {
								echo $formText_Closed_output;
							}
							?></td>
						</tr>
						<?php
					}
					?>
				</table>
				<div id="error_message"></div>
				<input type="submit" value="<?php echo $formText_ResetFees_output;?>"/>
			</form>
			<script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
			<script type="text/javascript">

			$(document).ready(function() {
				$(".select_all").off("click").on("click", function(){
					if($(this).is(":checked")) {
						$(".reset_fee_select").slice(0,50).prop("checked", true);
					} else{
						$(".reset_fee_select").prop("checked", false);
					}
				})
			    $("form.output-form-case").validate({
			        ignore: [],
			        submitHandler: function(form) {
			            fw_loading_start();
						$("#error_message").html("");
			            $.ajax({
			                url: $(form).attr("action"),
			                cache: false,
			                type: "POST",
			                dataType: "json",
			                data: $(form).serialize(),
			                success: function (data) {
			                    fw_loading_end();
			                    if(data.error === undefined)
			                    {
									var data = {cid:'<?php echo $cid;?>'}
									loadView("show_fees_without_connection", data);
			                    } else {
									$("#error_message").html(data.error);
								}
			                }
			            }).fail(function() {
			                $(".popupform-<?php echo $caseId;?> #popup-validate-message-case").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
			                $(".popupform-<?php echo $caseId;?> #popup-validate-message-case").show();
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

			                $(".popupform-<?php echo $caseId;?> #popup-validate-message-case").html(message);
			                $(".popupform-<?php echo $caseId;?> #popup-validate-message-case").show();
			                $('.popupform-<?php echo $caseId;?> #popupeditbox').css('height', $('#popupeditboxcontent').height());
			            } else {
			                $(".popupform-<?php echo $caseId;?> #popup-validate-message-case").hide();
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
			});

			</script>
			<style>
				#error_message {
					color: red;
					font-weight: bold;
					margin-bottom: 10px;
				}
			</style>
		</div>
	</div>
	<style>
	.invoice_block {
		margin-bottom: 10px;
		background: #fff;
	}
	.page_link {
		cursor: pointer;
		margin-right: 5px;
	}
	.page_link.active {
		text-decoration: underline;
		font-weight: bold;
	}
	.invoice_case_block {
		padding: 5px;
	}
	.title_row  {
		font-size: 18px;
		margin-bottom: 10px;
	}
	</style> 
	<?php
}
?>
