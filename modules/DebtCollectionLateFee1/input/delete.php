<?php
$v_return = array();
define('BASEPATH', realpath(__DIR__.'/../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
if(!function_exists("ftp_file_put_content")) include(__DIR__."/includes/ftp_commands.php");
if(!function_exists("log_action")) include(__DIR__."/includes/fn_log_action.php");

include(__DIR__."/includes/readInputLanguage.php");

$s_sql = "SELECT * FROM session_framework WHERE companyaccessID = '".$o_main->db->escape_str($_GET['caID'])."' AND session = '".$o_main->db->escape_str($_COOKIE['sessionID'])."' AND username = '".$o_main->db->escape_str($_COOKIE['username'])."'".($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"")." LIMIT 1";
$o_query = $o_main->db->query($s_sql);
if($o_query && $fw_session = $o_query->row_array())
{
	$menuaccess = json_decode($fw_session['cache_menu'], true);
	$access = $menuaccess[$_GET['module']][2];
	$b_owner_access = ($menuaccess[$_GET['module']][6] == 1 ? true : false);
	if($fw_session['developeraccess'] >= 20)
	{
		$access = 111;
		$b_owner_access = false;
	}
} else {
	$access = 0;
	$b_owner_access = false;
}
$username = $_COOKIE['username'];

$error_msg = array();
$module = $_GET['module'];
$submodule = $_GET['submodule'];
$parentID = $_GET['parentID'];
$ID = intval($_GET['deleteID']);

if($o_main->db->table_exists($submodule))
{
	$o_query = $o_main->db->query("SELECT * FROM ".$o_main->db_escape_name($submodule)." WHERE id = '".$o_main->db->escape_str($ID)."'");
	if($o_query && $o_query->num_rows()>0)
	{
		$o_row = $o_query->row();
		$moduleID = $o_row->moduleID;
		
		// check owner access
		if($b_owner_access && $o_row->createdBy != $username)
		{
			$access = $access % 10;
		}
		
		if($access >= 100)
		{
			$s_return_url = substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&";
			 
			if($parentID == 0 || $parentID == ""){
				$s_return_url .= "module=$module&submodule=$submodule";
			} else {
				$s_return_url .= "module=$module&submodule=$submodule&includefile=list";
			}
			
			if(isset($_GET['relationID']))
			{
				$s_return_url .="&relationID=".$_GET['relationID']."&relationfield=".$_GET['relationfield'];
			}
			if(isset($_GET["content_status"]))
			{
				$s_return_url .= "&content_status=".$_GET['content_status'];
			}
			
			if(is_dir(__DIR__.'/../addOn_Delete/'))
			{
				$o_dir_handle = opendir(__DIR__.'/../addOn_Delete/');
				$foldercount = 0;
				while($s_addon_file = readdir($o_dir_handle))
				{	
					$v_file_parts = explode(".",$s_addon_file);
					if($v_file_parts[2] != "LCK" && $v_file_parts[1] == "php" && $v_file_parts[0] != "")
					{
						include(__DIR__.'/../addOn_Delete/'.$s_addon_file);
					}
				}
			}
			
			include(__DIR__."/includes/fieldloader.php");
			
			$o_query = $o_main->db->query("SELECT id FROM ".$submodule." WHERE id = ? AND content_status = ?", array($ID, 2));
			if($activateSafeDelete=="1" && $o_query && $o_query->num_rows()>0)
			{
				$activateSafeDelete = 0;
			}
			$headTree = new ChildTree();
			$headTree->name = $submodule;
			$currentTree = $headTree;
			$childrenArray = array($submodule);
			$currentTree->addChildren($childrenArray);
			
			$currentTree->deleteChildContent($o_main, $ID, $error_msg, $moduleID, $activateSafeDelete);
			
			if($activateSafeDelete=="1")
			{
				$o_query = $o_main->db->query("UPDATE ".$submodule." SET content_status = ? WHERE id = ?", array(2, $ID));
				if($o_query)
				{
					$s_sql = "UPDATE session_framework SET error_msg = '".$o_main->db->escape_str(json_encode(array("info_1"=>$formText_ContentMarkedAsDeleted_input)))."' WHERE companyaccessID = '".$o_main->db->escape_str($_GET['caID'])."' AND session = '".$o_main->db->escape_str($_COOKIE['sessionID'])."' AND username = '".$o_main->db->escape_str($_COOKIE['username'])."'".($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"");
					$o_main->db->query($s_sql);
				}
			} else {
				// ali - call each fieldtype delete code
				$fieldsPath = __DIR__."/settings/fields/".$submodule."fields.php";
				if(file_exists($fieldsPath))
				{
					include($fieldsPath);
					foreach($prefields as $field)
					{
						$fieldInfo = explode("¤",$field);
						$deleteFieldID = $ID;
						$deleteFieldTable = $submodule;
						$deleteFieldRelID = "id";
						$deleteFieldField = $fieldInfo[0];
						$deleteFieldPath = __DIR__."/fieldtypes/".$fieldInfo[4]."/predeleteContent.php";
						if(is_file($deleteFieldPath)) include($deleteFieldPath);
					}
				}
				
				if(sizeof($error_msg)==0)
				{
					$o_main->db->delete($submodule, array('id' => $ID));
					if($o_main->db->table_exists($submodule.'content'))
					{
						$o_main->db->delete($submodule.'content', array($submodule.'ID' => $ID));
					}
					
					foreach($databases as $basetable)
					{
						if($basetable->connection == $submodule)
						{
							$o_main->db->delete($basetable->name, array($submodule.'ID' => $ID));
						}
					}
					$o_main->db->delete('pageID', array('contentID' => $ID, 'contentTable' => $submodule));
					
					$s_sql = "UPDATE session_framework SET error_msg = '".$o_main->db->escape_str(json_encode(array("info_1"=>$formText_ContentHasBeenDeleted_input)))."' WHERE companyaccessID = '".$o_main->db->escape_str($_GET['caID'])."' AND session = '".$o_main->db->escape_str($_COOKIE['sessionID'])."' AND username = '".$o_main->db->escape_str($_COOKIE['username'])."'".($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"");
					$o_main->db->query($s_sql);
				}
			}
		} else {
			$error_msg["error_".count($error_msg)] = $formText_YouHaveNoAccessToDeleteThisContent_input;
		}
	} else {
		$error_msg["error_".count($error_msg)] = $formText_ContentNotFound_Input;
	}
} else {
	$error_msg["error_".count($error_msg)] = $formText_IncorrectOrMissingParameters_input;
}
// return error messages
if(count($error_msg)>0)
{
	$v_return["error"] = $error_msg;
	log_action("content_delete_fail");
} else {
	$v_return["url"] = $s_return_url;
	log_action("content_delete");
}
echo json_encode($v_return);
exit;

class ChildTree
{
	var $name = "";
	var $children = array();
	var $connectFields = "";
	var $linkToModuleId = 0;
	 
	function start($name){}
	 
	function addChildren($childrenArray)
	{
		$childPath = __DIR__."/settings/relations/".$this->name.".php";
		if(file_exists($childPath))
		{
			include($childPath);
			foreach($prerelations as $relation)
			{
				$splitName = explode("¤",$relation);
				if(!in_array($splitName[2],$childrenArray))
				{
					$newChild = new ChildTree();
					$newChild->name = $splitName[2];
					$childrenArray[] = $splitName[2];
					$newChild->connectFields = $splitName[3];
					if(isset($splitName[13])) $newChild->linkToModuleId = $splitName[13];
					$this->children[] = $newChild;
				}
			}
		}
		foreach($this->children as $child)
		{
			$child->addChildren($childrenArray);
		}
	}
	 
	function deleteChildContent($o_main, $ID, &$error_msg, $moduleID, $activateSafeDelete)
	{
		foreach($this->children as $deleter)
		{
			if($activateSafeDelete=="1")
			{
				$o_main->db->query("UPDATE ".$deleter->name." SET content_status = ? WHERE ".$deleter->connectFields." = ?".($deleter->linkToModuleId ? " AND moduleID = '".$o_main->db-escape_str($moduleID)."'" : ''), array(2, $ID));
			} else {
				// ali - call each fieldtype delete code
				$fieldsPath = __DIR__."/settings/fields/".$deleter->name."fields.php";
				if(file_exists($fieldsPath))
				{
					include($fieldsPath);
					foreach($prefields as $field)
					{
						$fieldInfo = explode("¤",$field);
						$deleteFieldID = $ID;
						$deleteFieldTable = $deleter->name;
						$deleteFieldRelID = $deleter->connectFields;
						$deleteFieldField = $fieldInfo[0];
						$deleteFieldPath = __DIR__."/fieldtypes/".$fieldInfo[4]."/predeleteContent.php";
						if(is_file($deleteFieldPath)) include($deleteFieldPath);
					}
				}
				$v_ids = array();
				$o_query = $o_main->db->query("SELECT id FROM ".$deleter->name." WHERE ".$deleter->connectFields." = ?".($deleter->linkToModuleId ? " AND moduleID = '".$o_main->db->escape_str($moduleID)."'" : ''), array($ID));
				if($o_query && $o_query->num_rows()>0)
				{
					foreach($o_query->result() as $o_row)
					{
						$v_ids[] = $o_row->id;
						$deleter->deleteChildContent($o_main, $o_row->id, $error_msg, $moduleID, $activateSafeDelete);
					}
				}
				
				if(0 == sizeof($error_msg))
				{
					$v_param = array($deleter->connectFields => $ID);
					if($deleter->linkToModuleId) $v_param['moduleID'] = $moduleID;
					
					$o_main->db->delete($deleter->name, $v_param);
					if($o_main->db->table_exists($deleter->name.'content') && 0 < sizeof($v_ids))
					{
						$o_delete = $o_main->db->where_in($deleter->name.'ID', $v_ids)->delete($deleter->name.'content');
					}
				}
			}
		}
	}
}