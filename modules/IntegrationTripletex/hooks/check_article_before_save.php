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

    // External account list
    $external_accounts_list = $api->get_account_list();

    // Params
    $article_id = $data['id'] ? $data['id'] : 0;
    if ($article_id) {
        $sql = "SELECT * FROM article WHERE id = ?";
        $o_query = $o_main->db->query($sql, array($article_id));
        $article_data = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();
    }
    $account = $data['account'] ? $data['account'] : 0;
    $vat = $data['vat'] ? $data['vat'] : 0;
    $article_code = $data['articleCode'] ? $data['articleCode'] : 0;
    $name = $data['name'];
    $costWithoutVat = $data['costWithoutVat'];
    $priceWithoutVat = $data['priceWithoutVat'];

    if (!$account) return array(
        'error' => true,
        'message' => 'No account specified'
    );

    if($external_accounts_list['error'] != "") {
        return array(
            'error' => true,
            'message' => $external_accounts_list['error']
        );
    }
    // Find account in external list
    $external_account = $external_accounts_list['by_number'][$account];

    if (!$external_account) return array(
        'error' => true,
        'message' => 'Account not found in Tripletex'
    );

    if ($external_account['vatType']['id']!= $vat) return array(
        'error' => true,
        'message' => 'Account in Tripletex has different VAT code'
    );

    // Article code check
    if ($article_code) {
        $external_product = $api->get_product_by_number($article_code);
        if ($external_product['id']) {
            if ($external_product['id'] != $article_data['external_sys_id']) {
                return array(
                    'error' => true,
                    'message' => 'Article code is used by other product in Tripletex (id: ' . $external_product['id'] . ', name: ' . $external_product['name'] .')'
                );
            }
        }
    }

    // Save product
    $save_product_data = array(
        'name' => $name,
        'account' => $account,
        'vat' => $vat,
        'priceWithoutVat' => $priceWithoutVat,
        'costWithoutVat' => $costWithoutVat
    );

    if ($article_code) {
        $save_product_data['number'] = $article_code;
    }

    if ($article_data['external_sys_id']) {
        $save_product_data['id'] = $article_data['external_sys_id'];
    }

    $product = $api->save_product($save_product_data);

    if ($product['error']) {
        return array(
            'error' => true,
            'message' => $product['message']
        );
    }

    return array(
        'error' => false,
        'product_data' => $product
    );
}
?>
