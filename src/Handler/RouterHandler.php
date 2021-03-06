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

namespace Lepre\Framework\Handler;

use Fig\Http\Message\StatusCodeInterface;
use Lepre\Framework\Controller\ArgumentsResolverInterface;
use Lepre\Framework\Controller\ControllerResolverInterface;
use Lepre\Routing\Exception\MethodNotAllowedException;
use Lepre\Routing\Exception\ResourceNotFoundException;
use Lepre\Routing\RouterInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Uses the Lepre Router to provide a psr-15 request handler.
 *
 * @author Daniele De Nobili <danieledenobili@gmail.com>
 */
final class RouterHandler implements RequestHandlerInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var ControllerResolverInterface
     */
    private $controllerResolver;

    /**
     * @var ArgumentsResolverInterface
     */
    private $argumentsResolver;

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @param RouterInterface             $router
     * @param ControllerResolverInterface $controllerResolver
     * @param ArgumentsResolverInterface  $argumentsResolver
     * @param ResponseFactoryInterface    $responseFactory
     */
    public function __construct(
        RouterInterface $router,
        ControllerResolverInterface $controllerResolver,
        ArgumentsResolverInterface $argumentsResolver,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->router = $router;
        $this->controllerResolver = $controllerResolver;
        $this->argumentsResolver = $argumentsResolver;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $route = $this->router->match($request);
        } catch (ResourceNotFoundException $e) {
            return $this->createResponse(StatusCodeInterface::STATUS_NOT_FOUND, $e->getMessage());
        } catch (MethodNotAllowedException $e) {
            return $this->createResponse(StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED, $e->getMessage())
                ->withHeader('Allow', implode(', ', $e->getAllowedMethods()));
        }

        foreach ($route->getParams() as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        $handler = $route->getHandler();

        if ($handler instanceof RequestHandlerInterface) {
            return $handler->handle($request);
        }

        $controller = $this->controllerResolver->getController($handler);
        $arguments = $this->argumentsResolver->getArguments($controller, $request);

        $result = call_user_func_array($controller, $arguments);

        if ($result instanceof ResponseInterface) {
            return $result;
        }

        return $this->createResponse(StatusCodeInterface::STATUS_OK, $result);
    }

    /**
     * @param int    $statusCode
     * @param string $message
     * @return ResponseInterface
     */
    private function createResponse(int $statusCode, string $message): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($statusCode);

        $stream = $response->getBody();
        $stream->write($message);

        return $response->withBody($stream);
    }
}
