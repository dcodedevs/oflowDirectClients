<?php
ini_set('opcache.revalidate_freq', 0);
$module_absolute_path = realpath(__DIR__.'/../../');
$account_absolute_path = realpath(__DIR__.'/../../../../');
if(!function_exists("ftp_file_put_content")) require_once(__DIR__."/ftp_commands.php");
if(!function_exists("sys_get_button_config")) require_once(__DIR__."/fn_sys_get_button_config.php");
if(!class_exists("Button")) require_once(__DIR__."/class_Button.php");

#
# Save data
#
if(isset($_POST['savebutton']))
{
	define('BASEPATH', realpath(__DIR__.'/../../../../').DIRECTORY_SEPARATOR);
	require_once(BASEPATH.'elementsGlobal/cMain.php');
	if(!function_exists("log_action")) include(__DIR__."/fn_log_action.php");
	
	$module = $_POST['module'];
	$submodule = $_POST['submodule'];
	$tableconnect = explode(":",$_POST['tableconnect']);
	$extra = "";
	
	$fieldWork = $_GET['fieldWork'];
	
	$alterData = new Button();
	$words = str_word_count($_POST['buttonnamelist'], 1);
	$buttonformname = '$formText_';
	foreach($words as $formtextword)
	{
		$buttonformname .= ucfirst(strtolower($formtextword));
	}
	$buttonformname .="_module";
	
	$alterData->init($_POST['buttonnamelist'], $buttonformname, $_POST['selectedbutton'], $tableconnect[1], $_POST['hiddenfield'], $_POST['hiddendirectfield'],$tableconnect[0], $_POST['contentstatusfilter']);
	
	$variables = sys_get_button_config("/modules/$module/input/settings/buttonconfig/".$submodule.$_POST['buttontype'].".php", $account_absolute_path);
	
	$new = 1;
	foreach($variables as $vab)
	{
		if($fieldWork == $vab->buttonnamelist)
		{
			$new = 0;				 
		}
	} 
	if($new == 1)
	{
		$variables[] = $alterData;
	} else
		$variables[$fieldWork] = $alterData;
	
	$makeString = "";
	foreach($variables as $nut)
	{
		if(strpos($nut->buttonname,'$') !== false and strpos($nut->buttonname,'{') === false) $nut->buttonname = '{'.$nut->buttonname.'}';
		$makeString .= $nut->buttonnamelist.":".$nut->buttonname.":".$nut->selectedbutton.":".$nut->tableconnect.":".$nut->hiddenfield.":".$nut->hiddendirectfield.":".$nut->tableconnectmodule.":".$nut->content_status."¤";
	}
	
	$outString = "\$prebuttonconfig = \"{$makeString}\";";
	ftp_file_put_content("/modules/$module/input/settings/buttonconfig/".$submodule."".$_POST['buttontype'].".php",str_replace("[%]","$","<?php\n{$outString}\n?>"));
	
	log_action("content_order");
	
	header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=".$_GET['includefile']);
	exit;
}


$fieldWork = "";
if(isset($_GET['fieldWork']) && $_GET['fieldWork'] != "")
{
	$fieldWork = $_GET['fieldWork'];
}

$fileinputform = "/modules/$module/input/settings/buttonconfig/".$submodule."inputform.php";
$filelist = "/modules/$module/input/settings/buttonconfig/".$submodule."list.php";

$variablesInputform = sys_get_button_config($fileinputform, $account_absolute_path);
$variablesList = sys_get_button_config($filelist, $account_absolute_path);

