<?php
function fn_create_sync_index($o_main)
{
	$o_main->db->query("INSERT INTO sys_filearchive_sync_index SET moduleID = 0, createdBy = 'API', created = NOW(), content_status = 1");
	$l_sync_index = $o_main->db->insert_id();
	$v_structure = fn_create_sync_index_recursive($o_main);
	$o_main->db->query("UPDATE sys_filearchive_sync_index SET structure = ?, content_status = 0 WHERE id = ?", array(json_encode($v_structure), $l_sync_index));
	
	return true;
}

function fn_create_sync_index_recursive($o_main, $l_folder_id = 0, $s_path = '')
{
	$v_return = array();
	$s_sql = "SELECT * FROM sys_filearchive_folder WHERE parent_id = ? and content_status = 0 ORDER BY name";
	$o_query = $o_main->db->query($s_sql, array($l_folder_id));
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $v_folder)
	{
		$b_rename = $b_move = $b_delete = $b_store_items = 0;
		if($v_folder['disallow_rename'] == 1 || $v_folder['device_disallow_rename'] == 1) $b_store_items = 1;
		if($v_folder['disallow_move'] == 1 || $v_folder['device_disallow_move'] == 1) $b_store_items = 1;
		if($v_folder['disallow_delete'] == 1 || $v_folder['device_disallow_delete'] == 1) $b_store_items = 1;
		if($v_folder['disallow_store_items'] == 1 || $v_folder['device_disallow_store_items'] == 1) $b_store_items = 1;
		
		$v_return[] = array(
							'id' => $v_folder['id'],
							'version_id' => 0,
							'path' => $s_path.$v_folder['name'],
							'parent_id' => $v_folder['parent_id'],
							'type' => 0,
							'checksum' => 0,
							'disallow_rename' => $b_rename,
							'disallow_move' => $b_move,
							'disallow_delete' => $b_delete,
							'disallow_store_items' => $b_store_items
					);
		$v_return = array_merge($v_return, fn_create_sync_index_recursive($o_main, $v_folder['id'], $s_path.$v_folder['name'].'/'));
		
		$s_sql = "SELECT * FROM sys_filearchive_file WHERE folder_id = ? AND content_status = 0 ORDER BY sortnr";
		$o_query = $o_main->db->query($s_sql, array($v_folder['id']));
		if($o_query && $o_query->num_rows()>0)
		foreach($o_query->result_array() as $v_file)
		{
			$b_rename = $b_move = $b_delete = 0;
			if($v_file['disallow_rename'] == 1 || $v_file['device_disallow_rename'] == 1) $b_store_items = 1;
			if($v_file['disallow_move'] == 1 || $v_file['device_disallow_move'] == 1) $b_store_items = 1;
			if($v_file['disallow_delete'] == 1 || $v_file['device_disallow_delete'] == 1) $b_store_items = 1;
			
			$s_sql = "SELECT * FROM sys_filearchive_file_version WHERE file_id = ? AND status = 1 ORDER BY id DESC";
			$v_version = array();
			$o_query = $o_main->db->query($s_sql, array($v_file['id']));
			if($o_query && $o_query->num_rows()>0) $v_version = $o_query->row_array();
			
			$v_json = json_decode($v_version['file'], true);
			$v_tmp = explode("/", $v_json[0][1][0]);
			$s_file = array_pop($v_tmp);
			$v_return[] = array(
								'id' => $v_file['id'],
								'version_id' => $v_version['id'],
								'path' => $s_path.$v_folder['name'].'/'.$s_file,
								'folder_id' => $v_file['folder_id'],
								'type' => 1,
								'checksum' => $v_version['checksum'],
								'disallow_rename' => $b_rename,
								'disallow_move' => $b_move,
								'disallow_delete' => $b_delete
						);
		}
	}
	return $v_return;
}
?>