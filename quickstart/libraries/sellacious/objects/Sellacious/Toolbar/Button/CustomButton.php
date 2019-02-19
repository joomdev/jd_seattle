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

use Joomla\Registry\Registry;
use Sellacious\Toolbar\ToolbarButton;

/**
 * Renders a custom button
 *
 * @since  1.6.0
 */
class CustomButton extends ToolbarButton
{
	/**
	 * Button type
	 *
	 * @var    string
	 *
	 * @since   1.6.0
	 */
	protected $name = 'Custom';

	/**
	 * Constructor
	 *
	 * @param   string  $html
	 * @param   string  $id
	 *
	 * @since   1.6.0
	 */
	public function __construct($html = '', $id = 'custom')
	{
		$this->params = new Registry(get_defined_vars());

		parent::__construct();
	}

	/**
	 * Get the button CSS Id
	 *
	 * @return  string  Button CSS Id
	 *
	 * @since   1.6.0
	 */
	public function getId()
	{
		return $this->toolbar->getName() . '-' . ($this->group ? $this->group . '-' : '') . $this->params->get('id', 'custom');
	}

	/**
	 * Fetch the HTML for the button
	 *
	 * @return  string  HTML string for the button
	 *
	 * @since   1.6.0
	 */
	public function getButton()
	{
		return $this->params->get('html');
	}
}
