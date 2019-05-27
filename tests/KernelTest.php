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

namespace Lepre\Framework\Http {

    if (!function_exists('Lepre\Framework\Http\header')) {
        function header($string)
        {
            \Lepre\Framework\Test\HeaderStack::push($string);
        }
    }
}

namespace Lepre\Framework\Tests {

    use Lepre\DI\Container;
    use Lepre\Framework\Http\ResponseSenderInterface;
    use Lepre\Framework\Http\ServerRequestFactoryFromGlobalsInterface;
    use Lepre\Framework\Kernel;
    use Lepre\Framework\ModuleInterface;
    use Lepre\Framework\Test\HeaderStack;
    use Lepre\Routing\RouterMap;
    use PHPUnit\Framework\TestCase;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestFactoryInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Psr\Http\Server\RequestHandlerInterface;

    /**
     * @covers \Lepre\Framework\Kernel
     */
    final class KernelTest extends TestCase
    {
        /**
         * @param iterable $modules
         * @dataProvider modulesProvider
         */
        public function testIterableModules($modules)
        {
            $exception = null;

            try {
                new Kernel($modules);
            } catch (\TypeError $exception) {
            }

            $this->assertNull($exception, 'Unexpected TypeError');
        }

        public function modulesProvider()
        {
            // array
            yield [
                [
                    $this->createMock(ModuleInterface::class),
                    $this->createMock(ModuleInterface::class),
                ]
            ];

            // iterator
            yield [
                new \ArrayIterator([
                    $this->createMock(ModuleInterface::class),
                    $this->createMock(ModuleInterface::class),
                ])
            ];

            // generator
            $generator = function () {
                yield $this->createMock(ModuleInterface::class);
                yield $this->createMock(ModuleInterface::class);
            };

            yield [$generator()];
        }

        /**
         * Tests the internal php error is run when create the kernel with invalid modules.
         *
         * @param mixed $modules
         * @dataProvider wrongModulesProvider
         */
        public function testWrongModules($modules)
        {
            $this->expectException(\TypeError::class);

            new Kernel($modules);
        }

        public function wrongModulesProvider()
        {
            yield [null];
            yield [123];
            yield ['must be an iterable'];
            yield [['must be a ModuleInterface']];
        }

        public function testRun()
        {
            $request = $this->createMock(ServerRequestInterface::class);
            $response = $this->createMock(ResponseInterface::class);

            $requestFactory = $this->createMock(ServerRequestFactoryFromGlobalsInterface::class);
            $requestFactory->expects($this->once())->method('createServerRequestFromGlobals')->willReturn($request);

            $requestHandler = $this->createMock(RequestHandlerInterface::class);
            $requestHandler->expects($this->once())->method('handle')->with($request)->willReturn($response);

            $responseSender = $this->createMock(ResponseSenderInterface::class);
            $responseSender->expects($this->once())->method('send')->with($response);

            $module = new class($requestFactory, $requestHandler, $responseSender) implements ModuleInterface
            {
                /**
                 * @var ServerRequestFactoryInterface
                 */
                private $requestFactory;

                /**
                 * @var RequestHandlerInterface
                 */
                private $requestHandler;

                /**
                 * @var ResponseSenderInterface
                 */
                private $responseSender;

                /**
                 * @param ServerRequestFactoryFromGlobalsInterface $requestFactory
                 * @param RequestHandlerInterface                  $requestHandler
                 * @param ResponseSenderInterface                  $responseSender
                 */
                public function __construct(
                    ServerRequestFactoryFromGlobalsInterface $requestFactory,
                    RequestHandlerInterface $requestHandler,
                    ResponseSenderInterface $responseSender
                ) {
                    $this->requestFactory = $requestFactory;
                    $this->requestHandler = $requestHandler;
                    $this->responseSender = $responseSender;
                }

                /**
                 * @inheritDoc
                 */
                public function boot(Container $container)
                {
                    $container->set('http.request_factory_from_globals', function () {
                        return $this->requestFactory;
                    });

                    $container->set('http.request_handler', function () {
                        return $this->requestHandler;
                    });

                    $container->set('http.response_sender', function () {
                        return $this->responseSender;
                    });
                }
            };

            $kernel = new Kernel([$module]);
            $kernel->run();
        }

        /**
         * @runInSeparateProcess
         */
        public function testRealCase()
        {
            $originalServer = $_SERVER;

            $_SERVER = [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI'    => '/',
            ];

            $module = new class() implements ModuleInterface
            {
                public function boot(Container $container)
                {
                    $container->extend(
                        RouterMap::class,
                        function (RouterMap $routerMap) {
                            $routerMap->get('/', function () {
                                return 'This is the home page';
                            });
                        }
                    );
                }
            };

            ob_start();

            $kernel = new Kernel([$module]);
            $kernel->run();
            $content = ob_get_contents();

            ob_end_clean();

            $_SERVER = $originalServer;

            $this->assertTrue(HeaderStack::has('HTTP/1.1 200 OK'));
            $this->assertEquals('This is the home page', $content);
        }
    }
}
