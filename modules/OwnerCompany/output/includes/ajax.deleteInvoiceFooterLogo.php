<?php
$ownercompanyId = isset($_POST['ownercompanyId']) ? $o_main->db->escape_str($_POST['ownercompanyId']) : 0;
$imageUploadId = isset($_POST['imageUploadId']) ? $o_main->db->escape_str($_POST['imageUploadId']) : 0;

if ($ownercompanyId && $imageUploadId) {
    $entryData_sql = $o_main->db->query("SELECT * FROM ownercompany WHERE id = ?", array($ownercompanyId));
    if($entryData_sql && $entryData_sql->num_rows() > 0) $entryData = $entryData_sql->row();
    $imageList = json_decode($entryData->invoice_footer_logos);

    $newImageList = array();

    foreach ($imageList as $image) {
        if ($image[4] != $imageUploadId) {
            array_push($newImageList, $image);
        } else {
            foreach($image[1] as $single_file){
                unlink(__DIR__."/../../../../".$single_file);
                $parent_dir_name = dirname($single_file);
                rmdir(__DIR__."/../../../../".$parent_dir_name);
                $parent_dir_name2 = dirname($parent_dir_name);
                rmdir(__DIR__."/../../../../".$parent_dir_name2);
            }
        }
    }

    $newImageList = json_encode($newImageList);

    $o_main->db->query("UPDATE ownercompany SET invoice_footer_logos = '$newImageList' WHERE id = ?", array($ownercompanyId));
}
?>
