<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>

<form action="<?php echo r2('index.php?ext=chronoforms&cont=blocks'); ?>" method="post" name="admin_form" id="admin_form" class="ui form">
	
	<h2 class="ui header"><?php el('Blocks manager'); ?></h2>
	<div class="ui">
		<button type="button" class="compact ui button red icon labeled toolbar-button" data-selections="1" data-message="<?php el('Please make a selection.'); ?>" data-url="<?php echo r2('index.php?ext=chronoforms&cont=blocks&act=delete'); ?>">
			<i class="trash icon"></i><?php el('Delete'); ?>
		</button>
		<button type="button" class="compact ui button teal icon labeled toolbar-button" data-selections="1" data-message="<?php el('Please make a selection.'); ?>" data-url="<?php echo r2('index.php?ext=chronoforms&cont=blocks&act=copy'); ?>">
			<i class="copy icon"></i><?php el('Copy'); ?>
		</button>
		<button type="button" class="compact ui button orange icon labeled toolbar-button" data-selections="1" data-message="<?php el('Please make a selection.'); ?>" data-url="<?php echo r2('index.php?ext=chronoforms&cont=blocks&act=backup'); ?>">
			<i class="download icon"></i><?php el('Backup'); ?>
		</button>
		<a class="compact ui button blue icon labeled toolbar-button" href="<?php echo r2('index.php?ext=chronoforms&cont=blocks&act=restore'); ?>">
			<i class="upload icon"></i><?php el('Restore'); ?>
		</a>
	</div>
	
	<div class="ui clearing divider"></div>
	
	<div class="ui message top attached" style="padding:7px 12px;">
		<div class="ui action input" style="float:left;">
			<input type="text" name="search" placeholder="<?php el('Find blocks...'); ?>">
			<button class="ui icon button">
			<i class="search icon"></i>
			</button>
		</div>
		<div style="float:right;">
			<?php echo $this->Paginator->navigation('Block'); ?>
			<?php echo $this->Paginator->limiter('Block'); ?>
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
				<th class="single line"><?php echo $this->Sorter->link(rl('ID'), 'block_id'); ?></th>
				<th class=""><?php echo $this->Sorter->link(rl('Title'), 'block_title'); ?></th>
				<th class="single line"><?php el('Unique ID'); ?></th>
				<th class="single line"><?php el('Type'); ?></th>
				<th class="single line"><?php el('Group'); ?></th>
				<th class="five wide"><?php el('Description'); ?></th>
				<th class="single line"><?php echo $this->Sorter->link(rl('Enabled'), 'block_published'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($blocks as $i => $Block): ?>
			<tr>
				<td class="collapsing">
					<div class="ui checkbox selector">
						<input type="checkbox" class="hidden" name="gcb[]" value="<?php echo $Block['Block']['id']; ?>">
						<label></label>
					</div>
				</td>
				<td class="collapsing"><?php echo $Block['Block']['id']; ?></td>
				<td><?php echo $this->Html->attr('href', r2('index.php?ext=chronoforms&cont=blocks&act=edit'.rp('id', $Block['Block'])))->content($Block['Block']['title'])->tag('a'); ?></td>
				<td class="collapsing"><?php echo $Block['Block']['block_id']; ?></td>
				<td class="collapsing"><?php echo $Block['Block']['type']; ?></td>
				<td class="collapsing"><?php echo $Block['Block']['group']; ?></td>
				<td><?php echo nl2br($Block['Block']['desc']); ?></td>
				<td>
					<?php
						echo $this->Html
						->attr('href', r2('index.php?ext=chronoforms&cont=blocks&act=toggle'.rp('gcb', $Block['Block']['id']).rp('fld', 'published').rp('val', (int)!(bool)$Block['Block']['published'])))
						->addClass('compact ui button icon mini circular '.((int)$Block['Block']['published'] ? 'green' : 'red'))
						->content('<i class="icon '.((int)$Block['Block']['published'] ? 'check' : 'cancel').'"></i>')
						->tag('a');
					?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<div class="ui message bottom attached" style="padding:7px 12px;">
		<div style="float:right">
			<?php echo $this->Paginator->navigation('Block'); ?>
			<?php echo $this->Paginator->limiter('Block'); ?>
		</div>
		<div style="clear:both;"></div>
	</div>
	
</form>
