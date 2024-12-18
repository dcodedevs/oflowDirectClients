<?php
ini_set('opcache.revalidate_freq', 0);
// includes  necasary functions
if(!function_exists("ftp_file_put_content")) include_once (__DIR__.'/ftp_commands.php');
// function for geting list of files by extension
include_once(__DIR__.'/fnctn_get_files.php');
// function for geting list of files by extension
include_once(__DIR__.'/fnctn_get_dirs.php');
// function for getting list of all languages form database
if(!function_exists("getOutputLanguages")) include_once (__DIR__.'/fnctn_get_all_languages.php');
// function for geting current script GET params as string
include_once(__DIR__.'/fnctn_get_curent_GET_params.php');
// function for geting list of language variables used in scripts
if(!function_exists("get_language_variables")) include_once(__DIR__.'/fnctn_get_language_variables.php');
// function for geting list of accesselement variables used in scripts
include_once(__DIR__.'/fnctn_get_accesselement_variables.php');
// function for geting list of variables from file
include_once(__DIR__.'/fnctn_get_form_text_variables.php');
if(!function_exists("devide_by_uppercase")) include_once(__DIR__.'/fnctn_devide_by_upercase.php');
if(!function_exists("include_local")) include_once(__DIR__.'/fn_include_local.php');

$addonFolders = array();
$scan = scandir(__DIR__."/../..");
foreach($scan as $file)
{
	if(strtolower(substr($file,0,6)) == 'addon_')
		$addonFolders[] = $file;
}

$data_of_variables=array();
$data_of_accesselement_variables=array();
$variable_ids=array();
$accesselement_variable_ids=array();

//gets php files for searching the language variables
//directory  where to look for php scripts
$dirs=array(realpath(__DIR__."/../.."));
//define extension of the files
$extensions=array('php');
//directory exceptions
$except_dirs=array(realpath(__DIR__."/../../input"), realpath(__DIR__."/../../properties"));
foreach($addonFolders as $addonFolder) $except_dirs[] = realpath(__DIR__."/../../$addonFolder");
//should check subdirs
$check_subdirs=1;
//gets files

$output_folders=get_dirs($dirs, $except_dirs,0);
//print('output dirs<br />');
//print_r($output_folders);
//print('output dirs end <br />');



$language_files = array();
// language ids
$lang_ids=array();
// current values of the langauge variables
$current_values=array();

// gets defined languages in the database
// gives back array in form array("en"=>"English", "default"=> "0 or 1");
$db_languages_titles=getOutputLanguages();

