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
use Lepre\Framework\Http\Serializer\MessageStringSerializer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Uri;

/**
 * @covers \Lepre\Framework\Http\Serializer\MessageStringSerializer
 */
class MessageStringSerializerTest extends TestCase
{
    /**
     * @param ServerRequestInterface $request
     * @param string                 $expected
     *
     * @dataProvider requestsProvider
     */
    public function testSerializeRequest(ServerRequestInterface $request, string $expected)
    {
        $serializer = new MessageStringSerializer();
        $this->assertEquals($expected, $serializer->serializeRequest($request));
    }

    /**
     * @param ResponseInterface $response
     * @param string            $expected
     *
     * @dataProvider responsesProvider
     */
    public function testSerializeResponse(ResponseInterface $response, string $expected)
    {
        $serializer = new MessageStringSerializer();
        $this->assertEquals($expected, $serializer->serializeResponse($response));
    }

    public function requestsProvider()
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

    public function responsesProvider()
    {
        $response = (new Response())
            ->withStatus(200)
            ->withHeader('Foo', 'Bar');

        $expected = <<<EOT
HTTP/1.1 200 OK
Foo: Bar
EOT;

        yield [$response, $expected];

        $response = (new Response())
            ->withStatus(404, 'Page not found')
            ->withProtocolVersion('2')
            ->withHeader('Content-Type', 'text/html')
            ->withBody((new StreamFactory())->createStream('Hello world!'));

        $expected = <<<EOT
HTTP/2 404 Page not found
Content-Type: text/html

Hello world!
EOT;

        yield [$response, $expected];
    }
}
