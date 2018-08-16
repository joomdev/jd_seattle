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

require_once(ACYMAILING_INC.'phpmailer'.DS.'class.phpmailer.php');

class acymailerHelper extends acymailingPHPMailer{

	var $report = true;

	var $loadedToSend = true;

	var $checkConfirmField = true;

	var $checkEnabled = true;

	var $checkAccept = true;

	var $parameters = array();

	var $dispatcher;

	var $errorNumber = 0;

	var $reportMessage = '';

	var $autoAddUser = false;

	var $errorNewTry = array(1, 6);

	var $app;

	var $alreadyCheckedAddresses = false;

	var $checkPublished = true;

	var $introtext;

	var $trackEmail = false;

	public $From = '';

	public $FromName = '';

	function __construct(){

		static $loaded = false;
		if(!$loaded){
			$loaded = true;
			acymailing_importPlugin('acymailing');
		}

		$this->SMTPAutoTLS = false;

		$this->subscriberClass = acymailing_get('class.subscriber');
		$this->encodingHelper = acymailing_get('helper.encoding');
		$this->userHelper = acymailing_get('helper.user');


		$this->config = acymailing_config();
		$this->setFrom($this->config->get('from_email'), $this->config->get('from_name'));

		$this->Sender = $this->cleanText($this->config->get('bounce_email'));
		if(empty($this->Sender)) $this->Sender = '';

		switch($this->config->get('mailer_method', 'phpmail')){
			case 'smtp' :
				$this->isSMTP();
				$this->Host = trim($this->config->get('smtp_host'));
				$port = $this->config->get('smtp_port');
				if(empty($port) && $this->config->get('smtp_secured') == 'ssl') $port = 465;
				if(!empty($port)) $this->Host .= ':'.$port;
				$this->SMTPAuth = (bool)$this->config->get('smtp_auth', true);
				$this->Username = trim($this->config->get('smtp_username'));
				$this->Password = trim($this->config->get('smtp_password'));
				$this->SMTPSecure = trim((string)$this->config->get('smtp_secured'));

				if(empty($this->Sender)) $this->Sender = strpos($this->Username, '@') ? $this->Username : $this->config->get('from_email');
				break;
			case 'sendmail' :
				$this->isSendmail();
				$this->Sendmail = trim($this->config->get('sendmail_path'));
				if(empty($this->Sendmail)) $this->Sendmail = '/usr/sbin/sendmail';
				break;
			case 'qmail' :
				$this->isQmail();
				break;
			case 'elasticemail' :
				$port = $this->config->get('elasticemail_port', 'rest');
				if(is_numeric($port)){
					$this->isSMTP();
					if($port == '25'){
						$this->Host = 'smtp25.elasticemail.com:25';
					}else{
						$this->Host = 'smtp.elasticemail.com:2525';
					}
					$this->Username = trim($this->config->get('elasticemail_username'));
					$this->Password = trim($this->config->get('elasticemail_password'));
					$this->SMTPAuth = true;
				}else{
					include_once(ACYMAILING_INC.'phpmailer'.DS.'class.elasticemail.php');
					$this->Mailer = 'elasticemail';
					$this->{$this->Mailer} = new acymailingElasticemail();
					$this->{$this->Mailer}->Username = trim($this->config->get('elasticemail_username'));
					$this->{$this->Mailer}->Password = trim($this->config->get('elasticemail_password'));
				}

				break;
			default :
				$this->isMail();
				break;
		}//endswitch


		$this->PluginDir = dirname(__FILE__).DS;
		$this->CharSet = strtolower($this->config->get('charset'));
		if(empty($this->CharSet)) $this->CharSet = 'utf-8';

		$this->clearAll();

		$this->Encoding = $this->config->get('encoding_format');
		if(empty($this->Encoding)) $this->Encoding = '8bit';

		$this->WordWrap = intval($this->config->get('word_wrapping', 0));

		@ini_set('pcre.backtrack_limit', 1000000);

		$this->SMTPOptions = array("ssl" => array("verify_peer" => false, "verify_peer_name" => false, "allow_self_signed" => true));
	}//endfct

