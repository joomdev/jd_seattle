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

class acypluginsHelper{

	public $wraped = false;
	public $name = 'content';

	function getFormattedResult($elements, $parameter){
		if(count($elements) < 2) return implode('', $elements);

		$beforeAll = array();
		$beforeAll['table'] = '<table cellspacing="0" cellpadding="0" border="0" width="100%" class="elementstable">'."\n";
		$beforeAll['ul'] = '<ul class="elementsul">'."\n";
		$beforeAll['br'] = '';

		$beforeBlock = array();
		$beforeBlock['table'] = '<tr class="elementstable_tr numrow{rownum}">'."\n";
		$beforeBlock['ul'] = '';
		$beforeBlock['br'] = '';

		$beforeOne = array();
		$beforeOne['table'] = '<td valign="top" width="{equalwidth}" class="elementstable_td numcol{numcol}" >'."\n";
		$beforeOne['ul'] = '<li class="elementsul_li numrow{rownum}">'."\n";
		$beforeOne['br'] = '';

		$afterOne = array();
		$afterOne['table'] = '</td>'."\n";
		$afterOne['ul'] = '</li>'."\n";
		$afterOne['br'] = '<br />'."\n";

		$afterBlock = array();
		$afterBlock['table'] = '</tr>'."\n";
		$afterBlock['ul'] = '';
		$afterBlock['br'] = '';

		$afterAll = array();
		$afterAll['table'] = '</table>'."\n";
		$afterAll['ul'] = '</ul>'."\n";
		$afterAll['br'] = '';


		$type = 'table';
		$cols = 1;
		if(!empty($parameter->displaytype)) $type = $parameter->displaytype;
		if($type == 'none') return implode('', $elements);
		if(!empty($parameter->cols)) $cols = $parameter->cols;

		$string = $beforeAll[$type];
		$a = 0;
		$numrow = 1;
		foreach($elements as $oneElement){
			if($a == $cols){
				$string .= $afterBlock[$type];
				$a = 0;
			}
			if($a == 0){
				$string .= str_replace('{rownum}', $numrow, $beforeBlock[$type]);
				$numrow++;
			}
			$string .= str_replace('{numcol}', $a + 1, $beforeOne[$type]).$oneElement.$afterOne[$type];
			$a++;
		}
		while($cols > $a){
			$string .= str_replace('{numcol}', $a + 1, $beforeOne[$type]).$afterOne[$type];
			$a++;
		}

		$string .= $afterBlock[$type];
		$string .= $afterAll[$type];

		$equalwidth = intval(100 / $cols).'%';

		$string = str_replace(array('{equalwidth}'), array($equalwidth), $string);

		return $string;
	}

	function formatString(&$replaceme, $mytag){
		if(!empty($mytag->part)){
			$parts = explode(' ', $replaceme);
			if($mytag->part == 'last'){
				$replaceme = count($parts) > 1 ? end($parts) : '';
			}else{
				if(is_numeric($mytag->part) && count($parts) >= $mytag->part){
					$replaceme = $parts[$mytag->part - 1];
				}else{
					$replaceme = reset($parts);
				}
			}
		}

		if(!empty($mytag->type)){
			if(empty($mytag->format)) $mytag->format = acymailing_translation('DATE_FORMAT_LC3');
			if($mytag->type == 'date'){
				$replaceme = acymailing_getDate(acymailing_getTime($replaceme), $mytag->format);
			}elseif($mytag->type == 'time'){
				$replaceme = acymailing_getDate($replaceme, $mytag->format);
			}elseif($mytag->type == 'diff'){
				try{
					$date = $replaceme;
					if(is_numeric($date)) $date = acymailing_getDate($replaceme, '%Y-%m-%d %H:%M:%S');
					$dateObj = new DateTime($date);
					$nowObj = new DateTime();
					$diff = $dateObj->diff($nowObj);
					$replaceme = $diff->format($mytag->format);
				}catch(Exception $e){
					$replaceme = 'Error using the "diff" parameter in your tag. Please make sure the DateTime() and diff() functions are available on your server.';
				}
			}
		}

		if(!empty($mytag->lower) || !empty($mytag->lowercase)) $replaceme = function_exists('mb_strtolower') ? mb_strtolower($replaceme, 'UTF-8') : strtolower($replaceme);
		if(!empty($mytag->upper) || !empty($mytag->uppercase)) $replaceme = function_exists('mb_strtoupper') ? mb_strtoupper($replaceme, 'UTF-8') : strtoupper($replaceme);
		if(!empty($mytag->ucwords)) $replaceme = ucwords($replaceme);
		if(!empty($mytag->ucfirst)) $replaceme = ucfirst($replaceme);
		if(isset($mytag->rtrim)) $replaceme = empty($mytag->rtrim) ? rtrim($replaceme) : rtrim($replaceme, $mytag->rtrim);
		if(!empty($mytag->urlencode)) $replaceme = urlencode($replaceme);
		if(!empty($mytag->substr)){
			$args = explode(',', $mytag->substr);
			if(isset($args[1])){
				$replaceme = substr($replaceme, intval($args[0]), intval($args[1]));
			}else{
				$replaceme = substr($replaceme, intval($args[0]));
			}
		}


		if(!empty($mytag->maxheight) || !empty($mytag->maxwidth)){
			$pictureHelper = acymailing_get('helper.acypict');
			$pictureHelper->maxHeight = empty($mytag->maxheight) ? 999 : $mytag->maxheight;
			$pictureHelper->maxWidth = empty($mytag->maxwidth) ? 999 : $mytag->maxwidth;
			$replaceme = $pictureHelper->resizePictures($replaceme);
		}
	}

