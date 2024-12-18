<?php
$run_hook = function($data) {
    global $o_main;
    $ownercompany_id = $data['ownercompany_id'];
    if(intval($ownercompany_id) == 0){
        $ownercompany_id = 1;
    }
    // Load integration
    require_once __DIR__ . '/../internal_api/load.php';

    $api = new IntegrationTripletex(array(
        'ownercompany_id' => $ownercompany_id,
        'o_main' => $o_main
    ));

    $session_data = $api->get_session_data();

    if ($session_data['id']) {
        // We assume that if we can get session data then we have
        // successfull connection to API
        return array(
            'error' => false,
        );
    } else {
        return array(
            'error' => true,
            'message' => 'Canceled! Issue accessing Tripletex API!'
        );
    }
}
?>
