
( function() {

	'use strict';

	var containerTpl = CKEDITOR.addTemplate( 'sharedcontainer', '<div' +
		' id="cke_{name}"' +
		' class="cke {id} cke_reset_all cke_chrome cke_editor_{name} cke_shared cke_detached cke_{langDir} ' + CKEDITOR.env.cssClass + '"' +
		' dir="{langDir}"' +
		' title="' + ( CKEDITOR.env.gecko ? ' ' : '' ) + '"' +
		' lang="{langCode}"' +
		' role="presentation"' +
		'>' +
			'<div class="cke_inner">' +
				'<div id="{spaceId}" class="cke_{space}" role="presentation">{content}</div>' +
			'</div>' +
		'</div>' );

	CKEDITOR.plugins.add( 'sharedspace', {
		init: function( editor ) {
			editor.on( 'loaded', function() {
				var spaces = editor.config.sharedSpaces;

				if ( spaces ) {
					for ( var spaceName in spaces )
						create( editor, spaceName, spaces[ spaceName ] );
				}
			}, null, null, 9 );
		}
	} );

	function create( editor, spaceName, target ) {
		var innerHtml, space;

		if ( typeof target == 'string' ) {
			target = CKEDITOR.document.getById( target );
		} else {
			target = new CKEDITOR.dom.element( target );
		}

		if ( target ) {
			innerHtml = editor.fire( 'uiSpace', { space: spaceName, html: '' } ).html;

			if ( innerHtml ) {
				editor.on( 'uiSpace', function( ev ) {
					if ( ev.data.space == spaceName )
						ev.cancel();
				}, null, null, 1 );  // Hi-priority

				space = target.append( CKEDITOR.dom.element.createFromHtml( containerTpl.output( {
					id: editor.id,
					name: editor.name,
					langDir: editor.lang.dir,
					langCode: editor.langCode,
					space: spaceName,
					spaceId: editor.ui.spaceId( spaceName ),
					content: innerHtml
				} ) ) );

				if ( target.getCustomData( 'cke_hasshared' ) )
					space.hide();
				else
					target.setCustomData( 'cke_hasshared', 1 );

				space.unselectable();

				space.on( 'mousedown', function( evt ) {
					evt = evt.data;
					if ( !evt.getTarget().hasAscendant( 'a', 1 ) )
						evt.preventDefault();
				} );

				editor.focusManager.add( space, 1 );

				editor.on( 'focus', function() {
					for ( var i = 0, sibling, children = target.getChildren(); ( sibling = children.getItem( i ) ); i++ ) {
						if ( sibling.type == CKEDITOR.NODE_ELEMENT &&
							!sibling.equals( space ) &&
							sibling.hasClass( 'cke_shared' ) ) {
							sibling.hide();
						}
					}

					space.show();
				} );

				editor.on( 'destroy', function() {
					space.remove();
				} );
			}
		}
	}
} )();