if(count($db_languages_titles)>0)
{
	$translate_from = 'languagevariable';
	if(isset($_GET['selectedTranslateFrom']) && $_GET['selectedTranslateFrom']!="0" and strlen($_GET['selectedTranslateFrom'])>0)
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
		$directory_id=basename($output_dir);
		array_push($except_dirs, $output_dir.'/'.'languagesOutput');

		$language_files[$output_dir]=get_files(array($output_dir), $extensions,$except_dirs,$check_subdirs);

		if (count($language_files[$output_dir])>0)
		{
			$data_of_variables[$output_dir]=get_language_variables($language_files[$output_dir]);
			$variable_ids[$output_dir]=array_keys($data_of_variables[$output_dir]);

			$accessElementsAllow = get_accesselement_variables($output_dir, "allow");
			$accessElementsRestrict = get_accesselement_variables($output_dir, "restrict");
			$data_of_accesselement_variables[$output_dir] = array_merge($accessElementsAllow, $accessElementsRestrict);
			$accesselement_variable_ids[$output_dir]=array_keys($data_of_accesselement_variables[$output_dir]);
		}

		//gets the info about used wariables in the scripts
		foreach($db_languages as $db_language)
		{
			$language_files[$output_dir][$db_language]=$output_dir.'/'."languagesOutput/$db_language.php";
			if(is_file("$output_dir/languagesOutput/$db_language.php"))
			{
				$language_values = include_local($output_dir.'/'."languagesOutput/$db_language.php");
				foreach($variable_ids[$output_dir] as $variable_identificator)
				{
					${$directory_id.'_'.$variable_identificator.'_'.$db_language}= $language_values[$variable_identificator];
				}
			}
			if(is_file("$output_dir/languagesOutput/accesselements_$db_language.php"))
			{
				$language_values = include_local($output_dir.'/'."languagesOutput/accesselements_$db_language.php");
				foreach($accesselement_variable_ids[$output_dir] as $variable_identificator)
				{
					${$directory_id.'_'.$variable_identificator.'_name_'.$db_language} = $language_values[$variable_identificator."_name"];
					${$directory_id.'_'.$variable_identificator.'_description_'.$db_language} = $language_values[$variable_identificator."_description"];
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



	if(isset($_GET['actionType']) and $_GET['actionType'] == 'saveOutputLanguageChanges')
	{
		$saveVariables = array();
		$saveAccessElementsName = array();
		$saveAccessElementsDesc = array();
		// goes trought the languages
		foreach($output_folders as $output_dir)
		{
			$tmp = explode('/'.$accountname.'/',$output_dir);
			$output_dir_relative = $tmp[1];
			$directory_id=basename($output_dir);
			foreach($db_languages as $db_language)
			{
				$lang_file					= "/".$output_dir_relative."/languagesOutput/$db_language.php";
				$defaultLang_file			= "/".$output_dir_relative."/languagesOutput/default.php";
				$emptyLang_file				= "/".$output_dir_relative."/languagesOutput/empty.php";
				$lang_file_with_sufix		= "/".$output_dir_relative."/languagesOutput/$db_language.$db_language";
				$lang_file_bckp				= "/".$output_dir_relative."/languagesOutput/$db_language.bckp";


				$accesselement_lang_file					= "/".$output_dir_relative."/languagesOutput/accesselements_$db_language.php";
				$accesselement_defaultLang_file				= "/".$output_dir_relative."/languagesOutput/accesselements_default.php";
				$accesselement_emptyLang_file				= "/".$output_dir_relative."/languagesOutput/accesselements_empty.php";

				$s_local_file = realpath(__DIR__.'/../../../../').$lang_file;
				if(is_file($s_local_file)) ftp_copy($s_local_file, $lang_file_bckp);


				$emptyPhpData = $defaultPhpData = $php_data = $accesselement_php_data = $accesselement_defaultPhpData = $accesselement_emptyPhpData = "<"."?php".PHP_EOL;

				foreach($variable_ids[$output_dir] as $var_id)
				{
					// Handle helptext
					$s_var_help_text = '';
					if(array_key_exists($directory_id.'_'.$var_id.'_'.$db_language.'_help', $_POST))
					{
						$_POST[$directory_id.'_'.$var_id.'_'.$db_language.'_help'] = strip_tags($_POST[$directory_id.'_'.$var_id.'_'.$db_language.'_help']);
						if('' != $_POST[$directory_id.'_'.$var_id.'_'.$db_language.'_help'])
						{
							$b_right_positioned = (isset($_POST[$directory_id.'_'.$var_id.'_'.$db_language.'_pos']) && 1 == $_POST[$directory_id.'_'.$var_id.'_'.$db_language.'_pos']);
							$s_var_help_text = "<span class='fw_var_help_text'><span class='glyphicon glyphicon-info-sign'></span><span class='txt".($b_right_positioned?' right-side-position':'')."'>".$_POST[$directory_id.'_'.$var_id.'_'.$db_language.'_help']."</span></span>";
						}
					}
					//print('<br /><br />'.$var_id.'='.$_POST[$directory_id.'_'.$var_id.'_'.$db_language].'<br /><br />');
					if(array_key_exists($directory_id.'_'.$var_id.'_'.$db_language, $_POST))
					{
						$php_data .= '$'.$var_id.'="'.str_replace('"', '&quot;', $_POST[$directory_id.'_'.$var_id.'_'.$db_language].$s_var_help_text).'";'.PHP_EOL;
						$saveVariables[] = array($var_id, $db_language, str_replace('"', '&quot;', $_POST[$directory_id.'_'.$var_id.'_'.$db_language].$s_var_help_text));
					} else {
						// do not update with empty string, set back previous variable.
						$php_data .= '$'.$var_id.'="'.str_replace('"','&quot;',$data_of_variables[$output_dir][$variable_id]['value_'.$db_language]).'";'.PHP_EOL;
						$saveVariables[] = array($var_id, $db_language, str_replace('"','&quot;',${$var_id.'_'.$db_language}));
					}
					
					$varDefaultValue = explode("_",$var_id);
					$varDefaultValue=$varDefaultValue[1];
					//print($varDefaultValue."<br />");
					$emptyPhpData	.= '$'.$var_id.'="";'.PHP_EOL;
					$defaultPhpData	.= '$'.$var_id.'="'.devide_by_uppercase($varDefaultValue).'";'.PHP_EOL;
				}

				$defaultPhpData	.= "?".">";
				$php_data		.= "?".">";
				$emptyPhpData	.= "?".">";

				foreach($accesselement_variable_ids[$output_dir] as $var_id)
				{
					$base_var_id = $var_id;
					$var_id = $base_var_id."_name";
					//print('<br /><br />'.$var_id.'='.$_POST[$directory_id.'_'.$var_id.'_'.$db_language].'<br /><br />');
					if(array_key_exists($directory_id.'_'.$var_id.'_'.$db_language,$_POST))
					{
						$accesselement_php_data .= '$'.$var_id.'="'.str_replace('"','&quot;',$_POST[$directory_id.'_'.$var_id.'_'.$db_language]).'";'.PHP_EOL;
						$saveAccessElementsName[] = array($var_id, $db_language, str_replace('"','&quot;',$_POST[$directory_id.'_'.$var_id.'_'.$db_language]));
					} else {
						// do not update with empty string, set back previous variable.
						$accesselement_php_data .= '$'.$var_id.'="'.str_replace('"','&quot;',$data_of_accesselement_variables[$output_dir][$var_id]['value_'.$db_language]).'";'.PHP_EOL;
						$saveAccessElementsName[] = array($var_id, $db_language, str_replace('"','&quot;',${$var_id.'_'.$db_language}));
					}
					$var_id = $base_var_id."_description";
					if(array_key_exists($directory_id.'_'.$var_id.'_'.$db_language,$_POST))
					{
						$accesselement_php_data .= '$'.$var_id.'="'.str_replace('"','&quot;',$_POST[$directory_id.'_'.$var_id.'_'.$db_language]).'";'.PHP_EOL;
						$saveAccessElementsDesc[] = array($var_id, $db_language, str_replace('"','&quot;',$_POST[$directory_id.'_'.$var_id.'_'.$db_language]));
					} else {
						// do not update with empty string, set back previous variable.
						$accesselement_php_data .= '$'.$var_id.'="'.str_replace('"','&quot;',$data_of_accesselement_variables[$output_dir][$var_id]['value_'.$db_language]).'";'.PHP_EOL;
						$saveAccessElementsDesc[] = array($var_id, $db_language, str_replace('"','&quot;',${$var_id.'_'.$db_language}));
					}

					$varDefaultValue = explode("_",$var_id);
					$varDefaultValue=$varDefaultValue[1];
					//print($varDefaultValue."<br />");
					$accesselement_emptyPhpData	.= '$'.$var_id.'="";'.PHP_EOL;
					$accesselement_defaultPhpData	.= '$'.$var_id.'="'.devide_by_uppercase($varDefaultValue).'";'.PHP_EOL;
				}

				$accesselement_defaultPhpData	.= "?".">";
				$accesselement_php_data		.= "?".">";
				$accesselement_emptyPhpData	.= "?".">";

				ftp_file_put_content($lang_file, $php_data);
				ftp_file_put_content($defaultLang_file, $defaultPhpData);
				ftp_file_put_content($emptyLang_file, $emptyPhpData);
				
				ftp_file_put_content($accesselement_lang_file, $accesselement_php_data);
				ftp_file_put_content($accesselement_defaultLang_file, $accesselement_defaultPhpData);
				ftp_file_put_content($accesselement_emptyLang_file, $accesselement_emptyPhpData);
			}
		}
		
		$data = array('data'=>json_encode(array('action'=>'set_variables', 'type'=>'output', 'data'=>$saveVariables)));
		$url = 'http://languages.getynet.com/api.php';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		
		$data = array('data'=>json_encode(array('action'=>'set_variables', 'type'=>'access_element_name', 'data'=>$saveAccessElementsName)));
		$url = 'http://languages.getynet.com/api.php';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		
		$data = array('data'=>json_encode(array('action'=>'set_variables', 'type'=>'access_element_desc', 'data'=>$saveAccessElementsDesc)));
		$url = 'http://languages.getynet.com/api.php';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		

		foreach($variable_ids as $variable_arr)
		{
			foreach($variable_arr as $variable_identificator) unset(${$variable_identificator});
		}
		//after save have to reload variables....
		foreach($output_folders as $output_dir)
		{
			$directory_id=basename($output_dir);
			foreach($db_languages as $db_language)
			{
				if(is_file("$output_dir/languagesOutput/$db_language.php"))
				{
					$language_values = include_local($output_dir.'/'."languagesOutput/$db_language.php");
					foreach($variable_ids[$output_dir] as $variable_identificator)
					{
						${$directory_id.'_'.$variable_identificator.'_'.$db_language}= $language_values[$variable_identificator];
					}
				}

				if(is_file("$output_dir/languagesOutput/accesselements_$db_language.php"))
				{
					$language_values = include_local($output_dir.'/'."languagesOutput/accesselements_$db_language.php");
					foreach($accesselement_variable_ids[$output_dir] as $variable_identificator)
					{
						${$directory_id.'_'.$variable_identificator.'_name_'.$db_language}= $language_values[$variable_identificator];
						${$directory_id.'_'.$variable_identificator.'_description'.'_'.$db_language} = $language_values[$variable_identificator];
					}
				}
			}
		}
		header("Location: ?".get_curent_GET_params(array("actionType","autoTranslate"))."&actionType=changeOutputLanguage".(isset($_GET['selectedLanguage']) ? "&selectedLanguage=".$_GET['selectedLanguage'] : ''));
		exit;
	}
	//print('<br><br>after saving <br><br>');
	//print_r($data_of_variables);
	//print_r($variable_ids);
	///print('<br><br>after saving <br><br>');

	//prints out form

	$displayForm = false;
	foreach($output_folders as $output_dir)
	{
		if(count($data_of_variables[$output_dir])) $displayForm = true;
	}
	if($displayForm)
	{
		?>
		<form name="updateLanguages" id="updateLanguages" action="?<?php echo get_curent_GET_params(array("actionType","autoTranslate"));?>&actionType=saveOutputLanguageChanges&selectedLanguage=<?php echo (isset($_GET['selectedLanguage']) ? $_GET['selectedLanguage'] : '');?>" method="POST">
		<input type="hidden" value="<?php echo (isset($_GET['choosenInputLang']) ? $_GET['choosenInputLang'] : '');?>" name="updateLang" />
		<input type="hidden" name="fwajax" value="1">

		<div style="margin:10px;">
			<input type="button" onClick="javascript: document.location.href='?<?=get_curent_GET_params(array("autoTranslate","actionType"));?>&actionType=changeOutputLanguage&autoTranslate=1';" value="<?=$formText_automaticallyTranslateEmpty_input;?>" />
			<select id="filter_show_variables" onChange="loadShowVariables()">
				<option value="1"><?php echo $formText_ShowEmptyLanguageVariables_EditLanguage;?></option>
				<option value="2"><?php echo $formText_ShowAllLanguageVariables_EditLanguage;?></option>
			</select>
		</div>

		<table class="dataTable" width="100%">
		<tr>
			<td width="33%">
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
			<td width="4%"></td>
			<td width="30%">
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
			<td width="29%"><?php echo $formText_HelpTextAndPosition_EditLanguage;?></td>
			<td width="4%"></td>
		</tr>
		<?php
		foreach($output_folders as $output_dir)
		{
			$directory_id=basename($output_dir);
			if(count($data_of_variables[$output_dir]))
			{
				?><tr><td colspan="5"><strong>Directory:<?php echo $directory_id;?></strong></td></tr><?php

				$variable_ids = array_keys($data_of_variables[$output_dir]);
				// goes trough variables and prints out all necasary info and functions
				$lang = $db_languages[0];
				if(is_file($output_dir.'/'."languagesOutput/empty.php")) include($output_dir.'/'."languagesOutput/empty.php");
				if(is_file($output_dir.'/'."languagesOutput/$lang.php")) include($output_dir.'/'."languagesOutput/$lang.php");
				$v_lang_vars = array();
				foreach($variable_ids as $var_id)
				{
					$v_lang_vars[] = $var_id;
				}
				$data = array('data'=>json_encode(array('action'=>'get_variables', 'type'=>'output', 'data'=>array($v_lang_vars, $lang), 'version'=>'2')));
				$url = 'https://languages.getynet.com/api.php';
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$s_response = curl_exec($ch);
				curl_close($ch);
				$v_lib_variables = json_decode($s_response,true);
						
				foreach($variable_ids as $var_id)
				{
					//print_r($data_of_variables[$output_dir][$var_id]);
					$formTextContext=$data_of_variables[$output_dir][$var_id]['context'];
					$formTextDefaultValue=$data_of_variables[$output_dir][$var_id]['defaultValue'];
					$formTextName=$data_of_variables[$output_dir][$var_id]['name'];
					$formTextID=$var_id;
					if(!isset(${$directory_id.'_'.$var_id.'_'.$lang})) ${$directory_id.'_'.$var_id.'_'.$lang} = '';
					
					// Split helptext
					$s_var_help_text = '';
					$b_right_positioned = FALSE;
					if(substr_count(${$directory_id.'_'.$var_id.'_'.$lang}, "<span class='fw_var_help_text'>")>0)
					{
						$v_tmp = explode("<span class='fw_var_help_text'>", ${$directory_id.'_'.$var_id.'_'.$lang});
						${$directory_id.'_'.$var_id.'_'.$lang} = $v_tmp[0];
						$b_right_positioned = substr_count($v_tmp[1], "<span class='txt right-side-position'>")>0;
						$s_var_help_text = strip_tags(substr($v_tmp[1], 0, -7));
					}
					?>
					<tr class="item<?php echo ('' != ${$directory_id.'_'.$var_id.'_'.$lang} ? ' trans-not-empty" style="display:none;' : '');?>">
						<td><span title="<?php echo $formTextID;?>"><?php echo (($translate_from=='languagevariable' or ${$directory_id.'_'.$var_id.'_'.$translate_from} == "") ? '<i>'.$formTextName.'</i>' : htmlspecialchars(${$directory_id.'_'.$var_id.'_'.$translate_from}));?></span></td>
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
							if(isset($_GET['autoTranslate']) and ${$directory_id.'_'.$var_id.'_'.$lang} == "")
							{
								${$directory_id.'_'.$var_id.'_'.$lang} = $data['data'][0][2];
								$color = $data['data'][0][0] + 10;
								if($data['data'][0][0] != 4 and $data['data'][0][3] < $data['total']) $percent = round(($data['data'][0][3]/$data['total'])*100);
							} else {
								foreach($data['data'] as $item)
								{
									if(${$directory_id.'_'.$var_id.'_'.$lang} == $item[2])
									{
										if(isset($_GET['autoTranslate']) and $item[0] == 4)
										{ //translate if match deleted item
											${$directory_id.'_'.$var_id.'_'.$lang} = $data['data'][0][2];
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
							if(substr_count(${$directory_id.'_'.$var_id.'_'.$lang}, "<span class='fw_var_help_text'>")>0)
							{
								$v_tmp = explode("<span class='fw_var_help_text'>", ${$directory_id.'_'.$var_id.'_'.$lang});
								${$directory_id.'_'.$var_id.'_'.$lang} = $v_tmp[0];
								$b_right_positioned = substr_count($v_tmp[1], "<span class='txt right-side-position'>")>0;
								$s_var_help_text = strip_tags(substr($v_tmp[1], 0, -7));
								
							}
						}
						if(isset($_GET['autoTranslate']) and ${$directory_id.'_'.$var_id.'_'.$lang} == "") $color += 10;
						?>
						<td>
							<div class="info-box info-color<?php echo $color;?>" onClick="javascript:$(this).closest('tr.item').next('tr.item-info').find('.wraper').fadeToggle();"><?php echo $percent;?></div>
						</td>
						<td>
						<?php
						if(substr_count($var_id, "formLongText")==0)
						{
							?><input class="inputFromField" onChange="dataChanged();" onKeyPress="dataChanged();" title="<?php echo $formTextContext;?>" type="text" name="<?php echo $directory_id.'_'.$formTextID.'_'.$lang;?>" value="<?php echo ${$directory_id.'_'.$var_id.'_'.$lang};?>" id="<?php echo $directory_id.'_'.$formTextID.'_'.$lang;?>"/><?php
						} else {
							?><textarea class="inputFromField" onChange="dataChanged();" onKeyPress="dataChanged();" title="<?php echo $formTextContext;?>" type="text" name="<?php echo $directory_id.'_'.$formTextID.'_'.$lang;?>" id="<?php echo $directory_id.'_'.$formTextID.'_'.$lang;?>"><?php echo ${$directory_id.'_'.$var_id.'_'.$lang};?></textarea><?php
						}
						?>
						</td>
						<td>
							<input class="inputFromField" onChange="dataChanged();" onKeyPress="dataChanged();" type="text" name="<?php echo $directory_id.'_'.$formTextID.'_'.$lang.'_help';?>" value="<?php echo $s_var_help_text;?>" id="<?php echo $directory_id.'_'.$formTextID.'_'.$lang.'_help';?>"/>
						</td>
						<td>
							<input onChange="dataChanged();" type="checkbox" name="<?php echo $directory_id.'_'.$formTextID.'_'.$lang.'_pos';?>" value="1" id="<?php echo $directory_id.'_'.$formTextID.'_'.$lang.'_pos';?>"<?php echo ($b_right_positioned ? ' checked' : '');?>/>
						</td>
					</tr>
					<tr class="item-info<?php echo ('' != ${$directory_id.'_'.$var_id.'_'.$lang} ? ' trans-not-empty' : '');?>">
						<td colspan="5">
							<div class="wraper">
								<div class="close" onClick="$(this).closest('.wraper').hide();">X</div>
								<div><b><i><?php echo $formTextID;?></i></b></div>
								<table width="100%"><?php
								if(isset($data['data']) && count($data['data'])>0)
								{
									foreach($data['data'] as $item)
									{
										$color = $item[0] + 10;
										if($item[0] != 4 and $item[3] < $data['total']) $percent = round(($item[3]/$data['total'])*100); else $percent = "";
										$s_var_help_text = '';
										$s_var_text = $item[2];
										$b_right_positioned = FALSE;
										if(substr_count($item[2], "<span class='fw_var_help_text'>")>0)
										{
											$v_tmp = explode("<span class='fw_var_help_text'>", $item[2]);
											$s_var_text = $v_tmp[0];
											$b_right_positioned = substr_count($v_tmp[1], "<span class='txt right-side-position'>")>0;
											$s_var_help_text = strip_tags(substr($v_tmp[1], 0, -7));
										}
										?><tr>
											<td width="30"><div class="info-box info-color<?php echo $color;?>"><?php echo $percent;?></div></td>
											<td class="use" onClick="javascript: $('#<?php echo $directory_id.'_'.$formTextID.'_'.$lang.'_help';?>').val('<?php echo str_replace(array("'",'"'),array("\'",'&quot;'), $s_var_help_text);?>'); $('#<?php echo $directory_id.'_'.$formTextID.'_'.$lang;?>').val('<?php echo str_replace(array("'",'"'), array("\'",'&quot;'), $s_var_text);?>').closest('tr.item').find('.info-box').attr('class','info-box info-color1').text(''); $(this).closest('.wraper').hide();" title="<?php echo $formText_use_input;?>"><?php echo $item[2];?></td>
											<td width="30"><a href="javascript:;" onClick="$('#<?php echo $directory_id.'_'.$formTextID.'_'.$lang;?>').val('<?php echo str_replace(array("'",'"'),array("\'",'&quot;'),$item[2]);?>').closest('tr.item').find('.info-box').attr('class','info-box info-color1').text(''); $(this).closest('.wraper').hide();"><?php echo $formText_use_input;?></a></td>
											<td width="60">
												<a<?php echo ($item[0]!=4 ? ' style="display:none;"' : '');?> class="delete1" href="javascript:;" onClick="delete_translation(this, '<?php echo $item[1];?>',0, 'output');"><?php echo $formText_undelete_input;?></a>
												<a<?php echo ($item[0]==4 ? ' style="display:none;"' : '');?> class="delete0" href="javascript:;" onClick="delete_translation(this, '<?php echo $item[1];?>',1, 'output');"><?php echo $formText_delete_sublist;?></a>
											</td>
										</tr><?php
									}
								}
								?>
								</table>
								<div class="files"><strong><?php echo $formText_files_input;?>:</strong><div><?php
								$tmp_count = 0;
								foreach($data_of_variables[$output_dir][$var_id]['files'] as $file)
								{
									if($tmp_count>0) print '<br/>';
									$tmp_value = explode('/'.$accountname.'/', $file);
									print $tmp_value[1];
									$tmp_count++;
								}
								?></div></div>
							</div>

						</td>
					</tr>
					<?php
				}
			}
		}


		?>
		<tr><td colspan="5"><strong>Access elements</strong></td></tr>
		<?php
		foreach($output_folders as $output_dir)
		{
			// $accessElementsAllow = get_accesselement_variables($output_dir, "allow");
			// $accessElementsRestrict = get_accesselement_variables($output_dir, "restrict");
			$directory_id=basename($output_dir);
			if(is_file($output_dir.'/'."languagesOutput/accesselements_empty.php")) include($output_dir.'/'."languagesOutput/accesselements_empty.php");
			if(is_file($output_dir.'/'."languagesOutput/accesselements_$lang.php")) include($output_dir.'/'."languagesOutput/accesselements_$lang.php");
			foreach($accesselement_variable_ids[$output_dir] as $var_id)
			{
				$formTextContext=$data_of_accesselement_variables[$output_dir][$var_id]['context'];
				$formTextDefaultValue=$data_of_accesselement_variables[$output_dir][$var_id]['defaultValue'];
				$formTextName=$data_of_accesselement_variables[$output_dir][$var_id]['name'];
				$formTextID=$var_id;
				if(!isset(${$directory_id.'_'.$var_id.'_'.$lang})) ${$directory_id.'_'.$var_id.'_'.$lang} = '';
				?>
				<tr class="item">
					<td valign="top">
						<span title="<?php echo $formTextID;?>"><i><?php echo $formTextName?></i></span>
					</td>
					<?php
					$color = 0;
					$percent = '';
					$data = array('data'=>json_encode(array('action'=>'get_variables', 'type'=>'access_element_name', 'data'=>array($var_id.'_name',$lang))));
					$url = 'https://languages.getynet.com/api.php';
					$ch = curl_init($url);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$response = curl_exec($ch);
					curl_close($ch);
					$data = json_decode($response,true);

					if(isset($data['data']) && count($data['data'])>0)
					{
						if(isset($_GET['autoTranslate']) and ${$directory_id.'_'.$var_id.'_name_'.$lang} == "")
						{
							${$directory_id.'_'.$var_id.'_name_'.$lang} = $data['data'][0][2];
							$color = $data['data'][0][0] + 10;
							if($data['data'][0][0] != 4 and $data['data'][0][3] < $data['total']) $percent = round(($data['data'][0][3]/$data['total'])*100);
						} else {
							foreach($data['data'] as $item)
							{
								if(${$directory_id.'_'.$var_id.'_name_'.$lang} == $item[2])
								{
									if(isset($_GET['autoTranslate']) and $item[0] == 4)
									{ //translate if match deleted item
										${$directory_id.'_'.$var_id.'_name_'.$lang} = $data['data'][0][2];
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
					if(isset($_GET['autoTranslate']) and ${$directory_id.'_'.$var_id.'_name_'.$lang} == "") $color += 10;
					?>
					<td valign="top">
						<div class="info-box info-color<?php echo $color;?>" onClick="javascript:$(this).closest('tr.item').next('tr.item-info').find('.wraper').fadeToggle();"><?php echo $percent;?></div>
					</td>
					<td colspan="3" valign="top">
						<input class="inputFromField" onchange="dataChanged();" onkeypress="dataChanged();" title="" type="text" name="<?php echo $directory_id.'_'.$var_id.'_name_'.$lang;?>" value="<?php echo ${$directory_id.'_'.$var_id.'_name_'.$lang};?>" id="<?php echo $directory_id.'_'.$var_id.'_name_'.$lang;?>">
					</td>
				</tr>
				<tr class="item-info">
					<td colspan="5">
						<div class="wraper">
							<div class="close" onClick="$(this).closest('.wraper').hide();">X</div>
							<div><b><i><?php echo $formTextID;?></i></b></div>
							<table width="100%"><?php
							if(isset($data['data']) && count($data['data'])>0)
							{
								foreach($data['data'] as $item)
								{
									$color = $item[0] + 10;
									if($item[0] != 4 and $item[3] < $data['total']) $percent = round(($item[3]/$data['total'])*100); else $percent = "";
									?><tr>
										<td width="30"><div class="info-box info-color<?php echo $color;?>"><?php echo $percent;?></div></td>
										<td class="use" onClick="$('#<?php echo $directory_id.'_'.$formTextID.'_name_'.$lang;?>').val('<?php echo str_replace(array("'",'"'),array("\'",'&quot;'),$item[2]);?>').closest('tr.item').find('.info-box').attr('class','info-box info-color1').text(''); $(this).closest('.wraper').hide();" title="<?php echo $formText_use_input;?>"><?php echo $item[2];?></td>
										<td width="30"><a href="javascript:;" onClick="$('#<?php echo $directory_id.'_'.$formTextID.'_name_'.$lang;?>').val('<?php echo str_replace(array("'",'"'),array("\'",'&quot;'),$item[2]);?>').closest('tr.item').find('.info-box').attr('class','info-box info-color1').text(''); $(this).closest('.wraper').hide();"><?php echo $formText_use_input;?></a></td>
										<td width="60">
											<a<?php echo ($item[0]!=4 ? ' style="display:none;"' : '');?> class="delete1" href="javascript:;" onClick="delete_translation(this, '<?php echo $item[1];?>',0, 'access_element');"><?php echo $formText_undelete_input;?></a>
											<a<?php echo ($item[0]==4 ? ' style="display:none;"' : '');?> class="delete0" href="javascript:;" onClick="delete_translation(this, '<?php echo $item[1];?>',1, 'access_element');"><?php echo $formText_delete_sublist;?></a>
										</td>
									</tr><?php
								}
							}
							?>
							</table>
							<div class="files"><strong><?php echo $formText_files_input;?>:</strong><div><?php
							$tmp_count = 0;
							foreach($data_of_variables[$output_dir][$var_id]['files'] as $file)
							{
								if($tmp_count>0) print '<br/>';
								$tmp_value = explode('/'.$accountname.'/', $file);
								print $tmp_value[1];
								$tmp_count++;
							}
							?></div></div>
						</div>
					</td>
				</tr>
				<tr class="item">
					<td valign="top">
						<span title="<?php echo $formTextID;?>"><i><?php echo $formTextName?></i></span>
					</td>
					<?php
					$color = 0;
					$percent = '';
					$data = array('data'=>json_encode(array('action'=>'get_variables', 'type'=>'access_element_desc', 'data'=>array($var_id.'_description',$lang))));
					$url = 'https://languages.getynet.com/api.php';
					$ch = curl_init($url);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					$response = curl_exec($ch);
					curl_close($ch);
					$data = json_decode($response,true);

					if(isset($data['data']) && count($data['data'])>0)
					{
						if(isset($_GET['autoTranslate']) and ${$directory_id.'_'.$var_id.'_description_'.$lang} == "")
						{
							${$directory_id.'_'.$var_id.'_description_'.$lang} = $data['data'][0][2];
							$color = $data['data'][0][0] + 10;
							if($data['data'][0][0] != 4 and $data['data'][0][3] < $data['total']) $percent = round(($data['data'][0][3]/$data['total'])*100);
						} else {
							foreach($data['data'] as $item)
							{
								if(${$directory_id.'_'.$var_id.'_description_'.$lang} == $item[2])
								{
									if(isset($_GET['autoTranslate']) and $item[0] == 4)
									{ //translate if match deleted item
										${$directory_id.'_'.$var_id.'_description_'.$lang} = $data['data'][0][2];
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
					if(isset($_GET['autoTranslate']) and ${$directory_id.'_'.$var_id.'_description_'.$lang} == "") $color += 10;
					?>
					<td valign="top">
						<div class="info-box info-color<?php echo $color;?>" onClick="javascript:$(this).closest('tr.item').next('tr.item-info').find('.wraper').fadeToggle();"><?php echo $percent;?></div>
					</td>
					<td valign="top" colspan="3">
						<textarea class="inputFromField" name="<?php echo $directory_id.'_'.$var_id.'_description_'.$lang;?>" id="<?php echo $directory_id.'_'.$var_id.'_description_'.$lang;?>"><?php echo ${$directory_id.'_'.$var_id.'_description_'.$lang.""};?></textarea>
					</td>
				</tr>
				<tr class="item-info">
					<td colspan="5">
						<div class="wraper">
							<div class="close" onClick="$(this).closest('.wraper').hide();">X</div>
							<div><b><i><?php echo $formTextID;?></i></b></div>
							<table width="100%"><?php
							if(isset($data['data']) && count($data['data'])>0)
							{
								foreach($data['data'] as $item)
								{
									$color = $item[0] + 10;
									if($item[0] != 4 and $item[3] < $data['total']) $percent = round(($item[3]/$data['total'])*100); else $percent = "";
									?><tr>
										<td width="30"><div class="info-box info-color<?php echo $color;?>"><?php echo $percent;?></div></td>
										<td class="use" onClick="$('#<?php echo $directory_id.'_'.$formTextID.'_description_'.$lang;?>').val('<?php echo str_replace(array("'",'"'),array("\'",'&quot;'),$item[2]);?>').closest('tr.item').find('.info-box').attr('class','info-box info-color1').text(''); $(this).closest('.wraper').hide();" title="<?php echo $formText_use_input;?>"><?php echo $item[2];?></td>
										<td width="30"><a href="javascript:;" onClick="$('#<?php echo $directory_id.'_'.$formTextID.'_description_'.$lang;?>').val('<?php echo str_replace(array("'",'"'),array("\'",'&quot;'),$item[2]);?>').closest('tr.item').find('.info-box').attr('class','info-box info-color1').text(''); $(this).closest('.wraper').hide();"><?php echo $formText_use_input;?></a></td>
										<td width="60">
											<a<?php echo ($item[0]!=4 ? ' style="display:none;"' : '');?> class="delete1" href="javascript:;" onClick="delete_translation(this, '<?php echo $item[1];?>',0, 'access_element');"><?php echo $formText_undelete_input;?></a>
											<a<?php echo ($item[0]==4 ? ' style="display:none;"' : '');?> class="delete0" href="javascript:;" onClick="delete_translation(this, '<?php echo $item[1];?>',1, 'access_element');"><?php echo $formText_delete_sublist;?></a>
										</td>
									</tr><?php
								}
							}
							?>
							</table>
							<div class="files"><strong><?php echo $formText_files_input;?>:</strong><div><?php
							$tmp_count = 0;
							foreach($data_of_variables[$output_dir][$var_id]['files'] as $file)
							{
								if($tmp_count>0) print '<br/>';
								$tmp_value = explode('/'.$accountname.'/', $file);
								print $tmp_value[1];
								$tmp_count++;
							}
							?></div></div>
						</div>
					</td>
				</tr>
				<?php
			}
		}
		?>
		<tr>
			<td colspan="5">
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
				document.location.href='?<?php echo get_curent_GET_params(array("selectedTranslateFrom","actionType","autoTranslate"));?>&actionType=changeOutputLanguage&selectedTranslateFrom='+langValue;
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
				document.location.href='?<?php echo get_curent_GET_params(array("selectedLanguage","actionType","autoTranslate"));?>&actionType=changeOutputLanguage&selectedLanguage='+langValue;
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
		function delete_translation(_this, id, delete_this, _type)
		{
			if(delete_this)
			{
				bootbox.confirm({
					message:"<?=$formText_DeleteItem_input;?>?",
					buttons:{confirm:{label:"<?=$formText_Yes_input;?>"},cancel:{label:"<?=$formText_No_input;?>"}},
					callback: function(result){
						if(result)
						{
							delete_translation_ajax(_this, id, delete_this);
						}
					}
				});
			} else {
				// Un-delete
				delete_translation_ajax(_this, id, delete_this, _type);
			}
		}
		function delete_translation_ajax(_this, id, delete_this, _type)
		{
			fw_loading_start();
			$(_this).hide();
			$.ajax({
				type: 'POST',
				url: '<?=$extradir;?>/input/includes/ajax_delete_translation.php',
				cache: false,
				data: { id: id, delete_this: delete_this, type: _type },
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
		.modulecontent { width:98% !important; }
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
		</style>
		<?php
	} else {
		?><h2><?php echo $formText_LanguageVariablesAreNotDefinedInOutput_input;?></h2><?php
	}
} else {
	?><h2><?php echo $formText_OutputLanguageIsNotDefined_input;?></h2><?php
}
