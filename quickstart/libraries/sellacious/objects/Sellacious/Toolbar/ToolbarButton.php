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

namespace Sellacious\Toolbar;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Layout\FileLayout;
use Joomla\Registry\Registry;

/**
 * Button base class
 *
 * @since   1.6.0
 */
abstract class ToolbarButton
{
	/**
	 * Reference to the toolbar that instantiated the element
	 *
	 * @var    Toolbar
	 *
	 * @since   1.6.0
	 */
	protected $toolbar = null;

	/**
	 * Reference to the button group that instantiated the element
	 *
	 * @var    string
	 *
	 * @since   1.6.0
	 */
	protected $group = null;

	/**
	 * Button type
	 *
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	protected $name;

	/**
	 * The button attributes
	 *
	 * @var    Registry
	 *
	 * @since   1.6.0
	 */
	protected $params = null;

	/**
	 * Constructor
	 *
	 * @since   1.6.0
	 */
	public function __construct()
	{
		$this->toolbar = Toolbar::getInstance();

		if ($this->params === null)
		{
			$this->params = new Registry;
		}
	}

	/**
	 * Get the element name
	 *
	 * @return  string   type of the parameter
	 *
	 * @since   1.6.0
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set the container toolbar instance for this button
	 *
	 * @param   Toolbar  $toolbar  The parent
	 *
	 * @since   1.6.0
	 */
	public function setToolbar($toolbar = null)
	{
		$this->toolbar = $toolbar;
	}

	/**
	 * Set the container group instance for this button, if any
	 *
	 * @param   string  $name  The group name
	 *
	 * @since   1.6.0
	 */
	public function setGroup($name = null)
	{
		$this->group = $name;
	}

	/**
	 * Get the HTML to render the button
	 *
	 * @return  string
	 *
	 * @since   1.6.0
	 */
	public function render()
	{
		$id     = $this->getId();
		$action = $this->getButton();

		if ($id)
		{
			$id = ' id="' . $id . '"';
		}

		$options = array();

		$options['id']     = $id;
		$options['action'] = $action;

		if ($this->group)
		{
			$layout = new FileLayout('sellacious.toolbar.group.base');
		}
		else
		{
			$layout = new FileLayout('sellacious.toolbar.base');
		}

		return $layout->render($options);
	}

	/**
	 * Method to get the CSS class name for an icon identifier,
	 * Templates can override the layout {'sellacious.toolbar.iconclass'} to define a value
	 *
	 * @param   string  $identifier  Icon identification string
	 *
	 * @return  string  CSS class name
	 *
	 * @since   1.6.0
	 */
	public function getIconClass($identifier)
	{
		$layout = new FileLayout('sellacious.toolbar.iconclass');

		return $layout->render(array('icon' => $identifier));
	}

	/**
	 * Get the button html id attribute
	 *
	 * @return  string
	 *
	 * @since   1.6.0
	 */
	abstract protected function getId();

	/**
	 * Get the button action
	 *
	 * @return  string
	 *
	 * @since   1.6.0
	 */
	abstract public function getButton();
}
