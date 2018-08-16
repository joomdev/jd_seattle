
CKEDITOR.editorConfig = function( config ) {

	config.toolbarGroups = [
		{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
		{ name: 'links' },
		{ name: 'insert' },
		{ name: 'forms' },
		{ name: 'tools' },
		{ name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'others' },
		'/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
		{ name: 'styles' },
		{ name: 'colors' },
		{ name: 'about' }
	];

	config.removeButtons = 'Underline,Subscript,Superscript';


	config.removeDialogTabs = 'image:advanced;link:advanced';

	//-----------------------//
	config.startupFocus = false;
	config.fillEmptyBlocks = false;
	config.filebrowserBrowseUrl = '';
	config.filebrowserImageBrowseUrl = '';
	config.filebrowserFlashBrowseUrl = '';
	config.filebrowserUploadUrl = '';
	config.filebrowserImageUploadUrl = '';
	config.filebrowserFlashUploadUrl = '';
	config.allowedContent = true;
	config.disableNativeSpellChecker = false;
	config.stylesSet = [];
	config.entities_greek = false;
};

