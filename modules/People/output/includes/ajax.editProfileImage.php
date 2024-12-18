<?php
// require_once __DIR__ . '/process_uploaded_image_to_entry.php';

$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/accounts/".$_GET['accountname']."/";

if(isset($_GET['caID'])){ $caID = $_GET['caID']; } else { $caID = ''; }
if(isset($_POST['cid'])){ $cid = $_POST['cid']; } else { $cid = NULL; }

$s_sql = "SELECT contactperson.* FROM contactperson
WHERE contactperson.id = ?";
$o_query = $o_main->db->query($s_sql, array($cid));
$v_data = $o_query ? $o_query->row_array() : array();

$b_registered_user = FALSE;
$v_user_external = array();
if(!function_exists("APIconnectorUser")) include(__DIR__."/APIconnector.php");

// $v_response = json_decode(APIconnectorAccount("userinfoget", $v_accountinfo['accountname'], $v_accountinfo['password'], array('SEARCH_USERNAME'=>$v_data['email'])), TRUE);
$v_response = json_decode(APIconnectorUser("userdetailsget", $variables->loggID, $variables->sessionID, array('USERNAME'=>$v_data['email'])), TRUE);

if(!array_key_exists("error", $v_response))
{
	$b_registered_user = TRUE;
	$v_user_external = $v_response;
	//if($variables->loggID == $v_data['email'])
} else {
	$b_registered_user = FALSE;
}

