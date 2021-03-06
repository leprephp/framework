<?php

/*
 * This file is part of the Lepre package.
 *
 * (c) Daniele De Nobili <danieledenobili@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Lepre\Framework\Tests\Handler;

use Http\Factory\Diactoros\ResponseFactory;
use Http\Factory\Diactoros\ServerRequestFactory;
use Lepre\Framework\Controller\ArgumentsResolverInterface;
use Lepre\Framework\Controller\ControllerResolverInterface;
use Lepre\Framework\Handler\RouterHandler;
use Lepre\Routing\Exception\MethodNotAllowedException;
use Lepre\Routing\Exception\ResourceNotFoundException;
use Lepre\Routing\RouteResult;
use Lepre\Routing\RouterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @covers \Lepre\Framework\Handler\RouterHandler
 */
final class RouterHandlerTest extends TestCase
{
    /**
     * @var RouterInterface|MockObject
     */
    protected $router;

    /**
     * @var ControllerResolverInterface|MockObject
     */
    protected $controllerResolver;

    /**
     * @var ArgumentsResolverInterface|MockObject
     */
    protected $argumentsResolver;

    /**
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        $this->router = $this->createMock(RouterInterface::class);
        $this->controllerResolver = $this->createMock(ControllerResolverInterface::class);
        $this->argumentsResolver = $this->createMock(ArgumentsResolverInterface::class);
        $this->responseFactory = new ResponseFactory();
        $this->request = (new ServerRequestFactory())->createServerRequest('GET', '/');
    }

    public function testResourceNotFoundException()
    {
        $this->router->method('match')->willThrowException(
            new ResourceNotFoundException('Page not found')
        );

        $handler = new RouterHandler(
            $this->router,
            $this->controllerResolver,
            $this->argumentsResolver,
            $this->responseFactory
        );

        $response = $handler->handle($this->request);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Page not found', $response->getBody()->__toString());
    }

    public function testMethodNotAllowedException()
    {
        $this->router->method('match')->willThrowException(
            new MethodNotAllowedException(['POST', 'PUT', 'PATCH'], 'The request method is not allowed')
        );

        $handler = new RouterHandler(
            $this->router,
            $this->controllerResolver,
            $this->argumentsResolver,
            $this->responseFactory
        );

        $response = $handler->handle($this->request);

        $this->assertEquals(405, $response->getStatusCode());
        $this->assertEquals('The request method is not allowed', $response->getBody()->__toString());
        $this->assertEquals('POST, PUT, PATCH', $response->getHeaderLine('Allow'));
    }

    public function testWhenTheControllerIsAnInstanceOfPsrRequestHandlerInterface()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|ServerRequestInterface $response */
        $response = $this->createMock(ResponseInterface::class);

        $params = [
            'foo' => 123,
            'bar' => 'baz',
            'baz' => ['a', 'b', 'c'],
        ];

        $handler = $this->createPsrRequestHandlerFixture(
            function (ServerRequestInterface $request) use ($params, $response) {
                $this->assertEquals($params, $request->getAttributes());

                return $response;
            }
        );

        $this->assertSame(
            $response,
            $this->createRouterHandler($handler, $params)->handle($this->request)
        );
    }

    public function testTheRouterParamsWillBePassedToTheRequest()
    {
        $params = [
            'foo' => 123,
            'bar' => 'baz',
            'baz' => ['a', 'b', 'c'],
        ];

        $response = $this->createMock(ResponseInterface::class);
        $controller = function (ServerRequestInterface $request) use ($response) {
            $this->assertEquals(123, $request->getAttribute('foo'));
            $this->assertEquals('baz', $request->getAttribute('bar'));
            $this->assertEquals(['a', 'b', 'c'], $request->getAttribute('baz'));

            return $response;
        };

        $this->assertSame($response, $this->createRouterHandler($controller, $params)->handle($this->request));
    }

    public function testWhenTheControllerReturnsAString()
    {
        $controller = function () {
            return 'The response message!';
        };

        $response = $this->createRouterHandler($controller)->handle($this->request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('The response message!', $response->getBody()->__toString());
    }

    /**
     * @param callable|RequestHandlerInterface $controller
     * @param array                            $params
     * @return RouterHandler
     */
    private function createRouterHandler($controller, array $params = [])
    {
        $route = new RouteResult($controller, $params);

        $this->router->expects($this->atLeastOnce())
            ->method('match')
            ->with($this->request)
            ->willReturn($route);

        if ($controller instanceof RequestHandlerInterface) {
            $this->controllerResolver
                ->expects($this->never())
                ->method('getController');
        } elseif (is_callable($controller)) {
            $this->controllerResolver
                ->method('getController')
                ->with($controller)
                ->willReturn($controller);
        } else {
            throw new \InvalidArgumentException(sprintf(
                'Argument 2 passed to %s() must be a callable or an instance of %s',
                __METHOD__,
                RequestHandlerInterface::class
            ));
        }

        $this->argumentsResolver
            ->method('getArguments')
            ->with($controller, $this->isInstanceOf(ServerRequestInterface::class))
            ->will($this->returnCallback(function ($controller, $request) {
                return [$request];
            }));

        return new RouterHandler(
            $this->router,
            $this->controllerResolver,
            $this->argumentsResolver,
            $this->responseFactory
        );
    }

    /**
     * @param callable $callable
     * @return RequestHandlerInterface
     */
    private function createPsrRequestHandlerFixture(callable $callable)
    {
        return new class($callable) implements RequestHandlerInterface
        {
            private $callable;

            public function __construct(callable $callable)
            {
                $this->callable = $callable;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return call_user_func($this->callable, $request);
            }
        };
    }
}
