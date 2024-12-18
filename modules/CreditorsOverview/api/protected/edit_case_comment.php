<?php
$_POST = $v_data['params']['post'];

$transaction_id = $_POST['transaction_id'];
$comment_id = $_POST['comment_id'];
$comment_text = $_POST['comment'];
$username= $v_data['params']['username'];
$languageID = 'no';

$s_sql = "SELECT * FROM collecting_cases_comments WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($comment_id));
$comment = $o_query ? $o_query->row_array() : array();

$sql = "SELECT * FROM creditor_transactions WHERE id = '".$o_main->db->escape_str($transaction_id)."'";
$o_query = $o_main->db->query($sql);
$transactionData = $o_query ? $o_query->row_array() : array();
if($transactionData){
	if($_POST['action'] == "delete"){
		$s_sql = "DELETE FROM collecting_cases_comments WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($comment['id']));
		if($o_query){
			$v_return['status'] = 1;
		} else {
		    $v_return['error'] = 'Error deleting comment';
		}
	} else {
	    $s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
	    $o_query = $o_main->db->query($s_sql, array($transactionData['creditor_id']));
	    $creditor = ($o_query ? $o_query->row_array() : array());

	    ob_start();
	    include(__DIR__."/../../output/languagesOutput/default.php");
	    if(is_file(__DIR__."/../../output/languagesOutput/".$languageID.".php")){
	        include(__DIR__."/../../output/languagesOutput/".$languageID.".php");
	    }
		if($_POST['output_form_submit']) {
			if($comment) {
				$s_sql = "UPDATE collecting_cases_comments SET comment = '".$o_main->db->escape_str($comment_text)."', updated = NOW(), updatedBy = '".$o_main->db->escape_str($username)."' WHERE id = '".$o_main->db->escape_str($comment['id'])."'";
				$o_query = $o_main->db->query($s_sql);
			} else {
				$s_sql = "INSERT INTO collecting_cases_comments SET comment = '".$o_main->db->escape_str($comment_text)."', created = NOW(), createdBy = '".$o_main->db->escape_str($username)."', transaction_id = '".$o_main->db->escape_str($transactionData['id'])."'";
				$o_query = $o_main->db->query($s_sql);
			}
			if($o_query){
			    $v_return['status'] = 1;
			} else {
			    $v_return['error'] = 'Error updating comments';
			}
		} else {
			$v_return['status'] = 1;
			$v_return['comment'] = $comment;
			$v_return['transactionData'] = $transactionData;
		}
	}
} else {
    $v_return['error'] = 'Missing case';
}
?>
