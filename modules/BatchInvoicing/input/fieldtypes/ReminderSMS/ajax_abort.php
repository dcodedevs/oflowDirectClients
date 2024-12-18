<?php
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
if(isset($_POST['id'], $_POST['table'], $_POST['moduleid'], $_POST['time']))
{
	$o_main->db->query("update sys_smssendto sst join sys_smssend ss on sst.smssend_id = ss.id set sst.status = 3, sst.status_message = 'Aborted' where ss.content_id = ? and ss.content_table = ? and ss.content_module_id = ? and ss.send_on = STR_TO_DATE(?,'%d-%m-%Y %H:%i') and sst.status = 0", array($_POST['id'], $_POST['table'], $_POST['moduleid'], $_POST['time']));
	?>OK<?php
} else {
	?>ERROR<?php
}
?>