	function replaceVideos($text){
		$text = preg_replace('#\[embed=videolink][^}]*youtube[^=]*=([^"/}]*)[^}]*}\[/embed]#i', '<a target="_blank" href="http://www.youtube.com/watch?v=$1"><img src="http://img.youtube.com/vi/$1/0.jpg"/></a>', $text);
		$text = preg_replace('#<video[^>]*youtube\.com/embed/([^"/]*)[^>]*>[^>]*</video>#i', '<a target="_blank" href="http://www.youtube.com/watch?v=$1"><img src="http://img.youtube.com/vi/$1/0.jpg"/></a>', $text);
		$text = preg_replace('#{JoooidContent[^}]*youtube[^}]*id"[^"]*"([^}"]*)"[^}]*}#i', '<a target="_blank" href="http://www.youtube.com/watch?v=$1"><img src="http://img.youtube.com/vi/$1/0.jpg"/></a>', $text);
		$text = preg_replace('#<iframe[^>]*src="[^"]*youtube[^"]*embed/([^"?]*)(\?[^"]*)?"[^>]*>[^<]*</iframe>#Uis', '<a target="_blank" href="http://www.youtube.com/watch?v=$1"><img src="http://img.youtube.com/vi/$1/0.jpg"/></a>', $text);
		$text = preg_replace('#{vimeo}([^{]+){/vimeo}#Uis', '<iframe src="https://player.vimeo.com/video/$1"></iframe>', $text);

		if(preg_match_all('#<iframe[^>]*src="([^"]*vimeo[^"]*)"[^>]*>[^<]*</iframe>#Uis', $text, $matches)){
			foreach($matches[1] as $key => $match){
				if(substr($matches[1][0], 0, 2) == '//') $matches[1][0] = 'https:'.$matches[1][0];
				$xml = acymailing_fileGetContent('https://vimeo.com/api/oembed.json?url='.urlencode($matches[1][0]));
				if(empty($xml)) continue;

				$xml = json_decode($xml);
				if(strpos($matches[0][$key], ' width="') !== false){
					$extension = substr($xml->thumbnail_url, strrpos($xml->thumbnail_url, '.'));
					preg_match('#width="([^"]*)"#Uis', $matches[0][$key], $width);

					$replace = strpos($xml->thumbnail_url, '_') === false ? '.' : '_';
					$xml->thumbnail_url = substr($xml->thumbnail_url, 0, strrpos($xml->thumbnail_url, $replace)).'_'.$width[1].$extension;
					$xml->thumbnail_url_with_play_button = 'https://i.vimeocdn.com/filter/overlay?src='.$xml->thumbnail_url.'&src=http://f.vimeocdn.com/p/images/crawler_play.png';
				}
				$text = str_replace($matches[0][$key], '<a target="_blank" href="'.($matches[1][0]).'"><img class="donotresize" alt="" src="'.($xml->thumbnail_url_with_play_button).'" /></a>', $text);
			}
		}

		$text = preg_replace('#\[embed=videolink][^}]*video":"([^"]*)[^}]*}\[/embed]#i', '<a target="_blank" href="$1"><img src="'.ACYMAILING_IMAGES.'/video.png"/></a>', $text);
		$text = preg_replace('#<video[^>]*src="([^"]*)"[^>]*>[^>]*</video>#i', '<a target="_blank" href="$1"><img src="'.ACYMAILING_IMAGES.'/video.png"/></a>', $text);
		return $text;
	}

