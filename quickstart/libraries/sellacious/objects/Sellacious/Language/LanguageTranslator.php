<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access.
namespace Sellacious\Language;

defined('_JEXEC') or die;

/**
 * Sellacious language translator to translate strings into another language using Google APIs
 * This object will work for one language at a time. To use another language create another instance.
 *
 * @since   1.6.0
 */
class LanguageTranslator
{
	/**
	 * The default language code for the application
	 *
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	protected $default = 'en-GB';

	/**
	 * The language code for the language to be indexed
	 *
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	protected $language;

	/**
	 * The loaded language strings
	 *
	 * @var   string[]
	 *
	 * @since   1.6.0
	 */
	protected $strings = array();

	/**
	 * Constructor
	 *
	 * @param   string  $language  The language to load, defaults to current language property of this object
	 *
	 * @since   1.6.0
	 */
	public function __construct($language)
	{
		$this->language = $language;
	}

	/**
	 * Returns all the loaded strings
	 *
	 * @return  string[]
	 *
	 * @since   1.6.0
	 */
	public function getStrings()
	{
		return $this->strings;
	}

	/**
	 * Returns the text value for the given string if found
	 *
	 * @param   string  $text  The language text to translate
	 *
	 * @return  string
	 *
	 * @throws  \Exception
	 *
	 * @since   1.6.0
	 */
	public function translate($text)
	{
		/**
		 * For some reason JHttp implementation doesn't work.
		 * This may be because we are unable to set SSL_VERIFY* options in that.
		 * Using plain CURL for now.
		 */
		if (trim($text) === '')
		{
			return '';
		}

		if (!function_exists('curl_init'))
		{
			throw new \Exception('CURL library is not available.');
		}

		if (!isset($this->strings[$text]))
		{
			$string = '';
			$query  = 'client=at&dt=t&dt=ld&dt=qca&dt=rm&dt=bd&dj=1&hl=en-GB&ie=UTF-8&oe=UTF-8&inputm=2&otf=2&iid=1dd3b944-fa62-4b55-b330-74909a99969e';
			$url    = 'https://translate.google.com/translate_a/single?' . $query;
			$uAgent = 'AndroidTranslate/5.3.0.RC02.130475354-53000263 5.1 phone TRANSLATE_OPM5_TEST_1';
			$fields = array('sl' => urlencode($this->default), 'tl' => urlencode($this->language), 'q' => urlencode($text));
			$qStr   = http_build_query($fields);
			$curl   = curl_init();

			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $qStr);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_ENCODING, 'UTF-8');
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_USERAGENT, $uAgent);

			$result = curl_exec($curl);

			curl_close($curl);

			$decoded = json_decode($result, true) ?: array();

			if (isset($decoded['sentences']))
			{
				foreach ($decoded['sentences'] as $s)
				{
					$string .= isset($s['trans']) ? $s['trans'] : '';
				}
			}

			$this->strings[$text] = urldecode($string);
		}

		return $this->strings[$text];
	}
}
