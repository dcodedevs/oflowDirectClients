<?php
//TODO: ALI security_check - direct_ajax
$return = array();
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
if(!function_exists("get_menulevel_parrents")) include(__DIR__."/fn_get_menulevel_parrents.php");

if(strlen($_POST['languageID'])>0)
{
	$langID = $_POST['languageID'];
} else {
	$row = array();
	$o_query = $o_main->db->query('SELECT languageID FROM language WHERE outputlanguage = 1 ORDER BY defaultOutputlanguage DESC, sortnr ASC');
	if($o_query && $o_query->num_rows()>0) $row = $o_query->row_array();
	$langID = $row['languageID'];
}

$contentName = trim($_POST['data']);

if(strpos($_POST['menulevelID'],'_')!==false) list($rest, $_POST['menulevelID']) = explode('_',$_POST['menulevelID']);
$row = array();
$o_query = $o_main->db->query('select levelname from menulevelcontent where menulevelID = ? AND languageID = ?', array($_POST['menulevelID'], $langID));
if($o_query && $o_query->num_rows()>0) $row = $o_query->row_array();
$levelName = trim(get_menulevel_parrents($_POST['menulevelID'], $langID).$row['levelname']);
$return['parsed'] = substr($contentName.($levelName==''?'':" - ".$levelName),0,70);

print json_encode($return);
