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

$html = array(
	'head' => $this->loadTemplate('head'),
	'body' => $this->loadTemplate('body'),
);

$data = $this->getProperties();

$data['name']      = $this->getName();
$data['view']      = &$this;
$data['html']      = &$html;
$data['script']    = false;
$data['view_item'] = 'location';

$options = array('client' => 2, 'debug' => 0);
JText::script('COM_SELLACIOUS_LOCATIONS_IMPORT_CONFIRM');

echo JLayoutHelper::render('com_sellacious.view.list', $data, '', $options);

?>
<script type="text/javascript">
Joomla.submitbutton = function (task) {
	if (task == 'locations.import') {
		if (confirm(Joomla.JText._('COM_SELLACIOUS_LOCATIONS_IMPORT_CONFIRM'))) {
			Joomla.submitform(task);
		}
		return false;
	}
	Joomla.submitform(task);
}
</script>
<?php
