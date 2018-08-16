<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	ob_start();
?>
<script>
	//jQuery('form').addClass('loading');
	jQuery(document).ready(function($) {
		$('body').on('click', '.remove_btn', function(){
			$(this).closest('.element-area').remove();
		});
		
		$('body').on('click', '.edit_btn', function(){
			$(this).closest('.element-area').find('.ui.settings').first().transition('slide down');
		});
		
		$('body').on('dragndrop.make.draggable', '.draggable', function(e){
			$(this).draggable({
				'helper' : 'clone',
				connectToSortable: '.droppable',
				start: function(e, ui){
					$(ui.helper).css('z-index', 9999);
					$(ui.helper).css('min-width', '200px');
				}
			});
		});
		
		$('body').find('.draggable').trigger('dragndrop.make.draggable');
		
		$('body').on('dragndrop.make.droppable', '.droppable', initDroppable);
		$('body').find('.droppable').trigger('dragndrop.make.droppable');
		
		function initDroppable(e){
			e.stopPropagation();
			$(this).sortable({
				connectWith: '.droppable',
				scroll: false,
				handle: '.sort_btn',
				placeholder: 'ui segment inverted yellow',
				start: function( event, ui ) {
					
				},
				sort: function( event, ui ) {
					$(ui.item).addClass('active_sortable');
				},
				receive: function( event, ui ) {
					if($(ui.helper).data('info')){
						$(ui.helper).css('width', '');
						$(ui.helper).find('.segment').addClass('fluid loading');
						drop($(ui.helper), $(this));
					}
					$(ui.item).find('.dragged_parent').first().val($(this).data('name'));
				},
				update: function( event, ui ) {
				
				},
				stop: function( event, ui ) {
					$(ui.item).removeClass('active_sortable');
				},
				over: function( event, ui ) {
					$(this).addClass('active_droppable');
				},
				out: function( event, ui ) {
					$(this).removeClass('active_droppable');
				},
				tolerance: 'pointer'
			});
		}
		
		function drop(draggable, droppable){
			var blockType = draggable.data('type');
			var dropInfo = draggable.data('info');
			var type = dropInfo.name;
			
			var counter = $(droppable).closest('.page-area').find('.page-counter').first();
			
			$(counter).val(parseInt($(counter).val()) + 1);
			
			$.ajax({
				url: draggable.data('url'),
				data: {'block' : blockType, 'name' : type, 'n' : $(counter).val(), 'pn': $(droppable).closest('.page-area').data('id')},
				success: function(result){
					var newFunc = $(result);
					
					draggable.replaceWith(newFunc);
					$(newFunc).find('.droppable').trigger('dragndrop.make.droppable');
					//set the parent event value
					newFunc.find('.dragged_parent').first().val(droppable.data('name'));
					
					newFunc.find('.page_id').first().val(droppable.closest('.page-area').data('id'));
					newFunc.find('.parent_id').first().val(droppable.closest('.element-area').data('id'));
					
					if(droppable.is('.echild-area')){
						newFunc.find('.sub_parent_id').first().val(droppable.data('id'));
					}
					
					newFunc.trigger('contentChange.basics');
				}
			});
		}
	});
</script>
<?php
	$jscode = ob_get_clean();
	\GApp::document()->addHeaderTag($jscode);
	\GApp::document()->_('jquery-ui');
	\GApp::document()->__('keepalive');
	\GApp::document()->_('semantic-ui', ['css' => ['accordion', 'transition']]);
	\GApp::document()->_('tinymce');
