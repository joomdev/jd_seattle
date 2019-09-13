<?php
/**
 * @package     FOF
 * @copyright   Copyright (c)2010-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Model\DataModel\Exception;

use Exception;

defined('_JEXEC') or die;

class NoContentType extends \UnexpectedValueException
{
	public function __construct( $className, $code = 500, Exception $previous = null )
	{
		$message = \JText::sprintf('LIB_FOF_MODEL_ERR_NOCONTENTTYPE', $className);

		parent::__construct( $message, $code, $previous );
	}

}
