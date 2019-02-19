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

use Joomla\Registry\Registry;

/**
 * Sellacious Application class
 *
 * @package   Sellacious
 *
 * @since     1.0.0
 */
final class JApplicationSellacious extends JApplicationCms
{
	/**
	 * Option to filter by language
	 *
	 * @var    boolean
	 * @since       3.2
	 * @deprecated  4.0  Will be renamed $language_filter
	 */
	protected $_language_filter = false;

	/**
	 * Option to detect language by the browser
	 *
	 * @var    boolean
	 * @since       3.2
	 * @deprecated  4.0  Will be renamed $detect_browser
	 */
	protected $_detect_browser = false;

	/**
	 * Class constructor.
	 *
	 * @param   JInput                 $input   An optional argument to provide dependency injection for the
	 *                                          application's input object.  If the argument is a JInput object that
	 *                                          object will become the application's input object, otherwise a default
	 *                                          input object is created.
	 * @param   Registry               $config  An optional argument to provide dependency injection for the
	 *                                          application's config object.  If the argument is a Registry object that
	 *                                          object will become the application's config object, otherwise a default
	 *                                          config object is created.
	 * @param   JApplicationWebClient  $client  An optional argument to provide dependency injection for the
	 *                                          application's client object.  If the argument is a
	 *                                          JApplicationWebClient object that object will become the application's
	 *                                          client object, otherwise a default client object is created.
	 *
	 * @since   3.2
	 */
	public function __construct(JInput $input = null, Registry $config = null, JApplicationWebClient $client = null)
	{
		// Register the application name
		$this->_name = 'sellacious';

		// Register the client ID
		$this->_clientId = 2;

		// Execute the parent constructor
		parent::__construct($input, $config, $client);

		// We don't allow live site config
		JFactory::getConfig()->set('live_site', '');

		// Set the root in the URI
		JUri::root(null, rtrim(dirname(JUri::base(true)), '\\/'));
	}

	/**
	 * Is sellacious application interface?
	 *
	 * @param   string  $name  The client name
	 *
	 * @return  boolean  True if this application is sellacious.
	 *
	 * @since   3.7.0
	 */
	public function isClient($name)
	{
		return $this->getName() === $name;
	}

	/**
	 * Check if the user can access the application
	 *
	 * @param   integer  $itemid  The item ID to check authorisation for
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function authorise($itemid)
	{
		$menus = $this->getMenu();
		$user  = JFactory::getUser();

		if (!$menus->authorise($itemid))
		{
			if ($user->get('id') == 0)
			{
				// Set the data
				$this->setUserState('users.login.form.data', array('return' => JUri::getInstance()->toString()));

				$url = JRoute::_('index.php?option=com_users&view=login', false);

				$this->enqueueMessage(JText::_('JGLOBAL_YOU_MUST_LOGIN_FIRST'));
				$this->redirect($url);
			}
			else
			{
				$this->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			}
		}
	}

	/**
	 * Dispatch the application
	 *
	 * @param   string  $component  The component which is being rendered.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function dispatch($component = null)
	{
		// Get the component if not set.
		if (empty($component))
		{
			$component = $this->findOption();
		}

		// Load the document to the API
		$this->loadDocument();

		// Set up the params
		$document = $this->getDocument();
		$router   = static::getRouter();
		$params   = $this->getParams();

		// Register the document object with JFactory
		JFactory::$document = $document;

		switch ($document->getType())
		{
			case 'html':
				// Get language
				$lang_code = $this->getLanguage()->getTag();
				$languages = JLanguageHelper::getLanguages('lang_code');

				// Set metadata
				if (isset($languages[$lang_code]) && $languages[$lang_code]->metakey)
				{
					$document->setMetaData('keywords', $languages[$lang_code]->metakey);
				}
				else
				{
					$document->setMetaData('keywords', $this->get('MetaKeys'));
				}

				$document->setMetaData('rights', $this->get('MetaRights'));

				// Get the template
				$template = $this->getTemplate(true);

				// Store the template and its params to the config
				$this->set('theme', $template->template);
				$this->set('themeParams', $template->params);

				break;
		}

		$document->setTitle($params->get('page_title'));
		$document->setDescription($params->get('page_description'));

		// Add version number or not based on global configuration
		if ($this->get('MetaVersion', 0))
		{
			$document->setGenerator('Joomla! - Open Source Content Management  - Version ' . JVERSION);
		}
		else
		{
			$document->setGenerator('Joomla! - Open Source Content Management');
		}

		// Joomla language loading order is not appropriate and they will not fix for whatever reason.
		$filename = JPATH_BASE . '/components/' . $component . '/' . substr($component, 4) . '.php';

		if (JComponentHelper::isEnabled($component) && file_exists($filename))
		{
			$lang = JFactory::getLanguage();
			$lang->load($component, JPATH_BASE . '/components/' . $component, null, false, true);
			$lang->load($component, JPATH_BASE, null, false, true);
		}

		$contents = JComponentHelper::renderComponent($component);
		$document->setBuffer($contents, 'component');

		// Trigger the onAfterDispatch event.
		JPluginHelper::importPlugin('system');
		$this->triggerEvent('onAfterDispatch');
	}

	/**
	 * Method to run the Web application routines.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function doExecute()
	{
		// Initialise the application
		$this->initialiseApp();

		// Test for magic quotes
		if (get_magic_quotes_gpc())
		{
			$lang = $this->getLanguage();

			if ($lang->hasKey('JERROR_MAGIC_QUOTES'))
			{
				$this->enqueueMessage(JText::_('JERROR_MAGIC_QUOTES'), 'error');
			}
			else
			{
				$this->enqueueMessage('Your host needs to disable magic_quotes_gpc to run sellacious.', 'error');
			}
		}

		// Mark afterInitialise in the profiler.
		JDEBUG ? $this->profiler->mark('afterInitialise') : null;

		// Route the application
		$this->route();

		// Mark afterRoute in the profiler.
		JDEBUG ? $this->profiler->mark('afterRoute') : null;

		// Dispatch the application
		$this->dispatch();

		// Mark afterDispatch in the profiler.
		JDEBUG ? $this->profiler->mark('afterDispatch') : null;
	}

	/**
	 * Return the current state of the detect browser option.
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	public function getDetectBrowser()
	{
		return $this->_detect_browser;
	}

	/**
	 * Return the current state of the language filter.
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	public function getLanguageFilter()
	{
		return $this->_language_filter;
	}

	/**
	 * Return a reference to the JMenu object.
	 *
	 * @param   string  $name     The name of the application/client.
	 * @param   array   $options  An optional associative array of configuration settings.
	 *
	 * @return  JMenu  JMenu object.
	 *
	 * @since   3.2
	 */
	public function getMenu($name = 'sellacious', $options = array())
	{
		$menu = parent::getMenu($name, $options);

		return $menu;
	}

