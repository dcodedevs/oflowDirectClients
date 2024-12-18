<?php
$updatedSuccessfully = array();
$adjustPricePercent = str_replace(",", ".", $_POST['adjustPrice']);

$subscriptions = $_POST['customer'];
if($adjustPricePercent != 0) {
    foreach($subscriptions as $subscriptionString) {
        $subscriptionArray = explode("-", $subscriptionString, 2);
        $subscription = $subscriptionArray[0];
        $orderlineId = $subscriptionArray[1];
        if($orderlineId > 0 && $subscription > 0) {
            $subscriptionLines = array();
            $s_sql_select = "SELECT sl.* FROM subscriptionline sl";
        	$s_sql_join = " ";
        	$s_sql_where = " WHERE sl.id = ".$orderlineId;
        	$s_sql_group = "";

        	$s_sql = $s_sql_select.$s_sql_join.$s_sql_where.$s_sql_group;
        	$o_query = $o_main->db->query($s_sql);
    		$subscriptionLine = $o_query ? $o_query->row_array() : array();

            if($subscriptionLine){
                $pricePerPiece = $subscriptionLine['pricePerPiece'];
                $newPricePerPiece = number_format($pricePerPiece * (1+$adjustPricePercent/100), 2,".", "");
                if($_POST['round_prices']){
                    $newPricePerPiece = round($newPricePerPiece);
                }
                $s_sql = "UPDATE subscriptionline SET pricePerPiece = ?, previous_adjustment_price = ?, previous_adjustment_date = NOW()  WHERE id = ?";
                $o_query = $o_main->db->query($s_sql, array($newPricePerPiece,$pricePerPiece, $subscriptionLine['id']));
                if($o_query) {
                    $updatedSuccessfully[$subscription]++;
                } else {
                    $fw_error_msg[] = $formText_ErrorUpdatingSubscriptionLine_output." ".$orderlineId;
                }
            }
        }
    }
    echo count($updatedSuccessfully);
} else {
    $fw_error_msg[] = $formText_MissingAdjustPercent_output;
}
?>
