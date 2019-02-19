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
 * Renders a standard button with a confirm dialog
 *
 * @since   1.6.0
 */
class ConfirmButton extends ToolbarButton
{
	/**
	 * Button type
	 *
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	protected $name = 'Confirm';

	/**
	 * Constructor
	 *
	 * @param   string  $message
	 * @param   string  $name
	 * @param   string  $text
	 * @param   string  $task
	 * @param   bool    $list
	 * @param   bool    $hideMenu
	 *
	 * @since   1.6.0
	 */
	public function __construct($message = '', $name = '', $text = '', $task = '', $list = true, $hideMenu = false)
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
		$options = array(
			'text'   => \JText::_($this->params->get('text')),
			'msg'    => \JText::_($this->params->get('message'), true),
			'class'  => $this->getIconClass($this->params->get('name')),
			'doTask' => $this->getCommand(),
		);

		if ($this->group)
		{
			$layout = new FileLayout('sellacious.toolbar.group.confirm');
		}
		else
		{
			$layout = new FileLayout('sellacious.toolbar.confirm');
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

		$message = \JText::_($this->params->get('message'), true);
		$command = "if (confirm('" . $message . "')) { Joomla.submitbutton('" . $this->params->get('task') . "'); }";

		if ($this->params->get('list'))
		{
			$alert   = "alert(Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));";
			$command = "if (document.adminForm.boxchecked.value == 0) { " . $alert . " } else { " . $command . " }";
		}

		return $command;
	}
}
