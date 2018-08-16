<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui grid">
	<div class="four wide column">
		<div class="">
			<?php
				//get views files
				$functions = \G2\L\Folder::getFolders(\G2\Globals::ext_path('chronofc', 'admin').'functions2'.DS);
				$functions_info = [];
				foreach($functions as $function){
					$name = basename($function);
					$info_file = $function.DS.$name.'.ini';
					$info = parse_ini_file($info_file);
					if(!empty($info['apps']) AND !in_array('forms', $info['apps'])){
						continue;
					}
					
					if(!empty($info['platform']) AND !in_array(\G2\Globals::get('app'), $info['platform'])){
						continue;
					}
					
					$functions_info[$name] = $info;
				}
				
				$types = ['core', 'more'];
				$blocks_functions = [];
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
								<div class="sixteen wide column draggable" data-type="function" data-private="<?php echo !empty($fn_info['private']) ? 1 : 0; ?>" data-info='<?php echo json_encode($fn_info); ?>' style="padding:5px;" data-url="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=block&tvout=view'); ?>">
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
	</div>
	
	<div class="twelve wide column">
		<div class="ui message droppable">
			<?php $this->view('views.connections.61.functions', ['pn' => $pn, 'page' => $page]); ?>
		</div>
	</div>
</div>