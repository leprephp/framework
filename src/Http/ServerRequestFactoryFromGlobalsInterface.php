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

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface to create a ServerRequest from PHP global variables.
 *
 * @author Daniele De Nobili <danieledenobili@gmail.com>
 */
interface ServerRequestFactoryFromGlobalsInterface
{
    /**
     * Creates a ServerRequest from PHP global variables.
     *
     * @return ServerRequestInterface
     */
    public function createServerRequestFromGlobals(): ServerRequestInterface;
}
