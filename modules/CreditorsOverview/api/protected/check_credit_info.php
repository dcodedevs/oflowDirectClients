<?php
include(__DIR__."/../languagesOutput/default.php");

$customer_id = $v_data['params']['customer_id'];
$username = $v_data['params']['username'];
$limited_info = $v_data['params']['limited_info'];
$extended_info = $v_data['params']['extended_info'];

if($customer_id != "") {
    
    $sql = "SELECT * FROM customer WHERE id = ?";
    $o_query = $o_main->db->query($sql, array($customer_id));
    $customer_data = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();

    $org_nr = 0;
    if($customer_data){
        $org_nr = intval(trim($customer_data['publicRegisterId']));
    }
    if($org_nr > 0){
        $auth_token = "S5FAxERkIkNfFaWalNkut7em6"; // valid till 24.11.2024
        $headers = array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Token ' . $auth_token
        );

        $curl = curl_init();
        $params = array();
        $get_params = $params ? '?' . http_build_query($params) : '';
        curl_setopt($curl,CURLOPT_URL, "https://ppc.proff.no/CompanyCreditReport/".$org_nr. $get_params);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($curl);
        $httpcode2 = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $response_decoded = json_decode(trim($response), true);
        $v_return['company_info'] = $response_decoded;

        $call_type = 0;

        $curl = curl_init();
        $params = array();
        $get_params = $params ? '?' . http_build_query($params) : '';

        curl_setopt($curl, CURLOPT_URL, "https://ppc.proff.no/CreditRating/".$org_nr. $get_params);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $response_decoded = json_decode(trim($response), true);
        $v_return['customer_data'] = $customer_data;
        $v_return['credit_rating'] = $response_decoded;
        $v_return['httpcode'] = $httpcode;
        if($httpcode == 200){
            $curl = curl_init();
            $params = array();
            $get_params = $params ? '?' . http_build_query($params) : '';
            curl_setopt($curl,CURLOPT_URL, "https://ppc.proff.no/PaymentRemarks/".$org_nr. $get_params);
            curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($curl);
            $httpcode2 = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $response_decoded = json_decode(trim($response), true);
            $v_return['payment_remarks'] = $response_decoded;
            if($limited_info){
                if($httpcode == 200 && $httpcode2 == 200) {
                    $sql = "INSERT INTO creditor_credit_info_call SET created = NOW(), createdBy=?, creditor_id = ?, customer_id = ?, call_type = 0";
                    $o_query = $o_main->db->query($sql, array($username, $customer_data['creditor_id'], $customer_data['id']));
                }
            }    
        }    
        if($extended_info) { 
            if($httpcode == 200 && $httpcode2 == 200) {
                $curl = curl_init();
                $params = array();
                $get_params = $params ? '?' . http_build_query($params) : '';
                curl_setopt($curl,CURLOPT_URL, "https://ppc.proff.no/PaymentRemarkDetails/".$org_nr. $get_params);
                curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                $response = curl_exec($curl);
                $httpcode3 = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                $response_decoded = json_decode(trim($response), true);
                $v_return['payment_remark_details'] = $response_decoded;
                if($httpcode3 == 200) {
                    $sql = "INSERT INTO creditor_credit_info_call SET created = NOW(), createdBy=?, creditor_id = ?, customer_id = ?, call_type = 1";
                    $o_query = $o_main->db->query($sql, array($username, $customer_data['creditor_id'], $customer_data['id']));
                }
            }
        }
    } else {
        $v_return['error'] = $formText_MissingOrgNr_output;        
    }
} else {
    $v_return['error'] = $formText_MissingCustomer_output;
}


?>
