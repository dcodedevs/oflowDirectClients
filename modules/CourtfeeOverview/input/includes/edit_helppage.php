<?php
if(!function_exists('ftp_file_put_content')) include(__DIR__."/ftp_commands.php");
if(!function_exists('format_form_variable')) include(__DIR__.'/fn_format_form_variable.php');
if(!function_exists('devide_by_uppercase')) include(__DIR__.'/fnctn_devide_by_uppercase.php');
$s_layout_file = realpath(__DIR__.'/../../').'/output_helppage/output.php';
$s_content_server_api_url = 'https://s21.getynet.com/scontent/api/';

if(isset($_POST['save_helppage']) && 1 == $_POST['save_helppage'])
{
	define('BASEPATH', realpath(__DIR__.'/../../../../').DIRECTORY_SEPARATOR);
	require_once(BASEPATH.'elementsGlobal/cMain.php');
	if(!function_exists('APIconnectAccount')) require_once(__DIR__.'/APIconnect.php');
	
	$s_sql = "SELECT * FROM session_framework WHERE companyaccessID = '".$o_main->db->escape_str($_GET['caID'])."' AND session = '".$o_main->db->escape_str($_COOKIE['sessionID'])."' AND username = '".$o_main->db->escape_str($_COOKIE['username'])."'".($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"")." LIMIT 1";
	$o_query = $o_main->db->query($s_sql);
	$fw_session = ($o_query && $o_query->num_rows()>0) ? $o_query->row_array() : array();
	$menuaccess = json_decode($fw_session['cache_menu'],true);
	$access = $menuaccess[$_GET['module']][2];
	if($access >= 10)
	{
		$b_handle_block = FALSE;
		foreach($_POST as $s_key => $s_value)
		{
			if('block-start' == $s_key)
			{
				$s_block_type = '';
				$b_handle_block = TRUE;
				$s_block_layout = '';
			}
			if('block-end' == $s_key)
			{
				$b_handle_block = FALSE;
			}
			if(!$b_handle_block) continue;
			
			if('block-type-' == substr($s_key, 0, 11))
			{
				$s_block_type = $s_value;
				$l_counter_len = strlen($s_key) - 11;
				$s_block_layout .= ('' != $s_block_layout ? '</div>'.PHP_EOL : '').'<div class="help-page-block '.$s_value.'">'.PHP_EOL;
			}
			
			$s_element = substr($s_key, 0, -$l_counter_len);
			if('text-block' == $s_block_type && ('title-' == $s_element || 'text-' == $s_element))
			{
				$s_value = format_form_variable($s_value, 'Helppage');
				if('' != $s_value)
				{
					$s_block_layout .= '<div class="'.substr($s_element, 0, -$l_counter_len).'"><?php echo '.$s_value.';?></div>'.PHP_EOL;
				}
			}
			
			if('text-image-block' == $s_block_type && ('title-' == $s_element || 'text-' == $s_element))
			{
				$s_value = format_form_variable($s_value, 'Helppage');
				if('' != $s_value)
				{
					$s_block_layout .= '<div class="'.substr($s_element, 0, -$l_counter_len).'"><?php echo '.$s_value.';?></div>'.PHP_EOL;
				}
			}
			if('text-image-block' == $s_block_type && 'image-' == $s_element)
			{
				if('' != $s_value)
				{
					$v_file = handle_file($s_value, $s_content_server_api_url);
					$s_block_layout .= '<div class="'.substr($s_element, 0, -$l_counter_len).'"><img src="'.$v_file['url'].'"><input type="hidden" class="options" value="handled|'.$v_file['upload_id'].'|'.$v_file['name'].'|'.$v_file['url'].'"></div>'.PHP_EOL;
				}
			}
			
		}
		$s_block_layout .= ('' != $s_block_layout ? '</div>'.PHP_EOL : '');
		
		foreach($_POST['delete_file'] as $s_options)
		{
			handle_file(str_replace('handled|', 'delete|', $s_options), $s_content_server_api_url);
		}
		
		ftp_file_put_content(str_replace(BASEPATH, '/', $s_layout_file), $s_block_layout);
		
		header("Location: ".substr($_SERVER['HTTP_REFERER'],0,strpos($_SERVER['HTTP_REFERER'],"?"))."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&submodule=".$_GET['submodule']."&includefile=".$_GET['includefile']);
	} else {
		?><div id="hovedfeltStrek"><table style="width:100%"><tr><td class="notAccessField" >You have no access to this module</td></tr></table></div><?php
	}
}

