<?php
$module_absolute_path = realpath(__DIR__.'/../../');
$account_absolute_path = realpath(__DIR__.'/../../../../');

if($variables->developeraccess == 20)
{
	$mods = array();
	$teller = 0;
	$mods = sys_module_addToMods($mods,$headmodule,$module,2);
	
	$tableorder = array();
	$findBase = opendir($module_absolute_path."/input/settings/tables");
	while($s_file = readdir($findBase))
	{
		if($s_file == '.' || $s_file == '..') continue;
		$fieldParts = explode(".", $s_file);
		if($fieldParts[1] == "php" && $fieldParts[0] != "" && (!isset($fieldParts[2]) || (isset($fieldParts[2]) && $fieldParts[2] != "LCK")))
		{ 
			$already = 0;
			foreach($mods as $mod)
			{
				if($mod[0] == $fieldParts[0])
				{
					$already = 1;
				}
			}
			if($already == 0) $mods[] = array($fieldParts[0],0);
			$table_settings = include_local($module_absolute_path."/input/settings/tables/".$fieldParts[0].".php");
			$tableorder[$fieldParts[0]] = $table_settings['tableordernr'];  
		}	 
	}
	asort($tableorder);
	reset($tableorder);
	?>
	<div class="module-manager">
	<div style="font-size:16px;"><?php echo $formText_TablesInModule_input;?>: <?php echo $module;?></div>
	<br />
	<a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=createNewTable";?>"><?php echo $formText_NewTable_input;?></a>
	<br /><br />
	<table class="table table-striped table-condensed"><?php
	while(list($tablename,$rest) = each($tableorder))
	{
		$s_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$tablename;
		?><tr>
		<td><?php echo $tablename; ?></td>
		<td><a class="optimize" href="<?php echo $s_url."&includefile=editButtons";?>"><?php echo $formText_EditButtons_input;?></a></td>
		<td><a class="optimize" href="<?php echo $s_url."&includefile=editTableSettings";?>"><?php echo $formText_EditSettings_input;?></a></td>
		<td><a class="optimize" href="<?php echo $s_url."&includefile=editRelations";?>"><?php echo $formText_EditRelations_input;?></a></td>
		<td><a class="optimize" href="<?php echo $s_url."&includefile=editIndexes";?>"><?php echo $formText_EditIndexes_input;?></a></td>
		<td><a class="optimize" href="<?php echo $s_url."&includefile=editFieldSettings";?>"><?php echo $formText_EditFields_input;?></a></td>
		<td><a class="optimize" href="<?php echo $s_url."&includefile=editTableOrder&editOrder=1";?>"><?php echo $formText_EditOrder_input;?></a></td>
		<td><a class="delete-confirm-btn" data-name="<?php echo $tablename;?>" href="<?php echo $extradir."/input/includes/deletesetting.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&deleteTable=".$tablename."&includefile=chooseToUpdate";?>"><?php echo $formText_Delete_input;?></a></td>
		</tr>
		<?php
	}
	?></table>
	</div><?php
}

function sys_module_addToMods($mods, $searchmod, $module, $currLev)
{
	$s_file = __DIR__.'/../settings/tables/'.$searchmod.'.php';
	if(is_file($s_file))
	{
		include($s_file);
		foreach($prechildmodule as $childmod)
		{
			if(!in_array($childmod,$mods) && $childmod != "")
			{
				$mods[] = array($childmod,$currLev);
				$mods = sys_module_addToMods($mods,$childmod,$module,$currLev + 1);
			}
		}
	}
	
	return $mods;
}
?>