?>
<form action="<?php echo r2('index.php?ext=chronoforms&cont=connections'); ?>" method="post" name="admin_form" id="admin_form" class="ui form">

	<h2 class="ui header"><?php echo !empty($this->data['Connection']['title']) ? $this->data['Connection']['title'] : rl('New form'); ?></h2>
	<div class="ui">
		<button type="button" class="ui button compact green icon labeled toolbar-button" data-fnx="saveform" name="save" data-url="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=edit2'); ?>">
			<i class="check icon"></i><?php el('Save'); ?>
		</button>
		<button type="button" class="ui button compact blue icon labeled toolbar-button" data-fn="saveform" name="apply" data-url="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=edit&easy=1'); ?>">
			<i class="check icon"></i><?php el('Apply'); ?>
		</button>
		<a class="ui button compact red icon labeled toolbar-button" href="<?php echo r2('index.php?ext=chronoforms&cont=connections'); ?>">
			<i class="cancel icon"></i><?php el('Cancel'); ?>
		</a>
		<!--
		<div class="ui button compact red basic icon right floated" id="inputs_counter" data-position="left center" data-hint="<?php el('The number of config inputs in your form, this should NOT exceed the max vars number above, which is your server upper limit.'); ?>"></div>
		-->
		<a class="ui button compact blue inverted active icon labeled toolbar-button right floated <?php if(empty($this->data['Connection']['alias'])): ?>disabled<?php endif; ?>" target="_blank" href="<?php echo r2('index.php?ext=chronoforms&cont=manager&chronoform='.$this->data['Connection']['alias']); ?>">
			<i class="tv icon"></i><?php el('Preview'); ?>
		</a>
	</div>
	
	<div class="ui clearing divider"></div>
	
	<div class="ui top attached ordered tiny steps G2-tabs">
		<a class="step active" data-tab="general">
			<div class="content"><div class="title"><?php el('General'); ?></div><div class="description"><?php el('Enter form name'); ?></div></div>
		</a>
		<a class="step" data-tab="sections">
			<div class="content"><div class="title"><?php el('Designer'); ?></div><div class="description"><?php el('Add some fields'); ?></div></div>
		</a>
		<a class="step" data-tab="events">
			<div class="content"><div class="title"><?php el('Setup'); ?></div><div class="description"><?php el('Enable form features'); ?></div></div>
		</a>
	</div>
	
	<div class="ui bottom attached tab segment active" data-tab="general">
		<input type="hidden" name="Connection[id]" value="">
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Title'); ?></label>
				<input type="text" placeholder="<?php el('Title'); ?>" name="Connection[title]">
				<small><?php el('Any title for your form, it will be used to generate the alias'); ?></small>
			</div>
			<div class="field easy_disabled">
				<label><?php el('Alias'); ?></label>
				<input type="text" placeholder="<?php el('Alias'); ?>" name="Connection[alias]">
				<small style="color:red;"><?php el('Use this alias to call your form in URLs or shortcodes.'); ?></small>
			</div>
		</div>
		
		<div class="two fields easy_disabled">
			<div class="field">
				<div class="ui checkbox">
					<input type="hidden" name="Connection[published]" data-ghost="1" value="">
					<input type="checkbox" checked="checked" class="hidden" name="Connection[published]" value="1">
					<label><?php el('Published'); ?></label>
				</div>
			</div>
			<div class="field">
				<div class="ui checkbox">
					<input type="hidden" name="Connection[public]" data-ghost="1" value="">
					<input type="checkbox" checked="checked" class="hidden" name="Connection[public]" value="1">
					<label><?php el('Public'); ?></label>
				</div>
			</div>
		</div>
		
		<div class="field">
			<label><?php el('Description'); ?></label>
			<textarea placeholder="<?php el('Description'); ?>" name="Connection[description]" id="conndesc" rows="5"></textarea>
			<small><?php el('Descriptive text for your form.'); ?></small>
		</div>
		
		<div class="two fields">
			<div class="field easy_disabled">
				<label><?php el('Default event'); ?></label>
				<input type="text" value="load" name="Connection[params][default_event]">
			</div>
			<div class="field">
				<label><?php el('Designer mode'); ?></label>
				<select name="Connection[params][mode]" class="ui fluid dropdown">
					<option value="advanced"><?php el('Advanced mode'); ?></option>
					<option value="easy"><?php el('Easy mode'); ?></option>
				</select>
			</div>
		</div>
		<input type="hidden" name="Connection[params][limited_edition]" value="1">
	</div>
	
	
	<div class="ui bottom attached tab segment" data-tab="events">
		<div class="ui top attached tabular menu G2-tabs">
			<?php foreach($this->data('Pages', []) as $pn => $page): ?>
				<a class="item" data-tab="page-<?php echo $page['Page']['name']; ?>">
					<div class="content">
						<div class="title"><?php echo !empty($page['Page']['title']) ? $page['Page']['title'] : $page['Page']['name']; ?></div>
						<?php if(!empty($page['Page']['desc'])): ?>
						<div class="description"><?php echo $page['Page']['desc']; ?></div>
						<?php endif; ?>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
		<?php foreach($this->data('Pages', []) as $pn => $page): ?>
			<div class="ui bottom attached tab segment page-area" data-id="<?php echo $pn; ?>" data-tab="page-<?php echo $page['Page']['name']; ?>">
				<?php
					$page_counter = !empty($page['Actions']) ? max(array_keys($page['Actions'])) : 0;
				?>
				<input type="hidden" value="<?php echo $page_counter; ?>" class="page-counter" name="page-counter">
				
				<div class="ui top attached tiny steps G2-tabs">
					<a class="step active" data-tab="page-<?php echo $page['Page']['name']; ?>-layout">
						<div class="content">
							<div class="title"><?php el('Layout'); ?></div>
							<div class="description"><?php el('Page output'); ?></div>
						</div>
					</a>
					<a class="step" data-tab="page-<?php echo $page['Page']['name']; ?>-actions">
						<div class="content">
							<div class="title"><?php el('Actions'); ?></div>
							<div class="description"><?php el('Page actions'); ?></div>
						</div>
					</a>
					<a class="step" data-tab="page-<?php echo $page['Page']['name']; ?>-settings">
						<div class="content">
							<div class="title"><?php el('Settings'); ?></div>
							<div class="description"><?php el('Page settings'); ?></div>
						</div>
					</a>
				</div>
				
				<div class="ui bottom attached tab segment active" data-tab="page-<?php echo $page['Page']['name']; ?>-layout">
					<?php $this->view('views.connections.61.layout'); ?>
				</div>
				
				<div class="ui bottom attached tab segment" data-tab="page-<?php echo $page['Page']['name']; ?>-actions">
					<?php $this->view('views.connections.61.actions', ['pn' => $pn, 'page' => $page]); ?>
				</div>
				
				<div class="ui bottom attached tab segment" data-tab="page-<?php echo $page['Page']['name']; ?>-settings">
					<?php $this->view('views.connections.61.settings', ['page' => $page, 'pn' => $pn]); ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	
	
</form>
