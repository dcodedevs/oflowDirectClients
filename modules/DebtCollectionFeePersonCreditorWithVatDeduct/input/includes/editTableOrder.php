<?php
$module_absolute_path = realpath(__DIR__.'/../../');
$account_absolute_path = realpath(__DIR__.'/../../../../');

if(isset($_POST['saveTableOrder']))
{
	define('BASEPATH', realpath(__DIR__.'/../../../../').DIRECTORY_SEPARATOR);
	require_once(BASEPATH.'elementsGlobal/cMain.php');
	if(!function_exists("log_action")) include(__DIR__."/fn_log_action.php");
	log_action("save_settings");
	
	require_once(__DIR__."/ftp_commands.php");
	
	$module = $_GET['module'];
	foreach($_POST['contentID'] as $key => $table)
	{
		$file = $module_absolute_path."/input/settings/tables/".$table.".php";
		$settingsFile = file($file);
		
		$useLines = array();
		foreach($settingsFile as $testLine)
		{
			$use = trim($testLine);
			if($use[0] == "$")
			{
				$useLines[] = $use;
			}  
		}
		$useLines[19] = "\$tableordernr = \"".($key+1)."\";"; 
		$newFile = ""; 
		for($x = 0; $x < sizeof($useLines); $x++)
		{
			$newFile .= $useLines[$x].PHP_EOL;
		}
		ftp_file_put_content("/modules/$module/input/settings/tables/".$table.".php","<?php\n{$newFile}\n?>");
	}
	header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$_GET['submodule']."&includefile=".$_GET['includefile']);
	exit;
}

$findBase = opendir($module_absolute_path."/input/settings/tables");
while($writeBase = readdir($findBase))
{
	$fieldParts = explode(".",$writeBase);
	if($fieldParts[2] != "LCK" && $fieldParts[1] == "php" && $fieldParts[0] != "")
	{ 
		$already = 0;
		foreach($mods as $mod)
		{
			if($mod[0] == $fieldParts[0])
			{
				$already = 1;
			}
		}
		if($already == 0)
			$mods[] = array($fieldParts[0],0);
		include($module_absolute_path."/input/settings/tables/".$fieldParts[0].".php");
		$tableorder[$fieldParts[0]] = $tableordernr;
	}
}
asort($tableorder);
?>
<script type="text/javascript">
$(document).ready(function() {
	$("#sortable").sortable().disableSelection();
});
</script>
<form method="post" action="<?php echo $extradir."/input/includes/editTableOrder.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&submodule=".$_GET['submodule']."&includefile=chooseToUpdate";?>">
<input type="hidden" name="saveTableOrder" value="1" />
<ul id="sortable">
<?php
foreach($tableorder as $key => $value)
{
	?><li class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span><input type="hidden" name="contentID[]" value="<?php echo $key;?>" /><?php echo $key;?></li><?php
}
?>
</ul>
<div><input class="btn btn-sm btn-success" name="submbtn" value="<?php echo $formText_save_input;?>" type="submit"></div>
</form>