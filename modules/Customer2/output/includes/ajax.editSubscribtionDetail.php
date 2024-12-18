<?php
$subscribtionId = $_POST['subscribtionId'] ? ($_POST['subscribtionId']) : 0;
$customerId = $_POST['customerId'] ? ($_POST['customerId']) : 0;
$action = $_POST['action'] ? ($_POST['action']) : '';

$s_sql = "select * from accountinfo";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) {
	$v_accountinfo = $o_query->row_array();
}


$s_sql = "SELECT * FROM ownercompany_accountconfig";
$o_query = $o_main->db->query($s_sql);
$ownercompany_accountconfig = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM company_product_set ORDER BY name ASC";
$o_query = $o_main->db->query($s_sql);
$company_product_sets = $o_query ? $o_query->result_array() : array();

$s_sql = "SELECT * FROM customer_basisconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $customer_basisconfig = $o_query->row_array();
}
$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_customer_accountconfig = $customer_accountconfig = $o_query->row_array();
}
require_once("fnc_rewritebasisconfig.php");
rewriteCustomerBasisconfig();

$s_sql = "select * from repeatingorder_accountconfig";
$o_query = $o_main->db->query($s_sql);
$v_repeatingorder_accountconfig = ($o_query ? $o_query->row_array() : array());

$s_sql = "select * from repeatingorder_basisconfig";
$o_query = $o_main->db->query($s_sql);
$v_repeatingorder_basisconfig = ($o_query ? $o_query->row_array() : array());

