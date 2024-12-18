<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);
ini_set('max_execution_time', 120);

if(isset($_POST['submitImportData'])) {
	$s_sql = "select * from file_links where content_table = 'offer_pdf'";
	$o_query = $o_main->db->query($s_sql);
	$file_links = $o_query ? $o_query->result_array() : array();
	$updated = 0;
	foreach($file_links as $file_link){
		$s_sql = "select * from offer_pdf where id = ?";
		$o_query = $o_main->db->query($s_sql, array($file_link['content_id']));
		$offer_pdf = $o_query ? $o_query->row_array() : array();
		if($offer_pdf){
			if($file_link['filepath'] != $offer_pdf['file']) {
				$s_sql = "UPDATE file_links SET filepath = ? WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($offer_pdf['file'], $file_link['id']));
				if($o_query){
					$updated++;
				}
			}
		}
	}
	echo $updated." offer links updated";
}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >
		<div class="formRow submitRow">
			<input type="submit" name="submitImportData" value="Fix offer links">
		</div>
	</form>
</div>
