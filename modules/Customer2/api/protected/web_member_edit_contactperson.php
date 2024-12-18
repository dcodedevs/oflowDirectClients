<?php
$customerId = $v_data['params']['customerId'];
$code = $v_data['params']['code'];
$contactpersonId = $v_data['params']['contactpersonId'];
$action = $v_data['params']['action'];
$name = $v_data['params']['name'];
$middleName = $v_data['params']['middlename'];
$lastName = $v_data['params']['lastname'];
$title = $v_data['params']['title'];
$mobile = $v_data['params']['mobile'];
$email = $v_data['params']['email'];
$mainContact = $v_data['params']['mainContact'];
$displayInMemberpage = $v_data['params']['displayInMemberpage'];

//$v_return['data'] = $v_data['params'];

$getComp = $o_main->db->query("SELECT * FROM customer WHERE id = ? AND member_profile_link_code = ?", array($customerId, $code));
$customer = $getComp ? $getComp->row_array() : array();
if($customer){
    if($action == "delete" && strlen($contactpersonId) > 0){
        $query = $o_main->db->query("DELETE FROM contactperson WHERE id = ? AND customerId = ?", array($contactpersonId, $customer['id']));
        if($query){
            $v_return['status'] = 1;
        }
    } else if($action == "update" && strlen($contactpersonId) > 0){
        $query = $o_main->db->query("UPDATE contactperson SET updated = NOW(),
        updatedBy='web',
        name = ?,
        lastname = ?,
        title = ?,
        mobile = ?,
        email = ?,
        mainContact = ?,
        displayInMemberpage = ?
        WHERE id = ?", array($name, $lastName, $title, $mobile, $email, $mainContact, $displayInMemberpage, $contactpersonId));
        if($query){
            $v_return['status'] = 1;
        }
        $v_return['data'] = $o_main->db->last_query();
    } else if($action == "create") {
        $query = $o_main->db->query("INSERT INTO contactperson SET created = NOW(),
        createdBy='web',
        name = ?,
        lastname = ?,
        title = ?,
        mobile = ?,
        email = ?,
        mainContact = ?,
        displayInMemberpage = ?,
        customerId = ?", array($name, $lastName, $title, $mobile, $email, $mainContact, $displayInMemberpage, $customer['id']));
        if($query){
            $v_return['status'] = 1;
        }
    }
}
?>