	function removeJS($text){
		$text = preg_replace("#(onmouseout|onmouseover|onclick|onfocus|onload|onblur) *= *\"(?:(?!\").)*\"#iU", '', $text);
		$text = preg_replace("#< *script(?:(?!< */ *script *>).)*< */ *script *>#isU", '', $text);
		return $text;
	}

	private function _convertbase64pictures(&$html){
		if(!preg_match_all('#<img[^>]*src=("data:image/([^;]{1,5});base64[^"]*")([^>]*)>#Uis', $html, $resultspictures)) return;

		

		$dest = ACYMAILING_MEDIA.'resized'.DS;
		acymailing_createDir($dest);
		foreach($resultspictures[2] as $i => $extension){
			$pictname = md5($resultspictures[1][$i]).'.'.$extension;
			$picturl = ACYMAILING_LIVE.ACYMAILING_MEDIA_FOLDER.'/resized/'.$pictname;
			$pictPath = $dest.$pictname;
			$pictCode = trim($resultspictures[1][$i], '"');
			if(file_exists($pictPath)){
				$html = str_replace($pictCode, $picturl, $html);
				continue;
			}

			$getfunction = '';
			switch($extension){
				case 'gif':
					$getfunction = 'ImageCreateFromGIF';
					break;
				case 'jpg':
				case 'jpeg':
					$getfunction = 'ImageCreateFromJPEG';
					break;
				case 'png':
					$getfunction = 'ImageCreateFromPNG';
					break;
			}

			if(empty($getfunction) || !function_exists($getfunction)) continue;

			$img = $getfunction($pictCode);

			if(in_array($extension, array('gif', 'png'))){
				imagealphablending($img, false);
				imagesavealpha($img, false);
			}

			ob_start();
			switch($extension){
				case 'gif':
					$status = imagegif($img);
					break;
				case 'jpg':
				case 'jpeg':
					$status = imagejpeg($img, null, 100);
					break;
				case 'png':
					$status = imagepng($img, null, 1);
					break;
			}
			$imageContent = ob_get_clean();
			$status = $status && acymailing_writeFile($pictPath, $imageContent);

			if(!$status) continue;
			$html = str_replace($pictCode, $picturl, $html);
		}
	}

	private function _lineheightfix(&$html){
		$pregreplace = array();
		$pregreplace['#<tr([^>"]*>([^<]*<td[^>]*>[ \n\s]*<img[^>]*>[ \n\s]*</ *td[^>]*>[ \n\s]*)*</ *tr)#Uis'] = '<tr style="line-height: 0px;" $1';
		$pregreplace['#<td(((?!style|>).)*>[ \n\s]*(<a[^>]*>)?[ \n\s]*<img[^>]*>[ \n\s]*(</a[^>]*>)?[ \n\s]*</ *td)#Uis'] = '<td style="line-height: 0px;" $1';

		$newbody = preg_replace(array_keys($pregreplace), $pregreplace, $html);
		if(!empty($newbody)) $html = $newbody;
	}

	private function _removecontenttags(&$html){
		$pregreplace = array();
		$pregreplace['#{tab[ =][^}]*}#is'] = '';
		$pregreplace['#{/tabs}#is'] = '';
		$pregreplace['#{jcomments\s+(on|off|lock)}#is'] = '';
		$newbody = preg_replace(array_keys($pregreplace), $pregreplace, $html);
		if(!empty($newbody)) $html = $newbody;
	}

	function cleanHtml(&$html){

		$this->_lineheightfix($html);
		$this->_removecontenttags($html);
		$this->_convertbase64pictures($html);
		$this->cleanEditorCode($html);
		$this->_removeEditorFromTemplate($html);
	}

