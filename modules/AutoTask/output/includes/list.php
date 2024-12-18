<div id="p_container" class="p_container <?php echo $module;?>">
	<div class="p_containerInner">
		<div class="p_content">
			<h2><?php echo $formText_AvailableAutoTasks_Output;?></h2>
			<?php
			$s_path_modules = BASEPATH.'modules/';
			if($o_modules = opendir($s_path_modules)) 
			{
				while(false !== ($s_module = readdir($o_modules)))
				{
					if(is_dir($s_path_modules.$s_module) && '.' != $s_module && '..' != $s_module)
					{
						if($o_handle = opendir($s_path_modules.$s_module.'/')) 
						{
							while(false !== ($s_task = readdir($o_handle)))
							{
								if('autotask_' == substr($s_task, 0, 9))
								{
									?><div class="panel panel-default">
									<div class="panel-heading"><?php echo $s_module.' - '.$s_task;?> <a href="#" class="output-add-auto-task" data-module="<?php echo $s_module;?>" data-task="<?php echo $s_task;?>"><?php echo $formText_Add_Output;?></a></div><?php
									$v_statuses = array(
										$formText_Idle_Output,
										$formText_Queue_Output,
										$formText_Running_Output,
										$formText_Stopped_Output,
									);
									$s_sql = "SELECT * FROM auto_task WHERE script_path = '".$o_main->db->escape_str('modules/'.$s_module.'/'.$s_task.'/run.php')."' AND content_status = 0 ORDER BY next_run";
									$o_query = $o_main->db->query($s_sql);
									if($o_query && $o_query->num_rows()>0)
									{
										?><ul class="list-group"><?php
										foreach($o_query->result_array() as $v_row)
										{
											$o_find = $o_main->db->query("SELECT * FROM auto_task_log WHERE auto_task_id = '".$o_main->db->escape_str($v_row['id'])."' AND status <= 2 ORDER BY id");
											$v_auto_task_log = ($o_find ? $o_find->row_array() : array());
											$v_auto_task_log['status'] = intval($v_auto_task_log['status']);
											?><li class="list-group-item"><?php echo $formText_LastRun_Output.': '.((empty($v_row['last_run']) || '0000-00-00 00:00:00' == $v_row['last_run']) ? '-' : date("Y.m.d H:i", strtotime($v_row['last_run']))).' '.$formText_NextRun_Output.': '.date("Y.m.d H:i", strtotime($v_row['next_run'])).', '.$formText_Status_Output.': '.$v_statuses[$v_auto_task_log['status']];?> <a href="#" class="output-edit-auto-task" data-id="<?php echo $v_row['id'];?>"><?php echo $formText_Edit_Output;?></a> <a href="#" class="output-delete-auto-task" data-id="<?php echo $v_row['id'];?>"><?php echo $formText_Delete_Output;?></a></li><?php
										}
										?></ul><?php
									}
									?></div><?php
								}
							}
							closedir($o_handle); 
						}
					}
				}
				closedir($o_modules); 
			}
			?>
		</div>
	</div>
</div>

<script type="text/javascript">
var out_popup;
var out_popup_options={
	follow: [true, false],
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function(){
		$(this).addClass('opened');
		//$(this).find('.b-close').on('click', function(){out_popup.close();});
	},
	onClose: function(){
		if($(this).is('.close-reload')) {
			var redirectUrl = $(this).data("redirect");
			if(redirectUrl !== undefined && redirectUrl != ""){
				document.location.href = redirectUrl;
			} else {
            	loadView("list");
            }
        }
		$(this).removeClass('opened');
	}
};

$(document).ready(function(){
	$(".output-add-auto-task").on('click', function(e){
		e.preventDefault();
		var data = {
			cid: 0,
			task: $(this).data('task'),
			module: $(this).data('module'),
		};
		ajaxCall('edit_task', data, function(json) {
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".output-edit-auto-task").on('click', function(e){
		e.preventDefault();
		var data = {
			cid: $(this).data('id'),
		};
		ajaxCall('edit_task', data, function(json) {
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".output-delete-auto-task").on('click', function(e){
		e.preventDefault();
		var data = {
			cid: $(this).data('id'),
			output_form_submit: 1,
			delete_item: 1,
		};
		fw_info_message_empty();
		bootbox.confirm('<?php echo $formText_AreYouSureYouWantToDeleteItem_output; ?>?', function(result) {
			if (result) {
				ajaxCall('edit_task', data, function(json) {
					if(json.error !== undefined)
                    {
						$.each(json.error, function(index, value){
							var _type = Array("error");
							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
							fw_info_message_add(_type[0], value);
						});
						fw_info_message_show();
					} else {
						loadView("list");
					}
				});
			}
		});
	});
});
</script>