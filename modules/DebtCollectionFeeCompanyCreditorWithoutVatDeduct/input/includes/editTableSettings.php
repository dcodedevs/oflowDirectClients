<?php
$module_absolute_path = realpath(__DIR__.'/../../');
//$account_absolute_path = realpath(__DIR__.'/../../../../');

$variables = $values = $newvals = array();
$ignoreMe = array("donotdeleteever","mysqlTableName","prechildmodule","preinputformName","preinputformDescription","preparentmodule","preinputLevel","thisLevel","tableordernr");
$useVars = array(
	"List in column 2" => "-1",
	"precolumn2ListMinWidth" => "18",
	"precolumn2ListMaxWidth" => "18",
	"prelistButtonDelete"=>"2",
	"prelistButtonEdit"=>"2",
	"prelistButtonCreate"=>"7",
	"preinputButtonSave"=>"2",
	"showShowpagebutton"=>"2",
	"preshowSearchField"=>"2",
	"presearchMethod"=>"23",
	"searchFieldName" => "3",
	"searchType" => "15",
	
	"preshowFilterByMenulevelField"=>"2",
	"prefilterMenuModule"=>"200",
	
	"orderManualOrByField" => "16",
	// "contentorderfield"=>"18",
	"preorderByField"=>"17",
	"preorderByDesc"=>"4",

	"preListSet"=>array(
		"preDefaultlist"=>"-100",
		"prenumberOfFields"=>"100",
		"prefieldInList"=>"102",
		"fieldInListWidth"=>"103",
		"presecondFieldInList"=>"102",
		"presecondFieldInListWidth"=>"103",
		"prethirdFieldInList"=>"102",
		"prethirdFieldInListWidth"=>"103",
		"preforthFieldInList"=>"102",
		"preforthFieldInListWidth"=>"103",
		"prefifthFieldInList"=>"102",
		"prefifthFieldInListWidth"=>"103",
		"presixthFieldInList"=>"102",
		"presixthFieldInListWidth"=>"103",
		"preListAdd"=>"104",
	),
	"preListSet2"=>array(
		"preSet2list"=>"-100",
		"preSet2numberOfFields"=>"100",
		"preSet2UseAfterWidth" => "101",
		"preSet2fieldInList"=>"102",
		"preSet2fieldInListWidth"=>"103",
		"preSet2secondFieldInList"=>"102",
		"preSet2secondFieldInListWidth"=>"103",
		"preSet2thirdFieldInList"=>"102",
		"preSet2thirdFieldInListWidth"=>"103",
		"preSet2forthFieldInList"=>"102",
		"preSet2forthFieldInListWidth"=>"103",
		"preSet2fifthFieldInList"=>"102",
		"preSet2fifthFieldInListWidth"=>"103",
		"preSet2sixthFieldInList"=>"102",
		"preSet2sixthFieldInListWidth"=>"103",
		"preSet2ListAdd"=>"104",
	),
	"preListSet3"=>array(
		"preSet3list"=>"-100",
		"preSet3numberOfFields"=>"100",
		"preSet3UseAfterWidth" => "101",
		"preSet3fieldInList"=>"102",
		"preSet3fieldInListWidth"=>"103",
		"preSet3secondFieldInList"=>"102",
		"preSet3secondFieldInListWidth"=>"103",
		"preSet3thirdFieldInList"=>"102",
		"preSet3thirdFieldInListWidth"=>"103",
		"preSet3forthFieldInList"=>"102",
		"preSet3forthFieldInListWidth"=>"103",
		"preSet3fifthFieldInList"=>"102",
		"preSet3fifthFieldInListWidth"=>"103",
		"preSet3sixthFieldInList"=>"102",
		"preSet3sixthFieldInListWidth"=>"103",
		"preSet3ListAdd"=>"104",
	),
	"preListSet4"=>array(
		"preSet4list"=>"-100",
		"preSet4numberOfFields"=>"100",
		"preSet4UseAfterWidth" => "101",
		"preSet4fieldInList"=>"102",
		"preSet4fieldInListWidth"=>"103",
		"preSet4secondFieldInList"=>"102",
		"preSet4secondFieldInListWidth"=>"103",
		"preSet4thirdFieldInList"=>"102",
		"preSet4thirdFieldInListWidth"=>"103",
		"preSet4forthFieldInList"=>"102",
		"preSet4forthFieldInListWidth"=>"103",
		"preSet4fifthFieldInList"=>"102",
		"preSet4fifthFieldInListWidth"=>"103",
		"preSet4sixthFieldInList"=>"102",
		"preSet4sixthFieldInListWidth"=>"103",
	),
	

	/*"preinputButtonCreate"=>"2",
	"preinputButtonDelete"=>"2",
	"preinputButtonSaveAndStay"=>"2",
	"showSave2button"=>"7",
	"showDuplicate"=>"2",
	"showPrint"=>"2",*/
	/*"showEditInputLanguage"=>"2",
	"showEditOutputLanguage"=>"2",
	"showEditOutputSettings"=>"2",*/

	"Detail page"=>"-1",
	"preDetailpageMinWidth" => "18",
	"preDetailpageMaxWidth" => "18",
	"preinputButtonSave"=>"2",
	"showSave2button"=>"7",
	"showDuplicate"=>"2",
	"textBeforeOrAbove"=>"6",
	"showUpdatedCreatedBy"=>"2",
    "showTranslateLanguageButton"=>"2",


	"List when submodule"=>"-1",
	"preexpandSublist"=>"2",
	"preperPage"=>"0",
	"preShowDeleteAllSubcontentButton"=>"2",

	"preSubmoduleListSet"=>array(
		"preSubmoduleDefaultlist"=>"-100",
		"presubNumberOfFields"=>"100",
		"presubFieldInList"=>"102",
		"presubFieldInListWidth"=>"103",
		"presubSecondFieldInList"=>"102",
		"presubSecondFieldInListWidth"=>"103",
		"presubThirdFieldInList"=>"102",
		"presubThirdFieldInListWidth"=>"103",
		"presubForthFieldInList"=>"102",
		"presubForthFieldInListWidth"=>"103",
		"presubFifthFieldInList"=>"102",
		"presubFifthFieldInListWidth"=>"103",
		"presubSixthFieldInList"=>"102",
		"presubSixthFieldInListWidth"=>"103",
		"presubListAdd"=>"104",
	),
	"preSubmoduleListSet2"=>array(
		"preSubmoduleSet2list"=>"-100",
		"preSet2subNumberOfFields"=>"100",
		"preSet2subUseAfterWidth" => "101",
		"preSet2subFieldInList"=>"102",
		"preSet2subFieldInListWidth"=>"103",
		"preSet2subSecondFieldInList"=>"102",
		"preSet2subSecondFieldInListWidth"=>"103",
		"preSet2subThirdFieldInList"=>"102",
		"preSet2subThirdFieldInListWidth"=>"103",
		"preSet2subForthFieldInList"=>"102",
		"preSet2subForthFieldInListWidth"=>"103",
		"preSet2subFifthFieldInList"=>"102",
		"preSet2subFifthFieldInListWidth"=>"103",
		"preSet2subSixthFieldInList"=>"102",
		"preSet2subSixthFieldInListWidth"=>"103",
		"preSet2subListAdd"=>"104",
	),
	"preSubmoduleListSet3"=>array(
		"preSubmoduleSet3list"=>"-100",
		"preSet3subNumberOfFields"=>"100",
		"preSet3subUseAfterWidth" => "101",
		"preSet3subFieldInList"=>"102",
		"preSet3subFieldInListWidth"=>"103",
		"preSet3subSecondFieldInList"=>"102",
		"preSet3subSecondFieldInListWidth"=>"103",
		"preSet3subThirdFieldInList"=>"102",
		"preSet3subThirdFieldInListWidth"=>"103",
		"preSet3subForthFieldInList"=>"102",
		"preSet3subForthFieldInListWidth"=>"103",
		"preSet3subFifthFieldInList"=>"102",
		"preSet3subFifthFieldInListWidth"=>"103",
		"preSet3subSixthFieldInList"=>"102",
		"preSet3subSixthFieldInListWidth"=>"103",
		"preSet3subListAdd"=>"104",
	),
	"preSubmoduleListSet4"=>array(
		"preSubmoduleSet4list"=>"-100",
		"preSet4subNumberOfFields"=>"100",
		"preSet4subUseAfterWidth" => "101",
		"preSet4subFieldInList"=>"102",
		"preSet4subFieldInListWidth"=>"103",
		"preSet4subSecondFieldInList"=>"102",
		"preSet4subSecondFieldInListWidth"=>"103",
		"preSet4subThirdFieldInList"=>"102",
		"preSet4subThirdFieldInListWidth"=>"103",
		"preSet4subForthFieldInList"=>"102",
		"preSet4subForthFieldInListWidth"=>"103",
		"preSet4subFifthFieldInList"=>"102",
		"preSet4subFifthFieldInListWidth"=>"103",
		"preSet4subSixthFieldInList"=>"102",
		"preSet4subSixthFieldInListWidth"=>"103",
	),

	"Getynet related"=>"-1",
	"showInGetynetMenu"=>"2",
	"fieldMenuname"=>"3",

	"Module"=>"-1",
	"moduledatatype"=>"21",
	"moduletype"=>"8",
	"moduleLibraryType"=>"24",
	"moduleMainTable"=>"2",
	"moduleTableAccesslevel"=>"19",
	"moduleTableAdminAccess"=>"2",
	"moduleTableSystemAdminAccess"=>"2",
	/*"outputModule"=>"2",*/
	"linkToModuleID"=>"7",
	"jumpfirstpage"=>"2",
	"numberLevels"=>"22",


	"Sending"=>"-1",
	"showSendMail"=>"2",
	"sendEmailTemplate"=>"9",
	"sendEmailActivateGetynetUsers"=>"2",
	"sendEmailActivateCustomUsers"=>"2",
	"sendEmailUserSource"=>"12",
	"showSendSms"=>"2",
	"sendSmsTemplate"=>"10",
	"sendSmsActivateGetynetUsers"=>"2",
	"sendSmsActivateCustomUsers"=>"2",
	"sendSmsUserSource"=>"12",
	"showSendPdf"=>"2",
	"sendPdfTemplate"=>"11",
	"sendPdfActivateGetynetUsers"=>"2",
	"sendPdfActivateCustomUsers"=>"2",
	"sendPdfUserSource"=>"13",
	
	"Backup"=>"-1",
	"activateHistory"=>"2",
	"activateSafeDelete"=>"2",
	
	"SEO"=>"-1",
	"activateSeo"=>"2",
	"expandSeoSettings"=>"2",
	"seoUrlEditType"=>"20",
	"seoTitleField"=>"3",
	"seoDescriptionField"=>"3",
	"seoUrlField"=>"3",
	"seoMenuField"=>"3",
	"enableEmptySeoUrl"=>"2",
	"activateSeoHeadingPreview"=>"2",
	"activateSeoKeywords"=>"2",
	"mergeHeaderAndMenulevelInTitle"=>"2",
	"activateOpenGraph"=>"2"
);
if(isset($_POST['send'])) $submodule = $_POST['submodule'];

