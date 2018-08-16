<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$items = $this->Parser->parse($view['data_provider'], true);
	if(!is_array($items)){
		$items = $this->get($view['data_provider'], []);
	}
	
	//if(empty($view['columns'])){
		if(!empty($view['auto_fields']) AND !empty($items)){
			$items_copy = $items;
			$first_item = array_shift($items_copy);
			foreach($first_item as $item_model => $item_data){
				foreach($item_data as $item_data_k => $item_data_v){
					$columns[] = $item_model.'.'.$item_data_k.':'.$item_model.'.'.$item_data_k;
				}
			}
			$view['columns'] = implode("\n", $columns);
		}
	//}
	
	$columns = [];
	$headers = [];
	if(!empty($view['columns'])){
		
		list($columns_data) = $this->Parser->multiline($view['columns'], true, false);
		
		if(is_array($columns_data)){
			foreach($columns_data as $columns_line){
				$columns[] = $column_name = $columns_line['name'];
				
				$headers[$column_name] = '';
				if(!empty($columns_line['value'])){
					$headers[$column_name] = $this->Parser->parse($columns_line['value'], true);
				}
			}
		}
		
		$columns_classes = [];
		if(!empty($view['classes'])){
			$columns_classes_data = explode("\n", $view['classes']);
			$columns_classes_data = array_map('trim', $columns_classes_data);
			
			foreach($columns_classes_data as $columns_classes_line){
				$columns_classes_line_data = explode(':', $columns_classes_line, 2);
				$columns_classes[array_shift($columns_classes_line_data)] = array_shift($columns_classes_line_data);
			}
		}
		
		$columns_views = [];
		if(!empty($view['views'])){
			$columns_views_data = explode("\n", $view['views']);
			$columns_views_data = array_map('trim', $columns_views_data);
			
			foreach($columns_views_data as $columns_views_line){
				$columns_views_line_data = explode(':', $columns_views_line, 2);
				$columns_views[array_shift($columns_views_line_data)] = array_shift($columns_views_line_data);
			}
		}
	}
	
	$form_id = \G2\L\Str::slug($view['name']);
?>
<?php if(!isset($view['form']) OR !empty($view['form'])): ?>
<form action="<?php echo r2($this->Parser->url('_self')); ?>" method="post" name="<?php echo $form_id; ?>" id="<?php echo $form_id; ?>" data-id="<?php echo $form_id; ?>" class="ui form">
<?php endif; ?>
	<table class="<?php if(isset($view['class'])): ?><?php echo $view['class']; ?><?php else: ?>ui selectable table<?php endif; ?>">
		<thead>
			<tr>
				<?php foreach($headers as $column_name => $header): ?>
					<?php
						if(!empty($columns_classes[$column_name])){
							$heade_class = $columns_classes[$column_name];
						}else{
							$heade_class = 'collapsing';
						}
					?>
					<th class="<?php echo $heade_class; ?>"><?php echo $header; ?></th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>
			<?php foreach($items as $key => $item): ?>
			<?php $this->set($view['name'].'.key', $key); ?>
			<?php $this->set($view['name'].'.row', $item); ?>
			<tr>
				<?php foreach($columns as $column): ?>
					<?php $this->set($view['name'].'.value', \G2\L\Arr::getVal($item, $column, '')); ?>
					<?php
						if(!empty($columns_classes[$column])){
							$cell_class = $columns_classes[$column];
						}else{
							$cell_class = 'collapsing';
						}
					?>
					<?php if(!empty($columns_views[$column])): ?>
						<td class="<?php echo $cell_class; ?>">
							<?php echo $this->Parser->parse($columns_views[$column], true); ?>
						</td>
					<?php else: ?>
						<td class="<?php echo $cell_class; ?>"><?php echo \G2\L\Arr::getVal($item, $column, ''); ?></td>
					<?php endif; ?>
				<?php endforeach; ?>
			</tr>
			<?php endforeach; ?>
		</tbody>
		
	</table>
<?php if(!isset($view['form']) OR !empty($view['form'])): ?>
</form>
<?php endif; ?>