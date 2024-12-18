<script type="text/javascript">

/**
 * **********************************************************
 * Public functions
 * **********************************************************
 * Available from "outside".
 * Postfixed with $fwaFileuploadConfig['id']
 * **********************************************************
 */

/**
* Public variable to track instance initalization status
* Helps to check if instance is already intialized and
* skip initalization part on some weird popup/view
* reloads, mainly to prevent duplicate events, etc.
*/
if(fwaFileuploadInitalized_<?php echo $fwaFileuploadConfig['id'];?> === undefined) {
  var fwaFileuploadInitalized_<?php echo $fwaFileuploadConfig['id'];?> = false;
}

/**
 * Instance perfix class (public), later assigned to private variable
 */
var instancePrefixClass_<?php echo $fwaFileuploadConfig['id'];?> = 'fwaFileupload_<?php echo $fwaFileuploadConfig['id'];?>';

/**
 * Change drop zone for initalized fileupload instance
 */
function fwaFileuploadChangeDropZone_<?php echo $fwaFileuploadConfig['id'];?>(element) {

  // Drop zone
  var dropZone;

  // Block itself
  var block = '.' + instancePrefixClass_<?php echo $fwaFileuploadConfig['id'];?>;

  // Block listens to drag & drop events only on itself
  if (element == 'block' || element.length == 0) {
    dropZone = block;
  }
  // All page
  else if (element == 'page') {
    dropZone = 'body';
  }
  // Raw value
  else {
    dropZone = element;
  }

  // Update dropzone for jQuery fileupload
  $('#<?php echo $fwaFileuploadConfig['id'];?>_upload').fileupload({
    dropZone: $(dropZone)
  });

  // Add styling events to dropzone
  $(dropZone).bind('dragover', function (e) {
    $(block).addClass('dragover');
  });

  $(dropZone).bind('dragleave', function (e) {
    $(block).removeClass('dragover');
  });

  $(dropZone).bind('drop', function (e) {
    setTimeout(function() {
      $(block).removeClass('dragover');
    }, 500);
  });

}

/**
 * **********************************************************
 * Private function
 * **********************************************************
 * We have all fileupload asset related functions in scope
 * so it doesn't crash if there are multiple fileupload
 * asset instances in DOM at the same time.
 * **********************************************************
 */