function handle_file($s_options, $s_content_server_api_url)
{
	$o_main = get_instance();
	
	$b_handle = FALSE;
	$v_return = array(
		'status' => 0
	);
	
	$v_options = explode('|', $s_options);
	
	// Handle on CDN server
	$v_upload = array(
		'file_count_limit' => 1,
		'items' => array()
	);
	//process|' + file.upload_id + '|' + file.name + '|' + file.url
	if('process' == $v_options[0])
	{
		// new uploads
		$b_handle = TRUE;
		$file = array(
			'action'=>'',
			'items'=>array(),
		);
		$file['filename'] = $v_options[2];
		$file['upload_id'] = $v_options[1];
		
		$file['action'] = 'process';
		$v_properties = array();
		$v_properties['protected'] = FALSE;
		$v_properties['path'] = $v_options[3];
		$file['items'][] = $v_properties;
		$file['labels'] = array();
		$file['links'] = '';
		$file['remove_original'] = TRUE;
		$v_upload['items'][] = $file;
	} else if('delete' == $v_options[0]) {
		$b_handle = TRUE;
		$file = array(
			'action'=>'',
			'items'=>array(),
		);
		$file['filename'] = $v_options[2];
		$file['upload_id'] = $v_options[1];
		$file['action'] = 'delete';
		$file['items'][] = $v_options[3];
		$file['labels'] = array();
		$file['links'] = '';
		$file['remove_original'] = TRUE;
		$v_upload['items'][] = $file;
	} else {
		$v_return = array(
			'status' => 1,
			'upload_id' => $v_options[1],
			'name' => $v_options[2],
			'url' => $v_options[3],
		);
	}
	
	if($b_handle)
	{
		$s_response = APIconnectAccount("account_authenticate", $o_main->accountinfo['accountname'], $o_main->accountinfo['password']);
		$v_response = json_decode($s_response, TRUE);
		
		$v_upload['data'] = json_encode(array('action'=>'handle_file'));
		$v_upload['items'] = json_encode($v_upload['items']);
		$v_upload['username'] = $_COOKIE['username'];
		$v_upload['accountname'] = $o_main->accountinfo['accountname'];
		$v_upload['token'] = $v_response['token'];
		
		//call api
		$ch = curl_init($s_content_server_api_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $v_upload);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		
		$s_response = curl_exec($ch);
		if($s_response !== false && $s_response != "")
		{
			$v_response = json_decode($s_response, TRUE);
			if(isset($v_response['status']) && 1 == $v_response['status'])
			{
				if(isset($v_response['errors']) && 0 < sizeof($v_response['errors']))
				{
					foreach($v_response['errors'] as $s_error) $v_return['message'][] = $s_error;
				} else {
					$s_files = $v_response['items'];
					$v_files = json_decode($s_files, TRUE);
					$v_return = array(
						'status' => 1,
						'upload_id' => $v_options[1],
						'name' => $v_options[2],
						'url' => $v_files[0][1][0],
					);
				}
			}
		} else {
			$v_return['message'][] = "Error occurred handling request";
		}
	}
	
	return $v_return;
}

