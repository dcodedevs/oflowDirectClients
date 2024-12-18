<?php
$b_activate_cdn = (isset($variables->fw_session['content_server_api_url']) && trim($variables->fw_session['content_server_api_url']) != '');
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
<div id="<?php echo $field_ui_id;?>_all">
	<span class="select_all unselected"><span class="glyphicon glyphicon-unchecked"></span><span class="glyphicon glyphicon-check"></span> <?php echo $formText_SelectAll_Fieldtype;?></span>
	<span class="delete_selected unselected"><span class="glyphicon glyphicon-trash"></span> <?php echo $formText_DeleteSelected_Fieldtype;?></span>
</div>
<div id="<?php echo $field_ui_id;?>_files" class="files"><div class="items"><?php
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
			<div class="col-md-1 col-xs-2">
				<div class="thumbnail"><a class="<?php echo $field_ui_id;?>_fancy script" href="<?php echo ($b_activate_cdn?"":$extradomaindirroot).$file.(strpos($file,'uploads/protected/')!==false?'?username='.$_COOKIE['username'].'&sessionID='.$_COOKIE['sessionID'].'&companyID='.$_GET['companyID'].'&caID='.$_GET['caID'].'&table='.$fields[0][3].'&field='.$field[0].'&ID='.$ID.'&languageID='.$variables->languageID.'&server_id='.array_shift(explode('.', $_SERVER['HTTP_HOST'])):'');?>" download=""><img class="ptr" src="<?php echo $extradir."/input/fieldtypes/".$field[4];?>/images/download.png"></a></div>
			</div>
			<div class="col-md-9 col-xs-8">
				<span class="filename"><input class="delete_select" type="checkbox"> <?php echo $name;?></span>
				<input class="name" type="hidden" value="<?php echo "|".$upload_id."|".$i."|".$name;?>" name="<?php echo $field[1].$ending."_name";?>[]">
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
			<div class="col-md-2 col-xs-2 text-right">
				<?php if($field[10]!=1 && $access >= 10) {?>
				<button class="btn btn-sm btn-delete delete_stored" data-type="POST" data-url="" data-name="<?php echo $name;?>"><i class="glyphicon glyphicon-trash"></i><div><?php echo $formText_delete_fieldtype;?></div></button>
				<?php } ?>
			</div>
		</div>
		<?php
		$i++;
	}
}
?></div><?php
if($field[9]!=1 && $field[10]!=1 && $access>=10)
{
	?>
	<div class="item row no-draggable dropzone add-item">
		<div class="col-md-2">
			<span class="glyphicon glyphicon-file"></span>
			<span><?php echo $formText_File_Fieldtype;?></span>
		</div>
		<div class="col-md-8">
			<div class="action">
				<i class="glyphicon glyphicon-plus"></i>
				<?php echo $formText_DragAndDropOr_fieldtype;?>
				<span class="fileinput-button">
					<span><?php echo $formText_BrowseFile_fieldtype;?></span>
					<input id="<?php echo $field_ui_id;?>_upload" type="file" name="<?php echo $field_ui_id;?>_files[]" multiple>
				</span>
			</div>
		</div>
		<div class="col-md-2"></div>
	</div>
	<?php
}
?></div>
<?php if($field[9]!=1 && $field[10]!=1 && $access>=10) { ?>
<input id="<?php echo $field_ui_id;?>counter" type="hidden" value="<?php echo $i;?>">
<style>
#<?php echo $field_ui_id;?>_all {
	margin:-20px 0 10px 100px;
	position:relative;
	display:block;
	text-align:right;
}
#<?php echo $field_ui_id;?>_all > span {
	margin-left:20px;
	color:#333333;
	cursor:pointer;
}
#<?php echo $field_ui_id;?>_all .select_all > span, #<?php echo $field_ui_id;?>_all > span:not(.unselected) > span {
	color:#0095e4;
}
#<?php echo $field_ui_id;?>_all .delete_selected.unselected {
	color:#999999;
	cursor:auto;
}
#<?php echo $field_ui_id;?>_all .select_all .glyphicon-check, #<?php echo $field_ui_id;?>_all .select_all.unselected .glyphicon-unchecked {
	display:inherit;
}
#<?php echo $field_ui_id;?>_all .select_all.unselected .glyphicon-check, #<?php echo $field_ui_id;?>_all .select_all .glyphicon-unchecked {
	display:none;
}
#<?php echo $field_ui_id;?>_files .item {
	margin:0 0 10px;
	background-color:#ffffff;
	border:1px solid #999999;
	border-radius:3px;
	padding:10px 0;
}
#<?php echo $field_ui_id;?>_files .item:not(.no-draggable):not(.upload-abort):hover {
	background-color:#F5F5F5;
	cursor:move;
}
#<?php echo $field_ui_id;?>_files .item .filename input {
	width:auto !important;
	margin:0 5px 0 0;
}
#<?php echo $field_ui_id;?>_files .thumbnail {
    width:50px;
    height:50px;
    margin-bottom:0;
    padding:0;
    border-radius:0;
}
#<?php echo $field_ui_id;?>_files .item.no-draggable {
	border:1px dashed #999999;
}
#<?php echo $field_ui_id;?>_files .item.upload-abort {
	background-color:#d11414;
	color:#ffffff;
	text-align:center;
	font-weight:bold;
	border:none;
	cursor:pointer;
}
#<?php echo $field_ui_id;?>_files .item.upload-abort:hover {
	color:#e9e9e9;
}
#<?php echo $field_ui_id;?>_files .action {
	color:#0095e4;
	text-align:center;
}
#<?php echo $field_ui_id;?>_files .fileinput-button {
	text-decoration:underline;
}
#<?php echo $field_ui_id;?>_files .btn-focus {
	background-color:#00a08e;
	font-weight:bold;
	color:#ffffff;
	margin-right:8px;
}
#<?php echo $field_ui_id;?>_files .btn-delete {
	background-color:#d11414;
	font-weight:bold;
	color:#ffffff;
}
#<?php echo $field_ui_id;?>_files .dropzone {
}
#<?php echo $field_ui_id;?>_files .dropzone.in {
    border-width:medium;
}
#<?php echo $field_ui_id;?>_files .dropzone.hover {
    background-color:#F5F5F5;
}
#<?php echo $field_ui_id;?>_files .dropzone.fade {
    -webkit-transition: all 0.3s ease-out;
    -moz-transition: all 0.3s ease-out;
    -ms-transition: all 0.3s ease-out;
    -o-transition: all 0.3s ease-out;
    transition: all 0.3s ease-out;
    opacity: 1;
}	
</style>
<script type="text/javascript">
<?php if(isset($ob_javascript)) { ob_start(); } ?>
$(function () {
	'use strict';
	var <?php echo $field_ui_id;?>counter,
	<?php echo $field_ui_id;?>alert_shown = false,
	<?php echo $field_ui_id;?>limit = parseInt('<?php echo $file_count_limit;?>');
	
	$("#<?php echo $field_ui_id;?>_files .items").sortable(/*{containment: "parent"}*/);
	
	<?php echo $field_ui_id;?>update_btns();
	function <?php echo $field_ui_id;?>update_btns()
	{
		var count = $('#<?php echo $field_ui_id;?>_files .items .item:not(.deleted):not(.abortable)').length
		if(count >= <?php echo $field_ui_id;?>limit)
		{
			$('#<?php echo $field_ui_id;?>_files .item.add-item').hide();
		} else {
			$('#<?php echo $field_ui_id;?>_files .item.add-item').show();
		}
		
		if($('#<?php echo $field_ui_id;?>_files .items .item:not(.deleted):not(.abortable)').length > 1)
		{
			$('#<?php echo $field_ui_id;?>_all, #<?php echo $field_ui_id;?>_files .item .filename input').show();
		} else {
			$('#<?php echo $field_ui_id;?>_all, #<?php echo $field_ui_id;?>_files .item .filename input').hide();
		}
		
		$('#<?php echo $field_ui_id;?>_all .select_all').off('click').on('click', function(e){
			e.preventDefault();
			if($('#<?php echo $field_ui_id;?>_files .item .filename input:checked').length > 0)
			{
				$('#<?php echo $field_ui_id;?>_files .item .filename input:checked').removeProp('checked');
				$('#<?php echo $field_ui_id;?>_all .delete_selected, #<?php echo $field_ui_id;?>_all .select_all').addClass('unselected');
			} else {
				$('#<?php echo $field_ui_id;?>_files .item .filename input[type=checkbox]').prop('checked', true);
				$('#<?php echo $field_ui_id;?>_all .delete_selected, #<?php echo $field_ui_id;?>_all .select_all').removeClass('unselected');
			}
		});
		$('#<?php echo $field_ui_id;?>_files .item .filename input').off('change').on('change', function(){
			if($('#<?php echo $field_ui_id;?>_files .item .filename input:checked').length > 0)
			{
				$('#<?php echo $field_ui_id;?>_all .delete_selected, #<?php echo $field_ui_id;?>_all .select_all').removeClass('unselected');
			} else {
				$('#<?php echo $field_ui_id;?>_all .delete_selected, #<?php echo $field_ui_id;?>_all .select_all').addClass('unselected');
			}
		});
	}
	$('#<?php echo $field_ui_id;?>_all .delete_selected').on('click', function(e){
		e.preventDefault();
		var $items = $('#<?php echo $field_ui_id;?>_files .item .filename input:checked');
		if($items.length > 0 && !fw_click_instance)
		{
			fw_click_instance = true;
			bootbox.confirm({
				message:"<?php echo $formText_DeleteSelectedItems_input;?>: " + $items.length + "?",
				buttons:{confirm:{label:"<?php echo $formText_Yes_input;?>"},cancel:{label:"<?php echo $formText_No_input;?>"}},
				callback: function(result){
					if(result)
					{
						$items.each(function(){
							var $item = $(this).closest('.item');
							if($item.find('.delete_stored').length)
							{
								var name = $item.find('input.name');
								if(!$(name).is('.deleted'))
								{
									$(name).addClass('deleted').val('process' + $(name).val());
									$item.addClass('deleted').find('input.file').each(function(){
										$(this).val('delete|' + $(this).val());
									});
									$item.hide();
									<?php echo $field_ui_id;?>update_btns();
								}
							} else {
								$item.find('.btn-delete').trigger('click');
							}
						});
					}
					fw_click_instance = false;
				}
			});
		}
	});
	
	$(document).on('drop dragover', function(e){
		e.preventDefault();
	});
	$(document).on('dragover', function(e){
		var dropZones = $('.dropzone'),
			timeout = window.dropZoneTimeout;
		if(timeout){
			clearTimeout(timeout);
		} else {
			dropZones.addClass('in');
		}
		var hoveredDropZone = $(e.target).closest(dropZones);
		dropZones.not(hoveredDropZone).removeClass('hover');
		hoveredDropZone.addClass('hover');
		window.dropZoneTimeout = setTimeout(function(){
			window.dropZoneTimeout = null;
			dropZones.removeClass('in hover');
		}, 100);
	});
	
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
								$(this).val('delete|' + $(this).val());
							});
							$_this.closest('.item').hide();
							<?php echo $field_ui_id;?>update_btns();
						}
						fw_click_instance = false;
					}
				});
			}
		}
	});
	
	$('#<?php echo $field_ui_id;?>_upload').fileupload({
        dropZone: $('#<?php echo $field_ui_id;?>_files .dropzone'),
		<?php if($b_activate_cdn) { ?>
		url: "<?php echo $variables->fw_session['content_server_api_url'];?>?param_name=<?php echo $field_ui_id;?>_files",
		<?php } else { ?>
		url: "<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_upload.php?param_name=<?php echo $field_ui_id;?>_files",
		<?php } ?>
		dataType: 'json',
		//limitMultiFileUploads: 3,
		//limitMultiFileUploadSize: 1000000,
		sequentialUploads: true,
		start: function (e, data) {
			fw_info_message_empty();
		},
		add: function (e, data) {
			fw_editing_instance = true;
			<?php if($b_activate_cdn) { ?>
			$.getJSON("<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/get_next_id.php", function (result) {
			<?php } ?>
				if($('#<?php echo $field_ui_id;?>_files .item.upload-abort').length == 0)
				{
					var abortContainer = $('<div/>')
						.addClass('item upload-abort')
						.append( '<?php echo $formText_AbortUploading_Fieldtype;?>' )
						.click( function() {
							data.abort();
							$('#<?php echo $field_ui_id;?>_files .items .item.abortable').remove();
						} );
					$('#<?php echo $field_ui_id;?>_files .items').after(abortContainer);
				}
				
				$.each(data.files, function (index, file){
					var tmp_upload_id = btoa(encodeURIComponent(file.name.trim()));
					var html = '<div class="item row abortable" data-tmp-upload-id="' + tmp_upload_id + '">' +
						'<div class="col-md-1 col-xs-2">' +
							'<span class="glyphicon glyphicon-file"></span>' +
						'</div>' +
						'<div class="col-md-9 col-xs-8">' +
							'<div class="name">' + file.name.trim() + '</div>' +
							'<input class="name" type="hidden" name="<?php echo $field[1].$ending."_name";?>[]" value="|' + tmp_upload_id + '|' + '|' + file.name.trim() + '">' +
							'<div class="progress">' +
								'<div class="progress-bar"></div>' +
							'</div>' +
						'</div>' +
						'<div class="col-md-2 col-xs-2 text-right">' +
						'</div>' +
					'</div>';
					$('#<?php echo $field_ui_id;?>_files .items').append(html);
				});
				<?php if($b_activate_cdn) { ?>
				data.formData = result;
				<?php } ?>
				data.submit();
			<?php if($b_activate_cdn) { ?>
			});
			<?php } ?>
		},
		done: function (e, data) {
			$.each(data.result.<?php echo $field_ui_id;?>_files, function (index, file) {
				if(file.error) {
					fw_info_message_add('error', file.name + ': ' + file.error);
					$('#<?php echo $field_ui_id;?>_files .items [data-tmp-upload-id="' + btoa(encodeURIComponent(file.name)) + '"]').remove();
				} else {
					if($('#<?php echo $field_ui_id;?>_files .items .item:not(.deleted):not(.abortable)').length >= <?php echo $field_ui_id;?>limit)
					{
						if(!<?php echo $field_ui_id;?>alert_shown)
						{
							fw_info_message_add('error', '<?php echo $formText_MaximumNumberOfAllowableFileUploadsHasBeenExceeded_Fieldtype."! ".$formText_FieldOnlyAllowsToUpload_fieldtype." ".$image_count_limit." ".$formText_filesInTotal_Fieldtype;?>.');
						}
						<?php echo $field_ui_id;?>alert_shown = true;
						$.post(file.deleteUrl + "&param_name=<?php echo $field_ui_id;?>_files", function(data){
							if(data[file.name] == true) $('#<?php echo $field_ui_id;?>_files .items [data-tmp-upload-id="' + btoa(encodeURIComponent(file.name)) + '"]').remove();
						},"json");
					} else {
						var <?php echo $field_ui_id;?>counter = parseInt($('#<?php echo $field_ui_id;?>counter').val())+1;
						var oDiv = $('<div/>').attr('class', 'item row');
						var oThumbCol = $('<div/>').attr('class', 'col-md-1 col-xs-2');
						var oThumbDiv = $('<div/>').attr('class', 'thumbnail');
						var oThumbImg = $('<img/>').attr('src', '<?php echo $extradir."/input/fieldtypes/".$field[4];?>/images/download.png');
						var oTextCol = $('<div/>').attr('class', 'col-md-9 col-xs-8').append('<span class="filename"><input class="delete_select" type="checkbox"> ' + file.name + '</span>');
						var oDeleteCol = $('<div/>').attr('class', 'col-md-2 col-xs-2 text-right');
						var oDeleteBtn = $('<button/>').attr('class', 'btn btn-sm btn-delete').attr('data-type', file.deleteType).attr('data-url', file.deleteUrl + "&param_name=<?php echo $field_ui_id;?>_files").append('<i class="glyphicon glyphicon-trash"></i><div><?php echo $formText_delete_fieldtype;?></div>');
						
						$('#<?php echo $field_ui_id;?>_files .items [data-tmp-upload-id="' + btoa(encodeURIComponent(file.name)) + '"]').replaceWith(oDiv);
						oTextCol.append('<input type="hidden" name="<?php echo $field[1].$ending;?>_name[]" value="process|' + file.upload_id + '|' + <?php echo $field_ui_id;?>counter + '|' + file.name + '"/>');
						oTextCol.append('<input type="hidden" name="<?php echo $field[1].$ending;?>_file' + <?php echo $field_ui_id;?>counter+ '" value="process|' + file.upload_id + '|' + file.url + '"/>');
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
								<?php echo $field_ui_id;?>update_btns();
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
			<?php echo $field_ui_id;?>update_btns();
			$(window).trigger('resize');
        },
		progress: function (e, data) {
			var progress = parseInt(data._progress.loaded / data._progress.total * 100, 10);
			$('#<?php echo $field_ui_id;?>_files .items [data-tmp-upload-id="' + btoa(encodeURIComponent(data.files[0].name)) + '"] .progress-bar').css('width', progress + '%');
		},
		stop: function(e) {
			$('#<?php echo $field_ui_id;?>_files .item.upload-abort').off('click').remove();
			<?php echo $field_ui_id;?>alert_shown = false;
			fw_editing_instance = false;
			fw_info_message_show();
		}
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');
});
<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
</script>
<?php } ?>