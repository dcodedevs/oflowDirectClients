<?php
$choosenAdminLang = $_POST['languageID'];
$extradir = __DIR__."/../../../";
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
include(__DIR__."/../../includes/readInputLanguage.php");

if(isset($_POST['id'], $_POST['table'], $_POST['moduleid'], $_POST['time']))
{
	if(isset($_POST['type'])) $type = $_POST['type'];
	else $type = 1;
	
	$total = $count = 0;
	$perpage = 5000;
	$sql = "select est.*, DATE_FORMAT(est.perform_time, '%d.%m.%Y %H:%i') perform from sys_emailsendto est join sys_emailsend es on est.emailsend_id = es.id where es.content_id = ? and es.content_table = ? and es.content_module_id = ? and es.send_on = STR_TO_DATE(?,'%d-%m-%Y %H:%i') and es.type = ? ORDER BY est.receiver_email ASC";
	$o_query = $o_main->db->query($sql, array($_POST['id'], $_POST['table'], $_POST['moduleid'], $_POST['time'], $type));
	if($o_query) $total = $o_query->num_rows();
	if(isset($_POST['page'])) $page = intval($_POST['page']);
	else $page = 0;
	$sql = $sql." LIMIT ".($page*$perpage).", $perpage";
	$o_query = $o_main->db->query($sql, array($_POST['id'], $_POST['table'], $_POST['moduleid'], $_POST['time'], $type));
	if($o_query) $count = $o_query->num_rows();
	$totalPages = ceil($total / $perpage);
	
	print '<div class="'.$_POST['field_ui_id'].'_sumarize">'.$formText_totalUsers_fieldtype.' '.$total.'.'.
	($totalPages > 1 ? $formText_showing_fieldtype.' '.$formText_from_fieldtype.' '.(($page*$perpage)+1).' '.$formText_to_fieldtype.' '.($total > (($page*$perpage)+$perpage) ? (($page*$perpage)+$perpage) : $total) : '').
	'</div>';
	//print $sql;
	?><div class="<?php echo $_POST['field_ui_id'];?>_head"><span class="email"><?php echo $formText_Email_fieldtype;?></span><span class="name"><?php echo $formText_Name_fieldtype;?></span><span class="performed"><?php echo $formText_Sent_fieldtype;?></span><span class="status"><?php echo $formText_Status_fieldtype;?></span><span class="count"><?php echo $formText_EmailRead_fieldtype;?></span><span class="count"><?php echo $formText_LinksOpened_fieldtype;?></span></div><?php
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $user)
	{
		$read_count = $link_count = 0;
		$o_query = $o_main->db->query("SELECT track_id FROM sys_emailsendtrack WHERE track_id = ? AND track_action = 1 GROUP BY session_id", array($user['track_id']));
		if($o_query) $read_count = $o_query->num_rows();
		$o_query = $o_main->db->query("SELECT track_id FROM sys_emailsendtrack WHERE track_id = ? AND track_action = 4 GROUP BY link", array($user['track_id']));
		if($o_query) $link_count = $o_query->num_rows();
		?><div class="<?php echo $_POST['field_ui_id'];?>_report"><span class="email"><?php echo $user['receiver_email'];?></span><span class="name"><?php echo $user['receiver'];?></span><span class="performed"><?php echo ($user['status']>0 ? $user['perform'] : '');?></span><span class="status"><img src="<?php echo $_POST['dir'];?>images/status<?php echo $user['status'];?>.png" border="0" title="<?php echo ($user['status'] == 2 ? $formText_errorOccured_fieldtype.': '.$user['status_msg'] : '');?>" /> <?php echo ($user['status'] == 0 ? $formText_WaitingInQueue_fieldtype : ($user['status'] == 1 ? $formText_Sent_fieldtype : $formText_errorOccured_fieldtype));?></span><span class="count"><?php echo $read_count;?></span><span class="count"><?php echo $link_count;?></span></div><?php
	}
	
	
	if($totalPages > 1)
	{
		?><div class="<?php echo $_POST['field_ui_id'];?>_paging"><?php
		for($i = 0; $i < $totalPages; $i++)
		{
			print '<a class="script" href="javascript:;" onClick="show_report_'.$_POST['field_ui_id'].'(\''.$_POST['id'].'\', \''.$_POST['table'].'\', \''.$_POST['moduleid'].'\', \''.$_POST['time'].'\', \''.$i.'\');">'.($i+1).'</a>&nbsp;&nbsp;';
		}
		?></div><?php
	}
}
?>