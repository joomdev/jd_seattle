<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php $fdata = !empty($fdata) ? $fdata : []; ?>
<form action="<?php echo $form; ?>" method="post" class="ui form">
	
	<h2 class="ui header"><?php echo $title; ?></h2>
	<div class="ui">
		<?php foreach($toolbar as $button): ?>
			<?php list($text, $link, $type, $color, $icon) = $button; ?>
			<?php $href = ($type == 'a') ? 'href' : 'data-url'; ?>
			<?php
				$extra = '';
				if($type == 'submit'){
					$type = 'button';
					$extra = 'data-selections="1" data-message="'.(!empty($button['message']) ? $button['message'] : rl('Please make a selection')).'"';
				}
			?>
			<<?php echo $type; ?> class="compact ui button <?php echo $color; ?> icon labeled toolbar-button" <?php echo $extra; ?> <?php if($type != 'a'): ?>type="button"<?php endif; ?> <?php echo $href; ?>="<?php echo $link; ?>">
				<i class="<?php echo $icon; ?> icon"></i><?php echo $text; ?>
			</<?php echo $type; ?>>
		<?php endforeach; ?>
	</div>
	
	<div class="ui clearing divider"></div>
	
	<?php if(!empty($paginate) OR !empty($search)): ?>
	<div class="ui message top attached" style="padding:7px 12px;">
		<?php if(!empty($search)): ?>
		<div class="ui action input" style="float:left;">
			<input type="text" name="search" placeholder="<?php echo $search; ?>">
			<button class="ui icon button">
			<i class="search icon"></i>
			</button>
		</div>
		<?php endif; ?>
		<?php if(!empty($paginate)): ?>
		<div style="float:right;">
			<?php echo $this->Paginator->info($paginate); ?>
			<?php echo $this->Paginator->navigation($paginate); ?>
			<?php echo $this->Paginator->limiter($paginate); ?>
		</div>
		<?php endif; ?>
		<div style="clear:both;"></div>
	</div>
	<?php endif; ?>
	<table class="ui selectable table attached">
		<thead>
			<tr>
				<?php foreach($columns as $column): ?>
					<?php list($text, $path, $output, $class) = $column; ?>
					<?php if($text == '*'): ?>
						<th class="collapsing">
							<div class="ui select_all checkbox">
								<input type="checkbox">
								<label></label>
							</div>
						</th>
					<?php else: ?>
						<?php if(in_array($path, $this->get('helpers.sorter.fields', []))): ?>
							<th class="<?php echo $class; ?>"><?php echo $this->Sorter->link($text, $path); ?></th>
						<?php else: ?>
							<th class="<?php echo $class; ?>"><?php echo $text; ?></th>
						<?php endif; ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach($data as $k => $row): ?>
			<tr>
				<?php foreach($columns as $column): ?>
					<?php list($text, $path, $output, $class) = $column; ?>
					<?php if($text == '*'): ?>
						<td class="collapsing">
							<div class="ui checkbox selector">
								<input type="checkbox" class="hidden" name="gcb[]" value="<?php echo \G2\L\Arr::getVal($row, $path, 0); ?>">
								<label></label>
							</div>
						</td>
					<?php else: ?>
						<?php if(is_callable($output)): ?>
							<td class="<?php echo $class; ?>"><?php echo call_user_func_array($output, array_merge([$row], $fdata)); ?></td>
						<?php else: ?>
							<td class="<?php echo $class; ?>"><?php echo \G2\L\Arr::getVal($row, $path); ?></td>
						<?php endif; ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php if(!empty($paginate)): ?>
	<div class="ui message bottom attached" style="padding:7px 12px;">
		<div style="float:right">
			<?php echo $this->Paginator->info($paginate); ?>
			<?php echo $this->Paginator->navigation($paginate); ?>
			<?php echo $this->Paginator->limiter($paginate); ?>
		</div>
		<div style="clear:both;"></div>
	</div>
	<?php endif; ?>
	
</form>
