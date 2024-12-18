<?php
if(!function_exists("include_local")) include(__DIR__."/../../../input/includes/fn_include_local.php");

include_once(__DIR__."/readOutputLanguage.php");

?>
<table class="table">
    <tr>
        <th><?php echo $formText_ClaimlineType_output;?></th>
        <th><?php echo $formText_CompletedCases_output;?></th>
        <th><?php echo $formText_OpenCases_output;?></th>
    </tr>
    <?php
    $s_sql = "SELECT * FROM collecting_cases_claim_line_type_basisconfig ORDER BY id DESC";
    $o_query = $o_main->db->query($s_sql);
    $claimlineTypes = $o_query ? $o_query->result_array() : array();
    $completedCasesCount = array();
    $openCasesCount = array();
    foreach($claimlineTypes as $claimlineType){
        $completedCasesCount[$claimlineType['id']] = 0;
        $openCasesCount[$claimlineType['id']] = 0;
    }
    $completedCasesCount['original'] = 0;
    $openCasesCount['original'] = 0;

    $s_sql = "SELECT * FROM collecting_cases WHERE status = 1 ORDER BY id DESC";
    $o_query = $o_main->db->query($s_sql);
    $completedCases = $o_query ? $o_query->result_array() : array();

    $s_sql = "SELECT * FROM collecting_cases WHERE status = 0 OR status is null LIMIT 100 ORDER BY id DESC ";
    $o_query = $o_main->db->query($s_sql);
    $activeCases = $o_query ? $o_query->result_array() : array();
    foreach($completedCases as $completedCase) {
        $s_sql = "SELECT * FROM creditor_invoice WHERE content_status < 2 AND collecting_case_id = ? ORDER BY created DESC";
        $o_query = $o_main->db->query($s_sql, array($completedCase['id']));
        $invoice = ($o_query ? $o_query->row_array() : array());
        $completedCasesCount['original'] += $invoice['collecting_case_original_claim'];

        $s_sql = "SELECT * FROM collecting_cases_claim_lines WHERE content_status < 2 AND collecting_case_id = ? ORDER BY claim_type ASC, created DESC";
        $o_query = $o_main->db->query($s_sql, array($completedCase['id']));
        $claims = ($o_query ? $o_query->result_array() : array());
        foreach($claims as $claim){
            $completedCasesCount[$claim['claim_type']] += $claim['amount'];
        }
    }
    foreach($activeCases as $completedCase) {
        $s_sql = "SELECT * FROM creditor_invoice WHERE content_status < 2 AND collecting_case_id = ? ORDER BY created DESC";
        $o_query = $o_main->db->query($s_sql, array($completedCase['id']));
        $invoice = ($o_query ? $o_query->row_array() : array());
        $openCasesCount['original'] += $invoice['collecting_case_original_claim'];

        $s_sql = "SELECT * FROM collecting_cases_claim_lines WHERE content_status < 2 AND collecting_case_id = ? ORDER BY claim_type ASC, created DESC";
        $o_query = $o_main->db->query($s_sql, array($completedCase['id']));
        $claims = ($o_query ? $o_query->result_array() : array());
        foreach($claims as $claim){
            $openCasesCount[$claim['claim_type']] += $claim['amount'];
        }
    }
    ?>
    <tr>
        <td><?php echo $formText_OriginalClaim_output;?></td>
        <td><?php echo $completedCasesCount['original'];?></td>
        <td><?php echo $openCasesCount['original'];?></td>
    </tr>
    <?php

    foreach($claimlineTypes as $claimlineType) {
        ?>
        <tr>
            <td><?php echo $claimlineType['type_name'];?></td>
            <td><?php echo $completedCasesCount[$claimlineType['id']];?></td>
            <td><?php echo $openCasesCount[$claimlineType['id']];?></td>
        </tr>
        <?php
    }
    ?>
</table>
