<?php
$customer_id = $v_data['params']['customer_id'];

$s_sql = "SELECT * FROM contactperson WHERE content_status < 2 AND customerId = ?";
$o_query = $o_main->db->query($s_sql, array($customer_id));
$contactpersons = ($o_query ? $o_query->result_array() : array());

$v_return['status'] = 1;
$v_return['contactPersons'] = $contactpersons;
?>
