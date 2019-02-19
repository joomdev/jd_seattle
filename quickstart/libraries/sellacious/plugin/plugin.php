<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

JLoader::import('sellacious.loader');

/**
 * Plugin base class for sellacious plugins process
 *
 * @since   1.3.3
 */
abstract class SellaciousPlugin extends JPlugin
{
	/**
	 * The full name of the plugin
	 *
	 * @var   string
	 *
	 * @since   1.5.2
	 */
	protected $pluginName;

	/**
	 * The full filesystem path of the plugin
	 *
	 * @var   string
	 *
	 * @since   1.5.2
	 */
	protected $pluginPath;

	/**
	 * A Registry object holding the parameters for the plugin
	 *
	 * @var    Registry
	 *
	 * @since  1.5
	 */
	public $params = null;

	/**
	 * The name of the plugin
	 *
	 * @var    string
	 * @since  1.5
	 */
	protected $_name = null;

	/**
	 * The plugin type
	 *
	 * @var    string
	 * @since  1.5
	 */
	protected $_type = null;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * The database driver object
	 *
	 * @var  JDatabaseDriver
	 *
	 * @since  1.6
	 */
	protected $db;

	/**
	 * The global application context
	 *
	 * @var  JApplicationCms
	 *
	 * @since  1.6
	 */
	protected $app;

	/**
	 * Sellacious helper factory object
	 *
	 * @var  SellaciousHelper
	 *
	 * @since  1.3.3
	 */
	protected $helper;

	/**
	 * Whether this class has a configuration to inject into sellacious configurations
	 *
	 * @var    bool
	 *
	 * @since  1.4.0
	 */
	protected $hasConfig = false;

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @since   1.5
	 */
	public function __construct(&$subject, $config = array())
	{
		// Get the parameters.
		if (isset($config['params']))
		{
			if ($config['params'] instanceof Registry)
			{
				$this->params = $config['params'];
			}
			else
			{
				$this->params = new Registry($config['params']);
			}
		}

		// Get the plugin name.
		if (isset($config['name']))
		{
			$this->_name = $config['name'];
		}

		// Get the plugin type.
		if (isset($config['type']))
		{
			$this->_type = $config['type'];
		}

		// Load the language files if needed.
		if ($this->autoloadLanguage)
		{
			$this->loadLanguage();
		}

		if (property_exists($this, 'app'))
		{
			$reflection = new \ReflectionClass($this);

			if (\JFactory::$application && $reflection->getProperty('app')->isPrivate() === false && $this->app === null)
			{
				$this->app = \JFactory::getApplication();
			}
		}

		if (property_exists($this, 'db'))
		{
			$reflection = new \ReflectionClass($this);

			if ($reflection->getProperty('db')->isPrivate() === false && $this->db === null)
			{
				$this->db = \JFactory::getDbo();
			}
		}

		// Register the observer ($this) so we can be notified
		$subject->attach($this);

		// Set the subject to observe
		$this->_subject = &$subject;

		// Setup sellacious
		$this->helper     = SellaciousHelper::getInstance();
		$this->pluginName = 'plg_' . $this->_type . '_' . $this->_name;
		$this->pluginPath = JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name;

		if ($this->hasConfig)
		{
			$this->params = $this->helper->config->getParams($this->pluginName);
		}

		$options = array('text_file' => $this->pluginName . '-log.php');

		JLog::addLogger($options, JLog::ALL, array($this->pluginName));
	}

	/**
	 * Adds additional fields to the sellacious field editing form
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   array  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function onContentPrepareForm($form, $data)
	{
		// Check we are manipulating a valid form.
		if ($this->hasConfig && ($form instanceof JForm))
		{
			$name = $form->getName();

			if ($name == 'com_sellacious.config')
			{
				$formPath = $this->pluginPath . '/' . $this->_name . '.xml';

				// Inject plugin configuration into config form.
				$form->loadFile($formPath, false, '//config');
			}
			elseif ($name == 'com_plugins.plugin')
			{
				// Don't let the plugin form show up in the Joomla plugin manager config page.
				$form->removeGroup($this->pluginName);
			}
		}

		return true;
	}

	/**
	 * Adds additional data to the sellacious field editing form data
	 *
	 * @param   string    $context  The context of the form to be altered.
	 * @param   stdClass  $data     The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function onContentPrepareData($context, $data)
	{
		if ($this->hasConfig && $context == 'com_sellacious.config' && is_object($data))
		{
			$extension = $this->pluginName;

			$data->$extension = $this->params;
		}

		return true;
	}

	/**
	 * Method to render a layout file from the plugin tmpl folder
	 *
	 * @param   string  $layout       The layout name to render
	 * @param   mixed   $displayData  The data required by the layout
	 * @param   string  $namespace    The scope of the layout to be loaded, omit to include default scope
	 *
	 * @return  string
	 *
	 * @since   1.5.2
	 */
	protected function renderLayout($layout = 'default', $displayData = null, $namespace = null)
	{
		$layout = $namespace ? $namespace . '_' . $layout : $layout;

		ob_start();

		$layoutPath = JPluginHelper::getLayoutPath($this->_type, $this->_name, $layout);

		if (is_file($layoutPath))
		{
			unset($namespace, $layout);

			/**
			 * Variables available to the layout
			 *
			 * @var  $this
			 * @var  $layoutPath
			 * @var  $displayData
			 */
			include $layoutPath;
		}

		return ob_get_clean();
	}
}
