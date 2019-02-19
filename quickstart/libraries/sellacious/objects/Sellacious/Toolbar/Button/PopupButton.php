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
 * Renders a modal window button
 *
 * @since  1.6.0
 */
class PopupButton extends ToolbarButton
{
	/**
	 * Button type
	 *
	 * @var    string
	 *
	 * @since   1.6.0
	 */
	protected $name = 'Popup';

	/**
	 * Constructor
	 *
	 * @param   string   $name     Modal name, used to generate element ID
	 * @param   string   $text     The link text
	 * @param   string   $url      URL for popup
	 * @param   integer  $width    Width of popup
	 * @param   integer  $height   Height of popup
	 * @param   integer  $top      Top attribute.  [@deprecated  Unused, will be removed in 4.0]
	 * @param   integer  $left     Left attribute. [@deprecated  Unused, will be removed in 4.0]
	 * @param   string   $onClose  JavaScript for the onClose event.
	 * @param   string   $title    The title text
	 * @param   string   $footer   The footer html
	 *
	 * @since   1.6.0
	 */
	public function __construct($name = '', $text = '', $url = '', $width = 640, $height = 480, $top = 0, $left = 0,
	                            $onClose = '', $title = '', $footer = null)
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
		return $this->toolbar->getName() . '-'  . ($this->group ? $this->group . '-' : '') . 'popup-' . $this->params->get('name');
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
		$text    = $this->params->get('text');
		$width   = $this->params->get('width');
		$height  = $this->params->get('height');
		$onClose = $this->params->get('onClose');
		$title   = $this->params->get('title');
		$footer  = $this->params->get('footer');

		if ($title === '')
		{
			$title = $text;
		}

		$tTitle  = \JText::_($title);
		$tText   = \JText::_($text);
		$command = $this->getCommand();

		$options = array(
			'name'   => $name,
			'text'   => $tText,
			'title'  => $tTitle,
			'class'  => $this->getIconClass($name),
			'doTask' => $command,
		);

		if ($this->group)
		{
			$layout = new FileLayout('sellacious.toolbar.group.popup');
		}
		else
		{
			$layout = new FileLayout('sellacious.toolbar.popup');
		}

		$html   = array();
		$html[] = $layout->render($options);

		// Place modal div and scripts in a new div
		$html[] = '<div class="btn-group" style="width: 0; margin: 0">';

		$params = array(
			'title'  => $tTitle,
			'url'    => $command,
			'height' => $height,
			'width'  => $width,
		);

		if ($footer)
		{
			$params['footer'] = $footer;
		}

		$html[] = \JHtml::_('bootstrap.renderModal', 'modal-' . $name, $params);

		if ($onClose !== '')
		{
			$html[] = "<script>jQuery('#modal-" . $name . "').on('hide', function () {" . $onClose . ";});</script>";
		}

		$html[] = '</div>';

		return implode("\n", $html);
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
		$url = $this->params->get('url');

		if (strpos($url, 'http') !== 0)
		{
			$url = \JUri::base() . $url;
		}

		return $url;
	}
}
