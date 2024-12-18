<?php
define('ACCOUNT_PATH', realpath(__DIR__.'/../../../')); // this is modified to fit this files location
define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
require_once ACCOUNT_PATH . '/elementsGlobal/cMain.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	// Boom baby we a POST method
	$entityBody = file_get_contents('php://input');
	$callback = json_decode($entityBody, true);

	$s_sql = "INSERT INTO callbacklog SET log = ?";
	$o_query = $o_main->db->query($s_sql, array($entityBody));

	$response = $callback['response'];
	if($response['uid'] > 0){
		if($response['status'] == 0) {
			$s_sql = "UPDATE collecting_cases_claim_letter SET sending_status = 1, performed_action = 0, performed_date = NOW() WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($response['uid']));
		} else {
			$s_sql = "UPDATE collecting_cases_claim_letter SET sending_status = 2, sending_error_log = ? WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array(json_encode($callback), $response['uid']));
		}
	}
} else {
	$s_sql = "INSERT INTO callbacklog SET log = ?";
	$o_query = $o_main->db->query($s_sql, array(json_encode(array($_POST, $_GET))));
}
?>
