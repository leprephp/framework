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

namespace Lepre\Framework;

use Http\Factory\Diactoros\ResponseFactory;
use Lepre\DI\Container;
use Lepre\Framework\Controller\ArgumentsResolver;
use Lepre\Framework\Controller\ArgumentsResolverInterface;
use Lepre\Framework\Controller\ControllerResolver;
use Lepre\Framework\Controller\ControllerResolverInterface;
use Lepre\Framework\Http\ResponseSender;
use Lepre\Framework\Http\ResponseSenderInterface;
use Lepre\Framework\Handler\RouterHandler;
use Lepre\Framework\Http\ServerRequestFactoryFromGlobals;
use Lepre\Framework\Http\ServerRequestFactoryFromGlobalsInterface;
use Lepre\Http\Server\Server;
use Lepre\Routing\Bridge\AuraRouter\AuraRouterMapAdapter;
use Lepre\Routing\RouterCollection;
use Lepre\Routing\RouterInterface;
use Lepre\Routing\RouterMap;
use Lepre\Routing\RouterMapAdapterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The Kernel is the main entry point of the Lepre framework.
 *
 * @author Daniele De Nobili <danieledenobili@gmail.com>
 */
final class Kernel
{
    /**
     * @var ModuleInterface[]
     */
    private $modules = [];

    /**
     * @var string
     */
    private $environment = 'production';

    /**
     * @var bool
     */
    private $debug = false;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ModuleInterface[] $modules
     * @param string            $environment
     * @param bool              $debug
     */
    public function __construct(iterable $modules = [], string $environment = 'production', bool $debug = false)
    {
        $this->environment = $environment;
        $this->debug = $debug;

        foreach ($modules as $module) {
            $this->registerModule($module);
        }
    }

    /**
     * @param ModuleInterface $module
     */
    private function registerModule(ModuleInterface $module)
    {
        $this->modules[] = $module;
    }

    /**
     * Creates a psr-7 request from PHP global variables and sends the corresponding response to the client.
     */
    public function run()
    {
        $this->getHttpResponseSender()->send(
            $this->handle(
                $this->createRequestFromGlobals()
            )
        );
    }

    /**
     * Handles a psr-7 request to convert it to a psr-7 response.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->getRequestHandler()->handle($request);
    }

    /**
     * @return ServerRequestInterface
     */
    private function createRequestFromGlobals(): ServerRequestInterface
    {
        return $this->getHttpRequestFactoryFromGlobals()->createServerRequestFromGlobals();
    }

    /**
     * @return ServerRequestFactoryFromGlobalsInterface
     */
    private function getHttpRequestFactoryFromGlobals(): ServerRequestFactoryFromGlobalsInterface
    {
        return $this->getContainer()->get('http.request_factory_from_globals');
    }

    /**
     * @return RequestHandlerInterface
     */
    private function getRequestHandler(): RequestHandlerInterface
    {
        return $this->getContainer()->get('http.request_handler');
    }

    /**
     * @return mixed
     */
    private function getHttpResponseSender(): ResponseSenderInterface
    {
        return $this->getContainer()->get('http.response_sender');
    }

    /**
     * @return ContainerInterface
     */
    private function getContainer(): ContainerInterface
    {
        if (null === $this->container) {
            $this->container = $this->buildContainer();
        }

        return $this->container;
    }

    /**
     * @return Container
     */
    private function buildContainer(): Container
    {
        $container = new Container();

        $container->set('environment', $this->environment);
        $container->set('debug', $this->debug);

        $container->set('http.request_handler', function (Container $container) {
            return new Server(
                $container->get('http.final_handler'),
                $container
            );
        });

        $container->set('http.request_factory_from_globals', function () {
            return new ServerRequestFactoryFromGlobals();
        });

        $container->set('http.response_factory', function () {
            return new ResponseFactory();
        });

        $container->set('http.response_sender', function () {
            return new ResponseSender();
        });

        $container->set('http.final_handler', function (Container $container) {
            return new RouterHandler(
                $container->get(RouterInterface::class),
                $container->get(ControllerResolverInterface::class),
                $container->get(ArgumentsResolverInterface::class),
                $container->get('http.response_factory')
            );
        });

        $container->alias(RouterInterface::class, RouterCollection::class);

        $container->set(RouterCollection::class, function (Container $container) {
            $collection = new RouterCollection();
            $collection->registerRouter($container->get(RouterMap::class));

            return $collection;
        });

        $container->set(RouterMap::class, function (Container $container) {
            return new RouterMap($container->get(RouterMapAdapterInterface::class));
        });

        $container->set(RouterMapAdapterInterface::class, function () {
            return new AuraRouterMapAdapter();
        });

        $container->set(ControllerResolverInterface::class, function (Container $container) {
            return new ControllerResolver($container);
        });

        $container->set(ArgumentsResolverInterface::class, function () {
            return new ArgumentsResolver();
        });

        foreach ($this->modules as $module) {
            $module->boot($container);
        }

        $container->freeze();

        return $container;
    }
}
