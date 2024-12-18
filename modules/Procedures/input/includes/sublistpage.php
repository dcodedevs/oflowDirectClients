<?php
$start = 0;
if($preorderByField != '0') $orderbyfield = " ORDER BY ".$submodule.".".$o_main->db_escape_name($preorderByField);
if($preorderByDesc == 1) $orderbyfield .= " DESC";

$linkers = array();
foreach($listFieldInSubmoduleVariables as $var) if(isset(${$var}) && !empty(${$var})) $linkers[0][] = ${$var};
foreach($listSet2FieldInSubmoduleVariables as $var) if(isset(${$var}) && !empty(${$var})) $linkers[1][] = ${$var};
foreach($listSet3FieldInSubmoduleVariables as $var) if(isset(${$var}) && !empty(${$var})) $linkers[2][] = ${$var};
foreach($listSet4FieldInSubmoduleVariables as $var) if(isset(${$var}) && !empty(${$var})) $linkers[3][] = ${$var};
$column_count = count($linkers[0]);
$column_width = floor(11/$column_count);
$column_width_extra = 11 - ($column_width*$column_count);
$linkersField = array();
foreach($linkers as $key => $outlink)
{
	foreach($outlink as $key=>$outlink) {
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
				else if(in_array($outfield[4],array("Image","File","FileOrImage")))
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
	}
}

if(isset($_GET['sublistorder']) && $_GET['sublistorder'] != "") {
	$orderfieldTable = $submodule;
	foreach($linkers as $key => $outlink)
	{
		foreach($outlink as $key=>$outlink) {
			foreach($fields as $outfield)
			{
				if($outfield[0] == $outlink && $outfield[0] == $_GET['sublistorder'])
				{
					$orderfieldTable = $outfield[3];
				}
			}
		}
	}
	$orderbyfield = " ORDER BY ".$orderfieldTable.".".$o_main->db_escape_name($_GET['sublistorder']);
	if(isset($_GET['sublistorderdir']) && $_GET['sublistorderdir'] != "") {
		if($_GET['sublistorderdir'] == "DESC") {	
			$orderbyfield .= " DESC";
		} else {
			$orderbyfield .= " ASC";
		}
	}
}
$extraWhere = "";
if($linkToModuleID==1)
{
	$moduleRow = array();
	$o_query = $o_main->db->query('SELECT * FROM moduledata WHERE name = ?', array($module));
	if($o_query && $o_query->num_rows()>0) $moduleRow = $o_query->row_array();
	$extraWhere .= " AND ".$submodule.".moduleID = ".$o_main->db->escape($moduleRow['uniqueID']?$moduleRow['uniqueID']:$moduleRow['id']);
}
$extraWhere .= " AND ".$submodule.".content_status ".($_GET["content_status"] != "" ? "= '".$o_main->db->escape($_GET["content_status"])."'" : "< 2");
if(isset($_GET['perPage']) && is_numeric($_GET['perPage'])) $perPage = intval($_GET['perPage']);
if(isset($_GET['start']) && is_numeric($_GET['start']))
{
	$start = intval($_GET['start']);
	$startList = $start * $perPage;
}

$module_multi_table = false;
if($o_main->db->table_exists($submodule.'content')) $module_multi_table = true;


$s_parent_table = $o_main->db_escape_name($_GET['parenttable']);
if($module_multi_table)
{
	$sqlSelect = "";
	$sqlExcludeColumns = array('id','moduleID','created','createdBy','updated','updatedBy','sortnr','origId');
	$o_fields = $o_main->db->list_fields($submodule.'content');
	foreach($o_fields as $s_field)
	{
		if(!in_array($s_field,$sqlExcludeColumns)) $sqlSelect .= ', '.$submodule.'content.'.$s_field;
	}
	$listSQL = "SELECT ".$submodule.".id as linkID, ".$submodule.".* ".$sqlSelect." FROM ".$submodule."
	LEFT OUTER JOIN ".$submodule."content ON ".$submodule."content.".$submodule."ID = ".$submodule.".id AND ".$submodule."content.languageID = ".$o_main->db->escape($s_default_output_language)."
	JOIN ".$s_parent_table." ON ".$submodule.".".$o_main->db_escape_name($_GET['sub_relationfield'])." = ".$s_parent_table.".".$o_main->db_escape_name($_GET['parentfield'])."
	WHERE ".$s_parent_table.".id = ".$o_main->db->escape($_GET['sub_parentID']).$extraWhere.$orderbyfield;
} else {
	$listSQL = "SELECT ".$submodule.".id as linkID, ".$submodule.".* FROM ".$submodule."
	JOIN ".$s_parent_table." ON ".$submodule.".".$o_main->db_escape_name($_GET['sub_relationfield'])." = ".$s_parent_table.".".$o_main->db_escape_name($_GET['parentfield'])."
	WHERE ".$s_parent_table.".id = ".$o_main->db->escape($_GET['sub_parentID']).$extraWhere.$orderbyfield;
}

