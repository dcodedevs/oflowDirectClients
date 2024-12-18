<?php
function sys_get_module_tables($moule, $choosenListInputLang)
{
	$tableorder = $return = array();
	include(__DIR__."/../../../".$moule."/input/includes/readInputLanguage.php");
	$findBase = opendir(__DIR__."/../../../".$moule."/input/settings/tables");
	while($writeBase = readdir($findBase))
	{
		$fieldParts = explode(".",$writeBase);
		if($fieldParts[2] != "LCK" && $fieldParts[1] == "php" && $fieldParts[0] != "")
		{
			include(__DIR__."/../../../".$moule."/input/settings/tables/".$fieldParts[0].".php");
			$tableorder[$fieldParts[0]] = $tableordernr.":".$preinputformName;
		}
	}
	asort($tableorder);
	reset($tableorder);
	
	while(list($tablename,$rest) = each($tableorder))
	{
		list($r,$table_name_print) = explode(":",$rest);
		$return[] = array($tablename, $table_name_print);
	}
	return $return;
}
?>