	public function send(){
		if(empty($this->ReplyTo) && empty($this->ReplyToQueue)){
			$this->_addReplyTo(empty($this->replyemail) ? $this->config->get('reply_email') : $this->replyemail, empty($this->replyname) ? $this->config->get('reply_name') : $this->replyname);
		}

		if((bool)$this->config->get('embed_images', 0) && $this->Mailer != 'elasticemail'){
			$this->embedImages();
		}

		if(empty($this->Subject) OR empty($this->Body)){
			$this->reportMessage = acymailing_translation('SEND_EMPTY');
			$this->errorNumber = 8;
			if($this->report){
				acymailing_enqueueMessage($this->reportMessage, 'error');
			}
			return false;
		}

		if(!$this->alreadyCheckedAddresses){
			$this->alreadyCheckedAddresses = true;

			$replyToTmp = '';
			if(!empty($this->ReplyTo)){
				$replyToTmp = reset($this->ReplyTo);
				$replyToTmp = $replyToTmp[0];
			}elseif(!empty($this->ReplyToQueue)){
				$replyToTmp = reset($this->ReplyToQueue);
				$replyToTmp = $replyToTmp[1];
			}

			if(empty($replyToTmp) || !$this->userHelper->validEmail($replyToTmp)){
				$this->reportMessage = acymailing_translation('VALID_EMAIL').' ( '.acymailing_translation('REPLYTO_ADDRESS').' : '.(empty($this->ReplyTo) ? '' : $replyToTmp).' ) ';
				$this->errorNumber = 9;
				if($this->report){
					acymailing_enqueueMessage($this->reportMessage, 'error');
				}
				return false;
			}

			if(empty($this->From) || !$this->userHelper->validEmail($this->From)){
				$this->reportMessage = acymailing_translation('VALID_EMAIL').' ( '.acymailing_translation('FROM_ADDRESS').' : '.$this->From.' ) ';
				$this->errorNumber = 9;
				if($this->report){
					acymailing_enqueueMessage($this->reportMessage, 'error');
				}
				return false;
			}

			if(!empty($this->Sender) && !$this->userHelper->validEmail($this->Sender)){
				$this->reportMessage = acymailing_translation('VALID_EMAIL').' ( '.acymailing_translation('BOUNCE_ADDRESS').' : '.$this->Sender.' ) ';
				$this->errorNumber = 9;
				if($this->report){
					acymailing_enqueueMessage($this->reportMessage, 'error');
				}
				return false;
			}
		}

		if(!empty($this->favicon)){
			$faviconHeader = '<link rel="shortcut icon" href="'.$this->favicon.'" type="image/x-icon" />';
			$this->Body = str_replace('</head>', $faviconHeader.'</head>', $this->Body);
		}

		if(function_exists('mb_convert_encoding') && !empty($this->sendHTML)){
			$this->Body = mb_convert_encoding($this->Body, 'HTML-ENTITIES', 'UTF-8');
			$this->Body = str_replace(array('&amp;', '&sigmaf;'), array('&', 'ς'), $this->Body);
		}

		if($this->CharSet != 'utf-8'){
			$this->Body = $this->encodingHelper->change($this->Body, 'UTF-8', $this->CharSet);
			$this->Subject = $this->encodingHelper->change($this->Subject, 'UTF-8', $this->CharSet);
			if(!empty($this->AltBody)) $this->AltBody = $this->encodingHelper->change($this->AltBody, 'UTF-8', $this->CharSet);
		}

		if(strpos($this->Host, 'elasticemail')){
			$this->addCustomHeader('referral:2f0447bb-173a-459d-ab1a-ab8cbebb9aab');
		}

		$this->Subject = str_replace(array('’', '“', '”', '–'), array("'", '"', '"', '-'), $this->Subject);

		$this->Body = str_replace(" ", ' ', $this->Body);

		ob_start();
		$result = parent::send();
		$warnings = ob_get_clean();

		if(!empty($warnings) && strpos($warnings, 'bloque')){
			$result = false;
		}
		
		$receivers = array();
		foreach($this->to as $oneReceiver){
			$receivers[] = $oneReceiver[0];
		}
		if(!$result){
			$this->reportMessage = acymailing_translation_sprintf('SEND_ERROR', '<b><i>'.$this->Subject.'</i></b>', '<b><i>'.implode(' , ', $receivers).'</i></b>');
			if(!empty($this->ErrorInfo)) $this->reportMessage .= ' | '.$this->ErrorInfo;
			if(!empty($warnings)) $this->reportMessage .= ' | '.$warnings;
			$this->errorNumber = 1;
			if($this->report){
				$this->reportMessage = str_replace('Could not instantiate mail function', '<a target="_blank" href="'.ACYMAILING_REDIRECT.'could-not-instantiate-mail-function" title="'.acymailing_translation('TELL_ME_MORE').'">Could not instantiate mail function</a>', $this->reportMessage);
				acymailing_enqueueMessage(nl2br($this->reportMessage), 'error');
			}
		}else{
			$this->reportMessage = acymailing_translation_sprintf('SEND_SUCCESS', '<b><i>'.$this->Subject.'</i></b>', '<b><i>'.implode(' , ', $receivers).'</i></b>');
			if(!empty($warnings)) $this->reportMessage .= ' | '.$warnings;
			if($this->report){
				acymailing_enqueueMessage(nl2br($this->reportMessage), 'message');
			}
		}

		return $result;
	}

