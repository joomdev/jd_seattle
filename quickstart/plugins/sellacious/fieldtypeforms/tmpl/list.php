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
$options = $field->value;

if (is_array($options))
{
	echo implode(', ', $options);
}
elseif (is_object($options))
{
	echo implode(', ', (array) $options);
}
else
{
	echo $options;
}
