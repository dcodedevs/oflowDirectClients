<?php
include(__DIR__."/fn_tags_get_parents.php");
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
$v_output_languages = array();
$o_query = $o_main->db->query('SELECT languageID, name FROM language WHERE outputlanguage = 1 ORDER BY defaultOutputlanguage DESC, sortnr ASC');
if($o_query && $o_query->num_rows()>0)
foreach($o_query->result_array() as $row)
{
	$v_output_languages[$row['languageID']] = $row['name'];
}
if(isset($_POST['add']))
{
	$type = 0; //not approved
	
	$o_main->db->query("insert into sys_tag(id, created, parentID, setID, name, `type`) values (NULL, NOW(), ?, ?, ?, ?);", array($_POST['tagparent'], $_POST['setID'], $_POST['tagname_'.$_POST['s_default_output_language']], $type));
	$lastID = $o_main->db->insert_id();
	foreach($v_output_languages as $langID => $value)
	{
		$o_main->db->query("update sys_tag set sortnr = id where id = ?", array($lastID));
		$o_main->db->query("insert into sys_tagcontent(id, sys_tagID, languageID, tagname) values (NULL, ?, ?, ?)", array($lastID, $langID, $_POST['tagname_'.$langID]));
	}
	$name = $_POST['tagname_'.$_POST['s_default_output_language']];
	$parentID = $_POST['tagparent'];
} else {
	$row = array();
	$o_query = $o_main->db->query("SELECT t.id, t.parentID, IF(tc.tagname<>'',tc.tagname,t.name) name FROM sys_tag t JOIN sys_tagcontent tc ON tc.sys_tagID = t.id and tc.languageID = ? where t.id = ?", array($_POST['s_default_output_language'], $_POST['tagid']));
	if($o_query && $o_query->num_rows()>0) $row = $o_query->row_array();
	$lastID = $row['id'];
	$name = $row['name'];
	$parentID = $row['parentID'];
}
if(isset($lastID))
{
	$parents = tags_get_parents($parentID, $_POST['s_default_output_language']);
	print json_encode(array('id'=>$lastID,'name'=>($parents==""?'':$parents.': ').$name,'pid'=>$parentID));
}
?>