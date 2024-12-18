<?php
$projectFileId = $_POST['cid'] ? ($_POST['cid']) : 0;

if ($projectFileId) {
    $path_split = explode("/modules/",__DIR__);
    $account_path = $path_split[0];

    /*
    function delete_folder_files($folder_id){
        global $o_main;

        $result = false;
        $s_sql = "SELECT * FROM customer_files WHERE folder_id = ?";
        $o_query = $o_main->db->query($s_sql, array($folder_id));
        if($o_query && $o_query->num_rows() > 0){
            $entryFiles = $o_query->result_array();
            $deletedFiles = 0;
            foreach($entryFiles as $entryData){
                $o_query = $o_main->db->query("DELETE customer_files FROM customer_files WHERE id = ?", array($entryData['id']));
                if($o_query){
                    $deletedFiles++;
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
            if($deletedFiles == count($entryFiles)) {
                $result = true;
            }
        } else {
            $result = true;
        }
        return $result;
    }*/
    function check_if_files_exist($folder_id){
        global $o_main;

        $s_sql = "SELECT * FROM customer_files WHERE folder_id = ?";
        $o_query = $o_main->db->query($s_sql, array($folder_id));
        $customer_files = $o_query ? $o_query->result_array() : array();
        if(count($customer_files) > 0){
            return true;
        } else {
            return false;
        }
    }

    $files_exist = false;

    function delete_folder_subfolders($folder_id){
        global $o_main;
        global $files_exist;

        $s_sql = "SELECT * FROM customer_folders WHERE parent_id = ?";
        $o_query = $o_main->db->query($s_sql, array($folder_id));
        $folders = $o_query ? $o_query->result_array() : array();
        $result = false;
        if(count($folders) > 0){
            $deletedFolders = 0;
            foreach($folders as $folder) {
                $files_exist = check_if_files_exist($folder['id']);
                if(!$files_exist){
                    if(delete_folder_subfolders($folder['id'])) {
                        $o_query = $o_main->db->query("DELETE customer_folders FROM customer_folders WHERE id = ?", array($folder['id']));
                        if($o_query) {
                            $deletedFolders++;
                        }
                    }
                } else {
                    break;
                }
            }
            if($deletedFolders == count($folders)) {
                $result = true;
            }
        } else {
            $result = true;
        }
        return $result;
    }

    $s_sql = "SELECT * FROM customer_folders WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($projectFileId));
    $folder_info = $o_query ? $o_query->row_array() : array();
    if($folder_info){
        $files_exist = check_if_files_exist($folder_info['id']);
        if(!$files_exist){
            if(delete_folder_subfolders($folder_info['id'])) {
                $o_query = $o_main->db->query("DELETE customer_folders FROM customer_folders WHERE id = ?", array($folder_info['id']));
            }
        }
    }
    if($files_exist){
        echo $formText_CanNotDeleteFolderThereAreFilesInFolder_output;
    }
}
?>
