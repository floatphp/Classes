<?php
/**
 * @author     : JIHAD SINNAOUR
 * @package    : FloatPHP
 * @subpackage : Classes Http Component
 * @version    : 1.0.1
 * @category   : PHP framework
 * @copyright  : (c) 2017 - 2023 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://www.floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Http;

use FloatPHP\Interfaces\Classes\RouterInterface;
use FloatPHP\Classes\Filesystem\{
    TypeCheck, Stringify, Arrayify
};
use \RuntimeException;
use \Traversable;

/**
 * Built-in HTTP router class,
 * @see Heavily inspired by https://altorouter.com
 */
class Router implements RouterInterface
{
    /**
     * @access protected
     * @var array $routes
     * @var array $namedRoutes
     * @var array $basePath
     * @var array $matchTypes
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
     * Create router in one call from config.
     *
     * @param array $routes
     * @param string $basePath
     * @param array $matchTypes
     */
    public function __construct($routes = [], $basePath = '', $matchTypes = [])
    {
        $this->addRoutes($routes);
        $this->setBasePath($basePath);
        $this->addMatchTypes($matchTypes);
    }

    /**
     * Retrieves all routes,
     * Useful if you want to process or display routes.
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
     * Add multiple routes at once from array in the following format,
     * $routes = [[$method, $route, $target, $name]].
     *
     * @access public
     * @param array $routes
     * @return void
     */
    public function addRoutes($routes)
    {
        if ( !TypeCheck::isArray($routes) && !$routes instanceof Traversable ) {
            throw new RuntimeException('Routes should be an array or an instance of Traversable');
        }
        foreach ($routes as $route) {
            call_user_func_array([$this,'map'],$route);
        }
    }

    /**
     * Set the base path.
     *
     * @access public
     * @param string $basePath
     * @return void
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * Add named match types.
     *
     * @access public
     * @param array $matchTypes
     * @return void
     */
    public function addMatchTypes($matchTypes)
    {
        $this->matchTypes = Arrayify::merge($this->matchTypes,$matchTypes);
    }

    /**
     * Map route to target (controller),
     * (GET|POST|PATCH|PUT|DELETE),
     * Custom regex must start with an '@'.
     *
     * @access public
     * @param string $method
     * @param string $route
     * @param mixed $controller
     * @param string $name
     * @param string $permissions
     * @return void
     * @throws RuntimeException
     */
    public function map($method, $route, $controller, $name = null, $permissions = null)
    {
        $this->routes[] = [$method,$route,$controller,$name,$permissions];
        if ( $name ) {
            if ( isset($this->namedRoutes[$name]) ) {
                throw new RuntimeException("Can not redeclare route '{$name}'");
            }
            $this->namedRoutes[$name] = $route;
        }
        return;
    }

    /**
     * Reversed routing,
     * Generate the URL for a named route.
     *
     * @access public
     * @param string $routeName
     * @param array @params
     * @return string
     * @throws RuntimeException
     */
    public function generate($routeName, $params = [])
    {
        // Check if named route exists
        if ( !isset($this->namedRoutes[$routeName]) ) {
            throw new RuntimeException("Route '{$routeName}' does not exist");
        }
        // Replace named parameters
        $route = $this->namedRoutes[$routeName];
        // prepend base path to route url again
        $url = $this->basePath . $route;
        if ( $matches = Stringify::matchAll('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`',$route,-1,PREG_SET_ORDER) ) {
            foreach ($matches as $index => $match) {
                list($block, $pre, $type, $param, $optional) = $match;
                if ( $pre ) {
                    $block = substr($block,1);
                }
                if ( isset($params[$param]) ) {
                    // Part is found, replace for param value
                    $url = Stringify::replace($block, $params[$param],$url);
                } elseif ( $optional && $index !== 0 ) {
                    // Only strip preceding slash if it's not at the base
                    $url = Stringify::replace("{$pre}{$block}",'',$url);
                } else {
                    // Strip match block
                    $url = Stringify::replace($block,'',$url);
                }
            }
        }
        return $url;
    }

    /**
     * Match given request URL against stored routes.
     *
     * @access public
     * @param string $requestUrl
     * @param string $requestMethod
     * @return mixed
     */
    public function match($requestUrl = null, $requestMethod = null)
    {
        $params = [];
        // set Request Url if it isn't passed as parameter
        if ( $requestUrl === null ) {
            $requestUrl = Server::isSetted('REQUEST_URI') ? Server::get('REQUEST_URI') : '/';
        }
        // strip base path from request url
        $requestUrl = substr($requestUrl, strlen($this->basePath));
        // Strip query string (?a=b) from Request Url
        if ( ($strpos = strpos($requestUrl, '?')) !== false ) {
            $requestUrl = substr($requestUrl, 0, $strpos);
        }
        $lastRequestUrlChar = $requestUrl ? $requestUrl[strlen($requestUrl)-1] : '';
        // set Request Method if it isn't passed as a parameter
        if ( $requestMethod === null ) {
            $requestMethod = Server::isSetted('REQUEST_METHOD') ? Server::get('REQUEST_METHOD') : 'GET';
        }
        foreach ($this->routes as $handler) {
            list($methods,$route,$target,$name,$permissions) = $handler;
            $method = (stripos($methods,$requestMethod) !== false);
            // Method did not match, continue to next route.
            if ( !$method ) {
                continue;
            }
            if ( $route === '*' ) {
                // * wildcard (matches all)
                $match = true;
            } elseif ( isset($route[0]) && $route[0] === '@' ) {
                // @ regex delimiter
                $pattern = '`' . substr($route, 1) . '`u';
                $match = preg_match($pattern, $requestUrl, $params) === 1;
            } elseif ( ($position = strpos($route, '[')) === false ) {
                // No params in url, do string comparison
                $match = strcmp($requestUrl, $route) === 0;
            } else {
                // Compare longest non-param string with url before moving on to regex
                if ( strncmp($requestUrl,$route,$position) !== 0 && ($lastRequestUrlChar === '/' || $route[$position-1] !== '/') ) {
                    continue;
                }
                $regex = $this->compileRoute($route);
                $match = preg_match($regex,$requestUrl,$params) === 1;
            }
            if ( $match ) {
                if ( $params ) {
                    foreach ($params as $key => $value) {
                        if ( TypeCheck::isInt($key) ) {
                            unset($params[$key]);
                        }
                    }
                }
                return [
                    'target'      => $target,
                    'params'      => $params,
                    'name'        => $name,
                    'permissions' => $permissions
                ];
            }
        }
        return false;
    }

    /**
     * Compile regex for given route (Expensive).
     *
     * @access protected
     * @param $route
     * @return string
     */
    protected function compileRoute($route)
    {
        if ( ($matches = Stringify::matchAll('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`',$route,-1,PREG_SET_ORDER)) ) {
            $matchTypes = $this->matchTypes;
            foreach ($matches as $match) {
                list($block, $pre, $type, $param, $optional) = $match;
                if ( isset($matchTypes[$type]) ) {
                    $type = $matchTypes[$type];
                }
                if ( $pre === '.' ) {
                    $pre = '\.';
                }
                $optional = $optional !== '' ? '?' : null;
                // Older versions of PCRE require the 'P' in (?P<named>)
                $pattern = '(?:'
                        . ($pre !== '' ? $pre : null)
                        . '('
                        . ($param !== '' ? "?P<$param>" : null)
                        . $type
                        . ')'
                        . $optional
                        . ')'
                        . $optional;

                $route = Stringify::replace($block,$pattern,$route);
            }
        }
        return "`^$route$`u";
    }
}
