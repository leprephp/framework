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

namespace Lepre\Framework;

use Lepre\DI\Container;

/**
 * Module interface.
 *
 * Defines all the hooks of a module lifecycle.
 *
 * @author Daniele De Nobili <danieledenobili@gmail.com>
 */
interface ModuleInterface
{
    /**
     * Hook on load the application kernel.
     *
     * You can use this hook to register services, add routes or set configurations.
     *
     * @param Container $container
     */
    public function boot(Container $container);

//    /**
//     * Hook on install the module.
//     *
//     * This hook is execute only at module installation. You can use this hook for prepare
//     * the database schema.
//     *
//     * @param Container $container
//     */
//    public function install(Container $container);
//
//    /**
//     * Hook on uninstall the module.
//     *
//     * This hook is execute only at module uninstallation. You can use this hook for remove
//     * the database schema modification made in the install hook.
//     *
//     * Please, note that the uninstall process must **delete all** data created by this module.
//     * You must also clean cache, logs and all temporary files.
//     *
//     * @param Container $container
//     */
//    public function uninstall(Container $container);
//
//    /**
//     * Hook on update the module.
//     *
//     * This hook is execute on update the module. You can use this hook for upgrade your
//     * database schema.
//     *
//     * @param Container $container
//     */
//    public function update(Container $container);
//
//    /**
//     * Hook on enable the module.
//     *
//     * @param Container $container
//     */
//    public function enable(Container $container);
//
//    /**
//     * Hook on disable the module.
//     *
//     * @param Container $container
//     */
//    public function disable(Container $container);
}
