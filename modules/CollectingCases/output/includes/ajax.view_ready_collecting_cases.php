<?php
$creditor_id = $_POST['creditor_id'];
$s_sql = "SELECT creditor.* FROM creditor WHERE id = '".$o_main->db->escape_str($creditor_id)."'";
$o_query = $o_main->db->query($s_sql);
$creditor = ($o_query ? $o_query->row_array() : array());
if($creditor){
    $s_sql = "SELECT * FROM customer WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($creditor['customer_id']));
    $creditorCustomer = $o_query ? $o_query->row_array() : array();
    if(intval($creditor['choose_progress_of_reminderprocess']) == 0){
        $creditor['choose_how_to_create_collectingcase'] = 0;
    }


    require_once __DIR__ . '/../../../CreditorsOverview/output/includes/creditor_functions.php';


    if($_POST['level'] == "reminderLevel"){
        if($creditor['choose_progress_of_reminderprocess'] == 1){
            $list_filter_fil = "reminderLevel";
            $filters['list_filter'] = "canSendReminderNow";
            $collectingCases = get_case_list($o_main, $creditor['id'], $list_filter_fil, $filters);
        } else {
            $collectingCases = array();
        }
    } else if($_POST['level'] == "collectingLevel") {
        $list_filter_fil = "collectingLevel";
        $collectingCases = array();
        $filters['list_filter'] = "activeOnCollectingLevel";
        $canSendReminderCollectingNowCount = get_case_list($o_main, $creditor['id'], $list_filter_fil, $filters);

        $filters['list_filter'] = "readyToStartInCollectingLevel";
        $readyCollectingNowCount = get_case_list($o_main, $creditor['id'], $list_filter_fil, $filters);
        $collectingCases = array_merge($readyCollectingNowCount, $canSendReminderCollectingNowCount);
    }
    ?>
    <table class="table">
        <tr>
            <th width="200" class="gtable_cell gtable_cell_head">
                <?php echo $formText_Debitor_output;?>
            </th>
            <th width="100" class="gtable_cell gtable_cell_head">
                <?php echo $formText_InvoiceNo_output;?><br/><?php echo $formText_InvoiceDate_output;?><br/><?php echo $formText_DueDate_output;?>
            </th>
            <th class="gtable_cell gtable_cell_head" style="width: 100px;"><?php echo $formText_MainClaim_output;?></th>
            <th class="gtable_cell gtable_cell_head" style="width: 250px;">
                <?php
                    echo $formText_NextStep_output;
                ?>
            </th>
        </tr>
        <?php
		$action_text_icons = array(1=>'<i class="fas fa-file"></i>', 2=>$formText_SendSms_output, 3=>$formText_Call_output, 4=>'<i class="fas fa-at"></i>');
        foreach($collectingCases as $v_row){
            $s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND collectingcase_id = ? ORDER BY created DESC";
			$o_query = $o_main->db->query($s_sql, array($v_row['id']));
			$invoices = ($o_query ? $o_query->result_array() : array());
            foreach($invoices as $invoice) {
                $totalSumOriginalClaim += $invoice['collecting_case_original_claim'];
            }
            $s_sql = "SELECT * FROM collecting_cases_process_steps WHERE id = ?";
            $o_query = $o_main->db->query($s_sql, array($v_row['collecting_cases_process_step_id']));
            $process_step = ($o_query ? $o_query->row_array() : array());
            $collectingLevelName = $process_step['name'];

            $v_row['collectingLevelName'] = $collectingLevelName;
            $v_row['totalSumOriginalClaim'] = $totalSumOriginalClaim;
            $v_row['invoices'] = $invoices;


            $s_sql = "SELECT cccl.*, ccha.created, ccha.action_type, ccha.performed_date FROM collecting_cases_claim_letter cccl
            LEFT OUTER JOIN collecting_cases_handling_action ccha ON ccha.id = cccl.action_id
            WHERE cccl.content_status < 2 AND cccl.case_id = ?  ORDER BY cccl.created DESC";
            $o_query = $o_main->db->query($s_sql, array($v_row['id']));
            $v_claim_letters = ($o_query ? $o_query->result_array() : array());
            $v_row['letters'] = $v_claim_letters;

            if($v_row['status'] == 0 || $v_row['status'] == 1){
                $s_sql = "SELECT * FROM collecting_cases_process WHERE id = ? ORDER BY sortnr ASC";
                $o_query = $o_main->db->query($s_sql, array($v_row['reminder_process_id']));
                $process = ($o_query ? $o_query->row_array() : array());
            } else if($v_row['status'] == 3 || $v_row['status'] == 7){
                $s_sql = "SELECT * FROM collecting_cases_process WHERE id = ? ORDER BY sortnr ASC";
                $o_query = $o_main->db->query($s_sql, array($v_row['collecting_process_id']));
                $process = ($o_query ? $o_query->row_array() : array());
            }

            $s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_steps.collecting_cases_process_id = ? ORDER BY sortnr ASC";
            $o_query = $o_main->db->query($s_sql, array($process['id']));
            $old_steps = ($o_query ? $o_query->result_array() : array());
            $steps = array();
            foreach($old_steps as $step) {
                $action_types = array();

                $s_sql = "SELECT * FROM collecting_cases_process_steps_action WHERE collecting_cases_process_steps_id = ? ORDER BY sortnr ASC";
                $o_query = $o_main->db->query($s_sql, array(intval($step['id'])));
                $actions = $o_query ? $o_query->result_array() : array();
                if(count($actions) > 0) {
                    foreach($actions as $action) {
                        if(!in_array($action['action'], $action_types)){
                            array_push($action_types, $action['action']);
                        }
                    }
                }
                $step['action_types'] = $action_types;
                array_push($steps, $step);

            }
            $next_step = array();
            $stepTrigger = false;
            $currentStep = array();
            foreach($steps as $step) {
                if(!$next_step){
                    $next_step = $step;
                }
                if($stepTrigger){
                    $next_step = $step;
                    $stepTrigger = false;
                }
                if($step['id'] == $v_row['collecting_cases_process_step_id']) {
                    $currentStep = $step;
                    $stepTrigger = true;
                }
            }

            $action_types = array();

            $s_sql = "SELECT * FROM collecting_cases_process_steps_action WHERE collecting_cases_process_steps_id = ? ORDER BY sortnr ASC";
            $o_query = $o_main->db->query($s_sql, array(intval($currentStep['id'])));
            $actions = $o_query ? $o_query->result_array() : array();
            if(count($actions) > 0) {
                foreach($actions as $action) {
                    if(!in_array($action['action'], $action_types)){
                        array_push($action_types, $action['action']);
                    }
                }
            }


            $v_row['steps'] = $steps;
            $v_row['next_step'] = $next_step;
            $v_row['action_types'] = $action_types;

            $s_sql = "SELECT * FROM collecting_cases_objection WHERE collecting_case_id = ? ORDER BY created DESC";
            $o_query = $o_main->db->query($s_sql, array($v_row['id']));
            $objections = ($o_query ? $o_query->result_array() : array());
            $v_row['objections'] = $objections;

            $steps = $v_row['steps'];
            $next_step = $v_row['next_step'];
            $invoices = $v_row['invoices'];
            $letters = $v_row['letters'];
            $objections = $v_row['objections'];
            ?>
            <tr class="gtable_row">
            <?php
            // Show default columns
             ?>
                <td class="gtable_cell"><?php echo $v_row['debitorName'];?></td>
                 <td class="gtable_cell">
                     <?php
                     $fileAddition = "";
                     $invoiceFile = $invoices[0]['invoiceFile'];
                     if($invoiceFile != ""){
                         $fileParts = explode('/',$invoiceFile);
                         $fileName = array_pop($fileParts);
                         $fileParts[] = rawurlencode($fileName);
                         $filePath = implode('/',$fileParts);

                         if($v_accountinfo['cus_portal_crm_account_url'] != ""){
                             $hash = md5($v_accountinfo['cus_portal_crm_account_url'] . '-' . $invoices[0]['id']);
                             $fileNameApi = "";
                             foreach($fileParts as $filePart) {
                                 if($filePart != "uploads" && $filePart != "protected"){
                                     $fileNameApi .= $filePart."/";
                                 }
                             }
                             $fileNameApi = trim($fileNameApi, "/");
                             $fileAddition = "&externalApiAccount=".$v_accountinfo['cus_portal_crm_account_url']."&externalApiHash=".$hash."&file=".$fileNameApi;
                        }
                    }
                    if($invoiceFile != ""){
                     ?>
                     <a href="../<?php echo $invoiceFile; ?>?caID=<?php echo $_GET['caID']?>&table=creditor_invoice&field=invoiceFile&ID=<?php echo $invoices[0]['id'].$fileAddition; ?>&time=<?php echo time();?>" class="generatePdf" target="_blank">
                    <?php } ?>
                        <?php echo $invoices[0]['invoice_nr'];?>
                    <?php 	if($invoiceFile != ""){ ?>
                        </a>
                     <?php } ?>
                     <?php echo "<br/>".date("d.m.Y", strtotime($invoices[0]['date']));?>
                     <?php echo "<br/>".date("d.m.Y", strtotime($invoices[0]['due_date']));?>
                 </td>
                <td class="gtable_cell rightAlign"><?php
                echo number_format($v_row['totalSumOriginalClaim'], 2, ",", " ");
                if($v_row['paid_amount'] != "0" && $v_row['paid_amount'] != null){
                    echo "<br/>".number_format($v_row['paid_amount'], 2, ",", " ");
                }
                if($v_row['credited_amount'] != "0" && $v_row['credited_amount'] != null){
                    echo "<br/>".number_format($v_row['credited_amount'], 2, ",", " ");
                }

                ?></td>

                <?php /*if($mainlist_filter == "cases_reminding") { ?>
                    <div class="gtable_cell">
                        <div class="move_to_next_step" data-case-id="<?php echo $v_row['id'];?>" data-process-id="<?php echo $process['process_id'];?>"><?php echo $formText_MoveToNextStep;?></div>
                    </div>
                <?php } */?>
                <td class="gtable_cell">
                    <?php if(intval($v_row['nextStepId']) > 0) {
                        ?>
                        <?php
                        // echo date("d.m.Y", strtotime($v_row['nextStepDate']));
                        ?>
                        <div class="case_step_wrapper">
                            <?php /*if($list_filter != "due_date_not_expired" && $list_filter == "due_date_expired_manual") { ?>
                                <select name="step_id" autocomplete="off"  class="case_step" data-case-id="<?php echo $v_row['id']?>">
                                    <?php foreach($steps as $step) { ?>
                                        <option value="<?php echo $step['id'];?>" <?php if($next_step['id'] == $step['id']) echo 'selected';?>><?php echo $step['name'];?></option>
                                    <?php } ?>
                                </select>
                            <?php } else {*/ ?>
                                <?php foreach($steps as $step) {
                                    if($next_step['id'] == $step['id']) echo ''.$step['name']."";
                                } ?>
                            <?php /*}*/ ?>

                            <div class="action_icon_wrapper">
                                <?php
                                    foreach($steps as $step) {
                                        $action_types = $step['action_types'];
                                        ?>
                                        <div class="step_action step_action_<?php echo $step['id']?>" <?php if($next_step['id'] == $step['id']) echo 'style="display: block;"'?>>
                                            <?php
                                            foreach($action_types as $action_type) {
                                                if(isset($action_text_icons[$action_type])){
                                                    if($action_type == 4){
                                                        //if no email just print
                                                        if($v_row['invoiceEmail'] != ""){
                                                            echo "<span class='email_wrapper'>".$action_text_icons[4]."</span>";

                                                            if($list_filter == "due_date_expired_manual"){
                                                            ?>
                                                                <i class="fas fa-caret-right open_override_action"></i>
                                                            <?php } ?>
                                                            <div class="override_action_wrapper">
                                                                <select name="override_action" class="override_action" data-case-id="<?php echo $v_row['id'];?>" <?php if($list_filter != "due_date_expired_manual") echo 'disabled';?>>
                                                                    <option value="0"><?php echo $formText_SendEmail_output;?></option>
                                                                    <option value="1"><?php echo $formText_Print_output;?></option>
                                                                </select>
                                                            </div>
                                                            <?php
                                                            echo "<span class='email_wrapper email_wrapper_text'>".$v_row['invoiceEmail'] ."</span>";
                                                        } else {
                                                            echo $action_text_icons[1];
                                                        }
                                                    } else {
                                                        echo $action_text_icons[$action_type];
                                                    }
                                                    ?>
                                                    </br>
                                                    <?php
                                                }
                                            }
                                            ?>
                                         </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="clear"></div>

                    <?php } else {
                            echo $formText_FinalStep_output.": ";
                            foreach($steps as $step) {
                                if($v_row['collecting_cases_process_step_id'] == $step['id']) echo ''.$step['name']."";
                            }
                        } ?>
                    <?php /* if($list_filter != "due_date_not_expired") { ?>
                        <?php if($list_filter == "due_date_expired_manual") { ?>
                            <div class="processToNext" data-case-id="<?php echo $v_row['id'];?>" data-process-id="<?php echo $process['process_id'];?>"><?php echo $formText_ProcessToNextStep_output;?></div>
                        <?php } ?>
                    <?php } */ ?>
                    <div class="clear"></div>
                </td>
            </tr>
            <?php
        }
        ?>
    </table>
    <style>
    .step_action {
        display: none;
        position: relative;
    }
    </style>
    <?php

} else {
    echo $formText_MissingCreditor_output;
}
?>
