<?php
$choosenListInputLang = $_POST['choosenListInputLang'];
$extradir = __DIR__."/../../../";
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
include(__DIR__."/../../includes/readInputLanguage.php");

if(isset($_POST['type'])) $type = $_POST['type'];
else $type = 1;

$s_sql = "select count(est.id) sends, es.* from sys_emailsend es
	left outer join sys_emailsendto est on est.emailsend_id = es.id
	where es.id = ? and es.type = ?
	group by es.id
	order by es.send_on desc, es.id desc";
$o_query = $o_main->db->query($s_sql, array($_POST['id'], $type));
if(!$o_query || ($o_query && $o_query->num_rows() == 0)) return;
$v_data = $o_query->row_array();

$v_opens = $v_clicks = array();
$s_sql = "select count(tmp.cnt) cnt from (select count(opens.track_id) cnt from sys_emailsendto est
join sys_emailsendtrack opens on opens.track_id = est.track_id and (opens.track_action = 1 or opens.track_action = 3)
where est.emailsend_id = ?
group by est.id) tmp";
$o_query = $o_main->db->query($s_sql, array($v_data['id']));
$v_opens = $o_query->row_array();

$s_sql = "select count(tmp.cnt) cnt from (select count(clicks.track_id) cnt from sys_emailsendto est
join sys_emailsendtrack clicks on clicks.track_id = est.track_id and clicks.track_action = 4
where est.emailsend_id = ?
group by est.id) tmp";
$o_query = $o_main->db->query($s_sql, array($v_data['id']));
$v_clicks = $o_query->row_array();

$s_opens = $s_clicks = "['".date("d-M-y",strtotime("-1 days",strtotime($v_data['send_on'])))."',0]";
$s_sql = "select tmp.created, count(tmp.opens) opens from (select DATE(opens.created) created, count(opens.track_id) opens from sys_emailsendto est
join sys_emailsendtrack opens on opens.track_id = est.track_id and (opens.track_action = 1 or opens.track_action = 3)
where est.emailsend_id = ?
group by est.id) tmp group by tmp.created";
$o_query = $o_main->db->query($s_sql, array($v_data['id']));
if($o_query && $o_query->num_rows()>0)
foreach($o_query->result_array() as $v_row)
{
	$s_opens .= ",['".date("d-M-y",strtotime($v_row['created']))."',".$v_row['opens']."]";
}
$s_sql = "select tmp.created, count(tmp.clicks) clicks from (select DATE(clicks.created) created, count(clicks.track_id) clicks from sys_emailsendto est
join sys_emailsendtrack clicks on clicks.track_id = est.track_id and clicks.track_action = 4
where est.emailsend_id = ?
group by est.id) tmp group by tmp.created";
$o_query = $o_main->db->query($s_sql, array($v_data['id']));
if($o_query && $o_query->num_rows()>0)
foreach($o_query->result_array() as $v_row)
{
	$s_clicks .= ",['".date("d-M-y",strtotime($v_row['created']))."',".$v_row['clicks']."]";
}
$s_link_extra = "&choosenListInputLang=".$choosenListInputLang;
$b_stat_no_open = $b_stat_no_click = false;
if(isset($_POST['stat_no_open']))
{
	$b_stat_no_open = true;
	$s_link_extra .= "&stat_no_open=1";
}
if(isset($_POST['stat_no_click']))
{
	$b_stat_no_click = true;
	$s_link_extra .= "&stat_no_click=1";
}
$s_link_receivers = $_POST['dir']."ajax_receivers.php?emailsend_id=".$v_data['id'].$s_link_extra;
$s_link_open = $_POST['dir']."ajax_receivers.php?emailsend_id=".$v_data['id']."&order_field=opens&filter=opens".$s_link_extra;
$s_link_click = $_POST['dir']."ajax_receivers.php?emailsend_id=".$v_data['id']."&order_field=clicks&filter=clicks".$s_link_extra;
?>
<div class="e-report">
	<div class="e-main-text">
		<div><b><?php echo $formText_Report_fieldtype;?>:</b> <?php echo $v_data['subject'];?></div>
		<div><b><?php echo $formText_DateSent_fieldtype;?>:</b> <?php echo date("d.m.Y H:i",strtotime($v_data['send_on']));?></div>
	</div>
	<div class="e-statistic">
		<table border="0" width="100%">
		<tr class="e-text-large">
			<td>
				<a class="optimize" href="<?php echo $s_link_receivers;?>" data-target="#email-report-action-list"><?php echo ($v_data['sends']>0?$v_data['sends']:0);?></a>
			</td>
			<?php if(!$b_stat_no_open) { ?>
			<td>
				<a class="optimize" href="<?php echo $s_link_open;?>" data-target="#email-report-action-list"><?php echo ($v_opens['cnt']>0?$v_opens['cnt']:0);?></a>
			</td>
			<?php } ?>
			<?php if(!$b_stat_no_click) { ?>
			<td>
				<a class="optimize" href="<?php echo $s_link_click;?>" data-target="#email-report-action-list"><?php echo ($v_clicks['cnt']>0?$v_clicks['cnt']:0);?></a>
			</td>
			<?php } ?>
			<!--<td><?php echo ($v_data['bounced']>0?$v_data['bounced']:0);?></td>-->
		</tr>
		<tr>
			<td>
				<a class="optimize" href="<?php echo $s_link_receivers;?>" data-target="#email-report-action-list"><?php echo $formText_Receivers_fieldtype;?></a>
				<div style="font-size:9px;">
					<a class="optimize" href="<?php echo $s_link_receivers;?>" data-target="#email-report-action-list"><?php echo $formText_ClickToSeeList_Fieldtype;?></a>
				</div>
			</td>
			<?php if(!$b_stat_no_open) { ?>
			<td>
				<a class="optimize" href="<?php echo $s_link_open;?>" data-target="#email-report-action-list"><?php echo $formText_OpenedEmail_fieldtype;?></a>
				<div style="font-size:9px;">
					<a class="optimize" href="<?php echo $s_link_open;?>" data-target="#email-report-action-list"><?php echo $formText_ClickToSeeList_Fieldtype;?></a>
				</div>
			</td>
			<?php } ?>
			<?php if(!$b_stat_no_click) { ?>
			<td>
				<a class="optimize" href="<?php echo $s_link_click;?>" data-target="#email-report-action-list"><?php echo $formText_ClickedOnLinks_fieldtype;?></a>
				<div style="font-size:9px;">
					<a class="optimize" href="<?php echo $s_link_click;?>" data-target="#email-report-action-list"><?php echo $formText_ClickToSeeList_Fieldtype;?></a>
				</div>
			</td>
			<?php } ?>
			<!--<td><?php echo $formText_Bounced_fieldtype;?><span style="font-size:9px;"><?php echo $formText_ClickToSeeList_Fieldtype;?></div></td>-->
		</tr>
		</table>
	</div>
	<div id="email-report-action-list" class="refresh"></div>
	<div class="e-preview">
		<div class="e-title"><?php echo $formText_EmailContent_fieldtype;?></div>
		<div class="e-content"><?php echo str_replace("SYS_COMPANY_ACCESS_ID", $_POST['caID'], $v_data['text']);?></div>
	</div>