	public function fixPictureDim(&$html){
		if(!preg_match_all('#(<img)([^>]*>)#i', $html, $results)) return;

		static $replace = array();
		foreach($results[0] as $num => $oneResult){
			if(isset($replace[$oneResult])) continue;

			if(strpos($oneResult, 'width=') || strpos($oneResult, 'height=')) continue;
			if(preg_match('#[^a-z_\-]width *:([0-9 ]{1,8})#i', $oneResult, $res) || preg_match('#[^a-z_\-]height *:([0-9 ]{1,8})#i', $oneResult, $res)) continue;

			if(!preg_match('#src="([^"]*)"#i', $oneResult, $url)) continue;

			$imageUrl = $url[1];

			$replace[$oneResult] = $oneResult;

			$base = str_replace(array('http://www.', 'https://www.', 'http://', 'https://'), '', ACYMAILING_LIVE);
			$replacements = array('https://www.'.$base, 'http://www.'.$base, 'https://'.$base, 'http://'.$base);
			$localpict = false;
			foreach($replacements as $oneReplacement){
				if(strpos($imageUrl, $oneReplacement) === false) continue;
				$imageUrl = str_replace(array($oneReplacement, '/'), array(ACYMAILING_ROOT, DS), urldecode($imageUrl));
				$localpict = true;
				break;
			}

			if(!$localpict) continue;

			$dim = @getimagesize($imageUrl);
			if(!$dim) continue;
			if(empty($dim[0]) || empty($dim[1])) continue;

			$replace[$oneResult] = str_replace('<img', '<img width="'.$dim[0].'" height="'.$dim[1].'"', $oneResult);
		}

		if(empty($replace)) return;

		$html = str_replace(array_keys($replace), $replace, $html);
	}

	private function cleanEditorCode(&$html){
		if(!strpos($html, 'cke_edition_en_cours')) return;

		$html = preg_replace('#<div[^>]*cke_edition_en_cours.*$#Uis', '', $html);
	}

	private function _removeEditorFromTemplate(&$html){
		if(strpos($html, 'acyeditor_sharedspace') == -1) return;
		$html = preg_replace('#<div .* class="acyeditor_sharedspace".*><\/div>#', '', $html);
	}

	function replaceTags(&$email, &$tags, $html = false){
		if(empty($tags)) return;

		$htmlVars = array('body');
		$textVars = array('altbody');
		$lineVars = array('subject', 'From', 'FromName', 'ReplyTo', 'ReplyName', 'bcc', 'cc', 'fromname', 'fromemail', 'replyname', 'replyemail', 'params');

		$variables = array_merge($htmlVars, $textVars, $lineVars);

		if($html){
			if(empty($this->mailerHelper)) $this->mailerHelper = acymailing_get('helper.mailer');

			$textreplace = array();
			$linereplace = array();
			foreach($tags as $i => &$params){
				if(isset($textreplace[$i])) continue;
				$textreplace[$i] = $this->mailerHelper->textVersion($params, true);
				$linereplace[$i] = strip_tags(preg_replace('#</tr>[^<]*<tr[^>]*>#Uis', ' | ', $params));
			}

			$htmlKeys = array_keys($tags);
			$lineKeys = array_keys($linereplace);
			$textKeys = array_keys($textreplace);
		}else{
			$textreplace = &$tags;
			$linereplace = &$tags;
			$htmlKeys = array_keys($tags);
			$lineKeys = &$htmlKeys;
			$textKeys = &$htmlKeys;
		}

		foreach($variables as &$var){
			if(empty($email->$var)) continue;

			if(is_array($email->$var)){
				foreach($email->$var as $i => &$arrayField){
					if(empty($arrayField)) continue;

					if(is_array($arrayField)){
						foreach($arrayField as $a => &$oneval){
							if(in_array($var, $htmlVars)){
								$oneval = str_replace($htmlKeys, $tags, $oneval);
							}elseif(in_array($var, $lineVars)){
								$oneval = str_replace($lineKeys, $linereplace, $oneval);
							}else{
								$oneval = str_replace($textKeys, $textreplace, $oneval);
							}
						}
					}else{
						if(in_array($var, $htmlVars)){
							$arrayField = str_replace($htmlKeys, $tags, $arrayField);
						}elseif(in_array($var, $lineVars)){
							$arrayField = str_replace($lineKeys, $linereplace, $arrayField);
						}else{
							$arrayField = str_replace($textKeys, $textreplace, $arrayField);
						}
					}
				}
			}else{
				if(in_array($var, $htmlVars)){
					$email->$var = str_replace($htmlKeys, $tags, $email->$var);
				}elseif(in_array($var, $lineVars)){
					$email->$var = str_replace($lineKeys, $linereplace, $email->$var);
				}else{
					$email->$var = str_replace($textKeys, $textreplace, $email->$var);
				}
			}
		}
	}

