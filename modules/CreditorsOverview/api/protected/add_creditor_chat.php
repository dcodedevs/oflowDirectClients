<?php 
$creditor_id = $v_data['params']['creditor_id'] ?? 0;
$collecting_company_case_id= $v_data['params']['collecting_company_case_id'] ?? 0;
$message = $v_data['params']['message'] ?? '';
$username = $v_data['params']['username'] ?? '';
$_POST = $v_data['params']['post'] ?? array();

$s_sql = "SELECT creditor.id, creditor.companyname FROM creditor
JOIN collecting_company_cases ccc ON ccc.creditor_id = creditor.id
WHERE creditor.id = ? AND ccc.id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_id, $collecting_company_case_id));
$selected_creditor = ($o_query ? $o_query->row_array() : array());
if($selected_creditor) {
    $moduleID = '10016';
    $fwaFileuploadConfigs = array(
        array (
          'module_folder' => 'CreditorChat', // module id in which this block is used
          'id' => 'articleimageeuploadpopup',
          'upload_type'=>'file',
          'content_table' => 'creditor_collecting_company_chat',
          'content_field' => 'files',
          'content_id' => 0,
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
          'content_id' => 0,
          'content_module_id' => $moduleID, // id of module
          'dropZone' => 'block',
          'callbackAll' => 'callBackOnUploadAll',
          'callbackStart' => 'callbackOnStart',
          'callbackDelete' => 'callbackOnDelete'
        )
    );
    
    $s_sql = "INSERT INTO creditor_collecting_company_chat SET created=  NOW(), createdBy=?, moduleID=?, message = ?, message_from_oflow = 0, creditor_id = ?, collecting_company_case_id = ?";
    $o_query = $o_main->db->query($s_sql, array($username, $moduleID, $message, $selected_creditor['id'], $collecting_company_case_id));
    if($o_query){
        $company_chat_id = $o_main->db->insert_id();
        foreach($fwaFileuploadConfigs as $fwaFileuploadConfig) {
            $fieldName = $fwaFileuploadConfig['id'];
            $fwaFileuploadConfig['content_id'] = $company_chat_id;
            include( __DIR__ . "/fileupload_popup/contentreg.php");
        }
    } else {
      $v_return['error']= 2;
    }
} else {
  $v_return['error']= 1;
}
?>