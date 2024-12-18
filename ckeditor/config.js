/**
 * @license Copyright (c) 2003-2019, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For complete reference see:
	// https://ckeditor.com/docs/ckeditor4/latest/api/CKEDITOR_config.html

	// The toolbar groups arrangement, optimized for two toolbar rows.
	config.toolbarGroups = [
        { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
		{ name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
		{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
		'/',
		{ name: 'links' },
		{ name: 'accLinks' },
		{ name: 'setattr' },
		{ name: 'insert' },
		{ name: 'forms' },
		{ name: 'tools' },
		{ name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'others' },
		// enabling styles also CONFIGURE it
		{ name: 'styles'}
	];


    /*
     * for any questions ask
     * niko@dcode.no
     */
        // including defaultsCommon.css file that contains common CSS styling
        // for CKEDITOR and current ACCOUNT
        config.contentsCss = '../elementsGlobal/defaults_ckeditor.css';
        // set Styles classes dropDown
        config.stylesSet = [
            { name: 'Dark', element: 'span', attributes: { 'class': 'ck_dark' } },
            { name: 'Red',  element: 'span', attributes: { 'class': 'ck_red' } }
        ];
        /*
        config.stylesSet = [
            { name: 'Dark', element: 'span', styles: { 'color': '#3c3c3f', 'font-size': '15px' } },
            { name: 'Red',  element: 'span', styles: { 'color': '#cc3039', 'font-size': '15px' } }
        ];
        */
        // config.skin = 'moonocolor';
        config.skin = 'moono-lisa';

        // set Formats classes dropDown
        config.format_h1 = { element: 'h1', attributes: { 'class': 'ck_h1' } };
        config.format_h2 = { element: 'h2', attributes: { 'class': 'ck_h2' } };
        config.format_h3 = { element: 'h3', attributes: { 'class': 'ck_h3' } };

        //config.extraPlugins = 'accLinks,video,youtube,setattr';
		config.extraPlugins = 'accLinks,youtube,setattr';
        config.youtube_responsive = true;

    /*
    ** END
    */

    // Remove some buttons provided by the standard plugins, which are
    // not needed in the Standard(s) toolbar.
    // config.removeButtons = 'Underline,Subscript,Superscript';
    config.removeButtons = 'Subscript,Superscript';

    // Set the most common block elements.
    config.format_tags = 'p;h1;h2;h3;pre';

    // Simplify the dialog windows.
    // config.removeDialogTabs = 'image:Advanced;link:Advanced';
    config.removeDialogTabs = 'image:Upload;image:Link;image:advanced;link:upload;link:advanced';

    config.filebrowserBrowseUrl = '../kcfinder/browse.php?opener=ckeditor&type=files';
    config.filebrowserImageBrowseUrl = '../kcfinder/browse.php?opener=ckeditor&type=images';
    config.filebrowserFlashBrowseUrl = '../kcfinder/browse.php?opener=ckeditor&type=flash';
    config.filebrowserUploadUrl = '../kcfinder/upload.php?opener=ckeditor&type=files';
    config.filebrowserImageUploadUrl = '../kcfinder/upload.php?opener=ckeditor&type=images';
    config.filebrowserFlashUploadUrl = '../kcfinder/upload.php?opener=ckeditor&type=flash';
	
	//CKEDITOR.config.allowedContent = true;
	//config.extraAllowedContent = 'span(ck_setattr)';
};
