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

/**
 * ControllerResolverInterface
 *
 * @author Daniele De Nobili <danieledenobili@gmail.com>
 */
interface ControllerResolverInterface
{
    /**
     * @param mixed $handler
     * @return callable
     */
    public function getController($handler): callable;
}
