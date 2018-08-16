<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>

<form action="<?php echo r2('index.php?ext=chronoforms&cont=connections'); ?>" method="post" name="admin_form" id="admin_form" class="ui form">
	
	<h2 class="ui header"><?php el('Forms manager'); ?></h2>
	<div class="ui">
		<div class="ui labeled icon dropdown button compact pointing blue">
			<i class="plus icon"></i>
			<span class="text"><?php el('New'); ?></span>
			<div class="menu">
				<a class="item" href="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=demos&name=easy_form'); ?>">
					<?php el('Easy form'); ?>
				</a>
				<a class="item" href="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=edit'); ?>">
					<?php el('Advanced form'); ?>
				</a>
			</div>
		</div>
		<div class="ui labeled icon dropdown button compact pointing green">
			<i class="magic icon"></i>
			<span class="text"><?php el('Demos'); ?></span>
			<div class="menu">
				<a class="item" href="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=demos&name=basic-contact'); ?>"><?php el('Basic contact form'); ?></a>
				<a class="item" href="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=demos&name=upload-files'); ?>"><?php el('Upload files'); ?></a>
				<a class="item" href="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=demos&name=paypal-redirect'); ?>"><?php el('PayPal payment'); ?></a>
				<a class="item" href="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=demos&name=fields-events'); ?>"><?php el('Fields events'); ?></a>
				<a class="item" href="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=demos&name=autocompleter'); ?>"><?php el('Auto Completer'); ?></a>
				<a class="item" href="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=demos&name=multi-page'); ?>"><?php el('Multi page'); ?></a>
				<a class="item" href="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=demos&name=switching'); ?>"><?php el('Event Switching'); ?></a>
				<a class="item" href="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=demos&name=modal'); ?>"><?php el('Modal'); ?></a>
				<a class="item" href="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=demos&name=dynamic-dropdown'); ?>"><?php el('Dynamic Dropdown'); ?></a>
			</div>
		</div>
		<button type="button" class="compact ui button red icon labeled toolbar-button" data-selections="1" data-message="<?php el('Please make a selection.'); ?>" data-url="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=delete'); ?>">
			<i class="trash icon"></i><?php el('Delete'); ?>
		</button>
		<button type="button" class="compact ui button teal icon labeled toolbar-button" data-selections="1" data-message="<?php el('Please make a selection.'); ?>" data-url="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=copy'); ?>">
			<i class="copy icon"></i><?php el('Copy'); ?>
		</button>
		<button type="button" class="compact ui button orange icon labeled toolbar-button" data-selections="1" data-message="<?php el('Please make a selection.'); ?>" data-url="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=backup'); ?>">
			<i class="download icon"></i><?php el('Backup'); ?>
		</button>
		<a class="compact ui button blue icon labeled toolbar-button" href="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=restore'); ?>">
			<i class="upload icon"></i><?php el('Restore'); ?>
		</a>
		<button type="button" class="compact ui button purple icon labeled toolbar-button" data-selections="1" data-message="<?php el('Please select a form.'); ?>" data-url="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=table'); ?>">
			<i class="database icon"></i><?php el('Create table'); ?>
		</button>
	</div>
	
	<div class="ui clearing divider"></div>
	
	<div class="ui message top attached" style="padding:7px 12px;">
		<div class="ui action input" style="float:left;">
			<input type="text" name="search" placeholder="<?php el('Find forms...'); ?>">
			<button class="ui icon button">
			<i class="search icon"></i>
			</button>
		</div>
		<div style="float:right;">
			<?php echo $this->Paginator->info('Connection'); ?>
			<?php echo $this->Paginator->navigation('Connection'); ?>
			<?php echo $this->Paginator->limiter('Connection'); ?>
		</div>
		<div style="clear:both;"></div>
	</div>
	<table class="ui selectable table attached">
		<thead>
			<tr>
				<th class="">
					<div class="ui select_all checkbox">
						<input type="checkbox">
						<label></label>
					</div>
				</th>
				<th class="single line"><?php echo $this->Sorter->link(rl('ID'), 'connection_id'); ?></th>
				<th class="five wide"><?php echo $this->Sorter->link(rl('Title'), 'connection_title'); ?></th>
				<th class=""><?php el('Alias'); ?></th>
				<th class="single line"><?php el('Data tables'); ?></th>
				<th class="single line"><?php echo $this->Sorter->link(rl('Public'), 'connection_public'); ?></th>
				<th class="single line"><?php echo $this->Sorter->link(rl('Published'), 'connection_published'); ?></th>
				<th class=""><?php el('View'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($connections as $i => $connection): ?>
			<tr>
				<td class="collapsing">
					<div class="ui checkbox selector">
						<input type="checkbox" class="hidden" name="gcb[]" value="<?php echo $connection['Connection']['id']; ?>">
						<label></label>
					</div>
				</td>
				<td class="collapsing"><?php echo $connection['Connection']['id']; ?></td>
				<?php
					$actname = 'edit';
					if(empty($connection['Connection']['events'])){
						$actname = 'edit2';
					}
				?>
				<td>
					<?php echo $this->Html->attr('href', r2('index.php?ext=chronoforms&cont=connections&act='.$actname.rp('id', $connection['Connection'])))->content($connection['Connection']['title'])->tag('a'); ?>
					<?php if(!empty($connection['Connection']['description'])): ?>
						<br />
						<span style="color:grey;"><?php echo nl2br($connection['Connection']['description']); ?></span>
					<?php endif; ?>
				</td>
				
				<td><?php echo $connection['Connection']['alias']; ?></td>
				<td><?php $this->view('views.connections.connected_tables', ['connection' => $connection]); ?></td>
				<td>
					<?php
						echo $this->Html
						->attr('href', r2('index.php?ext=chronoforms&cont=connections&act=toggle'.rp('gcb', $connection['Connection']['id']).rp('fld', 'public').rp('val', (int)!(bool)$connection['Connection']['public'])))
						->addClass('compact ui button icon mini circular '.((int)$connection['Connection']['public'] ? 'green' : 'red'))
						->content('<i class="icon '.((int)$connection['Connection']['public'] ? 'check' : 'cancel').'"></i>')
						->tag('a');
					?>
				</td>
				<td>
					<?php
						echo $this->Html
						->attr('href', r2('index.php?ext=chronoforms&cont=connections&act=toggle'.rp('gcb', $connection['Connection']['id']).rp('fld', 'published').rp('val', (int)!(bool)$connection['Connection']['published'])))
						->addClass('compact ui button icon mini circular '.((int)$connection['Connection']['published'] ? 'green' : 'red'))
						->content('<i class="icon '.((int)$connection['Connection']['published'] ? 'check' : 'cancel').'"></i>')
						->tag('a');
					?>
				</td>
				<td><?php echo $this->Html->attr('href', r2(\G2\Globals::get('ROOT_URL').'index.php?ext=chronoforms'.rp('chronoform', $connection['Connection']['alias'])))->attr('target', '_blank')->content(rl('View'))->tag('a'); ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<div class="ui message bottom attached" style="padding:7px 12px;">
		<div style="float:right">
			<?php echo $this->Paginator->info('Connection'); ?>
			<?php echo $this->Paginator->navigation('Connection'); ?>
			<?php echo $this->Paginator->limiter('Connection'); ?>
		</div>
		<div style="clear:both;"></div>
	</div>
	
</form>