	public function load($mailid){
		$mailClass = acymailing_get('class.mail');
		$this->defaultMail[$mailid] = $mailClass->get($mailid);

		if(empty($this->defaultMail[$mailid]->mailid)) return false;

		if(empty($this->defaultMail[$mailid]->altbody)) $this->defaultMail[$mailid]->altbody = $this->textVersion($this->defaultMail[$mailid]->body);

		if(!empty($this->defaultMail[$mailid]->attach)){
			$this->defaultMail[$mailid]->attachments = array();

			foreach($this->defaultMail[$mailid]->attach as $oneAttach){
				$attach = new stdClass();
				$attach->name = basename($oneAttach->filename);
				$attach->filename = str_replace(array('/', '\\'), DS, ACYMAILING_ROOT).$oneAttach->filename;
				$attach->url = ACYMAILING_LIVE.$oneAttach->filename;
				$this->defaultMail[$mailid]->attachments[] = $attach;
			}
		}

		if(!empty($this->defaultMail[$mailid]->favicon) && !empty($this->defaultMail[$mailid]->favicon->filename)){
			$this->defaultMail[$mailid]->favicon = ACYMAILING_LIVE.str_replace(DS, '/', $this->defaultMail[$mailid]->favicon->filename);
		}else{
			$this->defaultMail[$mailid]->favicon = '';
		}

		if(!empty($this->defaultMail[$mailid]->tempid)){
			$templateClass = acymailing_get('class.template');
			$this->defaultMail[$mailid]->template = $templateClass->get($this->defaultMail[$mailid]->tempid);
		}

		$this->triggerTagsWithRightLanguage($this->defaultMail[$mailid], $this->loadedToSend);

		$this->defaultMail[$mailid]->body = acymailing_absoluteURL($this->defaultMail[$mailid]->body);

		return $this->defaultMail[$mailid];
	}

	public function clearAll(){
		$this->Subject = '';
		$this->Body = '';
		$this->AltBody = '';
		$this->ClearAllRecipients();
		$this->ClearAttachments();
		$this->ClearCustomHeaders();
		$this->ClearReplyTos();
		$this->errorNumber = 0;
		$this->MessageID = '';
		$this->ErrorInfo = '';

		$this->setFrom($this->config->get('from_email'), $this->config->get('from_name'));


	}

