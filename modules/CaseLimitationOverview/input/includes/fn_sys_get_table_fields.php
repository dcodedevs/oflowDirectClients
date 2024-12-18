<?php
if(!function_exists('sys_get_table_fields')){
function sys_get_table_fields($module, $table, $choosenListInputLang)
{
	$return = array();
	include(__DIR__."/../../../".$module."/input/includes/readInputLanguage.php");
	include(__DIR__."/../../../".$module."/input/settings/fields/".$table."fields.php");
	foreach($prefields as $field)
	{
		$v_field = explode("¤",$field);
		$return[] = array($v_field[0], $v_field[2]);
	}
	return $return;
}
}