	function extractTags(&$email, $tagfamily){
		$results = array();

		$match = '#(?:{|%7B)'.$tagfamily.'(?:%3A|\\:)(.*)(?:}|%7D)#Ui';
		$variables = array('subject', 'body', 'altbody', 'From', 'FromName', 'ReplyTo', 'ReplyName', 'bcc', 'cc', 'fromname', 'fromemail', 'replyname', 'replyemail', 'params');
		$found = false;
		foreach($variables as &$var){
			if(empty($email->$var)) continue;
			if(is_array($email->$var)){
				foreach($email->$var as $i => &$arrayField){
					if(empty($arrayField)) continue;
					if(is_array($arrayField)){
						foreach($arrayField as $a => &$oneval){
							$found = preg_match_all($match, $oneval, $results[$var.$i.'-'.$a]) || $found;
							if(empty($results[$var.$i.'-'.$a][0])) unset($results[$var.$i.'-'.$a]);
						}
					}else{
						$found = preg_match_all($match, $arrayField, $results[$var.$i]) || $found;
						if(empty($results[$var.$i][0])) unset($results[$var.$i]);
					}
				}
			}else{
				$found = preg_match_all($match, $email->$var, $results[$var]) || $found;
				if(empty($results[$var][0])) unset($results[$var]);
			}
		}

		if(!$found) return array();

		$tags = array();
		foreach($results as $var => $allresults){
			foreach($allresults[0] as $i => $oneTag){
				if(isset($tags[$oneTag])) continue;
				$tags[$oneTag] = $this->extractTag($allresults[1][$i]);
			}
		}

		return $tags;
	}

	function extractTag($oneTag){
		$arguments = explode('|', strip_tags(urldecode($oneTag)));
		$tag = new stdClass();
		$tag->id = $arguments[0];
		$tag->default = '';
		for($i = 1, $a = count($arguments); $i < $a; $i++){
			$args = explode(':', $arguments[$i]);
			$arg0 = trim($args[0]);
			if(empty($arg0)) continue;
			if(isset($args[1])){
				$tag->$arg0 = $args[1];
				if(isset($args[2])) $tag->{$args[0]} .= ':'.$args[2];
			}else{
				$tag->$arg0 = true;
			}
		}
		return $tag;
	}

	function wrapText($text, $tag){

		$this->wraped = false;

		if(!empty($tag->wrap)) $tag->wrap = intval($tag->wrap);
		if(empty($tag->wrap)) return $text;

		$allowedTags = array();
		$allowedTags[] = 'b';
		$allowedTags[] = 'strong';
		$allowedTags[] = 'i';
		$allowedTags[] = 'em';
		$allowedTags[] = 'a';

		$aloneAllowedTags = array();
		$aloneAllowedTags[] = 'br';
		$aloneAllowedTags[] = 'img';

		$newText = preg_replace('/<p[^>]*>/i', '<br />', $text);
		$newText = preg_replace('/<div[^>]*>/i', '<br />', $newText);
		$newText = strip_tags($newText, '<'.implode('><', array_merge($allowedTags, $aloneAllowedTags)).'>');

		$newText = preg_replace('/^(\s|\n|(<br[^>]*>))+/i', '', trim($newText));
		$newText = preg_replace('/(\s|\n|(<br[^>]*>))+$/i', '', trim($newText));

		$newText = str_replace(array('&lt', '&gt'), array('<', '>'), $newText);

		$numChar = strlen($newText);

		$numCharStrip = strlen(strip_tags($newText));

		if($numCharStrip <= $tag->wrap) return $newText;

		$this->wraped = true;

		$open = array();

		$write = true;

		$countStripChar = 0;

		for($i = 0; $i < $numChar; $i++){
			if($newText[$i] == '<'){
				foreach($allowedTags as $oneAllowedTag){
					if($numChar >= ($i + strlen($oneAllowedTag) + 1) && substr($newText, $i, strlen($oneAllowedTag) + 1) == '<'.$oneAllowedTag && (in_array($newText[$i + strlen($oneAllowedTag) + 1], array(' ', '>')))){
						$write = false;
						$open[] = '</'.$oneAllowedTag.'>';
					}

					if($numChar >= ($i + strlen($oneAllowedTag) + 2) && substr($newText, $i, strlen($oneAllowedTag) + 2) == '</'.$oneAllowedTag){
						if(end($open) == '</'.$oneAllowedTag.'>') array_pop($open);
					}
				}

				foreach($aloneAllowedTags as $oneAllowedTag){
					if($numChar >= ($i + strlen($oneAllowedTag) + 1) && substr($newText, $i, strlen($oneAllowedTag) + 1) == '<'.$oneAllowedTag && (in_array($newText[$i + strlen($oneAllowedTag) + 1], array(' ', '/', '>')))){
						$write = false;
					}
				}
			}

			if($write) $countStripChar++;

			if($newText[$i] == ">") $write = true;

			if($newText[$i] == " " && $countStripChar >= $tag->wrap && $write){
				$newText = substr($newText, 0, $i).'...';

				$open = array_reverse($open);
				$newText = $newText.implode('', $open);

				break;
			}
		}

		$newText = preg_replace('/^(\s|\n|(<br[^>]*>))+/i', '', trim($newText));
		$newText = preg_replace('/(\s|\n|(<br[^>]*>))+$/i', '', trim($newText));

		return $newText;
	}

