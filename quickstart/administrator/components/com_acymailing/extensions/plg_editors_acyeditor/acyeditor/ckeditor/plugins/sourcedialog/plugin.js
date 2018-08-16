
CKEDITOR.plugins.add( 'sourcedialog', {
	lang: 'en', // %REMOVE_LINE_CORE%
	icons: 'sourcedialog,sourcedialog-rtl', // %REMOVE_LINE_CORE%
	hidpi: true, // %REMOVE_LINE_CORE%

	init: function( editor ) {
		editor.addCommand( 'sourcedialog', new CKEDITOR.dialogCommand( 'sourcedialog' ) );

		CKEDITOR.dialog.add( 'sourcedialog', this.path + 'dialogs/sourcedialog.js' );

		if ( editor.ui.addButton ) {
			editor.ui.addButton( 'Sourcedialog', {
				label: editor.lang.sourcedialog.toolbar,
				command: 'sourcedialog',
				icon: this.path.split("/plugins/")[0] + "/media/com_acymailing/images/editor/sourcedialog.png",
				toolbar: 'mode,10'
			} );
		}
	}
} );

