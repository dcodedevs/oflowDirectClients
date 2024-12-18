<?php
if(isset($_POST['editOrder']))
{
	define('BASEPATH', realpath(__DIR__.'/../../../../').DIRECTORY_SEPARATOR);
	require_once(BASEPATH.'elementsGlobal/cMain.php');
	
	$fw_session = array();
	$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
	$o_query = $o_main->db->get_where('session_framework', $v_param);
	if($o_query) $fw_session = $o_query->row_array();
	$menuaccess = json_decode($fw_session['cache_menu'],true);
	$access = $menuaccess[$_GET['module']][2];
}
if($access >= 10)
{
	if(isset($_POST['editOrder']))
	{
		if(!function_exists("log_action")) include(__DIR__."/fn_log_action.php");
		
		if($_POST['relationstable'] != '')
			$updatetable = $_POST['relationstable'];
		else
			$updatetable =$_POST['submodule'];
		
		if($_POST['orderbydesc'] == 1) $contentIDs = array_reverse($_POST['contentID']);
		else $contentIDs = $_POST['contentID'];
		foreach($contentIDs as $key => $value)
		{
			$key++;
			$o_main->db->query('update '.$o_main->db_escape_name($updatetable).' SET '.$o_main->db_escape_name($_POST['orderfield']).' = ? WHERE id = ?', array($key, $value));
		}
	
		$callback = substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module'];
		if(isset($_GET['parenttable']) && $_GET['parenttable'] != "")
		{
			$callback .= "&submodule=".$_GET['parenttable'];
		} else {
			$callback .= "&submodule=".$_GET['submodule'];
		}
		if(isset($_POST['relationID']))
		{
			if(isset($_POST['list']))
			{
				$callback .= "&relationID=".$_POST['relationID']."&relationfield=".$_POST['relationfield'];
			} else {
				$callback .= "&includefile=edit&ID=".$_POST['relationID'];
			}
		}
		if(isset($_POST["content_status_filter"]))
		{
			$callback .= "&content_status=".$_POST['content_status_filter'];
		}
		log_action("content_order");
		header("Location: $callback");
		exit;
	} else {
		if(isset($_GET['relationstable']))
			$updatetable = $_GET['relationstable'];
		else
			$updatetable =$_GET['submodule'];
		include(__DIR__."/../settings/tables/".$updatetable.".php");
	}
	$listField = array();
	foreach($fields as $field)
	{
		if($field[0] == $prefieldInList)
		{
			$listField = $field;
			break;
		}
	}
	?>
	<script type="text/javascript">
	$(document).ready(function() {
		$("#sortable").sortable().disableSelection();
	});
	</script>
	<?php
	if(isset($_GET['menulevel']))
	{
		$header = "";
		$o_query = $o_main->db->query('select * from menulevel where id = ?', array($_GET['menulevel']));
		while($o_query && $o_row = $o_query->row())
		{
			if($header!="") $header = ' - '.$header;
			$header = $o_row->name.$header;
			$o_query = $o_main->db->query('select * from menulevel where id = ?', array($o_row->parentlevelID));
		}
		?><h2 style="padding-left:20px;"><?php echo $formText_sortIn_input.': '.$header;?></h2><?php
	}
	?>
	<form name="sortableListForm" method="post" action="<?php print $extradir ."/input/includes/orderContent.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&submodule=".$_GET['submodule']."&includefile=".$_GET['includefile'];?>&parenttable=<?php echo $_GET['parenttable']?>">
		<input type="hidden" name="editOrder" value="1" />
		<input type="hidden" name="orderfield" value="<?php print $preorderByField; ?>" />
		<input type="hidden" name="orderbydesc" value="<?php print $preorderByDesc; ?>" />
		<input type="hidden" name="moduleID" value="<?php print $moduleID; ?>" />
		<?php if(isset($_GET['submodule'])) {?><input type="hidden" name="submodule" value="<?php echo $_GET['submodule'];?>" /><?php } ?>
		<?php if(isset($_GET['relationstable'])) {?><input type="hidden" name="relationstable" value="<?php echo $_GET['relationstable'];?>" /><?php } ?>
		<?php if(isset($_GET['relationID'])) {?><input type="hidden" name="relationID" value="<?php echo $_GET['relationID'];?>" /><?php } ?>
		<?php if(isset($_GET['relationfield'])) {?><input type="hidden" name="relationfield" value="<?php echo $_GET['relationfield'];?>" /><?php } ?>
		<?php if(isset($_GET['content_status'])) {?><input type="hidden" name="content_status_filter" value="<?php echo $_GET['content_status'];?>" /><?php } ?>
		<?php if(isset($_GET['list'])) {?><input type="hidden" name="list" value="<?php echo $_GET['list']; ?>" /><?php } ?>
		<ul id="sortable">
		<?php
		$extraJoin = $list_where = "";
		if($linkToModuleID == 1) $list_where .= " AND ".$submodule.".moduleID = ".$o_main->db->escape($moduleID);
		$list_where .= " AND ".$submodule.".content_status < 2";
		if(isset($_GET['menulevel']))
		{
			$extraJoin .= "JOIN pageID p ON p.contentID = ".$submodule.".id AND p.contentTable = ".$o_main->db->escape($submodule)." AND p.deleted <> 1 ";
			$list_where .= " AND p.menulevelID = ".$o_main->db->escape($_GET['menulevel']);
		}
		if(isset($_GET['ordersql']))
		{
			//TODO: ALI - security_check - wrong!!!
			$listSQL = base64_decode(urldecode($_GET['ordersql']));
		} else {
			if(!$o_main->db->table_exists($submodule.'content'))
			{
				$listSQL = "SELECT ".$submodule.".id as linkID,".$submodule.".* FROM ".$submodule." $extraJoin WHERE 1=1 $list_where ORDER BY ".$submodule.".".$preorderByField." ".($preorderByDesc==0 ? "ASC" : "DESC").";";
			} else {
				$listSQL = "SELECT ".$submodule.".id as linkID,".$submodule.".*,".$submodule."content.* FROM ".$submodule." JOIN ".$submodule."content ON ".$submodule."content.".$submodule."ID=".$submodule.".id and ".$submodule."content.languageID = ".$o_main->db->escape($s_default_output_language)." $extraJoin WHERE 1=1 ".$list_where." ORDER BY ".$submodule.".".$preorderByField." ".($preorderByDesc==0 ? "ASC" : "DESC").";";
			}
		}
		if($settingsChoice_maxLevel_inputMenuLevels > 0)
		{
			if(!isset($_GET['ordersql']))
			{
				$listSQL =  "SELECT ".$submodule.".id as linkID, ".$submodule.".*, ".$submodule."content.* FROM ".$submodule.",".$submodule."content WHERE ".$submodule.".level = ".$o_main->db->escape(0)." AND ".$submodule.".moduleID = ".$o_main->db->escape($moduleID)." AND ".$submodule."content.".$submodule."ID=".$submodule.".id AND ".$submodule."content.languageID = ".$o_main->db->escape($s_default_output_language)." AND ".$submodule.".content_status < 3 ORDER BY ".$submodule.".".$preorderByField." ".($preorderByDesc==0 ? "ASC" : "DESC").";";
			}
		}
		//echo "listSQL =  $listSQL<br />";
		$o_query = $o_main->db->query($listSQL);
		if($o_query && $o_query->num_rows()>0)
		{
			foreach($o_query->result_array() as $listcontent)
			{
				if($listField[4] == "Dropdown")
				{
					$varName = $listField[0]."ListArray";
					${$varName} = array();
					${$varName}["type"] = "dropdown";
					$tmp = explode("::",$listField[11]);
					foreach($tmp as $tmpv)
					{
						list($lKey,$lValue) = explode(":",$tmpv);
						${$varName}[$lKey] = $lValue;
					}
				}
				else if($listField[4] == "Number" and $listField[11] != "")
				{
					list($relTable, $relID, $relName) = explode(":",$listField[11]);
					$o_query = $o_main->db->query('SELECT '.$o_main->db_escape_name($relID).', '.$o_main->db_escape_name($relName).' FROM '.$o_main->db_escape_name($relTable));
					if($o_query && $o_query->num_rows()>0)
					{
						$varName = $listField[0]."ListArray";
						${$varName} = array();
						${$varName}["type"] = "relation";
						
						foreach($o_query->result() as $o_row) ${$varName}[$o_row->$relID] = $o_row->$relName;
					}
				}
				else if($listField[4] == "Checkbox")
				{
					$varName = $listField[0]."ListArray";
					${$varName} = array();
					${$varName}["type"] = "checkbox";
					${$varName}[''] = "No";
					${$varName}[0] = "No";
					${$varName}[1] = "Yes";
				}
				else if($listField[4] == "FileOrImage")
				{
					$varName = $listField[0]."Json";
					${$varName} = array();
					${$varName}["type"] = "json";
					${$varName}['levels'] = array(0);
				}
				else if($listField[4] == "Comment")
				{
					$varName = $listField[0]."Json";
					${$varName} = array();
					${$varName}["type"] = "json";
					${$varName}['levels'] = array(1);
				}
				//echo "prefieldInList = $prefieldInList<br />";
				$value = $listcontent[$prefieldInList];
				$varJson = $prefieldInList."Json";
				if(isset(${$varJson}))
				{
					$value = "";
					$data = json_decode($listcontent[$prefieldInList],true);
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
				} else {
					$value = (isset(${$varName}) ? ${$varName}[$listcontent[$prefieldInList]] : $listcontent[$prefieldInList]);
				}
				?><li class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span><input type="hidden" name="contentID[]" value="<?php print $listcontent['linkID']; ?>" /><?php print $value; ?></li><?php
			}
		}
		?>
		</ul>
		<div class="inputfeltbuttonLagre"><input name="submitbtn" value="Save" type="submit"></div>
	</form>
	<?php
} else {
	?><div id="hovedfeltStrek"><table style="width:100%"><tr><td class="notAccessField">You have no access to this module</td></tr></table></div><?php
}
?>