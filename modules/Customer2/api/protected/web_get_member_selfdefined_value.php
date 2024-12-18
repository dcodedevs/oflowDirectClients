<?php

$det = $v_data['params']['det'];
$selfdefined_id = $v_data['params']['selfdefined_id'];

$getInd = $o_main->db->query("SELECT customer_selfdefined_values.* FROM customer_selfdefined_values
LEFT OUTER JOIN customer ON customer.id = customer_selfdefined_values.customer_id
LEFT OUTER JOIN customer_selfdefined_list_lines ON customer_selfdefined_list_lines.id  = customer_selfdefined_values.value
WHERE customer_selfdefined_values.customer_id = '$det' AND customer_selfdefined_values.selfdefined_fields_id = '".$selfdefined_id."' AND customer_selfdefined_values.active = 1");


$bs = '';
$values = array();
foreach($getInd->result() AS $in){
	if(strlen($in->value) != ""){ $bs .= $in->value.', '; }
	$values[] = $in->value;
}

$bs = substr($bs, 0, -2);

$v_return['data'] = $bs;
$v_return['values'] = $values;

?>
