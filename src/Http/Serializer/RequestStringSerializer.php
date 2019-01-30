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

use Psr\Http\Message\RequestInterface;

/**
 * Serialize a psr-7 request in a string.
 *
 * @author Daniele De Nobili <danieledenobili@gmail.com>
 */
final class RequestStringSerializer extends AbstractMessageStringSerializer implements RequestSerializerInterface
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
}
