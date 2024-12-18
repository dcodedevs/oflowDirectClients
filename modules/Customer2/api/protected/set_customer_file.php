<?php
$l_customer_id = $v_data['params']['customer_id'];
if($l_customer_id > 0)
{
    $o_query = $o_main->db->query("SELECT * FROM customer WHERE content_status < 2 AND id = '".$o_main->db->escape_str($l_customer_id)."'");
	if($o_query && $o_query->num_rows()>0)
	{
		$v_customer = $o_query->row_array();
		
		$o_query = $o_main->db->query("SELECT * FROM moduledata WHERE name = 'Customer2'");
		$v_moduledata = $o_query ? $o_query->row_array() : array();
		
		$module_dir = __DIR__.'/../../';
		if(!function_exists("dirsizeexec")) include($module_dir."/input/fieldtypes/File/fn_dirsizeexec.php");
		if(!function_exists("mkdir_recursive")) include($module_dir."/input/fieldtypes/File/fn_mkdir_recursive.php");
		
		$v_files = array();
		if($_FILES["mailfile"]["error"] == UPLOAD_ERR_OK)
		{
			$o_main->db->query("INSERT INTO uploads SET created = NOW(), createdBy = '".$o_main->db->escape_str($v_data['username'])."', handle_status = 0, content_module_id = '".$o_main->db->escape_str($v_moduledata['uniqueID'])."', content_table = 'customer_files', content_field = 'file'");
			$l_upload_id = $o_main->db->insert_id();
			
			$s_tmp_name = $_FILES["mailfile"]["tmp_name"];
			$s_name = basename(rawurldecode($_FILES["mailfile"]["name"]));
			
			$uploads_dir = 'uploads/protected';
			$img_path = $uploads_dir.'/'.$l_upload_id.'/0/'.$s_name;
			$img_path_encoded = $uploads_dir.'/'.$l_upload_id.'/0/'.rawurlencode($s_name);
			mkdir_recursive(dirname(BASEPATH.$img_path));
			
			move_uploaded_file($s_tmp_name, BASEPATH.$img_path);
			if(is_file(BASEPATH.$img_path))
			{
				$v_files[] = array(
					$s_name,
					array($img_path_encoded),
					array(),
					"",
					$l_upload_id,
					"",
				);
			}
		}
		if(0 < sizeof($v_files))
		{
			$o_query = $o_main->db->query("INSERT INTO customer_files SET created = NOW(), createdBy = '".$o_main->db->escape_str($v_data['username'])."', moduleID = '".$o_main->db->escape_str($v_moduledata['uniqueID'])."', file = '".$o_main->db->escape_str(json_encode($v_files))."', customer_id = '".$o_main->db->escape_str($l_customer_id)."', folder_id = 0");
			if($o_query)
			{
				$v_return['status'] = 1;
			}
		}
	}
}