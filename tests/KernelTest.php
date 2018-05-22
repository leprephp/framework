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

    use Interop\Http\Factory\ServerRequestFactoryInterface;
    use Lepre\DI\Container;
    use Lepre\Framework\Http\ResponseSenderInterface;
    use Lepre\Framework\Kernel;
    use Lepre\Framework\ModuleInterface;
    use Lepre\Framework\Test\HeaderStack;
    use Lepre\Http\Server\Server;
    use Lepre\Routing\RouterMap;
    use PHPUnit\Framework\TestCase;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;

    /**
     * @covers \Lepre\Framework\Kernel
     */
    class KernelTest extends TestCase
    {
        public function testRun()
        {
            $request = $this->createMock(ServerRequestInterface::class);
            $response = $this->createMock(ResponseInterface::class);

            $requestFactory = $this->createMock(ServerRequestFactoryInterface::class);
            $requestFactory->expects($this->once())->method('createServerRequestFromArray')->willReturn($request);

            $server = $this->createMock(Server::class);
            $server->expects($this->once())->method('handle')->with($request)->willReturn($response);

            $responseSender = $this->createMock(ResponseSenderInterface::class);
            $responseSender->expects($this->once())->method('send')->with($response);

            $module = new class($requestFactory, $server, $responseSender) implements ModuleInterface
            {
                /**
                 * @var ServerRequestFactoryInterface
                 */
                private $requestFactory;

                /**
                 * @var Server
                 */
                private $server;

                /**
                 * @var ResponseSenderInterface
                 */
                private $responseSender;

                /**
                 * @param ServerRequestFactoryInterface $requestFactory
                 * @param Server                        $server
                 * @param ResponseSenderInterface       $responseSender
                 */
                public function __construct(
                    ServerRequestFactoryInterface $requestFactory,
                    Server $server,
                    ResponseSenderInterface $responseSender
                ) {
                    $this->requestFactory = $requestFactory;
                    $this->server = $server;
                    $this->responseSender = $responseSender;
                }

                /**
                 * @inheritDoc
                 */
                public function boot(Container $container)
                {
                    $container->set('http.request_factory', function () {
                        return $this->requestFactory;
                    });

                    $container->set('http.server', function () {
                        return $this->server;
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
