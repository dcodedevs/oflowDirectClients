<?php
define('FRAMEWORK_DEBUG', FALSE);
define('ACCOUNT_PATH', realpath(__DIR__.'/../../')); // this is modified to fit this files location
define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
$v_tmp = explode("/",ACCOUNT_PATH);
$accountname = array_pop($v_tmp);

include(__DIR__.'/../../elementsGlobal/cMain.php');
$key = $_GET['key'];

if($key != ""){
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
        //check for ip from share internet
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    } elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        // Check for the Proxy User
        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    } else {
        $ip = $_SERVER["REMOTE_ADDR"];
    }


    $s_sql = "select * from file_links_log where ip = ? AND successful = 0 AND created BETWEEN  DATE_FORMAT(NOW(), '%Y-%m-%d %H:00:00')  AND  DATE_FORMAT(NOW(), '%Y-%m-%d %H:59:59')";
    $o_query = $o_main->db->query($s_sql, array($ip));
    $attempts = $o_query ? $o_query->result_array() : array();

    if(count($attempts) < 3){
        $s_sql = "INSERT INTO file_links_log SET key_used=?, successful = 0, ip = ?, created = NOW()";
        $o_query = $o_main->db->query($s_sql, array($key, $ip));
        if($o_query){
            $log_id = $o_main->db->insert_id();
            $s_sql = "select * from file_links where link_key = ?";
            $o_query = $o_main->db->query($s_sql, array($key));
            $key_item = $o_query ? $o_query->row_array() : array();
            if($key_item) {
                $s_sql = "UPDATE file_links_log SET successful = 1 WHERE id = ?";
                $o_query = $o_main->db->query($s_sql, array($log_id));

                $content_table = $key_item['content_table'];
                $content_id = $key_item['content_id'];

                $s_sql = "select * from ".$content_table." where id = ?";
                $o_query = $o_main->db->query($s_sql, array($content_id));
                $link_content = $o_query ? $o_query->row_array() : array();
                if($link_content){
                    $filename = __DIR__."/../../".$key_item['filepath'];
                    if(file_exists($filename)){
                        //Get file type and set it as Content Type
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        header('Content-Type: ' . finfo_file($finfo, $filename));
                        finfo_close($finfo);

                        //Use Content-Disposition: attachment to specify the filename
                        header('Content-Disposition: attachment; filename="'.basename($filename).'"');

                        //No cache
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate');
                        header('Pragma: public');

                        //Define file size
                        header('Content-Length: ' . filesize($filename));

                        ob_clean();
                        flush();
                        readfile($filename);
                        exit;
                    }
                }
            }
        }
    } else {
        echo "Too many wrong requests. You have been suspended for 1 hour.";
    }
}
?>
