<?php
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');

$parentID = $_GET['parentID'];
$level = $_GET['level'];
$return = "";
$s_table = "menulevel";
if($level == 0)
{
	$sql = "SELECT $s_table.id cid, $s_table.*, {$s_table}content.* FROM $s_table JOIN {$s_table}content ON {$s_table}content.{$s_table}ID = $s_table.id AND {$s_table}content.languageID = ".$o_main->db->escape($_GET['s_default_output_language'])." WHERE $s_table.moduleID = ".$o_main->db->escape($parentID)." AND $s_table.level = 0 AND $s_table.content_status < 2";
} else {
	$sql = "SELECT $s_table.id cid, $s_table.*, {$s_table}content.* FROM $s_table JOIN {$s_table}content ON {$s_table}content.{$s_table}ID = $s_table.id AND {$s_table}content.languageID = ".$o_main->db->escape($_GET['s_default_output_language'])." WHERE $s_table.parentlevelID = ".$o_main->db->escape($parentID)." AND $s_table.level = ".$o_main->db->escape($level)." AND $s_table.content_status < 2";
}
$row = array();
$o_query = $o_main->db->query($sql);
if($o_query && $o_query->num_rows()>0) $row = $o_query->row_array();

$moduledata = array();
$o_module = $o_main->db->query('SELECT * FROM moduledata WHERE id = ?', array($row['moduleID']));
if($o_query && $o_query->num_rows()>0) $moduledata = $o_module->row_array();

include(__DIR__."/../../../../".$moduledata['name']."/input/settings/tables/menulevel.php");
if($orderByField != "") $sql .= " ORDER BY $s_table.".$orderByField;
if($prefieldInList == '') $prefieldInList = 'levelname';

$o_query = $o_main->db->query($sql);
if($o_query && $o_query->num_rows()>0)
{
	foreach($o_query->result() as $o_row)
	{
		if($return != "") $return .= ";";
		$return .= $o_row->cid.":".$o_row->$prefieldInList;
	}
}

if($return == "") $return = "NONE";
echo $return;
