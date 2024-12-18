<?php 
$creditor_id = $v_data['params']['creditor_id'] ?? 0;
$username = $v_data['params']['username'] ?? '';
$_POST = $v_data['params']['post'] ?? array();

$s_sql = "SELECT creditor.id, creditor.companyname, creditor.invoicelogo FROM creditor
WHERE creditor.id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_id));
$selected_creditor = ($o_query ? $o_query->row_array() : array());
if($selected_creditor) {
    if($_POST['action'] == "remove") {
        $images = json_decode($selected_creditor['invoicelogo'], true);
        foreach($images as $image){
            foreach($image[1] as $single_image){
                unlink(ACCOUNT_PATH."/".$single_image);
            }
        }
        $s_sql = "UPDATE creditor SET invoicelogo = '' WHERE creditor.id = ?";
        $o_query = $o_main->db->query($s_sql, array($creditor_id));
    } else {
        $moduleID = '110';
        $fwaFileuploadConfigs = array(
            array (
            'module_folder' => 'CreditorsOverview', // module id in which this block is used
            'id' => 'articleimageeuploadpopup',
            'upload_type'=>'image',
            'content_table' => 'creditor',
            'content_field' => 'invoicelogo',
            'content_id' => $creditor_id,
            'content_module_id' => $moduleID, // id of module
            'dropZone' => 'block',
            'callbackAll' => 'callBackOnUploadAll',
            'callbackStart' => 'callbackOnStart',
            'callbackDelete' => 'callbackOnDelete',
            'reupload' => 1
            )
        );
        foreach($fwaFileuploadConfigs as $fwaFileuploadConfig) {
            $fieldName = $fwaFileuploadConfig['id'];
            include( __DIR__ . "/fileupload_popup/contentreg.php");
        }
    }
}
?>