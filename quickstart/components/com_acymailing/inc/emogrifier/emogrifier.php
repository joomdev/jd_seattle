<?php

acymailing_cmsLoaded();

/*
UPDATES
		2008-08-10  Fixed CSS comment stripping regex to add PCRE_DOTALL (changed from '/\/\*.*\*\//U' to '/\/\*.*\*\//sU')
		2008-08-18  Added lines instructing DOMDocument to attempt to normalize HTML before processing
		2008-10-20  Fixed bug with bad variable name... Thanks Thomas!
		2008-03-02  Added licensing terms under the MIT License
								Only remove unprocessable HTML tags if they exist in the array
		2009-06-03  Normalize existing CSS (style) attributes in the HTML before we process the CSS.
								Made it so that the display:none stripper doesn't require a trailing semi-colon.
		2009-08-13  Added support for subset class values (e.g. "p.class1.class2").
								Added better protection for bad css attributes.
								Fixed support for HTML entities.
		2009-08-17  Fixed CSS selector processing so that selectors are processed by precedence/specificity, and not just in order.
		2009-10-29  Fixed so that selectors appearing later in the CSS will have precedence over identical selectors appearing earlier.
		2009-11-04  Explicitly declared static functions static to get rid of E_STRICT notices.
		2010-05-18  Fixed bug where full url filenames with protocols wouldn't get split improperly when we explode on ':'... Thanks Mark!
								Added two new attribute selectors
		2010-06-16  Added static caching for less processing overhead in situations where multiple emogrification takes place
		2010-07-26  Fixed bug where '0' values were getting discarded because of php's empty() function... Thanks Scott!
		2010-09-03  Added checks to invisible node removal to ensure that we don't try to remove non-existent child nodes of parents that have already been deleted


*/

class acymailingEmogrifier{

	private $html = '';
	private $css = '';
	private $unprocessableHTMLTags = array('wbr');

	public function __construct($html = '', $css = ''){
		$this->html = $html;
		$this->css = $css;
	}

	public function setHTML($html = ''){ $this->html = $html; }

	public function setCSS($css = ''){ $this->css = $css; }

	// there are some HTML tags that DOMDocument cannot process, and will throw an error if it encounters them.
	// these functions allow you to add/remove them if necessary.
	// it only strips them from the code (does not remove actual nodes).
	public function addUnprocessableHTMLTag($tag){ $this->unprocessableHTMLTags[] = $tag; }

	public function removeUnprocessableHTMLTag($tag){
		if(($key = array_search($tag, $this->unprocessableHTMLTags)) !== false)
			unset($this->unprocessableHTMLTags[$key]);
	}

	public static function strtolower($matches){
		return strtolower($matches[0]);
	}

