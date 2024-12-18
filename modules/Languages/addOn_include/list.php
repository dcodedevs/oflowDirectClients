<?php
require_once(__DIR__."/../input/includes/class_ListInfo.php");

$kols = array("#f6f7f8","#FFFFFF");
$soker = new ListInfo();
$soker->Start($submodule);
$start = 0;
$startList = 0;
$searchword = '';
$searchaddon = '';
$orgsearch = '';
$sortefield = '';
$orderByField = '';
$perPage = 100;
$orderByDesc = 'ASC';
$sqlList = '';

if(isset($_GET['sortfield']) && $_GET['sortfield'] != '')
{
	$sortefield = str_replace("'","",$_GET['sortfield']);
	$sortefield = str_replace(" ","",$sortefield);
	$orderByField = $sortefield;
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
	$startList = $start * $perPage;
	$_SESSION['caID_'.$_GET['caID']]['module_page'] = $start;
} else {
	unset($_SESSION['caID_'.$_GET['caID']]['module_page']);
}
if(isset($_GET['search']) && $_GET['search'] != "")
{
	$searchword = $_GET['search'];
	foreach($fields as $felt)
	{
		if($felt[0] != "createdBy" && $felt[0] != "updatedBy" && $felt[3] == $soker->mainTable)
		{
			$soker->searchConditions[] = array($searchword,"LIKE",$submodule,$felt[0]);
		}
	}
	$_SESSION['caID_'.$_GET['caID']]['module_search'] = $searchword;
} else {
	unset($_SESSION['caID_'.$_GET['caID']]['module_search']);
}
if(isset($_GET['relationfield']))
{
	$soker->conditions[] = array($_GET['relationID'],"=",$submodule,$_GET['relationfield']);
}
if($linkToModuleID == 1)
	$soker->conditions[] = array($moduleID,"=",$submodule,"moduleID");
if(is_file($extradir."/addOn_InputListFilter/addOn_InputListFilter.php"))
{
	include($extradir."/addOn_InputListFilter/addOn_InputListFilter.php");
}
if($settingsChoice_maxLevel_inputMenuLevels > 0)
{
	$soker->conditions[] = array(0,"=",$submodule,"level");
}

if(sizeof($soker->conditions) > 0 || sizeof($soker->searchConditions) > 0)
{
	if($sqlList == '') $sqlList = ' WHERE';
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
			$sqlList .= " ".$condition[2].".".$condition[3]." ".$condition[1]." '%".$condition[0]."%' ESCAPE '!'";
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
			$sqlList .= " ".$condition[2].".".$condition[3]." ".$condition[1]." ".$condition[0];
		}				 
	}
}
$module_multi_table = false;
if($o_main->db->table_exists($soker->mainTable."content'")) $module_multi_table = true;


