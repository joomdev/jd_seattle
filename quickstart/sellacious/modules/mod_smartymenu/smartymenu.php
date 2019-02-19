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
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Tree based class to render the admin menu
 *
 * @since  1.5
 */
class JAdminCssSmartyMenu extends JObject
{
	/**
	 * CSS string to add to document head
	 *
	 * @var  string
	 */
	protected $_css = null;

	/**
	 * Root node
	 *
	 * @var  object
	 */
	protected $_root = null;

	/**
	 * Current working node
	 *
	 * @var  object
	 */
	protected $_current = null;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->_root    = new JSmartyMenuNode('ROOT');
		$this->_current = &$this->_root;
	}

	/**
	 * Method to add a child
	 *
	 * @param   JSmartyMenuNode  $node        The node to process
	 * @param   boolean          $setCurrent  True to set as current working node
	 *
	 * @return  void
	 */
	public function addChild(JSmartyMenuNode $node, $setCurrent = false)
	{
		$this->_current->addChild($node);

		if ($setCurrent)
		{
			$this->_current = &$node;
		}
	}

	/**
	 * Method to get the parent
	 *
	 * @return  void
	 */
	public function getParent()
	{
		$this->_current = &$this->_current->getParent();
	}

	/**
	 * Method to get the parent
	 *
	 * @return  void
	 */
	public function reset()
	{
		$this->_current = &$this->_root;
	}

	/**
	 * Method to add a separator node
	 *
	 * @return  void
	 */
	public function addSeparator()
	{
		$this->addChild(new JSmartyMenuNode(null, null, 'separator', false));
	}

	/**
	 * Method to render the menu
	 *
	 * @param   string $id    The id of the menu to be rendered
	 * @param   string $class The class of the menu to be rendered
	 *
	 * @return  void
	 */
	public function renderMenu($id = 'menu', $class = '')
	{
		$depth = 1;

		echo "<nav id='$id' class='$class'>\n";

		// Recurse through children if they exist
		while ($this->_current->hasChildren())
		{
			echo "<ul>\n";

			foreach ($this->_current->getChildren() as $child)
			{
				$this->_current = &$child;
				$this->renderLevel($depth++);
			}

			echo "</ul>\n";
		}

		echo "</nav>\n";

		if ($this->_css)
		{
			// Add style to document head
			$doc = JFactory::getDocument();
			$doc->addStyleDeclaration($this->_css);
		}
	}

	/**
	 * Method to render a given level of a menu
	 *
	 * @param   integer $depth The level of the menu to be rendered
	 *
	 * @return  void
	 */
	public function renderLevel($depth)
	{
		if ($this->_current->class == 'separator')
		{
			// We do not handle separators, plus they do not have children ever
			return;
		}

		// Build the CSS class suffix
		$class = '';

		if ($this->_current->active)
		{
			$class .= 'active';
		}

		// Print the item
		echo "<li class=\"" . $class . "\">";

		$attr = '';
		$icon = '';
		$attr .= $this->_current->link != null ? ' href="' . $this->_current->link . '"' : ' href="#"';
		$attr .= $this->_current->target != null ? ' target="' . $this->_current->target . '"' : '';
		$title = $this->_current->title != null ? $this->_current->title : '';

		// $title = $this->_current->hasChildren() ? '<span class="menu-item-parent">' . $title . '</span>' : $title;
		$title = '<span class="menu-item-parent">' . $title . '</span>';

		$iconClass = $this->getIconClass($this->_current->class);

		if (!empty($iconClass))
		{
			$icon = '<i class="fa fa-lg fa-fw fa-' . $iconClass . '"></i> ';
		}

		echo '<a' . $attr . '>' . $icon . $title . '</a>';

		// Recurse through children if they exist
		while ($this->_current->hasChildren())
		{
			echo "<ul>\n";

			foreach ($this->_current->getChildren() as $child)
			{
				$this->_current = &$child;
				$this->renderLevel($depth++);
			}

			echo "</ul>\n";
		}

		echo "</li>\n";
	}

	/**
	 * Method to get the CSS class name for an icon identifier or create one if
	 * a custom image path is passed as the identifier
	 *
	 * @param   string  $identifier Icon identification string
	 *
	 * @return  string  CSS class name
	 *
	 * @since   1.5
	 */
	public function getIconClass($identifier)
	{
		static $classes;

		// Initialise the known classes array if it does not exist
		if (!is_array($classes))
		{
			// List all classes names used for icon
			$classes = array();
		}

		$class = explode(":", $identifier);

		return ArrayHelper::getValue($classes, $identifier, $class[1]);
	}
}

