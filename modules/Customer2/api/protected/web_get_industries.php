<?php
if(isset($v_data['params']['list_id'])){
	$list_id = $v_data['params']['list_id'];
} else {
	$list_id = 1;
}

$getSec = $o_main->db->query("SELECT DISTINCT(customer_selfdefined_list_lines.id) AS id, customer_selfdefined_list_lines.name AS name
FROM customer_selfdefined_list_lines
WHERE customer_selfdefined_list_lines.list_id = ?
GROUP BY customer_selfdefined_list_lines.id ORDER BY customer_selfdefined_list_lines.name", array($list_id));

$str = '';
foreach($getSec->result() AS $ss){
	if($ss->name != ''){
		$str.= '<div class="industry" rel="'.$ss->id.'">';
			$str.= '<a href="#i='.$ss->id.'">';
				$str.= '<span class="name">'.ucfirst(strtolower($ss->name)).'</span>';
			$str.= '</a>';
		$str.= '</div>';
	}
}

$values = array();
foreach($getSec->result_array() AS $ss){
	if($ss['name'] != ''){
		$values[] = $ss;
	}
}

$v_return['data'] = $str;
$v_return['values'] = $values;
?>
