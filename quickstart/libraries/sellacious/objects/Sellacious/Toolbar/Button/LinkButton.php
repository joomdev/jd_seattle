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
 * Renders a link button
 *
 * @since  3.0
 */
class LinkButton extends ToolbarButton
{
	/**
	 * Button type
	 *
	 * @var    string
	 *
	 * @since   1.6.0
	 */
	protected $name = 'Link';

	/**
	 * Constructor
	 *
	 * @param   string  $class  Name to be used as apart of the id
	 * @param   string  $text   Button text
	 * @param   string  $url    The link url
	 *
	 * @since   1.6.0
	 */
	public function __construct($class = 'back', $text = '', $url = null)
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
		return $this->toolbar->getName() . '-' . ($this->group ? $this->group . '-' : '') . $this->params->get('class');
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
		$options = array(
			'text'   => \JText::_($this->params->get('text')),
			'class'  => $this->getIconClass($this->params->get('class')),
			'doTask' => $this->getCommand(),
		);

		if ($this->group)
		{
			$layout = new FileLayout('sellacious.toolbar.group.link');
		}
		else
		{
			$layout = new FileLayout('sellacious.toolbar.link');
		}

		return $layout->render($options);
	}

	/**
	 * Get the JavaScript command for the button
	 *
	 * @return  string  JavaScript command string
	 *
	 * @since   1.6.0
	 */
	protected function getCommand()
	{
		return $this->params->get('url');
	}
}
