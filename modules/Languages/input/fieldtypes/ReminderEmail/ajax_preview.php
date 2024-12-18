<?php
$choosenAdminLang = $_POST['languageID'];
$extradir = __DIR__."/../../../";
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
include(__DIR__."/../../includes/readInputLanguage.php");

if(isset($_POST['id'], $_POST['table'], $_POST['moduleid'], $_POST['time']))
{
	if(isset($_POST['type'])) $type = $_POST['type'];
	else $type = 1;
	
	$row = array();
	$o_query = $o_main->db->query("select * from sys_emailsend where content_id = ? and content_table = ? and content_module_id = ? and send_on = STR_TO_DATE(?,'%d-%m-%Y %H:%i') and type = ?", array($_POST['id'], $_POST['table'], $_POST['moduleid'], $_POST['time'], $type));
	if($o_query && $o_query->num_rows()>0) $row = $o_query->row_array();
	?><div style="border-bottom:1px solid #e9e9e9; padding-bottom:10px; margin-bottom:20px;"><b><?php echo $row['subject'];?></b></div>
	<div style="background:#fff;"><?php echo $row['text'];?></div><br/><br/><?php
}
?>