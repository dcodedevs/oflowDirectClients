<?php
/**
 * Api Call Wrapper
 * version 1.0
 * author - Rinalds
 */
class ApiCallDriver {
    function __construct($base_url, $params = array()) {
        $this->base_url = $base_url;
        $this->auth_token = $auth_token;
        $this->urlencoded = $params['urlencoded'] ? true : false;
        $this->bearer = $params['bearer'] ? true : false;
        $content_type = $this->urlencoded ? 'Content-Type: application/x-www-form-urlencoded' : 'Content-Type: application/json';
        $this->headers = array($content_type);
    }

    function set_auth_token($auth_token) {
        $auth_type = $this->bearer ? 'Bearer' : 'Basic';
        array_push($this->headers, 'Authorization: ' . $auth_type . ' ' . $auth_token);
        return $this->headers;
    }

    function get($path, $params = array()) {
        $curl = curl_init();
        $get_params = $params ? '?' . http_build_query($params) : '';
        curl_setopt($curl,CURLOPT_URL, $this->base_url . $path . $get_params);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
		var_dump($params,$this->headers);
        $response = curl_exec($curl);
        $response_decoded = json_decode(trim($response), true);
        return $response_decoded;

    }

    function post($path, $params = array()) {
        $curl = curl_init();
        $params = $this->urlencoded ? http_build_query($params) : json_encode($params);
        curl_setopt($curl,CURLOPT_URL, $this->base_url . $path);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
		// var_dump($params,$this->headers);
        $response = curl_exec($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        $response_decoded = json_decode(trim($response), true);
        return array($response_decoded, $httpcode);
    }

    function put($path, $params = array()) {
        $curl = curl_init();
        $params = $this->urlencoded ? http_build_query($params) : json_encode($params);
        curl_setopt($curl,CURLOPT_URL, $this->base_url . $path);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        $response = curl_exec($curl);
        $response_decoded = json_decode(trim($response), true);
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
