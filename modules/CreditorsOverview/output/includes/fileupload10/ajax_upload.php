<?php
// ini_set('display_errors', 1);

ini_set('max_execution_time', 120);
define('FRAMEWORK_DEBUG', FALSE);
define('ACCOUNT_PATH', realpath(__DIR__.'/../../../../../')); // this is modified to fit this files location
define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
$v_tmp = explode("/",ACCOUNT_PATH);
$accountname = array_pop($v_tmp);
include(__DIR__."/../../../../../elementsGlobal/cMain.php");

$s_sql = "SHOW TABLES LIKE 'uploads'";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows() == 0) {
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

	$o_main->db->query($sql);
}

$remove_path = explode("/modules/",__DIR__);
$options = array(
	'remove_path' => "/modules/".$remove_path[1],
  	'delete_type' => 'POST',
	'mkdir_mode' => 0777,
	'param_name' => $_GET['param_name'],
  	'db_table' => 'uploads',
	'user_email' => $_COOKIE['username'],
	// 'accept_file_types' => '/\.(gif|jpe?g|png)$/i',
	'max_file_size' => 30* 1024 * 1024,
	'accept_file_types' => '/.*(?<!exe|php|pl|py|cgi|asp|js)$/i',
	'image_versions' => array(),
	'content_module_id' => $_POST['content_module_id'],
	'content_table' => $_POST['content_table'],
	'content_field' => $_POST['content_field'],
	'fileupload_session_id' => $_POST['fileupload_session_id'],
	'image_versions' => array(
		'' => array(
			'auto_orient' => true,
			'strip' => true
		)
	),
	'o_main' => $o_main
);

include(__DIR__."/../readOutputLanguage.php");
$error_messages = array(
	1 => $formText_UploadedFileExceedsTheMaximumUploadSize_output, //'The uploaded file exceeds the upload_max_filesize directive in php.ini',
	2 => $formText_TheUploadedFileExceedTheMaximumUploadSize_output, //'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
	3 => $formText_TheUploadedFileWasOnlyPartiallyUploaded_output, //'The uploaded file was only partially uploaded',
	4 => $formText_NoFileWasUploaded_output, //'No file was uploaded',
	6 => $formText_MissingATemporaryFolder_output, // 'Missing a temporary folder',
	7 => $formText_FailedToWrite_output, //'Failed to write file to disk',
	8 => $formText_StoppedTheFileUpload_output, //'A PHP extension stopped the file upload',
	'post_max_size' => $formText_UploadedFileExceedsTheMaximumUploadSize_output, // 'The uploaded file exceeds the post_max_size directive in php.ini',
	'max_file_size' => $formText_UploadedFileExceedsTheMaximumUploadSize_output, //'File is too big',
	'min_file_size' => $formText_UploadedFileRequiresTheMinimumUploadSize_output, //'File is too small',
	'accept_file_types' => $formText_FiletypeNotAllowed_output, //'Filetype not allowed',
	'max_number_of_files' => $formText_ExceededMaximumNumberOfFiles_output, //'Maximum number of files exceeded',
	'max_width' => $formText_ImageExceedsMaxWidth_output, //'Image exceeds maximum width',
	'min_width' => $formText_ImageRequiresMinWidth_output, //'Image requires a minimum width',
	'max_height' => $formText_ImageExceedsMaxHeight_output, //'Image exceeds maximum height',
	'min_height' => $formText_ImageRequiresMinWidth_output, //'Image requires a minimum height',
	'abort' => $formText_FileUploadAborted_output, //'File upload aborted',
	'image_resize' => $formText_FailedToResizeImage_output, //'Failed to resize image'
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
        $sql = "INSERT INTO ".$this->options['o_main']->db_escape_name($this->options['db_table'])." (created, createdBy, content_status, handle_status, content_module_id, content_table, content_field, fileupload_session_id) VALUES (NOW(), ?, 0, 0, ?, ?, ?, ?)";

		$this->options['o_main']->db->query($sql, array($this->options['user_email'], $this->options['content_module_id'], $this->options['content_table'], $this->options['content_field'], $this->options['fileupload_session_id']));

		$this->options['upload_id'] = $this->options['o_main']->db->insert_id();
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
				$sql = "UPDATE ".$this->options['o_main']->db_escape_name($this->options['db_table'])." SET filename = ?, filepath = ?, size = ? WHERE id = ?";
				$this->options['o_main']->db->query($sql, array($file->name, $file->url, $file->size, $this->options['upload_id']));
			} else {
				$sql = "DELETE FROM ".$this->options['o_main']->db_escape_name($this->options['db_table'])." WHERE id = ?";
				$this->options['o_main']->db->query($sql, array($this->options['upload_id']));
			}
		} else {
			$file = new \stdClass();
			$file->error = "Upload_id is not available";
		}
		return $file;
    }

    protected function set_additional_file_properties($file) {
        parent::set_additional_file_properties($file);
        if (isset($this->options['upload_id'])) {
            $file->deleteUrl .= '&_id='.rawurlencode($this->options['upload_id']);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['_id'])) {
			$s_sql = "SELECT * FROM ".$this->options['o_main']->db_escape_name($this->options['db_table'])." WHERE id = ?";
		    $o_query = $this->options['o_main']->db->query($s_sql, array($_GET['_id']));
		    if($o_query && $o_query->num_rows()>0) {
		        $row = $o_query->row_array();
		    }
			$this->options['upload_id'] = $row['id'];
        }
    }

	public function delete($print_response = true) {
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
				$sql = "UPDATE ".$this->options['o_main']->db_escape_name($this->options['db_table'])." SET handle_status = '3' WHERE id = ?";
				$this->options['o_main']->db->query($sql, array($this->options['upload_id']));
            }
            $response[$file_name] = $success;
        }
        return $this->generate_response($response, $print_response);
    }
}

$upload_handler = new FileUploadHandler($options, true, $error_messages);
?>
