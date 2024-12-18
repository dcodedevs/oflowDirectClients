<?php
/*
    $parameters = array('userID', 'notificationID');
*/

function fw_set_notification_pressed($o_main, $parameters) {
    $userID = $parameters['userID'];
    $notificationID = $parameters['notificationID'];
    $result = 0;
    if($o_main != "" && $userID != "") {
        $s_sql = "UPDATE notificationcenter SET is_pressed = 1 WHERE receiver_user_id = ? AND id = ?";
        $o_query = $o_main->db->query($s_sql, array($userID, $notificationID));
        if($o_query){
            $result = 1;
        }

    }
    return $result;
}
?>
