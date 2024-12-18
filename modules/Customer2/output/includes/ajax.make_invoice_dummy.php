<?php
$collectingOrderId = $_POST['collectingOrderId'];
if($collectingOrderId > 0){
	if($moduleAccesslevel > 10)
	{
		if(isset($_POST['output_form_submit']))
		{
			$s_sql = "SELECT * FROM customer_collectingorder WHERE id = '".$o_main->db->escape_str($collectingOrderId)."'";
			$o_query = $o_main->db->query($s_sql);
			$collectingorder = $o_query ? $o_query->row_array() : array();

		    $s_sql = "SELECT * FROM batch_invoicing_basisconfig";
			$o_query = $o_main->db->query($s_sql);
			if($o_query && $o_query->num_rows()>0){
				$basisConfigData = $o_query->row_array();
			}
			$s_sql = "SELECT * FROM batch_invoicing_accountconfig";
			$o_query = $o_main->db->query($s_sql);
			$batchinvoicing_accountconfig = $o_query ? $o_query->row_array() : array();

			if($batchinvoicing_accountconfig['activateCheckForVatCode'] == 1){
				$basisConfigData['activateCheckForVatCode'] = 1;
			} else if($batchinvoicing_accountconfig['activateCheckForVatCode'] == 2) {
				$basisConfigData['activateCheckForVatCode'] = 0;
			}
			if($batchinvoicing_accountconfig['activateCheckForArticleNumber'] == 1){
				$basisConfigData['activateCheckForArticleNumber'] = 1;
			} else if($batchinvoicing_accountconfig['activateCheckForArticleNumber'] == 2) {
				$basisConfigData['activateCheckForArticleNumber'] = 0;
			}
			if($batchinvoicing_accountconfig['activateCheckForProjectNr'] == 1){
				$basisConfigData['activateCheckForProjectNr'] = 1;
			} else if($batchinvoicing_accountconfig['activateCheckForProjectNr'] == 2) {
				$basisConfigData['activateCheckForProjectNr'] = 0;
			}
			if($batchinvoicing_accountconfig['activateCheckForDepartmentCode'] == 1){
				$basisConfigData['activateCheckForDepartmentCode'] = 1;
			} else if($batchinvoicing_accountconfig['activateCheckForDepartmentCode'] == 2) {
				$basisConfigData['activateCheckForDepartmentCode'] = 0;
			}
			if($batchinvoicing_accountconfig['activateCheckForBookaccountNr'] == 1){
				$basisConfigData['activateCheckForBookaccountNr'] = 1;
			} else if($batchinvoicing_accountconfig['activateCheckForBookaccountNr'] == 2) {
				$basisConfigData['activateCheckForBookaccountNr'] = 0;
			}
			if($_POST['external_invoice_nr'] > 0 && $_POST["invoice_date"] != "" && $_POST["due_date"] != "") {
				include(__DIR__."/../../../BatchInvoicing/procedure_create_invoices/scripts/CREATE_INVOICE/functions.php");
				$external_invoice_nr = $_POST['external_invoice_nr'];
				$ownercompany_id = $collectingorder['ownercompanyId'];

				$s_sql = "SELECT * FROM invoice WHERE external_invoice_nr = '".$o_main->db->escape_str($external_invoice_nr)."'";
				$o_query = $o_main->db->query($s_sql);
				$invoiceExists = $o_query ? $o_query->row_array() : array();
				if(!$invoiceExists) {

					$s_sql = "SELECT * FROM ownercompany WHERE id = ?";
					$o_query = $o_main->db->query($s_sql, array($ownercompany_id));
					$v_settings = $o_query ? $o_query->row_array() : array();

					$s_sql = "SELECT * FROM customer WHERE id = '".$o_main->db->escape_str($collectingorder['customerId'])."'";
					$o_query = $o_main->db->query($s_sql);
					$v_customer = $o_query ? $o_query->row_array() : array();

					$customerIdToDisplay = $v_customer['id'];
					if ($batchinvoicing_accountconfig['activate_customer_sync_at_start']) {
						$hook_params = array(
							'customer_id' => $v_customer['id'],
							'ownercompany_id' => $ownercompany_id,
						);
						$hook_file = __DIR__ . '/../../../../../' . $batchinvoicing_accountconfig['path_for_customer_sync_at_start'];
						if (file_exists($hook_file)) {
							include $hook_file;
							if (is_callable($run_hook)) {
								$batch_log.=" \nHook Started for customer ".$invoice_id." - ".date("d.m.Y H:i:s");
								$o_main->db->query("UPDATE batch_invoicing SET log = ? WHERE id = ?", array($batch_log, $notProcessedInvoice["batch_id"]));

								$hook_result = $run_hook($hook_params);

								$batch_log.=" \nHook Result ".json_encode($hook_result)." - ".date("d.m.Y H:i:s");
								$o_main->db->query("UPDATE batch_invoicing SET log = ? WHERE id = ?", array($batch_log, $notProcessedInvoice["batch_id"]));

								unset($run_hook);
								// Get customer external id
								$s_sql = "SELECT * FROM customer_externalsystem_id WHERE ownercompany_id = ? AND customer_id = ?";
								$o_query = $o_main->db->query($s_sql, array($ownercompany_id, $v_customer['id']));

								$externalCustomerIdData = $o_query ? $o_query->row_array() : array();
								$externalCustomerId = $externalCustomerIdData['external_id'];

								if ($externalCustomerId) {
									$customerIdToDisplay = $externalCustomerId;
									$external_sys_id = $externalCustomerIdData['external_sys_id'];
								}
							}
						}
					} else {

						if ($ownercompanyAccountconfig['activate_global_external_company_id']) {
							// Get customer external id
							$s_sql = "SELECT * FROM customer_externalsystem_id WHERE ownercompany_id = 0 AND customer_id = ?";
							$o_query = $o_main->db->query($s_sql, array($v_customer['id']));
							$externalCustomerIdData = $o_query ? $o_query->row_array() : array();

							$externalCustomerId = $externalCustomerIdData['external_id'];
							$customerIdToDisplay = $externalCustomerId;

							if (!$externalCustomerId) {
								$nextCustomerId = $ownercompanyAccountconfig['next_global_external_company_id'] ? $ownercompanyAccountconfig['next_global_external_company_id'] : 1;
								$o_main->db->query("INSERT INTO customer_externalsystem_id
								SET created = NOW(),
								ownercompany_id = ?,
								customer_id = ?,
								external_id = ?,
								external_sys_id = ?", array(0, $v_customer['id'], $nextCustomerId, 0));

								$customerIdToDisplay = $nextCustomerId;

								$nextCustomerId++;

								$o_main->db->query("UPDATE ownercompany_accountconfig SET next_global_external_company_id = $nextCustomerId");
							}
						}
						else {
							if ($v_settings['customerid_autoormanually']) {

								// Get customer external id
								$s_sql = "SELECT * FROM customer_externalsystem_id WHERE ownercompany_id = $ownercompany_id AND customer_id = ?";
								$o_query = $o_main->db->query($s_sql, array($v_customer['id']));
								$externalCustomerIdData = $o_query ? $o_query->row_array() : array();
								$externalCustomerId = $externalCustomerIdData['external_id'];

								if ($externalCustomerId) {
									$customerIdToDisplay = $externalCustomerId;
									$external_sys_id = $externalCustomerIdData['external_sys_id'];

									if ($use_integration) {
										$api->update_customer(array(
											'id' => $external_sys_id,
											'name' => trim($v_customer['name']),
											'customerNumber' => $externalCustomerId
										));
									}

								} else {
									// Create automatically
									if ($v_settings['customerid_autoormanually'] == '1') {

										// next customer id
										$nextCustomerId = $v_settings['nextCustomerId'];

										if ($use_integration) {
											$new_customer_on_api = $api->add_customer(array(
												'name' => trim($v_customer['name']),
												'customerNumber' => $nextCustomerId
											));
											$external_sys_id = $new_customer_on_api['id'] ? $new_customer_on_api['id'] : 0;
											$nextCustomerId = $new_customer_on_api['customerNumber'];
										} else {
											$external_sys_id = 0;
										}

										$o_main->db->query("INSERT INTO customer_externalsystem_id
										SET created = NOW(),
										ownercompany_id = ?,
										customer_id = ?,
										external_id = ?,
										external_sys_id = ?", array($ownercompany_id, $v_customer['id'], $nextCustomerId, $external_sys_id));

										$customerIdToDisplay = $nextCustomerId;

										$nextCustomerId++;

										$o_main->db->query("UPDATE ownercompany SET nextCustomerId = $nextCustomerId WHERE id = ?", array($ownercompany_id));
									}
								}
							}
						}
					}

					$newInvoiceNrOnInvoice = $external_invoice_nr;
					$kidnumber = generate_kidnumber($v_settings, $customerIdToDisplay, $newInvoiceNrOnInvoice, 0);

					$s_sql = "SELECT * FROM moduledata WHERE name = 'Invoice'";
					$o_query = $o_main->db->query($s_sql);
					if($o_query && $o_query->num_rows()>0){
						$v_row = $o_query->row_array();
					}
					$l_invoice_module_id = $v_row['uniqueID'];

					$sum = 0;
					$tax = 0;
					$totalSum = 0;

					$s_sql = "SELECT * FROM orders WHERE collectingorderId = '".$o_main->db->escape_str($collectingorder['id'])."'";
					$o_query = $o_main->db->query($s_sql);
					$orderlines = $o_query ? $o_query->result_array() : array();
					foreach($orderlines as $orderline){
						$sum += $orderline['priceTotal'];
						$tax += ($orderline['gross'] - $orderline['priceTotal']);
						$totalSum += $orderline['gross'];
					}
					$currentCurrencyCode = 0;
					$currentCurrency = 'EMPTY_CURRENCY';
					$creditRefNo = 0;
					$files_attached = json_decode($collectingorder['files_attached_to_invoice'], true);
					$for_print = 0;
					$for_sending = 0;

					$dateValShow = $_POST["invoice_date"];
					$dateExpireShow = $_POST["due_date"];
					$dateVal = date("Y-m-d", strtotime($dateValShow));
					$dateExpire = date("Y-m-d", strtotime($dateExpireShow));
					$files_attached_json = json_encode($files_attached);

					$invoiceSQL = "INSERT INTO invoice(created, createdBy, moduleID,customerId,invoiceDate,dueDate,totalExTax,tax,totalInclTax, currencyCode, currencyName, ownercompany_id, external_invoice_nr, creditRefNo, files_attached, kidNumber, batch_id, not_processed, for_print, for_sending)
					VALUES(NOW(), '".$_COOKIE['username']."', '".$l_invoice_module_id."','".$v_customer["id"]."','$dateVal','$dateExpire','$sum','$tax','$totalSum','$currentCurrencyCode','$currentCurrency', '$ownercompany_id', '$external_invoice_nr', '$creditRefNo', '$files_attached_json', '$kidnumber',
					'0', 0, $for_print, $for_sending);";
					$o_query = $o_main->db->query($invoiceSQL);
					if($o_query){
						$invoice_id = $o_main->db->insert_id();

						$s_sql = "UPDATE customer_collectingorder SET invoiceNumber = '".$o_main->db->escape_str($invoice_id)."' WHERE id = '".$o_main->db->escape_str($collectingorder['id'])."'";
						$o_query = $o_main->db->query($s_sql);
						$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['customerId'];
						return;
					} else {
						$fw_error_msg[] = $formText_ErrorAddingInvoice_output;
					}
				} else {
					$fw_error_msg[] = $external_invoice_nr." ".$formText_InvoiceNumberTaken_output;
				}
			}
			return;
		}
	}
	?>
	<div class="popupform">
		<div id="popup-validate-message" style="display: none;"></div>
	<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=".$s_inc_obj."&inc_act=".$s_inc_act;?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="customerId" value="<?php echo $_POST['customerId'];?>">
		<input type="hidden" name="collectingOrderId" value="<?php echo $_POST['collectingOrderId'];?>">

		<div class="inner">

			<?php echo $formText_InvoiceDate_output; ?>
			<input type="text" class="invoice_date_input datepicker" name="invoice_date" value="<?php
			if($v_customer_accountconfig['deactivate_buttons_for_invoice_date']){
				echo date('d.m.Y', time());
			}
			?>" autocomplete="off" required>
			<?php echo $formText_DueDate_output; ?>
			<input type="text" class="due_date_input datepicker" name="due_date" value="<?php
				echo date('d.m.Y', time() + 60*60*24*$creditTimeDays);
			?>" autocomplete="off" required>
			<br/><br/>
			<div class="line">
			<div class="lineTitle"><?php echo $formText_ExternalInvoiceNr_Output; ?></div>
			<div class="lineInput"><input type="text" class="popupforminput" autocomplete="off" name="external_invoice_nr" required></div>
			<div class="clear"></div>
			</div>

		</div>
		<div class="popupformbtn"><input type="submit" name="sbmbtn" value="<?php echo $formText_MakeInvoice_Output; ?>"></div>
	</form>
	</div>
	<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
	<script type="text/javascript">
	$(function() {
		$('.datepicker').datepicker({
			dateFormat: 'dd.mm.yy'
		});

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
						if(data.error !== undefined)
    					{
    						var _msg = '';
    						$.each(data.error, function(index, value){
    							var _type = Array("error");
    							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
    							_msg = _msg + '<div class="msg-' + _type[0] + '">' + value + '</div>';
    						});
    						$("#popup-validate-message").html(_msg, true);
    						$("#popup-validate-message").show();
    					} else {
							if(data.redirect_url !== undefined)
							{
								out_popup.addClass("close-reload");
								out_popup.close();
							}
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
<?php } ?>