if($b_registered_user)
{
	$v_user_external['fullname'] = preg_replace('/\s+/', ' ', $v_user_external['name'].' '.$v_user_external['middle_name'].' '.$v_user_external['last_name']);
	$v_profile_image = json_decode(urldecode($v_user_external['image']),TRUE);
	$v_user_external['profile_image'] = ($v_profile_image[0] != "" ? "https://pics.getynet.com/profileimages/".$v_profile_image[0] : "");
} else {
	$v_user_external['profile_image'] = '';
	$o_query = $o_main->db->query("SELECT * FROM accountinfo");
	$v_accountinfo = $o_query ? $o_query->row_array() : array();
	if($v_data['email'] != ""){
		$v_response = json_decode(APIconnectorAccount("user_image_upload_get", $v_accountinfo['accountname'], $v_accountinfo['password'], array('username'=>$v_data['email'])), TRUE);
		if(isset($v_response['status']) && $v_response['status'] == 1)
		{
			$v_profile_image = json_decode(urldecode($v_response['image']),TRUE);
			if($v_profile_image[0] != "") $v_user_external['profile_image'] = "https://pics.getynet.com/profileimages/".$v_profile_image[0];
		}
	}
}
if(isset($_POST['output_form_submit']))
{
	if(isset($_POST['processImageOnly'])){
		foreach($_POST['imagesToProcess'] as $key=>$uploaded_file_id) {
			$img_item = $_POST['imagesHandle'][$key];
			// Account path
			$account_path = ACCOUNT_PATH;

			// Get uploaded image data

			$s_sql = "SELECT * FROM uploads WHERE id = ? AND handle_status = 0";
			$o_query = $o_main->db->query($s_sql, array($uploaded_file_id));
			if($o_query && $o_query->num_rows() > 0){
				$uploaded_image_data = $o_query->row_array();
			}

			// Get content table and field where to assign this image
			$content_table = $uploaded_image_data['content_table'];
			$content_field = $uploaded_image_data['content_field'];
			$content_module_id = $uploaded_image_data['content_module_id'];


			// Get module name
			$s_sql = "SELECT * FROM moduledata WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($content_module_id));
			if($o_query && $o_query->num_rows() > 0){
				$module_data = $o_query->row_array();
			}
			$content_module_name = $module_data['name'];

			// Module dir path
			$module_dir = $account_path . '/modules/' . $content_module_name;
			// Include module fields file and language variables
			include($module_dir."/input/settings/fields/".$content_table."fields.php");

			// Directory related functions from input
			if(!function_exists("dirsizeexec")) include($module_dir."/input/fieldtypes/Image/fn_dirsizeexec.php");
			if(!function_exists("mkdir_recursive")) include($module_dir."/input/fieldtypes/Image/fn_mkdir_recursive.php");

			// Read fields in array
			foreach($prefields as $s_field) {
				$v_field = explode("¤",$s_field);
				$v_fields[$v_field[0]] = $v_field;
			}

			// Process field data
			list($type, $resize_codes) = explode(":",strtolower($v_fields[$content_field][11]),2);
			list($fieldtype, $limit) = explode(',',$type);
			$image_count_limit = ($limit>0?$limit:1);
			if(!isset($resize_codes) or $resize_codes == '') {
				$resize_codes = '0,0';
			}
			$resize_codes_array = explode(":", $resize_codes);
			$image_counter = 0;

			// Show text
			$show_text = strpos($fieldtype,"t2") !== false ? true : false;

			// Show link
			$show_link = strpos($fieldtype,"link") !== false ? true : false;

			// Protected
			$protected = strpos($fieldtype,"p") !== false ? true : false;

			// Remove original
			$b_remove_original = strpos($fieldtype,"o") !== false ? false : true;

			$img_obj = explode(":",$img_item); //process:upload_id:w,h,[a]c:orig_w:orig_h:img_src:[crop]x|0:y|0:w|0:h|0:rotate|0

			$uploads_dir = $protected ? 'uploads/protected' : 'uploads';
			// This will be used later for multiple images
			$images = array();

			// Current image array
			$image = array();

			// new uploads or delete old images
			$s_original_file = '';
			$focus_counter = 0;

			$image_name = explode(":",$item); //process:upload_id:counter:filename
			$image[0] = $uploaded_image_data['filename'];
			$x = array();

			foreach($resize_codes_array as $resize_codes){
				$resize_codes = explode(",",$resize_codes);

				// $img_size = explode(",",strtolower($img_obj[2]));
				$img_size = $resize_codes;

				// $img_path = $uploads_dir.$img_obj[1]."/".$variant."/".$image_name[3];
				$img_path = rawurldecode($uploaded_image_data['filepath']);
				// $img_obj[5] = rawurldecode($img_obj[5]);
				$s_original_file = rawurldecode($uploaded_image_data['filepath']);
				list($src_w, $src_h, $stype, $attr) = getimagesize($account_path."/".$s_original_file);
				// $s_original_file = $img_obj[5];

				// Resizing not needed, just moving image
				if(($img_size[0] == 0 && $img_size[1] == 0 ) ||
					(
					($src_w < $img_size[0] && $src_h < $img_size[1]) ||
					($src_w < $img_size[0] && $img_size[1] == 0) ||
					($img_size[0] == 0 && $src_h < $img_size[1])
					) && ($img_size[2] != "c" && $img_size[2] != "ac")
				) {
					copy($account_path."/".$s_original_file, $account_path."/".$img_path);

				}
				// Resize or crop needed
				else {

					if($img_size[0] == 0) $wratio = 0; else $wratio = $src_w / $img_size[0];
					if($img_size[1] == 0) $hratio = 0; else $hratio = $src_h / $img_size[1];
					$ratios = array($hratio, $wratio);
					if(($img_size[2] == "ac" || $img_size[2] == "c") && $hratio != 0 && $wratio != 0)
					{
						$denratio = min($ratios);
					} else if($img_size[2] == "m") {
						$denratio = min($ratios);
					} else {
						$denratio = max($ratios);
					}
					if($denratio == 0) $denratio = 1;

					$newwidth = ceil($src_w / $denratio);
					$newheight = ceil($src_h / $denratio);
					$src_x = $src_y = 0;
					// resize cropping in center
					if($img_size[2] == "ac")
					{
						$autowidth = $newwidth;
						$autoheight = $newheight;
						if($newwidth > $img_size[0])
						{
							$src_x = intval(($newwidth - $img_size[0])/2);
							//$src_w = $src_w - intval(($newwidth - $img_size[0]) * $denratio);
							$autowidth = $img_size[0];
						}
						if($newheight > $img_size[1])
						{
							$src_y = intval(($newheight - $img_size[1])/2);
							//$src_h = $src_h - intval(($newheight - $img_size[1]) * $denratio);
							$autoheight = $img_size[1];
						}
					}

					$imagick = new Imagick($account_path."/".$s_original_file);
					$imagick->setImagePage(0, 0, 0, 0);
					if($img_size[2] == "c")
					{
						$img_crop = array();
						$tmp = explode("|",$img_obj[6]);
						$img_crop[$tmp[0]] = $tmp[1];
						$tmp = explode("|",$img_obj[7]);
						$img_crop[$tmp[0]] = $tmp[1];
						$tmp = explode("|",$img_obj[8]);
						$img_crop[$tmp[0]] = $tmp[1];
						$tmp = explode("|",$img_obj[9]);
						$img_crop[$tmp[0]] = $tmp[1];
						$tmp = explode("|",$img_obj[10]);
						$img_crop[$tmp[0]] = $tmp[1];
						if($src_w < ($img_crop['width'] + $img_crop['x'])) $img_crop['x'] = $src_w - $img_crop['width'];
						if($src_h < ($img_crop['height'] + $img_crop['y'])) $img_crop['y'] = $src_h - $img_crop['height'];
						if($img_crop['x']<0) $img_crop['x'] = 0;
						if($img_crop['y']<0) $img_crop['y'] = 0;

						$imagick->cropImage($img_crop['width'], $img_crop['height'], $img_crop['x'], $img_crop['y']);
						$imagick->resizeImage($img_size[0], $img_size[1], imagick::FILTER_LANCZOS, 1);

						if(strpos($img_size[3],"f") !== false)
						{
							$tmp = explode(":",$_POST[$fieldName."_focus".$image_name[2]][$focus_counter]);
							$tmp[0] = floor(($tmp[0] - $img_crop['x'])/$denratio);
							$tmp[1] = floor(($tmp[1] - $img_crop['y'])/$denratio);
							if($tmp[0]>$img_size[0]) $tmp[0] = $img_size[0];
							if($tmp[1]>$img_size[1]) $tmp[1] = $img_size[1];
							if($tmp[0]<0) $tmp[0] = 0;
							if($tmp[1]<0) $tmp[1] = 0;
							$_POST[$fieldName."_focus".$image_name[2]][$focus_counter] = implode(":",$tmp);
							$focus_counter++;
						}
					} else {
						$imagick->resizeImage($newwidth, $newheight, imagick::FILTER_LANCZOS, 1);
						$tmp = explode(":",$_POST[$fieldName."_focus".$image_name[2]][$focus_counter]);
						$tmp[0] = floor($tmp[0]/$denratio);
						$tmp[1] = floor($tmp[1]/$denratio);
						if($img_size[2] == "ac")
						{
							$imagick->cropImage($autowidth, $autoheight, $src_x, $src_y);
							$tmp[0] = $tmp[0] - $src_x;
							$tmp[1] = $tmp[1] - $src_y;
						}
						if(strpos($img_size[3],"f") !== false)
						{
							if($tmp[0]>$img_size[0]) $tmp[0] = $img_size[0];
							if($tmp[1]>$img_size[1]) $tmp[1] = $img_size[1];
							if($tmp[0]<0) $tmp[0] = 0;
							if($tmp[1]<0) $tmp[1] = 0;
							$_POST[$fieldName."_focus".$image_name[2]][$focus_counter] = implode(":",$tmp);
							$focus_counter++;
						}
					}
					$imagick->writeImage($account_path."/".$img_path);
				}
				$image_counter++;
			}
		}
		//last uploaded image preview
		$fw_return_data = $actual_link.$img_path."?caID=".$caID."&uid=".$uploaded_file_id;
	} else {
		$v_return = array('status'=>0);
		$o_query = $o_main->db->query("SELECT * FROM accountinfo");
		$v_accountinfo = $o_query ? $o_query->row_array() : array();
		// process images
		if($_POST['imagesToProcess'])
		{
			foreach($_POST['imagesToProcess'] as $l_uploaded_file_id)
			{
				//process_uploaded_image_to_entry($o_main, $l_uploaded_file_id, $contentId);
				$s_sql = "SELECT * FROM uploads WHERE id = '".$o_main->db->escape_str($l_uploaded_file_id)."' AND handle_status = 0";
				$o_query = $o_main->db->query($s_sql);
				if($o_query && $o_query->num_rows() > 0)
				{
					$uploaded_image_data = $o_query->row_array();
				}
				$s_original_file = rawurldecode($uploaded_image_data['filepath']);
				$v_image_info = getimagesize(ACCOUNT_PATH.'/'.$s_original_file);

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_VERBOSE, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_URL, 'http://pics.getynet.com/serverapi/commands/profileimageadd.php');
				//most important curl assues @filed as file field
				$foldername =  preg_replace("[^[a-zA-Z]","",$uploaded_image_data['filename']);
				$post_array = array(
					"profileimage"=> new CurlFile(ACCOUNT_PATH.'/'.$s_original_file, $v_image_info['mime'], $uploaded_image_data['filename']),
					"upload"=>"Upload",
					"foldername"=>strtolower($foldername[0]),
					"oldprofileimage"=>$v_user_external['image'],
					"filetype"=>substr($uploaded_image_data['filename'],strrpos($uploaded_image_data['filename'],"."))
				);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post_array);
				$s_response = curl_exec($ch);
				$v_response = json_decode($s_response, TRUE);
				if($v_response === null || isset($v_response['error']))
				{
					$fw_error_msg = array($formText_ImageUploadFailed_Output);
				} else {
					if($b_registered_user)
					{
						$n_response = json_decode(APIconnectorAccount("userinfoset", $v_accountinfo['accountname'], $v_accountinfo['password'], array('IMAGE'=>$s_response, 'ID' => $v_user_external['id'])), true);
						// $n_response = json_decode(APIconnectorUser("userprofileset", $variables->loggID, $variables->sessionID, array('IMAGE'=>$s_response, 'USERNAME' => $v_user_external['username'])), true);
					} else {
						$n_response = json_decode(APIconnectorAccount("user_image_upload_set", $v_accountinfo['accountname'], $v_accountinfo['password'], array('username'=>$v_data['email'], 'image'=>$s_response)), true);
					}
					if(isset($n_response['error'])){
						$fw_error_msg = array($formText_ImageUploadFailed_Output);
					} else {
						$b_remove_original = TRUE;
						if($b_remove_original)
						{
							$l_i = 0;
							$s_delete_dir = dirname(ACCOUNT_PATH."/".$s_original_file);
							$s_uploads_dir = ACCOUNT_PATH.'/uploads';
							$s_check = ltrim(str_replace($s_uploads_dir, '', $s_delete_dir), '/');
							$v_check = explode('/', $s_check);
							if(strpos($s_delete_dir, $s_uploads_dir) === false || $s_check == '' || (count($v_check) == 1 && in_array($v_check[0], array('storage', 'protected', 'userfiles'))))
							{
								// bad request for deletion
								$o_main->db->query("UPDATE uploads SET handle_status = '10' WHERE id = '".$o_main->db->escape_str($l_uploaded_file_id)."'");
							} else {
								while(is_dir($s_delete_dir))
								{
									$l_i++;
									foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($s_delete_dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path)
									{
										$path->isDir() && !$path->isLink() ? rmdir($path->getPathname()) : unlink($path->getPathname());
									}
									rmdir($s_delete_dir);
									if($l_i > 5)
									{
										break;
									}
								}
							}
							if(!is_file(ACCOUNT_PATH."/".$s_original_file))
							{
								$o_main->db->query("DELETE FROM uploads WHERE id = '".$o_main->db->escape_str($l_uploaded_file_id)."'");
								//$o_main->db->query("UPDATE uploads SET handle_status = '1' WHERE id = '".$o_main->db->escape_str($l_uploaded_file_id)."'");
							}
							$s_original_file = "";
						}
					}
				}
			}
		}
	}
	if(count($fw_error_msg) == 0){
		$fw_return_result = 1;
		$o_main->db->query("UPDATE accountinfo SET force_cache_refresh = NOW()");
	}
	return;
} else {
?>
<div class="profileEditForm popupform">
  <div id="popup-validate-message"></div>
  <form class="output-form" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editProfileImage";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
  	<input type="hidden" name="fw_nocss" value="1">
  	<input type="hidden" name="output_form_submit" value="1">
    <input type="hidden" name="languageID" value="<?php echo $languageID?>">
    <input type="hidden" name="cid" value="<?php echo $cid?>">
    <div class="line">
      <div class="lineTitle"><?php echo $formText_ProfileImage_Output; ?></div>
      <div class="lineInput">
		  <div class="office-image">
              <div class="office-image-img">
                  <img style="width:200px;" src="<?php echo $v_user_external['profile_image']; ?>" data-defaultimage="<?php echo $v_user_external['profile_image']; ?>"/>
              </div>
          </div>
        <!-- <?php if($v_user_external['profile_image']!='') { ?>
        <div class="office-image">
            <div class="office-image-img">
                <img style="width:200px;" src="<?php echo $v_user_external['profile_image']; ?>" />
            </div>
            <div class="office-image-button"><a href="#" class="changeProfileImagesBtn"><?php echo $formText_ChangeImage_output; ?></a></div>
        </div>
        <?php } else { ?>
        <a href="#" class="changeProfileImagesBtn"><?php echo $formText_AddImage_output; ?></a>
        <?php } ?> -->
      </div>
      <div class="clear"></div>
      <br/>
    </div>
	<?php
	$fwaFileuploadConfig = array (
		'module_folder' => 'People', // module id in which this block is used
		'id' => 'peopleProfileImageUpload',
		'content_table' => 'contactperson',
		'content_field' => 'profileImage',
		'content_module_id' => 32, // id of module
		'dropZone' => 'block',
		'callback' => 'callbackOnImageUpload',
		'callbackDelete'=> 'callbackOnImageDelete',
		'callbackAll' => 'callBackOnUploadAll',
		'callbackStart' => 'callbackOnStart'
	);
	require __DIR__ . '/fileupload9/output.php';
	?>
	<div id="popup-image-message"></div>
      <div class="popupformbtn">
          <button type="button" class="output-btn b-large b-close fw_button_not_filled_color"><?php echo $formText_Close_Output;?></button>
		  <input type="submit" class="fw_button_color saveFiles" name="sbmbtn" value="<?php echo $formText_save_output; ?>">
      </div>

	<?php
	$field_ui_id = "upload_pagecover";
	?>
	<div id="<?php echo $field_ui_id;?>_files">
		<?php
		$account_path = ACCOUNT_PATH;
		$content_field = "profileImage";
		$module_dir = $account_path . '/modules/People';
		// Include module fields file and language variables
		include($module_dir."/input/settings/fields/userprofilefields.php");

		// Directory related functions from input
		if(!function_exists("dirsizeexec")) include($module_dir."/input/fieldtypes/Image/fn_dirsizeexec.php");
		if(!function_exists("mkdir_recursive")) include($module_dir."/input/fieldtypes/Image/fn_mkdir_recursive.php");

		// Read fields in array
		foreach($prefields as $s_field) {
			$v_field = explode("¤",$s_field);
			$v_fields[$v_field[0]] = $v_field;
		}

		list($type, $resize_codes) = explode(":",strtolower($v_fields[$content_field][11]),2);
		list($fieldtype, $limit) = explode(',',$type);
		$image_count_limit = ($limit>0?$limit:1);
		if(!isset($resize_codes) or $resize_codes == '') $resize_codes = '0,0';
		$show_focuspoint = (strpos($resize_codes,"f")!==false ? true : false);
		$resize_codes = explode(":",$resize_codes);

		foreach($resize_codes as $key => $resize_code)
		{ ?>
			<input type="hidden" class="handle handleInput handleInput<?php echo $key;?>" name="<?php echo $field_ui_id;?>_img[]" value=""/>
		<?php } ?>
	</div>
	<div class="modal fade" id="<?php echo $field_ui_id;?>modal" tabindex="-1" role="dialog" aria-labelledby="<?php echo $field_ui_id;?>modal" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
				<h4 class="modal-title"><?php echo $formText_DragTheCroppingAreaInRightPosition_imageCropping;?></h4>
				</div>
				<div class="modal-body">
					<div id="<?php echo $field_ui_id;?>crop"><img src="" /></div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" id="<?php echo $field_ui_id;?>_reset"><?php echo $formText_Reset_fieldtype;?></button>
					<button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo $formText_save_input;?></button>
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript">

	var $<?php echo $field_ui_id;?>image = $('#<?php echo $field_ui_id;?>crop > img');

	$("#<?php echo $field_ui_id;?>_reset").on('click', function(){
		$<?php echo $field_ui_id;?>image.cropper('reset');
	});
	$('#<?php echo $field_ui_id;?>modal').on('hidden.bs.modal', function () {
		var data = $<?php echo $field_ui_id;?>image.cropper('getData', true);
		var str = $(<?php echo $field_ui_id;?>handle).val() + obj_to_string_<?php echo $field_ui_id;?>(data);
		$(<?php echo $field_ui_id;?>handle).val(str);
		$<?php echo $field_ui_id;?>image.cropper('destroy');
		$(window).resize();
		imagesHandle.push( $('#<?php echo $field_ui_id;?>_files .handleInput').val());
		handle_<?php echo $field_ui_id;?>();


		var formdata = $(".output-content-form").serializeArray();
		var data = {};
		$(formdata ).each(function(index, obj){
			data[obj.name] = obj.value;
		});
		data.imagesToProcess = imagesToProcess;
		data.imagesHandle = imagesHandle;
		data.output_form_submit = 1;
		data.processImageOnly = 1;

		ajaxCall('editProfileImage', data, function(json) {
			$(".office-image-img img").attr("src", json.data);
		});

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

				$<?php echo $field_ui_id;?>image.attr('src', null);
				$<?php echo $field_ui_id;?>image.attr('src','<?php echo $actual_link?>' + option[5] + '?caID=<?php echo $caID; ?>&uid=' + option[1] + '&_=' + Math.random()).cropper({
					viewMode: 2,
					dragMode: 'none',
					zoomable:false,
					rotatable:false,
					strict: false,
					cropBoxResizable: false,
					aspectRatio:size[0]/size[1],
					autoCropArea:1,
					minContainerWidth: 500,
					minContainerHeight: 300,
					minCanvasWidth: 500,
					minCanvasHeight: 300,
					cropmove: function(){
					   // var data = $<?php echo $field_ui_id;?>image.cropper('getData');
					   // console.log(data);
					}
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
	</script>
      <div class="loader-overlay">
          <div class="output-loader"></div>
      </div>
  </form>
  </div>
  <style>
  .modal-dialog {
      z-index: 1060;
  }
  .modal-body img {
    max-width: 100%;
  }
  </style>

<?php

$s_path = $variables->account_root_url;

$v_script = array(
  'modules/'.$module.'/output/elementsOutput/jquery.validate/jquery.validate.min.js',
);

foreach($v_script as $s_item)
{
  $l_time = filemtime(BASEPATH.$s_item);
  ?><script type="text/javascript" src="<?php echo $s_path.$s_item.'?v='.$l_time;?>"></script><?php
}

?>

  <!-- <script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script> -->
  <script type="text/javascript">

	$("form.output-form").validate({
	  submitHandler: function(form) {
	    fw_loading_start();
		var formdata = $(form).serializeArray();
		var data = {};
		$(formdata ).each(function(index, obj){
			data[obj.name] = obj.value;
		});
		data.imagesToProcess = imagesToProcess;

	    $.ajax({
	      url: $(form).attr("action"),
	      cache: false,
	      type: "POST",
	      dataType: "json",
	      data: data,
	      success: function (data) {
	        if(data.error !== undefined)
	        {
  			 fw_loading_end();
	          var errorMessage = "";
	          $.each(data.error, function(index, value){
	            errorMessage += value+"<br/>";
	          });
	            $("#popup-validate-message").html(errorMessage, true);
	            $("#popup-validate-message").show();
	            $('#popupeditbox').css('height', "auto");
	        } else {
                if(data.redirect){
	  			 	fw_loading_end();
                    window.location = data.redirect;
                } else {
                    window.location.reload();
                }
	          // if(data.redirect_url !== undefined)
	          // {
	          //   out_popup.addClass("close-reload");
	          //   out_popup.close();
	          //   // fw_load_ajax(data.redirect_url, '', false);//window.location = data.redirect_url;
	          // }
	        }
	      }
	    }).fail(function() {
			fw_loading_end();
	      $("#popup-validate-message").html("<?php echo $formText_ErrorOccurredSavingContent_Output;?>", true);
	      $("#popup-validate-message").show();
	      $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
	    });
	  },
	  invalidHandler: function(event, validator) {
	    var errors = validator.numberOfInvalids();
	    if (errors) {
	      var message = errors == 1
	      ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
	      : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

	      $("#popup-validate-message").html(message);
	      $("#popup-validate-message").show();
	      $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
	    } else {
	      $("#popup-validate-message").hide();
	    }
	    setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
	  }
	});

	function reloadPopup(){
	    var data = {
	        cid: '<?php echo $cid;?>'
	    };
	    ajaxCall('editProfileImage', data, function(json) {
	        $('#popupeditboxcontent').html('');
	        $('#popupeditboxcontent').html(json.html);
	        out_popup = $('#popupeditbox').bPopup(out_popup_options);
	        $("#popupeditbox:not(.opened)").remove();
	    });
	}

	// New uploaded images to process
	var imagesToProcess = [];
	var imagesHandle = [];

	function callbackOnImageUpload(data) {
		if(imagesToProcess.length == 0){
		    for (first in data.result) break;
		    var uploaded_image = data.result[first][0];
		    imagesToProcess.push(uploaded_image.upload_id);
	        <?php foreach($resize_codes as $key => $resize_code) {?>
	            $("#<?php echo $field_ui_id;?>_files .handleInput<?php echo $key;?>").addClass("handle");
	            handle = $('#<?php echo $field_ui_id;?>_files .handle.handleInput<?php echo $key;?>').val("process:"+uploaded_image.upload_id+":<?php echo $resize_code;?>:"+uploaded_image.width+":"+uploaded_image.height+":"+uploaded_image.url+"");
	        <?php } ?>
	        handle_<?php echo $field_ui_id;?>();
		} else {
		}
	}
	// function callbackOnStart(){
	// 	console.log(imagesToProcess);
	// 	if(imagesToProcess.length > 0){
	// 		// break;
	// 	}
	// }
	function callbackOnImageDelete(data) {
		if(data != undefined && data.upload_id != undefined){
			var index = imagesToProcess.indexOf(data.upload_id);
			if (index > -1) {
		  		imagesToProcess.splice(index, 1);
	    	  	imagesHandle.splice(index, 1);

	            $("#<?php echo $field_ui_id;?>_files .handleInput").addClass("handle");

				$(".office-image-img img").attr("src", $('.office-image-img img').data("defaultimage"));
			}
		}
	}

	function callBackOnUploadAll(data) {
	    $('.popupformbtn .saveFiles').val('<?php echo $formText_Save; ?>').prop('disabled',false);
	};
	function callbackOnStart(data) {
	    $('.popupformbtn .saveFiles').val('<?php echo $formText_UploadInProgress_output;?>...').prop('disabled',true);
	};

	$(document).ready(function() {
	});
	// $('.changeProfileImagesBtn').on('click', function(e) {
	//     e.preventDefault();
	//     var data = {
	//         cid: '<?php echo $cid; ?>'
	//     };
	//     ajaxCall('changeProfileImage', data, function(json) {
	// 		$('#popupeditboxcontent2').html('');
	//         $('#popupeditboxcontent2').html(json.html);
	//         out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
	//         $("#popupeditbox2:not(.opened)").remove();
	//         out_popup.addClass("close-reload");
	//         // reloadPopup();
	//     });
	// });
	</script>
<?php
}
