<?php
/**
 * @author     : Jakiboy
 * @package    : FloatPHP
 * @subpackage : Classes Http Component Tests
 * @version    : 1.5.x
 * @copyright  : (c) 2018 - 2025 Jihad Sinnaour <me@jihadsinnaour.com>
 * @link       : https://floatphp.com
 * @license    : MIT
 *
 * This file if a part of FloatPHP Framework.
 */

declare(strict_types=1);

namespace FloatPHP\Tests\Classes\Http;

use PHPUnit\Framework\TestCase;
use FloatPHP\Classes\Http\Router;

/**
 * Router class tests.
 */
final class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $this->router = new Router();
    }

    /**
     * Test router initialization.
     */
    public function testRouterInitialization(): void
    {
        $router = new Router();
        $this->assertInstanceOf(Router::class, $router);
    }

    /**
     * Test router initialization with routes.
     */
    public function testRouterInitializationWithRoutes(): void
    {
        $routes = [
            ['GET', '/', 'HomeController@index', 'home'],
            ['POST', '/users', 'UserController@create', 'users.create']
        ];
        
        $router = new Router($routes);
        $this->assertInstanceOf(Router::class, $router);
    }

    /**
     * Test router initialization with base path.
     */
    public function testRouterInitializationWithBasePath(): void
    {
        $router = new Router([], '/api/v1');
        $this->assertInstanceOf(Router::class, $router);
    }

    /**
     * Test router initialization with custom types.
     */
    public function testRouterInitializationWithCustomTypes(): void
    {
        $customTypes = [
            'slug' => '[a-z0-9\-]+',
            'uuid' => '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}'
        ];
        
        $router = new Router([], '', $customTypes);
        $this->assertInstanceOf(Router::class, $router);
    }

    /**
     * Test get routes.
     */
    public function testGetRoutes(): void
    {
        $routes = $this->router->getRoutes();
        $this->assertIsArray($routes);
    }

    /**
     * Test add routes method exists.
     */
    public function testAddRoutesMethodExists(): void
    {
        $this->assertTrue(method_exists(Router::class, 'addRoutes'));
    }

    /**
     * Test set base path.
     */
    public function testSetBase(): void
    {
        $this->router->setBase('/api');
        $this->assertInstanceOf(Router::class, $this->router);
    }

    /**
     * Test map method exists.
     */
    public function testMapMethodExists(): void
    {
        $this->assertTrue(method_exists(Router::class, 'map'));
    }

    /**
     * Test match method exists.
     */
    public function testMatchMethodExists(): void
    {
        $this->assertTrue(method_exists(Router::class, 'match'));
    }

    /**
     * Test generate method exists.
     */
    public function testGenerateMethodExists(): void
    {
        $this->assertTrue(method_exists(Router::class, 'generate'));
    }

    /**
     * Test router constants.
     */
    public function testRouterConstants(): void
    {
        $this->assertEquals('', Router::BASE);
        $this->assertIsString(Router::REGEX);
        $this->assertIsArray(Router::TYPES);
    }

    /**
     * Test default route types.
     */
    public function testDefaultRouteTypes(): void
    {
        $types = Router::TYPES;
        
        $this->assertArrayHasKey('i', $types);
        $this->assertArrayHasKey('a', $types);
        $this->assertArrayHasKey('h', $types);
        $this->assertArrayHasKey('*', $types);
        $this->assertArrayHasKey('**', $types);
        $this->assertArrayHasKey('', $types);
        
        $this->assertEquals('[0-9]++', $types['i']);
        $this->assertEquals('[0-9A-Za-z]++', $types['a']);
        $this->assertEquals('[0-9A-Fa-f]++', $types['h']);
        $this->assertEquals('.+?', $types['*']);
        $this->assertEquals('.++', $types['**']);
        $this->assertEquals('[^/\.]++',$types['']);
    }

    /**
     * Test regex pattern.
     */
    public function testRegexPattern(): void
    {
        $regex = Router::REGEX;
        $this->assertIsString($regex);
        $this->assertStringStartsWith('`', $regex);
        $this->assertStringEndsWith('`', $regex);
    }

    /**
     * Test router with simple routes.
     */
    public function testRouterWithSimpleRoutes(): void
    {
        $routes = [
            ['GET', '/', 'home'],
            ['POST', '/login', 'login'],
            ['GET', '/users', 'users.index']
        ];
        
        $router = new Router($routes);
        $routesList = $router->getRoutes();
        
        $this->assertIsArray($routesList);
    }

    /**
     * Test router with parametric routes.
     */
    public function testRouterWithParametricRoutes(): void
    {
        $routes = [
            ['GET', '/users/[i:id]', 'users.show'],
            ['GET', '/posts/[a:slug]', 'posts.show'],
            ['GET', '/categories/[*:path]', 'categories.show']
        ];
        
        $router = new Router($routes);
        $this->assertInstanceOf(Router::class, $router);
    }

    /**
     * Test router with named routes.
     */
    public function testRouterWithNamedRoutes(): void
    {
        $routes = [
            ['GET', '/', 'HomeController@index', 'home'],
            ['GET', '/about', 'PageController@about', 'about'],
            ['POST', '/contact', 'ContactController@send', 'contact.send']
        ];
        
        $router = new Router($routes);
        $this->assertInstanceOf(Router::class, $router);
    }

    /**
     * Test router with complex routes.
     */
    public function testRouterWithComplexRoutes(): void
    {
        $routes = [
            ['GET|POST', '/api/users/[i:id]/posts/[i:post_id]?', 'ApiController@userPost'],
            ['PUT|PATCH', '/api/users/[i:id]', 'ApiController@updateUser'],
            ['DELETE', '/api/users/[i:id]', 'ApiController@deleteUser']
        ];
        
        $router = new Router($routes);
        $this->assertInstanceOf(Router::class, $router);
    }

    /**
     * Test router with middleware.
     */
    public function testRouterWithMiddleware(): void
    {
        $routes = [
            ['GET', '/admin/*', 'AdminController@index', 'admin', ['auth', 'admin']],
            ['GET', '/dashboard', 'DashboardController@index', 'dashboard', ['auth']]
        ];
        
        $router = new Router($routes);
        $this->assertInstanceOf(Router::class, $router);
    }

    /**
     * Test router error handling.
     */
    public function testRouterErrorHandling(): void
    {
        $this->expectException(\FloatPHP\Exceptions\Classes\RouterException::class);
        
        // Pass invalid routes (not traversable)
        $invalidRoutes = 'not_an_array';
        $router = new Router();
        $router->addRoutes($invalidRoutes);
    }

    /**
     * Test empty base path.
     */
    public function testEmptyBasePath(): void
    {
        $router = new Router([], '');
        $this->assertInstanceOf(Router::class, $router);
    }

    /**
     * Test base path with slashes.
     */
    public function testBasePathWithSlashes(): void
    {
        $router = new Router([], '/api/v1/');
        $this->assertInstanceOf(Router::class, $router);
    }

    /**
     * Test route with optional parameters.
     */
    public function testRouteWithOptionalParameters(): void
    {
        $routes = [
            ['GET', '/posts/[i:year]?/[i:month]?/[a:slug]?', 'posts.archive']
        ];
        
        $router = new Router($routes);
        $this->assertInstanceOf(Router::class, $router);
    }

    /**
     * Test route with wildcards.
     */
    public function testRouteWithWildcards(): void
    {
        $routes = [
            ['GET', '/files/**', 'files.serve'],
            ['GET', '/assets/*', 'assets.serve']
        ];
        
        $router = new Router($routes);
        $this->assertInstanceOf(Router::class, $router);
    }
}
