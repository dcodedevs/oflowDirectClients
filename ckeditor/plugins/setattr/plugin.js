/*
 Copyright (c) 2021, Dcode. All rights reserved.
 For licensing, send email to developer@dcode.no
*/
CKEDITOR.plugins.add('setattr', {
    icons: 'setattr',
    init: function(editor) {
        editor.addCommand('setattr', new CKEDITOR.dialogCommand('setattrDialog', {
			allowedContent: 'span[lang]',
			requiredContent: 'span',
		}));
        editor.ui.addButton('setattr', {
            label: 'Set attributes',
            command: 'setattr',
            toolbar: 'setattr',
        });
		editor.on( 'doubleclick', function( evt ) {
			var element = evt.data.element;
			if ( element.is( 'span' ) && element.getAttribute( 'lang' ) )
				evt.data.dialog = 'setattrDialog';
		});
        CKEDITOR.dialog.add('setattrDialog', this.path + 'dialogs/setattr.js');
    }
});