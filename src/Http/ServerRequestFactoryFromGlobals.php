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
use Zend\Diactoros\ServerRequestFactory;

/**
 * Uses the Zend Diactoros ServerRequestFactory to implement the ServerRequestFactoryFromGlobalsInterface.
 *
 * @author Daniele De Nobili <danieledenobili@gmail.com>
 */
final class ServerRequestFactoryFromGlobals implements ServerRequestFactoryFromGlobalsInterface
{
    /**
     * @inheritDoc
     */
    public function createServerRequestFromGlobals(): ServerRequestInterface
    {
        return ServerRequestFactory::fromGlobals();
    }
}
