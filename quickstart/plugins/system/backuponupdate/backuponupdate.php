<?php
/**
 * @package    AkeebaBackup
 * @subpackage backuponupdate
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license    GNU General Public License version 3, or later
 *
 * @since      3.3
 */
defined('_JEXEC') or die();

if (!version_compare(PHP_VERSION, '5.4.0', '>='))
{
	return;
}

// Why, oh why, are you people using eAccelerator? Seriously, what's wrong with you, people?!
if (function_exists('eaccelerator_info'))
{
	$isBrokenCachingEnabled = true;

	if (function_exists('ini_get') && !ini_get('eaccelerator.enable'))
	{
		$isBrokenCachingEnabled = false;
	}

	if ($isBrokenCachingEnabled)
	{
		/**
		 * I know that this define seems pointless since I am returning. This means that we are exiting the file and
		 * the plugin class isn't defined, so Joomla cannot possibly use it.
		 *
		 * LOL. That is how PHP works. Not how that OBSOLETE, BROKEN PILE OF ROTTING BYTES called eAccelerator mangles
		 * your code.
		 *
		 * That disgusting piece of bit rot will exit right after the return statement below BUT it will STILL define
		 * the class. That's right. It ignores ALL THE CODE between here and the class declaration and parses the
		 * class declaration o_O  Therefore the only way to actually NOT load the  plugin when you are using it on
		 * a server where an indescribable character posing as a sysadmin has installed and enabled eAccelerator is to
		 * define a constant and use it to return from the constructor method, therefore forcing PHP to return null
		 * instead of an object. This prompts Joomla to not do anything with the plugin.
		 */
		if (!defined('AKEEBA_EACCELERATOR_IS_SO_BORKED_IT_DOES_NOT_EVEN_RETURN'))
		{
			define('AKEEBA_EACCELERATOR_IS_SO_BORKED_IT_DOES_NOT_EVEN_RETURN', 3245);
		}

		return;
	}
}

// Make sure Akeeba Backup is installed
if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_akeeba'))
{
	return;
}

// Load FOF
if (!defined('FOF30_INCLUDED') && !@include_once(JPATH_LIBRARIES . '/fof30/include.php'))
{
	return;
}

JLoader::import('joomla.filesystem.file');
$db = JFactory::getDbo();

// Is Akeeba Backup enabled?
$query = $db->getQuery(true)
            ->select($db->qn('enabled'))
            ->from($db->qn('#__extensions'))
            ->where($db->qn('element') . ' = ' . $db->q('com_akeeba'))
            ->where($db->qn('type') . ' = ' . $db->q('component'));
$db->setQuery($query);
$enabled = $db->loadResult();

if (!$enabled)
{
	return;
}

JLoader::import('joomla.application.plugin');

