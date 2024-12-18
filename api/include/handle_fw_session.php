<?php
$b_session_created = FALSE;
$l_company_id = $v_data['companyID'];
$l_companyaccess_id = $v_data['caID'];
$username = $v_data['username'];
$sessionID = $v_data['sessionID'];

include_once(__DIR__.'/../../fw/account_fw/includes/function.getModuleName.php');

$o_query = $o_main->db->query("SELECT * FROM accountinfo");
$v_accountinfo = $o_query ? $o_query->row_array() : array();

if(!$o_main->db->table_exists('session_framework'))
{
	$b_table_created = $o_main->db->query("CREATE TABLE session_framework (
		companyaccessID CHAR(64) NOT NULL,
		session CHAR(50) NOT NULL,
		username CHAR(100) NOT NULL,
		IP CHAR(32) NOT NULL,
		accountlanguageID CHAR(10) NOT NULL,
		developerlanguageID CHAR(15) NOT NULL,
		cache_menu TEXT NOT NULL,
		cache_userstatus TEXT NOT NULL,
		cache_messageshowto TEXT NOT NULL,
		cache_contactset TEXT NOT NULL,
		cache_userlist LONGTEXT NOT NULL,
		cache_right TEXT NOT NULL,
		cache_access_level TEXT NOT NULL,
		cache_timestamp DATETIME NOT NULL,
		accountnamefriendly CHAR(255) NOT NULL,
		upload_quota TEXT NOT NULL,
		companyname CHAR(255) NOT NULL,
		useradmin TINYINT(4) NOT NULL,
		system_admin TINYINT(4) NOT NULL,
		accesslevel TEXT NOT NULL,
		developeraccess TINYINT(4) NOT NULL DEFAULT '0',
		developeraccessoriginal TINYINT(4) NOT NULL DEFAULT '0',
		groupID TEXT NOT NULL,
		groupname TEXT NOT NULL,
		user_groups TEXT NOT NULL DEFAULT '',
		userConnectedToId INT(11) NOT NULL,
		membersystemmodule CHAR(255) NOT NULL,
		departmentenable TINYINT(4) NOT NULL,
		fullname CHAR(50) NOT NULL,
		profileimage CHAR(255) NOT NULL,
		fwbaseurl TEXT NOT NULL,
		error_msg TEXT NOT NULL,
		variables TEXT NOT NULL,
		urlpath TEXT NOT NULL,
		returl TEXT NOT NULL,
		channel_creation_level_for_all_users TINYINT(4) NOT NULL DEFAULT '0',
		account_edition INT(11) NOT NULL DEFAULT '0',
		user_profile TEXT NOT NULL DEFAULT '',
		style_set TEXT NOT NULL DEFAULT '',
		global_style_set TEXT NOT NULL DEFAULT '',
		invitation_config TEXT NOT NULL DEFAULT '',
		frontpage_config TEXT NOT NULL DEFAULT '',
		content_server_api_url TEXT NOT NULL DEFAULT '',
		last_request_time DATETIME NULL DEFAULT NULL,
		connection_type TINYINT(4) NOT NULL DEFAULT '0',
		app_name TEXT NOT NULL DEFAULT '',
		app_version TEXT NOT NULL DEFAULT '',
		app_platform TEXT NOT NULL DEFAULT '',
		device_name TEXT NOT NULL DEFAULT '',
		device_year_class TEXT NOT NULL DEFAULT '',
		sdk_version TEXT NOT NULL DEFAULT '',
		browser_version TEXT NOT NULL DEFAULT '',
		screen_resolution TEXT NOT NULL DEFAULT '',
		membership_tags TEXT NOT NULL DEFAULT '',
		active_crm_subscription TINYINT(4) NOT NULL DEFAULT '0',
		expired TINYINT(4) NOT NULL DEFAULT '0',
		INDEX Idx_1 (companyaccessID),
		INDEX Idx_2 (session, username, IP)
	)");
	if(!$b_table_created)
	{
		return;
	}
}
if(!$o_main->db->field_exists('system_admin', 'session_framework'))
{
	$o_main->db->query("ALTER TABLE session_framework ADD COLUMN system_admin TINYINT(4) NOT NULL DEFAULT '0' AFTER useradmin;");
}
if(!$o_main->db->field_exists('channel_creation_level_for_all_users', 'session_framework'))
{
	$o_main->db->query("ALTER TABLE session_framework ADD COLUMN channel_creation_level_for_all_users TINYINT(4) NOT NULL DEFAULT '0' AFTER returl;");
}
if(!$o_main->db->field_exists('account_edition', 'session_framework'))
{
	$o_main->db->query("ALTER TABLE session_framework ADD COLUMN account_edition INT(11) NOT NULL DEFAULT '0';");
}
if(!$o_main->db->field_exists('user_groups', 'session_framework'))
{
	$o_main->db->query("ALTER TABLE session_framework ADD COLUMN user_groups TEXT NOT NULL DEFAULT '' AFTER groupname;");
}
if(!$o_main->db->field_exists('force_cache_refresh', 'accountinfo'))
{
	$o_main->db->query("ALTER TABLE accountinfo ADD COLUMN force_cache_refresh DATETIME NULL DEFAULT NULL;");
}
if(!$o_main->db->field_exists('user_profile', 'session_framework'))
{
	$o_main->db->query("ALTER TABLE session_framework ADD COLUMN user_profile TEXT NOT NULL DEFAULT '';");
}
if(!$o_main->db->field_exists('style_set', 'session_framework'))
{
	$o_main->db->query("ALTER TABLE session_framework ADD COLUMN style_set TEXT NOT NULL DEFAULT '';");
}
if(!$o_main->db->field_exists('global_style_set', 'session_framework'))
{
	$o_main->db->query("ALTER TABLE session_framework ADD COLUMN global_style_set TEXT NOT NULL DEFAULT '';");
}
if(!$o_main->db->field_exists('invitation_config', 'session_framework'))
{
	$o_main->db->query("ALTER TABLE session_framework ADD COLUMN invitation_config TEXT NOT NULL DEFAULT '';");
}
if(!$o_main->db->field_exists('frontpage_config', 'session_framework'))
{
	$o_main->db->query("ALTER TABLE session_framework ADD COLUMN frontpage_config TEXT NOT NULL DEFAULT '';");
}
if(!$o_main->db->field_exists('content_server_api_url', 'session_framework'))
{
	$o_main->db->query("ALTER TABLE session_framework ADD COLUMN content_server_api_url TEXT NOT NULL DEFAULT '';");
}
if(!$o_main->db->field_exists('last_request_time', 'session_framework'))
{
	$o_main->db->query("ALTER TABLE session_framework ADD COLUMN last_request_time DATETIME NULL DEFAULT NULL;");
}
if(!$o_main->db->field_exists('connection_type', 'session_framework'))
{
	$o_main->db->query("ALTER TABLE session_framework ADD COLUMN connection_type TINYINT(4) NOT NULL DEFAULT '0';");
}
if(!$o_main->db->field_exists('app_name', 'session_framework'))
{
	$o_main->db->query("ALTER TABLE session_framework ADD COLUMN app_name TEXT NOT NULL DEFAULT '';");
	$o_main->db->query("ALTER TABLE session_framework ADD COLUMN app_version TEXT NOT NULL DEFAULT '';");
	$o_main->db->query("ALTER TABLE session_framework ADD COLUMN app_platform TEXT NOT NULL DEFAULT '';");
	$o_main->db->query("ALTER TABLE session_framework ADD COLUMN device_name TEXT NOT NULL DEFAULT '';");
	$o_main->db->query("ALTER TABLE session_framework ADD COLUMN device_year_class TEXT NOT NULL DEFAULT '';");
	$o_main->db->query("ALTER TABLE session_framework ADD COLUMN sdk_version TEXT NOT NULL DEFAULT '';");
	$o_main->db->query("ALTER TABLE session_framework ADD COLUMN browser_version TEXT NOT NULL DEFAULT '';");
	$o_main->db->query("ALTER TABLE session_framework ADD COLUMN screen_resolution TEXT NOT NULL DEFAULT '';");
}
if(!$o_main->db->field_exists('membership_tags', 'session_framework'))
{
	$o_main->db->query("ALTER TABLE session_framework ADD COLUMN membership_tags TEXT NOT NULL DEFAULT '';");
	$o_main->db->query("ALTER TABLE session_framework ADD COLUMN active_crm_subscription TINYINT(4) NOT NULL DEFAULT '0';");
}
if(!$o_main->db->field_exists('expired', 'session_framework'))
{
	$o_main->db->query("ALTER TABLE session_framework ADD COLUMN expired TINYINT(4) NOT NULL DEFAULT '0';");
}

