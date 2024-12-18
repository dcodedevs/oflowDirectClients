<?php
$collectingCaseId = $_POST['collectingCaseId'];
$amount = str_replace(",", ".",$_POST['amount']);
$paymentId = $_POST['paymentId'];

$s_sql = "SELECT * FROM collecting_company_cases WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($collectingCaseId));
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
if($amount > 0){
    if($collectingCase){
        if($creditor){
            if($coveringOrderAndSplit){
                include_once("fnc_calculate_coverlines.php");
                $insertInfo = calculate_coverlines($coveringOrderAndSplit, $paymentId, $collectingCase, $amount);
                if(!$insertInfo){
                    echo $formText_ConfigurationErrorNumbersNotMatching_output;
                } else {
                    foreach($insertInfo as $collecting_claim_line_type => $insertInfoSingle) {
                        $collectioncompany_share = $insertInfoSingle[0];
                        $creditor_share = $insertInfoSingle[1];
                        $total_amount = $insertInfoSingle[3];
    					$debitor_share = $insertInfoSingle[4];
                        if($collectioncompany_share > 0 || $creditor_share > 0 || $agent_share > 0){
                            $s_sql = "SELECT * FROM collecting_cases_claim_line_type_basisconfig WHERE id = ?";
                            $o_query = $o_main->db->query($s_sql, array($collecting_claim_line_type));
                            $claim_line_type = $o_query ? $o_query->row_array() : array();

                            ?>
                            <div class="type_wrapper">
                                <b><?php echo $claim_line_type['type_name'];?></b>
                                <div class="type_row"><?php echo $formText_CollectingCompanyShare_output.": ". $collectioncompany_share?></div>
                                <div class="type_row"><?php echo $formText_CreditorShare_output.": ". $creditor_share?></div>
                                <div class="type_row"><?php echo $formText_DebitorShare_output.": ". $debitor_share?></div>
                                <div class="type_row"><?php echo $formText_Total_output.": ". $total_amount?></div>
                            </div>
                            <?php
                        }
                    }
                }

            } else {
                echo $formText_CoveringOrderAndSplitMissing_Output;
            }
        } else {
            echo $formText_CreditorIsMissing_Output;
        }
    } else {
        echo $formText_CollectingCaseIsMissing_Output;
    }
} else {
    echo $formText_AmountIsMissing_Output;
}
?>
