<?php
/**
 * Api Call Wrapper
 * version 1.0
 * author - Rinalds
 */
class ApiCallDriver {
    function __construct($base_url) {
        $this->base_url = $base_url;
        $this->auth_token = isset($auth_token) ? $auth_token: '';
        $this->headers = array(
            'Content-Type: application/json',
        );
    }

    function set_auth_token($auth_token) {
        array_push($this->headers, 'Authorization: Bearer ' . $auth_token);
        return $this->headers;
    }

    function get($path, $params = FALSE) {
        $curl = curl_init();
        $get_params = $params ? '?' . http_build_query($params) : '';
        curl_setopt($curl,CURLOPT_URL, $this->base_url . $path . $get_params);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        $response = curl_exec($curl);
        $response_decoded = json_decode(trim($response), true);
        return $response_decoded;

    }
    function get_file($path, $params = FALSE) {
        $curl = curl_init();
        $get_params = $params ? '?' . http_build_query($params) : '';
        curl_setopt($curl,CURLOPT_URL, $this->base_url . $path . $get_params);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        $response = curl_exec($curl);
        $response_decoded = trim($response);
        return $response_decoded;
    }

    function post($path, $params) {
        $curl = curl_init();
        $params = json_encode($params);
        curl_setopt($curl,CURLOPT_URL, $this->base_url . $path);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        $response = curl_exec($curl);
        $response_decoded = json_decode(trim($response), true);
        return $response_decoded;
    }

    function put($path, $params) {
        $curl = curl_init();
        $params = json_encode($params);
        var_dump($params);
        curl_setopt($curl,CURLOPT_URL, $this->base_url . $path);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        $response = curl_exec($curl);
        $response_decoded = json_decode(trim($response), true);
        return $response_decoded;
    }

    function patch($path, $params) {
        $curl = curl_init();
        $params = json_encode($params);
        curl_setopt($curl,CURLOPT_URL, $this->base_url . $path);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        $response = curl_exec($curl);
        $httpInfo = curl_getinfo($curl);
        if($httpInfo['http_code'] >= 400){
            $response_decoded = json_decode(trim($response), true);
            return $response_decoded;
        } else {
            return 1;
        }
    }
    function delete($path) {
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL, $this->base_url . $path);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        $response = curl_exec($curl);
        $response_decoded = json_decode(trim($response), true);
        return $response_decoded;
    }
}

?>
