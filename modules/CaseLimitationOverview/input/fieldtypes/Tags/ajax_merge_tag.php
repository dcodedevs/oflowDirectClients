<?php
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
if(isset($_POST['action']) and $_POST['action'] == 'merge')
{
	$o_query = $o_main->db->query('select id from sys_tag where id = ?', array($_POST['undertagid']));
	if($o_query && $o_query->num_rows()>0)
	{
		$o_main->db->query("update sys_tagrelation set tagID = ? where tagID = ?", array($_POST['undertagid'], $_POST['selectedtagid']));
		$o_main->db->query("delete from sys_tagcontent where sys_tagID = ?", array($_POST['selectedtagid']));
		$o_main->db->query("delete from sys_tag where id = ?", array($_POST['selectedtagid']));
	}
} else {
	include(__DIR__."/fn_tags_print_selection.php");
	tags_print_selection(0,0,$_GET['className'],'merge',$_GET['selectedtagid'],$_GET['s_default_output_language']);
}
