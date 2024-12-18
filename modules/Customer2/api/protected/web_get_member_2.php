<?php

$rel = $v_data['params']['rel'];
$type = $v_data['params']['type'];
$shopname_order = $v_data['params']['shopname_order'];
$date_order = $v_data['params']['date_order'];

$selfdefinedfields_to_include = $v_data['params']['selfdefinedfields_to_include'];
$selfdefinedfields_to_filterby = $v_data['params']['selfdefinedfields_to_filterby'];
$selfdefinedfields_to_filterby_value = $v_data['params']['selfdefinedfields_to_filterby_value'];

$subscriptionsubtypes_to_filterby = $v_data['params']['subscriptionsubtypes_to_filterby'];

$selfdefinedfield_act_as_subscription = $v_data['params']['selfdefinedfield_act_as_subscription'];

// $getComp = $o_main->db->query("SELECT customer.* FROM customer
// LEFT JOIN
// 	(SELECT subscriptionmulti.startDate, subscriptionmulti.id, subscriptionmulti.customerId, MIN(subscriptionmulti.stoppedDate) AS stoppedDate, subscriptionmulti.extraCheckbox FROM subscriptionmulti
// 		WHERE subscriptionmulti.customerId <> 0 GROUP by subscriptionmulti.customerId) subscriptionmulti
// 	ON subscriptionmulti.customerId = customer.id
// WHERE (subscriptionmulti.extraCheckbox = 0 OR subscriptionmulti.extraCheckbox is null) AND ((subscriptionmulti.startDate is not null AND subscriptionmulti.startDate <> '0000-00-00') AND (subscriptionmulti.stoppedDate is null OR subscriptionmulti.stoppedDate = '0000-00-00'))
// ORDER BY name");
$sql_select = "";
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
if(count($selfdefinedfields_to_filterby_value) > 0){
	if($selfdefinedfields_to_filterby_value['type'] == "multi"){
		$sql_join_selfdefined .= " LEFT OUTER JOIN customer_selfdefined_values csv2 ON customer.id = csv2.customer_id
		LEFT OUTER JOIN  customer_selfdefined_values_connection csvc ON csvc.selfdefined_value_id = csv2.id";
		$sql_filter_selfdefined .= " AND csv2.selfdefined_fields_id = '".$o_main->db->escape_str($selfdefinedfields_to_filterby_value['id'])."' AND csvc.selfdefined_list_line_id = '".$o_main->db->escape_str($selfdefinedfields_to_filterby_value['value'])."'";
	} else {
		$sql_join_selfdefined .= " LEFT OUTER JOIN customer_selfdefined_values csv2 ON customer.id = csv2.customer_id ";
		$sql_filter_selfdefined .= " AND csv2.selfdefined_fields_id = '".$o_main->db->escape_str($selfdefinedfields_to_filterby_value['id'])."' AND csv2.value = '".$o_main->db->escape_str($selfdefinedfields_to_filterby_value['value'])."'";
	}
}

$sql_join_subscriptionsubtypes = "";
$sql_filter_subscriptionsubtypes = "";
$sql_filter_subscriptionsubtypes = "";

