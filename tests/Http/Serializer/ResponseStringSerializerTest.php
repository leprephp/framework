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
use Lepre\Framework\Http\Serializer\ResponseStringSerializer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;

class ResponseStringSerializerTest extends TestCase
{
    /**
     * @param ResponseInterface $response
     * @param string                 $expected
     *
     * @dataProvider serializeProvider
     */
    public function testSerialize(ResponseInterface $response, string $expected)
    {
        $serializer = new ResponseStringSerializer();
        $this->assertEquals($expected, $serializer->serializeResponse($response));
    }

    public function serializeProvider()
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