if(isset($_GET['editOrder']))
{ 
	if($_GET['buttontype'] == 'inputform' )
	{
		$file = $fileinputform;
		$filearray = $variablesInputform;
	} else {
		$file = $filelist;
		$filearray = $variablesList;
	}
	$fromRelations = 0;
	$buttonsort = "1";
	$arrayname = "prebuttonconfig";
	include(__DIR__."/editFileDelimetedOrder.php");
	
} else if($fieldWork == "") {

	$kols = array("#FFFFFF","#f6f7f8");
	?>
	<div class="module-manager">
	<div style="font-size:16px;"><?php echo $formText_ButtonsInModule_input.': '.$module.'('.$submodule.') - '.$formText_Inputform_input;?></div>
	<br />
	<a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=".$_GET['includefile']."&fieldWork=newfield&buttontype=inputform";?>"><?php echo $formText_NewButton_input;?></a>
	<br /><br />
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<?php
	$counter = 1;
	foreach($variablesInputform as $vab)
	{
		?><tr class="module-item" bgcolor="<?php echo $kols[$counter % 2]; ?>">
		<td><?php echo $vab->buttonnamelist;?></td>
		<td><a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=".$_GET['includefile']."&fieldWork=".$vab->buttonnamelist."&buttontype=inputform";?>"><?php echo $formText_EditButton_input;?></a></td>
		<td><a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=".$_GET['includefile']."&fieldWork=".$vab->buttonnamelist."&editOrder&buttontype=inputform";?>"><?php echo $formText_EditOrder_input;?></a></td>
		<td><a class="delete-confirm-btn" data-name="<?php echo $vab->buttonnamelist?>" onclick="return false;" href="<?php echo $extradir."/input/includes/deletesetting.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=".$_GET['includefile']."&deleteButtonField=".$vab->buttonnamelist."&buttontype=inputform";?>"><?php echo $formText_Delete_input;?></a></td>
		</tr>
		<?php
		$counter++;	 
	}
	?></table> 
	<div style="font-size:16px;"><?php echo $formText_ButtonsInModule_input.': '.$module.'('.$submodule.') - '.$formText_List_input;?></div>
	<br />
	<a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=".$_GET['includefile']."&fieldWork=newfield&buttontype=list";?>"><?php echo $formText_NewButton_input;?></a><br /><br />
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<?php
	$counter = 1;
	foreach($variablesList as $vab)
	{
		?><tr class="module-item" bgcolor="<?php echo $kols[$counter % 2]; ?>">
		<td><?php echo $vab->buttonnamelist; ?></td>
		<td><a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=".$_GET['includefile']."&fieldWork=".$vab->buttonnamelist."&buttontype=list";?>"><?php echo $formText_EditField_input;?></a></td>
		<td><a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=".$_GET['includefile']."&fieldWork=".$vab->buttonnamelist."&editOrder=1&buttontype=list";?>"><?php echo $formText_EditOrder_input;?></a></td>
		<td><a class="delete-confirm-btn" data-name="<?php echo $vab->buttonnamelist?>" onclick="return false;" href="<?php echo $extradir."/input/includes/deletesetting.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=".$_GET['includefile']."&deleteButtonField=".$vab->buttonnamelist."&buttontype=list";?>"><?php echo $formText_Delete_input;?></a></td>
		</tr>
		<?php
		$counter++;	 
	}
	?></table>
	</div><?php
	
} else {
	
	if($_GET['buttontype'] == 'inputform' )
		$variables = $variablesInputform;
	else
		$variables = $variablesList;
	
	?><form action="<?php echo $extradir."/input/includes/editButtons.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=".$_GET['includefile']."&fieldWork=".$fieldWork;?>" method="post">
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
	<input type="hidden" name="buttontype" value="<?php echo $_GET['buttontype']; ?>" />
	<table border="0" cellpadding="0" cellspacing="0">
		<tr><td class="fieldname" width="150"><?php echo $formText_NameInList_input;?></td><td><input name="buttonnamelist" type="text" value="<?php echo $variables[$fieldWork]->buttonnamelist;?>" /></td></tr>
		<tr><td class="fieldname"><?php echo $formText_ButtonType_input;?></td><td><select name="selectedbutton" id="fieldtypeholder">
		<?php
		$i = 0;
		$i_cur = 0;
		$items = array();
		$buttontypeDir = opendir($module_absolute_path."/input/buttontypes");
		while($typeDirFile = readdir())
		{
			if($typeDirFile != '.' && $typeDirFile != '..')
			{
				$items[] = $typeDirFile;
			}
		}
		closedir($buttontypeDir);
		natcasesort($items);
		foreach($items as $i => $item)
		{
			?><option value="<?php echo $item;?>"<?php if($variables[$fieldWork]->selectedbutton == $item){ ?> selected<?php $i_cur = $i;} ?> ><?php echo $item?></option><?php
		}
		?>
		</select>
		</td></tr>
		<tr><td class="fieldname"><?php echo $formText_TableConnection_input;?></td><td>
		
		<select name="tableconnect">
		<option value="None"><?php echo $formText_none_input;?></option>
		<?php
		$items = array();
		$findBase = opendir($account_absolute_path."/modules");
		while($writeBase = readdir($findBase))
		{
			if($writeBase != '.' && $writeBase != '..')
			$items[] = $writeBase;
		}
		natcasesort($items);
		foreach($items as $i => $writeBase)
		{
			$s_module_name = '';
			$o_query = $o_main->db->query('SELECT name FROM moduledata WHERE name = ?', array($writeBase));
			if($o_query && $o_row = $o_query->row()) $s_module_name = $o_row->name;
			$findTables = opendir($account_absolute_path."/modules/".$writeBase."/input/settings/tables");
			while($writeTables = readdir($findTables))
			{
				$fieldParts = explode(".",$writeTables);
				if($fieldParts[2] != "LCK" && $fieldParts[1] == "php" && $fieldParts[0] != "")
				{
					if($firstTable == "")
					{
						$firstTable = $fieldParts[0];
					}
					?><option value="<?php echo $s_module_name.":".$fieldParts[0].":".$writeBase; ?>" <?php if($fieldParts[0] == $variables[$fieldWork]->tableconnect && $writeBase == $variables[$fieldWork]->tableconnectmodule ){ ?> selected="selected"<?php } ?>><?php echo $writeBase.' - '.$fieldParts[0]; ?></option><?php		
				}
			}
		}
		?>
		</select> 
		</td></tr>
		<tr><td class="fieldname"><?php echo $formText_HiddenButton_input;?></td><td>
		<select name="hiddenfield">
			<option value="0"<?php  if($variables[$fieldWork]->hiddenfield == 0){ ?> selected<?php } ?>><?php echo $formText_no_input;?></option>
			<option value="1"<?php  if($variables[$fieldWork]->hiddenfield == 1){ ?> selected<?php } ?>><?php echo $formText_yes_input;?></option>
		</select>
		</td></tr>
		<tr><td class="fieldname"><?php echo $formText_HiddenDirectAccess_input;?></td><td>
		<select name="hiddendirectfield">
			<option value="0"<?php  if($variables[$fieldWork]->hiddendirectfield == 0){ ?> selected<?php } ?>><?php echo $formText_no_input;?></option>
			<option value="1"<?php  if($variables[$fieldWork]->hiddendirectfield == 1){ ?> selected<?php } ?>><?php echo $formText_yes_input;?></option>
		</select>
		</td></tr>
		<tr><td class="fieldname"><?php echo $formText_ContentStatus_input;?></td><td>
		<input type="text" name="contentstatusfilter" value="<?php echo $variables[$fieldWork]->content_status;?>">
		</td></tr>
		<tr><td colspan="2">
		<div class="fieldholder" style="padding-top:5px; padding-left:10px;">
		<input class="sys-admin-button" type="submit" name="savebutton" value="<?php echo $formText_save_input;?>" />
		</div>      
		</td></tr>
		<tr><td colspan="2">&nbsp;</td></tr>
	</table>
	</form><?php
}
?>