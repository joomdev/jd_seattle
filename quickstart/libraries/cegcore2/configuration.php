<?php
namespace G2;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class Configuration {
	///;< ? p hp die(); ? > for ini
	public static $config = [
		'site' => [
			'title' => '',
			'language' => 'en_GB',
			'autolanguage' => 0,
			'timezone' => 'UTC',
			'url' => '',
		],
		'limit' => [
			'default' => '30',
			'max' => '100',
		],
		'meta' => [
			'keywords' => '',
			'description' => '',
			'robots' => '',
		],
		'session' => [
			'handler' => 'php',
			'lifetime' => 35.5,
		],
		'cookie' => [
			'domain' => '',
			'path' => '',
		],
		'sef' => [
			'enabled' => 1,
			'rewrite' => 1,
		],
		'cache' => [
			'enabled' => 1,
			'engine' => 'file',
			'lifetime' => 900,
			'dbinfo' => [
				'enabled' => 1,
				'lifetime' => 43200,
			],
			'query' => [
				'enabled' => 0,
				'lifetime' => 3600,
			],
			'permissions' => 1,
		],
		'mail' => [
			'method' => 'phpmail',
			'from_name' => '',
			'from_email' => '',
			'reply_name' => '',
			'reply_email' => '',
			'smtp' => [
				'security' => '',
				'host' => '',
				'port' => '',
				'username' => '',
				'password' => '',
				'debug' => '',
			],
		],
		'db' => [
			'host' => '',
			'type' => '',
			'name' => '',
			'user' => '',
			'path' => '',
			'prefix' => '',
			'adapter' => 'pdo',
		],
		'error' => [
			'debug' => 0,
			'reporting' => 1,
		],
	];
}