<?php
function getModuleName($modulename,$languageID)
{
 	$module = $modulename;
	$o_main = get_instance();
	$variables = new stdClass();
	$choosenListInputLang = $languageID;
	$account_path = realpath(__DIR__.'/../../../');
	if(is_dir($account_path."/modules/".$modulename."/input/settings/tables"))
	{
		$intModuleName = $modulename;
		$folder = $folderfile= 'input';
		include($account_path."/modules/".$modulename."/input/includes/readInputLanguage.php");
		
		$findBase = opendir($account_path."/modules/".$modulename."/input/settings/tables");
		while($writeBase = readdir($findBase))
		{	
			$fieldParts = explode(".",$writeBase);
			if((!isset($fieldParts[2]) || $fieldParts[2] != "LCK") && $fieldParts[1] == "php" && $fieldParts[0] != "")
			{
				include($account_path."/modules/".$modulename."/input/settings/tables/".$fieldParts[0].".php");
				if($tableordernr == "1")
				{
					if(!is_numeric($moduletype))
					{
						$folderfile = 'output';
						$folder = $moduletype;//'output';
					}
					include($account_path."/modules/".$modulename."/input/settings/tables/".$fieldParts[0].".php");
					if(!empty($preinputformName))
					{
						$intModuleName = $preinputformName;
					}
				}
			}
		}
		
 		return array($intModuleName,$folder,$folderfile);
	}
}
