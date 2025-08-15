<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Html Component
 * @version    : 1.5.x
 * @copyright  : (c) 2018 - 2025 Jihad Sinnaour <me@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file is a part of FloatPHP Framework.
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
	 * @access public
	 * @var int const PRIORITY
	 * @var int const COUNT
	 */
	public const PRIORITY = 10;
	public const COUNT    = 1;

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
	 */
	public function addFilter(string $name, $callback, int $priority = self::PRIORITY, int $args = self::COUNT) : bool
	{
		// Validate parameters
		if ( empty($name) || !is_callable($callback) ) {
			return false;
		}

		if ( $priority < 0 ) {
			$priority = self::PRIORITY;
		}

		if ( $args < 1 ) {
			$args = self::COUNT;
		}

		$id = $this->filterUniqueId($callback);

		if ( $id === false ) {
			return false;
		}

		$this->filters[$name][$priority][$id] = [
			'callable' => $callback,
			'count'    => $args
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
		// Validate parameters
		if ( empty($name) ) {
			return false;
		}

		$id = $this->filterUniqueId($callback);

		if ( $id === false || !isset($this->filters[$name][$priority][$id]) ) {
			return false;
		}

		unset($this->filters[$name][$priority][$id]);
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
		if ( empty($name) || !isset($this->filters[$name]) ) {
			return false;
		}

		if ( $callback === false ) {
			return !empty($this->filters[$name]);
		}

		$id = $this->filterUniqueId($callback);

		if ( $id === false ) {
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
	 */
	public function applyFilter(string $name, $value, ...$args) : mixed
	{
		if ( empty($name) ) {
			return $value;
		}

		// Prepare arguments array with value as first argument
		$allArgs = array_merge([$value], $args);

		// Do 'all' actions first
		if ( isset($this->filters['all']) ) {
			$this->currentFilter[] = $name;
			$all = self::getArgs($name, ...$allArgs);
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

		do {
			foreach ((array)current($this->filters[$name]) as $current) {
				if ( $current['callable'] !== null ) {
					$allArgs[0] = $value;
					// Limit arguments based on callback count
					$limitedArgs = array_slice($allArgs, 0, $current['count']);
					$value = self::callUserFunctionArray($current['callable'], $limitedArgs);
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
		if ( empty($name) || empty($args) ) {
			return $args[0] ?? null;
		}

		// Do 'all' actions first
		if ( isset($this->filters['all']) ) {
			$this->currentFilter[] = $name;
			$all = self::getArgs($name, ...$args);
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
					// Limit arguments based on callback count
					$limitedArgs = array_slice($args, 0, $current['count']);
					$args[0] = self::callUserFunctionArray($current['callable'], $limitedArgs);
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

		// Prepare arguments properly
		$callArgs = $args;

		// Sort
		if ( !isset($this->mergedFilters[$name]) ) {
			ksort($this->filters[$name]);
			$this->mergedFilters[$name] = true;
		}
		reset($this->filters[$name]);

		do {
			foreach ((array)current($this->filters[$name]) as $current) {
				if ( $current['callable'] !== null ) {
					// Limit arguments based on callback count
					$limitedArgs = array_slice($callArgs, 0, $current['count']);
					self::callUserFunctionArray($current['callable'], $limitedArgs);
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
		if ( empty($name) ) {
			return;
		}

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
			$all = self::getArgs($name, ...$args);
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
		if ( !isset($this->mergedFilters[$name]) ) {
			ksort($this->filters[$name]);
			$this->mergedFilters[$name] = true;
		}
		reset($this->filters[$name]);

		do {
			foreach ((array)current($this->filters[$name]) as $current) {
				if ( $current['callable'] !== null ) {
					// Limit arguments based on callback count
					$limitedArgs = array_slice($args, 0, $current['count']);
					self::callUserFunctionArray($current['callable'], $limitedArgs);
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
		$did = TypeCheck::isArray($this->actions)
			&& isset($this->actions[$name]);

		return $did ? $this->actions[$name] : 0;
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
	 * @param mixed ...$args
	 * @return array
	 */
	public static function getArgs(...$args) : array
	{
		return $args;
	}

	/**
	 * Get function arg.
	 *
	 * @access public
	 * @param int $position
	 * @return mixed
	 */
	public static function getArg(int $position) : mixed
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
	 * Get all function arguments.
	 *
	 * @access public
	 * @return array
	 */
	public static function getAllArgs() : array
	{
		return func_get_args();
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

		// Closures are currently implemented as objects
		if ( TypeCheck::isObject($callback) ) {
			$callback = [$callback, ''];

		} else {
			$callback = (array)$callback;
		}

		// Object class calling
		if ( TypeCheck::isObject($callback[0]) ) {
			return spl_object_hash($callback[0]) . $callback[1];
		}

		// Static calling
		if ( TypeCheck::isString($callback[0]) ) {
			return "{$callback[0]}{$callback[1]}";
		}

		return false;
	}

	/**
	 * Get all registered filters for debugging.
	 *
	 * @access public
	 * @param ?string $name
	 * @return array
	 */
	public function getFilters(?string $name = null) : array
	{
		if ( $name !== null ) {
			return $this->filters[$name] ?? [];
		}
		return $this->filters;
	}

	/**
	 * Get current action being executed.
	 *
	 * @access public
	 * @return ?string
	 */
	public function getCurrentAction() : ?string
	{
		return end($this->currentFilter) ?: null;
	}

	/**
	 * Clear all hooks.
	 *
	 * @access public
	 * @return void
	 */
	public function clearAllHooks() : void
	{
		$this->filters = [];
		$this->mergedFilters = [];
		$this->actions = [];
		$this->currentFilter = [];
	}
}
