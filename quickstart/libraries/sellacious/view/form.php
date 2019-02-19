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
defined('_JEXEC') or die;

/**
 * Base class for sellacious views.
 * Class holding methods for displaying presentation data in list layout.
 *
 * @since  3.0
 */
abstract class SellaciousViewForm extends SellaciousView
{
	/**
	 * @var  JObject
	 *
	 * @since   1.1.0
	 */
	protected $state;

	/**
	 * @var  JObject
	 *
	 * @since   1.1.0
	 */
	protected $item;

	/**
	 * @var  JForm
	 *
	 * @since   1.1.0
	 */
	protected $form;

	/**
	 * @var  string
	 *
	 * @since   1.1.0
	 */
	protected $action_prefix;

	/**
	 * @var  string
	 *
	 * @since   1.1.0
	 */
	protected $view_item;

	/**
	 * @var  string
	 *
	 * @since   1.1.0
	 */
	protected $view_list;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed
	 *
	 * @throws  Exception
	 *
	 * @since   1.1.0
	 */
	public function display($tpl = null)
	{
		if (!isset($this->state))
		{
			$this->state = $this->get('State');
		}

		if (!isset($this->item))
		{
			$this->item  = $this->get('Item');
		}

		if (!isset($this->form))
		{
			$this->form  = $this->get('Form');
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode("\n", $errors), JLog::WARNING, 'jerror');

			return false;
		}

		$this->prepareDisplay();

		return parent::display($tpl);
	}

	/**
	 * Method to prepare data/view before rendering the display.
	 * Child classes can override this to alter view object before actual display is called.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.1.0
	 */
	protected function prepareDisplay()
	{
		$this->app->input->set('hidemainmenu', true);

		$this->addToolbar();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @throws  Exception
	 *
	 * @since   1.1.0
	 */
	protected function addToolbar()
	{
		$isNew = ($this->item->get('id') == 0);

		$this->setPageTitle();

		if ($isNew ? $this->helper->access->check($this->action_prefix . '.create') : $this->helper->access->check($this->action_prefix . '.edit'))
		{
			JToolBarHelper::apply($this->view_item . '.apply', 'JTOOLBAR_APPLY');

			JToolBarHelper::save($this->view_item . '.save', 'JTOOLBAR_SAVE');

			if ($this->helper->access->check($this->action_prefix . '.create'))
			{
				JToolBarHelper::custom($this->view_item . '.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);

				if (!$isNew)
				{
					JToolBarHelper::custom($this->view_item . '.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
				}
			}
		}

		JToolBarHelper::cancel($this->view_item . '.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}

	/**
	 * To set the document page title based on appropriate logic.
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	protected function setPageTitle()
	{
		JToolBarHelper::title(JText::_(strtoupper($this->getOption() . '_TITLE_' . $this->getName())), 'file');
	}
}
