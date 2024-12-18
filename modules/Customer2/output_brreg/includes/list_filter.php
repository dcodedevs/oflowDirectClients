<?php

$l_status = 4;
$v_status = array(
	$formText_Initialize_Output,
	$formText_Running_Output,
	$formText_SyncReportCreated_Output,
	$formText_ErrorOccurredOnSync_Output,
	$formText_PressButtonToCreateSyncReport_Output
);
$l_hour = 0;
$l_minute = 0;
$o_query = $o_main->db->query("SELECT * FROM sys_cronjob WHERE content_id = 1 AND script_path = '".$o_main->db->escape_str('modules/Customer2/output_brreg/cron_sync_brreg.php')."'");
if($o_query && $o_query->num_rows()>0)
{
	$v_sys_cronjob = $o_query->row_array();
	$l_status = $v_sys_cronjob['status'];
	$l_time = strtotime($v_sys_cronjob['perform_time']);
	$l_hour = date("H", $l_time);
	$l_minute = date("i", $l_time);
}


$l_delay = 10;
$o_query = $o_main->db->query("SELECT * FROM customer_accountconfig");
$v_accountinfo = $o_query ? $o_query->row_array() : array();
if(intval($v_accountinfo['brreg_sync_again_days'])>0) $l_delay = $v_accountinfo['brreg_sync_again_days'];
?>
<div class="p_tableFilter">
    <div class="p_tableFilter_left">
        <div><?php echo $formText_Status_Output;?>: <strong><?php echo $v_status[$l_status];?></strong></div>
    </div>
    <div class="p_tableFilter_right"<?php echo (($b_wait_for_result || $totalPages>0)?' style="display:none;"':'');?>>
        <form class="searchFilterForm" id="searchFilterForm">
            <?php /*?><label><?php echo $formText_Time_Output;?></label>
			<select name="hour">
			<?php
			for($l_i = 0; $l_i <= 23; $l_i++)
			{
				?><option value="<?php echo $l_i;?>"<?php echo ($l_i==$l_hour?' selected':'');?>><?php echo sprintf('%02d', $l_i);?></option><?php
			}
			?>
            </select>
			:
			<select name="minute">
			<?php
			for($l_i = 0; $l_i <= 59; $l_i++)
			{
				?><option value="<?php echo $l_i;?>"<?php echo ($l_i==$l_minute?' selected':'');?>><?php echo sprintf('%02d', $l_i);?></option><?php
			}
			?>
            </select><?php */?>
			<label><?php echo $formText_Synchronize_Output;?></label>:
			<select name="type">
				<option value="1"><?php echo $formText_WithActiveSubscription_Output;?></option>
				<option value="2"><?php echo $formText_AllCustomers_Output;?></option>
			</select>
			<label><?php echo $formText_CompareAgainAfterNumberOfDays_Output;?></label>:
			<input type="text" name="delay" value="<?php echo $l_delay;?>" style="width:50px !important;">
            <button id="p_tableFilterSearchBtn"><?php echo $formText_SyncWithBrreg_Output; ?></button>
        </form>
    </div>
</div>
<style>
    .filteredWrapper {
        margin-top: 10px;
    }
    .filterLine {
        display: inline-block;
        vertical-align: middle;
        margin-right: 15px;
    }
    .p_tableFilter_left {
        max-width: 60%;
        float: left;
    }
    .p_tableFilter_right {
        float: right;
    }
    .filteredRow {
        margin-top: 5px;
        margin-right: 5px;
        float: left;
        border: 1px solid #23527c;
        padding: 2px 5px;
        border-radius: 3px;
    }
    .filteredRow .filteredLabel{
        float: left;
    }
    .filteredRow .filteredValue{
        float: left;
        margin-left: 3px;
    }
    .filteredRow .filterRemove {
        float: right;
        font-size: 10px;
        line-height: 14px;
        margin-left: 10px;
        padding: 0px 3px 1px;
        cursor: pointer;
        color: #23527c;
    }
</style>
<script type="text/javascript">
    /*var delay = (function(){
        var timer = 0;
        return function(callback, ms){
            clearTimeout (timer);
            timer = setTimeout(callback, ms);
        };
    })();
    $(".searchFilter").keyup(function(){
        delay(function(){   
            var data = {                
                list_filter: '<?php echo $list_filter; ?>',
                search_filter: $('.searchFilter').val(),
                search_by: $(".searchBy").val(),
                updateOnlyList: true,            
            };
            ajaxCall('list', data, function(json) {
                $('.resultTableWrapper').html(json.html);
            });
        }, 500 );
    });*/
    $('.searchFilterForm').on('submit', function(e) {
        e.preventDefault();
        if(!fw_click_instance)
		{
			fw_click_instance = true;
			fw_loading_start();
			var data = {
				type: $(this).find('select[name="type"]').val(),
				delay: $(this).find('input[name="delay"]').val()
			};
			ajaxCall('schedule', data, function(json) {
				if(json.error !== undefined)
				{
					$.each(json.error, function(index, value){
						var _type = Array("error");
						if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
						fw_info_message_add(_type[0], value);
					});
					fw_info_message_show();
					fw_loading_end();
					fw_click_instance = false;
				} else {
					ajaxCall('list', data, function(json) {
						$('.p_pageContent').html(json.html);
						fw_loading_end();
						fw_click_instance = false;
					});
				}
			});
		}
    });
</script>
