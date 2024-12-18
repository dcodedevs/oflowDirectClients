<?php
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
if(isset($_POST['id'], $_POST['table'], $_POST['moduleid'], $_POST['time']))
{
	$o_main->db->query("update sys_emailsendto est join sys_emailsend es on est.emailsend_id = es.id set est.status = 2, est.status_msg = ? where es.content_id = ? and es.content_table = ? and es.content_module_id = ? and es.send_on = STR_TO_DATE(?,'%d-%m-%Y %H:%i') and es.type = 1 and est.status = 0", array('Aborted', $_POST['id'], $_POST['table'], $_POST['moduleid'], $_POST['time']));
	?>OK<?php
} else {
	?>ERROR<?php
}
?>