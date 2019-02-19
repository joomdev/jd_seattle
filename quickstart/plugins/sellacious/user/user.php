<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die;

/**
 * Sellacious user plugin
 *
 * @since  1.5
 */
class PlgSellaciousUser extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Adds user registration template fields to the sellacious form for creating email templates
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   array  $data  The associated data for the form.
	 *
	 * @return  boolean
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (!$form instanceof JForm)
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		if ($form->getName() != 'com_sellacious.emailtemplate')
		{
			return true;
		}

		$contexts = array();

		$this->onFetchEmailContext('com_sellacious.emailtemplate', $contexts);

		if (!empty($contexts))
		{
			$array = is_object($data) ? Joomla\Utilities\ArrayHelper::fromObject($data) : (array) $data;

			if (array_key_exists($array['context'], $contexts))
			{
				$form->loadFile(__DIR__ . '/forms/user_registration.xml', false);
			}
		}

		return true;
	}

	/**
	 * Fetch the available context of email template
	 *
	 * @param   string    $context   The calling context
	 * @param   string[]  $contexts  The list of email context the should be populated
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 */
	public function onFetchEmailContext($context, array &$contexts = array())
	{
		if ($context == 'com_sellacious.emailtemplate')
		{
			$contexts['user_registration.self']  = JText::_('PLG_SELLACIOUS_EMAILTEMPLATE_CONTEXT_USER_REGISTRATION_RECIPIENT_USER_USER');
			$contexts['user_registration.admin'] = JText::_('PLG_SELLACIOUS_EMAILTEMPLATE_CONTEXT_USER_REGISTRATION_RECIPIENT_ADMIN_USER');
		}
	}

	/**
	 * This method sends a registration email on new users created.
	 *
	 * @param   string   $context  The calling context
	 * @param   JUser    $user     Holds the new user data.
	 * @param   boolean  $isNew    True if a new user is stored.
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   1.2.0
	 */
	public function onAfterSaveUser($context, $user, $isNew)
	{
		jimport('sellacious.loader');

		if ($context == 'com_sellacious.user' && $isNew && class_exists('SellaciousHelper'))
		{
			// Send to the user
			$table = JTable::getInstance('EmailTemplate', 'SellaciousTable');
			$table->load(array('context' => 'user_registration.self'));

			if ($table->get('state'))
			{
				$recipients = explode(',', $table->get('recipients'));

				if ($table->get('send_actual_recipient'))
				{
					array_unshift($recipients, $user->email);
				}

				$this->addMail($table, $user, $recipients);
			}

			// Send to administrators
			$table = JTable::getInstance('EmailTemplate', 'SellaciousTable');
			$table->load(array('context' => 'user_registration.admin'));

			if ($table->get('state'))
			{
				$recipients = explode(',', $table->get('recipients'));

				if ($table->get('send_actual_recipient'))
				{
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);

					try
					{
						$helper = SellaciousHelper::getInstance();

						// Super user = 8, as of J3.x
						$groups = (array) $helper->config->get('usergroups_company') ?: array(8);

						$query->select('u.email')->from('#__users u')
							->where('u.block = 0');

						$query->join('inner', '#__user_usergroup_map m ON m.user_id = u.id')
							->where('m.group_id IN (' . implode($groups) . ')');

						$query->group('u.email');

						$admins     = (array) $db->setQuery($query)->loadColumn();
						$recipients = array_merge($admins, $recipients);
					}
					catch (Exception $e)
					{
						// Ignored exception
					}
				}

				if (!empty($recipients))
				{
					$this->addMail($table, $user, $recipients);
				}
			}
		}
	}

	/**
	 * Send the email for the given user object using given email template object
	 *
	 * @param   JTable  $template    The template table object
	 * @param   JUser   $user        The user object
	 * @param   array   $recipients  List of recipient email addresses
	 */
	protected function addMail($template, $user, $recipients = array())
	{
		// Pre instantiate for constant access.
		$table  = JTable::getInstance('MailQueue', 'SellaciousTable');
		$config = JFactory::getConfig();
		$baseU  = JUri::getInstance()->toString(array('scheme', 'host', 'port'));

		if ($user->get('activation'))
		{
			$link = JRoute::_('index.php?option=com_users&task=registration.activate&token=' . $user->get('activation'));
		}
		else
		{
			$link = JRoute::_('index.php?option=com_users&view=login');
		}

		$link = str_replace(JUri::base(true), JUri::root(true), $link);

		$helper      = SellaciousHelper::getInstance();
		$emailParams = $helper->config->getParams('com_sellacious', 'emailtemplate_options');

		$replacements = array(
			'sitename'          => $config->get('sitename'),
			'site_url'          => rtrim(JUri::root(), '/'),
			'email_header'      => $emailParams->get('header', ''),
			'email_footer'      => $emailParams->get('footer', ''),
			'activation_link'   => rtrim($baseU, '/') . '/' . ltrim($link, '/'),
			'full_name'         => $user->get('name'),
			'email_address'     => $user->get('email'),
			'password'          => $user->get('password_clear', JText::_('PLG_SELLACIOUS_USER_NOPW')),
			'registration_date' => JHtml::_('date', $user->get('registerDate'), 'F d, Y h:i A T'),
		);

		// All codes are in upper case
		$replacements = array_change_key_case($replacements, CASE_UPPER);

		$data             = new stdClass;
		$data->context    = $template->get('context');
		$data->subject    = $template->get('subject');
		$data->body       = $template->get('body');
		$data->is_html    = true;
		$data->state      = SellaciousTableMailQueue::STATE_QUEUED;
		$data->recipients = (array) $recipients;
		$data->sender     = $template->get('sender');
		$data->cc         = !empty($template->cc) ? explode(',', $template->cc) : array();
		$data->bcc        = !empty($template->bcc) ? explode(',', $template->bcc) : array();
		$data->replyto    = !empty($template->replyto) ? explode(',', $template->replyto) : array();

		foreach ($replacements as $code => $replacement)
		{
			$data->subject = str_replace('%' . $code . '%', $replacement, $data->subject);
			$data->body    = str_replace('%' . $code . '%', $replacement, $data->body);
		}

		try
		{
			$table->bind($data);
			$table->check();
			$table->store();
		}
		catch (Exception $e)
		{
			// Todo: Handle this
		}
	}
}
