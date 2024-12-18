<?php
$run_hook = function($data) {
    global $o_main;
    global $variables;

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
    // Return object
    $return = array();
    $s_sql = "SELECT * FROM integrationtripletex";
    if($ownercompany_id > 0){
        $s_sql = "SELECT * FROM integrationtripletex WHERE ownerCompanyId = ?";
    }
    $o_query = $o_main->db->query($s_sql, array($ownercompany_id));
    $config = $o_query ? $o_query->row_array() : array();
    if($config){
        $s_sql = "SELECT * FROM ownercompany WHERE id = '".$o_main->db->escape_str($ownercompany_id)."'";
        $o_query = $o_main->db->query($s_sql);
        $ownercompany = $o_query ? $o_query->row_array() : array();

        $external_products = $api->get_products();
        $external_account_list = $api->get_account_list();

        foreach ($external_products as $product) {
            $external_sys_id = $product['id'];
            $sql = "SELECT * FROM article WHERE external_sys_id = '".$o_main->db->escape_str($external_sys_id)."'";
            $o_query = $o_main->db->query($sql);
            $article = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();
            $account_data = $external_account_list['by_id'][$product['account']['id']];
            if($article) {
                $s_sql = "UPDATE article SET
                updated = now(),
                updatedBy= '".$o_main->db->escape_str($variables->loggID)."',
                name= '".$o_main->db->escape_str($product['name'])."',
                costPrice = '".$o_main->db->escape_str($product['costExcludingVatCurrency'])."',
                price = '".$o_main->db->escape_str($product['priceExcludingVatCurrency'])."',
                SalesAccountWithVat = '".$o_main->db->escape_str($account_data['number'])."',
                VatCodeWithVat = '".$o_main->db->escape_str($product['vatType']['id'])."',
                articleCode =  '".$o_main->db->escape_str($product['number'])."',
                company_product_set_id = '".$o_main->db->escape_str($ownercompany['company_product_set_id'])."'
                WHERE id = '".$o_main->db->escape_str($article['id'])."'";
                $o_query = $o_main->db->query($s_sql);
            } else {
                $s_sql = "INSERT INTO article SET
                created = now(),
                createdBy= '".$o_main->db->escape_str($variables->loggID)."',
                name= '".$o_main->db->escape_str($product['name'])."',
                costPrice = '".$o_main->db->escape_str($product['costExcludingVatCurrency'])."',
                price = '".$o_main->db->escape_str($product['priceExcludingVatCurrency'])."',
                SalesAccountWithVat = '".$o_main->db->escape_str($account_data['number'])."',
                VatCodeWithVat = '".$o_main->db->escape_str($product['vatType']['id'])."',
                articleCode =  '".$o_main->db->escape_str($product['number'])."',
                external_sys_id = '".$o_main->db->escape_str($external_sys_id)."',
                company_product_set_id = '".$o_main->db->escape_str($ownercompany['company_product_set_id'])."'";
                $o_query = $o_main->db->query($s_sql);
            }
            if($o_query){
                $return['successully_imported'][] = $external_sys_id;
            } else {
                $return['failed_to_import'][] = $external_sys_id;
            }
        }
    }

    return $return;
}
?>
