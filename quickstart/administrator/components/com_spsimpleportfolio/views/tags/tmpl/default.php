<?php

/**
 * @package     SP Simple Portfolio
 *
 * @copyright   Copyright (C) 2010 - 2018 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die();

JHtml::_('formbehavior.chosen', 'select');

$user		= JFactory::getUser();
$userId		= $user->get('id');

$listOrder = $this->escape($this->filter_order);
$listDirn = $this->escape($this->filter_order_Dir);
?>

<form action="<?php echo JRoute::_('index.php?option=com_spsimpleportfolio&view=tags'); ?>" method="post" id="adminForm" name="adminForm">
		<?php if (!empty( $this->sidebar)) : ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
			<div id="j-main-container" class="span10">
		<?php else : ?>
			<div id="j-main-container">
		<?php endif; ?>

		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped" id="tagList">
				<thead>
				<tr>
					<th width="2%" class="hidden-phone">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th>
						<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
					</th>
					<th width="1%">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
				</thead>

				<tfoot>
					<tr>
						<td colspan="12">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>

				<tbody>
					<?php if (!empty($this->items)) : ?>
						<?php foreach ($this->items as $i => $item) :
							$canEdit    = $user->authorise('core.edit', 'com_spsimpleportfolio.tag.' . $item->id) || ($user->authorise('core.edit.own',   'com_spsimpleportfolio.tag.' . $item->id) && $item->created_by == $userId);
							$canChange  = $user->authorise('core.edit.state', 'com_spsimpleportfolio.tag.' . $item->id);
							$link = JRoute::_('index.php?option=com_spsimpleportfolio&task=tag.edit&id=' . $item->id);
						?>
							<tr class="row<?php echo $i % 2; ?>">
								<td class="hidden-phone">
									<?php echo JHtml::_('grid.id', $i, $item->id); ?>
								</td>
								<td>
									<?php if ($canEdit) : ?>
										<a href="<?php echo JRoute::_('index.php?option=com_spsimpleportfolio&task=tag.edit&id='.$item->id);?>">
											<?php echo $this->escape($item->title); ?>
										</a>
									<?php else : ?>
										<?php echo $this->escape($item->title); ?>
									<?php endif; ?>

									<span class="small break-word">
										<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
									</span>
								</td>

								<td align="center">
									<?php echo $item->id; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
