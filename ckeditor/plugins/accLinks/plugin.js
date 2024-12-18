/*
 Copyright (c) 2015, Dcode & Niko. All rights reserved.
 For licensing, send email to niko@dcode.no
*/
CKEDITOR.plugins.add('accLinks', {
    icons: 'accLinks',
    init: function(editor) {
        editor.addCommand('accLinks', new CKEDITOR.dialogCommand('accLinksDialog'));
        editor.ui.addButton('accLinks', {
            label: 'Choose account URL',
            command: 'accLinks',
            toolbar: 'accLinks',
        });
		editor.on( 'doubleclick', function( evt ) {
			var element = evt.data.element;
			if ( element.is( 'a' ) && ( element.hasClass( 'ck_accLinks' ) || element.getAttribute( 'data-pageid' ) ) )
				evt.data.dialog = 'accLinksDialog';
		});
        CKEDITOR.dialog.add('accLinksDialog', this.path + 'dialogs/accLinks.js');
    }
});