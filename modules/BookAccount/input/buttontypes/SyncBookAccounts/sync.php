<?php
$o_query = $o_main->db->get('bookaccount_accountconfig');
$bookaccount_accountconfig = $o_query ? $o_query->row_array() : array();

$integration = $bookaccount_accountconfig['integration'];
$integration_file = __DIR__ . '/../../../../'. $integration .'/internal_api/load.php';

if (file_exists($integration_file)) {
    require_once $integration_file;
    if (class_exists($integration)) {
        if ($api) unset($api);
        $api = new $integration(array(
            'o_main' => $o_main
        ));
    }

    $account_list = $api->get_accounts_list();

    if (count($account_list)) {
        // Empty table
        $o_main->db->truncate('bookaccount');

        foreach($account_list as $account) {
            $o_main->db->insert('bookaccount', array(
                'moduleID' => $moduleID,
                'created' => date('Y-m-d H:i:s'),
                'createdBy' => $variables->loggID,
                'accountNr' => $account['code'],
                'name' => $account['name']
            ));
        }

        echo count($account_list) . ' ' . $formText_AccountsSynced_output;
    } else {
        echo $formText_EmptyAccountListReturn_output;
    }
}
else {
    echo $formText_InvalidIntegrationSpecified_output;
}
?>