?>
<form action="../modules/<?php echo $_GET['module']."/input/includes/".$_GET['includefile'].".php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&submodule=".$_GET['submodule']."&includefile=".$_GET['includefile'];?>" method="post">
	<input type="hidden" name="save_helppage" value="1">
	<input type="hidden" name="block-start" value="1">
	<div id="helppage-layout-container">
		<?php
		$l_block_count = 1;
		if(is_file($s_layout_file))
		{
			$s_content = str_replace(array('<?php echo $', ';?>'), '', file_get_contents($s_layout_file));
			//$v_items = simplexml_load_string('<div>'.$s_content.'</div>');
			//print_r($v_items);
			$doc = new DOMDocument();
			$doc->loadHTML('<section>'.$s_content.'</section>');
			$divs = $doc->getElementsByTagName('section');
			//print_r($divs);
			foreach($divs as $l_key => $item)
			{
				//echo $l_key."\n\n";print_r($item);
				//if($item->hasAttributes()) echo $item->getAttribute('class')."\n\n";
				foreach($item->childNodes as $block)
				{
					//echo $l_key."\n\n";print_r($block);
					if($block->hasAttributes() && 'help-page-block' == substr($block->getAttribute('class'), 0, 15))
					{
						$s_block_type = substr($block->getAttribute('class'), 16);
						if('text-block' == $s_block_type)
						{
							$v_data = array();
							foreach($block->childNodes as $element)
							{
								if($element->hasAttributes() && '' != $element->getAttribute('class'))
								{
									$v_tmp = explode('_', $element->nodeValue);
									$v_data[$element->getAttribute('class')] = devide_by_uppercase(isset($v_tmp[1]) ? $v_tmp[1] : '');
								}
							}
							?><div class="text-block">
								<input type="hidden" class="block-type input-field" name="block-type-<?php echo $l_block_count;?>" value="text-block">
								<div class="panel panel-default">
									<div class="panel-heading">
										<div class="row">
											<div class="col-xs-11">
												<input type="text" class="form-control input-field" name="title-<?php echo $l_block_count;?>" placeholder="<?php echo $formText_GiveNameForTitleField_EditHelppage;?>" value="<?php echo $v_data['title'];?>">
											</div>
											<div class="col-xs-1">
												<button type="button" class="close" aria-label="Close" onClick="javascript:$(this).closest('.text-block').remove();"><span aria-hidden="true">&times;</span></button>
											</div>
										</div>
									</div>
									<div class="panel-body">
										<input type="text" class="form-control input-field" name="text-<?php echo $l_block_count;?>" placeholder="<?php echo $formText_GiveNameForTextField_EditHelppage;?>" value="<?php echo $v_data['text'];?>">
									</div>
								</div>
							</div><?php
						}
						
						if('text-image-block' == $s_block_type)
						{
							$v_data = array();
							foreach($block->childNodes as $element)
							{
								if($element->hasAttributes() && '' != $element->getAttribute('class'))
								{
									if('image' == $element->getAttribute('class'))
									{
										foreach($element->childNodes as $object)
										{
											if($object->hasAttributes() && '' != $object->getAttribute('class'))
											{
												if('options' == $object->getAttribute('class'))
												{
													$v_data[$element->getAttribute('class')] = explode('|', $object->getAttribute('value'));
												}
											}
										}
									} else {
										$v_tmp = explode('_', $element->nodeValue);
										$v_data[$element->getAttribute('class')] = devide_by_uppercase(isset($v_tmp[1]) ? $v_tmp[1] : '');
									}
								}
							}
							?><div class="text-image-block">
								<input type="hidden" class="block-type input-field" name="block-type-<?php echo $l_block_count;?>" value="text-image-block">
								<div class="panel panel-default">
									<div class="panel-heading">
										<div class="row">
											<div class="col-xs-11">
												<input type="text" class="form-control input-field" name="title-<?php echo $l_block_count;?>" placeholder="<?php echo $formText_GiveNameForTitleField_EditHelppage;?>" value="<?php echo $v_data['title'];?>">
											</div>
											<div class="col-xs-1">
												<button type="button" class="close" aria-label="Close" onClick="javascript:$(this).closest('.text-image-block').find('.btn-delete').trigger('click');$(this).closest('.text-image-block').remove();"><span aria-hidden="true">&times;</span></button>
											</div>
										</div>
									</div>
									<div class="panel-body">
										<div class="row">
											<div class="col-xs-8">
												<input type="text" class="form-control input-field" name="text-<?php echo $l_block_count;?>" placeholder="<?php echo $formText_GiveNameForTextField_EditHelppage;?>" value="<?php echo $v_data['text'];?>">
											</div>
											<div class="col-xs-4 items">
												<div class="uploader no-draggable dropzone"<?php echo (0 < sizeof($v_data['image']) ? ' style="display:none;"' : '');?>>
													<?php echo $formText_DragAndDropOr_EditHelppage;?>
													<span class="fileinput-button">
														<span><?php echo $formText_BrowseFile_EditHelppage;?></span>
														<input class="edit_helppage_upload" type="file" name="edit_helppage_files[]" multiple>
													</span>
												</div>
												<?php if(0 < sizeof($v_data['image'])) { ?>
												<div class="item"><div class="thumbnail"><img src="<?php echo $v_data['image'][3];?>"></div><div><input type="hidden" name="image-<?php echo $l_block_count;?>" value="<?php echo implode('|', $v_data['image']);?>"></div><div class="text-right"><button type="button" class="btn btn-sm btn-delete" data-status="handled"><i class="glyphicon glyphicon-trash"></i><div>Delete</div></button></div></div>
												<?php } ?>
											</div>
										</div>
									</div>
								</div>
							</div><?php
						}
						$l_block_count++;
					}
				}
			}
		}
		?>
	</div>
	<input type="hidden" name="block-end" value="1">
	<div id="helppage-block-buttons">
		<button type="button" data-type="text-block"><?php echo $formText_TextBlock_EditHelppage;?></button>
		<button type="button" data-type="text-image-block"><?php echo $formText_TextAndImageBlock_EditHelppage;?></button>
	</div>
	<div class="fieldholder" style="padding-top:5px; padding-left:10px;">
		<input style="background-color:#cccccc; font-family:Verdana, Arial, Helvetica, sans-serif; border:1px solid #000000; font-size:10px; font-weight:bold; padding-left:17px; padding-right:17px; line-height:20px;" type="submit" name="send" value="Save" />
	</div>  
