<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  SellaciousViewEmailTemplate $this */

$fieldsets = $this->form->getFieldsets();
$visible   = array();

// Find visible tabs
foreach ($fieldsets as $fs_key => $fieldset)
{
	$fields = $this->form->getFieldset($fieldset->name);

	$visible[$fs_key] = 0;

	foreach ($fields as $field)
	{
		if (!$field->hidden)
		{
			$visible[$fs_key]++;
		}
	}
}
?>
	<ul id="myTab3" class="nav nav-tabs tabs-pull-left bordered">
		<?php
		$counter = 0;

		// Add links for visible tabs
		foreach ($fieldsets as $fs_key => $fieldset):
			if ($visible[$fs_key]):
				$class = ($counter++ ? '' : ' active') . ((isset($fieldset->align) && $fieldset->align == 'right') ? 'pull-right' : '');
				?>
				<li class="<?php echo $class ?>">
					<a href="#tab-<?php echo $fs_key; ?>" data-toggle="tab">
						<?php echo JText::_($fieldset->label, true) ?>
					</a>
				</li>
				<?php
			endif;
		endforeach;
		?>
	</ul>
<?php // Add content for visible tabs ?>
	<div class="tab-content">
		<?php
		$counter = 0;

		foreach ($fieldsets as $fs_key => $fieldset):
			if ($visible[$fs_key]):
				?>
				<div class="tab-pane fade <?php echo ($counter++) ? '' : ' in active' ?>" id="tab-<?php echo $fs_key ?>">

						<?php echo $this->loadTemplate('tab_slides', array($counter, $fs_key, $fieldset)); ?>

				</div>
				<?php
			endif;
		endforeach;
		?>
	</div>
<?php
// Add (remaining) content for invisible tabs
foreach ($fieldsets as $fs_key => $fieldset):
	if (!$visible[$fs_key]):
		$fields = $this->form->getFieldset($fieldset->name);
		foreach ($fields as $field):
			echo $field->input;
		endforeach;
	endif;
endforeach;
