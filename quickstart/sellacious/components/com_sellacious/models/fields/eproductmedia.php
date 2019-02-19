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
defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('file');

/**
 * Form Field to provide an input field for files
 *
 * @link  http://www.w3.org/TR/html-markup/input.file.html#input.file
 */
class JFormFieldEProductMedia extends JFormFieldFile
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'EProductMedia';

	/**
	 * @var  int
	 */
	protected $product_id;

	/**
	 * @var  int
	 */
	protected $variant_id;

	/**
	 * @var  int
	 */
	protected $seller_uid;

	/**
	 * @var  bool
	 */
	protected $rename;

	/**
	 * @var  bool
	 */
	protected $file_types;

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement $element   The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed            $value     The form field value to validate.
	 * @param   string           $group     The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   3.2
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$file_types = explode('|', (string) $this->element['filetype']);

			$this->file_types = array('media' => $file_types[0], 'sample' => isset($file_types[1]) ? $file_types[1] : $file_types[0]);
			$this->rename     = (string) $this->element['rename'] != 'false';

			$this->product_id = (int) $this->element['product_id'];
			$this->seller_uid = (int) $this->element['seller_uid'];
			$this->variant_id = strlen((string) $this->element['variant_id']) == 0 ? null : (int) $this->element['variant_id'];
		}

		return $return;
	}

	/**
	 * Method to get the field input markup for the file field.
	 * Field attributes allow specification of a maximum file size and a string
	 * of accepted file extensions.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 *
	 * @note    The field does not include an upload mechanism.
	 * @see     JFormFieldMedia
	 */
	protected function getInput()
	{
		if ($this->seller_uid)
		{
			$this->addScript();

			// Load value automatically, don't depend on model
			$this->value = $this->getMedias();
			$displayData = (object) get_object_vars($this);
			$layoutFile  = 'com_sellacious.formfield.eproductmedia';
		}
		else
		{
			$displayData = null;
			$layoutFile  = 'com_sellacious.formfield.eproductmedia.inactive';
		}

		$html = JLayoutHelper::render($layoutFile, $displayData, '', array('client' => 2, 'debug' => 0));

		return $html;
	}

	/**
	 * Get media files for e-products
	 *
	 * @return  stdClass[]
	 */
	protected function getMedias()
	{
		if (!isset($this->variant_id))
		{
			return array();
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$where = array(
			'a.product_id = ' . (int) $this->product_id,
			'a.variant_id = ' . (int) $this->variant_id,
			'a.seller_uid = ' . (int) $this->seller_uid,
		);

		$query->select('a.*')->from($db->qn('#__sellacious_eproduct_media', 'a'))->where($where);

		$db->setQuery($query);

		try
		{
			$helper = SellaciousHelper::getInstance();
			$items  = $db->loadObjectList();

			if ($items)
			{
				$filter = array(
					'list.select' => 'a.id, a.path, a.state, a.original_name',
					'table_name'  => 'eproduct_media',
					'context'     => null,
					'record_id'   => null,
				);

				foreach ($items as &$item)
				{
					$filter['record_id'] = $item->id;

					$filter['context'] = 'media';
					$item->media       = $helper->media->loadObject($filter);

					$filter['context'] = 'sample';
					$item->sample      = $helper->media->loadObject($filter);
				}
			}
		}
		catch (Exception $e)
		{
			$items = array();

			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
		}

		return $items;
	}

	/**
	 * Add the javascript behavior for this form field
	 *
	 * @return  void
	 */
	protected function addScript()
	{
		$jsOptions = array(
			'wrapper'    => $this->id . '_wrapper',
			'siteRoot'   => JUri::root(true),
			'token'      => JSession::getFormToken() . '=1',
			'types'      => $this->file_types,
			'product_id' => $this->product_id,
			'variant_id' => $this->variant_id,
			'seller_uid' => $this->seller_uid,
			'target'     => array(
				'table'     => 'eproduct_media',
				'rename'    => $this->rename,
				'record_id' => null,
				'context'   => null,
				'type'      => null,
				'limit'     => 1,
				'temp'      => 0,
			),
		);

		$jsOptions = json_encode($jsOptions);
		$doc       = JFactory::getDocument();

		JHtml::_('behavior.framework');
		JHtml::_('jquery.framework');
		JHtml::_('script', 'com_sellacious/field.eproductmedia.js', array('version' => S_VERSION_CORE, 'relative' => true));
		JHtml::_('stylesheet', 'com_sellacious/field.eproductmedia.css', array('version' => S_VERSION_CORE, 'relative' => true));

		$doc->addScriptDeclaration("
			jQuery(document).ready(function () {
				var o = new JFormFieldEProductMedia;
				o.setup($jsOptions);
			});
		");
	}
}
