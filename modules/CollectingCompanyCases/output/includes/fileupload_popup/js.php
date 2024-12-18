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

 var $<?php echo $fwaFileuploadConfig['id'];?>image = $('#<?php echo $fwaFileuploadConfig['id'];?>crop > img'),
 <?php echo $fwaFileuploadConfig['id'];?>handle,
 <?php echo $fwaFileuploadConfig['id'];?>counter,
 <?php echo $fwaFileuploadConfig['id'];?>alert_shown = false,
 <?php echo $fwaFileuploadConfig['id'];?>limit = parseInt('<?php echo $image_count_limit;?>'),
 <?php echo $fwaFileuploadConfig['id'];?>limitw = parseInt('<?php echo $focus_w_limit;?>'),
 <?php echo $fwaFileuploadConfig['id'];?>limith = parseInt('<?php echo $focus_h_limit;?>'),
 <?php echo $fwaFileuploadConfig['id'];?>handler = false;


$(document).ready(function() {
     var out_popup_fileupload_<?php echo $fwaFileuploadConfig['id'];?>;
     var out_popup_options_fileupload_<?php echo $fwaFileuploadConfig['id'];?>={
         follow: [true, true],
         followSpeed: 0,
         fadeSpeed: 0,
         modalClose: false,
         escClose: false,
         closeClass:'b-close',
         onOpen: function(){
             $(this).addClass('opened');
         },
         onClose: function(){
             if(!fileuploadInProcess){
                 $(".fwaFileupload_<?php echo $fwaFileuploadConfig['id'];?> .fwaFileupload_FilesList_Files .item").detach().appendTo('.fwaFileupload_FilesList_Files<?php echo $fwaFileuploadConfig['id'];?>');
             }
             $(".fwaFileupload_<?php echo $fwaFileuploadConfig['id'];?>").detach().insertAfter(".fwaFileuploadInit_<?php echo $fwaFileuploadConfig['id'];?>").hide();
             fwaFileuploadCallbackPopupClose();
             $(this).removeClass('opened');
         }
     };
    $.ajaxSetup({ cache: false });
    $(".fwaFileuploadInit_<?php echo $fwaFileuploadConfig['id'];?>").off("click").on("click", function(){
        $('#popupeditboxcontent_fileupload<?php echo $fwaFileuploadConfig['id'];?>').html("");
        $(".fwaFileupload_<?php echo $fwaFileuploadConfig['id'];?>").detach().appendTo('#popupeditboxcontent_fileupload<?php echo $fwaFileuploadConfig['id'];?>').show();
		out_popup_fileupload_<?php echo $fwaFileuploadConfig['id'];?> = $('#popupeditbox_fileupload<?php echo $fwaFileuploadConfig['id'];?>').bPopup(out_popup_options_fileupload_<?php echo $fwaFileuploadConfig['id'];?>);
		$("#popupeditbox_fileupload<?php echo $fwaFileuploadConfig['id'];?>:not(.opened)").remove();
    })
	$('#<?php echo $fwaFileuploadConfig['id'];?>focusmark').draggable({
		containment: "parent",
		stop: function() {
			$(<?php echo $fwaFileuploadConfig['id'];?>handle).val(
				Math.round((<?php echo $fwaFileuploadConfig['id'];?>rempx($(this).css('left'))+12)/$(this).data('ratio'))
				+':'+
				Math.round((<?php echo $fwaFileuploadConfig['id'];?>rempx($(this).css('top'))+12)/$(this).data('ratio'))
			).data('x',$(this).css('left')).data('y',$(this).css('top'));
		}
	});
	$('#<?php echo $fwaFileuploadConfig['id'];?>modal .btn.action').on('click', function(e){
		e.preventDefault();
		if($(this).data('action').length)
		if($(this).data('action') == 'move_left') {
			$<?php echo $fwaFileuploadConfig['id'];?>image.cropper('move', -5, 0);
		} else if($(this).data('action') == 'move_right') {
			$<?php echo $fwaFileuploadConfig['id'];?>image.cropper('move', 5, 0);
		} else if($(this).data('action') == 'move_up') {
			$<?php echo $fwaFileuploadConfig['id'];?>image.cropper('move', 0, -5);
		} else if($(this).data('action') == 'move_down') {
			$<?php echo $fwaFileuploadConfig['id'];?>image.cropper('move', 0, 5);
		} else if($(this).data('action') == 'scale_x') {
			var data = $<?php echo $fwaFileuploadConfig['id'];?>image.cropper('getData');
			$<?php echo $fwaFileuploadConfig['id'];?>image.cropper('scaleX', (data.scaleX * (-1)));
		} else if($(this).data('action') == 'reset') {
			$<?php echo $fwaFileuploadConfig['id'];?>image.cropper('reset');
		} else if($(this).data('action') == 'cancel') {
			$(<?php echo $fwaFileuploadConfig['id'];?>handle).closest('.item').find('.delete-upload').trigger('click');
			$('#<?php echo $fwaFileuploadConfig['id'];?>modal').modal('hide');
		}
	});
    $(".<?php echo $fwaFileuploadConfig['id'];?>_fancy").fancybox();

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
    function fwaFileuploadCallbackPopupClose() {
      <?php if ($fwaFileuploadConfig['callbackPopupClose']): ?>
      <?php echo $fwaFileuploadConfig['callbackPopupClose']; ?>();
      <?php endif; ?>
    }
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

  $(document).off("click", '.fwaFileupload_FilesList_Files<?php echo $fwaFileuploadConfig['id'];?> .item .delete_stored').on('click', '.fwaFileupload_FilesList_Files<?php echo $fwaFileuploadConfig['id'];?> .item .delete_stored', function(e){
      e.preventDefault();
      if(!fw_changes_made && !fw_click_instance)
      {
          fw_click_instance = true;
          var $_this = $(this);
          var name = $_this.closest('.item').find('input.name');
          if(!$(name).is('.deleted'))
          {
              bootbox.confirm({
                  message:"<?php echo $formText_DeleteItem_input;?>: " + $_this.attr("data-name") + "?",
                  buttons:{confirm:{label:"<?php echo $formText_Yes_input;?>"},cancel:{label:"<?php echo $formText_No_input;?>"}},
                  callback: function(result){
                      if(result)
                      {
                          $(name).addClass('deleted').val('process' + $(name).val());
                          $_this.closest('.item').addClass('deleted').find('input.image').each(function(){
                              $(this).val('delete|' + $(this).val());
                          });
                          $_this.closest('.item').hide();
                          // <?php echo $field_ui_id;?>update_btns();
                          fwaFileuploadCallbackDelete({ 'upload_id': $(this).closest('li').data('upload-id') });
                      }
                      fw_click_instance = false;
                  }
              });
          }
      }
  });
  /**
   * Trigger fileupload events on input & start listening for drag & drop
   */
   <?php
   $fileuploadLink = '../modules/'.$fwaFileuploadConfig['module_folder'].'/output/includes/fileupload_popup/ajax_upload.php?param_name='.$fwaFileuploadConfig['id'].'_files&file_type='.$fwaFileuploadConfig['upload_type'];
   if($variables->fw_session['content_server_api_url'] != ""){
       $fileuploadLink = $variables->fw_session['content_server_api_url']."?param_name=".$fwaFileuploadConfig['id']."_files&fieldextra=".$fieldtype;
   }
   ?>
  function fwaFileuploadInit() {
      var thumbnail = false;
      <?php if($fwaFileuploadConfig['custom']) {?>
          thumbnail = true;
      <?php } ?>
    // Session id
    var fileupload_session_id = '<?php echo uniqid(); ?>';
    //'<?php echo '../modules/'.$fwaFileuploadConfig['module_folder'].'/output/includes/fileupload_popup/ajax_upload.php?param_name='.$fwaFileuploadConfig['id'].'_files'; ?>',
    // Create jQuery fileupload instance
    $('#<?php echo $fwaFileuploadConfig['id'];?>_upload').unbind().fileupload({
      url: "<?php echo $fileuploadLink;?>",
      dataType: 'json',
      start: function (e, data) {
          fw_info_message_empty();
          <?php echo $fwaFileuploadConfig['id'];?>alert_shown = false;
      },
      add: function (e, data) {
          $.getJSON("<?php echo '../modules/'.$fwaFileuploadConfig['module_folder'].'/output/includes/fileupload_popup/get_next_id.php?type='.$fwaFileuploadConfig['upload_type'].'&time='.time();?>", function (result) {
              if($('.' + instancePrefixClass +  ' .item.upload-abort').length == 0)
              {
                  var abortContainer = $('<div/>')
                      .addClass('item upload-abort')
                      .append( '<?php echo $formText_AbortUploading_Fieldtype;?>' )
                      .click( function() {
                          data.abort();
                          $('.' + instancePrefixClass +  ' .fwaFileupload_FilesList_Files .item.abortable').remove();
                      } );
                  $('.' + instancePrefixClass +  ' .fwaFileupload_FilesList_Files').after(abortContainer);
              }

              $.each(data.files, function (index, file){
                  var tmp_upload_id = btoa(encodeURIComponent(file.name));
                  var html = '<li class="item abortable loading" data-tmp-upload-id="' + tmp_upload_id + '">' +
                                '<div class="name"><label class="fileLabel">' + file.name + '</label></div>' +
                                '<div class="progress">' +
                                  '<div class="progress-fill"></div>' +
                                '</div>';
                    <?php if($fwaFileuploadConfig['custom'] == "customCssClassImage") { ?>
                        html+='<div class="thumbnailOverlay"></div>';
                    <?php } ?>
                    html += '<a href="" class="delete-upload trash">';
                    <?php if($fwaFileuploadConfig['custom'] == "customCssClassImage") { ?>
                        html += '<span class="glyphicon glyphicon-remove"></span>';
                    <?php } else { ?>
                        html += '<span class="glyphicon glyphicon-trash"></span>';
                    <?php } ?>
                    html += '</a>';
                    html += '</li>';
                  $('.' + instancePrefixClass +  ' .fwaFileupload_FilesList_Files').append(html);
              });
              data.formData = result;
              data.submit();
              // Trigger fwaFileuploadCallbackStart on first file
              if(!fileuploadInProcess) {
                fwaFileuploadCallbackStart();
                fileuploadInProcess = true;
              }
          });

        // var tmp_upload_id = data.files[0].name.replace('"','').replace("\\",'') +  '-' + data.files[0].size + '-' + Math.floor((Math.random() * 1000));
        // fwaFileuploadAddFileToProgress(data.files[0].name, tmp_upload_id);
        // fwaFileuploadFixFileListScroolbar();
        // data.files[0].tmp_upload_id = tmp_upload_id;
        // data.submit();

      },
      progress: function (e, data) {
        var progress = data._progress.loaded / data._progress.total * 100;
        $('.' + instancePrefixClass +  ' .fwaFileupload_FilesList_Files [data-tmp-upload-id="'  + btoa(encodeURIComponent(data.files[0].name)) + '"] .progress-fill').css('width', progress + '%');
      },
      done: function (e, data) {
        //
        // // Set real upload id
        // $('.' + instancePrefixClass +  ' .fwaFileupload_FilesList_Files [data-tmp-upload-id="' + btoa(encodeURIComponent(data.files[0].name)) + '"]').attr('data-upload-id', data.result.<?php echo $fwaFileuploadConfig['id'];?>_files[0].upload_id);
        // // Set delete file url
        // $('.' + instancePrefixClass +  ' .fwaFileupload_FilesList_Files [data-tmp-upload-id="' + btoa(encodeURIComponent(data.files[0].name)) + '"]').attr('data-delete-url', data.result.<?php echo $fwaFileuploadConfig['id'];?>_files[0].deleteUrl + "&param_name=<?php echo $fwaFileuploadConfig['id'];?>_files");
        // // Add fileupload_session_id to data
        // data.fileupload_session_id = fileupload_session_id;
        if(data.result.message){
            fw_info_message_add('error', data.result.message);
            $('.' + instancePrefixClass +  ' .fwaFileupload_FilesList_Files [data-tmp-upload-id="' + btoa(encodeURIComponent(data.files[0].name)) + '"]').remove();
            fw_info_message_show();
        } else {
            var progress = data._progress.loaded / data._progress.total * 100;
            if(progress >= 100) $('.' + instancePrefixClass +  ' .fwaFileupload_FilesList_Files [data-tmp-upload-id="' + btoa(encodeURIComponent(data.files[0].name)) + '"] .progress').addClass('progress-complete').closest('li').find('.name').prepend('<span class="glyphicon glyphicon-ok progress-complete-icon"></span>');

            $.each(data.result.<?php echo $fwaFileuploadConfig['id'];?>_files, function (index, file) {
                if(file.error) {
                    fw_info_message_add('error', file.name + ': ' + file.error);
                    $('.' + instancePrefixClass +  ' .fwaFileupload_FilesList_Files [data-tmp-upload-id="' + btoa(encodeURIComponent(file.name)) + '"]').remove();
                } else {
                    if($('.' + instancePrefixClass +  ' .fwaFileupload_FilesList_Files .item:not(.deleted):not(.loading)').length + $('.fwaFileupload_FilesList_Files<?php echo $fwaFileuploadConfig['id'];?> .item:not(.deleted):not(.loading)').length >= <?php echo $fwaFileuploadConfig['id'];?>limit)
                    {
                        if(!<?php echo $fwaFileuploadConfig['id'];?>alert_shown)
                        {
                            fw_info_message_add('error', '<?php echo $formText_MaximumNumberOfAllowableFileUploadsHasBeenExceeded_Fieldtype."! ".$formText_FieldOnlyAllowsToUpload_fieldtype." ".$image_count_limit." ".$formText_filesInTotal_Fieldtype;?>.');
                        }
                        <?php echo $fwaFileuploadConfig['id'];?>alert_shown = true;
                        $.post(file.deleteUrl + "&param_name=<?php echo $fwaFileuploadConfig['id'];?>_files", function(data){
                            if(data[file.name] == true) $('.' + instancePrefixClass +  ' .fwaFileupload_FilesList_Files [data-tmp-upload-id="' + btoa(encodeURIComponent(file.name)) + '"]').remove();
                        },"json");
                    } else {
                        var oDiv = $('[data-tmp-upload-id="' + btoa(encodeURIComponent(file.name)) + '"]').eq(0).removeClass('abortable');
                        oDiv.attr("data-tmp-upload-id", btoa(encodeURIComponent(file.name)+file.upload_id));
                        // Set real upload id
                        $('[data-tmp-upload-id="' + btoa(encodeURIComponent(file.name)+file.upload_id) + '"]').attr('data-upload-id', file.upload_id);
                        // Set delete file url
                        $('[data-tmp-upload-id="' + btoa(encodeURIComponent(file.name)+file.upload_id) + '"]').attr('data-delete-url', file.deleteUrl + "&param_name=<?php echo $fwaFileuploadConfig['id'];?>_files");


                        var <?php echo $fwaFileuploadConfig['id'];?>counter = parseInt($('#<?php echo $fwaFileuploadConfig['id'];?>counter').val())+1;
                        // var oDiv = $('<div/>').attr('class', 'item row');
                        // var oThumbCol = $('<div/>').attr('class', 'col-md-1 col-xs-2');
                        <?php if($fwaFileuploadConfig['upload_type'] == 'image') {?>
                            var oThumbDiv = $('<div/>').attr('class', 'thumbnail');
                            var oThumbImg = $('<img/>').attr('src', '<?php if($variables->fw_session['content_server_api_url'] == "") echo $extradomaindirroot;?>'+(((file.no_handle || thumbnail) || file.thumbUrl == undefined) ? file.url : file.thumbUrl) + '?caID=<?php echo $_GET['caID'];?>&uid=' + file.upload_id);
                            oThumbImg.appendTo(oThumbDiv);
                            oThumbDiv.insertAfter(oDiv.find(".name span"));
                        <?php } ?>

                        var oTextCol = $('<div/>');
                        // var oDeleteCol = $('<div/>').attr('class', 'col-md-2 col-xs-2 text-right');
                        // var oDeleteBtn = $('<button/>').attr('class', 'btn btn-sm btn-delete').attr('data-type', file.deleteType).attr('data-url', file.deleteUrl + "&param_name=<?php echo $fwaFileuploadConfig['id'];?>_files").append('<i class="glyphicon glyphicon-trash"></i><div><?php echo $formText_delete_fieldtype;?></div>');
                        // <?php if($show_focuspoint) { ?>var oFocusBtn = $('<button/>').attr('class', 'btn btn-sm btn-focus set_focuspoint').append('<i class="glyphicon glyphicon-record"></i><div><?php echo $formText_Focus_fieldtype;?></div>');<?php } ?>
                        //
                        // $('.' + instancePrefixClass +  ' .fwaFileupload_FilesList_Files [data-tmp-upload-id="' + btoa(encodeURIComponent(file.name)) + '"]').replaceWith(oDiv);
                        oTextCol.append('<input type="hidden" name="<?php echo $fwaFileuploadConfig['id']?>_name[]" value="process|' + file.upload_id + '|' + <?php echo $fwaFileuploadConfig['id'];?>counter + '|' + file.name + '"/>');
                        <?php

                        foreach($resize_codes as $resize_code)
                        {
                            $tmp = explode(",", $resize_code);
                            ?>oTextCol.append('<input type="hidden" class="<?php echo ($tmp[2]=="c"?"handle":"");?>" name="<?php echo $fwaFileuploadConfig['id'];?>_img' + <?php echo $fwaFileuploadConfig['id'];?>counter+ '[]" value="process|' + file.upload_id + '|<?php echo $resize_code;?>|' + file.width + '|' + file.height + '|' + file.url + '" data-device="<?php echo $tmp[4];?>"/>');<?php

                            if(strpos($tmp[3],"f")!==false)
                            {
                                ?>
                                var h = file.height, w = file.width, ratio = 0;
                                if(h > w)
                                {
                                    var ini_h = h;
                                    if(h > <?php echo $fwaFileuploadConfig['id'];?>limith) h = <?php echo $fwaFileuploadConfig['id'];?>limith;
                                    ratio = h/ini_h;
                                    w = Math.round(w*ratio);
                                } else {
                                    var ini_w = w;
                                    if(w > <?php echo $fwaFileuploadConfig['id'];?>limitw) w = <?php echo $fwaFileuploadConfig['id'];?>limitw;
                                    ratio = w/ini_w;
                                    h = Math.round(h*ratio);
                                }
                                oTextCol.append('<input class="focus handlefocus" type="hidden" name="<?php echo $fwaFileuploadConfig['id']."_focus";?>' + <?php echo $fwaFileuploadConfig['id'];?>counter+ '[]" value="0:0" data-src="' + file.url + '?caID=<?php echo $_GET['caID'];?>&uid=' + file.upload_id + '" data-w="' + w + '" data-h="' + h + '" data-x="0" data-y="0" data-ratio="' + ratio + '">');<?php
                            }
                        }
                        ?>
                        if(file.no_handle)
                        {
                            oTextCol.find('input').removeClass('handle');
                        }

                        oTextCol.appendTo(oDiv);
                        oDiv.removeClass("loading");
                        $(window).resize();

                        // oThumbCol.appendTo(oDiv);
                        // oThumbDiv.appendTo(oThumbCol);
                        // oDeleteBtn.on("click", function() {
                        //     $.post($(this).data('url'), function(data){
                        //         if(data[file.name] == true) oDiv.remove();
                        //         <?php echo $fwaFileuploadConfig['id'];?>update_btns();
                        //     },"json");
                        // });
                        // oDeleteBtn.appendTo(oDeleteCol);
                        // <?php if($show_focuspoint) { ?>
                        // oFocusBtn.on("click", function() {
                        //     $(this).closest('.item').find('input.focus').addClass('handlefocus');
                        //     handle_<?php echo $fwaFileuploadConfig['id'];?>();
                        // });
                        // oFocusBtn.prependTo(oDeleteCol);
                        // <?php } ?>
                        // oDeleteCol.appendTo(oDiv);

                        $('#<?php echo $fwaFileuploadConfig['id'];?>counter').val(<?php echo $fwaFileuploadConfig['id'];?>counter);

                        handle_<?php echo $fwaFileuploadConfig['id'];?>();
                    }
                }
            });
            fw_info_message_show();
            // Run callback after file upload
            fwaFileuploadCallback(data);
        }
      },
      stop: function(e, data) {
          $('.' + instancePrefixClass + ' .item.upload-abort').unbind('click').remove();
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
  // function fwaFileuploadAddFileToProgress (name, tmp_upload_id) {
  //   var html = '<li data-tmp-upload-id="' + tmp_upload_id + '">' +
  //                 '<div class="name">' + name + '</div>' +
  //                 '<div class="progress">' +
  //                   '<div class="progress-fill"></div>' +
  //                 '</div>' +
  //                 '<a href="" class="delete-upload trash"><span class="glyphicon glyphicon-trash"></span></a>' +
  //               '</li>';
  //
  //   // Be aware - prefixed with instancePrefixClass
  //   $('.' + instancePrefixClass +  ' .fwaFileupload_FilesList_Files').append(html);
  // }

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
	   var fileItem = $(this).closest('li');
       $.post($(this).closest('li').data('deleteUrl'), function(data){
           fileItem.remove();
           fwaFileuploadCallbackDelete({ 'upload_id': $(this).closest('li').data('upload-id') });
       },"json");
     });
     $('body').on('click', '.fwaFileupload_FilesList_Files<?php echo $fwaFileuploadConfig['id'];?> .delete-upload', function(e) {
        e.preventDefault();
		var fileItem = $(this).closest('li');
        $.post($(this).closest('li').data('deleteUrl'), function(data){
            fileItem.remove();
            fwaFileuploadCallbackDelete({ 'upload_id': $(this).closest('li').data('upload-id') });
        },"json");
      });

     // When "Browse for files" is clicked
     $('body').on('click', '.' + instancePrefixClass + ' .fwaFileupload_FilesBrowseDrop_Browse', function(e) {
       e.preventDefault();
       $('#<?php echo $fwaFileuploadConfig['id'];?>_upload').click()
     });
   }

  // Run init
  fwaFileuploadInit();
  $('#<?php echo $fwaFileuploadConfig['id'];?>modal').on('hidden.bs.modal', function () {
      var data = $<?php echo $fwaFileuploadConfig['id'];?>image.cropper('getData');
      var str = $(<?php echo $fwaFileuploadConfig['id'];?>handle).val() + obj_to_string_<?php echo $fwaFileuploadConfig['id'];?>(data);
      $(<?php echo $fwaFileuploadConfig['id'];?>handle).val(str);
      $<?php echo $fwaFileuploadConfig['id'];?>image.cropper('destroy');
      $(window).resize();
      <?php echo $fwaFileuploadConfig['id'];?>handler = false;
      handle_<?php echo $fwaFileuploadConfig['id'];?>();
  });
  $('#<?php echo $fwaFileuploadConfig['id'];?>modalfocus').on('hidden.bs.modal', function () {
      <?php echo $fwaFileuploadConfig['id'];?>handler = false;
      handle_<?php echo $fwaFileuploadConfig['id'];?>();
  });
  $('#<?php echo $fwaFileuploadConfig['id'];?>focusmark').draggable({
      containment: "parent",
      stop: function() {
          $(<?php echo $fwaFileuploadConfig['id'];?>handle).val(
              Math.round((<?php echo $fwaFileuploadConfig['id'];?>rempx($(this).css('left'))+12)/$(this).data('ratio'))
              +':'+
              Math.round((<?php echo $fwaFileuploadConfig['id'];?>rempx($(this).css('top'))+12)/$(this).data('ratio'))
          ).data('x',$(this).css('left')).data('y',$(this).css('top'));
      }
  });
  function <?php echo $fwaFileuploadConfig['id'];?>rempx(string){
      return Number(string.substring(0, (string.length - 2)));
  }

  function handle_<?php echo $fwaFileuploadConfig['id'];?>()
  {
      if(!<?php echo $fwaFileuploadConfig['id'];?>handler)
      {
          <?php echo $fwaFileuploadConfig['id'];?>handler = true;
          var _cur = '';
          if($('.' + instancePrefixClass +  ' .cur .handle').length > 0 || $('.' + instancePrefixClass +  ' .cur .handlefocus').length > 0)
          {
              _cur = ' .cur';
          }
          var handle = $('.' + instancePrefixClass +  '' + _cur + ' .handle'),
              handlefocus = $('.' + instancePrefixClass +  '' + _cur + ' .handlefocus');

          if(handle.length > 0)
          {
              <?php echo $fwaFileuploadConfig['id'];?>handle = handle.get(0);
              var option = $(<?php echo $fwaFileuploadConfig['id'];?>handle).val().split('|');
              if(option.length > 2){
                  fw_loading_start();
                  var size = option[2].split(',');
                  var $devices = $('#<?php echo $fwaFileuploadConfig['id'];?>modal .modal-header .device-list');
                  if(option[0] == 'process' && size[2] == 'c')
                  {
                      $devices.find('i').removeClass('active');
                      $('#<?php echo $fwaFileuploadConfig['id'];?>modal').modal({show:true});
                      var device_txt = '<?php echo $formText_GeneralPurpose_fieldtype;?>';
                      if(size[4] != '')
                      {
                          $devices.find('i.' + size[4]).addClass('active');
                          device_txt = $devices.find('i.' + size[4]).attr('title');
                      }
                      $('#<?php echo $fwaFileuploadConfig['id'];?>modal .modal-header .device-name').text(device_txt);
                      $('#<?php echo $fwaFileuploadConfig['id'];?>modal .modal-footer .file-name').text($(<?php echo $fwaFileuploadConfig['id'];?>handle).parent().find('.filename').text());
                      $<?php echo $fwaFileuploadConfig['id'];?>image.attr('src','').attr('src', option[5] + '?caID=<?php echo $_GET['caID'];?>&uid=' + option[1] + '&_=' + Math.random()).off("load").load(function(){
                          $(this).cropper({
                              strict:false,
                              zoomable:false,
                              rotatable:false,
                              aspectRatio:size[0]/size[1],
                              autoCropArea:1/*,
                              preview:"#<?php echo $fwaFileuploadConfig['id'];?>preview"*/
                          });
                          fw_loading_end();
                      });
                  }

                  if(_cur == '') $(<?php echo $fwaFileuploadConfig['id'];?>handle).parent().addClass('cur');
                  $(<?php echo $fwaFileuploadConfig['id'];?>handle).removeClass("handle");
              }
          }
          else if(handlefocus.length > 0)
          {
              fw_loading_start();
              <?php echo $fwaFileuploadConfig['id'];?>handle = $(handlefocus).get(0);
              $('#<?php echo $fwaFileuploadConfig['id'];?>focus').css({
                  'background-image': 'none',//url('+$(<?php echo $fwaFileuploadConfig['id'];?>handle).data('src')+')',
                  width: $(<?php echo $fwaFileuploadConfig['id'];?>handle).data('w'),
                  height: $(<?php echo $fwaFileuploadConfig['id'];?>handle).data('h'),
              });
              $('#<?php echo $fwaFileuploadConfig['id'];?>focusmark').css({
                  left: $(<?php echo $fwaFileuploadConfig['id'];?>handle).data('x'),
                  top: $(<?php echo $fwaFileuploadConfig['id'];?>handle).data('y'),
              }).data('ratio',$(<?php echo $fwaFileuploadConfig['id'];?>handle).data('ratio'));
              $('#<?php echo $fwaFileuploadConfig['id'];?>modalfocus').modal({show:true});

              var bgimage = new Image();
                  bgimage.src = $(<?php echo $fwaFileuploadConfig['id'];?>handle).data('src');
              $(bgimage).load(function(){
                  $('#<?php echo $fwaFileuploadConfig['id'];?>focus').css({'background-image': 'url('+$(<?php echo $fwaFileuploadConfig['id'];?>handle).data('src')+')'});
                  fw_loading_end();
                  $(bgimage).remove();
              });

              if(_cur == '') $(<?php echo $fwaFileuploadConfig['id'];?>handle).parent().addClass('cur');
              $(<?php echo $fwaFileuploadConfig['id'];?>handle).removeClass("handlefocus");
          } else {
              <?php echo $fwaFileuploadConfig['id'];?>handler = false;
          }
      } else {
          setTimeout(handle_<?php echo $fwaFileuploadConfig['id'];?>, 300);
      }
  }
  function obj_to_string_<?php echo $fwaFileuploadConfig['id'];?>(obj)
  {
      var str = '';
      for (var p in obj) {
          if (obj.hasOwnProperty(p)) {
              str += '|' + p + '!' + obj[p];
          }
      }
      return str;
  }
});
</script>
