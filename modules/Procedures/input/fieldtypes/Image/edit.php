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
<div id="<?php echo $field_ui_id;?>_files" class="files"><?php
$i=0;
if($field[6][$langID] != "")
{
	if(!stristr($field[6][$langID][0],"[")) // ali - fix for old image style
	{
		$tmp = explode(";",$field[6][$langID]);
		$data = array(array(substr($tmp[0],strrpos($tmp[0],"/")+1), $tmp, array()));
		
	} else { // normal image processing
		$data = json_decode($field[6][$langID], true);
	}
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
		$thumb_img_link = $extradomaindirroot.$thumb_img;
		?>
		<div class="item row">
			<div class="col-md-2">
				<div class="thumbnail"><a rel="gal_<?php echo $field_ui_id."_".$i;?>" class="<?php echo $field_ui_id;?>_fancy script" href="<?php echo $extradomaindirroot.$popup_img.(strpos($popup_img,'uploads/protected/')!==false?'?caID='.$_GET['caID'].'&table='.$fields[0][3].'&field='.$field[0].'&ID='.$ID.'&languageID='.$variables->languageID:'');?>"><img class="ptr" src="<?php echo $thumb_img_link.(strpos($thumb_img_link,'uploads/protected/')!==false?'?caID='.$_GET['caID'].'&table='.$fields[0][3].'&field='.$field[0].'&ID='.$ID.'&languageID='.$variables->languageID:'');?>"></a></div>
			</div>
			<div class="col-md-8">
				<strong><?php echo $formText_Filename_fieldtype;?></strong>: <?php echo $name;?>
				<input class="name" type="hidden" name="<?php echo $field[1].$ending."_name";?>[]" value="<?php echo ":".$upload_id.":".$i.":".$name;?>"><?php
				$focus_counter = 0;
				foreach($image as $x=>$item)
				{
					$tmp = explode(",", $resize_codes[$x]);
					?><input class="image" type="hidden" name="<?php echo $field[1].$ending."_img".$i;?>[]" value="<?php echo $item;?>"><?php
					if(strpos($tmp[3],"f")!==false && is_file(ACCOUNT_PATH."/".rawurldecode($item)))
					{
						list($w,$h,$t,$r) = getimagesize(ACCOUNT_PATH."/".rawurldecode($item));
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
						?><input class="focus" type="hidden" name="<?php echo $field[1].$ending."_focus".$i;?>[]" value="<?php echo $focus[$focus_counter];?>" data-src="<?php echo $extradomaindirroot.$item.(strpos($item,'uploads/protected/')!==false?'?caID='.$_GET['caID'].'&table='.$fields[0][3].'&field='.$field[0].'&ID='.$ID.'&languageID='.$variables->languageID:'');?>" data-w="<?php echo $w;?>" data-h="<?php echo $h;?>" data-x="<?php echo $x-12;?>" data-y="<?php echo $y-12;?>" data-ratio="<?php echo $ratio;?>"><?php
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
<div class="row">
	<div class="col-md-2">
		<span class="btn btn-success btn-xs fileinput-button">
			<i class="glyphicon glyphicon-plus"></i>
			<span><?php echo $formText_SelectImages_fieldtype;?></span>
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
	
	$("#<?php echo $field_ui_id;?>_files").sortable(/*{containment: "parent"}*/);
	
	$("#<?php echo $field_ui_id;?>_reset").on('click', function(){
		$<?php echo $field_ui_id;?>image.cropper('reset');
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
							$_this.closest('.item').addClass('deleted').find('input.image').each(function(){
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
	$('#<?php echo $field_ui_id;?>_files .item .set_focuspoint').on('click',function(e){
		e.preventDefault();
		if(!fw_click_instance)
		{
			fw_click_instance = true;
			$(this).closest('.item').find('input.focus').addClass('handlefocus');
			handle_<?php echo $field_ui_id;?>();
			fw_click_instance = false;
		}
	});
	
	$('#<?php echo $field_ui_id;?>_upload').fileupload({
        url: "<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_upload.php?param_name=<?php echo $field_ui_id;?>_files&fieldextra=<?php echo $fieldtype?>",
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
						fw_info_message_add('error', '<?php echo $formText_FieldOnlyAllowsToUpload_fieldtype." (".$image_count_limit.") ".$formText_files_fieldtype.". ".$formText_FollowingFileWasUploadedButNotAddedToContent_fieldtype;?>: ' + file.name);
					} else {
						var <?php echo $field_ui_id;?>counter = parseInt($('#<?php echo $field_ui_id;?>counter').val())+1;
						var oDiv = $('<div/>').attr('class', 'item row').appendTo('#<?php echo $field_ui_id;?>_files');
						var oThumbCol = $('<div/>').attr('class', 'col-md-2');
						var oThumbDiv = $('<div/>').attr('class', 'thumbnail');
						var oThumbImg = $('<img/>').attr('src', '<?php echo $extradomaindirroot;?>' + file.thumbUrl + '?caID=<?php echo $_GET['caID'];?>&uid=' + file.upload_id);
						var oTextCol = $('<div/>').attr('class', 'col-md-8').append('<strong><?php echo $formText_Filename_fieldtype;?></strong>: ' + file.name);
						var oDeleteCol = $('<div/>').attr('class', 'col-md-2');
						var oDeleteBtn = $('<button/>').attr('class', 'btn btn-xs btn-danger').attr('data-type', file.deleteType).attr('data-url', file.deleteUrl + "&param_name=<?php echo $field_ui_id;?>_files").append('<i class="glyphicon glyphicon-trash"></i><span><?php echo $formText_delete_fieldtype;?></span>');
						<?php if($show_focuspoint) { ?>var oFocusBtn = $('<button/>').attr('class', 'btn btn-xs btn-info set_focuspoint').append('<i class="glyphicon glyphicon-screenshot"></i><span><?php echo $formText_Focuspoint_fieldtype;?></span>');<?php } ?>
						
						oTextCol.append('<input type="hidden" name="<?php echo $field[1].$ending;?>_name[]" value="process:' + file.upload_id + ':' + <?php echo $field_ui_id;?>counter + ':' + file.name + '"/>');
						<?php
						foreach($resize_codes as $resize_code)
						{
							$tmp = explode(",", $resize_code);
							?>oTextCol.append('<input type="hidden" class="<?php echo ($tmp[2]=="c"?"handle":"");?>" name="<?php echo $field[1].$ending;?>_img' + <?php echo $field_ui_id;?>counter+ '[]" value="process:' + file.upload_id + ':<?php echo $resize_code;?>:' + file.width + ':' + file.height + ':' + file.url + '"/>');<?php
							
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
								oTextCol.append('<input class="focus handlefocus" type="hidden" name="<?php echo $field[1].$ending."_focus";?>' + <?php echo $field_ui_id;?>counter+ '[]" value="0:0" data-src="<?php echo $extradomaindirroot;?>' + file.url + '?caID=<?php echo $_GET['caID'];?>&uid=' + file.upload_id + '" data-w="' + w + '" data-h="' + h + '" data-x="0" data-y="0" data-ratio="' + ratio + '">');<?php
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
			handle_<?php echo $field_ui_id;?>();
		}
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');
	
	$('#<?php echo $field_ui_id;?>modal').on('hidden.bs.modal', function () {
		var data = $<?php echo $field_ui_id;?>image.cropper('getData');
		var str = $(<?php echo $field_ui_id;?>handle).val() + obj_to_string_<?php echo $field_ui_id;?>(data);
		$(<?php echo $field_ui_id;?>handle).val(str);
		$<?php echo $field_ui_id;?>image.cropper('destroy');
		$(window).resize();
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
		var handle = $('#<?php echo $field_ui_id;?>_files .handle'),
			handlefocus = $('#<?php echo $field_ui_id;?>_files .handlefocus');
		if(handle.length > 0)
		{
			<?php echo $field_ui_id;?>handle = $(handle).get(0);
			var option = $(<?php echo $field_ui_id;?>handle).val().split(':');
			var size = option[2].split(',');
			if(option[0] == 'process' && size[2] == 'c')
			{
				$('#<?php echo $field_ui_id;?>modal').modal({show:true});
				$<?php echo $field_ui_id;?>image.attr('src','<?php echo $extradomaindirroot;?>' + option[5] + '?caID=<?php echo $_GET['caID'];?>&uid=' + option[1] + '&_=' + Math.random()).cropper({
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
				str += ':' + p + '|' + obj[p];
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