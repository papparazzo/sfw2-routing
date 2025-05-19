<?php

namespace SFW2\Routing\Tests\Unit\ControllerMap;

use ArrayObject;
use DateTime;
use Exception;
use PHPUnit\Framework\TestCase;
use SFW2\Routing\ControllerMap\ControllerData;
use SplFileInfo;
use SplFileObject;
use SplStack;
use stdClass;

class ControllerDataTest extends TestCase
{
    /**
     * Test creating a new ControllerData instance with default values
     */
    public function testCreateWithDefaults(): void
    {
        $controllerData = new ControllerData(stdClass::class);
        
        static::assertSame(stdClass::class, $controllerData->getClassName());
        static::assertSame('index', $controllerData->getAction());
        static::assertSame([], $controllerData->getAdditionalData());
        static::assertSame([], $controllerData->getActionData());
    }
    
    /**
     * Test creating a new ControllerData instance with custom values
     */
    public function testCreateWithCustomValues(): void
    {
        $className = DateTime::class;
        $action = 'custom';
        $additionalData = ['key' => 'value', 'another' => 123];
        
        $controllerData = new ControllerData($className, $action, $additionalData);
        
        static::assertSame($className, $controllerData->getClassName());
        static::assertSame($action, $controllerData->getAction());
        static::assertSame($additionalData, $controllerData->getAdditionalData());
        static::assertSame([], $controllerData->getActionData()); // Should be empty initially
    }
    
    /**
     * Test the withActionParams method creates a new instance with updated action data
     */
    public function testWithActionParams(): void
    {
        $original = new ControllerData(Exception::class, 'test');
        $actionData = ['id' => '123', 'slug' => 'test-slug'];
        
        $new = $original->withActionParams($actionData);
        
        // Test that a new instance was returned
        static::assertNotSame($original, $new);
        
        // Test that the action data was updated in the new instance
        static::assertSame($actionData, $new->getActionData());
        
        // Test that original was not modified
        static::assertSame([], $original->getActionData());
        
        // Test that other properties were preserved
        static::assertSame($original->getClassName(), $new->getClassName());
        static::assertSame($original->getAction(), $new->getAction());
        static::assertSame($original->getAdditionalData(), $new->getAdditionalData());
    }
    
    /**
     * Test that getClassName returns the correct class name
     */
    public function testGetClassName(): void
    {
        $className = ArrayObject::class;
        $controllerData = new ControllerData($className);
        
        static::assertSame($className, $controllerData->getClassName());
    }
    
    /**
     * Test that getAction returns the correct action name
     */
    public function testGetAction(): void
    {
        $action = 'delete';
        $controllerData = new ControllerData(SplFileInfo::class, $action);
        
        static::assertSame($action, $controllerData->getAction());
    }
    
    /**
     * Test that getActionData returns the correct action data
     */
    public function testGetActionData(): void
    {
        $controllerData = new ControllerData(SplFileObject::class);
        $actionData = ['param1' => 'value1', 'param2' => 'value2'];
        
        $updatedControllerData = $controllerData->withActionParams($actionData);
        
        static::assertSame($actionData, $updatedControllerData->getActionData());
    }
    
    /**
     * Test that getAdditionalData returns the correct additional data
     */
    public function testGetAdditionalData(): void
    {
        $additionalData = ['config' => 'test', 'values' => [1, 2, 3]];
        $controllerData = new ControllerData(SplStack::class, 'update', $additionalData);
        
        static::assertSame($additionalData, $controllerData->getAdditionalData());
    }
}
