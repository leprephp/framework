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

use Lepre\Framework\Controller\ArgumentsResolver;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @covers \Lepre\Framework\Controller\ArgumentsResolver
 */
final class ArgumentsResolverTest extends TestCase
{
    public function testClosureWithoutArguments()
    {
        $resolver = new ArgumentsResolver();
        $request = $this->createRequestMock();

        $this->assertEquals([], $resolver->getArguments(function () {}, $request));
    }

    public function testClosureWithRequestInArguments()
    {
        $resolver = new ArgumentsResolver();
        $request = $this->createRequestMock([
            'foo' => 'Foo Value',
        ]);

        $this->assertEquals(
            ['Foo Value', $request, 'Default Value'],
            $resolver->getArguments(
                function ($foo, ServerRequestInterface $request, $bar = 'Default Value') {},
                $request
            )
        );
    }

    public function testClosureWithoutDefaultArgumentValue()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Callable "Closure" requires that you provide a value for the "$foo" argument (because there is no default value or because there is a non optional argument after this one).');

        $resolver = new ArgumentsResolver();
        $request = $this->createRequestMock();

        $resolver->getArguments(
            function ($foo) {},
            $request
        );
    }

    public function testFunctionWithoutArguments()
    {
        function fooController() {}

        $resolver = new ArgumentsResolver();
        $request = $this->createRequestMock();

        $this->assertEquals([], $resolver->getArguments(__NAMESPACE__ . '\\fooController', $request));
    }

    public function testFunctionWithRequestInArguments()
    {
        function fooController2($foo, ServerRequestInterface $request, $bar = 'Default Value') {}

        $resolver = new ArgumentsResolver();
        $request = $this->createRequestMock([
            'foo' => 'Foo Value',
        ]);

        $this->assertEquals(
            ['Foo Value', $request, 'Default Value'],
            $resolver->getArguments(__NAMESPACE__ . '\\fooController2', $request)
        );
    }

    public function testFunctionWithoutDefaultArgumentValue()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Callable "Lepre\\Framework\\Tests\\Controller\\fooController3" requires that you provide a value for the "$foo" argument (because there is no default value or because there is a non optional argument after this one).');

        function fooController3($foo) {}

        $resolver = new ArgumentsResolver();
        $request = $this->createRequestMock();

        $resolver->getArguments(__NAMESPACE__ . '\\fooController3', $request);
    }

    public function testInvokableWithoutArguments()
    {
        $controller = new class() {
            public function __invoke() {}
        };

        $resolver = new ArgumentsResolver();
        $request = $this->createRequestMock();

        $this->assertEquals([], $resolver->getArguments($controller, $request));
    }

    public function testInvokableWithRequestInArguments()
    {
        $controller = new class() {
            public function __invoke($foo, ServerRequestInterface $request, $bar = 'Default Value') {}
        };

        $resolver = new ArgumentsResolver();
        $request = $this->createRequestMock([
            'foo' => 'Foo Value',
        ]);

        $this->assertEquals(
            ['Foo Value', $request, 'Default Value'],
            $resolver->getArguments($controller, $request)
        );
    }

    public function testInvokableWithoutDefaultArgumentValue()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Callable "Lepre\\Framework\\Tests\\Controller\\InvokableWithoutDefaultArgumentValueFixture" requires that you provide a value for the "$foo" argument (because there is no default value or because there is a non optional argument after this one).');

        $resolver = new ArgumentsResolver();
        $request = $this->createRequestMock();

        $resolver->getArguments(new InvokableWithoutDefaultArgumentValueFixture(), $request);
    }

    public function testArrayCallableWithoutArguments()
    {
        $controller = new class() {
            public function foo() {}
        };

        $resolver = new ArgumentsResolver();
        $request = $this->createRequestMock();

        $this->assertEquals([], $resolver->getArguments([$controller, 'foo'], $request));
    }

    public function testArrayCallableWithRequestInArguments()
    {
        $controller = new class() {
            public function foo($foo, ServerRequestInterface $request, $bar = 'Default Value') {}
        };

        $resolver = new ArgumentsResolver();
        $request = $this->createRequestMock([
            'foo' => 'Foo Value',
        ]);

        $this->assertEquals(
            ['Foo Value', $request, 'Default Value'],
            $resolver->getArguments([$controller, 'foo'], $request)
        );
    }

    public function testArrayCallableWithoutDefaultArgumentValue()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Callable "Lepre\\Framework\\Tests\\Controller\\ArrayCallableWithoutDefaultArgumentValueFixture::foo()" requires that you provide a value for the "$foo" argument (because there is no default value or because there is a non optional argument after this one).');

        $resolver = new ArgumentsResolver();
        $request = $this->createRequestMock();

        $resolver->getArguments([new ArrayCallableWithoutDefaultArgumentValueFixture(), 'foo'], $request);
    }

    /**
     * @param array $attributes
     * @return \PHPUnit_Framework_MockObject_MockObject|ServerRequestInterface
     */
    private function createRequestMock(array $attributes = [])
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttributes')->willReturn($attributes);

        return $request;
    }
}

class InvokableWithoutDefaultArgumentValueFixture {
    public function __invoke($foo) {

    }
}

class ArrayCallableWithoutDefaultArgumentValueFixture {
    public function foo($foo) {

    }
}
