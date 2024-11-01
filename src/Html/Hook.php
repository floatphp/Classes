<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Html Component
 * @version    : 1.1.0
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Html;

use FloatPHP\Classes\Filesystem\{
	TypeCheck, Arrayify
};

/**
 * Built-in Hook class,
 * @uses Inspired by WordPress kernel https://make.wordpress.org
 */
class Hook
{
	/**
	 * @access protected
	 * @var array $filters
	 * @var array $mergedFilters
	 * @var array $actions
	 * @var array $currentFilter
	 */
	protected $filters = [];
	protected $mergedFilters = [];
	protected $actions = [];
	protected $currentFilter = [];

	/**
	 * @access private
	 * @var int const PRIORITY
	 */
	private const PRIORITY = 50;

	/**
	 * Get singleton hook instance.
	 *
	 * @access public
	 * @return object
	 */
	public static function getInstance() : Hook
	{
		static $instance;
		if ( $instance === null ) {
			$instance = new self();
		}
		return $instance;
	}

	/**
	 * Add filter hook.
	 *
	 * @access public
	 * @param string $tag
	 * @param string|array $callable
	 * @param int $priority
	 * @param string $path
	 * @return true
	 */
	public function addFilter($tag, $callable, $priority = self::PRIORITY, $path = null)
	{
		$id = $this->filterUniqueId($callable);
		$this->filters[$tag][$priority][$id] = [
			'callable' => $callable,
			'path'     => TypeCheck::isString($path) ? $path : null
		];
		unset($this->mergedFilters[$tag]);
		return true;
	}

	/**
	 * Remove filter hook.
	 *
	 * @access public
	 * @param string $tag
	 * @param string|array $callable
	 * @param int $priority
	 * @return bool
	 */
	public function removeFilter($tag, $callable, $priority = self::PRIORITY)
	{
		$callable = $this->filterUniqueId($callable);
		if ( !isset($this->filters[$tag][$priority][$callable]) ) {
			return false;
		}
		unset($this->filters[$tag][$priority][$callable]);
		if ( empty($this->filters[$tag][$priority]) ) {
			unset($this->filters[$tag][$priority]);
		}
		unset($this->mergedFilters[$tag]);
		return true;
	}

	/**
	 * Remove all filters.
	 *
	 * @access public
	 * @param string $tag
	 * @param int $priority false
	 * @return bool
	 */
	public function removeFilters($tag, $priority = false)
	{
		if ( isset($this->mergedFilters[$tag]) ) {
			unset($this->mergedFilters[$tag]);
		}
		if ( !isset($this->filters[$tag]) ) {
			return true;
		}
		if ( $priority !== false && isset($this->filters[$tag][$priority]) ) {
			unset($this->filters[$tag][$priority]);
		} else {
			unset($this->filters[$tag]);
		}
		return true;
	}

	/**
	 * Check filter hook.
	 *
	 * @access public
	 * @param string $tag
	 * @param string $callable
	 * @return mixed
	 */
	public function hasFilter($tag, $callable = false)
	{
		$has = isset($this->filters[$tag]);
		if ( $callable === false || !$has ) {
			return $has;
		}
		if ( !($id = $this->filterUniqueId($callable)) ) {
			return false;
		}
		foreach ( (array)array_keys($this->filters[$tag]) as $priority ) {
			if ( isset($this->filters[$tag][$priority][$id]) ) {
				return $priority;
			}
		}
		return false;
	}

	/**
	 * Apply filter hook.
	 *
	 * @access public
	 * @param string|array $tag
	 * @param mixed $value
	 * @return mixed
	 */
	public function applyFilter($tag, $value)
	{
		$args = [];

		// Do 'all' actions first
		if ( isset($this->filters['all']) ) {
			$this->currentFilter[] = $tag;
			$args = func_get_args();
			$this->callHooks($args);
		}

		if ( !isset($this->filters[$tag]) ) {
			if (isset($this->filters['all'])) {
				array_pop($this->currentFilter);
			}
			return $value;
		}
		if ( !isset($this->filters['all']) ) {
			$this->currentFilter[] = $tag;
		}

		// Sort
		if ( !isset($this->mergedFilters[$tag]) ) {
			ksort($this->filters[$tag]);
			$this->mergedFilters[$tag] = true;
		}
		reset($this->filters[$tag]);
		if ( empty($args) ) {
			$args = func_get_args();
		}
		Arrayify::shift($args);

		do {
			foreach ( (array)current($this->filters[$tag]) as $current ) {
				if ( $current['callable'] !== null ) {
					if ( $current['path'] !== null ) {
						include_once($current['path']);
					}
					$args[0] = $value;
					$value = call_user_func_array($current['callable'], $args);
				}
			}
		} while ( next($this->filters[$tag]) !== false );

		array_pop($this->currentFilter);
		return $value;
	}