	public function sendOne($mailid, $receiverid){
		$this->clearAll();

		if(!isset($this->defaultMail[$mailid])){
			$this->loadedToSend = true;
			if(!$this->load($mailid)){
				$this->reportMessage = 'Can not load the e-mail : '.htmlspecialchars($mailid, ENT_COMPAT, 'UTF-8');
				if($this->report){
					acymailing_enqueueMessage($this->reportMessage, 'error');
				}
				$this->errorNumber = 2;
				return false;
			}
		}


		if(!isset($this->forceVersion) AND $this->checkPublished AND empty($this->defaultMail[$mailid]->published)){
			$this->reportMessage = acymailing_translation_sprintf('SEND_ERROR_PUBLISHED', htmlspecialchars($mailid, ENT_COMPAT, 'UTF-8'));
			$this->errorNumber = 3;
			if($this->report){
				acymailing_enqueueMessage($this->reportMessage, 'error');
			}
			return false;
		}

		if(!is_object($receiverid)){
			$receiver = $this->subscriberClass->get($receiverid);
			if(empty($receiver->subid) AND is_string($receiverid) AND $this->autoAddUser){
				if($this->userHelper->validEmail($receiverid)){
					$newUser = new stdClass();
					$newUser->email = $receiverid;
					$this->subscriberClass->checkVisitor = false;
					$this->subscriberClass->sendConf = false;
					$subid = $this->subscriberClass->save($newUser);
					$receiver = $this->subscriberClass->get($subid);
				}
			}
		}else{
			$receiver = $receiverid;
		}

		if(empty($receiver->email)){
			$this->reportMessage = acymailing_translation_sprintf('SEND_ERROR_USER', '<b><i>'.(isset($receiver->subid) ? $receiver->subid : htmlspecialchars($receiverid, ENT_COMPAT, 'UTF-8')).'</i></b>');
			if($this->report){
				acymailing_enqueueMessage($this->reportMessage, 'error');
			}
			$this->errorNumber = 4;
			return false;
		}


		$this->MessageID = "<".preg_replace("|[^a-z0-9+_]|i", '', base64_encode(rand(0, 9999999))."AC".$receiver->subid."Y".$this->defaultMail[$mailid]->mailid."BA".base64_encode(time().rand(0, 99999)))."@".$this->serverHostname().">";

		if(strpos($this->Host, 'mailjet') !== false && !empty($this->defaultMail[$mailid]->alias)){
			$this->addCustomHeader('X-Mailjet-Campaign: '.$this->defaultMail[$mailid]->alias);
		}

		if(!isset($this->forceVersion)){
			if($this->checkConfirmField AND empty($receiver->confirmed) AND $this->config->get('require_confirmation', 0) AND strpos($this->defaultMail[$mailid]->alias, 'confirm') === false){
				$this->reportMessage = acymailing_translation_sprintf('SEND_ERROR_CONFIRMED', '<b><i>'.htmlspecialchars($receiver->email, ENT_COMPAT, 'UTF-8').'</i></b>');
				if($this->report){
					acymailing_enqueueMessage($this->reportMessage, 'error');
				}
				$this->errorNumber = 5;
				return false;
			}

			if($this->checkEnabled AND empty($receiver->enabled) AND strpos($this->defaultMail[$mailid]->alias, 'enable') === false){
				$this->reportMessage = acymailing_translation_sprintf('SEND_ERROR_APPROVED', '<b><i>'.htmlspecialchars($receiver->email, ENT_COMPAT, 'UTF-8').'</i></b>');
				if($this->report){
					acymailing_enqueueMessage($this->reportMessage, 'error');
				}
				$this->errorNumber = 6;
				return false;
			}
		}


		if($this->checkAccept AND empty($receiver->accept)){
			$this->reportMessage = acymailing_translation_sprintf('SEND_ERROR_ACCEPT', '<b><i>'.htmlspecialchars($receiver->email, ENT_COMPAT, 'UTF-8').'</i></b>');
			if($this->report){
				acymailing_enqueueMessage($this->reportMessage, 'error');
			}
			$this->errorNumber = 7;
			return false;
		}

		$addedName = '';
		if($this->config->get('add_names', true)){
			$nameTmp = acymailing_translation('ACY_TO_NAME');
			$testTag = preg_match_all('/\[(.*)\]/U', $nameTmp, $matches);
			if($testTag != 0){
				foreach($matches[0] as $i => $oneMatch){
					$replaceValue = '';
					if(!empty($receiver->{$matches[1][$i]})) $replaceValue = $receiver->{$matches[1][$i]};
					$nameTmp = str_replace($oneMatch, $replaceValue, $nameTmp);
				}
			}
			$addedName = $this->cleanText($nameTmp);
			if($addedName == $this->cleanText($receiver->email)) $addedName = '';
		}
		$this->addAddress($this->cleanText($receiver->email), $addedName);

		if(!isset($this->forceVersion)){
			$this->isHTML($receiver->html && $this->defaultMail[$mailid]->html);
		}else{
			$this->isHTML((bool)$this->forceVersion);
		}

		$this->Subject = $this->defaultMail[$mailid]->subject;

		if($this->sendHTML){
			$this->Body = $this->defaultMail[$mailid]->body;
			if($this->config->get('multiple_part', false)){
				$this->AltBody = $this->defaultMail[$mailid]->altbody;
			}
		}else{
			$this->Body = $this->defaultMail[$mailid]->altbody;
		}

		$this->setFrom($this->defaultMail[$mailid]->fromemail, $this->defaultMail[$mailid]->fromname);
		$this->_addReplyTo($this->defaultMail[$mailid]->replyemail, $this->defaultMail[$mailid]->replyname);

		$this->defaultMail[$mailid]->bccaddresses = isset($this->defaultMail[$mailid]->bccaddresses) ? $this->defaultMail[$mailid]->bccaddresses : '';
		$bcc = trim(str_replace(array(',', ' '), ';', $this->defaultMail[$mailid]->bccaddresses));
		if(!empty($bcc)){
			$allBcc = explode(';', $bcc);
			foreach($allBcc as $oneBcc){
				if(empty($oneBcc)) continue;
				$this->AddBCC($oneBcc);
			}
		}

		if(!empty($this->defaultMail[$mailid]->attachments)){
			if($this->config->get('embed_files')){
				foreach($this->defaultMail[$mailid]->attachments as $attachment){
					$this->addAttachment($attachment->filename);
				}
			}else{
				$attachStringHTML = '<br /><fieldset><legend>'.acymailing_translation('ATTACHMENTS').'</legend><table>';
				$attachStringText = "\n"."\n".'------- '.acymailing_translation('ATTACHMENTS').' -------';
				foreach($this->defaultMail[$mailid]->attachments as $attachment){
					$attachStringHTML .= '<tr><td><a href="'.$attachment->url.'" target="_blank">'.$attachment->name.'</a></td></tr>';
					$attachStringText .= "\n".'-- '.$attachment->name.' ( '.$attachment->url.' )';
				}
				$attachStringHTML .= '</table></fieldset>';

				if($this->sendHTML){
					$this->Body .= $attachStringHTML;
					if(!empty($this->AltBody)) $this->AltBody .= "\n".$attachStringText;
				}else{
					$this->Body .= $attachStringText;
				}
			}
		}

		$this->replaceParams();

		if(!empty($this->introtext)){
			$this->Body = $this->introtext.$this->Body;
			$this->AltBody = $this->textVersion($this->introtext).$this->AltBody;
		}


		$this->body = &$this->Body;
		$this->altbody = &$this->AltBody;
		$this->subject = &$this->Subject;
		$this->from = &$this->From;
		$this->fromName = &$this->FromName;
		$this->replyto = &$this->ReplyTo;
		$this->replyname = $this->defaultMail[$mailid]->replyname;
		$this->replyemail = $this->defaultMail[$mailid]->replyemail;
		$this->mailid = $this->defaultMail[$mailid]->mailid;
		$this->key = $this->defaultMail[$mailid]->key;
		$this->alias = $this->defaultMail[$mailid]->alias;
		$this->type = $this->defaultMail[$mailid]->type;
		$this->tempid = $this->defaultMail[$mailid]->tempid;
		$this->sentby = $this->defaultMail[$mailid]->sentby;
		$this->userid = $this->defaultMail[$mailid]->userid;
		$this->filter = $this->defaultMail[$mailid]->filter;
		$this->template = @$this->defaultMail[$mailid]->template;
		$this->language = @$this->defaultMail[$mailid]->language;
		$this->favicon = @$this->defaultMail[$mailid]->favicon;

		$this->replaceParams();

		if(empty($receiver->key) && !empty($receiver->subid)){
			$receiver->key = acymailing_generateKey(14);
			acymailing_query('UPDATE '.acymailing_table('subscriber').' SET `key`= '.acymailing_escapeDB($receiver->key).' WHERE subid = '.(int)$receiver->subid.' LIMIT 1');
		}

		if(strpos($receiver->email, '@mail-tester.com') !== false){
			$currentUser = $this->subscriberClass->get(acymailing_currentUserEmail());
			if(empty($currentUser)) $currentUser = $receiver;
			acymailing_trigger('acymailing_replaceusertags', array(&$this, &$currentUser, true));
		}else{
			acymailing_trigger('acymailing_replaceusertags', array(&$this, &$receiver, true));
		}

		if($this->sendHTML){
			if(!empty($this->AltBody)) $this->AltBody = $this->textVersion($this->AltBody, false);
		}else{
			$this->Body = $this->textVersion($this->Body, false);
		}

		$status = $this->send();
		if($this->trackEmail){
			$helperQueue = acymailing_get('helper.queue');
			$statsAdd = array();
			$statsAdd[$this->mailid][$status][$this->sendHTML][] = $receiver->subid;
			$helperQueue->statsAdd($statsAdd);
			$this->trackEmail = false;
		}
		return $status;
	}

