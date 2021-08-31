<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Html Component
 * @version   : 1.0.0
 * @category  : PHP framework
 * @copyright : (c) 2017 - 2021 JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace FloatPHP\Classes\Html;

use FloatPHP\Classes\Filesystem\TypeCheck;
use FloatPHP\Classes\Filesystem\Arrayify;

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
	 * Prevent object clone
	 *
	 * @param void
	 */
    public function __clone()
    {
        die(__METHOD__.': Clone denied');
    }

	/**
	 * Prevent object serialization
	 *
	 * @param void
	 */
    public function __wakeup()
    {
        die(__METHOD__.': Unserialize denied');
    }

	/**
	 * Returns a Singleton instance of this class
	 *
	 * @access public
	 * @param void
	 * @return object Hook
	 */
	public static function getInstance()
	{
		static $instance;
		if ( $instance === null ) {
			$instance = new self();
		}
		return $instance;
	}

	/**
	 * Add Hooks to function or method to a specific filter action
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
	 * Remove function from a specified filter hook
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
	 * Remove all of the hooks from a filter
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
	 * Check if any filter has been registered for the given hook
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
	 * Call the functions added to a filter hook
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
	 * Execute functions hooked on a specific filter hook
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
	 * Hooks a function on to a specific action
	 *
	 * @access public
	 * @param string $tag
	 * @param array $args
	 * @return mixed
	 */
	public function addAction($tag, $callable, $priority = self::PRIORITY, $path = null)
	{
		return $this->addFilter($tag,$callable,$priority,$path);
	}

	/**
	 * Check if any action has been registered for a hook.
	 *
	 * @access public
	 * @param string $tag
	 * @param array $args
	 * @return mixed
	 */
	public function hasAction($tag, $callable = false)
	{
	return $this->hasFilter($tag,$callable);
	}

	/**
	 * Removes a function from a specified action hook
	 *
	 * @access public
	 * @param string $tag
	 * @param array $args
	 * @return mixed
	 */
	public function removeAction($tag, $callable, $priority = self::PRIORITY)
	{
		return $this->removeFilter($tag,$callable,$priority);
	}

	/**
	 * Remove all of the hooks from an action
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
	 * Execute functions hooked on a specific action hook
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
	 * Execute functions hooked on a specific action hook
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
	 * Retrieve the number of times an action has fired
	 *
	 * @access public
	 * @param string $tag
	 * @return mixed
	 */
	public function didAction($tag)
	{
		if ( !TypeCheck::isArray($this->actions) || !isset($this->actions[$tag]) ) {
			return 0;
		}
		return $this->actions[$tag];
	}

	/**
	 * Retrieve the name of the current filter or action
	 *
	 * @access public
	 * @param void
	 * @return mixed
	 */
	public function currentFilter()
	{
		return end($this->currentFilter);
	}

	/**
	 * Call all hooks
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
	 * Breaking changes of callHooks method
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
	 * Build Unique ID for storage and retrieval
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
			$callable = [$callable,''];
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
