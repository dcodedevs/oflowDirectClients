<?php 

$username = $v_data['params']['email'];
$list_filter_fil = "reminderLevel";
$list_filter = $v_data['params']['list_filter'];
$creditor_id = $v_data['params']['creditor_id'];

if($v_data['params']['new_tab'] == 1){
	require_once __DIR__ . '/../../output/includes/creditor_functions_v2.php';
} else {
	require_once __DIR__ . '/../../output/includes/creditor_functions.php';
}
$tab_names = array("reminderLevel", "canSendReminderNow", "dueDateNotExpired", "doNotSend", "stoppedWithObjection", "notPayedConsiderCollectingProcess");
$countArray = array();
$suggested_count = 0;
if(in_array($list_filter, $tab_names)) {
    $filters['list_filter'] = $list_filter;
    $suggested_count = get_transaction_count2($o_main, $creditor_id, $list_filter_fil, $filters);
}
$v_return['status'] = 1;
$v_return['count'] = $suggested_count;

?>