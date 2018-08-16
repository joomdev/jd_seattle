<?php
namespace G2\L\T;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
trait Emails{
	public function Emails(){
		return EmailsObject::getInstance($this);
	}
	
}

class EmailsObject extends \G2\L\Component{
	use \G2\L\T\Model;
	use \G2\L\T\Helper;
	
	var $models = [
		'Email' => '\G2\A\M\Email',
		'EmailElement' => '\G2\A\M\EmailElement',
		'Element' => '\G2\A\M\Element',
	];
	
	var $helpers = [
		'Parser' => '\G2\H\Parser2',
	];
	
	function select($module, $name){
		static $emails;
		
		if(empty($emails[$module][$name])){
			$this->Model('Email')->hasMany($this->Model('EmailElement'), 'EmailElement', 'email_id', ['belongsTo' => [[$this->Model('Element'), 'Element', 'element_id']]], true)
			->where('EmailElement.enabled', 1)
			->order(['ordering' => 'asc'])
			->settings(['json' => ['Element.params', 'EmailElement.params']]);
			
			$emails[$module][$name] = $this->Model('Email')
			->where('module', $module)
			->where('name', $name)
			->where('enabled', 1)
			->order(['core' => 'asc'])
			->order(['ordering' => 'desc'])
			->select('first', ['json' => ['params']]);
		}
		
		return $emails[$module][$name];
	}
	
	function view($module, $name){
		$email = $this->select($module, $name);
		
		if(!empty($email)){
			if(!empty($email['EmailElement'])){
				foreach($email['EmailElement'] as $k => $element){
					$email['EmailElement'][$k] = array_replace_recursive($email['Element'][$k], $email['EmailElement'][$k]);
				}
				
				//$elements = \G2\L\Arr::getVal($elements, ['[n]', 'EmailElement'], []);
				$elements = $email['EmailElement'];
				
				return $this->controller->View()->view('views.emails.body', ['elements' => $elements], true);
			}
		}
		
		
	}
	
	function send($module, $name, $to){
		$email = $this->select($module, $name);
		
		if(!empty($email)){
			$mailer = new \G2\L\Mail();
			$from_name = !empty($email['Email']['params']['from_name']) ? $email['Email']['params']['from_name'] : '';
			$from_email = !empty($email['Email']['params']['from_email']) ? $email['Email']['params']['from_email'] : '';
			$mailer->from($from_email, $from_name);
			echo $to;
			echo $this->view($module, $name);
			return;
			$mailer->to($to)
			->subject($this->Helper('Parser')->parse($email['Email']['params']['subject']))
			->body($this->view($module, $name))
			->send();
		}
	}
	
}
?>