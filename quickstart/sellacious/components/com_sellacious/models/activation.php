<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/**
 * Sellacious model.
 */
class SellaciousModelActivation extends SellaciousModel
{
	/**
	 * Stock method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function populateState()
	{
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  Registry
	 *
	 * @since   1.6
	 */
	public function getItem($pk = null)
	{
		// "name","email","sitekey","version","sitename","siteurl","template"
		$registry = new Joomla\Registry\Registry();
		$license  = $this->helper->config->get('license', array(), 'sellacious', 'application');

		// Get site template
		$tpl      = array('list.select' => 'a.template, a.title', 'list.from' => '#__template_styles', 'client_id' => '0', 'home' => 1);
		$style    = $this->helper->config->loadObject($tpl);
		$template = is_object($style) ? sprintf('%s (%s)', $style->template, $style->title) : 'NA';

		$registry->set('license', $license);
		$registry->set('sitename', $this->app->get('sitename'));
		$registry->set('siteurl', trim(JUri::root(), '\\/'));
		$registry->set('version', S_VERSION_CORE);
		$registry->set('template', $template);

		return $registry;
	}
}
