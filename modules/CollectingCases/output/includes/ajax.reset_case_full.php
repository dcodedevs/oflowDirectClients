<?php
include_once(__DIR__."/../../../CreditorsOverview/output/includes/fnc_process_open_cases_for_tabs.php");
$s_sql = "SELECT ct.* FROM creditor_transactions ct WHERE ct.collectingcase_id = ? ";
$o_query = $o_main->db->query($s_sql, array($_POST['case_id']));
$v_row = ($o_query ? $o_query->row_array() : array());

if($v_row) {
	$s_sql = "SELECT cc.* FROM collecting_cases cc WHERE cc.id = ? ";
	$o_query = $o_main->db->query($s_sql, array($_POST['case_id']));
	$caseData = ($o_query ? $o_query->row_array() : array());

	$s_sql = "SELECT c.* FROM customer c WHERE c.id = ? ";
	$o_query = $o_main->db->query($s_sql, array($caseData['debitor_id']));
	$debitorCustomer = ($o_query ? $o_query->row_array() : array());

    $s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') AND link_id = ? AND creditor_id = ?";
    $o_query = $o_main->db->query($s_sql, array($v_row['link_id'], $v_row['creditor_id']));
    $transaction_payments = ($o_query ? $o_query->result_array() : array());

    $s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND link_id = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%_%' ORDER BY created DESC";
    $o_query = $o_main->db->query($s_sql, array($v_row['link_id'], $v_row['creditor_id']));
    $transaction_fees = ($o_query ? $o_query->result_array() : array());

	$case_profiles = array();

	$s_sql = "SELECT * FROM creditor_reminder_custom_profiles WHERE creditor_id = ? AND content_status < 2";
	$o_query = $o_main->db->query($s_sql, array($v_row['creditor_id']));
	$creditor_reminder_custom_profiles = ($o_query ? $o_query->result_array() : array());
	foreach($creditor_reminder_custom_profiles as $creditor_reminder_custom_profile) {
		$s_sql = "SELECT ccp.*, pst.name as stepTypeName FROM collecting_cases_process ccp LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id WHERE  ccp.content_status < 2 AND ccp.id = ?";
	    $o_query = $o_main->db->query($s_sql, array($creditor_reminder_custom_profile['reminder_process_id']));
	    $currentProcess = $o_query ? $o_query->row_array() : array();
		$isPersonType = 0;
		if($currentProcess['available_for'] == 1){
			$isPersonType = 1;
		}

		$showProfile = false;
		$customer_type_collect_debitor = $debitorCustomer['customer_type_collect'];
		if($debitorCustomer['customer_type_collect_addition'] > 0){
			$customer_type_collect_debitor = $debitorCustomer['customer_type_collect_addition'] - 1;
		}
		if($customer_type_collect_debitor == $isPersonType){
			$showProfile = true;
		}

		if($creditor_reminder_custom_profile['name'] == ""){
			$creditor_reminder_custom_profile['name'] = $currentProcess['fee_level_name']." ".$currentProcess['stepTypeName'];
		}
		$creditor_reminder_custom_profile['isPersonType'] = $isPersonType;
		if($showProfile){
			$case_profiles[] = $creditor_reminder_custom_profile;
		}
	}

    if($_POST['output_form_submit']) {
		$initialAmount = $v_row['amount'];
	    $amount = $initialAmount;
	    $openFeeAmount = 0;
		$mainAmountLeft = $initialAmount;
	    foreach($transaction_fees as $transaction_fee) {
	        $amount += $transaction_fee['amount'];
	        if($transaction_fee['open']) {
	            $openFeeAmount += $transaction_fee['amount'];
	        }
	    }
	    foreach($transaction_payments as $transaction_payment) {
	        $amount += $transaction_payment['amount'];
			$mainAmountLeft+=$transaction_payment['amount'];
	    }

		// if(bccomp($openFeeAmount, $amount) == 0) {
	        $s_sql = "SELECT * FROM creditor WHERE id = ?";
	        $o_query = $o_main->db->query($s_sql, array($v_row['creditor_id']));
	        $creditor = ($o_query ? $o_query->row_array() : array());
	        if($creditor) {
	            if($creditor['sync_status'] != 1) {
					$notFullData = true;
					if($_POST['profile_id'] > 0) {
						$s_sql = "SELECT creditor_reminder_custom_profiles.id FROM creditor_reminder_custom_profiles
						WHERE creditor_reminder_custom_profiles.id = '".$o_main->db->escape_str($_POST['profile_id'])."' AND creditor_reminder_custom_profiles.creditor_id = '".$o_main->db->escape_str($creditor['id'])."'";
						$o_query = $o_main->db->query($s_sql);
						$profile_exists = ($o_query ? $o_query->row_array() : array());
						if($profile_exists){
							$notFullData = false;
						}
					}
					if(!$notFullData){
						$s_sql = "INSERT INTO creditor_syncing SET created = NOW(), creditor_id = ?, started = NOW()";
						$o_query = $o_main->db->query($s_sql, array($creditor['id']));
						if($o_query){
							$creditor_syncing_id = $o_main->db->insert_id();
						}

						$currencyName = "";
						$invoiceDifferentCurrency = false;
						if($v_row['currency'] == 'LOCAL') {
							$currencyName = trim($creditor['default_currency']);
						} else {
							$currencyName = trim($v_row['currency']);
							$invoiceDifferentCurrency = true;
						}
						if($currencyName != ""){
							$currency_rate = 1;
							if($currencyName != "NOK") {
								$error_with_currency = true;

								$hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/get_currency_rates.php';
								if (file_exists($hook_file)) {
								   include $hook_file;
								   if (is_callable($run_hook)) {
										$hook_result = $run_hook(array("creditor_id"=>$creditor['id']));
										if(count($hook_result['currencyRates']) > 0){
											$currencyRates = $hook_result['currencyRates'];
											foreach($currencyRates as $currencyRate) {
												if($currencyRate['symbol'] == $currencyName) {
													$currency_rate = $currencyRate['rate'];
													$error_with_currency = false;
													break;
												}
											}
										}
								   }
							   }
							}
							if(!$error_with_currency){
				                $s_sql = "UPDATE creditor SET sync_status = 1, sync_started_time = now() WHERE id = ?";
				                $o_query = $o_main->db->query($s_sql, array($creditor['id']));
				                $reminder_bookaccount = 8070;
				                $interest_bookaccount = 8050;
				                if($creditor['reminder_bookaccount'] != ""){
				                    $reminder_bookaccount = $creditor['reminder_bookaccount'];
				                }
				                if($creditor['interest_bookaccount'] != ""){
				                    $interest_bookaccount = $creditor['interest_bookaccount'];
				                }

				                $open_fees = array();
				                foreach($transaction_fees as $transaction_fee){
				                    if($transaction_fee['open']){
				                        $open_fees[] = $transaction_fee;
				                    }
				                }
				                $noFeeError3 = true;
				                if(count($open_fees) > 0){
				                    $noFeeError3 = false;
				                }
				                $noFeeError3count = 0;


				                foreach($open_fees as $fee_transaction){
									$commentArray = explode("_",$fee_transaction['comment']);
									if($commentArray[2] == "interest"){
									   $transactionType = "interest";
									} else if($commentArray[2] == "reminderFee"){
									  $transactionType = "reminderFee";
								  	} else if($commentArray[0] == "Rente"){
										$transactionType = "interest";
									} else {
										$transactionType = "reminderFee";
									}
				                    $dueDate = $fee_transaction['due_date'];
				                    $hook_params = array(
				                        'transaction_id' => $fee_transaction['id'],
				                        'amount'=>$fee_transaction['amount']*(-1),
				                        'dueDate'=>$dueDate,
				                        'text'=>$commentArray[0],
				                        'type'=>$transactionType,
				                        'accountNo'=>$commentArray[1],
				                        'close'=> 1,
										'username'=> $username
				                    );
									if($invoiceDifferentCurrency) {
										$hook_params['currency'] = $currencyName;
										$hook_params['currency_rate'] = $currency_rate;
										$hook_params['currency_unit'] = 1;
									}

				                    $hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/insert_transaction.php';
				                    $v_return['log'] = $hook_file;
				                    if (file_exists($hook_file)) {
				                        include $hook_file;
				                        if (is_callable($run_hook)) {
											$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 2, creditor_syncing_id = ?";
											$o_query = $o_main->db->query($s_sql, array($creditor['id'], 'reset fee started: '.$transactionType, $creditor_syncing_id));

				                            $hook_result = $run_hook($hook_params);
				                            if($hook_result['result']){
				                                $noFeeError3count++;
												$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 2, creditor_syncing_id = ?";
												$o_query = $o_main->db->query($s_sql, array($creditor['id'], 'reset fee finished '.$transactionType, $creditor_syncing_id));
				                            } else {
				                                // var_dump("deleteError".$hook_result['error']);
												$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 2, creditor_syncing_id = ?";
												$o_query = $o_main->db->query($s_sql, array($creditor['id'], 'reset fee failed: '.json_encode($hook_result['error']), $creditor_syncing_id));
				                            }
				                        }
				                        unset($run_hook);
				                    }
				                }
				                if($noFeeError3count == count($open_fees)){
				                    $noFeeError3 = true;
				                }
				                if($noFeeError3){
									$transaction_ids = array($v_row['transaction_id']);
								    foreach($transaction_payments as $transaction_payment) {
										$transaction_ids[]=$transaction_payment['transaction_id'];
									}
									$hook_params = array(
										'transaction_ids' => $transaction_ids,
										'creditor_id'=>$creditor['id'],
										'username'=>$username
									);

									$hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/relink_transaction.php';
									if (file_exists($hook_file)) {
										include $hook_file;
										if (is_callable($run_hook)) {
											$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 2, creditor_syncing_id = ?";
											$o_query = $o_main->db->query($s_sql, array($creditor['id'], 'fee link started: '.$transactionType, $creditor_syncing_id));

											$hook_result = $run_hook($hook_params);
											if($hook_result['result']){
												$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 2, creditor_syncing_id = ?";
												$o_query = $o_main->db->query($s_sql, array($creditor['id'], 'fee link finished '.$transactionType, $creditor_syncing_id));
											} else {
												// var_dump("deleteError".$hook_result['error']);
												$s_sql = "INSERT INTO creditor_syncing_log SET created = NOW(), creditor_id = ?, log = ?, type = 2, creditor_syncing_id = ?";
												$o_query = $o_main->db->query($s_sql, array($creditor['id'], 'fee link failed: '.json_encode($hook_result), $creditor_syncing_id));
											}
										}
										unset($run_hook);
									}


				                    if(is_file(__DIR__."/../../../CreditorsOverview/output/includes/import_scripts/import_cases2.php")){
				                        ob_start();
				                        include(__DIR__."/../../../CreditorsOverview/output/languagesOutput/default.php");
				                        if(is_file(__DIR__."/../../../CreditorsOverview/output/languagesOutput/".$languageID.".php")){
				                            include(__DIR__."/../../../CreditorsOverview/output/languagesOutput/".$languageID.".php");
				                        }
				                        $creditorId = $creditor['id'];
										$fromResetFees = true;
				                        include(__DIR__."/../../../CreditorsOverview/output/includes/import_scripts/import_cases2.php");
				                        // include(__DIR__."/../../output/includes/create_cases.php");
				                        $result_output = ob_get_contents();
				                        $result_output = trim(preg_replace('/\s\s+/', '', $result_output));
				                        ob_end_clean();
										$transactionDueDate = $v_row['due_date'];
										$s_sql = "UPDATE collecting_cases SET updated=NOW(), reminder_profile_id = '".$o_main->db->escape_str($_POST['profile_id'])."', due_date = '".$o_main->db->escape_str($transactionDueDate)."', collecting_cases_process_step_id = 0  WHERE id = ?";
										$o_query = $o_main->db->query($s_sql, array($caseData['id']));

										$s_sql = "DELETE FROM collecting_cases_fee_transaction_log WHERE collectingcase_id = ?";
										$o_query = $o_main->db->query($s_sql, array($caseData['id']));

										$fw_redirect_url = $_POST['redirect_url'];
				                    } else {
				                        $fw_error_msg[] =  'Missing sync script. Contact system developer';
				                    }

									$s_sql = "UPDATE creditor SET sync_status = 0 WHERE id = ?";
									$o_query = $o_main->db->query($s_sql, array($creditor['id']));

									//trigger reordering 	
									$s_sql = "UPDATE creditor_transactions SET to_be_reordered = 1
									WHERE creditor_id = '".$o_main->db->escape_str($creditor['id'])."' AND collectingcase_id = '".$o_main->db->escape_str($caseData['id'])."'";
									$o_query = $o_main->db->query($s_sql);
													
									process_open_cases_for_tabs($creditor['id'], 4);
				                } else {
				                    $s_sql = "UPDATE creditor SET sync_status = 0 WHERE id = ?";
				                    $o_query = $o_main->db->query($s_sql, array($creditor['id']));
				                    $v_return['error'] = 'Error with syncing. Contact system developer';
				                }
							} else {
								$fw_error_msg[] = 'Error with currency retrieval. Please try again later';
							}
						}
					} else {
						$fw_error_msg[] =  'Wrong profile';
					}
	            } else {
	                $fw_error_msg[] = 'Sync already running. If sync wasn\'t finished please contact system developer';
	            }
			}
        // } else {
        //     $v_return['error'] = 'Missing customer. Contact system developer';
        // }
    }
	?>
	<div class="popupform popupform-<?php echo $eventId;?>">
        <div id="popup-validate-message" style="display:none;"></div>
        <form class="output-form main" action="<?php if(isset($formActionUrl)) { echo $formActionUrl; } else { print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=reset_case_full"; }?>" method="post">
            <input type="hidden" name="fwajax" value="1">
            <input type="hidden" name="fw_nocss" value="1">
            <input type="hidden" name="output_form_submit" value="1">
            <input type="hidden" name="languageID" value="<?php echo $variables->languageID?>">
            <input type="hidden" name="case_id" value="<?php echo $_POST['case_id'];?>">
            <input type="hidden" name="redirect_url" value="<?php if(isset($formRedirectUrl)) { echo $formRedirectUrl; } else { echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$eventId; } ?>">
            <div class="inner">
                <div class="popupformTitle"><?php echo $formText_CaseWillBeFullyReset_output;?></div>
				<div class="line">
					<?php echo $formText_CaseWillBeResetAndBeReadyToStartOver_output;?><br/>
					<?php echo $formText_AllFeesWillBeResetAndYouCanChooseToChangeTheProfile_output;?>
				</div><br/>
                <div class="line">
					<b><?php echo $formText_Fees_output;?></b><br/>
                    <table class="table">
                        <tr>
                            <th><?php echo $formText_Name_output;?></th>
                            <th><?php echo $formText_Amount_output?></th>
                        </tr>
                        <?php
                        foreach($transaction_fees as $transaction_fee) {
                            if($transaction_fee['open']) {
                                $claim_text_array = explode("_", $transaction_fee['comment']);
                                ?>
                                <tr>
                                    <td><?php echo $claim_text_array[0];?></td>
                                    <td><?php echo number_format($transaction_fee['amount'], 2, ",", " ");?></td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </table>
                </div>
				<div class="line">
					<div class="lineTitle"><?php echo $formText_Profile_Output; ?></div>
					<div class="lineInput">
						<select name="profile_id" autocomplete="off" class="popupforminput botspace" required>
							<option value=""><?php echo $formText_Select_output;?></option>
							<?php foreach($case_profiles as $case_profile) { ?>
								<option value="<?php echo $case_profile['id'];?>" <?php if($case_profile['id'] == $caseData['reminder_profile_id']) echo 'selected';?>><?php echo $case_profile['name'];?></option>
							<?php } ?>
						</select>
					</div>
					<div class="clear"></div>
				</div>
				<?php
				echo $formText_TotalBalanceWithoutFees_output.": ".$v_row['amount'];
				?>
            </div>

            <div class="popupformbtn">
                <button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
                <input type="submit" name="sbmbtn" value="<?php echo $formText_Reset_Output; ?>">
            </div>
        </form>
    </div>
    <script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
    <script type="text/javascript">

    $("form.output-form").validate({
        submitHandler: function(form) {
            fw_loading_start();
            var formdata = $(form).serializeArray();
            var data = {};
            $(formdata).each(function(index, obj){
                data[obj.name] = obj.value;
            });
            $("#popup-validate-message").hide();

            $.ajax({
                url: $(form).attr("action"),
                cache: false,
                type: "POST",
                dataType: "json",
                data: data,
                success: function (data) {
                    fw_loading_end();
                    if(data.error !== undefined)
                    {
                        $.each(data.error, function(index, value){
                            var _type = Array("error");
                            if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
                            $("#popup-validate-message").append(value);
                        });
                        $("#popup-validate-message").show();
                        fw_loading_end();
                        fw_click_instance = fw_changes_made = false;
                    } else  if(data.redirect_url !== undefined)
                    {
                        out_popup.addClass("close-reload");
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
    </script>
	<?php
}
?>
