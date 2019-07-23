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

use Psr\Http\Message\ServerRequestInterface;

/**
 * This implementation extracts the controller arguments from the request attributes.
 *
 * This class is loosely based on the Symfony project ({@link https://symfony.com/}).
 *
 * @author Daniele De Nobili <danieledenobili@gmail.com>
 */
final class ArgumentsResolver implements ArgumentsResolverInterface
{
    /**
     * @inheritDoc
     */
    public function getArguments(callable $controller, ServerRequestInterface $request): array
    {
        $parameters = $this->getParameters($controller);
        $attributes = $request->getAttributes();

        $arguments = [];
        foreach ($parameters as $param) {
            if (array_key_exists($param->name, $attributes)) {
                $arguments[] = $attributes[$param->name];
            } elseif ($param->getClass() && $param->getClass()->getName() === ServerRequestInterface::class) {
                $arguments[] = $request;
            } elseif ($param->isDefaultValueAvailable()) {
                $arguments[] = $param->getDefaultValue();
            } else {
                if (is_array($controller)) {
                    $repr = sprintf('%s::%s()', get_class($controller[0]), $controller[1]);
                } elseif (is_object($controller)) {
                    $repr = get_class($controller);
                } else {
                    $repr = $controller;
                }

                throw new \RuntimeException(sprintf(
                    'Callable "%s" requires that you provide a value for the "$%s" argument (because there is no default value or because there is a non optional argument after this one).',
                    $repr,
                    $param->name
                ));
            }
        }

        return $arguments;
    }

    /**
     * @param callable $controller
     * @return \ReflectionParameter[]
     */
    private function getParameters(callable $controller): array
    {
        if (is_array($controller)) {
            $ref = new \ReflectionMethod($controller[0], $controller[1]);
        } elseif (is_object($controller) && !$controller instanceof \Closure) {
            $ref = (new \ReflectionObject($controller))->getMethod('__invoke');
        } else {
            $ref = new \ReflectionFunction($controller);
        }

        return $ref->getParameters();
    }
}
