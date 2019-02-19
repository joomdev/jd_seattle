<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

/** @var  PlgSystemSellaciousImporter  $this */
/** @var  stdClass  $displayData */
$template = $displayData;

JHtml::_('script', 'com_importer/select2.js', false, true);

$form = $this->getConfigForm($template->import_type, $template);
?>
<?php if ($form instanceof JForm): ?>

	<div class="widget-body edittabs">
		<fieldset>
			<?php
			if ($form instanceof JForm):

				$fields = $form->getFieldset();

				foreach ($fields as $field): ?>
					<div class="row <?php echo $field->label ? 'input-row' : '' ?>">
						<?php if ($field->type == 'Note'): ?>
							<div class="controls col-md-12"><?php echo $field->label ?></div>
						<?php elseif ($field->label == ''): ?>
							<div class="controls col-md-12"><?php echo $field->input ?></div>
						<?php else: ?>
							<div class="form-label col-sm-4 col-md-4 col-lg-3 nowrap"><?php echo $field->label ?></div>
							<div class="controls col-sm-8 col-md-8 col-lg-9"><?php echo $field->input ?></div>
						<?php endif; ?>
					</div>
					<div class="clearfix"></div>
					<?php
				endforeach;

			endif;
			?>
		</fieldset>
	</div>

<?php endif; ?>
