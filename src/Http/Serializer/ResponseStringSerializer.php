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

use Psr\Http\Message\ResponseInterface;

/**
 * ResponseStringSerializer
 */
final class ResponseStringSerializer extends AbstractMessageStringSerializer implements ResponseSerializerInterface
{
    /**
     * @inheritdoc
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
}
