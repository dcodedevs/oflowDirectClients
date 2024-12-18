<?php
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
if(isset($_POST['action']) and $_POST['action'] == 'move')
{
	$o_query = $o_main->db->query('select id from sys_tag where id = ?', array($_POST['undertagid']));
	if($o_query && $o_query->num_rows()>0)
		$o_main->db->query("update sys_tag set parentID = ? where id = ?", array($_POST['undertagid'], $_POST['selectedtagid']));
} else {
	include(__DIR__."/fn_tags_print_selection.php");
	tags_print_selection(0,0,$_GET['className'],'move',$_GET['selectedtagid'],$_GET['s_default_output_language']);
}
?>