$b_create = FALSE;
$v_fw_session = array();
$o_query = $o_main->db->query("SELECT *, TIME_TO_SEC(TIMEDIFF(NOW(), cache_timestamp)) refreshtime, cache_timestamp FROM session_framework WHERE companyaccessID = '".$o_main->db->escape_str($l_companyaccess_id)."' AND session = '".$o_main->db->escape_str($sessionID)."' AND username = '".$o_main->db->escape_str($username)."' LIMIT 1");
if($o_query && $o_query->num_rows()>0)
{
	$v_fw_session = $o_query->row_array();
	$b_session_created = TRUE;
} else {
	$b_create = TRUE;
}

$v_modules = array();
$s_sql = 'SELECT id moduleID, name modulename, externalurl external, moduledata.* FROM moduledata ORDER BY modulemode, ordernr';
$o_modules = $o_main->db->query($s_sql);
foreach($o_modules->result_array() as $v_row)
{
	$v_modules[$v_row['moduleID']] = $v_row;
}

$v_param = array(
	'COMPANYACCESS_ID'=>$l_companyaccess_id,
	'COMPANY_ID'=>$l_company_id,
	'ACCOUNTNAME'=>$v_accountinfo['accountname'],
	'PASSWORD'=>$v_accountinfo['password'],
	'MODULE_IDS'=>array_keys($v_modules),
	'CACHE_TIMESTAMP'=>$v_fw_session['cache_timestamp'],
	'CACHE_RECREATE'=>strtotime($v_accountinfo['force_cache_refresh']) > strtotime($v_fw_session['cache_timestamp'])
);
$v_connect = json_decode(APIconnectorUser('account_connect_v2', $username, $sessionID, $v_param), TRUE);

