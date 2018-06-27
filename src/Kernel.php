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
use Http\Factory\Diactoros\ServerRequestFactory;
use Interop\Http\Factory\ServerRequestFactoryInterface;
use Lepre\DI\Container;
use Lepre\Framework\Controller\ArgumentsResolver;
use Lepre\Framework\Controller\ArgumentsResolverInterface;
use Lepre\Framework\Controller\ControllerResolver;
use Lepre\Framework\Controller\ControllerResolverInterface;
use Lepre\Framework\Http\ResponseSender;
use Lepre\Framework\Http\ResponseSenderInterface;
use Lepre\Framework\Handler\RouterHandler;
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
 * Kernel
 *
 * @author Daniele De Nobili <danieledenobili@gmail.com>
 */
final class Kernel
{
    /**
     * @var ModuleInterface[]
     */
    private $modules;

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
    public function __construct(array $modules = [], string $environment = 'production', bool $debug = false)
    {
        $this->modules = $modules;
        $this->environment = $environment;
        $this->debug = $debug;
    }

    /**
     *
     */
    public function run()
    {
        $this->getHttpResponseSender()->send(
            $this->handle(
                $this->getHttpRequestFactory()->createServerRequestFromArray($_SERVER)
            )
        );
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->getRequestHandler()->handle($request);
    }

    /**
     * @return mixed
     */
    private function getHttpRequestFactory(): ServerRequestFactoryInterface
    {
        return $this->getContainer()->get('http.request_factory');
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

        $container->set('http.request_factory', function () {
            return new ServerRequestFactory();
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

        $container->set(RouterInterface::class, function (Container $container) {
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
            $this->registerModule($module, $container);
        }

        $container->freeze();

        return $container;
    }

    /**
     * @param ModuleInterface $module
     * @param Container       $container
     */
    private function registerModule(ModuleInterface $module, Container $container)
    {
        $module->boot($container);
    }
}
