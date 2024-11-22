<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Html Component
 * @version    : 1.3.x
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Html;

use FloatPHP\Classes\Filesystem\{Arrayify, TypeCheck};

/**
 * Built-in hook class.
 * @see https://developer.wordpress.org/apis/hooks/
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
	 * @var int const COUNT
	 */
	private const PRIORITY = 10;
	private const COUNT    = 1;

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
	 * @param string $name
	 * @param mixed $callback
	 * @param int $priority
	 * @param int $args
	 * @return bool
	 * @todo $args
	 */
	public function addFilter(string $name, $callback, int $priority = self::PRIORITY, int $args = self::COUNT) : bool
	{
		$id = $this->filterUniqueId($callback);
		$this->filters[$name][$priority][$id] = [
			'callable' => $callback,
			'args'     => $args
		];

		unset($this->mergedFilters[$name]);
		return true;
	}

	/**
	 * Remove filter hook.
	 *
	 * @access public
	 * @param string $name
	 * @param mixed $callback
	 * @param int $priority
	 * @return bool
	 */
	public function removeFilter(string $name, $callback, int $priority = self::PRIORITY) : bool
	{
		$callback = $this->filterUniqueId($callback);
		if ( !isset($this->filters[$name][$priority][$callback]) ) {
			return false;
		}

		unset($this->filters[$name][$priority][$callback]);
		if ( empty($this->filters[$name][$priority]) ) {
			unset($this->filters[$name][$priority]);
		}

		unset($this->mergedFilters[$name]);
		return true;
	}

	/**
	 * Remove all filters.
	 *
	 * @access public
	 * @param string $name
	 * @param mixed $priority
	 * @return bool
	 */
	public function removeFilters(string $name, $priority = false) : bool
	{
		if ( isset($this->mergedFilters[$name]) ) {
			unset($this->mergedFilters[$name]);
		}

		if ( !isset($this->filters[$name]) ) {
			return true;
		}

		if ( $priority !== false && isset($this->filters[$name][$priority]) ) {
			unset($this->filters[$name][$priority]);

		} else {
			unset($this->filters[$name]);
		}

		return true;
	}

	/**
	 * Check filter hook.
	 *
	 * @access public
	 * @param string $name
	 * @param mixed $callback
	 * @return mixed
	 */
	public function hasFilter(string $name, $callback = false) : mixed
	{
		if ( !isset($this->filters[$name]) ) {
			return false;
		}

		if ( !($id = $this->filterUniqueId($callback)) ) {
			return false;
		}

		$filters = $this->filters[$name] ?: [];
		foreach (Arrayify::keys($filters) as $priority) {
			if ( isset($this->filters[$name][$priority][$id]) ) {
				return $priority;
			}
		}

		return false;
	}

	/**
	 * Apply filter hook.
	 *
	 * @access public
	 * @param string $name
	 * @param mixed $value
	 * @param mixed $args
	 * @return mixed
	 * @todo ...$args
	 */
	public function applyFilter(string $name, $value, ...$args) : mixed
	{
		$args = [];

		// Do 'all' actions first
		if ( isset($this->filters['all']) ) {
			$this->currentFilter[] = $name;
			$all = self::getArgs();
			$this->callHooks($all);
		}

		if ( !isset($this->filters[$name]) ) {
			if ( isset($this->filters['all']) ) {
				Arrayify::pop($this->currentFilter);
			}
			return $value;
		}

		if ( !isset($this->filters['all']) ) {
			$this->currentFilter[] = $name;
		}

		// Sort
		if ( !isset($this->mergedFilters[$name]) ) {
			ksort($this->filters[$name]);
			$this->mergedFilters[$name] = true;
		}
		reset($this->filters[$name]);
		if ( empty($args) ) {
			$args = self::getArgs();
		}
		Arrayify::shift($args);

		do {
			foreach ((array)current($this->filters[$name]) as $current) {
				if ( $current['callable'] !== null ) {
					$args[0] = $value;
					$value = self::callUserFunctionArray($current['callable'], $args);
				}
			}
		} while (next($this->filters[$name]) !== false);

		Arrayify::pop($this->currentFilter);
		return $value;
	}

	/**
	 * Apply array filter hook.
	 *
	 * @access public
	 * @param string $name
	 * @param array $args
	 * @return mixed
	 */
	public function applyFilterArray(string $name, array $args) : mixed
	{
		// Do 'all' actions first
		if ( isset($this->filters['all']) ) {
			$this->currentFilter[] = $name;
			$all = self::getArgs();
			$this->callHooks($all);
		}

		if ( !isset($this->filters[$name]) ) {
			if ( isset($this->filters['all']) ) {
				Arrayify::pop($this->currentFilter);
			}
			return $args[0];
		}

		if ( !isset($this->filters['all']) ) {
			$this->currentFilter[] = $name;
		}

		// Sort
		if ( !isset($this->mergedFilters[$name]) ) {
			ksort($this->filters[$name]);
			$this->mergedFilters[$name] = true;
		}
		reset($this->filters[$name]);

		do {
			foreach ((array)current($this->filters[$name]) as $current) {
				if ( $current['callable'] !== null ) {
					$args[0] = self::callUserFunctionArray($current['callable'], $args);
				}
			}
		} while (next($this->filters[$name]) !== false);

		Arrayify::pop($this->currentFilter);
		return $args[0];
	}

	/**
	 * Add action hook.
	 *
	 * @access public
	 * @param string $name
	 * @param callable $callback
	 * @param int $priority
	 * @param int $args
	 * @return bool
	 */
	public function addAction(string $name, $callback, int $priority = self::PRIORITY, int $args = self::COUNT) : bool
	{
		return $this->addFilter($name, $callback, $priority, $args);
	}

	/**
	 * Check action hook.
	 *
	 * @access public
	 * @param string $name
	 * @param mixed $callback
	 * @return mixed
	 */
	public function hasAction(string $name, $callback = false) : mixed
	{
		return $this->hasFilter($name, $callback);
	}

	/**
	 * Remove action hook.
	 *
	 * @access public
	 * @param string $name
	 * @param mixed $callback
	 * @param int $priority
	 * @return bool
	 */
	public function removeAction(string $name, $callback, int $priority = self::PRIORITY) : bool
	{
		return $this->removeFilter($name, $callback, $priority);
	}

	/**
	 * Remove all actions hooks.
	 *
	 * @access public
	 * @param string $name
	 * @param mixed $priority
	 * @return mixed
	 */
	public function removeActions(string $name, $priority = false) : bool
	{
		return $this->removeFilters($name, $priority);
	}

	/**
	 * Do action hook.
	 *
	 * @access public
	 * @param string $name
	 * @param mixed $args
	 * @return void
	 * @todo ...$args
	 */
	public function doAction(string $name, ...$args) : void
	{
		if ( !TypeCheck::isArray($this->actions) ) {
			$this->actions = [];
		}

		if ( !isset($this->actions[$name]) ) {
			$this->actions[$name] = 1;

		} else {
			++$this->actions[$name];
		}

		// Do 'all' actions first
		if ( isset($this->filters['all']) ) {
			$this->currentFilter[] = $name;
			$all = self::getArgs();
			$this->callHooks($all);
		}

		if ( !isset($this->filters[$name]) ) {
			if ( isset($this->filters['all']) ) {
				Arrayify::pop($this->currentFilter);
			}
			return;
		}

		if ( !isset($this->filters['all']) ) {
			$this->currentFilter[] = $name;
		}

		$_args = [];
		if ( TypeCheck::isArray($args) && isset($args[0]) && TypeCheck::isObject($args[0]) && 1 == count($args) ) {
			$_args[] = &$args[0];

		} else {
			$_args[] = $args;
		}

		$count = self::countArgs();
		for ($a = 2; $a < $count; $a++) {
			$_args[] = self::getArg($a);
		}

		// Sort
		if ( !isset($this->mergedFilters[$name]) ) {
			ksort($this->filters[$name]);
			$this->mergedFilters[$name] = true;
		}
		reset($this->filters[$name]);

		do {
			foreach ((array)current($this->filters[$name]) as $current) {
				if ( $current['callable'] !== null ) {
					self::callUserFunctionArray($current['callable'], $args);
				}
			}
		} while (next($this->filters[$name]) !== false);

		Arrayify::pop($this->currentFilter);
	}

	/**
	 * Do array action hook.
	 *
	 * @access public
	 * @param string $name
	 * @param array $args
	 * @return void
	 */
	public function doActionArray(string $name, array $args) : void
	{
		if ( !TypeCheck::isArray($this->actions) ) {
			$this->actions = [];
		}

		if ( !isset($this->actions[$name]) ) {
			$this->actions[$name] = 1;

		} else {
			++$this->actions[$name];
		}

		// Do 'all' actions first
		if ( isset($this->filters['all']) ) {
			$this->currentFilter[] = $name;
			$all = self::getArgs();
			$this->callHooks($all);
		}

		if ( !isset($this->filters[$name]) ) {
			if ( isset($this->filters['all']) ) {
				Arrayify::pop($this->currentFilter);
			}
			return;
		}

		if ( !isset($this->filters['all']) ) {
			$this->currentFilter[] = $name;
		}

		// Sort
		if ( !isset($mergedFilters[$name]) ) {
			ksort($this->filters[$name]);
			$mergedFilters[$name] = true;
		}
		reset($this->filters[$name]);

		do {
			foreach ((array)current($this->filters[$name]) as $current) {
				if ( $current['callable'] !== null ) {
					self::callUserFunctionArray($current['callable'], $args);
				}
			}
		} while (next($this->filters[$name]) !== false);

		Arrayify::pop($this->currentFilter);
	}

	/**
	 * Check fired action hook.
	 *
	 * @access public
	 * @param string $name
	 * @return int
	 */
	public function didAction(string $name) : int
	{
		if (
			!TypeCheck::isArray($this->actions)
			|| !isset($this->actions[$name])
		) {
			return 0;
		}
		return $this->actions[$name];
	}

	/**
	 * Get current filter hook.
	 *
	 * @access public
	 * @return string
	 */
	public function currentFilter() : string
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
	public function callHooks(array &$args) : void
	{
		reset($this->filters['all']);

		do {
			foreach ((array)current($this->filters['all']) as $current) {
				if ( $current['callable'] !== null ) {
					self::callUserFunctionArray($current['callable'], $args);
				}
			}
		} while (next($this->filters['all']) !== false);
	}

	/**
	 * Break callHooks method.
	 *
	 * @access public
	 * @param array $args
	 * @return void
	 */
	public function callHooksAfter(array $args) : void
	{
		$this->callHooks($args);
	}

	/**
	 * Get function args.
	 *
	 * @access public
	 * @return array
	 */
	public static function getArgs() : array
	{
		return func_get_args();
	}

	/**
	 * Get function arg.
	 *
	 * @access public
	 * @param int $position
	 * @return array
	 */
	public static function getArg(int $position) : array
	{
		return func_get_arg($position);
	}

	/**
	 * Get function number of args.
	 *
	 * @access public
	 * @return int
	 */
	public static function countArgs() : int
	{
		return func_num_args();
	}

	/**
	 * Call user function.
	 *
	 * @access public
	 * @param callable $callback
	 * @param mixed $args
	 * @return array
	 */
	public static function callUserFunction(callable $callback, ...$args) : mixed
	{
		return call_user_func($callback, ...$args);
	}

	/**
	 * Call user function array.
	 *
	 * @access public
	 * @param callable $callback
	 * @param array $args
	 * @return array
	 */
	public static function callUserFunctionArray(callable $callback, array $args) : mixed
	{
		return call_user_func_array($callback, $args);
	}

	/**
	 * Filter unique Id.
	 *
	 * @access private
	 * @param mixed $callback
	 * @return mixed
	 */
	private function filterUniqueId($callback) : mixed
	{
		if ( TypeCheck::isString($callback) ) {
			return $callback;
		}

		if ( TypeCheck::isObject($callback) ) {
			// Closures are currently implemented as objects
			$callback = [$callback, ''];

		} else {
			$callback = (array)$callback;
		}

		if ( TypeCheck::isObject($callback[0]) ) {
			// Object Class Calling
			return spl_object_hash($callback[0]) . $callback[1];
		}

		if ( TypeCheck::isString($callback[0]) ) {
			// Static Calling
			return "{$callback[0]}{$callback[1]}";
		}

		return false;
	}
}
