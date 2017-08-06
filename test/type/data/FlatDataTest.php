<?php

namespace test\type\data;

use PHPUnit\Framework\TestCase;

use lola\type\data\IKeyAccessor;
use lola\type\data\IKeyMutator;
use lola\type\data\IItemAccessor;
use lola\type\data\IItemMutator;
use lola\type\data\FlatData;
use lola\type\data\FlatAccessException;



final class FlatDataTest
extends TestCase
{

	private function _produceData(array& $data = null) {
		if (is_null($data)) $data = [
			'foo' => 1,
			'bar' => 2
		];

		return new FlatData($data);
	}


	public function testInheritance() {
		$data = $this->_produceData();

		$this->assertInstanceOf(IKeyAccessor::class, $data);
		$this->assertInstanceOf(IKeyMutator::class, $data);
		$this->assertInstanceOf(IItemAccessor::class, $data);
		$this->assertInstanceOf(IItemMutator::class, $data);
	}


	public function testHasKey() {
		$data = $this->_produceData();

		$this->assertTrue($data->hasKey('foo'));
		$this->assertTrue($data->hasKey('bar'));
		$this->assertFalse($data->hasKey('baz'));
	}

	public function testRemoveKey() {
		$data = $this->_produceData();

		$this->assertSame($data, $data->removeKey('foo'));
		$this->assertFalse($data->hasKey('foo'));
		$this->assertTrue($data->hasKey('bar'));
		$this->assertSame($data, $data->removeKey('baz'));
		$this->assertFalse($data->hasKey('baz'));
		$this->assertTrue($data->hasKey('bar'));
	}

	public function testUseItem() {
		$data = $this->_produceData();

		$this->assertEquals(1, $data->useItem('foo'));
		$this->assertEquals(2, $data->useItem('bar'));

		$i =& $data->useItem('foo');
		$i = 3;

		$this->assertEquals(3, $data->useItem('foo'));
	}

	public function testUseItem_noKey() {
		$data = $this->_produceData();

		$this->expectException(FlatAccessException::class);
		$this->expectExceptionMessage('ACC_NO_PROP:baz');

		$data->useItem('baz');
	}

	public function testSetItem() {
		$data = $this->_produceData();

		$this->assertSame($data, $data->setItem('foo', 3));
		$this->assertEquals(3, $data->useItem('foo'));
		$this->assertSame($data, $data->setItem('baz', 4));
		$this->assertEquals(4, $data->useItem('baz'));
	}
}