	private function replaceParams(){
		if(empty($this->parameters)) return;

		$this->generateAllParams();
		$keysparams = array_keys($this->parameters);
		$this->Subject = str_replace($keysparams, $this->parameters, $this->Subject);
		$this->Body = str_replace($keysparams, $this->parameters, $this->Body);
		if(!empty($this->AltBody)) $this->AltBody = str_replace($keysparams, $this->parameters, $this->AltBody);


		if(!empty($this->From)) str_replace($keysparams, $this->parameters, $this->From);
		if(!empty($this->FromName)) str_replace($keysparams, $this->parameters, $this->FromName);

		if(!empty($this->replyname)) $this->replyname = str_replace($keysparams, $this->parameters, $this->replyname);
		if(!empty($this->replyemail)) $this->replyemail = str_replace($keysparams, $this->parameters, $this->replyemail);
		if(!empty($this->ReplyTo)){
			foreach($this->ReplyTo as $i => $replyto){
				foreach($replyto as $a => $oneval){
					$this->ReplyTo[$i][$a] = str_replace($keysparams, $this->parameters, $this->ReplyTo[$i][$a]);
				}
			}
		}
	}

	protected function embedImages(){
		preg_match_all('/(src|background)=[\'|"]([^"\']*)[\'|"]/Ui', $this->Body, $images);
		$result = true;

		if(empty($images[2])) return $result;

		$mimetypes = array('bmp' => 'image/bmp', 'gif' => 'image/gif', 'jpeg' => 'image/jpeg', 'jpg' => 'image/jpeg', 'jpe' => 'image/jpeg', 'png' => 'image/png', 'tiff' => 'image/tiff', 'tif' => 'image/tiff');

		$allimages = array();

		foreach($images[2] as $i => $url){
			if(isset($allimages[$url])) continue;
			$allimages[$url] = 1;

			$path = $url;
			$base = str_replace(array('http://www.', 'https://www.', 'http://', 'https://'), '', ACYMAILING_LIVE);
			$replacements = array('https://www.'.$base, 'http://www.'.$base, 'https://'.$base, 'http://'.$base);
			foreach($replacements as $oneReplacement){
				if(strpos($url, $oneReplacement) === false) continue;
				$path = str_replace(array($oneReplacement, '/'), array(ACYMAILING_ROOT, DS), urldecode($url));
				break;
			}

			$filename = str_replace(array('%', ' '), '_', basename($url));
			$md5 = md5($filename);
			$cid = 'cid:'.$md5;
			$fileParts = explode(".", $filename);
			if(empty($fileParts[1])) continue;
			$ext = strtolower($fileParts[1]);
			if(!isset($mimetypes[$ext])) continue;
			$mimeType = $mimetypes[$ext];
			if($this->addEmbeddedImage($path, $md5, $filename, 'base64', $mimeType)){
				$this->Body = preg_replace("/".preg_quote($images[0][$i], '/')."/Ui", $images[1][$i]."=\"".$cid."\"", $this->Body);
			}else{
				$result = false;
			}
		}
		return $result;
	}

