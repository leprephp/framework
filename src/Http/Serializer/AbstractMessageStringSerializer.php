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

/**
 * @author Daniele De Nobili <danieledenobili@gmail.com>
 */
abstract class AbstractMessageStringSerializer
{
    /**
     * @param MessageInterface $message
     * @return string
     */
    protected function prepareHeaders(MessageInterface $message)
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
    protected function prepareBody(MessageInterface $message)
    {
        return (string) $message->getBody()->getContents();
    }
}
