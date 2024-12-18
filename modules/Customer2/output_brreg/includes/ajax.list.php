<?php
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

if(isset($_POST['page'])) {
	$page = $_POST['page'];
}
if(intval($page) == 0){
	$page = 1;
}
$rowOnly = $_POST['rowOnly'];
$perPage = 100;
$showing = $page * $perPage;
$showMore = false;

$v_fields = array(
	'name',
	'paStreet',
	'paPostalNumber',
	'paCity',
	'paCountry',
	'vaStreet',
	'vaPostalNumber',
	'vaCity',
	'vaCountry'
);
$v_labels = array(
	'name' => $formText_Name_Output,
	'paStreet' => $formText_PaStreet_Output,
	'paPostalNumber' => $formText_PaPostalNumber_Output,
	'paCity' => $formText_PaCity_Output,
	'paCountry' => $formText_PaCountry_Output,
	'vaStreet' => $formText_VaStreet_Output,
	'vaPostalNumber' => $formText_VaPostalNumber_Output,
	'vaCity' => $formText_VaCity_Output,
	'vaCountry' => $formText_VaCountry_Output,
);

$s_sql_fields = '';
foreach($v_fields as $s_field)
{
	$s_sql_fields .= ", COALESCE(MAX(CASE WHEN csd.field = '".$o_main->db->escape_str($s_field)."' THEN csd.brreg_value END), '[NULL]') AS difi_".$s_field;
}

$s_sql = "SELECT c.*, csd.customer_id".$s_sql_fields." FROM customer c JOIN customer_sync_data csd ON csd.customer_id = c.id GROUP BY csd.customer_id ORDER BY csd.customer_id";
$o_query = $o_main->db->query($s_sql);
$l_total_count = ($o_query?$o_query->num_rows():0);
$l_page_count = 0;
$totalPages = ceil($l_total_count/$perPage);

$b_wait_for_result = FALSE;
$o_query = $o_main->db->query("SELECT * FROM sys_cronjob WHERE content_id = 1 AND script_path = '".$o_main->db->escape_str('modules/Customer2/output_brreg/cron_sync_brreg.php')."'");
if($o_query && $o_query->num_rows()>0)
{
	$v_sys_cronjob = $o_query->row_array();
	if($v_sys_cronjob['status'] <= 1)
	{
		$b_wait_for_result = TRUE;
	}
}

include(__DIR__."/list_filter.php");


?>
<div class="resultTableWrapper">
<div class="gtable" id="gtable_search">
	<div class="gtable_row">
		<?php /*?><div class="gtable_cell gtable_cell_head c0"><input type="checkbox" class="selection-switch main" value="" checked><label class="selection-switch-btn main"></label></div><?php */?>
		<div class="gtable_cell gtable_cell_head c0"><?php echo $formText_SyncNow_Output;?></div>
		<div class="gtable_cell gtable_cell_head c0"><?php echo $formText_SkipThisTime_Output;?></div>
		<div class="gtable_cell gtable_cell_head c0"><?php echo $formText_NeverSync_Output;?></div>
		<div class="gtable_cell gtable_cell_head"><?php echo $formText_PublicRegisterId_Output;?></div>
		<div class="gtable_cell gtable_cell_head"><?php echo $formText_Differences_Output;?></div>
	</div>
    <?php
	if(!$b_wait_for_result)
	{
		$offset = ($page-1)*$perPage;
		if($offset < 0){
			$offset = 0;
		}
		$o_query = $o_main->db->query($s_sql." LIMIT ".$perPage." OFFSET ".$offset);
		if($o_query && $o_query->num_rows()>0)
		foreach($o_query->result_array() as $v_row)
		{
			$s_difference = '';
			foreach($v_fields as $s_field)
			{
				$b_same = $v_row['difi_'.$s_field] == '[NULL]';
				$s_difference .= '<div class="contactcell_row'.(!$b_same?' diff':'').'">';
				$s_difference .= '<div class="contactcell_cell cc1">'.$v_labels[$s_field].'</div>';
				$s_difference .= '<div class="contactcell_cell cc2">'.$v_row[$s_field].'</div>';
				$s_difference .= '<div class="contactcell_cell cc3">'.($b_same ? $v_row[$s_field] : $v_row['difi_'.$s_field]).'</div>';
				$s_difference .= '</div>';
			}
			?>
			<div class="gtable_row output-row-selection">
				<div class="gtable_cell c1"><input type="checkbox" class="selection-switch sync" value="<?php echo $v_row['customer_id'];?>" checked><label class="selection-switch-btn"></label></div>
				<div class="gtable_cell c1"><input type="checkbox" class="selection-switch skip" value="<?php echo $v_row['customer_id'];?>"><label class="selection-switch-btn"></label></div>
				<div class="gtable_cell c1"><input type="checkbox" class="selection-switch nosync" value="<?php echo $v_row['customer_id'];?>"><label class="selection-switch-btn"></label></div>
				<div class="gtable_cell c2"><?php echo $v_row['publicRegisterId'];?></div>
				<div class="gtable_cell c3"><?php echo $s_difference;?></div>
			</div>
			<?php
			$l_page_count++;
		}
	} else {
		?>
		<div class="gtable_row">
			<div class="gtable_cell c1">&nbsp;</div>
			<div class="gtable_cell c1">&nbsp;</div>
			<div class="gtable_cell c1">&nbsp;</div>
			<div class="gtable_cell c2">
				<div><center><?php echo $formText_SynchronizationInProgress_Output.'. '.$formText_ResultsWillBeShownAfterFinishingSync_Output;?>.</center></div>
				<div><center><button type="button" class="output-reload-results output-btn"><?php echo $formText_ClickHereToReload_Output;?></button></center></div>
			</div>
			<div class="gtable_cell c3">&nbsp;</div>
		</div>
		<?php
	}
	?>
