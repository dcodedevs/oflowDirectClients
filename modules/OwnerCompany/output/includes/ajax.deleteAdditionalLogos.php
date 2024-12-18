<?php
$contentId = isset($_POST['cid']) ? ($_POST['cid']) : 0;

if ($contentId) {

    $s_sql = "SELECT * FROM ownercompany_logos WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($contentId));
    if($o_query && $o_query->num_rows() > 0){
        $entryData = $o_query->row_array();
        $imageList = json_decode($entryData['logo']);
    }

    if($o_main->db->query("DELETE FROM ownercompany_logos WHERE id = ?", array($contentId))){

        foreach ($imageList as $image) {
    		// delete file from ftp
    		foreach($image[1] as $single_file){
    			unlink(__DIR__."/../../../../".$single_file);
    			$parent_dir_name = dirname($single_file);
    			rmdir(__DIR__."/../../../../".$parent_dir_name);
    			$parent_dir_name2 = dirname($parent_dir_name);
    			rmdir(__DIR__."/../../../../".$parent_dir_name2);
    		}
        }
        echo json_encode(array("result" => 1));
    }
}
?>
