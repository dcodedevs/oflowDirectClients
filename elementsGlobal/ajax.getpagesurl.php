<?php
define('BASEPATH', realpath(__DIR__.'/../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');

$v_return = array();
$v_items = explode(",", $_POST['ids']);
$s_sql = 'SELECT p.id AS pid, p.menulevelID, pc.urlrewrite FROM pageID AS p LEFT OUTER JOIN pageIDcontent AS pc ON p.id = pc.pageIDID AND pc.languageID = ? WHERE p.id IN ?';
$o_query = $o_main->db->query($s_sql, array($_POST['languageID'], $v_items));
if($o_query && $o_query->num_rows()>0)
{
	foreach($o_query->result() as $o_row)
	{
		$v_return[$o_row->pid] = array($o_row->pid, $o_row->menulevelID, $o_row->urlrewrite);
	}
}
echo json_encode($v_return);
?>