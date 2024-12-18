<?php
if($fwaFileuploadConfig['module_folder'] != "" && $fwaFileuploadConfig['content_table'] != "" && $fwaFileuploadConfig['content_field'] != "" && $fwaFileuploadConfig['upload_type'] != "") {
	$account_path = ACCOUNT_PATH;
	$module_dir = $account_path . '/modules/' . $fwaFileuploadConfig['module_folder'];
	// Include module fields file and language variables
	include($module_dir."/input/settings/fields/".$fwaFileuploadConfig['content_table']."fields.php");
	// Directory related functions from input
	if(!function_exists("dirsizeexec")) include($module_dir."/input/fieldtypes/Image/fn_dirsizeexec.php");
	if(!function_exists("mkdir_recursive")) include($module_dir."/input/fieldtypes/Image/fn_mkdir_recursive.php");
	$fieldCounter = 0;
	foreach($prefields as $child)
	{
		$addToPre = explode("¤",$child);
		$tempre = $addToPre[6];
		$addToPre[6] = array();
		$addToPre[6]['all'] = $tempre;
		$addToPre['index'] = $fieldCounter;
		$fields[] = $addToPre;
		$fieldCounter++;
	}

	foreach($fields as $field) {
		if($field[0] == $fwaFileuploadConfig['content_field']){
			break;
		}
	}

	$v_no_handle_types = array(
		'image/gif',
		'image/svg+xml',
		'image/x-icon',
	);
	$focus_w_limit = 560;
	$focus_h_limit = 460;
	if($field[11] == '') $field[11] = 'T1:0,0';
	list($type, $resize_codes) = explode(":",strtolower($field[11]),2);
	list($fieldtype, $limit) = explode(',',$type);
	$image_count_limit = ($limit>0?$limit:1);
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

	/**
	 * Base styling for fileupload block
	 */
	require __DIR__ . '/style.php';
	/**
	 * JavaScript logic for fileupload block (drag & drop events, upload handling)
	 */
	require __DIR__ . '/js.php';

	/**
	 * Start local widget session
	 * Should consist of id and some timestamp?
	 */
	$files = array();


	?>



	<div id="popupeditbox_fileupload<?php echo $fwaFileuploadConfig['id'];?>" class="popupeditbox">
		<span class="button b-close fw_popup_x_color"><span>X</span></span>
		<div id="popupeditboxcontent_fileupload<?php echo $fwaFileuploadConfig['id'];?>"></div>
	</div>
	<ul class="fwaFileupload_FilesList_Files fwaFileupload_FilesList_Files<?php echo $fwaFileuploadConfig['id'];?>">
		<?php
		$sql = "SELECT * FROM ".$fwaFileuploadConfig['content_table']." WHERE id = ".$fwaFileuploadConfig['content_id'];
		$o_query = $o_main->db->query($sql);
		$fileupload_content = $o_query ? $o_query->row_array() : array();

		$data = json_decode($fileupload_content[$fwaFileuploadConfig['content_field']], true);
		$i=0;
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
			$b_is_local_image = FALSE;
			foreach($image as $img)
			{
				if(!is_file(ACCOUNT_PATH."/".rawurldecode($img))) continue;
				$b_is_local_image = TRUE;
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

			if(!$b_is_local_image) $thumb_img = $popup_img = $image[0];
			$thumb_img_link = ($b_is_local_image?$extradomaindirroot:'').$thumb_img;
			$popup_img = ($b_is_local_image?$extradomaindirroot:'').$popup_img;
			?>
			<li class="item">

				<div class="name">
					<span class="glyphicon glyphicon-ok progress-complete-icon"></span>
					<?php if($fwaFileuploadConfig['upload_type'] == "image") { ?>
						<div class="thumbnail"><a rel="gal_<?php echo $fwaFileuploadConfig['id']."_".$i;?>" class="<?php echo $fwaFileuploadConfig['id'];?>_fancy script" href="<?php echo $popup_img.(strpos($popup_img,'uploads/protected/')!==false?'?username='.$_COOKIE['username'].'&sessionID='.$_COOKIE['sessionID'].'&companyID='.$_GET['companyID'].'&caID='.$_GET['caID'].'&table='.$fwaFileuploadConfig['content_table'].'&field='.$field[0].'&ID='.$fwaFileuploadConfig['content_id'].'&languageID='.$variables->languageID.'&server_id='.array_shift(explode('.', $_SERVER['HTTP_HOST'])):'');?>"><img class="ptr" src="<?php echo $thumb_img_link.(strpos($thumb_img_link,'uploads/protected/')!==false?'?username='.$_COOKIE['username'].'&sessionID='.$_COOKIE['sessionID'].'&companyID='.$_GET['companyID'].'&caID='.$_GET['caID'].'&table='.$fwaFileuploadConfig['content_table'].'&field='.$field[0].'&ID='.$fwaFileuploadConfig['content_id'].'&languageID='.$variables->languageID.'&server_id='.array_shift(explode('.', $_SERVER['HTTP_HOST'])):'');?>"></a></div>
					<?php } ?>
					<label class="fileLabel">
						<?php echo $name;?>
					</label>
				</div>

				<div class="progress progress-complete"><div class="progress-fill" style="width: 100%;"></div></div>
				<?php if($fwaFileuploadConfig['custom'] == "customCssClassImage") { ?>
					<div class="thumbnailOverlay"></div>
				<?php }?>
				<a href="" class="delete-upload trash delete_stored" data-type="POST" data-url="" data-name="<?php echo $name;?>">
					<?php if($fwaFileuploadConfig['custom'] == "customCssClassImage") { ?>
						<span class="glyphicon glyphicon-remove"></span>
					<?php } else { ?>
						<span class="glyphicon glyphicon-trash"></span>
					<?php } ?>
				</a>
				<div class="">
					<input class="name" type="hidden" name="<?php echo $fwaFileuploadConfig['id']."_name";?>[]" value="<?php echo "|".$upload_id."|".$i."|".$name;?>">
					<?php
					$focus_counter = 0;
					foreach($image as $x=>$item)
					{
						$tmp = explode(",", $resize_codes[$x]);
						?><input class="image" type="hidden" name="<?php echo $fwaFileuploadConfig['id']."_img".$i;?>[]" value="<?php echo $item;?>"><?php
						if(strpos($tmp[3],"f")!==false && (!$b_is_local_image || is_file(ACCOUNT_PATH."/".rawurldecode($item))))
						{
							list($w,$h,$t,$r) = getimagesize(($b_is_local_image?ACCOUNT_PATH."/":'').rawurldecode($item));
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
							?><input class="focus" type="hidden" name="<?php echo $fwaFileuploadConfig['id']."_focus".$i;?>[]" value="<?php echo $focus[$focus_counter];?>" data-src="<?php echo ($b_is_local_image?$extradomaindirroot:'').$item.(strpos($item,'uploads/protected/')!==false?'?username='.$_COOKIE['username'].'&sessionID='.$_COOKIE['sessionID'].'&companyID='.$_GET['companyID'].'&caID='.$_GET['caID'].'&table='.$fwaFileuploadConfig['content_table'].'&field='.$field[0].'&ID='.$fwaFileuploadConfig['content_id'].'&languageID='.$variables->languageID.'&server_id='.array_shift(explode('.', $_SERVER['HTTP_HOST'])):'');?>" data-w="<?php echo $w;?>" data-h="<?php echo $h;?>" data-x="<?php echo $x-12;?>" data-y="<?php echo $y-12;?>" data-ratio="<?php echo $ratio;?>"><?php
							$focus_counter++;
						}
					}
					?>
				</div>
			</li>
		<?php
			$i++;
		} ?>
	</ul>

	<div class="fwaFileuploadAddFiles fwaFileuploadInit_<?php echo $fwaFileuploadConfig['id'];?>">
		<span class="fw_icon_color"><span class="fas fa-plus"></span> <?php echo $formText_Add_output;?></span>
	</div>
	<div class="fwaFileupload fwaFileupload_<?php echo $fwaFileuploadConfig['id'];?> <?php if($fwaFileuploadConfig['custom'] != "") echo $fwaFileuploadConfig['custom'];?>" style="display: none;">
		<div class="fwaFileupload_Files">
			<div class="fwaFileupload_FilesBrowseDrop">
				<div class="fwaFileupload_FilesBrowseDrop_Title">
					<?php echo $formText_DragAndDropFilesHere; ?>
				</div>
				<div class="fwaFileupload_FilesBrowseDrop_Icon">
					<span class="glyphicon glyphicon-arrow-down"></span>
				</div>
				<div class="fwaFileupload_FilesBrowseDrop_Browse">
					<div class="fwaFileupload_FilesBrowseDrop_Browse_Or"><?php echo $formText_or; ?></div>
					<a href=""><?php echo $formText_BrowseFilesOnYourComputer; ?></a>
				</div>
			</div>
			<div class="fwaFileupload_FilesList">
				<ul class="fwaFileupload_FilesList_Files">

				</ul>
			</div>
			<?php if($fwaFileuploadConfig['custom'] == "") { ?>
				<input id="<?php echo $fwaFileuploadConfig['id']; ?>_upload" type="file" name="<?php echo $fwaFileuploadConfig['id']; ?>_files[]" multiple>
			<?php } ?>

			<input id="<?php echo $fwaFileuploadConfig['id'];?>counter" type="hidden" value="<?php echo count($data) - 1;?>">
			<div class="modal fade" id="<?php echo $fwaFileuploadConfig['id'];?>modal" tabindex="-1" role="dialog" aria-labelledby="<?php echo $fwaFileuploadConfig['id'];?>modal" aria-hidden="true">
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
							<div id="<?php echo $fwaFileuploadConfig['id'];?>crop"><img /></div>
							<?php /*?><div id="<?php echo $fwaFileuploadConfig['id'];?>preview"><img /></div><?php */?>
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
			<div class="modal fade" id="<?php echo $fwaFileuploadConfig['id'];?>modalfocus" tabindex="-1" role="dialog" aria-labelledby="<?php echo $fwaFileuploadConfig['id'];?>modalfocus" aria-hidden="true">
			    <div class="modal-dialog">
			        <div class="modal-content">
			            <div class="modal-header">
			            <h4 class="modal-title"><?php echo $formText_MoveFocusPointToDesiredLocation_imageCropping;?></h4>
			            </div>
			            <div class="modal-body">
							<div id="<?php echo $fwaFileuploadConfig['id'];?>focus"><div id="<?php echo $fwaFileuploadConfig['id'];?>focusmark"></div></div>
						</div>
			            <div class="modal-footer">
			                <button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo $formText_save_input;?></button>
						</div>
			        </div>
			    </div>
			</div>
			<style>
			#<?php echo $fwaFileuploadConfig['id'];?>crop {
				width:100%;
				height:350px;
				overflow:hidden;
				background-color: #fcfcfc;
			    box-shadow: 0 0 5px rgba(0, 0, 0, 0.25) inset;
			}
			#<?php echo $fwaFileuploadConfig['id'];?>crop img {
				display:block;
				max-width:100%;
				height:auto;
			}
			#<?php echo $fwaFileuploadConfig['id'];?>modal .modal-header, #<?php echo $fwaFileuploadConfig['id'];?>modalfocus .modal-header {
				background:none;
				padding:15px;
				border-bottom:1px solid #999999;
			}
			#<?php echo $fwaFileuploadConfig['id'];?>modal .modal-header .device-list i {
				color:#bebebe;
				margin:0 3px;
			}
			#<?php echo $fwaFileuploadConfig['id'];?>modal .modal-header .device-list i.active {
				color:#000000;
			}
			#<?php echo $fwaFileuploadConfig['id'];?>focus{position:relative; background-size:contain;background-repeat:no-repeat;min-width:100px;min-height:100px;}
			#<?php echo $fwaFileuploadConfig['id'];?>focusmark{width:24px;height:24px;padding:0;border:0;background-image:url("<?php echo $extradir."/input/fieldtypes/".$field[4];?>/images/target.png");}
			.fwaFileuploadAddFiles {
				cursor: pointer;
				margin-top: 6px;
			}
			</style>
		</div>
	</div>
<?php } else {
	echo $formText_MissingConfigurationPleaseContactSystemDeveloper_output;
	?>
<?php } ?>
