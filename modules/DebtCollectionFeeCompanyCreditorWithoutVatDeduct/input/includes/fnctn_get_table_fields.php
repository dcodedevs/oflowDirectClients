<?php
function get_table_fields($table,$pre_path)
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
		foreach ($prefields as $fieldFromFile)
		{
			$fieldFromFileData = explode("¤", $fieldFromFile);
			//print_r($fieldFromFile);
			//print ("<br />");
			$fieldID = $fieldFromFileData[0];
			$fieldType =$fieldFromFileData[4];
			$field_names[$fieldID]['id']=$fieldID;
			$field_names[$fieldID]['type']=$fieldType;
			
			include ($pre_path."../fieldtypes/$fieldType/fielddata.php");
			
			$field_names[$fieldID]['databaseType']=$thisDatabaseField;
			//print_r($field_names[$fieldID]);
			//print("<br /><br /><br />");
		}
	} else{}
	//print("<br />".$where_to_look."<br />");
	//$table_files=get_files($where_to_look, $extensions,$except_dirs,$check_subdirs);
	//print_r($table_files);
	
	return  $field_names;
}



?>