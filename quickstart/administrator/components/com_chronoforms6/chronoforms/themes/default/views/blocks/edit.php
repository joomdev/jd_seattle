<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<form action="<?php echo r2('index.php?ext=chronoforms&cont=blocks'); ?>" method="post" name="admin_form" id="admin_form" class="ui form">

	<h2 class="ui header"><?php echo !empty($this->data['Block']['title']) ? $this->data['Block']['title'] : rl('New form'); ?></h2>
	<div class="ui">
		<button type="button" class="ui button compact green icon labeled toolbar-button" name="save" data-url="<?php echo r2('index.php?ext=chronoforms&cont=blocks&act=edit'); ?>">
			<i class="check icon"></i><?php el('Save'); ?>
		</button>
		<button type="button" class="ui button compact blue icon labeled toolbar-button" name="apply" data-url="<?php echo r2('index.php?ext=chronoforms&cont=blocks&act=edit'); ?>">
			<i class="check icon"></i><?php el('Apply'); ?>
		</button>
		<a class="ui button compact red icon labeled toolbar-button" href="<?php echo r2('index.php?ext=chronoforms&cont=blocks'); ?>">
			<i class="cancel icon"></i><?php el('Cancel'); ?>
		</a>
		<a class="ui button compact orange icon labeled toolbar-button right floated <?php if(empty($this->data['Block']['id'])): ?>disabled<?php endif; ?>" href="<?php echo r2('index.php?ext=chronoforms&cont=blocks&act=backup&gcb[]='.$this->data['Block']['id']); ?>">
			<i class="download icon"></i><?php el('Backup'); ?>
		</a>
		
	</div>
	
	<div class="ui clearing divider"></div>
	
	<div class="ui top attached ordered tiny menu tabular G2-tabs">
		<a class="item active" data-tab="general">
			<div class="content"><div class="title"><?php el('General'); ?></div></div>
		</a>
	</div>
	
	<div class="ui bottom attached tab segment active" data-tab="general">
		<input type="hidden" name="Block[id]" value="">
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Title'); ?></label>
				<input type="text" placeholder="<?php el('Title'); ?>" name="Block[title]">
				<small><?php el('The block title as going to appear in the wizard designer.'); ?></small>
			</div>
			<div class="field">
				<label><?php el('Group'); ?></label>
				<input type="text" name="Block[group]">
				<small><?php el('The group to which the block belongs in the wizard, can be left empty.'); ?></small>
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<div class="ui checkbox">
					<input type="hidden" name="Block[published]" data-ghost="1" value="">
					<input type="checkbox" checked="checked" class="hidden" name="Block[published]" value="1">
					<label><?php el('Published'); ?></label>
					<small><?php el('Enable or disable this block.'); ?></small>
				</div>
			</div>
		</div>
		
		<div class="field">
			<label><?php el('Description'); ?></label>
			<textarea placeholder="<?php el('Description'); ?>" name="Block[desc]" id="conndesc" rows="5"></textarea>
			<small><?php el('Block description shown in wizard tooltips.'); ?></small>
		</div>
		
		<div class="field">
			<label><?php el('Block unique id'); ?></label>
			<input type="text" name="Block[block_id]">
			<small><?php el('Optionally provide a unique id for this block, it will be used to update the block data if a block with the same id was restored.'); ?></small>
		</div>
	</div>
	
</form>