	/**
	 * Apply array filter hook.
	 *
	 * @access public
	 * @param string $tag
	 * @param array $args
	 * @return mixed
	 */
	public function applyFilterArray($tag, $args)
	{
		// Do 'all' actions first
		if ( isset($this->filters['all']) ) {
			$this->currentFilter[] = $tag;
			$allArgs = func_get_args();
			$this->callHooks($allArgs);
		}

		if ( !isset($this->filters[$tag]) ) {
			if (isset($this->filters['all'])) {
				array_pop($this->currentFilter);
			}
			return $args[0];
		}

		if ( !isset($this->filters['all']) ) {
			$this->currentFilter[] = $tag;
		}

		// Sort
		if ( !isset($this->mergedFilters[$tag]) ) {
			ksort($this->filters[$tag]);
			$this->mergedFilters[$tag] = true;
		}
		reset($this->filters[$tag]);

		do {
			foreach ( (array)current($this->filters[$tag]) as $current ) {
				if ( $current['callable'] !== null ) {
					if ( $current['path'] !== null ) {
						include_once($current['path']);
					}
					$args[0] = call_user_func_array($current['callable'], $args);
				}
			}
		} while ( next($this->filters[$tag]) !== false );

		array_pop($this->currentFilter);
		return $args[0];
	}

	/**
	 * Add action hook.
	 *
	 * @access public
	 * @param string $tag
	 * @param array $args
	 * @return mixed
	 */
	public function addAction($tag, $callable, $priority = self::PRIORITY, $path = null)
	{
		return $this->addFilter($tag, $callable, $priority, $path);
	}

	/**
	 * Check action hook.
	 *
	 * @access public
	 * @param string $tag
	 * @param array $args
	 * @return mixed
	 */
	public function hasAction($tag, $callable = false)
	{
		return $this->hasFilter($tag, $callable);
	}

	/**
	 * Remove action hook.
	 *
	 * @access public
	 * @param string $tag
	 * @param array $args
	 * @return mixed
	 */
	public function removeAction($tag, $callable, $priority = self::PRIORITY)
	{
		return $this->removeFilter($tag, $callable, $priority);
	}

	/**
	 * Remove actions hooks.
	 *
	 * @access public
	 * @param string $tag
	 * @param int $priority
	 * @return mixed
	 */
	public function removeAllActions($tag, $priority = false)
	{
		return $this->removeFilters($tag,$priority);
	}

	/**
	 * Do action hook.
	 *
	 * @access public
	 * @param string $tag
	 * @param mixed $arg
	 * @return mixed
	 */
	public function doAction($tag, $arg = '')
	{
		if ( !TypeCheck::isArray($this->actions) ) {
			$this->actions = [];
		}

		if ( !isset($this->actions[$tag]) ) {
			$this->actions[$tag] = 1;

		} else {
			++$this->actions[$tag];
		}

		// Do 'all' actions first
		if ( isset($this->filters['all']) ) {
			$this->currentFilter[] = $tag;
			$allArgs = func_get_args();
			$this->callHooks($allArgs);
		}

		if ( !isset($this->filters[$tag]) ) {
			if ( isset($this->filters['all']) ) {
				array_pop($this->currentFilter);
			}
			return false;
		}

		if ( !isset($this->filters['all']) ) {
			$this->currentFilter[] = $tag;
		}

		$args = [];
		if ( TypeCheck::isArray($arg) && isset($arg[0]) && TypeCheck::isObject($arg[0]) && 1 == count($arg) ) {
			$args[] =& $arg[0];
		} else {
			$args[] = $arg;
		}
		$numArgs = func_num_args();
		for ($a = 2; $a < $numArgs; $a++) {
			$args[] = func_get_arg($a);
		}

		// Sort
		if ( !isset($this->mergedFilters[$tag]) ) {
			ksort($this->filters[$tag]);
			$this->mergedFilters[$tag] = true;
		}
		reset($this->filters[$tag]);

		do {
			foreach ( (array)current($this->filters[$tag]) as $current ) {
				if ( $current['callable'] !== null ) {
					if ($current['path'] !== null ) {
					  include_once($current['path']);
					}
					call_user_func_array($current['callable'], $args);
				}
			}
		} while ( next($this->filters[$tag]) !== false );

		array_pop($this->currentFilter);
		return true;
	}

