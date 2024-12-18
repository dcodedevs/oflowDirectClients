<?php
if (!function_exists('APIconnectorUser')) require_once(__DIR__."/APIconnector.php");
if (!function_exists('fw_access_element_retreive')) {
    function fw_access_element_retreive($variables, $companyID, $access_element_name, $module_name, $folder_name){
        global $o_main;

        $haveAccessToGroups = array();
        $response = json_decode(APIconnectorUser("companyaccessbycompanyidget", $variables->loggID, $variables->sessionID, array('COMPANY_ID'=>$companyID, 'GET_GROUPS'=>1)), true);
        $v_membersystem = array();
        foreach($response['data'] as $writeContent)
        {
            array_push($v_membersystem, $writeContent);
        }
        $v_user_details = array();
        foreach($v_membersystem as $member){
            if($member['username'] == $variables->loggID){
                $haveAccessToGroups = $member['groups'];
            }
        }
        $groupIdsWithAccess = array();
        foreach($haveAccessToGroups as $singleGroup){
            array_push($groupIdsWithAccess, $singleGroup['id']);
        }
        $access_element = false;
        if(count($groupIdsWithAccess) > 0){
            $sql = "SELECT * FROM accesselementadmin WHERE keyname = ? AND modulename = ? AND foldername = ? AND groupId IN (".implode(",", $groupIdsWithAccess).")";
            $o_query = $o_main->db->query($sql, array($access_element_name, $module_name, $folder_name));
            $attachedGroups = $o_query ? $o_query->num_rows() : 0;
            if($attachedGroups > 0){
                $access_element = true;
            }
        }
        return $access_element;
    }
}
?>
