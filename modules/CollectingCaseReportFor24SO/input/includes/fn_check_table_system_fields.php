<?php
function check_table_system_fields($table, $pre_path, $module = '')
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
			if(isset($activateSeo) && "1" == $activateSeo)
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
			
			/*
			** Check OpenGraph fields
			*/
			if(isset($activateOpenGraph) && "1" == $activateOpenGraph)
			{
				$b_og_title = $b_og_description = $b_og_image = TRUE;
				if(strpos($variablesBase,'"open_graph_title¤') !== FALSE) $b_og_title = FALSE;
				if(strpos($variablesBase,'"open_graph_description¤') !== FALSE) $b_og_description = FALSE;
				if(strpos($variablesBase,'"open_graph_image¤') !== FALSE) $b_og_image = FALSE;
				if($b_og_title) $variablesBase .= ',"open_graph_title¤'.$table.'open_graph_title¤{$formText_OpenGraphTitle_input}¤'.$table.($multilanguage==1 ? 'content' : '').'¤Textfield¤¤¤¤¤0¤0¤¤1¤1¤0¤0¤¤"';
				if($b_og_description) $variablesBase .= ',"open_graph_description¤'.$table.'open_graph_description¤{$formText_OpenGraphDescription_input}¤'.$table.($multilanguage==1 ? 'content' : '').'¤Textfield¤¤¤¤¤0¤0¤¤1¤1¤0¤0¤¤"';
				if($b_og_image) $variablesBase .= ',"open_graph_image¤'.$table.'open_graph_image¤{$formText_OpenGraphImage_input}¤'.$table.'¤Image¤¤¤¤¤0¤0¤¤1¤1¤0¤0¤¤"';
				
				if($b_og_title or $b_og_description or $b_og_image)
				{
					$b_save_file = true;
				}
				
				$preblocks = array();
				$v_open_graph_block = array
				(
					'sys_name' => '$formText_OpenGraph_settingBlocks',
					'sys_childs' => array
					(
						'open_graph_title' => 1,
						'open_graph_description' => 1,
						'open_graph_image' => 1,
					),
					'sys_collapse' => '1'
				);
				$b_insert_og_block = TRUE;
				$s_blocks_file = $pre_path."blocks/".$table.".php";
				$s_blocks_file_ftp = 'modules/'.$module.'/input/settings/blocks/'.$table.'.php';
				if(is_file($s_blocks_file))
				{
					$s_file_content = file_get_contents($s_blocks_file);
					if(strpos($s_file_content,'open_graph_title') !== FALSE) $b_insert_og_block = FALSE;
					if($b_insert_og_block)
					{
						$b_found = FALSE;
						for($i=0;$i<strlen($s_file_content);$i++)
						{
							if($s_file_content[$i] == '$' && substr($s_file_content,$i,5)=='$form')
							{
								$b_found = TRUE;
								$s_file_content = substr_replace($s_file_content, "'", $i, 0);
								$i++;
							}
							if($b_found && $s_file_content[$i] == ",")
							{
								$b_found = FALSE;
								$s_file_content = substr_replace($s_file_content, "'", $i, 0);
								$i++;
							}
						}
						$path = explode('/modules/', realpath($s_blocks_file), 2);
						ftp_file_put_content('modules/'.$path[1], $s_file_content);
						include($s_blocks_file);
					}
				}
				if($b_insert_og_block)
				{
					$preblocks[] = $v_open_graph_block;
					$b_found = FALSE;
					$s_file_content = var_export($preblocks, TRUE);
					for($i=0;$i<strlen($s_file_content);$i++)
					{
						if($s_file_content[$i] == '$' && substr($s_file_content,$i,5)=='$form')
						{
							$b_found = TRUE;
							$s_file_content = substr_replace($s_file_content, '', $i-1, 1);
							$i--;
						}
						if($b_found && $s_file_content[$i] == "'")
						{
							$b_found = FALSE;
							$s_file_content = substr_replace($s_file_content, '', $i, 1);
							$i--;
						}
					}
					ftp_file_put_content($s_blocks_file_ftp, "<?php\n\$preblocks = ".$s_file_content.";\n?>");
				}
			}
			
			if($b_save_file)
			{
				$path = explode('/modules/',realpath($file),2);
				ftp_file_put_content('modules/'.$path[1],"<?php\n\$prefields = array(".$variablesBase.");\n?>");
			}
		}
	}
}
?>