	/**
	 * Get the application parameters
	 *
	 * @param   string  $option  The component option
	 *
	 * @return  object  The parameters object
	 *
	 * @since       3.2
	 * @deprecated  4.0  Use getParams() instead
	 */
	public function getPageParameters($option = null)
	{
		return $this->getParams($option);
	}

	/**
	 * Get the application parameters
	 *
	 * @param   string  $option  The component option
	 *
	 * @return  object  The parameters object
	 *
	 * @since   3.2
	 */
	public function getParams($option = null)
	{
		static $params = array();

		$hash = '__default';

		if (!empty($option))
		{
			$hash = $option;
		}

		if (!isset($params[$hash]))
		{
			// Get component parameters
			if (!$option)
			{
				$option = $this->input->getCmd('option', null);
			}

			// Get new instance of component global parameters
			$params[$hash] = clone JComponentHelper::getParams($option);

			// Get menu parameters
			$menus = $this->getMenu();
			$menu  = $menus->getActive();

			// Get language
			$lang_code = $this->getLanguage()->getTag();
			$languages = JLanguageHelper::getLanguages('lang_code');

			$title = $this->get('sitename');

			if (isset($languages[$lang_code]) && $languages[$lang_code]->metadesc)
			{
				$description = $languages[$lang_code]->metadesc;
			}
			else
			{
				$description = $this->get('MetaDesc');
			}

			$rights = $this->get('MetaRights');
			$robots = $this->get('robots');

			// Lets cascade the parameters if we have menu item parameters
			if (is_object($menu))
			{
				$temp = new Registry;
				$temp->loadString($menu->params);
				$params[$hash]->merge($temp);
				$title = $menu->title;
			}
			else
			{
				// Get com_menu global settings
				$temp = clone JComponentHelper::getParams('com_menus');
				$params[$hash]->merge($temp);

				// If supplied, use page title
				$title = $temp->get('page_title', $title);
			}

			$params[$hash]->def('page_title', $title);
			$params[$hash]->def('page_description', $description);
			$params[$hash]->def('page_rights', $rights);
			$params[$hash]->def('robots', $robots);
		}

		return $params[$hash];
	}

