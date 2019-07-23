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

namespace Lepre\Framework\Http\Serializer;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Daniele De Nobili <danieledenobili@gmail.com>
 */
final class MessageStringSerializer implements MessageSerializerInterface
{
    /**
     * @inheritDoc
     *
     * @return string
     */
    public function serializeRequest(RequestInterface $request)
    {
        $body = $this->prepareBody($request);

        $result = "{$request->getMethod()} {$request->getUri()->getPath()} HTTP/{$request->getProtocolVersion()}";
        $result .= "\n{$this->prepareHeaders($request)}";

        if ($body) {
            $result .= "\n\n{$body}";
        }

        return $result;
    }

    /**
     * @inheritDoc
     *
     * @return string
     */
    public function serializeResponse(ResponseInterface $response)
    {
        $body = $this->prepareBody($response);

        $result = "HTTP/{$response->getProtocolVersion()} {$response->getStatusCode()} {$response->getReasonPhrase()}";
        $result .= "\n{$this->prepareHeaders($response)}";

        if ($body) {
            $result .= "\n\n{$body}";
        }

        return $result;
    }

    /**
     * @param MessageInterface $message
     * @return string
     */
    private function prepareHeaders(MessageInterface $message)
    {
        $headers = [];
        foreach ($message->getHeaders() as $name => $values) {
            $headers[] = $name . ': ' . implode(', ', $values);
        }

        return implode("\n", $headers);
    }

    /**
     * @param MessageInterface $message
     * @return string
     */
    private function prepareBody(MessageInterface $message)
    {
        return (string) $message->getBody()->getContents();
    }
}