	function getStandardDisplay($format){
		if(empty($format->tag->format)) $format->tag->format = 'TOP_LEFT';
		if(!in_array($format->tag->format, array('TOP_LEFT', 'TOP_RIGHT', 'TITLE_IMG', 'TITLE_IMG_RIGHT', 'CENTER_IMG', 'TOP_IMG', 'COL_LEFT', 'COL_RIGHT'))) return 'Wrong format suppied: '.$format->tag->format;

		$invertValues = array('TOP_LEFT' => 'TOP_RIGHT', 'TITLE_IMG' => 'TITLE_IMG_RIGHT', 'COL_LEFT' => 'COL_RIGHT', 'TOP_RIGHT' => 'TOP_LEFT', 'TITLE_IMG_RIGHT' => 'TITLE_IMG', 'COL_RIGHT' => 'COL_LEFT');
		if(!empty($format->tag->invert) && !empty($invertValues[$format->tag->format])) $format->tag->format = $invertValues[$format->tag->format];

		$image = '';
		if(!empty($format->imagePath)){
			$style = '';
			if(in_array($format->tag->format, array('TOP_LEFT', 'TITLE_IMG'))){
				$style = ' style="float:left;"';
			}elseif(in_array($format->tag->format, array('TOP_RIGHT', 'TITLE_IMG_RIGHT'))){
				$style = ' style="float:right;"';
			}
			$image = '<img alt="" src="'.$format->imagePath.'"'.$style.' />';
		}

		$result = '';
		if($format->tag->format == 'TITLE_IMG' || $format->tag->format == 'TITLE_IMG_RIGHT'){
			$format->title = $image.$format->title;
			$image = '';
		}

		if(!empty($format->link) && !empty($image)) $image = '<a target="_blank" href="'.$format->link.'" '.$style.'>'.$image.'</a>';

		if($format->tag->format == 'TOP_IMG' && !empty($image)){
			$result = $image;
			$image = '';
		}

		if(in_array($format->tag->format, array('COL_LEFT', 'COL_RIGHT'))){
			if(empty($image)){
				$format->tag->format = 'TOP_LEFT';
			}else{
				$result = '<table><tr><td valign="top" class="acyleftcol">';
				if($format->tag->format == 'COL_LEFT') $result .= $image.'</td><td valign="top" class="acyrightcol">';
			}
		}

		if(!empty($format->title)){
			if(!empty($format->link)) $format->title = '<a'.(!empty($format->tag->type) && $format->tag->type == 'title' ? ' class="acymailing_title"' : '').' href="'.$format->link.'" target="_blank" name="'.$this->name.'-'.$format->tag->id.'">'.$format->title.'</a>';
			if(empty($format->tag->type) || $format->tag->type != 'title') $format->title = '<h2 class="acymailing_title">'.$format->title.'</h2>';
			$result .= $format->title;
		}

		if(!empty($format->afterTitle)) $result .= $format->afterTitle;
		if(!empty($format->description)) $format->description = $this->wrapText($format->description, $format->tag);


		$rowText = '<div class="acydescription">';
		$endRow = '</div><br />';
		if(in_array($format->tag->format, array('TOP_LEFT', 'TOP_RIGHT', 'TITLE_IMG', 'TITLE_IMG_RIGHT', 'TOP_IMG'))){
			if(!empty($image) || !empty($format->description)) $result .= $rowText.$image.$format->description.$endRow;
		}elseif($format->tag->format == 'CENTER_IMG'){
			if(!empty($image)) $result .= '<div class="acymainimage">'.$image.$endRow;
			if(!empty($format->description)) $result .= $rowText.$format->description.$endRow;
		}elseif(in_array($format->tag->format, array('COL_LEFT', 'COL_RIGHT'))){
			if(!empty($format->description)) $result .= $rowText.$format->description.$endRow;
			if($format->tag->format == 'COL_RIGHT') $result .= '</td><td valign="top" class="acyrightcol">'.$image;
			$result .= '</td></tr></table>';
		}

		if(!empty($format->customFields)){
			$result .= '<table style="width:100%;" class="customfieldsarea"><tr>';

			if(empty($format->cols)) $format->cols = 1;
			$i = 0;
			foreach($format->customFields as $oneField){
				if($i != 0 && $i % $format->cols == 0) $result .= '</tr><tr>';
				$result .= '<td nowrap="nowrap" class="';
				if(empty($oneField[0])){
					$result .= 'cfvalue" colspan="2">';
				}else{
					$result .= 'cflabel">'.$oneField[0].'</td><td class="cfvalue">';
				}
				$result .= $oneField[1].'</td>';
				$i++;
			}

			while($i % $format->cols != 0){
				$result .= '<td colspan="2"></td>';
				$i++;
			}

			$result .= '</tr></table>';
		}

		if(!empty($format->afterArticle)) $result .= $format->afterArticle;

		return $result;
	}

