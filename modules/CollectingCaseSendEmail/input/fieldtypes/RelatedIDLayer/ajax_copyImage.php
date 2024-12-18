<?php
$account_path = realpath(__DIR__."/../../../../../");
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
if(!function_exists("mkdir_recursive")) include(__DIR__."/../Image/fn_mkdir_recursive.php");

$s_default_output_language = $_POST['s_default_output_language'];
$choosenListInputLang = $_POST['choosenListInputLang'];
$extradir = __DIR__."/../../../";
include(__DIR__."/../../includes/readInputLanguage.php");

$show_text = $show_link = $protected = $show_focuspoint = false;
$b_remove_original = true;

$settings = explode('(#)',$_POST['settings']);
$access = $_POST['access'];
$field_ui_id = $_POST['field_ui_id'];

$l_img_counter = $_POST['img_counter'];
$image_fieldname = $_POST['image_fieldname'];
$extradir = $_POST['extradir'];
$extraimagedir = $_POST['extraimagedir'];
$imageSizeLimit = 1024;
$extradomaindirroot = $_POST['extradomaindirroot'];

$newImages = array();
$images = $_POST['value'];
foreach($images as $image)
{
	$o_main->db->query('INSERT INTO uploads (created, createdBy) VALUES (NOW(), ?)', array($_COOKIE['username']));
	$l_upload_id = $o_main->db->insert_id();
	$obj = $image;
	$obj[1] = array();
	$obj[4] = $l_upload_id;
	foreach($settings as $variant => $setting)
	{
		list($l_image_index, $v_tmp) = explode('#',strtolower($setting), 2);
		$img_size = explode('#',$v_tmp);
		if(isset($image[1][$l_image_index]))
		{
			$img_src = $image[1][$l_image_index];
			$v_src_image = explode("/", $img_src);
			$s_image_name = array_pop($v_src_image);
			
			$uploads_dir = "uploads/";
			//if($protected or strpos($img_size[3],"p") !== false) $uploads_dir .= "protected/";
			$img_path = $uploads_dir.$l_upload_id."/".$variant."/".$s_image_name;
			mkdir_recursive(dirname($account_path."/".$img_path));
			list($src_w, $src_h, $stype, $attr) = getimagesize($account_path."/".$img_src);
				
			if(($img_size[0] == 0 && $img_size[1] == 0 ) || 
				(
				($src_w < $img_size[0] && $src_h < $img_size[1]) ||
				($src_w < $img_size[0] && $img_size[1] == 0) ||
				($img_size[0] == 0 && $src_h < $img_size[1])
				) && ($img_size[2] != "c" && $img_size[2] != "ac")
			)
			{
				copy($account_path."/".$img_src, $account_path."/".$img_path);
			}
			else
			{
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
				
				$imagick = new Imagick($account_path."/".$img_src);
				/*if($img_size[2] == "c")
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
				} else {*/
					$imagick->resizeImage($newwidth, $newheight, imagick::FILTER_LANCZOS, 1);
					//$tmp = explode(":",$_POST[$fieldName."_focus".$image_name[2]][$focus_counter]);
					//$tmp[0] = floor($tmp[0]/$denratio);
					//$tmp[1] = floor($tmp[1]/$denratio);
					if($img_size[2] == "ac")
					{
						$imagick->cropImage($autowidth, $autoheight, $src_x, $src_y);
						//$tmp[0] = $tmp[0] - $src_x;
						//$tmp[1] = $tmp[1] - $src_y;
					}
					/*if(strpos($img_size[3],"f") !== false)
					{
						if($tmp[0]>$img_size[0]) $tmp[0] = $img_size[0];
						if($tmp[1]>$img_size[1]) $tmp[1] = $img_size[1];
						if($tmp[0]<0) $tmp[0] = 0;
						if($tmp[1]<0) $tmp[1] = 0;
						$_POST[$fieldName."_focus".$image_name[2]][$focus_counter] = implode(":",$tmp);
						$focus_counter++;
					}*/
				//}
				$imagick->writeImage($account_path."/".$img_path);
			}
			
			
			
			
			$obj[1][] = $img_path;
		}
	}
	
	$o_main->db->query('DELETE FROM uploads WHERE id = ?', array($l_upload_id));
	$newImages[] = $obj;
}

