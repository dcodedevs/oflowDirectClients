<?php
$project_id = isset($_GET['project_filter']) ? $_GET['project_filter'] : 0;
$department_id = isset($_GET['department_filter']) ? $_GET['department_filter'] : 0;

$ownercompany_filter = $_GET['ownercompany'] ? explode(",", $_GET['ownercompany']) : array();
$customerselfdefinedlist_filter = $_GET['customerselfdefinedlist_filter'] ? $_GET['customerselfdefinedlist_filter'] : "";
$ownercompany_filter_sql = "";
$selfdefined_join = "";
$selfdefined_sql = "";
$real_ownercompany_filter = array();
if(count($ownercompany_filter) > 0){
	foreach($ownercompany_filter as $singleItem){
		if($singleItem > 0){
			array_push($real_ownercompany_filter, $singleItem);
		}
	}
	if(count($real_ownercompany_filter) > 0){
		$ownercompany_filter_sql = " AND subscriptionmulti.ownercompany_id IN (".implode(',', $real_ownercompany_filter).")";
	}
}

$s_sql = "SELECT * FROM subscriptionmulti WHERE onhold = 1 AND content_status < 2 ".$ownercompany_filter_sql;
$o_query = $o_main->db->query($s_sql);
$onholdOrders = $o_query ? $o_query->result_array() : array();
?>

<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>

	<div class="popupformTitle"><?php echo $formText_RepeatingOrdersOnHold_output;?></div>
    <?php
    foreach($onholdOrders as $onholdOrder) {
        ?>
        <div class="order_row_onhold"><?php echo $onholdOrder['subscriptionName'];?></div>
        <?php
    }
    ?>
</div>
