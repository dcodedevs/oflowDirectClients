<?php
if($_POST['companyID'] > 0){
    if($_POST['checked']){
        $v_param = array(
        	'COMPANY_ID'=>$_POST['companyID']
        );
        $s_response = APIconnectorUser("companyallowededituserdata_add", $variables->loggID, $variables->sessionID, $v_param);
        $v_response = json_decode($s_response, TRUE);
        if($v_response['data'] != 'OK'){
            echo $formText_ErrorAddingAccessPleaseContactSystemDeveloper_output;
        }
    } else {
        $v_param = array(
        	'COMPANY_ID'=>$_POST['companyID']
        );
        $s_response = APIconnectorUser("companyallowededituserdata_delete", $variables->loggID, $variables->sessionID, $v_param);
        $v_response = json_decode($s_response, TRUE);
        if($v_response['data'] != 'OK'){
            echo $formText_ErrorDeletingAccessPleaseContactSystemDeveloper_output;
        }
    }
} else {
    echo $formText_MissingCompanyId_output;
}
?>