</form>

<div id="helppage-block-elements" style="display:none;">

	<div class="text-block">
		<input type="hidden" class="block-type input-field" name="block-type" value="text-block">
		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-11">
						<input type="text" class="form-control input-field" name="title" placeholder="<?php echo $formText_GiveNameForTitleField_EditHelppage;?>">
					</div>
					<div class="col-xs-1">
						<button type="button" class="close" aria-label="Close" onClick="javascript:$(this).closest('.text-block').remove();"><span aria-hidden="true">&times;</span></button>
					</div>
				</div>
			</div>
			<div class="panel-body">
				<input type="text" class="form-control input-field" name="text" placeholder="<?php echo $formText_GiveNameForTextField_EditHelppage;?>">
			</div>
		</div>
	</div>
	
	<div class="text-image-block">
		<input type="hidden" class="block-type input-field" name="block-type" value="text-image-block">
		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="row">
					<div class="col-xs-11">
						<input type="text" class="form-control input-field" name="title" placeholder="<?php echo $formText_GiveNameForTitleField_EditHelppage;?>">
					</div>
					<div class="col-xs-1">
						<button type="button" class="close" aria-label="Close" onClick="javascript:$(this).closest('.text-block').remove();"><span aria-hidden="true">&times;</span></button>
					</div>
				</div>
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-xs-8">
						<input type="text" class="form-control input-field" name="text" placeholder="<?php echo $formText_GiveNameForTextField_EditHelppage;?>">
					</div>
					<div class="col-xs-4 items">
						<div class="uploader no-draggable dropzone">
							<?php echo $formText_DragAndDropOr_EditHelppage;?>
							<span class="fileinput-button">
								<span><?php echo $formText_BrowseFile_EditHelppage;?></span>
								<input class="edit_helppage_upload" type="file" name="edit_helppage_files[]" multiple>
							</span>
						</div>
						
					</div>
				</div>
			</div>
		</div>
	</div>
	<input type="hidden" id="edit_helppage_upload_counter" value="0">
</div>

<script type="text/javascript">
var edit_helppage_upload_alert_shown;
var edit_helppage_upload_counter;
var block_cnt = parseInt('<?php echo $l_block_count;?>');
$(function(){
	$('#helppage-block-buttons button').off('click').on('click', function(e){
		e.preventDefault();
		var $block = $('#helppage-block-elements div.' + $(this).data('type')).clone();
		$block.find('.input-field').each(function(){$(this).prop('name', $(this).prop('name') + '-' + block_cnt);});
		$('#helppage-layout-container').append($block);
		if($block.find('.edit_helppage_upload').length > 0)
		{
			$block.find('.edit_helppage_upload').data('block-id', block_cnt);
			bind_image_upload($block.find('.edit_helppage_upload'));
		}
		block_cnt++;
	});
	$('.text-image-block .btn-delete').off('click').on('click', function(e){
		if($(this).data('status') && 'handled' == $(this).data('status'))
		{
			e.preventDefault();
			var $input = $(this).closest('.item').find('input').clone();
			$input.attr('name', 'delete_file[]');
			$('#helppage-layout-container').closest('form').append($input);
			$(this).closest('.items').find('.uploader').show();
			$(this).closest('.item').remove();
		}
	});
});

