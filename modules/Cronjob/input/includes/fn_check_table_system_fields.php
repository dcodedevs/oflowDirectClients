<?php
function check_table_system_fields($table,$pre_path)
{
	$b_save_file = false;
	$pre_path=$pre_path."/input/settings/";
	$file = $pre_path."tables/".$table.".php";
	if(is_file($file))
	{
		include($file);
		$multilanguage = 0;
		if(sizeof($mysqlTableName)>1) $multilanguage = 1;
		
		$file = $pre_path."fields/".$table."fields.php";
		if(is_file($file))
		{
			$settingsFile = file($file);
			$useLines = "";
			foreach($settingsFile as $testLine)
			{
				$use = trim($testLine);
				if($use[0] == "$")
				{
					$useLines = $use;
				}
			}
			$splitOne = explode("array(",$useLines);
			$variablesBase = str_replace(");","",$splitOne[1]);
			
			/*
			** Check content_status field
			*/
			if(strpos($variablesBase,'"content_status¤') === false)
			{
				$b_save_file = true;
				$variablesBase .= ',"content_status¤'.$table.'content_status¤{$formText_ContentStatus_input}¤'.$table.'¤ContentStatus¤¤¤¤¤1¤0¤¤1¤1¤0¤¤¤"';
			}
			
			/*
			** Check SEO fields
			*/
			if($activateSeo=="1")
			{
				$seotitle = $seodescription = $seourl = $seokeywords = true;
				if(strpos($variablesBase,'"seotitle¤') !== false) $seotitle = false;
				if(strpos($variablesBase,'"seodescription¤') !== false) $seodescription = false;
				if(strpos($variablesBase,'"seourl¤') !== false) $seourl = false;
				if(strpos($variablesBase,'"seokeywords¤') !== false) $seokeywords = false;
				if($seotitle) $variablesBase .= ',"seotitle¤'.$table.'seotitle¤{$formText_seoTitle_input}¤'.$table.($multilanguage==1 ? 'content' : '').'¤SeoText¤¤¤¤¤1¤0¤¤1¤1¤0¤¤¤"';
				if($seodescription) $variablesBase .= ',"seodescription¤'.$table.'seodescription¤{$formText_seoDescription_input}¤'.$table.($multilanguage==1 ? 'content' : '').'¤SeoText¤¤¤¤¤1¤0¤¤1¤1¤0¤¤¤"';
				if($seourl) $variablesBase .= ',"seourl¤'.$table.'seourl¤{$formText_seoUrl_input}¤'.$table.($multilanguage==1 ? 'content' : '').'¤SeoUrl¤¤¤¤¤1¤0¤¤1¤1¤0¤¤¤"';
				if($activateSeoKeywords == "1" && $seokeywords) $variablesBase .= ',"seokeywords¤'.$table.'seokeywords¤{$formText_seoKeywords_input}¤'.$table.($multilanguage==1 ? 'content' : '').'¤SeoText¤¤¤¤¤1¤0¤¤1¤1¤0¤¤¤"';
				
				if($seotitle or $seodescription or $seourl or ($activateSeoKeywords == "1" && $seokeywords))
				{
					$b_save_file = true;
				}
			}
			
			if($b_save_file)
			{
				$path = explode('/modules/',realpath($file),2);
				ftp_file_put_content('modules/'.$path[1],"<?php\n\$prefields = array({$variablesBase});\n?>");
			}
		}
	}
}
?>