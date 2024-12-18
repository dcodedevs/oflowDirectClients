<?php
/*
    $parameters = array('userID');
*/

function fw_set_notifications_seen($o_main, $parameters) {
    $userID = $parameters['userID'];
    $result = 0;
    if($o_main != "" && $userID != "") {
        $s_sql = "UPDATE notificationcenter SET is_seen = 1 WHERE notificationcenter.receiver_user_id = ?";
        $o_query = $o_main->db->query($s_sql, array($userID));
        if($o_query){
            $result = 1;
        }

    }
    return $result;
}
?>
