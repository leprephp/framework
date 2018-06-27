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

namespace Lepre\Framework\Tests\Controller;

use Lepre\DI\Exception\NotFoundException;
use Lepre\Framework\Controller\ControllerResolver;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers \Lepre\Framework\Controller\ControllerResolver
 */
final class ControllerResolverTest extends TestCase
{
    public function testClosure()
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->never())->method('has');
        $container->expects($this->never())->method('get');
        $controller = function () {};

        /**
         * @var ContainerInterface $container
         */

        $resolver = new ControllerResolver($container);
        $this->assertSame($controller, $resolver->getController($controller));
    }

    public function testService()
    {
        $container = $this->createMock(ContainerInterface::class);
        $controller = function () {};

        $container->method('has')
            ->with('controller_service_name')
            ->willReturn(true);

        $container
            ->method('get')
            ->with('controller_service_name')
            ->willReturn($controller);

        /**
         * @var ContainerInterface $container
         */

        $resolver = new ControllerResolver($container);
        $this->assertSame($controller, $resolver->getController('controller_service_name'));
    }

    public function testColon()
    {
        $container = $this->createMock(ContainerInterface::class);
        $controller = new class {
            public function fooAction() {}
        };

        $container->method('has')
            ->willReturnMap([
                ['controller', true],
                ['controller:fooAction', false],
            ]);

        $container->method('get')
            ->willReturnMap([
                ['controller', $controller],
                ['controller:fooAction', $this->throwException(new NotFoundException('controller:fooAction'))],
            ]);

        /**
         * @var ContainerInterface $container
         */

        $resolver = new ControllerResolver($container);
        $this->assertSame([$controller, 'fooAction'], $resolver->getController('controller:fooAction'));
    }

    public function testServiceHasMorePriorityThenColon()
    {
        $container = $this->createMock(ContainerInterface::class);
        $controller = function () {};
        $colonController = function () {};

        $container->method('has')
            ->willReturnMap([
                ['controller', true],
                ['controller:fooAction', true],
            ]);

        $container->method('get')
            ->willReturnMap([
                ['controller', $controller],
                ['controller:fooAction', $colonController],
            ]);

        /**
         * @var ContainerInterface $container
         */

        $resolver = new ControllerResolver($container);
        $this->assertSame($colonController, $resolver->getController('controller:fooAction'));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The handler must be a callable, a valid service name or a string in the form "controller:action".
     */
    public function testException()
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->with('controller')->willReturn(false);

        /**
         * @var ContainerInterface $container
         */

        $resolver = new ControllerResolver($container);
        $resolver->getController('controller');
    }
}
