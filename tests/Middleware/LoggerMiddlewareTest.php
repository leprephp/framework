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

namespace Lepre\Framework\Tests\Middleware;

use Lepre\Framework\Http\Serializer\MessageSerializerInterface;
use Lepre\Framework\Middleware\LoggerMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * @covers \Lepre\Framework\Middleware\LoggerMiddleware
 */
class LoggerMiddlewareTest extends TestCase
{
    public function testProcess()
    {
        $request = $this->createRequestMock();
        $response = $this->createResponseMock();
        $requestHandler = $this->createHandlerMock($request, $response);
        $serializer = $this->createSerializerMock($request, $response);

        $logger = $this->createLoggerMock();
        $this->expectLogRequest($logger, 0, LogLevel::DEBUG);
        $this->expectLogResponse($logger, 1, LogLevel::DEBUG);
        $logger->expects($this->exactly(2))->method('log');

        $middleware = new LoggerMiddleware($logger, $serializer);
        $this->assertSame($response, $middleware->process($request, $requestHandler));
    }

    public function testCustomLogLevel()
    {
        $request = $this->createRequestMock();
        $response = $this->createResponseMock();
        $requestHandler = $this->createHandlerMock($request, $response);
        $serializer = $this->createSerializerMock($request, $response);

        $logger = $this->createLoggerMock();
        $this->expectLogRequest($logger, 0, LogLevel::INFO);
        $this->expectLogResponse($logger, 1, LogLevel::INFO);
        $logger->expects($this->exactly(2))->method('log');

        $middleware = new LoggerMiddleware($logger, $serializer);
        $middleware->setRequestLogLevel(LogLevel::INFO);
        $middleware->setResponseLogLevel(LogLevel::INFO);
        $this->assertSame($response, $middleware->process($request, $requestHandler));
    }

    public function testDisableRequestLog()
    {
        $request = $this->createRequestMock();
        $response = $this->createResponseMock();
        $requestHandler = $this->createHandlerMock($request, $response);
        $serializer = $this->createSerializerMock($request, $response);

        $logger = $this->createLoggerMock();
        $this->expectLogResponse($logger, 0, LogLevel::DEBUG);
        $logger->expects($this->exactly(1))->method('log');

        $middleware = new LoggerMiddleware($logger, $serializer);
        $middleware->setRequestLogLevel(false);
        $this->assertSame($response, $middleware->process($request, $requestHandler));
    }

    public function testDisableResponseLog()
    {
        $request = $this->createRequestMock();
        $response = $this->createResponseMock();
        $requestHandler = $this->createHandlerMock($request, $response);
        $serializer = $this->createSerializerMock($request, $response);

        $logger = $this->createLoggerMock();
        $this->expectLogRequest($logger, 0, LogLevel::DEBUG);
        $logger->expects($this->exactly(1))->method('log');

        $middleware = new LoggerMiddleware($logger, $serializer);
        $middleware->setResponseLogLevel(false);
        $this->assertSame($response, $middleware->process($request, $requestHandler));
    }

    public function testWhenTheRequestHandlerThrowsAnException()
    {
        $request = $this->createRequestMock();
        $exception = new \Exception('The exception message');
        $requestHandler = $this->createHandlerMock($request, null, $exception);
        $serializer = $this->createSerializerMock($request);

        $logger = $this->createLoggerMock();
        $this->expectLogRequest($logger, 0, LogLevel::DEBUG);
        $this->expectLogException($logger, $exception, 1, LogLevel::EMERGENCY);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The exception message');

        $middleware = new LoggerMiddleware($logger, $serializer);
        $middleware->process($request, $requestHandler);
    }

    public function testCustomLogLevelAndTheRequestHandlerThrowsAnException()
    {
        $request = $this->createRequestMock();
        $exception = new \Exception('The exception message');
        $requestHandler = $this->createHandlerMock($request, null, $exception);
        $serializer = $this->createSerializerMock($request);

        $logger = $this->createLoggerMock();
        $this->expectLogRequest($logger, 0, LogLevel::INFO);
        $this->expectLogException($logger, $exception, 1, LogLevel::ALERT);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The exception message');

        $middleware = new LoggerMiddleware($logger, $serializer);
        $middleware->setRequestLogLevel(LogLevel::INFO);
        $middleware->setExceptionLogLevel(LogLevel::ALERT);
        $middleware->process($request, $requestHandler);
    }

    /**
     * @param string $method
     *
     * @dataProvider             logLevelMethodsProvider
     */
    public function testWrongLogLevel($method)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Level "wrong" is not defined, use one of: debug, info, notice, warning, error, critical, alert, emergency');

        $logger = $this->createLoggerMock();
        $serializer = $this->createSerializerMock();

        /**
         * @var MessageSerializerInterface  $serializer
         * @var LoggerInterface             $logger
         */

        $middleware = new LoggerMiddleware($logger, $serializer);
        $middleware->$method('wrong');
    }

    public function logLevelMethodsProvider()
    {
        return [
            ['setRequestLogLevel'],
            ['setResponseLogLevel'],
            ['setExceptionLogLevel'],
        ];
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|ServerRequestInterface
     */
    private function createRequestMock()
    {
        return $this->createMock(ServerRequestInterface::class);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|ResponseInterface
     */
    private function createResponseMock()
    {
        return $this->createMock(ResponseInterface::class);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param \Exception             $exception
     * @return \PHPUnit\Framework\MockObject\MockObject|RequestHandlerInterface
     */
    private function createHandlerMock($request, $response = null, $exception = null)
    {
        $requestHandler = $this->createMock(RequestHandlerInterface::class);

        if ($response) {
            $requestHandler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->willReturn($response);
        }

        if ($exception) {
            $requestHandler->expects($this->once())
                ->method('handle')
                ->with($request)
                ->willThrowException($exception);
        }

        return $requestHandler;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @return \PHPUnit\Framework\MockObject\MockObject|MessageSerializerInterface
     */
    private function createSerializerMock($request = null, $response = null)
    {
        $serializer = $this->createMock(MessageSerializerInterface::class);

        if ($request) {
            $serializer->expects($this->any())
                ->method('serializeRequest')
                ->with($request)
                ->willReturn('the serialized request');
        }

        if ($response) {
            $serializer->expects($this->any())
                ->method('serializeResponse')
                ->with($response)
                ->willReturn('the serialized response');
        }

        return $serializer;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|LoggerInterface
     */
    private function createLoggerMock()
    {
        return $this->createMock(LoggerInterface::class);
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject $logger
     * @param int                                      $index
     * @param string                                   $logLevel
     */
    private function expectLogRequest($logger, $index = 0, $logLevel = LogLevel::DEBUG)
    {
        $logger->expects($this->at($index))
            ->method('log')
            ->with($logLevel, 'Request', ['request' => 'the serialized request']);
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject $logger
     * @param int                                      $index
     * @param string                                   $logLevel
     */
    private function expectLogResponse($logger, $index = 0, $logLevel = LogLevel::DEBUG)
    {
        $logger->expects($this->at($index))
            ->method('log')
            ->with($logLevel, 'Response', ['response' => 'the serialized response']);
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject $logger
     * @param \Throwable                               $exception
     * @param int                                      $index
     * @param string                                   $logLevel
     */
    private function expectLogException($logger, $exception, $index = 0, $logLevel = LogLevel::DEBUG)
    {
        $logger->expects($this->at($index))
            ->method('log')
            ->with(
                $logLevel,
                $exception->getMessage(),
                ['request' => 'the serialized request', 'exception' => $exception]
            );
    }
}
