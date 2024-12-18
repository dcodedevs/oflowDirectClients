<?php
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
$parentID = $_GET['parentID'];
$level = $_GET['level'];
$return = "";
$table = "menulevel";
if($level == 0)
{
	$sql = "SELECT $table.id cid, $table.*, {$table}content.* FROM $table JOIN {$table}content ON {$table}content.{$table}ID = $table.id AND {$table}content.languageID = ".$o_main->db->escape($_GET['s_default_output_language'])." WHERE $table.moduleID = ".$o_main->db->escape($parentID)." AND $table.level = '0' AND $table.content_status < 2";
} else {
	$sql = "SELECT $table.id cid, $table.*, {$table}content.* FROM $table JOIN {$table}content ON {$table}content.{$table}ID = $table.id AND {$table}content.languageID = ".$o_main->db->escape($_GET['s_default_output_language'])." WHERE $table.parentlevelID = ".$o_main->db->escape($parentID)." AND $table.level = ".$o_main->db->escape($level)." AND $table.content_status < 2";
}
$row = array();
$o_query = $o_main->db->query($sql);
if($o_query && $o_query->num_rows()>0) $row = $o_query->row_array();

$moduledata = array();
$o_module = $o_main->db->query("SELECT * FROM moduledata WHERE id = ?", array($row['moduleID']));
if($o_module && $o_module->num_rows()>0) $moduledata = $o_module->row_array();

include(__DIR__."/../../../../".$moduledata['name']."/input/settings/tables/menulevel.php");
if($orderByField != "") $sql .= " ORDER BY $table.".$o_main->db_escape_name($orderByField);
if($prefieldInList == '') $prefieldInList = 'levelname';

$o_query = $o_main->db->query($sql);
if($o_query && $o_query->num_rows()>0)
foreach($o_query->result_array() as $row)
{
	if($return != "") $return .= ";";
	$return .= $row['cid'].":".$row[$prefieldInList].($row['content_status']==1?" - [inactive]":"");
}

if($return == "") $return = "NONE";
print $return;
?>