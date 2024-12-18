<?php
$content_id = $_POST['id'];
$content_table = $_POST['table'];

$v_fields = array();
$o_query = $o_main->db->query("SELECT * FROM sys_content_history_basisconfig WHERE name = '".$o_main->db->escape_str($_POST['table'])."'");
$v_content_history_basisconfig = $o_query ? $o_query->row_array() : array();
if(isset($v_content_history_basisconfig['id']))
{
	$v_json = json_decode($v_content_history_basisconfig['field_config'], TRUE);
	if(FALSE !== $v_json && 0 < sizeof($v_json))
	{
		$v_fields = $v_json;
	}
}
$v_fields_account = array();
$o_query = $o_main->db->query("SELECT * FROM sys_content_history_accountconfig WHERE name = '".$o_main->db->escape_str($_POST['table'])."'");
$v_content_history_accountconfig= $o_query ? $o_query->row_array() : array();
if(isset($v_content_history_accountconfig['id']))
{
	$v_json = json_decode($v_content_history_accountconfig['field_config'], TRUE);
	if(FALSE !== $v_json && 0 < sizeof($v_json))
	{
		$v_fields = $v_json;
	}
}

$s_sql = "SELECT * FROM sys_content_history WHERE content_id = '".$o_main->db->escape_str($content_id)."' AND content_table = '".$o_main->db->escape_str($content_table)."' ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0)
{
	?>
	<div class="popupform">
		<div id="popup-validate-message" style="display:none;"></div>
		<div class="reportform-container">
		<table class="table table-fixed">
			<tr>
				<?php
				$b_created = $b_created_by = FALSE;
				foreach($v_fields as $s_field_id)
				{
					if($s_field_id == 'created' || $s_field_id == 'updated')
					{
						if(!$b_created)
						{
							$b_created = TRUE;
							?><td><?php echo $formText_EditedDate_Output;?></td><?php
						}
					} else if($s_field_id == 'createdBy' || $s_field_id == 'updatedBy')
					{
						if(!$b_created_by)
						{
							$b_created_by = TRUE;
							?><td><?php echo $formText_EditedByUser_Output;?></td><?php
						}
					} else {
						?><td><?php echo $s_field_id;?></td><?php
					}
				}
				?>
			</tr>
			<?php
			foreach($o_query->result_array() as $v_row)
			{
				$v_content = json_decode($v_row['content_value'], TRUE);
				$v_content = array_pop($v_content);
				?>
				<tr>
					<?php
					$b_created = $b_created_by = FALSE;
					foreach($v_fields as $s_field_id)
					{
						if($s_field_id == 'created' || $s_field_id == 'updated')
						{
							if(!$b_created)
							{
								$b_created = TRUE;
								$s_created = '';
								if('' != $v_content['created'] && '0000-00-00 00:00:00' != $v_content['created'])
								{
									$s_created = date('d.m.Y H:i', strtotime($v_content['created']));
								}
								if('' != $v_content['updated'] && '0000-00-00 00:00:00' != $v_content['updated'])
								{
									$s_created = date('d.m.Y H:i', strtotime($v_content['updated']));
								}
								?><td><?php echo $s_created;?></td><?php
							}
						} else if($s_field_id == 'createdBy' || $s_field_id == 'updatedBy')
						{
							if(!$b_created_by)
							{
								$b_created_by = TRUE;
								$s_created_by = '';
								if('' != $v_content['created'] && '0000-00-00 00:00:00' != $v_content['created'])
								{
									$s_created_by = $v_content['createdBy'];
								}
								if('' != $v_content['updated'] && '0000-00-00 00:00:00' != $v_content['updated'])
								{
									$s_created_by = $v_content['updatedBy'];
								}
								?><td><?php echo $s_created_by;?></td><?php
							}
						} else {
							?><td><?php echo $v_content[$s_field_id];?></td><?php
						}
					}
					?>
				</tr>
				<?php
			}
			?>
		</table>
		<div class="form-group">
			<div class="popupformbtn">
				<button type="button" class="output-btn b-large output-edit-content-history-config"><?php echo $formText_EditConfiguration_Output;?></button>
				<button type="button" class="output-btn b-large b-close"><?php echo $formText_Close_Output;?></button>
			</div>
		</div>
	</div>
	<?php
}
?>
<script type="text/javascript">
$(function(){
	$('.output-edit-content-history-config').off('click').on('click', function(e) {
        e.preventDefault();
        fw_loading_start();
		var data = {
            id: '<?php echo $content_id;?>',
            table: '<?php echo $content_table;?>',
        };
        ajaxCall('edit_content_history_config', data, function(json) {
			$('#popupeditboxcontent2').html(json.html);
			out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
			$("#popupeditbox2:not(.opened)").remove();
			if (typeof(callback) === 'function') callback();
        });
    });
});
</script>
<style>
.popupform {
    border: 0;
}
</style>
