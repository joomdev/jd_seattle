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

?>
<div class="template-title">
	<i class="fa fa-chevron-right"></i>
	<span class="title-text" data-id="<?php echo $template->id ?>">
		<?php echo htmlspecialchars($template->title, ENT_COMPAT, 'UTF-8'); ?>
	</span>
	<?php
	if ($template->id > 0)
	{
		JText::script('PLG_SYSTEM_SELLACIOUSIMPORTER_TEMPLATE_RENAME_MESSAGE');
		JText::script('PLG_SYSTEM_SELLACIOUSIMPORTER_TEMPLATE_DELETE_WARNING');

		if ($this->helper->access->check('template.delete', $template->id, 'com_importer') ||
			 ($this->helper->access->check('template.delete.own', $template->id, 'com_importer') && $me->id == $template->created_by)): ?>
			<span class="title-tools pull-right">
				<i class="fa fa-edit btn-edit"> </i>
				<i class="fa fa-trash btn-delete"> </i>
			</span>
			<?php
		endif;
	}
	?>
</div>