	/**
	 * Return a reference to the JPathway object.
	 *
	 * @param   string  $name     The name of the application.
	 * @param   array   $options  An optional associative array of configuration settings.
	 *
	 * @return  JPathway  A JPathway object
	 *
	 * @since   3.2
	 */
	public function getPathway($name = 'sellacious', $options = array())
	{
		return parent::getPathway($name, $options);
	}

	/**
	 * Return a reference to the JRouter object.
	 *
	 * @param   string  $name     The name of the application.
	 * @param   array   $options  An optional associative array of configuration settings.
	 *
	 * @return    JRouter
	 *
	 * @since    3.2
	 */
	public static function getRouter($name = 'sellacious', array $options = array())
	{
		$config          = JFactory::getConfig();
		$options['mode'] = $config->get('sef');

		return parent::getRouter($name, $options);
	}

	/**
	 * Gets the name of the current template.
	 *
	 * @param   boolean  $params  True to return the template parameters
	 *
	 * @return  mixed  The name of the template.
	 *
	 * @since   3.2
	 * @throws  InvalidArgumentException
	 */
	public function getTemplate($params = false)
	{
		if (is_object($this->template))
		{
			if (!file_exists(JPATH_THEMES . '/' . $this->template->template . '/index.php'))
			{
				throw new InvalidArgumentException(JText::sprintf('JERROR_COULD_NOT_FIND_TEMPLATE', $this->template->template));
			}

			if ($params)
			{
				return $this->template;
			}

			return $this->template->template;
		}

		// Get the id of the active menu item
		$menu = $this->getMenu();
		$item = $menu->getActive();

		if (!$item)
		{
			$item = $menu->getItem($this->input->getInt('Itemid', null));
		}

		$id = 0;

		if (is_object($item))
		{
			// Valid item retrieved
			$id = $item->template_style_id;
		}

		$tid = $this->input->getUint('templateStyle', 0);

		if (is_numeric($tid) && (int)$tid > 0)
		{
			$id = (int)$tid;
		}

		$cache = JFactory::getCache('com_templates', '');
		$tag   = $this->_language_filter ? $this->getLanguage()->getTag() : '';

		if (!$templates = $cache->get('templates0' . $tag))
		{
			// Load styles
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('id, home, template, s.params')
				->from('#__template_styles as s')
				->where('s.client_id = ' . $this->getClientId())
				->where('e.enabled = 1')
				->join('LEFT', '#__extensions as e ON e.element=s.template AND e.type=' . $db->q('template') . ' AND e.client_id=s.client_id');

			$db->setQuery($query);
			$templates = $db->loadObjectList('id');

			foreach ($templates as &$template)
			{
				$registry = new Registry;
				$registry->loadString($template->params);
				$template->params = $registry;

				// Create home element
				if ($template->home == 1 && !isset($templates[0]) || $this->_language_filter && $template->home == $tag)
				{
					$templates[0] = clone $template;
				}
			}

			$cache->store($templates, 'templates0' . $tag);
		}

		$template = isset($templates[$id]) ? $templates[$id] : $templates[0];

		// Allows for overriding the active template from the request
		$template->template = $this->input->getCmd('template', $template->template);

		// Need to filter the default value as well
		$template->template = JFilterInput::getInstance()->clean($template->template, 'cmd');

		// Fallback template
		if (!file_exists(JPATH_THEMES . '/' . $template->template . '/index.php'))
		{
			$this->enqueueMessage(JText::_('JERROR_ALERTNOTEMPLATE'), 'error');

			// Try to find data for 'sellacious' template
			$original_tmpl = $template->template;

			foreach ($templates as $tmpl)
			{
				if ($tmpl->template == 'sellacious')
				{
					$template = $tmpl;
					break;
				}
			}

			// Check, the data were found and if template really exists
			if (!file_exists(JPATH_THEMES . '/' . $template->template . '/index.php'))
			{
				throw new InvalidArgumentException(JText::sprintf('JERROR_COULD_NOT_FIND_TEMPLATE', $original_tmpl));
			}
		}

		// Cache the result
		$this->template = $template;

		if ($params)
		{
			return $template;
		}

		return $template->template;
	}

