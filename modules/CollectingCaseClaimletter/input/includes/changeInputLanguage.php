<?php
ini_set('opcache.revalidate_freq', 0);
$module_absolute_path = realpath(__DIR__.'/../../');
$account_absolute_path = realpath(__DIR__.'/../../../../');
// includes  necasary functions
if(!function_exists("ftp_file_put_content")) include_once (__DIR__.'/ftp_commands.php');
// function for geting list of files by extension
include_once(__DIR__.'/fnctn_get_files.php');
// function for geting list of files by extension
include_once(__DIR__.'/fnctn_get_dirs.php');
// function for getting list of all languages form database
if(!function_exists("getInputLanguages")) include_once (__DIR__.'/fnctn_get_all_languages.php');
// function for geting current script GET params as string
include_once(__DIR__.'/fnctn_get_curent_GET_params.php');
// function for geting list of language variables used in scripts
include_once(__DIR__.'/fnctn_get_language_variables.php');
// function for geting list of variables from file
include_once(__DIR__.'/fnctn_get_form_text_variables.php');
include_once(__DIR__.'/fnctn_devide_by_upercase.php');
if(!function_exists("include_local")) include_once(__DIR__.'/fn_include_local.php');


$addonFolders = array();
$scan = scandir($module_absolute_path);
foreach($scan as $file)
{
	if(strtolower(substr($file,0,6)) == 'addon_')
		$addonFolders[] = $file;
}

// includes read language script witch gets all the values from the xmlfiles...
// this part checks for xml files and converts them into PHP files
$search_folders=array($module_absolute_path."/input/languagesInput");
foreach($addonFolders as $addonFolder) $search_folders[] = $module_absolute_path."/$addonFolder/languagesInput";
$extensions = array("xml");
$except_dirs = array();
$checkSubdirs= 1;

$xmlFiles = get_files($search_folders, $extensions,$except_dirs,$checkSubdirs);
//print_r($xmlFiles);
foreach ($xmlFiles as $xmlFile)
{
	$xmlFilePhp=str_replace(".xml", ".php", $xmlFile);
	$basenameFile = basename($xmlFile);
	$basenameFilePhp = str_replace(".xml", ".php", $basenameFile);
	
	if (file_exists($xmlFilePhp))
	{
		print ("Could not start converting file <strong> $basenameFile </strong> because already exists <strong> $basenameFilePhp </strong>. XML file deleted!<br />");
		ftp_delete_file($xmlFile);
	} else {
		print ("Converting file <strong> $basenameFile </strong> to <strong>$basenameFilePhp</strong> <br />");
		$xmlData = simplexml_load_file($xmlFile);
		$phpFileData = "<"."?php";
		foreach ($xmlData as $xmlArray)
		{
			$phpFileData =$phpFileData."
			$".$xmlArray->variableName."='".$xmlArray->variableValue."';";
		}
		$phpFileData =$phpFileData . "
		?".">";
		
		ftp_file_put_content($xmlFilePhp, $phpFileData);
		
		ftp_delete_file($xmlFile);
	}
} 


$data_of_variables=array();
$variable_ids=array();
$variable_ids_grouped=array();

// HIDE NOT USED FIELDTYPES
$hidden_dirs = array();
$found = array();
$scanDir = $module_absolute_path."/input/settings/fields/";
$scan = scandir($scanDir);
foreach($scan as $file)
{
	if($file[0] == ".") continue;
	$prefields = "";
	include($scanDir.$file);
	foreach($prefields as $field)
	{
		if($field=="") continue;
		$fieldConf = explode('¤',$field);
		if(isset($fieldConf[4]) and $fieldConf[4]!="")
		{
			$found[] = $fieldConf[4];
		}
	}
}
$scanDir = $module_absolute_path."/input/fieldtypes/";
$scan = scandir($scanDir);
foreach($scan as $file)
{
	if($file[0] == ".") continue;
	if(!in_array($file, $found))
	{
		$hidden_dirs[] = $scanDir.$file;
	}
}
// HIDE NOT USED BUTTONTYPES
$found = array();
$scanDir = $module_absolute_path."/input/settings/buttonconfig/";
$scan = scandir($scanDir);
foreach($scan as $file)
{
	if($file[0] == ".") continue;
	$prebuttonconfig = "";
	include($scanDir.$file);
	$buttons = explode('¤',$prebuttonconfig);
	foreach($buttons as $button)
	{
		if($button=="") continue;
		$buttonConf = explode(':',$button);
		if(isset($buttonConf[2]) and $buttonConf[2]!="")
		{
			$found[] = $buttonConf[2];
		}
	}
}
$scanDir = $module_absolute_path."/input/buttontypes/";
$scan = scandir($scanDir);
foreach($scan as $file)
{
	if($file[0] == ".") continue;
	if(!in_array($file, $found))
	{
		$hidden_dirs[] = $scanDir.$file;
	}
}

