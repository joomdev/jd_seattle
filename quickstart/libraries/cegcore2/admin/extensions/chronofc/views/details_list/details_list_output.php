<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$items = (array)$this->Parser->parse($view['data_provider'], true);
	
	$columns = [];
	
	if(!empty($view['auto_fields'])){
		$columns = array_keys($items);
	}
	
	$headers = [];
	if(!empty($view['fields'])){
		
		list($columns_data) = $this->Parser->multiline($view['fields'], true, 'name');
		
		if(is_array($columns_data)){
			foreach($columns_data as $columns_line){
				
				$column_name = $columns_line['name'];
				
				if(!in_array($column_name, $columns)){
					$columns[] = $column_name;
				}
				
				if(!empty($columns_line['namep']) AND $columns_line['namep'] == '-'){
					if(in_array($column_name, $columns)){
						$column_key = array_search($column_name, $columns);
						if($column_key !== false){
							unset($columns[$column_key]);
						}
					}
				}
				
				$headers[$column_name] = $column_name;
				if(!empty($columns_line['value'])){
					$headers[$column_name] = $this->Parser->parse($columns_line['value'], true);
				}
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
	
	?>
	<?php if(!empty($columns)): ?>
	<table class="ui very basic selectable table">
		<tbody>
		<?php foreach($columns as $k => $column): ?>
		<?php
			$this->set($view['name'].'.row', $items);
			$this->set($view['name'].'.value', \G2\L\Arr::getVal($items, $column, ''));
		?>
		<tr>
			<td class="collapsing right aligned"><h4 class="ui header"><?php echo !empty($headers[$column]) ? $headers[$column]: $column; ?></h4></td>
			<td>
			<?php if(!empty($columns_views[$column])): ?>
				<?php $this->Parser->parse($columns_views[$column]); ?>
			<?php else: ?>
				<?php
					$column_data = \G2\L\Arr::getVal($items, $column, '');
					if(is_array($column_data)){
						pr($column_data);
					}else{
						echo $column_data;
					}
				?>
			<?php endif; ?>
			</td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>