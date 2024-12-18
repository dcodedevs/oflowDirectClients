<?php
// error_reporting(E_ALL);
// ini_set("display_errors", 1);
define('ACCOUNT_PATH', realpath(__DIR__.'/../../../../../'));
$account_path = ACCOUNT_PATH;
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../../input/includes/APIconnect.php");

// Get content table and field where to assign this image
$content_table = $fwaFileuploadConfig['content_table'];
$content_field = $fwaFileuploadConfig['content_field'];
$content_module_id = $fwaFileuploadConfig['content_module_id'];
if(isset($variables->languageID)){
    $languageID = $variables->languageID;
}
if(isset($variables->loggID)){
    $loggID = $variables->loggID;
}


// Get module name
$s_sql = "SELECT * FROM moduledata WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($content_module_id));
if($o_query && $o_query->num_rows() > 0){
    $module_data = $o_query->row_array();
}
$content_module_name = $module_data['name'];

// Module dir path
$module_dir = $account_path . '/modules/' . $content_module_name;

// Check if module fields file exists
if(!is_file($module_dir."/input/settings/fields/".$content_table."fields.php")) {
    echo "Module settings not found";
    exit;
}

// Include module fields file and language variables
include_once($module_dir."/input/settings/fields/".$content_table."fields.php");

// Directory related functions from input
if(!function_exists("dirsizeexec")) include_once($module_dir."/input/fieldtypes/Image/fn_dirsizeexec.php");
if(!function_exists("mkdir_recursive")) include_once($module_dir."/input/fieldtypes/Image/fn_mkdir_recursive.php");

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
	if($field[0] == $content_field){
		break;
	}
}

if($field[11] == '') $field[11] = 'T1,100:0,0';
list($type, $resize_codes) = explode(":",strtolower($field[11]),2);
list($fieldtype, $limit) = explode(',',$type);
$image_count_limit = ($limit>0?$limit:1);
if(!isset($resize_codes) or $resize_codes == '') $resize_codes = '0,0';
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
if(strpos($fieldtype,"p") !== false)
{
	$protected = true;
} else {
	$protected = false;
}
if(strpos($fieldtype,"o") !== false)
{
	$b_remove_original = false;
} else {
	$b_remove_original = true;
}

