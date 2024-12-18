<?php
$function_file = __DIR__ . '/../../../fw/getynet_fw/modules/NotificationCenter/output/includes/fnc_fw_set_notification_pressed.php';

if (file_exists($function_file)) {
    require_once $function_file;
    $result = fw_set_notification_pressed($o_main, array(
        'userID' => $fw_api_user_data['userID'],
        'notificationID' => $v_data['id'],
    ));

    $v_return['data'] = $result;
    
} else {
    $v_return['data'] = array();
    $v_return['error'] = true;
}