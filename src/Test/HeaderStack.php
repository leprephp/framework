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

namespace Lepre\Framework\Test;

/**
 * Store response output artifacts.
 *
 * This class is loosely based on the Zend Diactoros project ({@link https://github.com/zendframework/zend-diactoros}).
 *
 * @author Daniele De Nobili <danieledenobili@gmail.com>
 */
final class HeaderStack
{
    /**
     * @var array
     */
    private static $stack = [];

    /**
     * Resets the headers stack.
     */
    public static function reset()
    {
        self::$stack = [];
    }

    /**
     * Push a header string.
     *
     * @param string $header
     */
    public static function push(string $header)
    {
        self::$stack[] = $header;
    }

    /**
     * Return true if the header string is present in the stack, false otherwise.
     *
     * @param string $header
     * @return bool
     */
    public static function has(string $header): bool
    {
        return in_array($header, self::$stack);
    }

    /**
     * @return array
     */
    public static function all(): array
    {
        return self::$stack;
    }
}
