<?php
exit;
if (php_sapi_name() == "cli") {
    // In cli-mode
	error_reporting(E_ALL & ~E_NOTICE); ini_set("display_errors", 0);
} else {
    if($_SERVER['REMOTE_ADDR']!='87.110.235.137') {
		return;
	}
	echo 'run in CLI'; exit;
}

$s_lock_file = __DIR__.'/'.basename(__FILE__, '.php').'.lock';

if(cron_lock($s_lock_file))
{
	define('BASEPATH', __DIR__.DIRECTORY_SEPARATOR);
	require_once(BASEPATH.'elementsGlobal/cMain.php');


	//set_time_limit(0);
	$l_log_start = microtime(true);
	$l_log_number = floor($l_log_start);
	$l_log_fraction = round($l_log_start - $l_log_number, 4);
	$s_log_time = date("Y-m-d H:i:s", (int)$l_log_start).' '.$l_log_fraction;
	echo "\n".'STARTED: '.$s_log_time."\n\n";

	$v_skip_fields = array(
		'created',
		'createdBy',
		'updated',
		'updatedBy',
		'name_sort',
	);
	$s_first = '';
	$s_last = '';
	$o_query = $o_main->db->query("SELECT h.*, IF(c.id IS NULL, 1, 0) AS is_new FROM sys_content_history AS h LEFT JOIN sys_content_history_copy AS c ON c.id = h.id WHERE h.origId IS NULL ORDER BY h.id DESC LIMIT 70000");
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $v_hist)
	{
		$l_check = $v_hist['id'] / 100000;
		if((int)$l_check == $l_check) echo $v_hist['id']."\n";
		if('' == $s_first) $s_first = $v_hist['id'];
		$s_last = $v_hist['id'];
		if($o_main->db->table_exists($v_hist['content_table']))
		{
			$s_sql = "SELECT * FROM ".$o_main->db_escape_name($v_hist['content_table'])." WHERE id = '".$o_main->db->escape_str($v_hist['content_id'])."'";
			$o_find = $o_main->db->query($s_sql);
			if($o_find && $o_find->num_rows()>0)
			{
				$v_content = $o_find->row_array();
				$v_comp = json_decode(str_replace("\n", '\n', $v_hist['content_value']), TRUE);
				//print_r($v_comp);
				if(array_key_exists(0, $v_comp)) $v_comp = $v_comp[0];
				
				$o_find = $o_main->db->query("SELECT content_value FROM sys_content_history WHERE id > '".$o_main->db->escape_str($v_hist['id'])."' AND content_id = '".$o_main->db->escape_str($v_hist['content_id'])."' AND content_table = '".$o_main->db->escape_str($v_hist['content_table'])."' ORDER BY DESC");
				if($o_find && $o_find->num_rows()>0)
				{
					//Overwrite
					$v_hist2 = $o_find->row_array();
					$v_over = json_decode($v_hist2['content_value'], TRUE);
					if(isset($v_over[0])) $v_over = $v_over[0];
					foreach($v_over as $s_field => $s_value)
					{
						$v_content[$s_field] = $s_value;
					}
				}

				// Compare
				foreach($v_content as $s_field => $s_value)
				{
					if(array_key_exists($s_field, $v_comp))
					{
						if(in_array($s_field, $v_skip_fields)) unset($v_comp[$s_field]);
						if($s_value == $v_comp[$s_field])
						{
							if(1 == count($v_comp))
							{
								unset($v_comp[$s_field]);
								break;
							} else {
								unset($v_comp[$s_field]);
							}
						}
					}
				}
				//print_r($v_content);
				if(is_countable($v_comp) && 0 < count($v_comp))
				{
					/*foreach($v_comp as $s_field => $s_value)
					{
						echo $s_field.': '.$s_value.' -> '.$v_content[$s_field]."\n";
					}
					echo "--------\n\n";*/
					$v_hist['content_value'] = json_encode($v_comp);
					if(0 == $v_hist['is_new'])
					{
						$s_sql = "UPDATE sys_content_history_copy SET
						content_value = '".$o_main->db->escape_str($v_hist['content_value'])."'
						WHERE id = '".$o_main->db->escape_str($v_hist['id'])."'";
					} else {
						$s_sql = "INSERT INTO sys_content_history_copy SET
						id = '".$o_main->db->escape_str($v_hist['id'])."',
						created = '".$o_main->db->escape_str($v_hist['created'])."',
						content_id = '".$o_main->db->escape_str($v_hist['content_id'])."',
						content_table = '".$o_main->db->escape_str($v_hist['content_table'])."',
						content_value = '".$o_main->db->escape_str($v_hist['content_value'])."'";
					}
					$o_main->db->query($s_sql);
				}
			} else {
				//Content deleted
				if(0 == $v_hist['is_new'])
				{
					$s_sql = "UPDATE sys_content_history_copy SET
					content_value = '".$o_main->db->escape_str($v_hist['content_value'])."'
					WHERE id = '".$o_main->db->escape_str($v_hist['id'])."'";
				} else {
					$s_sql = "INSERT INTO sys_content_history_copy SET
					id = '".$o_main->db->escape_str($v_hist['id'])."',
					created = '".$o_main->db->escape_str($v_hist['created'])."',
					content_id = '".$o_main->db->escape_str($v_hist['content_id'])."',
					content_table = '".$o_main->db->escape_str($v_hist['content_table'])."',
					content_value = '".$o_main->db->escape_str($v_hist['content_value'])."'";
				}
				$o_main->db->query($s_sql);
			}
		} else {
			// Table deleted
		}
	}

	$s_sql = "UPDATE sys_content_history SET origId = 1, content_status = 0 WHERE id <= '".$o_main->db->escape_str($s_first)."' AND id >= '".$o_main->db->escape_str($s_last)."'";
	$o_main->db->query($s_sql);

	$l_log_end = microtime(true);
	$l_log_number = floor($l_log_end);
	$l_log_fraction = round($l_log_end - $l_log_number, 4);
	$s_log_time = date("Y-m-d H:i:s", (int)$l_log_end).' '.$l_log_fraction;
	echo "END_TIME: ".$s_log_time." (TIME: ".($l_log_end - $l_log_start)." seconds. First: ".$s_first.", Last: ".$s_last.")\n\n";
	
	cron_unlock($s_lock_file);
} else {
	echo "Locked\n";
}

function cron_lock($s_lock_file)
{
	if(is_file($s_lock_file)) return false;
	file_put_contents($s_lock_file,date('Y-m-d H:i',time()));
	return true;
}

function cron_unlock($s_lock_file)
{
	unlink($s_lock_file);
}