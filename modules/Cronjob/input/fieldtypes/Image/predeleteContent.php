<?php
$account_path = realpath(__DIR__."/../../../../../");
$s_sql = 'SELECT id, '.$o_main->db_escape_name($deleteFieldField).' FROM '.$o_main->db_escape_name($deleteFieldTable).' WHERE '.$o_main->db_escape_name($deleteFieldRelID).' = ?';
$o_query = $o_main->db->query($s_sql, array($deleteFieldID));
if($o_query && $o_query->num_rows()>0)
{
	foreach($o_query->result() as $o_row)
	{
		$jsondata = json_decode($o_row->$deleteFieldField,true);
		foreach($jsondata as $obj)
		{
			$delete_file_fail = false;
			$delete_file = $obj[1];
			foreach($delete_file as $delete_item)
			{
				if(is_file($account_path."/".$delete_item))
				{
					$uploads_dir = "/uploads/";
					if(strpos($delete_item,"uploads/protected/")!==false) $uploads_dir = "/uploads/protected/";
					if(strpos($delete_item,"uploads/storage/")!==false) $uploads_dir = "/uploads/storage/";
					$remove_path = str_replace($account_path.$uploads_dir,"",dirname($account_path."/".$delete_item));
					
					unlink($account_path."/".$delete_item);
					if(is_file($account_path."/".$delete_item))
					{
						$delete_file_fail = true;
					} else {
						// remove directory
						$remove_path = explode("/",$remove_path);
						while(count($remove_path)>0)
						{
							$remove_dir = array_pop($remove_path);
							rmdir($account_path.$uploads_dir.implode("/",$remove_path)."/".$remove_dir);
						}
					}
				}
			}
			if($delete_file_fail)
			{
				$error_msg["error_".count($error_msg)] = "Following file was not deleted for this content or sub-content: ".$obj[0];
			}
		}
	}
}
?>