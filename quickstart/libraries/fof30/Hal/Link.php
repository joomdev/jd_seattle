<?php
/**
 * @package     FOF
 * @copyright   Copyright (c)2010-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Hal;

use FOF30\Hal\Exception\InvalidLinkFormat;

defined('_JEXEC') or die;

/**
 * Implementation of the Hypertext Application Language link in PHP.
 *
 * @see http://stateless.co/hal_specification.html
 *
 * @property  $href       string
 * @property  $templated  bool
 * @property  $name       string
 * @property  $hreflang   string
 */
class Link
{
	/**
	 * For indicating the target URI. Corresponds with the ’Target IRI’ as
	 * defined in Web Linking (RFC 5988). This attribute MAY contain a URI
	 * Template (RFC6570) and in which case, SHOULD be complemented by an
	 * additional templated attribute on the link with a boolean value true.
	 *
	 * @var string
	 */
	protected $_href = '';

	/**
	 * This attribute SHOULD be present with a boolean value of true when the
	 * href of the link contains a URI Template (RFC6570).
	 *
	 * @var  boolean
	 */
	protected $_templated = false;

	/**
	 * For distinguishing between Resource and Link elements that share the
	 * same relation
	 *
	 * @var  string
	 */
	protected $_name = null;

	/**
	 * For indicating what the language of the result of dereferencing the link should be.
	 *
	 * @var  string
	 */
	protected $_hreflang = null;

	/**
	 * For labeling the destination of a link with a human-readable identifier.
	 *
	 * @var  string
	 */
	protected $_title = null;

	/**
	 * Public constructor of a FOFHalLink object
	 *
	 * @param   string   $href       See $this->_href
	 * @param   boolean  $templated  See $this->_templated
	 * @param   string   $name       See $this->_name
	 * @param   string   $hreflang   See $this->_hreflang
	 * @param   string   $title      See $this->_title
	 *
	 * @throws  \InvalidArgumentException  If $href is empty
	 */
	public function __construct($href, $templated = false, $name = null, $hreflang = null, $title = null)
	{
		if (empty($href))
		{
			throw new InvalidLinkFormat;
		}

		$this->_href = $href;
		$this->_templated = $templated;
		$this->_name = $name;
		$this->_hreflang = $hreflang;
		$this->_title = $title;
	}

	/**
	 * Is this a valid link? Checks the existence of required fields, not their
	 * values.
	 *
	 * @return  boolean
	 */
	public function check()
	{
		return !empty($this->_href);
	}

	/**
	 * Magic getter for the protected properties
	 *
	 * @param   string  $name  The name of the property to retrieve, sans the underscore
	 *
	 * @return  mixed  Null will always be returned if the property doesn't exist
	 */
	public function __get($name)
	{
		$property = '_' . $name;

		if (property_exists($this, $property))
		{
			return $this->$property;
		}
		else
		{
			return null;
		}
	}

	/**
	 * Magic setter for the protected properties
	 *
	 * @param   string  $name   The name of the property to set, sans the underscore
	 * @param   mixed   $value  The value of the property to set
	 *
	 * @return  void
	 */
	public function __set($name, $value)
	{
		if (($name == 'href') && empty($value))
		{
			return;
		}

		$property = '_' . $name;

		if (property_exists($this, $property))
		{
			$this->$property = $value;
		}
	}
}
