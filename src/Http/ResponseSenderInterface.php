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

namespace Lepre\Framework\Http;

use Psr\Http\Message\ResponseInterface;

/**
 * The psr-7 sender response interface.
 *
 * @author Daniele De Nobili <danieledenobili@gmail.com>
 */
interface ResponseSenderInterface
{
    /**
     * Sends a psr-7 response to the client.
     *
     * @param ResponseInterface $response
     */
    public function send(ResponseInterface$response);
}
