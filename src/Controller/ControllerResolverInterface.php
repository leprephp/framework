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
 * Determines which controller to execute based on the handler returned by the router.
 *
 * This class is loosely based on the Symfony project ({@link https://symfony.com/}).
 *
 * @author Daniele De Nobili <danieledenobili@gmail.com>
 */
interface ControllerResolverInterface
{
    /**
     * Returns the controller callable associated with the given handler.
     *
     * @param mixed $handler
     * @return callable
     */
    public function getController($handler): callable;
}
