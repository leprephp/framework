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

namespace Lepre\Framework\Tests\Http\Serializer;

use Http\Factory\Diactoros\StreamFactory;
use Lepre\Framework\Http\Serializer\RequestStringSerializer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;

/**
 * @covers \Lepre\Framework\Http\Serializer\AbstractMessageStringSerializer
 * @covers \Lepre\Framework\Http\Serializer\RequestStringSerializer
 */
class RequestStringSerializerTest extends TestCase
{
    /**
     * @param ServerRequestInterface $request
     * @param string                 $expected
     *
     * @dataProvider serializeProvider
     */
    public function testSerialize(ServerRequestInterface $request, string $expected)
    {
        $serializer = new RequestStringSerializer();
        $this->assertEquals($expected, $serializer->serializeRequest($request));
    }

    public function serializeProvider()
    {
        $request = (new ServerRequest())
            ->withMethod('GET')
            ->withUri(new Uri('http://example.com/'))
            ->withHeader('Foo', 'Bar');

        $expected = <<<EOT
GET / HTTP/1.1
Host: example.com
Foo: Bar
EOT;

        yield [$request, $expected];

        $request = (new ServerRequest())
            ->withMethod('PUT')
            ->withUri(new Uri('/edit'))
            ->withProtocolVersion('2')
            ->withHeader('Content-Type', 'text/html')
            ->withBody((new StreamFactory())->createStream('key=value&id=123'));

        $expected = <<<EOT
PUT /edit HTTP/2
Content-Type: text/html

key=value&id=123
EOT;

        yield [$request, $expected];
    }
}
