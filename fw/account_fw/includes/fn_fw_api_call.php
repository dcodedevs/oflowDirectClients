<?php
// fw_api_call probabbly should be moved to better place by Agris
// Added by Rinalds @ 28.08.2017
// Update by Rinalds @ 28.11.2017
if (!function_exists('fw_api_call')) {
    function fw_api_call($params, $create_account_access_token = false) {
        global $o_main;

        if ($create_account_access_token) {
            $o_query = $o_main->db->get('accountinfo');
            $accountinfo_data = $o_query ? $o_query->row_array() : array();
            $account_name = $accountinfo_data['accountname'];
            $account_password = $accountinfo_data['password'];

            if(!function_exists("APIconnectAccount")) include(__DIR__."/../../../modules/Languages/input/includes/APIconnect.php");
            $response = APIconnectAccount("account_authenticate", $account_name, $account_password, array());
            $response_decoded = json_decode($response, true);
            $token = $response_decoded['token'];

            $params['account_access_token'] = $token;
            $params['account_access_caller_account_name'] = $account_name;
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $params['api_url'],
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_POST => true,
            CURLOPT_POSTREDIR => 3,
            CURLOPT_POSTFIELDS => json_encode($params),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            )
        ));

        $resp = curl_exec($curl);

        return json_decode($resp, true);
    }
}
