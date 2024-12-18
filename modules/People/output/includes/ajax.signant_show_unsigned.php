<?php
if(1 != $accessElementAllow_SendFilesToSignant)
{
	return;
}

$s_signant_file = BASEPATH.'modules/IntegrationSignant/output/output_functions.php';
if(is_file($s_signant_file)) include($s_signant_file);

$v_sign_status = array(
	0 => $formText_NotSigned_Output,
	1 => $formText_PartlySigned_Output,
	2 => $formText_Signed_Output,
	3 => $formText_Canceled_Output,
	4 => $formText_Failure_Output,
	5 => $formText_Rejected_Output,
);

$v_membersystem = array();
$v_membersystem_membership = array();
$o_query = $o_main->db->query("SELECT * FROM cache_userlist_access");
$v_cache_userlist = $o_query ? $o_query->result_array() : array();
foreach($v_cache_userlist as $v_user_cached_info) {
	$v_membersystem[$v_user_cached_info['username']] = $v_user_cached_info;
}

$o_query = $o_main->db->query("SELECT * FROM cache_userlist_membershipaccess");
$v_cache_userlist_membership = $o_query ? $o_query->result_array() : array();
foreach($v_cache_userlist_membership as $v_user_cached_info) {
	$v_membersystem_membership[$v_user_cached_info['username']] = $v_user_cached_info;
}

?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<?php
	$s_sql = "SELECT s.*, p.name AS first_name, p.middlename AS middle_name, p.lastname AS last_name, p.email FROM people_files AS pf JOIN integration_signant AS s ON s.id = pf.signant_id LEFT OUTER JOIN contactperson AS p ON pf.peopleId = p.id WHERE s.content_status = 0 AND s.sign_status < 2 AND s.posting_id <> '' GROUP BY s.id ORDER BY s.created DESC";
	$o_query = $o_main->db->query($s_sql);
	
	?>
	<table class="table">
		<thead>
			<tr>
				<th><?php echo $formText_PersonName_Output; ?></th>
				<th><?php echo $formText_Document_Output; ?></th>
				<th><?php echo $formText_Created_Output; ?></th>
				<th><?php echo $formText_Status_output; ?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		$l_count = 0;
		if($o_query && $o_query->num_rows()>0)
		foreach($o_query->result_array() as $v_row)
		{
			$nameToDisplay = $v_row['first_name'].' '.$v_row['middle_name'].' '.$v_row['last_name'];
			if(isset($v_membersystem[$v_row['email']]) || isset($v_membersystem_membership[$v_row['email']]))
			{
				if(isset($v_membersystem[$v_row['email']])){
					$member = $v_membersystem[$v_row['email']];
				} else if(isset($v_membersystem_membership[$v_row['email']])){
					$member = $v_membersystem_membership[$v_row['email']];
				}
				if($member['user_id'] > 0)
				{
					if($member['image'] != "" && $member['image'] != null){
						$imgToDisplay = json_decode($member['image'], TRUE);
					}
					if($member['first_name'] != "") {
						$nameToDisplay = $member['first_name'] . " ". $member['middle_name']." ".$member['last_name'];
					}
					if($member['mobile'] != "") {
						$phoneToDisplay = $member['mobile'];
					}
				}
			}
			$s_file_field = 'file_original';
			$s_sql = "SELECT * FROM integration_signant_attachment WHERE signant_id = '".$o_main->db->escape_str($v_row['id'])."'";
			$o_attachment = $o_main->db->query($s_sql);
			$v_attachment = $o_attachment ? $o_attachment->row_array() : array();
			if(1 == $v_row['sign_status'] || 2 == $v_row['sign_status'])
			{
				$s_file_field = 'file_signed';
			}
			$v_files = json_decode($v_attachment[$s_file_field], TRUE);
			$s_download_url = $variables->account_root_url.$v_files[0][1][0].'?caID='.$_GET['caID'].'&table=integration_signant_attachment&field='.$s_file_field.'&ID='.$v_attachment['id'];
			?><tr>
				<td><?php echo preg_replace('/\s+/', ' ', $nameToDisplay); ?></td>
				<td><?php echo $v_row['name']; ?></td>
				<td><?php echo date('d.m.Y H:i',strtotime($v_row['created']));?></td>
				<td>
					<?php
					if(1 < $v_row['sign_status'])
					{
						echo '<span class="hoverEye">'.$v_sign_status[$v_row['sign_status']].integration_signant_get_status_details($v_row['id']).'</span>';
					} else {
						?><div class="signant-status load" data-id="<?php echo $v_row["id"];?>"><?php echo $formText_Checking_Output;?> <loading-dots>.</loading-dots></div><?php
					}
					?>
				</td>
			</tr><?php
			$l_count++;
		}
		?>
		</tbody>
	</table>
</div>
<script type="text/javascript">
$(function(){
	setTimeout(integration_signant_status_check, 800);
})
function integration_signant_status_check()
{
	var handle = $('.signant-status.load');
	if(handle.length > 0)
	{
		var obj = $(handle).get(0);
		var data = {
			output_form_submit: 1,
			id: $(obj).data('id'),
		};
		ajaxCall('sync_document', data, function(json) {
            if(json.data !== undefined)
			{
				if(json.data.download_url)
				{
					$(obj).closest('tr').find('a.download-url').attr('href', json.data.download_url);
				}
				if(json.data.s > 1)
				{
					$(obj).closest('tr').find('a.output-cancel-document').remove();
				}
				$(obj).replaceWith(json.data.sign_status);
			}
			integration_signant_status_check();
        }, false);
	}
}
</script>
<style>
.popupform input.popupforminput.checkbox {
	width: auto;
}
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
	border:1px solid #e8e8e8;
	position:relative;
}
label.error { display: none !important; }
input.error { border-color:#c11; }
#popup-validate-message, .error-msg { font-weight:bold; color:#c11; padding-bottom:10px; }
/* css for timepicker */
.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
.ui-timepicker-div dl { text-align: left; }
.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
.ui-timepicker-div td { font-size: 90%; }
.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
.clear {
	clear:both;
}
.inner {
	padding:10px;
}
.pplineV {
	position:absolute;
	top:0;bottom:0;left:70%;
	border-left:1px solid #e8e8e8;
}
.popupform input.popupforminput, .popupform textarea.popupforminput, .col-md-8z input {
	width:100%;
	border-radius: 4px;
	padding:5px 10px;
	font-size:12px;
	line-height:17px;
	color:#3c3c3f;
	background-color:transparent;
	-webkit-box-sizing: border-box;
	   -moz-box-sizing: border-box;
		 -o-box-sizing: border-box;
			box-sizing: border-box;
	font-weight:400;
	border: 1px solid #cccccc;
}
.popupformname {
	font-size:12px;
	font-weight:bold;
	padding:5px 0px;
}
.popupforminput.botspace {
	margin-bottom:10px;
}
textarea {
	min-height:50px;
	max-width:100%;
	min-width:100%;
	width:100%;
}
.popupformname {
	font-weight: 700;
	font-size: 13px;
}
.popupformbtn {
	text-align:right;
	margin:10px;
}
.popupformbtn input {
	border-radius: 4px;
	border:0px none;
	background-color:#0393ff;
	font-size:13px;
	line-height:0px;
	padding: 20px 35px;
	font-weight:700;
	color:#FFF;
}
.error {
	border: 1px solid #c11;
}
.popupform .lineTitle {
	font-weight:700;
}
.popupform .line .lineTitle {
	width:30%;
	float:left;
	font-weight:700;
	padding:5px 0;
}
.popupform .line .lineInput {
	width:70%;
	float:left;
}
</style>