$v_connect_status = $v_connect['status'];

if($v_connect['cache_status'] != 2 && $v_connect_status == 1)
{
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
			$v_module_settings = getModuleName($v_module['modulename'], $v_connect['accountlanguageID']);
			$l_owner_access = ($v_access['owneraccess'] == 1 ? 1 : 0);

			$v_account_menu[$v_module['modulename']] = array(($v_module['local_name']!="" ? $v_module['local_name'] : ($v_module_settings[0]!='' ? $v_module_settings[0] : $v_module['modulename'])), 'module='.$v_module['modulename'].'&moduleID='.$v_module['moduleID'].'&modulename='.$v_module['modulename'].'&folder='.($v_module_settings[1]!=''?$v_module_settings[1]:'input').'&folderfile='.($v_module_settings[2]!=''?$v_module_settings[2]:'input').'&updatepath=1'.($v_module['external']!='' ? '&external='.$v_module['external'] : ''), $v_access['moduleAccesslevel'], $v_module['modulemode'], array('data'=>array($v_access)), ($v_module['deactivated']==1?1:0), $l_owner_access);
		}
	}

	$v_update = array(
		'cache_menu' => json_encode($v_account_menu),
		'cache_userstatus' => '',
		'cache_timestamp' => $v_connect['cache_timestamp'], //date("Y-m-d H:i:s", time()),
		'IP' => $_SERVER['REMOTE_ADDR'],
		'developerlanguageID' => ($v_connect['developerlanguageID'] != NULL ? $v_connect['developerlanguageID'] : ''),
		'accountlanguageID' => ($v_connect['accountlanguageID'] != NULL ? $v_connect['accountlanguageID'] : ''),
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
		//'connection_type' => 2, // 1 - desktop; 2 - mobile - this will be moved to API log_user_and_device_info
	);

	if($b_create)
	{
		$v_update['companyaccessID'] = $l_companyaccess_id;
		$v_update['session'] = $sessionID;
		$v_update['username'] = $username;
		$b_session_created = $o_main->db->insert('session_framework', $v_update);

	} else {
		$v_update_where = array(
			'companyaccessID' => $l_companyaccess_id,
			'session' => $sessionID,
			'username' => $username
		);
		$b_session_created = $o_main->db->update('session_framework', $v_update, $v_update_where);
	}

	$s_sql_fw_session = "SELECT *, TIME_TO_SEC(TIMEDIFF(NOW(), cache_timestamp)) refreshtime, cache_timestamp FROM session_framework WHERE companyaccessID = '".$o_main->db->escape_str($l_companyaccess_id)."' AND session = '".$o_main->db->escape_str($sessionID)."' AND username = '".$o_main->db->escape_str($username)."' LIMIT 1";
	$o_query = $o_main->db->query($s_sql_fw_session);
	$v_fw_session = $o_query ? $o_query->row_array() : array();
}

