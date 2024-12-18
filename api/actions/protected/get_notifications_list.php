<?php
$function_file = __DIR__ . '/../../../fw/getynet_fw/modules/NotificationCenter/output/includes/fnc_fw_get_notifications.php';

if (file_exists($function_file)) {
    require_once $function_file;
    $notifications = fw_get_notifications($o_main, array(
        'userID' => $fw_api_user_data['userID'],
        'per_page' => $v_data['per_page'] ? $v_data['per_page'] : 50,
        'before_id' => $v_data['before_id'] ? $v_data['before_id'] : 0,
        'after_id' => $v_data['after_id'] ? $v_data['after_id'] : 0,
    ));

    $v_return['data'] = $notifications;
    
} else {
    $v_return['data'] = array();
    $v_return['error'] = true;
}