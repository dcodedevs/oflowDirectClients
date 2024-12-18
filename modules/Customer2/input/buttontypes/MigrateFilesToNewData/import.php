<?php

	// error_reporting(E_ALL);
	// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);
	if(isset($_POST['fixFiles'])) {
		$s_sql = "SELECT * FROM moduledata WHERE name = 'Customer2'";
		$o_query = $o_main->db->query($s_sql);
		$module_data = $o_query && $o_query->num_rows()>0 ? $o_query->row_array() : array();
		$moduleID = $module_data['uniqueID'];

		$content_table = 'subscriptionmulti#visible_only_in_here';
		$folders_data = array();
		$s_sql = "SELECT * FROM sys_filearchive_folder WHERE connected_content_table = ? ";
		$o_query = $o_main->db->query($s_sql, array($content_table));
		if($o_query && $o_query->num_rows()>0){
			$folders_data = $o_query->result_array();
		}
		foreach($folders_data as $folder_data){
			$folder_id = $folder_data['id'];
			$subscriptionmulti_id = $folder_data['connected_content_id'];


			if($subscriptionmulti_id > 0){
				$files = array();
				$s_sql = "SELECT * FROM sys_filearchive_file WHERE folder_id = ? AND content_status = '0'";
				$o_query = $o_main->db->query($s_sql, array($folder_id));
				$files = $o_query ? $o_query->result_array() : array();

				foreach($files as $file) {
					$s_sql = "SELECT * FROM sys_filearchive_file_version WHERE file_id = ? AND content_status = '0'";
					$o_query = $o_main->db->query($s_sql, array($file['id']));
					$file_entry = $o_query ? $o_query->row_array() : array();
					$s_sql = "INSERT INTO subscriptionmulti_files SET moduleID = ?, created = NOW(), createdBy = ?, subscriptionmulti_id = ?, file = ?";
		    		$o_query = $o_main->db->query($s_sql, array($moduleID, $variables->loggID, $subscriptionmulti_id, $file_entry['file']));
					if($o_query){
						$s_sql = "DELETE sys_filearchive_file, sys_filearchive_file_version FROM sys_filearchive_file
						LEFT OUTER JOIN sys_filearchive_file_version ON sys_filearchive_file_version.file_id = sys_filearchive_file.id
						WHERE sys_filearchive_file.id = ?";
						$o_query = $o_main->db->query($s_sql, array($file['id']));
					}
				}
			}
		}

		$content_table = 'subscriptionmulti#visible_for_customer';
		$folders_data = array();
		$s_sql = "SELECT * FROM sys_filearchive_folder WHERE connected_content_table = ? ";
		$o_query = $o_main->db->query($s_sql, array($content_table));
		if($o_query && $o_query->num_rows()>0){
			$folders_data = $o_query->result_array();
		}
		foreach($folders_data as $folder_data){
			$folder_id = $folder_data['id'];
			$subscriptionmulti_id = $folder_data['connected_content_id'];

			if($subscriptionmulti_id > 0){
				$files = array();
				$s_sql = "SELECT * FROM sys_filearchive_file WHERE folder_id = ? AND content_status = '0'";
				$o_query = $o_main->db->query($s_sql, array($folder_id));
				$files = $o_query ? $o_query->result_array() : array();

				foreach($files as $file) {
					$s_sql = "SELECT * FROM sys_filearchive_file_version WHERE file_id = ? AND content_status = '0'";
					$o_query = $o_main->db->query($s_sql, array($file['id']));
					$file_entry = $o_query ? $o_query->row_array() : array();
					$s_sql = "INSERT INTO subscriptionmulti_files SET moduleID = ?, created = NOW(), createdBy = ?, subscriptionmulti_id = ?, file = ?, show_to_customer = 1";
		    		$o_query = $o_main->db->query($s_sql, array($moduleID, $variables->loggID, $subscriptionmulti_id, $file_entry['file']));
					if($o_query){
						$s_sql = "DELETE sys_filearchive_file, sys_filearchive_file_version FROM sys_filearchive_file
						LEFT OUTER JOIN sys_filearchive_file_version ON sys_filearchive_file_version.file_id = sys_filearchive_file.id
						WHERE sys_filearchive_file.id = ?";
						$o_query = $o_main->db->query($s_sql, array($file['id']));
					}
				}
			}
		}
		$content_table = 'subscriptionmulti';
		$folders_data = array();
		$s_sql = "SELECT * FROM sys_filearchive_folder WHERE connected_content_table = ? ";
		$o_query = $o_main->db->query($s_sql, array($content_table));
		if($o_query && $o_query->num_rows()>0){
			$folders_data = $o_query->result_array();
		}
		foreach($folders_data as $folder_data){
			$folder_id = $folder_data['id'];
			$subscriptionmulti_id = $folder_data['connected_content_id'];

			if($subscriptionmulti_id > 0){
				$files = array();
				$s_sql = "SELECT * FROM sys_filearchive_file WHERE folder_id = ? AND content_status = '0'";
				$o_query = $o_main->db->query($s_sql, array($folder_id));
				$files = $o_query ? $o_query->result_array() : array();

				foreach($files as $file) {
					$s_sql = "SELECT * FROM sys_filearchive_file_version WHERE file_id = ? AND content_status = '0'";
					$o_query = $o_main->db->query($s_sql, array($file['id']));
					$file_entry = $o_query ? $o_query->row_array() : array();
					$s_sql = "INSERT INTO subscriptionmulti_files SET moduleID = ?, created = NOW(), createdBy = ?, subscriptionmulti_id = ?, file = ?";
		    		$o_query = $o_main->db->query($s_sql, array($moduleID, $variables->loggID, $subscriptionmulti_id, $file_entry['file']));
					if($o_query){
						$s_sql = "DELETE sys_filearchive_file, sys_filearchive_file_version FROM sys_filearchive_file
						LEFT OUTER JOIN sys_filearchive_file_version ON sys_filearchive_file_version.file_id = sys_filearchive_file.id
						WHERE sys_filearchive_file.id = ?";
						$o_query = $o_main->db->query($s_sql, array($file['id']));
					}
				}
			}
		}

		$folders_data = array();
		$s_sql = "SELECT * FROM repeatingorder_files";
		$o_query = $o_main->db->query($s_sql);
		if($o_query && $o_query->num_rows()>0){
			$folders_data = $o_query->result_array();
		}
		foreach($folders_data as $folder_data){
			$subscriptionmulti_id = $folder_data['subscription_id'];
			if($subscriptionmulti_id > 0){
				$s_sql = "INSERT INTO subscriptionmulti_files SET moduleID = ?, created = NOW(), createdBy = ?, subscriptionmulti_id = ?, file = ?, show_to_performer = 1";
				$o_query = $o_main->db->query($s_sql, array($moduleID, $variables->loggID, $subscriptionmulti_id, $folder_data['file']));
				if($o_query){
					$s_sql = "DELETE repeatingorder_files FROM repeatingorder_files WHERE id = ?";
					$o_query = $o_main->db->query($s_sql, array($folder_data['id']));
				}
			}
		}

		$content_table = 'customer';
		$folders_data = array();
		$s_sql = "SELECT * FROM sys_filearchive_folder WHERE connected_content_table = ? ";
		$o_query = $o_main->db->query($s_sql, array($content_table));
		if($o_query && $o_query->num_rows()>0){
			$folders_data = $o_query->result_array();
		}
		foreach($folders_data as $folder_data){
			$folder_id = $folder_data['id'];
			$subscriptionmulti_id = $folder_data['connected_content_id'];

			if($subscriptionmulti_id > 0){
				$files = array();
				$s_sql = "SELECT * FROM sys_filearchive_file WHERE folder_id = ? AND content_status = '0'";
				$o_query = $o_main->db->query($s_sql, array($folder_id));
				$files = $o_query ? $o_query->result_array() : array();

				foreach($files as $file) {
					$s_sql = "SELECT * FROM sys_filearchive_file_version WHERE file_id = ? AND content_status = '0'";
					$o_query = $o_main->db->query($s_sql, array($file['id']));
					$file_entry = $o_query ? $o_query->row_array() : array();
					$s_sql = "INSERT INTO customer_files SET moduleID = ?, created = NOW(), createdBy = ?, customer_id = ?, file = ?";
		    		$o_query = $o_main->db->query($s_sql, array($moduleID, $variables->loggID, $subscriptionmulti_id, $file_entry['file']));
					if($o_query){
						$s_sql = "DELETE sys_filearchive_file, sys_filearchive_file_version FROM sys_filearchive_file
						LEFT OUTER JOIN sys_filearchive_file_version ON sys_filearchive_file_version.file_id = sys_filearchive_file.id
						WHERE sys_filearchive_file.id = ?";
						$o_query = $o_main->db->query($s_sql, array($file['id']));
					}
				}
			}
		}

	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">

			<input type="submit" name="fixFiles" value="Move subscription files">

		</div>
	</form>
</div>
