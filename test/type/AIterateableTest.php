<?php

namespace test\type;

use PHPUnit\Framework\TestCase;

use lola\type\AIterateable;



final class AIterateableTest
extends TestCase
{

	private function _mockIterateable(array& $items = ['foo', 'bar']) : AIterateable {
		$ins = $this
			->getMockBuilder(AIterateable::class)
			->getMockForAbstractClass();

		$ins
			->expects($this->any())
			->method('_hasItem')
			->with($this->isType('integer'))
			->willReturnCallback(function(int $index) use (& $items) : bool {
				return $index > -1 && $index < count($items);
			});

		$ins
			->expects($this->any())
			->method('_useItem')
			->with($this->isType('integer'))
			->willReturnCallback(function& (int $index) use (& $items) {
				return $items[$index];
			});

		return $ins;
	}


	public function testGetIndex() {
		$ins = $this->_mockIterateable();

		$this->assertEquals(0, $ins->getIndex());
	}

	public function testUseIndex() {
		$ins = $this->_mockIterateable();

		$this->assertEquals('foo', $ins->useIndex(0));
		$this->assertEquals(0, $ins->getIndex());
		$this->assertEquals('bar', $ins->useIndex(1));
		$this->assertEquals(1, $ins->getIndex());
		$this->assertNull($ins->useIndex(2));
		$this->assertEquals(2, $ins->getIndex());
	}

	public function testUseOffset() {
		$ins = $this->_mockIterateable();

		$this->assertEquals('bar', $ins->useOffset(1));
		$this->assertEquals(1, $ins->getIndex());
		$this->assertEquals('foo', $ins->useOffset(-1));
		$this->assertEquals(0, $ins->getIndex());
		$this->assertNull($ins->useOffset(2));
		$this->assertEquals(2, $ins->getIndex());
	}

	public function testUseFirst() {
		$ins = $this->_mockIterateable();

		$this->assertEquals('bar', $ins->useIndex(1));
		$this->assertEquals('foo', $ins->useFirst());
		$this->assertEquals(0, $ins->getIndex());
	}

	public function testUsePrev() {
		$ins = $this->_mockIterateable();

		$this->assertEquals('bar', $ins->useNext());
		$this->assertEquals(1, $ins->getIndex());
		$this->assertNull($ins->useNext());
		$this->assertEquals(2, $ins->getIndex());
	}

	public function testUseNext() {
		$ins = $this->_mockIterateable();

		$this->assertNull($ins->useIndex(2));
		$this->assertEquals(2, $ins->getIndex());
		$this->assertEquals('bar', $ins->usePrev());
		$this->assertEquals(1, $ins->getIndex());
		$this->assertEquals('foo', $ins->usePrev());
	}


	public function testIterate() {
		$list = [ 'baz', 'quux' ];
		$ins = $this->_mockIterateable($list);

		$cursor = 0;

		foreach ($ins->iterate() as $index => & $item) {
			$this->assertEquals($cursor, $index);
			$this->assertEquals($list[$cursor], $item);

			$cursor += 1;
		}

		$this->assertEquals(2, $cursor);
	}
}