foreach($v_repeatingorder_accountconfig as $key=>$value){
    if(array_key_exists($key, $v_repeatingorder_basisconfig) && $value > 0){
        $v_repeatingorder_basisconfig[$key] = ($value - 1);
    }
}
// Save
if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
        $confirmedUpdate = true;
        if($subscribtionId){
            if($_POST['stoppedDate'] != "") {
                if(!$_POST['keep_workplanlines']){
                    if($_POST['delete_workplanlines']){
                        $s_sql = "DELETE FROM workplanlineworker WHERE date > ? AND repeatingOrderId = ?";
                        $o_query = $o_main->db->query($s_sql, array(unformatDate($_POST['stoppedDate'], true), $subscribtionId));
                    }
                    $s_sql = "SELECT contactperson.*, workplanlineworker.*  FROM workplanlineworker
                    LEFT OUTER JOIN contactperson ON contactperson.id = workplanlineworker.employeeId
                    WHERE workplanlineworker.date > ? AND workplanlineworker.repeatingOrderId = ?";
                    $o_query = $o_main->db->query($s_sql, array(unformatDate($_POST['stoppedDate'], true), $subscribtionId));
                    $workplanlines = $o_query ? $o_query->result_array() : array();
                    if(count($workplanlines) > 0){
                        $confirmedUpdate = false;
                    }
                }

            }
        }
		// Defining a callback function
		function myFilter($var){
		    return ($var !== NULL && $var !== FALSE && $var !== "");
		}
		$s_sql = "SELECT * FROM subscriptiontype WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($_POST['subscriptiontype_id']));
		$subscriptionType = $o_query ? $o_query->row_array() : array();

		if(!$subscriptionType){
			$fw_error_msg[] = $formText_MissingSubscriptionType_output;
			return;
		}
		if($subscriptionType['subscription_category'] == 1){
			if(intval($_POST['activeSubmember']) == 0){
				if(count($_POST['dates_for_invoicing']) > 0){
					$errorInDates = false;
					$startDate = $_POST['dates_for_invoicing'][0];
					$yearJump = 0;
					$yearJumpCurrent = 0;
					$year = "2020";
					$_POST['dates_for_invoicing'] = array_filter($_POST['dates_for_invoicing'], "myFilter");
					foreach($_POST['dates_for_invoicing'] as $index => $date_for_invoicing){
						if($date_for_invoicing != ""){
							$nextIndex = $index+1;
							if($nextIndex >= count($_POST['dates_for_invoicing'])){
								$nextIndex = 0;
							}
							if($index > 0){
								$next_date_for_invoicing = $_POST['dates_for_invoicing'][$nextIndex];
								if(strtotime($startDate.".".$year) >= strtotime($next_date_for_invoicing.".".$year)) {
									$yearJump = 1;
								}
								if(strtotime($startDate.".".$year) >= strtotime($date_for_invoicing.".".$year)) {
									$yearJumpCurrent = 1;
								}

								if(strtotime($next_date_for_invoicing.".".($year+$yearJump)) <= strtotime($date_for_invoicing.".".($year+$yearJumpCurrent))) {
									$errorInDates = true;
								}
							}
						}
					}
					if($errorInDates){
						$fw_error_msg[] = $formText_WrongDates_output." ". implode(", ", $_POST['dates_for_invoicing']);
						return;
					}
				}
				if(count($_POST['dates_for_invoicing']) > 0 && !in_array(date("d.m", strtotime($_POST['nextRenewalDate'])), $_POST['dates_for_invoicing'])) {
					$fw_error_msg[] = $formText_NextRenewalDateCanBeOnlyDuringTheInvoiceDates_output." ". implode(", ", $_POST['dates_for_invoicing']);
					return;
				}
			}
		}
		if($subscriptionType){
			$s_sql = "SELECT * FROM subscriptiontype_subtype WHERE subscriptiontype_id = ?";
			$o_query = $o_main->db->query($s_sql, array($_POST['subscriptiontype_id']));
			$subscriptionSubTypes = $o_query ? $o_query->result_array() : array();
			if(count($subscriptionSubTypes) > 0) {
				if($_POST['subscriptionsubtypeId'.$subscriptionType['id']] > 0){

				} else {
					$fw_error_msg[] = $formText_MissingSubscriptionSubtype_output;
					return;
				}
			}
		}
		if(!$subscriptionType['activateSubmemberWithoutInvoicing']) {
			$_POST['connectedCustomerId'] = "";
		}
		if(!$_POST['activeSubmember']) {
			$_POST['connectedCustomerId'] = "";
		}
		if($_POST['nextRenewalDate'] != ""){
			if($_POST['startDate'] == "") {
				$fw_error_msg[] = $formText_MissingStartDate_output;
				return;
			}
		}
		if($_POST['startDate'] != ""){
			if(intval($_POST['activeSubmember']) == 0){
				if($_POST['nextRenewalDate'] == "") {
					$fw_error_msg[] = $formText_MissingNextRenewalDate_output;
					return;
				}
			}
		}
		if($_POST['periodNumberOfMonths'] == 0){
			if(intval($_POST['activeSubmember']) == 0 && $subscriptionType['subscription_category'] != 1){
				$fw_error_msg[] = $formText_PeriodNumberCanNotBe0_output;
				return;
			}
		}

		if($v_customer_accountconfig['activate_subunits']){
			$s_sql = "SELECT * FROM customer_subunit WHERE customer_id = ? AND content_status < 1 ORDER BY name";
			$o_query = $o_main->db->query($s_sql, array($_POST['customerId']));
			$subunits = $o_query ? $o_query->result_array() : array();

			if(intval($_POST['subunit_id']) == 0 && count($subunits) > 0){
				$fw_error_msg[] = $formText_MissingSubUnit_output;
				return;
			}
		}


        if($v_repeatingorder_basisconfig['activate_seller'] ==  2) {
            if(intval($_POST['seller_people_id']) == 0){
                $fw_error_msg = array($formText_SellerIsMandatory_output);
                return;
            }
        }

		$s_sql = "SELECT subscriptiontype_selfdefined_connection.*, customer_selfdefined_fields.* FROM subscriptiontype_selfdefined_connection
		LEFT OUTER JOIN customer_selfdefined_fields ON customer_selfdefined_fields.id = subscriptiontype_selfdefined_connection.selfdefinedfield_id
		WHERE subscriptiontype_selfdefined_connection.subscriptiontype_id = ? ORDER BY customer_selfdefined_fields.name ASC";
		$o_query = $o_main->db->query($s_sql, array($subscriptionType['id']));
		$selfdefinedFields = $o_query ? $o_query->result_array() : array();
		if(count($selfdefinedFields) > 0) {
			foreach($selfdefinedFields as $selfdefinedField) {
				if($selfdefinedField['type'] == 2){
					$valueItems = $_POST['selfdefinedfieldValues'][$subscriptionType['id']][$selfdefinedField['id']];
					if(!$selfdefinedField['not_mandatory']){
						$missingValues = true;
						foreach($valueItems as $valueItem){
							if($valueItem != "") {
								$missingValues = false;
							}
						}
						if($missingValues) {
							$fw_error_msg[] = $selfdefinedField['name']." ".$formText_isMandatory_output;
						}
					}
				} else {
					if(!$selfdefinedField['not_mandatory']){
						if($selfdefinedField['type'] == 0){
							if($selfdefinedField['hide_textfield']){
								$valueItem = $_POST['selfdefinedfieldCheckboxes'][$subscriptionType['id']][$selfdefinedField['id']];
							} else {
								$valueItem = $_POST['selfdefinedfieldValues'][$subscriptionType['id']][$selfdefinedField['id']];
							}
						} else {
							$valueItem = $_POST['selfdefinedfieldValues'][$subscriptionType['id']][$selfdefinedField['id']];
						}

						if($valueItem == "") {
							$fw_error_msg[] = $selfdefinedField['name']." ".$formText_isMandatory_output;
						}
					}
				}
			}
		}

		if(count($fw_error_msg) > 0){
			return;
		}
        if($confirmedUpdate){
			$originalDate = $_POST['original_start_date'];
			if($originalDate == ""){
				$originalDate = $_POST['startDate'];
			}
            if ($subscribtionId) {
				$approved = true;

				$s_sql = "SELECT * FROM subscriptionmulti WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($subscribtionId));
				$subscriptionmulti = $o_query ? $o_query->row_array() : array();


				$repeatingOrderWorkersToDelete = array();
				$workplanlineworkersToDelete = array();
				$repeatingOrderWorkLinesToDelete = array();
				if($subscriptionmulti['subscriptiontype_id'] != $_POST['subscriptiontype_id']) {
					$s_sql = "SELECT * FROM subscriptiontype WHERE id = ? ORDER BY name";
					$o_query = $o_main->db->query($s_sql, array($subscriptionmulti['subscriptiontype_id']));
					$oldsubscriptiontype = $o_query ? $o_query->row_array() : array();

					$s_sql = "SELECT * FROM subscriptiontype WHERE id = ? ORDER BY name";
					$o_query = $o_main->db->query($s_sql, array($_POST['subscriptiontype_id']));
					$newsubscriptiontype = $o_query ? $o_query->row_array() : array();
					if($newsubscriptiontype['activate_specified_invoicing']){
						$approved = false;

						$s_sql = "SELECT * FROM workplanlineworker WHERE repeatingOrderId = ? AND (preapproved = '' OR preapproved is null)";
						$o_query = $o_main->db->query($s_sql, array($subscriptionmulti['id']));
						$workplanlineworkersToDelete = $o_query ? $o_query->result_array() : array();


						$s_sql = "SELECT * FROM repeatingorderwork WHERE repeatingOrderId = ?";
						$o_query = $o_main->db->query($s_sql, array($subscriptionmulti['id']));
						$repeatingOrderWork = ($o_query ? $o_query->row_array() : array());

						if($repeatingOrderWork['periodType'] == 1 || $repeatingOrderWork['periodType'] == null){
							$s_sql = "SELECT * FROM repeatingorderworkline WHERE repeatingOrderWorkId = ? ORDER BY ABS(dayNumber) ASC";
						} else {
							$s_sql = "SELECT * FROM repeatingorderworkline WHERE repeatingOrderWorkId = ? ORDER BY weekType, weekDay ASC";
						}
						$o_query = $o_main->db->query($s_sql, array($repeatingOrderWork['id']));
						$repeatingOrderWorkLinesToDelete = ($o_query ? $o_query->result_array() : array());
						foreach($repeatingOrderWorkLinesToDelete as $repeatingOrderWorkLine){
							$s_sql = "SELECT *, reporderworklineworker.id FROM reporderworklineworker
							LEFT OUTER JOIN contactperson ON contactperson.id = reporderworklineworker.employeeId
							WHERE reporderworklineworker.repeatingOrderWorkLineId = ? ORDER BY reporderworklineworker.id";

							$o_query = $o_main->db->query($s_sql, array($repeatingOrderWorkLine['id']));
							$repeatingOrderWorklineWorkers = ($o_query ? $o_query->result_array() : array());
							foreach($repeatingOrderWorklineWorkers as $repeatingOrderWorklineWorker) {
								$repeatingOrderWorkersToDelete[] = $repeatingOrderWorklineWorker;
							}
						}
						if(count($repeatingOrderWorkersToDelete) == 0 && count($workplanlineworkersToDelete) == 0) {
							$approved = true;
						} else {

						}
					}
					if($_POST['approve']) {
						$approved = true;
					}
				}
				$beforeStartDate = false;
				$approvedWorkplanlines = array();
				if(unformatDate($_POST['startDate']) != $subscriptionmulti['startDate']){
					$s_sql = "SELECT * FROM workplanlineworker WHERE repeatingOrderId = ? AND date < ?";
					$o_query = $o_main->db->query($s_sql, array($subscriptionmulti['id'], unformatDate($_POST['startDate'])));
					$workplanlineworkersToDelete = $o_query ? $o_query->result_array() : array();
					if(count($workplanlineworkersToDelete) > 0){
						$approved = false;
						$beforeStartDate = true;
						foreach($workplanlineworkersToDelete as $workplanlineworkerToDelete){
							if($workplanlineworkerToDelete['salaryreportId'] > 0) {
								$approvedWorkplanlines[] = $workplanlineworkerToDelete;
							}
						}
						if(count($approvedWorkplanlines) == 0) {
							if($_POST['approve']) {
								$approved = true;
							}
						}
					}
				}
				if($approved) {
					if($customer_basisconfig['activateProjectConnection'] == 4 || $customer_basisconfig['activateProjectConnection'] == 5) {
						if($subscriptionmulti['projectId'] == ""){
							$fw_error_msg[] = $formText_MissingProjectCodeContactSystemDeveloper_output;
							return;
						}
					}
					foreach($repeatingOrderWorkLinesToDelete as $repeatingOrderWorkLineToDelete){
						$s_sql = "DELETE FROM repeatingorderworkline WHERE id = ?";
						$o_query = $o_main->db->query($s_sql, array($repeatingOrderWorkLineToDelete['id']));
					}
					foreach($repeatingOrderWorkersToDelete as $repeatingOrderWorkerToDelete){
						$s_sql = "DELETE FROM reporderworklineworker WHERE id = ?";
						$o_query = $o_main->db->query($s_sql, array($repeatingOrderWorkerToDelete['id']));
					}
					foreach($workplanlineworkersToDelete as $workplanlineworkerToDelete){
						$s_sql = "DELETE FROM workplanlineworker WHERE id = ?";
						$o_query = $o_main->db->query($s_sql, array($workplanlineworkerToDelete['id']));
					}

					$sql_extra = "";
					if($subscriptionmulti['agreement_entered_date'] == "" || $subscriptionmulti['agreement_entered_date'] == "0000-00-00") {
						$sql_extra .= ", agreement_entered_date = '".$o_main->db->escape_str(unformatDate($_POST['startDate']))."'";
					}
					if($subscriptionmulti['agreement_terminated_date'] == "" || $subscriptionmulti['agreement_terminated_date'] == "0000-00-00") {
						$sql_extra .= ", agreement_terminated_date = '".$o_main->db->escape_str(unformatDate($_POST['stoppedDate'], true))."'";
					}
					if($_POST['stoppedDate'] != ""){
						$sql_extra .= ", calendarScheduleType = 0";
					}
	                $s_sql = "UPDATE subscriptionmulti SET
	                updated = now(),
	                updatedBy= ?,
	                subscriptionName= ?,
	                startDate= ?,
	                stoppedDate= ?,
	                nextRenewalDate= ?,
	                ownercompany_id= ?,
	                subscriptiontype_id= ?,
	                periodNumberOfMonths= ?,
	                vat_free_contract = ?,
	                workgroupId = ?,
	                ".(isset($_POST['projectCode']) ? "projectId = '".$o_main->db->escape_str($_POST['projectCode'])."'," : '')."
	                freeNoBilling = ?,
	                extraCheckbox = ?,
	                departmentCode = ?,
	                placeSubscriptionNameInInvoiceLine = ?,
	                onhold = ?,
					connectedCustomerId = ?,
					subscriptionsubtypeId = ?,
					invoice_to_other_customer_id = ?,
					customer_subunit_id = ?".$sql_extra.",
					original_start_date = '".unformatDate($originalDate)."',
		            seller_people_id = '".$o_main->db->escape_str($_POST['seller_people_id'])."',
		            estimated_sales_value = '".$o_main->db->escape_str(str_replace(" ", "", str_replace(",", ".", $_POST['estimated_sales_value'])))."'
	                WHERE id = ?";
	                $o_main->db->query($s_sql, array($variables->loggID, $_POST['subscriptionName'], unformatDate($_POST['startDate']), unformatDate($_POST['stoppedDate'], true), unformatDate($_POST['nextRenewalDate']),
	                $_POST['ownercompany_id'], $_POST['subscriptiontype_id'], $_POST['periodNumberOfMonths'], $_POST['vatFreeContract'], $_POST['workgroup'],
	                $_POST['freeNoBilling'], $_POST['extraCheckbox'], $_POST['departmentCode'], $_POST['placeSubscriptionNameInInvoiceLine'], $_POST['onhold'], $_POST['connectedCustomerId'],$_POST['subscriptionsubtypeId'.$subscriptionType['id']],$_POST['invoiceCustomerId'],$_POST['subunit_id'], $subscribtionId));

	                $fw_redirect_url = $_POST['redirect_url'];

					foreach($selfdefinedFields as $selfdefinedField){
						if(is_array($_POST['selfdefinedfieldValues'][$subscriptionType['id']][$selfdefinedField['id']])) {
							$valueItems = $_POST['selfdefinedfieldValues'][$subscriptionType['id']][$selfdefinedField['id']];
						} else {
							$valueItems = array($_POST['selfdefinedfieldValues'][$subscriptionType['id']][$selfdefinedField['id']]);
						}

						$s_sql = "SELECT * FROM customer_selfdefined_values WHERE customer_id = ? AND selfdefined_fields_id = ?";
						$o_query = $o_main->db->query($s_sql, array($customerId, $selfdefinedField['id']));
						$selfdefinedValue = $o_query ? $o_query->row_array() : array();

						$addedListIds = array(-1);
						foreach($valueItems as $valueItem) {
							if($selfdefinedField['type'] == 0) {
								$checked = 0;
								if($_POST['selfdefinedfieldCheckboxes'][$subscriptionType['id']][$selfdefinedField['id']]) {
									$checked = 1;
								}
							} else {
								$checked = 1;
							}
							if(!$checked){
								$valueItem = "";
							}

							$updateSql = ", active = ".$o_main->db->escape($checked).", value = ".$o_main->db->escape($valueItem);

							if($selfdefinedValue){
								$o_main->db->query("UPDATE customer_selfdefined_values SET updated = NOW(), updatedBy = ?, customer_id = ?, selfdefined_fields_id = ?".$updateSql." WHERE id = ?", array($variables->loggID, $customerId, $selfdefinedField['id'], $selfdefinedValue['id']));
								$selfdefinedValueId = $selfdefinedValue['id'];
							} else {
								$o_main->db->query("INSERT INTO customer_selfdefined_values SET created = NOW(), createdBy = ?, customer_id = ?, selfdefined_fields_id = ?".$updateSql, array($variables->loggID, $customerId, $selfdefinedField['id']));
								$selfdefinedValueId = $o_main->db->insert_id();
							}
							if($selfdefinedField['type'] == 2) {
								$s_sql = "SELECT * FROM customer_selfdefined_values_connection  WHERE selfdefined_value_id = ? AND selfdefined_list_line_id = ?";
							    $o_query = $o_main->db->query($s_sql, array($selfdefinedValueId, $valueItem));
							    if($o_query){
							    	if($o_query->num_rows() == 0) {
							    		$o_main->db->query("INSERT INTO customer_selfdefined_values_connection SET selfdefined_value_id = ?, selfdefined_list_line_id = ?", array($selfdefinedValueId, $valueItem));
									}
									array_push($addedListIds, $valueItem);
							    }
							}
						}
						if($selfdefinedValue){
							$s_sql = "DELETE customer_selfdefined_values_connection FROM customer_selfdefined_values_connection
							WHERE selfdefined_value_id = ? AND selfdefined_list_line_id NOT IN (".implode(",", $addedListIds).")";
							$o_query = $o_main->db->query($s_sql, array($selfdefinedValue['id']));
						}
					}
					if($_POST['ownercompany_id'] != $subscriptionmulti['ownercompany_id']){
						$sql = "DELETE subscriptionline FROM subscriptionline WHERE subscriptionline.subscribtionId = ?";
						$o_main->db->query($sql, array($subscribtionId));
					}


				} else {
					?>
					<div class="popupform">
						<div id="popup-validate-message" style="display:none;"></div>
						<form class="output-form approveForm main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editSubscribtionDetail";?>" method="post">
							<input type="hidden" value="1" name="approve"/>
							<?php
							foreach($_POST as $key=>$value) {
								if(is_array($value)){
									foreach($value as $single){
										?>
										<input type="hidden" name="<?php echo $key;?>" value="<?php echo $single;?>"/>
										<?php
									}
								} else {
									?>
									<input type="hidden" name="<?php echo $key;?>" value="<?php echo $value;?>"/>
									<?php
								}
							}
							?>
							<div class="inner">
								<div class="warningMessage">
									<?php
									if($beforeStartDate) {
										if($approvedWorkplanlines) {
											echo $formText_CanNotSetStartDate_output." ".$_POST['startDate']." ".$formText_WorkplanlinesSettledBeforeTheDate_output;
										} else {
											echo count($workplanlineworkersToDelete)." ".$formText_WorkplanlinesExistBeforeStartDate_output." ".$_POST['startDate'];
											echo "<br/>".$formText_WorkplanlinesWillBeDeleted_output;
										}
									} else {
										 echo $formText_WorkplanlinesAndRepeatingorderWorksWillBeDeleted_output;
								 	}

									 ?>
								</div>
							</div>
							<div class="popupformbtn">
								<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
								<button type="button" class="output-btn b-large btn-project-period-ignore" data-project-id="<?php echo $projectId;?>" data-project-period-id="<?php echo $projectPeriodId;?>"><?php echo $formText_Confirm_Output; ?></button>
								<script type="text/javascript">

									$(".btn-project-period-ignore").off("click").on('click', function(e){
										var form = $(".approveForm");
										fw_loading_start();
										$.ajax({
 										   url: form.attr("action"),
 										   cache: false,
 										   type: "POST",
 										   dataType: "json",
 										   data: form.serialize(),
 										   success: function (data) {
 											   fw_loading_end();
											   if(data.redirect_url !== undefined)
											   {
												   out_popup.addClass("close-reload");
												   out_popup.close();
											   } else if(data.error == "delete_workplanlines"){
												   $(".popupformbtn .delete_workplanlines").show();
												   $(".popupformbtn .keep_workplanlines").show();
												   $(".output-form .notification_msg").html(data.data);
											   } else if(data.error != undefined){
												   $("#popup-validate-message").html(data.error, true);
												   $("#popup-validate-message").show();
											   } else  if(data.html != ""){
													 $('#popupeditboxcontent').html('');
													 $('#popupeditboxcontent').html(data.html);
													 out_popup = $('#popupeditbox').bPopup(out_popup_options);
													 $("#popupeditbox:not(.opened)").remove();
										 		}
 										   }
 									   }).fail(function() {
 										   $("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
 										   $("#popup-validate-message").show();
 										   $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
 										   fw_loading_end();
 									   });
								   });
								</script>
							</div>
						</form>
					</div>
					<?php
					return;
				}
            } else {
				if($customer_basisconfig['activateProjectConnection'] == 4 || $customer_basisconfig['activateProjectConnection'] == 5) {
					if($v_customer_accountconfig['next_available_projectcode'] > 0) {
						$s_sql = "SELECT * FROM projectforaccounting WHERE projectnumber = '".$o_main->db->escape_str($v_customer_accountconfig['next_available_projectcode'])."'";
						$o_query = $o_main->db->query($s_sql);
						$projectExisting = $o_query ? $o_query->row_array() : array();
						if(!$projectExisting) {
							$s_sql = "INSERT INTO projectforaccounting SET
			                created = now(),
			                createdBy= '".$o_main->db->escape_str($variables->loggID)."',
							name= '".$o_main->db->escape_str($_POST['subscriptionName'])."',
							projectnumber = '".$o_main->db->escape_str($v_customer_accountconfig['next_available_projectcode'])."',
							ownercompany_id = '".$o_main->db->escape_str($_POST['ownercompany_id'])."',
							customer_id = '".$o_main->db->escape_str($_POST['customerId'])."'";
							$o_query = $o_main->db->query($s_sql);
							if($o_query) {
								$projectforaccountingId = $o_main->db->insert_id();
								$_POST['projectCode'] = $v_customer_accountconfig['next_available_projectcode'];
								$nextCode = $v_customer_accountconfig['next_available_projectcode'] + 1;

								$s_sql = "UPDATE customer_accountconfig SET
				                updated = now(),
				               	updatedBy = '".$o_main->db->escape_str($variables->loggID)."',
								next_available_projectcode = '".$o_main->db->escape_str($nextCode)."'
								WHERE id = '".$o_main->db->escape_str($v_customer_accountconfig['id'])."'";
								$o_query = $o_main->db->query($s_sql);

								if($customer_basisconfig['activateProjectConnection'] == 5 && $v_customer_accountconfig['project_code_syncing_path'] != "") {
									$hook_params = array(
										'ownercompany_id' => $_POST['ownercompany_id'],
										'projectforaccountingId'=>$projectforaccountingId
						            );

						            $hook_file = __DIR__ . '/../../../../' . $v_customer_accountconfig['project_code_syncing_path'];
						            if (file_exists($hook_file)) {
						                require_once $hook_file;
						                if (is_callable($run_hook)) {
						                    $hook_result = $run_hook($hook_params);
						                    unset($run_hook);
						                }
						            }

						            // Error message
						            if ($hook_result['error']) {
										$fw_error_msg['notifications'][] = $formText_ProjectCodeWasNotSyncedWithAccountingSystemYouCanTrySyncingLater_output;
						            } else {
									    $o_query = $o_main->db->query("SELECT * FROM projectforaccounting WHERE id = ?", array($projectforaccountingId));
									    $projectForAccounting = $o_query ? $o_query->row_array() : array();
										if(intval($projectForAccounting['external_project_id']) == 0){
											$fw_error_msg['notifications'][] = $formText_ProjectCodeWasNotSyncedWithAccountingSystemYouCanTrySyncingLater_output;
										}
						            }
								}
							} else {
								$fw_error_msg[] = $formText_ErrorAddingProjectCodeContactSystemDeveloper_output;
								return;
							}
						} else {
							$fw_error_msg[] = $formText_ProjectWithProjectCodeExistsTryAgain_output;
							return;
						}
					} else {
						$fw_error_msg[] = $formText_MissingProjectCode_output;
						return;
					}
				}
    			$s_sql = "INSERT INTO subscriptionmulti SET
                created = now(),
                createdBy= ?,
                subscriptionName= ?,
                startDate= ?,
                stoppedDate= ?,
                nextRenewalDate= ?,
                customerId= ?,
                ownercompany_id= ?,
                subscriptiontype_id= ?,
                periodNumberOfMonths= ?,
                vat_free_contract = ?,
                workgroupId = ?,
                ".(isset($_POST['projectCode']) ? "projectId = '".$o_main->db->escape_str($_POST['projectCode'])."'," : '')."
                freeNoBilling = ?,
                extraCheckbox = ?,
                departmentCode = ?,
                placeSubscriptionNameInInvoiceLine = ?,
                onhold = ?,
				renewalappearance = 0,
				renewalappearance_daynumber= 0,
				invoicedate_suggestion= 0,
				invoicedate_daynumber= 0,
				duedate= 0,
				duedate_daynumber= 0,
				connectedCustomerId = ?,
				subscriptionsubtypeId = ?,
				invoice_to_other_customer_id = ?,
				customer_subunit_id = ?,
				original_start_date = '".unformatDate($originalDate)."',
	            seller_people_id = '".$o_main->db->escape_str($_POST['seller_people_id'])."',
				estimated_sales_value = '".$o_main->db->escape_str(str_replace(" ", "", str_replace(",", ".", $_POST['estimated_sales_value'])))."'";
                $o_main->db->query($s_sql, array($variables->loggID, $_POST['subscriptionName'], unformatDate($_POST['startDate']), unformatDate($_POST['stoppedDate'], true), unformatDate($_POST['nextRenewalDate']),
                $_POST['customerId'], $_POST['ownercompany_id'], $_POST['subscriptiontype_id'], $_POST['periodNumberOfMonths'], $_POST['vatFreeContract'], $_POST['workgroup'],
                $_POST['freeNoBilling'], $_POST['extraCheckbox'], $_POST['departmentCode'], $_POST['placeSubscriptionNameInInvoiceLine'], $_POST['onhold'], $_POST['connectedCustomerId'], $_POST['subscriptionsubtypeId'.$subscriptionType['id']], $_POST['invoiceCustomerId'], $_POST['subunit_id']));
                $fw_return_data = $s_sql;
                $fw_redirect_url = $_POST['redirect_url'];
                $insertId = $subscribtionId = $o_main->db->insert_id();

                $s_sql = "SELECT * FROM default_repeatingorder_invoicedate_settings ORDER BY sortnr ASC";
                $o_query = $o_main->db->query($s_sql);
                $default_repeatingorder_invoicedate_settings = $o_query ? $o_query->row_array() : array();
                if ($default_repeatingorder_invoicedate_settings && $insertId) {
					$s_sql = "SELECT * FROM subscriptiontype WHERE id = ?";
                    $o_query = $o_main->db->query($s_sql, array($_POST['subscriptiontype_id']));
                    $subscriptionType = ($o_query ? $o_query->row_array():array());
					if(!$subscriptionType['subscription_category'] == 1) {
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
	                    $o_main->db->query($s_sql, array($variables->loggID, $default_repeatingorder_invoicedate_settings['renewalappearance'], $default_repeatingorder_invoicedate_settings['renewalappearance_daynumber'], $default_repeatingorder_invoicedate_settings['invoicedate_suggestion'], $default_repeatingorder_invoicedate_settings['invoicedate_daynumber'],  $default_repeatingorder_invoicedate_settings['duedate'],  $default_repeatingorder_invoicedate_settings['duedate_daynumber'], $insertId));
					}
                }

                $customerId = $_POST['customerId'];

                // require_once __DIR__ . '/filearchive_functions.php';
                // create_customer_folders($o_main, $customerId);
    		}

			if($subscribtionId > 0){
				$fw_return_data = $subscribtionId;
				$s_sql = "DELETE FROM subscriptionmulti_date_for_invoicing WHERE subscriptionmulti_id = ".$subscribtionId."";
				$o_query = $o_main->db->query($s_sql);

				if(intval($_POST['activeSubmember']) == 0){
					foreach($_POST['dates_for_invoicing'] as $date_for_invoicing){
						if($date_for_invoicing != ""){
							$s_sql = "INSERT INTO subscriptionmulti_date_for_invoicing SET created = NOW(), createdBy = '".$variables->loggID."', subscriptionmulti_id = '".$subscribtionId."', date = '".date("Y-m-d", strtotime($date_for_invoicing.".2020"))."'";
							$o_query = $o_main->db->query($s_sql);
						}
					}
				}
				if(intval($_POST['contactPersonRoleType']) == 0) {
					$s_sql = "SELECT * FROM contactperson_role_conn WHERE subscriptionmulti_id = ? and contactperson_id = ?";
	                $o_query = $o_main->db->query($s_sql, array($subscribtionId, $_POST['contactPerson']));
	                $contactperson_conn = $o_query ? $o_query->row_array() : array();
					if($contactperson_conn){
						$s_sql = "UPDATE contactperson_role_conn SET
		                updated = now(),
		                updatedBy= ?,
		                subscriptionmulti_id = ?,
						contactperson_id = ?,
						role = 0
						WHERE id = ?";
						$o_main->db->query($s_sql, array($variables->loggID, $subscribtionId, $_POST['contactPerson'], $contactperson_conn['id']));
						$contactperson_conn_id = $contactperson_conn['id'];
					} else {
						$s_sql = "INSERT INTO contactperson_role_conn SET
		                created = now(),
		                createdBy= ?,
		                subscriptionmulti_id = ?,
						contactperson_id = ?,
						role = 0";
						$o_main->db->query($s_sql, array($variables->loggID, $subscribtionId, $_POST['contactPerson']));
						$contactperson_conn_id = $o_main->db->insert_id();
					}
					if($contactperson_conn_id > 0) {
						$s_sql = "DELETE FROM contactperson_role_conn WHERE subscriptionmulti_id = ? AND id <> ?";
						$o_query = $o_main->db->query($s_sql, array($subscribtionId, $contactperson_conn_id));
					}
				} else if($_POST['contactPersonRoleType'] == 1){
					if($customer_basisconfig['rolesAvailableForContactperson'] != "") {
						$roles = explode(",", str_replace(" ", "",$customer_basisconfig['rolesAvailableForContactperson']));
						$roleNames = array('', $formText_InvoiceReferencePerson_output, $formText_ReceiverOfReports_output, $formText_ContactPersonForPerformer_output);

						$s_sql = "SELECT * FROM contactperson_role_conn WHERE subscriptionmulti_id = ?";
						$o_query = $o_main->db->query($s_sql, array($subscribtionId));
						$contactperson_conns = $o_query ? $o_query->result_array() : array();
						$updatedConnectionIds = array();
						foreach($roles as $role) {
							foreach($_POST['role'.$role] as $contactpersonId){
								if($contactpersonId > 0){
									$s_sql = "SELECT * FROM contactperson_role_conn WHERE subscriptionmulti_id = ? AND contactperson_id = ? AND role = ?";
					                $o_query = $o_main->db->query($s_sql, array($subscribtionId, $contactpersonId, $role));
					                $contactperson_conn = $o_query ? $o_query->row_array() : array();
									if($contactperson_conn){
										$s_sql = "UPDATE contactperson_role_conn SET
						                updated = now(),
						                updatedBy= ?,
						                subscriptionmulti_id = ?,
										contactperson_id = ?,
										role = ?
										WHERE id = ?";
										$o_query = $o_main->db->query($s_sql, array($variables->loggID, $subscribtionId, $contactpersonId, $role, $contactperson_conn['id']));
										if($o_query){
											$updatedConnectionIds[] = $contactperson_conn['id'];
										}
									} else {
										$s_sql = "INSERT INTO contactperson_role_conn SET
						                created = now(),
						                createdBy= ?,
						                subscriptionmulti_id = ?,
										contactperson_id = ?,
										role = ?";
										$o_query = $o_main->db->query($s_sql, array($variables->loggID, $subscribtionId, $contactpersonId, $role));
										if($o_query){
											$updatedConnectionIds[] = $o_main->db->insert_id();
										}
									}
								}
							}
						}
						if(count($updatedConnectionIds) > 0) {
							$s_sql = "DELETE FROM contactperson_role_conn WHERE subscriptionmulti_id = ? AND id NOT IN (".implode(',', $updatedConnectionIds).")";
							$o_query = $o_main->db->query($s_sql, array($subscribtionId));
						} else {
							$s_sql = "DELETE FROM contactperson_role_conn WHERE subscriptionmulti_id = ?";
							$o_query = $o_main->db->query($s_sql, array($subscribtionId));
						}
					}
				}
			}
			if($v_repeatingorder_accountconfig['activate_syncing_of_project'] && $v_repeatingorder_accountconfig['path_syncing_of_project'] != ""){
				$sql = "SELECT * FROM subscriptionmulti WHERE id = $fw_return_data";
				$o_query = $o_main->db->query($sql);
				$projectData = $o_query ? $o_query->row_array() : array();

				$hook_params = array(
					'subscription_id' => $projectData['id'],
					'ownercompany_id' => 1
				);
				$hook_file = __DIR__ . '/../../../' . $v_repeatingorder_accountconfig['path_syncing_of_project'];
				if (file_exists($hook_file)) {
					require_once $hook_file;
					if (is_callable($run_hook)) {
						$hook_result = $run_hook($hook_params);
						unset($run_hook);
					}
				}

				// Error message
				if ($hook_result['error']) {
					$noError = false;
					$integrationHookError = true;
					$integrationHookErrorMessage = $hook_result['message'];
				} else {

				}
			}

            if(isset($v_customer_accountconfig['activate_account_connection']) && 1 == $v_customer_accountconfig['activate_account_connection'])
    		{
    			$o_main->db->query("UPDATE subscriptionmulti_accounts SET content_status = 1 WHERE subscriptionmulti_id = '".$o_main->db->escape_str($subscribtionId)."'");
    			foreach($_POST['connected_account'] as $s_account)
    			{
    				if('' != $s_account)
					{
						$o_find = $o_main->db->query("SELECT id FROM subscriptionmulti_accounts WHERE subscriptionmulti_id = '".$o_main->db->escape_str($subscribtionId)."' AND accountname = '".$o_main->db->escape_str($s_account)."'");
						if($o_find && $o_find->num_rows()>0)
						{
							$o_main->db->query("UPDATE subscriptionmulti_accounts SET content_status = 0 WHERE subscriptionmulti_id = '".$o_main->db->escape_str($subscribtionId)."' AND accountname = '".$o_main->db->escape_str($s_account)."'");
						} else {
							$o_main->db->query("INSERT INTO subscriptionmulti_accounts SET subscriptionmulti_id = '".$o_main->db->escape_str($subscribtionId)."', accountname = '".$o_main->db->escape_str($s_account)."'");
						}
					}
    			}
    		}
        } else {
            $fw_error_msg = "delete_workplanlines";
            $first_workplanline = $workplanlines[0];
            $last_workplanline = $workplanlines[count($workplanlines) - 1];
            $fw_return_data = $formText_ThereAreWorkplanlinesAfterStoppedDate_output ." (".count($workplanlines).") <br/>"
            .$formText_First_output.": ".date("d.m.Y", strtotime($first_workplanline['date']))." ".$first_workplanline['name']." ".$first_workplanline['middlename']." ".$first_workplanline['lastname']." <br/>"
            .$formText_Last_output.": ".date("d.m.Y", strtotime($last_workplanline['date']))." ".$last_workplanline['name']." ".$last_workplanline['middlename']." ".$last_workplanline['lastname'];
            return;
        }
	}
}

