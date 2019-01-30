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
 * Interface to serialize a psr-7 response for log or print purpose.
 *
 * @author Daniele De Nobili <danieledenobili@gmail.com>
 */
interface ResponseSerializerInterface
{
    /**
     * Serializes a psr-7 response object.
     *
     * @param ResponseInterface $response
     * @return array|string
     */
    public function serializeResponse(ResponseInterface $response);
}
