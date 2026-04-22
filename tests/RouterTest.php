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
     * Test get routes returns array.
     */
    public function testGetRoutes(): void
    {
        $routes = $this->router->getRoutes();
        $this->assertIsArray($routes);
        $this->assertEmpty($routes);
    }

    /**
     * Test get routes returns added routes.
     */
    public function testGetRoutesReturnsAddedRoutes(): void
    {
        $routes = [
            ['GET', '/', 'home'],
            ['POST', '/login', 'login']
        ];
        $router = new Router($routes);
        $this->assertCount(2, $router->getRoutes());
    }

    /**
     * Test add routes adds routes to the list.
     */
    public function testAddRoutes(): void
    {
        $routes = [
            ['GET', '/test', 'test.controller'],
            ['POST', '/test', 'test.store']
        ];
        $this->router->addRoutes($routes);
        $this->assertCount(2, $this->router->getRoutes());
    }

    /**
     * Test set and get base path.
     */
    public function testSetAndGetBase(): void
    {
        $this->router->setBase('/api');
        $this->assertEquals('/api', $this->router->getBase());
    }

    /**
     * Test default base path is empty string.
     */
    public function testDefaultBasePath(): void
    {
        $this->assertEquals('', $this->router->getBase());
    }

    /**
     * Test get types returns array.
     */
    public function testGetTypes(): void
    {
        $this->assertIsArray($this->router->getTypes());
        $this->assertArrayHasKey('i', $this->router->getTypes());
    }

    /**
     * Test add custom types merges with defaults.
     */
    public function testAddTypes(): void
    {
        $this->router->addTypes(['slug' => '[a-z0-9\-]+']);
        $types = $this->router->getTypes();
        $this->assertArrayHasKey('slug', $types);
        $this->assertArrayHasKey('i', $types);
    }

    /**
     * Test get names returns empty array initially.
     */
    public function testGetNamesEmpty(): void
    {
        $this->assertIsArray($this->router->getNames());
        $this->assertEmpty($this->router->getNames());
    }

    /**
     * Test get names returns named routes.
     */
    public function testGetNamesWithNamedRoutes(): void
    {
        $this->router->map('GET', '/', 'home', 'home');
        $this->router->map('GET', '/about', 'about', 'about');
        $names = $this->router->getNames();
        $this->assertArrayHasKey('home', $names);
        $this->assertArrayHasKey('about', $names);
        $this->assertEquals('/', $names['home']);
        $this->assertEquals('/about', $names['about']);
    }

    /**
     * Test has route returns true for existing named route.
     */
    public function testHasRouteTrue(): void
    {
        $this->router->map('GET', '/dashboard', 'DashboardController@index', 'dashboard');
        $this->assertTrue($this->router->hasRoute('dashboard'));
    }

    /**
     * Test has route returns false for non-existing named route.
     */
    public function testHasRouteFalse(): void
    {
        $this->assertFalse($this->router->hasRoute('nonexistent'));
    }

    /**
     * Test clear routes empties routes, names and cache.
     */
    public function testClearRoutes(): void
    {
        $this->router->map('GET', '/', 'home', 'home');
        $this->router->clearRoutes();
        $this->assertEmpty($this->router->getRoutes());
        $this->assertEmpty($this->router->getNames());
        $this->assertFalse($this->router->hasRoute('home'));
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
        $this->assertCount(3, $routesList);
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

    // -------------------------------------------------------------------------
    // match() tests
    // -------------------------------------------------------------------------

    /**
     * Test match returns false when no routes registered.
     */
    public function testMatchReturnsFalseWithNoRoutes(): void
    {
        $result = $this->router->match('/', 'GET');
        $this->assertFalse($result);
    }

    /**
     * Test match returns false when no route matches.
     */
    public function testMatchReturnsFalseOnNoMatch(): void
    {
        $this->router->map('GET', '/', 'home');
        $result = $this->router->match('/notfound', 'GET');
        $this->assertFalse($result);
    }

    /**
     * Test match returns match array for simple static route.
     */
    public function testMatchSimpleRoute(): void
    {
        $this->router->map('GET', '/', 'HomeController@index', 'home');
        $result = $this->router->match('/', 'GET');

        $this->assertIsArray($result);
        $this->assertEquals('HomeController@index', $result['target']);
        $this->assertEquals('home', $result['name']);
        $this->assertIsArray($result['params']);
        $this->assertEmpty($result['params']);
    }

    /**
     * Test match with integer parameter.
     */
    public function testMatchWithIntegerParam(): void
    {
        $this->router->map('GET', '/users/[i:id]', 'UserController@show');
        $result = $this->router->match('/users/42', 'GET');

        $this->assertIsArray($result);
        $this->assertEquals('UserController@show', $result['target']);
        $this->assertEquals('42', $result['params']['id']);
    }

    /**
     * Test match with alphanumeric parameter.
     */
    public function testMatchWithAlphanumericParam(): void
    {
        $this->router->map('GET', '/posts/[a:slug]', 'PostController@show');
        $result = $this->router->match('/posts/myPost123', 'GET');

        $this->assertIsArray($result);
        $this->assertEquals('myPost123', $result['params']['slug']);
    }

    /**
     * Test match with multiple parameters.
     */
    public function testMatchWithMultipleParams(): void
    {
        $this->router->map('GET', '/users/[i:userId]/posts/[i:postId]', 'PostController@show');
        $result = $this->router->match('/users/5/posts/10', 'GET');

        $this->assertIsArray($result);
        $this->assertEquals('5', $result['params']['userId']);
        $this->assertEquals('10', $result['params']['postId']);
    }

    /**
     * Test match respects HTTP method.
     */
    public function testMatchRespectsMethod(): void
    {
        $this->router->map('POST', '/users', 'UserController@create');
        $result = $this->router->match('/users', 'GET');
        $this->assertFalse($result);
    }

    /**
     * Test match with multiple methods separated by pipe.
     */
    public function testMatchWithMultipleMethods(): void
    {
        $this->router->map('GET|POST', '/contact', 'ContactController@handle');

        $getResult  = $this->router->match('/contact', 'GET');
        $postResult = $this->router->match('/contact', 'POST');
        $putResult  = $this->router->match('/contact', 'PUT');

        $this->assertIsArray($getResult);
        $this->assertIsArray($postResult);
        $this->assertFalse($putResult);
    }

    /**
     * Test match with wildcard method.
     */
    public function testMatchWithWildcardMethod(): void
    {
        $this->router->map('*', '/any', 'AnyController@handle');

        $this->assertIsArray($this->router->match('/any', 'GET'));
        $this->assertIsArray($this->router->match('/any', 'DELETE'));
        $this->assertIsArray($this->router->match('/any', 'PATCH'));
    }

    /**
     * Test match with wildcard route.
     */
    public function testMatchWithWildcardRoute(): void
    {
        $this->router->map('GET', '*', 'FallbackController@handle');
        $result = $this->router->match('/anything/at/all', 'GET');
        $this->assertIsArray($result);
    }

    /**
     * Test match returns permission.
     */
    public function testMatchReturnsPermission(): void
    {
        $this->router->map('GET', '/admin', 'AdminController@index', 'admin', ['auth', 'admin']);
        $result = $this->router->match('/admin', 'GET');

        $this->assertIsArray($result);
        $this->assertEquals(['auth', 'admin'], $result['permission']);
    }

    /**
     * Test match strips query string from URL.
     */
    public function testMatchStripsQueryString(): void
    {
        $this->router->map('GET', '/search', 'SearchController@index');
        $result = $this->router->match('/search?q=hello&page=2', 'GET');
        $this->assertIsArray($result);
    }

    /**
     * Test match with base path.
     */
    public function testMatchWithBasePath(): void
    {
        $router = new Router([], '/api/v1');
        $router->map('GET', '/users', 'UserController@index');

        $this->assertIsArray($router->match('/api/v1/users', 'GET'));
        $this->assertFalse($router->match('/users', 'GET'));
    }

    /**
     * Test match with regex (@) route prefix.
     */
    public function testMatchWithRegexRoute(): void
    {
        $this->router->map('GET', '@^/files/[0-9]+$', 'FileController@show');
        $result = $this->router->match('/files/123', 'GET');
        $this->assertIsArray($result);
    }

    /**
     * Test match is case-insensitive on HTTP method.
     */
    public function testMatchMethodCaseInsensitive(): void
    {
        $this->router->map('GET', '/page', 'PageController@show');
        $result = $this->router->match('/page', 'get');
        $this->assertIsArray($result);
    }

    // -------------------------------------------------------------------------
    // generate() tests
    // -------------------------------------------------------------------------

    /**
     * Test generate returns path for static route.
     */
    public function testGenerateStaticRoute(): void
    {
        $this->router->map('GET', '/about', 'PageController@about', 'about');
        $url = $this->router->generate('about');
        $this->assertEquals('/about', $url);
    }

    /**
     * Test generate substitutes named parameters.
     */
    public function testGenerateWithParams(): void
    {
        $this->router->map('GET', '/users/[i:id]', 'UserController@show', 'users.show');
        $url = $this->router->generate('users.show', ['id' => 7]);
        $this->assertEquals('/users/7', $url);
    }

    /**
     * Test generate with multiple parameters.
     */
    public function testGenerateWithMultipleParams(): void
    {
        $this->router->map('GET', '/users/[i:userId]/posts/[i:postId]', 'PostController@show', 'user.post');
        $url = $this->router->generate('user.post', ['userId' => 3, 'postId' => 9]);
        $this->assertEquals('/users/3/posts/9', $url);
    }

    /**
     * Test generate prepends base path.
     */
    public function testGenerateWithBasePath(): void
    {
        $router = new Router([], '/api/v1');
        $router->map('GET', '/users', 'UserController@index', 'users.index');
        $url = $router->generate('users.index');
        $this->assertEquals('/api/v1/users', $url);
    }

    // -------------------------------------------------------------------------
    // RouterException tests
    // -------------------------------------------------------------------------

    /**
     * Test map throws RouterException for empty method.
     */
    public function testMapThrowsExceptionOnEmptyMethod(): void
    {
        $this->expectException(\FloatPHP\Exceptions\Classes\RouterException::class);
        $this->router->map('', '/test', 'controller');
    }

    /**
     * Test map throws RouterException for empty route.
     */
    public function testMapThrowsExceptionOnEmptyRoute(): void
    {
        $this->expectException(\FloatPHP\Exceptions\Classes\RouterException::class);
        $this->router->map('GET', '', 'controller');
    }

    /**
     * Test map throws RouterException for duplicate named route.
     */
    public function testMapThrowsExceptionOnDuplicateName(): void
    {
        $this->expectException(\FloatPHP\Exceptions\Classes\RouterException::class);
        $this->router->map('GET', '/home', 'HomeController@index', 'home');
        $this->router->map('GET', '/home2', 'HomeController@index', 'home');
    }

    /**
     * Test generate throws RouterException for empty name.
     */
    public function testGenerateThrowsExceptionOnEmptyName(): void
    {
        $this->expectException(\FloatPHP\Exceptions\Classes\RouterException::class);
        $this->router->generate('');
    }

    /**
     * Test generate throws RouterException for non-existent route name.
     */
    public function testGenerateThrowsExceptionOnNonExistentRoute(): void
    {
        $this->expectException(\FloatPHP\Exceptions\Classes\RouterException::class);
        $this->router->generate('does.not.exist');
    }
}
