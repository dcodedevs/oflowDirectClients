<?php

$rel = $v_data['params']['rel'];
$link = $v_data['params']['link'];
$partial = $v_data['params']['partial'];
$type = $v_data['params']['type'];
$hide_link = isset($v_data['params']['hide_link']) ? $v_data['params']['hide_link'] : 0;
$selfdefinedfields_to_include = $v_data['params']['selfdefinedfields_to_include'];
$selfdefinedfields_to_filterby = $v_data['params']['selfdefinedfields_to_filterby'];
$selfdefinedfields_to_filterby_value = $v_data['params']['selfdefinedfields_to_filterby_value'];

// $getComp = $o_main->db->query("SELECT customer.* FROM customer
// LEFT JOIN
// 	(SELECT subscriptionmulti.startDate, subscriptionmulti.id, subscriptionmulti.customerId, MIN(subscriptionmulti.stoppedDate) AS stoppedDate, subscriptionmulti.extraCheckbox FROM subscriptionmulti
// 		WHERE subscriptionmulti.customerId <> 0 GROUP by subscriptionmulti.customerId) subscriptionmulti
// 	ON subscriptionmulti.customerId = customer.id
// WHERE (subscriptionmulti.extraCheckbox = 0 OR subscriptionmulti.extraCheckbox is null) AND ((subscriptionmulti.startDate is not null AND subscriptionmulti.startDate <> '0000-00-00') AND (subscriptionmulti.stoppedDate is null OR subscriptionmulti.stoppedDate = '0000-00-00'))
// ORDER BY name");
$sql_join_selfdefined = "";
$sql_filter_selfdefined = "";
if(count($selfdefinedfields_to_filterby) > 0){
	$escapedIds = array();
	foreach($selfdefinedfields_to_filterby as $selfdefinedfield_to_filterby){
		$escapedIds[] = $o_main->db->escape_str($selfdefinedfield_to_filterby);
	}
	$sql_join_selfdefined = " LEFT OUTER JOIN customer_selfdefined_values csv ON customer.id = csv.customer_id ";
	$sql_filter_selfdefined .= " AND csv.selfdefined_fields_id IN (".implode(",",$escapedIds).") AND csv.active = 1";
}
if(count($selfdefinedfields_to_filterby_value) == 2){
	$sql_join_selfdefined .= " LEFT OUTER JOIN customer_selfdefined_values csv2 ON customer.id = csv2.customer_id ";
	$sql_filter_selfdefined .= " AND csv2.selfdefined_fields_id = '".$o_main->db->escape_str($selfdefinedfields_to_filterby_value['id'])."' AND csv2.value = '".$o_main->db->escape_str($selfdefinedfields_to_filterby_value['value'])."'";
}

