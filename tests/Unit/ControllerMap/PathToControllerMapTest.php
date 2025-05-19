<?php

declare(strict_types=1);

namespace SFW2\Routing\Tests\Unit\ControllerMap;

use PHPUnit\Framework\TestCase;
use SFW2\Exception\HttpExceptions\Status4xx\HttpStatus404NotFound;
use SFW2\Exception\HttpExceptions\Status4xx\HttpStatus405MethodNotAllowed;
use SFW2\Routing\ControllerMap\ControllerData;
use SFW2\Routing\ControllerMap\PathToControllerMap;

class PathToControllerMapTest extends TestCase
{
    private PathToControllerMap $map;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->map = new PathToControllerMap();
    }

    /**
     * @throws HttpStatus404NotFound
     * @throws HttpStatus405MethodNotAllowed
     */
    public function testAppendControllerData(): void
    {
        $controllerData = new ControllerData('TestController');
        
        $this->map->appendControllerData('GET', '/test', $controllerData);
        
        $result = $this->map->getControllerRulesetByPath('GET', '/test');
        
        static::assertSame($controllerData->getClassName(), $result->getClassName());
        static::assertSame($controllerData->getAction(), $result->getAction());
    }

    /**
     * @throws HttpStatus404NotFound
     * @throws HttpStatus405MethodNotAllowed
     */
    public function testMethodNormalization(): void
    {
        $controllerData = new ControllerData('TestController');
        
        // Add with a lowercase method
        $this->map->appendControllerData('get', '/test', $controllerData);
        
        // Retrieve with uppercase method
        $result = $this->map->getControllerRulesetByPath('GET', '/test');
        
        static::assertSame($controllerData->getClassName(), $result->getClassName());
    }

    /**
     * @throws HttpStatus404NotFound
     * @throws HttpStatus405MethodNotAllowed
     */
    public function testRegexPathMatching(): void
    {
        $controllerData = new ControllerData('TestController');
        
        // Path with a regex pattern
        $this->map->appendControllerData('GET', '/users/(\d+)', $controllerData);
        
        $result = $this->map->getControllerRulesetByPath('GET', '/users/123');
        
        static::assertSame($controllerData->getClassName(), $result->getClassName());
        static::assertNotEmpty($result->getActionData());
        static::assertSame('123', $result->getActionData()[0]);
    }

    /**
     * @throws HttpStatus404NotFound
     * @throws HttpStatus405MethodNotAllowed
     */
    public function testNamedRegexPathMatching(): void
    {
        $controllerData = new ControllerData('TestController');
        
        // Path with named regex parameters
        $this->map->appendControllerData('GET', '/users/(?<id>\d+)/profile/(?<section>[a-z]+)', $controllerData);
        
        $result = $this->map->getControllerRulesetByPath('GET', '/users/123/profile/details');
        
        static::assertSame($controllerData->getClassName(), $result->getClassName());
        static::assertNotEmpty($result->getActionData());
        static::assertSame('123', $result->getActionData()['id']);
        static::assertSame('details', $result->getActionData()['section']);
    }

    /**
     * @throws HttpStatus404NotFound
     * @throws HttpStatus405MethodNotAllowed
     */
    public function testAnyMethodFallback(): void
    {
        $controllerData = new ControllerData('TestController');
        
        // Add with ANY method
        $this->map->appendControllerData('ANY', '/wildcard', $controllerData);
        
        // Should match with any HTTP method
        $result = $this->map->getControllerRulesetByPath('GET', '/wildcard');
        static::assertSame($controllerData->getClassName(), $result->getClassName());
        
        $result = $this->map->getControllerRulesetByPath('POST', '/wildcard');
        static::assertSame($controllerData->getClassName(), $result->getClassName());
        
        $result = $this->map->getControllerRulesetByPath('DELETE', '/wildcard');
        static::assertSame($controllerData->getClassName(), $result->getClassName());
    }

    /**
     * @return void
     * @throws HttpStatus404NotFound
     * @throws HttpStatus405MethodNotAllowed
     */
    public function testMethodNotAllowedException(): void
    {
        $controllerData = new ControllerData('TestController');
        
        // Add only GET method
        $this->map->appendControllerData('GET', '/restricted', $controllerData);
        
        // Should throw HttpStatus405MethodNotAllowed for other methods
        $this->expectException(HttpStatus405MethodNotAllowed::class);
        $this->map->getControllerRulesetByPath('POST', '/restricted');
    }

    /**
     * @return void
     * @throws HttpStatus404NotFound
     */
    public function testMethodNotAllowedIncludesAllowedMethods(): void
    {
        $controllerData = new ControllerData('TestController');
        
        // Add two methods
        $this->map->appendControllerData('GET', '/multiple', $controllerData);
        $this->map->appendControllerData('POST', '/multiple', $controllerData);
        
        try {
            $this->map->getControllerRulesetByPath('DELETE', '/multiple');
            static::fail('Expected HttpStatus405MethodNotAllowed exception was not thrown');
        } catch (HttpStatus405MethodNotAllowed $exception) {
            // Check that allowed methods are included in the exception
            $additionalHeaders = $exception->getAdditionalHeaders();
            static::assertArrayHasKey('Allow', $additionalHeaders);
            static::assertStringContainsString('GET', $additionalHeaders['Allow']);
            static::assertStringContainsString('POST', $additionalHeaders['Allow']);
        }
    }

    /**
     * @throws HttpStatus405MethodNotAllowed
     */
    public function testNotFoundException(): void
    {
        // Empty map should throw 404 for any path
        $this->expectException(HttpStatus404NotFound::class);
        $this->map->getControllerRulesetByPath('GET', '/nonexistent');
    }

    /**
     * @throws HttpStatus405MethodNotAllowed
     */
    public function testNoMatchingPathThrows404(): void
    {
        $controllerData = new ControllerData('TestController');
        
        // Add a specific path
        $this->map->appendControllerData('GET', '/exists', $controllerData);
        
        // Different path should throw 404
        $this->expectException(HttpStatus404NotFound::class);
        $this->map->getControllerRulesetByPath('GET', '/doesnotexist');
    }

    /**
     * @throws HttpStatus405MethodNotAllowed
     */
    public function testPartialPathMatchNoMatch(): void
    {
        $controllerData = new ControllerData('TestController');
        
        // Add a specific path
        $this->map->appendControllerData('GET', '/api/v1/users', $controllerData);
        
        // Partial match should still throw 404
        $this->expectException(HttpStatus404NotFound::class);
        $this->map->getControllerRulesetByPath('GET', '/api/v1');
    }
}
