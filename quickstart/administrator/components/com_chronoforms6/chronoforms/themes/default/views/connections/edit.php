<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php $this->view(\G2\Globals::ext_path('chronofc', 'admin').DS.'themes'.DS.'default'.DS.'views'.DS.'designer.php'); ?>
<form action="<?php echo r2('index.php?ext=chronoforms&cont=connections'); ?>" method="post" name="admin_form" id="admin_form" class="ui form">

	<h2 class="ui header"><?php echo !empty($this->data['Connection']['title']) ? $this->data['Connection']['title'] : rl('New form'); ?></h2>
	<div class="ui sticky white segment" style="z-index:9999;">
		<button type="button" class="ui button compact green icon labeled toolbar-button" data-fn="saveform" name="save" data-url="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=edit'); ?>">
			<i class="check icon"></i><?php el('Save & Close'); ?>
		</button>
		<?php if(!empty($this->data['Connection']['id'])): ?>
		<button type="button" class="ui button compact blue icon labeled" data-fn="saveform" name="apply" id="apply" data-url="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=edit&tvout=view&apply=1'); ?>">
			<i class="check icon"></i><?php el('Save'); ?>
		</button>
		<?php else: ?>
		<button type="button" class="ui button compact blue icon labeled toolbar-button" data-fn="saveform" name="apply" data-url="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=edit&apply=1'); ?>">
			<i class="save icon"></i><?php el('Save'); ?>
		</button>
		<?php endif; ?>
		<a class="ui button compact red icon labeled toolbar-button" href="<?php echo r2('index.php?ext=chronoforms&cont=connections'); ?>">
			<i class="cancel icon"></i><?php el('Cancel'); ?>
		</a>
		<!--
		<div class="ui button compact red basic icon right floated" id="inputs_counter" data-position="left center" data-hint="<?php el('The number of config inputs in your form, this should NOT exceed the max vars number above, which is your server upper limit.'); ?>"></div>
		-->
		<a class="ui button compact blue inverted active icon labeled toolbar-button right floated <?php if(empty($this->data['Connection']['alias'])): ?>disabled<?php endif; ?>" target="_blank" href="<?php echo r2('index.php?ext=chronoforms&cont=manager&chronoform='.$this->data['Connection']['alias']); ?>">
			<i class="tv icon"></i><?php el('Preview'); ?>
		</a>
		<a class="ui button compact orange icon labeled toolbar-button right floated <?php if(empty($this->data['Connection']['id'])): ?>disabled<?php endif; ?>" href="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=backup&gcb[]='.$this->data['Connection']['id']); ?>">
			<i class="download icon"></i><?php el('Backup'); ?>
		</a>
		
	</div>
	
	<div class="ui top attached tiny steps G2-tabs">
		<a class="step active" data-tab="general">
			<i class="settings icon"></i>
			<div class="content"><div class="title"><?php el('Basics'); ?></div><div class="description"><?php el('Basic settings'); ?></div></div>
		</a>
		<a class="step" data-tab="sections">
			<i class="object group icon"></i>
			<div class="content"><div class="title"><?php el('Design'); ?></div><div class="description"><?php el('Build form interface'); ?></div></div>
		</a>
		<a class="step" data-tab="events">
			<i class="tasks icon"></i>
			<div class="content"><div class="title"><?php el('Setup'); ?></div><div class="description"><?php el('Select form functions'); ?></div></div>
		</a>
		<a class="step" data-tab="locales">
			<i class="translate icon"></i>
			<div class="content"><div class="title"><?php el('Translate'); ?></div><div class="description"><?php el('Optional - translate form content'); ?></div></div>
		</a>
		<?php if($this->get('permissions_deactivated', false) === false): ?>
		<a class="step" data-tab="permissions">
			<i class="key icon"></i>
			<div class="content"><div class="title"><?php el('Access control'); ?></div><div class="description"><?php el('Optional - groups permissions'); ?></div></div>
		</a>
		<?php endif; ?>
	</div>
	
	<div class="ui bottom attached tab segment active" data-tab="general">
		<input type="hidden" name="Connection[id]" value="">
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Title'); ?></label>
				<input type="text" placeholder="<?php el('Title'); ?>" name="Connection[title]">
			</div>
			<div class="field">
				<label><?php el('Alias'); ?></label>
				<input type="text" placeholder="<?php el('Alias'); ?>" name="Connection[alias]">
				<small style="color:red;"><?php el('Use this alias to call your form in URLs or shortcodes.'); ?></small>
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<div class="ui checkbox">
					<input type="hidden" name="Connection[published]" data-ghost="1" value="">
					<input type="checkbox" checked="checked" class="hidden" name="Connection[published]" value="1">
					<label><?php el('Published'); ?></label>
					<small><?php el('Enable or disable this form.'); ?></small>
				</div>
			</div>
			<div class="field">
				<div class="ui checkbox">
					<input type="hidden" name="Connection[public]" data-ghost="1" value="">
					<input type="checkbox" checked="checked" class="hidden" name="Connection[public]" value="1">
					<label><?php el('Public'); ?></label>
					<small><?php el('Enable frontend view of this form.'); ?></small>
				</div>
			</div>
		</div>
		
		<div class="field">
			<label><?php el('Description'); ?></label>
			<textarea placeholder="<?php el('Description'); ?>" name="Connection[description]" id="conndesc" rows="5"></textarea>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Designer mode'); ?></label>
				<select name="Connection[params][mode]" class="ui fluid dropdown">
					<option value="advanced"><?php el('Advanced mode'); ?></option>
					<option value="easy"><?php el('Easy mode'); ?></option>
				</select>
			</div>
		</div>
		
		<div class="field">
			<div class="ui checkbox">
				<input type="hidden" name="Connection[params][permissions_deactivated]" data-ghost="1" value="">
				<input type="checkbox" class="hidden" name="Connection[params][permissions_deactivated]" value="1">
				<label><?php el('Permissions deactivated'); ?></label>
				<small><?php el('Disable the permissions conrol across all the form configuration.'); ?></small>
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Default event'); ?></label>
				<input type="text" value="load" name="Connection[params][default_event]">
			</div>
			<div class="field">
				<label><?php el('Event not found'); ?></label>
				<input type="text" value="" name="Connection[params][event_not_found]">
				<small><?php el('Output displayed when the called form event does not exist.'); ?></small>
			</div>
		</div>
		<input type="hidden" name="Connection[params][limited_edition]" value="1">
	</div>
	
	<div class="ui bottom attached tab segment structures-list" data-tab="events" data-name="event">
		<div class="ui grid">
			<input type="hidden" value="<?php echo empty($this->data['Connection']['functions']) ? 0 : max(array_keys($this->data['Connection']['functions'])); ?>" id="functions-count" name="functions-count">
			
			<div class="four wide column scrollableBox">
				<?php
					//get views files
					$functions = \G2\L\Folder::getFolders(\G2\Globals::ext_path('chronofc', 'admin').'functions'.DS);
					$functions_info = [];
					foreach($functions as $function){
						$name = basename($function);
						$info_file = $function.DS.$name.'.ini';
						$info = parse_ini_file($info_file);
						if(!empty($info['apps'])){
							if(!in_array('forms', $info['apps'])){
								continue;
							}
						}
						if(!isset($info['color'])){
							$info['color'] = 'blue';
						}
						if(!empty($info['private']) AND !empty($public)){
							continue;
						}
						if(!empty($info['platform']) AND !in_array(\G2\Globals::get('app'), $info['platform'])){
							continue;
						}
						$functions_info[$name] = $info;
					}
					$types = ['core', 'more'];
					$functions_info2 = ['core' => $functions_info, 'more' => $blocks_functions];
					$functions_groups = ['core' => array_unique(array('Basic') + \G2\L\Arr::getVal($functions_info, '[n].group', [])), 'more' => array_unique(array('Default') + \G2\L\Arr::getVal($blocks_functions, '[n].group', []))];
					asort($functions_groups['core']);
				?>
				<div class="ui secondary pointing menu small G2-tabs">
					<a class="item active" data-tab="functionslist-core"><?php el('Core'); ?></a>
					<a class="item" data-tab="functionslist-more"><?php el('More'); ?></a>
				</div>
				
				<?php foreach($types as $kt => $type): ?>
				<div class="ui bottom attached tab <?php if($kt == 0): ?>active<?php endif; ?>" data-tab="functionslist-<?php echo $type; ?>">
					<div class="ui fluid accordion styled draggable-list">
						<?php foreach($functions_groups[$type] as $kfg => $functions_group): ?>
						<div class="title ui header small blue <?php if(empty($kfg)): ?> active<?php endif; ?>"><i class="dropdown icon"></i><?php echo $functions_group; ?></div>
						<div class="content<?php if(empty($kfg)): ?> active<?php endif; ?>">
							
							<div class="ui grid center aligned small functions-list">
								<?php foreach($functions_info2[$type] as $fn_name => $fn_info): ?>
									<?php if($fn_info['group'] == $functions_group): ?>
									<div class="eight wide large screen sixteen wide small screen column draggable" data-type="function" data-private="<?php echo !empty($fn_info['private']) ? 1 : 0; ?>" data-info='<?php echo json_encode($fn_info); ?>' style="padding:5px;" data-url="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=block_config&tvout=view'); ?>">
										<div class="ui segment tiny" style="padding:5px 1px;">
											<div class="ui header small">
											<?php if(!empty($fn_info['icon'])): ?>
											<i class="icon <?php echo $fn_info['icon']; ?> fitted"></i>
											<?php endif; ?>
											<?php echo $fn_info['title']; ?>
											</div>
										</div>
									</div>
									<?php endif; ?>
								<?php endforeach; ?>
							</div>
							
						</div>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endforeach; ?>
				
			</div>
			
			<div class="twelve wide column">
				<div class="ui container fluid event-data areas">
					<?php foreach($this->data['Connection']['events'] as $event_n => $event): ?>
						<?php $this->view('views.connections.events_config', ['name' => $event['name'], 'count' => $event_n, 'functions' => !empty($this->data['Connection']['functions']) ? $this->data['Connection']['functions'] : array()]); ?>
					<?php endforeach; ?>
				</div>
				
				<div class="ui form">
					<div class="ui action input fluid">
						<input type="text" placeholder="<?php el('Event name...'); ?>" class="event-name">
						<button type="button" class="ui button green compact disabled add-event" data-url="<?php echo r2('index.php?ext='.\GApp::instance()->extension.'&cont=connections&act=events_config&tvout=view'); ?>">
							<?php el('Add event'); ?>
						</button>
					</div>
				</div>
			</div>
			
		</div>
	</div>
	
	<div class="ui bottom attached tab segment structures-list" data-tab="sections" data-name="section">
		<div class="ui grid">
			<input type="hidden" value="<?php echo empty($this->data['Connection']['views']) ? 0 : max(array_keys($this->data['Connection']['views'])); ?>" id="views-count">
			
			<div class="four wide column scrollableBox">
				<?php
					//get views files
					$views = \G2\L\Folder::getFolders(\G2\Globals::ext_path('chronofc', 'admin').'views'.DS);
					$views_info = [];
					foreach($views as $view){
						$name = basename($view);
						$info_file = $view.DS.$name.'.ini';
						$info = parse_ini_file($info_file);
						if(!empty($info['apps'])){
							if(!in_array('forms', $info['apps'])){
								continue;
							}
						}
						$views_info[$name] = $info;
					}
					$types = ['core', 'more'];
					$views_info2 = ['core' => $views_info, 'more' => $blocks_views];
					$views_groups = ['core' => array_unique(array('Fields') + \G2\L\Arr::getVal($views_info, '[n].group', [])), 'more' => array_unique(array('Default') + \G2\L\Arr::getVal($blocks_views, '[n].group', []))];
				?>
				<div class="ui secondary pointing menu small G2-tabs">
					<a class="item active" data-tab="viewslist-core"><?php el('Core'); ?></a>
					<a class="item" data-tab="viewslist-more"><?php el('More'); ?></a>
				</div>
				
				<?php foreach($types as $kt => $type): ?>
				<div class="ui bottom attached tab <?php if($kt == 0): ?>active<?php endif; ?>" data-tab="viewslist-<?php echo $type; ?>">
					<div class="ui fluid accordion styled draggable-list">
						<?php foreach($views_groups[$type] as $kvg => $views_group): ?>
						<div class="title ui header small blue <?php if(empty($kvg)): ?> active<?php endif; ?>"><i class="dropdown icon"></i><?php echo $views_group; ?></div>
						<div class="content <?php if(empty($kvg)): ?> active<?php endif; ?>">
							
							<div class="ui grid center aligned small views-list">
								<?php foreach($views_info2[$type] as $vw_name => $vw_info): ?>
									<?php if($vw_info['group'] == $views_group): ?>
									<div class="eight wide large screen sixteen wide small screen column draggable" data-type="view" data-info='<?php echo json_encode($vw_info); ?>' style="padding:5px;" data-url="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=block_config&tvout=view'); ?>">
										<div class="ui segment tiny" style="padding:5px 1px;" <?php if(!empty($vw_info['desc'])): ?>data-hint="<?php echo nl2br($vw_info['desc']); ?>"<?php endif; ?>>
											<div class="ui header small">
												<?php if(!empty($vw_info['icon'])): ?>
												<i class="icon <?php echo $vw_info['icon']; ?> fitted"></i>
												<?php endif; ?>
												<?php echo $vw_info['title']; ?>
											</div>
										</div>
									</div>
									<?php endif; ?>
								<?php endforeach; ?>
							</div>
							
						</div>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endforeach; ?>
				
			</div>
			
			<div class="twelve wide column">
				<div class="ui container fluid section-data areas">
					<?php foreach($this->data['Connection']['sections'] as $section_n => $section): ?>
						<?php $this->view('views.connections.sections_config', ['name' => $section['name'], 'count' => $section_n, 'views' => !empty($this->data['Connection']['views']) ? $this->data['Connection']['views'] : array()]); ?>
					<?php endforeach; ?>
				</div>
				
				<div class="ui form">
					<div class="ui action input fluid">
						<input type="text" placeholder="<?php el('Section name...'); ?>" class="section-name">
						<button type="button" class="ui button green compact disabled add-section" data-url="<?php echo r2('index.php?ext='.\GApp::instance()->extension.'&cont=connections&act=sections_config&tvout=view'); ?>">
						<?php el('Add section'); ?>
						</button>
					</div>
				</div>
			</div>
			
		</div>
	</div>
	
	<div class="ui bottom attached tab segment structures-list" data-tab="locales" data-name="locale">
		<div class="ui grid">
		
			<div class="five wide column">
				<div class="ui vertical pointing menu fluid G2-tabs locale-list">
					<?php foreach($this->data['Connection']['locales'] as $locale_n => $locale): ?>
						<a class="blue item <?php if($locale_n == 'en_GB'): ?>active<?php endif; ?>" data-tab="locale-<?php echo $locale['name']; ?>">
							<?php if($locale_n != 'en_GB'): ?><div class="ui red label delete_block"><?php el('Delete'); ?></div><?php endif; ?>
							<?php echo $locale['name']; ?>
						</a>
					<?php endforeach; ?>
				</div>
				<div class="ui action input fluid">
					<input type="text" placeholder="<?php el('Locale tag...'); ?>" class="locale-name">
					<button type="button" id="add_new_locale" class="ui button green compact disabled add-locale" data-url="<?php echo r2('index.php?ext='.\GApp::instance()->extension.'&cont=connections&act=locales_config&tvout=view'); ?>">
						<?php el('Add locale'); ?>
					</button>
				</div>
			</div>
			
			<div class="eleven wide stretched column locale-data">
				<?php foreach($this->data['Connection']['locales'] as $locale_n => $locale): ?>
					<?php $this->view('views.connections.locales_config', ['name' => $locale['name'], 'count' => $locale_n]); ?>
				<?php endforeach; ?>
			</div>
			
		</div>
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="permissions">
		<?php $this->view('views.permissions_manager', ['model' => 'Connection', 'perms' => ['access' => rl('Access')], 'groups' => $_groups]); ?>
	</div>
	
</form>
