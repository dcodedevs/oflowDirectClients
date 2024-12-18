<?php

$det = $v_data['params']['det'];
$selfdefined_id = $v_data['params']['selfdefined_id'];
$selfdefined_sql = "";
$bs = '';
if(intval($selfdefined_id) > 0){
	$getSelf = $o_main->db->query("SELECT * FROM customer_selfdefined_fields WHERE id = '".$o_main->db->escape_str($selfdefined_id)."'");
	$selfdefinedField = $getSelf ? $getSelf->row_array(): array();
	if($selfdefinedField){
		if($selfdefinedField['type'] == 2){
			$selfdefined_sql = " AND customer_selfdefined_values.selfdefined_fields_id = '".$selfdefined_id."'";

			$getInd = $o_main->db->query("SELECT DISTINCT(customer_selfdefined_list_lines.name) AS name FROM customer_selfdefined_values
			LEFT OUTER JOIN customer ON customer.id = customer_selfdefined_values.customer_id
			LEFT OUTER JOIN customer_selfdefined_values_connection ON customer_selfdefined_values_connection.selfdefined_value_id = customer_selfdefined_values.id
			LEFT OUTER JOIN customer_selfdefined_list_lines ON customer_selfdefined_list_lines.id  = customer_selfdefined_values_connection.selfdefined_list_line_id
			WHERE customer_selfdefined_values.customer_id = '$det' ".$selfdefined_sql);
		} else {
			$selfdefined_sql = " AND customer_selfdefined_values.selfdefined_fields_id = '".$selfdefined_id."'";

			$getInd = $o_main->db->query("SELECT DISTINCT(customer_selfdefined_list_lines.name) AS name FROM customer_selfdefined_values
			LEFT OUTER JOIN customer ON customer.id = customer_selfdefined_values.customer_id
			LEFT OUTER JOIN customer_selfdefined_list_lines ON customer_selfdefined_list_lines.id  = customer_selfdefined_values.value
			WHERE customer_selfdefined_values.customer_id = '$det' ".$selfdefined_sql);
		}
	}
}else {
	$getInd = $o_main->db->query("SELECT DISTINCT(customer_selfdefined_list_lines.name) AS name FROM customer_selfdefined_values
	LEFT OUTER JOIN customer ON customer.id = customer_selfdefined_values.customer_id
	LEFT OUTER JOIN customer_selfdefined_list_lines ON customer_selfdefined_list_lines.id  = customer_selfdefined_values.value
	WHERE customer_selfdefined_values.customer_id = '$det' AND customer_selfdefined_values.active = '1'");
}

foreach($getInd->result() AS $in){
	if(strlen($in->name) > 1){ $bs .= $in->name.', '; }
}

$bs = substr($bs, 0, -2);

$v_return['data'] = $bs;

?>