	function managePicts($tag, $result){
		if(!isset($tag->pict)) return $result;

		$pictureHelper = acymailing_get('helper.acypict');
		if($tag->pict === 'resized'){
			$pictureHelper->maxHeight = empty($tag->maxheight) ? 150 : $tag->maxheight;
			$pictureHelper->maxWidth = empty($tag->maxwidth) ? 150 : $tag->maxwidth;
			if($pictureHelper->available()){
				$result = $pictureHelper->resizePictures($result);
			}elseif(acymailing_isAdmin()){
				acymailing_enqueueMessage($pictureHelper->error, 'notice');
			}
		}elseif($tag->pict == '0'){
			$result = $pictureHelper->removePictures($result);
		}

		return $result;
	}

	function getOrderingField($values, $ordering, $direction, $function = 'updateTagAuto'){
		$orderingValues = array();
		foreach($values as $value => $title){
			$orderingValues[] = acymailing_selectOption($value, acymailing_translation($title));
		}
		$orderingValues[] = acymailing_selectOption("rand", acymailing_translation('ACY_RANDOM'));

		$orderingDirections = array();
		$orderingDirections[] = acymailing_selectOption("DESC", 'DESC');
		$orderingDirections[] = acymailing_selectOption("ASC", 'ASC');

		return acymailing_select($orderingValues, 'contentorder', 'size="1" onchange="'.$function.'();" style="width:100px;"', 'value', 'text', $ordering).' '.acymailing_select($orderingDirections, 'contentorderdir', 'size="1" onchange="'.$function.'();" style="width:80px;"', 'value', 'text', $direction);
	}

	function translateItem(&$item, &$tag, $referenceTable, $referenceId = 0){
		if(empty($tag->lang) || (!file_exists(ACYMAILING_ROOT.'components'.DS.'com_falang') && !file_exists(ACYMAILING_ROOT.'components'.DS.'com_joomfish'))) return;
		$langid = (int)substr($tag->lang, strpos($tag->lang, ',') + 1);

		if(empty($langid)) return;

		if(empty($referenceId)) $referenceId = $tag->id;
		$table = (ACYMAILING_J16 && file_exists(ACYMAILING_ROOT.'components'.DS.'com_falang')) ? '`#__falang_content`' : '`#__jf_content`';
		$query = "SELECT reference_field, value FROM ".$table." WHERE `published` = 1 AND `reference_table` = ".acymailing_escapeDB($referenceTable)." AND `language_id` = $langid AND `reference_id` = ".$referenceId;
		$translations = acymailing_loadObjectList($query);

		if(empty($translations)) return;

		foreach($translations as $oneTranslation){
			if(empty($oneTranslation->value)) continue;
			$translatedfield = $oneTranslation->reference_field;
			$item->$translatedfield = $oneTranslation->value;
		}
	}

