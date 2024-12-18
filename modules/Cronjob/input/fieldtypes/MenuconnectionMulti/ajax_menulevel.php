<?php
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
$selected = $v_menues = array();
if($_GET['menumodulemultiselect']==1)
{
	$o_query = $o_main->db->query('SELECT * FROM moduledata WHERE type = 1');
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $v_row)
	{
		$v_menues[$v_row['id']] = $v_row['name'];
	}
}

if(strpos($_GET['parentID'],'_')!==false)
{
	$filter = 'IN (';
	$parents = explode(':',$_GET['parentID']);
	foreach($parents as $key => $parent)
	{
		list($parentLevel, $useID) = explode('_',$parent);
		if($key>0) $filter .= ',';
		$filter .= $o_main->db->escape($useID);
	}
	$filter .= ')';
	$multi = true;
} else {
	if(strpos($_GET['parentID'],':')!==false)
	{
		$parents = explode(':',$_GET['parentID']);
		foreach($parents as $key => $parent) $parents[$key] = $o_main->db->escape($parent);
		$filter = " IN (".implode(",",$parents).")";
	} else {
		$filter = " = ".$o_main->db->escape($_GET['parentID']);
	}
}
$selecteds = explode(':',$_GET['selected']);
foreach($selecteds as $key => $item)
{
	list($cLevel, $id) = explode('_',$item);
	$selected[$id] = true;
}
$level = $_GET['level'];

$writeOut = "";
$s_table = "menulevel";
if($level == 0)
{
	$s_sql = "SELECT ml.id cid, ml.*, mlc.* FROM ".$s_table." ml JOIN ".$s_table."content mlc ON mlc.".$s_table."ID = ml.id AND mlc.languageID = ".$o_main->db->escape($_GET['s_default_output_language'])." WHERE ml.moduleID $filter AND ml.level = 0 AND ml.content_status < 2 ORDER BY ml.moduleID, ml.parentlevelID, ml.sortnr";
} else {
	$s_sql = "SELECT ml.id cid, ml.*, mlc.* FROM ".$s_table." ml JOIN ".$s_table."content mlc ON mlc.".$s_table."ID = ml.id AND mlc.languageID = ".$o_main->db->escape($_GET['s_default_output_language'])." WHERE ml.parentlevelID $filter AND ml.level = ".$o_main->db->escape($level)." AND ml.content_status < 2 ORDER BY ml.moduleID, ml.parentlevelID, ml.sortnr";
}

$module_setting = array();
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0)
foreach($o_query->result_array() as $v_row)
{
	if(!isset($module_setting[$v_row['moduleID']]))
	{
		$moduledata = array();
		$o_module = $o_main->db->query('SELECT * FROM moduledata WHERE id = ?', array($v_row['moduleID']));
		if($o_query && $o_query->num_rows()>0) $moduledata = $o_module->row_array();
		
		include(__DIR__."/../../../../".$moduledata['name']."/input/settings/tables/menulevel.php");
		$module_setting[$v_row['moduleID']] = $prefieldInList;
	}
	if($module_setting[$v_row['moduleID']] == '') $module_setting[$v_row['moduleID']] = 'levelname';
	if($level > 0)
	{
		$s_sql = "SELECT ml.id cid, ml.*, mlc.* FROM ".$s_table." ml JOIN ".$s_table."content mlc ON mlc.".$s_table."ID = ml.id AND mlc.languageID = ".$o_main->db->escape($s_default_output_language)." WHERE ml.id = ".$o_main->db->escape($v_row['parentlevelID'])." AND ml.content_status < 2 LIMIT 1";
		$s_parent_name = '';
		$o_find = $o_main->db->query($s_sql);
		if($o_find && $o_row = $o_find->row()) $s_parent_name = $o_row->$module_setting[$v_row['moduleID']];
	}
	if($writeOut != "")
	{
		$writeOut .= ";";
	}
	$writeOut .= (($multi or isset($_GET['multi'])) ? $v_row['parentlevelID'].'_' : '').$v_row['id'].":".($multi ? $s_parent_name.' - ' : '').(($v_row['parentlevelID']==0 && $_GET['menumodulemultiselect']==1) ? $v_menues[$v_row['moduleID']]." - " : "").$v_row[$module_setting[$v_row['moduleID']]].":".($selected[$v_row['id']] ? 1 : 0);
}

if($writeOut == "")
{
	$writeOut = "NONE";
}

print $writeOut;
?>