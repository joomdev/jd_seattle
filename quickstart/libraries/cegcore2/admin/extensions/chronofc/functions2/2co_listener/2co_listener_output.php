<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	
	$vendorid = $function['sid'];
	$secretword = $function['secret'];
	$md5hash = strtoupper(md5($this->data('sale_id').$vendorid.$this->data('invoice_id').$secretword));
	
	//if the hash is ok
	if($md5hash == $this->data('md5_hash')){
		//switch messages types
		/*switch($this->data('message_type')){
			case 'ORDER_CREATED':
				$this->Parser->fevents[$function['name']]['order_created'] = 1;
				break;
			case 'FRAUD_STATUS_CHANGED':
				$this->Parser->fevents[$function['name']]['fraud_status_changed'] = 1;
				break;
			case 'REFUND_ISSUED':
				$this->Parser->fevents[$function['name']]['refund_issued'] = 1;
				break;
			default:
				$this->Parser->fevents[$function['name']]['other'] = 1;
				break;
		}*/
		if(!empty($this->data('message_type'))){
			$this->Parser->fevents[$function['name']][strtolower($this->data('message_type'))] = 1;
		}
	}else{
		$this->Parser->fevents[$function['name']]['fail'] = 1;
	}