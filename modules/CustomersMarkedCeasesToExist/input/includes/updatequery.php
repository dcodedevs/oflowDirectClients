<?php
$b_mandatory_check = true;
$v_update_values = array();
$v_update_check = $v_content_check;
$fields_has_errors = false;
foreach($basetable->fieldNums as $nums)
{
	if(isset($fields[$nums][6][$basetable->langfields[$z]]['error'])){
		$fields_has_errors = true;
		if($fields[$nums][6][$basetable->langfields[$z]]['error'] == 1){
			$error_msg["error_".count($error_msg)] = $formText_AccessTokenShouldBe16CharactersAndMore_input;
		}
	}
	if($fields[$nums][14] == 1 && trim($fields[$nums][6][$basetable->langfields[$z]]) == '')
	{
		$error_msg["error_".count($error_msg)] = "<strong>".$fields[$nums][2]."</strong> ".$formText_isMandatory_input;
		$b_mandatory_check = false;
	}
	else if($fields[$nums][12] != 0 || $fields[$nums][12] == "")
	{
		$v_update_values[$fields[$nums][0]] = $fields[$nums][6][$basetable->langfields[$z]];

		if($fields[$nums][0] != 'updatedBy' and $fields[$nums][0] != 'updated')
			$v_update_check[$fields[$nums][0]] = $fields[$nums][6][$basetable->langfields[$z]];
	}
}

if(!$fields_has_errors){
	if($b_mandatory_check)
	{
		if($activateHistory == "1")
		{
			$o_query = $o_main->db->get_where($basetable->name, $v_update_check);
			if(!$o_query || ($o_query && $o_query->num_rows() == 0))
			{
				$b_do_history = true;
			}
			$v_history[$basetable->name] = array();
			foreach($v_content as $s_key => $s_value)
			{
				if($s_key == "id") $s_value = "NULL";
				if($s_key == "content_status") $s_value = 3;
				if($s_key == "origId") $s_value = $v_content["id"];
				$v_history[$basetable->name][$s_key] = $s_value;
			}
		}

		$o_main->db->update($basetable->name, $v_update_values, $v_content_check);
	}
}
?>
