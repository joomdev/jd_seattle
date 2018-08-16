<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$act = !empty($act) ? $act : 'toggle';
	$tvout = '';
	$class = '';
	$link = !empty($link) ? $link : r2('index.php?ext='.$this->extension.'&cont='.$this->controller.'&act='.$act.'&gcb='.$id.'&fld='.$field.'&val='.(int)!(bool)$value);
	$actions = '{}';
	
	$states = !empty($states) ? (is_bool($states) ? [0 => 'red', 1 => 'green'] : $states) : false;
	
	if(!empty($dynamic) OR !empty($states)){
		$tvout = '&tvout=view';
		$class = 'G2-dynamic2 '.$act.'-'.$field.'-'.$id;
		
		$link = r2($link.$tvout);
		
		$actions = json_encode([
			'click' => [
				[
					'act' => 'ajax',
					'result.content' => ['place' => 'replace'],
					'url' => $link,
				],
			]
		]);
	}
?>
<?php if(!empty($states)): ?>
	<?php foreach($states as $v => $color): ?>
		<?php
			$style = 'display:none;';
			if($v == $value){
				$style = '';
			}
			
			$link = r2('index.php?ext='.$this->extension.'&cont='.$this->controller.'&act='.$act.'&gcb='.$id.'&fld='.$field.'&val='.(int)!(bool)$v.$tvout);
			$actions = json_encode([
				'click' => [
					[
						'act' => 'ajax',
						'result.state' => '.'.$act.'-'.$field.'-'.$id,
						'url' => $link,
					],
				]
			]);
		?>
		<a href="<?php echo $link; ?>" data-state="<?php echo $v; ?>" style="<?php echo $style; ?>" class="compact ui button icon mini circular <?php echo $class; ?>" data-actions='<?php echo $actions; ?>'><i class="icon circle <?php echo $color; ?>"></i></a>
	<?php endforeach; ?>
<?php else: ?>
	<?php if(!empty($value)): ?>
		<a href="<?php echo $link; ?>" class="compact ui button icon mini circular green <?php echo $class; ?>" data-actions='<?php echo $actions; ?>'><i class="icon check"></i></a>
	<?php else: ?>
		<a href="<?php echo $link; ?>" class="compact ui button icon mini circular red <?php echo $class; ?>" data-actions='<?php echo $actions; ?>'><i class="icon cancel"></i></a>
	<?php endif; ?>
<?php endif; ?>