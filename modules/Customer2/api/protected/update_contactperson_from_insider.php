<?php
$contactpersonData = $v_data['params']['contactperson'];
$customerId = $v_data['params']['customerId'];
$contactpersonId = $v_data['params']['contactpersonId'];

$v_return['status'] = 0;
if(count($contactpersonData) > 0 && $customerId > 0 ) {
    unset($contactpersonData['contactpersonId']);
    $contactpersonData['customerId'] = $customerId;
    if($contactpersonId > 0){
        $s_sql = "select * from contactperson where id = ? and customerId = ?";
        $o_query = $o_main->db->query($s_sql, array($contactpersonId, $customerId));
        $contactpersonLocalData = $o_query ? $o_query->row_array() : array();
    } else {
        $s_sql = "select * from contactperson where customerId = ? AND email = ?";
        $o_query = $o_main->db->query($s_sql, array($customerId, $contactpersonData['email']));
        $contactpersonLocalData = $o_query ? $o_query->row_array() : array();
    }
    if($v_data['params']['delete']){
        $o_query = "UPDATE contactperson SET inactive = 1 WHERE id = ?";
        $o_query_people = $o_main->db->query($o_query, array($contactpersonLocalData['id']));
        if($o_query_people){
            $v_return['status'] = 1;
        }
    } else {
        if($contactpersonLocalData){
            $o_main->db->set($contactpersonData);
            $o_main->db->where('id', $contactpersonLocalData['id']);
            $o_query_people = $o_main->db->update("contactperson");
            $contactpersonId = $contactpersonLocalData['id'];
			$s_include_file = __DIR__.'/../../../ContactpersonAccess/output/includes/perform_contactperson_sync.php';
			if(is_file($s_include_file)) include($s_include_file);
        } else {
            $o_main->db->set($contactpersonData);
            $o_query_people = $o_main->db->insert("contactperson");
            $contactpersonId = $o_main->db->insert_id();
        }
        if($o_query_people){
            $v_return['data'] = $contactpersonId;
            $v_return['status'] = 1;
        }
    }
}
?>