	function getFormatOption($plugin, $default = 'TOP_LEFT', $singleElement = true, $function = 'updateTag'){
		$contentformat = array('TOP_LEFT' => '-208', 'TOP_RIGHT' => '-260', 'TITLE_IMG' => '0', 'TITLE_IMG_RIGHT' => '-52', 'CENTER_IMG' => '-104', 'TOP_IMG' => '-156', 'COL_LEFT' => '-312', 'COL_RIGHT' => '-364');

		$name = $singleElement ? 'contentformat' : 'contentformatauto';

		$result = '<input type="hidden" name="'.$name.'" id="'.$name.'" value="'.$default.'" size="1"/>';
		$result .= '<span id="'.$name.'button" class="btn acybuttonformat" style="margin: 0px 10px 0px 0px; background-position: '.$contentformat[$default].'px -6px;height:34px;" onclick="togglediv'.$name.'();"></span>';
		$result .= '<div id="'.$name.'div" class="formatbox" style="display:none;">';

		$reset = '';
		if(file_exists(ACYMAILING_MEDIA.'plugins')){

			

			$files = acymailing_getFiles(ACYMAILING_MEDIA.'plugins', '^'.$plugin);
			foreach($files as $oneFile){
				$reset .= "document.getElementById('".$name.$oneFile."').style.backgroundPosition = '-480px -5px';document.getElementById('".$name.$oneFile."').style.boxShadow = 'inset 0 1px 0 rgba(255,255,255,.2), 0 1px 2px rgba(0,0,0,.05)';";
				$result .= '<span id="'.$name.$oneFile.'" class="btn acybuttonformat" style="background-position: -480px -5px;height:34px;" onclick="selectFormat'.$name.'(\''.$oneFile.'\',\''.$oneFile.'\',true);"></span>'.substr($oneFile, 0, strlen($oneFile) - 4).'<br/>';
			}
			$result .= '<br />';
		}

		foreach($contentformat as $value => $position){
			$reset .= "document.getElementById('".$name.$value."').style.backgroundPosition = '".$position."px -10px';document.getElementById('".$name.$value."').style.boxShadow = 'inset 0 1px 0 rgba(255,255,255,.2), 0 1px 2px rgba(0,0,0,.05)';";
			$result .= '<span id="'.$name.$value.'" class="btn acybuttonformat" style="background-position: '.$position.'px '.($value == $default ? -64 : -10).'px;" onclick="selectFormat'.$name.'(\''.$value.'\',\''.$position.'\',false);"></span>';
		}

		$result .= '<br />';

		if(!$singleElement){
			$result .= '<br /><input type="hidden" id="'.$name.'invert" value="0"/>';
			$result .= '<span id="'.$name.'invertbutton" class="btn acybuttonformat" style="background-position:-415px -8px;width:58px;height:30px;" onclick="toggleInvert'.$name.'();"></span>'.acymailing_tooltip('Alternatively display the image on the left and right', 'Alternate', '', 'Alternate');
		}

		$result .= '<span class="btn acyokbutton acybuttonformat" onclick="togglediv'.$name.'();">'.acymailing_translation('ACY_CLOSE').'</span>';
		$result .= '</div>';
		ob_start();
		?>
		<script type="text/javascript">
			<!--
			function togglediv<?php echo $name; ?>(){
				var divelement = document.getElementById('<?php echo $name; ?>div');
				if(divelement.style.display == 'none'){
					divelement.style.display = '';
				}else{
					divelement.style.display = 'none';
				}
			}
			<?php if(!$singleElement){ ?>
			function toggleInvert<?php echo $name; ?>(){
				var invertElement = document.getElementById('<?php echo $name; ?>invert');
				var posy = '8';
				var shadow = 'inset 0 1px 0 rgba(255,255,255,.2), 0 1px 2px rgba(0,0,0,.05)';
				if(invertElement.value == 0){
					posy = '60';
					shadow = 'inset 0 2px 4px rgba(0,0,0,.15), 0 1px 2px rgba(0,0,0,.05)';
				}
				invertElement.value = 1 - invertElement.value;
				document.getElementById('<?php echo $name; ?>invertbutton').style.backgroundPosition = '-415px -' + posy + 'px';
				document.getElementById('<?php echo $name; ?>invertbutton').style.boxShadow = shadow;
				<?php echo $function; ?>();
			}
			<?php } ?>

			function selectFormat<?php echo $name; ?>(format, position, custom){
				<?php echo $reset; ?>
				var prosy = '64';
				var newVal = format;
				if(custom){
					position = '-480';
					prosy = '58';
					newVal = '<?php echo $default; ?>| template:' + format;
				}
				document.getElementById('<?php echo $name; ?>').value = newVal;
				document.getElementById('<?php echo $name; ?>button').style.backgroundPosition = position + 'px -5px';
				document.getElementById('<?php echo $name; ?>' + format).style.backgroundPosition = position + 'px -' + prosy + 'px';
				document.getElementById('<?php echo $name; ?>' + format).style.boxShadow = 'inset 0 2px 4px rgba(0,0,0,.15), 0 1px 2px rgba(0,0,0,.05)';
				<?php echo $function; ?>();
			}
			-->
		</script>
		<?php
		$result .= ob_get_clean();
		return $result;
	}
}

