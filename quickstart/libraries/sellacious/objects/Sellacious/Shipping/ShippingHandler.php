<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Shipping;

// no direct access.
defined('_JEXEC') or die;

/**
 * This base object will be immutable, however this can be extended
 * and the child classes may allow property write if needed.
 *
 * @package  Sellacious\Shipping
 *
 * @property-read  $name
 * @property-read  $title
 * @property-read  $rateQuoteSupported
 * @property-read  $printLabelSupported
 * @property-read  $trackingSupported
 *
 * @since   1.5.2
 */
class ShippingHandler
{
	/**
	 * @var   string
	 *
	 * @since   1.5.2
	 */
	protected $_name;

	/**
	 * @var   string
	 *
	 * @since   1.5.2
	 */
	protected $_title;

	/**
	 * @var   bool
	 *
	 * @since   1.5.2
	 */
	protected $_rateQuoteSupported;

	/**
	 * @var   bool
	 *
	 * @since   1.5.2
	 */
	protected $_printLabelSupported;

	/**
	 * @var   bool
	 *
	 * @since   1.5.2
	 */
	protected $_trackingSupported;

	/**
	 * Constructor
	 *
	 * @param   string  $name
	 * @param   string  $title
	 * @param   bool    $rateQuoteSupported
	 * @param   bool    $printLabelSupported
	 * @param   bool    $trackingSupported
	 *
	 * @since   1.5.2
	 */
	public function __construct($name, $title, $rateQuoteSupported = true, $printLabelSupported = false, $trackingSupported = false)
	{
		$this->_name                = (string) $name;
		$this->_title               = (string) $title;
		$this->_rateQuoteSupported  = (bool) $rateQuoteSupported;
		$this->_printLabelSupported = (bool) $printLabelSupported;
		$this->_trackingSupported   = (bool) $trackingSupported;
	}

	/**
	 * This is an immutable object
	 *
	 * @param   string  $name  The property name
	 *
	 * @return  mixed
	 *
	 * @since   1.5.2
	 */
	public function __get($name)
	{
		$name = '_' . $name;

		if (isset($this->$name))
		{
			return $this->$name;
		}

		return null;
	}

	/**
	 * Convert to string (JSON)
	 *
	 * @return  string
	 *
	 * @since   1.5.2
	 */
	public function __toString()
	{
		$array = array(
			'name'                 => $this->name,
			'title'                => $this->title,
			'rate_quote_supported' => $this->rateQuoteSupported,
			'label_supported'      => $this->printLabelSupported,
			'tracking_supported'   => $this->trackingSupported,
		);

		return json_encode($array);
	}
}
