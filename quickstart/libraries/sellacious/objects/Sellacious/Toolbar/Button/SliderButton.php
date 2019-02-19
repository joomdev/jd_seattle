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
 * Renders a button to render an HTML element in a slider container
 *
 * @since  1.6.0
 */
class SliderButton extends ToolbarButton
{
	/**
	 * Button type
	 *
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	protected $name = 'Slider';

	/**
	 * Constructor
	 *
	 * @param   string   $name     Button name
	 * @param   string   $text     The link text
	 * @param   string   $url      URL for popup
	 * @param   integer  $width    Width of popup
	 * @param   integer  $height   Height of popup
	 * @param   string   $onClose  JavaScript for the onClose event.
	 *
	 * @since   1.6.0
	 */
	public function __construct($name = '', $text = '', $url = '', $width = 640, $height = 480, $onClose = '')
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
		return $this->toolbar->getName() . '-' . ($this->group ? $this->group . '-' : '') . 'slider-' . $this->params->get('name');
	}

	/**
	 * Fetch the HTML for the button
	 *
	 * @param   string   $type     Unused string, formerly button type.
	 *
	 * @return  string  HTML string for the button
	 *
	 * @since   1.6.0
	 */
	public function getButton($type = 'Slider')
	{
		\JHtml::_('script', 'jui/cms.js', array('version' => 'auto', 'relative' => true));

		$onClose = $this->params->get('onClose');
		$name    = $this->params->get('name');
		$options = array(
			'text'    => \JText::_($this->params->get('text')),
			'class'   => $this->getIconClass($name),
			'onClose' => '',
			'doTask'  => $this->getCommand(),
		);

		if ($onClose)
		{
			$options['onClose'] = ' rel="{onClose: function() {' . $onClose . '}}"';
		}

		if ($this->group)
		{
			$layout = new FileLayout('sellacious.toolbar.group.slider');
		}
		else
		{
			$layout = new FileLayout('sellacious.toolbar.slider');
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
	private function getCommand()
	{
		$url    = $this->params->get('url');
		$name   = $this->params->get('name');
		$height = $this->params->get('height');

		if (strpos($url, 'http') !== 0)
		{
			$url = \JUri::base() . $url;
		}

		return "Joomla.setcollapse('" . $url . "', '" . $name . "', '" . $height . "');";
	}
}