	// applies the CSS you submit to the html you submit. places the css inline
	public function emogrify(){
		$body = $this->html;
		// process the CSS here, turning the CSS style blocks into inline css
		if(count($this->unprocessableHTMLTags)){
			$unprocessableHTMLTags = implode('|', $this->unprocessableHTMLTags);
			$body = preg_replace("/<($unprocessableHTMLTags)[^>]*>/i", '', $body);
		}

		//$encoding = mb_detect_encoding($body);
		$encoding = 'UTF-8';
		$body = mb_convert_encoding($body, 'HTML-ENTITIES', $encoding);

		$xmldoc = @ new DOMDocument;
		if(!is_object($xmldoc) || !method_exists($xmldoc, 'loadHTML')) return $this->html;

		$xmldoc->encoding = $encoding;
		$xmldoc->strictErrorChecking = false;
		$xmldoc->formatOutput = true;
		//ACYBA MODIFICATION : let's avoid some warnings
		//Disable the loadHTML function errors which may crash some servers.
		if(function_exists('libxml_use_internal_errors')) libxml_use_internal_errors(true);
		@$xmldoc->loadHTML($body);
		$xmldoc->normalizeDocument();

		$xpath = new DOMXPath($xmldoc);

		// before be begin processing the CSS file, parse the document and normalize all existing CSS attributes (changes 'DISPLAY: none' to 'display: none');
		// we wouldn't have to do this if DOMXPath supported XPath 2.0.
		$nodes = @$xpath->query('//'.'*[@style]');
		if($nodes->length > 0) foreach($nodes as $node){
			$node->setAttribute('style', preg_replace_callback('/[A-z\-]+(?=\:)/S', array($this, 'strtolower'), $node->getAttribute('style')));
		}
		// get rid of css comment code
		$re_commentCSS = '/\/\*.*\*\//sU';
		$css = preg_replace($re_commentCSS, '', $this->css);

		static $csscache = array();
		$csskey = md5($css);
		if(!isset($csscache[$csskey])){

			// process the CSS file for selectors and definitions
			$re_CSS = '/^\s*([^{]+){([^}]+)}/mis';
			preg_match_all($re_CSS, $css, $matches);

			$all_selectors = array();
			foreach($matches[1] as $key => $selectorString){
				// if there is a blank definition, skip
				if(!strlen(trim($matches[2][$key]))) continue;

				// else split by commas and duplicate attributes so we can sort by selector precedence
				$selectors = explode(',', $selectorString);
				foreach($selectors as $selector){
					// don't process pseudo-classes
					if(strpos($selector, ':') !== false) continue;
					$all_selectors[] = array(
						'selector' => $selector,
						'attributes' => $matches[2][$key],
						'index' => $key, // keep track of where it appears in the file, since order is important
					);
				}
			}

			// now sort the selectors by precedence
			usort($all_selectors, array('self', 'sortBySelectorPrecedence'));

			$csscache[$csskey] = $all_selectors;
		}

		for($a = count($csscache[$csskey]) - 1; $a >= 0; $a--){

			// query the body for the xpath selector
			$nodes = @$xpath->query($this->translateCSStoXpath(trim($csscache[$csskey][$a]['selector'])));
			if(empty($nodes)) continue;

			foreach($nodes as $node){
				// if it has a style attribute, get it, process it, and append (overwrite) new stuff
				if($node->hasAttribute('style')){
					// break it up into an associative array
					$oldStyleArr = $this->cssStyleDefinitionToArray($node->getAttribute('style'));
					$newStyleArr = $this->cssStyleDefinitionToArray($csscache[$csskey][$a]['attributes']);

					// new styles overwrite the old styles (not technically accurate, but close enough)
					//Changed by Acyba, we don't overwrite the old styles, we keep them and add only the new ones
					//$combinedArr = array_merge($oldStyleArr,$newStyleArr);
					$combinedArr = array_merge($newStyleArr, $oldStyleArr);
					$style = '';
					foreach($combinedArr as $k => $v) $style .= (strtolower($k).':'.$v.';');
				}
				else{
					// otherwise create a new style
					$style = trim($csscache[$csskey][$a]['attributes']);
				}
				$node->setAttribute('style', $style);
			}
		}

		//Adrien : we don't need that... it removed display:none elements from the Newsletter, we may need them with media query
		// This removes styles from your email that contain display:none. You could comment these out if you want.
		//$nodes = $xpath->query('//'.'*[contains(translate(@style," ",""),"display:none")]');
		// the checks on parentNode and is_callable below are there to ensure that if we've deleted the parent node,
		// we don't try to call removeChild on a nonexistent child node
		//if ($nodes->length > 0) foreach ($nodes as $node) if ($node->parentNode && is_callable(array($node->parentNode,'removeChild'))) $node->parentNode->removeChild($node);

		$result = $this->fixCompatibility($xmldoc->saveHTML());
		
		// Special fix for ElasticEmail, they force their users to insert something like this:
		// <a href="{unsubscribeauto:http://link-to-your-unsubscribe-page}">Unsubscribe</a>
		// The { and } are obviously urlencoded, we should prevent it as the EE team automatically adds something ugly in the emails otherwise
		if(strpos($result, 'href="%7Bunsubscribe') !== false){
			$result = preg_replace_callback('#href="%7B(unsubscribe[^"]+)%7D([^"]*)"#Uis', array($this, 'decodeUnsubscribeTags'), $result);
		}
		return $result;
	}
	
	function decodeUnsubscribeTags($matches){
		return 'href="{'.urldecode($matches[1]).'}'.$matches[2].'"';
	}

	private static function sortBySelectorPrecedence($a, $b){
		$precedenceA = self::getCSSSelectorPrecedence($a['selector']);
		$precedenceB = self::getCSSSelectorPrecedence($b['selector']);

		// we want these sorted ascendingly so selectors with lesser precedence get processed first and
		// selectors with greater precedence get sorted last
		return ($precedenceA == $precedenceB) ? ($a['index'] < $b['index'] ? -1 : 1) : ($precedenceA < $precedenceB ? -1 : 1);
	}

