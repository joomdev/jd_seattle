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

class ToolbarNotFound extends RuntimeException
{
	public function __construct( $toolbarClass, $code = 500, Exception $previous = null )
	{
		$message = \JText::sprintf('LIB_FOF_TOOLBAR_ERR_NOT_FOUND', $toolbarClass);

		parent::__construct( $message, $code, $previous );
	}

}
