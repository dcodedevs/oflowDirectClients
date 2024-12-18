<?php
$o_query = $o_main->db->get('accountinfo');
$v_accountinfo = $accountinfo = $o_query ? $o_query->row_array() : array();

if(1==0 && $v_accountinfo['activate_crm_user_content_filtering_tags']){
	if($v_accountinfo['crm_account_url'] != "" && $v_accountinfo['crm_access_token'] != ""  && $v_accountinfo['crm_account_module'] != "" ){
		// $sql = "UPDATE crm_user_content_filtering_tags SET synced = 0";
		// $o_query = $o_main->db->query($sql);
		// $sql = "UPDATE crm_user_content_filtering_tags_for_admin SET synced = 0";
		// $o_query = $o_main->db->query($sql);
		// $sql = "UPDATE crm_tag_and_group_connection SET synced = 0";
		// $o_query = $o_main->db->query($sql);
		//
		// $usernames = array();
		//
		// $usernames_array = array();
		// $o_query = $o_main->db->query("SELECT * FROM cache_userlist_membershipaccess_conn");
		// $v_cache_userlist = $o_query ? $o_query->result_array() : array();
		// foreach($v_cache_userlist as $v_user_cached_info) {
		// 	if(intval($v_user_cached_info['membersystemID']) > 0 && ($v_user_cached_info['membersystemmodule'] == "Customer2" || $v_user_cached_info['membersystemmodule'] == "customer")){
		// 		$usernames_array[$v_user_cached_info['username']][] = $v_user_cached_info['membersystemID'];
		// 	}
		// }
		//
		// $s_sql = "SELECT * FROM people";
		// $o_query = $o_main->db->query($s_sql, array());
		// $cached_userlist = $o_query ? $o_query->result_array() : array();
		// foreach($cached_userlist as $cache_user){
		// 	if(trim($cache_user['email']) != "") {
		// 		if(!in_array($cache_user['email'], $usernames)){
		// 			array_push($usernames, $cache_user['email']);
		// 		}
		// 	}
		// }
		// $params = array(
		// 	'api_url' => $v_accountinfo['crm_account_url'],
		// 	'access_token'=> $v_accountinfo['crm_access_token'],
		// 	'module' => $v_accountinfo['crm_account_module'],
		// 	'action' => 'get_crm_user_content_filtering_tags_info',
		// 	'params' => array(
		// 		'usernames' => $usernames,
		// 		'usernames_with_customers' => $usernames_array
		// 	)
		// );
		// $response = fw_api_call($params, false);
		// if($response['status']) {
		// 	$tag_infos = $response['data'];
		// 	foreach($tag_infos as $tag_info) {
		// 		$tag_username = $tag_info['username'];
		// 		$read_tags = $tag_info['tags_read'];
		// 		$read_groups = $tag_info['groups_read'];
		// 		$write_tags = $tag_info['tags_write'];
		// 		$write_groups = $tag_info['groups_write'];
		// 		$membership_settings = $tag_info['membership_settings'];
		// 		$doorcode_settings = $tag_info['doorcode_settings'];
		//
		// 		$tags['tags_read'] = $read_tags;
		// 		$tags['groups_read'] = $read_groups;
		// 		$tags['tags_write'] = $write_tags;
		// 		$tags['groups_write'] = $write_groups;
		//
		// 		$s_sql = "SELECT * FROM crm_user_content_filtering_tags WHERE username = ?";
		// 		$o_query = $o_main->db->query($s_sql, array($tag_username));
		// 		$tagItem = $o_query ? $o_query->row_array() : array();
		// 		if($tagItem) {
		// 			$sql = "UPDATE crm_user_content_filtering_tags SET
		// 			updated = now(),
		// 			updatedBy= ?,
		// 			username = ?,
		// 			tags = ?,
		// 			membership_settings = ?,
		// 			doorcode_settings = ?,
		// 			synced = 1
		// 			WHERE id = ?";
		//
		// 			$o_query = $o_main->db->query($sql, array($v_data['username'], $tag_username, json_encode($tags), json_encode($membership_settings), json_encode($doorcode_settings), $tagItem['id']));
		//
		// 		} else {
		// 			$sql = "INSERT INTO crm_user_content_filtering_tags SET
		// 			created = now(),
		// 			createdBy= ? ,
		// 			username = ?,
		// 			tags = ?,
		// 			membership_settings = ?,
		// 			doorcode_settings = ?,
		// 			synced = 1";
		//
		// 			$o_query = $o_main->db->query($sql, array($v_data['username'], $tag_username, json_encode($tags), json_encode($membership_settings), json_encode($doorcode_settings)));
		// 		}
		// 	}
		//
		// 	$all_tags = $response['all_tags'];
		// 	$all_groups = $response['all_groups'];
		//
		// 	$admin_tags = array();
		// 	$admin_tags['tags'] = $all_tags;
		// 	$admin_tags['groups'] = $all_groups;
		//
		// 	$s_sql = "SELECT * FROM crm_user_content_filtering_tags_for_admin WHERE tags = ?";
		// 	$o_query = $o_main->db->query($s_sql, array(json_encode($admin_tags)));
		// 	$adminTagItem = $o_query ? $o_query->row_array() : array();
		// 	if($adminTagItem) {
		// 		$sql = "UPDATE crm_user_content_filtering_tags_for_admin SET
		// 		updated = now(),
		// 		updatedBy= ?,
		// 		tags = ?,
		// 		synced = 1
		// 		WHERE id = ?";
		// 		$o_query = $o_main->db->query($sql, array($v_data['username'], json_encode($admin_tags), $adminTagItem['id']));
		// 	} else {
		// 		$sql = "INSERT INTO crm_user_content_filtering_tags_for_admin SET
		// 		created = now(),
		// 		createdBy= ? ,
		// 		tags = ?,
		// 		synced = 1";
		// 		$o_query = $o_main->db->query($sql, array($v_data['username'], json_encode($admin_tags)));
		// 	}
		//
		// 	$tag_group_connections = $response['tag_group_connection'];
		//
		// 	foreach($tag_group_connections as $tag_group_connection){
		// 		$s_sql = "SELECT * FROM crm_tag_and_group_connection WHERE tag_id = ? AND group_id = ?";
		// 		$o_query = $o_main->db->query($s_sql, array($tag_group_connection['tagId'], $tag_group_connection['groupId']));
		// 		$adminTagItem = $o_query ? $o_query->row_array() : array();
		// 		if($tagConnectionItem) {
		// 			$sql = "UPDATE crm_tag_and_group_connection SET
		// 			updated = now(),
		// 			updatedBy= ?,
		// 			tag_id = ?,
		// 			group_id = ?,
		// 			synced = 1
		// 			WHERE id = ?";
		// 			$o_query = $o_main->db->query($sql, array($v_data['username'], $tag_group_connection['tagId'], $tag_group_connection['groupId'], $tagConnectionItem['id']));
		// 		} else {
		// 			$sql = "INSERT INTO crm_tag_and_group_connection SET
		// 			created = now(),
		// 			createdBy= ?,
		// 			tag_id = ?,
		// 			group_id = ?,
		// 			synced = 1";
		// 			$o_query = $o_main->db->query($sql, array($v_data['username'], $tag_group_connection['tagId'], $tag_group_connection['groupId']));
		// 		}
		//
		// 	}
		//
		// 	$sql = "DELETE crm_user_content_filtering_tags FROM crm_user_content_filtering_tags WHERE synced = 0";
		// 	$o_query = $o_main->db->query($sql);
		// 	$sql = "DELETE crm_user_content_filtering_tags_for_admin FROM crm_user_content_filtering_tags_for_admin WHERE synced = 0";
		// 	$o_query = $o_main->db->query($sql);
		// 	$sql = "DELETE crm_tag_and_group_connection FROM crm_tag_and_group_connection WHERE synced = 0";
		// 	$o_query = $o_main->db->query($sql);
		// }
	}
}
