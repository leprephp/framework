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

namespace Lepre\Framework\Tests\Test;

use Lepre\Framework\Test\HeaderStack;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lepre\Framework\Test\HeaderStack
 */
final class HeaderStackTest extends TestCase
{
    public function setUp(): void
    {
        HeaderStack::reset();
    }

    public function testHeaderStack()
    {
        $this->assertEmpty(HeaderStack::all());

        HeaderStack::push('Foo: bar');
        $this->assertNotEmpty(HeaderStack::all());
        $this->assertTrue(HeaderStack::has('Foo: bar'));

        HeaderStack::reset();
        $this->assertEmpty(HeaderStack::all());
    }
}
