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
	private $config = [];
	private $ttl;

	/**
	 * @param array $config
	 * @param int|string $ttl
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
			'defaultChmod'       => 755,
			'cacheFileExtension' => 'db'
		],$config);

		// Set adapter default config
		CacheManager::setDefaultConfig(new Config($this->config));

		// Init adapter
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
	 * @return bool
	 */
	public function set($data, $tag = null)
	{
		$this->cache->set($data)
		->expiresAfter($this->ttl);
		if ( $tag ) {
			$tag = Stringify::formatKey($tag);
			$this->cache->addTag($tag);
		}
		return $this->adapter->save($this->cache);
	}

	/**
	 * @access public
	 * @param string $key
	 * @param mixed $data
	 * @return bool
	 */
	public function update($key, $data)
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
	public function delete($key)
	{
		$key = Stringify::formatKey($key);
		return $this->adapter->deleteItem($key);
	}

	/**
	 * @access public
	 * @param string $tag
	 * @return bool
	 */
	public function deleteByTag($tag)
	{
		return $this->adapter->deleteItemsByTag($tag);
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
	 * Set filecache TTL
	 *
	 * @access public
	 * @param int|string
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
	public function purge()
	{
		return File::clearDir($this->config['path']);
	}
}
