<?php
$module_absolute_path = realpath(__DIR__.'/../../');
$account_absolute_path = realpath(__DIR__.'/../../../../');

$values = $newvals = $ignoreMe = array();
$removeThem = array(";","\"");
$useVars = array("showGroupToAdmin"=>"7");

$settingsFile = file($module_absolute_path."/output/settingsOutput/settings.php");
$useLines = $variableNames = $variableVal = array();
foreach($settingsFile as $testLine)
{
	$use = trim($testLine);
	if($use[0] == "$")
	{
		$nameFind = explode(" ",$use);
		$variableName = str_replace("$","",$nameFind[0]);
		$useLines[$variableName] = $use;
		$variableNames[$variableName] = $variableName;
		if(!array_search($variableName,$ignoreMe))
		{		     
			$variableVal[$variableName] = str_replace($removeThem,"",$nameFind[2]);
		}
	}  
}

if(isset($_POST['send']))
{
	if(!function_exists("ftp_file_put_content")) include_once (__DIR__.'/ftp_commands.php');
	$newFile = "";
	foreach($useLines as $key => $value)
	{
		if(array_search($variableNames[$key],$ignoreMe))
		{
			$newFile .= $value."\n";
		}	 
	}
	foreach($useVars as $key => $value)
	{
		if(!array_search($variableNames[$key],$ignoreMe))
		{
			$newFile .= "\$".$key." = \"".$_POST[$key]."\";\n";
			$variableVal[$key] = $_POST[$key];
		}	 
	}
	ftp_file_put_content("/modules/".$_GET['module']."/output/settingsOutput/settings.php","<?php\n{$newFile}?>");
	
	header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&actionType=changeOutputSettings");
	exit;
}

?>
<form action="<?php echo $extradir."/input/includes/changeOutputSettings.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module;?>" method="post">
	<input type="hidden" name="submodule" value="<?php echo $submodule;?>" />
	<input type="hidden" name="module" value="<?php echo $module;?>" />
	<input type="hidden" name="moduleID" value="<?php echo $moduleID;?>" />
	<input type="hidden" name="pageID" value="<?php echo $_GET['pageID'];?>" />
	<input type="hidden" name="extradir" value="<?php echo $extradir;?>" />
	<input type="hidden" name="parentdir" value="<?php echo $parentdir;?>" />
	<input type="hidden" name="choosenListInputLang" value="<?php echo $choosenListInputLang;?>">
	<input type="hidden" name="choosenAdminLang" value="<?php echo $choosenAdminLang;?>">
	<input type="hidden" name="languageID" value="<?php echo $choosenInputLang;?>" />
	<input type="hidden" name="extraimagedir" value="<?php echo $extraimagedir;?>" />
	<input type="hidden" name="loginType" value="<?php echo ($access<211?1:2) ?>" />
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<?php  
	foreach($useVars as $key => $value)
	{
		if($value == 0)
		{
			if(!isset($variableVal[$key])) $variableVal[$key] = '';
			?><tr><td ><?php echo str_replace("pre","",$key);  ?> </td><td> <input style="width:400px;" name="<?php echo $key; ?>" value="<?php echo $variableVal[$key]; ?>" type="text" /></td></tr><?php
		} else if($value == 1) {
			if(!isset($variableVal[$key])) $variableVal[$key] = '';
			?><tr><td ><?php echo str_replace("pre","",$key);  ?> </td><td> <textarea style="width:400px; height:60px;" name="<?php echo $key; ?>"><?php echo $variableVal[$key]; ?></textarea></td></tr><?php
		} else if($value == 2) {
			if(!isset($variableVal[$key])) $variableVal[$key] = 0;
			?><tr><td ><?php echo str_replace("pre","",$key);  ?> </td><td> <select style="width:400px;" name="<?php echo $key; ?>">
			<option value="0"<?php if($variableVal[$key] == 0){ ?> selected<?php } ?>><?php echo $formText_no_input;?></option>
			<option value="1"<?php if($variableVal[$key] == 1){ ?> selected<?php } ?>><?php echo $formText_yes_input;?></option>
			</select></td></tr><?php
		} else if($value == 7) {
			if(!isset($variableVal[$key])) $variableVal[$key] = 1;
			?><tr><td ><?php echo str_replace("pre","",$key);  ?> </td><td> <select style="width:400px;" name="<?php echo $key; ?>">
			<option value="1"<?php if($variableVal[$key] == 1){ ?> selected<?php } ?>><?php echo $formText_yes_input;?></option>
			<option value="0"<?php if($variableVal[$key] == 0){ ?> selected<?php } ?>><?php echo $formText_no_input;?></option>
			</select></td></tr><?php
		} else if($value == 6) {
			if(!isset($variableVal[$key])) $variableVal[$key] = 0;
			?><tr><td ><?php echo str_replace("pre","",$key);  ?> </td><td> <select style="width:400px;" name="<?php echo $key; ?>">
			<option value="0"<?php if($variableVal[$key] == 0){ ?> selected<?php } ?>><?php echo $formText_Above_input;?></option>
			<option value="1"<?php if($variableVal[$key] == 1){ ?> selected<?php } ?>><?php echo $formText_Before_input;?></option>
			</select></td></tr><?php
		} else if($value == 3) {
			if(!isset($variableVal[$key])) $variableVal[$key] = '';
			?><tr><td ><?php echo str_replace("pre","",$key);  ?> </td><td> <select style="width:400px;" name="<?php echo $key; ?>">
			<option value=""><?php echo $formText_none_input;?></option>
			<?php foreach($fields as $choice) { ?>
				<option value="<?php echo $choice[0]; ?>"<?php if($variableVal[$key] == $choice[0]){ ?> selected<?php } ?>><?php echo $choice[0]; ?></option>
			<?php } ?>
			</select></td></tr><?php
		} else if($value == 4) {
			if(!isset($variableVal[$key])) $variableVal[$key] = 0;
			?><tr><td ><?php echo str_replace("pre","",$key);  ?> </td><td><select style="width:400px;" name="<?php echo $key; ?>">
			<option value="0"<?php if($variableVal[$key] == 0){ ?> selected<?php } ?>><?php echo $formText_Ascending_input;?></option>
			<option value="1"<?php if($variableVal[$key] == 1){ ?> selected<?php } ?>><?php echo $formText_Descending_input;?></option>
			</select></td></tr><?php
		} else if($value == 5) {
			if(!isset($variableVal[$key])) $variableVal[$key] = 0;
			?><tr><td ><?php echo str_replace("pre","",$key);  ?> </td><td> <select style="width:400px;" name="<?php echo $key; ?>">
			<option value="0"<?php if($variableVal[$key] == $choice[0]){ ?> selected<?php } ?>><?php echo $formText_NoSorting_input;?></option>
			<?php foreach($fields as $choice) { ?>
				<option value="<?php echo $choice[0]; ?>"<?php if($variableVal[$key] == $choice[0]){ ?> selected<?php } ?>><?php echo $choice[0]; ?></option>
			<?php } ?>
			</select></td></tr><?php
		}
	}
	?>
	</table>
	<div class="fieldholder" style="padding-top:5px; padding-left:10px;">
	<input class="sys-admin-button" type="submit" name="send" value="<?php echo $formText_save_input;?>" />
	</div>  
</form>