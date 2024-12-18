<?php

$det = $v_data['params']['det'];
$selfdefinedfields_to_include = $v_data['params']['selfdefinedfields_to_include'];

$getComp = $o_main->db->query("SELECT * FROM customer WHERE id = '".$o_main->db->escape_str($det)."'");
$p = $getComp->row();
if(count($selfdefinedfields_to_include) > 0){
    $p->selfdefinedfields = array();
    foreach($selfdefinedfields_to_include as $selfdefinedfield_to_include_array) {
        $selfdefinedfield_to_include = $selfdefinedfield_to_include_array['id'];
        $selfdefinedfield_values = array();

        $s_sql = "SELECT * FROM customer_selfdefined_fields WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($selfdefinedfield_to_include));
        $predefinedField = $o_query ? $o_query->row_array() : array();
        $value = "";
        if($predefinedField) {
            $s_sql = "SELECT * FROM customer_selfdefined_values WHERE selfdefined_fields_id = '".$o_main->db->escape_str($selfdefinedfield_to_include)."' AND customer_id = '".$o_main->db->escape_str($p->id)."'";
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
            array_push($p->selfdefinedfields, array('name'=>$predefinedField['name'], 'values'=>$selfdefinedfield_values));
        }
    }
}
$v_return['data'] = $p;

?>
