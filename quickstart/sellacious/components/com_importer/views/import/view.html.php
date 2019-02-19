<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Sellacious\Import\ImportHandler;

/**
 * View class for import
 *
 * @since   1.5.2
 */
class ImporterViewImport extends SellaciousView
{
	/**
	 * The model state
	 *
	 * @var  JObject
	 *
	 * @since   1.5.2
	 */
	protected $state;

	/**
	 * The import handlers
	 *
	 * @var   ImportHandler[]
	 *
	 * @since   1.5.2
	 */
	protected $handlers;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   1.5.2
	 */
	public function display($tpl = null)
	{
		$this->state    = $this->get('State');
		$this->handlers = $this->get('Handlers');

		if (count($this->handlers) == 0)
		{
			$this->app->enqueueMessage(JText::_('COM_IMPORTER_PREMIUM_FEATURE_NOTICE_IMPORTER'), 'premium');

			$this->setLayout('purchase');
		}

		if ($this->getLayout() == 'purchase')
		{
			return parent::display($tpl);
		}

		return parent::display($tpl);
	}
}
