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

namespace Lepre\Framework\Controller;

use Psr\Container\ContainerInterface;

/**
 * ControllerResolver
 *
 * @author Daniele De Nobili <danieledenobili@gmail.com>
 */
final class ControllerResolver implements ControllerResolverInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritDoc
     */
    public function getController($handler): callable
    {
        if (is_callable($handler)) {
            return $handler;
        }

        if (is_string($handler)) {
            if ($this->container->has($handler)) {
                return $this->container->get($handler);
            }

            if ($pos = strpos($handler, ':')) {
                return [
                    $this->container->get(substr($handler, 0, $pos)),
                    substr($handler, $pos + 1)
                ];
            }
        }

        throw new \RuntimeException('The handler must be a callable, a valid service name or a string in the form "controller:action".');
    }
}