class plgSystemBackuponupdate extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param       object $subject The object to observe
	 * @param       array  $config  An array that holds the plugin configuration
	 *
	 * @since       2.5
	 */
	public function __construct(& $subject, $config)
	{
		/**
		 * I know that this piece of code cannot possibly be executed since I have already returned BEFORE declaring
		 * the class when eAccelerator is detected. However, eAccelerator is a GINORMOUS, STINKY PILE OF BULL CRAP. The
		 * stupid thing will return above BUT it will also declare the class EVEN THOUGH according to how PHP works
		 * this part of the code should be unreachable o_O Therefore I have to define this constant and exit the
		 * constructor when we have already determined that this class MUST NOT be defined. Because screw you
		 * eAccelerator, that's why.
		 */
		if (defined('AKEEBA_EACCELERATOR_IS_SO_BORKED_IT_DOES_NOT_EVEN_RETURN'))
		{
			return;
		}

		parent::__construct($subject, $config);
	}

	public function onAfterInitialise()
	{
		// Make sure this is the back-end
		$app = JFactory::getApplication();

		if (!in_array($app->getName(), array('administrator', 'admin')))
		{
			return;
		}

		// Handle the flag toggle through AJAX
		$ji        = new JInput();
		$toggleParam = $ji->getCmd('_akeeba_backup_on_update_toggle');

		if ($toggleParam && ($toggleParam == JFactory::getSession()->getToken()))
		{
			$this->toggleBoUFlag();

			return;
		}

		// Make sure we are active
		if ($this->getBoUFlag() != 1)
		{
			return;
		}

		// Get the input variables
		$component = $ji->getCmd('option', '');
		$task      = $ji->getCmd('task', '');
		$backedup  = $ji->getInt('is_backed_up', 0);

		// Perform a redirection on Joomla! Update download or install task, unless we have already backed up the site
		if (($component == 'com_joomlaupdate') && ($task == 'update.install') && !$backedup)
		{
			// Get the backup profile ID
			$profileId = (int) $this->params->get('profileid', 1);

			if ($profileId <= 0)
			{
				$profileId = 1;
			}

			$jtoken = JFactory::getSession()->getFormToken();

			// Get the return URL
			$return_url = JUri::base() . 'index.php?option=com_joomlaupdate&task=update.install&is_backed_up=1&'.$jtoken.'=1';

			// Get the redirect URL
			$redirect_url = JUri::base() . 'index.php?option=com_akeeba&view=Backup&autostart=1&returnurl=' . urlencode($return_url) . '&profileid=' . $profileId . "&$jtoken=1";

			// Perform the redirection
			$app = JFactory::getApplication();
			$app->redirect($redirect_url);
		}
	}

	/**
	 * Renders the Backup on Update status icon in the Joomla! backend.
	 *
	 * We use a bit of fine trickery to accomplish that. The onAfterModuleList event is triggered after Joomla! has
	 * loaded a list of the modules to render on the page. We use that event to inject a fake module object of type
	 * mod_custom with the HTML we want to render in the 'status' position of the template.
	 *
	 * @param   array  $modules  The array of module objects passed to us by Joomla!
	 *
	 * @since   5.4.1
	 * @throws  Exception
	 */
	public function onAfterModuleList(&$modules)
	{
		$app = JFactory::getApplication();

		// Only work when format=html (since we try adding CSS and Javascript on the page which is only valid in HTML).
		if ($app->input->getCmd('format', 'html') != 'html')
		{
			return;
		}

		// Am I in the administrator application to begin with?
		if (version_compare(JVERSION, '3.7.0', 'lt'))
		{
			$isAdmin = $app->isAdmin();
		}
		else
		{
			$isAdmin = $app->isClient('administrator');
		}

		if (!$isAdmin)
		{
			return;
		}

		// Load the language
		$this->loadLanguage();

		JHtml::_('bootstrap.popover');

		try
		{
			/**
			 * Apparently you may have format=html with an application that returns no document...?! I can't see how it's
			 * possible lest a 3PD has screwed up. In any case, this happened in tickets 28218, 28223, 28224 and 28225. My
			 * workaround is to first check if the application can and does return a document. If not, try to get the document
			 * via JFactory (legacy method). If that fails too, skip the "Disable plugin" feature altogether.
			 */
			$document = null;

			if (method_exists($app, 'getDocument'))
			{
				$document = $app->getDocument();
			}

			if (is_null($document) || !method_exists($document, 'addStyleDeclaration'))
			{
				/**
				 * Don't remove the class_exists. Joomla! 3.8 will have JFactor as an alias to a namespaced class so I might
				 * need to load it with the class_exists trick. As for the method_exists, it's us trying to make sure future
				 * versions of Joomla! won't break anything.
				 */
				if (class_exists('JFactory', true) && method_exists('JFactory', 'getDocument'))
				{
					$document = JFactory::getDocument();
				}
			}

			/**
			 * Now, if the document is still unset (a 3PD seriously cocked up a JApplicationCms subclass) OR the document is
			 * quite obviously not JDocumentHtml (which means a 3PD should be tarred, feathered and stringed for cocking up an
			 * application AND a document subclass) we have to skip our "Disable plugin" feature since it, well, not work at
			 * all.
			 */
			if (is_null($document) || !method_exists($document, 'addStyleDeclaration'))
			{
				return;
			}

			$isJoomla4 = version_compare(JVERSION, '3.999999.999999', 'gt');
			$baseDocumentName = $isJoomla4 ? 'joomla4' : 'default';

			$document->addStyleDeclaration($this->loadTemplate($baseDocumentName . '.css'));

			$fakeModule = (object)[
				'id' => -1,
				'title' => 'Backup on Update',
				'module' => 'mod_custom',
				'position' => 'status',
				'content' => $this->loadTemplate($baseDocumentName . '.html', [
					'active' => $this->getBoUFlag()
				]),
				'showtitle' => 0,
				'params' => '{"prepare_content":"0","layout":"_:default","moduleclass_sfx":"","cache":"0","cache_time":"1","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}',
				'menuid' => 0,
			];
		}
		catch (Exception $e)
		{
			return;
		}


		$modules[] = $fakeModule;
	}

	/**
	 * Load a plugin layout file. These files can be overridden with standard Joomla! template overrides.
	 *
	 * @param   string  $layout  The layout file to load
	 * @param   array   $params  An array passed verbatim to the layout file as the `$params` variable
	 *
	 * @return  string  The rendered contents of the file
	 *
	 * @since   5.4.1
	 */
	private function loadTemplate($layout, array $params = [])
	{
		$file = JPluginHelper::getLayoutPath('system', 'backuponupdate', $layout);

		ob_start();

		require_once $file;

		$ret = ob_get_clean();

		return $ret;
	}

	private function getBoUFlag()
	{
		return JFactory::getSession()->get('active', 1, 'plg_system_backuponupdate');
	}

	private function toggleBoUFlag()
	{
		$status = 1 - $this->getBoUFlag();

		JFactory::getSession()->set('active', $status, 'plg_system_backuponupdate');
	}
}