// gets php files for searching the language variables

// define extension of the files
$extensions=array('php');
//directory exceptions
$except_dirs=array($module_absolute_path."/input/languagesInput");
//should check subdirs
$check_subdirs=1;
$output_folders=array();
foreach($addonFolders as $addonFolder)
{
	$except_dirs[] = $module_absolute_path."/$addonFolder/languagesInput";
	$output_folders[] = $module_absolute_path."/$addonFolder";
}
$output_folders[] = $module_absolute_path."/input";

//print('output dirs<br />');print_r($output_folders);print('output dirs end <br />');

// directory for languages
$language_files = array();
// language ids
$lang_ids=array();
// current values of the langauge variables
$current_values=array();

// gets defined languages in the database
// gives back array in form array("en"=>"English", "default"=> "0 or 1");
$db_languages_titles=getInputLanguages();
if(count($db_languages_titles)>0)
{
	$translate_from = 'languagevariable';
	if(isset($_GET['selectedTranslateFrom']) && $_GET['selectedTranslateFrom']!="0" && strlen($_GET['selectedTranslateFrom'])>0)
	{
		$translate_from=$_GET['selectedTranslateFrom'];
	}
	$db_languages=array($db_languages_titles['default'][0],$translate_from);
	if(isset($_GET['selectedLanguage']) && $_GET['selectedLanguage']!="0" and strlen($_GET['selectedLanguage'])>0)
	{
		$db_languages[0]=$_GET['selectedLanguage'];
	}
	
	//gets ids for languages
	foreach($output_folders as $output_dir)
	{
		//print('<br /><br />'.$output_dir.'<br /><br />');
		array_push($except_dirs, $output_dir.'/'.'languagesInput');
		
		$language_files[$output_dir]=get_files(array($output_dir), $extensions,$except_dirs,$check_subdirs);
		if(count($language_files[$output_dir])>0)
		{
			$data_of_variables[$output_dir]=get_language_variables($language_files[$output_dir]);
			$variable_ids[$output_dir]=array_keys($data_of_variables[$output_dir]);
		}
		
		foreach($variable_ids[$output_dir] as $key)
		{
			$hide = true;
			foreach($data_of_variables[$output_dir][$key]['files'] as $file)
			{
				$found = false;
				foreach($hidden_dirs as $dir)
				{
					//print $file.'<br>'.$dir.'<br><br><br>';
					if(strpos($file,$dir)!==false) $found = true;
				}
				if(!$found)
				{
					$hide = false;
					break;
				}
			}
			if($hide)
			{
				$data_of_variables[$output_dir][$key]['hidden'] = 1;
				$variable_ids_grouped[$output_dir]['not_in_use'][] = $key;
			} else {
				$group = str_replace($module_absolute_path."/","",$data_of_variables[$output_dir][$key]['files'][0]);
				$group = explode('/',$group);
				array_pop($group);
				$variable_ids_grouped[$output_dir][implode("_",array_slice($group,0,2))][] = $key;
			}
		}
		
		//gets the info about used variables in the scripts
		foreach($db_languages as $db_language)
		{
			$language_files[$output_dir][$db_language]=$output_dir.'/'."languagesInput/$db_language.php";
			if(is_file("$output_dir/languagesInput/$db_language.php"))
			{
				$language_values = include_local($output_dir.'/'."languagesInput/$db_language.php");
				foreach($variable_ids[$output_dir] as $variable_identificator)
				{
					${$variable_identificator."_".$db_language}= (isset($language_values[$variable_identificator]) ? $language_values[$variable_identificator] : '');
				}
			}
		}
	}
	array_pop($db_languages);
	
	//print('<br><br>before saving <br><br>');
	//print_r($data_of_variables);
	//print_r($variable_ids);
	//print('<br><br>before saving <br><br>');
	//print_r($variable_ids);
	//print_r($variable_ids_grouped);
	
	
	
	// checks what is current mode
	if(isset($_GET['actionType']) and $_GET['actionType'] == 'saveInputLanguageChanges')
	{
		$saveVariables = array();
		// goes trought the languages
		foreach($output_folders as $output_dir)
		{
			$directory_id=basename($output_dir);
			$save_dir = str_replace($account_absolute_path,'',$output_dir);
			foreach($db_languages as $db_language)
			{
				$lang_file					=$save_dir."/languagesInput/$db_language.php";
				$defaultLang_file			=$save_dir."/languagesInput/default.php";
				$emptyLang_file				=$save_dir."/languagesInput/empty.php";
				$lang_file_with_sufix		=$save_dir."/languagesInput/$db_language.$db_language";
				$lang_file_bckp				=$save_dir."/languagesInput/$db_language.bckp";
				$lang_file_with_sufix_bckp	=$save_dir."/languagesInput/$db_language.$db_language.bckp";
				
				$s_local_file = $account_absolute_path.$lang_file;
				if(is_file($s_local_file)) ftp_copy($s_local_file, $lang_file_bckp);
				$s_local_file = $account_absolute_path.$lang_file_with_sufix;
				if(is_file($s_local_file)) ftp_copy($s_local_file, $lang_file_with_sufix_bckp);
				
				$emptyPhpData = $defaultPhpData = $php_data = $php_with_sufix_data ="<"."?php".PHP_EOL;
				
				foreach ($variable_ids[$output_dir] as $var_id)
				{
					//print('<br /><br />'.$var_id.'='.$_POST[$directory_id.'_'.$var_id.'_'.$db_language.''].'<br /><br />');
					if(array_key_exists($directory_id.'_'.$var_id.'_'.$db_language,$_POST))
					{
						$php_data 			.= '$'.$var_id.'="'.str_replace('"','&quot;',$_POST[$directory_id.'_'.$var_id.'_'.$db_language]).'";'.PHP_EOL;
						$php_with_sufix_data.= '$'.$var_id.'_'.$db_language.'="'.str_replace('"','&quot;',$_POST[$directory_id.'_'.$var_id.'_'.$db_language]).'";'.PHP_EOL;
						
						//if(!isset($data_of_variables[$output_dir][$var_id]['hidden']))
						$saveVariables[] = array($var_id, $db_language, str_replace('"','&quot;',$_POST[$directory_id.'_'.$var_id.'_'.$db_language]));
					} else {
						//print($db_language."--".$_POST[$directory_id.'_'.$var_id.'_'.$db_language]."<br />");
						// do not update with empty string, set back previous variable.
						$php_data 			.= '$'.$var_id.'="'.str_replace('"','&quot;',${$var_id.'_'.$db_language}).'";'.PHP_EOL;
						$php_with_sufix_data.= '$'.$var_id.'_'.$db_language.'="'.str_replace('"','&quot;',${$var_id.'_'.$db_language}).'";'.PHP_EOL;
						//if(!isset($data_of_variables[$output_dir][$var_id]['hidden']))
						$saveVariables[] = array($var_id, $db_language, str_replace('"','&quot;',${$var_id.'_'.$db_language}));
					}
					
					$varDefaultValue = explode("_",$var_id);
					$varDefaultValue=$varDefaultValue[1];
					//print($varDefaultValue."<br />");
					$emptyPhpData	.= '$'.$var_id.'="";'.PHP_EOL;
					$defaultPhpData	.= '$'.$var_id.'="'.devide_by_uppercase($varDefaultValue).'";'.PHP_EOL;
				}
				
				ftp_file_put_content($lang_file, $php_data);
				ftp_file_put_content($defaultLang_file, $defaultPhpData);
				ftp_file_put_content($emptyLang_file, $emptyPhpData);
				ftp_file_put_content($lang_file_with_sufix, $php_with_sufix_data);
			}
		}
		$data = array('data'=>json_encode(array('action'=>'set_variables','data'=>$saveVariables)));
		$url = 'https://languages.getynet.com/api.php';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		
		//after save have to reload variables....
		foreach($output_folders as $output_dir)
		{
			foreach($db_languages as $db_language)
			{
				if(is_file("$output_dir/languagesInput/$db_language.php"))
				{
					$language_values = include_local($output_dir.'/'."languagesInput/$db_language.php");
					foreach($variable_ids[$output_dir] as $variable_identificator)
					{
						${$variable_identificator."_".$db_language}= $language_values[$variable_identificator];
					}
				}
			}
		}
		header("Location: ?".get_curent_GET_params(array("actionType","autoTranslate"))."&actionType=changeInputLanguage".(isset($_GET['selectedLanguage']) ? "&selectedLanguage=".$_GET['selectedLanguage'] : ''));
		exit;
	}
	//print('<br><br>after saving <br><br>');
	//print_r($data_of_variables);
	//print_r($variable_ids);
	//print('<br><br>after saving <br><br>');
	
	
	
	//prints out form
	?>
	<form name="updateLanguages" id="updateLanguages" action="?<?php echo get_curent_GET_params(array("actionType", "selectedLanguage", "autoTranslate"));?>&actionType=saveInputLanguageChanges&selectedLanguage=<?php echo (isset($_GET['selectedLanguage']) ? $_GET['selectedLanguage'] : '');?>" method="POST">
	<input type="hidden" value="<?php echo (isset($_GET['choosenInputLang']) ? $_GET['choosenInputLang'] : '');?>" name="updateLang" />
	<input type="hidden" name="fwajax" value="1">
	
	<div style="margin:10px;">
		<input type="button" onClick="javascript: document.location.href='?<?php echo get_curent_GET_params(array("autoTranslate","actionType"));?>&actionType=changeInputLanguage&autoTranslate=1';" value="<?php echo $formText_automaticallyTranslateEmpty_input;?>" />
		<select id="filter_show_variables" onChange="loadShowVariables()">
			<option value="1"><?php echo $formText_ShowEmptyLanguageVariables_EditLanguage;?></option>
			<option value="2"><?php echo $formText_ShowAllLanguageVariables_EditLanguage;?></option>
		</select>
	</div>
	
	<table class="dataTable" width="100%">
	<tr>
		<td width="47%">
			<select id="selectedTranslateFrom" name="selectedTranslateFrom" onChange="loadFromLanguage()">
				<option value="languagevariable"><?php echo $formText_LanguageVariables_EditLanguage;?></option><?php
				$inputLanguages = getInputLanguages();
				foreach($inputLanguages as $key => $item)
				{
					if($key!='default')
					{
						?><option<?php echo ($key==$translate_from ? ' selected="selected"':'');?> value="<?php echo $key;?>"><?php echo $item['name'];?></option><?php
					}
				}
				?>
			</select>
		</td>
		<td width="6%"></td>
		<td width="47%">
			<select id="selectedLanguage" name="selectedLanguage" onChange="loadTranslateLanguage()">
				<?php
				foreach($inputLanguages as $key => $item)
				{
					if($key!='default')
					{
						?><option<?php echo ($key==$db_languages[0] ? ' selected="selected"':'');?> value="<?php echo $key;?>"><?php echo $item['name'];?></option><?php
					}
				}
				?>
			</select> 
		</td>
	</tr>
	<?php
	foreach($output_folders as $output_dir)
	{
		$directory_id=basename($output_dir);
		if(count($data_of_variables[$output_dir]))
		{
			/*?><tr><td colspan="3"><strong>Directory:<?=$directory_id;?></strong></td></tr><?php*/
		
			$variable_ids = array_keys($data_of_variables[$output_dir]);
			$groups = array_keys($variable_ids_grouped[$output_dir]);
			sort($groups);
			// goes trough variables and prints out all necasary info and functions
			$lang = $db_languages[0];
			include($output_dir.'/'."languagesInput/empty.php");                      
			include($output_dir.'/'."languagesInput/$lang.php");
			foreach($groups as $group)
			{
				$v_lang_vars = array();
				foreach($variable_ids_grouped[$output_dir][$group] as $var_id)
				{
					$v_lang_vars[] = $var_id;
				}
				$data = array('data'=>json_encode(array('action'=>'get_variables', 'data'=>array($v_lang_vars, $lang), 'version'=>'2')));
				$url = 'https://languages.getynet.com/api.php';
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$s_response = curl_exec($ch);
				curl_close($ch);
				$v_lib_variables = json_decode($s_response,true);
				
				
				?><tr><td colspan="3"><h2><?php echo str_replace("_"," ",ucfirst($group));?></h2></td></tr><?php
				foreach($variable_ids_grouped[$output_dir][$group] as $var_id)
				{
					//print_r($data_of_variables[$output_dir][$var_id]);
					$formTextContext=$data_of_variables[$output_dir][$var_id]['context'];
					$formTextDefaultValue=$data_of_variables[$output_dir][$var_id]['defaultValue'];
					$formTextName=$data_of_variables[$output_dir][$var_id]['name'];
					$formTextID=$var_id;
					?>
					<tr class="item<?php echo ('' != ${$var_id.'_'.$lang} ? ' trans-not-empty" style="display:none;' : '');?>">
						<td><span title="<?php echo $formTextID;?>"><?php echo (($translate_from=='languagevariable' or ${$var_id.'_'.$translate_from} == "") ? '<i>'.$formTextName.'</i>' : htmlspecialchars(${$var_id.'_'.$translate_from}));?></span></td>
						<?php
						$color = 0;
						$percent = '';
						$data = array();
						if(isset($v_lib_variables['items'], $v_lib_variables['items'][$var_id]))
						{
							$data = $v_lib_variables['items'][$var_id];
						}
						
						if(isset($data['data']) && count($data['data'])>0)
						{
							if(isset($_GET['autoTranslate']) and ${$var_id.'_'.$lang} == "")
							{
								${$var_id.'_'.$lang} = $data['data'][0][2];
								$color = $data['data'][0][0] + 10;
								if($data['data'][0][0] != 4 and $data['data'][0][3] < $data['total']) $percent = round(($data['data'][0][3]/$data['total'])*100);
							} else {
								foreach($data['data'] as $item)
								{
									if(${$var_id.'_'.$lang} == $item[2])
									{
										if(isset($_GET['autoTranslate']) and $item[0] == 4)
										{ //translate if match deleted item
											${$var_id.'_'.$lang} = $data['data'][0][2];
											$color = $data['data'][0][0] + 10;
											if($data['data'][0][0] != 4 and $data['data'][0][3] < $data['total']) $percent = round(($data['data'][0][3]/$data['total'])*100);
										} else {
											$color = $item[0];
											if($item[0] != 4 and $item[3] < $data['total']) $percent = round(($item[3]/$data['total'])*100);
										}
									}
								}
								if($color == 0 and count($data['data'])>0) $color = 3;
							}
						}
						if(isset($_GET['autoTranslate']) and ${$var_id.'_'.$lang} == "") $color += 10;
						?>
						<td>
							<div class="info-box info-color<?php echo $color;?>" onClick="javascript:$(this).closest('tr.item').next('tr.item-info').find('.wraper').fadeToggle();"><?php echo $percent;?></div>
						</td>
						<td>
						<?php
						if(substr_count($var_id, "formLongText")==0)
						{
							?><input class="inputFromField" onChange="dataChanged();" onKeyPress="dataChanged();" title="<?php echo $formTextContext;?>" type="text" name="<?php echo $directory_id.'_'.$formTextID.'_'.$lang;?>" value="<?php echo ${$var_id.'_'.$lang};?>" id="<?php echo $directory_id.'_'.$formTextID.'_'.$lang;?>"/><?php
						} else {
							?><textarea class="inputFromField" onChange="dataChanged();" onKeyPress="dataChanged();" title="<?php echo $formTextContext;?>" type="text" name="<?php echo $directory_id.'_'.$formTextID.'_'.$lang;?>" id="<?php echo $directory_id.'_'.$formTextID.'_'.$lang;?>"><?php echo ${$var_id.'_'.$lang};?></textarea><?php
						}
						?>
						</td>
					</tr>
					<tr class="item-info<?php echo ('' != ${$var_id.'_'.$lang} ? ' trans-not-empty' : '');?>">
						<td colspan="3">
							<div class="wraper">
								<div class="close" onClick="$(this).closest('.wraper').hide();">X</div>
								<div><b><i><?php echo $formTextID;?></i></b></div>
								<table width="100%"><?php
								if(count($data['data'])>0)
								{
									foreach($data['data'] as $item)
									{
										$color = $item[0] + 10;
										if($item[0] != 4 and $item[3] < $data['total']) $percent = round(($item[3]/$data['total'])*100); else $percent = "";
										?><tr>
											<td width="30"><div class="info-box info-color<?php echo $color;?>"><?php echo $percent;?></div></td>
											<td class="use" onClick="$('#<?php echo $directory_id.'_'.$formTextID.'_'.$lang;?>').val('<?php echo str_replace(array("'",'"'),array("\'",'&quot;'),$item[2]);?>').closest('tr.item').find('.info-box').attr('class','info-box info-color1').text(''); $(this).closest('.wraper').hide();" title="<?php echo $formText_use_input;?>"><?php echo $item[2];?></td>
											<td width="30"><a href="javascript:;" onClick="$('#<?php echo $directory_id.'_'.$formTextID.'_'.$lang;?>').val('<?php echo str_replace(array("'",'"'),array("\'",'&quot;'),$item[2]);?>').closest('tr.item').find('.info-box').attr('class','info-box info-color1').text(''); $(this).closest('.wraper').hide();"><?php echo $formText_use_input;?></a></td>
											<td width="60">
												<a<?php echo ($item[0]!=4 ? ' style="display:none;"' : '');?> class="delete1" href="javascript:;" onClick="delete_translation(this, '<?php echo $item[1];?>',0);"><?php echo $formText_undelete_input;?></a>
												<a<?php echo ($item[0]==4 ? ' style="display:none;"' : '');?> class="delete0" href="javascript:;" onClick="delete_translation(this, '<?php echo $item[1];?>',1);"><?php echo $formText_delete_sublist;?></a>
											</td>
										</tr><?php
									}
								}
								?>
								</table>
								<div class="files"><strong><?php echo $formText_files_input;?>:</strong><div><?php echo implode('<br/>',$data_of_variables[$output_dir][$var_id]['files']);?></div></div>
							</div>
						</td>
					</tr>
					<?php
				}
			}
		}
	}
	?>
	<tr>
		<td colspan="3">
			<input type="submit" value="<?php echo $formText_save_input;?>" name="summitButton"/>
		</td>
	</tr>
	</table>
	</form>
	
	<script language="javascript">
	<?php if(isset($ob_javascript)) { ob_start(); } ?>
	var JS_dataChanged=0;
	
	function dataChanged()
	{
		JS_dataChanged=1;
	}
	
	function loadFromLanguage()
	{
		if(checkChanges())
		{
			langValue = document.getElementById('selectedTranslateFrom').value;
			document.location.href='?<?php echo get_curent_GET_params(array("selectedTranslateFrom","actionType","autoTranslate"));?>&actionType=changeInputLanguage&selectedTranslateFrom='+langValue;
		}
	}
	function loadShowVariables()
	{
		if(1 == document.getElementById('filter_show_variables').value)
		{
			$('#updateLanguages .trans-not-empty').hide();
		} else {
			$('#updateLanguages .trans-not-empty').show();
		}
	}
	function loadTranslateLanguage()
	{
		if(checkChanges())
		{
			langValue = document.getElementById('selectedLanguage').value;
			document.location.href='?<?php echo get_curent_GET_params(array("selectedLanguage","actionType","autoTranslate"));?>&actionType=changeInputLanguage&selectedLanguage='+langValue;
		}
	}
	
	function checkChanges()
	{
		if(JS_dataChanged)
		{
			bootbox.confirm({
				message:"<?php echo $formText_ThereIsChangesMadeDoYouWantToSaveBeforeContinue_input;?>?",
				buttons:{confirm:{label:"<?php echo $formText_Yes_input;?>"},cancel:{label:"<?php echo $formText_No_input;?>"}},
				callback: function(result){
					if(result)
					{
						document.forms["updateLanguages"].submit();
					}
				}
			});
			return false;
		}
		return true;
	}
	function delete_translation(_this, id, delete_this)
	{
		if(delete_this)
		{
			bootbox.confirm({
				message:"<?php echo $formText_DeleteItem_input;?>?",
				buttons:{confirm:{label:"<?php echo $formText_Yes_input;?>"},cancel:{label:"<?php echo $formText_No_input;?>"}},
				callback: function(result){
					if(result)
					{
						delete_translation_ajax(_this, id, delete_this);
					}
				}
			});
		} else {
			// Un-delete
			delete_translation_ajax(_this, id, delete_this);
		}
	}
	function delete_translation_ajax(_this, id, delete_this)
	{
		fw_loading_start();
		$(_this).hide();
		$.ajax({
			type: 'POST',
			url: '<?php echo $extradir;?>/input/includes/ajax_delete_translation.php',
			cache: false,
			data: { id: id, delete_this: delete_this },
			success: function(data) {
				if(data=='OK')
				{
					$(_this).closest('td').find('a.delete'+delete_this).show();
					if(delete_this)
						$(_this).closest('tr').find('.info-box').addClass('info-deleted').removeClass('info-active');
					else
						$(_this).closest('tr').find('.info-box').removeClass('info-deleted').addClass('info-active');
				}
				fw_loading_end();
			}
		});
	}
	<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
	</script>
	
	<style>
	.info-box { background-color:#f8e1e1; min-width:14px; height:14px; display:inline-block; margin-left:5px; padding:4px; text-align:center; }
	.item .info-box { cursor:pointer; }
	.info-color1, .info-color2 { background-color:#ffffff; border:1px solid #eeeeee; }
	.info-color3 { border-color:#f8e1e1 #adf8a3 #adf8a3 #f8e1e1; border-style:solid; border-width:0 0 22px 22px; height:0; line-height:0; padding:0; width:0; min-width:0; }
	.info-color4 { background-color:#7e0b80; }
	.info-color10 { background-color:#df302c; }
	.info-color11 { background-color:#41d92c; }
	.info-color12 { background-color:#d9962c; }
	.info-color14 { background-color:#7e0b80; }
	.info-deleted { background-color:#7e0b80; color:#7e0b80; }
	.info-active { background-color:#41d92c; }
	
	.inputFromField { width:95%; }
	.item-info .wraper { margin:2px; padding:3px; background-color:#f8f8f8; border:1px solid #666666; display:none; position:relative; }
	.item-info .close { position:absolute; right:8px; cursor:pointer; font-weight:bold; }
	.item-info .wraper .use:hover { cursor:pointer; background-color:#b9edb2; }
	.item-info .files { margin:2px; padding:3px; background-color:#ffffff; border:1px solid #bbbbbb; font-size:10px; font-style:italic; }
	</style><?php
} else {
	?><h2><?php echo $formText_InputLanguageIsNotDefined_input;?></h2><?php
}