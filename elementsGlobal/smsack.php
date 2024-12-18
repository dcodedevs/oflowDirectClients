<?php
define('BASEPATH', realpath(__DIR__.'/../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');

if($_GET['StatusCode'] == 1)
{
	$o_main->db->query('UPDATE sys_smssendto SET status = 2, perform_time = NOW(), response = ? WHERE id = ?', array(json_encode($_GET), $_GET['BatchID']));
} else if($_GET['StatusCode'] == 2) {
	$o_result = $o_main->db->query('UPDATE sys_smssendto SET status = 3, perform_time = NOW(), status_msg = ?, response = ? WHERE id = ?', array('Part of message was sent', json_encode($_GET), $_GET['BatchID']));
	if(!$o_result)
	{
		$o_main->db->query('UPDATE sys_smssendto SET status = 3, perform_time = NOW(), status_message = ?, response = ? WHERE id = ?', array('Part of message was sent', json_encode($_GET), $_GET['BatchID']));
	}
} else {
	$o_result = $o_main->db->query('UPDATE sys_smssendto SET status = 3, perform_time = NOW(), status_msg = ?, response = ? WHERE id = ?', array('Message was not sent', json_encode($_GET), $_GET['BatchID']));
	if(!$o_result)
	{
		$o_result = $o_main->db->query('UPDATE sys_smssendto SET status = 3, perform_time = NOW(), status_message = ?, response = ? WHERE id = ?', array('Message was not sent', json_encode($_GET), $_GET['BatchID']));
	}
}
echo 'OK';
?>