	/**
	 * Initialise the application.
	 *
	 * @param   array  $options  An optional associative array of configuration settings.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function initialiseApp($options = array())
	{
		$user = JFactory::getUser();

		// If the user is a guest we populate it with the guest user group.
		if ($user->guest)
		{
			$registry       = JComponentHelper::getParams('com_users');
			$guestUsergroup = $registry->get('guest_usergroup', 1);
			$user->groups   = array($guestUsergroup);
		}

		// If a language was specified it has priority, otherwise use user or default language settings
		JPluginHelper::importPlugin('system', 'languagefilter');

		if (empty($options['language']))
		{
			// Detect the specified language
			$lang = $this->input->get('lang', null);

			// Make sure that the language exists
			if ($lang && JLanguage::exists($lang))
			{
				$options['language'] = $lang;
			}
		}

		if (empty($options['language']))
		{
			// Detect the specified language
			$lang = $this->getUserState('application.lang');

			// Make sure that the language exists
			if ($lang && JLanguage::exists($lang))
			{
				$options['language'] = $lang;
			}
		}

		if (empty($options['language']))
		{
			// Detect the specified language
			$lang = $user->getParam('sellacious_language', null);

			// Make sure that the user's language exists
			if ($lang && JLanguage::exists($lang))
			{
				$options['language'] = $lang;
			}
		}

		if ($this->_language_filter && empty($options['language']))
		{
			// Detect cookie language
			$lang = $this->input->cookie->get(md5($this->get('secret') . 'language'), null, 'string');

			// Make sure that the user's language exists
			if ($lang && JLanguage::exists($lang))
			{
				$options['language'] = $lang;
			}
		}

		if (empty($options['language']))
		{
			// Detect user language
			$lang = $user->getParam('language');

			// Make sure that the user's language exists
			if ($lang && JLanguage::exists($lang))
			{
				$options['language'] = $lang;
			}
		}

		if ($this->_detect_browser && empty($options['language']))
		{
			// Detect browser language
			$lang = JLanguageHelper::detectLanguage();

			// Make sure that the user's language exists
			if ($lang && JLanguage::exists($lang))
			{
				$options['language'] = $lang;
			}
		}

		if (empty($options['language']))
		{
			// Detect default language (use site default)
			$params = JComponentHelper::getParams('com_languages');

			$options['language'] = $params->get('site', $this->get('language', 'en-GB'));
		}

		// One last check to make sure we have something
		if (!JLanguage::exists($options['language']))
		{
			$lang = $this->config->get('language', 'en-GB');

			// As a last ditch fail to english
			$options['language'] = JLanguage::exists($lang) ? $lang : 'en-GB';
		}

		// Update the session storage
		$this->setUserState('application.lang', $options['language']);

		$this->overrideCms();

		// Finish initialisation
		parent::initialiseApp($options);

		JLoader::registerNamespace('Sellacious', JPATH_LIBRARIES . '/sellacious/objects');
		JLoader::registerAlias('JToolbarHelper', 'Sellacious\Toolbar\ToolbarHelper');

		/*
		 * Try the lib_joomla file in the current language (without allowing the loading of the file in the default language)
		 * Fallback to the default language if necessary
		 *
		 * The site and administrator languages are sufficient, no need to load from sellacious application
		 */
		$this->getLanguage()->load('lib_joomla', JPATH_SITE, null, false, true)
			|| $this->getLanguage()->load('lib_joomla', JPATH_ADMINISTRATOR, null, false, true);
	}

	/**
	 * Method to override cms behaviours that has bug or has conflicts with our application layer
	 *
	 * @since   1.4.1
	 */
	protected function overrideCms()
	{
		// Bypass all methods that calls for mooTools js library
		JHtml::register('jhtml.behavior.framework', function () {});
		JHtml::register('jhtml.behavior.modal', function () {});
		JHtml::register('jhtml.behavior.tooltip', function () {});
	}

	/**
	 * Login authentication function
	 *
	 * @param   array  $credentials  Array('username' => string, 'password' => string)
	 * @param   array  $options      Array('remember' => boolean)
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function login($credentials, $options = array())
	{
		// Make sure users are not auto-registered
		$options['autoregister'] = false;

		// Set the application login entry point
		if (!array_key_exists('entry_url', $options))
		{
			$options['entry_url'] = JUri::base() . 'index.php?option=com_users&task=login';
		}

		$result = parent::login($credentials, $options);

		if (!($result instanceof Exception))
		{
			$lang = $this->input->getCmd('lang', null);
			$lang = preg_replace('/[^A-Z-]/i', '', $lang);

			if ($lang)
			{
				$this->setUserState('application.lang', $lang);
			}

			static::purgeMessages();
		}

		return $result;
	}

	/**
	 * Purge the jos_messages table of old messages
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public static function purgeMessages()
	{
		$user   = JFactory::getUser();
		$userid = $user->get('id');

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
					->select('*')
					->from($db->quoteName('#__messages_cfg'))
					->where($db->quoteName('user_id') . ' = ' . (int)$userid, 'AND')
					->where($db->quoteName('cfg_name') . ' = ' . $db->quote('auto_purge'), 'AND');
		$db->setQuery($query);
		$config = $db->loadObject();

		// Check if auto_purge value set
		$purge = is_object($config) && $config->cfg_name == 'auto_purge' ? $config->cfg_value : 7;

		// If purge value is not 0, then allow purging of old messages
		if ($purge > 0)
		{
			// Purge old messages at day set in message configuration
			$past      = JFactory::getDate(time() - $purge * 86400);
			$pastStamp = $past->toSql();

			$query->clear()
				  ->delete($db->quoteName('#__messages'))
				  ->where($db->quoteName('date_time') . ' < ' . $db->Quote($pastStamp), 'AND')
				  ->where($db->quoteName('user_id_to') . ' = ' . (int)$userid, 'AND');
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * Rendering is the process of pushing the document buffers into the template
	 * placeholders, retrieving data from the document and pushing it into
	 * the application response buffer.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function render()
	{
		switch ($this->document->getType())
		{
			case 'feed':
				// No special processing for feeds
				break;

			case 'html':
			default:
				$template  = $this->getTemplate(true);
				$file      = $this->input->getCmd('tmpl', 'index');
				$component = $this->input->getCmd('option', 'com_login');

				if (!$this->get('offline') && $file == 'offline')
				{
					$file = 'index';
				}

				if ($component == 'com_login')
				{
					$file = 'login';
				}

				$this->set('themeFile', $file . '.php');

				if ($this->get('offline') && !JFactory::getUser()->authorise('core.login.offline'))
				{
					$this->setUserState('users.login.form.data', array('return' => JUri::getInstance()->toString()));
					$this->set('themeFile', 'offline.php');
					$this->setHeader('Status', '503 Service Temporarily Unavailable', true);
				}

				if (!is_dir(JPATH_THEMES . '/' . $template->template) && !$this->get('offline'))
				{
					$this->set('themeFile', 'component.php');
				}

				// Ensure themeFile is set by now
				if ($this->get('themeFile') == '')
				{
					$this->set('themeFile', 'index.php');
				}

				break;
		}

		parent::render();
	}

	/**
	 * Route the application.
	 *
	 * Routing is the process of examining the request environment to determine which
	 * component should receive the request. The component optional parameters
	 * are then set in the request object to be processed when the application is being
	 * dispatched.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function route()
	{
		// Execute the parent method
		parent::route();

		$Itemid = $this->input->getInt('Itemid', null);
		$this->authorise($Itemid);
	}

	/**
	 * Set the current state of the detect browser option.
	 *
	 * @param   boolean  $state  The new state of the detect browser option
	 *
	 * @return  boolean  The previous state
	 *
	 * @since    3.2
	 */
	public function setDetectBrowser($state = false)
	{
		$old                   = $this->_detect_browser;
		$this->_detect_browser = $state;

		return $old;
	}

	/**
	 * Set the current state of the language filter.
	 *
	 * @param   boolean $state The new state of the language filter
	 *
	 * @return    boolean     The previous state
	 *
	 * @since    3.2
	 */
	public function setLanguageFilter($state = false)
	{
		$old                    = $this->_language_filter;
		$this->_language_filter = $state;

		return $old;
	}

	/**
	 * Overrides the default template that would be used
	 *
	 * @param   string  $template     The template name
	 * @param   mixed   $styleParams  The template style parameters
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function setTemplate($template, $styleParams = null)
	{
		if (is_dir(JPATH_THEMES . '/' . $template))
		{
			$this->template           = new stdClass;
			$this->template->template = $template;

			if ($styleParams instanceof Registry)
			{
				$this->template->params = $styleParams;
			}
			else
			{
				$this->template->params = new Registry($styleParams);
			}
		}
	}

	/**
	 * Return the application option string [main component].
	 *
	 * @return  string  The component to access.
	 *
	 * @since   1.5
	 */
	public static function findOption()
	{
		$app    = JFactory::getApplication();
		$option = strtolower($app->input->get('option'));

		$app->loadIdentity();
		$user   = $app->getIdentity();

		// sellacious currently set to use site login access only
		if ($user->get('guest'))
		{
			$app->setUserState('users.login.form.data', array('return' => JUri::getInstance()->toString()));

			$option = 'com_login';
		}
		elseif (!$user->authorise('core.login.site') || ($app->get('offline') && !$user->authorise('core.login.offline')))
		{
			$app->setUserState('users.login.form.data', array('return' => JUri::getInstance()->toString()));
			$app->logout($user->id, array('client_id' => 2));

			$app->redirect('index.php');
		}

		if (empty($option))
		{
			$option = 'com_sellacious';
		}

		$app->input->set('option', $option);

		return $option;
	}
}
