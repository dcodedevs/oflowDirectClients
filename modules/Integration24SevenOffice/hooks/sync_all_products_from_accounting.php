<?php
$run_hook = function($data) {
    global $o_main;
    global $variables;

    // Params
    $customer_id = $data['customer_id'];
    $ownercompany_id = $data['ownercompany_id'];
    // Load integration
    require_once __DIR__ . '/../internal_api/load.php';
    $api = new Integration24SevenOffice(array(
        'o_main' => $o_main,
        'ownercompany_id' => $ownercompany_id
    ));

    // Return object
    $return = array();
    $newArticles = 0;
    $updatedArticles = 0;

    foreach($products as $product) {
        $external_id = $product['id'];
        $name = $product['name'];
        $costPrice = $product['cost'];
        $price = $product['price'];


        $s_sql = "SELECT * FROM article WHERE external_sys_id = '".$o_main->db->escape_str($external_id)."'";
        $o_query = $o_main->db->query($s_sql);
        $article = $o_query ? $o_query->row_array() : array();
        if($article) {
            $s_sql = "UPDATE article SET updated = NOW(), name= '".$o_main->db->escape_str($name)."',
            costPrice = '".$o_main->db->escape_str($costPrice)."', price = '".$o_main->db->escape_str($price)."' WHERE id = '".$o_main->db->escape_str($article['id'])."'";
            $o_query = $o_main->db->query($s_sql);
            if($o_query) {
                $updatedArticles++;
            }
        } else {
            $s_sql = "INSERT INTO article SET created = NOW(), name= '".$o_main->db->escape_str($name)."',
            costPrice = '".$o_main->db->escape_str($costPrice)."', external_sys_id = '".$o_main->db->escape_str($external_id)."', price = '".$o_main->db->escape_str($price)."'";
            $o_query = $o_main->db->query($s_sql);
            if($o_query) {
                $newArticles++;
            }
        }
    }
    $return['new_articles'] = $newArticles;
    $return['updated_articles'] = $updatedArticles;
    return $return;
}
?>