if($action == "syncProjectCode") {
	$projectforaccountingId =  $_POST['accountingprojectId'];
	if($projectforaccountingId > 0) {
		if($customer_basisconfig['activateProjectConnection'] == 5 && $v_customer_accountconfig['project_code_syncing_path'] != "") {
			$hook_params = array(
				'ownercompany_id' => 1,
				'projectforaccountingId'=>$projectforaccountingId
			);

			$hook_file = __DIR__ . '/../../../../' . $v_customer_accountconfig['project_code_syncing_path'];
			if (file_exists($hook_file)) {
				require_once $hook_file;
				if (is_callable($run_hook)) {
					$hook_result = $run_hook($hook_params);
					unset($run_hook);
				}
			}

			// Error message
			if ($hook_result['error']) {
				$fw_error_msg[] = $formText_ProjectCodeWasNotSyncedWithAccountingSystemYouCanTrySyncingLater_output;
			} else {
				$o_query = $o_main->db->query("SELECT * FROM projectforaccounting WHERE id = ?", array($projectforaccountingId));
				$projectForAccounting = $o_query ? $o_query->row_array() : array();
				if(intval($projectForAccounting['external_project_id']) == 0){
					$fw_error_msg[] = $formText_ProjectCodeWasNotSyncedWithAccountingSystemYouCanTrySyncingLater_output;
				} else {
					$fw_return_data = $projectforaccountingId;
				}
			}
		}
	}
	return;
}
if ($action == 'deleteSubscription' && $moduleAccesslevel > 110) {

	$canBeDeleted = true;
	$s_sql = "SELECT * FROM workplanlineworker WHERE workplanlineworker.repeatingOrderId = ?";
	$o_query = $o_main->db->query($s_sql, array($subscribtionId));
	$workplanlineWorkers = ($o_query ? $o_query->result_array() : array());
	if(count($workplanlineWorkers) > 0) {
		$canBeDeleted = false;
	}
	$s_sql = "SELECT * FROM orders WHERE orders.subscribtionId = ?";
	$o_query = $o_main->db->query($s_sql, array($subscribtionId));
	$orders = ($o_query ? $o_query->result_array() : array());
	if(count($orders) > 0) {
		$canBeDeleted = false;
	}
	if($canBeDeleted){
	    $sql = "UPDATE subscriptionmulti SET content_status = 2 WHERE id = ?";
	    $o_main->db->query($sql, array($subscribtionId));
	} else {
		echo $formText_CanNotDeleteSubscriptionWithWorkplanlines_output;
	}
	return;
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
$s_sql = "SELECT * FROM customer WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($subscribtionData['connectedCustomerId']));
$connectedCustomer = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT customer.*, cei.external_id FROM customer
LEFT OUTER JOIN customer_externalsystem_id cei ON cei.customer_id = customer.id WHERE customer.id = ?";
$o_query = $o_main->db->query($s_sql, array($subscribtionData['invoice_to_other_customer_id']));
$invoiceCustomer = $o_query ? $o_query->row_array() : array();

function formatDate($date) {
    global $customer_basisconfig;
    global $formText_NotSet_output;
    $monthType = $customer_basisconfig['activateUseMonthOnSubscriptionPeriods'];
    if ($date == '0000-00-00' || !$date || empty($date)) return '';
    if($monthType){
        return date('m.Y', strtotime($date));
    } else {
        return date('d.m.Y', strtotime($date));
    }
}

function unformatDate($date, $lastDay = false) {
    global $customer_basisconfig;
    $monthType = $customer_basisconfig['activateUseMonthOnSubscriptionPeriods'];
    if ($date == '0000-00-00' || !$date || empty($date)) return '';
    $d = explode('.', $date);
    if($monthType){
        if($lastDay){
            return date("Y-m-t", strtotime($d[1].'-'.$d[0].'-01'));
        } else {
            return $d[1].'-'.$d[0].'-01';
        }
    } else {
        return $d[2].'-'.$d[1].'-'.$d[0];
    }
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
$b_activate_accounting_project = ($customer_basisconfig['activeAccountingProjectOnOrder'] && !$customer_basisconfig['activateProjectConnection']) || in_array($customer_basisconfig['activateProjectConnection'], array(1,3,4,5));

?>

<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form output-worker-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editSubscribtionDetail";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="subscribtionId" value="<?php echo $subscribtionId;?>">
		<input type="hidden" name="customerId" value="<?php echo $customerId;?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customerId; ?>">
		<div class="inner">

            <?php if(count($ownercompanies) > 1){ ?>
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_ChooseOwnerCompany_Output; ?></div>
                    <div class="lineInput">
                    <select name="ownercompany_id" class="buildingOwner" required>
                        <option value=""><?php echo $formText_Select_output;?></option>
                        <?php foreach ($ownercompanies as $ownercompany): ?>
                            <option value="<?php echo $ownercompany['id']; ?>" <?php echo $subscribtionData['ownercompany_id'] == $ownercompany['id'] ? 'selected="selected"' : ''; ?>><?php echo $ownercompany['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    </div>
                    <div class="clear"></div>
                </div>
            <?php } else if(count($ownercompanies) == 1) {  ?>
                <input type="hidden" value="<?php echo $ownercompanies[0]['id']?>" name="ownercompany_id"  class="buildingOwner"/>
            <?php } ?>


    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_SubscriptionName2_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="subscriptionName" value="<?php if($subscribtionData['subscriptionName'] != ""){ echo $subscribtionData['subscriptionName']; } else { echo $customer['name'];}?>" autocomplete="off">
                </div>
        		<div class="clear"></div>
    		</div>

            <div class="line contactpersonWrapper">
                <div class="lineTitle">
					<span class="defaultContactpersonSpan"><?php echo $formText_ContactPerson_Output; ?></span>
					<span class="personInMembershipSpan"><?php echo $formText_PersonInMembership_Output; ?></span>
				</div>
                <div class="lineInput">
                    <?php
                    $resources = array();

                    $s_sql = "SELECT * FROM contactperson WHERE content_status < 2 AND customerId =  ? ORDER BY name ASC";
                    $o_query = $o_main->db->query($s_sql, array($customerId));
                    if($o_query && $o_query->num_rows()>0) {
                        $resources = $o_query->result_array();
                    }
					$s_sql = "SELECT contactperson.* FROM contactperson_role_conn
					LEFT OUTER JOIN contactperson ON contactperson.id = contactperson_role_conn.contactperson_id
					WHERE contactperson_role_conn.subscriptionmulti_id = ? AND (contactperson_role_conn.role = 0 OR contactperson_role_conn.role is null OR contactperson_role_conn.role = 1)
					ORDER BY contactperson_role_conn.role DESC";
					$o_query = $o_main->db->query($s_sql, array($subscribtionData['id']));
					$contactPerson = $o_query ? $o_query->row_array() : array();

                    ?>
                    <select class="singleContactPersonSelect contactPerson contactPersonSelect" name="contactPerson">
                        <option value=""><?php echo $formText_None_output;?></option>
                        <?php foreach($resources as $resource) { ?>
                        <option value="<?php echo $resource['id']?>" <?php if($contactPerson['id'] == $resource['id']) echo 'selected';?>><?php echo $resource['name']." ".$resource['middlename']." ".$resource['lastname']?></option>
                        <?php } ?>
    					<option value="-1" class="createNewOption"><?php echo $formText_CreateNew_output;?></option>
                    </select>
					<?php
					if($customer_basisconfig['rolesAvailableForContactperson'] != ""){
						$roles = explode(",", str_replace(" ", "",$customer_basisconfig['rolesAvailableForContactperson']));
						$roleNames = array('', $formText_InvoiceReferencePerson_output, $formText_ReceiverOfReports_output, $formText_ContactPersonForPerformer_output);
						if(count($roles) > 0){
							$roleWidth = floor(100/count($roles));

							$s_sql = "SELECT * FROM contactperson_role_conn WHERE subscriptionmulti_id = ? AND role <> 0";
							$o_query = $o_main->db->query($s_sql, array($subscribtionId));
							$contactperson_conn = $o_query ? $o_query->result_array() : array();
							?>
							<div class="define_multiple_contactpersons"><?php echo $formText_DefineMultipleContactPersons_output;?></div>
							<div class="multiple_contactpersons_wrapper">
								<div class="popupform contactperson_conn_wrap">
									<div class="inner">
										<div class="popupformTitle"><?php echo $formText_SelectContactPerson_output;?></div>
										<div class="line">
											<div class="lineTitle"><?php echo $formText_ContactPersonType; ?></div>
											<div class="lineInput">
												<input type="radio" id="singlecp_rad" name="contactPersonRoleType" class="contactPersonRoleTypeSelect" value="0" checked/>
    											<label for="singlecp_rad"><?php echo $formText_Single_output;?></label>
    											<div class="cp_radio_row">
	    											<input type="radio" id="multicp_rad" name="contactPersonRoleType" class="contactPersonRoleTypeSelect" value="1" <?php if(count($contactperson_conn) > 0) echo 'checked';?>/>
	    											<label for="multicp_rad"><?php echo $formText_Multiple_output;?></label>
												</div>
											</div>
											<div class="clear"></div>
										</div>
    									<div class="multicp_wrapper">
											<div class="line">
												<div class="lineTitle"><?php echo $formText_ContactPersonName; ?></div>
												<div class="lineInput">
													<table width="100%">
														<tr>
															<?php foreach($roles as $role) { ?>
																<th width="<?php echo $roleWidth?>%"><?php echo $roleNames[$role];?></th>
															<?php } ?>
														</tr>
													</table>
												</div>
												<div class="clear"></div>
											</div>
											<?php foreach($resources as $resource) { ?>
												<div class="line multicp_<?php echo $resource['id'];?>">
									        		<div class="lineTitle"><?php echo $resource['name']." ".$resource['middlename']." ".$resource['lastname']; ?></div>
									        		<div class="lineInput">
														<table width="100%">
															<tr>
															<?php foreach($roles as $role) {
																$s_sql = "SELECT * FROM contactperson_role_conn WHERE subscriptionmulti_id = ? AND contactperson_id = ? AND role = ?";
																$o_query = $o_main->db->query($s_sql, array($subscribtionId, $resource['id'], $role));
																$contactperson_conn = $o_query ? $o_query->row_array() : array();
																?>
																<td width="<?php echo $roleWidth?>%"><input class="contactPersonRole contactPersonRole<?php echo $role; ?>" <?php if($contactperson_conn) echo 'checked';?> type="checkbox" name="role<?php echo $role?>[]" value="<?php echo $resource['id'];?>"/></td>
															<?php } ?>
															</tr>
														</table>
													</div>
									        		<div class="clear"></div>
									    		</div>
											<?php } ?>
										</div>

    									<div class="createNewContactPerson"><?php echo $formText_CreateNewContactPerson_output;?></div>
    									<div class="singlecp_wrapper">
    										<?php echo $formText_CloseThePopupToChooseContactPersonInDropdownInRegularWay_output;?>
    									</div>

    									<div class="popupformbtn">
    										<button type="button" class="output-btn b-large b-close"><?php echo $formText_Close_Output;?></button>
    									</div>
									</div>
									<script type="text/javascript">
									$(function(){
										$(".contactPersonRole1").off("click").on("click", function(){
    										if($(this).is(":checked")){
    											$(".contactPersonRole1").prop("checked", false);
    											$(this).prop("checked", true);
    										} else {
    											$(".contactPersonRole1").prop("checked", false);
    											$(this).prop("checked", false);
    										}
    									})
    									$(".contactPersonRole3").off("click").on("click", function(){
    										if($(this).is(":checked")){
    											$(".contactPersonRole3").prop("checked", false);
    											$(this).prop("checked", true);
    										} else {
    											$(".contactPersonRole3").prop("checked", false);
    											$(this).prop("checked", false);
    										}
    									})
    									$("#singlecp_rad").off("click").on("click", function(){
    										$(".multicp_wrapper").hide();
    										$(".singlecp_wrapper").show();
    									})
    									$("#multicp_rad").off("click").on("click", function(){
    										$(".multicp_wrapper").show();
    										$(".singlecp_wrapper").hide();
    									})
									})
									</script>
								</div>
							</div>
							<?php
						}
					}
					?>
					<span class="maincontact_oninvoices_text"><?php echo $formText_MainContactWillBeUsedOnInvoices_output;?></span>
                </div>
                <div class="clear"></div>
            </div>

            <?php
			$s_sql = "SELECT * FROM contactperson  WHERE contactperson.id = ?";
			$o_query = $o_main->db->query($s_sql, array($subscribtionData['seller_people_id']));
			$seller = ($o_query ? $o_query->row_array() : array());
			if($v_repeatingorder_basisconfig['activate_seller'] > 0 || $seller) { ?>
				<div class="line ">
					<div class="lineTitle"><?php echo $formText_Seller_Output; ?></div>
					<div class="lineInput">
						<?php
						if($seller) { ?>
						<a href="#" class="selectEmployee"><?php echo $seller['name']." ".$seller['middlename']." ".$seller['lastname'];?></a>
						<?php } else { ?>
						<a href="#" class="selectEmployee"><?php echo $formText_SelectSeller_Output;?></a>
						<?php } ?>
						<?php if($v_repeatingorder_basisconfig['activate_seller'] < 2) { ?>
							<a href="#" class="resetSeller"><?php echo $formText_ResetSeller_output;?></a>
						<?php } ?>
						<input type="hidden" name="seller_people_id" id="employeeId2" value="<?php print $seller['id'];?>" <?php if($v_repeatingorder_basisconfig['activate_seller'] ==  2) { echo 'required';}?>>
						<div class="estimated_sales_value">
							<label><?php echo $formText_EstimatedSalesValue_output;?></label>
							<input type="text" name="estimated_sales_value" class="popupforminput botspace" value="<?php echo number_format($subscribtionData['estimated_sales_value'], 2, ",", "");?>"/>
						</div>
					</div>
					<div class="clear"></div>
				</div>
			<?php } ?>
			<?php
			if($customer_basisconfig['rolesAvailableForContactperson'] != ""){
				?>
				<div class="multipleContactPersonWrap">
					<?php
					foreach($roles as $role) {
					?>
					<div class="line">
						<div class="lineTitle"><?php echo $roleNames[$role]; ?></div>
						<div class="lineInput roleCpWrapper<?php echo $role;?>">

						</div>
						<div class="clear"></div>
					</div>
					<?php
					}
					?>
				</div>
				<?php
			}
			?>
			<div class="line">
                <div class="lineTitle"><?php echo $formText_SubscriptionType_Output; ?></div>
                <div class="lineInput">
                <select name="subscriptiontype_id" class="subscriptionType" required>
                    <option value=""><?php echo $formText_Select_output;?></option>
                    <?php
					if(count($subscriptiontypes) == 1){
						$subscribtionData['subscriptiontype_id'] = $subscriptiontypes[0]['id'];
					}
					foreach ($subscriptiontypes as $subscriptiontype):
                        if($subscribtionData['subscriptiontype_id'] == $subscriptiontype['id'] ){
                            $currentSubscriptionType = $subscriptiontype;
                        }
                        ?>
						<option data-personinmembership="<?php echo $subscriptiontype['activatePersonalSubscriptionConnection']?>" data-usemaincontact="<?php echo $subscriptiontype['useMainContactAsContactperson']?>" data-hide_contactperson="<?php echo $subscriptiontype['hide_contactperson']?>"  data-activate_invoice_to_other="<?php echo $subscriptiontype['activateSubscriptionInvoiceToOtherCustomer']?>"
							data-categorytype="<?php echo $subscriptiontype['subscription_category'];?>" data-activate_submember="<?php if($subscriptiontype['activateSubmemberWithoutInvoicing']){ echo 1;} else {echo 0;}?>"
							value="<?php echo $subscriptiontype['id']; ?>" <?php echo $subscribtionData['subscriptiontype_id'] == $subscriptiontype['id'] ? 'selected="selected"' : ''; ?>
							data-subscription-periodunit="<?php echo $subscriptiontype['periodUnit']?>" data-activate_personal_subscription_connection="<?php echo $subscriptiontype['activatePersonalSubscriptionConnection'];?>">

							<?php echo $subscriptiontype['name']; ?>
						</option>
                    <?php endforeach; ?>
                </select>

				<span class="activateSubmemberWithoutInvoicing">
					<input type="checkbox" class="activateSubmemberWithoutInvoicingChanger" autocomplete="off" name="activeSubmember" id="activateSubmemberWithoutInvoicing" <?php if($connectedCustomer) echo 'checked';?> value="1"/> <label for="activateSubmemberWithoutInvoicing"><?php echo $formText_ActiveSubmember_output;?></label>
				</span>
				</div>
                <div class="clear"></div>
            </div>
			<?php
			foreach ($subscriptiontypes as $subscriptiontype) {
				$s_sql = "SELECT * FROM subscriptiontype_subtype WHERE subscriptiontype_id = ?";
				$o_query = $o_main->db->query($s_sql, array($subscriptiontype['id']));
				$subSubscriptiontypes = ($o_query ? $o_query->result_array() : array());
				if(count($subSubscriptiontypes) > 0){
					?>
					<div class="line subSubscriptionType subSubscriptionType<?php echo $subscriptiontype['id'];?>">
		                <div class="lineTitle"><?php echo $formText_SubscriptionSubType_Output; ?></div>
		                <div class="lineInput">
							<select name="subscriptionsubtypeId<?php echo $subscriptiontype['id']?>" class="subscriptionSubtype" autocomplete="off">
			                    <option value=""><?php echo $formText_Select_output;?></option>
								<?php
								foreach($subSubscriptiontypes as $subSubscriptiontype) {
									?>
				                    <option <?php if($subSubscriptiontype['id'] == $subscribtionData['subscriptionsubtypeId']) echo 'selected'; ?> data-free="<?php if($subSubscriptiontype['is_free'] || $subSubscriptiontype['type'] == 4) echo 1;?>" value="<?php echo $subSubscriptiontype['id'];?>"><?php echo $subSubscriptiontype['name'];?></option>
									<?php
								}
								?>
							</select>
						</div>
		                <div class="clear"></div>
					</div>
					<?php
				}
			}

			foreach ($subscriptiontypes as $subscriptiontype) {
				$s_sql = "SELECT subscriptiontype_selfdefined_connection.*, customer_selfdefined_fields.* FROM subscriptiontype_selfdefined_connection
				LEFT OUTER JOIN customer_selfdefined_fields ON customer_selfdefined_fields.id = subscriptiontype_selfdefined_connection.selfdefinedfield_id
				WHERE subscriptiontype_selfdefined_connection.subscriptiontype_id = ? ORDER BY customer_selfdefined_fields.name ASC";
				$o_query = $o_main->db->query($s_sql, array($subscriptiontype['id']));
				$selfdefinedFields = $o_query ? $o_query->result_array() : array();
				if(count($selfdefinedFields) > 0) {
				?>
				<div class="line subscriptionTypeLine subscriptionTypeLine<?php echo $subscriptiontype['id']?>">
					 <div class="lineTitle"><?php echo $formText_SelfdefinedFields_Output; ?></div>
					 <div class="lineInput">
						<table class="table">
						<?php
						foreach($selfdefinedFields as $selfdefinedField) {

							$predefinedFieldValue = null;
							$s_sql = "SELECT * FROM customer_selfdefined_values WHERE customer_id = ? AND selfdefined_fields_id = ?";
							$o_query = $o_main->db->query($s_sql, array($customerId, $selfdefinedField['id']));
							if($o_query && $o_query->num_rows()>0){
								$predefinedFieldValue = $o_query->row_array();
							}
							$selfdefinedList = null;
							$s_sql = "SELECT * FROM customer_selfdefined_lists WHERE id = ?";
							$o_query = $o_main->db->query($s_sql, array($selfdefinedField['list_id']));
							if($o_query && $o_query->num_rows()>0){
								$selfdefinedList = $o_query->row_array();
							}

							$resources = array();

							$s_sql = "SELECT * FROM customer_selfdefined_list_lines WHERE list_id = ? ORDER BY name ASC";
							$o_query = $o_main->db->query($s_sql, array($selfdefinedList['id']));
							if($o_query && $o_query->num_rows()>0){
								$resources = $o_query->result_array();
							}
							?>
							<tr>
								<?php if($selfdefinedField['type'] == 1) { ?>
									<td class="txt-label bold" colspan="2" style="width:20%; padding: 6px 10px;"><?php echo $selfdefinedField['name']?></td>
									<td class="txt-value" style=" padding: 6px 10px;">
										<input type="hidden" class="selfdefinedCheckbox" value="1" <?php if($predefinedFieldValue['active']) echo 'checked="checked"';?> id="selfdefinedCheckbox<?php echo $selfdefinedField['id']?>" data-selfdefinedfieldid="<?php echo $selfdefinedField['id'];?>" data-customerid="<?php echo $customerId?>"
										<?php if($moduleAccesslevel <= 10) { ?> disabled<?php } ?>
										/>
										<?php if($moduleAccesslevel > 10) { ?>
											<select class="selfdefinedFieldValue selfdefinedDropdown" name="selfdefinedfieldValues[<?php echo $subscriptiontype['id']?>][<?php echo $selfdefinedField['id']?>]">
												<option value=""><?php echo $formText_Select_output; ?></option>
												<?php foreach($resources as $resource) { ?>
													<option value="<?php echo $resource['id']; ?>" <?php echo $resource['id'] == $predefinedFieldValue['value'] ? 'selected="selected"' : ''; ?>><?php echo $resource['name']; ?></option>
												<?php
												}
											?>
											</select>
										<?php } else {
											$singleResource = null;
											$s_sql = "SELECT * FROM customer_selfdefined_list_lines WHERE list_id = ? AND id = ? ORDER BY name ASC";
											$o_query = $o_main->db->query($s_sql, array($selfdefinedList['id'], $predefinedFieldValue['value']));
											if($o_query && $o_query->num_rows()>0){
												$singleResource = $o_query->row_array();
											}
											echo $singleResource['name'];
											?>

										<?php } ?>
									</td>
								<?php } else if($selfdefinedField['type'] == 2) { ?>
									<td class="txt-value" style=" padding: 6px 10px;" colspan="3">
										<input type="hidden" class="selfdefinedCheckbox" value="1" <?php if($predefinedFieldValue['active']) echo 'checked="checked"';?> id="selfdefinedCheckbox<?php echo $selfdefinedField['id']?>" data-selfdefinedfieldid="<?php echo $selfdefinedField['id'];?>" data-customerid="<?php echo $customerId?>"
										<?php if($moduleAccesslevel <= 10) { ?> disabled<?php } ?>
										/>
										<div class="bold">
										<?php echo $selfdefinedField['name']?>
										</div>
										<div class="selfchkWrapper">
											<?php
											if($moduleAccesslevel > 10) {
												foreach($resources as $resource) {
													$selected = false;
													$s_sql = "SELECT * FROM customer_selfdefined_values_connection  WHERE selfdefined_value_id = ? AND selfdefined_list_line_id = ?";
													$o_query = $o_main->db->query($s_sql, array($predefinedFieldValue['id'], $resource['id']));
													if($o_query && $o_query->num_rows()>0){
														$selected = true;
													}
													?>
													<div>
														<input type="checkbox" name="selfdefinedfieldValues[<?php echo $subscriptiontype['id']?>][<?php echo $selfdefinedField['id']?>][]" <?php if($moduleAccesslevel <= 10) { ?> disabled<?php } ?> class="selfdefinedValueLineChk" value="<?php echo $resource['id'];?>" id="selfdefinedChx<?php echo $selfdefinedField['id']?>_<?php echo $resource['id']?>" <?php if($selected) { echo "checked";}?> /><label for="selfdefinedChx<?php echo $selfdefinedField['id']?>_<?php echo $resource['id']?>"></label><label class="labelText" for="selfdefinedChx<?php echo $selfdefinedField['id']?>_<?php echo $resource['id']?>"><?php echo $resource['name'];?></label>
													</div>
												<?php
												}
											}?>
										</div>
									</td>
									<?php
								} else if($selfdefinedField['type'] == 0) { ?>
									<td class="txt-value" style="width:5%; padding: 6px 10px;">
										<input type="checkbox" class="selfdefinedCheckbox" value="1" name="selfdefinedfieldCheckboxes[<?php echo $subscriptiontype['id']?>][<?php echo $selfdefinedField['id']?>]" <?php if($predefinedFieldValue['active']) echo 'checked="checked"';?> id="selfdefinedCheckbox<?php echo $selfdefinedField['id']?>" data-selfdefinedfieldid="<?php echo $selfdefinedField['id'];?>" data-customerid="<?php echo $customerId?>"
										<?php if($moduleAccesslevel <= 10) { ?> disabled<?php } ?>
										/><label for="selfdefinedCheckbox<?php echo $selfdefinedField['id']?>" style="vertical-align:middle;"></label>
									</td>
									<td class="txt-label bold" style="width:20%; padding: 6px 10px;"><?php echo $selfdefinedField['name']?></td>
									<td class="txt-value" style=" padding: 6px 10px;">
										<?php
										if($predefinedFieldValue['active']) {
											if(!$selfdefinedField['hide_textfield']) { ?>
												<input type="text" value="<?php echo $predefinedFieldValue['value'];?>" name="selfdefinedfieldValues[<?php echo $subscriptiontype['id']?>][<?php echo $selfdefinedField['id']?>]" class="selfdefinedFieldValue" autocomplete="off"/>
											<?php } else { ?>
												<?php

												$s_sql = "SELECT customer_selfdefined_lists.* FROM customer_selfdefined_lists_connection
												LEFT OUTER JOIN customer_selfdefined_lists ON customer_selfdefined_lists.id = customer_selfdefined_lists_connection.customer_selfdefined_list_id
												WHERE customer_selfdefined_field_id = ?";
												$o_query = $o_main->db->query($s_sql, array($selfdefinedField['id']));
												$selfdefinedLists = $o_query ? $o_query->result_array() : array();

												foreach($selfdefinedLists as $connection){

													$s_sql = "SELECT * FROM customer_selfdefined_values WHERE customer_id = ? AND selfdefined_fields_id = ? AND list_id = ?";
													$o_query = $o_main->db->query($s_sql, array($customerId, $selfdefinedField['id'], $connection['id']));
													if($o_query && $o_query->num_rows()>0){
														$predefinedFieldValue = $o_query->row_array();
													}

													$resources = array();

													$s_sql = "SELECT * FROM customer_selfdefined_list_lines WHERE list_id = ? ORDER BY name ASC";
													$o_query = $o_main->db->query($s_sql, array($connection['id']));
													if($o_query && $o_query->num_rows()>0){
														$resources = $o_query->result_array();
													}
												?>
													<select class="selfdefinedFieldValue selfdefinedDropdown2" name="selfdefinedfieldValues[<?php echo $subscriptiontype['id']?>][<?php echo $selfdefinedField['id']?>]">
														<option value=""><?php echo $formText_Select_output; ?></option>
														<?php foreach($resources as $resource) {
															$selected = false;
															$s_sql = "SELECT * FROM customer_selfdefined_values_connection  WHERE selfdefined_value_id = ? AND selfdefined_list_line_id = ?";
															$o_query = $o_main->db->query($s_sql, array($predefinedFieldValue['id'], $resource['id']));
															if($o_query && $o_query->num_rows()>0){
																$selected = true;
															}
														?>
															<option value="<?php echo $resource['id']; ?>" <?php echo $selected ? 'selected="selected"' : ''; ?>><?php echo $resource['name']; ?></option>
														<?php
														}
													?>
													</select>
												<?php } ?>
											<?php } ?>
										<?php } ?>
									</td>
								<?php } ?>
							</tr>
							<?php
						}

						?>
						</table>
					</div>
				</div>
				<?php
				}
			}
			?>

            <div class="line">
                <div class="lineTitle">
                    <?php
                    if($customer_basisconfig['activateUseMonthOnSubscriptionPeriods']){
                        echo $formText_StartMonth_Output;
                    } else {
                        echo $formText_StartDate_Output;
                    }?>
                </div>
                <div class="lineInput">
                    <input type="text" class="popupforminput shortinput datefield botspace startDateInput" name="startDate" value="<?php echo formatDate($subscribtionData['startDate']); ?>" autocomplete="off">
					<?php
					if($customer_basisconfig['activate_original_start_date']){
					?>
						<span class="inputInfo">
							<?php echo $formText_OriginalStartDate_output;?>: <span class="originalStartDateShow"><?php if($subscribtionData['original_start_date'] != "" && $subscribtionData['original_start_date'] != "0000-00-00" && $subscribtionData['original_start_date'] != null){ echo formatDate($subscribtionData['original_start_date']); } else { echo formatDate($subscribtionData['startDate']);}?></span>
							<span class="originalStartDateChange glyphicon glyphicon-pencil"></span>
							<span class="originalStartDateWrapper"><input type="text" class="originalStartDateInput datefield" name="original_start_date" value="<?php echo formatDate($subscribtionData['original_start_date']);?>"/></span>
						</span>
					<?php } ?>
				</div>
                <div class="clear"></div>
            </div>
            <?php if(!$subscribtionData['freeNoBilling']) {
				$subscriptionInvoiced = false;
				$nextRenewalInfo = "";

				$s_sql = "SELECT i.* FROM invoice i
				JOIN customer_collectingorder co ON co.invoiceNumber = i.id
				JOIN orders o ON o.collectingorderId = co.id
				WHERE o.subscribtionId = ?
				GROUP BY i.id ORDER BY i.invoiceDate DESC LIMIT 5";
				$o_query = $o_main->db->query($s_sql, array($subscribtionData['id']));
				$subscriptionInvoices = $o_query ? $o_query->result_array() : array();
				if(count($subscriptionInvoices) > 0){
					$subscriptionInvoiced = true;
				}
				?>
                <div class="line submemberHide nextRenewalDateWrapper">
                    <div class="lineTitle">
                        <?php
                        if($customer_basisconfig['activateUseMonthOnSubscriptionPeriods']){
							if(!$subscriptionInvoiced){
								echo $formText_FirstInvoicePeriodFromMonth_output;
							} else {
								echo $formText_nextInvoicePeriodFromMonth_output;
							}
                        } else {
							if(!$subscriptionInvoiced){
								echo $formText_FirstInvoicePeriodFromDate_output;
							} else {
								echo $formText_nextInvoicePeriodFromDate_output;
							}
                        }
						if(!$subscriptionInvoiced) {
							$nextRenewalInfo = $formText_InvoiceNeverSent_output;
						} else {
							$nextRenewalInfo = $formText_InvoiceHistory_output.'&nbsp;&nbsp;<span class="glyphicon glyphicon-info-sign hoverEye"><div class="hoverInfo">';
							$nextRenewalInfo.= '<div class="hoverLabel">'.$formText_InvoiceHistory_output.'</div><table class="table"><tr><th>'.$formText_date_output.'</th><th>'.$formText_InvoiceNumber_output.'</th></tr>';
							foreach($subscriptionInvoices as $subscriptionInvoice) {
								$nextRenewalInfo.= '<tr><td>'.date("d.m.Y",strtotime($subscriptionInvoice['invoiceDate'])).'</td><td>'.$subscriptionInvoice['id'].'</td></tr>';
							}
							$nextRenewalInfo .= '</table></div></span>';
						}

						?>
                    </div>
                    <div class="lineInput">
                        <input type="text" class="popupforminput shortinput datefield botspace nextRenewalDateInput" name="nextRenewalDate" value="<?php echo formatDate($subscribtionData['nextRenewalDate']); ?>" autocomplete="off">
						<span class="inputInfo"><?php echo $nextRenewalInfo;?></span>
					</div>
                    <div class="clear"></div>
                </div>
            <?php } ?>
            <div class="line">
                <div class="lineTitle">
                    <?php
                    if($customer_basisconfig['activateUseMonthOnSubscriptionPeriods']){
                        echo $formText_StoppedLastMonth_output;
                    } else {
                        echo $formText_StopDate_Output;
                    }?>
                </div>
                <div class="lineInput">
                    <input type="text" class="popupforminput shortinput datefield botspace" name="stoppedDate" value="<?php echo formatDate($subscribtionData['stoppedDate']); ?>" autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>

			<div class="line connectToCustomer">
				<div class="lineTitle">
					<?php
						echo $formText_ConnectToCustomer_Output;
					?>
				</div>
				<div class="lineInput">
					<?php if($connectedCustomer) { ?>
					<a href="#" class="selectCustomer"><?php echo $connectedCustomer['name']?></a>
					<?php } else { ?>
					<a href="#" class="selectCustomer"><?php echo $formText_SelectCustomer_Output;?></a>
					<?php } ?>
					<input type="hidden" name="connectedCustomerId" id="connectedCustomerId" value="<?php print $connectedCustomer['id'];?>" required />
					<span class="reset_customer"><?php echo $formText_ResetCustomer_output;?></span>
				</div>
				<div class="clear"></div>
			</div>

            <div class="line submemberHide periodLine">
                <div class="lineTitle month"><?php echo $formText_PeriodNumberOfMonths_Output; ?></div>
                <div class="lineTitle year" style="display: none;"><?php echo $formText_PeriodNumberOfYears_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="periodNumberOfMonths" value="<?php echo $subscribtionData['periodNumberOfMonths']; ?>" autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>
			<div class="line submemberHide invoicingDatesLine">
					<?php
					$s_sql = "SELECT * FROM subscriptionmulti_date_for_invoicing WHERE subscriptionmulti_id = ".$subscribtionData['id']." ORDER BY id ASC";
					$o_query = $o_main->db->query($s_sql);
					$datesForInvoicing = ($o_query ? $o_query->result_array() : array());
					$datesForInvoicingCount = count($datesForInvoicing);
					if($datesForInvoicingCount == 0){
						$datesForInvoicingCount = 1;
					}
					if(count($datesForInvoicing) > 0){
						$counterNumber = 1;
						foreach($datesForInvoicing as $dateForInvoicing){
							?>
							<div class="lineBlock">
								<div class="lineTitle"><?php echo $formText_InvoiceDate_Output; ?> (<?php echo $counterNumber;?> <?php echo $formText_Of_output; ?> <span class="totalInvoicingDates"><?php echo $datesForInvoicingCount;?></span>)</div>
								<div class="lineInput">
									<input type="text" class="popupforminput botspace datefieldNoYear" name="dates_for_invoicing[]" value="<?php echo date("d.m", strtotime($dateForInvoicing['date']));?>" autocomplete="off">
								</div>
								<div class="clear"></div>
							</div>
							<?php
							$counterNumber++;
						}
					} else {
						?>
						<div class="lineBlock">
							<div class="lineTitle"><?php echo $formText_InvoiceDate_Output; ?> (1 <?php echo $formText_Of_output; ?> <span class="totalInvoicingDates"><?php echo $datesForInvoicingCount;?></span>)</div>
							<div class="lineInput">
								<input type="text" class="popupforminput botspace datefieldNoYear" name="dates_for_invoicing[]" value="" autocomplete="off">
							</div>
							<div class="clear"></div>
						</div>
					<?php } ?>
				<div class="lineBlock">
					<div class="lineTitle">				</div>
					<div class="lineInput">
						<div class="addInvoicingDate"><?php echo $formText_AddInvoicingDate_output;?></div>
					</div>
					<div class="clear"></div>
				</div>
			</div>
			<?php if(isset($customer_accountconfig['activate_account_connection']) && 1 == $customer_accountconfig['activate_account_connection']) { ?>
                <div class="line submemberHide">
                <div class="lineTitle"><?php echo $formText_ConnectedAccount_Output; ?></div>
                <div class="lineInput">
                    <?php
                    $v_param = array
					(
						'PARTNER_ID'=>$customer_accountconfig['getynet_partner_id'],
						'PARTNER_PWD'=>$customer_accountconfig['getynet_partner_pw'],
						'COMPANY_ID'=>$customer['getynet_customer_id'],
						'SHOW_ALL_PARTNER_ACCOUNTS'=>$v_customer_accountconfig['getynet_show_all_partner_accounts'],
					);

					$s_request = APIconnectorAccount("accountlistbypartneridget", $v_accountinfo['accountname'], $v_accountinfo['password'], $v_param);
					$v_accounts = json_decode($s_request, TRUE);
					$s_sql = "SELECT * FROM subscriptionmulti_accounts WHERE subscriptionmulti_id = '".$o_main->db->escape_str($subscribtionId)."' AND content_status = 0";
					$o_query = $o_main->db->query($s_sql);
					if($o_query && $o_query->num_rows()>0)
					foreach($o_query->result_array() as $v_connected_account)
					{
						?><div>
						<select name="connected_account[]">
							<option value=""><?php echo $formText_None_output;?></option>
							<?php foreach($v_accounts as $s_key => $v_account) { ?>
							<option value="<?php echo $v_account['accountname'];?>" <?php if($v_connected_account['accountname'] == $v_account['accountname']) echo 'selected';?>><?php echo $v_account['accountname'];?></option>
							<?php } ?>
						</select>
						</div><?php
					}
                    ob_start();
					?>
                    <div>
					<select name="connected_account[]">
                        <option value=""><?php echo $formText_None_output;?></option>
                        <?php foreach($v_accounts as $s_key => $v_account) { ?>
                        <option value="<?php echo $v_account['accountname'];?>"><?php echo $v_account['accountname'];?></option>
                        <?php } ?>
                    </select>
					</div>
					<?php $s_buffer = base64_encode(ob_get_clean());?>
					<button type="button" class="connected_account_clone" data-clone="<?php echo $s_buffer;?>"><?php echo $formText_Add_Output;?></button>
                </div>
                <div class="clear"></div>
                </div>
            <?php } ?>
			<?php
			if($v_customer_accountconfig['activate_subunits']){

				$s_sql = "SELECT * FROM customer_subunit WHERE customer_id = ? AND content_status < 1";
				$o_query = $o_main->db->query($s_sql, array($customerId));
				$subunits = $o_query ? $o_query->result_array() : array();
				if(count($subunits) > 1){
					?>
					<div class="line">
	                    <div class="lineTitle"><?php echo $formText_ChooseSubunit_Output; ?></div>
	                    <div class="lineInput">
	                    <select name="subunit_id" required>
	                        <option value=""><?php echo $formText_Select_output;?></option>
	                        <?php foreach ($subunits as $subunit): ?>
	                            <option value="<?php echo $subunit['id']; ?>" <?php echo $subscribtionData['customer_subunit_id'] == $subunit['id'] ? 'selected="selected"' : ''; ?>><?php echo $subunit['name']; ?></option>
	                        <?php endforeach; ?>
	                    </select>
	                    </div>
	                    <div class="clear"></div>
	                </div>
					<?php
				} else if(count($subunits) == 1) {
					?>
					<div class="line">
	                    <div class="lineTitle"><?php echo $formText_Subunit_Output; ?></div>
	                    <div class="lineInput">
			                <input type="hidden" value="<?php echo $subunits[0]['id']?>" name="subunit_id" autocomplete="off"/>
							<?php echo $subunits[0]['name']?>
	                    </div>
	                    <div class="clear"></div>
	                </div>
					<?php
				}
				?>
				<?php
			}
			?>

            <?php if($b_activate_accounting_project) { ?>
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_Project_Output; ?></div>
                    <div class="lineInput <?php if($customer_basisconfig['activateProjectConnection'] != 4 && $customer_basisconfig['activateProjectConnection'] != 5) { ?> projectWrapper <?php } ?> <?php echo (3 == $customer_basisconfig['activateProjectConnection'] ? ' required' : '');?>">
						<?php if($customer_basisconfig['activateProjectConnection'] == 4 || $customer_basisconfig['activateProjectConnection'] == 5) {
							if($subscribtionData) {
								echo $subscribtionData['projectId'];

								$s_sql = "SELECT * FROM projectforaccounting  WHERE projectforaccounting.projectnumber = ?";
								$o_query = $o_main->db->query($s_sql, array($subscribtionData['projectId']));
								$accountingProject = ($o_query ? $o_query->row_array() : array());
								if($accountingProject && intval($accountingProject['external_sys_id']) == 0) {
									echo '<span class="sync_projectcode" data-accountingprojectid="'.$accountingProject['id'].'">'.$formText_SyncProjectCodeToAccounting_output.'</span>';
								}

							 } else { echo $formText_AutomaticallyCreatedOnCreating_output; }
							?>
							<input type="hidden" name="projectCode" id="projectCode" value="<?php print $subscribtionData['projectId'];?>"/>
							<?php
						} ?>
                    </div>
                    <div class="clear"></div>
                </div>
            <?php } ?>
            <?php
            if($v_customer_accountconfig['activateAccountingDepartmentOnSubscription'] > 1) { ?>
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_Department_Output; ?></div>
                    <div class="lineInput departmentWrapper">

                    </div>
                    <div class="clear"></div>
                </div>
            <?php } ?>

            <?php

            if($customer_basisconfig['activateWorklineOnOrder']){
                ?>
                <div class="line">
                <div class="lineTitle"><?php echo $formText_WorkGroup_Output; ?></div>
                <div class="lineInput">
                    <?php
                    $resources = array();

                    $s_sql = "SELECT * FROM workgroup WHERE content_status < 2 ORDER BY name ASC";
                    $o_query = $o_main->db->query($s_sql);
                    $resources = ($o_query ? $o_query->result_array() : array());
                    foreach($resources as $resource){
                        if($currentResourceID == null){
                            $currentResourceID = $resource['id'];
                        }
                    }
                    ?>
                    <select name="workgroup" required>
                        <option value=""><?php echo $formText_None_output;?></option>
                        <?php foreach($resources as $resource) { ?>
                        <option value="<?php echo $resource['id']?>" <?php if($subscribtionData['workgroupId'] == $resource['id']) echo 'selected';?>><?php echo $resource['name']?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="clear"></div>
                </div>
                <?php
            }
            ?>
            <div class="line submemberHide">
                <div class="lineTitle"><?php echo $formText_PlaceSubscriptionNameInInvoiceLine_Output; ?></div>
                <div class="lineInput">
                    <?php

                    $selectedOption = intval($subscribtionData['placeSubscriptionNameInInvoiceLine']);

                    ?>
                    <select name="placeSubscriptionNameInInvoiceLine" class="placeSubscriptionNameInInvoiceLine">
                        <option value="0" <?php if(0 == $selectedOption) echo 'selected';?>><?php echo $formText_UseDefault_output;?></option>
                        <option value="1" <?php if(1 == $selectedOption) echo 'selected';?>><?php echo $formText_No_output;?></option>
                        <option value="2" <?php if(2 == $selectedOption) echo 'selected';?>><?php echo $formText_Yes_output;?></option>
                    </select>
                    <?php foreach ($subscriptiontypes as $subscriptiontype) {
                        $defaultChoise = intval($subscriptiontype['default_subscriptionname_in_invoiceline']);
                        $defaultChoiseText = "";
                        if($defaultChoise == 0){
                            $defaultChoiseText = $formText_No_output;
                        } else if ($defaultChoise == 1) {
                            $defaultChoiseText = $formText_Yes_output;
                        }
                        ?>
                        <span class="defaultplaceSubscriptionNameInInvoiceLine subscriptionType<?php echo $subscriptiontype['id'];?>"> (<?php echo $defaultChoiseText;?>) </span>
                    <?php } ?>
                </div>
                <div class="clear"></div>
            </div>

            <?php if($customer_basisconfig['activateRentalUnitConnection']) { ?>
                <div class="line submemberHide">
                    <div class="lineTitle"><?php echo $formText_VatFreeContract_Output; ?></div>
                    <div class="lineInput">
                        <input type="checkbox" class="checkbox popupforminput botspace" name="vatFreeContract" value="1" <?php if($subscribtionData['vat_free_contract']) { echo 'checked'; }?>>
                    </div>
                    <div class="clear"></div>
                </div>
            <?php } ?>
            <?php if($customer_basisconfig['activateFreeNoBilling']) { ?>
                <div class="line submemberHide">
                    <div class="lineTitle"><?php echo $formText_FreeNoBilling_Output; ?></div>
                    <div class="lineInput">
                        <input type="checkbox" class="checkbox popupforminput botspace" name="freeNoBilling" value="1" <?php if($subscribtionData['freeNoBilling']) { echo 'checked'; }?>>
                    </div>
                    <div class="clear"></div>
                </div>
            <?php } ?>
            <?php if($customer_accountconfig['activateExtraCheckbox']) { ?>
                <div class="line submemberHide">
                    <div class="lineTitle"><?php echo $customer_accountconfig['extraCheckboxName']; ?></div>
                    <div class="lineInput">
                        <input type="checkbox" class="checkbox popupforminput botspace" name="extraCheckbox" value="1" <?php if($subscribtionData['extraCheckbox']) { echo 'checked'; }?>>
                    </div>
                    <div class="clear"></div>
                </div>
            <?php } ?>
            <?php if($customer_accountconfig['activateSubscriptionOnHold']) { ?>
                <div class="line submemberHide">
                    <div class="lineTitle"><?php echo $formText_OnHold_Output ?></div>
                    <div class="lineInput">
                        <input type="checkbox" class="checkbox popupforminput botspace onholdCheckbox"  name="onhold" value="1" <?php if($subscribtionData['onhold']) { echo 'checked'; }?>>
                    </div>
                    <div class="clear"></div>
                </div>
            <?php } ?>
			<div class="line invoiceToOtherCustomerWrapper">
                <div class="lineTitle">
                    <?php
                        echo $formText_InvoiceToOtherCustomer_output;
					?>
                </div>
                <div class="lineInput">
					<?php if($invoiceCustomer) { ?>
                    <a href="#" class="selectInvoiceCustomer"><?php echo $invoiceCustomer['name']. " ".$invoiceCustomer['external_id']?></a>
                    <?php } else { ?>
                    <a href="#" class="selectInvoiceCustomer"><?php echo $formText_SelectCustomer_Output;?></a>
				<?php } ?> <span class="reset_invoice_customer glyphicon glyphicon-trash" <?php if($invoiceCustomer) echo 'style="display:inline-block"';?>></span>
                    <input type="hidden" name="invoiceCustomerId" id="invoiceCustomerId" value="<?php print $invoiceCustomer['id'];?>"/>
				</div>
                <div class="clear"></div>
            </div>
            <!-- <div class="line">
                <div class="lineTitle"><?php echo $formText_Comment_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="comments" value="<?php echo $subscribtionData['comments']; ?>">
                </div>
                <div class="clear"></div>
            </div> -->
		</div>
        <div class="notification_msg"></div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
			<input type="submit" class="keep_workplanlines" name="sbmbtn3" style="display: none;" value="<?php echo $formText_KeepWorkplanlines_Output; ?>">
			<input type="submit" class="delete_workplanlines" name="sbmbtn2" style="display: none;" value="<?php echo $formText_DeleteWorkplanlines_Output; ?>">
            <input type="hidden" class="keep_workplanlines_input" name="keep_workplanlines" value="0"/>
            <input type="hidden" class="delete_workplanlines_input" name="delete_workplanlines" value="0"/>
		</div>
	</form>
</div>
<div id="popupeditbox3" class="popupeditbox">
	<span class="button b-close"><span>X</span></span>
	<div id="popupeditboxcontent3"></div>
</div>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
var out_popup3;
var out_popup_options3={
	follow: [true, true],
	followSpeed: 300,
	fadeSpeed: 0,
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function(){
		$(this).addClass('opened');
		//$(this).find('.b-close').on('click', function(){out_popup.close();});
	},
	onClose: function(){
		$(this).removeClass('opened');
		$(this).removeClass('fixedWidth');
		updateConnectionRoles();
		$('.multiple_contactpersons_wrapper').html($("#popupeditboxcontent3").contents());
        $(this).find("#popupeditboxcontent3").html("");

	}
};
function updateConnectionRoles(){
	<?php if($customer_basisconfig['rolesAvailableForContactperson'] != ""){ ?>
		<?php foreach($roles as $role) { ?>
			var contactPersonsChosenForRole<?php echo $role;?> = '';
			$(".contactperson_conn_wrap .line").each(function(){
				if($(this).find(".contactPersonRole<?php echo $role;?>").is(":checked")){
					var cpname = $(this).find(".lineTitle").html();
					if(contactPersonsChosenForRole<?php echo $role;?> == ''){
						contactPersonsChosenForRole<?php echo $role;?> += cpname;
					} else {
						contactPersonsChosenForRole<?php echo $role;?> += ', '+cpname;
					}
				}
			})
			$(".roleCpWrapper<?php echo $role;?>").html(contactPersonsChosenForRole<?php echo $role;?>);
		<?php } ?>
		if($("#singlecp_rad").is(":checked")){
			$(".singleContactPersonSelect").show();
			$(".multipleContactPersonWrap").hide();
		} else {
			$(".singleContactPersonSelect").hide();
			$(".multipleContactPersonWrap").show();
		}
	<?php } ?>
}
updateConnectionRoles();
$(document).ready(function() {
	$(".define_multiple_contactpersons").off("click").on("click", function(e){
		e.preventDefault();
		$("#multicp_rad").click();
		$('#popupeditboxcontent3').html('');
		$('#popupeditboxcontent3').html($(".multiple_contactpersons_wrapper").contents());
		out_popup3 = $('#popupeditbox3').bPopup(out_popup_options3);
		$("#popupeditbox3:not(.opened)").remove();
	})
	$(".createNewContactPerson").off("click").on("click", function(){
		var data = {
			customerId: '<?php echo $customerId?>',
            fromSubscription: 1,
			from_popup: 1
		};
		ajaxCall({module_file:'edit_contactperson', module_name: 'Customer2', module_folder: 'output'}, data, function(json) {
			$('#popupeditboxcontent2').html('');
			$('#popupeditboxcontent2').html(json.html);
			out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
			$("#popupeditbox2:not(.opened)").remove();
			$(window).resize();
		});
	})

	$(".contactPersonSelect").change(function(){
		if($(this).val() == "-1") {
			var data = {
				customerId: '<?php echo $customerId?>',
	            fromSubscription: 1,
				from_popup: 1
			};
			ajaxCall({module_file:'edit_contactperson', module_name: 'Customer2', module_folder: 'output'}, data, function(json) {
				$('#popupeditboxcontent2').html('');
				$('#popupeditboxcontent2').html(json.html);
				out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
				$("#popupeditbox2:not(.opened)").remove();
				$(window).resize();
			});
		} else {
			$(".multicp_wrapper .multicp_"+$(this).val()+" input").prop("checked", false).click();
		}
	}).change();
    $('button.connected_account_clone').off('click').on('click', function(e){
		e.preventDefault();
		$(atob($(this).data('clone'))).insertBefore($(this));
	});
    $(".delete_workplanlines").off("click").on("click", function(e){
        e.preventDefault();
        bootbox.confirm({
            message:'<?php echo $formText_ConfirmDeleteWorkplanlines_output;?>',
            buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
            callback: function(result){
                if(result)
                {
                    $(".delete_workplanlines_input").val(1);
                    $(".keep_workplanlines_input").val(0);
                    $("form.output-form").submit();
                }
            }
        }).css("z-index", "1000000");
    })
    $(".keep_workplanlines").off("click").on("click", function(e){
        e.preventDefault();
        $(".delete_workplanlines_input").val(0);
        $(".keep_workplanlines_input").val(1);
        $("form.output-form").submit();
    })
    $("form.output-form").validate({
        submitHandler: function(form) {
			$("#popup-validate-message").html("").hide();
			var contactpersonValid = true;
			var activatePersonalSubscriptionConnection = $(".subscriptionType").find("option:selected").data("activate_personal_subscription_connection");
			if(activatePersonalSubscriptionConnection) {
				contactpersonValid = false;
				if($(".contactPersonSelect").val() > 0){
					contactpersonValid = true;
				}
			}
			if(contactpersonValid){
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
								if(data.error != undefined){
									$.each(data.error, function(index, value){
										if(index == "notifications") {
							                $('#popupeditboxcontent').html('');
							                $('#popupeditboxcontent').append("<div>"+value+"</div>");
											out_popup.addClass("close-reload").data("subscription-id", data.data);
										} else {
											var _type = Array("error");
											if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
											$("#popup-validate-message").append("<div>"+value+"</div>");
										}
									});
									$("#popup-validate-message").show();
								}  else {
			                        out_popup.addClass("close-reload").data("subscription-id", data.data);
			                        out_popup.close();
								}
		                    } else if(data.error == "delete_workplanlines"){
		                        $(".popupformbtn .delete_workplanlines").show();
		                        $(".popupformbtn .keep_workplanlines").show();
		                        $(".output-form .notification_msg").html(data.data);
		                    } else if(data.error != undefined){
								$.each(data.error, function(index, value){
									if(index == "notifications") {
						                $('#popupeditboxcontent').html('');
						                $('#popupeditboxcontent').append("<div>"+value+"</div>");
										out_popup.addClass("close-reload").data("subscription-id", data.data);
									} else {
										var _type = Array("error");
										if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
										$("#popup-validate-message").append("<div>"+value+"</div>");
									}
								});
								$("#popup-validate-message").show();
							} else if(data.html != ""){
								  $('#popupeditboxcontent').html('');
								  $('#popupeditboxcontent').html(data.html);
								  out_popup = $('#popupeditbox').bPopup(out_popup_options);
								  $("#popupeditbox:not(.opened)").remove();
							  }
	                }
	            }).fail(function() {
	                $("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
	                $("#popup-validate-message").show();
	                $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
	                fw_loading_end();
	            });
			} else {
				$("#popup-validate-message").html("<?php echo $formText_PleaseSelectContactperson_Output;?>", true);
				$("#popup-validate-message").show();
			}
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

    $('.datefield').datepicker({
        firstDay: 1,
        <?php if($customer_basisconfig['activateUseMonthOnSubscriptionPeriods']){ ?>
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            dateFormat: 'mm.yy',
            onClose: function(dateText, inst) {
                function isDonePressed() {
                    return ($('#ui-datepicker-div').html().indexOf('ui-datepicker-close ui-state-default ui-priority-primary ui-corner-all ui-state-hover') > -1);
                }
                if (isDonePressed()){
                    $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
                }

            }
        <?php } else { ?>
            dateFormat: 'dd.mm.yy',
        <?php } ?>
    });
	$('.datefieldNoYear').datepicker({
		firstDay: 1,
		dateFormat: 'dd.mm'
	});
	$(".subscriptionType").change(function(){
        var periodUnit = $(this).find("option:selected").data("subscription-periodunit");
        var typeCategory = $(this).find("option:selected").data("categorytype");
		var activate_submember = $(this).find("option:selected").data("activate_submember");
		var usemaincontact = $(this).find("option:selected").data("usemaincontact");
		var personInMembership = $(this).find("option:selected").data("personinmembership");
		var hide_contactperson = $(this).find("option:selected").data("hide_contactperson");
		var activateInvoiceToOtherCustomer = $(this).find("option:selected").data("activate_invoice_to_other");
		var activatePersonalSubscriptionConnection = $(this).find("option:selected").data("activate_personal_subscription_connection");
        if(periodUnit == "0"){
            $(".periodLine .month").show();
            $(".periodLine .year").hide();
        } else if(periodUnit == "1"){
            $(".periodLine .month").hide();
            $(".periodLine .year").show();
        }
        $(".placeSubscriptionNameInInvoiceLine").change();

		$(".connectToCustomer").hide();
		$(".activateSubmemberWithoutInvoicing").hide()
		if(activate_submember) {
			$(".activateSubmemberWithoutInvoicing").show();
		}
		if(typeCategory == 1){
			$(".submemberHide").show();
			$(".periodLine").hide();
			$(".invoicingDatesLine").show();
		} else {
			$(".submemberHide").show();
			$(".periodLine").show();
			$(".invoicingDatesLine").hide();
		}
		$(".subSubscriptionType").hide();
		if($(this).val() != "") {
			$(".subSubscriptionType.subSubscriptionType"+$(this).val()).show();
		}
		if(hide_contactperson){
			$(".contactpersonWrapper").hide();
		} else {
			$(".contactpersonWrapper").show();
		}
		if(usemaincontact) {
			$(".maincontact_oninvoices_text").show();
		} else {
			$(".maincontact_oninvoices_text").hide();
		}
		if(personInMembership) {
			$(".personInMembershipSpan").show();
			$(".defaultContactpersonSpan").hide();
		} else {
			$(".personInMembershipSpan").hide();
			$(".defaultContactpersonSpan").show();
		}
		if($(".activateSubmemberWithoutInvoicingChanger").is(":checked")){
			$(".submemberHide").hide();
			$(".connectToCustomer").show();
		}
		if(activateInvoiceToOtherCustomer){
			$(".invoiceToOtherCustomerWrapper input").prop("required", false);
			$(".invoiceToOtherCustomerWrapper").show();
		} else {
			$(".invoiceToOtherCustomerWrapper input").prop("required", false);
			$(".invoiceToOtherCustomerWrapper input").val("");
			$(".invoiceToOtherCustomerWrapper .selectInvoiceCustomer").html("<?php echo $formText_SelectCustomer_output;?>");

			$(".invoiceToOtherCustomerWrapper").hide();
		}

		$(".subscriptionTypeLine").hide();
		$(".subscriptionTypeLine"+$(this).val()).show();
		$(window).resize();
    })
    $(".subscriptionType").change();
	$(".subscriptionSubtype").change(function(){
		var selectedOption = $(".subscriptionSubtype option:selected");
		var is_free = selectedOption.data("free");
		if(is_free){
			$(".nextRenewalDateWrapper").hide();
		} else {
	    	$(".subscriptionType").change();
		}

	})
    $(".subscriptionSubtype").change();
	$(".activateSubmemberWithoutInvoicingChanger").change(function(){
		if($(this).is(":checked")){
			$(".submemberHide").hide();
			$(".connectToCustomer").show();
		} else {
			$(".subscriptionType").change();
		}
	}).change();
	$(".addInvoicingDate").off("click").on("click", function(){
		var totalInvoicingDates = $(".invoicingDatesLine .lineBlock").length;
		var inputHtml = '<div class="lineBlock">'+
			'<div class="lineTitle"><?php echo $formText_InvoiceDate_Output; ?> ('+totalInvoicingDates+' <?php echo $formText_Of_output; ?> <span class="totalInvoicingDates">'+totalInvoicingDates+'</span>)</div>'+
			'<div class="lineInput">'+
				'<input type="text" class="popupforminput botspace datefieldNoYear" name="dates_for_invoicing[]" value="" autocomplete="off">'+
			'</div>'+
			'<div class="clear"></div>'+
		'</div>';
		$(".totalInvoicingDates").html(totalInvoicingDates);
		$(inputHtml).insertBefore($(this).parents(".lineBlock"));

		$('.datefieldNoYear').datepicker({
			firstDay: 1,
			dateFormat: 'dd.mm'
		});
	})
    $(".placeSubscriptionNameInInvoiceLine").change(function(){
        $(".defaultplaceSubscriptionNameInInvoiceLine").hide();
        if($(this).val() == 0){
            $(".defaultplaceSubscriptionNameInInvoiceLine.subscriptionType"+$(".subscriptionType").val()).show();
        }
    })
    $(".placeSubscriptionNameInInvoiceLine").change();

	var initownerValue = $(".popupform .buildingOwner").val();
	$(".buildingOwner").change(function(){
		<?php if($b_activate_accounting_project) { ?>
		var data = {
			ownercompany_id: $(this).val(),
			projectCode: '<?php echo $subscribtionData['projectId']?>',
			<?php if(empty($subscribtionData['id']) && $v_customer_accountconfig['activate_add_accounting_project_number_on_new_subscription'] > 0) { ?>
			customer_id: '<?php echo $customerId;?>',
			<?php } ?>
		};
		ajaxCall('getProjects', data, function(json) {
			$('.projectWrapper').html(json.html);
			if($('.projectWrapper').is('.required'))
			{
				$('.projectWrapper').find('select').attr('required', true);
			}
		});
		<?php } ?>
    	<?php if($customer_accountconfig['activateAccountingDepartmentOnSubscription'] > 1) { ?>
		var data = {
			buildingOwnerId: $(this).val(),
			departmentCode: '<?php echo $subscribtionData['departmentCode']?>',
			<?php if($customer_accountconfig['activateAccountingDepartmentOnSubscription'] == 3) { ?>
				projectMandatory: 1
			<?php } ?>
		};
		ajaxCall('getAccountingDepartments', data, function(json) {
			$('.departmentWrapper').html(json.html);
		});
		<?php } ?>

		<?php  if($ownercompany_accountconfig['activate_company_product_sets']  && count($company_product_sets) > 0) { ?>
			if(initownerValue > 0 && initownerValue != $(".popupform .buildingOwner").val()){
				bootbox.alert("<?php echo $formText_SubscriptionlinesWillBeDeleted_output;?>").css("z-index", 10000);
			}
		<?php }?>
	})
	$(".buildingOwner").change();
	$(".onholdCheckbox").change(function(){
		var checked = $(this).is(":checked");
		if(checked){
			bootbox.confirm({
	            message:'<?php echo $formText_ConfirmPuttingSubscriptionOnHold_output;?>',
	            buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
	            callback: function(result){
	                if(!result)
	                {
						$(".onholdCheckbox").prop("checked", false);
	                }
	            }
	        }).css("z-index", "1000000");
		} else {
			bootbox.confirm({
				message:'<?php echo $formText_ConfirmRemovingOnholdPleaseUpdateTheNextRenewalDate_output;?>',
				buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
				callback: function(result){
					if(!result)
					{
						$(".onholdCheckbox").prop("checked", true);
					}
				}
			}).css("z-index", "1000000");
		}
	})
	$(".popupform .selectCustomer").unbind("click").bind("click", function(e){
		e.preventDefault();
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1};
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_customers";?>',
            data: _data,
            success: function(obj){
                fw_loading_end();
                $('#popupeditboxcontent2').html('');
                $('#popupeditboxcontent2').html(obj.html);
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                $("#popupeditbox2:not(.opened)").remove();
            }
        });
    })

	$(".reset_customer").off("click").on("click", function(){
		$(".selectCustomer").html("<?php echo $formText_SelectCustomer_output;?>");
		$(".contactPersonSelectWrapper").html("");
		$("#connectedCustomerId").val("");
	})
	$(".popupform .selectInvoiceCustomer").unbind("click").bind("click", function(e){
		e.preventDefault();
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1, invoice_customer:1};
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_customers";?>',
            data: _data,
            success: function(obj){
                fw_loading_end();
                $('#popupeditboxcontent2').html('');
                $('#popupeditboxcontent2').html(obj.html);
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                $("#popupeditbox2:not(.opened)").remove();
            }
        });
    })

	$(".reset_invoice_customer").off("click").on("click", function(){
		$(".selectInvoiceCustomer").html("<?php echo $formText_SelectCustomer_output;?>");
		$("#invoiceCustomerId").val("");
		$(this).hide();
	})
	$(".startDateInput").change(function(){
		if($(".nextRenewalDateInput").val() == ""){
			$(".nextRenewalDateInput").val($(this).val());
		}
		if($(".originalStartDateInput").val() == ""){
			$(".originalStartDateShow").html($(this).val());
		}
	})
	$(".nextRenewalDateInput").change(function(){
		var value = $(this).val();
		var startDateValue = $(".startDateInput").val();
		var valueArray = value.split(".");
		var startDateValueArray = startDateValue.split(".");
		var lessThanStart = 0;
		if (Date.parse(valueArray[2]+"-"+valueArray[1]+"-"+valueArray[0]) < Date.parse(startDateValueArray[2]+"-"+startDateValueArray[1]+"-"+startDateValueArray[0])) {
			lessThanStart = 1;
		}
		if(lessThanStart) {
			$(this).val("");
		}
	})
	$(".originalStartDateInput").change(function(){
		$(".originalStartDateShow").html($(".originalStartDateInput").val());
		$(".originalStartDateShow").show();
		$(".originalStartDateChange").show();
		$(".originalStartDateWrapper").hide();
	})
	$(".originalStartDateChange").off("click").on("click", function(){
		$(".originalStartDateWrapper").show();
		$(".originalStartDateShow").hide();
		$(this).hide();
	})
	$(".originalStartDateSave").off("click").on("click", function(){
		$(".originalStartDateShow").html($(".originalStartDateInput").val());
		$(".originalStartDateShow").show();
		$(".originalStartDateChange").show();
		$(".originalStartDateWrapper").hide();
	})

	$(".selectEmployee").unbind("click").bind("click", function(e){
		e.preventDefault();
		fw_loading_start();
		var _data = { fwajax: 1, fw_nocss: 1, seller: 1};
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_employees";?>',
			data: _data,
			success: function(obj){
				$('#popupeditboxcontent2').html('');
				$('#popupeditboxcontent2').html(obj.html);
				out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
				$("#popupeditbox2:not(.opened)").remove();
				fw_loading_end();
			}
		});
	})
	$(".resetSeller").on("click", function(e){
        e.preventDefault();
		$("#employeeId2").val("");
		$(".selectEmployee").html("<?php echo $formText_SelectSeller_Output;?>");
	})

	$(".sync_projectcode").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			accountingprojectId: $(this).data("accountingprojectid"),
			action: "syncProjectCode"
		};
		ajaxCall('editSubscribtionDetail', data, function(json) {
			if(json.error !== undefined)
			{
				$('#popupeditboxcontent2').html('');
				$.each(json.error, function(index, value){
	                $('#popupeditboxcontent2').append(value);
	                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
	                $("#popupeditbox2:not(.opened)").remove();
				});
				fw_click_instance = fw_changes_made = false;
			} else {
				if(json.data){
					$(".sync_projectcode").hide();
				}
			}
		});
	})
});

