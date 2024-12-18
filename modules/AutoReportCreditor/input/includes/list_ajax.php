<?php
session_start();
define('BASEPATH', realpath(__DIR__.'/../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
$output = array();
$output['html'] = '<div class="input_list_item">No data</div>';

$module = $o_main->db_escape_name($_POST['module']);
$submodule = $o_main->db_escape_name($_POST['submodule']);

$s_sql = "SELECT * FROM session_framework WHERE companyaccessID = '".$o_main->db->escape_str($_GET['caID'])."' AND session = '".$o_main->db->escape_str($_COOKIE['sessionID'])."' AND username = '".$o_main->db->escape_str($_COOKIE['username'])."'".($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"")." LIMIT 1";
$o_query = $o_main->db->query($s_sql);
if(!$o_query || ($o_query && $o_query->num_rows() == 0) || empty($_GET['caID']))
{
	$output['html'] = 'session_error';
	echo json_encode($output);
	return;
}
$fw_session = $o_query->row_array();
$menuaccess = json_decode($fw_session['cache_menu'],true);
$access = $menuaccess[$module][2];

$s_default_input_language = '';
$o_query = $o_main->db->query('SELECT languageID FROM language ORDER BY defaultInputlanguage DESC, inputlanguage DESC, sortnr ASC');
if($o_query && $o_row = $o_query->row()) $s_default_input_language = $o_row->languageID;

$s_default_output_language = '';
$o_query = $o_main->db->query('SELECT languageID FROM language ORDER BY defaultOutputlanguage DESC, outputlanguage DESC, sortnr ASC');
if($o_query && $o_row = $o_query->row()) $s_default_output_language = $o_row->languageID;

$moduleID = '';
$o_query = $o_main->db->query('SELECT uniqueID FROM moduledata WHERE name = ?', array($module));
if($o_query && $o_row = $o_query->row()) $moduleID = $o_row->uniqueID;

$includefile = 'list';
if(isset($_GET['includefile'])) $includefile = $_GET['includefile'];

// ALI - check does user have appropriate permissions
$permissionsRead = array('list', 'sublist', 'edit');
$permissionsWrite = array('orderContent', 'sendEmail', 'sendSms', 'sendPdf', 'cropimage', 'editContentSettings');
if($access >= 100 /*all permissions*/ or ($access > 0 and in_array($includefile, $permissionsRead)) or ($access >=10 and in_array($includefile, $permissionsWrite)) )
{
	if(isset($_POST['soker']) && $_POST['soker'] != "" && $access > 0)
	{
		$start = 0;
		$startList = 0;
		$searchword = "";
		$searchaddon = "";
		$orgsearch = "";
		$sortefield = "";
		$e_search_method = $_POST['search_method'];
		$searchFieldName = $_POST['searchFieldName'];
		$searchType = $_POST['searchType'];
		$soker = json_decode($_POST['soker']);
		$soker->mainTable = $o_main->db_escape_name($soker->mainTable);
		$orderByField = $_POST['orderByField'];
		$orderByDesc = $_POST['orderByDesc'];
		$l_list_items_per_page = intval($_POST['perPage']);
		$choosenListInputLang = $_POST['choosenListInputLang'];
		$listFieldVariables = json_decode($_POST['listFieldVariables']);
		$listSet2FieldVariables = json_decode($_POST['listSet2FieldVariables']);
		$listSet3FieldVariables = json_decode($_POST['listSet3FieldVariables']);
		$listSet4FieldVariables = json_decode($_POST['listSet4FieldVariables']);
		$accountPath = realpath(__DIR__."/../../../../");
		include(__DIR__."/moduleinit.php");
		$_SERVER['PHP_SELF'] = $_POST['selfurl'];
		$extradomaindirroot = $_POST['extradomaindirroot'];
		if(!function_exists("include_local") && is_file(__DIR__."/fn_include_local.php")) include(__DIR__."/fn_include_local.php");
		//reads language variables
		include(__DIR__."/readInputLanguage.php");
		$headmodule = "";
		$submods = array();
		if($findBase = opendir(__DIR__."/../settings/tables"))
		{
			while($writeBase = readdir($findBase))
			{
				if($writeBase == '.' || $writeBase == '..') continue;
				$fieldParts = explode(".",$writeBase);
				if($fieldParts[2] != "LCK" && $fieldParts[1] == "php" && $fieldParts[0] != "")
				{
					$submods[] = $fieldParts[0];
					$vars = include_local(__DIR__."/../settings/tables/".$fieldParts[0].".php");	
					
					if($vars['tableordernr'] == "1")
					{
						$headmodule = $fieldParts[0];
					}
				}
			}
			if($headmodule == "")
			{
				$headmodule = $submods[0];
			}
			if(is_file(__DIR__."/../settings/tables/".$headmodule.".php")) include(__DIR__."/../settings/tables/".$headmodule.".php");
			closedir($findBase);
		}
		
		$module_multi_table = false;
		if($o_main->db->table_exists($soker->mainTable.'content')) $module_multi_table = true;
		if(!$o_main->db->table_exists($soker->mainTable)) return;
		
		$linkers = array();
		foreach($listFieldVariables as $var) if(isset(${$var}) && !empty(${$var})) $linkers[0][] = ${$var};
		foreach($listSet2FieldVariables as $var) if(isset(${$var}) && !empty(${$var})) $linkers[1][] = ${$var};
		foreach($listSet3FieldVariables as $var) if(isset(${$var}) && !empty(${$var})) $linkers[2][] = ${$var};
		foreach($listSet4FieldVariables as $var) if(isset(${$var}) && !empty(${$var})) $linkers[3][] = ${$var};
		$data_columns = count($linkers[0]);
		
		if($_POST['saveFilter'])
		{
			$_SESSION['filter1'] = intval($_POST['filter1']);
			$_SESSION['filter2'] = ($_SESSION['filter1'] > 0 ? intval($_POST['filter2']) : 0);
			$_SESSION['filter3'] = ($_SESSION['filter2'] > 0 ? intval($_POST['filter3']) : 0);
            $_SESSION['filter4'] = ($_SESSION['filter3'] > 0 ? intval($_POST['filter4']) : 0);
		}

		if(isset($_GET['sortfield']) && $_GET['sortfield'] != '')
		{
			$orderByField = $o_main->db_escape_name($_GET['sortfield']);
		}
		if(isset($_GET['descUse']) && $_GET['descUse'] != "")
		{
			if(strtoupper($_GET['descUse']) == 'DESC') $orderByDesc = 'DESC';
			else $orderByDesc = 'ASC';
		}
		$otherdesc = "DESC";
		if($orderByDesc == "DESC") $otherdesc = "ASC";
		if($orderByField == "") $orderByField = $contentorderfield;
		$s_sql_order = '';
		if($orderByField != "") $s_sql_order = ' ORDER BY '.$o_main->db_escape_name($orderByField).' '.$orderByDesc;
		if(isset($_GET['start']) && is_numeric($_GET['start']))
		{
			$start = intval($_GET['start']);
			$startList = $start * $l_list_items_per_page;
			$_SESSION['caID_'.$_GET['caID']]['module_page'] = $start;
		} else {
			unset($_SESSION['caID_'.$_GET['caID']]['module_page']);
		}
		if(isset($_POST['search']) && $_POST['search'] != "")
		{
			$v_exception_columns = array('createdBy', 'updatedBy', 'sortnr', 'origId', 'content_status', 'account_id');
			$searchword = $_POST['search'];
			
			if($e_search_method == 0 && $searchFieldName != "")
			{
				$soker->searchConditions[] = array($searchword, "LIKE", $soker->mainTable, $searchFieldName);
			} else if($e_search_method == 1)
			{
				foreach($linkers[0] as $s_field)
				{
					if(isset($fieldsStructure[$s_field])) $soker->searchConditions[] = array($searchword, "LIKE", $soker->mainTable, $fieldsStructure[$s_field][0]);
				}
			} else {
				foreach($fields as $v_field)
				{
					if(!in_array($v_field[0], $v_exception_columns))
					{
						$soker->searchConditions[] = array($searchword, "LIKE", $soker->mainTable, $v_field[0]);
					}
				}
			}
			$_SESSION[$_GET['caID'].$_GET['accountname'].$submodule.'_search'] = $searchword;
		} else {
			unset($_SESSION[$_GET['caID'].$_GET['accountname'].$submodule.'_search']);
		}
		if(isset($_GET['relationfield']) && $_GET['relationfield'] != "")
		{
			$soker->conditions[] = array($_GET['relationID'], "=", $soker->mainTable, $_GET['relationfield']);
		}	
		if($settingsChoice_maxLevel_inputMenuLevels > 0)
		{
			$soker->conditions[] = array(0,"=",$soker->mainTable,"level");
		}
		
		if(sizeof($soker->conditions) > 0 || sizeof($soker->searchConditions) > 0)
		{
			$sqlList .= " WHERE";
			if(sizeof($soker->searchConditions) > 0)
			{
				$sqlList .= " (";
				foreach($soker->searchConditions as $key => $condition)
				{
					if(!in_array(strtolower($condition[1]), array('=', '<', '>', '<=', '>=', '<>', '!=', 'is', 'in', 'like'))) continue;
					$condition[0] = $o_main->db->escape_like_str($condition[0]);
					$condition[2] = $o_main->db_escape_name($condition[2]);
					$condition[3] = $o_main->db_escape_name($condition[3]);
					if($key > 0) $sqlList .= " OR";
					if($module_multi_table && $o_main->db->field_exists($condition[3], $condition[2]."content"))
					{
						$sqlList .= " ".$condition[2]."content.".$condition[3]." ".$condition[1];
					} else {
						$sqlList .= " ".$condition[2].".".$condition[3]." ".$condition[1];
					}
					if($searchType == 0){
						$sqlList.=" '".$condition[0]."%' ESCAPE '!'";
					}else {
						$sqlList.=" '%".$condition[0]."%' ESCAPE '!'";
					}
				}
				$sqlList .= ")";
			}
			
			if(sizeof($soker->conditions) > 0)
			{
				if(sizeof($soker->searchConditions) > 0) $sqlList .= " AND";
				foreach($soker->conditions as $key => $condition)
				{
					if(!in_array(strtolower($condition[1]), array('=', '<', '>', '<=', '>=', '<>', '!=', 'is', 'in', 'like'))) continue;
					$condition[0] = $o_main->db->escape($condition[0]);
					$condition[2] = $o_main->db_escape_name($condition[2]);
					$condition[3] = $o_main->db_escape_name($condition[3]);
					if($key > 0) $sqlList .= " AND";
					if($module_multi_table && $o_main->db->field_exists($condition[3], $condition[2]."content"))
					{
						$sqlList .= " ".$condition[2]."content.".$condition[3]." ".$condition[1]." ".$condition[0]."";
					} else {
						$sqlList .= " ".$condition[2].".".$condition[3]." ".$condition[1]." ".$condition[0]."";
					}
					
				}				 
			}
		}

		if($_SESSION['filter1'] > 0 || $_SESSION['filter2'] > 0 || $_SESSION['filter3'] > 0 || $_SESSION['filter4'] > 0)
		{
			$l_filter = $_SESSION['filter1'];
			if($_SESSION['filter2'] > 0)
			{
				$l_filter = $_SESSION['filter2'];
			}
			if($_SESSION['filter3'] > 0)
			{
				$l_filter = $_SESSION['filter3'];
			}
            if($_SESSION['filter4'] > 0)
			{
				$l_filter = $_SESSION['filter4'];
			}
			if($sqlList == '') $sqlList = ' WHERE '; else $sqlList .= ' AND ';
			$sqlList .= " EXISTS(SELECT pageID.id FROM pageID WHERE pageID.contentID = ".$soker->mainTable.".id AND pageID.contentTable = '".$soker->mainTable."' AND pageID.menulevelID = ".$o_main->db->escape($l_filter)." AND pageID.deleted <> 1)";
		}
		
		if($_SESSION['filter1'] == -1)
		{
			if($sqlList == '') $sqlList = ' WHERE '; else $sqlList .= ' AND ';
			$sqlList .= " NOT EXISTS(SELECT pageID.id FROM pageID WHERE pageID.contentID = ".$soker->mainTable.".id AND pageID.contentTable = '".$soker->mainTable."' AND pageID.contentID = ".$soker->mainTable.".id)";
		}
		
		if($module_multi_table)
		{
			$sqlSelect = "";
			$sqlExcludeColumns = array('id','moduleID','created','createdBy','updated','updatedBy','sortnr','origId','content_status');
			$v_table_fields = $o_main->db->list_fields($soker->mainTable.'content');
			foreach($v_table_fields as $s_field)
			{
				if(!in_array($s_field, $sqlExcludeColumns)) $sqlSelect .= ', '.$soker->mainTable.'content.'.$s_field;
			}
			
			$listOut = "SELECT ".$soker->mainTable.".*, ".$soker->mainTable.".id as sideID ".$sqlSelect." FROM ".$soker->mainTable." LEFT OUTER JOIN ".$soker->mainTable."content ON ".$soker->mainTable."content.".$soker->mainTable."ID = ".$soker->mainTable.".id AND ".$soker->mainTable."content.languageID = ".$o_main->db->escape($s_default_output_language)." ".$s_sql_filter_join." ".$sqlList.$s_sql_order." LIMIT $startList, $l_list_items_per_page";
			
			$countOut = "SELECT COUNT(".$soker->mainTable.".id) cnt FROM ".$soker->mainTable." LEFT OUTER JOIN ".$soker->mainTable."content ON ".$soker->mainTable."content.".$soker->mainTable."ID = ".$soker->mainTable.".id AND ".$soker->mainTable."content.languageID = ".$o_main->db->escape($s_default_output_language)." ".$sqlList;
		} else {
			$listOut = "SELECT ".$soker->mainTable.".*, ".$soker->mainTable.".id as sideID FROM ".$soker->mainTable.$sqlList.$s_sql_order." LIMIT $startList, $l_list_items_per_page;";
			
			$countOut = "SELECT COUNT(".$soker->mainTable.".id) cnt FROM ".$soker->mainTable.$sqlList;
		}
		
		$number = 0;
		$o_query = $o_main->db->query($countOut);
		if($o_query && $o_row = $o_query->row()) $number = $o_row->cnt;			
		$pageNum = ceil($number / $l_list_items_per_page);		
		$o_get_content = $o_main->db->query($listOut);
		
		$linkStandard = $_POST['selfurl']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&start=".$start; 
		if(isset($_GET['relationID']) && $_GET['relationID'] != "") $linkStandard .= "&relationID=".$_GET['relationID'];
		if(isset($_GET['relationfield']) && $_GET['relationfield'] != "") $linkStandard .= "&relationfield=".$_GET['relationfield'];
		if(isset($_GET["content_status"])) $linkStandard .= "&content_status=".$_GET['content_status'];

		$counter = 0;
		$linkersField = array();
		foreach($linkers as $key => $outlink)
		{
			foreach($outlink as $key=>$outlink){
				$linkUse = $linkStandard;
				if($sortefield == $outlink)
				{
					$linkUse .= "&amp;descUse=".$otherdesc; 
				}
				foreach($fields as $outfield)
				{
					if($outfield[0] == $outlink)
					{
						$linkersField[$key] = $outfield;
						if($outfield[4] == "Dropdown" or $outfield[4] == "GroupDropdownField")
						{
							$varName = $outfield[0]."ListArray";
							${$varName} = array();
							${$varName}["type"] = "dropdown";
							$tmp = explode("::",$outfield[11]);
							foreach($tmp as $tmpv)
							{
								list($lKey,$lValue) = explode(":",$tmpv);
								${$varName}[$lKey] = $lValue;
							}
						}
						elseif($outfield[4] == "RadioButtonAdv")
						{
							$varName = $outfield[0]."ListArray";
							${$varName} = array();
							${$varName}["type"] = "dropdown";
							$tmp = explode("::",$outfield[11]);
							foreach($tmp as $tmpv)
							{
								list($lKey,$lValue) = explode(":",$tmpv);
								${$varName}[$lKey] = $lValue;
							}
						}
						elseif($outfield[4] == "DropdownTable")
						{
							$varName = $outfield[0]."ListArray";
							${$varName} = array();
							${$varName}["type"] = "dropdown";
							$expl = explode(":",$outfield[11]);
							$dataTable = $expl[0];
							$dataID = $expl[1];
							$dataNames = explode(",",$expl[2]);
							$dataFilter = $expl[3];
							
							//TODO: ALI - security_check - filter
							$s_sql = "SELECT * FROM ".$o_main->db_escape_name($dataTable);
							if($o_main->multi_acc)
							{
								$dataFilter .= (""!=$dataFilter?" AND ":"")."account_id = '".$o_main->db->escape_str($o_main->account_id)."'";
							}
							if($dataFilter!="") $s_sql .= ' WHERE '.$dataFilter;
							$o_query = $o_main->db->query($s_sql);
							if($o_query && $o_query->num_rows()>0)
							{
								foreach($o_query->result() as $o_row)
								{
									$outName = "";
									foreach($dataNames as $dataName) $outName .= $o_row->$dataName." ";
									${$varName}[$o_row->$dataID] = $outName;
								}
							}
						}
						else if($outfield[4] == "Number" and $outfield[11] != "")
						{
							list($relTable, $relID, $relName) = explode(":",$outfield[11]);
							$o_query = $o_main->db->query('SELECT '.$o_main->db_escape_name($relID).', '.$o_main->db_escape_name($relName).' FROM '.$o_main->db_escape_name($relTable).($o_main->multi_acc?" WHERE account_id = '".$o_main->db->escape_str($o_main->account_id)."'":""));
							if($o_query && $o_query->num_rows()>0)
							{
								$varName = $outfield[0]."ListArray";
								${$varName} = array();
								${$varName}["type"] = "relation";
								
								foreach($o_query->result() as $o_row)
								{
									${$varName}[$o_row->$relID] = $o_row->$relName;
								}
							}
						}
						else if($outfield[4] == "Checkbox")
						{
							$varName = $outfield[0]."ListArray";
							${$varName} = array();
							${$varName}["type"] = "checkbox";
							${$varName}[''] = ${$varName}[0] = "<div class=\"adm-ui-checkbox-empty\"></div>";
							${$varName}[1] = "<div class=\"adm-ui-checkbox-full\"></div>";
						}
						else if($outfield[4] == "FileOrImage")
						{
							$varName = $outfield[0]."Json";
							${$varName} = array();
							${$varName}["type"] = "json";
							${$varName}['levels'] = array(0);
						}
						else if($outfield[4] == "Comment")
						{
							$varName = $outfield[0]."Json";
							${$varName} = array();
							${$varName}["type"] = "json";
							${$varName}['levels'] = array(1);
						}
						else if($outfield[4] == "InvoiceNumber")
						{
							$varName = $outfield[0]."invoiceSearch";
							${$varName} = "SEARCH";
						}
						else if($outfield[4] == "ShowInvoice")
						{
							$varName = $outfield[0]."invoiceSearch";
							${$varName} = "OWNSEARCH";							 
						}				
					}
				}
				if($outlink != "")
				{
					$linkUse .= "&amp;sortfield=".$outlink; 
				}
				$counter++;
			}
		}
		$result = array();

		$l_rand_num = rand(1,999999);
		$prebuttonconfig = '';
		$s_include_file = $accountPath."/modules/".$module."/input/settings/buttonconfig/".$submodule."inputform.php";
		if(is_file($s_include_file))
		{
			include($s_include_file);
			$buttons = explode("¤",$prebuttonconfig);
			foreach($buttons as $button)
			{
				$items = explode(":",$button);
				if(count($items) > 2 )
				{
					$content_buttons[] = $items;
				}
			}
		}
		$exDir = explode("modules",$extradir);
		ob_start();
		if($o_get_content && $o_get_content->num_rows()>0)
		{
			foreach($o_get_content->result_array() as $writeContent)
			{
				$showpageID = array();
				$content_id = $writeContent['sideID'];
				$s_sql = 'SELECT p.id, pc.urlrewrite FROM pageID p LEFT OUTER JOIN pageIDcontent pc ON pc.pageIDID = p.id AND pc.languageID = ? WHERE p.contentID = ? AND p.contentTable = ?';
				$o_query = $o_main->db->query($s_sql, array($s_default_output_language, $content_id, $submodule));
				if($o_query) $showpageID = $o_query->row_array();
				$editLink = $_POST['selfurl']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&ID=".$content_id."&includefile=edit&submodule=".$submodule; 
				if(isset($_GET['relationID']) && $_GET['relationID'] != "") $editLink .= "&relationID=".$_GET['relationID']."&parentID=".$_GET['relationID'];
				if(isset($_GET['relationfield']) && $_GET['relationfield'] != "") $editLink .= "&relationfield=".$_GET['relationfield'];
				if(isset($_GET["content_status"])) $editLink .= "&content_status=".$_GET['content_status'];
				$edit_link_attr = "";
				
				?><div class="input_list_item activate<?php echo ($content_id==$_GET['ID'] ? ' active' : '');?>" data-group="input_list_item">
					<div class="input_list_data"><?php
					ob_start();
					$setCounter = 1;
					$item_name = "";
					foreach($linkers as $key => $finList)
					{
						?>
						<div class="inputListSet inputListSet<?php echo $setCounter;?>">
						<?php
						$counter = 0;
						foreach($finList as $key => $finList)
						{
							$varName = $finList."ListArray";
							?><div class="column column<?php echo $counter;?> <?php echo ($counter>0?" next":"");?>"><?php
							if(is_file(__DIR__."/../fieldtypes/".$linkersField[$key][4]."/customlistdisplay.php"))
							{
								include(__DIR__."/../fieldtypes/".$linkersField[$key][4]."/customlistdisplay.php");
							} else {
								$invoiceName = $finList."invoiceSearch";
								$varJson = $finList."Json";
								if(isset(${$invoiceName}))
								{
									$invoiceID = $content_id;
									if(${$invoiceName} == "SEARCH")
									{
										$invoiceID = $writeContent[$finList];
									}
									$o_query = $o_main->db->query("SELECT invoiceFile FROM invoice WHERE id = ?".($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":""), array($invoiceID));
									if($o_query && $o_row = $o_query->row())
									{
										if($o_row->invoiceFile != "")
										{
											$v_path_split = explode("uploads/",$o_row->invoiceFile);
											$filepathTo = $extradomaindirroot."uploads/".$v_path_split[1];
											echo '<a href="'.$filepathTo.(strpos($filepathTo,'uploads/protected/')!==false ? '?caID='.$_GET['caID'].'&table=invoice&field=invoiceFile&ID='.$invoiceID : '').'" target="_blank">'.$formText_openPdf_input.'</a>';
										}
									}
								}
								else if(isset(${$varJson}))
								{
									$value = "";
									$data = json_decode($writeContent[$finList]);
									foreach($data as $item0)
									{
										if(sizeof(${$varJson}['levels'])>1)
										{
											foreach($item0[${$varJson}['levels'][0]] as $item1)
											{
												if($value!="") $value.="; ";
												$value .= html_entity_decode($item1[${$varJson}['levels'][1]]);
											}
										} else {
											if($value!="") $value.="; ";
											$value .= html_entity_decode($item0[${$varJson}['levels'][0]]);
										}
									}
									//if($nameDirectLink == 1) 
									echo '<a href="'.$editLink.'" class="optimize">';
									echo  htmlentities($value);
									//if($nameDirectLink == 1) 
									echo '</a>';
									if($item_name == "") $item_name = $value;
								} else {
									if($access>=10 and isset(${$varName}) && ${$varName}["type"] == "checkbox")
									{
										echo '<a href="'.$extradir."/input/update.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&updateID=".$content_id."&updatemodule=".$submodule."&updatefield=".$finList."&updatevalue=".($writeContent[$finList]==1 ? "0" : "1")."&start=".$start."&moduleID=".$moduleID."&parentdir=".$parentdir."&extradir=".$extradir.(isset($_GET["content_status"]) ? "&content_status=".$_GET["content_status"] : "").'">';
									}
									else //if($nameDirectLink == 1)
									{
										echo '<a href="'.$editLink.'" class="optimize">';
									}
									echo htmlentities(isset(${$varName}) ? ${$varName}[$writeContent[$finList]] : $writeContent[$finList]);
									//if($nameDirectLink == 1 || ($access>=10 and isset(${$varName}) && ${$varName}["type"] == "checkbox"))
									echo '</a>'; 
									if($item_name == "") $item_name = (isset(${$varName}) ? ${$varName}[$writeContent[$finList]] : $writeContent[$finList]);
								}
							}
							?></div><?php
							$counter++;
						}?>
						</div>
						<?php
						$setCounter++;
					}
					if($item_name == "")
					{
						ob_clean();
						$setCounter = 1;
						$item_name = "noname";
						foreach($linkers as $key => $finList)
						{
							?>
							<div class="inputListSet inputListSet<?php echo $setCounter;?>">
							<?php
							$counter = 0;
							foreach($finList as $key => $finList)
							{
								?><div class="column column<?php echo $counter;?> <?php echo ($counter>0?" next":"");?>"><?php
								if($counter==0) echo '<a href="'.$editLink.'" class="optimize">'.htmlentities($item_name).'</a>';
								?></div><?php
								$counter++;
							}?>
							</div>
							<?php
							$setCounter++;
						}
					}
					echo ob_get_clean();
					?>
					</div>
					<?php if(isset($_GET['subcontent'])) { ?>
					<div class="list_buttons popup_button_box dropdown">
						<a id="<?php echo "l_btn_".$module."_".$submodule."_".$content_id;?>" class="script" data-target="#" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<span class="glyphicon glyphicon-menu-hamburger glyphicon-white"></span>
						</a>
						<ul class="dropdown-menu" role="menu" aria-labelledby="<?php echo "l_btn_".$module."_".$submodule."_".$content_id;?>"><?php
						foreach($content_buttons as $buttonsArray)
						{
							$buttonSubmodule = $buttonsArray[6];
							$buttonModule = $buttonsArray[0];
							$buttonInclude = $buttonsArray[2];
							$buttonRelationModule = $buttonsArray[3];
							$buttonMode = $buttonsArray[4];
							if($buttonMode == 0)
							{
								?><li><?php
								include($accountPath."/modules/".$module."/input/buttontypes/$buttonInclude/button.php");
								?></li><?php
							}
						}
						if($orderManualOrByField == '0' and $access >= 10)
						{
							?><li><a href="<?php echo $_POST['selfurl']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&ID=".$content_id."&includefile=orderContent&submodule=".$submodule.(array_key_exists('menulevel',$writeContent) ? "&menulevel=".$writeContent['menulevel'] : "").(isset($_GET['relationID']) ? "&relationID=".$_GET['relationID']."&relationfield=".$_GET['relationfield']."&list=1" : "").(isset($_GET["content_status"]) ? "&content_status=".$_GET['content_status'] : "")."&_=".$l_rand_num;?>" class="optimize" role="menuitem"><?php echo $formText_order_list;?></a></li><?php
						} 
						
						if($listButtonContentSettings == 1 and $access >= 10)
						{
							?><li><a href="<?php echo $_POST['selfurl']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&ID=".$content_id."&includefile=editContentSettings&submodule=".$submodule.(isset($_GET["content_status"]) ? "&content_status=".$_GET['content_status'] : "");?>" class="optimize" role="menuitem"><?php echo $formText_contentsettings_list;?></a></li><?php
						}
						
						if($showShowpagebutton == 1)
						{
							?><li><a href="<?php echo (isset($languagedir) ? $languagedir : "../").(isset($showpageID['urlrewrite']) and $showpageID['urlrewrite'] != '' ? $showpageID['urlrewrite'] : "index.php?pageID=".$showpageID['id']);?>" target="_blank" role="menuitem"><?php echo $formText_showPage_list;?></a></li><?php
						}
					
						
						if($prelistButtonDelete == 1 and $access >= 100)
						{
							?><li><a class="delete-confirm-btn" data-name="<?php echo $item_name;?>" href="<?php echo $extradir."/input/delete.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&deleteID=".$content_id."&deletemodule=".$submodule."&submodule=".$submodule."&choosenListInputLang=".$choosenListInputLang.(isset($_GET['relationID'])?"&relationID=".$_GET['relationID']."&parentID=".$_GET['relationID']:"").(isset($_GET['relationfield'])?"&relationfield=".$_GET['relationfield']:"").(isset($_GET["content_status"]) ? "&content_status=".$_GET['content_status'] : "");?>" role="menuitem"><?php echo $formText_delete_list;?></a></li><?php
						}
						?>
						</ul>
					</div>
					<?php } ?>
					<div class="clear_both"></div>
				</div><?php
				$counter++;
			}
		}
		$result = ob_get_clean();
		if($result != "") {
			$output['html'] = $result;
		}
	}
}else{
	$output['html'] = '<div class="input_list_item">No access</div>';
}
echo json_encode($output);