	public function textVersion($html, $fullConvert = true){

		$html = acymailing_absoluteURL($html);

		if($fullConvert){
			$html = preg_replace('# +#', ' ', $html);
			$html = str_replace(array("\n", "\r", "\t"), '', $html);
		}


		$removepictureslinks = "#< *a[^>]*> *< *img[^>]*> *< *\/ *a *>#isU";
		$removeScript = "#< *script(?:(?!< */ *script *>).)*< */ *script *>#isU";
		$removeStyle = "#< *style(?:(?!< */ *style *>).)*< */ *style *>#isU";
		$removeStrikeTags = '#< *strike(?:(?!< */ *strike *>).)*< */ *strike *>#iU';
		$replaceByTwoReturnChar = '#< *(h1|h2)[^>]*>#Ui';
		$replaceByStars = '#< *li[^>]*>#Ui';
		$replaceByReturnChar1 = '#< */ *(li|td|dt|tr|div|p)[^>]*> *< *(li|td|dt|tr|div|p)[^>]*>#Ui';
		$replaceByReturnChar = '#< */? *(br|p|h1|h2|legend|h3|li|ul|dd|dt|h4|h5|h6|tr|td|div)[^>]*>#Ui';
		$replaceLinks = '/< *a[^>]*href *= *"([^#][^"]*)"[^>]*>(.+)< *\/ *a *>/Uis';

		$text = preg_replace(array($removepictureslinks, $removeScript, $removeStyle, $removeStrikeTags, $replaceByTwoReturnChar, $replaceByStars, $replaceByReturnChar1, $replaceByReturnChar, $replaceLinks), array('', '', '', '', "\n\n", "\n* ", "\n", "\n", '${2} ( ${1} )'), $html);

		$text = preg_replace('#(&lt;|&\#60;)([^ \n\r\t])#i', '&lt; ${2}', $text);

		$text = str_replace(array(" ", "&nbsp;"), ' ', strip_tags($text));

		$text = trim(@html_entity_decode($text, ENT_QUOTES, 'UTF-8'));

		if($fullConvert){
			$text = preg_replace('# +#', ' ', $text);
			$text = preg_replace('#\n *\n\s+#', "\n\n", $text);
		}

		return $text;
	}

