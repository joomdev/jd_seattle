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

JHtml::_('jquery.framework');

/** @var  \SellaciousView  $this */
$app    = JFactory::getApplication();
$editId = $app->getUserState('com_sellacious.edit.shippingrule.id');

if (!$editId)
{
	echo '<div class="center">Not editing any record currently.</div>';

	return;
}

$methodName = $app->getUserState('com_sellacious.edit.shippingrule.data.method_name');
$ruleTitle  = $app->getUserState('com_sellacious.edit.shippingrule.data.title');

if (!$methodName)
{
	$slabs     = $this->helper->shippingRule->getSlabs($editId);
	$ruleTitle = $this->helper->shippingRule->loadResult(array('id' => $editId, 'list.select' => 'a.title'));
}
elseif ($methodName == 'slabs.price')
{
	$slabs = $app->getUserState('com_sellacious.edit.shippingrule.data.slabs.price_slabs');

}
elseif ($methodName == 'slabs.weight')
{
	$slabs = $app->getUserState('com_sellacious.edit.shippingrule.data.slabs.weight_slabs');

}
elseif ($methodName == 'slabs.quantity')
{
	$slabs = $app->getUserState('com_sellacious.edit.shippingrule.data.slabs.quantity_slabs');
}
else
{
	$slabs = array();
}

if (is_string($slabs))
{
	$slabs = json_decode($slabs, true);
}

$this->state->set('shippingrule.title', $ruleTitle);

if ($app->input->getCmd('format') == 'csv')
{
	echo $this->loadTemplate('csv', $slabs);
}
else
{
	echo $this->loadTemplate('html', $slabs);
}
