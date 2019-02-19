<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */

/** @var  object  $field */
$location = $helper->location->loadObject(array('id' => $field->value));

if ($location)
{
	switch ($location->type)
	{
		case 'country':
			echo $location->title;
			break;
		case 'state':
			echo $location->title . ', ' . $location->country_title;
			break;
		case 'district':
			echo $location->title . ', ' . $location->state_title . ', ' . $location->country_title;
			break;
		case 'area':
			echo $location->title . ', ' . $location->state_title . ', ' . $location->country_title;
			break;
		case 'zip':
			echo $location->title . ', ' . $location->district_title . ', ' . $location->country_title;
			break;
	}
}
else
{
	echo '–';
}
