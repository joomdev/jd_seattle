<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

/**
 * Form Field class for the Joomla Framework.
 *
 * @since   1.6.0
 */
class JFormFieldTimings extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	protected $type = 'Timings';

	/**
	 * The field layout.
	 *
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	protected $layout = 'field_timings';

	/**
	 * Method to get the field input markup.
	 *
	 * @return   string  The field input markup.
	 *
	 * @throws   Exception
	 *
	 * @since    1.6.0
	 */
	protected function getInput()
	{
		$this->value = is_string($this->value) ? (json_decode($this->value, true) ?: array()) : (array) $this->value;
		$this->layout = $this->element['layout'] ? $this->element['layout'] : $this->layout;

		$data          = array();
		$data['days']  = array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
		$data['field'] = array(
			'name'    => $this->name,
			'id'      => $this->id,
			'fldName' => $this->getAttribute('name'),
			'value'   => $this->value,
		);

		$timeOptions = array();
		$time        = JFactory::getDate('00:00');
		$end         = clone $time;
		$end->modify("+24 hours");

		while ($time < $end) {
			$timeOptions[$time->format('h:i A')] = $time->format('h:i A');
			$time->modify('+15 minutes');
		}

		$data['time_options'] = $timeOptions;

		$html = $this->renderTimings($data, $this->layout);

		return $html;
	}

	/**
	 * Render Timings layout
	 *
	 * @param   array $data The seller timings data
	 *
	 * @return  string
	 *
	 * @since   1.6.0
	 *
	 * @throws  \Exception
	 */
	public function renderTimings($data, $layoutName)
	{
		ob_start();
		$layoutPath = JPluginHelper::getLayoutPath('system', 'sellacioushyperlocal', $layoutName);

		if (is_file($layoutPath))
		{
			$displayData = $data;

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
