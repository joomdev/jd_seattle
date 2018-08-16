<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/
namespace G2\L;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class Mail {
	var $to = [];
	var $cc = [];
	var $bcc = [];
	var $subject = null;
	var $body = null;
	var $from_name = null;
	var $from_email = null;
	var $reply_name = null;
	var $reply_email = null;
	var $attachments = [];
	var $mode = 'html';
	
	private function reset(){
		$this->to = [];
		$this->cc = [];
		$this->bcc = [];
		$this->subject = null;
		$this->body = null;
		$this->from_name = null;
		$this->from_email = null;
		$this->reply_name = null;
		$this->reply_email = null;
		$this->attachments = [];
		$this->mode = 'html';
	}
	
	public function to($to){
		if(is_array($to)){
			$this->to = array_merge($this->to, $to);
		}else{
			$this->to[] = $to;
		}
		return $this;
	}
	
	public function attachments($attachments){
		if(is_array($attachments)){
			$this->attachments = array_merge($this->attachments, $attachments);
		}else{
			$this->attachments[] = $attachments;
		}
		return $this;
	}
	
	public function subject($subject){
		$this->subject = $subject;
		return $this;
	}
	
	public function body($body){
		$this->body = $body;
		return $this;
	}
	
	public function from($from_email, $from_name){
		$this->from_name = $from_name;
		$this->from_email = $from_email;
		return $this;
	}
	
	public function replyTo($reply_email, $reply_name){
		$this->reply_name = $reply_name;
		$this->reply_email = $reply_email;
		return $this;
	}
	
	public function cc($cc){
		if(is_array($cc)){
			$this->cc = array_merge($this->cc, $cc);
		}else{
			$this->cc[] = $cc;
		}
		return $this;
	}
	
	public function bcc($bcc){
		if(is_array($bcc)){
			$this->bcc = array_merge($this->bcc, $bcc);
		}else{
			$this->bcc[] = $bcc;
		}
		return $this;
	}
	
	public function send(){
		$this->from_name = !empty($this->from_name) ? $this->from_name : Config::get('mail.from_name');
		$this->from_email = !empty($this->from_email) ? $this->from_email : Config::get('mail.from_email');
		
		$info = [
			'to' => $this->to,
			'cc' => $this->cc,
			'bcc' => $this->bcc,
			'subject' => $this->subject,
			'from_name' => $this->from_name,
			'from_email' => $this->from_email,
			'reply_name' => $this->reply_name,
			'reply_email' => $this->reply_email,
			'mode' => $this->mode,
		];
		
		if(\G2\Globals::get('app') == 'joomla'){
			$mailer = \JFactory::getMailer();
			/*
			$mailer->setSender([$this->from_email, $this->from_name]);
			$mailer->addRecipient($this->to);
			$mailer->setSubject($this->subject);
			$mailer->setBody($this->body);
			$mailer->isHtml(true);
			foreach($this->attachments as $attachment){
				$mailer->addAttachment($attachment);
			}
			*/
			$result = $mailer->sendMail(
				$this->from_email, 
				$this->from_name, 
				$this->to, 
				$this->subject, 
				$this->body, 
				($this->mode == 'html') ? true : false, 
				$this->cc, 
				$this->bcc, 
				!empty($this->attachments) ? $this->attachments : null, 
				$this->reply_email, 
				$this->reply_name
			);
		}else if(\G2\Globals::get('app') == 'wordpress'){
			$headers = array('Content-Type: text/html; charset=UTF-8');
			
			if(!empty($this->from_email)){
				$headers[] = 'From: '.$this->from_name.' <'.$this->from_email.'>';
			}
			if(!empty($this->reply_email)){
				$headers[] = 'Reply-To: '.$this->reply_name.' <'.$this->reply_email.'>';
			}
			if(!empty($this->cc)){
				foreach($this->cc as $addr){
					$headers[] = 'Cc: '.$addr;
				}
			}
			if(!empty($this->bcc)){
				foreach($this->bcc as $addr){
					$headers[] = 'Bcc: '.$addr;
				}
			}
			
			$result = wp_mail($this->to, $this->subject, $this->body, $headers, $this->attachments);
		}else{
			$result = $this->_send($info, $this->body, $this->attachments);
		}
		
		$this->reset();
		
		if(is_bool($result)){
			return $result;
		}else{
			return false;
		}
	}

