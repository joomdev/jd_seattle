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

JLoader::import('sellacious.loader');

if (class_exists('SellaciousHelper')):

/**
 * Sellacious mailer plugin
 *
 * @since  1.0
 */
class PlgSystemSellaciousMailer extends SellaciousPlugin
{
	/**
	 * @var    boolean
	 *
	 * @since  1.4.0
	 */
	protected $hasConfig = true;

	/**
	 * This method sends a reminder email for non-activated users.
	 *
	 * @return  void
	 *
	 * @since   1.3.3
	 */
	public function onAfterRoute()
	{
		if (class_exists('SellaciousHelper'))
		{
			$app     = JFactory::getApplication();
			$db      = JFactory::getDbo();
			$limit   = $this->params->get('limit', 10);
			$cron    = $this->params->get('cron', 1);
			$cronKey = $this->params->get('cron_key', '');

			// Cron use is disabled or the cronKey matches
			if ($cron == 0 || ($cronKey == $app->input->getString('mailer_key') && trim($cronKey) != ''))
			{
				$table = JTable::getInstance('MailQueue', 'SellaciousTable');

				$token       = rand(1000, 999999);
				$unixTimeNow = JFactory::getDate()->toUnix();

				// Unlock all records of token time 15 min older
				$query = $db->getQuery(true);
				$query->update($db->qn($table->getTableName()))
					->set($db->quoteName('lock_token') . ' = 0')
					->set($db->quoteName('lock_time') . ' = 0')
					->where('lock_time < ' . $db->quote($unixTimeNow - 900))
					->where('lock_token <> 0')
					->where('lock_time <> 0');

				$db->setQuery($query);
				$db->execute();

				// Set token and token time for queued records So that they are locked for exclusive processing
				$query = $db->getQuery(true);
				$query->update($db->qn($table->getTableName()))
					->set($db->quoteName('lock_token') . ' = ' . $db->quote($token))
					->set($db->quoteName('lock_time') . ' = ' . $db->quote($unixTimeNow))
					->where('state = ' . (int) SellaciousTableMailQueue::STATE_QUEUED)
					->where('lock_token = 0');

				$db->setQuery($query, 0, $limit);
				$db->execute();

				// Get records locked by current thread
				$query = $db->getQuery(true);
				$query->select('a.id')->from($db->qn($table->getTableName(), 'a'))
					->where('a.state = ' . (int) SellaciousTableMailQueue::STATE_QUEUED)
					->where('a.lock_token =' . $db->quote($token));

				$db->setQuery($query, 0, $limit);
				$iterator = $db->getIterator();

				if ($iterator->count())
				{
					foreach ($iterator as $item)
					{
						if ($table->load($item->id))
						{
							$this->sendMail($table);
						}
					}
				}

				if ($cron)
				{
					$app->close();
				}
			}
		}
	}

	/**
	 * Send the email from the given mail queue object
	 *
	 * @param   JTable  $email  The queued email from the sellacious mail queue
	 *
	 * @throws  Exception
	 *
	 * @since   1.3.3
	 */
	protected function sendMail($email)
	{
		$config     = JFactory::getConfig();
		$body       = trim($email->get('body'));
		$subject    = trim($email->get('subject')) ? $email->get('subject') : JText::_('PLG_SYSTEM_SELLACIOUSMAILER_MAIL_NO_SUBJECT');
		$sender     = $email->get('sender') ?: $config->get('mailfrom');
		$recipients = array_filter((array) $email->get('recipients'));
		$cc         = array_filter((array) $email->get('cc'));
		$bcc        = array_filter((array) $email->get('bcc'));
		$replyto    = array_filter((array) $email->get('replyto'));

		$params      = $email->get('params');
		$params      = is_string($params) ? json_decode($params, true) : (array) $params;
		$attachments = array();

		if (!empty($params['attachments']))
		{
			foreach ($params['attachments'] as $attachment)
			{
				if (is_file($path = JPATH_SITE . '/' . $attachment['path']))
				{
					$attachments[$path] = $attachment['name'];
				}
			}
		}

		try
		{
			if (trim($body) != '' && count($recipients) != 0)
			{
				/** @var  \Joomla\CMS\Mail\Mail $mailer */
				$mailer = JFactory::getMailer();
				$helper = SellaciousHelper::getInstance();

				if ($helper->config->get('show_brand_footer', 1) || !$helper->access->isSubscribed())
				{
					ob_start();
					include JPluginHelper::getLayoutPath($this->_type, $this->_name, 'footer');
					$body .= ob_get_clean();
				}

				$mailer->setSubject($subject);
				$mailer->setBody($body);
				$mailer->setFrom($sender, $config->get('fromname'));
				$mailer->isHtml(true);

				if (is_array($cc) && !empty($cc))
				{
					$mailer->addCc($cc);
				}

				if (is_array($bcc) && !empty($bcc))
				{
					$mailer->addBcc($bcc);
				}

				if (is_array($replyto) && !empty($replyto))
				{
					$mailer->addReplyTo($replyto);
				}

				if (count($attachments))
				{
					$mailer->addAttachment(array_keys($attachments), array_values($attachments));
				}

				$mailer->addRecipient($recipients);

				$sent = $mailer->Send();

				if ($sent === true)
				{
					$email->set('response', JText::_('PLG_SYSTEM_SELLACIOUSMAILER_MAIL_SENT'));
					$email->set('state', SellaciousTableMailQueue::STATE_SENT);
					$email->set('sent_date', JFactory::getDate()->toSql());
				}
				else
				{
					throw new RuntimeException($sent);
				}
			}
			else
			{
				$email->set('response', JText::_('PLG_SYSTEM_SELLACIOUSMAILER_MAIL_BODY_EMPTY_ERROR'));
				$email->set('state', SellaciousTableMailQueue::STATE_IGNORED);
			}
		}
		catch (Exception $e)
		{
			$email->set('response', JText::sprintf('PLG_SYSTEM_SELLACIOUSMAILER_MAIL_FAILED_ERROR', $e->getMessage()));
			$email->set('sent_date', JFactory::getDate()->toSql());

			$retries = $email->get('retries', 0);

			if ($retries >= (int) $this->params->get('retry', 5))
			{
				$email->set('state', SellaciousTableMailQueue::STATE_IGNORED);
			}
			else
			{
				$email->set('retries', $retries + 1);
			}
		}

		// Unlock record when we're done processing it
		$email->set('lock_token', 0);
		$email->set('lock_time', 0);

		$email->store();
	}
}

endif;
