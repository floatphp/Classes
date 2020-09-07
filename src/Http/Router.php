<?php
/**
 * @author    : JIHAD SINNAOUR
 * @package   : FloatPHP
 * @subpackage: Classes Http Component
 * @version   : 1.1.0
 * @category  : PHP framework
 * @copyright : (c) JIHAD SINNAOUR <mail@jihadsinnaour.com>
 * @link      : https://www.floatphp.com
 * @license   : MIT License
 *
 * This file if a part of FloatPHP Framework
 */

namespace floatPHP\Classes\Http;

class Router
{
	/**
	 * @access protected
	 * @var array $routes All routes
	 * @var array $namedRoutes All named routes
	 * @var string $basePath
	 * @var array $matchTypes Default match types
	 */
	protected $routes = [];
	protected $namedRoutes = [];
	protected $basePath = '';
	protected $matchTypes = [
		'i'  => '[0-9]++',
		'a'  => '[0-9A-Za-z]++',
		'h'  => '[0-9A-Fa-f]++',
		'*'  => '.+?',
		'**' => '.++',
		''   => '[^/\.]++'
	];

	/**
	 * Create router in one call from config
	 *
	 * @param array $routes
	 * @param string $basePath
	 * @param array $matchTypes
	 * @return void
	 */
	public function __construct($routes = [], $basePath = '', $matchTypes = [])
	{
		$this->addRoutes($routes);
		$this->setBasePath($basePath);
		$this->addMatchTypes($matchTypes);
	}
	
	/**
	 * Retrieves all routes
	 *
	 * @access public
	 * @param void
	 * @return array
	 */
	public function getRoutes()
	{
		return $this->routes;
	}

	/**
	 * Add multiple routes at once from array
	 *
	 * @access public
	 * @param array $routes
	 * @throws Exception
	 * @return void
	 */
	public function addRoutes($routes)
	{
		if (!is_array($routes) && !$routes instanceof Traversable) {
			throw new \Exception('Routes should be an array or an instance of Traversable');
		}
		foreach($routes as $route) {
			call_user_func_array(array($this, 'map'), $route);
		}
	}

	/**
	 * Set the base path
	 *
	 * @access public
	 * @param array $routes
	 * @return void
	 */
	public function setBasePath($basePath)
	{
		$this->basePath = $basePath;
	}

	/**
	 * Add named match types
	 *
	 * @access public
	 * @param array $matchTypes
	 * @return void
	 */
	public function addMatchTypes($matchTypes)
	{
		$this->matchTypes = array_merge($this->matchTypes, $matchTypes);
	}

	/**
	 * Map routes to targets
	 *
	 * @access public
	 * @param string $method (GET|POST|PATCH|PUT|DELETE)
	 * @param string $route Route regex
	 * @param mixed $target
	 * @param string $name Optional
	 * @return void
	 */
	public function map($method, $route, $target, $name = null)
	{
		$this->routes[] = array($method, $route, $target, $name);
		if ($name) {
			if (isset($this->namedRoutes[$name])) {
				throw new \Exception("Can not redeclare route '{$name}'");
			} else {
				$this->namedRoutes[$name] = $route;
			}
		}
		return;
	}

