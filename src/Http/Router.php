<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Http Component
 * @version    : 1.2.x
 * @copyright  : (c) 2018 - 2024 Jihad Sinnaour <mail@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Classes\Http;

use FloatPHP\Classes\Filesystem\{
    TypeCheck, Stringify, Arrayify
};
use FloatPHP\Interfaces\Classes\RouterInterface;
use FloatPHP\Exceptions\Classes\RouterException;

/**
 * Built-in HTTP router class,
 * @see https://dannyvankooten.github.io/AltoRouter/
 */
class Router implements RouterInterface
{
    /**
     * @access public
     *
     * @var string BASE, Routes base path
     * @var string REGEX, Routes compile pattern
     * @var array TYPES, Routes matching types (regex)
     */
    public const BASE  = '';
    public const REGEX = '`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`';
    public const TYPES = [
        'i'  => '[0-9]++',
        'a'  => '[0-9A-Za-z]++',
        'h'  => '[0-9A-Fa-f]++',
        '*'  => '.+?',
        '**' => '.++',
        ''   => '[^/\.]++'
    ];

    /**
     * @access protected
     *
     * @var array $routes
     * @var string $base, Route base path
     * @var array $types, Route types
     * @var array $names, Named routes
     */
    protected $routes = [];
    protected $base;
    protected $types = [];
    protected $names = [];

    /**
     * @inheritdoc
     */
    public function __construct(array $routes = [], string $base = self::BASE, array $types = self::TYPES)
    {
        $this->addRoutes($routes);
        $this->setBase($base);
        $this->addTypes($types);
    }

    /**
     * @inheritdoc
     */
    public function getRoutes() : array
    {
        return $this->routes;
    }

    /**
     * @inheritdoc
     */
    public function addRoutes(array $routes)
    {
        if ( !TypeCheck::isArray($routes) && !($routes instanceof \Traversable) ) {
            throw new RouterException(
                RouterException::notTraversable()
            );
        }

        foreach ($routes as $route) {
            call_user_func_array([$this, 'map'], $route);
        }
    }

    /**
     * @inheritdoc
     */
    public function setBase(string $base)
    {
        $this->base = $base;
    }

    /**
     * @inheritdoc
     */
    public function addTypes(array $types)
    {
        $this->types = Arrayify::merge($this->types, $types);
    }

    /**
     * @inheritdoc
     */
    public function map(string $method, string $route, $controller, ?string $name = null, $permission = null)
    {
        $this->routes[] = [$method, $route, $controller, $name, $permission];

        if ( $name ) {
            if ( isset($this->names[$name]) ) {
                throw new RouterException(
                    RouterException::redeclared($name)
                );
            }
            $this->names[$name] = $route;
        }

        return;
    }

    /**
     * @inheritdoc
     */
    public function generate(string $name, array $params = []) : string
    {
        // Check if named route exists
        if ( !isset($this->names[$name]) ) {
            throw new RouterException(
                RouterException::notExisting($name)
            );
        }

        // Replace named parameters
        $route = $this->names[$name];

        // Prepend base path to route url again
        $url = "{$this->base}{$route}";

        if ( Stringify::matchAll(static::REGEX , $route, $matches, 2) ) {

            foreach ($matches as $index => $match) {

                list($block, $pre, $type, $param, $optional) = $match;

                if ( $pre ) {
                    $block = substr($block, 1);
                }

                if ( isset($params[$param]) ) {
                    // Part is found, replace for param value
                    $url = Stringify::replace($block, $params[$param], $url);

                } elseif ( $optional && $index !== 0 ) {
                    // Only strip preceding slash if it's not at the base
                    $url = Stringify::remove("{$pre}{$block}", $url);

                } else {
                    // Strip matched block
                    $url = Stringify::remove($block, $url);
                }
            }
        }

        return $url;
    }

    /**
     * @inheritdoc
     */
    public function match(?string $url = null, ?string $method = null)
    {
        $params = [];

        // Set request URL
        if ( TypeCheck::isNull($url) ) {
            $url = Server::get('request-uri') ?: '/';
        }

        // Set request method
        if ( TypeCheck::isNull($method) ) {
            $method = Server::get('request-method') ?: 'GET';
        }

        // Strip base path from request URL
        $url = substr($url, strlen($this->base));

        // Strip query string (?a=b) from request URL
        if ( ($strpos = strpos($url, '?')) !== false ) {
            $url = substr($url, 0, $strpos);
        }

        // Get last request URL char
        $last = $url ? $url[strlen($url) -1 ] : '';

        foreach ($this->routes as $handler) {

            list($routeMethod, $route, $controller, $name, $permission) = $handler;
            $routeMethod = (stripos($routeMethod, $method) !== false);

            // Method did not match, continue to next route.
            if ( !$routeMethod ) {
                continue;
            }

            if ( $route === '*' ) {
                // * Wildcard (matches all)
                $match = true;

            } elseif ( isset($route[0]) && $route[0] === '@' ) {
                // @ regex delimiter
                $pattern = '`' . substr($route, 1) . '`u';
                $match = Stringify::match($pattern, $url, $params);

            } elseif ( ($position = strpos($route, '[')) === false ) {
                // No params in URL, do string comparison
                $match = strcmp($url, $route) === 0;

            } else {
                // Compare longest non-param string with URL
                if ( (strncmp($url, $route, $position) !== 0 ) 
                  && ($last === '/' || $route[$position -1 ] !== '/') ) {
                    continue;
                }
                $regex = $this->compile($route);
                $match = Stringify::match($regex, $url, $params);
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
                    'target'     => $controller,
                    'params'     => $params,
                    'name'       => $name,
                    'permission' => $permission
                ];
            }
        }

        return false;
    }

    /**
     * Compile route regex (Expensive).
     *
     * @access protected
     * @param string $route
     * @return string
     */
    protected function compile(string $route) : string
    {
        if ( Stringify::matchAll(static::REGEX , $route, $matches, 2) ) {

            foreach ($matches as $match) {

                list($block, $pre, $type, $param, $optional) = $match;

                if ( isset($this->types[$type]) ) {
                    $type = $this->types[$type];
                }

                if ( $pre === '.' ) {
                    $pre = '\.';
                }

                $optional = $optional !== '' ? '?' : null;

                // Legacy version of PCRE require the 'P' in (?P<named>)
                $pattern = '(?:'
                    . ($pre !== '' ? $pre : null)
                    . '('
                    . ($param !== '' ? "?P<$param>" : null)
                    . $type
                    . ')'
                    . $optional
                    . ')'
                    . $optional;

                $route = Stringify::replace($block, $pattern, $route);
            }
        }

        return "`^{$route}$`u";
    }
}
