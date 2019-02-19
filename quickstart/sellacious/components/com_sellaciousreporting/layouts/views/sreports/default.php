<?php
/**
 * @version     1.6.1
 * @package     com_sellaciousreporting
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

defined('_JEXEC') or die;

$html = array(
	'toolbar' => JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)),
	'head'    => $this->loadTemplate('head'),
	'body'    => $this->loadTemplate('body'),
	'batch'   => '<input type="hidden" name="report_id" value="' . $this->reportId . '">'
);

$data = $this->getProperties();

$data['name']      = $this->getName();
$data['view']      = &$this;
$data['html']      = &$html;
$data['view_item'] = 'sreports';

$options = array('client' => 2, 'debug' => 0);

echo $this->handler->getRenderedSummary();

?><div style="overflow-x: auto"><?php
echo JLayoutHelper::render('com_sellacious.view.list', $data, '', $options);
?></div><br>
<?php
