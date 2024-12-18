<?php
$s_current_module= "framework";
$s_current_module_folder="getynet_fw";

$v_haveAccessToGroups = array();
$v_response = json_decode(APIconnectorUser("companyaccessbycompanyidget", $variables->loggID, $variables->sessionID, array('COMPANY_ID'=>$companyID, 'GET_GROUPS'=>1)), true);
$v_membersystem_un = array();
foreach($v_response['data'] as $v_writeContent)
{
    array_push($v_membersystem_un, $v_writeContent);
}
foreach($v_membersystem_un as $v_member){
    if(mb_strtolower($v_member['username']) == mb_strtolower($variables->loggID)){
        $v_haveAccessToGroups = $v_member['groups'];
    }
}

$v_groupIdsWithAccess = array();
foreach($v_haveAccessToGroups as $v_singleGroup){
    array_push($v_groupIdsWithAccess, $v_singleGroup['id']);
}

$sql = "SELECT * FROM accesselementadmin WHERE modulename = ? AND foldername = ?";
$o_query = $o_main->db->query($sql, array($s_current_module, $s_current_module_folder));
$allAccessElements = $o_query ? $o_query->result_array() : array();
foreach($allAccessElements as $accessElement) {
    ${$accessElement['keyname']} = false;
}

foreach($allAccessElements as $accessElement) {
    //update database if used old
    $v_string = $accessElement['keyname'];
    $v_subString = "_access";
    $v_strlen = strlen($v_string);
    $v_subStringLength = strlen($v_subString);

    if(substr_compare($v_string, $v_subString, $v_strlen - $v_subStringLength, $v_subStringLength) === 0) {
        $newKeyname = str_replace("_access", "", $accessElement['keyname']);
        $o_main->db->query("UPDATE accesselementadmin SET keyname = ? WHERE id = ?", array($newKeyname, $accessElement['id']));
        if($o_query) {
            $accessElement['keyname'] = $newKeyname;
        }
    }

    if(in_array($accessElement['groupId'], $v_groupIdsWithAccess)){
        ${$accessElement['keyname']} = true;
    }
}
?>
