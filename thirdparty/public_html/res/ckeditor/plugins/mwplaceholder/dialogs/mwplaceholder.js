
/**
 * @license Copyright (c) 2003-2016, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

/**
 * @fileOverview Definition for placeholder plugin dialog.
 *
 */

'use strict';

CKEDITOR.dialog.add( 'mwplaceholder', function( editor ) {
	var lang = editor.lang.mwplaceholder,
		generalLabel = editor.lang.common.generalTab,
		validNameRegex = /^[^\[\]<>]+$/;

	return {
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
						label: "var",
						'default': '',
						
						
						setup: function( widget ) {
							//this.setValue( widget.data.name );
						},
						commit: function( widget ) {
							//widget.setData( 'name', this.getValue() );
						}
					},
					{
						id: 'inputscontainer',
						type: 'html',
						style: 'width: 100%;',
						html:"<div>...</div>",
						onShow:function(){
							var _this=this;
							if(editor.config.mw){
								if(editor.config.mw.placeholderman){
									editor.config.mw.placeholderman.onShowHtml(editor,_this);
								}
							}

						
						},

						
					},
					
					{
						id: 'btnsel',
						type: 'button',
						
						label: 'adv',
					},
					
				]
			}
		]
	};
} );
