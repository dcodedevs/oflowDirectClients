<?php
ob_start();
$v_return = array(
	'status' => 0,
	'messages' => array(),
);

define('BASEPATH', realpath(__DIR__.'/../../../').'/');
require_once(BASEPATH.'elementsGlobal/cMain.php');
include_once(__DIR__."/includes/readOutputLanguage.php");

$v_input = $_SERVER['argv'];
list($s_script_path, $l_auto_task_id) = $v_input;
$s_sql = "SELECT at.*, atl.id AS auto_task_log_id FROM auto_task at JOIN auto_task_log atl ON atl.auto_task_id = at.id WHERE at.id = '".$o_main->db->escape_str($l_auto_task_id)."' AND atl.status = 1";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0)
{
	$v_auto_task = $o_query->row_array();
	$o_main->db->query("UPDATE auto_task_log SET status = 2, started = NOW() WHERE id = '".$o_main->db->escape_str($v_auto_task['auto_task_log_id'])."'");
	$v_auto_task_config = json_decode($v_auto_task['config'], TRUE);
	
	if(!class_exists('IntegrationSignant')) include(BASEPATH.'modules/IntegrationSignant/output/includes/class_IntegrationSignant.php');
	$o_signat = new IntegrationSignant();
			
	$s_sql = "SELECT * FROM integration_signant WHERE sign_status < 2 AND posting_id <> ''";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $v_signant)
	{
		$s_posting_id = $v_signant['posting_id'];
		$l_signant_id = $v_signant['id'];
		
		$v_response = $o_signat->getSignPostingStatusDetails($s_posting_id);
		if(isset($v_response['status']) && 1 == $v_response['status'])
		{
			$l_partly = $l_completed = $l_rejected = $l_total = 0;
			foreach($v_response['attachments'] as $v_item_attachment)
			{
				$l_total++;
				$s_sql = "SELECT * FROM integration_signant_attachment WHERE attachment_id = '".$o_main->db->escape_str($v_item_attachment['id'])."' AND attachment_group_id = '".$o_main->db->escape_str($v_item_attachment['group_id'])."'";
				$o_attachment = $o_main->db->query($s_sql);
				if($o_attachment && $o_attachment->num_rows()>0)
				{
					$v_attachment = $o_attachment->row_array();
					$l_count = $l_signed_recipients = $l_rejected_recipients = 0;
					foreach($v_item_attachment['recipients'] as $v_recipient)
					{
						$l_count++;
						if($v_recipient['signed']) $l_signed_recipients++;
						if($v_recipient['rejected']) $l_rejected_recipients++;
						
						$s_sql = "SELECT * FROM integration_signant_recipient WHERE signant_id = '".$o_main->db->escape_str($l_signant_id)."' AND email = '".$o_main->db->escape_str($v_recipient['email'])."'";
						$o_find = $o_main->db->query($s_sql);
						$l_recipient_id = (($o_find && $o_row = $o_find->row()) ? $o_row->id : 0);
						
						$s_sql = "SELECT * FROM integration_signant_attachment_recipient WHERE signant_recipient_id = '".$o_main->db->escape_str($v_recipient['id'])."' AND attachment_id = '".$o_main->db->escape_str($v_attachment['id'])."'";
						$o_attachment = $o_main->db->query($s_sql);
						if($o_attachment && $o_attachment->num_rows()>0)
						{
							$s_sql = "UPDATE integration_signant_attachment_recipient SET
							action_type = '".$o_main->db->escape_str($v_recipient['action_type'])."',
							action_last_date_time = '".$o_main->db->escape_str('' == $v_recipient['action_last_date_time'] ? '' : date('Y-m-d H:i:s', strtotime($v_recipient['action_last_date_time'])))."',
							action_status = '".$o_main->db->escape_str($v_recipient['action_status'])."',
							signed = '".$o_main->db->escape_str($v_recipient['signed'] ? 1 : 0)."',
							is_read = '".$o_main->db->escape_str($v_recipient['read'] ? 1 : 0)."',
							rejected = '".$o_main->db->escape_str($v_recipient['rejected'] ? 1 : 0)."',
							reject_reason = '".$o_main->db->escape_str($v_recipient['reject_reason'])."',
							last_reminder_date_time = '".$o_main->db->escape_str('' == $v_recipient['last_reminder_date_time'] ? '' : date('Y-m-d H:i:s', strtotime($v_recipient['last_reminder_date_time'])))."',
							signature_url = '".$o_main->db->escape_str($v_recipient['signature_url'])."',
							completed_date_time = '".$o_main->db->escape_str('' == $v_recipient['completed_date_time'] ? '' : date('Y-m-d H:i:s', strtotime($v_recipient['completed_date_time'])))."',
							recipient_id = '".$o_main->db->escape_str($l_recipient_id)."'
							WHERE signant_recipient_id = '".$o_main->db->escape_str($v_recipient['id'])."' AND attachment_id = '".$o_main->db->escape_str($v_attachment['id'])."'";
						} else {
							$s_sql = "INSERT INTO integration_signant_attachment_recipient SET
							action_type = '".$o_main->db->escape_str($v_recipient['action_type'])."',
							action_last_date_time = '".$o_main->db->escape_str('' == $v_recipient['action_last_date_time'] ? '' : date('Y-m-d H:i:s', strtotime($v_recipient['action_last_date_time'])))."',
							action_status = '".$o_main->db->escape_str($v_recipient['action_status'])."',
							signed = '".$o_main->db->escape_str($v_recipient['signed'] ? 1 : 0)."',
							is_read = '".$o_main->db->escape_str($v_recipient['read'] ? 1 : 0)."',
							rejected = '".$o_main->db->escape_str($v_recipient['rejected'] ? 1 : 0)."',
							reject_reason = '".$o_main->db->escape_str($v_recipient['reject_reason'])."',
							last_reminder_date_time = '".$o_main->db->escape_str('' == $v_recipient['last_reminder_date_time'] ? '' : date('Y-m-d H:i:s', strtotime($v_recipient['last_reminder_date_time'])))."',
							signature_url = '".$o_main->db->escape_str($v_recipient['signature_url'])."',
							completed_date_time = '".$o_main->db->escape_str('' == $v_recipient['completed_date_time'] ? '' : date('Y-m-d H:i:s', strtotime($v_recipient['completed_date_time'])))."',
							signant_recipient_id = '".$o_main->db->escape_str($v_recipient['id'])."',
							attachment_id = '".$o_main->db->escape_str($v_attachment['id'])."',
							recipient_id = '".$o_main->db->escape_str($l_recipient_id)."'";
						}
						$o_main->db->query($s_sql);
					}
					if(0 < $l_count && 0 < $l_signed_recipients)
					{
						$l_partly++;
					}
					if(0 < $l_count && $l_count == ($l_signed_recipients + $l_rejected_recipients))
					{
						$l_completed++;
					}
					if(0 < $l_rejected_recipients)
					{
						$l_rejected++;
					}
					if(strtotime($v_item_attachment['file_modified']) != strtotime($v_attachment['file_modified']))
					{
						$v_files = json_decode($v_attachment['file_original'], TRUE);
						$v_tmp = explode('.', $v_files[0][1][0]);
						$v_tmp[count($v_tmp)-2] .= '_signed';
						$v_files[0][1][0] = implode('.', $v_tmp);
						
						$v_response_attachment = $o_signat->downloadAttachment($s_posting_id, $v_attachment['attachment_id']);
						if(isset($v_response_attachment['status']) && 1 == $v_response_attachment['status'])
						{
							file_put_contents(BASEPATH.$v_files[0][1][0], $v_response_attachment['file_content']);
							$s_sql = "UPDATE integration_signant_attachment SET file_signed = '".$o_main->db->escape_str(json_encode($v_files))."'".('' == $v_item_attachment['file_modified'] ? '' : ", file_modified = '".$o_main->db->escape_str(date('Y-m-d H:i:s', strtotime($v_item_attachment['file_modified'])))."'")." WHERE id = '".$o_main->db->escape_str($v_attachment['id'])."'";
							$o_main->db->query($s_sql);
						}
					}
				}
			}
			if(0 < $l_partly) $l_status = 1;
			if(0 < $l_total && $l_completed == $l_total) $l_status = 2;
			if(2 == $l_status && 0 < $l_rejected) $l_status = 5;
			$o_main->db->query("UPDATE integration_signant SET sign_status = '".$o_main->db->escape_str($l_status)."', synced = NOW() WHERE id = '".$o_main->db->escape_str($l_signant_id)."'");
		}
	}
	
	/*$v_auto_task_config['repeat_minutes'] = intval($v_auto_task_config['repeat_minutes']);
	if(0 == $v_auto_task_config['repeat_minutes']) $v_auto_task_config['repeat_minutes'] = 1;
	
	$s_format = (!empty($v_auto_task_config['runtime_y'])?$v_auto_task_config['runtime_y']:'Y').'-'.
				(!empty($v_auto_task_config['runtime_m'])?$v_auto_task_config['runtime_m']:'m').'-'.
				(!empty($v_auto_task_config['runtime_d'])?$v_auto_task_config['runtime_d']:'d').' '.
				(!empty($v_auto_task_config['runtime_h'])?$v_auto_task_config['runtime_h']:'H').':'.
				(!empty($v_auto_task_config['runtime_i'])?$v_auto_task_config['runtime_i']:'i').':00';
	$s_date = date($s_format);
	$l_runtime = strtotime($s_date);
	while($l_runtime < time())
	{
		$l_runtime = $l_runtime + ($v_auto_task_config['repeat_minutes']*60);
	}*/
	
	$l_next_run = strtotime($v_auto_task['next_run']) + 3600;
	
	$o_main->db->query("UPDATE auto_task SET last_run = next_run, next_run = '".$o_main->db->escape_str(date("Y-m-d H:i:s", $l_next_run))."' WHERE id = '".$o_main->db->escape_str($v_auto_task['id'])."'");
	//status (0 - Idle (only for showing in web), 1 - Queued, 2 - Running, 3 - Completed)
	$o_main->db->query("UPDATE auto_task_log SET status = 3, finished = NOW(), message = '".$o_main->db->escape_str(json_encode(var_export($o_main->db, TRUE)))."' WHERE id = '".$o_main->db->escape_str($v_auto_task['auto_task_log_id'])."'");
	$v_return['status'] = 1;
} else {
	$v_return['messages'][] = $formText_AutoTaskCannotBeFound_Output;
}

ob_end_clean();
echo json_encode($v_return);