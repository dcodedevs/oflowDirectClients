<?php
ini_set('opcache.revalidate_freq', 0);
if(isset($_POST['send']))
{
	define('BASEPATH', realpath(__DIR__.'/../../../../').DIRECTORY_SEPARATOR);
	require_once(BASEPATH.'elementsGlobal/cMain.php');
	sanitize_escape($_POST['module'], 'string', $module);
	$extradir = __DIR__.'/../../';
	
	include(__DIR__."/ftp_commands.php");
	include(__DIR__."/readInputLanguage.php");
	include(__DIR__."/config_mysql_reserved_words.php");
	if(!function_exists("log_action")) include(__DIR__."/fn_log_action.php");
	log_action("save_settings");
	
	$error_msg = array();
	$tableName = preg_replace('#[^A-za-z0-9_ ]+#', '',trim($_POST['tablename']));
	$tmp = array_map("ucfirst",explode("_",str_replace(" ","_",$tableName)));
	$table_language_variable = implode("",$tmp);
	$tableName = strtolower(str_replace(" ","_",$tableName));
	if(is_file(__DIR__."/../settings/tables/".$tableName.".php"))
	{
		$error_msg["error_".sizeof($error_msg)] = $formText_tableAlreadyExists_input;
	}
	if(in_array($tableName,$reservedWords))
	{
		$error_msg["error_".sizeof($error_msg)] = $formText_TableWithThisNameNotAllowedItIsReservedWord_input;
	}
	if(count($error_msg)==0)
	{
		$multilanguage = $_POST['submoduleconnect'];
		if(!$o_main->db->table_exists($tableName))
		{
			$o_main->db->simple_query("CREATE TABLE ".$tableName."(id INT PRIMARY KEY AUTO_INCREMENT, moduleID INT, createdBy CHAR(255), created DATETIME, updatedBy CHAR(255), updated DATETIME, origId INT, sortnr INT, content_status TINYINT(2) NOT NULL".($o_main->multi_acc?", account_id INT NOT NULL":"").", INDEX origIdIdx (origId)".($o_main->multi_acc?", INDEX account_idx (account_id)":"").")");
		}
		
		$newTableString = "\"$tableName:0::0\"";
		$fieldString = '"id¤id¤{$formText_id_settings}¤'.$tableName.'¤ID¤¤¤¤¤1¤0¤0¤0¤0¤0¤","moduleID¤moduleID¤{$formText_moduleId_settings}¤'.$tableName.'¤ModuleID¤¤¤¤¤1¤0¤¤1¤1¤0¤","createdBy¤createdBy¤{$formText_createdBy_settings}¤'.$tableName.'¤UsernameLogged¤¤¤¤¤1¤0¤¤0¤1¤0¤","created¤subjectcreated¤{$formText_created_settings}¤'.$tableName.'¤DateTimeUpdateCreate¤¤¤¤¤1¤0¤¤0¤1¤0¤","updatedBy¤subjectupdatedBy¤{$formText_updatedBy_settings}¤'.$tableName.'¤UsernameLogged¤¤¤¤¤1¤0¤¤1¤0¤0¤","updated¤subjectupdated¤{$formText_updated_settings}¤'.$tableName.'¤DateTimeUpdateCreate¤¤¤¤¤1¤0¤¤1¤0¤0¤","origId¤origId¤{$formText_origId_settings}¤'.$tableName.'¤ID¤¤¤¤¤1¤0¤¤1¤1¤0¤","sortnr¤sortnr¤{$formText_orderNumber_settings}¤'.$tableName.'¤OrderNr¤¤¤¤¤1¤0¤¤1¤1¤0¤","content_status¤'.$tableName.'content_status¤{$formText_ContentStatus_input}¤'.$tableName.'¤ContentStatus¤¤¤¤¤1¤0¤¤1¤1¤0¤¤¤"'.($o_main->multi_acc?',"account_id¤'.$tableName.'account_id¤{$formText_AccountId_input}¤'.$tableName.'¤AccountId¤¤¤¤¤1¤0¤¤1¤1¤0¤¤¤"':'');
		
		if($multilanguage == 1)
		{
			$newTableString .= ",\"".$tableName."content:1:$tableName:0\"";
			if(!$o_main->db->table_exists($tableName.'content'))
			{
				$o_main->db->simple_query('CREATE TABLE '.$tableName.'content(id INT PRIMARY KEY AUTO_INCREMENT, '.$tableName.'ID INT, languageID CHAR(15), INDEX '.$tableName.'IDIdx ('.$tableName.'ID))');
			}
		}
		
		$tablecounter = 1;
		$findBase = opendir(__DIR__."/../settings/tables");
		while($writeBase = readdir($findBase))
		{
			if($writeBase[0]=='.') continue;
			
			$fieldParts = explode(".",$writeBase);
			if($fieldParts[1] == "php" && $fieldParts[0] != "" && $fieldParts[0] != $tableName && (!isset($fieldParts[2]) || $fieldParts[2] != "LCK"))
			{
				$tablecounter++;
			}
		}
		
		$useLines = array();
		$settingsFile = file(__DIR__."/../settings/standardtablefile.php");
		foreach($settingsFile as $item)
		{
			$use = trim($item);
			if($use[0] == "$")
			{
				$useLines[] = $use; 
			}  
		}
		$useLines[0] = "\$mysqlTableName = array(".$newTableString.");";
		$useLines[2] = "\$preinputformName = \$formText_".$table_language_variable."_moduleName;"; 
		$useLines[3] = "\$preinputformDescription = \$formLongText_".$table_language_variable."Description_moduleDescription;"; 
		$useLines[19] = "\$tableordernr = \"".$tablecounter."\";";
		
		$newFile = "";
		for($l = 0; $l < sizeof($useLines); $l++)
		{
			$newFile .= $useLines[$l]."\n";
		} 
		ftp_file_put_content("modules/$module/input/settings/fields/".$tableName."fields.php","<?php\n\$prefields = array($fieldString);\n?>");
		ftp_file_put_content("modules/$module/input/settings/tables/".$tableName.".php","<?php\n$newFile\n?>");
		
		header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&includefile=chooseToUpdate");
	} else {
		$s_sql = "UPDATE session_framework SET error_msg = '".$o_main->db->escape_str(json_encode($error_msg))."' WHERE companyaccessID = '".$o_main->db->escape_str($_GET['caID'])."' AND session = '".$o_main->db->escape_str($_COOKIE['sessionID'])."' AND username = '".$o_main->db->escape_str($_COOKIE['username'])."'".($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"");
		$o_main->db->query($s_sql);
		
		header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&includefile=createNewTable");
	}
	exit;
}
?>
<form action="<?php echo $extradir."/input/includes/createNewTable.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule;?>" method="post">
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
	<table border="0" cellpadding="1" cellspacing="0">
	<tr>
		<td><div class="fieldname"><?php echo $formText_TableName_input;?></div></td>
		<td><input name="tablename" type="text" value="" /></td>
	</tr>
	<tr>
		<td><div class="fieldname"><?php echo $formText_MultiLanguage_input;?></div></td>
		<td><select name="submoduleconnect">
		<option value="1"><?php echo $formText_yes_input;?></option>
		<option value="0"><?php echo $formText_no_input;?></option>
		</select></td>
	</tr>
	</table>
	<div class="fieldholder" style="padding-top:5px;">
		<input class="sys-admin-button" type="submit" name="send" value="<?php echo $formText_save_input;?>" />
	</div> 
</form>