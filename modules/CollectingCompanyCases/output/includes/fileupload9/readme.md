# Fileupload asset
# Version: 7

Use it like this. Callbacks are optional.

<?php
$fwaFileuploadConfig = array (
  'module_folder' => 'Case', // module id in which this block is used
  'id' => 'casefileupload',
  'content_table' => 'sys_filearchive_file_version',
  'content_field' => 'file',
  'content_module_id' => $fileArchiveModuleId, // id of FileArchiveGBox module
  'dropZone' => 'block',
  'callback' => 'callbackOnFileUpload',
  'callbackStart' => 'callbackOnFileUploadStart',
  'callbackAll' => 'callbackOnFileUploadAll',
  'callbackDelete' => 'callbackOnFileDelete'
);
  require __DIR__ . '/includes/fileupload3/output.php';
?>

# Config variables

#id
Should be used to seprate multiple upload blocks
It can be random or it can follow some naming convetions,
doesn't matter, BUT it must be unique!

#content_table
You can seti it manually or inherit from other
globally available variable

#content_field

#module_id

#module_folder

#dropZone
'block' - listens to drag & drop events on
itself (fileupload block)

'page' - listens for drag & drop events on
all page (body)

[class/id] - for example .container or #upload_block
!!! do not wrap with $()

Defaults to 'block'

You can also change drag & drop zone "on fly" with
public available function for each instance:
fwaFileuploadChangeDropZone_[upload_instance_id](element);
where upload_instance_id is id defined in this config (look up).
Very handy in situations, where you can have multiple
fileupload instances in DOM but hidden in tabs or slidedown divs.

#callback
JavaScript callback function name
Will be triggered on file upload
(jquery.fileupload - done)
data object will get passed to callback
function as parameter

#callbackAll
JavaScript callback function name
Will be triggered when all file uploads are finished
(when doing multiple file upload at once)!

#callbackDelete
JavaScript callback function name
Will be triggered when temp file is deleted
