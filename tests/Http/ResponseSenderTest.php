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

namespace Lepre\Framework\Http {

    if (!function_exists('Lepre\Framework\Http\header')) {
        function header($string)
        {
            \Lepre\Framework\Test\HeaderStack::push($string);
        }
    }
}

namespace Lepre\Framework\Tests\Http {

    use Lepre\Framework\Http\ResponseSender;
    use Lepre\Framework\Test\HeaderStack;
    use PHPUnit\Framework\TestCase;
    use Zend\Diactoros\Response;

    /**
     * @covers \Lepre\Framework\Http\ResponseSender
     */
    class ResponseSenderTest extends TestCase
    {
        /**
         * @var ResponseSender
         */
        private $responseSender;

        public function setUp()
        {
            HeaderStack::reset();
            $this->responseSender = new ResponseSender();
        }

        /**
         * @runInSeparateProcess
         */
        public function testSend()
        {
            $response = (new Response())
                ->withStatus(200)
                ->withHeader('Content-Type', 'text/plain');

            $response->getBody()->write('This is the content!');

            ob_start();
            $this->responseSender->send($response);
            $content = ob_get_contents();
            ob_end_clean();

            $this->assertTrue(HeaderStack::has('HTTP/1.1 200 OK'));
            $this->assertTrue(HeaderStack::has('Content-Type: text/plain'));
            $this->assertEquals('This is the content!', $content);
        }

        public function testSendResponseWithoutReasonPhrase()
        {
            $response = new class extends Response {
                public function getReasonPhrase()
                {
                    return null;
                }
            };

            $response = $response
                ->withStatus(200)
                ->withHeader('Content-Type', 'text/html');

            $response->getBody()->write('This is the content!');

            ob_start();
            $this->responseSender->send($response);
            $content = ob_get_contents();
            ob_end_clean();

            $this->assertTrue(HeaderStack::has('HTTP/1.1 200 OK'));
            $this->assertTrue(HeaderStack::has('Content-Type: text/html'));
            $this->assertEquals('This is the content!', $content);
        }
    }
}
