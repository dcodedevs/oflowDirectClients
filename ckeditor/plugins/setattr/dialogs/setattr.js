/*
 Copyright (c) 2021, Dcode. All rights reserved.
 For licensing, send email to sale@dcode.no
*/
CKEDITOR.dialog.add("setattrDialog", function(editor) {
    return {
        name: "setattr",
        id: "setattr",
        title: "Set element attributes",
        minWidth: 350,
        minHeight: 200,
        contents: [{
            id: "tab-basic",
            label: "Basic attributes",
            elements: [{
				type: 'text',
				id: 'txt',
				label: 'Text',
				validate: CKEDITOR.dialog.validate.notEmpty( "Text field cannot be empty." ),
				setup: function( element ) {
					this.setValue( element.getText() );
				},
				commit: function( element ) {
					element.setText( this.getValue() );
				}
			},{
                type: "text",
                id: "lang",
                label: "Language code",
                validate: CKEDITOR.dialog.validate.notEmpty("Language code field cannot be empty."),
                setup: function( element ) {
					this.setValue( element.getAttribute( "lang" ) );
				},
				commit: function(element) {
                    element.setAttributes({
                        lang: this.getValue()
                    })
                }
            }]
        }],
        onShow: function() {
			var selection = editor.getSelection();
			var selectionText = selection.getSelectedText();
            var element = selection.getStartElement();
			
            if ( element )
                element = element.getAscendant( 'span', true );

            if ( !element || element.getName() != 'span' ) {
                element = editor.document.createElement( 'span' );
				element.setText(selectionText);
                this.insertMode = true;
            }
            else
                this.insertMode = false;

            this.element = element;
            //if ( !this.insertMode )
                this.setupContent( this.element );
        },
        onOk: function() {
            var dialog = this;
            var span = this.element;
            this.commitContent( span );

            if ( this.insertMode )
                editor.insertElement( span );
        }
    }
});