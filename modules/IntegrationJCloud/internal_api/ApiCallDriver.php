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
            'Content-Type: application/x-distribution',
        );
    }

    function set_auth_token($auth_token) {
        array_push($this->headers, 'Authorization: Basic ' . $auth_token);
        return $this->headers;
    }

    function get($path, $params = FALSE) {
        $curl = curl_init();
        $get_params = $params ? '?' . http_build_query($params) : '';
        curl_setopt($curl,CURLOPT_URL, $this->base_url . $path . $get_params);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        $response = curl_exec($curl);
        $response_decoded = trim($response);
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
        $params = $params;
        curl_setopt($curl, CURLOPT_URL, $this->base_url . $path);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params, JSON_UNESCAPED_UNICODE));
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        $response = curl_exec($curl);
        $response_decoded = trim($response);
		$responseInfo = curl_getinfo($curl);
        return $response_decoded;
    }

    function put($path, $params) {
        $curl = curl_init();
        $params = $params;
        $headers = $this->headers;
        $headers[] = "Content-Length: ".strlen($params);
        $this->headers = $headers;
        curl_setopt($curl, CURLOPT_URL, $this->base_url . $path);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_PUT, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params, JSON_UNESCAPED_UNICODE));
        $response = curl_exec($curl);
        $response_decoded = trim($response);
        return $response_decoded;
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
