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

namespace Lepre\Framework\Tests\Http;

use Lepre\Framework\Http\ServerRequestFactoryFromGlobals;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequest;

/**
 * @covers \Lepre\Framework\Http\ServerRequestFactoryFromGlobals
 */
class ServerRequestFactoryFromGlobalsTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testCreateServerRequestFromGlobals()
    {
        $_SERVER = [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI'    => '/',
        ];

        $factory = new ServerRequestFactoryFromGlobals();
        $request = $factory->createServerRequestFromGlobals();

        $this->assertInstanceOf(ServerRequest::class, $request);
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/', $request->getUri()->getPath());
    }
}
