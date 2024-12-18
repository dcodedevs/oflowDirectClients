<?php
$b_mandatory_check = true;
$v_insert = array();
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
	else if(($fields[$nums][13] != 0 || $fields[$nums][13] == "") && !($fields[$nums][15] == 1 && $insertStatus == 1) )
	{
		$v_insert[$fields[$nums][0]] = $fields[$nums][6][$basetable->langfields[$z]];
	}
}
if($basetable->connection != "")
{
	$v_insert[$basetable->connection.'ID'] = $databases[$basetable->connection]->ID;
}
if($basetable->multilanguage == 1)
{
	$v_insert['languageID'] = $basetable->langfields[$z];
}
if(!$fields_has_errors){
	if($b_mandatory_check)
	{
		$o_main->db->insert($basetable->name, $v_insert);
		$basetable->ID = $o_main->db->insert_id();
	}
}
