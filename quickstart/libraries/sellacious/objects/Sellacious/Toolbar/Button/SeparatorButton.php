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

namespace Sellacious\Toolbar\Button;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Layout\FileLayout;
use Joomla\Registry\Registry;
use Sellacious\Toolbar\ToolbarButton;

/**
 * Renders a button separator
 *
 * @since   1.6.0
 */
class SeparatorButton extends ToolbarButton
{
	/**
	 * Button type
	 *
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	protected $name = 'Separator';

	/**
	 * Constructor
	 *
	 * @param   string  $class  Separator class name
	 * @param   string  $css    The CSS style for the separator
	 *
	 * @since   1.6.0
	 */
	public function __construct($class, $css)
	{
		$this->params = new Registry(get_defined_vars());

		parent::__construct();
	}

	/**
	 * Get the HTML for a separator in the toolbar
	 *
	 * @return  string  The HTML for the separator
	 *
	 * @since   1.6.0
	 */
	public function render()
	{
		$css  = $this->params->get('css');
		$data = array(
			'class' => $this->params->get('class'),
			'style' => $css && is_numeric($css) ? ' style="width:' . (int) $css . 'px;"' : $css,
		);

		if ($this->group)
		{
			$layout = new FileLayout('sellacious.toolbar.group.separator');
		}
		else
		{
			$layout = new FileLayout('sellacious.toolbar.separator');
		}

		return $layout->render($data);
	}

	/**
	 * Get the button html id attribute
	 *
	 * @return  string
	 *
	 * @since   1.6.0
	 */
	protected function getId()
	{
		return null;
	}

	/**
	 * Get the button action
	 *
	 * @return  string
	 *
	 * @since   1.6.0
	 */
	public function getButton()
	{
		return null;
	}
}
