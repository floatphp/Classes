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
	private $config = [];
	private $ttl;

	/**
	 * @param array $config
	 * @param int $ttl
	 */
	public function __construct(array $config = [], $ttl = 5)
	{
		// Set cache ttl
		$this->ttl = intval($ttl);

		// Set cache config
		$this->config = Arrayify::merge([
			'path'               => 'cache',
			'autoTmpFallback'    => true,
			'compressData'       => true,
			'defaultChmod'       => 0755,
			'cacheFileExtension' => 'db'
		],$config);

		// Set adapter default config
		CacheManager::setDefaultConfig(new Config($this->config));

		// Init adapter
		$this->reset();
		$this->adapter = CacheManager::getInstance('Files');
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
		$this->reset();
	}

	/**
	 * Reset cache instance
	 *
	 * @access private
	 * @param void
	 * @return void
	 */
	private function reset()
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
	 * @param mixed $tag
	 * @return bool
	 */
	public function set($data, $tag = null) : bool
	{
		$this->cache->set($data)
		->expiresAfter($this->ttl);
		if ( $tag ) {
			$tag = Stringify::formatKey($tag);
			if ( TypeCheck::isArray($tag) ) {
				$this->cache->addTags($tag);
			} else {
				$this->cache->addTag($tag);
			}
		}
		return $this->adapter->save($this->cache);
	}

	/**
	 * @access public
	 * @param string $key
	 * @param mixed $data
	 * @return bool
	 */
	public function update($key, $data) : bool
	{
		$key = Stringify::formatKey($key);
		$this->cache = $this->adapter->getItem($key);
		$this->cache->set($data)
		->expiresAfter($this->ttl);
		return $this->adapter->save($this->cache);
	}

	/**
	 * @access public
	 * @param string $key
	 * @return bool
	 */
	public function delete($key) : bool
	{
		$key = Stringify::formatKey($key);
		return $this->adapter->deleteItem($key);
	}

	/**
	 * @access public
	 * @param mixed $tag
	 * @return bool
	 */
	public function deleteByTag($tag = '') : bool
	{
		if ( TypeCheck::isArray($tag) ) {
			return $this->adapter->deleteItemsByTags($tag);
		} else {
			return $this->adapter->deleteItemsByTag($tag);
		}
	}

	/**
	 * @access public
	 * @param void
	 * @return bool
	 */
	public function isCached() : bool
	{
		return $this->cache->isHit();
	}

	/**
	 * Set filecache TTL
	 *
	 * @access public
	 * @param int
	 * @return void
	 */
	public function setTTL($ttl = 5)
	{
		$this->ttl = intval($ttl);
	}

	/**
	 * Purge filecache
	 *
	 * @access public
	 * @param void
	 * @return bool
	 */
	public function purge() : bool
	{
		return File::clearDir($this->config['path']);
	}
}
