<?php

namespace test\type;

use PHPUnit\Framework\TestCase;

use lola\type\ASizedIterateable;



final class ASizedIterateableTest
extends TestCase
{

	private function _mockIterateable(array& $items = ['foo', 'bar']) : ASizedIterateable {
		$ins = $this
			->getMockBuilder(ASizedIterateable::class)
			->getMockForAbstractClass();

		$ins
			->expects($this->any())
			->method('getLength')
			->with()
			->willReturnCallback(function() use (& $items) {
				return count($items);
			});

		$ins
			->expects($this->any())
			->method('_useItem')
			->with($this->isType('integer'))
			->willReturnCallback(function(int $index) use (& $items) {
				return $items[$index];
			});

		return $ins;
	}


	public function testUseIndex() {
		$ins = $this->_mockIterateable();

		$this->assertEquals('foo', $ins->useIndex(0));
		$this->assertEquals(0, $ins->getIndex());
		$this->assertEquals('bar', $ins->useIndex(1));
		$this->assertEquals(1, $ins->getIndex());
		$this->assertNull($ins->useIndex(2));
		$this->assertEquals(2, $ins->getIndex());
		$this->assertNull($ins->useIndex(-1));
		$this->assertEquals(-1, $ins->getIndex());
	}

	public function testUseLast() {
		$ins = $this->_mockIterateable();

		$this->assertEquals('foo', $ins->useFirst());
		$this->assertEquals(0, $ins->getIndex());
		$this->assertEquals('bar', $ins->useLast());
		$this->assertEquals(1, $ins->getIndex());
	}
}
