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
 * Ajax component helper class.
 *
 * @since   1.6.0
 */
class AjaxHelper
{
	/**
	 * Ajax interface trigger for plugin type extension
	 *
	 * @return  mixed  The responses received from the plugins
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public static function pluginAjax()
	{
		$app   = JFactory::getApplication();
		$group = $app->input->get('group', 'ajax');
		$event = $app->input->get('event', '');

		// B/C for old parameter name 'plugin'
		$event = $event ?: $app->input->get('plugin', '');

		JPluginHelper::importPlugin($group);

		$dispatcher = JEventDispatcher::getInstance();
		$results    = $dispatcher->trigger('onAjax' . $event);

		return $results;
	}

	/**
	 * Ajax interface trigger for module type extension
	 *
	 * @return  mixed  The responses received from the module
	 *
	 * @throws  Exception
	 *
	 * @since  1.6.0
	 */
	public static function moduleAjax()
	{
		$app    = JFactory::getApplication();
		$name   = $app->input->get('module');
		$method = $app->input->get('method');

		$module = JModuleHelper::getModule('mod_' . $name, null);

		// As JModuleHelper::isEnabled always returns true, we check for an id other than 0 to see if it is published.
		if ($module->id == 0)
		{
			throw new LogicException(JText::sprintf('COM_AJAX_MODULE_NOT_ACCESSIBLE', 'mod_' . $name), 404);
		}

		$helperFile = JPATH_BASE . '/modules/mod_' . $name . '/helper.php';

		// The helper file does not exist
		if (!is_file($helperFile))
		{
			throw new RuntimeException(JText::sprintf('COM_AJAX_FILE_NOT_EXISTS', 'mod_' . $name . '/helper.php'), 404);
		}

		$class  = implode('', array_map('ucfirst', explode('_', str_replace('-', '_', $name))));
		$class  = 'Mod' . $class . 'Helper';
		$method = $method ? $method . 'Ajax' : 'getAjax';

		JLoader::register($class, $helperFile);

		if (!is_callable(array($class, $method)))
		{
			throw new LogicException(JText::sprintf('COM_AJAX_METHOD_NOT_EXISTS', $method), 404);
		}

		// Load language file for module
		$lang = JFactory::getLanguage();

		$lang->load('mod_' . $name, JPATH_BASE . '/modules/mod_' . $name, null, false, true);
		$lang->load('mod_' . $name, JPATH_BASE, null, false, true);

		// Call the method over the helper class
		$results = call_user_func(array($class, $method));

		return $results;
	}

	/**
	 * Ajax interface trigger for template type extension
	 *
	 * @return  mixed  The responses received from the template
	 *
	 * @throws  Exception
	 *
	 * @since  1.6.0
	 */
	public static function templateAjax()
	{
		$app    = JFactory::getApplication();
		$name   = $app->input->get('template');
		$method = $app->input->get('method');

		/** @var  JTableExtension  $table */
		$table      = JTable::getInstance('Extension');
		$templateId = $table->find(array('type' => 'template', 'element' => $name));

		if (!$templateId || !$table->load($templateId) || !$table->get('enabled'))
		{
			throw new LogicException(JText::sprintf('COM_AJAX_TEMPLATE_NOT_ACCESSIBLE', 'tpl_' . $name), 404);
		}

		$client = JApplicationHelper::getClientInfo($table->get('client_id', 0));

		if ($client && $client->path)
		{
			$helperFile = $client->path . '/templates/' . $name . '/helper.php';
		}

		if (!isset($helperFile) || !is_file($helperFile))
		{
			throw new RuntimeException(JText::sprintf('COM_AJAX_FILE_NOT_EXISTS', 'tpl_' . $name . '/helper.php'), 404);
		}

		$class  = implode('', array_map('ucfirst', explode('_', str_replace('-', '_', $name))));
		$class  = 'Tpl' . $class . 'Helper';
		$method = $method ? $method . 'Ajax' : 'getAjax';

		JLoader::register($class, $helperFile);

		if (!is_callable(array($class, $method)))
		{
			throw new LogicException(JText::sprintf('COM_AJAX_METHOD_NOT_EXISTS', $method), 404);
		}

		// Load language file for template
		$lang = JFactory::getLanguage();

		$lang->load('tpl_' . $name, $client->path . '/templates/' . $name, null, false, true);
		$lang->load('tpl_' . $name, $client->path, null, false, true);

		// Call the method over the helper class
		$results = call_user_func(array($class, $method));

		return $results;
	}
}
