<?php
$customerId = $v_data['params']['customerId'];
$code = $v_data['params']['code'];

$getComp = $o_main->db->query("SELECT * FROM customer WHERE id = ? AND member_profile_link_code = ?", array($customerId, $code));
$customer = $getComp ? $getComp->row_array() : array();
if($customer){
    $v_return['status'] = 1;
}
?>