</div>
<script type="text/javascript">
$(function(){
	$('#email-report-action-list').on('click','#email-receivers-panel-search',function(){
		var _form = $(this).closest('form');
		fw_load_ajax($(_form).prop('action') + '&fwajax=1&' + $(_form).serialize(), $(_form).data('target'));
	});
});
</script>
<style>
#popupeditbox {
	padding:0 !important;
}
.e-report {
	max-width:1024px;
}
.e-report .e-main-text {
	font-size:16px;
	padding:10px 20px;
	border-bottom:1px solid #efecec;
	background-color:#f8f8f8;
}
.e-report .e-statistic {
	background-color:#fbfbfb;
	padding:25px 0;
}
.e-report .e-statistic td {
	text-align:center;
	color:#5f5d5d;
	width:20%;
	font-size:18px;
}
.e-report .e-statistic td a {
	color:#353a46;
}
.e-report .e-statistic .e-text-large td {
	font-size:36px;
	font-weight:bold;
}
.e-report .e-preview {
	border-top:1px solid #efecec;
	padding:10px 20px;
}	
.e-report .e-preview .e-title {
	font-size:14px;
	font-weight:500;
	padding:5px 0;
}
.e-report .e-preview .e-content {
	background-color:#f9f8f8;
	padding:20px;
	border:1px solid #efecec;
	border-radius:3px;
}
#email-report-action-list > div {
	margin:10px 20px;
}
.btn.btn-search {
    background-color: #27a1d5;
    border: 1px solid #27a1d5;
    border-radius: 3px;
    color: #ffffff;
    font-weight: bold;
}
</style>