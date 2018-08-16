<?php
namespace G2\L\CacheEngines;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class Apc extends \G2\L\Cache{
	var $expiration = 0;
	var $domain = 'gcore';
	
	function __construct($domain = 'gcore', $params = array('expiration' => 0)){
		$this->expiration = !empty($params['expiration']) ? $params['expiration'] : \G2\L\Base::getConfig('app_cache_expiry', 900);
		$this->domain = $this->__safe_id($domain);
	}

	public function get($key){
		$cache = apc_fetch($this->domain);
		if($cache !== false){
			//$cache = unserialize($cache);
			$data = array_key_exists($key, $cache) ? $cache[$key] : false;
		}else{
			return false;
		}		
		return $data;
	}

	public function set($key, $data){
		$cached_data = apc_fetch($this->domain);
		$cache = array();
		if($cached_data !== false){
			$cache = $cached_data;//unserialize($cached_data);
			if(is_null($data) AND array_key_exists($key, $cache)){
				unset($cache[$key]);
			}else{
				$cache[$key] = $data;
			}
		}else{
			$cache[$key] = $data;
		}
		return apc_store($this->domain, $cache, $this->expiration);
	}

	public function clear($key){
		$cached_data = apc_fetch($this->domain);
		$cache = array();
		if($cached_data !== false){
			$cache = $cached_data;//unserialize($cached_data);
			$cache[$key] = null;
		}else{
			$cache[$key] = null;
		}
		return apc_store($this->domain, $cache, $this->expiration);
	}
	
	public function destroy(){
		return apc_delete($this->domain);
	}
	
	protected function __safe_id($str){
		return str_replace(array('/', '\\', ' '), '_', $str);
	}
}
?>