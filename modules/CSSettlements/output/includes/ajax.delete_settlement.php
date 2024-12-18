<?php
$settlement_id = $_POST['settlement_id'];
if($settlement_id > 0){

    $sql = "SELECT * FROM cs_settlement cs WHERE cs.content_status < 2 ORDER BY cs.date DESC, cs.id DESC";
    $o_query = $o_main->db->query($sql);
    $last_settlement = $o_query ? $o_query->row_array() : array();
	if($last_settlement['id'] == $settlement_id) {
	    $s_sql = "DELETE FROM cs_settlement WHERE id = '".$o_main->db->escape_str($settlement_id)."'";
	    $o_query = $o_main->db->query($s_sql);
	    if($o_query) {
	        $s_sql = "UPDATE cs_mainbook_voucher SET settlement_id = null WHERE settlement_id = '".$o_main->db->escape_str($settlement_id)."'";
	        $o_query = $o_main->db->query($s_sql);
	    }
	}
}
?>
