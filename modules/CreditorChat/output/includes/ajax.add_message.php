<?php 
$creditor_id = $_POST['creditor_id'] ?? 0;
$collecting_company_case_id = $_POST['collecting_company_case_id'] ?? 0;
$message = $_POST['message'] ?? '';

if($message != ""){
    $fwaFileuploadConfigs = array(
        array (
          'module_folder' => 'CreditorChat', // module id in which this block is used
          'id' => 'articleimageeuploadpopup',
          'upload_type'=>'file',
          'content_table' => 'creditor_collecting_company_chat',
          'content_field' => 'files',
          'content_id' => $cid,
          'content_module_id' => $moduleID, // id of module
          'dropZone' => 'block',
          'callbackAll' => 'callBackOnUploadAll',
          'callbackStart' => 'callbackOnStart',
          'callbackDelete' => 'callbackOnDelete'
        ),
        array (
          'module_folder' => 'CreditorChat', // module id in which this block is used
          'id' => 'articleinsfileupload',
          'upload_type' => 'image',
          'content_table' => 'creditor_collecting_company_chat',
          'content_field' => 'screenshot',
          'content_id' => $cid,
          'content_module_id' => $moduleID, // id of module
          'dropZone' => 'block',
          'callbackAll' => 'callBackOnUploadAll',
          'callbackStart' => 'callbackOnStart',
          'callbackDelete' => 'callbackOnDelete'
        )
    );

    $s_sql = "SELECT creditor.id, creditor.companyname FROM creditor
    JOIN collecting_company_cases ccc ON ccc.creditor_id = creditor.id
    WHERE creditor.id = ? AND ccc.id = ?";
    $o_query = $o_main->db->query($s_sql, array($creditor_id, $collecting_company_case_id));
    $selected_creditor = ($o_query ? $o_query->row_array() : array());
    if($selected_creditor) {
        $s_sql = "INSERT INTO creditor_collecting_company_chat SET created= NOW(),createdBy= ?, moduleID=?, message = ?, message_from_oflow = 1, creditor_id = ?, collecting_company_case_id = ?";
        $o_query = $o_main->db->query($s_sql, array($variables->loggID, $moduleID, $message, $selected_creditor['id'], $collecting_company_case_id));
        if($o_query){
            $company_chat_id = $o_main->db->insert_id();
            foreach($fwaFileuploadConfigs as $fwaFileuploadConfig) {
                $fieldName = $fwaFileuploadConfig['id'];
                $fwaFileuploadConfig['content_id'] = $company_chat_id;
                include( __DIR__ . "/fileupload_popup/contentreg.php");
            }
        }
    }
}
?>