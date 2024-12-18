<?php
ob_start();
$v_status_icon = array(0=>"time",1=>"ok-sign",2=>"exclamation-sign");
$v_status_text = array(0=>"",1=>"text-success",2=>"text-danger");
$choosenListInputLang = $_GET['choosenListInputLang'];
$extradir = __DIR__."/../../../";
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
include(__DIR__."/../../includes/readInputLanguage.php");

if(isset($_GET['emailsend_id']))
{
	$s_search = "";
	if(isset($_GET['search'])) $s_search = $o_main->db->escape_like_str($_GET['search']);
	$l_start_page = 0;
	if(isset($_GET['page'])) $l_start_page = intval($_GET['page']);
	$l_per_page = 500;
	$s_order = "est.status desc, est.perform_time";
	$s_extra_select = $s_extra_join = $s_extra_group = "";
	if(isset($_GET['order_field']))
	{
		if($_GET['order_field']=="opens" || $_GET['order_field']=="clicks")
		{
			$s_order = "cnt".($_GET['order']==1?"":" desc");
		}
		if($_GET['order_field']=="email")
		{
			$s_order = "est.receiver_email".($_GET['order']==1?"":" desc");
		}
		if($_GET['order_field']=="time")
		{
			$s_order = "est.perform_time".($_GET['order']==1?"":" desc");
		}
		if($_GET['order_field']=="status")
		{
			$s_order = "est.status".($_GET['order']==1?"":" desc");
		}
	}
	$s_sql = "select est.*, count(t.track_id) cnt from sys_emailsendto est
		".(($_GET['filter']=="opens" || $_GET['filter']=="clicks")?"":"left outer ")."join sys_emailsendtrack t on 
		t.track_id = est.track_id and ".($_GET['order_field']=="clicks" ? "t.track_action = 4" : "(t.track_action = 1 || t.track_action = 3)")."
		where est.emailsend_id = ".$o_main->db->escape($_GET['emailsend_id']).($s_search!=""?" and (est.receiver_email like '%".$s_search."%' ESCAPE '!' or est.receiver like '%".$s_search."%' ESCAPE '!')":"")."
		group by est.id";
	
	$l_total_amount = 0;
	$o_query = $o_main->db->query($s_sql);
	if($o_query) $l_total_amount = $o_query->num_rows();
	$l_total_pages = ceil($l_total_amount/$l_per_page);
	$s_sql .= " order by $s_order limit ".($l_start_page*$l_per_page).",".$l_per_page;
	$o_query = $o_main->db->query($s_sql);
	
	$b_show_extra1 = $b_show_extra2 = false;
	$o_check = $o_main->db->query("SELECT id FROM sys_emailsendto WHERE emailsend_id = ? AND extra1 <> '' LIMIT 1", array($_GET['emailsend_id']));
	if($o_check && $o_check->num_rows()>0) $b_show_extra1 = true;
	$o_check = $o_main->db->query("SELECT id FROM sys_emailsendto WHERE emailsend_id = ? AND extra2 <> '' LIMIT 1", array($_GET['emailsend_id']));
	if($o_check && $o_check->num_rows()>0) $b_show_extra2 = true;
}
$s_link_extra = "&choosenListInputLang=".$choosenListInputLang;
$b_stat_no_open = $b_stat_no_click = false;
if(isset($_GET['stat_no_open']))
{
	$b_stat_no_open = true;
	$s_link_extra .= "&stat_no_open=1";
}
if(isset($_GET['stat_no_click']))
{
	$b_stat_no_click = true;
	$s_link_extra .= "&stat_no_click=1";
}
?>
<div class="panel panel-default email-receivers-panel">
	<div class="panel-heading">
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td><?=$formText_ReceiverList_fieldtype;?></td>
			<td width="30%">
				<form action="<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=reports_receiver&folder=output&fw_nocss=1&emailsend_id=".$_GET['emailsend_id']."&choosenListInputLang=".$choosenListInputLang.(isset($_GET['page'])?"&page=".$l_start_page:"").(isset($_GET['order_field']) ? "&order_field=email".(empty($_GET['order'])?"":"&order=1"):"").(isset($_GET['filter'])?"&filter=".$_GET['filter']:"").$s_link_extra;?>" method="get" class="form-inline" data-target="#email-report-action-list">
				<input type="search" class="form-control input-sm" name="search" value="<?=$_GET['search'];?>" placeholder="<?=$formText_Search_fieldtype;?>" onkeypress="if(event.keyCode == 13) {$('#email-receivers-panel-search').trigger('click'); return false;}">
				<button id="email-receivers-panel-search" type="button" class="btn btn-sm btn-search" aria-label="<?=$formText_Search_fieldtype;?>">
					<span class="glyphicon glyphicon-search" aria-hidden="true"></span>
				</button>
				</form>
			</td>
			<td style="position:relative; width:30px;">
				<div style="border-left:1px solid #ccc; margin:-10px 0; position:absolute; content:''; top:0; left:0; height:51px; width:1px;"></div>
				<button type="button" class="close" onClick="$(this).closest('.email-receivers-panel').remove();"><span aria-hidden="true">&times;</span></button>
			</td>
		</tr>
		</table>
	</div>
	<?php
	if(isset($_GET['emailsend_id']))
	{
		?>
		<table class="table table-hover table-striped table-condensed">
		<thead>
			<tr>
				<th>#</th>
				<th><?=$formText_Name_fieldtype;?></th>
				<th><a class="optimize" href="<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=reports_receiver&folder=output&fw_nocss=1&emailsend_id=".$_GET['emailsend_id']."&order_field=email".(($_GET['order_field']=="email"&&empty($_GET['order']))?"&order=1":"").(isset($_GET['filter'])?"&filter=".$_GET['filter']:"").$s_link_extra;?>" data-target="#email-report-action-list"><?=$formText_Email_fieldtype;?></a></th>
				<?php if($b_show_extra1){?><th><?=$formText_Extra1_ReminderEmail;?></th><?php } ?>
				<?php if($b_show_extra2){?><th><?=$formText_Extra2_ReminderEmail;?></th><?php } ?>
				<th><a class="optimize" href="<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=reports_receiver&folder=output&fw_nocss=1&emailsend_id=".$_GET['emailsend_id']."&order_field=time".(($_GET['order_field']=="time"&&empty($_GET['order']))?"&order=1":"").(isset($_GET['filter'])?"&filter=".$_GET['filter']:"").$s_link_extra;?>" data-target="#email-report-action-list"><?=$formText_Sent_fieldtype;?></a></th>
				<?php if(!$b_stat_no_open) { ?>
				<th><?php
				if(!isset($_GET['filter']) || $_GET['filter'] == "opens")
				{
					?><a class="optimize" href="<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=reports_receiver&folder=output&fw_nocss=1&emailsend_id=".$_GET['emailsend_id']."&order_field=opens".(($_GET['order_field']=="opens"&&empty($_GET['order']))?"&order=1":"").(isset($_GET['filter'])?"&filter=".$_GET['filter']:"").$s_link_extra;?>" data-target="#email-report-action-list"><?=$formText_Opens_fieldtype;?></a><?php
				} else {
					print $formText_Opens_fieldtype;
				}
				?></th>
				<?php } ?>
				<?php if(!$b_stat_no_click) { ?>
				<th><?php
				if(!isset($_GET['filter']) || $_GET['filter'] == "clicks")
				{
					?><a class="optimize" href="<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=reports_receiver&folder=output&fw_nocss=1&emailsend_id=".$_GET['emailsend_id']."&order_field=clicks".(($_GET['order_field']=="clicks"&&empty($_GET['order']))?"&order=1":"").(isset($_GET['filter'])?"&filter=".$_GET['filter']:"").$s_link_extra;?>" data-target="#email-report-action-list"><?=$formText_Clicks_fieldtype;?></a><?php
				} else {
					print $formText_Clicks_fieldtype;
				}
				?></th>
				<?php } ?>
				<th><a class="optimize" href="<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=reports_receiver&folder=output&fw_nocss=1&emailsend_id=".$_GET['emailsend_id']."&order_field=status".(($_GET['order_field']=="status"&&empty($_GET['order']))?"&order=1":"").(isset($_GET['filter'])?"&filter=".$_GET['filter']:"").$s_link_extra;?>" data-target="#email-report-action-list"><?=$formText_Status_fieldtype;?></a></th>
			</tr>
		</thead>
		<tbody><?php
			$l_i = ($l_start_page*$l_per_page)+1;
			if($o_query && $o_query->num_rows()>0)
			foreach($o_query->result_array() as $v_row)
			{
				$v_opens = $v_clicks = array();
				$s_sql = "select count(track_id) cnt from sys_emailsendtrack where track_id = ? and (track_action = 1 or track_action = 3)";
				$o_find = $o_main->db->query($s_sql, array($v_row['track_id']));
				if($o_find && $o_find->num_rows()>0) $v_opens = $o_find->row_array();
				$s_sql = "select count(track_id) cnt from sys_emailsendtrack where track_id = ? and track_action = 4";
				$o_find = $o_main->db->query($s_sql, array($v_row['track_id']));
				if($o_find && $o_find->num_rows()>0) $v_opens = $o_find->row_array();
				?><tr>
					<td><?=$l_i;?></td>
					<td><?=$v_row['receiver'];?></td>
					<td><?=$v_row['receiver_email'];?></td>
					<?php if($b_show_extra1){?><td><?=$v_row['extra1'];?></td><?php } ?>
					<?php if($b_show_extra2){?><td><?=$v_row['extra2'];?></td><?php } ?>
					<td><?=($v_row['status']>0 ? date("d.m.Y H:i",strtotime($v_row['perform_time'])) : '');?></td>
					<?php if(!$b_stat_no_open) { ?>
					<td><?=($v_opens['cnt']>0?$v_opens['cnt']:0);?></td>
					<?php } ?>
					<?php if(!$b_stat_no_click) { ?>
					<td><?=($v_clicks['cnt']>0?$v_clicks['cnt']:0);?></td>
					<?php } ?>
					<td><span class="glyphicon glyphicon-<?=$v_status_icon[$v_row['status']];?> <?=$v_status_text[$v_row['status']];?>" aria-hidden="true"></span></td>
				</tr><?php
				$l_i++;
			}
		?>
		</tbody>
		</table><?php
		if($l_total_pages>1)
		{
			?>
			<div class="panel-body"><nav>
				<ul class="pagination pagination-sm">
					<?php
					if($l_start_page==0)
					{
						?><li class="disabled"><span><span aria-hidden="true">&laquo;</span></span></li><?php
					} else {
						?><li><a class="optimize" href="<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=reports_receiver&folder=output&fw_nocss=1&emailsend_id=".$_GET['emailsend_id']."&page=".($l_start_page-1).(isset($_GET['order_field']) ? "&order_field=email".(empty($_GET['order'])?"":"&order=1"):"").(isset($_GET['filter'])?"&filter=".$_GET['filter']:"").$s_link_extra;?>" data-target="#email-report-action-list" aria-label="<?=$formText_Previous_fieldtype;?>"><span aria-hidden="true">&laquo;</span></a></li><?php
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
								?><li><a class="optimize" href="<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=reports_receiver&folder=output&fw_nocss=1&emailsend_id=".$_GET['emailsend_id']."&page=".$l_x.(isset($_GET['order_field']) ? "&order_field=email".(empty($_GET['order'])?"":"&order=1").(isset($_GET['filter'])?"&filter=".$_GET['filter']:""):"").$s_link_extra;?>" data-target="#email-report-action-list"><?=($l_x+1);?></a></li><?php
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
						?><li><a class="optimize" href="<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=reports_receiver&folder=output&fw_nocss=1&emailsend_id=".$_GET['emailsend_id']."&page=".($l_start_page+1).(isset($_GET['order_field']) ? "&order_field=email".(empty($_GET['order'])?"":"&order=1"):"").(isset($_GET['filter'])?"&filter=".$_GET['filter']:"").$s_link_extra;?>" data-target="#email-report-action-list" aria-label="<?=$formText_Next_fieldtype;?>"><span aria-hidden="true">&raquo;</span></a></li><?php
					}
					?>
				</ul>
			</nav></div><?php
		}
	} else {
		?><div class="panel-body"><?=$formText_EmailNotFound_fieldtype;?></div><?php
	}
	?>
</div>
<?php
$return = array();
$return['html'] = ob_get_clean();
echo json_encode($return);
?>