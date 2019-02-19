<?php
/**
 * @version      1.6.0
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Mohd Kareemuddin <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Config\ConfigHelper;

defined('_JEXEC') or die('Restricted access');

JLoader::import('sellacious.loader');

if (class_exists('SellaciousHelper')):

/**
 * The Product Notification management plugin
 *
 * @since   1.6.0
 */
class plgSystemSellaciousProductNotification extends SellaciousPlugin
{
	/**
	 * @var    bool
	 *
	 * @since  1.6.0
	 */
	protected $hasConfig = true;

	/**
	 * Log entries collected during execution.
	 *
	 * @var    array
	 *
	 * @since  1.6.0
	 */
	protected $log = array();

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 *
	 * @since  1.6.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * @var    \JApplicationCms
	 *
	 * @since  1.6.0
	 */
	protected $app;

	/**
	 * @var    \JDatabaseDriver
	 *
	 * @since  1.6.0
	 */
	protected $db;

	/**
	 * Adds product notification template fields to the sellacious form for creating email templates
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.6.0
	 */
	public function onContentPrepareForm($form, $data)
	{
		parent::onContentPrepareForm($form, $data);

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

		$array = is_object($data) ? ArrayHelper::fromObject($data) : (array) $data;

		if (!isset($array['context']) || !array_key_exists($array['context'], $contexts))
		{
			return true;
		}

		if (strpos($array['context'], 'product_notification') !== false)
		{
			$form->loadFile(__DIR__ . '/forms/notification.xml', false);
		}
		elseif (strpos($array['context'], 'product_status') !== false)
		{
			$form->loadFile(__DIR__ . '/forms/status.xml', false);
		}
		elseif (strpos($array['context'], 'product_approval') !== false)
		{
			$form->loadFile(__DIR__ . '/forms/approval.xml', false);
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
	 * @since   1.6.0
	 */
	public function onFetchEmailContext($context, array &$contexts = array())
	{
		if ($context == 'com_sellacious.emailtemplate')
		{
			$contexts['product_notification.admin'] = JText::_('PLG_SYSTEM_SELLACIOUSPRODUCTNOTIFICATION_PRODUCT_NOTIFICATION_ADMIN');
			$contexts['product_approval.admin']     = JText::_('PLG_SYSTEM_SELLACIOUSPRODUCTNOTIFICATION_PRODUCT_APPROVAL_ADMIN');
			$contexts['product_status.seller']      = JText::_('PLG_SYSTEM_SELLACIOUSPRODUCTNOTIFICATION_PRODUCT_STATUS_SELLER');
		}
	}

	/**
	 * This method send product notification based on a schedule cron job or page loads at set interval
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function onAfterRoute()
	{
		$useCRON = $this->params->get('cron', 1);
		$cronKey = $this->params->get('cron_key');
		$key     = $this->app->input->getString('notification_key');

		// Skip if Cron use is enabled and the cronKey doesn't match
		if ($useCRON && (trim($cronKey) == '' || $cronKey != $key))
		{
			return;
		}

		$t = microtime(true);

		try
		{
			$template = JTable::getInstance('EmailTemplate', 'SellaciousTable');
			$template->load(array('context' => 'product_notification.admin'));

			if ($template->get('state'))
			{
				$params    = new Registry($template->get('params'));
				$intervals = (array) $params->get('intervals');

				foreach ($intervals as $interval)
				{
					if ($range = $this->getReportDateRange($interval, $useCRON))
					{
						$this->addProductReportAdminMail($template, $range, $interval);
					}
				}
			}
		}
		catch (Exception $e)
		{
			$this->log($e->getMessage());

			JLog::add($e->getMessage(), JLog::CRITICAL);
		}

		if ($useCRON)
		{
			echo '<pre>';
			echo 'Time (sec): ' . microtime(true) - $t;
			echo "\n";
			echo implode("\n", $this->log);
			echo '</pre>';

			jexit();
		}
	}

	/**
	 * Method is called right after an item is saved
	 *
	 * @param   string    $context  The calling context
	 * @param   stdClass  $object   Holds the new message data
	 * @param   boolean   $isNew    If the content is just created
	 *
	 * @return  boolean
	 *
	 * @since   1.6.0
	 */
	public function onContentAfterSave($context, $object, $isNew)
	{
		if ($context == 'com_sellacious.product' && class_exists('SellaciousHelper'))
		{
			$this->handleProductSave($object, $isNew);
		}

		return true;
	}

	/**
	 * Method is called right after an item state is changed
	 *
	 * @param   string  $context  The calling context
	 * @param   int[]   $pks      Record ids which are affected
	 * @param   bool    $value    The new state value
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function onContentChangeState($context, $pks, $value)
	{
		if ($context == 'com_sellacious.product' && count($pks) && class_exists('SellaciousHelper'))
		{
			$this->handleProductState($pks, $value);
		}

		return true;
	}

	/**
	 * Handle the events for Product state changed, sends notification to the relevant sellers
	 *
	 * @param   int[]  $pks    The product id
	 * @param   int    $value  The new product status
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	protected function handleProductState($pks, $value)
	{
		if ($value != 1 && $value != - 3)
		{
			return;
		}

		// Send to the respective sellers
		$table = JTable::getInstance('EmailTemplate', 'SellaciousTable');
		$table->load(array('context' => 'product_status.seller'));

		if (!$table->get('state'))
		{
			return;
		}

		$recipients = array_filter(explode(',', $table->get('recipients')));

		foreach ($pks as $pk)
		{
			$object  = $this->helper->product->getItem($pk);
			$sellers = $this->helper->product->getSellers($object->id, false);

			foreach ($sellers as $seller)
			{
				$object->seller_company = $seller->store_name ?: $seller->company;
				$object->product_code   = $this->helper->product->getCode($object->id, 0, $seller->seller_uid);

				$replacements = $this->getValues('product_status.seller', $object);
				$to           = $recipients;

				if ($table->get('send_actual_recipient'))
				{
					array_unshift($to, $seller->email);
				}

				$this->queue($table, $replacements, $to);
			}
		}
	}

	/**
	 * Handler for Product save
	 *
	 * @param   stdClass  $object
	 * @param   bool      $isNew
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	protected function handleProductSave($object, $isNew)
	{
		$requireApproval = $this->helper->config->get('seller_product_approve', 0);

		if ($object->state == -1 && $requireApproval)
		{
			// Send to administrators
			$table = JTable::getInstance('EmailTemplate', 'SellaciousTable');
			$table->load(array('context' => 'product_approval.admin'));

			if ($table->get('state'))
			{
				$recipients = array_filter(explode(',', $table->get('recipients')));

				if ($table->get('send_actual_recipient'))
				{
					$recipients = array_merge($this->getAdmins(), $recipients);
				}

				// We should use "owned_by" field, or not?
				$filter     = array('list.select' => 'a.store_name, a.title', 'user_id' => $object->created_by);
				$sellerInfo = $this->helper->seller->loadObject($filter);

				$object->seller_company = $sellerInfo ? ($sellerInfo->store_name ?: $sellerInfo->title) : '';
				$object->product_code   = $this->helper->product->getCode($object->id, 0, $object->created_by);

				$this->queue($table, $this->getValues('product_approval.admin', $object), $recipients);
			}
		}
	}

	/**
	 * Get an array of replacement data for an email
	 *
	 * @param   string  $context
	 * @param   object  $object
	 *
	 * @return  string[]
	 *
	 * @since   1.6.0
	 */
	protected function getValues($context, $object)
	{
		$emailParams = $this->helper->config->getParams('com_sellacious', 'emailtemplate_options');

		switch ($context)
		{
			case 'product_status.seller':

				if($object->state == 1)
				{
					$status = JText::_('PLG_SELLACIOUS_MAILQUEUE_PRODUCT_STATUS_PUBLISHED');
				}
				elseif($object->state == -3)
				{
					$status = JText::_('PLG_SELLACIOUS_MAILQUEUE_PRODUCT_STATUS_DISAPPROVED');
				}
				else
				{
					$status = JText::_('JUNPUBLISHED');
				}

				$values = array(
					'sitename'       => JFactory::getConfig()->get('sitename'),
					'site_url'       => rtrim(JUri::root(), '/'),
					'email_header'   => $emailParams->get('header', ''),
					'email_footer'   => $emailParams->get('footer', ''),
					'date'           => JHtml::_('date', $object->created, 'F d, Y h:i A T'),
					'product_name'   => $object->title,
					'product_url'    => JRoute::_(JUri::root() . 'index.php?option=com_sellacious&view=product&p=' . $object->product_code),
					'status'         => $status,
					'seller_company' => $object->seller_company,
				);
				break;
			case 'product_approval.admin':

				if($object->state == -1)
				{
					$status = JText::_('PLG_SELLACIOUS_MAILQUEUE_PRODUCT_APPROVAL_PENDING');
				}

				$values = array(
					'sitename'       => JFactory::getConfig()->get('sitename'),
					'site_url'       => rtrim(JUri::root(), '/'),
					'email_header'   => $emailParams->get('header', ''),
					'email_footer'   => $emailParams->get('footer', ''),
					'date'           => JHtml::_('date', $object->created, 'F d, Y h:i A T'),
					'product_name'   => $object->title,
					'product_url'    => JRoute::_(JUri::root() . 'index.php?option=com_sellacious&view=product&p=' . $object->product_code),
					'status'         => $status,
					'seller_company' => $object->seller_company,
				);
				break;
			default:
				$values = array();
		}

		return $values;
	}

	/**
	 * Queue the email in the database using given template and data for the given recipients
	 *
	 * @param   JTable  $template      The template table object
	 * @param   array   $replacements  The short code replacements for the email text
	 * @param   array   $recipients    The recipient emails
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	protected function queue($template, $replacements, $recipients)
	{
		$recipients = array_filter($recipients);
		$subject    = trim($template->get('subject'));
		$body       = trim($template->get('body'));

		// Check Recipients, subject and body should not empty before adding to Email Queue
		if (empty($recipients) || $subject == '' || $body == '')
		{
			return;
		}

		// Pre instantiate for constant access.
		$table = JTable::getInstance('MailQueue', 'SellaciousTable');

		// All codes are in upper case
		$replacements = array_change_key_case($replacements, CASE_UPPER);

		$data             = new stdClass;
		$data->context    = $template->get('context');
		$data->subject    = $subject;
		$data->body       = $body;
		$data->is_html    = true;
		$data->state      = SellaciousTableMailQueue::STATE_QUEUED;
		$data->recipients = $recipients;
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
			$table->save($data);
		}
		catch (Exception $e)
		{
			// Todo: Handle this
		}
	}

	/**
	 * This method calculates the date range to send a product statistic notification email to admin.
	 *
	 * @param   string  $interval  The 'strtotime' function compliant time interval spec (N day | N week | N month)
	 * @param   bool    $useCRON   Whether using cron is enabled
	 *
	 * @return  array|null  The array containing two dates, viz. for start and end of the period respectively
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	protected function getReportDateRange($interval, $useCRON)
	{
		$start = JFactory::getDate()->setTime(0, 0, 0);
		$end   = JFactory::getDate()->setTime(0, 0, 0);

		if (strpos($interval, 'day'))
		{
			$start->modify('-' . $interval);
		}
		elseif (strpos($interval, 'week'))
		{
			$start->modify('+1 day')->modify('last sunday')->modify('-' . $interval);
			$end->modify('+1 day')->modify('last sunday')->modify('-1 second');
		}
		elseif (strpos($interval, 'month'))
		{
			$start->modify('first day of this month')->modify('-' . $interval);
			$end->modify('first day of this month')->modify('-1 second');
		}
		else
		{
			return null;
		}

		$config     = ConfigHelper::getInstance($this->pluginName, 'logs');
		$lastAccess = $config->get('last_report_sent', 0);

		if ($useCRON || $lastAccess < $end->toUnix())
		{
			// Mark as started earlier to avoid any other instance creating in between
			$config->set('last_report_sent', JFactory::getDate()->toUnix());
			$config->store();

			return array($start, $end);
		}

		return null;
	}

	/**
	 * Send the email to the administrators for the given products objects using given email template object
	 *
	 * @param   JTable   $template  The template table object
	 * @param   JDate[]  $range     Start and end dates for report
	 * @param   string   $interval  The interval value
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	protected function addProductReportAdminMail($template, $range, $interval)
	{
		/**
		 * Define variables
		 *
		 * @var   JDate  $start
		 * @var   JDate  $end
		 */
		list($start, $end) = $range;

		$from    = $this->db->q($start->toSql());
		$until   = $this->db->q($end->toSql());
		$filters = array('list.where' => array('a.state = 1', 'a.created >= ' . $from, 'a.created <= ' . $until));
		$count   = (int) $this->helper->product->count($filters);

		$recipients = explode(',', $template->get('recipients'));

		if ($template->get('send_actual_recipient'))
		{
			$recipients = array_merge($this->getAdmins(), $recipients);
		}

		$recipients = array_filter(array_unique($recipients));

		if (count($recipients))
		{
			$emailParams  = $this->helper->config->getParams('com_sellacious', 'emailtemplate_options');
			$replacements = array(
				'sitename'       => JFactory::getConfig()->get('sitename'),
				'site_url'       => rtrim(JUri::root(), '/'),
				'email_header'   => $emailParams->get('header'),
				'email_footer'   => $emailParams->get('footer'),
				'total_products' => (int) $count,
				'time_duration'  => $interval,
			);

			$this->queue($template, $replacements, $recipients);
		}
	}

	/**
	 * Get a list of administrator users who can receive administrative emails
	 *
	 * @return  array
	 *
	 * @since   1.6.0
	 */
	protected function getAdmins()
	{
		try
		{
			// Super user = 8, as of J3.x
			$groups = (array) $this->helper->config->get('usergroups_company') ?: array(8);

			$query = $this->db->getQuery(true);

			$query->select('u.email')->from('#__users u')->where('u.block = 0');

			$query->join('inner', '#__user_usergroup_map m ON m.user_id = u.id')
				->where('m.group_id IN (' . implode(',', $groups) . ')');

			$query->group(('u.email'));

			$admins = $this->db->setQuery($query)->loadColumn();
		}
		catch (Exception $e)
		{
			$admins = array();
		}

		return $admins;
	}

	/**
	 * Log the messages if logging enabled
	 *
	 * @param   string  $message  The message line to be logged
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	protected function log($message)
	{
		$this->log[] = $message;
	}
}

endif;
