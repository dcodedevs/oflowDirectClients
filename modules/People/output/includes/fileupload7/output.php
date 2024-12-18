<?php
/**
 * Base styling for fileupload block
 */
require __DIR__ . '/style.php';
/**
 * JavaScript logic for fileupload block (drag & drop events, upload handling)
 */
require __DIR__ . '/js.php';

/**
 * Start local widget session
 * Should consist of id and some timestamp?
 */

?>

<div class="fwaFileupload fwaFileupload_<?php echo $fwaFileuploadConfig['id'];?>">
	<div class="fwaFileupload_Files">
		<div class="fwaFileupload_FilesBrowseDrop">
			<div class="fwaFileupload_FilesBrowseDrop_Title">
				<?php echo $formText_DragAndDropFilesHere; ?>
			</div>
			<div class="fwaFileupload_FilesBrowseDrop_Icon">
				<span class="glyphicon glyphicon-arrow-down"></span>
			</div>
			<div class="fwaFileupload_FilesBrowseDrop_Browse">
				<div class="fwaFileupload_FilesBrowseDrop_Browse_Or"><?php echo $formText_or; ?></div>
				<a href=""><?php echo $formText_BrowseFilesOnYourComputer; ?></a>
			</div>
		</div>
		<div class="fwaFileupload_FilesList">
			<ul class="fwaFileupload_FilesList_Files"></ul>
		</div>
		<input id="<?php echo $fwaFileuploadConfig['id']; ?>_upload" type="file" name="<?php echo $fwaFileuploadConfig['id']; ?>_files[]">
	</div>
</div>
