<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;
JHtml::_('jquery.framework');
JHtml::_('script', 'media/jui/js/jquery.autocomplete.min.js', false, false, false, false, true);

$ajaxUrl = JRoute::_('index.php?option=com_finder&task=suggestions.suggest&format=json&tmpl=component');

$script = <<<JS
jQuery(function($) {
	$('#q').autocomplete({
		serviceUrl: "$ajaxUrl",
		paramName: 'q',
		minChars: 1,
		maxHeight: 400,
		width: 300,
		zIndex: 9999,
		deferRequestBy: 500
	});
});
JS;

JFactory::getDocument()->addScriptDeclaration($script);

$uri = new JUri($this->query->toUri());
$uri->setVar('option', 'com_sellacious');
$uri->setVar('view', 'search');
$uri->delVar('Itemid');

?>
<form id="finder-search" action="<?php echo JRoute::_($uri); ?>" method="get" class="form-inline">
	<?php echo $this->getFields(); ?>
	<fieldset class="word">
		<label for="q"><?php echo JText::_('COM_SELLACIOUS_SEARCH_TERMS'); ?></label>
		<input type="text" name="q" id="q" size="30" value="<?php echo $this->escape($this->query->input); ?>" class="inputbox"/>
		<button name="Search" type="submit" class="btn btn-primary"><span class="icon-search icon-white"></span> <?php
			echo JText::_('COM_SELLACIOUS_SEARCH_SUBMIT'); ?></button>
	</fieldset>
</form>