if($v_accountinfo['activate_crm_user_content_filtering_tags'])
{
	$l_membership_active_subscription = 0;
	$v_membership_tags = array();
	$v_properties = $v_property_groups = $v_group_properties = array();
	$s_sql = "SELECT cp.* FROM contactperson AS cp
	LEFT OUTER JOIN subscriptionmulti AS sm ON sm.customerId = cp.customerId AND sm.startDate <= CURDATE() AND (sm.stoppedDate = '0000-00-00' OR sm.stoppedDate is null OR sm.stoppedDate >= CURDATE())
	
	LEFT OUTER JOIN contactperson_subscription_connection AS csc ON csc.contactperson_id = cp.id
	LEFT OUTER JOIN subscriptionmulti AS sm2 ON csc.subscriptionmulti_id = sm2.id AND sm2.startDate <= CURDATE()
	AND (sm2.stoppedDate = '0000-00-00' OR sm2.stoppedDate is null OR sm2.stoppedDate >= CURDATE())
	
	WHERE cp.email = '".$o_main->db->escape_str($username)."' AND (
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
	
	$o_main->db->query("UPDATE session_framework SET expired = 1, membership_tags = '".$o_main->db->escape_str(json_encode($v_membership_tags))."', active_crm_subscription = '".$o_main->db->escape_str($l_membership_active_subscription)."' WHERE companyaccessID = '".$o_main->db->escape_str($l_companyaccess_id)."' AND session = '".$o_main->db->escape_str($sessionID)."' AND username = '".$o_main->db->escape_str($username)."'");
	
	$v_fw_session['membership_tags'] = json_encode($v_membership_tags);
	$v_fw_session['active_crm_subscription'] = $l_membership_active_subscription;
}

if(isset($v_data['logAppInfo']))
{
	$v_update = array(
		'connection_type' => (isset($v_data['logAppInfo']['isMobileApp']) && $v_data['logAppInfo']['isMobileApp']) ? 2 : 1, // 1 - desktop; 2 - mobile
		'app_name' => $v_data['logAppInfo']['appName'],
		'app_version' => $v_data['logAppInfo']['appVersion'],
		'app_platform' => $v_data['logAppInfo']['appPlatform'],
		'device_name' => $v_data['logAppInfo']['deviceName'],
		'device_year_class' => $v_data['logAppInfo']['deviceYearClass'],
		'sdk_version' => $v_data['logAppInfo']['sdkVersion'],
		'screen_resolution' => $v_data['logAppInfo']['screenResolution'],
	);
	$v_update_where = array(
		'companyaccessID' => $l_companyaccess_id,
		'session' => $sessionID,
		'username' => $username
	);
	$b_query = $o_main->db->update('session_framework', $v_update, $v_update_where);
}

if(!isset($v_data['doNotUpdateRequestTime']))
{
	$o_main->db->query("UPDATE session_framework SET last_request_time = NOW() WHERE companyaccessID = '".$o_main->db->escape_str($l_companyaccess_id)."' AND session = '".$o_main->db->escape_str($sessionID)."' AND username = '".$o_main->db->escape_str($username)."'");
}
