<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Filesystem Component
 * @version   : 1.0.0
 * @category  : PHP framework
 * @copyright : (c) 2017 - 2021 JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace FloatPHP\Classes\Filesystem;

use Phpfastcache\CacheManager;
use Phpfastcache\Drivers\Files\Config;
use Phpfastcache\Config\ConfigurationOption;

/**
 * Wrapper Class for External FileCache
 */
class FileCache
{
	/**
	 * @access private
	 * @var object $cache
	 * @var object $adapter
	 * @var array $config
	 * @var int $ttl
	 */
	private $cache = false;
	private $adapter = false;
	protected static $config = null;
	protected static $ttl = null;

	/**
	 * @param void
	 */
	public function __construct()
	{
		// Set cache default params
		if ( TypeCheck::isNull(self::$config) ) {
			self::setConfig();
		}

		// Set default ttl
		if ( TypeCheck::isNull(self::$ttl) ) {
			self::expireIn();
		}

		// Set adapter default params
		CacheManager::setDefaultConfig(new Config([
			'path'               => self::$config['path'],
			'cacheFileExtension' => self::$config['extension']
		]));

		global $cacheAdapter;
		if ( !$cacheAdapter ) {
			$cacheAdapter = CacheManager::getInstance('Files');
		}
		$this->adapter = $cacheAdapter;
	}

	/**
	 * Clear adapter instances
	 *
	 * @access public
	 * @param void
	 * @return void
	 */
	public function __destruct()
	{
		CacheManager::clearInstances();
	}

	/**
	 * @access public
	 * @param string $key
	 * @return mixed
	 */
	public function get($key)
	{
		$key = Stringify::formatKey($key);
		$this->cache = $this->adapter->getItem($key);
		return $this->cache->get();
	}

	/**
	 * @access public
	 * @param mixed $data
	 * @param string $tag
	 * @return void
	 */
	public function set($data, $tag = null)
	{
		$this->cache->set($data)
		->expiresAfter(self::$ttl);
		if ( $tag ) {
			$tag = Stringify::formatKey($tag);
			$this->cache->addTag($tag);
		}
		$this->adapter->save($this->cache);
	}

	/**
	 * @access public
	 * @param string $key
	 * @param mixed $data
	 * @return void
	 */
	public function update($key, $data)
	{
		$key = Stringify::formatKey($key);
		$this->cache = $this->adapter->getItem($key);
		$this->cache->set($data)
		->expiresAfter(self::$ttl);
		$this->adapter->save($this->cache);
	}

	/**
	 * @access public
	 * @param string $key
	 * @return void
	 */
	public function delete($key)
	{
		$key = Stringify::formatKey($key);
		$this->adapter->deleteItem($key);
	}

	/**
	 * @access public
	 * @param string $tag
	 * @return void
	 */
	public function deleteByTag($tag)
	{
		$this->adapter->deleteItemsByTag($tag);
	}

	/**
	 * @access public
	 * @param void
	 * @return bool
	 */
	public function isCached()
	{
		return $this->cache->isHit();
	}

	/**
	 * Purge filecache
	 *
	 * @access public
	 * @param void
	 * @return void
	 */
	public static function purge()
	{
		File::clearDir(self::$config['path']);
	}

	/**
	 * Set filecache config
	 *
	 * @access public
	 * @param array $config
	 * @return void
	 */
	public static function setConfig($config = [])
	{
		// Set defaults
		self::$config = [
			'path'      => 'cache',
			'extension' => 'db'
		];
		self::$config = Arrayify::merge(self::$config,$config);
	}

	/**
	 * @access public
	 * @param int $ttl
	 * @return void
	 */
	public static function expireIn($ttl = 5)
	{
		self::$ttl = intval($ttl);
	}
}
