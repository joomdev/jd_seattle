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

/**
 * ToolBar handler
 *
 * @since  1.6.0
 */
class Toolbar
{
	/**
	 * Toolbar name
	 *
	 * @var    string
	 *
	 * @since  1.6.0
	 */
	protected $name = array();

	/**
	 * An ordered array of toolbar spl_object_hash for assigned buttons and button-groups
	 *
	 * @var    string[]
	 *
	 * @since  1.6.0
	 */
	protected $items = array();

	/**
	 * An array of all button-groups
	 *
	 * @var    ButtonGroup[]
	 *
	 * @since  1.6.0
	 */
	protected $groups = array();

	/**
	 * An array of all buttons
	 *
	 * @var    ToolbarButton[]
	 *
	 * @since  1.6.0
	 */
	protected $buttons = array();

	/**
	 * Stores the singleton instances of various toolbar.
	 *
	 * @var    Toolbar[]
	 *
	 * @since  1.6.0
	 */
	protected static $instances = array();

	/**
	 * Constructor
	 *
	 * @param   string  $name  The toolbar name
	 *
	 * @since   1.6.0
	 */
	public function __construct($name = 'toolbar')
	{
		$this->name = $name;
	}

	/**
	 * Returns the Toolbar object, only creating it if it doesn't already exist
	 *
	 * @param   string  $name  The name of the toolbar
	 *
	 * @return  static  The Toolbar object
	 *
	 * @since   1.6.0
	 */
	public static function getInstance($name = 'toolbar')
	{
		if (empty(self::$instances[$name]))
		{
			self::$instances[$name] = new Toolbar($name);
		}

		return self::$instances[$name];
	}

	/**
	 * Add a button in the toolbar
	 *
	 * @param   ToolbarButton  $button   The button to append
	 * @param   bool           $prepend  Whether to add this button before the current first button/group in the toolbar
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function appendButton(ToolbarButton $button, $prepend = false)
	{
		$id = spl_object_hash($button);

		$this->buttons[$id] = $button;

		if ($prepend)
		{
			array_unshift($this->items, $id);
		}
		else
		{
			$this->items[$id] = $id;
		}
	}

	/**
	 * Add a button group in the toolbar
	 *
	 * @param   ButtonGroup  $group    The button group to append
	 * @param   bool         $prepend  Whether to add this group before the current first button/group in the toolbar
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function appendGroup(ButtonGroup $group, $prepend = false)
	{
		$name = $group->getName();
		$id   = spl_object_hash($group);

		$this->groups[$id] = $group;

		if ($prepend)
		{
			$this->items = array_merge(array($name => $id), $this->items);
		}
		else
		{
			$this->items[$name] = $id;
		}
	}

	/**
	 * Get a button group from the toolbar
	 *
	 * @param   string  $name The button group name
	 *
	 * @return  ButtonGroup
	 *
	 * @since   1.6.0
	 */
	public function getGroup($name)
	{
		$group = null;

		if (isset($this->items[$name]))
		{
			$id = $this->items[$name];

			if (isset($this->groups[$id]) && $this->groups[$id] instanceof ButtonGroup)
			{
				$group = $this->groups[$id];
			}
		}

		return $group;
	}

	/**
	 * Get the list of toolbar links
	 *
	 * @return  array
	 *
	 * @since   1.6.0
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * Get the name of the toolbar
	 *
	 * @return  string
	 *
	 * @since   1.6.0
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Render a toolbar
	 *
	 * @return  string  HTML for the toolbar
	 *
	 * @since   1.6.0
	 */
	public function render()
	{
		$html = array();

		// Start toolbar div
		$layout = new FileLayout('sellacious.toolbar.container.open');

		$html[] = $layout->render(array('id' => $this->name));

		// Render each button/group in the toolbar
		foreach ($this->items as $identifier)
		{
			if (isset($this->buttons[$identifier]))
			{
				$button = $this->buttons[$identifier];

				$html[] = $button->render();
			}
			elseif (isset($this->groups[$identifier]))
			{
				$group  = $this->groups[$identifier];

				$html[] = $group->render();
			}
		}

		// End toolbar div
		$layout = new FileLayout('sellacious.toolbar.container.close');

		$html[] = $layout->render(array());

		return implode('', $html);
	}
}