/**
 * A Node for JAdminCssMenu
 *
 * @see    JAdminCssMenu
 * @since  1.5
 */
class JSmartyMenuNode extends JObject
{
	/**
	 * Node Title
	 *
	 * @var  string
	 */
	public $title = null;

	/**
	 * Node Id
	 *
	 * @var  string
	 */
	public $id = null;

	/**
	 * Node Link
	 *
	 * @var  string
	 */
	public $link = null;

	/**
	 * Link Target
	 *
	 * @var  string
	 */
	public $target = null;

	/**
	 * CSS Class for node
	 *
	 * @var  string
	 */
	public $class = null;

	/**
	 * Active Node?
	 *
	 * @var  boolean
	 */
	public $active = false;

	/**
	 * Parent node
	 *
	 * @var  JSmartyMenuNode
	 */
	protected $_parent = null;

	/**
	 * Array of Children
	 *
	 * @var  array
	 */
	protected $_children = array();

	/**
	 * Constructor for the class.
	 *
	 * @param   string   $title      The title of the node
	 * @param   string   $link       The node link
	 * @param   string   $class      The CSS class for the node
	 * @param   boolean  $active     True if node is active, false otherwise
	 * @param   string   $target     The link target
	 * @param   string   $titleicon  The title icon for the node
	 */
	public function __construct($title, $link = null, $class = null, $active = false, $target = null, $titleicon = null)
	{
		$this->title  = $titleicon ? $title . $titleicon : $title;
		$this->link   = JFilterOutput::ampReplace($link);
		$this->class  = $class;
		$this->active = $active;

		$this->id = null;

		if (!empty($link) && $link !== '#')
		{
			$uri   = new JUri($link);
			$query = $uri->getQuery();

			$this->id = preg_replace('/[^A-Z0-9-]+/i', '-', $query);
		}

		$this->target = $target;

		parent::__construct();
	}

	/**
	 * Add child to this node
	 *
	 * If the child already has a parent, the link is unset
	 *
	 * @param   JSmartyMenuNode  &$child  The child to be added
	 *
	 * @return  void
	 */
	public function addChild(JSmartyMenuNode &$child)
	{
		$child->setParent($this);
	}

	/**
	 * Set the parent of a this node
	 *
	 * If the node already has a parent, the link is unset
	 *
	 * @param   JSmartyMenuNode  &$parent  The JSmartyMenuNode for parent to be set or null
	 *
	 * @return  void
	 */
	public function setParent(JSmartyMenuNode &$parent = null)
	{
		$hash = spl_object_hash($this);

		if (!is_null($this->_parent))
		{
			unset($this->_parent->children[$hash]);
		}

		if (!is_null($parent))
		{
			$parent->_children[$hash] = &$this;
		}

		$this->_parent = &$parent;
	}

	/**
	 * Get the children of this node
	 *
	 * @return  array  The children
	 */
	public function &getChildren()
	{
		return $this->_children;
	}

	/**
	 * Get the parent of this node
	 *
	 * @return  mixed  JSmartyMenuNode object with the parent or null for no parent
	 */
	public function &getParent()
	{
		return $this->_parent;
	}

	/**
	 * Test if this node has children
	 *
	 * @return  boolean  True if there are children
	 */
	public function hasChildren()
	{
		return (bool) count($this->_children);
	}

	/**
	 * Test if this node has a parent
	 *
	 * @return  boolean  True if there is a parent
	 */
	public function hasParent()
	{
		return $this->getParent() != null;
	}
}
