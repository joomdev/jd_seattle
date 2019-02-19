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
 * View class for a list of questions.
 *
 * @since   1.6.0
 */
class SellaciousViewQuestions extends SellaciousViewList
{
	/**
	 * @var  string
	 */
	protected $action_prefix = 'question';

	/**
	 * @var  string
	 */
	protected $view_item = 'question';

	/**
	 * @var  string
	 */
	protected $view_list = 'questions';

	/**
	 * @var   int
	 */
	protected  $total_questions;

	/**
	 * @var   int
	 */
	protected  $total_unreplied;

	/**
	 * Method to preprocess data before rendering the display.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	protected function prepareDisplay()
	{
		// Gel Total Questions (Replied and Unreplied)
		$this->total_questions = $this->getQuestionsCount(2);

		// Gel Total Unreplied Questions
		$this->total_unreplied = $this->getQuestionsCount(0);

		parent::prepareDisplay();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	protected function addToolbar()
	{
		if (count($this->items))
		{
			if ($this->helper->access->check($this->action_prefix . '.edit') || $this->helper->access->check($this->action_prefix . '.edit.own'))
			{
				JToolBarHelper::editList($this->view_item . '.edit', 'COM_SELLACIOUS_QUESTIONS_REPLY_BUTTON_TITLE');
			}
		}

		$this->setPageTitle();
	}

	/**
	 * Get count of Questions
	 *
	 * @param   int  $state   0=unreplied, 1=replied and 2=both(replied and unreplied)
	 *
	 * @return  int
	 *
	 * @since   1.6.0
	 */
	protected function getQuestionsCount($state)
	{
		/** @var  SellaciousModelQuestions  $model */
		$model = JModelLegacy::getInstance('Questions', 'SellaciousModel', array('ignore_request' => true));

		// State 2 is used to get all replied and unreplied questions
		if ($state == 2)
		{
			$state = '';
		}

		$model->setState('filter.state', $state);

		$total = $model->getTotal();

		$model->setState('filter.state', '');

		return $total;
	}
}
