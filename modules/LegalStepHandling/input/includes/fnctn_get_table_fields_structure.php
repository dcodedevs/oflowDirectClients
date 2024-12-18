<?php
if(!function_exists('get_table_fields_structure')){
function get_table_fields_structure($table,$pre_path,$table_structure)
{
	$pre_path=$pre_path."/input/settings/";
	//print ($pre_path."<br /><br />");
	$tmp_return = array();
	$extensions=array('php');
	$except_dirs=array();
	$check_subdirs=0;
	$where_to_look=$pre_path."fields";
	$field_names =array();
	$field_file = $where_to_look."/".$table."fields.php";
	//print ("<br /><br />".$field_file."<br /><br />");
	if(is_file($field_file))
	{
		include ($field_file);
		$tmpField = array();
		foreach($prefields as $fieldFromFile)
		{
			$thisDatabaseFieldExtra = "";
			$fieldFromFileData = explode("¤", $fieldFromFile);
			//print_r($fieldFromFileData);
			//print ("<br />");
			$fieldID = $fieldFromFileData[0];
			$fieldType =$fieldFromFileData[4];
			$tmpField['id']=$fieldID;
			$tmpField['type']=$fieldType;
			include($pre_path."../fieldtypes/$fieldType/fielddata.php");
			$tmpField['databaseType']=$thisDatabaseField;
			$tmpField['databaseTypeExtra']=$thisDatabaseFieldExtra;
            //PSH: PHP update: if($table != $fieldFromFileData[3] && sizeof($table_structure[$fieldFromFileData[3]])==0)
			if($table != $fieldFromFileData[3] && !in_array($fieldFromFileData[3],$table_structure))
			{
				$table_structure[$fieldFromFileData[3]]['id'] = array('id' => 'id', 'type' => 'ID', 'databaseType' => 'INT');
				$table_structure[$fieldFromFileData[3]][$table.'ID'] = array('id' => $table.'ID', 'type' => 'ID', 'databaseType' => 'INT');
				$table_structure[$fieldFromFileData[3]]['languageID'] = array('id' => 'languageID', 'type' => 'Textfield', 'databaseType' => 'CHAR(15)');
			}
			$table_structure[$fieldFromFileData[3]][$fieldID]=$tmpField;
		}
	} else {}
	
	//return  $field_names;
	return $table_structure;
}
}