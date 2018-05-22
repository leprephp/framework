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

namespace Lepre\Framework\Controller;

use Psr\Http\Message\ServerRequestInterface;

/**
 * ArgumentsResolverInterface
 *
 * @author Daniele De Nobili <danieledenobili@gmail.com>
 */
interface ArgumentsResolverInterface
{
    /**
     * @param callable               $controller
     * @param ServerRequestInterface $request
     * @return array
     */
    public function getArguments(callable $controller, ServerRequestInterface $request): array;
}
