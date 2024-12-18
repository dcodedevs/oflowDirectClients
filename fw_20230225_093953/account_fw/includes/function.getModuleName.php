<?php
function getModuleName($modulename,$languageID)
{
 	$account_path = realpath(__DIR__.'/../../../');
	if(is_dir($account_path."/modules/".$modulename."/input/settings/tables"))
	{
		$intModuleName = $modulename;
		$folder = $folderfile= 'input';
		
		$findBase = opendir($account_path."/modules/".$modulename."/input/settings/tables");
		while($writeBase = readdir($findBase))
		{	
			$fieldParts = explode(".",$writeBase);
			if((!isset($fieldParts[2]) || $fieldParts[2] != "LCK") && $fieldParts[1] == "php" && $fieldParts[0] != "")
			{
				include($account_path."/modules/".$modulename."/input/languagesInput/empty.php");
				if($languageID != "" && is_file($account_path."/modules/".$modulename."/input/languagesInput/$languageID.php"))
					include($account_path."/modules/".$modulename."/input/languagesInput/$languageID.php");
				include($account_path."/modules/".$modulename."/input/settings/tables/".$fieldParts[0].".php");
				if($tableordernr == "1")
				{
					if(!is_numeric($moduletype))
					{
						$folderfile = 'output';
						$folder = $moduletype;//'output';
					}
					include($account_path."/modules/".$modulename."/input/settings/tables/".$fieldParts[0].".php");
					if($preinputformName != '')
					{
						$intModuleName = $preinputformName;
					}
				}
			}
		}
		
 		return array($intModuleName,$folder,$folderfile);
	}
}
?>