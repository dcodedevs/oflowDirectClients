<?php
/*
 * Version 1.3
*/
$file_src = "";
require_once(__DIR__."/../../../fw/account_fw/includes/fn_log_action.php");
require_once(__DIR__."/../../../fw/account_fw/includes/fn_fw_api_call.php");

if(isset($v_data['file'], $v_data['ID'], $v_data['table'], $v_data['field'], $v_data['caID'], $v_data['username'], $v_data['sessionID'], $v_data['ip_address']))
{
	$v_param = array('companyaccessID' => $v_data['caID'], 'session' => $v_data['sessionID'], 'username' => $v_data['username'], 'IP' => $v_data['ip_address']);
	$o_query = $o_main->db->get_where('session_framework', $v_param);
	if($o_query) $fw_session = $o_query->row_array();
	$access = json_decode($fw_session['cache_menu'],true);

    if ($v_data['externalApiAccount']) {
        $hash = $v_data['externalApiHash'];
        $check_hash = md5($v_data['externalApiAccount'] . '-' . $v_data['ID']);
        if ($check_hash == $hash) {
            $api_url = rtrim(urldecode($v_data['externalApiAccount']), '/') . '/api';
            $api_response = fw_api_call(array(
                'api_url' => $api_url,
                'action' => 'get_protected_file',
                'params' => array(
                    'file' => $v_data['file'],
                    'ID' => $v_data['ID'],
                    'table' => $v_data['table'],
                    'field' => $v_data['field']
                )
            ), true);

            if ($api_response['data']['file_path']) {
                $b_found_file = true;
                $module = array('name' => $api_response['data']['file_module']);
                $api_file_path = $api_response['data']['file_path'];
            }
        }
    }

    else {
        if ($v_data['externalProtected']) {
            $o_query = $o_main->db->query("SELECT * FROM accountinfo");
            $accountConfig = $o_query ? $o_query->row_array() : array();
            $externalAccountPath = rtrim($accountConfig['externalAccountPath'], '/');
            $o_main->database('external', false, true);
        }

        $s_table = $o_main->db_escape_name($v_data['table']);

        if($o_main->db->table_exists($s_table.'content'))
        {
            $s_sql = "SELECT ".$s_table.".id cid, ".$s_table.".*, ".$s_table."content.* FROM ".$s_table." JOIN ".$s_table."content ON ".$s_table."content.".$s_table."ID = ".$s_table.".id AND ".$s_table."content.languageID = ".$o_main->db->escape($v_data['languageID'])." WHERE ".$s_table.".id = ".$o_main->db->escape($v_data['ID']);
        } else {
            $s_sql = "SELECT ".$s_table.".id cid, ".$s_table.".* FROM ".$s_table." WHERE ".$s_table.".id = ".$o_main->db->escape($v_data['ID']);
        }
        $content = array();
        $o_query = $o_main->db->query($s_sql);
        if($o_query && $o_query->num_rows()>0) $content = $o_query->row_array();

        $module = array();
        $o_query = $o_main->db->query('select name from moduledata where id = ?', array($content['moduleID']));
        if($o_query && $o_query->num_rows()>0) $module = $o_query->row_array();

        $v_data['file'] = stripslashes($v_data['file']);
        $v_file = explode("/", $v_data['file']);
        $s_file = array_pop($v_file);
        $s_file_encoded = implode("/", $v_file)."/".rawurlencode($s_file);
		
        $b_found_file = false;
        if(strpos($content[$v_data['field']], $v_data['file']) !== false || strpos($content[$v_data['field']], $s_file_encoded) !== false)
        {
            $b_found_file = true;
        } else {
            $v_json = json_decode($content[$v_data['field']],true);
            foreach($v_json as $v_item)
            {
                foreach($v_item[1] as $s_file)
                {
                    if(strpos($s_file, $v_data['file']) !== false || strpos($s_file, $s_file_encoded) !== false)
                    {
                        $b_found_file = true;
                        break 2;
                    }
                }
            }
        }
    }
	
	if(array_key_exists($module['name'],$access) and $access[$module['name']][2] > 0 and $b_found_file)
	{
		$file_src = $v_data['file'];
		
        if ($v_data['externalProtected']) {
            $file_src = $v_return['external'] = $externalAccountPath.'/uploads/protected/'.$file_src;
        }

        if ($v_data['externalApiAccount']) {
            $file_src = $api_file_path;
        }
	}
}
if($file_src!="")
{
	log_action("uploads_storage_get");
	$v_return['status'] = 1;
} else {
	log_action("uploads_storage_fail");
}