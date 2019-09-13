<?php
/**
 * @package     FOF
 * @copyright   Copyright (c)2010-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Factory\Exception;

use Exception;
use RuntimeException;

defined('_JEXEC') or die;

class ControllerNotFound extends RuntimeException
{
	public function __construct( $controller, $code = 500, Exception $previous = null )
	{
		$message = \JText::sprintf('LIB_FOF_CONTROLLER_ERR_NOT_FOUND', $controller);

		parent::__construct( $message, $code, $previous );
	}

}
