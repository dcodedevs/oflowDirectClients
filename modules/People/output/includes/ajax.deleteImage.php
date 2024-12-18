<?php
$contentId = isset($_POST['cid']) ? ($_POST['cid']) : 0;
$imageUploadId = isset($_POST['imageUploadId']) ? ($_POST['imageUploadId']) : 0;

if ($contentId && $imageUploadId) {

    $s_sql = "SELECT * FROM contactperson WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($contentId));
    if($o_query && $o_query->num_rows() > 0){
        $entryData = $o_query->row_array();
        $imageList = json_decode($entryData['profileBannerImage']);
    }

    $newImageList = array();

    foreach ($imageList as $image) {
        if ($image[4] != $imageUploadId) {
            array_push($newImageList, $image);
        }
    }

    $newImageList = json_encode($newImageList);

    if($o_main->db->query("UPDATE contactperson SET profileBannerImage = ? WHERE id = ?", array($newImageList, $contentId))){
      echo json_encode(array("result" => 1));
    }
}
?>
