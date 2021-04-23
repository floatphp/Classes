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
	const PRIORITY = 50;

	/**
	 * Prevent object construction
	 *
	 * @param void
	 */
	public function __construct()
	{
		die(__METHOD__.': Construct denied');
	}

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
	 * @param void
	 * @return object Hook
	 */
	public static function getInstance()
	{
		static $instance;
		if ( null === $instance ) {
			$instance = new self();
		}
		return $instance;
	}

	/**
	 * Add Hooks to function or method to a specific filter action
	 *
	 * @access public
	 * @param string $tag
	 * @param string|array $callableTodo
	 * @param int $priority
	 * @param string $path
	 * @return true
	 */
	public function addFilter($tag, $callableTodo, $priority = self::PRIORITY, $path = null)
	{
		$id = $this->filterUniqueId($callableTodo);
		$this->filters[$tag][$priority][$id] = [
			'callable' => $callableTodo,
			'path'     => is_string($path) ? $path : null
		];
		unset($this->mergedFilters[$tag]);
		return true;
	}

	/**
	 * Remove function from a specified filter hook
	 *
	 * @access public
	 * @param string $tag
	 * @param string|array $callableToRemove
	 * @param int $priority
	 * @return bool
	 */
	public function removeFilter($tag, $callableToRemove, $priority = self::PRIORITY)
	{
		$callableToRemove = $this->filterUniqueId($callableToRemove);
		if ( !isset($this->filters[$tag][$priority][$callableToRemove]) ) {
			return false;
		}
		unset($this->filters[$tag][$priority][$callableToRemove]);
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
	 * @param string $callableToCheck false
	 * @return mixed
	 */
	public function hasFilter($tag, $callableToCheck = false)
	{
		$has = isset($this->filters[$tag]);
		if ( $callableToCheck === false || !$has ) {
			return $has;
		}
		if ( !($id = $this->filterUniqueId($callableToCheck)) ) {
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
		array_shift($args);
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
	public function addAction($tag, $callableTodo, $priority = self::PRIORITY, $path = null)
	{
		return $this->addFilter($tag,$callableTodo,$priority,$path);
	}

	/**
	 * Check if any action has been registered for a hook.
	 *
	 * @access public
	 * @param string $tag
	 * @param array $args
	 * @return mixed
	 */
	public function hasAction($tag, $callableToCheck = false)
	{
	return $this->hasFilter($tag,$callableToCheck);
	}

	/**
	 * Removes a function from a specified action hook
	 *
	 * @access public
	 * @param string $tag
	 * @param array $args
	 * @return mixed
	 */
	public function removeAction($tag, $callableToRemove, $priority = self::PRIORITY)
	{
		return $this->removeFilter($tag,$callableToRemove,$priority);
	}

	/**
	 * Remove all of the hooks from an action
	 *
	 * @access public
	 * @param string $tag
	 * @param string $callableToCheck false
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
	 * @param string $arg
	 * @return mixed
	 */
	public function doAction($tag, $arg = '')
	{
		if ( !is_array($this->actions) ) {
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
		if ( is_array($arg) && isset($arg[0]) && is_object($arg[0]) && 1 == count($arg) ) {
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
		if ( !is_array($this->actions) ) {
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
		if ( !is_array($this->actions) || !isset($this->actions[$tag]) ) {
			return 0;
		}
		return $this->actions[$tag];
	}

	/**
	 * Retrieve the name of the current filter or action
	 *
	 * @access public
	 * @param string $tag
	 * @param string $callableToCheck false
	 * @return mixed
	 */
	public function currentFilter()
	{
		return end($this->currentFilter);
	}

	/**
	 * Build Unique ID for storage and retrieval
	 *
	 * @access public
	 * @param string $callable
	 * @return mixed
	 */
	private function filterUniqueId($callable)
	{
		if ( is_string($callable) ) {
			return $callable;
		}
		if ( is_object($callable) ) {
			// Closures are currently implemented as objects
			$callable = [$callable,''];
		} else {
			$callable = (array)$callable;
		}
		if ( is_object($callable[0]) ) {
			// Object Class Calling
			return spl_object_hash($callable[0]) . $callable[1];
		}
		if ( is_string($callable[0]) ) {
			// Static Calling
			return "{$callable[0]}{$callable[1]}";
		}
		return false;
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
}
