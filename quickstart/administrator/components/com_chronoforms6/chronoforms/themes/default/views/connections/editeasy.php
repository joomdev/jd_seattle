<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php $this->view(\G2\Globals::ext_path('chronofc', 'admin').DS.'themes'.DS.'default'.DS.'views'.DS.'designer.php'); ?>
<?php
	$css = [];
	$css[] = 'a[data-tab$="-permissions"]{display:none !important;}';
	$css[] = 'a[data-tab$="-advanced"]{display:none !important;}';
	$css[] = 'a[data-tab$="-external"]{display:none !important;}';
	$css[] = '.dragged_item .ui.label.black{display:none !important;}';
	$css[] = '.easy_disabled{display:none !important;}';
	$css[] = '.save_link{display:none !important;}';
	//$css[] = '.private_config{display:none !important;}';
	\GApp::document()->addCssCode(implode("\n", $css));
?>
<form action="<?php echo r2('index.php?ext=chronoforms&cont=connections'); ?>" method="post" name="admin_form" id="admin_form" class="ui form">

	<h2 class="ui header"><?php echo !empty($this->data['Connection']['title']) ? $this->data['Connection']['title'] : rl('New form'); ?></h2>
	<div class="ui">
		<button type="button" class="ui button compact green icon labeled toolbar-button" data-fn="saveform" name="save" data-url="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=edit&easy=1'); ?>">
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
		<div class="ui grid">
			<input type="hidden" value="<?php echo empty($this->data['Connection']['functions']) ? 0 : max(array_keys($this->data['Connection']['functions'])); ?>" id="functions-count" name="functions-count">
			
			<?php
				//get views files
				$functions = \G2\L\Folder::getFolders(\G2\Globals::ext_path('chronofc', 'admin').'functions'.DS);
				$functions_info = [];
				foreach($functions as $function){
					$name = basename($function);
					$info_file = $function.DS.$name.'.ini';
					$info = parse_ini_file($info_file);
					if(!isset($info['color'])){
						$info['color'] = 'blue';
					}
					if(!empty($info['private']) AND !empty($public)){
						continue;
					}
					$functions_info[$name] = $info;
				}
				$functions_groups = array_unique(array('Basic') + \G2\L\Arr::getVal($functions_info, '[n].group', []));
			?>
			
			<input type="hidden" value="load" name="Connection[events][load][name]">
			<input type="hidden" value="submit" name="Connection[events][submit][name]">
			
			<div class="four wide column">
				
				<div class="ui fluid vertical steps small G2-tabs">
					<?php if(!empty($this->data['Connection']['functions'])): ?>
						<?php $counter = 0; ?>
						<?php foreach($this->data['Connection']['functions'] as $function_n => $function): ?>
							<?php
								if(empty($function['type'])){
									continue;
								}
							?>
							<?php
								$style = '';
								if(!empty($functions_info[$function['type']]['easy_config_disabled'])){
									$style = 'display:none;';
									$counter--;
								}
							?>
							<a class="step <?php if(empty($counter)): ?>active<?php endif; ?>" style="<?php echo $style; ?>" data-tab="functiontab-<?php echo $function_n; ?>">
								<i class="icon <?php if(!empty($functions_info[$function['type']]['icon'])): ?><?php echo $functions_info[$function['type']]['icon']; ?><?php endif; ?>"></i>
								<div class="content">
									<div class="title"><?php echo $functions_info[$function['type']]['title']; ?></div>
									<div class="description"><?php echo !empty($function['label']) ? $function['label'] : ''; ?></div>
								</div>
							</a>
							<?php $counter++; ?>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
				
			</div>
			
			<div class="twelve wide column events-data">
				<?php if(!empty($this->data['Connection']['functions'])): ?>
					<?php $counter = 0; ?>
					<?php foreach($this->data['Connection']['functions'] as $function_n => $function): ?>
						<?php
							if(empty($function['type'])){
								continue;
							}
						?>
						<?php
							$style = '';
							if(!empty($functions_info[$function['type']]['easy_config_disabled'])){
								$style = 'display:none;';
								$counter--;
							}
						?>
						<div class="ui tab segment <?php if(empty($counter)): ?>active<?php endif; ?>" style="<?php echo $style; ?>" data-tab="functiontab-<?php echo $function_n; ?>">
							<input type="hidden" value="" name="Connection[functions][<?php echo $function_n; ?>][_event]" class="dragged_parent">
							<?php
								$fn_path = \G2\Globals::ext_path('chronofc', 'admin').'functions'.DS.$function['type'].DS.$function['type'].'_config.php';
								$this->view($fn_path, ['n' => $function_n, 'function' => !empty($function) ? $function : []]);
							?>
						</div>
						<?php $counter++; ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			
		</div>
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="sections">
		<div class="ui grid">
			<input type="hidden" value="<?php echo empty($this->data['Connection']['views']) ? 0 : max(array_keys($this->data['Connection']['views'])); ?>" id="views-count">
			
			<div class="four wide column">
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
						if(isset($info['easy']) AND empty($info['easy'])){
							continue;
						}
						$views_info[$name] = $info;
					}
					$views_groups = array_unique(array('Fields') + \G2\L\Arr::getVal($views_info, '[n].group', []));
				?>
				
				<div class="ui fluid styled draggable-list">
					<?php foreach($views_groups as $kvg => $views_group): ?>
					<div class="ui divider"></div>
					<div class="content <?php if(empty($kvg)): ?> active<?php endif; ?>">
						
						<div class="ui grid center aligned small views-list">
							<?php foreach($views_info as $vw_name => $vw_info): ?>
								<?php if($vw_info['group'] == $views_group): ?>
								<div class="eight wide large screen sixteen wide small screen column draggable" data-type="view" data-info='<?php echo json_encode($vw_info); ?>' style="padding:5px;" data-url="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=block_config&tvout=view'); ?>">
									<div class="ui segment tiny" style="padding:10px 3px;">
										<div class="ui header small"><i class="icon <?php echo $vw_info['icon']; ?> fitted"></i><?php echo $vw_info['title']; ?></div>
									</div>
								</div>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
						
					</div>
					<?php endforeach; ?>
				</div>
				
				
			</div>
			
			<div class="twelve wide column sections-data">
				<?php //foreach($this->data['Connection']['sections'] as $section_n => $section): ?>
					<?php $this->view('views.connections.sections_config_easy', ['name' => 'one', 'count' => 0, 'views' => !empty($this->data['Connection']['views']) ? $this->data['Connection']['views'] : array()]); ?>
				<?php //endforeach; ?>
			</div>
			
		</div>
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="locales">
		<div class="ui grid">
		
			<div class="five wide column">
				<div class="ui vertical pointing menu fluid G2-tabs locales-list">
					<?php foreach($this->data['Connection']['locales'] as $locale_n => $locale): ?>
						<a class="blue item <?php if($locale_n == 'en_GB'): ?>active<?php endif; ?>" data-tab="locales-<?php echo $locale['name']; ?>"><?php echo $locale['name']; ?></a>
					<?php endforeach; ?>
				</div>
				<div class="ui action input fluid">
					<input type="text" placeholder="<?php el('Locale tag...'); ?>" name="new_locale_name" id="new_locale_name">
					<button type="button" id="add_new_locale" class="ui button green compact disabled"><?php el('Add locale'); ?></button>
				</div>
			</div>
			
			<div class="eleven wide stretched column locales-data">
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
