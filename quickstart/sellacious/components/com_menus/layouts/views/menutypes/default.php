<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('stylesheet', 'com_sellacious/menu.accordion.css', array('version' => S_VERSION_CORE, 'relative' => true));

$input = JFactory::getApplication()->input;

// Checking if loaded via index.php or component.php
$tmpl = ($input->getCmd('tmpl') != '');

JHtml::_('behavior.core');

if ($tmpl):
	$js = <<<JS
		setMenutype = function(type) {			
			window.parent.setMenuItemType("item.setType", type);
			window.parent.jQuery("#menuTypeModal").modal("hide");
		};
JS;
else:
	$js = <<<JS
		setMenutype = function(type) {
			window.location="index.php?option=com_menus&view=item&task=item.setType&layout=edit&type=" + type;
		};
JS;
endif;

JFactory::getDocument()->addScriptDeclaration($js);
?>
<div class="padding-10">
	<?php echo JHtml::_('bootstrap.startAccordion', 'collapseTypes', array('active' => 'collapse0', 'parent' => true)); ?>
		<?php $i = 0; ?>
		<?php foreach ($this->types as $name => $list) : ?>
			<?php echo JHtml::_('bootstrap.addSlide', 'collapseTypes', $name, 'collapse' . ($i++)); ?>
				<ul class="nav nav-tabs nav-stacked">
					<?php foreach ($list as $title => $item) : ?>
						<li>
							<?php
							$menutype = array(
								'id'      => $this->recordId,
								'title'   => isset($item->type) ? $item->type : $item->title,
								'request' => $item->request,
							);
							?>
							<?php $menutype = base64_encode(json_encode($menutype)); ?>
							<a class="choose_type" href="#" title="<?php echo JText::_($item->description); ?>"
								onclick="setMenutype('<?php echo $menutype; ?>')">
								<?php echo $title; ?> <br><small><em><?php echo JText::_($item->description); ?></em></small>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php echo JHtml::_('bootstrap.endSlide'); ?>
		<?php endforeach; ?>
	<?php echo JHtml::_('bootstrap.endSlide'); ?>
	<?php echo JHtml::_('bootstrap.endAccordion'); ?>
</div>
