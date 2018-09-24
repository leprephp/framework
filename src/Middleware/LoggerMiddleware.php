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

namespace Lepre\Framework\Middleware;

use Lepre\Framework\Http\Serializer\ResponseSerializerInterface;
use Lepre\Framework\Http\Serializer\RequestSerializerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * LoggerMiddleware
 */
final class LoggerMiddleware implements MiddlewareInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RequestSerializerInterface
     */
    private $requestSerializer;

    /**
     * @var ResponseSerializerInterface
     */
    private $responseSerializer;

    /**
     * The log level for the request.
     *
     * @var false|string
     */
    private $requestLogLevel = LogLevel::DEBUG;

    /**
     * The log level for the response.
     *
     * @var false|string
     */
    private $responseLogLevel = LogLevel::DEBUG;

    /**
     * The log level for the exception eventually thrown by the request handler.
     *
     * @var false|string
     */
    private $exceptionLogLevel = LogLevel::EMERGENCY;

    /**
     * @param LoggerInterface             $logger
     * @param RequestSerializerInterface  $requestSerializer
     * @param ResponseSerializerInterface $responseSerializer
     */
    public function __construct(
        LoggerInterface $logger,
        RequestSerializerInterface $requestSerializer,
        ResponseSerializerInterface $responseSerializer
    ) {
        $this->logger = $logger;
        $this->requestSerializer = $requestSerializer;
        $this->responseSerializer = $responseSerializer;
    }

    /**
     * Sets the log level for the request.
     *
     * You can pass `false` to disable the log.
     *
     * @param false|string $requestLogLevel
     * @return $this
     * @throws InvalidArgumentException If the log level is not valid.
     */
    public function setRequestLogLevel($requestLogLevel)
    {
        $this->requestLogLevel = $this->checkLogLevel($requestLogLevel);

        return $this;
    }

    /**
     * Sets the log level for the response.
     *
     * You can pass `false` to disable the log.
     *
     * @param false|string $responseLogLevel
     * @return $this
     * @throws InvalidArgumentException If the log level is not valid.
     */
    public function setResponseLogLevel($responseLogLevel)
    {
        $this->responseLogLevel = $this->checkLogLevel($responseLogLevel);

        return $this;
    }

    /**
     * Sets the log level for the exception eventually thrown by the request handler.
     *
     * You can pass `false` to disable the log.
     *
     * @param false|string $exceptionLogLevel
     * @return $this
     * @throws InvalidArgumentException If the log level is not valid.
     */
    public function setExceptionLogLevel($exceptionLogLevel)
    {
        $this->exceptionLogLevel = $this->checkLogLevel($exceptionLogLevel);

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @throws \Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->requestLogLevel) {
            $this->logger->log(
                $this->requestLogLevel,
                'Request',
                [
                    'request' => $this->requestSerializer->serializeRequest($request),
                ]
            );
        }

        try {
            $response = $handler->handle($request);
        } catch (\Throwable $e) {
            if ($this->exceptionLogLevel) {
                $this->logger->log(
                    $this->exceptionLogLevel,
                    $e->getMessage(),
                    [
                        'exception' => $e,
                        'request'   => $this->requestSerializer->serializeRequest($request),
                    ]
                );
            }

            throw $e;
        }

        if ($this->responseLogLevel) {
            $this->logger->log(
                $this->responseLogLevel,
                'Response',
                [
                    'response' => $this->responseSerializer->serializeResponse($response),
                ]
            );
        }

        return $response;
    }

    /**
     * @param mixed $level
     * @return bool|string
     * @throws InvalidArgumentException If the log level is not valid.
     */
    private function checkLogLevel($level)
    {
        if (false === $level) {
            return false;
        }

        if (defined(LogLevel::class . '::' . strtoupper($level))) {
            return constant(LogLevel::class . '::' . strtoupper($level));
        }

        $logLevels = [
            LogLevel::DEBUG,
            LogLevel::INFO,
            LogLevel::NOTICE,
            LogLevel::WARNING,
            LogLevel::ERROR,
            LogLevel::CRITICAL,
            LogLevel::ALERT,
            LogLevel::EMERGENCY,
        ];

        throw new InvalidArgumentException(
            'Level "' . $level . '" is not defined, use one of: ' . implode(', ', $logLevels)
        );
    }
}
