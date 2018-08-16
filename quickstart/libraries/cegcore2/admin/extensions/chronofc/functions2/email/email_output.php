<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	
	$_result = false;
	$_errors = [];
	$_debug = [];
	
	if(!empty($function['recipients']) AND !empty($function['subject']) AND (!empty($function['body']) OR !empty($function['autoemail']))){
		$mailer = new \G2\L\Mail();
		
		$addresses = explode(',', $function['recipients']);
		
		$recipients = [];
		foreach($addresses as $address){
			if(!empty($address)){
				$list = $this->Parser->parse(trim($address), true);
				
				if(!empty($list)){
					if(is_string($list)){
						$list = explode(',', $list);
						$list = array_map('trim', $list);
						$list = array_filter($list);
					}
					$recipients = array_merge($recipients, $list);
				}
			}
		}
		$this->Parser->debug[$function['name']]['recipients'] = $recipients;
		
		$subject = $this->Parser->parse($function['subject'], true);
		
		$this->Parser->debug[$function['name']]['subject'] = $subject;
		
		if(!empty($function['autoemail'])){
			$connection = $this->Parser->_connection();
			
			$stored = \GApp::session()->get($connection['alias'].'.email', []);
			
			if(!empty($stored)){
				$abody = '<table width="100%" cellpadding="5" cellspacing="3" border="0" class="ui table">';
				if(!empty($function['template']['header'])){
					$abody = $function['template']['header'];
				}
				
				foreach($stored as $field){
					if(!empty($field['name'])){
						$fname = rtrim(str_replace(['[]', '[', ']', '(N)'], ['(N)', '.', '', '.[n]'], $field['name']), '.');
						
						if(is_null($this->data($fname))){
							continue;
						}
						
						$fname_tag = '{data'.(strpos($fname, '[n]') !== false ? '/join' : '').':'.$fname.'}';
						if($field['type'] == 'field_textarea'){
							$fname_tag = '{data.br:'.$fname.'}';
						}
						if(!empty($field['options']) AND !is_null($this->data($fname))){
							if(is_array($this->data($fname))){
								$fname_tag = implode(', ', array_intersect_key($field['options'], array_flip($this->data($fname))));
							}else{
								if(isset($field['options'][$this->data($fname)])){
									$fname_tag = $field['options'][$this->data($fname)];
								}
							}
						}
						
						if(empty($function['template']['body'])){
							$abody .= '<tr><td width="30%" valign="top" align="right"><strong>'.$field['label'].'</strong></td><td width="70%" valign="top" align="left">'.$fname_tag.'</td></tr>';
						}else{
							$abody .= str_replace(['{label}', '{value}'], [$field['label'], $fname_tag], $function['template']['body']);
						}
					}else{
						
					}
				}
				
				if(!empty($function['template']['footer'])){
					$abody .= $function['template']['footer'];
				}else{
					$abody .= '</table>';
				}
				
				if(strpos($function['body'], '{AUTO_FIELDS}') !== false){
					$function['body'] = str_replace('{AUTO_FIELDS}', $abody, $function['body']);
				}else{
					$function['body'] = $function['body']."\n".$abody;
				}
			}
			
			//\GApp::session()->clear($connection['alias'].'.email');
		}
		
		$body = $this->Parser->parse($function['body'], true);
		
		$this->Parser->debug[$function['name']]['body'] = $body;
		
		$mailer->mode = !empty($function['mode']) ? $function['mode'] : 'html';
		
		if(!empty($function['advanced_template'])){
			$body = $mailer->prepareContent($body);
		}
		
		//encrypt the email
		if(!empty($function['encrypted']) AND class_exists('Crypt_GPG')){
			$mySecretKeyId = trim($function['gpg_sec_key']);
			$gpg = new Crypt_GPG();
			$gpg->addEncryptKey($mySecretKeyId);
			$body = $gpg->encrypt($body);
		}
		
		if(!empty($function['autofields'])){
			$connection = $this->Parser->_connection();
			
			$stored = \GApp::session()->get($connection['alias'].'.attach', []);
			
			if(!empty($stored)){
				foreach($stored as $field){
					$fname = $field['name'];
					if(!empty($field['path'])){
						//$filepath = $this->Parser->parse($field['path'], true);
						$function['attachments'] = $function['attachments']."\n".$field['path'];
					}
				}
			}
			
			//\GApp::session()->clear($connection['alias'].'.attach');
		}
		
		if(!empty($function['attachments'])){
			list($files) = $this->Parser->multiline(trim($function['attachments']), false);
			
			//$this->Parser->debug[$function['name']]['files'] = $files;
			
			foreach($files as $file){
				if(!empty($file) AND strpos($file, DS) !== false){
					$file = $this->Parser->parse($file, true);
					$mailer->attachments($file);
					$this->Parser->debug[$function['name']]['files'][] = $file;
				}else{
					$path = $this->Parser->parse($file, true);
					if(!empty($path)){
						if(is_array($path)){
							foreach($path as $file){
								$mailer->attachments($file);
								$this->Parser->debug[$function['name']]['files'][] = $file;
							}
						}else{
							$mailer->attachments($path);
							$this->Parser->debug[$function['name']]['files'][] = $path;
						}
					}
				}
			}
		}
		
		if(!empty($function['from_name']) OR !empty($function['from_email'])){
			$from_name = $this->Parser->parse($function['from_name'], true);
			$from_email = $this->Parser->parse($function['from_email'], true);
			
			$mailer->from($from_email, $from_name);
			
			$this->Parser->debug[$function['name']]['from_name'] = $from_name;
			$this->Parser->debug[$function['name']]['from_email'] = $from_email;
		}
		
		if(!empty($function['reply_name']) OR !empty($function['reply_email'])){
			$reply_name = $this->Parser->parse($function['reply_name'], true);
			$reply_email = $this->Parser->parse($function['reply_email'], true);
			
			$mailer->replyTo($reply_email, $reply_name);
			
			$this->Parser->debug[$function['name']]['reply_name'] = $reply_name;
			$this->Parser->debug[$function['name']]['reply_email'] = $reply_email;
		}
		
		if(!empty($function['cc'])){
			$cc_addresses = explode(',', $function['cc']);
			$cc_recipients = [];
			
			foreach($cc_addresses as $cc_address){
				$list = $this->Parser->parse(trim($cc_address), true);
				
				if(is_array($list)){
					$cc_recipients = array_merge($cc_recipients, $list);
				}else{
					$cc_recipients[] = $list;
				}
			}
			
			if(!empty($cc_recipients)){
				$mailer->cc($cc_recipients);
			}
			
			$this->Parser->debug[$function['name']]['cc'] = $cc_recipients;
		}
		
		if(!empty($function['bcc'])){
			$bcc_addresses = explode(',', $function['bcc']);
			$bcc_recipients = [];
			
			foreach($bcc_addresses as $bcc_address){
				$list = $this->Parser->parse(trim($bcc_address), true);
				
				if(is_array($list)){
					$bcc_recipients = array_merge($bcc_recipients, $list);
				}else{
					$bcc_recipients[] = $list;
				}
			}
			
			if(!empty($bcc_recipients)){
				$mailer->bcc($bcc_recipients);
			}
			
			$this->Parser->debug[$function['name']]['bcc'] = $bcc_recipients;
		}
		
		$_result = $mailer->to($recipients)
		->subject($subject)
		->body($body)
		->send();
	}else{
		if(empty($function['recipients'])){
			$_errors[] = rl('Recipients list is empty');
		}
		if(empty($function['subject'])){
			$_errors[] = rl('Subject is empty');
		}
		if(empty($function['body'])){
			$_errors[] = rl('Body is empty');
		}
	}
	
	if(!empty($_errors)){
		$this->Parser->messages['error'][$function['name']] = $_errors;
	}
	
	$this->set($function['name'], $_result);
	
	if(!empty($_result)){
		$this->Parser->debug[$function['name']]['result'] = rl('the Mail sent successfully.');
	}else{
		$this->Parser->debug[$function['name']]['result'] = rl('the Mail could not be sent.');
	}