	public function _send($info = array(), $message = '', $attachments = array()){
		if(!class_exists('PHPMailer')){
			require_once(\G2\Globals::get('FRONT_PATH').'vendors'.DS.'phpmailer'.DS.'PHPMailerAutoload.php');
		}

		$mail = new \PHPMailer();
		$mail->SMTPAutoTLS = false;
		$mail->CharSet = 'utf-8';
		//get recipients
		foreach((array)$info['to'] as $address){
			$mail->AddAddress(trim($address));
		}
		//subject
		$mail->Subject = $info['subject'];
		//reply to
		$reply_name = !empty($info['reply_name']) ? $info['reply_name'] : Config::get('mail.reply_name');
		$reply_email = !empty($info['reply_email']) ? $info['reply_email'] : Config::get('mail.reply_email');
		if(!empty($reply_name) AND !empty($reply_email)){
			$mail->AddReplyTo($reply_email, $reply_name);
		}
		//from
		$from_name = !empty($info['from_name']) ? $info['from_name'] : Config::get('mail.from_name');
		$from_email = !empty($info['from_email']) ? $info['from_email'] : Config::get('mail.from_email');
		$mail->SetFrom($from_email, $from_name);

		//set custom headers
		if(!empty($info['custom'])){
			foreach($info['custom'] as $k => $v){
				$mail->addCustomHeader($k.': '.$v);
			}
		}
		
		//set CC and BCC
		if(!empty($info['cc'])){
			foreach($info['cc'] as $k => $cc){
				$mail->AddCC($cc);
			}
		}
		if(!empty($info['bcc'])){
			foreach($info['bcc'] as $k => $bcc){
				$mail->AddBCC($bcc);
			}
		}

		if(Config::get('mail.method', 'phpmail') == 'smtp'){
			$mail->IsSMTP();
			if(Config::get('mail.smtp.username') AND Config::get('mail.smtp.password')){
				$mail->SMTPAuth = true;
			}
			if(Config::get('mail.smtp.security')){
				$mail->SMTPSecure = Config::get('mail.smtp.security');
			}
			$mail->Host       = Config::get('mail.smtp.host');
			$mail->Port       = Config::get('mail.smtp.port');
			$mail->Username   = Config::get('mail.smtp.username');
			$mail->Password   = Config::get('mail.smtp.password');
		}else if(Config::get('mail.smtp.method', 'phpmail') == 'sendmail'){
			$mail->IsSendmail();
		}
		
		if(!isset($info['mode']) OR $info['mode'] == 'html'){
			$mail->AltBody = strip_tags($message);//'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
			//$message = nl2br($message);
			//$mail->MsgHTML($message);
			$mail->Body = $message;
			$mail->IsHTML(true);
		}else{
			$mail->Body = $message;
			$mail->IsHTML(false);
		}
		
		$mail->SMTPDebug = (int) Config::get('mail.smtp.debug', 0);
		//attachments
		foreach((array)$attachments as $attachment){
			if(is_array($attachment) AND !empty($attachment['path'])){
				$attachment = array_merge(array('name' => basename($attachment['path']), 'type' => 'application/octet-stream', 'encoding' => 'base64'), $attachment);
				$mail->AddAttachment($attachment['path'], $attachment['name'], $attachment['encoding'], $attachment['type']);
			}else{
				$mail->AddAttachment($attachment);
			}
		}
		
		if(!$mail->Send()){
			\GApp::session()->flash('warning', 'Mailer Error: '.$mail->ErrorInfo);
			return false;
		}

		return true;
	}
	
	public function prepareContent($content){
		if(!class_exists('\Pelago\Emogrifier')){
			require_once(\G2\Globals::get('FRONT_PATH').'vendors'.DS.'emogrifier'.DS.'Emogrifier.php');
		}
		$emogrifier = new \Pelago\Emogrifier();
		//pr($content);
		
		$template = file_get_contents(\G2\Globals::get('FRONT_PATH').'assets'.DS.'foundation'.DS.'boilerplate.html');
		$template = '{content}';
		$content = str_replace('{content}', $this->template($content), $template);
		
		$css = file_get_contents(\G2\Globals::get('FRONT_PATH').'assets'.DS.'foundation'.DS.'foundation-emails.css');

		$emogrifier->setHtml($content);
		$emogrifier->setCss($css);
		
		$content = $emogrifier->emogrify();
		
		//pr($content);
		return $content;
	}
	
