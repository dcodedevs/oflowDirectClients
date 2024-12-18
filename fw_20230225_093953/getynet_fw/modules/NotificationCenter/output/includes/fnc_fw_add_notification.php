<?php
/*
    to be updated later
*/

function fw_add_notification($o_main, $parameters) {
    $username = $parameters['username'];

    $text = $parameters['text'];
    $created_by_user_id = $parameters['receiver_user_id'];
    $receiver_user_id = $parameters['receiver_user_id'];
    $content_table = $parameters['content_table'];
    $content_id = $parameters['content_id'];
    $is_seen = $parameters['is_seen'];
    $is_pressed = $parameters['is_pressed'];
    $result = 0;
    if($o_main != "" && $username != "") {
        $s_sql = "INSERT INTO notificationcenter SET created=NOW(), createdBy=?, content_status = 0, text = ?, created_by_user_id = ?, receiver_user_id = ?, content_table = ?, content_id = ?, is_seen = ?, is_pressed = ?";
        $o_query = $o_main->db->query($s_sql, array($username, $text, $created_by_user_id, $receiver_user_id, $content_table, $content_id, $is_seen, $is_pressed));
        if($o_query){
            $result = 1;
        }

    }
    return $notifications;
}
?>
