<?php

$list_id = $v_data['params']['list_id'];

$getSec = $o_main->db->query("SELECT * FROM customer_selfdefined_list_lines WHERE list_id = ? ORDER BY name ASC", array( $list_id));
$lines = $getSec ? $getSec->result_array() : array();

$v_return['data'] = $lines;
?>
