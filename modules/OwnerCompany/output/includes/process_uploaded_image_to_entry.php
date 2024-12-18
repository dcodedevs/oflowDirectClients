<?php



/**
 * This is not completely finished!!
 *
 * TODO
 * 1) [DONE - 21.03.2017] multiple image upload to same field
 *      reads previous data, adds new image at end
 * 2) multilanguage
 * 3) focus point implementation
 * 4) upload qutoes
 */

function process_uploaded_image_to_entry ($o_main, $uploaded_file_id, $entry_id) {
    // error_reporting(E_ALL);
    // ini_set('display_errors', 1);
	// $uploaded_file_id - file id in "uploads" table
	// $entry_id - entry id in content table to which add this image to

	// Account path
	$account_path = ACCOUNT_PATH;

	// Get uploaded image data
	$uploaded_image_data_sql = $o_main->db->query("SELECT * FROM uploads WHERE id = ? AND handle_status = 0", array($uploaded_file_id));
	if($uploaded_image_data_sql && $uploaded_image_data_sql->num_rows() > 0) $uploaded_image_data = $uploaded_image_data_sql->row_array();

	// Get content table and field where to assign this image
    $content_table = $uploaded_image_data['content_table'];
    $content_field = $uploaded_image_data['content_field'];
    $content_module_id = $uploaded_image_data['content_module_id'];


	// Get module name
	$module_data_sql = $o_main->db->query("SELECT * FROM moduledata WHERE id = ?", array($content_module_id));
	if($module_data_sql && $module_data_sql->num_rows() > 0) $module_data = $module_data_sql->row();
	$content_module_name = $module_data->name;

	// Module dir path
	$module_dir = $account_path . '/modules/' . $content_module_name;

	// Check if module fields file exists
	if(!is_file($module_dir."/input/settings/fields/".$content_table."fields.php")) {
		echo "Module settings not found";
		exit;
	}

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

	// Image with single text or text for each language
	if(strpos($fieldtype,"s") !== false) {
		$output_languages = array("all"=>"");
	}
	else {
		$output_languages = array();
		$rs = $o_main->db->query("SELECT languageID, name FROM language WHERE outputlanguage = 1 ORDER BY defaultOutputlanguage DESC, sortnr ASC;");
		if($rs && $rs->num_rows() > 0)
		foreach($rs->result() AS $row) {
			$output_languages[$row->languageID] = $row->name;
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
		$img_path = $uploads_dir . '/' . $uploaded_image_data['id'] . '/'.$image_counter.'/' . $uploaded_image_data['filename'];

		mkdir_recursive(dirname($account_path."/".$img_path));
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

    // Get previous content

    $entry_data_sql = $o_main->db->query("SELECT * FROM $content_table WHERE id = ?", array($entry_id));
    if($entry_data_sql && $entry_data_sql->num_rows() > 0) $entryData = $entry_data_sql->row();

    if ($entryData->$content_field) {
        $images = json_decode($entryData->$content_field, true);
    } else {
        $images = array();
    }

    $images[] = $image;

	$images = json_encode($images);

	$sql = "UPDATE $content_table SET $content_field = '$images' WHERE id = ?";

	$o_main->db->query($sql, array($entry_id));

}

?>
