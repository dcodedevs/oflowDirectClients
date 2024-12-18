<?php
$b_activate_cdn = (isset($variables->fw_session['content_server_api_url']) && trim($variables->fw_session['content_server_api_url']) != '');
$v_no_handle_types = array(
	'image/gif',
	'image/svg+xml',
	'image/x-icon',
);
$focus_w_limit = 560;
$focus_h_limit = 460;
if($field[11] == '') $field[11] = 'T1:0,0';
list($type, $resize_codes) = explode(":",strtolower($field[11]),2);
$v_tmp = explode(',',$type);
$fieldtype = $v_tmp[0];
$image_count_limit = ((isset($v_tmp[1]) && 0 < $v_tmp[1])?$v_tmp[1]:1);
if(!isset($resize_codes) or $resize_codes == '') $resize_codes = '0,0';
$show_focuspoint = (strpos($resize_codes,"f")!==false ? true : false);
$resize_codes = explode(":",$resize_codes);
$v_device_types = array(
	'list' => '<i class="far fa-list-alt list" title="'.$formText_ThumbnailInList_fieldtype.'"></i>',
	'mobile' => '<i class="fas fa-mobile-alt mobile" title="'.$formText_Mobile_fieldtype.'"></i>',
	'tablet' => '<i class="fas fa-tablet-alt tablet" title="'.$formText_Tablet_fieldtype.'"></i>',
	'laptop' => '<i class="fas fa-laptop laptop" title="'.$formText_Laptop_fieldtype.'"></i>',
	'desktop' => '<i class="fas fa-desktop desktop" title="'.$formText_Destkop_fieldtype.'"></i>',
);
$v_devices = array();
foreach($resize_codes as $resize_code)
{
	$v_tmp = explode(',', $resize_code);
	if(!empty($v_tmp[4])) $v_devices[] = $v_tmp[4];
}

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
		$alt_text = $obj[6];
		$max_w = 0;
		$min_w = 100000;
		foreach($image as $img)
		{
			if(!is_file(ACCOUNT_PATH."/".rawurldecode($img))) continue;
			$s_mime_content_type = mime_content_type(ACCOUNT_PATH."/".rawurldecode($img));
			if(in_array($s_mime_content_type, $v_no_handle_types))
			{
				$popup_img = $thumb_img = $img;
				break;
			}
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
		
		if($b_activate_cdn) $thumb_img = $popup_img = $image[0];
		$thumb_img_link = ($b_activate_cdn?'':$extradomaindirroot).$thumb_img;
		$popup_img = ($b_activate_cdn?'':$extradomaindirroot).$popup_img;
		?>
		<div class="item row">
			<div class="col-md-1 col-xs-2">
				<div class="thumbnail"><a rel="gal_<?php echo $field_ui_id."_".$i;?>" class="<?php echo $field_ui_id;?>_fancy script" href="<?php echo $popup_img.(strpos($popup_img,'uploads/protected/')!==false?'?username='.$_COOKIE['username'].'&sessionID='.$_COOKIE['sessionID'].'&companyID='.$_GET['companyID'].'&caID='.$_GET['caID'].'&table='.$fields[0][3].'&field='.$field[0].'&ID='.$ID.'&languageID='.$variables->languageID.'&server_id='.array_shift(explode('.', $_SERVER['HTTP_HOST'])):'');?>"><img class="ptr" src="<?php echo $thumb_img_link.(strpos($thumb_img_link,'uploads/protected/')!==false?'?username='.$_COOKIE['username'].'&sessionID='.$_COOKIE['sessionID'].'&companyID='.$_GET['companyID'].'&caID='.$_GET['caID'].'&table='.$fields[0][3].'&field='.$field[0].'&ID='.$ID.'&languageID='.$variables->languageID.'&server_id='.array_shift(explode('.', $_SERVER['HTTP_HOST'])):'');?>"></a></div>
			</div>
			<div class="col-md-9 col-xs-8">
				<span class="filename"><input class="delete_select" type="checkbox"> <?php echo $name;?></span>
				<input class="name" type="hidden" name="<?php echo $field[1].$ending."_name";?>[]" value="<?php echo "|".$upload_id."|".$i."|".$name;?>"><?php
				$focus_counter = 0;
				foreach($image as $x=>$item)
				{
					$tmp = explode(",", $resize_codes[$x]);
					?><input class="image" type="hidden" name="<?php echo $field[1].$ending."_img".$i;?>[]" value="<?php echo $item;?>"><?php
					if(strpos($tmp[3],"f")!==false && ($b_activate_cdn || is_file(ACCOUNT_PATH."/".rawurldecode($item))))
					{
						list($w,$h,$t,$r) = getimagesize(($b_activate_cdn?'':ACCOUNT_PATH."/").rawurldecode($item));
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
						?><input class="focus" type="hidden" name="<?php echo $field[1].$ending."_focus".$i;?>[]" value="<?php echo $focus[$focus_counter];?>" data-src="<?php echo (!$b_activate_cdn?$extradomaindirroot:'').$item.(strpos($item,'uploads/protected/')!==false?'?username='.$_COOKIE['username'].'&sessionID='.$_COOKIE['sessionID'].'&companyID='.$_GET['companyID'].'&caID='.$_GET['caID'].'&table='.$fields[0][3].'&field='.$field[0].'&ID='.$ID.'&languageID='.$variables->languageID.'&server_id='.array_shift(explode('.', $_SERVER['HTTP_HOST'])):'');?>" data-w="<?php echo $w;?>" data-h="<?php echo $h;?>" data-x="<?php echo $x-12;?>" data-y="<?php echo $y-12;?>" data-ratio="<?php echo $ratio;?>"><?php
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
				foreach($output_languages as $lid => $value)
				{
					$b_decorative = ('' == $alt_text[$lid]);
					if($b_decorative) $alt_text[$lid] = '';
					?><div class="row"><div class="col-md-4"><b><?php echo $formText_AlternativeText_fieldtype;?></b> <i><?php echo $value;?></i>:</div><div class="col-md-8"><input type="text" name="<?php echo $field[1].$ending."_alt_text".$lid.$i;?>" value="<?php echo htmlspecialchars($alt_text[$lid]);?>"<?php echo ($field[10]==1||$access<10?" readonly":"");?> class="<?php echo ($b_decorative ? 'decorative' : 'mandatory');?>" data-mandatory-text="<?php echo $formText_AlternativeTextIsMissingOrSetAsDecorative_fieldtype;?>"<?php echo ($b_decorative ? ' placeholder="['.$formText_Decorative_Output.']"' : '');?>><span class="set_decorative_alt"><?php echo $formText_SetDecorative_fieldtype;?></span></div></div><?php
				}
				?>
			</div>
			<div class="col-md-2 col-xs-2 text-right">
				<?php if($field[10]!=1 && $access >= 10) {?>
				<?php if($show_focuspoint) { ?><button class="btn btn-sm btn-focus set_focuspoint"><i class="glyphicon glyphicon-record"></i><div><?php echo $formText_Focus_fieldtype;?></div></button><?php } ?><button class="btn btn-sm btn-delete delete_stored" data-type="POST" data-url="" data-name="<?php echo $name;?>"><i class="glyphicon glyphicon-trash"></i><div><?php echo $formText_delete_fieldtype;?></div></button>
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
			<span class="glyphicon glyphicon-picture"></span>
			<span><?php echo $formText_Picture_fieldtype;?></span>
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
<div class="modal fade" id="<?php echo $field_ui_id;?>modal" tabindex="-1" role="dialog" aria-labelledby="<?php echo $field_ui_id;?>modal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
            	<div class="row">
					<div class="col-xs-7">
						<div class="modal-title"><b><?php echo $formText_ChooseSection_imageCropping;?></b> <?php echo $formText_PullTheDischargeAreaIntoTheCorrectPosition_fieldtype;?></div>
					</div>
					<div class="col-xs-5">
						<div class="pull-right">
							<span class="device-name"></span>
							<span class="device-list"><?php foreach($v_devices as $s_device) echo $v_device_types[$s_device]; ?></span>
						</div>
					</div>
				</div>
            </div>
            <div class="modal-body">
				<div id="<?php echo $field_ui_id;?>crop"><img /></div>
				<?php /*?><div id="<?php echo $field_ui_id;?>preview"><img /></div><?php */?>
			</div>
            <div class="modal-footer">
				<div class="row">
					<div class="col-xs-4 text-left">
						<button type="button" class="btn action" data-action="cancel"><?php echo $formText_Cancel_Fieldtype;?></button>
						<span class="file-name"></span>
					</div>
					<div class="col-xs-8">
						<button type="button" class="btn action" data-action="move_left"><span class="fa fa-arrow-left"></span></button>
						<button type="button" class="btn action" data-action="move_right"><span class="fa fa-arrow-right"></span></button>
						<button type="button" class="btn action" data-action="move_up"><span class="fa fa-arrow-up"></span></button>
						<button type="button" class="btn action" data-action="move_down"><span class="fa fa-arrow-down"></span></button>
						<button type="button" class="btn action" data-action="scale_x"><span class="fas fa-arrows-alt-h"></span></button>
						
						<button type="button" class="btn action" data-action="reset"><?php echo $formText_Reset_fieldtype;?></button>
						<button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo $formText_save_input;?></button>
					</div>
				</div>
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
	position:relative;
}
#<?php echo $field_ui_id;?>_files .thumbnail img {
	max-height: 100%;  
    max-width: 100%; 
    width: auto;
    height: auto;
    position: absolute;  
    top: 0;  
    bottom: 0;  
    left: 0;  
    right: 0;  
    margin: auto;
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
#<?php echo $field_ui_id;?>_files .item .set_decorative_alt {
	cursor:pointer;
	color:#0095E4;
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
#<?php echo $field_ui_id;?>crop {
	width:100%;
	height:350px;
	overflow:hidden;
	background-color: #fcfcfc;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.25) inset;
}
#<?php echo $field_ui_id;?>crop img {
	display:block;
	max-width:100%;
	height:auto;
}
#<?php echo $field_ui_id;?>modal .modal-header, #<?php echo $field_ui_id;?>modalfocus .modal-header {
	background:none;
	padding:15px;
	border-bottom:1px solid #999999;
}
#<?php echo $field_ui_id;?>modal .modal-header .device-list i {
	color:#bebebe;
	margin:0 3px;
}
#<?php echo $field_ui_id;?>modal .modal-header .device-list i.active {
	color:#000000;
}
#<?php echo $field_ui_id;?>focus{position:relative; background-size:contain;background-repeat:no-repeat;min-width:100px;min-height:100px;}
#<?php echo $field_ui_id;?>focusmark{width:24px;height:24px;padding:0;border:0;background-image:url("<?php echo $extradir."/input/fieldtypes/".$field[4];?>/images/target.png");}
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
	var $<?php echo $field_ui_id;?>image = $('#<?php echo $field_ui_id;?>crop > img'),
	<?php echo $field_ui_id;?>handle,
	<?php echo $field_ui_id;?>counter,
	<?php echo $field_ui_id;?>alert_shown = false,
	<?php echo $field_ui_id;?>limit = parseInt('<?php echo $image_count_limit;?>'),
	<?php echo $field_ui_id;?>limitw = parseInt('<?php echo $focus_w_limit;?>'),
	<?php echo $field_ui_id;?>limith = parseInt('<?php echo $focus_h_limit;?>'),
	<?php echo $field_ui_id;?>handler = false;
	
	$('#<?php echo $field_ui_id;?>_files .items').sortable(/*{containment: "parent"}*/);
	
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
		
		if(count > 1)
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
		$('#<?php echo $field_ui_id;?>_files .item .set_decorative_alt').off('click').on('click',function(){
			var obj = $(this).prev('input');
			$(obj).addClass('decorative').removeClass('mandatory');
			$(obj).attr('placeholder', "[<?php echo $formText_Decorative_Output;?>]").val('');
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
									$item.addClass('deleted').find('input.image').each(function(){
										$(this).val('delete|' + $(this).val());
									});
									$item.hide().find('.mandatory').removeClass('mandatory');
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
	$('#<?php echo $field_ui_id;?>modal .btn.action').on('click', function(e){
		e.preventDefault();
		if($(this).data('action').length)
		if($(this).data('action') == 'move_left') {
			$<?php echo $field_ui_id;?>image.cropper('move', -5, 0);
		} else if($(this).data('action') == 'move_right') {
			$<?php echo $field_ui_id;?>image.cropper('move', 5, 0);
		} else if($(this).data('action') == 'move_up') {
			$<?php echo $field_ui_id;?>image.cropper('move', 0, -5);
		} else if($(this).data('action') == 'move_down') {
			$<?php echo $field_ui_id;?>image.cropper('move', 0, 5);
		} else if($(this).data('action') == 'scale_x') {
			var data = $<?php echo $field_ui_id;?>image.cropper('getData');
			$<?php echo $field_ui_id;?>image.cropper('scaleX', (data.scaleX * (-1)));
		} else if($(this).data('action') == 'reset') {
			$<?php echo $field_ui_id;?>image.cropper('reset');
		} else if($(this).data('action') == 'cancel') {
			$(<?php echo $field_ui_id;?>handle).closest('.item').find('.btn-delete').trigger('click');
			$('#<?php echo $field_ui_id;?>modal').modal('hide');
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
							$_this.closest('.item').addClass('deleted').find('input.image').each(function(){
								$(this).val('delete|' + $(this).val());
							});
							$_this.closest('.item').hide().find('.mandatory').removeClass('mandatory');
							<?php echo $field_ui_id;?>update_btns();
						}
						fw_click_instance = false;
					}
				});
			}
		}
	});
	$('#<?php echo $field_ui_id;?>_files .item .set_focuspoint').off('click').on('click',function(e){
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
        dropZone: $('#<?php echo $field_ui_id;?>_files .dropzone'),
		<?php if($b_activate_cdn) { ?>
		url: "<?php echo $variables->fw_session['content_server_api_url'];?>?param_name=<?php echo $field_ui_id;?>_files&fieldextra=<?php echo $fieldtype?>",
		<?php } else { ?>
		url: "<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_upload.php?param_name=<?php echo $field_ui_id;?>_files&fieldextra=<?php echo $fieldtype?>",
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
							'<span class="glyphicon glyphicon-picture"></span>' +
						'</div>' +
						'<div class="col-md-9 col-xs-8">' +
							'<div class="name">' + file.name.trim() + '</div>' +
							'<input class="name" type="hidden" name="<?php echo $field[1].$ending."_name";?>[]" value="handle_upload|' + tmp_upload_id + '|' + '|' + file.name.trim() + '">' +
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
						var oThumbImg = $('<img/>').attr('src', '<?php echo ($b_activate_cdn?'':$extradomaindirroot);?>' +(file.no_handle ? file.url : file.thumbUrl) + '?username=<?php echo $_COOKIE['username'];?>&sessionID=<?php echo $_COOKIE['sessionID'];?>&companyID=<?php echo $_GET['companyID'];?>&caID=<?php echo $_GET['caID'];?>&uid=' + file.upload_id + '&server_id=<?php echo array_shift(explode('.', $_SERVER['HTTP_HOST']));?>');
						var oTextCol = $('<div/>').attr('class', 'col-md-9 col-xs-8').append('<span class="filename"><input class="delete_select" type="checkbox"> ' + file.name + '</span>');
						var oDeleteCol = $('<div/>').attr('class', 'col-md-2 col-xs-2 text-right');
						var oDeleteBtn = $('<button/>').attr('class', 'btn btn-sm btn-delete').attr('data-type', file.deleteType).attr('data-url', file.deleteUrl + "&param_name=<?php echo $field_ui_id;?>_files").append('<i class="glyphicon glyphicon-trash"></i><div><?php echo $formText_delete_fieldtype;?></div>');
						<?php if($show_focuspoint) { ?>var oFocusBtn = $('<button/>').attr('class', 'btn btn-sm btn-focus set_focuspoint').append('<i class="glyphicon glyphicon-record"></i><div><?php echo $formText_Focus_fieldtype;?></div>');<?php } ?>
						
						$('#<?php echo $field_ui_id;?>_files .items [data-tmp-upload-id="' + btoa(encodeURIComponent(file.name)) + '"]').replaceWith(oDiv);
						oTextCol.append('<input type="hidden" name="<?php echo $field[1].$ending;?>_name[]" value="process|' + file.upload_id + '|' + <?php echo $field_ui_id;?>counter + '|' + file.name + '"/>');
						<?php
						foreach($resize_codes as $resize_code)
						{
							$tmp = explode(",", $resize_code);
							?>oTextCol.append('<input type="hidden" class="<?php echo ($tmp[2]=="c"?"handle":"");?>" name="<?php echo $field[1].$ending;?>_img' + <?php echo $field_ui_id;?>counter+ '[]" value="process|' + file.upload_id + '|<?php echo $resize_code;?>|' + file.width + '|' + file.height + '|' + file.url + '" data-device="<?php echo $tmp[4];?>"/>');<?php
							
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
								oTextCol.append('<input class="focus handlefocus" type="hidden" name="<?php echo $field[1].$ending."_focus";?>' + <?php echo $field_ui_id;?>counter+ '[]" value="0:0" data-src="<?php echo ($b_activate_cdn?'':$extradomaindirroot);?>' + file.url + '?username=<?php echo $_COOKIE['username'];?>&sessionID=<?php echo $_COOKIE['sessionID'];?>&companyID=<?php echo $_GET['companyID'];?>&caID=<?php echo $_GET['caID'];?>&uid=' + file.upload_id + '&server_id=<?php echo array_shift(explode('.', $_SERVER['HTTP_HOST']));?>" data-w="' + w + '" data-h="' + h + '" data-x="0" data-y="0" data-ratio="' + ratio + '">');<?php
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
						foreach($output_languages as $lid => $value)
						{
							?>oTextCol.append('<div class="row"><div class="col-md-4"><?php echo "<b>".$formText_AlternativeText_fieldtype."</b>"." <i>".$value."</i>";?>:</div><div class="col-md-8"><input type="text" name="<?php echo $field[1].$ending;?>_alt_text<?php echo $lid;?>' + <?php echo $field_ui_id;?>counter + '" value="" class="mandatory" data-mandatory-parent=".item" data-mandatory-field="span.filename" data-mandatory-text="<?php echo $formText_AlternativeTextIsMissingOrSetAsDecorative_fieldtype;?>"/><span class="set_decorative_alt"><?php echo $formText_SetDecorative_fieldtype;?></span></div></div>');<?php
							if($singleLanguage) break; // stop for single language
						}
						?>
						if(file.no_handle)
						{
							oTextCol.find('input').removeClass('handle');
						}
						oThumbCol.appendTo(oDiv);
						oThumbDiv.appendTo(oThumbCol);
						oThumbImg.appendTo(oThumbDiv);
						oTextCol.appendTo(oDiv);
						oDeleteBtn.on("click", function() {
							$.post($(this).data('url'), function(data){
								if(data[file.name] == true) oDiv.remove();
								<?php echo $field_ui_id;?>update_btns();
							},"json");
						});
						oDeleteBtn.appendTo(oDeleteCol);
						<?php if($show_focuspoint) { ?>
						oFocusBtn.on("click", function() {
							$(this).closest('.item').find('input.focus').addClass('handlefocus');
							handle_<?php echo $field_ui_id;?>();
						});
						oFocusBtn.prependTo(oDeleteCol);
						<?php } ?>
						oDeleteCol.appendTo(oDiv);
						
						$('#<?php echo $field_ui_id;?>counter').val(<?php echo $field_ui_id;?>counter);
						
						handle_<?php echo $field_ui_id;?>();
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
		fail: function (e, data) {
			fw_info_message_add('error', data.files[0].name + ': ' + data.errorThrown);
			$('#<?php echo $field_ui_id;?>_files .items [data-tmp-upload-id="' + btoa(encodeURIComponent(data.files[0].name)) + '"]').off('click').remove();
		},
		stop: function(e) {
			$('#<?php echo $field_ui_id;?>_files .item.upload-abort').off('click').remove();
			<?php echo $field_ui_id;?>alert_shown = false;
			fw_editing_instance = false;
			fw_info_message_show();
		}
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');
	$('#<?php echo $field_ui_id;?>modal').on('hidden.bs.modal', function () {
		var data = $<?php echo $field_ui_id;?>image.cropper('getData');
		var str = $(<?php echo $field_ui_id;?>handle).val() + obj_to_string_<?php echo $field_ui_id;?>(data);
		$(<?php echo $field_ui_id;?>handle).val(str);
		$<?php echo $field_ui_id;?>image.cropper('destroy');
		$(window).trigger('resize');
		<?php echo $field_ui_id;?>handler = false;
		handle_<?php echo $field_ui_id;?>();
	});
	$('#<?php echo $field_ui_id;?>modalfocus').on('hidden.bs.modal', function () {
		<?php echo $field_ui_id;?>handler = false;
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
		if(!<?php echo $field_ui_id;?>handler)
		{
			<?php echo $field_ui_id;?>handler = true;
			var _cur = '';
			if($('#<?php echo $field_ui_id;?>_files .cur .handle').length > 0 || $('#<?php echo $field_ui_id;?>_files .cur .handlefocus').length > 0)
			{
				_cur = ' .cur';
			}
			var handle = $('#<?php echo $field_ui_id;?>_files' + _cur + ' .handle'),
				handlefocus = $('#<?php echo $field_ui_id;?>_files' + _cur + ' .handlefocus');
			
			if(handle.length > 0)
			{
				fw_loading_start();
				<?php echo $field_ui_id;?>handle = $(handle).get(0);
				var option = $(<?php echo $field_ui_id;?>handle).val().split('|');
				var size = option[2].split(',');
				var $devices = $('#<?php echo $field_ui_id;?>modal .modal-header .device-list');
				if(option[0] == 'process' && size[2] == 'c')
				{
					$devices.find('i').removeClass('active');
					$('#<?php echo $field_ui_id;?>modal').modal({show:true});
					var device_txt = '<?php echo $formText_GeneralPurpose_fieldtype;?>';
					if(size[4] != '')
					{
						$devices.find('i.' + size[4]).addClass('active');
						device_txt = $devices.find('i.' + size[4]).attr('title');
					}
					$('#<?php echo $field_ui_id;?>modal .modal-header .device-name').text(device_txt);
					$('#<?php echo $field_ui_id;?>modal .modal-footer .file-name').text($(<?php echo $field_ui_id;?>handle).parent().find('.filename').text());
					$<?php echo $field_ui_id;?>image.attr('src','').attr('src', '<?php echo ($b_activate_cdn?'':$extradomaindirroot);?>' + option[5] + '?username=<?php echo $_COOKIE['username'];?>&sessionID=<?php echo $_COOKIE['sessionID'];?>&companyID=<?php echo $_GET['companyID'];?>&caID=<?php echo $_GET['caID'];?>&uid=' + option[1] + '&server_id=<?php echo array_shift(explode('.', $_SERVER['HTTP_HOST']));?>&_=' + Math.random()).off('load').on('load', function(){
						$(this).cropper({
							strict:false,
							zoomable:false,
							rotatable:false,
							aspectRatio:size[0]/size[1],
							autoCropArea:1/*,
							preview:"#<?php echo $field_ui_id;?>preview"*/
						});
						fw_loading_end();
					});
				}
				
				if(_cur == '') $(<?php echo $field_ui_id;?>handle).parent().addClass('cur');
				$(<?php echo $field_ui_id;?>handle).removeClass("handle");
			}
			else if(handlefocus.length > 0)
			{
				fw_loading_start();
				<?php echo $field_ui_id;?>handle = $(handlefocus).get(0);
				$('#<?php echo $field_ui_id;?>focus').css({
					'background-image': 'none',//url('+$(<?php echo $field_ui_id;?>handle).data('src')+')',
					width: $(<?php echo $field_ui_id;?>handle).data('w'),
					height: $(<?php echo $field_ui_id;?>handle).data('h'),
				});
				$('#<?php echo $field_ui_id;?>focusmark').css({
					left: $(<?php echo $field_ui_id;?>handle).data('x'),
					top: $(<?php echo $field_ui_id;?>handle).data('y'),
				}).data('ratio',$(<?php echo $field_ui_id;?>handle).data('ratio'));
				$('#<?php echo $field_ui_id;?>modalfocus').modal({show:true});
				
				var bgimage = new Image();
					bgimage.src = $(<?php echo $field_ui_id;?>handle).data('src');       
				$(bgimage).off('load').on('load', function(){
					$('#<?php echo $field_ui_id;?>focus').css({'background-image': 'url('+$(<?php echo $field_ui_id;?>handle).data('src')+')'});
					fw_loading_end();
					$(bgimage).remove();
				});
				
				if(_cur == '') $(<?php echo $field_ui_id;?>handle).parent().addClass('cur');
				$(<?php echo $field_ui_id;?>handle).removeClass("handlefocus");
			} else {
				<?php echo $field_ui_id;?>handler = false;
			}
		} else {
			setTimeout(handle_<?php echo $field_ui_id;?>, 300);
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