if(count($subscriptionsubtypes_to_filterby) > 0){
    $sql_select_selfdefined .= ", subSubtype.as subscriptionSubtypeName";
	$sql_join_subscriptionsubtypes .= " LEFT OUTER JOIN subscriptionmulti s ON customer.id = s.customerId LEFT OUTER JOIN subscriptiontype_subtype subSubtype ON subSubtype.id = s.subscriptionsubtypeId ";
	$sql_filter_subscriptionsubtypes .= " AND s.startDate < CURDATE() AND (s.stoppedDate = '0000-00-00' OR s.stoppedDate IS NULL OR (s.stoppedDate >= CURDATE())) AND s.content_status < 2 AND s.subscriptionsubtypeId IN (".implode(",", $subscriptionsubtypes_to_filterby).")";
}
$orderBy_sql = " ORDER BY name ASC";
if($shopname_order){
	$orderBy_sql = " ORDER BY order_by_shop_name ASC";
}
if($date_order){
	$orderBy_sql = " ORDER BY startDate DESC";
}
$actAsSubscription_sql = " AND (subscriptionmulti.content_status < 2 AND (subscriptionmulti.startDate is not null AND subscriptionmulti.startDate <> '0000-00-00') AND (subscriptionmulti.stoppedDate is null OR subscriptionmulti.stoppedDate = '0000-00-00'))";
if($selfdefinedfield_act_as_subscription > 0) {
	$sql_select .= ", IF(csv_sub.active = 1, 0, 1) as realSubscription";
	$sql_join_selfdefined .= " LEFT OUTER JOIN customer_selfdefined_values csv_sub ON customer.id = csv_sub.customer_id AND csv_sub.selfdefined_fields_id = '".$o_main->db->escape_str($selfdefinedfield_act_as_subscription)."'";
	$actAsSubscription_sql = " AND ((subscriptionmulti.content_status < 2 AND (subscriptionmulti.startDate is not null AND subscriptionmulti.startDate <> '0000-00-00') AND (subscriptionmulti.stoppedDate is null OR subscriptionmulti.stoppedDate = '0000-00-00')) OR csv_sub.active = 1) ";
}
if($type == 'full'){
	$getComp = $o_main->db->query("SELECT customer.*, IF(CHAR_LENGTH(customer.shop_name) > 1, customer.shop_name, customer.name) order_by_shop_name".$sql_select." FROM customer
	LEFT JOIN
		(SELECT subscriptionmulti.startDate, subscriptionmulti.id, subscriptionmulti.customerId, MIN(subscriptionmulti.stoppedDate) AS stoppedDate, subscriptionmulti.extraCheckbox, subscriptionmulti.content_status FROM subscriptionmulti
			WHERE subscriptionmulti.customerId <> 0 AND subscriptionmulti.content_status < 2  GROUP by subscriptionmulti.customerId) subscriptionmulti
		ON subscriptionmulti.customerId = customer.id
	".$sql_join_selfdefined.$sql_join_subscriptionsubtypes."
	WHERE customer.content_status <> '2' ".$sql_filter_selfdefined.$sql_filter_subscriptionsubtypes." AND (subscriptionmulti.extraCheckbox = 0 OR subscriptionmulti.extraCheckbox is null)
	".$actAsSubscription_sql."
	GROUP BY customer.id ".$orderBy_sql);

} else if($type == 'letter'){

	$getComp = $o_main->db->query("SELECT customer.*, IF(CHAR_LENGTH(customer.shop_name) > 1, customer.shop_name, customer.name) order_by_shop_name".$sql_select." FROM customer
	LEFT JOIN
		(SELECT subscriptionmulti.startDate, subscriptionmulti.id, subscriptionmulti.customerId, MIN(subscriptionmulti.stoppedDate) AS stoppedDate, subscriptionmulti.extraCheckbox, subscriptionmulti.content_status FROM subscriptionmulti
			WHERE subscriptionmulti.customerId <> 0 AND subscriptionmulti.content_status < 2  GROUP by subscriptionmulti.customerId) subscriptionmulti
		ON subscriptionmulti.customerId = customer.id
	".$sql_join_selfdefined.$sql_join_subscriptionsubtypes."
	WHERE customer.content_status <> '2' ".$sql_filter_selfdefined.$sql_filter_subscriptionsubtypes."  AND customer.name LIKE '".$o_main->db->escape_str($rel)."%' AND (subscriptionmulti.extraCheckbox = 0 OR subscriptionmulti.extraCheckbox is null)
	".$actAsSubscription_sql."
	GROUP BY customer.id ".$orderBy_sql);

} else if($type == 'search'){
	$getComp = $o_main->db->query("SELECT customer.*, IF(CHAR_LENGTH(customer.shop_name) > 1, customer.shop_name, customer.name) order_by_shop_name".$sql_select." FROM customer
	LEFT JOIN
		(SELECT subscriptionmulti.startDate, subscriptionmulti.id, subscriptionmulti.customerId, MIN(subscriptionmulti.stoppedDate) AS stoppedDate, subscriptionmulti.extraCheckbox, subscriptionmulti.content_status FROM subscriptionmulti
			WHERE subscriptionmulti.customerId <> 0 AND subscriptionmulti.content_status < 2  GROUP by subscriptionmulti.customerId) subscriptionmulti
		ON subscriptionmulti.customerId = customer.id
	".$sql_join_selfdefined.$sql_join_subscriptionsubtypes."
	WHERE customer.content_status <> '2' ".$sql_filter_selfdefined.$sql_filter_subscriptionsubtypes." AND (customer.name LIKE '%".$o_main->db->escape_str($rel)."%' OR customer.shop_name LIKE '%".$o_main->db->escape_str($rel)."%') AND (subscriptionmulti.extraCheckbox = 0 OR subscriptionmulti.extraCheckbox is null)
	".$actAsSubscription_sql."
	GROUP BY customer.id ".$orderBy_sql);

} else if($type == 'industry'){
	$selfdefined_sql = "";
	if(isset($v_data['params']['selfdefined_id'])){
		$selfdefined_sql = " AND customer_selfdefined_values.selfdefined_fields_id = '".$o_main->db->escape_str($v_data['params']['selfdefined_id'])."'";
		$listline_sql = "LEFT OUTER JOIN customer_selfdefined_list_lines ON customer_selfdefined_list_lines.id  = customer_selfdefined_values.value";

		$getSelf = $o_main->db->query("SELECT * FROM customer_selfdefined_fields WHERE id = '".$o_main->db->escape_str($v_data['params']['selfdefined_id'])."'");
		$selfdefinedField = $getSelf ? $getSelf->row_array(): array();
		if($selfdefinedField){
			if($selfdefinedField['type'] == 2){
				$listline_sql = "LEFT OUTER JOIN customer_selfdefined_values_connection ON customer_selfdefined_values_connection.selfdefined_value_id = customer_selfdefined_values.id
				LEFT OUTER JOIN customer_selfdefined_list_lines ON customer_selfdefined_list_lines.id  = customer_selfdefined_values_connection.selfdefined_list_line_id";
			}
		}
	}
	$getComp = $o_main->db->query("SELECT customer.*, IF(CHAR_LENGTH(customer.shop_name) > 1, customer.shop_name, customer.name) order_by_shop_name".$sql_select." FROM customer
	LEFT OUTER JOIN customer_selfdefined_values ON customer.id = customer_selfdefined_values.customer_id
	".$listline_sql."
	LEFT JOIN
		(SELECT subscriptionmulti.startDate, subscriptionmulti.id, subscriptionmulti.customerId, MIN(subscriptionmulti.stoppedDate) AS stoppedDate, subscriptionmulti.extraCheckbox, subscriptionmulti.content_status FROM subscriptionmulti
			WHERE subscriptionmulti.customerId <> 0 AND subscriptionmulti.content_status < 2 GROUP by subscriptionmulti.customerId) subscriptionmulti
		ON subscriptionmulti.customerId = customer.id
	".$sql_join_selfdefined.$sql_join_subscriptionsubtypes."
	WHERE customer.content_status <> '2' ".$sql_filter_selfdefined.$sql_filter_subscriptionsubtypes." ".$selfdefined_sql." AND customer_selfdefined_list_lines.id = '".$o_main->db->escape_str($rel)."' AND (subscriptionmulti.extraCheckbox = 0 OR subscriptionmulti.extraCheckbox is null)
	".$actAsSubscription_sql."
	GROUP BY customer.id ".$orderBy_sql);
} else if($type == "latest"){
	$orderBy_sql = " ORDER BY startDate DESC LIMIT 6";
	$getComp = $o_main->db->query("SELECT customer.*, IF(CHAR_LENGTH(customer.shop_name) > 1, customer.shop_name, customer.name) order_by_shop_name".$sql_select." FROM customer
	LEFT JOIN
		(SELECT subscriptionmulti.startDate, subscriptionmulti.id, subscriptionmulti.customerId, MIN(subscriptionmulti.stoppedDate) AS stoppedDate, subscriptionmulti.extraCheckbox, subscriptionmulti.content_status FROM subscriptionmulti
			WHERE subscriptionmulti.customerId <> 0 AND subscriptionmulti.content_status < 2  GROUP by subscriptionmulti.customerId) subscriptionmulti
		ON subscriptionmulti.customerId = customer.id
	".$sql_join_selfdefined.$sql_join_subscriptionsubtypes."
	WHERE customer.content_status <> '2'  ".$sql_filter_selfdefined.$sql_filter_subscriptionsubtypes." AND (subscriptionmulti.extraCheckbox = 0 OR subscriptionmulti.extraCheckbox is null)
	".$actAsSubscription_sql."
	GROUP BY customer.id ".$orderBy_sql);

}

$nums = $getComp->num_rows();

$str = '';
foreach($getComp->result_array() AS $c){
    $c['selfdefinedfields'] = array();
    foreach($selfdefinedfields_to_include as $selfdefinedfield_to_include_array) {
        $selfdefinedfield_to_include = $selfdefinedfield_to_include_array['id'];
        $selfdefinedfield_values = array();

        $s_sql = "SELECT * FROM customer_selfdefined_fields WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($selfdefinedfield_to_include));
        $predefinedField = $o_query ? $o_query->row_array() : array();
        $value = "";
        if($predefinedField) {
            $s_sql = "SELECT * FROM customer_selfdefined_values WHERE selfdefined_fields_id = '".$o_main->db->escape_str($selfdefinedfield_to_include)."' AND customer_id = '".$o_main->db->escape_str($c['id'])."'";
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
        if(count($selfdefinedfield_values) > 0){
            array_push($c['selfdefinedfields'], array('name'=>$predefinedField['name'], 'values'=>$selfdefinedfield_values));
        }
    }
    $customers[] = $c;
}
$v_return['data']['customers'] = $customers;
$v_return['data']['count'] = $nums;

?>
