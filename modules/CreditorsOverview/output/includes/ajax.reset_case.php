<?php
$username= $variables->loggID;

$s_sql = "SELECT ct.* FROM creditor_transactions ct WHERE ct.collectingcase_id = ? ";
$o_query = $o_main->db->query($s_sql, array($_POST['case_id']));
$v_row = ($o_query ? $o_query->row_array() : array());
$v_return['initial_transaction'] = $v_row;
if($v_row){

	$s_sql = "SELECT * FROM creditor WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($v_row['creditor_id']));
	$creditor = ($o_query ? $o_query->row_array() : array());

	$connected_transactions = array();
	$all_connected_transaction_ids = array($invoice['id']);
	if($v_row['link_id'] > 0 && ($creditor['checkbox_1'])) {
		$s_sql = "SELECT * FROM creditor_transactions ct WHERE ct.link_id = ? AND (ct.open = 1) AND (ct.system_type='InvoiceCustomer' OR ct.system_type = 'CreditnoteCustomer') AND ct.id <> ?";
		$o_query = $o_main->db->query($s_sql, array($v_row['link_id'], $v_row['id']));
		$connected_transactions_raw = ($o_query ? $o_query->result_array() : array());
		foreach($connected_transactions_raw as $connected_transaction_raw){
			if(strpos($connected_transaction_raw['comment'], '_') === false){
				$connected_transactions[] = $connected_transaction_raw;
			}
		}
		foreach($connected_transactions as $connected_transaction){
			$all_connected_transaction_ids[] = $connected_transaction['id'];
		}
	}

    $s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') AND link_id = ? AND creditor_id = ?";
    $o_query = $o_main->db->query($s_sql, array($v_row['link_id'], $v_row['creditor_id']));
    $all_transaction_payments = ($o_query ? $o_query->result_array() : array());
	$transaction_payments = array();
	foreach($all_transaction_payments as $all_transaction_payment) {
		if(!in_array($all_transaction_payment['id'], $all_connected_transaction_ids)){
			$transaction_payments[] = $all_transaction_payment;
		}
	}

    $s_sql = "SELECT * FROM creditor_transactions WHERE system_type='InvoiceCustomer' AND link_id = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND (comment LIKE '%reminderFee_%' OR comment LIKE '%interest_%') ORDER BY created DESC";
    $o_query = $o_main->db->query($s_sql, array($v_row['link_id'], $v_row['creditor_id']));
    $transaction_fees = ($o_query ? $o_query->result_array() : array());

    // foreach($transaction_fees as $transaction_fee){
    //     if(!$transaction_fee['open']){
    //         $s_sql = "SELECT * FROM creditor_transactions  WHERE (system_type='Payment' OR system_type ='CreditnoteCustomer') AND link_id = ? AND creditor_id = ?";
    //         $o_query = $o_main->db->query($s_sql, array($transaction_fee['link_id'], $transaction_fee['creditor_id']));
    //         $fee_payments = ($o_query ? $o_query->result_array() : array());
    //         $transaction_payments = array_merge($transaction_payments, $fee_payments);
    //     }
    // }
    $v_return['transaction_payments'] = $transaction_payments;
    $v_return['transaction_fees'] = $transaction_fees;

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
	        if($creditor){
	            if($creditor['sync_status'] != 1){
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

	                $s_sql = "UPDATE creditor SET sync_status = 1 WHERE id = ?";
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
									$o_query = $o_main->db->query($s_sql, array($creditor['id'], 'fee link failed: '.json_encode($hook_result['error']), $creditor_syncing_id));
								}
							}
							unset($run_hook);
						}

	                    $languageID = $_POST['languageID'];
	                    if(is_file(__DIR__."/../../output/includes/import_scripts/import_cases2.php")){
	                        ob_start();
	                        include(__DIR__."/../../output/languagesOutput/default.php");
	                        if(is_file(__DIR__."/../../output/languagesOutput/".$languageID.".php")){
	                            include(__DIR__."/../../output/languagesOutput/".$languageID.".php");
	                        }
	                        $creditorId = $creditor['id'];
							$fromResetFees = true;
	                        include(__DIR__."/../../output/includes/import_scripts/import_cases2.php");
	                        // include(__DIR__."/../../output/includes/create_cases.php");
	                        $result_output = ob_get_contents();
	                        $result_output = trim(preg_replace('/\s\s+/', '', $result_output));
	                        ob_end_clean();

	                        $s_sql = "UPDATE creditor SET sync_status = 0 WHERE id = ?";
	                        $o_query = $o_main->db->query($s_sql, array($creditor['id']));

	                        $v_return['status'] = 1;
	                    } else {
	                        $v_return['error'] = 'Missing sync script. Contact system developer';
	                    }
	                } else {
	                    $s_sql = "UPDATE creditor SET sync_status = 0 WHERE id = ?";
	                    $o_query = $o_main->db->query($s_sql, array($creditor['id']));
	                    $v_return['error'] = 'Error with syncing. Contact system developer';
	                }
	            } else {
	                $v_return['error'] = 'Sync already running. If sync wasn\'t finished please contact system developer';
	            }
			}
        // } else {
        //     $v_return['error'] = 'Missing customer. Contact system developer';
        // }
    } else {
        $v_return['status'] = 1;
    }
}