	public function template($html){
		$tags = ['wrapper', 'container', 'row', 'column', 'spacer', 'menu', 'item', 'panel', 'button', 'img', 'p'];
		$news = [
			'wrapper' => '<table class="wrapper" align="center"{extras}><tr><td class="wrapper-inner">{content}</td></tr></table>',
			'container' => '<table align="center" class="container"{extras}><tbody><tr><td>{content}</td></tr></tbody></table>',
			'row' => '<table class="row{class}"{extras}><tbody><tr>{content}</tr></tbody></table>',
			'column' => '<th class="columns{class}"{extras}><table><tr><th>{content}</th><th class="expander"></th></tr></table></th>',
			'spacer' => '<table class="spacer{class}"><tbody><tr><td height="{size}px" style="font-size:{size}px;line-height:{size}px;">&#xA0;</td></tr></tbody></table>',
			'menu' => '<table class="menu{class}"{extras}><tr><td><table><tr>{content}</tr></table></td></tr></table>',
			'item' => '<th class="menu-item{class}"><a{extras}>{content}</a></th>',
			'panel' => '<table class="callout"{extras}><tr><th class="callout-inner{class}">{content}</th><th class="expander"></th></tr></table>',
			'button' => '<table class="button{class}"><tr><td><table><tr><td><a{extras}>{content}</a></td></tr></table></td></tr></table>',
			'img' => '<img class="{class}"{extras}>',
			'p' => '<p class="{class}"{extras}>{content}</p>',
		];
		$attrs = [
			'row' => ['class' => ''],
			'column' => ['class' => 'first last'],
		];
		
		foreach($tags as $tag){
			$pattern = $this->tagRegex($tag);
			preg_match_all($pattern, $html, $matches);
			
			if(!empty($matches[0])){
				foreach($matches[0] as $k => $match){
					
					$pos = strpos($html, $match);
					$newTag = str_replace('{content}', $matches[2][$k], $news[$tag]);
					
					$classes = '';
					if(!empty($matches[1][$k])){
						$str = $matches[1][$k];
						$vars = explode(' ', $str);
						$vars = array_filter($vars);
						
						$extras = [];
						$settings = [];
						foreach($vars as $k => $var){
							if(strpos($var, ':') !== false){
								unset($vars[$k]);
								$s = explode(':', $var, 2);
								$extras[$s[0]] = $s[1];
							}
							if(strpos($var, '=') !== false){
								unset($vars[$k]);
								$s = explode('=', $var, 2);
								$settings[$s[0]] = $s[1];
							}
						}
						//replace extras
						$exstrings = [];
						foreach($extras as $exname => $exval){
							$exstrings[] = $exname.'="'.$exval.'"';
						}
						$newTag = str_replace('{extras}', ' '.implode(' ', $exstrings), $newTag);
						
						//replace settings
						foreach($settings as $setting_name => $setting_val){
							$newTag = str_replace('{'.$setting_name.'}', $setting_val, $newTag);
						}
						
						$classes = implode(' ', $vars);
						//pr($classes);
					}
					
					if(!empty($classes)){
						$newTag = str_replace('{class}', ' '.$classes, $newTag);
					}else if(isset($attrs[$tag]['class'])){
						$newTag = str_replace('{class}', !empty($attrs[$tag]['class']) ? ' '.$attrs[$tag]['class'] : '', $newTag);
					}else{
						$newTag = str_replace('{class}', '', $newTag);
					}
					
					$newTag = str_replace('{extras}', '', $newTag);
					
					$html = substr_replace($html, $newTag, $pos, strlen($match));
				}
			}
		}
		
		return $html;
	}
	
	function tagRegex($tag){
		$regex = '/<'.$tag.'([^>]*?)>(.*?)<\/'.$tag.'>/is';
		return $regex;
	}
}