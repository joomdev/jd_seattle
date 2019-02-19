<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/** @var  SellaciousViewProduct $this */
$fieldsets = $this->form->getFieldsets();
$fields    = $this->form->getFieldset('basic');
$fieldset  = ArrayHelper::getValue($fieldsets, 'basic');

$col1 = array(
	'jform_id',
	'jform_categories',
	'jform_basic_type',
	'jform_basic_title',
	'jform_basic_parent_id',
	'jform_basic_manufacturer_id',
	'jform_basic_local_sku',
	'jform_basic_manufacturer_sku',
	'jform_basic_features',
	'jform_basic_introtext',
	'jform_basic_primary_image',
	'jform_basic_primary_video_url',
	'jform_basic_address',
	'jform_basic_location',
);

$col2 = array(
	'jform_basic_description',
	'jform_basic_images',
	'jform_basic_attachments',
);
?>
<div class="tab-pane fade in active" id="tab-basic">
	<div class="row padding-10">
		<div class="col-lg-6 col-md-12 col-sm-12">
			<fieldset>
				<?php
				foreach ($fields as $fKey => $field)
				{
					if (!in_array($fKey, $col1))
					{
						continue;
					}
					elseif ($field->hidden)
					{
						echo $field->input;
					}
					else
					{
						?>
						<div class="row <?php echo $field->label ? 'input-row' : '' ?>">
							<?php
							if ($field->label == '' || (isset($fieldset->width) && $fieldset->width == 12))
							{
								echo '<div class="controls col-md-12">' . $field->input . '</div>';
							}
							else
							{
								echo '<div class="form-label col-sm-4 col-md-4 col-lg-3">' . $field->label . '</div>';
								echo '<div class="controls col-sm-8 col-md-8 col-lg-9">' . $field->input . '</div>';
							}
							?>
						</div>
						<?php
					}
				}
				?>
			</fieldset>
		</div>

		<div class="col-lg-6 col-md-12 col-sm-12">
			<fieldset>
				<?php
				foreach ($fields as $fKey => $field)
				{
					if (!in_array($fKey, $col2))
					{
						continue;
					}
					elseif ($field->hidden)
					{
						echo $field->input;
					}
					else
					{
						?>
						<div class="row <?php echo $field->label ? 'input-row' : '' ?>">
							<?php
							if ($field->fieldname == 'description')
							{
								echo '<div class="form-label col-md-12">' . $field->label . '</div>';
								echo '<div class="controls col-md-12">' . $field->input . '</div>';
							}
							elseif ($field->label && (!isset($fieldset->width) || $fieldset->width < 12))
							{
								echo '<div class="form-label col-sm-4 col-md-4 col-lg-3">' . $field->label . '</div>';
								echo '<div class="controls col-sm-8 col-md-8 col-lg-9">' . $field->input . '</div>';
							}
							else
							{
								echo '<div class="controls col-md-12">' . $field->input . '</div>';
							}
							?>
						</div>
						<?php
					}
				}
				?>
			</fieldset>
		</div>

		<!-- Additional fields free flow 100% -->
		<div class="col-lg-6 col-md-12 col-sm-12">
			<fieldset>
				<?php
				foreach ($fields as $fKey => $field)
				{
					if (in_array($fKey, $col1) || in_array($fKey, $col2))
					{
						continue;
					}
					elseif ($field->hidden)
					{
						echo $field->input;
					}
					else
					{
						?>
						<div class="row <?php echo $field->label ? 'input-row' : '' ?>">
							<?php
							if ($field->label == '' || (isset($fieldset->width) && $fieldset->width == 12))
							{
								echo '<div class="controls col-md-12">' . $field->input . '</div>';
							}
							else
							{
								echo '<div class="form-label col-sm-4 col-md-4 col-lg-3">' . $field->label . '</div>';
								echo '<div class="controls col-sm-8 col-md-8 col-lg-9">' . $field->input . '</div>';
							}
							?>
						</div>
						<?php
					}
				}
				?>
			</fieldset>
		</div>
	</div>
</div>
