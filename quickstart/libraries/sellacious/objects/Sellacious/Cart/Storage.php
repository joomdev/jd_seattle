<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Cart;

use Sellacious\Cart;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * Sellacious cart storage handler interface.
 *
 * @since  1.4.5
 */
abstract class Storage
{
	/**
	 * @var    static[]  Storage instances container.
	 *
	 * @since  1.4.5
	 */
	protected static $instances = array();

	/**
	 * @var    \SellaciousHelper  Sellacious application helper global instance
	 *
	 * @since  1.4.5
	 */
	protected $helper;

	/**
	 * Constructor for the storage handler
	 *
	 * @param   Cart  $cart  The cart object instance
	 *
	 * @throws  \Exception
	 *
	 * @since  1.4.5
	 */
	public function __construct($cart)
	{
		$this->cart   = $cart;
		$this->helper = \SellaciousHelper::getInstance();
	}

	/**
	 * Returns a cart storage handler object, only creating it if it doesn't already exist.
	 *
	 * @param   Cart    $cart  The cart object instance
	 * @param   string  $name  The cart storage handler to instantiate
	 *
	 * @return  static
	 * @throws  \InvalidArgumentException
	 *
	 * @since   1.4.5
	 */
	public static function getInstance($cart, $name = 'database')
	{
		$name = strtolower(\JFilterInput::getInstance()->clean($name, 'cmd'));
		$key  = $name . '-' . (int) $cart->getUser()->id;

		if (strlen($name) == 0)
		{
			throw new \InvalidArgumentException(\JText::_('COM_SELLACIOUS_CART_STORAGE_NOT_SPECIFIED'));
		}

		if (empty(static::$instances[$key]))
		{
			$class = '\\Sellacious\\Cart\\Storage\\' . ucfirst($name);

			if (!class_exists($class))
			{
				// Classes are autoload-able
				throw new \RuntimeException(\JText::sprintf('COM_SELLACIOUS_CART_STORAGE_NOT_LOADED', ucfirst($name)));
			}

			static::$instances[$key] = new $class($cart);
		}

		return static::$instances[$key];
	}

	/**
	 * Assign the current cart to the logged in user if the cart is currently referred to by the token
	 *
	 * @param   string  $token
	 *
	 * @return  bool
	 *
	 * @since   1.4.5
	 */
	abstract public function assign($token);

	/**
	 * Load the cart object data from storage
	 *
	 * @return  Registry
	 * @throws  \Exception
	 *
	 * @since   1.4.5
	 */
	abstract public function load();

	/**
	 * Load the cart items instances data from storage
	 *
	 * @return  Item[]
	 * @throws  \Exception
	 *
	 * @since   1.4.5
	 */
	abstract public function loadItems();

	/**
	 * Store the object instance data to persistent cart storage
	 *
	 * @param   bool  $infoOnly  Whether to store cart info only (=true) or include items too (=false, default)
	 *
	 * @return  bool
	 * @throws  \Exception
	 *
	 * @since   1.4.5
	 */
	abstract public function store($infoOnly = false);
}
