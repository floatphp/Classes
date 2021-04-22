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

/**
 * Wrapper Class for External Filecache
 */
class Cache
{
	/**
	 * @access private
	 * @var object $cache, cache object
	 * @var object $adapter, adapter object
	 * @var array $config, cache config
	 */
	private $cache = false;
	private $adapter = false;
	private static $config = [];

	private const PATH = 'Storage/cache/app';
	private const EXPIRE = 5;

	/**
	 * @param void
	 * @return void
	 */
	public function __construct()
	{
		// Set cache default params
		if ( !self::$config ) {
			self::setConfig();
		}

		// Set adapter default params
		CacheManager::setDefaultConfig(new Config([
			'path' => self::$config['path'],
			'cacheFileExtension' => 'db'
		]));

		global $cacheAdapter;
		if ( !$cacheAdapter ) {
			$cacheAdapter = CacheManager::getInstance('Files');
		}
		$this->adapter = $cacheAdapter;
	}

	/**
	 * @access public
	 * @param void
	 * @return void
	 */
	public function __destruct()
	{
		// Clear adapter instances
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
	 * @param string $tag null
	 * @return void
	 */
	public function set($data, $tag = null)
	{
		$this->cache->set($data)
		->expiresAfter(self::$config['expire']);
		if ($tag) {
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
		->expiresAfter(self::$config['expire']);
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
	 * @return boolean
	 */
	public function isCached()
	{
		return $this->cache->isHit();
	}

	/**
	 * @access public
	 * @param void
	 * @return void
	 */
	public static function remove()
	{
		// Secured removing
		if ( Stringify::contains(self::$config['path'], self::getRoot()) ) {
			File::clearDir(self::$config['path']);
		}
	}

	/**
	 * @param void
	 * @return void
	 */
	public static function setConfig($config = [])
	{
		// Set defaults
		self::$config = [
			'path'   => self::PATH,
			'expire' => self::EXPIRE
		];
		self::$config = array_merge(self::$config, $config);

		// Fixed cache relative
		self::$config['path'] = self::getRoot() .'/'. self::$config['path'];
	}

	/**
	 * @param void
	 * @return void
	 */
	private static function getRoot()
	{
		return Stringify::formatPath(dirname(dirname(dirname(__DIR__))));
	}
}
