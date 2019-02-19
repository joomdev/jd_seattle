<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Sellacious ProductQuery Plugin
 *
 * @since  1.2.0
 */
class PlgContentSellaciousProductQuery extends JPlugin
{
	/**
	 * @var  SellaciousHelper
	 *
	 * @since  1.2.0
	 */
	protected $helper;

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language' (this list is not meant to be comprehensive).
	 *
	 * @since   1.2.0
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		jimport('sellacious.loader');

		try
		{
			$this->helper = SellaciousHelper::getInstance();
		}
		catch (Exception $e)
		{
			$this->helper = null;
		}
	}

	/**
	 * Example after save content method
	 * Article is passed by reference, but after the save, so no changes will be saved.
	 * Method is called right after the content is saved
	 *
	 * @param   string   $context  The context of the content passed to the plugin (added in 1.6)
	 * @param   object   $article  A JTable object, here it would be the SellaciousTableProductQuery object
	 * @param   boolean  $isNew    If the content is just about to be created
	 *
	 * @return  boolean  True if function not enabled, is in front-end or is new. Else true or
	 *                   False depending on success of save function.
	 *
	 * @since   1.2.0
	 */
	public function onContentAfterSave($context, $article, $isNew)
	{
		if (!$this->helper || $context != 'com_sellacious.product.query' || !$isNew)
		{
			return true;
		}

		$me     = JFactory::getUser();
		$now    = JFactory::getDate();
		$title1 = $this->helper->product->loadResult(array('list.select' => 'a.title', 'id' => $article->product_id));
		$title2 = $this->helper->variant->loadResult(array('list.select' => 'a.title', 'id' => $article->variant_id));
		$sendTo = $this->helper->config->get('query_form_recipient');

		$recipients = array();
		$recTypes   = array();

		if ($sendTo == 2 || $sendTo == 3)
		{
			// Todo: Find group id by access
			$recipients = (array) JAccess::getUsersByGroup(8);
			$recTypes[] = sprintf('%d Administrators', count($recipients));
		}

		if ($sendTo == 1 || $sendTo == 3)
		{
			$recipients[] = $article->seller_uid;
			$recTypes[]   = ($article->seller_uid > 0) ? JFactory::getUser($article->seller_uid)->get('name') . ' (Seller)' : '(Seller)';
		}

		$message = array(
			'id'        => null,
			'parent_id' => 1,
			'sender'    => $me->id,
			'recipient' => count($recipients) > 1 ? -1 : reset($recipients),
			'is_read'   => 0,
			'title'     => JText::sprintf('COM_SELLACIOUS_PRODUCT_QUERY_FOR_TITLE', $title1, $title2),
			'body'      => null,
			'context'   => 'product.query',
			'ref_id'    => $article->id,
			'date_sent' => $now->toSql(),
			'state'     => 1,
			'params'    => array('recipients' => $recTypes, 'users' => $recipients),
		);

		try
		{
			/** @var  SellaciousTableMessage $table */
			$table = SellaciousTable::getInstance('Message');

			$table->bind($message);
			$table->setLocation($table->parent_id, 'last-child');
			$table->check();
			$table->store();

			$db     = JFactory::getDbo();
			$query  = $db->getQuery(true);
			$msg_id = $table->get('id');

			$query->insert('#__sellacious_message_recipients')
				->columns(array('message_id', 'recipient'));

			foreach (array_unique($recipients) as $recipient)
			{
				$query->values($db->q($msg_id) . ', ' . $db->q($recipient));
			}

			$db->setQuery($query)->execute();

			// Trigger message event as we just created it
			$message            = (object) $table->getProperties();
			$message->recipient = ($message->recipient == -1) ? $recipients : $message->recipient;
			$message->body      = $this->renderQuery($article->id, array('link' => true));

			$dispatcher = $this->helper->core->loadPlugins();
			$dispatcher->trigger('onContentAfterSave', array('com_sellacious.message', $message, $isNew = true));
		}
		catch (Exception $e)
		{
		}

		return true;
	}

	/**
	 * Plugin to load query form data and populate the message object with its text format.
	 *
	 * @param   string   $context   The context of the content being passed to the plugin.
	 * @param   object   &$message  An object with the properties of the loaded record.
	 * @param   mixed    $params    Additional parameters. Unused. See {@see PlgContentContent()}.
	 * @param   integer  $page      Optional page number. Unused. Defaults to zero.
	 *
	 * @return  bool  True on success
	 *
	 * @since   1.2.0
	 */
	public function onContentPrepare($context, &$message, $params, $page = 0)
	{
		if ($context == 'com_sellacious.message' && $message->context == 'product.query')
		{
			$message->text = $this->renderQuery($message->ref_id, $params);
		}

		return true;
	}

	/**
	 * Get the submitted query rendered as HTML
	 *
	 * @param   int    $queryId  An object with the properties of the loaded record.
	 * @param   mixed  $params   Additional parameters. Unused. See {@see PlgContentContent()}.
	 *
	 * @return  string
	 *
	 * @since   1.6.0
	 */
	protected function renderQuery(&$queryId, $params = null)
	{
		$query  = $this->helper->productQuery->getItem($queryId);
		$code   = $this->helper->product->getCode($query->product_id, $query->variant_id, $query->seller_uid);
		$url    = JRoute::_('../index.php?option=com_sellacious&view=product&p=' . $code);
		$params = $params instanceof Registry ? $params : new Registry($params);
		$fields = new Registry($query->query);
		$fields = (array) $fields->toObject();
		$fields = $this->renderFields($fields);

		/**
		 * Template variables:
		 *
		 * @var   static   $this    The plugin object
		 * @var   object   $message The sellacious message
		 * @var   Registry $params  Template options
		 * @var   object   $query   The product query record
		 * @var   string   $code    The product code
		 * @var   array    $fields  The query form fields and values
		 * @var   string   $url     Product URL to frontend
		 */
		ob_start();
		include JPluginHelper::getLayoutPath($this->_type, $this->_name, 'query');
		$text = ob_get_clean();

		return $text;
	}

	/**
	 * Method to render fields according to their field type
	 *
	 * @param   array  $fields  The query form fields and values
	 *
	 * @return  array
	 *
	 * @since   1.6.0
	 */
	protected function renderFields($fields)
	{
		foreach ($fields as $field)
		{
			$fieldItem    = $this->helper->field->getItem($field->id);
			$field->value = $this->helper->field->renderValue($field->value, $fieldItem->type);
		}

		return $fields;
	}
}
