<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	if(empty($this->get('routes'))){
		$Route = new \G2\A\E\Chronodirector\M\Route();
		$routes = $Route->select('all');
		$this->set('routes', $routes);
	}
?>
<input type="hidden" value="routes_group" name="Connection[conditions][<?php echo $n; ?>][type]">
<input type="hidden" value="route_id" name="Connection[conditions][<?php echo $n; ?>][key]">

<div class="ui segment active" data-tab="conditions-<?php echo $n; ?>">
	
	<div class="field">
		<div class="ui checkbox toggle">
			<input type="hidden" name="Connection[conditions][<?php echo $n; ?>][not]" data-ghost="1" value="0">
			<input type="checkbox" class="hidden" name="Connection[conditions][<?php echo $n; ?>][not]" value="1">
			<label><?php el('Inverse'); ?></label>
		</div>
	</div>
	
	<div class="field">
		<label><?php el('Routes'); ?></label>
		<select name="Connection[conditions][<?php echo $n; ?>][value][]" multiple class="ui fluid dropdown">
		<?php foreach($routes as $route): ?>
			<option value="<?php echo $route['Route']['rid']; ?>"><?php echo $route['Route']['title']; ?></option>
		<?php endforeach; ?>
		</select>
	</div>
	
</div>