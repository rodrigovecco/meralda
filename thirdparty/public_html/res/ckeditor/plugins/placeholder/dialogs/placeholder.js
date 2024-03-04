
/**
 * @license Copyright (c) 2003-2016, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

/**
 * @fileOverview Definition for placeholder plugin dialog.
 *
 */

'use strict';

CKEDITOR.dialog.add( 'placeholder', function( editor ) {
	var lang = editor.lang.placeholder,
		generalLabel = editor.lang.common.generalTab,
		validNameRegex = /^[^\[\]<>]+$/;
		
		
	var result={
		title: lang.title,
		minWidth: 300,
		minHeight: 80,
		contents: [
			{
				id: 'info',
				label: generalLabel,
				title: generalLabel,
				elements: [
					// Dialog window UI elements.
					{
						id: 'name',
						type: 'text',
						style: 'width: 100%;',
						label: lang.name,
						'default': '',
						required: true,
						validate: CKEDITOR.dialog.validate.regex( validNameRegex, lang.invalidName ),
						setup: function( widget ) {
							this.setValue( widget.data.name );
						},
						commit: function( widget ) {
							widget.setData( 'name', this.getValue() );
						}
					},
					/*
					{
						id: 'btnsel',
						type: 'button',
						
						label: lang.selectPlaceholder,
						hidden:advhidden,
						onClick:function(){
							var d=this.getDialog();
							
							if(editor.config.mw){
								if(editor.config.mw.placeholderman){
									editor.config.mw.placeholderman.openDialogForCkEditor(editor,d);
								}
							}

						
						},
						setup: function( widget ) {
						},
					},
					*/
					
				]
			}
		]
	}
	if(editor.config.mw){
		if(editor.config.mw.placeholderman){
			editor.config.mw.placeholderman.prepareCKeditorPHDialog(editor,this,result);
		}
	}

	return result;
} );
