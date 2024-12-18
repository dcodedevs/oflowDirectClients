<?php
//The settings variables
//		For setting variable to appear in settings list you have to use such variable nameing :
//		If its just a variable for storing some kind of settings
//		sude be used such naming :settingsVar_name_contextDescription
//		If the variable should be choice type (dropdownselect)
//		should be used such naming  settingsChoice_name_contextDescription
// 
//Language variables
//		for using language variables use nameing formText_nameOfTheVariable_contextDescription
//		then it will appear in language module automatically..
//error_reporting(E_ERROR | E_WARNING | E_PARSE); ini_set("display_errors", 1);

include(__DIR__."/includes/config_module.php");
$listFieldVariables = array('prefieldInList','presecondFieldInList','prethirdFieldInList','preforthFieldInList','prefifthFieldInList','presixthFieldInList');
$listSet2FieldVariables = array('preSet2fieldInList','preSet2secondFieldInList','preSet2thirdFieldInList',
	'preSet2forthFieldInList','preSet2fifthFieldInList','preSet2sixthFieldInList');
$listSet3FieldVariables = array('preSet3fieldInList','preSet3secondFieldInList','preSet3thirdFieldInList',
	'preSet3forthFieldInList','preSet3fifthFieldInList','preSet3sixthFieldInList');
$listSet4FieldVariables = array('preSet4fieldInList','preSet4secondFieldInList','preSet4thirdFieldInList',
	'preSet4forthFieldInList','preSet4fifthFieldInList','preSet4sixthFieldInList');

$listFieldInSubmoduleVariables = array('presubFieldInList','presubSecondFieldInList','presubThirdFieldInList','presubForthFieldInList',
	'presubFifthFieldInList','presubSixthFieldInList');
$listSet2FieldInSubmoduleVariables = array('preSet2subFieldInList','preSet2subSecondFieldInList','preSet2subThirdFieldInList',
	'preSet2subForthFieldInList','preSet2subFifthFieldInList','preSet2subSixthFieldInList');
$listSet3FieldInSubmoduleVariables = array('preSet3subFieldInList','preSet3subSecondFieldInList','preSet3subThirdFieldInList',
	'preSet3subForthFieldInList','preSet3subFifthFieldInList','preSet3subSixthFieldInList');
$listSet4FieldInSubmoduleVariables = array('preSet4subFieldInList','preSet4subSecondFieldInList','preSet4subThirdFieldInList',
	'preSet4subForthFieldInList','preSet4subFifthFieldInList','preSet4subSixthFieldInList');

foreach($listFieldVariables as $var) unset(${$var});
unset($frameworkColumnOne, $frameworkColumnTwo);

if($extradir == '')
	$extradir = "../"."modules/".$module;
if(!isset($extradiraccountname))
	$extradiraccountname = "..";
if(!isset($extraimagedir))
	$extraimagedir = "../";
else
	$extraimagedir = "../../../".$extraimagedir;
//getynet account path fix (also for ckeditor/ckfinder):
if(isset($editordir))
{
	$accountdir = $editordir;
} else {
	$accountdir = "../";
}

$error_msg = json_decode($fw_session['error_msg'],true);
$menuaccess = json_decode($fw_session['cache_menu'],true);
$access = $menuaccess[$module][2];
$b_owner_access = ($menuaccess[$module][6] == 1 ? true : false);
if($variables->developeraccess >= 20)
{
	$access = 111;
	$b_owner_access = false;
}
$s_default_output_language = '';
$o_query = $o_main->db->query('SELECT languageID FROM language ORDER BY defaultOutputlanguage DESC, outputlanguage DESC, sortnr ASC');
if($o_query && $o_row = $o_query->row()) $s_default_output_language = $o_row->languageID;

$includefile = 'list';
if(isset($_GET['includefile'])) $includefile = $_GET['includefile'];

//reads language variables
include(__DIR__."/includes/readInputLanguage.php");

