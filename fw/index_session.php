<?php
function fw_load_user_session($accountname, $companyID, $companyaccess_id, $v_accountinfo, $v_accountinfo_basisconfig)
{
	$o_main = get_instance();
	$v_return = array(
		'status' => 0,
	);
	
	$s_url = '';
	$v_modules = array();
	$s_sql = 'SELECT id moduleID, name modulename, externalurl external, moduledata.* FROM moduledata ORDER BY modulemode, ordernr';
	$o_modules = $o_main->db->query($s_sql);
	foreach($o_modules->result_array() as $v_row)
	{
		$v_modules[$v_row['moduleID']] = $v_row;
	}
	
	$v_param = array('companyaccessID' => $companyaccess_id, 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
	$o_query = $o_main->db->query("SELECT *, TIME_TO_SEC(TIMEDIFF(NOW(), cache_timestamp)) refreshtime FROM session_framework WHERE companyaccessID = '".$o_main->db->escape_str($companyaccess_id)."' AND session = '".$o_main->db->escape_str($_COOKIE['sessionID'])."' AND username = '".$o_main->db->escape_str($_COOKIE['username'])."' LIMIT 1");
	$fw_session = $o_query ? $o_query->row_array() : array();
	
	$v_param = array(
		'COMPANYACCESS_ID'=>$companyaccess_id,
		'COMPANY_ID'=>$companyID,
		'ACCOUNTNAME'=>$v_accountinfo['accountname'],
		'MODULE_IDS'=>array_keys($v_modules),
		'CACHE_TIMESTAMP'=>isset($fw_session['cache_timestamp']) ? $fw_session['cache_timestamp'] : '',
		'CACHE_RECREATE'=>1//strtotime($v_accountinfo['force_cache_refresh']) > strtotime($fw_session['cache_timestamp'])
	);
	$v_connect = json_decode(APIconnectorUser('account_connect_v2', $_COOKIE['username'], $_COOKIE['sessionID'], $v_param), true);
	
	if(isset($v_connect['status']) && 1 == $v_connect['status'] && $v_connect['cache_status'] != 2)
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
		}
	
		if($v_accountinfo['getynet_app_id'] != $v_connect['app_id'])
		{
			$o_main->db->query("UPDATE accountinfo SET getynet_app_id = '".$o_main->db->escape_str($v_connect['app_id'])."'");
		}
	
		$v_account_menu = array();
		foreach($v_modules as $moduleID => $v_module)
		{
			if(!isset($v_connect['module_access'][$moduleID])) continue;
	
			$v_access = $v_connect['module_access'][$moduleID];
			if($v_access['useraccountaccess'] > 0 || $v_access['usercompanyaccess'] == 1)
			{
				/*if(isset($v_module['external']) && $v_module['external'] != '')
				{
					$extradir = $v_module['external'];
				} else {
					$extradir = $v_accountinfo['accountname'].'/modules/'.$v_module['modulename'];
				}*/
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
			'developeraccess' => 0,
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
			'fwbaseurl' => '&accountname='.$_GET['accountname'].'&companyID='.$_GET['companyID'].'&caID='.$companyaccess_id,
			'user_profile' => json_encode($v_connect['user_profile']),
			'style_set' => $v_connect['style_set'],
			'global_style_set' => $v_connect['global_style_set'],
			'invitation_config' => $v_connect['invitation_config'],
			'frontpage_config' => $v_connect['frontpage_config'],
			'content_server_api_url' => $v_connect['content_server_api_url'],
			'connection_type' => 1, // 1 - desktop; 2 - mobile
			'expired' => 0,
		);
	
		$b_session_created = FALSE;
		$o_query = $o_main->db->query("SELECT session FROM session_framework WHERE companyaccessID = '".$o_main->db->escape_str($companyaccess_id)."' AND session = '".$o_main->db->escape_str($_COOKIE['sessionID'])."' AND username = '".$o_main->db->escape_str($_COOKIE['username'])."' LIMIT 1");
		if($o_query && $o_query->num_rows()>0)
		{
			$v_update_where = array(
				'companyaccessID' => $companyaccess_id,
				'session' => $_COOKIE['sessionID'],
				'username' => $_COOKIE['username']
			);
			$b_session_created = $o_main->db->update('session_framework', $v_update, $v_update_where);
		} else {
			$v_update['companyaccessID'] = $companyaccess_id;
			$v_update['session'] = $_COOKIE['sessionID'];
			$v_update['username'] = $_COOKIE['username'];
			$b_session_created = $o_main->db->insert('session_framework', $v_update);
		}
	} else if(isset($v_connect['status']) && 1 == $v_connect['status']) {
		$b_session_created = TRUE;
		$s_url = urldecode($fw_session['urlpath']);
		$s_url = explode('&companyID='.intval($_GET['companyID']).'&', urldecode($fw_session['urlpath']));
		$s_url = ltrim(str_replace(array('pageID=35', '&accountname='.$accountname, '&companyID='.$companyID, '&caID='.$companyaccess_id), array('', '', '', ''), $s_url[1]), '&');
	}
	/*
	** LOAD SESSION END
	*/
	
	// Handle creation of external table views
	unset($s_view_field);
	$v_existing_views = array();
	$s_sql = "SHOW FULL TABLES WHERE TABLE_TYPE LIKE '%VIEW%'";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $v_row)
	{
		if(!isset($s_view_field))
		{
			$v_tmp = array_keys($v_row);
			$s_view_field = $v_tmp[0];
		}
		$v_existing_views[] = $v_row[$s_view_field];
	}
	$b_create_views = FALSE;
	if($o_main->db->table_exists('accountinfo_external_table_view'))
	{
		$s_sql = "SELECT * FROM accountinfo_external_table_view ORDER BY table_name";
		$o_query = $o_main->db->query($s_sql);
		if($o_query && $o_query->num_rows()>0)
		foreach($o_query->result_array() as $v_row)
		{
			if(!in_array($v_row['table_name'], $v_existing_views)) $b_create_views = TRUE;
		}
	}
	if($b_create_views)
	{
		$v_param = array();
		$s_response = APIconnectorAccount('account_database_view_refresh', $v_accountinfo['accountname'], $v_accountinfo['password'], $v_param);
		$v_response = json_decode($s_response, TRUE);
		if(isset($v_response['status']) && 1 == $v_response['status'])
		{
			// account messaging!
		}
	}
	
	if($b_session_created)
	{
		$v_cache_userlist_access = array('cache_timestamp' => '');
		if($o_main->db->table_exists('cache_userlist_access'))
		{
			$o_query = $o_main->db->query("SELECT * FROM cache_userlist_access LIMIT 1");
			$v_cache_userlist_access = $o_query ? $o_query->row_array() : array();
			$b_recreate = !empty($v_accountinfo['force_cache_refresh']) && strtotime($v_accountinfo['force_cache_refresh']) > strtotime($v_cache_userlist_access['cache_timestamp']);
	
			$v_param = array(
				'COMPANY_ID'=>$companyID,
				'CACHE_TIMESTAMP'=>$v_cache_userlist_access['cache_timestamp'],
				'CACHE_RECREATE'=>$b_recreate,
				'GET_MEMBERSHIPS' => 1
			);
			$s_response = APIconnectorUser("companyaccessbycompanyidget_v2", $_COOKIE['username'], $_COOKIE['sessionID'], $v_param);
			$v_response = json_decode($s_response, TRUE);
			if(isset($v_response['status']) && $v_response['status'] == 1 && $v_response['cache_status'] != 2)
			{
				$o_main->db->query("TRUNCATE cache_userlist_access");
				foreach($v_response['data'] as $v_item)
				{
					$o_main->db->query("INSERT INTO cache_userlist_access SET cache_timestamp = '".$o_main->db->escape_str($v_response['cache_timestamp'])."', companyaccess_id = '".$o_main->db->escape_str($v_item['companyaccess_id'])."', user_id = '".$o_main->db->escape_str($v_item['user_id'])."', username = '".$o_main->db->escape_str($v_item['username'])."', first_name = '".$o_main->db->escape_str($v_item['first_name'])."', middle_name = '".$o_main->db->escape_str($v_item['middle_name'])."', last_name = '".$o_main->db->escape_str($v_item['last_name'])."', deactivated = '".$o_main->db->escape_str($v_item['deactivated'])."', admin = '".$o_main->db->escape_str($v_item['admin'])."', system_admin = '".$o_main->db->escape_str($v_item['system_admin'])."', groupID = '".$o_main->db->escape_str($v_item['groupID'])."', groupname = '".$o_main->db->escape_str($v_item['groupname'])."', accesslevel = '".$o_main->db->escape_str($v_item['accesslevel'])."', image = '".$o_main->db->escape_str($v_item['image'])."', mobile = '".$o_main->db->escape_str($v_item['mobile'])."', mobile_prefix = '".$o_main->db->escape_str($v_item['mobile_prefix'])."', mobile_verified = '".$o_main->db->escape_str($v_item['mobile_verified'])."', firstlogin = '".$o_main->db->escape_str($v_item['firstlogin'])."', lastlogin = '".$o_main->db->escape_str($v_item['lastlogin'])."', last_activity = '".$o_main->db->escape_str($v_item['last_activity'])."', invitationsent = '".$o_main->db->escape_str($v_item['invitationsent'])."', invitationsentnr = '".$o_main->db->escape_str($v_item['invitationsentnr'])."', `groups` = '".$o_main->db->escape_str(json_encode($v_item['groups']))."'");
		
				}
		
				$o_main->db->query("TRUNCATE cache_userlist_membershipaccess");
				$o_main->db->query("TRUNCATE cache_userlist_membershipaccess_conn");
				foreach($v_response['data_memberships'] as $v_item)
				{
					$o_main->db->query("INSERT INTO cache_userlist_membershipaccess SET cache_timestamp = '".$o_main->db->escape_str($v_response['cache_timestamp'])."', companyaccess_id = '".$o_main->db->escape_str($v_item['companyaccess_id'])."', user_id = '".$o_main->db->escape_str($v_item['user_id'])."', username = '".$o_main->db->escape_str($v_item['username'])."', first_name = '".$o_main->db->escape_str($v_item['first_name'])."', middle_name = '".$o_main->db->escape_str($v_item['middle_name'])."', last_name = '".$o_main->db->escape_str($v_item['last_name'])."', deactivated = '".$o_main->db->escape_str($v_item['deactivated'])."', admin = '".$o_main->db->escape_str($v_item['admin'])."', system_admin = '".$o_main->db->escape_str($v_item['system_admin'])."', groupID = '".$o_main->db->escape_str($v_item['groupID'])."', groupname = '".$o_main->db->escape_str($v_item['groupname'])."', accesslevel = '".$o_main->db->escape_str($v_item['accesslevel'])."', image = '".$o_main->db->escape_str($v_item['image'])."', mobile = '".$o_main->db->escape_str($v_item['mobile'])."', mobile_prefix = '".$o_main->db->escape_str($v_item['mobile_prefix'])."', mobile_verified = '".$o_main->db->escape_str($v_item['mobile_verified'])."', firstlogin = '".$o_main->db->escape_str($v_item['firstlogin'])."', lastlogin = '".$o_main->db->escape_str($v_item['lastlogin'])."', last_activity = '".$o_main->db->escape_str($v_item['last_activity'])."', invitationsent = '".$o_main->db->escape_str($v_item['invitationsent'])."', invitationsentnr = '".$o_main->db->escape_str($v_item['invitationsentnr'])."', `groups` = '".$o_main->db->escape_str(json_encode($v_item['groups']))."'");
					$o_main->db->query("INSERT INTO cache_userlist_membershipaccess_conn SET username = '".$o_main->db->escape_str($v_item['username'])."', companyaccess_id='".$o_main->db->escape_str($v_item['companyaccess_id'])."', membersystemID = '".$o_main->db->escape_str($v_item['membersystemID'])."', membersystemmodule = '".$o_main->db->escape_str($v_item['membersystemmodule'])."'");
		
				}
			}
		}
		
		$l_membership_active_subscription = 0;
		$v_membership_tags = array();
		if($v_accountinfo['activate_crm_user_content_filtering_tags'])
		{
			$v_properties = $v_property_groups = $v_group_properties = array();
			$s_sql = "SELECT cp.* FROM contactperson AS cp
			LEFT OUTER JOIN subscriptionmulti AS sm ON sm.customerId = cp.customerId AND sm.startDate <= CURDATE() AND (sm.stoppedDate = '0000-00-00' OR sm.stoppedDate is null OR sm.stoppedDate >= CURDATE())
			
			LEFT OUTER JOIN contactperson_subscription_connection AS csc ON csc.contactperson_id = cp.id
			LEFT OUTER JOIN subscriptionmulti AS sm2 ON csc.subscriptionmulti_id = sm2.id AND sm2.startDate <= CURDATE()
			AND (sm2.stoppedDate = '0000-00-00' OR sm2.stoppedDate is null OR sm2.stoppedDate >= CURDATE())
			
			WHERE cp.email = '".$o_main->db->escape_str($_COOKIE['username'])."' AND (
			(IFNULL(cp.intranet_membership_subscription_type, 0) = 0 AND sm.id IS NOT NULL) OR 
			(cp.intranet_membership_subscription_type = 1 AND sm2.id IS NOT NULL AND csc.id IS NOT NULL) OR
			cp.intranet_membership_subscription_type = 2
			)";
			$o_query = $o_main->db->query($s_sql);
			if($o_query && $o_query->num_rows()>0)
			foreach($o_query->result_array() as $v_contactperson)
			{
				$l_membership_active_subscription = 1;
				$v_items = array();
				if(intval($v_contactperson['intranet_membership_type']) == 0)
				{
					$s_sql = "SELECT imao.object_id, pr.name AS property_name, imao.objectgroup_id, prg.name AS property_group_name, pgc.property_id AS group_property_id, pr2.name AS group_property_name FROM intranet_membership AS im
					JOIN intranet_membership_customer_connection AS im_cus ON im_cus.membership_id = im.id
					JOIN intranet_membership_attached_object AS imao ON imao.membership_id = im_cus.membership_id
					LEFT OUTER JOIN property AS pr ON pr.id = imao.object_id
					LEFT OUTER JOIN property_group AS prg ON prg.id = imao.objectgroup_id
					LEFT OUTER JOIN property_group_connection AS pgc ON pgc.property_group_id = imao.objectgroup_id AND imao.object_id = 0
					LEFT OUTER JOIN property AS pr2 ON pr2.id = pgc.property_id
					WHERE im_cus.customer_id = '".$o_main->db->escape_str($v_contactperson['customerId'])."'";
					$o_find = $o_main->db->query($s_sql);
					$v_items = $o_find ? $o_find->result_array() : array();
		
				} else if($v_contactperson['intranet_membership_type'] == 1)
				{
					$s_sql = "SELECT imao.object_id, pr.name AS property_name, imao.objectgroup_id, prg.name AS property_group_name, pgc.property_id AS group_property_id, pr2.name AS group_property_name FROM intranet_membership AS im
					JOIN intranet_membership_contactperson_connection AS im_cp ON im_cp.membership_id = im.id
					JOIN intranet_membership_attached_object AS imao ON imao.membership_id = im_cp.membership_id
					LEFT OUTER JOIN property AS pr ON pr.id = imao.object_id
					LEFT OUTER JOIN property_group AS prg ON prg.id = imao.objectgroup_id
					LEFT OUTER JOIN property_group_connection AS pgc ON pgc.property_group_id = imao.objectgroup_id AND imao.object_id = 0
					LEFT OUTER JOIN property AS pr2 ON pr2.id = pgc.property_id
					WHERE im_cp.contactperson_id = '".$o_main->db->escape_str($v_contactperson['id'])."'";
					$o_find = $o_main->db->query($s_sql);
					$v_items = $o_find ? $o_find->result_array() : array();
		
				}
				foreach($v_items as $v_item)
				{
					if(0 < $v_item['object_id'] && !array_key_exists($v_item['object_id'], $v_properties))
					{
						$v_properties[$v_item['object_id']] = array('id' => $v_item['object_id'], 'name' => $v_item['property_name']);
					}
					if(0 < $v_item['objectgroup_id'] && !array_key_exists($v_item['objectgroup_id'], $v_property_groups))
					{
						$v_property_groups[$v_item['objectgroup_id']] = array('id' => $v_item['objectgroup_id'], 'name' => $v_item['property_group_name']);
					}
					if(0 < $v_item['group_property_id'] && !array_key_exists($v_item['group_property_id'], $v_group_properties))
					{
						$v_group_properties[$v_item['group_property_id']] = array('id' => $v_item['group_property_id'], 'name' => $v_item['group_property_name']);
					}
				}
			}
			
			$v_membership_tags['tags_read'] = $v_properties;
			$v_membership_tags['tags_write'] = $v_properties;
			$v_membership_tags['groups_read'] = $v_property_groups;
			$v_membership_tags['groups_write'] = $v_property_groups;
			$v_membership_tags['group_properties'] = $v_group_properties;
		}
	
		$hostsplit =  explode(".",$_SERVER['HTTP_HOST']);
		$host = (count($hostsplit) == 3 ? substr($_SERVER['HTTP_HOST'],strpos($_SERVER['HTTP_HOST'],".")+1) : $_SERVER['HTTP_HOST']);
	
		setcookie($accountname.'_caID', intval($companyaccess_id), time()+60*60*24*365, '/', '.'.$host, true, true);
	
		$o_main->db->query("UPDATE session_framework SET app_name = 'WEB_".$o_main->db->escape_str($v_connect['app_name'])."', app_version = '".$o_main->db->escape_str($v_connect['app_version'])."', app_platform = 'Framework', browser_version = '".$o_main->db->escape_str($_SERVER['HTTP_USER_AGENT'])."', screen_resolution = '".$o_main->db->escape_str(isset($_SESSION['fw_screen_width'], $_SESSION['fw_screen_height']) ? $_SESSION['fw_screen_width'].'x'.$_SESSION['fw_screen_height'] : '')."', membership_tags = '".$o_main->db->escape_str(json_encode($v_membership_tags))."', active_crm_subscription = '".$o_main->db->escape_str($l_membership_active_subscription)."' WHERE companyaccessID = '".$o_main->db->escape_str($companyaccess_id)."' AND session = '".$o_main->db->escape_str($_COOKIE['sessionID'])."' AND username = '".$o_main->db->escape_str($_COOKIE['username'])."'");
		
		$v_return['status'] = 1;
		$v_return['s_url'] = $s_url;
	}
	
	return $v_return;
}