$useLines = $variableNames = $variableVal = array();
$settingsFile = file($module_absolute_path."/input/settings/tables/$submodule.php");
foreach($settingsFile as $testLine)
{
	$use = trim($testLine);
	if($use[0] == "$")
	{
		$nameFind = explode("=",$use);
		$variableName = str_replace("$","",trim($nameFind[0]));
		$useLines[$variableName] = $use;
		$variableNames[$variableName] = $variableName;
		if(!array_search($variableName,$ignoreMe))
		{		     
			$variableVal[$variableName] = trim($nameFind[1]," ;\"");
		}
	}  
}

if(isset($_POST['send']))
{
	define('BASEPATH', realpath(__DIR__.'/../../../../').DIRECTORY_SEPARATOR);
	require_once(BASEPATH.'elementsGlobal/cMain.php');
	include(__DIR__."/ftp_commands.php");
	if(!function_exists("log_action")) include(__DIR__."/fn_log_action.php");
	log_action("save_settings");
	
	$newFile = "";
	$module = $_POST['module'];
	$moduleID = $_POST['moduleID'];
	foreach($useLines as $key => $value)
	{
		if(array_search($variableNames[$key],$ignoreMe))
		{
			$newFile .= $value."\n";
		}	 
	}
	
	foreach($useVars as $key => $value)
	{
		if(!array_search($variableNames[$key],$ignoreMe) and $value >= 0) // and ignore headlines
		{
			if($value == 12 or $value == 13)
			{
				foreach($_POST[$key] as $x => $y) if(trim($y)=="") unset($_POST[$key][$x]); else $_POST[$key][$x] = trim($y);
				$newFile .= "\$".$key." = \"".addslashes(json_encode($_POST[$key]))."\";\n";
			} else {
				$newFile .= "\$".$key." = \"".$_POST[$key]."\";\n";
			}
			$variableVal[$key] = $_POST[$key];
			if(is_array($value)){
				foreach($value as $key2=>$value2){
					if(!array_search($variableNames[$key2],$ignoreMe) and $value2 >= 0) // and ignore headlines
					{
						if($value2 == 12 or $value2 == 13)
						{
							foreach($_POST[$key2] as $x => $y) if(trim($y)=="") unset($_POST[$key2][$x]); else $_POST[$key2][$x] = trim($y);
							$newFile .= "\$".$key2." = \"".addslashes(json_encode($_POST[$key2]))."\";\n";
						} else {
							$newFile .= "\$".$key2." = \"".$_POST[$key2]."\";\n";
						}
					}
				}
			}
		}	 
	}
	ftp_file_put_content("/modules/$module/input/settings/tables/$submodule.php","<?php\n{$newFile}?>");
	
	header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&includefile=chooseToUpdate");
	exit;
}  
?>
<style>
	.editTableSettings {
		width: 100%;
	}
	.tableSettingsRow {
		padding: 3px 0px;
	}
	.form-control {

	}
	.tableSettingsRow .tableSettingsLabel {
		width: 20%;
		display: inline-block;
		vertical-align: top;
		word-break: break-all;
	}
	.tableSettingsRow .tableSettingsContent {
		width: 68%;
		display: inline-block;
		vertical-align: top;
	}
	.tableSettingsRow.fieldGroup {
		display: none;
	}
	.tableSettingsRow.fieldGroupWidth {
		display: none;
	}
	.tableSettingsRow .tableSettingsContent .inlineBlock {
		display: inline-block; 
		vertical-align: middle;
	}
	.fieldsetAddBtn {
		display: inline-block;
		padding: 6px 16px;
		border-radius: 3px;
		font-size: 12px;
		font-weight: bold;
		background: #0497E5 none repeat scroll 0% 0%;
		cursor: pointer;
		color: #ddd;
	}
	.fieldsetAddBtn:hover {
		color: #fff;
	}
