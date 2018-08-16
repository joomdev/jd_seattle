(function($){
	$.G2.tinymce = {};
	
	$.G2.tinymce.remove = function(selector){
		if(selector == undefined){
			selector = 'textarea[data-editor="1"]';
		}
		tinymce.remove(selector);
	}
	
	$.G2.tinymce.init = function(selector){
		$.G2.tinymce.remove();
		var tinymceSettings = {
			//selector: $(textarea),
			//target: textarea,
			width: '100%',
			height: 200,
			theme: 'modern',
			plugins: [
			'advlist autolink lists link image charmap print preview hr anchor pagebreak',
			'searchreplace wordcount visualblocks visualchars code fullscreen',
			'media save table directionality',
			'emoticons template paste textcolor colorpicker textpattern imagetools codesample'
			],
			toolbar1: 'fullscreen code visualblocks | undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify',
			toolbar2: 'bullist numlist outdent indent | link image media | forecolor backcolor | hr | removeformat | preview',
			image_advtab: true,
			visualblocks_default_state: true,
			menu : {},
			relative_urls: false,
			//document_base_url : "http://www.example.com/path1/",
			remove_script_host: false,
			convert_urls: false,
			//link_context_toolbar: true,
			//link_assume_external_targets: true,
			protect: [
				/\<\/?(if|endif)\>/g,  // Protect <if> & </endif>
				/<\?php.*?\?>/g  // Protect php code
			],
			content_css: [
				//'//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
				//'//www.tinymce.com/css/codepen.min.css'
			]
			
		};
		
		if(selector == undefined){
			selector = 'textarea[data-editor="1"]';
		}
		
		$(selector).each(function(i, textarea){
			tinymceSettings['target'] = textarea;
			
			if($(textarea).data('eheight')){
				tinymceSettings['height'] = $(textarea).data('eheight');
			}
			if($(textarea).data('ewidth')){
				tinymceSettings['width'] = $(textarea).data('ewidth');
			}
			
			var newTD = '<td class="colorable" valign="top"></td>';
			var newTR = '<tr width="100%" style="height:100px;" class="main-row"><td class="main-cell colorable" width="100%" valign="top"></td></tr>';
			
			if($(textarea).data('editormode') == 'email'){
				tinymceSettings['toolbar3'] = 'mailBox | addRowBefore addRowAfter removeRow | columns addColumnBefore addColumnAfter removeColumn | mailButton | cellColor';
				tinymceSettings['setup'] = function (editor) {
					/*editor.on('init', function(e){
						//editor.setContent('<span>some</span> html', {format: 'raw'});
						editor.setContent('<table width="100%" style="overflow-x:auto;max-width:600px;" cellpadding="0" cellspacing="0" border="0" class="main-table"><tr width="100%" style="height:100px;" class="main-row"><td width="100%" valign="top" class="main-cell colorable"></td></tr></table>');
					});*/
					
					editor.addButton('mailBox', {
						text: '',
						tooltip: 'Insert a mail page layout',
						icon: 'm icon mail',
						onclick: function () {
							var node = editor.selection.getNode();
							if(node.nodeName == 'P'){
								var target = $(node);
							}else if($(node).closest('p').length > 0){
								var target = $(node).closest('p');
							}else{
								var target = false;
							}
							
							if(target != false){
								target.replaceWith('<table class="container-table" width="100%" height="100%" style="height:100%; min-width:348px;" border="0" cellspacing="0" cellpadding="0"><tbody><tr align="center"><td><table width="100%" style="overflow-x:auto; max-width:600px; min-width:332px;" cellpadding="0" cellspacing="0" border="0" class="main-table mce-item-table"><tbody><tr width="100%" style="height:100px;" class="main-row"><td width="100%" valign="top" class="main-cell colorable"></td></tr></tbody></table></td></tr></tbody></table>');
							}
							
						},
						onpostrender: function (){
							var btn = this;
							editor.on('NodeChange', function(e){
								if(e.element.nodeName == 'P' || $(e.element).closest('p').length > 0){
									btn.disabled(false);
								}else{
									btn.disabled(true);
								}
							});
						}
					});
					
					editor.addButton('mailButton', {
						text: '',
						tooltip: 'Insert a formatted link button',
						icon: 'm icon external',
						onclick: function () {
							var node = editor.selection.getNode();
							if(node.nodeName == 'TD'){
								var target = $(node);
							}else if($(node).closest('td').length > 0){
								var target = $(node).closest('td');
							}else{
								var target = false;
							}
							//if(target != false){
								$(node).append('<a class="colorable" rel="nofollow" target="_blank" href="#" style="display:inline-block;text-align:center;text-decoration:none;min-height:36px;line-height:36px;padding-left:8px;padding-right:8px;min-width:88px;font-weight:400;color:#ffffff;background-color:#000;border-radius:2px;border-width:0px;">Button</a>');
							//}
						},
						onpostrender: function (){
							var btn = this;
							editor.on('NodeChange', function(e){
								if(e.element.nodeName == 'TD' || $(e.element).closest('td').length > 0){
									btn.disabled(false);
								}else{
									btn.disabled(true);
								}
							});
						}
					});
					
					editor.addButton('addRowBefore', {
						text: '',
						tooltip: 'Add row before active one',
						icon: 'tableinsertrowbefore',
						onclick: function () {
							var node = editor.selection.getNode();
							if($(node).closest('table.main-table').length > 0){
								$(node).closest('tr.main-row').before(newTR);
							}
						},
						onpostrender: function (){
							var btn = this;
							editor.on('NodeChange', function(e){
								if($(e.element).closest('table.main-table').length > 0){
									btn.disabled(false);
								}else{
									btn.disabled(true);
								}
							});
						}
					});
					
					editor.addButton('addRowAfter', {
						text: '',
						tooltip: 'Add row after active one',
						icon: 'tableinsertrowafter',
						onclick: function () {
							var node = editor.selection.getNode();
							if($(node).closest('table.main-table').length > 0){
								$(node).closest('tr.main-row').after(newTR);
							}
						},
						onpostrender: function (){
							var btn = this;
							editor.on('NodeChange', function(e){
								if($(e.element).closest('table.main-table').length > 0){
									btn.disabled(false);
								}else{
									btn.disabled(true);
								}
							});
						}
					});
					
					editor.addButton('removeRow', {
						text: '',
						tooltip: 'Remove active row',
						icon: 'tabledeleterow',
						onclick: function () {
							var node = editor.selection.getNode();
							if($(node).closest('table.main-table').find('tr.main-row').length > 1){
								var MainTable = $(node).closest('table.main-table');
								$(node).closest('tr.main-row').remove();
								editor.selection.setCursorLocation(MainTable.find('td').first().get(0));
							}
						},
						onpostrender: function () {
							var btn = this;
							editor.on('NodeChange', function(e){
								if($(e.element).closest('table.main-table').length > 0 && $(e.element).closest('table.main-table').find('tr.main-row').length > 1){
									btn.disabled(false);
								}else{
									btn.disabled(true);
								}
							});
						}
					});
					
					editor.addButton('columns', {
						text: '',
						tooltip: 'Split into two columns',
						icon: 'e icon columns',
						onclick: function () {
							var node = editor.selection.getNode();
							if($(node).closest('td').length > 0){
								$(node).closest('td').html('<table width="100%" style="height:100%; width:100%; max-width:600px;" cellpadding="0" cellspacing="0" border="0" class="columns"><tbody><tr width="100%"><td class="colorable" width="50%" valign="top"></td><td class="colorable" width="50%" valign="top"></td></tr></tbody></table>');
								editor.selection.setCursorLocation($(node).closest('td').find('table').find('td').first().get(0));
							}
						},
						onpostrender: function () {
							var btn = this;
							editor.on('NodeChange', function(e) {
								if($(e.element).closest('td').length > 0){
									btn.disabled(false);
								}else{
									btn.disabled(true);
								}
							});
						}
					});
					
					editor.addButton('addColumnBefore', {
						text: '',
						tooltip: 'Add column before active one',
						icon: 'tableinsertcolbefore',
						onclick: function () {
							var node = editor.selection.getNode();
							if($(node).closest('tr').not('.main-row').length > 0){
								$(node).closest('td').before(newTD);
								$(node).closest('tr').children('td').attr('width', (100 / $(node).closest('tr').children('td').length) + '%');
							}
						},
						onpostrender: function () {
							var btn = this;
							editor.on('NodeChange', function(e) {
								if($(e.element).closest('tr').not('.main-row').length > 0){
									btn.disabled(false);
								}else{
									btn.disabled(true);
								}
							});
						}
					});
					
					editor.addButton('addColumnAfter', {
						text: '',
						tooltip: 'Add column after active one',
						icon: 'tableinsertcolafter',
						onclick: function () {
							var node = editor.selection.getNode();
							if($(node).closest('tr').not('.main-row').length > 0){
								$(node).closest('td').after(newTD);
								$(node).closest('tr').children('td').attr('width', (100 / $(node).closest('tr').children('td').length) + '%');
							}
						},
						onpostrender: function () {
							var btn = this;
							editor.on('NodeChange', function(e) {
								if($(e.element).closest('tr').not('.main-row').length > 0){
									btn.disabled(false);
								}else{
									btn.disabled(true);
								}
							});
						}
					});
					
					editor.addButton('removeColumn', {
						text: '',
						tooltip: 'Remove active column',
						icon: 'tabledeletecol',
						onclick: function () {
							var node = editor.selection.getNode();
							if($(node).closest('tr').not('.main-row').length > 0){
								var TR = $(node).closest('tr');
								if(TR.find('td').length == 1){
									var MainRow = $(node).closest('tr.main-row');
									$(node).closest('table').remove();
									editor.selection.setCursorLocation(MainRow.find('td').first().get(0));
								}else{
									$(node).closest('td').remove();
									TR.children('td').attr('width', (100 / TR.children('td').length) + '%');
									editor.selection.setCursorLocation(TR.children('td').first().get(0));
								}
							}
						},
						onpostrender: function () {
							var btn = this;
							editor.on('NodeChange', function(e) {
								if($(e.element).closest('tr').not('.main-row').length > 0){
									btn.disabled(false);
								}else{
									btn.disabled(true);
								}
							});
						}
					});
					
					var rows = {'bgcolor' : 5};
					var cols = {'bgcolor' : 8};
					
					function applyFormat(format, value) {
						editor.focus();
						var node = editor.selection.getNode();
						if($(node).closest('.colorable').length > 0){
							$(node).closest('.colorable').css('background-color', value);
							editor.nodeChanged();
						}
					}

					function removeFormat(format) {
						editor.focus();
						var node = editor.selection.getNode();
						if($(node).closest('.colorable').length > 0){
							$(node).closest('.colorable').css('background-color', 'transparent');
							editor.nodeChanged();
						}
					}
					
					editor.addButton('cellColor', {
						
						type: 'colorbutton',
						tooltip: 'Background color',
						icon: 'm icon paint brush',
						//format: 'hilitecolor',
						panel: {
							origin: 'backgroundcolor',
							role: 'application',
							ariaRemember: true,
							html: function(){
								var self = this, colors, color, html, last, x, y, i, id = self._id, count = 0, type;

								type = 'bgcolor';
								//var rows = {'bgcolor' : 5};
								//var cols = {'bgcolor' : 8};

								function mapColors(type) {
									var i, colors = [], colorMap;

									colorMap = [
										"000000", "Black",
										"993300", "Burnt orange",
										"333300", "Dark olive",
										"003300", "Dark green",
										"003366", "Dark azure",
										"000080", "Navy Blue",
										"333399", "Indigo",
										"333333", "Very dark gray",
										"800000", "Maroon",
										"FF6600", "Orange",
										"808000", "Olive",
										"008000", "Green",
										"008080", "Teal",
										"0000FF", "Blue",
										"666699", "Grayish blue",
										"808080", "Gray",
										"FF0000", "Red",
										"FF9900", "Amber",
										"99CC00", "Yellow green",
										"339966", "Sea green",
										"33CCCC", "Turquoise",
										"3366FF", "Royal blue",
										"800080", "Purple",
										"999999", "Medium gray",
										"FF00FF", "Magenta",
										"FFCC00", "Gold",
										"FFFF00", "Yellow",
										"00FF00", "Lime",
										"00FFFF", "Aqua",
										"00CCFF", "Sky blue",
										"993366", "Red violet",
										"FFFFFF", "White",
										"FF99CC", "Pink",
										"FFCC99", "Peach",
										"FFFF99", "Light yellow",
										"CCFFCC", "Pale green",
										"CCFFFF", "Pale cyan",
										"99CCFF", "Light sky blue",
										"CC99FF", "Plum"
									];

									colorMap = editor.settings.textcolor_map || colorMap;
									colorMap = editor.settings[type + '_map'] || colorMap;

									for (i = 0; i < colorMap.length; i += 2) {
										colors.push({
											text: colorMap[i + 1],
											color: '#' + colorMap[i]
										});
									}

									return colors;
								}

								function getColorCellHtml(color, title) {
									var isNoColor = color == 'transparent';

									return (
									'<td class="mce-grid-cell' + (isNoColor ? ' mce-colorbtn-trans' : '') + '">' +
									'<div id="' + id + '-' + (count++) + '"' +
									' data-mce-color="' + (color ? color : '') + '"' +
									' role="option"' +
									' tabIndex="-1"' +
									' style="' + (color ? 'background-color: ' + color : '') + '"' +
									' title="' + tinymce.translate(title) + '">' +
									(isNoColor ? '&#215;' : '') +
									'</div>' +
									'</td>'
									);
								}

								colors = mapColors(type);
								colors.push({
									text: tinymce.translate("No color"),
									color: "transparent"
								});

								html = '<table class="mce-grid mce-grid-border mce-colorbutton-grid" role="list" cellspacing="0"><tbody>';
								last = colors.length - 1;

								for (y = 0; y < rows[type]; y++) {
									html += '<tr>';

									for (x = 0; x < cols[type]; x++) {
										i = y * cols[type] + x;

										if (i > last) {
											html += '<td></td>';
										} else {
											color = colors[i];
											html += getColorCellHtml(color.color, color.text);
										}
									}

									html += '</tr>';
								}

								if (editor.settings.color_picker_callback) {
									html += (
									'<tr>' +
									'<td colspan="' + cols[type] + '" class="mce-custom-color-btn">' +
									'<div id="' + id + '-c" class="mce-widget mce-btn mce-btn-small mce-btn-flat" ' +
									'role="button" tabindex="-1" aria-labelledby="' + id + '-c" style="width: 100%">' +
									'<button type="button" role="presentation" tabindex="-1">' + tinymce.translate('Custom...') + '</button>' +
									'</div>' +
									'</td>' +
									'</tr>'
									);

									html += '<tr>';

									for (x = 0; x < cols[type]; x++) {
										html += getColorCellHtml('', 'Custom color');
									}

									html += '</tr>';
								}

								html += '</tbody></table>';

								return html;
							},
							onclick: function(e){
								var buttonCtrl = this.parent(), value, type;

								type = buttonCtrl.settings.origin;
								
								function getCurrentColor(format) {
									var color;

									editor.dom.getParents(editor.selection.getStart(), function (elm) {
										var value;
										if ((value = elm.style[format == 'forecolor' ? 'color' : 'background-color'])) {
											color = value;
										}
									});

									return color;
								}
								
								function selectColor(value) {
									buttonCtrl.hidePanel();
									buttonCtrl.color(value);
									applyFormat(buttonCtrl.settings.format, value);
								}

								function resetColor() {
									buttonCtrl.hidePanel();
									buttonCtrl.resetColor();
									removeFormat(buttonCtrl.settings.format);
								}

								function setDivColor(div, value) {
									div.style.background = value;
									div.setAttribute('data-mce-color', value);
								}

								if (tinymce.DOM.getParent(e.target, '.mce-custom-color-btn')) {
									buttonCtrl.hidePanel();

									editor.settings.color_picker_callback.call(editor, function (value) {
										var tableElm = buttonCtrl.panel.getEl().getElementsByTagName('table')[0];
										var customColorCells, div, i;

										customColorCells = tinymce.map(tableElm.rows[tableElm.rows.length - 1].childNodes, function (elm) {
											return elm.firstChild;
										});

										for (i = 0; i < customColorCells.length; i++) {
											div = customColorCells[i];
											if (!div.getAttribute('data-mce-color')) {
												break;
											}
										}

										// Shift colors to the right
										// TODO: Might need to be the left on RTL
										if (i == cols[type]) {
											for (i = 0; i < cols[type] - 1; i++) {
												setDivColor(customColorCells[i], customColorCells[i + 1].getAttribute('data-mce-color'));
											}
										}

										setDivColor(div, value);
										selectColor(value);
									}, getCurrentColor(buttonCtrl.settings.format));
								}

								value = e.target.getAttribute('data-mce-color');
								if (value) {
									if (this.lastId) {
										document.getElementById(this.lastId).setAttribute('aria-selected', false);
									}

									e.target.setAttribute('aria-selected', true);
									this.lastId = e.target.id;

									if (value == 'transparent') {
										resetColor();
									} else {
										selectColor(value);
									}
								} else if (value !== null) {
									buttonCtrl.hidePanel();
								}
							}
						},
						onclick: function(){
							var self = this;
							if (self._color) {
								applyFormat(self.settings.format, self._color);
							} else {
								removeFormat(self.settings.format);
							}
						}
					});
					
					editor.addButton('Borders',{
						title: 'Borders',
						text: 'Borders',
						onclick: function(){
							var node = editor.selection.getNode();
							if($(node).closest('td.main-cell').length > 0){
								var cell = $(node).closest('td.main-cell');
								
								console.log(cell.get(0).style['border-top-width']);
								
								editor.windowManager.open({
									title: 'Test Widgets',
									body: [
										{
											type   : 'textbox',
											name   : 'bordertop',
											label  : 'Border top',
											tooltip: 'Top border css',
											value  : cell.get(0).style['border-top-width'] +' '+ cell.get(0).style['border-top-style'] +' '+ cell.get(0).style['border-top-color']
										},
										{
											type   : 'textbox',
											name   : 'borderright',
											label  : 'Border right',
											tooltip: 'Right border css',
											value  : cell.css('border-right-width') +' '+ cell.css('border-right-style') +' '+ cell.css('border-right-color')
										},
										{
											type   : 'textbox',
											name   : 'borderbottom',
											label  : 'Border bottom',
											tooltip: 'Bottom border css',
											value  : cell.css('border-bottom-width') +' '+ cell.css('border-bottom-style') +' '+ cell.css('border-bottom-color')
										},
										{
											type   : 'textbox',
											name   : 'borderleft',
											label  : 'Border left',
											tooltip: 'Left border css',
											value  : cell.css('border-left-width') +' '+ cell.css('border-left-style') +' '+ cell.css('border-left-color')
										},
									],
									onsubmit: function(e){
										console.log(e.data);
										cell.css('border-top', e.data.bordertop);
										cell.css('border-right', e.data.borderright);
										cell.css('border-bottom', e.data.borderbottom);
										cell.css('border-left', e.data.borderleft);
									}
								});
							}
						},
						onpostrender: function (){
							var btn = this;
							editor.on('NodeChange', function(e){
								if($(e.element).closest('td.main-cell').length > 0){
									btn.disabled(false);
								}else{
									btn.disabled(true);
								}
							});
						},
						onsubmit: function(e){
							console.log(e);
						}
					});
				};
			}
			tinymce.init(tinymceSettings);
		});
	};
	
}(jQuery));