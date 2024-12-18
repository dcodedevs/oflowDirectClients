<?php
if($_POST['cid'] > 0){
	$s_sql = "SELECT * FROM autoreportcreditor WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($_POST['cid']));
	$autoreportcreditor = $o_query ? $o_query->row_array() : array();
	if($autoreportcreditor) {
		require_once(__DIR__."/../../../../elementsCustomized/".$autoreportcreditor['scriptUrl']);


		if(!$_POST['closed']) {
			$sql = "SELECT autoreportcreditor_lines.* FROM autoreportcreditor_lines
			LEFT OUTER JOIN autoreportcreditor_report ON autoreportcreditor_report.id = autoreportcreditor_lines.autoreportcreditor_report_id
		 	WHERE autoreportcreditor_lines.autoreportcreditor_id = ? AND IFNULL(autoreportcreditor_report.created, '0000-00-00') = '0000-00-00'";
			$result = $o_main->db->query($sql, array($autoreportcreditor['id']));
			$autoreportcreditor_lines = $result ? $result->result_array(): array();
			if(count($autoreportcreditor_lines) > 0) {
				$s_sql = "INSERT INTO autoreportcreditor_report SET created = NOW(), autoreportcreditor_id = ?";
				$o_query = $o_main->db->query($s_sql, array($autoreportcreditor['id']));
				if($o_query) {
					$report_id = $o_main->db->insert_id();
					if($report_id > 0) {
						foreach($autoreportcreditor_lines as $autoreportcreditor_line) {
							$s_sql = "UPDATE autoreportcreditor_lines SET updated = NOW(), autoreportcreditor_report_id = ? WHERE id = ?";
							$o_query = $o_main->db->query($s_sql, array($report_id, $autoreportcreditor_line['id']));
						}
					}
				}
			}
		}
	}
}