	private static function getCSSSelectorPrecedence($selector){
		static $selectorcache = array();
		$selectorkey = md5($selector);
		if(!isset($selectorcache[$selectorkey])){
			$precedence = 0;
			$value = 100;
			$search = array('\#', '\.', ''); // ids: worth 100, classes: worth 10, elements: worth 1

			foreach($search as $s){
				if(trim($selector == '')) break;
				$num = 0;
				$selector = preg_replace('/'.$s.'\w+/', '', $selector, -1, $num);
				$precedence += ($value * $num);
				$value /= 10;
			}
			$selectorcache[$selectorkey] = $precedence;
		}

		return $selectorcache[$selectorkey];
	}

	// right now we support all CSS 1 selectors and /some/ CSS2/3 selectors.
	// http://plasmasturm.org/log/444/
	private function translateCSStoXpath($css_selector){


		$css_selector = trim($css_selector);
		static $xpathcache = array();
		$xpathkey = md5($css_selector);
		if(!isset($xpathcache[$xpathkey])){
			// returns an Xpath selector
			$search = array(
				'/\s+>\s+/', // Matches any F element that is a child of an element E.
				'/(\w+)\s+\+\s+(\w+)/', // Matches any F element that is a child of an element E.
				'/\s+/', // Matches any F element that is a descendant of an E element.
				'/(\w)\[(\w+)\]/', // Matches element with attribute
				'/(\w)\[(\w+)\=[\'"]?(\w+)[\'"]?\]/'); // Matches element with EXACT attribute);
			$replace = array(
				'/',
				'\\1/following-sibling::*[1]/self::\\2',
				'//',
				'\\1[@\\2]',
				'\\1[@\\2="\\3"]');


			// The preg_replace doesn't handle the "e" modifier anymore in PHP 7+, use preg_replace_callback instead
			$value = preg_replace($search, $replace, $css_selector);
			$value = preg_replace_callback('/(\w+)?\#([\w\-]+)/', array($this, 'callable1'), $value);
			$value = preg_replace_callback('/(\w+|\*)?((\.[\w\-]+)+)/', array($this, 'callable2'), $value);

			$xpathcache[$xpathkey] = '//'.$value;
		}
		return $xpathcache[$xpathkey];
	}

	function callable1($matches){
		return (strlen($matches[1]) ? $matches[1] : '*').'[@id="'.$matches[2].'"]';
	}

	function callable2($matches){
		$result = (strlen($matches[1]) ? $matches[1] : '*');
		$result .= '[contains(concat(" ",@class," "),concat(" ","';
		$result .= implode('"," "))][contains(concat(" ",@class," "),concat(" ","', explode('.', substr($matches[2], 1)));
		$result .= '"," "))]';

		return $result;
	}

	private function cssStyleDefinitionToArray($style){
		$definitions = explode(';', $style);
		$retArr = array();
		foreach($definitions as $def){
			if(empty($def) || strpos($def, ':') === false) continue;
			list($key, $value) = explode(':', $def, 2);
			if(empty($key) || strlen(trim($value)) === 0) continue;
			$retArr[trim($key)] = trim($value);
		}
		return $retArr;
	}

	private function fixCompatibility($text){
		$replace = array();
		$replace['#<br>#Ui'] = '<br />';
		$replace['#<img([^>]*[^/])>#Ui'] = '<img$1 />';
		//We replace the header properly as it may display a non valid DOCTYPE...
		$replace['#<\!DOCTYPE[^>]*>#Usi'] = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
		$body = preg_replace(array_keys($replace), $replace, $text);

		//Just in case of...
		if(empty($body)) $body = $text;
		//Be careful with that line!
		//$body = mb_convert_encoding($body, 'UTF-8', 'HTML-ENTITIES');

		//Debug informations...
		//echo '<textarea cols="100" rows="10">'.htmlentities($text).'</textarea>';
		//echo '<textarea cols="100" rows="10">'.htmlentities($body).'</textarea>';
		return $body;
	}
}

//Just in case of... we used to call it Emogrifier so we don't want to break plugins using this class via the AcyMailing files...
if(!class_exists('Emogrifier')){
	class Emogrifier extends acymailingEmogrifier{
	}
}