foreach($newImages as $obj)
{
	$name = (string)$obj[0];
	$image = (array)$obj[1];
	$labels = (array)$obj[2];
	$link = (string)$obj[3];
	$upload_id = $obj[4];
	//$focus = $obj[5];
	$max_w = 0;
	$min_w = 100000;
	foreach($image as $img)
	{
		list($w, $h, $t, $a) = getimagesize($account_path."/".$img);
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
//		if($upload_id>0 && is_file(ACCOUNT_PATH."/uploads/storage/".$upload_id."/thumb/".$name))
//		{
//			$thumb_img_link = $extradomaindirroot."uploads/storage/".$upload_id."/thumb/".$name."?caID=".$_GET['caID']."&uid=".$upload_id;
//		} else {
//			$thumb_img_link = $extradomaindirroot.$thumb_img;
//		}
	$thumb_img_link = $extradomaindirroot.$thumb_img;
	?>
	<div class="item row">
		<div class="col-md-2">
			<div class="thumbnail"><a rel="gal_<?php echo $field_ui_id."_".$l_img_counter;?>" class="<?php echo $field_ui_id;?>_fancy script" target="_blank" href="<?php echo $extradomaindirroot.$popup_img.(strpos($popup_img,'uploads/protected/')!==false?'?caID='.$_GET['caID'].'&table='.$fields[0][3].'&field='.$field[0].'&ID='.$ID.'&languageID='.$variables->languageID:'');?>"><img class="ptr" src="<?php echo $thumb_img_link.(strpos($thumb_img_link,'uploads/protected/')!==false?'?caID='.$_GET['caID'].'&table='.$fields[0][3].'&field='.$field[0].'&ID='.$ID.'&languageID='.$variables->languageID:'');?>"></a></div>
		</div>
		<div class="col-md-8">
			<strong><?php echo $formText_Filename_fieldtype;?></strong>: <?php echo $name;?>
			<input class="name" type="hidden" name="<?php echo $image_fieldname."_name";?>[]" value="<?php echo ":".$upload_id.":".$l_img_counter.":".$name;?>"><?php
			$focus_counter = 0;
			foreach($image as $x=>$item)
			{
				//$tmp = explode(",", $resize_codes[$x]);
				?><input class="image" type="hidden" name="<?php echo $image_fieldname."_img".$l_img_counter;?>[]" value="<?php echo $item;?>"><?php
				/*if(strpos($tmp[3],"f")!==false && is_file(ACCOUNT_PATH."/".$item))
				{
					list($w,$h,$t,$r) = getimagesize(ACCOUNT_PATH."/".$item);
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
					?><input class="focus" type="hidden" name="<?php echo $image_fieldname."_focus".$l_img_counter;?>[]" value="<?php echo $focus[$focus_counter];?>" data-src="<?php echo $extradomaindirroot.$item.(strpos($item,'uploads/protected/')!==false?'?caID='.$_GET['caID'].'&table='.$fields[0][3].'&field='.$field[0].'&ID='.$ID.'&languageID='.$variables->languageID:'');?>" data-w="<?php echo $w;?>" data-h="<?php echo $h;?>" data-x="<?php echo $x-12;?>" data-y="<?php echo $y-12;?>" data-ratio="<?php echo $ratio;?>"><?php
					$focus_counter++;
				}*/
			}
			if($show_link)
			{
				?><div class="row"><div class="col-md-4"><b><?php echo $formText_PictureLink_fieldtype;?></b>:</div><div class="col-md-8"><input type="text" name="<?php echo $image_fieldname."_link".$l_img_counter;?>" value="<?php echo htmlspecialchars($link);?>"<?php echo ($field[10]==1||$access<10?" readonly":"");?>></div></div><?php
			}
			if($show_text)
			{
				foreach($output_languages as $lid => $value)
				{
					?><div class="row"><div class="col-md-4"><b><?php echo $formText_Picturetext_fieldtype;?></b> <i><?php echo $value;?></i>:</div><div class="col-md-8"><input type="text" name="<?php echo $image_fieldname."_label".$lid.$l_img_counter;?>" value="<?php echo htmlspecialchars($labels[$lid]);?>"<?php echo ($field[10]==1||$access<10?" readonly":"");?>></div></div><?php
				}
			}
			?>
		</div>
		<div class="col-md-2">
			<?php if(/*$field[10]!=1 && */$access >= 10) {?>
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
	$l_img_counter++;
}
?>