	/**
	 * Do array action hook.
	 *
	 * @access public
	 * @param string $tag
	 * @param array $args
	 * @return mixed
	 */
	public function doActionArray($tag, $args)
	{
		if ( !TypeCheck::isArray($this->actions) ) {
			$this->actions = [];
		}

		if ( !isset($this->actions[$tag]) ) {
			$this->actions[$tag] = 1;

		} else {
			++ $this->actions[$tag];
		}

		// Do 'all' actions first
		if ( isset($this->filters['all']) ) {
			$this->currentFilter[] = $tag;
			$allArgs = func_get_args();
			$this->callHooks($allArgs);
		}

		if ( !isset($this->filters[$tag]) ) {
			if ( isset($this->filters['all']) ) {
				array_pop($this->currentFilter);
			}
			return false;
		}

		if ( !isset($this->filters['all']) ) {
			$this->currentFilter[] = $tag;
		}

		// Sort
		if ( !isset($mergedFilters[$tag]) ) {
			ksort($this->filters[$tag]);
			$mergedFilters[$tag] = true;
		}
		reset($this->filters[$tag]);

		do {
			foreach ( (array)current($this->filters[$tag]) as $current ) {
				if ( $current['callable'] !== null ) {
					if ( $current['path'] !== null ) {
						include_once($current['path']);
					}
					call_user_func_array($current['callable'], $args);
				}
			}
		} while ( next($this->filters[$tag]) !== false );

		array_pop($this->currentFilter);
		return true;
	}

	/**
	 * Check fired action hook.
	 *
	 * @access public
	 * @param string $tag
	 * @return mixed
	 */
	public function didAction($tag)
	{
		if ( !TypeCheck::isArray($this->actions) 
		  || !isset($this->actions[$tag]) ) {
			return 0;
		}
		return $this->actions[$tag];
	}

	/**
	 * Get current filter hook.
	 *
	 * @access public
	 * @return mixed
	 */
	public function currentFilter()
	{
		return end($this->currentFilter);
	}

	/**
	 * Call all hooks.
	 *
	 * @access public
	 * @param array $args
	 * @return void
	 */
	public function callHooks($args)
	{
		reset($this->filters['all']);
		do {
			foreach ((array)current($this->filters['all']) as $current) {
				if ( $current['callable'] !== null ) {
					if ( $current['path'] !== null ) {
						include_once($current['path']);
					}
					call_user_func_array($current['callable'], $args);
				}
			}
		} while ( next($this->filters['all']) !== false );
	}

	/**
	 * Break callHooks method.
	 *
	 * @access public
	 * @param array $args
	 * @return void
	 */
	public function callHooksAfter($args)
	{
		$this->callHooks($args);
	}
	
	/**
	 * Filter unique Id.
	 *
	 * @access private
	 * @param string $callable
	 * @return mixed
	 */
	private function filterUniqueId($callable)
	{
		if ( TypeCheck::isString($callable) ) {
			return $callable;
		}

		if ( TypeCheck::isObject($callable) ) {
			// Closures are currently implemented as objects
			$callable = [$callable, ''];

		} else {
			$callable = (array)$callable;
		}

		if ( TypeCheck::isObject($callable[0]) ) {
			// Object Class Calling
			return spl_object_hash($callable[0]) . $callable[1];
		}

		if ( TypeCheck::isString($callable[0]) ) {
			// Static Calling
			return "{$callable[0]}{$callable[1]}";
		}

		return false;
	}
}
