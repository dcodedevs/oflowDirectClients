<?php

$firmaName = $o_main->db->escape_str($v_data['params']["firma"]);
$firmaNumb = $o_main->db->escape_str($v_data['params']["org"]);
$firmaTel = $o_main->db->escape_str($v_data['params']["phone"]);
$firmaFax = $o_main->db->escape_str($v_data['params']["fax"]);
$firmaEmail = $o_main->db->escape_str($v_data['params']["epost"]);
$firmaWeb = $o_main->db->escape_str($v_data['params']["web"]);

// $oms = $v_data['params']["revenue"];
$antall = $v_data['params']["count"]; $cnt = intval($antall);
$industry = $v_data['params']["industry"];
$industryMulti = $v_data['params']["industryMulti"];
$selfdefined_id = 3;
if(isset($v_data['params']["selfdefined_id"])){
    $selfdefined_id = $v_data['params']["selfdefined_id"];
}
$selfdefined2_id = 3;
if(isset($v_data['params']["selfdefined2_id"])){
    $selfdefined2_id = $v_data['params']["selfdefined2_id"];
}

$selfdefinedfield_dropdown_values = $v_data['params']["selfdefinedfield_dropdown_values"];
$selfdefinedfield_checkbox_values = $v_data['params']["selfdefinedfield_checkbox_values"];

$contact = $v_data['params']['contact'];
$contact = substr($contact, 0, -2);
$bigArray = explode('||', $contact);

$a1 = $v_data['params']["visit_address"];
$p1 = $v_data['params']["visit_post"];
$c1 = $v_data['params']["visit_city"];
$a2 = $v_data['params']["post_address"];
$p2 = $v_data['params']["post_post"];
$c2 = $v_data['params']["post_city"];
$invoiceEmail = "";
if($v_data['params']["invoiceEmail"] != ""){
    $invoiceEmail = $v_data['params']["invoiceEmail"];
}
$INSERT = "INSERT INTO customer SET moduleID = '41', created = NOW()";
$o_query = $o_main->db->query($INSERT);
if($o_query){
    $max = $o_main->db->insert_id();

    $newmax = intval($max+1);

    $UPDATE = "UPDATE customer SET name = '$firmaName', email = '$firmaEmail', phone = '$firmaTel', created = NOW(), publicRegisterId = '$firmaNumb', paStreet = '$a2', paPostalNumber = '$p2', paCity = '$c2',
    vaStreet = '$a1', vaPostalNumber = '$p1', vaCity = '$c1', fax = '$firmaFax', homepage = '$firmaWeb', selfRegisteredDate = NOW(), selfregistered = 1, invoiceEmail = '".$o_main->db->escape_str($invoiceEmail)."'
    WHERE id = '".$max."'";
    $o_query = $o_main->db->query($UPDATE);

    for($i=0;$i<count($bigArray);$i++){
    	$INSERT3 = "INSERT INTO contactperson SET moduleID = '41', created = NOW()";
    	$o_query = $o_main->db->query($INSERT3);
        if($o_query){
            $maxi = $o_main->db->insert_id();
            $smallArray = explode('@@', $bigArray[$i]);
        	$nameArray = explode(')))', $smallArray[0]);
            $name = $nameArray[0];
        	$lastname = $nameArray[1];

        	$UPDATE2 = "UPDATE contactperson SET moduleID = '27', created = NOW(), sortnr = '$maxi', customerId = '$max', name = '$name', lastname = '$lastname', title = '$smallArray[1]', mobile = '$smallArray[2]', email = '$smallArray[3]', displayInMemberpage = 0
        	WHERE id = '".$maxi."'";
        	$o_main->db->query($UPDATE2);
        }
    }
    if($cnt > 0) {
        $INSERT5 = "INSERT INTO customer_selfdefined_values SET created = NOW(), customer_id = '$max', selfdefined_fields_id = ?, value = ?, active = ?";
        $o_query = $o_main->db->query($INSERT5, array($selfdefined2_id, $cnt, 1));
    }
    if(strlen($industry) > 0) {
        $INSERT4 = "INSERT INTO customer_selfdefined_values SET created = NOW(), customer_id = '$max', selfdefined_fields_id = '".$o_main->db->escape_str($selfdefined_id)."', value = '".$o_main->db->escape_str($industry)."'";
    	$o_query = $o_main->db->query($INSERT4);
        if($o_query) {
            $valueId = $o_main->db->insert_id();

            $multiConfig = false;
            $getSelf = $o_main->db->query("SELECT * FROM customer_selfdefined_fields WHERE id = '".$o_main->db->escape_str($selfdefined_id)."'");
    		$selfdefinedField = $getSelf ? $getSelf->row_array(): array();
    		if($selfdefinedField){
    			if($selfdefinedField['type'] == 2){
                    $multiConfig = true;
                }
            }
            if($industryMulti || $multiConfig){
                $INSERT41 = "INSERT INTO customer_selfdefined_values_connection SET created = NOW(), selfdefined_value_id = '".$o_main->db->escape_str($valueId)."', selfdefined_list_line_id = '".$o_main->db->escape_str($industry)."'";
            	$o_query = $o_main->db->query($INSERT41);
            }
        }
    }
    if(count($selfdefinedfield_dropdown_values) > 0){
        foreach($selfdefinedfield_dropdown_values as $selfdefinedfield_dropdown_value){
            $INSERT4 = "INSERT INTO customer_selfdefined_values SET created = NOW(), customer_id = '$max', selfdefined_fields_id = '".$o_main->db->escape_str($selfdefinedfield_dropdown_value['id'])."', value = '".$o_main->db->escape_str($selfdefinedfield_dropdown_value['value'])."'";
        	$o_query = $o_main->db->query($INSERT4);
        }
    }
    if(count($selfdefinedfield_checkbox_values) > 0){
        foreach($selfdefinedfield_checkbox_values as $selfdefinedfield_checkbox_value){
            $INSERT4 = "INSERT INTO customer_selfdefined_values SET created = NOW(), customer_id = '$max', selfdefined_fields_id = '".$o_main->db->escape_str($selfdefinedfield_checkbox_value['id'])."', value = '".$o_main->db->escape_str($selfdefinedfield_checkbox_value['value'])."', active = 1";
        	$o_query = $o_main->db->query($INSERT4);
        }
    }

    // if($valueId > 0){
    //     $INSERT5 = "INSERT INTO customer_selfdefined_values_connection SET created = NOW(), selfdefined_value_id = '$valueId', selfdefined_list_line_id = '$industry'";
    // 	$o_query = $o_main->db->query($INSERT5);
    // }


    $v_return['status'] = 1;
}

?>
