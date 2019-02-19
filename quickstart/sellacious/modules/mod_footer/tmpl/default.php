<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die;

jimport('sellacious.loader');

$helper    = SellaciousHelper::getInstance();
$isAllowed = $helper->access->check('config.edit');
$license   = $helper->core->getLicense();

// Get site template
$tpl   = array('list.select' => 'a.template, a.title', 'list.from' => '#__template_styles', 'client_id' => '0', 'home' => 1);
$style = $helper->config->loadObject($tpl);
$style = is_object($style) ? sprintf('%s (%s)', $style->template, $style->title) : 'NA';

$license->set('template', $style);
unset($tpl, $style);

/** @var  \Joomla\Registry\Registry  $params */
require JModuleHelper::getLayoutPath('mod_footer', $isAllowed ? 'default_auto' : 'default_basic');