	public function cleanText($text){
		return trim(preg_replace('/(%0A|%0D|\n+|\r+)/i', '', (string)$text));
	}

	public function setFrom($email, $name = '', $auto = false){

		if(!empty($email)){
			$this->From = $this->cleanText($email);
		}
		if(!empty($name) AND $this->config->get('add_names', true)){
			$this->FromName = $this->cleanText($name);
		}
	}

	private function generateAllParams(){
		$result = '<table style="border:1px solid;border-collapse:collapse;" border="1" cellpadding="10"><tr><td>Tag</td><td>Value</td></tr>';
		foreach($this->parameters as $name => $value){
			if(!is_string($value)) continue;
			$result .= '<tr><td>'.$name.'</td><td>'.$value.'</td></tr>';
		}
		$result .= '</table>';
		$this->addParam('alltags', $result);
	}

	public function addParamInfo(){
		if(!empty($_SERVER)){
			$serverinfo = array();
			foreach($_SERVER as $oneKey => $oneInfo){
				$serverinfo[] = $oneKey.' => '.strip_tags(print_r($oneInfo, true));
			}
			$this->addParam('serverinfo', implode('<br />', $serverinfo));
		}

		if(!empty($_REQUEST)){
			$postinfo = array();
			foreach($_REQUEST as $oneKey => $oneInfo){
				$postinfo[] = $oneKey.' => '.strip_tags(print_r($oneInfo, true));
			}
			$this->addParam('postinfo', implode('<br />', $postinfo));
		}
	}

	public function addParam($name, $value){
		$tagName = '{'.$name.'}';
		$this->parameters[$tagName] = $value;
	}

	protected function _addReplyTo($email, $name){
		if(empty($email)) return;
		$replyToName = $this->config->get('add_names', true) ? $this->cleanText(trim($name)) : '';
		$replyToEmail = trim($email);
		if(substr_count($replyToEmail, '@') > 1){
			$replyToEmailArray = explode(';', str_replace(array(';', ','), ';', $replyToEmail));
			$replyToNameArray = explode(';', str_replace(array(';', ','), ';', $replyToName));
			foreach($replyToEmailArray as $i => $oneReplyTo){
				$this->addReplyTo($this->cleanText($oneReplyTo), @$replyToNameArray[$i]);
			}
		}else{
			$this->addReplyTo($this->cleanText($replyToEmail), $replyToName);
		}
	}

	protected function ACY_DKIM_Sign($s){
		if(!empty($this->DKIM_passphrase)){
			$privKey = openssl_pkey_get_private($this->DKIM_private, $this->DKIM_passphrase);
		}else{
			$privKey = $this->DKIM_private;
		}
		$signature = '';
		if(openssl_sign($s, $signature, $privKey)){
			return base64_encode($signature);
		}
	}