	/**
	 * Reversed routing
	 *
	 * @access public
	 * @param string $routeName
	 * @param array @params
	 * @throws Exception
	 * @return string
	 */
	public function generate($routeName, array $params = [])
	{
		// Check if named route exists
		if ( !isset($this->namedRoutes[$routeName]) ) {
			throw new \Exception("Route '{$routeName}' does not exist.");
		}

		// Replace named parameters
		$route = $this->namedRoutes[$routeName];
		
		// prepend base path to route url again
		$url = $this->basePath . $route;

		if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER)) {
			foreach($matches as $match) {
				list($block, $pre, $type, $param, $optional) = $match;
				if ($pre) {
					$block = substr($block, 1);
				}
				if (isset($params[$param]) ) {
					$url = str_replace($block, $params[$param], $url);
				} elseif ($optional) {
					$url = str_replace($pre . $block, '', $url);
				}
			}
		}
		return $url;
	}

	/**
	 * Match a given Request Url against stored routes
	 *
	 * @access public
	 * @param string $requestUrl
	 * @param string $requestMethod
	 * @return array|boolean
	 */
	public function match($requestUrl = null, $requestMethod = null)
	{
		$params = [];
		$match = false;

		// set Request Url if it isn't passed as parameter
		if ( $requestUrl === null ) {
			$requestUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
		}

		// strip base path from request url
		$requestUrl = substr($requestUrl, strlen($this->basePath));

		// Strip query string (?a=b) from Request Url
		if ( ($strpos = strpos($requestUrl, '?')) !== false ) {
			$requestUrl = substr($requestUrl, 0, $strpos);
		}

		// set Request Method if it isn't passed as a parameter
		if ( $requestMethod === null ) {
			$requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
		}

		foreach($this->routes as $handler) {
			list($method, $_route, $target, $name) = $handler;
			$methods = explode('|', $method);
			$methodMatch = false;

			// Check if request method matches. If not, abandon early. (CHEAP)
			foreach($methods as $method) {
				if (strcasecmp($requestMethod, $method) === 0) {
					$methodMatch = true;
					break;
				}
			}

			// Method did not match, continue to next route.
			if (!$methodMatch) continue;

			// Check for a wildcard (matches all)
			if ($_route === '*') {
				$match = true;

			} elseif (isset($_route[0]) && $_route[0] === '@') {
				$pattern = '`' . substr($_route, 1) . '`u';
				$match = preg_match($pattern, $requestUrl, $params);

			} else {
				$route = null;
				$regex = false;
				$j = 0;
				$n = isset($_route[0]) ? $_route[0] : null;
				$i = 0;
				// Find the longest non-regex substring and match it against the URI
				while (true) {
					if ( !isset($_route[$i]) ) {
						break;
						
					} elseif (false === $regex) {
						$c = $n;
						$regex = $c === '[' || $c === '(' || $c === '.';
						if (false === $regex && false !== isset($_route[$i+1])) {
							$n = $_route[$i + 1];
							$regex = $n === '?' || $n === '+' || $n === '*' || $n === '{';
						}
						if (false === $regex && $c !== '/' && (!isset($requestUrl[$j]) || $c !== $requestUrl[$j])) {
							continue 2;
						}
						$j++;
					}
					$route .= $_route[$i++];
				}
				$regex = $this->compileRoute($route);
				$match = preg_match($regex, $requestUrl, $params);
			}

			if ( ($match == true || $match > 0) ) {

				if ( $params ) {
					foreach($params as $key => $value) {
						if ( is_numeric($key)) unset($params[$key] );
					}
				}
				return array(
					'target' => $target,
					'params' => $params,
					'name'   => $name
				);
			}
		}
		return false;
	}

	/**
	 * Compile the regex for a given route (EXPENSIVE)
	 *
	 * @access private
	 * @param string $route
	 * @return string
	 */
	private function compileRoute($route)
	{
		if ( preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER) ) {
			$matchTypes = $this->matchTypes;
			foreach($matches as $match) {
				list($block, $pre, $type, $param, $optional) = $match;
				if ( isset($matchTypes[$type]) ) {
					$type = $matchTypes[$type];
				}
				if ( $pre === '.' ) {
					$pre = '\.';
				}
				// Older versions of PCRE require the 'P' in (?P<named>)
				$pattern = '(?:'
						. ($pre !== '' ? $pre : null)
						. '('
						. ($param !== '' ? "?P<$param>" : null)
						. $type
						. '))'
						. ($optional !== '' ? '?' : null);

				$route = str_replace($block, $pattern, $route);
			}
		}
		return "`^$route$`u";
	}
}
