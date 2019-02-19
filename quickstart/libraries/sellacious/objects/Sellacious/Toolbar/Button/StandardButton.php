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
 * Renders a standard button
 *
 * @since  1.6.0
 */
class StandardButton extends ToolbarButton
{
	/**
	 * Button type
	 *
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	protected $name = 'Standard';

	/**
	 * Constructor
	 *
	 * @param   string   $name      The name of the button icon class.
	 * @param   string   $text      Button text.
	 * @param   string   $task      Task associated with the button.
	 * @param   boolean  $list      True to allow lists
	 * @param   boolean  $hideMenu  True to hide the menu on click
	 *
	 * @since   1.6.0
	 */
	public function __construct($name = '', $text = '', $task = '', $list = true, $hideMenu = false)
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
		return $this->toolbar->getName() . '-' . ($this->group ? $this->group . '-' : '') . $this->params->get('name');
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
		$name    = $this->params->get('name');
		$tText   = \JText::_($this->params->get('text'));
		$options = array(
			'text'     => $tText,
			'class'    => $this->getIconClass($name),
			'btnClass' => 'button-' . $name,
			'doTask'   => $this->getCommand(),
		);

		if ($name === 'apply' || $name === 'new')
		{
			$options['btnClass'] .= ' btn-success';
			$options['class']    .= ' icon-white';
		}

		if ($this->group)
		{
			$layout = new FileLayout('sellacious.toolbar.group.standard');
		}
		else
		{
			$layout = new FileLayout('sellacious.toolbar.standard');
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
		\JText::script('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');

		$cmd = "Joomla.submitbutton('" . $this->params->get('task') . "');";

		if ($this->params->get('list'))
		{
			$alert = "alert(Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));";
			$cmd   = "if (document.adminForm.boxchecked.value == 0) { " . $alert . " } else { " . $cmd . " }";
		}

		return $cmd;
	}
}
