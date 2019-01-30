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
 * Interface to serialize a psr-7 request for log or print purpose.
 *
 * @author Daniele De Nobili <danieledenobili@gmail.com>
 */
interface RequestSerializerInterface
{
    /**
     * Serializes a psr-7 request object.
     *
     * @param RequestInterface $request
     * @return array|string
     */
    public function serializeRequest(RequestInterface $request);
}
