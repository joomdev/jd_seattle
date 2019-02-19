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
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/** @var  SellaciousView  $this */
/** @var  stdClass[]  $tplData */
$slabs = $tplData;

$currency = $this->helper->currency->getGlobal('code_3');
$filename = $this->state->get('shippingrule.title', 'shipping-slabs');

if (!headers_sent($file, $line))
{
	header('content-type: text/csv');
	header('content-disposition: attachment; filename="' . htmlspecialchars($filename) . '.csv"');
}
else
{
	echo 'Headers already sent at ' . $file . ':' . $line . '.';

	return;
}

$rows   = array();
$rows[] = array('Min', 'Max', 'Country', 'State', 'Zip', 'Shipping');

foreach ($slabs as $i => $record)
{
	$record  = (array) $record;
	$min     = ArrayHelper::getValue($record, 'min', 0, 'float');
	$max     = ArrayHelper::getValue($record, 'max', 0, 'float');
	$country = ArrayHelper::getValue($record, 'country', 0, 'int');
	$state   = ArrayHelper::getValue($record, 'state', 0, 'int');
	$zip     = ArrayHelper::getValue($record, 'zip', 0, 'string');
	$price   = ArrayHelper::getValue($record, 'price', 0, 'float');

	try
	{
		$country = $this->helper->location->loadResult(array('id' => $country, 'list.select' => 'a.iso_code'));
	}
	catch (Exception $e)
	{
		$country = '';
	}

	try
	{
		$state = $this->helper->location->loadResult(array('id' => $state, 'list.select' => 'a.iso_code'));
	}
	catch (Exception $e)
	{
		$state = '';
	}

	$rows[] = array($min, $max, $country, $state, $zip, $price);
}


echo $this->helper->core->array2csv($rows);
