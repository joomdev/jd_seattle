<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('behavior.formvalidator');

JHtml::_('formbehavior.chosen', 'select');
JHtml::_('bootstrap.tooltip');

JHtml::_('script', 'com_sellacious/util.noframes.js', false, true);

JHtml::_('script', 'media/com_sellacious/js/plugin/select2-3.5/select2.js', false, false);
JHtml::_('stylesheet', 'media/com_sellacious/js/plugin/select2-3.5/select2.css', null, false);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);

JHtml::_('script', 'media/com_sellacious/js/plugin/serialize-object/jquery.serialize-object.min.js', false, false);
JHtml::_('script', 'com_sellaciousopc/fe.view.opc.js', false, true);

JHtml::_('stylesheet', 'com_sellacious/fe.view.opc.css', null, true);

JHtml::_('script', 'media/com_sellacious/js/plugin/datepicker/dcalendar.picker.js', false, false);
JHtml::_('stylesheet', 'media/com_sellacious/js/plugin/datepicker/dcalendar.picker.css', null, false);

?>
<h1><?php echo JText::_("COM_SELLACIOUSOPC");?></h1>