$(document).ready(function() {

  /**
   * Instance prefix class - to seperate events, actions and public functions between
   * multiple fileupload instances
   */
  var instancePrefixClass = instancePrefixClass_<?php echo $fwaFileuploadConfig['id'];?>;

  /**
   * Upload in process variable
   */
  var fileuploadInProcess = false;

  /**
   * Callback function proxy. Doesn't do anything by itself.
   * Is used as proxy in Fileupload scripts.
   * Calls function that is defined in fileupload config options.
   * Passes data object returned by ajax php handler script.
   * Does callback on EACH file upload.
   */
  function fwaFileuploadCallback(data) {
    <?php if ($fwaFileuploadConfig['callback']): ?>
    if(data === undefined) var data = {};
    <?php echo $fwaFileuploadConfig['callback']; ?>(data);
    <?php endif; ?>
  }

  /**
   * Callback function proxy that runs when ALL (multiple) file
   * uploads are done!
   */
  function fwaFileuploadCallbackAll(data) {
    <?php if ($fwaFileuploadConfig['callbackAll']): ?>
    if(data === undefined) var data = {};
    <?php echo $fwaFileuploadConfig['callbackAll']; ?>(data);
    <?php endif; ?>
  }

  /**
   * Callback function proxy that runs when file is deleted
   */
  function fwaFileuploadCallbackDelete(data) {
    <?php if ($fwaFileuploadConfig['callbackDelete']): ?>
    if(data === undefined) var data = {};
    <?php echo $fwaFileuploadConfig['callbackDelete']; ?>(data);
    <?php endif; ?>
  }

  /**
   * Callback function proxy that runs when uploads start
   */
  function fwaFileuploadCallbackStart() {
    <?php if ($fwaFileuploadConfig['callbackStart']): ?>
    <?php echo $fwaFileuploadConfig['callbackStart']; ?>();
    <?php endif; ?>
  }

  /**
   * Trigger fileupload events on input & start listening for drag & drop
   */
  function fwaFileuploadInit() {

    // Session id
    var fileupload_session_id = '<?php echo uniqid(); ?>';
    <?php
    $actual_link = "";
    if($fromApi){
        $actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }
    ?>
    // Create jQuery fileupload instance
    $('#<?php echo $fwaFileuploadConfig['id'];?>_upload').unbind().fileupload({
      url: '<?php echo $actual_link.'../modules/'.$fwaFileuploadConfig['module_folder'].'/output/includes/fileupload9/ajax_upload.php?param_name='.$fwaFileuploadConfig['id'].'_files'; ?>',
      dataType: 'json',
      formData: {
        content_module_id: '<?php echo $fwaFileuploadConfig['content_module_id'];?>',
        content_table: '<?php echo $fwaFileuploadConfig['content_table'];?>',
        content_field: '<?php echo $fwaFileuploadConfig['content_field'];?>',
        fileupload_session_id: fileupload_session_id
      },
      add: function (e, data) {
        var tmp_upload_id = data.files[0].name.replace('"','').replace("\\",'') +  '-' + data.files[0].size + '-' + Math.floor((Math.random() * 1000));
        fwaFileuploadAddFileToProgress(data.files[0].name, tmp_upload_id);
        fwaFileuploadFixFileListScroolbar();
        data.files[0].tmp_upload_id = tmp_upload_id;
        data.submit();
        // Trigger fwaFileuploadCallbackStart on first file
        if(!fileuploadInProcess) {
          fwaFileuploadCallbackStart();
          fileuploadInProcess = true;
        }
      },
      progress: function (e, data) {
        var progress = data._progress.loaded / data._progress.total * 100;
        $('[data-tmp-upload-id="' + data.files[0].tmp_upload_id + '"] .progress-fill').css('width', progress + '%');
      },
      done: function (e, data) {
        var progress = data._progress.loaded / data._progress.total * 100;
        if(progress >= 100) $('[data-tmp-upload-id="' + data.files[0].tmp_upload_id + '"] .progress').addClass('progress-complete').closest('li').find('.name').prepend('<span class="glyphicon glyphicon-ok progress-complete-icon"></span>');

        // Set real upload id
        $('[data-tmp-upload-id="' + data.files[0].tmp_upload_id + '"]').attr('data-upload-id', data.result.<?php echo $fwaFileuploadConfig['id'];?>_files[0].upload_id);
        // Set delete file url
        $('[data-tmp-upload-id="' + data.files[0].tmp_upload_id + '"]').attr('data-delete-url', data.result.<?php echo $fwaFileuploadConfig['id'];?>_files[0].deleteUrl + "&param_name=<?php echo $fwaFileuploadConfig['id'];?>_files");
        // Add fileupload_session_id to data
        data.fileupload_session_id = fileupload_session_id;
        // Run callback after file upload
        fwaFileuploadCallback(data);
      },
      stop: function(e, data) {
        // Add fileupload_session_id to data
        data.fileupload_session_id = fileupload_session_id;
        // Run callback when all files are uploaded
        fwaFileuploadCallbackAll(data);
        // Reset fileuploadInProcess variable
        fileuploadInProcess = false;
      }
    });

    // Set initial dropzone (from config)
    fwaFileuploadChangeDropZone_<?php echo $fwaFileuploadConfig['id'];?>('<?php echo $fwaFileuploadConfig['dropZone']; ?>');

    // If instance already initalized, skip duplicat event listeners
    if (!fwaFileuploadInitalized_<?php echo $fwaFileuploadConfig['id'];?>) fwaFileuploadAttachListeners();

    // Set initalization status to true
    fwaFileuploadInitalized_<?php echo $fwaFileuploadConfig['id'];?> = true;
  }

  /**
   * Add file to file upload list
   */
  function fwaFileuploadAddFileToProgress (name, tmp_upload_id) {
    var html = '<li data-tmp-upload-id="' + tmp_upload_id + '">' +
                  '<div class="name">' + name + '</div>' +
                  '<div class="progress">' +
                    '<div class="progress-fill"></div>' +
                  '</div>' +
                  '<a href="" class="delete-upload trash"><span class="glyphicon glyphicon-trash"></span></a>' +
                '</li>';

    // Be aware - prefixed with instancePrefixClass
    $('.' + instancePrefixClass +  ' .fwaFileupload_FilesList_Files').append(html);
  }

  /**
   * Add scroolbar if file list overflows file list container.
   * We use this workaround to hide scrollbar until it's necessary
   * on some browsers
   */
  function fwaFileuploadFixFileListScroolbar() {
    if($('.' + instancePrefixClass +  ' .fwaFileupload_FilesList').height() <= $('.' + instancePrefixClass +  ' .fwaFileupload_FilesList_Files').height()) {
      $('.' + instancePrefixClass +  ' .fwaFileupload_FilesList').css('overflow-y', 'scroll');
    }
    else {
      $('.' + instancePrefixClass +  ' .fwaFileupload_FilesList').css('overflow-y', 'hidden');
    }
  }

  /**
   * Event listeners
   * Be aware, that event listeners are also prefixed
   * with instacePrefixClass, so they don't conflict
   * with other event handlers, if fileupload
   * asset is used multiple times in DOM.
   */

   function fwaFileuploadAttachListeners() {
     // When delete button is clicked
     $('body').on('click', '.' + instancePrefixClass + ' .delete-upload', function(e) {
       e.preventDefault();
       $.post($(this).closest('li').data('deleteUrl'));
       $(this).closest('li').remove();
       fwaFileuploadCallbackDelete({ 'upload_id': $(this).closest('li').data('upload-id') });
     });

     // When "Browse for files" is clicked
     $('body').on('click', '.' + instancePrefixClass + ' .fwaFileupload_FilesBrowseDrop_Browse', function(e) {
       e.preventDefault();
       $('#<?php echo $fwaFileuploadConfig['id'];?>_upload').click()
     });
   }

  // Run init
  fwaFileuploadInit();

});
</script>
