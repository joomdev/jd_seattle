<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/
namespace G2\L\Payments\Checkout;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class Checkout {
	
	public static function process($data, $settings, $live = true, $return = false){
		if(empty($live)){
			$url = 'https://sandbox.2checkout.com/checkout/purchase?';
		}else{
			$url = 'https://www.2checkout.com/checkout/purchase?';
		}
		
		$vars = [
			'mode' => '2CO',
			'sid' => $settings['sid'],
			
			'card_holder_name' => $data['name'],
			'email' => $data['email'],
			'street_address' => $data['street_address'],
			'street_address2' => $data['street_address2'],
			'city' => $data['city'],
			'state' => $data['state'],
			'zip' => $data['zip'],
			'country' => $data['country'],
			'phone' => $data['phone'],
			'phone_extension' => $data['phone_extension'],
			
			'ship_name' => !empty($data['ship_name']) ? $data['ship_name'] : null,
			'ship_street_address' => !empty($data['ship_street_address']) ? $data['ship_street_address'] : null,
			'ship_street_address2' => !empty($data['ship_street_address2']) ? $data['ship_street_address2'] : null,
			'ship_city' => !empty($data['ship_city']) ? $data['ship_city'] : null,
			'ship_state' => !empty($data['ship_state']) ? $data['ship_state'] : null,
			'ship_zip' => !empty($data['ship_zip']) ? $data['ship_zip'] : null,
			'ship_country' => !empty($data['ship_country']) ? $data['ship_country'] : null,
			
			'demo' => !empty($settings['demo']) ? $settings['demo'] : null,
			'paypal_direct' => !empty($settings['paypal_direct']) ? 'Y' : null,
			'currency_code' => $data['currency_code'],
			'lang' => $data['lang'],
			'merchant_order_id' => $data['order_id'],
			'purchase_step' => !empty($settings['purchase_step']) ? $settings['purchase_step'] : 'payment-method',
			'x_receipt_link_url' => $data['return_url'],
			//'coupon' => $data['coupon'],
		];
		
		$valid_attrs = [
			'type',
			'name',
			'quantity',
			'price',
			'tangible',
			'product_id',
			'description',
			'recurrence',
			'duration',
			'startup_fee',
		];
		
		$products = $data['products'];
		foreach($products as $k => $product){
			foreach($product as $attr => $val){
				if(in_array($attr, $valid_attrs)){
					$vars['li_'.$k.'_'.$attr] = $val;
				}
			}
		}
		
		$query = http_build_query($vars);
		//pr($vars);
		//die();
		$url = $url.$query;
		
		if(!empty($return)){
			return $url;
		}else{
			if(empty(\GApp::instance()->tvout)){
				\G2\L\Env::redirect($url);
			}else{
				echo '
				<script type="text/javascript">
					jQuery(document).ready(function($){
						window.location = "'.r2($url, false, true).'";
					});
				</script>';
			}
		}
	}
	
	public static function complete($data, $settings){
		if(!empty($data['key'])){
			$hashSecretWord = $settings['secret'];
			$hashSid = $settings['sid'];
			$hashTotal = $data['Order']['total'];
			$hashOrder = !empty($settings['demo']) ? 1 : $data['order_number'];
			
			$StringToHash = strtoupper(md5($hashSecretWord . $hashSid . $hashOrder . $hashTotal));

			if($StringToHash != $data['key']){
				return false;
			}else{
				$return = [];
				$return['order'] = [
					'payment_id' => $data['order_number'],
					'payment_date' => \G2\L\Dater::datetime(),
					'fraud_status' => $data['fraud_status'],
				];
				
				$return['customer'] = [
					'name' => $data['card_holder_name'],
					'email' => $data['email'],
					'phone' => $data['phone'],
					//'ip' => $data['customer_ip'],
					'country' => $data['country'],
					'currency' => $data['currency_code'],
					'ptype' => $data['pay_method'],
				];
				
				return $return;
			}
		}
		return false;
	}
	
	public static function notifications($data, $settings){
		if(!empty($data['md5_hash']) AND !empty($data['vendor_order_id'])){
			$sale_id = $data['sale_id'];
			$vendor_id = $settings['sid'];
			$invoice_id = $data['invoice_id'];
			$secret = $settings['secret'];
			
			$StringToHash = strtoupper(md5($sale_id.$vendor_id.$invoice_id.$secret));
			
			if($StringToHash != $data['md5_hash']){
				return false;
			}elseif(!empty($data['message_type'])){
				$return = [];
				$return['order_id'] = $data['vendor_order_id'];
				
				if($data['message_type'] == 'ORDER_CREATED'){
					$return['type'] = 'payment';
					$return['total'] = $data['invoice_list_amount'];
					$return['order'] = [
						'payment_id' => $data['sale_id'],
						'payment_date' => $data['sale_date_placed'],
						'fraud_status' => self::fraud($data['fraud_status']),
					];
					
					$return['customer'] = [
						'name' => $data['customer_name'],
						'email' => $data['customer_email'],
						'phone' => $data['customer_phone'],
						'ip' => $data['customer_ip'],
						'country' => $data['customer_ip_country'],
						'currency' => $data['cust_currency'],
						'ptype' => $data['payment_type'],
					];
				}elseif($data['message_type'] == 'FRAUD_STATUS_CHANGED'){
					$return['type'] = 'fraud';
					$return['order'] = [
						'fraud_status' => self::fraud($data['fraud_status']),
					];
				}elseif($data['message_type'] == 'REFUND_ISSUED'){
					$return['type'] = 'refund';
					$count = $data['item_count'];
					
					$i = 1;
					while($i <= $count){
						$return['items'][$i] = [
							'id' => $data['item_id_'.$i],
							'total' => $data['item_list_amount_'.$i],
						];
						$i++;
					}
				}
				
				return $return;
			}
		}
		return false;
	}
	
	private static function fraud($type){
		if($type == 'pass'){
			return 1;
		}else if($type == 'fail'){
			return -1;
		}else{
			return 0;
		}
	}
}