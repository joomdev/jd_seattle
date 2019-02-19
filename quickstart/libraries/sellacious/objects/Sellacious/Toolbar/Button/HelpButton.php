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

use Joomla\CMS\Help\Help;
use Joomla\CMS\Layout\FileLayout;
use Joomla\Registry\Registry;
use Sellacious\Toolbar\ToolbarButton;

/**
 * Renders a help popup window button
 *
 * @since  1.6.0
 */
class HelpButton extends ToolbarButton
{
	/**
	 * Button type
	 *
	 * @var    string
	 *
	 * @since   1.6.0
	 */
	protected $name = 'Help';

	/**
	 * Constructor
	 *
	 * @param   string   $ref        The name of the help screen (its key reference).
	 * @param   boolean  $com        Use the help file in the component directory.
	 * @param   string   $override   Use this URL instead of any other.
	 * @param   string   $component  Name of component to get Help (null for current component)
	 *
	 * @since   1.6.0
	 */
	public function __construct($ref = '', $com = false, $override = null, $component = null)
	{
		$this->params = new Registry(get_defined_vars());

		parent::__construct();
	}

	/**
	 * Get the button id
	 *
	 * @return  string	Button CSS Id
	 *
	 * @since   1.6.0
	 */
	public function getId()
	{
		return $this->toolbar->getName() . '-' . ($this->group ? $this->group . '-' : '') . 'help';
	}

	/**
	 * Fetches the button HTML code.
	 *
	 * @return  string
	 *
	 * @since   1.6.0
	 */
	public function getButton()
	{
		$options = array(
			'text' => \JText::_('JTOOLBAR_HELP'),
			'doTask' => $this->getCommand(),
		);

		if ($this->group)
		{
			$layout = new FileLayout('sellacious.toolbar.group.help');
		}
		else
		{
			$layout = new FileLayout('sellacious.toolbar.help');
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
		$ref          = $this->params->get('ref');
		$useComponent = $this->params->get('com');
		$override     = $this->params->get('override');
		$component    = $this->params->get('component');

		$url = Help::createUrl($ref, $useComponent, $override, $component);
		$url = htmlspecialchars($url, ENT_QUOTES);
		$str = \JText::_('JHELP', true);

		return "Joomla.popupWindow('$url', '$str', 700, 500, 1)";
	}
}
