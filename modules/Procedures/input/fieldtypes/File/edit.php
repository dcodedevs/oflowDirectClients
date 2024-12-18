<?php
$type = ($field[11]=="" ? 't1' : strtolower($field[11]));
list($fieldtype, $limit) = explode(',',$type);
$file_count_limit = ($limit>0?$limit:1);

if(strpos($fieldtype,"s") !== false)
{
	$output_languages = array("all"=>"");
}  else {
	$output_languages = array();
	$langName = array();
	$o_query = $o_main->db->query('SELECT languageID, name FROM language WHERE outputlanguage = 1 ORDER BY defaultOutputlanguage DESC, sortnr ASC');
	if($o_query && $o_query->num_rows()>0)
	{
		foreach($o_query->result() as $o_row)
		{
			$output_languages[$o_row->languageID] = $o_row->name;
		}
	}
}

if(strpos($fieldtype,"t2") !== false)
{
	$show_text = true;
} else {
	$show_text = false;
}
if(strpos($fieldtype,"link") !== false)
{
	$show_link = true;
} else {
	$show_link = false;
}
?>
<div id="<?php echo $field_ui_id;?>_files" class="files"><?php
$i=0;
if($field[6][$langID] != "")
{
	$data = json_decode($field[6][$langID], true);
	//print_r($data);
	foreach($data as $obj)
	{
		$name = $obj[0];
		$file = $obj[1][0];
		$labels = $obj[2];
		$link = $obj[3];
		$upload_id = $obj[4];
		?>
		<div class="item row">
			<div class="col-md-2">
				<div class="thumbnail"><a class="<?php echo $field_ui_id;?>_fancy script" href="<?php echo $extradomaindirroot."/".$file.(strpos($file,'uploads/protected/')!==false?'?caID='.$_GET['caID'].'&table='.$fields[0][3].'&field='.$field[0].'&ID='.$ID.'&languageID='.$variables->languageID:'');?>" download=""><img class="ptr" src="<?php echo $extradir."/input/fieldtypes/".$field[4];?>/images/download.png"></a></div>
			</div>
			<div class="col-md-8">
				<strong><?php echo $formText_Filename_fieldtype;?></strong>: <?php echo $name;?>
				<input class="name" type="hidden" value="<?php echo ":".$upload_id.":".$i.":".$name;?>" name="<?php echo $field[1].$ending."_name";?>[]">
				<input class="file" type="hidden" name="<?php echo $field[1].$ending."_file".$i;?>" value="<?php echo $file;?>"><?php
				if($show_link)
				{
					?><div class="row"><div class="col-md-4"><b><?php echo $formText_FileLink_fieldtype;?></b>:</div><div class="col-md-8"><input type="text" name="<?php echo $field[1].$ending."_link".$i;?>" value="<?php echo htmlspecialchars($link);?>"<?php echo ($field[10]==1||$access<10?" readonly":"");?>></div></div><?php
				}
				if($show_text)
				{
					foreach($output_languages as $lid => $value)
					{
						?><div class="row"><div class="col-md-4"><b><?php echo $formText_FileText_fieldtype;?></b> <i><?php echo $value;?></i>:</div><div class="col-md-8"><input type="text" name="<?php echo $field[1].$ending."_label".$lid.$i;?>" value="<?php echo htmlspecialchars($labels[$lid]);?>"<?php echo ($field[10]==1||$access<10?" readonly":"");?>></div></div><?php
					}
				}
				?>
			</div>
			<div class="col-md-2">
				<?php if($field[10]!=1 && $access >= 10) {?>
				<button class="btn  btn-xs btn-danger delete_stored" data-type="POST" data-url="" data-name="<?php echo $name;?>"><i class="glyphicon glyphicon-trash"></i><span><?php echo $formText_delete_fieldtype;?></span></button>
				<?php } ?>
			</div>
		</div>
		<?php
		$i++;
	}
}
?></div>
<?php if($field[9]!=1 && $field[10]!=1 && $access>=10) { ?>
<div class="row">
	<div class="col-md-2">
		<span class="btn  btn-xs btn-success fileinput-button">
			<i class="glyphicon glyphicon-plus"></i>
			<span><?php echo $formText_SelectFiles_fieldtype;?></span>
			<input id="<?php echo $field_ui_id;?>_upload" type="file" name="<?php echo $field_ui_id;?>_files[]" multiple>
		</span>
	</div>
	<div class="col-md-10">
		<div id="<?php echo $field_ui_id;?>_progress" class="progress" style="display:none; margin:7px 0;">
			<div class="progress-bar progress-bar-success"></div>
		</div>
	</div>
</div>
<input id="<?php echo $field_ui_id;?>counter" type="hidden" value="<?php echo $i;?>">
<style>
#<?php echo $field_ui_id;?>_files .item {
	margin:1px 1px 4px 1px;
}
#<?php echo $field_ui_id;?>_files .item:hover {
	background:rgba(255,255,255,0.5);
	margin:0px 0px 3px 0px;
	border:1px dashed #999999;
	border-radius:3px;
	cursor:move;
}	
</style>
<script type="text/javascript">
<?php if(isset($ob_javascript)) { ob_start(); } ?>
$(function () {
	'use strict';
	var <?php echo $field_ui_id;?>counter,
	<?php echo $field_ui_id;?>limit = parseInt('<?php echo $file_count_limit;?>');
	
	$("#<?php echo $field_ui_id;?>_files").sortable(/*{containment: "parent"}*/);
	
	$(document).on('click', '#<?php echo $field_ui_id;?>_files .item .delete_stored', function(e){
		e.preventDefault();
		if(!fw_changes_made && !fw_click_instance)
		{
			fw_click_instance = true;
			var $_this = $(this);
			var name = $_this.closest('.item').find('input.name');
			if(!$(name).is('.deleted'))
			{
				bootbox.confirm({
					message:"<?php echo $formText_DeleteItem_input;?>: " + $_this.attr("data-name") + "?",
					buttons:{confirm:{label:"<?php echo $formText_Yes_input;?>"},cancel:{label:"<?php echo $formText_No_input;?>"}},
					callback: function(result){
						if(result)
						{
							$(name).addClass('deleted').val('process' + $(name).val());
							$_this.closest('.item').addClass('deleted').find('input.file').each(function(){
								$(this).val('delete:' + $(this).val());
							});
							$_this.closest('.item').hide();
						}
						fw_click_instance = false;
					}
				});
			}
		}
	});
	
	$('#<?php echo $field_ui_id;?>_upload').fileupload({
        url: "<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_upload.php?param_name=<?php echo $field_ui_id;?>_files",
		dataType: 'json',
		start: function (e, data) {
			fw_info_message_empty();
			$('#<?php echo $field_ui_id;?>_progress .progress-bar').css('width', '0%');
			$('#<?php echo $field_ui_id;?>_progress').show();
		},
		done: function (e, data) {
			$.each(data.result.<?php echo $field_ui_id;?>_files, function (index, file) {
				if(file.error) {
					fw_info_message_add('error', file.name + ': ' + file.error);
				} else {
					if($('#<?php echo $field_ui_id;?>_files .item:not(.deleted)').length >= <?php echo $field_ui_id;?>limit)
					{
						fw_info_message_add('error', '<?php echo $formText_FieldOnlyAllowsToUpload_fieldtype." (".$file_count_limit.") ".$formText_files_fieldtype. ". ".$formText_FollowingFileWasUploadedButNotAddedToContent_fieldtype;?>: ' + file.name);
					} else {
						var <?php echo $field_ui_id;?>counter = parseInt($('#<?php echo $field_ui_id;?>counter').val())+1;
						var oDiv = $('<div/>').attr('class', 'item row').appendTo('#<?php echo $field_ui_id;?>_files');
						var oThumbCol = $('<div/>').attr('class', 'col-md-2');
						var oThumbDiv = $('<div/>').attr('class', 'thumbnail');
						var oThumbImg = $('<img/>').attr('src', '<?php echo $extradir."/input/fieldtypes/".$field[4];?>/images/download.png');
						var oTextCol = $('<div/>').attr('class', 'col-md-8').append('<strong><?php echo $formText_Filename_fieldtype;?></strong>: ' + file.name);
						var oDeleteCol = $('<div/>').attr('class', 'col-md-2');
						var oDeleteBtn = $('<button/>').attr('class', 'btn btn-danger').attr('data-type', file.deleteType).attr('data-url', file.deleteUrl + "&param_name=<?php echo $field_ui_id;?>_files").append('<i class="glyphicon glyphicon-trash"></i><span><?php echo $formText_delete_fieldtype;?></span>');
						
						oTextCol.append('<input type="hidden" name="<?php echo $field[1].$ending;?>_name[]" value="process:' + file.upload_id + ':' + <?php echo $field_ui_id;?>counter + ':' + file.name + '"/>');
						oTextCol.append('<input type="hidden" name="<?php echo $field[1].$ending;?>_file' + <?php echo $field_ui_id;?>counter+ '" value="process:' + file.upload_id + ':' + file.url + '"/>');
						<?php
						if($show_link)
						{
							?>oTextCol.append('<div class="row"><div class="col-md-4"><b><?php echo $formText_FileLink_fieldtype;?></b>:</div><div class="col-md-8"><input type="text" name="<?php echo $field[1].$ending;?>_link' + <?php echo $field_ui_id;?>counter + '" value=""/></div></div>');<?php
						}
						if($show_text)
						{
							foreach($output_languages as $lid => $value)
							{
								?>oTextCol.append('<div class="row"><div class="col-md-4"><?php echo "<b>".$formText_FileText_fieldtype."</b>"." <i>".$value."</i>";?>:</div><div class="col-md-8"><input type="text" name="<?php echo $field[1].$ending;?>_label<?php echo $lid;?>' + <?php echo $field_ui_id;?>counter + '" value=""/></div></div>');<?php
								if($singleLanguage) break; // stop for single language
							}
						}
						?>
	
						oDeleteBtn.on("click", function() {
							$.post($(this).data('url'), function(data){
								if(data[file.name] == true) oDiv.remove();
							},"json");
						});
						oThumbCol.appendTo(oDiv);
						oThumbDiv.appendTo(oThumbCol);
						oThumbImg.appendTo(oThumbDiv);
						oTextCol.appendTo(oDiv);
						oDeleteBtn.appendTo(oDeleteCol);
						oDeleteCol.appendTo(oDiv);
						
						$('#<?php echo $field_ui_id;?>counter').val(<?php echo $field_ui_id;?>counter);
					}
				}
            });
			$(window).resize();
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#<?php echo $field_ui_id;?>_progress .progress-bar').css(
                'width',
                progress + '%'
            );
        },
		stop: function(e) {
			setTimeout(function() { $('#<?php echo $field_ui_id;?>_progress').hide(); }, 500);
			fw_info_message_show();
		}
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');
});
<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
</script>
<?php } ?>