</div>
<?php if(!$b_wait_for_result) { ?>
<div><?php echo $formText_Showing_Output.' '.$l_page_count.' '.$formText_of_Output.' '.$l_total_count;?></div>
<div><a href="#" class="output-updated-selected-customers"><?php echo $formText_UpdateSelectedCustomers_Output;?></a></div>
<?php } ?>
</div>
<script type="text/javascript">
$(function() {
	/*$(document).off('click', '.output-row-selection').on('click', '.output-row-selection', function(e){
		if(e.target.nodeName == 'DIV')
		{
			$(this).closest('.gtable_row').find('.selection-switch-btn').trigger('click')
		}
	});
	$(document).off('click', '.selection-switch-btn').on('click', '.selection-switch-btn', function(e){
		e.preventDefault();
		if($(this).is('.main'))
		{
			if($(this).parent().find('input').is(':checked')) $('.selection-switch').removeProp('checked');
			else $('.selection-switch').prop('checked', true);
		} else {
			var $input = $(this).parent().find('input');
			if($input.is(':checked'))
			{
				$input.removeProp('checked');
				$('.selection-switch.main').removeProp('checked');
			} else {
				$input.prop('checked', true);
				if($('.selection-switch:not(.main):not(:checked)').length == 0) $('.selection-switch.main').prop('checked', true);
			}
		}
	});*/
	$(document).off('click', '.selection-switch-btn').on('click', '.selection-switch-btn', function(e){
		e.preventDefault();
		var $input = $(this).parent().find('input');
		var $row = $(this).closest('.gtable_row');
		if($input.is(':checked'))
		{
			$row.find('input:checked').removeProp('checked');
			$row.find('.selection-switch.skip').prop('checked', true);
		} else {
			$row.find('input:checked').removeProp('checked');
			$input.prop('checked', true);
		}
	});
	$(document).off('click', '.output-reload-results').on('click', '.output-reload-results', function(e){
		e.preventDefault();
		if(!fw_click_instance)
		{
			fw_click_instance = true;
			fw_loading_start();
			ajaxCall('list', {}, function(json) {
				$('.p_pageContent').html(json.html);
				fw_loading_end();
				fw_click_instance = false;
			});
		}
	});
	$(document).off('click', '.output-updated-selected-customers').on('click', '.output-updated-selected-customers', function(e){
		e.preventDefault();
		if(!fw_click_instance)
		{
			fw_click_instance = true;
			fw_loading_start();
			var skip = [];
			var sync = [];
			var nosync = [];
			$('.selection-switch.skip:not(.main):checked').each(function(index, obj){
				skip.push(obj.value);
			});
			$('.selection-switch.sync:not(.main):checked').each(function(index, obj){
				sync.push(obj.value);
			});
			$('.selection-switch.nosync:not(.main):checked').each(function(index, obj){
				sync.push(obj.value);
			});
			if(skip.length > 0 || sync.length > 0 || nosync.length > 0)
			{
				var data = {
					fwajax: 1,
					fw_nocss: 1,
					skip: skip,
					sync: sync,
					nosync: nosync
				};
				ajaxCall('update_selected', data, function(json) {
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
						ajaxCall('list', {}, function(json) {
							$('.p_pageContent').html(json.html);
							fw_loading_end();
							fw_click_instance = false;
						});
					}
				});
			} else {
				fw_info_message_add('error', "<?php echo $formText_NothingToUpdate_Output;?>", true, true);
				fw_loading_end();
				fw_click_instance = false;
			}
		}
	});
});
</script>