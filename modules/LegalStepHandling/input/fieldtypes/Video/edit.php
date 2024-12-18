<?php
$dataInit = json_decode($field[6][$langID], true);
$video_settings = $dataInit[1];
if($access>=10)
{
	$s_tab_type = 'youtube';
	$youtubeActive = " active";
	$vimeoActive = "";
	$fileActive = "";
	if(isset($dataInit[3]) && 'vimeo' == $dataInit[3])
	{
		$s_tab_type = 'vimeo';
		$youtubeActive = "";
		$vimeoActive = " active";
		$fileActive = "";
	}
	if(is_array($dataInit[0])){
		$s_tab_type = 'file';
		$youtubeActive = "";
		$vimeoActive = "";
		$fileActive = " active";
	}
	?>
	<div id="<?php echo $field_ui_id;?>_tab_changer">
		<div class="youtube_tab tab_switcher_btn<?php echo $youtubeActive?>"><?php echo $formText_Youtube_output;?></div>
		<div class="vimeo_tab tab_switcher_btn<?php echo $vimeoActive?>"><?php echo $formText_Vimeo_output;?></div>
		<div class="file_tab tab_switcher_btn<?php echo $fileActive?>"><?php echo $formText_File_output;?></div>
		<div class="clear"></div>
	</div>
	<input type="hidden" id="<?php echo $field_ui_id;?>_type" name="<?php echo $field[1].$ending;?>_type" value="<?php echo $s_tab_type;?>">
	<div id="<?php echo $field_ui_id;?>_tab_youtube" class="<?php echo $youtubeActive?>">
		<input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="text" name="<?php echo $field[1].$ending;?>" value="<?php if(!is_array($dataInit[0]) && $youtubeActive) echo $dataInit[0];?>" />
	</div>
	<div id="<?php echo $field_ui_id;?>_tab_vimeo" class="<?php echo $vimeoActive?>">
		<input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>_vimeo" type="text" name="<?php echo $field[1].$ending;?>_vimeo" value="<?php if(!is_array($dataInit[0]) && $vimeoActive) echo $dataInit[0];?>" />
	</div>
	<div id="<?php echo $field_ui_id;?>_tab_file" class="<?php echo $field_ui_id;?>_tab_file <?php echo $fileActive?>">
		<div class="fileWrapper">
			<label><?php echo $formText_VideoFile_fieldtype;?></label>
			<?php if($field[9]!=1 && $field[10]!=1 && $access>=10) { ?>
			<div class="row" style="display: inline-block;vertical-align: middle; padding-left: 15px;">
				<div class="col-md-2 nopadding">
					<span class="btn  btn-xs btn-success fileinput-button">
						<i class="glyphicon glyphicon-plus"></i>
						<span><?php echo $formText_SelectFile_fieldtype;?></span>
						<input id="<?php echo $field_ui_id;?>_upload" type="file" name="<?php echo $field_ui_id;?>_files[]" multiple>
					</span>
				</div>
			</div>
			<div>
				<div id="<?php echo $field_ui_id;?>_progress" class="progress" style="display:none; margin:7px 0;">
					<div class="progress-bar progress-bar-success"></div>
				</div>
			</div>
			<?php } ?>
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
		if(is_array($dataInit[0]))
		{
			$data = $dataInit[0];
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
					<div class="col-md-2 nopadding">
						<strong><?php echo $formText_Filename_fieldtype;?></strong>: <?php echo $name;?>
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
						<?php if($field[10]!=1 && $access >= 10) {?>
						<button class="customBtn  delete_stored" data-type="POST" data-url="" data-name="<?php echo $name;?>"><i class="glyphicon glyphicon-trash"></i></button>
						<?php } ?>
					</div>
				</div>
				<?php
				$i++;
			}
		}
		?></div>
		<style>
		#<?php echo $field_ui_id;?>_files {
			margin-top: 10px;
		}
		#<?php echo $field_ui_id;?>_files .item {
			margin:1px 1px 4px 1px;
			background: #fff;
			padding: 8px 12px;
			border: 1px solid #9fa2a4;
		}
		#<?php echo $field_ui_id;?>_files .customBtn {
			border: 0;
			color: #4aa5f9;
			background: #fff;
			margin-left: 15px;
		}
		#<?php echo $field_ui_id;?>_tab_file  .row {
			margin: 0!important;
		}
		#<?php echo $field_ui_id;?>_files .item:hover {
			/* background:rgba(255,255,255,0.5);
			margin:0px 0px 3px 0px;
			border:1px dashed #999999;
			border-radius:3px;
			cursor:move; */
		}
		</style>
		<?php if($field[9]!=1 && $field[10]!=1 && $access>=10) { ?>
		<input id="<?php echo $field_ui_id;?>counter" type="hidden" value="<?php echo $i;?>">

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
										$(this).val('delete|' + $(this).val());
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
		        <?php if($o_main->activate_cdn) { ?>
				url: "<?php echo $o_main->cdn_api_url;?>?param_name=<?php echo $field_ui_id;?>_files",
				<?php } else { ?>
				url: "<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_upload.php?param_name=<?php echo $field_ui_id;?>_files",
				<?php } ?>
				dataType: 'json',
				//limitMultiFileUploads: 3,
				//limitMultiFileUploadSize: 1000000,
				sequentialUploads: true,
				start: function (e, data) {
					fw_info_message_empty();
					$('#<?php echo $field_ui_id;?>_progress .progress-bar').css('width', '0%');
					$('#<?php echo $field_ui_id;?>_progress').show();
				},
				<?php if($o_main->activate_cdn) { ?>
				add: function (e, data) {
					fw_editing_instance = true;
					$.getJSON("<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/get_next_id_file.php?accountname=<?php echo $_GET['accountname'];?>&companyID=<?php echo $_GET['companyID'];?>&caID=<?php echo $_GET['caID'];?>", function (result) {
						data.formData = result;
						data.submit();
					});
				},
				<?php } ?>
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
								var oTextCol = $('<div/>').attr('class', 'col-md-2 nopadding').append('<strong><?php echo $formText_Filename_fieldtype;?></strong>: ' + file.name);
								var oDeleteBtn = $('<button/>').attr('class', 'customBtn').attr('data-type', file.deleteType).attr('data-url', file.deleteUrl + "&param_name=<?php echo $field_ui_id;?>_files").append('<i class="glyphicon glyphicon-trash"></i><span></span>');

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
									},"json");
								});
								oTextCol.appendTo(oDiv);
								oDeleteBtn.appendTo(oTextCol);

								$('#<?php echo $field_ui_id;?>counter').val(<?php echo $field_ui_id;?>counter);
							}
						}
		            });
					$(window).trigger('resize');
		        },
		        progressall: function (e, data) {
		            var progress = parseInt(data.loaded / data.total * 100, 10);
		            $('#<?php echo $field_ui_id;?>_progress .progress-bar').css(
		                'width',
		                progress + '%'
		            );
		        },
				stop: function(e) {
					setTimeout(function() { $('#<?php echo $field_ui_id;?>_progress').hide(); fw_editing_instance = false; }, 500);
					fw_info_message_show();
				}
		    }).prop('disabled', !$.support.fileInput)
		        .parent().addClass($.support.fileInput ? undefined : 'disabled');
		});
		<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
		</script>
		<?php } ?>
		</div>
	</div>

		<div id="<?php echo $field_ui_id;?>_settings_edit"  class="video_settings_edit" data-target="#<?php echo $field_ui_id;?>_settings"><?php echo $formText_EditSettings_fieldtype;?> <div class="arrowdown"></div></div>
		<div id="<?php echo $field_ui_id;?>_settings" class="video_settings">
			<div class="video_settings_item">
				<input id="<?php echo $field[1].$ending;?>_settings_autoplay" type="checkbox" name="<?php echo $field[1].$ending;?>_settings_autoplay" <?php if($video_settings[0]) echo ' checked';?> value="1"/><label for="<?php echo $field[1].$ending;?>_settings_autoplay"><?php echo $formText_Autoplay_fieldtype;?></label>
			</div><div class="video_settings_item">
			<input id="<?php echo $field[1].$ending;?>_settings_related"  type="checkbox" name="<?php echo $field[1].$ending;?>_settings_related" <?php if($video_settings[1]) echo ' checked';?> value="1"/><label for="<?php echo $field[1].$ending;?>_settings_related"><?php echo $formText_RelatedVideos_fieldtype;?></label>
			</div><div class="video_settings_item">
			<input id="<?php echo $field[1].$ending;?>_settings_controls"  type="checkbox" name="<?php echo $field[1].$ending;?>_settings_controls" <?php if($video_settings[2]) echo ' checked';?> value="1"/><label for="<?php echo $field[1].$ending;?>_settings_controls"><?php echo $formText_Controls_fieldtype;?></label>
			</div><div class="video_settings_item">
			<input id="<?php echo $field[1].$ending;?>_settings_loop"  type="checkbox" name="<?php echo $field[1].$ending;?>_settings_loop" <?php if($video_settings[3]) echo ' checked';?> value="1"/><label for="<?php echo $field[1].$ending;?>_settings_loop"><?php echo $formText_Loop_fieldtype;?></label>
			</div><div class="video_settings_item">
			<input id="<?php echo $field[1].$ending;?>_settings_muted"  type="checkbox" name="<?php echo $field[1].$ending;?>_settings_muted" <?php if($video_settings[4]) echo ' checked';?> value="1"/><label for="<?php echo $field[1].$ending;?>_settings_muted"><?php echo $formText_Muted_fieldtype;?></label>
			</div><div class="video_settings_item">
			<input id="<?php echo $field[1].$ending;?>_settings_showinfo"  type="checkbox" name="<?php echo $field[1].$ending;?>_settings_showinfo" <?php if($video_settings[5]) echo ' checked';?> value="1"/><label for="<?php echo $field[1].$ending;?>_settings_showinfo"><?php echo $formText_ShowInfo_fieldtype;?></label>
			</div>
		</div>

	<div id="<?php echo $field_ui_id;?>_tab_file" class="<?php echo $field_ui_id;?>_tab_file <?php echo $fileActive?>">
		<div class="imageWrapper">
			<label><?php echo $formText_PreviewImageFallbackOnMobileDevices_fieldtype;?></label>
			<?php if($field[9]!=1 && $field[10]!=1 && $access>=10) { ?>
				<div class="row" style=" display:inline-block; vertical-align: middle; padding-left: 10px;">
					<div class="col-md-2 nopadding">
						<span class="btn btn-success btn-xs fileinput-button">
							<i class="glyphicon glyphicon-plus"></i>
							<span><?php echo $formText_SelectImage_fieldtype;?></span>
							<input id="<?php echo $field_ui_id;?>_imageupload" type="file" name="<?php echo $field_ui_id;?>_images[]" multiple>
						</span>
					</div>
				</div>
				<div class="">
					<div id="<?php echo $field_ui_id;?>_imageprogress" class="progress" style="display:none; margin:7px 0;">
						<div class="progress-bar progress-bar-success"></div>
					</div>
				</div>
			<?php } ?>
			<?php
			$focus_w_limit = 560;
			$focus_h_limit = 460;
			if($field[11] == '') $field[11] = 'T1:0,0';
			list($type, $resize_codes) = explode(":",strtolower($field[11]),2);
			list($fieldtype, $limit) = explode(',',$type);
			$image_count_limit = ($limit>0?$limit:1);
			if(!isset($resize_codes) or $resize_codes == '') $resize_codes = '0,0';
			$show_focuspoint = (strpos($resize_codes,"f")!==false ? true : false);
			$resize_codes = explode(":",$resize_codes);

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
			<div id="<?php echo $field_ui_id;?>_images" class="files"><?php
			$i=0;
			if(is_array($dataInit[2]))
			{
				$data = $dataInit[2];
				//print_r($data);
				foreach($data as $obj)
				{
					$name = $obj[0];
					$image = $obj[1];
					$labels = $obj[2];
					$link = $obj[3];
					$upload_id = $obj[4];
					$focus = $obj[5];
					$max_w = 0;
					$min_w = 100000;
					foreach($image as $img)
					{
						if(!is_file(ACCOUNT_PATH."/".rawurldecode($img))) continue;
						list($w, $h, $t, $a) = getimagesize(ACCOUNT_PATH."/".rawurldecode($img));
						if($max_w < $w)
						{
							$max_w = $w;
							$popup_img = $img;
						}
						if($min_w > $w)
						{
							$min_w = $w;
							$thumb_img = $img;
						}
					}
					if($o_main->activate_cdn) $thumb_img = $popup_img = $image[0];
					$thumb_img_link = ($o_main->activate_cdn?'':$extradomaindirroot).$thumb_img;
					$v_token = array(
						'upload_id' => $upload_id,
						'accountname' => ($o_main->multi_acc?$o_main->accountname:$accountname),
						'folder' => 'protected',
						'created' => date('Y-m-d H:i'),
					);
					$cdn_token_protected = urlencode($o_main->get_cdn_token($v_token));
					$s_protected_local = '?username='.$_COOKIE['username'].'&sessionID='.$_COOKIE['sessionID'].'&companyID='.$_GET['companyID'].'&caID='.$_GET['caID'].'&table='.$fields[0][3].'&field='.$field[0].'&ID='.$ID.'&languageID='.$variables->languageID.'&server_id='.array_shift(explode('.', $_SERVER['HTTP_HOST']));
					$s_protected_cdn = '?caID='.$_GET['caID'].'&cdn_token='.$cdn_token_protected;
					?>
					<div class="item row">
						<div class="col-md-2">
							<div class="thumbnail"><a rel="gal_<?php echo $field_ui_id."_".$i;?>" class="<?php echo $field_ui_id;?>_fancy script" href="<?php echo ($o_main->activate_cdn?"":$extradomaindirroot).$popup_img.(strpos($popup_img,'uploads/protected/')!==false?($o_main->multi_acc?$s_protected_cdn:$s_protected_local):'');?>"><img class="ptr" src="<?php echo $thumb_img_link.(strpos($thumb_img_link,'uploads/protected/')!==false?($o_main->multi_acc?$s_protected_cdn:$s_protected_local):'');?>"></a></div>
						</div>
						<div class="col-md-8">
							<strong><?php echo $formText_Filename_fieldtype;?></strong>: <?php echo $name;?>
							<input class="name" type="hidden" name="<?php echo $field[1].$ending."_imagename";?>[]" value="<?php echo "|".$upload_id."|".$i."|".$name;?>"><?php
							$focus_counter = 0;
							foreach($image as $x=>$item)
							{
								$tmp = explode(",", $resize_codes[$x]);
								?><input class="image" type="hidden" name="<?php echo $field[1].$ending."_img".$i;?>[]" value="<?php echo $item;?>"><?php
								if(strpos($tmp[3],"f")!==false && ($o_main->activate_cdn || is_file(ACCOUNT_PATH."/".rawurldecode($item))))
								{
									list($w,$h,$t,$r) = getimagesize(($o_main->activate_cdn?'':ACCOUNT_PATH."/").rawurldecode($item));
									if($h>$w)
									{
										$ini_h = $h;
										if($h > $focus_h_limit) $h = $focus_h_limit;
										$ratio = $h/$ini_h;
										$w = round($w*$ratio);
									} else {
										$ini_w = $w;
										if($w > $focus_w_limit) $w = $focus_w_limit;
										$ratio = $w/$ini_w;
										$h = round($h*$ratio);
									}
									$ini_x = round(12/$ratio);
									if($focus[$focus_counter]=="") $focus[$focus_counter] = "$ini_x:$ini_x";
									list($x,$y) = explode(":",$focus[$focus_counter]);
									$x=round($x*$ratio);
									$y=round($y*$ratio);

									if($x>($w-12)) $x = $w - $ini_x;
									if($y>($h-12)) $y = $h - $ini_x;
									if($x<$ini_x) $x = $ini_x;
									if($y<$ini_x) $y = $ini_x;
									?><input class="focus" type="hidden" name="<?php echo $field[1].$ending."_focus".$i;?>[]" value="<?php echo $focus[$focus_counter];?>" data-src="<?php echo ($o_main->activate_cdn?"":$extradomaindirroot).$item.(strpos($item,'uploads/protected/')!==false?($o_main->multi_acc?$s_protected_cdn:$s_protected_local):'');?>" data-w="<?php echo $w;?>" data-h="<?php echo $h;?>" data-x="<?php echo $x-12;?>" data-y="<?php echo $y-12;?>" data-ratio="<?php echo $ratio;?>"><?php
									$focus_counter++;
								}
							}
							if($show_link)
							{
								?><div class="row"><div class="col-md-4"><b><?php echo $formText_PictureLink_fieldtype;?></b>:</div><div class="col-md-8"><input type="text" name="<?php echo $field[1].$ending."_link".$i;?>" value="<?php echo htmlspecialchars($link);?>"<?php echo ($field[10]==1||$access<10?" readonly":"");?>></div></div><?php
							}
							if($show_text)
							{
								foreach($output_languages as $lid => $value)
								{
									?><div class="row"><div class="col-md-4"><b><?php echo $formText_Picturetext_fieldtype;?></b> <i><?php echo $value;?></i>:</div><div class="col-md-8"><input type="text" name="<?php echo $field[1].$ending."_label".$lid.$i;?>" value="<?php echo htmlspecialchars($labels[$lid]);?>"<?php echo ($field[10]==1||$access<10?" readonly":"");?>></div></div><?php
								}
							}
							?>
						</div>
						<div class="col-md-2">
							<?php if($field[10]!=1 && $access >= 10) {?>
							<button class="btn btn-xs btn-danger delete_stored" data-type="POST" data-url="" data-name="<?php echo $name;?>"><i class="glyphicon glyphicon-trash"></i><span><?php echo $formText_delete_fieldtype;?></span></button>
							<?php
							if($show_focuspoint)
							{
								?><button class="btn btn-xs btn-info set_focuspoint"><i class="glyphicon glyphicon-screenshot"></i><span><?php echo $formText_Focuspoint_fieldtype;?></span></button><?php
							}
							?>
							<?php } ?>
						</div>
					</div>
					<?php
					$i++;
				}
			}
			?></div>
			<?php if($field[9]!=1 && $field[10]!=1 && $access>=10) { ?>
			<input id="<?php echo $field_ui_id;?>counter" type="hidden" value="<?php echo $i;?>">
			<div class="modal fade" id="<?php echo $field_ui_id;?>modal" tabindex="-1" role="dialog" aria-labelledby="<?php echo $field_ui_id;?>modal" aria-hidden="true">
			    <div class="modal-dialog">
			        <div class="modal-content">
			            <div class="modal-header">
			            <h4 class="modal-title"><?php echo $formText_DragTheCroppingAreaInRightPosition_imageCropping;?></h4>
			            </div>
			            <div class="modal-body">
							<div id="<?php echo $field_ui_id;?>crop"><img /></div>
						</div>
			            <div class="modal-footer">
			                <button type="button" class="btn btn-primary" id="<?php echo $field_ui_id;?>_reset"><?php echo $formText_Reset_fieldtype;?></button>
							<button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo $formText_save_input;?></button>
						</div>
			        </div>
			    </div>
			</div>
			<div class="modal fade" id="<?php echo $field_ui_id;?>modalfocus" tabindex="-1" role="dialog" aria-labelledby="<?php echo $field_ui_id;?>modalfocus" aria-hidden="true">
			    <div class="modal-dialog">
			        <div class="modal-content">
			            <div class="modal-header">
			            <h4 class="modal-title"><?php echo $formText_MoveFocusPointToDesiredLocation_imageCropping;?></h4>
			            </div>
			            <div class="modal-body">
							<div id="<?php echo $field_ui_id;?>focus"><div id="<?php echo $field_ui_id;?>focusmark"></div></div>
						</div>
			            <div class="modal-footer">
			                <button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo $formText_save_input;?></button>
						</div>
			        </div>
			    </div>
			</div>
			<style>
			.nopadding {
			   padding: 0 !important;
			   margin: 0 !important;
			}
			#<?php echo $field_ui_id;?>_images {
				margin-top: 15px;
			}
			#<?php echo $field_ui_id;?>_images .item {
				margin:1px 1px 4px 1px;
			}
			#<?php echo $field_ui_id;?>_images .item:hover {
				/* background:rgba(255,255,255,0.5);
				margin:0px 0px 3px 0px;
				border:1px dashed #999999;
				border-radius:3px;
				cursor:move; */
			}
			#<?php echo $field_ui_id;?>crop {
				width:100%;
				height:350px;
				overflow:auto;
				background-color: #fcfcfc;
			    box-shadow: 0 0 5px rgba(0, 0, 0, 0.25) inset;
			}
			#<?php echo $field_ui_id;?>crop img {
				display:block;
				max-width:100%;
				height:auto;
			}
			#<?php echo $field_ui_id;?>focus{position:relative; background-size:contain;background-repeat:no-repeat;}
			#<?php echo $field_ui_id;?>focusmark{width:24px;height:24px;padding:0;border:0;background-image:url("<?php echo $extradir."/input/fieldtypes/".$field[4];?>/images/target.png");}
			</style>
			<script type="text/javascript">
			<?php if(isset($ob_javascript)) { ob_start(); } ?>
			$(function () {
				'use strict';
				var $<?php echo $field_ui_id;?>image = $('#<?php echo $field_ui_id;?>crop > img'),
				<?php echo $field_ui_id;?>handle,
				<?php echo $field_ui_id;?>counter,
				<?php echo $field_ui_id;?>limit = parseInt('<?php echo $image_count_limit;?>'),
				<?php echo $field_ui_id;?>limitw = parseInt('<?php echo $focus_w_limit;?>'),
				<?php echo $field_ui_id;?>limith = parseInt('<?php echo $focus_h_limit;?>');

				$("#<?php echo $field_ui_id;?>_images").sortable(/*{containment: "parent"}*/);

				$("#<?php echo $field_ui_id;?>_reset").on('click', function(){
					$<?php echo $field_ui_id;?>image.cropper('reset');
				});
				$(document).on('click', '#<?php echo $field_ui_id;?>_images .item .delete_stored', function(e){
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
										$_this.closest('.item').addClass('deleted').find('input.image').each(function(){
											$(this).val('delete|' + $(this).val());
										});
										$_this.closest('.item').hide();
									}
									fw_click_instance = false;
								}
							});
						}
					}
				});
				$('#<?php echo $field_ui_id;?>_images .item .set_focuspoint').on('click',function(e){
					e.preventDefault();
					if(!fw_click_instance)
					{
						fw_click_instance = true;
						$(this).closest('.item').find('input.focus').addClass('handlefocus');
						handle_<?php echo $field_ui_id;?>();
						fw_click_instance = false;
					}
				});

				$('#<?php echo $field_ui_id;?>_imageupload').fileupload({
			        <?php if($o_main->activate_cdn) { ?>
					url: "<?php echo $o_main->cdn_api_url;?>?param_name=<?php echo $field_ui_id;?>_images&fieldextra=<?php echo $fieldtype?>",
					<?php } else { ?>
					url: "<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_imageupload.php?param_name=<?php echo $field_ui_id;?>_images&fieldextra=<?php echo $fieldtype?>",
					<?php } ?>
			        dataType: 'json',
					start: function (e, data) {
						fw_info_message_empty();
						$('#<?php echo $field_ui_id;?>_imageprogress .progress-bar').css('width', '0%');
						$('#<?php echo $field_ui_id;?>_imageprogress').show();
					},
					<?php if($o_main->activate_cdn) { ?>
					add: function (e, data) {
						$.getJSON("<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/get_next_id_image.php?accountname=<?php echo $_GET['accountname'];?>&companyID=<?php echo $_GET['companyID'];?>&caID=<?php echo $_GET['caID'];?>", function (result) {
							data.formData = result;
							data.submit();
						});
					},
					<?php } ?>
					done: function (e, data) {
						$.each(data.result.<?php echo $field_ui_id;?>_images, function (index, file) {
							if(file.error) {
								fw_info_message_add('error', file.name + ': ' + file.error);
							} else {
								if($('#<?php echo $field_ui_id;?>_images .item:not(.deleted)').length >= <?php echo $field_ui_id;?>limit)
								{
									fw_info_message_add('error', '<?php echo $formText_FieldOnlyAllowsToUpload_fieldtype." (".$image_count_limit.") ".$formText_files_fieldtype.". ".$formText_FollowingFileWasUploadedButNotAddedToContent_fieldtype;?>: ' + file.name);
								} else {
									var <?php echo $field_ui_id;?>counter = parseInt($('#<?php echo $field_ui_id;?>counter').val())+1;
									var oDiv = $('<div/>').attr('class', 'item row').appendTo('#<?php echo $field_ui_id;?>_images');
									var oThumbCol = $('<div/>').attr('class', 'col-md-2');
									var oThumbDiv = $('<div/>').attr('class', 'thumbnail');
									var oThumbImg = $('<img/>').attr('src', '<?php echo ($o_main->activate_cdn?'':$extradomaindirroot);?>' + file.thumbUrl + '?caID=<?php echo $_GET['caID'];?>&uid=' + file.upload_id + '<?php echo ($o_main->activate_cdn?"&cdn_token=' + file.cdn_token + '":"&username=".$_COOKIE['username']."&sessionID=".$_COOKIE['sessionID']."&companyID=".$_GET['companyID']."&server_id=".array_shift(explode('.', $_SERVER['HTTP_HOST'])) );?>');
									var oTextCol = $('<div/>').attr('class', 'col-md-8').append('<strong><?php echo $formText_Filename_fieldtype;?></strong>: ' + file.name);
									var oDeleteCol = $('<div/>').attr('class', 'col-md-2');
									var oDeleteBtn = $('<button/>').attr('class', 'btn btn-xs btn-danger').attr('data-type', file.deleteType).attr('data-url', file.deleteUrl + "&param_name=<?php echo $field_ui_id;?>_images").append('<i class="glyphicon glyphicon-trash"></i><span><?php echo $formText_delete_fieldtype;?></span>');
									<?php if($show_focuspoint) { ?>var oFocusBtn = $('<button/>').attr('class', 'btn btn-xs btn-info set_focuspoint').append('<i class="glyphicon glyphicon-screenshot"></i><span><?php echo $formText_Focuspoint_fieldtype;?></span>');<?php } ?>

									oTextCol.append('<input type="hidden" name="<?php echo $field[1].$ending;?>_imagename[]" value="process|' + file.upload_id + '|' + <?php echo $field_ui_id;?>counter + '|' + file.name + '"/>');
									<?php
									foreach($resize_codes as $resize_code)
									{
										$tmp = explode(",", $resize_code);
										?>oTextCol.append('<input type="hidden" class="<?php echo ($tmp[2]=="c"?"handle":"");?>" name="<?php echo $field[1].$ending;?>_img' + <?php echo $field_ui_id;?>counter+ '[]" value="process|' + file.upload_id + '|<?php echo $resize_code;?>|' + file.width + '|' + file.height + '|' + file.url + '"<?php echo ($o_main->activate_cdn?' data-cdn-token="\' + file.cdn_token + \'"':'');?>/>');<?php

										if(strpos($tmp[3],"f")!==false)
										{
											?>
											var h = file.height, w = file.width, ratio = 0;
											if(h > w)
											{
												var ini_h = h;
												if(h > <?php echo $field_ui_id;?>limith) h = <?php echo $field_ui_id;?>limith;
												ratio = h/ini_h;
												w = Math.round(w*ratio);
											} else {
												var ini_w = w;
												if(w > <?php echo $field_ui_id;?>limitw) w = <?php echo $field_ui_id;?>limitw;
												ratio = w/ini_w;
												h = Math.round(h*ratio);
											}
											oTextCol.append('<input class="focus handlefocus" type="hidden" name="<?php echo $field[1].$ending."_focus";?>' + <?php echo $field_ui_id;?>counter+ '[]" value="0:0" data-src="<?php echo ($o_main          ->activate_cdn?'':$extradomaindirroot);?>' + file.url + '?caID=<?php echo $_GET['caID'];?>&uid=' + file.upload_id + '<?php echo ($o_main->activate_cdn?"&cdn_token' + file.cdn_token + '":"&username=".$_COOKIE['username']."&sessionID=".$_COOKIE['sessionID']."&companyID=".$_GET['companyID']."&server_id=".array_shift(explode('.', $_SERVER['HTTP_HOST'])) );?>" data-w="' + w + '" data-h="' + h + '" data-x="0" data-y="0" data-ratio="' + ratio + '">');<?php
										}
									}
									if($show_link)
									{
										?>oTextCol.append('<div class="row"><div class="col-md-4"><b><?php echo $formText_PictureLink_fieldtype;?></b>:</div><div class="col-md-8"><input type="text" name="<?php echo $field[1].$ending;?>_link' + <?php echo $field_ui_id;?>counter + '" value=""/></div></div>');<?php
									}
									if($show_text)
									{
										foreach($output_languages as $lid => $value)
										{
											?>oTextCol.append('<div class="row"><div class="col-md-4"><?php echo "<b>".$formText_Picturetext_fieldtype."</b>"." <i>".$value."</i>";?>:</div><div class="col-md-8"><input type="text" name="<?php echo $field[1].$ending;?>_label<?php echo $lid;?>' + <?php echo $field_ui_id;?>counter + '" value=""/></div></div>');<?php
											if($singleLanguage) break; // stop for single language
										}
									}
									?>
									oThumbCol.appendTo(oDiv);
									oThumbDiv.appendTo(oThumbCol);
									oThumbImg.appendTo(oThumbDiv);
									oTextCol.appendTo(oDiv);
									oDeleteBtn.on("click", function() {
										$.post($(this).data('url'), function(data){
											if(data[file.name] == true) oDiv.remove();
										},"json");
									});
									oDeleteBtn.appendTo(oDeleteCol);
									<?php if($show_focuspoint) { ?>
									oFocusBtn.on("click", function() {
										$(this).closest('.item').find('input.focus').addClass('handlefocus');
										handle_<?php echo $field_ui_id;?>();
									});
									oFocusBtn.appendTo(oDeleteCol);
									<?php } ?>
									oDeleteCol.appendTo(oDiv);

									$('#<?php echo $field_ui_id;?>counter').val(<?php echo $field_ui_id;?>counter);
								}
							}
			            });
						$(window).trigger('resize');
			        },
			        progressall: function (e, data) {
			            var progress = parseInt(data.loaded / data.total * 100, 10);
			            $('#<?php echo $field_ui_id;?>_imageprogress .progress-bar').css(
			                'width',
			                progress + '%'
			            );
			        },
					stop: function(e) {
						setTimeout(function() { $('#<?php echo $field_ui_id;?>_imageprogress').hide(); }, 500);
						fw_info_message_show();
						handle_<?php echo $field_ui_id;?>();
					}
			    }).prop('disabled', !$.support.fileInput)
			        .parent().addClass($.support.fileInput ? undefined : 'disabled');

				$('#<?php echo $field_ui_id;?>modal').on('hidden.bs.modal', function () {
					var data = $<?php echo $field_ui_id;?>image.cropper('getData');
					var str = $(<?php echo $field_ui_id;?>handle).val() + obj_to_string_<?php echo $field_ui_id;?>(data);
					$(<?php echo $field_ui_id;?>handle).val(str);
					$<?php echo $field_ui_id;?>image.cropper('destroy');
					$(window).trigger('resize');
					handle_<?php echo $field_ui_id;?>();
				});
				$('#<?php echo $field_ui_id;?>modalfocus').on('hidden.bs.modal', function () {
					handle_<?php echo $field_ui_id;?>();
				});
				$('#<?php echo $field_ui_id;?>focusmark').draggable({
					containment: "parent",
					stop: function() {
						$(<?php echo $field_ui_id;?>handle).val(
							Math.round((<?php echo $field_ui_id;?>rempx($(this).css('left'))+12)/$(this).data('ratio'))
							+':'+
							Math.round((<?php echo $field_ui_id;?>rempx($(this).css('top'))+12)/$(this).data('ratio'))
						).data('x',$(this).css('left')).data('y',$(this).css('top'));
					}
				});
				function <?php echo $field_ui_id;?>rempx(string){
					return Number(string.substring(0, (string.length - 2)));
				}

				function handle_<?php echo $field_ui_id;?>()
				{
					var handle = $('#<?php echo $field_ui_id;?>_images .handle'),
						handlefocus = $('#<?php echo $field_ui_id;?>_images .handlefocus');
					if(handle.length > 0)
					{
						<?php echo $field_ui_id;?>handle = $(handle).get(0);
						var option = $(<?php echo $field_ui_id;?>handle).val().split(':');
						var size = option[2].split(',');
						if(option[0] == 'process' && size[2] == 'c')
						{
							$('#<?php echo $field_ui_id;?>modal').modal({show:true});
							$<?php echo $field_ui_id;?>image.attr('src', '<?php echo ($o_main->activate_cdn?'':$extradomaindirroot);?>' + option[5] + '?caID=<?php echo $_GET['caID'];?>&uid=' + option[1] + '<?php echo ($o_main->activate_cdn?"&cdn_token=' + $(".$field_ui_id."handle).data(\"cdn-token\") + '":"&username=".$_COOKIE['username']."&sessionID=".$_COOKIE['sessionID']."&companyID=".$_GET['companyID']);?>&_=' + Math.random()).cropper({
								strict:false,
								zoomable:false,
								rotatable:false,
								aspectRatio:size[0]/size[1],
								autoCropArea:1,
							});
						}
						$(<?php echo $field_ui_id;?>handle).removeClass("handle");
					}
					else if(handlefocus.length > 0)
					{
						<?php echo $field_ui_id;?>handle = $(handlefocus).get(0);
						$('#<?php echo $field_ui_id;?>focus').css({
							'background-image': 'url('+$(<?php echo $field_ui_id;?>handle).data('src')+')',
							width: $(<?php echo $field_ui_id;?>handle).data('w'),
							height: $(<?php echo $field_ui_id;?>handle).data('h'),
						});
						$('#<?php echo $field_ui_id;?>focusmark').css({
							left: $(<?php echo $field_ui_id;?>handle).data('x'),
							top: $(<?php echo $field_ui_id;?>handle).data('y'),
						}).data('ratio',$(<?php echo $field_ui_id;?>handle).data('ratio'));
						$('#<?php echo $field_ui_id;?>modalfocus').modal({show:true});

						$(<?php echo $field_ui_id;?>handle).removeClass("handlefocus");
					}
				}
				function obj_to_string_<?php echo $field_ui_id;?>(obj)
				{
					var str = '';
					for (var p in obj) {
						if (obj.hasOwnProperty(p)) {
							str += '|' + p + '!' + obj[p];
						}
					}
					return str;
				}
			});
			<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
			</script>
			<?php } ?>
			<script type="text/javascript">
			<?php if(isset($ob_javascript)) { ob_start(); } ?>
			$(function () {
				$("a.<?php echo $field_ui_id;?>_fancy").fancybox({mouseWheel:false,helpers:{overlay:{locked:false}}});
			});
			<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
			</script>
		</div>
	</div>
	<script type="text/javascript">
		$(".video_settings_edit").off("click").on("click", function(){
			var target = $(this).data("target");
			if($(target).is(":visible")){
				$(target).slideUp();
				$(this).removeClass("active");
			} else {
				$(target).slideDown();
				$(this).addClass("active");
			}
		})
		$("#<?php echo $field_ui_id;?>_tab_changer .youtube_tab").off("click").on("click", function(){
			$('#<?php echo $field_ui_id;?>_type').val('youtube');
			$(".<?php echo $field_ui_id;?>_tab_file").removeClass("active");
			$("#<?php echo $field_ui_id;?>_tab_vimeo").removeClass("active");
			$("#<?php echo $field_ui_id;?>_tab_youtube").addClass("active");
			$("#<?php echo $field_ui_id;?>_tab_changer .file_tab").removeClass("active");
			$("#<?php echo $field_ui_id;?>_tab_changer .vimeo_tab").removeClass("active");
			$("#<?php echo $field_ui_id;?>_tab_changer .youtube_tab").addClass("active");
		})
		$("#<?php echo $field_ui_id;?>_tab_changer .vimeo_tab").off("click").on("click", function(){
			$('#<?php echo $field_ui_id;?>_type').val('vimeo');
			$(".<?php echo $field_ui_id;?>_tab_file").removeClass("active");
			$("#<?php echo $field_ui_id;?>_tab_youtube").removeClass("active");
			$("#<?php echo $field_ui_id;?>_tab_vimeo").addClass("active");
			$("#<?php echo $field_ui_id;?>_tab_changer .file_tab").removeClass("active");
			$("#<?php echo $field_ui_id;?>_tab_changer .youtube_tab").removeClass("active");
			$("#<?php echo $field_ui_id;?>_tab_changer .vimeo_tab").addClass("active");
		})
		$("#<?php echo $field_ui_id;?>_tab_changer .file_tab").off("click").on("click", function(){
			$('#<?php echo $field_ui_id;?>_type').val('file');
			$("#<?php echo $field_ui_id;?>_tab_youtube").removeClass("active");
			$("#<?php echo $field_ui_id;?>_tab_vimeo").removeClass("active");
			$(".<?php echo $field_ui_id;?>_tab_file").addClass("active");
			$("#<?php echo $field_ui_id;?>_tab_changer .youtube_tab").removeClass("active");
			$("#<?php echo $field_ui_id;?>_tab_changer .vimeo_tab").removeClass("active");
			$("#<?php echo $field_ui_id;?>_tab_changer .file_tab").addClass("active");
		})

	</script>
	<?php
}
?>
<style>
	#<?php echo $field_ui_id;?>_tab_changer {
		display: inline-block;
		background: #fff;
		border: 1px solid #9fa2a4;
		border-radius: 5px;
		margin-bottom: 10px;
	}
	#<?php echo $field_ui_id;?>_tab_changer .tab_switcher_btn {
		float: left;
		padding: 5px 20px;
		cursor: pointer;
		z-index: 10;
		position: relative;
	}
	#<?php echo $field_ui_id;?>_tab_changer .tab_switcher_btn.active {
		color: #fff;
		background: #4aa5f9;
	}
	#<?php echo $field_ui_id;?>_tab_youtube,
	#<?php echo $field_ui_id;?>_tab_vimeo,
	#<?php echo $field_ui_id;?>_tab_file {
		display: none;
	}
	#<?php echo $field_ui_id;?>_tab_file .imageWrapper {
		margin-top: 15px;
	}
	#<?php echo $field_ui_id;?>_tab_youtube.active,
	#<?php echo $field_ui_id;?>_tab_vimeo.active,
	#<?php echo $field_ui_id;?>_tab_file.active {
		display: block;
	}
	#<?php echo $field_ui_id;?> {
		border-radius: 0;
		border: 1px solid #9fa2a4;
	}
	#<?php echo $field_ui_id;?>_settings_edit {
		float: right;
		margin-top: -34px;
		height: 34px;
		padding: 10px 10px;
		background: #4aa5f9;
		cursor: pointer;
		z-index: 1;
		position: relative;
		border: 1px solid #9fa2a4;
		color: #fff;
	}
	#<?php echo $field_ui_id;?>_settings_edit .arrowdown {
		display: none;
		position:absolute;
		left: 50%;
		margin-left: -15px;
		width: 0;
		height: 0;
		border-left: 15px solid transparent;
		border-right: 15px solid transparent;
		border-top: 15px solid #4aa5f9;
	}
	#<?php echo $field_ui_id;?>_settings_edit.active .arrowdown {
		display:block;
	}
	#<?php echo $field_ui_id;?>_settings {
		display: none;
		padding: 10px 15px 0px;
		background: #ffffff;
		border: 1px solid #9fa2a4;
		margin-top: 10px;
	}
	#<?php echo $field_ui_id;?>_settings .video_settings_item {
		display: inline-block;
		vertical-align: middle;
		margin-bottom: 10px;
	}
	#<?php echo $field_ui_id;?>_settings input {
		margin: 0;
		display: inline-block;
		width: auto;
		vertical-align: middle;
	}
	#<?php echo $field_ui_id;?>_settings label {
		display: inline-block;
		vertical-align: middle;
		margin-left: 10px;
		margin-right: 20px;
	}
	.clear {
		clear: both;
	}
</style>
