<?php
$customerId = $v_data['params']['customerId'];
$code = $v_data['params']['code'];
$webpage = $v_data['params']['webpage'];
$email = $v_data['params']['email'];
$phone = $v_data['params']['phone'];

$sec = $v_data['params']["industry"];

$sec = substr($sec,0,-1);
$secArr = explode('¤', $sec);

$getComp = $o_main->db->query("SELECT * FROM customer WHERE id = ? AND member_profile_link_code = ?", array($customerId, $code));
$customer = $getComp ? $getComp->row_array() : array();
if($customer){
    $query = $o_main->db->query("UPDATE customer SET updated = NOW(),
    updatedBy='web',
    homepage = ?,
    email = ?,
    phone = ?
    WHERE id = ?", array($webpage, $email, $phone, $customer['id']));
    if($query){
        $v_return['status'] = 1;
    }

    $getID = $o_main->db->query("SELECT id FROM customer_selfdefined_values 
    WHERE customer_id = ? AND selfdefined_fields_id = ?", array($customerId, 3));
    $res = $getID->row();

    $DELETE = $o_main->db->query("DELETE FROM customer_selfdefined_values_connection 
    WHERE selfdefined_value_id = ?", array($res->id));

    for($j=0;$j<count($secArr);$j++){
        $INSERT5 = "INSERT INTO customer_selfdefined_values_connection SET created = NOW(), selfdefined_value_id = '$res->id', selfdefined_list_line_id = '$secArr[$j]'";
    	$o_query = $o_main->db->query($INSERT5);
    }
}
?>
