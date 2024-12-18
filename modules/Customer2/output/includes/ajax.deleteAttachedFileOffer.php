<?php
$collectingorderId = $_POST['offerId'] ? ($_POST['offerId']) : 0;
$uid = $_POST['uid'] ? ($_POST['uid']) : 0;

if ($uid && $collectingorderId) {
    $path_split = explode("/modules/",__DIR__);
    $account_path = $path_split[0];
    $s_sql = "SELECT * FROM offer WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($collectingorderId));
    if($o_query && $o_query->num_rows() > 0){
        $entryData = $o_query->row_array();
        $files = json_decode($entryData['files_attached_to_email']);
        $newFiles = array();
        foreach($files as $file){
            $file_url = "";
            if($file[4] != $uid) {
                array_push($newFiles, $file);
            } else {
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
        $newFilesJson = json_encode($newFiles);
        $o_query = $o_main->db->query("UPDATE offer SET  files_attached_to_email = ? WHERE id = ?", array($newFilesJson, $collectingorderId));
    }
}
?>
