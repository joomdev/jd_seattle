<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php

class plgAcymailingTemplate extends JPlugin{

	var $templates = array();
	var $tags = array();
	var $headerstyles = array();
	var $others = array();
	var $stylesheets = array();
	var $templateClass = '';
	var $config;

	function __construct(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('acymailing', 'template');
			$this->params = new acyParameter($plugin->params);
		}
		$this->config = acymailing_config();
		if(version_compare(PHP_VERSION, '5.0.0', '>=') && class_exists('DOMDocument') && function_exists('mb_convert_encoding')){
			require_once(ACYMAILING_FRONT.'inc'.DS.'emogrifier'.DS.'emogrifier.php');
		}
	}

	private function _applyTemplate(&$email, $addbody){
		if(empty($email->tempid)) return;

		if(!isset($this->templates[$email->tempid])){
			$this->headerstyles[$email->tempid] = array();
			$this->headerstyles[$email->tempid][] = '.ReadMsgBody{width: 100%;}';
			$this->headerstyles[$email->tempid][] = '.ExternalClass{width: 100%;}';
			$this->headerstyles[$email->tempid][] = 'div, p, a, li, td { -webkit-text-size-adjust:none; }';
			$this->headerstyles[$email->tempid][] = 'a[x-apple-data-detectors]{
			color: inherit !important;
			text-decoration: inherit !important;
			font-size: inherit !important;
			font-family: inherit !important;
			font-weight: inherit !important;
			line-height: inherit !important;
			}';

			$this->templates[$email->tempid] = array();
			if(empty($this->templateClass)){
				$this->templateClass = acymailing_get('class.template');
			}
			if(!empty($email->template) && $email->tempid == $email->template->tempid){
				$template = $email->template;
			}else{
				$template = $email->template = $this->templateClass->get($email->tempid);
			}

			if(!empty($template->styles) OR !empty($template->stylesheet)){
				$this->stylesheets[$email->tempid] = $this->templateClass->buildCSS($template->styles, $template->stylesheet);

				if(preg_match_all('#@import[^;]*;#is', $this->stylesheets[$email->tempid], $results)){
					foreach($results[0] as $oneResult){
						array_unshift($this->headerstyles[$email->tempid], trim($oneResult));
					}
				}

				if(preg_match_all('#@media.*}[^{}]*}#Uis', $this->stylesheets[$email->tempid], $results)){

					foreach($results[0] as $oneResult){
						$this->stylesheets[$email->tempid] = str_replace($oneResult, '', $this->stylesheets[$email->tempid]);
						$this->headerstyles[$email->tempid][] = trim($oneResult);
					}
				}

				if(preg_match_all('#}([^}]+:hover[^{]*{[^{]*})#Uis', '} '.$this->stylesheets[$email->tempid], $results)){
					foreach($results[1] as $oneResult){
						$this->stylesheets[$email->tempid] = str_replace($oneResult, '', $this->stylesheets[$email->tempid]);
						$this->headerstyles[$email->tempid][] = trim($oneResult);
					}
				}
			}


			if(!empty($template->styles)){
				foreach($template->styles as $class => $style){
					if(empty($style)) continue;
					if(preg_match('#^tag_(.*)$#', $class, $result)){
						$this->tags[$email->tempid]['#< *'.$result[1].'((?:(?!style).)*)>#Ui'] = '<'.$result[1].' style="'.$style.'" $1>';
						if(strpos($style, '!important')) $this->headerstyles[$email->tempid][] = $result[1].'{ '.str_replace('!important', '', $style).' }';
					}elseif($class == 'color_bg'){
						$this->others[$email->tempid][$class] = $style;
					}else{
						$this->templates[$email->tempid]['class="'.$class.'"'] = 'style="'.$style.'"';
					}
				}
				if(!empty($template->styles['tag_a'])){
					$this->headerstyles[$email->tempid][] = 'a:visited{'.$template->styles['tag_a'].'}';
				}
			}
		}

		if($addbody AND !strpos($email->body, '</body>')){
			$before = '<html><head>'."\n";
			if(!empty($template->header)) $before .= $template->header."\n";
			$before .= '<meta http-equiv="Content-Type" content="text/html; charset='.strtolower($this->config->get('charset')).'" />'."\n";
			$before .= '<meta name="viewport" content="width=device-width, initial-scale=1.0" />'."\n";
			$before .= '<title>'.$email->subject.'</title>'."\n";
			if(!empty($this->headerstyles[$email->tempid])){
				$before .= '<style type="text/css">'."\n";
				$before .= implode("\n", $this->headerstyles[$email->tempid])."\n";
				$before .= '</style>'."\n";
			}
			$before .= '</head>'."\n".'<body yahoo="fix"';
			if(!empty($this->others[$email->tempid]['color_bg'])) $before .= ' bgcolor="'.$this->others[$email->tempid]['color_bg'].'" ';
			$before .= '>'."\n";
			$email->body = $before.$email->body.'</body>'."\n".'</html>';
		}

		if(!empty($this->stylesheets[$email->tempid]) AND class_exists('acymailingEmogrifier')){
			$emogrifier = new acymailingEmogrifier($email->body, $this->stylesheets[$email->tempid]);
			$email->body = $emogrifier->emogrify();

			if(!$addbody AND strpos($email->body, '<!DOCTYPE') !== false){
				$email->body = preg_replace('#<\!DOCTYPE.*<body([^>]*)>#Usi', '', $email->body);
				$email->body = preg_replace('#</body>.*$#si', '', $email->body);
			}
		}else{
			if(!empty($this->templates[$email->tempid])){
				$email->body = str_replace(array_keys($this->templates[$email->tempid]), $this->templates[$email->tempid], $email->body);
			}

			if(!empty($this->tags[$email->tempid])){
				$email->body = preg_replace(array_keys($this->tags[$email->tempid]), $this->tags[$email->tempid], $email->body);
			}
		}

		$newbody = preg_replace('#(<(div|tr|td|table)[^>]*)title="[^"]*"#Uis', '$1', $email->body);
		if(!empty($newbody)) $email->body = $newbody;

		$newbody = preg_replace('# id="zone_[0-9]+"#Uis', ' ', $email->body);
		if(!empty($newbody)) $email->body = $newbody;

		$newbody = preg_replace('# *(acyeditor_text|acyeditor_picture|acyeditor_delete|acyeditor_sortable|ui-sortable) *#is', '', $email->body);
		$newbody = preg_replace('#(class|title|style|id)=" *"#Ui', '', $newbody);
		if(!empty($newbody)) $email->body = $newbody;
	}

	public function acymailing_replaceusertags(&$email, &$user, $send = true){

		if(!$email->sendHTML) return;

		if((!acymailing_level(1) || acymailing_level(4)) && !empty($email->type) && in_array($email->type, array('news', 'followup'))){
			$pict = '<div style="text-align:center;margin:10px auto;display:block;"><a target="_blank" href="https://www.acyba.com/?utm_source=acymailing&utm_medium=e-mail&utm_content=img&utm_campaign=powered-by"><img alt="Powered by AcyMailing" src="media/com_acymailing/images/poweredby.png" /></a></div>';

			if(strpos($email->body, '</body>')){
				$email->body = str_replace('</body>', $pict.'</body>', $email->body);
			}else{
				$email->body .= $pict;
			}
		}

		$this->_applyTemplate($email, $send);

		$email->body = preg_replace('#< *(tr|td|table)([^>]*)(style="[^"]*)background-image *: *url\(\'?([^)\']*)\'?\);?#Ui', '<$1 background="$4" $2 $3', $email->body);
		$email->body = acymailing_absoluteURL($email->body);

		if(preg_match_all('#< *img([^>]*)>#Ui', $email->body, $allPictures)){

			foreach($allPictures[0] as $i => $onePict){
				if(strpos($onePict, 'align=') !== false) continue;
				if(!preg_match('#(style="[^"]*)(float *: *)(right|left|top|bottom|middle)#Ui', $onePict, $pictParams)) continue;

				$newPict = str_replace('<img', '<img align="'.$pictParams[3].'" ', $onePict);

				$email->body = str_replace($onePict, $newPict, $email->body);

				if(strpos($onePict, 'hspace=') !== false) continue;

				$hspace = 5;
				if(preg_match('#margin(-right|-left)? *:([^";]*)#i', $onePict, $margins)){
					$currentMargins = explode(' ', trim($margins[2]));
					$myMargin = (count($currentMargins) > 1) ? $currentMargins[1] : $currentMargins[0];
					if(strpos($myMargin, 'px') !== false) $hspace = preg_replace('#[^0-9]#i', '', $myMargin);
				}

				$lastPict = str_replace('<img', '<img hspace="'.$hspace.'" ', $newPict);

				$email->body = str_replace($newPict, $lastPict, $email->body);
			}
		}

		if(!preg_match('#(<thead|<tfoot|< *tbody *[^> ]+ *>)#Ui', $email->body)){
			$email->body = preg_replace('#< *\/? *tbody *>#Ui', '', $email->body);
		}

		$email->body = preg_replace_callback('/src="([^"]* [^"]*)"/Ui', array($this, '_convertSpaces'), $email->body);

		$this->fixPictureSize($email->body);

		$acypluginsHelper = acymailing_get('helper.acyplugins');
		$acypluginsHelper->fixPictureDim($email->body);
	}//endfct

	public function acymailing_replacetags(&$email, $send = true){
		$this->linksSEF($email);
		$this->checkThumbnailYoutube($email);
	}

	public function linksSEF(&$email){
		$results = array();
		$altresults = array();
		$found = preg_match_all('#(?:{|%7B)acyfrontsef(?:}|%7D)(.*)(?:{|%7B)/acyfrontsef(?:}|%7D)#Uis', $email->body, $results);
		$found = preg_match_all('#(?:{|%7B)acyfrontsef(?:}|%7D)(.*)(?:{|%7B)/acyfrontsef(?:}|%7D)#Uis', $email->altbody, $altresults) || $found;

		if(!$found) return;

		$results[0] = array_merge($results[0], $altresults[0]);
		$results[1] = array_merge($results[1], $altresults[1]);

		$clearesults = array(0 => array(), 1 => array());
		foreach($results[0] as $i => $val){
			if(in_array($val, $clearesults[0])) continue;
			$clearesults[0][] = $val;
			$clearesults[1][] = $results[1][$i];
		}
		$results = $clearesults;

		$urls = '';
		$i = 0;
		$passedResults = array(0 => array(), 1 => array());
		foreach($results[1] as $key => $link){
			$urls .= '&urls['.$i.']='.base64_encode($link);
			$passedResults[0][] = $results[0][$key];
			$passedResults[1][] = $link;
			$i++;

			if($i > 40){
				$this->_callFrontURL($email, $urls, $passedResults);
				$passedResults = array(0 => array(), 1 => array());
				$urls = '';
				$i = 0;
			}
		}

		if(!empty($urls)) $this->_callFrontURL($email, $urls, $passedResults);
	}

	private function _callFrontURL(&$email, $urls, $results){
		$sefLinks = acymailing_fileGetContent(acymailing_rootURI().'index.php?option=com_acymailing&ctrl=url&task=sef'.$urls);
		$newLinks = json_decode($sefLinks, true);

		if($newLinks == null){
			if(!empty($sefLinks) && defined('JDEBUG') && JDEBUG) acymailing_enqueueMessage('Error trying to get the sef links: '.$sefLinks);

			$newLinks = array();
			foreach($results[1] as $link){
				$key = $link;
				$link = ltrim($link, '/');
				$mainurl = acymailing_mainURL($link);
				$newLinks[$key] = $mainurl.$link;
			}
		}
		$replacement = array();
		if(empty($newLinks)) return;

		foreach($results[1] as $key => $origin){
			$replacement[$results[0][$key]] = $newLinks[$results[1][$key]];
		}

		$email->body = str_replace(array_keys($replacement), $replacement, $email->body);
		$email->altbody = str_replace(array_keys($replacement), $replacement, $email->altbody);
	}

	public function _convertSpaces($matches){
		return "src='".str_replace(' ', '%20', $matches[1])."'";
	}

	private function fixPictureSize(&$body){
		if(!preg_match_all('#(<img)([^>]*>)#i', $body, $results)) return;

		$replace = array();
		$widthheight = array('width', 'height');
		foreach($results[0] as $num => $oneResult){
			$add = array();
			foreach($widthheight as $whword){
				if(preg_match('#'.$whword.' *=#i', $oneResult) || !preg_match('#[^a-z_\-]'.$whword.' *:([0-9 ]{1,8})px#i', $oneResult, $resultWH)) continue;

				if(empty($resultWH[1])) continue;
				$add[] = $whword.'="'.trim($resultWH[1]).'" ';
			}
			if(!empty($add)) $replace[$oneResult] = '<img '.implode(' ', $add).$results[2][$num];
		}

		if(empty($replace)) return;

		$body = str_replace(array_keys($replace), $replace, $body);
	}

	function checkThumbnailYoutube(&$mail){
		$acypluginsHelper = acymailing_get('helper.acyplugins');
		$mail->body = $acypluginsHelper->replaceVideos($mail->body);
	}
}//endclass
