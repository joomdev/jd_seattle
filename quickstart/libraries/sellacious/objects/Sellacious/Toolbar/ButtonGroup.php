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

use Joomla\CMS\Layout\FileLayout;
use Sellacious\Toolbar\Button\SeparatorButton;

defined('JPATH_PLATFORM') or die;

/**
 * Button base class
 *
 * @since   1.6.0
 */
class ButtonGroup
{
	/**
	 * reference to the object that instantiated the element
	 *
	 * @var   Toolbar
	 *
	 * @since   1.6.0
	 */
	protected $toolbar = null;

	/**
	 * Button group name
	 *
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	protected $name;

	/**
	 * Button group label (unused if has a split button)
	 *
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	protected $label;

	/**
	 * An array of all buttons
	 *
	 * @var    ToolbarButton[]
	 *
	 * @since   1.6.0
	 */
	protected $buttons = array();

	/**
	 * Flag to determine whether to use split buttons dropdown
	 *
	 * @var    bool
	 *
	 * @since   1.6.0
	 */
	protected $split;

	/**
	 * Constructor
	 *
	 * @param   string  $name   The unique group name
	 * @param   string  $label  The Label for the group (unused if has a split button)
	 *
	 * @since   1.6.0
	 */
	public function __construct($name, $label)
	{
		$this->name    = $name;
		$this->label   = \JText::_($label);
		$this->toolbar = Toolbar::getInstance();
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
	 * Method to set whether to use split style for this button group
	 *
	 * @param   bool  $new  The new settings. Use null to simply get the current value without changing it.
	 *
	 * @return  bool  Old value of the flag
	 *
	 * @since   1.6.0
	 */
	public function useSplit($new = true)
	{
		$old = $this->split;

		if (is_bool($new))
		{
			$this->split = $new;
		}

		return $old;
	}

	/**
	 * The button group name
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
	 * Add a button in the group
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
		$button->setGroup($this->name);

		if ($prepend)
		{
			array_unshift($this->buttons, $button);
		}
		else
		{
			$this->buttons[] = $button;
		}
	}

	/**
	 * Render a toolbar button group
	 *
	 * @return  string  HTML for the toolbar button group
	 *
	 * @since   1.6.0
	 */
	public function render()
	{
		if (count($this->buttons) == 0)
		{
			return '';
		}

		if (count($this->buttons) == 1)
		{
			// Single item in group rendered as simple button
			$button = reset($this->buttons);

			$button->setGroup(null);
			$html = $button->render();
			$button->setGroup($this->name);

			return $html;
		}

		$html    = array();
		$buttons = array_values($this->buttons);

		// Start div
		$layout = new FileLayout('sellacious.toolbar.group.open');

		$html[] = $layout->render(array('id' => $this->toolbar->getName() . '-group-' . $this->name));

		// Main button and dropdown
		/** @var   ToolbarButton  $button */
		$button = $this->split ? array_shift($buttons) : null;

		// Temporarily switch to default rendering to render primary action
		if ($button && !$button instanceof SeparatorButton)
		{
			$button->setGroup(null);
			$html[] = $button->render();
			$button->setGroup($this->name);
		}
		else
		{
			$this->split = false;
		}

		$layout = new FileLayout('sellacious.toolbar.group.dropdown');

		$html[] = $layout->render(array('split' => $this->split, 'label' => $this->label));

		// Start dropdown menu
		$layout = new FileLayout('sellacious.toolbar.group.menustart');

		$html[] = $layout->render(array());

		// Render each button/group in the toolbar
		foreach ($buttons as $button)
		{
			if ($button instanceof ToolbarButton)
			{
				$html[] = $button->render();
			}
		}

		// Start dropdown menu
		$layout = new FileLayout('sellacious.toolbar.group.menuend');

		$html[] = $layout->render(array());

		// End div
		$layout = new FileLayout('sellacious.toolbar.group.close');

		$html[] = $layout->render(array());

		return implode('', $html);
	}
}
