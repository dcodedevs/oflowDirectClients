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

	$sql = "SELECT * FROM ownercompany WHERE id = ?";
    $o_query = $o_main->db->query($sql, array($ownercompany_id));
    $ownercompany = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();

    $external_products = $api->get_products();
    $external_account_list = $api->get_account_list();
    // Get local articles
    $sql = "SELECT * FROM article WHERE content_status = 0 AND (article_supplier_id is null OR article_supplier_id = 0) AND (company_product_set_id is null OR company_product_set_id = 0)";
    if($ownercompany['company_product_set_id'] > 0){
        $sql = "SELECT * FROM article WHERE content_status = 0 AND (article_supplier_id is null OR article_supplier_id = 0) AND company_product_set_id = ?";
    }
    $o_query = $o_main->db->query($sql, array($ownercompany['company_product_set_id']));
    $articles = $o_query && $o_query->num_rows() ? $o_query->result_array() : array();

    $article_errors = array();
    $error_count = 0;

    foreach ($articles as $article) {
        $article_errors[$article['id']] = array(
            'id' => $article['id'],
            'errors' => array()
        );

        if (!$article['external_sys_id']) {
            $suggested_external_products = $api->get_product_by_name($article['name']);
            $processed_suggested_external_products = array();
            foreach($suggested_external_products as $suggested_external_product) {
                $account_data = $external_account_list['by_id'][$suggested_external_product['account']['id']];
                $suggested_external_product['SalesAccountWithVat'] = $account_data['number'];
                $suggested_external_product['VatCodeWithVat'] = $suggested_external_product['vatType']['id'];
                $suggested_external_product['price'] = $suggested_external_product['priceExcludingVatCurrency'];
                $suggested_external_product['cost'] = $suggested_external_product['costExcludingVatCurrency'];

                $processed_suggested_external_products[] = $suggested_external_product;
            }
            $article_errors[$article['id']]['suggested_external_products'] = $processed_suggested_external_products;

            array_push($article_errors[$article['id']]['errors'], array(
                'message' => 'Not synced, missing external_sys_id'
            ));

            $error_count++;
        }

        if ($article['external_sys_id']) {
            $external_sys_id_found = false;
            $external_product = false;

            foreach ($external_products as $product) {
                if ($product['id'] == $article['external_sys_id']) {
                    $external_sys_id_found = true;
                    $external_product = $product;
                    break;
                }
            }

            if (!$external_sys_id_found) {
                array_push($article_errors[$article['id']]['errors'], array(
                    'message' => 'Locally stored external product id does not exist in Tripletex. Please contact DCode!'
                ));
                $error_count++;
            } else {
                // If accout number do not much
                $account_data = $external_account_list['by_id'][$external_product['account']['id']];
                if ($account_data['number'] != $article['SalesAccountWithVat']) {
                    array_push($article_errors[$article['id']]['errors'], array(
                        'message' => 'Different account number in Tripletex (' . $account_data['number'] . ')'
                    ));
                }

                // If vat do not match
                if ($external_product['vatType']['id'] != $article['VatCodeWithVat']) {
                    array_push($article_errors[$article['id']]['errors'], array(
                        'message' => 'Different VAT number in Tripletex (' . $external_product['vatType']['id'] . ')'
                    ));
                }

                // If vat do not match
                if ($external_product['number'] != $article['articleCode']) {
                    array_push($article_errors[$article['id']]['errors'], array(
                        'message' => 'Different article code (product number) in Tripletex (' . $external_product['number'] . ')'
                    ));
                }

            }
        }

    }

    return array(
        'error' =>  $error_count ? true : false,
        'message' => $error_count . ' errors found',
        'data' => $article_errors
    );
}
?>