if($module_multi_table)
{
	$sqlSelect = "";
	$sqlExcludeColumns = array('id','moduleID','created','createdBy','updated','updatedBy','sortnr','origId');
	$v_fields = $o_main->db->list_fields($soker->mainTable.'content');
	foreach($v_fields as $s_field)
	{
		if(!in_array($s_field, $sqlExcludeColumns)) $sqlSelect .= ', '.$soker->mainTable.'content.'.$s_field;
	}
	
	$listOut = 'SELECT '.$soker->mainTable.'.*, '.$soker->mainTable.'.id as sideID '.$sqlSelect.' FROM '.$soker->mainTable.' LEFT OUTER JOIN '.$soker->mainTable.'content ON '.$soker->mainTable.'content.'.$soker->mainTable.'ID = '.$soker->mainTable.'.id AND '.$soker->mainTable.'content.languageID = '.$o_main->db->escape($s_default_output_language).' '.$sqlList.$s_sql_order.' LIMIT '.$startList.', '.$perPage;
	
	$countOut = 'SELECT COUNT('.$soker->mainTable.'.id) cnt FROM '.$soker->mainTable.' LEFT OUTER JOIN '.$soker->mainTable.'content ON '.$soker->mainTable.'content.'.$soker->mainTable.'ID = '.$soker->mainTable.'.id AND '.$soker->mainTable.'content.languageID = '.$o_main->db->escape($s_default_output_language).' '.$sqlList;
} else {
	$listOut = 'SELECT '.$soker->mainTable.'.*, '.$soker->mainTable.'.id as sideID FROM '.$soker->mainTable.$sqlList.$s_sql_order.' LIMIT '.$startList.', '.$perPage;
	
	$countOut = 'SELECT COUNT('.$soker->mainTable.'.id) cnt FROM '.$soker->mainTable.$sqlList;
}
//print "listOut = $listOut<br>";
//print "countOut = $countOut<br>";
$number = 0;
$o_query = $o_main->db->query($countOut);
if($o_query && $o_row = $o_query->row()) $number = $o_row->cnt;			
$pageNum = ceil($number / $perPage);
$o_get_content = $o_main->db->query($listOut);
?>
<div style="padding-left:12px; padding-top:12px;">
	<?php
	if($showSearchField == 1)
	{
		?><form style="padding:0; margin:0;" action="<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID'];?>" method="get">
		<input type="hidden" name="module" value="<?=$module; ?>" />
		<input type="hidden" name="submodule" value="<?=$submodule; ?>" />
		<input type="text" name="search" value="<?=$searchword; ?>" />&nbsp;&nbsp;&nbsp;
		<?php if(isset($_GET['relationID'])){ ?><input type="hidden" name="relationID" value="<?=$_GET['relationID']; ?>" /><?php } ?>
		<?php if(isset($_GET['relationfield'])){ ?><input type="hidden" name="relationfield" value="<?=$_GET['relationfield']; ?>" /><?php } ?>
		<input style="background-color:#cccccc; font-family:Verdana, Arial, Helvetica, sans-serif; border:1px solid #000000; font-size:10px; font-weight:bold; padding-left:17px; padding-right:17px; line-height:20px;" type="submit" name="send" value="Search" />
		</form><?php
	}		
	
	$linkers = array($fieldInList,$presecondFieldInList,$prethirdFieldInList,$preforthFieldInList,$prefifthFieldInList,$presixthFieldInList);
	
	$linkStandard = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&start=".$start; 
	if(isset($_GET['relationID'])) { $linkStandard .= "&amp;relationID=".$_GET['relationID'];  }
	if(isset($_GET['relationfield'])) { $linkStandard .= "&amp;relationfield=".$_GET['relationfield'];  }
	//print_r($fields);
	?>
