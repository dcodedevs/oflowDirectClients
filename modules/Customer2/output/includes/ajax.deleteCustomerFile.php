<?php
$projectFileId = $_POST['cid'] ? ($_POST['cid']) : 0;

if ($projectFileId) {
    $path_split = explode("/modules/",__DIR__);
    $account_path = $path_split[0];
    $s_sql = "SELECT * FROM customer_files WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($projectFileId));
    if($o_query && $o_query->num_rows() > 0){
        $entryData = $o_query->row_array();
        if($entryData){
            $o_query = $o_main->db->query("DELETE customer_files FROM customer_files WHERE id = ?", array($projectFileId));
            if($o_query){
                $file_url = "";
				$files = json_decode($entryData['file']);
				foreach($files as $file){
					foreach($file[1] as $file_version){
						$file_url = $account_path . '/'. $file_version;
				        if (is_file($file_url)) {
				            unlink($file_url);
				            rmdir(dirname($file_url));
				        }
					}
				}
				if($file_url != ""){
					$parts = explode('/', dirname($file_url));
					$last = array_pop($parts);
					rmdir(implode('/', $parts));
				}
            }
        }
    }
}
?>