$v_upload = array(
	'upload_quota' => 0,
	'image_count_limit' => $image_count_limit,
	'items' => array()
);
if(array_key_exists($fieldName."_name",$_POST))
{
	$v_param = array('companyaccessID' => $_GET['caID'], 'session' => $_COOKIE['sessionID'], 'username' => $_COOKIE['username']);
	$o_query = $o_main->db->get_where('session_framework', $v_param);
	$fw_session = $o_query ? $o_query->row_array() : array();
	$v_upload['upload_quota'] = $fw_session['upload_quota'];

    $error_msg = array();
    if($fw_session['content_server_api_url'] != ""){
    	foreach($_POST[$fieldName."_name"] as $key => $item)
    	{
    		$image = array(
    			'action'=>'',
    			'items'=>array()
    		);
    		if(strpos($item,"process|") !== false)
    		{
    			// new uploads or delete old images
    			$focus_counter = 0;
    			$image_name = explode("|",$item); //process|upload_id|counter|filename
    			$image['filename'] = $image_name[3];
    			$image['upload_id'] = $image_name[1];
    			foreach($_POST[$fieldName."_img".$image_name[2]] as $variant => $img_item)
    			{
    				$img_obj = explode("|",$img_item); //process|upload_id|w,h,[a]c|orig_w|orig_h|img_src|[crop]x!0|y!0|w!0|h!0|rotate!0
    				if($img_obj[0] == "delete")
    				{
    					$image['action'] = 'delete';
    					$image['items'][] = $img_obj[1];

    				} else if($img_obj[0] == "process")
    				{
    					$v_properties = array();
    					$image['action'] = 'process';
    					//$image['items'][] = $img_item;
    					$img_size = explode(",",strtolower($img_obj[2]));
    					$v_properties['width'] = $img_size[0];
    					$v_properties['height'] = $img_size[1];
    					$v_properties['options'] = $img_size[2];
    					$v_properties['options_extra'] = $img_size[3];
    					$v_properties['protected'] = ($protected or strpos($img_size[3],"p") !== false);
    					$v_properties['path'] = $img_obj[5];
    					for($l_tmp = 6; $l_tmp < sizeof($img_obj); $l_tmp++)
    					{
    						$v_tmp = explode("!",$img_obj[$l_tmp]);
    						$v_properties['crop_'.$v_tmp[0]] = $v_tmp[1];
    					}
    					$image['items'][] = $v_properties;
    				}
    			}

    			$x = array();
    			if($show_text)
    			{
    				foreach($output_languages as $lid => $value)
    				{
    					$x[$lid] = $_POST[$fieldName."_label".$lid.$image_name[2]];
    				}
    			}
    			$image['labels'] = $x;
    			$image['links'] = ($show_link ? $_POST[$fieldName."_link".$image_name[2]] : "");
    			$image['focus'] = (isset($_POST[$fieldName."_focus".$image_name[2]]) ? $_POST[$fieldName."_focus".$image_name[2]] : "");
    			$image['remove_original'] = $b_remove_original;
    		} else {
    			// old images
    			$image_name = explode("|",$item); //process|upload_id|counter|filename
    			$image['filename'] = $image_name[3];
    			$image['upload_id'] = $image_name[1];
    			$image['action'] = 'handled_items';
    			$image['items'] = $_POST[$fieldName."_img".$image_name[2]];

    			$x = array();
    			if($show_text)
    			{
    				foreach($output_languages as $lid => $value)
    				{
    					$x[$lid] = $_POST[$fieldName."_label".$lid.$image_name[2]];
    				}
    			}
    			$image['labels'] = $x;
    			$image['links'] = ($show_link ? $_POST[$fieldName."_link".$image_name[2]] : "");
    			$image['focus'] = (isset($_POST[$fieldName."_focus".$image_name[2]]) ? $_POST[$fieldName."_focus".$image_name[2]] : "");
    		}

    		$v_upload['items'][] = $image;
    	}
        $o_query = $o_main->db->query("SELECT * FROM accountinfo");
        $v_accountinfo = $o_query ? $o_query->row_array() : array();

        $s_response = APIconnectAccount("account_authenticate", $v_accountinfo['accountname'], $v_accountinfo['password']);
        $v_response = json_decode($s_response, TRUE);

        $v_upload['data'] = json_encode(array('action'=>'handle_image'));
        $v_upload['items'] = json_encode($v_upload['items']);
        $v_upload['username'] = $_COOKIE['username'];
        $v_upload['accountname'] = $v_accountinfo['accountname'];
        $v_upload['token'] = $v_response['token'];

        //call api
        $ch = curl_init($fw_session['content_server_api_url']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $v_upload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $s_response = curl_exec($ch);

        $images = array();
        if($s_response !== false && $s_response != "")
        {
        	$v_response = json_decode($s_response, true);
        	if(isset($v_response['status']) && 1 == $v_response['status'])
        	{
        		$images = $v_response['items'];
        	}
        	if(isset($v_response['errors']) && 0 < sizeof($v_response['errors']))
        	{
        		foreach($v_response['errors'] as $s_error) $error_msg["error_".count($error_msg)] = $s_error;
        	}
        } else {
        	$error_msg["error_".count($error_msg)] = "Error occurred handling request";
        }
    } else {
    	// Process field data
    	list($type, $resize_codes) = explode(":",strtolower($field[11]),2);
    	list($fieldtype, $limit) = explode(',',$type);
    	$image_count_limit = ($limit>0?$limit:1);
    	if(!isset($resize_codes) or $resize_codes == '') {
    		$resize_codes = '0,0';
    	}
    	$resize_codes_array = explode(":", $resize_codes);
    	$image_counter = 0;

    	// Image with single text or text for each language
    	if(strpos($fieldtype,"s") !== false) {
    		$output_languages = array("all"=>"");
    	}
    	else {
    		$output_languages = array();
    		$rows = array();
    		$s_sql = "SELECT languageID, name FROM language WHERE outputlanguage = 1 ORDER BY defaultOutputlanguage DESC, sortnr ASC;";
    		$o_query = $o_main->db->query($s_sql);
    		if($o_query && $o_query->num_rows() > 0){
    			$rows = $o_query->result_array();
    		}
    		foreach($rows as $row){
    			$output_languages[$row['languageID']] = $row['name'];
    		}
    	}
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
        $s_sql = "SELECT * FROM ".$fwaFileuploadConfig['content_table']." WHERE id = ?";
    	$o_query = $o_main->db->query($s_sql, array($fwaFileuploadConfig['content_id']));
    	if($o_query && $o_query->num_rows() > 0){
    		$entry_data = $o_query->row_array();
    	}
        if ($entry_data[$fwaFileuploadConfig['content_field']]) {
            $images = json_decode($entry_data[$fwaFileuploadConfig['content_field']], true);
            if($fwaFileuploadConfig['reupload']){
                foreach($images as $image){
                    foreach($image[1] as $single_image){
                        unlink(ACCOUNT_PATH."/".$single_image);
                    }
                }
                $images = array();
            }
        } else {
            $images = array();
        }
      

        foreach($_POST[$fieldName."_name"] as $key => $item)
        {
            // Current image array
            $image = array();

            // new uploads or delete old images
            $s_original_file = '';
            $focus_counter = 0;

            $image_name = explode("|",$item); //process:upload_id:counter:filename
            if($image_name[0] == "process") {
                $toBeRemoved = false;
                if($_POST[$fieldName."_img".$key]) {
                    foreach($_POST[$fieldName."_img".$key] as $imageKey => $imageItem){
                        $imagename_field = explode("|", $imageItem);
                        if($imagename_field[0] == "delete")
                        {
                            foreach($images as $keyItem => $imageItem){
                                if($image_name[1] == $imageItem[4]) {
                                    unset($images[$keyItem]);
                                    $toBeRemoved = true;
                                }
                            }
                            break;
                        }
                    }
                    $newImages = array();
                    foreach($images as $image){
                        $newImages[] = $image;
                    }
                    $images = $newImages;
                }
                if(!$toBeRemoved) {
                    $o_main->db->reconnect();
					$s_sql = "SELECT * FROM uploads WHERE id = ? AND handle_status = 0";
                    $o_query = $o_main->db->query($s_sql, array($image_name[1]));
                    $uploaded_image_data = $o_query ? $o_query->row_array() : array();
                    $image[0] = $uploaded_image_data['filename'];
                    if($fwaFileuploadConfig['upload_type'] == 'video') {
                        // new uploads or delete old images
                        $s_original_file = rawurldecode($uploaded_image_data['filepath']);
                        $s_filename = preg_replace('#[^A-za-z0-9_/-]+#', '_', substr($uploaded_image_data['filename'], 0, strrpos($uploaded_image_data['filename'], '.')));


                        // Check rotation
                        $s_rotation = '';
                        exec('ffmpeg -i '.$account_path.'/'.$s_original_file.' 2>&1', $v_output);
                        foreach($v_output as $s_line)
                        {
                            $v_line = explode(':', $s_line);
                            if('rotate' == strtolower(trim($v_line[0])))
                            {
                                $s_rotation = trim($v_line[1]);
                            }
                        }

                        $s_rotate_cmd = '';
                        if ($s_rotation == '90')
                        {
                            $s_rotate_cmd = '-metadata:s:v:0 rotate=0 -vf "transpose=1" ';
                        }
                        else if ($rotation == '180')
                        {
                            $s_rotate_cmd = '-metadata:s:v:0 rotate=0 -vf "transpose=2,transpose=2" ';
                        }
                        else if ($rotation == '270')
                        {
                            $s_rotate_cmd = '-metadata:s:v:0 rotate=0 -vf "transpose=2" ';
                        }

                        $l_item = 0;
                        $x = array();
                        $v_video_outputs = array(
                            'mp4'=>'-f mp4 -vcodec libx264 -preset slow -profile:v main -acodec aac -strict -2 -movflags +faststart ',
                            'webm'=>'-f webm -c:v libvpx -b:v 2M -acodec libvorbis ',
                            'ogg'=>'-codec:v libtheora -q:v 5 '
                        );
                        foreach($v_video_outputs as $s_format => $s_options)
                        {
                            $s_img_path = $uploads_dir . '/' . $uploaded_image_data['id'] . '/'.$l_item.'/' . $s_filename . '.' . $s_format;
                            mkdir_recursive(dirname($account_path."/".$s_img_path));
                            exec('ffmpeg -i "'.$account_path.'/'.$s_original_file.'" '.$s_options.$s_rotate_cmd.'"'.$account_path.'/'.$s_img_path.'" -hide_banner');
                            $x[] = $s_img_path;
                            $l_item++;
                        }

                        // Img path
                        $image[] = $x;

                        // Upload id
                        $image[] = $uploaded_image_data['id'];

                        // Remove original image
                        if($b_remove_original)
                        {
                            $l_i = 0;
                            $s_delete_dir = dirname($account_path."/".$s_original_file);
                            $s_uploads_dir = $account_path.'/uploads';
                            $s_check = ltrim(str_replace($s_uploads_dir, '', $s_delete_dir), '/');
                            $v_check = explode('/', $s_check);
                            if(strpos($s_delete_dir, $s_uploads_dir) === false || $s_check == '' || (count($v_check) == 1 && in_array($v_check[0], array('storage', 'protected', 'userfiles'))))
                            {
                                // bad request for deletion
                                $o_main->db->reconnect();
								$o_main->db->query("UPDATE uploads SET handle_status = '10' WHERE id = ?", array($uploaded_image_data['id']));
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
                                        $error_msg["error_".count($error_msg)] = /*$formText_StorageFileWasNotDeleted_fieldtype*/"Storage file was not deleted".": ".$s_original_file;
                                        break;
                                    }
                                }
                            }
                            if(!is_file($account_path."/".$s_original_file))
                            {
                                // mysql_query("delete from uploads where id = '".$image_name[1]."'");
                                // mysql_query("UPDATE uploads SET handle_status = '1' WHERE id = ".$uploaded_image_data['id']."");

                            }
                            $s_original_file = "";
                        }
                    } else {
                        $x = array();

                        foreach($resize_codes_array as $resize_codes){
                            $resize_codes = explode(",",$resize_codes);

                            // $img_size = explode(",",strtolower($img_obj[2]));
                            $img_size = $resize_codes;

                            // $img_path = $uploads_dir.$img_obj[1]."/".$variant."/".$image_name[3];
                            $img_path = $uploads_dir . '/' . $uploaded_image_data['id'] . '/'.$image_counter.'/' . $uploaded_image_data['filename'];

                            mkdir_recursive(dirname($account_path."/".$img_path));
                            // $img_obj[5] = rawurldecode($img_obj[5]);
                            $s_original_file = rawurldecode($uploaded_image_data['filepath']);
                            list($src_w, $src_h, $stype, $attr) = getimagesize($account_path."/".$s_original_file);
                            // $s_original_file = $img_obj[5];
                            // Resizing not needed, just moving image, skip resize/crop if it is gif
                            if(($img_size[0] == 0 && $img_size[1] == 0 ) ||
                                (
                                ($src_w < $img_size[0] && $src_h < $img_size[1]) ||
                                ($src_w < $img_size[0] && $img_size[1] == 0) ||
                                ($img_size[0] == 0 && $src_h < $img_size[1])
                                ) && ($img_size[2] != "c" && $img_size[2] != "ac") || exif_imagetype($account_path."/".$s_original_file) == IMAGETYPE_GIF
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
                            $x[] = $img_path;
                            $image_counter++;
                        }
                        // Img path
                        $image[1] = $x;

                        // Multi lang
                        $x = array();
                        if($show_text)
                        {
                            foreach($output_languages as $lid => $value)
                            {
                                // $x[$lid] = $_POST[$fieldName."_label".$lid.$image_name[2]];
                            }
                        }
                        $image[2] = $x;

                        // Link
                        $image[3] = ($show_link ? $_POST[$fieldName."_link".$image_name[2]] : "");

                        // Upload id
                        $image[4] = $uploaded_image_data['id'];

                        // Focus point
                        $image[5] = (isset($_POST[$fieldName."_focus".$image_name[2]]) ? $_POST[$fieldName."_focus".$image_name[2]] : "");

                        // Remove original image
                        if($b_remove_original)
                        {
                            $l_i = 0;
                            $s_delete_dir = dirname($account_path."/".$s_original_file);
                            $s_uploads_dir = $account_path.'/uploads';
                            $s_check = ltrim(str_replace($s_uploads_dir, '', $s_delete_dir), '/');
                            $v_check = explode('/', $s_check);
                            if(strpos($s_delete_dir, $s_uploads_dir) === false || $s_check == '' || (count($v_check) == 1 && in_array($v_check[0], array('storage', 'protected', 'userfiles'))))
                            {
                                // bad request for deletion
								$o_main->db->reconnect();
								$o_main->db->query("UPDATE uploads SET handle_status = '10' WHERE id = ?", array($uploaded_image_data['id']));
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
                                        $error_msg["error_".count($error_msg)] = /*$formText_StorageFileWasNotDeleted_fieldtype*/"Storage file was not deleted".": ".$s_original_file;
                                        break;
                                    }
                                }
                            }
                            if(!is_file($account_path."/".$s_original_file))
                            {
                            }
                            $s_original_file = "";
                        }
                    }
                    $images[] = $image;
                }
            }
        }
        $images = json_encode($images);
    }
    if(count($error_msg) == 0){
        $field[6] = array();
		$field[6][$languageID] = $images;
        $s_sql = "UPDATE ".$fwaFileuploadConfig['content_table']." SET
        updated = now(),
        updatedBy=?,
        ".$fwaFileuploadConfig['content_field']." = ?
        WHERE id = ?";
		$o_main->db->reconnect();
        $o_main->db->query($s_sql, array($loggID, $field[6][$languageID], $fwaFileuploadConfig['content_id']));
    }
}
