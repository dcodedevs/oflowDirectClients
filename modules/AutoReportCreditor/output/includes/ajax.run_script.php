<?php
if($_POST['cid'] > 0){
	$s_sql = "SELECT * FROM autoreportcreditor WHERE id = ? ORDER BY name";
	$o_query = $o_main->db->query($s_sql, array($_POST['cid']));
	$autoreportcreditor = $o_query ? $o_query->row_array() : array();
	if($autoreportcreditor) {
		include(__DIR__."/../../../../elementsCustomized/".$autoreportcreditor['scriptUrl']);
	}
}