$ordersql = htmlspecialchars(urlencode(base64_encode($listSQL)));
//print "listSQL = $listSQL<br>";		
$find_content = $o_main->db->query($listSQL." LIMIT $startList,$perPage");
if($find_content){
	$prebuttonconfig = '';
	include(ACCOUNT_PATH."/modules/".$module."/input/settings/buttonconfig/".$submodule."inputform.php");
	$buttons = explode("¤",$prebuttonconfig);
	foreach($buttons as $button)
	{
		$items = explode(":",$button);
		if(count($items) > 2 )
		{
			$content_buttons[] = $items;
		}
	}

	foreach($find_content->result_array() as $writeContent)
	{
		$content_id = $writeContent['linkID'];
		$editLink = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&"."module=".$module."&ID=".$content_id."&parentID=".$_GET['sub_parentID']."&subcontent=1&includefile=edit&submodule=".$submodule."&relationID=".$_GET['sub_relationID']."&relationfield=".$_GET['sub_relationfield'].($_GET["content_status"]!="" ? "&content_status=".$_GET["content_status"] : "");
		$edit_link_attr = ' data-target="#content_'.$module.'_'.$submodule.'_'.$content_id.'"';
		
		?><div class="input_list_item row" data-group="input_list_item"><?php
			ob_start();
			$setCounter = 1;
			$item_name = "";
			foreach($linkers as $key => $finList)
			{
				$counter = 0;
				?>
				<div class="inputListSet inputListSet<?php echo $setCounter;?>">
					<?php
					foreach($finList as $key=>$finList)
					{
						$varJson = $finList."Json";
						$varName = $finList."ListArray";
						$column = $column_width+($column_width_extra-$counter>0?1:0);
						?><div class="column column<?php echo $counter?>"><?php
						if(is_file(ACCOUNT_PATH."/modules/".$module."/input/fieldtypes/".$linkersField[$key][4]."/customlistdisplay.php"))
						{
							include(ACCOUNT_PATH."/modules/".$module."/input/fieldtypes/".$linkersField[$key][4]."/customlistdisplay.php");
						} else {
							$invoiceName = $finList."invoiceSearch";
							if(isset(${$invoiceName}))
							{
								$invoiceID = $content_id;
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
							} else {
								$value = "";
								if(isset(${$varJson}))
								{
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
								} else if(isset(${$varName}))
								{
									$value = ${$varName}[$writeContent[$finList]];
								} else {
									$value = $writeContent[$finList];
								}
								if(trim($value) == "")
								{
									$value = "...";
								}
								print '<a href="'.$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&"."module=".$module."&ID=".$content_id."&parentID=".$_GET['sub_parentID']."&subcontent=1&includefile=edit&submodule=".$submodule."&relationID=".$_GET['sub_relationID']."&relationfield=".$_GET['sub_relationfield'].($_GET["content_status"]!="" ? "&content_status=".$_GET["content_status"] : "").'" class="optimize" data-target="#content_'.$module.'_'.$submodule.'_'.$content_id.'"> '.$value.'</a>';
								
								if($item_name == "") $item_name = htmlspecialchars(strip_tags($value));
							}
						}
						?></div><?php
						$counter++;
					}
					?>
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
					$counter = 0;
					?><div class="inputListSet inputListSet<?php echo $setCounter;?>"><?php
					foreach($finList as $key=>$finList)
					{
						$varJson = $finList."Json";
						$varName = $finList."ListArray";
						$column = $column_width+($column_width_extra-$counter>0?1:0);
						?><div class="column column<?php echo $counter?>"><?php
						if($counter==0) print '<a href="'.$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&"."module=".$module."&ID=".$content_id."&parentID=".$_GET['sub_parentID']."&subcontent=1&includefile=edit&submodule=".$submodule."&relationID=".$_GET['sub_relationID']."&relationfield=".$_GET['sub_relationfield'].($_GET["content_status"]!="" ? "&content_status=".$_GET["content_status"] : "").'" class="optimize" data-target="#content_'.$module.'_'.$submodule.'_'.$content_id.'"> '.$item_name.'</a>';
						?></div><?php
						$counter++;
					}
					?></div><?php
					$setCounter++;
				}
			}
			print ob_get_clean();
			?>
			<div class="list_buttons popup_button_box col-md-1 dropdown">
				<a id="<?php echo "sl_btn_".$module."_".$submodule."_".$content_id;?>" class="script" data-target="#" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<span class="glyphicon glyphicon-menu-hamburger glyphicon-white"></span>
				</a>
				<ul class="dropdown-menu" role="menu" aria-labelledby="<?php echo "sl_btn_".$module."_".$submodule."_".$content_id;?>"><?php
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
						include(ACCOUNT_PATH."/modules/".$module."/input/buttontypes/$buttonInclude/button.php");
						?></li><?php
					}
				}
				
				
				/*if($showSendMail == 1 and $access >= 10)
				{
					?><li><a role="menuitem" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&ID=".$content_id."&parentID=".$_GET['sub_parentID']."&includefile=sendEmail&submodule=".$submodule."&relationID=".$_GET['sub_relationID']."&relationfield=".$_GET['sub_relationfield'].($_GET["content_status"]!="" ? "&content_status=".$_GET["content_status"] : "");?>" class="optimize"><?php echo $formText_sendEmail_list;?></a></li><?php
				}
				
				if($showSendSms == 1 and $access >= 10)
				{
					?><li><a role="menuitem" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&ID=".$content_id."&parentID=".$_GET['sub_parentID']."&includefile=sendSms&submodule=".$submodule."&relationID=".$_GET['sub_relationID']."&relationfield=".$_GET['sub_relationfield'].($_GET["content_status"]!="" ? "&content_status=".$_GET["content_status"] : "");?>" class="optimize"><?php echo $formText_sendSms_list;?></a></li><?php
				}
				
				if($showSendPdf == 1 and $access >= 10)
				{
					?><li><a role="menuitem" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&ID=".$content_id."&parentID=".$_GET['sub_parentID']."&includefile=sendPdf&submodule=".$submodule."&relationID=".$_GET['sub_relationID']."&relationfield=".$_GET['sub_relationfield'].($_GET["content_status"]!="" ? "&content_status=".$_GET["content_status"] : "");?>" class="optimize"><?php echo $formText_sendPdf_list;?></a></li><?php
				}*/
				
				if($orderManualOrByField == '0' and $access >= 10){ ?><li><a role="menuitem" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&ID=".$content_id."&includefile=orderContent&submodule=".$submodule.(array_key_exists('menulevel',$writeContent) ? "&menulevel=".$writeContent['menulevel']:"")."&relationID=".$_GET['sub_relationID']."&relationfield=".$_GET['sub_relationfield']."&relationstable=".$_GET['sub_relationstable'].($_GET["content_status"]!="" ? "&content_status=".$_GET["content_status"] : "")."&parenttable=".$submodule."&ordersql=".$ordersql;?>" class="optimize"><?php echo $formText_order_list;?></a></li><?php }
				
				if($prelistButtonDelete == 1 and $access >= 100){ ?><li><a role="menuitem" class="delete" data-name="<?php echo $item_name;?>" href="<?php echo $extradir."/input/delete.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&parentID=".$_GET['sub_parentID']."&deleteID=".$content_id."&deletemodule=".$submodule."&submodule=".$submodule."&relationID=".$_GET['sub_relationID']."&relationfield=".$_GET['sub_relationfield']."&relationstable=".$_GET['sub_relationstable'].($_GET["content_status"]!="" ? "&content_status=".$_GET["content_status"] : "");?>"><?php echo $formText_delete_sublist;?></a></li><?php }
				
				$counter++;
				?>
				</ul>
			</div>
		</div>
		<div id="<?php echo "content_".$module."_".$submodule."_".$content_id;?>" class="input_list_form"></div>
		<?php
	}
}
?>