// ALI - check does user have appropriate permissions
$permissionsRead = array('list', 'sublist', 'edit');
$permissionsWrite = array('orderContent', 'sendEmail', 'sendSms', 'sendPdf', 'cropimage', 'editContentSettings');
if($access >= 100 /*all permissions*/ or ($access > 0 and in_array($includefile, $permissionsRead)) or ($access >=10 and in_array($includefile, $permissionsWrite)) )
{
	if(!function_exists("ftp_file_put_content")) include(__DIR__."/includes/ftp_commands.php");
	if(!function_exists("include_local")) include(__DIR__."/includes/fn_include_local.php");
	if(!function_exists("find_related_modules")) include(__DIR__."/includes/fnctn_find_related_modules.php");
	
	include(__DIR__."/includes/databaseFieldsCheck.php");
	
	if(sizeof($error_msg)>0)
	{
		$print = "";
		foreach($error_msg as $key => $message)
		{
			list($class,$rest) = explode("_",$key);
			$print .= addslashes('<div class="item ui-corner-all '.$class.'"><button type="button" class="close"><span>&times;</span></button>'.$message.'</div>');
		}
		if(isset($ob_javascript))
		{
			$ob_javascript .= ' $(function(){$(".fw_info_messages").html("'.$print.'").slideDown();});';
		} else {
			?><script type="text/javascript" language="javascript"><?php echo '$(function(){$(".fw_info_messages").html("'.$print.'").slideDown();});';?></script><?php
		}
		$fw_session['error_msg'] = array();
		$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
		$o_main->db->update('session_framework', array('error_msg' => ''), $v_param);
	}
	
	$headmodule = "";
	$submods = $v_module_main_tables = array();
	if($findBase = opendir(__DIR__."/settings/tables"))
	{
		while($s_file = readdir($findBase))
		{
			if($s_file == '.' || $s_file == '..') continue;
			$v_file = explode(".", $s_file);
			if($v_file[0] != "" && $v_file[1] == "php" && (!isset($v_file[2]) || $v_file[2] != "LCK"))
			{
				$submods[] = $v_file[0];
				$vars = include_local(__DIR__."/settings/tables/".$v_file[0].".php", $v_language_variables);
				$l_table_access = intval($vars['moduleTableAccesslevel']);
				$l_table_access_admin = intval($vars['moduleTableAdminAccess']);
				$l_table_access_sysadmin = intval($vars['moduleTableSystemAdminAccess']);
				
				if($vars['tableordernr'] == "1")
				{
					$headmodule = $v_file[0];
					$o_query = $o_main->db->query("SELECT * FROM moduledata WHERE name = ?", array($module));
					if($o_query && $o_row = $o_query->row())
					{
						if($o_row->local_name!='' && $variables->developeraccess == 0) $vars['preinputformName'] = $o_row->local_name;
					}
					$v_module_main_tables[1] = array($v_file[0], $vars['preinputformName'], $vars['moduletype']);
				}
				else if($vars['moduleMainTable'] == "1" && 
					($l_table_access <= $fw_session['developeraccess']
					|| $variables->developeraccess >= 20
					|| ($l_table_access_admin == 1 && $variables->useradmin == 1)
					|| ($l_table_access_sysadmin == 1 && $variables->system_admin == 1)
					)
				)
				{
					$l_id = intval($vars['tableordernr']);
					if(array_key_exists($vars['tableordernr'], $v_module_main_tables)) $l_id += 20;
					$v_module_main_tables[$l_id] = array($v_file[0], $vars['preinputformName'], $vars['moduletype']);
				}
			}
		}
		if($headmodule == "" && isset($submods[0]))
		{
			$headmodule = $submods[0];
		}
		if(count($v_module_main_tables)==0 && isset($submods[0]))
		{
			$vars = include_local(__DIR__."/settings/tables/".$submods[0].".php", $v_language_variables);
			$v_module_main_tables[1] = array($submods[0], $vars['preinputformName'], $vars['moduletype']);
		}
		if(is_file(__DIR__."/settings/tables/".$headmodule.".php")) include(__DIR__."/settings/tables/".$headmodule.".php");
		closedir($findBase);
	}
	$ID = $parentID = $level = $moduleID = 0;
	$headrelatedmodule = (isset($submods[1]) ? $submods[1] : '');
	$submodule = $headmodule;
	$languageEnding = array("all");
	$languageName = array();
	$languageName['all'] = "";
	$settingsChoice_maxLevel_inputMenuLevels = (isset($numberLevels) ? $numberLevels : 0);
	$ui_id_counter = 0;
	$ui_editform_id = preg_replace('/[^A-za-z0-9]+/','',uniqid());
	
	if(isset($_GET['deactivateID']) && is_numeric($_GET['deactivateID']))
	{
		$o_main->db->update($submodule, array('deactivated' => 1), array('id' => $_GET['deactivateID']));
	}
	
	include(__DIR__."/includes/moduleinit.php");
	
	// Framework columns
	$fw_showCustomized = $fw_showList = $fw_showOther = false;
	$fw_columns = 1;
	if(!isset($moduletype) || $moduletype == "0")
	{
		$moduletype = 0;
		$fw_columns = 2;
	} else {
		$moduletype = 1;
	}
	
	if(isset($_POST['fwajax']))
	{
		if($moduletype == 0 && ($includefile != 'list' || isset($_GET['actionType']))) $fw_column = 2;
	}
	if($fw_column == 1)
	{
		if($moduletype == 0)
		{
			$includefile = 'list';
			$fw_showList = true;
			unset($ID);
		}
		if($moduletype == 1) $fw_showCustomized = true;
	} else if($fw_column == 2) {
		if($moduletype == 0 && ($includefile != 'list' || isset($_GET['actionType']))) $fw_showOther = true;
	}
	
	if(!isset($moduleCol1MaxWidth) && isset($precolumn2ListMaxWidth)) $moduleCol1MaxWidth = $precolumn2ListMaxWidth;
	if(!isset($moduleCol1MinWidth) && isset($precolumn2ListMinWidth)) $moduleCol1MinWidth = $precolumn2ListMinWidth;
	if(!isset($moduleCol2MaxWidth) && isset($preDetailpageMaxWidth)) $moduleCol2MaxWidth = $preDetailpageMaxWidth;
	if(!isset($moduleCol2MinWidth) && isset($preDetailpageMinWidth)) $moduleCol2MinWidth = $preDetailpageMinWidth;
	
	$ob_module_relations = $ob_module_info = $ob_module_buttons = $ob_module_content = "";
	if(isset($_GET["includefile"]) && $_GET["includefile"] == "sublistpage")
	{
		include(__DIR__."/includes/sublistpage.php");
	} else if(isset($_GET["includefile"]) && $_GET["includefile"] == "showbuttons")
	{
		include(__DIR__."/includes/buttons.php");
	} else if($fw_showCustomized || $fw_showList || $fw_showOther)
	{
		/*
		** Get module head
		*/
		ob_start();
		if(count($v_module_main_tables)>0)
		{
			?><ul class="list-inline"><?php
			$v_keys = array_keys($v_module_main_tables);
			sort($v_keys);
			foreach($v_keys as $l_key)
			{
				?><li<?php echo ($v_module_main_tables[$l_key][0]==$submodule?' class="active"':'');?>><a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$v_module_main_tables[$l_key][0].(isset($_GET['relationID'])?"&relationID=".$_GET['relationID']:"").(isset($_GET['relationfield'])?"&relationfield=".$_GET['relationfield']:"").(!is_numeric($v_module_main_tables[$l_key][2])?"&folderfile=output&folder=".$v_module_main_tables[$l_key][2]:"");?>" class="optimize"><?php echo ($v_module_main_tables[$l_key][1]!=""?$v_module_main_tables[$l_key][1]:$v_module_main_tables[$l_key][0]);?></a></li><?php
			}
			?></ul><?php
		}
		$ob_module_head = ob_get_clean();
		
		/*
		** Get relations
		*/
		ob_start();
		$relationsID = "";
		if(isset($_GET['includefile']) && ($fw_showCustomized || $fw_showOther) && !isset($_GET['subcontent']))
		{
			$relationarray = array();
			$relationarray = array_reverse(find_related_modules($module,$relationarray,$choosenListInputLang,$extradiraccountname));
			//echo "relarray = ";print_r($relationarray);
			$relationoutprint = array();
			if(count($relationarray) >0)
			{
				$relationsID = $_GET['ID'];
				$o_query = $o_main->db->get_where($submodule, array('ID' => $relationsID));
				if($o_query && $relationresult = $o_query->row_array())
				{
					$counter = 0;
					$relationresult[$_GET['relationfield']] = $_GET['relationID'];
					//TODO: this loop is making problems. Rewrite!!!!
					for($x=(count($relationarray)-1);$x>=0;$x--)
					{
						$testrelationresult = NULL;
						$relationsID = $relationresult[$relationarray[$x][2]];
						if(is_file($extradiraccountname."/modules/".$relationarray[$x][0]."/addOn_InputRelationFilter/addOn_InputRelationFilter.php"))
						{
							include($extradiraccountname."/modules/".$relationarray[$x][0]."/addOn_InputRelationFilter/addOn_InputRelationFilter.php");
						} else {
							if(intval($relationresult[$relationarray[$x][2]])==0) continue;
							
							$s_sql = 'SELECT * FROM '.$relationarray[$x][6].' JOIN '.$relationarray[$x][6].'content ON '.$relationarray[$x][6].".".$relationarray[$x][6].'ID = '.$relationarray[$x][6].'.id AND languageID = ? WHERE '.$relationarray[$x][6].'.id = ? and '.$relationarray[$x][6].'.moduleID = ?';
							$o_query = $o_main->db->query($s_sql, array($s_default_output_language, $relationresult[$relationarray[$x][2]], $relationarray[$x][7]));
							if(!$o_query || ($o_query && $o_query->num_rows() == 0))
							{
								$s_sql = 'SELECT * FROM '.$relationarray[$x][6].' WHERE id = ? and moduleID = ?';
								$o_query = $o_main->db->query($s_sql, array($relationresult[$relationarray[$x][2]], $relationarray[$x][7]));
							}
							if($o_query && $o_query->num_rows() > 0)
							{
								$testrelationresult = $o_query->row_array();
							} else {
								continue;
							}
						}
						
						if(is_array($testrelationresult))
						{
							$relationresult = $testrelationresult;
							$relationoutprinttext = "<div><a class=\"optimize\" href=\"".$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$relationarray[$x][0]."&ID=".$relationsID."&includefile=edit&relationfield=".$relationarray[$x][2]."&relationID=".$relationsID."&submodule=".$relationarray[$x][6]."\">".$relationresult[$relationarray[$x][4]]."</a></div>";
							array_unshift($relationoutprint,$relationoutprinttext);
							$x=count($relationarray)-1;
						}
						$counter++;
						if($counter>10) break;
					}
					
					for($x=0;$x<count($relationoutprint);$x++)
					{
						print $relationoutprint[$x];
					}
				}
			}
		}
		$ob_module_relations = ob_get_clean();
		
		/*
		** Get module info
		*/
		ob_start();
		if($variables->developeraccess >= 10 && ($fw_showCustomized || $fw_showList) && !isset($_GET['subcontent']))
		{
			include(__DIR__."/includes/moduleinfo.php");
		}
		$ob_module_info = ob_get_clean();
		
		/*
		** Get module buttons
		*/
		ob_start();
		include(__DIR__."/includes/buttons.php");
		$ob_module_buttons = trim(ob_get_clean());
		
		/*
		** Get module content
		*/
		ob_start();
		if(!$fw_showList && isset($_GET['actionType']))
		{
			switch($_GET['actionType'])
			{
				case 'changeInputLanguage':
					$includefile="changeInputLanguage";
					break;
				case 'saveInputLanguageChanges':
					$includefile="changeInputLanguage";
					break;
				case 'changeOutputLanguage':
					$includefile="changeOutputLanguage";
					break;
				case 'saveOutputLanguageChanges':
					$includefile="changeOutputLanguage";
					break;
				case 'changeOutputSettings':
					$includefile="changeOutputSettings";
					break;
				case 'saveOutputSettings':
					$includefile="changeOutputSettings";
					break;
				default:
					print $formText_ThereIsNotDefinedAction_input.': '.$_GET['actionType'];
					break;
			}
			
			if($fw_showCustomized || $fw_showOther)
			{
				if(is_file(__DIR__."/includes/".$includefile.".php")) include(__DIR__."/includes/".$includefile.".php");
			}
			
		} else {
			
			$includeCheck = explode('/',strtolower($includefile));
			
			if(is_file(__DIR__."/../addOn_include/".$includefile.".php"))
			{
				$includepath = __DIR__."/../addOn_include/".$includefile.".php";
			} else if(in_array('list', $includeCheck))
			{
				//customized list with permission check
				$includepath = __DIR__."/include_safe/list.php";
			} else if(in_array('edit', $includeCheck))
			{
				//customized list with permission check
				$includepath = __DIR__."/include_safe/edit.php";
			} else {
				$includepath = __DIR__."/includes/".$includefile.".php";
			}
			//echo "<br /><br />includepath = $includepath<br />";
			
			if(is_file($includepath)) include($includepath);
		}
		$ob_module_content = ob_get_clean();
		
		if($moduletype == 0)
		{
			?><div class="<?php echo ($fw_column == 1 ? 'module_list':'module_content');?>"><?php
				if($fw_column == 1)
				{
					$fw_module_head = $ob_module_head;
					?><div class="moduleinfofield"><?php echo $ob_module_info;?></div>
					<ul class="buttons list-inline">
						<?php echo str_replace(array('<span class="buttonlink">','</span>'),array('<li>','</li>'),$ob_module_buttons);?>
					</ul>
					<div><?php echo $ob_module_content;?></div><?php
				} else {
					if(!isset($_GET['subcontent']))
					{
						$fw_module_head = $ob_module_head;
						?><div class="module_relations"><?php echo $ob_module_relations;?></div>
						<ul class="buttons list-inline">
							<?php echo str_replace(array('<span class="buttonlink">','</span>'),array('<li>','</li>'),$ob_module_buttons);?>
						</ul>
						<?php
					}
					?><div class="modulecontent new"><?php echo $ob_module_content;?></div><?php
				}
			?></div><?php
		} else {
			$fw_module_head = $ob_module_head;
			?><div class="module_customized">
				<div class="module_relations"><?php echo $ob_module_relations;?></div>
				<div class="moduleinfofield"><?php echo $ob_module_info;?></div>
				<ul class="buttons list-inline">
					<?php echo str_replace(array('<span class="buttonlink">','</span>'),array('<li>','</li>'),$ob_module_buttons);?>
				</ul>
				<div class="modulecontent"><?php echo $ob_module_content;?></div>
			</div>
			<?php
		}
	}
} else {
	print $formText_YouHaveNoAccessToThisModule_input;
}
?>