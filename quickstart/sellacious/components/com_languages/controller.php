<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access.
defined('_JEXEC') or die;

/**
 * Languages Controller.
 *
 * @method   bool  checkEditId(string $context, int  $id)
 *
 * @since  1.5
 */
class LanguagesController extends JControllerLegacy
{
	/**
	 * @var		string	The default view.
	 * @since   1.6
	 */
	protected $default_view = 'languages';

	/**
	 * Hold a JInput object for easier access to the input variables.
	 *
	 * @var    \JInput
	 * @since  3.0
	 */
	protected $input;

	/**
	 * Method to display a view.
	 *
	 * @param   boolean     $cachable   If true, the view output will be cached.
	 * @param   array|bool  $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  \JControllerLegacy  This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$view   = $this->input->get('view', $this->default_view);
		$layout = $this->input->get('layout', 'default');
		$id     = $this->input->getInt('id');

		// Check for edit form.
		if ($view == 'language' && $layout == 'edit' && !$this->checkEditId('com_languages.edit.language', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id) , 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_languages&view=languages', false));

			return $this;
		}

		return parent::display();
	}
}
