<?php
$customer_id = $v_data['params']['customer_id'];
$username= $v_data['params']['username'];
$_POST = $v_data['params']['post'];

require_once(__DIR__."/../../output/includes/fnc_password_encrypt.php");

$s_sql = "SELECT creditor.* FROM customer
JOIN creditor ON creditor.customer_id = customer.id
WHERE customer.id = ".$o_main->db->escape($customer_id)."
AND customer.content_status < 2 GROUP BY customer.id ORDER BY customer.name";
$o_query = $o_main->db->query($s_sql);
$creditor = ($o_query ? $o_query->row_array() : array());

if($creditor){
    if(isset($_POST['entity_id'])) {
        $s_sql = "UPDATE creditor SET
        updated = now(),
        updatedBy='".$o_main->db->escape_str($username)."',
        entity_id='".$o_main->db->escape_str($_POST['entity_id'])."'
        WHERE id = '".$o_main->db->escape_str($creditor['id'])."'";
        $o_query = $o_main->db->query($s_sql);
        if($o_query){
            $v_return['data'] = 1;
        } else {
            $v_return['error'] = 'Error updating entry';
        }
    } else {
        $s_sql = "UPDATE creditor SET
        updated = now(),
        updatedBy='".$o_main->db->escape_str($username)."',
        integration_module='".$o_main->db->escape_str($_POST['integration_module'])."',
        24sevenoffice_username = '".$o_main->db->escape_str($_POST['24sevenoffice_username'])."',
        24sevenoffice_password = '".$o_main->db->escape_str(encrypt($_POST['24sevenoffice_password'], "uVh1eiS366"))."',
        24sevenoffice_identityname = '".$o_main->db->escape_str($_POST['24sevenoffice_identityname'])."',
        tripletex_employeetoken = '".$o_main->db->escape_str($_POST['tripletex_employeetoken'])."',
        entity_id='',
        tripletex_sessiondata = null
        WHERE id = '".$o_main->db->escape_str($creditor['id'])."'";
        $o_query = $o_main->db->query($s_sql);
        if($o_query){
            $v_return['data'] = 1;
        } else {
            $v_return['error'] = 'Error updating entry';
        }
    }
} else {
    $v_return['error'] = 'Customer Not found';
}
?>
