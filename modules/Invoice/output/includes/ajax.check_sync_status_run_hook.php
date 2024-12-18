<?php
$sql = "SELECT * FROM invoice_accountconfig";
$o_query = $o_main->db->query($sql);
$invoice_accountconfig = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();
if (!$invoice_accountconfig['path_check_sync_status']) {
    $return['error'] = 'No hook path configured';
} else {

    $hook_params = array(
        'invoice_id' => $_POST['id']
    );
    
    $hook_file = __DIR__ . '/../../../../' . $invoice_accountconfig['path_check_sync_status'];

    // print_r($hook_file);
    if (file_exists($hook_file)) {
        require_once $hook_file;
        if (is_callable($run_hook)) {
            $hook_result = $run_hook($hook_params);
            unset($run_hook);
        }
    }

    $return['data'] = $hook_result;
}
?>