</script>
<style>
.maincontact_oninvoices_text {
	display: none;
}
.personInMembershipSpan {
	display: none;
}
.resetSeller {
	margin-left: 10px;
}
.popupform input.popupforminput.shortinput {
	width: 40%;
}
.reset_invoice_customer {
	cursor: pointer;
	color: #46b2e2;
	margin-left: 10px;
	display: none;
}
.invoiceToOtherCustomerWrapper {
	display: none;
}
.activateSubmemberWithoutInvoicing {
	display: none;
	margin-left: 20px;
}
.subSubscriptionType {
	display: none;
}
.addInvoicingDate {
	cursor: pointer;
	color: #46b2e2;
	margin-bottom: 10px;
}
.invoicingDatesLine {
	display: none;
}
.cp_radio_row {
	margin-top: 5px;
}
.multicp_wrapper {
	margin-top: 10px;
	margin-bottom: 10px;
}
.createNewContactPerson {
	margin: 10px 0px;
	cursor: pointer;
	color: #46b2e2;
}
.contactPersonSelect {
	margin-right: 15px;
}
.define_multiple_contactpersons {
	cursor: pointer;
	color: #46b2e2;
	display: inline-block;
}
.multiple_contactpersons_wrapper {
	display: none;
}
.multipleContactPersonWrap {
	display: inline-block;
	width: 100%;
}
.notification_msg {
    text-align: right;
    font-size: 14px;
    color: red;
}
.defaultplaceSubscriptionNameInInvoiceLine {
    display: none;
}
<?php if($customer_basisconfig['activateUseMonthOnSubscriptionPeriods']){ ?>
.ui-datepicker-calendar {
    display: none;
}
<?php } ?>
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
.connectToCustomer {
	display: none;
}
.subscriptionTypeLine {
	display: none;
}
.inputInfo {
	display: inline-block;
	vertical-align: middle;
	margin-left: 10px;
}
.originalStartDateWrapper {
	display: none;
}
.originalStartDateChange {
	cursor: pointer;
	color: #46b2e2;
}
.originalStartDateSave {
	margin-left: 5px;
	border: 1px solid #194273;
	background-color: #194273;
	color: #fff;
	padding: 2px 3px;
	border-radius: 3px;
	cursor: pointer;
}
.sync_projectcode {
	margin-left: 10px;
	cursor: pointer;
	color: #46b2e2;
}
.estimated_sales_value {
	float: right;
}
.estimated_sales_value input.popupforminput {
	padding: 2px 5px;
	width: 80px;
}
</style>
