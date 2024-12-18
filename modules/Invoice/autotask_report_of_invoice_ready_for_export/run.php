<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
ob_start();
$v_return = array(
	'status' => 0,
	'messages' => array(),
);
require_once('Exception.php');
require_once('PHPMailer.php');
require_once('SMTP.php');

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

	$time_for_launch = "H:i";
	if($v_auto_task_config['runtime_h'] != null){
		$time_for_launch = $v_auto_task_config['runtime_h'];

		if($v_auto_task_config['runtime_i'] != null){
			$time_for_launch .= ":".$v_auto_task_config['runtime_i'];
		} else {
			$time_for_launch .= ":i";
		}
	}

	if($v_auto_task_config['parameters']['time']['value'] != ""){
		$time_for_launch = $v_auto_task_config['parameters']['time']['value'];
	}
	function getNextExportInvoiceFromTo($o_main, $ownerCompanyId) {
	    // Min, max from invoice table
	    $sql = "SELECT MAX(external_invoice_nr) max_number,
	    MIN(external_invoice_nr) min_number,
	    MAX(id) max_id,
	    MIN(id) min_id,
	    COUNT(*) count
	    FROM invoice";
	    if ($ownerCompanyId) {
	        $sql .= " WHERE ownercompany_id = ?";
	    }
	    $o_query = $o_main->db->query($sql, array($ownerCompanyId));
	    $invoice_minmax = $o_query ? $o_query->row_array() : array();

	    // Last export
	    $sql = "SELECT MAX(invoiceNrTo) last_export_number,
	    MAX(invoiceIdTo) last_export_id,
	    COUNT(*) count
	    FROM invoice_export_history";
	    if ($ownerCompanyId) {
	        $sql .= " WHERE ownercompanyId = ?";
	    }
	    $o_query = $o_main->db->query($sql, array($ownerCompanyId));
	    $export_history_minmax = $o_query ? $o_query->row_array() : array();

	    // From id and number
	    if ($export_history_minmax['count']) {
	        $fromId = $export_history_minmax['last_export_id'] + 1;
	        $fromNumber = $export_history_minmax['last_export_number'] + 1;
	    } else {
	        $fromId = $invoice_minmax['min_id'];
	        $fromNumber = $invoice_minmax['min_number'];
	    }

	    // To id and number
	    $toId = $invoice_minmax['max_id'];
	    $toNumber = $invoice_minmax['max_number'];

	    // Has unexported invoices?
	    $hasUnexportedInvoices = $fromId <= $toId && $invoice_minmax['count'] > 0 ? 1 : 0;

	    // Return
	    $return = array (
	        'fromId' => $fromId,
	        'fromNumber' => $fromNumber,
	        'toId' => $toId,
	        'toNumber' => $toNumber,
	        'hasUnexportedInvoices' => $hasUnexportedInvoices
	    );

	    return $return;
	}
	$s_sql = "SELECT invoice_accountconfig.* FROM invoice_accountconfig ORDER BY id";
	$o_query = $o_main->db->query($s_sql);
	$invoice_accountconfig = ($o_query ? $o_query->row_array() : array());
	if($invoice_accountconfig['activateSendInvoiceExportMessage'] && $invoice_accountconfig['exportMessageSendToEmail'] != ""){
		$invoiceEmailsString = str_replace(";", ",", $invoice_accountconfig['exportMessageSendToEmail']);
		$invoiceEmails = explode(",", $invoiceEmailsString);


		$ownercompanies = array();
	    $o_query = $o_main->db->get('ownercompany');
	    if ($o_query && $o_query->num_rows()) {
	        foreach ($o_query->result_array() as $row) {
	            array_push($ownercompanies, $row);
	        }
	    }
		$sendEmail = false;
		$filesAttached = array();
		if ($invoice_accountconfig['activate_global_export']) {
			$currentOwnerCompanyId = 0;
			$nextExport = getNextExportInvoiceFromTo($o_main, $currentOwnerCompanyId);
			$fromId = $nextExport['fromId'];
			$fromNumber = $nextExport['fromNumber'];
			$toId = $nextExport['toId'];
			$toNumber = $nextExport['toNumber'];
			$hasUnexportedInvoices = $nextExport['hasUnexportedInvoices'];
			if($hasUnexportedInvoices){
				$sendEmail = true;
				$s_email_body .= $formText_InvoiceIds_output." ".$fromId." - ".$toId;

				//generate export
				if($invoice_accountconfig['activateSendExportFileWithMessage']){
					$o_query = $o_main->db->get('ownercompany');
					$ownercompany = $o_query ? $o_query->row_array() : array();

					if($ownercompany['exportScriptFolder'] != ""){
						$_GET['activate_global_export'] = $invoice_accountconfig['activate_global_export'];
						$_GET['ownercompany_id'] = $currentOwnerCompanyId;
						$_GET['from'] = $invoice_accountconfig['activate_global_export'] ? $fromId : $fromNumber;
						$_GET['to'] = $invoice_accountconfig['activate_global_export'] ? $toId : $toNumber;
						include(__DIR__."/../../OwnerCompany/output/includes/exportScripts/".basename($ownercompany['exportScriptFolder'])."/export.php");
						ob_get_clean();

						$sql = "SELECT h.*, oc.companyname ownerCompanyName,
					    oc.exportScriptFolder exportScriptFolder
					    FROM invoice_export_history h
					    LEFT JOIN ownercompany oc ON oc.id = h.ownerCompanyId
						WHERE h.ownerCompanyId = '".$currentOwnerCompanyId."'
					    ORDER by h.created DESC";
						$o_query = $o_main->db->query($sql);

						$invoice_export_history = ($o_query ? $o_query->row_array() : array());
						$file = json_decode($invoice_export_history['file'], true);
						if($file) {
							$filesAttached[] = $file[0][1][0];
						}
					}
				}
			}
		} else {
			foreach ($ownercompanies as $ownercompany) {
				$currentOwnerCompanyId = $ownercompany['id'];
				$nextExport = getNextExportInvoiceFromTo($o_main, $currentOwnerCompanyId);
				$fromId = $nextExport['fromId'];
				$fromNumber = $nextExport['fromNumber'];
				$toId = $nextExport['toId'];
				$toNumber = $nextExport['toNumber'];
				$hasUnexportedInvoices = $nextExport['hasUnexportedInvoices'];
				if($hasUnexportedInvoices) {
					$sendEmail = true;
					$s_email_body .= $ownercompany['name']." ".$formText_InvoiceNumbers_output." ".$fromNumber." - ".$toNumber."<br/>";

					//generate export
					if($invoice_accountconfig['activateSendExportFileWithMessage']){
						if($ownercompany['exportScriptFolder'] != ""){
							$_GET['activate_global_export'] = $invoice_accountconfig['activate_global_export'];
							$_GET['ownercompany_id'] = $currentOwnerCompanyId;
							$_GET['from'] = $invoice_accountconfig['activate_global_export'] ? $fromId : $fromNumber;
							$_GET['to'] = $invoice_accountconfig['activate_global_export'] ? $toId : $toNumber;
							include(__DIR__."/../../OwnerCompany/output/includes/exportScripts/".basename($ownercompany['exportScriptFolder'])."/export.php");
							ob_get_clean();

							$sql = "SELECT h.*, oc.companyname ownerCompanyName,
							oc.exportScriptFolder exportScriptFolder
							FROM invoice_export_history h
							LEFT JOIN ownercompany oc ON oc.id = h.ownerCompanyId
							WHERE h.ownerCompanyId = '".$currentOwnerCompanyId."'
							ORDER by h.created DESC";
							$o_query = $o_main->db->query($sql);

							$invoice_export_history = ($o_query ? $o_query->row_array() : array());
							$file = json_decode($invoice_export_history['file'], true);
							if($file) {
								$filesAttached[] = $file[0][1][0];
							}
						}
					}
				}
			}
		}

		if($sendEmail){
			foreach($invoiceEmails as $invoiceEmail) {
				if(trim($invoiceEmail) != ""){
					$s_receiver_email = $invoiceEmail;
					$s_receiver_name = $invoiceEmail;

					$o_query = $o_main->db->query("SELECT * FROM sys_emailserverconfig ORDER BY default_server DESC");
					$v_email_server_config = $o_query ? $o_query->row_array() : array();

					$s_email_body = '<h3>'.$formText_ThereAreInvoicesReadyForExport_AutoTask.'</h3>'.$s_email_body;

					$mail = new PHPMailer;
					$mail->CharSet	= 'UTF-8';
					if($v_email_server_config['host'] != "")
					{
						$mail->Host	= $v_email_server_config['host'];
						if($v_email_server_config['port'] != "") $mail->Port = $v_email_server_config['port'];

						if($v_email_server_config['username'] != "" and $v_email_server_config['password'] != "")
						{
							$mail->SMTPAuth	= true;
							$mail->Username	= $v_email_server_config['username'];
							$mail->Password	= $v_email_server_config['password'];

						}
					} else {
						$mail->Host = "mail.dcode.no";
					}

					$s_email_subject = $formText_ThereAreInvoicesReadyForExport_AutoTask;
					$s_sender_email = 'noreply@getynet.com';
					$s_sender_name = 'AutoTask';

					$mail->IsSMTP(true);
					$mail->From		= $s_sender_email;
					$mail->FromName	= $s_sender_name;
					$mail->Subject	= html_entity_decode($s_email_subject, ENT_QUOTES, 'UTF-8');
					$mail->Body		= $s_email_body;
					$mail->isHTML(true);
					$mail->AddAddress($s_receiver_email);
					if(count($filesAttached) > 0){
						foreach($filesAttached as $attached_file){
							$attachmentFile = __DIR__."/../../../".$attached_file;
							$mail->AddAttachment($attachmentFile);
						}
					}

					$l_send_status = 2;
					if($mail->Send())
					{
						$l_send_status = 1;
					}

					$sql = "INSERT INTO sys_emailsend SET created = NOW(), createdBy = 'AutoTask', send_on = NOW(), sender = '".$o_main->db->escape_str($s_sender_name)."', sender_email = '".$o_main->db->escape_str($s_sender_email)."', subject = '".$o_main->db->escape_str($s_email_subject)."', text = '".$o_main->db->escape_str($s_email_body)."'";
					$o_insert = $o_main->db->query($sql);
					if($o_insert)
					{
						$l_email_send_id = $o_main->db->insert_id();

						$sql = "INSERT INTO sys_emailsendto SET emailsend_id = '".$o_main->db->escape_str($l_email_send_id)."', receiver = '".$o_main->db->escape_str($s_receiver_name)."', receiver_email = '".$o_main->db->escape_str($s_receiver_email)."', extra1 = '', extra2 = '', `status` = '".$o_main->db->escape_str($l_send_status)."', status_message = '', perform_time = NOW(), perform_count = 1";
						$o_main->db->query($sql);
					}
				}
			}
		}
	}

	$l_next_run = strtotime($v_auto_task['next_run']) + 86400;
	$l_next_run = strtotime(date("d.m.Y ".$time_for_launch.":00", $l_next_run));

	$o_main->db->query("UPDATE auto_task SET last_run = next_run, next_run = '".$o_main->db->escape_str(date("Y.m.d H:i:s", $l_next_run))."'".$finishedStatusSql." WHERE id = '".$o_main->db->escape_str($v_auto_task['id'])."'");
	//status (0 - Idle (only for showing in web), 1 - Queued, 2 - Running, 3 - Completed)
	$o_main->db->query("UPDATE auto_task_log SET status = 3, finished = NOW(), message = ? WHERE id = '".$o_main->db->escape_str($v_auto_task['auto_task_log_id'])."'", array(''));

	$v_return['status'] = 1;
} else {
	$v_return['messages'][] = $formText_AutoTaskCannotBeFound_Output;
}

ob_end_clean();
echo json_encode($v_return);
