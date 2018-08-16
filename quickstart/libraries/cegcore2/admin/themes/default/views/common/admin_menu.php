<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui menu inverted">
	<a class="item icon blue <?php if($this->action == 'index' AND $this->controller == ''): ?>active<?php endif; ?>" href="<?php echo r2('index.php?ext='.$this->extension); ?>">
		<div class="ui blue inverted compact small active header large"><?php echo $etitle; ?></div>
	</a>
	<?php
		$menuitems = array_merge($menuitems, [
			['act' => 'clear_cache', 'title' => rl('Clear cache'), 'hidden' => true],
			['cont' => 'languages', 'title' => rl('Languages'), 'hidden' => true],
			['act' => 'validateinstall', 'title' => rl('Validate')],
		]);
	?>
	<?php foreach($menuitems as $k => $amdata): ?>
		<?php
			$menuitems[$k]['active'] = '';
			$icon = '';
			
			if(!empty($amdata['cont'])){
				if($this->controller == $amdata['cont']){
					$menuitems[$k]['active'] = 'active';
				}
				
				if($amdata['cont'] == 'languages'){
					$menuitems[$k]['icon'] = 'translate';
				}
				
				if($amdata['cont'] == 'tags'){
					$menuitems[$k]['icon'] = 'tag';
				}
			}
			if(!empty($amdata['act'])){
				if($this->action == $amdata['act']){
					$menuitems[$k]['active'] = 'active';
				}
				
				if($amdata['act'] == 'install_feature'){
					$menuitems[$k]['icon'] = 'magic';
				}
				
				if($amdata['act'] == 'clear_cache'){
					$menuitems[$k]['icon'] = 'refresh';
				}
				
				if($amdata['act'] == 'validateinstall'){
					$menuitems[$k]['icon'] = 'checkmark';
				}
				
				if($amdata['act'] == 'info'){
					$menuitems[$k]['icon'] = 'question';
				}
				
				if($amdata['act'] == 'settings'){
					$menuitems[$k]['icon'] = 'settings';
				}
				
				if($amdata['act'] == 'permissions'){
					$menuitems[$k]['icon'] = 'key';
				}
			}
			
			if(!empty($amdata['icon'])){
				$menuitems[$k]['icon'] = $amdata['icon'];
			}
			
			$menuitems[$k]['url'] = 'index.php?ext='.$this->extension.(!empty($amdata['cont']) ? '&cont='.$amdata['cont'] : '').(!empty($amdata['act']) ? '&act='.$amdata['act'] : '');
			
			if(!empty($amdata['hidden'])){
				continue;
			}
		?>
		<a class="item blue <?php echo $menuitems[$k]['active']; ?>" href="<?php echo r2($menuitems[$k]['url']); ?>">
			<?php if(!empty($menuitems[$k]['icon'])): ?>
				<i class="<?php echo $menuitems[$k]['icon']; ?> icon"></i>
			<?php endif; ?>
			<?php echo $amdata['title']; ?>
			<?php
				if(!empty($amdata['act']) AND $amdata['act'] == 'validateinstall'){
					$valid = \GApp::extension($this->get('ext'))->valid();
					if($valid === false){
						echo '&nbsp;<i class="icon exclamation red circular inverted small"></i>';
					}else if($valid === true){
						echo '&nbsp;<i class="icon checkmark green circular inverted small"></i>';
					}else if(is_numeric($valid)){
						echo '<span class="ui label green"><i class="icon checkmark"></i>'.rl('%s days left', [$valid]).'</span>';
					}
				}
			?>
		</a>
	<?php endforeach; ?>
	<div class="ui dropdown icon item">
		<i class="ellipsis horizontal icon"></i>
		<div class="menu">
			<?php foreach($menuitems as $k => $amdata): ?>
				<?php
					if(empty($amdata['hidden'])){
						continue;
					}
				?>
				<a class="item blue <?php echo $menuitems[$k]['active']; ?>" href="<?php echo r2($menuitems[$k]['url']); ?>">
					<?php if(!empty($menuitems[$k]['icon'])): ?>
						<i class="<?php echo $menuitems[$k]['icon']; ?> icon"></i>
					<?php endif; ?>
					<?php echo $amdata['title']; ?>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</div>