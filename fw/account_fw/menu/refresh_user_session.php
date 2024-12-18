<?php
if(isset($v_accountinfo_basisconfig['account_type']) && 'webapp' == $v_accountinfo_basisconfig['account_type'])
{
	$o_query = $o_main->db->query("SELECT * FROM language ORDER BY default_webapp_language DESC, webapp_language DESC, inputlanguage DESC");
	$languageID = (($o_query && $o_row = $o_query->row()) ? $o_row->languageID : '');
}

$o_query = $o_main->db->query("SELECT *, TIME_TO_SEC(TIMEDIFF(NOW(), cache_timestamp)) refreshtime FROM session_framework WHERE companyaccessID = '".$o_main->db->escape_str($_GET['caID'])."' AND session = '".$o_main->db->escape_str($_COOKIE['sessionID'])."' AND username = '".$o_main->db->escape_str($_COOKIE['username'])."' LIMIT 1");
if($o_query && $o_query->num_rows()>0)
{
	$fw_session = $o_query->row_array();

	if(isset($v_accountinfo_basisconfig['account_type']) && 'webapp' == $v_accountinfo_basisconfig['account_type'])
	{
		if(isset($fw_session['accountlanguageID']) && '' != $fw_session['accountlanguageID'])
		{
			$o_query = $o_main->db->query("SELECT * FROM language WHERE languageID = '".$o_main->db->escape_str($fw_session['accountlanguageID'])."' AND webapp_language = 1 AND published_webapp_language = 1");
			if($o_query && $o_query->num_rows()>0)
			{
				$languageID = $fw_session['accountlanguageID'];
			}
		}
	}

	/*$l_session_reload_sec = 86400; // 24 hours
	if(isset($variables->fw_settings_accountconfig))
	{
		$fw_settings = $variables->fw_settings_accountconfig;
	} else {
		$o_query = $o_main->db->get('frameworksettings');
		$fw_settings = $o_query ? $o_query->row_array() : array();
	}
	if(isset($fw_settings['user_session_reload_interval_minutes']) && $fw_settings['user_session_reload_interval_minutes'] > 0)
	{
		$l_session_reload_sec = intval($fw_settings['user_session_reload_interval_minutes']) * 60;
	}

	// run only in one instance every X seconds or when forced
	if($fw_session['refreshtime'] > $l_session_reload_sec || strtotime($v_accountinfo['force_cache_refresh']) > strtotime($fw_session['cache_timestamp']))
	{
		//block execution in more instances
		/*$s_sql = "UPDATE session_framework SET cache_timestamp = NOW() WHERE companyaccessID = '".$o_main->db->escape_str($_GET['caID'])."' AND username = '".$o_main->db->escape_str($_COOKIE['username'])."' AND session = '".$o_main->db->escape_str($_COOKIE['sessionID'])."'";
		$o_query = $o_main->db->query($s_sql);*/

	if(!isset($v_accountinfo))
	{
		$o_query = $o_main->db->get('accountinfo');
		$v_accountinfo = $o_query ? $o_query->row_array() : array();
	}

	$v_modules = array();
	$s_sql = 'SELECT id moduleID, name modulename, externalurl external, moduledata.* FROM moduledata ORDER BY modulemode, ordernr';
	$o_modules = $o_main->db->query($s_sql);
	foreach($o_modules->result_array() as $v_row)
	{
		$v_modules[$v_row['moduleID']] = $v_row;
	}

	$b_recreate = TRUE;
	if(!empty($v_accountinfo['force_cache_refresh']) && !empty($fw_session['cache_timestamp']))
	{
		$b_recreate = strtotime($v_accountinfo['force_cache_refresh']) > strtotime($fw_session['cache_timestamp']);
	}
	$s_key = $v_accountinfo['accountname'].'_cache_timestamp';
	if(!$b_recreate && isset($_SESSION[$s_key]) && $_SESSION[$s_key] == $fw_session['cache_timestamp'])
	{
		return;
	}
	$v_param = array(
		'COMPANYACCESS_ID'=>$_GET['caID'],
		'COMPANY_ID'=>$_GET['companyID'],
		'ACCOUNTNAME'=>$v_accountinfo['accountname'],
		'PASSWORD'=>$v_accountinfo['password'],
		'MODULE_IDS'=>array_keys($v_modules),
		'CACHE_TIMESTAMP'=>$fw_session['cache_timestamp'],
		'CACHE_RECREATE'=>$b_recreate,
	);
	$v_connect = json_decode(APIconnectorUser('account_connect_v2', $_COOKIE['username'], $_COOKIE['sessionID'], $v_param), true);
	if($v_connect['cache_status'] != 2)
	{
		$s_account_language_id = ($v_connect['accountlanguageID'] != NULL ? $v_connect['accountlanguageID'] : '');
		if(isset($v_accountinfo_basisconfig['account_type']) && 'webapp' == $v_accountinfo_basisconfig['account_type'])
		{
			$b_get_default = TRUE;
			if(isset($v_connect['choosen_language_id']) && '' != $v_connect['choosen_language_id'])
			{
				$o_query = $o_main->db->query("SELECT * FROM language WHERE languageID = '".$o_main->db->escape_str($v_connect['choosen_language_id'])."' AND webapp_language = 1 AND published_webapp_language = 1");
				if($o_query && $o_query->num_rows()>0)
				{
					$b_get_default = FALSE;
					$s_account_language_id = $v_connect['choosen_language_id'];
				}
			}
			if($b_get_default)
			{
				$o_query = $o_main->db->query("SELECT * FROM language ORDER BY default_webapp_language DESC, webapp_language DESC, inputlanguage DESC");
				$s_account_language_id = (($o_query && $o_row = $o_query->row()) ? $o_row->languageID : '');
			}
			$languageID = $s_account_language_id;
		}

		$v_account_menu = array();
		foreach($v_modules as $moduleID => $v_module)
		{
			if(!isset($v_connect['module_access'][$moduleID])) continue;

			$v_access = $v_connect['module_access'][$moduleID];
			if($v_access['useraccountaccess'] > 0 || $v_access['usercompanyaccess'] == 1)
			{
				if(isset($v_module['external']) && $v_module['external'] != '')
				{
					$extradir = $v_module['external'];
				} else {
					$extradir = $v_accountinfo['accountname'].'/modules/'.$v_module['modulename'];
				}
				if(is_null($v_module['virtual_module_source'])) $v_module['virtual_module_source'] = '';
				$v_module_settings = getModuleName($v_module[''==trim($v_module['virtual_module_source'])?'modulename':'virtual_module_source'], $s_account_language_id);
				$l_owner_access = ((isset($v_access['owneraccess']) && 1 == $v_access['owneraccess']) ? 1 : 0);

				$v_account_menu[$v_module['modulename']] = array(
					($v_module['local_name']!="" ? $v_module['local_name'] : ($v_module_settings[0]!='' ? $v_module_settings[0] : $v_module['modulename'])),
					'module='.$v_module['modulename'].'&moduleID='.$v_module['moduleID'].'&modulename='.$v_module['modulename'].'&folder='.($v_module_settings[1]!=''?$v_module_settings[1]:'input').'&folderfile='.($v_module_settings[2]!=''?$v_module_settings[2]:'input').'&updatepath=1'.($v_module['external']!='' ? '&external='.$v_module['external'] : ''),
					$v_access['moduleAccesslevel'],
					$v_module['modulemode'],
					array('data'=>array($v_access)),
					($v_module['deactivated']==1?1:0),
					$l_owner_access
				);

				if($v_module['modulemode'] == 'C' && $v_access['moduleAccesslevel'] > 0)
				{
					if($s_url=='')
						$s_url = 'module='.$v_module['modulename'].'&moduleID='.$v_module['moduleID'].'&modulename='.$v_module['modulename'].'&folder='.($v_module_settings[1]!=''?$v_module_settings[1]:'input').'&folderfile='.($v_module_settings[2]!=''?$v_module_settings[2]:'input').'&updatepath=1'.($v_module['external']!='' ? '&external='.$v_module['external'] : '');
				}
			}
		}
		
		$_SESSION[$v_accountinfo['accountname'].'_cache_timestamp'] = $v_connect['cache_timestamp'];

		$v_update = array(
			'cache_menu' => json_encode($v_account_menu),
			'cache_userstatus' => '',
			'cache_timestamp' => $v_connect['cache_timestamp'], //date("Y-m-d H:i:s", time()),
			'IP' => $_SERVER['REMOTE_ADDR'],
			'developerlanguageID' => ($v_connect['developerlanguageID'] != NULL ? $v_connect['developerlanguageID'] : ''),
			'accountlanguageID' => $s_account_language_id,
			'accountnamefriendly' => ($v_connect['friendlyaccountname'] != '' ? $v_connect['friendlyaccountname'] : $v_connect['accountname']),
			'upload_quota' => ($v_connect['upload_size_quota'] != NULL ? $v_connect['upload_size_quota'] : ''),
			'companyname' => ($v_connect['companyname'] != NULL ? $v_connect['companyname'] : ''),
			'useradmin' => ($v_connect['companyaccess']['admin'] != NULL ? $v_connect['companyaccess']['admin'] : ''),
			'system_admin' => ($v_connect['companyaccess']['system_admin'] != NULL ? $v_connect['companyaccess']['system_admin'] : ''),
			'accesslevel' => ($v_connect['companyaccess']['accesslevel'] != NULL ? $v_connect['companyaccess']['accesslevel'] : ''),
			/*'developeraccess' => 0,*/ // ALI - no need to set this value anymore
			'developeraccessoriginal' => ($v_connect['companyaccess']['developeraccess'] != NULL ? $v_connect['companyaccess']['developeraccess'] : ''),
			'groupID' => ($v_connect['companyaccess']['groupID'] != NULL ? $v_connect['companyaccess']['groupID'] : ''),
			'groupname' => ($v_connect['companyaccess']['groupname'] != NULL ? $v_connect['companyaccess']['groupname'] : ''),
			'user_groups' => json_encode($v_connect['user_groups']),
			'userConnectedToId' => ($v_connect['companyaccess']['membersystemID'] != NULL ? $v_connect['companyaccess']['membersystemID'] : ''),
			'membersystemmodule' => ($v_connect['companyaccess']['membersystemmodule'] != NULL ? $v_connect['companyaccess']['membersystemmodule'] : ''),
			'departmentenable' => ($v_connect['departmentenable'] != NULL ? $v_connect['departmentenable'] : ''),
			'fullname' => ($v_connect['user_profile']['name'] != NULL ? $v_connect['user_profile']['name'] : ''),
			'profileimage' => ($v_connect['user_profile']['image'] != NULL ? $v_connect['user_profile']['image'] : ''),
			'channel_creation_level_for_all_users' => ($v_connect['channel_creation_level_for_all_users'] != NULL ? $v_connect['channel_creation_level_for_all_users'] : ''),
			'account_edition' => $v_connect['edition'],
			/*'fwbaseurl' => '',*/ // ALI - no need to set this value anymore
			'user_profile' => json_encode($v_connect['user_profile']),
			'style_set' => $v_connect['style_set'],
			'global_style_set' => $v_connect['global_style_set'],
			'invitation_config' => $v_connect['invitation_config'],
			'frontpage_config' => $v_connect['frontpage_config'],
			'content_server_api_url' => $v_connect['content_server_api_url'],
		);
		$v_update_where = array(
			'companyaccessID' => $_GET['caID'],
			'session' => $_COOKIE['sessionID'],
			'username' => $_COOKIE['username']
		);
		$o_main->db->update('session_framework', $v_update, $v_update_where);
	} else {
		$_SESSION[$v_accountinfo['accountname'].'_cache_timestamp'] = $fw_session['cache_timestamp'];
	}
}
