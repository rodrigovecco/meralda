'use strict';
//
( function() {
	CKEDITOR.plugins.add( 'mwplaceholder', {
		requires: 'placeholder,widget,dialog',
		lang: 'en,es', // %REMOVE_LINE_CORE%
		onLoad: function() {
			// Register styles for placeholder widget frame.
			//CKEDITOR.addCss( '.cke_placeholder{background-color:#ff0}' );
		},

		init: function( editor ) {

			var lang = editor.lang.mwplaceholder;

			// Register dialog.
			CKEDITOR.dialog.add( 'mwplaceholder', this.path + 'dialogs/mwplaceholder.js' );

		},

		afterInit: function( editor ) {
		}
	} );

} )();
