<?php
set_time_limit(100);
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');

if(!$o_main->db->table_exists('uploads'))
{
	$o_main->db->simple_query("CREATE TABLE `uploads` (
		`id` INT(11) NOT NULL AUTO_INCREMENT,
		`moduleID` INT(11) NULL DEFAULT NULL,
		`createdBy` CHAR(255) NULL DEFAULT NULL,
		`created` DATETIME NULL DEFAULT NULL,
		`updatedBy` CHAR(255) NULL DEFAULT NULL,
		`updated` DATETIME NULL DEFAULT NULL,
		`origId` INT(11) NULL DEFAULT NULL,
		`sortnr` INT(11) NULL DEFAULT NULL,
		`filename` TEXT NULL,
		`filepath` TEXT NULL,
		`size` INT(11) NULL DEFAULT NULL,
		PRIMARY KEY (`id`),
		INDEX `origIdIdx` (`origId`)
	)");
}

$fieldtype = $_GET['fieldextra'];
if(strpos($fieldtype,"q") !== false)
{
	$imageQuality = "";
} else {
	$imageQuality = 75;
}
$remove_path = explode("/modules/",__DIR__);
$options = array(
	'remove_path' => "/modules/".$remove_path[1],
    'delete_type' => 'POST',
	'mkdir_mode' => 0777,
	'param_name' => $_GET['param_name'],
    'db_table' => 'uploads',
	'user_email' => $_COOKIE['username'],
	'accept_file_types' => '/\.(gif|jpe?g|png)$/i',
	'image_versions' => array(
		'' => array(
			'auto_orient' => true,
			'jpeg_quality' => $imageQuality,
			'strip' => true
		),
		'thumb' => array(
			'crop' => true,
			'max_width' => 100,
			'max_height' => 100,
			'auto_orient' => true,
			'jpeg_quality' => $imageQuality,
			'strip' => true
		)
	)
);

require_once("class_UploadHandler.php");


class ImageUploadHandler extends UploadHandler {

    protected function initialize() {
		$this->options['script_url'] = $this->get_full_url().str_replace(__DIR__,'',__FILE__);
        $this->options['upload_dir'] = rtrim(str_replace($this->options['remove_path'], '', __DIR__),'/').'/uploads/storage/';
		$this->options['upload_url'] = 'uploads/storage/';
        parent::initialize();
    }

    /*protected function handle_form_data($file, $index) {
        $file->title = @$_REQUEST['title'][$index];
        $file->description = @$_REQUEST['description'][$index];
    }*/
	
	protected function get_upload_path($file_name = null, $version = null) {
        $file_name = $file_name ? $file_name : '';
        $version_path = '';
		if (empty($version)) {
			if(!empty($this->options['upload_id'])) {
				$version_path .= $this->options['upload_id'].'/';
			}
        } else {
            $version_dir = @$this->options['image_versions'][$version]['upload_dir'];
            if ($version_dir) {
                return $version_dir.$this->get_user_path().$file_name;
            }
            if(!empty($this->options['upload_id'])) {
				$version_path .= $this->options['upload_id'].'/';
			}
			$version_path .= $version.'/';
        }
        return $this->options['upload_dir'].$this->get_user_path()
            .$version_path.$file_name;
    }
	
	protected function get_download_url($file_name, $version = null, $direct = false) {
        if (!$direct && $this->options['download_via_php']) {
            $url = $this->options['script_url']
                .$this->get_query_separator($this->options['script_url'])
                .$this->get_singular_param_name()
                .'='.rawurlencode($file_name);
            if ($version) {
                $url .= '&version='.rawurlencode($version);
            }
            return $url.'&download=1';
        }
        $version_path = '';
		if (empty($version)) {
			if(!empty($this->options['upload_id'])) {
				$version_path .= rawurlencode($this->options['upload_id']).'/';
			}
        } else {
            $version_url = @$this->options['image_versions'][$version]['upload_url'];
            if ($version_url) {
                return $version_url.$this->get_user_path().rawurlencode($file_name);
            }
			if(!empty($this->options['upload_id'])) {
				$version_path .= rawurlencode($this->options['upload_id']).'/';
			}
			$version_path .= rawurlencode($version).'/';
        }
        return $this->options['upload_url'].$this->get_user_path()
            .$version_path.rawurlencode($file_name);
    }

    protected function handle_file_upload($uploaded_file, $name, $size, $type, $error,
            $index = null, $content_range = null) {
        $o_main = get_instance();
		$sql = "INSERT INTO ".$this->options['db_table']." (created, createdBy) VALUES (NOW(), ?)";
		$o_main->db->query($sql, array($this->options['user_email']));
		$this->options['upload_id'] = $o_main->db->insert_id();
		if($this->options['upload_id'] > 0)
		{
			list($img_width, $img_height) = $this->get_image_size($uploaded_file);
			$file = parent::handle_file_upload(
				$uploaded_file, $name, $size, $type, $error, $index, $content_range
			);
			$file->width = $img_width;
			$file->height = $img_height;
			$file->upload_id = $this->options['upload_id'];
			if (empty($file->error)) {
				$sql = "UPDATE ".$this->options['db_table']." SET filename = ?, filepath = ?, size = ? WHERE id = ?";
				$o_main->db->query($sql, array($file->name, $file->url, $file->size, $this->options['upload_id']));
			} else {
				$sql = "DELETE FROM ".$this->options['db_table']." WHERE id = ?";
				$o_main->db->query($sql, array($this->options['upload_id']));
			}
		} else {
			$file = new \stdClass();
			$file->error = "Upload_id is not available";
		}
		return $file;
    }

    protected function set_additional_file_properties($file) {
        parent::set_additional_file_properties($file);
        $o_main = get_instance();
		if (isset($this->options['upload_id'])) {
            $file->deleteUrl .= '&_id='.rawurlencode($this->options['upload_id']);
        }
		
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['_id'])) {
			$this->options['upload_id'] = '';
			$o_query = $o_main->db->query("SELECT * FROM ".$this->options['db_table']." WHERE id = ?", array($_GET['_id']));
			if($o_query && $o_row = $o_query->row()) $this->options['upload_id'] = $o_row->id;
        }
    }
	
	public function delete($print_response = true) {
        $o_main = get_instance();
		$file_names = $this->get_file_names_params();
		$file_ids = $this->get_query_param('_id');
        if (empty($file_names)) {
            $file_names = array($this->get_file_name_param());
			$file_ids = array($this->get_query_param('_id'));
        }
        $response = array();
        foreach($file_names as $idx=>$file_name) {
            if($file_ids[$idx]) {
				$this->options['upload_id'] = $file_ids[$idx];
			}
			$file_path = $this->get_upload_path($file_name);
            $success = is_file($file_path) && $file_name[0] !== '.' && unlink($file_path);
            if ($success) {
                foreach($this->options['image_versions'] as $version => $options) {
                    if (!empty($version)) {
                        $file = $this->get_upload_path($file_name, $version);
                        if (is_file($file)) {
                            unlink($file);
							rmdir(dirname($file));
                        }
                    }
                }
				rmdir($this->get_upload_path());
				$o_main->db->query("DELETE FROM ".$this->options['db_table']." WHERE id = ?", array($this->options['upload_id']));
            }
            $response[$file_name] = $success;
        }
        return $this->generate_response($response, $print_response);
    }
}

$upload_handler = new ImageUploadHandler($options);
?>