	protected function ACY_DKIM_Add($body){
		$DKIMsignatureType = 'rsa-sha1'; // Signature & hash algorithms
		$DKIMcanonicalization = 'relaxed/simple'; // Canonicalization of header/body
		$DKIMquery = 'dns/txt'; // Query method
		$DKIMtime = time(); // Signature Timestamp = seconds since 00:00:00 - Jan 1, 1970 (UTC time zone)

		$subject = $this->encodeHeader($this->secureHeader($this->Subject));

		$subjecta_header = "Subject: $subject";
		$from = array();
		$from[0][0] = trim($this->From);
		$from[0][1] = $this->FromName;
		$fromc_header = $this->addrAppend('From', $from);
		$toy_header = $this->addrAppend('To', $this->to);

		$body = $this->DKIM_BodyC($body);
		$DKIMlen = strlen($body); // Length of body
		$DKIMb64 = base64_encode(pack("H*", sha1($body))); // Base64 of packed binary SHA-1 hash of body
		$ident = (empty($this->DKIM_identity)) ? '' : " i=".$this->DKIM_identity.";";
		$dkimhdrs = "DKIM-Signature: v=1; a=".$DKIMsignatureType."; q=".$DKIMquery."; l=".$DKIMlen."; s=".$this->DKIM_selector.";\r\n"."\tt=".$DKIMtime."; c=".$DKIMcanonicalization."; h=from:to:subject;\r\n"."\td=".$this->DKIM_domain.";".$ident." bh=".$DKIMb64.";\r\n"."\tb=";
		$toSign = $this->DKIM_HeaderC($fromc_header."\r\n".$toy_header."\r\n".$subjecta_header."\r\n".$dkimhdrs);
		$signed = wordwrap($this->ACY_DKIM_Sign($toSign), 60, "\r\n\t", true);
		if(empty($signed)) return '';
		return $dkimhdrs.$signed."\r\n";
	}

	protected function edebug($str){
		$this->ErrorInfo .= ' '.$str;
	}

	public function setWordWrap(){
		if($this->WordWrap < 1){
			return;
		}

		if(!empty($this->AltBody)) $this->AltBody = $this->wrapText($this->AltBody, $this->WordWrap);
		$this->Body = $this->wrapText($this->Body, $this->WordWrap);
	}

	public function isHTML($ishtml = true){
		parent::isHTML($ishtml);
		$this->sendHTML = $ishtml;
	}

	public function getMailMIME(){
		$result = parent::getMailMIME();

		$result = rtrim($result, $this->LE);

		if($this->Mailer != 'mail'){
			$result .= $this->LE.$this->LE;
		}

		return $result;
	}

	public static function validateAddress($address, $patternselect = 'auto'){
		return true;
	}

	function triggerTagsWithRightLanguage(&$mail, $loadedToSend){
		if(!empty($mail->language) && !in_array($mail->language, acymailing_getLanguageLocale())){
			$emaillangcode = '';

			$languages = acymailing_getLanguages();
			foreach($languages as $key => $oneLang){
				if($oneLang->sef != $mail->language) continue;
				$emaillangcode = $key;
				break;
			}

			if(!empty($emaillangcode)){
				$previousLanguage = acymailing_setLanguage($emaillangcode);
				acymailing_loadLanguageFile(ACYMAILING_COMPONENT, ACYMAILING_ROOT, $emaillangcode, true);
				acymailing_loadLanguageFile(ACYMAILING_COMPONENT.'_custom', ACYMAILING_ROOT, $emaillangcode, true);
				acymailing_loadLanguageFile('joomla', ACYMAILING_BASE, $emaillangcode, true);
			}
		}

		acymailing_trigger('acymailing_replacetags', array(&$mail, &$loadedToSend));

		if(empty($previousLanguage)) return;
		acymailing_setLanguage($previousLanguage);
		acymailing_loadLanguageFile(ACYMAILING_COMPONENT, ACYMAILING_ROOT, $previousLanguage, true);
		acymailing_loadLanguageFile(ACYMAILING_COMPONENT.'_custom', ACYMAILING_ROOT, $previousLanguage, true);
		acymailing_loadLanguageFile('joomla', ACYMAILING_BASE, $previousLanguage, true);
	}
}