</style>
<div style="padding:5px 10px;">
<form action="<?php echo $extradir."/input/includes/editTableSettings.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule;?>" method="post">
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
	<div class="editTableSettings">
	<?php  
	foreach($useVars as $key => $value)
	{
		switch($value)
		{
			case -2:
			?>
				<div class="tableSettingsRow config-<?php echo $key;?>"><h4><?php echo str_replace("pre","",$key);?></h4></div>
			<?php
				break;
			case -1:
			?>				
				<div class="tableSettingsRow config-<?php echo $key;?>"><h2><?php echo str_replace("pre","",$key);?></h2></div>
			<?php
				break;
			case 0:
			?>
				<div class="tableSettingsRow config-<?php echo $key;?>">
					<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key);?></div>
					<div class="tableSettingsContent"><input style="width:400px;" name="<?php echo $key; ?>" value="<?php echo $variableVal[$key]; ?>" type="text" class="form-control input-sm" /></div>
				</div>
			<?php
				break;
			case 1:
			?>				
				<div class="tableSettingsRow config-<?php echo $key;?>">
					<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key);?></div>
					<div class="tableSettingsContent"><textarea style="width:400px; height:60px;" name="<?php echo $key; ?>" class="form-control input-sm"><?php echo $variableVal[$key]; ?></textarea></div>
				</div>
			<?php				
				break;
			case 2:
			?>
				<div class="tableSettingsRow config-<?php echo $key;?>">
					<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key);?></div>
					<div class="tableSettingsContent">
						<select style="width:400px;" name="<?php echo $key; ?>" class="form-control input-sm">
							<option value="0"<?php if($variableVal[$key] == 0){ ?> selected<?php } ?>>No</option>
							<option value="1"<?php if($variableVal[$key] == 1){ ?> selected<?php } ?>>Yes</option>
						</select>
					</div>
				</div>
			<?php
				break;
			case 3:
				?>
				<div class="tableSettingsRow config-<?php echo $key;?>">
					<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key);?></div>
					<div class="tableSettingsContent">
						<select style="width:400px;" name="<?php echo $key; ?>" class="form-control input-sm">
							<option value=""><?php echo $formText_none_input;?></option>
							<?php 
							foreach($fields as $choice){ ?>
							<option value="<?php echo $choice[0]; ?>"<?php if($variableVal[$key] == $choice[0]){ ?> selected<?php } ?>><?php echo $choice[0]; ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
				<?php
				break;
			case 4:
				?>
				<div class="tableSettingsRow config-<?php echo $key;?>">
					<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key);?></div>
					<div class="tableSettingsContent">
						<select style="width:400px;" name="<?php echo $key; ?>" class="form-control input-sm">
							<option value="0"<?php if($variableVal[$key] == 0){ ?> selected<?php } ?>>ASC</option>
							<option value="1"<?php if($variableVal[$key] == 1){ ?> selected<?php } ?>>DESC</option>
						</select>
					</div>
				</div>
				<?php
				break;
			case 5:
				?>
				<div class="tableSettingsRow config-<?php echo $key;?>">
					<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key);?></div>
					<div class="tableSettingsContent">
						<select style="width:400px;" name="<?php echo $key; ?>" class="form-control input-sm">
							<option value="0"<?php if($variableVal[$key] == $choice[0]){ ?> selected<?php } ?>>No sorting</option>
							<?php 
							foreach($fields as $choice){ ?>
							<option value="<?php echo $choice[0]; ?>"<?php if($variableVal[$key] == $choice[0]){ ?> selected<?php } ?>><?php echo $choice[0]; ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
				<?php
				break;
			case 6:
				?>
				<div class="tableSettingsRow config-<?php echo $key;?>">
					<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key);?></div>
					<div class="tableSettingsContent">
						<select style="width:400px;" name="<?php echo $key; ?>" class="form-control input-sm">
							<option value="0"<?php if($variableVal[$key] == 0){ ?> selected<?php } ?>>Above</option>
							<option value="1"<?php if($variableVal[$key] == 1){ ?> selected<?php } ?>>Before</option>
						</select>
					</div>
				</div>
				<?php
				break;
			case 7:
				?>
				<div class="tableSettingsRow config-<?php echo $key;?>">
					<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key);?></div>
					<div class="tableSettingsContent">
						<select style="width:400px;" name="<?php echo $key; ?>" class="form-control input-sm">
							<option value="1"<?php if($variableVal[$key] == 1){ ?> selected<?php } ?>>Yes</option>
							<option value="0"<?php if($variableVal[$key] == 0){ ?> selected<?php } ?>>No</option>
						</select>
					</div>
				</div>
				<?php
				break;
			case 8:
				?>
				<div class="tableSettingsRow config-<?php echo $key;?>">
					<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key);?></div>
					<div class="tableSettingsContent">
						<select style="width:400px;" name="<?php echo $key;?>" class="form-control input-sm">
							<option value="0"<?php if($variableVal[$key] == 0){ ?> selected<?php } ?>>Standard input</option>
							<option value="1"<?php if($variableVal[$key] == 1){ ?> selected<?php } ?>>Input in one column</option>
							<?php
							if(!function_exists("devide_by_uppercase")) include_once("fnctn_devide_by_upercase.php");
							$s_output_dir = __DIR__."/../../";
							if($o_handler = opendir($s_output_dir)) 
							{
								while(false !== ($s_file = readdir($o_handler)))
								{
									if($s_file!="." and $s_file!=".." and is_dir($s_output_dir."/".$s_file) && substr($s_file,0,6)=="output")
									{
										?><option value="<?php echo $s_file;?>"<?php echo ($variableVal[$key]==$s_file?' selected':'');?>><?php echo $s_file;?></option><?php
									}
								}
								closedir($o_handler);
							}
							?>
						</select>
					</div>
				</div>
				<?php
				break;
			case 9:
				?>
				<div class="tableSettingsRow config-<?php echo $key;?>">
					<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key);?></div>
					<div class="tableSettingsContent">
						<select style="width:400px;" name="<?php echo $key; ?>" class="form-control input-sm">
							<option value=""><?php echo $formText_none_input;?></option>
							<option value="default_notify"<?php echo ($variableVal[$key]=='default_notify'?' selected':'');?>><?php echo $formText_Notification_SendFromInput;?></option>
							<?php
							if(!function_exists("devide_by_uppercase")) include_once("fnctn_devide_by_upercase.php");
							$templateDir = __DIR__."/../../";
							if($handle = opendir($templateDir)) 
							{
								while(false !== ($file = readdir($handle)))
								{
									if($file!="." and $file!=".." and is_dir($templateDir."/".$file))
									{
										if(strpos($file,"output_emailFromModule_")!==false)
										{
											$template = str_replace("output_emailFromModule_","",$file);
											?><option value="<?php echo $template;?>"<?php echo ($variableVal[$key]==$template?' selected':'');?>><?php echo devide_by_uppercase($template);?></option><?php
										}
									}
								}
								closedir($handle);
							}
							?>
						</select>
					</div>
				</div>
				<?php
				break;
			case 10:
			?>
				<div class="tableSettingsRow config-<?php echo $key;?>">
					<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key);?></div>
					<div class="tableSettingsContent">
						<select style="width:400px;" name="<?php echo $key; ?>" class="form-control input-sm">
							<option value=""><?php echo $formText_none_input;?></option>
							<option value="default_notify"<?php echo ($variableVal[$key]=='default_notify'?' selected':'');?>><?php echo $formText_Notification_SendFromInput;?></option>
							<?php
							//output folders in module with "output_emailFromModule_[Name]" string.
							if(!function_exists("devide_by_uppercase")) include_once("fnctn_devide_by_upercase.php");
							$templateDir = __DIR__."/../../";
							if($handle = opendir($templateDir)) 
							{
								while(false !== ($file = readdir($handle)))
								{
									if($file!="." and $file!=".." and is_dir($templateDir."/".$file))
									{
										if(strpos($file,"output_smsFromModule_")!==false)
										{
											$template = str_replace("output_smsFromModule_","",$file);
											?><option value="<?php echo $template;?>"<?php echo ($variableVal[$key]==$template?' selected':'');?>><?php echo devide_by_uppercase($template);?></option><?php
										}
									}
								}
								closedir($handle);
							}
							?>
						</select>
					</div>
				</div>
			<?php
				break;
			case 11:
			?>
				<div class="tableSettingsRow config-<?php echo $key;?>">
					<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key);?></div>
					<div class="tableSettingsContent">
						<select style="width:400px;" name="<?php echo $key; ?>" class="form-control input-sm">
							<option value=""><?php echo $formText_none_input;?></option>
							<option value="default_notify"<?php echo ($variableVal[$key]=='default_notify'?' selected':'');?>><?php echo $formText_Notification_SendFromInput;?></option>
							<?php
							//output folders in module with "output_pdfFromModule_[Name]" string.
							if(!function_exists("devide_by_uppercase")) include_once("fnctn_devide_by_upercase.php");
							$templateDir = __DIR__."/../../";
							if($handle = opendir($templateDir)) 
							{
								while(false !== ($file = readdir($handle)))
								{
									if($file!="." and $file!=".." and is_dir($templateDir."/".$file))
									{
										if(strpos($file,"output_pdfFromModule_")!==false)
										{
											$template = str_replace("output_pdfFromModule_","",$file);
											?><option value="<?php echo $template;?>"<?php echo ($variableVal[$key]==$template?' selected':'');?>><?php echo devide_by_uppercase($template);?></option><?php
										}
									}
								}
								closedir($handle); 
							}
							?>
						</select>
					</div>
				</div>
			<?php
				break;
			case 12:
			?>
				<div class="tableSettingsRow config-<?php echo $key;?>">
					<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key);?></div>
					<div class="tableSettingsContent">
						<div style="border:1px solid #c9c9c9; padding:5px 5px 0 5px;">
							<div class="wraper"><?php
								$data = json_decode(stripslashes($variableVal[$key]),true);
								foreach($data as $dataValue)
								{
									if($dataValue=="") continue;
									?><input style="width:380px; margin-bottom:5px" name="<?php echo $key;?>[]" value="<?php echo $dataValue;?>" type="text" /><?php
								}
								?><input class="clone form-control input-sm" style="width:380px; margin-bottom:5px" name="<?php echo $key;?>[]" value="" type="text" /><?php
								?>
							</div>
							<div>[source_table]:[friendly_name]:[email_or_mobile]:[name(can be merged more fields separated by comma)]:[extra_field_1(optional)]:[extra_field_2(optional)]:[field_order]:[sort_by_field]:[import_empty_email_or_mobile(write 1)](:)[prefilter]:[field]:[value] or [sys]:[category_set_Ids(separated by comma)] or [mod]:[on_sub_levels(0 or 1)]:[module]:[field]<br><br>[name] - possible to specify more fields for example if firstname and lastname is in two columns, then its possible to merge specifying "firstname,lastname". In extra its possible to merge some extra value in DB (not dropdowns, checkboxes)<br><br>[field_order] - specify order for fields (email, name, extra1, extra2) by writing order number separated by comma. Example: 2,1,3,4 will show in list name, email, extra1, extra2.<br><br>[sort_by_field] - specify by which field to sort userlist. Example: to sort by extra1 wirite here 3.<br><br>Source, prefilter or filter options should be separated with (:)</div>
							<input type="button" value="<?php echo $formText_Add_inputSettings;?>" onClick="$(this).prevAll('.wraper').append($(this).prevAll('.wraper').children('input.clone').clone().removeClass('clone').val(''));" style="float:right;">
							<br clear="all">
						</div>
					</div>
				</div>

			<?php
				break;
			case 13:
			?>
				<div class="tableSettingsRow config-<?php echo $key;?>">
					<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key);?></div>
					<div class="tableSettingsContent">
						<div style="border:1px solid #c9c9c9; padding:5px 5px 0 5px;">
							<div class="wraper">
								<?php
								$data = json_decode(stripslashes($variableVal[$key]),true);
								foreach($data as $dataValue)
								{
									if($dataValue=="") continue;
									?><input style="width:380px; margin-bottom:5px" name="<?php echo $key;?>[]" value="<?php echo $dataValue;?>" type="text" /><?php
								}
								?><input class="clone form-control input-sm" style="width:380px; margin-bottom:5px" name="<?php echo $key;?>[]" value="" type="text" /><?php
								?>
							</div>
							<div>[source_table]:[friendly_name]:[name(can be merged more fields separated by comma)]:[extra_field_1]:[extra_field_2]:[extra_field_3]:[extra_field_4]:[extra_field_5]:[extra_field_6]:[extra_field_7]:[extra_field_8]:[extra_field_9]:[extra_field_10]:[field_order]:[sort_by_field]:[import_empty_email_or_mobile(write 1)](:)[prefilter]:[field]:[value] or [sys]:[category_set_Ids(separated by comma)] or [mod]:[on_sub_levels(0 or 1)]:[module]:[field]<br><br>[name] - possible to specify more fields for example if firstname and lastname is in two columns, then its possible to merge specifying "firstname,lastname". In extra its possible to merge some extra value in DB (not dropdowns, checkboxes)<br><br>[field_order] - specify order for fields (email, name, extra1, extra2) by writing order number separated by comma. Example: 2,1,3,4 will show in list name, email, extra1, extra2.<br><br>[sort_by_field] - specify by which field to sort userlist. Example: to sort by extra1 wirite here 3.<br><br>Source, prefilter or filter options should be separated with (:)</div>
							<input type="button" value="<?php echo $formText_Add_inputSettings;?>" onClick="$(this).prevAll('.wraper').append($(this).prevAll('.wraper').children('input.clone').clone().removeClass('clone').val(''));" style="float:right;">
							<br clear="all">
						</div>
					</div>
				</div>
			<?php
				break;
			case 14:
			?>
				<div class="tableSettingsRow config-<?php echo $key;?>">
					<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key);?></div>
					<div class="tableSettingsContent">
						<select style="width:400px;" name="<?php echo $key; ?>" class="form-control input-sm">
							<option value="0"<?php if($variableVal[$key] == 0){ ?> selected<?php } ?>>All content</option>
							<option value="1"<?php if($variableVal[$key] == 1){ ?> selected<?php } ?>>Just list</option>
							<option value="2"<?php if($variableVal[$key] == 2){ ?> selected<?php } ?>>All other (NOT list)</option>
							<option value="10"<?php if($variableVal[$key] == 10){ ?> selected<?php } ?>>None</option>
						</select>
					</div>
				</div>
			<?php
				break;
			case 15:
				?>
				<div class="tableSettingsRow config-<?php echo $key;?>">
					<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key);?></div>
					<div class="tableSettingsContent">
						<select style="width:400px;" name="<?php echo $key; ?>" class="form-control input-sm">
							<option value="0"<?php if($variableVal[$key] == 0){ ?> selected<?php } ?>>Start</option>
							<option value="1"<?php if($variableVal[$key] == 1){ ?> selected<?php } ?>>Inside</option>
						</select>
					</div>
				</div>
			<?php
				break;	
			case 16:
				?>
				<div class="tableSettingsRow config-<?php echo $key;?>">
					<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key);?></div>
					<div class="tableSettingsContent">
						<select style="width:400px;" name="<?php echo $key; ?>" class="form-control input-sm">
							<option value="0"<?php if($variableVal[$key] == 0){ ?> selected<?php } ?>>Manual</option>
							<option value="1"<?php if($variableVal[$key] == 1){ ?> selected<?php } ?>>Field</option>
						</select>
					</div>
				</div>
				<?php
				break;	
			case 17:
				?>
				<div class="tableSettingsRow config-<?php echo $key;?>">
					<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key);?></div>
					<div class="tableSettingsContent">
						<select style="width:400px;" name="<?php echo $key; ?>" class="form-control input-sm">
							<option value=""><?php echo $formText_none_input;?></option>
							<?php 
							foreach($fields as $choice){ ?>
							<option value="<?php echo $choice[0]; ?>"<?php if($variableVal[$key] == $choice[0]){ ?> selected<?php } ?>><?php echo $choice[0]; ?></option>
							<?php } ?>
						</select>
						<input style="width:400px;" name="<?php echo $key; ?>" value="sortnr" readonly type="text" class="form-control input-sm" />
					</div>
				</div>
				<?php
				break;	
			case 18:
				?>
				<div class="tableSettingsRow config-<?php echo $key;?>">
					<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key);?></div>
					<div class="tableSettingsContent">
						<div class="inlineBlock">
							<input style="width:80px;" name="<?php echo $key; ?>" value="<?php echo $variableVal[$key]; ?>" class="form-control input-sm" type="text" />
						</div>
						<div class="inlineBlock">px</div>
					</div>
				</div>
				<?php
				break;		
			case 19:
				?>
				<div class="tableSettingsRow config-<?php echo $key;?>">
					<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key);?></div>
					<div class="tableSettingsContent">
						<select style="width:400px;" name="<?php echo $key;?>" class="form-control input-sm">
							<?php 
							foreach($developeraccesslevels as $access_id => $access_value)
							{
								?><option value="<?php echo $access_id;?>"<?php echo ($variableVal[$key]==$access_id?' selected':'');?>><?php echo $access_value;?></option><?php
							}
							?>
						</select>
					</div>
				</div>
				<?php
				break;
			case 20:
				?><div class="tableSettingsRow config-<?php echo $key;?>">
					<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key);?></div>
					<div class="tableSettingsContent">
						<select style="width:400px;" name="<?php echo $key; ?>" class="form-control input-sm">
							<option value="0"<?php if($variableVal[$key] == 0){ ?> selected<?php } ?>>No editing</option>
							<option value="1"<?php if($variableVal[$key] == 1){ ?> selected<?php } ?>>Edit content part</option>
							<option value="2"<?php if($variableVal[$key] == 2){ ?> selected<?php } ?>>Full url edit</option>
						</select>
					</div>
				</div><?php
				break;
			case 21:
				$v_moduledatatypes = array(0=>"Content module", 1=>"Menu module", 2=>"Always load output css", 3=>"Email template", 4=>"CRM email template", 10=>"System module", 100=>"Payment module", 101=>"Delivery module", 102=>"Giftcard module", 103=>"Customer module");
				?>
				<div class="tableSettingsRow config-<?php echo $key;?>">
					<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key);?></div>
					<div class="tableSettingsContent">
						<select style="width:400px;" name="<?php echo $key;?>" class="form-control input-sm">
						<?php
						foreach($v_moduledatatypes as $l_key => $s_type)
						{
							?><option value="<?php echo $l_key;?>"<?php echo ($variableVal[$key] == $l_key ? " selected":"");?>><?php echo $s_type;?></option><?php
						}
						?>
						</select>
					</div>
				</div>
				<?php
				break;
			case 22:
				$v_options = array(0=>"Not menu module", 1=>"Menu with 1 level", 2=>"Menu with 2 levels", 3=>"Menu with 3 levels", 4=>"Menu with 4 levels", 5=>"Menu with 5 levels");
				?>
				<div class="tableSettingsRow config-<?php echo $key;?>">
					<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key);?></div>
					<div class="tableSettingsContent">
						<select style="width:400px;" name="<?php echo $key;?>" class="form-control input-sm">
						<?php
						foreach($v_options as $s_key => $s_name)
						{
							?><option value="<?php echo $s_key;?>"<?php echo ($variableVal[$key] == $s_key ? " selected":"");?>><?php echo $s_name;?></option><?php
						}
						?>
						</select>
					</div>
				</div>
				<?php
				break;
			case 23:
				$v_options = array(0=>"Search in specific field", 1=>"Search in visible list fields", 2=>"Search in all fields");
				?>
				<div class="tableSettingsRow config-<?php echo $key;?>">
					<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key);?></div>
					<div class="tableSettingsContent">
						<select style="width:400px;" name="<?php echo $key;?>" class="form-control input-sm">
						<?php
						foreach($v_options as $s_key => $s_name)
						{
							?><option value="<?php echo $s_key;?>"<?php echo ($variableVal[$key] == $s_key ? " selected":"");?>><?php echo $s_name;?></option><?php
						}
						?>
						</select>
					</div>
				</div>
				<?php
				break;
			case 24:
				$v_options = array(1=>"Complete module", 2=>"Reduced input (Settings and Language)");
				?>
				<div class="tableSettingsRow config-<?php echo $key;?>">
					<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key);?></div>
					<div class="tableSettingsContent">
						<select style="width:400px;" name="<?php echo $key;?>" class="form-control input-sm">
						<?php
						foreach($v_options as $s_key => $s_name)
						{
							?><option value="<?php echo $s_key;?>"<?php echo ($variableVal[$key] == $s_key ? " selected":"");?>><?php echo $s_name;?></option><?php
						}
						?>
						</select>
					</div>
				</div>
				<?php
				break;
			case 200:
				
				?>
				<div class="tableSettingsRow">
					<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key);?></div>
					<div class="tableSettingsContent">
						<select style="width:400px;" name="<?php echo $key; ?>" class="form-control input-sm">
							<?php
							$getModules = "SELECT * FROM moduledata WHERE type = 1 AND content_status < 2";
							$o_query = $o_main->db->query($getModules);
							if($o_query && $menuModules = $o_query->result_array()) {
								foreach($menuModules as $menuModule)
								{
									?><option value="<?php echo $menuModule['uniqueID'];?>"<?php echo ($variableVal[$key] == $menuModule['uniqueID'] ? " selected":"");?>><?php echo $menuModule['name'];?></option><?php
								}
					 		} ?>
							?>
						</select>
					</div>
				</div>
				<?php
				break;
			default: 
				if(is_array($value)){
					fieldListSettingReturn($key, $value, $variableVal, $fields, $formText_none_input, true, $formText_Add_input);
				}
				break;
		}
	}
	
	function fieldListSettingReturn($key, $value, $variableVal, $fields, $formText_none_input, $addBtn, $formText_Add_input){
		?>
		<div class="group-<?php echo $key?> groupField">
		<?php foreach($value as $key2=>$value2){
			switch($value2){
				case -100:
					?>
					<div class="tableSettingsRow config-<?php echo $key;?>"><h4><?php echo str_replace("pre","",$key2);?></h4></div>
					<?php
					break;
				case 100:
					?>
					<div class="tableSettingsRow config-<?php echo $key;?>">
						<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key2);?></div>
						<div class="tableSettingsContent">
							<select style="width:400px;" name="<?php echo $key2; ?>" class="select-<?php echo $key2; ?> form-control input-sm">
								<option value="0"<?php if($variableVal[$key2] == 0){ ?> selected<?php } ?>>Select</option>
								<option value="1"<?php if($variableVal[$key2] == 1){ ?> selected<?php } ?>>1</option>
								<option value="2"<?php if($variableVal[$key2] == 2){ ?> selected<?php } ?>>2</option>
								<option value="3"<?php if($variableVal[$key2] == 3){ ?> selected<?php } ?>>3</option>
								<option value="4"<?php if($variableVal[$key2] == 4){ ?> selected<?php } ?>>4</option>
								<option value="5"<?php if($variableVal[$key2] == 5){ ?> selected<?php } ?>>5</option>
								<option value="6"<?php if($variableVal[$key2] == 6){ ?> selected<?php } ?>>6</option>
							</select>
						</div>
					</div>
					<script type="text/javascript">	
					<?php if(intval($variableVal[$key2]) > 0) { ?>							
						var firstChange<?php echo $key2?> = false;
						$(".group-<?php echo $key?>").prev().find(".fieldsetAddBtn").hide();
					<?php }else {?>
						var firstChange<?php echo $key2?> = true;	
						if($(".group-<?php echo $key?>").prev().hasClass("groupField")){
							$(".group-<?php echo $key?>").hide();								
						}
					<?php } ?>
					$(".group-<?php echo $key?> .select-<?php echo $key2; ?>").change(function(){	
						$(".group-<?php echo $key?> .fieldGroupWidth").find("input").prop("readonly", false);					
						var count = parseInt($(this).val());
						if(count > 0){
							$(".group-<?php echo $key?> .fieldGroup").find("select").prop("disabled", false);
							$(".group-<?php echo $key?> .fieldGroupWidth").find("input").prop("disabled", false);
							
							$(".group-<?php echo $key?> .fieldGroup").each(function(index, el){
								if(index < count){
									$(this).show();
								}else{
									$(this).hide();
									$(this).find("select").val("");
								}
							});
							$(".group-<?php echo $key?> .fieldGroupWidth").each(function(index, el){
								if(index < count){
									$(this).show();
								}else{
									$(this).hide();
									$(this).find("input").val("");
								}
							});
						}else{
							$(".group-<?php echo $key?> .fieldGroup").hide().find("select").prop("disabled", true);
							$(".group-<?php echo $key?> .fieldGroupWidth").hide();
							$(".group-<?php echo $key?> .fieldGroupWidth").find("input").val("").prop("disabled", true);
						}
						
						var eachColumnWidth = Math.floor(100/count);
						var result = 0;
						if(firstChange<?php echo $key2?>){
							$(".group-<?php echo $key?> .fieldGroupWidth").each(function(index, el){
								if(index < count){
									if(index != count-1){
										$(this).find("input").val(eachColumnWidth);										
										result += eachColumnWidth;
									}else {
										var lastColumnWidth = 100 - result;
										$(this).find("input").val(lastColumnWidth);									
									}
									
								}
							})
						}
						var lastElement = $(".group-<?php echo $key?> .fieldGroupWidth").eq(count-1);
						lastElement.find("input").prop("readonly", true);
					})
				
					$(".group-<?php echo $key?> .select-<?php echo $key2; ?>").change();

					$(".group-<?php echo $key?> .fieldGroupWidth input").unbind("change").change(function(){
						var count = parseInt($(this).parents(".groupField").find(".select-<?php echo $key2; ?>").val());
						if(count > 0){
							var result = 0;
							var lastColumnWidth = -1;
							var lastColumnIndex = count-1;
							while(lastColumnWidth < 0){
								$(".group-<?php echo $key?> .fieldGroupWidth").each(function(index, el){
									if(index < count){
										if(index != lastColumnIndex){
											result += parseInt($(this).find("input").val());
										}else {
											lastColumnWidth = 100 - result;
											if(lastColumnWidth < 0){
												lastColumnIndex--;
												result = 0;
												lastColumnWidth2 = 0;
											}else{
												lastColumnWidth2 = lastColumnWidth;
											}
											$(this).find("input").val(lastColumnWidth2);	
																			
										}									
									}								
								})
							}
						}
					})

					$(document).ready(function(){
						$(".group-<?php echo $key?> .select-<?php echo $key2; ?>").change();
						//show selected number of fields
						$(".group-<?php echo $key?> .select-<?php echo $key2; ?>").change(function(){	
							$(".group-<?php echo $key?> .fieldGroupWidth").find("input").prop("readonly", false);					
							var count = parseInt($(this).val());
							if(count > 0){
								$(".group-<?php echo $key?> .fieldGroup").find("select").prop("disabled", false);
								$(".group-<?php echo $key?> .fieldGroupWidth").find("input").prop("disabled", false);
								
								$(".group-<?php echo $key?> .fieldGroup").each(function(index, el){
									if(index < count){
										$(this).show();
									}else{
										$(this).hide();
										$(this).find("select").val("");
									}
								});
								$(".group-<?php echo $key?> .fieldGroupWidth").each(function(index, el){
									if(index < count){
										$(this).show();
									}else{
										$(this).hide();
										$(this).find("input").val("");
									}
								});
							}else{
								$(".group-<?php echo $key?> .fieldGroup").hide().find("select").prop("disabled", true);
								$(".group-<?php echo $key?> .fieldGroupWidth").hide();
								$(".group-<?php echo $key?> .fieldGroupWidth").find("input").val("").prop("disabled", true);
							}
							var eachColumnWidth = Math.floor(100/count);
							var result = 0;
							if(firstChange<?php echo $key2?>){
								$(".group-<?php echo $key?> .fieldGroupWidth").each(function(index, el){
									if(index < count){
										if(index != count-1){
											$(this).find("input").val(eachColumnWidth);										
											result += eachColumnWidth;
										}else {
											var lastColumnWidth = 100 - result;
											$(this).find("input").val(lastColumnWidth);									
										}
										
									}
								})
							}else{							
								firstChange<?php echo $key2?> = true;
							}
							var lastElement = $(".group-<?php echo $key?> .fieldGroupWidth").eq(count-1);
							lastElement.find("input").prop("readonly", true);
						})		
						$(".group-<?php echo $key?> .fieldGroupWidth input").unbind("change").change(function(){
							var count = parseInt($(this).parents(".groupField").find(".select-<?php echo $key2; ?>").val());
							if(count > 0){
								var result = 0;
								var lastColumnWidth = -1;
								var lastColumnIndex = count-1;
								while(lastColumnWidth < 0){
									$(".group-<?php echo $key?> .fieldGroupWidth").each(function(index, el){
										if(index < count){
											if(index != lastColumnIndex){
												result += parseInt($(this).find("input").val());
											}else {
												lastColumnWidth = 100 - result;
												if(lastColumnWidth < 0){
													lastColumnIndex--;
													result = 0;
													lastColumnWidth2 = 0;
												}else{
													lastColumnWidth2 = lastColumnWidth;
												}
												$(this).find("input").val(lastColumnWidth2);	
																				
											}									
										}								
									})
								}
							}
						})
					})
					</script>
					<?php
					break;
				case 101:
					?>
					<div class="tableSettingsRow config-<?php echo $key;?>">
						<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key2);?></div>
						<div class="tableSettingsContent">
							<div class="inlineBlock">
								<input style="width:80px;" name="<?php echo $key2; ?>" value="<?php echo $variableVal[$key2]; ?>" class="form-control input-sm" type="text" />
							</div>
							<div class="inlineBlock">px</div>
						</div>
					</div>
					<?php
					break;
				case 102:
					?>
					<div class="tableSettingsRow config-<?php echo $key;?> fieldGroup">
						<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key2);?></div>
						<div class="tableSettingsContent">
							<select style="width:400px;" name="<?php echo $key2; ?>"  class="form-control input-sm" >
								<option value=""><?php echo $formText_none_input;?></option>
								<?php 
								foreach($fields as $choice){ ?>
								<option value="<?php echo $choice[0]; ?>"<?php if($variableVal[$key2] == $choice[0]){ ?> selected<?php } ?>><?php echo $choice[0]; ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					<?php
					break;
				case 103:
					?>
					<div class="tableSettingsRow config-<?php echo $key;?> fieldGroupWidth">
						<div class="tableSettingsLabel"><?php echo str_replace("pre","",$key2);?></div>
						<div class="tableSettingsContent">
							<div class="inlineBlock">
								<input style="width:60px;" name="<?php echo $key2; ?>" value="<?php echo $variableVal[$key2]; ?>" class="form-control input-sm" type="text" />
							</div>
							<div class="inlineBlock"> % </div>
						</div>
					</div>
					<?php
					break;

				case 104:
					if($addBtn){
						?>
						<div class="tableSettingsRow config-<?php echo $key;?>">
							<div class="fieldsetAddBtn"><?php echo $formText_Add_input;?></div>
						</div>
						<script type="text/javascript">
						$(".group-<?php echo $key?> .fieldsetAddBtn").unbind("click").bind("click", function(){
							$(this).parents(".groupField").next(".groupField").show();	
							$(this).hide();
						})	
						</script>
						<?php
					}
					break;
			}
		}
		?>
		</div>
		<?php
	}
	?>
	</div>
	<div class="fieldholder content_buttons" style="padding-top:5px;">
		<input class="content_button ui-corner-all " type="submit" name="send" value="Save" />
	</div> 
</form>
</div>
<br /><br />
<script type="text/javascript">
$(".config-presearchMethod select").change(function(){
	if($(this).val() >= 1){
		$(".config-searchFieldName select").prop("disabled", true).hide();
	}else{
		$(".config-searchFieldName select").prop("disabled", false).show();
	}
});
$(".config-orderManualOrByField select").change(function(){
	if($(this).val() == 0){
		$(".config-preorderByField select").prop("disabled", true).hide();
		$(".config-preorderByField input").prop("disabled", false).show();
	}else{
		$(".config-preorderByField select").prop("disabled", false).show();
		$(".config-preorderByField input").prop("disabled", true).hide();
	}
});
$(document).ready(function(){
	$(".config-presearchMethod select").change();
	$(".config-orderManualOrByField select").change();
})
</script>