if($type == 'full'){

	$getComp = $o_main->db->query("SELECT customer.* FROM customer
	LEFT JOIN
		(SELECT subscriptionmulti.startDate, subscriptionmulti.id, subscriptionmulti.customerId, MIN(subscriptionmulti.stoppedDate) AS stoppedDate, subscriptionmulti.extraCheckbox FROM subscriptionmulti
			WHERE subscriptionmulti.customerId <> 0 AND subscriptionmulti.content_status < 2 GROUP by subscriptionmulti.customerId) subscriptionmulti
		ON subscriptionmulti.customerId = customer.id
	".$sql_join_selfdefined."
	WHERE customer.content_status <> '2' ".$sql_filter_selfdefined." AND (subscriptionmulti.extraCheckbox = 0 OR subscriptionmulti.extraCheckbox is null) AND ((subscriptionmulti.startDate is not null AND subscriptionmulti.startDate <> '0000-00-00') AND (subscriptionmulti.stoppedDate is null OR subscriptionmulti.stoppedDate = '0000-00-00'))
	GROUP BY customer.id ORDER BY name");

} else if($type == 'letter'){

	$getComp = $o_main->db->query("SELECT customer.* FROM customer
	LEFT JOIN
		(SELECT subscriptionmulti.startDate, subscriptionmulti.id, subscriptionmulti.customerId, MIN(subscriptionmulti.stoppedDate) AS stoppedDate, subscriptionmulti.extraCheckbox FROM subscriptionmulti
			WHERE subscriptionmulti.customerId <> 0 AND subscriptionmulti.content_status < 2 GROUP by subscriptionmulti.customerId) subscriptionmulti
		ON subscriptionmulti.customerId = customer.id
	".$sql_join_selfdefined."
	WHERE customer.content_status <> '2' ".$sql_filter_selfdefined."  AND customer.name LIKE '".$o_main->db->escape_str($rel)."%' AND (subscriptionmulti.extraCheckbox = 0 OR subscriptionmulti.extraCheckbox is null) AND ((subscriptionmulti.startDate is not null AND subscriptionmulti.startDate <> '0000-00-00') AND (subscriptionmulti.stoppedDate is null OR subscriptionmulti.stoppedDate = '0000-00-00'))
	GROUP BY customer.id ORDER BY name");

} else if($type == 'search'){

	$getComp = $o_main->db->query("SELECT customer.* FROM customer
	LEFT JOIN
		(SELECT subscriptionmulti.startDate, subscriptionmulti.id, subscriptionmulti.customerId, MIN(subscriptionmulti.stoppedDate) AS stoppedDate, subscriptionmulti.extraCheckbox FROM subscriptionmulti
			WHERE subscriptionmulti.customerId <> 0 AND subscriptionmulti.content_status < 2 GROUP by subscriptionmulti.customerId) subscriptionmulti
		ON subscriptionmulti.customerId = customer.id
	".$sql_join_selfdefined."
	WHERE customer.content_status <> '2' ".$sql_filter_selfdefined." AND customer.name LIKE '%".$o_main->db->escape_str($rel)."%' AND (subscriptionmulti.extraCheckbox = 0 OR subscriptionmulti.extraCheckbox is null) AND ((subscriptionmulti.startDate is not null AND subscriptionmulti.startDate <> '0000-00-00') AND (subscriptionmulti.stoppedDate is null OR subscriptionmulti.stoppedDate = '0000-00-00'))
	GROUP BY customer.id ORDER BY name");

} else if($type == 'industry'){
	$selfdefined_sql = "";
	if(isset($v_data['params']['selfdefined_id'])){
		$selfdefined_sql = " AND customer_selfdefined_values.selfdefined_fields_id = '".$o_main->db->escape_str($v_data['params']['selfdefined_id'])."'";
	}
	$getComp = $o_main->db->query("SELECT customer.* FROM customer
	LEFT OUTER JOIN customer_selfdefined_values ON customer.id = customer_selfdefined_values.customer_id
	LEFT OUTER JOIN customer_selfdefined_list_lines ON customer_selfdefined_list_lines.id  = customer_selfdefined_values.value
	LEFT JOIN
		(SELECT subscriptionmulti.startDate, subscriptionmulti.id, subscriptionmulti.customerId, MIN(subscriptionmulti.stoppedDate) AS stoppedDate, subscriptionmulti.extraCheckbox FROM subscriptionmulti
			WHERE subscriptionmulti.customerId <> 0 AND subscriptionmulti.content_status < 2 GROUP by subscriptionmulti.customerId) subscriptionmulti
		ON subscriptionmulti.customerId = customer.id
	".$sql_join_selfdefined."
	WHERE customer.content_status <> '2' ".$sql_filter_selfdefined." ".$selfdefined_sql." AND customer_selfdefined_list_lines.id = '".$o_main->db->escape_str($rel)."' AND (subscriptionmulti.extraCheckbox = 0 OR subscriptionmulti.extraCheckbox is null) AND ((subscriptionmulti.startDate is not null AND subscriptionmulti.startDate <> '0000-00-00') AND (subscriptionmulti.stoppedDate is null OR subscriptionmulti.stoppedDate = '0000-00-00'))
	GROUP BY customer.id
	ORDER BY customer.name");

}

$nums = $getComp->num_rows();

$str = '';
foreach($getComp->result() AS $c){

	$ids = $c->id;
	$home = $c->homepage;
	$homeArr = explode('www', $c->homepage);
	$homeArr2 = explode('http', $c->homepage);
	if(strlen($homeArr[1]) > 0){
		$page_part = $homeArr[1];
	} else {
		$page_part = '.'.$homeArr2[1];
	}

	if(substr($page_part, 0, 4) == ".://"){ $page_part = '.'.substr($page_part, 4); }
	if(substr($page_part, 0, 5) == ".s://"){ $page_part = '.'.substr($page_part, 5); }

	if(substr($c->homepage, 0, 7) != "http://" && substr($c->homepage, 0, 8) != "https://"){
		$c->homepage = 'http://'.$c->homepage;
	} else {
		$c->homepage = $c->homepage;
	}

	if($page_part == '.'){ $page_part = '.'.$home; }

	$str .= '<div class="company trans" rel="'.$c->id.'">';
		if(!$hide_link){
			$str .= '<a class="bigL" href="'.$link.'?details='.$ids.'">';
		}
			$str .= '<div class="wrap">';
				$str .= '<div class="name">'.$c->name.'</div>';
				if(strlen($c->email) > 0){ $str .= '<div class="email"><img src="'.$partial.'elementsGlobal/envelope.svg" alt="">'; $str .= '<a href="mailto:'.$c->email.'"><span>'.$c->email; $str .= '</span>'; $str .= '</a></div>'; }
				if(strlen(trim($home)) > 0){ $str .= '<div class="web"><img src="'.$partial.'elementsGlobal/globe.svg" alt="">'; $str .= '<a target="_blank" href="'.$c->homepage.'"><span>www'.$page_part; $str .= '</span>'; $str .= '</a></div>'; }

				if(count($selfdefinedfields_to_include) > 0){

				    foreach($selfdefinedfields_to_include as $selfdefinedfield_to_include_array) {
						$selfdefinedfield_to_include = $selfdefinedfield_to_include_array['id'];
				        $selfdefinedfield_values = array();

				        $s_sql = "SELECT * FROM customer_selfdefined_fields WHERE id = ?";
				        $o_query = $o_main->db->query($s_sql, array($selfdefinedfield_to_include));
				        $predefinedField = $o_query ? $o_query->row_array() : array();
				        if($predefinedField) {
				            $s_sql = "SELECT * FROM customer_selfdefined_values WHERE selfdefined_fields_id = '".$o_main->db->escape_str($selfdefinedfield_to_include)."' AND customer_id = '".$o_main->db->escape_str($c->id)."'";
				            $o_query = $o_main->db->query($s_sql);
				            $v_selfdefined_field_value = $o_query ? $o_query->row_array() : array();

				            $s_sql = "SELECT * FROM customer_selfdefined_lists WHERE id = ?";
				            $o_query = $o_main->db->query($s_sql, array($predefinedField['list_id']));
				            $selfdefinedList = $o_query ? $o_query->row_array() : array();
				            if($predefinedField['type'] == 1) {
				                $s_sql = "SELECT * FROM customer_selfdefined_list_lines WHERE list_id = ? AND id = ? ORDER BY name ASC";
				                $o_query = $o_main->db->query($s_sql, array($selfdefinedList['id'], $v_selfdefined_field_value['value']));
				                $singleResource = $o_query ? $o_query->row_array() : array();
				                $selfdefinedfield_values[] = $singleResource['name'];
				            }else if($predefinedField['type'] == 2) {
				                $selfdefinedListLines = array();
				                $s_sql = "SELECT * FROM customer_selfdefined_list_lines JOIN customer_selfdefined_values_connection ON customer_selfdefined_values_connection.selfdefined_list_line_id = customer_selfdefined_list_lines.id
				                    WHERE customer_selfdefined_values_connection.selfdefined_value_id = ?";
				                $o_query = $o_main->db->query($s_sql, array($v_selfdefined_field_value['id']));
				                if($o_query && $o_query->num_rows()>0){
				                    $selfdefinedListLines = $o_query->result_array();
				                }
				                foreach($selfdefinedListLines as $selfdefinedListLine){
				                    $selfdefinedfield_values[] = $selfdefinedListLine['name'];
				                }

				            } else if($predefinedField['type'] == 0) {
				                if($v_selfdefined_field_value['active']) {
				                    if(!$predefinedField['hide_textfield']) {
				                        $selfdefinedfield_values[] = $v_selfdefined_field_value['value'];
				                    } else {
				                        $s_sql = "SELECT customer_selfdefined_lists.* FROM customer_selfdefined_lists_connection
				                        LEFT OUTER JOIN customer_selfdefined_lists ON customer_selfdefined_lists.id = customer_selfdefined_lists_connection.customer_selfdefined_list_id
				                        WHERE customer_selfdefined_field_id = ?";
				                        $o_query = $o_main->db->query($s_sql, array($predefinedField['id']));
				                        $selfdefinedLists = $o_query ? $o_query->result_array() : array();
				                        if(count($selfdefinedLists) > 0) {
				                            foreach($selfdefinedLists as $connection){

				                                $s_sql = "SELECT customer_selfdefined_list_lines.* FROM customer_selfdefined_values_connection
				                                LEFT OUTER JOIN customer_selfdefined_list_lines ON customer_selfdefined_list_lines.id = customer_selfdefined_values_connection.selfdefined_list_line_id
				                                WHERE customer_selfdefined_values_connection.selfdefined_value_id = ? AND customer_selfdefined_list_lines.list_id = ?";
				                                $o_query = $o_main->db->query($s_sql, array($v_selfdefined_field_value['id'], $connection['id']));
				                                $values = $o_query ? $o_query->result_array() : array();
				                                foreach($values as $singleValue) {
				                                    $selfdefinedfield_values[] = $singleValue['name'];
				                                }
				                            }
				                        } else {
				                            $selfdefinedfield_values[] = $v_selfdefined_field_value['value'];
				                        }
				                    }
				                }
				            }
				        }
						if(count($selfdefinedfield_values) > 0){ $str .= '<div class="selfdefinedField selfdefinedField'.$selfdefinedfield_to_include.'">';
							if($selfdefinedfield_to_include_array['image'] != ""){
								$str .= '<img src="'.$partial.'elementsGlobal/'.$selfdefinedfield_to_include_array['image'].'" alt="">';
							}
							$str .= '<span>'.$selfdefinedfield_to_include_array['prefix']." ".$predefinedField['name']. " ". implode(",", $selfdefinedfield_values);
							$str .= '</span>';
							$str .= '</div>';
						}
				    }
				}

			$str .= '</div>';
		if(!$hide_link){
			$str .= '</a>';
		}
	$str .= '</div>';
}

$v_return['data'][0] = $str;
$v_return['data'][1] = $nums;

?>
