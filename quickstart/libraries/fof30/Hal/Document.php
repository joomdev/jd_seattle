<?php
/**
 * @package     FOF
 * @copyright   Copyright (c)2010-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Hal;

use FOF30\Hal\Exception\InvalidRenderFormat;
use FOF30\Hal\Render\RenderInterface;

defined('_JEXEC') or die;

/**
 * Implementation of the Hypertext Application Language document in PHP. It can
 * be used to provide hypermedia in a web service context.
 *
 * @see http://stateless.co/hal_specification.html
 */
class Document
{
	/**
	 * The collection of links of this document
	 *
	 * @var   \FOF30\Hal\Links
	 */
	private $_links = null;

	/**
	 * The data (resource state or collection of resource state objects) of the
	 * document.
	 *
	 * @var   array
	 */
	private $_data = null;

	/**
	 * Embedded documents. This is an array of FOFHalDocument instances.
	 *
	 * @var   array
	 */
	private $_embedded = array();

	/**
	 * When $_data is an array we'll output the list of data under this key
	 * (JSON) or tag (XML)
	 *
	 * @var   string
	 */
	private $_dataKey = '_list';

	/**
	 * Public constructor
	 *
	 * @param   mixed  $data  The data of the document (usually, the resource state)
	 */
	public function __construct($data = null)
	{
		$this->_data = $data;
		$this->_links = new Links;
	}

	/**
	 * Add a link to the document
	 *
	 * @param   string   $rel        The relation of the link to the document.
	 *                               See RFC 5988 http://tools.ietf.org/html/rfc5988#section-6.2.2 A document MUST always have
	 *                               a "self" link.
	 * @param   Link     $link       The actual link object
	 * @param   boolean  $overwrite  When false and a link of $rel relation exists, an array of links is created. Otherwise the
	 *                              existing link is overwriten with the new one
	 *
	 * @see Links::addLink
	 *
	 * @return  boolean  True if the link was added to the collection
	 */
	public function addLink($rel, Link $link, $overwrite = true)
	{
		return $this->_links->addLink($rel, $link, $overwrite);
	}

	/**
	 * Add links to the document
	 *
	 * @param   string   $rel        The relation of the link to the document. See RFC 5988
	 * @param   array    $links      An array of FOFHalLink objects
	 * @param   boolean  $overwrite  When false and a link of $rel relation exists, an array of
	 *                               links is created. Otherwise the existing link is overwriten
	 *                               with the new one
	 *
	 * @see Links::addLinks
	 *
	 * @return  boolean
	 */
	public function addLinks($rel, array $links, $overwrite = true)
	{
		return $this->_links->addLinks($rel, $links, $overwrite);
	}

	/**
	 * Add data to the document
	 *
	 * @param   \stdClass  $data       The data to add
	 * @param   boolean   $overwrite  Should I overwrite existing data?
	 *
	 * @return  void
	 */
	public function addData($data, $overwrite = true)
	{
		if (is_array($data))
		{
			$data = (object) $data;
		}

		if ($overwrite)
		{
			$this->_data = $data;
		}
		else
		{
			if (!is_array($this->_data))
			{
				$this->_data = array($this->_data);
			}

			$this->_data[] = $data;
		}
	}

	/**
	 * Add an embedded document
	 *
	 * @param   string    $rel        The relation of the embedded document to its container document
	 * @param   Document  $document   The document to add
	 * @param   boolean   $overwrite  Should I overwrite existing data with the same relation?
	 *
	 * @return  boolean
	 */
	public function addEmbedded($rel, Document $document, $overwrite = true)
	{
		if (!array_key_exists($rel, $this->_embedded) || $overwrite)
		{
			$this->_embedded[$rel] = $document;

			return true;
		}
		elseif (array_key_exists($rel, $this->_embedded) && !$overwrite)
		{
			if (!is_array($this->_embedded[$rel]))
			{
				$this->_embedded[$rel] = array($this->_embedded[$rel]);
			}

			$this->_embedded[$rel][] = $document;

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Returns the collection of links of this document
	 *
	 * @param   string  $rel  The relation of the links to fetch. Skip to get all links.
	 *
	 * @return  array
	 */
	public function getLinks($rel = null)
	{
		return $this->_links->getLinks($rel);
	}

	/**
	 * Returns the collection of embedded documents
	 *
	 * @param   string  $rel  Optional; the relation to return the embedded documents for
	 *
	 * @return  array|Document
	 */
	public function getEmbedded($rel = null)
	{
		if (empty($rel))
		{
			return $this->_embedded;
		}
		elseif (isset($this->_embedded[$rel]))
		{
			return $this->_embedded[$rel];
		}
		else
		{
			return array();
		}
	}

	/**
	 * Return the data attached to this document
	 *
	 * @return   array|\stdClass
	 */
	public function getData()
	{
		return $this->_data;
	}

	/**
	 * Instantiate and call a suitable renderer class to render this document
	 * into the specified format.
	 *
	 * @param   string  $format  The format to render the document into, e.g. 'json'
	 *
	 * @return  string  The rendered document
	 *
	 * @throws  \LogicException  If the format is unknown, i.e. there is no suitable renderer
	 */
	public function render($format = 'json')
	{
		$class_name = '\\FOF30\\Hal\\Render\\' . ucfirst($format);

		if (!class_exists($class_name, true))
		{
			throw new InvalidRenderFormat($format);
		}

		/** @var RenderInterface $renderer */
		$renderer = new $class_name($this);

		return $renderer->render(
			array(
				'data_key'		=> $this->_dataKey
			)
		);
	}
}