function bind_image_upload(upload_obj)
{
	$(upload_obj).fileupload({
        dropZone: $(this).closest('.dropzone'),
		url: "<?php echo $s_content_server_api_url;?>?param_name=edit_helppage_files&fieldextra=t1",
        dataType: 'json',
		//limitMultiFileUploads: 3,
		//limitMultiFileUploadSize: 1000000,
		sequentialUploads: true,
		start: function (e, data) {
			fw_info_message_empty();
		},
		add: function (e, data) {
			fw_editing_instance = true;
			var _this = this;
			$.getJSON("<?php echo $extradir;?>/input/fieldtypes/File/get_next_id.php", function (result){
				$.each(data.files, function (index, file){
					var tmp_upload_id = btoa(encodeURIComponent(file.name));
					var html = 
					'<div class="item row" data-tmp-upload-id="' + tmp_upload_id + '">' +
						'<div class="name">' + file.name + '</div>' +
						'<input class="name" type="hidden" name="image-' + $(_this).data('block-id') + '" value="process|' + tmp_upload_id + '|' + file.name + '">' +
						'<div class="progress">' +
							'<div class="progress-bar"></div>' +
						'</div>' +
					'</div>';
					$(_this).closest('.items').append(html);
				});
				data.formData = result;
				data.submit();
			});
		},
		done: function (e, data) {
			var _this = this;
			$.each(data.result.edit_helppage_files, function (index, file) {
				if(file.error) {
					fw_info_message_add('error', file.name + ': ' + file.error);
					$(_this).closest('.items').find('[data-tmp-upload-id="' + btoa(encodeURIComponent(file.name)) + '"]').remove();
				} else {
					if($(_this).closest('.items').find('.item:not(.deleted):not(.abortable)').length > 1)
					{
						if(!edit_helppage_upload_alert_shown)
						{
							fw_info_message_add('error', '<?php echo $formText_MaximumNumberOfAllowableFileUploadsHasBeenExceeded_Fieldtype."! ".$formText_FieldOnlyAllowsToUpload_fieldtype." ".$image_count_limit." ".$formText_filesInTotal_Fieldtype;?>.');
						}
						edit_helppage_upload_alert_shown = true;
						$.post(file.deleteUrl + "&param_name=edit_helppage_files", function(data){
							if(data[file.name] == true) $('.items [data-tmp-upload-id="' + btoa(encodeURIComponent(file.name)) + '"]').remove();
						},"json");
					} else {
						file.no_handle = true;
						var edit_helppage_upload_counter = parseInt($('#edit_helppage_upload_counter').val())+1;
						var oDiv = $('<div/>').attr('class', 'item');
						var oThumbCol = $('<div/>').attr('class', 'thumbnail');
						var oThumbImg = $('<img/>').attr('src', file.url + '?username=<?php echo $_COOKIE['username'];?>&sessionID=<?php echo $_COOKIE['sessionID'];?>&companyID=<?php echo $_GET['companyID'];?>&caID=<?php echo $_GET['caID'];?>&uid=' + file.upload_id + '&server_id=<?php echo array_shift(explode('.', $_SERVER['HTTP_HOST']));?>');
						var oTextCol = $('<div/>');
						var oDeleteCol = $('<div/>').attr('class', 'text-right');
						var oDeleteBtn = $('<button/>').attr('class', 'btn btn-sm btn-delete').attr('type', 'button').attr('data-type', file.deleteType).attr('data-url', file.deleteUrl + "&param_name=edit_helppage_files").append('<i class="glyphicon glyphicon-trash"></i><div><?php echo $formText_delete_fieldtype;?></div>');
						
						$(_this).closest('.items').find('[data-tmp-upload-id="' + btoa(encodeURIComponent(file.name)) + '"]').replaceWith(oDiv);
						oTextCol.append('<input type="hidden" name="image-' + $(_this).data('block-id') + '" value="process|' + file.upload_id + '|' + file.name + '|' + file.url + '"/>');
						oThumbCol.appendTo(oDiv);
						oThumbImg.appendTo(oThumbCol);
						oTextCol.appendTo(oDiv);
						oDeleteBtn.on("click", function(e) {
							e.preventDefault();
							$.post($(this).data('url'), function(data){
								if(data[file.name] == true) oDiv.remove();
								$(_this).closest('.uploader').show();
							},"json");
						});
						oDeleteBtn.appendTo(oDeleteCol);
						oDeleteCol.appendTo(oDiv);
						
						$('#edit_helppage_upload_counter').val(edit_helppage_upload_counter);
					}
				}
            });
			$(_this).closest('.uploader').hide();
			$(window).trigger('resize');
        },
		progress: function (e, data) {
			var progress = parseInt(data._progress.loaded / data._progress.total * 100, 10);
			$(this).closest('.items').find('[data-tmp-upload-id="' + btoa(encodeURIComponent(data.files[0].name)) + '"] .progress-bar').css('width', progress + '%');
		},
		stop: function(e) {
			edit_helppage_upload_alert_shown = false;
			fw_editing_instance = false;
			fw_info_message_show();
		}
    }).prop('disabled', !$.support.fileInput);
}
</script>
<style type="text/css">
.uploader {
	border: 1px dashed #ababab;
	min-height: 120px;
	text-align: center;
	overflow: hidden;
}
</style>