<br />
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<?php
$linkersField = array();
foreach($linkers as $key => $outlink)
{  
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
				$s_sql = 'SELECT * FROM '.$o_main->db_escape_name($dataTable);
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
				$o_query = $o_main->db->query('SELECT '.$o_main->db_escape_name($relID).', '.$o_main->db_escape_name($relName).' FROM '.$o_main->db_escape_name($relTable));
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
	?><td><a href="<?=$linkUse;?>" class="optimize"><?=$linkersField[$key][2];?></a></td><?php
}
?></tr><?php
$exDir = explode("modules",$extradir);
if($o_get_content && $o_get_content->num_rows()>0)
foreach($o_get_content->result_array() as $writeContent)
{
	$showpageID = array();
	$content_id = $writeContent['sideID'];
	$s_sql = 'SELECT p.id, pc.urlrewrite FROM pageID p LEFT OUTER JOIN pageIDcontent pc ON pc.pageIDID = p.id AND pc.languageID = ? WHERE p.contentID = ? AND p.contentTable = ?';
	$o_query = $o_main->db->query($s_sql, array($s_default_output_language, $content_id, $submodule));
	if($o_query) $showpageID = $o_query->row_array();
	$linkText = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&ID=".$writeContent['sideID']."&includefile=edit&submodule=".$submodule; 
	if(isset($_GET['relationID'])){ $linkText .= "&relationID=".$_GET['relationID']."&parentID=".$_GET['relationID']; } 
	if(isset($_GET['relationfield'])){ $linkText .= "&relationfield=".$_GET['relationfield']; }
	
	?><tr bgcolor="<?=$kols[$counter % 2]; ?>"><?php
	$item_name = "";
	foreach($linkers as $key => $finList)
	{
		?><td style="padding:2px; line-height:14px;"><?php
		if(is_file(dirname(__FILE__)."/../fieldtypes/".$linkersField[$key][4]."/customlistdisplay.php"))
		{
			include(dirname(__FILE__)."/../fieldtypes/".$linkersField[$key][4]."/customlistdisplay.php");
		} else {
			$invoiceName = $finList."invoiceSearch";
			$varJson = $finList."Json";
			if(isset(${$invoiceName}))
			{
				$invoiceID = $writeContent['id'];
				if(${$invoiceName} == "SEARCH")
				{
					$invoiceID = $writeContent[$finList];
				}
				$o_query = $o_main->db->query('SELECT invoiceFile FROM invoice WHERE id = ?', array($invoiceID));
				if($o_query && $o_row = $o_query->row())
				{
					if($o_row->invoiceFile != "")
					{
						$v_path_split = explode("uploads/",$writeInvoice['invoiceFile']);
						$filepathTo = $extradomaindirroot."uploads/".$v_path_split[1];
						print '<a href="'.$filepathTo.(strpos($filepathTo,'uploads/protected/')!==false ? '?caID='.$_GET['caID'].'&table=invoice&field=invoiceFile&ID='.$invoiceID : '').'" target="_blank">'.$formText_openPdf_input.'</a>';
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
				if($nameDirectLink == 1){ ?><a href="<?php print $linkText;?>" style="text-decoration:none; color:#000000;" class="optimize"> <?php  }
				echo $value;
				if($nameDirectLink == 1){ ?></a><?php }
				if($item_name == "") $item_name = $value;
			} else {
				$varName = $finList."ListArray";
				if($access>=10 and isset(${$varName}) && ${$varName}["type"] == "checkbox")
				{
					print '<a href="'.$extradir.'/input/update.php?pageID='.$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID'].'&module='.$module.'&updateID='.$writeContent['sideID'].'&updatemodule='.$submodule.'&updatefield='.$finList.'&updatevalue='.($writeContent[$finList]==1 ? '0' : '1').'&start='.$start.'&moduleID='.$moduleID.'&parentdir='.$parentdir.'&extradir='.$extradir.'">';
				}
				else if($nameDirectLink == 1)
				{
					?><a href="<?php print $linkText;?>" style="text-decoration:none; color:#000000;" class="optimize"><?php
				}
				echo (isset(${$varName}) ? ${$varName}[$writeContent[$finList]] : $writeContent[$finList]);
				if($nameDirectLink == 1 || ($access>=10 and isset(${$varName}) && ${$varName}["type"] == "checkbox")){ ?></a><?php } 
				if($item_name == "") $item_name = (isset(${$varName}) ? ${$varName}[$writeContent[$finList]] : $writeContent[$finList]);
			}
		}
	}
	
	
	
	?></td><?php
	
	if($listButtonEdit == 1)
	{
		?><td style="padding:2px;"><a style="text-decoration:none; color:#000066;" href="<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&moduleID=".$moduleID."&ID=".$writeContent['sideID']."&includefile=edit&submodule=".$submodule.(isset($_GET['relationID']) ? "&relationID=".$_GET['relationID']."&parentID=".$_GET['relationID']:'').(isset($_GET['relationfield']) ? "&relationfield=".$_GET['relationfield']:'');?>" class="optimize"><?=$formText_edit_list;?></a></td><?php
	}
	
	if($contentorderfield != '0' and $access >= 10)
	{
		?><td style="padding:2px;"><a style="text-decoration:none; color:#000066;" href="<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&ID=".$writeContent['sideID']."&includefile=orderContent&submodule=".$submodule.(array_key_exists('menulevel',$writeContent) ? "&menulevel=".$writeContent['menulevel']:"").(isset($_GET['relationID']) ? "&relationID={$_GET['relationID']}&relationfield={$_GET['relationfield']}&list=1":"");?>" class="optimize"><?=$formText_order_list;?></a></td><?php
	} 
	
	if($listButtonContentSettings == 1 and $access >= 10)
	{
		?><td style="padding:2px;"><a style="text-decoration:none; color:#000066;" href="<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&ID=".$writeContent['sideID']."&includefile=editContentSettings&submodule=".$submodule;?>" class="optimize"><?=$formText_contentsettings_list;?></a></td><?php
	}
	
	if($listButtonDelete == 1 and $access >= 100)
	{
		?><td style="padding:2px;"><a style="text-decoration:none; color:#000066;" onClick="return confirm('<?=addslashes($formText_DeleteItem_input.': '.$item_name);?>?');" href="<?=$extradir."/input/delete.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&deleteID=".$writeContent['sideID']."&deletemodule=".$submodule."&start=".$start."&parentdir=".$parentdir."&submodule=".$submodule."&extradir=".$extradir.(isset($_GET['relationID']) ? "&relationID=".$_GET['relationID']."&parentID=".$_GET['relationID']:"").(isset($_GET['relationfield']) ? "&relationfield=".$_GET['relationfield']:"")."&moduleID=".$moduleID;?>"><?=$formText_delete_list;?></a></td><?php
	}
	?></tr><?php
	$counter++;
	$listParentID = $writeContent['sideID'];
}
?></table></div><br />
<?php
if($start > 0)
{
?><div style="float:left; line-height:19px; padding-right:4px; padding-left:4px;"><a style="font-family:Helvetica, Arial, sans-serif; color:#70685a; text-decoration:none; font-size:11px;" href="<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&start=".($start - 1)."&search=".$searchword.($sortefield != "" ? "&sortfield=".$sortefield:"")."&descUse=".$orderByDesc.(isset($_GET['relationfield']) ? "&relationfield=".$_GET['relationfield']."&relationID=".$_GET['relationID']:"");?>" class="optimize"><?=$formText_Previous_input;?></a></div><?php
}	
if($pageNum > 1)
{
	for($z = 0; ($z+1) <= $pageNum; $z++)
	{
	?><div style="float:left; line-height:19px; padding-right:4px; padding-left:4px;"><a style="font-family:Helvetica, Arial, sans-serif; text-decoration:none; font-size:11px;<?php if($start == $z){ ?>font-weight:bold; color:#669900;<?php }else{ ?>color:#70685a;<?php }  ?>" href="<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&search=".$searchword."&submodule=".$submodule."&start=".$z.($sortefield != "" ? "&sortfield=".$sortefield:"")."&descUse=".$orderByDesc.(isset($_GET['relationfield']) ? "&relationfield=".$_GET['relationfield']."&relationID=".$_GET['relationID']:"");?>" class="optimize"><?=($z + 1); ?></a></div><?php
	}
	if(($start+1) < $pageNum)
	{
	?><div style="float:left; line-height:19px; padding-right:4px; padding-left:4px;"><a style="font-family:Helvetica, Arial, sans-serif; color:#70685a; text-decoration:none; font-size:11px;" href="<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&start=".($start + 1)."&search=".$searchword.($sortefield != "" ? "&sortfield=".$sortefield:"")."&descUse=".$orderByDesc.(isset($_GET['relationfield']) ? "&relationfield=".$_GET['relationfield']."&relationID=".$_GET['relationID']:"");?>" class="optimize"><?=$formText_Next_input;?></a></div><?php
	}
}
?><br clear="all" />