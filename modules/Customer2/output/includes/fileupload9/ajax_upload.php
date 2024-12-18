<?php

// Constants (taken from fw/index.php)
define('FRAMEWORK_DEBUG', FALSE);
define('ACCOUNT_PATH', realpath(__DIR__.'/../../../../../')); // this is modified to fit this files location
define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
$v_tmp = explode("/",ACCOUNT_PATH);
$accountname = array_pop($v_tmp);

// Load database
require_once __DIR__ . '/../../../../../elementsGlobal/cMain.php';

$o_query = $o_main->db->query("SHOW TABLES LIKE 'uploads'");

if($o_query && !$o_query->num_rows())
{
	$sql = "CREATE TABLE `uploads` (
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
	 `content_status` TINYINT(2) NOT NULL,
	 `handle_status` INT(11) NULL DEFAULT NULL,
	 `content_module_id` INT(11) NULL DEFAULT NULL,
	 `content_table` TEXT NULL,
	 `content_field` TEXT NULL,
	 `fileupload_session_id` TEXT NULL,
	 PRIMARY KEY (`id`),
	 INDEX `origIdIdx` (`origId`),
	 INDEX `relation` (`content_module_id`, `content_table`(100), `content_field`(100))
	)
	COLLATE='utf8_general_ci';";
    $o_query = $o_main->db->query($sql);
} else {
    // if table exists, check if all fields are there
    $fields = $o_main->db->list_fields('uploads');

    if (!$fields['handle_status']) {
        $sql = "ALTER TABLE uploads ADD `handle_status` INT(11) NULL DEFAULT NULL";
        $o_query = $o_main->db->query($sql);
    }

    if (!$fields['content_module_id']) {
        $sql = "ALTER TABLE uploads ADD `content_module_id` INT(11) NULL DEFAULT NULL";
        $o_query = $o_main->db->query($sql);
    }

    if (!$fields['content_table']) {
        $sql = "ALTER TABLE uploads ADD `content_table` TEXT NULL";
        $o_query = $o_main->db->query($sql);
    }

    if (!$fields['content_field']) {
        $sql = "ALTER TABLE uploads ADD `content_field` TEXT NULL";
        $o_query = $o_main->db->query($sql);
    }

    if (!$fields['fileupload_session_id']) {
        $sql = "ALTER TABLE uploads ADD `fileupload_session_id` TEXT NULL";
        $o_query = $o_main->db->query($sql);
    }
}

$remove_path = explode("/modules/",__DIR__);
$options = array(
    'remove_path' => "/modules/".$remove_path[1],
    'delete_type' => 'POST',
    'mkdir_mode' => 0777,
    'param_name' => $_GET['param_name'],
    'db_table' => 'uploads',
    'user_email' => $_COOKIE['username'],
    /*'accept_file_types' => '/\.(gif|jpe?g|png|docx?|xlsx?|pdf|rar|zip|mp3|mp4|mp3|mov|flv|swf|wmv|wav|avi|bmp)$/i',*/
    'accept_file_types' => '/.*(?<!exe|php|pl|py|cgi|asp|js)$/i',
    'image_versions' => array(),
    'content_module_id' => $_POST['content_module_id'],
    'content_table' => $_POST['content_table'],
    'content_field' => $_POST['content_field'],
    'fileupload_session_id' => $_POST['fileupload_session_id']
);

require_once("class_UploadHandler.php");

class FileUploadHandler extends UploadHandler {

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

        global $o_main;
        $sql = "INSERT INTO ".$this->options['db_table']." (created, createdBy, content_status, handle_status, content_module_id, content_table, content_field, fileupload_session_id) VALUES (NOW(), '".$this->options['user_email']."', 0, 0, '".$this->options['content_module_id']."', '".$this->options['content_table']."', '".$this->options['content_field']."', '".$this->options['fileupload_session_id']."')";

        $o_query = $o_main->db->query($sql);
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
				$file->name = addslashes($file->name);
				$sql = "UPDATE ".$this->options['db_table']." SET filename = '".$file->name."', filepath = '".$file->url."', size = '".$file->size."' WHERE id = '".$this->options['upload_id']."'";
                $o_query = $o_main->db->query($sql);
			} else {
				$sql = "DELETE FROM ".$this->options['db_table']." WHERE id = '".$this->options['upload_id']."'";
                $o_query = $o_main->db->query($sql);
			}
		} else {
			$file = new \stdClass();
			$file->error = "Upload_id is not available";
		}
		return $file;
    }

    protected function set_additional_file_properties($file) {
        global $o_main;
        parent::set_additional_file_properties($file);
        if (isset($this->options['upload_id'])) {
            $file->deleteUrl .= '&_id='.rawurlencode($this->options['upload_id']);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['_id'])) {
            $sql = "SELECT * FROM ".$this->options['db_table']." WHERE id = '".$o_main->db->escape_str($_GET['_id'])."'";
            $o_query = $o_main->db->query($sql);
			$row = $o_query ? $o_query->row_array() : array();;
			$this->options['upload_id'] = $row['id'];
        }
    }

	public function delete($print_response = true) {
        global $o_main;
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
				// $sql = "DELETE FROM ".$this->options['db_table']." WHERE id = '".$this->options['upload_id']."'";
				$sql = "UPDATE ".$this->options['db_table']." SET handle_status = '3' WHERE id = '".$this->options['upload_id']."'";
                $o_query = $o_main->db->query($sql);
            }
            $response[$file_name] = $success;
        }
        return $this->generate_response($response, $print_response);
    }
}

$upload_handler = new FileUploadHandler($options);
?>