if($_POST['output_form_submit']){
    if($v_return['error']){
        $fw_error_msg[] = $v_return['error'];
    } else {
        $fw_redirect_url = $_POST['redirect_url'];
    }
}
if($v_return['status']) {
    $initial_transaction = $v_return['initial_transaction'];
    $transactions = $v_return['transaction_fees'];
    $transaction_payments = $v_return['transaction_payments'];

    $initialAmount = $initial_transaction['amount'];
    $amount = $initialAmount;
    $openFeeAmount = 0;
	$mainAmountLeft = $initialAmount;
    foreach($transactions as $transaction_fee) {
        $amount += $transaction_fee['amount'];
        if($transaction_fee['open']) {
            $openFeeAmount += $transaction_fee['amount'];
        }
    }
    foreach($transaction_payments as $transaction_payment) {
        $amount += $transaction_payment['amount'];
		$mainAmountLeft+=$transaction_payment['amount'];
    }
	$transaction_ids = array($initial_transaction['transaction_id']);
	foreach($transaction_payments as $transaction_payment) {
		$transaction_ids[]=$transaction_payment['transaction_id'];
	}
    ?>
    <div class="popupform popupform-<?php echo $eventId;?>">
        <div id="popup-validate-message" style="display:none;"></div>
        <form class="output-form main" action="<?php if(isset($formActionUrl)) { echo $formActionUrl; } else { print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=reset_case"; }?>" method="post">
            <input type="hidden" name="fwajax" value="1">
            <input type="hidden" name="fw_nocss" value="1">
            <input type="hidden" name="output_form_submit" value="1">
            <input type="hidden" name="languageID" value="<?php echo $variables->languageID?>">
            <input type="hidden" name="case_id" value="<?php echo $_POST['case_id'];?>">
            <input type="hidden" name="redirect_url" value="<?php if(isset($formRedirectUrl)) { echo $formRedirectUrl; } else { echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$eventId; } ?>">
            <div class="inner">
                <div class="popupformTitle"><?php echo $formText_CaseFeesWillBeReset_output;?></div>
                <div class="line">
                    <table class="table">
                        <tr>
                            <th><?php echo $formText_Name_output;?></th>
                            <th><?php echo $formText_Amount_output?></th>
                        </tr>
                        <?php
                        foreach($transactions as $transaction_fee) {
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
				<?php
				echo $formText_TotalBalanceWithoutFees_output.": ".$mainAmountLeft;
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
                        out_popup.addClass("close-reload-creditor");
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
