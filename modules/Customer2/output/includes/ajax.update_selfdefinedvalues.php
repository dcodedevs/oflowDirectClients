<?php
$selfdefinedFieldId = $_POST['selfdefinedFieldId'] ? ($_POST['selfdefinedFieldId']) : 0;
$customer_id = ($_POST['customerId']);

if($selfdefinedFieldId > 0 && $customer_id > 0){
	$s_sql = "SELECT * FROM subscriptionmulti WHERE customerId = ? AND content_status < 2";
	$o_query = $o_main->db->query($s_sql, array($customer_id));
	$subscriptions = ($o_query ? $o_query->result_array() : array());
	$subscriptiontypes = array();
	foreach($subscriptions as $subscription) {
		$s_sql = "SELECT * FROM subscriptiontype WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($subscription['subscriptiontype_id']));
		$subscriptiontype = ($o_query ? $o_query->row_array() : array());
		if($subscriptiontype){
			$subscriptiontypes[] = $subscriptiontype;
		}
	}
	$selfdefinedFieldSubscription = array();
	foreach($subscriptiontypes as $subscriptiontype) {
		$s_sql = "SELECT subscriptiontype_selfdefined_connection.*, customer_selfdefined_fields.* FROM subscriptiontype_selfdefined_connection
		LEFT OUTER JOIN customer_selfdefined_fields ON customer_selfdefined_fields.id = subscriptiontype_selfdefined_connection.selfdefinedfield_id
		WHERE subscriptiontype_selfdefined_connection.subscriptiontype_id = ? ORDER BY customer_selfdefined_fields.name ASC";
		$o_query = $o_main->db->query($s_sql, array($subscriptiontype['id']));
		$selfdefinedFields = $o_query ? $o_query->result_array() : array();

		if(count($selfdefinedFields) > 0) {
			foreach($selfdefinedFields as $selfdefinedField) {
				if($selfdefinedField['id'] == $selfdefinedFieldId) {
					$selfdefinedFieldSubscription = $selfdefinedField;
				}
			}
		}
	}

	$list_id = $_POST['listid'];
	$s_sql = "SELECT * FROM customer_selfdefined_fields WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($selfdefinedFieldId));
    if($o_query && $o_query->num_rows()>0){
        $selfdefinedField = $o_query->row_array();
    }
	if(!$selfdefinedField){
		$fw_error_msg[] = $formText_MissingSelfdefinedField_output;
		return;
	}
	if($selfdefinedFieldSubscription){
		if(!$selfdefinedFieldSubscription['not_mandatory']){
			$valid = true;

			if($selfdefinedFieldSubscription['type'] == 2){
				if($_POST['lineChecked'] == "false"){
					$missingValues = true;

					$s_sql = "SELECT * FROM customer_selfdefined_values WHERE customer_id = ? AND selfdefined_fields_id = ?";
				    $o_query = $o_main->db->query($s_sql, array($customer_id, $selfdefinedFieldSubscription['id']));
			        $selfdefinedValue = $o_query->row_array();

					$s_sql = "SELECT * FROM customer_selfdefined_values_connection  WHERE selfdefined_value_id = ?";
				    $o_query = $o_main->db->query($s_sql, array($selfdefinedValue['id']));
					$valueItems = $o_query ? $o_query->result_array() : array();
					if(count($valueItems) > 1){
						$missingValues = false;
					}
					if($missingValues) {
						$valid = false;
						$fw_error_msg[] = $selfdefinedFieldSubscription['name']." ".$formText_isMandatory_output;
					}
				}
			} else {
				if($selfdefinedFieldSubscription['type'] == 0){
					if($_POST['checked'] == "false"){
						$valid = false;
						$fw_error_msg[] = $selfdefinedFieldSubscription['name']." ".$formText_isMandatory_output;
					}
				} else {
					if($_POST['value'] == ""){
						$valid = false;
						$fw_error_msg[] = $selfdefinedFieldSubscription['name']." ".$formText_isMandatory_output;
					}
				}
			}
			if(!$valid){
				return;
			}
		}
	}

	$action = ($_POST['action']);
	$updateSql = "";
	if($action == "updateActive"){
		$isChecked = ($_POST['checked']);
		$checked = 0;
		if($isChecked == "true"){
			$checked = 1;
		}
		$updateSql .= ", active = ".$o_main->db->escape($checked);

		$value = ($_POST['value']);
		if($value != ""){
			$updateSql .= ", value = ".$o_main->db->escape($value);
		}
	} else if($action == "updateText") {
		$value = ($_POST['value']);
		$updateSql .= ", value = ".$o_main->db->escape($value);
	}
	$s_sql = "SELECT * FROM customer_selfdefined_values WHERE customer_id = ? AND selfdefined_fields_id = ?";
    $o_query = $o_main->db->query($s_sql, array($customer_id, $selfdefinedField['id']));
    if($o_query && $o_query->num_rows()>0){
        $selfdefinedValue = $o_query->row_array();
    }

	if($selfdefinedValue){
		$o_main->db->query("UPDATE customer_selfdefined_values SET updated = NOW(), updatedBy = ?, customer_id = ?, selfdefined_fields_id = ? ".$updateSql." WHERE id = ?", array($variables->loggID, $customer_id, $selfdefinedField['id'], $selfdefinedValue['id']));
		$selfdefinedValueId = $selfdefinedValue['id'];
	} else {
		$o_main->db->query("INSERT INTO customer_selfdefined_values SET created = NOW(), createdBy = ?, customer_id = ?, selfdefined_fields_id = ? ".$updateSql, array($variables->loggID, $customer_id, $selfdefinedField['id']));
		$selfdefinedValueId = $o_main->db->insert_id();
	}
	if($action == "updateCheckboxes"){
		if($selfdefinedValueId > 0){
			$lineId = ($_POST['lineId']);
			$lineChecked = ($_POST['lineChecked']);
			$lineTrueChecked = 0;
			if($lineChecked == "true"){
				$lineTrueChecked = 1;
			}
			if($lineTrueChecked){
				$s_sql = "SELECT * FROM customer_selfdefined_values_connection  WHERE selfdefined_value_id = ".$selfdefinedValueId ." AND selfdefined_list_line_id = ".$lineId;
			    $o_query = $o_main->db->query($s_sql, array($selfdefinedValueId, $lineId));
			    if($o_query){
			    	if($o_query->num_rows() == 0) {
			    		$o_main->db->query("INSERT INTO customer_selfdefined_values_connection SET selfdefined_value_id = ?, selfdefined_list_line_id = ?", array($selfdefinedValueId, $lineId));
			    	}
			    }
			} else {
				$o_main->db->query("DELETE customer_selfdefined_values_connection FROM customer_selfdefined_values_connection WHERE selfdefined_value_id = ? AND selfdefined_list_line_id = ?", array($selfdefinedValueId, $lineId));
			}
		}
	}

	if($action == "updateDropdowns"){
		if($selfdefinedValueId > 0){
			$lineArrayId = explode(",",$_POST['value']);
			$addedListIds = array(-1);
			foreach($lineArrayId as $lineId){
				if($lineId > 0){
					$s_sql = "SELECT * FROM customer_selfdefined_values_connection  WHERE selfdefined_value_id = ".$selfdefinedValueId ." AND selfdefined_list_line_id = ".$lineId;
				    $o_query = $o_main->db->query($s_sql, array($selfdefinedValueId, $lineId));
				    if($o_query){
				    	if($o_query->num_rows() == 0) {
				    		$o_main->db->query("INSERT INTO customer_selfdefined_values_connection SET selfdefined_value_id = ?, selfdefined_list_line_id = ?", array($selfdefinedValueId, $lineId));
				    	}
				    }
					array_push($addedListIds, $lineId);
				}
			}

			$s_sql = "DELETE customer_selfdefined_values_connection FROM customer_selfdefined_values_connection
			WHERE selfdefined_value_id = ? AND selfdefined_list_line_id NOT IN (".implode(",", $addedListIds).")";
			$o_query = $o_main->db->query($s_sql, array($selfdefinedValueId));

		}
	}
}
?>
