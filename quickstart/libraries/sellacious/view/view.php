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
 * Base class for a Sellacious Views
 *
 * Class holding methods for displaying presentation data.
 *
 * @method   string  _createFileName(string $type, array $parts = array())
 *
 * @package  Sellacious
 *
 * @since  3.0
 */
class SellaciousView extends JViewLegacy
{
	/**
	 * @var SellaciousHelper
	 *
	 * @since  1.0.0
	 */
	public $helper;

	/**
	 * @var  \JApplicationCms
	 *
	 * @since   1.6.0
	 */
	protected $app;

	/**
	 * The name of the view
	 *
	 * @var    array
	 * @since  3.0
	 */
	protected $_option = null;

	/**
	 * Layout name
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $_layout;

	/**
	 * The output of the template script.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $_output = null;

	/**
	 * The set of search directories for resources (templates)
	 *
	 * @var    array
	 * @since  3.0
	 */
	protected $_path = array('template' => array(), 'helper' => array());

	/**
	 * The name of the default template source file.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $_template = null;

	/**
	 * Constructor
	 *
	 * @param   array  $config  A named configuration array for object construction.<br/>
	 *                          name: the name (optional) of the view (defaults to the view class name suffix).<br/>
	 *                          charset: the character set to use for display<br/>
	 *                          escape: the name (optional) of the function to use for escaping strings<br/>
	 *                          base_path: the parent path (optional) of the views directory (defaults to the component folder)<br/>
	 *                          template_path: the path (optional) of the layout directory (defaults to base_path + /views/ + view name<br/>
	 *                          helper_path: the path (optional) of the helper files (defaults to base_path + /helpers/)<br/>
	 *                          layout: the layout (optional) to use to display the view<br/>
	 *
	 * @throws  Exception
	 *
	 * @see     JView
	 * @since   3.0
	 */
	public function __construct($config = array())
	{
		$config['template_path'] = JPATH_BASE . '/components/' . $this->getOption() . '/layouts/views/' . $this->getName();

		parent::__construct($config);

		$this->app    = JFactory::getApplication();
		$this->helper = SellaciousHelper::getInstance();
	}

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @see     JViewLegacy::loadTemplate()
	 * @since   12.2
	 */
	public function display($tpl = null)
	{
		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onBeforeDisplayView', array('com_sellacious.' . $this->getName(), &$this));

		$display = parent::display($tpl);

		if ($this->app->isSite() && $this->document->getType() == 'html'&& $this->app->input->get('tmpl', 'index') == 'index')
		{
			echo $this->helper->core->renderBrandFooter();
		}

		return $display;
	}

	/**
	 * Method to get the view name
	 *
	 * The model name by default parsed using the class name, or it can be set
	 * by passing a $config['name'] in the class constructor
	 *
	 * @return  string  The name of the model
	 *
	 * @since   3.0
	 * @throws  \Exception
	 */
	public function getOption()
	{
		if (empty($this->_option))
		{
			$classname = get_class($this);
			$viewpos = strpos($classname, 'View');

			if ($viewpos == 0)
			{
				throw new \Exception(\JText::_('JLIB_APPLICATION_ERROR_VIEW_GET_NAME'), 500);
			}

			$this->_option = strtolower('com_' . substr($classname, 0, $viewpos));
		}

		return $this->_option;
	}

	/**
	 * Load a template file -- first look in the templates folder for an override
	 *
	 * @param   string  $tpl      The name of the template source file; automatically searches the template paths and compiles as needed.
	 * @param   mixed   $tplData  The data object to be used by the layout (optional)
	 *
	 * @return  string
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.2
	 */
	public function loadTemplate($tpl = null, $tplData = null)
	{
		// Clear prior output
		$this->_output = null;

		$template       = $this->app->getTemplate();
		$layout         = $this->getLayout();
		$layoutTemplate = $this->getLayoutTemplate();

		// Create the template file name based on the layout
		$file = isset($tpl) ? $layout . '_' . $tpl : $layout;

		// Clean the file name
		$file = preg_replace('/[^A-Z0-9_\.-]/i', '', $file);
		$tpl  = isset($tpl) ? preg_replace('/[^A-Z0-9_\.-]/i', '', $tpl) : $tpl;

		// Load the language file for the template
		$lang = JFactory::getLanguage();
		$lang->load('tpl_' . $template, JPATH_BASE, null, false, true)
		|| $lang->load('tpl_' . $template, JPATH_THEMES . "/$template", null, false, true);

		// Change the template folder if alternative layout is in different template
		if (isset($layoutTemplate) && $layoutTemplate != '_' && $layoutTemplate != $template)
		{
			$this->_path['template'] = str_replace($template, $layoutTemplate, $this->_path['template']);
		}

		// Load the template script
		jimport('joomla.filesystem.path');
		$filetofind      = $this->_createFileName('template', array('name' => $file));
		$this->_template = JPath::find($this->_path['template'], $filetofind);

		// If alternate layout can't be found, fall back to default layout
		if ($this->_template == false)
		{
			$filetofind      = $this->_createFileName('', array('name' => 'default' . (isset($tpl) ? '_' . $tpl : $tpl)));
			$this->_template = JPath::find($this->_path['template'], $filetofind);
		}

		// If default layout can't be found, quit!
		if ($this->_template == false)
		{
			throw new Exception(JText::sprintf('JLIB_APPLICATION_ERROR_LAYOUTFILE_NOT_FOUND', $file), 500);
		}

		// Unset so as not to introduce into template scope
		unset($tpl);
		unset($file);
		unset($layout);
		unset($template);
		unset($layoutTemplate);
		unset($filetofind);
		unset($lang);

		// Never allow a 'this' property
		if (isset($this->this))
		{
			unset($this->this);
		}

		// Start capturing output into a buffer
		ob_start();

		// Include the requested template filename in the local scope (this will execute the view logic).
		include $this->_template;

		// Done with the requested template; get the buffer and clear it.
		$this->_output = ob_get_contents();

		ob_end_clean();

		return $this->_output;
	}
}
