<?php

namespace FuckingSmallTest;

use FuckingSmall\RequestInterface;
use FuckingSmall\Router;
use FuckingSmall\Request;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleRouteMatching()
    {
        $request = $this->prophesize(Request::class);
        $request->willImplement(RequestInterface::class);
        $request->getUri()
            ->shouldBeCalledTimes(3)
            ->willReturn('/bob');

        $router = new Router();

        $router->attach('foo', '/foo', 'SimpleController::indexAction');
        $router->attach('bar', '/bar', 'SimpleController::indexAction');
        $router->attach('bob', '/bob', 'SimpleController::indexAction');

        $payload = $router->resolve($request->reveal());

        $this->assertEquals('SimpleController', $payload['_controller']);
        $this->assertEquals('indexAction', $payload['_method']);
    }

    public function testRouteMatchingWithTokens()
    {
        $request = $this->prophesize(Request::class);
        $request->getUri()
                ->shouldBeCalledTimes(1)
                ->willReturn('/foo/14');

        $router = new Router();

        $router->attach('foo', '/foo/{id}', 'SimpleController::indexAction');

        $payload = $router->resolve($request->reveal());

        $this->assertEquals('SimpleController', $payload['_controller']);
        $this->assertEquals('indexAction', $payload['_method']);
        $this->assertEquals(14, $payload['id']);
    }

    public function testRouteMatchingWithOptional()
    {
        $request = $this->prophesize(Request::class);
        $request->getUri()
                ->shouldBeCalledTimes(1)
                ->willReturn('/foo');

        $router = new Router();

        $router->attach('foo', '/foo/{id}', 'SimpleController::indexAction', ['defaults' => ['id' => 14]]);

        $payload = $router->resolve($request->reveal());

        $this->assertEquals('SimpleController', $payload['_controller']);
        $this->assertEquals('indexAction', $payload['_method']);
        $this->assertEquals(14, $payload['id']);
    }

    public function testRouteMatchingIntFilter()
    {
        $request = $this->prophesize(Request::class);
        $request->getUri()
                ->shouldBeCalledTimes(1)
                ->willReturn('/foo/14');

        $router = new Router();

        $router->attach('foo', '/foo/{id}', 'SimpleController::indexAction', ['filters' => ['id' => '{int}']]);

        $payload = $router->resolve($request->reveal());

        $this->assertEquals('SimpleController', $payload['_controller']);
        $this->assertEquals('indexAction', $payload['_method']);
        $this->assertEquals(14, $payload['id']);
    }

    public function testRouteFailsToMatchIntFilter()
    {
        $request = $this->prophesize(Request::class);
        $request->getUri()
                ->shouldBeCalledTimes(2)
                ->willReturn('/foo/foo');

        $router = new Router();

        $router->attach('foo_int', '/foo/{id}', 'SimpleController::indexAction', ['filters' => ['id' => '{int}']]);
        $router->attach('foo_string', '/foo/{id}', 'SimpleController::indexAction');

        $payload = $router->resolve($request->reveal());

        $this->assertEquals('SimpleController', $payload['_controller']);
        $this->assertEquals('indexAction', $payload['_method']);
        $this->assertEquals('foo_string', $payload['_route']);
        $this->assertEquals('foo', $payload['id']);
    }
}