<?php
$choosenAdminLang = $_POST['languageID'];
$extradir = __DIR__."/../../../";
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
include(__DIR__."/../../includes/readInputLanguage.php");

$v_status_icon = array(1=>"time",2=>"ok-sign",3=>"exclamation-sign");
$v_status_text = array(1=>"",2=>"text-success",3=>"text-danger");

if(isset($_POST['id'], $_POST['table'], $_POST['moduleid'], $_POST['time']))
{
	if(isset($_POST['type'])) $type = $_POST['type'];
	else $type = 1;
	
	$row = array();
	$o_query = $o_main->db->query("select ss.* from sys_smssend ss join sys_smssendto sst on sst.smssend_id = ss.id where ss.content_id = ? and ss.content_table = ? and ss.content_module_id = ? and ss.send_on = STR_TO_DATE(?,'%d-%m-%Y %H:%i') and ss.type = ? group by ss.id", array($_POST['id'], $_POST['table'], $_POST['moduleid'], $_POST['time'], $type));
	if($o_query && $o_query->num_rows()>0) $row = $o_query->row_array();
	?><div><i><?php echo $row['message'];?></i></div><br/><br/><?php
	
	$b_show_extra1 = $b_show_extra2 = false;
	$o_check = $o_main->db->query("SELECT id FROM sys_smssendto WHERE smssend_id = ? AND extra1 <> '' LIMIT 1", array($row['id']));
	if($o_check && $o_check->num_rows()>0) $b_show_extra1 = true;
	$o_check = $o_main->db->query("SELECT id FROM sys_smssendto WHERE smssend_id = ? AND extra2 <> '' LIMIT 1", array($row['id']));
	if($o_check && $o_check->num_rows()>0) $b_show_extra2 = true;
	
	$total = $count = 0;
	$l_per_page = 500;
	$sql = "select sst.*, DATE_FORMAT(sst.perform_time, '%d.%m.%Y %H:%i') perform from sys_smssendto sst where sst.smssend_id = ? ORDER BY sst.receiver ASC";
	$o_query = $o_main->db->query($sql, array($row['id']));
	if($o_query) $total = $o_query->num_rows();
	$l_start_page = 0;
	if(isset($_POST['page'])) $l_start_page = intval($_POST['page']);
	$sql = $sql." LIMIT ".($l_start_page*$l_per_page).", $l_per_page";
	$o_query = $o_main->db->query($sql, array($_POST['id']));
	if($o_query) $count = $o_query->num_rows();
	$l_total_pages = ceil($total / $l_per_page);
	
	print '<div class="'.$_POST['field_ui_id'].'_sumarize">'.$formText_totalUsers_fieldtype.' '.$total.'.'.
	($l_total_pages > 1 ? $formText_showing_fieldtype.' '.$formText_from_fieldtype.' '.(($l_start_page*$l_per_page)+1).' '.$formText_to_fieldtype.' '.($total > (($l_start_page*$l_per_page)+$l_per_page) ? (($l_start_page*$l_per_page)+$l_per_page) : $total) : '').
	'</div>';
	?><table class="table table-hover table-striped table-condensed">
		<thead>
			<tr>
				<th>#</th>
				<th><?=$formText_Name_fieldtype;?></th>
				<th><?=$formText_Phone_fieldtype;?></th>
				<?php if($b_show_extra1){?><th><?=$formText_Extra1_ReminderSMS;?></th><?php } ?>
				<?php if($b_show_extra2){?><th><?=$formText_Extra2_ReminderSMS;?></th><?php } ?>
				<th><?=$formText_Sent_fieldtype;?></th>
				<th><?=$formText_Status_fieldtype;?></th>
			</tr>
		</thead>
		<tbody><?php
			$l_i = ($l_start_page*$l_per_page)+1;
			if($o_query && $o_query->num_rows()>0)
			foreach($o_query->result_array() as $v_row)
			{
				?><tr>
					<td><?=$l_i;?></td>
					<td><?=$v_row['receiver'];?></td>
					<td><?=$v_row['receiver_mobile'];?></td>
					<?php if($b_show_extra1>0){?><td><?=$v_row['extra1'];?></td><?php } ?>
					<?php if($b_show_extra2>0){?><td><?=$v_row['extra2'];?></td><?php } ?>
					<td><?=($v_row['status']>0 ? $v_row['perform'] : '');?></td>
					<td><span class="glyphicon glyphicon-<?=$v_status_icon[$v_row['status']];?> <?=$v_status_text[$v_row['status']];?>" aria-hidden="true"<?php echo ($v_row['status']==3 ? ' title="'.$formText_errorOccured_fieldtype.': '.str_replace('"',"'",$v_row['status_msg']).'" onClick="javascript:alert($(this).attr(\'title\'));"' : '');?>></span></td>
				</tr><?php
				$l_i++;
			}
		?>
		</tbody>
	</table>
	<?php
	if($l_total_pages > 1)
	{
		?>
		<div class="panel-body"><nav>
			<ul class="pagination pagination-sm">
				<?php
				if($l_start_page==0)
				{
					?><li class="disabled"><span><span aria-hidden="true">&laquo;</span></span></li><?php
				} else {
					?><li><a class="script" href="javascript:;" onClick="show_report_<?php echo $_POST['field_ui_id'].'(\''.$_POST['id'].'\', \''.$_POST['table'].'\', \''.$_POST['moduleid'].'\', \''.$_POST['time'].'\', \''.($l_start_page-1).'\');';?>" aria-label="<?=$formText_Previous_fieldtype;?>"><span aria-hidden="true">&laquo;</span></a></li><?php
				}
				for($l_x=0; $l_x < $l_total_pages; $l_x++)
				{
					if($l_x < 1 || ($l_x > ($l_start_page - 7) && $l_x < ($l_start_page + 7)) || $l_x >= ($l_total_pages - 1))
					{
						$b_print_space = true;
						if($l_x == $l_start_page)
						{
							?><li class="active"><span><?=($l_x+1);?></span></li><?php
						} else {
							?><li><a class="script" href="javascript:;" onClick="show_report_<?php echo $_POST['field_ui_id'].'(\''.$_POST['id'].'\', \''.$_POST['table'].'\', \''.$_POST['moduleid'].'\', \''.$_POST['time'].'\', \''.($l_x).'\');';?>" data-target="#email-report-action-list"><?=($l_x+1);?></a></li><?php
						}
					} else if($b_print_space) {
						$b_print_space = false;
						?><li><a class="script" onClick="javascript:return false;">...</a></li><?php
					}
				}
				if($l_start_page==($l_total_pages-1))
				{
					?><li class="disabled"><span><span aria-hidden="true">&raquo;</span></span></li><?php
				} else {
					?><li><a class="script" href="javascript:;" onClick="show_report_<?php echo $_POST['field_ui_id'].'(\''.$_POST['id'].'\', \''.$_POST['table'].'\', \''.$_POST['moduleid'].'\', \''.$_POST['time'].'\', \''.($l_start_page+1).'\');';?>" aria-label="<?=$formText_Next_fieldtype;?>"><span aria-hidden="true">&raquo;</span></a></li><?php
				}
				?>
			</ul>
		</nav></div><?php
	}
}
?>