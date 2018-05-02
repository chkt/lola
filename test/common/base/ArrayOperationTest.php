<?php

namespace test\common\base;

use PHPUnit\Framework\TestCase;
use lola\common\base\ArrayOperation;



final class ArrayOperationTest
extends TestCase
{

	public function testInheritance() {
		$this->assertTrue(is_subclass_of(ArrayOperation::class, \eve\common\base\ArrayOperation::class));
	}

	public function testIterate() {
		$data = [
			'foo' => 1,
			'bar' => [
				'foo' => 2,
				'bar' => 3,
				'baz' => [
					'foo' => [],
					'bar' => 4
				]
			],
			'baz' => 5
		];

		$gen = ArrayOperation::iterate($data);
		$expectValue = 1;

		foreach ($gen as $key => $value) {
			if ($expectValue === 1) $expectKey = 'foo';
			else if ($expectValue === 2) $expectKey = 'bar.foo';
			else if ($expectValue === 3) $expectKey = 'bar.bar';
			else if ($expectValue === 4) $expectKey = 'bar.baz.bar';
			else if ($expectValue === 5) $expectKey = 'baz';
			else $this->fail();

			$this->assertEquals($expectKey, $key);
			$this->assertEquals($expectValue, $value);

			$expectValue += 1;
		}

		$this->assertEquals(6, $expectValue);
	}
}
