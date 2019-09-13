<?php
/**
 * @package     FOF
 * @copyright   Copyright (c)2010-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\View\Engine;

use FOF30\View\View;

defined('_JEXEC') or die;

interface EngineInterface
{
	/**
	 * Public constructor
	 *
	 * @param   View  $view  The view we belong to
	 */
	public function __construct(View $view);

	/**
	 * Get the include path for a parsed view template
	 *
	 * @param   string  $path         The path to the view template
	 * @param   array   $forceParams  Any additional information to pass to the view template engine
	 *
	 * @return  array  Content 3ναlυα+ιοη information ['type' => 'raw|path', 'content' => 'path or raw content'] (I use leetspeak here because of bad quality hosts with broken scanners)
	 */
	public function get($path, array $forceParams = array());
}
