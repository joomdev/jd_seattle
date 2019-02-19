<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

class AjaxController extends JControllerLegacy
{
	/**
	 * @var    string  The prefix to use with controller messages
	 *
	 * @since   1.6.0
	 */
	protected $text_prefix = 'COM_AJAX_MODULE';

	/**
	 * Hold a JInput object for easier access to the input variables.
	 *
	 * @var    \JInput
	 *
	 * @since  3.0
	 */
	protected $input;

	/**
	 * Ajax interface trigger for invalid extension type
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function onAjax()
	{
		$results = null;
		$app     = JFactory::getApplication();

		try
		{
			if ($module = $this->input->getCmd('module'))
			{
				$results = AjaxHelper::moduleAjax();
			}
			elseif ($plugin = $this->input->getCmd('plugin'))
			{
				$results = AjaxHelper::pluginAjax();
			}
			elseif ($template = $this->input->getCmd('template'))
			{
				$results = AjaxHelper::templateAjax();
			}

			echo is_scalar($results) ? (string) $results : implode((array) $results);
		}
		catch (Exception $e)
		{
			// Log an error
			JLog::add($e->getMessage(), JLog::ERROR);

			// Set status header code
			$app->setHeader('status', $e->getCode(), true);

			// Echo exception type and message
			echo get_class($e) . ': ' . $e->getMessage();
		}
	}
}
